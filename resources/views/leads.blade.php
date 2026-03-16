@extends('layout')
@section('title', 'Leads Management - eseCRM')

@section('content')
    @php
        $roles = session('roles');
        $roleArray = is_array($roles->permissions ?? '') ? $roles->permissions : explode(',', (string) ($roles->permissions ?? ''));
    @endphp

    <style>
        .bg-alert {
            background-color: #fff1f1 !important;
            border-left: 5px solid #dc3545 !important;
        }

        /*.dataTables_wrapper .dataTables_filter { display: none; }*/
        #leadslists tbody tr {
            cursor: pointer;
        }

        .section-divider {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #888;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin: 15px 0;
        }

        .form-label {
            font-weight: 600;
            font-size: 11px;
            margin-bottom: 2px;
        }

        .timeline-box {
            max-height: 450px;
            overflow-y: auto;
            border-left: 2px solid #eee;
            padding-left: 20px;
        }

        @media (max-width:767px) {
            .mob-style {
                flex-wrap: wrap;
                gap: 13px !important;
            }

            .input-group {
                min-width: 100% !important;
            }

            #leadslists_previous {
                display: none;
            }
        }
    </style>

    <section class="task__section">
        @include('inc.header', ['title' => 'Leads Board'])

        <div class="dash-container">

            {{-- Toolbar --}}
            <div class="leads-toolbar mb-3">
                {{-- Left: Filters --}}
                <div class="leads-toolbar-left">
                    @if(in_array('All', $roleArray))
                        <select id="ajaxSalesRep" class="lb-select">
                            <option value="">All Sales Reps</option>
                            @foreach($getUsers as $user)
                                <option value="{{ $user->name }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <select id="ajaxStatus" class="lb-select">
                        <option value="">All Status</option>
                        <option value="0">🟢 Fresh</option>
                        <option value="1">🔵 Follow Up</option>
                        <option value="9">🔴 Loss</option>
                    </select>
                    <button class="lb-icon-btn" id="refreshBtn" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                </div>

                {{-- Right: Actions --}}
                <div class="leads-toolbar-right">
                    @if(in_array('leads_import', $roleArray) || in_array('All', $roleArray))
                        <a href="javascript:void(0)" class="lb-btn lb-btn-secondary" id="importFile">
                            <i class="bx bx-upload"></i>
                            <span class="d-none d-sm-inline">Import</span>
                        </a>
                        <a href="/public/assets/leads.csv" class="lb-btn lb-btn-ghost" target="_blank" download="leads.csv" title="Download CSV Sample">
                            <i class="bx bx-download"></i>
                            <span class="d-none d-sm-inline">Sample</span>
                        </a>
                    @endif
                    @if(in_array('leads_add', $roleArray) || in_array('All', $roleArray))
                        <a href="/manage-lead" class="lb-btn lb-btn-primary">
                            <i class="bx bx-plus"></i>
                            <span>Add Lead</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Leads Table --}}
            <div class="dash-card">
                <div class="table-responsive">
                    <table id="leadslists" class="leads-table" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th class="m-none">Company</th>
                                <th class="m-none mw80">Mobile</th>
                                <th class="m-none mw60">Status</th>
                                <th class="m-none mw80">Since</th>
                                <th class="m-none mw80">Purpose</th>
                                <th class="m-none mw60">Value</th>
                                <th class="m-none mw70">Last Talk</th>
                                <th class="m-none mw150">Next Move</th>
                                @if(in_array('All', $roleArray))
                                    <th class="m-none mw60">Assigned</th>
                                @else
                                    <th class="m-none mw60">POC</th>
                                @endif
                                <th class="text-center" width="60px">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>

    <!-- Offcanvas for Edit/Profile -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="leadModal" aria-labelledby="leadModalLabel" style="width: 800px; max-width: 100vw; border-top-left-radius: 20px; border-bottom-left-radius: 20px; box-shadow: -10px 0 30px rgba(0,0,0,0.1);">
        <div class="offcanvas-header lb-offcanvas-header">
            <div class="d-flex align-items-center gap-3">
                <div class="lb-offcanvas-avatar" id="leadAvatarBadge">L</div>
                <div>
                    <h5 class="offcanvas-title" id="leadModalLabel">Lead Details</h5>
                    <span class="lb-offcanvas-subtitle" id="leadAvatarCompany">Loading...</span>
                </div>
            </div>
            <button type="button" class="lb-offcanvas-close" data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="bx bx-x"></i>
            </button>
        </div>
        <div class="offcanvas-body lb-offcanvas-body">
                    <!--<ul class="nav nav-tabs mb-3" id="leadModalTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="lead-details-tab" data-bs-toggle="pill" data-bs-target="#lead-details" type="button" role="tab" aria-controls="lead-details" aria-selected="true">Profile</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="porposal-tab" data-bs-toggle="pill" data-bs-target="#porposal" type="button" role="tab" aria-controls="comments" aria-selected="false" tabindex="-1">Porposal</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="comments-tab" data-bs-toggle="pill" data-bs-target="#comments" type="button" role="tab" aria-controls="comments" aria-selected="false" tabindex="-1">Conversations</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="new-comment-tab" data-bs-toggle="pill" data-bs-target="#new-comment" type="button" role="tab" aria-controls="new-comment" aria-selected="false" tabindex="-1">Reminder</button>
                            </li>
                        </ul>-->
                    <ul class="nav nav-tabs nav-justified bg-default mb-3" id="leadModalTab" role="tablist">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab"
                                data-bs-target="#tab-profile">Profile Info</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                data-bs-target="#tab-comments">Conversations</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab"
                                data-bs-target="#tab-porposal">Porposal</button></li>
                    </ul>
                    <div class="tab-content">
                        <!-- Profile Tab -->
                        <div class="tab-pane fade show active" id="tab-profile">
                            <form id="editLeadForm">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-12 text-left">
                                        <h4 class="h5 font-weight-bold divider">Lead Information</h4>
                                        <span class="div-line mb-0"></span>
                                        <input type="hidden" id="m_id" name="id" value="">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="name">Name*</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-user'></i></span>
                                            <input type="text" class="form-control" id="m_name" name="name"
                                                placeholder="Enter Name*" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="email">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-envelope-open'></i></span>
                                            <input type="email" class="form-control" id="m_email" name="email"
                                                placeholder="Enter Email Id">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="mobile">Mobile Number*</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-phone'></i></span>
                                            <input type="text" class="form-control" id="m_mob" name="mob"
                                                placeholder="Enter Mobile Number*" value="91" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="whatsapp">Whatsapp</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bxl-whatsapp'></i></span>
                                            <input type="text" class="form-control" id="m_whatsapp" name="whatsapp"
                                                placeholder="Enter Whatsapp Number" value="91">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="company">Company</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-briefcase'></i></span>
                                            <input type="text" class="form-control" id="m_company" name="company"
                                                placeholder="Enter Company">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="position">Position</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-user'></i></span>
                                            <input type="text" class="form-control" id="m_position" name="position"
                                                placeholder="Enter Position">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="industry">Industry</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-building'></i></span>
                                            <input type="text" class="form-control" id="m_industry" name="industry"
                                                placeholder="Enter Industry">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="industry">GST No.</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-building'></i></span>
                                            <input type="text" class="form-control" id="m_gstno" name="gstno"
                                                placeholder="Enter GST No.">
                                        </div>
                                    </div>
                                    <div class="col-12 text-left pt-3">
                                        <h4 class="h5 font-weight-bold divider">Address Details</h4>
                                        <span class="div-line mb-0"></span>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="address">Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-home'></i></span>
                                            <input type="text" class="form-control" id="m_address" name="address[address]"
                                                placeholder="Enter Address">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="city">City</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-map'></i></span>
                                            <input type="text" class="form-control" id="m_city" name="address[city]"
                                                placeholder="Enter City">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="state">State</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-map-pin'></i></span>
                                            <input type="text" class="form-control" id="m_state" name="address[state]"
                                                placeholder="Enter State">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="country">Country</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-globe'></i></span>
                                            <input type="text" class="form-control" id="m_country" name="address[country]"
                                                placeholder="Enter Country">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="zip">Zip/Postal Code</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-pin'></i></span>
                                            <input type="text" class="form-control" id="m_zip" name="address[zip]"
                                                placeholder="Enter Zip/Postal Code">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="website">Website</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-link'></i></span>
                                            <input type="url" class="form-control" id="m_website" name="website"
                                                placeholder="Enter Website Link">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="source">Language</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-world'></i></span>
                                            <input type="text" class="form-control" id="m_language" name="language"
                                                placeholder="Enter Language">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="source">Purpose</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-target-lock'></i></span>
                                            <input type="text" class="form-control" id="m_purpose" name="purpose"
                                                placeholder="Enter Purpose">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="values">Lead Value</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-rupee'></i></span>
                                            <input type="number" class="form-control" id="m_value" name="values"
                                                placeholder="Enter Values">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="source">Assigned</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-share-alt'></i></span>
                                            <input type="text" class="form-control" id="m_assigned" name="assigned"
                                                placeholder="Enter Assigned User Name">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="source">POC</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-user-check'></i></span>
                                            <input type="text" class="form-control" id="m_poc" name="poc"
                                                placeholder="Enter Point of Contact">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="source">Tags</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-user-check'></i></span>
                                            <input type="text" class="form-control" id="m_tags" name="tags"
                                                placeholder="Enter Tags (Search Keywords, K2)">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Status:</label><br>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-list-check'></i></span>
                                            <select name="status" id="m_status" class="form-control">
                                                <option value="0">Fresh</option>
                                                <option value="1">Follow Up</option>
                                                <option value="5">Converted</option>
                                                <option value="9">Loss</option>
                                            </select>
                                        </div>
                                    </div>
                                    @if(in_array('leads_edit', $roleArray) || in_array('leads_delete', $roleArray) || in_array('All', $roleArray))
                                        <div class="form-group col-md-12 mt-3 d-flex justify-content-between">
                                            @if(in_array('leads_delete', $roleArray) || in_array('All', $roleArray))
                                                <button type="button" class="btn btn-danger border px-4 leadDelete" id="leadDelete"
                                                    data-page="leadDelete">Delete</button>
                                            @else
                                                <div></div>
                                            @endif

                                            @if(in_array('leads_edit', $roleArray) || in_array('All', $roleArray))
                                                <div>
                                                    <button type="reset" class="btn btn-light ml-auto border px-4">Reset</button>
                                                    <button type="submit"
                                                        class="btn btn-indigo px-4">Save</button>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </form>
                        </div>

                        <!-- Conversations Tab -->
                        <div class="tab-pane fade" id="tab-comments">
                            <div class="row">
                                <div class="col-md-7 border-end">
                                    <div id="commentHistory" class="timeline-box"></div>
                                </div>
                                <div class="col-md-5">
                                    <form id="addCommentForm">
                                        @csrf <input type="hidden" name="lead_id" id="c_lead_id">
                                        <div class="mb-3"><label class="form-label">Message</label><textarea name="msg"
                                                id="c_msg" class="form-control form-control-sm" rows="4"
                                                required></textarea></div>
                                        <div class="mb-3"><label class="form-label">Next Reminder</label><input
                                                type="datetime-local" name="next_date" id="c_next_date"
                                                class="form-control form-control-sm" required></div>
                                        <button type="submit"
                                            class="btn btn-indigo btn-sm w-100 py-2">Save
                                            Comment</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Proposals Tab -->
                        <div class="tab-pane fade" id="tab-porposal">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5 class="mb-0">Proposals</h5>
                                        <a href="/manage-proposal" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus mr-1"></i> New Proposal
                                        </a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped rounded mb-0"
                                            style="width: 100%;border: 1px solid #ccc; border-radius: 5px !important; overflow: hidden;">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="110px" class="m-none">#</th>
                                                    <th>Subject</th>
                                                    <th>Total</th>
                                                    <th width="110px" class="m-none">Date</th>
                                                    <th width="110px" class="m-none">Open Till</th>
                                                    <th class="m-none">Status</th>
                                                    <th width="110px" class="m-none text-right">Created Date</th>
                                                </tr>
                                            </thead>
                                            <tbody id="Proposals"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            // 1. Init DataTable
            var table = $('#leadslists').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 50,

                ajax: {
                    url: "{{ route('leads.index') }}",
                    data: function (d) {
                        d.status = $('#ajaxStatus').val();
                        d.assign_user = $('#ajaxSalesRep').val();
                    }
                },

                columns: [
                    { data: 'name' },
                    { data: 'company' },
                    { data: 'mobile' },
                    { data: 'status' },
                    { data: 'since' },
                    { data: 'purpose' },
                    { data: 'value' },
                    { data: 'last_talk' },
                    { data: 'next_move' },
                    { data: 'assigned' },
                    { data: 'action', orderable: false, searchable: false }
                ],

                columnDefs: [
                    { targets: 0, className: 'mw150' },
                    { targets: 1, className: 'm-none' },
                    { targets: 2, className: 'm-none mw80' },
                    { targets: 3, className: 'm-none mw60' },
                    { targets: 4, className: 'm-none mw80' },
                    { targets: 5, className: 'm-none mw80' },
                    { targets: 6, className: 'm-none mw60' },
                    { targets: 7, className: 'm-none mw70 tm' },
                    { targets: 8, className: 'm-none mw150' },
                    { targets: 9, className: 'm-none mw60' },
                    { targets: 10, className: 'position-sticky end-0 bg-default mw60' }
                ],

                createdRow: function (row, data, dataIndex) {
                    if (data.row_class) {
                        $(row).addClass(data.row_class);
                    }
                    $(row).attr('data-id', data.id);
                }
            });


            // 2. Filters & Refresh
            $('#ajaxSearch').keyup(function () { table.search($(this).val()).draw(); });
            $('#ajaxStatus, #ajaxSalesRep').change(function () { table.draw(); });
            $('#refreshBtn').click(function () { table.draw(); });



            // 4. Modal Open & Row Click
            $(document).on('click', '#leadslists tbody tr', function (e) {
                if ($(e.target).closest('input, a, button').length) return;
                var id = $(this).attr('data-id');
                if (!id) return;

                $('#m_id').val(id); $('#c_lead_id').val(id);
                $.get("/get-lead-details/" + id, function (data) {
                    var l = data.lead;
                    const location = JSON.parse(l.location);
                    $('#m_name').val(l.name);
                    $('#m_email').val(l.email);
                    $('#m_mob').val(l.mob);
                    $('#m_whatsapp').val(l.whatsapp);
                    $('#m_company').val(l.company);
                    $('#m_position').val(l.position);
                    $('#m_industry').val(l.industry);
                    $('#m_gstno').val(l.gstno);
                    $('#m_address').val(location['address'] ?? '');
                    $('#m_city').val(location['city'] ?? '');
                    $('#m_state').val(location['state'] ?? '');
                    $('#m_country').val(location['country'] ?? '');
                    $('#m_zip').val(location['zip'] ?? '');
                    $('#m_website').val(l.website);
                    $('#m_language').val(l.language);
                    $('#m_purpose').val(l.purpose);
                    $('#m_value').val(l.values);
                    $('#m_assigned').val(l.assigned);
                    $('#m_poc').val(l.poc);
                    $('#m_status').val(l.status);
                    $('#m_tags').val(l.tags);

                    var html = '';
                    data.comments.forEach(function (c) {
                        html += '<div class="pb-2 border-bottom mb-2"><small class="text-primary fw-bold">' + c.created_at + '</small><p class="mb-0 small">' + c.msg + '</p></div>';
                    });
                    $('#commentHistory').html(html || 'No history.');
                    $('#leadModal').offcanvas('show');
                });
            });



            // 6. Submit Forms
            $('#editLeadForm').on('submit', function (e) {
                e.preventDefault();
                $.get("{{ route('leads.update') }}", $(this).serialize(), function () {
                    alert('Profile Updated');
                    $('#leadModal').offcanvas('hide');
                    table.ajax.reload(null, false);
                });
            });

            $('#addCommentForm').on('submit', function (e) {
                e.preventDefault();
                $.post("{{ route('leads.storeComment') }}", $(this).serialize(), function () {
                    alert('Comment Saved');
                    $('#leadModal').offcanvas('hide');
                    table.ajax.reload(null, false);
                });
            });

            $('#leadDelete').on('click', function () {
                if (confirm('Are you sure you want to delete this lead?')) {
                    let id = $('#m_id').val();

                    $.post("/delete-lead", {
                        _token: "{{ csrf_token() }}",
                        id: id
                    }, function (res) {
                        alert('Lead deleted successfully');
                        $('#leadModal').offcanvas('hide');

                        // FIX: Use this instead of table.ajax.reload()
                        $('#leadslists').DataTable().ajax.reload(null, false);

                    }).fail(function (xhr) {
                        alert('Error: ' + xhr.statusText);
                    });
                }
            });
        });
    </script>

    {{-- Hidden form for CSV import (required by #importFile button handler) --}}
    <form id="leadsubmit" action="/import-leads-file" method="post" enctype="multipart/form-data">
        @csrf
        <input type="file" name="impLeadFile" id="impLeadFile" accept=".csv, .xls" style="display:none;" />
    </form>

@endsection