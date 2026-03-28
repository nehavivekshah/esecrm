@extends('layout')
@section('title','Customers - eseCRM')

@section('content')
    @php
    
        $roles = session('roles');
        $roleArray = explode(',',($roles->permissions ?? ''));
    
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/css/lead-panel.css') }}">

    <section class="task__section">
        @include('inc.header', ['title' => 'Projects'])
        
        <div class="dash-container">
            {{-- Toolbar --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <form action="/projects" method="GET" id="projectFilterForm" class="d-flex align-items-center gap-2">
                        <div class="lb-search-box">
                            <i class="bx bx-search"></i>
                            <input type="text" name="search" id="projectSearch" class="form-control" placeholder="Search projects..." value="{{ $search ?? '' }}">
                        </div>
                    </form>
                </div>
                <div class="leads-toolbar-right">
                    @if(in_array('clients_add', $roleArray) || in_array('All', $roleArray))
                        <a href="/manage-project" class="lb-btn-primary">
                            <i class="bx bx-plus"></i>
                            <span>Add Project</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 py-3 table-responsive">
                    <table id="projectList" class="table table-condensed m-table leads" style="width:100%; border-radius: 8px; overflow: hidden; background: #fff;">
                        <thead>
                            <tr>
                                <th width="10">#</th>
                                <th>Project Name</th>
                                <th>Client / Company</th>
                                <th>Type</th>
                                <th>Total Amount</th>
                                <th width="100">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $project)
                            <tr class="selectrow pointer-cursor project-row" onclick="window.location.href='/project/view/{{ $project->id }}'" id="{{ $project->id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="lb-avatar-sm" style="background: var(--teal-gradient);">
                                            {{ substr($project->name, 0, 1) }}
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">{{ $project->name }}</span>
                                            <span class="small text-muted">ID: #PROU-{{ str_pad($project->id, 4, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span>{{ $project->client_name }}</span>
                                        <span class="small text-muted">{{ $project->client_company }}</span>
                                    </div>
                                </td>
                                <td><span class="badge bg-soft-info text-info">{{ $project->type ?? 'General' }}</span></td>
                                <td class="fw-bold text-primary">₹{{ number_format($project->amount, 2) }}</td>
                                <td>
                                    <div class="table-btn">
                                        @if($project->deployment_url)
                                            <a href="{{ $project->deployment_url }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Visit Site">
                                                <i class="bx bx-link-external"></i>
                                            </a>
                                        @endif
                                        <a href="/manage-project?id={{ $project->id }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <script>
        $(document).ready(function(){
            // Project Search Filter (Debounced)
            let searchTimer;
            $('#projectSearch').on('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    $('#projectFilterForm').submit();
                }, 500);
            });
        });
    </script>

@endsection