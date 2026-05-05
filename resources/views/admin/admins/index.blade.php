@extends('layouts.admin')

@section('page-title', 'Admin Users')

@push('styles')
<style>
    /* Modern UI Variables */
    :root {
        --card-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        --card-shadow-hover: 0 4px 16px rgba(0, 0, 0, 0.12);
        --border-radius: 12px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Role Badges - Modern Design */
    .role-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        transition: var(--transition);
    }
    
    .role-super_admin { 
        background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); 
        color: white;
        box-shadow: 0 2px 4px rgba(111, 66, 193, 0.3);
    }
    .role-admin { 
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); 
        color: white;
        box-shadow: 0 2px 4px rgba(13, 110, 253, 0.3);
    }
    .role-receptionist { 
        background: linear-gradient(135deg, #20c997 0%, #1aa179 100%); 
        color: white;
        box-shadow: 0 2px 4px rgba(32, 201, 151, 0.3);
    }
    .role-designer { 
        background: linear-gradient(135deg, #fd7e14 0%, #e86a0a 100%); 
        color: white;
        box-shadow: 0 2px 4px rgba(253, 126, 20, 0.3);
    }
    .role-saler { 
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); 
        color: white;
        box-shadow: 0 2px 4px rgba(108, 117, 125, 0.3);
    }
    .role-operator { 
        background: linear-gradient(135deg, #e83e8c 0%, #d91a72 100%); 
        color: white;
        box-shadow: 0 2px 4px rgba(232, 62, 140, 0.3);
    }
    .role-delivery { 
        background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%); 
        color: white;
        box-shadow: 0 2px 4px rgba(23, 162, 184, 0.3);
    }
    .role-gatekeeper { 
        background: linear-gradient(135deg, #343a40 0%, #23272b 100%); 
        color: white;
        box-shadow: 0 2px 4px rgba(52, 58, 64, 0.3);
    }
    .role-accountant { 
        background: linear-gradient(135deg, #198754 0%, #157347 100%); 
        color: white;
        box-shadow: 0 2px 4px rgba(25, 135, 84, 0.3);
    }
    
    /* Filter Badges - Modern Design */
    .filter-badge {
        cursor: pointer;
        transition: var(--transition);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
    }
    
    .filter-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    .filter-badge.active {
        box-shadow: 0 0 0 2px #fff, 0 0 0 4px #dc3545;
        transform: scale(1.05);
    }

    /* Page Header */
    .page-header h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 0.25rem;
    }

    .page-header p {
        color: #6c757d;
        font-size: 0.875rem;
        margin-bottom: 0;
    }

    /* Add User Button */
    .btn-primary {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border: none;
        padding: 0.625rem 1.25rem;
        border-radius: 8px;
        font-weight: 600;
        transition: var(--transition);
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.25);
        position: relative;
        overflow: hidden;
    }

    .btn-primary:hover:not(:disabled) {
        background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.35);
    }

    .btn-primary:disabled {
        opacity: 0.7;
        cursor: wait;
        transform: none;
    }

    .btn-primary .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        border-width: 0.15em;
    }
    /* Card Stats */
    .card-metric {
        transition: var(--transition);
        border: 1px solid rgba(0,0,0,0.03);
    }
    
    .card-metric:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.06) !important;
    }
    
    .icon-circle {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
    }

    /* Action Buttons Design from Customer Module */
    .btn-action {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: none;
        transition: all 0.2s ease;
        text-decoration: none;
        cursor: pointer;
        padding: 0 !important;
    }
    
    .btn-view {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }
    
    .btn-view:hover {
        background-color: #0d6efd;
        color: white;
        transform: translateY(-2px);
    }
    
    .btn-edit {
        background-color: rgba(13, 202, 240, 0.1);
        color: #0dcaf0;
    }
    
    .btn-edit:hover {
        background-color: #0dcaf0;
        color: white;
        transform: translateY(-2px);
    }

    .btn-pay {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
    }
    
    .btn-pay:hover {
        background-color: #198754;
        color: white;
        transform: translateY(-2px);
    }
    
    .btn-delete {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    
    .btn-delete:hover {
        background-color: #dc3545;
        color: white;
        transform: translateY(-2px);
    }

    .btn-profile {
        background-color: rgba(13, 202, 240, 0.1);
        color: #0dcaf0;
    }

    .btn-profile:hover {
        background-color: #0dcaf0;
        color: white;
        transform: translateY(-2px);
    }

    .x-small { font-size: 11px !important; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4 page-header">
                <div>
                    <h2 class="mb-1">Team Members</h2>
                    <p class="text-muted mb-0">Manage users and their permissions</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'accountant']))
                    <button type="button" class="btn btn-success btn-sm shadow-sm" onclick="payAllModal()">
                        <i class="fas fa-money-bill-wave me-1"></i>Pay All
                    </button>
                    @endif
                    <button type="button" class="btn btn-primary btn-sm shadow-sm" id="createAdminBtn" onclick="createAdmin()">
                        <i class="fas fa-user-plus me-2"></i>New Member
                    </button>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="row g-3 mb-4">
                <!-- Total Team -->
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100 card-metric">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-primary bg-opacity-10 text-primary me-3">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="text-muted small fw-bold text-uppercase ls-1">Total Team</div>
                            </div>
                            <div class="h3 mb-0 fw-bold text-dark ps-1">{{ $stats['total'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                <!-- Active -->
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100 card-metric">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-success bg-opacity-10 text-success me-3">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div class="text-muted small fw-bold text-uppercase ls-1">Active</div>
                            </div>
                            <div class="h3 mb-0 fw-bold text-dark ps-1">{{ $stats['active'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                <!-- New This Month -->
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100 card-metric">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-info bg-opacity-10 text-info me-3">
                                    <i class="fas fa-calendar-plus"></i>
                                </div>
                                <div class="text-muted small fw-bold text-uppercase ls-1">New (Month)</div>
                            </div>
                            <div class="h3 mb-0 fw-bold text-dark ps-1">{{ $stats['new_this_month'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                <!-- Online (Placeholder) -->
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100 card-metric">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-warning bg-opacity-10 text-warning me-3">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <div class="text-muted small fw-bold text-uppercase ls-1">Roles</div>
                            </div>
                            <div class="h3 mb-0 fw-bold text-dark ps-1">{{ count($roles) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters Section -->
            <div class="collapse {{ request('role') ? 'show' : '' }} mb-4 filter-section" id="filterCollapse">
                <div class="card border-0 shadow-sm border-top border-4 border-primary">
                    <div class="card-body bg-light p-3">
                        <form action="{{ route('admin.admins.index') }}" method="GET" class="row g-2 align-items-end" data-no-global-handler>
                            <div class="col-6 col-md-3">
                                <label class="form-label fw-bold x-small text-uppercase mb-1">Role</label>
                                <select class="form-select form-select-sm" name="role">
                                    <option value="">All Roles</option>
                                    @php
                                        $rolesList = [
                                            'super_admin' => 'Super Admin',
                                            'admin' => 'Admin',
                                            'manager' => 'Manager',
                                            'saler' => 'Saler',
                                            'receptionist' => 'Receptionist',
                                            'designer' => 'Designer',
                                            'operator' => 'Operator',
                                            'delivery' => 'Delivery',
                                            'gatekeeper' => 'Gatekeeper',
                                            'accountant' => 'Accountant'
                                        ];
                                    @endphp
                                    @foreach($rolesList as $key => $roleName)
                                        @if(auth()->user()->role === 'accountant' && in_array($key, ['super_admin', 'admin', 'manager', 'accountant']))
                                            @continue
                                        @endif
                                        <option value="{{ $key }}" {{ request('role') == $key ? 'selected' : '' }}>
                                            {{ $roleName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label fw-bold x-small text-uppercase mb-1">Department</label>
                                <select class="form-select form-select-sm" name="department_id">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <!-- Search or space -->
                            </div>
                            <div class="col-md-2 d-flex gap-1">
                                <button type="submit" class="btn btn-primary btn-sm flex-grow-1 fw-bold">
                                    <i class="fas fa-search me-1"></i> SEARCH
                                </button>
                                <a href="{{ route('admin.admins.index') }}" class="btn btn-outline-secondary btn-sm fw-bold">
                                    <i class="fas fa-undo"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admins Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-2">
            <h6 class="mb-0 fw-bold text-white" style="font-size: 0.9rem;">
                <i class="fas fa-users-cog me-2"></i>All Administrators
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle modern-admin-table">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col" class="ps-4 py-3 text-secondary text-uppercase x-small fw-bold border-0">Admin</th>
                            <th scope="col" class="px-3 py-3 text-secondary text-uppercase x-small fw-bold border-0 d-none d-md-table-cell">Contact</th>
                            <th scope="col" class="px-3 py-3 text-secondary text-uppercase x-small fw-bold border-0">Role</th>
                            <th scope="col" class="px-3 py-3 text-secondary text-uppercase x-small fw-bold border-0">Dept</th>
                            <th scope="col" class="px-3 py-3 text-secondary text-uppercase x-small fw-bold border-0">Status</th>
                            <th scope="col" class="px-3 py-3 text-secondary text-uppercase x-small fw-bold border-0 d-none d-lg-table-cell">Created</th>
                            @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'accountant']))
                                <th scope="col" class="px-3 py-3 text-secondary text-uppercase x-small fw-bold border-0">Salary</th>
                            @endif
                            <th scope="col" class="pe-4 py-3 text-secondary text-uppercase x-small fw-bold border-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="admin-avatar me-2 me-md-3">
                                            @php
                                                $adminAvatarSrc = !empty($admin->profile_image ?? null)
                                                    ? asset('storage/' . $admin->profile_image)
                                                    : asset('img/avatars/placeholder.png');
                                            @endphp
                                            <img
                                                src="{{ $adminAvatarSrc }}"
                                                alt="{{ $admin->name }}"
                                                class="rounded shadow-sm"
                                                style="width: 40px; height: 40px; min-width: 40px; object-fit: cover;"
                                                onerror="this.onerror=null; this.src='{{ asset('img/avatars/placeholder.png') }}';"
                                            >
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark">{{ $admin->name }}</div>
                                            <small class="text-muted d-block d-md-none x-small">{{ $admin->email }}</small>
                                            <small class="text-muted d-none d-md-block x-small">ID: {{ $admin->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 d-none d-md-table-cell">
                                    <div class="d-flex flex-column text-start">
                                        <span class="text-dark fw-medium">{{ $admin->email }}</span>
                                        @if($admin->phone)
                                            <span class="text-muted small x-small"><i class="fas fa-phone-alt me-1 x-small"></i>{{ $admin->phone }}</span>
                                        @else
                                            <span class="text-muted x-small fst-italic">No phone</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="text-start">
                                        <span class="role-badge role-{{ $admin->role }}">
                                            {{ $roles[$admin->role] ?? ucfirst($admin->role) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="text-start">
                                        @if($admin->department)
                                            <span class="badge bg-light text-dark border fw-semibold rounded-pill">
                                                {{ $admin->department->name }}
                                            </span>
                                        @else
                                            <span class="text-muted x-small">Global</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="text-start">
                                        @if($admin->is_active ?? true)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                                Active
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                                                Inactive
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 d-none d-lg-table-cell">
                                    <div class="d-flex flex-column text-start">
                                        <span class="text-dark fw-medium small">{{ $admin->created_at->format('M d, Y') }}</span>
                                        <span class="text-muted x-small">{{ $admin->created_at->diffForHumans() }}</span>
                                    </div>
                                </td>
                                @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'accountant']))
                                    <td class="px-3 py-3">
                                        <div class="text-start">
                                            <div class="fw-bold text-dark">{{ number_format($admin->monthly_salary ?? 0) }}</div>
                                            <div class="text-muted x-small">TZS / Month</div>
                                        </div>
                                    </td>
                                @endif
                                <td class="pe-4 py-3">
                                    <div class="d-flex justify-content-start gap-2">
                                        <a href="{{ route('admin.admins.show', $admin->id) }}" class="btn-action btn-view" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($admin->id !== auth()->id())
                                            <button type="button" class="btn-action btn-edit" 
                                                    onclick="editAdmin({{ $admin->id }}, '{{ addslashes($admin->name) }}', '{{ $admin->email }}', '{{ $admin->phone ?? '' }}', '{{ $admin->role }}', {{ $admin->is_active ? 'true' : 'false' }}, '{{ $admin->created_at->format('M d, Y') }}', '{{ $admin->profile_image ? asset('storage/' . $admin->profile_image) : '' }}', '{{ $admin->department_id ?? '' }}', '{{ $admin->monthly_salary ?? 0 }}')"
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'accountant']) && ($admin->monthly_salary ?? 0) > 0)
                                                @if(in_array($admin->id, $paidStaffIds ?? []))
                                                    <button type="button" class="btn-action btn-pay opacity-50 pe-none"
                                                            title="Paid this month">
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn-action btn-pay"
                                                            onclick="paySalary({{ $admin->id }}, '{{ addslashes($admin->name) }}', {{ $admin->monthly_salary }})"
                                                            title="Pay Salary">
                                                        <i class="fas fa-money-bill-wave"></i>
                                                    </button>
                                                @endif
                                            @endif
                                            <button type="button" class="btn-action btn-delete"
                                                    onclick="deleteAdmin({{ $admin->id }}, '{{ $admin->name }}')"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @else
                                            <a href="{{ route('admin.profile') }}" class="btn-action btn-profile" title="View Profile">
                                                <i class="fas fa-user"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-users-slash text-muted mb-3" style="font-size: 4rem; opacity: 0.5;"></i>
                                        <h5 class="text-muted mb-2">No admin users found</h5>
                                        <p class="text-muted small mb-0">Get started by adding your first team member</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($admins->hasPages())
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-3 pt-3 border-top bg-light px-3 px-md-4 py-3">
                    <div class="text-muted small fw-medium text-center text-md-start">
                        <i class="fas fa-info-circle me-1"></i>
                        Showing {{ $admins->firstItem() }} to {{ $admins->lastItem() }} of {{ $admins->total() }} users
        </div>
                    <div class="d-flex justify-content-center">
                        {{ $admins->appends(request()->query())->links('pagination::bootstrap-4') }}
    </div>
</div>
            @endif
                    </div>
                        </div>
                        </div>

@if(session('success'))
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div class="toast show" role="alert">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                        </div>
            <div class="toast-body">
                {{ session('success') }}
                        </div>
                        </div>
                    </div>
@endif

@if(session('error'))
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div class="toast show" role="alert">
            <div class="toast-header bg-danger text-white">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong class="me-auto">Error</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
            <div class="toast-body">
                {{ session('error') }}
            </div>
        </div>
    </div>
@endif

<!-- Unified Admin Modal (Create/Edit) -->
<div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.admins.store') }}" id="adminForm">
                @csrf
                <input type="hidden" name="_method" id="form_method" value="POST">
                <input type="hidden" name="admin_id" id="admin_id" value="">
                
                <div class="modal-header py-2 border-bottom-0">
                    <h6 class="modal-title fw-bold text-dark" id="adminModalLabel">
                        <i class="fas fa-user-plus me-2 text-primary" id="modal-icon"></i>
                        <span id="modal-title-text">Create New Team Member</span>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    @if($errors->any() && old('name'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- User Meta Details (Only for Edit) -->
                    <div id="user-meta-details" class="card bg-light border-0 mb-3" style="display: none;">
                        <div class="card-body p-2 d-flex align-items-center">
                            <div class="me-3">
                                <div class="admin-avatar shadow-sm" style="width: 40px; height: 40px; font-size: 1rem;" id="meta-avatar">
                                    <!-- Populated by JS -->
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark small" id="meta-name"></h6>
                                <div class="text-muted x-small">
                                    Member since <span id="meta-joined"></span> • ID: <span id="meta-id"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', '') }}" required 
                                   placeholder="Enter full name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', '') }}" required 
                                   placeholder="Enter email address">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                <option value="" disabled>Select a role</option>
                                @foreach($roles as $key => $role)
                                    @if(auth()->user()->role === 'accountant' && in_array($key, ['super_admin', 'admin', 'manager', 'accountant']))
                                        @continue
                                    @endif
                                    <option value="{{ $key }}" {{ old('role') == $key ? 'selected' : '' }} data-description="{{ $roleDescriptions[$key]['description'] ?? 'No description available' }}">
                                        {{ $role }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3 d-flex align-items-center">
                            <div class="form-check form-switch w-100 p-3 bg-light rounded border">
                                <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" id="is_active" 
                                       name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} style="width: 2.5em; height: 1.25em;">
                                <label class="form-check-label pt-1" for="is_active">
                                    <div class="fw-bold text-dark">Active Account</div>
                                    <div class="text-muted x-small">User can log in to system</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select class="form-select @error('department_id') is-invalid @enderror" id="department_id" name="department_id">
                                <option value="">None (Global)</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="monthly_salary" class="form-label">Monthly Salary (TZS)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 fw-bold text-muted">TZS</span>
                                <input type="number" step="0.01" class="form-control border-start-0 ps-0 @error('monthly_salary') is-invalid @enderror" 
                                       id="monthly_salary" name="monthly_salary" value="{{ old('monthly_salary', '0') }}" 
                                       placeholder="0.00">
                            </div>
                            @error('monthly_salary')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 fw-bold text-muted">+</span>
                                <input type="text" class="form-control border-start-0 ps-0 @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', '255') }}" 
                                       placeholder="255123456789">
                            </div>
                            <div class="form-text text-muted x-small">Start with country code (e.g. 255)</div>
                            @error('phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    
                    <div class="row" id="password-row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger" id="password-required">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" 
                                           minlength="8" placeholder="••••••••">
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text">Minimum 8 characters</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger" id="password-confirm-required">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password_confirmation" 
                                           name="password_confirmation" placeholder="••••••••">
                                    <button class="btn btn-outline-secondary toggle-password" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    
                    <div class="alert alert-info small mb-0" id="info-alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="info-text">After creating the user, an email with login instructions will be sent to the user's email address.</span>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="adminSubmit" data-no-global-handler>
                        <i class="fas fa-user-plus me-2" id="submit-icon"></i>
                        <span id="submit-text">Create User</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Pay Salary Modal -->
<div class="modal fade" id="paySalaryModal" tabindex="-1" aria-labelledby="paySalaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form id="paySalaryForm" method="POST" action="" data-no-global-handler>
                @csrf
                <div class="modal-header py-2 border-bottom-0">
                    <h6 class="modal-title fw-bold text-dark" id="paySalaryModalLabel">
                        <i class="fas fa-money-bill-wave me-2 text-success"></i>Pay Monthly Salary
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="icon-circle bg-light text-success mx-auto mb-3" style="width: 70px; height: 70px; font-size: 2rem; border-radius: 50%;">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <h5 class="fw-bold mb-1" id="salaryAdminName"></h5>
                        <p class="text-muted small">Record salary payment for this month</p>
                    </div>

                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small fw-bold text-uppercase">Monthly Salary</span>
                                <span class="h4 mb-0 fw-bold text-success" id="salaryAmountDisplay"></span>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="amount" id="salaryAmountInput">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Payment Method</label>
                            <select class="form-select border-0 shadow-sm bg-light" name="payment_method" required>
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Mobile Money">Mobile Money (M-Pesa/Airtel Money)</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Payment Date</label>
                            <input type="date" class="form-control border-0 shadow-sm bg-light" name="date" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small fw-bold text-dark">Notes (Internal)</label>
                        <textarea class="form-control border-0 shadow-sm bg-light" name="notes" rows="3" placeholder="e.g., Salary for October 2023"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4 border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm flex-grow-1 py-2" data-no-global-handler>
                        <i class="fas fa-check-circle me-2"></i>PROCESS SALARY PAYMENT
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Pay Salary Modal -->
<div class="modal fade" id="payAllSalariesModal" tabindex="-1" aria-labelledby="payAllSalariesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form id="payAllSalariesForm" method="POST" action="{{ route('admin.admins.pay-all') }}" data-no-global-handler>
                @csrf
                <div class="modal-header py-2 border-bottom-0">
                    <h6 class="modal-title fw-bold text-dark" id="payAllSalariesModalLabel">
                        <i class="fas fa-money-check-alt me-2 text-primary"></i>Bulk Salary Payment
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="icon-circle bg-light text-primary mx-auto mb-3" style="width: 70px; height: 70px; font-size: 2rem; border-radius: 50%;">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <h5 class="fw-bold mb-1">Process All Salaries</h5>
                        <p class="text-muted small">Record salary expenses for all eligible staff based on current filters</p>
                    </div>

                    <div class="alert alert-warning py-2 mb-4 d-flex align-items-center border-0 small">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span>This will create expense records for all filtered staff with a monthly salary.</span>
                    </div>

                    <!-- Hidden fields to pass current filters -->
                    <input type="hidden" name="role" value="{{ request('role') }}">
                    <input type="hidden" name="department_id" value="{{ request('department_id') }}">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Payment Method</label>
                            <select class="form-select border-0 shadow-sm bg-light" name="payment_method" required>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cash">Cash</option>
                                <option value="Mobile Money">Mobile Money (M-Pesa/Airtel Money)</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Salary Month</label>
                            <input type="text" class="form-control border-0 shadow-sm bg-light" name="month_year" value="{{ date('F Y') }}" placeholder="e.g., October 2023" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Payment Date</label>
                            <input type="date" class="form-control border-0 shadow-sm bg-light" name="date" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="mt-3 text-muted x-small">
                        <p class="mb-0"><i class="fas fa-info-circle me-1"></i> Hierarchy logic applies: you will only pay staff you are authorized to manage.</p>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4 border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm flex-grow-1 py-2" data-no-global-handler>
                        <i class="fas fa-check-double me-2"></i>PROCEED WITH BULK PAYMENT
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Modern Admin Avatar */
.admin-avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.125rem;
    overflow: hidden;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.25);
        transition: var(--transition);
    }

    .admin-avatar:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.35);
}

.admin-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

    /* Modern Card Styles */
.card {
        border: none;
        border-radius: var(--border-radius);
        box-shadow: var(--card-shadow);
        background: #ffffff;
        transition: var(--transition);
        overflow: hidden;
    }

    .card:hover {
        box-shadow: var(--card-shadow-hover);
}

.card-header {
        background: #dc3545;
        border: none;
        padding: 0.75rem 1.25rem;
}

    /* Modern Table Styles - Bootstrap 5 Compatible */
    .modern-admin-table {
        margin-bottom: 0;
        font-size: 13px !important;
    }

    .modern-admin-table thead th {
        font-size: 11px !important;
        font-weight: 700 !important;
        color: #495057 !important;
        background-color: #f8f9fa !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none !important;
        white-space: nowrap;
        vertical-align: middle;
        text-align: left !important;
    }

    .modern-admin-table tbody tr {
        transition: var(--transition);
        border-bottom: 1px solid #f1f3f5;
    }

    .modern-admin-table tbody tr:hover {
        background-color: #f8f9fa;
}

    .modern-admin-table tbody td {
    vertical-align: middle;
        border-top: 1px solid #f1f3f5;
}

    /* Action Buttons */
    .action-buttons .btn-action {
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        transition: var(--transition);
        border-width: 1.5px;
        font-weight: 500;
    }

    .action-buttons .btn-outline-primary {
        border-color: #0d6efd;
        color: #0d6efd;
    }

    .action-buttons .btn-outline-primary:hover {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        border-color: #0d6efd;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(13, 110, 253, 0.3);
}

    .action-buttons .btn-outline-danger {
        border-color: #dc3545;
        color: #dc3545;
    }

    .action-buttons .btn-outline-danger:hover {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border-color: #dc3545;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
    }

    .action-buttons .btn-outline-info {
        border-color: #0dcaf0;
        color: #0dcaf0;
    }

    .action-buttons .btn-outline-info:hover {
        background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
        border-color: #0dcaf0;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(13, 202, 240, 0.3);
    }

    /* Empty State */
    .empty-state {
        padding: 3rem 1rem;
    }

    .empty-state i {
        display: block;
        margin-bottom: 1rem;
}

    /* Modern Modal Enhancements */
.modal {
    z-index: 1055 !important;
}

.modal-dialog {
    z-index: 1056 !important;
    position: relative;
    margin: 1.75rem auto;
        max-width: 800px;
}

.modal-content {
    border: none;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    position: relative;
    z-index: 1057 !important;
    pointer-events: auto !important;
    background-color: #fff;
    display: flex;
    flex-direction: column;
    width: 100%;
        overflow: hidden;
}

.modal-header.bg-primary {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
        border: none;
        border-radius: 16px 16px 0 0;
        padding: 1.5rem;
    pointer-events: auto !important;
}

.modal-header .modal-title {
    font-weight: 600;
    font-size: 1.25rem;
        color: white;
        display: flex;
        align-items: center;
        gap: 0.5rem;
}

.modal-body {
        padding: 1.75rem;
    pointer-events: auto !important;
        background: #ffffff;
}

.modal-body input,
.modal-body select,
.modal-body textarea {
    pointer-events: auto !important;
    z-index: 1;
    position: relative;
        border-radius: 8px;
        border: 1.5px solid #e9ecef;
        transition: var(--transition);
        padding: 0.625rem 0.875rem;
    }

    .modal-body input:focus,
    .modal-body select:focus,
    .modal-body textarea:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.15);
}

.modal-footer.bg-light {
        background: #f8f9fa !important;
    border-top: 1px solid #e9ecef;
        border-radius: 0 0 16px 16px;
        padding: 1.25rem 1.75rem;
    pointer-events: auto !important;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }

    .modal-footer .btn {
        border-radius: 8px;
        padding: 0.625rem 1.25rem;
        font-weight: 600;
        transition: var(--transition);
    }

    .modal-footer .btn-primary {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border: none;
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.25);
    }

    .modal-footer .btn-primary:hover {
        background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.35);
    }

    .modal-footer .btn-secondary {
        background: #6c757d;
        border: none;
        color: white;
    }

    .modal-footer .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
}

.modal-backdrop {
    background-color: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
    z-index: 1054 !important;
    pointer-events: auto !important;
}

.modal.show .modal-dialog {
    transform: none;
}

    /* Form Controls Modern Styling */
    .form-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
}

    .form-control,
    .form-select {
        font-size: 0.9375rem;
        transition: var(--transition);
}

    .form-check-input:checked {
        background-color: #dc3545;
        border-color: #dc3545;
    }
    
    .alert-info {
        background: linear-gradient(135deg, rgba(13, 202, 240, 0.1) 0%, rgba(13, 202, 240, 0.05) 100%);
        border: 1px solid rgba(13, 202, 240, 0.2);
        border-radius: 8px;
        color: #0c5460;
    }

    .alert-danger {
        border-radius: 8px;
        border: none;
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.15);
    }
    
    /* Responsive Design - Using Bootstrap 5 Classes */
    @media (max-width: 991.98px) {
        .container-fluid {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
    }
    
        .page-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 1rem;
            margin-bottom: 1.5rem !important;
        }

        .page-header h2 {
            font-size: 1.5rem;
        margin-bottom: 0.25rem;
        }

        .page-header p {
            font-size: 0.8125rem;
        }

        .page-header .btn-primary {
            width: 100%;
            padding: 0.75rem 1.25rem;
            font-size: 0.9375rem;
    }
    
        /* Filter badges - scrollable on mobile */
        .d-flex.flex-wrap.gap-2 {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 0.5rem;
            margin-bottom: 1.25rem;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .d-flex.flex-wrap.gap-2::-webkit-scrollbar {
        display: none;
    }
    
        .filter-badge {
            font-size: 0.75rem;
            padding: 0.5rem 0.875rem;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .me-2.text-muted.fw-medium {
            font-size: 0.8125rem;
            white-space: nowrap;
            margin-right: 0.75rem !important;
        }

        /* Card improvements */
        .card {
            border-radius: 16px;
            overflow: hidden;
        }

        .card-header {
            padding: 1rem 1.25rem;
        }

        .card-header h6 {
            font-size: 0.875rem;
        }

        /* Table responsive adjustments - Bootstrap 5 table-responsive handles scrolling */
        .table-responsive {
            border-radius: 0;
        }

        .admin-avatar {
            width: 48px;
            height: 48px;
            font-size: 1.125rem;
        }

        .table td,
        .table th {
            padding: 0.75rem 0.5rem;
        }

        /* Empty state mobile */
        .empty-state {
            padding: 2rem 1rem;
        }

        .empty-state i {
            font-size: 3rem !important;
        }

        .empty-state h5 {
            font-size: 1rem;
        }


        /* Modal mobile improvements */
        .modal-dialog {
            margin: 0.5rem;
            max-width: calc(100% - 1rem);
            min-height: calc(100% - 1rem);
            display: flex;
            align-items: center;
        }

        .modal-content {
            border-radius: 20px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }

        .modal-header.bg-primary {
            padding: 1.25rem;
            border-radius: 20px 20px 0 0;
        }

        .modal-header .modal-title {
            font-size: 1.125rem;
        }

        .modal-body {
            padding: 1.25rem;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .modal-footer.bg-light {
            padding: 1rem 1.25rem;
            flex-direction: column-reverse;
            gap: 0.75rem;
            border-radius: 0 0 20px 20px;
        }

        .modal-footer .btn {
            width: 100%;
            padding: 0.75rem;
            font-size: 0.9375rem;
        }

        .modal-footer .btn-secondary {
            order: 2;
        }

        .modal-footer .btn-primary {
            order: 1;
        }

        /* Form controls mobile */
        .form-label {
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            font-size: 0.9375rem;
            padding: 0.75rem;
        }

        .form-text {
            font-size: 0.8125rem;
        }

        /* Alert mobile */
        .alert {
            font-size: 0.875rem;
            padding: 1rem;
        }

        .alert ul {
            margin-bottom: 0;
            padding-left: 1.25rem;
        }
    }

    @media (max-width: 767.98px) {
        .table td,
        .table th {
            padding: 0.625rem 0.5rem;
            font-size: 0.875rem;
        }

        .admin-avatar {
            width: 44px;
            height: 44px;
            font-size: 1rem;
        }

        .role-badge {
            font-size: 0.6875rem;
            padding: 0.375rem 0.625rem;
        }

        .badge {
            font-size: 0.6875rem;
            padding: 0.375rem 0.625rem;
        }

        .btn-action {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }

        .modal-dialog {
            margin: 0.5rem;
            max-width: calc(100% - 1rem);
        }

        .modal-header,
        .modal-body,
        .modal-footer {
            padding: 1rem;
        }

        .modal-footer {
            flex-direction: column-reverse;
            gap: 0.5rem;
        }

        .modal-footer .btn {
            width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        .container-fluid {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .page-header h2 {
            font-size: 1.375rem;
        }

        .filter-badge {
            font-size: 0.75rem;
            padding: 0.4375rem 0.75rem;
        }

        .card-header {
            padding: 0.875rem;
        }

        .table td,
        .table th {
            padding: 0.5rem 0.375rem;
            font-size: 0.8125rem;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            font-size: 0.9375rem;
        }

        .btn-action {
            padding: 0.4375rem 0.625rem;
            font-size: 0.8125rem;
        }
    }

    @media (max-width: 375px) {
        .admin-avatar {
            width: 36px;
            height: 36px;
            font-size: 0.875rem;
        }

        .btn-action {
            padding: 0.375rem 0.5rem;
            font-size: 0.75rem;
    }
}
</style>

<script>
// Global variables for modal handling
let isSubmitting = false;
const originalButtonText = '<i class="fas fa-user-plus me-2"></i>Create User';

// Cache DOM elements for better performance
let cachedElements = null;

function getCachedElements() {
    if (!cachedElements) {
        const form = document.getElementById('adminForm');
        if (!form) return null;
        
        cachedElements = {
            form: form,
            modal: document.getElementById('adminModal'),
            modalInstance: null,
            formMethod: document.getElementById('form_method'),
            adminId: document.getElementById('admin_id'),
            modalTitleText: document.getElementById('modal-title-text'),
            modalIcon: document.getElementById('modal-icon'),
            submitText: document.getElementById('submit-text'),
            submitIcon: document.getElementById('submit-icon'),
            passwordRow: document.getElementById('password-row'),
            passwordField: document.getElementById('password'),
            passwordConfirmField: document.getElementById('password_confirmation'),
            passwordRequired: document.getElementById('password-required'),
            passwordConfirmRequired: document.getElementById('password-confirm-required'),
            metaDetails: document.getElementById('user-meta-details'),
            metaName: document.getElementById('meta-name'),
            metaJoined: document.getElementById('meta-joined'),
            metaId: document.getElementById('meta-id'),
            metaAvatar: document.getElementById('meta-avatar'),
            isActive: document.getElementById('is_active'),
            infoAlert: document.getElementById('info-alert'),
            createBtn: document.getElementById('createAdminBtn'),
            createBtnText: document.getElementById('createBtnText')
        };
        
        // Initialize modal instance once
        if (cachedElements.modal) {
            cachedElements.modalInstance = bootstrap.Modal.getOrCreateInstance(cachedElements.modal);
        }
    }
    return cachedElements;
}

// Create new admin - Optimized version
function createAdmin() {
    const el = getCachedElements();
    if (!el || !el.form) {
        console.error('Required elements not found');
        return;
    }
    
    // Show immediate visual feedback
    if (el.createBtn) {
        const originalBtnContent = el.createBtn.innerHTML;
        el.createBtn.disabled = true;
        el.createBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Opening...';
        
        // Use requestAnimationFrame for smooth UI update
        requestAnimationFrame(() => {
            try {
                // Reset form efficiently
                el.form.reset();
                el.form.action = '{{ route('admin.admins.store') }}';
                el.formMethod.value = 'POST';
                el.adminId.value = '';
                
                // Batch DOM updates
                if (el.modalTitleText) el.modalTitleText.textContent = 'Create New Team Member';
                if (el.modalIcon) el.modalIcon.className = 'fas fa-user-plus me-2';
                if (el.submitText) el.submitText.textContent = 'Create User';
                if (el.submitIcon) el.submitIcon.className = 'fas fa-user-plus me-2';
                
                // Password fields
                if (el.passwordRow) el.passwordRow.style.display = 'flex';
                if (el.passwordField) {
                    el.passwordField.required = true;
                    el.passwordField.setAttribute('minlength', '8');
                }
                if (el.passwordConfirmField) el.passwordConfirmField.required = true;
                if (el.passwordRequired) el.passwordRequired.style.display = 'inline';
                if (el.passwordConfirmRequired) el.passwordConfirmRequired.style.display = 'inline';
                
                // Hide meta details
                if (el.metaDetails) el.metaDetails.style.display = 'none';

                // Active status
                if (el.isActive) el.isActive.checked = true;
                
                // Info alert
                if (el.infoAlert) {
                    el.infoAlert.style.display = 'block';
                    const infoText = el.infoAlert.querySelector('#info-text');
                    if (infoText) {
                        infoText.textContent = 'After creating the user, an email with login instructions will be sent to the user\'s email address.';
                    }
                }
                
                // Clear validation
                el.form.classList.remove('was-validated');
                
                // Clear errors efficiently
                const invalidFields = el.form.querySelectorAll('.is-invalid');
                invalidFields.forEach(field => field.classList.remove('is-invalid'));
                
                const errorAlerts = el.form.querySelectorAll('.alert-danger');
                errorAlerts.forEach(alert => alert.remove());
                
                // Show modal
                if (el.modalInstance) {
                    el.modalInstance.show();
                }
                
            } catch (error) {
                console.error('Error in createAdmin:', error);
            } finally {
                // Restore button state after a short delay
                setTimeout(() => {
                    if (el.createBtn) {
                        el.createBtn.disabled = false;
                        el.createBtn.innerHTML = originalBtnContent;
                    }
                }, 300);
            }
        });
    } else {
        // Fallback if button not found
        const form = document.getElementById('adminForm');
        if (!form) return;
        
        form.reset();
        form.action = '{{ route('admin.admins.store') }}';
        document.getElementById('form_method').value = 'POST';
        
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('adminModal'));
        modal.show();
    }
}

// Edit existing admin - Optimized version
function editAdmin(adminId, name, email, phone, role, isActive, joinedDate, avatarUrl, departmentId, salary) {
    const el = getCachedElements();
    if (!el || !el.form) {
        console.error('Required elements not found');
        return;
    }
    
    // Use requestAnimationFrame for smooth UI update
    requestAnimationFrame(() => {
        try {
            // Set form action and method
            el.form.action = `/admin/admins/${adminId}`;
            el.formMethod.value = 'PUT';
            el.adminId.value = adminId;
            
            // Populate form fields efficiently
            const nameField = document.getElementById('name');
            const emailField = document.getElementById('email');
            const phoneField = document.getElementById('phone');
            const roleField = document.getElementById('role');
            const deptField = document.getElementById('department_id');
            const salaryField = document.getElementById('monthly_salary');
            
            if (nameField) nameField.value = name || '';
            if (emailField) emailField.value = email || '';
            if (phoneField) phoneField.value = phone || '';
            if (roleField) roleField.value = role || '';
            if (deptField) deptField.value = departmentId || '';
            if (salaryField) salaryField.value = salary || '0';
            if (el.isActive) el.isActive.checked = isActive !== false;

            // Show meta details
            if (el.metaDetails) {
                el.metaDetails.style.display = 'block';
                if (el.metaName) el.metaName.textContent = name;
                if (el.metaJoined) el.metaJoined.textContent = joinedDate || 'N/A';
                if (el.metaId) el.metaId.textContent = adminId;
                
                // Set avatar
                if (el.metaAvatar) {
                    if (avatarUrl) {
                        el.metaAvatar.innerHTML = `<img src="${avatarUrl}" alt="${name}" class="rounded">`;
                    } else {
                        el.metaAvatar.innerHTML = name.charAt(0).toUpperCase();
                        // Reset gradient background just in case image covered it
                        el.metaAvatar.style.background = 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)';
                    }
                }
            }
            
            // Clear password fields (not required for edit)
            if (el.passwordField) {
                el.passwordField.value = '';
                el.passwordField.required = false;
                el.passwordField.removeAttribute('minlength');
            }
            if (el.passwordConfirmField) {
                el.passwordConfirmField.value = '';
                el.passwordConfirmField.required = false;
            }
            
            // Hide password required indicators
            if (el.passwordRequired) el.passwordRequired.style.display = 'none';
            if (el.passwordConfirmRequired) el.passwordConfirmRequired.style.display = 'none';
            
            // Update modal title and icon
            if (el.modalTitleText) el.modalTitleText.textContent = `Edit Admin: ${name}`;
            if (el.modalIcon) el.modalIcon.className = 'fas fa-user-edit me-2';
            if (el.submitText) el.submitText.textContent = 'Update Admin';
            if (el.submitIcon) el.submitIcon.className = 'fas fa-save me-2';
            
            // Hide info alert for edit mode
            if (el.infoAlert) {
                el.infoAlert.style.display = 'none';
                const infoText = el.infoAlert.querySelector('#info-text');
                if (infoText) {
                    infoText.textContent = 'Leave password fields blank to keep the current password.';
                }
            }
            
            // Clear validation
            el.form.classList.remove('was-validated');
            
            // Clear old error classes
            const invalidFields = el.form.querySelectorAll('.is-invalid');
            invalidFields.forEach(field => field.classList.remove('is-invalid'));
            
            // Hide any error alerts
            const errorAlerts = el.form.querySelectorAll('.alert-danger');
            errorAlerts.forEach(alert => alert.remove());
            
            // Show modal
            if (el.modalInstance) {
                el.modalInstance.show();
            }
        } catch (error) {
            console.error('Error in editAdmin:', error);
        }
    });
}

// Delete admin
function deleteAdmin(adminId, adminName) {
    if (confirm(`Are you sure you want to delete admin "${adminName}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/admins/${adminId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}

// Pay Salary
function paySalary(adminId, adminName, salaryAmount) {
    const modalElement = document.getElementById('paySalaryModal');
    const modal = new bootstrap.Modal(modalElement);
    const form = document.getElementById('paySalaryForm');
    const nameDisplay = document.getElementById('salaryAdminName');
    const amountDisplay = document.getElementById('salaryAmountDisplay');
    const amountInput = document.getElementById('salaryAmountInput');
    
    // Set form action dynamically
    form.action = `/admin/admins/${adminId}/pay-salary`;
    
    // Set data
    nameDisplay.textContent = adminName;
    amountDisplay.textContent = 'TZS ' + new Intl.NumberFormat().format(salaryAmount);
    amountInput.value = salaryAmount;
    
    // Set default notes
    const monthYear = new Intl.DateTimeFormat('en-US', { month: 'long', year: 'numeric' }).format(new Date());
    form.querySelector('textarea[name="notes"]').value = `Salary for ${monthYear}`;
    
    modal.show();
}

function payAllModal() {
    const modal = new bootstrap.Modal(document.getElementById('payAllSalariesModal'));
    modal.show();
}

// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    // Initialize cached elements
    getCachedElements();
    
    const el = getCachedElements();
    if (!el || !el.form || !document.getElementById('adminSubmit')) {
        console.error('Admin form or button not found');
        return;
    }
    
    const form = el.form;
    const submitBtn = document.getElementById('adminSubmit');
    const modal = el.modal;
    
    // Reopen modal if there are validation errors
    @if($errors->any() && old('name'))
        requestAnimationFrame(() => {
            // If editing (has admin_id in old data), populate for edit mode
            @if(old('admin_id'))
                editAdmin({{ old('admin_id') }}, '{{ addslashes(old('name')) }}', '{{ old('email') }}', '{{ old('phone') }}', '{{ old('role') }}', {{ old('is_active', true) ? 'true' : 'false' }}, '{{ old('created_at') }}', '');
            @else
                createAdmin();
            @endif
        });
    @endif
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        // Prevent double submission
        if (isSubmitting) {
            e.preventDefault();
            return false;
        }
        
        // Check if password is required (for create mode)
        const isEditMode = document.getElementById('form_method').value === 'PUT';
        const passwordField = document.getElementById('password');
        const passwordConfirmField = document.getElementById('password_confirmation');
        
        if (!isEditMode) {
            // Create mode - password is required
            passwordField.required = true;
            passwordConfirmField.required = true;
        } else {
            // Edit mode - password is optional, but if one is filled, both must be filled
            if (passwordField.value || passwordConfirmField.value) {
                passwordField.required = true;
                passwordConfirmField.required = true;
                if (passwordField.value.length < 8) {
                    e.preventDefault();
                    passwordField.setCustomValidity('Password must be at least 8 characters');
                    passwordField.reportValidity();
                    form.classList.add('was-validated');
                    return false;
                }
                if (passwordField.value !== passwordConfirmField.value) {
                    e.preventDefault();
                    passwordConfirmField.setCustomValidity('Passwords do not match');
                    passwordConfirmField.reportValidity();
                    form.classList.add('was-validated');
                    return false;
                }
            } else {
                passwordField.required = false;
                passwordConfirmField.required = false;
            }
            passwordField.setCustomValidity('');
            passwordConfirmField.setCustomValidity('');
        }
        
        // Validate form
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            form.classList.add('was-validated');
            return false;
        }
        
        // Set submitting state
        isSubmitting = true;
        submitBtn.disabled = true;
        const currentText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        
        // Auto-recovery after 10 seconds
        setTimeout(() => {
            if (isSubmitting) {
                isSubmitting = false;
                submitBtn.disabled = false;
                submitBtn.innerHTML = currentText;
                console.warn('Form submission timeout - resetting button');
            }
        }, 10000);
        
        // Form will submit naturally
        return true;
    });
    
    // Reset form when modal closes
    modal.addEventListener('hidden.bs.modal', function() {
        isSubmitting = false;
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalButtonText;
        form.reset();
        form.classList.remove('was-validated');
    
        // Reset form action to create
        form.action = '{{ route('admin.admins.store') }}';
        document.getElementById('form_method').value = 'POST';
        document.getElementById('admin_id').value = '';
        
        // Reset select if used, for now just normal select
        document.getElementById('role').value = '';
        
        // Set default phone to 255
        if(document.getElementById('phone')) document.getElementById('phone').value = '255';

        // Show info alert (for create mode default)
        document.getElementById('info-alert').style.display = 'block';
        
        // Clear all error messages
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        const errorAlerts = form.querySelectorAll('.alert-danger');
        errorAlerts.forEach(alert => alert.remove());
    });
                
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
                });
    });
});
</script>
@endsection
