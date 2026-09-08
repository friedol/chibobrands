@extends('layouts.admin')

@section('title', 'Profit and Loss (P&L)')

@section('content')
<div class="container-fluid">
    <style>
        @media print {
            .no-print { display: none !important; }
        }
    </style>
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                <div>
                    <h2 class="fw-bold mb-1">Profit and Loss</h2>
                    <p class="text-muted small mb-0">
                        Basis: <strong>{{ $basisLabel }}</strong>
                        @if($dateFrom && $dateTo)
                            · {{ $dateFrom->format('d M, Y') }} – {{ $dateTo->format('d M, Y') }}
                        @endif
                    </p>
                    <p class="text-muted small mb-0">Amount displayed in base currency: <strong>TZS</strong></p>
                </div>
                <div class="d-flex gap-2 no-print">
                    <x-report-export-menu
                        :print-url="route('admin.finance.profit-loss.print', request()->all())"
                        :pdf-url="route('admin.finance.profit-loss.pdf', request()->all())"
                        :excel-url="route('admin.finance.profit-loss.excel', request()->all())"
                    />
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.finance.profit-loss') }}" method="GET" class="row g-3 align-items-end" data-no-global-handler>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">Period</label>
                    <select name="period" class="form-select form-select-sm">
                        <option value="today" {{ ($period ?? '') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ ($period ?? '') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="week" {{ ($period ?? '') === 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ ($period ?? '') === 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="year" {{ ($period ?? '') === 'year' ? 'selected' : '' }}>This Year</option>
                        <option value="custom" {{ ($period ?? '') === 'custom' ? 'selected' : '' }}>Custom Date</option>
                        <option value="all" {{ ($period ?? '') === 'all' ? 'selected' : '' }}>All Time</option>
                    </select>
                </div>

                <div class="col-6 col-md-2 {{ ($period ?? '') === 'custom' ? '' : 'd-none' }}">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">From</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                </div>
                <div class="col-6 col-md-2 {{ ($period ?? '') === 'custom' ? '' : 'd-none' }}">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">To</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                </div>

                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-sync-alt me-1"></i> Update
                    </button>
                    <a href="{{ route('admin.finance.profit-loss') }}" class="btn btn-outline-secondary btn-sm">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    @php
        $net = (float) ($netProfitLoss ?? 0);
        $isLoss = $net < 0;
        $lossOrProfitLabel = $isLoss ? 'Net Loss' : 'Net Profit';
        $displayNetProfitLoss = $net; // keep sign so Loss is negative
    @endphp

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-file-invoice-dollar me-2 text-success"></i>Statement
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Account</th>
                            <th class="text-end pe-3">Amount (TZS)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-3">Operating Income</td>
                            <td class="text-end pe-3 fw-bold">{{ number_format($operatingIncome ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="ps-3 text-muted">Sales</td>
                            <td class="text-end pe-3">{{ number_format($sales ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="ps-3 text-muted">Discount</td>
                            <td class="text-end pe-3 text-danger">
                                {{ number_format($discount ?? 0, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-3">Total Operating Income</td>
                            <td class="text-end pe-3 fw-bold">{{ number_format($operatingIncome ?? 0, 2) }}</td>
                        </tr>

                        <tr>
                            <td class="ps-3">Cost of Goods Sold</td>
                            <td class="text-end pe-3 fw-bold">{{ number_format($cogs ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="ps-3">Gross Profit</td>
                            <td class="text-end pe-3 {{ ($grossProfit ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($grossProfit ?? 0, 2) }}
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-3">Operating Expense</td>
                            <td class="text-end pe-3 fw-bold text-danger">{{ number_format($operatingExpense ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="ps-3">Operating Profit</td>
                            <td class="text-end pe-3 {{ ($operatingProfit ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($operatingProfit ?? 0, 2) }}
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-3">Non Operating Income</td>
                            <td class="text-end pe-3 fw-bold">0.00</td>
                        </tr>
                        <tr>
                            <td class="ps-3">Non Operating Expense</td>
                            <td class="text-end pe-3 fw-bold text-danger">0.00</td>
                        </tr>

                        <tr class="{{ $isLoss ? 'table-warning' : '' }}">
                            <td class="ps-3 fw-bold">{{ $lossOrProfitLabel }}</td>
                            <td class="text-end pe-3 fw-bold {{ $isLoss ? 'text-danger' : 'text-success' }}">
                                {{ number_format($displayNetProfitLoss, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

