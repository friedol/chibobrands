@extends('layouts.admin')
@section('title', 'HR — Employee Management')

@section('content')
<div class="container-fluid py-3">

    {{-- ── Header ── --}}
    <div class="row mb-3 align-items-center">
        <div class="col-lg-6">
            <h4 class="fw-bold mb-0"><i class="fas fa-users-cog text-primary me-2"></i>Human Resources</h4>
            <p class="text-muted small mb-0">Employee Management, Attendance & Performance</p>
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
            <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                <i class="fas fa-user-plus me-1"></i>Add Employee
            </button>
        </div>
    </div>

    {{-- ── Summary Cards ── --}}
    <div class="row g-3 mb-4">
        @php
            $summaryCards = [
                ['label' => 'Active Staff', 'value' => $totalActive, 'icon' => 'fas fa-user-check', 'color' => 'success'],
                ['label' => 'On Leave', 'value' => $onLeave, 'icon' => 'fas fa-beach', 'color' => 'warning'],
                ['label' => 'Pending Leaves', 'value' => $pendingLeaves, 'icon' => 'fas fa-clock', 'color' => 'danger'],
                ['label' => 'Present Today', 'value' => $todayPresent, 'icon' => 'fas fa-calendar-day', 'color' => 'info'],
            ];
        @endphp
        @foreach($summaryCards as $c)
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center py-3">
                    <div class="rounded-circle bg-{{ $c['color'] }} bg-opacity-10 p-3 me-3">
                        <i class="{{ $c['icon'] }} text-{{ $c['color'] }}"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold">{{ $c['value'] }}</div>
                        <div class="x-small text-muted fw-semibold">{{ $c['label'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
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
                                    <button class="btn btn-sm btn-outline-secondary py-0 px-2"
                                        data-bs-toggle="modal" data-bs-target="#editEmployeeModal"
                                        onclick="fillEditModal({{ $emp->toJson() }})"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
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

{{-- ── Add Employee Modal ── --}}
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-plus text-primary me-2"></i>Add Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.hr.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Personal Info --}}
                        <div class="col-12"><h6 class="fw-bold text-muted small text-uppercase border-bottom pb-1">Personal Information</h6></div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Full Name *</label>
                            <input type="text" name="full_name" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Phone</label>
                            <input type="text" name="phone" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Email</label>
                            <input type="email" name="email" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">National ID</label>
                            <input type="text" name="national_id" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Photo</label>
                            <input type="file" name="photo" class="form-control form-control-sm" accept="image/*">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Link to System User</label>
                            <select name="user_id" class="form-select form-select-sm">
                                <option value="">Not Linked</option>
                                @foreach($linkedUsers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Employment --}}
                        <div class="col-12 mt-2"><h6 class="fw-bold text-muted small text-uppercase border-bottom pb-1">Employment Details</h6></div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Department</label>
                            <input type="text" name="department" class="form-control form-control-sm" list="dept-list">
                            <datalist id="dept-list">
                                @foreach($departments as $d)<option value="{{ $d }}">@endforeach
                            </datalist>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Role / Job Title</label>
                            <input type="text" name="role_title" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Contract Type *</label>
                            <select name="contract_type" class="form-select form-select-sm" required>
                                <option value="permanent">Permanent</option>
                                <option value="contract">Contract</option>
                                <option value="part_time">Part-Time</option>
                                <option value="intern">Intern</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Hire Date</label>
                            <input type="date" name="hire_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Contract End Date</label>
                            <input type="date" name="contract_end_date" class="form-control form-control-sm">
                        </div>

                        {{-- Salary --}}
                        <div class="col-12 mt-2"><h6 class="fw-bold text-muted small text-uppercase border-bottom pb-1">Salary Information</h6></div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Basic Salary (TZS)</label>
                            <input type="number" name="basic_salary" class="form-control form-control-sm" min="0" step="any">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Allowances (TZS)</label>
                            <input type="number" name="allowances" class="form-control form-control-sm" min="0" step="any">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Deductions (TZS)</label>
                            <input type="number" name="deductions" class="form-control form-control-sm" min="0" step="any">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Bank Account Number</label>
                            <input type="text" name="bank_account" class="form-control form-control-sm">
                        </div>

                        {{-- Emergency --}}
                        <div class="col-12 mt-2"><h6 class="fw-bold text-muted small text-uppercase border-bottom pb-1">Emergency Contact</h6></div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Contact Name</label>
                            <input type="text" name="emergency_contact_name" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Contact Phone</label>
                            <input type="text" name="emergency_contact_phone" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Home Address</label>
                            <textarea name="address" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Notes</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Save Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Edit Employee Modal (simplified, fields pre-filled via JS) ── --}}
<div class="modal fade" id="editEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Edit Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editEmployeeForm" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-body" id="editModalBody">
                    {{-- Populated by JS --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Update Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function fillEditModal(emp) {
    document.getElementById('editEmployeeForm').action = `/admin/hr/${emp.id}`;
    document.getElementById('editModalBody').innerHTML = `
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label fw-semibold small">Full Name *</label>
                <input type="text" name="full_name" class="form-control form-control-sm" value="${emp.full_name}" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold small">Phone</label>
                <input type="text" name="phone" class="form-control form-control-sm" value="${emp.phone || ''}"></div>
            <div class="col-md-4"><label class="form-label fw-semibold small">Email</label>
                <input type="email" name="email" class="form-control form-control-sm" value="${emp.email || ''}"></div>
            <div class="col-md-4"><label class="form-label fw-semibold small">Department</label>
                <input type="text" name="department" class="form-control form-control-sm" value="${emp.department || ''}"></div>
            <div class="col-md-4"><label class="form-label fw-semibold small">Role / Job Title</label>
                <input type="text" name="role_title" class="form-control form-control-sm" value="${emp.role_title || ''}"></div>
            <div class="col-md-4"><label class="form-label fw-semibold small">Status</label>
                <select name="status" class="form-select form-select-sm">
                    ${['active','inactive','terminated','on_leave'].map(s =>
                        `<option value="${s}" ${emp.status===s?'selected':''}>${s.replace('_',' ').replace(/\b\w/g,c=>c.toUpperCase())}</option>`
                    ).join('')}
                </select></div>
            <div class="col-md-4"><label class="form-label fw-semibold small">Contract Type *</label>
                <select name="contract_type" class="form-select form-select-sm" required>
                    ${['permanent','contract','part_time','intern'].map(t =>
                        `<option value="${t}" ${emp.contract_type===t?'selected':''}>${t.replace('_',' ').replace(/\b\w/g,c=>c.toUpperCase())}</option>`
                    ).join('')}
                </select></div>
            <div class="col-md-4"><label class="form-label fw-semibold small">Basic Salary</label>
                <input type="number" name="basic_salary" class="form-control form-control-sm" value="${emp.basic_salary || 0}" min="0" step="any"></div>
            <div class="col-md-4"><label class="form-label fw-semibold small">Allowances</label>
                <input type="number" name="allowances" class="form-control form-control-sm" value="${emp.allowances || 0}" min="0" step="any"></div>
            <div class="col-md-4"><label class="form-label fw-semibold small">Deductions</label>
                <input type="number" name="deductions" class="form-control form-control-sm" value="${emp.deductions || 0}" min="0" step="any"></div>
        </div>
    `;
}
</script>
@endpush
