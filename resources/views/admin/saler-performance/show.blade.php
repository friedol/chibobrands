@extends('layouts.admin')

@section('title', 'Saler Performance - ' . $saler->name)

@section('content')
<div class="container-fluid">
    <!-- Print Only Header -->
    <div class="print-only report-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                @include('partials.logo-print', ['logoStyle' => 'height:50px;object-fit:contain;'])
                <h1 class="fw-bold text-dark mt-2">Individual Performance Report</h1>
                <p class="mb-0 text-dark">Staff: {{ $saler->name }} ({{ $saler->phone }})</p>
                <p class="mb-0 text-dark">Period: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</p>
            </div>
            <div class="text-end text-dark">
                <p class="mb-1 fw-bold">CHIBOBRAND CO. LTD.</p>
                <p class="mb-1">Generated: {{ now()->format('M d, Y H:i') }}</p>
                <p class="mb-0">Format: A4 Professional Analytics</p>
            </div>
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.saler-performance.index') }}" class="text-decoration-none">
                    <i class="fas fa-chart-line me-1"></i>Saler Performance
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                {{ $saler->name }} Analytics
            </li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="mb-4 no-print">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center overflow-hidden">
                <div class="flex-shrink-0">
                    <div class="bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px; border: 2px solid #fff;">
                        <i class="fas fa-user text-white fs-5"></i>
                    </div>
                </div>
                <div class="ms-3 overflow-hidden">
                    <h4 class="mb-0 fw-bold text-truncate">{{ $saler->name }}</h4>
                </div>
            </div>
            <div class="action-icons d-flex gap-2 ms-2">
                <a href="{{ route('admin.saler-performance.export', ['type' => 'pdf', 'saler_id' => $saler->id]) }}" class="text-danger fs-5" data-no-preloader data-no-global-handler title="Download PDF">
                    <i class="fas fa-file-pdf"></i>
                </a>
                @php $salerShowPdfUrl = route('admin.saler-performance.export', ['type' => 'pdf', 'saler_id' => $saler->id]); @endphp
                <a href="{{ $salerShowPdfUrl }}" target="_blank" rel="noopener" class="text-success fs-5 share-pdf-btn" data-pdf-url="{{ $salerShowPdfUrl }}" data-pdf-filename="saler-{{ $saler->id }}-performance.pdf" title="Share PDF">
                    <i class="fas fa-share-alt"></i>
                </a>
                <a href="{{ route('admin.saler-performance.export', ['type' => 'excel', 'saler_id' => $saler->id]) }}" class="text-success fs-5" data-no-preloader data-no-global-handler title="Export Excel">
                    <i class="fas fa-file-excel"></i>
                </a>
                <button class="text-primary fs-5 border-0 bg-transparent p-0" onclick="window.print()" title="Print Report">
                    <i class="fas fa-print"></i>
                </button>
            </div>
        </div>
        <div class="d-flex align-items-center justify-content-between">
            <div class="text-muted x-small">
                <span class="badge bg-soft-primary text-primary me-2">Salesperson</span>
                <i class="fas fa-phone me-1"></i>{{ $saler->phone }}
            </div>
            <div class="d-inline-flex border rounded p-1 bg-white shadow-sm">
                <select id="timeframe-selector" class="form-select form-select-sm border-0 shadow-none x-small py-0" style="width: 130px;">
                    <option value="daily">Last 14 Days</option>
                    <option value="weekly">Last 8 Weeks</option>
                    <option value="monthly" selected>Last 12 Months</option>
                    <option value="yearly">Last 5 Years</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="row mb-4 g-2 g-md-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                <div class="card-body p-2 p-md-4 text-center">
                    <div class="stats-icon text-primary mx-auto mb-2" style="font-size: 1.2rem;">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="text-muted x-small mb-1">Total Revenue</div>
                    <h5 id="stat-revenue" class="mb-0 fw-bold">TZS 0</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                <div class="card-body p-2 p-md-4 text-center">
                    <div class="stats-icon text-success mx-auto mb-2" style="font-size: 1.2rem;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="text-muted x-small mb-1">Total Customers</div>
                    <h5 id="stat-customers" class="mb-0 fw-bold">0</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                <div class="card-body p-2 p-md-4 text-center">
                    <div class="stats-icon text-warning mx-auto mb-2" style="font-size: 1.2rem;">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="text-muted x-small mb-1">Total Orders</div>
                    <h5 id="stat-orders" class="mb-0 fw-bold">0</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
                <div class="card-body p-2 p-md-4 text-center">
                    <div class="stats-icon text-info mx-auto mb-2" style="font-size: 1.2rem;">
                        <i class="fas fa-paint-brush"></i>
                    </div>
                    <div class="text-muted x-small mb-1">Design Tasks</div>
                    <h5 id="stat-tasks" class="mb-0 fw-bold">0</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold">Revenue Trend</h5>
                </div>
                <div class="card-body" style="height: 250px; position: relative;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold">Revenue Distribution</h5>
                </div>
                <div class="card-body" style="height: 250px; position: relative;">
                    <canvas id="revenueSourceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold">Customer Acquisition Trend</h5>
                </div>
                <div class="card-body" style="height: 200px; position: relative;">
                    <canvas id="customerChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables Section (Advanced Printing) -->
    <div class="row g-4 mb-4 detailed-data-section">
        <!-- Recent Orders Table -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-shopping-cart me-2"></i>Recent Orders</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Date</th>
                                    <th>Customer</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-3">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                <tr>
                                    <td class="ps-3">{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>{{ $order->user->name ?? 'Walk-in' }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $order->approval_status == 'approved' ? 'bg-success' : ($order->approval_status == 'cancelled' ? 'bg-danger' : 'bg-warning') }} rounded-pill">
                                            {{ ucfirst($order->approval_status) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3 fw-bold">{{ number_format($order->total_amount) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No recent orders found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Tasks Table -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-info"><i class="fas fa-palette me-2"></i>Recent Design Tasks</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Task Title</th>
                                    <th>Customer</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-3">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTasks as $task)
                                <tr>
                                    <td class="ps-3 text-truncate" style="max-width: 150px;">{{ $task->title }}</td>
                                    <td>{{ $task->customer->name ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-info rounded-pill">
                                            {{ ucfirst($task->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3 fw-bold">{{ number_format($task->price) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No recent design tasks found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
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

@push('styles')
<style>
    .bg-soft-primary { background-color: rgba(78, 115, 223, 0.1); }
    .text-primary { color: #4e73df !important; }
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }
    .border-start.border-primary { border-left-color: #4e73df !important; }
    .border-start.border-success { border-left-color: #1cc88a !important; }
    .border-start.border-warning { border-left-color: #f6c23e !important; }
    .border-start.border-info { border-left-color: #36b9cc !important; }

    @media print {
        @page { size: A4; margin: 8mm; }
        body { background: white !important; font-size: 10pt !important; margin: 0 !important; padding: 0 !important; }
        .btn, .sidebar, .sidebar-nav, .top-navbar, .breadcrumb, .mobile-menu-toggle, .btn-group, #timeframe-selector, .no-print, .badge, footer { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; overflow: visible !important; }
        .container-fluid { width: 100% !important; padding: 0 !important; max-width: 100% !important; }
        .card { border: 1px solid #eee !important; box-shadow: none !important; margin-bottom: 10px !important; break-inside: avoid; }
        .card-body { padding: 0.5rem !important; }
        
        /* High density layout for stats cards */
        .row { display: flex !important; flex-wrap: wrap !important; margin-left: -5px !important; margin-right: -5px !important; }
        .col-md-3 { flex: 0 0 25% !important; max-width: 25% !important; padding: 0 5px !important; }
        
        /* Charts side-by-side in print */
        .col-lg-8 { flex: 0 0 60% !important; max-width: 60% !important; padding: 0 5px !important; }
        .col-lg-4 { flex: 0 0 40% !important; max-width: 40% !important; padding: 0 5px !important; }
        .col-lg-12 { flex: 0 0 100% !important; max-width: 100% !important; padding: 0 5px !important; }
        
        canvas { max-width: 100% !important; height: auto !important; max-height: 180px !important; }
        
        /* Table Visibility & Contrast */
        .table { width: 100% !important; border-collapse: collapse !important; font-size: 10pt !important; color: #000 !important; }
        .table th, .table td { border: 1.5px solid #000 !important; padding: 5px 8px !important; }
        .table thead th { background-color: #eee !important; font-weight: 800 !important; -webkit-print-color-adjust: exact; color-adjust: exact; text-transform: uppercase; }

        .chart-container { height: 200px !important; }
        
        .print-only { display: block !important; }
        .report-header { margin-bottom: 15px; border-bottom: 3px solid #000; padding-bottom: 5px; }
        .report-header h1 { font-size: 18pt !important; margin: 0; }
        
        /* Show detailed tables in full width for clarity */
        .detailed-data-section { display: block !important; margin-top: 15px !important; }
        .detailed-data-section .col-lg-6 { flex: 0 0 100% !important; max-width: 100% !important; margin-bottom: 15px !important; }
        .detailed-data-section .card { border: 1.5px solid #000 !important; }
        
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
    }
    .print-only { display: none; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const timeframeSelector = document.getElementById('timeframe-selector');
    let revenueChart, customerChart, revenueSourceChart;
    const salerId = {{ $saler->id }};

    function formatCurrency(value) {
        return 'TZS ' + new Intl.NumberFormat().format(Math.round(value));
    }

    async function updateCharts() {
        const timeframe = timeframeSelector.value;
        const url = `{{ route('admin.saler-performance.chart-data') }}?timeframe=${timeframe}&saler_id=${salerId}`;
        
        try {
            const response = await fetch(url);
            const data = await response.json();
            
            // Calculate totals
            const totalRevenue = data.revenue.reduce((a, b) => a + b, 0);
            const totalCustomers = data.customers.reduce((a, b) => a + b, 0);
            const totalOrdersRevenue = data.orders_revenue.reduce((a, b) => a + b, 0);
            const totalTasksRevenue = data.tasks_revenue.reduce((a, b) => a + b, 0);
            const totalOrders = data.orders_count.reduce((a, b) => a + b, 0);
            const totalTasks = data.tasks_count.reduce((a, b) => a + b, 0);
            
            // Update Stats Cards
            document.getElementById('stat-revenue').textContent = formatCurrency(totalRevenue);
            document.getElementById('stat-customers').textContent = totalCustomers;
            document.getElementById('stat-orders').textContent = totalOrders;
            document.getElementById('stat-tasks').textContent = totalTasks;

            // Revenue Trend Chart
            if (revenueChart) revenueChart.destroy();
            const ctxRev = document.getElementById('revenueChart').getContext('2d');
            revenueChart = new Chart(ctxRev, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Revenue',
                        data: data.revenue,
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        borderColor: '#4e73df',
                        borderWidth: 3,
                        pointBackgroundColor: '#4e73df',
                        pointBorderColor: '#fff',
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) { return formatCurrency(value); }
                            }
                        }
                    }
                }
            });

            // Revenue Source Pie Chart
            if (revenueSourceChart) revenueSourceChart.destroy();
            const ctxSource = document.getElementById('revenueSourceChart').getContext('2d');
            
            // If no data, show a placeholder-like state or at least the chart with a grey ring
            const hasRevenueData = totalOrdersRevenue > 0 || totalTasksRevenue > 0;
            
            revenueSourceChart = new Chart(ctxSource, {
                type: 'doughnut',
                data: {
                    labels: hasRevenueData ? ['Orders', 'Design Tasks'] : ['No Data'],
                    datasets: [{
                        data: hasRevenueData ? [totalOrdersRevenue, totalTasksRevenue] : [1],
                        backgroundColor: hasRevenueData ? ['#e74a3b', '#6f42c1'] : ['#f0f0f0'],
                        borderWidth: 0,
                        hoverOffset: hasRevenueData ? 10 : 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            position: 'bottom',
                            display: hasRevenueData
                        },
                        tooltip: {
                            enabled: hasRevenueData
                        }
                    },
                    cutout: '70%'
                }
            });

            // Customer Trend Chart
            if (customerChart) customerChart.destroy();
            const ctxCust = document.getElementById('customerChart').getContext('2d');
            customerChart = new Chart(ctxCust, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'New Customers',
                        data: data.customers,
                        backgroundColor: 'rgba(28, 200, 138, 0.8)',
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });

        } catch (error) {
            console.error('Error fetching chart data:', error);
        }
    }

    timeframeSelector.addEventListener('change', updateCharts);
    updateCharts();

    // Professional Print Title
    window.onbeforeprint = () => { document.title = "Saler_Performance_{{ $saler->name }}_{{ now()->format('Ymd') }}"; };
    window.onafterprint = () => { document.title = "@yield('title')"; };
});
</script>
@endpush
@endsection
