@extends('layouts.admin')

@section('title', 'Reports & Analytics - CHIBO BRAND')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-4">
                    <h1 class="display-5 fw-bold text-primary mb-2">
                        <i class="fas fa-chart-bar me-3"></i>Reports & Analytics
                    </h1>
                    <p class="lead text-muted mb-0">
                        Comprehensive business insights and performance analytics
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-icon primary">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stats-number">{{ \App\Models\Order::count() }}</div>
                <div class="stats-label">Total Orders</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stats-number">{{ \App\Models\Order::where('approval_status', 'approved')->count() }}</div>
                <div class="stats-label">Approved Orders</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-icon warning">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stats-number">TZS {{ number_format(\App\Models\Order::where('approval_status', 'approved')->sum('total_amount'), 0) }}</div>
                <div class="stats-label">Total Revenue</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-icon info">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stats-number">{{ \App\Models\User::whereIn('role', ['retail_customer', 'wholesale_customer'])->count() }}</div>
                <div class="stats-label">Total Customers</div>
            </div>
        </div>
    </div>

    <!-- Report Types -->
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-day me-2 text-primary"></i>Daily Reports
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Track daily order performance, revenue, and trends with detailed analytics.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Daily order summaries</li>
                        <li><i class="fas fa-check text-success me-2"></i>Revenue tracking</li>
                        <li><i class="fas fa-check text-success me-2"></i>Approval rates</li>
                        <li><i class="fas fa-check text-success me-2"></i>Export to PDF/Excel</li>
                    </ul>
                    <a href="{{ route('admin.reports.daily') }}" class="btn btn-primary w-100">
                        <i class="fas fa-chart-line me-2"></i>View Daily Reports
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2 text-success"></i>Monthly Reports
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Comprehensive monthly analysis with growth trends and performance metrics.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Monthly summaries</li>
                        <li><i class="fas fa-check text-success me-2"></i>Growth analysis</li>
                        <li><i class="fas fa-check text-success me-2"></i>Customer insights</li>
                        <li><i class="fas fa-check text-success me-2"></i>Product performance</li>
                    </ul>
                    <a href="{{ route('admin.reports.monthly') }}" class="btn btn-success w-100">
                        <i class="fas fa-chart-bar me-2"></i>View Monthly Reports
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-box me-2 text-warning"></i>Product Reports
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Analyze product performance, stock levels, and sales trends.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Product sales analysis</li>
                        <li><i class="fas fa-check text-success me-2"></i>Stock level reports</li>
                        <li><i class="fas fa-check text-success me-2"></i>Category performance</li>
                        <li><i class="fas fa-check text-success me-2"></i>Low stock alerts</li>
                    </ul>
                    <a href="{{ route('admin.enhanced-products.index') }}" class="btn btn-warning w-100">
                        <i class="fas fa-box me-2"></i>View Product Reports
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-paint-brush me-2 text-info"></i>Designer Performance
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Track designer productivity and task completion metrics.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Designer task counts</li>
                        <li><i class="fas fa-check text-success me-2"></i>Completion rates</li>
                        <li><i class="fas fa-check text-success me-2"></i>Real-time task status</li>
                        <li><i class="fas fa-check text-success me-2"></i>Productivity trends</li>
                    </ul>
                    <a href="{{ route('admin.reports.design-tasks') }}" class="btn btn-info w-100">
                        <i class="fas fa-chart-line me-2"></i>View Designer Performance
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row mt-4">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Activity</th>
                                    <th>Details</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $recentOrders = \App\Models\Order::with('user')->orderBy('created_at', 'desc')->limit(10)->get();
                                @endphp
                                @forelse($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $order->created_at->format('M j, Y') }}</div>
                                            <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <i class="fas fa-shopping-cart me-2 text-primary"></i>
                                            New Order
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $order->order_code }}</div>
                                            <small class="text-muted">{{ $order->user->name }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $order->approval_status === 'approved' ? 'success' : ($order->approval_status === 'cancelled' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($order->approval_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No recent activity</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy me-2"></i>Top Products
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $topProducts = \App\Models\OrderItem::with('product')
                            ->selectRaw('product_id, SUM(quantity) as total_sold')
                            ->groupBy('product_id')
                            ->orderBy('total_sold', 'desc')
                            ->limit(5)
                            ->get();
                    @endphp
                    @forelse($topProducts as $item)
                        <div class="d-flex align-items-center mb-3 p-3 bg-light rounded">
                            <div class="me-3">
                                @if($item->product->images->count() > 0)
                                    <img src="{{ $item->product->images->first()->url }}" alt="{{ $item->product->name }}" 
                                         class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold">{{ $item->product->name }}</h6>
                                <small class="text-muted">{{ $item->total_sold }} units sold</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary">{{ $item->total_sold }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No sales data available</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate stats cards
    const statsCards = document.querySelectorAll('.stats-card');
    statsCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Animate cards
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, (index + 4) * 100);
    });
});
</script>
@endsection
