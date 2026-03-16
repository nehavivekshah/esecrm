@php
    $roles     = session('roles');
    $roleArray = explode(',', ($roles->permissions ?? ''));
    $task      = $taskSingle[0];
    $labels    = [
        '#787878' => 'New Task',
        '#007265' => 'In Working',
        '#ff9800' => 'Pause',
        '#e91e1e' => 'Urgent',
        '#0dd500' => 'Complete',
    ];
    // Working hours calculation
    $isRunning  = !empty($taskHistory[0]->id) && $taskHistory[0]->status == '0';
    $workingMin = $isRunning
        ? (strtotime(date('d-m-Y h:i:s a')) - strtotime($taskHistory[0]->start_time)) / 60
        : 0;
    // Total duration
    $total_min = 0;
    foreach ($taskHistory as $t) {
        $diff = intval((strtotime($t->start_time ?? '') - strtotime($t->end_time ?? '')) / 60);
        $total_min += intval($diff / 60) * 60 + $diff % 60;
    }
    $th = intval($total_min / 60);
    $tm = $total_min % 60;
@endphp

{{-- Backdrop overlay --}}
<div class="et-backdrop" onclick="window.location='{{ route('task') }}';"></div>

<div class="offcanvas offcanvas-end show" tabindex="-1" id="taskOffcanvas"
     style="width:820px; max-width:100vw; border-top-left-radius:16px; border-bottom-left-radius:16px;
            box-shadow:-12px 0 40px rgba(0,0,0,0.12); z-index:1061; visibility:visible; overflow:hidden;">

    {{-- ── HEADER ── --}}
    <div class="et-header">
        <div class="et-header-icon">
            <i class="bx bx-task"></i>
        </div>
        <div class="flex-grow-1 min-w-0">
            <textarea id="tasktitle" class="et-title-input" rows="1"
                      placeholder="Task title…">{{ ucfirst($task->title) }}</textarea>
        </div>
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            {{-- Timer start/stop --}}
            @if($isRunning)
                <a href="javascript:void(0)" class="lb-btn lb-btn-ghost et-timer-btn et-timer-running taskstart"
                   data-taskhr="{{ round($workingMin, 2) }}" id="{{ $taskHistory[0]->id }}"
                   title="Stop Timer">
                    <i class="bx bx-stop-circle"></i>
                    <span class="d-none d-sm-inline">Stop &bull; {{ floor($workingMin/60) }}h {{ floor($workingMin%60) }}m</span>
                </a>
            @else
                <a href="javascript:void(0)" class="lb-btn lb-btn-ghost et-timer-btn taskstart"
                   id="{{ $task->id }}" title="Start Timer">
                    <i class="bx bx-play-circle"></i>
                    <span class="d-none d-sm-inline">Start</span>
                </a>
            @endif
            {{-- Delete --}}
            @if(in_array('tasks_delete', $roleArray) || in_array('All', $roleArray))
                <a href="javascript:void(0)" class="kb-action-btn kb-action-del taskdeleted"
                   id="{{ $task->id }}" title="Delete Task">
                    <i class="bx bx-trash"></i>
                </a>
            @endif
            {{-- Close --}}
            <a href="{{ route('task') }}" class="kb-action-btn" title="Close"
               style="background:rgba(60,64,67,0.07);color:#5f6368;">
                <i class="bx bx-x"></i>
            </a>
        </div>
    </div>

    <div class="offcanvas-body p-0" style="overflow-y:auto;">
        <div class="et-body">

            {{-- ── LEFT SIDEBAR ── --}}
            <div class="et-sidebar">

                {{-- Label selector --}}
                <div class="et-section">
                    <div class="et-section-title">
                        <i class="bx bxs-label"></i> Label
                    </div>
                    <div class="et-label-row">
                        <span class="et-label-dot" id="labelicon"
                              style="background:{{ $task->label ?? '#787878' }};"></span>
                        <select id="colorpalet" class="et-label-select">
                            <option value="">Select…</option>
                            @foreach($labels as $hex => $name)
                                <option value="{{ $hex }}"
                                    {{ ($task->label ?? '') == $hex ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Priority / Status quick-view --}}
                <div class="et-section">
                    <div class="et-section-title">
                        <i class="bx bx-radio-circle-marked"></i> Status
                    </div>
                    @php
                        $statusMap = [
                            '0' => ['#80868b', 'Open'],
                            '1' => ['#ea4335', 'Urgent'],
                            '2' => ['#f29900', 'Pending'],
                            '3' => ['#1a73e8', 'In Progress'],
                            '4' => ['#34a853', 'Done'],
                            '5' => ['#006666', 'Closed'],
                        ];
                        [$sColor, $sLabel] = $statusMap[$task->status] ?? ['#80868b', 'Open'];
                    @endphp
                    <span class="et-status-badge" style="background:{{ $sColor }}18; color:{{ $sColor }};">
                        {{ $sLabel }}
                    </span>
                </div>

                {{-- Assigned to --}}
                <div class="et-section">
                    <div class="et-section-title">
                        <i class="bx bx-user"></i> Assigned To
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @php $assignee = $userSingle[0] ?? null; @endphp
                        @if($assignee)
                            <div class="et-assignee-avatar">
                                {{ strtoupper(substr($assignee->name, 0, 1)) }}
                            </div>
                            <span class="et-assignee-name">{{ $assignee->name }}</span>
                        @else
                            <span class="text-muted small">Unassigned</span>
                        @endif
                    </div>
                </div>

                {{-- Duration history --}}
                @if(count($taskHistory) > 0)
                    <div class="et-section">
                        <div class="et-section-title">
                            <i class="bx bx-time-five"></i> Time Log
                        </div>
                        <div class="et-time-list">
                            @foreach($taskHistory as $t)
                                @php
                                    $d  = intval((strtotime($t->start_time ?? '') - strtotime($t->end_time ?? '')) / 60);
                                    $h  = intval($d / 60);
                                    $m  = $d % 60;
                                @endphp
                                <div class="et-time-row">
                                    <span class="et-time-date">{{ date_format(date_create($t->created_at), 'd M') }}</span>
                                    <span class="et-time-val">{{ -$h }}h {{ -$m }}m</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="et-time-total">
                            <i class="bx bx-calculator"></i>
                            Total: <strong>{{ -$th }}h {{ -$tm }}m</strong>
                        </div>
                    </div>
                @endif

            </div>

            {{-- ── MAIN CONTENT ── --}}
            <div class="et-main">

                {{-- Description --}}
                <div class="et-panel">
                    <div class="et-panel-header">
                        <i class="bx bx-align-left"></i>
                        <span>Description</span>
                    </div>
                    <form id="edttaskdetails" method="post">
                        @csrf
                        <input type="hidden" name="taskid" id="taskid" value="{{ $task->id }}" />
                        <textarea name="taskdes" rows="6" class="et-textarea" id="example"
                                  placeholder="Add a more detailed description…"
                                  required>{{ ucfirst($task->des) }}</textarea>
                        @if(in_array('tasks_edit', $roleArray) || in_array('All', $roleArray))
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <button type="submit" class="lb-btn lb-btn-primary" style="padding:5px 16px;font-size:0.80rem;">
                                    <i class="bx bx-save"></i> Save
                                </button>
                                <button type="reset" class="lb-btn lb-btn-ghost" style="padding:5px 12px;font-size:0.80rem;">
                                    Cancel
                                </button>
                                <span id="res" class="small ms-1"></span>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- Comments --}}
                <div class="et-panel">
                    <div class="et-panel-header">
                        <i class="bx bx-comment-dots"></i>
                        <span>Comments</span>
                    </div>

                    {{-- Comment input --}}
                    <form method="post" id="taskComments">
                        @csrf
                        <input type="hidden" name="commenttaskid" value="{{ $task->id }}" />
                        <div class="et-comment-input-wrap">
                            <div class="et-auth-avatar">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <textarea name="taskcomment" rows="2" id="commentInputs"
                                      class="et-comment-input"
                                      placeholder="Write a comment… (Ctrl+Enter to post)" required></textarea>
                        </div>
                        <div class="d-flex gap-2 mt-2 ps-1" style="padding-left:40px;">
                            <button type="submit" class="lb-btn lb-btn-primary" style="padding:4px 14px;font-size:0.78rem;">
                                <i class="bx bx-send"></i> Post
                            </button>
                            <span id="res1" class="small align-self-center"></span>
                        </div>
                    </form>

                    {{-- Comment list --}}
                    <div id="reloadMsg" class="et-comment-list mt-3">
                        @if(count($taskComments) > 0)
                            @foreach($taskComments as $c)
                                @php $isMine = $c->uid == Auth::user()->id; @endphp
                                <div class="et-comment {{ $isMine ? 'et-comment-mine' : 'et-comment-other' }}">
                                    <div class="et-comment-avatar" style="{{ $isMine ? 'background:rgba(0,102,102,0.12);color:#006666;' : 'background:rgba(26,115,232,0.10);color:#1a73e8;' }}">
                                        {{ strtoupper(substr($c->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div class="et-comment-bubble {{ $isMine ? 'et-bubble-mine' : 'et-bubble-other' }}">
                                        <div class="et-comment-name">{{ $c->name ?? 'Unknown' }}</div>
                                        <div class="et-comment-text">{{ $c->comments }}</div>
                                        <div class="et-comment-time">{{ \Carbon\Carbon::parse($c->created_at)->format('d M Y, H:i') }}</div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="kb-empty-col" style="padding:24px 0;">
                                <i class="bx bx-comment"></i>
                                <span>No comments yet. Be the first!</span>
                            </div>
                        @endif
                    </div>
                </div>

            </div>{{-- /et-main --}}
        </div>{{-- /et-body --}}
    </div>{{-- /offcanvas-body --}}
</div>

<script>
(function () {
    /* 1. Auto-resize title textarea */
    const titleTA = document.getElementById('tasktitle');
    function resizeTitle() {
        titleTA.style.height = 'auto';
        titleTA.style.height = titleTA.scrollHeight + 'px';
    }
    if (titleTA) { resizeTitle(); titleTA.addEventListener('input', resizeTitle); }

    /* 2. Live label dot update (background, not color) */
    const colorSel = document.getElementById('colorpalet');
    const labelDot = document.getElementById('labelicon');
    if (colorSel && labelDot) {
        colorSel.addEventListener('change', function () {
            labelDot.style.background = this.value || '#787878';
            /* keep script.js happy — it targets #labelicon.style.color */
            labelDot.style.color = this.value || '#787878';
        });
    }

    /* 3. Ctrl+Enter to submit comment */
    const commentTA = document.getElementById('commentInputs');
    if (commentTA) {
        commentTA.addEventListener('keydown', function (e) {
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('taskComments').dispatchEvent(new Event('submit', { bubbles: true }));
            }
        });
    }

    /* 4. Live running timer counter in Stop button */
    const stopBtn = document.querySelector('.et-timer-running');
    if (stopBtn) {
        let startMs = Date.now();
        const baseMin = parseFloat(stopBtn.dataset.taskhr || 0) * 60000;
        const span    = stopBtn.querySelector('span');
        if (span) {
            setInterval(function () {
                const totalMs  = baseMin + (Date.now() - startMs);
                const h = Math.floor(totalMs / 3600000);
                const m = Math.floor((totalMs % 3600000) / 60000);
                const s = Math.floor((totalMs % 60000) / 1000);
                span.textContent = 'Stop \u2022 ' + (h ? h + 'h ' : '') + m + 'm ' + s + 's';
            }, 1000);
        }
    }
})();
</script>