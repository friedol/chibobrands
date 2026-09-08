@extends('layouts.admin')

@section('title', 'Financial Dashboard')

@push('styles')
<style>
    .section-label {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        color: #94a3b8;
        text-transform: uppercase;
        margin-bottom: 0.6rem;
        padding-left: 2px;
    }
    /* Compact Stat Card Styles — matches admin dashboard */
    .cust-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 9px 11px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: all 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .cust-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .cust-stat-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
    }
    .cust-stat-val {
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.25;
        margin-top: 4px;
    }
    .cust-stat-lbl {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        margin-top: 1px;
    }
    .cust-stat-sub {
        font-size: 10px;
        font-weight: 500;
        color: #94a3b8;
    }
    .hover-lift { transition: transform 0.2s cubic-bezier(0.4,0,0.2,1), box-shadow 0.2s; }
    .icon-circle { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 0.8rem; }
    .chart-card { border-radius: 16px; }
    .chart-toggle-btn { border-radius: 8px !important; font-size: 0.75rem; padding: 4px 10px; }
    .x-small { font-size: 11px; }
    .table th { font-size: 11px; letter-spacing: 0.04em; }
    .table td { font-size: 13px; }
</style>
@endpush

@section('content')
    <div class="container-fluid">

        {{-- ── HEADER ────────────────────────────────────────────────── --}}
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
                                class="form-select form-select-sm rounded-3 px-3 border-0 shadow-sm fw-bold x-small"
                                style="background-color: #f8f9fa; cursor: pointer; min-width: 120px;">
                                <option value="today" {{ ($period ?? '') == 'today' || !isset($period) ? 'selected' : '' }}>Today</option>
                                <option value="yesterday" {{ ($period ?? '') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                                <option value="week" {{ ($period ?? '') == 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ ($period ?? '') == 'month' ? 'selected' : '' }}>This Month</option>
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
                                <button type="submit" class="btn btn-primary btn-sm rounded-3 px-2 x-small">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── INCOME & REVENUE ──────────────────────────────────────── --}}
        <div class="section-label"><i class="fas fa-coins me-1"></i>Income & Revenue</div>
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-4 col-lg-2">
                <div class="cust-stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="cust-stat-icon bg-info-subtle text-info"><i class="fas fa-tags"></i></div>
                        <span class="cust-stat-sub">Stable</span>
                    </div>
                    <div class="cust-stat-val text-dark">{{ number_format($totalBilled ?? 0) }}</div>
                    <div class="cust-stat-lbl">Total Sales</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="cust-stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="cust-stat-icon bg-primary-subtle text-primary"><i class="fas fa-chart-line"></i></div>
                        <span class="cust-stat-sub">Stable</span>
                    </div>
                    <div class="cust-stat-val text-primary" style="font-size:1.02rem;">TZS {{ number_format($totalRevenue ?? 0) }}</div>
                    <div class="cust-stat-lbl">Total Revenue</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="cust-stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="cust-stat-icon bg-success-subtle text-success"><i class="fas fa-wallet"></i></div>
                        <span class="cust-stat-sub">Stable</span>
                    </div>
                    <div class="cust-stat-val text-success" style="font-size:1.02rem;">TZS {{ number_format($totalIn) }}</div>
                    <div class="cust-stat-lbl">Total Collected</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="cust-stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="cust-stat-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-hand-holding-dollar"></i></div>
                        <span class="cust-stat-sub">Stable</span>
                    </div>
                    <div class="cust-stat-val" style="font-size:1.02rem;color:#059669;">TZS {{ number_format($debtCollected ?? 0) }}</div>
                    <div class="cust-stat-lbl">Debt Collected</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.finance.pending-payments') }}" class="text-decoration-none">
                    <div class="cust-stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="cust-stat-icon bg-warning-subtle text-warning"><i class="fas fa-clock"></i></div>
                            <span class="cust-stat-sub">Stable</span>
                        </div>
                        <div class="cust-stat-val text-warning" style="font-size:1.02rem;">TZS {{ number_format($balanceDue ?? 0) }}</div>
                        <div class="cust-stat-lbl">Balance Due</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="cust-stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="cust-stat-icon" style="background:#f3e8ff;color:#7c3aed;"><i class="fas fa-scale-balanced"></i></div>
                        <span class="cust-stat-sub">Stable</span>
                    </div>
                    <div class="cust-stat-val" style="font-size:1.02rem;color:#7c3aed;">TZS {{ number_format($netProfit) }}</div>
                    <div class="cust-stat-lbl">Net Profit</div>
                </div>
            </div>
        </div>

        {{-- ── OUTFLOWS & LIABILITIES ────────────────────────────────── --}}
        <div class="section-label mt-3"><i class="fas fa-arrow-trend-down me-1"></i>Outflows & Liabilities</div>
        <div class="row g-2 mb-4">
            <div class="col-6 col-md-3">
                <div class="cust-stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="cust-stat-icon bg-danger-subtle text-danger"><i class="fas fa-receipt"></i></div>
                        <span class="cust-stat-sub">Stable</span>
                    </div>
                    <div class="cust-stat-val text-danger" style="font-size:1.02rem;">TZS {{ number_format($totalOut) }}</div>
                    <div class="cust-stat-lbl">Total Expenses</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="cust-stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="cust-stat-icon" style="background:#ffe4e6;color:#e11d48;"><i class="fas fa-exclamation-triangle"></i></div>
                        <span class="cust-stat-sub">Stable</span>
                    </div>
                    <div class="cust-stat-val" style="font-size:1.02rem;color:#e11d48;">TZS {{ number_format($totalLosses ?? 0) }}</div>
                    <div class="cust-stat-lbl">Losses (Hasara)</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="cust-stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="cust-stat-icon bg-danger-subtle text-danger"><i class="fas fa-file-invoice-dollar"></i></div>
                        <span class="cust-stat-sub">Stable</span>
                    </div>
                    <div class="cust-stat-val text-danger" style="font-size:1.02rem;">TZS {{ number_format($generalBalanceDue ?? 0) }}</div>
                    <div class="cust-stat-lbl">Global Balance Due</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="cust-stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="cust-stat-icon bg-warning-subtle text-warning"><i class="fas fa-hand-holding-usd"></i></div>
                        <span class="cust-stat-sub">Stable</span>
                    </div>
                    <div class="cust-stat-val text-warning" style="font-size:1.02rem;">TZS {{ number_format($totalCredits ?? 0) }}</div>
                    <div class="cust-stat-lbl">Customer Credits</div>
                </div>
            </div>
        </div>

        {{-- ── CHARTS ────────────────────────────────────────────────── --}}
        <div class="section-label"><i class="fas fa-chart-bar me-1"></i>Analytics</div>
        <div class="row g-3 mb-4">
            {{-- Cash Flow Bar Chart --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm chart-card h-100">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Cash Flow Trend</h6>
                            <p class="x-small text-muted mb-0">{{ ucfirst(str_replace('_', ' ', $period ?? 'Today')) }}</p>
                        </div>
                        <div class="d-flex gap-1">
                            <button id="btnBar" onclick="setCashFlowChart('bar')"
                                class="btn btn-sm btn-primary chart-toggle-btn">
                                <i class="fas fa-chart-bar me-1"></i>Bar
                            </button>
                            <button id="btnLine" onclick="setCashFlowChart('line')"
                                class="btn btn-sm btn-outline-secondary chart-toggle-btn">
                                <i class="fas fa-chart-line me-1"></i>Line
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-0 pb-3">
                        <div style="height: 310px; position: relative;">
                            <canvas id="cashFlowChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Expense Allocation Horizontal Bar --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm chart-card h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="fw-bold mb-0 text-dark">Expense Allocation</h6>
                        <p class="x-small text-muted mb-0">Spending by category</p>
                    </div>
                    <div class="card-body pt-0 pb-3">
                        <div style="height: 310px; position: relative;">
                            <canvas id="expenseAllocationChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── DEPARTMENTAL P&L & RECENT REVENUE ────────────────────── --}}
        <div class="section-label"><i class="fas fa-building me-1"></i>Department Performance</div>
        <div class="row g-3 mb-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-building-user me-2 text-info"></i>Departmental P&L</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 border-0">Department</th>
                                        <th class="text-end border-0">Total Sales</th>
                                        <th class="text-end border-0">Revenue</th>
                                        <th class="text-end border-0">Expenses</th>
                                        <th class="text-end pe-4 border-0">Profit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($departmentReports as $report)
                                        <tr>
                                            <td class="ps-4 fw-bold small">{{ $report->name }}</td>
                                            <td class="text-end small text-primary">TZS {{ number_format($report->total_sales) }}</td>
                                            <td class="text-end small text-success">TZS {{ number_format($report->total_revenue) }}</td>
                                            <td class="text-end small text-danger">TZS {{ number_format($report->total_expenses) }}</td>
                                            <td class="text-end pe-4 fw-bold small {{ $report->profit >= 0 ? 'text-success' : 'text-danger' }}">
                                                TZS {{ number_format($report->profit) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-money-bill-transfer me-2 text-success"></i>Recent Revenue</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($recentPayments as $payment)
                                <div class="list-group-item border-0 py-3 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="icon-circle bg-success bg-opacity-10 text-success">
                                            <i class="fas fa-plus fa-sm"></i>
                                        </div>
                                        <div>
                                            <div class="small fw-bold">{{ $payment->customer->name ?? 'Guest' }}</div>
                                            <div class="x-small text-muted">{{ $payment->department->name ?? 'POS' }}</div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small fw-bold text-success">+TZS {{ number_format($payment->amount) }}</div>
                                        <div class="x-small text-muted">{{ $payment->date->diffForHumans() }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── LOSS RECORDS & CUSTOMER CREDITS ──────────────────────── --}}
        <div class="section-label"><i class="fas fa-file-invoice me-1"></i>Records</div>
        <div class="row g-3 mb-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-heart-crack me-2 text-danger"></i>Hasara ya Kazi (Loss Records)</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 border-0">Date</th>
                                        <th class="border-0">Task / Customer</th>
                                        <th class="border-0">Reason</th>
                                        <th class="text-end pe-4 border-0">Amount</th>
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
                                            <td class="small text-muted">{{ $loss->loss_reason }}</td>
                                            <td class="text-end pe-4 fw-bold text-danger small">TZS {{ number_format($loss->loss_amount) }}</td>
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
                                        <th class="ps-4 border-0">Customer</th>
                                        <th class="border-0">Reference</th>
                                        <th class="text-end pe-4 border-0">Credit</th>
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
                                            <td class="text-end pe-4 fw-bold text-success small">TZS {{ number_format(abs($task->balance)) }}</td>
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
                                            <td class="text-end pe-4 fw-bold text-success small">TZS {{ number_format(abs($order->balance)) }}</td>
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
@endsection

@push('scripts')
<script>
    let dashChart;
    const cashLabels   = @json($chartData['labels'] ?? []);
    const cashIncome   = @json($chartData['income'] ?? []);
    const cashExpenses = @json($chartData['expenses'] ?? []);

    function setCashFlowChart(type) {
        if (dashChart) dashChart.destroy();

        const isBar = type === 'bar';
        document.getElementById('btnBar').className  = 'btn btn-sm chart-toggle-btn ' + (isBar ? 'btn-primary' : 'btn-outline-secondary');
        document.getElementById('btnLine').className = 'btn btn-sm chart-toggle-btn ' + (!isBar ? 'btn-primary' : 'btn-outline-secondary');

        dashChart = new Chart(
            document.getElementById('cashFlowChart').getContext('2d'),
            {
                type: type,
                data: {
                    labels: cashLabels,
                    datasets: [
                        {
                            label: 'Cash In',
                            data: cashIncome,
                            backgroundColor: isBar ? 'rgba(25,135,84,0.85)' : 'rgba(25,135,84,0.08)',
                            borderColor: '#198754',
                            borderWidth: isBar ? 0 : 2,
                            borderRadius: isBar ? 6 : 0,
                            fill: !isBar,
                            tension: 0.4,
                            pointRadius: isBar ? 0 : 4,
                            pointBackgroundColor: '#198754',
                        },
                        {
                            label: 'Cash Out',
                            data: cashExpenses,
                            backgroundColor: isBar ? 'rgba(220,53,69,0.85)' : 'rgba(220,53,69,0.08)',
                            borderColor: '#dc3545',
                            borderWidth: isBar ? 0 : 2,
                            borderRadius: isBar ? 6 : 0,
                            fill: !isBar,
                            tension: 0.4,
                            pointRadius: isBar ? 0 : 4,
                            pointBackgroundColor: '#dc3545',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: { boxWidth: 12, padding: 16, font: { size: 11 } }
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: ctx => ' ' + ctx.dataset.label + ': TZS ' + (ctx.parsed.y ?? 0).toLocaleString()
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                            ticks: {
                                font: { size: 11 },
                                callback: v => 'TZS ' + (v >= 1000 ? (v/1000).toFixed(0)+'K' : v)
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 } }
                        }
                    }
                }
            }
        );
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Default: bar chart
        setCashFlowChart('bar');

        // Expense Allocation — horizontal bar
        const allocCtx = document.getElementById('expenseAllocationChart');
        if (allocCtx) {
            const breakdownLabels = @json($expenseBreakdown->pluck('category') ?? []);
            const breakdownData   = @json($expenseBreakdown->pluck('total') ?? []);
            const palette = ['#3b82f6','#ef4444','#f59e0b','#10b981','#8b5cf6','#ec4899','#64748b','#0dcaf0','#fd7e14'];

            if (breakdownLabels.length > 0) {
                new Chart(allocCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: breakdownLabels,
                        datasets: [{
                            label: 'Amount (TZS)',
                            data: breakdownData,
                            backgroundColor: palette.slice(0, breakdownLabels.length),
                            borderRadius: 6,
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                padding: 10,
                                cornerRadius: 8,
                                callbacks: {
                                    label: ctx => ' TZS ' + (ctx.parsed.x ?? 0).toLocaleString()
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                                ticks: {
                                    font: { size: 10 },
                                    callback: v => v >= 1000 ? (v/1000).toFixed(0)+'K' : v
                                }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { font: { size: 11 } }
                            }
                        }
                    }
                });
            }
        }

        // Period filter auto-submit
        const periodSelect    = document.getElementById('periodSelect');
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
