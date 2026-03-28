@extends('layout')
@section('title','Customers - eseCRM')

@section('content')
    @php
    
        $roles = session('roles');
        $roleArray = explode(',',($roles->permissions ?? ''));
    
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/lead-panel.css') }}">

    <section class="task__section">
        @include('inc.header', ['title' => 'Projects'])
        
        <div class="dash-container">
            {{-- Toolbar --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <form action="/projects" method="GET" id="projectFilterForm" class="d-flex align-items-center gap-2">
                        <div class="lb-search-box">
                            <i class="bx bx-search"></i>
                            <input type="text" name="search" id="projectSearch" class="form-control" placeholder="Search projects..." value="{{ $search ?? '' }}">
                        </div>
                    </form>
                </div>
                <div class="leads-toolbar-right">
                    @if(in_array('clients_add', $roleArray) || in_array('All', $roleArray))
                        <a href="/manage-project" class="lb-btn-primary">
                            <i class="bx bx-plus"></i>
                            <span>Add Project</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 py-3 table-responsive">
                    <table id="projectList" class="table table-condensed m-table leads" style="width:100%; border-radius: 8px; overflow: hidden; background: #fff;">
                        <thead>
                            <tr>
                                <th width="10">#</th>
                                <th>Project Name</th>
                                <th>Client / Company</th>
                                <th>Type</th>
                                <th>Total Amount</th>
                                <th width="100">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $project)
                            <tr class="view selectrow pointer-cursor" id="{{ $project->id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="lb-avatar-sm" style="background: var(--teal-gradient);">
                                            {{ substr($project->name, 0, 1) }}
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">{{ $project->name }}</span>
                                            <span class="small text-muted">ID: #PROU-{{ str_pad($project->id, 4, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span>{{ $project->client_name }}</span>
                                        <span class="small text-muted">{{ $project->client_company }}</span>
                                    </div>
                                </td>
                                <td><span class="badge bg-soft-info text-info">{{ $project->type ?? 'General' }}</span></td>
                                <td class="fw-bold text-primary">₹{{ number_format($project->amount, 2) }}</td>
                                <td>
                                    <div class="table-btn">
                                        @if($project->deployment_url)
                                            <a href="{{ $project->deployment_url }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Visit Site">
                                                <i class="bx bx-link-external"></i>
                                            </a>
                                        @endif
                                        <a href="/manage-project?id={{ $project->id }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Project Details Offcanvas -->
    <div class="offcanvas offcanvas-end lb-offcanvas" tabindex="-1" id="projectModal" aria-labelledby="projectModalLabel">
        <div class="offcanvas-body p-0">
            <!-- Premium Header -->
            <div class="lb-offcanvas-header p-4 position-relative overflow-hidden">
                <div class="lb-offcanvas-banner"></div>
                <div class="d-flex align-items-start justify-content-between position-relative z-1 mb-3">
                    <div class="d-flex align-items-center gap-3 mt-4">
                        <div class="lb-avatar-lg shadow-lg border border-3 border-white" id="p-avatar-box">P</div>
                        <div class="text-white">
                            <h4 class="mb-1 text-white fw-bold" id="p-name">Loading...</h4>
                            <p class="mb-0 opacity-75" id="p-client">Loading client...</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                
                <div class="d-flex gap-2 position-relative z-1 mt-4">
                    <a href="#" id="p-action-call" class="lb-btn-light"><i class="bx bx-phone"></i> Call</a>
                    <a href="#" id="p-action-wa" class="lb-btn-light"><i class="bx bxl-whatsapp"></i> WhatsApp</a>
                    <a href="#" id="p-action-url" target="_blank" class="lb-btn-light"><i class="bx bx-link-external"></i> Deployment</a>
                    <div class="ms-auto pt-2">
                        <span class="text-white small opacity-75">Created on: <span id="p-created-at">--</span></span>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="px-4 mt-3">
                <div class="ld-tabs-container">
                    <div class="ld-tab active" onclick="cTab(event, 'p-tab-info')" id="defaultOpen">
                        <i class="bx bx-info-circle"></i> Profile
                    </div>
                    <div class="ld-tab" onclick="cTab(event, 'p-tab-billing')">
                        <i class="bx bx-receipt"></i> Billing
                    </div>
                    <div class="ld-tab" onclick="cTab(event, 'p-tab-license')">
                        <i class="bx bx-key"></i> License
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="p-4 overflow-auto" style="height: calc(100vh - 350px);">
                <!-- Profile Tab -->
                <div id="p-tab-info" class="ld-tab-content">
                    <div class="mb-4">
                        <h6 class="lb-section-title"><i class="bx bx-detail me-2 text-primary"></i>Project Overview</h6>
                        <div class="ld-info-grid">
                            <div class="ld-info-card">
                                <label><i class="bx bx-category"></i> Type</label>
                                <span id="p-type">--</span>
                            </div>
                            <div class="ld-info-card">
                                <label><i class="bx bx-money"></i> Budget</label>
                                <span id="p-budget" class="fw-bold text-primary">₹0.00</span>
                            </div>
                            <div class="ld-info-card col-span-2">
                                <label><i class="bx bx-note"></i> Notes</label>
                                <span id="p-notes">No notes available.</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h6 class="lb-section-title"><i class="bx bx-user me-2 text-primary"></i>Client Information</h6>
                        <div class="ld-info-grid">
                            <div class="ld-info-card">
                                <label><i class="bx bx-envelope"></i> Email</label>
                                <span id="p-client-email">--</span>
                            </div>
                            <div class="ld-info-card">
                                <label><i class="bx bx-phone"></i> Mobile</label>
                                <span id="p-client-mob">--</span>
                            </div>
                            <div class="ld-info-card col-span-2">
                                <label><i class="bx bx-map"></i> Location</label>
                                <span id="p-client-location">--</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billing Tab -->
                <div id="p-tab-billing" class="ld-tab-content" style="display:none;">
                    <h6 class="lb-section-title"><i class="bx bx-history me-2 text-primary"></i>Recovery History</h6>
                    <div class="table-responsive">
                        <table class="table table-sm lb-table-premium">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody id="p-billing-body">
                                <tr><td colspan="4" class="text-center py-4 text-muted">No payments found.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- License Tab -->
                <div id="p-tab-license" class="ld-tab-content" style="display:none;">
                    <h6 class="lb-section-title"><i class="bx bx-shield-quarter me-2 text-primary"></i>License Details</h6>
                    <div id="p-license-area">
                        <div class="alert alert-info border-0 bg-light p-3 rounded-3 mb-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bx bx-info-circle text-info fs-4"></i>
                                <span class="fw-bold">No Active License</span>
                            </div>
                            <p class="small mb-0 opacity-75">There is no active license key associated with this project at the moment.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function cTab(evt, tabName) {
            $(".ld-tab-content").hide();
            $(".ld-tab").removeClass("active");
            $("#" + tabName).show();
            $(evt.currentTarget).addClass("active");
        }

        $(document).ready(function(){
            // Project Search Filter (Debounced)
            let searchTimer;
            $('#projectSearch').on('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    $('#projectFilterForm').submit();
                }, 500);
            });

            const projectModal = new bootstrap.Offcanvas(document.getElementById('projectModal'));

            $('.view').click(function(){
                const id = $(this).attr('id');
                
                // Set default tab
                $(".ld-tab-content").hide();
                $("#p-tab-info").show();
                $(".ld-tab").removeClass("active");
                $(".ld-tab:first-child").addClass("active");

                // Show modal immediately with loading state
                $('#p-name').text('Loading...');
                $('#p-client').text('Fetching project details...');
                projectModal.show();

                $.ajax({
                    url: '/view-single-project',
                    type: 'GET',
                    data: { id: id },
                    success: function(response) {
                        const project = response.project;
                        const recoveries = response.recoveries;
                        const license = response.license;

                        // Header & Actions
                        $('#p-name').text(project.name);
                        $('#p-client').text(project.client_name + ' | ' + project.client_company);
                        $('#p-avatar-box').text(project.name.charAt(0));
                        $('#p-created-at').text(new Date(project.created_at).toLocaleDateString());

                        const waLink = `https://api.whatsapp.com/send/?phone=${project.client_mob}&text=Regarding Project: ${project.name}&type=phone_number&app_absent=0`;
                        $('#p-action-call').attr('href', 'tel:' + project.client_mob);
                        $('#p-action-wa').attr('href', waLink);
                        
                        if (project.deployment_url) {
                            $('#p-action-url').attr('href', project.deployment_url).show();
                        } else {
                            $('#p-action-url').hide();
                        }

                        // Profile Tab
                        $('#p-type').text(project.type || 'General');
                        $('#p-budget').text('₹' + parseFloat(project.amount).toLocaleString('en-IN', {minimumFractionDigits: 2}));
                        $('#p-notes').text(project.note || 'No notes available.');
                        $('#p-client-email').text(project.client_email || '--');
                        $('#p-client-mob').text(project.client_mob || '--');
                        $('#p-client-location').text(project.client_location || 'Not specified');

                        // Billing Tab
                        let billingHtml = '';
                        if (recoveries.length > 0) {
                            recoveries.forEach(rec => {
                                const statusClass = rec.status == '1' ? 'bg-success' : 'bg-warning';
                                const statusText = rec.status == '1' ? 'Paid' : 'Pending';
                                billingHtml += `
                                    <tr>
                                        <td>${new Date(rec.created_at).toLocaleDateString()}</td>
                                        <td class="fw-bold">₹${parseFloat(rec.paid).toLocaleString('en-IN')}</td>
                                        <td><span class="badge ${statusClass}">${statusText}</span></td>
                                        <td class="small">${rec.note || '-'}</td>
                                    </tr>
                                `;
                            });
                        } else {
                            billingHtml = '<tr><td colspan="4" class="text-center py-4 text-muted">No payment history found.</td></tr>';
                        }
                        $('#p-billing-body').html(billingHtml);

                        // License Tab
                        let licenseHtml = '';
                        if (license) {
                            licenseHtml = `
                                <div class="lb-info-card border-primary border-start border-4 mb-3">
                                    <label><i class="bx bx-key"></i> License Key</label>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <code class="fs-5 text-primary fw-bold">${license.eselicense_key}</code>
                                        <button class="btn btn-sm btn-link" onclick="navigator.clipboard.writeText('${license.eselicense_key}')"><i class="bx bx-copy"></i></button>
                                    </div>
                                </div>
                                <div class="ld-info-grid">
                                    <div class="ld-info-card">
                                        <label><i class="bx bx-code-alt"></i> Tech Stack</label>
                                        <span>${license.technology_stack || 'N/A'}</span>
                                    </div>
                                    <div class="ld-info-card">
                                        <label><i class="bx bx-calendar-event"></i> Expiry Date</label>
                                        <span class="${new Date(license.expiry_date) < new Date() ? 'text-danger fw-bold' : ''}">
                                            ${new Date(license.expiry_date).toLocaleDateString()}
                                        </span>
                                    </div>
                                </div>
                            `;
                        } else {
                            licenseHtml = `
                                <div class="alert alert-info border-0 bg-light p-3 rounded-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bx bx-info-circle text-info fs-4"></i>
                                        <span class="fw-bold">No Active License</span>
                                    </div>
                                    <p class="small mb-0 opacity-75">There is no active license key associated with this project at the moment.</p>
                                </div>
                            `;
                        }
                        $('#p-license-area').html(licenseHtml);

                    },
                    error: function(xhr) {
                        console.error('Error fetching project details:', xhr);
                        $('#p-name').text('Error');
                        $('#p-client').text('Could not load project details.');
                    }
                });
            });
        });
    </script>

@endsection