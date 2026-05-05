@extends('layouts.admin')

@section('title', 'Customer Details - CHIBO BRAND Admin')
@section('description', 'View customer details and analytics')

@section('content')

<!-- Ensure CSRF token is available -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div class="d-flex align-items-center gap-3">
            @php
                $customerProfileImage = $customer->profile_image ?? null;
                $avatarSrc = !empty($customerProfileImage) ? asset('storage/' . $customerProfileImage) : asset('img/avatars/placeholder.png');
            @endphp
            <img
                src="{{ $avatarSrc }}"
                alt="{{ $customer->name }}"
                class="rounded-circle shadow-sm"
                style="width: 64px; height: 64px; object-fit: cover;"
                onerror="this.onerror=null; this.src='{{ asset('img/avatars/placeholder.png') }}';"
            >
            <div>
                <h2 class="h4 mb-1 fw-bold text-dark">{{ $customer->name }}</h2>
                <div class="d-flex align-items-center gap-2 text-muted small">
                    <span class="d-flex align-items-center gap-1"><i class="fas fa-envelope"></i> {{ $customer->email ?? 'No Email' }}</span>
                    <span class="mx-1">•</span>
                    <span class="d-flex align-items-center gap-1"><i class="fas fa-phone"></i> {{ $customer->phone }}</span>
                    @if($customer->verified)
                        <span class="badge bg-success-subtle text-success border border-success-subtle ms-2 rounded-pill">Verified</span>
                    @else
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-2 rounded-pill">Unverified</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
            @if(auth()->user()->hasPermission('manage_customers') || auth()->user()->role === 'accountant')
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-cog me-2"></i>Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                    <li><a class="dropdown-item" href="{{ route('admin.customers.edit', $customer) }}"><i class="fas fa-edit me-2 text-muted"></i>Edit Profile</a></li>
                    
                    @if(!$customer->verified)
                        <li><button class="dropdown-item text-success" onclick="verifyCustomerAjax(this)"><i class="fas fa-check me-2"></i>Verify Account</button></li>
                    @else
                        <li><button class="dropdown-item text-warning" onclick="unverifyCustomer()"><i class="fas fa-times me-2"></i>Unverify (Test)</button></li>
                    @endif

                    <li>
                        <form method="POST" action="{{ route('admin.customers.status.update', $customer) }}" class="d-inline">
                            @csrf @method('PUT')
                            <input type="hidden" name="is_active" value="{{ $customer->is_active ? 0 : 1 }}">
                            <button type="submit" class="dropdown-item {{ $customer->is_active ? 'text-warning' : 'text-success' }}" data-no-global-handler>
                                <i class="fas {{ $customer->is_active ? 'fa-pause' : 'fa-play' }} me-2"></i>{{ $customer->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </li>
                    @if($templates->count() > 0)
                        <li><hr class="dropdown-divider"></li>
                        <li class="dropdown-header">Send Message</li>
                        @foreach($templates as $template)
                            <li>
                                <form action="{{ route('admin.message-templates.send') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="template_id" value="{{ $template->id }}">
                                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                                    <button type="submit" class="dropdown-item" data-no-global-handler>
                                        <i class="fas fa-paper-plane me-2 text-muted"></i>{{ \Illuminate\Support\Str::limit($template->title, 20) }}
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" id="deleteCustomerForm">
                            @csrf @method('DELETE')
                            <button type="button" class="dropdown-item text-danger" onclick="modernConfirm('Delete Customer?', 'This action cannot be undone.', () => document.getElementById('deleteCustomerForm').submit(), { title: 'Delete Customer', type: 'danger', icon: 'fa-trash', confirmText: 'Delete' })">
                                <i class="fas fa-trash me-2"></i>Delete Customer
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
            @endif
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Spend -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden text-white" style="background: linear-gradient(135deg, #0d6efd, #0a58ca);">
                <div class="card-body position-relative p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="bg-white bg-opacity-25 rounded p-2"><i class="fas fa-wallet fa-lg"></i></div>
                        <span class="badge bg-white bg-opacity-25">Lifetime</span>
                    </div>
                    <h3 class="fw-bold mb-1">TZS {{ number_format($lifetimeTotal, 0) }}</h3>
                    <div class="small opacity-75">All-time Contribution</div>
                    <!-- Decorative Circle -->
                    <div class="position-absolute top-0 end-0 translate-middle p-4 rounded-circle bg-white opacity-10" style="width: 120px; height: 120px; margin-right: -40px; margin-top: -40px;"></div>
                </div>
            </div>
        </div>
        
        <!-- Orders Stats -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded p-1 bg-success-subtle text-success"><i class="fas fa-shopping-cart"></i></span>
                            <span class="fw-bold text-dark">Orders</span>
                            <span class="badge bg-light text-muted border x-small">{{ $period == '6_months' ? '6M' : ucfirst(str_replace('_', ' ', $period)) }}</span>
                        </div>
                        <span class="text-success fw-bold small">{{ $totalOrders }}</span>
                    </div>
                    <h4 class="fw-bold mb-1 text-dark">TZS {{ number_format($totalOrderValue, 0) }}</h4>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: {{ $periodTotal > 0 ? ($totalOrderValue / $periodTotal) * 100 : 0 }}%"></div>
                    </div>
                    <small class="text-muted mt-2 d-block">Avg. Order: TZS {{ number_format($averageOrderValue, 0) }}</small>
                </div>
            </div>
        </div>

        <!-- Design Tasks Stats -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded p-1 bg-purple-subtle text-purple"><i class="fas fa-paint-brush"></i></span>
                            <span class="fw-bold text-dark">Tasks</span>
                            <span class="badge bg-light text-muted border x-small">{{ $period == '6_months' ? '6M' : ucfirst(str_replace('_', ' ', $period)) }}</span>
                        </div>
                        <span class="text-purple fw-bold small">{{ $totalTasks }}</span>
                    </div>
                    <h4 class="fw-bold mb-1 text-dark">TZS {{ number_format($totalTaskValue, 0) }}</h4>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-purple" style="width: {{ $periodTotal > 0 ? ($totalTaskValue / $periodTotal) * 100 : 0 }}%"></div>
                    </div>
                    <small class="text-muted mt-2 d-block">{{ $periodTotal > 0 ? round(($totalTaskValue / $periodTotal) * 100) : 0 }}% of period total</small>
                </div>
            </div>
        </div>

        <!-- Account Status -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-bold small">TYPE</span>
                        @if($customer->is_wholesale)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Wholesale</span>
                        @else
                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">Retail</span>
                        @endif
                    </div>
                    <div class="d-flex flex-column gap-2 mt-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Status</span>
                            @if($customer->is_active)
                                <span class="badge bg-success-subtle text-success rounded-pill">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger rounded-pill">Inactive</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Joined</span>
                            <span class="text-dark small fw-medium">{{ $customer->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="row">
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom pt-4 px-4 pb-0 rounded-top-4">
                    <style>
                        /* Fix for transparent/white nav links from global admin.css */
                        .card-header-tabs .nav-link {
                            color: #6c757d !important;
                            background: transparent !important;
                            border: none !important;
                            border-bottom: 2px solid transparent !important;
                            border-radius: 0 !important;
                            padding-bottom: 1rem !important;
                        }
                        .card-header-tabs .nav-link:hover {
                            color: #0d6efd !important;
                            background: transparent !important;
                            border-bottom: 2px solid rgba(13, 110, 253, 0.3) !important;
                        }
                        .card-header-tabs .nav-link.active {
                            color: #0d6efd !important;
                            background: transparent !important;
                            border-bottom: 2px solid #0d6efd !important;
                            box-shadow: none !important;
                        }
                        .card-header-tabs .nav-link::before {
                            display: none !important;
                        }
                        .card-header-tabs .nav-link i {
                            font-size: 1rem;
                        }
                    </style>
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active fw-medium" data-bs-toggle="tab" href="#overview" role="tab"><i class="fas fa-chart-pie me-2"></i>Overview</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-medium" data-bs-toggle="tab" href="#orders" role="tab"><i class="fas fa-shopping-cart me-2"></i>Orders</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-medium" data-bs-toggle="tab" href="#tasks" role="tab"><i class="fas fa-palette me-2"></i>Design Tasks</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-medium" data-bs-toggle="tab" href="#gallery" role="tab"><i class="fas fa-images me-2"></i>Design Gallery</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content">
                        <!-- Overview Tab -->
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h6 class="fw-bold mb-0">Contribution Analysis</h6>
                                <form method="GET" action="{{ url()->current() }}" data-no-global-handler class="d-flex align-items-center gap-2">
                                    <select name="period" id="periodSelect" class="form-select form-select-sm rounded-pill px-3 border-0 shadow-sm text-uppercase fw-bold x-small" style="background-color: #f8f9fa; cursor: pointer;">
                                        <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Today</option>
                                        <option value="yesterday" {{ $period == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                                        <option value="week" {{ $period == 'week' ? 'selected' : '' }}>This Week</option>
                                        <option value="month" {{ $period == 'month' ? 'selected' : '' }}>This Month</option>
                                        <option value="6_months" {{ $period == '6_months' ? 'selected' : '' }}>Last 6 Months</option>
                                        <option value="year" {{ $period == 'year' ? 'selected' : '' }}>This Year</option>
                                        <option value="2_years" {{ $period == '2_years' ? 'selected' : '' }}>Last 2 Years</option>
                                        <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom Range</option>
                                        <option value="all" {{ $period == 'all' ? 'selected' : '' }}>All Time</option>
                                    </select>
                                    <div id="customDateRange" class="d-flex align-items-center gap-1 {{ ($period ?? '') == 'custom' ? '' : 'd-none' }}">
                                        <input type="date" name="start_date" class="form-control form-control-sm rounded-pill border-0 shadow-sm x-small" value="{{ request('start_date') }}" style="width: 110px;">
                                        <span class="x-small text-muted">to</span>
                                        <input type="date" name="end_date" class="form-control form-control-sm rounded-pill border-0 shadow-sm x-small" value="{{ request('end_date') }}" style="width: 110px;">
                                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-2 x-small"><i class="fas fa-check"></i></button>
                                    </div>
                                </form>
                            </div>
                            <div style="height: 300px; width: 100%;">
                                <canvas id="contributionChart"></canvas>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-3">Participation Breakdown</h6>
                                    <div style="height: 200px; position: relative;">
                                        <canvas id="breakdownChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-light border h-100 rounded-3">
                                        <h6 class="alert-heading fw-bold d-flex align-items-center"><i class="fas fa-lightbulb text-warning me-2"></i>Insights</h6>
                                        <p class="small text-muted mb-2 mt-2">
                                            This customer primarily engages via 
                                            @if($totalOrderValue > $totalTaskValue)
                                                <strong>Orders</strong> which make up {{ round(($totalOrderValue / max($periodTotal,1)) * 100) }}% of their total volume over the selected period.
                                            @else
                                                <strong>Design Services</strong> which make up {{ round(($totalTaskValue / max($periodTotal,1)) * 100) }}% of their total volume over the selected period.
                                            @endif
                                        </p>
                                        <div class="mt-3 text-muted x-small">
                                            <div class="mb-1"><i class="fas fa-clock me-2"></i>Last Active: <strong>{{ $customer->updated_at->diffForHumans() }}</strong></div>
                                            <div><i class="fas fa-sign-in-alt me-2"></i>Last Login: <strong>{{ $customer->last_login_at ? $customer->last_login_at->format('M d, H:i') : 'Never' }}</strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Orders Tab -->
                        <div class="tab-pane fade" id="orders" role="tabpanel">
                            @if($orders->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="small fw-bold border-0 rounded-start ps-3">Code</th>
                                                <th class="small fw-bold border-0">Date</th>
                                                <th class="small fw-bold border-0">Items</th>
                                                <th class="small fw-bold border-0">Total</th>
                                                <th class="small fw-bold border-0">Payment</th>
                                                <th class="small fw-bold border-0">Status</th>
                                                <th class="small fw-bold border-0 rounded-end text-end pe-3">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($orders as $order)
                                                <tr>
                                                    <td class="ps-3"><span class="font-monospace fw-bold text-primary x-small">{{ $order->order_code }}</span></td>
                                                    <td class="small text-muted">{{ $order->created_at->format('M d, Y') }}</td>
                                                    <td class="small">{{ $order->items->count() }}</td>
                                                    <td class="fw-bold text-dark small">{{ $order->formatted_total }}</td>
                                                    <td>
                                                        @if($order->payment_status == 'paid')
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill x-small">Paid</span>
                                                        @elseif($order->payment_status == 'partial')
                                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill x-small">Partial</span>
                                                        @else
                                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill x-small">Unpaid</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($order->approval_status == 'approved')
                                                            <span class="badge bg-success-subtle text-success rounded-pill x-small">Approved</span>
                                                        @elseif($order->approval_status == 'cancelled')
                                                            <span class="badge bg-danger-subtle text-danger rounded-pill x-small">Cancelled</span>
                                                        @else
                                                            <span class="badge bg-warning-subtle text-warning rounded-pill x-small">Pending</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end pe-3">
                                                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-icon btn-light border shadow-sm rounded-circle"><i class="fas fa-eye small"></i></a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                        <i class="fas fa-shopping-cart text-muted opacity-50"></i>
                                    </div>
                                    <p class="text-muted small mt-2">No orders found for this period.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Tasks Tab -->
                        <div class="tab-pane fade" id="tasks" role="tabpanel">
                            @if($designTasks->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="small fw-bold border-0 rounded-start ps-3">Code</th>
                                                <th class="small fw-bold border-0">Title</th>
                                                <th class="small fw-bold border-0">Deadline</th>
                                                <th class="small fw-bold border-0">Price</th>
                                                <th class="small fw-bold border-0">Paid</th>
                                                <th class="small fw-bold border-0">Status</th>
                                                <th class="small fw-bold border-0 rounded-end text-end pe-3">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($designTasks as $task)
                                                <tr>
                                                    <td class="ps-3"><span class="font-monospace fw-bold text-purple x-small">{{ $task->task_code }}</span></td>
                                                    <td class="small fw-medium">{{ \Illuminate\Support\Str::limit($task->title, 25) }}</td>
                                                    <td class="small text-muted">{{ $task->deadline ? $task->deadline->format('M d') : '-' }}</td>
                                                    <td class="fw-bold text-dark small">TZS {{ number_format($task->price, 0) }}</td>
                                                    <td class="text-success small fw-medium">TZS {{ number_format($task->amount_paid, 0) }}</td>
                                                    <td>
                                                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill x-small">{{ $task->status_label }}</span>
                                                    </td>
                                                    <td class="text-end pe-3">
                                                        <a href="{{ route('admin.design-tasks.show', $task->id) }}" class="btn btn-sm btn-icon btn-light border shadow-sm rounded-circle"><i class="fas fa-eye small"></i></a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                        <i class="fas fa-paint-brush text-muted opacity-50"></i>
                                    </div>
                                    <p class="text-muted small mt-2">No design tasks found for this period.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Gallery Tab -->
                        <div class="tab-pane fade" id="gallery" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="fw-bold mb-0">Design Gallery</h6>
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#uploadDesignModal">
                                    <i class="fas fa-upload me-2"></i>Upload Design
                                </button>
                            </div>

                            <div class="row g-3">
                                @forelse($customer->designs as $design)
                                    <div class="col-6 col-md-4 col-lg-3">
                                        <div class="card h-100 border-0 shadow-sm overflow-hidden design-card position-relative">
                                            @php
                                                $extension = strtolower(pathinfo($design->image_path, PATHINFO_EXTENSION));
                                                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                                            @endphp
                                            <a href="{{ asset('storage/' . $design->image_path) }}" target="_blank">
                                                @if($isImage)
                                                    <img src="{{ asset('storage/' . $design->image_path) }}" class="card-img-top" alt="{{ $design->title }}" style="height: 180px; object-fit: cover;">
                                                @else
                                                    <div class="card-img-top d-flex flex-column align-items-center justify-content-center bg-light text-primary" style="height: 180px;">
                                                        @if($extension === 'pdf')
                                                            <i class="fas fa-file-pdf fa-4x mb-2 text-danger"></i>
                                                        @elseif($extension === 'psd')
                                                            <i class="fas fa-file-alt fa-4x mb-2" style="color: #31a8ff;"></i>
                                                        @elseif($extension === 'ai')
                                                            <i class="fas fa-file-alt fa-4x mb-2" style="color: #ff9a00;"></i>
                                                        @else
                                                            <i class="fas fa-file fa-4x mb-2"></i>
                                                        @endif
                                                        <span class="x-small fw-bold text-uppercase">{{ $extension }} File</span>
                                                    </div>
                                                @endif
                                            </a>
                                            @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin')
                                                <div class="position-absolute top-0 end-0 p-2">
                                                    <form action="{{ route('admin.customers.designs.destroy', $design) }}" method="POST" onsubmit="return confirm('Delete this design photo?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger rounded-circle shadow-sm" style="width: 30px; height: 30px; padding: 0;">
                                                            <i class="fas fa-trash-alt small"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                            <div class="card-body p-2">
                                                <div class="fw-bold small text-truncate">{{ $design->title ?? 'Untitled Design' }}</div>
                                                <div class="x-small text-muted">By {{ $design->user->name }} • {{ $design->created_at->format('M d, Y') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-5">
                                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                            <i class="fas fa-images text-muted opacity-50"></i>
                                        </div>
                                        <p class="text-muted small mt-2">No photos in gallery yet.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar Profile -->
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom rounded-top-4">
                    <h6 class="mb-0 fw-bold">Profile Details</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label class="text-muted x-small text-uppercase fw-bold mb-1">Company Info</label>
                        <div class="fw-bold text-dark">{{ $customer->company_name ?? 'Individual Details' }}</div>
                        <div class="small text-muted">{{ $customer->business_type ?? 'N/A' }}</div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="text-muted x-small text-uppercase fw-bold mb-1">Contact Information</label>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-center gap-3 mb-2">
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-light text-muted" style="width: 32px; height: 32px;"><i class="fas fa-envelope small"></i></div>
                                <div class="text-dark small">{{ $customer->email }}</div>
                            </li>
                            <li class="d-flex align-items-center gap-3 mb-2">
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-light text-muted" style="width: 32px; height: 32px;"><i class="fas fa-phone small"></i></div>
                                <div class="text-dark small">{{ $customer->phone }}</div>
                            </li>
                            @if($customer->whatsapp_number)
                            <li class="d-flex align-items-center gap-3 mb-2">
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success" style="width: 32px; height: 32px;"><i class="fab fa-whatsapp small"></i></div>
                                <div class="text-dark small">{{ $customer->whatsapp_number }}</div>
                            </li>
                            @endif
                            @if($customer->website)
                            <li class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-light text-muted" style="width: 32px; height: 32px;"><i class="fas fa-globe small"></i></div>
                                <a href="{{ $customer->website }}" target="_blank" class="text-primary small text-decoration-none">{{ parse_url($customer->website, PHP_URL_HOST) }}</a>
                            </li>
                            @endif
                        </ul>
                    </div>

                    @if($customer->address)
                    <div class="mb-4">
                        <label class="text-muted x-small text-uppercase fw-bold mb-1">Address</label>
                        <div class="d-flex gap-3">
                            <div class="d-flex align-items-start justify-content-center rounded-circle bg-light text-muted flex-shrink-0" style="width: 32px; height: 32px; margin-top: 2px;"><i class="fas fa-map-marker-alt small"></i></div>
                            <div class="text-dark small">{{ $customer->address }}</div>
                        </div>
                    </div>
                    @endif

                    @if($customer->tax_id)
                    <div class="mb-4">
                        <label class="text-muted x-small text-uppercase fw-bold mb-1">Tax ID / TIN</label>
                        <div class="font-monospace bg-light rounded px-3 py-2 small d-inline-block text-dark border">{{ $customer->tax_id }}</div>
                    </div>
                    @endif

                    @if($customer->notes)
                    <div>
                        <label class="text-muted x-small text-uppercase fw-bold mb-1">Notes</label>
                        <div class="bg-warning-subtle text-dark p-3 rounded small border border-warning-subtle">
                            <i class="fas fa-sticky-note text-warning me-2"></i>{{ $customer->notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Prepare Data from PHP
        const labels = @json($months);
        const orderData = @json($orderData);
        const taskData = @json($taskData);

        // Chart Config
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'top',
                    labels: { usePointStyle: true, boxWidth: 6 }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(25, 25, 25, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#495057',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('en-TZ', { style: 'currency', currency: 'TZS' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [2, 2], drawBorder: false },
                    ticks: {
                        callback: function(value) {
                            return 'TZS ' + (value/1000) + 'k';
                        }
                    }
                },
                x: {
                    grid: { display: false }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        };

        // Contribution Chart
        const ctx = document.getElementById('contributionChart');
        if (ctx) {
            new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Orders',
                            data: orderData,
                            borderColor: '#198754',
                            backgroundColor: 'rgba(25, 135, 84, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#198754',
                            pointRadius: 4
                        },
                        {
                            label: 'Design Tasks',
                            data: taskData,
                            borderColor: '#6f42c1',
                            backgroundColor: 'rgba(111, 66, 193, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#6f42c1',
                            pointRadius: 4
                        }
                    ]
                },
                options: commonOptions
            });
        }

        // Breakdown Chart
        const ctxPie = document.getElementById('breakdownChart');
        if (ctxPie) {
            new Chart(ctxPie.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Orders', 'Design Tasks'],
                    datasets: [{
                        data: [{{ $totalOrderValue ?? 0 }}, {{ $totalTaskValue ?? 0 }}],
                        backgroundColor: ['#198754', '#6f42c1'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                    }
                }
            });
        }

        // Period Selection Handling
        const periodSelect = document.getElementById('periodSelect');
        const customDateRange = document.getElementById('customDateRange');
        
        if (periodSelect) {
            periodSelect.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customDateRange.classList.remove('d-none');
                } else {
                    customDateRange.classList.add('d-none');
                    this.form.submit();
                }
            });
        }
    });

    // Verification Logic (Preserved from previous implementation)
    function verifyCustomerAjax(button) {
        const url = '{{ route("admin.customers.verify", $customer) }}';
        const token = document.querySelector('meta[name="csrf-token"]').content;
        
        button.disabled = true;
        const originalText = button.innerHTML;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying...';
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok || response.redirected) {
                location.reload();
            } else {
                throw new Error('Verification failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            button.disabled = false;
            button.innerHTML = originalText;
            alert('Verification failed. Please try again.');
        });
    }

    function unverifyCustomer() {
        if(confirm('Are you sure you want to unverify this customer?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.customers.unverify", $customer) }}';
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
<!-- Upload Design Modal -->
<div class="modal fade" id="uploadDesignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-upload me-2"></i>Upload Design File</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.customers.designs.store', $customer) }}" method="POST" enctype="multipart/form-data" data-no-global-handler>
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Design File <span class="text-danger">*</span></label>
                        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.pdf,.psd,.ai" required>
                        <div class="form-text x-small">Supported: JPG, PNG, SVG, PDF, PSD, AI. Max 20MB.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Design Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Logo Design for Summer Event">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small">Notes / Description</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Additional details about this design..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill" data-no-global-handler>Upload Design</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
