@extends('layout')
@section('title', 'Customers - eseCRM')

@section('content')
    @php
        $roles     = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));
    @endphp

    <link rel="stylesheet" href="{{ asset('assets/css/lead-panel.css') }}">

    <section class="task__section">
        @include('inc.header', ['title' => 'Customers'])

        <div class="dash-container">

            {{-- Toolbar --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <form action="/clients" method="GET" id="clientFilterForm" class="d-flex align-items-center gap-2">
                        <div class="lb-search-box">
                            <i class="bx bx-search"></i>
                            <input type="text" name="search" id="clientSearch" placeholder="Search customers..." value="{{ $search ?? '' }}">
                        </div>
                        <select name="status" id="clientStatusFilter" class="lb-select-sm">
                            <option value="">All Status</option>
                            <option value="1" {{ ($status ?? '') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ ($status ?? '') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </form>
                </div>
                <div class="leads-toolbar-right">
                    <button class="lb-icon-btn" onclick="location.reload()" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                    @if(in_array('clients_add', $roleArray) || in_array('All', $roleArray))
                        <a href="/manage-client" class="lb-btn lb-btn-primary">
                            <i class="bx bx-plus"></i>
                            <span class="d-none d-sm-inline">Add Customer</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Table Card --}}
            <div class="dash-card mb-4">
                <div class="table-responsive">
                    <table id="lists" class="leads-table clients" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th class="m-none">Company</th>
                                <th class="m-none">Email</th>
                                <th class="m-none mw80">Mobile</th>
                                <th class="m-none mw135 text-center">Created</th>
                                <th class="m-none mw80">Status</th>
                                <th class="text-center position-sticky end-0 bg-default mw60">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clients as $client)
                                <tr class="view selectrow" id="{{ $client->id ?? '' }}">
                                    {{-- Name --}}
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="lb-avatar-sm">{{ strtoupper(substr($client->name ?? 'C', 0, 1)) }}</div>
                                            <div>
                                                <div class="fw-500">{{ $client->name ?? '' }}</div>
                                                <div class="text-muted small d-none">{{ $client->company ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    {{-- Company --}}
                                    <td class="m-none text-muted">
                                        {{ substr(($client->company ?? ''), 0, 22) }}
                                    </td>
                                    {{-- Email --}}
                                    <td class="m-none text-muted">
                                        {{ substr(($client->email ?? ''), 0, 22) }}
                                    </td>
                                    {{-- Mobile --}}
                                    <td class="m-none">{{ $client->mob ?? '' }}</td>
                                    {{-- Created --}}
                                    <td class="m-none text-center">
                                        {!! date_format(date_create($client->created_at ?? ''), 'd M, Y') !!}
                                    </td>
                                    {{-- Status --}}
                                    <td>
                                        @if($client->status == '1')
                                            <span class="leads-status-badge leads-status-fresh">Active</span>
                                        @else
                                            <span class="leads-status-badge leads-status-loss">Inactive</span>
                                        @endif
                                    </td>
                                    {{-- Actions --}}
                                    <td class="position-sticky end-0 bg-white">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            @if(!empty($client->whatsapp))
                                                <a href="https://api.whatsapp.com/send/?phone={{ $client->whatsapp }}&text=Hi&type=phone_number&app_absent=0"
                                                   target="_blank" class="kb-action-btn kb-action-wa" title="WhatsApp">
                                                    <i class="bx bxl-whatsapp"></i>
                                                </a>
                                            @endif
                                            @if(!empty($client->email))
                                                <a href="mailto:{{ $client->email }}"
                                                   class="kb-action-btn kb-action-email" title="Email">
                                                    <i class="bx bx-envelope"></i>
                                                </a>
                                            @endif
                                            @if(!empty($client->mob))
                                                <a href="tel:{{ $client->mob }}"
                                                   class="kb-action-btn kb-action-call" title="Call">
                                                    <i class="bx bx-phone"></i>
                                                </a>
                                            @endif
                                            @if(in_array('client_edit', $roleArray) || in_array('All', $roleArray))
                                                <a href="/manage-client?id={{ $client->id ?? '' }}"
                                                   class="kb-action-btn kb-action-edit" title="Edit">
                                                    <i class="bx bx-pencil"></i>
                                                </a>
                                            @endif
                                            @if(in_array('client_delete', $roleArray) || in_array('All', $roleArray))
                                                <a href="javascript:void(0)"
                                                   class="kb-action-btn kb-action-del delete"
                                                   id="{{ $client->id }}" date-page="clientDelete" title="Delete">
                                                    <i class="bx bx-trash"></i>
                                                </a>
                                            @endif
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

    {{-- Hidden import form --}}
    <form id="Clientsubmit" action="/import-client-file" method="post" enctype="multipart/form-data">
        @csrf
        <input type="file" name="impClientFile" id="impClientFile" accept=".csv, .xls" style="display:none;" />
    </form>

    {{-- Offcanvas: Client Details --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="clientModal" aria-labelledby="clientModalLabel"
         style="width:800px; max-width:100vw; border:none; box-shadow:-10px 0 30px rgba(0,0,0,0.15);">

        <div class="offcanvas-header lb-offcanvas-banner">
            <div class="d-flex align-items-center gap-3">
                <div class="lb-offcanvas-avatar" id="clientAvatarBadge">C</div>
                <div class="text-white">
                    <h5 class="offcanvas-title mb-0" id="clientModalLabel">Customer Details</h5>
                    <div class="d-flex align-items-center gap-2 small opacity-75 mt-1">
                        <span id="clientAvatarSub">Loading...</span>
                        <span class="lb-dot"></span>
                        <span id="clientSince">Added on —</span>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="lb-header-actions me-3">
                    <a href="#" id="c_btnCall" class="lb-action-btn-circle" title="Call"><i class="bx bx-phone"></i></a>
                    <a href="#" id="c_btnWa" class="lb-action-btn-circle" title="WhatsApp"><i class="bx bxl-whatsapp"></i></a>
                    <a href="#" id="c_btnMail" class="lb-action-btn-circle" title="Email"><i class="bx bx-envelope"></i></a>
                </div>
                <button type="button" class="lb-btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close">
                    <i class="bx bx-x"></i>
                </button>
            </div>
        </div>

        <div class="offcanvas-body p-0">
            {{-- Tabs Navigation --}}
            <div class="ld-tab-nav">
                <button class="ld-tab active" onclick="cTab(this, 'c-tab-info')">
                    <i class="bx bx-user"></i> Profile
                </button>
                <button class="ld-tab" onclick="cTab(this, 'c-tab-timeline')">
                    <i class="bx bx-history"></i> Timeline
                </button>
                <button class="ld-tab" onclick="cTab(this, 'c-tab-props')">
                    <i class="bx bx-file"></i> Proposals
                </button>
                <button class="ld-tab" onclick="cTab(this, 'c-tab-projects')">
                    <i class="bx bx-briefcase"></i> Projects
                </button>
            </div>

            <div class="ld-tab-container">
                
                {{-- Profile Tab --}}
                <div id="c-tab-info" class="ld-tab-content active">
                    <div class="ld-info-grid">
                        {{-- Contact Card --}}
                        <div class="ld-info-card">
                            <h6><i class="bx bx-phone-call"></i> Contact Information</h6>
                            <div class="ld-info-row">
                                <span class="label">Primary Phone</span>
                                <span class="value" id="c_mob">—</span>
                            </div>
                            <div class="ld-info-row">
                                <span class="label">WhatsApp</span>
                                <span class="value" id="c_wa">—</span>
                            </div>
                            <div class="ld-info-row">
                                <span class="label">Email Address</span>
                                <span class="value" id="c_email">—</span>
                            </div>
                            <div class="ld-info-row">
                                <span class="label">Website</span>
                                <span class="value"><a href="#" id="c_website" target="_blank">—</a></span>
                            </div>
                        </div>

                        {{-- Business Card --}}
                        <div class="ld-info-card">
                            <h6><i class="bx bx-building-house"></i> Business Details</h6>
                            <div class="ld-info-row">
                                <span class="label">Company Name</span>
                                <span class="value" id="c_company_val">—</span>
                            </div>
                            <div class="ld-info-row">
                                <span class="label">GST Number</span>
                                <span class="value" id="c_gst">—</span>
                            </div>
                            <div class="ld-info-row">
                                <span class="label">Position</span>
                                <span class="value" id="c_position">—</span>
                            </div>
                            <div class="ld-info-row">
                                <span class="label">Industry</span>
                                <span class="value" id="c_industry">—</span>
                            </div>
                        </div>

                        {{-- Location Card --}}
                        <div class="ld-info-card full-width">
                            <h6><i class="bx bx-map"></i> Location & Address</h6>
                            <div class="ld-info-row">
                                <span class="label">Full Address</span>
                                <span class="value" id="c_location_val">—</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 pt-0">
                        <a href="#" id="c_editBtn" class="lb-btn lb-btn-primary w-100">
                            <i class="bx bx-pencil"></i> Edit Customer Full Profile
                        </a>
                    </div>
                </div>

                {{-- Timeline Tab --}}
                <div id="c-tab-timeline" class="ld-tab-content">
                    <div class="p-4">
                        <div class="ld-timeline" id="c_timeline">
                            {{-- Timeline items will be injected here --}}
                        </div>
                    </div>
                </div>

                {{-- Proposals Tab --}}
                <div id="c-tab-props" class="ld-tab-content">
                    <div class="p-0">
                        <table class="table leads-table mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Subject</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="c_proposals">
                                <tr><td colspan="4" class="text-center py-4">Loading proposals...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Projects Tab --}}
                <div id="c-tab-projects" class="ld-tab-content">
                    <div class="p-0">
                        <table class="table leads-table mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Project</th>
                                    <th>Value</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody id="c_projects">
                                <tr><td colspan="3" class="text-center py-4">Loading projects...</td></tr>
                            </tbody>
                        </table>

                        <div class="p-3 bg-light border-top">
                            <h6 class="mb-3 small text-uppercase fw-bold text-muted">Related Invoices</h6>
                            <table class="table leads-table mb-0 bg-white shadow-sm rounded">
                                <thead>
                                    <tr>
                                        <th>Inv #</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="c_invoices">
                                    <tr><td colspan="4" class="text-center py-3 small text-muted">No invoices found.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection