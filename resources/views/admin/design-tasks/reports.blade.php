@extends('layouts.admin')

@section('title', 'Design Task Reports')

@push('styles')
<style>
    /* Mobile Responsive - Font Size Reductions (Matching Task Index) */
    @media screen and (max-width: 768px) {
        .container-fluid {
            padding: 0.5rem;
        }
        
        h2 {
            font-size: 1.25rem !important;
        }
        
        .text-muted {
            font-size: 0.75rem !important;
        }
        
        .card-header h6 {
            font-size: 0.8rem !important;
        }
        
        .badge {
            font-size: 0.65rem !important;
            padding: 0.3rem 0.5rem !important;
        }
        
        .btn {
            font-size: 0.75rem !important;
            padding: 0.375rem 0.625rem !important;
        }
        
        .btn-sm {
            font-size: 0.7rem !important;
            padding: 0.25rem 0.5rem !important;
        }
        
        .table th, .table td {
            font-size: 0.75rem !important;
            padding: 0.375rem 0.5rem !important;
        }
        
        strong {
            font-size: 0.85rem !important;
        }
        
        small {
            font-size: 0.65rem !important;
        }

        /* Analytics Card Side-by-Side on small screens */
        .stats-col {
            flex: 0 0 50% !important;
            max-width: 50% !important;
        }
    }
    
    @media screen and (max-width: 575.98px) {
        .container-fluid {
            padding: 0.25rem;
        }
        
        h2 {
            font-size: 1.1rem !important;
        }
        
        .text-muted {
            font-size: 0.7rem !important;
        }
        
        .card-header {
            padding: 0.375rem 0.5rem !important;
        }
        
        .card-header h6 {
            font-size: 0.75rem !important;
        }
        
        .card-body {
            padding: 0.75rem !important;
        }
        
        .badge {
            font-size: 0.6rem !important;
            padding: 0.25rem 0.4rem !important;
        }
        
        .btn {
            font-size: 0.7rem !important;
            padding: 0.3rem 0.5rem !important;
        }
        
        .btn-sm {
            font-size: 0.65rem !important;
            padding: 0.2rem 0.4rem !important;
        }
        
        .table th, .table td {
            font-size: 0.7rem !important;
            padding: 0.3rem 0.4rem !important;
        }
        
        strong {
            font-size: 0.8rem !important;
        }
        
        small {
            font-size: 0.6rem !important;
        }
    }

    .icon-circle {
        height: 40px; width: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
    }

    .card-metric .h2, .card-metric .h3 { font-size: 1.4rem; margin-bottom: 2px; }

    /* Stat card — same design as design task index */
    .dash-stat-card {
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
        text-decoration: none;
        color: inherit;
    }
    .dash-stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); color: inherit; }
    .dsc-icon {
        width: 30px; height: 30px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.85rem; flex-shrink: 0;
    }
    .dsc-trend { font-size: 10px; font-weight: 500; color: #94a3b8; white-space: nowrap; }
    .dsc-value { font-size: 1.05rem; font-weight: 700; line-height: 1.25; margin-top: 4px; }
    .dsc-label { font-size: 11px; font-weight: 600; color: #64748b; margin-top: 1px; }

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
        .btn, .sidebar, .sidebar-nav, .filter-section, .top-navbar, .mobile-menu-toggle, .btn-group, .card-header .btn, .breadcrumb, footer { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; overflow: visible !important; }
        .container-fluid { width: 100% !important; padding: 0 !important; max-width: 100% !important; }
        .card { border: 1px solid #dee2e6 !important; box-shadow: none !important; margin-bottom: 4px !important; break-inside: avoid; }
        .card-body { padding: 0.25rem !important; }
        .card-metric .h2 { font-size: 1rem !important; }
        .icon-circle { width: 20px !important; height: 20px !important; }
        .icon-circle i { font-size: 0.6rem !important; }
        
        /* Table Visibility & Contrast */
        .table { width: 100% !important; border-collapse: collapse !important; font-size: 9.5pt !important; color: #000 !important; }
        .table th, .table td { border: 1px solid #dee2e6 !important; padding: 4px 6px !important; }
        .table thead th { background-color: #eee !important; font-weight: 800 !important; -webkit-print-color-adjust: exact; color-adjust: exact; text-transform: uppercase; }
        
        .stats-col { flex: 0 0 25% !important; max-width: 25% !important; }
        
        /* Force charts to show and fit */
        canvas { max-width: 100% !important; height: auto !important; max-height: 180px !important; }
        .chart-area { height: 180px !important; }
        
        .no-print { display: none !important; }
        .print-only { display: block !important; }
        .report-header { margin-bottom: 10px; border-bottom: 2px solid #dee2e6; padding-bottom: 5px; }
        .report-header h1 { font-size: 18pt !important; margin: 0; }
        
        /* Recurring Footer on Every Page */
        .print-footer {
            position: fixed;
            bottom: 0px;
            left: 0;
            right: 0;
            background: white !important;
            padding: 8px 0;
            border-top: 1px solid #dee2e6 !important;
            text-align: center;
            font-size: 8pt !important;
            color: #000 !important;
        }
        body { padding-bottom: 50px !important; }
        
        /* Layout adjustments for A4 - Horizontal flow */
        .row { display: flex !important; flex-wrap: wrap !important; margin-left: -2px !important; margin-right: -2px !important; }
        .row > [class*="col-"] { padding: 0 2px !important; }
        
        .metrics-row .col-6 { flex: 0 0 16.666667% !important; max-width: 16.666667% !important; }
        .charts-row .col-lg-7 { flex: 0 0 60% !important; max-width: 60% !important; }
        .charts-row .col-lg-5 { flex: 0 0 40% !important; max-width: 40% !important; }
        
        .detailed-list-section { display: block !important; width: 100% !important; margin-top: 10px !important; }
    }
    .print-only { display: none; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Print Header -->
    <div class="print-only report-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                @include('partials.logo-print', ['logoStyle' => 'height:50px;object-fit:contain;'])
                <h1 class="fw-bold text-dark mt-2">Design Performance Report</h1>
                <p class="mb-0 text-dark">Period: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</p>
            </div>
            <div class="text-end text-dark">
                <p class="mb-1 fw-bold">CHIBOBRAND CO. LTD.</p>
                <p class="mb-1">Generated: {{ now()->format('M d, Y H:i') }}</p>
                <p class="mb-0">Format: A4 Professional Analytics</p>
            </div>
        </div>
    </div>

    <!-- Header (Matching Task Index Style) -->
    <div class="row mb-3 no-print">
        <div class="col-12">
            <div class="d-flex flex-row justify-content-between align-items-center mb-2 gap-2">
                <h2 class="mb-0 fw-bold">Design Task Analytics</h2>
                <x-report-export-menu
                    :print-url="route('admin.design-tasks.reports.print', request()->all())"
                    :pdf-url="route('admin.design-tasks.reports.pdf', request()->all())"
                    :excel-url="route('admin.design-tasks.reports.excel', request()->all())"
                />
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-4 filter-section">
        <div class="card border-0 shadow-sm border-top border-4 border-primary">
            <div class="card-body bg-light p-3">
                <form action="{{ route('admin.design-tasks.reports') }}" method="GET" class="row g-2">
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">From</label>
                        <input type="date" class="form-control form-control-sm" name="date_from" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">To</label>
                        <input type="date" class="form-control form-control-sm" name="date_to" value="{{ $dateTo }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Designer</label>
                        <select class="form-select form-select-sm" name="designer_id">
                            <option value="">All Designers</option>
                            @foreach($designers as $designer)
                                <option value="{{ $designer->id }}" {{ request('designer_id') == $designer->id ? 'selected' : '' }}>{{ $designer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Grouping</label>
                        <select class="form-select form-select-sm" name="period">
                            <option value="hour" {{ $period == 'hour' ? 'selected' : '' }}>Hourly</option>
                            <option value="day" {{ $period == 'day' ? 'selected' : '' }}>Daily</option>
                            <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Weekly</option>
                            <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Monthly</option>
                            <option value="half_month" {{ $period == 'half_month' ? 'selected' : '' }}>15-Day</option>
                            <option value="quarter" {{ $period == 'quarter' ? 'selected' : '' }}>Quarterly</option>
                            <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Yearly</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Status</label>
                        <select class="form-select form-select-sm" name="status">
                            <option value="">All</option>
                            @foreach(\App\Models\DesignTask::getStatusOptions() as $key => $label)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end mt-2 mt-md-0">
                        <button type="submit" data-no-global-handler class="btn btn-primary btn-sm w-100 fw-bold">
                            <i class="fas fa-sync-alt me-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Metrics -->
    <div class="row g-2 mb-3 metrics-row">
        <!-- Total Tasks -->
        <div class="col-6 col-md-2 stats-col">
            <div class="dash-stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="dsc-icon bg-primary-subtle text-primary"><i class="fas fa-tasks"></i></div>
                    <span class="dsc-trend">All</span>
                </div>
                <div class="dsc-value text-primary">{{ number_format($totalTasks) }}</div>
                <div class="dsc-label">Total Tasks</div>
            </div>
        </div>
        <!-- Completed -->
        <div class="col-6 col-md-2 stats-col">
            <div class="dash-stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="dsc-icon bg-success-subtle text-success"><i class="fas fa-check-double"></i></div>
                    <span class="dsc-trend">Done</span>
                </div>
                <div class="dsc-value text-success">{{ number_format($completedTasks) }}</div>
                <div class="dsc-label">Completed · {{ $totalTasks > 0 ? round(($completedTasks/$totalTasks)*100,1) : 0 }}%</div>
            </div>
        </div>
        <!-- Printing Jobs -->
        <div class="col-6 col-md-2 stats-col">
            <div class="dash-stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="dsc-icon text-white" style="background:#f97316;"><i class="fas fa-print"></i></div>
                    <span class="dsc-trend">Press</span>
                </div>
                <div class="dsc-value" style="color:#f97316;">{{ number_format($printingTasks) }}</div>
                <div class="dsc-label">Printing Jobs</div>
            </div>
        </div>
        <!-- Overdue -->
        <div class="col-6 col-md-2 stats-col">
            <div class="dash-stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="dsc-icon bg-danger-subtle text-danger"><i class="fas fa-exclamation-triangle"></i></div>
                    <span class="dsc-trend">Late</span>
                </div>
                <div class="dsc-value text-danger">{{ number_format($overdueTasks) }}</div>
                <div class="dsc-label">Overdue Tasks</div>
            </div>
        </div>
        <!-- Revisions -->
        <div class="col-6 col-md-2 stats-col">
            <div class="dash-stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="dsc-icon bg-danger-subtle text-danger"><i class="fas fa-undo-alt"></i></div>
                    <span class="dsc-trend">Fixes</span>
                </div>
                <div class="dsc-value text-danger">{{ number_format($revisionCount) }}</div>
                <div class="dsc-label">Revisions</div>
            </div>
        </div>
        <!-- Avg Time -->
        <div class="col-6 col-md-2 stats-col">
            <div class="dash-stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="dsc-icon bg-warning-subtle text-warning"><i class="fas fa-stopwatch"></i></div>
                    <span class="dsc-trend">Speed</span>
                </div>
                <div class="dsc-value text-warning">{{ number_format($avgCompletionHours, 1) }}h</div>
                <div class="dsc-label">Avg Completion</div>
            </div>
        </div>
        <!-- Total Revenue -->
        <div class="col-6 col-md-2 stats-col">
            <div class="dash-stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="dsc-icon bg-info-subtle text-info"><i class="fas fa-wallet"></i></div>
                    <span class="dsc-trend">Revenue</span>
                </div>
                <div class="dsc-value text-info">{{ number_format($totalRevenue) }}</div>
                <div class="dsc-label">Total Revenue</div>
            </div>
        </div>
        <!-- Amount Collected -->
        <div class="col-6 col-md-2 stats-col">
            <div class="dash-stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="dsc-icon bg-primary-subtle text-primary"><i class="fas fa-receipt"></i></div>
                    <span class="dsc-trend">Paid</span>
                </div>
                <div class="dsc-value text-primary">{{ number_format($totalPaid) }}</div>
                <div class="dsc-label">Collected</div>
            </div>
        </div>
        <!-- Balance Due -->
        <div class="col-6 col-md-2 stats-col">
            <div class="dash-stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="dsc-icon bg-warning-subtle text-warning"><i class="fas fa-hourglass-half"></i></div>
                    <span class="dsc-trend">Debt</span>
                </div>
                <div class="dsc-value text-warning">{{ number_format($totalBalance) }}</div>
                <div class="dsc-label">Balance Due</div>
            </div>
        </div>
        <!-- Priority cards -->
        @php
            $priorityCards = [
                'High'   => ['icon'=>'fas fa-flag','bg'=>'bg-danger-subtle','text'=>'text-danger','sub'=>'Urgent'],
                'Medium' => ['icon'=>'fas fa-flag','bg'=>'bg-warning-subtle','text'=>'text-warning','sub'=>'Normal'],
                'Low'    => ['icon'=>'fas fa-flag','bg'=>'bg-success-subtle','text'=>'text-success','sub'=>'Light'],
            ];
        @endphp
        @foreach($priorityCards as $label => $pc)
        <div class="col-6 col-md-2 stats-col">
            <div class="dash-stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="dsc-icon {{ $pc['bg'] }} {{ $pc['text'] }}"><i class="{{ $pc['icon'] }}"></i></div>
                    <span class="dsc-trend">{{ $pc['sub'] }}</span>
                </div>
                <div class="dsc-value {{ $pc['text'] }}">{{ number_format($priorityDistribution[$label] ?? 0) }}</div>
                <div class="dsc-label">{{ $label }} Priority</div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4 charts-row">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-area me-2"></i>Engagement Trend</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 280px;">
                        <canvas id="tasksAreaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-pie me-2"></i>Current Status</h6>
                </div>
                <div class="card-body">
                    <div style="height: 220px;">
                        <canvas id="statusPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Multi-Role Performance Row -->
    <div class="row g-3 mb-4">
        <!-- Designers -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary">Top Designers</h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary x-small">By Volume</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th class="ps-3 border-0 x-small">Design Staff</th>
                                    <th class="text-center border-0 x-small">Tasks</th>
                                    <th class="text-end pe-3 border-0 x-small">Revenue</th>
                                    <th class="text-center border-0 x-small no-print">Print</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topDesigners as $designer)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center py-1">
                                            <div class="avatar-circle bg-primary text-white me-2">
                                                {{ substr($designer->designer->name ?? '?', 0, 1) }}
                                            </div>
                                            <span class="small fw-bold">{{ $designer->designer->name ?? 'Unknown' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center small">{{ $designer->task_count }}</td>
                                    <td class="text-end pe-3 small text-success fw-bold">{{ number_format($designer->total_revenue) }}</td>
                                    <td class="text-center no-print">
                                        @php
                                            $printParams = array_merge(request()->except('designer_id'), ['designer_id' => $designer->designer_id]);
                                        @endphp
                                        <a href="{{ route('admin.design-tasks.reports.print', $printParams) }}"
                                           target="_blank"
                                           class="btn btn-outline-primary btn-xs p-1" title="Print {{ $designer->designer->name ?? 'Designer' }} Report">
                                            <i class="fas fa-print" style="font-size:0.7rem;"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Receptionists -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-info">Top Receptionists</h6>
                    <span class="badge bg-info bg-opacity-10 text-info x-small">Lead Gen</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th class="ps-3 border-0 x-small">User</th>
                                    <th class="text-center border-0 x-small">Tasks Managed</th>
                                    <th class="text-center border-0 x-small">Impact</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topReceptionists as $rec)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center py-1">
                                            <div class="avatar-circle bg-info text-white me-2">
                                                {{ substr($rec->receptionist->name ?? '?', 0, 1) }}
                                            </div>
                                            <span class="small fw-bold">{{ $rec->receptionist->name ?? 'Unknown' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center small">{{ $rec->task_count }}</td>
                                    <td class="text-center">
                                        <div class="progress" style="height: 4px; width: 60px; margin: 0 auto;">
                                            <div class="progress-bar bg-info" style="width: {{ $totalTasks > 0 ? ($rec->task_count / $totalTasks) * 100 : 0 }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Role Row -->
    <div class="row g-3 mb-4">
         <!-- Salers -->
         <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-success">Top Salers</h6>
                    <span class="badge bg-success bg-opacity-10 text-success x-small">Sales Boost</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th class="ps-3 border-0 x-small">Saler</th>
                                    <th class="text-center border-0 x-small">Design Conversion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topSalers as $saler)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center py-1">
                                            <div class="avatar-circle bg-success text-white me-2">
                                                {{ substr($saler->saler->name ?? '?', 0, 1) }}
                                            </div>
                                            <span class="small fw-bold">{{ $saler->saler->name ?? 'Unknown' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center small">{{ $saler->task_count }} jobs</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operators -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark">Top Operators</h6>
                    <span class="badge bg-dark bg-opacity-10 text-dark x-small">Production</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th class="ps-3 border-0 x-small">Operator</th>
                                    <th class="text-center border-0 x-small">Total Handling</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topOperators as $op)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center py-1">
                                            <div class="avatar-circle bg-dark text-white me-2">
                                                {{ substr($op->operator->name ?? '?', 0, 1) }}
                                            </div>
                                            <span class="small fw-bold">{{ $op->operator->name ?? 'Unknown' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center small">{{ $op->task_count }} jobs</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    </div>

    <!-- Detailed List Section (Advanced Printing) -->
    <div class="row g-3 mb-4 detailed-list-section">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-list me-2"></i>Recent Design Tasks</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th class="small ps-3">Date</th>
                                    <th class="small">Code</th>
                                    <th class="small">Title</th>
                                    <th class="small">Customer</th>
                                    <th class="small">Designer</th>
                                    <th class="small text-center">Status</th>
                                    <th class="text-end pe-3">Price</th>
                                    <th class="text-center no-print">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTasks as $task)
                                <tr>
                                    <td class="ps-3 small">{{ $task->created_at->format('M d, Y') }}</td>
                                    <td class="small fw-bold">{{ $task->task_code }}</td>
                                    <td class="small">{{ Str::limit($task->title, 30) }}</td>
                                    <td class="small">{{ $task->customer->name ?? 'N/A' }}</td>
                                    <td class="small">{{ $task->designer->name ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-opacity-10 
                                            {{ $task->status == 'completed' || $task->status == 'super_completed' ? 'bg-success text-success' : 'bg-warning text-dark' }}" style="font-size: 0.65rem;">
                                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3 small fw-bold">{{ number_format($task->price) }}</td>
                                    <td class="text-center no-print">
                                        <a href="{{ route('admin.design-tasks.show', $task) }}" class="btn btn-primary btn-xs p-1" title="View Task">
                                            <i class="fas fa-eye shadow-sm" style="font-size: 0.75rem;"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No design tasks found for this period.</td>
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
        <p class="mb-0">&copy; {{ date('Y') }} CHIBO BRANDS CO. LTD. All rights reserved.</p>
        <p class="mb-0">Developed by <a href="https://fridoltech.org" style="color: #000; text-decoration: none; font-weight: bold;">Fridoltech</a></p>
    </div>
</div>

@push('scripts')
<script>
    const chartDataRaw = @json($chartData);
    const periods = chartDataRaw.map(item => item.period);
    const counts = chartDataRaw.map(item => item.count);
    const revenues = chartDataRaw.map(item => item.revenue);

    const statusDataRaw = @json($statusDistribution);
    const statusLabels = Object.keys(statusDataRaw);
    const statusValues = Object.values(statusDataRaw);

    // Area Chart
    const ctxArea = document.getElementById("tasksAreaChart").getContext('2d');
    new Chart(ctxArea, {
        type: 'line',
        data: {
            labels: periods,
            datasets: [{
                label: "Task Count",
                lineTension: 0.3,
                backgroundColor: "rgba(78, 115, 223, 0.05)",
                borderColor: "rgba(78, 115, 223, 1)",
                pointRadius: 2,
                data: counts,
                yAxisID: 'y'
            }, {
                label: "Revenue",
                type: 'bar',
                backgroundColor: "rgba(28, 200, 138, 0.4)",
                data: revenues,
                yAxisID: 'y1',
                barThickness: 8
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { type: 'linear', position: 'left', ticks: { font: { size: 10 } } },
                y1: { type: 'linear', position: 'right', grid: { display: false }, ticks: { font: { size: 9 }, callback: v => v.toLocaleString() } }
            },
            plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 10 } } } }
        }
    });

    // Doughnut Chart
    const ctxPie = document.getElementById("statusPieChart").getContext('2d');
    const colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69', '#fd7e14', '#20c997'];
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: statusLabels.map(s => s.replace('_', ' ').toUpperCase()),
            datasets: [{
                data: statusValues,
                backgroundColor: colors.slice(0, statusValues.length),
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 } } }
            },
            cutout: '70%'
        }
    });

    // Professional Print Title
    window.onbeforeprint = () => { document.title = "Designer_Performance_Report_{{ now()->format('Ymd') }}"; };
    window.onafterprint = () => { document.title = "@yield('title')"; };

</script>
@endpush
@endsection
