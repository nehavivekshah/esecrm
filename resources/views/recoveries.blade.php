@extends('layout')
@section('title','Recoveries - eseCRM')

@section('content')
    @php
    
        $roles = session('roles');
        $roleArray = explode(',',($roles->permissions ?? ''));
    
    @endphp
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i> 
            All Recovery
            <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
        </div>
        <div class="container-fluid">
            <div class="board-title board-title-flex">
                <h1>List Board</h1>
                @if(in_array('users_add',$roleArray) || in_array('All',$roleArray))
                <div class="btn-group">
                    <label>Total Remaining Bal. {{ number_format($totalRemaining, 2) }} /-</label>
                    <a href="/manage-recovery" class="btn btn-primary bg-primary text-white btn-sm"><i class="bx bx-plus"></i> <span>Add New</span></a>
                </div>
                @endif
            </div>
            <div class="row">
                <div class="col-md-12 py-3 table-responsive">
                    <table id="lists" class="table table-condensed m-table" style="width:100%;border-radius: 5px!important;overflow: hidden;">
                        <thead>
                            <tr>
                                <th width="30px" class="m-none">SN.</th>
                                <th width="70px" class="m-none">Batch No.</th>
                                <th>Name</th>
                                <th class="m-none">Company</th>
                                <th>Amount</th>
                                <th class="m-none">Reminder</th>
                                <th class="m-none">Note</th>
                                <th class="m-none">Executive</th>
                                <th class="actionWidth position-sticky end-0">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recoveries as $k=>$recovery)
                            
                            @php
                                $reminderTimes = strtotime($recovery->reminder) * 1000;
                                
                                $colorStatus = ($recovery->remaining_amount == 0) ? 'table-success' : (
                                     ($recovery->status == '0' && 
                                      (date('Y-m-d', strtotime($recovery->reminder)) < date('Y-m-d') || 
                                      (date('Y-m-d', strtotime($recovery->reminder)) == date('Y-m-d')))) ? 
                                     'table-alert bg-alert' : 'table-white'
                                   );
                   
                                $status = ($recovery->status == '1') ? "Paid" : "Unpaid";
                            @endphp

                            <tr class="lead-row-{{ $reminderTimes }} {{ $colorStatus ?? '' }}">
                                <td width="30px" class="m-none">{{($k+1) ?? ''}}</td>
                                <td width="70px" class="m-none">{{$recovery->batchNo ?? ''}}</td>
                                <td>{{$recovery->name ?? ''}}<span class="small d-none">{{ $recovery->company ?? '' }}</span></td>
                                <td class="m-none">{{$recovery->company ?? ''}}</td>
                                <td>Rs. {{$recovery->remaining_amount ?? 0}}</td>
                                <td class="m-none">{{date_format(date_create(($recovery->reminder ?? '')), 'd M, y')}}</td>
                                <td class="m-none">{{$recovery->project_note ?? ''}}</td>
                                <td class="m-none">{{$recovery->poc ?? ''}}</td>
                                <td class="abtn position-sticky end-0" style="padding-left:0px!important;">
                                    <a href="javascript:void(0)" class="btn btn-warning btn-sm reminder" data-id="{{$recovery->id ?? ''}}" title="Reminder"><i class="bx bx-alarm"></i></a>
                                    <a href="javascript:void(0)" class="btn btn-primary bg-primary text-white btn-sm border-primary received" data-id="{{$recovery->id ?? ''}}" title="Received"><i class="bx bx-rupee"></i></a>
                                    @if(!empty($recovery->whatsapp))
                                    <a href="https://api.whatsapp.com/send/?phone={{$recovery->whatsapp}}&text=Hi&type=phone_number&app_absent=0" class="btn btn-success bg-success text-white btn-sm p-1" target="_blank" title="Whatsapp"><i class="bx bxl-whatsapp"></i></a>
                                    @endif
                                    @if(!empty($recovery->mob))
                                    <a href="tel:+{{ $recovery->mob }}" class="btn btn-warning btn-sm" title="Call"><i class="bx bx-phone"></i></a>
                                    @endif
                                    <a href="/manage-recovery?id={{$recovery->id ?? ''}}" class="btn btn-info btn-sm" title="Edit"><i class="bx bx-edit"></i></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    <!-- Modal Structure -->
    <div class="modal fade" id="recoveryModal" tabindex="-1" aria-labelledby="recoveryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="recoveryModalLabel">Recovery</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 px-2" id="loadContent"></div>
            </div>
        </div>
    </div>
@endsection