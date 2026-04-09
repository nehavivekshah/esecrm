@extends('layout')
@section('title','Attendances - eseCRM')

@section('content')
@php
    $roles = session('roles');
    $roleArray = explode(',',($roles->permissions ?? ''));
@endphp

<section class="task__section">
    @include('inc.header', ['title' => 'Attendance Management'])

    <div class="dash-container">
        {{-- ── Stat Cards ── --}}
        <div class="rv-stat-row mb-4">
            <div class="rv-stat-card">
                <div class="rv-stat-icon" style="background:rgba(26,115,232,0.1);color:#1a73e8;">
                    <i class="bx bx-calendar-check"></i>
                </div>
                <div>
                    <div class="rv-stat-num">{{ $summary['working_days'] }}</div>
                    <div class="rv-stat-label">Working Days</div>
                </div>
            </div>
            <div class="rv-stat-card" title="Worked vs Expected (Hrs)">
                <div class="rv-stat-icon" style="background:rgba(52,168,83,0.1);color:#34a853;">
                    <i class="bx bx-stopwatch"></i>
                </div>
                <div>
                    <div class="rv-stat-num" style="color:#34a853;">{{ $summary['worked_hours'] }} / {{ $summary['expected_hours'] }}</div>
                    <div class="rv-stat-label">Hours Logged</div>
                </div>
            </div>
            <div class="rv-stat-card">
                <div class="rv-stat-icon" style="background:rgba(251,188,4,0.1);color:#f9a825;">
                    <i class="bx bx-user-check"></i>
                </div>
                <div>
                    <div class="rv-stat-num" style="color:#f9a825;">{{ $summary['present'] }}</div>
                    <div class="rv-stat-label">Present</div>
                </div>
            </div>
            <div class="rv-stat-card">
                <div class="rv-stat-icon" style="background:rgba(234,67,53,0.1);color:#ea4335;">
                    <i class="bx bx-user-x"></i>
                </div>
                <div>
                    <div class="rv-stat-num" style="color:#ea4335;">{{ $summary['absent'] }}</div>
                    <div class="rv-stat-label">Absent</div>
                </div>
            </div>
        </div>

        {{-- ── Secondary Stats (Small) ── --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="rv-mini-stat d-flex align-items-center gap-3 p-2 px-3 border rounded-pill bg-white shadow-sm">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bx bx-calendar-edit text-muted"></i>
                        <span class="text-muted small fw-bold">Leaves:</span>
                        <span class="fw-800">{{ $summary['leaves'] }}</span>
                    </div>
                    <div class="vr"></div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bx bx-calendar-star text-muted"></i>
                        <span class="text-muted small fw-bold">Holidays:</span>
                        <span class="fw-800">{{ $summary['holidays'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Toolbar & Filters ── --}}
        <div class="leads-toolbar mb-3">
            <div class="leads-toolbar-left">
                 <form method="GET" class="d-flex align-items-center gap-2 m-0 p-0">
                    @if($isAdmin)
                        <select name="user_id" id="user_id" class="form-select form-select-sm border-0 bg-light fw-600" style="min-width: 180px; border-radius: 8px;">
                            <option value="">All Team Members</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $selectedUserId == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                    <select name="range" id="range" class="form-select form-select-sm border-0 bg-light fw-600" style="min-width: 150px; border-radius: 8px;">
                        <option value="today" {{ $range=='today' ? 'selected' : '' }}>Today</option>
                        <option value="7days" {{ $range=='7days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="month" {{ $range=='month' ? 'selected' : '' }}>This Month</option>
                        <option value="last-month" {{ $range=='last-month' ? 'selected' : '' }}>Last Month</option>
                        <option value="year" {{ $range=='year' ? 'selected' : '' }}>This Year</option>
                    </select>
                    <button type="submit" class="lb-icon-btn" style="background:#006666; color:#fff;" title="Apply Filters">
                        <i class="bx bx-search"></i>
                    </button>
                </form>
            </div>
            <div class="leads-toolbar-right">
                <button class="lb-icon-btn" onclick="location.reload()" title="Refresh List">
                    <i class="bx bx-refresh"></i>
                </button>
            </div>
        </div>

        {{-- ── Table Card ── --}}
        <div class="dash-card mb-4">
            <div class="table-responsive">
                <table id="lists" class="leads-table" style="width:100%;">
                    <thead>
                        <tr>
                            @if($isAdmin)<th>Team Member</th>@endif
                            <th>Date & Day</th>
                            <th>Timing <small>(In - Out)</small></th>
                            <th class="text-center">Method</th>
                            <th style="width:120px;">Status</th>
                            <th>Type</th>
                            <th>Hours <small>(Work/Exp)</small></th>
                            <th class="text-end">Balance</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($final as $r)
                        @php
                            $workedHours = is_numeric($r['worked_hours']) ? (float)$r['worked_hours'] : 0;
                            $expectedHours = is_numeric($r['expected_hours']) ? (float)$r['expected_hours'] : 0;
                            $diff = 0;
                            $diff_class = 'text-muted';

                            if (($workedHours > 0 || $expectedHours > 0) && !in_array($r['status'], ['Holiday', 'Leave', 'Absent'])) {
                                $diff = $workedHours - $expectedHours;
                                $diff_class = $diff >= 0 ? 'text-success' : 'text-danger';
                            }
                            
                            $workedHoursformatted = sprintf('%02d:%02d', floor($workedHours), round(($workedHours - floor($workedHours)) * 60));
                            $expectedHoursformatted = sprintf('%02d:%02d', floor($expectedHours), round(($expectedHours - floor($expectedHours)) * 60));
                            
                            $absDiff = abs($diff);
                            $diff_formatted = sprintf('%s%02d:%02d', $diff >= 0 ? '+' : '-', floor($absDiff), round(($absDiff - floor($absDiff)) * 60));
                        @endphp
                        <tr>
                            @if($isAdmin)
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="lb-avatar-sm" style="background:rgba(26,115,232,0.1); color:#1a73e8; border:none; font-weight:800;">
                                            {{ substr($r['user'] ?? 'U', 0, 1) }}
                                        </div>
                                        <div class="fw-600 text-dark small">{{ $r['user'] }}</div>
                                    </div>
                                </td>
                            @endif
                            <td>
                                <div class="fw-600 text-dark">{{ \Carbon\Carbon::parse($r['date'])->format('d M, Y') }}</div>
                                <div class="text-muted small fw-500">{{ $r['day'] }}</div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-dark fw-600" style="font-size:0.85rem;">
                                        <i class="bx bx-log-in-circle text-success small"></i> {{ $r['check_in'] ?: '--:--' }}
                                    </span>
                                    <span class="text-muted small" style="font-size:0.75rem;">
                                        <i class="bx bx-log-out-circle text-danger small"></i> {{ $r['check_out'] ?: '--:--' }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($r['method'])
                                    <span class="badge bg-light text-muted border px-2 py-1" style="font-size:0.65rem;">{{ $r['method'] }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @php
                                    $stStyle = match($r['status']) {
                                        'Present' => ['#34a853', 'bx-check-circle'],
                                        'Leave' => ['#5f6368', 'bx-calendar-edit'],
                                        'Holiday' => ['#1a73e8', 'bx-calendar-star'],
                                        'Absent' => ['#ea4335', 'bx-user-x'],
                                        default => ['#dadce0', 'bx-help-circle']
                                    };
                                @endphp
                                <span class="rv-status-pill" style="background:{{ $stStyle[0] }}15; color:{{ $stStyle[0] }};">
                                    <i class="bx {{ $stStyle[1] }}"></i> {{ $r['status'] }}
                                </span>
                            </td>
                            <td><small class="fw-500">{{ $r['type'] ?: '-' }}</small></td>
                            <td class="{{ $diff_class }}">
                                @if ($workedHours > 0 || $expectedHours > 0)
                                    <strong class="text-dark">{{ $workedHoursformatted }}</strong> <span class="text-muted">/ {{ $expectedHoursformatted }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end fw-800 {{ $diff_class }}" style="font-size:0.9rem;">
                                {{ !in_array($r['status'], ['Holiday', 'Leave', 'Absent']) && ($workedHours > 0 || $expectedHours > 0) ? $diff_formatted : '-' }}
                            </td>
                            <td><small class="text-muted text-truncate d-inline-block" style="max-width: 150px;" title="{{ $r['remarks'] }}">{{ $r['remarks'] ?: '-' }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 10 : 9 }}" class="text-center py-5">
                                <div class="rv-empty">
                                    <i class="bx bx-calendar-x"></i>
                                    <span>No attendance records found for this selection.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<style>
    /* ── Page Layout ── */
    .dash-container { padding: 0 24px 24px; }
    .rv-stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
    @media (max-width: 991px) { .rv-stat-row { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 576px) { .rv-stat-row { grid-template-columns: repeat(1, 1fr); } }

    .rv-stat-card { background: #fff; border: 1px solid #e8eaed; border-radius: 16px; padding: 18px; display: flex; align-items: center; gap: 14px; transition: all 0.2s; }
    .rv-stat-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-color: #d2d4d7; }
    .rv-stat-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
    .rv-stat-num { font-size: 1.3rem; font-weight: 800; color: #202124; line-height: 1; white-space: nowrap; }
    .rv-stat-label { font-size: 0.72rem; color: #80868b; margin-top: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }

    /* ── Status Pills ── */
    .rv-status-pill { display: inline-flex; align-items: center; gap: 4px; border-radius: 20px; padding: 4px 12px; font-size: 0.72rem; font-weight: 700; }
    .rv-status-pill i { font-size: 0.9rem; }

    /* ── Empty State ── */
    .rv-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px; color: #9aa0a6; text-align: center; }
    .rv-empty i { font-size: 3rem; margin-bottom: 12px; color: #dadce0; }
    .rv-empty span { font-size: 0.95rem; font-weight: 500; display: block; }

    /* ── Toolbar Overrides ── */
    .leads-toolbar { display: flex; align-items: center; justify-content: space-between; background: #fff; border: 1px solid #e8eaed; border-radius: 12px; padding: 8px 16px; min-height: 56px; }
    
    /* ── Custom Selects ── */
    .form-select-sm:focus { border-color: #006666; box-shadow: none; background-color: #f8f9fa; }
</style>
@endsection
