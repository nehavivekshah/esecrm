@extends('layout')
@section('title','Licenses - eseCRM')

@section('content')
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i> 
            Licenses
            <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>
        <div class="container-fluid">
            <div class="board-title board-title-flex">
                <h1>License Board</h1>
                <div class="btn-group">
                    <a href="/manage-license" class="btn btn-primary bg-primary text-white btn-sm"><i class="bx bx-plus"></i> <span>Add New</span></a>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 py-3 table-responsive">
                    <table id="lists" class="table table-condensed m-table leads" style="width:100%;border-radius: 5px!important;overflow: hidden;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Deployment URL</th>
                                <th>License Key</th>
                                <th class="m-none" width="150px">Expiry Date</th>
                                <th class="m-none" width="60px">Database</th>
                                <!--<th class="m-none" width="60px">Status</th>-->
                                <th class="position-sticky end-0" width="60px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($licenses as $k=>$license)
                            <tr>
                                <td>{{ $k+1 }}</td>
                                <td>{{ $license->client_name ?? '' }} - {{ $license->name ?? '' }}</td>
                                <td>{{ $license->deployment_url ?? '' }}</td>
                                <td>{{ $license->eselicense_key ?? '' }}</td>
                                <td class="m-none" width="150px">{{ $license->expiry_date ?? 'N/A' }}</td>
                                <td class="m-none" width="60px">
                                    <a href="javascript:void(0)" class="badge bg-dark dbbackup" data-domain="{{ $license->deployment_url ?? '' }}" data-key="{{ $license->eselicense_key ?? '' }}" title="Download"><i class="bx bx-download"></i></a>
                                    <a href="javascript:void(0)" class="badge bg-danger dbdelete" data-domain="{{ $license->deployment_url ?? '' }}" data-key="{{ $license->eselicense_key ?? '' }}" title="Delete"><i class="bx bx-trash"></i></a>
                                </td>
                                <!--<td width="60px">
                                    @if($license->status == 'active')
                                    <a href="javascript:void(0)" class="badge bg-success accountstatus" id="{{ $license->id }}" data-page="licenseDeactivate">Active</a>
                                    @else
                                    <a href="javascript:void(0)" class="badge bg-danger accountstatus" id="{{ $license->id }}" data-page="licenseActivate">Deactive</a>
                                    @endif
                                </td>-->
                                <td width="60px" class="position-sticky end-0">
                                    <div class="table-btn">
                                        <a href="/manage-license?id={{ $license->id }}" class="btn btn-info btn-sm" title="Edit"><i class="bx bx-edit"></i></a>
                                        <!--<a href="javascript:void(0)" class="btn btn-danger btn-sm delete" id="{{ $license->id }}" data-page="licenseDelete" title="Delete"><i class="bx bx-trash"></i></a>-->
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

