@extends('layouts.admin')

@section('title', 'Create Enhanced Product')

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
                <i class="fas fa-plus me-1"></i>Create Product
            </li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
               
                <h4 class="page-title">Create Enhanced Product</h4>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Validation Errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.enhanced-products.store') }}" method="POST" enctype="multipart/form-data" id="product-form" novalidate>
        @csrf
        
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0 text-white">
                            <i class="fas fa-info-circle me-2"></i>Basic Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', 'Test Product') }}" required autocomplete="off">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_nickname" class="form-label">Product Nickname</label>
                                    <input type="text" class="form-control @error('product_nickname') is-invalid @enderror" 
                                           id="product_nickname" name="product_nickname" value="{{ old('product_nickname') }}">
                                    @error('product_nickname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="product_id" class="form-label">Product ID</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control @error('product_id') is-invalid @enderror" 
                                               id="product_id" name="product_id" value="{{ old('product_id') }}" readonly>
                                        <button type="button" class="btn btn-outline-primary" id="btn-generate-product-id">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </div>
                                    @error('product_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="barcode" class="form-label">Barcode</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control @error('barcode') is-invalid @enderror" 
                                               id="barcode" name="barcode" value="{{ old('barcode') }}" readonly>
                                        <button type="button" class="btn btn-outline-primary" id="btn-generate-barcode">
                                            <i class="fas fa-barcode"></i>
                                        </button>
                                    </div>
                                    @error('barcode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="slug" class="form-label">Slug</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                               id="slug" name="slug" value="{{ old('slug') }}" readonly>
                                        <button type="button" class="btn btn-outline-primary" id="btn-generate-slug">
                                            <i class="fas fa-link"></i>
                                        </button>
                                    </div>
                                    @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                    @isset($categories)
                                        <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required autocomplete="off">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->name }}" {{ old('category', $categories->first()->name ?? '') === $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" class="form-control @error('category') is-invalid @enderror" 
                                               id="category" name="category" value="{{ old('category') }}" required>
                                    @endisset
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="brand" class="form-label">Brand</label>
                                    <input type="text" class="form-control @error('brand') is-invalid @enderror" 
                                           id="brand" name="brand" value="{{ old('brand') }}">
                                    @error('brand')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="material" class="form-label">Material</label>
                                    <input type="text" class="form-control @error('material') is-invalid @enderror" 
                                           id="material" name="material" value="{{ old('material') }}">
                                    @error('material')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="weight" class="form-label">Weight (kg)</label>
                                    <input type="number" step="0.01" class="form-control @error('weight') is-invalid @enderror" 
                                           id="weight" name="weight" value="{{ old('weight') }}" placeholder="0.00">
                                    @error('weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="printing_type" class="form-label">Printing Type</label>
                                    <input type="text" class="form-control @error('printing_type') is-invalid @enderror" 
                                           id="printing_type" name="printing_type" value="{{ old('printing_type') }}">
                                    @error('printing_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Variants -->
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <h6 class="card-title mb-0 text-white">
                            <i class="fas fa-layer-group me-2"></i>Product Variants
                        </h6>
                        <button type="button" class="btn btn-light btn-sm" id="btn-add-variant-category">
                            <i class="fas fa-plus me-1"></i>Add Variant Category
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            Add variant categories (e.g., Size, Color, Material) and their options with price adjustments.
                        </p>
                        <div id="variants-container">
                            <!-- Variant categories will be added here -->
                        </div>
                    </div>
                </div>

                <!-- Retail Pricing Tiers (temporarily disabled) -->
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <h6 class="card-title mb-0 text-white">
                            <i class="fas fa-shopping-cart me-2"></i>Retail Pricing Tiers
                        </h6>
                        <button type="button" class="btn btn-light btn-sm" id="btn-add-retail-tier">
                            <i class="fas fa-plus me-1"></i>Add Retail Tier
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            These prices will be visible only to retail customers.
                        </p>
                        <div id="retail-tiers-container">
                            <!-- Retail pricing tiers will be added here -->
                        </div>
                    </div>
                </div>

                <!-- Wholesale Pricing Tiers (temporarily disabled) -->
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <h6 class="card-title mb-0 text-white">
                            <i class="fas fa-warehouse me-2"></i>Wholesale Pricing Tiers
                        </h6>
                        <button type="button" class="btn btn-light btn-sm" id="btn-add-wholesale-tier">
                            <i class="fas fa-plus me-1"></i>Add Wholesale Tier
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            These prices will be visible only to wholesale customers.
                        </p>
                        <div id="wholesale-tiers-container">
                            <!-- Wholesale pricing tiers will be added here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Product Images -->
                <div class="card">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0 text-white">
                            <i class="fas fa-images me-2"></i>Product Images
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="image-upload-box">
                                    <input type="file" class="form-control" id="image1" name="images[]" accept="image/*" onchange="previewImage(this, 'preview1')">
                                    <div id="preview1" class="image-preview">
                                        <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
                                        <small class="text-muted d-block">Image 1</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="image-upload-box">
                                    <input type="file" class="form-control" id="image2" name="images[]" accept="image/*" onchange="previewImage(this, 'preview2')">
                                    <div id="preview2" class="image-preview">
                                        <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
                                        <small class="text-muted d-block">Image 2</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="image-upload-box">
                                    <input type="file" class="form-control" id="image3" name="images[]" accept="image/*" onchange="previewImage(this, 'preview3')">
                                    <div id="preview3" class="image-preview">
                                        <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
                                        <small class="text-muted d-block">Image 3</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="image-upload-box">
                                    <input type="file" class="form-control" id="image4" name="images[]" accept="image/*" onchange="previewImage(this, 'preview4')">
                                    <div id="preview4" class="image-preview">
                                        <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
                                        <small class="text-muted d-block">Image 4</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="image-upload-box">
                                    <input type="file" class="form-control" id="image5" name="images[]" accept="image/*" onchange="previewImage(this, 'preview5')">
                                    <div id="preview5" class="image-preview">
                                        <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
                                        <small class="text-muted d-block">Image 5</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Settings -->
                <div class="card mt-3">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0 text-white">
                            <i class="fas fa-dollar-sign me-2"></i>Pricing & Settings
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="buying_price" class="form-label">Buying Price (TZS) <span class="text-danger">*</span></label>
                                   <input type="number" step="0.01" class="form-control @error('buying_price') is-invalid @enderror" 
                                   id="buying_price" name="buying_price" value="{{ old('buying_price', '100') }}" required autocomplete="off">
                            @error('buying_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="retail_base_price" class="form-label">Retail Base Price (TZS)</label>
                                    <input type="number" step="0.01" class="form-control @error('retail_base_price') is-invalid @enderror" 
                                           id="retail_base_price" name="retail_base_price" value="{{ old('retail_base_price') }}" placeholder="0.00">
                                    <div class="form-text">Base price for retail customers</div>
                                    @error('retail_base_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="b2b_base_price" class="form-label">B2B Base Price (TZS)</label>
                                    <input type="number" step="0.01" class="form-control @error('b2b_base_price') is-invalid @enderror" 
                                           id="b2b_base_price" name="b2b_base_price" value="{{ old('b2b_base_price') }}" placeholder="0.00">
                                    <div class="form-text">Base price for wholesale customers</div>
                                    @error('b2b_base_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="min_quantity" class="form-label">Minimum Order Quantity</label>
                                    <input type="number" min="1" class="form-control @error('min_quantity') is-invalid @enderror"
                                           id="min_quantity" name="min_quantity" value="{{ old('min_quantity', 1) }}" autocomplete="off">
                                    <div class="form-text">Customers must order at least this quantity (e.g. 10, 100).</div>
                                    @error('min_quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_quantity" class="form-label">Maximum Order Quantity</label>
                                    <input type="number" min="1" class="form-control @error('max_quantity') is-invalid @enderror"
                                           id="max_quantity" name="max_quantity" value="{{ old('max_quantity') }}" autocomplete="off">
                                    <div class="form-text">Optional upper limit per order. Leave empty for no limit.</div>
                                    @error('max_quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>


                        <div class="mb-3">
                            <label for="stock_quantity" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" min="0" class="form-control @error('stock_quantity') is-invalid @enderror" 
                                       id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', '0') }}" required autocomplete="off">
                                <select class="form-select @error('stock_unit') is-invalid @enderror" 
                                        id="stock_unit" name="stock_unit" style="max-width: 120px" required>
                                    <option value="pcs" {{ old('stock_unit', 'pcs') == 'pcs' ? 'selected' : '' }}>pcs</option>
                                    <option value="m" {{ old('stock_unit') == 'm' ? 'selected' : '' }}>m</option>
                                </select>
                            </div>
                            <div class="form-text" id="stock_help">Enter the quantity you have in stock or are importing.</div>
                            @error('stock_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('stock_unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="retail_visible" name="retail_visible" value="1" {{ old('retail_visible', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="retail_visible">Retail Visible</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="wholesale_visible" name="wholesale_visible" value="1" {{ old('wholesale_visible', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="wholesale_visible">Wholesale Visible</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <input type="submit" class="btn btn-primary btn-lg" id="submit-btn" value="Create Product" data-no-global-handler>
                            <a href="{{ route('admin.enhanced-products.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
.image-upload-box {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
    min-height: 140px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.image-upload-box:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.image-preview {
    margin-top: 15px;
}

.image-preview img {
    max-width: 100%;
    max-height: 120px;
    border-radius: 4px;
}

.variant-item, .pricing-tier-item {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #dee2e6;
}

.variant-category-item {
    margin-bottom: 25px;
}

.variant-category-item .card {
    border: 2px solid #e3f2fd;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.variant-category-item .card-header {
    background-color: #dc3545 !important;
    border-bottom: 1px solid #bb2d3b;
    padding: 15px;
}

.variant-option-item {
    background-color: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 15px;
    transition: all 0.2s ease;
    margin-bottom: 15px;
}

.variant-option-item:hover {
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    border-color: #90caf9;
}

.custom-category-input {
    border-color: #ffc107 !important;
}

/* Pricing Tier Background Colors */
.bg-light-success {
    background-color: #d4edda !important;
    border: 1px solid #c3e6cb;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.bg-light-primary {
    background-color: #d1ecf1 !important;
    border: 1px solid #bee5eb;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

/* Color Picker Styles */
.color-picker-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
}

.color-picker-input {
    width: 50px;
    height: 38px;
    padding: 2px;
    border: 2px solid #dee2e6;
    border-radius: 6px;
    cursor: pointer;
}

.color-picker-input:hover {
    border-color: #90caf9;
}

.color-preview-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 2px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

.color-preview-circle:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn-loading {
    position: relative;
    color: transparent !important;
}

.btn-loading::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.fade-in {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
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

/* Font size reductions */
.form-label {
    font-size: 0.875rem;
    font-weight: 500;
}

.form-control, .form-select {
    font-size: 0.875rem;
}

.form-text {
    font-size: 0.75rem;
}

.form-check-label {
    font-size: 0.875rem;
}

.btn {
    font-size: 0.875rem;
}

.btn-sm {
    font-size: 0.75rem;
}

.card-title {
    font-size: 1rem;
}

.page-title {
    font-size: 1.25rem;
}

.text-muted {
    font-size: 0.75rem;
}

.breadcrumb {
    font-size: 0.8rem;
}

.breadcrumb-item i {
    font-size: 0.7rem;
}

/* Variant specific font reductions */
.variant-category-item .form-label {
    font-size: 0.8rem;
}

.variant-option-item .form-label {
    font-size: 0.75rem;
}

.variant-option-item .form-control {
    font-size: 0.8rem;
}

.variant-option-item .btn {
    font-size: 0.75rem;
}

/* Pricing tier font reductions */
.pricing-tier-item .form-label {
    font-size: 0.8rem;
}

.pricing-tier-item .form-control {
    font-size: 0.8rem;
}

.pricing-tier-item .btn {
    font-size: 0.75rem;
}

.pricing-tier-item .text-muted {
    font-size: 0.7rem;
}

/* Card body spacing */
.card-body {
    padding: 20px;
}

/* Form group spacing */
.mb-3 {
    margin-bottom: 1.25rem !important;
}

.mb-2 {
    margin-bottom: 1rem !important;
}

.mt-2 {
    margin-top: 1rem !important;
}

.mt-3 {
    margin-top: 1.5rem !important;
}

/* Row spacing */
.row {
    margin-bottom: 1rem;
}

/* Button spacing */
.btn {
    padding: 0.5rem 1rem;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
}

/* Input group spacing */
.input-group {
    margin-bottom: 0.5rem;
}

/* Alert spacing */
.alert {
    margin-bottom: 1.5rem;
    padding: 1rem 1.25rem;
}
</style>
@endpush

@push('scripts')
<script>
// Submission hard fallback: ensure form submits when button clicked
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('product-form');
    const btn = document.getElementById('submit-btn');
    if (form && btn) {
        btn.addEventListener('click', function() {
            setTimeout(function() {
                try {
                    if (document.readyState === 'complete' && window.location.pathname.endsWith('/admin/enhanced-products/create')) {
                        form.submit();
                    }
                } catch (e) {}
            }, 0);
        }, { passive: true });
    }
});

// Global counters
let variantCounter = 0;
let pricingTierCounter = 0;

// Generate Product ID
document.getElementById('btn-generate-product-id').addEventListener('click', function() {
    const button = this;
    const input = document.getElementById('product_id');
    
    button.classList.add('btn-loading');
    button.disabled = true;
    
    setTimeout(() => {
        const productId = 'PID-' + Math.random().toString(36).substr(2, 9).toUpperCase();
        input.value = productId;
        
        button.classList.remove('btn-loading');
        button.disabled = false;
        
        // Success animation
        input.style.borderColor = '#28a745';
        setTimeout(() => {
            input.style.borderColor = '';
        }, 2000);
    }, 1000);
});

// Generate Barcode
document.getElementById('btn-generate-barcode').addEventListener('click', function() {
    const button = this;
    const input = document.getElementById('barcode');
    
    button.classList.add('btn-loading');
    button.disabled = true;
    
    setTimeout(() => {
        const timestamp = Date.now().toString().slice(-6);
        const barcode = 'CHB-' + timestamp;
        input.value = barcode;
        
        button.classList.remove('btn-loading');
        button.disabled = false;
        
        // Success animation
        input.style.borderColor = '#28a745';
        setTimeout(() => {
            input.style.borderColor = '';
        }, 2000);
    }, 1000);
});

// Generate Slug
document.getElementById('btn-generate-slug').addEventListener('click', function() {
    const productName = document.getElementById('name').value;
    const slugInput = document.getElementById('slug');
    
    if (productName) {
        const slug = productName
            .toLowerCase()
            .replace(/[^a-z0-9 -]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim('-');
        slugInput.value = slug;
        
        // Success animation
        slugInput.style.borderColor = '#28a745';
        setTimeout(() => {
            slugInput.style.borderColor = '';
        }, 2000);
    } else {
        alert('Please enter a product name first');
    }
});

// Auto-generate slug when product name changes
document.getElementById('name').addEventListener('input', function() {
    const slugInput = document.getElementById('slug');
    if (slugInput.value === '') {
        const productName = this.value;
        if (productName) {
            const slug = productName
                .toLowerCase()
                .replace(/[^a-z0-9 -]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            slugInput.value = slug;
        }
    }
});

// Predefined variant categories
const predefinedCategories = ['Size', 'Color', 'Material', 'Capacity', 'Weight', 'Packaging Type', 'Custom'];

// Add Variant Category
document.getElementById('btn-add-variant-category').addEventListener('click', function() {
    addVariantCategory();
});

function addVariantCategory() {
    const container = document.getElementById('variants-container');
    const categoryDiv = document.createElement('div');
    categoryDiv.className = 'variant-category-item fade-in';
    categoryDiv.setAttribute('data-category-index', variantCounter);
    
    categoryDiv.innerHTML = `
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                    <label class="form-label mb-0 me-2 text-white">Variant Category:</label>
                    <select class="form-select form-select-sm d-inline-block w-auto variant-category-select" 
                            name="variants[${variantCounter}][category]" 
                            onchange="handleCategoryChange(this, ${variantCounter})" 
                            data-category-index="${variantCounter}" 
                            required>
                        <option value="">Select Category</option>
                        ${predefinedCategories.map(cat => `<option value="${cat}">${cat}</option>`).join('')}
                    </select>
                    <input type="text" class="form-control form-control-sm d-none custom-category-input" placeholder="Enter custom category name" style="width: 200px; display: inline-block;">
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeVariantCategory(this)">
                    <i class="fas fa-trash"></i> Remove Category
                </button>
            </div>
            <div class="card-body">
                <div class="variant-options-container" data-category-index="${variantCounter}">
                    <!-- Variant options will be added here -->
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addVariantOption(${variantCounter})">
                    <i class="fas fa-plus me-1"></i>Add Option
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(categoryDiv);
    variantCounter++;
}

// Handle category change
function handleCategoryChange(select, categoryIndex) {
    const customInput = select.nextElementSibling;
    
    // Handle custom category
    if (select.value === 'Custom') {
        select.classList.add('d-none');
        customInput.classList.remove('d-none');
        customInput.required = true;
        customInput.focus();
        select.removeAttribute('name');
        customInput.setAttribute('name', `variants[${categoryIndex}][category]`);
    }
    
    // Store category type for later use when adding options
    const categoryCard = select.closest('.variant-category-item');
    categoryCard.setAttribute('data-category-type', select.value);
}

// Add Variant Option
function addVariantOption(categoryIndex) {
    const container = document.querySelector(`.variant-options-container[data-category-index="${categoryIndex}"]`);
    const optionIndex = container.children.length;
    
    // Check if this is a Color category
    const categoryCard = container.closest('.variant-category-item');
    const categoryType = categoryCard.getAttribute('data-category-type');
    const isColorCategory = categoryType === 'Color';
    
    const optionDiv = document.createElement('div');
    optionDiv.className = 'variant-option-item row mb-2 align-items-end';
    
    if (isColorCategory) {
        // Color variant with color picker
        optionDiv.innerHTML = `
            <div class="col-md-2">
                <label class="form-label small">Color Picker <span class="text-danger">*</span></label>
                <div class="color-picker-wrapper">
                    <input type="color" class="form-control form-control-color form-control-sm color-picker-input" 
                           name="variants[${categoryIndex}][options][${optionIndex}][color_code]" 
                           value="#000000"
                           onchange="updateColorPreview(this, ${categoryIndex}, ${optionIndex})"
                           title="Choose color">
                    <div class="color-preview-circle" id="color-preview-${categoryIndex}-${optionIndex}" 
                         style="background-color: #000000;"></div>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Color Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm" 
                       name="variants[${categoryIndex}][options][${optionIndex}][name]" 
                       value="Black"
                       placeholder="e.g., Red" required>
                <small class="text-muted" style="font-size: 0.75rem;">Auto-detected, editable</small>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Description</label>
                <input type="text" class="form-control form-control-sm" 
                       name="variants[${categoryIndex}][options][${optionIndex}][description]" 
                       placeholder="Optional description">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Retail Price (TZS)</label>
                <input type="number" step="0.01" class="form-control form-control-sm" 
                       name="variants[${categoryIndex}][options][${optionIndex}][retail_price]" 
                       value="0" min="0" placeholder="0.00">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Wholesale Price (TZS)</label>
                <input type="number" step="0.01" class="form-control form-control-sm" 
                       name="variants[${categoryIndex}][options][${optionIndex}][wholesale_price]" 
                       value="0" min="0" placeholder="0.00">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeVariantOption(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    } else {
        // Standard variant (Size, Material, etc.)
        optionDiv.innerHTML = `
            <div class="col-md-3">
                <label class="form-label small">Option Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm" 
                       name="variants[${categoryIndex}][options][${optionIndex}][name]" 
                       placeholder="e.g., Small, Large" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Description</label>
                <input type="text" class="form-control form-control-sm" 
                       name="variants[${categoryIndex}][options][${optionIndex}][description]" 
                       placeholder="Optional description">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Retail Price (TZS)</label>
                <input type="number" step="0.01" class="form-control form-control-sm" 
                       name="variants[${categoryIndex}][options][${optionIndex}][retail_price]" 
                       value="0" min="0" placeholder="0.00">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Wholesale Price (TZS)</label>
                <input type="number" step="0.01" class="form-control form-control-sm" 
                       name="variants[${categoryIndex}][options][${optionIndex}][wholesale_price]" 
                       value="0" min="0" placeholder="0.00">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeVariantOption(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    }
    
    container.appendChild(optionDiv);
}

// Color name database
const colorNames = {
    '#000000': 'Black', '#FFFFFF': 'White', '#FF0000': 'Red', '#00FF00': 'Lime',
    '#0000FF': 'Blue', '#FFFF00': 'Yellow', '#00FFFF': 'Cyan', '#FF00FF': 'Magenta',
    '#C0C0C0': 'Silver', '#808080': 'Gray', '#800000': 'Maroon', '#808000': 'Olive',
    '#008000': 'Green', '#800080': 'Purple', '#008080': 'Teal', '#000080': 'Navy',
    '#FFA500': 'Orange', '#FFC0CB': 'Pink', '#A52A2A': 'Brown', '#FFD700': 'Gold',
    '#4B0082': 'Indigo', '#EE82EE': 'Violet', '#F0E68C': 'Khaki', '#E6E6FA': 'Lavender',
    '#FFE4E1': 'Misty Rose', '#FA8072': 'Salmon', '#FF6347': 'Tomato', '#FF4500': 'Orange Red',
    '#DC143C': 'Crimson', '#B22222': 'Fire Brick', '#8B0000': 'Dark Red', '#FF1493': 'Deep Pink',
    '#FF69B4': 'Hot Pink', '#FFB6C1': 'Light Pink', '#FFA07A': 'Light Salmon', '#F08080': 'Light Coral',
    '#E9967A': 'Dark Salmon', '#CD5C5C': 'Indian Red', '#BC8F8F': 'Rosy Brown', '#F4A460': 'Sandy Brown',
    '#DAA520': 'Goldenrod', '#B8860B': 'Dark Goldenrod', '#CD853F': 'Peru', '#D2691E': 'Chocolate',
    '#8B4513': 'Saddle Brown', '#A0522D': 'Sienna', '#D2B48C': 'Tan', '#DEB887': 'Burlywood',
    '#F5DEB3': 'Wheat', '#FFDEAD': 'Navajo White', '#FFE4B5': 'Moccasin', '#FFEFD5': 'Papaya Whip',
    '#FAFAD2': 'Light Goldenrod', '#FFFFE0': 'Light Yellow', '#FFFACD': 'Lemon Chiffon',
    '#EEE8AA': 'Pale Goldenrod', '#F0E68C': 'Khaki', '#BDB76B': 'Dark Khaki', '#9ACD32': 'Yellow Green',
    '#556B2F': 'Dark Olive Green', '#6B8E23': 'Olive Drab', '#7CFC00': 'Lawn Green', '#7FFF00': 'Chartreuse',
    '#ADFF2F': 'Green Yellow', '#00FF00': 'Lime', '#32CD32': 'Lime Green', '#00FA9A': 'Medium Spring Green',
    '#00FF7F': 'Spring Green', '#90EE90': 'Light Green', '#98FB98': 'Pale Green', '#8FBC8F': 'Dark Sea Green',
    '#3CB371': 'Medium Sea Green', '#2E8B57': 'Sea Green', '#228B22': 'Forest Green', '#006400': 'Dark Green',
    '#66CDAA': 'Medium Aquamarine', '#7FFFD4': 'Aquamarine', '#40E0D0': 'Turquoise', '#48D1CC': 'Medium Turquoise',
    '#00CED1': 'Dark Turquoise', '#20B2AA': 'Light Sea Green', '#5F9EA0': 'Cadet Blue', '#008B8B': 'Dark Cyan',
    '#B0E0E6': 'Powder Blue', '#ADD8E6': 'Light Blue', '#87CEEB': 'Sky Blue', '#87CEFA': 'Light Sky Blue',
    '#00BFFF': 'Deep Sky Blue', '#1E90FF': 'Dodger Blue', '#6495ED': 'Cornflower Blue', '#4169E1': 'Royal Blue',
    '#0000CD': 'Medium Blue', '#00008B': 'Dark Blue', '#191970': 'Midnight Blue', '#7B68EE': 'Medium Slate Blue',
    '#6A5ACD': 'Slate Blue', '#483D8B': 'Dark Slate Blue', '#9370DB': 'Medium Purple', '#8B008B': 'Dark Magenta',
    '#9400D3': 'Dark Violet', '#9932CC': 'Dark Orchid', '#BA55D3': 'Medium Orchid', '#DA70D6': 'Orchid',
    '#DDA0DD': 'Plum', '#D8BFD8': 'Thistle', '#E0B0FF': 'Mauve', '#C71585': 'Medium Violet Red'
};

// Get color name from hex code
function getColorName(hexColor) {
    const upperHex = hexColor.toUpperCase();
    
    // Exact match
    if (colorNames[upperHex]) {
        return colorNames[upperHex];
    }
    
    // Find closest color name
    let closestColor = 'Custom Color';
    let minDistance = Infinity;
    
    const rgb = hexToRgb(hexColor);
    
    for (const [hex, name] of Object.entries(colorNames)) {
        const targetRgb = hexToRgb(hex);
        const distance = Math.sqrt(
            Math.pow(rgb.r - targetRgb.r, 2) +
            Math.pow(rgb.g - targetRgb.g, 2) +
            Math.pow(rgb.b - targetRgb.b, 2)
        );
        
        if (distance < minDistance) {
            minDistance = distance;
            closestColor = name;
        }
    }
    
    // If very close to a named color, use it
    return minDistance < 50 ? closestColor : 'Custom Color';
}

// Convert hex to RGB
function hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : { r: 0, g: 0, b: 0 };
}

// Update color preview circle and auto-fill color name
function updateColorPreview(input, categoryIndex, optionIndex) {
    const preview = document.getElementById(`color-preview-${categoryIndex}-${optionIndex}`);
    const nameInput = document.querySelector(`input[name="variants[${categoryIndex}][options][${optionIndex}][name]"]`);
    
    if (preview) {
        preview.style.backgroundColor = input.value;
    }
    
    // Auto-fill color name if field is empty or contains a color name
    if (nameInput) {
        const currentValue = nameInput.value.trim();
        const detectedName = getColorName(input.value);
        
        // Only auto-fill if empty or if current value looks like a color name
        const isColorName = currentValue === '' || 
                           Object.values(colorNames).some(name => 
                               currentValue.toLowerCase() === name.toLowerCase()
                           ) ||
                           currentValue === 'Custom Color';
        
        if (isColorName) {
            nameInput.value = detectedName;
            // Add visual feedback
            nameInput.style.borderColor = '#28a745';
            setTimeout(() => {
                nameInput.style.borderColor = '';
            }, 1000);
        }
    }
}

// Remove Variant Category
function removeVariantCategory(button) {
    button.closest('.variant-category-item').remove();
}

// Remove Variant Option
function removeVariantOption(button) {
    button.closest('.variant-option-item').remove();
}

// Add Retail Pricing Tier
document.getElementById('btn-add-retail-tier').addEventListener('click', function() {
    addPricingTier('retail');
});

// Add Wholesale Pricing Tier
document.getElementById('btn-add-wholesale-tier').addEventListener('click', function() {
    addPricingTier('wholesale');
});

// Add Pricing Tier (Generic Function)
function addPricingTier(customerType) {
    const container = document.getElementById(`${customerType}-tiers-container`);
    const tierDiv = document.createElement('div');
    tierDiv.className = 'pricing-tier-item fade-in';
    
    const bgColor = customerType === 'retail' ? 'bg-light-success' : 'bg-light-primary';
    
    tierDiv.innerHTML = `
        <div class="row ${bgColor}">
            <div class="col-md-3">
                <label class="form-label small">Min Quantity <span class="text-danger">*</span></label>
                <input type="number" class="form-control" 
                       name="price_tiers[${pricingTierCounter}][min_quantity]" 
                       min="1" value="1" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Max Quantity</label>
                <input type="number" class="form-control" 
                       name="price_tiers[${pricingTierCounter}][max_quantity]" 
                       min="1" placeholder="Unlimited">
                <small class="text-muted">Leave empty for unlimited</small>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Price per Unit (TZS) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" 
                       name="price_tiers[${pricingTierCounter}][price_per_unit]" 
                       min="0" placeholder="0.00" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="removePricingTier(this)">
                    <i class="fas fa-trash me-1"></i>Remove
                </button>
            </div>
            <input type="hidden" name="price_tiers[${pricingTierCounter}][customer_type]" value="${customerType}">
        </div>
    `;
    
    container.appendChild(tierDiv);
    pricingTierCounter++;
}

// Remove Pricing Tier
function removePricingTier(button) {
    button.closest('.pricing-tier-item').remove();
}

// Image Preview
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            input.value = '';
            return;
        }
        
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Preview" class="img-fluid">
                <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeImage('${input.id}', '${previewId}')">
                    <i class="fas fa-times"></i> Remove
                </button>
            `;
        };
        reader.readAsDataURL(file);
    }
}

// Remove Image
function removeImage(inputId, previewId) {
    document.getElementById(inputId).value = '';
    const preview = document.getElementById(previewId);
    preview.innerHTML = `
        <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
        <small class="text-muted d-block">Image ${previewId.replace('preview', '')}</small>
    `;
}

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate initial values
    setTimeout(() => {
        const btnProductId = document.getElementById('btn-generate-product-id');
        const btnBarcode = document.getElementById('btn-generate-barcode');
        
        if (btnProductId) btnProductId.click();
        if (btnBarcode) btnBarcode.click();
    }, 500);

    // Update stock quantity help text based on selected unit
    const stockUnitSelect = document.getElementById('stock_unit');
    const stockQuantityHelp = document.getElementById('stock_help');
    
    if (stockUnitSelect && stockQuantityHelp) {
        function updateHelpText() {
            const unit = stockUnitSelect.value;
            const unitText = unit === 'pcs' ? 'pieces' : 'meters';
            if (stockQuantityHelp) {
                stockQuantityHelp.textContent = `Enter the quantity in ${unitText} you have in stock or are importing.`;
            }
        }
        
        stockUnitSelect.addEventListener('change', updateHelpText);
        updateHelpText(); // Initial call
    }
    
    // ULTRA SIMPLE FORM TEST - NO JAVASCRIPT INTERFERENCE
    console.log('=== ULTRA SIMPLE FORM TEST ===');
    
    const form = document.getElementById('product-form');
    const submitBtn = document.getElementById('submit-btn');
    
    console.log('Form element:', form);
    console.log('Submit button:', submitBtn);
    console.log('Form action:', form ? form.action : 'NO FORM');
    console.log('Form method:', form ? form.method : 'NO FORM');
    
    // Remove all JS submission handlers to allow pure HTML POST
});
</script>
@endpush