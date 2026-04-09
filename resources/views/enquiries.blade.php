@extends('layout')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header Section --}}
    <div class="row align-items-center mb-4">
        <div class="col">
            <h2 class="fw-bold mb-1" style="color: #006666;">Landing Page Enquiries</h2>
            <p class="text-muted small mb-0">Manage incoming lead requests from your marketing pages</p>
        </div>
        <div class="col-auto">
            <div class="btn-group shadow-sm bg-white p-1" style="border-radius: 12px; border: 1px solid #e0e0e0;">
                <button type="button" class="btn btn-sm px-3 py-2 border-0 rounded-3 active" id="cardViewBtn" onclick="setView('card')">
                    <i class="bx bx-grid-alt me-1"></i> Grid
                </button>
                <button type="button" class="btn btn-sm px-3 py-2 border-0 rounded-3" id="tableViewBtn" onclick="setView('table')">
                    <i class="bx bx-list-ul me-1"></i> List
                </button>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; background: linear-gradient(135deg, #006666, #009688);">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 text-white">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px; height:48px; background: rgba(255,255,255,0.2);">
                            <i class="bx bx-infinite fs-3"></i>
                        </div>
                        <div class="fs-2 fw-bold">{{ $stats['total'] }}</div>
                    </div>
                    <div class="text-white opacity-75">Total Requests</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; border-left: 5px solid #0dcaf0 !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info" style="width:48px; height:48px;">
                            <i class="bx bx-mail-send fs-3"></i>
                        </div>
                        <div class="fs-2 fw-bold">{{ $stats['new'] }}</div>
                    </div>
                    <div class="text-muted small uppercase fw-semibold tracking-wider">New Leads</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; border-left: 5px solid #ffc107 !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning" style="width:48px; height:48px;">
                            <i class="bx bx-conversation fs-3"></i>
                        </div>
                        <div class="fs-2 fw-bold">{{ $stats['contacted'] }}</div>
                    </div>
                    <div class="text-muted small uppercase fw-semibold tracking-wider">In Discussion</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; border-left: 5px solid #198754 !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success" style="width:48px; height:48px;">
                            <i class="bx bx-check-double fs-3"></i>
                        </div>
                        <div class="fs-2 fw-bold">{{ $stats['closed'] }}</div>
                    </div>
                    <div class="text-muted small uppercase fw-semibold tracking-wider">Qualified/Closed</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-6 col-lg-8">
                    <div class="input-group search-box" style="width: 300px; max-width: 100%;">
                        <span class="input-group-text bg-light border-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" class="form-control bg-light border-0 p-2" id="enquirySearch" placeholder="Search inquiries..." style="font-size: 0.9rem;">
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 text-end">
                    <select class="form-select border-0 bg-light py-2 shadow-none" id="statusFilter" style="font-size: 0.9rem; border-radius: 8px;">
                        <option value="all">All Statuses</option>
                        <option value="0">New Only</option>
                        <option value="1">Contacted</option>
                        <option value="2">Closed</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid View --}}
    <div id="cardView" class="row g-4 mb-4">
        @forelse($enquiries as $enquiry)
            <div class="col-md-6 col-lg-4 enquiry-card-wrapper" data-status="{{ $enquiry->status }}" data-search="{{ strtolower($enquiry->name . ' ' . $enquiry->email . ' ' . $enquiry->subject) }}">
                <div class="card border-0 shadow-sm h-100 pj-card" style="border-radius: 20px; transition: all 0.3s ease;">
                    <div class="pj-card-accent" style="height: 6px; background: @if($enquiry->status == 0)#0dcaf0 @elseif($enquiry->status == 1)#ffc107 @else#198754 @endif; border-radius: 20px 20px 0 0;"></div>
                    <div class="card-body p-4 open-enquiry-modal cur-pointer" data-url="/manage-enquiry?id={{ $enquiry->id }}">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white me-3" 
                                     style="width:50px; height:50px; font-size:1.2rem; background: linear-gradient(45deg, #006666, #009688);">
                                    {{ strtoupper(substr($enquiry->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0" style="color: #2c3e50;">{{ $enquiry->name }}</h5>
                                    <small class="text-muted">{{ $enquiry->created_at->format('d M, Y · h:i A') }}</small>
                                </div>
                            </div>
                            <span class="badge @if($enquiry->status == 0) bg-info @elseif($enquiry->status == 1) bg-warning @else bg-success @endif px-3 py-2" style="border-radius: 8px;">
                                @if($enquiry->status == 0) New @elseif($enquiry->status == 1) Contacted @else Closed @endif
                            </span>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-1 text-muted small">
                                <i class="bx bx-envelope me-2"></i> {{ $enquiry->email ?? 'N/A' }}
                            </div>
                            <div class="d-flex align-items-center text-muted small">
                                <i class="bx bx-phone me-2"></i> {{ $enquiry->mob ?? 'N/A' }}
                            </div>
                        </div>

                        @if($enquiry->subject)
                            <div class="fw-bold mb-2 p-2 bg-light rounded-3" style="font-size: 0.85rem; border-left: 3px solid #006666;">
                                {{ $enquiry->subject }}
                            </div>
                        @endif

                        <p class="text-muted small mb-0 message-preview">
                            {{ Str::limit($enquiry->message, 120) }}
                        </p>
                    </div>
                    <div class="card-footer bg-transparent border-top-0 p-4 pt-0">
                        <div class="d-flex gap-2">
                             <button type="button" class="btn btn-sm w-100 open-enquiry-modal" data-url="/manage-enquiry?id={{ $enquiry->id }}" style="background: rgba(0,102,102,0.1); color: #006666; font-weight: 500;">
                                <i class="bx bx-show-alt me-1"></i> Details
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger px-3 delete-enquiry-btn" data-id="{{ $enquiry->id }}">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <img src="/assets/images/no-data.svg" alt="No data" style="width: 200px; opacity: 0.5;">
                <h5 class="text-muted mt-3">No enquiries found yet.</h5>
            </div>
        @endforelse
    </div>

    {{-- Table View --}}
    <div id="tableView" class="card border-0 shadow-sm mb-4 overflow-hidden" style="display:none; border-radius: 20px;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 border-0 text-muted small fw-bold">NAME</th>
                        <th class="py-3 border-0 text-muted small fw-bold">CONTACT</th>
                        <th class="py-3 border-0 text-muted small fw-bold">STATUS</th>
                        <th class="py-3 border-0 text-muted small fw-bold">RECEIVED</th>
                        <th class="pe-4 py-3 border-0 text-end text-muted small fw-bold">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($enquiries as $enquiry)
                        <tr class="enquiry-card-wrapper pointer-cursor" data-status="{{ $enquiry->status }}" data-search="{{ strtolower($enquiry->name . ' ' . $enquiry->email . ' ' . $enquiry->subject) }}" onclick="openModal(this)">
                            <td class="ps-4">
                                <div class="fw-bold" style="color: #2c3e50;">{{ $enquiry->name }}</div>
                                <div class="text-muted mini-text">{{ Str::limit($enquiry->subject, 30) }}</div>
                            </td>
                            <td>
                                <div>{{ $enquiry->email }}</div>
                                <div class="text-muted small">{{ $enquiry->mob }}</div>
                            </td>
                            <td>
                                <span class="badge @if($enquiry->status == 0) bg-info @elseif($enquiry->status == 1) bg-warning @else bg-success @endif px-3 py-1" style="border-radius: 6px;">
                                    @if($enquiry->status == 0) New @elseif($enquiry->status == 1) Contacted @else Closed @endif
                                </span>
                            </td>
                            <td>
                                <div class="small">{{ $enquiry->created_at->format('d M, Y') }}</div>
                                <div class="text-muted mini-text">{{ $enquiry->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <button class="btn btn-sm bg-light text-primary open-enquiry-modal" 
                                            data-url="/manage-enquiry?id={{ $enquiry->id }}">
                                        <i class="bx bx-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm bg-light text-danger delete-enquiry-btn" data-id="{{ $enquiry->id }}">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL --}}
<div class="modal fade" id="enquiryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0" style="border-radius: 24px; overflow: hidden;">
            <div id="enquiryModalContent"></div>
        </div>
    </div>
</div>

{{-- STYLES --}}
<style>
    .tracking-wider { letter-spacing: 0.05em; }
    .uppercase { text-transform: uppercase; }
    .mini-text { font-size: 0.75rem; }
    .cur-pointer { cursor: pointer; }
    
    .pj-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 102, 102, 0.12) !important;
    }

    .btn-group .btn.active {
        background: #006666 !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(0, 102, 102, 0.2);
    }

    #tableView tr { transition: background 0.2s; }
    #tableView tr:hover { background-color: rgba(0,102,102,0.03); }

    .message-preview {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        height: 2.8em;
    }
</style>

{{-- SCRIPTS --}}
<script>
    function setView(view) {
        localStorage.setItem('enquiry_view_pref', view);
        const cardView = document.getElementById('cardView');
        const tableView = document.getElementById('tableView');
        const cardBtn = document.getElementById('cardViewBtn');
        const tableBtn = document.getElementById('tableViewBtn');

        if (view === 'card') {
            cardView.style.display = 'flex';
            tableView.style.display = 'none';
            cardBtn.classList.add('active');
            tableBtn.classList.remove('active');
        } else {
            cardView.style.display = 'none';
            tableView.style.display = 'block';
            cardBtn.classList.remove('active');
            tableBtn.classList.add('active');
        }
    }

    // Modal Trigger function for table rows
    function openModal(row) {
        const url = row.querySelector('.open-enquiry-modal').dataset.url;
        loadEnquiryModal(url);
    }

    function loadEnquiryModal(url) {
        const content = document.getElementById('enquiryModalContent');
        const modal = new bootstrap.Modal(document.getElementById('enquiryModal'));
        
        content.innerHTML = '<div class="p-5 text-center"><i class="bx bx-loader-alt bx-spin fs-1" style="color:#006666;"></i></div>';
        modal.show();

        fetch(url)
            .then(res => res.text())
            .then(html => {
                content.innerHTML = html;
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const pref = localStorage.getItem('enquiry_view_pref') || 'card';
        setView(pref);

        // Search & Filter Logic
        const searchInput = document.getElementById('enquirySearch');
        const statusFilter = document.getElementById('statusFilter');

        function applyFilters() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusTerm = statusFilter.value;
            const items = document.querySelectorAll('.enquiry-card-wrapper');

            items.forEach(item => {
                const searchData = item.dataset.search;
                const statusData = item.dataset.status;
                const matchesSearch = searchData.includes(searchTerm);
                const matchesStatus = statusTerm === 'all' || statusData === statusTerm;

                item.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', applyFilters);
        statusFilter.addEventListener('change', applyFilters);

        // Global Modal Delegate
        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('.open-enquiry-modal');
            if (trigger) {
                e.preventDefault();
                loadEnquiryModal(trigger.dataset.url);
            }

            // Delete logic
            const delBtn = e.target.closest('.delete-enquiry-btn');
            if (delBtn) {
                if (confirm('Are you sure you want to delete this enquiry?')) {
                    window.location.href = '/delete-enquiry?id=' + delBtn.dataset.id;
                }
            }
        });
    });
</script>
@endsection
