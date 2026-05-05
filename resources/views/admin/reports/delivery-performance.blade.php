@extends('layouts.admin')

@section('title', 'Delivery Performance Dashboard')

@push('styles')
<style>
    /* Mobile Responsive - Font Size Reductions */
    @media (max-width: 768px) {
        .container-fluid { padding: 0.5rem; }
        h2 { font-size: 1.25rem !important; }
        .text-muted { font-size: 0.75rem !important; }
        .card-header h6 { font-size: 0.8rem !important; }
        .btn-sm { font-size: 0.7rem !important; }
        .table th, .table td { font-size: 0.75rem !important; padding: 0.375rem 0.5rem !important; }
        .stats-col { flex: 0 0 50% !important; max-width: 50% !important; }
    }
    
    @media (max-width: 575.98px) {
        .container-fluid { padding: 0.25rem; }
        h2 { font-size: 1.1rem !important; }
        .card-body { padding: 0.75rem !important; }
    }

    .icon-circle {
        height: 40px;
        width: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .card-metric .h2 { font-size: 1.4rem; margin-bottom: 2px; }

    .avatar-circle {
        height: 28px;
        width: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: bold;
    }

    /* High-Density Print Layout */
    @media print {
        @page { size: A4; margin: 8mm; }
        body { background: white !important; font-size: 10pt !important; margin: 0 !important; padding: 0 !important; color: #000 !important; }
        .btn, .sidebar, .sidebar-nav, .filter-section, .top-navbar, .mobile-menu-toggle, .btn-group, #filterCollapse, .card-header .btn, .breadcrumb, footer, .d-flex.gap-1.no-print { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; overflow: visible !important; }
        .container-fluid { width: 100% !important; padding: 0 !important; max-width: 100% !important; }
        .card { border: 1px solid #000 !important; box-shadow: none !important; margin-bottom: 8px !important; break-inside: avoid; }
        .card-body { padding: 0.5rem !important; }
        
        /* Table Visibility & Contrast */
        .table { width: 100% !important; border-collapse: collapse !important; font-size: 9.5pt !important; color: #000 !important; }
        .table th, .table td { border: 1px solid #000 !important; padding: 4px 6px !important; }
        .table thead th { background-color: #eee !important; font-weight: 800 !important; -webkit-print-color-adjust: exact; color-adjust: exact; text-transform: uppercase; }
        
        .stats-col { flex: 0 0 25% !important; max-width: 25% !important; }
        
        /* Force charts to show and fit */
        canvas { max-width: 100% !important; height: auto !important; max-height: 180px !important; }
        
        .no-print { display: none !important; }
        .print-only { display: block !important; }
        .report-header { margin-bottom: 10px; border-bottom: 2px solid #000; padding-bottom: 5px; }
        .report-header h1 { font-size: 18pt !important; margin: 0; }
        
        /* Recurring Footer on Every Page */
        .print-footer {
            position: fixed;
            bottom: 0px;
            left: 0;
            right: 0;
            background: white !important;
            padding: 8px 0;
            border-top: 1px solid #000 !important;
            text-align: center;
            font-size: 8pt !important;
            color: #000 !important;
        }
        body { padding-bottom: 50px !important; }
        
        /* Layout adjustments for A4 - Horizontal flow */
        .row { display: flex !important; flex-wrap: wrap !important; margin-left: -2px !important; margin-right: -2px !important; }
        .row > [class*="col-"] { padding: 0 2px !important; }
        
        .metrics-row .col-6 { flex: 0 0 25% !important; max-width: 25% !important; }
        .charts-row .col-lg-8 { flex: 0 0 65% !important; max-width: 65% !important; }
        .charts-row .col-lg-4 { flex: 0 0 35% !important; max-width: 35% !important; }
        
        .detailed-list-section { display: block !important; width: 100% !important; margin-top: 10px !important; }
    }
    .print-only { display: none; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Print Only Header -->
    <div class="print-only report-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <img src="{{ asset('images/logo.webp') }}" alt="Logo" style="height: 50px;" onerror="this.style.display='none'">
                <h1 class="fw-bold text-dark mt-2">Delivery Performance Report</h1>
                <p class="mb-0 text-dark">Period: {{ ucfirst($period ?: 'Custom Range') }}</p>
            </div>
            <div class="text-end text-dark">
                <p class="mb-1 fw-bold">CHIBOBRAND CO. LTD.</p>
                <p class="mb-1">Generated: {{ now()->format('M d, Y H:i') }}</p>
                <p class="mb-0">Format: A4 Professional Analytics</p>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-row justify-content-between align-items-start mb-2 gap-2">
                <div class="flex-grow-1">
                    <h2 class="mb-0 fw-bold">Delivery Performance</h2>
                    <p class="text-muted mb-0 small">Task delivery tracking and personnel analytics</p>
                </div>
                <div class="d-flex gap-1 no-print">
                    <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                        <i class="fas fa-filter"></i>
                    </button>
                    @php $deliveryPdfUrl = route('admin.delivery-performance.export', array_filter(['period' => $period, 'delivery_person_id' => $deliveryPersonId])); @endphp
                    <a href="{{ $deliveryPdfUrl }}" class="btn btn-danger btn-sm" data-no-preloader title="Download PDF">
                        <i class="fas fa-file-pdf"></i>
                    </a>
                    <a href="{{ $deliveryPdfUrl }}" target="_blank" rel="noopener" class="btn btn-success btn-sm share-pdf-btn" data-pdf-url="{{ $deliveryPdfUrl }}" data-pdf-filename="delivery-performance-{{ $period ?? 'report' }}.pdf" title="Share PDF">
                        <i class="fas fa-share-alt me-1"></i> Share PDF
                    </a>
                    <button class="btn btn-primary btn-sm" onclick="window.print()">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="collapse {{ ($deliveryPersonId || request('period') || request('date_from')) ? 'show' : '' }} mb-4 filter-section" id="filterCollapse">
        <div class="card border-0 shadow-sm border-top border-4 border-primary">
            <div class="card-body bg-light p-3">
                <form action="{{ route('admin.delivery-performance.index') }}" method="GET" class="row g-2" data-no-preloader data-no-global-handler>
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Period</label>
                        <select class="form-select form-select-sm" name="period" id="periodSelect">
                            <option value="">Custom Range</option>
                            <option value="today" {{ ($period ?? '') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="week" {{ ($period ?? '') == 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ ($period ?? '') == 'month' && !request('date_from') ? 'selected' : '' }}>This Month</option>
                            <option value="quarter" {{ ($period ?? '') == 'quarter' ? 'selected' : '' }}>This Quarter</option>
                            <option value="half_year" {{ ($period ?? '') == 'half_year' ? 'selected' : '' }}>Last 6 Months</option>
                            <option value="year" {{ ($period ?? '') == 'year' ? 'selected' : '' }}>This Year</option>
                            <option value="all" {{ ($period ?? '') == 'all' ? 'selected' : '' }}>All Time</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">From</label>
                        <input type="date" class="form-control form-control-sm" name="date_from" id="dateFrom" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">To</label>
                        <input type="date" class="form-control form-control-sm" name="date_to" id="dateTo" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Delivery Person</label>
                        <select class="form-select form-select-sm" name="delivery_person_id">
                            <option value="">All Deliveries (Full Team)</option>
                            @foreach($allDeliveryPersonnel as $dp)
                                <option value="{{ $dp->id }}" {{ $deliveryPersonId == $dp->id ? 'selected' : '' }}>
                                    {{ $dp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end mt-2 mt-md-0">
                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold" data-no-global-handler>
                            <i class="fas fa-sync-alt me-1"></i> UPDATE
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Clear period when custom dates are used
        document.addEventListener('DOMContentLoaded', function() {
            const periodSelect = document.getElementById('periodSelect');
            const dateFrom = document.getElementById('dateFrom');
            const dateTo = document.getElementById('dateTo');

            if (dateFrom && dateTo && periodSelect) {
                dateFrom.addEventListener('change', function() {
                    if (this.value) periodSelect.value = '';
                });
                dateTo.addEventListener('change', function() {
                    if (this.value) periodSelect.value = '';
                });
                periodSelect.addEventListener('change', function() {
                    if (this.value) {
                        dateFrom.value = '';
                        dateTo.value = '';
                    }
                });
            }
        });
    </script>

    <!-- Main Metrics -->
    <div class="row g-2 mb-4 metrics-row">
        <div class="col-6 col-lg-3 stats-col">
            <div class="card border-0 shadow-sm h-100 card-metric">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2">
                            <i class="fas fa-tasks fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Total Deliveries</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-dark">{{ number_format($summary['total_deliveries']) }}</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3 stats-col">
            <div class="card border-0 shadow-sm h-100 card-metric">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-success bg-opacity-10 text-success me-2">
                            <i class="fas fa-check-circle fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Delivered</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-dark">{{ number_format($summary['delivered']) }}</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3 stats-col">
            <div class="card border-0 shadow-sm h-100 card-metric">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-warning bg-opacity-10 text-warning me-2">
                            <i class="fas fa-truck fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Pending</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-dark">{{ number_format($summary['pending']) }}</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3 stats-col">
            <div class="card border-0 shadow-sm h-100 card-metric">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-dark bg-opacity-10 text-dark me-2">
                            <i class="fas fa-users fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Active Staff</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-dark">{{ number_format($summary['active_personnel']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-line me-2"></i>Delivery Personnel Performance Rankings</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3 border-0 x-small">Delivery Person</th>
                            <th class="text-center border-0 x-small">Total Tasks</th>
                            <th class="text-center border-0 x-small">Delivered</th>
                            <th class="text-center border-0 x-small">Pending</th>
                            <th class="text-end pe-3 border-0 x-small">Impact</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deliveryPersonnel as $person)
                        @php
                            $impact = $summary['total_deliveries'] > 0 ? round(($person->total_tasks / $summary['total_deliveries']) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center py-1">
                                    <div class="avatar-circle bg-info text-white me-2">
                                        {{ substr($person->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="small fw-bold">{{ $person->name }}</div>
                                        <div class="x-small text-muted">{{ $person->phone }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center small">{{ $person->total_tasks }}</td>
                            <td class="text-center small">{{ $person->delivered_count }}</td>
                            <td class="text-center small">{{ $person->pending_count }}</td>
                            <td class="text-end pe-3">
                                <div class="d-flex align-items-center justify-content-end">
                                    <div class="progress me-2" style="height: 4px; width: 50px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $impact }}%"></div>
                                    </div>
                                    <span class="x-small fw-bold">{{ $impact }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-3 mb-4 charts-row">
        <!-- Delivery Trend -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-line me-2"></i>Delivery Trend (Last 30 Days)</h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="deliveryTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- Status Distribution -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-pie me-2"></i>Delivery Status</h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="deliveryStatusChart"></canvas>
                    </div>
                </div>
            </div>
    <!-- Detailed List Section (Advanced Printing) -->
    <div class="row g-3 mb-4 detailed-list-section">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-list me-2"></i>Recent Deliveries Activity</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th class="ps-3">Date</th>
                                    <th>Task Code</th>
                                    <th>Customer</th>
                                    <th>Delivery Person</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-3">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentDeliveries as $delivery)
                                <tr>
                                    <td class="ps-3 small">{{ $delivery->delivered_at ? $delivery->delivered_at->format('M d, Y') : $delivery->created_at->format('M d, Y') }}</td>
                                    <td class="small fw-bold">{{ $delivery->task_code }}</td>
                                    <td class="small">{{ $delivery->customer->name ?? 'N/A' }}</td>
                                    <td class="small">{{ $delivery->delivery->name ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-opacity-10 
                                            {{ $delivery->delivery_status == 'delivered' ? 'bg-success text-success' : ($delivery->delivery_status == 'pending' ? 'bg-warning text-dark' : 'bg-danger text-danger') }}" style="font-size: 0.65rem;">
                                            {{ ucfirst($delivery->delivery_status) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3 small text-muted italic">{{ Str::limit($delivery->delivery_notes, 30) ?: '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No special delivery records found.</td>
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
        <p class="mb-0">Developed by <a href="https://fridoltech.org" style="color: #000; text-decoration: none; font-weight: bold;">Fridoltech</a></p>
    </div>
</div>

@push('scripts')
<script>
    // Trend Chart
    const trendData = @json($trendData);
    const trendLabels = trendData.map(d => d.date);
    const countValues = trendData.map(d => d.count);

    new Chart(document.getElementById('deliveryTrendChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                label: 'Deliveries',
                data: countValues,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
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
    new Chart(document.getElementById('deliveryStatusChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
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

    // Professional Print Title
    window.onbeforeprint = () => { document.title = "Delivery_Performance_Report_{{ now()->format('Ymd') }}"; };
    window.onafterprint = () => { document.title = "@yield('title')"; };
</script>
@endpush
@endsection
