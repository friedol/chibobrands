@extends('public.layouts.app')

@section('title', 'Order Details - ' . $order->order_code)

@section('content')
<div class="container py-4">
    <!-- Hero Section with Red Gradient -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="hero-dashboard position-relative overflow-hidden rounded-4" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%); min-height: 100px;">
                <div class="position-absolute top-0 end-0" style="opacity: 0.1;">
                    <i class="fas fa-receipt" style="font-size: 8rem; color: white;"></i>
                </div>
                <div class="position-relative p-2 text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1 fw-bold" style="font-size: 1.2rem;">Order Details</h1>
                            <p class="mb-0 opacity-90" style="font-size: 0.9rem;">Order #{{ $order->order_code }}</p>
                        </div>
                        <div class="text-end">
                            @switch($order->approval_status)
                                @case('requested')
                                    <div class="badge bg-warning text-dark px-2 py-1" style="font-size: 0.7rem;">
                                        <i class="fas fa-clock me-1"></i>Pending
                                    </div>
                                    @break
                                @case('approved')
                                    <div class="badge bg-success px-2 py-1" style="font-size: 0.7rem;">
                                        <i class="fas fa-check me-1"></i>Approved
                                    </div>
                                    @break
                                @case('cancelled')
                                    <div class="badge bg-danger px-2 py-1" style="font-size: 0.7rem;">
                                        <i class="fas fa-times me-1"></i>Cancelled
                                    </div>
                                    @break
                            @endswitch
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Order Information -->
        <div class="col-lg-8">
            <div class="modern-card" style="background: white; border: 2px solid #f3f4f6; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden;">
                <div class="card-header-modern" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); padding: 0.3rem; border-bottom: none;">
                    <h5 class="card-title mb-0 text-white fw-bold" style="font-size: 0.75rem;">Order Information</h5>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="fw-semibold text-muted" style="font-size: 0.65rem;">Order Number</label>
                                <p class="mb-0 fw-bold" style="font-size: 0.7rem;">{{ $order->order_code }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="fw-semibold text-muted" style="font-size: 0.65rem;">Order Date</label>
                                <p class="mb-0" style="font-size: 0.7rem;">{{ $order->created_at->format('M d, Y \a\t g:i A') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="fw-semibold text-muted" style="font-size: 0.65rem;">Payment Status</label>
                                <p class="mb-0">
                                    <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}" style="font-size: 0.6rem;">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="fw-semibold text-muted" style="font-size: 0.65rem;">Approval Status</label>
                                <p class="mb-0">
                                    <span class="badge bg-{{ $order->approval_status === 'approved' ? 'success' : ($order->approval_status === 'cancelled' ? 'danger' : 'warning') }}" style="font-size: 0.6rem;">
                                        {{ ucfirst($order->approval_status) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        @if($order->notes)
                        <div class="col-12">
                            <div class="info-item">
                                <label class="fw-semibold text-muted" style="font-size: 0.65rem;">Notes</label>
                                <p class="mb-0" style="font-size: 0.7rem;">{{ $order->notes }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="modern-card mt-4" style="background: white; border: 2px solid #f3f4f6; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden;">
                <div class="card-header-modern" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); padding: 0.3rem; border-bottom: none;">
                    <h5 class="card-title mb-0 text-white fw-bold" style="font-size: 0.75rem;">Order Items</h5>
                </div>
                <div class="card-body p-0">
                    @if($order->items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="font-size: 0.65rem;">Product</th>
                                        <th style="font-size: 0.65rem;">Qty</th>
                                        <th style="font-size: 0.65rem;">Unit Price</th>
                                        <th style="font-size: 0.65rem;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @php
                                                        // Try to find a working image for this product
                                                        $productImage = null;
                                                        if ($item->product && $item->product->images->count() > 0) {
                                                            $originalPath = $item->product->images->first()->image_path;
                                                            // Try enhanced-products directory first (most likely location)
                                                            $enhancedPath = 'enhanced-products/' . basename($originalPath);
                                                            if (file_exists(public_path('storage/' . $enhancedPath))) {
                                                                $productImage = asset('storage/' . $enhancedPath);
                                                            } elseif (file_exists(public_path('storage/' . $originalPath))) {
                                                                $productImage = asset('storage/' . $originalPath);
                                                            } else {
                                                                // Try to find any image in enhanced-products
                                                                $enhancedDir = public_path('storage/enhanced-products/');
                                                                if (is_dir($enhancedDir)) {
                                                                    $files = glob($enhancedDir . '*.{jpg,jpeg,png,webp}', GLOB_BRACE);
                                                                    if (!empty($files)) {
                                                                        $randomImage = basename($files[0]);
                                                                        $productImage = asset('storage/enhanced-products/' . $randomImage);
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    @endphp
                                                    
                                                    <img src="{{ $productImage ?: asset('images/default.svg') }}" 
                                                         alt="{{ $item->product_name ?: 'Product' }}" 
                                                         class="me-2" 
                                                         style="width: 30px; height: 30px; object-fit: cover; border-radius: 4px;"
                                                         onerror="this.onerror=null; this.src='{{ asset('images/default.svg') }}';">
                                                    <div>
                                                        @php
                                                            // Get the correct product name
                                                            $productName = $item->product_name;
                                                            if (!$productName && $item->product) {
                                                                $productName = $item->product->name;
                                                            }
                                                            if (!$productName) {
                                                                $productName = 'Product';
                                                            }
                                                        @endphp
                                                        <h6 class="mb-1 fw-semibold" style="font-size: 0.85rem;">{{ $productName }}</h6>
                                                        @if($item->product && $item->product->barcode)
                                                            <small class="text-muted" style="font-size: 0.7rem;">SKU: {{ $item->product->barcode }}</small>
                                                        @endif
                                                        @if($item->variants && count($item->variants) > 0)
                                                            <div class="mt-1">
                                                                @foreach($item->variants as $key => $value)
                                                                    @if($value)
                                                                        <small class="badge bg-light text-dark me-1">{{ $key }}: {{ $value }}</small>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-semibold" style="font-size: 0.75rem;">{{ $item->quantity }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold" style="font-size: 0.75rem;">TZS {{ number_format($item->unit_price, 0) }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-primary" style="font-size: 0.75rem;">TZS {{ number_format($item->subtotal, 0) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No items found for this order.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="modern-card" style="background: white; border: 2px solid #f3f4f6; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden;">
                <div class="card-header-modern" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); padding: 0.4rem; border-bottom: none;">
                    <h5 class="card-title mb-0 text-white fw-bold" style="font-size: 0.85rem;">Order Summary</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-semibold" style="font-size: 0.75rem;">Subtotal</span>
                        <span class="fw-bold" style="font-size: 0.75rem;">TZS {{ number_format($order->total_amount, 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-semibold" style="font-size: 0.75rem;">Tax</span>
                        <span class="fw-bold" style="font-size: 0.75rem;">TZS 0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-semibold" style="font-size: 0.75rem;">Shipping</span>
                        <span class="fw-bold" style="font-size: 0.75rem;">TZS 0</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold" style="font-size: 0.85rem;">Total</span>
                        <span class="fw-bold text-primary" style="font-size: 0.85rem;">TZS {{ number_format($order->total_amount, 0) }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="modern-card mt-4" style="background: white; border: 2px solid #f3f4f6; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden;">
                <div class="card-header-modern" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); padding: 0.4rem; border-bottom: none;">
                    <h5 class="card-title mb-0 text-white fw-bold" style="font-size: 0.85rem;">Quick Actions</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-grid gap-2">
                        <a href="{{ request()->is('b2b*') ? route('b2b.customer.orders.index') : route('retail.customer.orders.index') }}" 
                           class="btn btn-modern-outline">
                            <i class="fas fa-arrow-left me-2"></i>Back to Orders
                        </a>
                        <a href="{{ request()->is('b2b*') ? route('b2b.customer.dashboard') : route('retail.customer.dashboard') }}" 
                           class="btn btn-modern-primary">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        @if($order->approval_status === 'requested')
                            <button class="btn btn-modern-outline" disabled>
                                <i class="fas fa-clock me-2"></i>Awaiting Approval
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.hero-dashboard {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
    min-height: 100px;
}

.modern-card {
    background: white;
    border: 2px solid #f3f4f6;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s ease;
}

.modern-card:hover {
    box-shadow: 0 15px 35px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.card-header-modern {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    padding: 0.75rem;
    border-bottom: none;
}

.btn-modern-primary {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    border: none;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-modern-primary:hover {
    background: linear-gradient(135deg, #b91c1c, #991b1b);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(220, 38, 38, 0.3);
}

.btn-modern-outline {
    background: transparent;
    border: 2px solid #dc2626;
    color: #dc2626;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-modern-outline:hover {
    background: #dc2626;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(220, 38, 38, 0.3);
}

.info-item {
    margin-bottom: 0.75rem;
}

.info-item:last-child {
    margin-bottom: 0;
}

.info-item label {
    display: block;
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.info-item p {
    margin: 0;
    font-size: 1rem;
}
</style>
@endpush
@endsection
