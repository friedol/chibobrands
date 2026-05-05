@extends('layouts.admin')

@section('title', 'Daily Order Summary - CHIBO BRAND Admin')
@section('description', 'Daily order summary and analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 mb-1">Daily Order Summary</h2>
        <p class="text-muted mb-0">Order analytics and summary for {{ \Carbon\Carbon::parse($summary['date'])->format('F d, Y') }}</p>
    </div>
    <div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Orders
        </a>
    </div>
</div>

<!-- Date Selector -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row align-items-end">
            <div class="col-md-4">
                <label class="form-label">Select Date</label>
                <input type="date" name="date" class="form-control" value="{{ $summary['date'] }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>View Summary
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-shopping-cart text-primary mb-2" style="font-size: 2rem;"></i>
                <h4 class="card-title">{{ $summary['total_requests'] }}</h4>
                <p class="card-text text-muted">Total Requests</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-check-circle text-success mb-2" style="font-size: 2rem;"></i>
                <h4 class="card-title">{{ $summary['approved'] }}</h4>
                <p class="card-text text-muted">Approved</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-times-circle text-danger mb-2" style="font-size: 2rem;"></i>
                <h4 class="card-title">{{ $summary['cancelled'] }}</h4>
                <p class="card-text text-muted">Cancelled</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-clock text-warning mb-2" style="font-size: 2rem;"></i>
                <h4 class="card-title">{{ $summary['pending'] }}</h4>
                <p class="card-text text-muted">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-cog text-info mb-2" style="font-size: 2rem;"></i>
                <h4 class="card-title">{{ $summary['processing'] }}</h4>
                <p class="card-text text-muted">Processing</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-shipping-fast text-primary mb-2" style="font-size: 2rem;"></i>
                <h4 class="card-title">{{ $summary['shipped'] + $summary['delivered'] }}</h4>
                <p class="card-text text-muted">Shipped/Delivered</p>
            </div>
        </div>
    </div>
</div>

<!-- Status Breakdown Chart -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Order Status Breakdown</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Order Value Summary</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-primary">${{ number_format($orders->sum('total_amount'), 2) }}</h4>
                            <p class="text-muted mb-0">Total Value</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-success">${{ number_format($orders->where('status', 'approved')->sum('total_amount'), 2) }}</h4>
                            <p class="text-muted mb-0">Approved Value</p>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-info">${{ $summary['total_requests'] > 0 ? number_format($orders->sum('total_amount') / $summary['total_requests'], 2) : '0.00' }}</h4>
                            <p class="text-muted mb-0">Average Order</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-warning">{{ $summary['total_requests'] > 0 ? number_format(($summary['approved'] / $summary['total_requests']) * 100, 1) : '0' }}%</h4>
                            <p class="text-muted mb-0">Approval Rate</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Orders for {{ \Carbon\Carbon::parse($summary['date'])->format('F d, Y') }}</h5>
    </div>
    <div class="card-body">
        @if($orders->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Domain</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>
                                    <strong>{{ $order->order_number }}</strong>
                                </td>
                                <td>
                                    @if($order->user)
                                        <div>
                                            <strong>{{ $order->user->name }}</strong>
                                            @if($order->user->company_name)
                                                <br><small class="text-muted">{{ $order->user->company_name }}</small>
                                            @endif
                                        </div>
                                    @else
                                        <div>
                                            <strong>{{ $order->name }}</strong>
                                            <br><small class="text-muted">{{ $order->phone }}</small>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    {{ $order->created_at->format('h:i A') }}
                                </td>
                                <td>
                                    @switch($order->status)
                                        @case('pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                            @break
                                        @case('approved')
                                            <span class="badge bg-info">Approved</span>
                                            @break
                                        @case('processing')
                                            <span class="badge bg-primary">Processing</span>
                                            @break
                                        @case('shipped')
                                            <span class="badge bg-success">Shipped</span>
                                            @break
                                        @case('delivered')
                                            <span class="badge bg-success">Delivered</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge bg-danger">Cancelled</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    <strong>{{ $order->formatted_total }}</strong>
                                </td>
                                <td>
                                    @if(str_contains($order->notes, 'b2b.chibobrand.com'))
                                        <span class="badge bg-primary">B2B</span>
                                    @else
                                        <span class="badge bg-secondary">Retail</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-calendar-day text-muted" style="font-size: 4rem;"></i>
                <h4 class="mt-3">No Orders Found</h4>
                <p class="text-muted">No orders were placed on {{ \Carbon\Carbon::parse($summary['date'])->format('F d, Y') }}.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Chart
    const ctx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'],
            datasets: [{
                data: [
                    {{ $summary['approved'] }},
                    {{ $summary['pending'] }},
                    {{ $summary['processing'] }},
                    {{ $summary['shipped'] }},
                    {{ $summary['delivered'] }},
                    {{ $summary['cancelled'] }}
                ],
                backgroundColor: [
                    '#17a2b8', // Approved - Info
                    '#ffc107', // Pending - Warning
                    '#007bff', // Processing - Primary
                    '#28a745', // Shipped - Success
                    '#28a745', // Delivered - Success
                    '#dc3545'  // Cancelled - Danger
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
});
</script>
@endpush
