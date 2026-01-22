<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use App\Models\Activity;
use Illuminate\Http\Request;

class ClientApiController extends Controller
{
    /**
     * Get all clients with filters and search
     */
    public function index(Request $request)
    {
        $query = Clients::query();

        // Filter by company
        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by name, email, phone
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $clients = $query->with(['company', 'invoices', 'proposals', 'projects'])
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'message' => 'Clients retrieved successfully',
            'data' => $clients,
        ]);
    }

    /**
     * Create a new client
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'nullable|exists:companies,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients',
            'phone' => 'nullable|regex:/^[0-9]{10}$/',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
        ]);

        $client = Clients::create($validated);

        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'entity_type' => 'Client',
            'entity_id' => $client->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'message' => 'Client created successfully',
            'data' => $client->load('company'),
        ], 201);
    }

    /**
     * Get a single client
     */
    public function show($id)
    {
        $client = Clients::with(['company', 'invoices', 'proposals', 'projects'])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Client retrieved successfully',
            'data' => $client,
        ]);
    }

    /**
     * Update a client
     */
    public function update(Request $request, $id)
    {
        $client = Clients::findOrFail($id);

        $validated = $request->validate([
            'company_id' => 'sometimes|exists:companies,id',
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:clients,email,' . $id,
            'phone' => 'sometimes|regex:/^[0-9]{10}$/',
            'address' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'country' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:10',
            'notes' => 'sometimes|string',
            'status' => 'sometimes|in:active,inactive,lead',
        ]);

        $changes = [];
        foreach ($validated as $key => $value) {
            if ($client->$key !== $value) {
                $changes[$key] = ['old' => $client->$key, 'new' => $value];
            }
        }

        $client->update($validated);

        if (!empty($changes)) {
            Activity::create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'entity_type' => 'Client',
                'entity_id' => $id,
                'changes' => $changes,
                'ip_address' => request()->ip(),
            ]);
        }

        return response()->json([
            'message' => 'Client updated successfully',
            'data' => $client->load('company'),
        ]);
    }

    /**
     * Delete a client
     */
    public function destroy($id)
    {
        $client = Clients::findOrFail($id);

        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'entity_type' => 'Client',
            'entity_id' => $id,
            'ip_address' => request()->ip(),
        ]);

        $client->delete();

        return response()->json([
            'message' => 'Client deleted successfully',
        ]);
    }

    /**
     * Get client statistics
     */
    public function statistics()
    {
        $stats = [
            'total_clients' => Clients::count(),
            'active_clients' => Clients::where('status', 'active')->count(),
            'inactive_clients' => Clients::where('status', 'inactive')->count(),
            'leads' => Clients::where('status', 'lead')->count(),
            'total_invoice_value' => Clients::with('invoices')->get()
                ->sum(function ($client) {
                    return $client->invoices->sum('total');
                }),
            'avg_client_value' => Clients::count() > 0 
                ? Clients::with('invoices')->get()
                    ->sum(function ($client) {
                        return $client->invoices->sum('total');
                    }) / Clients::count()
                : 0,
        ];

        return response()->json([
            'message' => 'Client statistics retrieved',
            'data' => $stats,
        ]);
    }

    /**
     * Get client with complete history (invoices, proposals, projects)
     */
    public function history($id)
    {
        $client = Clients::findOrFail($id);

        return response()->json([
            'message' => 'Client history retrieved',
            'data' => [
                'client' => $client,
                'invoices' => $client->invoices()->latest()->get(),
                'proposals' => $client->proposals()->latest()->get(),
                'projects' => $client->projects()->latest()->get(),
            ],
        ]);
    }
}
