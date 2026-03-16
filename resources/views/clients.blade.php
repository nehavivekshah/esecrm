@extends('layout')
@section('title', 'Customers - eseCRM')

@section('content')
    @php
        $roles     = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));
    @endphp

    <section class="task__section">
        @include('inc.header', ['title' => 'Customers'])

        <div class="dash-container">

            {{-- Toolbar --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <span class="lb-page-count" id="clientCount">
                        <i class="bx bx-group"></i>
                        {{ count($clients) }} Customers
                    </span>
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

    {{-- Offcanvas: Client Details / Edit --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="clientModal" aria-labelledby="clientModalLabel"
         style="width:800px; max-width:100vw; border-top-left-radius:20px; border-bottom-left-radius:20px; box-shadow:-10px 0 30px rgba(0,0,0,0.1);">

        <div class="offcanvas-header lb-offcanvas-header">
            <div class="d-flex align-items-center gap-3">
                <div class="lb-offcanvas-avatar" id="clientAvatarBadge">C</div>
                <div>
                    <h5 class="offcanvas-title" id="clientModalLabel">Customer Details</h5>
                    <span class="lb-offcanvas-subtitle" id="clientAvatarSub">Loading...</span>
                </div>
            </div>
            <button type="button" class="lb-offcanvas-close" data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="bx bx-x"></i>
            </button>
        </div>

        <div class="offcanvas-body lb-offcanvas-body">

            {{-- Tabs --}}
            <ul class="nav nav-tabs" id="clientTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#client-details" type="button">
                        <i class="bx bx-user me-1"></i> Profile
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#new-comment" type="button">
                        <i class="bx bx-calendar me-1"></i> Reminder
                    </button>
                </li>
            </ul>

            <div class="tab-content">

                {{-- Profile Tab --}}
                <div class="tab-pane fade show active" id="client-details" role="tabpanel">
                    <form action="manage-client" method="post" class="row g-3">
                        @csrf
                        <input type="hidden" id="id" name="id" value="{{ $_GET['id'] ?? '' }}">

                        {{-- Contact Info --}}
                        <div class="col-12">
                            <p class="lb-offcanvas-body divider">Contact Information</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-envelope-open"></i></span>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                <input type="text" class="form-control" id="mob" name="mob" placeholder="91XXXXXXXXXX" value="91" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Alternative Mobile</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-phone"></i></span>
                                <input type="text" class="form-control" id="alterMob" name="alterMob" placeholder="91XXXXXXXXXX" value="91">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text" style="color:#25d366;"><i class="bx bxl-whatsapp"></i></span>
                                <input type="text" class="form-control" id="whatsapp" name="whatsapp" placeholder="91XXXXXXXXXX" value="91">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Website</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-link"></i></span>
                                <input type="url" class="form-control" id="website" name="website" placeholder="https://example.com">
                            </div>
                        </div>

                        {{-- Business Info --}}
                        <div class="col-12 mt-2">
                            <p class="lb-offcanvas-body divider">Business Details</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Company</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-briefcase"></i></span>
                                <input type="text" class="form-control" id="company" name="company" placeholder="Company Name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">GST No.</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                                <input type="text" class="form-control" id="gst" name="gst" placeholder="GSTIN">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Position / Role</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-user-pin"></i></span>
                                <input type="text" class="form-control" id="position" name="position" placeholder="e.g. Manager">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Industry</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-building"></i></span>
                                <input type="text" class="form-control" id="industry" name="industry" placeholder="e.g. IT, Retail">
                            </div>
                        </div>

                        {{-- Address --}}
                        <div class="col-12 mt-2">
                            <p class="lb-offcanvas-body divider">Address</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Street / Building</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-home"></i></span>
                                <input type="text" class="form-control" id="address" name="address[address]" placeholder="Address">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-map"></i></span>
                                <input type="text" class="form-control" id="city" name="address[city]" placeholder="City">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">State</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-map-pin"></i></span>
                                <input type="text" class="form-control" id="state" name="address[state]" placeholder="State">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-globe"></i></span>
                                <input type="text" class="form-control" id="country" name="address[country]" placeholder="Country">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Zip / Postal Code</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-pin"></i></span>
                                <input type="text" class="form-control" id="zip" name="address[zip]" placeholder="Zip Code">
                            </div>
                        </div>

                        {{-- Status & Save --}}
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-list-check"></i></span>
                                <select class="form-control" id="status" name="status"></select>
                            </div>
                        </div>

                        @if(in_array('clients_edit', $roleArray) || in_array('All', $roleArray))
                            <div class="col-12">
                                <div class="lb-form-footer">
                                    <span class="text-muted small">Fields marked <span class="text-danger">*</span> are required</span>
                                    <div class="d-flex gap-2">
                                        <button type="reset" class="lb-btn lb-btn-ghost">
                                            <i class="bx bx-reset"></i> Reset
                                        </button>
                                        <button type="submit" class="lb-btn lb-btn-primary">
                                            <i class="bx bx-check-circle"></i> Save
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- Reminder Tab --}}
                <div class="tab-pane fade" id="new-comment" role="tabpanel">
                    <div class="cmtArea">
                        <form action="manage-lead-comment" method="post" class="cmt-form row g-3">
                            @csrf
                            <input type="hidden" name="client_id" id="commentClientId">
                            <div class="col-12">
                                <label class="form-label">Message <span class="text-danger">*</span></label>
                                <textarea class="form-control" rows="5" id="message" name="message"
                                    placeholder="Write your note here..." required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Next Follow-up Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                    <input type="datetime-local" class="form-control" id="nxtDate" name="nxtDate" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="lb-form-footer">
                                    <span></span>
                                    <div class="d-flex gap-2">
                                        <button type="reset" class="lb-btn lb-btn-ghost">
                                            <i class="bx bx-reset"></i> Reset
                                        </button>
                                        <button type="submit" class="lb-btn lb-btn-primary">
                                            <i class="bx bx-check-circle"></i> Submit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>{{-- /tab-content --}}
        </div>{{-- /offcanvas-body --}}
    </div>

@endsection