@extends('layout')
@section('title', 'Customers - eseCRM')

@section('content')

    @php $location = json_decode(($clients->location ?? '[]'), true); @endphp
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i>
            @if(!empty($_GET['id'])) Edit Customer @else New Customer @endif
            <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>
        <div class="container-fluid py-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="/clients" class="btn btn-light border-0 shadow-sm rounded-circle p-2"
                    style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="bx bx-arrow-back" style="font-size: 1.2rem; color: var(--color-default);"></i>
                </a>
                <h1 class="h4 fw-bold mb-0">@if(!empty($_GET['id'])) Edit Customer Details @else Add New Customer @endif
                </h1>
            </div>

            @if(!empty($_GET['id']))
                <!-- Nav Tabs -->
                <ul class="nav nav-pills mb-4 gap-2" id="clientTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill px-4" id="profile-tab" data-bs-toggle="pill"
                            data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="true"><i
                                class='bx bx-user me-1'></i> Profile Details</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4" id="interactions-tab" data-bs-toggle="pill"
                            data-bs-target="#interactions" type="button" role="tab" aria-controls="interactions"
                            aria-selected="false"><i class='bx bx-history me-1'></i> Interaction History</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4" id="documents-tab" data-bs-toggle="pill"
                            data-bs-target="#documents" type="button" role="tab" aria-controls="documents"
                            aria-selected="false"><i class='bx bx-file me-1'></i> Documents</button>
                    </li>
                </ul>
            @endif

            <div class="tab-content" id="clientTabsContent">
                <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                    <form action="manage-client" method="post" class="row g-4">
                        @csrf
                        <input type="hidden" name="id" value="{{ $_GET['id'] ?? '' }}">

                        <!-- Primary Information -->
                        <div class="col-lg-6">
                            <div class="form-card">
                                <div class="section-title">
                                    <i class='bx bx-user'></i> Primary Information
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name">Name*</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-user'></i></span>
                                            <input type="text" class="form-control" id="name" name="name"
                                                placeholder="Full Name" value="{{ $clients->name ?? '' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email">Email Address*</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-envelope'></i></span>
                                            <input type="email" class="form-control" id="email" name="email"
                                                placeholder="email@example.com" value="{{ $clients->email ?? '' }}"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mobile">Mobile Number*</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-phone'></i></span>
                                            <input type="text" class="form-control" id="mob" name="mob"
                                                placeholder="91XXXXXXXXXX" value="{{ $clients->mob ?? '91' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="alterMob">Alternative Mobile</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-phone'></i></span>
                                            <input type="text" class="form-control" id="alterMob" name="alterMob"
                                                placeholder="91XXXXXXXXXX" value="{{ $clients->alterMob ?? '91' }}">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label for="whatsapp">Whatsapp</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bxl-whatsapp'></i></span>
                                            <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                                                placeholder="91XXXXXXXXXX" value="{{ $clients->whatsapp ?? '91' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Business Details -->
                        <div class="col-lg-6">
                            <div class="form-card">
                                <div class="section-title">
                                    <i class='bx bx-briefcase'></i> Business Details
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="company">Company Name*</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-buildings'></i></span>
                                            <input type="text" class="form-control" id="company" name="company"
                                                placeholder="Enter Company" value="{{ $clients->company ?? '' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="gst">GST No.</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-id-card'></i></span>
                                            <input type="text" class="form-control" id="gst" name="gst"
                                                placeholder="GSTIN Number" value="{{ $clients->gstno ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lifecycle_stage">Lifecycle Stage</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-pie-chart-alt-2'></i></span>
                                            <select class="form-select" id="lifecycle_stage" name="lifecycle_stage">
                                                <option value="" {{ empty($clients->lifecycle_stage) ? 'selected' : '' }}>
                                                    Select Stage</option>
                                                <option value="Lead" {{ ($clients->lifecycle_stage ?? '') == 'Lead' ? 'selected' : '' }}>Lead</option>
                                                <option value="Marketing Qualified" {{ ($clients->lifecycle_stage ?? '') == 'Marketing Qualified' ? 'selected' : '' }}>Marketing Qualified
                                                </option>
                                                <option value="Sales Qualified" {{ ($clients->lifecycle_stage ?? '') == 'Sales Qualified' ? 'selected' : '' }}>Sales Qualified</option>
                                                <option value="Customer" {{ ($clients->lifecycle_stage ?? '') == 'Customer' ? 'selected' : '' }}>Customer</option>
                                                <option value="Evangelist" {{ ($clients->lifecycle_stage ?? '') == 'Evangelist' ? 'selected' : '' }}>Evangelist</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="position">Position</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-user-pin'></i></span>
                                            <input type="text" class="form-control" id="position" name="position"
                                                placeholder="Job Title" value="{{ $clients->position ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="industry">Industry</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-cog'></i></span>
                                            <input type="text" class="form-control" id="industry" name="industry"
                                                placeholder="e.g. IT, Manufacturing" value="{{ $clients->industry ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="website">Website</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-globe'></i></span>
                                            <input type="url" class="form-control" name="website"
                                                placeholder="https://example.com" value="{{ $clients->website ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location Details -->
                        <div class="col-lg-6">
                            <div class="form-card">
                                <div class="section-title">
                                    <i class='bx bx-map'></i> Location Details
                                </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="address">Full Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-home'></i></span>
                                            <input type="text" class="form-control" id="address" name="address[address]"
                                                placeholder="Street, Building" value="{{ $location['address'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="city">City</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-map-alt'></i></span>
                                            <input type="text" class="form-control" id="city" name="address[city]"
                                                placeholder="City" value="{{ $location['city'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="state">State</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-map-pin'></i></span>
                                            <input type="text" class="form-control" id="state" name="address[state]"
                                                placeholder="State" value="{{ $location['state'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="country">Country</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-globe-alt'></i></span>
                                            <input type="text" class="form-control" name="address[country]"
                                                placeholder="Country" value="{{ $location['country'] ?? 'India' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="zip">Zip/Postal Code</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-pin'></i></span>
                                            <input type="text" class="form-control" name="address[zip]"
                                                placeholder="Zip Code" value="{{ $location['zip'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Departments & Branches -->
                        <div class="col-lg-6">
                            <div class="form-card">
                                <div class="section-title d-flex justify-content-between align-items-center">
                                    <span><i class='bx bx-git-branch'></i> Departments & Branches</span>
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill"
                                        id="addDepartment">
                                        <i class="bx bx-plus"></i> Add New
                                    </button>
                                </div>
                                <div class="table-responsive mt-3" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-sm table-hover" id="departmentTable">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th class="border-0">Dept Name</th>
                                                <th class="border-0">Branch</th>
                                                <th class="border-0">POC</th>
                                                <th class="border-0 text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="departmentBody">
                                            @if(isset($clients->departments) && count($clients->departments) > 0)
                                                @foreach($clients->departments as $index => $dept)
                                                    <tr>
                                                        <td class="pb-3">
                                                            <input type="text" name="departments[{{$index}}][name]"
                                                                class="form-control form-control-sm" value="{{$dept->name}}"
                                                                placeholder="Sales">
                                                            <input type="hidden" name="departments[{{$index}}][id]"
                                                                value="{{$dept->id}}">
                                                        </td>
                                                        <td class="pb-3"><input type="text" name="departments[{{$index}}][location]"
                                                                class="form-control form-control-sm" value="{{$dept->location}}"
                                                                placeholder="Location"></td>
                                                        <td class="pb-3"><input type="text" name="departments[{{$index}}][poc]"
                                                                class="form-control form-control-sm" value="{{$dept->poc}}"
                                                                placeholder="POC"></td>
                                                        <td class="pb-3 text-center align-middle">
                                                            <button type="button"
                                                                class="btn btn-light text-danger btn-sm rounded-circle remove-dept">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td class="pb-3"><input type="text" name="departments[0][name]"
                                                            class="form-control form-control-sm" placeholder="Sales"></td>
                                                    <td class="pb-3"><input type="text" name="departments[0][location]"
                                                            class="form-control form-control-sm" placeholder="Location"></td>
                                                    <td class="pb-3"><input type="text" name="departments[0][poc]"
                                                            class="form-control form-control-sm" placeholder="POC"></td>
                                                    <td class="pb-3 text-center align-middle">
                                                        <button type="button"
                                                            class="btn btn-light text-danger btn-sm rounded-circle remove-dept">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                let deptIndex = {{ isset($clients->departments) ? count($clients->departments) : 1 }};

                                document.getElementById('addDepartment').addEventListener('click', function () {
                                    const tbody = document.getElementById('departmentBody');
                                    const tr = document.createElement('tr');
                                    tr.innerHTML = `
                                            <td class="pb-3"><input type="text" name="departments[${deptIndex}][name]" class="form-control form-control-sm" placeholder="Sales"></td>
                                            <td class="pb-3"><input type="text" name="departments[${deptIndex}][location]" class="form-control form-control-sm" placeholder="Location"></td>
                                            <td class="pb-3"><input type="text" name="departments[${deptIndex}][poc]" class="form-control form-control-sm" placeholder="POC"></td>
                                            <td class="pb-3 text-center align-middle">
                                                <button type="button" class="btn btn-light text-danger btn-sm rounded-circle remove-dept">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </td>
                                        `;
                                    tbody.appendChild(tr);
                                    deptIndex++;
                                });

                                document.getElementById('departmentBody').addEventListener('click', function (e) {
                                    if (e.target.closest('.remove-dept')) {
                                        e.target.closest('tr').remove();
                                    }
                                });
                            });
                        </script>

                        <!-- Form Controls -->
                        <div class="col-12 mt-2 mb-5">
                            <div
                                class="d-flex align-items-center justify-content-end gap-3 p-3 bg-white rounded shadow-sm border">
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class='bx bx-reset me-1'></i> Reset Form
                                </button>
                                <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                                    <i class='bx bx-check-circle' style="font-size: 1.2rem;"></i>
                                    @if(!empty($_GET['id'])) Update Customer Details @else Save New Customer @endif
                                </button>
                            </div>
                        </div>
                    </form>
                </div><!-- end profile tab -->

                @if(!empty($_GET['id']))
                    <!-- Interactions Tab -->
                    <div class="tab-pane fade" id="interactions" role="tabpanel" aria-labelledby="interactions-tab">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="form-card">
                                    <div class="section-title"><i class='bx bx-plus-circle'></i> Add Interaction</div>
                                    <form action="{{ route('clients.interaction') }}" method="post"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="client_id" value="{{ $_GET['id'] }}">
                                        <div class="mb-3">
                                            <label>Interaction Type</label>
                                            <select name="type" class="form-select" required>
                                                <option value="Note">Note</option>
                                                <option value="Call">Call Log</option>
                                                <option value="Meeting">Meeting</option>
                                                <option value="Email">Email Sent</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label>Notes / Content</label>
                                            <textarea name="content" class="form-control" rows="4" required
                                                placeholder="Details..."></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <button type="submit" class="btn btn-primary w-100">Save Interaction</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-card">
                                    <div class="section-title"><i class='bx bx-history'></i> History</div>
                                    <div class="timeline-container" style="max-height: 500px; overflow-y: auto;">
                                        @if(isset($interactions) && $interactions->where('type', '!=', 'Document')->count() > 0)
                                            <div class="list-group">
                                                @foreach($interactions->where('type', '!=', 'Document') as $interaction)
                                                    <div
                                                        class="list-group-item list-group-item-action flex-column align-items-start mb-2 border rounded">
                                                        <div class="d-flex w-100 justify-content-between">
                                                            <h6 class="mb-1 fw-bold text-primary">
                                                                @if($interaction->type == 'Note') <i class='bx bx-notepad'></i>
                                                                @elseif($interaction->type == 'Call') <i class='bx bx-phone-call'></i>
                                                                @elseif($interaction->type == 'Meeting') <i class='bx bx-group'></i>
                                                                @else <i class='bx bx-envelope'></i> @endif
                                                                {{ $interaction->type }}
                                                            </h6>
                                                            <small
                                                                class="text-muted">{{ $interaction->created_at->format('d M Y, H:i') }}</small>
                                                        </div>
                                                        <p class="mb-1 mt-2 text-dark">{{ $interaction->content }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center p-4 text-muted">
                                                <i class='bx bx-info-circle fs-1 mb-2'></i>
                                                <p>No interactions logged yet.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end interactions tab -->

                    <!-- Documents Tab -->
                    <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="form-card">
                                    <div class="section-title"><i class='bx bx-upload'></i> Upload Document</div>
                                    <form action="{{ route('clients.interaction') }}" method="post"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="client_id" value="{{ $_GET['id'] }}">
                                        <input type="hidden" name="type" value="Document">
                                        <div class="mb-3">
                                            <label>Description / Title</label>
                                            <input type="text" name="content" class="form-control"
                                                placeholder="e.g. Signed Contract" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>File Attachment</label>
                                            <input type="file" name="attachment" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <button type="submit" class="btn btn-primary w-100">Upload Document</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-card">
                                    <div class="section-title"><i class='bx bx-folder-open'></i> Client Documents</div>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Uploaded On</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(isset($interactions) && $interactions->where('type', 'Document')->count() > 0)
                                                    @foreach($interactions->where('type', 'Document') as $doc)
                                                        <tr>
                                                            <td><i class='bx bx-file text-primary me-2'></i>{{ $doc->content }}</td>
                                                            <td>{{ $doc->created_at->format('d M Y') }}</td>
                                                            <td>
                                                                <a href="{{ asset('storage/' . $doc->attachment_path) }}" target="_blank"
                                                                    class="btn btn-sm btn-outline-primary"><i
                                                                        class='bx bx-download'></i> Download</a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted p-4">No documents uploaded.
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end documents tab -->
                @endif

            </div><!-- end tab content -->
        </div>
    </section>

@endsection