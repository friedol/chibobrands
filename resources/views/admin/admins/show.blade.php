@extends('layouts.admin')

@section('title', 'Staff Details: ' . $admin->name . ' - CHIBO BRAND')

@section('content')
<div class="container-fluid py-4 px-4 staff-detail-view">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.admins.index') }}" class="text-decoration-none text-muted">Staff Management</a></li>
            <li class="breadcrumb-item active fw-bold" aria-current="page">Staff Profile</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Profile Column -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-dark py-5 text-center position-relative">
                    <div class="position-absolute w-100 h-100 top-0 start-0 opacity-10" 
                         style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png');"></div>
                    <div class="position-relative">
                        @if($admin->profile_image)
                            <img src="{{ asset('storage/' . $admin->profile_image) }}" alt="{{ $admin->name }}" 
                                 class="rounded-circle border border-4 border-white shadow-sm"
                                 style="width: 120px; height: 120px; object-fit: cover;">
                        @else
                            <div class="rounded-circle border border-4 border-white shadow-sm bg-secondary d-inline-flex align-items-center justify-content-center mx-auto text-white fw-bold"
                                 style="width: 120px; height: 120px; font-size: 3rem;">
                                {{ strtoupper(substr($admin->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body pt-0 text-center">
                    <div style="margin-top: -30px;" class="position-relative mb-3">
                        <span class="badge {{ $admin->is_active ? 'bg-success' : 'bg-danger' }} rounded-pill px-3 shadow-sm border border-2 border-white">
                            {{ $admin->is_active ? 'Active Account' : 'Inactive' }}
                        </span>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">{{ $admin->name }}</h3>
                    <p class="text-muted d-flex align-items-center justify-content-center gap-2 mb-4">
                        <span class="badge bg-soft-primary text-primary text-uppercase">{{ str_replace('_', ' ', $admin->role) }}</span>
                        @if($admin->department)
                            <span class="text-muted">•</span>
                            <span>{{ $admin->department->name }}</span>
                        @endif
                    </p>

                    <div class="d-flex flex-column gap-3 text-start mt-4 px-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-soft-secondary text-secondary rounded-3 p-2">
                                <i class="fas fa-envelope fa-fw"></i>
                            </div>
                            <div>
                                <div class="text-muted x-small">Email Address</div>
                                <div class="fw-medium">{{ $admin->email }}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-soft-secondary text-secondary rounded-3 p-2">
                                <i class="fas fa-phone fa-fw"></i>
                            </div>
                            <div>
                                <div class="text-muted x-small">Phone Number</div>
                                <div class="fw-medium">{{ $admin->phone ?? 'Not provided' }}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-soft-secondary text-secondary rounded-3 p-2">
                                <i class="fas fa-calendar-alt fa-fw"></i>
                            </div>
                            <div>
                                <div class="text-muted x-small">Joined Date</div>
                                <div class="fw-medium">{{ $admin->created_at->format('M d, Y') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 mb-3 px-3">
                        @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'accountant']))
                        <div class="bg-light rounded-4 p-4 text-center border">
                            <div class="text-muted small mb-1">Monthly Salary</div>
                            <div class="h3 fw-bold text-dark mb-0">TZS {{ number_format($admin->monthly_salary ?? 0) }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Column -->
        <div class="col-lg-8">
            <!-- Salary History Section -->
            @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'accountant']) || auth()->user()->id === $admin->id)
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-history text-primary me-2"></i>Salary Payment History
                        </h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-bottom-0 text-secondary small fw-bold text-uppercase">Date</th>
                                    <th class="py-3 border-bottom-0 text-secondary small fw-bold text-uppercase">Amount</th>
                                    <th class="py-3 border-bottom-0 text-secondary small fw-bold text-uppercase">Method</th>
                                    <th class="py-3 border-bottom-0 text-secondary small fw-bold text-uppercase">Approved By</th>
                                    <th class="pe-4 py-3 border-bottom-0 text-secondary small fw-bold text-uppercase">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($salaryHistory as $payment)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $payment->date->format('M d, Y') }}</div>
                                        <div class="x-small text-muted">{{ $payment->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">{{ number_format($payment->amount) }} TZS</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-info text-info border border-info-subtle">{{ $payment->payment_method }}</span>
                                    </td>
                                    <td>
                                        <div class="small fw-medium">{{ $payment->approvedBy->name ?? 'System' }}</div>
                                    </td>
                                    <td class="pe-4">
                                        <div class="small text-muted text-wrap" style="max-width: 200px;">{{ $payment->notes ?? '-' }}</div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="mb-3">
                                            <i class="fas fa-receipt text-muted opacity-25 fa-3x"></i>
                                        </div>
                                        <h6 class="text-muted">No salary payments recorded for this staff member.</h6>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Role Permissions / Description -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-user-shield text-primary me-2"></i>Account Role Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-soft-primary border-0 rounded-4 p-4">
                        <h6 class="fw-bold text-primary mb-2 text-uppercase">{{ str_replace('_', ' ', $admin->role) }}</h6>
                        <p class="mb-0 text-dark opacity-75">
                            @php
                                $roleDescriptions = [
                                    'super_admin' => 'Full access to all features and settings. Can manage all users and system configurations.',
                                    'admin' => 'Can manage most settings and content. Cannot manage other admin users or system settings.',
                                    'manager' => 'Can manage products, categories, and view reports. Limited access to system settings.',
                                    'receptionist' => 'Can manage customer interactions, appointments, and assign tasks to designers.',
                                    'designer' => 'Can view and update assigned design tasks. Limited access to other features.',
                                    'saler' => 'Can manage sales, customers, and orders. Limited access to system settings.',
                                    'operator' => 'Can act as both designer and receptionist. Can perform all tasks assigned to both roles.',
                                    'delivery' => 'Can view assigned delivery tasks and update delivery status.',
                                    'gatekeeper' => 'Can verify items leaving the premises.',
                                    'accountant' => 'Full access to financial dashboards, expenses, cash flow, and management of subordinate staff.',
                                ];
                            @endphp
                            {{ $roleDescriptions[$admin->role] ?? 'Standard staff access permissions.' }}
                        </p>
                    </div>
                    
                    <div class="row g-3 mt-4">
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="small text-muted mb-2">Account Security</div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span class="fw-medium">Verified Identity</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <i class="fas fa-lock text-primary"></i>
                                    <span class="fw-medium">Encrypted Password</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="small text-muted mb-2">Activity Monitoring</div>
                                <div class="small">Last recorded activity: </div>
                                <div class="fw-bold">{{ $admin->updated_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Staff Detail View Styles */
    .staff-detail-view { font-size: 14px; line-height: 1.5; }
    .staff-detail-view .card-title, .staff-detail-view h5 { font-size: 15px !important; }
    .staff-detail-view .table th, .staff-detail-view .table td { font-size: 13px !important; }
    .staff-detail-view .small, .staff-detail-view .x-small, .staff-detail-view .text-muted { font-size: 12px !important; }
    .staff-detail-view .badge { font-size: 11px !important; font-weight: 600; }
    .staff-detail-view .fw-bold { font-size: 14px; }
    .staff-detail-view h3.fw-bold { font-size: 18px; }
    
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
    .text-primary { color: #0d6efd !important; }
    .bg-soft-secondary { background-color: rgba(108, 117, 125, 0.1); }
    .alert-soft-primary { background-color: rgba(13, 110, 253, 0.05); }
    .rounded-4 { border-radius: 1rem !important; }
    .icon-box { display: inline-flex; width: 32px; height: 32px; align-items: center; justify-content: center; }
</style>
@endsection
