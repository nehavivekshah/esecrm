@extends('layout')
@section('title', 'Manage Invoice - eseCRM')

@section('content')
    <style>
        .mp-sidebar-sticky { position: sticky; top: 84px; z-index: 10; }
        .mp-item-row { background: #fff; border: 1px solid #e8eaed; border-radius: 16px; padding: 1.25rem; margin-bottom: 1.25rem; position: relative; transition: all 0.2s; }
        .mp-item-row:hover { border-color: #006666; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .mp-item-row-header { display: flex; align-items: center; gap: 10px; margin-bottom: 15px; border-bottom: 1px solid #f1f3f4; padding-bottom: 10px; }
        .mp-item-num { width: 24px; height: 24px; background: #006666; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700; }
        .mp-item-row-title { font-size: 0.88rem; font-weight: 700; color: #202124; }
        .mp-item-row-body { display: grid; grid-template-columns: 2fr 2fr 0.8fr 1.2fr 1.5fr 1.2fr; gap: 12px; }
        .mp-item-label { display: block; font-size: 0.68rem; font-weight: 700; color: #80868b; text-transform: uppercase; margin-bottom: 4px; }
        .mp-item-amount-val { font-size: 0.9rem; font-weight: 700; color: #202124; display: block; margin-top: 6px; }
        .mp-add-item-btn { width: 100%; padding: 12px; background: #fff; border: 1px dashed #ced4da; border-radius: 12px; color: #5f6368; font-weight: 600; font-size: 0.85rem; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 6px; }
        .mp-add-item-btn:hover { background: #f8f9fa; border-color: #006666; color: #006666; }
        .mp-summary-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; font-size: 0.85rem; }
        .mp-summary-label { color: #80868b; font-weight: 500; }
        .mp-summary-val { color: #202124; font-weight: 700; }
        .mp-summary-total { margin-top: 15px; padding-top: 15px; border-top: 2px solid #f1f3f4; display: flex; justify-content: space-between; align-items: center; }
        .mp-grand-total-val { font-size: 1.4rem; font-weight: 800; color: #006666; }
        .ml-card { background: #fff; border: 1px solid #e8eaed; border-radius: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
        .ml-card-header { padding: 16px 20px; border-bottom: 1px solid #f1f3f4; display: flex; align-items: center; gap: 12px; }
        .ml-card-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
        .ml-card-title { font-size: 1rem; font-weight: 700; color: #202124; margin: 0; }
        .ml-card-sub { font-size: 0.72rem; color: #80868b; display: block; margin-top: 1px; font-weight: 400; }
        .ml-label { display: block; font-size: 0.75rem; font-weight: 700; color: #5f6368; margin-bottom: 6px; }
        .mp-steps { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 30px; }
        .mp-step { display: flex; align-items: center; gap: 8px; color: #dadce0; }
        .mp-step-active { color: #006666; }
        .mp-step-done { color: #34a853; }
        .mp-step-num { width: 24px; height: 24px; border-radius: 50%; border: 2px solid currentColor; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; }
        .mp-step-label { font-size: 0.85rem; font-weight: 600; }
        .mp-step-line { height: 2px; width: 40px; background: #f1f3f4; border-radius: 2px; }
        .mp-line-done { background: #34a853; }
        .mp-autoresize { resize: none; overflow: hidden; }
        .inv-status-pill { padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .bank-info-bg { background: #f8f9fa; border: 1px dashed #ced4da; border-radius: 12px; padding: 16px; }
        @media (max-width: 1200px) { .mp-item-row-body { grid-template-columns: 1.5fr 1.5fr 0.8fr 1fr 1fr 1fr; } }
        @media (max-width: 991px) { .mp-sidebar-sticky { position: static; } .mp-item-row-body { grid-template-columns: 1fr 1fr; gap: 15px; } }
        @media (max-width: 575px) { .mp-item-row-body { grid-template-columns: 1fr; } }
    </style>

    <section class="task__section">
        @include('inc.header', ['title' => !empty($invoice->id) ? 'Edit Invoice' : 'Create Invoice'])

        <div class="dash-container">
            <form id="invoiceForm" action="/manage-invoice" method="POST">
                @csrf
                <input type="hidden" name="id" value="{{ $invoice->id ?? '' }}">


            {{-- ── Page heading bar ── --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left gap-3">
                    <a href="/invoices" class="btn kb-action-btn" title="Back to Invoices"
                       style="width:34px;height:34px;background:#f1f3f4;color:#5f6368;">
                        <i class="bx bx-arrow-back"></i>
                    </a>
                    <div>
                        @if(!empty($invoice->id))
                            <span class="lb-page-count"><i class="bx bx-edit"></i> Edit Invoice</span>
                            <span class="inv-status-pill ms-2" style="background:#00666615;color:#006666;">
                                INV-{{ $invoice->invoice_number }}
                            </span>
                        @else
                            <span class="lb-page-count"><i class="bx bx-plus-circle"></i> Create New Invoice</span>
                        @endif
                    </div>
                </div>
                <div class="leads-toolbar-right gap-2">
                    <button type="submit" form="invoiceForm" class="lb-btn lb-btn-primary">
                        <i class="bx bx-save"></i> Save Invoice
                    </button>
                </div>
            </div>

            {{-- ── Progress steps ── --}}
            <div class="mp-steps mb-4">
                <div class="mp-step mp-step-done">
                    <span class="mp-step-num"><i class="bx bx-check" style="font-size:0.8rem;"></i></span>
                    <span class="mp-step-label">Invoice Info</span>
                </div>
                <div class="mp-step-line mp-line-done"></div>
                <div class="mp-step mp-step-done">
                    <span class="mp-step-num"><i class="bx bx-check" style="font-size:0.8rem;"></i></span>
                    <span class="mp-step-label">Client Details</span>
                </div>
                <div class="mp-step-line"></div>
                <div class="mp-step mp-step-active">
                    <span class="mp-step-num">3</span>
                    <span class="mp-step-label">Items &amp; Summary</span>
                </div>
            </div>

                <div class="row g-4">
                    {{-- ── Left Column: Main Form & Items ── --}}
                    <div class="col-lg-12">
                        {{-- ── Invoice Information ── --}}
                        <div class="ml-card mb-4">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(0,102,102,0.10);color:#006666;">
                                    <i class="bx bx-file"></i>
                                </div>
                                <div>
                                    <h6 class="ml-card-title">Invoice Information</h6>
                                    <span class="ml-card-sub">Number, dates, type & status</span>
                                </div>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="ml-label">Invoice Number <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-hash"></i></span>
                                            <input type="text" name="invoice_number" id="invoice_number" class="form-control"
                                                   value="{{ old('invoice_number', $invoice->invoice_number ?? '') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="ml-label">Type <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-category"></i></span>
                                            <select class="form-select" name="invoice_type" required>
                                                <option value="invoice" @if(old('invoice_type', $invoice->invoice ?? '') == 'invoice') selected @endif>Invoice</option>
                                                <option value="proforma" @if(old('invoice_type', $invoice->invoice ?? '') == 'proforma') selected @endif>Proforma</option>
                                                <option value="tax" @if(old('invoice_type', $invoice->invoice ?? '') == 'tax') selected @endif>Tax Invoice</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="ml-label">Invoice Date <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                            <input type="date" name="date" class="form-control"
                                                   value="{{ old('date', $invoice && $invoice->date ? \Carbon\Carbon::parse($invoice->date)->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="ml-label">Due Date</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-calendar-check"></i></span>
                                            <input type="date" name="due_date" class="form-control"
                                                   value="{{ old('due_date', $invoice && $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') : '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">Status</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-info-circle"></i></span>
                                            <select class="form-select" name="status">
                                                <option value="unpaid" @if(old('status', $invoice->status ?? 'unpaid') == 'unpaid') selected @endif>Unpaid</option>
                                                <option value="paid" @if(old('status', $invoice->status ?? '') == 'paid') selected @endif>Paid</option>
                                                <option value="partial" @if(old('status', $invoice->status ?? '') == 'partial') selected @endif>Partial</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="ml-label">Reference / PO #</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-note"></i></span>
                                            <input type="text" name="reference" class="form-control"
                                                   placeholder="Order or Reference Number"
                                                   value="{{ old('reference', $invoice->reference ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <label class="ml-label">Select Client <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-user"></i></span>
                                            <select class="selectpicker form-select" id="client_id" name="client_id"
                                                    data-live-search="true" data-width="calc(100% - 46px)" required>
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
                                                            @if((old('client_id', $invoice->client_id ?? '') == $client->id) || (!empty($project_id) && $client->project_id == $project_id)) selected @endif>
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
                                </div>
                            </div>
                        </div>

                        {{-- ── Billing & Shipping ── --}}
                        <div class="ml-card mb-4">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(52,168,83,0.10);color:#34a853;">
                                    <i class="bx bx-map"></i>
                                </div>
                                <div>
                                    <h6 class="ml-card-title">Billing & Shipping</h6>
                                    <span class="ml-card-sub">Client address & GST details</span>
                                </div>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="ml-label">Billing Address</label>
                                        <textarea class="form-control bg-light mp-autoresize" name="billing_address" id="billing_address" rows="2"
                                                  placeholder="Standard billing address">{{ old('billing_address', $invoice->billing_address ?? '') }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">Shipping Address</label>
                                        <textarea class="form-control bg-light mp-autoresize" name="shipping_address" id="shipping_address" rows="2"
                                                  placeholder="Standard shipping address">{{ old('shipping_address', $invoice->shipping_address ?? '') }}</textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="ml-label">GST No.</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                                            <input type="text" name="client_gst" id="client_gst" class="form-control"
                                                   value="{{ old('client_gst', $invoice->client_gstno ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-8 d-flex align-items-center">
                                        <div class="form-check mt-3">
                                            <input type="checkbox" class="form-check-input" id="recurring_invoice"
                                                   name="recurring_invoice" value="1" @if(old('recurring_invoice', $invoice->recurring_invoice ?? false)) checked @endif>
                                            <label class="form-check-label fw-600 text-muted" for="recurring_invoice" style="font-size:0.85rem;">Enable Recurring Invoice</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Payment & Bank Details ── --}}
                        <div class="ml-card mb-4">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(242,153,0,0.10);color:#f29900;">
                                    <i class="bx bx-credit-card"></i>
                                </div>
                                <div>
                                    <h6 class="ml-card-title">Payment & Bank Details</h6>
                                    <span class="ml-card-sub">Mode, currency & organization details</span>
                                </div>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="ml-label">Payment Mode</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-wallet"></i></span>
                                            <select class="form-control" name="payment_mode">
                                                <option value="">Select Mode...</option>
                                                <option value="cash" @if(old('payment_mode', $invoice->payment_mode ?? '') == 'cash') selected @endif>Cash</option>
                                                <option value="card" @if(old('payment_mode', $invoice->payment_mode ?? '') == 'card') selected @endif>Credit/Debit Card</option>
                                                <option value="bank" @if(old('payment_mode', $invoice->payment_mode ?? '') == 'bank') selected @endif>Bank Transfer</option>
                                                <option value="paypal" @if(old('payment_mode', $invoice->payment_mode ?? '') == 'paypal') selected @endif>PayPal</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">Currency</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-globe"></i></span>
                                            <select class="form-control" id="currency" name="currency">
                                                <option value="INR" @if(old('currency', $invoice->currency ?? 'INR') == 'INR') selected @endif>₹ INR</option>
                                                <option value="USD" @if(old('currency', $invoice->currency ?? '') == 'USD') selected @endif>$ USD</option>
                                                <option value="EUR" @if(old('currency', $invoice->currency ?? '') == 'EUR') selected @endif>€ EUR</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <div class="bank-info-bg">
                                            <h6 class="fw-700 mb-3 text-dark small text-uppercase" style="letter-spacing:0.5px;">Organization Bank Details</h6>
                                            @php
                                                $company = session('companies');
                                                $bank = json_decode($invoice->bank_details ?? $company->bank_details ?? '["","","","",""]', true);
                                                if (!is_array($bank)) $bank = ["","","","",""];
                                            @endphp
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="ml-label" style="font-size:0.68rem;">Bank Name</label>
                                                    <input type="text" class="form-control form-control-sm" name="bank_details[]" value="{{ $bank[0] ?? '' }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="ml-label" style="font-size:0.68rem;">Account Holder</label>
                                                    <input type="text" class="form-control form-control-sm" name="bank_details[]" value="{{ $bank[1] ?? '' }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="ml-label" style="font-size:0.68rem;">Account Number</label>
                                                    <input type="text" class="form-control form-control-sm" name="bank_details[]" value="{{ $bank[2] ?? '' }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="ml-label" style="font-size:0.68rem;">IFSC / SWIFT</label>
                                                    <input type="text" class="form-control form-control-sm" name="bank_details[]" value="{{ $bank[3] ?? '' }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="ml-label" style="font-size:0.68rem;">UPI ID</label>
                                                    <input type="text" class="form-control form-control-sm" name="bank_details[]" value="{{ $bank[4] ?? '' }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Line Items ── --}}
                        <div class="ml-card mb-4">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(242,153,0,0.10);color:#f29900;">
                                    <i class="bx bx-list-ul"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="ml-card-title">Items &amp; Summary</h6>
                                    <span class="ml-card-sub">Add line items — totals update automatically</span>
                                </div>
                                <button type="button" class="lb-btn" id="addItemButton"
                                        style="background:rgba(0,102,102,0.08);color:#006666;">
                                    <i class="bx bx-plus"></i> Add Item
                                </button>
                            </div>
                            <div class="ml-card-body p-3">
                                <div id="invoiceItemsBody">
                                    @php
                                        $taxes = !empty($companies->tax) ? explode(',', $companies->tax) : [];
                                    @endphp

                                    @foreach($invoiceItems as $k => $item)
                                        <div class="mp-item-row" data-item-row="{{ $k }}">
                                            <div class="mp-item-row-header">
                                                <span class="mp-item-num">{{ $k + 1 }}</span>
                                                <span class="mp-item-row-title">Item {{ $k + 1 }}</span>
                                                <button type="button" class="btn kb-action-btn kb-action-del removeRowButton ms-auto"
                                                        style="width:28px;height:28px;" title="Remove item">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                            <div class="mp-item-row-body">
                                                <div class="mp-item-field" style="grid-column: span 2;">
                                                    <label class="mp-item-label">Item Name</label>
                                                    <textarea class="form-control form-control-sm item-name mp-autoresize"
                                                              name="invoice_items[{{ $k }}][short_description]"
                                                              placeholder="e.g. Web Development" rows="1">{{ $item->short_description ?? '' }}</textarea>
                                                </div>
                                                <div class="mp-item-field" style="grid-column: span 2;">
                                                    <label class="mp-item-label">Description</label>
                                                    <textarea class="form-control form-control-sm item-longdesc mp-autoresize"
                                                              name="invoice_items[{{ $k }}][long_description]"
                                                              placeholder="Optional details…" rows="1">{{ $item->long_description ?? '' }}</textarea>
                                                </div>
                                                <div class="mp-item-field">
                                                    <label class="mp-item-label">SAC/HSN</label>
                                                    <input type="text" class="form-control form-control-sm item-sac"
                                                           name="invoice_items[{{ $k }}][sac_code]" value="{{ $item->sac_code ?? '' }}">
                                                </div>
                                                <div class="mp-item-field">
                                                    <label class="mp-item-label">Qty</label>
                                                    <input type="number" class="form-control form-control-sm item-qty text-center"
                                                           name="invoice_items[{{ $k }}][quantity]" value="{{ $item->quantity ?? 1 }}" min="1">
                                                </div>
                                                <div class="mp-item-field">
                                                    <label class="mp-item-label">Rate (₹)</label>
                                                    <input type="number" class="form-control form-control-sm item-price text-end"
                                                           name="invoice_items[{{ $k }}][price]" placeholder="0.00" value="{{ $item->price ?? '' }}">
                                                </div>
                                                <div class="mp-item-field">
                                                    <label class="mp-item-label">Tax</label>
                                                    <select class="selectpicker form-control form-control-sm item-tax" multiple
                                                            data-selected-text-format="count > 2" data-container="body"
                                                            name="invoice_items[{{ $k }}][tax_rate][]" title="No Tax">
                                                        @foreach($taxes as $index => $tax)
                                                            @php $calTax = ($tax ?? 0) / 100; @endphp
                                                            <option value="{{ $index . ':' . $calTax }}"
                                                                @php
                                                                    $isSel = false;
                                                                    if($index == 0 && !empty($item->cgst_percent)) $isSel = true;
                                                                    elseif($index == 1 && !empty($item->sgst_percent)) $isSel = true;
                                                                    elseif($index == 2 && !empty($item->igst_percent)) $isSel = true;
                                                                    elseif($index == 3 && !empty($item->vat_percent)) $isSel = true;
                                                                @endphp
                                                                @if($isSel) selected @endif>
                                                                {{ ['CGST','SGST','IGST','VAT'][$index] ?? 'Tax' }} {{ $tax }}%
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mp-item-field">
                                                    <label class="mp-item-label">Amount</label>
                                                    <span class="line-total mp-item-amount-val">
                                                        ₹{{ number_format(($item->price ?? 0) * ($item->quantity ?? 0), 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    @if(count($invoiceItems) == 0)
                                        <div class="mp-item-row" data-item-row="0">
                                            <div class="mp-item-row-header">
                                                <span class="mp-item-num">1</span>
                                                <span class="mp-item-row-title">Item 1</span>
                                                <button type="button" class="btn kb-action-btn kb-action-del removeRowButton ms-auto"
                                                        style="width:28px;height:28px;" title="Remove item">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                            <div class="mp-item-row-body">
                                                <div class="mp-item-field" style="grid-column: span 2;">
                                                    <label class="mp-item-label">Item Name</label>
                                                    <textarea class="form-control form-control-sm item-name mp-autoresize"
                                                              name="invoice_items[0][short_description]"
                                                              placeholder="e.g. Web Development" rows="1" required></textarea>
                                                </div>
                                                <div class="mp-item-field" style="grid-column: span 2;">
                                                    <label class="mp-item-label">Description</label>
                                                    <textarea class="form-control form-control-sm item-longdesc mp-autoresize"
                                                              name="invoice_items[0][long_description]"
                                                              placeholder="Optional details…" rows="1"></textarea>
                                                </div>
                                                <div class="mp-item-field">
                                                    <label class="mp-item-label">SAC/HSN</label>
                                                    <input type="text" class="form-control form-control-sm item-sac"
                                                           name="invoice_items[0][sac_code]" value="998314">
                                                </div>
                                                <div class="mp-item-field">
                                                    <label class="mp-item-label">Qty</label>
                                                    <input type="number" class="form-control form-control-sm item-qty text-center"
                                                           name="invoice_items[0][quantity]" value="1" min="1">
                                                </div>
                                                <div class="mp-item-field">
                                                    <label class="mp-item-label">Rate (₹)</label>
                                                    <input type="number" class="form-control form-control-sm item-price text-end"
                                                           name="invoice_items[0][price]" placeholder="0.00" required>
                                                </div>
                                                <div class="mp-item-field">
                                                    <label class="mp-item-label">Tax</label>
                                                    <select class="selectpicker form-control form-control-sm item-tax" multiple
                                                            data-selected-text-format="count > 2" data-container="body"
                                                            name="invoice_items[0][tax_rate][]" title="No Tax">
                                                        @foreach($taxes as $index => $tax)
                                                            @php $calTax = ($tax ?? 0) / 100; @endphp
                                                            <option value="{{ $index . ':' . $calTax }}">
                                                                {{ ['CGST','SGST','IGST','VAT'][$index] ?? 'Tax' }} {{ $tax }}%
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mp-item-field">
                                                    <label class="mp-item-label">Amount</label>
                                                    <span class="line-total mp-item-amount-val">₹0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <button type="button" class="mp-add-item-btn mt-3" id="addItemButtonSecondary">
                                    <i class="bx bx-plus"></i> Add Another Item
                                </button>
                            </div>
                        </div>

                        {{-- ── Notes ── --}}
                        <div class="ml-card mb-4">
                            <div class="ml-card-header">
                                <div class="ml-card-icon" style="background:rgba(26,115,232,0.10);color:#1a73e8;">
                                    <i class="bx bx-edit-alt"></i>
                                </div>
                                <div>
                                    <h6 class="ml-card-title">Notes &amp; Terms</h6>
                                    <span class="ml-card-sub">Internal notes & legal terms</span>
                                </div>
                            </div>
                            <div class="ml-card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="ml-label">Admin Note (Internal)</label>
                                        <textarea class="form-control bg-light mp-autoresize" name="admin_note" rows="2"
                                                  placeholder="Not visible to client">{{ old('admin_note', $invoice->admin_note ?? '') }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="ml-label">Client Note (on PDF)</label>
                                        <textarea class="form-control bg-light mp-autoresize" name="client_note" rows="2"
                                                  placeholder="Visible on PDF">{{ old('client_note', $invoice->client_note ?? '') }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="ml-label">Terms &amp; Conditions</label>
                                        <textarea class="form-control bg-light mp-autoresize" name="terms" rows="2"
                                                  placeholder="Terms and conditions">{{ old('terms', $invoice->terms ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> {{-- End col-lg-8 --}}

                    <div class="col-lg-12">
                        {{-- ══ RIGHT — Sticky Summary + Actions ══ --}}
                        <div class="mp-sidebar-sticky">
                            {{-- Summary card --}}
                            <div class="ml-card mb-3">
                                <div class="ml-card-header">
                                    <div class="ml-card-icon" style="background:rgba(52,168,83,0.10);color:#34a853;">
                                        <i class="bx bx-calculator"></i>
                                    </div>
                                    <div>
                                        <h6 class="ml-card-title">Summary</h6>
                                        <span class="ml-card-sub">Live totals as you type</span>
                                    </div>
                                </div>
                                <div class="ml-card-body">
                                    <div class="mp-summary-row">
                                        <span class="mp-summary-label">Sub Total</span>
                                        <span id="subTotal" class="mp-summary-val">₹0.00</span>
                                    </div>

                                    <div class="divider my-2 border-top border-dashed"></div>

                                    <div class="mb-3">
                                        <label class="ml-label">Apply Discount</label>
                                        <div class="input-group input-group-sm">
                                            <select name="discount_type" id="discountApplicationType" class="form-select bg-light">
                                                <option value="none" @if(($invoice->discount_type ?? '') == 'none') selected @endif>None</option>
                                                <option value="before-tax" @if(($invoice->discount_type ?? '') == 'before-tax') selected @endif>Before Tax</option>
                                                <option value="after-tax" @if(($invoice->discount_type ?? '') == 'after-tax') selected @endif>After Tax</option>
                                            </select>
                                            <select name="discount_mode" id="discountValueType" class="form-select bg-light" style="max-width:75px;">
                                                <option value="flat" @if(($invoice->discount_mode ?? '') == 'flat') selected @endif>FLAT</option>
                                                <option value="percentage" @if(($invoice->discount_mode ?? '') == 'percentage') selected @endif>%</option>
                                            </select>
                                            <input type="number" class="form-control bg-light text-end" name="discount"
                                                   id="discountValue" value="{{ $invoice->discount ?? 0 }}" step="0.01">
                                        </div>
                                    </div>

                                    <div class="mp-summary-row text-success small" id="discountAmountRow" style="display:none !important;">
                                        <span class="mp-summary-label text-success">Net Discount</span>
                                        <span id="discountAmountCalculated" class="mp-summary-val text-success">(-₹0.00)</span>
                                    </div>

                                    <div class="divider my-2 border-top border-dashed"></div>

                                    <div id="tax-summary-rows">
                                        {{-- Tax rows populated by JS --}}
                                        <div class="mp-summary-row">
                                            <span class="mp-summary-label">Total Tax</span>
                                            <span id="totalTax" class="mp-summary-val">₹0.00</span>
                                        </div>
                                    </div>

                                    <div class="divider my-2 border-top border-dashed"></div>

                                    <div class="mb-3">
                                        <label class="ml-label">Adjustment / Setup Fee</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text"><i class="bx bx-plus-minus"></i></span>
                                            <input type="number" class="form-control text-end" name="adjustment"
                                                   id="adjustment" value="{{ $invoice->adjustment ?? 0 }}" step="0.01">
                                        </div>
                                    </div>

                                    <div class="mp-summary-total">
                                        <span>Grand Total</span>
                                        <strong id="grandTotal" class="mp-grand-total-val">₹0.00</strong>
                                    </div>
                                    <input type="hidden" name="gtAmount" id="gtAmount" value="0" />
                                </div>
                            </div>

                            {{-- Action buttons card --}}
                            <div class="ml-card">
                                <div class="ml-card-body">
                                    <div class="d-flex flex-column gap-2">
                                        <button type="submit" form="invoiceForm" class="lb-btn lb-btn-primary w-100 justify-content-center">
                                            <i class="bx bx-save"></i> Save Invoice
                                        </button>
                                        <button type="button" class="lb-btn w-100 justify-content-center" id="previewInvoiceBtn"
                                                style="background:rgba(26,115,232,0.08);color:#1a73e8;">
                                            <i class="bx bx-show"></i> Live Preview
                                        </button>
                                        <a href="/invoices" class="lb-btn w-100 justify-content-center"
                                           style="background:transparent;color:#9aa0a6;border:1px solid #e8eaed;">
                                            <i class="bx bx-x"></i> Cancel
                                        </button>
                                    </div>
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
        const availableTaxes = @json($taxes);
        const taxNames = ['CGST','SGST','IGST','VAT'];

        function formatCurrency(amount) {
            return parseFloat(amount || 0).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function recalculateTotals() {
            try {
                const discountAppType = $('#discountApplicationType').val();
                const discountValueType = $('#discountValueType').val();
                const discountValue = parseFloat($('#discountValue').val()) || 0;
                const adjustment = parseFloat($('#adjustment').val()) || 0;

                let initialSubTotal = 0;
                let totalTax = 0;
                let taxBreakdown = {};

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
                            const amt = lineSubTotal * rate;
                            lineTaxAmount += amt;
                            const label = $row.find(`.item-tax option[value="${val}"]`).text().trim();
                            taxBreakdown[label] = (taxBreakdown[label] || 0) + amt;
                        });
                    }

                    $row.data('initialSubtotal', lineSubTotal);
                    $row.data('initialTax', lineTaxAmount);
                    initialSubTotal += lineSubTotal;

                    $row.find('.line-total').text('₹ ' + formatCurrency(lineSubTotal + lineTaxAmount));
                });

                // Calculate Net Discount
                let calculatedDiscountAmount = 0;
                if (discountValue > 0) {
                    let discountBase = (discountAppType === 'before-tax') ? initialSubTotal : 
                                       (initialSubTotal + $rows.toArray().reduce((sum, el) => sum + parseFloat($(el).data('initialTax') || 0), 0));
                    
                    calculatedDiscountAmount = (discountValueType === 'percentage') ? (discountBase * (discountValue / 100)) : discountValue;
                    calculatedDiscountAmount = Math.min(calculatedDiscountAmount, discountBase);
                }

                // Final Taxes & Subtotal
                let finalSubTotal = initialSubTotal;
                totalTax = 0;

                if (discountAppType === 'before-tax') {
                    finalSubTotal = initialSubTotal - calculatedDiscountAmount;
                    $rows.each(function() {
                        const rowSub = parseFloat($(this).data('initialSubtotal') || 0);
                        const rowRatio = initialSubTotal > 0 ? (rowSub / initialSubTotal) : 0;
                        const rowDiscount = calculatedDiscountAmount * rowRatio;
                        const rowFinalSub = rowSub - rowDiscount;
                        
                        const selectedTares = $(this).find('.item-tax').val();
                        if (selectedTares) {
                            selectedTares.forEach(val => {
                                const rate = parseFloat(val.split(':')[1]);
                                totalTax += rowFinalSub * rate;
                            });
                        }
                    });
                } else {
                    $rows.each(function() { totalTax += parseFloat($(this).data('initialTax') || 0); });
                }

                const grandTotal = Math.max(0, (discountAppType === 'before-tax') ? (finalSubTotal + totalTax - adjustment) : 
                                   (discountAppType === 'after-tax') ? (initialSubTotal + totalTax - calculatedDiscountAmount - adjustment) : 
                                   (initialSubTotal + totalTax - adjustment));

                $('#subTotal').text('₹ ' + formatCurrency(initialSubTotal));
                $('#totalTax').text('₹ ' + formatCurrency(totalTax));
                $('#grandTotal').text('₹ ' + formatCurrency(grandTotal));
                $('#gtAmount').val(grandTotal.toFixed(2));

                $('#discountAmountRow').toggle(calculatedDiscountAmount > 0).css('display', calculatedDiscountAmount > 0 ? 'flex' : 'none');
                $('#discountAmountCalculated').text(`(-₹ ${formatCurrency(calculatedDiscountAmount)})`);

                // Tax breakdown for summary
                let taxHtml = '';
                for (let label in taxBreakdown) {
                    taxHtml += `<div class="mp-summary-row small"><span class="mp-summary-label">${label}</span><span class="mp-summary-val">₹ ${formatCurrency(taxBreakdown[label])}</span></div>`;
                }
                if (taxHtml) {
                    $('#tax-summary-rows').html(taxHtml + `<div class="divider my-1 border-top border-dashed"></div><div class="mp-summary-row"><span class="mp-summary-label">Total Tax</span><span class="mp-summary-val">₹ ${formatCurrency(totalTax)}</span></div>`);
                }

            } catch (e) { console.error("Calc Error:", e); }
        }

        $(document).ready(function() {
            // Add Item Logic
            $('#addItemButton, #addItemButtonSecondary').on('click', function() {
                const index = $('.mp-item-row').length;
                let taxOptions = '';
                availableTaxes.forEach((val, i) => {
                    const rate = (val || 0) / 100;
                    taxOptions += `<option value="${i}:${rate}">${taxNames[i] || 'Tax'} ${val}%</option>`;
                });

                const html = `
                    <div class="mp-item-row shadow-sm animate__animated animate__fadeInUp">
                        <div class="mp-item-row-header">
                            <span class="mp-item-num">${index + 1}</span>
                            <span class="mp-item-row-title">Item ${index + 1}</span>
                            <button type="button" class="btn btn-link text-danger p-0 ms-auto removeRowButton"><i class="bx bx-trash fs-5"></i></button>
                        </div>
                        <div class="mp-item-row-body">
                            <div class="mp-item-field" style="grid-column: span 2;">
                                <label class="mp-item-label">Item Name</label>
                                <textarea class="form-control form-control-sm item-name mp-autoresize" name="invoice_items[${index}][short_description]" rows="1" required></textarea>
                            </div>
                            <div class="mp-item-field" style="grid-column: span 2;">
                                <label class="mp-item-label">Description</label>
                                <textarea class="form-control form-control-sm item-longdesc mp-autoresize" name="invoice_items[${index}][long_description]" rows="1"></textarea>
                            </div>
                            <div class="mp-item-field">
                                <label class="mp-item-label">Qty</label>
                                <input type="number" class="form-control form-control-sm item-qty text-center" name="invoice_items[${index}][quantity]" value="1" min="1">
                            </div>
                            <div class="mp-item-field">
                                <label class="mp-item-label">Rate (₹)</label>
                                <input type="number" class="form-control form-control-sm item-price text-end" name="invoice_items[${index}][price]" placeholder="0.00" required>
                            </div>
                            <div class="mp-item-field">
                                <label class="mp-item-label">Tax</label>
                                <select class="selectpicker form-control form-control-sm item-tax" multiple data-container="body" name="invoice_items[${index}][tax_rate][]" title="No Tax">
                                    ${taxOptions}
                                </select>
                            </div>
                            <div class="mp-item-field">
                                <label class="mp-item-label">Amount</label>
                                <span class="line-total mp-item-amount-val">₹ 0.00</span>
                            </div>
                        </div>
                    </div>`;

                $('#invoiceItemsBody').append(html);
                $('.selectpicker').selectpicker('render');
                recalculateTotals();
            });

            // Remove Item Logic
            $(document).on('click', '.removeRowButton', function() {
                if ($('.mp-item-row').length > 1) {
                    $(this).closest('.mp-item-row').fadeOut(300, function() {
                        $(this).remove();
                        // Update numbering
                        $('.mp-item-row').each((i, el) => {
                            $(el).find('.mp-item-num').text(i + 1);
                            $(el).find('.mp-item-row-title').text('Item ' + (i + 1));
                        });
                        recalculateTotals();
                    });
                } else {
                    Swal.fire('Warning', 'At least one item is required.', 'warning');
                }
            });

            // Client Auto-fill
            $('#client_id').on('change', function() {
                const opt = $(this).find(':selected');
                if (opt.val()) {
                    const addr = opt.data('address') || '';
                    $('#billing_address').val(addr);
                    $('#shipping_address').val(addr);
                    $('#client_gst').val(opt.data('gstno') || '');
                }
            });

            // Event Listeners for calculations
            $(document).on('input', '.item-qty, .item-price, #discountValue, #adjustment', recalculateTotals);
            $(document).on('change', '.item-tax, #discountApplicationType, #discountValueType', recalculateTotals);

            // Initial calc
            setTimeout(() => {
                $('.selectpicker').selectpicker('refresh');
                recalculateTotals();
            }, 300);
        });
    </script>
            </form>
        </div>
    </section>
@endsection
