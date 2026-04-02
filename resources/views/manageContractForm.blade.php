{{-- Select2 (only if not already loaded globally) --}}
@once
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
    /* ── Contract Modal Styles ── */
    .cm-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 24px;
        border-bottom: 1px solid #f0f0f0;
        background: #fff;
        border-radius: 16px 16px 0 0;
    }
    .cm-modal-header-left { display: flex; align-items: center; gap: 12px; }
    .cm-modal-icon {
        width: 40px; height: 40px; border-radius: 10px;
        background: rgba(0,102,102,0.10); color: #006666;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
    }
    .cm-modal-title { font-size: 1rem; font-weight: 700; color: #202124; margin: 0; }
    .cm-modal-sub   { font-size: 0.78rem; color: #9aa0a6; margin: 0; }

    /* Card sections inside modal */
    .cm-section {
        background: #f8f9fa;
        border: 1px solid #e8eaed;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 12px;
    }
    .cm-section-header {
        display: flex; align-items: center; gap: 10px;
        padding: 12px 16px;
        background: #fff;
        border-bottom: 1px solid #f0f0f0;
    }
    .cm-section-icon {
        width: 30px; height: 30px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.95rem; flex-shrink: 0;
    }
    .cm-section-title { font-size: 0.85rem; font-weight: 600; color: #202124; margin: 0; }
    .cm-section-sub   { font-size: 0.75rem; color: #9aa0a6; margin: 0; }
    .cm-section-body  { padding: 14px 16px; }

    /* Labels & inputs */
    .cm-label {
        font-size: 0.8rem; font-weight: 600;
        color: #5f6368; margin-bottom: 4px; display: block;
    }
    .cm-label span.req { color: #ea4335; }

    /* Select2 inside modal fix */
    .select2-container { width: 100% !important; }
    .select2-container--default .select2-selection--single {
        height: 38px; border: 1px solid #dee2e6; border-radius: 6px;
        display: flex; align-items: center; padding: 0 8px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: normal; padding-left: 4px; color: #212529;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
    .select2-dropdown { z-index: 9999 !important; }

    /* Footer bar */
    .cm-modal-footer {
        display: flex; align-items: center; justify-content: flex-end; gap: 8px;
        padding: 14px 24px;
        border-top: 1px solid #f0f0f0;
        background: #fff;
        border-radius: 0 0 16px 16px;
    }
</style>
@endonce

@php
    $isEdit     = !empty($contract->id);
    $showCustom = old('contract_type', $contract->contract_type ?? '') === 'new';
@endphp

{{-- ── Modal Header ── --}}
<div class="cm-modal-header">
    <div class="cm-modal-header-left">
        <div class="cm-modal-icon">
            <i class="bx {{ $isEdit ? 'bx-edit' : 'bx-plus-circle' }}"></i>
        </div>
        <div>
            <p class="cm-modal-title">{{ $isEdit ? 'Edit Contract' : 'New Contract' }}</p>
            <p class="cm-modal-sub">{{ $isEdit ? 'Update contract details below' : 'Fill in details to create a new contract' }}</p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

{{-- ── Modal Body ── --}}
<div class="modal-body px-4 py-3" style="max-height:70vh; overflow-y:auto;">
    <form action="/manage-contract" method="post" id="contractForm">
        @csrf
        @if($isEdit)
            <input type="hidden" name="id" value="{{ $contract->id }}">
        @endif

        {{-- Section 1: Contract Info --}}
        <div class="cm-section">
            <div class="cm-section-header">
                <div class="cm-section-icon" style="background:rgba(26,115,232,0.10);color:#1a73e8;">
                    <i class="bx bx-file-blank"></i>
                </div>
                <div>
                    <p class="cm-section-title">Contract Information</p>
                    <p class="cm-section-sub">Client, subject &amp; type</p>
                </div>
            </div>
            <div class="cm-section-body">
                <div class="row g-3">

                    {{-- Client --}}
                    <div class="col-md-12">
                        <label class="cm-label">Select Client <span class="req">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-user"></i></span>
                            <select class="form-select ps-2" id="client_id" name="client_id" required style="border-left:0;">
                                <option value="">-- Select Client --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" @if($client->id == ($contract->client_id ?? '')) selected @endif>
                                        {{ $client->name ?? 'Unnamed' }} {{ $client->company ? '('.$client->company.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('client_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- Subject --}}
                    <div class="col-md-12">
                        <label class="cm-label">Subject <span class="req">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-text"></i></span>
                            <input type="text" class="form-control" name="subject" placeholder="e.g. Domain + Hosting Renewal 2025"
                                   value="{{ old('subject', $contract->subject ?? '') }}" required>
                        </div>
                        @error('subject') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- Contract Type --}}
                    <div class="col-md-6">
                        <label class="cm-label">Contract Type <span class="req">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-category"></i></span>
                            <select class="form-select" id="contract_type" name="contract_type" required style="border-left:0;">
                                <option value="">Select Type</option>
                                @foreach([
                                    'domain'               => 'Domain Renewal',
                                    'hosting'              => 'Hosting Renewal',
                                    'domain-hosting'       => 'Domain + Hosting Renewal',
                                    'hosting-email'        => 'Hosting + Email Renewal',
                                    'hosting-webmail'      => 'Hosting + Webmail Renewal',
                                    'domain-hosting-email' => 'Domain + Hosting + Email Renewal',
                                    'seo'                  => 'SEO',
                                    'new'                  => 'New...',
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
                    <div class="col-md-6" id="custom_contract_type_container" style="{{ $showCustom ? '' : 'display:none;' }}">
                        <label class="cm-label">Custom Type Name <span class="req">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-pencil"></i></span>
                            <input type="text" class="form-control" name="custom_contract_type" id="custom_contract_type"
                                   placeholder="Enter type name"
                                   value="{{ old('custom_contract_type', '') }}">
                        </div>
                        @error('custom_contract_type') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- Section 2: Financial & Dates --}}
        <div class="cm-section">
            <div class="cm-section-header">
                <div class="cm-section-icon" style="background:rgba(52,168,83,0.10);color:#34a853;">
                    <i class="bx bx-rupee"></i>
                </div>
                <div>
                    <p class="cm-section-title">Value &amp; Duration</p>
                    <p class="cm-section-sub">Contract value and validity period</p>
                </div>
            </div>
            <div class="cm-section-body">
                <div class="row g-3">

                    {{-- Value --}}
                    <div class="col-md-4">
                        <label class="cm-label">Contract Value (₹)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-rupee"></i></span>
                            <input type="number" step="0.01" class="form-control" name="value" placeholder="0.00"
                                   value="{{ old('value', $contract->value ?? '') }}">
                        </div>
                        @error('value') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- Start Date --}}
                    <div class="col-md-4">
                        <label class="cm-label">Start Date <span class="req">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                            <input type="date" class="form-control" name="start_date"
                                   value="{{ old('start_date', !empty($contract->start_date) ? \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d') : '') }}" required>
                        </div>
                        @error('start_date') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- End Date --}}
                    <div class="col-md-4">
                        <label class="cm-label">End Date</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-calendar-x"></i></span>
                            <input type="date" class="form-control" name="end_date"
                                   value="{{ old('end_date', !empty($contract->end_date) ? \Carbon\Carbon::parse($contract->end_date)->format('Y-m-d') : '') }}">
                        </div>
                        @error('end_date') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- Section 3: Description --}}
        <div class="cm-section mb-0">
            <div class="cm-section-header">
                <div class="cm-section-icon" style="background:rgba(251,188,4,0.10);color:#f29900;">
                    <i class="bx bx-notepad"></i>
                </div>
                <div>
                    <p class="cm-section-title">Description / Scope</p>
                    <p class="cm-section-sub">Optional notes or scope of work</p>
                </div>
            </div>
            <div class="cm-section-body">
                <textarea class="form-control" name="description" rows="3"
                          placeholder="Describe the scope or details of this contract...">{{ old('description', $contract->des ?? '') }}</textarea>
                @error('description') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
        </div>

    </form>
</div>

{{-- ── Modal Footer ── --}}
<div class="cm-modal-footer">
    <button type="button" class="lb-btn lb-btn-ghost" data-bs-dismiss="modal">
        <i class="bx bx-x"></i> Cancel
    </button>
    <button type="submit" form="contractForm" class="lb-btn lb-btn-primary">
        <i class="bx bx-check-circle"></i>
        {{ $isEdit ? 'Update Contract' : 'Save Contract' }}
    </button>
</div>

<script>
    (function () {
        // Select2 on client dropdown
        if (typeof $.fn.select2 !== 'undefined') {
            $('#client_id').select2({
                placeholder: '-- Select Client --',
                allowClear: true,
                dropdownParent: $('#manageContractModal'),
                width: '100%'
            });
        }

        // Contract type custom field toggle
        const typeEl     = document.getElementById('contract_type');
        const customWrap = document.getElementById('custom_contract_type_container');
        const customEl   = document.getElementById('custom_contract_type');

        function toggleCustom() {
            if (!typeEl || !customWrap) return;
            const show = typeEl.value === 'new';
            customWrap.style.display = show ? 'block' : 'none';
            if (customEl) customEl.required = show;
        }

        if (typeEl) {
            toggleCustom();
            typeEl.addEventListener('change', toggleCustom);
        }
    })();
</script>
