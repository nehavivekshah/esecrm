<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Task_comments;
use App\Models\Task_working_hours;
use App\Models\Activity;
use Illuminate\Http\Request;

class TaskApiController extends Controller
{
    /**
     * Get all tasks with filters
     */
    public function index(Request $request)
    {
        $query = Task::query();

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Get my tasks
        if ($request->has('my_tasks') && $request->my_tasks) {
            $query->where('assigned_to', auth()->id());
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        $tasks = $query->with(['project', 'assignee', 'comments', 'workingHours'])
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'message' => 'Tasks retrieved successfully',
            'data' => $tasks,
        ]);
    }

    /**
     * Create a new task
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after:start_date',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'status' => 'nullable|in:todo,in_progress,completed,on_hold',
        ]);

        $task = Task::create($validated);

        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'entity_type' => 'Task',
            'entity_id' => $task->id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'message' => 'Task created successfully',
            'data' => $task->load('project', 'assignee'),
        ], 201);
    }

    /**
     * Get a single task
     */
    public function show($id)
    {
        $task = Task::with(['project', 'assignee', 'comments.user', 'workingHours'])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Task retrieved successfully',
            'data' => $task,
        ]);
    }

    /**
     * Update a task
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'assigned_to' => 'sometimes|exists:users,id',
            'due_date' => 'sometimes|date',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'status' => 'sometimes|in:todo,in_progress,completed,on_hold',
            'progress' => 'sometimes|integer|min:0|max:100',
        ]);

        $task->update($validated);

        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'entity_type' => 'Task',
            'entity_id' => $id,
            'ip_address' => request()->ip(),
        ]);

        return response()->json([
            'message' => 'Task updated successfully',
            'data' => $task->load('project', 'assignee'),
        ]);
    }

    /**
     * Delete a task
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);

        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'entity_type' => 'Task',
            'entity_id' => $id,
            'ip_address' => request()->ip(),
        ]);

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }

    /**
     * Add comment to task
     */
    public function addComment(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        $comment = Task_comments::create([
            'task_id' => $id,
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
        ]);

        return response()->json([
            'message' => 'Comment added successfully',
            'data' => $comment->load('user'),
        ], 201);
    }

    /**
     * Log working hours
     */
    public function logHours(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.5|max:24',
            'notes' => 'nullable|string',
        ]);

        $workingHour = Task_working_hours::create(array_merge($validated, [
            'task_id' => $id,
            'user_id' => auth()->id(),
        ]));

        return response()->json([
            'message' => 'Working hours logged successfully',
            'data' => $workingHour,
        ], 201);
    }

    /**
     * Get task statistics
     */
    public function statistics(Request $request)
    {
        $query = Task::query();

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $stats = [
            'total_tasks' => $query->count(),
            'todo_tasks' => (clone $query)->where('status', 'todo')->count(),
            'in_progress_tasks' => (clone $query)->where('status', 'in_progress')->count(),
            'completed_tasks' => (clone $query)->where('status', 'completed')->count(),
            'on_hold_tasks' => (clone $query)->where('status', 'on_hold')->count(),
            'urgent_tasks' => (clone $query)->where('priority', 'urgent')->count(),
            'overdue_tasks' => (clone $query)->where('due_date', '<', now())->count(),
            'avg_progress' => $query->count() > 0 ? (clone $query)->avg('progress') : 0,
            'total_hours_logged' => Task_working_hours::whereIn('task_id', (clone $query)->pluck('id'))->sum('hours'),
        ];

        return response()->json([
            'message' => 'Task statistics retrieved',
            'data' => $stats,
        ]);
    }
}
