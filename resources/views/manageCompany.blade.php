@extends('layout')
@section('title','Manage Company - eseCRM')

@section('content')

@php
    // Retrieve session roles and extract permissions
    $sessionroles = session('roles');
    $roleArray    = explode(',', ($sessionroles->permissions ?? ''));

    /* -----------------------------------------------
       Build rate array so edit‑mode keeps old values
       Index 0‑3  →  CGST, SGST, IGST, VAT respectively
    -----------------------------------------------*/
    $rates = !empty($company->tax) ? explode(',', $company->tax) : [];
    $rates = array_pad($rates, 4, '');
@endphp

<section class="task__section">
    <div class="text">
        <i class="bx bx-menu" id="mbtn"></i>
        @if(Request::segment(1) !== 'my-company') Manage Company @else My Company @endif
        <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
    </div>

    <div class="container-fluid">
        <div class="board-title board-title-flex mb-2">
            @if(Request::segment(1) !== 'my-company')
                <a href="companies" class="btn btn-primary btn-sm back-btn"><i class="bx bx-arrow-back"></i></a>
                @if(request()->filled('id')) <h1>Edit Company</h1> @else <h1>Add New Company</h1> @endif
            @else
                <h1>Edit Details</h1>
            @endif
        </div>

        <div class="row g-3 px-2">
            <div class="col-md-12 bg-white py-3 px-4 rounded">
                <form action="my-company" method="post" class="row" enctype="multipart/form-data">
                    @csrf

                    {{-- Company logo --}}
                    <div class="form-group col-md-3 col-sm-6">
                        <label for="img">Main Logo</label>
                        <div class="input-group">
                            @if(!empty($company->logo))
                                <span class="input-group-text" style="padding:5px;">
                                    <img src="{{ asset('public/assets/images/company/logos/'.$company->logo) }}" style="width:30px;border-radius:30px">
                                </span>
                            @else
                                <span class="input-group-text"><i class="bx bx-image"></i></span>
                            @endif
                            <input type="file" class="form-control" id="logo" name="logo">
                        </div>
                    </div>


                    {{-- Company logo --}}
                    <div class="form-group col-md-3 col-sm-6">
                        <label for="img">PDF Logo</label>
                        <div class="input-group">
                            @if(!empty($company->img))
                                <span class="input-group-text" style="padding:5px;">
                                    <img src="{{ asset('public/assets/images/company/'.$company->img) }}" style="width:30px;border-radius:30px">
                                </span>
                            @else
                                <span class="input-group-text"><i class="bx bx-image"></i></span>
                            @endif
                            <input type="file" class="form-control" id="img" name="img">
                        </div>
                    </div>

                    {{-- Basic contact --}}
                    <div class="form-group col-md-3 col-sm-6">
                        <label for="name">Company Name*</label>
                        <input type="text" class="form-control" id="name" name="name"
                               placeholder="Enter Company Name" value="{{ $company->name ?? '' }}" required>
                        <input type="hidden" name="id" value="{!! $_GET['id'] ?? '' !!}" />
                    </div>

                    <div class="form-group col-md-3 col-sm-6">
                        <label for="mob">Mobile No.</label>
                        <input type="tel" pattern="\d{10}" inputmode="numeric"
                               class="form-control" id="mob" name="mob"
                               placeholder="Enter Mobile No." value="{{ $company->mob ?? '' }}">
                    </div>

                    <div class="form-group col-md-3 col-sm-6">
                        <label for="email">Email Id</label>
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="Enter Email id" value="{{ $company->email ?? '' }}">
                    </div>

                    {{-- GST No. followed by its 3 rate boxes --}}
                    <div class="form-group col-md-3 col-sm-6">
                        <label for="gst">GST No.</label>
                        <input type="text" class="form-control" id="gst" name="gst"
                               placeholder="Enter GST No." value="{{ $company->gst ?? '' }}">
                    </div>

                    @foreach(['CGST','SGST','IGST'] as $i => $label)
                        <div class="form-group col-md-3 col-sm-6" data-tax="{{ $label }}">
                            <label>{{ $label }}</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="tax_rates[]"
                                       class="form-control"
                                       value="{{ $rates[$i] }}"
                                       placeholder="Enter {{ $label }} Rate">
                            </div>
                        </div>
                    @endforeach

                    {{-- VAT No. followed by its rate box --}}
                    <div class="form-group col-md-3 col-sm-6">
                        <label for="vat">VAT No.</label>
                        <input type="text" class="form-control" id="vat" name="vat"
                               placeholder="Enter VAT No." value="{{ $company->vat ?? '' }}">
                    </div>

                    <div class="form-group col-md-3 col-sm-6" data-tax="VAT">
                        <label>VAT</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="tax_rates[]"
                                   class="form-control"
                                   value="{{ $rates[3] }}"
                                   placeholder="Enter VAT Rate">
                        </div>
                    </div>
                    
                    <div class="form-group col-md-6"></div>
                    {{-- Address --}}
                    <div class="form-group col-md-6">
                        <label for="address">Address</label>
                        <textarea class="form-control" id="address" rows="4" name="address"
                                  placeholder="Enter Address">{{ $company->address ?? '' }}</textarea>
                    </div>

                    <div class="form-group col-md-6">
                        <div class="row">
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="city">City</label>
                                <input type="text" class="form-control" id="city" name="city"
                                       placeholder="Enter City" value="{{ $company->city ?? '' }}">
                            </div>
        
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="state">State</label>
                                <input type="text" class="form-control" id="state" name="state"
                                       placeholder="Enter State" value="{{ $company->state ?? '' }}">
                            </div>
        
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="zipcode">Zip Code</label>
                                <input type="text" class="form-control" id="zipcode" name="zipcode"
                                       placeholder="Enter Zip Code" value="{{ $company->zipcode ?? '' }}">
                            </div>
        
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="country">Country</label>
                                <input type="text" class="form-control" id="country" name="country"
                                       placeholder="Enter Country" value="{{ $company->country ?? '' }}">
                            </div>
                        </div>
                    </div>
                    @php
                        $bank_details = json_decode(($company->bank_details ?? ''),true);
                    @endphp
                    <div class="form-group col-md-12">
                        <div class="row">
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="Bank_Name">Bank Name</label>
                                <input type="text" class="form-control" id="bankname" name="bank_details[]"
                                       placeholder="Enter Bank Name" value="{{ $bank_details[0] ?? '' }}">
                            </div>
        
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="accountname">Account Name</label>
                                <input type="text" class="form-control" id="accountname" name="bank_details[]"
                                       placeholder="Enter Account Name" value="{{ $bank_details[1] ?? '' }}">
                            </div>
        
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="accountno">Account No.</label>
                                <input type="text" class="form-control" id="accountno" name="bank_details[]" maxlength="25"
                                       placeholder="Enter Account No." value="{{ $bank_details[2] ?? '' }}">
                            </div>
        
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="ifsc">IFSC</label>
                                <input type="text" class="form-control" id="ifsc" name="bank_details[]" maxlength="25"
                                       placeholder="Enter IFSC" value="{{ $bank_details[3] ?? '' }}">
                            </div>
        
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="upi">UPI Id</label>
                                <input type="text" class="form-control" id="upi" name="bank_details[]" maxlength="25"
                                       placeholder="Enter Upi Id" value="{{ $bank_details[4] ?? '' }}">
                            </div>
                        </div>
                    </div>
                    @php
                        $subscriptions = ["standard", "premium", "pro"];
                    @endphp
                    
                    @if(Auth::user()->role == 'master')
                    <div class="form-group col-md-12">
                        <div class="row">
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="subscription">Subscriptions Plan</label><br>
                                <div class="d-inline-flex justified-content-start g-22 align-items-center">
                                @foreach($subscriptions as $subscription)
                                    <div class="check-item">
                                        <input type="radio" id="subscription" name="subscription" value="{{ $subscription }}" @if($subscription == ($company->plan ?? '')) checked @endif> {{ $subscription }}
                                    </div>
                                @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="form-group text-right col-md-12 mt-2">
                        <button type="submit" class="btn btn-primary px-4">Submit</button>
                        <button type="reset"  class="btn btn-light border px-4">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

{{-- ----------------------------------------------------------
     JS: show GST‑rate rows only if GSTNo. filled,
         and VAT‑rate row only if VATNo. filled
----------------------------------------------------------- --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const gstInput = document.getElementById('gst');
    const vatInput = document.getElementById('vat');

    const taxRows = {
        CGST : document.querySelector('[data-tax="CGST"]'),
        SGST : document.querySelector('[data-tax="SGST"]'),
        IGST : document.querySelector('[data-tax="IGST"]'),
        VAT  : document.querySelector('[data-tax="VAT"]')
    };

    function setRowVisible(row, visible) {
        row.style.display = visible ? '' : 'none';
        row.querySelector('input').disabled = !visible; // keep POST payload clean
    }

    function refreshVisibility() {
        const hasGST = gstInput.value.trim() !== '';
        const hasVAT = vatInput.value.trim() !== '';

        // GST present → show 3 rows
        ['CGST','SGST','IGST'].forEach(key =>
            setRowVisible(taxRows[key], hasGST)
        );

        // VAT present → show VAT row
        setRowVisible(taxRows.VAT, hasVAT);
    }

    // first run & live updates
    refreshVisibility();
    gstInput.addEventListener('input', refreshVisibility);
    vatInput.addEventListener('input', refreshVisibility);
});
</script>

@endsection
