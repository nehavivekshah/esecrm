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

        <section class="task__section">
            @include('inc.header', ['title' => 'Dashboard'])

            <div class="dash-container">

                @if(Auth::user()->role != 'master')
                    {{-- STAT WIDGETS ROW --}}
                    <div class="row g-3 mb-4">

                        <div class="col-xl-3 col-md-6">
                            <a href="/invoices" class="dash-stat-card text-decoration-none" style="--card-accent: #006666;">
                                <div class="dash-stat-icon" style="background:rgba(0,102,102,0.10); color:#006666;">
                                    <i class="bx bx-receipt"></i>
                                </div>
                                <div class="dash-stat-body">
                                    <span class="dash-stat-label">Outstanding Invoices</span>
                                    <div class="dash-stat-value">₹{{ number_format($outstandingInvoices, 0) }}</div>
                                    <span class="dash-stat-badge" style="background:#e6f4f1; color:#006666;">
                                        <i class="bx bx-time-five"></i> Pending Payment
                                    </span>
                                </div>
                            </a>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <a href="/proposals" class="dash-stat-card text-decoration-none" style="--card-accent: #ea4335;">
                                <div class="dash-stat-icon" style="background:rgba(234,67,53,0.10); color:#ea4335;">
                                    <i class="bx bx-file-blank"></i>
                                </div>
                                <div class="dash-stat-body">
                                    <span class="dash-stat-label">Pending Proposals</span>
                                    <div class="dash-stat-value">{{ $pendingProposals }}</div>
                                    <span class="dash-stat-badge" style="background:#fdecea; color:#ea4335;">
                                        <i class="bx bx-error-circle"></i> Awaiting Action
                                    </span>
                                </div>
                            </a>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <a href="/task" class="dash-stat-card text-decoration-none" style="--card-accent: #fbbc04;">
                                <div class="dash-stat-icon" style="background:rgba(251,188,4,0.10); color:#f9a825;">
                                    <i class="bx bx-task"></i>
                                </div>
                                <div class="dash-stat-body">
                                    <span class="dash-stat-label">Assigned Tasks</span>
                                    <div class="dash-stat-value">{{ $myPendingTasks }}</div>
                                    <span class="dash-stat-badge" style="background:#fffde7; color:#f57f17;">
                                        <i class="bx bx-list-ul"></i> Your Queue
                                    </span>
                                </div>
                            </a>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <a href="/leads" class="dash-stat-card text-decoration-none" style="--card-accent: #34a853;">
                                <div class="dash-stat-icon" style="background:rgba(52,168,83,0.10); color:#34a853;">
                                    <i class="bx bx-trending-up"></i>
                                </div>
                                <div class="dash-stat-body">
                                    <span class="dash-stat-label">Total Leads</span>
                                    <div class="dash-stat-value">{{ $totalLeads }}</div>
                                    <span class="dash-stat-badge" style="background:#e8f5e9; color:#2e7d32;">
                                        <i class="bx bx-up-arrow-alt"></i> Sales Pipeline
                                    </span>
                                </div>
                            </a>
                        </div>

                    </div>
                @endif

                {{-- QUICK ACTIONS --}}
                <div class="dash-section mb-4">
                    <div class="dash-section-header">
                        <div class="dash-section-icon" style="background:rgba(0,102,102,0.08); color:#006666;">
                            <i class="bx bx-bolt-circle"></i>
                        </div>
                        <h6 class="dash-section-title">Quick Actions</h6>
                    </div>
                    <div class="quick-actions-grid">
                        @if(Auth::user()->role == 'master')
                            <a href="/companies" class="action-tile">
                                <i class="bx bx-building" style="background:rgba(0,102,102,0.08); color:#006666;"></i>
                                <div class="action-tile-body">
                                    <h6>Compnanies</h6>
                                    <span>Manage</span>
                                </div>
                            </a>
                            <a href="/enquiries" class="action-tile" style="--tile-color: #34a853;">
                                <i class="bx bx-mail-send" style="background:rgba(52,168,83,0.08); color:#34a853;"></i>
                                <div class="action-tile-body">
                                    <h6>Enquiries</h6>
                                    <span>Leads</span>
                                </div>
                            </a>
                            <a href="/subscriptions" class="action-tile" style="--tile-color: #fbbc04;">
                                <i class="bx bx-crown" style="background:rgba(251,188,4,0.08); color:#fbbc04;"></i>
                                <div class="action-tile-body">
                                    <h6>Subscriptions</h6>
                                    <span>Plans</span>
                                </div>
                            </a>
                            <a href="/support" class="action-tile" style="--tile-color: #1a73e8;">
                                <i class="bx bx-help-circle" style="background:rgba(26,115,232,0.08); color:#1a73e8;"></i>
                                <div class="action-tile-body">
                                    <h6>Support</h6>
                                    <span>Tickets</span>
                                </div>
                            </a>
                        @else
                            @if(in_array('leads', $roleArray) || in_array('All', $roleArray) || (Auth::user()->role == '0'))
                                <a href="/leads" class="action-tile">
                                    <i class="bx bx-filter-alt" style="background:rgba(0,102,102,0.08); color:#006666;"></i>
                                    <div class="action-tile-body">
                                        <h6>Leads Pipeline</h6>
                                        <span>{{ count($leads ?? []) }}</span>
                                    </div>
                                </a>
                            @endif
                            @if(in_array('clients', $roleArray) || in_array('All', $roleArray) || (Auth::user()->role == '0'))
                                <a href="/clients" class="action-tile" style="--tile-color: #3b82f6;">
                                    <i class="bx bx-group" style="background:rgba(59,130,246,0.08); color:#3b82f6;"></i>
                                    <div class="action-tile-body">
                                        <h6>Client Base</h6>
                                        <span>{{ count($clients ?? []) }}</span>
                                    </div>
                                </a>
                                <a href="/recoveries" class="action-tile" style="--tile-color: #f59e0b;">
                                    <i class="bx bx-coin-stack" style="background:rgba(245,158,11,0.08); color:#f59e0b;"></i>
                                    <div class="action-tile-body">
                                        <h6>Recovery Log</h6>
                                        <span>{{ count($recoveries ?? []) }}</span>
                                    </div>
                                </a>
                            @endif
                            @if(in_array('users', $roleArray) || in_array('All', $roleArray) || (Auth::user()->role == '0'))
                                <a href="/users" class="action-tile" style="--tile-color: #f43f5e;">
                                    <i class="bx bx-user" style="background:rgba(244,63,94,0.08); color:#f43f5e;"></i>
                                    <div class="action-tile-body">
                                        <h6>Team Users</h6>
                                        <span>{{ count($users ?? []) }}</span>
                                    </div>
                                </a>
                            @endif
                            {{-- My Tasks shortcut --}}
                            <a href="#" class="action-tile" style="--tile-color: #8b5cf6;" data-bs-toggle="modal" data-bs-target="#todoListModal">
                                <i class="bx bx-check-double" style="background:rgba(139,92,246,0.08); color:#8b5cf6;"></i>
                                <div class="action-tile-body">
                                    <h6>My Tasks</h6>
                                    <span style="color:#8b5cf6;">Todo</span>
                                </div>
                            </a>
                        @endif
                    </div>
                </div>

                @if(Auth::user()->role != 'master')
                    {{-- CRM ALERTS --}}
                    <div class="dash-section mb-4">
                        <div class="dash-section-header">
                            <div class="dash-section-icon" style="background:rgba(234,67,53,0.08); color:#ea4335;">
                                <i class="bx bxs-zap"></i>
                            </div>
                            <h6 class="dash-section-title">Action Required &amp; CRM Alerts</h6>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="dash-card">
                                    <div class="dash-card-header">
                                        <i class="bx bx-history" style="color:#ea4335;"></i>
                                        <span>Overdue Follow-ups</span>
                                        <span class="dash-badge ms-auto" style="background:#fdecea; color:#ea4335;">{{ count($overdueLeadsList) }}</span>
                                        @if(count($overdueLeadsList))
                                            <a href="/leads" class="ms-2 text-decoration-none" style="font-size:0.70rem; color:#006666; font-weight:600; white-space:nowrap;">View all →</a>
                                        @endif
                                    </div>
                                    <div class="dash-list">
                                        @forelse($overdueLeadsList as $olead)
                                            <a href="/manage-lead?id={{ $olead->id }}" class="dash-list-item">
                                                <div style="min-width:0; flex:1;">
                                                    <div class="dash-list-title">{{ $olead->name }}</div>
                                                    <div class="dash-list-sub" style="color:#ea4335;"><i class="bx bx-time-five"></i> Missed: {{ \Carbon\Carbon::parse($olead->next_date)->diffForHumans() }}</div>
                                                </div>
                                                <i class="bx bx-chevron-right dash-list-arrow"></i>
                                            </a>
                                        @empty
                                            <div class="dash-list-empty"><i class="bx bx-check-circle"></i><span>No overdue follow-ups! Great job.</span></div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="dash-card">
                                    <div class="dash-card-header">
                                        <i class="bx bx-time-five" style="color:#fbbc04;"></i>
                                        <span>Proposals Near Expiry</span>
                                        <span class="dash-badge ms-auto" style="background:#fffde7; color:#f57f17;">{{ count($expiringProposals) }}</span>
                                        @if(count($expiringProposals))
                                            <a href="/proposals" class="ms-2 text-decoration-none" style="font-size:0.70rem; color:#006666; font-weight:600; white-space:nowrap;">View all →</a>
                                        @endif
                                    </div>
                                    <div class="dash-list">
                                        @forelse($expiringProposals as $eprop)
                                            <a href="/manage-proposal?id={{ $eprop->id }}" class="dash-list-item">
                                                <div style="min-width:0; flex:1;">
                                                    <div class="dash-list-title">{{ $eprop->subject }}</div>
                                                    <div class="dash-list-sub" style="color:#f57f17;"><i class="bx bx-calendar"></i> Expires {{ \Carbon\Carbon::parse($eprop->open_till)->diffForHumans() }}</div>
                                                </div>
                                                <i class="bx bx-chevron-right dash-list-arrow"></i>
                                            </a>
                                        @empty
                                            <div class="dash-list-empty"><i class="bx bx-check-circle"></i><span>All proposals are up to date.</span></div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- CHARTS + ACTIVITY FEED --}}
                <div class="dash-section mb-4">
                    <div class="dash-section-header">
                        <div class="dash-section-icon" style="background:rgba(26,115,232,0.08); color:#1a73e8;">
                            <i class="bx bx-bar-chart-square"></i>
                        </div>
                        <h6 class="dash-section-title">Analytics &amp; Live Feed</h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="dash-card h-100">
                                        <div class="dash-card-header">
                                            <i class="bx bx-line-chart" style="color:#34a853;"></i>
                                            <span>Revenue Growth</span>
                                            <span class="dash-badge ms-auto" style="background:#e8f5e9; color:#2e7d32;">{{ date('Y') }}</span>
                                        </div>
                                        <div class="dash-chart-container">
                                            <canvas id="revenueChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="dash-card h-100">
                                        <div class="dash-card-header">
                                            <i class="bx bx-bar-chart-alt-2" style="color:#1a73e8;"></i>
                                            <span>Activity Flux</span>
                                            <select id="activityDateRange" class="ms-auto" style="font-size:0.72rem; border:1px solid #dadce0; border-radius:20px; padding:3px 10px; color:#3c4043; outline:none; background:#fff; cursor:pointer;">
                                                <option value="7" {{ $selectedActivityDays == 7 ? 'selected' : '' }}>7 Days</option>
                                                <option value="30"{{ $selectedActivityDays == 30 ? 'selected' : '' }}>30 Days</option>
                                                <option value="90"{{ $selectedActivityDays == 90 ? 'selected' : '' }}>90 Days</option>
                                            </select>
                                        </div>
                                        <div class="dash-chart-container">
                                            <canvas id="activityFlowChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="dash-card" style="max-height:376px; display:flex; flex-direction:column;">
                                <div class="dash-card-header">
                                    <i class="bx bx-pulse" style="color:#ea4335;"></i>
                                    <span>Live Activities</span>
                                    <span class="dash-badge ms-auto" style="background:#fdecea; color:#ea4335; font-size:0.60rem; letter-spacing:0.5px;">REAL-TIME</span>
                                </div>
                                <div class="activity-log" style="flex:1; overflow-y:auto;">
                                    @forelse(collect($activities ?? [])->take(20) as $activity)
                                        <div class="activity-feed-item">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <span class="activity-feed-user">{{ $activity->user_name ?? 'System' }}</span>
                                                <span class="activity-feed-time">{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</span>
                                            </div>
                                            <p class="activity-feed-text mb-1">{{ $activity->type }} &mdash; {{ $activity->description ?? 'Action recorded' }}</p>
                                            @if(isset($activity->subject))
                                                <span class="dash-badge" style="background:rgba(0,102,102,0.08); color:#006666; font-size:0.60rem;">{{ strtoupper($activity->subject) }}</span>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="dash-list-empty" style="padding:40px 0;">
                                            <i class="bx bx-news" style="font-size:2.5rem; color:#dadce0;"></i>
                                            <span style="color:#9aa0a6;">Waiting for activities...</span>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- end .dash-container --}}

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


        <!-- UI MODALS -->







        <!-- MY TODO LIST MODAL — Enhanced -->

@endsection

