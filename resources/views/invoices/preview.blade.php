@extends('layout')
@section('title', 'Preview Invoice # INV-' . ($invoice->invoice_number ?? ''))

@section('content')

@php
    $company = session('companies');
    
    function amountToWords($amount, string $locale = 'en_IN'): string
    {
        // normalise
        $amount   = (float) str_replace([',', ' '], '', $amount);
        $rupees   = (int) $amount;
        $paise    = (int) round(($amount - $rupees) * 100);
    
        if (class_exists('NumberFormatter')) {
            $fmt = new NumberFormatter($locale, NumberFormatter::SPELLOUT);
            $words  = ucfirst($fmt->format($rupees)) . ' rupees';
            if ($paise) {
                $words .= ' and ' . $fmt->format($paise) . ' paise';
            }
            return $words . ' only';
        }
    
        $units  = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven',
                   'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen',
                   'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen',
                   'nineteen'];
        $tens   = ['', '', 'twenty', 'thirty', 'forty', 'fifty',
                   'sixty', 'seventy', 'eighty', 'ninety'];
    
        // helper for 1‑ or 2‑digit chunks
        $twoDigits = function ($n) use ($units, $tens) {
            if ($n < 20) return $units[$n];
            $t   = (int) ($n / 10);
            $u   =  $n % 10;
            return $tens[$t] . ($u ? '-' . $units[$u] : '');
        };
    
        // helper for 3‑digit chunk
        $threeDigits = function ($n) use ($twoDigits, $units) {
            $h = (int) ($n / 100);
            $r = $n % 100;
            return ($h ? $units[$h] . ' hundred' . ($r ? ' ' : '') : '')
                 . ($r ? $twoDigits($r) : '');
        };
    
        $parts = [
            'crore'   => (int) ($rupees / 10000000),
            'lakh'    => (int) ($rupees / 100000) % 100,
            'thousand'=> (int) ($rupees / 1000)  % 100,
            'hundred' => (int) ($rupees / 100)   % 10,
            'rest'    =>  $rupees % 100,
        ];
    
        $inWords = [];
        if ($parts['crore'])    $inWords[] = $threeDigits($parts['crore'])    . ' crore';
        if ($parts['lakh'])     $inWords[] = $threeDigits($parts['lakh'])     . ' lakh';
        if ($parts['thousand']) $inWords[] = $threeDigits($parts['thousand']) . ' thousand';
        if ($parts['hundred'])  $inWords[] = $units[$parts['hundred']] . ' hundred';
        if ($parts['rest'])     $inWords[] = $twoDigits($parts['rest']);
    
        $words  = ucfirst(implode(' ', $inWords)) . ' rupees';
        if ($paise) {
            $words .= ' and ' . $twoDigits($paise) . ' paise';
        }
        return $words . ' only';
    }
@endphp

<section class="task__section">
    <div class="text">
        <i class="bx bx-menu" id="mbtn"></i>
        Invoice Preview
        <a href="/signout" class="logoutbtn"><i class='bx bx-log-out'></i></a>
    </div>

    <div class="container-fluid">
        <div class="board-title board-title-flex mb-3">
            <div class="header-left">
                <a href="/invoices" class="btn btn-primary btn-sm back-btn"><i class="bx bx-arrow-back"></i></a>
                <label class="font-weight-bold">Invoice # INV-{{ $invoice->invoice_number }}</label>
            </div>
            <a href="/invoices/download/{{ $invoice->id ?? 0 }}" class="btn btn-danger btn-sm">
                <i class='bx bxs-file-pdf'></i> Download PDF
            </a>
        </div>

        <div class="invoice-preview bg-white p-3 rounded">
            {{-- HEADER / COMPANY INFO --}}
            <div class="row mb-4 align-items-center">
                <div class="col-md-6">
                    @if(!empty($invoice->img))
                        <img 
                            src="{{ asset('/public/assets/images/company/' . ($invoice->img ?? '')) }}" 
                            alt="{{ $invoice->name ?? '' }}" 
                            style="max-height:60px; filter: drop-shadow(0px 0px 0px black);"
                        ><br>
                    @endif
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <label style="font-size: 23px;text-transform:uppercase;">{{ $invoice->invoice }} @if(($invoice->invoice ?? '') != 'invoice') INVOICE @endif</label><br>
                    <label style="color: #737373;">
                        #{{ ($invoice->invoice ?? '') != 'tax' ? strtoupper(substr($invoice->invoice, 0, 3)) : 'INV' }}-{{ $invoice->invoice_number }}
                    </label><br>
                    {{-- Payment Status Badge --}}
                    @php
                        $status = strtolower($invoice->status);
                        $badgeClass = match($status) {
                            'unpaid' => 'text-danger',
                            'paid' => 'text-success',
                            'partial' => 'text-warning',
                            'cancelled' => 'text-secondary',
                            default => 'text-info'
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}" style="font-size: 1rem;">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>
            </div>

            {{-- BILL TO / SHIP TO --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5 style="margin: 0 0 5px 0;">{{ $invoice->cn ?? '' }}</h5>
                    @php
                        $addressParts = array_filter([
                            $invoice->city ?? null,
                            $invoice->state ?? null,
                            " - ".$invoice->zipcode ?? null,
                            $invoice->country ?? null
                        ]);
                    @endphp
                    
                    @if(!empty($addressParts))
                    <div style="white-space: pre-line;">{{ $invoice->address ?? '' }}<br>{{ implode(', ', $addressParts) }}</div>
                    @endif
                    <div style="white-space: pre-line;">Phone: +91-{{ $invoice->cm ?? '' }}</div>
                    <div style="white-space: pre-line;">Email: {{ $invoice->ce ?? '' }}</div>
                    <!--GST Number / Vat Number-->
                    @if(!empty($invoice->cgst) && (($invoice->invoice ?? '') != 'invoice'))
                    <div style="white-space: pre-line;"><strong>GST NO.:</strong> {{ $invoice->cgst }}</div>
                    @endif
                    @if(!empty($invoice->cvat) && (($invoice->invoice ?? '') != 'invoice'))
                    <div style="white-space: pre-line;"><strong>VAT NO.:</strong> {{ $invoice->cvat }}</div>
                    @endif
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <h6 class="font-weight-bold">Bill To</h6>
                    <h5 style="margin: 0 0 5px 0;">{{ $invoice->company }}</h5>
                    @if(!empty($invoice->billing_address))
                        {!! nl2br(e($invoice->billing_address)) !!}
                    @endif
                    
                    @if(!empty($invoice->shipping_address))
                        <br><br>
                        <h6 class="font-weight-bold">Ship To</h6>
                        {!! nl2br(e($invoice->shipping_address)) !!}
                    @endif
                    
                    <!--GST Number / Vat Number-->
                    @if(!empty($invoice->client_gstno))
                    <div style="white-space: pre-line;"><strong>GST NO.:</strong>{{ $invoice->client_gstno }}</div>
                    @endif
                    
                    @if(!empty($invoice->vat))
                    <div style="white-space: pre-line;"><strong>VAT NO.:</strong>{{ $invoice->vat }}</div>
                    @endif
                </div>
            </div>

            {{-- DATES & REFERENCE --}}
            <div class="row mb-3">
                <div class="col-md-4 mb-3 mb-md-0">
                    <strong>Invoice Date:</strong> 
                    {{ \Carbon\Carbon::parse($invoice->date)->format('M d, Y') }}<br>
                    
                    <strong>Due Date:</strong> 
                    {{ $invoice->due_date 
                        ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') 
                        : 'N/A' }}<br>
                        
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    
                </div>
                <div class="col-md-4 text-md-end">
                    <strong>Reference / PO #:</strong> 
                    {{ $invoice->reference ?? 'N/A' }}

                    @if(!empty($invoice->payment_mode))
                        <br><strong>Payment Mode:</strong> 
                        {{ ucfirst($invoice->payment_mode) }}
                    @endif
                    
                    <br><strong>Sales Agent:</strong> 
                    {{ ucfirst($invoice->sales_agent) }}
                </div>
            </div>

            {{-- ITEM TABLE --}}
            <div class="table-responsive mb-4">
                <table class="table table-striped rounded" style="border-radius: 5px!important;overflow: hidden;">
                    <thead class="table-default">
                        <tr>
                            <th class="text-center">#</th>
                            <th>Item</th>
                            <th class="text-center" style="width: 100px;">SAC Code</th>
                            <th class="text-center" style="width: 75px;">Qty/Hours</th>
                            <th class="text-end" style="width: 75px;">Rate</th>
                            <th class="text-end" style="width: 130px;">Tax (%)</th>
                            <th class="text-end" style="width: 130px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $subTotal  = 0;
                            $totalTax  = 0;
                            $totalCgst = 0;
                            $totalSgst = 0;
                            $totalIgst = 0;
                            $totalVat  = 0;
                        @endphp

                        @foreach($invoice_items as $k=>$item)
                            @php
                                $qty       = $item->quantity;
                                $price     = $item->price;
                                $cgstRate  = $item->cgst_percent;
                                $sgstRate  = $item->sgst_percent;
                                $igstRate  = $item->igst_percent;
                                $vatRate   = $item->vat_percent;

                                $lineSub   = $qty * $price;
                                
                                // Calculate tax for each type
                                $cgstTax   = ($lineSub * $cgstRate) / 100;
                                $sgstTax   = ($lineSub * $sgstRate) / 100;
                                $igstTax   = ($lineSub * $igstRate) / 100;
                                $vatTax    = ($lineSub * $vatRate) / 100;

                                // Add to total tax variables
                                $totalCgst += $cgstTax;
                                $totalSgst += $sgstTax;
                                $totalIgst += $igstTax;
                                $totalVat  += $vatTax;

                                $lineTotal = $lineSub + $cgstTax + $sgstTax + $igstTax + $vatTax; // Total includes all tax types
                                $subTotal += $lineSub;
                                $totalTax += $cgstTax + $sgstTax + $igstTax + $vatTax; // Sum of all taxes
                            @endphp
                            <tr>
                                <td class="text-center">{!! $k+1 !!}</td>
                                <td><strong>{!! nl2br(e($item->short_description)) !!}</strong><br>{!! nl2br(e($item->long_description)) !!}</td>
                                <td class="text-center">{{ $item->sac_code ?? '--' }}</td>
                                <td class="text-center">{{ $qty }}</td>
                                <td class="text-end">{{ number_format($price, 2) }}</td>
                                <td class="text-end" style="width: 130px;">
                                    @if($item->cgst_percent > 0)
                                        CGST {{ number_format($item->cgst_percent, 2) }}%<br>
                                    @endif
                                    @if($item->sgst_percent > 0)
                                        SGST {{ number_format($item->sgst_percent, 2) }}%<br>
                                    @endif
                                    @if($item->igst_percent > 0)
                                        IGST {{ number_format($item->igst_percent, 2) }}%<br>
                                    @endif
                                    @if($item->vat_percent > 0)
                                        VAT {{ number_format($item->vat_percent, 2) }}%
                                    @endif
                                </td>
                                <td class="text-end" style="width: 130px;">{{ number_format($lineSub, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- CALCULATION SUMMARY --}}
            @php
                // Calculate discount
                $discountType = $invoice->discount_type ?? 'flat';
                $discountVal  = floatval($invoice->discount ?? 0);

                if ($discountType === 'percentage') {
                    // Apply discount on the total including tax if the place is 'after-tax'
                    $discount = ($invoice->discount_place == 'after-tax') 
                                ? ($subTotal + $totalTax) * ($discountVal / 100) 
                                : $subTotal * ($discountVal / 100);
                } else {
                    // For flat discount
                    $discount = $discountVal;
                }

                // Adjustments
                $adjustment = floatval($invoice->adjustment ?? 0);

                // Grand Total
                $grandTotal = $subTotal + $totalTax - $discount - $adjustment;

                // If you store partial payments, calculate total paid
                $amountPaid = isset($invoice->payments)
                                ? $invoice->payments->sum('amount')
                                : 0;

                // Balance remaining
                $balanceDue = $grandTotal - $amountPaid;
            @endphp

            <div class="row">
                <div class="col-md-8"></div>
                <div class="col-md-4">
                    <table class="table border mb-0" style="border-radius: 5px!important;">
                        <tr>
                            <th class="text-right">Subtotal</th>
                            <td class="text-right">
                                {{ number_format($subTotal, 2) }}
                            </td>
                        </tr>

                        <!-- Discount -->
                        @if(!empty($discount) && $invoice->discount_type == 'before-tax')
                        <tr>
                            <td class="text-right"><strong>Discount</strong></td>
                            <td class="text-right">{{ number_format($discount, 2) }}</td>
                        </tr>
                        @endif

                        <!-- Total Taxes -->
                        @if(!empty($totalCgst))
                        <tr>
                            <td class="text-right"><strong>Total CGST</strong></td>
                            <td class="text-right">{{ number_format($totalCgst, 2) }}</td>
                        </tr>
                        @endif
                        @if(!empty($totalSgst))
                        <tr>
                            <td class="text-right"><strong>Total SGST</strong></td>
                            <td class="text-right">{{ number_format($totalSgst, 2) }}</td>
                        </tr>
                        @endif
                        @if(!empty($totalIgst))
                        <tr>
                            <td class="text-right"><strong>Total IGST</strong></td>
                            <td class="text-right">{{ number_format($totalIgst, 2) }}</td>
                        </tr>
                        @endif
                        @if(!empty($totalVat))
                        <tr>
                            <td class="text-right"><strong>Total VAT</strong></td>
                            <td class="text-right">{{ number_format($totalVat, 2) }}</td>
                        </tr>
                        @endif

                        <!-- Discount -->
                        @if(!empty($discount) && $invoice->discount_type == 'after-tax')
                        <tr>
                            <td class="text-right"><strong>Discount</strong></td>
                            <td class="text-right">{{ number_format($discount, 2) }}</td>
                        </tr>
                        @endif

                        @if(isset($adjustment) && $invoice->adjustment > 0)
                        <tr>
                            <th class="text-right">Advance Payment</th>
                            <td class="text-right">
                                - {{ number_format($adjustment, 2) }}
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <th class="text-right">Grand Total</th>
                            <td class="text-right">
                                <strong>{{ number_format($grandTotal, 2) }}</strong>
                                @if(!empty($invoice->currency))
                                    <small>{{ $invoice->currency }}</small>
                                @endif
                            </td>
                        </tr>

                        @if(isset($amountPaid) && $amountPaid > 0)
                        <tr>
                            <th class="text-right">Paid</th>
                            <td class="text-right">
                                {{ number_format($amountPaid, 2) }}
                            </td>
                        </tr>
                        @endif
                        @if(isset($balanceDue) && $balanceDue > 0)
                        <tr>
                            <th class="text-right">Balance Due</th>
                            <td class="text-right">
                                <strong>{{ number_format($balanceDue, 2) }}</strong>
                                @if(!empty($invoice->currency))
                                    <small>{{ $invoice->currency }}</small>
                                @endif
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
                <div class="col-md-12 text-center mt-4">
                    <h6 class="font-weight-bold">{{ ucwords(amountToWords($grandTotal ?? 0)) }}</h6>
                </div>
                <div class="col-md-12">
                    @php $company = session('companies'); $companyBankDetails = json_decode($invoice->bank_details ?? $company->bank_details ?? ''); @endphp
                    
                    @if(!empty($companyBankDetails[0]))
                    <hr style="border-top:1px solid #cacaca;">
                    <div class="row">
                        <div class="col-md-12">
                            <h6>Payment & Account Info</h6>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-0">Name: {{ $companyBankDetails[1] ?? '' }}</p>
                            <p class="mb-0">Bank Name: {{ $companyBankDetails[0] ?? '' }}</p>
                            <p class="mb-0">IFSC Code: {{ $companyBankDetails[3] ?? '' }}</p>
                            <p class="mb-0">Ac No: {{ $companyBankDetails[2] ?? '' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-0">Upi: {{ $companyBankDetails[4] ?? '' }}</p><br><br><br>
                        </div>
                    </div>
                    @endif
                    
                    {{-- Notes --}}
                    @if(!empty($invoice->client_note))
                        <div class="mb-3">
                            <h6>Client Note</h6>
                            <p>{!! nl2br(e($invoice->client_note)) !!}</p>
                        </div>
                    @endif
                    
                    @if(!empty($invoice->terms))
                        <div class="mb-3">
                            <h6>Terms &amp; Conditions</h6>
                            <p>{!! nl2br(e($invoice->terms)) !!}</p>
                        </div>
                    @endif

                    @if(!empty($invoice->admin_note))
                        <div class="alert alert-warning">
                            <strong>Admin Note:</strong><br>
                            {!! nl2br(e($invoice->admin_note)) !!}
                        </div>
                    @endif
                </div>
            </div>
            <!--@php
            $company = json_decode(($invoice->bank_details ?? ''),true);
            @endphp
            {{-- BANK DETAILS --}}
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="font-weight-bold">Company Bank Details</h6>
                    <p>
                        <strong>Bank Name:</strong> {{ $company[0] ?? 'N/A' }}<br>
                        <strong>Account Number:</strong> {{ $company[2] ?? 'N/A' }}<br>
                        <strong>IFSC Code:</strong> {{ $company[3] ?? 'N/A' }}<br>
                        <strong>Account Holder:</strong> {{ $company[1] ?? 'N/A' }}
                    </p>
                </div>
            </div>-->

            {{-- FOOTER MESSAGE --}}
            <div class="text-center mt-4">
                <p class="text-muted">
                    Thank you for your business!
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
