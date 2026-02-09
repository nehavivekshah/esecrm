@extends('layout')
@section('title', 'Dashboard - eseCRM')

@section('content')

    @php
        $company = session('companies');
        $roles = session('roles');
        $roleArray = explode(',', ($roles->features ?? ''));

        if (!function_exists('formatLeadCount')) {
            function formatLeadCount($num) {
                if ($num >= 1000000) return round($num / 1000000, 1) . 'M';
                if ($num >= 1000) return round($num / 1000, 1) . 'K';
                if ($num >= 99) return '99+';
                return $num;
            }
        }
    @endphp
    <style>
        :root {
            --card-radius: 12px;
            --accent-primary: #4e73df;
            --accent-success: #1cc88a;
            --glass-bg: rgba(255, 255, 255, 0.95);
        }

        .card {
            border-radius: var(--card-radius);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            background: #fff;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .chart-container {
            position: relative;
            height: 350px;
        }

        .activity-log {
            overflow-y: auto;
            flex-grow: 1;
            padding-right: 5px;
        }

        /* Custom scrollbar */
        .activity-log::-webkit-scrollbar {
            width: 5px;
        }
        .activity-log::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .activity-log::-webkit-scrollbar-thumb {
            background: #ddd;
            border-radius: 10px;
        }
        .activity-log::-webkit-scrollbar-thumb:hover {
            background: #ccc;
        }

        .card-title {
            font-weight: 700;
            color: #2e384d;
            letter-spacing: -0.01em;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Modern Quick Links View */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .action-card {
            background: #fff;
            padding: 25px 20px;
            text-align: center;
            border-radius: var(--card-radius);
            text-decoration: none !important;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .action-card:hover {
            background: var(--accent-primary);
            color: white !important;
            transform: translateY(-5px);
        }

        .action-card i {
            font-size: 2.2rem;
            margin-bottom: 15px;
            display: block;
            color: var(--accent-primary);
            transition: color 0.3s ease;
        }

        .action-card:hover i {
            color: white;
        }

        .action-card h6 {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 1.05rem;
        }

        .action-card span {
            font-size: 1.25rem;
            font-weight: 700;
            opacity: 0.9;
        }

        /* Widget Refinements */
        .widget-card {
            height: 100%;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .widget-label {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 10px;
            opacity: 0.85;
        }

        /* Feed Styles */
        .activity-feed-item {
            padding: 15px;
            border-left: 3px solid #eee;
            margin-bottom: 10px;
            background: #fdfdfd;
            border-radius: 0 8px 8px 0;
            transition: background 0.2s;
        }

        .activity-feed-item:hover {
            background: #f8f9ff;
            border-left-color: var(--accent-primary);
        }

        .badge-soft-danger { background: rgba(255, 71, 87, 0.1); color: #ff4757; }
        .badge-soft-success { background: rgba(46, 213, 115, 0.1); color: #2ed573; }
        
        @media (max-width: 767px) {
            .quick-actions-grid { grid-template-columns: repeat(2, 1fr); }
            .header-right .btn span { display: none; }
        }
    </style>
    <section class="task__section">
        <div class="text d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="bx bx-menu me-3" id="mbtn" style="cursor: pointer; font-size: 1.5rem;"></i>
                <span class="h4 mb-0 font-weight-bold text-dark">Dashboard</span>
            </div>
            <div class="header-right">
                <div class="position-relative dropdown">
                    <a href="/notifications" class="text-dark bg-light p-2 rounded-circle d-flex align-items-center justify-content-center" 
                       style="width: 40px; height: 40px; transition: all 0.2s;" role="button" data-bs-toggle="dropdown"
                        title="Notifications">
                        <i class="bx bx-bell" style="font-size: 1.3rem;"></i>
                        @if(count($leads) > 0 && (in_array('leads', $roleArray) || in_array('All', $roleArray) || (Auth::user()->role == '0')))
                            <span class="badge position-absolute bg-danger border border-white rounded-circle" 
                                  style="top: -5px; right: -5px; padding: 4px 6px; font-size: 0.65rem;">
                                {{ formatLeadCount(count($newLeads)) }}
                            </span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 p-2" style="border-radius: 12px; min-width: 250px;">
                        @if(count($newLeads) > 0 && (in_array('leads', $roleArray) || in_array('All', $roleArray) || (Auth::user()->role == '0')))
                            <div class="px-3 py-2 border-bottom mb-2">
                                <h6 class="mb-0 font-weight-bold">Recent Leads</h6>
                            </div>
                            <li><a class="dropdown-item rounded-3 py-2" href="/leads">
                                <i class="bx bx-user-plus me-2 text-primary"></i>New Leads 
                                <span class="badge bg-soft-danger ms-auto float-end">{{ formatLeadCount(count($newLeads)) }}</span>
                            </a></li>
                        @else
                            <li class="px-3 py-4 text-center text-muted">
                                <i class="bx bx-ghost d-block mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                                <span class="small">No new notifications</span>
                            </li>
                        @endif
                    </ul>
                </div>
                <!-- TODO LIST TRIGGER BUTTON -->
                <button type="button" class="btn btn-primary btn-sm rounded-pill d-flex align-items-center px-4 py-2 shadow-sm"
                    data-bs-toggle="modal" data-bs-target="#todoListModal" style="font-weight: 600; letter-spacing: 0.5px;">
                    <i class="bx bx-check-double me-2" style="font-size: 1.1rem;"></i> <span>My Todo</span>
                </button>
                <a href="/signout" class="btn btn-outline-danger btn-sm rounded-circle d-flex align-items-center justify-content-center" 
                   style="width: 40px; height: 40px;" title="Logout">
                    <i class="bx bx-log-out" style="font-size: 1.2rem;"></i>
                </a>
            </div>
        </div>

        <div class="container-fluid mb-2">
            <!-- DASHBOARD WIDGETS -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card widget-card border-0 shadow-sm"
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <span class="widget-label">Outstanding Invoices</span>
                        <div class="d-flex align-items-center justify-content-between">
                            <h2 class="font-weight-bold mb-0">₹{{ number_format($outstandingInvoices, 0) }}</h2>
                            <i class="bx bx-receipt opacity-50" style="font-size: 2rem;"></i>
                        </div>
                        <small class="text-white-50 mt-2">Waiting for payment</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-card border-0 shadow-sm"
                        style="background: linear-gradient(135deg, #FF5F6D 0%, #FFC371 100%); color: white;">
                        <span class="widget-label">Pending Proposals</span>
                        <div class="d-flex align-items-center justify-content-between">
                            <h2 class="font-weight-bold mb-0">{{ $pendingProposals }}</h2>
                            <i class="bx bx-file-blank opacity-50" style="font-size: 2rem;"></i>
                        </div>
                        <small class="text-white-50 mt-2">Open or Sent</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-card border-0 shadow-sm"
                        style="background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%); color: white;">
                        <span class="widget-label">Pending Tasks</span>
                        <div class="d-flex align-items-center justify-content-between">
                            <h2 class="font-weight-bold mb-0">{{ $myPendingTasks }}</h2>
                            <i class="bx bx-task opacity-50" style="font-size: 2rem;"></i>
                        </div>
                        <small class="text-white-50 mt-2">Assigned to you</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card widget-card border-0 shadow-sm"
                        style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
                        <span class="widget-label">Total Leads</span>
                        <div class="d-flex align-items-center justify-content-between">
                            <h2 class="font-weight-bold mb-0">{{ $totalLeads }}</h2>
                            <i class="bx bx-pulse opacity-50" style="font-size: 2rem;"></i>
                        </div>
                        <small class="text-white-50 mt-2">In the pipeline</small>
                    </div>
                </div>
            </div>

        <div class="container-fluid mb-4">
            <h5 class="mb-3 font-weight-bold text-dark">Quick Actions</h5>
            <div class="quick-actions-grid">
                @if(in_array('leads', $roleArray) || in_array('All', $roleArray) || (Auth::user()->role == '0'))
                    <a href="/leads" class="action-card shadow-sm">
                        <i class="bx bx-filter-alt"></i>
                        <h6>Leads</h6>
                        <span>{{ count($leads ?? []) }}</span>
                    </a>
                @endif
                @if(in_array('clients', $roleArray) || in_array('All', $roleArray) || (Auth::user()->role == '0'))
                    <a href="/clients" class="action-card shadow-sm">
                        <i class="bx bx-group"></i>
                        <h6>Customers</h6>
                        <span>{{ count($clients ?? []) }}</span>
                    </a>
                    <a href="/projects" class="action-card shadow-sm">
                        <i class="bx bx-file"></i>
                        <h6>Projects</h6>
                        <span>{{ count($projects ?? []) }}</span>
                    </a>
                    <a href="/recoveries" class="action-card shadow-sm">
                        <i class="bx bx-coin-stack"></i>
                        <h6>Recovery</h6>
                        <span>{{ count($recoveries ?? []) }}</span>
                    </a>
                @endif
                @if(in_array('users', $roleArray) || in_array('All', $roleArray) || (Auth::user()->role == '0'))
                    <a href="/users" class="action-card shadow-sm">
                        <i class="bx bx-user"></i>
                        <h6>Users</h6>
                        <span>{{ count($users ?? []) }}</span>
                    </a>
                @endif
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <!-- Revenue Growth -->
                            <div class="card h-100 p-4">
                                <h5 class="card-title mb-4">Revenue Growth</h5>
                                <div class="chart-container">
                                    <canvas id="revenueChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <!-- Activity monitor -->
                            <div class="card h-100 p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="card-title mb-0">Activity Monitor Flow</h5>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="modal"
                                            data-bs-target="#activityBreakdownModal">
                                            <i class="bx bx-list-ul"></i>
                                        </button>
                                        <select id="activityDateRange" class="form-select form-select-sm border-0 bg-light rounded-pill" style="width: auto;">
                                            <option value="7" {{ $selectedActivityDays == 7 ? 'selected' : '' }}>7D</option>
                                            <option value="30" {{ $selectedActivityDays == 30 ? 'selected' : '' }}>30D</option>
                                            <option value="90" {{ $selectedActivityDays == 90 ? 'selected' : '' }}>90D</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="chart-container">
                                    <canvas id="activityFlowChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 d-flex flex-column">
                    <!-- RECENT ACTIVITY FEED -->
                    <div class="card h-100 p-4 d-flex flex-column" style="max-height: 480px;">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0">Recent Activities</h5>
                            <a href="#" class="btn btn-link btn-sm text-decoration-none p-0">View all</a>
                        </div>
                        <div class="activity-log">
                            @forelse(collect($activities ?? [])->take(20) as $activity)
                                <div class="activity-feed-item">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="font-weight-bold text-dark small" style="font-size: 0.8rem;">{{ $activity->user_name ?? 'System' }}</span>
                                        <span class="text-muted" style="font-size: 0.65rem;">{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</span>
                                    </div>
                                    <p class="mb-1 text-muted" style="font-size: 0.75rem; line-height: 1.4;">{{ $activity->type }} - {{ $activity->description ?? 'Performed an action' }}</p>
                                    @if(isset($activity->subject))
                                        <span class="badge bg-soft-info text-info rounded-pill" style="font-size: 0.6rem;">{{ $activity->subject }}</span>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-5 text-muted flex-grow-1 d-flex flex-column justify-content-center">
                                    <i class="bx bx-news mb-2" style="font-size: 2.5rem; opacity: 0.3;"></i>
                                    <p class="small">No recent activity found</p>
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
                    let d = new Date(currentReminder);
                    if (!currentReminder.endsWith('Z')) {
                        d = new Date(currentReminder + 'Z'); // Treat server time as UTC
                    }
                    // Adjust to local ISO string roughly
                    const offset = d.getTimezoneOffset() * 60000;
                    const localISOTime = (new Date(d - offset)).toISOString().slice(0, 16);
                    dateInput.value = localISOTime;
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
    <!-- RECENT ACTIVITY LOG MODAL -->
    <div class="modal fade" id="activityBreakdownModal" tabindex="-1" aria-labelledby="activityBreakdownModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-white border-bottom-0 pb-0">
                    <h5 class="modal-title font-weight-bold text-dark" id="activityBreakdownModalLabel">
                        <i class="bx bx-history me-2 text-primary"></i>Day-wise Activity Breakdown
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="table-responsive rounded shadow-sm border">
                        <table class="table table-hover mb-0" style="font-size: 14px;">
                            <thead class="bg-light border-bottom">
                                <tr>
                                    <th class="py-3 px-3">Date</th>
                                    <th class="py-3 px-3">User Name</th>
                                    <th class="py-3 px-3">Activity Type</th>
                                    <th class="py-3 px-3 text-center">Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $groupedActivities = collect($activities ?? [])->groupBy(function ($item) {
                                        $date = \Carbon\Carbon::parse($item->created_at)->format('Y-m-d');
                                        return $date . '|||' . ($item->user_name ?? 'Unknown User') . '|||' . ($item->type ?? 'General');
                                    })->sortKeysDesc();
                                @endphp
                                @forelse($groupedActivities as $key => $group)
                                    @php 
                                        $details = explode('|||', $key);
                                        $date = \Carbon\Carbon::parse($details[0])->format('M j, Y');
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-3 font-weight-bold"><span class="text-muted">{{ $date }}</span></td>
                                        <td class="px-3 py-3"><span class="text-primary font-weight-bold">{{ $details[1] }}</span></td>
                                        <td class="px-3 py-3"><span class="badge bg-soft-info text-info border border-info">{{ $details[2] }}</span></td>
                                        <td class="px-3 py-3 text-center"><span class="h6 font-weight-bold">{{ count($group) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">No activities recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MY TODO LIST MODAL -->
    <div class="modal fade" id="todoListModal" tabindex="-1" aria-labelledby="todoListModalLabel" aria-hidden="true" style="z-index: 99999;">
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
                    <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden; border: 1px solid #dee2e6;">
                        <input type="text" id="taskInput" class="form-control border-0 px-3" placeholder="Add a new task..." style="height: 45px;" />
                        <button id="addTask" class="btn btn-primary px-3 border-0"><i class="bx bx-plus" style="font-size: 1.2rem;"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
        .modal-backdrop { z-index: 99980 !important; }
        .modal { z-index: 99999 !important; }
        .modal-content { box-shadow: 0 10px 40px rgba(0,0,0,0.2) !important; }
        .badge.bg-soft-info { color: #0dcaf0 !important; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof fetchTasks === 'function') {
                fetchTasks();
            }
        });
    </script>
@endsection