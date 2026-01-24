@extends('layout')
@section('title','Invoices - eseCRM')

@section('content')
    @php
        // Retrieve role permissions from session
        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));
    @endphp

    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i> 
            Invoices
            <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>
        <div class="container-fluid">
            <div class="board-title board-title-flex">
                <h1>Invoice Board</h1>
                @if(in_array('invoice_add', $roleArray) || in_array('All', $roleArray))
                    <div class="btn-group">
                        <a href="/manage-invoice" class="btn btn-primary bg-primary text-white btn-sm">
                            <i class="bx bx-plus"></i> 
                            <span>Create New Invoice</span>
                        </a>
                    </div>
                @endif
            </div>
            <div class="row">
                <div class="col-md-12 py-3 table-responsive">
                    <table id="lists" class="table table-condensed m-table" style="width:100%;border-radius: 5px!important;overflow: hidden;">
                        <thead>
                            <tr>
                                <th class="m-none">#</th>
                                <th class="m-none">Invoice #</th>
                                <th>Client</th>
                                <th>Company</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th class="m-none">Date</th>
                                <th class="m-none">Due Date</th>
                                <!--<th>Amount</th>
                                <th>Total Tax</th>-->
                                <th width="50px">Status</th>
                                <th width="50px" class="position-sticky end-0">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $k=>$invoice)
                                <tr>
                                    <td>{{ $k+1 }}</td>
                                    <td class="m-none">INV-{{ $invoice->invoice_number }}</td>
                                    <td>{{ $invoice->client_name }}</td>
                                    <td>{{ $invoice->client_company }}</td>
                                    <td>Rs. {{ $invoice->total_amount ?? 0.00 }}</td>
                                    <td>{{ $invoice->invoice }}</td>
                                    <td class="m-none">{!! date_format(date_create($invoice->date),'d M, Y') !!}</td>
                                    <td class="m-none">{!! date_format(date_create($invoice->due_date),'d M, Y') !!}</td>
                                    <!--<td>{{ $invoice->total_amount }}</td>-->
                                    <td>
                                        @if($invoice->status == 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($invoice->status == 'unpaid')
                                            <span class="badge bg-danger">Unpaid</span>
                                        @else
                                            <span class="badge bg-warning">{{ ucfirst($invoice->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="position-sticky end-0">
                                        <div class="table-btn">
                                            <a href="/invoices/pdf/preview/{{ $invoice->id }}" 
                                               class="btn btn-primary bg-primary text-white btn-sm" 
                                               title="View"
                                               target="_blank">
                                                <i class="bx bx-file"></i>
                                            </a>
                                            @if(in_array('invoice_edit', $roleArray) || in_array('All', $roleArray))
                                                <a href="/manage-invoice?id={{ $invoice->id }}" 
                                                   class="btn btn-info btn-sm" 
                                                   title="Edit">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                            @endif

                                            @if(in_array('invoice_delete', $roleArray) || in_array('All', $roleArray))
                                                <a href="javascript:void(0)" 
                                                   class="btn btn-danger btn-sm delete" 
                                                   data-id="{{ $invoice->id }}" 
                                                   data-page="invoiceDelete" 
                                                   title="Delete">
                                                    <i class="bx bx-trash"></i>
                                                </a>
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
    </section>
@endsection
