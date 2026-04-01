@extends('layout')
@section('title', 'Sales Automations - eseCRM')

@section('content')
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i>
            Sales Automations & Workflows
        </div>

        <div class="container-fluid py-4">
            <div class="row g-4">

                <!-- Create Automation -->
                <div class="col-md-4">
                    <div class="form-card bg-white p-4 rounded shadow-sm">
                        <h5 class="fw-bold mb-4"><i class='bx bx-git-branch'></i> Create Workflow Rule</h5>
                        <form action="{{ route('automations.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label text-muted fw-bold">1. When this happens...</label>
                                <select name="trigger_event" class="form-select border-primary bg-light" required>
                                    <option value="">Select Trigger</option>
                                    <option value="Lead Created">New Lead Created</option>
                                    <option value="Opportunity Closed Won">Opportunity Marked as Closed Won</option>
                                    <option value="Opportunity Stage Changed">Opportunity enters specific Stage</option>
                                    <option value="Task Overdue">Follow-up Task becomes Overdue</option>
                                </select>
                                <small class="text-muted">The condition that starts the automation.</small>
                            </div>
                            <div class="text-center my-3"><i
                                    class='bx border rounded-circle bg-light p-2 bx-down-arrow-alt'></i></div>
                            <div class="mb-4">
                                <label class="form-label text-muted fw-bold">2. Do this...</label>
                                <select name="action" class="form-select border-success bg-light" required>
                                    <option value="">Select Action</option>
                                    <option value="Send Welcome Email">Send Welcome Email Template</option>
                                    <option value="Send Thank You Email">Send Thank You Email</option>
                                    <option value="Assign Next Task">Auto-assign follow-up call task</option>
                                    <option value="Notify Admin">Send Notification to Administrator</option>
                                </select>
                                <small class="text-muted">The action performed automatically.</small>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-bold">Enable Automation Workflow</button>
                        </form>
                    </div>
                </div>

                <!-- Existing Workflows -->
                <div class="col-md-8">
                    <div class="form-card bg-white p-4 rounded shadow-sm">
                        <h5 class="fw-bold mb-4"><i class='bx bx-list-ul'></i> Active Automations</h5>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%"></th>
                                        <th>Trigger Event</th>
                                        <th><i class='bx bx-right-arrow-alt text-muted'></i></th>
                                        <th>Executed Action</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($automations as $auto)
                                        <tr id="auto-{{ $auto->id }}">
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input status-toggle" type="checkbox"
                                                        data-id="{{ $auto->id }}" {{ $auto->status === 'Active' ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td class="fw-bold text-primary">{{ $auto->trigger_event }}</td>
                                            <td><i class='bx bx-chevron-right fs-4 text-muted'></i></td>
                                            <td><span
                                                    class="badge bg-success bg-opacity-75 text-light px-3 py-2 rounded-pill"><i
                                                        class='bx bx-bolt-circle'></i> {{ $auto->action }}</span></td>
                                            <td>
                                                <span
                                                    class="badge {{ $auto->status === 'Active' ? 'bg-success' : 'bg-secondary' }} status-badge">{{ $auto->status }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class='bx bx-outline fs-1 mb-2'></i><br>
                                                No active workflows. Automate your sales pipeline by creating a rule!
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
            $('.status-toggle').change(function () {
                let autoId = $(this).data('id');
                let $row = $('#auto-' + autoId);

                $.post("{{ route('automations.toggle_status') }}", {
                    _token: "{{ csrf_token() }}",
                    id: autoId
                }, function (res) {
                    if (res.status === 'Active') {
                        $row.find('.status-badge').removeClass('bg-secondary').addClass('bg-success').text('Active');
                    } else {
                        $row.find('.status-badge').removeClass('bg-success').addClass('bg-secondary').text('Inactive');
                    }
                }).fail(function () {
                    alert('Error updating automation status.');
                });
            });
        });
    </script>
@endsection
