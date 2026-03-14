@extends('layout')
@section('title','Tasks - eseCRM')

@section('content')
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i> 
            Task
            <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>
        <div class="container-fluid">
            <div class="board-title board-title-flex px-4">
                <h1>Kanban Board</h1>
                <form method="post" class="task-search">
                    @csrf
                    <input type="text" id="taskSearch" name="taskSearch" class="form-size" placeholder="Search Task.." />
                </form>
                <div class="searchTaskResult">
                    <ul id="tsdata"></ul>
                </div>
            </div>
            <div class="flex">
                <input type="hidden" id="userCount" value="{{ count($users) }}" />
                
                @foreach ($kanbanData as $column)
                    <div class="scrum-board backlog">
                        <h2>{{ $column['user']->name }}</h2>
                        <div class="scrum-board-column">
                            <div class="eventblock connectedSortable" data-user="{{ $column['user']->id }}">
                                
                                @foreach ($column['tasks'] as $task)
                                    @php
                                        if ($task->status == '1') { $borderColorClass = 'scrum-task-argent'; }
                                        elseif ($task->status == '2') { $borderColorClass = 'scrum-task-warning'; }
                                        elseif ($task->status == '3') { $borderColorClass = 'scrum-task-info'; }
                                        elseif ($task->status == '4') { $borderColorClass = 'scrum-task-success'; }
                                        elseif ($task->status == '5') { $borderColorClass = 'scrum-task-primary'; }
                                        else { $borderColorClass = 'scrum-task'; }

                                        $highlightClass = $task->is_highlighted ? 'task-highlighted' : '';
                                        $displayTitle = strlen($task->title) > 28 ? substr($task->title, 0, 28) . '...' : $task->title;
                                    @endphp

                                    <a href="{{ route('edit-task', ['id' => $task->id]) }}" 
                                       class="{{ $borderColorClass }} {{ $highlightClass }} overflow ui-state-default" 
                                       draggable="true" 
                                       data-taskid="{{ $task->id }}" 
                                       style="border-color:{{ $task->label }}">
                                        
                                        <div class="scrum-task-description">
                                            <p>{{ $displayTitle }}</p>
                                            <div class="scrum-edit">
                                                @if ($task->status == '0')
                                                    <i class="bx bx-time playicon" id="playicon" title="Stop"></i>
                                                @else
                                                    <i class="bx bx-stopwatch playicon" id="playicon" title="Start"></i>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                @endforeach

                            </div>
                            <div class="scrum-task-assignee">
                                <form action="{{ route('task') }}" method="post" class="task-form" id="tf{{ $column['user']->id }}" style="display:none;">
                                    @csrf
                                    <input type="hidden" name="uid" value="{{ $column['user']->id }}" />
                                    <input type="hidden" name="cid" value="{{ $column['user']->cid }}" />
                                    <textarea type="text" name="msg" class="form-contol" id="tx{{ $column['user']->id }}" placeholder="Enter a title for this card.." required></textarea>
                                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                    <button type="reset" class="btn btn-light btn-sm" id="cls{{ $column['user']->id }}">Reset</button>
                                </form>
                                @if($canAddTask)
                                    <a href="javascript:void(0)" onclick="addtask(this.id)" id="{{ $column['user']->id }}" class="nc">
                                        <i class="bx bx-plus" id="edit_task"></i> Add New Card
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    
    @if(isset($_GET['id']))
    
    @include('inc.task.popup')
    
    @endif
    
@endsection