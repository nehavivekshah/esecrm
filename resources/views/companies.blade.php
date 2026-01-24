@extends('layout')
@section('title','Companies - eseCRM')

@section('content')
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i> 
            Companies
            <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>
        <div class="container-fluid">
            <div class="board-title board-title-flex">
                <h1>List Board</h1>
                <div class="btn-group">
                    <!--<a href="javascript:void(0)" class="btn btn-warning btn-sm" id="importFile"><i class="bx bx-upload"></i> <span>Import</span></a>
                    <a href="/public/assets/leads.csv" class="btn btn-danger btn-sm" target="_blank" download="leads.csv" title="Download CSV Sample File"><i class="bx bx-download"></i> <span>Sample File</span></a>-->
                    <a href="/manage-company" class="btn btn-primary btn-sm"><i class="bx bx-plus"></i> <span>Add New</span></a>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 py-3 table-responsive">
                    <table id="lists" class="table table-condensed m-table leads" style="width:100%;border-radius: 5px!important;overflow: hidden;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th class="m-none">Email ids</th>
                                <th class="m-none" width="80px">Mobile No.</th>
                                <th class="m-none" width="80px">GST No.</th>
                                <th class="m-none" width="60px">Status</th>
                                <th class="position-sticky end-0" width="60px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($companies as $k=>$company)
                            <!-- Add rows with your data here -->
                            <tr>
                                <td>{{ $k+1 }}</td>
                                <td>{{ $company->name ?? '' }}<span class="small d-none">{{ $company->company ?? '' }}</span></td>
                                <td class="m-none">{{ $company->email ?? '' }}</td>
                                <td class="m-none" width="80px">{{ $company->mob ?? '' }}</td>
                                <td class="m-none" width="80px">{{ $company->gst ?? '' }}</td>
                                <td width="50px">
                                    @if($company->status == '1')
                                    <a href="javascript:void(0)" class="badge bg-success accountstatus" id="{{ $company->id }}" data-page="companyDeactivate">Active</a>
                                    @else
                                    <a href="javascript:void(0)" class="badge bg-danger accountstatus" id="{{ $company->id }}" data-page="companyActivate">Deactive</a>
                                    @endif
                                </td>
                                <td width="50px" class="position-sticky end-0">
                                    <div class="table-btn">
                                        <a href="/manage-company?id={{ $company->id }}" class="btn btn-info btn-sm" title="Edit"><i class="bx bx-edit"></i></a>
                                        <a href="javascript:void(0)" class="btn btn-danger btn-sm delete" id="{{ $company->id }}" date-page="companyDelete" title="Delete"><i class="bx bx-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            <!-- Repeat rows as needed -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection