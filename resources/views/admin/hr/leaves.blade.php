@extends('layouts.admin')
@section('title', 'Leave Management & HR Automation')

@section('content')
<div class="container-fluid py-3">

    {{-- ── Header ── --}}
    <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
            <h4 class="fw-bold mb-0">
                <i class="fas fa-calendar-minus text-warning me-2"></i>Leave Management 
            </h4>
            <div class="d-flex align-items-center gap-2 mt-1">
                @if($pendingCount > 0)
                    <span class="badge bg-danger">{{ $pendingCount }} pending approval{{ $pendingCount > 1 ? 's' : '' }}</span>
                @endif
            </div>
        </div>
        <div class="col-lg-5 text-lg-end mt-2 mt-lg-0">
            <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addLeaveModal">
                <i class="fas fa-plus me-1"></i>New Leave Request
            </button>
            <a href="{{ route('admin.hr.index') }}" class="btn btn-outline-secondary btn-sm px-3 ms-1">
                <i class="fas fa-arrow-left me-1"></i>Back to HR
            </a>
        </div>
    </div>

    {{-- ── HR Leave Automation Summary Cards ── --}}
    <div class="row g-2 g-md-3 mb-4 leave-summary-row">
        <div class="col-6 col-md-3">
            <div class="leave-stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="leave-stat-icon bg-warning-subtle text-warning">
                        <i class="fas fa-plane-departure"></i>
                    </div>
                    <span class="leave-stat-sub">Today</span>
                </div>
                <div class="leave-stat-val text-dark">{{ number_format(count($leaveMetrics['on_leave_employees'] ?? [])) }}</div>
                <div class="leave-stat-lbl">Currently On Leave</div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="leave-stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="leave-stat-icon bg-success-subtle text-success">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <span class="leave-stat-sub">Today</span>
                </div>
                <div class="leave-stat-val text-success">{{ number_format(count($leaveMetrics['returning_today'] ?? [])) }}</div>
                <div class="leave-stat-lbl">Returning Today (Active)</div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="leave-stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="leave-stat-icon bg-info-subtle text-info">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <span class="leave-stat-sub">7 Days</span>
                </div>
                <div class="leave-stat-val text-info">{{ number_format(count($leaveMetrics['returning_soon'] ?? [])) }}</div>
                <div class="leave-stat-lbl">Returning Soon</div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="leave-stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="leave-stat-icon bg-primary-subtle text-primary">
                        <i class="fas fa-history"></i>
                    </div>
                    <span class="leave-stat-sub">Recent</span>
                </div>
                <div class="leave-stat-val text-primary">{{ number_format(count($leaveMetrics['recently_completed'] ?? [])) }}</div>
                <div class="leave-stat-lbl">Completed Leaves</div>
            </div>
        </div>
    </div>

    {{-- ── Returning Today & Returning Soon Alerts Section ── --}}
    @if(!empty($leaveMetrics['returning_today']) && count($leaveMetrics['returning_today']) > 0)
    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4 p-3 rounded-3" role="alert">
        <i class="fas fa-check-circle fs-3 me-3 text-success"></i>
        <div>
            <h6 class="fw-bold mb-1 text-success"><i class="fas fa-bell me-1"></i>Employees Returning From Leave Today:</h6>
            <div class="d-flex flex-wrap gap-2">
                @foreach($leaveMetrics['returning_today'] as $ret)
                    <span class="badge bg-white text-success border border-success px-3 py-1.5 rounded-pill shadow-sm">
                        <i class="fas fa-user-clock me-1"></i>{{ $ret->employee?->full_name }} ({{ ucfirst($ret->leave_type) }} ended {{ $ret->end_date->format('d M') }})
                    </span>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ── Filters ── --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body bg-light p-3">
            <form action="{{ route('admin.hr.leaves') }}" method="GET" class="row g-2">
                <div class="col-6 col-md-3">
                    <select name="employee_id" class="form-select form-select-sm">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="all">All Status</option>
                        <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                        <option value="approved"  {{ request('status') === 'approved'  ? 'selected' : '' }}>Approved (On Leave)</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed (Active Restored)</option>
                        <option value="rejected"  {{ request('status') === 'rejected'  ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="leave_type" class="form-select form-select-sm">
                        <option value="all">All Types</option>
                        @foreach(['annual','sick','unpaid','maternity','paternity','compassionate'] as $type)
                            <option value="{{ $type }}" {{ request('leave_type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto d-flex gap-1 align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                    <a href="{{ route('admin.hr.leaves') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Leave Requests Table ── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($leaveRequests->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-calendar-check fa-3x mb-3 text-secondary opacity-50"></i>
                    <p>No leave requests found.</p>
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-dark">
                        <tr>
                            <th>Employee</th>
                            <th>Employee Status</th>
                            <th>Leave Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th class="text-center">Days</th>
                            <th>Reason</th>
                            <th>Leave Status</th>
                            <th>Reviewed By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaveRequests as $leave)
                        <tr>
                            <td class="fw-semibold">{{ $leave->employee?->full_name ?? '—' }}</td>
                            <td>
                                @if($leave->employee?->status === 'on_leave')
                                    <span class="badge bg-warning text-dark"><i class="fas fa-bed me-1"></i>On Leave</span>
                                @elseif($leave->employee?->status === 'active')
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Active</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($leave->employee?->status ?? 'N/A') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ ucfirst($leave->leave_type) }}</span>
                            </td>
                            <td>{{ $leave->start_date->format('d M Y') }}</td>
                            <td>{{ $leave->end_date->format('d M Y') }}</td>
                            <td class="text-center fw-bold">{{ $leave->total_days }}</td>
                            <td class="text-muted" style="max-width:200px;">{{ Str::limit($leave->reason, 60) }}</td>
                            <td>
                                @if($leave->status === 'completed')
                                    <span class="badge bg-success" title="Leave ended and employee status returned to Active"><i class="fas fa-check-double me-1"></i>Completed</span>
                                @elseif($leave->status === 'approved')
                                    <span class="badge bg-info text-dark"><i class="fas fa-clock me-1"></i>Approved</span>
                                @elseif($leave->status === 'rejected')
                                    <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Rejected</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half me-1"></i>Pending</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $leave->approvedBy?->name ?? '—' }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary py-0 px-2"
                                        data-bs-toggle="modal" data-bs-target="#editLeaveModal"
                                        onclick="openEditModal({{ $leave->id }}, {{ $leave->employee_id }}, '{{ $leave->leave_type }}', '{{ $leave->start_date->format('Y-m-d') }}', '{{ $leave->end_date->format('Y-m-d') }}', {{ json_encode($leave->reason) }}, {{ json_encode($leave->notes ?? '') }}, '{{ $leave->status }}')"
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($leave->status === 'pending')
                                    <form action="{{ route('admin.hr.leaves.approve', $leave) }}" method="POST" class="d-inline ms-1">
                                        @csrf @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-success py-0 px-2"
                                                onclick="return confirm('Approve this leave? Status will automatically sync depending on dates.')" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <button class="btn btn-sm btn-danger py-0 px-2 ms-1"
                                            data-bs-toggle="modal" data-bs-target="#rejectModal"
                                            onclick="openRejectModal({{ $leave->id }})"
                                            title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top">
                {{ $leaveRequests->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ── Add Leave Request Modal ── --}}
<div class="modal fade" id="addLeaveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">New Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.hr.leaves.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Employee *</label>
                        <select name="employee_id" class="form-select form-select-sm" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }} — {{ $emp->department }} ({{ ucfirst($emp->status) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Leave Type *</label>
                            <select name="leave_type" class="form-select form-select-sm" required>
                                @foreach(['annual'=>'Annual Leave','sick'=>'Sick Leave','unpaid'=>'Unpaid Leave','maternity'=>'Maternity','paternity'=>'Paternity','compassionate'=>'Compassionate'] as $val => $lbl)
                                    <option value="{{ $val }}">{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-3">
                            <label class="form-label fw-semibold small">From *</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-3">
                            <label class="form-label fw-semibold small">To *</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Reason *</label>
                        <textarea name="reason" class="form-control form-control-sm" rows="3" required></textarea>
                    </div>
                    <div>
                        <label class="form-label fw-semibold small">Additional Notes</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Edit Leave Modal ── --}}
<div class="modal fade" id="editLeaveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2 text-primary"></i>Edit Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editLeaveForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Employee *</label>
                        <select name="employee_id" id="edit_employee_id" class="form-select form-select-sm" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }} — {{ $emp->department }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Leave Type *</label>
                            <select name="leave_type" id="edit_leave_type" class="form-select form-select-sm" required>
                                @foreach(['annual'=>'Annual Leave','sick'=>'Sick Leave','unpaid'=>'Unpaid Leave','maternity'=>'Maternity','paternity'=>'Paternity','compassionate'=>'Compassionate'] as $val => $lbl)
                                    <option value="{{ $val }}">{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-3">
                            <label class="form-label fw-semibold small">From *</label>
                            <input type="date" name="start_date" id="edit_start_date" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-3">
                            <label class="form-label fw-semibold small">To *</label>
                            <input type="date" name="end_date" id="edit_end_date" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Status *</label>
                        <select name="status" id="edit_status" class="form-select form-select-sm" required>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved (On Leave)</option>
                            <option value="completed">Completed (Active Restored)</option>
                            <option value="rejected">Rejected</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Reason *</label>
                        <textarea name="reason" id="edit_reason" class="form-control form-control-sm" rows="3" required></textarea>
                    </div>
                    <div>
                        <label class="form-label fw-semibold small">Additional Notes</label>
                        <textarea name="notes" id="edit_notes" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Reject Modal ── --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold text-danger">Reject Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf @method('PUT')
                <input type="hidden" id="rejectLeaveId" name="_leave_id">
                <div class="modal-body">
                    <label class="form-label fw-semibold small">Reason for Rejection *</label>
                    <textarea name="rejection_reason" class="form-control form-control-sm" rows="3" required
                              placeholder="Explain why this leave is being rejected..."></textarea>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4">Reject Leave</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openRejectModal(leaveId) {
    document.getElementById('rejectLeaveId').value = leaveId;
    document.getElementById('rejectForm').action = `/admin/hr/leaves/${leaveId}/reject`;
}

function openEditModal(id, employeeId, leaveType, startDate, endDate, reason, notes, status) {
    document.getElementById('editLeaveForm').action = `/admin/hr/leaves/${id}`;
    document.getElementById('edit_employee_id').value = employeeId;
    document.getElementById('edit_leave_type').value  = leaveType;
    document.getElementById('edit_start_date').value  = startDate;
    document.getElementById('edit_end_date').value    = endDate;
    document.getElementById('edit_reason').value      = reason;
    document.getElementById('edit_notes').value       = notes;
    document.getElementById('edit_status').value      = status;
}
</script>
@endpush

@push('styles')
<style>
    .leave-stat-card {
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

    .leave-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .leave-stat-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }

    .leave-stat-val {
        font-size: 1.3rem;
        font-weight: 700;
        line-height: 1.15;
        margin-top: 8px;
    }

    .leave-stat-lbl {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        margin-top: 2px;
    }

    .leave-stat-sub {
        font-size: 10px;
        font-weight: 500;
        color: #94a3b8;
    }

    @media (max-width: 768px) {
        .leave-stat-card {
            padding: 9px 10px;
        }

        .leave-stat-val {
            font-size: 1.08rem;
        }

        .leave-stat-lbl {
            font-size: 11px;
        }
    }
</style>
@endpush
