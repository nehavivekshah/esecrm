@extends('layout')
@section('title','Manage User - eseCRM')

@section('content')

    @php
    
        $sessionroles = session('roles');
        $roleArray = explode(',',($sessionroles->permissions ?? ''));
        $userAssign = explode(',',($users->assign ?? ''));
        $userFeaturs = explode(',',($users->features ?? ''));
        
    @endphp
    
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i> 
            @if(Request::segment(1) != 'my-profile') Manage User @else My Account @endif
            <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>
        <div class="container-fluid">
            <div class="board-title board-title-flex mb-2">
                @if(Request::segment(1) != 'my-profile')
                
                <a href="users" class="btn btn-primary btn-sm back-btn"><i class="bx bx-arrow-back"></i></a>
                
                @if(!empty($_GET['id'])) <h1>Edit User</h1> @else <h1>Add New User</h1> @endif
                
                @else
                
                <h1>Edit Details</h1>
                
                @endif
            </div>

            <div class="row g-3 px-2">
                <div class="col-md-12 bg-white py-3 px-4 rounded">
                    <form action="manage-user" method="post" class="row" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="role">Profile Photo</label>
                            <div class="input-group">
                                @if(!empty($users->photo))
                                <span class="input-group-text" style="padding:5px;"><img src="/public/assets/images/profile/{{$users->photo ?? ''}}" style="width:30px;height:26px;object-fit:contain;border-radius:30px" /></span>
                                @else
                                <span class="input-group-text"><i class='bx bx-image'></i></span>
                                @endif
                                <input type="file" class="form-control" id="profilePhoto" name="profilePhoto">
                            </div>
                        </div>
                        
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="role">Signature</label>
                            <div class="input-group">
                                @if(!empty($users->imgsign))
                                <span class="input-group-text" style="padding:5px;"><img src="/public/assets/images/signs/{{$users->imgsign ?? ''}}" style="width:30px;height:26px;object-fit:contain;border-radius:30px" /></span>
                                @else
                                <span class="input-group-text"><i class='bx bx-image'></i></span>
                                @endif
                                <input type="file" class="form-control" id="imgsign" name="imgsign">
                            </div>
                        </div>
                        
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="name">Name*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-user'></i></span>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name*" value="{{ $users->name ?? '' }}" required>
                            </div>
                            @if(Request::segment(1) == 'my-profile')
                            <input type="hidden" name="id" value="{{ Auth::user()->id ?? '' }}">
                            @else
                            <input type="hidden" name="id" value="{{ $_GET['id'] ?? '' }}">
                            @endif
                        </div>
                        
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="role">Email Id</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-user'></i></span>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email id" value="{{ $users->email ?? '' }}">
                            </div>
                        </div>
                        
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="role">Mobile No.</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-user'></i></span>
                                <input type="text" class="form-control" id="mob" name="mob" placeholder="Enter Mobile No." value="{{ $users->mob ?? '' }}">
                            </div>
                        </div>
                        
                        @if(Request::segment(1) != 'my-profile')
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="role">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-user'></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password" value="">
                            </div>
                        </div>
                        @endif
                        
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="role">Role*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-user'></i></span>
                                <select class="form-control" id="role" name="role" @if(($users->roleFeatures ?? '') == 'All') style="pointer-events:none;" @else required @endif>
                                    @if(($users->roleFeatures ?? '') == 'All')
                                    <option value="">{{$users->title ?? ''}}</option>
                                    @endif
                                    @foreach($roles as $role)
                                    <option value="{{$role->id ?? ''}}" @if(($users->role ?? '') == $role->id) selected @endif>{{$role->title ?? ''}} - {{$role->subtitle ?? ''}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        @if(in_array('users_assign',$roleArray) || in_array('All',$roleArray))
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="role">Assigned</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-user'></i></span>
                                <select class="selectpicker form-select" multiple data-live-search="true" name=assign[]">
                                    @foreach($allusers as $user)
                                    <option value="{{$user->id ?? ''}}" @if(in_array(($user->id ?? ''),$userAssign)) selected @endif>{{$user->name ?? ''}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="role">Access</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-user'></i></span>
                                <select class="selectpicker form-select" multiple data-live-search="true" name="features[]">
                                    <option value="tasks" @if(in_array('tasks',$userFeaturs)) selected @endif>Tasks</option>
                                    <option value="leads" @if(in_array('leads',$userFeaturs)) selected @endif>Leads</option>
                                    <option value="customers" @if(in_array('customers',$userFeaturs)) selected @endif>Customers</option>
                                </select>
                            </div>
                        </div>
                        @endif
                        
                        @php 
                            $workingTime = json_decode($user->working_times ?? '', true) ?? [];
                        @endphp
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="role">Working Start Time</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-time'></i></span>
                                <input type="time" name="time[start]" class="form-control" value="{{ $workingTime[0] ?? '10:00' }}" @if(in_array('users_assign',$roleArray) || in_array('All',$roleArray)) @else disabled @endif>
                            </div>
                        </div>
                        
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="role">Working End Time</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-time'></i></span>
                                <input type="time" name="time[end]" class="form-control" value="{{ $workingTime[1] ?? '18:00' }}" @if(in_array('users_assign',$roleArray) || in_array('All',$roleArray)) @else disabled @endif>
                            </div>
                        </div>
                        
                        <div class="form-group col-md-3 col-sm-6">
                            <label for="role">Status*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class='bx bx-user'></i></span>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="1" @if(($users->status ?? '') == '1') selected @endif>Active</option>
                                    <option value="2" @if(($users->status ?? '') == '2') selected @endif>Deactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group col-md-12 col-sm-12 mb-3">
                            <label for="role">Email Signature</label>
                            <textarea type="text" class="form-control mt-1" id="emailSign" rows="4" name="emailSign" placeholder="Write Here...">{{$users->esign ?? ''}}</textarea>
                        </div>
                        
                        <div class="form-group text-right col-md-12">
                            <button type="submit" class="btn btn-primary px-4">Submit</button>
                            <button type="reset" class="btn btn-light border px-4">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    
@endsection