@extends('layouts.admin')

@section('title', 'My Sales Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="fw-bold mb-1">Welcome, {{ auth()->user()->name }}</h2>
                    <p class="text-muted small mb-0">You have <a href="{{ route('admin.customers.index') }}" class="fw-bold text-primary text-decoration-none border-bottom border-primary border-opacity-25">{{ $customers }}</a> active customers in your portfolio</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <form method="GET" action="{{ route('admin.saler.my-dashboard') }}" id="periodForm" class="d-flex align-items-center gap-2" data-no-global-handler>
                        <select name="period" id="periodSelect" class="form-select form-select-sm rounded-pill px-3 border-0 shadow-sm text-uppercase fw-bold x-small" style="background-color: #f8f9fa; cursor: pointer;">
                            <option value="today" {{ ($period ?? '') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="yesterday" {{ ($period ?? '') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                            <option value="week" {{ ($period ?? '') == 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ ($period ?? '') == 'month' || !isset($period) ? 'selected' : '' }}>This Month</option>
                            <option value="6_months" {{ ($period ?? '') == '6_months' ? 'selected' : '' }}>Last 6 Months</option>
                            <option value="year" {{ ($period ?? '') == 'year' ? 'selected' : '' }}>This Year</option>
                            <option value="2_years" {{ ($period ?? '') == '2_years' ? 'selected' : '' }}>Last 2 Years</option>
                            <option value="custom" {{ ($period ?? '') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            <option value="all" {{ ($period ?? '') == 'all' ? 'selected' : '' }}>All Time</option>
                        </select>
                        <div id="customDateRange" class="d-flex align-items-center gap-1 {{ ($period ?? '') == 'custom' ? '' : 'd-none' }}">
                            <input type="date" name="start_date" class="form-control form-control-sm rounded-pill border-0 shadow-sm x-small" value="{{ request('start_date') }}" style="width: 110px;">
                            <span class="x-small text-muted">to</span>
                            <input type="date" name="end_date" class="form-control form-control-sm rounded-pill border-0 shadow-sm x-small" value="{{ request('end_date') }}" style="width: 110px;">
                            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-2 x-small"><i class="fas fa-check"></i></button>
                        </div>
                    </form>
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill x-small no-print">
                        <i class="fas fa-chart-line me-1"></i> Performance: Active
                    </span>
                    <button class="btn btn-primary btn-sm rounded-pill px-3" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Only Header -->
    <div class="print-only report-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <img src="{{ asset('images/logo.webp') }}" alt="Logo" style="height: 50px;" onerror="this.style.display='none'">
                <h1 class="fw-bold text-dark mt-2">Agent Performance Summary</h1>
                <p class="mb-0 text-dark">Agent: {{ auth()->user()->name }}</p>
                <p class="mb-0 text-dark">Period: {{ ucfirst(str_replace('_', ' ', $period ?? 'month')) }}</p>
            </div>
            <div class="text-end text-dark">
                <p class="mb-1 fw-bold">CHIBOBRAND CO. LTD.</p>
                <p class="mb-1">Generated: {{ now()->format('M d, Y H:i') }}</p>
                <p class="mb-0">Format: A4 Professional Analytics</p>
            </div>
        </div>
    </div>

    <div class="row g-2 g-md-3 mb-4">
        <!-- Revenue Card -->
        <div class="col-6 col-lg-3 stats-col">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-success hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-success bg-opacity-10 text-success me-2">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Total Sales</span>
                    </div>
                    <div class="h3 mb-0 fw-bold text-dark">TZS {{ number_format($totalRevenue + $tasksRevenue) }}</div>
                    <div class="mt-2 x-small text-success">
                        <i class="fas fa-plus me-1"></i> Orders & Tasks
                    </div>
                </div>
            </div>
        </div>

        <!-- Paid Card -->
        <div class="col-6 col-lg-3 stats-col">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-primary hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Amount Paid</span>
                    </div>
                    <div class="h3 mb-0 fw-bold text-dark">TZS {{ number_format($overallPaid) }}</div>
                    <div class="mt-2 x-small text-primary">
                        <i class="fas fa-check-circle me-1"></i> Collected
                    </div>
                </div>
            </div>
        </div>

        <!-- Outstanding Card -->
        <div class="col-6 col-lg-3 stats-col">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-danger hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-danger bg-opacity-10 text-danger me-2">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Unpaid Balance</span>
                    </div>
                    <div class="h3 mb-0 fw-bold text-dark">TZS {{ number_format($overallBalance) }}</div>
                    <div class="mt-2 x-small text-danger">
                        <i class="fas fa-clock me-1"></i> Pending Payment
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoices Card -->
        <div class="col-6 col-lg-3 stats-col">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-info hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-info bg-opacity-10 text-info me-2">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Total Invoices</span>
                    </div>
                    <div class="h3 mb-0 fw-bold text-dark">{{ $totalInvoices }}</div>
                    <div class="mt-2 x-small text-info">
                        <i class="fas fa-shopping-bag me-1"></i> Orders Count
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($currentTarget)
    <!-- Sales Target Tracking -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); color: white;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center mb-1">
                                <h5 class="fw-bold mb-0"><i class="fas fa-bullseye me-2"></i>Sales Target Achievement ({{ $targetPeriod }})</h5>
                                <span class="badge bg-white bg-opacity-20 text-white ms-3 px-3 rounded-pill x-small">Active Goal</span>
                            </div>
                            <p class="small text-white-50 mb-4">You have achieved {{ $targetAchievement }}% of your sales goal for this period.</p>
                            
                            <div class="progress bg-white bg-opacity-20 mb-2" style="height: 12px; border-radius: 6px;">
                                <div class="progress-bar bg-white shadow-sm" role="progressbar" style="width: {{ min(100, $targetAchievement) }}%; border-radius: 6px;" aria-valuenow="{{ $targetAchievement }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between x-small fw-bold">
                                <span>{{ number_format($targetAchievement, 1) }}% Completed</span>
                                <span>Goal: TZS {{ number_format($targetAmount) }}</span>
                            </div>
                        </div>
                        <div class="col-lg-4 text-center mt-3 mt-lg-0">
                            <div class="bg-white bg-opacity-10 rounded-4 p-3 border border-white border-opacity-10">
                                <div class="x-small text-white-50 text-uppercase fw-bold mb-1">Remaining to Target</div>
                                <div class="h3 mb-0 fw-bold text-white">TZS {{ number_format($targetRemaining) }}</div>
                                <div class="x-small mt-2 text-white-50">
                                    <i class="fas fa-clock me-1"></i> Ends {{ $currentTarget->end_date->format('M d, Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Predictive Sales Follow-ups (Smart Reminders) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark"><i class="fas fa-magic text-primary me-2"></i>Predictive Sales Follow-ups</h6>
                    <a href="{{ route('admin.customer-data-center.index') }}" class="btn btn-sm btn-light rounded-pill px-3 x-small fw-bold">View Daily List</a>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0">
                        <!-- Stats Column -->
                        <div class="col-md-4 border-end">
                            <div class="p-4 h-100">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="p-3 bg-danger bg-opacity-10 rounded-4 text-center">
                                            <div class="h3 fw-bold text-danger mb-0">{{ $followUpStats['overdue'] }}</div>
                                            <div class="x-small text-danger fw-bold text-uppercase">Overdue</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 bg-warning bg-opacity-10 rounded-4 text-center">
                                            <div class="h3 fw-bold text-warning mb-0">{{ $followUpStats['due_today'] }}</div>
                                            <div class="x-small text-warning fw-bold text-uppercase">Due Today</div>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-2 text-center p-2 bg-light rounded-4">
                                        <div class="small text-muted mb-0">Total Urgent: <span class="fw-bold text-dark">{{ $followUpStats['total_follow_ups'] }}</span></div>
                                        <div class="x-small text-muted">{{ $followUpStats['upcoming'] }} upcoming in next 3 days</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- List Column -->
                        <div class="col-md-8">
                            <div class="p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light bg-opacity-50">
                                            <tr class="x-small text-uppercase fw-bold text-muted">
                                                <th class="ps-4 border-0">Customer</th>
                                                <th class="border-0">Status</th>
                                                <th class="border-0">Priority</th>
                                                <th class="text-end pe-4 border-0">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($urgentFollowUps as $customer)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold text-dark small">{{ $customer->name }}</div>
                                                    <div class="x-small text-muted">Last: {{ $customer->last_order_date?->format('M d') ?: 'No history' }}</div>
                                                </td>
                                                <td>
                                                    <span class="badge x-small rounded-pill bg-{{ $customer->status_color }}-subtle text-{{ $customer->status_color }}">
                                                        {{ $customer->follow_up_status }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 me-2" style="height: 4px; width: 40px;">
                                                            <div class="progress-bar bg-primary" style="width: {{ min(100, $customer->priority_ranking / 2) }}%"></div>
                                                        </div>
                                                        <span class="x-small font-monospace">#{{ $customer->priority_ranking }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="{{ route('admin.customer-data-center.show', $customer) }}" class="btn btn-sm btn-primary rounded-pill px-3 x-small">
                                                        Insights
                                                    </a>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted small">
                                                    <i class="fas fa-check-circle text-success me-2"></i>You are all caught up for today!
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3 mt-2">
        <div class="col-12 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold text-muted text-uppercase x-small mb-0 ms-1"><i class="fas fa-stream me-2"></i>My Design Work Progress ({{ $totalTasks }} Total)</h6>
            <span class="badge bg-info bg-opacity-10 text-info px-3 py-1 rounded-pill x-small">Active Tasks</span>
        </div>
    </div>
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6 col-lg-3 stats-col">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-warning hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-warning bg-opacity-10 text-warning me-2">
                            <i class="fas fa-clock"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Awaiting</span>
                    </div>
                    <div class="h3 mb-0 fw-bold">{{ $taskStatus['pending'] }}</div>
                    <div class="x-small text-muted mt-2">New tasks</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 stats-col">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-primary hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2">
                            <i class="fas fa-magic"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Designing</span>
                    </div>
                    <div class="h3 mb-0 fw-bold">{{ $taskStatus['in_progress'] }}</div>
                    <div class="x-small text-muted mt-2">In progress</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 stats-col">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-info hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-info bg-opacity-10 text-info me-2">
                            <i class="fas fa-eye"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Review</span>
                    </div>
                    <div class="h3 mb-0 fw-bold">{{ $taskStatus['in_review'] }}</div>
                    <div class="x-small text-muted mt-2">Feedback phase</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 stats-col">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-dark hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-dark bg-opacity-10 text-dark me-2">
                            <i class="fas fa-print"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Printing</span>
                    </div>
                    <div class="h3 mb-0 fw-bold">{{ $taskStatus['printing'] }}</div>
                    <div class="x-small text-muted mt-2">Production</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <!-- Monthly Revenue Trend -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-line me-2"></i>Personal Sales Trend</h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="salerMyRevenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Distribution -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-pie me-2"></i>Sales Mix</h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="salerMyCategoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Section -->
    <div class="row g-3">
        <!-- Recent Individual Orders -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark"><i class="fas fa-shopping-cart me-2"></i>Recent Deals</h6>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">History</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3 border-0 x-small">Date</th>
                                    <th class="border-0 x-small">Customer</th>
                                    <th class="border-0 x-small">Total</th>
                                    <th class="text-end pe-3 border-0 x-small">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                <tr>
                                    <td class="ps-3 small text-muted">{{ $order->created_at->format('M d') }}</td>
                                    <td>
                                        <div class="small fw-bold text-dark">{{ $order->user->name ?? 'Guest' }}</div>
                                    </td>
                                    <td class="small fw-bold">TZS {{ number_format($order->total_amount) }}</td>
                                    <td class="text-end pe-3">
                                        <span class="badge x-small bg-{{ $order->approval_status == 'approved' ? 'success' : 'warning' }}">
                                            {{ ucfirst($order->approval_status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted small">No recent deals found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Tasks -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-dark"><i class="fas fa-palette me-2"></i>Linked Design Work</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentTasks as $task)
                        <div class="list-group-item border-0 py-2">
                             <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small fw-bold text-dark">{{ Str::limit($task->title, 20) }}</div>
                                    <div class="x-small text-muted">{{ $task->customer->name ?? 'N/A' }}</div>
                                </div>
                                <span class="badge x-small rounded-pill bg-primary bg-opacity-10 text-primary border-0">
                                    {{ ucfirst($task->status) }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted small">No linked tasks</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Only Footer (Repeats on every page) -->
    <div class="print-only print-footer">
        <p class="mb-0">&copy; {{ date('Y') }} CHIBOBRAND CO. LTD. All rights reserved.</p>
        <p class="mb-0">Developed by <a href="https://fridoltech.org" style="color: #666; text-decoration: none; font-weight: bold;">Fridoltech</a></p>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Revenue Line Chart
        const revCtx = document.getElementById('salerMyRevenueChart').getContext('2d');
        new Chart(revCtx, {
            type: 'line',
            data: {
                labels: @json($revenueLabels),
                datasets: [{
                    label: 'Monthly Revenue',
                    data: @json($revenueData),
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.05)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { beginAtZero: true, ticks: { font: { size: 10 } } }
                }
            }
        });

        // Category Pie Chart
        const catCtx = document.getElementById('salerMyCategoryChart').getContext('2d');
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: @json($categoryLabels),
                datasets: [{
                    data: @json($categorySales),
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                    borderWidth: 0
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
                cutout: '70%'
            }
        });

        // Period Selection Handling
        const periodSelect = document.getElementById('periodSelect');
        const customDateRange = document.getElementById('customDateRange');
        
        if (periodSelect) {
            periodSelect.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customDateRange.classList.remove('d-none');
                } else {
                    customDateRange.classList.add('d-none');
                    this.form.submit();
                }
            });
        }
    });
</script>
@endpush
@push('styles')
<style>
    @media print {
        .btn, .sidebar, .sidebar-nav, .top-navbar, .mobile-menu-toggle, .form-select, .no-print, .btn-group, .badge { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; }
        .container-fluid { width: 100% !important; padding: 0 !important; }
        .card { border: 1px solid #dee2e6 !important; box-shadow: none !important; margin-bottom: 20px !important; break-inside: avoid; }
        .table { width: 100% !important; border-collapse: collapse !important; }
        .table th, .table td { border: 1px solid #dee2e6 !important; }
        body { background: white !important; font-size: 11pt !important; }
        .stats-col { flex: 0 0 25% !important; max-width: 25% !important; }
        canvas { max-width: 100% !important; height: auto !important; }
        
        /* Recurring Footer on Every Page */
        .print-footer {
            position: fixed;
            bottom: 0px;
            left: 0;
            right: 0;
            background: white !important;
            padding: 10px 0;
            border-top: 1px solid #ddd !important;
            text-align: center;
            font-size: 8pt !important;
            color: #666 !important;
        }
        body { padding-bottom: 50px !important; } /* Space for recurring footer */
        
        .print-only { display: block !important; }
        .report-header { border-bottom: 2px solid #0d6efd; padding-bottom: 10px; margin-bottom: 30px; }
    }
    .print-only { display: none; }
    
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
        .h3 { font-size: 1.1rem !important; }
        .card-body { padding: 0.75rem !important; }
        .icon-circle { width: 28px; height: 28px; font-size: 11px; }
        .x-small { font-size: 9px; }
    }
</style>
@endpush
@endsection
