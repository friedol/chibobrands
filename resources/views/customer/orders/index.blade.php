@extends('public.layouts.app')

@section('title', 'My Orders - CHIBO BRAND')
@section('description', 'View your order history and track your purchases')

@push('styles')
<style>
    .hero-dashboard {
        box-shadow: 0 20px 40px rgba(220, 38, 38, 0.15);
    }
    
    .modern-card {
        transition: all 0.3s ease;
        border: none !important;
    }
    
    .modern-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
    }
    
    .order-item {
        transition: all 0.3s ease;
    }
    
    .order-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .btn-modern-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(220, 38, 38, 0.3);
    }
    
    .btn-modern-outline:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
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
    
    .card-title {
        font-size: 0.95rem !important;
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
    
    h5, h6 {
        font-size: 0.9rem !important;
    }
    
    .order-item h6 {
        font-size: 0.85rem !important;
    }
    
    .order-item small {
        font-size: 0.75rem !important;
    }
    
    /* Mobile responsive adjustments */
    @media (max-width: 768px) {
        .hero-dashboard h1 {
            font-size: 1.1rem !important;
        }
        
        .hero-dashboard p {
            font-size: 0.75rem !important;
        }
        
        .card-title {
            font-size: 0.85rem !important;
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
        
        h5, h6 {
            font-size: 0.8rem !important;
        }
        
        .order-item h6 {
            font-size: 0.75rem !important;
        }
        
        .order-item small {
            font-size: 0.65rem !important;
        }
    }
</style>
@endpush

@section('content')
<!-- Modern Orders Page with Red Decoration -->
<div class="container py-4">
    <!-- Hero Section with Red Gradient -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="hero-dashboard position-relative overflow-hidden rounded-4" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%); min-height: 100px;">
                <div class="position-absolute top-0 end-0" style="opacity: 0.1;">
                    <i class="fas fa-shopping-bag" style="font-size: 8rem; color: white;"></i>
                </div>
                <div class="position-relative p-2 text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h4 mb-2 fw-bold">My Orders</h1>
                            <p class="mb-0 fs-6 opacity-90">Track your order history and status</p>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-white text-dark px-3 py-2">
                                <i class="fas fa-list me-2"></i>{{ $orders->count() }} Orders
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders List -->
    <div class="row">
        <div class="col-12">
            <div class="modern-card" style="background: white; border: 2px solid #f3f4f6; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden;">
                <div class="card-header-modern" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); padding: 0.75rem; border-bottom: none;">
                    <h5 class="card-title mb-0 text-white fw-bold">
                        <i class="fas fa-list me-2"></i>Order History
                    </h5>
                </div>
                <div class="card-body p-0">
                    @forelse($orders as $order)
                        <div class="order-item border-bottom p-4" style="background: linear-gradient(135deg, #fef7f7 0%, #ffffff 100%); border-left: 4px solid #dc2626;">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <div class="text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px; background: linear-gradient(135deg, #dc2626, #b91c1c);">
                                                <i class="fas fa-receipt"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-bold" style="color: #dc2626;">{{ $order->order_code }}</h6>
                                            <small class="text-muted">{{ $order->created_at->format('M j, Y H:i') }}</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div>
                                        <h6 class="mb-1">Items</h6>
                                        <p class="mb-0 text-muted">{{ $order->items->count() }} item(s)</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <div>
                                        <h6 class="mb-1">Total</h6>
                                        <p class="mb-0 fw-bold text-success">TZS {{ number_format($order->total_amount, 0) }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <div>
                                        <h6 class="mb-1">Status</h6>
                                        <span class="badge px-3 py-2 fw-medium" 
                                              style="background: linear-gradient(135deg, {{ $order->approval_status === 'approved' ? '#16a34a, #15803d' : ($order->approval_status === 'cancelled' ? '#dc2626, #b91c1c' : '#d97706, #b45309') }}); border-radius: 12px;">
                                            {{ ucfirst($order->approval_status) }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-modern-outline btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#orderModal{{ $order->id }}"
                                                style="background: linear-gradient(135deg, #f9fafb, #f3f4f6); border: 2px solid #e5e7eb; border-radius: 8px; color: #374151;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($order->approval_status === 'requested')
                                            <a href="https://wa.me/255687183330?text={{ urlencode($order->generateWhatsAppMessage()) }}" 
                                               class="btn btn-sm" 
                                               target="_blank"
                                               style="background: linear-gradient(135deg, #16a34a, #15803d); border: none; border-radius: 8px; color: white;">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Details Modal -->
                        <div class="modal fade" id="orderModal{{ $order->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
                                    <div class="modal-header" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); border-radius: 16px 16px 0 0; border-bottom: none;">
                                        <h5 class="modal-title text-white fw-bold">Order Details - {{ $order->order_code }}</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Order Summary -->
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <h6 class="fw-bold">Order Information</h6>
                                                <p class="mb-1"><strong>Order Code:</strong> {{ $order->order_code }}</p>
                                                <p class="mb-1"><strong>Date:</strong> {{ $order->created_at->format('M j, Y H:i') }}</p>
                                                <p class="mb-1"><strong>Status:</strong> 
                                                    <span class="badge badge-{{ $order->approval_status === 'approved' ? 'success' : ($order->approval_status === 'cancelled' ? 'danger' : 'warning') }}">
                                                        {{ ucfirst($order->approval_status) }}
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="fw-bold">Payment Information</h6>
                                                <p class="mb-1"><strong>Payment Status:</strong> 
                                                    <span class="badge badge-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'unpaid' ? 'danger' : 'warning') }}">
                                                        {{ ucfirst($order->payment_status) }}
                                                    </span>
                                                </p>
                                                <p class="mb-1"><strong>Total Amount:</strong> 
                                                    <span class="fw-bold text-success">TZS {{ number_format($order->total_amount, 0) }}</span>
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Order Items -->
                                        <h6 class="fw-bold mb-3">Order Items</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Quantity</th>
                                                        <th>Unit Price</th>
                                                        <th>Subtotal</th>
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
                                                                            // First try the original path
                                                                            $originalPath = $item->product->images->first()->image_path;
                                                                            if (file_exists(public_path('storage/' . $originalPath))) {
                                                                                $productImage = asset('storage/' . $originalPath);
                                                                            } else {
                                                                                // Try enhanced-products directory
                                                                                $enhancedPath = 'enhanced-products/' . basename($originalPath);
                                                                                if (file_exists(public_path('storage/' . $enhancedPath))) {
                                                                                    $productImage = asset('storage/' . $enhancedPath);
                                                                                } else {
                                                                                    // Try to find any image in enhanced-products
                                                                                    $enhancedDir = public_path('storage/enhanced-products/');
                                                                                    if (is_dir($enhancedDir)) {
                                                                                        $files = glob($enhancedDir . '*');
                                                                                        if (!empty($files)) {
                                                                                            $randomImage = basename($files[0]);
                                                                                            $productImage = asset('storage/enhanced-products/' . $randomImage);
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    
                                                                    @if($productImage)
                                                                        <img src="{{ $productImage }}" 
                                                                             alt="{{ $item->product->name }}" 
                                                                             class="img-thumbnail me-2" 
                                                                             style="width: 40px; height: 40px; object-fit: cover;"
                                                                             onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik0xNiAxNkgxNlYxNkgxNloiIGZpbGw9IiM5OTk5OTkiLz4KPC9zdmc+';">
                                                                    @else
                                                                        <div class="bg-light d-flex align-items-center justify-content-center me-2 rounded" 
                                                                             style="width: 40px; height: 40px;">
                                                                            <i class="fas fa-image text-muted"></i>
                                                                        </div>
                                                                    @endif
                                                                    <div>
                                                                        <div class="fw-bold">{{ $item->product->name }}</div>
                                                                        <small class="text-muted">{{ $item->product->category->name }}</small>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-primary">{{ $item->quantity }}</span>
                                                            </td>
                                                            <td>TZS {{ number_format($item->unit_price, 0) }}</td>
                                                            <td class="fw-bold">TZS {{ number_format($item->subtotal, 0) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr class="table-primary">
                                                        <th colspan="3" class="text-end">Total:</th>
                                                        <th class="text-success">TZS {{ number_format($order->total_amount, 0) }}</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                        @if($order->notes)
                                            <div class="mt-3">
                                                <h6 class="fw-bold">Notes</h6>
                                                <p class="text-muted">{{ $order->notes }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="modal-footer" style="border-top: 1px solid #f3f4f6; background: #f9fafb;">
                                        <button type="button" class="btn btn-modern-outline" data-bs-dismiss="modal"
                                                style="background: linear-gradient(135deg, #f9fafb, #f3f4f6); border: 2px solid #e5e7eb; border-radius: 8px; color: #374151;">
                                            <i class="fas fa-times me-2"></i>Close
                                        </button>
                                        @if($order->approval_status === 'requested')
                                            <a href="https://wa.me/255687183330?text={{ urlencode($order->generateWhatsAppMessage()) }}" 
                                               class="btn btn-modern-primary" 
                                               target="_blank"
                                               style="background: linear-gradient(135deg, #16a34a, #15803d); border: none; border-radius: 8px; color: white;">
                                                <i class="fab fa-whatsapp me-2"></i>Contact via WhatsApp
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5" style="background: linear-gradient(135deg, #fef7f7 0%, #ffffff 100%);">
                            <div class="mb-4">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                                     style="width: 80px; height: 80px; background: linear-gradient(135deg, #dc2626, #b91c1c);">
                                    <i class="fas fa-shopping-bag text-white" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                            <h5 class="text-muted mb-3">No orders found</h5>
                            <p class="text-muted mb-4">You haven't placed any orders yet.</p>
                            <a href="{{ route('products.index') }}" 
                               class="btn btn-modern-primary"
                               style="background: linear-gradient(135deg, #dc2626, #b91c1c); border: none; border-radius: 12px; color: white; padding: 0.75rem 2rem;">
                                <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                            </a>
                        </div>
                    @endforelse
                </div>
                
                <!-- Pagination -->
                @if($orders->hasPages())
                    <div class="card-footer" style="background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); border-top: 1px solid #e5e7eb;">
                        <div class="d-flex justify-content-center">
                            {{ $orders->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add animation to order cards
    const orderCards = document.querySelectorAll('.order-item');
    orderCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateX(0)';
        }, index * 100);
    });

    // Add hover effects to buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.05)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
});
</script>
@endpush
@endsection
