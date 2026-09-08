@extends('layouts.admin')

@section('title', 'Saler Performance Dashboard')

@push('styles')
    <style>
        @media screen and (max-width: 768px) {
            .container-fluid { padding: 0.5rem; }
            h4 { font-size: 1.15rem !important; }
            .text-muted { font-size: 0.75rem !important; }
            .card-header h6 { font-size: 0.8rem !important; }
            .btn-sm { font-size: 0.7rem !important; }
            .table th, .table td { font-size: 0.75rem !important; padding: 0.375rem 0.5rem !important; }
        }

        @media print {
            @page { size: A4; margin: 8mm; }
            body { background: white !important; font-size: 10pt !important; margin: 0 !important; padding: 0 !important; color: #000 !important; }
            .btn, .sidebar, .sidebar-nav, .filter-section, .top-navbar, .mobile-menu-toggle, .btn-group, #filterCollapse, .card-header .btn, .breadcrumb, footer, .no-print { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; overflow: visible !important; }
            .container-fluid { width: 100% !important; padding: 0 !important; max-width: 100% !important; }
            .card { border: 1px solid #bfbcbc !important; box-shadow: none !important; margin-bottom: 8px !important; break-inside: avoid; }
            .card-header { border: none !important; padding-left: 0 !important; padding-bottom: 5px !important; border-bottom: 2px solid #000 !important; margin-bottom: 10px !important; }
            .card-header h5, .card-header h6 { font-size: 11pt !important; color: #000 !important; display: inline-block; margin-bottom: 0 !important; }
            .card-body { padding: 0.5rem !important; }
            .row { display: flex !important; flex-wrap: wrap !important; margin-left: -2px !important; margin-right: -2px !important; }
            .row > [class*="col-"] { padding: 0 2px !important; }
            .table { width: 100% !important; border-collapse: collapse !important; font-size: 9.5pt !important; color: #000 !important; }
            .table th, .table td { border: 1px solid #000 !important; padding: 4px 6px !important; }
            .table thead th { background-color: #eee !important; font-weight: 800 !important; -webkit-print-color-adjust: exact; color-adjust: exact; text-transform: uppercase; }
            canvas { max-width: 100% !important; height: auto !important; max-height: 180px !important; }
            .chart-container { height: 180px !important; }
            .print-only { display: block !important; }
            .report-header { margin-bottom: 10px; border-bottom: 2px solid #000; padding-bottom: 5px; }
            .report-header h1 { font-size: 18pt !important; margin: 0; }
            .print-footer { position: fixed; bottom: 0px; left: 0; right: 0; background: white !important; padding: 8px 0; border-top: 1px solid #000 !important; text-align: center; font-size: 8pt !important; color: #000 !important; }
            body { padding-bottom: 50px !important; }
        }

        .print-only { display: none; }

        .report-header { text-align: left; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .report-header img { max-width: 120px; margin-bottom: 10px; }
        .report-header h1 { font-size: 24px; font-weight: bold; margin: 0; }
        .report-header p { color: #666; margin: 2px 0; }
    </style>
@endpush

@section('content')
    <div class="container-fluid no-print-padding">
        <div class="print-only report-header mb-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    @include('partials.logo-print', ['logoStyle' => 'height:50px;object-fit:contain;'])
                    <h1 class="fw-bold text-dark mt-2" style="font-size: 20pt; margin-bottom: 0;">Saler Activity &amp; Revenue Report</h1>
                </div>
                <div class="text-end text-dark" style="font-size: 9pt;">
                    <p class="mb-0 fw-bold" style="font-size: 11pt;">CHIBOBRAND CO. LTD.</p>
                    <p class="mb-0">Period: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</p>
                    <p class="mb-0">Generated: {{ now()->format('M d, Y H:i') }}</p>
                </div>
            </div>
            <hr class="border-dark opacity-100 my-2">
        </div>

        {{-- ── PAGE HEADER ──────────────────────────────────────────── --}}
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3 gap-2 no-print">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="fas fa-user-tie text-danger me-2"></i>Saler Performance Report
                </h4>
                <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $period)) }} &middot; {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} &ndash; {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</small>
            </div>
            <div class="d-flex gap-2">
                @php
                    $salerBaseParams = ['saler_id' => $salerId, 'period' => $period];
                    if ($period === 'custom') {
                        $salerBaseParams['start_date'] = request('start_date');
                        $salerBaseParams['end_date']   = request('end_date');
                    }
                    $salerPdfUrl   = route('admin.saler-performance.export', array_merge($salerBaseParams, ['type' => 'pdf']));
                    $salerExcelUrl = route('admin.saler-performance.export', array_merge($salerBaseParams, ['type' => 'excel']));
                    $salerPrintUrl = route('admin.saler-performance.print') . '?' . http_build_query(request()->all());
                @endphp
                <x-report-export-menu
                    :print-url="$salerPrintUrl"
                    :pdf-url="$salerPdfUrl"
                    :excel-url="$salerExcelUrl"
                    label="Export"
                />
                <a href="{{ route('admin.saler.sales-report') }}" class="btn btn-danger btn-sm fw-bold">
                    <i class="fas fa-file-alt me-1"></i>Seller Activity
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-4 filter-section no-print" id="filterCollapse">
            <div class="card-body bg-light">
                <form action="{{ route('admin.saler-performance.index') }}" method="GET" class="row g-2 align-items-end" data-no-preloader data-no-global-handler>

                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-bold mb-1">Time Period</label>
                        <select name="period" id="periodSelect" class="form-select form-select-sm">
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
                    </div>

                    <div class="col-6 col-md-2 custom-date-group {{ ($period ?? '') == 'custom' ? '' : 'd-none' }}">
                        <label class="form-label small fw-bold mb-1">From</label>
                        <input type="date" class="form-control form-control-sm" name="start_date" value="{{ request('start_date', $dateFrom) }}">
                    </div>

                    <div class="col-6 col-md-2 custom-date-group {{ ($period ?? '') == 'custom' ? '' : 'd-none' }}">
                        <label class="form-label small fw-bold mb-1">To</label>
                        <input type="date" class="form-control form-control-sm" name="end_date" value="{{ request('end_date', $dateTo) }}">
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold mb-1">Salesperson</label>
                        <select class="form-select form-select-sm" name="saler_id">
                            <option value="">All Revenue Generated (Full Team)</option>
                            @foreach($allSalers as $s)
                                <option value="{{ $s->id }}" {{ $salerId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-danger btn-sm w-100 fw-bold">Apply</button>
                        <a href="{{ route('admin.saler-performance.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const periodSelect = document.getElementById('periodSelect');
                    const customDateGroups = document.querySelectorAll('.custom-date-group');

                    if (periodSelect) {
                        periodSelect.addEventListener('change', function () {
                            if (this.value === 'custom') {
                                customDateGroups.forEach(group => group.classList.remove('d-none'));
                            } else {
                                customDateGroups.forEach(group => group.classList.add('d-none'));
                            }
                        });
                    }
                });
            </script>
        @endpush

        {{-- Summary stats --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="fas fa-chart-pie text-danger me-2"></i>Summary</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-12 col-sm-6 col-md-2">
                        <div class="border rounded p-2 h-100 text-center">
                            <div class="small text-muted text-uppercase fw-bold mb-1"><i class="fas fa-users me-1"></i>Customers</div>
                            <div class="fs-5 fw-bold text-info">{{ number_format($summary['total_customers']) }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-2">
                        <div class="border rounded p-2 h-100 text-center">
                            <div class="small text-muted text-uppercase fw-bold mb-1"><i class="fas fa-tasks me-1"></i>Design Tasks</div>
                            <div class="fs-5 fw-bold text-primary">{{ number_format($summary['total_tasks']) }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-2">
                        <div class="border rounded p-2 h-100 text-center">
                            <div class="small text-muted text-uppercase fw-bold mb-1"><i class="fas fa-coins me-1"></i>Total Revenue</div>
                            <div class="fs-5 fw-bold text-success">TZS {{ number_format($summary['total_revenue']) }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-2">
                        <div class="border border-danger rounded p-2 h-100 text-center">
                            <div class="small text-muted text-uppercase fw-bold mb-1"><i class="fas fa-exclamation-circle me-1"></i>Balance Due</div>
                            <div class="fs-5 fw-bold text-danger">TZS {{ number_format($summary['balance_due']) }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-2">
                        <div class="border rounded p-2 h-100 text-center">
                            <div class="small text-muted text-uppercase fw-bold mb-1"><i class="fas fa-bullseye me-1"></i>Total Leads</div>
                            <div class="fs-5 fw-bold text-warning">{{ number_format($summary['total_leads']) }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-2">
                        <div class="border rounded p-2 h-100 text-center">
                            <div class="small text-muted text-uppercase fw-bold mb-1"><i class="fas fa-user-check me-1"></i>Active Salers</div>
                            <div class="fs-5 fw-bold text-secondary">{{ number_format($summary['active_salers']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Performance Table --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0"><i class="fas fa-trophy text-danger me-2"></i>Staff Performance Rankings</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Salesperson</th>
                                <th class="text-center">Orders</th>
                                <th class="text-center">Design Tasks</th>
                                <th class="text-end">Total Revenue</th>
                                <th class="text-end">Impact</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salers as $saler)
                                @php
                                    $revenue = $saler->designTasks->sum('price');
                                    $impact = $summary['total_revenue'] > 0 ? round(($revenue / $summary['total_revenue']) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $saler->name }}</div>
                                        <div class="small text-muted">{{ $saler->phone }}</div>
                                    </td>
                                    <td class="text-center">{{ $saler->saler_orders_count }}</td>
                                    <td class="text-center">{{ $saler->design_tasks_count }}</td>
                                    <td class="text-end fw-bold text-success">{{ number_format($revenue) }}</td>
                                    <td class="text-end">
                                        <div class="d-flex align-items-center justify-content-end">
                                            <div class="progress me-2" style="height: 5px; width: 60px;">
                                                <div class="progress-bar bg-dark" style="width: {{ $impact }}%"></div>
                                            </div>
                                            <span class="small fw-bold">{{ $impact }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Charts Section --}}
        <div class="row g-3 mb-4 charts-row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0"><i class="fas fa-chart-line text-dark me-2"></i>Revenue Trend</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 250px;">
                            <canvas id="salerTrendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0"><i class="fas fa-chart-pie text-dark me-2"></i>Order Status</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 250px;">
                            <canvas id="salerStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0"><i class="fas fa-chart-bar text-dark me-2"></i>Revenue Contribution by Staff</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="salerRevenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detailed Data Tables (Visible in Print) --}}
        <div class="row g-3 mb-4 detailed-tables">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0"><i class="fas fa-shopping-cart text-danger me-2"></i>Recent Team Orders</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Saler</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentOrders as $order)
                                        <tr>
                                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                                            <td>{{ $order->user->name ?? 'Walk-in' }}</td>
                                            <td>{{ $order->saler->name ?? 'N/A' }}</td>
                                            <td class="text-end fw-bold">{{ number_format($order->total_amount) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">No orders found for this period.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h6 class="fw-bold mb-0"><i class="fas fa-palette text-dark me-2"></i>Recent Team Design Tasks</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Task Title</th>
                                        <th>Saler</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentTasks as $task)
                                        <tr>
                                            <td class="text-truncate" style="max-width: 150px;">{{ $task->title }}</td>
                                            <td>{{ $task->saler->name ?? 'N/A' }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary" style="font-size: 0.7rem;">{{ ucfirst($task->status) }}</span>
                                            </td>
                                            <td class="text-end fw-bold">{{ number_format($task->price) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">No design tasks found for this period.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Print Only Footer (Repeats on every page) --}}
        <div class="print-only print-footer">
            <p class="mb-0">&copy; {{ date('Y') }} CHIBOBRAND CO. LTD. All rights reserved.</p>
            <p class="mb-0">Developed by <a href="https://fridoltech.org" style="color: #666; text-decoration: none; font-weight: bold;">Fridoltech</a></p>
        </div>
    </div>

    @push('scripts')
        <script>
            // Trend Chart
            const trendData = @json($trendData);
            const trendLabels = trendData.map(d => d.date);
            const revenueValues = trendData.map(d => d.revenue);

            new Chart(document.getElementById('salerTrendChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'Revenue',
                        data: revenueValues,
                        borderColor: '#111827',
                        backgroundColor: 'rgba(17, 24, 39, 0.05)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                        y: { ticks: { font: { size: 9 }, callback: v => v.toLocaleString() } }
                    }
                }
            });

            // Status Chart
            const statusData = @json($statusDistribution);
            new Chart(document.getElementById('salerStatusChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(statusData),
                    datasets: [{
                        data: Object.values(statusData),
                        backgroundColor: ['#6b7280', '#111827', '#dc2626', '#9ca3af'],
                        borderWidth: 0
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
                    },
                    cutout: '70%'
                }
            });

            // Bar Chart
            const salerNames = @json($salers->pluck('name'));
            const salerRevenues = @json($salers->map(fn($s) => $s->designTasks->sum('price')));

            const ctx = document.getElementById('salerRevenueChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: salerNames,
                    datasets: [{
                        label: 'Total Revenue Generated',
                        data: salerRevenues,
                        backgroundColor: 'rgba(17, 24, 39, 0.85)',
                        borderColor: '#111827',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { font: { size: 9 }, callback: v => v.toLocaleString() } },
                        y: { ticks: { font: { size: 10 } } }
                    }
                }
            });

            // Professional Print Title
            window.onbeforeprint = () => { document.title = "Saler_Performance_Report_{{ now()->format('Ymd') }}"; };
            window.onafterprint = () => { document.title = "@yield('title')"; };
        </script>
    @endpush
@endsection
