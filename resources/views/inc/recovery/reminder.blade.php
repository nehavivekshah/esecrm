@php
    $roles = session('roles');
    $roleArray = explode(',', ($roles->permissions ?? ''));
    $amount = $project->amount ?? 0;
    $remaining = $amount; // Initialize remaining amount with the total project amount
@endphp

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 my-2">
            <!--<div class="flex py-2 space-between mb-3 bg-main rounded item-center">
                <div class="companyName">
                    <h6 class="font-weight-bold mb-0">{{ $client->company }}</h6>
                </div>
                <div class="totalRemaining">
                    Total Remaining Bal.: <span class="font-weight-bold">Rs. {{$amount - $totalPaid}} /-</span>
                </div>
            </div>-->
            <form id="updatePayment" action="/recovery" class="row" method="post">
                @csrf
                <div class="form-group col-md-6">
                    <label for="payment" class="small">Reminder Date*</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                        <input type="date" name="reminderDate" class="form-control" required />
                    </div>
                    <input type="hidden" name="received" value="0" />
                    <input type="hidden" name="client_id" value="{{ $client->id ?? '' }}" />
                    <input type="hidden" name="project_id" value="{{ $project->id ?? '' }}" />
                </div>
                <div class="form-group col-md-6">
                    <label for="payment" class="small">Remaining Amount</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-rupee"></i></span>
                        <input type="number" name="bal" class="form-control" value="{{$amount - $totalPaid}}" readonly />
                    </div>
                </div>
                <div class="form-group col-md-12 my-3">
                    <textarea type="text" name="note" class="form-control" placeholder="Write note here..." ></textarea>
                </div>
                <div class="form-group col-md-12 text-right">
                    <button type="submit" class="btn btn-success bg-success border text-white"><i class="bx bx-check"></i> Save</button>
                    <button type="reset" class="btn btn-white border"><i class="bx bx-gear"></i> Reset</button>
                </div>
            </form>
        </div>
    </div>
</div>
