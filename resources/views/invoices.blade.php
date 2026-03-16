@extends('layout')
@section('title','Invoices - eseCRM')

@section('content')
    @php
        // Retrieve role permissions from session
        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));
    @endphp

    <section class="task__section">
        @include('inc.header', ['title' => 'Invoices'])
        
        <div class="dash-container">
            {{-- ── Page heading bar ── --}}
            <div class="leads-toolbar mb-4">
                <div class="leads-toolbar-left gap-3">
                    <div class="leads-toolbar-item">
                        <span class="lb-page-count"><i class="bx bx-file"></i> Invoices</span>
                        <span class="ms-2 badge bg-light text-dark border">{{ count($invoices) }} Total</span>
                    </div>
                </div>
                <div class="leads-toolbar-right">
                    @if(in_array('invoice_add', $roleArray) || in_array('All', $roleArray))
                        <a href="/manage-invoice" class="lb-btn lb-btn-primary">
                            <i class="bx bx-plus"></i> Create New Invoice
                        </a>
                    @endif
                </div>
            </div>

            {{-- ── Quick Stats ── --}}
            <div class="row g-3 mb-4">
                @php
                    $totalAmt = $invoices->sum('total_amount');
                    $paidCount = $invoices->where('status', 'paid')->count();
                    $unpaidCount = $invoices->where('status', 'unpaid')->count();
                @endphp
                <div class="col-md-3">
                    <div class="ml-card p-3 d-flex align-items-center gap-3">
                        <div class="ml-card-icon" style="background:rgba(0,102,102,0.1);color:#006666;width:40px;height:40px;">
                            <i class="bx bx-wallet"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Total Amount</div>
                            <div class="h5 mb-0 fw-bold">₹{{ number_format($totalAmt, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="ml-card p-3 d-flex align-items-center gap-3">
                        <div class="ml-card-icon" style="background:rgba(52,168,83,0.1);color:#34a853;width:40px;height:40px;">
                            <i class="bx bx-check-circle"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Paid Invoices</div>
                            <div class="h5 mb-0 fw-bold">{{ $paidCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="ml-card p-3 d-flex align-items-center gap-3">
                        <div class="ml-card-icon" style="background:rgba(234,67,53,0.1);color:#ea4335;width:40px;height:40px;">
                            <i class="bx bx-error-circle"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Unpaid Invoices</div>
                            <div class="h5 mb-0 fw-bold">{{ $unpaidCount }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="ml-card">
                        <div class="ml-card-body p-0 table-responsive">
                            <table id="lists" class="table m-table mb-0" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Invoice #</th>
                                        <th>Client & Company</th>
                                        <th>Amount</th>
                                        <th>Dates</th>
                                        <th>Status</th>
                                        <th width="120px" class="pe-4 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $k=>$invoice)
                                        <tr>
                                            <td class="ps-4 align-middle">{{ $k+1 }}</td>
                                            <td class="align-middle fw-bold text-primary">INV-{{ $invoice->invoice_number }}</td>
                                            <td class="align-middle">
                                                <div class="fw-bold">{{ $invoice->client_name }}</div>
                                                <div class="text-muted small">{{ $invoice->client_company }}</div>
                                            </td>
                                            <td class="align-middle fw-bold">₹{{ number_format($invoice->total_amount, 2) }}</td>
                                            <td class="align-middle">
                                                <div class="small"><span class="text-muted">Inv:</span> {!! date_format(date_create($invoice->date),'d M, Y') !!}</div>
                                                <div class="small"><span class="text-muted">Due:</span> {!! date_format(date_create($invoice->due_date),'d M, Y') !!}</div>
                                            </td>
                                            <td class="align-middle">
                                                @if($invoice->status == 'paid')
                                                    <span class="badge" style="background:rgba(52,168,83,0.1);color:#34a853;border:1px solid rgba(52,168,83,0.2);">Paid</span>
                                                @elseif($invoice->status == 'unpaid')
                                                    <span class="badge" style="background:rgba(234,67,53,0.1);color:#ea4335;border:1px solid rgba(234,67,53,0.2);">Unpaid</span>
                                                @else
                                                    <span class="badge" style="background:rgba(251,188,5,0.1);color:#fbbc05;border:1px solid rgba(251,188,5,0.2);">{{ ucfirst($invoice->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="pe-4 align-middle">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <a href="/invoices/pdf/preview/{{ $invoice->id }}" 
                                                       class="kb-action-btn" title="View PDF" target="_blank"
                                                       style="background:rgba(66,133,244,0.1);color:#4285f4;">
                                                        <i class="bx bx-file"></i>
                                                    </a>
                                                    @if(in_array('invoice_edit', $roleArray) || in_array('All', $roleArray))
                                                        <a href="/manage-invoice?id={{ $invoice->id }}" 
                                                           class="kb-action-btn" title="Edit"
                                                           style="background:rgba(52,168,83,0.1);color:#34a853;">
                                                            <i class="bx bx-edit"></i>
                                                        </a>
                                                    @endif
                                                    
                                                    <a href="mailto:{{ $invoice->client_email ?? '' }}?subject=Invoice INV-{{ $invoice->invoice_number }}&body=Please find attached invoice." 
                                                       class="kb-action-btn" title="Send Email"
                                                       style="background:rgba(251,188,5,0.1);color:#fbbc05;">
                                                        <i class="bx bx-envelope"></i>
                                                    </a>

                                                    @if(in_array('invoice_delete', $roleArray) || in_array('All', $roleArray))
                                                        <button type="button" 
                                                           class="kb-action-btn kb-action-del delete"
                                                           data-id="{{ $invoice->id }}" 
                                                           data-page="invoiceDelete" 
                                                           title="Delete"
                                                           style="background:rgba(234,67,53,0.1);color:#ea4335;">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Client Filter Logic & Send Action -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // 1. Add Filter Dropdown
            const table = $('#lists').DataTable();
            
            // Create filter container
            const filterContainer = $('<div class="d-flex gap-2 mb-3"></div>').insertBefore('#lists_wrapper');
            
            // Client Filter
            const clientSelect = $('<select class="form-select form-select-sm" style="width: 200px;"><option value="">All Clients</option></select>')
                .appendTo(filterContainer)
                .on('change', function () {
                    const val = $.fn.dataTable.util.escapeRegex($(this).val());
                    table.column(2).search(val ? '^' + val + '$' : '', true, false).draw();
                });
 
            // Populate Client Filter
            table.column(2).data().unique().sort().each(function (d, j) {
                // Strip HTML if present (though client name usually plain text)
                const cleanData = $('<div>').html(d).text(); 
                if(cleanData) clientSelect.append('<option value="' + cleanData + '">' + cleanData + '</option>');
            });

            // 2. Add Send Logic (Placeholder)
            /*$('.send-invoice-btn').click(function(e) {
                e.preventDefault();
                alert('Send Invoice functionality to be implemented.');
            });*/
        });
    </script>
