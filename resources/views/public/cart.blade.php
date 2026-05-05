@extends('public.layouts.app')

@section('title', 'Shopping Cart - CHIBO BRAND')
@section('description', 'Review your selected products and proceed to checkout.')

@section('content')
<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="page-title">Shopping Cart</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Cart</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0 text-muted">{{ count($cart) }} item(s) in cart</p>
            </div>
        </div>
    </div>
</div>

<div class="container">
    @if(count($cart) > 0)
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Cart Items</h5>
                    </div>
                    <div class="card-body">
                        @foreach($cart as $item)
                            <div class="row align-items-center mb-4 cart-item" data-item-id="{{ $item['id'] }}">
                                <div class="col-md-2">
                                    @if($item['image'])
                                        <img src="{{ $item['image'] }}" class="img-fluid rounded" alt="{{ $item['name'] }}">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 80px;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <h6 class="mb-1">{{ $item['name'] }}</h6>
                                    <p class="text-muted small mb-0">
                                        <span class="base-price">TZS {{ number_format($item['price'], 0) }}</span>
                                        <span class="vat-price" style="display: none;">TZS {{ number_format($item['price'] * 1.18, 0) }}</span>
                                        <span class="price-label"> each</span>
                                    </p>
                                    
                                    <!-- Variations -->
                                    @if(!empty($item['variations']) || !empty($item['variants']))
                                        <div class="mt-2">
                                            @if(!empty($item['variations']))
                                                @foreach($item['variations'] as $variation)
                                                    <small class="badge bg-secondary me-1">
                                                        {{ $variation['name'] }}: {{ $variation['option_value'] }}
                                                    </small>
                                                @endforeach
                                            @elseif(!empty($item['variants']))
                                                @foreach($item['variants'] as $key => $value)
                                                    <small class="badge bg-secondary me-1">
                                                        {{ $key }}: {{ $value }}
                                                    </small>
                                                @endforeach
                                            @endif
                                        </div>
                                    @endif
                                    
                                    <!-- Add-ons -->
                                    @if(!empty($item['addons']))
                                        <div class="mt-2">
                                            @foreach($item['addons'] as $addon)
                                                <small class="badge bg-info me-1">
                                                    {{ $addon['addon_name'] }}
                                                </small>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group" style="max-width: 120px;">
                                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] - 1 }})">-</button>
                                        <input type="number" class="form-control form-control-sm text-center" 
                                               value="{{ $item['quantity'] }}" min="1" max="100" 
                                               onchange="updateQuantity('{{ $item['id'] }}', this.value)">
                                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] + 1 }})">+</button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-end">
                                        <div class="fw-bold">
                                            <span class="base-subtotal">TZS {{ number_format($item['price'] * $item['quantity'], 0) }}</span>
                                            <span class="vat-subtotal" style="display: none;">TZS {{ number_format($item['price'] * $item['quantity'] * 1.18, 0) }}</span>
                                        </div>
                                        <button class="btn btn-sm btn-outline-danger mt-1" onclick="removeItem('{{ $item['id'] }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Continue Shopping -->
                <div class="text-center mt-4">
                    <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <!-- VAT Receipt Option -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="vatReceipt" onchange="toggleVATReceipt()">
                                <label class="form-check-label" for="vatReceipt">
                                    <strong>Include VAT Receipt (+18%)</strong>
                                    <small class="text-muted d-block">Check this if you need a VAT receipt for your purchase</small>
                                </label>
                            </div>
                        </div>
                        
                        <div class="small">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Subtotal:</span>
                                <span id="subtotal-display">TZS {{ number_format($totals['subtotal'], 0) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1" id="vat-row" style="display: none;">
                                <span>VAT (18%):</span>
                                <span id="vat-amount">TZS {{ number_format($totals['vat'], 0) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Shipping:</span>
                                <span>TZS {{ number_format($totals['shipping'], 0) }}</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between">
                                <strong>Total:</strong>
                                <strong class="text-primary" id="total-display">TZS {{ number_format($totals['total'], 0) }}</strong>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="{{ route('checkout') }}" class="btn btn-primary btn-lg" id="checkout-btn">
                                <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                            </a>
                            <button class="btn btn-outline-danger" onclick="clearCart()">
                                <i class="fas fa-trash me-2"></i>Clear Cart
                            </button>
                        </div>
                    </div>
                </div>

                <!-- WhatsApp Quick Order -->
                <div class="card mt-3">
                    <div class="card-body text-center">
                        <h6 class="card-title">Quick Order via WhatsApp</h6>
                        <p class="text-muted small">Skip the checkout form and order directly via WhatsApp</p>
                        <a href="https://wa.me/255655392319?text={{ urlencode('Hello CHIBO BRAND 👋, I\'d like to place an order. Please check my cart on the website.') }}" 
                           class="btn btn-success" target="_blank">
                            <i class="fab fa-whatsapp me-2"></i>Order via WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Empty Cart -->
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart text-muted" style="font-size: 4rem;"></i>
            <h3 class="mt-3">Your Cart is Empty</h3>
            <p class="text-muted">Looks like you haven't added any items to your cart yet.</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-bag me-2"></i>Start Shopping
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Toggle VAT receipt option
    function toggleVATReceipt() {
        const vatCheckbox = document.getElementById('vatReceipt');
        const vatRow = document.getElementById('vat-row');
        const basePrices = document.querySelectorAll('.base-price');
        const vatPrices = document.querySelectorAll('.vat-price');
        const baseSubtotals = document.querySelectorAll('.base-subtotal');
        const vatSubtotals = document.querySelectorAll('.vat-subtotal');
        const checkoutBtn = document.getElementById('checkout-btn');
        
        if (vatCheckbox.checked) {
            // Show VAT prices
            vatRow.style.display = 'flex';
            basePrices.forEach(price => price.style.display = 'none');
            vatPrices.forEach(price => price.style.display = 'inline');
            baseSubtotals.forEach(subtotal => subtotal.style.display = 'none');
            vatSubtotals.forEach(subtotal => subtotal.style.display = 'inline');
            
            // Update checkout URL to include VAT
            checkoutBtn.href = '{{ route("checkout") }}?vat_receipt=1';
            
            // Calculate and display VAT amount
            updateVATTotals();
        } else {
            // Show base prices
            vatRow.style.display = 'none';
            basePrices.forEach(price => price.style.display = 'inline');
            vatPrices.forEach(price => price.style.display = 'none');
            baseSubtotals.forEach(subtotal => subtotal.style.display = 'inline');
            vatSubtotals.forEach(subtotal => subtotal.style.display = 'none');
            
            // Update checkout URL to exclude VAT
            checkoutBtn.href = '{{ route("checkout") }}';
            
            // Reset totals
            updateVATTotals();
        }
    }
    
    // Update VAT totals
    function updateVATTotals() {
        const vatCheckbox = document.getElementById('vatReceipt');
        const subtotalDisplay = document.getElementById('subtotal-display');
        const vatAmount = document.getElementById('vat-amount');
        const totalDisplay = document.getElementById('total-display');
        
        // Get base subtotal from the original PHP value
        const baseSubtotal = {{ $totals['subtotal'] }};
        const shipping = {{ $totals['shipping'] }};
        const currentVAT = {{ $totals['vat'] }};
        
        if (vatCheckbox.checked) {
            const vatValue = Math.round(baseSubtotal * 0.18);
            const newSubtotal = baseSubtotal + vatValue;
            const newTotal = newSubtotal + shipping;
            
            subtotalDisplay.textContent = 'TZS ' + newSubtotal.toLocaleString();
            vatAmount.textContent = 'TZS ' + vatValue.toLocaleString();
            totalDisplay.textContent = 'TZS ' + newTotal.toLocaleString();
        } else {
            subtotalDisplay.textContent = 'TZS ' + baseSubtotal.toLocaleString();
            vatAmount.textContent = 'TZS 0';
            totalDisplay.textContent = 'TZS ' + (baseSubtotal + shipping).toLocaleString();
        }
    }

    // Update item quantity
    function updateQuantity(itemId, newQuantity) {
        if (newQuantity < 1) {
            removeItem(itemId);
            return;
        }
        
        $.ajax({
            url: '{{ route("cart.update") }}',
            method: 'PUT',
            data: {
                item_id: itemId,
                quantity: newQuantity,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload(); // Reload to show updated totals
                }
            },
            error: function(xhr) {
                showToast('Error updating quantity', 'error');
            }
        });
    }

    // Remove item from cart
    function removeItem(itemId) {
        if (confirm('Are you sure you want to remove this item from your cart?')) {
            $.ajax({
                url: '{{ route("cart.remove") }}',
                method: 'DELETE',
                data: {
                    item_id: itemId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showToast('Item removed from cart', 'success');
                        location.reload();
                    }
                },
                error: function(xhr) {
                    showToast('Error removing item', 'error');
                }
            });
        }
    }

    // Clear entire cart
    function clearCart() {
        if (confirm('Are you sure you want to clear your entire cart?')) {
            $.ajax({
                url: '{{ route("cart.clear") }}',
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showToast('Cart cleared', 'success');
                        location.reload();
                    }
                },
                error: function(xhr) {
                    showToast('Error clearing cart', 'error');
                }
            });
        }
    }
</script>
@endpush
