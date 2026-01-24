@extends('layout')
@section('title','Edit Email Template - eseCRM')

@section('content')
<section class="task__section">
    <div class="text">
        <i class="bx bx-menu" id="mbtn"></i>
        Edit Email Template
        <a href="/signout" class="logoutbtn">
            <i class="bx bx-log-out"></i>
        </a>
    </div>

    <div class="container-fluid">
        <div class="board-title board-title-flex mb-2">
            <a href="{{ route('email-templates.index') }}" class="btn btn-primary btn-sm back-btn"><i class="bx bx-arrow-back"></i></a>
            <h1>Edit Template</h1>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card border-0">
                    <div class="card-body">

                        <form method="POST"
                              action="{{ route('email-templates.update', $template->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                
                                <div class="col-md-12 mb-3">
                                    <!--<strong>Available Variables:</strong> <small class="text-muted">Click on a variable to copy it.</small>-->
                                    <div id="variables" style="margin-top: 5px;">
                                        <strong>Available Variables:</strong>
                                        <span class="variable badge bg-light text-dark" data-value="@{{client_name}}" style="cursor: pointer; margin-right: 5px;">@{{client_name}}</span>
                                        <span class="variable badge bg-light text-dark" data-value="@{{company_name}}" style="cursor: pointer; margin-right: 5px;">@{{company_name}}</span>
                                        <span class="variable badge bg-light text-dark" data-value="@{{client_contract}}" style="cursor: pointer; margin-right: 5px;">@{{client_contract}}</span>
                                        <span class="variable badge bg-light text-dark" data-value="@{{invoice_number}}" style="cursor: pointer; margin-right: 5px;">@{{invoice_number}}</span>
                                        <span class="variable badge bg-light text-dark" data-value="@{{amount}}" style="cursor: pointer; margin-right: 5px;">@{{amount}}</span>
                                        <span class="variable badge bg-light text-dark" data-value="@{{due_date}}" style="cursor: pointer; margin-right: 5px;">@{{due_date}}</span>
                                        <span class="variable badge bg-light text-dark" data-value="@{{end_date}}" style="cursor: pointer; margin-right: 5px;">@{{end_date}}</span>
                                    </div>
                                </div>

                                <!-- Module -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Module</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-layer"></i></span>
                                        <input type="text"
                                               class="form-control"
                                               value="{{ ucfirst($template->module) }}"
                                               disabled>
                                    </div>
                                </div>

                                <!-- Event -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Event</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-calendar-event"></i></span>
                                        <input type="text"
                                               name="event"
                                               class="form-control"
                                               value="{{ $template->event }}"
                                               required>
                                    </div>
                                </div>

                                <!-- Subject -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Subject</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                                        <input type="text"
                                               name="subject"
                                               class="form-control"
                                               value="{{ $template->subject }}"
                                               required>
                                    </div>
                                </div>

                                <!-- Email Body -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Email Body</label>
                                    <div class="input-group">
                                        <!--<span class="input-group-text"><i class="bx bx-message-square-detail"></i></span>-->
                                        <textarea name="body"
                                                  rows="8"
                                                  class="form-control"
                                                  required>{{ $template->body }}</textarea>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-toggle-right"></i></span>
                                        <select name="is_active" class="form-control">
                                            <option value="1" {{ $template->is_active ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !$template->is_active ? 'selected' : '' }}>Deactive</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Reminder Days -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Send Reminder Before Event (Days)</label>
                                    <div id="reminder-days" class="row">
                                        @if(old('reminder_days', $template->reminder_days ?? []))
                                            @foreach(old('reminder_days', $template->reminder_days ?? []) as $day)
                                                <div class="col-md-2">
                                                    <div class="input-group mb-2">
                                                        <span class="input-group-text"><i class="bx bx-time"></i></span>
                                                        <input type="number" name="reminder_days[]" class="form-control" value="{{ $day }}" min="0">
                                                        <button type="button" class="btn btn-danger remove-day"><i class="bx bx-minus"></i></button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="col-md-2">
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text"><i class="bx bx-time"></i></span>
                                                    <input type="number" name="reminder_days[]" class="form-control" placeholder="Days before event" min="0">
                                                    <button type="button" class="btn btn-danger remove-day"><i class="bx bx-minus"></i></button>
                                                    <button type="button" class="btn btn-primary" id="add-day"><i class="bx bx-plus"></i></button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="col-md-12 text-right">
                                    <button class="btn btn-success bg-success text-white">
                                        <i class="bx bx-save"></i> Update Template
                                    </button>
                                </div>

                            </div>
                        </form>

                        <!--<div class="col-md-12 mt-3">
                            <strong>Available Variables:</strong> 
                            @{{client_name}}, @{{company_name}}, @{{contract_number}},
                            @{{invoice_number}}, @{{amount}}, @{{due_date}}, @{{end_date}}
                        </div>-->

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Add new reminder day input
    document.getElementById('add-day').addEventListener('click', function() {
        let container = document.getElementById('reminder-days');
        let div = document.createElement('div');
        div.classList.add('col-md-2');
        div.innerHTML = `
        <div class="input-group mb-3">
            <span class="input-group-text"><i class="bx bx-time"></i></span>
            <input type="number" name="reminder_days[]" class="form-control" placeholder="Days before event" min="0">
            <button type="button" class="btn btn-danger remove-day"><i class="bx bx-minus"></i></button>
        </div>
        `;
        container.appendChild(div);
    });

    // Remove reminder day input
    document.addEventListener('click', function(e){
        if(e.target && e.target.classList.contains('remove-day')){
            e.target.closest('.col-md-2').remove();
        }
    });
    
    // Click-to-copy functionality for variables
    document.querySelectorAll('.variable').forEach(function(el){
        el.addEventListener('click', function() {
            const value = el.getAttribute('data-value');
            navigator.clipboard.writeText(value).then(() => {
                // Optional: show a temporary message
                const original = el.innerHTML;
                el.innerHTML = 'Copied!';
                setTimeout(() => el.innerHTML = original, 1000);
            });
        });
    });
</script>
@endsection
