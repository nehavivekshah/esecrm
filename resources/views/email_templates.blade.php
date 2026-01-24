@extends('layout')
@section('title','Email Templates - eseCRM')

@section('content')
<section class="task__section">
    <div class="text">
        <i class="bx bx-menu" id="mbtn"></i> 
        Email Templates
        <a href="/signout" class="logoutbtn">
            <i class="bx bx-log-out"></i>
        </a>
    </div>

    <div class="container-fluid">
        <div class="board-title board-title-flex">
            <h1>List Board</h1>
            <div class="btn-group">
                <a href="{{ route('email-templates.create') }}"
                   class="btn btn-primary bg-primary text-white btn-sm">
                    <i class="bx bx-plus"></i> <span>Add New</span>
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 py-3 table-responsive">
                <table id="lists"
                   class="table table-condensed m-table"
                   style="width:100%;border-radius:5px!important;overflow:hidden;">
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Event</th>
                        <th class="m-none">Subject</th>
                        <th>Reminder Days</th> <!-- new column -->
                        <th>Status</th>
                        <th class="wpx-100 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $template)
                    <tr>
                        <td>{{ ucfirst($template->module) }}</td>
                        <td>{{ ucfirst($template->event) }}</td>
                        <td class="m-none">{{ $template->subject }}</td>
            
                        <!-- Display Reminder Days -->
                        <td>
                            @if(!empty($template->reminder_days))
                                {{ implode(', ', $template->reminder_days) }} day(s)
                            @else
                                --
                            @endif
                        </td>
            
                        <td>
                            @if($template->is_active)
                                <span class="font-weight-bold text-success">Active</span>
                            @else
                                <span class="font-weight-bold text-danger">Deactive</span>
                            @endif
                        </td>
            
                        <td class="text-center">
                            <a href="{{ route('email-templates.edit', $template->id) }}"
                               class="btn btn-info btn-sm"
                               title="Edit">
                                <i class="bx bx-edit"></i>
                            </a>
            
                            <form action="{{ route('email-templates.toggle', $template->id) }}"
                                  method="POST"
                                  class="d-inline">
                                @csrf
                                <button type="submit"
                                        class="btn btn-sm {{ $template->is_active ? 'btn-warning' : 'btn-success bg-success text-white' }}"
                                        title="Toggle Status">
                                    <i class="bx bx-power-off"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
            
                    @if($templates->count() == 0)
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            No email templates found
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
            </div>
        </div>
    </div>
</section>
@endsection
