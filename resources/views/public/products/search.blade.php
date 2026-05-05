@extends('public.layouts.app')

@section('title', 'Search Results - CHIBO BRAND')
@section('description', 'Search results for CHIBO BRAND products. Find the perfect printing and branding solutions for your business.')

@section('content')
<!-- Search Header -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2 class="h4 mb-3">Search Results</h2>
                @if($query)
                    <p class="text-muted">Showing results for: <strong>"{{ $query }}"</strong></p>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Search Form -->
<section class="py-3 bg-white">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <form method="GET" action="{{ route('products.search') }}" class="d-flex">
                    <input type="text" name="q" class="form-control me-2" placeholder="Search products..." value="{{ $query }}">
                    <select name="category" class="form-select me-2" style="width: auto;">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>
            <div class="col-lg-4">
                <div class="d-flex justify-content-lg-end">
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to All Products
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Search Results -->
<section class="py-5">
    <div class="container">
        @if($products->count() > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <p class="text-muted">Found {{ $products->total() }} product(s)</p>
                </div>
            </div>
            
            <div class="row g-2 row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6">
                @foreach($products as $product)
                    <div class="col">
                        <div class="card product-card h-100">
                            <div class="position-relative">
                                @if($product->images->count() > 0)
                                    <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" 
                                         class="card-img-top" 
                                         alt="{{ $product->name }}"
                                         style="height: 250px; object-fit: cover;">
                                @else
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                         style="height: 250px;">
                                        <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                                    </div>
                                @endif
                                
                                @if($product->availability === 'out_of_stock')
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-danger">Out of Stock</span>
                                    </div>
                                @elseif($product->availability === 'custom')
                                    <div class="position-absolute top-0 end-0 m-2">
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
                                        <span class="h5 text-primary mb-0">{{ $product->formatted_price }}</span>
                                        @if($product->category)
                                            <small class="text-muted">{{ $product->category->name }}</small>
                                        @endif
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('products.show', $product->slug) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </a>
                                        
                                        @if($product->availability !== 'out_of_stock')
                                            <button type="button" 
                                                    class="btn btn-primary btn-sm add-to-cart" 
                                                    data-product-id="{{ $product->id }}"
                                                    data-product-name="{{ $product->name }}"
                                                    data-product-price="{{ $product->base_price }}">
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
                    {{ $products->appends(request()->query())->links() }}
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-12 text-center py-5">
                    <i class="fas fa-search text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">No Products Found</h4>
                    <p class="text-muted">
                        @if($query)
                            We couldn't find any products matching "{{ $query }}". Try different keywords or browse our categories.
                        @else
                            No products found for the selected criteria.
                        @endif
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('products.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>Browse All Products
                        </a>
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-tags me-2"></i>Browse Categories
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cartKey = window.location.hostname.includes('b2b') || window.location.pathname.includes('/b2b') ? 'chibo_wholesale_cart' : 'chibo_retail_cart';
    let cart = JSON.parse(localStorage.getItem(cartKey)) || [];
    
    // Add to cart functionality (same as index page)
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const productPrice = parseFloat(this.dataset.productPrice);
            
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
            
            localStorage.setItem(cartKey, JSON.stringify(cart));
            showAlert('Product added to cart!', 'success');
        });
    });
    
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
});
</script>
@endpush
