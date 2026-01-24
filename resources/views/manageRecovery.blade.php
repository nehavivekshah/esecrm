@extends('layout')
@section('title','Manage Recovery - eseCRM')

@section('content')

    @php
    
        $sessionroles = session('roles');
        $roleArray = explode(',',($sessionroles->permissions ?? ''));
        $userAssign = explode(',',($recoveries->assign ?? ''));
        $userFeaturs = explode(',',($recoveries->features ?? ''));
        
    @endphp
    
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i> 
            Manage Recovery
            <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>
        <div class="container-fluid">
            <div class="board-title board-title-flex mb-2">
                <a href="recoveries" class="btn btn-primary btn-sm back-btn"><i class="bx bx-arrow-back"></i></a>
                @if(!empty($_GET['id'])) 
                    <h1>Edit Details</h1> 
                @else 
                    <h1>Add New Details</h1> 
                @endif
            </div>

            <div class="row g-3 px-2">
                <div class="col-md-12 bg-white py-3 px-4 rounded">
                    <form action="manage-recovery" method="post" class="row" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="customer">Customers*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-list-ul'></i></span>
                                <select class="selectpicker form-select" name="clientId" id="clientId" data-live-search="true" title="Select a customer..." required>
                                    <option value="new">+ New Customer</option>
                                    <option data-divider="true"></option>
                                    @foreach($clients as $client)
                                    <option value="{{ $client->id ?? '' }}" @if($client->id == ($recoveries->client_id ?? '')) selected @endif>{{ $client->name ?? '' }} - {{ $client->company ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="project">Projects*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-list-ul'></i></span>
                                <select class="selectpicker form-select" name="projectId" id="projectId" data-live-search="true" title="Select a project..." required>
                                    <option value="new">+ New Project</option>
                                    @if(!empty($_GET['id']))
                                    <option data-divider="true"></option>
                                    @foreach($projects as $project)
                                    <option value="{{ $project->id ?? '' }}" @if($project->id == ($recoveries->project_id ?? '')) selected @endif>{{ $project->name ?? '' }} - {{ $project->amount ?? '' }}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="batch">Batch No.*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-barcode'></i></span>
                                <input type="text" class="form-control" id="btno" name="btno" placeholder="Enter Batch No.*" value="{{ $recoveries->batchNo ?? '' }}" required>
                                <input type="hidden" class="form-control" name="id" value="{{ $_GET['id'] ?? '' }}">
                            </div>
                        </div>
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="name">Client's Name*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-user'></i></span>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Client's Name*" value="{{ $recoveries->name ?? '' }}" required>
                            </div>
                        </div>
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="company">Company*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-building'></i></span>
                                <input type="text" class="form-control" id="company" name="company" placeholder="Enter Company*" value="{{ $recoveries->company ?? '' }}" required>
                            </div>
                        </div>
                        <div class="form-group col-md-3 col-sm-6 @if(empty($recoveries->project)) none @endif" id="pDiv" style="display:none;">
                            <label for="Amount">Project Name*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-box'></i></span>
                                <input type="text" class="form-control" id="project" name="project" placeholder="Enter Project Name*" value="{{ $recoveries->project ?? '' }}">
                            </div>
                        </div>
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="Amount">Amount*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-rupee'></i></span>
                                <input type="number" class="form-control" id="amount" name="amount" placeholder="Enter Amount*" value="{{ $recoveries->amount ?? '' }}" required>
                            </div>
                        </div>
                        @if(empty($_GET['id']))
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="Amount">Received Amount</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-rupee'></i></span>
                                <input type="number" class="form-control" id="received" name="received" placeholder="Enter Received Amount" value="{{ $recoveries->paid ?? '0' }}">
                            </div>
                        </div>
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="reminder">Reminder</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-alarm"></i></span>
                                <input type="datetime-local" class="form-control" id="reminder" name="reminder" placeholder="Enter Reminder" value="{{ $recoveries->reminder ?? '' }}">
                            </div>
                        </div>
                        @endif
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="mob">Mobile No.*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-mobile-alt'></i></span>
                                <input type="phone" class="form-control" id="phone" name="phone" placeholder="Enter Mobile No." value="{{ $recoveries->mob ?? '91' }}" required>
                            </div>
                        </div>
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="Whatsapp">Whatsapp No.*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bxl-whatsapp'></i></span>
                                <input type="phone" class="form-control" id="whatsapp" name="whatsapp" placeholder="Enter Whatsapp No." value="{{ $recoveries->whatsapp ?? '91' }}" required>
                            </div>
                        </div>
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="email">Email Id</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-envelope'></i></span>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email id" value="{{ $recoveries->email ?? '' }}">
                            </div>
                        </div>
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="Executive">Executive</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-id-card'></i></span>
                                <input type="text" class="form-control" id="executive" name="executive" placeholder="Enter Executive" value="{{ $recoveries->poc ?? '' }}">
                            </div>
                        </div>
                        <div class="form-group col-md-3 col-sm-6 @if(empty($recoveries->industry)) none @endif">
                            <label for="Industry">Industry & Segment</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-category-alt'></i></span>
                                <input type="text" class="form-control" id="industry" name="industry" placeholder="Enter Industry" value="{{ $recoveries->industry ?? '' }}">
                            </div>
                        </div>
                        <!--<div class="form-group col-md-3 col-sm-6 @if(empty($recoveries->project)) none @endif">
                            <label for="Product">Product</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-box'></i></span>
                                <input type="text" class="form-control" id="product" name="product" placeholder="Enter Product" value="{{ $recoveries->project ?? '' }}">
                            </div>
                        </div>-->
                        <div class="form-group col-md-3 col-sm-6 @if(empty($recoveries->website)) none @endif">
                            <label for="website">Website</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-globe'></i></span>
                                <input type="url" class="form-control" id="website" name="website" placeholder="Enter Website" value="{{ $recoveries->website ?? '' }}">
                            </div>
                        </div>
                        <div class="form-group col-md-6 @if(empty($recoveries->msg)) none @endif">
                            <label for="note">Note</label>
                            <div class="input-group">
                                <textarea type="text" class="form-control" id="note" name="note" placeholder="Note">{{ $recoveries->msg ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group mt-2 mb-0">
                            <a href="javascript:void(0)" class="more">Show Advance Mode</a>
                        </div>
                        <div class="form-group col-md-12 text-right">
                            <button type="submit" name="submit" class="btn btn-primary border px-4">Submit</button>
                            <button type="reset" class="btn btn-light border px-4">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    
@endsection
