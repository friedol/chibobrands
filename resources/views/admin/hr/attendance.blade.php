@extends('layouts.admin')
@section('title', 'Attendance Management')

@section('content')
<div class="container-fluid py-3">

    {{-- ── Header ── --}}
    <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
            <h4 class="fw-bold mb-0"><i class="fas fa-calendar-check text-info me-2"></i>Attendance Management</h4>
            <p class="text-muted small mb-0">
                Date: <strong>{{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}</strong>
            </p>
        </div>
        <div class="col-lg-5 text-lg-end mt-2 mt-lg-0 d-flex flex-wrap justify-content-lg-end gap-2">
            <form action="{{ route('admin.hr.attendance') }}" method="GET" class="d-flex gap-2 align-items-end">
                <div>
                    <label class="form-label fw-bold x-small text-uppercase mb-1">Select Date</label>
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}"
                           onchange="this.form.submit()">
                </div>
            </form>
            <a href="{{ route('admin.hr.attendance.report') }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fas fa-chart-bar me-1"></i>Monthly Report
            </a>
        </div>
    </div>

    {{-- ── Summary ── --}}
    <div class="row g-3 mb-4">
        @php
            $summaries = [
                ['label'=>'Total Active', 'value'=>$total, 'color'=>'primary', 'icon'=>'fas fa-users'],
                ['label'=>'Present', 'value'=>$present, 'color'=>'success', 'icon'=>'fas fa-user-check'],
                ['label'=>'Absent', 'value'=>$absent, 'color'=>'danger', 'icon'=>'fas fa-user-times'],
                ['label'=>'Late', 'value'=>$late, 'color'=>'warning', 'icon'=>'fas fa-clock'],
                ['label'=>'Not Recorded', 'value'=>max(0, $total - ($present + $absent)), 'color'=>'secondary', 'icon'=>'fas fa-question-circle'],
            ];
        @endphp
        @foreach($summaries as $s)
        <div class="col-6 col-md-{{ $loop->count > 4 ? '2' : '3' }}">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center py-3">
                    <div class="rounded-circle bg-{{ $s['color'] }} bg-opacity-10 p-3 me-3">
                        <i class="{{ $s['icon'] }} text-{{ $s['color'] }}"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold">{{ $s['value'] }}</div>
                        <div class="x-small text-muted fw-semibold">{{ $s['label'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Bulk Attendance Form ── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
            <span class="fw-bold">Daily Attendance — {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</span>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-success" onclick="markAll('present')">
                    <i class="fas fa-check me-1"></i>Mark All Present
                </button>
                <button type="submit" form="bulkAttendanceForm" class="btn btn-sm btn-primary px-3">
                    <i class="fas fa-save me-1"></i>Save Attendance
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            @if($employees->isEmpty())
                <div class="text-center py-5 text-muted">No active employees found.</div>
            @else
            <form id="bulkAttendanceForm" action="{{ route('admin.hr.attendance.bulk') }}" method="POST">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-dark">
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Clock In</th>
                                <th>Clock Out</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employees as $emp)
                            @php $rec = $emp->today; @endphp
                            <tr class="{{ $rec?->is_late ? 'table-warning' : '' }}">
                                <td>
                                    <div class="fw-semibold">{{ $emp->full_name }}</div>
                                    <div class="text-muted x-small">{{ $emp->employee_code }}</div>
                                </td>
                                <td class="text-muted">{{ $emp->department ?? '—' }}</td>
                                <td>
                                    <select name="records[{{ $emp->id }}][status]"
                                            class="form-select form-select-sm status-select"
                                            data-emp="{{ $emp->id }}"
                                            style="min-width:120px">
                                        @foreach(['present'=>'Present','absent'=>'Absent','late'=>'Late','half_day'=>'Half Day','on_leave'=>'On Leave','holiday'=>'Holiday'] as $val => $lbl)
                                            <option value="{{ $val }}" {{ ($rec?->status ?? 'absent') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="time" name="records[{{ $emp->id }}][clock_in]"
                                           class="form-control form-control-sm" style="min-width:100px"
                                           value="{{ $rec?->clock_in ?? '' }}">
                                </td>
                                <td>
                                    <input type="time" name="records[{{ $emp->id }}][clock_out]"
                                           class="form-control form-control-sm" style="min-width:100px"
                                           value="{{ $rec?->clock_out ?? '' }}">
                                </td>
                                <td>
                                    <input type="text" name="records[{{ $emp->id }}][notes]"
                                           class="form-control form-control-sm"
                                           placeholder="Optional note..." value="{{ $rec?->notes ?? '' }}">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
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
function markAll(status) {
    document.querySelectorAll('.status-select').forEach(sel => sel.value = status);
}
</script>
@endpush
