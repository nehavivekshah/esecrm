@extends('layout')
@section('title', 'Dashboard - eseCRM')

@section('content')

    @php
        $company = session('companies');
        $roles = session('roles');
        $roleArray = explode(',', ($roles->features ?? ''));

        if (!function_exists('formatLeadCount')) {
            function formatLeadCount($num)
            {
                if ($num >= 1000000)
                    return round($num / 1000000, 1) . 'M';
                if ($num >= 1000)
                    return round($num / 1000, 1) . 'K';
                if ($num >= 99)
                    return '99+';
                return $num;
            }
        }
    @endphp
    <style>
        :root {
            --card-radius: 16px;
            --accent-primary: #006666; /* Deep Teal - Primary */
            --accent-secondary: #ffff00; /* Bright Yellow - Secondary */
            --accent-indigo: #004d4d;
            --accent-emerald: #10b981;
            --accent-rose: #f43f5e;
            --accent-amber: #f59e0b;
            --bg-glass: rgba(255, 255, 255, 0.8);
            --header-height: 70px;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        body {
            background-color: #f0f4f4; /* Slightly tinted for brand feel */
            color: #1e293b;
            font-family: 'Poppins', sans-serif;
        }

        .task__section {
            padding: 0;
        }

        /* Premium Header with Glassmorphism */
        .dashboard-header {
            height: var(--header-height);
            background: var(--bg-glass);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 0 30px;
            margin-bottom: 25px;
        }

        .bg-indigo-soft { background-color: rgba(0, 102, 102, 0.1) !important; }
        .bg-blue-soft { background-color: rgba(59, 130, 246, 0.1) !important; }
        .bg-rose-soft { background-color: rgba(244, 63, 94, 0.1) !important; }
        .bg-emerald-soft { background-color: rgba(16, 185, 129, 0.1) !important; }
        .bg-slate-50 { background-color: #f8fafc !important; }
        .bg-slate-100 { background-color: #f1f5f9 !important; }
        
        .text-indigo-600 { color: #006666 !important; }
        .text-emerald-600 { color: #059669 !important; }
        .text-slate-900 { color: #0f172a !important; }
        .text-slate-800 { color: #1e293b !important; }
        .text-slate-700 { color: #334155 !important; }
        .text-slate-600 { color: #475569 !important; }
        .text-slate-500 { color: #64748b !important; }
        .text-slate-400 { color: #94a3b8 !important; }
        .text-slate-300 { color: #cbd5e1 !important; }
        .text-slate-200 { color: #e2e8f0 !important; }

        .btn-indigo { background: linear-gradient(135deg, #006666 0%, #004d4d 100%) !important; color: white !important; border: none !important; }
        .btn-indigo:hover { box-shadow: 0 4px 14px 0 rgba(0,102,102,0.39) !important; transform: scale(1.02); color: #ffff00 !important; }
        .btn-light { background-color: #fff !important; border: 1px solid #e2e8f0 !important; color: #64748b !important; }
        .btn-light:hover { background-color: #f8fafc !important; color: #1e293b !important; }
        
        .bg-rose-500 { background-color: #f43f5e !important; }
        .bg-secondary-brand { background-color: var(--accent-secondary) !important; color: #000 !important; }
        .text-rose-500 { color: #f43f5e !important; }
        .text-primary-brand { color: var(--accent-primary) !important; }
        
        .no-caret::after { display: none !important; }
        .btn-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; padding: 0; }
        .tracking-tight { letter-spacing: -0.025em; }

        .modal-backdrop { z-index: 1040 !important; }
        .modal { z-index: 1050 !important; }
        .modal-content { border-radius: 20px !important; box-shadow: var(--shadow-lg) !important; border: none !important; }

        .card {
            border-radius: var(--card-radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(226, 232, 240, 0.6);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #fff;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .chart-container {
            position: relative;
            height: 350px;
        }

        .activity-log {
            overflow-y: auto;
            flex-grow: 1;
            padding-right: 8px;
        }

        /* Minimal Scrollbar */
        .activity-log::-webkit-scrollbar { width: 4px; }
        .activity-log::-webkit-scrollbar-track { background: transparent; }
        .activity-log::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .activity-log::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.025em;
        }

        /* Mesh Gradients for Metric Widgets with Brand Colors */
        .mesh-gradient-1 { background: radial-gradient(at 0% 0%, #008080 0%, transparent 50%), radial-gradient(at 100% 0%, #006666 0%, transparent 50%), radial-gradient(at 100% 100%, #004d4d 0%, transparent 50%), radial-gradient(at 0% 100%, #006666 0%, transparent 50%), #006666; }
        .mesh-gradient-2 { background: radial-gradient(at 0% 0%, #fb7185 0%, transparent 50%), radial-gradient(at 100% 0%, #f43f5e 0%, transparent 50%), radial-gradient(at 100% 100%, #fda4af 0%, transparent 50%), radial-gradient(at 0% 100%, #f43f5e 0%, transparent 50%), #e11d48; }
        .mesh-gradient-3 { background: radial-gradient(at 0% 0%, #006666 0%, transparent 50%), radial-gradient(at 100% 0%, #00a6a6 0%, transparent 50%), radial-gradient(at 100% 100%, #004d4d 0%, transparent 50%), radial-gradient(at 0% 100%, #008080 0%, transparent 50%), #005a5a; }
        .mesh-gradient-4 { background: radial-gradient(at 0% 0%, #10b981 0%, transparent 50%), radial-gradient(at 100% 0%, #059669 0%, transparent 50%), radial-gradient(at 100% 100%, #34d399 0%, transparent 50%), radial-gradient(at 0% 100%, #10b981 0%, transparent 50%), #059669; }

        .widget-card {
            position: relative;
            overflow: hidden;
            border: none;
            color: white;
        }

        .widget-card i.bg-icon {
            position: absolute;
            right: -10px;
            bottom: -10px;
            font-size: 6rem;
            opacity: 0.15;
            transform: rotate(-15deg);
        }

        /* Premium Quick Actions Tiles */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .action-tile {
            background: #fff;
            padding: 24px;
            border-radius: var(--card-radius);
            text-decoration: none !important;
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .action-tile::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--accent-primary);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 0;
        }

        .action-tile:hover {
            transform: translateY(-8px);
            border-color: transparent;
            box-shadow: 0 20px 25px -5px rgb(0 102 102 / 0.15), 0 8px 10px -6px rgb(0 102 102 / 0.1);
        }

        .action-tile:hover::before { opacity: 0.03; }

        .action-tile i {
            width: 50px;
            height: 50px;
            background: rgba(0, 102, 102, 0.08); /* Primary faded */
            color: var(--accent-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 16px;
            transition: all 0.3s ease;
            z-index: 1;
        }

        .action-tile:hover i {
            background: var(--accent-secondary);
            color: #000; /* High contrast for yellow */
            transform: scale(1.1) rotate(5deg);
        }

        .action-tile h6 {
            font-weight: 700;
            margin-bottom: 4px;
            color: #334155;
            z-index: 1;
        }

        .action-tile span {
            font-size: 1.2rem;
            font-weight: 800;
            color: #0f172a;
            z-index: 1;
        }

        .activity-feed-item {
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 12px;
            background: #f8fafc;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .activity-feed-item:hover {
            background: #fff;
            border-color: #e2e8f0;
            box-shadow: var(--shadow-sm);
        }

        @media (max-width: 1024px) {
            .quick-actions-grid { grid-template-columns: repeat(3, 1fr); }
        }

        @media (max-width: 768px) {
            .quick-actions-grid { grid-template-columns: repeat(2, 1fr); }
            .dashboard-header { padding: 0 15px; }
        }
    </style>
        <section class="task__section">
            <div class="dashboard-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="p-2 bg-indigo-soft rounded-3 me-3 d-lg-none" id="mbtn" style="cursor: pointer;">
                        <i class="bx bx-menu h4 mb-0"></i>
                    </div>
                    <h1 class="h3 mb-0 font-weight-bold tracking-tight text-slate-900">Dashboard</h1>
                </div>
                <div class="header-right">
                    <div class="position-relative dropdown">
                        <a href="#" class="btn btn-icon btn-light rounded-circle" data-bs-toggle="dropdown">
                            <i class="bx bx-bell h5 mb-0"></i>
                            @if(count($newLeads) > 0)
                                <span class="position-absolute translate-middle badge rounded-pill bg-rose-500 border border-white" 
                                      style="top: 10px; right: -5px; padding: 0.35em 0.35em;">
                                    <span class="visually-hidden">notifications</span>
                                </span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-xl border-0 mt-2 p-2" style="border-radius: 16px; min-width: 280px;">
                            <li class="px-3 py-2 border-bottom mb-2">
                                <span class="text-xs font-weight-bold text-uppercase text-slate-400">Notifications</span>
                            </li>
                            @if(count($newLeads) > 0)
                                <li><a class="dropdown-item rounded-3 py-2" href="/leads">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-blue-soft p-2 rounded-3 me-3">
                                            <i class="bx bx-user-plus text-primary h5 mb-0"></i>
                                        </div>
                                        <div>
                                            <div class="small font-weight-bold text-slate-700">New Leads Received</div>
                                            <div class="text-xs text-slate-400">{{ formatLeadCount(count($newLeads)) }} potential clients</div>
                                        </div>
                                    </div>
                                </a></li>
                            @else
                                <li class="px-3 py-4 text-center">
                                    <i class="bx bx-bell-off text-slate-300 h1 d-block mb-2"></i>
                                    <span class="small text-slate-500">All clear! No new alerts</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <button type="button" class="btn btn-indigo rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#todoListModal">
                        <i class="bx bx-check-double me-2"></i> <span>My Tasks</span>
                    </button>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle no-caret" data-bs-toggle="dropdown">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=006666&color=ffff00" 
                                 class="rounded-circle border border-2 border-white shadow-sm" width="40" height="40" alt="Profile">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-xl border-0 mt-2 p-2" style="border-radius: 16px;">
                            <li><a class="dropdown-item rounded-3 mb-1" href="/profile"><i class="bx bx-user me-2"></i>My Profile</a></li>
                            <li><hr class="dropdown-divider mx-2"></li>
                            <li><a class="dropdown-item rounded-3 text-rose-500" href="/signout"><i class="bx bx-log-out me-2"></i>Sign Out</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="container-fluid mb-2">
                <!-- DASHBOARD WIDGETS -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card widget-card mesh-gradient-1 h-100 p-4">
                            <i class="bx bx-receipt bg-icon"></i>
                            <span class="widget-label text-white-50">Outstanding Invoices</span>
                            <div class="d-flex align-items-end">
                                <h2 class="font-weight-bold mb-0 me-2 mt-2">₹{{ number_format($outstandingInvoices, 0) }}</h2>
                            </div>
                            <div class="mt-3">
                                <span class="badge bg-white bg-opacity-20 rounded-pill small">Pending</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card widget-card mesh-gradient-2 h-100 p-4">
                            <i class="bx bx-file-blank bg-icon"></i>
                            <span class="widget-label text-white-50">Pending Proposals</span>
                            <div class="d-flex align-items-end">
                                <h2 class="font-weight-bold mb-0 me-2 mt-2">{{ $pendingProposals }}</h2>
                            </div>
                            <div class="mt-3">
                                <span class="badge bg-white bg-opacity-20 rounded-pill small">Active</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card widget-card mesh-gradient-3 h-100 p-4">
                            <i class="bx bx-task bg-icon"></i>
                            <span class="widget-label text-white-50">Assigned Tasks</span>
                            <div class="d-flex align-items-end">
                                <h2 class="font-weight-bold mb-0 me-2 mt-2">{{ $myPendingTasks }}</h2>
                            </div>
                            <div class="mt-3">
                                <span class="badge bg-white bg-opacity-20 rounded-pill small">Your Queue</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card widget-card mesh-gradient-4 h-100 p-4">
                            <i class="bx bx-pulse bg-icon"></i>
                            <span class="widget-label text-white-50">Total Leads</span>
                            <div class="d-flex align-items-end">
                                <h2 class="font-weight-bold mb-0 me-2 mt-2">{{ $totalLeads }}</h2>
                            </div>
                            <div class="mt-3">
                                <span class="badge bg-white bg-opacity-20 rounded-pill small">Sales Pipeline</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- QUICK ACTIONS SECTION -->
                <div class="mb-5">
                    <div class="d-flex align-items-center mb-4">
                        <div class="p-2 bg-indigo-50 rounded-circle me-3">
                            <i class="bx bx-bolt-circle text-indigo-600 h4 mb-0"></i>
                        </div>
                        <h5 class="mb-0 font-weight-bold text-slate-800">Operational Hub</h5>
                    </div>
                    <div class="quick-actions-grid">
                        @if(in_array('leads', $roleArray) || in_array('All', $roleArray) || (Auth::user()->role == '0'))
                            <a href="/leads" class="action-tile">
                                <i class="bx bx-filter-alt"></i>
                                <h6>Leads Pipeline</h6>
                                <span>{{ count($leads ?? []) }}</span>
                            </a>
                        @endif
                        @if(in_array('clients', $roleArray) || in_array('All', $roleArray) || (Auth::user()->role == '0'))
                            <a href="/clients" class="action-tile" style="--tile-color: #3b82f6;">
                                <i class="bx bx-group" style="background: rgba(59, 130, 246, 0.08); color: #3b82f6;"></i>
                                <h6>Client Base</h6>
                                <span>{{ count($clients ?? []) }}</span>
                            </a>
                            <a href="/projects" class="action-tile" style="--tile-color: #10b981;">
                                <i class="bx bx-file" style="background: rgba(16, 185, 129, 0.08); color: #10b981;"></i>
                                <h6>Project Map</h6>
                                <span>{{ count($projects ?? []) }}</span>
                            </a>
                            <a href="/recoveries" class="action-tile" style="--tile-color: #f59e0b;">
                                <i class="bx bx-coin-stack" style="background: rgba(245, 158, 11, 0.08); color: #f59e0b;"></i>
                                <h6>Recovery Log</h6>
                                <span>{{ count($recoveries ?? []) }}</span>
                            </a>
                        @endif
                        @if(in_array('users', $roleArray) || in_array('All', $roleArray) || (Auth::user()->role == '0'))
                            <a href="/users" class="action-tile" style="--tile-color: #f43f5e;">
                                <i class="bx bx-user" style="background: rgba(244, 63, 94, 0.08); color: #f43f5e;"></i>
                                <h6>Team Users</h6>
                                <span>{{ count($users ?? []) }}</span>
                            </a>
                        @endif
                    </div>
                </div>

                    <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="card h-100 p-4 shadow-sm border-0">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="p-2 bg-emerald-soft rounded-3 me-3">
                                            <i class="bx bx-trending-up text-emerald-600 h5 mb-0"></i>
                                        </div>
                                        <h5 class="card-title mb-0">Revenue Growth</h5>
                                    </div>
                                    <div class="chart-container">
                                        <canvas id="revenueChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100 p-4 shadow-sm border-0">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div class="d-flex align-items-center">
                                            <div class="p-2 bg-blue-soft rounded-3 me-3">
                                                <i class="bx bx-bar-chart-alt-2 text-primary h5 mb-0"></i>
                                            </div>
                                            <h5 class="card-title mb-0">Activity Flux</h5>
                                        </div>
                                        <select id="activityDateRange" class="form-select form-select-sm border-0 bg-slate-50 rounded-pill px-3" style="width: auto; font-size: 0.75rem; font-weight: 600;">
                                            <option value="7" {{ $selectedActivityDays == 7 ? 'selected' : '' }}>7 DAYS</option>
                                            <option value="30" {{ $selectedActivityDays == 30 ? 'selected' : '' }}>30 DAYS</option>
                                            <option value="90" {{ $selectedActivityDays == 90 ? 'selected' : '' }}>90 DAYS</option>
                                        </select>
                                    </div>
                                    <div class="chart-container">
                                        <canvas id="activityFlowChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card h-100 p-4 shadow-sm border-0 d-flex flex-column" style="max-height: 455px;">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="p-2 bg-rose-soft rounded-3 me-3">
                                        <i class="bx bx-pulse text-rose-500 h5 mb-0"></i>
                                    </div>
                                    <h5 class="card-title mb-0">Live Activities</h5>
                                </div>
                                <span class="badge bg-slate-100 text-slate-500 rounded-pill px-3 py-2 font-weight-bold" style="font-size: 0.65rem;">REAL-TIME</span>
                            </div>
                            <div class="activity-log">
                                @forelse(collect($activities ?? [])->take(20) as $activity)
                                    <div class="activity-feed-item">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="font-weight-bold text-slate-800 small">{{ $activity->user_name ?? 'System' }}</span>
                                            <span class="text-slate-400" style="font-size: 0.65rem;">{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</span>
                                        </div>
                                        <p class="mb-2 text-slate-600 small" style="line-height: 1.5;">{{ $activity->type }} - {{ $activity->description ?? 'Action recorded' }}</p>
                                        @if(isset($activity->subject))
                                            <span class="badge bg-indigo-soft text-indigo-600 rounded px-2 py-1" style="font-size: 0.6rem; font-weight: 700;">{{ strtoupper($activity->subject) }}</span>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-center py-5 text-muted flex-grow-1 d-flex flex-column justify-content-center">
                                        <i class="bx bx-news mb-2 text-slate-200" style="font-size: 3rem;"></i>
                                        <p class="small text-slate-400">Waiting for activities...</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </section>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // REVENUE CHART (Dynamic)
            const ctx = document.getElementById('revenueChart').getContext('2d');
            const monthlyRevenue = {!! json_encode($monthlyRevenue) !!}; // Passed from Controller

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [
                        {
                            label: 'Total Revenue ({{ date("Y") }})',
                            data: monthlyRevenue,
                            borderColor: '#2ecc71',
                            backgroundColor: 'rgba(46, 204, 113, 0.1)',
                            fill: true,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) { return '₹' + value; }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return 'Revenue: ₹' + context.raw;
                                }
                            }
                        }
                    }
                }
            });

            // ACTIVITY MONITOR FLOW CHART (Day-wise, stacked by user)
            const activityCtx = document.getElementById('activityFlowChart').getContext('2d');

            const activityLabels = {!! json_encode($activityChartLabels) !!}; // Dates
            const activityDatasets = {!! json_encode($activityChartDatasets) !!}; // User datasets

            // Generate colors for each user
            const colors = [
                'rgba(46, 204, 113, 0.7)',   // Green
                'rgba(52, 152, 219, 0.7)',   // Blue
                'rgba(155, 89, 182, 0.7)',   // Purple
                'rgba(241, 196, 15, 0.7)',   // Yellow
                'rgba(231, 76, 60, 0.7)',    // Red
                'rgba(26, 188, 156, 0.7)',   // Teal
                'rgba(230, 126, 34, 0.7)',   // Orange
                'rgba(149, 165, 166, 0.7)',  // Gray
            ];

            const datasets = activityDatasets.map((dataset, index) => ({
                label: dataset.label,
                data: dataset.data,
                backgroundColor: colors[index % colors.length],
                borderColor: colors[index % colors.length].replace('0.7', '1'),
                borderWidth: 1
            }));

            new Chart(activityCtx, {
                type: 'bar',
                data: {
                    labels: activityLabels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                padding: 10,
                                font: { size: 11 }
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                footer: function (tooltipItems) {
                                    let total = 0;
                                    tooltipItems.forEach(item => total += item.parsed.y);
                                    return 'Total: ' + total;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            grid: { display: false }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            grid: { color: '#f0f0f0' },
                            ticks: {
                                stepSize: 1,
                                precision: 0
                            }
                        }
                    }
                }
            });

            // Date range selector event listener
            document.getElementById('activityDateRange').addEventListener('change', function () {
                const days = this.value;
                const url = new URL(window.location.href);
                url.searchParams.set('activity_days', days);
                window.location.href = url.toString();
            });
        </script>

        <!-- Firebase Scripts -->
        <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
        <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-messaging.js"></script>

        <!-- TODO LIST SCRIPTS -->
        <script>
            const taskInput = document.getElementById('taskInput');
            const addTaskButton = document.getElementById('addTask');
            const todoList = document.getElementById('todoList');
            const clearAll = document.getElementById('clearAll');
            let tasks = [];

            function fetchTasks() {
                fetch('/todo-lists').then(response => response.json()).then(data => {
                    tasks = data;
                    renderTasks();
                });
            }

            taskInput.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); addTaskButton.click(); } });

            function renderTasks() {
                todoList.innerHTML = '';
                tasks.forEach((task, index) => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item d-flex justify-content-between align-items-center mb-2 shadow-sm rounded';
                    li.draggable = true;
                    li.dataset.id = task.id;

                    // Reminder Display logic
                    let reminderHtml = '';
                    let badgeClass = 'bg-info';
                    if (task.reminder_at) {
                        const reminderDate = new Date(task.reminder_at); // Implicitly treats as local if YYYY-MM-DD HH:MM:SS
                        const now = new Date();
                        const isOverdue = reminderDate < now && !task.completed;
                        const dateStr = reminderDate.toLocaleString([], { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

                        if (isOverdue) badgeClass = 'bg-danger';
                        else if (task.completed) badgeClass = 'bg-secondary';

                        reminderHtml = `<span class="badge ${badgeClass} ms-2" title="Reminder: ${task.reminder_at}">
                                        <i class="bx bx-time"></i> ${dateStr}
                                    </span>`;
                    }

                    li.innerHTML = `
                                    <div class="d-flex align-items-center flex-grow-1">
                                        <i class="bx bx-grid-vertical text-muted me-2 handle" style="cursor: grab;"></i>
                                        <input type="checkbox" ${task.completed ? 'checked' : ''} data-id="${task.id}" class="me-3 toggleTask form-check-input" style="cursor: pointer; width: 1.2em; height: 1.2em;" />
                                        <div class="d-flex flex-column">
                                            <span class="${task.completed ? 'text-decoration-line-through text-muted' : 'fw-bold'} task-text">${task.text}</span>
                                            <div class="small mt-1">${reminderHtml}</div>
                                        </div>
                                    </div>
                                    <div class="row-btn ms-2">
                                        <button class="btn btn-warning btn-sm editTask p-1 me-1" title="Edit" data-id="${task.id}"><i class="bx bx-edit"></i></button>
                                        <button class="btn btn-danger btn-sm deleteTask p-1" title="Delete" data-id="${task.id}"><i class="bx bx-trash"></i></button>
                                    </div>`;



                    // Drag and Drop Events
                    li.addEventListener('dragstart', (e) => {
                        li.classList.add('dragging');
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/plain', index);
                    });

                    li.addEventListener('dragend', () => {
                        li.classList.remove('dragging');
                        document.querySelectorAll('.dragging').forEach(el => el.classList.remove('dragging'));

                        // Sync new order
                        const newOrder = [...todoList.querySelectorAll('li')].map(item => item.dataset.id);
                        fetch('/todo-lists/reorder', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ order: newOrder, _token: '{{ csrf_token() }}' })
                        });
                    });

                    li.addEventListener('dragover', (e) => {
                        e.preventDefault(); // allow drop
                        const draggingItem = document.querySelector('.dragging');
                        // Get all *other* draggable items to determine position
                        const siblings = [...todoList.querySelectorAll('li:not(.dragging)')];
                        const nextSibling = siblings.find(sibling => {
                            return e.clientY <= sibling.getBoundingClientRect().top + sibling.offsetHeight / 2;
                        });
                        todoList.insertBefore(draggingItem, nextSibling);
                    });

                    todoList.appendChild(li);
                });
            }

            function addTask() {
                const taskValue = taskInput.value.trim();
                if (taskValue) {
                    const task = { text: taskValue, _token: '{{ csrf_token() }}', completed: false };
                    fetch('/manage-todolist-item', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(task)
                    }).then(response => response.json()).then(newTask => {
                        tasks.unshift(newTask);
                        taskInput.value = '';
                        renderTasks();
                    });
                }
            }

            addTaskButton.addEventListener('click', addTask);

            // Completion Toggle
            todoList.addEventListener('change', (e) => {
                if (e.target.classList.contains('toggleTask')) {
                    const id = e.target.dataset.id;
                    const completed = e.target.checked;
                    fetch(`/manage-todolist-item/${id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ completed, _token: '{{ csrf_token() }}' })
                    }).then(() => fetchTasks());
                }
            });

            // Initialize Firebase
            const firebaseConfig = {
                apiKey: "{{ env('FIREBASE_API_KEY') }}",
                authDomain: "{{ env('FIREBASE_AUTH_DOMAIN') }}",
                projectId: "{{ env('FIREBASE_PROJECT_ID') }}",
                storageBucket: "{{ env('FIREBASE_STORAGE_BUCKET') }}",
                messagingSenderId: "{{ env('FIREBASE_MESSAGING_SENDER_ID') }}",
                appId: "{{ env('FIREBASE_APP_ID') }}",
                measurementId: "{{ env('FIREBASE_MEASUREMENT_ID') }}"
            };

            // Initialize Firebase only if config is present
            if (firebaseConfig.apiKey) {
                console.log("Firebase config found. Initializing...");
                console.log("Current Permission Status:", Notification.permission);

                try {
                    firebase.initializeApp(firebaseConfig);
                    const messaging = firebase.messaging();
                    console.log("Firebase Messaging initialized.");

                    // Request Permission and Get Token
                    const getToken = () => {
                        messaging.getToken().then((currentToken) => {
                            if (currentToken) {
                                console.log('FCM Token generated:', currentToken);
                                // Save token to database
                                fetch('/save-token', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ token: currentToken, _token: '{{ csrf_token() }}' })
                                })
                                    .then(res => res.json())
                                    .then(data => console.log('Token saved to server:', data))
                                    .catch(err => console.error('Error sending token to server:', err));
                            } else {
                                console.warn('No registration token available. Request permission to generate one.');
                            }
                        }).catch((err) => {
                            console.error('An error occurred while retrieving token: ', err);
                        });
                    };

                    if (Notification.permission === 'default') {
                        console.log("Requesting permission...");
                        messaging.requestPermission().then(() => {
                            console.log('Notification permission granted.');
                            getToken();
                        }).catch((err) => {
                            console.error('Unable to get permission to notify.', err);
                        });
                    } else if (Notification.permission === 'granted') {
                        console.log("Permission already granted.");
                        getToken();
                    } else {
                        console.error("Notification permission denied.");
                    }

                    // Handle incoming messages
                    messaging.onMessage(function (payload) {
                        console.log("Foreground Message received: ", payload);
                        const notificationTitle = payload.notification.title;
                        const notificationOptions = {
                            body: payload.notification.body,
                            icon: '/favicon.ico',
                            data: payload.data
                        };

                        if (Notification.permission === 'granted') {
                            navigator.serviceWorker.ready.then(function (registration) {
                                registration.showNotification(notificationTitle, notificationOptions);
                            }).catch(function (err) {
                                console.warn("Service worker not ready, using standard Notification", err);
                                new Notification(notificationTitle, notificationOptions);
                            });
                        }
                    });
                } catch (err) {
                    console.error("Firebase initialization failed:", err);
                }
            } else {
                console.warn("Firebase API Key missing from config.");
            }

            // Event Listener for Todo List Actions
            todoList.addEventListener('click', async (e) => {
                // Delete Action
                const deleteBtn = e.target.closest('.deleteTask');
                if (deleteBtn) {
                    e.preventDefault();
                    const id = deleteBtn.dataset.id;
                    swal({
                        title: "Delete this task?",
                        icon: "warning",
                        buttons: ["Cancel", "Yes, delete"],
                        dangerMode: true
                    }).then(async (willDelete) => {
                        if (!willDelete) return;
                        const res = await fetch(`/manage-todolist-item/${id}`, {
                            method: 'DELETE',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ _token: '{{ csrf_token() }}' })
                        });
                        if (res.ok) { fetchTasks(); }
                    });
                    return;
                }

                // Edit Action
                const editBtn = e.target.closest('.editTask');
                if (editBtn) {
                    e.preventDefault();
                    const li = editBtn.closest('li');
                    const textSpan = li.querySelector('.task-text');
                    const currentText = textSpan ? textSpan.innerText : '';
                    const id = editBtn.dataset.id;

                    // Find existing reminder date from badge title or dataset if we added it
                    // Better approach: find task in local array 'tasks'
                    const task = tasks.find(t => t.id == id);
                    const currentReminder = task && task.reminder_at ? task.reminder_at : '';

                    // Avoid double input creation
                    if (li.querySelector('.edit-container')) return;

                    // Create container for edit form
                    const editContainer = document.createElement('div');
                    editContainer.className = 'edit-container flex-grow-1 me-2';

                    // Text Input
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.className = 'form-control form-control-sm mb-1';
                    input.value = currentText;
                    input.placeholder = 'Task description';

                    // Date Input Group
                    const dateGroup = document.createElement('div');
                    dateGroup.className = 'input-group input-group-sm';

                    const dateInput = document.createElement('input');
                    dateInput.type = 'datetime-local';
                    dateInput.className = 'form-control';
                    // Format for datetime-local: YYYY-MM-DDTHH:MM
                    if (currentReminder) {
                        // Format: YYYY-MM-DD HH:MM:SS -> YYYY-MM-DDTHH:MM
                        dateInput.value = currentReminder.replace(' ', 'T').slice(0, 16);
                    }

                    // Clear Reminder Button
                    const clearBtn = document.createElement('button');
                    clearBtn.className = 'btn btn-outline-secondary';
                    clearBtn.type = 'button';
                    clearBtn.innerHTML = '<i class="bx bx-x"></i>';
                    clearBtn.title = 'Clear Reminder';
                    clearBtn.onclick = () => { dateInput.value = ''; };

                    // Save Button
                    const saveBtn = document.createElement('button');
                    saveBtn.className = 'btn btn-success ms-1';
                    saveBtn.type = 'button';
                    saveBtn.innerHTML = '<i class="bx bx-check"></i>';
                    saveBtn.title = 'Save';

                    // Cancel Button
                    const cancelBtn = document.createElement('button');
                    cancelBtn.className = 'btn btn-danger ms-1';
                    cancelBtn.type = 'button';
                    cancelBtn.innerHTML = '<i class="bx bx-x-circle"></i>';
                    cancelBtn.title = 'Cancel';

                    dateGroup.appendChild(dateInput);
                    dateGroup.appendChild(clearBtn);

                    editContainer.appendChild(input);
                    editContainer.appendChild(dateGroup);

                    // Action Buttons Container (replace existing buttons)
                    const actionContainer = document.createElement('div');
                    actionContainer.className = 'd-flex';
                    actionContainer.appendChild(saveBtn);
                    actionContainer.appendChild(cancelBtn);

                    // Hide original content
                    const originalContent = li.querySelector('.d-flex.align-items-center.flex-grow-1');
                    const originalButtons = li.querySelector('.row-btn');

                    originalContent.style.display = 'none';
                    originalButtons.style.display = 'none';

                    li.insertBefore(editContainer, originalButtons);
                    li.appendChild(actionContainer);

                    input.focus();

                    // Save Function
                    const saveEdit = async () => {
                        const newText = input.value.trim();
                        const reminderAt = dateInput.value; // Send local time string (e.g. T17:00)

                        if (newText) {
                            try {
                                const res = await fetch(`/manage-todolist-item/${id}`, {
                                    method: 'PUT',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({
                                        text: newText,
                                        completed: task.completed,
                                        reminder_at: reminderAt,
                                        _token: '{{ csrf_token() }}'
                                    })
                                });
                                if (res.ok) {
                                    fetchTasks();
                                } else {
                                    alert('Failed to update task');
                                    renderTasks();
                                }
                            } catch (error) {
                                console.error('Error updating task:', error);
                                renderTasks();
                            }
                        } else {
                            renderTasks();
                        }
                    };

                    // Event Listeners
                    saveBtn.onclick = saveEdit;
                    cancelBtn.onclick = renderTasks;

                    input.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') saveEdit();
                        if (e.key === 'Escape') renderTasks();
                    });

                    dateInput.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') saveEdit();
                        if (e.key === 'Escape') renderTasks();
                    });
                }
            });
        </script>

        <!-- UI MODALS -->

        <!-- MY TODO LIST MODAL -->
        <div class="modal fade" id="todoListModal" tabindex="-1" aria-labelledby="todoListModalLabel" aria-hidden="true"
            style="z-index: 99999;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow-lg border-0" style="border-radius: 12px; background: #fff;">
                    <div class="modal-header bg-white border-bottom-0 pb-0">
                        <h5 class="modal-title font-weight-bold text-dark" id="todoListModalLabel">
                            <i class="bx bx-list-check me-2 text-primary" style="font-size: 1.5rem;"></i>My Todo List
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <ul class="list-group border-0" id="todoList" style="max-height: 55vh; overflow-y: auto;">
                            <!-- Tasks dynamically loaded -->
                        </ul>
                    </div>
                    <div class="modal-footer border-top-0 bg-white p-4">
                        <div class="input-group shadow-sm"
                            style="border-radius: 8px; overflow: hidden; border: 1px solid #dee2e6;">
                            <input type="text" id="taskInput" class="form-control border-0 px-3" placeholder="Add a new task..."
                                style="height: 45px;" />
                            <button id="addTask" class="btn btn-primary px-3 border-0"><i class="bx bx-plus"
                                    style="font-size: 1.2rem;"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .bg-soft-info {
                background-color: rgba(13, 202, 240, 0.1);
            }

            .modal-backdrop {
                z-index: 99980 !important;
            }

            .modal {
                z-index: 99999 !important;
            }

            .modal-content {
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2) !important;
            }

            .badge.bg-soft-info {
                color: #0dcaf0 !important;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof fetchTasks === 'function') {
                    fetchTasks();
                }
            });
        </script>
@endsection