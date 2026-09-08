@extends('layouts.admin')

@section('title', 'Sales Reports')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-1">Sales Reports</h4>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <button class="btn btn-outline-dark btn-sm rounded-pill px-3 x-small" type="button"
                        data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                        <i class="fas fa-filter me-1"></i> Filter
                        @if(request()->anyFilled(['period', 'start_date', 'end_date', 'group_by']))
                            <span class="badge bg-primary ms-1">Active</span>
                        @endif
                    </button>
                    <button class="btn btn-outline-dark btn-sm rounded-pill px-3 x-small"
                        onclick="window.location.reload()">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                    <x-report-export-menu
                        :print-url="route('admin.sales-dept.reports.print', request()->all())"
                        :pdf-url="route('admin.sales-dept.reports.pdf', request()->all())"
                        :excel-url="route('admin.sales-dept.reports.excel', request()->all())"
                        print-target="_blank"
                    />
                </div>
            </div>
        </div>
    </div>

    <!-- Collapsable Filters -->
    <div class="collapse {{ request()->anyFilled(['period', 'start_date', 'end_date', 'group_by']) ? 'show' : '' }} mb-4"
        id="filterCollapse">
        <div class="card border-0 shadow-sm border-top border-4 border-primary">
            <div class="card-body bg-light p-3">
                <form action="{{ route('admin.sales-dept.reports') }}" method="GET" class="row g-2 align-items-end"
                    data-no-global-handler>
                    
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Group By</label>
                        <select name="group_by" class="form-select form-select-sm">
                            <option value="seller" {{ $groupBy == 'seller' ? 'selected' : '' }}>By Seller</option>
                            <option value="department" {{ $groupBy == 'department' ? 'selected' : '' }}>By Department</option>
                            <option value="product" {{ $groupBy == 'product' ? 'selected' : '' }}>By Product</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Time Period</label>
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
                        <label class="form-label fw-bold x-small text-uppercase mb-1">From Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm"
                            value="{{ request('start_date', $dateFrom) }}">
                    </div>

                    <div class="col-6 col-md-2 custom-date-group {{ ($period ?? '') == 'custom' ? '' : 'd-none' }}">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">To Date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm"
                            value="{{ request('end_date', $dateTo) }}">
                    </div>

                    <div class="col-12 col-md-auto ms-auto">
                        <div class="btn-group shadow-sm w-100">
                            <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">APPLY</button>
                            <a href="{{ route('admin.sales-dept.reports') }}" class="btn btn-dark btn-sm px-4 fw-bold">RESET</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const periodSelect = document.getElementById('periodSelect');
            const customDateGroups = document.querySelectorAll('.custom-date-group');

            if (periodSelect) {
                periodSelect.addEventListener('change', function() {
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

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0">Results Report (Grouped by {{ ucfirst($groupBy) }})</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    @if($groupBy === 'product')
                                        <th class="ps-4">Product Name</th>
                                        <th class="text-center">Total Quantity</th>
                                        <th class="text-end pe-4">Total Revenue</th>
                                    @elseif($groupBy === 'department')
                                        <th class="ps-4">Department Name</th>
                                        <th class="text-end pe-4">Total Revenue</th>
                                    @else
                                        <th class="ps-4">Seller Name</th>
                                        <th class="text-end pe-4">Total Revenue</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($results as $item)
                                <tr>
                                    @if($groupBy === 'product')
                                        <td class="ps-4 fw-bold">{{ $item->name }}</td>
                                        <td class="text-center">{{ number_format($item->total_qty) }}</td>
                                        <td class="text-end pe-4 fw-bold text-primary">TZS {{ number_format($item->total_revenue) }}</td>
                                    @elseif($groupBy === 'department')
                                        <td class="ps-4 fw-bold">{{ $item->name }}</td>
                                        <td class="text-end pe-4 fw-bold text-primary">TZS {{ number_format($item->design_tasks_sum_price ?? 0) }}</td>
                                    @else
                                        <td class="ps-4 fw-bold">{{ $item->name }}</td>
                                        <td class="text-end pe-4 fw-bold text-primary">TZS {{ number_format($item->design_tasks_sum_price ?? 0) }}</td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted">No data found matching your filters.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
