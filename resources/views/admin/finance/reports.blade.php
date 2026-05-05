@extends('layouts.admin')

@section('title', 'Financial Analytics Report')

@section('content')
<div class="container-fluid">
    <!-- Header & Action Bar -->
    <div class="row align-items-center mb-4 no-print">
        <div class="col-md-6">
            <h2 class="fw-bold mb-1">Financial Analytics</h2>
            <p class="text-muted small mb-0">Performance monitoring and audit control</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                <a href="{{ route('admin.finance.daily-report') }}" data-no-global-handler data-no-preloader class="btn btn-outline-info btn-sm px-3 shadow-sm bg-white text-info fw-bold">
                    <i class="fas fa-calendar-day me-1"></i> Daily Summary
                </a>
                <a href="{{ route('admin.finance.balance-sheet') }}" data-no-global-handler data-no-preloader class="btn btn-outline-primary btn-sm px-3 shadow-sm bg-white text-primary fw-bold">
                    <i class="fas fa-balance-scale me-1"></i> Balance Sheet
                </a>
                @php
                    $filterHasValues = request()->anyFilled([
                        'period',
                        'start_date',
                        'end_date',
                        'department_id',
                        'customer_date_from',
                        'customer_date_to',
                    ]);
                @endphp
                <button
                    class="btn btn-outline-primary btn-sm px-3 fw-bold"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterCollapse"
                >
                    <i class="fas fa-filter me-1"></i> Filter
                    @if($filterHasValues)
                        <span class="badge bg-primary ms-1">Active</span>
                    @endif
                </button>
                <button type="button" class="btn btn-dark btn-sm px-3 shadow-sm fw-bold" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print Report
                </button>
            </div>
        </div>
    </div>

    <!-- Modern Collapsable Filters (same UI as daily-report) -->
    @php
        $filterHasValues = request()->anyFilled(['period', 'department_id', 'customer_date_from', 'customer_date_to']);
    @endphp
    <div class="collapse {{ $filterHasValues ? 'show' : '' }} mb-4 no-print" id="filterCollapse">
        <div class="card border-0 shadow-sm border-top border-4 border-primary">
            <div class="card-body bg-light p-3">
                <form action="{{ route('admin.finance.reports') }}" method="GET" class="row g-2 align-items-end" data-no-global-handler>
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Time Period</label>
                        <select name="period" id="periodSelect" class="form-select form-select-sm">
                            <option value="today" {{ ($period ?? '') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="yesterday" {{ ($period ?? '') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                            <option value="week" {{ ($period ?? '') == 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ ($period ?? 'month') == 'month' ? 'selected' : '' }}>This Month</option>
                            <option value="year" {{ ($period ?? '') == 'year' ? 'selected' : '' }}>This Year</option>
                            <option value="customer_range" {{ ($period ?? '') == 'customer_range' ? 'selected' : '' }}>Customer Date Range</option>
                        </select>
                    </div>

                    {{-- Consolidated Department --}}
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Consolidated (All)</label>
                        <select name="department_id" class="form-select form-select-sm">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Customer date range --}}
                    <div class="col-6 col-md-2 customer-date-group {{ ($period ?? '') == 'customer_range' ? '' : 'd-none' }}">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">From Date</label>
                        <input type="date" name="customer_date_from" class="form-control form-control-sm" value="{{ request('customer_date_from') }}">
                    </div>
                    <div class="col-6 col-md-2 customer-date-group {{ ($period ?? '') == 'customer_range' ? '' : 'd-none' }}">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">To Date</label>
                        <input type="date" name="customer_date_to" class="form-control form-control-sm" value="{{ request('customer_date_to') }}">
                    </div>

                    <div class="col-12 col-md-auto ms-md-auto">
                        <div class="btn-group shadow-sm w-100">
                            <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">APPLY</button>
                            <a href="{{ route('admin.finance.reports') }}" class="btn btn-dark btn-sm px-4 fw-bold">RESET</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Print Header (Hidden on screen) -->
    <div class="print-only report-header mb-4">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3">
            <div>
                <img src="{{ asset('images/logo.webp') }}" alt="Logo" style="height: 50px;" onerror="this.style.display='none'">
                <h2 class="fw-bold text-dark mt-2 mb-1">
                    {{ $currentDept ? $currentDept->name . ' - ' : '' }}Financial Performance Report
                </h2>
                <p class="mb-0 text-muted">Scope: {{ $currentDept ? 'Departmental Analysis' : 'Consolidated Group Report' }}</p>
                <p class="mb-0 text-muted">Period: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</p>
                @php
                    $custFrom = request('customer_date_from');
                    $custTo = request('customer_date_to');
                @endphp
                @if($custFrom || $custTo)
                    <p class="mb-0 text-muted small">
                        Customers Added: {{ $custFrom ? \Carbon\Carbon::parse($custFrom)->format('M d, Y') : '...'}} -
                        {{ $custTo ? \Carbon\Carbon::parse($custTo)->format('M d, Y') : '...'}}
                    </p>
                @endif
            </div>
            <div class="text-end text-dark">
                <p class="mb-1 fw-bold">CHIBOBRAND CO. LTD.</p>
                <p class="mb-1 small">Generated: {{ now()->format('M d, Y H:i') }}</p>
                <p class="mb-0 small">Ref: FIN-{{ now()->format('Ymd') }}-{{ $currentDept ? $currentDept->id : 'ALL' }}</p>
            </div>
        </div>
    </div>

    <!-- Section 1: Executive Summary -->
    <div class="row mb-3 g-2">
        <div class="col-6 col-md-4 col-lg">
            <div class="card border-0 shadow-sm h-100 card-metric border-start border-4 border-info">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-info bg-opacity-10 text-info me-2" style="height:35px; width:35px;">
                            <i class="fas fa-tags fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Total Sales</span>
                    </div>
                    <div class="h4 mb-0 fw-bold text-info">{{ number_format($totalBilled ?? 0) }}</div>
                    <div class="x-small text-muted">Gross value sold</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="card border-0 shadow-sm h-100 card-metric border-start border-4 border-primary">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2" style="height:35px; width:35px;">
                            <i class="fas fa-file-invoice fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Total Revenue</span>
                    </div>
                    <div class="h4 mb-0 fw-bold text-dark">{{ number_format($totalRevenue) }}</div>
                    <div class="x-small text-muted">Collected + Outstanding</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="card border-0 shadow-sm h-100 card-metric border-start border-4 border-success">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-success bg-opacity-10 text-success me-2" style="height:35px; width:35px;">
                            <i class="fas fa-money-bill-wave fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Total Received</span>
                    </div>
                    <div class="h4 mb-0 fw-bold text-dark">{{ number_format($deposited) }}</div>
                    <div class="x-small text-success fw-bold">Money in hand</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="card border-0 shadow-sm h-100 card-metric border-start border-4" style="border-left-color: #20c997 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-opacity-10 me-2" style="background-color: rgba(32, 201, 151, 0.1); color: #20c997; height:35px; width:35px;">
                            <i class="fas fa-hand-holding-dollar fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Debt Collected</span>
                    </div>
                    <div class="h4 mb-0 fw-bold" style="color: #20c997;">{{ number_format($debtCollected ?? 0) }}</div>
                    <div class="x-small text-muted">Past debts recovered</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="card border-0 shadow-sm h-100 card-metric border-start border-4 border-warning">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-warning bg-opacity-10 text-warning me-2" style="height:35px; width:35px;">
                            <i class="fas fa-clock fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Outstanding</span>
                    </div>
                    <div class="h4 mb-0 fw-bold text-dark">{{ number_format($balanceDue ?? 0) }}</div>
                    <div class="x-small text-danger fw-bold">Balance Due</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-6 col-lg">
            <div class="card border-0 shadow-sm h-100 card-metric border-start border-4 border-danger">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-danger bg-opacity-10 text-danger me-2" style="height:35px; width:35px;">
                            <i class="fas fa-receipt fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Total Expenses</span>
                    </div>
                    <div class="h4 mb-0 fw-bold text-dark">{{ number_format($spent) }}</div>
                    <div class="x-small text-muted">Money spent</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-6 col-lg">
            <div class="card border-0 shadow-sm h-100 card-metric border-start border-4 border-dark">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-dark bg-opacity-10 text-dark me-2" style="height:35px; width:35px;">
                            <i class="fas fa-wallet fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Net Balance</span>
                    </div>
                    <div class="h4 mb-0 fw-bold text-dark">{{ number_format($balance) }}</div>
                    <div class="x-small text-muted">Cash Surplus/Deficit</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Visual Analytics -->
    <div class="row g-2 mb-2 charts-row">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-1 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary small"><i class="fas fa-chart-area me-1"></i>Performance Trend</h6>
                    <div class="btn-group btn-group-sm shadow-sm" role="group">
                        <button type="button" class="btn btn-outline-dark active" id="btnReportLine" onclick="toggleReportChart('line')" style="padding: 2px 6px; font-size: 10px;">
                            <i class="fas fa-chart-line"></i>
                        </button>
                        <button type="button" class="btn btn-outline-dark" id="btnReportBar" onclick="toggleReportChart('bar')" style="padding: 2px 6px; font-size: 10px;">
                            <i class="fas fa-chart-bar"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-1">
                    <div style="height: 200px;">
                        <canvas id="financeTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-1 border-0">
                    <h6 class="m-0 fw-bold text-primary small"><i class="fas fa-chart-pie me-1"></i>Revenue Split</h6>
                </div>
                <div class="card-body p-1">
                    <div style="height: 200px;">
                        <canvas id="revenuePieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Detailed Breakdowns -->
    <div class="row g-2 mb-3">
        <!-- Department Performance -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">Departmental Performance Analysis</h5>
                    <span class="badge bg-light text-dark border x-small">Audit Mode</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="x-small text-uppercase fw-bold text-muted">
                                    <th class="ps-2 py-2">Department</th>
                                    <th class="text-end py-2">Collected</th>
                                    <th class="text-end py-2">Credited</th>
                                    <th class="text-end py-2">Total Rev</th>
                                    <th class="text-end py-2">Expenses</th>
                                    <th class="text-end pe-2 py-2">Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deptWise as $dept)
                                <tr class="{{ (request('department_id') == $dept['id']) ? 'bg-primary bg-opacity-10' : '' }}">
                                    <td class="ps-2 py-2">
                                        <div class="fw-bold">{{ $dept['name'] }}</div>
                                        <a href="?department_id={{ $dept['id'] }}&period={{ $period }}" class="x-small text-decoration-none no-print"><i class="fas fa-eye me-1"></i>Focus</a>
                                    </td>
                                    <td class="text-end text-success py-2">{{ number_format($dept['collected']) }}</td>
                                    <td class="text-end text-warning py-2">{{ number_format($dept['credited']) }}</td>
                                    <td class="text-end fw-bold text-dark py-2">{{ number_format($dept['total_revenue']) }}</td>
                                    <td class="text-end text-danger low-font py-2">{{ number_format($dept['expenses']) }}</td>
                                    <td class="text-end pe-2 fw-bold py-2 {{ ($dept['collected'] - $dept['expenses']) >= 0 ? 'text-primary' : 'text-danger' }}">
                                        {{ number_format($dept['collected'] - $dept['expenses']) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Performance -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0 text-dark">Collections by Staff</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($sellerWise as $seller)
                        <div class="list-group-item px-3 py-2 border-0 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark">{{ $seller['name'] }}</span>
                                <span class="badge bg-primary rounded-pill">TZS {{ number_format($seller['total_revenue']) }}</span>
                            </div>
                            <div class="row g-0 x-small">
                                <div class="col-6">
                                    <span class="text-success fw-bold">Collected:</span> 
                                    <span class="text-muted">{{ number_format($seller['collected']) }}</span>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="text-danger fw-bold">Uncollected:</span> 
                                    <span class="text-muted">{{ number_format($seller['credited']) }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: Transaction Journal -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">Activity Ledger (Audit Trail)</h5>
                    <p class="x-small text-muted mb-0">Latest 10 transactions for this period</p>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="x-small text-uppercase fw-bold text-muted">
                                    <th class="ps-4">Date</th>
                                    <th>Customer / Description</th>
                                    <th>Department</th>
                                    <th>Method</th>
                                    <th class="text-end pe-4">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $tx)
                                <tr>
                                    <td class="ps-4 small text-muted">{{ $tx->date->format('d M, Y') }}</td>
                                    <td>
                                        <div class="fw-bold small">{{ $tx->customer->name ?? 'Direct Payment' }}</div>
                                        <div class="x-small text-muted">Ref: {{ $tx->id }}</div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $tx->department->name ?? 'POS' }}</span></td>
                                    <td class="small">{{ ucfirst(str_replace('_', ' ', $tx->payment_method)) }}</td>
                                    <td class="text-end pe-4 fw-bold text-success">+{{ number_format($tx->amount) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">No transactions found in this period.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Footer -->
    <div class="print-only print-footer">
        <div class="row align-items-center mb-1">
            <div class="col-8 text-start">
                <p class="mb-0 x-small text-muted">&copy; {{ date('Y') }} CHIBOBRAND. Confidential Document.</p>
            </div>
            <div class="col-4 text-end">
                <p class="mb-0 small"><b>Printed by:</b> {{ auth()->user()->name }}</p>
            </div>
        </div>
        <div class="text-center">
            <p class="mb-0 x-small text-muted">Developer: Fridoltech | Generated: {{ now()->format('M d, Y H:i') }}</p>
        </div>
    </div>
</div>

@push('styles')
<style>
    .container-fluid { font-size: 13px; }
    h2 { font-size: 1.4rem !important; }
    @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800&display=swap');

    :root {
        --report-primary: #3b82f6;
        --report-bg: #f8fafc;
        --glass-bg: rgba(255, 255, 255, 0.85);
        --glass-border: rgba(255, 255, 255, 0.3);
    }

    body {
        background-color: var(--report-bg);
        font-family: 'Nunito Sans', sans-serif;
    }

    .glass-morphism {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
    }

    .filter-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-left: 5px; margin-bottom: 5px; }

    .btn-preset {
        font-size: 13px; font-weight: 600; padding: 8px 18px;
        border: 1px solid #e2e8f0; background: white; color: #475569; transition: all 0.2s ease;
    }
    .btn-preset:hover { background: #f1f5f9; color: var(--report-primary); }
    .btn-preset.active {
        background: var(--report-primary); color: white; border-color: var(--report-primary);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .input-modern {
        display: flex; align-items: center; background: white;
        border: 1px solid #e2e8f0; border-radius: 10px; padding: 6px 12px; gap: 8px;
    }
    .input-modern .icon { color: var(--report-primary); font-size: 14px; }
    .input-modern select { font-size: 13px; font-weight: 600; color: #1e293b; outline: none; border: none; background: transparent; }

    .btn-apply {
        background: #1e293b; color: white; font-weight: 700; padding: 10px 24px;
        border-radius: 10px; font-size: 13px; transition: all 0.2s ease; border: none;
    }
    .btn-apply:hover { background: #0f172a; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); }

    .filter-divider { width: 1px; height: 35px; background: #e2e8f0; align-self: flex-end; margin-bottom: 5px; }
    .preset-group { border-radius: 10px; overflow: hidden; }

    .card-metric { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .card-metric:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.05) !important; }
    
    .x-small { font-size: 11px; }
    
    @media print {
        .no-print { display: none !important; }
        .print-only { display: block !important; }
        body { background: white !important; }
        .card { border: 1px solid #eee !important; box-shadow: none !important; }
        @page { size: A4; margin: 5mm; }
        body { background: white !important; font-size: 8.5pt !important; margin: 0 !important; padding: 0 !important; color: #000 !important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; overflow: visible !important; }
        .container-fluid { width: 100% !important; padding: 0 !important; max-width: 100% !important; }
        
        .print-only { display: block !important; }
        .report-header { margin-bottom: 10px; border-bottom: 2px solid #ddd; padding-bottom: 4px; }
        .report-header h1 { font-size: 14pt !important; margin: 0; color: #333; }
        
        .card { border: 1px solid #ddd !important; box-shadow: none !important; margin-bottom: 5px !important; break-inside: avoid; overflow: visible !important; height: auto !important; }
        .card-body { padding: 0.3rem !important; }
        .card-header { padding: 0.2rem 0.4rem !important; border-bottom: 1px solid #ddd !important; background-color: #fcfcfc !important; -webkit-print-color-adjust: exact; }
        .card-header h6, .card-header h5 { color: #0d6efd !important; font-weight: bold !important; font-size: 9pt !important; }
        
        .table-responsive { overflow: visible !important; display: block !important; }
        .table { width: 100% !important; border-collapse: collapse !important; font-size: 7.5pt !important; color: #333 !important; }
        .table th, .table td { border: 1px solid #ddd !important; padding: 2px 4px !important; }
        .table thead th { background-color: #f8f9fa !important; font-weight: 900 !important; -webkit-print-color-adjust: exact; color: #555 !important; }
        
        .text-end { white-space: nowrap !important; }
        
        /* Tighten rows and columns */
        .row { display: flex !important; flex-wrap: wrap !important; margin-left: -3px !important; margin-right: -3px !important; }
        .row > [class*="col-"] { padding: 0 3px !important; margin-bottom: 5px !important; flex: 0 0 auto !important; }
        
        .col-6.col-md-3 { width: 25% !important; }
        .charts-row .col-lg-8, .charts-row .col-lg-7 { width: 65% !important; }
        .charts-row .col-lg-4, .charts-row .col-lg-5 { width: 35% !important; }
        .col-lg-7 { width: 65% !important; }
        .col-lg-5 { width: 35% !important; }
        .col-12 { width: 100% !important; }
        
        canvas { width: 100% !important; height: auto !important; max-height: 150px !important; }
        
        .print-footer {
            position: fixed;
            bottom: 0px;
            width: 100%;
            border-top: 1px solid #ddd;
            padding-top: 3px;
            font-size: 7pt;
            background: white !important;
        }

        .no-print, .btn, .btn-group, .sidebar, .sidebar-nav, .top-navbar, .breadcrumb, footer, form, select, .no-print * { 
            display: none !important; 
        }
    }
    .print-only { display: none; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show date fields depending on selected mode:
    // period="customer_range" => show customer_date_from/customer_date_to
    const periodSelect = document.getElementById('periodSelect');
    const customDateGroups = document.querySelectorAll('.custom-date-group');
    const customerDateGroups = document.querySelectorAll('.customer-date-group');

    if (periodSelect) {
        function toggleCustomDates() {
            const mode = periodSelect.value;
            if (mode === 'custom') {
                customDateGroups.forEach(el => el.classList.remove('d-none'));
                customerDateGroups.forEach(el => el.classList.add('d-none'));
            } else if (mode === 'customer_range') {
                customDateGroups.forEach(el => el.classList.add('d-none'));
                customerDateGroups.forEach(el => el.classList.remove('d-none'));
            } else {
                customDateGroups.forEach(el => el.classList.add('d-none'));
                customerDateGroups.forEach(el => el.classList.add('d-none'));
            }
        }

        toggleCustomDates();
        periodSelect.addEventListener('change', toggleCustomDates);
    }

    // Trend Chart
    let trendChart;
    
    window.toggleReportChart = function(type) {
        const isBar = type === 'bar';
        document.getElementById('btnReportLine').classList.toggle('active', !isBar);
        document.getElementById('btnReportBar').classList.toggle('active', isBar);
        
        if (trendChart) trendChart.destroy();
        
        const ctxTrend = document.getElementById('financeTrendChart').getContext('2d');
        trendChart = new Chart(ctxTrend, {
            type: type,
            data: {
                labels: @json($chartData['labels']),
                datasets: [
                    {
                        label: 'Cash In',
                        data: @json($chartData['income']),
                        borderColor: '#198754',
                        backgroundColor: isBar ? '#198754' : 'rgba(25, 135, 84, 0.05)',
                        borderWidth: isBar ? 0 : 2,
                        fill: !isBar,
                        tension: 0.3,
                        pointRadius: 2
                    },
                    {
                        label: 'Cash Out',
                        data: @json($chartData['expenses']),
                        borderColor: '#dc3545',
                        backgroundColor: isBar ? '#dc3545' : 'rgba(220, 53, 69, 0.05)',
                        borderWidth: isBar ? 0 : 2,
                        fill: !isBar,
                        tension: 0.3,
                        pointRadius: 2
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                layout: { padding: { top: 5, bottom: 5, left: 5, right: 5 } },
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 10, padding: 5, font: { size: 10 } } }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 9 }, maxRotation: 0 } },
                    y: { ticks: { font: { size: 9 }, padding: 2, callback: v => 'TZS ' + (v/1000) + 'k' } }
                }
            }
        });
    };
    
    toggleReportChart('line');

    // Pie Chart
    const ctxPie = document.getElementById('revenuePieChart').getContext('2d');
    const deptWise = @json($deptWise);
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: deptWise.map(d => d.name),
            datasets: [{
                data: deptWise.map(d => d.total_revenue),
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'],
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            layout: { padding: 5 },
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 10, padding: 8, font: { size: 10 } } }
            },
            cutout: '65%'
        }
    });

    window.onbeforeprint = () => { document.title = "Finance_Report_{{ $currentDept ? $currentDept->name : 'Consolidated' }}_{{ now()->format('Ymd') }}"; };
});
</script>
@endpush
@endsection
