@extends('layouts.admin')

@section('title', 'My Sales Report - CHIBO BRANDS')

@push('styles')
<style>
    .filter-select {
        background-color: #f8f9fa;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        padding: 5px 10px;
        height: 32px;
        cursor: pointer;
    }
    .filter-select:focus { border-color: #f59e0b; box-shadow: 0 0 0 2px rgba(245,158,11,.15); outline: none; }
    .report-table th { font-size: 11px; text-transform: uppercase; letter-spacing: 0.4px; background: #1e293b; color: #fff; padding: 10px 10px; white-space: nowrap; }
    .report-table td { font-size: 12px; vertical-align: middle; padding: 9px 10px; }
    .status-badge { font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 20px; text-transform: capitalize; }
    .summary-block { border-left: 3px solid #f59e0b; padding: 10px 16px; background: #fffbeb; border-radius: 0 8px 8px 0; }
    .summary-block .s-row { display: flex; justify-content: space-between; font-size: 12.5px; padding: 3px 0; border-bottom: 1px solid rgba(0,0,0,0.05); }
    .summary-block .s-row:last-child { border-bottom: none; }
    .summary-block .s-label { color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.3px; }
    .summary-block .s-val { font-weight: 700; color: #1e293b; }
    .summary-total { border-top: 2px solid #1e293b; margin-top: 8px; padding-top: 8px; font-size: 13px; }
    @media (max-width:575px) {
        .filter-row { flex-wrap: wrap !important; gap: 6px !important; }
        .filter-row > * { flex: 1 1 calc(50% - 4px); min-width: 0; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 pt-2 pb-4">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Seller Activity Report</h5>
            <p class="text-muted x-small mb-0"><i class="fas fa-user me-1"></i>{{ $saler->name }} &nbsp;·&nbsp; {{ $periodLabel }}</p>
        </div>
        <x-report-export-menu
            id="printBtn"
            :print-url="route('admin.saler.sales-report.print', array_merge(request()->all(), ['saler_id' => $saler->id, 'period' => $period, 'notice' => $notice]))"
            :pdf-url="route('admin.saler.sales-report.pdf', array_merge(request()->all(), ['saler_id' => $saler->id, 'period' => $period, 'notice' => $notice]))"
            :excel-url="route('admin.saler.sales-report.excel', array_merge(request()->all(), ['saler_id' => $saler->id, 'period' => $period, 'notice' => $notice]))"
            label="Export"
        />
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body px-3 py-2">
            <form method="GET" action="{{ route('admin.saler.sales-report') }}" id="reportForm" data-no-global-handler>
                <input type="hidden" name="notice" id="noticeHidden" value="{{ $notice }}">
                <div class="filter-row d-flex align-items-center gap-2">

                    @if(auth()->user()->role !== 'saler')
                        <select name="saler_id" class="filter-select" style="min-width:160px;" onchange="this.form.submit()">
                            @foreach($allSalers as $s)
                                <option value="{{ $s->id }}" {{ $saler->id == $s->id ? 'selected' : '' }}>{{ $s->name }} ({{ ucfirst($s->role) }})</option>
                            @endforeach
                        </select>
                    @endif

                    <select name="period" id="periodSelect" class="filter-select" style="min-width:140px;">
                        @foreach(['today'=>'Today','yesterday'=>'Yesterday','week'=>'This Week','month'=>'This Month','year'=>'This Year','custom'=>'Custom Range'] as $val => $label)
                            <option value="{{ $val }}" {{ $period === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>

                    <input type="date" name="start_date" id="startDate"
                           class="filter-select {{ $period === 'custom' ? '' : 'd-none' }}"
                           style="min-width:120px;" value="{{ request('start_date', $from->format('Y-m-d')) }}">

                    <input type="date" name="end_date" id="endDate"
                           class="filter-select {{ $period === 'custom' ? '' : 'd-none' }}"
                           style="min-width:120px;" value="{{ request('end_date', $to->format('Y-m-d')) }}">

                    <button type="submit" class="btn btn-warning btn-sm fw-bold rounded-3 px-3" style="font-size:12px;height:32px;color:#fff;">
                        <i class="fas fa-filter me-1"></i>Apply
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered report-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name of Client</th>
                            <th>Phone Number</th>
                            <th>Source of Leads</th>
                            <th>Follow Up Date</th>
                            <th>Status of Follow Up</th>
                            <th>Product Asked</th>
                            <th>Products Ordered</th>
                            <th>Amount Paid</th>
                            <th>Status of Work</th>
                            <th>Delivery Status</th>
                            <th>After Sale Feedback</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $row)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td class="fw-bold">{{ $row->client_name ?: '—' }}</td>
                            <td>{{ $row->phone ?: '—' }}</td>
                            <td>
                                @if($row->source)
                                    <span class="status-badge bg-primary bg-opacity-10 text-primary">{{ ucfirst($row->source) }}</span>
                                @else —
                                @endif
                            </td>
                            <td>{{ $row->follow_up_date ? \Carbon\Carbon::parse($row->follow_up_date)->format('M d, Y') : '—' }}</td>
                            <td>
                                @php
                                    $fcolor = match($row->follow_up_status) {
                                        'converted' => 'success', 'lost' => 'danger', 'pending' => 'warning', default => 'secondary'
                                    };
                                @endphp
                                <span class="status-badge bg-{{ $fcolor }} bg-opacity-10 text-{{ $fcolor }}">
                                    {{ ucfirst($row->follow_up_status ?? '—') }}
                                </span>
                            </td>
                            <td>{{ $row->product_asked ?: '—' }}</td>
                            <td>{{ $row->product_ordered ?: '—' }}</td>
                            <td>
                                @if($row->amount_paid !== null)
                                    <span class="fw-bold text-success">TZS {{ number_format($row->amount_paid) }}</span>
                                @else —
                                @endif
                            </td>
                            <td>
                                @if($row->work_status)
                                    @php
                                        $wcolor = match($row->work_status) {
                                            'completed','super_completed' => 'success',
                                            'in_progress' => 'primary',
                                            'cancelled' => 'danger',
                                            'delivered' => 'info',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="status-badge bg-{{ $wcolor }} bg-opacity-10 text-{{ $wcolor }}">
                                        {{ ucwords(str_replace('_', ' ', $row->work_status)) }}
                                    </span>
                                @else —
                                @endif
                            </td>
                            <td>
                                @if($row->delivery_status)
                                    <span class="status-badge bg-info bg-opacity-10 text-info">
                                        {{ ucwords(str_replace('_', ' ', $row->delivery_status)) }}
                                    </span>
                                @else —
                                @endif
                            </td>
                            <td class="text-muted" style="max-width:160px;">{{ $row->feedback ?: '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                                No leads data found for this period.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── SUMMARY + NOTICE ── --}}
    @if(!$rows->isEmpty())
    <div class="row g-4 mt-2">

        {{-- Summary by source --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3">
                    <h6 class="fw-bold text-uppercase mb-3" style="font-size:11px;letter-spacing:.5px;color:#64748b;">
                        <i class="fas fa-chart-bar me-1 text-warning"></i>Summary
                    </h6>

                    @php $grandPaid = 0; @endphp
                    @foreach($summary as $grp)
                    @php $grandPaid += $grp['amount_paid']; @endphp
                    <div class="summary-block mb-3">
                        <div class="fw-bold text-uppercase mb-2" style="font-size:11px;color:#1e293b;letter-spacing:.5px;">
                            {{ $grp['label'] }}
                        </div>
                        <div class="s-row">
                            <span class="s-label">{{ $grp['label'] }}</span>
                            <span class="s-val">{{ $grp['total'] }}</span>
                        </div>
                        <div class="s-row">
                            <span class="s-label">Total {{ $grp['label'] }} Paid</span>
                            <span class="s-val text-success">{{ $grp['paid_count'] }}</span>
                        </div>
                        <div class="s-row">
                            <span class="s-label">Total {{ $grp['label'] }} Unpaid</span>
                            <span class="s-val text-danger">{{ $grp['unpaid_count'] }}</span>
                        </div>
                        <div class="s-row">
                            <span class="s-label">{{ $grp['label'] }} Amount Paid</span>
                            <span class="s-val text-primary">TZS {{ number_format($grp['amount_paid']) }}</span>
                        </div>
                    </div>
                    @endforeach

                    <div class="summary-block summary-total d-flex justify-content-between">
                        <span class="s-label" style="font-size:12px;">TOTAL AMOUNT PAID</span>
                        <span class="s-val" style="font-size:14px;color:#d63031;">TZS {{ number_format($grandPaid) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary notice --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex flex-column">
                    <h6 class="fw-bold text-uppercase mb-2" style="font-size:11px;letter-spacing:.5px;color:#64748b;">
                        <i class="fas fa-sticky-note me-1 text-warning"></i>Summary Notice
                    </h6>
                    <textarea id="noticeArea" rows="8"
                        class="form-control flex-grow-1"
                        style="font-size:13px;resize:none;border:1.5px solid #e2e8f0;border-radius:8px;background:#fffbeb;"
                        placeholder="Add a summary note, observations, or remarks for this report…">{{ $notice }}</textarea>
                    <div class="text-end mt-2">
                        <span class="x-small text-muted">This notice will appear on the printed report.</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ps = document.getElementById('periodSelect');
    const s  = document.getElementById('startDate');
    const e  = document.getElementById('endDate');
    const noticeArea   = document.getElementById('noticeArea');
    const noticeHidden = document.getElementById('noticeHidden');
    const exportLinks  = document.querySelectorAll('ul[aria-labelledby="printBtn"] a.dropdown-item');

    if (ps) {
        ps.addEventListener('change', function () {
            if (this.value === 'custom') {
                s.classList.remove('d-none');
                e.classList.remove('d-none');
            } else {
                s.classList.add('d-none');
                e.classList.add('d-none');
                this.form.submit();
            }
        });
    }

    // Keep notice in sync with Print/PDF/Excel export links
    if (noticeArea && exportLinks.length) {
        noticeArea.addEventListener('input', function () {
            exportLinks.forEach(function (link) {
                const url = new URL(link.href);
                url.searchParams.set('notice', noticeArea.value);
                link.href = url.toString();
            });
            if (noticeHidden) noticeHidden.value = this.value;
        });
    }
});
</script>
@endpush
