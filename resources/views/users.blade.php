@extends('layout')
@section('title','Users - eseCRM')

@section('content')
    @php
    
        $roles = session('roles');
        $roleArray = explode(',',($roles->permissions ?? ''));
    
    @endphp
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i> 
            All Users
            <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>
        <div class="container-fluid">
            <div class="board-title board-title-flex">
                <h1>List Board</h1>
                @if(in_array('users_add',$roleArray) || in_array('All',$roleArray))
                <div class="btn-group">
                    <a href="/manage-user" class="btn btn-primary btn-sm"><i class="bx bx-plus"></i> <span>Add New</span></a>
                </div>
                @endif
            </div>
            <div class="row">
                <div class="col-md-12 py-3 table-responsive">
                    <table id="lists" class="table table-condensed m-table" style="width:100%;border-radius: 5px!important;overflow: hidden;">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th class="m-none">Email Id</th>
                                <th class="m-none">Mobile No.</th>
                                <th class="m-none">Role</th>
                                <th width="50px">Status</th>
                                @if(in_array('users_edit',$roleArray) || in_array('users_delete',$roleArray) || in_array('All',$roleArray))
                                <th width="50px" class="position-sticky end-0">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>{{$user->name ?? '--'}}<span class="small d-none">{{$user->mob ?? '--'}}</span></td>
                                <td class="m-none">{{$user->email ?? '--'}}</td>
                                <td class="m-none">{{$user->mob ?? '--'}}</td>
                                <td class="m-none">{{$user->title ?? '--'}} - {{$user->subtitle ?? ''}}</td>
                                <td width="50px">@if($user->status == '1')<span class="badge bg-success">Active</span>@else<span class="badge bg-danger">Deactive</span>@endif</td>
                                @if(in_array('users_edit',$roleArray) || in_array('users_delete',$roleArray) || in_array('All',$roleArray))
                                <td width="50px" class="position-sticky end-0">
                                    <div class="table-btn">
                                        @if(in_array('users_edit',$roleArray) || in_array('All',$roleArray))
                                        <a href="/manage-user?id={{ $user->id }}" class="btn btn-info btn-sm" title="Edit"><i class="bx bx-edit"></i></a>
                                        @endif
                                        @if(in_array('users_delete',$roleArray) || in_array('All',$roleArray))
                                        <a href="javascript:void(0)" class="btn btn-danger btn-sm delete" id="{{ $user->id }}" date-page="userDelete" title="Delete"><i class="bx bx-trash"></i></a>
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