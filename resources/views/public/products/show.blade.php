@extends('public.layouts.app')

@section('title', $product->name . ' - CHIBO BRAND')
@section('description', $product->description)

@section('content')
<div class="container px-3 px-md-4 py-3 py-md-4">
    @php
        $displayName = preg_replace('/\s+\d+$/', '', (string) ($product->name ?? ''));
    @endphp
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3 mb-md-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}" class="text-decoration-none">
                    <i class="fas fa-home me-1"></i>Home
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('products.index') }}" class="text-decoration-none">
                    <i class="fas fa-shopping-bag me-1"></i>Products
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <i class="fas fa-box me-1"></i>{{ $displayName }}
            </li>
        </ol>
    </nav>

    <div class="row g-3 g-md-4">
        <!-- Product Images -->
        <div class="col-lg-6">
            <div class="product-images">
                @php
                    $defaultUrl = '/images/default.webp';
                    // Get all product images
                    $productImages = $product->images ?? collect();
                    // Map to include path and color info
                    $imagesData = $productImages->filter(function($img) {
                        return !empty($img->image_path);
                    })->map(function($img) {
                        return [
                            'path' => ltrim($img->image_path, '/'),
                            'color' => $img->color ? strtolower(trim($img->color)) : null
                        ];
                    })->values()->toArray();
                    
                    $validImagesCount = count($imagesData);
                    
                    // Get main image URL (using relative path to avoid APP_URL issues in production)
                    $mainImageUrl = $validImagesCount > 0 
                        ? '/storage/' . $imagesData[0]['path']
                        : '/images/default.webp';
                    
                    // Debug info for developer in HTML source
                    echo "<!-- Debug: validImagesCount=$validImagesCount, mainImageUrl=$mainImageUrl -->";
                @endphp
                
                <!-- Main Image Container with Navigation -->
                <div class="main-image-container mb-3 mb-md-4 position-relative">
                    <div class="image-wrapper rounded-3 shadow-sm overflow-hidden" style="background: #f8f9fa;">
                        <img id="mainImage" 
                             src="{{ $mainImageUrl }}?v={{ time() }}" 
                             onerror="this.onerror=null;this.src='{{ $defaultUrl }}'" 
                             alt="{{ $displayName }}"
                             class="img-fluid w-100 main-image"
                             loading="eager">
                    </div>
                    
                    <!-- Navigation Arrows (only show if multiple images) -->
                    @if($validImagesCount > 1)
                        <button class="btn btn-light btn-sm position-absolute top-50 start-0 translate-middle-y ms-2 rounded-circle gallery-nav" 
                                id="prevBtn" onclick="previousImage()" 
                                style="z-index: 10; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="btn btn-light btn-sm position-absolute top-50 end-0 translate-middle-y me-2 rounded-circle gallery-nav" 
                                id="nextBtn" onclick="nextImage()" 
                                style="z-index: 10; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        
                        <!-- Image Counter -->
                        <div class="position-absolute bottom-0 end-0 m-3">
                            <span class="badge bg-dark bg-opacity-75 text-white" id="imageCounter">
                                1 / {{ $validImagesCount }}
                            </span>
                        </div>
                    @endif
                </div>
                
                <!-- Thumbnail Gallery -->
                @if($validImagesCount > 1)
                    <div class="thumbnail-gallery mt-2">
                        <div class="d-flex gap-2 overflow-auto pb-2" style="max-width: 100%; -webkit-overflow-scrolling: touch; scroll-behavior: smooth;">
                            @foreach($imagesData as $index => $imgInfo)
                                <div class="thumbnail-item flex-shrink-0" style="width: 70px;">
                                    <img onclick="changeImage('/storage/{{ $imgInfo['path'] }}', {{ $index }})" 
                                         src="/storage/{{ $imgInfo['path'] }}?v={{ time() }}" 
                                         alt="{{ $displayName }} - Image {{ $index + 1 }}"
                                         class="img-fluid rounded-2 cursor-pointer border border-2 {{ $index === 0 ? 'border-primary' : 'border-light' }} thumbnail-img"
                                         style="height: 70px; width: 70px; object-fit: cover; transition: all 0.3s ease;"
                                         data-index="{{ $index }}"
                                         onerror="this.onerror=null;this.src='/images/default.webp'"
                                         loading="lazy">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Product Information -->
        <div class="col-lg-6">
            <div class="product-info">
                <!-- Product Title -->
                <h1 class="h3 h2-md text-primary mb-3 mb-md-4 fw-bold">{{ $displayName }}</h1>
                
                <!-- Product Description -->
                @if($product->description)
                    <p class="text-muted mb-3 mb-md-4 lh-base">{{ $product->description }}</p>
                @endif

                <!-- Product Details -->
                <div class="product-details mb-3 mb-md-4">
                    <div class="product-details-row">
                        @php
                            // Helper function to check if value should be displayed
                            $shouldDisplay = function($value) {
                                if (is_null($value)) return false;
                                if (is_string($value) && (trim($value) === '' || strtoupper(trim($value)) === 'N/A')) return false;
                                if (is_object($value) && method_exists($value, '__toString')) {
                                    $str = trim((string)$value);
                                    return $str !== '' && strtoupper($str) !== 'N/A';
                                }
                                return true;
                            };
                            
                            // Get category name (handle both object and string)
                            $categoryName = null;
                            if (is_object($product->category) && isset($product->category->name)) {
                                $categoryName = $product->category->name;
                            } elseif (is_string($product->category)) {
                                $categoryName = $product->category;
                            }
                        @endphp
                        
                        @if($shouldDisplay($product->barcode))
                        <div class="detail-item p-2 p-md-3 bg-light rounded-2">
                            <small class="text-muted d-block mb-1">SKU</small>
                            <strong class="text-dark">{{ $product->barcode }}</strong>
                        </div>
                        @endif
                        
                        @if($shouldDisplay($categoryName))
                        <div class="detail-item p-2 p-md-3 bg-light rounded-2">
                            <small class="text-muted d-block mb-1">Category</small>
                            <strong class="text-dark">{{ $categoryName }}</strong>
                        </div>
                        @endif
                        
                        @if($shouldDisplay($product->brand))
                        <div class="detail-item p-2 p-md-3 bg-light rounded-2">
                            <small class="text-muted d-block mb-1">Brand</small>
                            <strong class="text-dark">{{ $product->brand }}</strong>
                        </div>
                        @endif
                        
                        @if($shouldDisplay($product->material))
                        <div class="detail-item p-2 p-md-3 bg-light rounded-2">
                            <small class="text-muted d-block mb-1">Material</small>
                            <strong class="text-dark">{{ $product->material }}</strong>
                        </div>
                        @endif
                        
                        @if($shouldDisplay($product->printing_type))
                        <div class="detail-item p-2 p-md-3 bg-light rounded-2">
                            <small class="text-muted d-block mb-1">Printing</small>
                            <strong class="text-dark">{{ $product->printing_type }}</strong>
                        </div>
                        @endif
                        
                        <div class="detail-item p-2 p-md-3 bg-light rounded-2">
                            <small class="text-muted d-block mb-1">Availability</small>
                            @if($product->availability === 'in_stock')
                                <span class="badge bg-success">In Stock</span>
                            @elseif($product->availability === 'out_of_stock')
                                <span class="badge bg-danger">Out of Stock</span>
                            @elseif($product->availability === 'custom')
                                <span class="badge bg-warning">Custom Order</span>
                            @else
                                <span class="badge bg-secondary">N/A</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Features -->
                @if($product->features)
                <div class="product-features mb-3 mb-md-4">
                    <h6 class="text-dark mb-2 mb-md-3 fw-semibold">
                        <i class="fas fa-star me-2 text-warning"></i>Key Features
                    </h6>
                    <ul class="list-unstyled">
                        @foreach(explode("\n", $product->features) as $feature)
                            @if(trim($feature))
                                <li class="mb-1 mb-md-2">
                                    <i class="fas fa-check text-success me-2"></i>{{ trim($feature) }}
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Channel-specific pricing (no toggle needed) -->
                <div class="channel-info mb-3 mb-md-4">
                    <div class="alert alert-info border-0 rounded-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>{{ $channel === 'wholesale' ? 'Wholesale' : 'Retail' }} Pricing</strong>
                        @if($channel === 'wholesale')
                            - Special bulk pricing for business customers
                        @else
                            - Standard pricing for individual customers
                        @endif
                    </div>
                </div>

                <!-- Pricing Information -->
                <div class="pricing-section mb-3 mb-md-4">
                    <h5 class="text-dark mb-2 mb-md-3 fw-semibold">
                        <i class="fas fa-tags me-2 text-primary"></i>{{ $channel === 'wholesale' ? 'Wholesale' : 'Retail' }} Pricing
                    </h5>
                    
                    <!-- Base Price Display -->
                    <div class="base-price-display mb-3 mb-md-4">
                        <div class="card border-0 bg-gradient rounded-3 shadow-sm">
                            <div class="card-body p-3 p-md-4">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h6 class="mb-1 text-muted small">Base Price</h6>
                                        <h4 class="mb-0 text-primary fw-bold">
                                            TZS {{ number_format($product->getBasePriceForChannel($channel), 0) }}
                                        </h4>
                                        <small class="text-muted">per {{ $product->stock_unit ?? 'pcs' }}</small>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        @if($channel === 'wholesale' && $product->b2b_base_price)
                                            <span class="badge bg-success">Wholesale Price</span>
                                        @elseif($channel === 'retail' && $product->retail_base_price)
                                            <span class="badge bg-info">Retail Price</span>
                                        @else
                                            <span class="badge bg-secondary">Standard Price</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Price Tiers Table -->
                    @if($priceTiers->count() > 0)
                        <div class="price-tiers">
                            <h6 class="text-muted mb-2 mb-md-3 fw-semibold">
                                <i class="fas fa-layer-group me-2"></i>Volume Discounts
                            </h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover rounded-3 overflow-hidden">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center py-2 py-md-3">Quantity Range</th>
                                        <th class="text-center py-2 py-md-3">Price per Unit</th>
                                        <th class="text-center py-2 py-md-3">Savings</th>
                                    </tr>
                                </thead>
                                <tbody>
                                        @php
                                            $basePrice = $product->getBasePriceForChannel($channel);
                                        @endphp
                                    @foreach($priceTiers as $tier)
                                            @php
                                                $savings = $basePrice - $tier->price_per_unit;
                                                $savingsPercent = $basePrice > 0 ? round(($savings / $basePrice) * 100, 1) : 0;
                                            @endphp
                                        <tr class="price-tier-row" 
                                            data-min="{{ $tier->min_quantity }}" 
                                            data-max="{{ $tier->max_quantity ?? '999999' }}"
                                            data-price="{{ $tier->price_per_unit }}">
                                            <td class="text-center py-2 py-md-3">
                                                <strong>{{ $tier->min_quantity }}</strong>
                                                @if($tier->max_quantity)
                                                    - {{ $tier->max_quantity }}
                                                @else
                                                    +
                                                @endif
                                                <small class="text-muted d-block">pieces</small>
                                            </td>
                                            <td class="text-center py-2 py-md-3">
                                                <strong class="text-primary">{{ number_format($tier->price_per_unit, 0) }}</strong>
                                                <small class="text-muted d-block">TZS</small>
                                            </td>
                                                <td class="text-center py-2 py-md-3">
                                                    @if($savings > 0)
                                                        <span class="text-success">
                                                            <strong>-{{ number_format($savings, 0) }} TZS</strong>
                                                            <small class="d-block">({{ $savingsPercent }}% off)</small>
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info border-0 rounded-3">
                            <i class="fas fa-info-circle me-2"></i>
                            No volume discounts available. Base price applies to all quantities.
                        </div>
                    @endif
                </div>

                <!-- Product Variants -->
                @if($product->variantCategories->count() > 0)
                    <div class="variants-section mb-3 mb-md-4">
                        <h5 class="text-dark mb-2 mb-md-3 fw-semibold">
                            <i class="fas fa-swatchbook me-2 text-primary"></i>Available Variants
                        </h5>
                        
                        @foreach($product->variantCategories as $category)
                            <div class="variant-category mb-3 mb-md-4">
                                <div class="card border-0 bg-light rounded-3">
                                    <div class="card-body p-3 p-md-4">
                                        <h6 class="card-title mb-2 mb-md-3 fw-semibold">
                                            <i class="fas fa-tag me-2"></i>{{ $category->category }}
                                            @php
                                                $categoryPrice = $category->price_adjustment ?? 0;
                                            @endphp
                                            @if($categoryPrice > 0)
                                                <span class="badge bg-success ms-2">+{{ number_format($categoryPrice, 0) }} TZS</span>
                                            @elseif($categoryPrice < 0)
                                                <span class="badge bg-danger ms-2">{{ number_format($categoryPrice, 0) }} TZS</span>
                                            @endif
                                        </h6>
                                        
                                        @if($category->items->count() > 0)
                                            <div class="row g-2 g-md-3">
                                                @foreach($category->items as $item)
                                                    <div class="col-3 col-md-auto">
                                                        <div class="variant-option">
                                                            @if($category->category === 'Color' && $item->color_code)
                                                                <button type="button" 
                                                                        class="btn btn-outline-secondary variant-btn position-relative w-100"
                                                                        data-category="{{ $category->category }}"
                                                                        data-value="{{ $item->name }}"
                                                                        data-price="{{ $item->getPriceForCustomerType($channel) }}"
                                                                        title="{{ $item->name }} - {{ $item->description ?? '' }}">
                                                                    <div class="color-preview" 
                                                                         style="width: 24px; height: 24px; background-color: {{ $item->color_code }}; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 1px #ddd; margin: 0 auto;"></div>
                                                                    <small class="d-block mt-2">{{ $item->name }}</small>
                                                                    @if($item->getPriceForCustomerType($channel) > 0)
                                                                        <small class="text-success">+{{ number_format($item->getPriceForCustomerType($channel), 0) }} TZS</small>
                                                                    @endif
                                                                </button>
                                                            @else
                                                                <button type="button" 
                                                                        class="btn btn-outline-secondary variant-btn w-100"
                                                                        data-category="{{ $category->category }}"
                                                                        data-value="{{ $item->name }}"
                                                                        data-price="{{ $item->getPriceForCustomerType($channel) }}"
                                                                        title="{{ $item->description ?? '' }}">
                                                                    {{ $item->name }}
                                                                    @if($item->getPriceForCustomerType($channel) > 0)
                                                                        <small class="d-block text-success">+{{ number_format($item->getPriceForCustomerType($channel), 0) }} TZS</small>
                                                                    @endif
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted mb-0">No options available for this variant.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        <!-- Selected Variants Display -->
                        <div id="selectedVariants" class="selected-variants mb-3" style="display: none;">
                            <h6 class="text-muted mb-2">Selected Options:</h6>
                            <div id="selectedVariantsList" class="d-flex flex-wrap gap-2"></div>
                        </div>
                    </div>
                @elseif($product->variants && $product->variants->count() > 0)
                    <!-- Fallback to old variant structure -->
                    <div class="variant-selection mb-4">
                        <h5 class="text-dark mb-3">
                            <i class="fas fa-swatchbook me-2"></i>Product Variants
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="variantColor" class="form-label">Color</label>
                                <select id="variantColor" class="form-select">
                                    <option value="">Select Color</option>
                                    @foreach(collect($product->variants ?? [])->unique('color') as $variant)
                                        <option value="{{ $variant['color'] ?? ($variant->color ?? '') }}">{{ $variant['color'] ?? ($variant->color ?? '') }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="variantSize" class="form-label">Size</label>
                                <select id="variantSize" class="form-select">
                                    <option value="">Select Size</option>
                                    @foreach(collect($product->variants ?? [])->unique('size') as $variant)
                                        <option value="{{ $variant['size'] ?? ($variant->size ?? '') }}">{{ $variant['size'] ?? ($variant->size ?? '') }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Quantity and Price -->
                <div class="quantity-price mb-3 mb-md-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="quantity" class="form-label fw-semibold">Quantity</label>
                            @php
                                $minOrderQty = $product->min_quantity ?? 1;
                            @endphp
                            <input type="number" 
                                   id="quantity" 
                                   class="form-control form-control-lg" 
                                   value="{{ $minOrderQty }}" 
                                   min="{{ $minOrderQty }}" 
                                   max="9999"
                                   onchange="calculatePrice()">
                        </div>
                        <div class="col-md-8">
                            <div class="price-display">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Unit Price:</span>
                                    <span id="unitPrice" class="h5 text-primary mb-0 fw-bold">0 TZS</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted fw-semibold">Total Price:</span>
                                    <span id="totalPrice" class="h4 text-primary mb-0 fw-bold">0 TZS</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add to Cart Button -->
                <div class="add-to-cart">
                    <button id="addToCartBtn" 
                            class="btn btn-primary btn-lg w-100 mb-3 rounded-3"
                            onclick="addToCart()"
                            disabled>
                        <i class="fas fa-shopping-cart me-2"></i>Place Your Order
                    </button>
                    
                    <div class="text-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Need help? <a href="https://wa.me/255655392319" target="_blank" class="text-decoration-none">Contact us on WhatsApp</a>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
    <div id="cartToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="fas fa-check-circle text-success me-2"></i>
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage">
            Product added to cart successfully!
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Breadcrumb styles */
    .breadcrumb {
        background: transparent;
        padding: 0.5rem 0;
        margin-bottom: 0;
        font-size: 0.875rem;
    }

    .breadcrumb-item {
        display: flex;
        align-items: center;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        color: #6c757d;
        font-size: 1.2rem;
        font-weight: 600;
        margin: 0 0.5rem;
    }

    .breadcrumb-item a {
        color: #6c757d;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .breadcrumb-item a:hover {
        color: #0d6efd;
    }

    .breadcrumb-item.active {
        color: #495057;
        font-weight: 500;
    }

    .breadcrumb-item i {
        font-size: 0.75rem;
    }
    
    .cursor-pointer {
        cursor: pointer;
    }
    
    .detail-item {
        padding: 0.5rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .detail-item:last-child {
        border-bottom: none;
    }
    
    .price-tier-row:hover {
        background-color: #f8f9fa;
    }
    
    .price-tier-row.active {
        background-color: #e3f2fd;
        border-left: 4px solid #2196f3;
    }
    
    .btn-check:checked + .btn-outline-primary {
        background-color: var(--chibo-red);
        border-color: var(--chibo-red);
        color: white;
    }
    
    .thumbnail-container img:hover {
        border-color: var(--chibo-red) !important;
    }
    
    .price-display {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    .product-images img {
        transition: all 0.3s ease;
    }
    
    .product-images img:hover {
        transform: scale(1.02);
    }
    
    /* Enhanced Image Gallery Styles */
    .main-image-container {
        position: relative;
        background: #f8f9fa;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .image-wrapper {
        position: relative;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    /* Maintain a pleasant aspect ratio for main image */
    .main-image {
        aspect-ratio: 4 / 3;
        width: 100%;
        height: auto;
        object-fit: cover;
        transition: opacity 0.3s ease;
        background-color: #f8f9fa;
    }
    
    .thumbnail-gallery {
        margin-top: 1rem;
    }
    
    .thumbnail-gallery::-webkit-scrollbar {
        height: 4px;
    }
    
    .thumbnail-gallery::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 2px;
    }
    
    .thumbnail-gallery::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 2px;
    }
    
    .thumbnail-gallery::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
    .thumbnail-img {
        cursor: pointer;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .thumbnail-img:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .thumbnail-img.border-primary {
        box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
    }
    
    #prevBtn, #nextBtn {
        opacity: 0.9;
        transition: all 0.25s ease;
    }
    
    #prevBtn:hover, #nextBtn:hover {
        opacity: 1;
        transform: scale(1.05);
    }
    
    #imageCounter {
        font-size: 0.875rem;
        font-weight: 500;
        backdrop-filter: blur(4px);
    }

    .gallery-nav {
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Mobile refinements */
    @media (max-width: 992px) {
        .main-image { aspect-ratio: 1 / 1; }
    }

    @media (max-width: 576px) {
        .thumbnail-item { width: 60px !important; }
        .thumbnail-img { width: 60px !important; height: 60px !important; }
        #prevBtn, #nextBtn { width: 32px; height: 32px; }
        #imageCounter { font-size: 0.75rem; }
        
        /* Mobile typography - significantly reduced font sizes */
        .h1, .h3.h2-md, h1 { font-size: 0.95rem !important; }
        .h2, h2 { font-size: 0.9rem !important; }
        .h4, h4 { font-size: 0.85rem !important; }
        .h5, h5 { font-size: 0.8rem !important; }
        .h6, h6 { font-size: 0.75rem !important; }
        p, .text-muted { font-size: 0.75rem !important; }
        small { font-size: 0.65rem !important; }
        .lead { font-size: 0.75rem !important; }
        strong, b { font-size: 0.8rem !important; }
        
        /* Mobile labels */
        label, .form-label { font-size: 0.75rem !important; }
        .fw-semibold { font-size: 0.8rem !important; }
        
        /* Mobile spacing */
        .mb-3 { margin-bottom: 0.5rem !important; }
        .mb-4 { margin-bottom: 0.75rem !important; }
        .p-2 { padding: 0.5rem !important; }
        .p-3 { padding: 0.75rem !important; }
        .p-md-3 { padding: 0.75rem !important; }
        
        /* Mobile form controls */
        .form-control-lg, .form-control { font-size: 0.8rem !important; padding: 0.5rem !important; }
        
        /* Mobile cards */
        .card-body { padding: 0.75rem !important; }
        
        /* Mobile buttons */
        .btn, .btn-lg { padding: 0.5rem 0.75rem; font-size: 0.8rem !important; }
        
        /* Mobile breadcrumb - smaller */
        .breadcrumb, .breadcrumb-item, .breadcrumb-item a { font-size: 0.65rem !important; }
        .breadcrumb-item i { font-size: 0.6rem !important; }
        
        /* Mobile table */
        table, .table th, .table td { font-size: 0.75rem !important; }
        
        /* Mobile badges */
        .badge { font-size: 0.65rem !important; padding: 0.25rem 0.5rem !important; }
        
        /* Mobile alert */
        .alert { font-size: 0.75rem !important; padding: 0.75rem !important; }
        
        /* Mobile product details - 2 columns with flex layout */
        .product-details-row {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 0.35rem;
        }
        .product-details-row .detail-item {
            flex: 0 0 calc(50% - 0.175rem) !important;
            max-width: calc(50% - 0.175rem) !important;
            padding: 0.5rem !important;
            min-height: auto !important;
        }
        .product-details-row .detail-item small {
            font-size: 0.6rem !important;
            margin-bottom: 0.25rem !important;
        }
        .product-details-row .detail-item strong {
            font-size: 0.75rem !important;
            line-height: 1.2 !important;
        }
    }
    
    /* Desktop enhancements */
    @media (min-width: 992px) {
        .main-image-container { border-radius: 20px; }
        .thumbnail-img { border-radius: 12px; }
        .card { border-radius: 16px; }
        .btn { border-radius: 12px; }
        .form-control { border-radius: 12px; }
    }
    
    /* Variant Styles */
    .variant-btn {
        min-width: 80px;
        transition: all 0.3s ease;
        border-radius: 12px;
        padding: 0.75rem;
        text-align: center;
        font-size: 0.875rem;
        border: 2px solid #e9ecef;
    }
    
    .variant-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-color: #dc3545;
    }
    
    .variant-btn.active {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }
    
    .color-preview {
        margin: 0 auto;
        transition: all 0.3s ease;
    }
    
    .variant-btn:hover .color-preview {
        transform: scale(1.1);
    }
    
    /* Mobile variant optimizations */
    @media (max-width: 576px) {
        .variant-btn {
            min-width: auto;
            width: 100%;
            padding: 0.35rem 0.15rem !important;
            font-size: 0.65rem !important;
            line-height: 1.2 !important;
        }
        
        .color-preview {
            width: 16px !important;
            height: 16px !important;
        }
        
        /* Make sure variant buttons text wraps properly */
        .variant-btn small {
            font-size: 0.55rem !important;
            display: block;
            margin-top: 0.15rem;
            line-height: 1.1 !important;
        }
        
        /* Reduce column gaps for better fit */
        .row.g-2 {
            gap: 0.25rem !important;
        }
        
        /* Make variants appear 4 in a row on mobile */
        .variants-section .row {
            margin-left: -0.125rem !important;
            margin-right: -0.125rem !important;
        }
        .variants-section [class*="col-"] {
            flex: 0 0 25% !important;
            max-width: 25% !important;
            padding-left: 0.125rem !important;
            padding-right: 0.125rem !important;
        }
    }
    
    .selected-variants {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        border: 1px solid #e9ecef;
    }
    
    .selected-variant-item {
        background-color: var(--bs-primary);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .selected-variant-item .remove-btn {
        background: none;
        border: none;
        color: white;
        font-size: 0.75rem;
        cursor: pointer;
        padding: 0;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .selected-variant-item .remove-btn:hover {
        background-color: rgba(255,255,255,0.2);
    }
    
</style>
@endpush

@push('scripts')
<script>
    // Product data from backend
    const productData = {
        barcode: '{{ $product->barcode }}',
        name: '{{ $product->name }}',
        priceTiers: @json($priceTiers),
        customerType: '{{ $customerType }}',
        channel: '{{ $channel }}',
        basePrice: {{ method_exists($product, 'getBasePriceForChannel') ? $product->getBasePriceForChannel($channel) : 0 }},
        images: @json($imagesData),
        minQuantity: {{ $product->min_quantity ?? 1 }}
    };

    // Image gallery state
    let currentImageIndex = 0;
    const totalImages = productData.images.length;

    // Enhanced image gallery functions
    function changeImage(imageUrl, index = null) {
        const mainImage = document.getElementById('mainImage');
        
        // Add fade effect
        mainImage.style.opacity = '0.5';
        
        setTimeout(() => {
            mainImage.src = imageUrl;
            mainImage.style.opacity = '1';
            
            // Update current index
            if (index !== null) {
                currentImageIndex = index;
            }
        
        // Update thumbnail borders
            updateThumbnailBorders();
            
            // Update image counter
            updateImageCounter();
        }, 150);
    }

    // Touch swipe support for mobile
    (function enableSwipe() {
        let touchStartX = 0;
        let touchEndX = 0;
        const threshold = 40; // min px to count as swipe
        const mainImage = document.getElementById('mainImage');

        if (!mainImage) return;

        mainImage.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        mainImage.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            const delta = touchEndX - touchStartX;
            if (Math.abs(delta) > threshold) {
                if (delta > 0) {
                    previousImage();
                } else {
                    nextImage();
                }
            }
        }, { passive: true });
    })();

    function previousImage() {
        if (totalImages <= 1) return;
        
        currentImageIndex = (currentImageIndex - 1 + totalImages) % totalImages;
        const imageUrl = '/storage/' + productData.images[currentImageIndex].path + '?v=' + Date.now();
        changeImage(imageUrl);
    }

    function nextImage() {
        if (totalImages <= 1) return;
        
        currentImageIndex = (currentImageIndex + 1) % totalImages;
        const imageUrl = '/storage/' + productData.images[currentImageIndex].path + '?v=' + Date.now();
        changeImage(imageUrl);
    }

    function updateThumbnailBorders() {
        document.querySelectorAll('.thumbnail-img').forEach((img, index) => {
            img.classList.remove('border-primary');
            img.classList.add('border-light');
            
            if (index === currentImageIndex) {
                img.classList.remove('border-light');
                img.classList.add('border-primary');
            }
        });
    }

    function updateImageCounter() {
        const counter = document.getElementById('imageCounter');
        if (counter) {
            counter.textContent = `${currentImageIndex + 1} / ${totalImages}`;
        }
    }

    // Keyboard navigation
    function handleKeyPress(event) {
        if (totalImages <= 1) return;
        
        switch(event.key) {
            case 'ArrowLeft':
                event.preventDefault();
                previousImage();
                break;
            case 'ArrowRight':
                event.preventDefault();
                nextImage();
                break;
        }
    }

    // Calculate price based on quantity and fixed channel
    function calculatePrice() {
        const quantityInput = document.getElementById('quantity');
        let quantity = parseInt(quantityInput.value || '1');
        const minQty = parseInt(quantityInput.getAttribute('min') || String(productData.minQuantity || 1));
        if (isNaN(quantity) || quantity < minQty) {
            quantity = minQty;
            quantityInput.value = minQty;
        }
        const customerType = productData.channel; // Use fixed channel instead of toggle
        
        // Find matching price tier
        let unitPrice = 0;
        let matchedTier = null;
        
        for (let tier of productData.priceTiers) {
            if (tier.customer_type === customerType) {
                const minQty = parseInt(tier.min_quantity);
                const maxQty = tier.max_quantity ? parseInt(tier.max_quantity) : 999999;
                
                if (quantity >= minQty && quantity <= maxQty) {
                    unitPrice = parseFloat(tier.price_per_unit);
                    matchedTier = tier;
                    break;
                }
            }
        }
        
        // Use base price as fallback if no tier found
        if (unitPrice === 0) {
            unitPrice = productData.basePrice;
        }
        
        // Add variant pricing
        let variantPriceAdjustment = 0;
        Object.values(selectedVariants).forEach(variant => {
            variantPriceAdjustment += variant.price;
        });
        
        const finalUnitPrice = unitPrice + variantPriceAdjustment;
        const totalPrice = finalUnitPrice * quantity;
        
        // Update price display
        document.getElementById('unitPrice').textContent = finalUnitPrice.toLocaleString() + ' TZS';
        document.getElementById('totalPrice').textContent = totalPrice.toLocaleString() + ' TZS';
        
        // Highlight matching tier in table
        document.querySelectorAll('.price-tier-row').forEach(row => {
            row.classList.remove('active');
        });
        
        if (matchedTier) {
            const matchingRow = document.querySelector(`[data-min="${matchedTier.min_quantity}"][data-max="${matchedTier.max_quantity || '999999'}"]`);
            if (matchingRow) {
                matchingRow.classList.add('active');
            }
        }
        
        // Enable/disable add to cart button
        const addToCartBtn = document.getElementById('addToCartBtn');
        if (unitPrice > 0) {
            addToCartBtn.disabled = false;
            addToCartBtn.innerHTML = '<i class="fas fa-shopping-cart me-2"></i>Place Your Order';
        } else {
            addToCartBtn.disabled = true;
            addToCartBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>No Price Available';
        }
    }

    // Add to cart function
    function addToCart() {
        const quantityInput = document.getElementById('quantity');
        let quantity = parseInt(quantityInput.value || '1');
        const minQty = parseInt(quantityInput.getAttribute('min') || String(productData.minQuantity || 1));
        if (isNaN(quantity) || quantity < minQty) {
            quantity = minQty;
            quantityInput.value = minQty;
        }
        const customerType = productData.channel; // Use fixed channel instead of toggle
        
        // Calculate base price from tiers
        let baseUnitPrice = 0;
        for (let tier of productData.priceTiers) {
            if (tier.customer_type === customerType) {
                const minQty = parseInt(tier.min_quantity);
                const maxQty = tier.max_quantity ? parseInt(tier.max_quantity) : 999999;
                
                if (quantity >= minQty && quantity <= maxQty) {
                    baseUnitPrice = parseFloat(tier.price_per_unit);
                    break;
                }
            }
        }
        
        // Use base price as fallback if no tier found
        if (baseUnitPrice === 0) {
            baseUnitPrice = productData.basePrice;
        }
        
        if (baseUnitPrice === 0) {
            showAlert('No price available for selected quantity and customer type.', 'warning');
            return;
        }
        
        // Add variant pricing to unit price
        let variantPriceAdjustment = 0;
        Object.values(selectedVariants).forEach(variant => {
            variantPriceAdjustment += variant.price;
        });
        
        const finalUnitPrice = baseUnitPrice + variantPriceAdjustment;
        
        // Create cart item with proper variant format
        const cartItem = {
            barcode: productData.barcode,
            name: productData.name,
            channel: customerType,
            qty: quantity,
            unitPrice: finalUnitPrice,
            total: finalUnitPrice * quantity,
            variants: selectedVariants, // Include all selected variants with prices
            image: document.getElementById('mainImage').src,
            // Store pricing metadata so cart modal can recalculate using same volume discounts
            pricing: {
                channel: customerType,
                basePrice: productData.basePrice,
                // Only keep tiers for this customer/channel for precise volume discounts
                priceTiers: (productData.priceTiers || []).filter(tier => tier.customer_type === customerType)
            },
            // Store total variant extra so modal can keep unit price in sync if qty changes
            variantExtra: variantPriceAdjustment,
            // Persist minimum order quantity so cart modal respects it
            minQuantity: productData.minQuantity || 1
        };
        
        // Add to localStorage cart
        const cartKey = window.location.hostname.includes('b2b') || window.location.pathname.includes('/b2b') ? 'chibo_wholesale_cart' : 'chibo_retail_cart';
        let cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
        
        // Check if item already exists in cart with same variants
        const existingItemIndex = cart.findIndex(item => {
            if (item.barcode !== cartItem.barcode || item.channel !== cartItem.channel) {
                return false;
            }
            
            // Compare variants
            const itemVariants = item.variants || {};
            const cartItemVariants = cartItem.variants || {};
            
            const itemKeys = Object.keys(itemVariants).sort();
            const cartKeys = Object.keys(cartItemVariants).sort();
            
            if (itemKeys.length !== cartKeys.length) return false;
            
            for (let key of itemKeys) {
                if (!cartItemVariants[key] || itemVariants[key].value !== cartItemVariants[key].value) {
                    return false;
                }
            }
            
            return true;
        });
        
        if (existingItemIndex > -1) {
            // Update existing item
            cart[existingItemIndex].qty += cartItem.qty;
            cart[existingItemIndex].total = cart[existingItemIndex].qty * cart[existingItemIndex].unitPrice;
        } else {
            // Add new item
            cart.push(cartItem);
        }
        
        localStorage.setItem(cartKey, JSON.stringify(cart));
        // Update all cart counters and dispatch event for listeners
        try { document.querySelectorAll('.cart-count').forEach(el => el.textContent = cart.length); } catch(e){}
        if (typeof updateCartDisplay === 'function') updateCartDisplay();
        document.dispatchEvent(new Event('chibo_cart_updated'));
        // Keep customer on the page (do NOT force cart modal)
        showToast('Added to cart', 'success');
        console.log('Cart after add:', JSON.parse(localStorage.getItem('chibo_cart')||'[]'));
    }

    // Variant selection functionality
    let selectedVariants = {};
    
    function selectVariant(category, value, price, button) {
        // Check if this button is already active (for unselection)
        if (button.classList.contains('active')) {
            // Unselect: remove active class and clear variant
            button.classList.remove('active');
            delete selectedVariants[category];
        } else {
            // Select: remove active class from other buttons in the same category
            document.querySelectorAll(`[data-category="${category}"]`).forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active class to selected button
            button.classList.add('active');
            
            // Store selected variant
            selectedVariants[category] = {
                value: value,
                price: parseFloat(price) || 0
            };

            // Switch image if it's a color category
            if (category.toLowerCase().includes('color') || category.toLowerCase().includes('rangi')) {
                const colorValue = value.toLowerCase().trim();
                const matchingImageIndex = productData.images.findIndex(img => img.color === colorValue);
                if (matchingImageIndex !== -1) {
                    const imageUrl = '/storage/' + productData.images[matchingImageIndex].path;
                    changeImage(imageUrl, matchingImageIndex);
                }
            }
        }
        
        // Update selected variants display
        updateSelectedVariantsDisplay();
        
        // Recalculate price
        calculatePrice();
    }
    
    function updateSelectedVariantsDisplay() {
        const container = document.getElementById('selectedVariants');
        const list = document.getElementById('selectedVariantsList');
        
        if (Object.keys(selectedVariants).length === 0) {
            container.style.display = 'none';
            return;
        }
        
        container.style.display = 'block';
        list.innerHTML = '';
        
        Object.entries(selectedVariants).forEach(([category, data]) => {
            const item = document.createElement('div');
            item.className = 'selected-variant-item';
            item.innerHTML = `
                <span>${category}: ${data.value}</span>
                ${data.price > 0 ? `<small>(+${data.price.toLocaleString()} TZS)</small>` : ''}
                <button class="remove-btn" onclick="removeVariant('${category}')">×</button>
            `;
            list.appendChild(item);
        });
    }
    
    function removeVariant(category) {
        // Remove active class from button
        const button = document.querySelector(`[data-category="${category}"].active`);
        if (button) {
            button.classList.remove('active');
        }
        
        // Remove from selected variants
        delete selectedVariants[category];
        
        // Update display
        updateSelectedVariantsDisplay();
        
        // Recalculate price
        calculatePrice();
    }
    
    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        // Initial price calculation
        calculatePrice();
        
        // Quantity input event listener
        document.getElementById('quantity').addEventListener('input', calculatePrice);
        
        // Keyboard navigation for image gallery
        document.addEventListener('keydown', handleKeyPress);
        
        // Initialize image gallery
        if (totalImages > 0) {
            updateThumbnailBorders();
            updateImageCounter();
        }
        
        // Add event listeners to variant buttons
        document.querySelectorAll('.variant-btn').forEach(button => {
            button.addEventListener('click', function() {
                const category = this.getAttribute('data-category');
                const value = this.getAttribute('data-value');
                const price = this.getAttribute('data-price');
                selectVariant(category, value, price, this);
            });
        });
        
        // Initialize cart display
        updateCartDisplay();
    });
    
    // Toast notification functions
    function showToast(message, type = 'success') {
        const toast = document.getElementById('cartToast');
        const toastMessage = document.getElementById('toastMessage');
        const toastHeader = toast.querySelector('.toast-header');
        
        // Update message
        toastMessage.textContent = message;
        
        // Update icon and color based on type
        const icon = toastHeader.querySelector('i');
        if (type === 'success') {
            icon.className = 'fas fa-check-circle text-success me-2';
            toastHeader.querySelector('strong').textContent = 'Success';
        } else if (type === 'error') {
            icon.className = 'fas fa-exclamation-circle text-danger me-2';
            toastHeader.querySelector('strong').textContent = 'Error';
        } else if (type === 'warning') {
            icon.className = 'fas fa-exclamation-triangle text-warning me-2';
            toastHeader.querySelector('strong').textContent = 'Warning';
        }
        
        // Show toast
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
    }
    
    
    // Update the existing addToCart function to show toast and open modal
    const originalAddToCart = addToCart;
    addToCart = function() {
        originalAddToCart();
        showToast('Product added to cart successfully!', 'success');
        // Optionally open cart modal
        setTimeout(() => {
            openCartModal();
        }, 1000);
    };
    
    // Update cart display in navbar
    function updateCartDisplay() {
        // Determine which cart to use
        const cartKey = window.location.hostname.includes('b2b') || window.location.pathname.includes('/b2b') ? 'chibo_wholesale_cart' : 'chibo_retail_cart';
        let cart = [];
        try {
            cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
        } catch(e) {
            cart = [];
        }
        
        // Update all cart count badges
        document.querySelectorAll('.cart-count').forEach(badge => {
            badge.textContent = cart.length;
        });
    }
</script>
@endpush
