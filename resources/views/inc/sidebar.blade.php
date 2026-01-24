@php
    $company = session('companies');
    $roles = session('roles');
    $roleArray = explode(',',($roles->features ?? ''));
    $standard = ["standard","premium","pro"];
    $premium = ["premium","pro"];
    $pro = ["premium","pro"];
@endphp
<div class="sidebar @if(isset($_COOKIE['sidebarOpen']) && $_COOKIE['sidebarOpen'] == 'open') open @endif">
    <div class="logo_details">
        @if(!empty($company->logo))
        <img src="{{ asset('/public/assets/images/company/logos/'.($company->logo ?? '')) }}" alt="{{$company->name ?? ''}}">
        @else
        <div class="logo_name text-white">{{ $company->name ?? 'Admin Panel' }}</div>
        @endif
        <i class="bx bx-menu-alt-right" id="btn"></i>
    </div>
    <ul class="nav-list" id="accordion">
        
        <li class="profile">
            <div class="profile_details">
                @if(!empty(Auth::user()->photo))
                <img src="{{ asset('/public/assets/images/profile/'.(Auth::user()->photo ?? '')) }}" class="shadow-sm" alt="{{Auth::user()->name ?? ''}}">
                @else
                <img src="{{ asset('public/assets/images/profile/user.png') }}" alt="profile image">
                @endif
                <div class="profile_content">
                    <div class="name">{{ Auth::user()->name ?? '' }}</div>
                    <div class="designation">{{ $roles->title ?? '' }}</div>
                </div>
            </div>
        </li>
        
        <li>
            <a href="/home" @if(Request::segment(1) == '' || Request::segment(1) == 'home') class="active" @endif>
                <i class="bx bx-grid-alt"></i>
                <span class="link_name">Dashboard</span>
            </a>
        </li>
        
        @if(Auth::user()->role == 'master')
        <li>
            <a href="/companies" @if(Request::segment(1) == 'companies') class="active" @endif><i class="bx bx-building"></i> <span class="link_name">Companies</span></a>
            <span class="tooltip">Companies</span>
        </li>
        @endif
        
        @if(in_array('leads',$roleArray) || (in_array('All',$roleArray) && in_array(($company->plan ?? ''),$standard)))
        <li>
            <a href="/leads" @if(Request::segment(1) == 'leads') class="active" @endif>
                <i class="bx bx-user-pin"></i> 
                <span class="link_name">Leads</span>
            </a>
            <span class="tooltip">Leads</span>
        </li>
        @endif
        
        @if(in_array('clients',$roleArray) || (in_array('All',$roleArray) && in_array(($company->plan ?? ''),$standard)))
        <li>
            <a href="/clients" @if(Request::segment(1) == 'clients') class="active" @endif>
                <i class="bx bx-user"></i> 
                <span class="link_name">Customers</span>
            </a>
            <span class="tooltip">Customers</span>
        </li>
        @endif
        
        @if(in_array('attendances',$roleArray) || (in_array('All',$roleArray) && in_array(($company->plan ?? ''),$standard)))
        <li>
            <a href="/attendances" @if(Request::segment(1) == 'attendances') class="active" @endif>
                <i class="bx bx-calendar-check"></i> 
                <span class="link_name">Attendance</span>
            </a>
            <span class="tooltip">Attendance</span>
        </li>
        @endif

        @if(in_array('tasks',$roleArray) || (in_array('All',$roleArray) && in_array(($company->plan ?? ''),$standard)))
        <li>
            <a href="/task" @if(Request::segment(1) == 'task' || Request::segment(1) == 'edit-task') class="active" @endif>
                <i class="bx bx-calendar-check"></i>
                <span class="link_name">Tasks</span>
            </a>
            <span class="tooltip">Tasks</span>
        </li>
        @endif
        
        <li>
            <span class="divider" data-bs-toggle="collapse" data-bs-target="#sales-menu"><label>Sales</label> <i class="bx bx-chevron-down-circle"></i></span>
            <div id="sales-menu" class="collapse @if(Request::segment(1) == 'proposals' || Request::segment(1) == 'invoices' || Request::segment(1) == 'contracts' || Request::segment(1) == 'recoveries' || Request::segment(1) == 'manage-proposal' || Request::segment(1) == 'manage-invoice' || Request::segment(1) == 'manage-contract' || Request::segment(1) == 'manage-recovery') show @endif" data-bs-parent="#accordion">
                <ul class="sb_submenu">
                    @if(in_array('proposals',$roleArray) || (in_array('All',$roleArray) && in_array(($company->plan ?? ''),$premium)))
                    <li>
                        <a href="/proposals" @if(Request::segment(1) == 'proposals' || Request::segment(1) == 'manage-proposal') class="active" @endif>
                            <i class="bx bx-briefcase"></i>
                            <span class="link_name">Proposals</span>
                        </a>
                        <span class="tooltip">Proposals</span>
                    </li>
                    @endif

                    @if(in_array('invoice',$roleArray) || (in_array('All',$roleArray) && in_array(($company->plan ?? ''),$premium)))
                    <li>
                        <a href="/invoices" @if(Request::segment(1) == 'invoices' || Request::segment(1) == 'manage-invoice') class="active" @endif>
                            <i class="bx bx-file"></i>
                            <span class="link_name">Invoices</span>
                        </a>
                        <span class="tooltip">Invoices</span>
                    </li>
                    @endif

                    @if(in_array('contracts',$roleArray) || (in_array('All',$roleArray) && in_array(($company->plan ?? ''),$premium)) || (Auth::user()->role == 'master'))
                    <li>
                        <a href="/contracts" @if(Request::segment(1) == 'contracts' || Request::segment(1) == 'manage-contract') class="active" @endif>
                            <i class="bx bx-box"></i>
                            <span class="link_name">Contracts</span>
                        </a>
                        <span class="tooltip">Contracts</span>
                    </li>
                    @endif

                    @if(in_array('recoveries',$roleArray) || (in_array('All',$roleArray) && in_array(($company->plan ?? ''),$premium)))
                    <li>
                        <a href="/recoveries" @if(Request::segment(1) == 'recoveries' || Request::segment(1) == 'manage-recovery') class="active" @endif>
                            <i class="bx bx-money"></i>
                            <span class="link_name">Recovery</span>
                        </a>
                        <span class="tooltip">Recovery</span>
                    </li>
                    @endif
                </ul>
            </div>
        </li>
        
        @if(Auth::user()->role == 'master')
        <li>
            <a href="/licensing" @if(Request::segment(1) == 'licensing' || Request::segment(1) == 'manage-license') class="active" @endif>
                <i class="bx bx-file"></i>
                <span class="link_name">Licensing</span>
            </a>
            <span class="tooltip">Licensing</span>
        </li>
        @endif
        
        <li>
            <span class="divider" data-bs-toggle="collapse" data-bs-target="#s"><label>Settings</label> <i class="bx bx-chevron-down-circle"></i></span>
            <div id="s" class="collapse @if(Request::segment(1) == 'my-profile' || Request::segment(1) == 'smtp-settings' || Request::segment(1) == 'email-templates' || Request::segment(1) == 'my-company' || Request::segment(1) == 'reset-password' || Request::segment(1) == 'role-settings' || Request::segment(1) == 'manage-role-setting') show @endif" data-bs-parent="#accordion">
                <ul class="sb_submenu">
                    
                     @if(in_array('company_edit',$roleArray) || (in_array('All',$roleArray) && in_array(($company->plan ?? ''),$premium)))
                    <li>
                        <a href="/my-company" @if(Request::segment(1) == 'my-company') class="active" @endif><i class="bx bx-building"></i> <span class="link_name">My Company</span></a>
                        <span class="tooltip">My Companys</span>
                    </li>
                    @endif
                    
                    @if(Auth::user()->role != 'master')
                    <li>
                        <a href="/my-profile" @if(Request::segment(1) == 'my-profile') class="active" @endif><i class="bx bx-user"></i> <span class="link_name">My Profile</span></a>
                        <span class="tooltip">My Profile</span>
                    </li>
                    @endif
                    
                    @if(in_array('smtp_edit',$roleArray) || (in_array('All',$roleArray) && in_array(($company->plan ?? ''),$premium)))
                    <li>
                        <a href="/smtp-settings" @if(Request::segment(1) == 'smtp-setup') class="active" @endif><i class="bx bx-cog"></i> <span class="link_name">SMTP Settings</span></a>
                        <span class="tooltip">SMTP Settings</span>
                    </li>
                    <li>
                        <a href="/email-templates" @if(Request::segment(1) == 'email-templates') class="active" @endif>
                            <i class="bx bx-envelope"></i>
                            <span class="link_name">Email Templates</span>
                        </a>
                        <span class="tooltip">Email Templates</span>
                    </li>

                    @endif
                    
                    <li>
                        <a href="/reset-password" @if(Request::segment(1) == 'reset-password') class="active" @endif><i class="bx bx-lock"></i> <span class="link_name">Reset Password</span></a>
                        <span class="tooltip">Reset Password</span>
                    </li>
                    
                    @if(in_array('settings',$roleArray) || (in_array('All',$roleArray) && in_array(($company->plan ?? ''),$premium)))
                    <li>
                        <a href="/role-settings" @if(Request::segment(1) == 'role-settings') class="active" @endif><i class="bx bx-shield"></i> <span class="link_name">Role Settings</span></a>
                        <span class="tooltip">Role Settings</span>
                    </li>
                    @endif
                </ul>

            </div>
        </li>
        <!--<li class="profile">
            <div class="profile_details">
                @if(!empty(Auth::user()->photo))
                <img src="{{ asset('/public/assets/images/profile/'.(Auth::user()->photo ?? '')) }}" class="shadow-sm" alt="{{Auth::user()->name ?? ''}}">
                @else
                <img src="{{ asset('public/assets/images/profile/user.png') }}" alt="profile image">
                @endif
                <div class="profile_content">
                    <div class="name">{{ Auth::user()->name ?? '' }}</div>
                    <div class="designation">{{ $roles->title ?? '' }}</div>
                </div>
            </div>
        </li>-->
    </ul>
</div>