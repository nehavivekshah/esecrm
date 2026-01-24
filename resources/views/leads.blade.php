@extends('layout')
@section('title','Leads Management - eseCRM')

@section('content')
    @php
        $roles = session('roles');
        $roleArray = is_array($roles->permissions ?? '') ? $roles->permissions : explode(',', (string)($roles->permissions ?? ''));
    @endphp
    
    <style>
        .bg-alert { background-color: #fff1f1 !important; border-left: 5px solid #dc3545 !important; }
        /*.dataTables_wrapper .dataTables_filter { display: none; }*/
        .filter-bar { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 15px; }
        #leadslists tbody tr { cursor: pointer; }
        .section-divider { font-size: 12px; font-weight: 700; text-transform: uppercase; color: #888; border-bottom: 1px solid #eee; padding-bottom: 5px; margin: 15px 0; }
        .form-label { font-weight: 600; font-size: 11px; margin-bottom: 2px; }
        .timeline-box { max-height: 450px; overflow-y: auto; border-left: 2px solid #eee; padding-left: 20px; }
        @media (max-width:767px){
            .mob-style{flex-wrap: wrap; gap: 13px !important;}
            .input-group { min-width: 100%!important; }
            #leadslists_previous { display: none; }
        }
    </style>
    
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i> 
            Leads Board
            <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>
        <div class="container-fluid">
            <!-- Filter & Assign Bar -->
            <div class="filter-bar d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div class="d-flex flex-wrap gap-2">
                    @if(in_array('All',$roleArray))
                    <!-- Sales Rep Filter -->
                    <select id="ajaxSalesRep" class="form-select form-select-sm" style="width: 150px;">
                        <option value="">All Sales Reps</option>
                        @foreach($getUsers as $user)
                            <option value="{{ $user->name }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @endif

                    <!-- Status Filter -->
                    <select id="ajaxStatus" class="form-select form-select-sm" style="width: 120px;">
                        <option value="">Status</option>
                        <option value="0">Fresh</option>
                        <option value="1">Follow Up</option>
                        <option value="9">Loss</option>
                    </select>
                    
                    <button class="btn btn-primary btn-sm" id="refreshBtn"><i class="bx bx-refresh"></i></button>
                </div>

                <div class="d-flex mob-style gap-2">
                    @if(in_array('All',$roleArray))
                    <!-- BULK ASSIGN OPTION -->
                    <div class="input-group input-group-sm" style="width: 280px;">
                        <select id="bulkAssignUserId" class="form-select">
                            <option value="">Assign Selected To...</option>
                            @foreach($getUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-dark" id="btnRunAssign">Apply</button>
                    </div>
                    @endif
                    
                    <!--<input type="text" id="ajaxSearch" class="form-control form-control-sm" placeholder="Search...">-->
                    @if(in_array('leads_export',$roleArray) || in_array('All',$roleArray))
                    <!--<a href="/export-leads-file" class="btn btn-info btn-sm" target="_blank" download="leads.csv" title="Download Leads CSV File"><i class="bx bx-download"></i> <span>Export</span></a>-->
                    @endif
                    
                    @if(in_array('leads_import',$roleArray) || in_array('All',$roleArray))
                    <a href="javascript:void(0)" class="btn btn-warning btn-sm" id="importFile"><i class="bx bx-upload"></i> <span>Import</span></a>
                    <a href="/public/assets/leads.csv" class="btn btn-danger btn-sm" target="_blank" download="leads.csv" title="Download CSV Sample File"><i class="bx bx-download"></i> <span>Sample File</span></a>
                    @endif
                    
                    @if(in_array('leads_add',$roleArray) || in_array('All',$roleArray))
                    <a href="/manage-lead" class="btn btn-primary bg-primary text-white btn-sm"><i class="bx bx-plus"></i> <span>Add New</span></a>
                    @endif
                    <!--<a href="/manage-lead" class="btn btn-success btn-sm"><i class="bx bx-plus"></i></a>-->
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive mb-3">
                <table id="leadslists" class="table table-hover table-condensed m-table leads" style="width:100%;border-radius:5px;overflow:hidden;">
                    <thead>
                        <tr>
                            <th class="checkbox-column pr-0"><input type="checkbox" id="checkall" value="all"></th>
                            <th>Name</th>
                            <th class="m-none">Company</th>
                            <th class="m-none">GST No.</th>
                            <th class="m-none mw80">Mobile No.</th>
                            <th class="m-none mw60">Status</th>
                            <th class="m-none mw80">Since</th>
                            <th class="m-none mw80">Purpose</th>
                            <th class="m-none mw60">Value</th>
                            <th class="m-none mw70">Last Talk</th>
                            <th class="m-none mw150">Next Move</th>
                            @if(in_array('All',$roleArray))
                            <th class="m-none mw60">Assigned</th>
                            @else
                            <th class="m-none mw60">POC</th>
                            @endif
                            <th class="position-sticky end-0" width="60px">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Modal for Edit/Profile (same as before) -->
    <div class="modal fade" id="leadModal" tabindex="-1" aria-hidden="true">
        <!-- ... (Internal modal content remains the same as provided in previous full code) ... -->
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title">Edit Lead Details</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0">
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
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-profile">Profile Info</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-comments">Conversations</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-porposal">Porposal</button></li>
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
                                            <input type="text" class="form-control" id="m_name" name="name" placeholder="Enter Name*" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="email">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-envelope-open'></i></span>
                                            <input type="email" class="form-control" id="m_email" name="email" placeholder="Enter Email Id">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="mobile">Mobile Number*</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-phone'></i></span>
                                            <input type="text" class="form-control" id="m_mob" name="mob" placeholder="Enter Mobile Number*" value="91" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="whatsapp">Whatsapp</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bxl-whatsapp'></i></span>
                                            <input type="text" class="form-control" id="m_whatsapp" name="whatsapp" placeholder="Enter Whatsapp Number" value="91">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="company">Company</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-briefcase'></i></span>
                                            <input type="text" class="form-control" id="m_company" name="company" placeholder="Enter Company">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="position">Position</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-user'></i></span>
                                            <input type="text" class="form-control" id="m_position" name="position" placeholder="Enter Position">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="industry">Industry</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-building'></i></span>
                                            <input type="text" class="form-control" id="m_industry" name="industry" placeholder="Enter Industry">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="industry">GST No.</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-building'></i></span>
                                            <input type="text" class="form-control" id="m_gstno" name="gstno" placeholder="Enter GST No.">
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
                                            <input type="text" class="form-control" id="m_address" name="address[address]" placeholder="Enter Address">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="city">City</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-map'></i></span>
                                            <input type="text" class="form-control" id="m_city" name="address[city]" placeholder="Enter City">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="state">State</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-map-pin'></i></span>
                                            <input type="text" class="form-control" id="m_state" name="address[state]" placeholder="Enter State">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="country">Country</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-globe'></i></span>
                                            <input type="text" class="form-control" id="m_country" name="address[country]" placeholder="Enter Country">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="zip">Zip/Postal Code</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-pin'></i></span>
                                            <input type="text" class="form-control" id="m_zip" name="address[zip]" placeholder="Enter Zip/Postal Code">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="website">Website</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-link'></i></span>
                                            <input type="url" class="form-control" id="m_website" name="website" placeholder="Enter Website Link">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="source">Language</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-world'></i></span>
                                            <input type="text" class="form-control" id="m_language" name="language" placeholder="Enter Language">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="source">Purpose</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-target-lock'></i></span>
                                            <input type="text" class="form-control" id="m_purpose" name="purpose" placeholder="Enter Purpose">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="values">Lead Value</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-rupee'></i></span>
                                            <input type="number" class="form-control" id="m_value" name="values" placeholder="Enter Values">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="source">Assigned</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-share-alt'></i></span>
                                            <input type="text" class="form-control" id="m_assigned" name="assigned" placeholder="Enter Assigned User Name">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="source">POC</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-user-check'></i></span>
                                            <input type="text" class="form-control" id="m_poc" name="poc" placeholder="Enter Point of Contact">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label for="source">Tags</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-user-check'></i></span>
                                            <input type="text" class="form-control" id="m_tags" name="tags" placeholder="Enter Tags (Search Keywords, K2)">
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Status:</label><br>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-list-check'></i></span>
                                            <select name="status" id="m_status" class="form-control">
                                                <option value="0">Fresh</option><option value="1">Follow Up</option><option value="5">Converted</option><option value="9">Loss</option>
                                            </select>
                                        </div>
                                    </div>
                                    @if(in_array('leads_edit',$roleArray) || in_array('leads_delete',$roleArray) || in_array('All',$roleArray))
                                    <div class="form-group col-md-12 mt-3 d-flex justify-content-between">
                                        @if(in_array('leads_delete',$roleArray) || in_array('All',$roleArray))
                                        <button type="button" class="btn btn-danger border px-4 leadDelete" id="leadDelete" data-page="leadDelete">Delete</button>
                                        @else
                                        <div></div>
                                        @endif
                                        
                                        @if(in_array('leads_edit',$roleArray) || in_array('All',$roleArray))
                                        <div>
                                            <button type="reset" class="btn btn-light ml-auto border px-4">Reset</button>
                                            <button type="submit" class="btn btn-success bg-success text-white px-4">Save</button>
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
                                <div class="col-md-7 border-end"><div id="commentHistory" class="timeline-box"></div></div>
                                <div class="col-md-5">
                                    <form id="addCommentForm">
                                        @csrf <input type="hidden" name="lead_id" id="c_lead_id">
                                        <div class="mb-3"><label class="form-label">Message</label><textarea name="msg" id="c_msg" class="form-control form-control-sm" rows="4" required></textarea></div>
                                        <div class="mb-3"><label class="form-label">Next Reminder</label><input type="datetime-local" name="next_date" id="c_next_date" class="form-control form-control-sm" required></div>
                                        <button type="submit" class="btn btn-success bg-success btn-sm w-100 py-2 text-white">Save Comment</button>
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
                                        <table class="table table-hover table-striped rounded mb-0" style="width: 100%;border: 1px solid #ccc; border-radius: 5px !important; overflow: hidden;">
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
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // 1. Init DataTable
            var table = $('#leadslists').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 50,
            
                ajax: { 
                    url: "{{ route('leads.index') }}", 
                    data: function(d) { 
                        d.status = $('#ajaxStatus').val(); 
                        d.assign_user = $('#ajaxSalesRep').val(); 
                    } 
                },
            
                columns: [
                    { data:'checkbox', orderable:false, searchable:false },
                    { data:'name' },
                    { data:'company' },
                    { data:'gstno' },
                    { data:'mobile' },
                    { data:'status' },
                    { data:'since' },
                    { data:'purpose' },
                    { data:'value' },
                    { data:'last_talk' },
                    { data:'next_move' },
                    { data:'assigned' },
                    { data:'action', orderable:false, searchable:false }
                ],
            
                columnDefs: [
                    { targets: 0, className: 'checkbox-column pr-0' },
                    { targets: 1, className: 'mw150' },
                    { targets: 2, className: 'm-none' },
                    { targets: 3, className: 'm-none' },
                    { targets: 4, className: 'm-none mw80' },
                    { targets: 5, className: 'm-none mw60' },
                    { targets: 6, className: 'm-none mw80' },
                    { targets: 7, className: 'm-none mw80' },
                    { targets: 8, className: 'm-none mw60' },
                    { targets: 9, className: 'm-none mw70 tm' },
                    { targets: 10, className: 'm-none mw150' },
                    { targets: 11, className: 'm-none mw60' },
                    { targets: 12, className: 'position-sticky end-0 bg-default mw60' }
                ],
            
                createdRow: function(row, data, dataIndex) { 
                    if (data.row_class) {
                        $(row).addClass(data.row_class);
                    }
                    $(row).attr('data-id', data.id); 
                }
            });


            // 2. Filters & Refresh
            $('#ajaxSearch').keyup(function() { table.search($(this).val()).draw(); });
            $('#ajaxStatus, #ajaxSalesRep').change(function() { table.draw(); });
            $('#refreshBtn').click(function() { table.draw(); });

            // 3. BULK ASSIGN LOGIC
            $('#btnRunAssign').click(function() {
                var userId = $('#bulkAssignUserId').val();
                var selectedLeads = [];
                
                // Collect all checked lead IDs
                $('.checklead:checked').each(function() {
                    selectedLeads.push($(this).val());
                });

                if (selectedLeads.length === 0) {
                    alert('Please select at least one lead using the checkboxes.');
                    return;
                }
                if (!userId) {
                    alert('Please select a user to assign these leads to.');
                    return;
                }

                if (confirm('Assign ' + selectedLeads.length + ' leads to the selected user?')) {
                    $.post("{{ route('leads.bulkAssign') }}", {
                        _token: "{{ csrf_token() }}",
                        leads: selectedLeads,
                        user_id: userId
                    }, function(res) {
                        alert('Leads successfully assigned!');
                        table.ajax.reload(null, false); // Reload table without jumping pages
                        $('#checkall').prop('checked', false);
                    }).fail(function() {
                        alert('Error assigning leads.');
                    });
                }
            });

            // 4. Modal Open & Row Click
            $(document).on('click', '#leadslists tbody tr', function(e) {
                if ($(e.target).closest('input, a, button').length) return;
                var id = $(this).attr('data-id'); 
                if(!id) return;

                $('#m_id').val(id); $('#c_lead_id').val(id);
                $.get("/get-lead-details/" + id, function(data) {
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
                    data.comments.forEach(function(c){
                        html += '<div class="pb-2 border-bottom mb-2"><small class="text-primary fw-bold">'+c.created_at+'</small><p class="mb-0 small">'+c.msg+'</p></div>';
                    });
                    $('#commentHistory').html(html || 'No history.');
                    $('#leadModal').modal('show');
                });
            });

            // 5. Check All logic
            $('#checkall').click(function() {
                $('.checklead').prop('checked', $(this).prop('checked'));
            });

            // 6. Submit Forms
            $('#editLeadForm').on('submit', function(e) {
                e.preventDefault();
                $.get("{{ route('leads.update') }}", $(this).serialize(), function() { 
                    alert('Profile Updated'); 
                    $('#leadModal').modal('hide');
                    table.ajax.reload(null, false); 
                });
            });

            $('#addCommentForm').on('submit', function(e) {
                e.preventDefault();
                $.post("{{ route('leads.storeComment') }}", $(this).serialize(), function() { 
                    alert('Comment Saved'); 
                    $('#leadModal').modal('hide'); 
                    table.ajax.reload(null, false); 
                });
            });
            
            $('#leadDelete').on('click', function() {
                if(confirm('Are you sure you want to delete this lead?')) {
                    let id = $('#m_id').val();
                    
                    $.post("/delete-lead", { 
                        _token: "{{ csrf_token() }}", 
                        id: id 
                    }, function(res) {
                        alert('Lead deleted successfully');
                        $('#leadModal').modal('hide');
                        
                        // FIX: Use this instead of table.ajax.reload()
                        $('#leadslists').DataTable().ajax.reload(null, false); 
                        
                    }).fail(function(xhr) {
                        alert('Error: ' + xhr.statusText);
                    });
                }
            });
        });
    </script>
@endsection