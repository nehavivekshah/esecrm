<?php

namespace App\Http\Controllers;

use App\Models\Leads;
use App\Models\LeadAssigns;
use App\Models\Lead_comments;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LeadApiController extends Controller
{
    /**
     * Get all leads with filters
     */
    public function index(Request $request)
    {
        $query = Leads::query();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by assigned user
        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by company
        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mob', 'like', "%{$search}%");
            });
        }

        // Pagination
        $leads = $query->with(['creator', 'assignedUser', 'comments'])
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'message' => 'Leads retrieved successfully',
            'data' => $leads,
        ]);
    }

    /**
     * Create a new lead
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'mob' => 'nullable|regex:/^[0-9]{10}$/',
            'gstno' => 'nullable|string|max:15',
            'location' => 'nullable|string|max:255',
            'purpose' => 'nullable|string|max:255',
            'poc' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|regex:/^[0-9]{10}$/',
            'position' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|url',
            'values' => 'nullable|numeric',
            'language' => 'nullable|string|max:50',
            'tags' => 'nullable|array',
            'company_id' => 'nullable|exists:companies,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'new';

        $lead = Leads::create($validated);

        // Log activity
        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'entity_type' => 'Lead',
            'entity_id' => $lead->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'message' => 'Lead created successfully',
            'data' => $lead->load('creator', 'assignedUser'),
        ], 201);
    }

    /**
     * Get a single lead
     */
    public function show($id)
    {
        $lead = Leads::with(['creator', 'assignedUser', 'assignments', 'comments'])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Lead retrieved successfully',
            'data' => $lead,
        ]);
    }

    /**
     * Update a lead
     */
    public function update(Request $request, $id)
    {
        $lead = Leads::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'company' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'mob' => 'sometimes|regex:/^[0-9]{10}$/',
            'gstno' => 'sometimes|string|max:15',
            'location' => 'sometimes|string|max:255',
            'purpose' => 'sometimes|string|max:255',
            'poc' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:new,qualified,negotiating,won,lost',
            'whatsapp' => 'sometimes|regex:/^[0-9]{10}$/',
            'position' => 'sometimes|string|max:255',
            'industry' => 'sometimes|string|max:255',
            'website' => 'sometimes|url',
            'values' => 'sometimes|numeric',
            'language' => 'sometimes|string|max:50',
            'tags' => 'sometimes|array',
            'assigned_to' => 'sometimes|exists:users,id',
        ]);

        // Track changes
        $changes = [];
        foreach ($validated as $key => $value) {
            if ($lead->$key !== $value) {
                $changes[$key] = ['old' => $lead->$key, 'new' => $value];
            }
        }

        $lead->update($validated);

        // Log activity
        if (!empty($changes)) {
            Activity::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'entity_type' => 'Lead',
                'entity_id' => $lead->id,
                'changes' => $changes,
                'ip_address' => request()->ip(),
            ]);
        }

        return response()->json([
            'message' => 'Lead updated successfully',
            'data' => $lead->load('creator', 'assignedUser'),
        ]);
    }

    /**
     * Delete a lead
     */
    public function destroy($id)
    {
        $lead = Leads::findOrFail($id);

        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'entity_type' => 'Lead',
            'entity_id' => $id,
            'ip_address' => request()->ip(),
        ]);

        $lead->delete();

        return response()->json([
            'message' => 'Lead deleted successfully',
        ]);
    }

    /**
     * Assign lead to user
     */
    public function assign(Request $request, $id)
    {
        $lead = Leads::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'deadline' => 'nullable|date|after:today',
        ]);

        LeadAssigns::create([
            'lead_id' => $id,
            'user_id' => $validated['user_id'],
            'assigned_date' => now(),
            'deadline' => $validated['deadline'] ?? null,
            'priority' => $validated['priority'] ?? 'medium',
        ]);

        $lead->update(['assigned_to' => $validated['user_id']]);

        return response()->json([
            'message' => 'Lead assigned successfully',
            'data' => $lead->load('assignedUser'),
        ]);
    }

    /**
     * Add comment to lead
     */
    public function addComment(Request $request, $id)
    {
        $lead = Leads::findOrFail($id);

        $validated = $request->validate([
            'comment' => 'required|string',
            'comment_type' => 'nullable|in:note,status_change,action',
        ]);

        $comment = Lead_comments::create([
            'lead_id' => $id,
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
            'comment_type' => $validated['comment_type'] ?? 'note',
        ]);

        return response()->json([
            'message' => 'Comment added successfully',
            'data' => $comment->load('user'),
        ], 201);
    }

    /**
     * Get lead comments
     */
    public function getComments($id)
    {
        $lead = Leads::findOrFail($id);
        $comments = $lead->comments()->with('user')->latest()->get();

        return response()->json([
            'message' => 'Comments retrieved successfully',
            'data' => $comments,
        ]);
    }

    /**
     * Get lead statistics
     */
    public function statistics()
    {
        $stats = [
            'total_leads' => Leads::count(),
            'new_leads' => Leads::where('status', 'new')->count(),
            'qualified_leads' => Leads::where('status', 'qualified')->count(),
            'negotiating_leads' => Leads::where('status', 'negotiating')->count(),
            'won_leads' => Leads::where('status', 'won')->count(),
            'lost_leads' => Leads::where('status', 'lost')->count(),
            'assigned_leads' => Leads::whereNotNull('assigned_to')->count(),
            'unassigned_leads' => Leads::whereNull('assigned_to')->count(),
            'total_values' => Leads::sum('values'),
            'avg_lead_value' => Leads::avg('values'),
        ];

        return response()->json([
            'message' => 'Statistics retrieved successfully',
            'data' => $stats,
        ]);
    }
}
