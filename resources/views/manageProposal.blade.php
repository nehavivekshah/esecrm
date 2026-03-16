@extends('layout')
@section('title', 'Manage Proposal - eseCRM')

@section('content')
@php $taxes = !empty($companies->tax) ? explode(',', $companies->tax) : []; @endphp

<section class="task__section">
    @include('inc.header', ['title' => empty($proposal->id) ? 'Create Proposal' : 'Edit Proposal'])

    <div class="dash-container">

        {{-- ── Page heading bar ── --}}
        <div class="leads-toolbar mb-4">
            <div class="leads-toolbar-left gap-3">
                <a href="/proposals" class="kb-action-btn" title="Back to Proposals"
                   style="width:34px;height:34px;background:#f1f3f4;color:#5f6368;">
                    <i class="bx bx-arrow-back"></i>
                </a>
                <div>
                    @if(!empty($proposal->id))
                        <span class="lb-page-count"><i class="bx bx-edit"></i> Edit Proposal</span>
                        <span class="pr-id-badge ms-2">PRO-{{ str_pad($proposal->id, 4, '0', STR_PAD_LEFT) }}</span>
                    @else
                        <span class="lb-page-count"><i class="bx bx-plus-circle"></i> Create New Proposal</span>
                    @endif
                </div>
            </div>
            <div class="leads-toolbar-right gap-2">
                <button type="submit" form="proposalForm" class="lb-btn"
                        style="background:#f1f3f4;color:#202124;">
                    <i class="bx bx-save"></i> Save Draft
                </button>
                <button type="submit" form="proposalForm" name="submit" value="Save & Send"
                        class="lb-btn lb-btn-primary">
                    <i class="bx bx-send"></i> Save &amp; Send
                </button>
            </div>
        </div>

        <form id="proposalForm" action="/manage-proposal" method="post">
            @csrf
            <input type="hidden" name="id" id="id" value="{{ $proposal->id ?? '' }}">

            <div class="row g-4">

                {{-- ══════════════════════════════════════════
                     LEFT COLUMN — Info + Client + Items
                ══════════════════════════════════════════ --}}
                <div class="col-lg-12">

                    {{-- ── Proposal Information ── --}}
                    <div class="ml-card mb-4">
                        <div class="ml-card-header">
                            <div class="ml-card-icon" style="background:rgba(0,102,102,0.10);color:#006666;">
                                <i class="bx bx-file"></i>
                            </div>
                            <div>
                                <h6 class="ml-card-title">Proposal Information</h6>
                                <span class="ml-card-sub">Subject, dates, currency & discount</span>
                            </div>
                        </div>
                        <div class="ml-card-body">
                            <div class="row g-3">
                                {{-- Subject --}}
                                <div class="col-md-4">
                                    <label class="ml-label">Subject <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-rename"></i></span>
                                        <input type="text" name="subject" id="subject" class="form-control"
                                               placeholder="e.g. Website Redesign Proposal"
                                               value="{{ $proposal->subject ?? '' }}" required>
                                    </div>
                                    <div class="form-text">Short descriptive subject line</div>
                                </div>

                                {{-- Related type --}}
                                <div class="col-md-4">
                                    <label class="ml-label">Related To</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-link"></i></span>
                                        <select name="related" id="related" class="form-select" required>
                                            <option value="1" @if(($proposal->related ?? '') == '1') selected @endif>Lead</option>
                                            <option value="2" @if(($proposal->related ?? '') == '2') selected @endif>Client</option>
                                        </select>
                                    </div>
                                    <div class="form-text">Link proposal to a lead or client</div>
                                </div>

                                {{-- Related list --}}
                                <div class="col-md-4">
                                    <label class="ml-label" id="proposalType">Leads List</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-list-ul"></i></span>
                                        @if(($proposal->related ?? '') == '2')
                                            <select name="lead_id" id="relatedList" class="form-select">
                                                <option>Select…</option>
                                                @foreach($clients as $lead)
                                                    @php $location = json_decode(($lead->location ?? ''), true) @endphp
                                                    <option value="{{ $lead->id ?? '' }}"
                                                        data-name="{{ $lead->name ?? '' }}"
                                                        data-company="{{ $lead->company ?? '' }}"
                                                        data-email="{{ $lead->email ?? '' }}"
                                                        data-mob="{{ $lead->mob ?? '' }}"
                                                        data-address="{{ $location[0] ?? '' }}"
                                                        data-city="{{ $location[1] ?? '' }}"
                                                        data-state="{{ $location[2] ?? '' }}"
                                                        data-country="{{ $location[3] ?? '' }}"
                                                        data-zip="{{ $location[4] ?? '' }}"
                                                        @if(($proposal->lead_id ?? '') == ($lead->id ?? '')) selected @endif>
                                                        {{ $lead->name ?? '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <select name="lead_id" id="relatedList"
                                                    class="selectpicker form-select" data-live-search="true">
                                                <option>Select…</option>
                                                @foreach($leads as $lead)
                                                    @php $location = json_decode(($lead->location ?? ''), true) @endphp
                                                    <option value="{{ $lead->id ?? '' }}"
                                                        data-name="{{ $lead->name ?? '' }}"
                                                        data-company="{{ $lead->company ?? '' }}"
                                                        data-email="{{ $lead->email ?? '' }}"
                                                        data-mob="{{ $lead->mob ?? '' }}"
                                                        data-address="{{ $location[0] ?? '' }}"
                                                        data-city="{{ $location[1] ?? '' }}"
                                                        data-state="{{ $location[2] ?? '' }}"
                                                        data-country="{{ $location[3] ?? '' }}"
                                                        data-zip="{{ $location[4] ?? '' }}"
                                                        @if(($proposal->lead_id ?? '') == ($lead->id ?? '')) selected @endif>
                                                        {{ $lead->name ?? '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                    <div class="form-text">Auto-fills client details below</div>
                                </div>

                                {{-- Proposal date --}}
                                <div class="col-md-3">
                                    <label class="ml-label">Proposal Date <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                        <input type="date" name="proposal_date" id="proposalDate" class="form-control"
                                               value="{{ $proposal->proposal_date ?? date('Y-m-d') }}" required>
                                    </div>
                                </div>

                                {{-- Valid till --}}
                                <div class="col-md-3">
                                    <label class="ml-label">Valid Till</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-calendar-event"></i></span>
                                        <input type="date" name="open_till" id="openTill" class="form-control"
                                               value="{{ $proposal->open_till ?? \Carbon\Carbon::now()->addDays(7)->format('Y-m-d') }}">
                                    </div>
                                </div>

                                {{-- Currency --}}
                                <div class="col-md-3">
                                    <label class="ml-label">Currency <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-money"></i></span>
                                        <select name="currency" id="currency" class="form-select" required>
                                            <option value="INR" @if(($proposal->currency ?? '') == 'INR') selected @endif>₹ INR</option>
                                            <option value="USD" @if(($proposal->currency ?? '') == 'USD') selected @endif>$ USD</option>
                                            <option value="EUR" @if(($proposal->currency ?? '') == 'EUR') selected @endif>€ EUR</option>
                                            <option value="GBP" @if(($proposal->currency ?? '') == 'GBP') selected @endif>£ GBP</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Discount type --}}
                                <div class="col-md-3">
                                    <label class="ml-label">Discount Applied On</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-purchase-tag"></i></span>
                                        <select name="discount_type" id="discountType" class="form-select">
                                            <option value="none" @if(($proposal->discount_type ?? '') == 'none') selected @endif>No Discount</option>
                                            <option value="before-tax" @if(($proposal->discount_type ?? '') == 'before-tax') selected @endif>Before Tax</option>
                                            <option value="after-tax" @if(($proposal->discount_type ?? '') == 'after-tax') selected @endif>After Tax</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Tags --}}
                                <div class="col-md-8">
                                    <label class="ml-label">Tags</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-tag"></i></span>
                                        <input type="text" name="tags" id="tags" class="form-control"
                                               placeholder="Enter Tags (comma separated)"
                                               value="{{ $proposal->tags ?? '' }}">
                                    </div>
                                </div>

                                {{-- Notes --}}
                                <div class="col-12">
                                    <label class="ml-label">Proposal Notes</label>
                                    <textarea name="notes" id="editor" class="form-control" rows="3"
                                              placeholder="Add internal notes…">{{ $proposal->notes ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Client Details ── --}}
                    <div class="ml-card mb-4">
                        <div class="ml-card-header">
                            <div class="ml-card-icon" style="background:rgba(26,115,232,0.10);color:#1a73e8;">
                                <i class="bx bx-user-circle"></i>
                            </div>
                            <div>
                                <h6 class="ml-card-title">Client Details</h6>
                                <span class="ml-card-sub">Auto-filled when you choose from the list above</span>
                            </div>
                        </div>
                        <div class="ml-card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="ml-label">Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-user"></i></span>
                                        <input type="text" name="client_name" id="clientName" class="form-control"
                                               placeholder="Client Name"
                                               value="{{ $proposal->client_name ?? '' }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="ml-label">Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                        <input type="email" name="client_email" id="clientEmail" class="form-control"
                                               placeholder="client@example.com"
                                               value="{{ $proposal->client_email ?? '' }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="ml-label">Phone</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                        <input type="tel" name="client_phone" id="clientPhone" class="form-control"
                                               placeholder="+91 XXXXX XXXXX"
                                               value="{{ $proposal->client_phone ?? '91' }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="ml-label">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-home"></i></span>
                                        <input type="text" name="client_address" id="clientAddress" class="form-control"
                                               placeholder="Street address"
                                               value="{{ $proposal->client_address ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="ml-label">City</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-map"></i></span>
                                        <input type="text" name="client_city" id="clientCity" class="form-control"
                                               placeholder="City"
                                               value="{{ $proposal->client_city ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="ml-label">State / Province</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-map-pin"></i></span>
                                        <input type="text" name="client_state" id="clientState" class="form-control"
                                               placeholder="State"
                                               value="{{ $proposal->client_state ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="ml-label">Zip / Postal Code</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-pin"></i></span>
                                        <input type="text" name="client_zip" id="clientZip" class="form-control"
                                               placeholder="Postal code"
                                               value="{{ $proposal->client_zip ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="ml-label">Country</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-globe"></i></span>
                                        <input type="text" name="client_country" id="clientCountry" class="form-control"
                                               placeholder="Country"
                                               value="{{ $proposal->client_country ?? '' }}">
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
                            <button type="button" class="lb-btn add-item-btn"
                                    style="background:rgba(0,102,102,0.08);color:#006666;">
                                <i class="bx bx-plus"></i> Add Item
                            </button>
                        </div>
                        <div class="ml-card-body p-0">
                            <div class="table-responsive">
                                <table class="mp-items-table" id="items-table">
                                    <thead>
                                        <tr>
                                            <th style="min-width:200px;">Item</th>
                                            <th style="min-width:220px;">Description</th>
                                            <th style="width:80px;" class="text-center">Qty</th>
                                            <th style="width:130px;" class="text-end">Rate</th>
                                            <th style="width:140px;">Tax</th>
                                            <th style="width:130px;" class="text-end">Amount</th>
                                            <th style="width:46px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(count($proposalItems) > 0)
                                            @foreach($proposalItems as $k => $proposalItem)
                                                <tr data-item-row="0">
                                                    <td><textarea class="form-control item-name"
                                                            name="proposal_items[{{ $k }}][item_name]"
                                                            placeholder="Item Name" rows="1">{{ $proposalItem->item_name ?? '' }}</textarea></td>
                                                    <td><textarea class="form-control item-description"
                                                            name="proposal_items[{{ $k }}][description]"
                                                            placeholder="Description" rows="1">{{ $proposalItem->description ?? '' }}</textarea></td>
                                                    <td><input type="number" class="form-control item-qty text-center"
                                                            name="proposal_items[{{ $k }}][quantity]"
                                                            value="{{ $proposalItem->quantity ?? '' }}" min="1"></td>
                                                    <td><input type="number" class="form-control item-rate text-end"
                                                            name="proposal_items[{{ $k }}][rate]"
                                                            placeholder="0.00"
                                                            value="{{ $proposalItem->rate ?? '' }}"></td>
                                                    <td>
                                                        <select class="form-select item-tax" multiple
                                                            name="proposal_items[{{ $k }}][tax_percentage][]" title="No Tax">
                                                            @foreach($taxes as $index => $tax)
                                                                @php $calTax = ($tax ?? 0) / 100; @endphp
                                                                @if($index == 0)
                                                                    <option value="{{ $index . ':' . $calTax }}"
                                                                        @if(($proposalItem->cgst_percent ?? '') == ($calTax ?? 0)) selected @endif>
                                                                        CGST {{ $tax ?? 0 }} %</option>
                                                                @elseif($index == 1)
                                                                    <option value="{{ $index . ':' . $calTax }}"
                                                                        @if(($proposalItem->sgst_percent ?? '') == ($calTax ?? 0)) selected @endif>
                                                                        SGST {{ $tax ?? 0 }} %</option>
                                                                @elseif($index == 2)
                                                                    <option value="{{ $index . ':' . $calTax }}"
                                                                        @if(($proposalItem->igst_percent ?? '') == ($calTax ?? 0)) selected @endif>
                                                                        IGST {{ $tax ?? 0 }} %</option>
                                                                @elseif($index == 3)
                                                                    <option value="{{ $index . ':' . $calTax }}"
                                                                        @if(($proposalItem->vat_percent ?? '') == ($calTax ?? 0)) selected @endif>
                                                                        VAT {{ $tax ?? 0 }} %</option>
                                                                @else
                                                                    <option value="{{ $index . ':' . $calTax }}">{{ $tax ?? 0 }} %</option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="item-amount text-end fw-bold">
                                                        ₹{{ ($proposalItem->rate ?? 0) * ($proposalItem->quantity ?? 0) }}</td>
                                                    <td class="text-center">
                                                        <button type="button" class="kb-action-btn kb-action-del remove-item-btn"
                                                                style="width:30px;height:30px;" title="Remove">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr data-item-row="0">
                                                <td><textarea class="form-control item-name"
                                                        name="proposal_items[0][item_name]"
                                                        placeholder="Item Name" rows="1" required></textarea></td>
                                                <td><textarea class="form-control item-description"
                                                        name="proposal_items[0][description]"
                                                        placeholder="Description" rows="1"></textarea></td>
                                                <td><input type="number" class="form-control item-qty text-center"
                                                        name="proposal_items[0][quantity]"
                                                        value="1" min="1"></td>
                                                <td><input type="number" class="form-control item-rate text-end"
                                                        name="proposal_items[0][rate]"
                                                        placeholder="0.00" required></td>
                                                <td>
                                                    <select class="form-select item-tax" multiple
                                                        name="proposal_items[0][tax_percentage][]" title="No Tax">
                                                        @foreach($taxes as $index => $tax)
                                                            @php $calTax = ($tax ?? 0) / 100; @endphp
                                                            <option value="{{ $index . ':' . $calTax }}">{{ $tax ?? 0 }} %</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="item-amount text-end fw-bold">₹0.00</td>
                                                <td class="text-center">
                                                    <button type="button" class="kb-action-btn kb-action-del remove-item-btn"
                                                            style="width:30px;height:30px;" title="Remove">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            {{-- ── Summary Box ── --}}
                            <div class="d-flex justify-content-end p-4">
                                <div class="mp-summary-box">
                                    <div class="mp-summary-row">
                                        <span class="mp-summary-label">Sub Total</span>
                                        <span id="sub-total" class="mp-summary-val">₹{{ $proposal->sub_total ?? '0.00' }}</span>
                                        <input type="hidden" name="sub_total" id="sub-total1"
                                               value="{{ $proposal->sub_total ?? 0.00 }}">
                                    </div>

                                    <div class="mp-summary-row">
                                        <span class="mp-summary-label">
                                            Discount (<span id="discount-type-display" class="fst-italic">None</span>)
                                        </span>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="number" class="form-control form-control-sm text-end mp-disc-input"
                                                   name="discount_percentage" id="discountValue"
                                                   value="{{ $proposal->discount_percentage ?? 0 }}"
                                                   placeholder="%" step="0.01" min="0">
                                            <span id="discount-total" class="mp-summary-val text-danger">
                                                ₹{{ $proposal->discount_amount_calculated ?? '0.00' }}
                                            </span>
                                            <input type="hidden" name="discount_amount_calculated" id="discount-total1"
                                                   value="{{ $proposal->discount_amount_calculated ?? 0.00 }}">
                                        </div>
                                    </div>

                                    <div class="mp-summary-row mp-tax-row">
                                        <span class="mp-summary-label text-muted">CGST</span>
                                        <span id="cgst-total" class="mp-summary-val text-muted">₹{{ $proposal->cgst_total ?? '0.00' }}</span>
                                        <input type="hidden" name="cgst_total" id="cgst-total1" value="{{ $proposal->cgst_total ?? 0.00 }}">
                                    </div>
                                    <div class="mp-summary-row mp-tax-row">
                                        <span class="mp-summary-label text-muted">SGST</span>
                                        <span id="sgst-total" class="mp-summary-val text-muted">₹{{ $proposal->sgst_total ?? '0.00' }}</span>
                                        <input type="hidden" name="sgst_total" id="sgst-total1" value="{{ $proposal->sgst_total ?? 0.00 }}">
                                    </div>
                                    <div class="mp-summary-row mp-tax-row">
                                        <span class="mp-summary-label text-muted">IGST</span>
                                        <span id="igst-total" class="mp-summary-val text-muted">₹{{ $proposal->igst_total ?? '0.00' }}</span>
                                        <input type="hidden" name="igst_total" id="igst-total1" value="{{ $proposal->igst_total ?? 0.00 }}">
                                    </div>
                                    <div class="mp-summary-row mp-tax-row">
                                        <span class="mp-summary-label text-muted">VAT</span>
                                        <span id="vat-total" class="mp-summary-val text-muted">₹{{ $proposal->vat_total ?? '0.00' }}</span>
                                        <input type="hidden" name="vat_total" id="vat-total1" value="{{ $proposal->vat_total ?? 0.00 }}">
                                    </div>

                                    <div class="mp-summary-row">
                                        <span class="mp-summary-label">Adjustment</span>
                                        <input type="number" class="form-control form-control-sm text-end mp-disc-input"
                                               name="adjustment_amount" id="adjustment"
                                               value="{{ $proposal->adjustment_amount ?? 0 }}" step="0.01">
                                    </div>

                                    <div class="mp-summary-total">
                                        <span>Grand Total</span>
                                        <strong id="total" class="mp-grand-total-val">
                                            ₹{{ $proposal->grand_total ?? '0.00' }}
                                        </strong>
                                        <input type="hidden" name="grand_total" id="total1"
                                               value="{{ $proposal->grand_total ?? 0.00 }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>{{-- /col-lg-12 --}}
            </div>{{-- /row --}}
        </form>

    </div>
</section>

{{-- ── All original JS preserved exactly ── --}}
<script>
    const leadList = document.getElementById('relatedList');

    leadList.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const name    = selectedOption.getAttribute('data-name');
        const email   = selectedOption.getAttribute('data-email');
        const phone   = selectedOption.getAttribute('data-mob');
        const address = selectedOption.getAttribute('data-address');
        const city    = selectedOption.getAttribute('data-city');
        const state   = selectedOption.getAttribute('data-state');
        const zip     = selectedOption.getAttribute('data-zip');
        const country = selectedOption.getAttribute('data-country');

        document.getElementById('clientName').value    = name    || '';
        document.getElementById('clientEmail').value   = email   || '';
        document.getElementById('clientPhone').value   = phone   || '';
        document.getElementById('clientAddress').value = address || '';
        document.getElementById('clientCity').value    = city    || '';
        document.getElementById('clientState').value   = state   || '';
        document.getElementById('clientZip').value     = zip     || '';
        document.getElementById('clientCountry').value = country || '';
    });

    function updateRelatedList(relatedValue) {
        const $related = $('#relatedList').empty()
                                         .append(`<option value="">Select…</option>`);
        const map = {
            '1': { text: 'Leads List',   url: '/leads-list',   key: 'leads'   },
            '2': { text: 'Clients List', url: '/clients-list', key: 'clients' }
        };
        const cfg = map[relatedValue];
        if (!cfg) { console.warn('Invalid relatedValue'); return; }

        $('#proposalType').text(cfg.text);

        $.get(cfg.url)
         .done(resp => {
             let items;
             try {
                 items = JSON.parse(resp)[cfg.key] ?? [];
                 if (!Array.isArray(items)) throw new Error('Bad format');
             } catch (e) {
                 console.error(e);
                 $related.append(`<option value="">Error loading data</option>`);
                 return;
             }
             items.forEach(item => {
                 const loc = item.location ? JSON.parse(item.location) : [];
                 $('<option>', {
                     value: item.id, text: item.name,
                     'data-name': item.name, 'data-company': item.company,
                     'data-email': item.email, 'data-mob': item.mob,
                     'data-address': loc[0] || '', 'data-city': loc[1] || '',
                     'data-state': loc[2] || '', 'data-country': loc[3] || '',
                     'data-zip': loc[4] || ''
                 }).appendTo($related);
             });
             $related.selectpicker('refresh');
         })
         .fail((xhr, status, err) => {
             console.error(err);
             $related.append(`<option value="">Error loading data</option>`);
             $related.selectpicker('refresh');
         });
    }

    document.getElementById('related').addEventListener('change', function () {
        updateRelatedList(this.value);
    });

    window.onload = function () {
        updateRelatedList(document.getElementById('related').value);
    };

    document.addEventListener('DOMContentLoaded', function () {
        const itemsTableBody  = document.getElementById('items-table').querySelector('tbody');
        const addItemBtn      = document.querySelector('.add-item-btn');
        const currencySelect  = document.getElementById('currency');
        const adjustmentInput = document.getElementById('adjustment');
        const discountTypeSelect  = document.getElementById('discountType');
        const discountValueInput  = document.getElementById('discountValue');
        const discountTypeDisplay = document.getElementById('discount-type-display');
        const discountTotalDisplay  = document.getElementById('discount-total');
        const discountTotalDisplay1 = document.getElementById('discount-total1');

        function formatCurrency(amount, currencyCode = 'INR') {
            let options = { style: 'currency', currency: currencyCode };
            try {
                const locale = currencyCode === 'INR' ? 'en-IN' : undefined;
                return new Intl.NumberFormat(locale, options).format(amount);
            } catch (e) {
                const symbols = { INR: '₹', USD: '$', EUR: '€', GBP: '£' };
                return (symbols[currencyCode] || '') + amount.toFixed(2);
            }
        }

        function calculateTotals() {
            const currencyCode = currencySelect.value;
            const adjustment   = parseFloat(adjustmentInput.value) || 0;
            const discType     = discountTypeSelect.value;
            const discPct      = parseFloat(discountValueInput.value) || 0;

            let subTotal = 0, cgstTotal = 0, sgstTotal = 0, igstTotal = 0, vatTotal = 0;

            itemsTableBody.querySelectorAll('tr').forEach(row => {
                const qty  = parseFloat(row.querySelector('.item-qty').value)  || 0;
                const rate = parseFloat(row.querySelector('.item-rate').value) || 0;
                const line = qty * rate;
                subTotal += line;

                Array.from(row.querySelector('.item-tax').selectedOptions).forEach(opt => {
                    const [idx, pctStr] = opt.value.split(':');
                    const pct = parseFloat(pctStr) || 0;
                    const tax = line * pct;
                    switch (+idx) {
                        case 0: cgstTotal += tax; break;
                        case 1: sgstTotal += tax; break;
                        case 2: igstTotal += tax; break;
                        case 3: vatTotal  += tax; break;
                    }
                });

                row.querySelector('.item-amount').textContent = formatCurrency(line, currencyCode);
            });

            const taxTotal    = cgstTotal + sgstTotal + igstTotal + vatTotal;
            let   discountAmt = 0;
            if (discPct > 0) {
                const base = discType === 'before-tax' ? subTotal : subTotal + taxTotal;
                discountAmt = base * discPct / 100;
            }
            const grandTotal = subTotal + taxTotal - discountAmt + adjustment;

            document.getElementById('sub-total').textContent   = formatCurrency(subTotal, currencyCode);
            document.getElementById('sub-total1').value        = subTotal.toFixed(2);
            discountTypeDisplay.textContent                    = discountTypeSelect.selectedOptions[0].text;
            discountTotalDisplay.textContent                   = formatCurrency(discountAmt, currencyCode);
            discountTotalDisplay1.value                        = discountAmt.toFixed(2);
            document.getElementById('cgst-total').textContent  = formatCurrency(cgstTotal, currencyCode);
            document.getElementById('cgst-total1').value       = cgstTotal.toFixed(2);
            document.getElementById('sgst-total').textContent  = formatCurrency(sgstTotal, currencyCode);
            document.getElementById('sgst-total1').value       = sgstTotal.toFixed(2);
            document.getElementById('igst-total').textContent  = formatCurrency(igstTotal, currencyCode);
            document.getElementById('igst-total1').value       = igstTotal.toFixed(2);
            document.getElementById('vat-total').textContent   = formatCurrency(vatTotal, currencyCode);
            document.getElementById('vat-total1').value        = vatTotal.toFixed(2);
            document.getElementById('total').textContent       = formatCurrency(grandTotal, currencyCode);
            document.getElementById('total1').value            = grandTotal.toFixed(2);
        }

        addItemBtn.addEventListener('click', function () {
            const lastRow = itemsTableBody.querySelector('tr:last-child');
            if (!lastRow) return;
            const newRow      = lastRow.cloneNode(true);
            const newRowIndex = itemsTableBody.querySelectorAll('tr').length;
            newRow.querySelectorAll('input, textarea').forEach(input => {
                if (input.classList.contains('item-qty'))  { input.value = 1; }
                else if (input.classList.contains('item-rate')) { input.value = ''; }
                else if (!input.classList.contains('item-name') && !input.classList.contains('item-description')) { input.value = ''; }
            });
            newRow.querySelector('.item-tax').value         = '0';
            newRow.querySelector('.item-amount').textContent = formatCurrency(0, currencySelect.value);
            newRow.querySelector('.item-name').name        = `proposal_items[${newRowIndex}][item_name]`;
            newRow.querySelector('.item-description').name = `proposal_items[${newRowIndex}][description]`;
            newRow.querySelector('.item-qty').name         = `proposal_items[${newRowIndex}][quantity]`;
            newRow.querySelector('.item-rate').name        = `proposal_items[${newRowIndex}][rate]`;
            newRow.querySelector('.item-tax').name         = `proposal_items[${newRowIndex}][tax_percentage][]`;
            newRow.querySelector('.item-name').value = '';
            newRow.querySelector('.item-description').value = '';
            newRow.querySelector('.item-qty').value = '';
            newRow.querySelector('.item-rate').value = '';
            itemsTableBody.appendChild(newRow);
            calculateTotals();
        });

        itemsTableBody.addEventListener('click', function (event) {
            if (event.target.closest('.remove-item-btn')) {
                if (itemsTableBody.querySelectorAll('tr').length > 1) {
                    event.target.closest('tr').remove();
                    calculateTotals();
                } else {
                    alert("You must have at least one item.");
                }
            }
        });

        itemsTableBody.addEventListener('input', function (event) {
            const target = event.target;
            if (target.classList.contains('item-qty') || target.classList.contains('item-rate')) {
                calculateTotals();
            }
        });

        itemsTableBody.addEventListener('change', function (event) {
            if (event.target.classList.contains('item-tax')) { calculateTotals(); }
        });

        currencySelect.addEventListener('change', calculateTotals);
        adjustmentInput.addEventListener('input', calculateTotals);
        discountTypeSelect.addEventListener('change', calculateTotals);
        discountValueInput.addEventListener('input', calculateTotals);

        calculateTotals();
    });
</script>
<script>
    tinymce.init({
        selector: '#editor',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
    });
</script>
@endsection