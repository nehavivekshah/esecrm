@extends('layout')
@section('title', 'CRM Follow-Up Tasks - eseCRM')

@section('content')
    <section class="task__section">
        @include('inc.header', ['title' => 'CRM Follow-Up Tasks'])

        <div class="dash-container">

            {{-- Toolbar --}}
            <div class="leads-toolbar tk-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <span class="lb-page-count">
                        <i class="bx bx-task"></i>
                        Task Board — <span id="memberCount">{{ count($users) }}</span> Members
                    </span>

                    {{-- Sort order toggle --}}
                    <div class="tk-sort-group ms-2">
                        <button class="tk-sort-btn active" data-sort="priority" title="Sort by Priority (Urgent first)">
                            <i class="bx bx-sort-up"></i> Priority
                        </button>
                        <button class="tk-sort-btn" data-sort="default" title="Default order">
                            <i class="bx bx-list-ol"></i> Default
                        </button>
                    </div>
                </div>
                <div class="leads-toolbar-right gap-2 flex-wrap">
                    {{-- Status Filter chips --}}
                    <div class="tk-filter-row">
                        <button class="tk-filter-btn active" data-filter="all">All</button>
                        <button class="tk-filter-btn" data-filter="1" style="--fc:#ea4335;">🔴 Urgent</button>
                        <button class="tk-filter-btn" data-filter="2" style="--fc:#f29900;">🟡 Pending</button>
                        <button class="tk-filter-btn" data-filter="3" style="--fc:#1a73e8;">🔵 In Progress</button>
                        <button class="tk-filter-btn" data-filter="4" style="--fc:#34a853;">🟢 Done</button>
                        <button class="tk-filter-btn" data-filter="5" style="--fc:#006666;">✅ Closed</button>
                    </div>

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
                </div>
            </div>

            {{-- Board --}}
            <input type="hidden" id="userCount" value="{{ count($users) }}" />

            @php
                $colColors = [
                    '#1a73e8','#9334e9','#006666','#f29900',
                    '#34a853','#ea4335','#0b8043','#e52592'
                ];

                // Priority order for sorting: lower = higher priority
                $statusPriority = [
                    '1' => 1, // Urgent
                    '3' => 2, // In Progress
                    '2' => 3, // Pending
                    '4' => 4, // Done
                    '5' => 5, // Closed
                    '0' => 6, // Open / default
                    '6' => 7, // Newly created
                ];

                $statusMeta = [
                    '1' => ['#ea4335', 'Urgent'],
                    '2' => ['#f29900', 'Pending'],
                    '3' => ['#1a73e8', 'In Progress'],
                    '4' => ['#34a853', 'Done'],
                    '5' => ['#006666', 'Closed'],
                ];
            @endphp

            <div class="tk-board" id="tk-board">
                @foreach ($kanbanData as $idx => $column)
                    @php
                        $accent  = $colColors[$idx % count($colColors)];
                        $rgb     = sscanf(ltrim($accent,'#'), '%02x%02x%02x');
                        $bgAlpha = 'rgba('.implode(',',$rgb).',0.08)';
                        $initial = strtoupper(substr($column['user']->name, 0, 1));
                        $uid     = $column['user']->id;

                        // Sort tasks by status priority
                        $sortedTasks = collect($column['tasks'])
                            ->sortBy(fn($t) => $statusPriority[$t->status] ?? 6)
                            ->values();

                        // Count per status for breakdown pills
                        $statusCounts = $sortedTasks->groupBy('status')->map->count();
                        $totalTasks   = $sortedTasks->count();
                    @endphp

                    <div class="tk-col" data-user="{{ $uid }}">

                        {{-- Column Header --}}
                        <div class="tk-col-header" style="border-bottom-color:{{ $accent }};">
                            <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                                <div class="tk-col-avatar" style="background:{{ $bgAlpha }};color:{{ $accent }};">
                                    {{ $initial }}
                                </div>
                                <div class="min-w-0 flex-grow-1">
                                    <div class="tk-col-name">{{ $column['user']->name }}</div>
                                    <div class="tk-col-count">
                                        <span class="tk-count-badge" id="count-{{ $uid }}">{{ $totalTasks }}</span> tasks
                                    </div>
                                </div>
                            </div>
                            @if($canAddTask)
                                <button type="button" class="tk-add-btn" onclick="addtask({{ $uid }})" title="Add Task">
                                    <i class="bx bx-plus"></i>
                                </button>
                            @endif
                        </div>

                        {{-- Status Breakdown Bar --}}
                        @if($totalTasks > 0)
                            <div class="tk-breakdown">
                                @foreach($statusMeta as $st => [$color, $label])
                                    @if(($statusCounts[$st] ?? 0) > 0)
                                        <span class="tk-breakdown-pill" style="background:{{ $color }}18;color:{{ $color }};" title="{{ $label }}">
                                            {{ $statusCounts[$st] }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        {{-- Cards --}}
                        <div class="tk-cards eventblock connectedSortable" data-user="{{ $uid }}">
                            @forelse ($sortedTasks as $task)
                                @php
                                    $sc = $statusMeta[$task->status] ?? ['#9aa0a6', 'Open'];
                                    $displayTitle = strlen($task->title) > 55
                                        ? substr($task->title, 0, 55).'…'
                                        : $task->title;
                                    $displayDesc = (!empty($task->msg) && $task->msg !== $task->title)
                                        ? (strlen($task->msg) > 70 ? substr($task->msg, 0, 70).'…' : $task->msg)
                                        : '';
                                    $whr = floatval($task->whr ?? 0);
                                @endphp

                                <a href="{{ route('edit-task', ['id' => $task->id]) }}"
                                   class="tk-card"
                                   draggable="true"
                                   data-taskid="{{ $task->id }}"
                                   data-status="{{ $task->status }}"
                                   data-title="{{ strtolower($task->title) }}"
                                   style="border-left-color:{{ $sc[0] }};{{ $task->is_highlighted ? 'background:#fffde7;' : '' }}">

                                    {{-- Top: status pill + icons --}}
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="tk-status-pill" style="background:{{ $sc[0] }}18;color:{{ $sc[0] }};">
                                            {{ $sc[1] }}
                                        </span>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="tk-card-action">
                                                @if($task->status == '0')
                                                    <i class="bx bx-time" title="Start Timer"></i>
                                                @else
                                                    <i class="bx bx-stopwatch" title="Running"></i>
                                                @endif
                                            </span>
                                            <i class="bx bx-dots-vertical-rounded tk-drag-handle" title="Drag to reorder"></i>
                                        </div>
                                    </div>

                                    {{-- Title --}}
                                    <div class="tk-card-title">{{ $displayTitle }}</div>

                                    {{-- Description --}}
                                    @if($displayDesc)
                                        <div class="tk-card-desc">{{ $displayDesc }}</div>
                                    @endif

                                    {{-- Footer --}}
                                    @if($whr > 0 || !empty($task->label))
                                        <div class="tk-card-footer">
                                            @if($whr > 0)
                                                <span class="tk-card-hours">
                                                    <i class="bx bx-time-five"></i> {{ $whr }}h
                                                </span>
                                            @else
                                                <span></span>
                                            @endif
                                            @if(!empty($task->label))
                                                <span class="tk-card-label-dot" style="background:{{ $task->label }};"></span>
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

                        {{-- Quick Add --}}
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
        /* ── Quick-add toggle ── */
        function addtask(uid) {
            const qa = document.getElementById('qa-' + uid);
            if (qa) {
                qa.style.display = qa.style.display === 'none' ? 'block' : 'none';
                const ta = document.getElementById('tx' + uid);
                if (ta) ta.focus();
            }
        }

        /* ── Status filter ── */
        document.querySelectorAll('.tk-filter-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.tk-filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const filter = this.dataset.filter;
                document.querySelectorAll('.tk-card').forEach(card => {
                    if (filter === 'all' || card.dataset.status === filter) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Show/hide empty state per column
                document.querySelectorAll('.tk-cards').forEach(col => {
                    const visible = [...col.querySelectorAll('.tk-card')].filter(c => c.style.display !== 'none');
                    let empty = col.querySelector('.tk-empty-col');
                    if (!empty) {
                        empty = document.createElement('div');
                        empty.className = 'tk-empty-col tk-empty-filter';
                        empty.innerHTML = '<i class="bx bx-filter-alt"></i><span>No matching tasks</span>';
                        col.appendChild(empty);
                    }
                    empty.style.display = visible.length === 0 ? 'flex' : 'none';
                });
            });
        });

        /* ── Live search within board ── */
        document.getElementById('taskSearch').addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.tk-card').forEach(card => {
                if (!q || (card.dataset.title || '').includes(q)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>

@endsection