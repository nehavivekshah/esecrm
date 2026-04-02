{{-- manageContractForm.blade.php - Pure partial for AJAX modal injection --}}
@php
    $showCustom = old('contract_type', $contract->contract_type ?? '') === 'new';
@endphp

<div class="modal-header" style="border-bottom:1px solid #f0f0f0; padding:16px 20px;">
    <h5 class="modal-title" style="font-size:1.1rem; font-weight:700; color:#202124;">
        @if(!empty($contract->id)) Edit Contract @else Add New Contract @endif
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body px-4 py-3">
    <form action="/manage-contract" method="post" class="row g-3">
        @csrf

        @if(!empty($contract->id))
            <input type="hidden" name="id" value="{{ $contract->id }}">
        @endif

        <!-- Client -->
        <div class="col-md-6">
            <label for="client_id" class="form-label fw-500" style="font-size:0.875rem;">
                Select Client <span class="text-danger">*</span>
            </label>
            <select class="form-select" id="client_id" name="client_id" required>
                <option value="">Select Client</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" @if($client->id == ($contract->client_id ?? '')) selected @endif>
                        {{ $client->name ?? 'Unnamed Client' }} {{ $client->company ? '('.$client->company.')' : '' }}
                    </option>
                @endforeach
            </select>
            @error('client_id') <div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div> @enderror
        </div>

        <!-- Subject -->
        <div class="col-md-6">
            <label for="subject" class="form-label fw-500" style="font-size:0.875rem;">
                Subject <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="subject" name="subject"
                   value="{{ old('subject', $contract->subject ?? '') }}" required>
            @error('subject') <div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div> @enderror
        </div>

        <!-- Contract Type -->
        <div class="col-md-6">
            <label for="contract_type" class="form-label fw-500" style="font-size:0.875rem;">
                Contract Type <span class="text-danger">*</span>
            </label>
            <select class="form-select" id="contract_type" name="contract_type" required>
                <option value="">Select Type</option>
                <option value="domain"           {{ old('contract_type', $contract->contract_type ?? '') === 'domain'               ? 'selected' : '' }}>Domain Renewal</option>
                <option value="hosting"          {{ old('contract_type', $contract->contract_type ?? '') === 'hosting'              ? 'selected' : '' }}>Hosting Renewal</option>
                <option value="domain-hosting"   {{ old('contract_type', $contract->contract_type ?? '') === 'domain-hosting'       ? 'selected' : '' }}>Domain + Hosting Renewal</option>
                <option value="hosting-email"    {{ old('contract_type', $contract->contract_type ?? '') === 'hosting-email'        ? 'selected' : '' }}>Hosting + Email Renewal</option>
                <option value="hosting-webmail"  {{ old('contract_type', $contract->contract_type ?? '') === 'hosting-webmail'      ? 'selected' : '' }}>Hosting + Webmail Renewal</option>
                <option value="domain-hosting-email" {{ old('contract_type', $contract->contract_type ?? '') === 'domain-hosting-email' ? 'selected' : '' }}>Domain + Hosting + Email Renewal</option>
                <option value="seo"              {{ old('contract_type', $contract->contract_type ?? '') === 'seo'                  ? 'selected' : '' }}>SEO</option>
                <option value="new"              {{ old('contract_type', $contract->contract_type ?? '') === 'new'                  ? 'selected' : '' }}>New...</option>
            </select>
            @error('contract_type') <div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div> @enderror
        </div>

        <!-- Custom Contract Type -->
        <div class="col-md-6" id="custom_contract_type_container" style="{{ $showCustom ? '' : 'display:none;' }}">
            <label for="custom_contract_type" class="form-label fw-500" style="font-size:0.875rem;">Enter New Contract Type</label>
            <input type="text" class="form-control" id="custom_contract_type" name="custom_contract_type"
                   value="{{ old('custom_contract_type', '') }}">
            @error('custom_contract_type') <div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div> @enderror
        </div>

        <!-- Value -->
        <div class="col-md-6">
            <label for="value" class="form-label fw-500" style="font-size:0.875rem;">Contract Value (₹)</label>
            <input type="number" step="0.01" class="form-control" id="value" name="value"
                   value="{{ old('value', $contract->value ?? '') }}">
            @error('value') <div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div> @enderror
        </div>

        <!-- Start Date -->
        <div class="col-md-6">
            <label for="start_date" class="form-label fw-500" style="font-size:0.875rem;">
                Start Date <span class="text-danger">*</span>
            </label>
            <input type="date" class="form-control" id="start_date" name="start_date"
                   value="{{ old('start_date', !empty($contract->start_date) ? \Carbon\Carbon::parse($contract->start_date)->format('Y-m-d') : '') }}" required>
            @error('start_date') <div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div> @enderror
        </div>

        <!-- End Date -->
        <div class="col-md-6">
            <label for="end_date" class="form-label fw-500" style="font-size:0.875rem;">End Date</label>
            <input type="date" class="form-control" id="end_date" name="end_date"
                   value="{{ old('end_date', !empty($contract->end_date) ? \Carbon\Carbon::parse($contract->end_date)->format('Y-m-d') : '') }}">
            @error('end_date') <div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div> @enderror
        </div>

        <!-- Description -->
        <div class="col-md-12">
            <label for="description" class="form-label fw-500" style="font-size:0.875rem;">Description / Scope</label>
            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $contract->des ?? '') }}</textarea>
            @error('description') <div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div> @enderror
        </div>

        <!-- Buttons -->
        <div class="col-md-12 pt-3 mt-2 border-top text-end">
            <button type="button" class="btn btn-light rounded-pill border px-4 me-2" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary rounded-pill px-4" style="background:#006666; border:none;">
                {{ !empty($contract->id) ? 'Update Contract' : 'Save Contract' }}
            </button>
        </div>
    </form>
</div>

<script>
    (function() {
        // Contract Type toggle
        const typeDropdown = document.getElementById('contract_type');
        const customContainer = document.getElementById('custom_contract_type_container');
        const customInput = document.getElementById('custom_contract_type');

        function toggleCustomField() {
            if (!typeDropdown || !customContainer) return;
            if (typeDropdown.value === 'new') {
                customContainer.style.display = 'block';
                if (customInput) customInput.required = true;
            } else {
                customContainer.style.display = 'none';
                if (customInput) customInput.required = false;
            }
        }

        if (typeDropdown) {
            toggleCustomField();
            typeDropdown.addEventListener('change', toggleCustomField);
        }
    })();
</script>
