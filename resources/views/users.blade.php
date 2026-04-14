@extends('layout')
@section('title','Users - eseCRM')

@section('content')
    @php
    
        $roles = session('roles');
        $roleArray = explode(',',($roles->permissions ?? ''));
    
    @endphp
    <section class="task__section">
        @include('inc.header', ['title' => 'Users'])
        <div class="container-fluid">
            <div class="board-title board-title-flex">
                <h1>List Board</h1>
                @if(in_array('users_add',$roleArray) || in_array('All',$roleArray))
                <div class="btn-group">
                    <a href="/manage-user" class="btn btn-indigo rounded-pill btn-sm"><i class="bx bx-plus"></i> <span>Add New</span></a>
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
                                <td width="50px">
                                    <div class="form-check form-switch p-0 d-flex justify-content-center">
                                        <input class="form-check-input status-toggle" type="checkbox" role="switch" 
                                               id="status_{{ $user->id }}" 
                                               data-id="{{ $user->id }}"
                                               style="cursor: pointer; width: 34px; height: 18px;"
                                               {{ $user->status == '1' ? 'checked' : '' }}
                                               {{ Auth::id() == $user->id ? 'disabled' : '' }}>
                                    </div>
                                    <span class="small text-muted d-block text-center" style="font-size: 0.65rem;">
                                        {{ $user->status == '1' ? 'Active' : 'Deactive' }}
                                    </span>
                                </td>
                                @if(in_array('users_edit',$roleArray) || in_array('users_delete',$roleArray) || in_array('All',$roleArray))
                                <td width="50px" class="position-sticky end-0">
                                    <div class="table-btn d-flex align-items-center gap-2">
                                        @if(in_array('users_edit',$roleArray) || in_array('All',$roleArray))
                                        <a href="/manage-user?id={{ $user->id }}" class="btn btn-outline-info btn-sm rounded-circle shadow-sm" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;" title="Edit"><i class="bx bx-edit"></i></a>
                                        @endif
                                        @if(in_array('users_delete',$roleArray) || in_array('All',$roleArray))
                                        <a href="javascript:void(0)" class="btn btn-outline-danger btn-sm delete rounded-circle shadow-sm" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;" id="{{ $user->id }}" date-page="userDelete" title="Delete"><i class="bx bx-trash"></i></a>
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

@push('scripts')
<script>
$(document).ready(function() {
    $('.status-toggle').on('change', function() {
        const userId = $(this).data('id');
        const isChecked = $(this).is(':checked');
        const newStatus = isChecked ? 1 : 2;
        const $label = $(this).closest('td').find('span');

        // Show loading state if needed
        $(this).prop('disabled', true);

        $.ajax({
            url: "{{ route('users.toggle_status') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: userId,
                status: newStatus
            },
            success: function(response) {
                if (response.success) {
                    $label.text(newStatus == 1 ? 'Active' : 'Deactive');
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: response.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    // Revert on failure
                    $('#status_' + userId).prop('checked', !isChecked);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                // Revert on error
                $('#status_' + userId).prop('checked', !isChecked);
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred while updating status.';
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: msg
                });
            },
            complete: function() {
                $('#status_' + userId).prop('disabled', false);
            }
        });
    });
});
</script>
@endpush
