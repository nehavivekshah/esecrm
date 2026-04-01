@extends('layout')
@section('title', 'Reports & Forecasting - eseCRM')

@section('content')
    <section class="task__section">
        <div class="text">
            <i class="bx bx-menu" id="mbtn"></i>
            Sales Analytics & Forecasting
        </div>

        <div class="row m-4 g-4">
            <!-- Summary Cards -->
            <div class="col-md-4">
                <div class="form-card text-center p-4 bg-white shadow-sm rounded">
                    <h6 class="text-muted fw-bold text-uppercase"><i class='bx bx-line-chart'></i> Total Pipeline Value</h6>
                    <h3 class="text-primary mt-2 fw-bolder">₹{{ number_format($totalPipelineValue) }}</h3>
                    <small class="text-muted">Total value of open opportunities</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-card text-center p-4 bg-white shadow-sm rounded">
                    <h6 class="text-muted fw-bold text-uppercase"><i class='bx bx-check-shield'></i> Total Revenue Won</h6>
                    <h3 class="text-success mt-2 fw-bolder">₹{{ number_format($totalWonValue) }}</h3>
                    <small class="text-muted">Total value of closed won opportunities</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-card text-center p-4 bg-white shadow-sm rounded">
                    <h6 class="text-muted fw-bold text-uppercase"><i class='bx bx-target-lock'></i> Overall Win Rate</h6>
                    <h3 class="text-warning mt-2 fw-bolder">{{ $winRate }}%</h3>
                    <small class="text-muted">Of all closed opportunities</small>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="col-md-12 mt-4">
                <div class="form-card bg-white shadow-sm p-4 rounded">
                    <h5 class="fw-bold mb-4 border-bottom pb-2"><i class='bx bx-bar-chart-alt-2'></i> Expected Revenue
                        Forecast</h5>
                    <p class="text-muted small">Forecast calculated using probability algorithm: (Deal Value) × (Stage
                        Probability Multiplier). Example: Proposal = 60% probability of closing.</p>
                    <div style="height: 300px;">
                        <canvas id="forecastChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Agent Performance Row -->
            <div class="col-md-12 mt-4">
                <div class="form-card bg-white shadow-sm p-4 rounded">
                    <h5 class="fw-bold mb-4 border-bottom pb-2"><i class='bx bx-group'></i> Agent Sales Performance</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Agent Name</th>
                                    <th>Total Deals Handled</th>
                                    <th>Deals Won</th>
                                    <th>Win Ratio</th>
                                    <th>Revenue Generated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($agentPerformance as $agent)
                                    <tr>
                                        <td class="fw-bold"><i
                                                class='bx bx-user-circle text-primary me-2'></i>{{ $agent->agent_name ?? 'Unassigned' }}
                                        </td>
                                        <td>{{ $agent->total_deals }}</td>
                                        <td><span class="badge bg-success">{{ $agent->won_deals }} Won</span></td>
                                        <td>
                                            <div class="progress" style="height: 8px;">
                                                @php $pct = $agent->total_deals > 0 ? ($agent->won_deals / $agent->total_deals) * 100 : 0; @endphp
                                                <div class="progress-bar bg-success" role="progressbar"
                                                    style="width: {{ $pct }}%" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <small class="text-muted mt-1 d-block">{{ round($pct, 1) }}% Conversion</small>
                                        </td>
                                        <td class="fw-bold text-success">₹{{ number_format($agent->revenue_generated) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No agent data available currently.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const ctx = document.getElementById('forecastChart').getContext('2d');
            const forecastLabels = {!! json_encode($forecastLabels) !!};
            const forecastData = {!! json_encode($forecastData) !!};

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: forecastLabels,
                    datasets: [{
                        label: 'Expected Revenue (₹)',
                        data: forecastData,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return '₹' + value.toLocaleString('en-IN');
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return '₹' + context.raw.toLocaleString('en-IN') + ' Expected';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
