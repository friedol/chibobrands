@extends('layouts.admin')
@section('title', 'Attendance Management')

@section('content')
<div class="att-page container-fluid py-3">

    {{-- ── Header ── --}}
    <div class="d-flex align-items-center gap-2 mb-3">
        <div class="att-header-icon">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div>
            <h5 class="fw-bold mb-0">Attendance Management</h5>
            <div class="att-sub-date">
                @if(isset($dateFrom) && isset($dateTo) && $dateFrom !== $dateTo)
                    {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                @else
                    {{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}
                @endif
            </div>
        </div>
        <div class="ms-auto">
            <a href="{{ route('admin.hr.attendance.report') }}" class="btn att-btn-report btn-sm">
                <i class="fas fa-chart-bar me-1"></i>Monthly Report
            </a>
        </div>
    </div>

    {{-- ── Filter Bar ── --}}
    <form id="filterForm" action="{{ route('admin.hr.attendance') }}" method="GET">
        <div class="att-filter-bar mb-4">
            <div class="att-filter-presets">
                <button type="button" class="att-preset {{ ($activePreset??'today')==='today'     ? 'active' : '' }}" onclick="applyPreset('today')">Today</button>
                <button type="button" class="att-preset {{ ($activePreset??'')==='yesterday'      ? 'active' : '' }}" onclick="applyPreset('yesterday')">Yesterday</button>
                <button type="button" class="att-preset {{ ($activePreset??'')==='week'           ? 'active' : '' }}" onclick="applyPreset('week')">This Week</button>
                <button type="button" class="att-preset {{ ($activePreset??'')==='month'          ? 'active' : '' }}" onclick="applyPreset('month')">This Month</button>
                <button type="button" class="att-preset {{ ($activePreset??'')==='year'           ? 'active' : '' }}" onclick="applyPreset('year')">This Year</button>
                <span class="att-filter-sep"></span>
                <span class="att-filter-label">Custom:</span>
                <input type="date" id="filterFrom" name="from" class="att-date-input"
                       value="{{ $dateFrom ?? $date }}" title="From">
                <span class="att-filter-dash">–</span>
                <input type="date" id="filterTo"   name="to"   class="att-date-input"
                       value="{{ $dateTo ?? $date }}" title="To">
                <button type="submit" class="att-preset att-preset-go">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
            </div>
        </div>
        {{-- hidden fallback for single-date routes --}}
        <input type="hidden" name="date" id="filterDate" value="{{ $date }}">
    </form>

    {{-- ── Stat Cards ── --}}
    <div class="row g-3 mb-4">
        @php $notRecorded = max(0, $total - ($present + $absent)); @endphp
        <div class="col-6 col-md-4 col-xl">
            <div class="attendance-stat-card">
                <div class="attendance-stat-icon text-primary"><i class="fas fa-users"></i></div>
                <div class="attendance-stat-val">{{ $total }}</div>
                <div class="attendance-stat-lbl">Total Active</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="attendance-stat-card">
                <div class="attendance-stat-icon text-success"><i class="fas fa-user-check"></i></div>
                <div class="attendance-stat-val">{{ $present }}</div>
                <div class="attendance-stat-lbl">Present</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="attendance-stat-card">
                <div class="attendance-stat-icon text-danger"><i class="fas fa-user-times"></i></div>
                <div class="attendance-stat-val">{{ $absent }}</div>
                <div class="attendance-stat-lbl">Absent</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="attendance-stat-card">
                <div class="attendance-stat-icon text-warning"><i class="fas fa-clock"></i></div>
                <div class="attendance-stat-val">{{ $late }}</div>
                <div class="attendance-stat-lbl">Late</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="attendance-stat-card">
                <div class="attendance-stat-icon text-secondary"><i class="fas fa-question-circle"></i></div>
                <div class="attendance-stat-val">{{ $notRecorded }}</div>
                <div class="attendance-stat-lbl">Not Recorded</div>
            </div>
        </div>
    </div>

    {{-- ── Table Card ── --}}
    <div class="att-card">
        <div class="att-card-head">
            <div>
                <span class="att-card-title">
                    <i class="fas fa-table me-2"></i>Daily Attendance
                </span>
                <span class="att-record-count ms-2">{{ $employees->count() }} employees</span>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" form="bulkNotesForm" class="btn att-btn-save btn-sm">
                    <i class="fas fa-save me-1"></i>Save Notes
                </button>
            </div>
        </div>

        <div class="att-card-body p-0">
            @if($employees->isEmpty())
                <div class="att-empty">
                    <i class="fas fa-user-slash fa-2x mb-3"></i>
                    <p class="mb-0">No active employees found.</p>
                </div>
            @else
            <form id="bulkNotesForm" action="{{ route('admin.hr.attendance.bulk') }}" method="POST">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <div class="table-responsive">
                    <table class="att-table">
                        <thead>
                            <tr class="att-thead-group">
                                <th rowspan="2" class="att-th-sticky">Employee</th>
                                <th rowspan="2">Department</th>
                                <th rowspan="2">Status</th>
                                <th colspan="3" class="att-group-header">Work Duration</th>
                                <th colspan="2" class="att-group-header">Late</th>
                                <th rowspan="2">Source</th>
                                <th rowspan="2">Notes</th>
                            </tr>
                            <tr class="att-thead-sub">
                                <th>Clock In</th>
                                <th>Clock Out</th>
                                <th>Duration</th>
                                <th>Times</th>
                                <th>Min</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employees as $emp)
                            @php
                                $rec         = $emp->today;
                                $status      = $rec?->status ?? 'absent';
                                $isLate      = $rec?->is_late;
                                $isBiometric = $rec?->method === 'biometric';
                                $hoursWorked = $rec?->hours_worked ?? null;
                                $lateMin     = $rec?->late_minutes ?? 0;

                                $durStr = '—';
                                if ($hoursWorked) {
                                    $totalMin = round(floatval($hoursWorked) * 60);
                                    $durStr   = floor($totalMin / 60) . ':' . str_pad($totalMin % 60, 2, '0', STR_PAD_LEFT);
                                }

                                $rowClass = match($status) {
                                    'late'     => 'att-row-late',
                                    'absent'   => 'att-row-absent',
                                    'on_leave' => 'att-row-leave',
                                    'half_day' => 'att-row-half',
                                    default    => '',
                                };

                                $statusLabels = [
                                    'present'  => ['Present',  'att-badge-present'],
                                    'late'     => ['Late',     'att-badge-late'],
                                    'absent'   => ['Absent',   'att-badge-absent'],
                                    'half_day' => ['Half Day', 'att-badge-half'],
                                    'on_leave' => ['On Leave', 'att-badge-leave'],
                                    'holiday'  => ['Holiday',  'att-badge-holiday'],
                                ];
                                [$statusLabel, $statusClass] = $statusLabels[$status] ?? ['—', 'att-badge-absent'];
                            @endphp
                            <tr class="{{ $rowClass }}">
                                {{-- Employee --}}
                                <td class="att-td-sticky att-emp-cell">
                                    <div class="att-emp-name">{{ $emp->full_name }}</div>
                                    <div class="att-emp-code">{{ $emp->employee_code }}</div>
                                </td>

                                {{-- Department --}}
                                <td class="att-td-dept">{{ $emp->department ?? '—' }}</td>

                                {{-- Status (auto) --}}
                                <td class="text-center">
                                    <span class="att-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>

                                {{-- Clock In (from device, read-only) --}}
                                <td class="att-td-time">
                                    {{ $rec?->clock_in ? \Carbon\Carbon::parse($rec->clock_in)->format('H:i') : '—' }}
                                </td>

                                {{-- Clock Out (from device, read-only) --}}
                                <td class="att-td-time">
                                    {{ $rec?->clock_out ? \Carbon\Carbon::parse($rec->clock_out)->format('H:i') : '—' }}
                                </td>

                                {{-- Duration --}}
                                <td class="att-td-dur {{ $hoursWorked ? 'att-td-dur-val' : '' }}">
                                    {{ $durStr }}
                                </td>

                                {{-- Late Times --}}
                                <td class="att-td-num {{ $isLate ? 'att-td-late' : 'att-td-zero' }}">
                                    {{ $isLate ? 1 : 0 }}
                                </td>

                                {{-- Late Min --}}
                                <td class="att-td-num {{ $lateMin > 0 ? 'att-td-late' : 'att-td-zero' }}">
                                    {{ $lateMin > 0 ? $lateMin : '—' }}
                                </td>

                                {{-- Source --}}
                                <td class="text-center">
                                    @if($isBiometric)
                                        <span class="att-badge att-badge-bio">
                                            <i class="fas fa-fingerprint me-1"></i>Biometric
                                        </span>
                                    @elseif($rec)
                                        <span class="att-badge att-badge-manual">
                                            <i class="fas fa-pen me-1"></i>Manual
                                        </span>
                                    @else
                                        <span class="att-td-zero">—</span>
                                    @endif
                                </td>

                                {{-- Notes (only editable field) --}}
                                <td>
                                    <input type="text" name="records[{{ $emp->id }}][notes]"
                                           class="att-notes-input"
                                           placeholder="Add note…"
                                           value="{{ $rec?->notes ?? '' }}">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr class="att-tfoot">
                                <td colspan="2" class="text-end pe-3"><strong>Totals</strong></td>
                                <td></td>
                                <td colspan="3"></td>
                                <td class="att-td-num"><strong>{{ $late }}</strong></td>
                                <td></td>
                                <td colspan="2">
                                    <span class="att-td-zero small">
                                        {{ $present }} present &middot; {{ $absent }} absent &middot; {{ $late }} late
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </form>
            @endif
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    // Date helpers
    function fmt(d) {
        return d.toISOString().split('T')[0];
    }
    function startOf(unit) {
        var d = new Date();
        if (unit === 'week') {
            var day = d.getDay();                    // 0=Sun
            d.setDate(d.getDate() - (day === 0 ? 6 : day - 1)); // Mon
        } else if (unit === 'month') {
            d.setDate(1);
        } else if (unit === 'year') {
            d.setMonth(0, 1);
        }
        return d;
    }

    window.applyPreset = function (preset) {
        var today = new Date();
        var from, to;
        if (preset === 'today') {
            from = to = fmt(today);
        } else if (preset === 'yesterday') {
            var y = new Date(today); y.setDate(y.getDate() - 1);
            from = to = fmt(y);
        } else if (preset === 'week') {
            var s = startOf('week');
            var e = new Date(s); e.setDate(s.getDate() + 6);
            from = fmt(s); to = fmt(e);
        } else if (preset === 'month') {
            var s2 = startOf('month');
            var e2 = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            from = fmt(s2); to = fmt(e2);
        } else if (preset === 'year') {
            from = today.getFullYear() + '-01-01';
            to   = today.getFullYear() + '-12-31';
        }
        document.getElementById('filterFrom').value = from;
        document.getElementById('filterTo').value   = to;
        document.getElementById('filterDate').value = from;

        // Mark active preset visually
        document.querySelectorAll('.att-preset').forEach(function(b) { b.classList.remove('active'); });
        event.currentTarget.classList.add('active');

        document.getElementById('filterForm').submit();
    };

    // Clicking custom date inputs clears preset active state
    ['filterFrom', 'filterTo'].forEach(function(id) {
        document.getElementById(id).addEventListener('change', function() {
            document.querySelectorAll('.att-preset').forEach(function(b) { b.classList.remove('active'); });
        });
    });
})();
</script>
@endpush

@push('styles')
<style>
/* ══ Page base ══ */
.att-page {
    --att-blue:   #1A54C8;
    --att-green:  #0A7A52;
    --att-red:    #C02020;
    --att-amber:  #9B6600;
    --att-grey:   #5B6F9A;
    --att-gold:   #B87000;
    --att-border: #E2E8F2;
    --att-surface:#FFFFFF;
    font-size: 13px;
}

/* ── Header ── */
.att-header-icon {
    width: 38px; height: 38px; border-radius: 10px;
    background: linear-gradient(135deg, #1A54C8, #B87000);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 16px; flex-shrink: 0;
}
.att-sub-date { font-size: 12px; color: #5B6F9A; margin-top: 2px; }
.att-btn-report { background: #F4F7FF; border: 1px solid var(--att-border); color: #5B6F9A; font-weight: 600; border-radius: 6px; }
.att-btn-report:hover { background: #E8EEFA; color: var(--att-blue); }

/* ── Filter bar ── */
.att-filter-bar {
    background: #fff; border: 1px solid var(--att-border);
    border-radius: 10px; padding: 10px 14px;
}
.att-filter-presets {
    display: flex; flex-wrap: wrap; align-items: center; gap: 6px;
}
.att-preset {
    padding: 5px 13px; border-radius: 6px; border: 1px solid var(--att-border);
    background: #F4F7FF; color: #5B6F9A; font-size: 12px; font-weight: 600;
    cursor: pointer; transition: all .15s; white-space: nowrap;
}
.att-preset:hover  { background: #E8EEFA; color: var(--att-blue); border-color: var(--att-blue); }
.att-preset.active { background: var(--att-blue); color: #fff; border-color: var(--att-blue); }
.att-preset-go     { background: var(--att-blue); color: #fff; border-color: var(--att-blue); }
.att-preset-go:hover { background: #1344A0; }
.att-filter-sep    { width: 1px; height: 22px; background: var(--att-border); flex-shrink: 0; }
.att-filter-label  { font-size: 11px; color: #94A3B8; font-weight: 600; white-space: nowrap; }
.att-filter-dash   { color: #94A3B8; font-size: 12px; }
.att-date-input    { border: 1px solid var(--att-border); border-radius: 6px; padding: 5px 8px; font-size: 12px; color: #28395E; background: #F8FAFF; outline: none; transition: border-color .15s; }
.att-date-input:focus { border-color: var(--att-blue); }

/* ── Stat cards (original simple style) ── */
.attendance-stat-card {
    background: #fff; border: 1px solid #e3e6f0;
    border-radius: 8px; padding: 16px 20px;
    display: flex; flex-direction: column; align-items: center;
    text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,.04);
}
.attendance-stat-icon { font-size: 1.6rem; margin-bottom: 8px; }
.attendance-stat-val  { font-size: 2rem; font-weight: 700; color: #2d3748; line-height: 1; margin-bottom: 4px; }
.attendance-stat-lbl  { font-size: 13px; color: #718096; font-weight: 500; }

/* ── Table card ── */
.att-card { background: var(--att-surface); border: 1px solid var(--att-border); border-radius: 12px; overflow: hidden; }
.att-card-head {
    display: flex; justify-content: space-between; align-items: center;
    padding: 13px 18px; border-bottom: 1px solid var(--att-border);
    background: #FAFBFF; flex-wrap: wrap; gap: 10px;
}
.att-card-title  { font-weight: 700; color: #0D1A36; font-size: 13.5px; }
.att-record-count{ font-size: 11px; color: #94A3B8; font-weight: 500; }
.att-btn-save    { background: var(--att-blue); border: none; color: #fff; font-weight: 700; border-radius: 6px; }
.att-btn-save:hover { background: #1344A0; }
.att-empty { padding: 60px 20px; text-align: center; color: #94A3B8; }

/* ── Table ── */
.att-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.att-table th, .att-table td {
    padding: 0; border-bottom: 1px solid var(--att-border);
    border-right: 1px solid #EEF2FC; vertical-align: middle;
}
.att-table th:last-child, .att-table td:last-child { border-right: none; }

.att-thead-group th, .att-thead-sub th {
    background: #F4F7FF; padding: 7px 10px;
    font-size: 10px; font-weight: 700; letter-spacing: .07em;
    text-transform: uppercase; color: #7E93BD;
    white-space: nowrap; text-align: center;
}
.att-group-header {
    background: #EEF3FF !important;
    border-left: 2px solid #D4DEEF !important;
    color: var(--att-blue) !important;
}
.att-thead-sub th { background: #F9FAFE !important; font-size: 9.5px; color: #94A3B8; }

.att-th-sticky, .att-td-sticky {
    position: sticky; left: 0; z-index: 2;
    background: var(--att-surface) !important;
    border-right: 2px solid var(--att-border) !important;
}
.att-thead-group .att-th-sticky { background: #F4F7FF !important; }

.att-table tbody tr:hover td { background: #F8FAFF; }
.att-table tbody tr:hover .att-td-sticky { background: #F0F4FF !important; }

.att-row-late   td { background: rgba(192,32,32,.03); }
.att-row-absent td { background: rgba(192,32,32,.02); }
.att-row-leave  td { background: rgba(26,84,200,.03); }
.att-row-half   td { background: rgba(10,122,82,.02); }

/* Employee cell */
.att-emp-cell { padding: 10px 12px !important; }
.att-emp-name { font-weight: 700; color: #0D1A36; }
.att-emp-code { font-size: 10.5px; font-family: monospace; color: var(--att-blue); margin-top: 2px; }

/* Department */
.att-td-dept { padding: 0 10px; color: #5B6F9A; white-space: nowrap; max-width: 160px; overflow: hidden; text-overflow: ellipsis; }

/* Clock time (read-only) */
.att-td-time { padding: 0 10px; text-align: center; font-family: monospace; font-size: 12.5px; color: #28395E; font-weight: 600; white-space: nowrap; }

/* Duration */
.att-td-dur { padding: 0 10px; text-align: center; font-family: monospace; color: #94A3B8; white-space: nowrap; }
.att-td-dur-val { color: var(--att-gold); font-weight: 700; }

/* Numeric */
.att-td-num  { padding: 0 10px; text-align: center; font-family: monospace; white-space: nowrap; }
.att-td-late { color: var(--att-red); font-weight: 700; }
.att-td-zero { color: #CBD5E1; }

/* Status + source badges */
.att-badge { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 99px; font-size: 11px; font-weight: 700; white-space: nowrap; }
.att-badge-present { background: rgba(10,122,82,.12);  color: #0A7A52; }
.att-badge-late    { background: rgba(192,32,32,.1);   color: #C02020; }
.att-badge-absent  { background: rgba(192,32,32,.07);  color: #C02020; }
.att-badge-half    { background: rgba(26,84,200,.1);   color: var(--att-blue); }
.att-badge-leave   { background: rgba(155,102,0,.1);   color: var(--att-amber); }
.att-badge-holiday { background: rgba(91,111,154,.1);  color: var(--att-grey); }
.att-badge-bio     { background: rgba(26,84,200,.1);   color: var(--att-blue); }
.att-badge-manual  { background: rgba(91,111,154,.1);  color: var(--att-grey); }

/* Notes */
.att-notes-input {
    border: 1.5px solid transparent; border-radius: 6px;
    padding: 5px 8px; font-size: 12px; color: #5B6F9A;
    background: transparent; outline: none; width: 100%; min-width: 140px; margin: 8px 6px;
    transition: border-color .18s, background .18s;
}
.att-notes-input:focus { border-color: var(--att-border); background: #F8FAFF; }
.att-notes-input::placeholder { color: #CBD5E1; }

/* Footer */
.att-tfoot td { background: #F4F7FF; padding: 9px 10px; font-size: 12px; color: #28395E; border-top: 2px solid var(--att-border); }

@media (max-width: 768px) {
    .att-filter-presets { gap: 4px; }
    .att-preset { padding: 4px 9px; font-size: 11px; }
    .att-filter-sep { display: none; }
}
</style>
@endpush
