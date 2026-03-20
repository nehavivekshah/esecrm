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

                {{-- STAT WIDGETS ROW --}}
                <div class="row g-3 mb-4">

                    <div class="col-xl-3 col-md-6">
                        <div class="dash-stat-card" style="--card-accent: #006666;">
                            <div class="dash-stat-icon" style="background:rgba(0,102,102,0.10); color:#006666;">
                                <i class="bx bx-receipt"></i>
                            </div>
                            <div class="dash-stat-body">
                                <span class="dash-stat-label">Outstanding Invoices</span>
                                <div class="dash-stat-value">₹{{ number_format($outstandingInvoices, 0) }}</div>
                                <span class="dash-stat-badge" style="background:#e6f4f1; color:#006666;">Pending Payment</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="dash-stat-card" style="--card-accent: #ea4335;">
                            <div class="dash-stat-icon" style="background:rgba(234,67,53,0.10); color:#ea4335;">
                                <i class="bx bx-file-blank"></i>
                            </div>
                            <div class="dash-stat-body">
                                <span class="dash-stat-label">Pending Proposals</span>
                                <div class="dash-stat-value">{{ $pendingProposals }}</div>
                                <span class="dash-stat-badge" style="background:#fdecea; color:#ea4335;">Awaiting Action</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="dash-stat-card" style="--card-accent: #fbbc04;">
                            <div class="dash-stat-icon" style="background:rgba(251,188,4,0.10); color:#f9a825;">
                                <i class="bx bx-task"></i>
                            </div>
                            <div class="dash-stat-body">
                                <span class="dash-stat-label">Assigned Tasks</span>
                                <div class="dash-stat-value">{{ $myPendingTasks }}</div>
                                <span class="dash-stat-badge" style="background:#fffde7; color:#f57f17;">Your Queue</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="dash-stat-card" style="--card-accent: #34a853;">
                            <div class="dash-stat-icon" style="background:rgba(52,168,83,0.10); color:#34a853;">
                                <i class="bx bx-trending-up"></i>
                            </div>
                            <div class="dash-stat-body">
                                <span class="dash-stat-label">Total Leads</span>
                                <div class="dash-stat-value">{{ $totalLeads }}</div>
                                <span class="dash-stat-badge" style="background:#e8f5e9; color:#2e7d32;">Sales Pipeline</span>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- QUICK ACTIONS --}}
                <div class="dash-section mb-4">
                    <div class="dash-section-header">
                        <div class="dash-section-icon" style="background:rgba(0,102,102,0.08); color:#006666;">
                            <i class="bx bx-bolt-circle"></i>
                        </div>
                        <h6 class="dash-section-title">Quick Actions</h6>
                    </div>
                    <div class="quick-actions-grid">
                        @if(in_array('leads', $roleArray) || in_array('All', $roleArray) || (Auth::user()->role == '0'))
                            <a href="/leads" class="action-tile">
                                <i class="bx bx-filter-alt"></i>
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
                    </div>
                </div>
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
                                </div>
                                <div class="dash-list">
                                    @forelse($overdueLeadsList as $olead)
                                        <a href="/manage-lead?id={{ $olead->id }}" class="dash-list-item">
                                            <div>
                                                <div class="dash-list-title">{{ $olead->name }}</div>
                                                <small class="dash-list-sub" style="color:#ea4335;"><i class="bx bx-time-five"></i> Missed: {{ \Carbon\Carbon::parse($olead->next_date)->diffForHumans() }}</small>
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
                                </div>
                                <div class="dash-list">
                                    @forelse($expiringProposals as $eprop)
                                        <a href="/manage-proposal?id={{ $eprop->id }}" class="dash-list-item">
                                            <div>
                                                <div class="dash-list-title">{{ $eprop->subject }}</div>
                                                <small class="dash-list-sub" style="color:#f57f17;"><i class="bx bx-calendar"></i> Expires {{ \Carbon\Carbon::parse($eprop->open_till)->diffForHumans() }}</small>
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

                {{-- CHARTS + ACTIVITY FEED --}}
                <div class="dash-section mb-4">
                    <div class="dash-section-header">
                        <div class="dash-section-icon" style="background:rgba(26,115,232,0.08); color:#1a73e8;">
                            <i class="bx bx-bar-chart-square"></i>
                        </div>
                        <h6 class="dash-section-title">Analytics & Live Feed</h6>
                    </div>
                <div class="row g-3">
                    <div class="col-lg-8">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="dash-card h-100">
                                    <div class="dash-card-header">
                                        <i class="bx bx-line-chart" style="color:#34a853;"></i>
                                        <span>Revenue Growth</span>
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
                                        <select id="activityDateRange" class="form-select form-select-sm ms-auto" style="width:auto; font-size:0.72rem; border:1px solid #dadce0; border-radius:20px; padding:3px 10px; color:#3c4043;">
                                            <option value="7" {{ $selectedActivityDays == 7 ? 'selected' : '' }}>7 Days</option>
                                            <option value="30" {{ $selectedActivityDays == 30 ? 'selected' : '' }}>30 Days</option>
                                            <option value="90" {{ $selectedActivityDays == 90 ? 'selected' : '' }}>90 Days</option>
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
                        <div class="dash-card h-100" style="max-height:460px; display:flex; flex-direction:column;">
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
                                            <span class="dash-badge" style="background:rgba(0,102,102,0.08); color:#006666; font-size:0.60rem; font-weight:700; letter-spacing:0.4px;">{{ strtoupper($activity->subject) }}</span>
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
                </div>{{-- end .row charts --}}
                </div>{{-- end .dash-section analytics --}}

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
        <div class="modal fade" id="todoListModal" tabindex="-1" aria-labelledby="todoListModalLabel" aria-hidden="true" style="z-index:99999;">
            <div class="modal-dialog modal-dialog-centered todo-modal-dialog">
                <div class="modal-content todo-modal-content">

                    <!-- Header -->
                    <div class="todo-modal-header">
                        <div class="todo-header-left">
                            <div class="todo-header-icon">
                                <i class="bx bx-check-double"></i>
                            </div>
                            <div>
                                <h5 class="todo-modal-title">My Todo List</h5>
                                <p class="todo-modal-subtitle" id="todoSubtitle">Loading tasks…</p>
                            </div>
                        </div>
                        <button type="button" class="todo-close-btn" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bx bx-x"></i>
                        </button>
                    </div>

                    <!-- Progress Bar -->
                    <div class="todo-progress-wrap">
                        <div class="todo-progress-bar">
                            <div class="todo-progress-fill" id="todoProgressFill" style="width:0%"></div>
                        </div>
                        <span class="todo-progress-label" id="todoProgressLabel">0%</span>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="todo-filters">
                        <button class="todo-filter-btn active" data-filter="all">All</button>
                        <button class="todo-filter-btn" data-filter="pending">Pending</button>
                        <button class="todo-filter-btn" data-filter="done">Done</button>
                    </div>

                    <!-- Task List -->
                    <div class="todo-list-wrap">
                        <ul id="todoList" class="todo-list"></ul>
                        <div class="todo-empty-state" id="todoEmptyState" style="display:none;">
                            <i class="bx bx-clipboard"></i>
                            <p>No tasks here. Add one below!</p>
                        </div>
                    </div>

                    <!-- Add Task Footer -->
                    <div class="todo-footer">
                        <div class="todo-input-row">
                            <input type="text" id="taskInput" class="todo-input" placeholder="Add a new task and press Enter…" autocomplete="off" />
                            <button id="addTask" class="todo-add-btn" title="Add Task">
                                <i class="bx bx-plus"></i>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <style>
            /* ===== TODO MODAL ===== */
            .todo-modal-dialog { max-width: 500px; }

            .todo-modal-content {
                border: none;
                border-radius: 18px;
                overflow: hidden;
                box-shadow: 0 24px 60px rgba(0,0,0,0.18) !important;
                display: flex;
                flex-direction: column;
            }

            /* Header */
            .todo-modal-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 20px 22px 14px;
                background: linear-gradient(135deg, #006666 0%, #009688 100%);
            }
            .todo-header-left { display: flex; align-items: center; gap: 12px; }
            .todo-header-icon {
                width: 40px; height: 40px; border-radius: 12px;
                background: rgba(255,255,255,0.18);
                display: flex; align-items: center; justify-content: center;
                font-size: 1.3rem; color: #fff;
            }
            .todo-modal-title { margin: 0; font-size: 1rem; font-weight: 700; color: #fff; }
            .todo-modal-subtitle { margin: 0; font-size: 0.72rem; color: rgba(255,255,255,0.72); }
            .todo-close-btn {
                width: 32px; height: 32px; border-radius: 50%;
                background: rgba(255,255,255,0.15); border: none; cursor: pointer;
                display: flex; align-items: center; justify-content: center;
                color: #fff; font-size: 1.2rem; transition: background 0.2s;
            }
            .todo-close-btn:hover { background: rgba(255,255,255,0.28); }

            /* Progress */
            .todo-progress-wrap {
                display: flex; align-items: center; gap: 10px;
                padding: 10px 22px 0;
                background: linear-gradient(135deg, #006666 0%, #009688 100%);
            }
            .todo-progress-bar {
                flex: 1; height: 5px; border-radius: 99px;
                background: rgba(255,255,255,0.25); overflow: hidden;
            }
            .todo-progress-fill {
                height: 100%; border-radius: 99px;
                background: #fff; transition: width 0.4s ease;
            }
            .todo-progress-label {
                font-size: 0.70rem; font-weight: 700; color: rgba(255,255,255,0.85);
                min-width: 30px; text-align: right;
            }

            /* Filters */
            .todo-filters {
                display: flex; gap: 6px; padding: 14px 22px 10px;
                background: #f8fafb; border-bottom: 1px solid #eef0f2;
            }
            .todo-filter-btn {
                padding: 4px 14px; border-radius: 20px; border: 1.5px solid #e0e4e8;
                background: transparent; color: #6b7280; font-size: 0.75rem;
                font-weight: 600; cursor: pointer; transition: all 0.18s;
            }
            .todo-filter-btn.active {
                background: #006666; color: #fff; border-color: #006666;
            }
            .todo-filter-btn:hover:not(.active) { background: #f0f5f5; color: #006666; border-color: #006666; }

            /* List Wrap */
            .todo-list-wrap {
                padding: 10px 16px; max-height: 48vh; overflow-y: auto;
                background: #fff;
            }
            .todo-list { list-style: none; margin: 0; padding: 0; }

            /* Task Item */
            .todo-item {
                display: flex; align-items: center; gap: 10px;
                padding: 10px 12px; border-radius: 10px;
                border: 1.5px solid #eef0f2; margin-bottom: 7px;
                background: #fff; cursor: grab; transition: all 0.2s;
            }
            .todo-item:hover { border-color: #00666640; box-shadow: 0 2px 8px rgba(0,102,102,0.08); }
            .todo-item.done { background: #f6faf8; opacity: 0.72; }
            .todo-item.dragging { opacity: 0.45; border-style: dashed; }

            /* Drag handle */
            .todo-drag { color: #c0c8d0; font-size: 1.1rem; flex-shrink: 0; cursor: grab; }

            /* Custom Checkbox */
            .todo-check {
                width: 18px; height: 18px; border-radius: 6px; border: 2px solid #c0c8d0;
                appearance: none; -webkit-appearance: none; cursor: pointer;
                flex-shrink: 0; transition: all 0.2s; position: relative; background: #fff;
            }
            .todo-check:checked {
                background: #006666; border-color: #006666;
            }
            .todo-check:checked::after {
                content: ''; position: absolute; top: 2px; left: 4px;
                width: 5px; height: 9px; border: 2px solid #fff;
                border-top: none; border-left: none; transform: rotate(45deg);
            }

            /* Text */
            .todo-text-wrap { flex: 1; min-width: 0; }
            .todo-task-text {
                font-size: 0.875rem; font-weight: 500; color: #1f2937;
                white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;
            }
            .todo-item.done .todo-task-text {
                text-decoration: line-through; color: #9ca3af;
            }
            .todo-reminder-badge {
                display: inline-flex; align-items: center; gap: 3px;
                font-size: 0.68rem; font-weight: 600; border-radius: 8px;
                padding: 1px 7px; margin-top: 3px;
            }
            .todo-reminder-badge.info { background: #e0f2fe; color: #0277bd; }
            .todo-reminder-badge.danger { background: #fdecea; color: #c62828; }
            .todo-reminder-badge.done { background: #f1f5f9; color: #94a3b8; }

            /* Action buttons */
            .todo-actions { display: flex; gap: 5px; flex-shrink: 0; }
            .todo-btn {
                width: 28px; height: 28px; border-radius: 8px; border: none;
                cursor: pointer; display: flex; align-items: center; justify-content: center;
                font-size: 0.9rem; transition: all 0.18s;
            }
            .todo-btn-edit { background: #fff8e1; color: #f57c00; }
            .todo-btn-edit:hover { background: #f57c00; color: #fff; }
            .todo-btn-delete { background: #fdecea; color: #c62828; }
            .todo-btn-delete:hover { background: #c62828; color: #fff; }

            /* Empty State */
            .todo-empty-state {
                text-align: center; padding: 40px 20px; color: #94a3b8;
            }
            .todo-empty-state i { font-size: 2.8rem; display: block; margin-bottom: 8px; color: #d1d5db; }
            .todo-empty-state p { font-size: 0.82rem; margin: 0; }

            /* Footer */
            .todo-footer { padding: 14px 18px 18px; background: #f8fafb; border-top: 1px solid #eef0f2; }
            .todo-input-row { display: flex; gap: 8px; }
            .todo-input {
                flex: 1; border: 1.5px solid #e0e4e8; border-radius: 10px;
                padding: 10px 14px; font-size: 0.875rem; outline: none;
                transition: border-color 0.2s; background: #fff; color: #1f2937;
            }
            .todo-input:focus { border-color: #006666; box-shadow: 0 0 0 3px rgba(0,102,102,0.10); }
            .todo-add-btn {
                width: 42px; height: 42px; border-radius: 10px; border: none;
                background: #006666; color: #fff; font-size: 1.3rem;
                display: flex; align-items: center; justify-content: center;
                cursor: pointer; transition: all 0.2s; flex-shrink: 0;
            }
            .todo-add-btn:hover { background: #005555; transform: scale(1.06); }

            /* Inline Edit */
            .todo-edit-wrap { flex: 1; display: flex; flex-direction: column; gap: 5px; }
            .todo-edit-input {
                border: 1.5px solid #006666; border-radius: 8px;
                padding: 6px 10px; font-size: 0.875rem; outline: none; width: 100%;
            }
            .todo-edit-date {
                border: 1.5px solid #e0e4e8; border-radius: 8px;
                padding: 5px 10px; font-size: 0.8rem; outline: none; width: 100%;
            }
            .todo-edit-actions { display: flex; gap: 5px; margin-top: 2px; }
            .todo-save-btn {
                padding: 5px 14px; border-radius: 8px; border: none;
                background: #006666; color: #fff; font-size: 0.78rem;
                font-weight: 600; cursor: pointer; transition: background 0.18s;
            }
            .todo-save-btn:hover { background: #005555; }
            .todo-cancel-btn {
                padding: 5px 14px; border-radius: 8px; border: none;
                background: #f1f5f9; color: #6b7280; font-size: 0.78rem;
                font-weight: 600; cursor: pointer; transition: background 0.18s;
            }
            .todo-cancel-btn:hover { background: #e5e7eb; }

            /* Modal z-index */
            .modal-backdrop { z-index: 99980 !important; }
            .modal { z-index: 99999 !important; }
        </style>

        <script>
        (function() {
            // ── State ──────────────────────────────────────────────
            let allTasks   = [];
            let activeFilter = 'all';

            // ── DOM Refs (lazy) ────────────────────────────────────
            const $ = id => document.getElementById(id);

            // ── Bootstrap modal open → fetch ───────────────────────
            document.addEventListener('DOMContentLoaded', () => {
                const modalEl = document.getElementById('todoListModal');
                if (!modalEl) return;

                modalEl.addEventListener('show.bs.modal', fetchTasks);

                // Filter buttons
                modalEl.querySelectorAll('.todo-filter-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        modalEl.querySelectorAll('.todo-filter-btn').forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');
                        activeFilter = btn.dataset.filter;
                        renderTasks();
                    });
                });

                // Add task
                const addBtn = $('addTask');
                const input  = $('taskInput');
                if (addBtn) addBtn.addEventListener('click', addTask);
                if (input)  input.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); addTask(); }});
            });

            // ── Fetch ──────────────────────────────────────────────
            function fetchTasks() {
                fetch('/todo-lists')
                    .then(r => r.ok ? r.json() : Promise.reject(r))
                    .then(data => { allTasks = data; renderTasks(); })
                    .catch(err => console.error('Fetch tasks error:', err));
            }

            // ── Render ─────────────────────────────────────────────
            function renderTasks() {
                const list    = $('todoList');
                const emptyEl = $('todoEmptyState');
                if (!list) return;
                list.innerHTML = '';

                const filtered = allTasks.filter(t => {
                    if (activeFilter === 'pending') return !t.completed;
                    if (activeFilter === 'done')    return  t.completed;
                    return true;
                });

                // Update subtitle & progress
                const total    = allTasks.length;
                const done     = allTasks.filter(t => t.completed).length;
                const pct      = total ? Math.round((done / total) * 100) : 0;
                const subtitle = $('todoSubtitle');
                const fill     = $('todoProgressFill');
                const label    = $('todoProgressLabel');
                if (subtitle) subtitle.textContent = `${done} of ${total} task${total !== 1 ? 's' : ''} completed`;
                if (fill)     fill.style.width = pct + '%';
                if (label)    label.textContent  = pct + '%';

                if (!filtered.length) {
                    if (emptyEl) emptyEl.style.display = 'block';
                    return;
                }
                if (emptyEl) emptyEl.style.display = 'none';

                filtered.forEach((task, idx) => {
                    const li = document.createElement('li');
                    li.className = 'todo-item' + (task.completed ? ' done' : '');
                    li.draggable = true;
                    li.dataset.id = task.id;

                    // Reminder badge
                    let remBadge = '';
                    if (task.reminder_at) {
                        const rDate = new Date(task.reminder_at);
                        const isOver = rDate < new Date() && !task.completed;
                        const cls  = task.completed ? 'done' : isOver ? 'danger' : 'info';
                        const str  = rDate.toLocaleString([], { month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' });
                        remBadge   = `<span class="todo-reminder-badge ${cls}"><i class="bx bx-time"></i>${str}</span>`;
                    }

                    li.innerHTML = `
                        <i class="bx bx-grid-vertical todo-drag"></i>
                        <input type="checkbox" class="todo-check toggleTask" data-id="${task.id}" ${task.completed ? 'checked' : ''} title="${task.completed ? 'Mark incomplete' : 'Mark complete'}">
                        <div class="todo-text-wrap">
                            <span class="todo-task-text" title="${escHtml(task.text)}">${escHtml(task.text)}</span>
                            ${remBadge}
                        </div>
                        <div class="todo-actions">
                            <button class="todo-btn todo-btn-edit editTask" data-id="${task.id}" title="Edit"><i class="bx bx-edit"></i></button>
                            <button class="todo-btn todo-btn-delete deleteTask" data-id="${task.id}" title="Delete"><i class="bx bx-trash"></i></button>
                        </div>`;

                    // Drag events
                    li.addEventListener('dragstart', e => {
                        li.classList.add('dragging');
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/plain', idx);
                    });
                    li.addEventListener('dragend', () => {
                        li.classList.remove('dragging');
                        const newOrder = [...list.querySelectorAll('li')].map(el => el.dataset.id);
                        fetch('/todo-lists/reorder', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                            body: JSON.stringify({ order: newOrder })
                        });
                    });
                    li.addEventListener('dragover', e => {
                        e.preventDefault();
                        const dragging = list.querySelector('.dragging');
                        const siblings = [...list.querySelectorAll('li:not(.dragging)')];
                        const next = siblings.find(s => e.clientY <= s.getBoundingClientRect().top + s.offsetHeight / 2);
                        list.insertBefore(dragging, next || null);
                    });

                    list.appendChild(li);
                });

                bindListEvents();
            }

            // ── Bind row events ────────────────────────────────────
            function bindListEvents() {
                const list = $('todoList');
                if (!list) return;

                // Toggle completion
                list.querySelectorAll('.toggleTask').forEach(chk => {
                    chk.addEventListener('change', () => {
                        fetch(`/manage-todolist-item/${chk.dataset.id}`, {
                            method: 'PUT',
                            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrfToken() },
                            body: JSON.stringify({ completed: chk.checked })
                        }).then(fetchTasks);
                    });
                });

                // Delete
                list.querySelectorAll('.deleteTask').forEach(btn => {
                    btn.addEventListener('click', () => {
                        swal({ title:'Delete this task?', icon:'warning', buttons:['Cancel','Yes, delete'], dangerMode:true })
                            .then(ok => {
                                if (!ok) return;
                                fetch(`/manage-todolist-item/${btn.dataset.id}`, {
                                    method: 'DELETE',
                                    headers: { 'X-CSRF-TOKEN': csrfToken() }
                                }).then(fetchTasks);
                            });
                    });
                });

                // Edit
                list.querySelectorAll('.editTask').forEach(btn => {
                    btn.addEventListener('click', () => openEditMode(btn));
                });
            }

            // ── Inline Edit ────────────────────────────────────────
            function openEditMode(btn) {
                const li   = btn.closest('li');
                if (li.querySelector('.todo-edit-wrap')) return; // already open
                const id   = btn.dataset.id;
                const task = allTasks.find(t => t.id == id);
                if (!task) return;

                // Hide normal content
                li.querySelector('.todo-text-wrap').style.display = 'none';
                li.querySelector('.todo-actions').style.display   = 'none';

                // Build inline editor
                const wrap = document.createElement('div');
                wrap.className = 'todo-edit-wrap';

                const textIn = document.createElement('input');
                textIn.type = 'text';
                textIn.className = 'todo-edit-input';
                textIn.value = task.text;

                const dateIn = document.createElement('input');
                dateIn.type = 'datetime-local';
                dateIn.className = 'todo-edit-date';
                if (task.reminder_at) dateIn.value = task.reminder_at.replace(' ', 'T').slice(0, 16);

                const acts = document.createElement('div');
                acts.className = 'todo-edit-actions';
                acts.innerHTML = `<button class="todo-save-btn"><i class="bx bx-check"></i> Save</button>
                                  <button class="todo-cancel-btn"><i class="bx bx-x"></i> Cancel</button>`;

                wrap.appendChild(textIn);
                wrap.appendChild(dateIn);
                wrap.appendChild(acts);
                li.insertBefore(wrap, li.querySelector('.todo-actions'));
                textIn.focus();

                const doSave = () => {
                    const newText = textIn.value.trim();
                    if (!newText) { renderTasks(); return; }
                    fetch(`/manage-todolist-item/${id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrfToken() },
                        body: JSON.stringify({ text: newText, completed: task.completed, reminder_at: dateIn.value })
                    }).then(fetchTasks);
                };

                acts.querySelector('.todo-save-btn').onclick   = doSave;
                acts.querySelector('.todo-cancel-btn').onclick = renderTasks;
                textIn.addEventListener('keydown', e => { if (e.key === 'Enter') doSave(); if (e.key === 'Escape') renderTasks(); });
            }

            // ── Add Task ───────────────────────────────────────────
            function addTask() {
                const input = $('taskInput');
                const val   = input ? input.value.trim() : '';
                if (!val) return;
                fetch('/manage-todolist-item', {
                    method: 'POST',
                    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrfToken() },
                    body: JSON.stringify({ text: val, completed: false })
                })
                .then(r => r.ok ? r.json() : Promise.reject(r))
                .then(newTask => {
                    allTasks.unshift(newTask);
                    input.value = '';
                    renderTasks();
                })
                .catch(() => alert('Could not add task.'));
            }

            // ── Helpers ────────────────────────────────────────────
            function csrfToken() {
                const m = document.querySelector('meta[name="csrf-token"]');
                return m ? m.content : '';
            }
            function escHtml(str) {
                return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
            }
        })();
        </script>

@endsection
