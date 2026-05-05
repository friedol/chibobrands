@extends('public.layouts.app')

@section('title', $category->name . ' - CHIBO BRAND')
@section('description', 'Browse products under the ' . $category->name . ' category.')

@push('styles')
<style>
    .category-header {
        background: linear-gradient(135deg, #1a0000 0%, #4d0000 50%, #1a0000 100%);
        padding: 2rem 0;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .category-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 30% 50%, rgba(255, 0, 0, 0.1) 0%, transparent 50%);
        animation: pulse 8s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 0.5; }
        50% { opacity: 1; }
    }
    
    .category-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: white;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        margin-bottom: 0.5rem;
    }
    
    .category-desc {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1.1rem;
    }
    
    .category-image-badge {
        width: 100px;
        height: 100px;
        border-radius: 15px;
        object-fit: cover;
        border: 3px solid white;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }
    
    .product-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(230, 0, 0, 0.2);
    }
    
    .product-image {
        height: 250px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .product-card:hover .product-image {
        transform: scale(1.05);
    }
    
    .price-tag {
        background: linear-gradient(135deg, #e60000, #ff3333);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 1.2rem;
        display: inline-block;
        box-shadow: 0 4px 10px rgba(230, 0, 0, 0.3);
    }
    
    .price-range {
        color: #6c757d;
        font-size: 0.9rem;
        margin-top: 0.25rem;
    }
    
    .btn-view-product {
        background: #e60000;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-view-product:hover {
        background: white;
        color: #e60000;
        box-shadow: 0 0 20px rgba(230, 0, 0, 0.4);
    }
    
    .breadcrumb-custom {
        background: rgba(255, 255, 255, 0.1);
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        display: inline-flex;
        backdrop-filter: blur(10px);
    }
    
    .breadcrumb-custom a {
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .breadcrumb-custom a:hover {
        color: rgba(255, 255, 255, 0.8);
    }
    
    .product-count-badge {
        background: white;
        color: #e60000;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 600;
        display: inline-block;
    }
    

    /* Mobile responsive font sizes */
    @media (max-width: 768px) {
        .category-title {
            font-size: 1.5rem !important;
        }
        
        .category-desc {
            font-size: 0.9rem !important;
        }
        
        .product-count-badge {
            font-size: 0.85rem !important;
            padding: 0.4rem 0.8rem !important;
        }
        
        .category-header {
            padding: 1.5rem 0 !important;
            margin-bottom: 1.5rem !important;
        }
        
        .breadcrumb-custom {
            padding: 0.5rem 1rem !important;
            font-size: 0.8rem !important;
        }
        
        /* Optimized product card styles */
        .product-card {
            height: 100% !important;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(230, 0, 0, 0.2);
        }
        
        .product-image {
            height: 180px !important;
        }
        
        .card-title {
            font-size: 0.85rem !important;
            line-height: 1.2 !important;
        }
        
        .card-body {
            font-size: 0.8rem !important;
            padding: 0.75rem !important;
        }
        
        .price-tag {
            font-size: 0.9rem !important;
            padding: 0.3rem 0.6rem !important;
        }
        
        .btn-view-product, .btn-outline-danger {
            font-size: 0.75rem !important;
            padding: 0.4rem 0.8rem !important;
        }
        
        .modal-title {
            font-size: 1rem !important;
        }
        
        .form-label {
            font-size: 0.85rem !important;
        }
        
        .btn-lg {
            padding: 0.75rem 1rem !important;
            font-size: 0.9rem !important;
        }
        
        small {
            font-size: 0.7rem !important;
        }
        
        .variant-btn {
            font-size: 0.75rem !important;
            padding: 0.35rem 0.5rem !important;
        }
        
        .variant-btn small {
            font-size: 0.65rem !important;
        }
        
        /* Further reduce all font sizes */
        h6.card-title {
            font-size: 0.8rem !important;
        }
        
        .price-tag {
            font-size: 0.85rem !important;
            padding: 0.25rem 0.5rem !important;
        }
        
        .btn-view-product {
            font-size: 0.7rem !important;
            padding: 0.35rem 0.6rem !important;
        }
        
        small {
            font-size: 0.65rem !important;
        }
        
        .card-body {
            padding: 0.65rem !important;
        }
        
    }
</style>
@endpush

@section('content')
<!-- Category Header -->
<section class="category-header">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb breadcrumb-custom mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fas fa-home me-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Categories</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">{{ $category->name }}</li>
                    </ol>
                </nav>
                <h1 class="category-title">{{ $category->name }}</h1>
                @if($category->description)
                    <p class="category-desc mb-3">{{ $category->description }}</p>
                @endif
                <span class="product-count-badge">
                    <i class="fas fa-box me-2"></i>{{ $products->total() }} {{ Str::plural('Product', $products->total()) }}
                </span>
            </div>
            @if(!empty($category->image_path))
            <div class="col-lg-4 text-end d-none d-lg-block">
                <img src="{{ asset('storage/'.$category->image_path) }}" alt="{{ $category->name }}" class="category-image-badge" onerror="this.src='{{ asset('images/default.webp') }}'">
            </div>
            @endif
        </div>
    </div>
</section>

<div class="container pb-5">

    <!-- Products Grid -->
    @if($products->count())
        <div class="row g-4">
            @foreach($products as $product)
                @php
                    // Get product image
                    $img = $product->images->first();
                    $imgUrl = $img && !empty($img->image_path) 
                        ? asset('storage/'.$img->image_path) 
                        : asset('images/default.webp');
                    
                    // Clean product name
                    $displayName = preg_replace('/\s+\d+$/', '', (string) $product->name);
                    
                    // Get view URL
                    $viewUrl = !empty($product->barcode) 
                        ? route('product.show', $product->barcode) 
                        : '#';
                    
                    // Use retail_base_price from enhanced_products
                    $price = $product->retail_base_price ?? $product->buying_price ?? 0;

                    // Minimum order quantity (fallback to 1)
                    $minOrderQty = (int) ($product->min_quantity ?? 1);
                @endphp
                <div class="col-6 col-sm-6 col-md-4 col-lg-3">
                    <div class="card product-card h-100" onclick="window.location.href='{{ $viewUrl }}'" style="cursor: pointer;">
                        <div class="position-relative overflow-hidden">
                            <img src="{{ $imgUrl }}" alt="{{ $displayName }}" class="card-img-top product-image" onerror="this.src='{{ asset('images/default.webp') }}'">
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title fw-bold mb-2">{{ $displayName }}</h6>
                            @if(!empty($product->barcode))
                                <small class="text-muted d-block mb-2">
                                    <i class="fas fa-barcode me-1"></i>{{ $product->barcode }}
                                </small>
                            @endif
                            
                            <div class="mt-auto">
                                <div class="mb-3">
                                    <span class="price-tag">
                                        TZS {{ number_format($price, 0) }}
                                    </span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-view-product flex-grow-1" data-bs-toggle="modal" data-bs-target="#productModal-{{ $product->id }}" onclick="event.preventDefault(); event.stopPropagation();">
                                        <i class="fas fa-cart-plus me-2"></i>Place Order
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Product Modal with Live Price Calculation -->
                <div class="modal fade" id="productModal-{{ $product->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Place Order - {{ $displayName }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <img src="{{ $imgUrl }}" class="img-fluid rounded" alt="{{ $displayName }}" onerror="this.src='{{ asset('images/default.webp') }}'">
                                    </div>
                                    <div class="col-md-7">
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-1">Base Price</h6>
                                            <h3 class="text-danger fw-bold">TZS {{ number_format($price, 0) }}</h3>
                                        </div>
                                        
                                        @if(method_exists($product, 'variantCategories') && $product->variantCategories && $product->variantCategories->count())
                                            <div class="mb-3">
                                                <h6 class="mb-2">Select Options:</h6>
                                                @foreach($product->variantCategories as $varCat)
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">{{ $varCat->category }}</label>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @foreach($varCat->items as $item)
                                                                @php
                                                                    $variantPrice = $channel === 'wholesale' ? ($item->wholesale_price ?? $item->price) : ($item->retail_price ?? $item->price);
                                                                @endphp
                                                                <button type="button" 
                                                                    class="btn btn-outline-secondary variant-btn" 
                                                                    data-product-id="{{ $product->id }}"
                                                                    data-category="{{ $varCat->category }}"
                                                                    data-name="{{ $item->name }}"
                                                                    data-price="{{ $variantPrice ?? 0 }}">
                                                                    {{ $item->name }}
                                                                    @if($variantPrice > 0)
                                                                        <small class="text-success">+{{ number_format($variantPrice, 0) }}</small>
                                                                    @endif
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                        
                                        <div class="row g-2 mb-3">
                                            <div class="col-12 col-sm-6">
                                                <label class="form-label">Quantity</label>
                                                <div class="input-group">
                                                    <button class="btn btn-outline-secondary" type="button"
                                                            onclick="changeCategoryQty({{ $product->id }}, -1)">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                <input type="number" 
                                                        class="form-control text-center qty-input" 
                                                    id="qty-{{ $product->id }}"
                                                    data-product-id="{{ $product->id }}"
                                                        value="{{ $minOrderQty }}" 
                                                        min="{{ $minOrderQty }}">
                                                    <button class="btn btn-outline-secondary" type="button"
                                                            onclick="changeCategoryQty({{ $product->id }}, 1)">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="card bg-light mb-3">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Unit Price:</span>
                                                    <strong id="unit-price-{{ $product->id }}">TZS {{ number_format($price, 0) }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>Total:</span>
                                                    <strong class="text-danger fs-5" id="total-price-{{ $product->id }}">TZS {{ number_format($price, 0) }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button class="btn btn-danger w-100 btn-lg add-to-cart-btn" 
                                            data-product-id="{{ $product->id }}"
                                            data-barcode="{{ $product->barcode }}"
                                            data-name="{{ $product->name }}"
                                            data-base-price="{{ $price }}"
                                            data-minqty="{{ $minOrderQty }}"
                                            data-bs-dismiss="modal">
                                            <i class="fas fa-shopping-cart me-2"></i>Place Order
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-5">
            {{ $products->links() }}
        </div>
    @else
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-box-open text-muted" style="font-size:4rem;"></i>
            </div>
            <h4 class="fw-bold mb-2">No Products Found</h4>
            <p class="text-muted mb-4">There are currently no products in this category.</p>
            <a href="{{ route('categories.index') }}" class="btn btn-view-product">
                <i class="fas fa-arrow-left me-2"></i>Back to Categories
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
// Template URL for tiered price API (same as product listing & details)
const productPriceUrlTemplate = '{{ route('product.get-price', ['barcode' => '__BARCODE__']) }}';

// Helper: fetch unit price from backend using volume-discount logic
async function fetchUnitPriceForQuantity(barcode, customerType, quantity, fallbackBase) {
    const safeQty = quantity && quantity > 0 ? quantity : 1;
    const customer = customerType || 'retail';
    
    try {
        const url = productPriceUrlTemplate.replace('__BARCODE__', encodeURIComponent(barcode)) +
            `?quantity=${encodeURIComponent(safeQty)}&customer_type=${encodeURIComponent(customer)}`;
        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (response.ok) {
            const data = await response.json();
            if (data && typeof data.unit_price !== 'undefined') {
                return parseFloat(data.unit_price);
            }
        }
    } catch (e) {
        console.error('Failed to fetch tiered price', e);
    }
    
    // Fallback: base price when no tier/endpoint available
    return parseFloat(fallbackBase || 0);
}

// Store selected variants per product
const selectedVariants = {};

// Initialize price display when modal opens
document.querySelectorAll('[data-bs-toggle="modal"]').forEach(btn => {
    btn.addEventListener('click', function() {
        const modalId = this.getAttribute('data-bs-target');
        const productId = modalId.replace('#productModal-', '');
        selectedVariants[productId] = {};
        updatePrice(productId);
    });
});

// Handle variant selection with instant feedback
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('variant-btn') || e.target.closest('.variant-btn')) {
        const btn = e.target.classList.contains('variant-btn') ? e.target : e.target.closest('.variant-btn');
        const productId = btn.dataset.productId;
        const category = btn.dataset.category;
        const name = btn.dataset.name;
        const price = parseFloat(btn.dataset.price || 0);
        
        // Initialize if needed
        if (!selectedVariants[productId]) selectedVariants[productId] = {};
        
        // Toggle selection
        if (btn.classList.contains('active')) {
            // Deselect
            btn.classList.remove('active');
            delete selectedVariants[productId][category];
        } else {
            // Deselect other buttons in same category
            const modal = btn.closest('.modal');
            modal.querySelectorAll(`.variant-btn[data-category="${category}"]`).forEach(b => {
                b.classList.remove('active');
            });
            // Select this button
            btn.classList.add('active');
            selectedVariants[productId][category] = { name, price };
        }
        
        // Update price instantly
        updatePrice(productId);
    }
});

// Handle quantity change with instant update
document.querySelectorAll('.qty-input').forEach(input => {
    input.addEventListener('input', function() {
        const productId = this.dataset.productId;
        updatePrice(productId);
    });
});

// Plus/minus quantity controls for modal (mobile-friendly)
function changeCategoryQty(productId, delta) {
    const qtyInput = document.getElementById(`qty-${productId}`);
    if (!qtyInput) return;
    const min = parseInt(qtyInput.getAttribute('min') || '1');
    let current = parseInt(qtyInput.value || String(min));
    if (isNaN(current) || current < min) current = min;
    current += delta;
    if (current < min) current = min;
    qtyInput.value = current;
    updatePrice(productId);
}

// Update price calculation instantly (uses volume discounts from backend)
async function updatePrice(productId) {
    const qtyInput = document.getElementById(`qty-${productId}`);
    const unitPriceEl = document.getElementById(`unit-price-${productId}`);
    const totalPriceEl = document.getElementById(`total-price-${productId}`);
    const addToCartBtn = document.querySelector(`.add-to-cart-btn[data-product-id="${productId}"]`);
    
    if (!qtyInput || !unitPriceEl || !totalPriceEl || !addToCartBtn) return;
    
    // Get base price & barcode
    const basePrice = parseFloat(addToCartBtn.dataset.basePrice || 0);
    const barcode = addToCartBtn.dataset.barcode || '';
    
    // Calculate variant additions
    let variantTotal = 0;
    const variants = selectedVariants[productId] || {};
    Object.values(variants).forEach(v => {
        variantTotal += parseFloat(v.price || 0);
    });
    
    // Clamp quantity to minimum
    const minQty = parseInt(qtyInput.getAttribute('min') || '1');
    let qty = parseInt(qtyInput.value || String(minQty));
    if (isNaN(qty) || qty < minQty) {
        qty = minQty;
        qtyInput.value = minQty;
    }

    // Get unit price from backend (volume discounts), then add variants
    let unitPrice = await fetchUnitPriceForQuantity(barcode, 'retail', qty, basePrice);
    unitPrice = unitPrice + variantTotal;
    const total = unitPrice * qty;
    
    // Update display instantly with animation
    unitPriceEl.style.transition = 'all 0.3s ease';
    totalPriceEl.style.transition = 'all 0.3s ease';
    
    unitPriceEl.textContent = `TZS ${unitPrice.toLocaleString('en-US', {maximumFractionDigits: 0})}`;
    totalPriceEl.textContent = `TZS ${total.toLocaleString('en-US', {maximumFractionDigits: 0})}`;
    
    // Flash effect
    unitPriceEl.style.color = '#dc3545';
    totalPriceEl.style.transform = 'scale(1.05)';
    setTimeout(() => {
        unitPriceEl.style.color = '';
        totalPriceEl.style.transform = 'scale(1)';
    }, 300);
}

// Add to cart functionality
document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const productId = this.dataset.productId;
        const barcode = this.dataset.barcode;
        const name = this.dataset.name;
        const basePrice = parseFloat(this.dataset.basePrice || 0);
        const minQty = parseInt(this.dataset.minqty || '1');
        
        const qtyInput = document.getElementById(`qty-${productId}`);
        let qty = parseInt(qtyInput.value || String(minQty));
        if (isNaN(qty) || qty < minQty) {
            qty = minQty;
            qtyInput.value = minQty;
        }
        
        // Calculate final unit price with variants
        let variantTotal = 0;
        const variants = selectedVariants[productId] || {};
        Object.values(variants).forEach(v => {
            variantTotal += parseFloat(v.price || 0);
        });
        
        // Get unit price with volume discounts applied
        let unitPrice = await fetchUnitPriceForQuantity(barcode, 'retail', qty, basePrice);
        unitPrice = unitPrice + variantTotal;
        const total = unitPrice * qty;
        
        // Create cart item
        const item = {
            barcode,
            name,
            channel: 'retail',
            qty,
            unitPrice,
            total,
            variants: variants,
            minQuantity: minQty
        };
        
        // Add to localStorage cart
        const cartKey = window.location.hostname.includes('b2b') || window.location.pathname.includes('/b2b') ? 'chibo_wholesale_cart' : 'chibo_retail_cart';
        let cart = [];
        try {
            cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
        } catch(e) {
            cart = [];
        }
        
        cart.push(item);
        localStorage.setItem(cartKey, JSON.stringify(cart));
        
        // Update all cart badges
        document.querySelectorAll('#cart-count, .cart-count').forEach(badge => {
            badge.textContent = cart.length;
            if (cart.length > 0) {
                badge.style.display = 'inline-block';
            }
        });
        
        // Show success toast
        showSuccessToast(name, qty);
    });
});

// Show success toast message
function showSuccessToast(productName, qty) {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        animation: slideIn 0.3s ease-out;
    `;
    toast.innerHTML = `
        <i class="fas fa-check-circle me-2"></i>
        <strong>${qty}x ${productName}</strong> added to cart!
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Initialize cart badge on page load
document.addEventListener('DOMContentLoaded', function() {
    try {
        const cartKey = window.location.hostname.includes('b2b') || window.location.pathname.includes('/b2b') ? 'chibo_wholesale_cart' : 'chibo_retail_cart';
        const cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
        document.querySelectorAll('#cart-count, .cart-count').forEach(badge => {
            badge.textContent = cart.length;
        });
    } catch(e) {
        document.querySelectorAll('#cart-count, .cart-count').forEach(badge => {
            badge.textContent = '0';
        });
    }
});
</script>

<style>
@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}
.variant-btn {
    transition: all 0.3s ease;
}

.variant-btn.active {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
    color: white !important;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.variant-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>
@endpush
@endsection


