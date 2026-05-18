@extends('layouts.admin')
@section('title', 'Leave Management')

@section('content')
<div class="container-fluid py-3">

    {{-- ── Header ── --}}
    <div class="row mb-3 align-items-center">
        <div class="col-lg-7">
            <h4 class="fw-bold mb-0"><i class="fas fa-calendar-minus text-warning me-2"></i>Leave Management</h4>
            @if($pendingCount > 0)
                <span class="badge bg-danger">{{ $pendingCount }} pending approval{{ $pendingCount > 1 ? 's' : '' }}</span>
            @endif
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
                        <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
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
                    <i class="fas fa-calendar-check fa-3x mb-3"></i>
                    <p>No leave requests found.</p>
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-dark">
                        <tr>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th class="text-center">Days</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Reviewed By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaveRequests as $leave)
                        <tr>
                            <td class="fw-semibold">{{ $leave->employee?->full_name ?? '—' }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ ucfirst($leave->leave_type) }}</span>
                            </td>
                            <td>{{ $leave->start_date->format('d M Y') }}</td>
                            <td>{{ $leave->end_date->format('d M Y') }}</td>
                            <td class="text-center fw-bold">{{ $leave->total_days }}</td>
                            <td class="text-muted" style="max-width:200px;">{{ Str::limit($leave->reason, 60) }}</td>
                            <td>
                                <span class="badge bg-{{ $leave->status_badge }}">{{ ucfirst($leave->status) }}</span>
                            </td>
                            <td class="text-muted">{{ $leave->approvedBy?->name ?? '—' }}</td>
                            <td>
                                @if($leave->status === 'pending')
                                    <form action="{{ route('admin.hr.leaves.approve', $leave) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-success py-0 px-2"
                                                onclick="return confirm('Approve this leave?')" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <button class="btn btn-sm btn-danger py-0 px-2 ms-1"
                                            data-bs-toggle="modal" data-bs-target="#rejectModal"
                                            onclick="openRejectModal({{ $leave->id }})"
                                            title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @else
                                    <span class="text-muted">—</span>
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
                                <option value="{{ $emp->id }}">{{ $emp->full_name }} — {{ $emp->department }}</option>
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
</script>
@endpush
