<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendances;
use App\Models\Activity;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TeamApiController extends Controller
{
    /**
     * Get all team members
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->has('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->has('active')) {
            $query->where('active', $request->active == 'true');
        }

        $users = $query->with(['role', 'company', 'assignedLeads', 'assignedTasks'])
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'message' => 'Team members retrieved successfully',
            'data' => $users,
        ]);
    }

    /**
     * Get team member details
     */
    public function show($id)
    {
        $user = User::with(['role', 'company', 'assignedLeads', 'assignedTasks', 'attendances'])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Team member retrieved successfully',
            'data' => $user,
        ]);
    }

    /**
     * Update team member
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'mob' => 'sometimes|unique:users,mob,' . $id . '|regex:/^[0-9]{10}$/',
            'role_id' => 'sometimes|exists:roles,id',
            'company_id' => 'sometimes|exists:companies,id',
            'active' => 'sometimes|boolean',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Team member updated successfully',
            'data' => $user->load('role', 'company'),
        ]);
    }

    /**
     * Get team performance metrics
     */
    public function performance(Request $request)
    {
        $company_id = $request->company_id;
        $query = User::query();

        if ($company_id) {
            $query->where('company_id', $company_id);
        }

        $users = $query->with(['assignedLeads', 'assignedTasks'])->get();

        $performance = $users->map(function ($user) {
            $leads = $user->assignedLeads;
            $tasks = $user->assignedTasks;

            return [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'email' => $user->email,
                'assigned_leads' => $leads->count(),
                'won_leads' => $leads->where('status', 'won')->count(),
                'active_tasks' => $tasks->where('status', 'in_progress')->count(),
                'completed_tasks' => $tasks->where('status', 'completed')->count(),
                'overdue_tasks' => $tasks->where('due_date', '<', now())
                    ->where('status', '!=', 'completed')->count(),
                'lead_conversion' => $leads->count() > 0 
                    ? round(($leads->where('status', 'won')->count() / $leads->count()) * 100, 2)
                    : 0,
                'task_completion' => $tasks->count() > 0
                    ? round(($tasks->where('status', 'completed')->count() / $tasks->count()) * 100, 2)
                    : 0,
            ];
        });

        return response()->json([
            'message' => 'Team performance metrics retrieved',
            'data' => $performance,
        ]);
    }

    /**
     * Get team attendance
     */
    public function attendance(Request $request)
    {
        $query = Attendances::query();

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date')) {
            $query->whereDate('attendance_date', $request->date);
        }

        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('attendance_date', [
                $request->from_date,
                $request->to_date,
            ]);
        }

        $attendances = $query->with('user')->latest()->paginate($request->per_page ?? 30);

        return response()->json([
            'message' => 'Attendance records retrieved',
            'data' => $attendances,
        ]);
    }

    /**
     * Check in employee
     */
    public function checkIn(Request $request)
    {
        $today = now()->toDateString();
        
        // Check if already checked in today
        $existing = Attendances::where('user_id', auth()->id())
            ->whereDate('attendance_date', $today)
            ->first();

        if ($existing && $existing->check_in) {
            return response()->json([
                'message' => 'Already checked in today',
                'data' => $existing,
            ], 409);
        }

        $attendance = Attendances::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'attendance_date' => $today,
            ],
            [
                'check_in' => now(),
                'status' => 'present',
            ]
        );

        return response()->json([
            'message' => 'Check-in successful',
            'data' => $attendance,
        ]);
    }

    /**
     * Check out employee
     */
    public function checkOut(Request $request)
    {
        $today = now()->toDateString();

        $attendance = Attendances::where('user_id', auth()->id())
            ->whereDate('attendance_date', $today)
            ->firstOrFail();

        $attendance->update(['check_out' => now()]);

        return response()->json([
            'message' => 'Check-out successful',
            'data' => $attendance,
        ]);
    }

    /**
     * Mark attendance manually
     */
    public function markAttendance(Request $request, $user_id)
    {
        $validated = $request->validate([
            'attendance_date' => 'required|date',
            'status' => 'required|in:present,absent,half-day,leave',
            'notes' => 'nullable|string',
        ]);

        $attendance = Attendances::updateOrCreate(
            [
                'user_id' => $user_id,
                'attendance_date' => $validated['attendance_date'],
            ],
            [
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Attendance marked successfully',
            'data' => $attendance,
        ]);
    }

    /**
     * Get attendance summary
     */
    public function attendanceSummary(Request $request)
    {
        $from_date = $request->from_date ?? now()->startOfMonth();
        $to_date = $request->to_date ?? now()->endOfMonth();

        $query = Attendances::whereBetween('attendance_date', [$from_date, $to_date]);

        if ($request->has('company_id')) {
            $company_id = $request->company_id;
            $query->whereIn('user_id', User::where('company_id', $company_id)->pluck('id'));
        }

        $summary = [
            'total_days' => $query->count(),
            'present' => (clone $query)->where('status', 'present')->count(),
            'absent' => (clone $query)->where('status', 'absent')->count(),
            'half_day' => (clone $query)->where('status', 'half-day')->count(),
            'leave' => (clone $query)->where('status', 'leave')->count(),
            'presence_percentage' => $query->count() > 0
                ? round(((clone $query)->where('status', 'present')->count() / $query->count()) * 100, 2)
                : 0,
        ];

        return response()->json([
            'message' => 'Attendance summary retrieved',
            'data' => $summary,
        ]);
    }

    /**
     * Get team workload
     */
    public function workload(Request $request)
    {
        $company_id = $request->company_id;
        $query = User::query();

        if ($company_id) {
            $query->where('company_id', $company_id);
        }

        $workload = $query->with('assignedLeads')
            ->get()
            ->map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'assigned_leads' => $user->assignedLeads->count(),
                    'active_leads' => $user->assignedLeads->whereIn('status', ['new', 'qualified', 'negotiating'])->count(),
                    'avg_lead_value' => $user->assignedLeads->count() > 0
                        ? round($user->assignedLeads->sum('values') / $user->assignedLeads->count(), 2)
                        : 0,
                ];
            });

        return response()->json([
            'message' => 'Team workload retrieved',
            'data' => $workload,
        ]);
    }
}
