@extends('layout')
@section('title','Manage Proposal - eseCRM')

@section('content')
<style>
    /*.editor-toolbar button,
    .editor-toolbar select,
    .editor-toolbar input[type="color"] {
        background: #f8f9fa;
        border: 1px solid #ccc;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 14px;
        cursor: pointer;
    }

    .editor-toolbar button:hover,
    .editor-toolbar select:hover {
        background: #e2e6ea;
    }

    #customEditor:focus {
        outline: none;
        border-color: #80bdff;
        box-shadow: 0 0 5px rgba(0,123,255,.25);
    }
    
    .table-responsive table td {
        min-width: 160px;
    }
    .dropdown.bootstrap-select.form-select
    {
        padding: 0px !important;
    }.bootstrap-select .dropdown-menu {
        min-width: 100px;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
        max-width: 100%;
    }

    @media (max-width: 767px) {
        .editor-toolbar {
            display: flex;
            align-items: flex-start;
        }
        .table{
            border-radius: 5px;
            overflow: hidden !important;
        }.form-control{
            padding: 6px;
        }
    }*/
</style>

<section class="task__section">
    <div class="text">
        <i class="bx bx-menu" id="mbtn"></i> 
        Manage Proposal
        <a href="/signout" class="logoutbtn"><i class='bx bx-log-out'></i></a>
    </div>

    <div class="container-fluid">
        <div class="board-title board-title-flex mb-4">
            <a href="/proposals" class="btn btn-primary btn-sm back-btn"><i class="bx bx-arrow-back"></i></a>
            @if(!empty($proposal->id))
                <h1>Edit Proposal #{{ $proposal->id ?? '' }}</h1>
            @else
                <h1>Create New Proposal</h1>
            @endif
        </div>

        <div class="row">
            <div class="col-md-12 csp-3">
                <form id="proposalForm" action="/manage-proposal" method="post" class="row g-3 bg-white p-3 mb-2 rounded">
                    @csrf
                    <!-- Proposal Information -->
                    <div class="col-md-4 form-group">
                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-rename'></i></span>
                            <input type="hidden" name="id" id="id" value="{{ $proposal->id ?? '' }}">
                            <input type="text" name="subject" id="subject" class="form-control" placeholder="Enter Subject" value="{{ $proposal->subject ?? '' }}" required>
                        </div>
                        <div class="form-text">e.g. Website Redesign Proposal</div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="Related" class="form-label">Related</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-info-circle'></i></span>
                            <select name="related" id="related" class="form-control" required>
                                <option value="1" @if(($proposal->related ?? '') == '1') selected @endif>Lead</option>
                                <option value="2" @if(($proposal->related ?? '') == '2') selected @endif>Client</option>
                            </select>
                        </div>
                        <div class="form-text">Optional: Select Lead or Client</div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="relatedList" class="form-label" id="proposalType">Leads List</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-list-ul'></i></span>
                            @if(($proposal->related ?? '') == '2')
                            <select name="lead_id" id="relatedList" class="form-control"><!--class="selectpicker form-select" data-live-search="true"-->
                                <!-- Dynamic options will be populated here -->
                                <option>Select...</option>
                                @foreach($clients as $lead)
                                @php $location = json_decode(($lead->location ?? ''),true) @endphp
                                <option value="{{ $lead->id ?? '' }}" data-name="{{ $lead->name ?? '' }}" data-company="{{ $lead->company ?? '' }}" data-email="{{ $lead->email ?? '' }}" data-mob="{{ $lead->mob ?? '' }}" data-address="{{ $location[0] ?? '' }}" data-city="{{ $location[1] ?? '' }}" data-state="{{ $location[2] ?? '' }}" data-country="{{ $location[3] ?? '' }}" data-zip="{{ $location[4] ?? '' }}" @if(($proposal->lead_id ?? '') == ($lead->id ?? '')) selected @endif>{{ $lead->name ?? '' }}</option>
                                @endforeach
                            </select>
                            @else
                            <select name="lead_id" id="relatedList" class="selectpicker form-select" data-live-search="true"><!--class="form-control"-->
                                <!-- Dynamic options will be populated here -->
                                <option>Select...</option>
                                @foreach($leads as $lead)
                                @php $location = json_decode(($lead->location ?? ''),true) @endphp
                                <option value="{{ $lead->id ?? '' }}" data-name="{{ $lead->name ?? '' }}" data-company="{{ $lead->company ?? '' }}" data-email="{{ $lead->email ?? '' }}" data-mob="{{ $lead->mob ?? '' }}" data-address="{{ $location[0] ?? '' }}" data-city="{{ $location[1] ?? '' }}" data-state="{{ $location[2] ?? '' }}" data-country="{{ $location[3] ?? '' }}" data-zip="{{ $location[4] ?? '' }}" @if(($proposal->lead_id ?? '') == ($lead->id ?? '')) selected @endif>{{ $lead->name ?? '' }}</option>
                                @endforeach
                            </select>
                            @endif
                        </div>
                        <div class="form-text">Select from the list based on the selected related entity</div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="date" class="form-label">Proposal Date <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-calendar'></i></span>
                            <input type="date" name="proposal_date" id="proposalDate" class="form-control" value="{{ $proposal->proposal_date ?? date('Y-m-d') }}" required>
                        </div>
                        <div class="form-text">The date the proposal was created</div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="openTill" class="form-label">Valid Till</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-calendar-event'></i></span>
                            <input type="date" name="open_till" id="openTill" class="form-control" 
                                value="{{ $proposal->open_till ?? \Carbon\Carbon::now()->addDays(7)->format('Y-m-d') }}">
                        </div>
                        <div class="form-text">Date till which the proposal is valid</div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-money'></i></span>
                            <select name="currency" id="currency" class="form-control" required>
                                <option value="INR" @if(($proposal->currency ?? '') == 'INR') selected @endif>₹ INR</option>
                                <option value="USD" @if(($proposal->currency ?? '') == 'USD') selected @endif>$ USD</option>
                                <option value="EUR" @if(($proposal->currency ?? '') == 'EUR') selected @endif>€ EUR</option>
                                <option value="GBP" @if(($proposal->currency ?? '') == 'GBP') selected @endif>£ GBP</option>
                            </select>
                        </div>
                        <div class="form-text">Currency for this proposal</div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="discountType" class="form-label">Discount Type</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-purchase-tag'></i></span>
                            <select name="discount_type" id="discountType" class="form-control">
                                <option value="none" @if(($proposal->open_till ?? '') == 'none') selected @endif>No discount</option>
                                <option value="before-tax" @if(($proposal->discount_type ?? '') == 'before-tax') selected @endif>Before Tax</option>
                                <option value="after-tax" @if(($proposal->discount_type ?? '') == 'after-tax') selected @endif>After Tax</option>
                            </select>
                        </div>
                        <div class="form-text">Type of Discount to apply</div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="tags" class="form-label">Tags</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-tag'></i></span>
                            <input type="text" name="tags" id="tags" class="form-control" placeholder="Enter Tags (comma separated)" value="{{ $proposal->tags ?? '' }}">
                        </div>
                        <div class="form-text">e.g. urgent, website, design</div>
                    </div>
                    <div class="col-12 form-group">
                        <label for="notes" class="form-label">Proposal Notes</label>
                        <textarea name="notes" id="editor" class="form-control " rows="2" placeholder="Add proposal notes here...">{{ $proposal->notes ?? '' }}</textarea>
                    </div>
                    
                    <!-- Client Details Section -->
                    <div class="col-12 text-left pt-3">
                        <h4 class="h5 font-weight-bold divider mb-0">Client Details</h4>
                        <span class="div-line mb-0"></span>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="clientName" class="form-label">Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-user'></i></span>
                            <input type="text" name="client_name" id="clientName" class="form-control" placeholder="Enter Client Name" value="{{ $proposal->client_name ?? '' }}" required>
                        </div>
                        <div class="form-text">Client's Name</div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="clientEmail" class="form-label">Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-envelope-open'></i></span>
                            <input type="email" name="client_email" id="clientEmail" class="form-control" placeholder="Enter Client Email" value="{{ $proposal->client_email ?? '' }}" required>
                        </div>
                        <div class="form-text">Client's Email Address</div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="clientPhone" class="form-label">Phone</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-phone'></i></span>
                            <input type="tel" name="client_phone" id="clientPhone" class="form-control" placeholder="Enter Client Phone" value="{{ $proposal->client_phone ?? '91' }}">
                        </div>
                        <div class="form-text">Client's Contact Number</div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="clientAddress" class="form-label">Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-home'></i></span>
                            <input type="text" name="client_address" id="clientAddress" class="form-control" placeholder="Enter Client Address" value="{{ $proposal->client_address ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="clientCity" class="form-label">City</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-map'></i></span>
                            <input type="text" name="client_city" id="clientCity" class="form-control" placeholder="Enter Client City" value="{{ $proposal->client_city ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="clientState" class="form-label">State/Province</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-map-pin'></i></span>
                            <input type="text" name="client_state" id="clientState" class="form-control" placeholder="Enter State/Province" value="{{ $proposal->client_state ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="clientZip" class="form-label">Zip/Postal Code</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-pin'></i></span>
                            <input type="text" name="client_zip" id="clientZip" class="form-control" placeholder="Enter Zip/Postal Code" value="{{ $proposal->client_zip ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="clientCountry" class="form-label">Country</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-pin'></i></span>
                            <input type="text" name="client_country" id="clientCountry" class="form-control" placeholder="Enter Country" value="{{ $proposal->client_country ?? '' }}">
                        </div>
                    </div>
                
                    <!-- Items Section -->
                    <div class="col-12 text-left pt-3">
                        <h4 class="h5 font-weight-bold divider mb-0">Items</h4>
                        <span class="div-line mb-0"></span>
                    </div>
                    <div class="col-md-12 mt-0 table-responsive">
                        <table class="table table-bordered text-center mb-0" id="items-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Description</th>
                                    <th>Qty</th>
                                    <th>Rate</th>
                                    <th>Tax</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $taxes = !empty($companies->tax) ? explode(',', $companies->tax) : [];
                                @endphp
                                @if(count($proposalItems)>0)
                                    @foreach($proposalItems as $k=>$proposalItem)
                                    <tr data-item-row="0">
                                        <td><textarea type="text" class="form-control item-name" name="proposal_items[{{ $k }}][item_name]" placeholder="Item Name">{{ $proposalItem->item_name ?? '' }}</textarea></td>
                                        <td><textarea type="text" class="form-control item-description" name="proposal_items[{{ $k }}][description]" placeholder="Description">{{ $proposalItem->description ?? '' }}</textarea></td>
                                        <td><input type="number" class="form-control item-qty" name="proposal_items[{{ $k }}][quantity]" value="{{ $proposalItem->quantity ?? '' }}" min="1"></td>
                                        <td><input type="number" class="form-control item-rate" name="proposal_items[{{ $k }}][rate]" placeholder="Rate" value="{{ $proposalItem->rate ?? '' }}"></td>
                                        <td><!-- selectpicker form-select-->
                                            <select class="form-control item-tax" multiple data-live-search="true" name="proposal_items[{{ $k }}][tax_percentage][]" title="No Tax">
                                                <!--<option value="0" @if(($proposalItem->tax_percentage ?? '') == '0') selected @endif>No Tax</option>-->
                                                @foreach($taxes as $index => $tax)
                                                @php $calTax = ($tax ?? 0)/100; @endphp
                                                @if($index == 0)
                                                <option value="{{ $index.":".$calTax }}" @if(($proposalItem->cgst_percent ?? '') == ($calTax ?? 0)) selected @endif>CGST {{ $tax ?? 0 }} %</option>
                                                @elseif($index == 1)
                                                <option value="{{ $index.":".$calTax }}" @if(($proposalItem->sgst_percent ?? '') == ($calTax ?? 0)) selected @endif>SGST {{ $tax ?? 0 }} %</option>
                                                @elseif($index == 2)
                                                <option value="{{ $index.":".$calTax }}" @if(($proposalItem->igst_percent ?? '') == ($calTax ?? 0)) selected @endif>IGST {{ $tax ?? 0 }} %</option>
                                                @elseif($index == 3)
                                                <option value="{{ $index.":".$calTax }}" @if(($proposalItem->vat_percent ?? '') == ($calTax ?? 0)) selected @endif>VAT{{ $tax ?? 0 }} %</option>
                                                @else
                                                <option value="{{ $index.":".$calTax }}">{{ $tax ?? 0 }} %</option>
                                                @endif
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="item-amount">₹{!! ($proposalItem->rate ?? 0)*($proposalItem->quantity ?? 0) !!}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger remove-item-btn"><i class='bx bx-trash'></i></button>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr data-item-row="0">
                                        <td><input type="text" class="form-control item-name" name="proposal_items[0][item_name]" placeholder="Item Name" required></td>
                                        <td><input type="text" class="form-control item-description" name="proposal_items[0][description]" placeholder="Description"></td>
                                        <td><input type="number" class="form-control item-qty" name="proposal_items[0][quantity]" value="1" min="1"></td>
                                        <td><input type="number" class="form-control item-rate" name="proposal_items[0][rate]" placeholder="Rate" required></td>
                                        <td><!-- selectpicker form-select-->
                                            <select class="form-control item-tax" multiple data-live-search="true" name="proposal_items[0][tax_percentage][]" title="No Tax">
                                                <!--<option value="0">No Tax</option>-->
                                                @foreach($taxes as $index => $tax)
                                                @php $calTax = ($tax ?? 0)/100; @endphp
                                                @if($index == 0)
                                                <option value="{{ $index.":".$calTax }}" @if(($proposalItem->cgst_percent ?? '') == ($calTax ?? 0)) selected @endif>{{ $tax ?? 0 }} %</option>
                                                @elseif($index == 1)
                                                <option value="{{ $index.":".$calTax }}" @if(($proposalItem->sgst_percent ?? '') == ($calTax ?? 0)) selected @endif>{{ $tax ?? 0 }} %</option>
                                                @elseif($index == 2)
                                                <option value="{{ $index.":".$calTax }}" @if(($proposalItem->igst_percent ?? '') == ($calTax ?? 0)) selected @endif>{{ $tax ?? 0 }} %</option>
                                                @elseif($index == 3)
                                                <option value="{{ $index.":".$calTax }}" @if(($proposalItem->vat_percent ?? '') == ($calTax ?? 0)) selected @endif>{{ $tax ?? 0 }} %</option>
                                                @else
                                                <option value="{{ $index.":".$calTax }}">{{ $tax ?? 0 }} %</option>
                                                @endif
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="item-amount">₹0.00</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger remove-item-btn"><i class='bx bx-trash'></i></button>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                        <button type="button" class="btn add-item-btn btn-sm">+ Add Item</button>
                    </div>
                    
                    <!-- Summary Section -->
                    <div class="col-md-5"></div>
                    <div class="col-md-7">
                        <div class="summary-box mb-2 d-flex justify-content-between align-items-center"> <!-- Use align-items-center -->
                            <span>Sub Total:</span>
                            <span id="sub-total">₹{{ $proposal->sub_total ?? 0.00 }}</span>
                            <input type="hidden" name="sub_total" id="sub-total1" value="{{ $proposal->sub_total ?? 0.00 }}">
                        </div>
                    
                        <!-- ****** MODIFIED DISCOUNT ROW ****** -->
                        <div class="summary-box mb-2 d-flex justify-content-between align-items-center">
                            <span>Discount (<span id="discount-type-display">None</span>):</span>
                            <div class="input-group input-group-sm w-50"> <!-- Wrap input and display -->
                                 <input type="number" class="form-control form-control-sm text-end" name="discount_percentage" id="discountValue"  value="{{ $proposal->discount_percentage ?? 0 }}" placeholder="Enter %" step="0.01" min="0">
                                 <span class="input-group-text" id="discount-total">₹{{ $proposal->discount_amount_calculated ?? 0.00 }}</span> <!-- Display calculated amount -->
                                 <input type="hidden" name="discount_amount_calculated" id="discount-total1" value="{{ $proposal->discount_amount_calculated ?? 0.00 }}">
                            </div>
                        </div>
                        <!-- *********************************** -->
                    
                         <div class="summary-box mb-2 d-flex justify-content-between align-items-center">
                            <span>Sub Total (CGST):</span>
                            <span id="cgst-total">₹{{ $proposal->cgst_total ?? 0.00 }}</span>
                            <input type="hidden" name="cgst_total" id="cgst-total1" value="{{ $proposal->cgst_total ?? 0.00 }}">
                         </div>
                         <div class="summary-box mb-2 d-flex justify-content-between align-items-center">
                            <span>Sub Total (SGST):</span>
                            <span id="sgst-total">₹{{ $proposal->sgst_total ?? 0.00 }}</span>
                            <input type="hidden" name="sgst_total" id="sgst-total1" value="{{ $proposal->sgst_total ?? 0.00 }}">
                         </div>
                         <div class="summary-box mb-2 d-flex justify-content-between align-items-center">
                            <span>Sub Total (IGST):</span>
                            <span id="igst-total">₹{{ $proposal->igst_total ?? 0.00 }}</span>
                            <input type="hidden" name="igst_total" id="igst-total1" value="{{ $proposal->igst_total ?? 0.00 }}">
                         </div>
                         <div class="summary-box mb-2 d-flex justify-content-between align-items-center">
                            <span>Sub Total (VAT):</span>
                            <span id="vat-total">₹{{ $proposal->vat_total ?? 0.00 }}</span>
                            <input type="hidden" name="vat_total" id="vat-total1" value="{{ $proposal->vat_total ?? 0.00 }}">
                         </div>
                         
                        <div class="summary-box mb-2 d-flex justify-content-between align-items-center">
                            <span>Adjustment:</span>
                             <!-- Added align-items-center to parent, adjusted width if needed -->
                            <input type="number" class="form-control form-control-sm text-end w-50" name="adjustment_amount" id="adjustment"  value="{{ $proposal->adjustment_amount ?? 0 }}" step="0.01">
                        </div>
                        <hr/> <!-- Optional: Add a visual separator -->
                        <div class="summary-box d-flex justify-content-between">
                            <strong>Total:</strong>
                            <strong id="total">₹0.00</strong>
                            <input type="hidden" name="grand_total" id="total1" value="{{ $proposal->grand_total ?? 0.00 }}">
                        </div>
                    </div>
                
                    <!-- Actions Section -->
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-success text-white bg-success border me-2">Save</button>
                        <input type="submit" class="btn btn-primary" name="submit" value="Save & Send">
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<script>
    
    const leadList = document.getElementById('relatedList');

    leadList.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];

        // Read all the data attributes from the selected <option>
        const name = selectedOption.getAttribute('data-name');
        const email = selectedOption.getAttribute('data-email');
        const phone = selectedOption.getAttribute('data-mob');
        const address = selectedOption.getAttribute('data-address');
        const city = selectedOption.getAttribute('data-city');
        const state = selectedOption.getAttribute('data-state');
        const zip = selectedOption.getAttribute('data-zip');
        const country = selectedOption.getAttribute('data-country');

        // Populate your form fields
        document.getElementById('clientName').value = name || '';
        document.getElementById('clientEmail').value = email || '';
        document.getElementById('clientPhone').value = phone || '';
        document.getElementById('clientAddress').value = address || '';
        document.getElementById('clientCity').value = city || '';
        document.getElementById('clientState').value = state || '';
        document.getElementById('clientZip').value = zip || '';
        document.getElementById('clientCountry').value = country || '';
    });
    
    // Function to populate the list based on selected type
    function updateRelatedList(relatedValue) {
        const $related = $('#relatedList')          // cache the jQuery object
                          .empty()                  // wipe out existing <option>s
                          .append(`<option value="">Select...</option>`);
    
        // decide which endpoint to hit
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
                      value:        item.id,
                      text:         item.name,
                      'data-name':  item.name,
                      'data-company': item.company,
                      'data-email': item.email,
                      'data-mob':    item.mob,
                      'data-address': loc[0] || '',
                      'data-city':    loc[1] || '',
                      'data-state':   loc[2] || '',
                      'data-country': loc[3] || '',
                      'data-zip':     loc[4] || ''
                  }).appendTo($related);
              });
    
              // ---- KEY LINE ----
              $related.selectpicker('refresh');   // tells bootstrap-select to rebuild #bs-select-X
          })
          .fail((xhr, status, err) => {
              console.error(err);
              $related.append(`<option value="">Error loading data</option>`);
              $related.selectpicker('refresh');
          });
    }

    // Initially load the list based on the selected "Related" value
    document.getElementById('related').addEventListener('change', function () {
        updateRelatedList(this.value);
    });

    // Trigger the function on page load
    window.onload = function() {
        updateRelatedList(document.getElementById('related').value);
    };
    
    document.addEventListener('DOMContentLoaded', function() {
        const itemsTableBody = document.getElementById('items-table').querySelector('tbody');
        const addItemBtn = document.querySelector('.add-item-btn');
        const currencySelect = document.getElementById('currency');
        const adjustmentInput = document.getElementById('adjustment');
        const discountTypeSelect = document.getElementById('discountType');
        const discountValueInput = document.getElementById('discountValue');
        const discountTypeDisplay = document.getElementById('discount-type-display');
        const discountTotalDisplay = document.getElementById('discount-total');
        const discountTotalDisplay1 = document.getElementById('discount-total1');
    
        function formatCurrency(amount, currencyCode = 'INR') {
            let options = { style: 'currency', currency: currencyCode };
            try {
                const locale = currencyCode === 'INR' ? 'en-IN' : undefined;
                return new Intl.NumberFormat(locale, options).format(amount);
            } catch (e) {
                console.error("Currency formatting error:", e);
                const symbols = { INR: '₹', USD: '$', EUR: '€', GBP: '£' };
                return (symbols[currencyCode] || '') + amount.toFixed(2);
            }
        }
    
        function updateRowAmount(row) {
            const qtyInput = row.querySelector('.item-qty');
            const rateInput = row.querySelector('.item-rate');
            const amountCell = row.querySelector('.item-amount');
            const currencyCode = currencySelect.value;
    
            const qty = parseFloat(qtyInput.value) || 0;
            const rate = parseFloat(rateInput.value) || 0;
            const amount = qty * rate;
    
            amountCell.textContent = formatCurrency(amount, currencyCode);
            return amount;
        }
    
        function calculateTotals () {
            const currencyCode = currencySelect.value;
            const adjustment   = parseFloat(adjustmentInput.value)   || 0;
            const discType     = discountTypeSelect.value;           // none | before-tax | after-tax
            const discPct      = parseFloat(discountValueInput.value) || 0;
        
            let subTotal = 0,
                cgstTotal = 0,
                sgstTotal = 0,
                igstTotal = 0,
                vatTotal  = 0;
        
            // ── line‑by‑line ───────────────────────────────────────────
            itemsTableBody.querySelectorAll('tr').forEach(row => {
                const qty   = parseFloat(row.querySelector('.item-qty').value)  || 0;
                const rate  = parseFloat(row.querySelector('.item-rate').value) || 0;
                const line  = qty * rate;
                subTotal   += line;
        
                // every selected tax option, e.g. "1:0.09"
                Array.from(row.querySelector('.item-tax').selectedOptions).forEach(opt => {
                    const [idx, pctStr] = opt.value.split(':');
                    const pct = parseFloat(pctStr) || 0;
                    const tax = line * pct;
        
                    switch (+idx) {
                        case 0: cgstTotal += tax; break;
                        case 1: sgstTotal += tax; break;
                        case 2: igstTotal += tax; break;
                        case 3: vatTotal  += tax; break;
                        default:            /* ignore extras */  break;
                    }
                });
        
                // keep row amount cell in sync
                row.querySelector('.item-amount').textContent = formatCurrency(line, currencyCode);
            });
        
            const taxTotal = cgstTotal + sgstTotal + igstTotal + vatTotal;
        
            // ── discount ───────────────────────────────────────────────
            let discountAmt = 0;
            if (discPct > 0) {
                const base = discType === 'before-tax' ? subTotal : subTotal + taxTotal;
                discountAmt = base * discPct / 100;
            }
        
            const grandTotal = subTotal + taxTotal - discountAmt + adjustment;
        
            // ── write back (currency visible, plain numbers hidden) ───
            document.getElementById('sub-total').textContent  = formatCurrency(subTotal, currencyCode);
            document.getElementById('sub-total1').value       = subTotal.toFixed(2);
        
            discountTypeDisplay.textContent                   = discountTypeSelect.selectedOptions[0].text;
            discountTotalDisplay.textContent                  = formatCurrency(discountAmt, currencyCode);
            discountTotalDisplay1.value                       = discountAmt.toFixed(2);
        
            document.getElementById('cgst-total').textContent = formatCurrency(cgstTotal, currencyCode);
            document.getElementById('cgst-total1').value      = cgstTotal.toFixed(2);
            document.getElementById('sgst-total').textContent = formatCurrency(sgstTotal, currencyCode);
            document.getElementById('sgst-total1').value      = sgstTotal.toFixed(2);
            document.getElementById('igst-total').textContent = formatCurrency(igstTotal, currencyCode);
            document.getElementById('igst-total1').value      = igstTotal.toFixed(2);
            document.getElementById('vat-total').textContent  = formatCurrency(vatTotal,  currencyCode);
            document.getElementById('vat-total1').value       = vatTotal.toFixed(2);
        
            document.getElementById('total').textContent      = formatCurrency(grandTotal, currencyCode);
            document.getElementById('total1').value           = grandTotal.toFixed(2);
        }
    
        addItemBtn.addEventListener('click', function() {
            const lastRow = itemsTableBody.querySelector('tr:last-child');
            if (!lastRow) return;
    
            const newRow = lastRow.cloneNode(true);
            const newRowIndex = itemsTableBody.querySelectorAll('tr').length; // Get number of rows for new index
            newRow.querySelectorAll('input[type="text"], input[type="number"], textarea').forEach(input => {
                if (input.classList.contains('item-qty')) {
                    input.value = 1;
                } else if (input.classList.contains('item-rate')) {
                    input.value = '0.00';
                } else if (!input.classList.contains('item-name') && !input.classList.contains('item-description')) {
                    input.value = '';
                }
            });
    
            newRow.querySelector('.item-tax').value = '0';
            newRow.querySelector('.item-amount').textContent = formatCurrency(0, currencySelect.value);
    
            // Update the name and description fields dynamically based on the row index
            const nameInput = newRow.querySelector('.item-name');
            const descInput = newRow.querySelector('.item-description');
            const qtyInput = newRow.querySelector('.item-qty');
            const rateInput = newRow.querySelector('.item-rate');
            const taxInput = newRow.querySelector('.item-tax');
            nameInput.name = `proposal_items[${newRowIndex}][item_name]`;
            descInput.name = `proposal_items[${newRowIndex}][description]`;
            qtyInput.name = `proposal_items[${newRowIndex}][quantity]`;
            rateInput.name = `proposal_items[${newRowIndex}][rate]`;
            taxInput.name = `proposal_items[${newRowIndex}][tax_percentage][]`;
            nameInput.value = ``;/*Item ${newRowIndex}*/
            descInput.value = ``;/*Description of Item ${newRowIndex}*/
            qtyInput.value = ``;/*Description of Item ${newRowIndex}*/
            rateInput.value = ``;/*Description of Item ${newRowIndex}*/
            taxInput.value = ``;/*Description of Item ${newRowIndex}*/
    
            itemsTableBody.appendChild(newRow);
            calculateTotals();
        });
    
        itemsTableBody.addEventListener('click', function(event) {
            if (event.target.closest('.remove-item-btn')) {
                if (itemsTableBody.querySelectorAll('tr').length > 1) {
                    event.target.closest('tr').remove();
                    calculateTotals();
                } else {
                    alert("You must have at least one item.");
                }
            }
        });
    
        itemsTableBody.addEventListener('input', function(event) {
            const target = event.target;
            if (target.classList.contains('item-qty') || target.classList.contains('item-rate')) {
                calculateTotals();
            }
        });
    
        itemsTableBody.addEventListener('change', function(event) {
            const target = event.target;
            if (target.classList.contains('item-tax')) {
                calculateTotals();
            }
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
