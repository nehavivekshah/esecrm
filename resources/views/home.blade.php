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
                <div class="col-lg-8 pt-2">
                    <!-- Revenue Growth Card -->
                    <div class="card p-4 mb-4 m-none">
                        <h5>Revenue Growth</h5>
                        <div class="chart-container">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                    
                    @if(in_array('All',$roleArray))

                    <!-- ACTIVITY MONITOR FLOW CHART -->
                    <div class="card p-4 m-none">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Activity Monitor Flow (User-wise)</h5>
                            <span class="badge bg-light text-dark border">User Contribution Tracking</span>
                        </div>
                        <div class="chart-container">
                            <canvas id="activityFlowChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- RECENT ACTIVITY LOG TABLE (GROUPED) -->
                    <div class="card mt-4 p-0 m-none">
                        <div class="card-header bg-white">
                            <h6 class="mb-0 font-weight-bold">User-wise Activity Type Count</h6>
                        </div>
                        <div class="table-responsive activity-log">
                            <table class="table table-sm table-hover mb-0" style="font-size: 13px;">
                                <thead class="bg-light">
                                    <tr>
                                        <th>User Name</th>
                                        <th>Activity Type</th>
                                        <th class="text-center">Activity Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Group activities by user_name AND type to avoid duplicates and show count
                                        $groupedActivities = collect($activities ?? [])->groupBy(function($item) {
                                            return $item->user_name . '|||' . $item->type;
                                        });
                                    @endphp
                                    @forelse($groupedActivities as $key => $group)
                                        @php 
                                            $details = explode('|||', $key); 
                                        @endphp
                                        <tr>
                                            <td><span class="text-primary font-weight-bold">{{ $details[0] }}</span></td>
                                            <td><span class="badge bg-info text-white">{{ $details[1] }}</span></td>
                                            <td class="text-center"><strong>{{ count($group) }}</strong></td>
                                        </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No activities recorded yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    @endif
                </div>

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

        // ACTIVITY MONITOR FLOW CHART (User-wise count)
        const activityCtx = document.getElementById('activityFlowChart').getContext('2d');
        
        const activityLabels = {!! json_encode($activityChartLabels) !!};
        const activityData = {!! json_encode($activityChartData) !!};

        new Chart(activityCtx, {
            type: 'bar',
            data: {
                labels: activityLabels,
                datasets: [{
                    label: 'Activities Count',
                    data: activityData,
                    backgroundColor: 'rgba(46, 204, 113, 0.6)',
                    borderColor: '#2ecc71',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: '#f0f0f0' },
                        ticks: { stepSize: 1 }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>

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
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.draggable = true;
                li.dataset.id = task.id;
                li.innerHTML = `
                    <div class="d-flex align-items-center">
                        <input type="checkbox" ${task.completed ? 'checked' : ''} data-id="${task.id}" class="me-2 toggleTask" />
                        <span class="${task.completed ? 'text-decoration-line-through' : ''}">${task.text}</span>
                    </div>
                    <div class="row-btn">
                        <button class="btn btn-warning btn-sm editTask p-1" style="line-height:0;" data-id="${task.id}"><i class="bx bx-edit"></i></button>
                        <button class="btn btn-danger btn-sm deleteTask p-1" style="line-height:0;" data-id="${task.id}"><i class="bx bx-trash"></i></button>
                    </div>`;
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

        // Delete Logic
        todoList.addEventListener('click', async (e) => {
            const btn = e.target.closest('.deleteTask');
            if (!btn) return;
            e.preventDefault();
            const id = btn.dataset.id;
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
        });

        fetchTasks();
    </script>
@endsection