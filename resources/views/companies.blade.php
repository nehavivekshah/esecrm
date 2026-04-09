@extends('layout')
@section('title', 'Companies - eseCRM')

@section('content')
    @php
        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));

        // Aggregate stats
        $totalCompanies = $companies->count();
        $activeCompanies = $companies->where('status', '1')->count();
        $inactiveCompanies = $companies->where('status', '0')->count();
        $gstCompanies = $companies->whereNotNull('gst')->where('gst', '!=', '')->count();
    @endphp

    <section class="task__section">
        @include('inc.header', ['title' => 'Companies'])

        <div class="dash-container">

            {{-- ── Stat Cards Row ── --}}
            <div class="pj-stat-row mb-4">
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(0,102,102,0.1);color:#006666;">
                        <i class="bx bx-building"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num">{{ $totalCompanies }}</div>
                        <div class="pj-stat-label">Total Companies</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(52,168,83,0.1);color:#34a853;">
                        <i class="bx bx-check-circle"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#34a853;">{{ $activeCompanies }}</div>
                        <div class="pj-stat-label">Active Companies</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(234,67,53,0.1);color:#ea4335;">
                        <i class="bx bx-minus-circle"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#ea4335;">{{ $inactiveCompanies }}</div>
                        <div class="pj-stat-label">Inactive</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(26,115,232,0.1);color:#1a73e8;">
                        <i class="bx bx-file"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#1a73e8;">{{ $gstCompanies }}</div>
                        <div class="pj-stat-label">GST Registered</div>
                    </div>
                </div>
            </div>

            {{-- ── Toolbar ── --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <form action="/companies" method="GET" id="companyFilterForm" class="d-flex align-items-center gap-2">
                        <select name="status" id="companyStatusFilter" class="form-select" onchange="this.form.submit()"
                            style="width: auto; min-width: 140px;">
                            <option value="">All Status</option>
                            <option value="1" {{ ($status ?? '') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ ($status ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </form>

                    <span class="lb-page-count">
                        {{ $totalCompanies }} {{ $totalCompanies == 1 ? 'Company' : 'Companies' }}
                    </span>
                </div>
                <div class="leads-toolbar-right gap-2">
                    {{-- View Toggle --}}
                    <div class="pj-view-toggle">
                        <button class="pj-view-btn" id="cardViewBtn" title="Card View" onclick="setView('card')">
                            <i class="bx bx-grid-alt"></i>
                        </button>
                        <button class="pj-view-btn active" id="tableViewBtn" title="Table View" onclick="setView('table')">
                            <i class="bx bx-list-ul"></i>
                        </button>
                    </div>
                    <button class="lb-icon-btn" onclick="location.reload()" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                    @if(in_array('All', $roleArray))
                        <button type="button" class="lb-btn lb-btn-primary" onclick="openManageCompanyModal()">
                            <i class="bx bx-plus"></i>
                            <span class="d-none d-sm-inline">Add Company</span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- ════════════════════════════════
            CARD VIEW
            ════════════════════════════════ --}}
            <div id="cardView" class="pj-card-grid mb-4" style="display:none;">
                @forelse($companies as $company)
                    <div class="pj-card" onclick="openManageCompanyModal('{{ $company->id }}')">
                        {{-- Top accent --}}
                        <div class="pj-card-accent" style="background: linear-gradient(90deg, #006666, #009688);"></div>

                        {{-- Header --}}
                        <div class="pj-card-header">
                            <div class="pj-card-avatar">
                                @if($company->logo)
                                    <img src="{{ asset('assets/images/company/logos/' . $company->logo) }}" alt="" style="width:100%; height:100%; border-radius:12px; object-fit:contain;">
                                @else
                                    {{ strtoupper(substr($company->name, 0, 1)) }}
                                @endif
                            </div>
                            <div class="pj-card-meta">
                                <div class="pj-card-name">{{ $company->name }}</div>
                                <div class="pj-card-id">
                                    {{ $company->industry ?? 'Global Company' }}
                                </div>
                            </div>
                            <div class="pj-card-actions" onclick="event.stopPropagation();">
                                <button type="button" onclick="openManageCompanyModal('{{ $company->id }}')" class="btn kb-action-btn" title="Edit"
                                    style="background:rgba(0,102,102,0.08);color:#006666;">
                                    <i class="bx bx-pencil"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Details --}}
                        <div class="pj-card-info mt-2">
                            <div class="pj-info-row">
                                <i class="bx bx-envelope"></i>
                                <span>{{ $company->email ?? 'No email' }}</span>
                            </div>
                            <div class="pj-info-row">
                                <i class="bx bx-phone"></i>
                                <span>{{ $company->mob ?? 'No phone' }}</span>
                            </div>
                            <div class="pj-info-row">
                                <i class="bx bx-map"></i>
                                <span>{{ $company->city }}{{ $company->state ? ', '.$company->state : '' }}</span>
                            </div>
                        </div>

                        {{-- Footer Badge --}}
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            @if($company->gst)
                                <span class="badge bg-light text-primary border" style="font-size:0.65rem;">GST: {{ $company->gst }}</span>
                            @endif
                            @if($company->status == 1)
                                <span class="pv-badge pv-badge-success">Active</span>
                            @else
                                <span class="pv-badge pv-badge-danger">Inactive</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="pj-empty" style="grid-column:1/-1;">
                        <i class="bx bx-building"></i>
                        <p>No companies found.</p>
                        @if(in_array('All', $roleArray))
                            <a href="/manage-company" class="lb-btn lb-btn-primary mt-2"><i class="bx bx-plus"></i> Add Company</a>
                        @endif
                    </div>
                @endforelse
            </div>

            {{-- ════════════════════════════════
            TABLE VIEW
            ════════════════════════════════ --}}
            <div id="tableView" class="dash-card mb-4"
                style="background: #fff; border: 1px solid #e8eaed; border-radius: 12px; overflow: hidden;">
                <div class="table-responsive">
                    <table class="leads-table projects align-middle" id="lists" style="width:100%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Company Details</th>
                                <th class="m-none">Contact</th>
                                <th class="m-none">Tax / GST</th>
                                <th class="m-none">Location</th>
                                <th class="text-center">Status</th>
                                <th class="text-center position-sticky end-0 mw60" data-orderable="false"
                                    style="z-index:1;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($companies as $k=>$company)
                                <tr class="pointer-cursor selectrow"
                                    onclick="openManageCompanyModal('{{ $company->id }}')">
                                    <td class="fw-bold text-muted" style="font-size:0.75rem;">
                                        {{ $k+1 }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="lb-avatar-sm"
                                                style="background:linear-gradient(135deg,#006666,#009688);color:#fff; overflow:hidden;">
                                                @if($company->logo)
                                                    <img src="{{ asset('assets/images/company/logos/' . $company->logo) }}" alt="" style="width:100%; height:100%; object-fit:contain; background:#fff;">
                                                @else
                                                    {{ strtoupper(substr($company->name, 0, 1)) }}
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <div class="fw-600 text-truncate" style="max-width:200px;">{{ $company->name }}</div>
                                                <div class="small text-muted text-truncate" style="max-width:200px;">{{ $company->industry ?? 'Business Services' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="m-none">
                                        <div class="small fw-500">{{ $company->email }}</div>
                                        <div class="small text-muted">{{ $company->mob }}</div>
                                    </td>
                                    <td class="m-none">
                                        @if($company->gst)
                                            <div class="badge bg-light text-dark border fw-normal" style="font-size:0.7rem;">{{ $company->gst }}</div>
                                        @else
                                            <span class="text-muted small">Not provided</span>
                                        @endif
                                    </td>
                                    <td class="m-none">
                                        <div class="small">{{ $company->city }}</div>
                                        <div class="small text-muted">{{ $company->state }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($company->status == 1)
                                            <span class="pv-badge pv-badge-success accountstatus" id="{{ $company->id }}" data-page="companyDeactivate">Active</span>
                                        @else
                                            <span class="pv-badge pv-badge-danger accountstatus" id="{{ $company->id }}" data-page="companyActivate">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="position-sticky end-0 bg-white" onclick="event.stopPropagation();">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <button type="button" onclick="openManageCompanyModal('{{ $company->id }}')"
                                                class="btn kb-action-btn kb-action-edit" title="Edit">
                                                <i class="bx bx-pencil"></i>
                                            </button>
                                            <a href="javascript:void(0)" class="btn kb-action-btn kb-action-del delete"
                                                id="{{ $company->id }}" date-page="companyDelete" title="Delete">
                                                <i class="bx bx-trash"></i>
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

    <style>
        /* ── Project Stat Cards (Reused) ── */
        .pj-stat-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        @media (max-width: 768px) {
            .pj-stat-row {  grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 480px) {
            .pj-stat-row { grid-template-columns: 1fr; }
        }

        .pj-stat-card {
            background: #fff;
            border: 1px solid #e8eaed;
            border-radius: 14px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: box-shadow 0.18s;
        }

        .pj-stat-card:hover {  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08); }

        .pj-stat-icon {
            width: 46px; height: 46px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; flex-shrink: 0;
        }

        .pj-stat-num { font-size: 1.2rem; font-weight: 700; color: #202124; line-height: 1.2; }
        .pj-stat-label { font-size: 0.72rem; color: #80868b; font-weight: 500; margin-top: 2px; }

        /* ── View Toggle ── */
        .pj-view-toggle {
            display: flex; gap: 3px; background: #f1f3f4; border-radius: 20px; padding: 3px;
        }

        .pj-view-btn {
            width: 30px; height: 30px; border: none; background: transparent;
            border-radius: 17px; cursor: pointer; color: #80868b;
            font-size: 1rem; display: flex; align-items: center; justify-content: center;
            transition: all 0.15s;
        }

        .pj-view-btn.active { background: #fff; color: #006666; box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12); }

        /* ── Card Grid ── */
        .pj-card-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;
        }

        .pj-card {
            background: #fff; border: 1px solid #e8eaed; border-radius: 16px;
            overflow: hidden; cursor: pointer; transition: box-shadow 0.2s, transform 0.18s;
            position: relative; padding: 0 16px 16px;
        }

        .pj-card:hover { box-shadow: 0 8px 28px rgba(0, 0, 0, 0.10); transform: translateY(-2px); }
        .pj-card-accent { height: 4px; margin: 0 -16px 14px; }

        .pj-card-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .pj-card-avatar {
            width: 42px; height: 42px; border-radius: 12px;
            background: linear-gradient(135deg, #006666, #009688);
            color: #fff; font-size: 1.1rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }

        .pj-card-meta { flex: 1; min-width: 0; }
        .pj-card-name { font-size: 0.9rem; font-weight: 700; color: #202124; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .pj-card-id { font-size: 0.68rem; color: #80868b; }

        .pj-card-info { display: flex; flex-direction: column; gap: 6px; }
        .pj-info-row { display: flex; align-items: center; gap: 8px; font-size: 0.78rem; color: #5f6368; }
        .pj-info-row i { font-size: 0.95rem; color: #006666; }

        .pj-empty { text-align: center; padding: 60px 20px; color: #9aa0a6; }
        .pj-empty i { font-size: 3rem; display: block; margin-bottom: 12px; color: #dadce0; }
        .pj-empty p { font-size: 0.85rem; margin: 0; }

        .pv-badge {
            padding: 3px 10px; border-radius: 20px; font-size: 0.68rem; font-weight: 600;
        }
        .pv-badge-success { background: rgba(52,168,83,0.1); color: #34a853; }
        .pv-badge-danger { background: rgba(234,67,53,0.1); color: #ea4335; }
        .pv-badge-info { background: rgba(26,115,232,0.1); color: #1a73e8; }
    </style>

    <script>
        function setView(view) {
            const cardView = document.getElementById('cardView');
            const tableView = document.getElementById('tableView');
            const cardBtn = document.getElementById('cardViewBtn');
            const tableBtn = document.getElementById('tableViewBtn');

            if (view === 'card') {
                cardView.style.display = 'grid';
                tableView.style.display = 'none';
                cardBtn.classList.add('active');
                tableBtn.classList.remove('active');
                localStorage.setItem('company_view_pref', 'card');
            } else {
                cardView.style.display = 'none';
                tableView.style.display = 'block';
                cardBtn.classList.remove('active');
                tableBtn.classList.add('active');
                localStorage.setItem('company_view_pref', 'table');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const pref = localStorage.getItem('company_view_pref');
            if (pref === 'card') {
                setView('card');
            }
        });

        const gstInput = document.getElementById('c_gst');
        const vatInput = document.getElementById('c_vat');
        const taxFields = document.querySelectorAll('.tax-rate-field');

        function refreshTaxVisibility() {
            const hasGST = gstInput.value.trim() !== '';
            const hasVAT = vatInput.value.trim() !== '';

            taxFields.forEach(field => {
                const type = field.getAttribute('data-tax');
                if (['CGST', 'SGST', 'IGST'].includes(type)) {
                    field.style.display = hasGST ? 'flex' : 'none';
                } else if (type === 'VAT') {
                    field.style.display = hasVAT ? 'flex' : 'none';
                }
            });
        }

        if (gstInput) gstInput.addEventListener('input', refreshTaxVisibility);
        if (vatInput) vatInput.addEventListener('input', refreshTaxVisibility);

        function openManageCompanyModal(id = null) {
            const modalEl = document.getElementById('manageCompanyModal');
            const modal = new bootstrap.Modal(modalEl);
            const form = document.getElementById('manageCompanyForm');
            const modalTitle = document.getElementById('modalTitle');
            const submitBtnText = document.getElementById('submitBtnText');

            form.reset();
            document.getElementById('company_id').value = '';
            document.getElementById('logoPreviewWrap').style.display = 'none';
            document.getElementById('logoDefaultIcon').style.display = 'flex';
            document.getElementById('pdfLogoPreviewWrap').style.display = 'none';
            document.getElementById('pdfLogoDefaultIcon').style.display = 'flex';
            
            if (id) {
                modalTitle.innerText = 'Edit Company Details';
                submitBtnText.innerText = 'Update Company';
                
                // Fetch data
                fetch(`/get-company-details/${id}`)
                    .then(r => r.json())
                    .then(data => {
                        const c = data.company;
                        document.getElementById('company_id').value = c.id;
                        document.getElementById('c_name').value = c.name || '';
                        document.getElementById('c_email').value = c.email || '';
                        document.getElementById('c_mob').value = c.mob || '';
                        document.getElementById('c_gst').value = c.gst || '';
                        document.getElementById('c_vat').value = c.vat || '';
                        document.getElementById('c_address').value = c.address || '';
                        document.getElementById('c_city').value = c.city || '';
                        document.getElementById('c_state').value = c.state || '';
                        document.getElementById('c_zipcode').value = c.zipcode || '';
                        document.getElementById('c_country').value = c.country || '';
                        document.getElementById('c_industry').value = c.industry || '';
                        document.getElementById('c_website').value = c.website || '';
                        
                        // Tax rates
                        if (data.tax_rates) {
                            data.tax_rates.forEach((rate, i) => {
                                const el = document.getElementById(`rate_${i}`);
                                if (el) el.value = rate;
                            });
                        }

                        // Bank details
                        if (data.bank_details) {
                            data.bank_details.forEach((val, i) => {
                                const el = document.getElementById(`bank_${i}`);
                                if (el) el.value = val;
                            });
                        }

                        // Logo preview
                        if (c.logo) {
                            document.getElementById('logoPreview').src = `/assets/images/company/logos/${c.logo}`;
                            document.getElementById('logoPreviewWrap').style.display = 'flex';
                            document.getElementById('logoDefaultIcon').style.display = 'none';
                        }
                        
                        // PDF Logo preview
                        if (c.pdf_logo) {
                            document.getElementById('pdfLogoPreview').src = `/assets/images/company/${c.pdf_logo}`;
                            document.getElementById('pdfLogoPreviewWrap').style.display = 'flex';
                            document.getElementById('pdfLogoDefaultIcon').style.display = 'none';
                        }
                        
                        // Plan (master only)
                        const planEl = document.getElementById(`plan_${c.plan || 'standard'}`);
                        if (planEl) planEl.checked = true;

                        refreshTaxVisibility();
                        modal.show();
                    });
            } else {
                modalTitle.innerText = 'Add New Company';
                submitBtnText.innerText = 'Save Company';
                refreshTaxVisibility();
                modal.show();
            }
        }
    </script>

    {{-- ════════════════════════════════════════════════════════════════
         MANAGE COMPANY MODAL
    ════════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="manageCompanyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius:16px; border:none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <form action="/manage-company" method="POST" enctype="multipart/form-data" id="manageCompanyForm">
                    @csrf
                    <input type="hidden" name="id" id="company_id">

                    <div class="cf-modal-header">
                        <div>
                            <p class="cf-modal-header-title" id="modalTitle">Add New Company</p>
                            <p class="cf-modal-header-sub">Configure your company identity and settings</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body px-4 py-3 cf-wrap" style="max-height:70vh; overflow-y:auto; background:#f8fdfd;">
                        
                        {{-- Identity Section --}}
                        <div class="cf-section-title">Identity & Branding</div>
                        <div class="row g-3">
                            <div class="col-md-4 cf-field">
                                <label>Company Name <span class="req">*</span></label>
                                <div class="cf-input-box">
                                    <span class="cf-icon"><i class="bx bx-building"></i></span>
                                    <input type="text" name="name" id="c_name" placeholder="Enter Company Name" required>
                                </div>
                            </div>
                            <div class="col-md-4 cf-field">
                                <label>Main Logo</label>
                                <div class="cf-input-box">
                                    <span class="cf-icon" id="logoPreviewWrap" style="padding:4px; display:none;">
                                        <img id="logoPreview" src="" style="width:100%; height:100%; border-radius:6px; object-fit:contain;">
                                    </span>
                                    <span class="cf-icon" id="logoDefaultIcon"><i class="bx bx-image-add"></i></span>
                                    <input type="file" name="logo" id="c_logo">
                                </div>
                            </div>
                            <div class="col-md-4 cf-field">
                                <label>PDF Logo (Invoices)</label>
                                <div class="cf-input-box">
                                    <span class="cf-icon" id="pdfLogoPreviewWrap" style="padding:4px; display:none;">
                                        <img id="pdfLogoPreview" src="" style="width:100%; height:100%; border-radius:6px; object-fit:contain;">
                                    </span>
                                    <span class="cf-icon" id="pdfLogoDefaultIcon"><i class="bx bx-file-blank"></i></span>
                                    <input type="file" name="pdf_logo" id="c_pdf_logo">
                                </div>
                            </div>
                        </div>

                        {{-- Contact Section --}}
                        <div class="cf-section-title mt-4">Contact Information</div>
                        <div class="row g-3">
                            <div class="col-md-3 cf-field">
                                <label>Email Address</label>
                                <div class="cf-input-box">
                                    <span class="cf-icon"><i class="bx bx-envelope"></i></span>
                                    <input type="email" name="email" id="c_email" placeholder="email@company.com">
                                </div>
                            </div>
                            <div class="col-md-3 cf-field">
                                <label>Mobile Number</label>
                                <div class="cf-input-box">
                                    <span class="cf-icon"><i class="bx bx-phone"></i></span>
                                    <input type="tel" name="mob" id="c_mob" placeholder="Phone">
                                </div>
                            </div>
                            <div class="col-md-3 cf-field">
                                <label>Industry</label>
                                <div class="cf-input-box">
                                    <span class="cf-icon"><i class="bx bx-briefcase"></i></span>
                                    <input type="text" name="industry" id="c_industry" placeholder="e.g. IT, Sales">
                                </div>
                            </div>
                            <div class="col-md-3 cf-field">
                                <label>Website</label>
                                <div class="cf-input-box">
                                    <span class="cf-icon"><i class="bx bx-globe"></i></span>
                                    <input type="url" name="website" id="c_website" placeholder="https://...">
                                </div>
                            </div>
                        </div>

                        {{-- Tax Section --}}
                        <div class="cf-section-title mt-4">Taxation & GST</div>
                        <div class="row g-3">
                            <div class="col-md-6 cf-field">
                                <label>GST Number</label>
                                <div class="cf-input-box">
                                    <span class="cf-icon"><i class="bx bx-buildings"></i></span>
                                    <input type="text" name="gst" id="c_gst" placeholder="Enter GSTIN">
                                </div>
                            </div>
                            <div class="col-md-6 cf-field">
                                <label>VAT Number</label>
                                <div class="cf-input-box">
                                    <span class="cf-icon"><i class="bx bx-file"></i></span>
                                    <input type="text" name="vat" id="c_vat" placeholder="Enter VAT No.">
                                </div>
                            </div>
                            
                            {{-- Dynamic Tax Rates --}}
                            <div class="col-md-3 cf-field tax-rate-field" data-tax="CGST">
                                <label>CGST (%)</label>
                                <div class="cf-input-box">
                                    <input type="number" step="0.01" name="tax_rates[]" id="rate_0" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-3 cf-field tax-rate-field" data-tax="SGST">
                                <label>SGST (%)</label>
                                <div class="cf-input-box">
                                    <input type="number" step="0.01" name="tax_rates[]" id="rate_1" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-3 cf-field tax-rate-field" data-tax="IGST">
                                <label>IGST (%)</label>
                                <div class="cf-input-box">
                                    <input type="number" step="0.01" name="tax_rates[]" id="rate_2" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-3 cf-field tax-rate-field" data-tax="VAT">
                                <label>VAT (%)</label>
                                <div class="cf-input-box">
                                    <input type="number" step="0.01" name="tax_rates[]" id="rate_3" placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        {{-- Address Section --}}
                        <div class="cf-section-title mt-4">Location & Address</div>
                        <div class="row g-3">
                            <div class="col-12 cf-field">
                                <label>Full Address</label>
                                <div class="cf-input-box cf-textarea-box">
                                    <textarea name="address" id="c_address" rows="2" placeholder="Street, Building, Area..."></textarea>
                                </div>
                            </div>
                            <div class="col-md-3 cf-field">
                                <label>City</label>
                                <div class="cf-input-box"><input type="text" name="city" id="c_city" placeholder="City"></div>
                            </div>
                            <div class="col-md-3 cf-field">
                                <label>State</label>
                                <div class="cf-input-box"><input type="text" name="state" id="c_state" placeholder="State"></div>
                            </div>
                            <div class="col-md-3 cf-field">
                                <label>Zip Code</label>
                                <div class="cf-input-box"><input type="text" name="zipcode" id="c_zipcode" placeholder="Zip"></div>
                            </div>
                            <div class="col-md-3 cf-field">
                                <label>Country</label>
                                <div class="cf-input-box"><input type="text" name="country" id="c_country" placeholder="Country"></div>
                            </div>
                        </div>

                        {{-- Bank Section --}}
                        <div class="cf-section-title mt-4">Bank Details</div>
                        <div class="row g-3">
                            <div class="col-md-6 cf-field">
                                <label>Bank Name</label>
                                <div class="cf-input-box"><input type="text" name="bank_details[]" id="bank_0" placeholder="Bank Name"></div>
                            </div>
                            <div class="col-md-6 cf-field">
                                <label>Account Holder</label>
                                <div class="cf-input-box"><input type="text" name="bank_details[]" id="bank_1" placeholder="Account Name"></div>
                            </div>
                            <div class="col-md-6 cf-field">
                                <label>Account number</label>
                                <div class="cf-input-box"><input type="text" name="bank_details[]" id="bank_2" placeholder="Account No."></div>
                            </div>
                            <div class="col-md-6 cf-field">
                                <label>IFSC Code</label>
                                <div class="cf-input-box"><input type="text" name="bank_details[]" id="bank_3" placeholder="IFSC Code"></div>
                            </div>
                            <div class="col-md-6 cf-field">
                                <label>UPI ID</label>
                                <div class="cf-input-box"><input type="text" name="bank_details[]" id="bank_4" placeholder="UPI Id"></div>
                            </div>
                        </div>

                        @if(Auth::user()->role == 'master')
                            <div class="cf-section-title mt-4">Subscription Plan</div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="d-flex gap-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="subscription" value="standard" id="plan_standard" checked>
                                            <label class="form-check-label" for="plan_standard">Standard</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="subscription" value="premium" id="plan_premium">
                                            <label class="form-check-label" for="plan_premium">Premium</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="subscription" value="pro" id="plan_pro">
                                            <label class="form-check-label" for="plan_pro">PRO</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>

                    <div class="cf-modal-footer">
                        <button type="button" class="cf-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="cf-btn-save">
                            <i class="bx bx-check"></i> <span id="submitBtnText">Save Company</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* ── Modal & UX Refinement ── */
        .cf-wrap * { box-sizing: border-box; }
        .cf-modal-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; background: linear-gradient(135deg, #005757, #007e7e); border-radius: 16px 16px 0 0; }
        .cf-modal-header-title { font-size: 1rem; font-weight: 700; color: #fff; margin: 0; }
        .cf-modal-header-sub { font-size: .73rem; color: rgba(255,255,255,.72); margin: 0; }
        .cf-modal-header .btn-close { filter: invert(1); opacity:.8; }

        .cf-section-title { font-size: .7rem; font-weight: 800; color: #006666; text-transform: uppercase; letter-spacing: .08em; margin: 18px 0 12px; padding-bottom: 5px; border-bottom: 1.5px solid rgba(0,102,102,.1); }
        .cf-section-title:first-child { margin-top: 0; }
        .cf-field { display: flex; flex-direction: column; gap: 4px; }
        .cf-field label { font-size: .78rem; font-weight: 500; color: #5f6368; }
        .req { color: #ea4335; }

        .cf-input-box { display: flex; align-items: center; border: 1.5px solid #dadce0; border-radius: 10px; background: #fff; height: 42px; overflow: hidden; transition: all 0.2s; }
        .cf-input-box:focus-within { border-color: #006666; box-shadow: 0 0 0 3.5px rgba(0,102,102,0.08); }
        .cf-input-box .cf-icon { width: 40px; height: 100%; display: flex; align-items: center; justify-content: center; color: #006666; font-size: 1.1rem; background: #f8fdfd; border-right: 1.5px solid #f1f3f4; }
        .cf-input-box input, .cf-input-box select { flex: 1; border: none !important; outline: none !important; padding: 0 12px; font-size: 0.88rem; color: #202124; background: transparent; }
        .cf-input-box.cf-textarea-box { height: auto; min-height: 80px; }
        .cf-input-box.cf-textarea-box textarea { width: 100%; padding: 12px; border: none; outline: none; font-size: 0.88rem; resize: none; background: transparent; }

        .cf-modal-footer { padding: 14px 20px; border-top: 1px solid #e8eaed; display: flex; justify-content: flex-end; gap: 10px; background: #fff; border-radius: 0 0 16px 16px; }
        .cf-btn-cancel { height: 38px; padding: 0 20px; border-radius: 10px; border: 1.5px solid #dadce0; background: #fff; color: #5f6368; font-size: 0.85rem; font-weight: 500; cursor: pointer; }
        .cf-btn-save { height: 38px; padding: 0 22px; border-radius: 10px; border: none; background: #006666; color: #fff; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(0,102,102,0.2); }
        .cf-btn-save:hover { background: #004d4d; transform: translateY(-1px); }

        .pj-card-actions button { border: none; padding: 6px; border-radius: 8px; font-size: 1.1rem; line-height: 1; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
        .pj-card-actions button:hover { transform: scale(1.1); }
