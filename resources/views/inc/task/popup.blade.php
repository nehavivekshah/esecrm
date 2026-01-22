@php

    $roles = session('roles');
    $roleArray = explode(',',($roles->permissions ?? ''));

@endphp
<section class="task_pop_section" id="task_pop_section"><!-- style="display:none;"-->
    <!--<div class="text">Task</div>-->
    <div class="pop-scrum-board-container">
        <div class="pop-scrum-board">
            
            <!--Header Content-->
            <div class="pop-flex">
                <div class="listicon">
                    <i class="bx bx-window-alt"></i>
                </div>
                <div class="headcontent">
                    <textarea type="text" id="tasktitle">{{ ucfirst($taskSingle[0]->title) }}</textarea>
                    <!--<h5 class="text-second">in list {{ $userSingle[0]->name }}</h5>-->
                </div>
                <div class="listicon">
                    <a href="{{ route('task') }}" class="close-pop"  onclick="closepop()"><i class="bx bx-x"></i></a>
                </div>
            </div>
            
            <!--Body Content-->
            <div class="pop-body-flex">
                <div class="pagesidebar">
                    <h4 class="pop-title h4 text-default font-weight-bold"><i class="bx bx-list-minus"></i> <span>Actions</span></h4>
                    <ul class="tab-list">
                        @if(!empty($taskHistory[0]->id) && $taskHistory[0]->status == '0')
                        <?php $workingHours = (strtotime(date('d-m-Y h:i:s a')) - strtotime($taskHistory[0]->start_time)) / 60; ?>
                        <li><a href="javascript:void(0)" class="taskstart" data-taskhr="{{ round($workingHours,2) }}" 
                        id="{{ $taskHistory[0]->id }}"><i class="bx bx-time"></i> <span class="p-0">Stop</span></a></li>
                        @else
                        <li><a href="javascript:void(0)" class="taskstart" id="{{ $taskSingle[0]->id }}"><i class="bx bx-stopwatch"></i> <span class="p-0">Start</span></a></li>
                        @endif
                        <li>
                          <div class="input-group d-flex">
                            <i class="bx bxs-label" id="labelicon" style="color:{{ $taskSingle[0]->label }}"></i>
                            <select id="colorpalet">
                              <option value="">Label</option>
                              <option value="#787878" @if(($taskSingle[0]->label ?? '') == "#787878") {{ "selected" }} @endif>New Task</option>
                              <option value="#007265" @if(($taskSingle[0]->label ?? '') == "#007265") {{ "selected" }} @endif>In Working</option>
                              <option value="#ff9800" @if(($taskSingle[0]->label ?? '') == "#ff9800") {{ "selected" }} @endif>Pause</option>
                              <option value="#e91e1e" @if(($taskSingle[0]->label ?? '') == "#e91e1e") {{ "selected" }} @endif>Urgent</option>
                              <option value="#0dd500" @if(($taskSingle[0]->label ?? '') == "#0dd500") {{ "selected" }} @endif>Complete</option>
                            </select>
                          </div>
                        </li>
                        <!--li><a href="javascript:void(0)"><i class="bx bxs-copy"></i> <span>Copy</span></a></li>
                        <li><a href="javascript:void(0)"><i class="bx bxs-arrow-from-left"></i> <span>Move</span></a></li>
                        <li><a href="javascript:void(0)"><i class="bx bxs-box"></i> <span>Acrive</span></a></li>
                        <li><a href="javascript:void(0)"><i class="bx bxs-share"></i> <span>Share</span></a></li-->
                        @if(in_array('tasks_delete',$roleArray) || in_array('All',$roleArray))
                        <li><a href="javascript:void(0)" class="taskdeleted text-danger" id="{{ $taskSingle[0]->id }}"><i class="bx bxs-trash"></i> <span>Delete</span></a></li>
                        @endif
                    </ul>
                  	
                  	@if(count($taskHistory)>0)
                  	<h5 class="text-default sb-title">Durations</h5>
                    <ul class="tab-time-list">
                      @php
                      
                      	$total_min = 0;
                      
                      @endphp
                      
                      @foreach($taskHistory as $taskTime)
                      
                      @php
                      
                   		$dateDiff = intval((strtotime($taskTime->start_time ?? '')-strtotime($taskTime->end_time ?? ''))/60);

                        $hours = intval($dateDiff/60);
                        $minutes = $dateDiff%60;
                      
                      	$total_min += intval($dateDiff/60)*60 + $dateDiff%60;
                      
                      @endphp
                      
                      <li>{{ date_format(date_create($taskTime->created_at),'d M') }} <span>{{ -$hours.'.'.-$minutes }}Hr.</span></li>
                      @endforeach
                  	</ul>
                  
                  	@php

                        $hours1 = intval($total_min/60);
                        $minutes1 = $total_min%60;
                      
                      @endphp
                  
                 	 <h5 class="text-default sb-title mt-2">Total: <span>{{ -$hours1.'Hr. '.-$minutes1.'Min.' }} </span></h5>
                  	@endif
                </div>
                <div class="pagecontent">
                    <div class="des-area">
                        <h3 class="pop-title h4 text-default"><i class="bx bx-list-minus"></i> <span>Description</span></h4>
                        <form class="edit-textarea" id="edttaskdetails" method="post">
                            @csrf
                            <div class="form-group">
                                <input type="hidden" name="taskid" id="taskid" value="{{ $taskSingle[0]->id }}" />
                                <textarea type="text" name="taskdes" rows="6" class="form-control" id="example" placeholder="Add a more detailed description…" required>{{ ucfirst($taskSingle[0]->des) }}</textarea>
                            </div>
                            @if(in_array('tasks_edit',$roleArray) || in_array('All',$roleArray))
                            <div class="form-group">
                                <button type="submit" name="tasksubmit" class="btn btn-primary btn-sm">Save</button>
                                <button type="reset" class="btn btn-light btn-sm border">Cancel</button>
                                <span id="res"></span>
                            </div>
                            @endif
                        </form>
                    </div>
                    <br>
                    <div class="activity-area">
                        <h3 class="pop-title h4 text-default"><i class="bx bx-list-minus"></i> <span>Comments</span></h4>
                        <form class="edit-textarea" method="post" id="taskComments">
                            @csrf
                            <div class="form-group">
                                <input type="hidden" name="commenttaskid" id="taskid" value="{{ $taskSingle[0]->id }}" />
                                <textarea type="text" name="taskcomment" rows="2" class="form-control" id="commentInputs" placeholder="Write a comment…" required ></textarea>
                            </div>
                            <div class="form-group">
                                <button type="submit" name="cmtsubmit" class="btn btn-primary btn-sm">Save</button>
                                <span id="res1"></span>
                            </div>
                        </form>
                        @if(count($taskComments)>0)
                        <div class="col-md-12 leftpadding-63">
                            <div class="bg-light rounded my-4 p-3" id="reloadMsg">
                            @foreach($taskComments as $taskComment)
                                @if($taskComment->uid == Auth::user()->id)
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="primary-user">
                                            <label class="small text-second">{{ Auth::user()->name }}</label><br>
                                            <p>{{ $taskComment->comments }}</p>
                                            <span class="small text-light">{{ $taskComment->created_at }}</span>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="sec-user">
                                            <label class="small text-default">{{ $taskComment->name }}</label><br>
                                            <p>{{ $taskComment->comments }}</p>
                                            <span class="small text-dark">{{ $taskComment->created_at }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>