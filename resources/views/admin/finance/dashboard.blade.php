@extends('layouts.admin')

@section('title', 'Financial Dashboard')

@section('content')
    <div class="container-fluid">
        <!-- Welcome Header & Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <h2 class="fw-bold mb-0" style="font-size: clamp(1.1rem, 4vw, 1.5rem);">Hello! {{ auth()->user()->name }}</h2>
                        <p class="text-muted x-small mb-0 d-none d-md-block">Financial overview and reporting</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <form method="GET" action="{{ route('admin.finance.dashboard') }}" id="periodForm"
                            class="d-flex flex-wrap align-items-center justify-content-end gap-2" data-no-global-handler>
                            <select name="period" id="periodSelect"
                                class="form-select form-select-sm rounded-3 px-3 border-0 shadow-sm text-uppercase fw-bold x-small"
                                style="background-color: #f8f9fa; cursor: pointer; min-width: 110px;">
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
                            <div id="customDateRange"
                                class="d-flex align-items-center gap-1 {{ ($period ?? '') == 'custom' ? '' : 'd-none' }}">
                                <input type="date" name="start_date"
                                    class="form-control form-control-sm rounded-3 border-0 shadow-sm x-small"
                                    value="{{ request('start_date') }}" style="width: 100px;">
                                <input type="date" name="end_date"
                                    class="form-control form-control-sm rounded-3 border-0 shadow-sm x-small"
                                    value="{{ request('end_date') }}" style="width: 100px;">
                                <button type="submit" class="btn btn-primary btn-sm rounded-3 px-2 x-small"><i
                                        class="fas fa-check"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Statistics -->
        <div class="row g-2 g-md-3 mb-4">
            <!-- Total Sales -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm border-start border-4 border-info h-100 hover-lift">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-1">
                            <div class="icon-circle-sm bg-info bg-opacity-10 text-info me-2">
                                <i class="fas fa-tags"></i>
                            </div>
                            <span class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem;">Total Sales</span>
                        </div>
                        <div class="h5 mb-0 fw-bold text-info">TZS {{ number_format($totalBilled ?? 0) }}</div>
                        <div class="mt-1 text-info" style="font-size: 0.65rem;">Gross value sold</div>
                    </div>
                </div>
            </div>
            <!-- Total Revenue -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm border-start border-4 border-primary h-100 hover-lift">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-1">
                            <div class="icon-circle-sm bg-primary bg-opacity-10 text-primary me-2">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <span class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem;">Total Revenue</span>
                        </div>
                        <div class="h5 mb-0 fw-bold">TZS {{ number_format($totalRevenue ?? 0) }}</div>
                        <div class="mt-1 text-muted" style="font-size: 0.65rem;">Collected + Outstanding</div>
                    </div>
                </div>
            </div>
            <!-- Total Collected -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm border-start border-4 border-success h-100 hover-lift">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-1">
                            <div class="icon-circle-sm bg-success bg-opacity-10 text-success me-2">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <span class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem;">Total Collected</span>
                        </div>
                        <div class="h5 mb-0 fw-bold text-success">TZS {{ number_format($totalIn) }}</div>
                        <div class="mt-1 text-success" style="font-size: 0.65rem;">Cash Received</div>
                    </div>
                </div>
            </div>
            <!-- Debt Collected -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm border-start border-4 h-100 hover-lift" style="border-left-color: #20c997 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-1">
                            <div class="icon-circle-sm bg-opacity-10 me-2" style="background-color: rgba(32, 201, 151, 0.1); color: #20c997;">
                                <i class="fas fa-hand-holding-dollar"></i>
                            </div>
                            <span class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem;">Debt Collected</span>
                        </div>
                        <div class="h5 mb-0 fw-bold" style="color: #20c997;">TZS {{ number_format($debtCollected ?? 0) }}</div>
                        <div class="mt-1 text-muted" style="font-size: 0.65rem;">Past debts recovered</div>
                    </div>
                </div>
            </div>
            <!-- Expenses -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm border-start border-4 border-danger h-100 hover-lift">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-1">
                            <div class="icon-circle-sm bg-danger bg-opacity-10 text-danger me-2">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <span class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem;">Total Spent</span>
                        </div>
                        <div class="h5 mb-0 fw-bold text-danger">TZS {{ number_format($totalOut) }}</div>
                        <div class="mt-1 text-danger" style="font-size: 0.65rem;">Total Expenses</div>
                    </div>
                </div>
            </div>
            <!-- Balance Due (Period) -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.finance.pending-payments') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm border-start border-4 border-warning h-100 hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-1">
                                <div class="icon-circle-sm bg-warning bg-opacity-10 text-warning me-2">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <span class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem;">Balance Due</span>
                            </div>
                            <div class="h5 mb-0 fw-bold text-warning">TZS {{ number_format($balanceDue ?? 0) }}</div>
                            <div class="mt-1 text-warning d-flex justify-content-between align-items-center" style="font-size: 0.65rem;">
                                <span>Unpaid (Period)</span>
                                <i class="fas fa-chevron-right" style="font-size: 0.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- Net Profit -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm border-start border-4 h-100 hover-lift" style="border-left-color: #6610f2 !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-1">
                            <div class="icon-circle-sm bg-opacity-10 me-2" style="background-color: rgba(102, 16, 242, 0.1); color: #6610f2;">
                                <i class="fas fa-scale-balanced"></i>
                            </div>
                            <span class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem;">Net Profit</span>
                        </div>
                        <div class="h5 mb-0 fw-bold" style="color: #6610f2;">TZS {{ number_format($netProfit) }}</div>
                        <div class="mt-1 text-muted" style="font-size: 0.65rem;">Collected - Spent</div>
                    </div>
                </div>
            </div>
            <!-- Total Losses -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm border-start border-4 h-100 hover-lift" style="border-left-color: #f8285b !important;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-1">
                            <div class="icon-circle-sm bg-opacity-10 me-2" style="background-color: rgba(248, 40, 91, 0.1); color: #f8285b;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <span class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem;">Losses (Hasara)</span>
                        </div>
                        <div class="h5 mb-0 fw-bold" style="color: #f8285b;">TZS {{ number_format($totalLosses ?? 0) }}</div>
                        <div class="mt-1 text-muted" style="font-size: 0.65rem;">Incorrect work value</div>
                    </div>
                </div>
            </div>

            <!-- Global Balance Due -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm border-start border-4 border-danger h-100 hover-lift">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-1">
                            <div class="icon-circle-sm bg-danger bg-opacity-10 text-danger me-2">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <span class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem;">Global Balance Due</span>
                        </div>
                        <div class="h5 mb-0 fw-bold text-danger">TZS {{ number_format($generalBalanceDue ?? 0) }}</div>
                        <div class="mt-1 text-muted" style="font-size: 0.65rem;">All-time unpaid</div>
                    </div>
                </div>
            </div>
            <!-- Customer Credits -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm border-start border-4 border-warning h-100 hover-lift">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-1">
                            <div class="icon-circle-sm bg-warning bg-opacity-10 text-warning me-2">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                            <span class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem;">Customer Credits</span>
                        </div>
                        <div class="h5 mb-0 fw-bold text-warning-emphasis">TZS {{ number_format($totalCredits ?? 0) }}</div>
                        <div class="mt-1 text-muted" style="font-size: 0.65rem;">Overpayments (Deni Lako)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-3 mb-4">
            <!-- Cash Flow Trend -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-chart-line me-2 text-primary"></i>Cash Flow Trend
                        </h6>
                        <div class="btn-group btn-group-sm shadow-sm" role="group">
                            <button type="button" class="btn btn-outline-dark active" id="btnDashLine"
                                onclick="toggleCashFlowChartDash('line')">
                                <i class="fas fa-chart-line"></i>
                            </button>
                            <button type="button" class="btn btn-outline-dark" id="btnDashBar"
                                onclick="toggleCashFlowChartDash('bar')">
                                <i class="fas fa-chart-bar"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="cashFlowChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Expense Breakdown -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-pie-chart me-2 text-danger"></i>Expense
                            Allocation</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 220px;">
                            <canvas id="expenseAllocationChart"></canvas>
                        </div>
                        <div class="mt-4">
                            @foreach($expenseBreakdown->take(3) as $expense)
                                <div class="d-flex justify-content-between x-small mb-1">
                                    <span class="text-muted">{{ $expense->category }}</span>
                                    <span class="fw-bold">{{ number_format($expense->total) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lists Row -->
        <div class="row g-3 mb-4">
            <!-- Department Performance -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-building-user me-2 text-info"></i>Departmental
                            P&L</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 x-small border-0">DEPT</th>
                                        <th class="text-end x-small border-0">REVENUE</th>
                                        <th class="text-end x-small border-0">EXPENSES</th>
                                        <th class="text-end pe-4 x-small border-0">PROFIT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($departmentReports as $report)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold small">{{ $report->name }}</div>
                                            </td>
                                            <td class="text-end small text-success">{{ number_format($report->total_revenue) }}
                                            </td>
                                            <td class="text-end small text-danger">{{ number_format($report->total_expenses) }}
                                            </td>
                                            <td
                                                class="text-end pe-4 fw-bold small {{ $report->profit >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ number_format($report->profit) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-money-bill-transfer me-2 text-success"></i>Recent
                            Revenue</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($recentPayments as $payment)
                                <div class="list-group-item border-0 py-3 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-success bg-opacity-10 text-success me-3">
                                            <i class="fas fa-plus fa-sm"></i>
                                        </div>
                                        <div>
                                            <div class="small fw-bold">{{ $payment->customer->name ?? 'Guest' }}</div>
                                            <div class="x-small text-muted">{{ $payment->department->name ?? 'POS' }}</div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small fw-bold text-success">+{{ number_format($payment->amount) }}</div>
                                        <div class="x-small text-muted">{{ $payment->date->diffForHumans() }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Losses & Credits Row -->
        <div class="row g-3 mb-4">
            <!-- Losses Table -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-heart-crack me-2 text-danger"></i>Hasara ya Kazi (Loss Records)</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 x-small border-0">DATE</th>
                                        <th class="x-small border-0">TASK / CUSTOMER</th>
                                        <th class="x-small border-0">REASON</th>
                                        <th class="text-end pe-4 x-small border-0">AMOUNT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentLosses as $loss)
                                        <tr>
                                            <td class="ps-4 small">{{ $loss->loss_recorded_at->format('M d') }}</td>
                                            <td>
                                                <div class="small fw-bold">{{ $loss->title }}</div>
                                                <div class="x-small text-muted">{{ $loss->customer->name ?? 'N/A' }}</div>
                                            </td>
                                            <td class="small">{{ $loss->loss_reason }}</td>
                                            <td class="text-end pe-4 fw-bold text-danger">{{ number_format($loss->loss_amount) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted small">No loss records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Customer Credits Table -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-hand-holding-dollar me-2 text-warning"></i>Customer Credits (Deni la Mteja)</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 x-small border-0">CUSTOMER</th>
                                        <th class="x-small border-0">REFERENCE</th>
                                        <th class="text-end pe-4 x-small border-0">CREDIT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $hasCredits = false; @endphp
                                    @foreach($creditTasks as $task)
                                        @php $hasCredits = true; @endphp
                                        <tr>
                                            <td class="ps-4">
                                                <div class="small fw-bold">{{ $task->customer->name ?? 'N/A' }}</div>
                                                <div class="x-small text-muted">{{ $task->customer->phone ?? '' }}</div>
                                            </td>
                                            <td class="small text-muted">{{ $task->task_code }}</td>
                                            <td class="text-end pe-4 fw-bold text-success">{{ number_format(abs($task->balance)) }}</td>
                                        </tr>
                                    @endforeach
                                    @foreach($creditOrders as $order)
                                        @php $hasCredits = true; @endphp
                                        <tr>
                                            <td class="ps-4">
                                                <div class="small fw-bold">{{ $order->user->name ?? 'N/A' }}</div>
                                                <div class="x-small text-muted">{{ $order->user->phone ?? '' }}</div>
                                            </td>
                                            <td class="small text-muted">{{ $order->order_code }}</td>
                                            <td class="text-end pe-4 fw-bold text-success">{{ number_format(abs($order->balance)) }}</td>
                                        </tr>
                                    @endforeach
                                    @if(!$hasCredits)
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted small">No overpaid credits found.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            let dashChart;

            function toggleCashFlowChartDash(type) {
                const chartLabels = @json($chartData['labels'] ?? []);
                const incomeData = @json($chartData['income'] ?? []);
                const expenseData = @json($chartData['expenses'] ?? []);

                if (!chartLabels.length) {
                    console.warn('No chart labels available');
                    return;
                }

                if (dashChart) dashChart.destroy();

                const canvas = document.getElementById('cashFlowChart');
                if (!canvas) return;
                
                const ctx = canvas.getContext('2d');
                dashChart = new Chart(ctx, {
                    type: type,
                    data: {
                        labels: chartLabels,
                        datasets: [
                            {
                                label: 'Cash In',
                                data: incomeData,
                                borderColor: '#22c55e',
                                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 2
                            },
                            {
                                label: 'Cash Out',
                                data: expenseData,
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 6 } },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                titleColor: '#1e293b',
                                bodyColor: '#475569',
                                borderColor: '#e2e8f0',
                                borderWidth: 1,
                                padding: 12,
                                displayColors: true,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) label += ': ';
                                        if (context.parsed.y !== null) {
                                            label += new Intl.NumberFormat('en-US').format(context.parsed.y) + ' TZS';
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                            y: {
                                beginAtZero: true,
                                grid: { borderDash: [2, 2], color: '#f1f5f9' },
                                title: { display: true, text: 'Amount (TZS)', font: { size: 11, weight: 'bold' } },
                                ticks: { 
                                    font: { size: 10 },
                                    callback: (val) => val >= 1000 ? (val / 1000) + 'k' : val 
                                }
                            }
                        }
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                toggleCashFlowChartDash('line');

                // Allocation Chart
                const allocatedCanvas = document.getElementById('expenseAllocationChart');
                if (allocatedCanvas) {
                    const allocatedCtx = allocatedCanvas.getContext('2d');
                    const breakdownLabels = @json($expenseBreakdown->pluck('category') ?? []);
                    const breakdownData = @json($expenseBreakdown->pluck('total') ?? []);

                    if (breakdownLabels.length > 0) {
                        new Chart(allocatedCtx, {
                            type: 'doughnut',
                            data: {
                                labels: breakdownLabels,
                                datasets: [{
                                    data: breakdownData,
                                    backgroundColor: ['#3b82f6', '#ef4444', '#f59e0b', '#10b981', '#8b5cf6', '#ec4899', '#64748b'],
                                    borderWeight: 0
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'right', labels: { boxWidth: 12, padding: 15, font: { size: 11 } } }
                                },
                                cutout: '70%'
                            }
                        });
                    }
                }

                // Period Selection Handling
                const periodSelect = document.getElementById('periodSelect');
                const customDateRange = document.getElementById('customDateRange');

                if (periodSelect) {
                    periodSelect.addEventListener('change', function () {
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

    <style>
        .container-fluid {
            font-size: 13px;
        }

        .h2 {
            font-size: 1.5rem;
        }

        .h3 {
            font-size: 1.25rem;
        }

        h6 {
            font-size: 14px !important;
        }

        .table th {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            font-size: 13px;
        }

        .btn-sm {
            font-size: 12px;
        }

        .x-small {
            font-size: 11px;
        }

        .small {
            font-size: 13px;
        }

        .icon-circle, .icon-circle-sm {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }

        .icon-circle {
            width: 32px;
            height: 32px;
        }

        .icon-circle-sm {
            width: 26px;
            height: 26px;
            font-size: 0.8rem;
        }

        .hover-lift {
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-lift:hover {
            transform: translateY(-3px);
        }

        .card-body {
            padding: 0.85rem !important;
        }

        @media (max-width: 768px) {
            .h5 {
                font-size: 0.95rem !important;
            }

            .card-body {
                padding: 0.6rem !important;
            }

            .icon-circle-sm {
                width: 22px;
                height: 22px;
                font-size: 0.7rem;
            }
        }
    </style>
@endsection