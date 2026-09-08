@extends('layouts.admin')

@section('title', 'Department Sales Report - CHIBO BRAND')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-building text-danger me-2"></i>Department Sales Report
            </h4>
            <small class="text-muted">
                {{ $startDate->format('d M Y') }} &ndash; {{ $endDate->format('d M Y') }}
                &nbsp;&middot;&nbsp; {{ $periodLabel }}
                @if($departmentId)
                    @php $selDept = $allDepartments->firstWhere('id', $departmentId); $selName = $selDept ? trim(str_replace(['CHIBO-','CHIBO –'], '', $selDept->name)) : ''; if($selName === 'MAIN') $selName = 'CHIBO MAIN'; @endphp
                    &nbsp;&middot;&nbsp; {{ $selName }}
                @endif
            </small>
        </div>
        <x-report-export-menu
            :print-url="route('admin.reports.department_sales.print', request()->all())"
            :pdf-url="route('admin.reports.department_sales.pdf', request()->all())"
            :excel-url="route('admin.reports.department_sales.excel', request()->all())"
        />
    </div>

    {{-- Filter card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light">
            <form method="GET" action="{{ route('admin.reports.department_sales') }}" id="periodForm" data-no-global-handler class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold mb-1">Department</label>
                    <select name="department_id" id="deptSelect" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        @foreach($allDepartments as $dept)
                            @php $dname = trim(str_replace(['CHIBO-','CHIBO –'], '', $dept->name)); if($dname==='MAIN') $dname='CHIBO MAIN'; @endphp
                            <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>{{ $dname }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold mb-1">Period</label>
                    <select name="period" id="periodSelect" class="form-select form-select-sm">
                        <option value="today"     {{ $period === 'today'     ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ $period === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="week"      {{ $period === 'week'      ? 'selected' : '' }}>This Week</option>
                        <option value="month"     {{ $period === 'month'     ? 'selected' : '' }}>This Month</option>
                        <option value="year"      {{ $period === 'year'      ? 'selected' : '' }}>This Year</option>
                        <option value="custom"    {{ $period === 'custom'    ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>

                <div class="col-6 col-md-2 {{ $period === 'custom' ? '' : 'd-none' }}" id="dateFromWrap">
                    <label class="form-label small fw-bold mb-1">Date From</label>
                    <input type="date" name="date_from" id="dateFrom" class="form-control form-control-sm" value="{{ $dateFrom ?? $startDate->format('Y-m-d') }}">
                </div>

                <div class="col-6 col-md-2 {{ $period === 'custom' ? '' : 'd-none' }}" id="dateToWrap">
                    <label class="form-label small fw-bold mb-1">Date To</label>
                    <input type="date" name="date_to" id="dateTo" class="form-control form-control-sm" value="{{ $dateTo ?? $endDate->format('Y-m-d') }}">
                </div>

                <div class="col-6 col-md-2 d-flex gap-2">
                    <button class="btn btn-danger btn-sm w-100" type="submit">Apply</button>
                    @if($period !== 'month' || $departmentId)
                        <a href="{{ route('admin.reports.department_sales') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
            <small class="text-muted d-block mt-2">
                <i class="fas fa-circle-info me-1"></i>Filters apply to department-level sales only &mdash; not combined with individual salesperson figures.
            </small>
        </div>
    </div>

    {{-- Stats summary --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h6 class="fw-bold mb-0"><i class="fas fa-bullseye text-danger me-2"></i>Target Achievement Summary</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach ($reportData as $data)
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="border rounded p-3 h-100">
                        <div class="small text-muted text-uppercase fw-bold">{{ $data['display_name'] }}</div>
                        <div class="fs-4 fw-bold {{ $data['percentage'] >= 100 ? 'text-success' : ($data['percentage'] >= 60 ? 'text-primary' : 'text-danger') }}">{{ $data['percentage'] }}%</div>
                        <div class="small text-muted">{{ number_format($data['total_sales']) }} / {{ number_format($data['target_amount']) }}</div>
                    </div>
                </div>
                @endforeach
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="border border-dark rounded p-3 h-100">
                        <div class="small text-muted text-uppercase fw-bold">Overall Total</div>
                        <div class="fs-4 fw-bold text-dark">{{ $overallPercentage }}%</div>
                        <div class="small text-muted">{{ number_format($overallTotalSales) }} / {{ number_format($overallTotalTarget) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed breakdowns per department --}}
    @foreach ($reportData as $data)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h6 class="fw-bold mb-0"><i class="fas fa-store text-dark me-2"></i>{{ $data['display_name'] }}</h6>
                @if($data['target_note'])
                    <small class="text-muted"><i class="fas fa-calculator me-1"></i>{{ $data['target_note'] }}</small>
                @endif
            </div>
            <span class="badge {{ $data['percentage'] >= 100 ? 'bg-success' : ($data['percentage'] >= 60 ? 'bg-primary' : 'bg-danger') }}">{{ $data['percentage'] }}% of target</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width:5%;">NO</th>
                            <th style="width:45%;">Task Description</th>
                            <th style="width:20%;">Customer</th>
                            <th class="text-center" style="width:10%;">Qty</th>
                            <th class="text-end" style="width:20%;">Revenue (TZS)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['tasks'] as $index => $task)
                        <tr>
                            <td class="text-center text-muted">{{ $index + 1 }}</td>
                            <td>
                                <span class="fw-semibold d-block">{{ $task['title'] }}</span>
                                @if($task['description'])
                                    <span class="small text-muted">{{ Str::limit($task['description'], 100) }}</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $task['customer_name'] }}</td>
                            <td class="text-center fw-semibold">{{ number_format($task['qty']) }}</td>
                            <td class="text-end fw-semibold">{{ number_format($task['price']) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No sales recorded in this period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="3"></td>
                            <td class="text-end fw-bold">TOTAL</td>
                            <td class="text-end fw-bold">{{ number_format($data['total_sales']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endforeach

    {{-- Grand total --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body text-end">
            <div class="fw-bold text-dark fs-6 mb-1">
                GRAND TOTAL: <span class="text-dark">{{ number_format($overallTotalSales) }} TZS</span>
            </div>
            <div class="fw-bold fs-5 {{ $overallPercentage >= 100 ? 'text-success' : 'text-primary' }}">OVERALL ACHIEVEMENT: {{ $overallPercentage }}%</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const periodSelect = document.getElementById('periodSelect');
    const dateFromWrap  = document.getElementById('dateFromWrap');
    const dateToWrap    = document.getElementById('dateToWrap');

    if (periodSelect) {
        periodSelect.addEventListener('change', function () {
            if (this.value === 'custom') {
                dateFromWrap.classList.remove('d-none');
                dateToWrap.classList.remove('d-none');
            } else {
                dateFromWrap.classList.add('d-none');
                dateToWrap.classList.add('d-none');
                this.form.submit();
            }
        });
    }
});
</script>
@endpush
@endsection
