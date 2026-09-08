@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Payroll Management</h1>
            <p class="text-muted small mb-0">Manage employee salaries, allowances, and deductions.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <x-report-export-menu
                :print-url="route('admin.finance.payroll.print')"
                :pdf-url="route('admin.finance.payroll.pdf')"
                :excel-url="route('admin.finance.payroll.excel')"
            />
            <button class="btn btn-primary btn-sm" onclick="alert('Processing bulk payroll for all active employees...')">
                <i class="fas fa-plus me-2"></i>Process Bulk Payroll
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fas fa-users text-primary fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">{{ $employees->count() }}</div>
                        <div class="text-muted small">Active Employees</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fas fa-money-bill-wave text-success fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">TZS {{ number_format($employees->sum('net_salary')) }}</div>
                        <div class="text-muted small">Total Net Payroll</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="fas fa-calendar-alt text-warning fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">{{ now()->format('F Y') }}</div>
                        <div class="text-muted small">Current Period</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payroll Table -->
    <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="m-0 font-weight-bold text-primary">Employee Payroll List</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">Employee</th>
                            <th>Department</th>
                            <th class="text-end">Basic Salary</th>
                            <th class="text-end">Allowances</th>
                            <th class="text-end">Deductions</th>
                            <th class="text-end">Net Salary</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    @if($emp->photo)
                                        <img src="{{ asset('storage/' . $emp->photo) }}" class="rounded-circle" width="36" height="36" style="object-fit:cover">
                                    @else
                                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:36px;height:36px">
                                            <span class="fw-bold text-primary">{{ strtoupper(substr($emp->full_name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark">{{ $emp->full_name }}</div>
                                        <div class="text-muted x-small">{{ $emp->employee_code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $emp->department ?? '—' }}</td>
                            <td class="text-end fw-semibold">{{ number_format($emp->basic_salary) }}</td>
                            <td class="text-end text-success">+{{ number_format($emp->allowances) }}</td>
                            <td class="text-end text-danger">-{{ number_format($emp->deductions) }}</td>
                            <td class="text-end fw-bold text-primary">{{ number_format($emp->net_salary) }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $emp->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($emp->status) }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.finance.payslip.pdf', $emp->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2" title="Generate Payslip">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-success py-1 px-2" title="Mark as Paid" onclick="if(confirm('Are you sure you want to mark {{ $emp->full_name }} as paid?')) alert('Marked as paid!');">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <p class="mb-0">No active employees found for payroll.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
