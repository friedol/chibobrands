@extends('layouts.admin')

@section('title', 'Customer Management - CHIBO BRAND Admin')
@section('description', 'Manage customer accounts and verification')

@push('styles')
<style>
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
    
    .btn-more {
        background-color: rgba(108, 117, 125, 0.1);
        color: #6c757d;
    }
    
    .btn-more:hover, .btn-more[aria-expanded="true"] {
        background-color: #6c757d;
        color: white;
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

    .x-small { font-size: 11px !important; }
    .ls-1 { letter-spacing: 0.5px; }
    
    /* Font size refinements */
    body { font-size: 13px !important; }
    .table thead th { font-size: 11px !important; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: #495057; }
    .table td { font-size: 13px !important; }
    .badge { font-weight: 600; padding: 0.5em 0.8em; font-size: 11px !important; }
    .form-control, .form-select, .btn, .modal-body label { font-size: 13px !important; }
    .modal-title { font-size: 15px !important; }
    h4, .h4 { font-size: 14px !important; }
    .text-muted.small { font-size: 12px !important; }
    .avatar-circle { font-size: 13px !important; }

    /* Compact Stat Card Styles */
    .cust-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 9px 11px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: all 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .cust-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .cust-stat-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
    }
    .cust-stat-val {
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.25;
        margin-top: 4px;
    }
    .cust-stat-lbl {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        margin-top: 1px;
    }
    .cust-stat-sub {
        font-size: 10px;
        font-weight: 500;
        color: #94a3b8;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header & Stats -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-2 text-dark">Customer Management</h4>
        </div>
        @if(in_array(auth()->user()->role, ['admin', 'super_admin', 'manager']))
        <div class="d-flex gap-2">
            <a href="{{ route('admin.customers.duplicates') }}" class="btn btn-outline-warning text-dark d-flex align-items-center gap-2 shadow-sm">
                <i class="fas fa-object-group text-warning"></i>
                <span>Merge Duplicates</span>
            </a>
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
                <i class="fas fa-plus"></i>
                <span>Add Customer</span>
            </a>
        </div>
        @elseif(auth()->user()->hasPermission('manage_customers') || auth()->user()->role === 'accountant')
        <div>
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
                <i class="fas fa-plus"></i>
                <span>Add Customer</span>
            </a>
        </div>
        @endif
    </div>

    @if(($duplicateCount ?? 0) > 0)
    <div class="dup-warning-banner shadow-sm rounded-3 d-flex align-items-center justify-content-between mb-3 p-3" style="background:#fffbe6; border: 1.5px solid #ffe58f;">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px; background:#ffe58f; color:#d48806;">
                <i class="fas fa-exclamation-triangle fa-lg"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0" style="color:#873800;">Duplicate Phone Numbers Detected ({{ $duplicateCount }} Groups Found)</h6>
                <small style="color:#b76e00;">Customers with variant phone formats (e.g. 0687123456 vs +255687123456) were detected. Consolidate them into single customer profiles.</small>
            </div>
        </div>
        <a href="{{ route('admin.customers.duplicates') }}" class="btn fw-bold rounded-pill px-4 btn-sm flex-shrink-0" style="background:#faad14; color:#fff; border:none;">
            <i class="fas fa-object-group me-1"></i> Review & Merge Records
        </a>
    </div>
    @endif

    @if ($errors->any())
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-danger-subtle mb-0" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-exclamation-circle text-danger"></i>
                    <ul class="mb-0 list-unstyled small fw-bold">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @endif

    <!-- Stats Cards Row 1 -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="cust-stat-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="cust-stat-icon bg-primary-subtle text-primary"><i class="fas fa-users"></i></div>
                    <span class="cust-stat-sub">+{{ $stats['new_this_week'] }} week</span>
                </div>
                <div class="cust-stat-val text-dark">{{ number_format($stats['total']) }}</div>
                <div class="cust-stat-lbl">Total Customers</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('admin.customers.index', ['status'=>'new']) }}" class="text-decoration-none">
                <div class="cust-stat-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="cust-stat-icon bg-success-subtle text-success"><i class="fas fa-user-plus"></i></div>
                        <span class="cust-stat-sub">+{{ $stats['new_this_week_new'] }} week</span>
                    </div>
                    <div class="cust-stat-val text-success">{{ number_format($stats['new_customers']) }}</div>
                    <div class="cust-stat-lbl">New Customers</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('admin.customers.index', ['status'=>'repeated']) }}" class="text-decoration-none">
                <div class="cust-stat-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="cust-stat-icon" style="background:#e0e7ff;color:#4f46e5;"><i class="fas fa-redo"></i></div>
                        <span class="cust-stat-sub">+{{ $stats['repeated_this_week'] }} week</span>
                    </div>
                    <div class="cust-stat-val" style="color:#4f46e5;">{{ number_format($stats['repeated_customers']) }}</div>
                    <div class="cust-stat-lbl">Repeat Customers</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="cust-stat-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="cust-stat-icon bg-info-subtle text-info"><i class="fas fa-check-circle"></i></div>
                    <span class="cust-stat-sub">{{ $stats['verified_percent'] }}%</span>
                </div>
                <div class="cust-stat-val text-dark">{{ number_format($stats['verified']) }}</div>
                <div class="cust-stat-lbl">Verified Accounts</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="cust-stat-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="cust-stat-icon" style="background:#f3e8ff;color:#9333ea;"><i class="fas fa-briefcase"></i></div>
                    <span class="cust-stat-sub">B2B</span>
                </div>
                <div class="cust-stat-val" style="color:#9333ea;">{{ number_format($stats['wholesale']) }}</div>
                <div class="cust-stat-lbl">Wholesale Partners</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="cust-stat-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="cust-stat-icon bg-warning-subtle text-warning"><i class="fas fa-user-clock"></i></div>
                    <span class="cust-stat-sub">Awaiting</span>
                </div>
                <div class="cust-stat-val text-warning">{{ number_format($stats['pending']) }}</div>
                <div class="cust-stat-lbl">Pending Verification</div>
            </div>
        </div>
    </div>

    <!-- Revenue & Segmentation Cards Row 2 -->
    <div class="row g-2 mb-3">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="cust-stat-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="cust-stat-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-coins"></i></div>
                    <span class="cust-stat-sub">New Seg.</span>
                </div>
                <div class="cust-stat-val text-success" style="font-size:1.02rem;">TZS {{ number_format($stats['new_customer_revenue'] ?? 0) }}</div>
                <div class="cust-stat-lbl">New Customer Revenue</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="cust-stat-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="cust-stat-icon" style="background:#e0e7ff;color:#4f46e5;"><i class="fas fa-hand-holding-usd"></i></div>
                    <span class="cust-stat-sub">Repeat Seg.</span>
                </div>
                <div class="cust-stat-val" style="font-size:1.02rem;color:#4f46e5;">TZS {{ number_format($stats['repeated_customer_revenue'] ?? 0) }}</div>
                <div class="cust-stat-lbl">Repeat Customer Revenue</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="cust-stat-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="cust-stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-building"></i></div>
                    <span class="cust-stat-sub">Wholesale</span>
                </div>
                <div class="cust-stat-val" style="font-size:1.02rem;color:#d97706;">{{ number_format($stats['new_customers_wholesale'] ?? 0) }}</div>
                <div class="cust-stat-lbl">New Customers: Wholesale</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="cust-stat-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="cust-stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-shopping-bag"></i></div>
                    <span class="cust-stat-sub">Retail</span>
                </div>
                <div class="cust-stat-val" style="font-size:1.02rem;color:#2563eb;">{{ number_format($stats['new_customers_retail'] ?? 0) }}</div>
                <div class="cust-stat-lbl">New Customers: Retail</div>
            </div>
        </div>
    </div>

    <!-- Export, Filter & Toolbar -->
    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
        {{-- Export actions --}}
        @if(auth()->user()->role !== 'saler')
        <div class="d-flex gap-2">
            <a href="{{ route('admin.customers.export.excel', request()->all()) }}"
               class="btn btn-sm btn-outline-success rounded-2 fw-bold px-3" data-no-preloader data-no-global-handler>
                <i class="fas fa-file-excel me-1"></i>Excel
            </a>
            <a href="{{ route('admin.customers.export.pdf', request()->all()) }}" target="_blank"
               class="btn btn-sm btn-outline-danger rounded-2 fw-bold px-3" data-no-preloader data-no-global-handler>
                <i class="fas fa-file-pdf me-1"></i>PDF
            </a>
        </div>
        @endif
        
        {{-- Filter trigger + My Customers toggle --}}
        <div class="d-flex gap-2">
            @if(in_array(auth()->user()->role, ['saler', 'senior_saler']))
                @php $ownOnly = request()->boolean('own_only'); @endphp
                <a href="{{ route('admin.customers.index', array_merge(request()->except('own_only', 'page'), $ownOnly ? [] : ['own_only' => '1'])) }}"
                   class="btn btn-sm rounded-2 fw-bold px-3 d-flex align-items-center gap-1 {{ $ownOnly ? 'btn-primary' : 'btn-outline-primary' }}"
                   title="{{ $ownOnly ? 'Showing My Customers — click to show all' : 'Show only my customers' }}">
                    <i class="fas fa-user me-1"></i>{{ $ownOnly ? 'My Customers' : 'All Customers' }}
                </a>
            @endif
            <button class="btn btn-sm btn-outline-secondary rounded-2 fw-bold px-3 d-flex align-items-center gap-1"
                    data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                <i class="fas fa-sliders-h"></i>Filter
            </button>
        </div>
    </div>

    @php
        $hasFilters = request()->anyFilled(['search', 'status', 'period', 'saler_id', 'own_only']);
    @endphp

    {{-- ── Advanced Filter Collapse ── --}}
    <div class="collapse {{ $hasFilters ? 'show' : '' }} mb-4" id="filterCollapse">
        <div class="card filter-card border-0 shadow-sm" style="border-radius:10px; border:1px solid #e2e8f0; background:#f8fafc;">
            <div class="card-body p-3">
                <form action="{{ route('admin.customers.index') }}" method="GET" class="row g-2" data-no-global-handler>
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1" style="font-size:10px; color:#64748b;">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0"
                                   placeholder="Name, phone, email, company…" value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1" style="font-size:10px; color:#64748b;">Status / Segment</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="all">All Stages</option>
                            <option value="verified"   {{ request('status') == 'verified'   ? 'selected' : '' }}>Verified</option>
                            <option value="unverified" {{ request('status') == 'unverified' ? 'selected' : '' }}>Unverified</option>
                            <option value="wholesale"  {{ request('status') == 'wholesale'  ? 'selected' : '' }}>Wholesale Partner</option>
                            <option value="active"     {{ request('status') == 'active'     ? 'selected' : '' }}>Active Status</option>
                            <option value="new"        {{ request('status') == 'new'        ? 'selected' : '' }}>New Customer</option>
                            <option value="repeated"   {{ request('status') == 'repeated'   ? 'selected' : '' }}>Repeat Customer</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1" style="font-size:10px; color:#64748b;">Joined Period</label>
                        <select name="period" class="form-select form-select-sm">
                            <option value="all"   {{ request('period') == 'all'   ? 'selected' : '' }}>All Time</option>
                            <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="week"  {{ request('period') == 'week'  ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>This Month</option>
                            <option value="year"  {{ request('period') == 'year'  ? 'selected' : '' }}>This Year</option>
                        </select>
                    </div>
                    @if(in_array(auth()->user()->role, ['admin','super_admin','accountant']))
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1" style="font-size:10px; color:#64748b;">Salesperson (Brought By)</label>
                        <select name="saler_id" class="form-select form-select-sm">
                            <option value="all">All Salespeople</option>
                            @foreach($salers as $s)
                                <option value="{{ $s->id }}" {{ request('saler_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-12 col-md-auto d-flex align-items-end gap-2 ms-md-auto">
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold rounded-2 flex-fill flex-md-grow-0">Apply</button>
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-sm px-3 fw-bold rounded-2 flex-fill flex-md-grow-0">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <!-- Filters & Content -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom py-3">
            <div class="row g-3 align-items-center justify-content-between">
                <div class="col-12 col-md-4">
                    <form method="GET" class="position-relative">
                        <i class="fas fa-search text-muted position-absolute top-50 start-0 translate-middle-y ms-3"></i>
                        <input type="text" name="search" class="form-control form-control-solid ps-5 rounded-pill bg-light border-0" 
                               placeholder="Search..." value="{{ request('search') }}">
                    </form>
                </div>
                <div class="col-12 col-md-auto d-flex gap-2 text-nowrap overflow-auto pb-1 pb-md-0">
                    <a href="{{ route('admin.customers.index') }}" 
                       class="btn btn-sm rounded-pill px-3 {{ !request('status') ? 'btn-dark' : 'btn-light border' }}">
                        All
                    </a>
                    <a href="{{ route('admin.customers.index', ['status' => 'verified']) }}" 
                       class="btn btn-sm rounded-pill px-3 {{ request('status') == 'verified' ? 'btn-dark' : 'btn-light border' }}">
                        Verified
                    </a>
                    <a href="{{ route('admin.customers.index', ['status' => 'unverified']) }}" 
                       class="btn btn-sm rounded-pill px-3 {{ request('status') == 'unverified' ? 'btn-dark' : 'btn-light border' }}">
                        Unverified
                    </a>
                    <a href="{{ route('admin.customers.index', ['status' => 'wholesale']) }}" 
                       class="btn btn-sm rounded-pill px-3 {{ request('status') == 'wholesale' ? 'btn-dark' : 'btn-light border' }}">
                        Wholesale
                    </a>
                    <a href="{{ route('admin.customers.index', ['status' => 'active']) }}"
                       class="btn btn-sm rounded-pill px-3 {{ request('status') == 'active' ? 'btn-dark' : 'btn-light border' }}">
                        Active
                    </a>
                    <a href="{{ route('admin.customers.index', ['status' => 'new']) }}"
                       class="btn btn-sm rounded-pill px-3 {{ request('status') == 'new' ? 'btn-success' : 'btn-light border' }}">
                        <i class="fas fa-user-plus me-1"></i>New
                    </a>
                    <a href="{{ route('admin.customers.index', ['status' => 'repeated']) }}"
                       class="btn btn-sm rounded-pill px-3 {{ request('status') == 'repeated' ? 'btn-primary' : 'btn-light border' }}">
                        <i class="fas fa-redo me-1"></i>Repeated
                    </a>
                    @if(request()->has('status') || request()->has('search'))
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 ms-2">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            @if($customers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 text-secondary text-uppercase x-small fw-bold border-0" style="width: 25%;">Customer</th>
                                <th class="text-secondary text-uppercase x-small fw-bold border-0" style="width: 20%;">Contact Info</th>
                                <th class="text-secondary text-uppercase x-small fw-bold border-0" style="width: 20%;">Business</th>
                                <th class="text-secondary text-uppercase x-small fw-bold border-0" style="width: 15%;">Status</th>
                                <th class="text-secondary text-uppercase x-small fw-bold border-0" style="width: 10%;">Joined</th>
                                <th class="pe-4 text-end text-secondary text-uppercase x-small fw-bold border-0" style="width: 10%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $customer)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            @php
                                                $customerProfileImage = $customer->profile_image ?? null;
                                                $avatarSrc = !empty($customerProfileImage) ? asset('storage/' . $customerProfileImage) : asset('img/avatars/placeholder.png');
                                            @endphp
                                            <img
                                                src="{{ $avatarSrc }}"
                                                alt="{{ $customer->name }}"
                                                class="rounded-circle shadow-sm me-3"
                                                style="width: 40px; height: 40px; min-width: 40px; object-fit: cover;"
                                                onerror="this.onerror=null; this.src='{{ asset('img/avatars/placeholder.png') }}';"
                                            >
                                            <div>
                                                <div class="fw-bold text-dark mb-0 text-truncate" style="max-width: 200px;">{{ $customer->name }}</div>
                                                <div class="d-flex gap-1 flex-wrap mt-1">
                                                    @if($customer->is_repeated)
                                                        <span class="badge rounded-pill px-2" style="background:#e0e7ff;color:#4338ca;font-size:9px;font-weight:700;">
                                                            <i class="fas fa-redo me-1"></i>REPEAT
                                                        </span>
                                                    @else
                                                        <span class="badge rounded-pill px-2" style="background:#dcfce7;color:#15803d;font-size:9px;font-weight:700;">
                                                            <i class="fas fa-star me-1"></i>NEW
                                                        </span>
                                                    @endif
                                                    @if($customer->is_wholesale)
                                                        <span class="badge bg-purple-subtle text-purple border border-purple-subtle rounded-pill x-small">Wholesale</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fas fa-envelope text-muted small" style="width: 16px;"></i>
                                                <span class="text-muted small text-truncate" style="max-width: 150px;" title="{{ $customer->email }}">{{ $customer->email }}</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fas fa-phone text-muted small" style="width: 16px;"></i>
                                                <span class="text-muted small">{{ $customer->phone }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($customer->company_name)
                                            <div class="fw-medium text-dark text-truncate" style="max-width: 150px;">{{ $customer->company_name }}</div>
                                            @if($customer->business_type)
                                                <div class="text-muted x-small">{{ $customer->business_type }}</div>
                                            @endif
                                        @else
                                            <span class="text-muted small fs-italic">Individual</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            @if($customer->verified)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill w-fit-content">Verified</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill w-fit-content">Unverified</span>
                                            @endif
                                            
                                            @if(!$customer->is_active)
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill w-fit-content">Inactive</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-dark small fw-medium">{{ $customer->created_at->format('M d, Y') }}</span>
                                            <span class="text-muted x-small">{{ $customer->created_at->diffForHumans() }}</span>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('admin.customers.show', $customer) }}" 
                                               class="btn-action btn-view" 
                                               title="View Details" 
                                               data-bs-toggle="tooltip">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @php
                                                $canEdit = auth()->user()->hasPermission('manage_customers') || 
                                                          auth()->user()->role === 'accountant' ||
                                                          (auth()->user()->role === 'saler' && 
                                                           (auth()->user()->id === $customer->added_by || auth()->user()->id === $customer->account_owner_id));
                                            @endphp
                                            
                                            @if($canEdit)
                                             <a href="{{ route('admin.customers.edit', $customer) }}" 
                                                class="btn-action btn-edit" 
                                                title="Edit Profile"
                                                data-bs-toggle="tooltip">
                                                 <i class="fas fa-edit"></i>
                                             </a>
                                             @endif

                                            @if((auth()->user()->hasPermission('manage_customers') || auth()->user()->role === 'accountant' || (auth()->user()->role === 'saler' && (auth()->user()->id === $customer->added_by || auth()->user()->id === $customer->account_owner_id))) && (!$customer->verified || $templates->count() > 0))
                                                <div class="dropdown d-inline-block">
                                                    <button class="btn-action btn-more" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-no-global-handler>
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg small">
                                                        @if(!$customer->verified)
                                                        <li>
                                                            <form method="POST" action="{{ route('admin.customers.verify', $customer) }}">
                                                                @csrf
                                                                <button type="submit" class="dropdown-item py-2 d-flex align-items-center gap-2 text-warning" data-no-global-handler>
                                                                    <i class="fas fa-check fa-fw"></i> Verify Manually
                                                                </button>
                                                            </form>
                                                        </li>
                                                        @endif
                                                        
                                                        @if($templates->count() > 0)
                                                            @if(!$customer->verified)
                                                                <li><hr class="dropdown-divider my-1"></li>
                                                            @endif
                                                            <li class="dropdown-header text-uppercase x-small fw-bold text-muted">Send Message</li>
                                                            @foreach($templates as $template)
                                                                <li>
                                                                    <form action="{{ route('admin.message-templates.send') }}" method="POST">
                                                                        @csrf
                                                                        <input type="hidden" name="template_id" value="{{ $template->id }}">
                                                                        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                                                                        <button type="submit" class="dropdown-item py-2 d-flex align-items-center gap-2" data-no-global-handler>
                                                                            <i class="fas fa-paper-plane text-info fa-fw opacity-50"></i> {{ \Illuminate\Support\Str::limit($template->title, 20) }}
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            @endforeach
                                                        @endif
                                                    </ul>
                                                </div>
                                            @endif

                                            @if(auth()->user()->hasPermission('manage_customers') || auth()->user()->role === 'accountant' || (auth()->user()->role === 'saler' && (auth()->user()->id === $customer->added_by || auth()->user()->id === $customer->account_owner_id)))
                                            <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" id="deleteForm{{ $customer->id }}" class="d-inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn-action btn-delete" 
                                                        title="Delete Customer"
                                                        data-bs-toggle="tooltip"
                                                        onclick="modernConfirm('Delete Customer?', 'Are you sure you want to delete this customer? This action cannot be undone.', () => document.getElementById('deleteForm{{ $customer->id }}').submit(), { title: 'Delete Customer', type: 'danger', icon: 'fa-trash', confirmText: 'Delete' })">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-users text-muted opacity-50" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="text-dark fw-bold">No Customers Found</h5>
                    <p class="text-muted small mb-4">
                        @if(request('search') || request('status'))
                            No customers match your current filters.
                        @else
                            No customers have registered yet.
                        @endif
                    </p>
                    @if(auth()->user()->hasPermission('manage_customers') || auth()->user()->role === 'accountant')
                    <a href="{{ route('admin.customers.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fas fa-plus me-2"></i>Add New Customer
                    </a>
                    @endif
                </div>
            @endif
        </div>
        
        @if($customers->hasPages())
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="text-muted small">
                        Showing <span class="fw-bold">{{ $customers->firstItem() }}</span> to <span class="fw-bold">{{ $customers->lastItem() }}</span> of <span class="fw-bold">{{ $customers->total() }}</span> results
                    </div>
                    <div class="pagination-wrapper">
                        {{ $customers->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endpush

@endsection
