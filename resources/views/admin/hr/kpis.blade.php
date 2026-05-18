@extends('layouts.admin')
@section('title', 'KPI Evaluations')

@section('content')
<div class="container-fluid py-3">

    <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
            <h4 class="fw-bold mb-0"><i class="fas fa-chart-bar text-success me-2"></i>KPI & Performance Evaluations</h4>
        </div>
        <div class="col-lg-5 text-lg-end mt-2 mt-lg-0 d-flex flex-wrap justify-content-lg-end gap-2">
            <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addKpiModal">
                <i class="fas fa-plus me-1"></i>New Evaluation
            </button>
            <a href="{{ route('admin.hr.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fas fa-arrow-left me-1"></i>Back to HR
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body bg-light p-3">
            <form action="{{ route('admin.hr.kpis') }}" method="GET" class="row g-2">
                <div class="col-6 col-md-4">
                    <select name="employee_id" class="form-select form-select-sm">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="period_type" class="form-select form-select-sm">
                        <option value="all">All Periods</option>
                        <option value="daily"   {{ request('period_type') === 'daily'   ? 'selected' : '' }}>Daily</option>
                        <option value="weekly"  {{ request('period_type') === 'weekly'  ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ request('period_type') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('admin.hr.kpis') }}" class="btn btn-outline-secondary btn-sm ms-1">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- KPI Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($kpis->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-chart-line fa-3x mb-3"></i>
                    <p>No KPI evaluations found.</p>
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-dark">
                        <tr>
                            <th>Employee</th>
                            <th>Period</th>
                            <th>Range</th>
                            <th class="text-center">Attend.</th>
                            <th class="text-center">Produc.</th>
                            <th class="text-center">Quality</th>
                            <th class="text-center">Punctual.</th>
                            <th class="text-center">Teamwork</th>
                            <th class="text-center">Overall</th>
                            <th class="text-center">Grade</th>
                            <th>Evaluated By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kpis as $kpi)
                        <tr>
                            <td class="fw-semibold">{{ $kpi->employee?->full_name ?? '—' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ ucfirst($kpi->period_type) }}</span></td>
                            <td class="text-muted">
                                {{ $kpi->period_start->format('d M') }} – {{ $kpi->period_end->format('d M Y') }}
                            </td>
                            @foreach(['attendance_score','productivity_score','quality_score','punctuality_score','teamwork_score'] as $score)
                            <td class="text-center">
                                <span class="{{ $kpi->$score >= 80 ? 'text-success' : ($kpi->$score >= 60 ? 'text-warning' : 'text-danger') }} fw-semibold">
                                    {{ number_format($kpi->$score, 1) }}
                                </span>
                            </td>
                            @endforeach
                            <td class="text-center fw-bold fs-6">{{ number_format($kpi->overall_score, 1) }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $kpi->grade_color }}">{{ $kpi->grade }}</span>
                            </td>
                            <td class="text-muted">{{ $kpi->evaluatedBy?->name ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top">
                {{ $kpis->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Add KPI Modal --}}
<div class="modal fade" id="addKpiModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">New KPI Evaluation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.hr.kpis.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Employee *</label>
                            <select name="employee_id" class="form-select form-select-sm" required>
                                <option value="">Select Employee</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small">Period *</label>
                            <select name="period_type" class="form-select form-select-sm" required>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly" selected>Monthly</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small">Start Date *</label>
                            <input type="date" name="period_start" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small">End Date *</label>
                            <input type="date" name="period_end" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-12"><hr class="my-1"><small class="fw-bold text-muted text-uppercase">Score each criterion 0–100</small></div>

                        @foreach(['attendance_score'=>'Attendance','productivity_score'=>'Productivity','quality_score'=>'Work Quality','punctuality_score'=>'Punctuality','teamwork_score'=>'Teamwork'] as $field => $label)
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">{{ $label }}</label>
                            <input type="number" name="{{ $field }}" class="form-control form-control-sm" min="0" max="100" step="0.1" required value="80">
                        </div>
                        @endforeach

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Strengths</label>
                            <textarea name="strengths" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Areas for Improvement</label>
                            <textarea name="areas_for_improvement" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Goals for Next Period</label>
                            <textarea name="goals_next_period" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Comments</label>
                            <textarea name="comments" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm px-4">Save Evaluation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
