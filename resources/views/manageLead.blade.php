@extends('layout')
@section('title','Leads - eseCRM')

@section('content')

    @php
    
    $location = explode('=>',($leads->location ?? ''));
    
    @endphp
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i> 
            Leads
            <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>
        <div class="container-fluid">
            <div class="board-title board-title-flex mb-4">
                <a href="/leads" class="btn btn-primary btn-sm back-btn"><i class="bx bx-arrow-back"></i></a>
                @if(!empty($_GET['id'])) <h1>Edit Lead Details</h1> @else <h1>Add New Lead</h1> @endif
                <!--<a href="/manage-lead" class="btn btn-primary btn-sm">Add New</a>-->
            </div>

            <div class="row">
                <div class="col-md-12 csp-3">
                    <form action="manage-lead" method="post" class="row g-3 bg-white p-3 rounded">
                        @csrf
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="name">Name*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-user'></i></span>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name*" value="{{ $leads->name ?? '' }}" required>
                                <input type="hidden" name="id" value="{{ $_GET['id'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="email">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-envelope-open'></i></span>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email Id" value="{{ $leads->email ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="mobile">Mobile Number*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-phone'></i></span>
                                <input type="text" class="form-control" id="mob" name="mob" placeholder="Enter Mobile Number*" value="{{ $leads->mob ?? '91' }}" required>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="whatsapp">Whatsapp</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bxl-whatsapp'></i></span>
                                <input type="text" class="form-control" id="whatsapp" name="whatsapp" placeholder="Enter Whatsapp Number" value="{{ $leads->whatsapp ?? '91' }}">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="company">Company</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-briefcase'></i></span>
                                <input type="text" class="form-control" id="company" name="company" placeholder="Enter Company" value="{{ $leads->company ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="position">Position</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-user'></i></span>
                                <input type="text" class="form-control" id="position" name="position" placeholder="Enter Position" value="{{ $leads->position ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="industry">Industry</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-building'></i></span>
                                <input type="text" class="form-control" id="industry" name="industry" placeholder="Enter Industry" value="{{ $leads->industry ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="address">Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-home'></i></span>
                                <input type="text" class="form-control" id="address" name="address[]" placeholder="Enter Address" value="{{ $location[0] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="city">City</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-map'></i></span>
                                <input type="text" class="form-control" id="city" name="address[]" placeholder="Enter City" value="{{ $location[1] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="state">State</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-map-pin'></i></span>
                                <input type="text" class="form-control" id="state" name="address[]" placeholder="Enter State" value="{{ $location[2] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="country">Country</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-globe'></i></span>
                                <input type="text" class="form-control" id="country" name="address[]" placeholder="Enter Country" value="{{ $location[3] ?? 'India' }}">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="zip">Zip/Postal Code</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-pin'></i></span>
                                <input type="text" class="form-control" id="clientZip" name="address[]" placeholder="Enter Zip/Postal Code" value="{{ $location[4] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="website">Website</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-link'></i></span>
                                <input type="url" class="form-control" id="country" name="website" placeholder="Enter Website Link" value="{{ $leads->website ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="source">Assigned</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-share-alt'></i></span>
                                <input type="text" class="form-control" id="source" name="source" placeholder="Enter Source" value="{{ $leads->source ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="source">Purpose</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-target-lock'></i></span>
                                <input type="text" class="form-control" id="purpose" name="purpose" placeholder="Enter Purpose" value="{{ $leads->purpose ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="values">Lead Value</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-rupee'></i></span>
                                <input type="number" class="form-control" id="value" name="value" placeholder="Enter Values" value="{{ $leads->values ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="source">Language</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-world'></i></span>
                                <input type="text" class="form-control" id="language" name="language" placeholder="Enter Language" value="{{ $leads->language ?? 'EN' }}">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="source">POC</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-user-check'></i></span>
                                <input type="text" class="form-control" id="poc" name="poc" placeholder="Enter Point of Contact" value="{{ $leads->poc ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="source">Tags</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-user-check'></i></span>
                                <input type="text" class="form-control" id="tags" name="tags" placeholder="Enter Tags (Search Keywords, K2)" value="{{ $leads->tags ?? '' }}">
                            </div>
                        </div>

                        @if(empty($_GET['id']))
                        <div class="col-md-3 col-sm-6 form-group">
                            <label for="source">Next Date</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-calendar'></i></span>
                                <input type="datetime-local" class="form-control" id="nxtDate" name="nxtDate">
                            </div>
                        </div>
                        <div class="col-md-12 form-group">
                            <div class="form-group">
                                <label for="message">Message</label>
                                <textarea class="form-control" rows="5" id="message" name="message" placeholder="Write Here..."></textarea>
                            </div>
                        </div>
                        @endif
                        
                        <div class="col-md-12 text-right mb-2">
                            <button type="submit" class="btn btn-primary px-4">Submit</button>
                            <button type="reset" class="btn btn-outline-secondary border px-4">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    
@endsection