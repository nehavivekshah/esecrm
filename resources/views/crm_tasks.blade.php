@extends('layout')
@section('title', 'CRM Follow-ups - eseCRM')

@section('content')
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i>
            CRM Follow-Up Tasks
        </div>

        <div class="container-fluid py-4">
            <div class="row g-4">
                <!-- Add Task Form -->
                <div class="col-md-4">
                    <div class="form-card bg-white p-4 rounded shadow-sm">
                        <h5 class="fw-bold mb-4"><i class='bx bx-plus-circle'></i> New Follow-up</h5>
                        <form action="{{ route('crm_tasks.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Task Name / Subject</label>
                                <input type="text" name="name" class="form-control" required
                                    placeholder="e.g. Call regarding quotation">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select" required>
                                    <option value="Call">Call</option>
                                    <option value="Meeting">Meeting</option>
                                    <option value="Email">Email</option>
                                    <option value="To-Do">To-Do</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Due Date & Time</label>
                                <input type="datetime-local" name="due_date" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Related To (Optional)</label>
                                <select name="rel_type" class="form-select mb-2">
                                    <option value="">None</option>
                                    <option value="Lead">Lead</option>
                                    <option value="Customer">Customer</option>
                                    <option value="Opportunity">Opportunity</option>
                                </select>
                                <input type="number" name="rel_id" class="form-control" placeholder="Related ID">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Schedule Task</button>
                        </form>
                    </div>
                </div>

                <!-- Task List -->
                <div class="col-md-8">
                    <div class="form-card bg-white p-4 rounded shadow-sm">
                        <h5 class="fw-bold mb-4"><i class='bx bx-list-check'></i> Upcoming Tasks</h5>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Status</th>
                                        <th>Task</th>
                                        <th>Type</th>
                                        <th>Related</th>
                                        <th>Due Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tasks as $task)
                                        <tr id="task-{{ $task->id }}">
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input task-check" type="checkbox"
                                                        data-id="{{ $task->id }}" {{ $task->status === 'Completed' ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td>
                                                <span
                                                    class="{{ $task->status === 'Completed' ? 'text-decoration-line-through text-muted' : 'fw-bold' }}">{{ $task->name }}</span>
                                            </td>
                                            <td>
                                                @if($task->type == 'Call') <span class="badge bg-info"><i
                                                    class='bx bx-phone'></i> Call</span>
                                                @elseif($task->type == 'Meeting') <span class="badge bg-warning text-dark"><i
                                                    class='bx bx-group'></i> Meeting</span>
                                                @elseif($task->type == 'Email') <span class="badge bg-secondary"><i
                                                    class='bx bx-envelope'></i> Email</span>
                                                @else <span class="badge bg-dark"><i class='bx bx-task'></i> To-Do</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($task->rel_type)
                                                    <small class="text-muted">{{ $task->rel_type }} #{{ $task->rel_id }}</small>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $due = \Carbon\Carbon::parse($task->due_date);
                                                    $isOverdue = $due->isPast() && $task->status !== 'Completed';
                                                @endphp
                                                <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                                    {{ $due->format('d M Y, h:i A') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class='bx bx-check-double fs-1 mb-2'></i><br>
                                                No upcoming tasks. You're all caught up!
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.task-check').change(function () {
                let taskId = $(this).data('id');
                let isChecked = $(this).is(':checked');
                let status = isChecked ? 'Completed' : 'Pending';
                let $row = $('#task-' + taskId);

                $.post("{{ route('crm_tasks.update_status') }}", {
                    _token: "{{ csrf_token() }}",
                    id: taskId,
                    status: status
                }, function (res) {
                    if (isChecked) {
                        $row.find('span').first().addClass('text-decoration-line-through text-muted');
                    } else {
                        $row.find('span').first().removeClass('text-decoration-line-through text-muted');
                    }
                }).fail(function () {
                    alert('Error updating task.');
                });
            });
        });
    </script>
@endsection