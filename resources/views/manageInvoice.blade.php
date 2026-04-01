@extends('layout')
@section('title', 'Manage Invoice - eseCRM')

@section('content')
    <style>
        .mp-sidebar-sticky { position: sticky; top: 85px; z-index: 10; }
        .mp-item-row { background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; position: relative; transition: all 0.2s; }
        .mp-item-row:hover { border-color: #006666; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .mp-item-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; gap: 1rem; }
        .mp-item-body { display: grid; grid-template-columns: 1fr 1fr 1.5fr 1fr; gap: 1rem; }
        .mp-summary-card { background: #fff; border: 1px solid #e0e0e0; border-radius: 16px; padding: 1.5rem; }
        .mp-summary-row { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; color: #5f6368; }
        .mp-summary-total { font-size: 1.5rem; font-weight: 700; color: #006666; margin-top: 1rem; padding-top: 1rem; border-top: 2px solid #f1f3f4; }
        .ml-card { background: #fff; border: 1px solid #e0e0e0; border-radius: 16px; transition: all 0.3s ease; height: 100%; border-0; box-shadow: 0 2px 12px rgba(0,0,0,0.03); }
        .ml-card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f3f4; display: flex; align-items: center; gap: 0.75rem; }
        .ml-card-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .ml-card-title { font-size: 1rem; font-weight: 600; color: #202124; margin: 0; }
        .ml-card-body { padding: 1.5rem; }
        .inv-status-pill { padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .bank-info-bg { background: #f8f9fa; border: 1px dashed #ced4da; }
        .divider { height: 1px; width: 100%; }
        .border-dashed { border-style: dashed !important; }
        @media (max-width: 991px) { 
            .mp-sidebar-sticky { position: static; } 
            .mp-item-body { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 575px) {
            .mp-item-body { grid-template-columns: 1fr; }
        }
    </style>

    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i>
            {{ !empty($invoice->id) ? 'Edit Invoice' : 'New Invoice' }}
            <a href="/signout" class="logoutbtn"><i class='bx bx-log-out'></i></a>
        </div>

        <div class="container-fluid py-4">
            <form action="/manage-invoice" method="POST" id="invoiceForm">
                @csrf
                @if(!empty($invoice->id))
                    <input type="hidden" name="id" value="{{ $invoice->id }}">
                @endif
                <input type="hidden" name="project_id" value="{{ old('project_id', $invoice->project_id ?? $project_id ?? '') }}">

                {{-- ── Premium Header & Breadcrumbs ── --}}
                <div class="leads-toolbar mb-4 bg-white p-3 rounded-3 shadow-sm border">
                    <div class="leads-toolbar-left gap-3">
                        <a href="/invoices" class="lb-icon-btn text-decoration-none">
                            <i class="bx bx-arrow-back"></i>
                        </a>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h4 class="mb-0 fw-700 text-dark">
                                    {{ !empty($invoice->id) ? 'Edit Invoice' : 'Create New Invoice' }}
                                </h4>
                                @if(!empty($invoice->id))
                                    <span class="inv-status-pill" style="background:#00666615;color:#006666;">
                                        INV-{{ $invoice->invoice_number }}
                                    </span>
                                @endif
                            </div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                                    <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Home</a></li>
                                    <li class="breadcrumb-item"><a href="/invoices" class="text-decoration-none">Invoices</a></li>
                                    <li class="breadcrumb-item active">{{ !empty($invoice->id) ? 'Edit' : 'Create' }}</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    {{-- ── Left Column: Main Form & Items ── --}}
                    <div class="col-lg-8">
                        <!-- Invoice Information -->
                        <div class="ml-card mb-4 shadow-sm">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:#00666615;color:#006666;">
                                    <i class='bx bx-file'></i>
                                </div>
                                <h5 class="ml-card-title">Invoice Information</h5>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">
                                    <div class="col-md-4 form-group">
                                        <label for="invoice_number" class="form-label fw-600 small">Invoice Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class='bx bx-hash'></i></span>
                                            <input type="text" class="form-control border-start-0" id="invoice_number" name="invoice_number"
                                                maxlength="20" placeholder="INV-001"
                                                value="{{ old('invoice_number', $invoice->invoice_number ?? null) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="invoice_type" class="form-label fw-600 small">Type <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class='bx bx-category'></i></span>
                                            <select class="form-control border-start-0" id="invoice_type" name="invoice_type" required>
                                                <option value="invoice" @if(old('invoice_type', $invoice->invoice ?? '') == 'invoice') selected @endif>Invoice</option>
                                                <option value="proforma" @if(old('invoice_type', $invoice->invoice ?? '') == 'proforma') selected @endif>Proforma Invoice</option>
                                                <option value="tax" @if(old('invoice_type', $invoice->invoice ?? '') == 'tax') selected @endif>Tax Invoice</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="status" class="form-label fw-600 small">Status</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class='bx bx-info-circle'></i></span>
                                            <select class="form-control border-start-0" id="status" name="status">
                                                <option value="unpaid" @if(old('status', $invoice->status ?? 'unpaid') == 'unpaid') selected @endif>Unpaid</option>
                                                <option value="paid" @if(old('status', $invoice->status ?? '') == 'paid') selected @endif>Paid</option>
                                                <option value="partial" @if(old('status', $invoice->status ?? '') == 'partial') selected @endif>Partially Paid</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12 form-group mt-2">
                                        <label for="client_id" class="form-label fw-600 small">Select Client <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class='bx bx-user'></i></span>
                                            <select class="selectpicker form-select border-start-0" id="client_id" name="client_id"
                                                data-live-search="true" data-width="calc(100% - 90px)" required>
                                                <option value="">Search for a client...</option>
                                                @foreach($clients as $client)
                                                    @php $location = json_decode(($client->location ?? '["","","","",""]'), true) @endphp
                                                    <option value="{{ $client->id }}" 
                                                        data-name="{{ $client->name ?? '' }}"
                                                        data-company="{{ $client->company ?? '' }}"
                                                        data-email="{{ $client->email ?? '' }}" 
                                                        data-phone="{{ $client->mob ?? '' }}"
                                                        data-gstno="{{ $client->gstno ?? '' }}"
                                                        data-address="{{ $location[0] ?? '' }}" 
                                                        @php 
                                                            $isSelected = (old('client_id', $invoice->client_id ?? '') == $client->id) || (!empty($project_id) && $client->project_id == $project_id);
                                                        @endphp
                                                        @if($isSelected) selected @endif>
                                                        {{ $client->name }} ({{ $client->company }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-indigo" data-bs-toggle="modal"
                                                data-bs-target="#addClientModal" title="Add New Client" style="width: 45px;">
                                                <i class='bx bx-plus'></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-3 form-group mt-2">
                                        <label for="date" class="form-label fw-600 small">Invoice Date <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class='bx bx-calendar'></i></span>
                                            <input type="date" class="form-control border-start-0" id="date" name="date"
                                                value="{{ old('date', $invoice && $invoice->date ? \Carbon\Carbon::parse($invoice->date)->format('Y-m-d') : now()->format('Y-m-d')) }}"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-3 form-group mt-2">
                                        <label for="due_date" class="form-label fw-600 small">Due Date</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class='bx bx-calendar-check'></i></span>
                                            <input type="date" class="form-control border-start-0" id="due_date" name="due_date"
                                                value="{{ old('due_date', $invoice && $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') : '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6 form-group mt-2">
                                        <label for="reference" class="form-label fw-600 small">Reference / PO #</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class='bx bx-note'></i></span>
                                            <input type="text" class="form-control border-start-0" id="reference" name="reference"
                                                maxlength="25" placeholder="Order or Reference Number"
                                                value="{{ old('reference', $invoice->reference ?? '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Billing & Shipping -->
                        <div class="ml-card mb-4 shadow-sm">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:#34a85315;color:#34a853;">
                                    <i class='bx bx-map'></i>
                                </div>
                                <h5 class="ml-card-title">Billing & Shipping</h5>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">
                                    <div class="col-md-6 form-group">
                                        <label for="billing_address" class="form-label fw-600 small">Billing Address</label>
                                        <textarea class="form-control bg-light" name="billing_address" id="billing_address" rows="3"
                                            placeholder="Standard billing address">{{ old('billing_address', $invoice->billing_address ?? '') }}</textarea>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="shipping_address" class="form-label fw-600 small">Shipping Address</label>
                                        <textarea class="form-control bg-light" name="shipping_address" id="shipping_address" rows="3"
                                            placeholder="Standard shipping address">{{ old('shipping_address', $invoice->shipping_address ?? '') }}</textarea>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="client_gst" class="form-label fw-600 small">GST No.</label>
                                        <div class="input-group shadow-none">
                                            <span class="input-group-text bg-light border-end-0"><i class='bx bx-id-card'></i></span>
                                            <input type="text" class="form-control border-start-0" name="client_gst" id="client_gst"
                                                placeholder="GSTIN Number"
                                                value="{{ old('client_gst', $invoice->client_gstno ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-8 d-flex align-items-end">
                                        <div class="form-check pb-2">
                                            <input type="checkbox" class="form-check-input" id="recurring_invoice"
                                                name="recurring_invoice" value="1" @if(old('recurring_invoice', $invoice->recurring_invoice ?? false)) checked @endif>
                                            <label class="form-check-label fw-600 text-muted" for="recurring_invoice">Enable recurring invoice</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment & Bank Details -->
                        <div class="ml-card mb-4 shadow-sm">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:#f2990015;color:#f29900;">
                                    <i class='bx bx-credit-card'></i>
                                </div>
                                <h5 class="ml-card-title">Payment & Bank Details</h5>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">
                                    <div class="col-md-6 form-group">
                                        <label for="payment_mode" class="form-label fw-600 small">Payment Mode</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class='bx bx-wallet'></i></span>
                                            <select class="form-control border-start-0" id="payment_mode" name="payment_mode">
                                                <option value="">Select Mode...</option>
                                                <option value="cash" @if(old('payment_mode', $invoice->payment_mode ?? '') == 'cash') selected @endif>Cash</option>
                                                <option value="card" @if(old('payment_mode', $invoice->payment_mode ?? '') == 'card') selected @endif>Credit/Debit Card</option>
                                                <option value="bank" @if(old('payment_mode', $invoice->payment_mode ?? '') == 'bank') selected @endif>Bank Transfer</option>
                                                <option value="paypal" @if(old('payment_mode', $invoice->payment_mode ?? '') == 'paypal') selected @endif>PayPal</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="currency" class="form-label fw-600 small">Currency</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class='bx bx-globe'></i></span>
                                            <select class="form-control border-start-0" id="currency" name="currency">
                                                <option value="INR" @if(old('currency', $invoice->currency ?? 'INR') == 'INR') selected @endif>INR - Indian Rupee</option>
                                                <option value="USD" @if(old('currency', $invoice->currency ?? '') == 'USD') selected @endif>USD - US Dollar</option>
                                                <option value="EUR" @if(old('currency', $invoice->currency ?? '') == 'EUR') selected @endif>EUR - Euro</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <div class="p-3 rounded-3 bank-info-bg">
                                            <h6 class="fw-700 mb-3 text-dark small text-uppercase letter-spacing-1">Company Bank Details</h6>
                                            @php 
                                                $company = session('companies');
                                                $companyBankDetails = json_decode($invoice->bank_details ?? $company->bank_details ?? '["","","","",""]', true);
                                                if (!is_array($companyBankDetails)) $companyBankDetails = ["","","","",""];
                                            @endphp
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="small fw-600 text-muted mb-1">Bank Name</label>
                                                    <input type="text" class="form-control form-control-sm" name="bank_details[]"
                                                        value="{{ $companyBankDetails[0] ?? '' }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="small fw-600 text-muted mb-1">Account Holder</label>
                                                    <input type="text" class="form-control form-control-sm" name="bank_details[]"
                                                        value="{{ $companyBankDetails[1] ?? '' }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="small fw-600 text-muted mb-1">Account Number</label>
                                                    <input type="text" class="form-control form-control-sm" name="bank_details[]"
                                                        value="{{ $companyBankDetails[2] ?? '' }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small fw-600 text-muted mb-1">IFSC / SWIFT</label>
                                                    <input type="text" class="form-control form-control-sm" name="bank_details[]"
                                                        value="{{ $companyBankDetails[3] ?? '' }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small fw-600 text-muted mb-1">UPI ID</label>
                                                    <input type="text" class="form-control form-control-sm" name="bank_details[]"
                                                        value="{{ $companyBankDetails[4] ?? '' }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Items -->
                        <div class="ml-card mb-4 shadow-sm border-0">
                            <div class="ml-card-header bg-white">
                                <div class="ml-card-icon" style="background:#00666615;color:#006666;">
                                    <i class='bx bx-list-ol'></i>
                                </div>
                                <h5 class="ml-card-title">Invoice Items</h5>
                            </div>
                            <div class="ml-card-body">
                                <div id="invoiceItemsBody">
                                    @php
                                        $available_taxes = [];
                                        $company_taxes = !empty($companies->tax) ? explode(',', $companies->tax) : [];
                                        $tax_labels = ['CGST', 'SGST', 'IGST', 'VAT', 'Tax'];
                                        foreach ($company_taxes as $tax_index => $tax_rate_str) {
                                            $tax_rate_str = trim($tax_rate_str);
                                            if (!is_numeric($tax_rate_str)) continue;
                                            $tax_rate = floatval($tax_rate_str);
                                            if ($tax_rate <= 0) continue;
                                            $tax_value = $tax_rate / 100.0;
                                            $tax_label = ($tax_labels[$tax_index] ?? "Tax") . " {$tax_rate_str}%";
                                            $available_taxes[] = ['value' => $tax_index . ":" . number_format($tax_value, 4, '.', ''), 'label' => $tax_label];
                                        }
                                    @endphp

                                    @forelse($invoiceItems as $index => $item)
                                        <div class="mp-item-row shadow-sm">
                                            <div class="mp-item-header">
                                                <div class="flex-grow-1">
                                                    <input type="text" class="form-control fw-600 border-0 bg-transparent p-0 fs-5 item-name"
                                                        name="invoice_items[{{ $index }}][short_description]"
                                                        placeholder="Item Name" value="{{ $item->short_description ?? '' }}">
                                                </div>
                                                <button type="button" class="btn btn-link text-danger p-0 removeRowButton" title="Remove Item">
                                                    <i class="bx bx-trash fs-5"></i>
                                                </button>
                                            </div>
                                            <div class="mb-2">
                                                <textarea class="form-control border-0 bg-light small item-longdesc" rows="1"
                                                    name="invoice_items[{{ $index }}][long_description]"
                                                    placeholder="Add a detailed description...">{{ $item->long_description ?? '' }}</textarea>
                                            </div>
                                            <div class="mp-item-body">
                                                <div>
                                                    <label class="small fw-600 text-muted mb-1">SAC Code</label>
                                                    <input type="text" class="form-control form-control-sm item-sac_code"
                                                        name="invoice_items[{{ $index }}][sac_code]"
                                                        value="{{ $item->sac_code ?? '998314' }}">
                                                </div>
                                                <div>
                                                    <label class="small fw-600 text-muted mb-1">Qty / Hrs</label>
                                                    <input type="number" class="form-control form-control-sm item-qty text-center"
                                                        name="invoice_items[{{ $index }}][quantity]"
                                                        value="{{ $item->quantity ?? 1 }}" min="0" step="any" required>
                                                </div>
                                                <div>
                                                    <label class="small fw-600 text-muted mb-1">Rate (₹)</label>
                                                    <input type="number" class="form-control form-control-sm item-price text-end"
                                                        name="invoice_items[{{ $index }}][price]"
                                                        value="{{ number_format($item->price ?? 0, 2, '.', '') }}" min="0" step="any" required>
                                                </div>
                                                <div>
                                                    <label class="small fw-600 text-muted mb-1">Tax</label>
                                                    @php
                                                        $selected_taxes = [];
                                                        foreach ($available_taxes as $tax_option) {
                                                            list($tax_idx, $tax_rate_decimal) = explode(':', $tax_option['value']);
                                                            $tax_rate_percent = floatval($tax_rate_decimal) * 100;
                                                            if ($tax_idx == 0 && !empty($item->cgst_percent) && abs(floatval($item->cgst_percent) - $tax_rate_percent) < 0.001) $selected_taxes[] = $tax_option['value'];
                                                            elseif ($tax_idx == 1 && !empty($item->sgst_percent) && abs(floatval($item->sgst_percent) - $tax_rate_percent) < 0.001) $selected_taxes[] = $tax_option['value'];
                                                            elseif ($tax_idx == 2 && !empty($item->igst_percent) && abs(floatval($item->igst_percent) - $tax_rate_percent) < 0.001) $selected_taxes[] = $tax_option['value'];
                                                            elseif ($tax_idx == 3 && !empty($item->vat_percent) && abs(floatval($item->vat_percent) - $tax_rate_percent) < 0.001) $selected_taxes[] = $tax_option['value'];
                                                        }
                                                    @endphp
                                                    <select class="selectpicker form-control item-tax" multiple
                                                        data-selected-text-format="count > 1" data-width="100%" data-container="body"
                                                        name="invoice_items[{{ $index }}][tax_rate][]">
                                                        @foreach($available_taxes as $tax)
                                                            <option value="{{ $tax['value'] }}" @if(in_array($tax['value'], $selected_taxes)) selected @endif>{{ $tax['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                                <span class="small text-muted fw-600">Line Total</span>
                                                <span class="fw-700 text-dark line-total">₹ 0.00</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-5 no-items-msg">
                                            <i class="bx bx-package fs-1 text-muted opacity-25"></i>
                                            <p class="text-muted small mt-2">No items added yet. Click "Add New Item" to begin.</p>
                                        </div>
                                    @endforelse
                                </div>
                                <div class="mt-4 pt-3 border-top">
                                    <button type="button" class="btn btn-indigo rounded-pill px-4" id="addItemButton">
                                        <i class="bx bx-plus me-1"></i> Add New Item
                                    </button>
                                </div>

                                <div class="mt-4 pt-4 border-top">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-600 small text-muted">Internal Admin Note</label>
                                            <textarea class="form-control bg-light" name="admin_note" rows="2"
                                                placeholder="Not visible to client">{{ old('admin_note', $invoice->admin_note ?? '') }}</textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-600 small text-muted">Client Note</label>
                                            <textarea class="form-control bg-light" name="client_note" rows="2"
                                                placeholder="Visible on PDF">{{ old('client_note', $invoice->client_note ?? '') }}</textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-600 small text-muted">Terms & Conditions</label>
                                            <textarea class="form-control bg-light" name="terms" rows="3"
                                                placeholder="Legal terms and conditions">{{ old('terms', $invoice->terms ?? '') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> {{-- End col-lg-8 --}}

                    {{-- ── Right Column: Sticky Sidebar ── --}}
                    <div class="col-lg-4">
                        <div class="mp-sidebar-sticky">
                            <div class="mp-summary-card shadow-sm border-0">
                                <h5 class="fw-700 mb-4 text-dark d-flex align-items-center gap-2">
                                    <i class="bx bx-calculator text-primary"></i>
                                    Invoice Summary
                                </h5>

                                <div class="mp-summary-row mt-3">
                                    <span>Subtotal</span>
                                    <span class="fw-600 text-dark" id="subTotal">0.00</span>
                                </div>

                                <div class="mp-summary-row text-success small" id="discountBeforeTaxRow" style="display:none !important;">
                                    <span>Discount (Before Tax)</span>
                                    <span id="discountBeforeTaxAmount">(-0.00)</span>
                                </div>

                                <div class="mp-summary-row">
                                    <span>Total Tax</span>
                                    <span class="fw-600 text-dark" id="totalTax">0.00</span>
                                </div>

                                <div class="divider my-3 border-top border-dashed"></div>

                                <div class="mb-3">
                                    <label class="form-label small fw-600 text-muted mb-1">Apply Discount</label>
                                    <div class="input-group input-group-sm">
                                        <select name="discount_type" id="discountApplicationType" class="form-select bg-light border-end-0">
                                            @php $currentAppType = old('discount_type', $invoice->discount_type ?? 'none'); @endphp
                                            <option value="none" @if($currentAppType == 'none') selected @endif>None</option>
                                            <option value="before-tax" @if($currentAppType == 'before-tax') selected @endif>Before Tax</option>
                                            <option value="after-tax" @if($currentAppType == 'after-tax') selected @endif>After Tax</option>
                                        </select>
                                        <select name="discount_mode" id="discountValueType" class="form-select bg-light border-end-0" style="max-width: 70px;">
                                            @php $currentValueType = old('discount_mode', $invoice->discount_mode ?? 'flat'); @endphp
                                            <option value="flat" @if($currentValueType == 'flat') selected @endif>Flat</option>
                                            <option value="percentage" @if($currentValueType == 'percentage') selected @endif>%</option>
                                        </select>
                                        <input type="number" class="form-control bg-light border-start-0 text-end" name="discount_value"
                                            id="discountValue" step="any" min="0"
                                            value="{{ old('discount', number_format($invoice->discount ?? 0, 2, '.', '')) }}"
                                            placeholder="0.00">
                                    </div>
                                </div>

                                <div class="mp-summary-row text-success small" id="discountAmountRow" style="display:none !important;">
                                    <span>Net Discount</span>
                                    <span id="discountAmountCalculated">(-0.00)</span>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-600 text-muted mb-1">Advance / Adjustment</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light border-end-0"><i class="bx bx-rupee"></i></span>
                                        <input type="number" class="form-control bg-light border-start-0 text-end"
                                            name="adjustment" id="adjustment" step="any"
                                            value="{{ old('adjustment', number_format($invoice->adjustment ?? 0, 2, '.', '')) }}"
                                            placeholder="0.00">
                                    </div>
                                </div>

                                <div class="mp-summary-total">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small fw-600 text-muted">Grand Total</span>
                                        <span>₹<span id="grandTotal">0.00</span></span>
                                    </div>
                                </div>
                                <input type="hidden" name="gtAmount" id="gtAmount" value="0" />

                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-indigo py-2 rounded-pill fw-600 shadow-sm">
                                        <i class="bx bx-save me-1"></i> Save Invoice
                                    </button>
                                    <button type="button" class="btn btn-outline-info py-2 rounded-pill fw-600" id="previewInvoiceBtn">
                                        <i class="bx bx-show me-1"></i> Live Preview
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div> {{-- End col-lg-4 --}}
                </div> {{-- End row g-4 --}}
            </form>
        </div>
    </section>

    <!-- Modal for New Client -->
    <div class="modal fade" id="addClientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form action="/manage-invoice-client" method="POST" id="addClientForm">
                @csrf
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-bottom-0 pt-4 px-4">
                        <h5 class="fw-700 text-dark">Add New Client</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-600">Full Name*</label>
                                <input type="text" class="form-control" name="name" required placeholder="John Doe">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-600">Email Address*</label>
                                <input type="email" class="form-control" name="email" required placeholder="john@example.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-600">Mobile Number*</label>
                                <input type="text" class="form-control" name="mob" required value="91">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-600">Company Name*</label>
                                <input type="text" class="form-control" name="company" required placeholder="Acme Corp">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-600">Address / Location</label>
                                <textarea class="form-control" name="address[]" rows="2" placeholder="Street, City, State, ZIP"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-600">GST Number</label>
                                <input type="text" class="form-control" name="gstno" placeholder="Optional">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pb-4 px-4">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-600" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-indigo rounded-pill px-4 fw-600">
                            <i class="bx bx-save me-1"></i> Save Client
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        const availableTaxes = @json($available_taxes);

        function formatCurrency(amount) {
            return parseFloat(amount || 0).toFixed(2);
        }

        function recalculateTotals() {
            try {
                const discountAppType = $('#discountApplicationType').val();
                const discountValueType = $('#discountValueType').val();
                const discountValue = parseFloat($('#discountValue').val()) || 0;
                const adjustment = parseFloat($('#adjustment').val()) || 0;

                let initialSubTotal = 0;
                let totalTax = 0;
                let calculatedDiscountAmount = 0;

                const $rows = $('.mp-item-row');
                $rows.each(function() {
                    const $row = $(this);
                    const qty = parseFloat($row.find('.item-qty').val()) || 0;
                    const price = parseFloat($row.find('.item-price').val()) || 0;
                    const lineSubTotal = qty * price;
                    
                    let lineTaxAmount = 0;
                    const $taxSelect = $row.find('.item-tax');
                    const selectedTaxValues = $taxSelect.val();
                    if (selectedTaxValues) {
                        selectedTaxValues.forEach(val => {
                            const rate = parseFloat(val.split(':')[1]);
                            if (rate > 0) lineTaxAmount += lineSubTotal * rate;
                        });
                    }

                    $row.data('initialSubtotal', lineSubTotal);
                    $row.data('initialTax', lineTaxAmount);
                    initialSubTotal += lineSubTotal;

                    $row.find('.line-total').text('₹ ' + formatCurrency(lineSubTotal + lineTaxAmount));
                });

                // Calculate Net Discount
                if (discountValue > 0) {
                    let discountBase = (discountAppType === 'before-tax') ? initialSubTotal : 
                                       (initialSubTotal + $rows.toArray().reduce((sum, el) => sum + parseFloat($(el).data('initialTax') || 0), 0));
                    
                    calculatedDiscountAmount = (discountValueType === 'percentage') ? (discountBase * (discountValue / 100)) : discountValue;
                    calculatedDiscountAmount = Math.min(calculatedDiscountAmount, discountBase);
                }

                // Distribution & Final Taxes
                let finalSubTotal = initialSubTotal;
                totalTax = 0;

                if (discountAppType === 'before-tax') {
                    finalSubTotal = initialSubTotal - calculatedDiscountAmount;
                    $rows.each(function() {
                        const rowSub = parseFloat($(this).data('initialSubtotal') || 0);
                        const rowRatio = initialSubTotal > 0 ? (rowSub / initialSubTotal) : 0;
                        const rowDiscount = calculatedDiscountAmount * rowRatio;
                        const rowFinalSub = rowSub - rowDiscount;
                        
                        const $taxSelect = $(this).find('.item-tax');
                        const selectedTaxes = $taxSelect.val();
                        if (selectedTaxes) {
                            selectedTaxes.forEach(val => {
                                const rate = parseFloat(val.split(':')[1]);
                                totalTax += rowFinalSub * rate;
                            });
                        }
                    });
                } else {
                    $rows.each(function() { totalTax += parseFloat($(this).data('initialTax') || 0); });
                }

                const grandTotal = (discountAppType === 'before-tax') ? (finalSubTotal + totalTax - adjustment) : 
                                   (discountAppType === 'after-tax') ? (initialSubTotal + totalTax - calculatedDiscountAmount - adjustment) : 
                                   (initialSubTotal + totalTax - adjustment);

                $('#subTotal').text(formatCurrency(initialSubTotal));
                $('#totalTax').text(formatCurrency(totalTax));
                $('#grandTotal').text(formatCurrency(grandTotal));
                $('#gtAmount').val(formatCurrency(grandTotal));

                $('#discountAmountRow').toggle(calculatedDiscountAmount > 0);
                $('#discountAmountCalculated').text(`(-${formatCurrency(calculatedDiscountAmount)})`);
                $('#discountBeforeTaxRow').toggle(discountAppType === 'before-tax' && calculatedDiscountAmount > 0);
                $('#discountBeforeTaxAmount').text(`(-${formatCurrency(calculatedDiscountAmount)})`);

            } catch (e) { console.error("Calc Error:", e); }
        }

        $('#addItemButton').on('click', function() {
            const index = $('.mp-item-row').length;
            $('.no-items-msg').hide();

            let taxOptions = '';
            availableTaxes.forEach(t => { taxOptions += `<option value="${t.value}">${t.label}</option>`; });

            const html = `
                <div class="mp-item-row shadow-sm animate__animated animate__fadeInUp">
                    <div class="mp-item-header">
                        <div class="flex-grow-1">
                            <input type="text" class="form-control fw-600 border-0 bg-transparent p-0 fs-5 item-name"
                                name="invoice_items[${index}][short_description]" placeholder="Item Name">
                        </div>
                        <button type="button" class="btn btn-link text-danger p-0 removeRowButton"><i class="bx bx-trash fs-5"></i></button>
                    </div>
                    <div class="mb-2">
                        <textarea class="form-control border-0 bg-light small item-longdesc" rows="1"
                            name="invoice_items[${index}][long_description]" placeholder="Description..."></textarea>
                    </div>
                    <div class="mp-item-body">
                        <div>
                            <label class="small fw-600 text-muted mb-1">SAC Code</label>
                            <input type="text" class="form-control form-control-sm item-sac_code" name="invoice_items[${index}][sac_code]" value="998314">
                        </div>
                        <div>
                            <label class="small fw-600 text-muted mb-1">Qty / Hrs</label>
                            <input type="number" class="form-control form-control-sm item-qty text-center" name="invoice_items[${index}][quantity]" value="1" step="any">
                        </div>
                        <div>
                            <label class="small fw-600 text-muted mb-1">Rate (₹)</label>
                            <input type="number" class="form-control form-control-sm item-price text-end" name="invoice_items[${index}][price]" value="0.00" step="any">
                        </div>
                        <div>
                            <label class="small fw-600 text-muted mb-1">Tax</label>
                            <select class="selectpicker form-control item-tax" multiple name="invoice_items[${index}][tax_rate][]" data-container="body" data-width="100%">${taxOptions}</select>
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <span class="small text-muted fw-600">Line Total</span>
                        <span class="fw-700 text-dark line-total">₹ 0.00</span>
                    </div>
                </div>`;
            
            $('#invoiceItemsBody').append(html);
            $('.selectpicker').selectpicker('refresh');
            recalculateTotals();
        });

        $(document).on('click', '.removeRowButton', function() {
            $(this).closest('.mp-item-row').remove();
            if ($('.mp-item-row').length === 0) $('.no-items-msg').show();
            recalculateTotals();
        });

        $('#client_id').on('change', function() {
            const opt = $(this).find(':selected');
            if (!opt.val()) return;
            $('#billing_address').val(`${opt.data('name')}\n${opt.data('company')}\n${opt.data('address')}`);
            $('#shipping_address').val($('#billing_address').val());
            $('#client_gst').val(opt.data('gstno') || '');
        });

        // Event Listeners for calculations
        $(document).on('input', '.item-qty, .item-price, #discountValue, #adjustment', recalculateTotals);
        $(document).on('change', '.item-tax, #discountApplicationType, #discountValueType', recalculateTotals);

        $(document).ready(function() {
            $('.selectpicker').selectpicker('refresh');
            recalculateTotals();
        });
    </script>
@endsection
