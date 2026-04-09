@extends('layout')
@section('title', 'Subscriptions - eseCRM')

@section('content')
    <section class="task__section">
        @include('inc.header', ['title' => 'Subscription Management'])

        <div class="dash-container">
            
            {{-- ── Plan Distribution Stats ── --}}
            <div class="pj-stat-row mb-4">
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(0,102,102,0.1);color:#006666;">
                        <i class="bx bx-building"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num">{{ $stats['total'] }}</div>
                        <div class="pj-stat-label">Total Companies</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(128,128,128,0.1);color:#808080;">
                        <i class="bx bx-medal"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num">{{ $stats['standard'] }}</div>
                        <div class="pj-stat-label">Standard Plans</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(0, 102, 102, 0.1);color:#006666;">
                        <i class="bx bxs-medal"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#006666;">{{ $stats['premium'] }}</div>
                        <div class="pj-stat-label">Premium Plans</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(255, 215, 0, 0.1);color:#d4af37;">
                        <i class="bx bxs-crown"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num" style="color:#b8860b;">{{ $stats['pro'] }}</div>
                        <div class="pj-stat-label">Pro / Enterprise</div>
                    </div>
                </div>
            </div>

            {{-- ── Subscriptions Table ── --}}
            <div class="dash-card mb-4" style="background: #fff; border: 1px solid #e8eaed; border-radius: 12px; overflow: hidden;">
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-700">Company Subscriptions</h5>
                    <div class="text-muted small">Manage billing tiers and access levels</div>
                </div>
                
                <div class="table-responsive">
                    <table class="leads-table projects align-middle" id="lists" style="width:100%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Company</th>
                                <th>Primary Contact</th>
                                <th>Tier / Plan</th>
                                <th>Member Since</th>
                                <th class="text-center position-sticky end-0 mw60" data-orderable="false" style="z-index: 1;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($companies as $k=>$company)
                                <tr>
                                    <td class="text-muted fw-bold">{{ $k+1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:32px; height:32px; background:#f4fbfb; border-radius:8px; overflow:hidden; display:flex; align-items:center; justify-content:center;">
                                                @if(!empty($company->logo))
                                                    <img src="{{ asset('assets/images/company/logos/'.$company->logo) }}" style="width:100%; height:100%; object-fit:contain;">
                                                @else
                                                    <i class="bx bx-building text-muted"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="fw-600 text-dark">{{ $company->name }}</div>
                                                <div class="small text-muted">{{ $company->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-dark">{{ $company->mob }}</div>
                                        <div class="small text-muted">{{ $company->city }}, {{ $company->country }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $planClass = [
                                                'standard' => 'bg-secondary',
                                                'premium' => 'bg-teal-premium',
                                                'pro' => 'bg-warning text-dark'
                                            ];
                                            $currentPlan = strtolower($company->plan ?? 'standard');
                                        @endphp
                                        <span class="badge {{ $planClass[$currentPlan] ?? 'bg-light text-dark' }} px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                            <i class="bx {{ $currentPlan == 'pro' ? 'bx-crown' : ($currentPlan == 'premium' ? 'bx-diamond' : 'bx-badge') }} me-1"></i>
                                            {{ $currentPlan }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        {{ $company->created_at ? $company->created_at->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="position-sticky end-0 bg-white">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <button type="button" class="btn lb-btn lb-btn-outline-primary btn-sm open-company-modal" 
                                                data-url="/manage-company?id={{ $company->id }}&ajax=1" title="Modify Plan">
                                                <i class="bx bx-cog me-1"></i> Manage
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-5 text-center text-muted">
                                        <i class="bx bx-info-circle mb-2" style="font-size:2rem;"></i>
                                        <p>No company data available.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    {{-- Reusable Company Modal Shell --}}
    <div class="modal fade" id="companyModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="border-radius:16px; border:none;" id="companyModalContent">
                <!-- Content via AJAX -->
            </div>
        </div>
    </div>

    <style>
        .dash-container { padding: 0 20px; }
        .bg-teal-premium { background-color: #006666 !important; color: #fff !important; }
        
        /* Shared Styles from Companies/Projects */
        .pj-stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
        @media (max-width: 992px) { .pj-stat-row { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 480px) { .pj-stat-row { grid-template-columns: 1fr; } }

        .pj-stat-card {
            background: #fff; border: 1px solid #e8eaed; border-radius: 14px;
            padding: 16px 18px; display: flex; align-items: center; gap: 14px;
            transition: box-shadow 0.2s;
        }
        .pj-stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; }
        .pj-stat-num { font-size: 1.25rem; font-weight: 700; color: #202124; line-height: 1.2; }
        .pj-stat-label { font-size: 0.72rem; color: #80868b; font-weight: 500; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Script Execution Utility
            function execScripts(container) {
                container.querySelectorAll('script').forEach(function (oldScript) {
                    var newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(function (attr) {
                        newScript.setAttribute(attr.name, attr.value);
                    });
                    newScript.textContent = oldScript.textContent;
                    document.body.appendChild(newScript);
                    oldScript.remove();
                });
            }

            // AJAX Modal Logic
            document.addEventListener('click', function(e) {
                const trigger = e.target.closest('.open-company-modal');
                if (trigger) {
                    e.preventDefault();
                    const url = trigger.dataset.url;
                    const content = document.getElementById('companyModalContent');
                    const modalEl = document.getElementById('companyModal');

                    content.innerHTML = '<div class="p-5 text-center"><i class="bx bx-loader-alt bx-spin" style="font-size:2rem;color:#006666;"></i><p class="mt-2 text-muted">Refining subscription data...</p></div>';
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();

                    fetch(url)
                        .then(r => r.text())
                        .then(html => {
                            content.innerHTML = html;
                            execScripts(content);
                        })
                        .catch(() => {
                            content.innerHTML = '<div class="p-5 text-center text-danger"><i class="bx bx-error" style="font-size:2rem;"></i><p>Error synchronizing data.</p></div>';
                        });
                }
            });
        });
    </script>
@endsection
