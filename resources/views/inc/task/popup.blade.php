@php
    $roles = session('roles');
    $roleArray = explode(',', ($roles->permissions ?? ''));
    $task = $taskSingle[0];
    $labels = [
        '#787878' => 'New Task',
        '#007265' => 'In Working',
        '#ff9800' => 'Pause',
        '#e91e1e' => 'Urgent',
        '#0dd500' => 'Complete',
    ];
    // Working hours calculation
    $isRunning = !empty($taskHistory[0]->id) && $taskHistory[0]->status == '0';
    $workingMin = $isRunning
        ? (strtotime(date('d-m-Y h:i:s a')) - strtotime($taskHistory[0]->start_time)) / 60
        : 0;
    // Total duration — fixed: end_time - start_time (not reversed)
    $total_min = 0;
    foreach ($taskHistory as $t) {
        $start = strtotime($t->start_time ?? '');
        $end   = strtotime($t->end_time ?? '');
        if ($end > $start) {
            $total_min += intval(($end - $start) / 60);
        }
    }
    $th = intval($total_min / 60);
    $tm = $total_min % 60;

    // Current assignee IDs for the multi-select
    $currentAssigneeIds = $task->assignees->pluck('id')->toArray();
    $allUsers   = $allUsers   ?? collect();
    $projects   = $projects   ?? collect();
@endphp

{{-- Backdrop overlay --}}
<div class="et-backdrop" onclick="closeTaskAjax();"></div>

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
                    data-taskhr="{{ round($workingMin, 2) }}" id="{{ $taskHistory[0]->id }}" title="Stop Timer">
                    <i class="bx bx-stop-circle"></i>
                    <span class="d-none d-sm-inline">Stop &bull; {{ floor($workingMin / 60) }}h
                        {{ floor($workingMin % 60) }}m</span>
                </a>
            @else
                <a href="javascript:void(0)" class="lb-btn lb-btn-ghost et-timer-btn taskstart" id="{{ $task->id }}"
                    title="Start Timer">
                    <i class="bx bx-play-circle"></i>
                    <span class="d-none d-sm-inline">Start</span>
                </a>
            @endif
            {{-- Delete --}}
            @if(in_array('tasks_delete', $roleArray) || in_array('All', $roleArray))
                <a href="javascript:void(0)" class="btn kb-action-btn kb-action-del taskdeleted" id="{{ $task->id }}"
                    title="Delete Task">
                    <i class="bx bx-trash"></i>
                </a>
            @endif
            {{-- Close --}}
            <a href="javascript:void(0)" onclick="closeTaskAjax()" class="btn kb-action-btn" title="Close"
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
                                <option value="{{ $hex }}" {{ ($task->label ?? '') == $hex ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Status --}}
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
                    <select id="taskStatusSelect" class="et-label-select"
                            style="border-color:{{ $sColor }}; color:{{ $sColor }}; font-weight:700;">
                        @foreach($statusMap as $val => [$col, $lbl])
                            <option value="{{ $val }}" {{ $task->status == $val ? 'selected' : '' }}
                                    style="color:{{ $col }};">{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Project Association (editable) --}}
                <div class="et-section">
                    <div class="et-section-title">
                        <i class="bx bx-briefcase-alt-2"></i> Project
                    </div>
                    <div class="cf-select2-wrap" style="height: 38px; border-color: #d1d5db; border-radius: 6px;">
                        <select id="taskProjectSelect" class="et-label-select w-100" style="height: 100%; border: none;">
                            <option value="">— No Project —</option>
                            @foreach($projects as $proj)
                                <option value="{{ $proj->id }}" {{ $task->project_id == $proj->id ? 'selected' : '' }}>
                                    {{ $proj->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Parent Task Association --}}
                @if($task->parent)
                    <div class="et-section">
                        <div class="et-section-title">
                            <i class="bx bx-subdirectory-right"></i> Parent Task
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small">
                                {{ $task->parent->title }}
                            </span>
                        </div>
                    </div>
                @endif

                {{-- ── ASSIGNEES (multi-user) ── --}}
                <div class="et-section">
                    <div class="et-section-title">
                        <i class="bx bx-group"></i> Assigned To
                    </div>

                    {{-- Current assignee avatar row --}}
                    <div class="et-assignee-row" id="et-assignee-row">
                        @forelse($task->assignees as $assignee)
                            <div class="et-avatar-chip" title="{{ $assignee->name }}">
                                {{ strtoupper(substr($assignee->name, 0, 1)) }}
                            </div>
                        @empty
                            @php $primary = $userSingle[0] ?? null; @endphp
                            @if($primary)
                                <div class="et-avatar-chip" title="{{ $primary->name }}">
                                    {{ strtoupper(substr($primary->name, 0, 1)) }}
                                </div>
                            @else
                                <span class="text-muted small">Unassigned</span>
                            @endif
                        @endforelse
                    </div>

                    {{-- Change assignees --}}
                    @if(in_array('tasks_edit', $roleArray) || in_array('All', $roleArray))
                        <div class="et-assignee-checkboxes mt-2" id="et-assignee-list">
                            @foreach($allUsers as $u)
                                <label class="et-assignee-check-row">
                                    <input type="checkbox"
                                           class="et-assignee-chk"
                                           name="assignee_ids[]"
                                           value="{{ $u->id }}"
                                           {{ in_array($u->id, $currentAssigneeIds) ? 'checked' : '' }} />
                                    <span class="et-chk-avatar">{{ strtoupper(substr($u->name, 0, 1)) }}</span>
                                    <span class="et-chk-name">{{ $u->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <button type="button" class="lb-btn lb-btn-primary mt-2" id="saveAssigneesBtn"
                                style="padding:4px 12px;font-size:0.76rem;width:100%;"
                                data-taskid="{{ $task->id }}">
                            <i class="bx bx-save"></i> Save Assignees
                        </button>
                    @endif
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
                                    $s  = strtotime($t->start_time ?? '');
                                    $e  = strtotime($t->end_time ?? '');
                                    $dm = $e > $s ? intval(($e - $s) / 60) : 0;
                                    $dh = intval($dm / 60);
                                    $dmin = $dm % 60;
                                @endphp
                                <div class="et-time-row">
                                    <span class="et-time-date">{{ date_format(date_create($t->created_at), 'd M') }}</span>
                                    <span class="et-time-val">{{ $dh }}h {{ $dmin }}m</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="et-time-total">
                            <i class="bx bx-calculator"></i>
                            Total: <strong>{{ $th }}h {{ $tm }}m</strong>
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
                            placeholder="Add a more detailed description…" required>{{ ucfirst($task->des) }}</textarea>
                        @if(in_array('tasks_edit', $roleArray) || in_array('All', $roleArray))
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <button type="submit" class="lb-btn lb-btn-primary"
                                    style="padding:5px 16px;font-size:0.80rem;">
                                    <i class="bx bx-save"></i> Save
                                </button>
                                <button type="reset" class="lb-btn lb-btn-ghost"
                                    style="padding:5px 12px;font-size:0.80rem;">
                                    Cancel
                                </button>
                                <span id="res" class="small ms-1"></span>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- Attachments --}}
                <div class="et-panel mt-3">
                    <div class="et-panel-header d-flex justify-content-between align-items-center p-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bx bx-paperclip text-muted"></i>
                            <span class="fw-semibold" style="font-size: 0.9rem;">Attachments (<span
                                    id="attachmentCount">{{ count($taskAttachments ?? []) }}</span>)</span>
                        </div>
                        <button type="button" class="lb-btn lb-btn-ghost btn-sm"
                            onclick="document.getElementById('taskAttachmentInput').click()"
                            style="padding: 4px 10px; font-size: 0.8rem;">
                            <i class="bx bx-upload"></i> Upload File
                        </button>
                        <input type="file" id="taskAttachmentInput" style="display:none;"
                            onchange="uploadTaskAttachment(this)" />
                    </div>

                    <div id="attachmentsWrap" class="p-3">
                        @forelse($taskAttachments ?? [] as $attachment)
                            <div class="d-flex justify-content-between align-items-center p-2 mb-2 border rounded bg-light"
                                id="attachment-{{ $attachment->id }}">
                                <a href="{{ asset($attachment->file_path) }}" target="_blank"
                                    class="d-flex align-items-center gap-2 text-decoration-none text-truncate"
                                    style="max-width: 80%;">
                                    <i class="bx bxs-file-pdf text-danger" style="font-size:1.6rem;"
                                        id="att-icon-{{ $attachment->id }}"></i>
                                    <span
                                        class="text-dark small fw-medium text-truncate">{{ $attachment->original_name }}</span>
                                </a>
                                @if(in_array('tasks_edit', $roleArray) || in_array('All', $roleArray))
                                    <button type="button" class="btn btn-sm text-danger border-0 bg-transparent"
                                        onclick="deleteAttachment({{ $attachment->id }})">
                                        <i class="bx bx-trash" style="font-size: 1.1rem;"></i>
                                    </button>
                                @endif
                            </div>
                        @empty
                            <div class="text-muted small text-center p-3" id="noAttachmentsMsg">No files attached yet.</div>
                        @endforelse
                    </div>
                    <div id="attachmentLoader" class="text-center p-3" style="display:none;">
                        <i class="bx bx-loader-alt bx-spin text-primary fs-4"></i>
                        <div class="small text-muted mt-1">Uploading...</div>
                    </div>
                </div>

                {{-- Comments --}}
                <div class="et-panel">
                    <div class="et-panel-header">
                        <i class="bx bx-comment-dots"></i>
                        <span>Comments</span>
                    </div>

                    <form method="post" id="taskComments">
                        @csrf
                        <input type="hidden" name="commenttaskid" value="{{ $task->id }}" />
                        <div class="et-comment-input-wrap">
                            <div class="et-auth-avatar">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <textarea name="taskcomment" rows="2" id="commentInputs" class="et-comment-input"
                                placeholder="Write a comment… (Ctrl+Enter to post)" required></textarea>
                        </div>
                        <div class="d-flex gap-2 mt-2 ps-1" style="padding-left:40px;">
                            <button type="submit" class="lb-btn lb-btn-primary"
                                style="padding:4px 14px;font-size:0.78rem;">
                                <i class="bx bx-send"></i> Post
                            </button>
                            <span id="res1" class="small align-self-center"></span>
                        </div>
                    </form>

                    <div id="reloadMsg" class="et-comment-list mt-3">
                        @if(count($taskComments) > 0)
                            @foreach($taskComments as $c)
                                @php $isMine = $c->uid == Auth::user()->id; @endphp
                                <div class="et-comment {{ $isMine ? 'et-comment-mine' : 'et-comment-other' }}">
                                    <div class="et-comment-avatar"
                                        style="{{ $isMine ? 'background:rgba(0,102,102,0.12);color:#006666;' : 'background:rgba(26,115,232,0.10);color:#1a73e8;' }}">
                                        {{ strtoupper(substr($c->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div class="et-comment-bubble {{ $isMine ? 'et-bubble-mine' : 'et-bubble-other' }}">
                                        <div class="et-comment-name">{{ $c->name ?? 'Unknown' }}</div>
                                        <div class="et-comment-text">{{ $c->comments }}</div>
                                        <div class="et-comment-time">
                                            {{ \Carbon\Carbon::parse($c->created_at)->format('d M Y, H:i') }}</div>
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

    /* 2. Live label dot update */
    const colorSel = document.getElementById('colorpalet');
    const labelDot = document.getElementById('labelicon');
    if (colorSel && labelDot) {
        colorSel.addEventListener('change', function () {
            labelDot.style.background = this.value || '#787878';
            labelDot.style.color      = this.value || '#787878';
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
        let startMs  = Date.now();
        const baseMin = parseFloat(stopBtn.dataset.taskhr || 0) * 60000;
        const span    = stopBtn.querySelector('span');
        if (span) {
            setInterval(function () {
                const totalMs = baseMin + (Date.now() - startMs);
                const h = Math.floor(totalMs / 3600000);
                const m = Math.floor((totalMs % 3600000) / 60000);
                const s = Math.floor((totalMs % 60000) / 1000);
                span.textContent = 'Stop \u2022 ' + (h ? h + 'h ' : '') + m + 'm ' + s + 's';
            }, 1000);
        }
    }

    /* 5. Status change AJAX */
    const statusSel = document.getElementById('taskStatusSelect');
    if (statusSel) {
        statusSel.addEventListener('change', function () {
            const taskId = document.getElementById('taskid').value;
            fetch('{{ route("task.meta.update") }}', {
                method : 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ task_id: taskId, status: this.value })
            }).then(r => r.json()).then(d => {
                if (!d.success) console.warn('Status update failed', d);
            });
        });
    }

    /* 6. Project change AJAX (with Select2 support) */
    if (typeof $.fn.select2 !== 'undefined') {
        $('#taskProjectSelect').select2({
            placeholder: "Search Project...",
            allowClear: true,
            dropdownParent: $('.offcanvas.show')
        }).on('change', function() {
            const taskId = document.getElementById('taskid').value;
            fetch('{{ route("task.meta.update") }}', {
                method : 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ task_id: taskId, project_id: this.value || null })
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    console.log('Project updated successfully');
                } else {
                    console.warn('Project update failed', d);
                }
            });
        });
    } else {
        const projSel = document.getElementById('taskProjectSelect');
        if (projSel) {
            projSel.addEventListener('change', function () {
                const taskId = document.getElementById('taskid').value;
                fetch('{{ route("task.meta.update") }}', {
                    method : 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ task_id: taskId, project_id: this.value || null })
                }).then(r => r.json()).then(d => {
                    if (d.success) {
                    // Show small "saved" feedback
                    const fb = document.createElement('span');
                    fb.className = 'text-success small';
                    fb.textContent = ' ✓ Saved';
                    projSel.parentNode.appendChild(fb);
                    setTimeout(() => fb.remove(), 2000);
                }
            });
        });
    }

    /* 7. Save Assignees AJAX */
    const saveAssBtn = document.getElementById('saveAssigneesBtn');
    if (saveAssBtn) {
        saveAssBtn.addEventListener('click', function () {
            const taskId = this.dataset.taskid;
            const checked = Array.from(document.querySelectorAll('.et-assignee-chk:checked'))
                                  .map(c => parseInt(c.value));

            fetch('{{ route("task.meta.update") }}', {
                method : 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ task_id: taskId, assignee_ids: checked })
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    document.getElementById('et-assignee-row').innerHTML = d.avatarHtml;
                    this.textContent = '✓ Saved!';
                    this.classList.add('btn-success');
                    setTimeout(() => {
                        this.innerHTML = '<i class="bx bx-save"></i> Save Assignees';
                        this.classList.remove('btn-success');
                    }, 2000);
                }
            }).catch(err => console.error('Assignee update error:', err));
        });
    }

    /* 8. Attachment upload/delete */
    window.uploadTaskAttachment = function (input) {
        if (!input.files || input.files.length === 0) return;
        let formData = new FormData();
        formData.append('file', input.files[0]);
        formData.append('task_id', '{{ $task->id }}');
        formData.append('_token', '{{ csrf_token() }}');

        document.getElementById('attachmentLoader').style.display = 'block';
        if (document.getElementById('noAttachmentsMsg')) {
            document.getElementById('noAttachmentsMsg').style.display = 'none';
        }

        fetch('{{ route("task.attachment.upload") }}', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                document.getElementById('attachmentLoader').style.display = 'none';
                if (data.status === 'success') {
                    const att  = data.attachment;
                    const html = `
                    <div class="d-flex justify-content-between align-items-center p-2 mb-2 border rounded bg-light" id="attachment-${att.id}">
                        <a href="/${att.file_path}" target="_blank" class="d-flex align-items-center gap-2 text-decoration-none text-truncate" style="max-width: 80%;">
                            <i class="bx bxs-file text-primary" style="font-size:1.6rem;"></i>
                            <span class="text-dark small fw-medium text-truncate">${att.original_name}</span>
                        </a>
                        <button type="button" class="btn btn-sm text-danger border-0 bg-transparent" onclick="deleteAttachment(${att.id})">
                            <i class="bx bx-trash" style="font-size: 1.1rem;"></i>
                        </button>
                    </div>`;
                    document.getElementById('attachmentsWrap').insertAdjacentHTML('beforeend', html);
                    let countSpan = document.getElementById('attachmentCount');
                    countSpan.innerText = parseInt(countSpan.innerText) + 1;
                } else {
                    alert(data.message || 'Error uploading file');
                }
            })
            .catch(error => {
                document.getElementById('attachmentLoader').style.display = 'none';
                alert('Error uploading file');
                console.error(error);
            });
        input.value = '';
    };

    window.deleteAttachment = function (id) {
        if (!confirm('Delete this attachment?')) return;
        fetch(`/task-attachment/${id}`, {
            method : 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(response => response.json()).then(data => {
            if (data.status === 'success') {
                document.getElementById('attachment-' + id).remove();
                let countSpan = document.getElementById('attachmentCount');
                let newCount  = parseInt(countSpan.innerText) - 1;
                countSpan.innerText = newCount;
                if (newCount === 0 && document.getElementById('noAttachmentsMsg')) {
                    document.getElementById('noAttachmentsMsg').style.display = 'block';
                }
            }
        });
    };
})();
</script>