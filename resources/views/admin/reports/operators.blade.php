@extends('layouts.admin')

@section('title', 'Operator Performance Reports')

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

    @media print {
        @page { size: A4; margin: 8mm; }
        body { background: white !important; font-size: 10pt !important; margin: 0 !important; padding: 0 !important; color: #000 !important; color-adjust: exact; -webkit-print-color-adjust: exact; }
        .btn, .sidebar, .sidebar-nav, .filter-section, .top-navbar, .mobile-menu-toggle, .btn-group, #filterCollapse, .card-header .btn, .breadcrumb, footer, .no-print { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; overflow: visible !important; }
        .container-fluid { width: 100% !important; padding: 0 !important; max-width: 100% !important; }
        .no-print-padding { padding: 0 !important; }
        
        /* Unified Card Style for Print */
        .card { border: 1px solid #bfbcbcff !important; box-shadow: none !important; margin-bottom: 8px !important; break-inside: avoid; }
        .card-header { border: none !important; padding-left: 0 !important; padding-bottom: 5px !important; border-bottom: 2px solid #000 !important; margin-bottom: 10px !important; }
        .card-header h5, .card-header h6 { font-size: 11pt !important; color: #000 !important; display: inline-block; margin-bottom: 0 !important; }
        .card-body { padding: 0.5rem !important; }
        
        /* Layout adjustments for A4 - Horizontal flow */
        .row { display: flex !important; flex-wrap: wrap !important; margin-left: -2px !important; margin-right: -2px !important; }
        .row > [class*="col-"] { padding: 0 2px !important; }
        
        .row.g-2.mb-4 .col-6 { flex: 0 0 25% !important; max-width: 25% !important; }
        .row.g-3.mb-4 .col-lg-8 { flex: 0 0 65% !important; max-width: 65% !important; }
        .row.g-3.mb-4 .col-lg-4 { flex: 0 0 35% !important; max-width: 35% !important; }
        
        /* Table Visibility & Contrast */
        .table { width: 100% !important; border-collapse: collapse !important; font-size: 9.5pt !important; color: #000 !important; }
        .table th, .table td { border: 1px solid #000 !important; padding: 4px 6px !important; }
        .table thead th { background-color: #eee !important; font-weight: 800 !important; -webkit-print-color-adjust: exact; color-adjust: exact; text-transform: uppercase; }
        
        /* Chart sizing */
        canvas { max-width: 100% !important; height: auto !important; max-height: 180px !important; }
        
        .print-only { display: block !important; }
        .report-header { margin-bottom: 10px; border-bottom: 2px solid #000; padding-bottom: 5px; }
        .report-header h1 { font-size: 18pt !important; margin: 0; }
        
        /* Fixed Footer at bottom of pages */
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
        .star-rating { color: #ccac00 !important; -webkit-print-color-adjust: exact; }
    }
    
    .print-only { display: none; }
    .star-rating { color: #ffc107; font-size: 0.8rem; }
</style>
@endpush

@section('content')
<div class="container-fluid no-print-padding">
    <div class="print-only report-header mb-4">
        <div class="d-flex justify-content-between align-items-end">
            <div>
                <img src="{{ asset('images/logo.webp') }}" alt="Logo" style="height: 50px;" onerror="this.style.display='none'">
                <h1 class="fw-bold text-dark mt-2" style="font-size: 20pt; margin-bottom: 0;">Operator Performance Report</h1>
            </div>
            <div class="text-end text-dark" style="font-size: 9pt;">
                <p class="mb-0 fw-bold" style="font-size: 11pt;">CHIBOBRAND CO. LTD.</p>
                <p class="mb-0">Period: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</p>
                <p class="mb-0">Generated: {{ now()->format('M d, Y H:i') }}</p>
                <p class="mb-0 italic">Professional Operations Audit</p>
            </div>
        </div>
        <hr class="border-dark opacity-100 my-2">
    </div>

    <!-- Header -->
    <div class="row mb-3 no-print">
        <div class="col-12">
            <div class="d-flex flex-row justify-content-between align-items-start mb-2 gap-2">
                <div class="flex-grow-1">
                    <h2 class="mb-0 fw-bold">Operator Performance</h2>
                    <p class="text-muted mb-0 small">Operational and production analytics</p>
                </div>
                <div class="d-flex gap-1">
                    <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" title="Toggle Filters">
                        <i class="fas fa-filter"></i>
                    </button>
                    <button class="btn btn-dark btn-sm shadow-sm px-3" onclick="window.print()" title="Print Performance Report">
                        <i class="fas fa-print me-1"></i> PRINT REPORT
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="collapse show mb-4 filter-section" id="filterCollapse">
        <div class="card border-0 shadow-sm border-top border-4 border-info">
            <div class="card-body bg-light p-3">
                <form action="{{ route('admin.reports.operators') }}" method="GET" class="row g-2">
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">From</label>
                        <input type="date" class="form-control form-control-sm" name="date_from" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">To</label>
                        <input type="date" class="form-control form-control-sm" name="date_to" value="{{ $dateTo }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Operator</label>
                        <select class="form-select form-select-sm" name="operator_id">
                            <option value="">All Operators</option>
                            @foreach($operators as $op)
                                <option value="{{ $op->id }}" {{ $operatorId == $op->id ? 'selected' : '' }}>
                                    {{ $op->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end mt-2 mt-md-0">
                        <button type="submit" data-no-global-handler class="btn btn-info text-white btn-sm w-100 fw-bold">
                            <i class="fas fa-sync-alt me-1"></i> UPDATE
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Metrics -->
    <div class="row g-2 mb-4">
        <div class="col-6 col-lg-3 stats-col">
            <div class="card border-0 shadow-sm h-100 card-metric">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2">
                            <i class="fas fa-tasks fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Total Managed</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-dark">{{ number_format($summary['total_managed']) }}</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3 stats-col">
            <div class="card border-0 shadow-sm h-100 card-metric">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-warning bg-opacity-10 text-warning me-2">
                            <i class="fas fa-print fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">In Printing</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-dark">{{ number_format($summary['printing']) }}</div>
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
                        <span class="text-uppercase x-small fw-bold text-muted">Jobs Printed</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-dark">{{ number_format($summary['printed']) }}</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3 stats-col">
            <div class="card border-0 shadow-sm h-100 card-metric">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-info bg-opacity-10 text-info me-2">
                            <i class="fas fa-wallet fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Revenue Managed</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-dark">{{ number_format($summary['revenue_managed']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="m-0 fw-bold text-primary"><i class="fas fa-user-shield me-2"></i>Operator Efficiency Rankings</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3 border-0 x-small">Operator</th>
                            <th class="text-center border-0 x-small">Total Handling</th>
                            <th class="text-center border-0 x-small">Printed</th>
                            <th class="text-center border-0 x-small">Efficiency</th>
                            <th class="text-center border-0 x-small">Rating</th>
                            <th class="text-end pe-3 border-0 x-small">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasksByOperator as $item)
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center py-1">
                                    <div class="avatar-circle bg-info text-white me-2">
                                        {{ substr($item['operator']->name ?? '?', 0, 1) }}
                                    </div>
                                    <span class="small fw-bold">{{ $item['operator']->name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="text-center small">{{ $item['total'] }}</td>
                            <td class="text-center small">{{ $item['printed'] }}</td>
                            <td class="text-center">
                                <span class="fw-bold {{ $item['efficiency'] >= 80 ? 'text-success' : ($item['efficiency'] >= 50 ? 'text-warning' : 'text-danger') }} small">
                                    {{ number_format($item['efficiency'], 1) }}%
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="star-rating text-nowrap">
                                    @php $stars = floor($item['efficiency'] / 20); @endphp
                                    @for($i=0; $i<5; $i++)
                                        <i class="{{ $i < $stars ? 'fas' : 'far' }} fa-star"></i>
                                    @endfor
                                </div>
                            </td>
                            <td class="text-end pe-3 small fw-bold">{{ number_format($item['revenue']) }}</td>
                        </tr>
                        @endforeach
                        @if($tasksByOperator->isEmpty())
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No operator activity found for this period.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-line me-2"></i>Production Trend</h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="operatorTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-pie me-2"></i>Status Distribution</h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="operatorStatusChart"></canvas>
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
    // Trend Data
    const trendDataRaw = @json($tasksByDate);
    const trendLabels = trendDataRaw.map(d => d.date);
    const totalTrend = trendDataRaw.map(d => d.total);
    const printedTrend = trendDataRaw.map(d => d.printed);

    new Chart(document.getElementById('operatorTrendChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                label: 'Total Jobs',
                data: totalTrend,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                fill: true,
                tension: 0.3
            }, {
                label: 'Printed',
                data: printedTrend,
                borderColor: '#1cc88a',
                borderWidth: 2,
                tension: 0.3
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { boxWidth: 10, font: { size: 10 } } } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                y: { ticks: { font: { size: 9 }, stepSize: 1 } }
            }
        }
    });

    // Pie Data
    const statusData = {
        'Printing': {{ $summary['printing'] }},
        'Printed': {{ $summary['printed'] }},
        'Other Managed': {{ max(0, $summary['total_managed'] - $summary['printing'] - $summary['printed']) }}
    };
    
    const ctx = document.getElementById('operatorStatusChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: ['#f6c23e', '#1cc88a', '#4e73df'],
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } }
            },
            cutout: '70%'
        }
    });
</script>
@endpush
@endsection
