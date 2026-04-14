@extends('layout')
@section('title', (Request::segment(1) != 'my-profile' ? 'Manage Staff - eseCRM' : 'My Account - eseCRM'))

@section('content')

    @php
        $sessionroles = session('roles');
        $roleArray = explode(',',($sessionroles->permissions ?? ''));
        $userAssign = explode(',', ($users->assign ?? ''));
        $userFeaturs = explode(',', ($users->features ?? ''));
        $workingTime = json_decode($users->working_times ?? '', true) ?? [];
    @endphp
    
    <section class="task__section">
        @include('inc.header', ['title' => (Request::segment(1) != 'my-profile' ? 'Manage Staff' : 'My Account')])

        <div class="db-wrap">
            {{-- ═══════════════════════ HEADER / BACK BUTTON ═══════════════════════ --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    @if(Request::segment(1) != 'my-profile')
                        <a href="/users" class="btn btn-white rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; border: 1px solid #eef2f3; background: #fff; color: #5f6368;">
                            <i class="bx bx-left-arrow-alt" style="font-size: 1.4rem;"></i>
                        </a>
                        <div>
                            <h2 class="mb-0" style="font-weight: 800; color: #202124; font-size: 1.4rem;">
                                @if(!empty($_GET['id'])) Edit Staff Member @else Personnel Registration @endif
                            </h2>
                            <p class="text-muted mb-0" style="font-size: 0.8rem;">Update profile details, permissions and accessibility</p>
                        </div>
                    @else
                        <div class="profile-icon-bg" style="width:48px; height:48px; background: linear-gradient(135deg, #006666 0%, #004d4d 100%); border-radius:14px; display:flex; align-items:center; justify-content:center; color:white; font-size:1.4rem; box-shadow: 0 4px 12px rgba(0,102,102,0.2);">
                            <i class="bx bx-user-circle"></i>
                        </div>
                        <div>
                            <h1 class="mb-0" style="font-weight: 800; color: #202124; font-size: 1.4rem;">My Account Settings</h1>
                            <p class="text-muted mb-0" style="font-size:0.82rem;">Personalize your profile and signature</p>
                        </div>
                    @endif
                </div>
            </div>

            <form action="/manage-user" method="post" enctype="multipart/form-data">
                @csrf
                @if(Request::segment(1) == 'my-profile')
                    <input type="hidden" name="id" value="{{ Auth::id() }}">
                @else
                    <input type="hidden" name="id" value="{{ $users->id ?? '' }}">
                @endif

                <div class="row g-4">
                    {{-- ═══════════════════════ LEFT COLUMN: PROFILE CARD ═══════════════════════ --}}
                    <div class="col-lg-4 col-xl-3">
                        <div class="db-card shadow-sm border-0 mb-4" style="border-radius: 20px; overflow: hidden;">
                            <div class="p-4 text-center" style="background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);">
                                <div class="position-relative d-inline-block mb-3">
                                    <div class="avatar-preview shadow-sm" style="width: 140px; height: 140px; border-radius: 50%; border: 4px solid #fff; overflow: hidden; background: #eef2f3;">
                                        <img id="avatar-img" src="{{ !empty($users->photo) ? asset('assets/images/profile/' . $users->photo) : 'https://ui-avatars.com/api/?name='.urlencode($users->name ?? 'User').'&background=006666&color=fff&size=200' }}" 
                                             style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                    <label for="profilePhoto" class="btn btn-primary rounded-circle position-absolute d-flex align-items-center justify-content-center" style="bottom: 5px; right: 5px; width: 36px; height: 36px; background: #006666; border: 3px solid #fff; cursor: pointer;">
                                        <i class="bx bx-camera" style="font-size: 1.1rem;"></i>
                                    </label>
                                    <input type="file" id="profilePhoto" name="profilePhoto" class="d-none" onchange="previewImage(this, 'avatar-img')">
                                </div>
                                <h5 class="mb-0" style="font-weight: 800; color: #202124;">{{ $users->name ?? 'New Staff' }}</h5>
                                <p class="text-muted mb-3" style="font-size: 0.8rem;">{{ $users->title ?? 'Access Level' }}</p>
                                
                                @if(($users->status ?? '1') == '1')
                                    <span class="badge" style="background: #e6f4ea; color: #1e7e34; padding: 6px 14px; border-radius: 10px; font-weight: 700; font-size: 0.7rem;">
                                        <i class="bx bxs-circle me-1" style="font-size: 0.5rem;"></i> ACTIVE ACCOUNT
                                    </span>
                                @else
                                    <span class="badge" style="background: #fdecea; color: #d93025; padding: 6px 14px; border-radius: 10px; font-weight: 700; font-size: 0.7rem;">
                                        <i class="bx bxs-circle me-1" style="font-size: 0.5rem;"></i> SUSPENDED
                                    </span>
                                @endif
                            </div>
                            
                            <div class="p-4 border-top">
                                <div class="mb-4">
                                    <label class="form-label d-block mb-2" style="font-weight: 700; font-size: 0.75rem; color: #5f6368; text-transform: uppercase; letter-spacing: 0.5px;">E-Signature</label>
                                    <div class="signature-box mb-3 border d-flex align-items-center justify-content-center" style="height: 80px; background: #fcfdfe; border-radius: 12px; border-style: dashed !important;">
                                        @if(!empty($users->imgsign))
                                            <img id="sign-img" src="{{ asset('assets/images/signs/' . $users->imgsign) }}" style="max-height: 60px; max-width: 90%; object-fit: contain;" />
                                        @else
                                            <div id="sign-placeholder" class="text-center text-muted">
                                                <i class="bx bx-pen d-block" style="font-size: 1.2rem;"></i>
                                                <small style="font-size: 0.65rem;">No Signature</small>
                                            </div>
                                            <img id="sign-img" style="display: none; max-height: 60px; max-width: 90%; object-fit: contain;" />
                                        @endif
                                    </div>
                                    <input type="file" class="form-control form-control-sm border-0 ps-0" id="imgsign" name="imgsign" onchange="previewImage(this, 'sign-img', 'sign-placeholder')">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══════════════════════ RIGHT COLUMN: FORM DETAILS ═══════════════════════ --}}
                    <div class="col-lg-8 col-xl-9">
                        <div class="db-card shadow-sm border-0" style="border-radius: 20px;">
                            <div class="db-card-head p-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bx bxs-user-detail" style="color: #006666; font-size: 1.2rem;"></i>
                                    <span class="db-card-title">Professional Information</span>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label label-premium">Full Name*</label>
                                        <input type="text" class="form-control input-premium" id="name" name="name" placeholder="John Doe" value="{{ $users->name ?? '' }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label label-premium">Email Address</label>
                                        <input type="email" class="form-control input-premium" id="email" name="email" placeholder="email@example.com" value="{{ $users->email ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mob" class="form-label label-premium">Mobile Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px; border-color: #eef2f3;"><i class="bx bx-phone text-muted"></i></span>
                                            <input type="text" class="form-control input-premium border-start-0 ps-0" id="mob" name="mob" placeholder="+91 ..." value="{{ $users->mob ?? '' }}">
                                        </div>
                                    </div>
                                    @if(Request::segment(1) != 'my-profile')
                                        <div class="col-md-6">
                                            <label for="password" class="form-label label-premium">Access Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px; border-color: #eef2f3;"><i class="bx bx-lock-alt text-muted"></i></span>
                                                <input type="password" class="form-control input-premium border-start-0 ps-0" id="password" name="password" placeholder="Leave blank to keep current">
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-md-6">
                                        <label for="role" class="form-label label-premium">System Role*</label>
                                        <select class="form-select input-premium" id="role" name="role" @if(($users->roleFeatures ?? '') == 'All') style="pointer-events:none; background-color:#f8f9fa;" @else required @endif>
                                            @if(($users->roleFeatures ?? '') == 'All')
                                                <option value="">{{ $users->title ?? '' }} (Super Admin)</option>
                                            @else
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id }}" @if(($users->role ?? '') == $role->id) selected @endif>
                                                        {{ $role->title }} ({{ $role->subtitle }})
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="status" class="form-label label-premium">Account Status*</label>
                                        <select class="form-select input-premium" id="status" name="status" required>
                                            <option value="1" @if(($users->status ?? '') == '1') selected @endif>Active (Grant Access)</option>
                                            <option value="2" @if(($users->status ?? '') == '2') selected @endif>Deactive (Suspend Access)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-5 mb-3 d-flex align-items-center gap-2">
                                    <i class="bx bxs-key" style="color: #006666; font-size: 1.2rem;"></i>
                                    <span class="db-card-title">Permissions & Schedule</span>
                                </div>
                                <div class="row g-4 pt-2">
                                    @if(in_array('users_assign', $roleArray) || in_array('All', $roleArray))
                                        <div class="col-md-6">
                                            <label class="form-label label-premium">Assigned Team Members</label>
                                            <select class="selectpicker form-control input-premium" multiple data-live-search="true" data-selected-text-format="count > 2" data-container="body" name="assign[]">
                                                @foreach($allusers as $user)
                                                    <option value="{{ $user->id }}" @if(in_array($user->id, $userAssign)) selected @endif>{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label label-premium">Enabled Modules</label>
                                            <select class="selectpicker form-control input-premium" multiple data-live-search="true" data-container="body" name="features[]">
                                                <option value="tasks" @if(in_array('tasks', $userFeaturs)) selected @endif>Tasks</option>
                                                <option value="leads" @if(in_array('leads', $userFeaturs)) selected @endif>Leads</option>
                                                <option value="customers" @if(in_array('customers', $userFeaturs)) selected @endif>Customers</option>
                                            </select>
                                        </div>
                                    @endif
                                    <div class="col-md-6">
                                        <label class="form-label label-premium">Working Hours (Start)</label>
                                        <input type="time" name="time[start]" class="form-control input-premium" value="{{ $workingTime['start'] ?? '10:00' }}" 
                                               @if(in_array('users_assign', $roleArray) || in_array('All', $roleArray)) @else readonly style="background:#f8f9fa;" @endif>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label label-premium">Working Hours (End)</label>
                                        <input type="time" name="time[end]" class="form-control input-premium" value="{{ $workingTime['end'] ?? '18:00' }}" 
                                               @if(in_array('users_assign', $roleArray) || in_array('All', $roleArray)) @else readonly style="background:#f8f9fa;" @endif>
                                    </div>
                                    <div class="col-12">
                                        <label for="emailSign" class="form-label label-premium">Email Signature Content</label>
                                        <textarea class="form-control input-premium" id="emailSign" rows="4" name="emailSign" placeholder="Enter HTML or plain text signature...">{{ $users->esign ?? '' }}</textarea>
                                    </div>
                                </div>

                                <div class="mt-5 pt-4 border-top text-end">
                                    <button type="reset" class="btn btn-light rounded-pill px-4 me-2" style="font-weight: 700; color: #5f6368; border: 1px solid #eef2f3;">Discard Changes</button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm" style="background: linear-gradient(135deg, #006666 0%, #004d4d 100%); border: none; font-weight: 700;">
                                        Apply & Save Profile
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <style>
        .label-premium {
            font-weight: 700;
            font-size: 0.8rem;
            color: #5f6368;
            margin-bottom: 8px;
        }
        
        .input-premium {
            border-radius: 12px;
            border-color: #eef2f3;
            padding: 10px 16px;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            box-shadow: none;
        }
        
        .input-premium:focus {
            border-color: #006666;
            box-shadow: 0 0 0 4px rgba(0, 102, 102, 0.05);
            background: #fcfdfe;
        }

        .input-group-text { border-color: #eef2f3; font-size: 1rem; }
        
        /* Custom styling for selectpicker inside our premium skin */
        .bootstrap-select .dropdown-toggle {
            border-radius: 12px !important;
            border-color: #eef2f3 !important;
            padding: 10px 16px !important;
            background-color: white !important;
        }
    </style>

    <script>
        function previewImage(input, targetId, placeholderId = null) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.getElementById(targetId);
                    img.src = e.target.result;
                    img.style.display = 'block';
                    if (placeholderId) {
                        document.getElementById(placeholderId).style.display = 'none';
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    
@endsection
