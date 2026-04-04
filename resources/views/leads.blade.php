@extends('layout')
@section('title', 'Leads Management - eseCRM')

@section('content')
    @php
        $roles = session('roles');
        $roleArray = is_array($roles->permissions ?? '') ? $roles->permissions : explode(',', (string) ($roles->permissions ?? ''));
    @endphp

    <link rel="stylesheet" href="{{ asset('assets/css/lead-panel.css') }}">

    <style>
        .bg-alert {
            background-color: #fff1f1 !important;
            border-left: 5px solid #dc3545 !important;
        }
        #leadslists tbody tr { cursor: pointer; }
        .section-divider { font-size: 12px; font-weight: 700; text-transform: uppercase; color: #888; border-bottom: 1px solid #eee; padding-bottom: 5px; margin: 15px 0; }
        .form-label { font-weight: 600; font-size: 11px; margin-bottom: 2px; }
        .timeline-box { max-height: 450px; overflow-y: auto; border-left: 2px solid #eee; padding-left: 20px; }

        /* ─── Bulk Select ─── */
        .lead-cb { width: 16px; height: 16px; cursor: pointer; accent-color: #006666; }
        #leadslists tbody tr.selected-row { background: rgba(0,102,102,0.06) !important; }

        /* ─── Floating Bulk Action Bar ─── */
        #bulkActionBar {
            position: fixed;
            bottom: 28px;
            left: 50%;
            transform: translateX(-50%) translateY(80px);
            opacity: 0;
            transition: transform 0.28s cubic-bezier(.4,0,.2,1), opacity 0.22s;
            z-index: 9999;
            background: #fff;
            border: 1px solid #e8eaed;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.16);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 420px;
            pointer-events: none;
        }
        #bulkActionBar.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
            pointer-events: all;
        }
        #bulkSelCount {
            font-size: 0.82rem;
            font-weight: 700;
            color: #006666;
            background: #e6f4f0;
            padding: 4px 10px;
            border-radius: 20px;
            white-space: nowrap;
        }
        #bulkSalesSelect {
            flex: 1;
            font-size: 0.82rem;
            border: 1px solid #dadce0;
            border-radius: 8px;
            padding: 6px 10px;
            color: #202124;
            outline: none;
        }
        #bulkAssignBtn {
            background: #006666;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 7px 18px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.18s;
        }
        #bulkAssignBtn:hover { background: #004d4d; }
        #bulkClearBtn {
            background: none;
            border: 1px solid #dadce0;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 0.80rem;
            color: #5f6368;
            cursor: pointer;
        }
        @media (max-width:767px) {
            .mob-style { flex-wrap: wrap; gap: 13px !important; }
            .input-group { min-width: 100% !important; }
            #leadslists_previous { display: none; }
            #bulkActionBar { min-width: 90vw; flex-wrap: wrap; bottom: 14px; }
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
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <select id="ajaxStatus" class="lb-select">
                        <option value="">All Status</option>
                        <option value="0">🟢 Fresh</option>
                        <option value="1">🟡 Follow Up</option>
                        <option value="2">🟣 Qualified</option>
                        <option value="3">🟠 Proposal Sent</option>
                        <option value="5">🔵 Closed (Won)</option>
                        <option value="9">🔴 Loss</option>
                    </select>
                    <input type="text" id="ajaxTags" class="lb-select" placeholder="Filter by Tags (e.g. VIP)" style="width: auto; max-width: 150px; padding: 4px 8px;">
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
                        <a href="{{ asset('assets/leads.csv') }}" class="lb-btn lb-btn-ghost" target="_blank" download="leads.csv" title="Download CSV Sample">
                            <i class="bx bx-download"></i>
                            <span class="d-none d-sm-inline">Sample</span>
                        </a>
                    @endif
                    <a href="{{ route('leads.kanban') }}" class="lb-btn lb-btn-ghost" title="Switch to Kanban View">
                        <i class="bx bx-layout"></i>
                        <span class="d-none d-sm-inline">Kanban</span>
                    </a>
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
                                <th class="p-0" style="width:36px;"><input type="checkbox" class="lead-cb" id="selectAllLeads" title="Select all"></th>
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

    {{-- ─── Floating Bulk Action Bar ─── --}}
    <div id="bulkActionBar">
        <span id="bulkSelCount">0 selected</span>
        <select id="bulkSalesSelect">
            <option value="">— Assign to Salesperson —</option>
            @foreach($getUsers as $u)
                <option value="{{ $u->id }}">{{ $u->name }}</option>
            @endforeach
        </select>
        <button id="bulkAssignBtn"><i class="bx bx-user-check"></i> Assign</button>
        <button id="bulkClearBtn">✕ Clear</button>
    </div>

    </section>

    <!-- ═══════════════════════════════════════════════════════════
         LEAD DETAILS MODAL  —  Contract-style UI
    ════════════════════════════════════════════════════════════ -->
    <div class="modal fade" id="leadModal" tabindex="-1" aria-labelledby="leadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width:860px;">
            <div class="modal-content" style="border-radius:16px; border:none;">

                <!-- ── Modal Header (matches contract style) ── -->
                <div class="modal-header" style="border-bottom:1px solid #f0f0f0; padding:16px 20px; gap:12px; align-items:center;">
                    <div class="d-flex align-items-center gap-3 flex-1 min-w-0">
                        <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#004d4d,#006666);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:700;flex-shrink:0;" id="leadAvatarBadge">L</div>
                        <div class="min-w-0">
                            <h5 class="modal-title mb-0" id="ld_display_name" style="font-size:1.05rem; font-weight:700; color:#202124;">Lead Details</h5>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span id="ld_display_company" style="font-size:0.8rem;color:#5f6368;"></span>
                                <span id="ld_status_chip" class="badge" style="font-size:0.7rem;font-weight:600;background:#f9ab00;color:#fff;">Fresh</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <a id="ld_btn_call" href="#" class="btn btn-sm btn-light border rounded-pill" title="Call" style="font-size:0.8rem;color:#006666;"><i class="bx bx-phone"></i></a>
                        <a id="ld_btn_wa" href="#" target="_blank" class="btn btn-sm btn-light border rounded-pill" title="WhatsApp" style="font-size:0.8rem;color:#25d366;"><i class="bx bxl-whatsapp"></i></a>
                        <a id="ld_btn_mail" href="#" class="btn btn-sm btn-light border rounded-pill" title="Email" style="font-size:0.8rem;color:#1a73e8;"><i class="bx bx-envelope"></i></a>
                        <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <!-- ── Nav Pills (matches contract tab style) ── -->
                <div style="border-bottom:1px solid #f0f0f0; padding:0 20px; background:#fff;">
                    <ul class="nav nav-pills gap-1 py-2" id="leadModalTabs">
                        <li class="nav-item">
                            <button class="nav-link active py-1 px-3" style="font-size:0.82rem;font-weight:600;" onclick="ldShowTab('tab-profile', this)">
                                <i class="bx bx-user-circle me-1"></i>Profile
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-1 px-3" style="font-size:0.82rem;font-weight:600;" onclick="ldShowTab('tab-comments', this)">
                                <i class="bx bx-message-detail me-1"></i>Timeline
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-1 px-3" style="font-size:0.82rem;font-weight:600;" onclick="ldShowTab('tab-porposal', this)">
                                <i class="bx bx-file me-1"></i>Proposals
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-1 px-3" style="font-size:0.82rem;font-weight:600;" onclick="ldShowTab('tab-assign', this)">
                                <i class="bx bx-user-plus me-1"></i>Assign
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- ── Modal Body ── -->
                <div class="modal-body px-4 py-3">

                    <!-- ══ PROFILE TAB ══ -->
                    <div class="ld-tab-pane" id="tab-profile" style="display:block;">

                        <!-- View Mode -->
                        <div id="ld-view-mode">
                            <div class="row g-3 mb-3">
                                <!-- Contact -->
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-3 h-100" style="background:#fafafa;">
                                        <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#006666;border-bottom:1px solid #f0f0f0;padding-bottom:6px;margin-bottom:10px;"><i class="bx bx-phone-call me-1"></i>Contact</div>
                                        <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted" style="font-size:0.8rem;"><i class="bx bx-mobile-alt me-1"></i>Mobile</span><span id="v_mob" class="fw-600" style="font-size:0.82rem;">—</span></div>
                                        <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted" style="font-size:0.8rem;"><i class="bx bxl-whatsapp me-1"></i>WhatsApp</span><span id="v_whatsapp" style="font-size:0.82rem;">—</span></div>
                                        <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted" style="font-size:0.8rem;"><i class="bx bx-envelope me-1"></i>Email</span><span id="v_email" style="font-size:0.82rem;">—</span></div>
                                        <div class="d-flex justify-content-between py-1"><span class="text-muted" style="font-size:0.8rem;"><i class="bx bx-world me-1"></i>Language</span><span id="v_language" style="font-size:0.82rem;">—</span></div>
                                    </div>
                                </div>
                                <!-- Business -->
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-3 h-100" style="background:#fafafa;">
                                        <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#006666;border-bottom:1px solid #f0f0f0;padding-bottom:6px;margin-bottom:10px;"><i class="bx bx-buildings me-1"></i>Business</div>
                                        <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted" style="font-size:0.8rem;"><i class="bx bx-briefcase me-1"></i>Company</span><span id="v_company" style="font-size:0.82rem;">—</span></div>
                                        <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted" style="font-size:0.8rem;"><i class="bx bx-user-pin me-1"></i>Position</span><span id="v_position" style="font-size:0.82rem;">—</span></div>
                                        <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted" style="font-size:0.8rem;"><i class="bx bx-trending-up me-1"></i>Industry</span><span id="v_industry" style="font-size:0.82rem;">—</span></div>
                                        <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted" style="font-size:0.8rem;"><i class="bx bx-id-card me-1"></i>GST No.</span><span id="v_gstno" style="font-size:0.82rem;">—</span></div>
                                        <div class="d-flex justify-content-between py-1"><span class="text-muted" style="font-size:0.8rem;"><i class="bx bx-globe me-1"></i>Website</span><span id="v_website" style="font-size:0.82rem;">—</span></div>
                                    </div>
                                </div>
                                <!-- CRM Intelligence -->
                                <div class="col-12">
                                    <div class="p-3 border rounded-3" style="background:#fafafa;">
                                        <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#006666;border-bottom:1px solid #f0f0f0;padding-bottom:6px;margin-bottom:10px;"><i class="bx bx-brain me-1"></i>CRM Intelligence</div>
                                        <div class="row g-0">
                                            <div class="col-md-6 pe-md-3" style="border-right:1px solid #f0f0f0;">
                                                <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted" style="font-size:0.8rem;"><i class="bx bx-target-lock me-1"></i>Purpose</span><span id="v_purpose" style="font-size:0.82rem;">—</span></div>
                                                <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted" style="font-size:0.8rem;"><i class="bx bx-rupee me-1"></i>Lead Value</span><span id="v_value" class="fw-bold text-success" style="font-size:0.82rem;">—</span></div>
                                                <div class="d-flex justify-content-between py-1"><span class="text-muted" style="font-size:0.8rem;"><i class="bx bx-user-check me-1"></i>POC</span><span id="v_poc" style="font-size:0.82rem;">—</span></div>
                                            </div>
                                            <div class="col-md-6 ps-md-3 mt-3 mt-md-0">
                                                <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted" style="font-size:0.8rem;"><i class="bx bx-user-pin me-1"></i>Assigned To</span><span id="v_assigned" class="fw-bold text-primary" style="font-size:0.82rem;">—</span></div>
                                                <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted" style="font-size:0.8rem;"><i class="bx bx-purchase-tag-alt me-1"></i>Tags</span><span id="v_tags" style="font-size:0.82rem;">—</span></div>
                                                <div class="d-flex justify-content-between py-1"><span class="text-muted" style="font-size:0.8rem;"><i class="bx bx-map-pin me-1"></i>Location</span><span id="v_address_full" class="text-muted" style="font-size:0.78rem;text-align:right;max-width:55%;">—</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- View Footer Buttons -->
                            @if(in_array('leads_edit', $roleArray) || in_array('leads_delete', $roleArray) || in_array('All', $roleArray))
                            <div class="pt-3 border-top text-end d-flex gap-2 justify-content-end">
                                @if(in_array('leads_delete', $roleArray) || in_array('All', $roleArray))
                                    <button type="button" class="btn btn-light border rounded-pill px-4 text-danger leadDelete" id="leadDelete" style="font-size:0.875rem;">
                                        <i class="bx bx-trash me-1"></i>Delete
                                    </button>
                                @endif
                                @if(in_array('leads_edit', $roleArray) || in_array('All', $roleArray))
                                    <button type="button" class="btn rounded-pill px-4" id="ld_edit_toggle" style="background:#006666;border:none;color:#fff;font-size:0.875rem;">
                                        <i class="bx bx-edit me-1"></i>Edit Lead
                                    </button>
                                @endif
                            </div>
                            @endif
                        </div>

                        <!-- Edit Mode (hidden by default) -->
                        <div id="ld-edit-mode" style="display:none;">
                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom gap-2">
                                <i class="bx bx-edit-alt text-primary"></i>
                                <span style="font-weight:700;font-size:0.95rem;color:#202124;">Editing Lead</span>
                                <button type="button" class="btn btn-light border rounded-pill btn-sm ms-auto" id="ld_edit_cancel" style="font-size:0.8rem;">
                                    <i class="bx bx-x"></i> Cancel
                                </button>
                            </div>
                            <form id="editLeadForm">
                                @csrf
                                <input type="hidden" id="m_id" name="id">
                                <div class="row g-3">
                                    <!-- Section: Contact -->
                                    <div class="col-12"><p class="m-0" style="font-size:0.7rem;font-weight:700;text-transform:uppercase;color:#006666;letter-spacing:.5px;">Contact Information</p></div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="m_name" name="name" placeholder="Full Name" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">Email</label>
                                        <input type="email" class="form-control" id="m_email" name="email" placeholder="Email">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">Mobile <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="m_mob" name="mob" placeholder="91XXXXXXXXXX" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">WhatsApp</label>
                                        <input type="text" class="form-control" id="m_whatsapp" name="whatsapp" placeholder="91XXXXXXXXXX">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">Language</label>
                                        <input type="text" class="form-control" id="m_language" name="language" placeholder="EN / HI">
                                    </div>

                                    <!-- Section: Business -->
                                    <div class="col-12 mt-1"><p class="m-0" style="font-size:0.7rem;font-weight:700;text-transform:uppercase;color:#006666;letter-spacing:.5px;">Business Details</p></div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">Company</label>
                                        <input type="text" class="form-control" id="m_company" name="company" placeholder="Company">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">Position</label>
                                        <input type="text" class="form-control" id="m_position" name="position" placeholder="e.g. Manager">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">Industry</label>
                                        <input type="text" class="form-control" id="m_industry" name="industry" placeholder="e.g. IT">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">GST No.</label>
                                        <input type="text" class="form-control" id="m_gstno" name="gstno" placeholder="GSTIN">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">Website</label>
                                        <input type="url" class="form-control" id="m_website" name="website" placeholder="https://">
                                    </div>

                                    <!-- Section: Address -->
                                    <div class="col-12 mt-1"><p class="m-0" style="font-size:0.7rem;font-weight:700;text-transform:uppercase;color:#006666;letter-spacing:.5px;">Address</p></div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">Street</label>
                                        <input type="text" class="form-control" id="m_address" name="address[address]" placeholder="Street">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">City</label>
                                        <input type="text" class="form-control" id="m_city" name="address[city]" placeholder="City">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">State</label>
                                        <input type="text" class="form-control" id="m_state" name="address[state]" placeholder="State">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">Country</label>
                                        <input type="text" class="form-control" id="m_country" name="address[country]" placeholder="Country">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">ZIP</label>
                                        <input type="text" class="form-control" id="m_zip" name="address[zip]" placeholder="ZIP Code">
                                    </div>

                                    <!-- Section: CRM -->
                                    <div class="col-12 mt-1"><p class="m-0" style="font-size:0.7rem;font-weight:700;text-transform:uppercase;color:#006666;letter-spacing:.5px;">CRM Intelligence</p></div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">Purpose</label>
                                        <input type="text" class="form-control" id="m_purpose" name="purpose" placeholder="e.g. Sales">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">Lead Value (₹)</label>
                                        <input type="number" class="form-control" id="m_value" name="values" placeholder="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">POC</label>
                                        <input type="text" class="form-control" id="m_poc" name="poc" placeholder="Point of Contact">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">Assign Salesperson</label>
                                        <select class="form-select" id="m_assigned" name="assigned">
                                            <option value="">— Select —</option>
                                            @foreach($getUsers as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">Tags</label>
                                        <input type="text" class="form-control" id="m_tags" name="tags" placeholder="K2, Hot, VIP">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">Status</label>
                                        <select name="status" id="m_status" class="form-select">
                                            <option value="0">🔵 New / Fresh</option>
                                            <option value="1">🟠 Contacted / Follow Up</option>
                                            <option value="2">🟣 Qualified</option>
                                            <option value="3">🟢 Proposal Sent</option>
                                            <option value="5">✅ Closed (Won)</option>
                                            <option value="9">❌ Lost</option>
                                        </select>
                                    </div>

                                    <!-- Edit Buttons -->
                                    <div class="col-12 mt-4 pt-3 border-top text-end d-flex gap-2 justify-content-end">
                                        <button type="reset" class="btn btn-light border rounded-pill px-4" style="font-size:0.875rem;">
                                            <i class="bx bx-reset me-1"></i>Reset
                                        </button>
                                        <button type="submit" class="btn rounded-pill px-4" style="background:#006666;border:none;color:#fff;font-size:0.875rem;">
                                            <i class="bx bx-check-circle me-1"></i>Save Changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- ══ TIMELINE TAB ══ -->
                    <div class="ld-tab-pane" id="tab-comments" style="display:none;">
                        <div class="row g-3">
                            <!-- History -->
                            <div class="col-md-7">
                                <div class="p-3 border rounded-3 h-100" style="background:#fafafa;">
                                    <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#006666;border-bottom:1px solid #f0f0f0;padding-bottom:6px;margin-bottom:12px;"><i class="bx bx-history me-1"></i>Conversation History</div>
                                    <div id="commentHistory" class="ld-timeline" style="max-height:380px;overflow-y:auto;"></div>
                                </div>
                            </div>
                            <!-- Add Note -->
                            <div class="col-md-5">
                                <div class="p-3 border rounded-3 h-100" style="background:#fafafa;">
                                    <div style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#006666;border-bottom:1px solid #f0f0f0;padding-bottom:6px;margin-bottom:12px;"><i class="bx bx-plus-circle me-1"></i>Add Note</div>
                                    <form id="addCommentForm">
                                        @csrf
                                        <input type="hidden" name="lead_id" id="c_lead_id">
                                        <div class="mb-3">
                                            <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">Message <span class="text-danger">*</span></label>
                                            <textarea name="msg" id="c_msg" class="form-control" rows="5" placeholder="Write a note about this conversation…" required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">Next Reminder <small class="text-muted">(optional)</small></label>
                                            <input type="datetime-local" name="next_date" id="c_next_date" class="form-control">
                                        </div>
                                        <button type="submit" class="btn w-100 rounded-pill" style="background:#006666;border:none;color:#fff;font-size:0.875rem;">
                                            <i class="bx bx-save me-1"></i>Save Note
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ══ PROPOSALS TAB ══ -->
                    <div class="ld-tab-pane" id="tab-porposal" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold" style="color:#202124;font-size:0.95rem;"><i class="bx bx-file me-1" style="color:#006666;"></i>Proposals</span>
                            <a href="/manage-proposal" class="btn rounded-pill px-3" style="background:#006666;border:none;color:#fff;font-size:0.82rem;">
                                <i class="bx bx-plus me-1"></i>New Proposal
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size:0.82rem;">
                                <thead style="background:#006666 !important;">
                                    <tr>
                                        <th style="color:#fff;font-weight:600;">#ID</th>
                                        <th style="color:#fff;font-weight:600;">Subject</th>
                                        <th style="color:#fff;font-weight:600;">Total</th>
                                        <th style="color:#fff;font-weight:600;">Date</th>
                                        <th style="color:#fff;font-weight:600;">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="Proposals"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ══ ASSIGN TAB ══ -->
                    <div class="ld-tab-pane" id="tab-assign" style="display:none;">
                        <div class="p-3 border rounded-3" style="background:#fafafa;max-width:420px;margin:0 auto;">
                            <div class="text-center mb-3">
                                <div style="width:48px;height:48px;border-radius:12px;background:rgba(0,102,102,0.1);display:inline-flex;align-items:center;justify-content:center;font-size:1.4rem;color:#006666;margin-bottom:8px;"><i class="bx bx-user-plus"></i></div>
                                <h6 class="mb-1" style="font-weight:700;color:#202124;">Assign Salesperson</h6>
                                <p class="text-muted mb-0" style="font-size:0.82rem;">Re-assign this lead to a different salesperson instantly.</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="font-weight:500;font-size:0.875rem;color:#495057;">Select Salesperson</label>
                                <select class="form-select" id="quick_assign_user">
                                    <option value="">— Select Salesperson —</option>
                                    @foreach($getUsers as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" class="btn w-100 rounded-pill" id="quickAssignBtn" style="background:#006666;border:none;color:#fff;font-size:0.875rem;">
                                <i class="bx bx-check-circle me-1"></i>Assign Now
                            </button>
                            <div id="quickAssignMsg" class="mt-2 text-center" style="font-size:0.82rem;"></div>
                        </div>
                    </div>

                </div>{{-- /modal-body --}}
            </div>{{-- /modal-content --}}
                 </div>{{-- /modal-dialog --}}
    </div>{{-- /modal --}}


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // User ID → Name map (from server)
        var userMap = {!! json_encode($getUsers->pluck('name', 'id')) !!};

        // Global tab switcher for the lead modal nav pills
        function ldShowTab(tabId, btn) {
            document.querySelectorAll('.ld-tab-pane').forEach(function (p) { p.style.display = 'none'; });
            document.querySelectorAll('#leadModalTabs .nav-link').forEach(function (b) { b.classList.remove('active'); });
            var pane = document.getElementById(tabId);
            if (pane) pane.style.display = 'block';
            if (btn) btn.classList.add('active');
        }

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
                        d.tags = $('#ajaxTags').val();
                    }
                },

                columns: [
                    {
                        data: 'id', orderable: false, searchable: false,
                        render: function (id) {
                            return '<input type="checkbox" class="lead-cb lead-row-cb" data-id="' + id + '">';
                        }
                    },
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
                    { targets: 0, className: 'text-center', width: '36px' },
                    { targets: 1, className: 'mw150' },
                    { targets: 2, className: 'm-none' },
                    { targets: 3, className: 'm-none mw80' },
                    { targets: 4, className: 'm-none mw60' },
                    { targets: 5, className: 'm-none mw80' },
                    { targets: 6, className: 'm-none mw80' },
                    { targets: 7, className: 'm-none mw60' },
                    { targets: 8, className: 'm-none mw70 tm' },
                    { targets: 9, className: 'm-none mw150' },
                    { targets: 10, className: 'm-none mw60' },
                    { targets: 11, className: 'position-sticky end-0 bg-default mw60' }
                ],

                createdRow: function (row, data, dataIndex) {
                    if (data.row_class) $(row).addClass(data.row_class);
                    $(row).attr('data-id', data.id);
                }
            });

            // ─── Bulk Selection Logic ───────────────────────────────────────
            function getSelectedIds() {
                return $('.lead-row-cb:checked').map(function () {
                    return $(this).data('id');
                }).get();
            }

            function updateBulkBar() {
                var ids = getSelectedIds();
                var bar = $('#bulkActionBar');
                if (ids.length > 0) {
                    $('#bulkSelCount').text(ids.length + ' selected');
                    bar.addClass('show');
                } else {
                    bar.removeClass('show');
                }
            }

            // Select-all header checkbox
            $(document).on('change', '#selectAllLeads', function () {
                var checked = $(this).prop('checked');
                $('.lead-row-cb').prop('checked', checked);
                $('#leadslists tbody tr').toggleClass('selected-row', checked);
                updateBulkBar();
            });

            // Individual row checkboxes
            $(document).on('change', '.lead-row-cb', function () {
                $(this).closest('tr').toggleClass('selected-row', $(this).prop('checked'));
                var total = $('.lead-row-cb').length;
                var checked = $('.lead-row-cb:checked').length;
                $('#selectAllLeads').prop('indeterminate', checked > 0 && checked < total)
                                   .prop('checked', checked === total && total > 0);
                updateBulkBar();
            });

            // Reset on table redraw
            table.on('draw', function () {
                $('#selectAllLeads').prop('checked', false).prop('indeterminate', false);
                updateBulkBar();
            });

            // Clear selection
            $('#bulkClearBtn').on('click', function () {
                $('.lead-row-cb, #selectAllLeads').prop('checked', false).prop('indeterminate', false);
                $('#leadslists tbody tr').removeClass('selected-row');
                updateBulkBar();
            });

            // Bulk Assign
            $('#bulkAssignBtn').on('click', function () {
                var ids = getSelectedIds();
                var salesId = $('#bulkSalesSelect').val();
                if (!ids.length) { return; }
                if (!salesId) { alert('Please select a salesperson first.'); return; }

                $(this).prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Assigning...');

                $.ajax({
                    url: "{{ route('leads.bulkAssign') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        lead_ids: ids,
                        assigned_to: salesId
                    },
                    success: function (res) {
                        // Toast-style feedback
                        var msg = res.message || 'Assigned successfully!';
                        alert(msg);
                        $('.lead-row-cb, #selectAllLeads').prop('checked', false).prop('indeterminate', false);
                        $('#leadslists tbody tr').removeClass('selected-row');
                        updateBulkBar();
                        table.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message || xhr.statusText));
                    },
                    complete: function () {
                        $('#bulkAssignBtn').prop('disabled', false).html('<i class="bx bx-user-check"></i> Assign');
                    }
                });
            });


            // 2. Filters & Refresh
            $('#ajaxSearch').keyup(function () { table.search($(this).val()).draw(); });
            $('#ajaxTags').keyup(function () { table.draw(); });
            $('#ajaxSalesRep, #ajaxStatus').on('change', function () { table.draw(); });
            $('#ajaxStatus, #ajaxSalesRep').change(function () { table.draw(); });
            $('#refreshBtn').click(function () { table.draw(); });



            // 4. Modal Open & Row Click
            $(document).on('click', '#leadslists tbody tr', function (e) {
                // Prevent modal opening when clicking the checkbox or bulk-action elements
                if ($(e.target).closest('input.lead-row-cb, input.lead-cb, a, button, select').length) return;
                var id = $(this).attr('data-id');
                if (!id) return;

                // Always reset to view mode on open
                $('#ld-view-mode').show();
                $('#ld-edit-mode').hide();

                $('#m_id').val(id); $('#c_lead_id').val(id);

                $.get("/get-lead-details/" + id, function (data) {
                    var l = data.lead;
                    var location = {};
                    try { location = JSON.parse(l.location) || {}; } catch(e) {}

                    // ── Header Banner ──
                    var initials = (l.name || 'L').charAt(0).toUpperCase();
                    $('#leadAvatarBadge').text(initials);
                    $('#ld_display_name').text(l.name || '—');
                    $('#ld_display_company').text(l.company || '—');
                    
                    // Added on date
                    var addDate = l.created_at ? new Date(l.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';
                    $('#ld_display_since').text(addDate);

                    var statusLabels = {'0':'Fresh','1':'Follow Up','2':'Qualified','3':'Proposal Sent','5':'Converted','9':'Loss'};
                    var statusColors = {'0':'#5f6368','1':'#f9ab00','2':'#673ab7','3':'#00bcd4','5':'#1e8e3e','9':'#d93025'};
                    var sl = statusLabels[l.status] || 'Fresh';
                    var sc = statusColors[l.status] || '#5f6368';
                    $('#ld_status_chip').text(sl).css({'background': sc, 'color': '#ffffff', 'border-color': sc});

                    $('#ld_btn_call').attr('href', l.mob ? 'tel:+' + l.mob : '#');
                    $('#ld_btn_wa').attr('href', l.whatsapp ? 'https://wa.me/' + l.whatsapp : '#');
                    $('#ld_btn_mail').attr('href', l.email ? 'mailto:' + l.email : '#');

                    // ── View Mode Cards ──
                    $('#v_mob').text(l.mob ? '+' + l.mob : '—');
                    $('#v_whatsapp').text(l.whatsapp ? '+' + l.whatsapp : '—');
                    $('#v_email').text(l.email || '—');
                    $('#v_language').text(l.language || '—');
                    $('#v_company').text(l.company || '—');
                    $('#v_position').text(l.position || '—');
                    $('#v_industry').text(l.industry || '—');
                    $('#v_gstno').text(l.gstno || '—');
                    $('#v_website').html(l.website ? '<a href="'+l.website+'" target="_blank">'+l.website+'</a>' : '—');
                    $('#v_address_full').text([location.address, location.city, location.state, location.zip, location.country].filter(Boolean).join(', ') || '—');
                    $('#v_purpose').text(l.purpose || '—');
                    $('#v_value').text(l.values ? '₹' + Number(l.values).toLocaleString('en-IN') : '—');
                    $('#v_poc').text(l.poc || '—');
                    $('#v_assigned').text(userMap[l.assigned] || l.assigned || '—');
                    $('#v_tags').text(l.tags || '—');

                    // ── Edit Form pre-fill ──
                    $('#m_name').val(l.name);
                    $('#m_email').val(l.email);
                    $('#m_mob').val(l.mob);
                    $('#m_whatsapp').val(l.whatsapp);
                    $('#m_company').val(l.company);
                    $('#m_position').val(l.position);
                    $('#m_industry').val(l.industry);
                    $('#m_gstno').val(l.gstno);
                    $('#m_address').val(location['address'] || '');
                    $('#m_city').val(location['city'] || '');
                    $('#m_state').val(location['state'] || '');
                    $('#m_country').val(location['country'] || '');
                    $('#m_zip').val(location['zip'] || '');
                    $('#m_website').val(l.website);
                    $('#m_language').val(l.language);
                    $('#m_purpose').val(l.purpose);
                    $('#m_value').val(l.values);
                    $('#m_assigned').val(l.assigned); // set dropdown by ID
                    $('#m_poc').val(l.poc);
                    $('#m_status').val(l.status);
                    $('#m_tags').val(l.tags);

                    // Pre-select quick assign tab
                    $('#quick_assign_user').val(l.assigned || '');
                    $('#quickAssignMsg').text('');

                    // ── Conversation Timeline ──
                    var html = '';
                    data.comments.forEach(function (c) {
                        html += '<div class="ld-timeline-item">'
                            + '<div class="ld-tl-dot"></div>'
                            + '<div class="ld-tl-body">'
                            + '<div class="ld-tl-meta">' + (c.next_date ? c.next_date : c.created_at) + '</div>'
                            + '<p class="ld-tl-msg">' + c.msg + '</p>'
                            + '</div></div>';
                    });
                    $('#commentHistory').html(html || '<p class="text-muted text-center p-4" style="font-size:0.82rem">No conversations yet.</p>');

                    var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('leadModal'));
                    modal.show();
                });
            });



            // ── Edit / Cancel Toggle ──
            $(document).on('click', '#ld_edit_toggle', function () {
                $('#ld-view-mode').hide();
                $('#ld-edit-mode').show();
            });
            $(document).on('click', '#ld_edit_cancel', function () {
                $('#ld-edit-mode').hide();
                $('#ld-view-mode').show();
            });

            // ── Quick Assign (Assign Tab) ──
            $(document).on('click', '#quickAssignBtn', function () {
                var leadId = $('#m_id').val();
                var userId = $('#quick_assign_user').val();
                if (!userId) { $('#quickAssignMsg').html('<span class="text-danger">Please select a salesperson.</span>'); return; }

                $(this).prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Assigning...');

                $.ajax({
                    url: "{{ route('leads.bulkAssign') }}",
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', lead_ids: [leadId], assigned_to: userId },
                    success: function (res) {
                        $('#quickAssignMsg').html('<span class="text-success"><i class="bx bx-check"></i> ' + res.message + '</span>');
                        table.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        $('#quickAssignMsg').html('<span class="text-danger">' + (xhr.responseJSON?.message || 'Error') + '</span>');
                    },
                    complete: function () {
                        $('#quickAssignBtn').prop('disabled', false).html('<i class="bx bx-check-circle"></i> Assign Now');
                    }
                });
            });

            // 6. Submit Forms
            $('#editLeadForm').on('submit', function (e) {
                e.preventDefault();
                $.post("{{ route('leads.update') }}", $(this).serialize(), function (res) {
                    alert(res.message || 'Profile Updated');
                    bootstrap.Modal.getInstance(document.getElementById('leadModal')).hide();
                    table.ajax.reload(null, false);
                }).fail(function (xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || xhr.statusText));
                });
            });

            $('#addCommentForm').on('submit', function (e) {
                e.preventDefault();
                $.post("{{ route('leads.storeComment') }}", $(this).serialize(), function () {
                    alert('Comment Saved');
                    bootstrap.Modal.getInstance(document.getElementById('leadModal')).hide();
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
                        bootstrap.Modal.getInstance(document.getElementById('leadModal')).hide();
                        $('#leadslists').DataTable().ajax.reload(null, false);
                    }).fail(function (xhr) {
                        alert('Error: ' + xhr.statusText);
                    });
                }
            });
        });

        // Reset to Profile tab every time the modal opens
        document.getElementById('leadModal').addEventListener('show.bs.modal', function () {
            ldShowTab('tab-profile', document.querySelector('#leadModalTabs .nav-link'));
            var viewMode = document.getElementById('ld-view-mode');
            var editMode = document.getElementById('ld-edit-mode');
            if (viewMode) viewMode.style.display = 'block';
            if (editMode) editMode.style.display = 'none';
        });
    </script>

    {{-- Hidden form for CSV import (required by #importFile button handler) --}}
    <form id="leadsubmit" action="/import-leads-file" method="post" enctype="multipart/form-data">
        @csrf
        <input type="file" name="impLeadFile" id="impLeadFile" accept=".csv, .xls" style="display:none;" />
    </form>

@endsection
