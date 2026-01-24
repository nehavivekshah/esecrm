@extends('layout')
@section('title', isset($license) ? 'Edit License - eseCRM' : 'Add New License - eseCRM')

@section('content')

@php
    $sessionroles = session('roles');
    $roleArray    = explode(',', ($sessionroles->permissions ?? ''));
@endphp

<section class="task__section">
    <div class="text">
        <i class="bx bx-menu" id="mbtn"></i>
        @if(Request::segment(1) !== 'my-license') Manage License @else My License @endif
        <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
    </div>

    <div class="container-fluid">
        <div class="board-title board-title-flex mb-2">
            @if(Request::segment(1) !== 'my-license')
                <a href="/licensing" class="btn btn-primary bg-primary text-white btn-sm back-btn"><i class="bx bx-arrow-back"></i></a>
                @if(request()->filled('id')) <h1>Edit License</h1> @else <h1>Add New License</h1> @endif
            @else
                <h1>Edit Details</h1>
            @endif
        </div>

        <div class="row g-3 px-2">
            <div class="col-md-12 bg-white py-3 px-4 rounded">
                <form action="{{ route('manageLicense') }}" method="post" class="row" enctype="multipart/form-data">
                    @csrf

                    {{-- Select Project --}}
                    <div class="form-group col-md-3 col-sm-6">
                        <label for="project_id">Select Project</label>
                        <select class="form-control" id="project_id" name="project_id">
                            <option value="">Select Assigned Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" 
                                    data-name="{{ $project->client_name ?? '' }}"
                                    data-company="{{ $project->company ?? '' }}"
                                    data-mobile="{{ $project->mob ?? '' }}"
                                    data-email="{{ $project->email ?? '' }}"
                                    data-projectname="{{ $project->name ?? '' }}"
                                    data-type="{{ $project->type ?? '' }}"
                                    data-cost="{{ $project->amount ?? '' }}"
                                    data-website="{{ $project->website ?? '' }}"
                                    data-note="{{ $project->note ?? '' }}"
                                    {{ (old('project_id', $license->project_id ?? '') == $project->id) ? 'selected' : '' }}>
                                    {{ $project->client_name ?? '' }} - {{ $project->name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Input Fields --}}
                    <div class="form-group col-md-3 col-sm-6">
                        <label for="name">Name*</label>
                        <input type="hidden" name="id" value="{{ $_GET['id'] ?? '' }}">
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $license->client_name ?? '') }}">
                    </div>

                    <div class="form-group col-md-3 col-sm-6">
                        <label for="company">Company (Optional)</label>
                        <input type="text" class="form-control" id="company" name="company" value="{{ old('company', $license->company ?? '') }}">
                    </div>

                    <div class="form-group col-md-3 col-sm-6">
                        <label for="mobile">Mobile*</label>
                        <input type="text" class="form-control" id="mobile" name="mobile" value="{{ old('mob', $license->mob ?? '') }}">
                    </div>

                    <div class="form-group col-md-3 col-sm-6">
                        <label for="email">Email*</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $license->email ?? '') }}">
                    </div>

                    <div class="form-group col-md-3 col-sm-6">
                        <label for="project_name">Project Name*</label>
                        <input type="text" class="form-control" id="project_name" name="project_name" value="{{ old('name', $license->name ?? '') }}">
                    </div>

                    <div class="form-group col-md-3 col-sm-6">
                        <label for="type">Type</label>
                        <input type="text" class="form-control" id="type" name="type" value="{{ old('type', $license->type ?? '') }}">
                    </div>

                    <div class="form-group col-md-3 col-sm-6">
                        <label for="cost">Cost</label>
                        <input type="number" step="0.01" class="form-control" id="cost" name="cost" value="{{ old('amount', $license->amount ?? '') }}">
                    </div>

                    <div class="form-group col-md-3 col-sm-6">
                        <label for="website">Website*</label>
                        <input type="url" class="form-control" id="website" name="website" value="{{ old('deployment_url', $license->deployment_url ?? '') }}" required>
                    </div>

                    <div class="form-group col-md-3 col-sm-6">
                        <label for="website">Technology Stack*</label>
                        <select class="form-control" id="technology_stack" name="technology_stack" required>
                            <option class="wordpress">Wordpres</option>
                            <option class="laravel">Laravel</option>
                            <option class="core_php">Core PHP</option>
                        </select>
                    </div>

                    <div class="form-group col-md-6 col-sm-12">
                        <label for="note">Note</label>
                        <textarea class="form-control" id="note" name="note">{{ old('note', $license->note ?? '') }}</textarea>
                    </div>

                    {{-- License Key --}}
                    <div class="form-group col-md-3 col-sm-6">
                        <label for="license_key">License Key*</label>
                        <input type="text" class="form-control" id="license_key" name="license_key"
                               placeholder="Enter License Key" value="{{ old('license_key', $license->license_key ?? '') }}" required readonly>
                        <button type="button" class="btn btn-secondary mt-2" id="generate_license_key">Generate License Key</button>
                    </div>

                    {{-- Expiry Date --}}
                    <div class="form-group col-md-3 col-sm-6">
                        <label for="expiry_date">Expiry Date</label>
                        <input type="date" class="form-control" id="expiry_date" name="expiry_date"
                               value="{{ old('expiry_date', $license->expiry_date ?? '') }}">
                    </div>

                    {{-- Status --}}
                    <div class="form-group col-md-3 col-sm-6">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="active" {{ (old('status', $license->status ?? '') == 'active') ? 'selected' : '' }}>Active</option>
                            <option value="blocked" {{ (old('status', $license->status ?? '') == 'blocked') ? 'selected' : '' }}>Blocked</option>
                        </select>
                    </div>

                    <div class="form-group text-right col-md-12 mt-2">
                        <button type="submit" class="btn btn-primary bg-primary text-white px-4">Submit</button>
                        <button type="reset" class="btn btn-light border px-4">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

{{-- JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const projectSelect = document.getElementById('project_id');
    
    projectSelect.addEventListener('change', function() {
        let selected = this.options[this.selectedIndex];
        document.getElementById('name').value = selected.getAttribute('data-name') || '';
        document.getElementById('company').value = selected.getAttribute('data-company') || '';
        document.getElementById('mobile').value = selected.getAttribute('data-mobile') || '';
        document.getElementById('email').value = selected.getAttribute('data-email') || '';
        document.getElementById('project_name').value = selected.getAttribute('data-projectname') || '';
        document.getElementById('type').value = selected.getAttribute('data-type') || '';
        document.getElementById('cost').value = selected.getAttribute('data-cost') || '';
        document.getElementById('website').value = selected.getAttribute('data-website') || '';
        document.getElementById('note').value = selected.getAttribute('data-note') || '';
    });

    const generateLicenseKeyButton = document.getElementById('generate_license_key');
    const licenseKeyInput = document.getElementById('license_key');

    function generateLicenseKey() {
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let licenseKey = '';
        for (let i = 0; i < 16; i++) {
            licenseKey += characters.charAt(Math.floor(Math.random() * characters.length));
        }
        licenseKeyInput.value = licenseKey;
    }

    generateLicenseKeyButton.addEventListener('click', generateLicenseKey);
    if (!licenseKeyInput.value) {
        generateLicenseKey();
    }
});
</script>

@endsection
