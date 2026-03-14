<div class="dashboard-header d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
        <div class="p-2 bg-indigo-soft rounded-3 me-3 d-lg-none" id="mbtn" style="cursor: pointer;">
            <i class="bx bx-menu h4 mb-0"></i>
        </div>
        <h1 class="h3 mb-0 font-weight-bold tracking-tight text-slate-900">{{ $title ?? 'Dashboard' }}</h1>
    </div>
    <div class="header-right d-flex align-items-center">
        <!-- Quick Add Dropdown -->
        <div class="dropdown me-2">
            <button class="btn btn-primary rounded-pill btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bx bx-plus me-1"></i> Quick Add
            </button>
            <ul class="dropdown-menu shadow-sm border-0 mt-2" style="border-radius: 12px;">
                <li><a class="dropdown-item" href="/manage-lead"><i class="bx bx-user-plus me-2 text-primary"></i> New Lead</a></li>
                <li><a class="dropdown-item" href="/manage-client"><i class="bx bx-group me-2 text-success"></i> New Client</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#todoListModal"><i class="bx bx-task me-2 text-warning"></i> Add Task</a></li>
            </ul>
        </div>

        <div class="position-relative dropdown me-2">
            <a href="#" class="btn btn-icon btn-light rounded-circle" data-bs-toggle="dropdown">
                <i class="bx bx-bell h5 mb-0"></i>
                @php
                    $newLeadsCount = \App\Models\Leads::where('status', 'New')->count();
                @endphp
                @if($newLeadsCount > 0)
                    <span class="position-absolute translate-middle badge rounded-pill bg-danger border border-white" 
                          style="top: 10px; right: -5px; padding: 0.35em 0.35em;">
                        <span class="visually-hidden">notifications</span>
                    </span>
                @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-xl border-0 mt-2 p-2" style="border-radius: 16px; min-width: 280px;">
                <li class="px-3 py-2 border-bottom mb-2">
                    <span class="text-xs font-weight-bold text-uppercase text-slate-400">Notifications</span>
                </li>
                @if($newLeadsCount > 0)
                    <li><a class="dropdown-item rounded-3 py-2" href="/leads">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                                <i class="bx bx-user-plus text-primary h5 mb-0"></i>
                            </div>
                            <div>
                                <div class="small font-weight-bold text-slate-700">New Leads Received</div>
                                <div class="text-xs text-slate-400">{{ $newLeadsCount }} potential clients</div>
                            </div>
                        </div>
                    </a></li>
                @else
                    <li class="px-3 py-4 text-center">
                        <i class="bx bx-bell-off text-muted h1 d-block mb-2"></i>
                        <span class="small text-muted">All clear! No new alerts</span>
                    </li>
                @endif
            </ul>
        </div>
        
        <button type="button" class="btn btn-outline-secondary rounded-pill btn-sm me-3" data-bs-toggle="modal" data-bs-target="#todoListModal">
            <i class="bx bx-check-double me-1"></i> <span class="d-none d-md-inline">My Tasks</span>
        </button>
        
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle no-caret" data-bs-toggle="dropdown">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=006666&color=ffff00" 
                     class="rounded-circle border border-2 border-white shadow-sm" width="36" height="36" alt="Profile">
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-xl border-0 mt-2 p-2" style="border-radius: 16px;">
                <li><a class="dropdown-item rounded-3 mb-1" href="/manageUser?id={{ Auth::id() }}"><i class="bx bx-user me-2"></i>My Profile</a></li>
                <li><hr class="dropdown-divider mx-2"></li>
                <li><a class="dropdown-item rounded-3 text-danger" href="/signout"><i class="bx bx-log-out me-2"></i>Sign Out</a></li>
            </ul>
        </div>
    </div>
</div>
