@extends('layout')
@section('title', 'Manage Invoice - eseCRM')

@section('content')
    <style>
        @media (max-width:767px) {
            .table>thead>tr>th {
                min-width: 185px !important;
            }
        }
    </style>
    <section class="task__section">
        @include('inc.header', ['title' => 'Invoices'])

        <div class="dash-container">
            <form action="/manage-invoice" method="POST">
                @csrf
                @if(!empty($invoice->id))
                    <input type="hidden" name="id" value="{{ $invoice->id }}">
                @endif

                {{-- ── Header & Action Bar ── --}}
                <div class="leads-toolbar mb-4 position-sticky top-0 bg-white" style="z-index: 100; margin: -10px -10px 20px -10px; padding: 10px; border-bottom: 1px solid #eef0f2;">
                    <div class="leads-toolbar-left gap-3">
                        <a href="/invoices" class="kb-action-btn" title="Back to Lists">
                            <i class="bx bx-arrow-back"></i>
                        </a>
                        <div class="leads-toolbar-item">
                            <h1 class="h5 mb-0 fw-bold">
                                @if(!empty($invoice->id))
                                    Edit Invoice <span class="text-primary">#{{ $invoice->invoice_number ?? '' }}</span>
                                @else
                                    Create New Invoice
                                @endif
                            </h1>
                        </div>
                    </div>
                    <div class="leads-toolbar-right gap-2">
                        <button type="button" class="lb-btn lb-btn-outline" id="previewInvoiceBtn">
                            <i class='bx bx-show'></i> Preview
                        </button>
                        <button type="submit" class="lb-btn lb-btn-primary">
                            <i class='bx bx-save'></i> Save Invoice
                        </button>
                    </div>
                </div>

                {{-- ── Progress Steps ── --}}
                <div class="mp-steps mb-4">
                    <div class="mp-step @if(!empty($invoice->id)) mp-step-done @else mp-step-active @endif">
                        <div class="mp-step-num">@if(!empty($invoice->id)) <i class="bx bx-check"></i> @else 1 @endif</div>
                        <div class="mp-step-label">Invoice Info</div>
                    </div>
                    <div class="mp-step-line @if(!empty($invoice->id)) mp-line-done @endif"></div>
                    <div class="mp-step @if(!empty($invoice->id)) mp-step-done @else mp-step-active @endif">
                        <div class="mp-step-num">@if(!empty($invoice->id)) <i class="bx bx-check"></i> @else 2 @endif</div>
                        <div class="mp-step-label">Client & Billing</div>
                    </div>
                    <div class="mp-step-line"></div>
                    <div class="mp-step mp-step-active">
                        <div class="mp-step-num">3</div>
                        <div class="mp-step-label">Items & Summary</div>
                    </div>
                </div>

                <div class="row">
                    {{-- ── Left Column: Main Form ── --}}
                    <div class="col-lg-8">
                        {{-- ── Invoice Details Card ── --}}
                        <div class="ml-card mb-4 shadow-sm">
                            <div class="ml-card-header bg-white border-bottom py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="ml-card-icon-sm" style="background:#e6f4f4;color:#006666;">
                                        <i class="bx bx-file"></i>
                                    </div>
                                    <h5 class="mb-0 fw-bold">Invoice Details</h5>
                                </div>
                            </div>
                            <div class="ml-card-body p-4">
                                <div class="row g-4">
                                    <div class="col-md-4">
                                        <label class="lb-label">Invoice Number</label>
                                        <div class="lb-input-group">
                                            <span class="lb-input-icon"><i class='bx bx-hash'></i></span>
                                            <input type="text" class="lb-input" name="invoice_number" id="invoice_number"
                                                placeholder="INV-001" value="{{ old('invoice_number', $invoice->invoice_number ?? null) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="lb-label">Invoice Type</label>
                                        <div class="lb-input-group">
                                            <span class="lb-input-icon"><i class='bx bx-category'></i></span>
                                            <select class="lb-input" name="invoice_type" id="invoice_type" required>
                                                <option value="invoice" @if(old('invoice_type', $invoice->invoice ?? '') == 'invoice') selected @endif>Standard Invoice</option>
                                                <option value="proforma" @if(old('invoice_type', $invoice->invoice ?? '') == 'proforma') selected @endif>Proforma Invoice</option>
                                                <option value="tax" @if(old('invoice_type', $invoice->invoice ?? '') == 'tax') selected @endif>Tax Invoice</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="lb-label">Status</label>
                                        <div class="lb-input-group">
                                            <span class="lb-input-icon"><i class='bx bx-info-circle'></i></span>
                                            <select class="lb-input" name="status" id="status">
                                                <option value="unpaid" @if(old('status', $invoice->status ?? 'unpaid') == 'unpaid') selected @endif>Unpaid</option>
                                                <option value="paid" @if(old('status', $invoice->status ?? '') == 'paid') selected @endif>Paid</option>
                                                <option value="partial" @if(old('status', $invoice->status ?? '') == 'partial') selected @endif>Partially Paid</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-8">
                                        <label class="lb-label">Client</label>
                                        <div class="lb-input-group">
                                            <span class="lb-input-icon"><i class='bx bx-user'></i></span>
                                            <select class="selectpicker form-control lb-input" id="client_id" name="client_id" data-live-search="true" required>
                                                <option value="">Select Client</option>
                                                @foreach($clients as $client)
                                                    @php $location = json_decode(($client->location ?? ''), true) @endphp
                                                    <option value="{{ $client->id }}" data-name="{{ $client->name ?? '' }}"
                                                        data-company="{{ $client->company ?? '' }}"
                                                        data-email="{{ $client->email ?? '' }}" data-mob="{{ $client->mob ?? '' }}"
                                                        data-gstno="{{ $client->gstno ?? '' }}"
                                                        data-address="{{ $location[0] ?? '' }}" data-city="{{ $location[1] ?? '' }}"
                                                        data-state="{{ $location[2] ?? '' }}"
                                                        data-country="{{ $location[3] ?? '' }}" data-zip="{{ $location[4] ?? '' }}"
                                                        @if(old('client_id', $invoice->client_id ?? '') == $client->id) selected @endif>
                                                        {{ $client->name }} @if($client->company) ({{ $client->company }}) @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="kb-action-btn ms-2" data-bs-toggle="modal" data-bs-target="#addClientModal" title="Add Client">
                                                <i class='bx bx-plus'></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="lb-label">Reference / PO #</label>
                                        <div class="lb-input-group">
                                            <span class="lb-input-icon"><i class='bx bx-note'></i></span>
                                            <input type="text" class="lb-input" name="reference" id="reference" placeholder="Ref No."
                                                value="{{ old('reference', $invoice->reference ?? '') }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="lb-label">Invoice Date</label>
                                        <div class="lb-input-group">
                                            <span class="lb-input-icon"><i class='bx bx-calendar'></i></span>
                                            <input type="date" class="lb-input" name="date" id="date"
                                                value="{{ old('date', $invoice && $invoice->date ? \Carbon\Carbon::parse($invoice->date)->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="lb-label">Due Date</label>
                                        <div class="lb-input-group">
                                            <span class="lb-input-icon"><i class='bx bx-calendar-check'></i></span>
                                            <input type="date" class="lb-input" name="due_date" id="due_date"
                                                value="{{ old('due_date', $invoice && $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') : '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Billing & Shipping Card ── --}}
                        <div class="ml-card mb-4 shadow-sm">
                            <div class="ml-card-header bg-white border-bottom py-2 px-4 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="ml-card-icon-sm" style="background:#fff7e6;color:#ffa940;">
                                        <i class="bx bx-map"></i>
                                    </div>
                                    <h5 class="mb-0 fw-bold">Billing & Shipping</h5>
                                </div>
                                <button type="button" class="btn btn-link btn-sm text-decoration-none" data-bs-toggle="collapse" data-bs-target="#collapseBilling">
                                    <i class="bx bx-chevron-down fs-4"></i>
                                </button>
                            </div>
                            <div class="collapse show" id="collapseBilling">
                                <div class="ml-card-body p-4">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="lb-label">Billing Address</label>
                                            <textarea class="lb-input mp-autoresize" name="billing_address" id="billing_address" rows="2" placeholder="Enter billing address">{{ old('billing_address', $invoice->billing_address ?? '') }}</textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="lb-label">Shipping Address</label>
                                            <textarea class="lb-input mp-autoresize" name="shipping_address" id="shipping_address" rows="2" placeholder="Enter shipping address">{{ old('shipping_address', $invoice->shipping_address ?? '') }}</textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="lb-label">GST No.</label>
                                            <div class="lb-input-group">
                                                <span class="lb-input-icon"><i class='bx bx-id-card'></i></span>
                                                <input type="text" class="lb-input" name="client_gst" id="client_gst" placeholder="GST NO." value="{{ old('client_gst', $invoice->client_gstno ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-8 d-flex align-items-end">
                                            <div class="form-check form-switch mb-2">
                                                <input type="checkbox" class="form-check-input" id="recurring_invoice" name="recurring_invoice" value="1" @if(old('recurring_invoice', $invoice->recurring_invoice ?? false)) checked @endif>
                                                <label class="form-check-label fw-bold ms-2" for="recurring_invoice">Enable Recurring Invoice</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Payment & Bank Info Card ── --}}
                        <div class="ml-card mb-4 shadow-sm">
                            <div class="ml-card-header bg-white border-bottom py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="ml-card-icon-sm" style="background:#e6f7ff;color:#1890ff;">
                                        <i class="bx bx-credit-card"></i>
                                    </div>
                                    <h5 class="mb-0 fw-bold">Payment & Bank Info</h5>
                                </div>
                            </div>
                            <div class="ml-card-body p-4">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="lb-label">Payment Mode</label>
                                        <div class="lb-input-group">
                                            <span class="lb-input-icon"><i class='bx bx-wallet'></i></span>
                                            <select class="lb-input" id="payment_mode" name="payment_mode">
                                                <option value="">Select Mode</option>
                                                <option value="cash" @if(old('payment_mode', $invoice->payment_mode ?? '') == 'cash') selected @endif>Cash</option>
                                                <option value="card" @if(old('payment_mode', $invoice->payment_mode ?? '') == 'card') selected @endif>Credit/Debit Card</option>
                                                <option value="bank" @if(old('payment_mode', $invoice->payment_mode ?? '') == 'bank') selected @endif>Bank Transfer</option>
                                                <option value="paypal" @if(old('payment_mode', $invoice->payment_mode ?? '') == 'paypal') selected @endif>PayPal</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="lb-label">Currency</label>
                                        <div class="lb-input-group">
                                            <span class="lb-input-icon"><i class='bx bx-globe'></i></span>
                                            <select class="lb-input" id="currency" name="currency">
                                                <option value="INR" @if(old('currency', $invoice->currency ?? 'INR') == 'INR') selected @endif>INR - Indian Rupee</option>
                                                <option value="USD" @if(old('currency', $invoice->currency ?? '') == 'USD') selected @endif>USD - US Dollar</option>
                                                <option value="EUR" @if(old('currency', $invoice->currency ?? '') == 'EUR') selected @endif>EUR - Euro</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <h6 class="fw-bold text-muted small text-uppercase mb-3">Company Bank Details</h6>
                                        @php 
                                            $company = session('companies');
                                            $companyBankDetails = json_decode($invoice->bank_details ?? $company->bank_details ?? '["","","","",""]', true);
                                            if(!is_array($companyBankDetails)) $companyBankDetails = ["","","","",""];
                                        @endphp
                                        <div class="row g-3 p-3 rounded" style="background:#f8f9fa; border:1px solid #eef0f2;">
                                            <div class="col-md-4">
                                                <label class="small text-muted mb-1">Bank Name</label>
                                                <input type="text" class="lb-input-sm w-100" name="bank_details[]" placeholder="Bank Name" value="{{ old('bank_name', $companyBankDetails[0] ?? '') }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small text-muted mb-1">Account Holder</label>
                                                <input type="text" class="lb-input-sm w-100" name="bank_details[]" placeholder="Holder Name" value="{{ old('bank_account_holder_name', $companyBankDetails[1] ?? '') }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small text-muted mb-1">Account Number</label>
                                                <input type="text" class="lb-input-sm w-100" name="bank_details[]" placeholder="A/C Number" value="{{ old('bank_account_number', $companyBankDetails[2] ?? '') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="small text-muted mb-1">IFSC / SWIFT Code</label>
                                                <input type="text" class="lb-input-sm w-100" name="bank_details[]" placeholder="IFSC Code" value="{{ old('bank_ifsc_code', $companyBankDetails[3] ?? '') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="small text-muted mb-1">UPI ID</label>
                                                <input type="text" class="lb-input-sm w-100" name="bank_details[]" placeholder="UPI ID" value="{{ old('upi', $companyBankDetails[4] ?? '') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Items & Financials Card ── --}}
                        <div class="ml-card mb-4 shadow-sm">
                            <div class="ml-card-header bg-white border-bottom py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="ml-card-icon-sm" style="background:#f6ffed;color:#52c41a;">
                                        <i class="bx bx-list-ol"></i>
                                    </div>
                                    <h5 class="mb-0 fw-bold">Items & Financials</h5>
                                </div>
                            </div>
                            <div class="ml-card-body p-4">
                                <div id="items-card-container">
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
                                        <div class="mp-item-row">
                                            <div class="mp-item-row-header">
                                                <div class="mp-item-num">{{ $index + 1 }}</div>
                                                <div class="mp-item-row-title">Line Item</div>
                                                <button type="button" class="kb-action-btn kb-action-del ms-auto removeRowButton" title="Remove Item">
                                                    <i class='bx bx-trash'></i>
                                                </button>
                                            </div>
                                            <div class="mp-item-row-body">
                                                <div class="mp-item-field mp-item-name-field">
                                                    <label class="mp-item-label">Item Name</label>
                                                    <textarea class="lb-input-sm mp-autoresize item-name" name="invoice_items[{{ $index }}][short_description]" placeholder="What are you invoicing?">{{ $item->short_description ?? '' }}</textarea>
                                                </div>
                                                <div class="mp-item-field mp-item-desc-field">
                                                    <label class="mp-item-label">Description</label>
                                                    <textarea class="lb-input-sm mp-autoresize item-longdesc" name="invoice_items[{{ $index }}][long_description]" placeholder="Detailed description...">{{ $item->long_description ?? '' }}</textarea>
                                                </div>
                                                <div class="mp-item-field" style="width: 100px;">
                                                    <label class="mp-item-label">SAC Code</label>
                                                    <input type="text" class="lb-input-sm text-center item-sac_code" name="invoice_items[{{ $index }}][sac_code]" value="{{ $item->sac_code ?? '998314' }}">
                                                </div>
                                                <div class="mp-item-field mp-item-qty-field">
                                                    <label class="mp-item-label">Qty/Hrs</label>
                                                    <input type="number" class="lb-input-sm text-center item-qty" name="invoice_items[{{ $index }}][quantity]" value="{{ $item->quantity ?? 1 }}" step="any" required>
                                                </div>
                                                <div class="mp-item-field mp-item-rate-field">
                                                    <label class="mp-item-label">Rate (₹)</label>
                                                    <input type="number" class="lb-input-sm text-end item-price" name="invoice_items[{{ $index }}][price]" value="{{ number_format($item->price ?? 0, 2, '.', '') }}" step="any" required>
                                                </div>
                                                <div class="mp-item-field mp-item-tax-field">
                                                    <label class="mp-item-label">Tax</label>
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
                                                    <select class="form-select form-select-sm item-tax" multiple name="invoice_items[{{ $index }}][tax_rate][]">
                                                        @foreach($available_taxes as $tax)
                                                            <option value="{{ $tax['value'] }}" @if(in_array($tax['value'], $selected_taxes)) selected @endif>{{ $tax['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mp-item-field mp-item-amount-field">
                                                    <label class="mp-item-label">Amount</label>
                                                    <span class="mp-item-amount-val line-total">₹0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                    @endforelse
                                </div>
                                <button type="button" class="mp-add-item-btn mt-3" id="addItemButton">
                                    <i class="bx bx-plus-circle fs-5"></i> Add Another Item
                                </button>
                            </div>
                        </div>

                        {{-- ── Notes & Terms Card ── --}}
                        <div class="ml-card mb-4 shadow-sm">
                            <div class="ml-card-header bg-white border-bottom py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="ml-card-icon-sm" style="background:#f9f0ff;color:#722ed1;">
                                        <i class="bx bx-notepad"></i>
                                    </div>
                                    <h5 class="mb-0 fw-bold">Notes & Terms</h5>
                                </div>
                            </div>
                            <div class="ml-card-body p-4">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="lb-label">Admin Note (Internal)</label>
                                        <textarea class="lb-input mp-autoresize" name="admin_note" id="admin_note" rows="2" placeholder="Internal memo...">{{ old('admin_note', $invoice->admin_note ?? '') }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="lb-label">Client Note</label>
                                        <textarea class="lb-input mp-autoresize" name="client_note" id="client_note" rows="2" placeholder="Visible to client...">{{ old('client_note', $invoice->client_note ?? '') }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="lb-label">Terms & Conditions</label>
                                        <textarea class="lb-input mp-autoresize" name="terms" id="terms" rows="2" placeholder="Legal terms and conditions...">{{ old('terms', $invoice->terms ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> {{-- End col-lg-8 --}}
                    {{-- ── Right Column: Sticky Sidebar ── --}}
                    <div class="col-lg-4">
                        <div class="mp-sidebar-sticky">
                            {{-- ── Summary Card ── --}}
                            <div class="ml-card shadow-sm border-0 mb-4" style="background: linear-gradient(135deg, #ffffff 0%, #f8faff 100%);">
                                <div class="ml-card-header bg-transparent border-bottom py-3">
                                    <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                                        <i class="bx bx-calculator text-primary"></i>
                                        Financial Summary
                                    </h5>
                                </div>
                                <div class="ml-card-body p-4">
                                    <div class="summary-list d-flex flex-column gap-3">
                                        <div class="summary-item d-flex justify-content-between align-items-center">
                                            <span class="text-muted">Sub Total</span>
                                            <span class="fw-bold" id="sub_total_display">₹0.00</span>
                                            <input type="hidden" name="sub_total" id="sub_total" value="{{ $invoice->sub_total ?? 0 }}">
                                        </div>

                                        <div class="summary-item">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-muted">Discount</span>
                                                <div class="d-flex align-items-center gap-2" style="width: 150px;">
                                                    <input type="number" class="lb-input-sm text-end" name="discount_percent" id="discount_percent" 
                                                        value="{{ $invoice->discount_percent ?? 0 }}" step="any" placeholder="0">
                                                    <span class="small fw-bold">%</span>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center small">
                                                <span></span>
                                                <span class="text-danger" id="discount_amount_display">-₹0.00</span>
                                                <input type="hidden" name="discount_amount" id="discount_amount" value="{{ $invoice->discount_amount ?? 0 }}">
                                            </div>
                                        </div>

                                        <div id="tax_rows_container" class="d-flex flex-column gap-2 border-top border-bottom py-3 my-1">
                                            {{-- Dynamic Tax Rows will be injected here via JS --}}
                                        </div>

                                        <div class="summary-item d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-muted">Adjustment</span>
                                                <i class="bx bx-info-circle text-muted" title="Ad-hoc adjustment (e.g. shipping, round-off)"></i>
                                            </div>
                                            <input type="number" class="lb-input-sm text-end" style="width: 120px;" name="adjustment" id="adjustment" 
                                                value="{{ $invoice->adjustment ?? 0 }}" step="any" placeholder="0.00">
                                        </div>

                                        <div class="total-section mt-3 p-3 rounded-3" style="background: #ebf1ff;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="h6 mb-0 fw-bold">Total Amount</span>
                                                <span class="h4 mb-0 fw-bold text-primary" id="total_display">₹0.00</span>
                                                <input type="hidden" name="total_amount" id="total_amount" value="{{ $invoice->total_amount ?? 0 }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ── Sidebar Actions ── --}}
                            <div class="ml-card shadow-sm border-0">
                                <div class="ml-card-body p-3">
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="lb-btn lb-btn-primary py-3">
                                            <i class="bx bx-save fs-5"></i>
                                            <strong>Save Invoice</strong>
                                        </button>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <button type="button" class="lb-btn lb-btn-outline w-100 py-2" id="sidebarPreviewBtn">
                                                    <i class="bx bx-show"></i> Preview
                                                </button>
                                            </div>
                                            <div class="col-6">
                                                <a href="/invoices" class="lb-btn lb-btn-light w-100 py-2 text-center text-decoration-none">
                                                    <i class="bx bx-x"></i> Cancel
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 pt-3 border-top text-center">
                                        <p class="small text-muted mb-0">
                                            <i class="bx bx-lock-alt"></i> All data is encrypted and secure
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </section>

    <!-- =========== Add New Client Modal =========== -->
    <div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="/manage-invoice-client" method="post" id="addClientForm"> {{-- Added ID --}}
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addClientModalLabel">Add New Client</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3"> {{-- Added g-3 for spacing --}}
                            {{-- Removed duplicate @csrf --}}
                            <div class="col-md-6 form-group">
                                <label for="modal_client_name">Name*</label> {{-- Unique ID --}}
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-user'></i></span>
                                    <input type="text" class="form-control" id="modal_client_name" name="name"
                                        placeholder="Enter Name*" required>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="modal_client_email">Email Address*</label> {{-- Unique ID --}}
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-envelope-open'></i></span>
                                    <input type="email" class="form-control" id="modal_client_email" name="email"
                                        placeholder="Enter Email Id*" required>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="modal_client_mob">Mobile Number*</label> {{-- Unique ID --}}
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-phone'></i></span>
                                    <input type="text" class="form-control" id="modal_client_mob" name="mob"
                                        placeholder="Enter Mobile Number*" value="91" required>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="modal_client_alterMob">Alternative Mobile Number</label> {{-- Unique ID --}}
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-phone'></i></span>
                                    <input type="text" class="form-control" id="modal_client_alterMob" name="alterMob"
                                        placeholder="Enter Mobile Number" value="91">
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="modal_client_whatsapp">Whatsapp</label> {{-- Unique ID --}}
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bxl-whatsapp'></i></span>
                                    <input type="text" class="form-control" id="modal_client_whatsapp" name="whatsapp"
                                        placeholder="Enter Whatsapp Number" value="91">
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="modal_client_company">Company*</label> {{-- Unique ID --}}
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-briefcase'></i></span>
                                    <input type="text" class="form-control" id="modal_client_company" name="company"
                                        placeholder="Enter Company*" required>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="modal_client_position">Position</label> {{-- Unique ID --}}
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-user'></i></span>
                                    <input type="text" class="form-control" id="modal_client_position" name="position"
                                        placeholder="Enter Position">
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="modal_client_industry">Industry</label> {{-- Unique ID --}}
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-building'></i></span>
                                    <input type="text" class="form-control" id="modal_client_industry" name="industry"
                                        placeholder="Enter Industry">
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="modal_client_address">Address</label> {{-- Unique ID --}}
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-home'></i></span>
                                    <input type="text" class="form-control" id="modal_client_address" name="address[]"
                                        placeholder="Enter Address">
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="modal_client_city">City</label> {{-- Unique ID --}}
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-map'></i></span>
                                    <input type="text" class="form-control" id="modal_client_city" name="address[]"
                                        placeholder="Enter City">
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="modal_client_state">State</label> {{-- Unique ID --}}
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-map-pin'></i></span>
                                    <input type="text" class="form-control" id="modal_client_state" name="address[]"
                                        placeholder="Enter State">
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="modal_client_country">Country</label> {{-- Unique ID --}}
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-globe'></i></span>
                                    <input type="text" class="form-control" id="modal_client_country" name="address[]"
                                        placeholder="Enter Country">
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="modal_client_website">Website</label> {{-- Unique ID --}}
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-link'></i></span>
                                    <input type="url" class="form-control" id="modal_client_website" name="website"
                                        placeholder="Enter Website Link"> {{-- Corrected ID --}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-3">
                        <button type="button" class="btn btn-light rounded-pill border px-4" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-indigo rounded-pill px-4" id="saveClientBtn"> {{-- Added
                            ID --}}
                            <i class='bx bx-save'></i> Save Client
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- =========== End Add New Client Modal =========== -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const itemsContainer = document.getElementById('items-card-container');
            const addItemBtn = document.getElementById('addItemButton');
            const availableTaxes = @json($available_taxes);

            // --- 1. Helper: Format Currency ---
            function formatCurrency(amount) {
                return new Intl.NumberFormat('en-IN', {
                    style: 'currency',
                    currency: 'INR',
                    minimumFractionDigits: 2
                }).format(amount);
            }

            // --- 2. Helper: Auto-resize Textareas ---
            function initAutoresize() {
                document.querySelectorAll('.mp-autoresize').forEach(textarea => {
                    textarea.style.height = 'auto';
                    textarea.style.height = (textarea.scrollHeight) + 'px';
                    textarea.addEventListener('input', function() {
                        this.style.height = 'auto';
                        this.style.height = (this.scrollHeight) + 'px';
                    });
                });
            }

            // --- 3. Helper: Renumber Items & Update Names ---
            function renumberItems() {
                const rows = itemsContainer.querySelectorAll('.mp-item-row');
                rows.forEach((row, index) => {
                    row.querySelector('.mp-item-num').textContent = index + 1;
                    
                    // Update field names for Laravel array handling
                    row.querySelectorAll('[name*="invoice_items"]').forEach(input => {
                        const name = input.getAttribute('name');
                        const newName = name.replace(/invoice_items\[\d+\]/, `invoice_items[${index}]`);
                        input.setAttribute('name', newName);
                    });
                });
            }

            // --- 4. CALCULATION ENGINE ---
            function calculateTotals() {
                let subTotal = 0;
                const taxTotals = {}; // Stores sum for each unique tax label

                itemsContainer.querySelectorAll('.mp-item-row').forEach(row => {
                    const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                    const rate = parseFloat(row.querySelector('.item-price').value) || 0;
                    const lineSub = qty * rate;
                    subTotal += lineSub;

                    let lineTaxTotal = 0;
                    const taxSelect = row.querySelector('.item-tax');
                    if (taxSelect) {
                        Array.from(taxSelect.selectedOptions).forEach(opt => {
                            const [taxIdx, taxRateDecimal] = opt.value.split(':');
                            const rateVal = parseFloat(taxRateDecimal) || 0;
                            const taxAmt = lineSub * rateVal;
                            lineTaxTotal += taxAmt;

                            // Group taxes by label for the summary
                            const label = opt.text;
                            taxTotals[label] = (taxTotals[label] || 0) + taxAmt;
                        });
                    }

                    row.querySelector('.line-total').textContent = formatCurrency(lineSub + lineTaxTotal);
                });

                // Update Summary Fields
                document.getElementById('sub_total').value = subTotal.toFixed(2);
                document.getElementById('sub_total_display').textContent = formatCurrency(subTotal);

                // Calculate Discount
                const discPercent = parseFloat(document.getElementById('discount_percent').value) || 0;
                const discAmount = subTotal * (discPercent / 100);
                document.getElementById('discount_amount').value = discAmount.toFixed(2);
                document.getElementById('discount_amount_display').textContent = '-' + formatCurrency(discAmount);

                // Update Tax Rows in Summary
                const taxContainer = document.getElementById('tax_rows_container');
                taxContainer.innerHTML = '';
                let totalTaxSum = 0;
                
                Object.entries(taxTotals).forEach(([label, amount]) => {
                    if (amount > 0) {
                        totalTaxSum += amount;
                        const taxRow = document.createElement('div');
                        taxRow.className = 'summary-item d-flex justify-content-between align-items-center small';
                        taxRow.innerHTML = `
                            <span class="text-muted">${label}</span>
                            <span class="fw-semibold">${formatCurrency(amount)}</span>
                        `;
                        taxContainer.appendChild(taxRow);
                    }
                });

                if (taxContainer.innerHTML === '') {
                    taxContainer.innerHTML = '<div class="text-center text-muted small py-2">No taxes applied</div>';
                }

                // Final Grand Total
                const adjustment = parseFloat(document.getElementById('adjustment').value) || 0;
                const grandTotal = (subTotal - discAmount) + totalTaxSum + adjustment;

                document.getElementById('total_amount').value = grandTotal.toFixed(2);
                document.getElementById('total_display').textContent = formatCurrency(grandTotal);
            }

            // --- 5. EVENT DELEGATION (Items Management) ---
            addItemBtn.addEventListener('click', function() {
                const index = itemsContainer.querySelectorAll('.mp-item-row').length;
                const template = `
                    <div class="mp-item-row" style="animation: slideInUp 0.3s ease-out;">
                        <div class="mp-item-row-header">
                            <div class="mp-item-num">${index + 1}</div>
                            <div class="mp-item-row-title">New Item</div>
                            <button type="button" class="kb-action-btn kb-action-del ms-auto removeRowButton">
                                <i class='bx bx-trash'></i>
                            </button>
                        </div>
                        <div class="mp-item-row-body">
                            <div class="mp-item-field mp-item-name-field">
                                <label class="mp-item-label">Item Name</label>
                                <textarea class="lb-input-sm mp-autoresize item-name" name="invoice_items[${index}][short_description]" placeholder="What are you invoicing?"></textarea>
                            </div>
                            <div class="mp-item-field mp-item-desc-field">
                                <label class="mp-item-label">Description</label>
                                <textarea class="lb-input-sm mp-autoresize item-longdesc" name="invoice_items[${index}][long_description]" placeholder="Detailed description..."></textarea>
                            </div>
                            <div class="mp-item-field" style="width: 100px;">
                                <label class="mp-item-label">SAC Code</label>
                                <input type="text" class="lb-input-sm text-center item-sac_code" name="invoice_items[${index}][sac_code]" value="998314">
                            </div>
                            <div class="mp-item-field mp-item-qty-field">
                                <label class="mp-item-label">Qty/Hrs</label>
                                <input type="number" class="lb-input-sm text-center item-qty" name="invoice_items[${index}][quantity]" value="1" step="any" required>
                            </div>
                            <div class="mp-item-field mp-item-rate-field">
                                <label class="mp-item-label">Rate (₹)</label>
                                <input type="number" class="lb-input-sm text-end item-price" name="invoice_items[${index}][price]" value="0.00" step="any" required>
                            </div>
                            <div class="mp-item-field mp-item-tax-field">
                                <label class="mp-item-label">Tax</label>
                                <select class="form-select form-select-sm item-tax" multiple name="invoice_items[${index}][tax_rate][]">
                                    ${availableTaxes.map(t => `<option value="${t.value}">${t.label}</option>`).join('')}
                                </select>
                            </div>
                            <div class="mp-item-field mp-item-amount-field">
                                <label class="mp-item-label">Amount</label>
                                <span class="mp-item-amount-val line-total">₹0.00</span>
                            </div>
                        </div>
                    </div>
                `;
                itemsContainer.insertAdjacentHTML('beforeend', template);
                initAutoresize();
                calculateTotals();
            });

            itemsContainer.addEventListener('click', function(e) {
                if (e.target.closest('.removeRowButton')) {
                    if (itemsContainer.querySelectorAll('.mp-item-row').length > 1) {
                        e.target.closest('.mp-item-row').remove();
                        renumberItems();
                        calculateTotals();
                    } else {
                        alert("Invoice must have at least one item.");
                    }
                }
            });

            itemsContainer.addEventListener('input', function(e) {
                if (e.target.matches('.item-qty, .item-price')) {
                    calculateTotals();
                }
            });

            itemsContainer.addEventListener('change', function(e) {
                if (e.target.matches('.item-tax')) {
                    calculateTotals();
                }
            });

            // Summary Inputs
            document.getElementById('discount_percent').addEventListener('input', calculateTotals);
            document.getElementById('adjustment').addEventListener('input', calculateTotals);

            // --- 6. Client Auto-fill ---
            $('#client_id').on('change', function() {
                const opt = this.options[this.selectedIndex];
                if (!opt.value) return;

                const address = opt.getAttribute('data-address') || '';
                const city = opt.getAttribute('data-city') || '';
                const state = opt.getAttribute('data-state') || '';
                const country = opt.getAttribute('data-country') || '';
                const zip = opt.getAttribute('data-zip') || '';
                const gst = opt.getAttribute('data-gstno') || '';

                const fullAddress = [address, [city, state].filter(Boolean).join(', '), [country, zip].filter(Boolean).join(' ')].filter(Boolean).join('\n');

                document.getElementById('billing_address').value = fullAddress;
                document.getElementById('client_gst').value = gst;
                
                // Trigger autoresize
                initAutoresize();
                
                // Visual feedback: expand billing section if collapsed
                const billingCollapse = document.getElementById('collapseBilling');
                if (!billingCollapse.classList.contains('show')) {
                    new bootstrap.Collapse(billingCollapse).show();
                }
            });

            // --- 7. Preview Action ---
            const handlePreview = () => {
                @if(!empty($invoice->id))
                    window.open("{{ route('invoicePreview', ['id' => $invoice->id]) }}", "_blank");
                @else
                    alert("Please save the invoice first to generate a full preview.");
                @endif
            };
            document.getElementById('previewInvoiceBtn').addEventListener('click', handlePreview);
            document.getElementById('sidebarPreviewBtn').addEventListener('click', handlePreview);

            // --- 8. INITIALIZE ---
            initAutoresize();
            if (itemsContainer.querySelectorAll('.mp-item-row').length === 0) {
                addItemBtn.click();
            }
            calculateTotals();
        });
    </script>
@endsection