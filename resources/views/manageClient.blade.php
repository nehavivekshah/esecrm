@extends('layout')
@section('title', 'Customers - eseCRM')

@section('content')

    @php $location = explode(',', ($clients->location ?? '')); @endphp
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i>
            Customers
            <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>
        <div class="container-fluid">
            <div class="board-title board-title-flex mb-4">
                <a href="/clients" class="btn btn-primary btn-sm back-btn"><i class="bx bx-arrow-back"></i></a>
                @if(!empty($_GET['id']))
                <h1>Edit Customer Details</h1> @else <h1>Add New Customer</h1> @endif
                <!--<a href="/manage-lead" class="btn btn-primary btn-sm">Add New</a>-->
            </div>

            <div class="row">
                <div class="col-md-12 csp-3">
                    <form action="manage-client" method="post" class="row g-3 bg-white p-3">
                        @csrf
                        <div class="col-md-6 form-group">
                            <label for="name">Name*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-user'></i></span>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name*"
                                    value="{{ $clients->name ?? '' }}" required>
                                <input type="hidden" name="id" value="{{ $_GET['id'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="email">Email Address*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-envelope-open'></i></span>
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="Enter Email Id*" value="{{ $clients->email ?? '' }}" required>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="mobile">Mobile Number*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-phone'></i></span>
                                <input type="text" class="form-control" id="mob" name="mob"
                                    placeholder="Enter Mobile Number*" value="{{ $clients->mob ?? '91' }}" required>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="mobile">Alternative Mobile Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-phone'></i></span>
                                <input type="text" class="form-control" id="alterMob" name="alterMob"
                                    placeholder="Enter Mobile Number" value="{{ $clients->alterMob ?? '91' }}">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="whatsapp">Whatsapp</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bxl-whatsapp'></i></span>
                                <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                                    placeholder="Enter Whatsapp Number" value="{{ $clients->whatsapp ?? '91' }}">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="company">Company*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-briefcase'></i></span>
                                <input type="text" class="form-control" id="company" name="company"
                                    placeholder="Enter Company*" value="{{ $clients->company ?? '' }}" required>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="gst">GST No.</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-link'></i></span>
                                <input type="text" class="form-control" id="gst" name="gst" placeholder="Enter GST No."
                                    value="{{ $clients->gstno ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="position">Position</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-user'></i></span>
                                <input type="text" class="form-control" id="position" name="position"
                                    placeholder="Enter Position" value="{{ $clients->position ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="industry">Industry</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-building'></i></span>
                                <input type="text" class="form-control" id="industry" name="industry"
                                    placeholder="Enter Industry" value="{{ $clients->industry ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="address">Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-home'></i></span>
                                <input type="text" class="form-control" id="address" name="address[address]"
                                    placeholder="Enter Address" value="{{ $location[0] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="city">City</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-map'></i></span>
                                <input type="text" class="form-control" id="city" name="address[city]"
                                    placeholder="Enter City" value="{{ $location[1] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="state">State</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-map-pin'></i></span>
                                <input type="text" class="form-control" id="state" name="address[state]"
                                    placeholder="Enter State" value="{{ $location[2] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="country">Country</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-globe'></i></span>
                                <input type="text" class="form-control" id="country" name="address[country]"
                                    placeholder="Enter Country" value="{{ $location[3] ?? 'India' }}">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="zip">Zip/Postal Code</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-pin'></i></span>
                                <input type="text" class="form-control" id="m_zip" name="address[zip]"
                                    placeholder="Enter Zip/Postal Code">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="website">Website</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-link'></i></span>
                                <input type="url" class="form-control" id="country" name="website"
                                    placeholder="Enter Website Link" value="{{ $clients->website ?? '' }}">
                            </div>
                        </div>

                        <div class="col-md-12 text-center mt-4">
                            <button type="submit" class="btn btn-primary px-4">Submit</button>
                            <button type="reset" class="btn btn-outline-secondary border px-4">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection