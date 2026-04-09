@extends('layout')
@section('title', 'Subscriptions - eseCRM')

@section('content')
    <section class="task__section">
        @include('inc.header', ['title' => 'Subscription Management'])

        <div class="dash-container">
            
            {{-- ── Plan Analytics ── --}}
            <div class="pj-stat-row mb-4">
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(0,102,102,0.1);color:#006666;">
                        <i class="bx bx-building"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num">{{ $stats['total'] }}</div>
                        <div class="pj-stat-label">Subscribed Companies</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(212, 175, 55, 0.1);color:#d4af37;">
                        <i class="bx bx-crown"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num">{{ $plans->count() }}</div>
                        <div class="pj-stat-label">Active Tiers</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(52, 168, 83, 0.1);color:#34a853;">
                        <i class="bx bx-trending-up"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num text-success">${{ number_format($plans->avg('price'), 2) }}</div>
                        <div class="pj-stat-label">Avg. Price Point</div>
                    </div>
                </div>
                <div class="pj-stat-card">
                    <div class="pj-stat-icon" style="background:rgba(0, 102, 102, 0.1);color:#006666;">
                        <i class="bx bx-shield-quarter"></i>
                    </div>
                    <div>
                        <div class="pj-stat-num">{{ $stats['pro'] + $stats['premium'] }}</div>
                        <div class="pj-stat-label">Premium Users</div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                {{-- ════════════════════════════════
                SUBSCRIPTION TIERS (The Plans)
                ════════════════════════════════ --}}
                <div class="col-12">
                    <div class="dash-card h-100" style="background: #fff; border: 1.5px solid #e8eaed; border-radius: 20px;">
                        <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-light" style="border-radius: 20px 20px 0 0;">
                            <div>
                                <h5 class="mb-1 fw-700 text-dark">Subscription Tiers</h5>
                                <div class="small text-muted">Manage your available billing packages and included benefits</div>
                            </div>
                            <button class="btn btn-sm btn-indigo rounded-pill open-plan-modal" data-url="/manage-plan?ajax=1">
                                <i class="bx bx-plus"></i> Add New Tier
                            </button>
                        </div>
                        <div class="p-4">
                            <div class="row g-4">
                                @foreach($plans as $plan)
                                    <div class="col-xl-4 col-md-6">
                                        <div class="plan-item-card p-4 border rounded shadow-sm hover-shadow transition-all" style="background: #fff; position: relative; min-height: 240px;">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="badge bg-soft-primary px-3 py-1 rounded-pill text-indigo fw-bold" style="font-size:0.8rem; background:rgba(0,102,102,0.08); color:#006666;">
                                                    ${{ number_format($plan->price, 2) }}/mo
                                                </div>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm p-0 text-muted" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                        <li><a class="dropdown-item py-2 open-plan-modal" href="javascript:void(0)" data-url="/manage-plan?item={{ $plan->id }}&id={{ $plan->id }}"><i class="bx bx-edit-alt me-2 text-warning"></i> Edit Tier</a></li>
                                                        <li><a class="dropdown-item py-2 text-danger" href="/delete-plan?id={{ $plan->id }}" onclick="return confirm('Archive this plan?')"><i class="bx bx-trash me-2"></i> Archive Tier</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <h5 class="fw-700 mb-2">{{ $plan->name }}</h5>
                                            <p class="small text-muted mb-4 lh-base" style="font-size: 0.85rem;">{{ $plan->description }}</p>
                                            
                                            @if($plan->features)
                                                <div class="plan-features-preview mt-3 pt-3 border-top">
                                                    <div class="fw-600 mb-2 small" style="text-transform:uppercase; letter-spacing:0.5px; font-size:0.65rem; color:#80868b;">Key Benefits</div>
                                                    @foreach(array_slice($plan->features, 0, 4) as $feat)
                                                        <div class="d-flex align-items-center gap-2 mb-2" style="font-size: 0.78rem; color: #5f6368;">
                                                            <i class="bx bx-check-circle text-success"></i> {{ $feat }}
                                                        </div>
                                                    @endforeach
                                                    @if(count($plan->features) > 4)
                                                        <div class="text-indigo small mt-1 fw-500" style="font-size: 0.72rem; cursor: pointer;">+ {{ count($plan->features) - 4 }} more features</div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Modals Shell --}}
    <div class="modal fade" id="companyModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="border-radius:16px; border:none;" id="companyModalContent"></div>
        </div>
    </div>

    <div class="modal fade" id="planModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-md">
            <div class="modal-content" style="border-radius:20px; border:none; box-shadow: 0 20px 60px rgba(0,0,0,0.15);" id="planModalContent"></div>
        </div>
    </div>

    <style>
        .dash-container { padding: 0 20px; }
        .bg-teal-premium { background-color: #006666 !important; color: #fff !important; }
        .plan-item-card { transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1); }
        .plan-item-card:hover { transform: translateY(-3px); border-color: #006666 !important; }
        
        /* Shared Styles */
        .pj-stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
        @media (max-width: 992px) { .pj-stat-row { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 480px) { .pj-stat-row { grid-template-columns: 1fr; } }

        .pj-stat-card {
            background: #fff; border: 1px solid #e8eaed; border-radius: 14px;
            padding: 16px 18px; display: flex; align-items: center; gap: 14px;
        }
        .pj-stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .pj-stat-num { font-size: 1.15rem; font-weight: 700; color: #202124; }
        .pj-stat-label { font-size: 0.72rem; color: #80868b; font-weight: 500; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

            // Global Modal Handler
            function handleAjaxModal(triggerClass, modalId, contentId, loaderText) {
                document.addEventListener('click', function(e) {
                    const trigger = e.target.closest(triggerClass);
                    if (trigger) {
                        e.preventDefault();
                        const url = trigger.dataset.url;
                        const content = document.getElementById(contentId);
                        const modalEl = document.getElementById(modalId);

                        content.innerHTML = `<div class="p-5 text-center"><i class="bx bx-loader-alt bx-spin" style="font-size:2rem;color:#006666;"></i><p class="mt-2 text-muted small">${loaderText}</p></div>`;
                        bootstrap.Modal.getOrCreateInstance(modalEl).show();

                        fetch(url)
                            .then(r => r.text())
                            .then(html => {
                                content.innerHTML = html;
                                execScripts(content);
                            })
                            .catch(() => {
                                content.innerHTML = '<div class="p-5 text-center text-danger small"><i class="bx bx-error-circle"></i> Error loading resource.</div>';
                            });
                    }
                });
            }

            handleAjaxModal('.open-company-modal', 'companyModal', 'companyModalContent', 'Syncing company parameters...');
            handleAjaxModal('.open-plan-modal', 'planModal', 'planModalContent', 'Opening tier configuration...');
        });
    </script>
@endsection
