@extends('layout')
@section('title', 'Marketing Campaigns - eseCRM')

@section('content')
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i>
            Marketing Campaigns
        </div>

        <div class="container-fluid py-4">
            <div class="row g-4">

                <!-- Create Campaign -->
                <div class="col-md-4">
                    <div class="form-card bg-white p-4 rounded shadow-sm">
                        <h5 class="fw-bold mb-4"><i class='bx bx-plus-circle'></i> New Campaign</h5>
                        <form action="{{ route('campaigns.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label text-muted fw-bold">Campaign Name</label>
                                <input type="text" name="name" class="form-control" required
                                    placeholder="e.g. Summer Sale 2026">
                                <small class="text-muted">Internal name for tracking.</small>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-muted fw-bold">Channel Type</label>
                                <select name="type" class="form-select" required>
                                    <option value="Email">Email Broadcast</option>
                                    <option value="WhatsApp">WhatsApp Message</option>
                                    <option value="SMS">SMS Text</option>
                                    <option value="Web Form">Lead Capture Form</option>
                                </select>
                                <small class="text-muted">The medium through which the campaign runs.</small>
                            </div>
                            <button type="submit" class="btn btn-indigo rounded-pill w-100 fw-bold">Create Draft</button>
                        </form>
                    </div>
                </div>

                <!-- Existing Campaigns -->
                <div class="col-md-8">
                    <div class="form-card bg-white p-4 rounded shadow-sm">
                        <h5 class="fw-bold mb-4"><i class='bx bx-bullseye'></i> Campaign List</h5>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Campaign Name</th>
                                        <th>Channel</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($campaigns as $camp)
                                        <tr id="camp-{{ $camp->id }}">
                                            <td class="fw-bold text-primary">{{ $camp->name }}</td>
                                            <td>
                                                @if($camp->type == 'Email') <i class='bx bx-envelope text-danger'></i>
                                                @elseif($camp->type == 'WhatsApp') <i class='bx bxl-whatsapp text-success'></i>
                                                @elseif($camp->type == 'SMS') <i
                                                    class='bx bx-message-rounded-dots text-info'></i>
                                                @else <i class='bx bx-window-alt text-primary'></i> @endif
                                                {{ $camp->type }}
                                            </td>
                                            <td>
                                                <span
                                                    class="badge {{ $camp->status === 'Active' ? 'bg-success' : 'bg-secondary' }} status-badge">{{ $camp->status }}</span>
                                            </td>
                                            <td>
                                                @if($camp->status === 'Draft')
                                                    <button class="btn btn-sm btn-outline-indigo rounded-pill launch-btn"
                                                        data-id="{{ $camp->id }}"><i class='bx bx-rocket'></i> Launch</button>
                                                @else
                                                    <button class="btn btn-sm btn-light rounded-pill disabled"><i class='bx bx-check'></i>
                                                        Running</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class='bx bx-broadcast fs-1 mb-2'></i><br>
                                                No campaigns created yet. Start marketing to your leads!
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
            $('.launch-btn').click(function () {
                let btn = $(this);
                let campId = btn.data('id');
                let $row = $('#camp-' + campId);

                if (confirm("Are you sure you want to launch this campaign?")) {
                    $.post("{{ route('campaigns.launch') }}", {
                        _token: "{{ csrf_token() }}",
                        id: campId
                    }, function (res) {
                        $row.find('.status-badge').removeClass('bg-secondary').addClass('bg-success').text('Active');
                        btn.replaceWith('<button class="btn btn-sm btn-light disabled"><i class=\'bx bx-check\'></i> Running</button>');
                    }).fail(function () {
                        alert('Error launching campaign.');
                    });
                }
            });
        });
    </script>
@endsection