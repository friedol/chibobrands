@extends('layouts.admin')

@section('title', 'Monthly Reports - CHIBO BRAND')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
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
            <li class="breadcrumb-item active" aria-current="page">
                <i class="fas fa-calendar-alt me-1"></i>Monthly Reports
            </li>
        </ol>
    </nav>

    <!-- Month/Year Filter -->
    <div class="card mb-4">
        <div class="card-header py-2">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Report Filters
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.monthly') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="year" class="form-label fw-bold">Year</label>
                        <select name="year" id="year" class="form-select">
                            @for($y = now()->year; $y >= now()->year - 5; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="month" class="form-label fw-bold">Month</label>
                        <select name="month" id="month" class="form-select">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.reports.monthly') }}" class="btn btn-secondary">
                            <i class="fas fa-redo me-1"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-shopping-cart text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Requests</h6>
                            <h3 class="mb-0 fw-bold">{{ $summary['total_requests'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-check-circle text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Approved</h6>
                            <h3 class="mb-0 fw-bold text-success">{{ $summary['total_approved'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-times-circle text-danger fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Cancelled</h6>
                            <h3 class="mb-0 fw-bold text-danger">{{ $summary['total_cancelled'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-dollar-sign text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Revenue</h6>
                            <h3 class="mb-0 fw-bold text-success">TZS {{ number_format($summary['total_revenue'] ?? 0, 0) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Report Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">
                <i class="fas fa-table me-2"></i>Monthly Report - {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="monthlyReportTable" style="border-radius: 0;">
                    <thead style="border-radius: 0;">
                        <tr>
                            <th>Date</th>
                            <th>Total Requests</th>
                            <th>Approved</th>
                            <th>Cancelled</th>
                            <th>Pending</th>
                            <th>Revenue</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report as $dayReport)
                            <tr>
                                <td class="fw-bold">{{ \Carbon\Carbon::parse($dayReport->date)->format('M j, Y') }}</td>
                                <td>
                                    <span class="badge bg-primary fs-6">{{ $dayReport->total }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-success fs-6">{{ $dayReport->approved }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-danger fs-6">{{ $dayReport->cancelled }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning fs-6">{{ $dayReport->pending }}</span>
                                </td>
                                <td class="fw-bold text-success">TZS {{ number_format($dayReport->approved_revenue ?? 0, 0) }}</td>
                                <td>
                                    <a href="{{ route('admin.orders.index', ['date' => $dayReport->date]) }}" class="btn btn-info btn-sm" title="View Day Details">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">No data available for {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($report->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th class="fw-bold">Total</th>
                            <th><span class="badge bg-primary fs-6">{{ $summary['total_requests'] ?? 0 }}</span></th>
                            <th><span class="badge bg-success fs-6">{{ $summary['total_approved'] ?? 0 }}</span></th>
                            <th><span class="badge bg-danger fs-6">{{ $summary['total_cancelled'] ?? 0 }}</span></th>
                            <th><span class="badge bg-warning fs-6">{{ $summary['total_pending'] ?? 0 }}</span></th>
                            <th class="fw-bold text-success">TZS {{ number_format($summary['total_revenue'] ?? 0, 0) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

@endsection






