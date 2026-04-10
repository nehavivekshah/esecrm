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

<section class="task__section" style="background-color: #f1f3f4; min-height: 100vh; padding-bottom: 50px;">
    <div class="text" style="background: #fff; border-bottom: 1px solid #e8eaed; padding: 15px 20px; margin-bottom: 20px;">
        <i class="bx bx-menu" id="mbtn"></i>
        Invoice Preview
        <a href="/signout" class="logoutbtn"><i class='bx bx-log-out'></i></a>
    </div>

    <div class="container-fluid" style="max-width: 1000px; margin: 0 auto;">
        
        {{-- Premium Sticky Action Bar --}}
        <div class="d-flex align-items-center justify-content-between bg-white px-4 py-3 mb-4 shadow-sm" style="border-radius: 12px; position: sticky; top: 85px; z-index: 100; box-shadow: 0 4px 15px rgba(0,0,0,0.03)!important; border: 1px solid #f1f3f4;">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ url()->previous() == url()->current() ? '/invoices' : url()->previous() }}" class="btn btn-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 50%; background: #f8f9fa; border: 1px solid #e8eaed; color: #5f6368;" title="Back">
                    <i class="bx bx-arrow-back" style="font-size: 1.2rem;"></i>
                </a>
                <div>
                    <h5 class="mb-0" style="font-weight: 700; color: #202124;">Preview: INV-{{ $invoice->invoice_number }}</h5>
                    <div style="font-size: 0.75rem; color: #80868b;">Review your invoice design before sending</div>
                </div>
            </div>
            <a href="/invoices/download/{{ $invoice->id ?? 0 }}" class="btn text-white px-4" style="background: linear-gradient(135deg, #006666, #00a3a3); border-radius: 8px; font-weight: 600; padding-top: 10px; padding-bottom: 10px; box-shadow: 0 4px 12px rgba(0,102,102,0.2); transition: all 0.2s;">
                <i class='bx bxs-file-pdf me-1'></i> Download PDF
            </a>
        </div>

        {{-- Invoice Paper Container --}}
        <div class="invoice-preview bg-white" style="border-radius: 16px; padding: 60px; box-shadow: 0 15px 35px rgba(0,0,0,0.06); border-top: 8px solid #006666; position: relative; overflow: hidden;">
            
            {{-- Watermark (Optional) --}}
            @if(strtolower($invoice->status) == 'paid')
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 8rem; font-weight: 900; color: rgba(52, 168, 83, 0.04); text-transform: uppercase; z-index: 0; pointer-events: none;">PAID</div>
            @endif

            <div style="position: relative; z-index: 1;">
                {{-- HEADER / COMPANY INFO --}}
                <div class="row mb-5 align-items-center pb-4" style="border-bottom: 2px solid #f1f3f4;">
                    <div class="col-md-6">
                        @if(!empty($invoice->img))
                            <img 
                                src="{{ asset('assets/images/company/' . ($invoice->img ?? '')) }}" 
                                alt="{{ $invoice->name ?? '' }}" 
                                style="max-height: 70px; object-fit: contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.05));"
                            >
                        @else
                            <h3 style="color: #006666; font-weight: 800; letter-spacing: -0.5px;">{{ $invoice->cn ?? 'OUR COMPANY' }}</h3>
                        @endif
                    </div>
                    <div class="col-md-6 text-md-end mt-4 mt-md-0">
                        <h2 style="font-size: 2.2rem; font-weight: 800; color: #202124; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 5px;">{{ $invoice->invoice ?? 'INVOICE' }}</h2>
                        <h5 style="color: #80868b; font-weight: 500; font-family: monospace; font-size: 1.1rem; margin-bottom: 15px;">
                            #{{ ($invoice->invoice ?? '') != 'tax' ? strtoupper(substr($invoice->invoice, 0, 3)) : 'INV' }}-{{ $invoice->invoice_number }}
                        </h5>
                        {{-- Payment Status Badge --}}
                        @php
                            $status = strtolower($invoice->status);
                            $badgeStyle = match($status) {
                                'unpaid' => 'background: rgba(220, 53, 69, 0.1); color: #dc3545; border: 1px solid rgba(220, 53, 69, 0.2);',
                                'paid' => 'background: rgba(40, 167, 69, 0.1); color: #28a745; border: 1px solid rgba(40, 167, 69, 0.2);',
                                'partial' => 'background: rgba(255, 193, 7, 0.1); color: #ffc107; border: 1px solid rgba(255, 193, 7, 0.2);',
                                'cancelled' => 'background: rgba(108, 117, 125, 0.1); color: #6c757d; border: 1px solid rgba(108, 117, 125, 0.2);',
                                default => 'background: rgba(23, 162, 184, 0.1); color: #17a2b8; border: 1px solid rgba(23, 162, 184, 0.2);'
                            };
                        @endphp
                        <span style="padding: 6px 16px; border-radius: 30px; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; {{ $badgeStyle }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </div>
                </div>

                {{-- ISSUER AND CLIENT SECION --}}
                <div class="row mb-5">
                    <div class="col-sm-6">
                        <div style="padding-right: 20px;">
                            <h6 style="color: #80868b; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; margin-bottom: 10px; letter-spacing: 0.5px;">Issued By</h6>
                            <h5 style="margin: 0 0 8px 0; font-weight: 700; color: #202124;">{{ $invoice->cn ?? '' }}</h5>
                            
                            @php
                                $addressParts = array_filter([
                                    $invoice->city ?? null,
                                    $invoice->state ?? null,
                                    ($invoice->zipcode ? " - " . $invoice->zipcode : null),
                                    $invoice->country ?? null
                                ]);
                            @endphp
                            <div style="font-size: 0.9rem; color: #5f6368; line-height: 1.6;">
                                @if(!empty($invoice->address))
                                    <div>{!! nl2br(e($invoice->address)) !!}</div>
                                @endif
                                @if(!empty($addressParts))
                                    <div>{{ implode(', ', $addressParts) }}</div>
                                @endif
                                <div class="mt-2">
                                    @if(!empty($invoice->cm)) <strong>P:</strong> +91-{{ $invoice->cm }}<br> @endif
                                    @if(!empty($invoice->ce)) <strong>E:</strong> {{ $invoice->ce }}<br> @endif
                                </div>
                                <div class="mt-2">
                                    @if(!empty($invoice->cgst) && (($invoice->invoice ?? '') != 'invoice'))
                                        <strong>GST NO:</strong> <span style="font-family: monospace;">{{ $invoice->cgst }}</span><br>
                                    @endif
                                    @if(!empty($invoice->cvat) && (($invoice->invoice ?? '') != 'invoice'))
                                        <strong>VAT NO:</strong> <span style="font-family: monospace;">{{ $invoice->cvat }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-6 mt-4 mt-sm-0 text-sm-end">
                        <div style="padding-left: 20px;">
                            <h6 style="color: #80868b; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; margin-bottom: 10px; letter-spacing: 0.5px;">Billed To</h6>
                            <h5 style="margin: 0 0 8px 0; font-weight: 700; color: #006666;">{{ $invoice->company }}</h5>
                            <div style="font-size: 0.9rem; color: #5f6368; line-height: 1.6;">
                                @if(!empty($invoice->billing_address))
                                    <div class="mb-2">{!! nl2br(e($invoice->billing_address)) !!}</div>
                                @endif
                                
                                @if(!empty($invoice->client_gstno) || !empty($invoice->vat))
                                    <div class="mt-2 p-2" style="background: #f8f9fa; border-radius: 6px; display: inline-block; text-align: left;">
                                        @if(!empty($invoice->client_gstno))
                                            <div style="font-size:0.8rem;"><strong>GST:</strong> <span style="font-family: monospace;">{{ $invoice->client_gstno }}</span></div>
                                        @endif
                                        @if(!empty($invoice->vat))
                                            <div style="font-size:0.8rem;"><strong>VAT:</strong> <span style="font-family: monospace;">{{ $invoice->vat }}</span></div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DATES & DETAILS CARDS --}}
                <div class="row mb-5 g-3">
                    <div class="col-sm-3 col-6">
                        <div style="background: #f8fdfd; border: 1px solid #e0f2f1; padding: 15px; border-radius: 10px;">
                            <div style="font-size: 0.72rem; color: #006666; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">Invoice Date</div>
                            <div style="font-weight: 600; color: #202124;">{{ \Carbon\Carbon::parse($invoice->date)->format('M d, Y') }}</div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-6">
                        <div style="background: #fff5f5; border: 1px solid #ffebe9; padding: 15px; border-radius: 10px;">
                            <div style="font-size: 0.72rem; color: #d73a49; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">Due Date</div>
                            <div style="font-weight: 600; color: #202124;">
                                {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') : 'N/A' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-6">
                        <div style="background: #f8f9fa; border: 1px solid #e8eaed; padding: 15px; border-radius: 10px;">
                            <div style="font-size: 0.72rem; color: #5f6368; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">Reference / PO</div>
                            <div style="font-weight: 600; color: #202124; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $invoice->reference ?: 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-6">
                        <div style="background: #f8f9fa; border: 1px solid #e8eaed; padding: 15px; border-radius: 10px;">
                            <div style="font-size: 0.72rem; color: #5f6368; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">Payment Mode</div>
                            <div style="font-weight: 600; color: #202124;">{{ $invoice->payment_mode ? ucfirst($invoice->payment_mode) : 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                {{-- ITEM TABLE --}}
                <div class="table-responsive mb-4" style="border-radius: 10px; overflow: hidden; border: 1px solid #e8eaed;">
                    <table class="table mb-0" style="width: 100%; border-collapse: collapse;">
                        <thead style="background: #f8f9fa; border-bottom: 2px solid #e8eaed;">
                            <tr>
                                <th class="text-center" style="padding: 15px 10px; color: #5f6368; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">#</th>
                                <th style="padding: 15px 10px; color: #5f6368; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Description</th>
                                <th class="text-center" style="padding: 15px 10px; color: #5f6368; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; width: 100px;">SAC Code</th>
                                <th class="text-center" style="padding: 15px 10px; color: #5f6368; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; width: 80px;">Qty</th>
                                <th class="text-end" style="padding: 15px 10px; color: #5f6368; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; width: 100px;">Rate</th>
                                <th class="text-end" style="padding: 15px 10px; color: #5f6368; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; width: 120px;">Tax</th>
                                <th class="text-end" style="padding: 15px 20px 15px 10px; color: #5f6368; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; width: 120px;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $subTotal  = 0;
                                $totalTax  = 0;
                                $totalCgst = 0; $totalSgst = 0; $totalIgst = 0; $totalVat  = 0;
                            @endphp

                            @foreach($invoice_items as $k=>$item)
                                @php
                                    $qty       = $item->quantity;
                                    $price     = $item->price;
                                    $lineSub   = $qty * $price;
                                    
                                    $cgstTax   = ($lineSub * $item->cgst_percent) / 100;
                                    $sgstTax   = ($lineSub * $item->sgst_percent) / 100;
                                    $igstTax   = ($lineSub * $item->igst_percent) / 100;
                                    $vatTax    = ($lineSub * $item->vat_percent) / 100;

                                    $totalCgst += $cgstTax; $totalSgst += $sgstTax; $totalIgst += $igstTax; $totalVat += $vatTax;
                                    $subTotal += $lineSub;
                                    $totalTax += $cgstTax + $sgstTax + $igstTax + $vatTax;
                                @endphp
                                <tr style="border-bottom: 1px solid #f1f3f4;">
                                    <td class="text-center" style="padding: 15px 10px; color: #80868b; font-size: 0.9rem;">{{ $k+1 }}</td>
                                    <td style="padding: 15px 10px;">
                                        <div style="font-weight: 700; color: #202124; font-size: 0.95rem;">{!! nl2br(e($item->short_description)) !!}</div>
                                        @if(!empty($item->long_description))
                                            <div style="font-size: 0.8rem; color: #5f6368; margin-top: 4px; line-height: 1.4;">{!! nl2br(e($item->long_description)) !!}</div>
                                        @endif
                                    </td>
                                    <td class="text-center" style="padding: 15px 10px; font-size: 0.85rem; color: #5f6368;">{{ $item->sac_code ?: '--' }}</td>
                                    <td class="text-center" style="padding: 15px 10px; font-weight: 600; color: #202124;">{{ $qty }}</td>
                                    <td class="text-end" style="padding: 15px 10px; font-size: 0.9rem; color: #202124;">{{ number_format($price, 2) }}</td>
                                    <td class="text-end" style="padding: 15px 10px; font-size: 0.8rem; color: #5f6368; line-height: 1.5;">
                                        @if($item->cgst_percent > 0) CGST: {{ number_format($item->cgst_percent, 1) }}%<br> @endif
                                        @if($item->sgst_percent > 0) SGST: {{ number_format($item->sgst_percent, 1) }}%<br> @endif
                                        @if($item->igst_percent > 0) IGST: {{ number_format($item->igst_percent, 1) }}%<br> @endif
                                        @if($item->vat_percent > 0) VAT: {{ number_format($item->vat_percent, 1) }}% @endif
                                        @if($item->cgst_percent == 0 && $item->sgst_percent == 0 && $item->igst_percent == 0 && $item->vat_percent == 0)
                                            <span style="color:#adb5bd;">--</span>
                                        @endif
                                    </td>
                                    <td class="text-end" style="padding: 15px 20px 15px 10px; font-weight: 700; color: #202124; font-size: 0.95rem;">{{ number_format($lineSub, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- CALCULATION SUMMARY --}}
                @php
                    $discountType = $invoice->discount_type ?? 'flat';
                    $discountVal  = floatval($invoice->discount ?? 0);
                    if ($discountType === 'percentage') {
                        $discount = ($invoice->discount_place == 'after-tax') ? ($subTotal + $totalTax) * ($discountVal / 100) : $subTotal * ($discountVal / 100);
                    } else {
                        $discount = $discountVal;
                    }
                    $adjustment = floatval($invoice->adjustment ?? 0);
                    $grandTotal = $subTotal + $totalTax - $discount - $adjustment;
                    $amountPaid = isset($invoice->payments) ? $invoice->payments->sum('amount') : 0;
                    $balanceDue = $grandTotal - $amountPaid;
                @endphp

                <div class="row">
                    <div class="col-md-7">
                        {{-- Notes & Bank --}}
                        <div class="mt-2 text-muted" style="font-size: 0.85rem; line-height: 1.6;">
                            @if(!empty($invoice->client_note))
                                <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #006666;">
                                    <strong style="color: #202124; display: block; margin-bottom: 5px;">Client Note:</strong>
                                    {!! nl2br(e($invoice->client_note)) !!}
                                </div>
                            @endif
                            @if(!empty($invoice->terms))
                                <div style="margin-bottom: 20px;">
                                    <strong style="color: #5f6368; display: block; margin-bottom: 5px; text-transform: uppercase; font-size: 0.75rem;">Terms & Conditions</strong>
                                    {!! nl2br(e($invoice->terms)) !!}
                                </div>
                            @endif

                            @php $companyBankDetails = json_decode($invoice->bank_details ?? session('companies')->bank_details ?? '["","","","",""]', true) ?: ["","","","",""]; @endphp
                            @if(!empty($companyBankDetails[0]))
                                <div style="margin-top: 30px; border-top: 1px solid #e8eaed; padding-top: 20px;">
                                    <h6 style="color: #202124; font-weight: 700; margin-bottom: 15px;">Payment Details</h6>
                                    <div style="background: #f8fdfd; border: 1px solid #e0f2f1; padding: 15px; border-radius: 8px; width: fit-content; min-width: 300px;">
                                        <table style="width: 100%; font-size: 0.85rem;">
                                            @if(!empty($companyBankDetails[0]))
                                            <tr><td style="color:#5f6368; padding-bottom:5px; width: 100px;">Bank Name:</td><td style="font-weight:600; color:#202124;">{{ $companyBankDetails[0] }}</td></tr>
                                            @endif
                                            @if(!empty($companyBankDetails[1]))
                                            <tr><td style="color:#5f6368; padding-bottom:5px;">A/C Name:</td><td style="font-weight:600; color:#202124;">{{ $companyBankDetails[1] }}</td></tr>
                                            @endif
                                            @if(!empty($companyBankDetails[2]))
                                            <tr><td style="color:#5f6368; padding-bottom:5px;">A/C No:</td><td style="font-weight:600; color:#202124;">{{ $companyBankDetails[2] }}</td></tr>
                                            @endif
                                            @if(!empty($companyBankDetails[3]))
                                            <tr><td style="color:#5f6368; padding-bottom:5px;">IFSC:</td><td style="font-weight:600; color:#202124;">{{ $companyBankDetails[3] }}</td></tr>
                                            @endif
                                            @if(!empty($companyBankDetails[4]))
                                            <tr><td style="color:#5f6368; padding-top:5px; border-top: 1px dashed #ced4da;">UPI ID:</td><td style="font-weight:600; color:#006666; padding-top:5px; border-top: 1px dashed #ced4da;">{{ $companyBankDetails[4] }}</td></tr>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-md-5">
                        <table style="width: 100%; font-size: 0.95rem;">
                            <!-- Subtotal -->
                            <tr>
                                <td style="padding: 10px 0; color: #5f6368;">Subtotal</td>
                                <td class="text-end" style="padding: 10px 0; font-weight: 600; color: #202124;">{{ number_format($subTotal, 2) }}</td>
                            </tr>

                            <!-- Discount (Before Tax) -->
                            @if(!empty($discount) && $invoice->discount_type == 'before-tax')
                            <tr>
                                <td style="padding: 5px 0; color: #d73a49;">Discount</td>
                                <td class="text-end" style="padding: 5px 0; font-weight: 600; color: #d73a49;">-{{ number_format($discount, 2) }}</td>
                            </tr>
                            @endif

                            <!-- Taxes -->
                            @if(!empty($totalCgst))
                            <tr>
                                <td style="padding: 5px 0; color: #5f6368; font-size: 0.85rem;">Total CGST</td>
                                <td class="text-end" style="padding: 5px 0; color: #5f6368; font-size: 0.85rem;">+{{ number_format($totalCgst, 2) }}</td>
                            </tr>
                            @endif
                            @if(!empty($totalSgst))
                            <tr>
                                <td style="padding: 5px 0; color: #5f6368; font-size: 0.85rem;">Total SGST</td>
                                <td class="text-end" style="padding: 5px 0; color: #5f6368; font-size: 0.85rem;">+{{ number_format($totalSgst, 2) }}</td>
                            </tr>
                            @endif
                            @if(!empty($totalIgst))
                            <tr>
                                <td style="padding: 5px 0; color: #5f6368; font-size: 0.85rem;">Total IGST</td>
                                <td class="text-end" style="padding: 5px 0; color: #5f6368; font-size: 0.85rem;">+{{ number_format($totalIgst, 2) }}</td>
                            </tr>
                            @endif
                            @if(!empty($totalVat))
                            <tr>
                                <td style="padding: 5px 0; color: #5f6368; font-size: 0.85rem;">Total VAT</td>
                                <td class="text-end" style="padding: 5px 0; color: #5f6368; font-size: 0.85rem;">+{{ number_format($totalVat, 2) }}</td>
                            </tr>
                            @endif

                            <!-- Discount (After Tax) -->
                            @if(!empty($discount) && $invoice->discount_type == 'after-tax')
                            <tr>
                                <td style="padding: 5px 0; color: #d73a49;">Discount</td>
                                <td class="text-end" style="padding: 5px 0; font-weight: 600; color: #d73a49;">-{{ number_format($discount, 2) }}</td>
                            </tr>
                            @endif

                            <!-- Adjustment -->
                            @if(isset($adjustment) && $adjustment > 0)
                            <tr>
                                <td style="padding: 5px 0; color: #d73a49;">Advance/Adj.</td>
                                <td class="text-end" style="padding: 5px 0; font-weight: 600; color: #d73a49;">-{{ number_format($adjustment, 2) }}</td>
                            </tr>
                            @endif

                            <!-- Grand Total -->
                            <tr>
                                <td colspan="2" style="padding: 10px 0;">
                                    <div style="background: rgba(0, 102, 102, 0.05); border-radius: 8px; border: 1px solid rgba(0, 102, 102, 0.1); padding: 15px; margin-top: 5px; display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-weight: 700; color: #006666; font-size: 1.1rem; text-transform: uppercase;">Total</span>
                                        <span style="font-weight: 800; color: #006666; font-size: 1.4rem;">
                                            @if(!empty($invoice->currency) && $invoice->currency != 'INR')
                                                <small style="font-size: 0.9rem; font-weight: 600;">{{ $invoice->currency }}</small> 
                                            @else
                                                ₹
                                            @endif
                                            {{ number_format($grandTotal, 2) }}
                                        </span>
                                    </div>
                                </td>
                            </tr>

                            <!-- Payments / Balance -->
                            @if(isset($amountPaid) && $amountPaid > 0)
                            <tr>
                                <td style="padding: 10px 0 5px 0; color: #28a745; font-weight: 600;">Amount Paid</td>
                                <td class="text-end" style="padding: 10px 0 5px 0; color: #28a745; font-weight: 700;">{{ number_format($amountPaid, 2) }}</td>
                            </tr>
                            @endif
                            @if(isset($balanceDue) && $balanceDue > 0 && isset($amountPaid) && $amountPaid > 0)
                            <tr>
                                <td style="padding: 5px 0; color: #dc3545; font-weight: 600;">Balance Due</td>
                                <td class="text-end" style="padding: 5px 0; color: #dc3545; font-weight: 700;">{{ number_format($balanceDue, 2) }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    
                    <div class="col-12 mt-4 text-center">
                        <div style="background: #f8f9fa; padding: 12px; border-radius: 6px; display: inline-block; border: 1px dashed #ced4da;">
                            <span style="color: #5f6368; font-size: 0.85rem; text-transform: uppercase; font-weight: 600; margin-right: 15px;">Amount in Words</span>
                            <span style="color: #202124; font-weight: 700; font-size: 0.9rem;">{{ ucwords(amountToWords($grandTotal ?? 0)) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Admin Note --}}
                @if(!empty($invoice->admin_note))
                    <div style="margin-top: 40px; padding: 15px; border-radius: 8px; border: 1px dashed #ffc107; background: #fff8e1; font-size: 0.85rem;">
                        <strong style="color: #d39e00;"><i class="bx bx-lock-alt"></i> Internal Admin Note (Not shown on PDF):</strong><br>
                        <span style="color: #5f6368;">{!! nl2br(e($invoice->admin_note)) !!}</span>
                    </div>
                @endif
                
                {{-- FOOTER MESSAGE --}}
                <div class="text-center" style="margin-top: 60px; border-top: 1px solid #f1f3f4; padding-top: 20px;">
                    <p style="color: #9aa0a6; font-size: 0.9rem; font-weight: 500;">
                        Thank you for your business!
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

