@extends('layout')
@section('title','Dashboard - eseCRM')

@section('content')

    @php
        $company = session('companies');
        $roles = session('roles');
        $roleArray = explode(',',($roles->features ?? ''));
    @endphp
    <style>
        .card {
            border-radius: 5px;
            box-shadow: 0px 0px 4px #00000014;
            border: 1px solid var(--color-lightgray);
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        .summary-item:hover {
            background-color: #f8f9fa;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }
        .card-title {
            font-weight: 600;
            color: #333;
        }.card .form-select {
            padding: 4px 35px 4px 15px !important;
            border-left: 1px solid #ddd !important;
        }
        .list-group{
            height: 60vh;
            overflow: auto;
            padding: 5px;
            font-size: 14px;
        }
        /* Custom scrollbar for activity log */
        .activity-log {
            max-height: 400px;
            overflow-y: auto;
        }
        .row-btn {
            width: 58px;
            display: flex;
            gap: 5px;
            justify-content: end;
        }
        @media (max-width: 767px){
            .m-none{
                display: none;
            }
            .row-btn{
                min-width: 60px !important;
                text-align: right;
            }
        }
        .list-group-item.dragging {
            opacity: 0.5;
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
        }
    </style>
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i> 
            Dashboard
            <div class="header-right">
                <div class="position-relative dropdown">
                    <a href="/notifications" class="text-dark" role="button" data-bs-toggle="dropdown" title="Notifications">
                        <i class="bx bx-bell"></i>
                        @php
                            function formatLeadCount($num) {
                                if ($num >= 1000000) {
                                    return round($num/1000000, 1).'M';
                                } elseif ($num >= 1000) {
                                    return round($num/1000, 1).'K';
                                } elseif ($num >= 99) {
                                    return round(99, 1).'+';
                                }
                                return $num;
                            }
                        @endphp
                        @if(count($leads)>0 && (in_array('leads',$roleArray) || in_array('All',$roleArray) || (Auth::user()->role == '0')))
                            <span class="badge position-absolute badge-danger">
                                {{ formatLeadCount(count($newLeads)) }}
                            </span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end npop-up">
                        @if(count($newLeads)>0 && (in_array('leads',$roleArray) || in_array('All',$roleArray) || (Auth::user()->role == '0')))
                        <li><a href="/leads">Leads <span class="small text-danger">{{ formatLeadCount(count($newLeads)) }}</span></a></li>
                        @else
                        <li class="emptyMsg">No messages found.</li>
                        @endif
                    </ul>
                </div>
                <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
            </div>
        </div>
        
        <div class="container-fluid mb-2">
            <!-- DASHBOARD WIDGETS -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card p-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h6 class="mb-1">Outstanding Invoices</h6>
                        <h3 class="font-weight-bold mb-0">₹{{ number_format($outstandingInvoices, 2) }}</h3>
                        <small class="text-white-50">Waiting for payment</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 99%, #fecfef 100%); color: #555;">
                        <h6 class="mb-1 text-danger">Pending Proposals</h6>
                        <h3 class="font-weight-bold mb-0 text-danger">{{ $pendingProposals }}</h3>
                        <small class="text-muted">Open or Sent</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%); color: white;">
                        <h6 class="mb-1">My Pending Tasks</h6>
                        <h3 class="font-weight-bold mb-0">{{ $myPendingTasks }}</h3>
                        <small class="text-white-50">Assigned to you</small>
                    </div>
                </div>
                <div class="col-md-3">
                   <div class="card p-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: #333;">
                        <h6 class="mb-1 text-success">Total Leads</h6>
                        <h3 class="font-weight-bold mb-0 text-success">{{ $totalLeads }}</h3>
                        <small class="text-muted">In the pipeline</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="flex-box">
                        @if(in_array('leads',$roleArray) || in_array('All',$roleArray) || (Auth::user()->role == '0'))
                        <a class="box td-none" href="/leads">
                            <div class="flex jsb">
                                <div class="smallbox-1">
                                    <span class="small text-success font-weight-bold">Leads</span>
                                    <h3 class="text-dark">{{ count($leads ?? []) }}</h3>
                                </div>
                                <div class="box-icon"><i class="bx bx-filter-alt"></i></div>
                            </div>
                        </a>
                        @endif
                        @if(in_array('clients',$roleArray) || in_array('All',$roleArray) || (Auth::user()->role == '0'))
                        <a class="box td-none" href="/clients">
                            <div class="flex jsb">
                                <div class="smallbox-1">
                                    <span class="small text-success font-weight-bold">Customers</span>
                                    <h3 class="text-dark">{{ count($clients ?? []) }}</h3>
                                </div>
                                <div class="box-icon"><i class="bx bx-group"></i></div>
                            </div>
                        </a>
                        <a class="box td-none" href="/projects">
                            <div class="flex jsb">
                                <div class="smallbox-1">
                                    <span class="small text-success font-weight-bold">Projects</span>
                                    <h3 class="text-dark">{{ count($projects ?? []) }}</h3>
                                </div>
                                <div class="box-icon"><i class="bx bx-file"></i></div>
                            </div>
                        </a>
                        <a class="box td-none" href="/recoveries">
                            <div class="flex jsb">
                                <div class="smallbox-1">
                                    <span class="small text-success font-weight-bold">Recovery</span>
                                    <h3 class="text-dark">{{ count($recoveries ?? []) }}</h3>
                                </div>
                                <div class="box-icon"><i class="bx bx-coin-stack"></i></div>
                            </div>
                        </a>
                        @endif
                        @if(in_array('users',$roleArray) || in_array('All',$roleArray) || (Auth::user()->role == '0'))
                        <a class="box td-none" href="/users">
                            <div class="flex jsb">
                                <div class="smallbox-1">
                                    <span class="small text-success font-weight-bold">Users</span>
                                    <h3 class="text-dark">{{ count($users ?? []) }}</h3>
                                </div>
                                <div class="box-icon"><i class="bx bx-user"></i></div>
                            </div>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid mb-3">
            <div class="row">
                @if(in_array('All',$roleArray))
                <div class="col-lg-8 pt-2">
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Revenue Growth Card -->
                            <div class="card p-4 mb-4 m-none">
                                <h5>Revenue Growth</h5>
                                <div class="chart-container">
                                    <canvas id="revenueChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- ACTIVITY MONITOR FLOW CHART -->
                            <div class="card p-4 m-none">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">Activity Monitor Flow (Day-wise)</h5>
                                    <select id="activityDateRange" class="form-select form-select-sm" style="width: auto;">
                                        <option value="7" {{ $selectedActivityDays == 7 ? 'selected' : '' }}>Last 7 Days</option>
                                        <option value="30" {{ $selectedActivityDays == 30 ? 'selected' : '' }}>Last 30 Days</option>
                                        <option value="90" {{ $selectedActivityDays == 90 ? 'selected' : '' }}>Last 90 Days</option>
                                    </select>
                                </div>
                                <div class="chart-container">
                                    <canvas id="activityFlowChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- RECENT ACTIVITY LOG TABLE (DAY-WISE) -->
                    <div class="card mt-4 p-0 m-none">
                        <div class="card-header bg-white">
                            <h6 class="mb-0 font-weight-bold">Day-wise Activity Breakdown</h6>
                        </div>
                        <div class="table-responsive activity-log">
                            <table class="table table-sm table-hover mb-0" style="font-size: 13px;">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>User Name</th>
                                        <th>Activity Type</th>
                                        <th class="text-center">Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Group activities by date, user_name AND type
                                        $groupedActivities = collect($activities ?? [])->groupBy(function($item) {
                                            $date = \Carbon\Carbon::parse($item->created_at)->format('Y-m-d');
                                            return $date . '|||' . $item->user_name . '|||' . $item->type;
                                        })->sortKeysDesc();
                                    @endphp
                                    @forelse($groupedActivities as $key => $group)
                                        @php 
                                            $details = explode('|||', $key);
                                            $date = \Carbon\Carbon::parse($details[0])->format('M j, Y');
                                        @endphp
                                        <tr>
                                            <td><span class="text-muted">{{ $date }}</span></td>
                                            <td><span class="text-primary font-weight-bold">{{ $details[1] }}</span></td>
                                            <td><span class="badge bg-info text-white">{{ $details[2] }}</span></td>
                                            <td class="text-center"><strong>{{ count($group) }}</strong></td>
                                        </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No activities recorded yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                </div>
                @endif

                <div class="col-lg-4 pt-2">
                    <div class="card todolist">
                        <div class="card-header bg-default">
                            <h5 class="text-white font-weight-bold my-1 h6">My Todo List</h5>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group" id="todoList" draggable="true">
                                <!-- Tasks dynamically loaded -->
                            </ul>
                        </div>
                        <div class="card-footer">
                            <div class="input-group">
                                <input type="text" id="taskInput" class="form-control" placeholder="Add a new task" />
                                <button id="addTask" class="btn btn-primary"><i class="bx bx-plus"></i></button>
                            </div>
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
                            callback: function(value) { return '₹' + value; }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
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
                            footer: function(tooltipItems) {
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
        document.getElementById('activityDateRange').addEventListener('change', function() {
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
            console.log("Initializing Firebase...");
            firebase.initializeApp(firebaseConfig);
            const messaging = firebase.messaging();
            
            // Request Permission
            messaging.requestPermission().then(function() {
                console.log('Notification permission granted.');
                return messaging.getToken();
            }).then(function(token) {
                console.log('FCM Token generated:', token);
                // Save token to database
                fetch('/save-token', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ token: token, _token: '{{ csrf_token() }}' })
                })
                .then(res => res.json())
                .then(data => console.log('Token saved to server:', data))
                .catch(err => console.error('Error saving token:', err));
            }).catch(function(err) {
                console.error('Unable to get permission to notify.', err);
            });
            
            // Handle incoming messages
            messaging.onMessage(function(payload) {
                console.log("Foreground Message received: ", payload);
                const notificationTitle = payload.notification.title;
                const notificationOptions = {
                    body: payload.notification.body,
                    icon: '/favicon.ico',
                    data: payload.data
                };
                
                if (Notification.permission === 'granted') {
                    // Try using Service Worker for more reliability
                    navigator.serviceWorker.ready.then(function(registration) {
                        registration.showNotification(notificationTitle, notificationOptions);
                    }).catch(function(err) {
                        console.warn("Service worker not ready, using standard Notification", err);
                        new Notification(notificationTitle, notificationOptions);
                    });
                } else {
                    console.warn("Notification permission not granted, cannot show notification.");
                }
            });
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

        fetchTasks();
    </script>
@endsection