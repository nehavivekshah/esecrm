@extends('layout')
@section('title', 'User Profile - eseCRM')

@section('content')

    @php
        $sessionroles = session('roles');
        $roleArray = explode(',',($sessionroles->permissions ?? ''));
        $userAssign = explode(',', ($users->assign ?? ''));
        $userFeaturs = explode(',', ($users->features ?? ''));
        $workingTime = json_decode($users->working_times ?? '', true) ?? [];
    @endphp
    
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i> 
            @if(Request::segment(1) != 'my-profile') Manage User @else My Account @endif
            <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>

        <div class="container-fluid">
            {{-- Header with Back Button --}}
            <div class="board-title board-title-flex mb-3">
                @if(Request::segment(1) != 'my-profile')
                    <a href="/users" class="btn btn-light btn-sm back-btn rounded-pill border">
                        <i class="bx bx-arrow-back"></i>
                    </a>
                    <h1>@if(!empty($_GET['id'])) Edit User @else Add New User @endif</h1>
                @else
                    <div class="d-flex align-items-center gap-3">
                        <div class="profile-icon-bg" style="width:48px; height:48px; background:#006666; border-radius:12px; display:flex; align-items:center; justify-content:center; color:white; font-size:1.4rem;">
                            <i class="bx bx-user-circle"></i>
                        </div>
                        <div>
                            <h1 class="mb-0" style="font-size:1.4rem;">My Profile</h1>
                            <p class="text-muted mb-0" style="font-size:0.82rem;">Manage your personal information and preferences</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="row g-0">
                <div class="col-md-11 mx-auto">
                    {{-- Form Container --}}
                    <div class="dash-card p-4" style="background:#fff; border-radius:16px; border:1px solid #f0f0f0; box-shadow:0 10px 30px rgba(0,0,0,0.02);">
                        <form action="/manage-user" method="post" class="row g-3" enctype="multipart/form-data">
                            @csrf
                            
                            @if(Request::segment(1) == 'my-profile')
                                <input type="hidden" name="id" value="{{ Auth::id() }}">
                            @else
                                <input type="hidden" name="id" value="{{ $users->id ?? '' }}">
                            @endif

                            {{-- Left Column: Avatar & Signature --}}
                            <div class="col-md-3">
                                <div class="d-flex flex-column gap-3">
                                    {{-- Photo Preview --}}
                                    <div class="text-center p-3 border rounded-3" style="background:#f8f9fa;">
                                        <label class="form-label d-block mb-3" style="font-weight:600; color:#495057;">Profile Photo</label>
                                        <div class="mb-3 position-relative d-inline-block">
                                            <img src="{{ !empty($users->photo) ? asset('assets/images/profile/' . $users->photo) : 'https://ui-avatars.com/api/?name='.urlencode($users->name ?? 'User').'&background=006666&color=fff&size=200' }}" 
                                                 style="width:120px; height:120px; object-fit:cover; border-radius:50%; border:3px solid #fff; box-shadow:0 4px 12px rgba(0,0,0,0.1);" />
                                        </div>
                                        <input type="file" class="form-control form-control-sm" id="profilePhoto" name="profilePhoto">
                                    </div>

                                    {{-- Signature Preview --}}
                                    <div class="text-center p-3 border rounded-3" style="background:#f8f9fa;">
                                        <label class="form-label d-block mb-3" style="font-weight:600; color:#495057;">E-Signature</label>
                                        <div class="mb-3" style="height:60px; display:flex; align-items:center; justify-content:center; background:white; border-radius:8px;">
                                            @if(!empty($users->imgsign))
                                                <img src="{{ asset('assets/images/signs/' . $users->imgsign) }}" style="max-height:50px; object-fit:contain;" />
                                            @else
                                                <small class="text-muted">No signature uploaded</small>
                                            @endif
                                        </div>
                                        <input type="file" class="form-control form-control-sm" id="imgsign" name="imgsign">
                                    </div>
                                </div>
                            </div>

                            {{-- Right Column: Information --}}
                            <div class="col-md-9">
                                <div class="row g-3">
                                    <h6 class="mb-1 text-uppercase" style="font-size:0.75rem; letter-spacing:1px; color:#006666; font-weight:700;">Account Information</h6>
                                    
                                    <div class="col-md-6">
                                        <label for="name" class="form-label" style="font-weight:500; font-size:0.875rem; color:#495057;">Full Name*</label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="John Doe" value="{{ $users->name ?? '' }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email" class="form-label" style="font-weight:500; font-size:0.875rem; color:#495057;">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="email@example.com" value="{{ $users->email ?? '' }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="mob" class="form-label" style="font-weight:500; font-size:0.875rem; color:#495057;">Mobile Number</label>
                                        <input type="text" class="form-control" id="mob" name="mob" placeholder="+91 ..." value="{{ $users->mob ?? '' }}">
                                    </div>

                                    @if(Request::segment(1) != 'my-profile')
                                    <div class="col-md-6">
                                        <label for="password" class="form-label" style="font-weight:500; font-size:0.875rem; color:#495057;">New Password</label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current">
                                    </div>
                                    @endif

                                    <div class="col-md-6">
                                        <label for="role" class="form-label" style="font-weight:500; font-size:0.875rem; color:#495057;">System Role*</label>
                                        <select class="form-select" id="role" name="role" @if(($users->roleFeatures ?? '') == 'All') style="pointer-events:none; background-color:#f8f9fa;" @else required @endif>
                                            @if(($users->roleFeatures ?? '') == 'All')
                                                <option value="">{{ $users->title ?? '' }} (Super Admin)</option>
                                            @endif
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" @if(($users->role ?? '') == $role->id) selected @endif>
                                                    {{ $role->title }} - {{ $role->subtitle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="status" class="form-label" style="font-weight:500; font-size:0.875rem; color:#495057;">Account Status*</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="1" @if(($users->status ?? '') == '1') selected @endif>Active</option>
                                            <option value="2" @if(($users->status ?? '') == '2') selected @endif>Deactive</option>
                                        </select>
                                    </div>

                                    <hr class="my-4">
                                    <h6 class="mb-1 text-uppercase" style="font-size:0.75rem; letter-spacing:1px; color:#006666; font-weight:700;">Permissions & Availability</h6>

                                    @if(in_array('users_assign', $roleArray) || in_array('All', $roleArray))
                                    <div class="col-md-6">
                                        <label class="form-label" style="font-weight:500; font-size:0.875rem; color:#495057;">Assigned Team Members</label>
                                        <select class="selectpicker form-select" multiple data-live-search="true" data-selected-text-format="count > 2" data-container="body" name="assign[]">
                                            @foreach($allusers as $user)
                                                <option value="{{ $user->id }}" @if(in_array($user->id, $userAssign)) selected @endif>{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" style="font-weight:500; font-size:0.875rem; color:#495057;">Module Access</label>
                                        <select class="selectpicker form-select" multiple data-live-search="true" data-selected-text-format="count > 2" data-container="body" name="features[]">
                                            <option value="tasks" @if(in_array('tasks', $userFeaturs)) selected @endif>Tasks</option>
                                            <option value="leads" @if(in_array('leads', $userFeaturs)) selected @endif>Leads</option>
                                            <option value="customers" @if(in_array('customers', $userFeaturs)) selected @endif>Customers</option>
                                        </select>
                                    </div>
                                    @endif

                                    <div class="col-md-6">
                                        <label class="form-label" style="font-weight:500; font-size:0.875rem; color:#495057;">Working Hours (Start)</label>
                                        <input type="time" name="time[start]" class="form-control" value="{{ $workingTime['start'] ?? '10:00' }}" 
                                               @if(in_array('users_assign', $roleArray) || in_array('All', $roleArray)) @else readonly style="background:#f8f9fa;" @endif>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" style="font-weight:500; font-size:0.875rem; color:#495057;">Working Hours (End)</label>
                                        <input type="time" name="time[end]" class="form-control" value="{{ $workingTime['end'] ?? '18:00' }}" 
                                               @if(in_array('users_assign', $roleArray) || in_array('All', $roleArray)) @else readonly style="background:#f8f9fa;" @endif>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="emailSign" class="form-label" style="font-weight:500; font-size:0.875rem; color:#495057;">Email Signature</label>
                                        <textarea class="form-control" id="emailSign" rows="3" name="emailSign" placeholder="HTML Signature or Plain text...">{{ $users->esign ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Footer Actions --}}
                            <div class="col-md-12 mt-4 pt-3 border-top text-end">
                                <button type="reset" class="btn btn-light rounded-pill border px-4 me-2">Reset Changes</button>
                                <button type="submit" class="btn btn-primary rounded-pill px-5" style="background:#006666; border:none;">
                                    Save Account Details
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
@endsection
