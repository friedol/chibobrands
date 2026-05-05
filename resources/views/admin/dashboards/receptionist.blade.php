@extends('layouts.admin')

@section('title', 'Receptionist Dashboard')

@section('content')
    <div class="container-fluid">
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
                        <a href="{{ route('admin.design-tasks.create') }}"
                            class="btn btn-primary btn-sm rounded-pill px-3 x-small">
                            <i class="fas fa-plus me-1"></i> New Task
                        </a>
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

        <!-- Global Workflow Visibility (Shared with all Admins) -->
        <div class="row g-2 mb-4">
            <div class="col-12">
                <div class="card border-0 bg-transparent">
                    <div class="card-body p-0">
                        <div class="d-flex gap-2 overflow-auto pb-2" style="scrollbar-width: none;">
                            <div class="flex-fill">
                                <div class="bg-white rounded-3 p-2 border-start border-3 border-warning shadow-sm">
                                    <div class="x-small text-muted fw-bold text-uppercase">Queue</div>
                                    <div class="h5 mb-0 fw-bold">
                                        {{ \App\Models\DesignTask::where('status', 'pending')->count() }}</div>
                                </div>
                            </div>
                            <div class="flex-fill">
                                <div class="bg-white rounded-3 p-2 border-start border-3 border-primary shadow-sm">
                                    <div class="x-small text-muted fw-bold text-uppercase">Designing</div>
                                    <div class="h5 mb-0 fw-bold">
                                        {{ \App\Models\DesignTask::where('status', 'in_progress')->count() }}</div>
                                </div>
                            </div>
                            <div class="flex-fill">
                                <div class="bg-white rounded-3 p-2 border-start border-3 border-info shadow-sm">
                                    <div class="x-small text-muted fw-bold text-uppercase">Review</div>
                                    <div class="h5 mb-0 fw-bold">
                                        {{ \App\Models\DesignTask::where('status', 'in_review')->count() }}</div>
                                </div>
                            </div>
                            <div class="flex-fill">
                                <div class="bg-white rounded-3 p-2 border-start border-3 border-dark shadow-sm">
                                    <div class="x-small text-muted fw-bold text-uppercase">Printing</div>
                                    <div class="h5 mb-0 fw-bold">
                                        {{ \App\Models\DesignTask::whereIn('status', ['printing', 'printed'])->count() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Receptionist Stats -->
        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-lg-3 stats-col">
                <div class="card shadow-sm h-100 border-0 border-start border-4 border-primary hover-lift">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <span class="text-uppercase x-small fw-bold text-muted">New Clients</span>
                        </div>
                        <div class="h3 mb-0 fw-bold">{{ $stats['monthly_customers'] }}</div>
                        <div class="mt-2 x-small text-muted">This month</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3 stats-col">
                <div class="card shadow-sm h-100 border-0 border-start border-4 border-success hover-lift">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-circle bg-success bg-opacity-10 text-success me-2">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <span class="text-uppercase x-small fw-bold text-muted">Tasks created</span>
                        </div>
                        <div class="h3 mb-0 fw-bold">{{ $stats['monthly_tasks'] }}</div>
                        <div class="mt-2 x-small text-muted">Waitlist current</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3 stats-col">
                <div class="card shadow-sm h-100 border-0 border-start border-4 border-warning hover-lift">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-circle bg-warning bg-opacity-10 text-warning me-2">
                                <i class="fas fa-spinner"></i>
                            </div>
                            <span class="text-uppercase x-small fw-bold text-muted">Processing</span>
                        </div>
                        <div class="h3 mb-0 fw-bold">{{ $stats['total_tasks'] - $stats['completed_tasks'] }}</div>
                        <div class="mt-2 x-small text-muted">Active design loop</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3 stats-col">
                <div class="card shadow-sm h-100 border-0 border-start border-4 border-info hover-lift">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-circle bg-info bg-opacity-10 text-info me-2">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <span class="text-uppercase x-small fw-bold text-muted">Handled</span>
                        </div>
                        <div class="h3 mb-0 fw-bold">{{ $stats['completed_tasks'] }}</div>
                        <div class="mt-2 x-small text-muted">Lifetime total</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-3 mb-4">
            <!-- Trend Chart -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-area me-2"></i>
                            {{ match ($period ?? 'month') {
        'today' => 'Task Intake by Hour',
        'week' => 'Daily Task Intake (Last 7 Days)',
        'month' => 'Daily Task Intake (Last 30 Days)',
        '2_years' => 'Monthly Task Intake (24 Months)',
        default => 'Task Intake Trend'
    } }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="receptionistTrendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Distribution -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-info"><i class="fas fa-chart-pie me-2"></i>Current Handling Status</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 240px;">
                            <canvas id="receptionistStatusChart"></canvas>
                        </div>
                        <div class="mt-4">
                            @foreach($tasksByStatus as $status => $count)
                                @if($count > 0)
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <div class="small text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">
                                                            <i class="fas fa-circle me-1" style="color: {{ [
                                        'pending' => '#ffc107',
                                        'in_progress' => '#0d6efd',
                                        'in_review' => '#6f42c1',
                                        'completed' => '#198754',
                                        'printing' => '#0dcaf0'
                                    ][$status] ?? '#6c757d' }}"></i>
                                                            {{ $status }}
                                                        </div>
                                                        <div class="small fw-bold">{{ $count }}</div>
                                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Intake Row -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-chart-bar me-2 text-primary"></i>
                            {{ match ($period ?? 'month') {
        'today' => 'Intake Volume by Hour',
        'week' => 'Intake Volume (Last 7 Days)',
        'month' => 'Intake Volume (Last 30 Days)',
        '2_years' => 'Monthly Intake Volume (Last 24 Months)',
        default => 'Monthly Intake Volume'
    } }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 250px;">
                            <canvas id="receptionistMonthlyBarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Tables -->
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-user-clock me-2"></i>Recent Customers</h6>
                        <a href="{{ route('admin.customers.index') }}"
                            class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr class="x-small text-uppercase fw-bold text-muted">
                                        <th class="ps-3 border-0">Name</th>
                                        <th class="border-0">Phone</th>
                                        <th class="text-end pe-3 border-0">Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentCustomers as $customer)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="small fw-bold text-dark">{{ $customer->name }}</div>
                                            </td>
                                            <td class="small">{{ $customer->phone }}</td>
                                            <td class="text-end pe-3 small text-muted">
                                                {{ $customer->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted small">No recent customers</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-tasks me-2"></i>Active Alerts</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($recentTasks as $task)
                                                    <div class="list-group-item border-0 py-3">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <div class="small fw-bold text-dark">{{ Str::limit($task->title, 25) }}</div>
                                                                <div class="x-small text-muted">{{ $task->customer->name ?? 'N/A' }}</div>
                                                            </div>
                                                            <span class="badge x-small bg-{{ [
                                    'pending' => 'warning text-dark',
                                    'in_progress' => 'primary',
                                    'in_review' => 'info',
                                    'completed' => 'success',
                                    'printing' => 'dark'
                                ][$task->status] ?? 'secondary' }}">
                                                                {{ ucfirst($task->status) }}
                                                            </span>
                                                        </div>
                                                    </div>
                            @empty
                                <div class="text-center py-4 text-muted small">No pending tasks</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Daily Intake Trend
                const ctxTrend = document.getElementById('receptionistTrendChart').getContext('2d');
                new Chart(ctxTrend, {
                    type: 'line',
                    data: {
                        labels: @json($last7Days),
                        datasets: [{
                            label: 'Tasks Created',
                            data: @json($tasksCompletedData),
                            borderColor: '#4e73df',
                            backgroundColor: 'rgba(78, 115, 223, 0.05)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 4,
                            pointBackgroundColor: '#4e73df'
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                            y: { beginAtZero: true, ticks: { font: { size: 10 }, stepSize: 1 } }
                        }
                    }
                });

                // Status Distribution
                const ctxStatus = document.getElementById('receptionistStatusChart').getContext('2d');
                const statusData = @json($tasksByStatus);
                new Chart(ctxStatus, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(statusData).map(k => k.toUpperCase()),
                        datasets: [{
                            data: Object.values(statusData),
                            backgroundColor: ['#ffc107', '#0d6efd', '#6f42c1', '#198754', '#0dcaf0'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: { legend: { display: false } }
                    }
                });

                // Monthly Performance Bar Chart
                const ctxBar = document.getElementById('receptionistMonthlyBarChart').getContext('2d');
                new Chart(ctxBar, {
                    type: 'bar',
                    data: {
                        labels: @json($last6Months),
                        datasets: [{
                            label: 'Monthly Intake',
                            data: @json($monthlyCompletedData),
                            backgroundColor: '#4e73df',
                            borderRadius: 8,
                            barThickness: 30
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                            y: { beginAtZero: true, ticks: { font: { size: 10 }, stepSize: 5 } }
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
    @push('styles')
        <style>
            .icon-circle {
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 8px;
                font-size: 0.8rem;
            }

            .hover-lift {
                transition: transform 0.2s ease-in-out;
            }

            .hover-lift:hover {
                transform: translateY(-5px);
            }

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
@endsection