@php
    $company = session('companies');
    $roles = session('roles');
    $roleArray = explode(',', ($roles->features ?? ''));
    $standard = ["standard", "premium", "pro"];
    $premium = ["premium", "pro"];
    $pro = ["premium", "pro"];
@endphp
<div class="sidebar @if(isset($_COOKIE['sidebarOpen']) && $_COOKIE['sidebarOpen'] == 'open') open @endif">
    <div class="logo_details">
        @if(!empty($company->logo))
            <img src="{{ asset('/public/assets/images/company/logos/' . ($company->logo ?? '')) }}"
                alt="{{$company->name ?? ''}}">
        @else
            <div class="logo_name text-white">{{ $company->name ?? 'Admin Panel' }}</div>
        @endif
        <i class="bx bx-menu-alt-right" id="btn"></i>
    </div>
    <ul class="nav-list" id="accordion">

        <li class="profile">
            <div class="profile_details">
                @if(!empty(Auth::user()->photo))
                    <img src="{{ asset('/public/assets/images/profile/' . (Auth::user()->photo ?? '')) }}" class="shadow-sm"
                        alt="{{Auth::user()->name ?? ''}}">
                @else
                    <img src="{{ asset('public/assets/images/profile/user.png') }}" alt="profile image">
                @endif
                <div class="profile_content">
                    <div class="name">{{ Auth::user()->name ?? '' }}</div>
                    <div class="designation">{{ $roles->title ?? '' }}</div>
                </div>
            </div>
        </li>

        <li class="nav-title">MAIN</li>
        <li>
            <a href="/home" @if(Request::segment(1) == '' || Request::segment(1) == 'home') class="active" @endif>
                <i class="bx bx-grid-alt"></i>
                <span class="link_name">Dashboard</span>
            </a>
        </li>

        @if(Auth::user()->role == 'master')
            <li>
                <a href="/companies" @if(Request::segment(1) == 'companies') class="active" @endif><i
                        class="bx bx-building"></i> <span class="link_name">Companies</span></a>
                <span class="tooltip">Companies</span>
            </li>
        @endif

        <li class="nav-title">CRM</li>
        @if(in_array('leads', $roleArray) || (in_array('All', $roleArray) && in_array(($company->plan ?? ''), $standard)))
            <li>
                <span class="divider" data-bs-toggle="collapse" data-bs-target="#leads-menu"><label>Leads</label> <i
                        class="bx bx-chevron-down-circle"></i></span>
                <div id="leads-menu"
                    class="collapse @if(Request::segment(1) == 'leads' || Request::segment(1) == 'leads' && Request::segment(2) == 'kanban') show @endif"
                    data-bs-parent="#accordion">
                    <ul class="sb_submenu">
                        <li>
                            <a href="/leads" @if(Request::segment(1) == 'leads' && Request::segment(2) == '') class="active"
                            @endif>
                                <i class="bx bx-list-ul"></i>
                                <span class="link_name">List View</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('leads.kanban') }}" @if(Request::segment(1) == 'leads' && Request::segment(2) == 'kanban') class="active" @endif>
                                <i class="bx bx-grid-alt"></i>
                                <span class="link_name">Kanban View</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        @endif

        @if(in_array('clients', $roleArray) || (in_array('All', $roleArray) && in_array(($company->plan ?? ''), $standard)))
            <li>
                <a href="/clients" @if(Request::segment(1) == 'clients') class="active" @endif>
                    <i class="bx bx-user"></i>
                    <span class="link_name">Customers</span>
                </a>
                <span class="tooltip">Customers</span>
            </li>
        @endif



        @if(in_array('tasks', $roleArray) || in_array('crm_tasks', $roleArray) || (in_array('All', $roleArray) && in_array(($company->plan ?? ''), $standard)))
            <li>
                <span class="divider" data-bs-toggle="collapse" data-bs-target="#tasks-menu"><label>Tasks</label> <i
                        class="bx bx-chevron-down-circle"></i></span>
                <div id="tasks-menu"
                    class="collapse @if(Request::segment(1) == 'task' || Request::segment(1) == 'edit-task' || Request::segment(1) == 'crm-tasks') show @endif"
                    data-bs-parent="#accordion">
                    <ul class="sb_submenu">
                        @if(in_array('crm_tasks', $roleArray) || (in_array('All', $roleArray) && in_array(($company->plan ?? ''), $standard)))
                            <li>
                                <a href="/crm-tasks" @if(Request::segment(1) == 'crm-tasks') class="active" @endif>
                                    <i class="bx bx-phone-call"></i>
                                    <span class="link_name">CRM Follow-ups</span>
                                </a>
                            </li>
                        @endif
                        @if(in_array('tasks', $roleArray) || (in_array('All', $roleArray) && in_array(($company->plan ?? ''), $standard)))
                            <li>
                                <a href="/task" @if(Request::segment(1) == 'task' || Request::segment(1) == 'edit-task')
                                class="active" @endif>
                                    <i class="bx bx-task"></i>
                                    <span class="link_name">Project Tasks</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </li>
        @endif

        <li class="nav-title">SALES</li>
        <li>
            <span class="divider" data-bs-toggle="collapse" data-bs-target="#sales-menu"><label>Sales</label> <i
                    class="bx bx-chevron-down-circle"></i></span>
            <div id="sales-menu"
                class="collapse @if(Request::segment(1) == 'proposals' || Request::segment(1) == 'opportunities' || Request::segment(1) == 'invoices' || Request::segment(1) == 'contracts' || Request::segment(1) == 'recoveries' || Request::segment(1) == 'manage-proposal' || Request::segment(1) == 'manage-invoice' || Request::segment(1) == 'manage-contract' || Request::segment(1) == 'manage-recovery') show @endif"
                data-bs-parent="#accordion">
                <ul class="sb_submenu">
                    @if(in_array('opportunities', $roleArray) || (in_array('All', $roleArray) && in_array(($company->plan ?? ''), $standard)))
                        <li>
                            <a href="/opportunities" @if(Request::segment(1) == 'opportunities') class="active" @endif>
                                <i class="bx bx-doughnut-chart"></i>
                                <span class="link_name">Opportunities</span>
                            </a>
                            <span class="tooltip">Opportunities</span>
                        </li>
                    @endif

                    @if(in_array('proposals', $roleArray) || (in_array('All', $roleArray) && in_array(($company->plan ?? ''), $premium)))
                        <li>
                            <a href="/proposals" @if(Request::segment(1) == 'proposals' || Request::segment(1) == 'manage-proposal') class="active" @endif>
                                <i class="bx bx-briefcase"></i>
                                <span class="link_name">Proposals</span>
                            </a>
                            <span class="tooltip">Proposals</span>
                        </li>
                    @endif

                    @if(in_array('invoice', $roleArray) || (in_array('All', $roleArray) && in_array(($company->plan ?? ''), $premium)))
                        <li>
                            <a href="/invoices" @if(Request::segment(1) == 'invoices' || Request::segment(1) == 'manage-invoice') class="active" @endif>
                                <i class="bx bx-file"></i>
                                <span class="link_name">Invoices</span>
                            </a>
                            <span class="tooltip">Invoices</span>
                        </li>
                    @endif

                    @if(in_array('contracts', $roleArray) || (in_array('All', $roleArray) && in_array(($company->plan ?? ''), $premium)) || (Auth::user()->role == 'master'))
                        <li>
                            <a href="/contracts" @if(Request::segment(1) == 'contracts' || Request::segment(1) == 'manage-contract') class="active" @endif>
                                <i class="bx bx-box"></i>
                                <span class="link_name">Contracts</span>
                            </a>
                            <span class="tooltip">Contracts</span>
                        </li>
                    @endif

                    @if(in_array('recoveries', $roleArray) || (in_array('All', $roleArray) && in_array(($company->plan ?? ''), $premium)))
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



        <li class="nav-title">MARKETING</li>
        @if(in_array('campaigns', $roleArray) || (in_array('All', $roleArray) && in_array(($company->plan ?? ''), $standard)))
            <li>
                <a href="/campaigns" @if(Request::segment(1) == 'campaigns') class="active" @endif>
                    <i class="bx bx-broadcast"></i>
                    <span class="link_name">Campaigns</span>
                </a>
                <span class="tooltip">Campaigns</span>
            </li>
        @endif

        @if(in_array('automations', $roleArray) || (in_array('All', $roleArray) && in_array(($company->plan ?? ''), $standard)))
            <li>
                <a href="/automations" @if(Request::segment(1) == 'automations') class="active" @endif>
                    <i class="bx bx-git-branch"></i>
                    <span class="link_name">Automations</span>
                </a>
                <span class="tooltip">Automations</span>
            </li>
        @endif

        @if(in_array('reports', $roleArray) || (in_array('All', $roleArray) && in_array(($company->plan ?? ''), $standard)))
            <li>
                <a href="/reports" @if(Request::segment(1) == 'reports') class="active" @endif>
                    <i class="bx bx-line-chart"></i>
                    <span class="link_name">Reports</span>
                </a>
                <span class="tooltip">Reports</span>
            </li>
        @endif

        <li class="nav-title">OPERATIONS</li>

        @if(in_array('attendances', $roleArray) || (in_array('All', $roleArray) && in_array(($company->plan ?? ''), $standard)))
            <li>
                <a href="/attendances" @if(Request::segment(1) == 'attendances') class="active" @endif>
                    <i class="bx bx-calendar-check"></i>
                    <span class="link_name">Attendance</span>
                </a>
                <span class="tooltip">Attendance</span>
            </li>
        @endif

        <li class="nav-title">SYSTEM</li>

        @if(Auth::user()->role == 'master')
            <li>
                <a href="/licensing" @if(Request::segment(1) == 'licensing' || Request::segment(1) == 'manage-license')
                class="active" @endif>
                    <i class="bx bx-file"></i>
                    <span class="link_name">Licensing</span>
                </a>
                <span class="tooltip">Licensing</span>
            </li>
        @endif

        <li>
            <span class="divider" data-bs-toggle="collapse" data-bs-target="#s"><label>Settings</label> <i
                    class="bx bx-chevron-down-circle"></i></span>
            <div id="s"
                class="collapse @if(Request::segment(1) == 'my-profile' || Request::segment(1) == 'smtp-settings' || Request::segment(1) == 'email-templates' || Request::segment(1) == 'my-company' || Request::segment(1) == 'reset-password' || Request::segment(1) == 'role-settings' || Request::segment(1) == 'manage-role-setting') show @endif"
                data-bs-parent="#accordion">
                <ul class="sb_submenu">

                    @if(in_array('company_edit', $roleArray) || (in_array('All', $roleArray) && in_array(($company->plan ?? ''), $premium)))
                        <li>
                            <a href="/my-company" @if(Request::segment(1) == 'my-company') class="active" @endif><i
                                    class="bx bx-building"></i> <span class="link_name">My Company</span></a>
                            <span class="tooltip">My Companys</span>
                        </li>
                    @endif

                    @if(Auth::user()->role != 'master')
                        <li>
                            <a href="/my-profile" @if(Request::segment(1) == 'my-profile') class="active" @endif><i
                                    class="bx bx-user"></i> <span class="link_name">My Profile</span></a>
                            <span class="tooltip">My Profile</span>
                        </li>
                    @endif

                    @if(in_array('smtp_edit', $roleArray) || (in_array('All', $roleArray) && in_array(($company->plan ?? ''), $premium)))
                        <li>
                            <a href="/smtp-settings" @if(Request::segment(1) == 'smtp-setup') class="active" @endif><i
                                    class="bx bx-cog"></i> <span class="link_name">SMTP Settings</span></a>
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
                        <a href="/reset-password" @if(Request::segment(1) == 'reset-password') class="active" @endif><i
                                class="bx bx-lock"></i> <span class="link_name">Reset Password</span></a>
                        <span class="tooltip">Reset Password</span>
                    </li>

                    @if(in_array('settings', $roleArray) || (in_array('All', $roleArray) && in_array(($company->plan ?? ''), $premium)))
                        <li>
                            <a href="/role-settings" @if(Request::segment(1) == 'role-settings') class="active" @endif><i
                                    class="bx bx-shield"></i> <span class="link_name">Role Settings</span></a>
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