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
    <div class="row g-4">
        <div class="col-12">
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
                        <li class="nav-item">
                            <a class="nav-link fw-medium" data-bs-toggle="tab" href="#timeline" role="tab"><i class="fas fa-stream me-2 text-primary"></i>Customer Timeline</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-medium" data-bs-toggle="tab" href="#businesses" role="tab"><i class="fas fa-building me-2 text-success"></i>Businesses Owned <span class="badge bg-light text-dark border ms-1">{{ $businesses->count() }}</span></a>
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

                        <!-- Customer Timeline Tab -->
                        <div class="tab-pane fade" id="timeline" role="tabpanel">
                            <h6 class="fw-bold mb-4"><i class="fas fa-stream text-primary me-2"></i>Complete Customer Timeline</h6>

                            <div class="d-flex flex-column gap-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white border-bottom py-3 px-4">
                                        <h6 class="mb-0 fw-bold">Lead Information</h6>
                                    </div>
                                    <div class="card-body p-4">
                                        @forelse($leads as $lead)
                                            <div class="border rounded-3 p-3 mb-3 {{ $loop->last ? '' : 'mb-3' }}">
                                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
                                                    <div>
                                                        <div class="fw-bold text-dark">{{ $lead->customer_name }}</div>
                                                        <div class="small text-muted">Created {{ $lead->created_at->format('d M Y, H:i') }}</div>
                                                    </div>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">{{ ucfirst($lead->status ?? 'pending') }}</span>
                                                        <span class="badge bg-light text-dark border rounded-pill">{{ $lead->source ?? 'Direct' }}</span>
                                                    </div>
                                                </div>
                                                <div class="row g-2 small text-dark">
                                                    <div class="col-md-6"><strong>Lead source:</strong> {{ $lead->source ?? 'Direct' }}</div>
                                                    <div class="col-md-6"><strong>Assigned salesperson:</strong> {{ $lead->seller->name ?? 'Unassigned' }}</div>
                                                    <div class="col-md-6"><strong>Lead notes:</strong> {{ $lead->customer_response ?? $lead->last_follow_up_notes ?? 'No notes recorded' }}</div>
                                                    <div class="col-md-6"><strong>Lead creation date:</strong> {{ $lead->created_at->format('d M Y, H:i') }}</div>
                                                </div>
                                                @if($lead->followUps->count() > 0)
                                                    <div class="mt-3 pt-3 border-top">
                                                        <div class="small fw-bold text-uppercase text-muted mb-2">Lead Follow-up Notes</div>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm align-middle mb-0">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th class="small">Date</th>
                                                                        <th class="small">Notes</th>
                                                                        <th class="small">Responsible Staff</th>
                                                                        <th class="small">Status</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($lead->followUps as $followUp)
                                                                        <tr>
                                                                            <td class="small text-muted">{{ optional($followUp->follow_up_date)->format('d M Y') ?? $followUp->created_at->format('d M Y') }}</td>
                                                                            <td class="small">{{ $followUp->notes }}</td>
                                                                            <td class="small">{{ $followUp->user->name ?? 'Staff' }}</td>
                                                                            <td class="small"><span class="badge bg-secondary-subtle text-dark border rounded-pill">Logged</span></td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-center py-4 text-muted">No lead records found for this customer.</div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white border-bottom py-3 px-4">
                                        <h6 class="mb-0 fw-bold">Follow-up History</h6>
                                    </div>
                                    <div class="card-body p-4">
                                        @forelse($customerFollowUps as $followUp)
                                            <div class="border rounded-3 p-3 mb-3">
                                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
                                                    <div class="fw-bold text-dark">{{ $followUp->action ?? 'Follow-up' }}</div>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">{{ $followUp->follow_up_date->format('d M Y') }}</span>
                                                        <span class="badge bg-light text-dark border rounded-pill">{{ $followUp->follow_up_date->isPast() && ! $followUp->follow_up_date->isToday() ? 'Overdue' : ($followUp->follow_up_date->isToday() ? 'Due Today' : 'Scheduled') }}</span>
                                                    </div>
                                                </div>
                                                <div class="row g-2 small text-dark">
                                                    <div class="col-md-4"><strong>Follow-up type:</strong> {{ $followUp->action ?? 'General' }}</div>
                                                    <div class="col-md-4"><strong>Responsible staff member:</strong> {{ $followUp->user->name ?? 'Staff' }}</div>
                                                    <div class="col-md-4"><strong>Follow-up status:</strong> {{ $followUp->follow_up_date->isPast() && ! $followUp->follow_up_date->isToday() ? 'Overdue' : ($followUp->follow_up_date->isToday() ? 'Due Today' : 'Scheduled') }}</div>
                                                    <div class="col-12"><strong>Communication notes:</strong> {{ $followUp->notes ?? 'No notes provided' }}</div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-4 text-muted">No follow-up activity found.</div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white border-bottom py-3 px-4">
                                        <h6 class="mb-0 fw-bold">Sales History</h6>
                                    </div>
                                    <div class="card-body p-4">
                                        @forelse($orders as $order)
                                            @php
                                                $productNames = $order->items->map(function ($item) {
                                                    return $item->product_name ?: ($item->product->name ?? 'Product');
                                                })->filter()->take(3)->implode(', ');
                                                $productLabel = $productNames ?: 'No products listed';
                                                if ($order->items->count() > 3) {
                                                    $productLabel .= ' +' . ($order->items->count() - 3) . ' more';
                                                }
                                            @endphp
                                            <div class="border rounded-3 p-3 mb-3">
                                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
                                                    <div>
                                                        <div class="fw-bold text-dark">Order {{ $order->order_code }}</div>
                                                        <div class="small text-muted">Purchased {{ $order->created_at->format('d M Y, H:i') }}</div>
                                                    </div>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">{{ ucfirst($order->payment_status ?? 'unpaid') }}</span>
                                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">{{ ucfirst($order->approval_status ?? 'pending') }}</span>
                                                    </div>
                                                </div>
                                                <div class="row g-2 small text-dark">
                                                    <div class="col-md-6"><strong>Products purchased:</strong> {{ $productLabel }}</div>
                                                    <div class="col-md-6"><strong>Sales representative:</strong> {{ $order->saler->name ?? $order->user->name ?? 'Unassigned' }}</div>
                                                    <div class="col-md-6"><strong>Business profile:</strong> {{ $order->customerBusiness->business_name ?? 'Main customer profile' }}</div>
                                                    <div class="col-md-6"><strong>Branch:</strong> {{ $order->department->name ?? 'Head Office' }}</div>
                                                    <div class="col-md-6"><strong>Transaction status:</strong> {{ ucfirst($order->payment_status ?? 'unpaid') }} / {{ ucfirst($order->approval_status ?? 'pending') }}</div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-4 text-muted">No sales orders found.</div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white border-bottom py-3 px-4">
                                        <h6 class="mb-0 fw-bold">Payment History</h6>
                                    </div>
                                    <div class="card-body p-4">
                                        @forelse($payments as $payment)
                                            @php
                                                $linkedBalance = optional($payment->designTask)->balance ?? optional($payment->order)->balance;
                                                $linkedReference = $payment->invoice_reference ?? optional($payment->order)->order_code ?? optional($payment->designTask)->task_code ?? 'N/A';
                                            @endphp
                                            <div class="border rounded-3 p-3 mb-3">
                                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
                                                    <div class="fw-bold text-dark">TZS {{ number_format((float) $payment->amount, 0) }}</div>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">{{ strtoupper($payment->payment_method ?? 'Cash') }}</span>
                                                        <span class="badge bg-light text-dark border rounded-pill">{{ $payment->date ? $payment->date->format('d M Y') : $payment->created_at->format('d M Y') }}</span>
                                                    </div>
                                                </div>
                                                <div class="row g-2 small text-dark">
                                                    <div class="col-md-4"><strong>Payment date:</strong> {{ $payment->date ? $payment->date->format('d M Y') : $payment->created_at->format('d M Y') }}</div>
                                                    <div class="col-md-4"><strong>Amount paid:</strong> TZS {{ number_format((float) $payment->amount, 0) }}</div>
                                                    <div class="col-md-4"><strong>Outstanding balance:</strong> {{ is_null($linkedBalance) ? 'N/A' : 'TZS ' . number_format((float) $linkedBalance, 0) }}</div>
                                                    <div class="col-md-4"><strong>Reference:</strong> {{ $linkedReference }}</div>
                                                    <div class="col-md-4"><strong>Business profile:</strong> {{ optional($payment->customerBusiness)->business_name ?? optional(optional($payment->designTask)->customerBusiness)->business_name ?? optional(optional($payment->order)->customerBusiness)->business_name ?? 'Main customer profile' }}</div>
                                                    <div class="col-md-4"><strong>Responsible staff:</strong> {{ $payment->seller->name ?? 'Staff' }}</div>
                                                    <div class="col-md-4"><strong>Payment method:</strong> {{ strtoupper($payment->payment_method ?? 'Cash') }}</div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-4 text-muted">No payment records found.</div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white border-bottom py-3 px-4">
                                        <h6 class="mb-0 fw-bold">Debt History</h6>
                                    </div>
                                    <div class="card-body p-4">
                                        @forelse($debtPayments as $debtPayment)
                                            @php
                                                $debtBalance = optional($debtPayment->designTask)->balance ?? optional($debtPayment->order)->balance;
                                                $debtProgress = optional($debtPayment->designTask)->amount_paid ?? optional($debtPayment->order)->amount_paid;
                                                $debtReference = $debtPayment->invoice_reference ?? optional($debtPayment->order)->order_code ?? optional($debtPayment->designTask)->task_code ?? 'N/A';
                                            @endphp
                                            <div class="border rounded-3 p-3 mb-3">
                                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
                                                    <div class="fw-bold text-dark">Debt transaction {{ $debtReference }}</div>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">{{ ucfirst($debtPayment->debt_status_label) }}</span>
                                                        <span class="badge bg-light text-dark border rounded-pill">{{ $debtPayment->date ? $debtPayment->date->format('d M Y') : $debtPayment->created_at->format('d M Y') }}</span>
                                                    </div>
                                                </div>
                                                <div class="row g-2 small text-dark">
                                                    <div class="col-md-4"><strong>Credit transaction:</strong> TZS {{ number_format((float) $debtPayment->amount, 0) }}</div>
                                                    <div class="col-md-4"><strong>Debt creation date:</strong> {{ $debtPayment->created_at->format('d M Y, H:i') }}</div>
                                                    <div class="col-md-4"><strong>Debt status:</strong> {{ $debtPayment->debt_status_label }}</div>
                                                    <div class="col-md-4"><strong>Payment progress:</strong> {{ is_null($debtBalance) ? 'N/A' : 'TZS ' . number_format((float) $debtProgress, 0) . ' / TZS ' . number_format((float) (($debtProgress ?? 0) + ($debtBalance ?? 0)), 0) }}</div>
                                                    <div class="col-md-4"><strong>Outstanding balance:</strong> {{ is_null($debtBalance) ? 'N/A' : 'TZS ' . number_format((float) $debtBalance, 0) }}</div>
                                                    <div class="col-md-4"><strong>Business profile:</strong> {{ optional($debtPayment->customerBusiness)->business_name ?? optional(optional($debtPayment->designTask)->customerBusiness)->business_name ?? optional(optional($debtPayment->order)->customerBusiness)->business_name ?? 'Main customer profile' }}</div>
                                                    <div class="col-md-4"><strong>Responsible staff:</strong> {{ $debtPayment->seller->name ?? 'Staff' }}</div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-4 text-muted">No debt history found.</div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white border-bottom py-3 px-4">
                                        <h6 class="mb-0 fw-bold">Communication History</h6>
                                    </div>
                                    <div class="card-body p-4">
                                        @forelse($smsRecipients as $sms)
                                            <div class="border rounded-3 p-3 mb-3">
                                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
                                                    <div class="fw-bold text-dark">SMS sent to {{ $sms->recipient_name ?? $customer->name }}</div>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <span class="badge bg-secondary-subtle text-dark border rounded-pill">{{ ucfirst($sms->status ?? 'sent') }}</span>
                                                        <span class="badge bg-light text-dark border rounded-pill">{{ optional($sms->sent_at ?? $sms->created_at)->format('d M Y, H:i') }}</span>
                                                    </div>
                                                </div>
                                                <div class="row g-2 small text-dark">
                                                    <div class="col-md-6"><strong>Communication date:</strong> {{ optional($sms->sent_at ?? $sms->created_at)->format('d M Y, H:i') }}</div>
                                                    <div class="col-md-6"><strong>Responsible user:</strong> {{ $sms->campaign->sender->name ?? 'System' }}</div>
                                                    <div class="col-12"><strong>Messages:</strong> {{ $sms->campaign->message ?? 'SMS sent' }}</div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-4 text-muted">No SMS or communication records found.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Businesses Owned Tab -->
                        <div class="tab-pane fade" id="businesses" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h6 class="fw-bold mb-0"><i class="fas fa-building text-success me-2"></i>Multiple Business Profiles</h6>
                                    <small class="text-muted">Manage all businesses owned and registered under this customer profile.</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addBusinessModal">
                                    <i class="fas fa-plus me-1"></i> Add Business
                                </button>
                            </div>

                            <div class="row g-3">
                                @forelse($businesses as $b)
                                    <div class="col-12 col-md-6">
                                        <div class="card border shadow-sm h-100">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h6 class="fw-bold mb-0 text-dark">{{ $b->business_name }}</h6>
                                                        @if($b->is_primary)
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill x-small mt-1">Primary Profile</span>
                                                        @endif
                                                        <small class="text-muted">{{ $b->business_type ?? 'Business Profile' }}</small>
                                                    </div>
                                                    <form action="{{ route('admin.customers.businesses.destroy', $b->id) }}" method="POST" onsubmit="return confirm('Remove this business profile?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-1">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                                <ul class="list-unstyled x-small text-muted mb-0">
                                                    <li><i class="fas fa-globe me-2 text-primary"></i>Country: <strong>{{ $b->country }}</strong></li>
                                                    @if($b->region)
                                                        <li><i class="fas fa-map-marker-alt me-2 text-danger"></i>Location: <strong>{{ $b->region->region_name }} {{ $b->district ? '- '.$b->district->district_name : '' }}</strong></li>
                                                    @endif
                                                    @if($b->address)
                                                        <li><i class="fas fa-compass me-2 text-secondary"></i>Address: {{ $b->address }}</li>
                                                    @endif
                                                    @if($b->phone)
                                                        <li><i class="fas fa-phone me-2 text-success"></i>Contact Phone: {{ $b->phone }}</li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-5">
                                        <i class="fas fa-city text-muted fa-3x mb-3 opacity-50"></i>
                                        <h6 class="fw-bold">No Additional Businesses Registered</h6>
                                        <p class="text-muted small">Click "Add Business" above to register additional company profiles owned by this customer.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                </div>
            </div>
        </div>
        
    </div>

    <div class="row g-4 mt-0">
        <!-- Sidebar Profile -->
        <div class="col-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom rounded-top-4">
                    <h6 class="mb-0 fw-bold">Profile Details</h6>
                </div>
                <div class="card-body p-4">
                    <!-- Customer Ownership & Audit Details Block -->
                    <div class="mb-4 p-3 bg-light rounded-3 border border-danger-subtle">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="text-danger x-small text-uppercase fw-bold mb-0"><i class="fas fa-shield-alt me-1"></i> Customer Ownership</label>
                            @if(in_array(auth()->user()->role, ['admin', 'super_admin', 'manager']))
                                <button class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill x-small" data-bs-toggle="modal" data-bs-target="#transferOwnershipModal">
                                    <i class="fas fa-exchange-alt me-1"></i> Transfer
                                </button>
                            @endif
                        </div>
                        <ul class="list-unstyled mb-0 small">
                            <li class="d-flex justify-content-between py-1 border-bottom border-light-subtle">
                                <span class="text-muted">Registered By:</span>
                                <span class="fw-semibold text-dark">{{ $customer->registeredBy->name ?? ($customer->addedBy->name ?? 'Online / System') }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom border-light-subtle">
                                <span class="text-muted">Registration Date:</span>
                                <span class="fw-semibold text-dark">{{ $customer->created_at->format('M d, Y H:i') }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom border-light-subtle">
                                <span class="text-muted">Branch:</span>
                                <span class="fw-semibold text-dark">{{ $customer->branch->name ?? 'Head Office' }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom border-light-subtle">
                                <span class="text-muted">Account Owner:</span>
                                <span class="fw-bold text-danger">{{ $customer->accountOwner->name ?? ($customer->addedBy->name ?? 'Unassigned') }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-1">
                                <span class="text-muted">Customer Source:</span>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">{{ $customer->customer_source ?? 'N/A' }}</span>
                            </li>
                        </ul>
                    </div>

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

                    @if($customer->region || $customer->district)
                    <div class="mb-4">
                        <label class="text-muted x-small text-uppercase fw-bold mb-1">Location</label>
                        <div class="d-flex gap-3">
                            <div class="d-flex align-items-start justify-content-center rounded-circle bg-light text-muted flex-shrink-0" style="width: 32px; height: 32px; margin-top: 2px;"><i class="fas fa-compass small"></i></div>
                            <div class="text-dark small">
                                @if($customer->region)
                                    {{ $customer->region->region_name }}
                                @endif
                                @if($customer->district)
                                    - {{ $customer->district->district_name }}
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

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

<!-- Transfer Ownership Modal -->
@if(in_array(auth()->user()->role, ['admin', 'super_admin', 'manager']))
<div class="modal fade" id="transferOwnershipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-exchange-alt text-danger me-2"></i>Transfer Customer Ownership</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.customers.transfer-ownership', $customer) }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="p-3 bg-light rounded-3 mb-3 small">
                        <div><strong>Customer:</strong> {{ $customer->name }}</div>
                        <div><strong>Current Owner:</strong> {{ $customer->accountOwner->name ?? ($customer->addedBy->name ?? 'Unassigned') }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Select New Account Owner <span class="text-danger">*</span></label>
                        <select name="account_owner_id" class="form-select rounded-3" required>
                            <option value="">-- Choose New Owner --</option>
                            @php $staffList = \App\Models\User::whereIn('role', ['saler', 'admin', 'super_admin', 'manager'])->orderBy('name')->get(); @endphp
                            @foreach($staffList as $staff)
                                <option value="{{ $staff->id }}" {{ $customer->account_owner_id == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }} ({{ ucfirst(str_replace('_', ' ', $staff->role)) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Reason for Transfer</label>
                        <textarea name="transfer_reason" class="form-control rounded-3" rows="3" placeholder="Reason for reassigning customer ownership..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4">Confirm Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Add Business Profile Modal -->
<div class="modal fade" id="addBusinessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('admin.customers.businesses.store', $customer->id) }}">
                @csrf
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title fw-bold text-dark"><i class="fas fa-building text-success me-2"></i>Add Business Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    @if($errors->has('business_name') || $errors->has('business_type') || $errors->has('phone') || $errors->has('email') || $errors->has('country') || $errors->has('region_id') || $errors->has('district_id') || $errors->has('address'))
                        <div class="alert alert-danger small py-2">
                            Please correct the highlighted business profile fields and try again.
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-bold small">BUSINESS NAME <span class="text-danger">*</span></label>
                        <input type="text" name="business_name" class="form-control @error('business_name') is-invalid @enderror" placeholder="e.g. Chibo Tech Store" required style="font-size:12px;" value="{{ old('business_name') }}">
                        @error('business_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">BUSINESS TYPE</label>
                        <select name="business_type" class="form-select @error('business_type') is-invalid @enderror" style="font-size:12px;">
                            <option value="">Select Business Type</option>
                            <option value="Retail Store" {{ old('business_type') === 'Retail Store' ? 'selected' : '' }}>Retail Store</option>
                            <option value="Wholesale Distributor" {{ old('business_type') === 'Wholesale Distributor' ? 'selected' : '' }}>Wholesale Distributor</option>
                            <option value="Printing Company" {{ old('business_type') === 'Printing Company' ? 'selected' : '' }}>Printing Company</option>
                            <option value="Advertising Agency" {{ old('business_type') === 'Advertising Agency' ? 'selected' : '' }}>Advertising Agency</option>
                            <option value="Corporate" {{ old('business_type') === 'Corporate' ? 'selected' : '' }}>Corporate</option>
                            <option value="Other" {{ old('business_type') === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('business_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">PHONE</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="+255..." style="font-size:12px;" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">EMAIL</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="biz@example.com" style="font-size:12px;" value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">COUNTRY <span class="text-danger">*</span></label>
                        <select name="country" id="biz-country-select" class="form-select fw-bold @error('country') is-invalid @enderror" style="font-size:12px;">
                            <option value="Tanzania" {{ old('country', 'Tanzania') === 'Tanzania' ? 'selected' : '' }}>🇹🇿 Tanzania</option>
                            <option value="Kenya" {{ old('country') === 'Kenya' ? 'selected' : '' }}>🇰🇪 Kenya</option>
                            <option value="Uganda" {{ old('country') === 'Uganda' ? 'selected' : '' }}>🇺🇬 Uganda</option>
                            <option value="Rwanda" {{ old('country') === 'Rwanda' ? 'selected' : '' }}>🇷🇼 Rwanda</option>
                            <option value="Burundi" {{ old('country') === 'Burundi' ? 'selected' : '' }}>🇧🇮 Burundi</option>
                            <option value="DR Congo" {{ old('country') === 'DR Congo' ? 'selected' : '' }}>🇨🇩 DR Congo</option>
                            <option value="China" {{ old('country') === 'China' ? 'selected' : '' }}>🇨🇳 China</option>
                            <option value="United Arab Emirates" {{ old('country') === 'United Arab Emirates' ? 'selected' : '' }}>🇦🇪 UAE</option>
                            <option value="United States" {{ old('country') === 'United States' ? 'selected' : '' }}>🇺🇸 United States</option>
                            <option value="United Kingdom" {{ old('country') === 'United Kingdom' ? 'selected' : '' }}>🇬🇧 United Kingdom</option>
                            <option value="Other International" {{ old('country') === 'Other International' ? 'selected' : '' }}>Other International</option>
                        </select>
                        @error('country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="biz-tanzania-location-block">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">REGION <span class="text-danger">*</span></label>
                            <select name="region_id" id="biz-region-select" class="form-select @error('region_id') is-invalid @enderror" style="font-size:12px;">
                                <option value="">Select Region</option>
                                @foreach($regions as $r)
                                    <option value="{{ $r->id }}" {{ (string) old('region_id') === (string) $r->id ? 'selected' : '' }}>{{ $r->region_name }}</option>
                                @endforeach
                            </select>
                            @error('region_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">DISTRICT <span class="text-danger">*</span></label>
                            <select name="district_id" id="biz-district-select" class="form-select @error('district_id') is-invalid @enderror" style="font-size:12px;">
                                <option value="">Select District</option>
                            </select>
                            @error('district_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">ADDRESS / LOCATION</label>
                        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" placeholder="Street / Building / Area" style="font-size:12px;" value="{{ old('address') }}">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-0">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="biz-is-primary" name="is_primary" {{ old('is_primary') ? 'checked' : '' }}>
                            <label class="form-check-label small fw-semibold" for="biz-is-primary">
                                Set as primary business profile
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">SAVE BUSINESS</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const countrySelect = document.getElementById('biz-country-select');
    const locationBlock = document.getElementById('biz-tanzania-location-block');
    const regionSelect  = document.getElementById('biz-region-select');
    const districtSelect= document.getElementById('biz-district-select');
    const oldDistrictId = @json(old('district_id'));

    const fetchDistricts = async (regionId, selectedDistrictId = null) => {
        if (!regionId) {
            districtSelect.innerHTML = '<option value="">Select District</option>';
            return;
        }

        districtSelect.innerHTML = '<option value="">Loading districts...</option>';
        try {
            const response = await fetch(`{{ url('/regions') }}/${regionId}/districts`, {
                headers: {
                    'Accept': 'application/json'
                }
            });
            const districts = await response.json();

            districtSelect.innerHTML = '<option value="">Select District</option>';
            (districts || []).forEach((district) => {
                const option = document.createElement('option');
                option.value = district.id;
                option.textContent = district.district_name;
                if (selectedDistrictId && String(selectedDistrictId) === String(district.id)) {
                    option.selected = true;
                }
                districtSelect.appendChild(option);
            });
        } catch (error) {
            districtSelect.innerHTML = '<option value="">Failed to load districts</option>';
            console.error('Failed to load districts:', error);
        }
    };

    if (countrySelect) {
        countrySelect.addEventListener('change', function() {
            const val = this.value;
            if (val === 'Tanzania') {
                regionSelect.setAttribute('required', 'required');
                districtSelect.setAttribute('required', 'required');
                locationBlock.style.display = 'block';
                if (regionSelect.value) {
                    fetchDistricts(regionSelect.value);
                }
            } else {
                regionSelect.removeAttribute('required');
                districtSelect.removeAttribute('required');
                locationBlock.style.display = 'none';
            }
        });
    }

    if (regionSelect && districtSelect) {
        regionSelect.addEventListener('change', function() {
            fetchDistricts(this.value);
        });
    }

    if (countrySelect && countrySelect.value === 'Tanzania') {
        regionSelect.setAttribute('required', 'required');
        districtSelect.setAttribute('required', 'required');
        locationBlock.style.display = 'block';
        if (regionSelect.value) {
            fetchDistricts(regionSelect.value, oldDistrictId);
        }
    }

    @if($errors->has('business_name') || $errors->has('business_type') || $errors->has('phone') || $errors->has('email') || $errors->has('country') || $errors->has('region_id') || $errors->has('district_id') || $errors->has('address'))
    const addBusinessModal = document.getElementById('addBusinessModal');
    if (addBusinessModal && window.bootstrap) {
        const modalInstance = new bootstrap.Modal(addBusinessModal);
        modalInstance.show();
    }
    @endif
});
</script>
@endpush

@endsection
