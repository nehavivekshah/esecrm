@php
    $company = session('companies');
    $roles = session('roles');
    $roleArray = explode(',', ($roles->permissions ?? ''));
    $amount = $project->amount ?? 0;
    $remaining = $amount; // Initialize remaining amount with the total project amount
@endphp

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 my-2">
            <div class="flex py-2 px-2 space-between mb-3 bg-main rounded item-center">
                <div class="companyName">
                    <h6 class="font-weight-bold mb-0">{{ $client->company }}</h6>
                </div>
                <div class="totalRemaining">
                    Total Remaining Bal.: <span class="font-weight-bold">Rs. {{$amount - $totalPaid}} /-</span>
                </div>
            </div>
            <form id="updatePayment" action="/recovery" class="row" method="post">
                @csrf
                <div class="form-group col-md-6">
                    <label for="payment" class="small">Payment Received*</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-rupee"></i></span>
                        <input type="number" name="received" id="received" class="form-control" placeholder="0.00" required />
                        <input type="hidden" name="client_id" value="{{ $client->id ?? '' }}" />
                        <input type="hidden" name="project_id" value="{{ $project->id ?? '' }}" />
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="payment" class="small">Payment Received Date*</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                        <input type="date" name="receivedDate" class="form-control" value="{{ now()->format('Y-m-d') }}" readonly />
                    </div>
                </div>
                <div class="form-group col-md-12 my-3">
                    <label for="payment" class="small" style="display:flex;gap:5px;"> <input type="checkbox" name="send" value="1" checked /> Send Thank You Note</label>
                    <textarea type="text" name="note" class="form-control" placeholder="Write thank you note here..." id="output">Thank you for payment of Rs. {{$amount - $totalPaid}} to {{$company->name ?? ''}}.</textarea>
                </div>
                <div class="form-group col-md-12 text-right">
                    <button type="submit" class="btn btn-success bg-success border text-white"><i class="bx bx-check"></i> Save</button>
                    <button type="reset" class="btn btn-white border"><i class="bx bx-gear"></i> Reset</button>
                </div>
            </form>
        </div>
        <div class="col-md-12">
            <h6 class="font-weight-bold">Payment History</h6>
        </div>
        <div class="col-md-12 py-3 table-responsive">
            <table class="table table-striped table-bordered m-table" style="width:100%">
                <thead>
                    <tr>
                        <th width="50px">Sr.No.</th>
                        <th>Date</th>
                        <th>Remaining</th>
                        <th>Paid</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recoveries as $k => $recovery)
                        @php
                            $status = ($recovery->status == '1') ? "Paid" : "Partly Paid";
                            $paid = $recovery->paid ?? 0; // Amount paid in this recovery
                            $remaining -= $paid; // Update remaining amount
                        @endphp

                        <tr>
                            <td>{{ $k + 1 }}</td>
                            <td>{{ date('d M, Y', strtotime($recovery->created_at ?? now())) }}</td>
                            <td>Rs. {{ number_format($remaining, 2) }}</td>
                            <td>
                                Rs.<input type="text" class="editableInputs" value="{{ number_format($paid, 2) }}" data-id="{{$recovery->id ?? ''}}" style="padding: 4px 7px;width: 100px;" title="Double click to edit" readonly />
                                <span class="status" style="margin-left: 10px; font-weight: bold;"></span>
                            </td>
                            <td class="text-success font-weight-bold">{{ $status }}</td>
                            <td class="text-center"><a href="javascript:void(0)" class="btn btn-danger btn-sm recoveryAmountDelete" data-id="{{$recovery->id ?? ''}}" data-page="recoveryAmountDelete" title="Delete"><i class="bx bx-trash"></i></a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    // Get the input field and output element
    const receivedInput = document.getElementById('received');
    const output = document.getElementById('output');

    // Add keyup event listener
    receivedInput.addEventListener('keyup', () => {
        // Get the current value of the input
        const value = receivedInput.value;

        // Display the value in the output element
        output.textContent = `Thank you for payment of Rs. ${value} to {{$company->name ?? ''}}.`;
    });
</script>

<script>

    $(document).ready(function () {
        // Function to sanitize input: Allow only numbers
        function sanitizeInput(value) {
            return value.replace(/[^0-9.]/g, ''); // Remove all non-numeric characters except the decimal point
        }
    
        // Function to validate if input is a valid number
        function isValidNumber(value) {
            return !isNaN(value) && Number(value) >= 0;
        }
    
        // AJAX function to send updated data
        function updateRecoveryAmount(inputElement, amount) {
            const recordId = $(inputElement).data('id'); // Assuming each input has a data-id attribute
            $.ajax({
                url: '/update-recovery-amount', // Replace with your actual endpoint
                method: 'GET', // Use GET method
                data: {
                    id: recordId,
                    amount: amount
                },
                success: function (response) {
                    //console.log('Amount updated:', response);
                    const $statusSpan = $(inputElement).next('.status');
                    $statusSpan.text('Updated').css('color', 'green');
                },
                error: function (xhr, status, error) {
                    console.error('Error updating amount:', error);
                    const $statusSpan = $(inputElement).next('.status');
                    $statusSpan.text('Error updating amount').css('color', 'red');
                }
            });
        }
    
        // Select all elements with the class "editableInputs"
        $('.editableInputs').each(function () {
            const $input = $(this); // Cache the current input element
    
            // Enable editing on double-click
            $input.on('dblclick', function () {
                $input.prop('readonly', false).focus(); // Remove readonly and focus the input
    
                const $statusSpan = $input.next('.status'); // Get the adjacent status span
                $statusSpan.text('Editing').css('color', 'blue'); // Update status span
            });
    
            // Handle keyup event for real-time sanitization and validation
            $input.on('input', function () {
                let value = $input.val();
                value = sanitizeInput(value); // Remove non-numeric characters
                $input.val(value); // Update the input field with the sanitized value
    
                const $statusSpan = $input.next('.status'); // Get the adjacent status span
    
                // Validate the sanitized value
                if (!isValidNumber(value)) {
                    $statusSpan.text('Invalid').css('color', 'red');
                } else {
                    $statusSpan.text('Valid').css('color', 'green');
                }
            });
    
            // Save the value on Enter key press
            $input.on('keyup', function (event) {
                const value = $input.val().trim();
                if (event.key === 'Enter' && isValidNumber(value)) {
                    $input.prop('readonly', true); // Reapply readonly
                    updateRecoveryAmount($input, value); // Call AJAX to update the amount
                }
            });
    
            // Disable editing on blur (focus loss)
            $input.on('blur', function () {
                const value = $input.val().trim();
                const $statusSpan = $input.next('.status'); // Get the adjacent status span
    
                if (isValidNumber(value)) {
                    updateRecoveryAmount($input, value); // Call AJAX to update the amount
                    $statusSpan.text('Editing').css('color', 'gray');
                } else {
                    $statusSpan.text('Invalid ').css('color', 'red');
                    $input.val(''); // Reset invalid input
                }
    
                $input.prop('readonly', true); // Reapply readonly
            });
        });
        
        //Delete Recovery Details
        $(".recoveryAmountDelete").click(function (e) {
            e.preventDefault(); // Prevent the default action
        
            const ele = $(this);
            const rowid = ele.data('id');
            const recoveryAmountDelete = ele.data('page');
        
            // SweetAlert confirmation
            swal({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover this!",
                icon: "warning",
                buttons: ["Cancel", "Yes, delete it!"],
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    // Proceed with AJAX request if confirmed
                    $.ajax({
                        type: 'get',
                        url: "/delete-recovery-amount",
                        data: { recoveryAmountDelete: recoveryAmountDelete, rowid: rowid },
                        success: function (response) {
                            $(ele).parent().parent().remove(); // Remove the parent element
                            swal("Deleted!", "The recovery detail has been deleted.", "success");
                        },
                        error: function (xhr, status, error) {
                            swal("Error!", "An error occurred while deleting the recovery detail. Please try again.", "error");
                            console.error("Error:", error);
                        }
                    });
                }
            });
        });

    });

</script>