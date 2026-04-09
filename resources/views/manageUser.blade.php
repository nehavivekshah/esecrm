@extends('layout')
@section('title', 'Profile Settings - eseCRM')

@section('content')

    @php
        $sessionroles = session('roles');
        $roleArray = explode(',', ($sessionroles->permissions ?? ''));
        $userAssign = explode(',', ($users->assign ?? ''));
        $userFeaturs = explode(',', ($users->features ?? ''));
        $workingTime = json_decode($users->working_times ?? '', true) ?? [];
    @endphp
    
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i> 
            @if(Request::segment(1) != 'my-profile') Manage User @else Security & Profile @endif
            <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>

        <div class="dash-container d-flex align-items-center justify-content-center py-5" style="min-height: calc(100vh - 80px); background: #f8f9fa;">
            <div class="dash-card p-0" style="width: 100%; max-width: 750px; background: #fff; border: 1px solid #e8eaed; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.04);">
                
                {{-- Card Header: Reset Password Style --}}
                <div class="p-4 border-bottom text-center" style="background: linear-gradient(135deg, #006666, #004d4d); border-radius: 20px 20px 0 0;">
                    <div class="mb-3 position-relative" style="width: 80px; height: 80px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; border: 2px solid rgba(255,255,255,0.2);">
                        @if(!empty($users->photo))
                            <img src="{{ asset('assets/images/profile/' . $users->photo) }}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;" />
                        @else
                            <i class="bx bx-user text-white" style="font-size: 2.5rem;"></i>
                        @endif
                    </div>
                    <h4 class="text-white mb-1 fw-700">@if(Request::segment(1) != 'my-profile') Edit User Account @else My Profile Settings @endif</h4>
                    <p class="text-white-50 small mb-0">Update personal information and account preferences</p>
                </div>

                <form action="/manage-user" method="post" class="p-4" enctype="multipart/form-data">
                    @csrf
                    
                    @if(Request::segment(1) == 'my-profile')
                        <input type="hidden" name="id" value="{{ Auth::id() }}">
                    @else
                        <input type="hidden" name="id" value="{{ $users->id ?? '' }}">
                    @endif

                    <div class="row g-4">
                        {{-- Identity Section --}}
                        <div class="col-12">
                            <h6 class="text-uppercase fw-700 small text-muted mb-0" style="letter-spacing: 1px;">Account Identity</h6>
                        </div>

                        {{-- Name --}}
                        <div class="col-md-6">
                            <label class="form-label fw-600 small text-muted mb-2">Full Name*</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light" style="border-radius: 12px 0 0 12px; border: 1.5px solid #e8eaed;">
                                    <i class="bx bx-user text-muted"></i>
                                </span>
                                <input type="text" name="name" id="name" class="form-control border-start-0 bg-light shadow-none" placeholder="Enter full name" value="{{ $users->name ?? '' }}" required 
                                    style="border-radius: 0 12px 12px 0; border: 1.5px solid #e8eaed; padding: 10px 14px; font-size: 0.9rem;">
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="col-md-6">
                            <label class="form-label fw-600 small text-muted mb-2">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light" style="border-radius: 12px 0 0 12px; border: 1.5px solid #e8eaed;">
                                    <i class="bx bx-envelope text-muted"></i>
                                </span>
                                <input type="email" name="email" id="email" class="form-control border-start-0 bg-light shadow-none" placeholder="email@example.com" value="{{ $users->email ?? '' }}"
                                    style="border-radius: 0 12px 12px 0; border: 1.5px solid #e8eaed; padding: 10px 14px; font-size: 0.9rem;">
                            </div>
                        </div>

                        {{-- Mobile --}}
                        <div class="col-md-6">
                            <label class="form-label fw-600 small text-muted mb-2">Mobile Number</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light" style="border-radius: 12px 0 0 12px; border: 1.5px solid #e8eaed;">
                                    <i class="bx bx-phone text-muted"></i>
                                </span>
                                <input type="text" name="mob" id="mob" class="form-control border-start-0 bg-light shadow-none" placeholder="+91 ..." value="{{ $users->mob ?? '' }}"
                                    style="border-radius: 0 12px 12px 0; border: 1.5px solid #e8eaed; padding: 10px 14px; font-size: 0.9rem;">
                            </div>
                        </div>

                        @if(Request::segment(1) != 'my-profile')
                        {{-- Password --}}
                        <div class="col-md-6">
                            <label class="form-label fw-600 small text-muted mb-2">Login Password</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light" style="border-radius: 12px 0 0 12px; border: 1.5px solid #e8eaed;">
                                    <i class="bx bx-lock-alt text-muted"></i>
                                </span>
                                <input type="password" name="password" id="password" class="form-control border-start-0 bg-light shadow-none" placeholder="Change password"
                                    style="border-radius: 0 12px 12px 0; border: 1.5px solid #e8eaed; padding: 10px 14px; font-size: 0.9rem;">
                            </div>
                        </div>
                        @endif

                        {{-- Profile Photos --}}
                        <div class="col-md-6">
                            <label class="form-label fw-600 small text-muted mb-2">Change Profile Photo</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light" style="border-radius: 12px 0 0 12px; border: 1.5px solid #e8eaed;">
                                    <i class="bx bx-image-add text-muted"></i>
                                </span>
                                <input type="file" name="profilePhoto" id="profilePhoto" class="form-control border-start-0 bg-light shadow-none" 
                                    style="border-radius: 0 12px 12px 0; border: 1.5px solid #e8eaed; padding: 10px 14px; font-size: 0.85rem;">
                            </div>
                        </div>

                        {{-- Signature --}}
                        <div class="col-md-6">
                            <label class="form-label fw-600 small text-muted mb-2">E-Signature Image</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light" style="border-radius: 12px 0 0 12px; border: 1.5px solid #e8eaed;">
                                    <i class="bx bx-pen text-muted"></i>
                                </span>
                                <input type="file" name="imgsign" id="imgsign" class="form-control border-start-0 bg-light shadow-none" 
                                    style="border-radius: 0 12px 12px 0; border: 1.5px solid #e8eaed; padding: 10px 14px; font-size: 0.85rem;">
                            </div>
                        </div>

                        {{-- Configurations Section --}}
                        <div class="col-12 mt-5">
                            <h6 class="text-uppercase fw-700 small text-muted mb-0" style="letter-spacing: 1px;">Access & Availability</h6>
                        </div>

                        {{-- Role --}}
                        <div class="col-md-6">
                            <label class="form-label fw-600 small text-muted mb-2">System Role*</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light" style="border-radius: 12px 0 0 12px; border: 1.5px solid #e8eaed;">
                                    <i class="bx bx-shield text-muted"></i>
                                </span>
                                <select class="form-select border-start-0 bg-light shadow-none" id="role" name="role" @if(($users->roleFeatures ?? '') == 'All') style="border-radius: 0 12px 12px 0; border: 1.5px solid #e8eaed; pointer-events:none; background-color:#f1f5f9; font-size: 0.9rem;" @else style="border-radius: 0 12px 12px 0; border: 1.5px solid #e8eaed; font-size: 0.9rem;" required @endif>
                                    @if(($users->roleFeatures ?? '') == 'All')
                                        <option value="">{{ $users->title ?? '' }} (Super Admin)</option>
                                    @endif
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" @if(($users->role ?? '') == $role->id) selected @endif>{{ $role->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="col-md-6">
                            <label class="form-label fw-600 small text-muted mb-2">Account Status*</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light" style="border-radius: 12px 0 0 12px; border: 1.5px solid #e8eaed;">
                                    <i class="bx bx-toggle-right text-muted"></i>
                                </span>
                                <select class="form-select border-start-0 bg-light shadow-none" id="status" name="status" required
                                    style="border-radius: 0 12px 12px 0; border: 1.5px solid #e8eaed; font-size: 0.9rem;">
                                    <option value="1" @if(($users->status ?? '') == '1') selected @endif>Active</option>
                                    <option value="2" @if(($users->status ?? '') == '2') selected @endif>Deactive</option>
                                </select>
                            </div>
                        </div>

                        {{-- Working Hours --}}
                        <div class="col-md-6">
                            <label class="form-label fw-600 small text-muted mb-2">Shift Start</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light" style="border-radius: 12px 0 0 12px; border: 1.5px solid #e8eaed;">
                                    <i class="bx bx-time-five text-muted"></i>
                                </span>
                                <input type="time" name="time[start]" class="form-control border-start-0 bg-light shadow-none" value="{{ $workingTime['start'] ?? '10:00' }}"
                                    style="border-radius: 0 12px 12px 0; border: 1.5px solid #e8eaed; font-size: 0.9rem;" 
                                    @if(in_array('users_assign', $roleArray) || in_array('All', $roleArray)) @else readonly @endif>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600 small text-muted mb-2">Shift End</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light" style="border-radius: 12px 0 0 12px; border: 1.5px solid #e8eaed;">
                                    <i class="bx bx-time text-muted"></i>
                                </span>
                                <input type="time" name="time[end]" class="form-control border-start-0 bg-light shadow-none" value="{{ $workingTime['end'] ?? '18:00' }}"
                                    style="border-radius: 0 12px 12px 0; border: 1.5px solid #e8eaed; font-size: 0.9rem;"
                                    @if(in_array('users_assign', $roleArray) || in_array('All', $roleArray)) @else readonly @endif>
                            </div>
                        </div>

                        {{-- Signature Text --}}
                        <div class="col-12">
                            <label class="form-label fw-600 small text-muted mb-2">Email Signature (HTML/Text)</label>
                            <textarea class="form-control bg-light shadow-none" id="emailSign" rows="4" name="emailSign" placeholder="Enter your business signature..."
                                style="border-radius: 12px; border: 1.5px solid #e8eaed; font-size: 0.9rem;">{{ $users->esign ?? '' }}</textarea>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="col-12 mt-4 pt-4 border-top">
                            <div class="d-grid gap-2">
                                <button type="submit" class="lb-btn lb-btn-primary py-3 fw-700 shadow-sm" style="font-size: 1rem; border-radius: 15px;">
                                    <i class="bx bx-save me-2"></i> Save Changes
                                </button>
                                <button type="reset" class="btn btn-light py-2 fw-600 text-muted border-0 bg-transparent">
                                    Reset to Default
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- Card Footer --}}
                <div class="p-4 text-center bg-light border-top" style="border-radius: 0 0 20px 20px;">
                    <div class="small text-muted" style="font-size: 0.75rem;">
                        <i class="bx bx-info-circle me-1 text-primary"></i> 
                        Account changes are logged for security purposes. Last update track available in audit logs.
                    </div>
                </div>
            </div>
        </div>
    </section>
    
@endsection
