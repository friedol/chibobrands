@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.enhanced-products.index') }}" class="text-decoration-none">
                    <i class="fas fa-box me-1"></i>Products
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <i class="fas fa-eye me-1"></i>View Product
            </li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                    <h2 class="mb-1">
                        <i class="fas fa-box text-primary me-2"></i>
                        {{ $enhancedProduct->name }}
                    </h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-barcode me-1"></i>{{ $enhancedProduct->barcode }} 
                        <span class="mx-2">•</span>
                        <i class="fas fa-tag me-1"></i>{{ $enhancedProduct->category ?? 'Uncategorized' }}
                    </p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('admin.enhanced-products.edit', $enhancedProduct->barcode) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>Edit Product
                    </a>
                    <a href="{{ route('admin.enhanced-products.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Products
                            </a>
                        </div>
                    </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Product Images -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0 text-white">
                        <i class="fas fa-images me-2"></i>Product Images
                    </h6>
                </div>
                <div class="card-body">
                    @if($enhancedProduct->images && $enhancedProduct->images->count() > 0)
                        <div class="row g-3">
                            @foreach($enhancedProduct->images as $index => $image)
                                <div class="col-6">
                                    <div class="image-container position-relative">
                                        <img src="{{ asset('storage/' . $image->image_path) }}" 
                                             alt="{{ $enhancedProduct->name }} - Image {{ $index + 1 }}"
                                             class="img-fluid rounded shadow-sm product-image"
                                             style="width: 100%; height: 150px; object-fit: cover; cursor: pointer;"
                                             onclick="openImageModal('{{ asset('storage/' . $image->image_path) }}', '{{ $enhancedProduct->name }} - Image {{ $index + 1 }}')">
                                        <div class="image-overlay">
                                            <i class="fas fa-search-plus text-white"></i>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3 mb-0">No images uploaded</p>
                            <small class="text-muted">Images will appear here when added</small>
                        </div>
                    @endif
                </div>
                        </div>
                    </div>

        <!-- Right Column - Product Details -->
        <div class="col-lg-8">
            <!-- Basic Information Card -->
            <div class="card mb-4">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0 text-white">
                        <i class="fas fa-info-circle me-2"></i>Basic Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="info-label">Product ID</label>
                                <div class="info-value">
                                    <span class="badge bg-secondary">{{ $enhancedProduct->product_id }}</span>
                                </div>
                            </div>
                            <div class="info-item mb-3">
                                <label class="info-label">Barcode</label>
                                <div class="info-value">
                                    <span class="badge bg-dark">{{ $enhancedProduct->barcode }}</span>
                                </div>
                            </div>
                            <div class="info-item mb-3">
                                <label class="info-label">Product Name</label>
                                <div class="info-value fw-bold">{{ $enhancedProduct->name }}</div>
                            </div>
                            @if($enhancedProduct->product_nickname)
                            <div class="info-item mb-3">
                                <label class="info-label">Nickname</label>
                                <div class="info-value">{{ $enhancedProduct->product_nickname }}</div>
                            </div>
                            @endif
                            <div class="info-item mb-3">
                                <label class="info-label">Category</label>
                                <div class="info-value">
                                    <span class="badge bg-info">{{ $enhancedProduct->category ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="info-item mb-3">
                                <label class="info-label">Brand</label>
                                <div class="info-value">{{ $enhancedProduct->brand ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="info-label">Material</label>
                                <div class="info-value">{{ $enhancedProduct->material ?? 'N/A' }}</div>
                            </div>
                            <div class="info-item mb-3">
                                <label class="info-label">Printing Type</label>
                                <div class="info-value">{{ $enhancedProduct->printing_type ?? 'N/A' }}</div>
                            </div>
                            <div class="info-item mb-3">
                                <label class="info-label">Measured In</label>
                                <div class="info-value">
                                    <span class="badge bg-light text-dark">{{ $enhancedProduct->measured_in ?? 'pieces' }}</span>
                                </div>
                            </div>
                            @if($enhancedProduct->weight)
                            <div class="info-item mb-3">
                                <label class="info-label">Weight</label>
                                <div class="info-value">{{ number_format($enhancedProduct->weight, 2) }} kg</div>
                            </div>
                                        @endif
                            @if($enhancedProduct->dimensions)
                            <div class="info-item mb-3">
                                <label class="info-label">Dimensions</label>
                                <div class="info-value">{{ $enhancedProduct->dimensions }}</div>
                            </div>
                                        @endif
                            <div class="info-item mb-3">
                                <label class="info-label">Availability</label>
                                <div class="info-value">
                                    @php
                                        $availabilityClass = match($enhancedProduct->availability) {
                                            'in_stock' => 'bg-success',
                                            'out_of_stock' => 'bg-danger',
                                            'custom' => 'bg-warning',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $availabilityClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $enhancedProduct->availability ?? 'in_stock')) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($enhancedProduct->description)
                    <div class="mt-4">
                        <label class="info-label">Description</label>
                        <div class="info-value">
                            <div class="p-3 bg-light rounded">
                                {{ $enhancedProduct->description }}
                            </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pricing & Stock Information -->
            <div class="card mb-4">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0 text-white">
                        <i class="fas fa-dollar-sign me-2"></i>Pricing & Stock Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="pricing-item text-center p-3 border rounded">
                                <i class="fas fa-shopping-cart text-primary mb-2" style="font-size: 1.5rem;"></i>
                                <h6 class="text-muted mb-1">Buying Price</h6>
                                <h4 class="text-primary mb-0">{{ number_format($enhancedProduct->buying_price, 2) }} TZS</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="pricing-item text-center p-3 border rounded">
                                <i class="fas fa-tag text-success mb-2" style="font-size: 1.5rem;"></i>
                                <h6 class="text-muted mb-1">Base Price</h6>
                                <h4 class="text-success mb-0">
                                    {{ $enhancedProduct->base_price ? number_format($enhancedProduct->base_price, 2) . ' TZS' : 'Not Set' }}
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="pricing-item text-center p-3 border rounded">
                                <i class="fas fa-boxes text-info mb-2" style="font-size: 1.5rem;"></i>
                                <h6 class="text-muted mb-1">Stock Quantity</h6>
                                <h4 class="text-info mb-0">
                                    <span class="badge {{ $enhancedProduct->stock_quantity > 0 ? 'bg-success' : 'bg-danger' }} fs-6">
                                        {{ number_format($enhancedProduct->stock_quantity) }} {{ $enhancedProduct->stock_unit ?? 'pcs' }}
                                    </span>
                                </h4>
                            </div>
                        </div>
                    </div>
                            </div>
                        </div>

            <!-- Visibility & Settings -->
            <div class="card mb-4">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0 text-white">
                        <i class="fas fa-eye me-2"></i>Visibility & Settings
                    </h6>
                </div>
                <div class="card-body">
                        <div class="row">
                                <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="info-label">Customer Visibility</label>
                                <div class="info-value">
                                    @if($enhancedProduct->retail_visible)
                                        <span class="badge bg-success me-1">
                                            <i class="fas fa-users me-1"></i>Retail
                                        </span>
                                    @endif
                                    @if($enhancedProduct->wholesale_visible)
                                        <span class="badge bg-primary me-1">
                                            <i class="fas fa-building me-1"></i>Wholesale
                                        </span>
                                    @endif
                                    @if(!$enhancedProduct->retail_visible && !$enhancedProduct->wholesale_visible)
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-eye-slash me-1"></i>Hidden
                                        </span>
                                    @endif
                                </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="info-label">Product Status</label>
                                <div class="info-value">
                                    <span class="badge {{ $enhancedProduct->is_active ? 'bg-success' : 'bg-danger' }}">
                                        <i class="fas fa-{{ $enhancedProduct->is_active ? 'check' : 'times' }} me-1"></i>
                                        {{ $enhancedProduct->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                        <div class="col-md-4">
                            <div class="info-item mb-3">
                                <label class="info-label">Track Stock</label>
                                <div class="info-value">
                                    <span class="badge {{ $enhancedProduct->track_stock ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $enhancedProduct->track_stock ? 'Yes' : 'No' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-item mb-3">
                                <label class="info-label">Customization Allowed</label>
                                <div class="info-value">
                                    <span class="badge {{ $enhancedProduct->customization_allowed ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $enhancedProduct->customization_allowed ? 'Yes' : 'No' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-item mb-3">
                                <label class="info-label">Wholesale Product</label>
                                <div class="info-value">
                                    <span class="badge {{ $enhancedProduct->is_wholesale ? 'bg-primary' : 'bg-secondary' }}">
                                        {{ $enhancedProduct->is_wholesale ? 'Yes' : 'No' }}
                                    </span>
                                </div>
                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>

            <!-- Stock Management -->
            @if($enhancedProduct->track_stock)
            <div class="card mb-4">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0 text-white">
                        <i class="fas fa-warehouse me-2"></i>Stock Management
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-item mb-3">
                                <label class="info-label">Current Stock</label>
                                <div class="info-value">
                                    <h4 class="text-{{ $enhancedProduct->stock_quantity > 0 ? 'success' : 'danger' }}">
                                        {{ number_format($enhancedProduct->stock_quantity) }} {{ $enhancedProduct->stock_unit ?? 'pcs' }}
                            </h4>
                        </div>
                    </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item mb-3">
                                <label class="info-label">Low Stock Threshold</label>
                                <div class="info-value">
                                    <span class="badge bg-warning">{{ $enhancedProduct->low_stock_threshold ?? 10 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item mb-3">
                                <label class="info-label">Min Quantity</label>
                                <div class="info-value">
                                    <span class="badge bg-light text-dark">{{ $enhancedProduct->min_quantity ?? 1 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item mb-3">
                                <label class="info-label">Max Quantity</label>
                                <div class="info-value">
                                    <span class="badge bg-light text-dark">{{ $enhancedProduct->max_quantity ?? 100 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Timestamps -->
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0 text-white">
                        <i class="fas fa-clock me-2"></i>Timestamps
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="info-label">Created At</label>
                                <div class="info-value">
                                    <i class="fas fa-calendar-plus text-success me-1"></i>
                                    {{ $enhancedProduct->created_at->format('M d, Y \a\t h:i A') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="info-label">Last Updated</label>
                                <div class="info-value">
                                    <i class="fas fa-calendar-edit text-primary me-1"></i>
                                    {{ $enhancedProduct->updated_at->format('M d, Y \a\t h:i A') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Product Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.info-label {
    font-weight: 600;
    color: #6c757d;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
    display: block;
}

.info-value {
    font-size: 1rem;
    color: #212529;
}

.pricing-item {
    transition: transform 0.2s ease;
}

.pricing-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.image-container {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.image-container:hover .image-overlay {
    opacity: 1;
}

.product-image {
    transition: transform 0.3s ease;
}

.image-container:hover .product-image {
    transform: scale(1.05);
}

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
}

.card-header {
    border-bottom: none;
}

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

/* Card header height reduction */
.card-header {
    padding: 0.5rem 1rem;
    min-height: 2.5rem;
}

.card-header h6 {
    font-size: 0.95rem;
    font-weight: 600;
    line-height: 1.2;
}

/* Responsive improvements for card headers */
@media (max-width: 768px) {
    .card-header {
        padding: 0.25rem 0.5rem;
        min-height: 2rem;
    }
    
    .card-header h6 {
        font-size: 0.85rem;
    }
}

/* Font size reductions */
.info-label {
    font-weight: 600;
    color: #6c757d;
    font-size: 0.8rem;
    margin-bottom: 0.25rem;
    display: block;
}

.info-value {
    font-size: 0.9rem;
    color: #212529;
}

.pricing-item h6 {
    font-size: 0.8rem;
}

.pricing-item h4 {
    font-size: 1.1rem;
}

.page-title {
    font-size: 1.25rem;
}

h2 {
    font-size: 1.5rem;
}

.text-muted {
    font-size: 0.8rem;
}

.badge {
    font-size: 0.75rem;
}

.btn {
    font-size: 0.875rem;
}

/* Breadcrumb font size */
.breadcrumb {
    font-size: 0.8rem;
}

.breadcrumb-item i {
    font-size: 0.7rem;
}

/* Card body content */
.card-body {
    font-size: 0.9rem;
}

/* Modal content */
.modal-title {
    font-size: 1.1rem;
}

/* Info items spacing */
.info-item {
    margin-bottom: 1rem;
}

/* Pricing items */
.pricing-item {
    padding: 1rem;
}

.pricing-item i {
    font-size: 1.2rem;
}

/* Image overlay text */
.image-overlay i {
    font-size: 1.5rem;
}

/* Empty state text */
.text-center.py-5 {
    font-size: 0.9rem;
}

.text-center.py-5 i {
    font-size: 2.5rem;
}

/* Button group */
.btn-group .btn {
    font-size: 0.8rem;
    padding: 0.375rem 0.75rem;
}

/* Header section */
.d-flex h2 {
    font-size: 1.4rem;
}

.d-flex p {
    font-size: 0.85rem;
}
</style>
@endpush

@push('scripts')
<script>
function openImageModal(imageSrc, imageAlt) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('modalImage').alt = imageAlt;
    document.getElementById('imageModalLabel').textContent = imageAlt;
    
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}
</script>
@endpush