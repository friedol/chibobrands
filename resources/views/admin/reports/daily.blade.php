@extends('layouts.admin')

@section('title', 'Daily Reports - CHIBO BRAND')

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
                <i class="fas fa-calendar-day me-1"></i>Daily Reports
            </li>
        </ol>
    </nav>

    <!-- Modern Collapsable Filters -->
    <div class="d-flex justify-content-end mb-3 no-print">
        <button class="btn btn-primary btn-sm px-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
            <i class="fas fa-filter me-1"></i> Filter
            @if(request()->anyFilled(['period', 'date_from', 'date_to', 'export_format']))
                <span class="badge bg-white text-primary ms-1">Active</span>
            @endif
        </button>
    </div>

    <div class="collapse {{ request()->anyFilled(['period', 'date_from', 'date_to', 'export_format']) ? 'show' : '' }} mb-4 no-print" id="filterCollapse">
        <div class="card border-0 shadow-sm border-top border-4 border-primary">
            <div class="card-body bg-light p-3">
                <form method="GET" action="{{ route('admin.reports.daily') }}" class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Time Period</label>
                        <select name="period" id="periodSelect" class="form-select form-select-sm">
                            <option value="today" {{ ($period ?? '') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="yesterday" {{ ($period ?? '') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                            <option value="week" {{ ($period ?? '') == 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ ($period ?? '') == 'month' || !isset($period) ? 'selected' : '' }}>This Month</option>
                            <option value="year" {{ ($period ?? '') == 'year' ? 'selected' : '' }}>This Year</option>
                            <option value="custom" {{ ($period ?? '') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            <option value="all" {{ ($period ?? '') == 'all' ? 'selected' : '' }}>All Time</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-2 custom-date-group {{ ($period ?? '') == 'custom' ? '' : 'd-none' }}">
                        <label for="date_from" class="form-label fw-bold x-small text-uppercase mb-1">From Date</label>
                        <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                    </div>

                    <div class="col-6 col-md-2 custom-date-group {{ ($period ?? '') == 'custom' ? '' : 'd-none' }}">
                        <label for="date_to" class="form-label fw-bold x-small text-uppercase mb-1">To Date</label>
                        <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                    </div>

                    <div class="col-md-3">
                        <label for="export_format" class="form-label fw-bold x-small text-uppercase mb-1">Export Format</label>
                        <select name="export_format" id="export_format" class="form-select form-select-sm">
                            <option value="">Select Format</option>
                            <option value="pdf" {{ request('export_format') == 'pdf' ? 'selected' : '' }}>PDF</option>
                            <option value="excel" {{ request('export_format') == 'excel' ? 'selected' : '' }}>Excel</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-auto ms-auto">
                        <div class="btn-group shadow-sm w-100">
                            <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">APPLY</button>
                            <a href="{{ route('admin.reports.daily') }}" class="btn btn-dark btn-sm px-4 fw-bold">RESET</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-icon primary">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stats-number">{{ $summary['total_orders'] ?? 0 }}</div>
                <div class="stats-label">Total Orders</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stats-number">{{ $summary['approved_orders'] ?? 0 }}</div>
                <div class="stats-label">Approved Orders</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-icon danger">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stats-number">{{ $summary['cancelled_orders'] ?? 0 }}</div>
                <div class="stats-label">Cancelled Orders</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-icon warning">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stats-number">TZS {{ number_format($summary['total_revenue'] ?? 0, 0) }}</div>
                <div class="stats-label">Total Revenue</div>
            </div>
        </div>
    </div>

    <!-- Daily Report Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0">
                <i class="fas fa-table me-2"></i>Daily Order Summary
            </h6>
            <div class="d-flex gap-2">
                <button class="btn btn-success btn-sm" onclick="exportToPDF()">
                    <i class="fas fa-file-pdf me-1"></i>Export PDF
                </button>
                <button class="btn btn-primary btn-sm" onclick="exportToExcel()">
                    <i class="fas fa-file-excel me-1"></i>Export Excel
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="dailyReportTable" style="border-radius: 0;">
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
                        @forelse($dailyReports as $report)
                            <tr>
                                <td class="fw-bold">{{ \Carbon\Carbon::parse($report->date)->format('M j, Y') }}</td>
                                <td>
                                    <span class="badge bg-primary fs-6">{{ $report->total_requests }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-success fs-6">{{ $report->approved }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-danger fs-6">{{ $report->cancelled }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning fs-6">{{ $report->pending }}</span>
                                </td>
                                <td class="fw-bold text-success">TZS {{ number_format($report->revenue, 0) }}</td>
                                <td>
                                    <button class="btn btn-info btn-sm" onclick="viewDayDetails('{{ $report->date }}')">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-chart-line fa-4x text-muted mb-4"></i>
                                    <h5 class="text-muted">No data found</h5>
                                    <p class="text-muted">Try adjusting your date range or check back later.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Order Trends
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="orderTrendsChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Day Details Modal -->
<div class="modal fade" id="dayDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details for <span id="modalDate"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="dayDetailsContent">
                    <div class="text-center">
                        <div class="loading"></div>
                        <p>Loading details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Period Selection Handling
    const periodSelect = document.getElementById('periodSelect');
    const customDateGroups = document.querySelectorAll('.custom-date-group');
    
    if (periodSelect) {
        function toggleCustomDates() {
            if (periodSelect.value === 'custom') {
                customDateGroups.forEach(el => el.classList.remove('d-none'));
            } else {
                customDateGroups.forEach(el => el.classList.add('d-none'));
            }
        }

        // Run on init
        toggleCustomDates();

        // Run on change
        periodSelect.addEventListener('change', toggleCustomDates);
    }

    // Initialize chart
    const ctx = document.getElementById('orderTrendsChart').getContext('2d');
    const chartData = @json($chartData ?? []);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels || [],
            datasets: [{
                label: 'Total Orders',
                data: chartData.total || [],
                borderColor: '#FF0000',
                backgroundColor: 'rgba(255, 0, 0, 0.1)',
                tension: 0.4
            }, {
                label: 'Approved Orders',
                data: chartData.approved || [],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4
            }, {
                label: 'Revenue (TZS)',
                data: chartData.revenue || [],
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Orders'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Revenue (TZS)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Daily Order Trends'
                }
            }
        }
    });

    // Animate stats cards
    const statsCards = document.querySelectorAll('.stats-card');
    statsCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});

function viewDayDetails(date) {
    document.getElementById('modalDate').textContent = new Date(date).toLocaleDateString();
    document.getElementById('dayDetailsContent').innerHTML = `
        <div class="text-center">
            <div class="loading"></div>
            <p>Loading details...</p>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('dayDetailsModal'));
    modal.show();
    
    // Fetch day details via AJAX
    fetch(`/admin/reports/daily-details?date=${date}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('dayDetailsContent').innerHTML = data.html;
        })
        .catch(error => {
            document.getElementById('dayDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error loading details. Please try again.
                </div>
            `;
        });
}

function exportToPDF() {
    const dateFrom = document.getElementById('date_from').value;
    const dateTo = document.getElementById('date_to').value;
    window.open(`/admin/reports/export-pdf?date_from=${dateFrom}&date_to=${dateTo}`, '_blank');
}

function exportToExcel() {
    const dateFrom = document.getElementById('date_from').value;
    const dateTo = document.getElementById('date_to').value;
    window.open(`/admin/reports/export-excel?date_from=${dateFrom}&date_to=${dateTo}`, '_blank');
}
</script>

<style>
/* Remove table header rounded corners */
.table thead th {
    border-radius: 0 !important;
}

.table thead th:first-child {
    border-top-left-radius: 0 !important;
}

.table thead th:last-child {
    border-top-right-radius: 0 !important;
}

.table {
    border-radius: 0 !important;
}

.table thead {
    border-radius: 0 !important;
}

/* Breadcrumb styles */
.breadcrumb {
    background: transparent;
    padding: 0.5rem 0;
    margin-bottom: 0;
    font-size: 0.875rem;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: #6c757d;
    font-size: 1.2rem;
    font-weight: 600;
    margin: 0 0.5rem;
}

.breadcrumb-item a {
    color: #6c757d;
    text-decoration: none;
    transition: color 0.2s ease;
}

.breadcrumb-item a:hover {
    color: #0d6efd;
}

.breadcrumb-item.active {
    color: #495057;
    font-weight: 500;
}

.breadcrumb-item i {
    font-size: 0.75rem;
}

/* Reduce font sizes */
.card-header h6 {
    font-size: 0.875rem;
}

.form-label {
    font-size: 0.8125rem;
}

.form-control, .form-select {
    font-size: 0.8125rem;
}

.btn {
    font-size: 0.8125rem;
}

.btn-sm {
    font-size: 0.75rem;
}

.table th {
    font-size: 0.75rem;
}

.table td {
    font-size: 0.75rem;
}

.badge {
    font-size: 0.6875rem;
}

.stats-card .stats-number {
    font-size: 1.5rem;
}

.stats-card .stats-label {
    font-size: 0.75rem;
}

.modal-title {
    font-size: 1rem;
}

.modal-body {
    font-size: 0.8125rem;
}
</style>
@endsection