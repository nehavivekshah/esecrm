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

                    {{-- Project Filter --}}
                    <div class="tk-project-filter-wrap">
                        <form method="GET" action="/task" id="projectFilterForm">
                            <div class="tk-project-filter-box">
                                <i class="bx bx-briefcase-alt-2 tk-filter-icon"></i>
                                <select name="project_id" id="projectFilterSelect" class="tk-project-filter-select"
                                        onchange="document.getElementById('projectFilterForm').submit()">
                                    <option value="">All Projects</option>
                                    @foreach($projects as $proj)
                                        <option value="{{ $proj->id }}" {{ $activeProjectId == $proj->id ? 'selected' : '' }}>
                                            {{ $proj->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>

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

            {{-- Active project badge --}}
            @if($activeProjectId)
                @php $activeProject = $projects->firstWhere('id', $activeProjectId); @endphp
                @if($activeProject)
                    <div class="tk-active-filter-bar mb-3">
                        <i class="bx bx-filter-alt"></i>
                        Filtered by project: <strong>{{ $activeProject->name }}</strong>
                        <a href="/task" class="tk-clear-filter" title="Clear filter">
                            <i class="bx bx-x"></i> Clear
                        </a>
                    </div>
                @endif
            @endif

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
                                    $taskAssignees = $task->assignees ?? collect();
                                @endphp

                                <a href="javascript:void(0)" onclick="openTaskAjax(event, '{{ $task->id }}')"
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

                                    {{-- Project & Subtask Info --}}
                                    @if($task->project || $task->parent)
                                        <div class="tk-card-relation mt-2 d-flex flex-wrap gap-1">
                                            @if($task->project)
                                                <span class="pv-badge pv-badge-info" style="font-size: 0.65rem; padding: 1px 6px;">
                                                    <i class="bx bx-briefcase-alt-2"></i> {{ $task->project->name }}
                                                </span>
                                            @endif
                                            @if($task->parent)
                                                <span class="pv-badge pv-badge-warn" style="font-size: 0.65rem; padding: 1px 6px;">
                                                    <i class="bx bx-subdirectory-right"></i> Subtask
                                                </span>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Multi-assignee avatar chips on card --}}
                                    @if($taskAssignees->count() > 1)
                                        <div class="tk-assignees-row mt-2">
                                            @foreach($taskAssignees->take(4) as $assignee)
                                                <div class="tk-assignee-chip" title="{{ $assignee->name }}">
                                                    {{ strtoupper(substr($assignee->name, 0, 1)) }}
                                                </div>
                                            @endforeach
                                            @if($taskAssignees->count() > 4)
                                                <div class="tk-assignee-chip tk-assignee-more">+{{ $taskAssignees->count() - 4 }}</div>
                                            @endif
                                        </div>
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

                        {{-- Quick Add Form --}}
                        @if($canAddTask)
                            <div class="tk-quick-add task-form" id="tf{{ $uid }}" style="display:none;">
                                <form action="{{ route('task') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="uid" value="{{ $uid }}" />
                                    <input type="hidden" name="cid" value="{{ $column['user']->cid }}" />
                                    <input type="hidden" name="parent_id" value="{{ request('parent_id') }}" />

                                    {{-- Task title --}}
                                    <textarea name="msg" id="tx{{ $uid }}" class="tk-quick-textarea"
                                              placeholder="Task title…" required rows="2"></textarea>

                                    {{-- Project selector --}}
                                    <div class="mt-2">
                                        <select name="project_id" class="tk-quick-select">
                                            <option value="">— No Project —</option>
                                            @foreach($projects as $proj)
                                                <option value="{{ $proj->id }}"
                                                    {{ $activeProjectId == $proj->id ? 'selected' : '' }}>
                                                    {{ $proj->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Multi-user assignee --}}
                                    <div class="mt-2">
                                        <div class="tk-quick-label"><i class="bx bx-user-plus"></i> Also assign to:</div>
                                        <div class="tk-assignee-checkboxes">
                                            @foreach($users as $u)
                                                @if($u->id != $uid)
                                                    <label class="tk-assignee-check-item">
                                                        <input type="checkbox" name="assignee_ids[]" value="{{ $u->id }}" />
                                                        <span class="tk-check-avatar">{{ strtoupper(substr($u->name, 0, 1)) }}</span>
                                                        <span class="tk-check-name">{{ $u->name }}</span>
                                                    </label>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

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

    <div id="taskAjaxContainer"></div>

    <script>
        // Open task modal via AJAX
        function openTaskAjax(event, taskId) {
            if(event) event.preventDefault();

            document.getElementById('taskAjaxContainer').innerHTML = '<div class="et-backdrop"></div><div class="offcanvas offcanvas-end show" tabindex="-1"><div class="offcanvas-header"><h5 class="offcanvas-title">Loading...</h5></div></div>';

            fetch('/task-details/' + taskId)
                .then(response => response.text())
                .then(html => {
                    const container = document.getElementById('taskAjaxContainer');
                    if (window.jQuery) {
                        $('#taskAjaxContainer').html(html);
                    } else {
                        container.innerHTML = html;
                        Array.from(container.querySelectorAll('script')).forEach(oldScript => {
                            const newScript = document.createElement('script');
                            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                            newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                            oldScript.parentNode.replaceChild(newScript, oldScript);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching task details:', error);
                    document.getElementById('taskAjaxContainer').innerHTML = '';
                    alert('Could not load task details.');
                });
        }

        // Close task modal
        function closeTaskAjax() {
            document.getElementById('taskAjaxContainer').innerHTML = '';
            if(window.location.search.includes('id=')) {
                window.location = '{{ route("task") }}';
            }
        }

        // Handle URL parameters for auto-actions
        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);

            const taskId = urlParams.get('id');
            if (taskId && !document.querySelector('.offcanvas.show')) {
                openTaskAjax(null, taskId);
            }

            if (urlParams.get('action') === 'add') {
                if (typeof addtask === 'function') {
                    addtask({{ $uid ?? 'null' }});
                    setTimeout(function() {
                        const ta = document.getElementById('tx{{ $uid ?? "" }}');
                        if (ta) ta.focus();
                    }, 100);
                }
            }

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
