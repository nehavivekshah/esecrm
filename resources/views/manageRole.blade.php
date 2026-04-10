@extends('layout')
@section('title', request()->get('id') ? 'Edit Role - eseCRM' : 'Add Role - eseCRM')

@section('content')
@php
    $isEdit      = !empty(request()->get('id'));
    $features    = explode(',', ($roles->features ?? ''));
    $permissions = explode(',', ($roles->permissions ?? ''));

    $modules = [
        'attendances' => ['label' => 'Attendances',  'icon' => 'bx-calendar-check', 'color' => '#1a73e8'],
        'users'       => ['label' => 'Users',         'icon' => 'bx-group',          'color' => '#006666'],
        'tasks'       => ['label' => 'Tasks',         'icon' => 'bx-task',           'color' => '#f9a825'],
        'proposals'   => ['label' => 'Proposals',     'icon' => 'bx-notepad',        'color' => '#ea4335'],
        'leads'       => ['label' => 'Leads',         'icon' => 'bx-user-voice',     'color' => '#34a853'],
        'clients'     => ['label' => 'Customers',     'icon' => 'bx-briefcase',      'color' => '#1a73e8'],
        'projects'    => ['label' => 'Projects',      'icon' => 'bx-folder-open',    'color' => '#8e24aa'],
        'contracts'   => ['label' => 'Contracts',     'icon' => 'bx-file-blank',     'color' => '#006666'],
        'recoveries'  => ['label' => 'Recovery',      'icon' => 'bx-alarm-exclamation', 'color' => '#d93025'],
        'invoice'     => ['label' => 'Invoices',      'icon' => 'bx-receipt',        'color' => '#34a853'],
        'company'     => ['label' => 'Company',       'icon' => 'bx-building',       'color' => '#0d47a1'],
        'smtp'        => ['label' => 'SMTP Settings', 'icon' => 'bx-envelope-open',  'color' => '#5f6368'],
    ];

    $permTypes = [
        'assign' => ['label' => 'Assigned',  'icon' => 'bx-user-plus'],
        'add'    => ['label' => 'Add',       'icon' => 'bx-plus-circle'],
        'edit'   => ['label' => 'Edit',      'icon' => 'bx-pencil'],
        'delete' => ['label' => 'Delete',    'icon' => 'bx-trash'],
        'export' => ['label' => 'Export',    'icon' => 'bx-download'],
        'import' => ['label' => 'Import',    'icon' => 'bx-upload'],
    ];
@endphp

<section class="task__section">
    @include('inc.header', ['title' => $isEdit ? 'Edit Role' : 'Add New Role'])

    <div class="dash-container">
        <div class="dash-card rs-form-card">

            {{-- Card Header --}}
            <div class="rs-form-header">
                <div>
                    <p class="rs-form-header-title">
                        <i class="bx {{ $isEdit ? 'bx-edit-alt' : 'bx-plus-circle' }} me-1"></i>
                        {{ $isEdit ? 'Edit Role & Permissions' : 'Define New Role & Permissions' }}
                    </p>
                    <p class="rs-form-header-sub">
                        {{ $isEdit ? 'Modify what this role can access across the CRM' : 'Set the role identity and module-level permissions' }}
                    </p>
                </div>
                <a href="/role-settings" class="rs-back-btn">
                    <i class="bx bx-arrow-back"></i> Back
                </a>
            </div>

            {{-- Form Body --}}
            <div class="rs-form-body">

                @if ($errors->any())
                <div class="rs-alert rs-alert-error mb-4">
                    <i class="bx bx-error-circle fs-5"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
                @endif

                <form action="manage-role-setting" method="POST" id="roleForm">
                    @csrf
                    <input type="hidden" name="id" value="{{ request()->get('id') ?? '' }}">

                    {{-- ── ROLE IDENTITY ── --}}
                    <div class="rs-section-title">Role Identity</div>
                    <div class="row g-3 mb-2">
                        <div class="col-md-4 rs-field">
                            <label>Role Name <span class="req">*</span></label>
                            <div class="rs-input-box">
                                <span class="rs-icon"><i class="bx bx-shield-quarter"></i></span>
                                <input type="text" name="role" id="role" required
                                       placeholder="e.g. Sales Manager"
                                       value="{{ old('role', $roles->title ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-4 rs-field">
                            <label>Designation / Sub-role</label>
                            <div class="rs-input-box">
                                <span class="rs-icon"><i class="bx bx-id-card"></i></span>
                                <input type="text" name="subrole" id="subrole"
                                       placeholder="e.g. Field Sales Executive"
                                       value="{{ old('subrole', $roles->subtitle ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-4 rs-field">
                            <label>Status <span class="req">*</span></label>
                            <div class="rs-input-box">
                                <span class="rs-icon"><i class="bx bx-toggle-right"></i></span>
                                <select name="status" required>
                                    <option value="1" {{ (($roles->status ?? '1') == '1') ? 'selected' : '' }}>Active</option>
                                    <option value="2" {{ (($roles->status ?? '') == '2') ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- ── PERMISSIONS MATRIX ── --}}
                    <div class="rs-section-title d-flex align-items-center justify-content-between">
                        <span>Module Permissions</span>
                        <div class="d-flex gap-2">
                            <button type="button" class="rs-bulk-btn" id="checkAll">
                                <i class="bx bx-check-double"></i> Select All
                            </button>
                            <button type="button" class="rs-bulk-btn rs-bulk-danger" id="uncheckAll">
                                <i class="bx bx-x"></i> Clear All
                            </button>
                        </div>
                    </div>

                    {{-- Permission grid header --}}
                    <div class="rs-perm-table">
                        <div class="rs-perm-head">
                            <div class="rs-col-module">Module</div>
                            @foreach($permTypes as $type => $meta)
                            <div class="rs-col-perm text-center">
                                <i class="bx {{ $meta['icon'] }}"></i>
                                <span>{{ $meta['label'] }}</span>
                            </div>
                            @endforeach
                        </div>

                        @foreach($modules as $key => $mod)
                        @php
                            $rowCheckedAll = collect(array_keys($permTypes))->every(fn($t) => in_array("{$key}_{$t}", $permissions));
                        @endphp
                        <div class="rs-perm-row" id="row-{{ $key }}">
                            <div class="rs-col-module">
                                <div class="rs-mod-info">
                                    <div class="rs-mod-dot" style="background:{{ $mod['color'] }}15; color:{{ $mod['color'] }};border:1px solid {{ $mod['color'] }}30;">
                                        <i class="bx {{ $mod['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <div class="rs-mod-label">{{ $mod['label'] }}</div>
                                        <button type="button" class="rs-row-toggle {{ $rowCheckedAll ? 'rs-row-toggle-on' : '' }}"
                                                data-module="{{ $key }}"
                                                onclick="toggleRow('{{ $key }}', this)">
                                            {{ $rowCheckedAll ? 'All On' : 'Select All' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @foreach($permTypes as $type => $meta)
                            @php $permKey = "{$key}_{$type}"; @endphp
                            <div class="rs-col-perm">
                                <label class="rs-check-wrap">
                                    <input type="checkbox"
                                           class="rs-check-input perm-{{ $key }}"
                                           name="permissions[{{ $key }}][]"
                                           value="{{ $type }}"
                                           {{ in_array($permKey, $permissions) ? 'checked' : '' }}
                                           @if($type === 'import' && in_array($key, ['proposals']))  disabled @endif>
                                    <span class="rs-check-box">
                                        <i class="bx bx-check"></i>
                                    </span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @endforeach

                    </div>

                    {{-- ── FOOTER ── --}}
                    <div class="rs-form-footer mt-4">
                        <a href="/role-settings" class="rs-btn-cancel">Cancel</a>
                        <button type="reset" class="rs-btn-cancel">Reset</button>
                        <button type="submit" class="rs-btn-save">
                            <i class="bx bx-check"></i>
                            {{ $isEdit ? 'Update Role' : 'Create Role' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</section>

<style>
/* ════════════════════════════════════
   Role Settings Form — Premium Styles
════════════════════════════════════ */
.dash-container { padding: 24px; }

/* ── Form Card ── */
.rs-form-card   { border-radius: 18px; border: 1px solid #e8eaed; overflow: hidden; }

/* ── Card Header ── */
.rs-form-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 28px;
    background: linear-gradient(135deg, #005757, #007e7e);
}
.rs-form-header-title { font-size: 1rem; font-weight: 700; color: #fff; margin: 0; }
.rs-form-header-sub   { font-size: 0.74rem; color: rgba(255,255,255,.72); margin: 4px 0 0; }
.rs-back-btn {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.3);
    color: #fff; border-radius: 10px; padding: 7px 14px;
    font-size: 0.82rem; font-weight: 600; text-decoration: none; transition: background .15s;
}
.rs-back-btn:hover { background: rgba(255,255,255,.25); color: #fff; }

/* ── Form Body ── */
.rs-form-body { padding: 28px; background: #f4fbfb; }

/* ── Section Title ── */
.rs-section-title {
    font-size: 0.72rem; font-weight: 700; color: #006666;
    text-transform: uppercase; letter-spacing: .07em;
    margin: 24px 0 14px; padding-bottom: 5px;
    border-bottom: 1.5px solid rgba(0,102,102,.12);
}
.rs-section-title:first-child { margin-top: 0; }

/* ── Field ── */
.rs-field { display: flex; flex-direction: column; }
.rs-field label { font-size: 0.78rem; color: #5f6368; margin-bottom: 5px; font-weight: 500; }
.rs-field .req  { color: #ea4335; }

/* ── Input Box ── */
.rs-input-box {
    display: flex; align-items: center;
    border: 1.5px solid #d1d5db; border-radius: 10px; background: #fff;
    overflow: hidden; transition: border-color .15s, box-shadow .15s; height: 44px;
}
.rs-input-box:focus-within { border-color: #006666; box-shadow: 0 0 0 3px rgba(0,102,102,.08); }
.rs-icon {
    display: flex; align-items: center; justify-content: center;
    width: 40px; height: 100%; flex-shrink: 0;
    color: #006666; font-size: 1.05rem;
    border-right: 1.5px solid #e8eaed; background: #f8fdfd;
}
.rs-input-box input,
.rs-input-box select {
    flex: 1; border: none !important; outline: none !important; box-shadow: none !important;
    background: transparent; font-size: 0.875rem; color: #202124;
    padding: 0 12px; height: 100%; appearance: none;
}
.rs-input-box input::placeholder { color: #9aa0a6; }
.rs-input-box select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='%235f6368'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 10px center; padding-right: 28px;
}

/* ── Bulk Buttons ── */
.rs-bulk-btn {
    display: inline-flex; align-items: center; gap: 4px;
    border: 1.5px solid rgba(0,102,102,.2); background: rgba(0,102,102,.05);
    color: #006666; border-radius: 8px; padding: 4px 12px;
    font-size: 0.72rem; font-weight: 700; cursor: pointer; transition: all .15s;
}
.rs-bulk-btn:hover { background: rgba(0,102,102,.1); }
.rs-bulk-danger { border-color: rgba(234,67,53,.2); background: rgba(234,67,53,.05); color: #ea4335; }
.rs-bulk-danger:hover { background: rgba(234,67,53,.1); }

/* ── Permissions Table ── */
.rs-perm-table {
    background: #fff; border: 1px solid #e8eaed; border-radius: 14px; overflow: hidden;
}

.rs-perm-head,
.rs-perm-row {
    display: grid;
    grid-template-columns: 220px repeat(6, 1fr);
    align-items: center;
}

.rs-perm-head {
    background: linear-gradient(135deg, #005757, #007e7e);
    padding: 12px 16px; gap: 4px;
}
.rs-perm-head .rs-col-module { color: rgba(255,255,255,.9); font-size: 0.78rem; font-weight: 700; }
.rs-perm-head .rs-col-perm   {
    display: flex; flex-direction: column; align-items: center; gap: 2px;
    color: rgba(255,255,255,.85); font-size: 0.65rem; font-weight: 700; text-transform: uppercase;
}
.rs-perm-head .rs-col-perm i { font-size: 1.1rem; }

.rs-perm-row {
    padding: 12px 16px; gap: 4px;
    border-bottom: 1px solid #f1f3f4; transition: background .12s;
}
.rs-perm-row:last-child { border-bottom: none; }
.rs-perm-row:hover { background: rgba(0,102,102,0.025); }

.rs-col-module { padding-right: 8px; }
.rs-col-perm   { display: flex; align-items: center; justify-content: center; }

/* Module info */
.rs-mod-info { display: flex; align-items: center; gap: 10px; }
.rs-mod-dot  {
    width: 34px; height: 34px; border-radius: 8px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 1rem;
}
.rs-mod-label { font-size: 0.82rem; font-weight: 600; color: #202124; }
.rs-row-toggle {
    display: inline-block; font-size: 0.65rem; color: #80868b;
    background: none; border: none; padding: 0; cursor: pointer;
    text-decoration: underline; transition: color .12s; margin-top: 1px;
}
.rs-row-toggle:hover { color: #006666; }
.rs-row-toggle-on { color: #006666; font-weight: 700; }

/* Custom Checkboxes */
.rs-check-wrap { display: inline-flex; cursor: pointer; margin: 0; }
.rs-check-wrap input[type="checkbox"] { display: none; }
.rs-check-box {
    width: 22px; height: 22px; border-radius: 6px;
    border: 2px solid #d1d5db; background: #fff;
    display: flex; align-items: center; justify-content: center;
    transition: all .15s; color: #fff; font-size: 0.9rem;
}
.rs-check-wrap input:checked + .rs-check-box {
    background: #006666; border-color: #006666;
}
.rs-check-wrap input:disabled + .rs-check-box {
    background: #f1f3f4; border-color: #e0e0e0; cursor: not-allowed; opacity: .5;
}
.rs-check-wrap:hover input:not(:checked):not(:disabled) + .rs-check-box {
    border-color: #006666; background: rgba(0,102,102,.05);
}

/* ── Alert ── */
.rs-alert { display:flex; align-items:flex-start; gap:10px; border-radius:10px; padding:12px 16px; font-size:0.85rem; font-weight:500; }
.rs-alert-error { background:rgba(234,67,53,.08); border:1px solid rgba(234,67,53,.25); color:#ea4335; }

/* ── Footer ── */
.rs-form-footer {
    display: flex; justify-content: flex-end; gap: 8px;
    padding-top: 20px; border-top: 1px solid #e8eaed; flex-wrap: wrap;
}
.rs-btn-cancel {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.85rem; padding: 9px 20px; border-radius: 10px;
    border: 1.5px solid #d1d5db; background: #fff; color: #5f6368;
    cursor: pointer; text-decoration: none; transition: background .15s;
}
.rs-btn-cancel:hover { background: #f5f5f5; color: #444; }
.rs-btn-save {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.85rem; font-weight: 700; padding: 9px 24px; border-radius: 10px;
    border: none; background: #006666; color: #fff; cursor: pointer; transition: background .15s;
}
.rs-btn-save:hover { background: #004e4e; }

@media (max-width: 900px) {
    .rs-perm-head,
    .rs-perm-row { grid-template-columns: 160px repeat(6, 1fr); }
}
@media (max-width: 768px) {
    .rs-form-body   { padding: 16px; }
    .rs-form-header { padding: 16px 18px; flex-direction: column; align-items: flex-start; gap: 10px; }
    .rs-perm-head .rs-col-perm span { display: none; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Select / Clear All ── */
    document.getElementById('checkAll')?.addEventListener('click', () => {
        document.querySelectorAll('.rs-check-input:not(:disabled)').forEach(cb => cb.checked = true);
        document.querySelectorAll('.rs-row-toggle').forEach(b => {
            b.textContent = 'All On'; b.classList.add('rs-row-toggle-on');
        });
    });
    document.getElementById('uncheckAll')?.addEventListener('click', () => {
        document.querySelectorAll('.rs-check-input:not(:disabled)').forEach(cb => cb.checked = false);
        document.querySelectorAll('.rs-row-toggle').forEach(b => {
            b.textContent = 'Select All'; b.classList.remove('rs-row-toggle-on');
        });
    });

    /* ── Row toggles ── */
    window.toggleRow = function (mod, btn) {
        const boxes = document.querySelectorAll('.perm-' + mod + ':not(:disabled)');
        const allChecked = [...boxes].every(cb => cb.checked);
        boxes.forEach(cb => cb.checked = !allChecked);
        btn.textContent = allChecked ? 'Select All' : 'All On';
        btn.classList.toggle('rs-row-toggle-on', !allChecked);
    };
});
</script>

@if(session('success'))
<script>document.addEventListener('DOMContentLoaded',()=>{if(typeof swal!=='undefined')swal("Saved!","{{ session('success') }}","success");});</script>
@endif
@endsection
