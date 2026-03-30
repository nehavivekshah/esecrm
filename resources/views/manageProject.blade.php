@extends('layout')
@section('title', ($project ? 'Edit Project' : 'Add Project') . ' - eseCRM')

@section('content')
<section class="task__section">
    @include('inc.header', ['title' => $project ? 'Edit Project' : 'New Project'])

    <div class="dash-container">

        {{-- Page Header --}}
        <div class="mp-page-header mb-4">
            <div class="mp-page-header-left">
                <a href="{{ $project ? url('/project/view/'.$project->id) : url('/projects') }}"
                   class="mp-back-btn">
                    <i class="bx bx-arrow-back"></i>
                </a>
                <div>
                    <h1 class="mp-page-title">
                        {{ $project ? 'Edit Project' : 'New Project' }}
                    </h1>
                    <p class="mp-page-sub">
                        @if($project)
                            Editing <strong>{{ $project->name }}</strong>
                            · #PROU-{{ str_pad($project->id, 4, '0', STR_PAD_LEFT) }}
                        @else
                            Fill in the details to create a new project
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Validation Errors --}}
        @if($errors->any())
        <div class="mp-alert mp-alert-danger mb-4">
            <i class="bx bx-error-circle"></i>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(session('success'))
        <div class="mp-alert mp-alert-success mb-4">
            <i class="bx bx-check-circle"></i> {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="/manage-project" id="projectForm">
            @csrf
            @if($project)
                <input type="hidden" name="id" value="{{ $project->id }}">
            @endif

            <div class="row g-4">

                {{-- ── Main Form Card ── --}}
                <div class="col-lg-8">
                    <div class="mp-card">
                        <div class="mp-card-head">
                            <i class="bx bx-detail"></i> Project Information
                        </div>

                        {{-- Project Name --}}
                        <div class="mp-field">
                            <label class="mp-label" for="name">
                                Project Name <span class="mp-required">*</span>
                            </label>
                            <div class="mp-input-wrap">
                                <i class="bx bx-layer mp-input-icon"></i>
                                <input type="text" id="name" name="name"
                                       class="mp-input @error('name') is-invalid @enderror"
                                       placeholder="e.g. Website Redesign, ERP Implementation…"
                                       value="{{ old('name', $project->name ?? '') }}"
                                       required>
                            </div>
                            @error('name')
                                <div class="mp-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Client --}}
                        <div class="mp-field">
                            <label class="mp-label" for="client_id">
                                Client <span class="mp-required">*</span>
                            </label>
                            <div class="mp-input-wrap">
                                <i class="bx bx-user-circle mp-input-icon"></i>
                                <select id="client_id" name="client_id"
                                        class="mp-input mp-select @error('client_id') is-invalid @enderror"
                                        required>
                                    <option value="">— Select a client —</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}"
                                            {{ old('client_id', $project->client_id ?? '') == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}
                                            @if($client->company) ({{ $client->company }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('client_id')
                                <div class="mp-error">{{ $message }}</div>
                            @enderror
                            @if($clients->isEmpty())
                                <div class="mp-hint">
                                    <i class="bx bx-info-circle"></i>
                                    No clients found. <a href="/manage-client">Add a client first</a>.
                                </div>
                            @endif
                        </div>

                        {{-- Project Type --}}
                        <div class="mp-field">
                            <label class="mp-label" for="type">Project Type</label>
                            <div class="d-flex gap-2 flex-wrap mb-2" id="typePills">
                                @foreach(['Web Development','Mobile App','ERP','CRM','E-Commerce','Design','Maintenance','General'] as $t)
                                    <button type="button" class="mp-type-pill"
                                            data-val="{{ $t }}"
                                            onclick="selectType('{{ $t }}')">
                                        {{ $t }}
                                    </button>
                                @endforeach
                            </div>
                            <div class="mp-input-wrap">
                                <i class="bx bx-category mp-input-icon"></i>
                                <input type="text" id="type" name="type"
                                       class="mp-input"
                                       placeholder="Or type a custom category…"
                                       value="{{ old('type', $project->type ?? '') }}">
                            </div>
                        </div>

                        {{-- Contract Amount --}}
                        <div class="mp-field">
                            <label class="mp-label" for="amount">
                                Contract Amount (₹)
                            </label>
                            <div class="mp-input-wrap">
                                <span class="mp-prefix">₹</span>
                                <input type="number" id="amount" name="amount"
                                       class="mp-input mp-input-prefix @error('amount') is-invalid @enderror"
                                       placeholder="0.00" step="0.01" min="0"
                                       value="{{ old('amount', $project->amount ?? '') }}">
                            </div>
                            @error('amount')
                                <div class="mp-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Deployment URL --}}
                        <div class="mp-field">
                            <label class="mp-label" for="deployment_url">
                                Deployment / Live URL
                            </label>
                            <div class="mp-input-wrap">
                                <i class="bx bx-globe mp-input-icon"></i>
                                <input type="url" id="deployment_url" name="deployment_url"
                                       class="mp-input @error('deployment_url') is-invalid @enderror"
                                       placeholder="https://client-site.com"
                                       value="{{ old('deployment_url', $project->deployment_url ?? '') }}">
                            </div>
                            @error('deployment_url')
                                <div class="mp-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Notes --}}
                        <div class="mp-field">
                            <label class="mp-label" for="note">Notes / Description</label>
                            <textarea id="note" name="note" class="mp-input mp-textarea"
                                      placeholder="Project scope, tech stack, special requirements…"
                                      rows="4">{{ old('note', $project->note ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ── Sidebar ── --}}
                <div class="col-lg-4">

                    {{-- Save Card --}}
                    <div class="mp-card mp-save-card mb-3">
                        <button type="submit" class="mp-submit-btn" id="saveBtn">
                            <i class="bx bx-save"></i>
                            {{ $project ? 'Save Changes' : 'Create Project' }}
                        </button>
                        <a href="{{ $project ? url('/project/view/'.$project->id) : url('/projects') }}"
                           class="mp-cancel-btn">Cancel</a>
                    </div>

                    {{-- Preview Card --}}
                    <div class="mp-card mp-preview-card" id="previewCard">
                        <div class="mp-card-head"><i class="bx bx-show"></i> Preview</div>
                        <div class="mp-preview-avatar" id="prevAvatar">?</div>
                        <div class="mp-preview-name" id="prevName">Project Name</div>
                        <div class="mp-preview-type" id="prevType">Type</div>
                        <div class="mp-preview-amount" id="prevAmount">₹0</div>
                        @if($project)
                        <div class="mp-preview-meta">
                            <i class="bx bx-calendar"></i>
                            Created {{ \Carbon\Carbon::parse($project->created_at)->format('d M, Y') }}
                        </div>
                        @endif
                    </div>

                    {{-- Quick Links --}}
                    @if($project)
                    <div class="mp-card mt-3">
                        <div class="mp-card-head"><i class="bx bx-link"></i> Quick Links</div>
                        <div class="mp-quick-links">
                            <a href="/project/view/{{ $project->id }}" class="mp-qlink">
                                <i class="bx bx-show"></i> View Project
                            </a>
                            <a href="/manage-recovery?id={{ $project->id }}" class="mp-qlink">
                                <i class="bx bx-receipt"></i> Add Recovery
                            </a>
                            <a href="/manage-license" class="mp-qlink">
                                <i class="bx bx-key"></i> Manage License
                            </a>
                        </div>
                    </div>
                    @endif
                </div>

            </div>{{-- /row --}}
        </form>
    </div>
</section>

<style>
/* ── Page Header ── */
.mp-page-header { display: flex; align-items: center; }
.mp-page-header-left { display: flex; align-items: center; gap: 14px; }
.mp-back-btn {
    width: 40px; height: 40px; border-radius: 50%;
    background: #fff; border: 1px solid #e8eaed;
    display: flex; align-items: center; justify-content: center;
    color: #5f6368; font-size: 1.1rem; text-decoration: none;
    transition: all 0.15s; flex-shrink: 0;
}
.mp-back-btn:hover { background: #f1f3f4; color: #006666; }
.mp-page-title { font-size: 1.2rem; font-weight: 800; color: #202124; margin: 0; }
.mp-page-sub { font-size: 0.78rem; color: #80868b; margin: 2px 0 0; }

/* ── Alerts ── */
.mp-alert {
    display: flex; align-items: flex-start; gap: 10px;
    border-radius: 12px; padding: 12px 16px; font-size: 0.84rem;
}
.mp-alert i { font-size: 1.1rem; flex-shrink: 0; margin-top: 1px; }
.mp-alert-danger  { background: rgba(234,67,53,0.08); color: #c62828; border: 1px solid rgba(234,67,53,0.2); }
.mp-alert-success { background: rgba(52,168,83,0.08); color: #2e7d32; border: 1px solid rgba(52,168,83,0.2); }

/* ── Form Card ── */
.mp-card {
    background: #fff; border: 1px solid #e8eaed;
    border-radius: 16px; padding: 22px;
}
.mp-card-head {
    font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.6px; color: #5f6368; margin-bottom: 18px;
    display: flex; align-items: center; gap: 6px;
    border-bottom: 1px solid #f0f0f0; padding-bottom: 12px;
}
.mp-card-head i { color: #006666; font-size: 1rem; }

/* ── Fields ── */
.mp-field { margin-bottom: 18px; }
.mp-label {
    display: block; font-size: 0.78rem; font-weight: 600; color: #3c4043;
    margin-bottom: 6px; letter-spacing: 0.1px;
}
.mp-required { color: #ea4335; }
.mp-input-wrap { position: relative; }
.mp-input-icon {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    color: #80868b; font-size: 1rem; pointer-events: none;
}
.mp-input {
    width: 100%; border: 1.5px solid #e0e4e8; border-radius: 10px;
    padding: 9px 12px 9px 38px;
    font-size: 0.875rem; color: #202124; background: #fff;
    transition: border-color 0.18s, box-shadow 0.18s;
    outline: none; font-family: inherit;
}
.mp-input:focus {
    border-color: #006666;
    box-shadow: 0 0 0 3px rgba(0,102,102,0.08);
}
.mp-select { appearance: none; cursor: pointer; }
.mp-input.mp-textarea { padding-left: 12px !important; resize: vertical; }
.mp-prefix {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    color: #5f6368; font-weight: 700; font-size: 0.9rem; pointer-events: none;
}
.mp-input.mp-input-prefix { padding-left: 26px; }
.mp-error { font-size: 0.72rem; color: #ea4335; margin-top: 4px; }
.mp-hint { font-size: 0.72rem; color: #80868b; margin-top: 5px; display: flex; align-items: center; gap: 4px; }
.mp-hint a { color: #006666; }

/* ── Type Pills ── */
.mp-type-pill {
    background: #f1f3f4; border: 1.5px solid #e0e4e8; border-radius: 20px;
    padding: 4px 12px; font-size: 0.72rem; font-weight: 600; color: #5f6368;
    cursor: pointer; transition: all 0.15s;
}
.mp-type-pill:hover, .mp-type-pill.active {
    background: rgba(0,102,102,0.08); border-color: #006666; color: #006666;
}

/* ── Save Card ── */
.mp-save-card { text-align: center; padding: 20px; }
.mp-submit-btn {
    width: 100%; padding: 11px; border: none; border-radius: 12px;
    background: linear-gradient(135deg, #006666, #009688);
    color: #fff; font-size: 0.9rem; font-weight: 700;
    cursor: pointer; transition: all 0.18s; margin-bottom: 10px;
    display: flex; align-items: center; justify-content: center; gap: 7px;
}
.mp-submit-btn:hover { box-shadow: 0 6px 20px rgba(0,102,102,0.3); transform: translateY(-1px); }
.mp-cancel-btn {
    display: block; color: #80868b; font-size: 0.8rem;
    text-decoration: none; text-align: center; padding: 4px;
}
.mp-cancel-btn:hover { color: #ea4335; }

/* ── Preview Card ── */
.mp-preview-card { text-align: center; }
.mp-preview-avatar {
    width: 54px; height: 54px; border-radius: 14px;
    background: linear-gradient(135deg, #006666, #009688);
    color: #fff; font-size: 1.3rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 10px;
}
.mp-preview-name { font-size: 0.95rem; font-weight: 700; color: #202124; margin-bottom: 4px; }
.mp-preview-type {
    display: inline-block; background: rgba(0,102,102,0.08); color: #006666;
    font-size: 0.68rem; font-weight: 600; border-radius: 20px;
    padding: 2px 10px; margin-bottom: 10px;
}
.mp-preview-amount { font-size: 1.1rem; font-weight: 800; color: #006666; }
.mp-preview-meta { font-size: 0.70rem; color: #80868b; margin-top: 6px; display: flex; align-items: center; justify-content: center; gap: 4px; }

/* ── Quick Links ── */
.mp-quick-links { display: flex; flex-direction: column; gap: 4px; }
.mp-qlink {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 10px; border-radius: 8px;
    font-size: 0.80rem; color: #3c4043; text-decoration: none;
    transition: background 0.12s;
}
.mp-qlink:hover { background: #f1f3f4; color: #006666; }
.mp-qlink i { font-size: 0.95rem; color: #006666; }
</style>

<script>
$(document).ready(function () {

    // Live preview
    $('#name').on('input', function () {
        const v = $(this).val().trim();
        $('#prevName').text(v || 'Project Name');
        $('#prevAvatar').text(v ? v.charAt(0).toUpperCase() : '?');
    });
    $('#type').on('input', function () {
        $('#prevType').text($(this).val().trim() || 'Type');
        // Sync pills
        $('.mp-type-pill').removeClass('active');
        $('.mp-type-pill[data-val="' + $(this).val().trim() + '"]').addClass('active');
    });
    $('#amount').on('input', function () {
        const n = parseFloat($(this).val()) || 0;
        $('#prevAmount').text('₹' + n.toLocaleString('en-IN'));
    });

    // Trigger on load (edit mode)
    $('#name, #type, #amount').trigger('input');

    // Mark active pill if match on load
    const currentType = $('#type').val().trim();
    if (currentType) {
        $('.mp-type-pill[data-val="' + currentType + '"]').addClass('active');
    }

    // Submit loading state
    $('#projectForm').on('submit', function () {
        $('#saveBtn').html('<i class="bx bx-loader-alt bx-spin"></i> Saving…').prop('disabled', true);
    });
});

function selectType(val) {
    $('#type').val(val).trigger('input');
    $('.mp-type-pill').removeClass('active');
    $('.mp-type-pill[data-val="' + val + '"]').addClass('active');
}
</script>
@endsection
