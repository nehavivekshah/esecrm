{{-- Select2 (loaded once per page) --}}
@once
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
/* ── Contract Modal ─────────────────────────────────── */
.cm-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:16px 20px; border-bottom:1px solid #eee;
    border-radius:16px 16px 0 0; background:#fff;
}
.cm-header-left   { display:flex; align-items:center; gap:10px; }
.cm-header-icon   {
    width:38px; height:38px; border-radius:10px; flex-shrink:0;
    background:rgba(0,102,102,.1); color:#006666;
    display:flex; align-items:center; justify-content:center; font-size:1.1rem;
}
.cm-header-title  { font-size:.95rem; font-weight:700; color:#202124; margin:0; line-height:1.2; }
.cm-header-sub    { font-size:.75rem; color:#9aa0a6; margin:0; line-height:1.3; text-transform:none !important; }

/* Section cards */
.cm-card {
    border:1px solid #e8eaed; border-radius:10px;
    overflow:hidden; margin-bottom:12px; background:#fff;
}
.cm-card:last-child { margin-bottom:0; }
.cm-card-header {
    display:flex; align-items:center; gap:10px;
    padding:10px 14px; background:#f8f9fa; border-bottom:1px solid #e8eaed;
}
.cm-card-icon {
    width:28px; height:28px; border-radius:7px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center; font-size:.85rem;
}
.cm-card-title { font-size:.82rem; font-weight:600; color:#202124; margin:0; text-transform:none !important; }
.cm-card-sub   { font-size:.73rem; color:#9aa0a6; margin:0; text-transform:none !important; }
.cm-card-body  { padding:14px; }

/* Labels */
.cm-label {
    display:block; font-size:.78rem; font-weight:600;
    color:#5f6368; margin-bottom:5px; text-transform:none !important;
}
.cm-label .req { color:#ea4335; }

/* Input icon prefix — floating not input-group to avoid Select2 conflict */
.cm-input-wrap { position:relative; }
.cm-input-wrap .cm-icon {
    position:absolute; left:9px; top:50%; transform:translateY(-50%);
    color:#9aa0a6; font-size:1rem; z-index:2; pointer-events:none;
}
.cm-input-wrap input.form-control,
.cm-input-wrap textarea.form-control { padding-left:32px; }
.cm-input-wrap .form-select          { padding-left:32px; }

/* Select2 overrides */
.select2-container { width:100% !important; }
.select2-container--default .select2-selection--single {
    height:38px; border:1px solid #dee2e6; border-radius:6px;
    display:flex; align-items:center; padding-left:32px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height:normal; color:#212529; padding-left:0;
}
.select2-container--default .select2-selection--single .select2-selection__arrow { height:36px; }
.select2-dropdown { z-index:9999 !important; }

/* Footer */
.cm-footer {
    display:flex; align-items:center; justify-content:flex-end; gap:8px;
    padding:12px 20px; border-top:1px solid #eee; background:#fff;
    border-radius:0 0 16px 16px;
}
</style>
@endonce

@php
    $isEdit     = !empty($contract->id);
    $showCustom = old('contract_type', $contract->contract_type ?? '') === 'new';
@endphp

{{-- ── Header ── --}}
<div class="cm-header">
    <div class="cm-header-left">
        <div class="cm-header-icon">
            <i class="bx {{ $isEdit ? 'bx-edit' : 'bx-plus-circle' }}"></i>
        </div>
        <div>
            <p class="cm-header-title">{{ $isEdit ? 'Edit Contract' : 'New Contract' }}</p>
            <p class="cm-header-sub">{{ $isEdit ? 'Update the contract details below' : 'Fill in the details to create a new contract' }}</p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

{{-- ── Body ── --}}
<div class="modal-body px-3 py-3" style="max-height:65vh; overflow-y:auto; background:#f8f9fa;">
    <form action="/manage-contract" method="post" id="contractModalForm">
        @csrf
        @if($isEdit)
            <input type="hidden" name="id" value="{{ $contract->id }}">
        @endif

        {{-- Card 1: Contract Info --}}
        <div class="cm-card">
            <div class="cm-card-header">
                <div class="cm-card-icon" style="background:rgba(26,115,232,.1);color:#1a73e8;">
                    <i class="bx bx-file-blank"></i>
                </div>
                <div>
                    <p class="cm-card-title">Contract Information</p>
                    <p class="cm-card-sub">Client, subject &amp; contract type</p>
                </div>
            </div>
            <div class="cm-card-body">
                <div class="row g-3">

                    {{-- Client --}}
                    <div class="col-12">
                        <label class="cm-label">Select Client <span class="req">*</span></label>
                        <div class="cm-input-wrap">
                            <i class="bx bx-user cm-icon"></i>
                            <select id="client_id" name="client_id" required>
                                <option value="">-- Select Client --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ ($contract->client_id ?? '') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name ?? 'Unnamed' }}{{ $client->company ? ' ('.$client->company.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('client_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- Subject --}}
                    <div class="col-12">
                        <label class="cm-label">Subject <span class="req">*</span></label>
                        <div class="cm-input-wrap">
                            <i class="bx bx-text cm-icon"></i>
                            <input type="text" class="form-control" name="subject"
                                   placeholder="e.g. Domain + Hosting Renewal 2025"
                                   value="{{ old('subject', $contract->subject ?? '') }}" required>
                        </div>
                        @error('subject') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- Contract Type --}}
                    <div class="col-md-6">
                        <label class="cm-label">Contract Type <span class="req">*</span></label>
                        <div class="cm-input-wrap">
                            <i class="bx bx-category cm-icon"></i>
                            <select class="form-select" id="contract_type" name="contract_type" required>
                                <option value="">Select Type</option>
                                @foreach([
                                    'domain'               => 'Domain Renewal',
                                    'hosting'              => 'Hosting Renewal',
                                    'domain-hosting'       => 'Domain + Hosting',
                                    'hosting-email'        => 'Hosting + Email',
                                    'hosting-webmail'      => 'Hosting + Webmail',
                                    'domain-hosting-email' => 'Domain + Hosting + Email',
                                    'seo'                  => 'SEO',
                                    'new'                  => 'New (Custom)...',
                                ] as $val => $label)
                                    <option value="{{ $val }}" {{ old('contract_type', $contract->contract_type ?? '') === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('contract_type') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- Custom Type --}}
                    <div class="col-md-6" id="custom_type_wrap" style="{{ $showCustom ? '' : 'display:none;' }}">
                        <label class="cm-label">Custom Type Name <span class="req">*</span></label>
                        <div class="cm-input-wrap">
                            <i class="bx bx-pencil cm-icon"></i>
                            <input type="text" class="form-control" name="custom_contract_type" id="custom_contract_type"
                                   placeholder="Enter type name"
                                   value="{{ old('custom_contract_type', '') }}">
                        </div>
                        @error('custom_contract_type') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- Card 2: Value & Dates --}}
        <div class="cm-card">
            <div class="cm-card-header">
                <div class="cm-card-icon" style="background:rgba(52,168,83,.1);color:#34a853;">
                    <i class="bx bx-rupee"></i>
                </div>
                <div>
                    <p class="cm-card-title">Value &amp; Duration</p>
                    <p class="cm-card-sub">Contract amount and validity period</p>
                </div>
            </div>
            <div class="cm-card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="cm-label">Contract Value (₹)</label>
                        <div class="cm-input-wrap">
                            <i class="bx bx-rupee cm-icon"></i>
                            <input type="number" step="0.01" class="form-control" name="value" placeholder="0.00"
                                   value="{{ old('value', $contract->value ?? '') }}">
                        </div>
                        @error('value') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="cm-label">Start Date <span class="req">*</span></label>
                        <div class="cm-input-wrap">
                            <i class="bx bx-calendar cm-icon"></i>
                            <input type="date" class="form-control" name="start_date"
                                   value="{{ old('start_date', !empty($contract->start_date) ? \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d') : '') }}" required>
                        </div>
                        @error('start_date') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="cm-label">End Date</label>
                        <div class="cm-input-wrap">
                            <i class="bx bx-calendar-x cm-icon"></i>
                            <input type="date" class="form-control" name="end_date"
                                   value="{{ old('end_date', !empty($contract->end_date) ? \Carbon\Carbon::parse($contract->end_date)->format('Y-m-d') : '') }}">
                        </div>
                        @error('end_date') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Description --}}
        <div class="cm-card" style="margin-bottom:0;">
            <div class="cm-card-header">
                <div class="cm-card-icon" style="background:rgba(251,188,4,.1);color:#f29900;">
                    <i class="bx bx-notepad"></i>
                </div>
                <div>
                    <p class="cm-card-title">Description / Scope</p>
                    <p class="cm-card-sub">Optional notes about this contract</p>
                </div>
            </div>
            <div class="cm-card-body">
                <textarea class="form-control" name="description" rows="3"
                          style="font-size:0.85rem;"
                          placeholder="Describe the scope or details of this contract...">{{ old('description', $contract->des ?? '') }}</textarea>
                @error('description') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
        </div>

    </form>
</div>

{{-- ── Footer ── --}}
<div class="cm-footer">
    <button type="button" class="lb-btn lb-btn-ghost" data-bs-dismiss="modal">
        <i class="bx bx-x"></i> Cancel
    </button>
    <button type="submit" form="contractModalForm" class="lb-btn lb-btn-primary">
        <i class="bx bx-check-circle"></i>
        {{ $isEdit ? 'Update Contract' : 'Save Contract' }}
    </button>
</div>

<script>
(function () {
    // ── Select2 on client dropdown ──
    if (typeof $.fn.select2 !== 'undefined') {
        $('#client_id').select2({
            placeholder: '-- Select Client --',
            allowClear: true,
            dropdownParent: $('#manageContractModal'),
            width: '100%'
        });
    }

    // ── Contract type toggle ──
    const typeEl     = document.getElementById('contract_type');
    const customWrap = document.getElementById('custom_type_wrap');
    const customEl   = document.getElementById('custom_contract_type');

    function toggleCustom() {
        if (!typeEl || !customWrap) return;
        const show = typeEl.value === 'new';
        customWrap.style.display = show ? '' : 'none';
        if (customEl) customEl.required = show;
    }

    if (typeEl) {
        toggleCustom();
        typeEl.addEventListener('change', toggleCustom);
    }
})();
</script>
