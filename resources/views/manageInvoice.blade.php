@extends('layout')
@section('title','Manage Invoice - eseCRM')

@section('content')
<style>
    @media (max-width:767px){
        .table>thead>tr>th {
            min-width: 185px !important;
        }
    }
</style>
<section class="task__section">
    <div class="text">
        <i class="bx bx-menu" id="mbtn"></i>
        Invoice
        <a href="/signout" class="logoutbtn"><i class='bx bx-log-out'></i></a>
    </div>

    <div class="container-fluid">
        <form action="/manage-invoice" method="POST">
            
        <div class="board-title board-title-flex mb-4">
            <div class="d-flex g-22">
                <a href="/invoices" class="btn btn-primary bg-primary text-white btn-sm back-btn"><i class="bx bx-arrow-back"></i></a>
                @if(!empty($invoice->id))
                    <h1>Edit Invoice #{{ $invoice->invoice_number ?? '' }}</h1>
                @else
                    <h1>Create New Invoice</h1>
                @endif
            </div>
            <div>
                <button type="button" class="btn btn-info me-2" id="previewInvoiceBtn"><i class='bx bx-show'></i> Preview</button>
                <button type="submit" class="btn btn-primary bg-primary text-white px-4"><i class='bx bx-save'></i> Save Invoice</button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 csp-3">
                <div class="row g-3 bg-white p-3 mb-2 rounded">
                    @csrf
                    @if(!empty($invoice->id))
                        <input type="hidden" name="id" value="{{ $invoice->id }}">
                    @endif

                    <!-- Invoice Number -->
                    <div class="col-md-3 form-group">
                        <label for="invoice_number">Invoice Number*</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-hash'></i></span>
                            <input
                                type="text"
                                class="form-control"
                                id="invoice_number"
                                name="invoice_number"
                                maxlength="20"
                                placeholder="Enter Invoice Number"
                                value="{{ old('invoice_number', $invoice->invoice_number ?? null) }}"
                                required
                            >
                        </div>
                    </div>

                    <!-- Invoice Type -->
                    <div class="col-md-3 form-group">
                        <label for="invoice_type">Type*</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-hash'></i></span>
                            <select class="form-control" id="invoice_type" name="invoice_type" required>
                                <option value="invoice" @if(old('invoice_type', $invoice->invoice ?? '') == 'invoice') selected @endif>Invoice</option>
                                <option value="proforma" @if(old('invoice_type', $invoice->invoice ?? '') == 'proforma') selected @endif>Proforma Invoice</option>
                                <option value="tax" @if(old('invoice_type', $invoice->invoice ?? '') == 'tax') selected @endif>Tax Invoice</option>
                            </select>
                        </div>
                    </div>

                    
                    <!-- Client Selection + Add New Client Button -->
                    <div class="col-md-6 form-group">
                        <label for="client_id">Client*</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-user'></i></span>
                            <select class="selectpicker form-select" id="client_id" name="client_id" data-live-search="true" required>
                                <option value="">Select Client</option>
                                @foreach($clients as $client)
                                    @php $location = json_decode(($client->location ?? ''),true) @endphp
                                    <option
                                        value="{{ $client->id }}"  data-name="{{ $client->name ?? '' }}" data-company="{{ $client->company ?? '' }}" data-email="{{ $client->email ?? '' }}" data-mob="{{ $client->mob ?? '' }}" data-gstno="{{ $client->gstno ?? '' }}" data-address="{{ $location[0] ?? '' }}" data-city="{{ $location[1] ?? '' }}" data-state="{{ $location[2] ?? '' }}" data-country="{{ $location[3] ?? '' }}" data-zip="{{ $location[4] ?? '' }}"
                                        @if(old('client_id', $invoice->client_id ?? '') == $client->id) selected @endif
                                    >
                                        {{ $client->name .' - '. $client->company }}
                                    </option>
                                @endforeach
                            </select>
                            <!-- Add New Client Button -->
                            <button
                                type="button"
                                class="btn btn-primary bg-primary text-white"
                                data-bs-toggle="modal"
                                data-bs-target="#addClientModal"
                                title="Add New Client" {{-- Added title --}}
                            >
                                <i class='bx bx-plus'></i>
                            </button>
                        </div>
                    </div>

                    <!-- Invoice Date -->
                    <div class="col-md-6 form-group">
                        <label for="date">Invoice Date*</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-calendar'></i></span>
                            <input
                                type="date"
                                class="form-control"
                                id="date"
                                name="date"
                                value="{{ old('date', $invoice && $invoice->date ? \Carbon\Carbon::parse($invoice->date)->format('Y-m-d') : now()->format('Y-m-d')) }}"
                                required
                            >
                        </div>
                    </div>

                    <!-- Due Date -->
                    <div class="col-md-6 form-group">
                        <label for="due_date">Due Date</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-calendar'></i></span>
                            <input
                                type="date"
                                class="form-control"
                                id="due_date"
                                name="due_date"
                                value="{{ old('due_date', $invoice && $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') : '') }}"
                            >
                        </div>
                    </div>

                    <!-- Invoice Status -->
                    <div class="col-md-6 form-group">
                        <label for="status">Status</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-info-circle'></i></span>
                            <select class="form-control" id="status" name="status">
                                <option value="unpaid"  @if(old('status', $invoice->status ?? 'unpaid') == 'unpaid') selected @endif>Unpaid</option>
                                <option value="paid"    @if(old('status', $invoice->status ?? '') == 'paid') selected @endif>Paid</option>
                                <option value="partial" @if(old('status', $invoice->status ?? '') == 'partial') selected @endif>Partially Paid</option>
                            </select>
                        </div>
                    </div>

                    <!-- Reference / PO # -->
                    <div class="col-md-6 form-group">
                        <label for="reference">Reference / PO #</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-note'></i></span>
                            <input
                                type="text"
                                class="form-control"
                                id="reference"
                                name="reference"
                                maxlength="25"
                                placeholder="Enter reference or PO number"
                                value="{{ old('reference', $invoice->reference ?? '') }}"
                            >
                        </div>
                    </div>

                    <!-- Payment Mode -->
                    <div class="col-md-6 form-group">
                        <label for="payment_mode">Payment Mode</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-money'></i></span>
                            <select class="form-control" id="payment_mode" name="payment_mode">
                                <option value="">Select Payment Mode</option>
                                <option value="cash"   @if(old('payment_mode', $invoice->payment_mode ?? '') == 'cash') selected @endif>Cash</option>
                                <option value="card"   @if(old('payment_mode', $invoice->payment_mode ?? '') == 'card') selected @endif>Credit/Debit Card</option>
                                <option value="bank"   @if(old('payment_mode', $invoice->payment_mode ?? '') == 'bank') selected @endif>Bank Transfer</option>
                                <option value="paypal" @if(old('payment_mode', $invoice->payment_mode ?? '') == 'paypal') selected @endif>PayPal</option>
                                <!-- Add your own options here -->
                            </select>
                        </div>
                    </div>

                    <!-- Currency -->
                    <div class="col-md-6 form-group">
                        <label for="currency">Currency</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-globe'></i></span>
                            <select class="form-control" id="currency" name="currency">
                                <option value="">Select Currency</option>
                                <option value="INR" @if(old('currency', $invoice->currency ?? 'INR') == 'INR') selected @endif>INR</option> {{-- Default to USD if not set --}}
                                <option value="USD" @if(old('currency', $invoice->currency ?? '') == 'USD') selected @endif>USD</option>
                                <option value="EUR" @if(old('currency', $invoice->currency ?? '') == 'EUR') selected @endif>EUR</option>
                                <option value="GBP" @if(old('currency', $invoice->currency ?? '') == 'GBP') selected @endif>GBP</option>
                                <!-- Add as many as needed -->
                            </select>
                        </div>
                    </div>

                    <!-- Sales Agent -->
                    <div class="col-md-6 form-group">
                        <label for="sales_agent">Sales Agent</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-user-voice'></i></span>
                            <input
                                type="text"
                                name="sales_agent"
                                id="sales_agent"
                                class="form-control"
                                placeholder="Enter Sales Agent name"
                                value="{{ old('sales_agent', $invoice->sales_agent ?? '') }}"
                            >
                        </div>
                    </div>
                    
                    <!-- Discount Type (Moved near summary section but kept name) -->
                    <div class="col-md-6 form-group">
                        <label for="discount_type" class="form-label">Discount Type</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-purchase-tag-alt'></i></span>
                            <select name="discount_type" id="discountApplicationType" class="form-control">
                                @php $currentAppType = old('discount_type', $invoice->discount_type ?? 'none'); @endphp
                                <option value="none"       @if($currentAppType == 'none') selected @endif>No Discount</option>
                                <option value="before-tax" @if($currentAppType == 'before-tax') selected @endif>Apply Before Tax</option>
                                <option value="after-tax"  @if($currentAppType == 'after-tax') selected @endif>Apply After Tax</option>
                            </select>
                        </div>
                    </div>

                    <!-- BILLING & SHIPPING DETAILS -->
                    <div class="col-md-6 form-group">
                        <label for="billing_address">Billing Address</label>
                        <textarea
                            class="form-control"
                            name="billing_address"
                            id="billing_address"
                            rows="4"
                            placeholder="Enter billing address"
                        >{{ old('billing_address', $invoice->billing_address ?? '') }}</textarea>
                    </div>

                    <div class="col-md-6 form-group">
                        <label for="shipping_address">Shipping Address</label>
                        <textarea
                            class="form-control"
                            name="shipping_address"
                            id="shipping_address"
                            rows="4"
                            placeholder="Enter shipping address"
                        >{{ old('shipping_address', $invoice->shipping_address ?? '') }}</textarea>
                    </div>

                    <div class="col-md-6 form-group">
                        <label for="client_gst">GST No.</label>
                        <input type="text"
                            class="form-control"
                            name="client_gst"
                            id="client_gst"
                            placeholder="Enter GST NO." value="{{ old('client_gst', $invoice->client_gstno ?? '') }}">
                    </div>

                    <!-- Recurring Invoice -->
                    <div class="col-md-6 form-group">
                        <!--<label for="recurring_invoice" class="form-label">Recurring Invoice</label>-->
                        <div class="form-check mt-4 pt-2">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="recurring_invoice"
                                name="recurring_invoice"
                                value="1"
                                @if(old('recurring_invoice', $invoice->recurring_invoice ?? false)) checked @endif
                            >
                            <h6 class="form-check-label mb-0" for="recurring_invoice">
                                Enable recurring invoice
                            </h6>
                        </div>
                    </div>

                    <!-- LINE ITEMS SECTION -->
                    <div class="col-md-12">
                        <h5 class="mt-3 mb-2">Invoice Items</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered align-top" id="invoiceItemsTable" style="border-radius: 8px!important;overflow: hidden;">
                                {{-- ... thead ... --}}
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 180px;" class="align-top">Item</th>
                                        <th style="width: 250px;" class="align-top">Description</th>
                                        <th style="width: 90px;" class="align-top text-center">SAC Code</th>
                                        <th style="width: 100px;" class="text-center align-top">Qty/Hours</th>
                                        <th style="width: 100px;" class="text-end align-top">Rate</th>
                                        <th style="width: 140px;" class="text-center align-top">Tax</th>
                                        <th style="width: 110px;" class="text-end align-top">Amount</th>
                                        <th style="width: 60px;" class="text-center align-top">Remove</th>
                                    </tr>
                                </thead>
                                <tbody id="invoiceItemsBody">
                                    {{-- Define available taxes for JS --}}
                                    @php
                                        // Re-using the existing available_taxes logic here...
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

                                    {{-- Loop through $invoiceItems --}}
                                    @forelse($invoiceItems as $index => $item)
                                        <tr>
                                            {{-- Item Name --}}
                                            <td class="align-top">
                                                <input type="text" class="form-control item-name" name="invoice_items[{{ $index }}][short_description]" placeholder="Item Name" value="{{ $item->short_description ?? '' }}">
                                                {{-- Store original price and quantity for potential recalculations if needed by complex logic later --}}
                                                <input type="hidden" class="item-original-price" value="{{ number_format($item->price ?? 0, 4, '.', '') }}">
                                                <input type="hidden" class="item-original-qty" value="{{ $item->quantity ?? 1 }}">
                                            </td>
                                            {{-- Description --}}
                                            <td class="align-top"><textarea class="form-control item-longdesc" rows="1" name="invoice_items[{{ $index }}][long_description]" placeholder="Description">{{ $item->long_description ?? '' }}</textarea></td>
                                            {{-- Sac Code --}}
                                            <td class="align-top text-center"><input type="number" class="form-control text-end item-sac_code" name="invoice_items[{{ $index }}][sac_code]" min="0" step="any" value="{{ $item->sac_code ?? '998314' }}"></td>
                                            {{-- Qty --}}
                                            <td class="align-top"><input type="number" class="form-control text-end item-qty" name="invoice_items[{{ $index }}][quantity]" value="{{ $item->quantity ?? 1 }}" min="0" step="any" required></td>
                                            {{-- Rate --}}
                                            <td class="align-top"><input type="number" class="form-control text-end item-price" name="invoice_items[{{ $index }}][price]" value="{{ number_format($item->price ?? 0, 2, '.', '') }}" min="0" step="any" placeholder="Rate" required></td>
                                            {{-- Tax --}}
                                            <td class="align-top">
                                                @php
                                                    $selected_taxes = [];
                                                    foreach ($available_taxes as $tax_option) {
                                                        list($tax_index, $tax_rate_decimal) = explode(':', $tax_option['value']);
                                                        $tax_rate_percent = floatval($tax_rate_decimal) * 100;
                                                        if ($tax_index == 0 && !empty($item->cgst_percent) && abs(floatval($item->cgst_percent) - $tax_rate_percent) < 0.001) $selected_taxes[] = $tax_option['value'];
                                                        elseif ($tax_index == 1 && !empty($item->sgst_percent) && abs(floatval($item->sgst_percent) - $tax_rate_percent) < 0.001) $selected_taxes[] = $tax_option['value'];
                                                        elseif ($tax_index == 2 && !empty($item->igst_percent) && abs(floatval($item->igst_percent) - $tax_rate_percent) < 0.001) $selected_taxes[] = $tax_option['value'];
                                                        elseif ($tax_index == 3 && !empty($item->vat_percent) && abs(floatval($item->vat_percent) - $tax_rate_percent) < 0.001) $selected_taxes[] = $tax_option['value'];
                                                    }
                                                @endphp
                                                <select class="form-select item-tax" multiple name="invoice_items[{{ $index }}][tax_rate][]" aria-label="Select Taxes">
                                                    @foreach($available_taxes as $tax)
                                                        <option value="{{ $tax['value'] }}" @if(in_array($tax['value'], $selected_taxes)) selected @endif>{{ $tax['label'] }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            {{-- Amount --}}
                                            <td class="text-end align-middle line-total">0.00</td>
                                            {{-- Remove Button --}}
                                            <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger removeRowButton" title="Remove Item"><i class='bx bx-trash'></i></button></td>
                                        </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{-- Add Item Button --}}
                        <div class="mb-3">
                            <button type="button" class="btn btn-secondary" id="addItemButton"><i class='bx bx-plus'></i> Add Item</button>
                        </div>
                    </div>

                    <!-- Calculation Summaries -->
                    <div class="col-md-4 offset-md-8">
                        <h5 class="mt-3 mb-2">Summary</h5>
                        <table class="table border">
                            <tr>
                                <th class="fw-normal">Subtotal <span id="subtotalLabelSuffix"></span></th> {{-- Suffix for clarity --}}
                                <td class="text-end" id="subTotal">0.00</td>
                            </tr>
                             {{-- Optional: Show discount amount BEFORE tax if applicable --}}
                             <tr id="discountBeforeTaxRow" style="display: none;">
                                <th class="fw-normal fst-italic">Discount (Before Tax)</th>
                                <td class="text-end fst-italic" id="discountBeforeTaxAmount">(-0.00)</td>
                            </tr>
                            <tr>
                                <th class="fw-normal">Total Tax</th>
                                <td class="text-end" id="totalTax">0.00</td>
                            </tr>

                            <!-- RENAMED: Discount Calculation Method -->
                            <tr>
                                <th class="align-middle fw-normal">Discount</th>
                                <td>
                                    <div class="input-group">
                                        <!-- Discount Value Type (Flat/%) -->
                                        <select
                                            name="discount_mode"
                                            id="discountValueType"
                                            class="form-control bg-white"
                                            style="max-width: 60px;"
                                            aria-label="Discount Value Type"
                                        >
                                            @php $currentValueType = old('discount_mode', $invoice->discount_mode ?? 'flat'); @endphp
                                            <option value="flat"       @if($currentValueType == 'flat') selected @endif>Flat</option>
                                            <option value="percentage" @if($currentValueType == 'percentage') selected @endif>%</option>
                                        </select>
                                        <!-- Discount Value -->
                                        <input
                                            type="number"
                                            class="form-control text-end"
                                            name="discount_value" {{-- RENAMED name --}}
                                            id="discountValue"    {{-- Kept id --}}
                                            step="any" min="0"
                                            value="{{ old('discount', number_format($invoice->discount ?? 0, 2, '.', '')) }}"
                                            placeholder="0.00"
                                            aria-label="Discount Value"
                                        >
                                    </div>
                                </td>
                            </tr>
                            {{-- Shows the final calculated discount amount regardless of when it was applied --}}
                            <tr id="discountAmountRow" style="display: none;">
                                <th class="fw-normal fst-italic">Total Discount Applied</th>
                                <td class="text-end fst-italic" id="discountAmountCalculated">(-0.00)</td>
                            </tr>

                            <!-- Adjustment row -->
                            <tr>
                                <th class="align-middle fw-normal">Advance Payment</th>
                                <td>
                                    <input
                                        type="number" class="form-control text-end" name="adjustment" id="adjustment"
                                        step="any" value="{{ old('adjustment', number_format($invoice->adjustment ?? 0, 2, '.', '')) }}"
                                        placeholder="0.00" aria-label="Adjustment">
                                </td>
                            </tr>

                            <!-- Grand Total -->
                            <tr class="table-light">
                                <th class="fw-bold">Grand Total</th>
                                <td class="text-end fw-bold fs-5" id="grandTotal">0.00</td>
                            </tr>
                        </table>
                        <input type="hidden" name="gtAmount" id="gtAmount" value="0" />
                    </div>
                    
                    <!-- =========== NEW: Bank Details Section =========== -->
                    <div class="col-md-12">
                        <h5 class="mt-3 mb-0">Company Bank Details for Payment</h5>
                    </div>
                    @php $company = session('companies'); $companyBankDetails = json_decode($invoice->bank_details ?? $company->bank_details ?? ''); @endphp
                    <div class="col-md-6 form-group">
                        <label for="bank_name">Bank Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bxs-bank'></i></span>
                            <input
                                type="text"
                                class="form-control"
                                id="bank_name"
                                name="bank_details[]"
                                placeholder="Enter Bank Name"
                                value="{{ old('bank_name', $companyBankDetails[0] ?? '') }}"
                            >
                        </div>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="bank_account_holder_name">Account Holder Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bxs-user-detail'></i></span>
                            <input
                                type="text"
                                class="form-control"
                                id="bank_account_holder_name"
                                name="bank_details[]"
                                placeholder="Enter Account Holder Name"
                                value="{{ old('bank_account_holder_name', $companyBankDetails[1] ?? '') }}"
                            >
                        </div>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="bank_account_number">Account Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-barcode'></i></span>
                            <input
                                type="text"
                                class="form-control"
                                id="bank_account_number"
                                name="bank_details[]"
                                placeholder="Enter Account Number"
                                value="{{ old('bank_account_number', $companyBankDetails[2] ?? '') }}"
                            >
                        </div>
                    </div>
                    <!--<div class="col-md-6 form-group">
                        <label for="bank_branch_name">Branch Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-git-branch'></i></span>
                            <input
                                type="text"
                                class="form-control"
                                id="bank_branch_name"
                                name="bank_branch_name"
                                placeholder="Enter Branch Name"
                                value="{{ old('bank_branch_name', $companyBankDetails[3] ?? '') }}"
                            >
                        </div>
                    </div>-->
                    <div class="col-md-6 form-group">
                        <label for="bank_ifsc_code">IFSC Code / SWIFT Code</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-fingerprint'></i></span>
                            <input
                                type="text"
                                class="form-control"
                                id="bank_ifsc_code"
                                name="bank_details[]"
                                placeholder="Enter IFSC or SWIFT Code"
                                value="{{ old('bank_ifsc_code', $companyBankDetails[3] ?? '') }}"
                            >
                        </div>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="upi">Upi Id</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-barcode'></i></span>
                            <input
                                type="text"
                                class="form-control"
                                id="upi"
                                name="bank_details[]"
                                placeholder="Enter Upi Id"
                                value="{{ old('upi', $companyBankDetails[4] ?? '') }}"
                            >
                        </div>
                    </div>
                    <!-- =========== End Bank Details Section =========== -->

                    <div class="col-md-12 form-group">
                         <label for="admin_note">Admin Note</label>
                         <textarea class="form-control" name="admin_note" id="admin_note" rows="3" placeholder="Admin-only note">{{ old('admin_note', $invoice->admin_note ?? '') }}</textarea>
                     </div>
                    <div class="col-md-12 form-group">
                         <label for="client_note">Client Note</label>
                         <textarea class="form-control" name="client_note" id="client_note" rows="3" placeholder="Message for the client">{{ old('client_note', $invoice->client_note ?? '') }}</textarea>
                     </div>
                    <div class="col-md-12 form-group">
                         <label for="terms">Terms & Conditions</label>
                         <textarea class="form-control" name="terms" id="terms" rows="3" placeholder="Terms and conditions">{{ old('terms', $invoice->terms ?? '') }}</textarea>
                     </div>
                    <div class="col-md-12 text-end mt-4">
                        <button type="button" class="btn btn-info me-2" id="previewInvoiceBtn"><i class='bx bx-show'></i> Preview</button>
                        <button type="submit" class="btn btn-primary bg-primary text-white px-4"><i class='bx bx-save'></i> Save Invoice</button>
                        <!--<button type="reset" class="btn btn-outline-secondary border px-4">Reset</button>-->
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
                                <input type="text" class="form-control" id="modal_client_name" name="name" placeholder="Enter Name*" required>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="modal_client_email">Email Address*</label> {{-- Unique ID --}}
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-envelope-open'></i></span>
                                <input type="email" class="form-control" id="modal_client_email" name="email" placeholder="Enter Email Id*" required>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="modal_client_mob">Mobile Number*</label> {{-- Unique ID --}}
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-phone'></i></span>
                                <input type="text" class="form-control" id="modal_client_mob" name="mob" placeholder="Enter Mobile Number*" value="91" required>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="modal_client_alterMob">Alternative Mobile Number</label> {{-- Unique ID --}}
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-phone'></i></span>
                                <input type="text" class="form-control" id="modal_client_alterMob" name="alterMob" placeholder="Enter Mobile Number" value="91">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="modal_client_whatsapp">Whatsapp</label> {{-- Unique ID --}}
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bxl-whatsapp'></i></span>
                                <input type="text" class="form-control" id="modal_client_whatsapp" name="whatsapp" placeholder="Enter Whatsapp Number" value="91">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="modal_client_company">Company*</label> {{-- Unique ID --}}
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-briefcase'></i></span>
                                <input type="text" class="form-control" id="modal_client_company" name="company" placeholder="Enter Company*" required>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="modal_client_position">Position</label> {{-- Unique ID --}}
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-user'></i></span>
                                <input type="text" class="form-control" id="modal_client_position" name="position" placeholder="Enter Position">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="modal_client_industry">Industry</label> {{-- Unique ID --}}
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-building'></i></span>
                                <input type="text" class="form-control" id="modal_client_industry" name="industry" placeholder="Enter Industry">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="modal_client_address">Address</label> {{-- Unique ID --}}
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-home'></i></span>
                                <input type="text" class="form-control" id="modal_client_address" name="address[]" placeholder="Enter Address">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="modal_client_city">City</label> {{-- Unique ID --}}
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-map'></i></span>
                                <input type="text" class="form-control" id="modal_client_city" name="address[]" placeholder="Enter City">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="modal_client_state">State</label> {{-- Unique ID --}}
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-map-pin'></i></span>
                                <input type="text" class="form-control" id="modal_client_state" name="address[]" placeholder="Enter State">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="modal_client_country">Country</label> {{-- Unique ID --}}
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-globe'></i></span>
                                <input type="text" class="form-control" id="modal_client_country" name="address[]" placeholder="Enter Country">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="modal_client_website">Website</label> {{-- Unique ID --}}
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-link'></i></span>
                                <input type="url" class="form-control" id="modal_client_website" name="website" placeholder="Enter Website Link"> {{-- Corrected ID --}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary bg-primary text-white" id="saveClientBtn"> {{-- Added ID --}}
                        <i class='bx bx-save'></i> Save Client
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- =========== End Add New Client Modal =========== -->

<script>
    // Store available tax options globally
    const availableTaxes = @json($available_taxes);

    // Helper to format currency
    function formatCurrency(amount) {
        return parseFloat(amount || 0).toFixed(2);
    }

    // Recalculate Invoice Totals
    function recalculateTotals() {
        // --- Get Control Elements ---
        const discountApplicationTypeSelect = document.getElementById('discountApplicationType');
        const discountValueTypeSelect = document.getElementById('discountValueType');
        const discountValueInput = document.getElementById('discountValue');
        const adjustmentInput = document.getElementById('adjustment');

        // --- Get Control Values ---
        const discountApplicationType = discountApplicationTypeSelect.value; // 'none', 'before-tax', 'after-tax'
        const discountValueType = discountValueTypeSelect.value;         // 'flat', 'percentage'
        const discountValue = parseFloat(discountValueInput.value) || 0;
        const adjustment = parseFloat(adjustmentInput.value) || 0;

        // --- Initialize Calculation Variables ---
        let initialSubTotal = 0;
        let finalSubTotal = 0; // Subtotal after potential 'before-tax' discount
        let totalTax = 0;
        let calculatedDiscountAmount = 0;

        // --- Stage 1: Calculate Initial Subtotal and Tax (per line) ---
        const itemRows = document.querySelectorAll('#invoiceItemsBody tr');
        itemRows.forEach((row) => {
            const qtyInput = row.querySelector('.item-qty');
            const priceInput = row.querySelector('.item-price');
            const taxSelect = row.querySelector('.item-tax');
            const lineTotalEl = row.querySelector('.line-total');

            const qty = parseFloat(qtyInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const lineSubTotal = qty * price;

            let lineTaxAmount = 0;
            if (taxSelect) {
                for (const option of taxSelect.selectedOptions) {
                    const valueParts = option.value.split(':');
                    if (valueParts.length === 2) {
                        const rate = parseFloat(valueParts[1]);
                        if (!isNaN(rate) && rate > 0) {
                            // Tax is initially calculated on the original line subtotal
                            lineTaxAmount += lineSubTotal * rate;
                        }
                    }
                }
            }

            // Store the initial subtotal and tax *before* any overall discount is applied
            row.dataset.initialSubtotal = lineSubTotal; // Store on the row for potential later use
            row.dataset.initialTax = lineTaxAmount;     // Store on the row

            initialSubTotal += lineSubTotal;

            // Temporarily update line total display (might be adjusted later if 'before-tax' discount)
            lineTotalEl.textContent = formatCurrency(lineSubTotal + lineTaxAmount);

            // We sum initial tax here, but it might be recalculated if discount is 'before-tax'
            // totalTax += lineTaxAmount; // Let's calculate final tax later
        });

        // --- Stage 2: Calculate Discount Amount ---
        if (discountApplicationType !== 'none' && discountValue > 0) {
            let discountBase = 0;
            if (discountApplicationType === 'before-tax') {
                discountBase = initialSubTotal;
            } else { // 'after-tax'
                // For 'after-tax', we need the tax calculated on the *initial* subtotal first
                let initialTotalTax = 0;
                 itemRows.forEach(row => {
                     initialTotalTax += parseFloat(row.dataset.initialTax || 0);
                 });
                discountBase = initialSubTotal + initialTotalTax;
            }

            if (discountValueType === 'percentage') {
                calculatedDiscountAmount = discountBase * (discountValue / 100.0);
            } else { // 'flat'
                calculatedDiscountAmount = discountValue;
            }

            // Ensure discount doesn't exceed the base it applies to
            calculatedDiscountAmount = Math.min(calculatedDiscountAmount, discountBase > 0 ? discountBase : 0 ); // Prevent negative total
        } else {
            calculatedDiscountAmount = 0; // No discount applied
        }

        // --- Stage 3: Calculate Final Subtotal, Tax, and Line Totals ---
        totalTax = 0; // Reset tax, recalculate based on discount type

        if (discountApplicationType === 'before-tax') {
            finalSubTotal = initialSubTotal - calculatedDiscountAmount;
            // Now, recalculate tax based on the *discounted* line subtotals
            itemRows.forEach(row => {
                const lineInitialSub = parseFloat(row.dataset.initialSubtotal || 0);
                 // Apply discount proportionally to the line item's subtotal
                 const lineDiscountRatio = initialSubTotal > 0 ? (lineInitialSub / initialSubTotal) : 0;
                 const lineDiscountAmount = calculatedDiscountAmount * lineDiscountRatio;
                 const lineFinalSubtotal = lineInitialSub - lineDiscountAmount;

                 let lineFinalTaxAmount = 0;
                 const taxSelect = row.querySelector('.item-tax');
                 if (taxSelect) {
                    for (const option of taxSelect.selectedOptions) {
                        const valueParts = option.value.split(':');
                        if (valueParts.length === 2) {
                            const rate = parseFloat(valueParts[1]);
                            if (!isNaN(rate) && rate > 0) {
                                // Calculate tax on the *discounted* line subtotal
                                lineFinalTaxAmount += lineFinalSubtotal * rate;
                            }
                        }
                    }
                 }
                 totalTax += lineFinalTaxAmount; // Sum up the final tax

                 // Update line total display based on discounted subtotal + recalculated tax
                 const lineTotalEl = row.querySelector('.line-total');
                 lineTotalEl.textContent = formatCurrency(lineFinalSubtotal + lineFinalTaxAmount);
            });
             // Subtotal displayed is the one *after* the before-tax discount
             document.getElementById('subtotalLabelSuffix').textContent = '(After Discount)';


        } else {
             // For 'none' or 'after-tax', the final subtotal is the initial one
             finalSubTotal = initialSubTotal;
             // Tax is the sum of initially calculated taxes
             itemRows.forEach(row => {
                 totalTax += parseFloat(row.dataset.initialTax || 0);
                 // Line total remains initial subtotal + initial tax
                  const lineTotalEl = row.querySelector('.line-total');
                  lineTotalEl.textContent = formatCurrency(parseFloat(row.dataset.initialSubtotal || 0) + parseFloat(row.dataset.initialTax || 0));
             });
             document.getElementById('subtotalLabelSuffix').textContent = ''; // No suffix needed

        }


        // --- Stage 4: Calculate Grand Total ---
        let grandTotal = 0;
        if (discountApplicationType === 'before-tax') {
            grandTotal = finalSubTotal + totalTax - adjustment;
            // Note: calculatedDiscountAmount was already subtracted to get finalSubTotal
        } else if (discountApplicationType === 'after-tax') {
             grandTotal = finalSubTotal + totalTax - calculatedDiscountAmount - adjustment;
        } else { // 'none'
             grandTotal = finalSubTotal + totalTax - adjustment;
        }

        // --- Stage 5: Update Summary Display ---
        document.getElementById('subTotal').textContent = formatCurrency(finalSubTotal);
        document.getElementById('totalTax').textContent = formatCurrency(totalTax);

        // Display calculated discount amount
        const discountAmountRow = document.getElementById('discountAmountRow');
        const discountBeforeTaxRow = document.getElementById('discountBeforeTaxRow'); // Get the specific before-tax row
        const discountAmountCalculatedEl = document.getElementById('discountAmountCalculated');
        const discountBeforeTaxAmountEl = document.getElementById('discountBeforeTaxAmount');

        discountAmountRow.style.display = 'none'; // Hide initially
        discountBeforeTaxRow.style.display = 'none'; // Hide initially

        if (calculatedDiscountAmount > 0) {
            const formattedDiscount = `(-${formatCurrency(calculatedDiscountAmount)})`;
            // Show the total discount applied row always if > 0
            discountAmountCalculatedEl.textContent = formattedDiscount;
            discountAmountRow.style.display = ''; // Show total discount row

            // Also show the specific "before tax" row if applicable
            if (discountApplicationType === 'before-tax') {
                 discountBeforeTaxAmountEl.textContent = formattedDiscount;
                 discountBeforeTaxRow.style.display = '';
            }
        }

        document.getElementById('grandTotal').textContent = formatCurrency(grandTotal);
        document.getElementById('gtAmount').value = formatCurrency(grandTotal);
    }

    // --- Event Listeners ---
    const invoiceItemsBody = document.getElementById('invoiceItemsBody');

    // Delegate listeners for item rows
    invoiceItemsBody.addEventListener('input', (e) => {
        if (
            e.target.classList.contains('item-qty') ||
            e.target.classList.contains('item-price')
        ) {
            recalculateTotals();
        }
    });
    invoiceItemsBody.addEventListener('change', (e) => {
        if (e.target.classList.contains('item-tax')) {
            recalculateTotals();
        }
    });

    // Listeners for summary/overall fields
    document.getElementById('discountApplicationType').addEventListener('change', recalculateTotals);
    document.getElementById('discountValueType').addEventListener('change', recalculateTotals);
    document.getElementById('discountValue').addEventListener('input', recalculateTotals);
    document.getElementById('adjustment').addEventListener('input', recalculateTotals);

    // --- Add/Remove Item Rows ---
    document.getElementById('addItemButton').addEventListener('click', () => {
        const tbody = document.getElementById('invoiceItemsBody');
        const newIndex = tbody.querySelectorAll('tr').length;

        const newRow = document.createElement('tr');
        // Simplified new row HTML (same structure as before)
        newRow.innerHTML = `
            <td class="align-top"><input type="text" class="form-control item-name" name="invoice_items[${newIndex}][short_description]" placeholder="Item Name"></td>
            <td class="align-top"><textarea class="form-control item-longdesc" rows="1" name="invoice_items[${newIndex}][long_description]" placeholder="Description"></textarea></td>
            <td class="align-top"><input type="number" class="form-control text-end item-sac_code" name="invoice_items[${newIndex}][sac_code]" value="998314" min="0" step="any"></td>
            <td class="align-top"><input type="number" class="form-control text-end item-qty" name="invoice_items[${newIndex}][quantity]" value="1" min="0" step="any" required></td>
            <td class="align-top"><input type="number" class="form-control text-end item-price" name="invoice_items[${newIndex}][price]" value="0.00" min="0" step="any" placeholder="Rate" required></td>
            <td class="align-top"><select class="form-select item-tax" multiple name="invoice_items[${newIndex}][tax_rate][]" aria-label="Select Taxes">${availableTaxes.map(tax => `<option value="${tax.value}">${tax.label}</option>`).join('')}</select></td>
            <td class="text-end align-middle line-total">0.00</td>
            <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger removeRowButton" title="Remove Item"><i class='bx bx-trash'></i></button></td>
        `;
        tbody.appendChild(newRow);
        updateItemIndices(); // Update indices before recalculating
        recalculateTotals();
    });

    // Remove item row (delegated)
    invoiceItemsBody.addEventListener('click', function(e) {
        const removeButton = e.target.closest('.removeRowButton');
        if (removeButton) {
            removeButton.closest('tr').remove();
            updateItemIndices();
            recalculateTotals();
        }
    });

    // Function to re-index item names/IDs
    function updateItemIndices() {
        const rows = document.querySelectorAll('#invoiceItemsBody tr');
        rows.forEach((row, index) => {
            row.querySelectorAll('[name^="invoice_items["]').forEach(input => {
                const name = input.getAttribute('name');
                const newName = name.replace(/invoice_items\[\d+\]/, `invoice_items[${index}]`);
                input.setAttribute('name', newName);
            });
            // Clear dataset values that might be stale after re-indexing/removal
            delete row.dataset.initialSubtotal;
            delete row.dataset.initialTax;
        });
    }

    // --- Initial Setup ---
    window.addEventListener('DOMContentLoaded', () => {
        // Initialize Bootstrap Selectpickers IF you are using them
        if (typeof $ !== 'undefined' && $.fn.selectpicker) {
           $('#client_id').selectpicker(); // Example for client
           // $('.item-tax').selectpicker(); // Example if using for taxes
        }

        // Add an empty row if creating a new invoice and no items exist
        @if(empty($invoice->id) && $invoiceItems->isEmpty())
            if (document.querySelectorAll('#invoiceItemsBody tr').length === 0) {
                document.getElementById('addItemButton').click(); // Adds row and triggers recalc
            } else {
                 recalculateTotals(); // Recalculate if somehow rows exist on new form
            }
        @else
            recalculateTotals(); // Always recalculate on load for editing
        @endif
    });
    
    const client_id = document.getElementById('client_id');

    client_id.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];

        // Read all the data attributes from the selected <option>
        const name = selectedOption.getAttribute('data-name');
        const email = selectedOption.getAttribute('data-email');
        const phone = selectedOption.getAttribute('data-mob');
        const gstno = selectedOption.getAttribute('data-gstno');
        const address = selectedOption.getAttribute('data-address');
        const city = selectedOption.getAttribute('data-city');
        const state = selectedOption.getAttribute('data-state');
        const zip = selectedOption.getAttribute('data-zip');
        const country = selectedOption.getAttribute('data-country');
        
        /*const inAddress = n2br((address || '') +"<br>"+ (city || '') + (state || '') +"<br>" + (country || '') + (zip || ''));
        console.log(inAddress);
        // Populate your form fields 
        document.getElementById('billing_address').value = inAddress || '';*/
        
        const cityState = [city, state].filter(Boolean).join(', ');
        const countryZip = [country, zip].filter(Boolean).join(' ');
        
        // Use newline characters for textarea
        const inAddress = [address, cityState, countryZip].filter(Boolean).join('\n');
        
        //console.log(inAddress);
        
        // Populate the textarea
        document.getElementById('billing_address').value = inAddress || '';
        document.getElementById('client_gst').value = gstno || '';


    });

    // --- Preview Button Logic ---
    // (Keep existing preview logic)
     document.getElementById('previewInvoiceBtn').addEventListener('click', function() {
        @if(empty($invoice->id))
            alert("Please save the invoice first before previewing.");
        @else
            try {
                const previewUrl = "{{ route('invoicePreview', ['id' => $invoice->id]) }}";
                window.open(previewUrl, "_blank");
            } catch (e) {
                 console.warn("Route 'invoicePreview' not found. Using fallback.");
                 const fallbackUrl = `/invoices/preview/{{ $invoice->id }}`;
                 window.open(fallbackUrl, "_blank");
            }
        @endif
    });

</script>
@endsection