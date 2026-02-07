@extends('layout')
@section('title', 'Leads - eseCRM')

@section('content')

    @php

        $location = json_decode(($leads->location ?? '[]'), true);

    @endphp
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i>
            @if(!empty($_GET['id'])) Edit Lead @else New Lead @endif
            <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>
        <div class="container-fluid py-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="/leads" class="btn btn-light border-0 shadow-sm rounded-circle p-2"
                    style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="bx bx-arrow-back" style="font-size: 1.2rem; color: var(--color-default);"></i>
                </a>
                <h1 class="h4 fw-bold mb-0">@if(!empty($_GET['id'])) Edit Lead Details @else Add New Lead @endif</h1>
            </div>

            <form action="manage-lead" method="post" class="row g-4">
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
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Full Name"
                                        value="{{ $leads->name ?? '' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="email">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-envelope'></i></span>
                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="email@example.com" value="{{ $leads->email ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="mobile">Mobile Number*</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-phone'></i></span>
                                    <input type="text" class="form-control" id="mob" name="mob" placeholder="91XXXXXXXXXX"
                                        value="{{ $leads->mob ?? '91' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="whatsapp">Whatsapp</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bxl-whatsapp'></i></span>
                                    <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                                        placeholder="91XXXXXXXXXX" value="{{ $leads->whatsapp ?? '91' }}">
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="gstno">GST No.</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-id-card'></i></span>
                                    <input type="text" class="form-control" id="gstno" name="gstno"
                                        placeholder="GSTIN Number" value="{{ $leads->gstno ?? '' }}">
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
                                <label for="company">Company Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-buildings'></i></span>
                                    <input type="text" class="form-control" id="company" name="company"
                                        placeholder="Enter Company" value="{{ $leads->company ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="position">Position</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-user-pin'></i></span>
                                    <input type="text" class="form-control" id="position" name="position"
                                        placeholder="Job Title" value="{{ $leads->position ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="industry">Industry</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-cog'></i></span>
                                    <input type="text" class="form-control" id="industry" name="industry"
                                        placeholder="e.g. IT, Healthcare" value="{{ $leads->industry ?? '' }}">
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="website">Website</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-globe'></i></span>
                                    <input type="url" class="form-control" name="website" placeholder="https://example.com"
                                        value="{{ $leads->website ?? '' }}">
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
                                    <input type="text" class="form-control" name="address[country]" placeholder="Country"
                                        value="{{ $location['country'] ?? 'India' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="zip">Zip/Postal Code</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-pin'></i></span>
                                    <input type="text" class="form-control" name="address[zip]" placeholder="Zip Code"
                                        value="{{ $location['zip'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lead Intelligence -->
                <div class="col-lg-6">
                    <div class="form-card">
                        <div class="section-title">
                            <i class='bx bx-brain'></i> Lead Intelligence
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="source">Assigned To</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-user-plus'></i></span>
                                    <input type="text" class="form-control" id="source" name="source" placeholder="Assignee"
                                        value="{{ $leads->source ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="purpose">Purpose</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-target-lock'></i></span>
                                    <input type="text" class="form-control" id="purpose" name="purpose"
                                        placeholder="e.g. Sales, Query" value="{{ $leads->purpose ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="values">Lead Value</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-rupee'></i></span>
                                    <input type="number" class="form-control" id="value" name="value"
                                        placeholder="Price/Value" value="{{ $leads->values ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="language">Language</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-world'></i></span>
                                    <input type="text" class="form-control" id="language" name="language"
                                        placeholder="EN/HI" value="{{ $leads->language ?? 'EN' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="poc">Point of Contact (POC)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-user-check'></i></span>
                                    <input type="text" class="form-control" id="poc" name="poc" placeholder="SPOK"
                                        value="{{ $leads->poc ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="tags">Keywords / Tags</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-purchase-tag-alt'></i></span>
                                    <input type="text" class="form-control" id="tags" name="tags"
                                        placeholder="e.g. K2, Hot, VIP" value="{{ $leads->tags ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Optional Next Action (New Leads Only) -->
                @if(empty($_GET['id']))
                    <div class="col-12">
                        <div class="form-card" style="border-left: 5px solid var(--color-default);">
                            <div class="section-title">
                                <i class='bx bx-calendar-event'></i> Next Action & Follow up
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="nxtDate">Reminder Date & Time</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-time'></i></span>
                                        <input type="datetime-local" class="form-control" id="nxtDate" name="nxtDate">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <label for="message">Closing Message / Note</label>
                                    <textarea class="form-control" rows="3" id="message" name="message"
                                        placeholder="Summary of the conversation..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Form Controls -->
                <div class="col-12 mt-2 mb-5">
                    <div class="d-flex align-items-center justify-content-end gap-3 p-3 bg-white rounded shadow-sm border">
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class='bx bx-reset me-1'></i> Reset Form
                        </button>
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                            <i class='bx bx-check-circle' style="font-size: 1.2rem;"></i>
                            @if(!empty($_GET['id'])) Update Lead Details @else Save New Lead @endif
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>


@endsection