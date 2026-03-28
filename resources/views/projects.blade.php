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
            {{-- Toolbar --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <form action="/projects" method="GET" id="projectFilterForm" class="d-flex align-items-center gap-2">
                        <div class="lb-search-box">
                            <i class="bx bx-search"></i>
                            <input type="text" name="search" id="projectSearch" placeholder="Search projects..." value="{{ $search ?? '' }}">
                        </div>
                    </form>
                </div>
                <div class="leads-toolbar-right">
                    <button class="lb-icon-btn" onclick="location.reload()" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                    @if(in_array('clients_add', $roleArray) || in_array('All', $roleArray))
                        <a href="/manage-project" class="lb-btn lb-btn-primary">
                            <i class="bx bx-plus"></i>
                            <span class="d-none d-sm-inline">Add Project</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Table Card --}}
            <div class="dash-card mb-4">
                <div class="table-responsive">
                    <table id="projectList" class="leads-table projects" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Project Name</th>
                                <th class="m-none">Client / Company</th>
                                <th class="m-none mw80">Type</th>
                                <th class="m-none mw135">Total Amount</th>
                                <th class="text-center position-sticky end-0 bg-default mw60">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $project)
                            <tr class="selectrow pointer-cursor project-row" onclick="window.location.href='/project/view/{{ $project->id }}'" id="{{ $project->id }}">
                                
                                {{-- Project Name --}}
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="lb-avatar-sm" style="background: var(--teal-gradient);">
                                            {{ strtoupper(substr($project->name, 0, 1)) }}
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-500">{{ $project->name }}</span>
                                            <span class="small text-muted">ID: #PROU-{{ str_pad($project->id, 4, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Client / Company --}}
                                <td class="m-none text-muted">
                                    <div class="d-flex flex-column">
                                        <span class="fw-500 text-dark">{{ substr(($project->client_name ?? ''), 0, 22) }}</span>
                                        <span class="small">{{ substr(($project->client_company ?? ''), 0, 22) }}</span>
                                    </div>
                                </td>

                                {{-- Type --}}
                                <td class="m-none">
                                    <span class="leads-status-badge" style="background: rgba(0, 102, 102, 0.1); color: #006666;">
                                        {{ $project->type ?? 'General' }}
                                    </span>
                                </td>

                                {{-- Amount --}}
                                <td class="m-none fw-bold text-primary">
                                    ₹{{ number_format($project->amount, 2) }}
                                </td>

                                {{-- Action --}}
                                <td class="position-sticky end-0 bg-white" onclick="event.stopPropagation();">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        @if($project->deployment_url)
                                            <a href="{{ $project->deployment_url }}" target="_blank" class="kb-action-btn kb-action-call" title="Visit Site">
                                                <i class="bx bx-link-external"></i>
                                            </a>
                                        @endif
                                        <a href="/manage-project?id={{ $project->id }}" class="kb-action-btn kb-action-edit" title="Edit">
                                            <i class="bx bx-pencil"></i>
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