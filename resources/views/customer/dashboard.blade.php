@extends('public.layouts.app')

@section('title', 'Dashboard - CHIBO BRAND')
@section('description', 'Your CHIBO BRAND customer dashboard')

@push('styles')
<style>
    .hero-dashboard {
        box-shadow: 0 20px 40px rgba(220, 38, 38, 0.15);
    }
    
    .stats-card {
        transition: all 0.3s ease;
        border: none !important;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
    }
    
    .modern-card {
        transition: all 0.3s ease;
    }
    
    .modern-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.12) !important;
    }
    
    .btn-modern-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(220, 38, 38, 0.3);
    }
    
    .btn-modern-outline:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    
    .info-item {
        transition: all 0.3s ease;
    }
    
    .info-item:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(220, 38, 38, 0.1);
    }
    
    .alert-modern {
        transition: all 0.3s ease;
    }
    
    .alert-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(217, 119, 6, 0.15);
    }
    
    body {
        background: linear-gradient(135deg, #fef7f7 0%, #fef2f2 100%);
        min-height: 100vh;
    }
    
    /* Global font size reductions */
    .hero-dashboard h1 {
        font-size: 1.3rem !important;
    }
    
    .hero-dashboard p {
        font-size: 0.85rem !important;
    }
    
    .stats-card h3 {
        font-size: 1.8rem !important;
    }
    
    .stats-card p {
        font-size: 0.8rem !important;
    }
    
    .card-title {
        font-size: 0.95rem !important;
    }
    
    .info-item .fw-bold {
        font-size: 0.85rem !important;
    }
    
    .info-item .text-muted {
        font-size: 0.75rem !important;
    }
    
    .btn {
        font-size: 0.85rem !important;
    }
    
    .table {
        font-size: 0.85rem !important;
    }
    
    .badge {
        font-size: 0.75rem !important;
    }
    
    .alert-heading {
        font-size: 0.9rem !important;
    }
    
    .alert p {
        font-size: 0.8rem !important;
    }
    
    /* Mobile responsive adjustments */
    @media (max-width: 768px) {
        .stats-card h3 {
            font-size: 1.2rem !important;
        }
        
        .stats-card p {
            font-size: 0.7rem !important;
        }
        
        .hero-dashboard h1 {
            font-size: 1.1rem !important;
        }
        
        .hero-dashboard p {
            font-size: 0.75rem !important;
        }
        
        .card-title {
            font-size: 0.85rem !important;
        }
        
        .info-item .fw-bold {
            font-size: 0.75rem !important;
        }
        
        .info-item .text-muted {
            font-size: 0.65rem !important;
        }
        
        .btn {
            font-size: 0.75rem !important;
        }
        
        .table {
            font-size: 0.75rem !important;
        }
        
        .badge {
            font-size: 0.65rem !important;
        }
        
        .alert-heading {
            font-size: 0.8rem !important;
        }
        
        .alert p {
            font-size: 0.7rem !important;
        }
    }
</style>
@endpush

@section('content')
<!-- Modern Dashboard with Red Decoration -->
<div class="container py-4">
    <!-- Hero Section with Red Gradient -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="hero-dashboard position-relative overflow-hidden rounded-4" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%); min-height: 100px;">
                <div class="position-absolute top-0 end-0" style="opacity: 0.1;">
                    <i class="fas fa-chart-line" style="font-size: 8rem; color: white;"></i>
                </div>
                <div class="position-relative p-2 text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h4 mb-2 fw-bold">Welcome back, {{ auth('customer')->user()->name }}!</h1>
                            <p class="mb-0 fs-6 opacity-90">Manage your orders and account settings</p>
                        </div>
                        <div class="text-end">
                            @if(!auth('customer')->user()->verified)
                                <div class="badge bg-warning text-dark px-2 py-1">
                                    <i class="fas fa-clock me-2"></i>Account Pending Verification
                                </div>
                            @else
                                <div class="badge bg-success px-2 py-1">
                                    <i class="fas fa-check me-2"></i>Account Verified
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Stats Cards -->
    <div class="row mb-4 g-4">
        <div class="col-lg-3 col-md-6 col-6">
            <div class="stats-card h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border: 2px solid #fecaca; border-radius: 16px;">
                <div class="position-absolute top-0 end-0" style="opacity: 0.1;">
                    <i class="fas fa-shopping-cart" style="font-size: 4rem; color: #dc2626;"></i>
                </div>
                <div class="p-4 position-relative">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stats-icon me-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #dc2626, #b91c1c); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-shopping-cart text-white" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold" style="color: #dc2626; font-size: 2rem;">{{ $recentOrders->count() }}</h3>
                            <p class="mb-0 text-muted fw-medium">Total Orders</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="stats-card h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #fed7aa; border-radius: 16px;">
                <div class="position-absolute top-0 end-0" style="opacity: 0.1;">
                    <i class="fas fa-clock" style="font-size: 4rem; color: #d97706;"></i>
                </div>
                <div class="p-4 position-relative">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stats-icon me-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #d97706, #b45309); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-clock text-white" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold" style="color: #d97706; font-size: 2rem;">{{ $recentOrders->where('approval_status', 'requested')->count() }}</h3>
                            <p class="mb-0 text-muted fw-medium">Pending Orders</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="stats-card h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #bbf7d0; border-radius: 16px;">
                <div class="position-absolute top-0 end-0" style="opacity: 0.1;">
                    <i class="fas fa-check-circle" style="font-size: 4rem; color: #16a34a;"></i>
                </div>
                <div class="p-4 position-relative">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stats-icon me-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #16a34a, #15803d); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-check-circle text-white" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold" style="color: #16a34a; font-size: 2rem;">{{ $recentOrders->where('approval_status', 'approved')->count() }}</h3>
                            <p class="mb-0 text-muted fw-medium">Approved Orders</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="stats-card h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 2px solid #bfdbfe; border-radius: 16px;">
                <div class="position-absolute top-0 end-0" style="opacity: 0.1;">
                    <i class="fas fa-truck" style="font-size: 4rem; color: #2563eb;"></i>
                </div>
                <div class="p-4 position-relative">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stats-icon me-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #2563eb, #1d4ed8); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-truck text-white" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold" style="color: #2563eb; font-size: 2rem;">{{ $recentOrders->where('approval_status', 'approved')->count() }}</h3>
                            <p class="mb-0 text-muted fw-medium">Completed Orders</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Account Status Alert -->
    @if(!auth('customer')->user()->verified)
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert-modern position-relative overflow-hidden" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #fed7aa; border-radius: 16px; padding: 0.75rem;">
                    <div class="position-absolute top-0 end-0" style="opacity: 0.1;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: #d97706;"></i>
                    </div>
                    <div class="d-flex align-items-center position-relative">
                        <div class="alert-icon me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #d97706, #b45309); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-exclamation-triangle text-white" style="font-size: 1.25rem;"></i>
                        </div>
                        <div>
                            <h6 class="alert-heading mb-2 fw-bold" style="color: #92400e;">Account Verification Pending</h6>
                            <p class="mb-0 text-muted">Your account is currently under review. You'll receive an email notification once your account is verified. 
                            This process usually takes 1-2 business days.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Recent Orders -->
        <div class="col-lg-8">
            <div class="modern-card h-100" style="background: white; border: 2px solid #f3f4f6; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden;">
                <div class="card-header-modern d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); padding: 0.75rem; border-bottom: none;">
                    <h5 class="card-title mb-0 text-white fw-bold">Recent Orders</h5>
                    <a href="{{ request()->is('b2b*') ? route('b2b.customer.orders.index') : route('retail.customer.orders.index') }}" 
                       class="btn btn-light btn-sm px-3 py-2 fw-medium" 
                       style="border-radius: 10px; border: none; color: #dc2626;">
                        <i class="fas fa-eye me-2"></i>View All
                    </a>
                </div>
                <div class="card-body">
                    @if($recentOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                        <tr>
                                            <td>
                                                <strong>{{ $order->order_code }}</strong>
                                            </td>
                                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                                            <td>
                                                @switch($order->approval_status)
                                                    @case('requested')
                                                        <span class="badge bg-warning text-dark">Requested</span>
                                                        @break
                                                    @case('approved')
                                                        <span class="badge bg-success">Approved</span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge bg-danger">Cancelled</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td><strong>{{ $order->formatted_total }}</strong></td>
                                            <td>
                                                <a href="{{ request()->is('b2b*') ? route('b2b.customer.orders.show', $order) : route('retail.customer.orders.show', $order) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-cart text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">No Orders Yet</h5>
                            <p class="text-muted">You haven't placed any orders yet.</p>
                            <a href="{{ auth('customer')->user()->is_wholesale ? route('wholesale.products') : route('retail.products') }}" class="btn btn-primary">
                                <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions & Account Info -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="modern-card mb-4" style="background: white; border: 2px solid #f3f4f6; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden;">
                <div class="card-header-modern" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); padding: 0.75rem; border-bottom: none;">
                    <h5 class="card-title mb-0 text-white fw-bold">Quick Actions</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-grid gap-3">
                        <a href="{{ auth('customer')->user()->is_wholesale ? route('wholesale.products') : route('retail.products') }}" 
                           class="btn btn-modern-primary py-3 fw-medium" 
                           style="background: linear-gradient(135deg, #dc2626, #b91c1c); border: none; border-radius: 12px; color: white; text-decoration: none; transition: all 0.3s ease;">
                            <i class="fas fa-shopping-bag me-2"></i>Browse Products
                        </a>
                        <a href="{{ request()->is('b2b*') ? route('b2b.customer.orders.index') : route('retail.customer.orders.index') }}" 
                           class="btn btn-modern-outline py-3 fw-medium" 
                           style="background: linear-gradient(135deg, #fef2f2, #fee2e2); border: 2px solid #fecaca; border-radius: 12px; color: #dc2626; text-decoration: none; transition: all 0.3s ease;">
                            <i class="fas fa-list me-2"></i>View All Orders
                        </a>
                        <a href="{{ request()->is('b2b*') ? route('b2b.customer.profile') : route('retail.customer.profile') }}" 
                           class="btn btn-modern-outline py-3 fw-medium" 
                           style="background: linear-gradient(135deg, #f9fafb, #f3f4f6); border: 2px solid #e5e7eb; border-radius: 12px; color: #374151; text-decoration: none; transition: all 0.3s ease;">
                            <i class="fas fa-user me-2"></i>Update Profile
                        </a>
                        <a href="{{ request()->is('b2b*') ? route('b2b.customer.change-password') : route('retail.customer.change-password') }}" 
                           class="btn btn-modern-outline py-3 fw-medium" 
                           style="background: linear-gradient(135deg, #f9fafb, #f3f4f6); border: 2px solid #e5e7eb; border-radius: 12px; color: #374151; text-decoration: none; transition: all 0.3s ease;">
                            <i class="fas fa-key me-2"></i>Change Password
                        </a>
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <div class="modern-card" style="background: white; border: 2px solid #f3f4f6; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden;">
                <div class="card-header-modern" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1b 100%); padding: 0.75rem; border-bottom: none;">
                    <h5 class="card-title mb-0 text-white fw-bold">Account Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="info-item mb-4 p-3" style="background: linear-gradient(135deg, #fef2f2, #fee2e2); border-radius: 12px; border-left: 4px solid #dc2626;">
                        <div class="d-flex align-items-center">
                            <div class="info-icon me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #dc2626, #b91c1c); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-user text-white" style="font-size: 1rem;"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark mb-1">Name</div>
                                <div class="text-muted">{{ auth('customer')->user()->name }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-item mb-4 p-3" style="background: linear-gradient(135deg, #fef2f2, #fee2e2); border-radius: 12px; border-left: 4px solid #dc2626;">
                        <div class="d-flex align-items-center">
                            <div class="info-icon me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #dc2626, #b91c1c); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-envelope text-white" style="font-size: 1rem;"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark mb-1">Email</div>
                                <div class="text-muted">{{ auth('customer')->user()->email }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-item mb-4 p-3" style="background: linear-gradient(135deg, #fef2f2, #fee2e2); border-radius: 12px; border-left: 4px solid #dc2626;">
                        <div class="d-flex align-items-center">
                            <div class="info-icon me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #dc2626, #b91c1c); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-phone text-white" style="font-size: 1rem;"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark mb-1">Phone</div>
                                <div class="text-muted">{{ auth('customer')->user()->phone }}</div>
                            </div>
                        </div>
                    </div>
                    
                    @if(auth('customer')->user()->company_name)
                        <div class="info-item mb-4 p-3" style="background: linear-gradient(135deg, #fef2f2, #fee2e2); border-radius: 12px; border-left: 4px solid #dc2626;">
                            <div class="d-flex align-items-center">
                                <div class="info-icon me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #dc2626, #b91c1c); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-building text-white" style="font-size: 1rem;"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark mb-1">Company</div>
                                    <div class="text-muted">{{ auth('customer')->user()->company_name }}</div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if(auth('customer')->user()->is_wholesale)
                        <div class="info-item mb-4 p-3" style="background: linear-gradient(135deg, #fef2f2, #fee2e2); border-radius: 12px; border-left: 4px solid #dc2626;">
                            <div class="d-flex align-items-center">
                                <div class="info-icon me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #dc2626, #b91c1c); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-store text-white" style="font-size: 1rem;"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark mb-1">Account Type</div>
                                    <div class="badge bg-danger px-3 py-2" style="border-radius: 8px;">Wholesale Customer</div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="info-item mb-0 p-3" style="background: linear-gradient(135deg, #fef2f2, #fee2e2); border-radius: 12px; border-left: 4px solid #dc2626;">
                        <div class="d-flex align-items-center">
                            <div class="info-icon me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #dc2626, #b91c1c); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-calendar text-white" style="font-size: 1rem;"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark mb-1">Member Since</div>
                                <div class="text-muted">{{ auth('customer')->user()->created_at->format('M d, Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection