@extends('layouts.admin')

@section('title', 'Finance Report - ' . ($isRange ? $carbonFrom->format('d M') . ' to ' . $carbonTo->format('d M, Y') : $carbonFrom->format('d M, Y')))

@section('content')
@php
    $pdfParams = array_filter([
        'period' => $period ?? null,
        'date_from' => $dateFrom ?? null,
        'date_to' => $dateTo ?? null,
    ], fn($v) => $v !== null && $v !== '');
    $pdfUrl = route('admin.finance.daily-report.pdf', $pdfParams);
    $pdfFilename = 'finance-daily-report-' . (($dateFrom ?? '') === ($dateTo ?? '') ? ($dateFrom ?? 'report') : ($dateFrom ?? '') . '_to_' . ($dateTo ?? '')) . '.pdf';
@endphp
<div class="container-fluid">
    <!-- Header Section -->
    <div class="smart-toolbar no-print mb-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
            <div class="flex-grow-1">
                    <h2 class="mb-0 fw-bold text-dark">Finance Analytics</h2>
                    <p class="text-muted small mb-0">
                        @if($isRange)
                            Period: {{ $carbonFrom->format('M d, Y') }} - {{ $carbonTo->format('M d, Y') }}
                        @else
                            {{ $carbonFrom->format('l, F d, Y') }}
                        @endif
                    </p>
                </div>
            <div class="d-flex flex-wrap gap-2 smart-actions">
                <a href="{{ route('admin.finance.dashboard') }}" data-no-global-handler data-no-preloader class="btn btn-smart btn-sm">
                    <i class="fas fa-chart-line me-1"></i> Finance Hub
                </a>
                <a href="{{ route('admin.finance.cash-flow') }}" data-no-global-handler data-no-preloader class="btn btn-smart btn-sm">
                    <i class="fas fa-exchange-alt me-1"></i> Cash Flow
                </a>
                <x-report-export-menu
                    :print-url="$previewUrl"
                    :pdf-url="$pdfUrl"
                    print-target="_blank"
                    label="Export"
                />
            </div>
        </div>
    </div>

    <!-- Smart Filters -->
    <div class="mb-4 no-print" id="filterCollapse">
        <div class="card smart-filter-card border-0 shadow-sm">
            <div class="card-body bg-light p-3">
                <form action="{{ route('admin.finance.daily-report') }}" method="GET" class="row g-2 g-md-3 align-items-end" data-no-global-handler>
                        <div class="col-12 col-md-4 col-lg-3">
                            <label class="form-label fw-bold x-small text-uppercase mb-1">Time Period</label>
                            <select name="period" id="periodSelect" class="form-select form-select-sm">
                                <option value="today" {{ ($period ?? '') == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="yesterday" {{ ($period ?? '') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                                <option value="week" {{ ($period ?? '') == 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ ($period ?? '') == 'month' ? 'selected' : '' }}>This Month</option>
                                <option value="6_months" {{ ($period ?? '') == '6_months' ? 'selected' : '' }}>Last 6 Months</option>
                                <option value="year" {{ ($period ?? '') == 'year' ? 'selected' : '' }}>This Year</option>
                                <option value="2_years" {{ ($period ?? '') == '2_years' ? 'selected' : '' }}>Last 2 Years</option>
                                <option value="custom" {{ ($period ?? '') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                                <option value="all" {{ ($period ?? '') == 'all' ? 'selected' : '' }}>All Time</option>
                            </select>
                        </div>
                        
                        <div class="col-6 col-md-4 col-lg-3 custom-date-group {{ ($period ?? '') == 'custom' ? '' : 'd-none' }}">
                            <label class="form-label fw-bold x-small text-uppercase mb-1">From Date</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                        </div>

                        <div class="col-6 col-md-4 col-lg-3 custom-date-group {{ ($period ?? '') == 'custom' ? '' : 'd-none' }}">
                            <label class="form-label fw-bold x-small text-uppercase mb-1">To Date</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                        </div>

                        <div class="col-12 col-md-auto ms-md-auto d-flex gap-2 filter-submit-actions">
                            <button type="submit" class="btn btn-smart-dark btn-sm px-4 fw-bold">Apply</button>
                            <a href="{{ route('admin.finance.daily-report') }}" class="btn btn-smart-light btn-sm px-4 fw-bold">Reset</a>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Report Content -->
    <div class="report-container">
        <!-- Print Header (Consistently Styled) -->
        <div class="print-only report-header">
            <div class="d-flex justify-content-between align-items-center mb-0">
                <div class="d-flex align-items-center">
                    @include('partials.logo-print', ['logoStyle' => 'height:60px;margin-right:20px;object-fit:contain;'])
                    <div>
                        <h1 class="fw-bold mb-0 text-dark" style="letter-spacing: 0.5px;">Financial Report</h1>
                      
                        <p class="mb-0 x-small text-muted mt-1">Dar es Salaam, Tanzania | Tel: +255 655 392 319</p>
                    </div>
                </div>
                <div class="text-end">
                    <h3 class="fw-bold mb-1 text-dark">BUSINESS FINANCE SUMMARY</h3>
                    <p class="mb-0 fw-bold fs-5 text-dark text-uppercase">
                        @if($isRange)
                            {{ $carbonFrom->format('d M') }} - {{ $carbonTo->format('d M Y') }}
                        @else
                            {{ $carbonFrom->format('d M Y') }}
                        @endif
                    </p>
                    <p class="mb-0 x-small text-muted">Generated: {{ now()->format('M d, Y H:i') }}</p>
                </div>
            </div>
            <div class="border-top border-2 border-dark mt-2 mb-3"></div>
        </div>

        @php
            $grandTotalIncomeMobile = 0;
            $grandTotalIncomeCash = 0;
            $grandTotalIncomeBank = 0;
            $grandTotalIncomeRemain = 0;
            
            $grandTotalExpenseMobile = 0;
            $grandTotalExpenseCash = 0;
            $grandTotalExpenseBank = 0;
        @endphp

        @foreach($reportData as $data)
            @php
                $incomeItems = $data['incomeItems'];
                $expenseItems = $data['expenseItems'];
                $dept = $data['department'];

                $maxItems = max(count($incomeItems), count($expenseItems));
                
                $deptTotalIncomeMobile = 0;
                $deptTotalIncomeCash = 0;
                $deptTotalIncomeBank = 0;
                $deptTotalIncomeRemain = 0;
                
                $deptTotalExpenseMobile = 0;
                $deptTotalExpenseCash = 0;
                $deptTotalExpenseBank = 0;
            @endphp

            <div class="department-segment mb-5">
                <div class="table-responsive">
                    <table class="table table-bordered report-table align-middle shadow-sm">
                        <thead>
                            <tr class="header-row">
                                <th style="width: 12%; text-align: left !important;">CUSTOMER</th>
                                <th style="width: 18%; text-align: left !important;">{{ $dept->name }} TASK</th>
                                <th style="width: 6%">MOBILE</th>
                                <th style="width: 6%">CASH</th>
                                <th style="width: 6%">BANK</th>
                                <th style="width: 6%">REMAIN</th>
                                <th style="width: 19%; text-align: left !important;">CASHOUT (EXPENSES)</th>
                                <th style="width: 9%">MOBILE</th>
                                <th style="width: 9%">CASH</th>
                                <th style="width: 9%">BANK</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($i = 0; $i < $maxItems; $i++)
                                @php
                                    $income = $incomeItems[$i] ?? null;
                                    $expense = $expenseItems[$i] ?? null;
                                    
                                    if($income) {
                                        $deptTotalIncomeMobile += $income['mobile'];
                                        $deptTotalIncomeCash += $income['cash'];
                                        $deptTotalIncomeBank += $income['bank'];
                                        $deptTotalIncomeRemain += $income['remain'];

                                        $grandTotalIncomeMobile += $income['mobile'];
                                        $grandTotalIncomeCash += $income['cash'];
                                        $grandTotalIncomeBank += $income['bank'];
                                        $grandTotalIncomeRemain += $income['remain'];
                                    }
                                    
                                    if($expense) {
                                        $deptTotalExpenseMobile += $expense['mobile'];
                                        $deptTotalExpenseCash += $expense['cash'];
                                        $deptTotalExpenseBank += $expense['bank'];

                                        $grandTotalExpenseMobile += $expense['mobile'];
                                        $grandTotalExpenseCash += $expense['cash'];
                                        $grandTotalExpenseBank += $expense['bank'];
                                    }
                                @endphp
                                <tr>
                                    <!-- Income Side -->
                                    <td class="description-cell ps-3">
                                        <div class="fw-bold text-dark">{{ $income['customer_name'] ?? '' }}@if(isset($income['is_debt']) && $income['is_debt']) (dept) @endif</div>
                                    </td>
                                    <td class="description-cell ps-3 small text-muted">{{ $income['description'] ?? '' }}</td>
                                    <td class="amount-cell">{{ $income && $income['mobile'] > 0 ? number_format($income['mobile'], 0) : '' }}</td>
                                    <td class="amount-cell">{{ $income && $income['cash'] > 0 ? number_format($income['cash'], 0) : '' }}</td>
                                    <td class="amount-cell">{{ $income && $income['bank'] > 0 ? number_format($income['bank'], 0) : '' }}</td>
                                    <td class="amount-cell remain-cell fw-bold">{{ $income && $income['remain'] > 0 ? number_format($income['remain'], 0) : '' }}</td>
                                    
                                    <!-- Expense Side -->
                                    <td class="description-cell ps-3">{{ $expense['description'] ?? '' }}</td>
                                    <td class="amount-cell">{{ $expense && $expense['mobile'] > 0 ? number_format($expense['mobile'], 0) : '' }}</td>
                                    <td class="amount-cell text-danger fw-bold">{{ $expense && $expense['cash'] > 0 ? number_format($expense['cash'], 0) : '' }}</td>
                                    <td class="amount-cell">{{ $expense && $expense['bank'] > 0 ? number_format($expense['bank'], 0) : '' }}</td>
                                </tr>
                            @endfor

                            <!-- Department Sub-Totals Row -->
                            <tr class="total-row">
                                <td colspan="2" class="fw-bold ps-3" style="text-align: left !important;">{{ $dept->name }} TOTALS</td>
                                <td class="amount-cell fw-bold">{{ number_format($deptTotalIncomeMobile, 0) }}</td>
                                <td class="amount-cell fw-bold text-success">{{ number_format($deptTotalIncomeCash, 0) }}</td>
                                <td class="amount-cell fw-bold">{{ number_format($deptTotalIncomeBank, 0) }}</td>
                                <td class="amount-cell fw-bold text-primary">{{ number_format($deptTotalIncomeRemain, 0) }}</td>
                                
                                <td class="fw-bold ps-3" style="text-align: left !important;">DEPT CASHOUT</td>
                                <td class="amount-cell fw-bold">{{ number_format($deptTotalExpenseMobile, 0) }}</td>
                                <td class="amount-cell fw-bold text-danger">{{ number_format($deptTotalExpenseCash, 0) }}</td>
                                <td class="amount-cell fw-bold">{{ number_format($deptTotalExpenseBank, 0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        @php
            $totalIn    = $grandTotalIncomeMobile + $grandTotalIncomeCash + $grandTotalIncomeBank;
            $totalOut   = $grandTotalExpenseMobile + $grandTotalExpenseCash + $grandTotalExpenseBank;
            $revenue    = $totalIn + $grandTotalIncomeRemain; // total billed = collected + outstanding
            $netRevenue = $revenue - $totalOut;               // revenue minus expenses
            $net        = $totalIn - $totalOut;
        @endphp

        <!-- Consolidated Summary Row (Only at the end) -->
        <div class="row mt-5 g-5 no-break">
            <!-- Left Side: Global Summary -->
            <div class="col-md-6">
                <div class="ps-2">
                    <h6 class="fw-bold text-uppercase mb-4 pb-2 border-bottom border-secondary border-opacity-10">
                        <i class="fas fa-calculator me-2 text-primary"></i>Global Consolidated Summary
                    </h6>

                    {{-- Revenue = total billed (price of all tasks = collected + outstanding) --}}
                    <div class="d-flex justify-content-between mb-1 small">
                        <span class="text-muted fw-semibold">Revenue:</span>
                        <span class="fw-bold text-dark">TZS {{ number_format($revenue, 0) }}</span>
                    </div>

                    {{-- Collected = payments actually received --}}
                    <div class="d-flex justify-content-between mb-1 small ps-3">
                        <span class="text-muted">↳ Collected:</span>
                        <span class="fw-bold text-success">TZS {{ number_format($totalIn, 0) }}</span>
                    </div>

                    {{-- Outstanding balance still owed --}}
                    <div class="d-flex justify-content-between mb-3 small ps-3">
                        <span class="text-muted">↳ Outstanding <span class="x-small">(Remain)</span>:</span>
                        <span class="fw-bold text-info">TZS {{ number_format($grandTotalIncomeRemain, 0) }}</span>
                    </div>

                    {{-- Expenses --}}
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Total Expenses (Out):</span>
                        <span class="fw-bold text-warning">− TZS {{ number_format($totalOut, 0) }}</span>
                    </div>

                    {{-- Debt collected --}}
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Total Debt Collected:</span>
                        <span class="fw-bold text-primary">TZS {{ number_format($grandTotals['income']['total_debt'], 0) }}</span>
                    </div>

                    {{-- Net Revenue = Total Sales − Expenses --}}
                    <div class="d-flex justify-content-between border-top border-secondary border-opacity-10 pt-3 mt-3 mb-1 align-items-center">
                        <span class="fw-bold small text-uppercase">Net Revenue <span class="x-small text-muted fw-normal">(Total Sales − Expenses)</span>:</span>
                        <span class="fw-bold fs-5 {{ $netRevenue >= 0 ? 'text-success' : 'text-danger' }}">
                            TZS {{ number_format($netRevenue, 0) }}
                        </span>
                    </div>

                    {{-- Net Cash = Revenue (collected) − Expenses --}}
                    <div class="d-flex justify-content-between align-items-center small">
                        <span class="text-muted">Net Cash <span class="x-small">(Revenue − Expenses)</span>:</span>
                        <span class="fw-bold text-primary">TZS {{ number_format($net, 0) }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Right Side: Method Breakdown -->
            <div class="col-md-6">
                <div class="ps-md-5 border-start border-secondary border-opacity-10">
                    <h6 class="fw-bold text-uppercase mb-4 pb-2 border-bottom border-secondary border-opacity-10">
                        <i class="fas fa-money-check-alt me-2 text-primary"></i>Payment Breakdown
                    </h6>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="small text-muted mb-1 text-uppercase x-small">Cash (In)</div>
                            <div class="fw-bold text-success small">TZS {{ number_format($grandTotals['income']['cash'], 0) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="small text-muted mb-1 text-uppercase x-small">Mobile (In)</div>
                            <div class="fw-bold text-primary small">TZS {{ number_format($grandTotals['income']['mobile'], 0) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="small text-muted mb-1 text-uppercase x-small">Bank (In)</div>
                            <div class="fw-bold text-info small">TZS {{ number_format($grandTotals['income']['bank'], 0) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="small text-muted mb-1 text-uppercase x-small">Total In</div>
                            <div class="fw-bold text-dark small">TZS {{ number_format($grandTotals['income']['cash'] + $grandTotals['income']['mobile'] + $grandTotals['income']['bank'], 0) }}</div>
                        </div>
                    </div>

                    <div class="pt-2 border-top border-secondary border-opacity-10">
                        <span class="x-small fw-bold text-muted text-uppercase d-block mb-2">Expenses Detail</span>
                        <div class="d-flex justify-content-between x-small mb-1">
                            <span class="text-muted">Cash:</span>
                            <span class="text-danger">- TZS {{ number_format($grandTotals['expense']['cash'], 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between x-small mb-1">
                            <span class="text-muted">Mobile:</span>
                            <span>- TZS {{ number_format($grandTotals['expense']['mobile'], 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between x-small mb-1">
                            <span class="text-muted">Bank:</span>
                            <span>- TZS {{ number_format($grandTotals['expense']['bank'], 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800&display=swap');

    :root {
        --report-primary: #3b82f6;
        --report-secondary: #ef4444;
        --report-success: #10b981;
        --report-danger: #f87171;
        --report-warning: #fbbf24;
        --report-bg: #f8fafc;
        --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        --glass-bg: rgba(255, 255, 255, 0.85);
        --glass-border: rgba(255, 255, 255, 0.3);
    }

    .smart-toolbar {
        background: transparent;
        border: none;
        border-radius: 0;
        padding: 0;
    }

    .smart-actions .btn {
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        padding: 6px 12px;
    }

    .btn-smart {
        background: #ffffff;
        border: 1px solid #d1d5db;
        color: #334155;
    }

    .btn-smart:hover {
        background: #f8fafc;
        border-color: #9ca3af;
        color: #111827;
    }

    .smart-filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0 !important;
        border-radius: 14px;
    }

    .smart-input {
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        color: #111827;
        height: 36px;
        font-size: 12px;
        font-weight: 600;
    }

    .smart-input:focus {
        border-color: #6b7280;
        box-shadow: 0 0 0 2px rgba(107, 114, 128, 0.15);
        outline: none;
    }

    .btn-smart-dark {
        background: #111827;
        color: #ffffff;
        border: 1px solid #111827;
        border-radius: 10px;
    }

    .btn-smart-dark:hover {
        background: #0f172a;
        color: #ffffff;
    }

    .btn-smart-light {
        background: #ffffff;
        color: #334155;
        border: 1px solid #d1d5db;
        border-radius: 10px;
    }

    .btn-smart-light:hover {
        background: #f8fafc;
        color: #111827;
    }

    .glass-morphism {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
    }

    .filter-bar {
        transition: all 0.3s ease;
    }

    @media (max-width: 768px) {
        .smart-toolbar {
            padding: 12px;
        }

        .smart-actions {
            width: 100%;
        }

        .smart-actions .btn {
            flex: 1 1 calc(50% - 8px);
            text-align: center;
        }

        .filter-submit-actions .btn {
            flex: 1 1 0;
            text-align: center;
        }
    }

    @media (min-width: 769px) {
        .filter-submit-actions .btn {
            min-width: 110px;
        }
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .filter-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #64748b;
        margin-left: 5px;
    }

    .btn-preset {
        font-size: 13px;
        font-weight: 600;
        padding: 8px 18px;
        border: 1px solid #e2e8f0;
        background: white;
        color: #475569;
        transition: all 0.2s ease;
    }

    .btn-preset:hover {
        background: #f1f5f9;
        color: var(--report-primary);
    }

    .btn-preset.active {
        background: var(--report-primary);
        color: white;
        border-color: var(--report-primary);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .input-modern {
        display: flex;
        align-items: center;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 6px 12px;
        gap: 10px;
    }

    .input-modern .icon {
        color: var(--report-primary);
        font-size: 14px;
    }

    .input-modern input {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
        outline: none;
    }

    .btn-apply {
        background: #1e293b;
        color: white;
        font-weight: 700;
        padding: 10px 24px;
        border-radius: 10px;
        font-size: 13px;
        transition: all 0.2s ease;
        border: none;
    }

    .btn-apply:hover {
        background: #0f172a;
        transform: translateY(-2px);
        color: white;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
    }

    .btn-print-alt {
        background: white;
        color: #475569;
        border: 1px solid #e2e8f0;
        padding: 10px 15px;
        border-radius: 10px;
        transition: all 0.2s ease;
    }

    .btn-print-alt:hover {
        background: #f1f5f9;
        color: var(--report-primary);
        transform: translateY(-2px);
    }

    .filter-divider {
        width: 1px;
        height: 35px;
        background: #e2e8f0;
        align-self: flex-end;
        margin-bottom: 5px;
    }

    .filter-actions {
        align-self: flex-end;
        margin-bottom: 0px;
    }

    .preset-group {
        border-radius: 10px;
        overflow: hidden;
    }

    .preset-group .btn-preset:first-child { border-radius: 10px 0 0 10px; }
    .preset-group .btn-preset:last-child { border-radius: 0 10px 10px 0; }

    .report-container {
        font-family: 'Nunito Sans', sans-serif;
        background: white;
        border-radius: 10px;
        padding: 40px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(0,0,0,0.02);
    }

    /* Modern Table Styling */
    .report-table {
        border: none !important;
        margin-top: 20px;
        border-radius: 6px;
        overflow: hidden;
        border-collapse: separate !important;
        border-spacing: 0;
    }

    .report-table tbody td {
        padding: 5px 10px !important;
        border: 1px solid #cbd5e1 !important;
        font-size: 13px;
        color: #1e293b;
        transition: none;
    }

    /* Right border removal - handled by cell borders */

    .report-table thead th {
        background: #f1f5f9 !important; 
        color: #334155 !important; 
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 10px;
        padding: 7px 10px !important;
        border: 1px solid #94a3b8 !important;
    }

    /* Header border removal - handled by th borders */

    /* Soft Red for Expense Header */
    .report-table thead th:nth-child(7) {
        background: #fff5f5 !important;
        color: #9b1c1c !important;
    }

    /* Distinct group backgrounds for better column distinction - Exclude Totals & Headers */
    .report-table tbody tr:not(.total-row) td:nth-child(1), 
    .report-table tbody tr:not(.total-row) td:nth-child(2),
    .report-table tbody tr:not(.total-row) td:nth-child(7) {
        background-color: rgba(248, 250, 252, 0.8) !important;
    }

    /* Ledger vertical separator (Heavy) - Middle column */
    .report-table td:nth-child(6), 
    .report-table th:nth-child(6) {
        border-right: 2px solid #94a3b8 !important;
    }

    .report-table tbody tr:hover:not(.total-row) td {
        background-color: #f1f5f9 !important;
        color: #000;
    }

    .description-cell {
        font-weight: 500;
        color: #1a202c !important;
        text-align: left !important;
    }

    .amount-cell {
        text-align: right;
        font-family: 'Courier New', monospace;
        font-weight: 600;
        letter-spacing: -0.5px;
    }

    .report-table tbody tr:not(.total-row) td.remain-cell {
        color: var(--report-primary) !important;
        background-color: rgba(67, 97, 238, 0.03) !important;
    }

    /* Soft light blue for total row */
    .report-table tr.total-row td {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        font-weight: 800 !important;
        font-size: 13px !important;
        border: 1px solid #64748b !important;
        padding: 7px 10px !important;
    }

    /* Redundant border removal */

    .total-row td * {
        color: inherit !important;
    }

    .total-row .fw-bold {
        letter-spacing: 1px;
    }

    /* Summary Card Cards */
    .summary-stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        border: 1px solid #edf2f7;
        height: 100%;
        transition: transform 0.3s ease;
    }

    .summary-stat-card:hover {
        transform: translateY(-5px);
    }

    .signature-box {
        margin-top: 50px;
        padding: 20px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px dashed #cbd5e0;
    }

    .x-small { font-size: 11px; font-weight: 600; }



    .no-break {
        page-break-inside: avoid;
        break-inside: avoid;
    }

    .department-segment {
        page-break-inside: auto;
    }

    /* Print Visibility Helper - Screen Default */
    .print-only { display: none; }

    /* Aggressive Print Styling to bypass any hidden parents */
    @media print {
        @page { 
            size: A4 landscape; 
            margin: 10mm; 
        }

        /* Hide EVERYTHING by default */
        body * {
            visibility: hidden;
        }

        /* Explicitly show the report container and its children */
        .report-container, .report-container * {
            visibility: visible !important;
        }

        /* Ensure Print-Only elements are actually displayed */
        .print-only {
            display: block !important;
        }

        /* Position report container at top-left to ignore parent layout constraints */
        .report-container {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
            z-index: 99999 !important; /* On top of everything */
            display: block !important;
        }

        /* Ensure table styling remains intact */
        .report-table {
            width: 100% !important;
            border-collapse: collapse !important;
            border: 1px solid #94a3b8 !important;
        }

        .report-table thead th {
             background-color: #f1f5f9 !important;
            color: #0f172a !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            font-weight: bold !important;
            padding: 8px !important;
            border: 1px solid #94a3b8 !important;
        }

        .report-table tbody td {
            color: #1e293b !important;
            padding: 6px 8px !important;
            border: 1px solid #cbd5e1 !important;
        }

        /* Total Rows Styling */
        .total-row td {
            background-color: #e2e8f0 !important;
            color: #0f172a !important;
            font-weight: 800 !important;
            border: 1px solid #64748b !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Fix for Summary Section Side-by-Side */
        .no-break.row {
            display: flex !important;
            flex-direction: row !important;
            width: 100% !important;
            margin-top: 30px !important;
        }

        .no-break.row > .col-md-6 {
            width: 50% !important;
            flex: 0 0 50% !important;
            max-width: 50% !important;
        }
        
        /* Font Sizes */
        .x-small { font-size: 9pt !important; }
        .small { font-size: 10pt !important; }
        .fs-5 { font-size: 14pt !important; }
    }
</style>
@endpush
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
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

    });
</script>
@endpush
@endsection
