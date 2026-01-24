@extends('layout')
@section('title','Proposals - eseCRM')

@section('content')
    @php
        // Retrieve role permissions from session
        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));
    @endphp
    <style>
        @media (max-width: 768px) {
            .actionWidth {
                max-width: 83px !important;
            }
            .bx{
                font-size: 16px;
            }
        }
    </style>
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i> 
            Proposals
            <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>
        <div class="container-fluid">
            <div class="board-title board-title-flex">
                <h1>List Board</h1>
                @if(in_array('proposals_add', $roleArray) || in_array('All', $roleArray))
                    <div class="btn-group">
                        <a href="/manage-proposal" class="btn btn-primary bg-primary text-white btn-sm">
                            <i class="bx bx-plus"></i> 
                            <span>New Proposal</span>
                        </a>
                    </div>
                @endif
            </div>
            <div class="row">
                <div class="col-md-12 py-3 table-responsive">
                    <table id="lists" class="table table-condensed m-table" style="width:100%;border-radius: 5px!important;overflow: hidden;">
                        <thead>
                            <tr>
                                <th width="100px" class="m-none">#</th>
                                <th>Subject</th>
                                <th class="m-none">Client Name</th>
                                <th>Company</th>
                                <th>Amount</th>
                                <th width="100px" class="m-none">Created Date</th>
                                <th width="100px" class="m-none">Open Till</th>
                                <th class="m-none">Projects</th>
                                <th>Status</th>
                                <th width="100px" class="m-none">Tags</th>
                                @if(in_array('proposals_edit',$roleArray) || in_array('proposals_delete',$roleArray) || in_array('All',$roleArray))
                                <th class="actionWidth position-sticky end-0">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="Proposals">
                            @foreach($proposals as $k=>$proposal)
                            <tr>
                                <td width="100px" class="m-none">PRO-{{ str_pad($proposal->id, 6, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $proposal->subject ?? '' }}</td>
                                <td>{{ $proposal->client_name ?? '' }}</td>
                                <td class="m-none">{!! substr(($proposal->company ?? ''),0,15) !!}..</td>
                                <td>{{ $proposal->grand_total ?? '' }} {{ $proposal->currency ?? '' }}</td>
                                <td width="100px" class="m-none">{!! date_format(date_create($proposal->proposal_date ?? null),'d M, Y') !!}</td>
                                <td width="100px" class="m-none">{!! date_format(date_create($proposal->open_till ?? null),'d M, Y') !!}</td>
                                <td class="m-none" class="m-none">{{ $proposal->project_name ?? '' }}</td>
                                <td>
                                    @if($proposal->status == 'Sent')<span class="badge bg-success">Sent</span>
                                    @elseif($proposal->status == 'Accepted')<span class="badge bg-primary">Accepted</span>
                                    @elseif($proposal->status == 'Declined')<span class="badge bg-danger">Declined</span>
                                    @elseif($proposal->status == 'Expired')<span class="badge bg-danger">Expired</span>
                                    @else<span class="badge bg-dark">Draft</span>@endif
                                </td>
                                <td width="100px" class="m-none">{{ $proposal->tags ?? '' }}</td>
                                @if(in_array('proposals_edit',$roleArray) || in_array('proposals_delete',$roleArray) || in_array('All',$roleArray))
                                <td class="actionWidth position-sticky end-0">
                                    <div class="table-btn">
                                        <a href="/quotation/{{ $proposal->id }}/{{ md5($proposal->client_email) }}" class="btn btn-primary bg-primary text-white btn-sm" title="View" target="_blank"><i class="bx bx-show"></i></a>
                                        @if(in_array('proposals_edit',$roleArray) || in_array('All',$roleArray))
                                        <a href="/manage-proposal?id={{ $proposal->id }}" class="btn btn-info btn-sm" title="Edit"><i class="bx bx-edit"></i></a>
                                        @endif
                                        @if(in_array('proposals_delete',$roleArray) || in_array('All',$roleArray))
                                        <a href="javascript:void(0)" class="btn btn-danger btn-sm delete" id="{{ $proposal->id }}" date-page="proposalDelete" title="Delete"><i class="bx bx-trash"></i></a>
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