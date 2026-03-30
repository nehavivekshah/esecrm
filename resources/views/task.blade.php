@extends('layout')
@section('title', 'CRM Follow-Up Tasks - eseCRM')

@section('content')
    <section class="task__section">
        @include('inc.header', ['title' => 'CRM Follow-Up Tasks'])

        <div class="dash-container">

            {{-- Toolbar --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <span class="lb-page-count">
                        <i class="bx bx-task"></i>
                        Task Board &mdash; <span id="memberCount">{{ count($users) }}</span> Members
                    </span>
                </div>
                <div class="leads-toolbar-right">
                    {{-- Search --}}
                    <div class="tb-search-wrap">
                        <form method="post" autocomplete="off" onsubmit="return false;">
                            @csrf
                            <div class="tb-search-box">
                                <i class="bx bx-search tb-search-icon"></i>
                                <input type="text" id="taskSearch" name="taskSearch"
                                       placeholder="Search tasks…" class="tb-search-input" />
                            </div>
                        </form>
                        {{-- Dropdown OUTSIDE the search-box so absolute positioning works --}}
                        <div class="searchTaskResult">
                            <ul id="tsdata"></ul>
                        </div>
                    </div>
                    {{-- Status Legend --}}
                    <div class="d-none d-md-flex align-items-center gap-2 ms-2">
                        <span class="tb-legend tb-legend-urgent">Urgent</span>
                        <span class="tb-legend tb-legend-pending">Pending</span>
                        <span class="tb-legend tb-legend-progress">In Progress</span>
                        <span class="tb-legend tb-legend-done">Done</span>
                        <span class="tb-legend tb-legend-closed">Closed</span>
                    </div>
                </div>
            </div>

            {{-- Kanban Board --}}
            <input type="hidden" id="userCount" value="{{ count($users) }}" />

            <div class="tk-board">
                @php
                    $colColors = [
                        '#1a73e8', '#9334e9', '#006666', '#f29900',
                        '#34a853', '#ea4335', '#0b8043', '#e52592'
                    ];
                @endphp

                @foreach ($kanbanData as $idx => $column)
                    @php
                        $accent  = $colColors[$idx % count($colColors)];
                        $bgAlpha = 'rgba(' . implode(',', sscanf(ltrim($accent,'#'), '%02x%02x%02x')) . ',0.07)';
                        $initial = strtoupper(substr($column['user']->name, 0, 1));
                        $uid     = $column['user']->id;
                    @endphp

                    <div class="tk-col" data-user="{{ $uid }}">
                        {{-- Column Header --}}
                        <div class="tk-col-header" style="border-bottom-color: {{ $accent }};">
                            <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                                <div class="tk-col-avatar" style="background:{{ $bgAlpha }}; color:{{ $accent }};">
                                    {{ $initial }}
                                </div>
                                <div class="min-w-0">
                                    <div class="tk-col-name">{{ $column['user']->name }}</div>
                                    <div class="tk-col-count">
                                        <span class="tk-count-badge" id="count-{{ $uid }}">{{ count($column['tasks']) }}</span> tasks
                                    </div>
                                </div>
                            </div>
                            @if($canAddTask)
                                <button type="button" class="tk-add-btn" onclick="addtask({{ $uid }})"
                                        data-uid="{{ $uid }}" title="Add Task">
                                    <i class="bx bx-plus"></i>
                                </button>
                            @endif
                        </div>

                        {{-- Cards --}}
                        <div class="tk-cards eventblock connectedSortable" data-user="{{ $uid }}">
                            @forelse ($column['tasks'] as $task)
                                @php
                                    $statusColors = [
                                        '1' => ['#ea4335', 'Urgent',      'bg-danger'],
                                        '2' => ['#f29900', 'Pending',     'bg-warning'],
                                        '3' => ['#1a73e8', 'In Progress', 'bg-primary'],
                                        '4' => ['#34a853', 'Done',        'bg-success'],
                                        '5' => ['#006666', 'Closed',      'bg-secondary'],
                                    ];
                                    $sc = $statusColors[$task->status] ?? ['#9aa0a6', 'Open', 'bg-light'];
                                    $displayTitle = strlen($task->title) > 55
                                        ? substr($task->title, 0, 55) . '…'
                                        : $task->title;
                                    $displayDesc  = (!empty($task->msg) && $task->msg !== $task->title)
                                        ? (strlen($task->msg) > 60 ? substr($task->msg, 0, 60) . '…' : $task->msg)
                                        : '';
                                    $whr = floatval($task->whr ?? 0);
                                @endphp

                                <a href="{{ route('edit-task', ['id' => $task->id]) }}"
                                   class="tk-card {{ $task->is_highlighted ? 'tk-card-highlighted' : '' }}"
                                   draggable="true" data-taskid="{{ $task->id }}"
                                   style="border-left-color: {{ $sc[0] }};">

                                    {{-- Top row: status pill + timer icon --}}
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="tk-status-pill" style="background:{{ $sc[0] }}18; color:{{ $sc[0] }};">
                                            {{ $sc[1] }}
                                        </span>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="tk-card-action">
                                                @if($task->status == '0')
                                                    <i class="bx bx-time" title="Start Timer"></i>
                                                @else
                                                    <i class="bx bx-stopwatch" title="Running"></i>
                                                @endif
                                            </div>
                                            <i class="bx bx-dots-vertical-rounded tk-drag-handle" title="Drag"></i>
                                        </div>
                                    </div>

                                    {{-- Title --}}
                                    <div class="tk-card-title">{{ $displayTitle }}</div>

                                    {{-- Description preview --}}
                                    @if($displayDesc)
                                        <div class="tk-card-desc">{{ $displayDesc }}</div>
                                    @endif

                                    {{-- Footer: hours worked & attachments --}}
                                    @if($whr > 0 || !empty($task->label) || ($task->attachment_count ?? 0) > 0)
                                        <div class="tk-card-footer d-flex align-items-center justify-content-between mt-2 pt-2 border-top border-light">
                                            <div class="d-flex align-items-center gap-2">
                                                @if($whr > 0)
                                                    <span class="tk-card-hours text-muted" title="Hours worked" style="font-size: 0.75rem;">
                                                        <i class="bx bx-time-five"></i> {{ $whr }}h
                                                    </span>
                                                @endif
                                                @if(($task->attachment_count ?? 0) > 0)
                                                    <span class="text-muted d-flex align-items-center gap-1" title="{{ $task->attachment_count }} Attachments" style="font-size: 0.75rem;">
                                                        <i class="bx bx-paperclip"></i> {{ $task->attachment_count }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if(!empty($task->label))
                                                <span class="tk-card-label-dot shadow-sm" style="background:{{ $task->label }};" title="Label"></span>
                                            @endif
                                        </div>
                                    @endif
                                </a>

                            @empty
                                <div class="tk-empty-col">
                                    <i class="bx bx-clipboard"></i>
                                    <span>No tasks yet</span>
                                </div>
                            @endforelse
                        </div>

                        {{-- Quick Add Form — id="tf{uid}" matches global addtask() in script.js --}}
                        @if($canAddTask)
                            <div class="tk-quick-add task-form" id="tf{{ $uid }}" style="display:none;">
                                <form action="{{ route('task') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="uid" value="{{ $uid }}" />
                                    <input type="hidden" name="cid" value="{{ $column['user']->cid }}" />
                                    <textarea name="msg" id="tx{{ $uid }}" class="tk-quick-textarea"
                                              placeholder="Task title…" required rows="2"></textarea>
                                    <div class="d-flex gap-2 mt-2">
                                        <button type="submit" class="lb-btn lb-btn-primary" style="padding:4px 12px;font-size:0.78rem;">
                                            <i class="bx bx-check"></i> Add
                                        </button>
                                        <button type="button" class="lb-btn lb-btn-ghost" style="padding:4px 10px;font-size:0.78rem;"
                                                onclick="this.closest('.task-form').style.display='none';">
                                            <i class="bx bx-x"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

        </div>
    </section>

    @if(isset($_GET['id']))
        @include('inc.task.popup')
    @endif

    <script>
        // Focus textarea after addtask() unhides the panel
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.tk-add-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const uid = this.dataset.uid;
                    setTimeout(function () {
                        const ta = document.getElementById('tx' + uid);
                        if (ta) ta.focus();
                    }, 50);
                });
            });
        });
    </script>

@endsection