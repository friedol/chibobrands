@extends('layouts.admin')

@section('title', 'Balance Sheet - ' . \Carbon\Carbon::parse($dateFrom)->format('M d') . ' – ' . $asAt->format('M d, Y'))

@section('content')
@php
    $pdfParams = array_filter([
        'period' => $period ?? null,
        'start_date' => $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('Y-m-d') : null,
        'end_date' => $dateTo ? \Carbon\Carbon::parse($dateTo)->format('Y-m-d') : null,
        'department_id' => request('department_id'),
    ], fn($v) => $v !== null && $v !== '');
    $pdfUrl = route('admin.finance.balance-sheet.pdf', $pdfParams);
    $pdfFilename = 'balance-sheet-' . $asAt->format('Y-m-d') . (request('department_id') ? '-dept-' . request('department_id') : '') . '.pdf';
@endphp
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3 no-print">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none"><i class="fas fa-home me-1"></i>Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.finance.dashboard') }}" class="text-decoration-none">Finance</a></li>
            <li class="breadcrumb-item active" aria-current="page">Balance Sheet</li>
        </ol>
    </nav>

    <!-- Header & Actions -->
    <div class="row align-items-center mb-4 no-print">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                <div>
                    <h2 class="mb-1 fw-bold text-dark">Balance Sheet</h2>
                    @php
                    $df = $dateFrom instanceof \Carbon\Carbon ? $dateFrom : \Carbon\Carbon::parse($dateFrom);
                    $dt = $dateTo instanceof \Carbon\Carbon ? $dateTo : \Carbon\Carbon::parse($dateTo);
                @endphp
<p class="text-muted small mb-0">{{ $df->format('M d, Y') }} – {{ $dt->format('M d, Y') }}{{ $currentDept ? ' · ' . $currentDept->name : ' · All departments' }}</p>
<p class="text-muted small mb-0">Base currency: <strong>TZS</strong></p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.finance.dashboard') }}" data-no-global-handler data-no-preloader class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-chart-line me-1"></i> Finance Hub
                    </a>
                    <a href="{{ route('admin.finance.reports') }}" data-no-global-handler data-no-preloader class="btn btn-outline-info btn-sm">
                        <i class="fas fa-file-invoice me-1"></i> Analytics
                    </a>
                    <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="btn btn-success btn-sm share-pdf-btn" data-pdf-url="{{ $pdfUrl }}" data-pdf-filename="{{ $pdfFilename }}" title="Share as PDF">
                        <i class="fas fa-share-alt me-1"></i> Share PDF
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-body p-3">
            @php
                $resolvedEnd = $dateTo instanceof \Carbon\Carbon ? $dateTo->format('Y-m-d') : \Carbon\Carbon::parse($dateTo)->format('Y-m-d');
                $resolvedStart = $dateFrom instanceof \Carbon\Carbon ? $dateFrom->format('Y-m-d') : \Carbon\Carbon::parse($dateFrom)->format('Y-m-d');
                $isCustom = ($period ?? '') === 'custom';
            @endphp
            <form action="{{ route('admin.finance.balance-sheet') }}" method="GET" class="row g-3 align-items-end" data-no-global-handler id="balanceSheetFilterForm">
                @if($isCustom)
                <input type="hidden" name="start_date" id="bsStartDate" value="{{ $resolvedStart }}">
                <input type="hidden" name="end_date" id="bsEndDate" value="{{ $resolvedEnd }}">
                @endif
                <div class="col-12 col-md-3">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">Period</label>
                    <select name="period" id="bsPeriodSelect" class="form-select form-select-sm">
                        <option value="today" {{ ($period ?? '') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ ($period ?? '') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="week" {{ ($period ?? '') == 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ ($period ?? '') == 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="year" {{ ($period ?? '') == 'year' ? 'selected' : '' }}>This Year</option>
                        <option value="custom" {{ ($period ?? '') == 'custom' ? 'selected' : '' }}>Custom Date</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 bs-custom-date {{ ($period ?? '') == 'custom' ? '' : 'd-none' }}">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">As at date</label>
                    <input type="date" name="end_date_visible" id="bsEndDateVisible" class="form-control form-control-sm" value="{{ request('end_date', $resolvedEnd) }}" aria-label="As at date">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">Department</label>
                    <select name="department_id" class="form-select form-select-sm">
                        <option value="">All (Consolidated)</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-sync-alt me-1"></i> Update
                    </button>
                    <a href="{{ route('admin.finance.balance-sheet') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary cards: Mobile, Cash, Bank, then Receivables etc. -->
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-info bg-opacity-10 text-info me-2" style="height:32px;width:32px;"><i class="fas fa-mobile-alt fa-sm"></i></div>
                        <span class="text-uppercase x-small fw-bold text-muted">Mobile</span>
                    </div>
                    <div class="h4 mb-0 fw-bold">TZS {{ number_format($cash_mobile ?? 0) }}</div>
                    <div class="x-small text-muted">In period (net)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2" style="height:32px;width:32px;"><i class="fas fa-money-bill-wave fa-sm"></i></div>
                        <span class="text-uppercase x-small fw-bold text-muted">Cash</span>
                    </div>
                    <div class="h4 mb-0 fw-bold">TZS {{ number_format($cash_cash ?? 0) }}</div>
                    <div class="x-small text-muted">In period (net)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm border-start border-4 border-secondary h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-secondary bg-opacity-10 text-secondary me-2" style="height:32px;width:32px;"><i class="fas fa-university fa-sm"></i></div>
                        <span class="text-uppercase x-small fw-bold text-muted">Bank</span>
                    </div>
                    <div class="h4 mb-0 fw-bold">TZS {{ number_format($cash_bank ?? 0) }}</div>
                    <div class="x-small text-muted">In period (net)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-success bg-opacity-10 text-success me-2" style="height:32px;width:32px;"><i class="fas fa-file-invoice-dollar fa-sm"></i></div>
                        <span class="text-uppercase x-small fw-bold text-muted">Receivables</span>
                    </div>
                    <div class="h4 mb-0 fw-bold">TZS {{ number_format($receivables) }}</div>
                    <div class="x-small text-muted">In period (tasks)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm border-start border-4 border-dark h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-dark bg-opacity-10 text-dark me-2" style="height:32px;width:32px;"><i class="fas fa-balance-scale fa-sm"></i></div>
                        <span class="text-uppercase x-small fw-bold text-muted">Total Assets</span>
                    </div>
                    <div class="h4 mb-0 fw-bold text-success">TZS {{ number_format($total_assets) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-warning bg-opacity-10 text-warning me-2" style="height:32px;width:32px;"><i class="fas fa-hand-holding-usd fa-sm"></i></div>
                        <span class="text-uppercase x-small fw-bold text-muted">Liabilities</span>
                    </div>
                    <div class="h4 mb-0 fw-bold">TZS {{ number_format($liabilities) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-warning bg-opacity-10 text-warning me-2" style="height:32px;width:32px;"><i class="fas fa-chart-pie fa-sm"></i></div>
                        <span class="text-uppercase x-small fw-bold text-muted">Equity</span>
                    </div>
                    <div class="h4 mb-0 fw-bold text-dark">TZS {{ number_format($equity) }}</div>
                    <div class="x-small text-muted">Net position</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Balance Sheet Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0 text-dark">Balance Sheet</h5>
                    <span class="text-muted small">{{ $dateFrom->format('M d') }} – {{ $asAt->format('M d, Y') }}{{ $currentDept ? ' · ' . $currentDept->name : ' · Consolidated' }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-2 ps-3">ASSETS</th>
                                    <th class="text-end py-2 pe-3">TZS</th>
                                    <th class="py-2 ps-3">LIABILITIES & EQUITY</th>
                                    <th class="text-end py-2 pe-3">TZS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-3">Mobile (net)</td>
                                    <td class="text-end fw-bold">{{ number_format($cash_mobile ?? 0) }}</td>
                                    <td class="ps-3">Liabilities</td>
                                    <td class="text-end fw-bold">{{ number_format($liabilities) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-3">Cash (net)</td>
                                    <td class="text-end fw-bold">{{ number_format($cash_cash ?? 0) }}</td>
                                    <td class="ps-3">Equity (net position)</td>
                                    <td class="text-end fw-bold">{{ number_format($equity) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-3">Bank (net)</td>
                                    <td class="text-end fw-bold">{{ number_format($cash_bank ?? 0) }}</td>
                                    <td class="ps-3"></td>
                                    <td class="text-end"></td>
                                </tr>
                                <tr>
                                    <td class="ps-3">Accounts receivable</td>
                                    <td class="text-end fw-bold">{{ number_format($receivables) }}</td>
                                    <td class="ps-3"></td>
                                    <td class="text-end"></td>
                                </tr>
                                <tr class="table-light">
                                    <td class="ps-3 fw-bold">Total assets</td>
                                    <td class="text-end fw-bold">{{ number_format($total_assets) }}</td>
                                    <td class="ps-3 fw-bold">Total liabilities & equity</td>
                                    <td class="text-end fw-bold">{{ number_format($total_liabilities_equity) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Department breakdown (when not filtering by single department) -->
    @if(!$currentDept && $department_breakdown->isNotEmpty())
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0 text-dark">By department ({{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} – {{ $asAt->format('M d, Y') }})</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="x-small text-uppercase fw-bold text-muted">
                                    <th class="ps-3 py-2">Department</th>
                                    <th class="text-end py-2">Mobile</th>
                                    <th class="text-end py-2">Cash</th>
                                    <th class="text-end py-2">Bank</th>
                                    <th class="text-end py-2">Receivables</th>
                                    <th class="text-end pe-3 py-2">Total assets</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($department_breakdown as $d)
                                <tr>
                                    <td class="ps-3">{{ $d['name'] }}</td>
                                    <td class="text-end">{{ number_format($d['mobile'] ?? 0) }}</td>
                                    <td class="text-end">{{ number_format($d['cash'] ?? 0) }}</td>
                                    <td class="text-end">{{ number_format($d['bank'] ?? 0) }}</td>
                                    <td class="text-end">{{ number_format($d['receivables']) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($d['total_assets']) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
(function() {
    var form = document.getElementById('balanceSheetFilterForm');
    var sel = document.getElementById('bsPeriodSelect');
    var customGroup = document.querySelectorAll('.bs-custom-date');
    var endDateVisible = document.getElementById('bsEndDateVisible');
    var startDateHidden = document.getElementById('bsStartDate');
    var endDateHidden = document.getElementById('bsEndDate');
    if (!sel || !form) return;

    function toggle() {
        var show = sel.value === 'custom';
        customGroup.forEach(function(el) { el.classList.toggle('d-none', !show); });
        if (show && endDateVisible && endDateHidden) endDateVisible.value = endDateHidden.value;
    }

    function syncCustomToHidden() {
        if (sel.value !== 'custom' || !endDateVisible || !startDateHidden || !endDateHidden) return;
        if (endDateVisible.value) {
            endDateHidden.value = endDateVisible.value;
            startDateHidden.value = endDateVisible.value;
        }
    }

    if (endDateVisible) endDateVisible.addEventListener('change', syncCustomToHidden);
    form.addEventListener('submit', function() { syncCustomToHidden(); });
    sel.addEventListener('change', toggle);
    toggle();
})();
</script>
@endpush
@endsection
