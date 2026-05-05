@extends('wholesale.layouts.app')

@section('title', 'Wholesale Cart - CHIBO BRAND B2B')
@section('description', 'Review your wholesale order and request a quote.')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">
        <i class="fas fa-shopping-cart me-2"></i>Wholesale Cart
    </h2>

    <div id="cart-container">
        @if(isset($cart) && count($cart) > 0)
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Variants</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cart-items">
                                        @foreach($cart as $index => $item)
                                            <tr data-index="{{ $index }}">
                                                <td>
                                                    <strong>{{ $item['name'] ?? 'Product' }}</strong>
                                                    @if(isset($item['barcode']))
                                                        <br><small class="text-muted">{{ $item['barcode'] }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(isset($item['variants']) && count($item['variants']) > 0)
                                                        @foreach($item['variants'] as $variant)
                                                            <span class="badge bg-secondary">
                                                                {{ $variant['category'] }}: {{ $variant['name'] }}
                                                            </span>
                                                        @endforeach
                                                    @else
                                                        <small class="text-muted">No variants</small>
                                                    @endif
                                                </td>
                                                <td>TZS {{ number_format($item['price'] ?? 0, 0) }}</td>
                                                <td>
                                                    <input type="number" 
                                                           class="form-control form-control-sm qty-update" 
                                                           value="{{ $item['quantity'] ?? 1 }}" 
                                                           min="1" 
                                                           data-index="{{ $index }}"
                                                           style="width: 80px;">
                                                </td>
                                                <td>
                                                    <strong>TZS {{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 0) }}</strong>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-danger remove-item" data-index="{{ $index }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong>TZS {{ number_format($totals['subtotal'] ?? 0, 0) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Items:</span>
                                <strong>{{ $totals['items'] ?? 0 }}</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong class="text-primary">TZS {{ number_format($totals['total'] ?? 0, 0) }}</strong>
                            </div>

                            <button class="btn btn-success w-100 mb-2" id="request-quote-btn">
                                <i class="fab fa-whatsapp me-2"></i>Request Quote via WhatsApp
                            </button>
                            
                            <a href="{{ route('wholesale.categories.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                <h4>Your cart is empty</h4>
                <p class="text-muted">Add some products to get started!</p>
                <a href="{{ route('wholesale.products.index') }}" class="btn btn-primary">
                    <i class="fas fa-box me-2"></i>Browse Products
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load cart from localStorage
    let cart = [];
    try {
        cart = JSON.parse(localStorage.getItem('chibo_wholesale_cart') || '[]');
    } catch(e) {
        cart = [];
    }

    // Display cart items
    displayCart();

    // Update quantity
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('qty-update')) {
            const index = parseInt(e.target.dataset.index);
            const newQty = parseInt(e.target.value);
            
            if (cart[index] && newQty > 0) {
                cart[index].quantity = newQty;
                localStorage.setItem('chibo_wholesale_cart', JSON.stringify(cart));
                displayCart();
            }
        }
    });

    // Remove item
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            const index = parseInt(e.target.closest('.remove-item').dataset.index);
            cart.splice(index, 1);
            localStorage.setItem('chibo_wholesale_cart', JSON.stringify(cart));
            displayCart();
            updateCartBadge();
        }
    });

    // Request quote via WhatsApp
    document.getElementById('request-quote-btn')?.addEventListener('click', function() {
        if (cart.length === 0) {
            alert('Your cart is empty!');
            return;
        }

        let message = 'Hello CHIBO BRAND! I would like to request a wholesale quote for:\n\n';
        
        cart.forEach((item, index) => {
            message += `${index + 1}. ${item.name}\n`;
            message += `   Quantity: ${item.quantity}\n`;
            message += `   Price: TZS ${item.price.toLocaleString()}\n`;
            
            if (item.variants && item.variants.length > 0) {
                message += `   Variants:\n`;
                item.variants.forEach(v => {
                    message += `   - ${v.category}: ${v.name}\n`;
                });
            }
            message += `   Subtotal: TZS ${(item.price * item.quantity).toLocaleString()}\n\n`;
        });

        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        message += `Total: TZS ${total.toLocaleString()}`;

        const whatsappUrl = `https://wa.me/255687183330?text=${encodeURIComponent(message)}`;
        window.open(whatsappUrl, '_blank');
    });

    function displayCart() {
        const container = document.getElementById('cart-container');
        
        if (cart.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                    <h4>Your cart is empty</h4>
                    <p class="text-muted">Add some products to get started!</p>
                    <a href="{{ route('wholesale.products.index') }}" class="btn btn-primary">
                        <i class="fas fa-box me-2"></i>Browse Products
                    </a>
                </div>
            `;
            return;
        }

        let subtotal = 0;
        let itemsHtml = '';

        cart.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;

            let variantsHtml = '';
            if (item.variants && item.variants.length > 0) {
                variantsHtml = item.variants.map(v => 
                    `<span class="badge bg-secondary">${v.category}: ${v.name}</span>`
                ).join(' ');
            } else {
                variantsHtml = '<small class="text-muted">No variants</small>';
            }

            itemsHtml += `
                <tr data-index="${index}">
                    <td>
                        <strong>${item.name}</strong>
                        ${item.barcode ? `<br><small class="text-muted">${item.barcode}</small>` : ''}
                    </td>
                    <td>${variantsHtml}</td>
                    <td>TZS ${item.price.toLocaleString()}</td>
                    <td>
                        <input type="number" 
                               class="form-control form-control-sm qty-update" 
                               value="${item.quantity}" 
                               min="1" 
                               data-index="${index}"
                               style="width: 80px;">
                    </td>
                    <td><strong>TZS ${itemTotal.toLocaleString()}</strong></td>
                    <td>
                        <button class="btn btn-sm btn-danger remove-item" data-index="${index}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        container.innerHTML = `
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Variants</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>${itemsHtml}</tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong>TZS ${subtotal.toLocaleString()}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Items:</span>
                                <strong>${cart.length}</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong class="text-primary">TZS ${subtotal.toLocaleString()}</strong>
                            </div>

                            <button class="btn btn-success w-100 mb-2" id="request-quote-btn">
                                <i class="fab fa-whatsapp me-2"></i>Request Quote via WhatsApp
                            </button>
                            
                            <a href="{{ route('wholesale.categories.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Re-attach event listener for new quote button
        document.getElementById('request-quote-btn')?.addEventListener('click', function() {
            if (cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }

            let message = 'Hello CHIBO BRAND! I would like to request a wholesale quote for:\n\n';
            
            cart.forEach((item, index) => {
                message += `${index + 1}. ${item.name}\n`;
                message += `   Quantity: ${item.quantity}\n`;
                message += `   Price: TZS ${item.price.toLocaleString()}\n`;
                
                if (item.variants && item.variants.length > 0) {
                    message += `   Variants:\n`;
                    item.variants.forEach(v => {
                        message += `   - ${v.category}: ${v.name}\n`;
                    });
                }
                message += `   Subtotal: TZS ${(item.price * item.quantity).toLocaleString()}\n\n`;
            });

            const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            message += `Total: TZS ${total.toLocaleString()}`;

            const whatsappUrl = `https://wa.me/255687183330?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        });
    }

    function updateCartBadge() {
        const badges = document.querySelectorAll('#cart-count, .cart-count');
        badges.forEach(badge => {
            badge.textContent = cart.length;
            if (cart.length > 0) {
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        });
    }
});
</script>
@endpush
