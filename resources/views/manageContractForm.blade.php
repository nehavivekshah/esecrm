{{-- Select2 --}}
@once
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
/* ── Contract Modal Clean Style ── */
#contractModalForm .cf-label {
    font-size: .78rem;
    font-weight: 600;
    color: #5f6368;
    margin-bottom: 5px;
    display: block;
    text-transform: none;
}
#contractModalForm .cf-label .req { color: #ea4335; }

#contractModalForm .form-control,
#contractModalForm .form-select {
    font-size: .875rem;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    height: 38px;
    color: #202124;
    background: #fff;
    transition: border-color .15s, box-shadow .15s;
}
#contractModalForm textarea.form-control { height: auto; resize: none; }
#contractModalForm .form-control:focus,
#contractModalForm .form-select:focus {
    border-color: #006666;
    box-shadow: 0 0 0 3px rgba(0,102,102,.1);
}

/* Divider label */
.cf-divider {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 16px 0 12px;
    font-size: .72rem;
    font-weight: 700;
    color: #9aa0a6;
    text-transform: uppercase;
    letter-spacing: .08em;
}
.cf-divider::before, .cf-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e8eaed;
}

/* Modal header */
.cf-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e8eaed;
    background: linear-gradient(135deg, #006666 0%, #008080 100%);
    border-radius: 16px 16px 0 0;
}
.cf-modal-header-title { font-size: 1rem; font-weight: 700; color: #fff; margin: 0; }
.cf-modal-header-sub   { font-size: .75rem; color: rgba(255,255,255,.75); margin: 0; }
.cf-modal-header .btn-close { filter: invert(1); opacity: .8; }

/* Footer */
.cf-modal-footer {
    padding: 12px 20px;
    border-top: 1px solid #e8eaed;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 8px;
    background: #fff;
    border-radius: 0 0 16px 16px;
}

/* Select2 fixes */
.select2-container { width: 100% !important; }
.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    display: flex;
    align-items: center;
    padding: 0 10px;
    transition: border-color .15s, box-shadow .15s;
}
.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--open .select2-selection--single {
    border-color: #006666;
    box-shadow: 0 0 0 3px rgba(0,102,102,.1);
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: normal;
    color: #202124;
    padding-left: 0;
    font-size: .875rem;
}
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
.select2-container--default .select2-selection--single .select2-selection__placeholder { color: #9aa0a6; }
.select2-dropdown { border: 1px solid #e0e0e0; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,.1); z-index: 9999 !important; }
.select2-search--dropdown .select2-search__field {
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 6px 10px;
    font-size: .85rem;
}
.select2-results__option--highlighted { background: #006666 !important; }
</style>
@endonce

@php
    $isEdit     = !empty($contract->id);
    $showCustom = old('contract_type', $contract->contract_type ?? '') === 'new';
@endphp

{{-- Header --}}
<div class="cf-modal-header">
    <div>
        <p class="cf-modal-header-title">
            <i class="bx {{ $isEdit ? 'bx-edit-alt' : 'bx-plus-circle' }} me-1"></i>
            {{ $isEdit ? 'Edit Contract' : 'New Contract' }}
        </p>
        <p class="cf-modal-header-sub">
            {{ $isEdit ? 'Update the contract details below' : 'Fill in the details to add a new contract' }}
        </p>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

{{-- Body --}}
<div class="modal-body px-4 py-3" style="max-height:68vh; overflow-y:auto; background:#fafafa;">
    <form action="/manage-contract" method="post" id="contractModalForm">
        @csrf
        @if($isEdit)
            <input type="hidden" name="id" value="{{ $contract->id }}">
        @endif

        {{-- Client & Subject --}}
        <div class="cf-divider"><i class="bx bx-user-circle"></i> Client & Subject</div>

        <div class="row g-3 mb-1">
            {{-- Client --}}
            <div class="col-12">
                <label class="cf-label">Client <span class="req">*</span></label>
                <select id="cf_client_id" name="client_id" required>
                    <option value="">Search or select a client...</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ ($contract->client_id ?? '') == $client->id ? 'selected' : '' }}>
                            {{ $client->name ?? 'Unnamed' }}{{ $client->company ? ' — ' . $client->company : '' }}
                        </option>
                    @endforeach
                </select>
                @error('client_id') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
            </div>

            {{-- Subject --}}
            <div class="col-12">
                <label class="cf-label">Subject <span class="req">*</span></label>
                <input type="text" class="form-control" name="subject"
                       placeholder="e.g. Domain + Hosting Renewal 2025"
                       value="{{ old('subject', $contract->subject ?? '') }}" required>
                @error('subject') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
            </div>
        </div>

        {{-- Contract Type --}}
        <div class="cf-divider"><i class="bx bx-category"></i> Contract Type</div>

        <div class="row g-3 mb-1">
            <div class="col-md-{{ $showCustom ? '6' : '12' }}" id="cf_type_col">
                <label class="cf-label">Type <span class="req">*</span></label>
                <select class="form-select" id="cf_contract_type" name="contract_type" required>
                    <option value="">Select contract type...</option>
                    @foreach([
                        'domain'               => 'Domain Renewal',
                        'hosting'              => 'Hosting Renewal',
                        'domain-hosting'       => 'Domain + Hosting Renewal',
                        'hosting-email'        => 'Hosting + Email Renewal',
                        'hosting-webmail'      => 'Hosting + Webmail Renewal',
                        'domain-hosting-email' => 'Domain + Hosting + Email',
                        'seo'                  => 'SEO',
                        'new'                  => '✏ New (Custom)...',
                    ] as $val => $label)
                        <option value="{{ $val }}" {{ old('contract_type', $contract->contract_type ?? '') === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('contract_type') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6" id="cf_custom_wrap" style="{{ $showCustom ? '' : 'display:none;' }}">
                <label class="cf-label">Custom Type Name <span class="req">*</span></label>
                <input type="text" class="form-control" name="custom_contract_type" id="cf_custom_type"
                       placeholder="Enter custom contract type"
                       value="{{ old('custom_contract_type', '') }}">
                @error('custom_contract_type') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
            </div>
        </div>

        {{-- Value & Period --}}
        <div class="cf-divider"><i class="bx bx-calendar-check"></i> Value & Period</div>

        <div class="row g-3 mb-1">
            <div class="col-md-4">
                <label class="cf-label">Contract Value (₹)</label>
                <div class="input-group">
                    <span class="input-group-text" style="background:#f1f3f4; border:1px solid #e0e0e0; border-right:0; border-radius:8px 0 0 8px; font-size:.85rem; color:#5f6368;">₹</span>
                    <input type="number" step="0.01" min="0" class="form-control" name="value"
                           placeholder="0.00"
                           style="border-left:0; border-radius:0 8px 8px 0;"
                           value="{{ old('value', $contract->value ?? '') }}">
                </div>
                @error('value') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-4">
                <label class="cf-label">Start Date <span class="req">*</span></label>
                <input type="date" class="form-control" name="start_date"
                       value="{{ old('start_date', !empty($contract->start_date) ? \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d') : '') }}" required>
                @error('start_date') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-4">
                <label class="cf-label">End Date</label>
                <input type="date" class="form-control" name="end_date"
                       value="{{ old('end_date', !empty($contract->end_date) ? \Carbon\Carbon::parse($contract->end_date)->format('Y-m-d') : '') }}">
                @error('end_date') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
            </div>
        </div>

        {{-- Description --}}
        <div class="cf-divider"><i class="bx bx-notepad"></i> Notes</div>

        <div class="mb-1">
            <textarea class="form-control" name="description" rows="3"
                      placeholder="Add any notes, scope of work, or terms for this contract (optional)...">{{ old('description', $contract->des ?? '') }}</textarea>
            @error('description') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
        </div>

    </form>
</div>

{{-- Footer --}}
<div class="cf-modal-footer">
    <button type="button" class="btn btn-light border rounded-pill px-4" style="font-size:.875rem;" data-bs-dismiss="modal">
        Cancel
    </button>
    <button type="submit" form="contractModalForm" class="btn rounded-pill px-4"
            style="background:#006666; color:#fff; font-size:.875rem; font-weight:600; border:none;">
        <i class="bx bx-check me-1"></i>
        {{ $isEdit ? 'Update Contract' : 'Save Contract' }}
    </button>
</div>

<script>
(function () {
    // ── Select2 ──
    if (typeof $.fn.select2 !== 'undefined') {
        $('#cf_client_id').select2({
            placeholder: 'Search or select a client...',
            allowClear: true,
            dropdownParent: $('#manageContractModal'),
            width: '100%'
        });
    }

    // ── Contract type toggle ──
    const typeEl      = document.getElementById('cf_contract_type');
    const customWrap  = document.getElementById('cf_custom_wrap');
    const customEl    = document.getElementById('cf_custom_type');
    const typeCol     = document.getElementById('cf_type_col');

    function toggleCustom() {
        if (!typeEl) return;
        const isNew = typeEl.value === 'new';
        if (customWrap)  customWrap.style.display  = isNew ? '' : 'none';
        if (customEl)    customEl.required          = isNew;
        if (typeCol) {
            typeCol.className = isNew ? 'col-md-6' : 'col-12';
        }
    }

    if (typeEl) {
        toggleCustom();
        typeEl.addEventListener('change', toggleCustom);
    }
})();
</script>
