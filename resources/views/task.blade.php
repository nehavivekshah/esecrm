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

                    @if($canAddTask)
                        <button type="button" class="lb-btn lb-btn-primary ms-3" onclick="addtask('{{ Auth::id() }}')">
                            <i class="bx bx-plus"></i> Add Task
                        </button>
                    @endif
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

                        {{-- Quick Add form has been moved to popup --}}
                    </div>
                @endforeach
            </div>

        </div>
    </section>

    @if(isset($_GET['id']))
        @include('inc.task.popup')
    @endif

    <div id="taskAjaxContainer"></div>

    {{-- Create Task Offcanvas --}}
    @if($canAddTask)
    <div class="offcanvas offcanvas-end" tabindex="-1" id="createTaskOffcanvas"
         style="width:820px; max-width:100vw; border-top-left-radius:16px; border-bottom-left-radius:16px; box-shadow:-12px 0 40px rgba(0,0,0,0.12); z-index:1061;">
        <div class="et-header">
            <div class="et-header-icon"><i class="bx bx-task"></i></div>
            <div class="flex-grow-1 min-w-0">
                <h5 class="mb-0" style="font-weight: 600; color: #202124;">Create New Task</h5>
            </div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <a href="javascript:void(0)" data-bs-dismiss="offcanvas" class="btn kb-action-btn" title="Close"
                    style="background:rgba(60,64,67,0.07);color:#5f6368;">
                    <i class="bx bx-x"></i>
                </a>
            </div>
        </div>
        <div class="offcanvas-body p-0" style="overflow-y:auto; background-color: #fafafa;">
            <form action="{{ route('task') }}" method="post" id="createTaskForm">
                @csrf
                <input type="hidden" name="parent_id" value="{{ request('parent_id') }}" />
                <div class="et-body">
                    {{-- LEFT SIDEBAR --}}
                    <div class="et-sidebar border-end">
                        <div class="et-section">
                            <div class="et-section-title"><i class="bx bxs-user"></i> Primary Assignee</div>
                            <select name="uid" id="createTaskUid" class="et-label-select w-100" required>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="et-section">
                            <div class="et-section-title"><i class="bx bx-group"></i> Also Assign To</div>
                            <div class="et-assignee-checkboxes mt-1" style="max-height: 200px;">
                                @foreach($users as $u)
                                    <label class="et-assignee-check-row">
                                        <input type="checkbox" name="assignee_ids[]" value="{{ $u->id }}" />
                                        <span class="et-chk-avatar">{{ strtoupper(substr($u->name, 0, 1)) }}</span>
                                        <span class="et-chk-name">{{ $u->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="et-section border-top pt-3">
                            <div class="et-section-title"><i class="bx bx-briefcase-alt-2"></i> Project</div>
                            <select name="project_id" class="et-label-select w-100">
                                <option value="">— No Project —</option>
                                @foreach($projects as $proj)
                                    <option value="{{ $proj->id }}" {{ $activeProjectId == $proj->id ? 'selected' : '' }}>
                                        {{ $proj->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="et-section">
                            <div class="et-section-title"><i class="bx bxs-label"></i> Label</div>
                            <div class="et-label-row">
                                <span class="et-label-dot" id="createLabelIcon" style="background:#787878;"></span>
                                <select name="label" id="createColorPalet" class="et-label-select">
                                    <option value="#787878">New Task</option>
                                    <option value="#007265">In Working</option>
                                    <option value="#ff9800">Pause</option>
                                    <option value="#e91e1e">Urgent</option>
                                    <option value="#0dd500">Complete</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    {{-- MAIN CONTENT --}}
                    <div class="et-main p-4">
                        <div class="form-group mb-3">
                            <label class="form-label text-muted fw-semibold small">Task Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control shadow-sm border-0" placeholder="Enter task title" required autofocus style="padding: 10px 14px; border-radius: 8px;">
                        </div>
                        <div class="form-group mb-4">
                            <label class="form-label text-muted fw-semibold small">Description</label>
                            <textarea name="des" class="form-control shadow-sm border-0" rows="8" placeholder="Add a more detailed description…" style="padding: 10px 14px; border-radius: 8px; resize: none;"></textarea>
                        </div>
                        <div class="d-flex align-items-center gap-2 pt-2 border-top">
                            <button type="submit" class="lb-btn lb-btn-primary" style="padding:8px 24px;">
                                <i class="bx bx-plus"></i> Create Task
                            </button>
                            <button type="button" class="lb-btn lb-btn-ghost" data-bs-dismiss="offcanvas">Cancel</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    <script>
        // Open Create Task Offcanvas explicitly overriding global addtask definition
        window.addtask = function(uid) {
            document.getElementById('createTaskUid').value = uid;
            var offcanvasEl = document.getElementById('createTaskOffcanvas');
            var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl) || new bootstrap.Offcanvas(offcanvasEl);
            offcanvas.show();
        };

        const createColorPalet = document.getElementById('createColorPalet');
        if (createColorPalet) {
            createColorPalet.addEventListener('change', function() {
                document.getElementById('createLabelIcon').style.background = this.value;
            });
        }

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
                    addtask('{{ Auth::id() }}');
                }
            }
        });
    </script>

@endsection
