@extends('public.layouts.app')

@section('title', $category->name . ' - CHIBO BRAND B2B')
@section('description', 'Browse wholesale products under the ' . $category->name . ' category.')

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
    
    /* Product Card Styles */
    .product-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        height: 100%;
    }
    
    .product-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
    }
    
    .product-image-wrapper {
        position: relative;
        background: #f1f3f5;
        overflow: hidden;
        height: 280px;
    }
    
    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .product-card:hover .product-image {
        transform: scale(1.1);
    }
    
    .badge-b2b {
        position: absolute;
        top: 12px;
        left: 12px;
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        padding: 0.35rem 0.85rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        z-index: 2;
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.4);
    }
    
    .product-body {
        padding: 1.25rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .product-name {
        font-size: 1rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 0.5rem;
        line-height: 1.4;
        min-height: 2.8rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .product-sku {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 1rem;
    }
    
    .price-container {
        margin-bottom: 1rem;
        margin-top: auto;
    }
    
    .product-price {
        background: #dc3545;
        color: white;
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        font-weight: 700;
        font-size: 1.25rem;
        display: inline-block;
        box-shadow: 0 3px 10px rgba(220, 53, 69, 0.25);
    }
    
    .price-label {
        display: block;
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 0.4rem;
        font-weight: 500;
    }
    
    .product-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .btn-details {
        background: white;
        color: #dc3545;
        border: 1px solid #dc3545;
        padding: 0.6rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s;
        text-align: center;
        text-decoration: none;
        font-size: 0.9rem;
    }
    
    .btn-details:hover {
        background: #dc3545;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }
    
    .btn-add-cart {
        background: #dc3545;
        color: white;
        border: none;
        padding: 0.7rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s;
        cursor: pointer;
        font-size: 0.95rem;
    }
    
    .btn-add-cart:hover {
        background: #bb2d3b;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(220, 53, 69, 0.4);
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .empty-state i {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 1.5rem;
    }
    
    .empty-state h4 {
        font-weight: 700;
        color: #495057;
        margin-bottom: 0.75rem;
    }
    
    .empty-state p {
        color: #6c757d;
        margin-bottom: 1.5rem;
    }
    
    /* Pagination */
    .pagination-wrapper {
        margin-top: 3rem;
        display: flex;
        justify-content: center;
    }
    
    @media (max-width: 768px) {
        .category-header {
            padding: 1.5rem 0 !important;
            margin-bottom: 1.5rem !important;
        }
        
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
        
        .breadcrumb-custom {
            padding: 0.5rem 1rem !important;
            font-size: 0.8rem !important;
        }
        
        /* Product card mobile adjustments */
        .product-image {
            height: 180px !important;
        }
        
        h6.card-title {
            font-size: 0.85rem !important;
            line-height: 1.2 !important;
        }
        
        .card-body {
            font-size: 0.8rem !important;
            padding: 0.65rem !important;
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
    
    /* Variant Section Styles */
    .variant-section {
        margin-bottom: 1rem;
    }
    
    .variant-label {
        display: block;
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.85rem;
    }
    
    .variant-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(70px, 1fr));
        gap: 0.4rem;
    }
    
    .variant-option-btn {
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-radius: 6px;
        padding: 0.4rem 0.6rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.2rem;
        min-height: 50px;
        justify-content: center;
    }
    
    .variant-option-btn:hover {
        background: #e9ecef;
        border-color: #dc3545;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .variant-option-btn.active {
        background: #dc3545;
        border-color: #dc3545;
        color: white;
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }
    
    .variant-name {
        font-weight: 500;
        font-size: 0.75rem;
        line-height: 1.2;
    }
    
    .variant-price {
        font-size: 0.65rem;
        opacity: 0.8;
        font-weight: 500;
    }
    
    /* Mobile Responsive Variants */
    @media (max-width: 768px) {
        .variant-options {
            grid-template-columns: repeat(4, 1fr);
            gap: 0.3rem;
        }
        
        .variant-option-btn {
            padding: 0.3rem 0.4rem;
            min-height: 40px;
        }
        
        .variant-name {
            font-size: 0.7rem;
        }
        
        .variant-price {
            font-size: 0.6rem;
        }
        
        .variant-label {
            font-size: 0.8rem;
        }
    }
    
    @media (max-width: 576px) {
        .variant-options {
            grid-template-columns: repeat(4, 1fr);
            gap: 0.25rem;
        }
        
        .variant-option-btn {
            padding: 0.25rem 0.3rem;
            min-height: 35px;
        }
        
        .variant-name {
            font-size: 0.65rem;
        }
        
        .variant-price {
            font-size: 0.55rem;
        }
        
        .variant-label {
            font-size: 0.75rem;
        }
    }
    
    @media (max-width: 480px) {
        .variant-options {
            grid-template-columns: repeat(4, 1fr);
            gap: 0.2rem;
        }
        
        .variant-option-btn {
            padding: 0.2rem 0.25rem;
            min-height: 32px;
        }
        
        .variant-name {
            font-size: 0.6rem;
        }
        
        .variant-price {
            font-size: 0.5rem;
        }
        
        .variant-label {
            font-size: 0.7rem;
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
                        <li class="breadcrumb-item"><a href="{{ route('wholesale.home') }}"><i class="fas fa-home me-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('wholesale.categories.index') }}">Categories</a></li>
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
                        
                        // Use b2b_base_price from enhanced_products table
                        $price = $product->b2b_base_price ?? $product->buying_price ?? 0;

                        // Minimum order quantity for wholesale (fallback to 1)
                        $minOrderQty = (int) ($product->min_quantity ?? 1);
                    @endphp
                    
                <div class="col-6 col-sm-6 col-md-4 col-lg-3">
                    <div class="card product-card h-100" onclick="window.location.href='{{ !empty($product->barcode) ? route('wholesale.product.show', $product->barcode) : '#' }}'" style="cursor: pointer;">
                        <div class="position-relative overflow-hidden">
                            <img src="{{ $imgUrl }}" alt="{{ $displayName }}" class="card-img-top product-image" onerror="this.src='{{ asset('images/default.webp') }}'">
                            <span class="badge bg-success position-absolute top-0 start-0 m-2">
                                <i class="fas fa-tags me-1"></i>B2B
                                </span>
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
                                    <button class="btn btn-view-product flex-grow-1" data-bs-toggle="modal" data-bs-target="#addToCartModal-{{ $product->id }}" onclick="event.preventDefault(); event.stopPropagation();">
                                        <i class="fas fa-cart-plus me-2"></i>Place Order
                                    </button>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Add to Cart Modal -->
                    <div class="modal fade" id="addToCartModal-{{ $product->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Place Order - {{ $displayName }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <img src="{{ $imgUrl }}" class="img-fluid rounded" alt="{{ $displayName }}" onerror="this.src='{{ asset('images/default.webp') }}'">
                                        </div>
                                        <div class="col-md-7">
                                            <div class="mb-3">
                                                <h6 class="text-muted mb-1">Price (Wholesale)</h6>
                                                <h3 class="text-danger fw-bold">TZS {{ number_format($price, 0) }}</h3>
                                            </div>

                                            @if(method_exists($product,'variantCategories') && $product->variantCategories && $product->variantCategories->count())
                                                <div class="mb-3">
                                                    <h6 class="mb-2">Select Options:</h6>
                                                    @foreach($product->variantCategories as $varCat)
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">{{ $varCat->category }}</label>
                                                        <div class="d-flex flex-wrap gap-2">
                                                                @foreach($varCat->items as $item)
                                                                @php
                                                                    $variantPrice = $item->wholesale_price ?? $item->price;
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
                                                                onclick="changeWholesaleCategoryQty({{ $product->id }}, -1)">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                    <input type="number" 
                                                           class="form-control text-center qty-input" 
                                                        id="qty-{{ $product->id }}"
                                                       data-product-id="{{ $product->id }}" 
                                                           value="{{ $minOrderQty }}" 
                                                           min="{{ $minOrderQty }}">
                                                        <button class="btn btn-outline-secondary" type="button"
                                                                onclick="changeWholesaleCategoryQty({{ $product->id }}, 1)">
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
                <a href="{{ route('wholesale.categories.index') }}" class="btn btn-view-product">
                    <i class="fas fa-arrow-left me-2"></i>Back to Categories
                </a>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Template URL for tiered price API (same endpoint, wholesale channel)
    const productPriceUrlTemplate = '{{ route('product.get-price', ['barcode' => '__BARCODE__']) }}';

    // Helper: fetch unit price from backend using wholesale volume discounts
    async function fetchUnitPriceForQuantity(barcode, quantity, fallbackBase) {
        const safeQty = quantity && quantity > 0 ? quantity : 1;
        const customer = 'wholesale';
        
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
            console.error('Failed to fetch wholesale tiered price', e);
        }
        
        // Fallback to base price when no tier/endpoint available
        return parseFloat(fallbackBase || 0);
    }

    // State: selected variants per product
    const selectedVariantsByProduct = {};
    
    // Variant selection handling
    document.querySelectorAll('.variant-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const pid = this.dataset.productId;
            const cat = this.dataset.category;
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price || '0');
            
            if (!selectedVariantsByProduct[pid]) selectedVariantsByProduct[pid] = {};
            
            // If same option is clicked, deselect it
            if (this.classList.contains('active')) {
                this.classList.remove('active');
                delete selectedVariantsByProduct[pid][cat];
            } else {
                // Deselect other options in same category
                const modal = this.closest('.modal');
                modal.querySelectorAll(`.variant-btn[data-product-id="${pid}"][data-category="${cat}"]`).forEach(sib => {
                    sib.classList.remove('active');
                });
                this.classList.add('active');
                selectedVariantsByProduct[pid][cat] = { name, price };
            }
            
            updatePriceDisplay(pid);
        });
    });
    
    // Quantity input handling (enforce min)
    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('input', function() {
            const pid = this.dataset.productId;
            const min = parseInt(this.getAttribute('min') || '1');
            let val = parseInt(this.value || String(min));
            if (isNaN(val) || val < min) {
                val = min;
                this.value = min;
            }
            updatePriceDisplay(pid);
        });
    });
    
    // Update price display (uses wholesale volume discounts from backend)
    async function updatePriceDisplay(pid) {
        const btn = document.querySelector(`[data-product-id="${pid}"].add-to-cart-btn`);
        const qtyInput = document.getElementById(`qty-${pid}`);
        if (!btn || !qtyInput) return;

        const basePrice = parseFloat(btn.dataset.basePrice || 0);
        const barcode = btn.dataset.barcode || '';

        // Clamp quantity to minimum
        const minQty = parseInt(qtyInput.getAttribute('min') || '1');
        let qty = parseInt(qtyInput.value || String(minQty));
        if (isNaN(qty) || qty < minQty) {
            qty = minQty;
            qtyInput.value = minQty;
        }
        
        // Calculate variant additions
        let unitPrice = await fetchUnitPriceForQuantity(barcode, qty, basePrice);
        const selected = selectedVariantsByProduct[pid] || {};
        Object.values(selected).forEach(v => {
            unitPrice += parseFloat(v.price || 0);
        });
        
        const total = unitPrice * qty;
        
        // Update display
        const unitEl = document.getElementById(`unit-${pid}`);
        const totalEl = document.getElementById(`total-${pid}`);
        
        if (unitEl) unitEl.textContent = `${unitPrice.toLocaleString()} TZS`;
        if (totalEl) totalEl.textContent = `${total.toLocaleString()} TZS`;
    }
    
    // Add to cart confirmation (wholesale "Place Order")
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const pid = this.dataset.productId;
            const productName = this.dataset.name;
            const productBarcode = this.dataset.barcode;
            const basePrice = parseFloat(this.dataset.basePrice || 0);
            const minQty = parseInt(this.dataset.minqty || '1');
            const qtyInput = document.getElementById(`qty-${pid}`);
            let qty = parseInt(qtyInput?.value || String(minQty));
            if (isNaN(qty) || qty < minQty) {
                qty = minQty;
                if (qtyInput) qtyInput.value = minQty;
            }
            
            // Calculate final unit price with variants
            // Get wholesale unit price with volume discounts
            let unitPrice = await fetchUnitPriceForQuantity(productBarcode, qty, basePrice);
            const selected = selectedVariantsByProduct[pid] || {};
            const variants = [];
            
            Object.entries(selected).forEach(([category, variant]) => {
                unitPrice += parseFloat(variant.price || 0);
                variants.push({
                    category: category,
                    name: variant.name,
                    price: variant.price
                });
            });
            
            // Get existing cart
            let cart = [];
            try {
                cart = JSON.parse(localStorage.getItem('chibo_wholesale_cart') || '[]');
            } catch(e) {
                cart = [];
            }
            
            // Get product image
            const productImage = document.querySelector(`#addToCartModal-${pid} img`)?.src || '';
            
            // Add item to cart
            cart.push({
                id: pid,
                barcode: productBarcode,
                name: productName,
                image: productImage,
                qty: qty,
                unitPrice: unitPrice,
                total: unitPrice * qty,
                variants: selected,
                channel: 'wholesale',
                // Persist minimum quantity so cart modal enforces it
                minQuantity: minQty
            });
            
            // Save to localStorage
            localStorage.setItem('chibo_wholesale_cart', JSON.stringify(cart));
            
            // Update cart badge
            updateCartBadge(cart.length);
            
            // Show success message
            showSuccessMessage(productName, qty);
            
            // Reset modal state
            delete selectedVariantsByProduct[pid];
            document.querySelectorAll(`.variant-btn[data-product-id="${pid}"]`).forEach(btn => {
                btn.classList.remove('active');
            });
            if (document.getElementById(`qty-${pid}`)) {
                document.getElementById(`qty-${pid}`).value = 1;
            }
        });
    });
    
    // Update cart badge
    function updateCartBadge(count) {
        const badges = document.querySelectorAll('#cart-count, .cart-count');
        badges.forEach(badge => {
            badge.textContent = count;
            if (count > 0) {
                badge.style.display = 'inline-block';
            }
        });
    }
    
    // Show success message
    function showSuccessMessage(productName, qty) {
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
    try {
        const cart = JSON.parse(localStorage.getItem('chibo_wholesale_cart') || '[]');
        updateCartBadge(cart.length);
    } catch(e) {
        updateCartBadge(0);
    }
    
    // Initialize price displays when modals open
    document.querySelectorAll('[id^="addToCartModal-"]').forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            const pid = this.id.replace('addToCartModal-', '');
            updatePriceDisplay(pid);
        });
    });
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
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
    .variant-option.active,
    .variant-btn.active {
        background-color: #dc3545 !important;
        border-color: #dc3545 !important;
        color: white !important;
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }
    
    .variant-btn {
        transition: all 0.3s ease;
    }
    
    .variant-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    @media (max-width: 576px) {
        .modal-dialog.modal-lg {
            max-width: 100%;
            margin: 0.75rem;
        }
    }
`;
document.head.appendChild(style);
</script>
@endpush
