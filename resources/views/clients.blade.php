@extends('layout')
@section('title', 'Customers - eseCRM')

@section('content')
    @php

        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));

    @endphp
    <section class="task__section">
        @include('inc.header', ['title' => 'Customers'])
        <div class="container-fluid">
            <!-- Filter Bar -->
            <div class="card p-3 mb-4 border-0 shadow-sm d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <h5 class="mb-0 font-weight-bold text-slate-800">Customers List</h5>
                @if(in_array('clients_add', $roleArray) || in_array('All', $roleArray))
                    <div class="d-flex gap-2">
                        <!--<a href="javascript:void(0)" class="btn btn-light btn-sm rounded-pill shadow-sm px-3" id="importFile"><i class="bx bx-upload"></i> <span>Import</span></a>-->
                        <a href="/manage-client" class="btn btn-indigo shadow-sm rounded-pill btn-sm px-3"><i class="bx bx-plus"></i> <span>Add New</span></a>
                    </div>
                @endif
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-0 table-responsive">
                            <table id="lists" class="table table-hover table-condensed m-table clients mb-0"
                                style="width:100%;border-radius:var(--card-radius);overflow:hidden;">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th class="m-none">Company</th>
                                        <th class="m-none">Email Id</th>
                                        <th class="m-none mw80">Mobile No.</th>
                                        <th class="m-none mw135 text-center">Date Created</th>
                                        <th class="m-none mw80">Status</th>
                                        <th class="position-sticky end-0 bg-slate-50" width="60px">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($clients as $client)
                                        <tr class="view selectrow" id="{{ $client->id ?? '' }}">
                                            <td>{{ $client->name ?? '' }}<span class="small d-none">{{ $client->company ?? '' }}</span></td>
                                            <td class="m-none">{{ substr(($client->company ?? ''), 0, 20) . ".." }}</td>
                                            <td class="m-none">{{ substr(($client->email ?? ''), 0, 20) . ".." }}</td>
                                            <td class="m-none">{{ $client->mob ?? '' }}</td>
                                            <td class="m-none text-center">{!! date_format(date_create($client->created_at ?? ''), 'd M, Y') !!}</td>
                                            <td width="80px">@if($client->status == '1')<span class="badge bg-emerald-soft text-emerald-600 rounded-pill">Active</span>@else<span class="badge bg-rose-soft text-rose-500 rounded-pill">Deactive</span>@endif</td>
                                            <td class="position-sticky end-0 bg-white">
                                                <div class="table-btn m-none">
                                                    @if(!empty($client->whatsapp))<a href="https://api.whatsapp.com/send/?phone={{ $client->whatsapp }}&text=Hi&type=phone_number&app_absent=0" class="btn btn-success bg-success text-white btn-sm" title="whatsapp"><i class="bx bxl-whatsapp"></i></a>@endif
                                                    @if(!empty($client->email))<a href="mailto:{{ $client->email }}" class="btn btn-info text-white btn-sm" title="Email"><i class="bx bx-envelope"></i></a>@endif
                                                    @if(!empty($client->mob))<a href="tel:{{ $client->mob }}" class="btn btn-warning text-dark btn-sm" title="Call"><i class="bx bx-phone"></i></a>@endif
                                                    @if(in_array('client_edit', $roleArray) || in_array('All', $roleArray))
                                                        <a href="/manage-client?id={{ $client->id ?? '' }}" class="btn btn-light btn-sm edit shadow-sm" title="Edit"><i class="bx bx-pencil text-primary"></i></a>
                                                    @endif
                                                    @if(in_array('client_delete', $roleArray) || in_array('All', $roleArray))
                                                        <a href="javascript:void(0)" class="btn btn-light btn-sm delete shadow-sm" id="{{ $client->id }}" date-page="clientDelete" title="Delete"><i class="bx bx-trash text-danger"></i></a>
                                                    @endif
                                                </div>
                                                <div class="table-btn d-none">
                                                    @if(!empty($client->whatsapp))<a href="https://api.whatsapp.com/send/?phone={{ $client->whatsapp }}&text=Hi&type=phone_number&app_absent=0" target="_blank" class="btn btn-success bg-success text-white btn-sm" title="whatsapp"><i class="bx bxl-whatsapp"></i></a>@endif
                                                    @if(!empty($client->email))<a href="mailto:{{ $client->email }}" class="btn btn-info text-white btn-sm" title="Email"><i class="bx bx-envelope"></i></a>@endif
                                                    @if(!empty($client->mob))<a href="tel:{{ $client->mob }}" class="btn btn-warning text-dark btn-sm" title="Call"><i class="bx bx-phone"></i></a>@endif
                                                    @if(in_array('client_edit', $roleArray) || in_array('All', $roleArray))
                                                        <a href="javascript:void(0)" class="btn btn-primary text-white btn-sm edit" data-view-id="{{ $client->id ?? '' }}" title="Call"><i class="bx bx-pencil"></i></a>
                                                    @endif
                                                    @if(in_array('client_delete', $roleArray) || in_array('All', $roleArray))
                                                        <a href="javascript:void(0)" class="btn btn-danger btn-sm delete" id="{{ $client->id }}" date-page="clientDelete" title="Delete"><i class="bx bx-trash"></i></a>
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
            </div>
        </div>
    </section>

    <form id="clientsubmit" action="/import-client-file" method="post" enctype="multipart/form-data">
        @csrf
        <input type="file" name="impClientFile" id="impClientFile" accept=".csv, .xls" style="display:none;" />
    </form>

    <!-- Offcanvas Structure for Client Details -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="clientModal" aria-labelledby="clientModalLabel" style="width: 800px; max-width: 100vw; border-top-left-radius: 20px; border-bottom-left-radius: 20px; box-shadow: -10px 0 30px rgba(0,0,0,0.1);">
        <div class="offcanvas-header border-bottom bg-slate-50" style="border-top-left-radius: 20px;">
            <h5 class="offcanvas-title font-weight-bold text-slate-800" id="clientModalLabel">Client Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-3 bg-white">
            <div class="tab-content" id="clientModalTabContent">
                <!-- Client Details Tab -->
                <div class="tab-pane fade show active" id="client-details" role="tabpanel"
                    aria-labelledby="client-details-tab">
                            <!--<div id="clientinfo" class="mt-3"></div>-->
                            <form action="manage-client" method="post" class="row g-3">
                                @csrf
                                <div class="col-md-6 form-group">
                                    <label for="name">Name*</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-user'></i></span>
                                        <input type="text" class="form-control" id="name" name="name"
                                            placeholder="Enter Name*" required>
                                        <input type="hidden" id="id" name="id" value="{{ $_GET['id'] ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="email">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-envelope-open'></i></span>
                                        <input type="email" class="form-control" id="email" name="email"
                                            placeholder="Enter Email Id">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="mobile">Mobile Number*</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-phone'></i></span>
                                        <input type="text" class="form-control" id="mob" name="mob"
                                            placeholder="Enter Mobile Number*" value="91" required>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="mobile">Alternative Mobile Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-phone'></i></span>
                                        <input type="text" class="form-control" id="alterMob" name="alterMob"
                                            placeholder="Enter Mobile Number" value="91">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="whatsapp">Whatsapp</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bxl-whatsapp'></i></span>
                                        <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                                            placeholder="Enter Whatsapp Number" value="91">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="company">Company</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-briefcase'></i></span>
                                        <input type="text" class="form-control" id="company" name="company"
                                            placeholder="Enter Company">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="gst">GST No.</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-link'></i></span>
                                        <input type="text" class="form-control" id="gst" name="gst"
                                            placeholder="Enter GST No.">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="position">Position</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-user'></i></span>
                                        <input type="text" class="form-control" id="position" name="position"
                                            placeholder="Enter Position">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="industry">Industry</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-building'></i></span>
                                        <input type="text" class="form-control" id="industry" name="industry"
                                            placeholder="Enter Industry">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="address">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-home'></i></span>
                                        <input type="text" class="form-control" id="address" name="address[address]"
                                            placeholder="Enter Address">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="city">City</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-map'></i></span>
                                        <input type="text" class="form-control" id="city" name="address[city]"
                                            placeholder="Enter City">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="state">State</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-map-pin'></i></span>
                                        <input type="text" class="form-control" id="state" name="address[state]"
                                            placeholder="Enter State">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="country">Country</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-globe'></i></span>
                                        <input type="text" class="form-control" id="country" name="address[country]"
                                            placeholder="Enter Country">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="zip">Zip/Postal Code</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-pin'></i></span>
                                        <input type="text" class="form-control" id="zip" name="address[zip]"
                                            placeholder="Enter Zip/Postal Code">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="website">Website</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-link'></i></span>
                                        <input type="url" class="form-control" id="website" name="website"
                                            placeholder="Enter Website Link">
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Status:</label><br>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-list-check'></i></span>
                                        <select class="form-control" id="status" name="status"></select>
                                    </div>
                                </div>
                                @if(in_array('clients_edit', $roleArray) || in_array('All', $roleArray))
                                    <div class="form-group col-md-12 text-center mt-3">
                                        <button type="submit" class="btn btn-indigo px-4">Save</button>
                                        <button type="reset" class="btn btn-light border px-4">Reset</button>
                                    </div>
                                @endif
                            </form>
                        </div>

                        <!-- Comments Tab -->
                        <div class="tab-pane fade" id="comments" role="tabpanel" aria-labelledby="comments-tab">
                            <div id="leadcomments" class="mt-3"></div>
                        </div>

                        <!-- New Comment Tab -->
                        <div class="tab-pane fade" id="new-comment" role="tabpanel" aria-labelledby="new-comment-tab">
                            <div class="cmtArea mt-3">
                                <form action="manage-lead-comment" method="post" class="cmt-form">
                                    @csrf
                                    <input type="hidden" name="client_id" id="commentClientId">
                                    <div class="form-group">
                                        <label for="message" class="form-label">Message*:</label>
                                        <textarea class="form-control" rows="5" id="message" name="message"
                                            placeholder="Write Here..." required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="nxtDate" class="form-label">Next Date*:</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-calendar'></i></span>
                                            <input type="datetime-local" class="form-control" min="{{today()}}" id="nxtDate"
                                                name="nxtDate" required>
                                        </div>
                                    </div>
                                    <div class="form-group text-center pt-2">
                                        <button type="submit" class="btn btn-indigo px-4">Submit</button>
                                        <button type="reset" class="btn btn-light border px-4">Reset</button>
                                    </div>
                                </form>
                            </div>
                        </div>
            </div>
        </div>
    </div>
@endsection