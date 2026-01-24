@extends('layout')
@section('title','Customers - eseCRM')

@section('content')
    @php
    
        $roles = session('roles');
        $roleArray = explode(',',($roles->permissions ?? ''));
    
    @endphp
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i> 
            Customers
            <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>
        <div class="container-fluid">
            <div class="board-title board-title-flex">
                <h1>List Board</h1>
                @if(in_array('clients_add',$roleArray) || in_array('All',$roleArray))
                <div class="btn-group">
                    <!--<a href="javascript:void(0)" class="btn btn-warning btn-sm" id="importFile"><i class="bx bx-upload"></i> <span>Import</span></a>
                    <a href="/public/assets/leads.csv" class="btn btn-danger btn-sm" target="_blank" download="leads.csv" title="Download CSV Sample File"><i class="bx bx-download"></i> <span>Sample File</span></a>-->
                    <a href="/manage-client" class="btn btn-primary btn-sm"><i class="bx bx-plus"></i> <span>Add New</span></a>
                </div>
                @endif
            </div>
            <div class="row">
                <div class="col-md-12 py-3 table-responsive">
                    <table id="leadslists" class="table table-condensed m-table leads" style="width:100%;border-radius: 5px!important;overflow: hidden;">
                        <thead>
                            <tr>
                                <th class="checkbox-column pl-2"><input type="checkbox" id="checkall" value="all"></th>
                                <th>Name</th>
                                <th class="m-none">Company</th>
                                <th class="m-none" width="80px">Mobile No.</th>
                                <th class="m-none">Projects</th>
                                <th class="m-none" width="60px">Status</th>
                                <th class="position-sticky end-0" width="60px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clients as $client)
                            @php
                                $colorStatus = ($client->status == '5') ? 'table-success' : 
                                           (($client->status == '9') ? 'table-danger' : 
                                           (($client->status == '1' 
                                           && date('Y-m-d', strtotime($client->next_date)) == date('Y-m-d') 
                                           && date('His', strtotime($client->next_date)) < date('His')) ? 'table-danger bg-danger' :
                                           ($client->status == '1' ? 'table-warning' : 'table-white')));

                                $status = ($client->status == '5') ? "converted" : 
                                          (($client->status == '9') ? "Lose" :
                                          (($client->status == '1') ? "Follow Up" : "Fresh"));
                            @endphp
                            <!-- Add rows with your data here -->
                            <tr class="{{ $colorStatus ?? '' }} view selectrow" id="{{ $client->id ?? '' }}">
                                <td class="checkbox-column pr-0"><input type="checkbox" name="checkleads[]" class="checklead" value="{{ $client->id ?? '' }}"></td>
                                <td>{{ $client->name ?? '' }}<span class="small d-none">{{ $client->company ?? '' }}</span></td>
                                <td class="m-none">{{ substr(($client->company ?? ''),0,20).".." }}</td>
                                <td class="m-none" width="80px">{{ $client->mob ?? '' }}</td>
                                <td class="m-none"></td>
                                <td class="m-none" width="60px">{{ $status ?? '' }}</td>
                                <td class="position-sticky end-0" width="60px">
                                    <div class="table-btn m-none">
                                        @if(!empty($client->whatsapp))<a href="https://api.whatsapp.com/send/?phone={{ $client->whatsapp }}&text=Hi&type=phone_number&app_absent=0" class="btn btn-primary text-white btn-sm" title="whatsapp"><i class="bx bxl-whatsapp"></i></a>@endif
                                        @if(!empty($client->email))<a href="mailto:{{ $client->email }}" class="btn btn-info text-white btn-sm" title="Email"><i class="bx bx-envelope"></i></a>@endif
                                        @if(!empty($client->mob))<a href="tel:{{ $client->mob }}" class="btn btn-warning text-dark btn-sm" title="Call"><i class="bx bx-phone"></i></a>@endif
                                    </div>
                                    <div class="table-btn d-none">
                                        @if(!empty($client->whatsapp))<a href="https://api.whatsapp.com/send/?phone={{ $client->whatsapp }}&text=Hi&type=phone_number&app_absent=0" target="_blank" class="btn btn-primary text-white btn-sm" title="whatsapp"><i class="bx bxl-whatsapp"></i></a>@endif
                                        @if(!empty($client->email))<a href="mailto:{{ $client->email }}" class="btn btn-info text-white btn-sm" title="Email"><i class="bx bx-envelope"></i></a>@endif
                                        @if(!empty($client->mob))<a href="tel:{{ $client->mob }}" class="btn btn-warning text-dark btn-sm" title="Call"><i class="bx bx-phone"></i></a>@endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            <!-- Repeat rows as needed -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    
    <form id="leadsubmit" action="/import-leads-file" method="post" enctype="multipart/form-data">
        @csrf
        <input type="file" name="impLeadFile" id="impLeadFile" accept=".csv, .xls" style="display:none;" />
    </form>
    
    <!-- Modal Structure -->
    <div class="modal fade" id="leadModal" tabindex="-1" aria-labelledby="leadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="leadModalLabel">Lead Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Tab navigation with tabs and auto-adjusting width -->
                    <ul class="nav nav-tabs mb-3 nav-justified" id="leadModalTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="lead-details-tab" data-bs-toggle="pill" data-bs-target="#lead-details" type="button" role="tab" aria-controls="lead-details" aria-selected="true">Lead Details</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="comments-tab" data-bs-toggle="pill" data-bs-target="#comments" type="button" role="tab" aria-controls="comments" aria-selected="false">Conversations</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="new-comment-tab" data-bs-toggle="pill" data-bs-target="#new-comment" type="button" role="tab" aria-controls="new-comment" aria-selected="false">New Reminder</button>
                        </li>
                    </ul>
    
                    <!-- Tab content -->
                    <div class="tab-content" id="leadModalTabContent">
                        <!-- Lead Details Tab -->
                        <div class="tab-pane fade show active" id="lead-details" role="tabpanel" aria-labelledby="lead-details-tab">
                            <!--<div id="leadinfo" class="mt-3"></div>-->
                            <form action="manage-lead" method="post" class="row g-3">
                                @csrf
                                <div class="col-md-6 form-group">
                                    <label for="name">Name*</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-user'></i></span>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name*" required>
                                        <input type="hidden" id="id" name="id" value="{{ $_GET['id'] ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="email">Email Address*</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-envelope-open'></i></span>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email Id*" required>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="mobile">Mobile Number*</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-phone'></i></span>
                                        <input type="text" class="form-control" id="mob" name="mob" placeholder="Enter Mobile Number*" value="91" required>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="whatsapp">Whatsapp*</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bxl-whatsapp'></i></span>
                                        <input type="text" class="form-control" id="whatsapp" name="whatsapp" placeholder="Enter Whatsapp Number*" value="91" required>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="company">Company*</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-briefcase'></i></span>
                                        <input type="text" class="form-control" id="company" name="company" placeholder="Enter Company*" required>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="position">Position*</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-user'></i></span>
                                        <input type="text" class="form-control" id="position" name="position" placeholder="Enter Position*" required>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="industry">Industry*</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-building'></i></span>
                                        <input type="text" class="form-control" id="industry" name="industry" placeholder="Enter Industry*" required>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="address">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-home'></i></span>
                                        <input type="text" class="form-control" id="address" name="address[]" placeholder="Enter Address">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="city">City</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-map'></i></span>
                                        <input type="text" class="form-control" id="city" name="address[]" placeholder="Enter City">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="state">State</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-map-pin'></i></span>
                                        <input type="text" class="form-control" id="state" name="address[]" placeholder="Enter State">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="country">Country</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-globe'></i></span>
                                        <input type="text" class="form-control" id="country" name="address[]" placeholder="Enter Country">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="website">Website</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-link'></i></span>
                                        <input type="url" class="form-control" id="website" name="website" placeholder="Enter Website Link">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="source">Assigned</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-share-alt'></i></span>
                                        <input type="text" class="form-control" id="source" name="source" placeholder="Enter Source">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="source">Purpose*</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-target-lock'></i></span>
                                        <input type="text" class="form-control" id="purpose" name="purpose" placeholder="Enter Purpose*" required>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="values">Lead Value</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-rupee'></i></span>
                                        <input type="number" class="form-control" id="value" name="value" placeholder="Enter Values">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="source">Language</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-world'></i></span>
                                        <input type="text" class="form-control" id="language" name="language" placeholder="Enter Language">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="source">POC</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-user-check'></i></span>
                                        <input type="text" class="form-control" id="poc" name="poc" placeholder="Enter Point of Contact">
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Status:</label><br>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-list-check'></i></span>
                                        <select class="form-control" id="status" name="status"></select>
                                    </div>
                                </div>
                                @if(in_array('leads_edit',$roleArray) || in_array('All',$roleArray))
                                <div class="form-group col-md-12 text-center mt-3">
                                    <button type="submit" class="btn btn-primary px-4">Save</button>
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
                                    <input type="hidden" name="lead_id" id="commentLeadId">
                                    <div class="form-group">
                                        <label for="message" class="form-label">Message*:</label>
                                        <textarea class="form-control" rows="5" id="message" name="message" placeholder="Write Here..." required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="nxtDate" class="form-label">Next Date*:</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-calendar'></i></span>
                                            <input type="datetime-local" class="form-control" min="{{today()}}" id="nxtDate" name="nxtDate" required>
                                        </div>
                                    </div>
                                    <div class="form-group text-center pt-2">
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                        <button type="reset" class="btn btn-light border">Reset</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            $('#checkall').click(function() {
                // Check if the .checkall checkbox is checked
                let isChecked = $(this).prop('checked');
                
                // Set all .checklead checkboxes to the same state
                $('.checklead').prop('checked', isChecked);
            });
            
            $('.selectrow').click(function() {
                // Check if the checkbox inside the current .selectrow is checked or unchecked
                if ($(this).find('.checklead').prop('checked')) {
                    // Add 'selected' class to the parent row if the checkbox is checked
                    $(this).closest('tr').addClass('selected');
                } else {
                    // Remove 'selected' class from the parent row if unchecked
                    $(this).closest('tr').removeClass('selected');
                }
            });

            $('#importFile').click(function() {
                $("#impLeadFile").trigger("click");
            });
            
            // Submit the form when a file is selected
            $('#impLeadFile').change(function() {
                // Submit the form automatically after file selection
                $('#leadsubmit').submit();
            });

            $('.view').dblclick(function(){
                let id = $(this).attr('id');
                let pagename = "leads";
                
                $('#commentLeadId').val(id);
                
                function formatDate(dateString) {
                    const options = { year: 'numeric', month: 'long', day: 'numeric' };
                    const date = new Date(dateString);
                    return date.toLocaleDateString(undefined, options);
                }
        
                $.ajax({
                    url: '/view-single-lead', // Replace with your server endpoint URL
                    type: 'GET', // You can use 'GET' or 'POST' depending on your requirement
                    data: {
                        id: id,
                        pagename: pagename
                    },
                    success: function(response){
                        let purpose;
                        // Parsing the JSON data
                        var parsedData = JSON.parse(response);
                        let lead = parsedData.leads;
                        let leadComments = parsedData.leadComments;
                        let location = lead.location || '';
                        let locationParts = location.split(',');
                        
                        let address = locationParts[0] ? locationParts[0].trim() : '';
                        let city = locationParts[1] ? locationParts[1].trim() : '';
                        let state = locationParts[2] ? locationParts[2].trim() : '';
                        let country = locationParts[3] ? locationParts[3].trim() : '';
    
                        if (parsedData.leads.purpose == '' || parsedData.leads.purpose != null) {
                            purpose = parsedData.leads.purpose; // Assign value if not null
                        } else {
                            purpose = "Customer Feedback"; // Default value if null
                        }
                        
                        let purposeHtml = `
                            <div class="date-style">
                                ${purpose}
                            </div>
                        `;
    
                        let commentsHtml = leadComments.map(comment => `
                            <div class="cmt-details">
                                <p>${comment.msg}</p>
                                <div class="mfooter">
                                    <p><strong>Last Talk:</strong> ${formatDate(comment.updated_at)}</p>
                                    <p><strong>Next Date:</strong> ${formatDate(comment.next_date)}</p>
                                </div>
                            </div>
                        `).join('');
    
                        // Injecting HTML content into the modal
                        $('#id').val(lead.id);
                        $('#name').val(lead.name);
                        $('#email').val(lead.email);
                        $('#mob').val(lead.mob);
                        $('#whatsapp').val(lead.whatsapp);
                        $('#company').val(lead.company);
                        $('#position').val(lead.position);
                        $('#industry').val(lead.industry);
                        $('#address').val(address);
                        $('#city').val(city);
                        $('#state').val(state);
                        $('#country').val(country);
                        $('#website').val(lead.website);
                        $('#source').val(lead.source);
                        $('#purpose').val(lead.purpose);
                        $('#value').val(lead.values);
                        $('#language').val(lead.language);
                        $('#poc').val(lead.poc);
                        
                        var status = lead.status;

                        let option = `
                            <option value="0" ${status == '0' ? 'selected' : ''}>Fresh</option>
                            <option value="1" ${status == '1' ? 'selected' : ''}>Follow Up</option>
                            <option value="5" ${status == '5' ? 'selected' : ''}>Converted</option>
                            <option value="9" ${status == '9' ? 'selected' : ''}>Loss</option>
                        `;
                        
                        $('#status').html(option);
                        
                        $('#leadcomments').html(purposeHtml + commentsHtml);
                        
                        let lastNextDate = leadComments.reduce((latest, comment) => {
                            return (new Date(comment.next_date) > new Date(latest)) ? comment.next_date : latest;
                        }, leadComments[0]?.next_date || null);
    
                        $("#nxtDate").attr("min",lastNextDate);
    
                        // Show the modal
                        $('#leadModal').modal('show');
                    },
                    error: function(xhr, status, error){
                        // Handle errors here
                        console.log('Error:', error);
                    }
                });
            });
        });

    </script>

@endsection