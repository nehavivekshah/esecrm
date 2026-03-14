<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\Task_working_hours;
use Illuminate\Support\Facades\Auth;

class TaskService
{
    /**
     * Get tasks grouped by user for the Kanban board.
     *
     * @return array
     */
    public function getKanbanData()
    {
        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));
        $canAddTask = in_array('tasks_add', $roleArray) || in_array('All', $roleArray);

        if ($roles->features == 'All') {
            $users = User::where('cid', '=', Auth::user()->cid)->orderBy('id', 'DESC')->get();
        } else {
            $users = User::where('id', '=', Auth::user()->id)->get();
            // Include assigned users
            $assignedIds = explode(',', ($users[0]->assign ?? ''));
            if (!empty(array_filter($assignedIds))) {
                $assignedUsers = User::whereIn('id', $assignedIds)->get();
                $users = $users->merge($assignedUsers);
            }
        }

        $kanbanData = [];

        foreach ($users as $user) {
            $tasks = Task::where('uid', '=', $user->id)->orderBy('position', 'asc')->get();
            
            // Enrich tasks with history/highlight info
            $enrichedTasks = $tasks->map(function ($task) {
                $taskHistory = Task_working_hours::where('taskid', '=', $task->id)->get();
                $task->is_highlighted = (!empty($taskHistory[0]->id) && $taskHistory[0]->status == '0');
                return $task;
            });

            $kanbanData[] = [
                'user' => $user,
                'tasks' => $enrichedTasks,
            ];
        }

        return [
            'kanbanData' => $kanbanData,
            'canAddTask' => $canAddTask,
            'users' => $users // Original users collection if needed
        ];
    }
}
