<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Task;
use App\Models\Task_comments;
use App\Models\Task_working_hours;
use App\Models\Todo_lists;

class TaskController extends Controller
{
    public function task()
    {

        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));

        if (in_array('tasks_add', $roleArray) || in_array('All', $roleArray)) {
            $display = "";
        } else {
            $display = "style='display:none!important;'";
        }

        $output = '';

        if ($roles->features == 'All') {

            $users = User::where('cid', '=', Auth::user()->cid)->orderBy('id', 'DESC')->get();

            foreach ($users as $k => $user) {

                $output .= '<div class="scrum-board backlog">
                <h2>' . $user->name . '</h2>
                <div class="scrum-board-column">
                    <div class="eventblock connectedSortable" data-user="' . $user->id . '">';

                $tasks = Task::where('uid', '=', $user->id)->orderBy('position', 'asc')->get();

                foreach ($tasks as $task) {

                    $taskHistory = Task_working_hours::where('taskid', '=', $task->id)->get();

                    $output .= '<a href="' . route('edit-task', ['id' => $task->id]) . '" class="';
                    if ($task->status == '1') {
                        $output .= 'scrum-task-argent';
                    } elseif ($task->status == '2') {
                        $output .= 'scrum-task-warning';
                    } elseif ($task->status == '3') {
                        $output .= 'scrum-task-info';
                    } elseif ($task->status == '4') {
                        $output .= 'scrum-task-success';
                    } elseif ($task->status == '5') {
                        $output .= 'scrum-task-primary';
                    } else {
                        $output .= 'scrum-task';
                    }

                    if (!empty($taskHistory[0]->id) && $taskHistory[0]->status == '0') {

                        $output .= ' task-highlighted ';

                    }

                    $output .= ' overflow ui-state-default" draggable="true" data-taskid="' . $task->id . '" style="border-color:' . $task->label . '">
                            <div class="scrum-task-description">
                                <p>';
                    if (strlen($task->title) > 28) {
                        $output .= substr($task->title, 0, 28) . '...';
                    } else {
                        $output .= $task->title;
                    }
                    $output .= '</p>
                                <div class="scrum-edit">';
                    if ($task->status == '0') {
                        $output .= '<i class="bx bx-time playicon" id="playicon" title="Stop"></i>';
                    } else {
                        $output .= '<i class="bx bx-stopwatch playicon" id="playicon" title="Start"></i>';
                    }
                    $output .= '</div>
                            </div>
                        </a>';

                }

                $output .= '</div>
                        <div class="scrum-task-assignee">
                            <form action="' . route('task') . '" method="post" class="task-form" id="tf' . $user->id . '" style="display:none;">
                                <input type="hidden" name="_token" value="' . csrf_token() . '" autocomplete="off">
                                <input type="hidden" name="uid" value="' . $user->id . '" />
                                <input type="hidden" name="cid" value="' . $user->cid . '" />
                                <textarea type="text" name="msg" class="form-contol" id="tx' . $user->id . '" placeholder="Enter a title for this card.." required></textarea>
                                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                <button type="reset" class="btn btn-light btn-sm" id="cls' . $user->id . '">Reset</button>
                            </form>
                            <a href="javascript:void(0)" onclick="addtask(this.id)" id="' . $user->id . '" class="nc" ' . $display . '><i class="bx bx-plus" id="edit_task"></i> Add New Card</a>
                        </div>
                    </div>
                </div>';
            }

        } else {
            $users = User::where('id', '=', Auth::user()->id)->get();

            foreach ($users as $k => $user) {

                $output .= '<div class="scrum-board backlog">
                <h2>' . $user->name . '</h2>
                <div class="scrum-board-column">
                    <div class="eventblock connectedSortable" data-user="' . $user->id . '">';

                $tasks = Task::where('uid', '=', $user->id)->orderBy('position', 'asc')->get();

                foreach ($tasks as $task) {

                    $taskHistory = Task_working_hours::where('taskid', '=', $task->id)->get();

                    $output .= '<a href="' . route('edit-task', ['id' => $task->id]) . '" class="';
                    if ($task->status == '1') {
                        $output .= 'scrum-task-argent';
                    } elseif ($task->status == '2') {
                        $output .= 'scrum-task-warning';
                    } elseif ($task->status == '3') {
                        $output .= 'scrum-task-info';
                    } elseif ($task->status == '4') {
                        $output .= 'scrum-task-success';
                    } elseif ($task->status == '5') {
                        $output .= 'scrum-task-primary';
                    } else {
                        $output .= 'scrum-task';
                    }

                    if (!empty($taskHistory[0]->id) && $taskHistory[0]->status == '0') {

                        $output .= ' task-highlighted ';

                    }

                    $output .= ' overflow ui-state-default" draggable="true" data-taskid="' . $task->id . '" style="border-color:' . $task->label . '">
                            <div class="scrum-task-description">
                                <p>';
                    if (strlen($task->title) > 35) {
                        $output .= substr($task->title, 0, 30) . '...';
                    } else {
                        $output .= $task->title;
                    }
                    $output .= '</p>
                                <div class="scrum-edit">';
                    if ($task->status == '0') {
                        $output .= '<i class="bx bx-dots-horizontal playicon" id="playicon" title="Stop"></i>';
                    } else {
                        $output .= '<i class="bx bx-stopwatch playicon" id="playicon" title="Start"></i>';
                    }
                    $output .= '</div>
                            </div>
                        </a>';

                }

                $output .= '</div>
                        <div class="scrum-task-assignee">
                            <form action="' . route('task') . '" method="post" class="task-form" id="tf' . $user->id . '" style="display:none;">
                                <input type="hidden" name="_token" value="' . csrf_token() . '" autocomplete="off">
                                <input type="hidden" name="uid" value="' . $user->id . '" />
                                <input type="hidden" name="cid" value="' . $user->cid . '" />
                                <textarea type="text" name="msg" class="form-contol" id="tx' . $user->id . '" placeholder="Enter a title for this card.." required></textarea>
                                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                <button type="reset" class="btn btn-light btn-sm" id="cls' . $user->id . '">Reset</button>
                            </form>
                            <a href="javascript:void(0)" onclick="addtask(this.id)" id="' . $user->id . '" class="nc" ' . $display . '><i class="bx bx-plus" id="edit_task"></i> Add New Card</a>
                        </div>
                    </div>
                </div>';
            }

            $assigned = explode(',', ($users[0]->assign ?? ''));

            foreach ($assigned as $assign) {

                $users = User::where('id', '=', ($assign ?? ''))->get();

                foreach ($users as $k => $user) {

                    $output .= '<div class="scrum-board backlog">
                    <h2>' . $user->name . '</h2>
                    <div class="scrum-board-column">
                        <div class="eventblock connectedSortable" data-user="' . $user->id . '">';

                    $tasks = Task::where('uid', '=', $user->id)->orderBy('position', 'asc')->get();

                    foreach ($tasks as $task) {

                        $taskHistory = Task_working_hours::where('taskid', '=', $task->id)->get();

                        $output .= '<a href="' . route('edit-task', ['id' => $task->id]) . '" class="';
                        if ($task->status == '1') {
                            $output .= 'scrum-task-argent';
                        } elseif ($task->status == '2') {
                            $output .= 'scrum-task-warning';
                        } elseif ($task->status == '3') {
                            $output .= 'scrum-task-info';
                        } elseif ($task->status == '4') {
                            $output .= 'scrum-task-success';
                        } elseif ($task->status == '5') {
                            $output .= 'scrum-task-primary';
                        } else {
                            $output .= 'scrum-task';
                        }

                        if (!empty($taskHistory[0]->id) && $taskHistory[0]->status == '0') {

                            $output .= ' task-highlighted ';

                        }

                        $output .= ' overflow ui-state-default" draggable="true" data-taskid="' . $task->id . '" style="border-color:' . $task->label . '">
                                <div class="scrum-task-description">
                                    <p>';
                        if (strlen($task->title) > 35) {
                            $output .= substr($task->title, 0, 30) . '...';
                        } else {
                            $output .= $task->title;
                        }
                        $output .= '</p>
                                    <div class="scrum-edit">';
                        if ($task->status == '0') {
                            $output .= '<i class="bx bx-dots-horizontal playicon" id="playicon" title="Stop"></i>';
                        } else {
                            $output .= '<i class="bx bx-stopwatch playicon" id="playicon" title="Start"></i>';
                        }
                        $output .= '</div>
                                </div>
                            </a>';

                    }

                    $output .= '</div>
                            <div class="scrum-task-assignee">
                                <form action="' . route('task') . '" method="post" class="task-form" id="tf' . $user->id . '" style="display:none;">
                                    <input type="hidden" name="_token" value="' . csrf_token() . '" autocomplete="off">
                                    <input type="hidden" name="uid" value="' . $user->id . '" />
                                    <input type="hidden" name="cid" value="' . $user->cid . '" />
                                    <textarea type="text" name="msg" class="form-contol" id="tx' . $user->id . '" placeholder="Enter a title for this card.." required></textarea>
                                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                    <button type="reset" class="btn btn-light btn-sm" id="cls' . $user->id . '">Reset</button>
                                </form>
                                <a href="javascript:void(0)" onclick="addtask(this.id)" id="' . $user->id . '" class="nc" ' . $display . '><i class="bx bx-plus" id="edit_task"></i> Add New Card</a>
                            </div>
                        </div>
                    </div>';
                }
            }
        }

        return view('task', ['users' => $users], ['tasks' => $tasks, 'output' => $output]);

    }

    public function taskPost(Request $request)
    {

        $tasklist = Task::where('cid', '=', Auth::user()->cid)
            ->where('uid', '=', $request->uid)->orderBy('position', 'asc')->get();

        $task = new Task();

        $task->cid = $request->cid;
        $task->uid = $request->uid;
        $task->title = $request->msg;
        $task->des = $request->msg;
        $task->label = '5';
        $task->whr = '0';
        $task->position = '0';
        $task->status = '6';

        foreach ($tasklist as $k => $singletask):

            $tasks = Task::find($singletask->id);

            $tasks->position = $k + 1;

            $tasks->updated_at = Now();

            $tasks->update();

        endforeach;

        $task->save();

        return back()->with('success', 'New Task Added');

        return back()->with('error', 'Oops, Somethings went worng.');

    }

    public function taskEdit(Request $request)
    {

        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));

        if (in_array('tasks_add', $roleArray) || in_array('All', $roleArray)) {
            $display = "";
        } else {
            $display = "style='display:none!important;'";
        }

        $output = '';

        if ($roles->features == 'All') {
            $users = User::where('cid', '=', Auth::user()->cid)->where('role', '!=', Auth::user()->role)->get();
            $tasks = Task::where('cid', '=', Auth::user()->cid)->get();

            foreach ($users as $k => $user) {
                $output .= '<div class="scrum-board backlog">
                <h2>' . $user->name . '</h2>
                <div class="scrum-board-column">
                    <div class="eventblock connectedSortable" data-user="' . $user->id . '"><!--  id="col{{ $k }}" ondrop="drop(event)" ondragover="dragover(event)"-->';
                foreach ($tasks as $task) {

                    if ($task->uid == $user->id) {

                        $taskHistory = Task_working_hours::where('taskid', '=', $task->id)->get();

                        $output .= '<a href="' . route('edit-task', ['id' => $task->id]) . '" class="';
                        if ($task->status == '1') {
                            $output .= 'scrum-task-argent';
                        } elseif ($task->status == '2') {
                            $output .= 'scrum-task-warning';
                        } elseif ($task->status == '3') {
                            $output .= 'scrum-task-info';
                        } elseif ($task->status == '4') {
                            $output .= 'scrum-task-success';
                        } elseif ($task->status == '5') {
                            $output .= 'scrum-task-primary';
                        } else {
                            $output .= 'scrum-task';
                        }

                        if (!empty($taskHistory[0]->id) && $taskHistory[0]->status == '0') {

                            $output .= ' task-highlighted ';

                        }

                        $output .= ' overflow ui-state-default" draggable="true" data-taskid="' . $task->id . '" style="border-color:' . $task->label . '"><!--ondragstart="dragstart(event)"-->
                                    <!-- onclick="tskedit()" id="{{ $task->id; }}"-->
                                    <div class="scrum-task-description">
                                        <p>';
                        if (strlen($task->title) > 35) {
                            $output .= substr($task->title, 0, 30) . '...';
                        } else {
                            $output .= $task->title;
                        }
                        $output .= '</p>
                                        <div class="scrum-edit">';
                        if ($task->status == '0') {
                            $output .= '<i class="bx bx-dots-horizontal playicon" id="playicon" title="Stop"></i>';
                        } else {
                            $output .= '<i class="bx bx-stopwatch playicon" id="playicon" title="Start"></i>';
                        }
                        $output .= '</div>
                                    </div>
                                </a>';

                    }

                }
                $output .= '</div>
                        <div class="scrum-task-assignee">
                            <form action="' . route('task') . '" method="post" class="task-form" id="tf' . $user->id . '" style="display:none;">
                                <input type="hidden" name="_token" value="' . csrf_token() . '" autocomplete="off">
                                <input type="hidden" name="uid" value="' . $user->id . '" />
                                <input type="hidden" name="cid" value="' . $user->cid . '" />
                                <textarea type="text" name="msg" class="form-contol" id="tx' . $user->id . '" placeholder="Enter a title for this card.." required></textarea>
                                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                <button type="reset" class="btn btn-light btn-sm" id="cls' . $user->id . '">Reset</button>
                            </form>
                            <a href="javascript:void(0)" onclick="addtask(this.id)" id="' . $user->id . '" class="nc" ' . $display . '><i class="bx bx-plus" id="edit_task"></i> Add New Card</a>
                        </div>
                    </div>
                </div>';
            }

        } else {
            $users = User::where('cid', '=', Auth::user()->cid)->where('id', '=', Auth::user()->id)->get();
            $tasks = Task::where('cid', '=', Auth::user()->cid)
                ->where('uid', '=', Auth::user()->id)->get();

            foreach ($users as $k => $user) {
                $output .= '<div class="scrum-board backlog">
                <h2>' . $user->name . '</h2>
                <div class="scrum-board-column">
                    <div class="eventblock connectedSortable" data-user="' . $user->id . '"><!--  id="col{{ $k }}" ondrop="drop(event)" ondragover="dragover(event)"-->';
                foreach ($tasks as $task) {

                    if ($task->uid == $user->id) {

                        $taskHistory = Task_working_hours::where('taskid', '=', $task->id)->get();

                        $output .= '<a href="' . route('edit-task', ['id' => $task->id]) . '" class="';
                        if ($task->status == '1') {
                            $output .= 'scrum-task-argent';
                        } elseif ($task->status == '2') {
                            $output .= 'scrum-task-warning';
                        } elseif ($task->status == '3') {
                            $output .= 'scrum-task-info';
                        } elseif ($task->status == '4') {
                            $output .= 'scrum-task-success';
                        } elseif ($task->status == '5') {
                            $output .= 'scrum-task-primary';
                        } else {
                            $output .= 'scrum-task';
                        }

                        if (!empty($taskHistory[0]->id) && $taskHistory[0]->status == '0') {

                            $output .= ' task-highlighted ';

                        }

                        $output .= ' overflow ui-state-default" draggable="true" data-taskid="' . $task->id . '" style="border-color:' . $task->label . '"><!--ondragstart="dragstart(event)"-->
                                    <!-- onclick="tskedit()" id="{{ $task->id; }}"-->
                                    <div class="scrum-task-description">
                                        <p>';
                        if (strlen($task->title) > 35) {
                            $output .= substr($task->title, 0, 30) . '...';
                        } else {
                            $output .= $task->title;
                        }
                        $output .= '</p>
                                        <div class="scrum-edit">';
                        if ($task->status == '0') {
                            $output .= '<i class="bx bx-dots-horizontal playicon" id="playicon" title="Stop"></i>';
                        } else {
                            $output .= '<i class="bx bx-stopwatch playicon" id="playicon" title="Start"></i>';
                        }
                        $output .= '</div>
                                    </div>
                                </a>';

                    }

                }
                $output .= '</div>
                        <div class="scrum-task-assignee">
                            <form action="' . route('task') . '" method="post" class="task-form" id="tf' . $user->id . '" style="display:none;">
                                <input type="hidden" name="_token" value="' . csrf_token() . '" autocomplete="off">
                                <input type="hidden" name="uid" value="' . $user->id . '" />
                                <input type="hidden" name="cid" value="' . $user->cid . '" />
                                <textarea type="text" name="msg" class="form-contol" id="tx' . $user->id . '" placeholder="Enter a title for this card.." required></textarea>
                                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                <button type="reset" class="btn btn-light btn-sm" id="cls' . $user->id . '">Reset</button>
                            </form>
                            <a href="javascript:void(0)" onclick="addtask(this.id)" id="' . $user->id . '" class="nc" ' . $display . '><i class="bx bx-plus" id="edit_task"></i> Add New Card</a>
                        </div>
                    </div>
                </div>';
            }

        }

        $taskSingle = Task::where('id', '=', $request->id)->get();

        $userSingle = User::where('cid', '=', Auth::user()->cid)->where('id', '=', $taskSingle[0]->uid)->get();

        $taskHistory = Task_working_hours::where('taskid', '=', $request->id)
            ->orderBy('id', 'DESC')->get();

        $taskComments = Task_comments::leftJoin('users', 'users.id', '=', 'task_comments.uid')
            ->select('users.name', 'task_comments.*')
            ->where('task_comments.taskid', '=', $request->id)
            ->orderBy('task_comments.id', 'DESC')->get();

        return view('task', ['users' => $users, 'tasks' => $tasks, 'taskSingle' => $taskSingle, 'userSingle' => $userSingle, 'taskHistory' => $taskHistory, 'output' => $output, 'taskComments' => $taskComments]);

    }

    public function tasksubmit(Request $request)
    {

        if (!empty($request->deltaskid)) {

            $tasks = Task::find($request->deltaskid);

            $tasks->delete();

            return response(['success' => 'Deleted']);

        } else if (!empty($request->userId)) {

            if (!empty($request->updatedPositions)) {
                // Loop through the updated positions and update them in the database
                foreach ($request->updatedPositions as $taskData) {
                    $task = Task::find($taskData['taskId']);
                    if ($task) {
                        $task->uid = $request->userId;
                        $task->position = $taskData['position'];
                        $task->update();
                    }
                }
                return response(['success' => 'Positions updated successfully']);
            }

            return response(['error' => 'No data provided']);

        } else if (!empty($request->tskId)) {

            $tasks = Task::find($request->tskId);

            $tasks->label = $request->label;

            $tasks->update();

            return response(['success' => 'Updated']);

        } else if (!empty($request->tskstartId)) {

            $taskHistory = Task_working_hours::where('id', $request->tskstartId)
                ->where('status', 0)
                ->first();

            if ($taskHistory) {
                $Task_working_hours = Task_working_hours::find($request->tskstartId);
                $Task_working_hours->end_time = Carbon::now()->format('d-m-Y h:i:s a');
                $Task_working_hours->hours = $request->tskhr;
                $Task_working_hours->status = '1';
                $Task_working_hours->update();

                $tid = $taskHistory->taskid ?? null;
                if ($tid) {
                    $tasks = Task::find($tid);
                    if ($tasks) {
                        $tasks->label = "#ff9800";
                        $tasks->status = '1';
                        $tasks->update();
                    }
                }

                return response(['success' => 'Updated']);
            } else {
                $task = new Task_working_hours();
                $task->taskid = $request->tskstartId;
                $task->start_time = Carbon::now()->format('d-m-Y h:i:s a');
                $task->end_time = Carbon::now()->format('d-m-Y h:i:s a');
                $task->hours = '0';
                $task->status = '0';
                $task->save();

                $tasks = Task::find($request->tskstartId);
                if ($tasks) {
                    $tasks->label = "#2196f3";
                    $tasks->status = '0';
                    $tasks->update();
                }

                return response(['success' => 'Inserted']);
            }

        } else if (!empty($request->commenttaskid)) {

            // $tasks = Task_comments::find($request->commenttaskid);
            // if(!empty($request->tasktitle)){
            //     $tasks->title = $request->tasktitle;
            // }else{
            //     $tasks->des = $request->taskdes;
            // }
            // $tasks->update();

            $task_comments = new Task_comments();

            $task_comments->uid = Auth::user()->id;
            $task_comments->taskid = $request->commenttaskid;
            $task_comments->comments = $request->taskcomment;

            $task_comments->save();

            $taskComments = Task_comments::leftJoin('users', 'users.id', '=', 'task_comments.uid')
                ->select('users.name', 'task_comments.*')
                ->where('task_comments.taskid', '=', $request->commenttaskid)
                ->orderBy('task_comments.id', 'DESC')->get();

            $messages = '';
            foreach ($taskComments as $taskComment) {
                if ($taskComment->uid == Auth::user()->id) {
                    $messages .= '<div class="row">
                    <div class="col-md-12">
                        <div class="primary-user">
                            <label class="small text-second">' . ($taskComment->name ?? '') . '</label><br>
                            <p>' . ($taskComment->comments ?? '') . '</p>
                            <span class="small text-light">' . ($taskComment->created_at ?? '') . '</span>
                        </div>
                    </div>
                </div>';
                } else {
                    $messages .= '<div class="row">
                    <div class="col-md-12">
                        <div class="sec-user">
                            <label class="small text-default">' . ($taskComment->name ?? '') . '</label><br>
                            <p>' . ($taskComment->comments ?? '') . '</p>
                            <span class="small text-dark">' . ($taskComment->created_at ?? '') . '</span>
                        </div>
                    </div>
                </div>';
                }
            }

            return response(['success' => 'Submitted', 'message' => $messages]);

        } else if (!empty($request->taskid)) {

            $tskId = $request->taskid ?? '';

            $tasks = Task::find($tskId);
            if (!empty($request->tasktitle)) {
                $tasks->title = $request->tasktitle;
            } else {
                $tasks->des = $request->taskdes;
            }
            $tasks->update();

            return response(['success' => 'Updated']);

        }
    }

    public function index()
    {
        $tasks = Todo_lists::where('uid', Auth::id())->orderBy('position', 'DESC')->get();
        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $task = new Todo_lists;
        $task->text = $request->text;
        $task->uid = Auth::id();
        $task->completed = $request->completed ? 1 : 0;
        $task->position = (Todo_lists::where('uid', Auth::id())->max('position') ?? 0) + 1;

        if ($request->has('reminder_at')) {
            $task->reminder_at = !empty($request->reminder_at) ? Carbon::parse($request->reminder_at) : null;
            $task->is_notified = 0; // Reset notification status
        }

        $task->save();

        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $task = Todo_lists::findOrFail($id);
        if ($request->has('text')) {
            $task->text = $request->text;
        }
        if ($request->has('completed')) {
            $task->completed = $request->completed ? 1 : 0;
        }

        if ($request->has('reminder_at')) {
            $task->reminder_at = !empty($request->reminder_at) ? Carbon::parse($request->reminder_at) : null;
            if ($task->reminder_at && $task->reminder_at > Carbon::now()) {
                $task->is_notified = 0; // Reset notification status if new future date
            }
        }

        $task->save();

        return response()->json($task);
    }

    public function reorder(Request $request)
    {
        $order = $request->order;
        $count = count($order);
        foreach ($order as $index => $id) {
            Todo_lists::where('id', $id)->update(['position' => $count - $index]);
        }

        return response()->json(['message' => 'Order updated']);
    }

    public function saveToken(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            \Log::info("Saving FCM token for user ID {$user->id}: " . substr($request->token, 0, 20) . "...");
            DB::table('users')->where('id', $user->id)->update(['fcm_token' => $request->token]);
            return response()->json([
                'message' => 'Token saved',
                'user_id' => $user->id,
                'token_prefix' => substr($request->token, 0, 10)
            ]);
        }
        \Log::warning("Token save attempted but no user is authenticated.");
        return response()->json(['message' => 'User not found'], 404);
    }

    public function destroy($id)
    {
        $task = Todo_lists::findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }

    public function clearAll()
    {
        Todo_lists::where('uid', Auth::id())->delete();
        return response()->json(['message' => 'All tasks cleared']);
    }

}
