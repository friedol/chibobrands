@extends('layouts.admin')
@section('title', 'Monthly Attendance Report')

@section('content')
<div class="container-fluid py-3">
    <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
            <h4 class="fw-bold mb-0"><i class="fas fa-chart-bar text-info me-2"></i>Monthly Attendance Report</h4>
            <p class="text-muted small mb-0">
                {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }} |
                Working Days: <strong>{{ $workingDays }}</strong>
            </p>
        </div>
        <div class="col-lg-5 text-lg-end mt-2 mt-lg-0 d-flex flex-wrap justify-content-lg-end gap-2">
            <form action="{{ route('admin.hr.attendance.report') }}" method="GET" class="d-flex gap-2 align-items-end">
                <div>
                    <select name="month" class="form-select form-select-sm">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <select name="year" class="form-select form-select-sm">
                        @foreach([now()->year - 1, now()->year] as $y)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Go</button>
            </form>
            <x-report-export-menu
                :print-url="route('admin.hr.attendance.report.print', ['month' => $month, 'year' => $year])"
                :pdf-url="route('admin.hr.attendance.report.pdf', ['month' => $month, 'year' => $year])"
                :excel-url="route('admin.hr.attendance.report.excel', ['month' => $month, 'year' => $year])"
                print-target="_blank"
                label="Export"
            />
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Department</th>
                            <th class="text-center text-success">Present</th>
                            <th class="text-center text-danger">Absent</th>
                            <th class="text-center text-warning">Late</th>
                            <th class="text-center text-info">On Leave</th>
                            <th class="text-center">Hours Worked</th>
                            <th class="text-center">Attendance %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $i => $emp)
                        @php
                            $attPct = $workingDays > 0 ? round((($emp->present + $emp->late) / $workingDays) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $emp->full_name }}</td>
                            <td class="text-muted">{{ $emp->department ?? '—' }}</td>
                            <td class="text-center fw-bold text-success">{{ $emp->present }}</td>
                            <td class="text-center fw-bold text-danger">{{ $emp->absent }}</td>
                            <td class="text-center fw-bold text-warning">{{ $emp->late }}</td>
                            <td class="text-center text-info">{{ $emp->on_leave }}</td>
                            <td class="text-center">{{ number_format($emp->total_hours, 1) }}h</td>
                            <td class="text-center">
                                <div class="d-flex align-items-center gap-1">
                                    <div class="progress flex-grow-1" style="height:8px">
                                        <div class="progress-bar bg-{{ $attPct >= 90 ? 'success' : ($attPct >= 70 ? 'warning' : 'danger') }}"
                                             style="width:{{ $attPct }}%"></div>
                                    </div>
                                    <small>{{ $attPct }}%</small>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
