@extends('layouts.admin')

@section('title', 'Designer Performance Reports')

@push('styles')
<style>
    /* Premium SaaS Pro Theme */
    :root {
        --premium-primary: #4f46e5;
        --premium-success: #10b981;
        --premium-warning: #f59e0b;
        --premium-danger: #ef4444;
        --premium-info: #0ea5e9;
        --premium-dark: #1e293b;
        --premium-gray: #64748b;
    }

    .icon-circle {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .hover-lift {
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.1) !important;
    }

    .card-metric .h2 {
        font-family: 'Nunito Sans', sans-serif;
        letter-spacing: -0.5px;
        font-weight: 700;
        color: #1e293b;
    }

    /* Mobile Responsive Adjustments */
    @media (max-width: 768px) {
        .container-fluid { padding: 0.75rem; }
        .h2 { font-size: 1.25rem !important; }
        .card-body { padding: 1rem !important; }
        .icon-circle { width: 32px; height: 32px; font-size: 11px; }
        .x-small { font-size: 9px; }
        .stats-col { flex: 0 0 50% !important; max-width: 50% !important; }
    }

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

    /* Table Refinement */
    .table thead th {
        background-color: #f8fafc;
        color: var(--premium-gray);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-top: none;
        padding: 1rem 0.75rem;
    }

    .table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        font-size: 13px;
        color: #334155;
    }

    @media print {
        @page { size: A4; margin: 8mm; }
        body { background: white !important; font-size: 10pt !important; margin: 0 !important; padding: 0 !important; color: #000 !important; color-adjust: exact; -webkit-print-color-adjust: exact; }
        .btn, .sidebar, .sidebar-nav, .filter-section, .top-navbar, .no-print, .breadcrumb, footer { display: none !important; }
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
    }
    
    .print-only { display: none; }
</style>
@endpush

@section('content')
<div class="container-fluid no-print-padding">
    <div class="print-only report-header mb-4">
        <div class="d-flex justify-content-between align-items-end">
            <div>
                <img src="{{ asset('images/logo.webp') }}" alt="Logo" style="height: 50px;" onerror="this.style.display='none'">
                <h1 class="fw-bold text-dark mt-2" style="font-size: 20pt; margin-bottom: 0;">Design Activity & Performance Report</h1>
            </div>
            <div class="text-end text-dark" style="font-size: 9pt;">
                <p class="mb-0 fw-bold" style="font-size: 11pt;">CHIBOBRAND CO. LTD.</p>
                <p class="mb-0">Period: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</p>
                <p class="mb-0">Generated: {{ now()->format('M d, Y H:i') }}</p>
                <p class="mb-0 italic">Design Operations Audit</p>
            </div>
        </div>
        <hr class="border-dark opacity-100 my-2">
    </div>

    <!-- Header -->
    <div class="row mb-4 no-print">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h1 class="h4 mb-1 text-dark fw-bold">Designer Performance</h1>
                    <p class="text-muted mb-0 small">Productivity and fulfillment analytics</p>
                </div>
                <div class="d-flex gap-2">
                    <div class="btn-group shadow-sm">
                        <a href="{{ route('admin.reports.design-tasks.export', ['type' => 'pdf', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-white btn-sm px-3" data-no-preloader title="Download PDF">
                            <i class="fas fa-file-pdf me-1 text-danger"></i> PDF
                        </a>
                        @php $designTasksPdfUrl = route('admin.reports.design-tasks.export', ['type' => 'pdf', 'date_from' => $dateFrom, 'date_to' => $dateTo]); @endphp
                        <a href="{{ $designTasksPdfUrl }}" target="_blank" rel="noopener" class="btn btn-success btn-sm px-3 share-pdf-btn" data-pdf-url="{{ $designTasksPdfUrl }}" data-pdf-filename="designer-performance-{{ $dateFrom }}-{{ $dateTo }}.pdf" title="Share PDF">
                            <i class="fas fa-share-alt me-1"></i> Share PDF
                        </a>
                        <button class="btn btn-dark btn-sm px-3" onclick="window.print()">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="collapse show mb-4 filter-section" id="filterCollapse">
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-3">
                <form action="{{ route('admin.reports.design-tasks') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-6 col-md-3">
                        <label class="text-uppercase x-small fw-bold text-muted mb-1 d-block">From Date</label>
                        <input type="date" class="form-control form-select-sm" name="date_from" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="text-uppercase x-small fw-bold text-muted mb-1 d-block">To Date</label>
                        <input type="date" class="form-control form-select-sm" name="date_to" value="{{ $dateTo }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="text-uppercase x-small fw-bold text-muted mb-1 d-block">Designer</label>
                        <select class="form-select form-select-sm" name="designer_id">
                            <option value="">All Designers</option>
                            @foreach($designers as $designer)
                                <option value="{{ $designer->id }}" {{ $designerId == $designer->id ? 'selected' : '' }}>
                                    {{ $designer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <button type="submit" data-no-global-handler class="btn btn-dark btn-sm w-100 fw-bold">
                            <i class="fas fa-sync-alt me-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Metrics Row -->
    <div class="row g-2 g-md-3 mb-4">
        <!-- Total Tasks -->
        <div class="col-6 col-lg-3 stats-col">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-primary hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Total</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-dark">{{ number_format($summary['total_tasks']) }}</div>
                </div>
            </div>
        </div>

        <!-- In Progress -->
        <div class="col-6 col-lg-3 stats-col">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-warning hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-warning bg-opacity-10 text-warning me-2">
                            <i class="fas fa-spinner"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Progress</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-dark">{{ number_format($summary['in_progress']) }}</div>
                </div>
            </div>
        </div>

        <!-- Completed -->
        <div class="col-6 col-lg-3 stats-col">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-success hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-success bg-opacity-10 text-success me-2">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Completed</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-dark">{{ number_format($summary['completed']) }}</div>
                </div>
            </div>
        </div>

        <!-- Rejected -->
        <div class="col-6 col-lg-3 stats-col">
            <div class="card shadow-sm h-100 border-0 border-start border-4 border-danger hover-lift">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-circle bg-danger bg-opacity-10 text-danger me-2">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Rejected</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-dark">{{ number_format($summary['rejected']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-line me-2"></i>Productivity Trend</h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="designerTrendChart"></canvas>
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
                        <canvas id="designerPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="m-0 fw-bold text-primary"><i class="fas fa-medal me-2"></i>Designer Productivity Rankings</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3 border-0 x-small">Designer</th>
                            <th class="text-center border-0 x-small">Total Tasks</th>
                            <th class="text-center border-0 x-small">Completed</th>
                            <th class="text-center border-0 x-small">Fulfillment</th>
                            <th class="text-end pe-3 border-0 x-small">Efficiency</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasksByDesigner as $item)
                        @php
                            $rate = $item['total'] > 0 ? round(($item['completed'] / $item['total']) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center py-1">
                                    <div class="avatar-circle bg-primary text-white me-2">
                                        {{ substr($item['designer']->name ?? 'D', 0, 1) }}
                                    </div>
                                    <span class="small fw-bold">{{ $item['designer']->name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="text-center small">{{ $item['total'] }}</td>
                            <td class="text-center small">{{ $item['completed'] }}</td>
                            <td class="text-center">
                                <div class="progress" style="height: 4px; width: 80px; margin: 0 auto;">
                                    <div class="progress-bar bg-{{ $rate > 80 ? 'success' : ($rate > 50 ? 'warning' : 'danger') }}" style="width: {{ $rate }}%"></div>
                                </div>
                            </td>
                            <td class="text-end pe-3 small fw-bold {{ $rate > 80 ? 'text-success' : 'text-dark' }}">{{ $rate }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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
    // Data from Backend
    const trendData = @json($tasksByDate);
    const labels = trendData.map(d => d.date);
    const completedCounts = trendData.map(d => d.completed);
    const totalCounts = trendData.map(d => d.total);

    // Productivity Chart
    new Chart(document.getElementById('designerTrendChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Tasks',
                data: totalCounts,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                fill: true,
                tension: 0.3,
                pointRadius: 2
            }, {
                label: 'Completed',
                data: completedCounts,
                borderColor: '#1cc88a',
                borderWidth: 2,
                tension: 0.3,
                pointRadius: 2
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

    // Pie Chart
    const statusSummary = {
        'Pending': {{ $summary['pending'] }},
        'In Progress': {{ $summary['in_progress'] }},
        'Review': {{ $summary['in_review'] }},
        'Completed': {{ $summary['completed'] }},
        'Rejected': {{ $summary['rejected'] }}
    };

    new Chart(document.getElementById('designerPieChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusSummary),
            datasets: [{
                data: Object.values(statusSummary),
                backgroundColor: ['#f6c23e', '#36b9cc', '#4e73df', '#1cc88a', '#e74a3b'],
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
</script>
@endpush
@endsection
