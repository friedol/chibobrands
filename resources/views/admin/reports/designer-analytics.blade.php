@extends('layouts.admin')

@section('title', 'Designer Analytics - ' . $designer->name)

@section('content')
<div class="container-fluid">
    <!-- Print Only Header -->
    <div class="print-only report-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                @include('partials.logo-print')
                <h1 class="fw-bold text-dark mt-2">Individual Performance Report</h1>
                <p class="mb-0 text-dark">Staff: {{ $designer->name }} ({{ ucfirst($designer->role) }})</p>
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
    <nav aria-label="breadcrumb" class="mb-3 no-print">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.reports') }}" class="text-decoration-none">
                    <i class="fas fa-chart-bar me-1"></i>Reports
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.reports.design-tasks') }}" class="text-decoration-none">
                    <i class="fas fa-paint-brush me-1"></i>Designer Performance
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                {{ $designer->name }} Analytics
            </li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="row align-items-center mb-4 no-print">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    @if($designer->profile_image)
                        <img src="{{ asset('storage/' . $designer->profile_image) }}" alt="{{ $designer->name }}" class="rounded-circle shadow-sm" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #fff;">
                    @else
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 80px; height: 80px; border: 3px solid #fff;">
                            <span class="text-white fs-2 fw-bold">{{ strtoupper(substr($designer->name, 0, 1)) }}</span>
                        </div>
                    @endif
                </div>
                <div class="ms-4">
                    <h2 class="mb-1 fw-bold">{{ $designer->name }}</h2>
                    <p class="text-muted mb-0">
                        <span class="badge bg-soft-primary text-primary me-2">{{ ucfirst($designer->role) }}</span>
                        <i class="fas fa-envelope me-1"></i>{{ $designer->email }}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            @php
                $dPdfUrl   = route('admin.reports.design-tasks.export', ['type' => 'pdf',   'designer_id' => $designer->id, 'date_from' => $dateFrom, 'date_to' => $dateTo]);
                $dExcelUrl = route('admin.reports.design-tasks.export', ['type' => 'excel', 'designer_id' => $designer->id, 'date_from' => $dateFrom, 'date_to' => $dateTo]);
            @endphp
            <div class="me-2 d-inline-block">
                <x-report-export-menu
                    :print-js="true"
                    :pdf-url="$dPdfUrl"
                    :excel-url="$dExcelUrl"
                    label="Export"
                />
            </div>
            <form method="GET" action="{{ route('admin.reports.designer-analytics', $designer->id) }}" class="d-inline-flex" data-no-preloader data-no-global-handler>
                <div class="input-group input-group-sm">
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    <button type="submit" class="btn btn-primary" data-no-global-handler>
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mb-4 g-4 summary-row">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 text-center">
                    <div class="stats-icon bg-primary text-white rounded-circle mx-auto mb-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h6 class="text-muted mb-2">Total Tasks</h6>
                    <h3 class="mb-0 fw-bold">{{ $summary['total_tasks'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 text-center">
                    <div class="stats-icon bg-success text-white rounded-circle mx-auto mb-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <h6 class="text-muted mb-2">Completion Rate</h6>
                    @php
                        $rate = $summary['total_tasks'] > 0 ? round(($summary['completed'] / $summary['total_tasks']) * 100, 1) : 0;
                    @endphp
                    <h3 class="mb-0 fw-bold">{{ $rate }}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 text-center">
                    <div class="stats-icon bg-info text-white rounded-circle mx-auto mb-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <h6 class="text-muted mb-2">Active Tasks</h6>
                    <h3 class="mb-0 fw-bold">{{ $summary['in_progress'] + $summary['in_review'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 text-center">
                    <div class="stats-icon bg-warning text-white rounded-circle mx-auto mb-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h6 class="text-muted mb-2">Awaiting Action</h6>
                    <h3 class="mb-0 fw-bold">{{ $summary['pending'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4 charts-row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Task Completion Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Status Distribution</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="statusDistributionChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tasks Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Task History ({{ count($tasks) }})</h5>
            <a href="{{ route('admin.design-tasks.index', ['designer_id' => $designer->id]) }}" class="btn btn-sm btn-soft-primary no-print">
                View All Tasks
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Task Info</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold small">{{ $task->title }}</div>
                                    <small class="text-muted">{{ $task->task_code }}</small>
                                </td>
                                <td class="small">{{ $task->customer->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-soft-primary text-primary rounded-pill px-3 py-1" style="font-size: 0.7rem;">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                                </td>
                                <td class="small">{{ $task->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <p class="text-muted mt-3">No tasks found for this period</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Print Only Footer (Repeats on every page) -->
    <div class="print-only print-footer">
        <p class="mb-0">&copy; {{ date('Y') }} CHIBOBRAND CO. LTD. All rights reserved.</p>
        <p class="mb-0">Developed by <a href="https://fridoltech.org" style="color: #000; text-decoration: none; font-weight: bold;">Fridoltech</a></p>
    </div>
</div>

@push('styles')
<style>
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
    .text-primary { color: #0d6efd !important; }
    .text-info { color: #0dcaf0 !important; }
    .btn-soft-primary { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; border: none; }
    .btn-soft-primary:hover { background-color: #0d6efd; color: #fff; }
    .btn-icon { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
    .rounded-pill { border-radius: 50rem !important; }

    /* High-Density Print Layout */
    @media print {
        @page { size: A4; margin: 8mm; }
        body { background: white !important; font-size: 10pt !important; margin: 0 !important; padding: 0 !important; color: #000 !important; }
        .btn, .sidebar, .sidebar-nav, .filter-section, .top-navbar, .mobile-menu-toggle, .btn-group, #filterCollapse, .card-header .btn, .breadcrumb, footer, .btn-group.shadow-sm, form.d-inline-flex, .no-print { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; overflow: visible !important; }
        .container-fluid { width: 100% !important; padding: 0 !important; max-width: 100% !important; }
        .card { border: 1px solid #000 !important; box-shadow: none !important; margin-bottom: 8px !important; break-inside: avoid; }
        .card-header { border-bottom: 1px solid #000 !important; }
        .card-body { padding: 0.5rem !important; }
        
        /* Table Visibility & Contrast */
        .table { width: 100% !important; border-collapse: collapse !important; font-size: 9.5pt !important; color: #000 !important; }
        .table th, .table td { border: 1px solid #000 !important; padding: 4px 6px !important; }
        .table thead th { background-color: #eee !important; font-weight: 800 !important; -webkit-print-color-adjust: exact; color-adjust: exact; text-transform: uppercase; }
        
        /* Force charts to show and fit */
        canvas { max-width: 100% !important; height: auto !important; max-height: 180px !important; }
        
        .print-only { display: block !important; }
        .report-header { margin-bottom: 15px; border-bottom: 2px solid #000; padding-bottom: 5px; }
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
        
        .summary-row .col-md-3 { flex: 0 0 25% !important; max-width: 25% !important; }
        .charts-row .col-lg-8 { flex: 0 0 65% !important; max-width: 65% !important; }
        .charts-row .col-lg-4 { flex: 0 0 35% !important; max-width: 35% !important; }
    }
    .print-only { display: none; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Performance Trend Chart
    const performanceCtx = document.getElementById('performanceChart').getContext('2d');
    new Chart(performanceCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($tasksByDate, 'date')) !!}.map(date => {
                const d = new Date(date);
                return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            }),
            datasets: [{
                label: 'Total Tasks',
                data: {!! json_encode(array_column($tasksByDate, 'total')) !!},
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Completed Tasks',
                data: {!! json_encode(array_column($tasksByDate, 'completed')) !!},
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusDistributionChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'In Progress', 'In Review', 'Completed', 'Rejected'],
            datasets: [{
                data: [
                    {{ $summary['pending'] }},
                    {{ $summary['in_progress'] }},
                    {{ $summary['in_review'] }},
                    {{ $summary['completed'] }},
                    {{ $summary['rejected'] }}
                ],
                backgroundColor: ['#ffc107', '#0dcaf0', '#0d6efd', '#198754', '#dc3545'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            cutout: '70%'
        }
    });

    // Professional Print Title
    window.onbeforeprint = () => { document.title = "{{ str_replace(' ', '_', $designer->name) }}_Performance_Report_{{ now()->format('Ymd') }}"; };
    window.onafterprint = () => { document.title = "@yield('title')"; };
});
</script>
@endpush
@endsection
