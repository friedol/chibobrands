@extends('layouts.admin')

@section('title', 'Delivery Dashboard')

@push('styles')
    <style>
        :root {
            --delivery-primary: #0d6efd;
            --delivery-warning: #f59e0b;
            --delivery-success: #10b981;
            --delivery-danger: #ef4444;
        }

        .icon-circle {
            width: 32px;
            height: 32px;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .card-metric {
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-lift {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        }

        .card-metric .h3 {
            font-size: 1.5rem;
            margin-bottom: 0;
        }

        .x-small {
            font-size: 0.75rem;
            letter-spacing: 0.025em;
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.02);
        }

        .dash-stat-card { border-radius:14px; padding:13px 13px 11px; background:#fff; border:1.5px solid rgba(0,0,0,0.08); display:block; height:100%; box-sizing:border-box; }
        .dsc-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:0.88rem; flex-shrink:0; }
        .dsc-trend { font-size:0.67rem; color:#94a3b8; font-weight:500; white-space:nowrap; }
        .dsc-value { font-size:1.05rem; font-weight:700; color:#1e293b; line-height:1.2; margin-top:10px; }
        .dsc-label { font-size:0.7rem; color:#94a3b8; margin-top:3px; font-weight:500; }

        @media (max-width: 768px) {
            .h3 {
                font-size: 1.1rem !important;
            }

            .card-body {
                padding: 0.75rem !important;
            }

            .icon-circle {
                width: 28px;
                height: 28px;
                font-size: 11px;
            }

            .x-small {
                font-size: 9px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h2 class="fw-bold mb-1">Hello! {{ auth()->user()->name }}</h2>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <button class="btn btn-outline-primary btn-sm rounded-pill px-3 x-small" type="button"
                            data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                            <i class="fas fa-filter me-1"></i> Filter
                            @if(request()->anyFilled(['period', 'start_date', 'end_date']))
                                <span class="badge bg-primary ms-1">Active</span>
                            @endif
                        </button>
                        <div class="avatar-circle shadow-sm"
                            style="height: 40px; width: 40px; background: #6f42c1; font-size: 0.9rem;">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modern Collapsable Filters -->
        <div class="collapse {{ request()->anyFilled(['period', 'start_date', 'end_date']) ? 'show' : '' }} mb-4"
            id="filterCollapse">
            <div class="card border-0 shadow-sm border-top border-4 border-primary">
                <div class="card-body bg-light p-3">
                    <form action="{{ route('admin.dashboard') }}" method="GET" class="row g-2 align-items-end"
                        data-no-global-handler>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-bold x-small text-uppercase mb-1">Time Period</label>
                            <select name="period" id="periodSelect" class="form-select form-select-sm">
                                <option value="today" {{ ($period ?? '') == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="yesterday" {{ ($period ?? '') == 'yesterday' ? 'selected' : '' }}>Yesterday
                                </option>
                                <option value="week" {{ ($period ?? '') == 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ ($period ?? '') == 'month' || !isset($period) ? 'selected' : '' }}>
                                    This Month</option>
                                <option value="6_months" {{ ($period ?? '') == '6_months' ? 'selected' : '' }}>Last 6 Months
                                </option>
                                <option value="year" {{ ($period ?? '') == 'year' ? 'selected' : '' }}>This Year</option>
                                <option value="2_years" {{ ($period ?? '') == '2_years' ? 'selected' : '' }}>Last 2 Years
                                </option>
                                <option value="custom" {{ ($period ?? '') == 'custom' ? 'selected' : '' }}>Custom Range
                                </option>
                                <option value="all" {{ ($period ?? '') == 'all' ? 'selected' : '' }}>All Time</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-2 custom-date-group {{ ($period ?? '') == 'custom' ? '' : 'd-none' }}">
                            <label class="form-label fw-bold x-small text-uppercase mb-1">From Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm"
                                value="{{ request('start_date') }}">
                        </div>

                        <div class="col-6 col-md-2 custom-date-group {{ ($period ?? '') == 'custom' ? '' : 'd-none' }}">
                            <label class="form-label fw-bold x-small text-uppercase mb-1">To Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm"
                                value="{{ request('end_date') }}">
                        </div>

                        <div class="col-12 col-md-auto ms-auto">
                            <div class="btn-group shadow-sm w-100">
                                <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">APPLY</button>
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-dark btn-sm px-4 fw-bold">RESET</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-lg-3 stats-col">
                <div class="dash-stat-card hover-lift" style="border-color:rgba(13,110,253,0.3);background:linear-gradient(150deg,rgba(13,110,253,0.06) 0%,#fff 100%);">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="dsc-icon" style="background:rgba(13,110,253,0.13);color:#0d6efd;"><i class="fas fa-tasks"></i></div>
                        <span class="dsc-trend">&#8212; Stable</span>
                    </div>
                    <div class="dsc-value">{{ $stats['assigned_tasks'] }}</div>
                    <div class="dsc-label">Assigned Tasks</div>
                </div>
            </div>
            <div class="col-6 col-lg-3 stats-col">
                <div class="dash-stat-card hover-lift" style="border-color:rgba(245,158,11,0.3);background:linear-gradient(150deg,rgba(245,158,11,0.06) 0%,#fff 100%);">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="dsc-icon" style="background:rgba(245,158,11,0.13);color:#f59e0b;"><i class="fas fa-truck"></i></div>
                        <span class="dsc-trend">&#8212; Stable</span>
                    </div>
                    <div class="dsc-value">{{ $stats['pending_delivery'] }}</div>
                    <div class="dsc-label">Pending Delivery</div>
                </div>
            </div>
            <div class="col-6 col-lg-3 stats-col">
                <div class="dash-stat-card hover-lift" style="border-color:rgba(25,135,84,0.3);background:linear-gradient(150deg,rgba(25,135,84,0.06) 0%,#fff 100%);">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="dsc-icon" style="background:rgba(25,135,84,0.13);color:#198754;"><i class="fas fa-check-circle"></i></div>
                        <span class="dsc-trend">&#8212; Stable</span>
                    </div>
                    <div class="dsc-value">{{ $stats['delivered'] }}</div>
                    <div class="dsc-label">Delivered</div>
                </div>
            </div>
            <div class="col-6 col-lg-3 stats-col">
                <div class="dash-stat-card hover-lift" style="border-color:rgba(220,53,69,0.3);background:linear-gradient(150deg,rgba(220,53,69,0.06) 0%,#fff 100%);">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="dsc-icon" style="background:rgba(220,53,69,0.13);color:#dc3545;"><i class="fas fa-times-circle"></i></div>
                        <span class="dsc-trend">&#8212; Stable</span>
                    </div>
                    <div class="dsc-value">{{ $stats['failed'] }}</div>
                    <div class="dsc-label">Failed</div>
                </div>
            </div>
        </div>

        <!-- Analytics Chart -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark">
                            <i class="fas fa-chart-bar me-2 text-primary"></i>Delivery Performance
                            ({{ ucfirst(str_replace('_', ' ', $period ?? 'Last 7 Days')) }})
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="deliveryChartContainer" style="position: relative; height: 300px; width: 100%;">
                            <canvas id="deliveryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Tasks and Recent Deliveries -->
        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-truck-loading me-2"></i>Tasks to Deliver</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr class="x-small text-uppercase fw-bold text-muted">
                                        <th class="ps-3 border-0">Task</th>
                                        <th class="border-0">Customer</th>
                                        <th class="border-0">Status</th>
                                        <th class="text-end pe-3 border-0">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($assignedTasks as $task)
                                        <tr onclick="window.location='{{ route('admin.design-tasks.show', $task) }}'"
                                            style="cursor: pointer;">
                                            <td class="ps-3">
                                                <div class="small fw-bold text-dark">{{ Str::limit($task->title, 30) }}</div>
                                                <div class="x-small text-muted">{{ $task->task_code }}</div>
                                            </td>
                                            <td class="small">
                                                <div class="fw-bold">{{ $task->customer->name ?? 'N/A' }}</div>
                                                <div class="text-muted">{{ $task->customer->phone ?? '' }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning x-small">
                                                    {{ ucfirst($task->delivery_status) }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <i class="fas fa-chevron-right text-muted x-small"></i>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted small">
                                                No tasks currently assigned for delivery
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-success"><i class="fas fa-history me-2"></i>Recent Deliveries</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($recentDeliveries as $task)
                                <div class="list-group-item border-0 px-3 py-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-success x-small">Delivered</span>
                                        <span class="x-small text-muted">{{ $task->delivered_at->format('M d, H:i') }}</span>
                                    </div>
                                    <div class="small fw-bold text-dark mb-1">{{ Str::limit($task->title, 25) }}</div>
                                    <div class="x-small text-muted">{{ $task->customer->name ?? 'N/A' }}</div>
                                    @if($task->delivery_notes)
                                        <div class="mt-2 p-2 bg-light rounded x-small text-muted fst-italic">
                                            "{{ Str::limit($task->delivery_notes, 50) }}"
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="fas fa-box-open text-muted mb-2 opacity-50" style="font-size: 1.5rem;"></i>
                                    <p class="small text-muted mb-0">No recent deliveries</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Delivery Performance Chart
            const ctx = document.getElementById('deliveryChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [{
                        label: 'Deliveries Completed',
                        data: @json($chartData['data']),
                        backgroundColor: 'rgba(13, 110, 253, 0.7)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                        barThickness: 30,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1f2937',
                            padding: 12,
                            cornerRadius: 8,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                borderDash: [5, 5],
                                drawBorder: false,
                            },
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Period Selection Handling
            const periodSelect = document.getElementById('periodSelect');
            const customDateGroups = document.querySelectorAll('.custom-date-group');

            if (periodSelect) {
                periodSelect.addEventListener('change', function () {
                    if (this.value === 'custom') {
                        customDateGroups.forEach(el => el.classList.remove('d-none'));
                    } else {
                        customDateGroups.forEach(el => el.classList.add('d-none'));
                    }
                });
            }
        });
    </script>
@endpush