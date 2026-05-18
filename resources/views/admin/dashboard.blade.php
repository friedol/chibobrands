@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="container-fluid">
        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
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
                        <button class="btn btn-outline-dark btn-sm rounded-pill px-3 x-small"
                            onclick="window.location.reload()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
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
                                <option value="month" {{ ($period ?? '') == 'month' || !isset($period) ? 'selected' : '' }}>
                                    This Month</option>
                                <option value="6_months" {{ ($period ?? '') == '6_months' ? 'selected' : '' }}>Last 6 Months
                                </option>
                                <option value="year" {{ ($period ?? '') == 'year' ? 'selected' : '' }}>This Year</option>
                                <option value="2_years" {{ ($period ?? '') == '2_years' ? 'selected' : '' }}>Last 2 Years
                                </option>
                                <option value="custom" {{ ($period ?? '') == 'custom' ? 'selected' : '' }}>Custom Range
                                </option>
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

        <!-- 12 NAVIGATABLE STATS CARDS - 6 Per Row on Desktop -->
        <div class="row g-2 g-md-3 mb-4">
            <!-- 0. Total Sales -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.finance.reports') }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-info hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-info bg-opacity-10 text-info me-2">
                                    <i class="fas fa-tags"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Total Sales</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-info">{{ number_format($stats['total_billed'] ?? 0) }}</div>
                            <div class="x-small text-muted mt-2">Gross value sold</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 1. Total Revenue -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.finance.reports') }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-primary hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2">
                                    <i class="fas fa-arrow-trend-up"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Total Revenue</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-dark">{{ number_format($stats['total_revenue'] ?? 0) }}</div>
                            <div class="x-small text-muted mt-2">Collected + Outstanding</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 2. Total Collected -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.finance.cash-flow') }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-success hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-success bg-opacity-10 text-success me-2">
                                    <i class="fas fa-wallet"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Collected</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-success">{{ number_format($stats['total_collected'] ?? 0) }}
                            </div>
                            <div class="x-small text-muted mt-2">Cash in hand</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- Debt Collected -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.finance.cash-flow') }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 hover-lift"
                        style="border-left-color: #20c997 !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-opacity-10 me-2"
                                    style="background-color: rgba(32, 201, 151, 0.1); color: #20c997;">
                                    <i class="fas fa-hand-holding-dollar"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Debt Collected</span>
                            </div>
                            <div class="h3 mb-0 fw-bold" style="color: #20c997;">
                                {{ number_format($stats['total_debt_collected'] ?? 0) }}</div>
                            <div class="x-small text-muted mt-2">Past debts recovered</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 3. Total Expenses -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.finance.expenses') }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-danger hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-danger bg-opacity-10 text-danger me-2">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Expenses</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-danger">{{ number_format($stats['total_expenses'] ?? 0) }}
                            </div>
                            <div class="x-small text-muted mt-2">Spending</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 4. Balance Due -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.finance.audit') }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-warning hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-warning bg-opacity-10 text-warning me-2">
                                    <i class="fas fa-clock text-warning"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Balance Due</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-warning">{{ number_format($stats['total_balance_due'] ?? 0) }}
                            </div>
                            <div class="x-small text-muted mt-2">Outstanding</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 5. Total Orders -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.orders.index') }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-info hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-info bg-opacity-10 text-info me-2">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Total Orders</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-dark">{{ number_format($stats['total_orders'] ?? 0) }}</div>
                            <div class="x-small text-muted mt-2">In period</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 6. Pending Orders -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.orders.index', ['status' => 'requested']) }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-secondary hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-secondary bg-opacity-10 text-secondary me-2">
                                    <i class="fas fa-hourglass-half"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Pending Orders</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-dark">{{ number_format($periodStats['pending'] ?? 0) }}</div>
                            <div class="x-small text-muted mt-2">Awaiting approval</div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- ROW 2 -->
            <!-- 7. Active Tasks -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.design-tasks.index') }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-info hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-info bg-opacity-10 text-info me-2">
                                    <i class="fas fa-palette"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Active Tasks</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-dark">{{ $stats['in_progress'] ?? 0 }}</div>
                            <div class="x-small text-info mt-2">Work in progress</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 8. Overdue Tasks -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.design-tasks.index') }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-danger hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-danger bg-opacity-10 text-danger me-2">
                                    <i class="fas fa-fire"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Overdue Tasks</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-danger">{{ $stats['overdue_tasks'] ?? 0 }}</div>
                            <div class="x-small text-danger mt-2">Past deadline</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 9. Completed Tasks -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.design-tasks.index', ['status' => 'completed']) }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-success hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-success bg-opacity-10 text-success me-2">
                                    <i class="fas fa-check-double"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Completed</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-success">{{ $stats['completed_tasks'] ?? 0 }}</div>
                            <div class="x-small text-success mt-2">Ready for delivery</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 10. Total Customers -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.customers.index') }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-dark hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-dark bg-opacity-10 text-dark me-2">
                                    <i class="fas fa-users"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Customers</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-dark">{{ number_format($stats['total_customers'] ?? 0) }}</div>
                            <div class="x-small text-muted mt-2">Database count</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 11. Net Profit -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.finance.reports') }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 hover-lift"
                        style="border-left-color: #6610f2 !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-opacity-10 me-2"
                                    style="background-color: rgba(102, 16, 242, 0.1); color: #6610f2;">
                                    <i class="fas fa-scale-balanced"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Net Profit</span>
                            </div>
                            <div class="h3 mb-0 fw-bold" style="color: #6610f2;">
                                {{ number_format($stats['net_profit'] ?? 0) }}</div>
                            <div class="x-small text-muted mt-2">After expenses</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 12. Stock Alerts -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.enhanced-products.index', ['stock' => 'low']) }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-danger hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-danger bg-opacity-10 text-danger me-2">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Stock Alerts</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-danger">{{ $lowStockProducts->count() }}</div>
                            <div class="x-small text-danger mt-2">Low inventory</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- MAIN GRAPHS ROW -->
        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-chart-line me-2"></i>Profitability Trend</h6>
                        <div class="btn-group btn-group-sm shadow-sm" role="group">
                            <button type="button" class="btn btn-outline-dark active" id="btnLineChart"
                                onclick="toggleProfitChart('line')">
                                <i class="fas fa-chart-line"></i>
                            </button>
                            <button type="button" class="btn btn-outline-dark" id="btnBarChart"
                                onclick="toggleProfitChart('bar')">
                                <i class="fas fa-chart-bar"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="combinedRevenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-tasks me-2"></i>Design Task Distribution</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 250px;">
                            <canvas id="taskStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DAILY LEAD COUNT WIDGET -->
        @if(in_array(auth()->user()->role, ['admin','super_admin','saler','accountant']))
        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.leads.index') }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-primary hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">New Today</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-primary">{{ $leadStats['today_new'] }}</div>
                            <div class="x-small text-muted mt-1">Leads added today</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.leads.follow-up-center', ['follow_up_status' => 'today']) }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-warning hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-warning bg-opacity-10 text-warning me-2">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Follow-Ups Today</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-warning">{{ $leadStats['today_follow_ups'] }}</div>
                            <div class="x-small text-muted mt-1">Due for contact today</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.leads.index', ['status' => 'converted']) }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-success hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-success bg-opacity-10 text-success me-2">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Converted Today</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-success">{{ $leadStats['today_conversions'] }}</div>
                            <div class="x-small text-muted mt-1">Closed as converted</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.leads.overdue') }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-danger hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-danger bg-opacity-10 text-danger me-2">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Overdue</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-danger">{{ $leadStats['overdue_count'] }}</div>
                            <div class="x-small text-muted mt-1">Missed follow-up date</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.leads.index', ['status' => 'pending']) }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-secondary hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-secondary bg-opacity-10 text-secondary me-2">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Total Pending</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-dark">{{ $leadStats['total_pending'] }}</div>
                            <div class="x-small text-muted mt-1">Awaiting conversion</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.leads.follow-up-center') }}" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 hover-lift" style="border-color:#6f42c1!important">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle text-purple me-2" style="background:rgba(111,66,193,0.1);color:#6f42c1!important">
                                    <i class="fas fa-calendar-week"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">This Week</span>
                            </div>
                            <div class="h3 mb-0 fw-bold" style="color:#6f42c1">{{ $leadStats['week_new'] }}</div>
                            <div class="x-small text-muted mt-1">New leads this week</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        @endif

        <!-- NEW vs REPEATED CUSTOMER + OVERDUE + SELLER RANKING WIDGETS -->
        @if(in_array(auth()->user()->role, ['admin','super_admin','accountant']))
        <div class="row g-3 mb-4">
            {{-- New vs Repeated Customers --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-2 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark small"><i class="fas fa-users me-2 text-primary"></i>Customer Analytics</h6>
                        <a href="{{ route('admin.reports.sales') }}" class="btn btn-link btn-sm py-0 text-muted">View Report</a>
                    </div>
                    <div class="card-body py-2">
                        @php
                            $newCustWeek = \App\Models\Customer::newCustomers()->addedThisWeek()->count();
                            $repCustWeek = \App\Models\Customer::repeatedCustomers()->count();
                            $totalCust   = \App\Models\Customer::count();
                        @endphp
                        <div class="row g-2 text-center mb-3">
                            <div class="col-6">
                                <div class="rounded-3 bg-success bg-opacity-10 p-3">
                                    <div class="fs-3 fw-bold text-success">{{ $newCustWeek }}</div>
                                    <div class="x-small text-muted fw-semibold">New This Week</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="rounded-3 bg-info bg-opacity-10 p-3">
                                    <div class="fs-3 fw-bold text-info">{{ $repCustWeek }}</div>
                                    <div class="x-small text-muted fw-semibold">Repeated Customers</div>
                                </div>
                            </div>
                        </div>
                        @if($totalCust > 0)
                        <div class="mb-1 small fw-semibold d-flex justify-content-between">
                            <span>Retention Rate</span>
                            <span>{{ round(($repCustWeek / $totalCust) * 100, 1) }}%</span>
                        </div>
                        <div class="progress" style="height:8px">
                            <div class="progress-bar bg-info" style="width:{{ round(($repCustWeek / $totalCust) * 100, 1) }}%"></div>
                        </div>
                        <div class="x-small text-muted mt-1">{{ $totalCust }} total customers</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Overdue Follow-Ups Alert --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-2 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark small"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Follow-Up Alerts</h6>
                        <a href="{{ route('admin.leads.overdue') }}" class="btn btn-link btn-sm py-0 text-muted">View All</a>
                    </div>
                    <div class="card-body py-2">
                        @php
                            $overdueLeads  = \App\Models\Lead::overdue()->count();
                            $todayLeads    = \App\Models\Lead::dueToday()->count();
                            $upcomingLeads = \App\Models\Lead::upcoming()->count();
                        @endphp
                        <div class="list-group list-group-flush small">
                            <a href="{{ route('admin.leads.overdue', ['filter' => 'overdue']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0">
                                <span><i class="fas fa-circle text-danger me-2 small"></i>Overdue Follow-Ups</span>
                                <span class="badge bg-danger rounded-pill">{{ $overdueLeads }}</span>
                            </a>
                            <a href="{{ route('admin.leads.overdue', ['filter' => 'today']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0">
                                <span><i class="fas fa-circle text-warning me-2 small"></i>Due Today</span>
                                <span class="badge bg-warning text-dark rounded-pill">{{ $todayLeads }}</span>
                            </a>
                            <a href="{{ route('admin.leads.overdue', ['filter' => 'upcoming']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0">
                                <span><i class="fas fa-circle text-success me-2 small"></i>Upcoming (7 days)</span>
                                <span class="badge bg-success rounded-pill">{{ $upcomingLeads }}</span>
                            </a>
                        </div>
                        @if($overdueLeads > 0)
                        <div class="alert alert-danger alert-sm py-1 px-2 mt-2 mb-0 small">
                            <i class="fas fa-bell me-1"></i>
                            <strong>{{ $overdueLeads }}</strong> follow-up{{ $overdueLeads > 1 ? 's' : '' }} require immediate attention!
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Seller Rankings (current month) --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-2 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark small"><i class="fas fa-trophy me-2 text-warning"></i>Seller Rankings</h6>
                        <a href="{{ route('admin.reports.sales') }}" class="btn btn-link btn-sm py-0 text-muted">Full Report</a>
                    </div>
                    <div class="card-body py-2 px-3">
                        @php
                            $topSellers = \App\Models\User::whereIn('role', ['saler', 'admin', 'super_admin'])
                                ->get()
                                ->map(function($s) {
                                    $rev = \App\Models\Payment::activeFinance()
                                        ->where('seller_id', $s->id)
                                        ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
                                        ->sum('amount');
                                    return ['name' => $s->name, 'revenue' => (float) $rev];
                                })
                                ->filter(fn($s) => $s['revenue'] > 0)
                                ->sortByDesc('revenue')
                                ->take(5)
                                ->values();
                        @endphp
                        @if($topSellers->isEmpty())
                            <p class="text-muted small text-center py-3">No sales data this month.</p>
                        @else
                        <ol class="list-group list-group-flush small mb-0">
                            @foreach($topSellers as $rank => $seller)
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    @if($rank === 0) <i class="fas fa-trophy text-warning"></i>
                                    @elseif($rank === 1) <i class="fas fa-medal text-secondary"></i>
                                    @elseif($rank === 2) <i class="fas fa-medal text-danger" style="color:#cd7f32!important"></i>
                                    @else <span class="text-muted fw-bold" style="width:16px;display:inline-block">{{ $rank+1 }}</span>
                                    @endif
                                    <span class="fw-semibold">{{ Str::limit($seller['name'], 18) }}</span>
                                </div>
                                <span class="text-success fw-bold small">{{ number_format($seller['revenue']) }}</span>
                            </li>
                            @endforeach
                        </ol>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- RECENT ACTIVITY ROW 1: ORDERS & TOP PRODUCTS -->
        <div class="row g-3 mb-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-pen-nib me-2 text-primary"></i>Top Design
                            Performance</h6>
                        <a href="{{ route('admin.design-tasks.index') }}"
                            class="btn btn-sm btn-link text-primary p-0 text-decoration-none small">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($topDesigners ?? [] as $designer)
                                <div class="list-group-item border-0 py-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center fw-bold me-2"
                                                style="width: 32px; height: 32px; font-size: 12px;">
                                                {{ substr($designer['name'], 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="small fw-bold text-dark">{{ $designer['name'] }}</div>
                                                <div class="x-small text-muted">Completed: <span
                                                        class="fw-bold">{{ $designer['completed_tasks'] }}</span> tasks</div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="small fw-bold text-info">
                                                {{ number_format($designer['raw_achievement'], 1) }}%</div>
                                            <div class="x-small text-muted">of target</div>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 6px; border-radius: 3px;">
                                        <div class="progress-bar bg-info" role="progressbar"
                                            style="width: {{ $designer['achievement'] }}%"
                                            aria-valuenow="{{ $designer['achievement'] }}" aria-valuemin="0"
                                            aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-palette fa-2x mb-2 opacity-25"></i>
                                    <p class="small mb-0">No design performance data available</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-trophy me-2 text-warning"></i>Top Sales
                            Performance</h6>
                        <a href="{{ route('admin.saler-performance.index') }}"
                            class="btn btn-sm btn-link text-primary p-0 text-decoration-none small">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($topSalers ?? [] as $saler)
                                <div class="list-group-item border-0 py-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold me-2"
                                                style="width: 32px; height: 32px; font-size: 12px;">
                                                {{ substr($saler['name'], 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="small fw-bold text-dark">{{ $saler['name'] }}</div>
                                                <div class="x-small text-muted">Sales: TZS
                                                    {{ number_format($saler['total_sales']) }}</div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="small fw-bold text-primary">
                                                {{ number_format($saler['raw_achievement'], 1) }}%</div>
                                            <div class="x-small text-muted">of target</div>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 6px; border-radius: 3px;">
                                        <div class="progress-bar bg-primary" role="progressbar"
                                            style="width: {{ $saler['achievement'] }}%"
                                            aria-valuenow="{{ $saler['achievement'] }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fa-2x mb-2 opacity-25"></i>
                                    <p class="small mb-0">No sales performance data available</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RECENT ACTIVITY ROW 2: DESIGN TASKS & RECENT EXPENSES -->
        <div class="row g-3 mb-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-palette me-2"></i>Recent Design Tasks</h6>
                        <a href="{{ route('admin.design-tasks.index') }}"
                            class="btn btn-sm btn-link text-primary p-0 text-decoration-none">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted x-small text-uppercase">
                                    <tr>
                                        <th class="ps-3 py-2">ID</th>
                                        <th>Designer</th>
                                        <th>Status</th>
                                        <th class="text-center pe-3">Deadline</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentAssignedTasks ?? [] as $task)
                                        <tr onclick="window.location='{{ route('admin.design-tasks.show', $task->id) }}'"
                                            style="cursor: pointer;">
                                            <td class="ps-3 fw-bold">#{{ $task->id }}</td>
                                            <td class="small">{{ $task->designer->name ?? 'Unassigned' }}</td>
                                            <td>
                                                <span
                                                    class="badge rounded-pill bg-info text-dark x-small">{{ ucfirst($task->status) }}</span>
                                            </td>
                                            <td class="text-center pe-3 small">
                                                {{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('M d') : '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-3 text-muted">No recent tasks</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-danger"><i class="fas fa-receipt me-2"></i>Recent Expenses</h6>
                        <a href="{{ route('admin.finance.expenses') }}"
                            class="btn btn-sm btn-link text-danger p-0 text-decoration-none">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted x-small text-uppercase">
                                    <tr>
                                        <th class="ps-3 py-2">Category</th>
                                        <th class="text-end pe-3">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentExpenses ?? [] as $expense)
                                        <tr>
                                            <td class="ps-3 py-2">
                                                <div class="small fw-bold">{{ $expense->category }}</div>
                                                <div class="x-small text-muted text-truncate" style="max-width: 150px;">
                                                    {{ $expense->notes }}</div>
                                            </td>
                                            <td class="text-end pe-3 fw-bold text-danger small">TZS
                                                {{ number_format($expense->amount) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-4 text-muted">No recent expenses</td>
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

@push('scripts')
    <script>
        let profitChart;

        function toggleProfitChart(type) {
            const ctx = document.getElementById('combinedRevenueChart').getContext('2d');
            const labels = {!! json_encode($profit_chart_data['labels'] ?? []) !!};
            const revenueData = {!! json_encode($profit_chart_data['revenue'] ?? []) !!};
            const expenseData = {!! json_encode($profit_chart_data['expenses'] ?? []) !!};
            const profitData = {!! json_encode($profit_chart_data['current'] ?? []) !!};

            if (profitChart) {
                profitChart.destroy();
            }

            const isBar = type === 'bar';

            // Update UI buttons
            document.getElementById('btnLineChart').classList.toggle('active', type === 'line');
            document.getElementById('btnBarChart').classList.toggle('active', type === 'bar');

            profitChart = new Chart(ctx, {
                type: type,
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Revenue',
                            data: revenueData,
                            borderColor: '#28a745',
                            backgroundColor: isBar ? '#28a745' : 'rgba(40, 167, 69, 0.05)',
                            borderWidth: 2,
                            fill: !isBar,
                            tension: 0.4,
                            barPercentage: 0.6,
                            categoryPercentage: 0.5
                        },
                        {
                            label: 'Expenses',
                            data: expenseData,
                            borderColor: '#dc3545',
                            backgroundColor: isBar ? '#dc3545' : 'rgba(220, 53, 69, 0.05)',
                            borderWidth: 2,
                            fill: !isBar,
                            tension: 0.4,
                            barPercentage: 0.6,
                            categoryPercentage: 0.5
                        },
                        {
                            label: 'Net Profit',
                            data: profitData,
                            borderColor: '#6610f2',
                            backgroundColor: isBar ? '#6610f2' : 'rgba(102, 16, 242, 0.1)',
                            borderWidth: 3,
                            pointBackgroundColor: '#6610f2',
                            fill: !isBar,
                            tension: 0.4,
                            barPercentage: 0.6,
                            categoryPercentage: 0.5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || '';
                                    if (label) label += ': ';
                                    if (context.parsed.y !== null) label += 'TZS ' + context.parsed.y.toLocaleString();
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: v => 'TZS ' + v.toLocaleString(),
                                font: { size: 10 }
                            },
                            grid: { borderDash: [5, 5] }
                        },
                        x: {
                            ticks: { font: { size: 10 } },
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Initial load
            toggleProfitChart('line');

            // Task Status Distribution Chart
            const taskStatusCtx = document.getElementById('taskStatusChart').getContext('2d');
            new Chart(taskStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'In Progress', 'In Review', 'Printing', 'Completed', 'Rejected'],
                    datasets: [{
                        data: [
                            {{ $designTaskStatus['pending'] ?? 0 }},
                            {{ $designTaskStatus['in_progress'] ?? 0 }},
                            {{ $designTaskStatus['in_review'] ?? 0 }},
                            {{ $designTaskStatus['printing'] ?? 0 }},
                            {{ ($designTaskStatus['completed'] ?? 0) + ($designTaskStatus['super_completed'] ?? 0) }},
                            {{ $designTaskStatus['rejected'] ?? 0 }}
                        ],
                        backgroundColor: ['#ffc107', '#007bff', '#6c757d', '#17a2b8', '#28a745', '#dc3545'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } },
                    cutout: '75%'
                }
            });

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

    <style>
        .icon-circle {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 0.8rem;
        }

        .x-small {
            font-size: 10px;
        }

        .hover-lift {
            transition: transform 0.2s ease-in-out;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
        }

        @media (max-width: 768px) {
            .h3 {
                font-size: 1.1rem !important;
            }

            .card-body {
                padding: 0.75rem !important;
            }

            .icon-circle {
                width: 28px;
                height: 28px;
                font-size: 11px;
            }

            .x-small {
                font-size: 9px;
            }
        }
    </style>
@endpush