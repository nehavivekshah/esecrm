@extends('layout')
@section('title', 'Project Details - eseCRM')

@section('content')
@php
    $totalPaid      = $recoveries->sum('paid');
    $totalAmount    = $project->amount ?? 0;
    $pendingAmount  = max(0, $totalAmount - $totalPaid);
    $recoveryPct    = $totalAmount > 0 ? min(100, round(($totalPaid / $totalAmount) * 100)) : 0;
    $pctColor       = $recoveryPct >= 80 ? '#34a853' : ($recoveryPct >= 40 ? '#fbbc04' : '#ea4335');

    $paidInvoices   = $invoices->where('status', 'paid')->count();
    $unpaidInvoices = $invoices->where('status', '!=', 'paid')->count();
    $pendingTasks   = $tasks->where('status', '!=', 'Completed')->count();
@endphp

<section class="task__section">
    @include('inc.header', ['title' => 'Project Details'])

    <div class="dash-container">

        {{-- ══ Hero Banner ══ --}}
        <div class="pv-hero mb-4">
            <div class="pv-hero-body">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="pv-hero-avatar">{{ strtoupper(substr($project->name, 0, 1)) }}</div>
                    <div class="pv-hero-info">
                        <h1 class="pv-hero-title">{{ $project->name }}</h1>
                        <div class="pv-hero-meta">
                            <span><i class="bx bx-building"></i> {{ $project->client_name }}</span>
                            @if($project->client_company)
                                <span class="pv-sep">·</span>
                                <span>{{ $project->client_company }}</span>
                            @endif
                            <span class="pv-sep">·</span>
                            <span><i class="bx bx-calendar"></i> {{ \Carbon\Carbon::parse($project->created_at)->format('d M, Y') }}</span>
                            <span class="pv-sep">·</span>
                            <span class="pv-id">#PROU-{{ str_pad($project->id, 4, '0', STR_PAD_LEFT) }}</span>
                        </div>
                    </div>
                </div>

                <div class="pv-hero-actions">
                    @if($project->client_mob)
                        <a href="tel:{{ $project->client_mob }}" class="pv-action-btn" title="Call">
                            <i class="bx bx-phone"></i>
                        </a>
                    @endif
                    @if($project->client_whatsapp || $project->client_mob)
                        @php $waNum = $project->client_whatsapp ?? $project->client_mob; @endphp
                        <a href="https://api.whatsapp.com/send/?phone={{ $waNum }}&text=Regarding Project: {{ urlencode($project->name) }}"
                           target="_blank" class="pv-action-btn pv-wa" title="WhatsApp">
                            <i class="bx bxl-whatsapp"></i>
                        </a>
                    @endif
                    @if($project->client_email)
                        <a href="mailto:{{ $project->client_email }}" class="pv-action-btn" title="Email">
                            <i class="bx bx-envelope"></i>
                        </a>
                    @endif
                    @if($project->deployment_url)
                        <a href="{{ $project->deployment_url }}" target="_blank" class="pv-action-btn" title="Live Site">
                            <i class="bx bx-globe"></i>
                        </a>
                    @endif
                    <a href="/manage-project?id={{ $project->id }}" class="pv-edit-btn">
                        <i class="bx bx-edit"></i> Edit Project
                    </a>
                    <a href="{{ url('/projects') }}" class="pv-back-btn">
                        <i class="bx bx-arrow-back"></i> Back
                    </a>
                </div>
            </div>

            {{-- Recovery progress strip --}}
            <div class="pv-progress-strip">
                <div class="pv-progress-fill" style="width:{{ $recoveryPct }}%; background:{{ $pctColor }};"></div>
            </div>
        </div>

        {{-- ══ KPI Cards ══ --}}
        <div class="pv-kpi-row mb-4">
            <div class="pv-kpi">
                <div class="pv-kpi-label">Contract Value</div>
                <div class="pv-kpi-val">₹{{ number_format($totalAmount, 0) }}</div>
            </div>
            <div class="pv-kpi" style="border-color:#34a85340;">
                <div class="pv-kpi-label" style="color:#34a853;">Recovered</div>
                <div class="pv-kpi-val" style="color:#34a853;">₹{{ number_format($totalPaid, 0) }}</div>
            </div>
            <div class="pv-kpi" style="border-color:#ea433540;">
                <div class="pv-kpi-label" style="color:#ea4335;">Pending</div>
                <div class="pv-kpi-val" style="color:#ea4335;">₹{{ number_format($pendingAmount, 0) }}</div>
            </div>
            <div class="pv-kpi">
                <div class="pv-kpi-label">Recovery Rate</div>
                <div class="pv-kpi-val" style="color:{{ $pctColor }};">{{ $recoveryPct }}%</div>
                <div class="pv-kpi-sub">
                    <div style="height:4px;background:#f0f0f0;border-radius:99px;margin-top:6px;overflow:hidden;">
                        <div style="width:{{ $recoveryPct }}%;height:100%;background:{{ $pctColor }};border-radius:99px;"></div>
                    </div>
                </div>
            </div>
            <div class="pv-kpi">
                <div class="pv-kpi-label">Invoices</div>
                <div class="pv-kpi-val">{{ $invoices->count() }}</div>
                <div class="pv-kpi-sub">{{ $paidInvoices }} paid · {{ $unpaidInvoices }} unpaid</div>
            </div>
            <div class="pv-kpi">
                <div class="pv-kpi-label">Tasks</div>
                <div class="pv-kpi-val">{{ $tasks->count() }}</div>
                <div class="pv-kpi-sub">{{ $pendingTasks }} pending</div>
            </div>
        </div>

        {{-- ══ Tabs ══ --}}
        <div class="ld-tab-nav mb-3" role="tablist">
            <button class="ld-tab active" data-bs-toggle="tab" data-bs-target="#overview" type="button">
                <i class="bx bx-info-circle"></i> Overview
            </button>
            <button class="ld-tab" data-bs-toggle="tab" data-bs-target="#billing" type="button">
                <i class="bx bx-receipt"></i> Billing
                @if($recoveries->count())
                    <span class="pv-tab-badge">{{ $recoveries->count() }}</span>
                @endif
            </button>
            <button class="ld-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button">
                <i class="bx bx-file"></i> Invoices
                @if($invoices->count())
                    <span class="pv-tab-badge">{{ $invoices->count() }}</span>
                @endif
            </button>
            <button class="ld-tab" data-bs-toggle="tab" data-bs-target="#tasks" type="button">
                <i class="bx bx-task"></i> Tasks
                @if($pendingTasks)
                    <span class="pv-tab-badge" style="background:#ea4335;">{{ $pendingTasks }}</span>
                @endif
            </button>
            <button class="ld-tab" data-bs-toggle="tab" data-bs-target="#license" type="button">
                <i class="bx bx-key"></i> License
            </button>
            <button class="ld-tab" data-bs-toggle="tab" data-bs-target="#proposals" type="button">
                <i class="bx bx-paper-plane"></i> Proposals
                @if($proposals->count())
                    <span class="pv-tab-badge">{{ $proposals->count() }}</span>
                @endif
            </button>
        </div>

        {{-- ══ Tab Content ══ --}}
        <div class="tab-content pv-tab-body" id="pvTabContent">

            {{-- ─ OVERVIEW ─ --}}
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="row g-4">
                    {{-- Project Details --}}
                    <div class="col-md-7">
                        <div class="pv-section-card">
                            <div class="pv-section-head">
                                <i class="bx bx-detail"></i> Project Specifications
                            </div>
                            <div class="pv-info-grid">
                                <div class="pv-info-item">
                                    <div class="pv-info-label"><i class="bx bx-category"></i> Type</div>
                                    <div class="pv-info-val">
                                        <span class="pj-type-pill">{{ $project->type ?: 'General' }}</span>
                                    </div>
                                </div>
                                <div class="pv-info-item">
                                    <div class="pv-info-label"><i class="bx bx-money"></i> Budget</div>
                                    <div class="pv-info-val fw-bold" style="color:#006666;font-size:1.05rem;">
                                        ₹{{ number_format($totalAmount, 2) }}
                                    </div>
                                </div>
                                @if($project->deployment_url)
                                <div class="pv-info-item" style="grid-column:span 2;">
                                    <div class="pv-info-label"><i class="bx bx-globe"></i> Deployment URL</div>
                                    <div class="pv-info-val">
                                        <a href="{{ $project->deployment_url }}" target="_blank"
                                           class="pv-link">{{ $project->deployment_url }}</a>
                                    </div>
                                </div>
                                @endif
                                @if($project->note)
                                <div class="pv-info-item" style="grid-column:span 2;">
                                    <div class="pv-info-label"><i class="bx bx-note"></i> Notes</div>
                                    <div class="pv-info-val text-muted">{{ $project->note }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Client Info --}}
                    <div class="col-md-5">
                        <div class="pv-section-card">
                            <div class="pv-section-head">
                                <i class="bx bx-user"></i> Client Information
                            </div>
                            <div class="pv-client-block">
                                <div class="pv-client-avatar">{{ strtoupper(substr($project->client_name ?? '?', 0, 1)) }}</div>
                                <div>
                                    <div class="pv-client-name">{{ $project->client_name }}</div>
                                    @if($project->client_company)
                                        <div class="pv-client-company">{{ $project->client_company }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="pv-contact-list">
                                @if($project->client_email)
                                <a href="mailto:{{ $project->client_email }}" class="pv-contact-row">
                                    <span class="pv-contact-icon"><i class="bx bx-envelope"></i></span>
                                    <span>{{ $project->client_email }}</span>
                                </a>
                                @endif
                                @if($project->client_mob)
                                <a href="tel:{{ $project->client_mob }}" class="pv-contact-row">
                                    <span class="pv-contact-icon"><i class="bx bx-phone"></i></span>
                                    <span>{{ $project->client_mob }}</span>
                                </a>
                                @endif
                                @if($project->client_location)
                                <div class="pv-contact-row">
                                    <span class="pv-contact-icon"><i class="bx bx-map"></i></span>
                                    <span>{{ $project->client_location }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Recovery Timeline --}}
                    @if($recoveries->count())
                    <div class="col-12">
                        <div class="pv-section-card">
                            <div class="pv-section-head d-flex justify-content-between">
                                <span><i class="bx bx-history"></i> Recent Recoveries</span>
                                <a href="#" class="pv-see-all" data-bs-toggle="tab" data-bs-target="#billing">
                                    View All <i class="bx bx-chevron-right"></i>
                                </a>
                            </div>
                            <div class="pv-timeline">
                                @foreach($recoveries->take(3) as $rec)
                                <div class="pv-timeline-item">
                                    <div class="pv-tl-dot {{ $rec->status == '1' ? 'pv-tl-paid' : 'pv-tl-pending' }}"></div>
                                    <div class="pv-tl-body">
                                        <div class="pv-tl-title">
                                            ₹{{ number_format($rec->paid, 2) }}
                                            <span class="pv-badge {{ $rec->status == '1' ? 'pv-badge-success' : 'pv-badge-warn' }}">
                                                {{ $rec->status == '1' ? 'Paid' : 'Pending' }}
                                            </span>
                                        </div>
                                        <div class="pv-tl-sub">
                                            {{ \Carbon\Carbon::parse($rec->created_at)->format('d M, Y') }}
                                            @if($rec->note) · {{ $rec->note }} @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ─ BILLING / RECOVERIES ─ --}}
            <div class="tab-pane fade" id="billing" role="tabpanel">
                <div class="pv-tab-toolbar">
                    <h2 class="pv-tab-title"><i class="bx bx-receipt"></i> Recovery History</h2>
                    <a href="/manage-recovery?id={{ $project->id }}" class="pv-add-btn">
                        <i class="bx bx-plus"></i> Add Recovery
                    </a>
                </div>

                {{-- Summary strip --}}
                <div class="pv-billing-summary mb-3">
                    <div class="pv-bs-item">
                        <span>Total</span>
                        <strong>₹{{ number_format($totalAmount, 2) }}</strong>
                    </div>
                    <div class="pv-bs-sep"></div>
                    <div class="pv-bs-item" style="color:#34a853;">
                        <span>Recovered</span>
                        <strong>₹{{ number_format($totalPaid, 2) }}</strong>
                    </div>
                    <div class="pv-bs-sep"></div>
                    <div class="pv-bs-item" style="color:#ea4335;">
                        <span>Pending</span>
                        <strong>₹{{ number_format($pendingAmount, 2) }}</strong>
                    </div>
                    <div class="pv-bs-sep"></div>
                    <div class="pv-bs-item" style="color:{{ $pctColor }};">
                        <span>Progress</span>
                        <strong>{{ $recoveryPct }}%</strong>
                    </div>
                </div>

                @forelse($recoveries as $rec)
                <div class="pv-rec-card">
                    <div class="pv-rec-icon {{ $rec->status == '1' ? 'pv-rec-paid' : 'pv-rec-pend' }}">
                        <i class="bx {{ $rec->status == '1' ? 'bx-check-circle' : 'bx-time' }}"></i>
                    </div>
                    <div class="pv-rec-body">
                        <div class="pv-rec-amount">₹{{ number_format($rec->paid, 2) }}</div>
                        <div class="pv-rec-meta">
                            {{ \Carbon\Carbon::parse($rec->created_at)->format('d M, Y · h:i A') }}
                            @if($rec->note) <span class="pv-sep">·</span> {{ $rec->note }} @endif
                        </div>
                    </div>
                    <span class="pv-badge {{ $rec->status == '1' ? 'pv-badge-success' : 'pv-badge-warn' }}">
                        {{ $rec->status == '1' ? 'Paid' : 'Pending' }}
                    </span>
                </div>
                @empty
                <div class="pv-empty-state">
                    <i class="bx bx-receipt"></i>
                    <p>No recovery records yet.</p>
                    <a href="/manage-recovery?id={{ $project->id }}" class="pv-add-btn">Add First Recovery</a>
                </div>
                @endforelse
            </div>

            {{-- ─ INVOICES ─ --}}
            <div class="tab-pane fade" id="invoices" role="tabpanel">
                <div class="pv-tab-toolbar">
                    <h2 class="pv-tab-title"><i class="bx bx-file"></i> Client Invoices</h2>
                    <a href="/manage-invoice" class="pv-add-btn"><i class="bx bx-plus"></i> Create Invoice</a>
                </div>
                <div class="table-responsive">
                    <table class="leads-table">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $inv)
                            @php
                                $overdue = \Carbon\Carbon::parse($inv->due_date)->isPast() && $inv->status != 'paid';
                            @endphp
                            <tr>
                                <td><span class="fw-bold" style="color:#006666;">{{ $inv->invoice_number }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($inv->date)->format('d M, Y') }}</td>
                                <td>
                                    <span class="{{ $overdue ? 'text-danger fw-bold' : '' }}">
                                        {{ \Carbon\Carbon::parse($inv->due_date)->format('d M, Y') }}
                                        @if($overdue) <i class="bx bx-error-circle ms-1"></i> @endif
                                    </span>
                                </td>
                                <td>
                                    @if($inv->status == 'paid')
                                        <span class="pv-badge pv-badge-success">Paid</span>
                                    @elseif($inv->status == 'partial')
                                        <span class="pv-badge pv-badge-info">Partial</span>
                                    @else
                                        <span class="pv-badge pv-badge-danger">Unpaid</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('invoicePreview', $inv->id) }}" class="kb-action-btn" title="View">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('invoicePdfPreview', $inv->id) }}" class="kb-action-btn" title="PDF" style="color:#ea4335;">
                                            <i class="bx bxs-file-pdf"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5">
                                    <div class="pv-empty-state">
                                        <i class="bx bx-file"></i>
                                        <p>No invoices found.</p>
                                        <a href="/manage-invoice" class="pv-add-btn">Create Invoice</a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ─ TASKS ─ --}}
            <div class="tab-pane fade" id="tasks" role="tabpanel">
                <div class="pv-tab-toolbar">
                    <h2 class="pv-tab-title"><i class="bx bx-task"></i> Project Tasks</h2>
                    <a href="/crm-tasks?rel_type=Project&rel_id={{ $project->id }}" class="pv-add-btn"><i class="bx bx-plus"></i> Manage Tasks</a>
                </div>
                @forelse($tasks as $t)
                @php
                    $isDone = ($t->status == 'Completed');
                    $taskOverdue = \Carbon\Carbon::parse($t->due_date)->isPast() && !$isDone;
                @endphp
                <div class="pv-rec-card task-item-row" id="task-row-{{ $t->id }}">
                    <div class="pv-rec-icon {{ $isDone ? 'pv-rec-paid' : ($taskOverdue ? 'pv-rec-danger' : 'pv-rec-pend') }}" id="task-icon-{{ $t->id }}">
                        <label class="pvt-check-wrap" title="{{ $isDone ? 'Mark pending' : 'Mark done' }}">
                            <input type="checkbox" class="task-status-check visually-hidden" 
                                   data-id="{{ $t->id }}" {{ $isDone ? 'checked' : '' }}>
                            <span class="pvt-check-circle {{ $isDone ? 'pvt-checked' : '' }}">
                                <i class="bx bx-check"></i>
                            </span>
                        </label>
                    </div>
                    <div class="pv-rec-body">
                        <div class="pv-rec-amount task-name {{ $isDone ? 'pvt-name-done' : '' }}" id="task-name-{{ $t->id }}" style="font-size:0.9rem;">{{ $t->name }}</div>
                        <div class="pv-rec-meta">
                            <span class="pv-badge pv-badge-info me-1">{{ $t->type }}</span>
                            Due: <span class="task-due-date {{ $taskOverdue ? 'text-danger fw-bold' : '' }}">{{ \Carbon\Carbon::parse($t->due_date)->format('d M, Y') }}</span>
                        </div>
                    </div>
                    <div class="task-status-badge-container">
                        @if($isDone)
                            <span class="pv-badge pv-badge-success">Done</span>
                        @elseif($taskOverdue)
                            <span class="pv-badge pv-badge-danger">Overdue</span>
                        @else
                            <span class="pv-badge pv-badge-warn">{{ $t->status }}</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="pv-empty-state">
                    <i class="bx bx-task"></i>
                    <p>No tasks assigned to this project.</p>
                </div>
                @endforelse
            </div>

            {{-- ─ LICENSE ─ --}}
            <div class="tab-pane fade" id="license" role="tabpanel">
                <div class="pv-tab-toolbar">
                    <h2 class="pv-tab-title"><i class="bx bx-key"></i> License Details</h2>
                    @if($license)
                        <a href="/manage-license?id={{ $license->id }}" class="pv-add-btn pv-btn-outline">
                            <i class="bx bx-edit"></i> Edit License
                        </a>
                    @else
                        <a href="/manage-license" class="pv-add-btn"><i class="bx bx-plus"></i> Add License</a>
                    @endif
                </div>

                @if($license)
                @php
                    $licExpired = \Carbon\Carbon::parse($license->expiry_date)->isPast();
                    $licDaysLeft = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($license->expiry_date), false);
                @endphp
                <div class="pv-license-card">
                    {{-- Key Display --}}
                    <div class="pv-lic-key-box">
                        <div class="pv-lic-key-label"><i class="bx bx-key"></i> License Key</div>
                        <div class="pv-lic-key-row">
                            <code id="licKey">{{ $license->eselicense_key }}</code>
                            <button class="pv-copy-btn" onclick="copyKey()" title="Copy">
                                <i class="bx bx-copy"></i> Copy
                            </button>
                        </div>
                    </div>

                    <div class="pv-lic-grid">
                        <div class="pv-lic-item">
                            <div class="pv-info-label"><i class="bx bx-code-alt"></i> Technology Stack</div>
                            <div class="pv-info-val fw-bold">{{ $license->technology_stack ?: 'N/A' }}</div>
                        </div>
                        <div class="pv-lic-item">
                            <div class="pv-info-label"><i class="bx bx-calendar-event"></i> Expiry Date</div>
                            <div class="pv-info-val fw-bold {{ $licExpired ? 'text-danger' : 'text-success' }}">
                                {{ \Carbon\Carbon::parse($license->expiry_date)->format('d F, Y') }}
                            </div>
                        </div>
                        <div class="pv-lic-item" style="grid-column:span 2;">
                            @if($licExpired)
                                <div class="pv-lic-status-bar pv-lic-expired">
                                    <i class="bx bx-error"></i>
                                    License Expired {{ \Carbon\Carbon::parse($license->expiry_date)->diffForHumans() }}
                                </div>
                            @elseif($licDaysLeft <= 30)
                                <div class="pv-lic-status-bar pv-lic-warn">
                                    <i class="bx bx-alarm"></i>
                                    Expires in {{ $licDaysLeft }} days — Consider renewal soon
                                </div>
                            @else
                                <div class="pv-lic-status-bar pv-lic-ok">
                                    <i class="bx bx-shield-quarter"></i>
                                    Active · {{ $licDaysLeft }} days remaining
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @else
                <div class="pv-empty-state">
                    <i class="bx bx-key"></i>
                    <p>No license associated with this project.</p>
                    <a href="/manage-license" class="pv-add-btn">Add License</a>
                </div>
                @endif
            </div>

            {{-- ─ PROPOSALS ─ --}}
            <div class="tab-pane fade" id="proposals" role="tabpanel">
                <div class="pv-tab-toolbar">
                    <h2 class="pv-tab-title"><i class="bx bx-paper-plane"></i> Client Proposals</h2>
                    <a href="/manage-proposal" class="pv-add-btn"><i class="bx bx-plus"></i> Create Proposal</a>
                </div>
                <div class="table-responsive">
                    <table class="leads-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th class="m-none">Date Sent</th>
                                <th class="m-none">Open Till</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proposals as $prop)
                            <tr>
                                <td class="fw-600">{{ Str::limit($prop->subject, 35) }}</td>
                                <td class="m-none text-muted">{{ \Carbon\Carbon::parse($prop->proposal_date)->format('d M, Y') }}</td>
                                <td class="m-none">
                                    @php $propExpired = \Carbon\Carbon::parse($prop->open_till)->isPast() && !in_array($prop->status, ['Accepted','Declined']); @endphp
                                    <span class="{{ $propExpired ? 'text-danger fw-bold' : 'text-muted' }}">
                                        {{ \Carbon\Carbon::parse($prop->open_till)->format('d M, Y') }}
                                    </span>
                                </td>
                                <td class="fw-bold" style="color:#006666;">₹{{ number_format($prop->grand_total, 0) }}</td>
                                <td>
                                    @if($prop->status == 'Accepted')
                                        <span class="pv-badge pv-badge-success">Accepted</span>
                                    @elseif($prop->status == 'Declined')
                                        <span class="pv-badge pv-badge-danger">Declined</span>
                                    @else
                                        <span class="pv-badge pv-badge-info">{{ $prop->status ?: 'Sent' }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5">
                                    <div class="pv-empty-state">
                                        <i class="bx bx-paper-plane"></i>
                                        <p>No proposals linked to this project.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>{{-- /tab-content --}}
    </div>{{-- /dash-container --}}
</section>

<style>
/* ══ Hero Banner ══ */
.pv-hero {
    background: linear-gradient(135deg, #004d4d 0%, #006666 60%, #009688 100%);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0,102,102,0.22);
}
.pv-hero-body {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 22px 24px 16px;
    flex-wrap: wrap;
    gap: 14px;
}
.pv-hero-avatar {
    width: 58px; height: 58px; border-radius: 16px;
    background: rgba(255,255,255,0.18);
    backdrop-filter: blur(4px);
    border: 2px solid rgba(255,255,255,0.35);
    color: #fff; font-size: 1.5rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.pv-hero-title {
    font-size: 1.3rem; font-weight: 800; color: #fff;
    margin: 0 0 4px; letter-spacing: -0.02em;
}
.pv-hero-meta {
    display: flex; align-items: center; flex-wrap: wrap; gap: 6px;
    font-size: 0.78rem; color: rgba(255,255,255,0.75);
}
.pv-hero-meta i { font-size: 0.85rem; }
.pv-sep { color: rgba(255,255,255,0.35); }
.pv-id { font-family: monospace; font-size: 0.72rem; background: rgba(255,255,255,0.12); padding: 1px 8px; border-radius: 20px; }

.pv-hero-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.pv-action-btn {
    width: 36px; height: 36px; border-radius: 50%;
    background: rgba(255,255,255,0.15); backdrop-filter: blur(4px);
    border: 1px solid rgba(255,255,255,0.3);
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-size: 1rem; text-decoration: none; transition: background 0.15s;
}
.pv-action-btn:hover { background: rgba(255,255,255,0.28); color: #fff; }
.pv-action-btn.pv-wa { background: rgba(37,211,102,0.25); }
.pv-edit-btn {
    display: flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,0.18); color: #fff;
    border: 1px solid rgba(255,255,255,0.3); border-radius: 20px;
    padding: 7px 16px; font-size: 0.82rem; font-weight: 600;
    text-decoration: none; transition: background 0.15s;
}
.pv-edit-btn:hover { background: rgba(255,255,255,0.3); color: #fff; }
.pv-back-btn {
    display: flex; align-items: center; gap: 5px;
    background: #fff; color: #006666; border-radius: 20px;
    padding: 7px 16px; font-size: 0.82rem; font-weight: 700;
    text-decoration: none; transition: box-shadow 0.15s;
}
.pv-back-btn:hover { box-shadow: 0 4px 14px rgba(0,0,0,0.15); color: #006666; }

.pv-progress-strip {
    height: 5px; background: rgba(255,255,255,0.18); position: relative;
}
.pv-progress-fill {
    height: 100%; border-radius: 0; transition: width 0.6s ease;
}

/* ══ KPI Cards ══ */
.pv-kpi-row {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 12px;
}
@media (max-width: 992px) { .pv-kpi-row { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 576px)  { .pv-kpi-row { grid-template-columns: repeat(2, 1fr); } }

.pv-kpi {
    background: #fff;
    border: 1px solid #e8eaed;
    border-radius: 14px;
    padding: 14px 16px;
    transition: box-shadow 0.18s;
}
.pv-kpi:hover { box-shadow: 0 4px 14px rgba(0,0,0,0.07); }
.pv-kpi-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.5px; color: #80868b; font-weight: 600; margin-bottom: 4px; }
.pv-kpi-val { font-size: 1.15rem; font-weight: 800; color: #202124; line-height: 1.2; }
.pv-kpi-sub { font-size: 0.68rem; color: #9aa0a6; margin-top: 3px; }

/* ══ Tab Body ══ */
.pv-tab-body {
    background: #fff;
    border: 1px solid #e8eaed;
    border-radius: 16px;
    padding: 24px;
    min-height: 300px;
}

.pv-tab-badge {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 18px; height: 18px; border-radius: 99px;
    background: #006666; color: #fff; font-size: 0.62rem; font-weight: 700;
    padding: 0 4px; margin-left: 4px;
}
.pv-tab-toolbar {
    display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;
}
.pv-tab-title {
    font-size: 1rem; font-weight: 700; color: #202124; margin: 0;
    display: flex; align-items: center; gap: 6px;
}
.pv-tab-title i { color: #006666; }

/* ══ Section Cards (Overview) ══ */
.pv-section-card {
    background: #fff; border: 1px solid #e8eaed; border-radius: 14px; padding: 18px; height: 100%;
}
.pv-section-head {
    font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
    color: #5f6368; margin-bottom: 14px;
    display: flex; align-items: center; gap: 6px;
}
.pv-section-head i { color: #006666; }
.pv-info-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
}
.pv-info-item { background: #f8fafb; border-radius: 10px; padding: 10px 12px; }
.pv-info-label { font-size: 0.68rem; color: #80868b; font-weight: 600; text-transform: uppercase; margin-bottom: 4px; display: flex; align-items: center; gap: 4px; }
.pv-info-val { font-size: 0.86rem; color: #202124; }
.pv-link { color: #006666; font-size: 0.8rem; word-break: break-all; }

.pv-client-block { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
.pv-client-avatar {
    width: 44px; height: 44px; border-radius: 12px;
    background: linear-gradient(135deg, #006666, #009688);
    color: #fff; font-size: 1.1rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.pv-client-name { font-weight: 700; color: #202124; font-size: 0.92rem; }
.pv-client-company { font-size: 0.75rem; color: #5f6368; }

.pv-contact-list { display: flex; flex-direction: column; gap: 8px; }
.pv-contact-row {
    display: flex; align-items: center; gap: 10px;
    font-size: 0.80rem; color: #3c4043; text-decoration: none;
    padding: 6px 10px; border-radius: 8px; background: #f8fafb; transition: background 0.12s;
}
.pv-contact-row:hover { background: #f0f4f0; color: #006666; }
.pv-contact-icon { color: #006666; font-size: 0.9rem; }

.pv-see-all { font-size: 0.75rem; color: #006666; text-decoration: none; display: flex; align-items: center; }

/* ══ Timeline ══ */
.pv-timeline { display: flex; flex-direction: column; gap: 0; }
.pv-timeline-item {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 10px 0; border-bottom: 1px solid #f0f0f0; position: relative;
}
.pv-timeline-item:last-child { border-bottom: none; }
.pv-tl-dot {
    width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0;
    margin-top: 4px; border: 2px solid transparent;
}
.pv-tl-paid { background: #34a853; border-color: rgba(52,168,83,0.3); box-shadow: 0 0 0 3px rgba(52,168,83,0.12); }
.pv-tl-pending { background: #fbbc04; border-color: rgba(251,188,4,0.3); }
.pv-tl-title { font-size: 0.85rem; font-weight: 700; color: #202124; display: flex; align-items: center; gap: 6px; }
.pv-tl-sub { font-size: 0.72rem; color: #80868b; margin-top: 2px; }

/* ══ Recovery Cards ══ */
.pv-rec-card {
    display: flex; align-items: center; gap: 14px;
    padding: 14px 16px; border: 1px solid #f0f0f0;
    border-radius: 12px; margin-bottom: 8px; background: #fff; transition: box-shadow 0.15s;
}
.pv-rec-card:hover { box-shadow: 0 2px 10px rgba(0,0,0,0.07); }
.pv-rec-icon {
    width: 42px; height: 42px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0;
}
.pv-rec-paid { background: rgba(52,168,83,0.1); color: #34a853; }
.pv-rec-pend { background: rgba(251,188,4,0.1); color: #f59f00; }
.pv-rec-danger { background: rgba(234,67,53,0.1); color: #ea4335; }
.pv-rec-body { flex: 1; min-width: 0; }
.pv-rec-amount { font-size: 0.95rem; font-weight: 700; color: #202124; }
.pv-rec-meta { font-size: 0.72rem; color: #80868b; margin-top: 2px; }

/* ══ Billing Summary ══ */
.pv-billing-summary {
    display: flex; align-items: center; gap: 0;
    background: #f8fafb; border: 1px solid #e8eaed; border-radius: 12px;
    padding: 12px 20px; flex-wrap: wrap; gap: 12px;
}
.pv-bs-item { display: flex; flex-direction: column; align-items: center; gap: 2px; }
.pv-bs-item span { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.5px; color: #80868b; }
.pv-bs-item strong { font-size: 1rem; font-weight: 800; color: inherit; }
.pv-bs-sep { width: 1px; height: 32px; background: #e8eaed; }
@media (max-width: 576px) { .pv-bs-sep { display: none; } }

/* ══ License Card ══ */
.pv-license-card { background: #f8fafb; border: 1px solid #e8eaed; border-radius: 14px; padding: 20px; }
.pv-lic-key-box { margin-bottom: 16px; }
.pv-lic-key-label { font-size: 0.70rem; text-transform: uppercase; letter-spacing: 0.5px; color: #80868b; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 4px; }
.pv-lic-key-row {
    display: flex; align-items: center; justify-content: space-between;
    background: #fff; border: 1px solid #e8eaed; border-radius: 10px; padding: 12px 16px;
    gap: 12px;
}
.pv-lic-key-row code {
    font-size: 1rem; font-weight: 700; color: #006666;
    letter-spacing: 0.05em; word-break: break-all;
}
.pv-copy-btn {
    display: flex; align-items: center; gap: 5px;
    background: #006666; color: #fff; border: none;
    border-radius: 20px; padding: 6px 14px; font-size: 0.78rem; font-weight: 600;
    cursor: pointer; flex-shrink: 0; transition: background 0.15s;
}
.pv-copy-btn:hover { background: #005555; }

.pv-lic-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.pv-lic-item { background: #fff; border: 1px solid #e8eaed; border-radius: 10px; padding: 12px 14px; }
.pv-lic-status-bar {
    display: flex; align-items: center; gap: 8px;
    border-radius: 10px; padding: 12px 16px; font-size: 0.82rem; font-weight: 600;
}
.pv-lic-ok      { background: rgba(52,168,83,0.08);  color: #34a853; }
.pv-lic-warn    { background: rgba(251,188,4,0.10);   color: #f59f00; }
.pv-lic-expired { background: rgba(234,67,53,0.08);   color: #ea4335; }

/* ══ Badges ══ */
.pv-badge {
    display: inline-flex; align-items: center;
    padding: 3px 10px; border-radius: 20px; font-size: 0.68rem; font-weight: 700;
}
.pv-badge-success { background: rgba(52,168,83,0.1);  color: #34a853; }
.pv-badge-warn    { background: rgba(251,188,4,0.12);  color: #f59f00; }
.pv-badge-danger  { background: rgba(234,67,53,0.1);   color: #ea4335; }
.pv-badge-info    { background: rgba(26,115,232,0.1);  color: #1a73e8; }

/* ══ Add / CTA Buttons ══ */
.pv-add-btn {
    display: inline-flex; align-items: center; gap: 5px;
    background: #006666; color: #fff; border: none; border-radius: 20px;
    padding: 7px 16px; font-size: 0.80rem; font-weight: 600;
    text-decoration: none; transition: background 0.15s;
}
.pv-add-btn:hover { background: #005555; color: #fff; }
.pv-add-btn.pv-btn-outline {
    background: transparent; border: 1px solid #006666; color: #006666;
}
.pv-add-btn.pv-btn-outline:hover { background: rgba(0,102,102,0.06); }

/* ══ Empty State ══ */
.pv-empty-state {
    text-align: center; padding: 50px 20px; color: #9aa0a6;
}
.pv-empty-state i { font-size: 3rem; display: block; margin-bottom: 12px; color: #dadce0; }
.pv-empty-state p { font-size: 0.85rem; margin-bottom: 14px; }

/* ══ Type Pill ══ */
.pj-type-pill {
    display: inline-block;
    background: rgba(0,102,102,0.08); color: #006666;
    font-size: 0.68rem; font-weight: 600;
    border-radius: 20px; padding: 2px 10px;
}
</style>

<style>
/* Task Interactive States */
.pvt-check-wrap { cursor: pointer; display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; }
.pvt-check-circle {
    width: 22px; height: 22px; border-radius: 50%; border: 2px solid currentColor;
    display: flex; align-items: center; justify-content: center; transition: all 0.2s;
    background: transparent;
}
.pvt-check-circle i { font-size: 14px; opacity: 0; transform: scale(0.5); transition: all 0.2s; }
.pvt-checked { background: currentColor !important; }
.pvt-checked i { opacity: 1; transform: scale(1); color: #fff !important; }
.pvt-name-done { text-decoration: line-through; opacity: 0.6; }
</style>

<script>
function copyKey() {
    const key = document.getElementById('licKey').textContent.trim();
    navigator.clipboard.writeText(key).then(() => {
        const btn = document.querySelector('.pv-copy-btn');
        btn.innerHTML = '<i class="bx bx-check"></i> Copied!';
        btn.style.background = '#34a853';
        setTimeout(() => {
            btn.innerHTML = '<i class="bx bx-copy"></i> Copy';
            btn.style.background = '';
        }, 2000);
    });
}

$(document).ready(function() {
    // 1. Animate progress bar on load
    const fills = document.querySelectorAll('.pv-progress-fill');
    fills.forEach(f => {
        const w = f.style.width;
        f.style.width = '0';
        setTimeout(() => { f.style.width = w; }, 100);
    });

    // 2. Handle task status toggle
    $('.task-status-check').on('change', function() {
        const $check = $(this);
        const taskId = $check.data('id');
        const isChecked = $check.is(':checked');
        const $row = $('#task-row-' + taskId);
        const $icon = $('#task-icon-' + taskId);
        const $circle = $check.next('.pvt-check-circle');
        const $name = $('#task-name-' + taskId);
        const $badgeContainer = $row.find('.task-status-badge-container');

        // Immediate visual feedback
        if (isChecked) {
            $circle.addClass('pvt-checked');
            $name.addClass('pvt-name-done');
            $icon.removeClass('pv-rec-pend pv-rec-danger').addClass('pv-rec-paid');
            $badgeContainer.html('<span class="pv-badge pv-badge-success">Done</span>');
        } else {
            $circle.removeClass('pvt-checked');
            $name.removeClass('pvt-name-done');
            // Assuming it goes back to 'Pending' visually; server state will determine if it stays overdue
            $icon.removeClass('pv-rec-paid').addClass('pv-rec-pend');
            $badgeContainer.html('<span class="pv-badge pv-badge-warn">Pending</span>');
        }

        $.ajax({
            url: "{{ route('crm_tasks.update_status') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: taskId,
                status: isChecked ? 'Completed' : 'Pending'
            },
            error: function() {
                alert('Connection error while updating task status');
            }
        });
    });
});
</script>
@endsection
