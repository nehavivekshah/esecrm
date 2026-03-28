@extends('layout')
@section('title', 'Project Details - eseCRM')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/lead-panel.css') }}">

<section class="task__section">
    @include('inc.header', ['title' => 'Project Details'])

    <div class="dash-container px-4 py-3">
        
        <!-- Premium Header Area -->
        <div class="lb-offcanvas-header p-4 position-relative overflow-hidden rounded-4 mb-4 shadow-sm" style="background: var(--dark-navy); color: white;">
            <div class="lb-offcanvas-banner" style="position: absolute; top:0; left:0; right:0; height:100px; background: var(--teal-gradient); opacity:0.8;"></div>
            
            <div class="d-flex align-items-center justify-content-between position-relative z-1 mb-3 pt-4 border-bottom border-secondary pb-3">
                <div class="d-flex align-items-center gap-4">
                    <div class="lb-avatar-lg shadow-lg border border-3 border-white d-flex align-items-center justify-content-center fw-bold fs-2" style="width: 80px; height: 80px; border-radius: 50%; background: var(--teal-gradient); color: white;">
                        {{ substr($project->name, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="mb-1 text-white fw-bold">{{ $project->name }}</h2>
                        <h6 class="mb-0 text-light opacity-75">
                            <i class='bx bx-building'></i> {{ $project->client_name }} 
                            @if($project->client_company) | {{ $project->client_company }} @endif
                        </h6>
                    </div>
                </div>
                <div>
                     <a href="{{ url('/projects') }}" class="btn btn-outline-light d-flex align-items-center gap-1"><i class="bx bx-arrow-back"></i> Back to Projects</a>
                </div>
            </div>
            
            <div class="d-flex gap-2 position-relative z-1 mt-3">
                @if($project->client_mob)
                    <a href="tel:{{ $project->client_mob }}" class="lb-btn-light text-decoration-none d-flex align-items-center gap-1"><i class="bx bx-phone"></i> Call</a>
                    
                    @php 
                       $waNumber = $project->client_whatsapp ?? $project->client_mob; 
                    @endphp
                    <a href="https://api.whatsapp.com/send/?phone={{ $waNumber }}&text=Regarding Project: {{ urlencode($project->name) }}" target="_blank" class="lb-btn-light text-decoration-none d-flex align-items-center gap-1"><i class="bx bxl-whatsapp"></i> WhatsApp</a>
                @endif
                
                @if($project->deployment_url)
                    <a href="{{ $project->deployment_url }}" target="_blank" class="lb-btn-light text-decoration-none d-flex align-items-center gap-1"><i class="bx bx-link-external"></i> Deployment</a>
                @endif
                
                <div class="ms-auto pt-2">
                    <span class="text-white small opacity-75"><i class='bx bx-calendar'></i> Created on: {{ \Carbon\Carbon::parse($project->created_at)->format('d M, Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs ld-tabs-container border-0 mb-4" id="projectTab" role="tablist" style="background: transparent; box-shadow: none; padding: 0;">
            <li class="nav-item" role="presentation">
                <button class="nav-link ld-tab active px-4 py-3 border-0 fw-semibold fs-6" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true" style="border-radius: 8px 8px 0 0; margin-right: 5px;">
                    <i class="bx bx-info-circle fs-5 me-1 align-bottom"></i> Project Overview
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link ld-tab px-4 py-3 border-0 fw-semibold fs-6" id="billing-tab" data-bs-toggle="tab" data-bs-target="#billing" type="button" role="tab" aria-controls="billing" aria-selected="false" style="border-radius: 8px 8px 0 0; margin-right: 5px;">
                    <i class="bx bx-receipt fs-5 me-1 align-bottom"></i> Billing & Recoveries
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link ld-tab px-4 py-3 border-0 fw-semibold fs-6" id="license-tab" data-bs-toggle="tab" data-bs-target="#license" type="button" role="tab" aria-controls="license" aria-selected="false" style="border-radius: 8px 8px 0 0; margin-right: 5px;">
                    <i class="bx bx-key fs-5 me-1 align-bottom"></i> License Details
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link ld-tab px-4 py-3 border-0 fw-semibold fs-6" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button" role="tab" aria-controls="invoices" aria-selected="false" style="border-radius: 8px 8px 0 0; margin-right: 5px;">
                    <i class="bx bx-file fs-5 me-1 align-bottom"></i> Client Invoices
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link ld-tab px-4 py-3 border-0 fw-semibold fs-6" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks" type="button" role="tab" aria-controls="tasks" aria-selected="false" style="border-radius: 8px 8px 0 0; margin-right: 5px;">
                    <i class="bx bx-task fs-5 me-1 align-bottom"></i> Tasks
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link ld-tab px-4 py-3 border-0 fw-semibold fs-6" id="proposals-tab" data-bs-toggle="tab" data-bs-target="#proposals" type="button" role="tab" aria-controls="proposals" aria-selected="false" style="border-radius: 8px 8px 0 0;">
                    <i class="bx bx-paper-plane fs-5 me-1 align-bottom"></i> Proposals
                </button>
            </li>
        </ul>

        <!-- Tab Content Zones -->
        <div class="tab-content bg-white p-4 rounded-4 shadow-sm border border-light" id="projectTabContent" style="min-height: 400px;">
            
            <!-- OVERVIEW TAB -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                <div class="row g-4">
                    <div class="col-md-7">
                        <h5 class="lb-section-title mb-4"><i class="bx bx-detail me-2 text-primary"></i>Project Specifications</h5>
                        <div class="ld-info-grid">
                            <div class="ld-info-card">
                                <label><i class="bx bx-category"></i> Type</label>
                                <span>{{ $project->type ?: 'General' }}</span>
                            </div>
                            <div class="ld-info-card">
                                <label><i class="bx bx-money"></i> Budget</label>
                                <span class="fw-bold text-primary fs-5">₹{{ number_format($project->amount, 2) }}</span>
                            </div>
                            <div class="ld-info-card" style="grid-column: span 2;">
                                <label><i class="bx bx-note"></i> Notes</label>
                                <span>{{ $project->note ?: 'No notes available.' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-5">
                       <h5 class="lb-section-title mb-4"><i class="bx bx-user me-2 text-primary"></i>Client Information</h5>
                        <div class="ld-info-grid" style="grid-template-columns: 1fr;">
                            <div class="ld-info-card">
                                <label><i class="bx bx-envelope"></i> Email</label>
                                <span>{{ $project->client_email ?: '--' }}</span>
                            </div>
                            <div class="ld-info-card">
                                <label><i class="bx bx-phone"></i> Mobile</label>
                                <span>{{ $project->client_mob ?: '--' }}</span>
                            </div>
                            <div class="ld-info-card">
                                <label><i class="bx bx-map"></i> Location</label>
                                <span>{{ $project->client_location ?: 'Not specified' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BILLING TAB (Recoveries) -->
            <div class="tab-pane fade" id="billing" role="tabpanel" aria-labelledby="billing-tab">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="lb-section-title mb-0"><i class="bx bx-history me-2 text-primary"></i>Recovery History</h5>
                    <a href="/manage-recovery?id={{$project->id}}" class="btn btn-sm btn-primary"><i class="bx bx-plus"></i> Add Recovery</a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover lb-table-premium align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date Received</th>
                                <th>Amount Paid</th>
                                <th>Status</th>
                                <th>Note / Ref</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recoveries as $rec)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($rec->created_at)->format('d M, Y h:i A') }}</td>
                                    <td class="fw-bold fs-6">₹{{ number_format($rec->paid, 2) }}</td>
                                    <td>
                                        @if($rec->status == '1')
                                            <span class="badge bg-success px-3 py-2 rounded-pill">Paid</span>
                                        @else
                                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $rec->note ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <div class="fs-1 mb-2"><i class="bx bx-receipt fs-1 text-light"></i></div>
                                        <p class="mb-0">No payment recovery history found for this project.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- LICENSE TAB -->
            <div class="tab-pane fade" id="license" role="tabpanel" aria-labelledby="license-tab">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="lb-section-title mb-0"><i class="bx bx-shield-quarter me-2 text-primary"></i>License Details</h5>
                    @if($license)
                       <a href="/manage-license?id={{$license->id}}" class="btn btn-sm btn-outline-primary"><i class="bx bx-edit"></i> Edit License</a>
                    @else
                       <a href="/manage-license" class="btn btn-sm btn-primary"><i class="bx bx-plus"></i> Add License</a>
                    @endif
                </div>

                @if($license)
                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class="lb-info-card border-primary mb-0 bg-light p-4 rounded-3 shadow-sm border-start" style="border-left-width: 5px !important;">
                                <label class="text-uppercase small fw-bold text-muted mb-2 d-block"><i class="bx bx-key"></i> License Key</label>
                                <div class="d-flex align-items-center justify-content-between bg-white p-3 rounded border">
                                    <code class="fs-4 text-primary fw-bold" id="lic-key">{{ $license->eselicense_key }}</code>
                                    <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" onclick="navigator.clipboard.writeText('{{ $license->eselicense_key }}')">
                                        <i class="bx bx-copy"></i> Copy Key
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="ld-info-card bg-light h-100 p-4 rounded border">
                                <label class="text-muted mb-2 d-block"><i class="bx bx-code-alt"></i> Technology Stack</label>
                                <span class="fs-5 fw-medium">{{ $license->technology_stack ?: 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="ld-info-card bg-light h-100 p-4 rounded border">
                                <label class="text-muted mb-2 d-block"><i class="bx bx-calendar-event"></i> Expiry Date</label>
                                @php $isExpired = \Carbon\Carbon::parse($license->expiry_date)->isPast(); @endphp
                                <span class="fs-5 fw-bold {{ $isExpired ? 'text-danger' : 'text-success' }}">
                                    {{ \Carbon\Carbon::parse($license->expiry_date)->format('d F, Y') }}
                                    @if($isExpired)
                                        <span class="badge bg-danger ms-2 rounded-pill fs-6"><i class="bx bx-error"></i> Expired</span>
                                    @else
                                        <span class="badge bg-success ms-2 rounded-pill fs-6">Active</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info border-0 bg-light p-5 rounded-4 text-center">
                        <i class="bx bx-info-circle text-info fs-1 mb-3"></i>
                        <h4 class="fw-bold">No Active License</h4>
                        <p class="mb-0 text-muted">There is no software license key associated with this project at the moment.</p>
                    </div>
                @endif
            </div>

            <!-- INVOICES TAB -->
            <div class="tab-pane fade" id="invoices" role="tabpanel" aria-labelledby="invoices-tab">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="lb-section-title mb-0"><i class="bx bx-file me-2 text-primary"></i>Client Invoices</h5>
                    <a href="/manage-invoice" class="btn btn-sm btn-primary"><i class="bx bx-plus"></i> Create Invoice</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover lb-table-premium align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $inv)
                                <tr>
                                    <td class="fw-bold text-primary">{{ $inv->invoice_number }}</td>
                                    <td>{{ \Carbon\Carbon::parse($inv->date)->format('d M, Y') }}</td>
                                    <td>
                                        <span class="{{ \Carbon\Carbon::parse($inv->due_date)->isPast() && $inv->status != 'paid' ? 'text-danger fw-bold' : '' }}">
                                        {{ \Carbon\Carbon::parse($inv->due_date)->format('d M, Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($inv->status == 'paid')
                                            <span class="badge bg-success px-2 py-1 text-white">Paid</span>
                                        @elseif($inv->status == 'partial')
                                            <span class="badge bg-info px-2 py-1 text-white">Partial</span>
                                        @else
                                            <span class="badge bg-danger px-2 py-1 text-white">Unpaid</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('invoicePreview', $inv->id) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="bx bx-show"></i></a>
                                        <a href="{{ route('invoicePdfPreview', $inv->id) }}" class="btn btn-sm btn-outline-danger" title="PDF"><i class="bx bxs-file-pdf"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <div class="fs-1 mb-2"><i class="bx bx-receipt fs-1 text-light"></i></div>
                                        <p class="mb-0">No invoices found for this client.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TASKS TAB -->
            <div class="tab-pane fade" id="tasks" role="tabpanel" aria-labelledby="tasks-tab">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="lb-section-title mb-0"><i class="bx bx-task me-2 text-primary"></i>Project Tasks</h5>
                    <a href="/crm-tasks" class="btn btn-sm btn-primary"><i class="bx bx-plus"></i> Go to Tasks</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover lb-table-premium align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Task Name</th>
                                <th>Type</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tasks as $t)
                                <tr>
                                    <td class="fw-bold text-primary">{{ $t->name }}</td>
                                    <td><span class="badge bg-secondary">{{ $t->type }}</span></td>
                                    <td>
                                        <span class="{{ \Carbon\Carbon::parse($t->due_date)->isPast() && $t->status != 'Completed' ? 'text-danger fw-bold' : '' }}">
                                        {{ \Carbon\Carbon::parse($t->due_date)->format('d M, Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($t->status == 'Completed')
                                            <span class="badge bg-success px-2 py-1 text-white">Completed</span>
                                        @else
                                            <span class="badge bg-warning px-2 py-1 text-dark">{{ $t->status }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <div class="fs-1 mb-2"><i class="bx bx-task fs-1 text-light"></i></div>
                                        <p class="mb-0">No tasks assigned to this project.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PROPOSALS TAB -->
            <div class="tab-pane fade" id="proposals" role="tabpanel" aria-labelledby="proposals-tab">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="lb-section-title mb-0"><i class="bx bx-paper-plane me-2 text-primary"></i>Client Proposals</h5>
                    <a href="/manage-proposal" class="btn btn-sm btn-primary"><i class="bx bx-plus"></i> Create Proposal</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover lb-table-premium align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Subject</th>
                                <th>Date Sent</th>
                                <th>Open Till</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proposals as $prop)
                                <tr>
                                    <td class="fw-bold text-primary">{{ $prop->subject }}</td>
                                    <td>{{ \Carbon\Carbon::parse($prop->proposal_date)->format('d M, Y') }}</td>
                                    <td>
                                        <span class="{{ \Carbon\Carbon::parse($prop->open_till)->isPast() && !in_array($prop->status, ['Accepted', 'Declined']) ? 'text-danger fw-bold' : '' }}">
                                        {{ \Carbon\Carbon::parse($prop->open_till)->format('d M, Y') }}
                                        </span>
                                    </td>
                                    <td class="fw-bold">₹{{ number_format($prop->grand_total, 2) }}</td>
                                    <td>
                                        @if($prop->status == 'Accepted')
                                            <span class="badge bg-success px-2 py-1 text-white">Accepted</span>
                                        @elseif($prop->status == 'Declined')
                                            <span class="badge bg-danger px-2 py-1 text-white">Declined</span>
                                        @else
                                            <span class="badge bg-info px-2 py-1 text-white">{{ $prop->status ?: 'Sent' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <div class="fs-1 mb-2"><i class="bx bx-paper-plane fs-1 text-light"></i></div>
                                        <p class="mb-0">No proposals found for this client.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</section>

<style>
/* Sub-navigation Tabs Styling Override for Project View */
.ld-tabs-container .nav-link {
    color: var(--dark-navy) !important;
    background: transparent !important;
    border-bottom: 3px solid transparent !important;
    transition: all 0.3s ease;
}
.ld-tabs-container .nav-link:hover {
    color: var(--primary) !important;
    background: #f8f9fa !important;
}
.ld-tabs-container .nav-link.active {
    color: var(--primary) !important;
    background: white !important;
    border-bottom: 3px solid var(--primary) !important;
}
.lb-table-premium th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    color: #6c757d;
}
</style>
@endsection
