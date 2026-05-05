@extends('layouts.admin')

@section('title', 'Gatekeeper Dashboard - CHIBO BRAND')

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-1">Hello! {{ auth()->user()->name }}</h2>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <button class="btn btn-outline-primary btn-sm rounded-pill px-3 x-small" type="button"
                        data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                        <i class="fas fa-filter me-1"></i> Filter
                        @if(request()->anyFilled(['period', 'start_date', 'end_date']))
                            <span class="badge bg-primary ms-1">Active</span>
                        @endif
                    </button>
                    <div class="avatar-circle shadow-sm"
                        style="height: 40px; width: 40px; background: #6f42c1; font-size: 0.9rem;">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Collapsable Filters -->
    <div class="collapse {{ request()->anyFilled(['period', 'start_date', 'end_date']) ? 'show' : '' }} mb-4"
        id="filterCollapse">
        <div class="card border-0 shadow-sm border-top border-4 border-primary">
            <div class="card-body bg-light p-3">
                <form action="{{ route('admin.dashboard') }}" method="GET" class="row g-2 align-items-end"
                    data-no-global-handler>
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Time Period</label>
                        <select name="period" id="periodSelect" class="form-select form-select-sm">
                            <option value="today" {{ ($period ?? '') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="yesterday" {{ ($period ?? '') == 'yesterday' ? 'selected' : '' }}>Yesterday
                            </option>
                            <option value="week" {{ ($period ?? '') == 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ ($period ?? '') == 'month' || !isset($period) ? 'selected' : '' }}>This
                                Month</option>
                            <option value="6_months" {{ ($period ?? '') == '6_months' ? 'selected' : '' }}>Last 6 Months
                            </option>
                            <option value="year" {{ ($period ?? '') == 'year' ? 'selected' : '' }}>This Year</option>
                            <option value="2_years" {{ ($period ?? '') == '2_years' ? 'selected' : '' }}>Last 2 Years</option>
                            <option value="custom" {{ ($period ?? '') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            <option value="all" {{ ($period ?? '') == 'all' ? 'selected' : '' }}>All Time</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-2 custom-date-group {{ ($period ?? '') == 'custom' ? '' : 'd-none' }}">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">From Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm"
                            value="{{ request('start_date') }}">
                    </div>

                    <div class="col-6 col-md-2 custom-date-group {{ ($period ?? '') == 'custom' ? '' : 'd-none' }}">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">To Date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm"
                            value="{{ request('end_date') }}">
                    </div>

                    <div class="col-12 col-md-auto ms-auto">
                        <div class="btn-group shadow-sm w-100">
                            <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">APPLY</button>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-dark btn-sm px-4 fw-bold">RESET</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Order Verification Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-qrcode me-2"></i>Verify Outgoing Order
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row justify-content-center py-4">
                        <div class="col-md-6 col-lg-5 text-center">
                            <div class="mb-4">
                                <i class="fas fa-search-location text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                            </div>
                            <h5 class="mb-3">Scan or Enter Order Code</h5>
                            <form action="{{ route('admin.orders.index') }}" method="GET" class="d-flex gap-2">
                                <input type="text" name="search" class="form-control form-control-lg text-uppercase"
                                    placeholder="e.g. ORD-2024-001" autofocus>
                                <button type="submit" class="btn btn-primary btn-lg px-4">
                                    <i class="fas fa-search me-2"></i>Verify
                                </button>
                            </form>
                            <p class="text-muted small mt-2 mb-0">
                                Enter the order code to verify payment status and items before allowing exit.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Pending Verification -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-clipboard-list me-2 text-warning"></i>Recent Paid Orders (Ready for Exit)
                    </h6>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">View All Orders</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3">Order Code</th>
                                    <th class="py-3">Customer</th>
                                    <th class="py-3">Items</th>
                                    <th class="py-3">Status</th>
                                    <th class="py-3 text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Placeholder for orders -->
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-box-open mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                                        <p class="mb-0">No recent paid orders to display.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Period Selection Handling
                const periodSelect = document.getElementById('periodSelect');
                const customDateGroups = document.querySelectorAll('.custom-date-group');

                if (periodSelect) {
                    periodSelect.addEventListener('change', function () {
                        if (this.value === 'custom') {
                            customDateGroups.forEach(el => el.classList.remove('d-none'));
                        } else {
                            customDateGroups.forEach(el => el.classList.add('d-none'));
                        }
                    });
                }
            });
        </script>
    @endpush

    @push('styles')
        <style>
            .avatar-circle {
                height: 40px;
                width: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: bold;
            }

            .x-small {
                font-size: 0.75rem;
            }
        </style>
    @endpush
@endsection