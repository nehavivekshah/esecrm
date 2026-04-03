import sys
import os

file_path = r'c:\Users\6315\Desktop\MobileApps\RND\esecrm\resources\views\inc\task\popup.blade.php'

with open(file_path, 'r', encoding='utf-8') as f:
    lines = f.readlines()

new_content = """{{-- Backdrop overlay --}}
<div class="modal-backdrop fade show" style="z-index: 1050;" onclick="closeTaskAjax();"></div>

<div class="modal fade show" tabindex="-1" id="taskModal" style="display: block; z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content cf-wrap" style="border-radius:16px; border:none; overflow:hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            
            {{-- ── HEADER ── --}}
            <div class="cf-modal-header" style="align-items: flex-start;">
                <div class="flex-grow-1 pe-3">
                    <p class="cf-modal-header-title d-flex align-items-center">
                        <i class="bx bx-task me-2 fs-5"></i> Edit Task #{{ $task->id }}
                    </p>
                    <div class="mt-2 text-white">
                        <textarea id="tasktitle" class="cf-task-title-input" style="width:100%; background:transparent; border:none; outline:none; color:#fff; font-size:1.15rem; font-weight:600; resize:none;" rows="1" placeholder="Task title…">{{ ucfirst($task->title) }}</textarea>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
                    {{-- Timer start/stop --}}
                    @if($isRunning)
                        <a href="javascript:void(0)" class="btn btn-sm btn-light text-danger et-timer-running taskstart"
                            data-taskhr="{{ round($workingMin, 2) }}" id="{{ $taskHistory[0]->id }}" title="Stop Timer" style="font-weight:600; border-radius:8px;">
                            <i class="bx bx-stop-circle"></i> <span>Stop &bull; {{ floor($workingMin / 60) }}h {{ floor($workingMin % 60) }}m</span>
                        </a>
                    @else
                        <a href="javascript:void(0)" class="btn btn-sm btn-light text-success taskstart" id="{{ $task->id }}" title="Start Timer" style="font-weight:600; border-radius:8px;">
                            <i class="bx bx-play-circle"></i> Start Timer
                        </a>
                    @endif
                    
                    {{-- Delete --}}
                    @if(in_array('tasks_delete', $roleArray) || in_array('All', $roleArray))
                        <button type="button" class="btn btn-sm taskdeleted" id="{{ $task->id }}" style="background:rgba(255,255,255,0.15); color:#fff; border-radius:8px;" title="Delete Task">
                            <i class="bx bx-trash"></i>
                        </button>
                    @endif
                    
                    {{-- Close --}}
                    <button type="button" onclick="closeTaskAjax()" class="btn-close btn-close-white ms-1" style="opacity:1;" title="Close"></button>
                </div>
            </div>

            {{-- ── BODY ── --}}
            <div class="modal-body p-4" style="background:#f4fbfb; max-height: 75vh; overflow-y:auto;">
                <div class="row g-4">
                    {{-- LEFT COLUMN (Main Content) --}}
                    <div class="col-lg-8 pe-lg-4 border-end">
                        
                        {{-- Description --}}
                        <div class="cf-section-title"><i class="bx bx-align-left"></i> Description</div>
                        <div class="cf-field">
                            <form id="edttaskdetails" method="post">
                                @csrf
                                <input type="hidden" name="taskid" id="taskid" value="{{ $task->id }}" />
                                <div class="cf-input-box cf-textarea-box" style="background:#fff; height:180px;">
                                    <textarea name="taskdes" class="et-textarea w-100 h-100" id="example" placeholder="Add a more detailed description…" required style="border:none; outline:none; resize:none;">{{ ucfirst($task->des) }}</textarea>
                                </div>
                                @if(in_array('tasks_edit', $roleArray) || in_array('All', $roleArray))
                                    <div class="d-flex align-items-center gap-2 mt-3">
                                        <button type="submit" class="cf-btn-save"><i class="bx bx-save"></i> Save Changes</button>
                                        <button type="reset" class="cf-btn-cancel">Reset</button>
                                        <span id="res" class="small ms-2 text-success fw-bold"></span>
                                    </div>
                                @endif
                            </form>
                        </div>
                        
                        {{-- Attachments --}}
                        <div class="cf-section-title mt-4"><i class="bx bx-paperclip"></i> Attachments (<span id="attachmentCount">{{ count($taskAttachments ?? []) }}</span>)</div>
                        <div class="bg-white border rounded p-3" style="border-color:#d1d5db;">
                            <div class="d-flex justify-content-end mb-2">
                                <button type="button" class="btn btn-sm" onclick="document.getElementById('taskAttachmentInput').click()" style="background:rgba(0,102,102,0.1); color:#006666; font-weight:600;">
                                    <i class="bx bx-upload"></i> Upload
                                </button>
                                <input type="file" id="taskAttachmentInput" style="display:none;" onchange="uploadTaskAttachment(this)" />
                            </div>
                            <div id="attachmentsWrap">
                                @forelse($taskAttachments ?? [] as $attachment)
                                    <div class="d-flex justify-content-between align-items-center p-2 mb-2 border rounded bg-light" id="attachment-{{ $attachment->id }}">
                                        <a href="{{ asset($attachment->file_path) }}" target="_blank" class="d-flex align-items-center gap-2 text-decoration-none text-truncate" style="max-width: 80%;">
                                            <i class="bx bxs-file-pdf text-danger fs-4"></i>
                                            <span class="text-dark small fw-medium text-truncate">{{ $attachment->original_name }}</span>
                                        </a>
                                        @if(in_array('tasks_edit', $roleArray) || in_array('All', $roleArray))
                                            <button type="button" class="btn btn-sm text-danger border-0 bg-transparent" onclick="deleteAttachment({{ $attachment->id }})">
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
                                <p class="small text-muted mb-0">Uploading...</p>
                            </div>
                        </div>
                        
                        {{-- Comments --}}
                        <div class="cf-section-title mt-4"><i class="bx bx-comment-dots"></i> Comments</div>
                        <div class="mb-4">
                            <form method="post" id="taskComments">
                                @csrf
                                <input type="hidden" name="commenttaskid" value="{{ $task->id }}" />
                                <div class="d-flex gap-2">
                                    <div class="et-auth-avatar" style="width:32px; height:32px; border-radius:50%; background:#006666; color:#fff; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:0.8rem; font-weight:bold;">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="cf-input-box cf-textarea-box bg-white" style="padding:0; min-height:60px;">
                                            <textarea name="taskcomment" rows="2" id="commentInputs" placeholder="Write a comment… (Ctrl+Enter to post)" required style="width:100%; border:none; resize:none; padding:10px; border-radius:6px; outline:none;"></textarea>
                                        </div>
                                        <div class="mt-2 d-flex align-items-center gap-2">
                                            <button type="submit" class="btn btn-sm text-white" style="font-size:0.8rem; font-weight:600; padding:4px 16px; background:#006666; border-radius:6px;"><i class="bx bx-send"></i> Post</button>
                                            <span id="res1" class="small text-success fw-bold"></span>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            
                            <div id="reloadMsg" class="mt-4">
                                @if(count($taskComments) > 0)
                                    <div class="d-flex flex-column gap-3">
                                    @foreach($taskComments as $c)
                                        @php $isMine = $c->uid == Auth::user()->id; @endphp
                                        <div class="d-flex gap-3 {{ $isMine ? 'flex-row-reverse' : '' }}">
                                            <div style="width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:0.75rem; font-weight:700; {{ $isMine ? 'background:rgba(0,102,102,0.12);color:#006666;' : 'background:rgba(26,115,232,0.10);color:#1a73e8;' }}">
                                                {{ strtoupper(substr($c->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div class="p-2 px-3 rounded shadow-sm" style="{{ $isMine ? 'background:#006666; color:#fff;' : 'background:#fff; border:1px solid #e8eaed;' }} max-width:85%;">
                                                <div class="small fw-bold mb-1" style="{{ $isMine ? 'color:rgba(255,255,255,0.9);' : 'color:#202124;' }}">{{ $c->name ?? 'Unknown' }}</div>
                                                <div class="small" style="line-height:1.4;">{{ $c->comments }}</div>
                                                <div style="font-size:0.65rem; margin-top:6px; {{ $isMine ? 'color:rgba(255,255,255,0.7);' : 'color:#9aa0a6;' }} text-align:{{ $isMine?'right':'left' }};">
                                                    {{ \Carbon\Carbon::parse($c->created_at)->format('d M Y, H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    </div>
                                @else
                                    <div class="text-center p-4 text-muted small border rounded bg-light" style="border-style:dashed!important;">
                                        <i class="bx bx-comment fs-3 mb-2 opacity-50"></i><br>
                                        No comments yet. Be the first!
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                    
                    {{-- RIGHT COLUMN (Sidebar Details) --}}
                    <div class="col-lg-4">
                        <div class="cf-section-title"><i class="bx bx-cog"></i> Properties</div>
                        <div class="bg-white border rounded p-3 mb-4" style="border-color:#d1d5db;">
                            
                            {{-- Status --}}
                            <div class="cf-field mb-3">
                                <label class="d-flex align-items-center gap-1"><i class="bx bx-radio-circle-marked"></i> Status</label>
                                @php
                                    $statusMap = ['0'=>['#80868b','Open'], '1'=>['#ea4335','Urgent'], '2'=>['#f29900','Pending'], '3'=>['#1a73e8','In Progress'], '4'=>['#34a853','Done'], '5'=>['#006666','Closed']];
                                    [$sColor, $sLabel] = $statusMap[$task->status] ?? ['#80868b','Open'];
                                @endphp
                                <div class="cf-input-box px-2" style="border-color:{{ $sColor }}; border-width:2px; height:38px;">
                                    <select id="taskStatusSelect" class="w-100" style="color:{{ $sColor }}; font-weight:700;">
                                        @foreach($statusMap as $val => [$col, $lbl])
                                            <option value="{{ $val }}" {{ $task->status == $val ? 'selected' : '' }} style="color:{{ $col }};">{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            {{-- Label --}}
                            <div class="cf-field mb-3">
                                <label class="d-flex align-items-center gap-1"><i class="bx bxs-label"></i> Label</label>
                                <div class="cf-input-box px-2" style="height:38px;">
                                    <span id="labelicon" style="width:14px; height:14px; border-radius:50%; background:{{ $task->label ?? '#787878' }}; display:inline-block; margin-right:8px; flex-shrink:0;"></span>
                                    <select id="colorpalet" class="w-100">
                                        <option value="">Select…</option>
                                        @foreach($labels as $hex => $name)
                                            <option value="{{ $hex }}" {{ ($task->label ?? '') == $hex ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Project --}}
                            <div class="cf-field mb-3">
                                <label class="d-flex align-items-center gap-1"><i class="bx bx-briefcase-alt-2"></i> Project</label>
                                <div class="cf-select2-wrap shadow-none" style="height:38px;">
                                    <select id="taskProjectSelect" class="w-100" style="height:100%; border:none; padding:0 10px;">
                                        <option value="">— No Project —</option>
                                        @foreach($projects as $proj)
                                            <option value="{{ $proj->id }}" {{ $task->project_id == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Assignees --}}
                            <div class="cf-field mb-3 border-top pt-3">
                                <label class="d-flex align-items-center gap-1"><i class="bx bx-group"></i> Assigned To</label>
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    @forelse($task->assignees as $assignee)
                                        <div class="badge bg-light text-dark border p-1 px-2 d-flex align-items-center gap-1" title="{{ $assignee->name }}">
                                            <div style="width:18px; height:18px; border-radius:50%; background:#006666; color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.5rem;">{{ strtoupper(substr($assignee->name, 0, 1)) }}</div>
                                            {{ explode(' ', $assignee->name)[0] }}
                                        </div>
                                    @empty
                                        @php $primary = $userSingle[0] ?? null; @endphp
                                        @if($primary)
                                            <div class="badge bg-light text-dark border p-1 px-2 d-flex align-items-center gap-1" title="{{ $primary->name }}">
                                                <div style="width:18px; height:18px; border-radius:50%; background:#006666; color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.5rem;">{{ strtoupper(substr($primary->name, 0, 1)) }}</div>
                                                {{ explode(' ', $primary->name)[0] }}
                                            </div>
                                        @else
                                            <span class="text-muted small">Unassigned</span>
                                        @endif
                                    @endforelse
                                </div>
                                
                                @if(in_array('tasks_edit', $roleArray) || in_array('All', $roleArray))
                                    <div class="bg-light border rounded px-1 pt-1 mt-2" style="max-height:160px; overflow-y:auto;">
                                        @foreach($allUsers as $u)
                                            <label class="d-flex align-items-center gap-2 mb-1 p-1 px-2 rounded" style="cursor:pointer; font-size:0.75rem; font-weight:500; transition:0.2s;">
                                                <input type="checkbox" class="et-assignee-chk" name="assignee_ids[]" value="{{ $u->id }}" {{ in_array($u->id, $currentAssigneeIds) ? 'checked' : '' }} style="accent-color:#006666; width:14px; height:14px;" />
                                                {{ $u->name }}
                                            </label>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-sm mt-2 w-100" id="saveAssigneesBtn" data-taskid="{{ $task->id }}" style="background:rgba(0,102,102,0.1); color:#006666; font-weight:600;">
                                        <i class="bx bx-save"></i> Update Assignees
                                    </button>
                                @endif
                            </div>

                        </div>
                        
                        {{-- Time Log --}}
                        @if(count($taskHistory) > 0)
                            <div class="cf-section-title"><i class="bx bx-time-five"></i> Time Log</div>
                            <div class="bg-white border rounded p-3" style="border-color:#d1d5db;">
                                <div class="bg-light border rounded px-2 pt-2 mb-2" style="max-height:120px; overflow-y:auto;">
                                    @foreach($taskHistory as $t)
                                        @php
                                            $s  = strtotime($t->start_time ?? '');
                                            $e  = strtotime($t->end_time ?? '');
                                            $dm = $e > $s ? intval(($e - $s) / 60) : 0;
                                            $dh = intval($dm / 60);
                                            $dmin = $dm % 60;
                                        @endphp
                                        <div class="d-flex justify-content-between small text-muted mb-2 border-bottom pb-1">
                                            <span>{{ date_format(date_create($t->created_at), 'd M Y') }}</span>
                                            <strong class="text-dark">{{ $dh }}h {{ $dmin }}m</strong>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="d-flex justify-content-between align-items-center px-1">
                                    <span class="small text-muted fw-bold"><i class="bx bx-calculator"></i> Total Logged Time</span>
                                    <strong style="color:#006666; font-size:1.1rem;">{{ $th }}h {{ $tm }}m</strong>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
"""

new_file_content = ''.join(lines[:34]) + new_content + ''.join(lines[371:])

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(new_file_content)

print('File replaced successfully')
