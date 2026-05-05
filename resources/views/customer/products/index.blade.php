@extends('customer.layouts.app')

@section('title', 'Wholesale Products - CHIBO BRAND B2B')
@section('description', 'Browse our wholesale catalog with exclusive pricing for business customers.')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 mb-1">Wholesale Products</h2>
        <p class="text-muted mb-0">Exclusive pricing for business customers</p>
    </div>
    <div>
        <span class="badge bg-primary fs-6">B2B Portal</span>
    </div>
</div>

<!-- Search and Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-center">
            <div class="col-lg-2">
                <label for="per_page" class="form-label fw-semibold mb-1">Show</label>
                <select name="per_page" id="per_page" class="form-select" onchange="this.form.submit()">
                    <option value="12" {{ request('per_page') == '12' ? 'selected' : '' }}>12 per page</option>
                    <option value="24" {{ request('per_page') == '24' || !request('per_page') ? 'selected' : '' }}>24 per page</option>
                    <option value="48" {{ request('per_page') == '48' ? 'selected' : '' }}>48 per page</option>
                    <option value="96" {{ request('per_page') == '96' ? 'selected' : '' }}>96 per page</option>
                </select>
            </div>

            <div class="col-lg-8">
                <form method="GET" action="{{ request()->is('b2b*') ? route('b2b.customer.products.index') : route('retail.customer.products.index') }}" class="d-flex">
                    <input type="hidden" name="per_page" value="{{ request('per_page', '24') }}">
                    <input type="text" name="q" class="form-control me-2" placeholder="Search wholesale products..." value="{{ request('q') }}">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>

            <div class="col-lg-2">
                <div class="d-flex justify-content-lg-end">
                    <a href="{{ request()->is('b2b*') ? route('b2b.customer.orders.index') : route('retail.customer.orders.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-shopping-cart me-2"></i>View Orders
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Categories Section -->
@if($categories->count() > 0)
<div class="card mb-4">
    <div class="card-body">
        <h6 class="mb-3">Browse by Category</h6>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ request()->is('b2b*') ? route('b2b.customer.products.index') : route('retail.customer.products.index') }}" class="btn btn-outline-secondary {{ !request('category') ? 'active' : '' }}">
                All Products
            </a>
            @foreach($categories as $category)
                <a href="{{ request()->is('b2b*') ? route('b2b.customer.products.index', ['category' => $category->id]) : route('retail.customer.products.index', ['category' => $category->id]) }}" 
                   class="btn btn-outline-secondary {{ request('category') == $category->id ? 'active' : '' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Products Grid -->
@if($products->count() > 0)
    <div class="row g-4">
        @foreach($products as $product)
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card product-card h-100">
                    <div class="position-relative">
                        @if($product->images->count() > 0)
                            <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" 
                                 class="card-img-top" 
                                 alt="{{ $product->name }}"
                                 style="height: 200px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 200px;">
                                <i class="fas fa-image text-muted" style="font-size: 2.5rem;"></i>
                            </div>
                        @endif
                        
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="wholesale-badge">WHOLESALE</span>
                        </div>
                        
                        @if($product->availability === 'out_of_stock')
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-danger">Out of Stock</span>
                            </div>
                        @elseif($product->availability === 'custom')
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-warning text-dark">Custom Order</span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title">{{ $product->name }}</h6>
                        <p class="card-text text-muted small flex-grow-1">
                            {{ Str::limit($product->description, 100) }}
                        </p>
                        
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    @php
                                        // Get real wholesale pricing and offers
                                        $wholesalePrice = (float) ($product->b2b_base_price ?? $product->base_price ?? 0);
                                        $basePrice = (float) ($product->base_price ?? 0);
                                        
                                        // Get wholesale offer from database
                                        $wholesaleOffer = $product->offers->where('target_channel', 'wholesale')->first() ?? 
                                                         $product->offers->where('target_channel', 'both')->first();
                                        $wholesaleDiscountAmount = 0;
                                        $wholesaleFinalPrice = $wholesalePrice;
                                        
                                        if ($wholesaleOffer) {
                                            if ($wholesaleOffer->offer_type === 'percentage') {
                                                $wholesaleDiscountAmount = ($wholesalePrice * $wholesaleOffer->discount_value) / 100;
                                            } elseif ($wholesaleOffer->offer_type === 'fixed') {
                                                $wholesaleDiscountAmount = $wholesaleOffer->discount_value;
                                            }
                                            $wholesaleFinalPrice = $wholesalePrice - $wholesaleDiscountAmount;
                                        }
                                    @endphp
                                    
                                    @if($wholesaleOffer)
                                        <div class="text-center">
                                            <span class="badge bg-success mb-2">Special Offer!</span>
                                            <div class="price-wholesale h6 mb-0 text-success">
                                                ${{ number_format($wholesaleFinalPrice, 2) }}
                                            </div>
                                            <small class="text-muted d-block">
                                                <del class="text-muted">${{ number_format($wholesalePrice, 2) }}</del>
                                                <span class="text-success fw-bold">-${{ number_format($wholesaleDiscountAmount, 2) }}</span>
                                            </small>
                                            <small class="text-success d-block">
                                                @if($wholesaleOffer->offer_type === 'percentage')
                                                    {{ $wholesaleOffer->discount_value }}% OFF
                                                @else
                                                    ${{ number_format($wholesaleOffer->discount_value, 2) }} OFF
                                                @endif
                                            </small>
                                        </div>
                                    @else
                                        <div class="text-center">
                                            <span class="price-wholesale h6 mb-0">${{ number_format($wholesalePrice, 2) }}</span>
                                            <small class="text-muted d-block">Wholesale Price</small>
                                            @if($wholesalePrice != $basePrice)
                                                <small class="text-muted">
                                                    <del class="text-muted">${{ number_format($basePrice, 2) }}</del>
                                                </small>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                @if($product->category)
                                    <small class="text-muted">{{ $product->category->name }}</small>
                                @endif
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="{{ request()->is('b2b*') ? route('b2b.customer.products.show', $product->slug) : route('retail.customer.products.show', $product->slug) }}" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                                
                                @if($product->availability !== 'out_of_stock')
                                    <button type="button" 
                                            class="btn btn-primary btn-sm add-to-cart" 
                                            data-product-id="{{ $product->id }}"
                                            data-product-name="{{ $product->name }}"
                                            data-product-price="{{ $wholesaleFinalPrice }}">
                                        <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                                    </button>
                                @else
                                    <button type="button" class="btn btn-secondary btn-sm" disabled>
                                        <i class="fas fa-times me-1"></i>Out of Stock
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <!-- Pagination -->
    <div class="row mt-5">
        <div class="col-12">
            {{ $products->links() }}
        </div>
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-box-open text-muted" style="font-size: 4rem;"></i>
        <h4 class="mt-3">No Wholesale Products Found</h4>
        <p class="text-muted">We're working on adding more wholesale products. Please check back soon!</p>
        <a href="{{ request()->is('b2b*') ? route('b2b.customer.products.index') : route('retail.customer.products.index') }}" class="btn btn-primary">
            <i class="fas fa-arrow-left me-2"></i>Back to All Products
        </a>
    </div>
@endif

<!-- Cart Modal -->
<div class="modal fade" id="cartModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Wholesale Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="cart-items">
                    <!-- Cart items will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Continue Shopping</button>
                <button type="button" class="btn btn-primary" id="checkout-btn">
                    <i class="fab fa-whatsapp me-2"></i>Send Quote Request
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Search Controls */
.card {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.form-label {
    font-size: 0.875rem;
    color: #495057;
    margin-bottom: 0.25rem;
}

.form-select {
    border: 1px solid #ced4da;
    border-radius: 8px;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.form-control {
    border: 1px solid #ced4da;
    border-radius: 8px;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn {
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0b5ed7 0%, #0a58ca 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.btn-outline-primary {
    border: 1px solid #0d6efd;
    color: #0d6efd;
}

.btn-outline-primary:hover {
    background: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}

/* Responsive */
@media (max-width: 768px) {
    .row > div {
        margin-bottom: 1rem;
    }
    
    .d-flex.gap-2 {
        flex-direction: column;
        gap: 0.5rem !important;
    }
    
    .d-flex.gap-2 .btn {
        width: 100%;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let cart = JSON.parse(localStorage.getItem('chibo_wholesale_cart')) || [];
    
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const productPrice = parseFloat(this.dataset.productPrice);
            
            // Check if product already in cart
            const existingItem = cart.find(item => item.id === productId);
            
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: productId,
                    name: productName,
                    price: productPrice,
                    quantity: 1
                });
            }
            
            localStorage.setItem('chibo_wholesale_cart', JSON.stringify(cart));
            updateCartDisplay();
            
            // Show success message
            showAlert('Product added to wholesale cart!', 'success');
        });
    });
    
    // Update cart display
    function updateCartDisplay() {
        const cartItems = document.getElementById('cart-items');
        if (cart.length === 0) {
            cartItems.innerHTML = '<p class="text-muted">Your wholesale cart is empty</p>';
            return;
        }
        
        let html = '';
        let total = 0;
        
        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            
            html += `
                <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                    <div>
                        <h6 class="mb-0">${item.name}</h6>
                        <small class="text-muted">$${item.price.toFixed(2)} x ${item.quantity}</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="me-3 fw-bold">$${itemTotal.toFixed(2)}</span>
                        <button class="btn btn-sm btn-outline-danger remove-item" data-id="${item.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        
        html += `<hr><div class="d-flex justify-content-between"><h5>Total: $${total.toFixed(2)}</h5></div>`;
        cartItems.innerHTML = html;
        
        // Add remove item functionality
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.dataset.id;
                cart = cart.filter(item => item.id !== itemId);
                localStorage.setItem('chibo_wholesale_cart', JSON.stringify(cart));
                updateCartDisplay();
            });
        });
    }
    
    // Checkout functionality
    document.getElementById('checkout-btn').addEventListener('click', function() {
        if (cart.length === 0) {
            showAlert('Your wholesale cart is empty!', 'warning');
            return;
        }
        
        let message = 'Hello! I would like to request a wholesale quote:\n\n';
        let total = 0;
        
        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            message += `${item.name} - $${item.price.toFixed(2)} x ${item.quantity} = $${itemTotal.toFixed(2)}\n`;
        });
        
        message += `\nTotal: $${total.toFixed(2)}\n\n`;
        message += 'Please provide:\n';
        message += '- Confirmed pricing\n';
        message += '- Minimum order quantities\n';
        message += '- Delivery timeline\n';
        message += '- Payment terms\n\n';
        message += 'Thank you!';
        
        const whatsappUrl = `https://wa.me/255687183330?text=${encodeURIComponent(message)}`;
        window.open(whatsappUrl, '_blank');
        
        // Clear cart after checkout
        localStorage.removeItem('chibo_wholesale_cart');
        cart = [];
        updateCartDisplay();
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('cartModal'));
        modal.hide();
        
        showAlert('Quote request sent via WhatsApp! We will contact you soon.', 'success');
    });
    
    // Show alert function
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
    
    // Initialize cart display
    updateCartDisplay();
});
</script>
@endpush
