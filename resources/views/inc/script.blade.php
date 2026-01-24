
    @if(Request::segment(1) != 'login')
        <!-- Scripts -->
        <script src="{{ asset('/public/assets/js/script.js') }}"></script>
    @else
    
    <script>
        $(document).ready(function() {
            
            // Press enter key to submit form
            $('body').on('keydown', function(event) {
                // Check if the key pressed was Enter (keyCode 13 or key 'Enter')
                if (event.key === 'Enter' || event.which === 13) {
                    // Prevent the default action (e.g., adding a newline or default form submit)
                    event.preventDefault();
                    // Trigger the same function the button uses
                    submitLogin();
                }
            });
            
        });
    
        function submitLogin() {
            var email = document.getElementById("email").value;
            var password = document.getElementById("password").value;

            //console.log("Email:", email, "Password:", password);

            var formData = JSON.stringify({ email: email, password: password });

            if (typeof Android !== "undefined" && typeof Android.sendFormData === "function") {
                Android.sendFormData(formData);
            } else {
                //console.log("Form Data:", formData);
            }

            $('#loginFRM').submit(); // Submits the form
        }
        
        function loadSharedPrefData() {
            if (typeof Android !== "undefined" && typeof Android.getSharedPrefData === "function") {
                const sharedData = Android.getSharedPrefData();
                const data = JSON.parse(sharedData);

                document.getElementById("email").value = data.email;
                document.getElementById("password").value = data.password;
                
                //alert(data.email);
                
                if(data.email != ''){
                    //$('#loginFRM').submit();
                }
                
                console.log("Login Successfully");
            } else {
                console.log("Android interface not available");
            }
        }
    </script>
    
    @endif
    
    @if(Request::segment(1) == 'task')
        
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
        <script>
            $(function() {
                // Define updateListPositions as a regular function, not as a method of a jQuery object
                function updateListPositions(container) {
                    let userId = $(container).attr("data-user");
                    let updatedPositions = [];
        
                    // Loop through all items in the container to get their new positions
                    $(container).children().each(function(index, element) {
                        let taskId = $(element).attr("data-taskid");
                        updatedPositions.push({
                            taskId: taskId,
                            position: index
                        });
                    });
                    
                    // Send updated positions via AJAX
                    $.ajax({
                        type: 'post',
                        url: "/tasksubmit",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // ADD THIS LINE
                        },
                        data: {
                            userId: userId,
                            updatedPositions: updatedPositions // Send the entire updated positions array
                        },
                        success: function(response) {
                            console.log(response);
                        }
                    });
                }
        
                $(".eventblock").sortable({
                    connectWith: ".connectedSortable",
                    opacity: 0.5,
        
                    // Handle when an item is moved within the same box
                    update: function(event, ui) {
                        let container = this;
                        $(this).sortable('refreshPositions'); // Ensure the positions are refreshed
                        updateListPositions(container); // Call the updateListPositions function
                    },
        
                    // Handle when an item is moved between different boxes
                    receive: function(event, ui) {
                        let container = this;
                        $(this).sortable('refreshPositions'); // Ensure the positions are refreshed
                        updateListPositions(container); // Call the updateListPositions function
                    }
                }).disableSelection();
            });
            
            $(document).ready(function() {
                $('#taskSearch').on('keydown', function(event) {
                    if (event.key === 'Enter') {
                        event.preventDefault(); // Prevent form submission on Enter
                    }
                });
            
                $('#taskSearch').on('keyup', function(event) {
                    let updatedPositions = $(this).val(); // Get the value from the input
            
                    $.ajax({
                        type: 'GET',
                        url: "/task-search", // Laravel route to handle the request
                        data: {
                            updatedPositions: updatedPositions,
                            _token: $('meta[name="csrf-token"]').attr('content') // Laravel CSRF token
                        },
                        success: function(response) {
                            let result = response.result;
            
                            $('.searchTaskResult').css('display','block');
                            $('#tsdata').html(result);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                        }
                    });
                });
            });

        </script>

    @endif
    
    @if(Request::segment(1) == 'contracts')
    
        <script>
            $(document).ready(function() {
                // When the delete button is clicked
                $('.delete').click(function() {
                    var selector = $(this);
                    var pagename = selector.attr("data-page"); // Corrected "date-page" to "data-page"
                    var rowid = selector.attr("id");
        
                    // Confirmation dialog using SweetAlert
                    swal({
                        title: "Are you sure?",
                        text: "You want to delete this row?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            // Perform the AJAX request
                            $.ajax({
                                type: 'GET',  // Use GET request as per your code, but ideally this should be POST or DELETE for deletion
                                url: "/ajax-send",
                                data: {
                                    pagename: pagename,
                                    rowid: rowid,
                                    contractDelete: 'contractDelete'  // Passing the deletion parameter
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Hide the row if deletion was successful
                                        selector.closest('tr').hide();  // Hides the entire row
                                        swal("Deleted!", "The row has been deleted successfully.", "success");
                                    } else {
                                        // Show error message if the server returned an error
                                        swal("Error", response.error || "There was an issue deleting the row.", "error");
                                    }
                                },
                                error: function() {
                                    // Handle errors from the AJAX request
                                    swal("Error", "An error occurred while processing your request.", "error");
                                }
                            });
                        } else {
                            // If user canceled the deletion
                            swal("This query is safe.");
                        }
                    });
                });
            });
        </script>
        
    @endif
    
    @if(Request::segment(1) == 'proposals')
    
        <script>
            $(document).ready(function() {
                // When the delete button is clicked
                $('.delete').click(function() {
                    var selector = $(this);
                    var pagename = selector.attr("data-page"); // Corrected "date-page" to "data-page"
                    var rowid = selector.attr("id");
        
                    // Confirmation dialog using SweetAlert
                    swal({
                        title: "Are you sure?",
                        text: "You want to delete this row?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            // Perform the AJAX request
                            $.ajax({
                                type: 'GET',  // Use GET request as per your code, but ideally this should be POST or DELETE for deletion
                                url: "/ajax-send",
                                data: {
                                    pagename: pagename,
                                    rowid: rowid,
                                    proposalDelete: 'proposalDelete'  // Passing the deletion parameter
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Hide the row if deletion was successful
                                        selector.closest('tr').hide();  // Hides the entire row
                                        swal("Deleted!", "The row has been deleted successfully.", "success");
                                    } else {
                                        // Show error message if the server returned an error
                                        swal("Error", response.error || "There was an issue deleting the row.", "error");
                                    }
                                },
                                error: function() {
                                    // Handle errors from the AJAX request
                                    swal("Error", "An error occurred while processing your request.", "error");
                                }
                            });
                        } else {
                            // If user canceled the deletion
                            swal("This query is safe.");
                        }
                    });
                });
            });
        </script>
        
    @endif
    
    @if(Request::segment(1) == 'leads')
    
    <script>
        // Helper: Get cookie by name
        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i].trim();
                if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length));
            }
            return null;
        }
    
        // Helper: Set cookie
        function setCookie(name, value, days = 1) {
            const expires = new Date(Date.now() + days * 864e5).toUTCString();
            document.cookie = name + "=" + encodeURIComponent(value) + "; expires=" + expires + "; path=/";
        }
    
        // Store selected checkboxes
        function storeSelectedLeads() {
            const selectedIds = $('.checklead:checked').map(function () {
                return $(this).val();
            }).get();
            setCookie('selectedLeads', selectedIds.join(','), 1);
        }
    
        // Restore checkbox states
        function restoreSelectedLeads() {
            const cookie = getCookie('selectedLeads');
            if (!cookie) return;
    
            const selected = cookie.split(',');
            $('.checklead').each(function () {
                const id = $(this).val();
                if (selected.includes(id)) {
                    $(this).prop('checked', true);
                    $(this).closest('tr').addClass('selected');
                } else {
                    $(this).prop('checked', false);
                    $(this).closest('tr').removeClass('selected');
                }
            });
    
            // Update master checkbox based on state
            $('#checkall').prop('checked', $('.checklead:checked').length === $('.checklead').length);
        }
    
        $(document).ready(function () {
            // Restore selection from cookie
            restoreSelectedLeads();
    
            // Master checkbox toggle
            $('#checkall').on('change', function () {
                let isChecked = $(this).prop('checked');
                $('.checklead').prop('checked', isChecked).trigger('change');
            });
    
            // Individual checkbox change
            $('#leadslists').on('change', '.checklead', function () {
                const row = $(this).closest('tr');
                if (this.checked) {
                    row.addClass('selected');
                } else {
                    row.removeClass('selected');
                }
    
                // If any box unchecked, uncheck master
                const allChecked = $('.checklead:checked').length === $('.checklead').length;
                $('#checkall').prop('checked', allChecked);
    
                storeSelectedLeads();
            });
        });
    </script>

    <script>
        $(document).ready(function(){
            /*$('#checkall').click(function() {
                // Check if the .checkall checkbox is checked
                let isChecked = $(this).prop('checked');
                
                // Set all .checklead checkboxes to the same state
                $('.checklead').prop('checked', isChecked);
            });
            
            $('#leadslists').on('click', '.selectrow', function() {
                // Check if the checkbox inside the current .selectrow is checked or unchecked
                if ($(this).find('.checklead').prop('checked')) {
                    // Add 'selected' class to the parent row if the checkbox is checked
                    $(this).closest('tr').addClass('selected');
                } else {
                    // Remove 'selected' class from the parent row if unchecked
                    $(this).closest('tr').removeClass('selected');
                }
            });*/

            $('#leadslists').on('dblclick', '.view', function() {
                let id = $(this).attr('id');
                let pagename = "leads";
                let leadModalLabel = $('#leadModalLabel');
                let locationParts = [];
                
                $('#commentLeadId').val(id);
                
                function formatDate(dateString) {
                    const options = { year: 'numeric', month: 'long', day: 'numeric' };
                    const date = new Date(dateString);
                    return date.toLocaleDateString(undefined, options);
                }
                
                $(document).on('click', '.edit-comment', function () {
                    let commentId = $(this).data('id');
                    let commentText = $(this).data('text');
                    let commentDate = $(this).data('date');
                    openEditCommentModal(commentId,commentText,commentDate);
                });
                
                // Open the edit comment modal
                function openEditCommentModal(commentId,commentText,commentDate) {
                    // Get existing comment data from the DOM
                    let msg = commentText;
                    let nextDate = commentDate;
                
                    // Fill modal fields
                    $('#editCommentId').val(commentId);
                    $('#editCommentMsg').val(msg);
                    $('#editCommentNextDate').val(new Date(nextDate).toISOString().slice(0,16)); // format for datetime-local
                    $('#editCommentNextDate').attr('min',(new Date(nextDate).toISOString().slice(0,16)));
                
                    // Show modal
                    $('#editCommentModal').modal('show');
                }
                
                // Save changes
                $('#saveCommentBtn').on('click', function() {
                    let commentId = $('#editCommentId').val();
                    let msg = $('#editCommentMsg').val();
                    let nextDate = $('#editCommentNextDate').val();
                
                    // Here you can send AJAX request to update comment on server
                    $.ajax({
                        url: '/manage-lead-comment', // your update endpoint
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            id: commentId,
                            message: msg,
                            nxtDate: nextDate
                        },
                        success: function(response) {
                            // Update the comment in the DOM
                            let commentDiv = $(`.cmt-details[data-id="${commentId}"]`);
                            commentDiv.find('p.ptext').text(msg);
                            commentDiv.find('.mfooter p.pdate strong:contains("Next Date")').parent().text(`Next Date: ${nextDate}`);
                            
                            $('#editCommentModal').modal('hide');
                        },
                        error: function(err) {
                            console.log(err);
                            alert('Error updating comment');
                        }
                    });
                });
                
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
                        let leadComments = parsedData.leadComments || [];
                        const proposals = parsedData.proposals || [];
                        let location = lead.location || '';
                        
                        if(location != ''){ locationParts = JSON.parse(location) ?? []; }
                        
                        let address = locationParts[0] ? locationParts[0].trim() : '';
                        let city = locationParts[1] ? locationParts[1].trim() : '';
                        let state = locationParts[2] ? locationParts[2].trim() : '';
                        let country = locationParts[3] ? locationParts[3].trim() : '';
                        let zip = locationParts[4] ? locationParts[4].trim() : '';
                        
                        if (parsedData.leads.name) {
                            leadModalLabel.html("#"+parsedData.leads.id + " - " + parsedData.leads.name);
                            
                            //purpose = parsedData.leads.name + " commented that,"; // Assign value if not null
                        } else {
                            //purpose = "Customer commented that,"; // Default value if null
                        }
                        
                        /*let purposeHtml = `
                            <div class="date-style">
                                ${purpose}
                            </div>
                        `;*/
    
                        /*let commentsHtml = leadComments.map(comment => `
                            <div class="cmt-details">
                                <p>${(comment.msg == null || comment.msg == '') ? 'Call back at next date' : comment.msg}</p>
                                <div class="mfooter">
                                    <p><strong>Last Talk:</strong> ${formatDate(comment.updated_at)}</p>
                                    <p><strong>Next Date:</strong> ${formatDate(comment.next_date)}</p>
                                </div>
                            </div>
                        `).join('');*/
                        
                        /*let commentsHtml = leadComments.map(comment => `
                            <div class="cmt-details">
                                <p>${(comment.msg == null || comment.msg == '') ? 'Call back at next date' : comment.msg}</p>
                                <div class="mfooter">
                                    <p><strong>Last Talk:</strong> ${formatDate(comment.updated_at)}</p>
                                    <p><strong>Next Date:</strong> ${formatDate(comment.next_date)}</p>
                                </div>
                                <button class="btn btn-sm btn-primary edit-comment" data-id="${comment.id}" data-text="${comment.msg}" data-date="${comment.updated_at}">
                                    Edit
                                </button>
                            </div>
                        `).join('');*/
                        
                        let reversedComments = [...leadComments].reverse();

                        let commentsHtml = reversedComments.map((comment, index) => {
                            let isLatest = index === 0; // after reversing, first item is the latest
                            return `
                                <div class="cmt-details" data-id="${comment.id}">
                                    <p class="ptext">${(comment.msg == null || comment.msg == '') ? 'Call back at next date' : comment.msg}</p>
                                    ${isLatest ? `<a class="edit-comment" data-id="${comment.id}" data-text="${comment.msg}" data-date="${comment.updated_at}">
                                        <i class="bx bx-edit"></i>
                                    </a>` : ''}
                                    <div class="mfooter">
                                        <p><strong>Last Talk:</strong> ${formatDate(comment.updated_at)}</p>
                                        <p class="pdate"><strong>Next Date:</strong> ${formatDate(comment.next_date)}</p>
                                    </div>
                                </div>
                            `;
                        }).join('');
                        
                        // If the resulting string is empty, set the message
                        if (!commentsHtml) { // checks for empty string, null, undefined
                            commentsHtml = '<p class="no-comments text-center py-5">No comments found.</p>';
                        }
    
                        // Injecting HTML content into the modal
                        $('#id').val(lead.id);
                        $('#leadDelete').attr("data-id",lead.id);
                        $('#name').val(lead.name);
                        $('#email').val(lead.email);
                        $('#mob').val(lead.mob);
                        $('#whatsapp').val(lead.whatsapp);
                        $('#gstno').val(lead.gstno);
                        $('#company').val(lead.company);
                        $('#position').val(lead.position);
                        $('#industry').val(lead.industry);
                        $('#address').val(address);
                        $('#city').val(city);
                        $('#state').val(state);
                        $('#country').val(country);
                        $('#zip').val(zip);
                        $('#website').val(lead.website);
                        $('#assigned').val(lead.assigned);
                        $('#purpose').val(lead.purpose);
                        $('#value').val(lead.values);
                        $('#language').val(lead.language);
                        $('#poc').val(lead.poc);
                        $('#tags').val(lead.tags);
                        
                        var status = lead.status;

                        let option = `
                            <option value="0" ${status == '0' ? 'selected' : ''}>Fresh</option>
                            <option value="1" ${status == '1' ? 'selected' : ''}>Follow Up</option>
                            <option value="5" ${status == '5' ? 'selected' : ''}>Converted</option>
                            <option value="9" ${status == '9' ? 'selected' : ''}>Loss</option>
                        `;
                        
                        $('#status').html(option);
                        
                        $('#leadcomments').html(commentsHtml);//purposeHtml + 
                        
                        let lastNextDate = leadComments.reduce((latest, comment) => {
                            // Compare dates and return the later one
                            return (new Date(comment.next_date) > new Date(latest)) ? comment.next_date : latest;
                        }, leadComments[0]?.next_date || null);
                        
                        // Format the date to 'YYYY-MM-DDTHH:mm' (for datetime-local input)
                        if (lastNextDate) {
                            let formattedDate = new Date(lastNextDate);
                            let year = formattedDate.getFullYear();
                            let month = String(formattedDate.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
                            let day = String(formattedDate.getDate()).padStart(2, '0');
                            let hours = String(formattedDate.getHours()).padStart(2, '0');
                            let minutes = String(formattedDate.getMinutes()).padStart(2, '0');
                        
                            let formattedDateString = `${year}-${month}-${day}T${hours}:${minutes}`;
                        
                            // Set the min attribute to the formatted date
                            $("#nxtDate").attr("min", formattedDateString);
                        }
                        
                        $('#lead_id').val(lead.id);
                        $('#clientName').val(lead.name);
                        $('#clientEmail').val(lead.email);
                        $('#clientPhone').val(lead.mob);
                        $('#clientAddress').val(address);
                        $('#clientCity').val(city);
                        $('#clientState').val(state);
                        $('#clientZip').val(lead.zipcode);
                        
                        // Assuming `parsedData` is the response object
                        renderProposals(proposals);
    
                        // Show the modal
                        $('#leadModal').modal('show');
                    },
                    error: function(xhr, status, error){
                        // Handle errors here
                        console.log('Error:', error);
                    }
                });
            });

            $('#importFile').click(function() {
                $("#impLeadFile").trigger("click");
            });
            
            // Submit the form when a file is selected
            $('#impLeadFile').change(function() {
                // Submit the form automatically after file selection
                $('#leadsubmit').submit();
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const itemsTableBody = document.getElementById('items-table').querySelector('tbody');
            const addItemBtn = document.querySelector('.add-item-btn');
            const currencySelect = document.getElementById('currency');
            const adjustmentInput = document.getElementById('adjustment');
            const discountTypeSelect = document.getElementById('discountType');
            const discountValueInput = document.getElementById('discountValue');
            const discountTypeDisplay = document.getElementById('discount-type-display');
            const discountTotalDisplay = document.getElementById('discount-total');
            const discountTotalDisplay1 = document.getElementById('discount-total1');
    
            function formatCurrency(amount, currencyCode = 'INR') {
                let options = { style: 'currency', currency: currencyCode };
                try {
                    const locale = currencyCode === 'INR' ? 'en-IN' : undefined;
                    return new Intl.NumberFormat(locale, options).format(amount);
                } catch (e) {
                    console.error("Currency formatting error:", e);
                    const symbols = { INR: '₹', USD: '$', EUR: '€', GBP: '£' };
                    return (symbols[currencyCode] || '') + amount.toFixed(2);
                }
            }
    
            function updateRowAmount(row) {
                const qtyInput = row.querySelector('.item-qty');
                const rateInput = row.querySelector('.item-rate');
                const amountCell = row.querySelector('.item-amount');
                const currencyCode = currencySelect.value;
    
                const qty = parseFloat(qtyInput.value) || 0;
                const rate = parseFloat(rateInput.value) || 0;
                const amount = qty * rate;
    
                amountCell.textContent = formatCurrency(amount, currencyCode);
                return amount;
            }
    
            function calculateTotals() {
                let subTotal = 0;
                let taxTotal = 0;
                let discountAmount = 0;
    
                const currencyCode = currencySelect.value;
                const adjustment = parseFloat(adjustmentInput.value) || 0;
                const discountType = discountTypeSelect.value;
                const discountPercentage = parseFloat(discountValueInput.value) || 0;
    
                const selectedDiscountTypeOption = discountTypeSelect.options[discountTypeSelect.selectedIndex];
                discountTypeDisplay.textContent = selectedDiscountTypeOption.text;
    
                itemsTableBody.querySelectorAll('tr').forEach(row => {
                    const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                    const rate = parseFloat(row.querySelector('.item-rate').value) || 0;
                    const taxRate = parseFloat(row.querySelector('.item-tax').value) || 0;
    
                    const rowAmount = qty * rate;
                    const rowTax = rowAmount * taxRate;
    
                    subTotal += rowAmount;
                    taxTotal += rowTax;
    
                    row.querySelector('.item-amount').textContent = formatCurrency(rowAmount, currencyCode);
                });
    
                if (discountType === 'before-tax' && discountPercentage > 0) {
                    discountAmount = subTotal * (discountPercentage / 100);
                } else if (discountType === 'after-tax' && discountPercentage > 0) {
                    discountAmount = (subTotal + taxTotal) * (discountPercentage / 100);
                }
    
                discountAmount = Math.max(0, discountAmount);
    
                const grandTotal = subTotal + taxTotal - discountAmount + adjustment;
    
                document.getElementById('sub-total').textContent = formatCurrency(subTotal, currencyCode);
                document.getElementById('sub-total1').value = formatCurrency(subTotal, currencyCode);
                discountTotalDisplay.textContent = formatCurrency(discountAmount, currencyCode);
                discountTotalDisplay1.value = formatCurrency(discountAmount, currencyCode);
                document.getElementById('tax-total').textContent = formatCurrency(taxTotal, currencyCode);
                document.getElementById('tax-total1').value = formatCurrency(taxTotal, currencyCode);
                document.getElementById('total').textContent = formatCurrency(grandTotal, currencyCode);
                document.getElementById('total1').value = formatCurrency(grandTotal, currencyCode);
            }
    
            addItemBtn.addEventListener('click', function() {
                const lastRow = itemsTableBody.querySelector('tr:last-child');
                if (!lastRow) return;
    
                const newRow = lastRow.cloneNode(true);
                const newRowIndex = itemsTableBody.querySelectorAll('tr').length; // Get number of rows for new index
                newRow.querySelectorAll('input[type="text"], input[type="number"], textarea').forEach(input => {
                    if (input.classList.contains('item-qty')) {
                        input.value = 1;
                    } else if (input.classList.contains('item-rate')) {
                        input.value = '0.00';
                    } else if (!input.classList.contains('item-name') && !input.classList.contains('item-description')) {
                        input.value = '';
                    }
                });
    
                newRow.querySelector('.item-tax').value = '0';
                newRow.querySelector('.item-amount').textContent = formatCurrency(0, currencySelect.value);
    
                // Update the name and description fields dynamically based on the row index
                const nameInput = newRow.querySelector('.item-name');
                const descInput = newRow.querySelector('.item-description');
                nameInput.name = `proposal_items[${newRowIndex}]['item_name']`;
                descInput.name = `proposal_items[${newRowIndex}]['description']`;
                nameInput.value = `Item ${newRowIndex}`;
                descInput.value = `Description of Item ${newRowIndex}`;
    
                itemsTableBody.appendChild(newRow);
                calculateTotals();
            });
    
            itemsTableBody.addEventListener('click', function(event) {
                if (event.target.closest('.remove-item-btn')) {
                    if (itemsTableBody.querySelectorAll('tr').length > 1) {
                        event.target.closest('tr').remove();
                        calculateTotals();
                    } else {
                        alert("You must have at least one item.");
                    }
                }
            });
    
            itemsTableBody.addEventListener('input', function(event) {
                const target = event.target;
                if (target.classList.contains('item-qty') || target.classList.contains('item-rate')) {
                    calculateTotals();
                }
            });
    
            itemsTableBody.addEventListener('change', function(event) {
                const target = event.target;
                if (target.classList.contains('item-tax')) {
                    calculateTotals();
                }
            });
    
            currencySelect.addEventListener('change', calculateTotals);
            adjustmentInput.addEventListener('input', calculateTotals);
            discountTypeSelect.addEventListener('change', calculateTotals);
            discountValueInput.addEventListener('input', calculateTotals);
    
            calculateTotals();
        });
    </script>
    
    <script>
        function formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            const date = new Date(dateString);
            return date.toLocaleDateString(undefined, options);
        }
    
        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'INR',
                minimumFractionDigits: 2
            }).format(parseFloat(amount));
        }
    
        function renderProposals(proposals) {
            const tableBody = document.getElementById('Proposals');
            tableBody.innerHTML = ''; // Clear old data
    
            if (!proposals || proposals.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-muted py-3">
                            No proposals found.
                        </td>
                    </tr>`;
                return;
            }
    
            proposals.forEach((proposal, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td width="110px">
                        PRO-${String(proposal.id).padStart(6, '0')}<br>
                        <div class="table-action"><a href="/proposal/${proposal.id}/${CryptoJS.MD5(proposal.client_email.trim().toLowerCase()).toString()}">View</a> | <a href="manage-proposal?id=${proposal.id}">Edit</a>
                    </td>
                    <td>${proposal.subject}</td>
                    <td>${formatCurrency(proposal.grand_total)}</td>
                    <td width="110px" class="m-none">${formatDate(proposal.proposal_date)}</td>
                    <td width="110px" class="m-none">${formatDate(proposal.open_till)}</td>
                    <td class="m-none text-center">
                        <span class="badge bg-${getStatusBadgeClass(proposal.status)}">${proposal.status}</span>
                    </td>
                    <td class="m-none text-right" width="110px">${formatDate(proposal.created_at)}</td>
                `;
                tableBody.appendChild(row);
            });
        }
    
        function getStatusBadgeClass(status) {
            switch (status.toLowerCase()) {
                case 'draft':
                    return 'dark';
                case 'sent':
                    return 'success';
                case 'accepted':
                    return 'primary';
                case 'declined':
                    return 'danger';
                default:
                    return 'light';
            }
        }
    
    </script>

    <script>
        const reminderTimes = [{!! json_encode($reminderTimes) !!}]; // Pass reminder times as a JS array
        console.log(reminderTimes);
        // Function to check reminders
        function checkReminders() {
            reminderTimes.forEach(function(reminderTime, index) {
                if (!reminderTime) return; // Skip invalid reminder times
        
                const currentTime = new Date().getTime();
                const timeDifference = reminderTime - currentTime;
                
                console.log( reminderTime - currentTime);
        
                // Get the row by class
                const leadRowClass = `.lead-row-${reminderTime}`; // Class selector in jQuery
        
                if (timeDifference > 0) {
                    
                    setTimeout(function() {
                        $(leadRowClass).removeClass('table-warning').addClass('table-danger bg-danger'); // Add both classes
                    }, timeDifference);
                }
            });
        }
    
        // Check reminders every minute (60000 milliseconds)
        setInterval(checkReminders, 15000);
    
        // Initial check when the page loads
        checkReminders();
    </script>
    
    <script>
        $(document).ready(function(){
        // When the delete button is clicked
            $('.leadDelete').click(function() {
                var selector = $(this);
                var pagename = selector.attr("data-page"); // Corrected "date-page" to "data-page"
                var rowid = selector.attr("data-id");
    
                // Confirmation dialog using SweetAlert
                swal({
                    title: "Are you sure?",
                    text: "You want to delete this row?",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        // Perform the AJAX request
                        $.ajax({
                            type: 'GET',  // Use GET request as per your code, but ideally this should be POST or DELETE for deletion
                            url: "/ajax-send",
                            data: {
                                pagename: pagename,
                                rowid: rowid,
                                leadDelete: 'leadDelete'  // Passing the deletion parameter
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Hide the row if deletion was successful
                                    $('#'+rowid).hide();  // Hides the entire row
                                    $('#leadModal').modal('hide');
                                    swal("Deleted!", "The row has been deleted successfully.", "success");
                                } else {
                                    // Show error message if the server returned an error
                                    swal("Error", response.error || "There was an issue deleting the row.", "error");
                                }
                            },
                            error: function() {
                                // Handle errors from the AJAX request
                                swal("Error", "An error occurred while processing your request.", "error");
                            }
                        });
                    } else {
                        // If user canceled the deletion
                        swal("This query is safe.");
                    }
                });
            });
        });
    </script>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchForm = document.getElementById('searchForm');
            if (!searchForm) return console.error("searchForm missing.");
        
            const searchInput = document.getElementById('searchInput');
            const searchLead = document.getElementById('searchLead');
            const statusFilter = document.getElementById('statusFilter');
            const selectRowCount = document.getElementById('selectrowcount');
            const assignUser = document.getElementById('assignUser');
        
            const rowcountHidden = document.getElementById('rowcountHidden');
            const assignUserHidden = document.getElementById('assignUserHidden');
        
            /* -----------------------------------------
               1) Debounced search (improved UX)
            ----------------------------------------- */
            let debounceTimer;
            const debounceDelay = 100000; // better than 2 seconds
        
            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => searchForm.submit(), debounceDelay);
                });
            }
        
            /* -----------------------------------------
               2) Status filter auto-submit
            ----------------------------------------- */
            if (statusFilter) {
                statusFilter.addEventListener('change', () => searchForm.submit());
            }
        
            /* -----------------------------------------
               3) Row count filter
            ----------------------------------------- */
            if (selectRowCount) {
                selectRowCount.addEventListener('change', () => {
                    rowcountHidden.value = selectRowCount.value;
                    searchForm.submit();
                });
            }
        
            /* -----------------------------------------
               4) Assign user filter
            ----------------------------------------- */
            if (assignUser) {
                assignUser.addEventListener('change', () => {
                    assignUserHidden.value = assignUser.value;
                    searchForm.submit();
                });
            }
        
            /* -----------------------------------------
               5) Search button click filter
            ----------------------------------------- */
            if (searchLead) {
                searchLead.addEventListener('click', () => {
                    searchForm.submit();
                });
            }
        });
    </script>
    
    @endif
    
    @if(Request::segment(1) == 'clients')
    
        <script>
            $(document).ready(function(){
                $('#checkall').click(function() {
                    // Check if the .checkall checkbox is checked
                    let isChecked = $(this).prop('checked');
                    
                    // Set all .checklead checkboxes to the same state
                    $('.checklead').prop('checked', isChecked);
                });
                
                $('#leadslists').on('click', '.selectrow', function() {
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
                    $("#impClientFile").trigger("click");
                });
                
                // Submit the form when a file is selected
                $('#impClientFile').change(function() {
                    // Submit the form automatically after file selection
                    $('#Clientsubmit').submit();
                });
                
                $('.edit').on('click', function () {
                    $('#' + $(this).data('view-id')).trigger('dblclick');
                });
    
                $('.view').dblclick(function(){
                    let id = $(this).attr('id');
                    let pagename = "client";
                    let locationParts = [];
                    
                    $('#commentClientId').val(id);
                    
                    function formatDate(dateString) {
                        const options = { year: 'numeric', month: 'long', day: 'numeric' };
                        const date = new Date(dateString);
                        return date.toLocaleDateString(undefined, options);
                    }
            
                    $.ajax({
                        url: '/view-single-client', // Replace with your server endpoint URL
                        type: 'GET', // You can use 'GET' or 'POST' depending on your requirement
                        data: {
                            id: id,
                            pagename: pagename
                        },
                        success: function(response){
                            let purpose;
                            // Parsing the JSON data
                            var parsedData = JSON.parse(response);
                            let client = parsedData.clients;
                            let leadComments = parsedData.leadComments;
                            let location = client.location || '';
                        
                            if(location != ''){ locationParts = JSON.parse(location) ?? []; }
                            
                            let address = locationParts[0] ? locationParts[0].trim() : '';
                            let city = locationParts[1] ? locationParts[1].trim() : '';
                            let state = locationParts[2] ? locationParts[2].trim() : '';
                            let country = locationParts[3] ? locationParts[3].trim() : '';
                            let zip = locationParts[4] ? locationParts[4].trim() : '';
        
                            // Injecting HTML content into the modal
                            $('#id').val(client.id);
                            $('#name').val(client.name);
                            $('#email').val(client.email);
                            $('#mob').val(client.mob);
                            $('#alterMob').val(client.alterMob);
                            $('#whatsapp').val(client.whatsapp);
                            $('#company').val(client.company);
                            $('#gst').val(client.gstno);
                            $('#position').val(client.position);
                            $('#industry').val(client.industry);
                            $('#address').val(address);
                            $('#city').val(city);
                            $('#state').val(state);
                            $('#country').val(country);
                            $('#website').val(client.website);
                            
                            var status = client.status;
    
                            let option = `
                                <option value="0" ${status == '0' ? 'selected' : ''}>Inactive</option>
                                <option value="1" ${status == '1' ? 'selected' : ''}>Active</option>
                            `;
                            
                            $('#status').html(option);
                            
                            // Show the modal
                            $('#clientModal').modal('show');
                        },
                        error: function(xhr, status, error){
                            // Handle errors here
                            console.log('Error:', error);
                        }
                    });
                });
            });
    
        </script>
        
        <script>
            $(document).ready(function() {
                // When the delete button is clicked
                $('.delete').click(function() {
                    var selector = $(this);
                    var pagename = selector.attr("data-page"); // Corrected "date-page" to "data-page"
                    var rowid = selector.attr("id");
        
                    // Confirmation dialog using SweetAlert
                    swal({
                        title: "Are you sure?",
                        text: "You want to delete this row?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            // Perform the AJAX request
                            $.ajax({
                                type: 'GET',  // Use GET request as per your code, but ideally this should be POST or DELETE for deletion
                                url: "/ajax-send",
                                data: {
                                    pagename: pagename,
                                    rowid: rowid,
                                    clientDelete: 'clientDelete'  // Passing the deletion parameter
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Hide the row if deletion was successful
                                        selector.closest('tr').hide();  // Hides the entire row
                                        swal("Deleted!", "The row has been deleted successfully.", "success");
                                    } else {
                                        // Show error message if the server returned an error
                                        swal("Error", response.error || "There was an issue deleting the row.", "error");
                                    }
                                },
                                error: function() {
                                    // Handle errors from the AJAX request
                                    swal("Error", "An error occurred while processing your request.", "error");
                                }
                            });
                        } else {
                            // If user canceled the deletion
                            swal("This query is safe.");
                        }
                    });
                });
            });
        </script>
        
    @endif
    
    
    @if(Request::segment(1) == 'users')
    
        <script>
            $(document).ready(function() {
                // When the delete button is clicked
                $('.delete').click(function() {
                    var selector = $(this);
                    var pagename = selector.attr("data-page"); // Corrected "date-page" to "data-page"
                    var rowid = selector.attr("id");
        
                    // Confirmation dialog using SweetAlert
                    swal({
                        title: "Are you sure?",
                        text: "You want to delete this row?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            // Perform the AJAX request
                            $.ajax({
                                type: 'GET',  // Use GET request as per your code, but ideally this should be POST or DELETE for deletion
                                url: "/ajax-send",
                                data: {
                                    pagename: pagename,
                                    rowid: rowid,
                                    userDelete: 'userDelete'  // Passing the deletion parameter
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Hide the row if deletion was successful
                                        selector.closest('tr').hide();  // Hides the entire row
                                        swal("Deleted!", "The row has been deleted successfully.", "success");
                                    } else {
                                        // Show error message if the server returned an error
                                        swal("Error", response.error || "There was an issue deleting the row.", "error");
                                    }
                                },
                                error: function() {
                                    // Handle errors from the AJAX request
                                    swal("Error", "An error occurred while processing your request.", "error");
                                }
                            });
                        } else {
                            // If user canceled the deletion
                            swal("This query is safe.");
                        }
                    });
                });
            });
        </script>
    
    @endif
    
    @if(Request::segment(1) == 'manage-recovery')
        
        <script>
        
            $(document).ready(function () {
                $(document).on('click', '.more', function () {
                    // Show the hidden elements by removing the 'none' class and adding 'block'
                    $('.none').removeClass('none').addClass('block');
            
                    // Change button text to 'Hide Advance Mode'
                    $(this).html('Hide Advance Mode');
            
                    // Toggle the button class to 'hide'
                    $(this).removeClass('more').addClass('hide');
                });
            
                $(document).on('click', '.hide', function () {
                    // Hide the elements by removing the 'block' class and adding 'none'
                    $('.block').removeClass('block').addClass('none');
            
                    // Change button text to 'Show Advance Mode'
                    $(this).html('Show Advance Mode');
            
                    // Toggle the button class to 'more'
                    $(this).removeClass('hide').addClass('more');
                });
            });
            
            document.getElementById('clientId').addEventListener('change', function () {
                const clientId = this.value;
                const projectDropdown = document.getElementById('projectId');
            
                // Function to clear client fields
                const clearClientFields = () => {
                    document.getElementById('btno').value = '';
                    document.getElementById('name').value = '';
                    document.getElementById('company').value = '';
                    document.getElementById('phone').value = '';
                    document.getElementById('whatsapp').value = '';
                };
            
                // Function to clear project dropdown
                const clearProjectDropdown = () => {
                    projectDropdown.innerHTML = '<option value="">Select a project...</option>';
                };
            
                // Handle "new" client selection
                if (clientId === 'new') {
                    clearClientFields();
                    // clearProjectDropdown();
                    return;
                }
            
                if (clientId) {
                    // Fetch client details
                    fetch(`/get-client/${clientId}`)
                        .then(response => {
                            if (!response.ok) throw new Error('Failed to fetch client details');
                            return response.json();
                        })
                        .then(data => {
                            if (data.client) {
                                document.getElementById('btno').value = data.client.batchNo || '';
                                document.getElementById('name').value = data.client.name || '';
                                document.getElementById('company').value = data.client.company || '';
                                document.getElementById('phone').value = data.client.mobile || '';
                                document.getElementById('whatsapp').value = data.client.whatsapp || '';
                            } else {
                                clearClientFields();
                                console.warn('No client data found');
                            }
                        })
                        .catch(error => {
                            clearClientFields();
                            console.error('Error fetching client data:', error);
                        });
            
                    // Fetch associated projects
                    fetch(`/get-projects/${clientId}`)
                        .then(response => {
                            if (!response.ok) throw new Error('Failed to fetch projects');
                            return response.json();
                        })
                        .then(data => {
                            clearProjectDropdown();
                            if (data.projects && data.projects.length > 0) {
                                
                                projectDropdown.innerHTML = '<option value="new">+ New Project</option>';
                                
                                data.projects.forEach(project => {
                                    const option = document.createElement('option');
                                    option.value = project.id;
                                    option.textContent = `${project.name} - ${project.amount}`;
                                    projectDropdown.appendChild(option);
                                });
                            } else {
                                const option = document.createElement('option');
                                option.value = 'new';
                                option.textContent = '+ New Project';
                                projectDropdown.appendChild(option);
                            }
                            $('.selectpicker').selectpicker('refresh'); // Refresh selectpicker
                        })
                        .catch(error => {
                            console.error('Error fetching projects:', error);
                            // clearProjectDropdown();
                            projectDropdown.innerHTML = '<option value="new">+ New Project</option>';
                        });
                } else {
                    clearClientFields();
                    // clearProjectDropdown();
                }
            });
            
            // Fetch and populate project details
            const projects = @json($projects);
            
            document.getElementById('projectId').addEventListener('change', function () {
                const projectId = this.value;
            
                // Clear project fields if no project or "+ New Project" is selected
                const clearProjectFields = () => {
                    document.getElementById('project').value = '';
                    document.getElementById('amount').value = '';
                };
            
                if (projectId === 'new' || projectId === '') {
                    clearProjectFields();
                    document.getElementById('pDiv').style = "display:block";
                    return;
                }
            
                const project = projects.find(p => p.id == projectId);
                if (project) {
                    document.getElementById('project').value = project.name || '';
                    document.getElementById('amount').value = project.amount || '';
                } else {
                    clearProjectFields();
                }
            });

        </script>

    @endif
    
    @if(Request::segment(1) == 'recoveries')
        
        <script>
        
            $(document).ready(function () {
                
                $(document).on('click', '.reminder, .received', function () {
                    const recoveryId = $(this).data('id'); // Get the ID
                    const recoveryTitle = $(this).attr('title'); // Get the ID
                    var title;
                    if(recoveryTitle == 'Received'){
                        title = "Receive Payment From";
                        
                        $.ajax({
                            url: `/recovery/${recoveryId}/${recoveryTitle}`, // Adjust your route
                            type: 'GET',
                            success: function (data) {
                                // Populate modal content with fetched data
                                $('#recoveryModal .modal-body').html(data);
                            },
                            error: function () {
                                console.error('Failed to fetch data for recovery ID:', recoveryId);
                            }
                        });
                    }else{
                        title = 'Send Recovery Reminder to';
                        
                        $.ajax({
                            url: `/recovery/${recoveryId}/${recoveryTitle}`, // Adjust your route
                            type: 'GET',
                            success: function (data) {
                                // Populate modal content with fetched data
                                $('#recoveryModal .modal-body').html(data);
                            },
                            error: function () {
                                console.error('Failed to fetch data for recovery ID:', recoveryId);
                            }
                        });
                    }
                    $('#recoveryModalLabel').html(title);
                    
                    $('#recoveryModal').modal('show'); // Show the modal
                });

            });

        </script>

    @endif
    
    @if(Request::segment(1) == 'companies')
    
        <script>
            $(document).ready(function() {
                // When the delete button is clicked
                $('.delete').click(function() {
                    var selector = $(this);
                    var pagename = selector.attr("data-page"); // Corrected "date-page" to "data-page"
                    var rowid = selector.attr("id");
        
                    // Confirmation dialog using SweetAlert
                    swal({
                        title: "Are you sure?",
                        text: "You want to delete this row?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            // Perform the AJAX request
                            $.ajax({
                                type: 'GET',  // Use GET request as per your code, but ideally this should be POST or DELETE for deletion
                                url: "/ajax-send",
                                data: {
                                    pagename: pagename,
                                    rowid: rowid,
                                    companyDelete: 'companyDelete'  // Passing the deletion parameter
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Hide the row if deletion was successful
                                        selector.closest('tr').hide();  // Hides the entire row
                                        swal("Deleted!", "The row has been deleted successfully.", "success");
                                    } else {
                                        // Show error message if the server returned an error
                                        swal("Error", response.error || "There was an issue deleting the row.", "error");
                                    }
                                },
                                error: function() {
                                    // Handle errors from the AJAX request
                                    swal("Error", "An error occurred while processing your request.", "error");
                                }
                            });
                        } else {
                            // If user canceled the deletion
                            swal("This query is safe.");
                        }
                    });
                });
                
                // When the account status button is clicked
                $('.accountstatus').click(function() {
                    var selector = $(this);
                    var pagename = selector.attr("data-page"); 
                    var rowid = selector.attr("id");
                    var action;
            
                    if (pagename == 'companyDeactivate') {
                        action = "deactivate";
                        newClass = "bg-danger";
                    } else {
                        action = "activate";
                        newClass = "bg-success";
                    }
            
                    // Confirmation dialog using SweetAlert
                    swal({
                        title: "Are you sure?",
                        text: "You want to " + action + " this row?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willProceed) => {
                        if (willProceed) {
                            // Perform the AJAX request
                            $.ajax({
                                type: 'GET',
                                url: "/ajax-send",
                                data: {
                                    pagename: pagename,
                                    rowid: rowid,
                                },
                                success: function(response) {
                                    if (response.success) {
                                        selector.removeClass("bg-success bg-danger").addClass(newClass);
                                        if (pagename == 'companyDeactivate') {
                                            selector.html("Deactive");
                                        }else{
                                            selector.html("Active");
                                        }
                                        swal(action.charAt(0).toUpperCase() + action.slice(1) + "d!", "The row has been " + action + "d successfully.", "success");
                                    } else {
                                        // Show error message if the server returned an error
                                        swal("Error", response.error || "There was an issue with the row.", "error");
                                    }
                                },
                                error: function() {
                                    // Handle errors from the AJAX request
                                    swal("Error", "An error occurred while processing your request.", "error");
                                }
                            });
                        } else {
                            swal("Action canceled", "No changes were made.", "info");
                        }
                    });
                });
            });
        </script>
    
    @endif
    
    @if(Request::segment(1) == 'licensing')
    
        <script>
            $(document).ready(function() {
                // When the delete button is clicked
                $('.delete').click(function() {
                    var selector = $(this);
                    var pagename = selector.attr("data-page"); // Corrected "date-page" to "data-page"
                    var rowid = selector.attr("id");
        
                    // Confirmation dialog using SweetAlert
                    swal({
                        title: "Are you sure?",
                        text: "You want to delete this row?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            // Perform the AJAX request
                            $.ajax({
                                type: 'GET',  // Use GET request as per your code, but ideally this should be POST or DELETE for deletion
                                url: "/ajax-send",
                                data: {
                                    pagename: pagename,
                                    rowid: rowid,
                                    licenseDelete: 'licenseDelete'  // Passing the deletion parameter
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Hide the row if deletion was successful
                                        selector.closest('tr').hide();  // Hides the entire row
                                        swal("Deleted!", "The row has been deleted successfully.", "success");
                                    } else {
                                        // Show error message if the server returned an error
                                        swal("Error", response.error || "There was an issue deleting the row.", "error");
                                    }
                                },
                                error: function() {
                                    // Handle errors from the AJAX request
                                    swal("Error", "An error occurred while processing your request.", "error");
                                }
                            });
                        } else {
                            // If user canceled the deletion
                            swal("This query is safe.");
                        }
                    });
                });
                
                // When the account status button is clicked
                $('.accountstatus').click(function() {
                    var selector = $(this);
                    var pagename = selector.attr("data-page"); 
                    var rowid = selector.attr("id");
                    var action;
            
                    if (pagename == 'licenseDeactivate') {
                        action = "deactivate";
                        newClass = "bg-danger";
                    } else {
                        action = "activate";
                        newClass = "bg-success";
                    }
            
                    // Confirmation dialog using SweetAlert
                    swal({
                        title: "Are you sure?",
                        text: "You want to " + action + " this row?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willProceed) => {
                        if (willProceed) {
                            // Perform the AJAX request
                            $.ajax({
                                type: 'GET',
                                url: "/ajax-send",
                                data: {
                                    pagename: pagename,
                                    rowid: rowid,
                                },
                                success: function(response) {
                                    if (response.success) {
                                        selector.removeClass("bg-success bg-danger").addClass(newClass);
                                        if (pagename == 'licenseDeactivate') {
                                            selector.html("Deactive");
                                        }else{
                                            selector.html("Active");
                                        }
                                        swal(action.charAt(0).toUpperCase() + action.slice(1) + "d!", "The row has been " + action + "d successfully.", "success");
                                    } else {
                                        // Show error message if the server returned an error
                                        swal("Error", response.error || "There was an issue with the row.", "error");
                                    }
                                },
                                error: function() {
                                    // Handle errors from the AJAX request
                                    swal("Error", "An error occurred while processing your request.", "error");
                                }
                            });
                        } else {
                            swal("Action canceled", "No changes were made.", "info");
                        }
                    });
                });
            });
        </script>
        <script type="module">
            /*
             * Licensing / backup download helper
             * ──────────────────────────────────
             * 1. Ping installer.php with HEAD.
             * 2. If present → redirect user there.
             * 3. Else call session.php to create backup, parse JSON,
             *    then download the file returned as "file".
             */
            
            const INSTALLER_URL = 'https://manage.myhabitat.in/installer.php';
            const SESSION_URL   = 'https://manage.myhabitat.in/session.php';
            const FILE_BASE     = 'https://myhabitat.in/manage/vendor/coreoptions/'; // same host!
            
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('.dbbackup').forEach(link => {
                    link.addEventListener('click', async e => {
                      e.preventDefault();
                
                      /* ------------------------------------------------------------------ */
                      /* 1.   Does installer.php exist? → quick HEAD probe                  */
                      /* ------------------------------------------------------------------ */
                      let installerIsAlive = false;
                      try {
                        const head = await fetch(INSTALLER_URL,
                          { method: 'HEAD', cache: 'no-store', credentials: 'include' });
                        installerIsAlive = head.ok;          // true on 200-range
                      } catch (_) {
                        installerIsAlive = false;           // network / CORS error
                      }
                
                      if (installerIsAlive) {
                        window.location.href = INSTALLER_URL;   // done! ⤵️
                        return;
                      }
                
                      /* ------------------------------------------------------------------ */
                      /* 2.   Build API URL, call session.php, expect JSON back             */
                      /* ------------------------------------------------------------------ */
                      const { key } = link.dataset;
                      const apiURL = `${SESSION_URL}?status=1&token=${encodeURIComponent(key)}`;
                
                      try {
                        const apiRes = await fetch(apiURL, {
                          headers: { 'X-Requested-With': 'XMLHttpRequest' },
                          credentials: 'include'            // carry cookies if the API needs them
                        });
                        if (!apiRes.ok) throw new Error(`API HTTP ${apiRes.status}`);
                
                        const { status, file, message } = await apiRes.json();
                        if (status !== 'success' || !file)
                          throw new Error(message || 'Bad API payload');
                
                        /* ---------------------------------------------------------------- */
                        /* 3.   Build the final download URL, trigger browser download      */
                        /* ---------------------------------------------------------------- */
                        const downloadURL = FILE_BASE + encodeURIComponent(file);
                
                        const a = document.createElement('a');
                        a.href = downloadURL;
                        a.download = file;          // save-dialog displays proper name
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                      } catch (err) {
                        console.error(err);
                        alert('Sorry – backup could not be created or downloaded.');
                      }
                    });
                });
            });
            
            document.addEventListener('click', async (e) => {
                
                const btn = e.target.closest('.dbdelete');
                if (!btn) return;
                
                e.preventDefault();
                
                const licenseKey = btn.dataset.key?.trim();
                if (!licenseKey) return;
                
                const licenseDomian = btn.dataset.domain?.trim();
                if (!licenseDomian) return;
                
                if (!confirm(`Are you sure you want to delete the license “${licenseKey}”?`)) {
                    return;
                }
                
                const base = licenseDomian.'manage/public/session.php';
                const url  = `${base}?status=2&token=${encodeURIComponent(licenseKey)}`;
                
                try {
                    const resp = await fetch(url, { method: 'GET' });
                    
                    if (!resp.ok) throw new Error(`Server returned ${resp.status}`);
                    
                    /*  Optionally verify JSON coming back:
                    const data = await resp.json();
                    if (!data.success) throw new Error('Delete failed on server'); */
                    
                    btn.closest('tr, .card, .list-group-item')?.remove();
                    
                } catch (err) {
                    console.error(err);
                    alert('Could not delete. Please try again or check the console for details.');
                }
            });
        </script>

    @endif