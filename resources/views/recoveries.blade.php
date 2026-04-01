@extends('layout')
@section('title', 'Recoveries - eseCRM')

@section('content')
    @php
        $roles = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));

        $totalCount = $recoveries->count();
        $paidCount = $recoveries->where('status', '1')->count();
        $unpaidCount = $recoveries->where('status', '0')->count();
        $totalPending = $recoveries->sum('remaining_amount');
        $overdueCount = $recoveries->filter(function ($r) {
            return $r->status == '0' && !empty($r->reminder) &&
                date('Y-m-d', strtotime($r->reminder)) <= date('Y-m-d');
        })->count();
    @endphp

    <section class="task__section">
        @include('inc.header', ['title' => 'Recoveries'])

        <div class="dash-container">

            {{-- ── Stat Cards ── --}}
            <div class="rv-stat-row mb-4">
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(0,102,102,0.10);color:#006666;">
                        <i class="bx bx-receipt"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num">{{ $totalCount }}</div>
                        <div class="rv-stat-label">Total Entries</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(52,168,83,0.10);color:#34a853;">
                        <i class="bx bx-check-circle"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#34a853;">{{ $paidCount }}</div>
                        <div class="rv-stat-label">Paid</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(234,67,53,0.10);color:#ea4335;">
                        <i class="bx bx-time-five"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#ea4335;">{{ $unpaidCount }}</div>
                        <div class="rv-stat-label">Pending</div>
                    </div>
                </div>
                <div class="rv-stat-card">
                    <div class="rv-stat-icon" style="background:rgba(251,188,4,0.12);color:#f9a825;">
                        <i class="bx bx-error-circle"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#f9a825;">{{ $overdueCount }}</div>
                        <div class="rv-stat-label">Overdue</div>
                    </div>
                </div>
                <div class="rv-stat-card rv-stat-wide">
                    <div class="rv-stat-icon" style="background:rgba(234,67,53,0.08);color:#ea4335;">
                        <i class="bx bx-rupee"></i>
                    </div>
                    <div>
                        <div class="rv-stat-num" style="color:#ea4335;">₹{{ number_format($totalPending, 0) }}</div>
                        <div class="rv-stat-label">Total Pending Balance</div>
                    </div>
                </div>
            </div>

            {{-- ── Toolbar ── --}}
            <div class="leads-toolbar mb-3">
                <div class="leads-toolbar-left">
                    <span class="lb-page-count">
                        <i class="bx bx-receipt"></i>
                        {{ $totalCount }} {{ $totalCount == 1 ? 'Recovery' : 'Recoveries' }}
                    </span>
                    @if($overdueCount > 0)
                        <span class="rv-overdue-pill">
                            <i class="bx bx-error-circle"></i> {{ $overdueCount }} Overdue
                        </span>
                    @endif
                </div>
                <div class="leads-toolbar-right gap-2">
                    <button class="lb-icon-btn" onclick="location.reload()" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                    @if(in_array('users_add', $roleArray) || in_array('All', $roleArray))
                        <a href="/manage-recovery" class="lb-btn lb-btn-primary">
                            <i class="bx bx-plus"></i>
                            <span class="d-none d-sm-inline">Add Recovery</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- ── Table Card ── --}}
            <div class="dash-card mb-4">
                <div class="table-responsive">
                    <table id="lists" class="leads-table" style="width:100%;">
                        <thead>
                            <tr>
                                <th class="m-none" style="width:40px;">#</th>
                                <th class="m-none" style="width:80px;">Batch</th>
                                <th>Client</th>
                                <th class="m-none">Company</th>
                                <th>Pending (₹)</th>
                                <th class="m-none">Reminder</th>
                                <th class="m-none">Executive</th>
                                <th class="text-center position-sticky end-0" style="width:130px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recoveries as $k => $recovery)
                                @php
                                    $reminderTimes = strtotime($recovery->reminder ?? '') * 1000;
                                    $isPaid = $recovery->status == '1';
                                    $isOverdue = !$isPaid &&
                                        !empty($recovery->reminder) &&
                                        date('Y-m-d', strtotime($recovery->reminder)) <= date('Y-m-d');
                                    $isFullyPaid = ($recovery->remaining_amount ?? 0) == 0;
                                @endphp
                                <tr class="lead-row-{{ $reminderTimes }}">
                                    <td class="m-none text-muted" style="font-size:0.78rem;">{{ $k + 1 }}</td>
                                    <td class="m-none">
                                        <span class="rv-batch">{{ $recovery->batchNo ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="lb-avatar-sm"
                                                style="background:linear-gradient(135deg,#006666,#009688);color:#fff;flex-shrink:0;">
                                                {{ strtoupper(substr($recovery->name ?? 'R', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-500">{{ $recovery->name ?? '—' }}</div>
                                                <div class="text-muted" style="font-size:0.72rem;">
                                                    {{ $recovery->company ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="m-none text-muted">{{ $recovery->company ?? '—' }}</td>
                                    <td>
                                        @if($isFullyPaid)
                                            <span class="rv-amount-badge rv-paid">
                                                <i class="bx bx-check-circle"></i> Cleared
                                            </span>
                                        @else
                                            <span class="rv-amount {{ $isOverdue ? 'rv-overdue-amount' : '' }}">
                                                ₹{{ number_format($recovery->remaining_amount ?? 0, 0) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="m-none">
                                        @if(!empty($recovery->reminder))
                                            <span class="rv-reminder {{ $isOverdue ? 'rv-reminder-overdue' : '' }}">
                                                <i class="bx bx-calendar"></i>
                                                {{ date('d M Y', strtotime($recovery->reminder)) }}
                                                @if($isOverdue)
                                                    <span class="rv-overdue-dot">Overdue</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="m-none text-muted" style="font-size:0.82rem;">{{ $recovery->poc ?? '—' }}</td>
                                    <td class="position-sticky end-0">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            {{-- Reminder --}}
                                            <button class="btn kb-action-btn reminder" data-id="{{ $recovery->id ?? '' }}"
                                                title="Set Reminder" style="background:rgba(251,188,4,0.10);color:#f9a825;">
                                                <i class="bx bx-alarm"></i>
                                            </button>
                                            {{-- Mark Received --}}
                                            <button class="btn kb-action-btn received" data-id="{{ $recovery->id ?? '' }}"
                                                title="Mark Received" style="background:rgba(52,168,83,0.10);color:#34a853;">
                                                <i class="bx bx-rupee"></i>
                                            </button>
                                            {{-- WhatsApp --}}
                                            @if(!empty($recovery->whatsapp))
                                                <a href="https://api.whatsapp.com/send/?phone={{ $recovery->whatsapp }}&text=Hi&type=phone_number&app_absent=0"
                                                    class="btn kb-action-btn" target="_blank" title="WhatsApp"
                                                    style="background:rgba(37,211,102,0.10);color:#25D366;">
                                                    <i class="bx bxl-whatsapp"></i>
                                                </a>
                                            @endif
                                            {{-- Call --}}
                                            @if(!empty($recovery->mob))
                                                <a href="tel:+{{ $recovery->mob }}" class="btn kb-action-btn" title="Call"
                                                    style="background:rgba(26,115,232,0.10);color:#1a73e8;">
                                                    <i class="bx bx-phone"></i>
                                                </a>
                                            @endif
                                            {{-- Edit --}}
                                            <a href="/manage-recovery?id={{ $recovery->id ?? '' }}" class="btn kb-action-btn"
                                                title="Edit" style="background:rgba(0,102,102,0.10);color:#006666;">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($recoveries->isEmpty())
                    <div class="rv-empty">
                        <i class="bx bx-receipt"></i>
                        <span>No recoveries recorded yet.</span>
                        <a href="/manage-recovery" class="lb-btn lb-btn-primary mt-2">
                            <i class="bx bx-plus"></i> Add Recovery
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </section>

    {{-- Recovery Modal --}}
    <div class="modal fade" id="recoveryModal" tabindex="-1" aria-labelledby="recoveryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius:16px; border:none;">
                <div class="modal-header" style="border-bottom:1px solid #f0f0f0; padding:16px 20px;">
                    <h5 class="modal-title" id="recoveryModalLabel"
                        style="font-size:0.95rem; font-weight:700; color:#202124;">
                        Recovery Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 px-2" id="loadContent"></div>
            </div>
        </div>
    </div>

    <style>
        /* ── Stat Row ── */
        .rv-stat-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
        }

        @media (max-width: 900px) {
            .rv-stat-row {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 576px) {
            .rv-stat-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .rv-stat-card {
            background: #fff;
            border: 1px solid #e8eaed;
            border-radius: 16px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .rv-stat-wide {
            grid-column: span 1;
        }

        .rv-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .rv-stat-num {
            font-size: 1.35rem;
            font-weight: 800;
            color: #202124;
            line-height: 1;
        }

        .rv-stat-label {
            font-size: 0.72rem;
            color: #80868b;
            margin-top: 3px;
            font-weight: 500;
        }

        /* ── Overdue pill in toolbar ── */
        .rv-overdue-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(234, 67, 53, 0.08);
            border: 1px solid rgba(234, 67, 53, 0.2);
            color: #ea4335;
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 0.73rem;
            font-weight: 600;
        }

        /* ── Batch badge ── */
        .rv-batch {
            display: inline-block;
            background: #f1f3f4;
            border-radius: 6px;
            padding: 2px 8px;
            font-size: 0.72rem;
            font-weight: 600;
            color: #5f6368;
        }

        /* ── Amount ── */
        .rv-amount {
            font-weight: 700;
            color: #202124;
            font-size: 0.875rem;
        }

        .rv-overdue-amount {
            color: #ea4335;
        }

        .rv-amount-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.72rem;
            font-weight: 600;
            border-radius: 20px;
            padding: 3px 9px;
        }

        .rv-paid {
            background: rgba(52, 168, 83, 0.08);
            color: #34a853;
        }

        /* ── Reminder ── */
        .rv-reminder {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.76rem;
            color: #5f6368;
        }

        .rv-reminder i {
            font-size: 0.9rem;
        }

        .rv-reminder-overdue {
            color: #ea4335;
        }

        .rv-overdue-dot {
            background: rgba(234, 67, 53, 0.10);
            color: #ea4335;
            border-radius: 20px;
            padding: 1px 7px;
            font-size: 0.68rem;
            font-weight: 700;
        }

        /* ── Empty state ── */
        .rv-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 50px 20px;
            color: #9aa0a6;
            text-align: center;
            gap: 8px;
        }

        .rv-empty i {
            font-size: 2.5rem;
        }

        .rv-empty span {
            font-size: 0.87rem;
        }
    </style>

@endsection