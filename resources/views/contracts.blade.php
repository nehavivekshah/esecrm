@extends('layout')
@section('title','Contracts - eseCRM')

@section('content')
    @php
        use Carbon\Carbon;
        // Retrieve role permissions from session
        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));
    @endphp

    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i> 
            Contracts
            <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>
        <div class="container-fluid">
            <div class="board-title board-title-flex">
                <h1>List Board</h1>
                @if(in_array('contracts_add', $roleArray) || in_array('All', $roleArray))
                    <div class="btn-group">
                        <a href="/manage-contract" class="btn btn-primary bg-primary text-white btn-sm">
                            <i class="bx bx-plus"></i> 
                            <span>New Contract</span>
                        </a>
                    </div>
                @endif
            </div>
            <div class="row">
                <div class="col-md-12 py-3 table-responsive">
                    <table id="lists" class="table table-condensed m-table" style="width:100%;border-radius: 5px!important;overflow: hidden;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Subject</th>
                                <th>Customers</th>
                                <th>Contract Type</th>
                                <th>Amount</th>
                                <th width="100px">Start Date</th>
                                <th width="100px">End Date</th>
                                @if(in_array('contracts_edit',$roleArray) || in_array('contracts_delete',$roleArray) || in_array('All',$roleArray))
                                <th class="actionWidth position-sticky end-0">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="contracts">
                            @foreach($contracts as $k => $contract)
                                @php
                                    $endDate = Carbon::parse($contract->end_date ?? null);
                                    $today = Carbon::today();
                                    $diffInDays = $today->diffInDays($endDate, false);
                                    $rowClass = '';

                                    if ($diffInDays < 0) {
                                        $rowClass = 'table-alert bg-alert'; // Expired
                                    } elseif ($diffInDays <= 7) {
                                        $rowClass = 'table-warning'; // Within 7 days
                                    } elseif ($diffInDays <= 15) {
                                        $rowClass = 'table-warning'; // Within 8–15 days
                                    }
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>{{ $k+1 }}</td>
                                    <td>{{ $contract->subject ?? '' }}</td>
                                    <td>{{ $contract->name ?? '' }}</td>
                                    <td>{{ $contract->contract_type ?? '' }}</td>
                                    <td>{{ $contract->value ?? '' }}</td>
                                    <td width="100px">{!! date_format(date_create($contract->start_date ?? null),'d M, Y') !!}</td>
                                    <td width="100px">{!! date_format(date_create($contract->end_date ?? null),'d M, Y') !!}</td>
                                    @if(in_array('contracts_edit',$roleArray) || in_array('contracts_delete',$roleArray) || in_array('All',$roleArray))
                                    <td class="actionWidth position-sticky end-0">
                                        <div class="table-btn">
                                            @if(in_array('contracts_edit',$roleArray) || in_array('All',$roleArray))
                                            <a href="/manage-contract?id={{ $contract->id }}" class="btn btn-info btn-sm" title="Edit"><i class="bx bx-edit"></i></a>
                                            @endif
                                            @if(in_array('contracts_delete',$roleArray) || in_array('All',$roleArray))
                                            <a href="javascript:void(0)" class="btn btn-danger btn-sm delete" id="{{ $contract->id }}" date-page="contractDelete" title="Delete"><i class="bx bx-trash"></i></a>
                                            @endif
                                        </div>    
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
