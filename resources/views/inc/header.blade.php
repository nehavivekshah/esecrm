<header class="dashboard-header">

    {{-- LEFT: Mobile menu toggle + Page Title --}}
    <div class="header-left">
        <button class="header-menu-btn d-lg-none" id="mbtn" aria-label="Open sidebar">
            <i class="bx bx-menu"></i>
        </button>
        <div class="header-title-block">
            <h1 class="header-page-title">{{ $title ?? 'Dashboard' }}</h1>
            <span class="header-greeting d-none d-md-block">
                Good {{ \Carbon\Carbon::now()->hour < 12 ? 'morning' : (\Carbon\Carbon::now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', Auth::user()->name)[0] }}
            </span>
        </div>
    </div>

    {{-- RIGHT: Actions bar --}}
    <div class="header-right">

        {{-- Quick Add --}}
        <div class="dropdown">
            <button class="header-action-btn header-action-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bx bx-plus"></i>
                <span class="d-none d-md-inline">Quick Add</span>
            </button>
            <ul class="dropdown-menu header-dropdown mt-2">
                <li class="dropdown-section-label">Create New</li>
                <li>
                    <a class="dropdown-item header-dropdown-item" href="/manage-lead">
                        <span class="hdi-icon" style="background:rgba(26,115,232,0.08); color:#1a73e8;"><i class="bx bx-user-plus"></i></span>
                        <div>
                            <div class="hdi-title">New Lead</div>
                            <small class="hdi-sub">Add to sales pipeline</small>
                        </div>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item header-dropdown-item" href="/manage-client">
                        <span class="hdi-icon" style="background:rgba(52,168,83,0.08); color:#34a853;"><i class="bx bx-group"></i></span>
                        <div>
                            <div class="hdi-title">New Client</div>
                            <small class="hdi-sub">Add to client base</small>
                        </div>
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1 mx-3"></li>
                <li>
                    <a class="dropdown-item header-dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#todoListModal">
                        <span class="hdi-icon" style="background:rgba(251,188,4,0.10); color:#f9a825;"><i class="bx bx-task"></i></span>
                        <div>
                            <div class="hdi-title">Add Task</div>
                            <small class="hdi-sub">Add to your to-do list</small>
                        </div>
                    </a>
                </li>
            </ul>
        </div>

        {{-- My Tasks Button --}}
        <button type="button" class="header-action-btn" data-bs-toggle="modal" data-bs-target="#todoListModal" title="My Tasks">
            <i class="bx bx-check-double"></i>
            <span class="d-none d-md-inline">Tasks</span>
        </button>

        {{-- Notifications --}}
        <div class="dropdown">
            <button class="header-icon-btn position-relative" type="button" data-bs-toggle="dropdown" aria-label="Notifications">
                <i class="bx bx-bell"></i>
                @php $newLeadsCount = \App\Models\Leads::where('status', 'New')->count(); @endphp
                @if($newLeadsCount > 0)
                    <span class="header-notif-dot"></span>
                @endif
            </button>
            <ul class="dropdown-menu header-dropdown dropdown-menu-end mt-2" style="min-width: 300px;">
                <li class="dropdown-section-label">Notifications</li>
                @if($newLeadsCount > 0)
                    <li>
                        <a class="dropdown-item header-dropdown-item" href="/leads">
                            <span class="hdi-icon" style="background:rgba(26,115,232,0.08); color:#1a73e8;"><i class="bx bx-user-plus"></i></span>
                            <div>
                                <div class="hdi-title">New Leads Received</div>
                                <small class="hdi-sub">{{ $newLeadsCount }} potential client{{ $newLeadsCount > 1 ? 's' : '' }} waiting</small>
                            </div>
                        </a>
                    </li>
                @else
                    <li>
                        <div class="header-dropdown-empty">
                            <i class="bx bx-bell-off"></i>
                            <span>All clear — no new alerts</span>
                        </div>
                    </li>
                @endif
            </ul>
        </div>

        {{-- Divider --}}
        <div class="header-divider d-none d-md-block"></div>

        {{-- User Avatar Dropdown --}}
        <div class="dropdown">
            <a href="#" class="header-user-btn no-caret dropdown-toggle" data-bs-toggle="dropdown" aria-label="Account menu">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=006666&color=ffff00&size=64"
                     class="header-avatar" width="36" height="36" alt="{{ Auth::user()->name }}">
                <div class="header-user-info d-none d-lg-block">
                    <div class="header-user-name">{{ Auth::user()->name }}</div>
                    <div class="header-user-role">{{ Auth::user()->role == '0' ? 'Admin' : 'Staff' }}</div>
                </div>
                <i class="bx bx-chevron-down header-user-chevron d-none d-lg-block"></i>
            </a>
            <ul class="dropdown-menu header-dropdown dropdown-menu-end mt-2" style="min-width: 200px;">
                <li class="header-user-meta px-3 py-2">
                    <div class="fw-bold" style="font-size: 0.83rem; color: #202124;">{{ Auth::user()->name }}</div>
                    <div style="font-size: 0.72rem; color: #5f6368;">{{ Auth::user()->email }}</div>
                </li>
                <li><hr class="dropdown-divider my-1 mx-2"></li>
                <li>
                    <a class="dropdown-item header-dropdown-item-sm" href="/manageUser?id={{ Auth::id() }}">
                        <i class="bx bx-user"></i> My Profile
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1 mx-2"></li>
                <li>
                    <a class="dropdown-item header-dropdown-item-sm text-danger" href="/signout">
                        <i class="bx bx-log-out"></i> Sign Out
                    </a>
                </li>
            </ul>
        </div>

    </div>

</header>
