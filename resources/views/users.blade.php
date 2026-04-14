@extends('layout')
@section('title','Users Management - eseCRM')

@section('content')
    @php
        $roles = session('roles');
        $roleArray = explode(',',($roles->permissions ?? ''));
        
        $activeCount = $users->where('status', '1')->count();
        $deactiveCount = $users->where('status', '2')->count();
    @endphp

    <section class="task__section">
        @include('inc.header', ['title' => 'Users Management'])

        <div class="db-wrap">
            {{-- ═══════════════════════ USER HERO ═══════════════════════ --}}
            <div class="db-hero" style="background: linear-gradient(135deg, #004d4d 0%, #006666 100%);">
                <div class="db-hero-left">
                    <div class="db-hero-greeting">Team Management</div>
                    <div class="db-hero-sub">Manage your organization's staff, roles, and system access levels.</div>
                    
                    <div class="db-hero-pills mt-3">
                        <span class="db-pill db-pill-green"><i class="bx bx-check-circle"></i> {{ $activeCount }} Active</span>
                        <span class="db-pill db-pill-red"><i class="bx bx-x-circle"></i> {{ $deactiveCount }} Deactive</span>
                        <span class="db-pill db-pill-blue"><i class="bx bx-group"></i> {{ count($users) }} Total Staff</span>
                    </div>
                </div>
                <div class="db-hero-right">
                    @if(in_array('users_add',$roleArray) || in_array('All',$roleArray))
                        <a href="/manage-user" class="btn btn-light rounded-pill px-4" style="color:#006666; font-weight:700; border:none; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                            <i class="bx bx-plus-circle me-1"></i> Add New User
                        </a>
                    @endif
                </div>
            </div>

            {{-- ═══════════════════════ USER LIST CARD ═══════════════════════ --}}
            <div class="db-card shadow-sm border-0" style="border-radius: 20px;">
                <div class="db-card-head d-flex align-items-center justify-content-between p-4">
                    <div class="d-flex align-items-center gap-3">
                        <span class="db-card-icon" style="color:#006666; background:rgba(0,102,102,.08); width:40px; height:40px; border-radius:12px;">
                            <i class="bx bx-list-ul" style="font-size: 1.3rem;"></i>
                        </span>
                        <div>
                            <span class="db-card-title d-block" style="font-size: 1.1rem; line-height: 1.2;">Staff Directory</span>
                            <span class="db-card-sub text-muted" style="font-size: 0.75rem;">Showing all registered system users</span>
                        </div>
                    </div>
                </div>

                <div class="p-4 pt-0">
                    <div class="table-responsive">
                        <table id="lists" class="table table-hover align-middle custom-table" style="width:100%;">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 ps-4 py-3" style="font-size: 0.8rem; font-weight: 700; color: #5f6368; text-transform: uppercase; letter-spacing: 0.5px;">Staff Member</th>
                                    <th class="border-0 m-none py-3" style="font-size: 0.8rem; font-weight: 700; color: #5f6368; text-transform: uppercase;">Contact Info</th>
                                    <th class="border-0 m-none py-3" style="font-size: 0.8rem; font-weight: 700; color: #5f6368; text-transform: uppercase;">Role & Permissions</th>
                                    <th class="border-0 text-center py-3" style="font-size: 0.8rem; font-weight: 700; color: #5f6368; text-transform: uppercase;" width="120px">Access</th>
                                    @if(in_array('users_edit',$roleArray) || in_array('users_delete',$roleArray) || in_array('All',$roleArray))
                                    <th class="border-0 text-end pe-4 py-3" style="font-size: 0.8rem; font-weight: 700; color: #5f6368; text-transform: uppercase;" width="120px">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr style="transition: all 0.2s ease;">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-circle" style="width: 40px; height: 40px; background: #f0f4f4; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #006666; font-weight: 700; font-size: 0.9rem;">
                                                @if(!empty($user->photo))
                                                    <img src="{{ asset('assets/images/profile/' . $user->photo) }}" style="width: 100%; height: 100%; border-radius: 12px; object-fit: cover;">
                                                @else
                                                    {{ strtoupper(substr($user->name ?? 'S', 0, 1)) }}
                                                @endif
                                            </div>
                                            <div>
                                                <div style="font-weight: 700; color: #202124; font-size: 0.9rem;">{{$user->name ?? '--'}}</div>
                                                <div class="text-muted" style="font-size: 0.75rem;">ID: #USR-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="m-none">
                                        <div class="d-flex flex-column">
                                            <span style="font-size: 0.85rem; color: #495057;"><i class="bx bx-envelope me-1 text-muted"></i> {{$user->email ?? '--'}}</span>
                                            <span style="font-size: 0.85rem; color: #495057;"><i class="bx bx-phone me-1 text-muted"></i> {{$user->mob ?? '--'}}</span>
                                        </div>
                                    </td>
                                    <td class="m-none">
                                        <div class="badge-role" style="display: inline-flex; align-items: center; gap: 5px; background: #eef2ff; color: #4f46e5; padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; border: 1px solid #e0e7ff;">
                                            <i class="bx bx-shield-quarter"></i> {{ $user->title ?? 'Staff' }}
                                        </div>
                                        <div class="text-muted mt-1" style="font-size: 0.7rem;">{{ $user->subtitle ?? 'Generic Access' }}</div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch p-0 d-flex justify-content-center flex-column align-items-center gap-1">
                                            <input class="form-check-input status-toggle" type="checkbox" role="switch" 
                                                   id="status_{{ $user->id }}" 
                                                   data-id="{{ $user->id }}"
                                                   style="cursor: pointer; width: 34px; height: 18px;"
                                                   {{ $user->status == '1' ? 'checked' : '' }}
                                                   {{ Auth::id() == $user->id ? 'disabled' : '' }}>
                                            <span class="small text-muted" style="font-size: 0.65rem; font-weight: 600;">
                                                {{ $user->status == '1' ? 'ACTIVE' : 'DISABLED' }}
                                            </span>
                                        </div>
                                    </td>
                                    @if(in_array('users_edit',$roleArray) || in_array('users_delete',$roleArray) || in_array('All',$roleArray))
                                    <td class="text-end pe-4">
                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                            @if(in_array('users_edit',$roleArray) || in_array('All',$roleArray))
                                            <a href="/manage-user?id={{ $user->id }}" class="btn btn-icon-premium edit" title="Edit Profile">
                                                <i class="bx bx-edit-alt"></i>
                                            </a>
                                            @endif
                                            @if(in_array('users_delete',$roleArray) || in_array('All',$roleArray))
                                            <a href="javascript:void(0)" class="btn btn-icon-premium delete" id="{{ $user->id }}" date-page="userDelete" title="Delete User">
                                                <i class="bx bx-trash-alt"></i>
                                            </a>
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
        </div>
    </section>

    <style>
        .custom-table thead th { border-bottom: 2px solid #f8f9fa; }
        .custom-table tbody tr:hover { background-color: #fcfdfe; transform: scale(1.002); }
        
        .btn-icon-premium {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            border: 1px solid #eef2f3;
            background: #fff;
            color: #5f6368;
            padding: 0;
        }
        
        .btn-icon-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        
        .btn-icon-premium.edit:hover { background: #eef9f9; color: #006666; border-color: #cdeaea; }
        .btn-icon-premium.delete:hover { background: #fff5f5; color: #ea4335; border-color: #fcd9d9; }

        .form-check-input:checked {
            background-color: #006666;
            border-color: #006666;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .db-wrap { padding: 15px; }
            .db-hero { padding: 20px; flex-direction: column; text-align: center; gap: 20px; }
            .db-hero-right { text-align: center; }
            .m-none { display: none !important; }
        }
    </style>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.status-toggle').on('change', function() {
        const userId = $(this).data('id');
        const isChecked = $(this).is(':checked');
        const newStatus = isChecked ? 1 : 2;
        const $label = $(this).closest('td').find('span');

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
                    $label.text(newStatus == 1 ? 'ACTIVE' : 'DISABLED');
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                } else {
                    $('#status_' + userId).prop('checked', !isChecked);
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            },
            error: function(xhr) {
                $('#status_' + userId).prop('checked', !isChecked);
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred while updating status.';
                Swal.fire({ icon: 'error', title: 'Oops...', text: msg });
            },
            complete: function() {
                $('#status_' + userId).prop('disabled', false);
            }
        });
    });
});
</script>
@endpush
