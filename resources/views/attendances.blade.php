@extends('layout')
@section('title','Attendances - eseCRM')

@section('content')
@php
    $roles = session('roles');
    $roleArray = explode(',', (optional($roles)->permissions ?? ''));
@endphp
<section class="task__section">
    <div class="text">
        <i class="bx bx-menu" id="mbtn"></i> 
        Attendances
        <a href="/signout" class="logoutbtn"><i class="bx bx-log-out"></i></a>
    </div>
    <div class="container-fluid">
        <div class="board-title board-title-flex">
            <h1>Attendance Records</h1>
            <form method="GET" class="d-flex flex-wrap align-items-center gap-3">
                @if($isAdmin)
                    <div class="flex-grow-1">
                        <!--<label for="user_id" class="form-label small">User</label>-->
                        <select name="user_id" id="user_id" class="form-select form-select-sm" style="min-width: 200px;">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $selectedUserId == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="flex-grow-1">
                    <!--<label for="range" class="form-label small">Date Range</label>-->
                    <select name="range" id="range" class="form-select form-select-sm" style="min-width: 180px;">
                        <option value="today" {{ $range=='today' ? 'selected' : '' }}>Today</option>
                        <option value="7days" {{ $range=='7days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="month" {{ $range=='month' ? 'selected' : '' }}>This Month</option>
                        <option value="last-month" {{ $range=='last-month' ? 'selected' : '' }}>Last Month</option>
                        <option value="year" {{ $range=='year' ? 'selected' : '' }}>This Year</option>
                    </select>
                </div>
                <div class="align-self-end">
                    <button class="btn btn-primary btn-sm"><i class="bx bx-search"></i> Apply Filter</button>
                </div>
            </form>
        </div>
        <div class="row g-3 mb-3 mt-2">
            @php
                $summaryCards = [
                    ['label' => 'Working Days', 'value' => $summary['working_days'], 'icon' => 'bx-calendar-check', 'class' => 'primary'],
                    ['label' => 'Expected Hours', 'value' => $summary['expected_hours'], 'icon' => 'bx-time', 'class' => 'info'],
                    ['label' => 'Worked Hours', 'value' => $summary['worked_hours'], 'icon' => 'bx-stopwatch', 'class' => 'success'],
                    ['label' => 'Present', 'value' => $summary['present'], 'icon' => 'bx-user-check', 'class' => 'success'],
                    ['label' => 'Absent', 'value' => $summary['absent'], 'icon' => 'bx-user-x', 'class' => 'danger'],
                    ['label' => 'Leaves', 'value' => $summary['leaves'], 'icon' => 'bx-calendar-edit', 'class' => 'dark'],
                    ['label' => 'Holidays', 'value' => $summary['holidays'], 'icon' => 'bx-calendar-star', 'class' => 'secondary'],
                ];
            @endphp

            @foreach($summaryCards as $card)
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                <div class="card text-white bg-{{ $card['class'] }} shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">{{ $card['label'] }}</h5>
                            <h3 class="fw-bold mb-0">{{ $card['value'] }}</h3>
                        </div>
                        <i class="bx {{ $card['icon'] }} fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="row">
            <div class="col-md-12 py-3 table-responsive">
                <table id="lists" class="table table-condensed m-table clients" style="width:100%;border-radius:5px!important;overflow:hidden;">
                    <thead class="table-light">
                        <tr>
                            @if($isAdmin)<th>User</th>@endif
                            <th>Date</th>
                            <th>Day</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Work Hours <small>(Worked/Expected)</small></th>
                            <th>Deficit / Surplus</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($final as $r)
                        @php
                            // Logic to calculate hours difference and set styling
                            $workedHours = is_numeric($r['worked_hours']) ? (float)$r['worked_hours'] : 0;
                            $expectedHours = is_numeric($r['expected_hours']) ? (float)$r['expected_hours'] : 0;
                            $diff = 0;
                            $diff_class = 'text-muted';
                            $diff_formatted = 'N/A';

                            if ($workedHours > 0 || $expectedHours > 0 && $r['status'] !== 'Holiday' && $r['status'] !== 'Leave' && $r['status'] !== 'Absent') {
                                $diff = $workedHours - $expectedHours;
                                if ($diff >= 0) {
                                    $diff_class = 'text-success';
                                    $diff_formatted = '(+'. number_format($diff, 2) .' hrs)';
                                } else {
                                    $diff_class = 'text-danger';
                                    $diff_formatted = '('. number_format($diff, 2) .' hrs)';
                                }
                            }
                            
                            $hours = floor($workedHours);
                            $minutes = round(($workedHours - $hours) * 60);
                            $workedHoursformatted = sprintf('%02d:%02d', $hours, $minutes);
                            
                            $hours = floor($expectedHours);
                            $minutes = round(($expectedHours - $hours) * 60);
                            $expectedHoursformatted = sprintf('%02d:%02d', $hours, $minutes);
                            
                            $hours = floor(abs($diff)); // Use abs() to avoid negative hour formatting
                            $minutes = round((abs($diff) - $hours) * 60);
                            $sign = $diff >= 0 ? '+' : '-';
                            $diff_formattedHoursformatted = sprintf('%s%02d:%02d', $sign, $hours, $minutes);

                        @endphp
                        <tr>
                            @if($isAdmin)<td>{{ $r['user'] }}</td>@endif
                            <td>{{ \Carbon\Carbon::parse($r['date'])->format('d M Y') }}</td>
                            <td>{{ $r['day'] }}</td>
                            <td>{{ $r['check_in'] ?: '-' }}</td>
                            <td>{{ $r['check_out'] ?: '-' }}</td>
                            <td>{{ $r['method'] ?: '-' }}</td>
                            <td>
                                @php
                                    $badge = match($r['status']) {
                                        'Present' => 'success',
                                        'Leave' => 'secondary',
                                        'Holiday' => 'info',
                                        'Absent' => 'danger',
                                        default => 'light'
                                    };
                                @endphp
                                <span class="badge rounded-pill bg-{{ $badge }}">{{ $r['status'] }}</span>
                            </td>
                            <td>{{ $r['type'] ?: '-' }}</td>
                            {{-- Worked / Expected Hours --}}
                            <td class="{{ $diff_class }}">
                                @if ($workedHours > 0 || $expectedHours > 0)
                                    <strong>{{ $workedHoursformatted }}</strong> / {{ $expectedHoursformatted }}
                                @else
                                    -
                                @endif
                            </td>
                            {{-- Deficit / Surplus --}}
                            <td class="fw-bold {{ $diff_class }}">
                                {{ $diff_formattedHoursformatted }}
                            </td>
                            <td>{{ $r['remarks'] ?: '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 11 : 10 }}" class="text-center py-5">
                                <i class="bx bx-info-circle fs-2 text-primary"></i>
                                <h5 class="mt-2">No Attendance Data Found</h5>
                                <p class="text-muted">There are no records matching your current filter criteria.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
