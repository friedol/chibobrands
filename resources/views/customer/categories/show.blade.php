@extends('customer.layouts.app')

@section('title', $category->name . ' - CHIBO BRAND')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ request()->is('b2b*') ? route('b2b.customer.dashboard') : route('retail.customer.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ request()->is('b2b*') ? route('b2b.customer.categories.index') : route('retail.customer.categories.index') }}">Categories</a></li>
                    <li class="breadcrumb-item active">{{ $category->name }}</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">{{ $category->name }}</h1>
                    @if($category->description)
                        <p class="text-muted mb-0">{{ $category->description }}</p>
                    @endif
                </div>
                <div>
                    <span class="badge bg-primary fs-6">{{ $products->total() }} Products</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row">
        @forelse($products as $product)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card h-100 product-card">
                    <!-- Product Image -->
                    <div class="position-relative">
                        @if($product->images && $product->images->count() > 0)
                            <img src="{{ $product->images->first()->url }}" 
                                 alt="{{ $product->name }}" 
                                 class="card-img-top" 
                                 style="height: 200px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 200px;">
                                <i class="fas fa-image text-muted fa-3x"></i>
                            </div>
                        @endif
                        
                        @if($product->stock <= 0)
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-danger">Out of Stock</span>
                            </div>
                        @elseif($product->stock < 10)
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-warning text-dark">Low Stock</span>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Product Details -->
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $product->name }}</h5>
                        
                        @if($product->description)
                            <p class="card-text text-muted small">
                                {{ Str::limit($product->description, 80) }}
                            </p>
                        @endif
                        
                        <!-- Product Info -->
                        <div class="mb-2">
                            @if($product->size)
                                <small class="text-muted">
                                    <i class="fas fa-ruler me-1"></i>{{ $product->size }}
                                </small>
                            @endif
                            @if($product->material)
                                <small class="text-muted ms-2">
                                    <i class="fas fa-cube me-1"></i>{{ $product->material }}
                                </small>
                            @endif
                        </div>
                        
                        <!-- Pricing -->
                        <div class="mt-auto">
                            @php
                                $customer = Auth::guard('customer')->user();
                                $isWholesale = $customer && $customer->is_wholesale;
                            @endphp
                            
                            @if($isWholesale)
                                <div class="mb-2">
                                    <span class="text-muted small text-decoration-line-through">
                                        TZS {{ number_format($product->base_price, 0) }}
                                    </span>
                                </div>
                                <div class="h5 text-success mb-3">
                                    TZS {{ number_format($product->wholesale_price, 0) }}
                                    <small class="text-muted fs-6">(Wholesale)</small>
                                </div>
                            @else
                                <div class="h5 text-primary mb-3">
                                    TZS {{ number_format($product->base_price, 0) }}
                                </div>
                            @endif
                            
                            <!-- Action Buttons -->
                            <div class="d-grid gap-2">
                                <a href="{{ route('customer.products.show', $product) }}" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                                
                                @if($product->stock > 0)
                                    <form action="{{ route('customer.cart.add', $product) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-cart-plus me-1"></i>Add to Cart
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-secondary btn-sm w-100" disabled>
                                        <i class="fas fa-times me-1"></i>Out of Stock
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No Products Found</h4>
                        <p class="text-muted">This category doesn't have any products yet.</p>
                        <a href="{{ route('customer.categories.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Categories
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    @if($products->hasPages())
        <div class="row mt-4">
            <div class="col-12 d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        </div>
    @endif
</div>

<style>
.product-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border: 1px solid #e0e0e0;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.card-img-top {
    border-bottom: 1px solid #e0e0e0;
}
</style>
@endsection
