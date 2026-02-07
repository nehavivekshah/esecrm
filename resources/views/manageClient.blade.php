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
                        <div class="col-md-12 mt-4">
                            <h5 class="mb-3">Departments / Branches</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="departmentTable">
                                    <thead>
                                        <tr>
                                            <th>Name*</th>
                                            <th>GST No.</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Address</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Existing Departments -->
                                        @if(isset($clients) && $clients->departments->count() > 0)
                                            @foreach($clients->departments as $index => $dept)
                                                <tr>
                                                    <td><input type="text" name="departments[{{$index}}][name]" class="form-control"
                                                            value="{{ $dept->name }}" required>
                                                        <input type="hidden" name="departments[{{$index}}][id]"
                                                            value="{{ $dept->id }}">
                                                    </td>
                                                    <td><input type="text" name="departments[{{$index}}][gst_no]"
                                                            class="form-control" value="{{ $dept->gst_no }}"></td>
                                                    <td><input type="email" name="departments[{{$index}}][email]"
                                                            class="form-control" value="{{ $dept->email }}"></td>
                                                    <td><input type="text" name="departments[{{$index}}][phone]"
                                                            class="form-control" value="{{ $dept->phone }}"></td>
                                                    <td><textarea name="departments[{{$index}}][address]" class="form-control"
                                                            rows="1">{{ $dept->address }}</textarea></td>
                                                    <td><button type="button" class="btn btn-danger btn-sm remove-dept"><i
                                                                class="bx bx-trash"></i></button></td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <!-- Default Empty Row -->
                                            <tr>
                                                <td><input type="text" name="departments[0][name]" class="form-control"
                                                        placeholder="Main Branch" required></td>
                                                <td><input type="text" name="departments[0][gst_no]" class="form-control"
                                                        placeholder="GST"></td>
                                                <td><input type="email" name="departments[0][email]" class="form-control"
                                                        placeholder="Email"></td>
                                                <td><input type="text" name="departments[0][phone]" class="form-control"
                                                        placeholder="Phone"></td>
                                                <td><textarea name="departments[0][address]" class="form-control" rows="1"
                                                        placeholder="Address"></textarea></td>
                                                <td><button type="button" class="btn btn-danger btn-sm remove-dept"><i
                                                            class="bx bx-trash"></i></button></td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-success btn-sm" id="addDeptRow"><i
                                        class="bx bx-plus"></i> Add Department</button>
                            </div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                let deptIndex = {{ isset($clients) ? $clients->departments->count() : 1 }};
                                if (deptIndex === 0) deptIndex = 1;

                                document.getElementById('addDeptRow').addEventListener('click', function () {
                                    const tableBody = document.querySelector('#departmentTable tbody');
                                    const newRow = `
                                            <tr>
                                                <td><input type="text" name="departments[${deptIndex}][name]" class="form-control" required></td>
                                                <td><input type="text" name="departments[${deptIndex}][gst_no]" class="form-control"></td>
                                                <td><input type="email" name="departments[${deptIndex}][email]" class="form-control"></td>
                                                <td><input type="text" name="departments[${deptIndex}][phone]" class="form-control"></td>
                                                <td><textarea name="departments[${deptIndex}][address]" class="form-control" rows="1"></textarea></td>
                                                <td><button type="button" class="btn btn-danger btn-sm remove-dept"><i class="bx bx-trash"></i></button></td>
                                            </tr>
                                        `;
                                    tableBody.insertAdjacentHTML('beforeend', newRow);
                                    deptIndex++;
                                });

                                document.querySelector('#departmentTable').addEventListener('click', function (e) {
                                    if (e.target.closest('.remove-dept')) {
                                        e.target.closest('tr').remove();
                                    }
                                });
                            });
                        </script>

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