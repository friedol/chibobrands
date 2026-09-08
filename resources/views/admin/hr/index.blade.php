@extends('layouts.admin')
@section('title', 'HR — Employee Management')

@section('content')
<div class="container-fluid py-3">

    {{-- ── Header ── --}}
    <div class="row mb-3 align-items-center">
        <div class="col-lg-6">
            <h4 class="fw-bold mb-0"><i class="fas fa-users-cog text-primary me-2"></i>Human Resources</h4>
        </div>
        <div class="col-lg-6 text-lg-end mt-2 mt-lg-0 d-flex flex-wrap justify-content-lg-end gap-2">
            <a href="{{ route('admin.hr.attendance') }}" class="btn btn-outline-info btn-sm px-3">
                <i class="fas fa-calendar-check me-1"></i>Attendance
            </a>
            <a href="{{ route('admin.hr.leaves') }}" class="btn btn-outline-warning btn-sm px-3">
                @if($pendingLeaves > 0)
                    <span class="badge bg-danger me-1">{{ $pendingLeaves }}</span>
                @endif
                <i class="fas fa-calendar-minus me-1"></i>Leave Requests
            </a>
            <a href="{{ route('admin.hr.kpis') }}" class="btn btn-outline-success btn-sm px-3">
                <i class="fas fa-chart-bar me-1"></i>KPI Evaluations
            </a>
            <a href="{{ route('admin.hr.create') }}" class="btn btn-primary btn-sm px-3">
                <i class="fas fa-user-plus me-1"></i>Add Employee
            </a>
        </div>
    </div>

    {{-- ── Summary Cards ── --}}
    <div class="row g-2 g-md-3 mb-4 hr-summary-row">
        @php
            $summaryCards = [
                ['label' => 'Active Staff', 'value' => $totalActive, 'icon' => 'fas fa-user-check', 'color' => 'success'],
                ['label' => 'On Leave', 'value' => $onLeave, 'icon' => 'fas fa-plane-departure', 'color' => 'warning'],
                ['label' => 'Pending Leaves', 'value' => $pendingLeaves, 'icon' => 'fas fa-clock', 'color' => 'danger'],
                ['label' => 'Present Today', 'value' => $todayPresent, 'icon' => 'fas fa-calendar-day', 'color' => 'info'],
            ];
        @endphp
        @foreach($summaryCards as $c)
        <div class="col-6 col-md-3 hr-summary-item">
            <div class="hr-stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="hr-stat-icon bg-{{ $c['color'] }}-subtle text-{{ $c['color'] }}">
                        <i class="{{ $c['icon'] }}"></i>
                    </div>
                    <span class="hr-stat-sub">Today</span>
                </div>
                <div class="hr-stat-val text-dark">{{ number_format($c['value']) }}</div>
                <div class="hr-stat-lbl">{{ $c['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── HR Automated Leave Sync Widget ── --}}
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-robot text-primary me-2"></i>HR Automated Leave Status Monitor</h6>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill small">
                    <i class="fas fa-check-circle me-1"></i>Active Status Auto-Restored Upon Leave Expiry
                </span>
            </div>
            
            <div class="row g-3 mt-1">
                <!-- On Leave Employees -->
                <div class="col-md-4">
                    <div class="bg-white border rounded p-3 h-100 shadow-2fs">
                        <span class="text-uppercase x-small fw-bold text-muted d-block mb-1">Employees On Leave Currently</span>
                        @if(empty($leaveMetrics['on_leave_employees']) || count($leaveMetrics['on_leave_employees']) == 0)
                            <div class="text-muted small py-2"><i class="fas fa-info-circle me-1"></i>No employees on leave.</div>
                        @else
                            <div class="d-flex flex-column gap-2 mt-2">
                                @foreach($leaveMetrics['on_leave_employees'] as $emp)
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-1">
                                        <span class="fw-semibold small text-dark">{{ $emp->full_name }}</span>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-umbrella-beach me-1"></i>On Leave</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Returning Today & Returning Soon -->
                <div class="col-md-4">
                    <div class="bg-white border rounded p-3 h-100">
                        <span class="text-uppercase x-small fw-bold text-success d-block mb-1">Returning Today (Active Status Restored)</span>
                        @if(empty($leaveMetrics['returning_today']) || count($leaveMetrics['returning_today']) == 0)
                            <div class="text-muted small py-2"><i class="fas fa-calendar-check me-1"></i>No leave expirations today.</div>
                        @else
                            <div class="d-flex flex-column gap-2 mt-2">
                                @foreach($leaveMetrics['returning_today'] as $ret)
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-1">
                                        <span class="fw-semibold small text-dark">{{ $ret->employee?->full_name }}</span>
                                        <span class="badge bg-success"><i class="fas fa-user-check me-1"></i>Active Restored</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recently Completed Leaves -->
                <div class="col-md-4">
                    <div class="bg-white border rounded p-3 h-100">
                        <span class="text-uppercase x-small fw-bold text-primary d-block mb-1">Recently Completed Leaves</span>
                        @if(empty($leaveMetrics['recently_completed']) || count($leaveMetrics['recently_completed']) == 0)
                            <div class="text-muted small py-2"><i class="fas fa-history me-1"></i>No recently completed leaves.</div>
                        @else
                            <div class="d-flex flex-column gap-2 mt-2">
                                @foreach($leaveMetrics['recently_completed']->take(3) as $comp)
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-1">
                                        <span class="fw-semibold small text-dark">{{ $comp->employee?->full_name }}</span>
                                        <span class="badge bg-secondary-subtle text-secondary small">{{ $comp->end_date->format('d M') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body bg-light p-3">
            <form action="{{ route('admin.hr.index') }}" method="GET" class="row g-2">
                <div class="col-12 col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Search name, code, phone..." value="{{ request('search') }}">
                </div>
                <div class="col-6 col-md-2">
                    <select name="department" class="form-select form-select-sm">
                        <option value="all">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="all">All Status</option>
                        <option value="active"     {{ request('status') === 'active'     ? 'selected' : '' }}>Active</option>
                        <option value="inactive"   {{ request('status') === 'inactive'   ? 'selected' : '' }}>Inactive</option>
                        <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>Terminated</option>
                        <option value="on_leave"   {{ request('status') === 'on_leave'   ? 'selected' : '' }}>On Leave</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="contract_type" class="form-select form-select-sm">
                        <option value="all">All Contracts</option>
                        <option value="permanent"  {{ request('contract_type') === 'permanent'  ? 'selected' : '' }}>Permanent</option>
                        <option value="contract"   {{ request('contract_type') === 'contract'   ? 'selected' : '' }}>Contract</option>
                        <option value="part_time"  {{ request('contract_type') === 'part_time'  ? 'selected' : '' }}>Part-Time</option>
                        <option value="intern"     {{ request('contract_type') === 'intern'     ? 'selected' : '' }}>Intern</option>
                    </select>
                </div>
                <div class="col-auto d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fas fa-search me-1"></i>Search</button>
                    <a href="{{ route('admin.hr.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Employees Table ── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center">
            <span class="fw-semibold">{{ $employees->total() }} Employees</span>
        </div>
        <div class="card-body p-0">
            @if($employees->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No employees found. Add your first employee to get started.</p>
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-dark">
                        <tr>
                            <th>Employee</th>
                            <th>Code</th>
                            <th>Department</th>
                            <th>Role</th>
                            <th>Contract</th>
                            <th>Hire Date</th>
                            <th class="text-end">Net Salary</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $emp)
                        @php
                            $statusColors = ['active' => 'success', 'inactive' => 'secondary', 'terminated' => 'danger', 'on_leave' => 'warning'];
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($emp->photo)
                                        <img src="{{ Storage::url($emp->photo) }}" class="rounded-circle" width="36" height="36" style="object-fit:cover">
                                    @else
                                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:36px;height:36px">
                                            <span class="fw-bold text-primary">{{ strtoupper(substr($emp->full_name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $emp->full_name }}</div>
                                        <div class="text-muted x-small">{{ $emp->phone ?? $emp->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><code class="small">{{ $emp->employee_code }}</code></td>
                            <td>{{ $emp->department ?? '—' }}</td>
                            <td>{{ $emp->role_title ?? '—' }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $emp->contract_type)) }}</span>
                            </td>
                            <td>{{ $emp->hire_date?->format('d M Y') ?? '—' }}</td>
                            <td class="text-end fw-bold">{{ number_format($emp->net_salary) }}</td>
                            <td>
                                <span class="badge bg-{{ $statusColors[$emp->status] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $emp->status)) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.hr.show', $emp) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.hr.edit', $emp) }}" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                <small class="text-muted">{{ $employees->firstItem() }}–{{ $employees->lastItem() }} of {{ $employees->total() }}</small>
                {{ $employees->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .hr-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 11px;
        padding: 11px 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .hr-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .hr-stat-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }

    .hr-stat-val {
        font-size: 1.3rem;
        font-weight: 700;
        line-height: 1.15;
        margin-top: 8px;
    }

    .hr-stat-lbl {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        margin-top: 2px;
    }

    .hr-stat-sub {
        font-size: 10px;
        font-weight: 500;
        color: #94a3b8;
    }

    @media (max-width: 768px) {
        .hr-stat-card {
            padding: 9px 10px;
        }

        .hr-stat-val {
            font-size: 1.08rem;
        }

        .hr-stat-lbl {
            font-size: 11px;
        }
    }
</style>
@endpush
