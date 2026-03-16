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
                    <form method="post" class="tb-search-wrap" autocomplete="off">
                        @csrf
                        <div class="tb-search-box">
                            <i class="bx bx-search tb-search-icon"></i>
                            <input type="text" id="taskSearch" name="taskSearch"
                                   placeholder="Search tasks…" class="tb-search-input" />
                        </div>
                        <div class="searchTaskResult">
                            <ul id="tsdata"></ul>
                        </div>
                    </form>
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
                                <button type="button" class="tk-add-btn" onclick="addtask({{ $uid }})" title="Add Task">
                                    <i class="bx bx-plus"></i>
                                </button>
                            @endif
                        </div>

                        {{-- Cards --}}
                        <div class="tk-cards eventblock connectedSortable" data-user="{{ $uid }}">
                            @foreach ($column['tasks'] as $task)
                                @php
                                    $statusColors = [
                                        '1' => ['#ea4335', 'Urgent',      'bg-danger'],
                                        '2' => ['#f29900', 'Pending',     'bg-warning'],
                                        '3' => ['#1a73e8', 'In Progress', 'bg-primary'],
                                        '4' => ['#34a853', 'Done',        'bg-success'],
                                        '5' => ['#006666', 'Closed',      'bg-secondary'],
                                    ];
                                    $sc = $statusColors[$task->status] ?? ['#aaa', 'Open', 'bg-light'];
                                    $displayTitle = strlen($task->title) > 50
                                        ? substr($task->title, 0, 50) . '…'
                                        : $task->title;
                                @endphp

                                <a href="{{ route('edit-task', ['id' => $task->id]) }}"
                                   class="tk-card {{ $task->is_highlighted ? 'tk-card-highlighted' : '' }}"
                                   draggable="true" data-taskid="{{ $task->id }}"
                                   style="border-left-color: {{ $sc[0] }};">

                                    {{-- Status pill --}}
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="tk-status-pill" style="background:{{ $sc[0] }}18; color:{{ $sc[0] }};">
                                            {{ $sc[1] }}
                                        </span>
                                        <div class="tk-card-action">
                                            @if($task->status == '0')
                                                <i class="bx bx-time" title="Start Timer"></i>
                                            @else
                                                <i class="bx bx-stopwatch" title="Running"></i>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Title --}}
                                    <div class="tk-card-title">{{ $displayTitle }}</div>

                                    {{-- Label dot --}}
                                    @if(!empty($task->label))
                                        <div class="tk-card-label-bar" style="background: {{ $task->label }};"></div>
                                    @endif
                                </a>
                            @endforeach
                        </div>

                        {{-- Quick Add Form --}}
                        @if($canAddTask)
                            <div class="tk-quick-add" id="qa-{{ $uid }}" style="display:none;">
                                <form action="{{ route('task') }}" method="post" id="tf{{ $uid }}">
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
                                                onclick="document.getElementById('qa-{{ $uid }}').style.display='none';">
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
        function addtask(uid) {
            const qa = document.getElementById('qa-' + uid);
            if (qa) {
                qa.style.display = qa.style.display === 'none' ? 'block' : 'none';
                const ta = document.getElementById('tx' + uid);
                if (ta) ta.focus();
            }
        }
    </script>

@endsection