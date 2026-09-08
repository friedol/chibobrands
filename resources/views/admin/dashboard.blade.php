@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@push('styles')
<style>
    .section-label {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        color: #94a3b8;
        text-transform: uppercase;
        margin-bottom: 0.6rem;
        padding-left: 2px;
    }
    /* Compact Stat Card Styles */
    .cust-stat-card {
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
    }
    .cust-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .cust-stat-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
    }
    .cust-stat-val {
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.25;
        margin-top: 4px;
    }
    .cust-stat-lbl {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        margin-top: 1px;
    }
    .cust-stat-sub {
        font-size: 10px;
        font-weight: 500;
        color: #94a3b8;
    }
</style>
@endpush

@section('content')
    <div class="container-fluid">

        {{-- ── HEADER ────────────────────────────────────────────────── --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <h2 class="fw-bold mb-0" style="font-size: clamp(1.1rem, 4vw, 1.5rem);">Hello! {{ auth()->user()->name }}</h2>
                        <p class="text-muted x-small mb-0 d-none d-md-block">Business overview and reporting</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <form method="GET" action="{{ route('admin.dashboard') }}" id="periodForm"
                            class="d-flex flex-wrap align-items-center justify-content-end gap-2" data-no-global-handler>
                            <select name="period" id="periodSelect"
                                class="form-select form-select-sm rounded-3 px-3 border-0 shadow-sm fw-bold x-small"
                                style="background-color: #f8f9fa; cursor: pointer; min-width: 120px;">
                                <option value="today" {{ ($period ?? '') == 'today' || !isset($period) ? 'selected' : '' }}>Today</option>
                                <option value="yesterday" {{ ($period ?? '') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                                <option value="week" {{ ($period ?? '') == 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ ($period ?? '') == 'month' ? 'selected' : '' }}>This Month</option>
                                <option value="6_months" {{ ($period ?? '') == '6_months' ? 'selected' : '' }}>Last 6 Months</option>
                                <option value="year" {{ ($period ?? '') == 'year' ? 'selected' : '' }}>This Year</option>
                                <option value="2_years" {{ ($period ?? '') == '2_years' ? 'selected' : '' }}>Last 2 Years</option>
                                <option value="custom" {{ ($period ?? '') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                                <option value="all" {{ ($period ?? '') == 'all' ? 'selected' : '' }}>All Time</option>
                            </select>
                            <div id="customDateRange"
                                class="d-flex align-items-center gap-1 {{ ($period ?? '') == 'custom' ? '' : 'd-none' }}">
                                <input type="date" name="start_date"
                                    class="form-control form-control-sm rounded-3 border-0 shadow-sm x-small"
                                    value="{{ request('start_date') }}" style="width: 100px;">
                                <input type="date" name="end_date"
                                    class="form-control form-control-sm rounded-3 border-0 shadow-sm x-small"
                                    value="{{ request('end_date') }}" style="width: 100px;">
                                <button type="submit" class="btn btn-primary btn-sm rounded-3 px-2 x-small"><i class="fas fa-check"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── FINANCIAL OVERVIEW ────────────────────────────────────── --}}
        <div class="section-label"><i class="fas fa-coins me-1"></i>Financial Overview</div>
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.finance.reports') }}" class="text-decoration-none">
                    <div class="cust-stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="cust-stat-icon bg-info-subtle text-info"><i class="fas fa-tags"></i></div>
                            <span class="cust-stat-sub">Stable</span>
                        </div>
                        <div class="cust-stat-val text-dark">{{ number_format($stats['total_billed'] ?? 0) }}</div>
                        <div class="cust-stat-lbl">Total Sales</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.finance.reports') }}" class="text-decoration-none">
                    <div class="cust-stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="cust-stat-icon bg-primary-subtle text-primary"><i class="fas fa-dollar-sign"></i></div>
                            <span class="cust-stat-sub">Stable</span>
                        </div>
                        <div class="cust-stat-val text-primary" style="font-size:1.02rem;">TZS {{ number_format($stats['total_revenue'] ?? 0) }}</div>
                        <div class="cust-stat-lbl">Total Revenue</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.finance.cash-flow') }}" class="text-decoration-none">
                    <div class="cust-stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="cust-stat-icon bg-success-subtle text-success"><i class="fas fa-wallet"></i></div>
                            <span class="cust-stat-sub">Stable</span>
                        </div>
                        <div class="cust-stat-val text-success" style="font-size:1.02rem;">TZS {{ number_format($stats['total_collected'] ?? 0) }}</div>
                        <div class="cust-stat-lbl">Collected</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.finance.expenses') }}" class="text-decoration-none">
                    <div class="cust-stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="cust-stat-icon bg-danger-subtle text-danger"><i class="fas fa-receipt"></i></div>
                            <span class="cust-stat-sub">Stable</span>
                        </div>
                        <div class="cust-stat-val text-danger" style="font-size:1.02rem;">TZS {{ number_format($stats['total_expenses'] ?? 0) }}</div>
                        <div class="cust-stat-lbl">Total Expenses</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.finance.audit') }}" class="text-decoration-none">
                    <div class="cust-stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="cust-stat-icon bg-warning-subtle text-warning"><i class="fas fa-clock"></i></div>
                            <span class="cust-stat-sub">Stable</span>
                        </div>
                        <div class="cust-stat-val text-warning" style="font-size:1.02rem;">TZS {{ number_format($stats['total_balance_due'] ?? 0) }}</div>
                        <div class="cust-stat-lbl">Balance Due</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.finance.reports') }}" class="text-decoration-none">
                    <div class="cust-stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="cust-stat-icon" style="background:#f3e8ff;color:#7c3aed;"><i class="fas fa-scale-balanced"></i></div>
                            <span class="cust-stat-sub">Stable</span>
                        </div>
                        <div class="cust-stat-val" style="font-size:1.02rem;color:#7c3aed;">TZS {{ number_format($stats['net_profit'] ?? 0) }}</div>
                        <div class="cust-stat-lbl">Net Profit</div>
                    </div>
                </a>
            </div>
        </div>

        {{-- ── OPERATIONS OVERVIEW ───────────────────────────────────── --}}
        <div class="section-label mt-3"><i class="fas fa-cogs me-1"></i>Operations</div>
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.orders.index') }}" class="text-decoration-none">
                    <div class="cust-stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="cust-stat-icon" style="background:#e0e7ff;color:#6366f1;"><i class="fas fa-shopping-cart"></i></div>
                            <span class="cust-stat-sub">Stable</span>
                        </div>
                        <div class="cust-stat-val" style="color:#6366f1;">{{ number_format($stats['total_orders'] ?? 0) }}</div>
                        <div class="cust-stat-lbl">Sales Orders</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.orders.index', ['status' => 'requested']) }}" class="text-decoration-none">
                    <div class="cust-stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="cust-stat-icon" style="background:#f1f5f9;color:#64748b;"><i class="fas fa-hourglass-half"></i></div>
                            <span class="cust-stat-sub">Stable</span>
                        </div>
                        <div class="cust-stat-val text-dark">{{ number_format($periodStats['pending'] ?? 0) }}</div>
                        <div class="cust-stat-lbl">Pending Orders</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.design-tasks.index') }}" class="text-decoration-none">
                    <div class="cust-stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="cust-stat-icon" style="background:#e0f2fe;color:#0284c7;"><i class="fas fa-palette"></i></div>
                            <span class="cust-stat-sub">Stable</span>
                        </div>
                        <div class="cust-stat-val" style="color:#0284c7;">{{ $stats['in_progress'] ?? 0 }}</div>
                        <div class="cust-stat-lbl">Active Tasks</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.design-tasks.index') }}" class="text-decoration-none">
                    <div class="cust-stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="cust-stat-icon bg-danger-subtle text-danger"><i class="fas fa-fire"></i></div>
                            <span class="cust-stat-sub">Urgent</span>
                        </div>
                        <div class="cust-stat-val text-danger">{{ $stats['overdue_tasks'] ?? 0 }}</div>
                        <div class="cust-stat-lbl">Overdue Tasks</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.design-tasks.index', ['status' => 'completed']) }}" class="text-decoration-none">
                    <div class="cust-stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="cust-stat-icon bg-success-subtle text-success"><i class="fas fa-check-double"></i></div>
                            <span class="cust-stat-sub">Stable</span>
                        </div>
                        <div class="cust-stat-val text-success">{{ $stats['completed_tasks'] ?? 0 }}</div>
                        <div class="cust-stat-lbl">Completed Tasks</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.customers.index') }}" class="text-decoration-none">
                    <div class="cust-stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="cust-stat-icon bg-primary-subtle text-primary"><i class="fas fa-users"></i></div>
                            <span class="cust-stat-sub">Stable</span>
                        </div>
                        <div class="cust-stat-val text-dark">{{ number_format($stats['total_customers'] ?? 0) }}</div>
                        <div class="cust-stat-lbl">Total Customers</div>
                    </div>
                </a>
            </div>
        </div>

        {{-- ── CHARTS ────────────────────────────────────────────────── --}}
        <div class="section-label"><i class="fas fa-chart-bar me-1"></i>Analytics</div>
        <div class="row g-3 mb-4">
            {{-- Revenue vs Expenses Bar Chart --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm chart-card h-100">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Revenue vs Expenses</h6>
                            <p class="x-small text-muted mb-0">{{ ucfirst(str_replace('_', ' ', $period ?? 'Today')) }}</p>
                        </div>
                        <div class="d-flex gap-1">
                            <button id="btnBar" onclick="setProfitChart('bar')"
                                class="btn btn-sm btn-primary chart-toggle-btn">
                                <i class="fas fa-chart-bar me-1"></i>Bar
                            </button>
                            <button id="btnLine" onclick="setProfitChart('line')"
                                class="btn btn-sm btn-outline-secondary chart-toggle-btn">
                                <i class="fas fa-chart-line me-1"></i>Line
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-0 pb-3">
                        <div style="height: 310px; position:relative;">
                            <canvas id="combinedRevenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Task Status Horizontal Bar --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm chart-card h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="fw-bold mb-0 text-dark">Task Pipeline</h6>
                        <p class="x-small text-muted mb-0">Design tasks by status</p>
                    </div>
                    <div class="card-body pt-0 pb-3">
                        <div style="height: 310px; position:relative;">
                            <canvas id="taskStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── LEAD PIPELINE ─────────────────────────────────────────── --}}
        @if(in_array(auth()->user()->role, ['admin','super_admin','saler','accountant']))
        <div class="section-label"><i class="fas fa-funnel-dollar me-1"></i>Lead Pipeline</div>
        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.leads.index') }}" class="text-decoration-none">
                    <div class="dash-stat-card hover-lift" style="border-color:rgba(13,110,253,0.3);background:linear-gradient(150deg,rgba(13,110,253,0.06) 0%,#fff 100%);">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="dsc-icon" style="background:rgba(13,110,253,0.13);color:#0d6efd;"><i class="fas fa-user-plus"></i></div>
                            <span class="dsc-trend">&#8212; Stable</span>
                        </div>
                        <div class="dsc-value">{{ $leadStats['today_new'] }}</div>
                        <div class="dsc-label">New Today</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.leads.index', ['follow_up_status' => 'today']) }}" class="text-decoration-none">
                    <div class="dash-stat-card hover-lift" style="border-color:rgba(245,158,11,0.3);background:linear-gradient(150deg,rgba(245,158,11,0.06) 0%,#fff 100%);">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="dsc-icon" style="background:rgba(245,158,11,0.13);color:#f59e0b;"><i class="fas fa-phone-alt"></i></div>
                            <span class="dsc-trend">&#8212; Stable</span>
                        </div>
                        <div class="dsc-value">{{ $leadStats['today_follow_ups'] }}</div>
                        <div class="dsc-label">Follow-Ups Today</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.leads.index', ['status' => 'converted']) }}" class="text-decoration-none">
                    <div class="dash-stat-card hover-lift" style="border-color:rgba(25,135,84,0.3);background:linear-gradient(150deg,rgba(25,135,84,0.06) 0%,#fff 100%);">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="dsc-icon" style="background:rgba(25,135,84,0.13);color:#198754;"><i class="fas fa-check-circle"></i></div>
                            <span class="dsc-trend">&#8212; Stable</span>
                        </div>
                        <div class="dsc-value">{{ $leadStats['today_conversions'] }}</div>
                        <div class="dsc-label">Converted Today</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.leads.overdue') }}" class="text-decoration-none">
                    <div class="dash-stat-card hover-lift" style="border-color:rgba(220,53,69,0.3);background:linear-gradient(150deg,rgba(220,53,69,0.06) 0%,#fff 100%);">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="dsc-icon" style="background:rgba(220,53,69,0.13);color:#dc3545;"><i class="fas fa-exclamation-triangle"></i></div>
                            <span class="dsc-trend">&#8212; Stable</span>
                        </div>
                        <div class="dsc-value">{{ $leadStats['overdue_count'] }}</div>
                        <div class="dsc-label">Overdue Leads</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.leads.index', ['status' => 'pending']) }}" class="text-decoration-none">
                    <div class="dash-stat-card hover-lift" style="border-color:rgba(100,116,139,0.3);background:linear-gradient(150deg,rgba(100,116,139,0.06) 0%,#fff 100%);">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="dsc-icon" style="background:rgba(100,116,139,0.13);color:#64748b;"><i class="fas fa-clock"></i></div>
                            <span class="dsc-trend">&#8212; Stable</span>
                        </div>
                        <div class="dsc-value">{{ $leadStats['total_pending'] }}</div>
                        <div class="dsc-label">Total Pending</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admin.leads.index') }}" class="text-decoration-none">
                    <div class="dash-stat-card hover-lift" style="border-color:rgba(111,66,193,0.3);background:linear-gradient(150deg,rgba(111,66,193,0.06) 0%,#fff 100%);">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="dsc-icon" style="background:rgba(111,66,193,0.13);color:#6f42c1;"><i class="fas fa-calendar-week"></i></div>
                            <span class="dsc-trend">&#8212; Stable</span>
                        </div>
                        <div class="dsc-value">{{ $leadStats['week_new'] }}</div>
                        <div class="dsc-label">This Week</div>
                    </div>
                </a>
            </div>
        </div>
        @endif

        {{-- ── INSIGHTS ROW ──────────────────────────────────────────── --}}
        @if(in_array(auth()->user()->role, ['admin','super_admin','accountant']))
        <div class="section-label"><i class="fas fa-lightbulb me-1"></i>Insights</div>
        <div class="row g-3 mb-4">
            {{-- Customer Analytics --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark small"><i class="fas fa-users me-2 text-primary"></i>Customer Analytics</h6>
                        <a href="{{ route('admin.reports.sales') }}" class="btn btn-link btn-sm py-0 text-muted x-small">View Report</a>
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

            {{-- Follow-Up Alerts --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark small"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Follow-Up Alerts</h6>
                        <a href="{{ route('admin.leads.overdue') }}" class="btn btn-link btn-sm py-0 text-muted x-small">View All</a>
                    </div>
                    <div class="card-body py-2">
                        @php
                            $overdueLeads  = \App\Models\Lead::overdue()->count();
                            $todayLeads    = \App\Models\Lead::dueToday()->count();
                            $upcomingLeads = \App\Models\Lead::upcoming()->count();
                        @endphp
                        <div class="list-group list-group-flush small">
                            <a href="{{ route('admin.leads.overdue', ['filter' => 'overdue']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0">
                                <span><i class="fas fa-circle text-danger me-2 small"></i>Overdue</span>
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
                        <div class="alert alert-danger py-1 px-2 mt-2 mb-0 small border-0">
                            <i class="fas fa-bell me-1"></i>
                            <strong>{{ $overdueLeads }}</strong> follow-up{{ $overdueLeads > 1 ? 's' : '' }} need immediate attention!
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Seller Rankings --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark small"><i class="fas fa-trophy me-2 text-warning"></i>Seller Rankings</h6>
                        <a href="{{ route('admin.reports.sales') }}" class="btn btn-link btn-sm py-0 text-muted x-small">Full Report</a>
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
                                    @elseif($rank === 2) <i class="fas fa-medal" style="color:#cd7f32"></i>
                                    @else <span class="text-muted fw-bold" style="width:16px;display:inline-block">{{ $rank+1 }}</span>
                                    @endif
                                    <span class="fw-semibold">{{ Str::limit($seller['name'], 18) }}</span>
                                </div>
                                <span class="text-success fw-bold small">TZS {{ number_format($seller['revenue']) }}</span>
                            </li>
                            @endforeach
                        </ol>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── PERFORMANCE ───────────────────────────────────────────── --}}
        <div class="section-label"><i class="fas fa-star me-1"></i>Performance</div>
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-pen-nib me-2 text-info"></i>Top Design Performance</h6>
                        <a href="{{ route('admin.design-tasks.index') }}" class="btn btn-sm btn-link text-primary p-0 text-decoration-none small">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($topDesigners ?? [] as $designer)
                                <div class="list-group-item border-0 py-3 px-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                                style="width:32px;height:32px;font-size:12px;flex-shrink:0;">
                                                {{ substr($designer['name'], 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="small fw-bold text-dark">{{ $designer['name'] }}</div>
                                                <div class="x-small text-muted">{{ $designer['completed_tasks'] }} tasks completed</div>
                                            </div>
                                        </div>
                                        <span class="small fw-bold text-info">{{ number_format($designer['raw_achievement'], 1) }}%</span>
                                    </div>
                                    <div class="progress mt-1" style="height:5px;border-radius:3px;">
                                        <div class="progress-bar bg-info" style="width:{{ $designer['achievement'] }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-palette fa-2x mb-2 opacity-25"></i>
                                    <p class="small mb-0">No design performance data</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-trophy me-2 text-warning"></i>Top Sales Performance</h6>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('admin.saler-performance.index') }}" class="btn btn-sm btn-link text-primary p-0 text-decoration-none small">View All</a>
                            <a href="{{ route('admin.saler-performance.print') }}?period={{ $period ?? 'today' }}&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}"
                               target="_blank"
                               class="btn btn-sm btn-outline-secondary rounded-3 px-2 py-1 x-small"
                               title="Print Sales Performance">
                                <i class="fas fa-print"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($topSalers ?? [] as $saler)
                                <div class="list-group-item border-0 py-3 px-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                                style="width:32px;height:32px;font-size:12px;flex-shrink:0;">
                                                {{ substr($saler['name'], 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="small fw-bold text-dark">{{ $saler['name'] }}</div>
                                                <div class="x-small text-muted">TZS {{ number_format($saler['total_sales']) }}</div>
                                            </div>
                                        </div>
                                        <span class="small fw-bold text-primary">{{ number_format($saler['raw_achievement'], 1) }}%</span>
                                    </div>
                                    <div class="progress mt-1" style="height:5px;border-radius:3px;">
                                        <div class="progress-bar bg-primary" style="width:{{ $saler['achievement'] }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fa-2x mb-2 opacity-25"></i>
                                    <p class="small mb-0">No sales performance data</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── RECENT ACTIVITY ───────────────────────────────────────── --}}
        <div class="section-label"><i class="fas fa-history me-1"></i>Recent Activity</div>
        <div class="row g-3 mb-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-palette me-2 text-primary"></i>Recent Design Tasks</h6>
                        <a href="{{ route('admin.design-tasks.index') }}" class="btn btn-sm btn-link text-primary p-0 text-decoration-none small">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted x-small">
                                    <tr>
                                        <th class="ps-3 py-2 border-0">ID</th>
                                        <th class="border-0">Designer</th>
                                        <th class="border-0">Status</th>
                                        <th class="text-center pe-3 border-0">Deadline</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentAssignedTasks ?? [] as $task)
                                        <tr onclick="window.location='{{ route('admin.design-tasks.show', $task->id) }}'" style="cursor:pointer;">
                                            <td class="ps-3 fw-bold small">#{{ $task->id }}</td>
                                            <td class="small">{{ $task->designer->name ?? 'Unassigned' }}</td>
                                            <td><span class="badge rounded-pill bg-info text-dark x-small">{{ ucfirst($task->status) }}</span></td>
                                            <td class="text-center pe-3 small">{{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('M d') : '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center py-4 text-muted small">No recent tasks</td></tr>
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
                        <a href="{{ route('admin.finance.expenses') }}" class="btn btn-sm btn-link text-danger p-0 text-decoration-none small">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted x-small">
                                    <tr>
                                        <th class="ps-3 py-2 border-0">Category</th>
                                        <th class="text-end pe-3 border-0">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentExpenses ?? [] as $expense)
                                        <tr>
                                            <td class="ps-3 py-2">
                                                <div class="small fw-bold">{{ $expense->category }}</div>
                                                <div class="x-small text-muted text-truncate" style="max-width:150px;">{{ $expense->notes }}</div>
                                            </td>
                                            <td class="text-end pe-3 fw-bold text-danger small">TZS {{ number_format($expense->amount) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="text-center py-4 text-muted small">No recent expenses</td></tr>
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
    const profitLabels   = {!! json_encode($profit_chart_data['labels'] ?? []) !!};
    const profitRevenue  = {!! json_encode($profit_chart_data['revenue'] ?? []) !!};
    const profitExpenses = {!! json_encode($profit_chart_data['expenses'] ?? []) !!};
    const profitNet      = {!! json_encode($profit_chart_data['current'] ?? []) !!};

    function setProfitChart(type) {
        if (profitChart) profitChart.destroy();

        const isBar = type === 'bar';
        document.getElementById('btnBar').className  = 'btn btn-sm chart-toggle-btn ' + (isBar ? 'btn-primary' : 'btn-outline-secondary');
        document.getElementById('btnLine').className = 'btn btn-sm chart-toggle-btn ' + (!isBar ? 'btn-primary' : 'btn-outline-secondary');

        profitChart = new Chart(
            document.getElementById('combinedRevenueChart').getContext('2d'),
            {
                type: type,
                data: {
                    labels: profitLabels,
                    datasets: [
                        {
                            label: 'Revenue',
                            data: profitRevenue,
                            backgroundColor: isBar ? 'rgba(25,135,84,0.85)' : 'rgba(25,135,84,0.08)',
                            borderColor: '#198754',
                            borderWidth: isBar ? 0 : 2,
                            borderRadius: isBar ? 6 : 0,
                            fill: !isBar,
                            tension: 0.4,
                            pointRadius: isBar ? 0 : 4,
                            pointBackgroundColor: '#198754',
                        },
                        {
                            label: 'Expenses',
                            data: profitExpenses,
                            backgroundColor: isBar ? 'rgba(220,53,69,0.85)' : 'rgba(220,53,69,0.08)',
                            borderColor: '#dc3545',
                            borderWidth: isBar ? 0 : 2,
                            borderRadius: isBar ? 6 : 0,
                            fill: !isBar,
                            tension: 0.4,
                            pointRadius: isBar ? 0 : 4,
                            pointBackgroundColor: '#dc3545',
                        },
                        {
                            label: 'Net Profit',
                            data: profitNet,
                            backgroundColor: isBar ? 'rgba(124,58,237,0.85)' : 'rgba(124,58,237,0.08)',
                            borderColor: '#7c3aed',
                            borderWidth: isBar ? 0 : 2.5,
                            borderRadius: isBar ? 6 : 0,
                            fill: !isBar,
                            tension: 0.4,
                            pointRadius: isBar ? 0 : 4,
                            pointBackgroundColor: '#7c3aed',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: { boxWidth: 12, padding: 16, font: { size: 11 } }
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: ctx => ' ' + ctx.dataset.label + ': TZS ' + (ctx.parsed.y ?? 0).toLocaleString()
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                            ticks: {
                                font: { size: 11 },
                                callback: v => 'TZS ' + (v >= 1000 ? (v/1000).toFixed(0)+'K' : v)
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 } }
                        }
                    }
                }
            }
        );
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Default: bar chart
        setProfitChart('bar');

        // Task Pipeline — horizontal bar
        new Chart(
            document.getElementById('taskStatusChart').getContext('2d'),
            {
                type: 'bar',
                data: {
                    labels: ['Pending', 'In Progress', 'In Review', 'Printing', 'Completed', 'Rejected'],
                    datasets: [{
                        label: 'Tasks',
                        data: [
                            {{ $designTaskStatus['pending'] ?? 0 }},
                            {{ $designTaskStatus['in_progress'] ?? 0 }},
                            {{ $designTaskStatus['in_review'] ?? 0 }},
                            {{ $designTaskStatus['printing'] ?? 0 }},
                            {{ ($designTaskStatus['completed'] ?? 0) + ($designTaskStatus['super_completed'] ?? 0) }},
                            {{ $designTaskStatus['rejected'] ?? 0 }}
                        ],
                        backgroundColor: [
                            'rgba(245,158,11,0.85)',
                            'rgba(13,110,253,0.85)',
                            'rgba(13,202,240,0.85)',
                            'rgba(51,65,85,0.85)',
                            'rgba(25,135,84,0.85)',
                            'rgba(220,53,69,0.85)'
                        ],
                        borderRadius: 6,
                        borderWidth: 0,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 10,
                            cornerRadius: 8,
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                            ticks: { font: { size: 11 }, stepSize: 1 }
                        },
                        y: {
                            grid: { display: false },
                            ticks: { font: { size: 11 } }
                        }
                    }
                }
            }
        );

        // Period filter auto-submit
        const periodSelect   = document.getElementById('periodSelect');
        const customDateRange = document.getElementById('customDateRange');
        if (periodSelect) {
            periodSelect.addEventListener('change', function () {
                if (this.value === 'custom') {
                    customDateRange.classList.remove('d-none');
                } else {
                    customDateRange.classList.add('d-none');
                    this.form.submit();
                }
            });
        }
    });
</script>
@endpush
