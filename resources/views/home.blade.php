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

            <div class="container-fluid mb-2">
                <!-- DASHBOARD WIDGETS -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card widget-card h-100 p-4 border-top" style="border-top: 4px solid #1a73e8 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="widget-label text-slate-500 font-weight-bold text-uppercase" style="font-size: 0.75rem;">Outstanding Invoices</span>
                                <div class="p-2 bg-blue-soft rounded-circle">
                                    <i class="bx bx-receipt text-primary h5 mb-0"></i>
                                </div>
                            </div>
                            <h2 class="font-weight-bold mb-0 text-slate-800">₹{{ number_format($outstandingInvoices, 0) }}</h2>
                            <div class="mt-3">
                                <span class="badge bg-slate-100 text-slate-600 rounded-pill small">Pending</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card widget-card h-100 p-4 border-top" style="border-top: 4px solid #ea4335 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="widget-label text-slate-500 font-weight-bold text-uppercase" style="font-size: 0.75rem;">Pending Proposals</span>
                                <div class="p-2 bg-rose-soft rounded-circle">
                                    <i class="bx bx-file-blank text-rose-500 h5 mb-0"></i>
                                </div>
                            </div>
                            <h2 class="font-weight-bold mb-0 text-slate-800">{{ $pendingProposals }}</h2>
                            <div class="mt-3">
                                <span class="badge bg-slate-100 text-slate-600 rounded-pill small">Active</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card widget-card h-100 p-4 border-top" style="border-top: 4px solid #fbbc04 !important;">
                             <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="widget-label text-slate-500 font-weight-bold text-uppercase" style="font-size: 0.75rem;">Assigned Tasks</span>
                                <div class="p-2 bg-warning bg-opacity-10 rounded-circle">
                                    <i class="bx bx-task text-warning h5 mb-0"></i>
                                </div>
                            </div>
                            <h2 class="font-weight-bold mb-0 text-slate-800">{{ $myPendingTasks }}</h2>
                            <div class="mt-3">
                                <span class="badge bg-slate-100 text-slate-600 rounded-pill small">Your Queue</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card widget-card h-100 p-4 border-top" style="border-top: 4px solid #34a853 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="widget-label text-slate-500 font-weight-bold text-uppercase" style="font-size: 0.75rem;">Total Leads</span>
                                <div class="p-2 bg-emerald-soft rounded-circle">
                                    <i class="bx bx-pulse text-emerald-600 h5 mb-0"></i>
                                </div>
                            </div>
                            <h2 class="font-weight-bold mb-0 text-slate-800">{{ $totalLeads }}</h2>
                            <div class="mt-3">
                                <span class="badge bg-slate-100 text-slate-600 rounded-pill small">Sales Pipeline</span>
                            </div>
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
                <!-- Action Required Alerts Section -->
                <div class="row g-4 mb-4">
                    <div class="col-md-12">
                        <div class="card p-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                            <div class="d-flex align-items-center mb-3">
                                <i class='bx bxs-zap text-amber-500 h4 me-2 mb-0'></i>
                                <h5 class="card-title mb-0">Action Required & CRM Alerts</h5>
                            </div>
                            <div class="row g-3">
                                <!-- Overdue Follow-ups -->
                                <div class="col-lg-6">
                                    <h6 class="text-slate-500 small font-weight-bold text-uppercase mb-2">Overdue Follow-ups ({{ count($overdueLeadsList) }})</h6>
                                    <div class="list-group list-group-flush border rounded-3 bg-white">
                                        @forelse($overdueLeadsList as $olead)
                                            <a href="/manage-lead?id={{ $olead->id }}" class="list-group-item list-group-item-action border-0 py-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <div class="fw-bold text-slate-800">{{ $olead->name }}</div>
                                                        <small class="text-rose-500"><i class='bx bx-history'></i> Missed: {{ \Carbon\Carbon::parse($olead->next_date)->diffForHumans() }}</small>
                                                    </div>
                                                    <i class='bx bx-chevron-right text-slate-300'></i>
                                                </div>
                                            </a>
                                        @empty
                                            <div class="p-4 text-center text-muted small">No overdue follow-ups! Great job.</div>
                                        @endforelse
                                    </div>
                                </div>
                                <!-- Expiring Proposals -->
                                <div class="col-lg-6">
                                    <h6 class="text-slate-500 small font-weight-bold text-uppercase mb-2">Proposals Near Expiry ({{ count($expiringProposals) }})</h6>
                                    <div class="list-group list-group-flush border rounded-3 bg-white">
                                        @forelse($expiringProposals as $eprop)
                                            <a href="/manage-proposal?id={{ $eprop->id }}" class="list-group-item list-group-item-action border-0 py-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <div class="fw-bold text-slate-800">{{ $eprop->subject }}</div>
                                                        <small class="text-amber-600"><i class='bx bx-time-five'></i> Expires in: {{ \Carbon\Carbon::parse($eprop->open_till)->diffForHumans() }}</small>
                                                    </div>
                                                    <i class='bx bx-chevron-right text-slate-300'></i>
                                                </div>
                                            </a>
                                        @empty
                                            <div class="p-4 text-center text-muted small">All proposals are up to date.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
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

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const taskInput = document.getElementById('taskInput');
                const addTaskButton = document.getElementById('addTask');
                const todoList = document.getElementById('todoList');
                const clearAll = document.getElementById('clearAll');
                let tasks = [];

                if (!taskInput || !addTaskButton || !todoList) {
                    console.error('Todo list elements not found!');
                    return;
                }

            function fetchTasks() {
                fetch('/todo-lists')
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        tasks = data;
                        renderTasks();
                    })
                    .catch(error => console.error('Error fetching tasks:', error));
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
                            headers: { 
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ order: newOrder })
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
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(task)
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => { throw new Error(text) });
                        }
                        return response.json();
                    })
                    .then(newTask => {
                        tasks.unshift(newTask);
                        taskInput.value = '';
                        renderTasks();
                    })
                    .catch(error => {
                        console.error('Error adding task:', error);
                        alert('Could not add task. Check console for details.');
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
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ completed })
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
                            headers: { 
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
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
                                    headers: { 
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({
                                        text: newText,
                                        completed: task.completed,
                                        reminder_at: reminderAt
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
            fetchTasks();
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

@endsection