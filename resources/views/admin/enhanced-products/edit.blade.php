@extends('layouts.admin')

@section('title', 'Edit Enhanced Product')

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
                <i class="fas fa-edit me-1"></i>Edit Product
            </li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Edit Product</h4>
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

    <form action="{{ route('admin.enhanced-products.update', $enhancedProduct->barcode) }}" method="POST" enctype="multipart/form-data" id="product-form" novalidate>
        @csrf
        @method('PUT')
        
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
                                           id="name" name="name" value="{{ old('name', $enhancedProduct->name) }}" required autocomplete="off">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_nickname" class="form-label">Product Nickname</label>
                                    <input type="text" class="form-control @error('product_nickname') is-invalid @enderror" 
                                           id="product_nickname" name="product_nickname" value="{{ old('product_nickname', $enhancedProduct->product_nickname) }}">
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
                                               id="product_id" name="product_id" value="{{ old('product_id', $enhancedProduct->product_id) }}" readonly>
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
                                               id="barcode" name="barcode" value="{{ old('barcode', $enhancedProduct->barcode) }}" readonly>
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
                                               id="slug" name="slug" value="{{ old('slug', $enhancedProduct->slug) }}" readonly>
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
                                      id="description" name="description" rows="3">{{ old('description', $enhancedProduct->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                    @php
                                        $categories = \App\Models\Category::orderBy('name')->get(['id', 'name']);
                                    @endphp
                                    @if($categories->count() > 0)
                                        <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required autocomplete="off">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->name }}" {{ old('category', $enhancedProduct->category) === $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" class="form-control @error('category') is-invalid @enderror" 
                                               id="category" name="category" value="{{ old('category', $enhancedProduct->category) }}" required>
                                    @endif
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="brand" class="form-label">Brand</label>
                                    <input type="text" class="form-control @error('brand') is-invalid @enderror" 
                                           id="brand" name="brand" value="{{ old('brand', $enhancedProduct->brand) }}">
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
                                           id="material" name="material" value="{{ old('material', $enhancedProduct->material) }}">
                                    @error('material')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="weight" class="form-label">Weight (kg)</label>
                                    <input type="number" step="0.01" class="form-control @error('weight') is-invalid @enderror" 
                                           id="weight" name="weight" value="{{ old('weight', $enhancedProduct->weight) }}" placeholder="0.00">
                                    @error('weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="printing_type" class="form-label">Printing Type</label>
                                    <input type="text" class="form-control @error('printing_type') is-invalid @enderror" 
                                           id="printing_type" name="printing_type" value="{{ old('printing_type', $enhancedProduct->printing_type) }}">
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
                        <button type="button" class="btn btn-light btn-sm py-1 px-2" id="btn-add-variant-category">
                            <i class="fas fa-plus me-1"></i>Add Category
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            Add variant categories (e.g., Size, Color, Material) and their options with price adjustments.
                        </p>
                        <div id="variants-container">
                            @php $variantCounter = 0; @endphp
                            @if($enhancedProduct->variantCategories && $enhancedProduct->variantCategories->count() > 0)
                                @foreach($enhancedProduct->variantCategories as $category)
                                    @if($category->category) {{-- Only show categories with valid names --}}
                                    <div class="variant-category-item fade-in" data-category-index="{{ $variantCounter }}">
                                        <div class="card mb-3">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <div class="flex-grow-1">
                                                    <label class="form-label mb-0 me-2 text-white">Variant Category:</label>
                                                    <select class="form-select form-select-sm d-inline-block w-auto variant-category-select" 
                                                            name="variants[{{ $variantCounter }}][category]" 
                                                            onchange="handleCategoryChange(this, {{ $variantCounter }})" 
                                                            data-category-index="{{ $variantCounter }}" 
                                                            required>
                                                        @php $predefinedCategories = ['Size', 'Color', 'Material', 'Capacity', 'Weight', 'Packaging Type', 'Custom']; @endphp
                                                        <option value="">Select Category</option>
                                                        @foreach($predefinedCategories as $cat)
                                                            <option value="{{ $cat }}" {{ $category->category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                                        @endforeach
                                                    </select>
                                                    <input type="text" class="form-control form-control-sm d-none custom-category-input" 
                                                           placeholder="Enter custom category name" 
                                                           style="width: 200px; display: inline-block;"
                                                           value="{{ $category->category === 'Custom' ? $category->category : '' }}">
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeVariantCategory(this)">
                                                    <i class="fas fa-trash"></i> Remove Category
                                                </button>
                                            </div>
                                            <div class="card-body">
                                                <div class="variant-options-container" data-category-index="{{ $variantCounter }}">
                                                    @php $optIndex = 0; @endphp
                                                    @if($category->items && $category->items->count() > 0)
                                                        @foreach($category->items as $item)
                                                            @php $isColor = $category->category === 'Color'; @endphp
                                                            <div class="variant-option-item row mb-2 align-items-end">
                                                                @if($isColor)
                                                                    <div class="col-md-2">
                                                                        <label class="form-label small">Color Picker <span class="text-danger">*</span></label>
                                                                        <div class="color-picker-wrapper">
                                                                            <input type="color" class="form-control form-control-color form-control-sm color-picker-input" 
                                                                                   name="variants[{{ $variantCounter }}][options][{{ $optIndex }}][color_code]" 
                                                                                   value="{{ $item->color_code ?? '#000000' }}"
                                                                                   onchange="updateColorPreview(this, {{ $variantCounter }}, {{ $optIndex }})"
                                                                                   title="Choose color">
                                                                            <div class="color-preview-circle" id="color-preview-{{ $variantCounter }}-{{ $optIndex }}" 
                                                                                 style="background-color: {{ $item->color_code ?? '#000000' }};"></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label class="form-label small">Color Name <span class="text-danger">*</span></label>
                                                                        <input type="text" class="form-control form-control-sm" 
                                                                               name="variants[{{ $variantCounter }}][options][{{ $optIndex }}][name]" 
                                                                               value="{{ $item->name ?? '' }}" placeholder="e.g., Red" required>
                                                                        <small class="text-muted" style="font-size: 0.75rem;">Auto-detected, editable</small>
                                                                    </div>
                                                                @else
                                                                    <div class="col-md-4">
                                                                        <label class="form-label small">Option Name <span class="text-danger">*</span></label>
                                                                        <input type="text" class="form-control form-control-sm" 
                                                                               name="variants[{{ $variantCounter }}][options][{{ $optIndex }}][name]" 
                                                                               value="{{ $item->name ?? '' }}" placeholder="e.g., Small, Large" required>
                                                                    </div>
                                                                @endif
                                                                <div class="col-md-2">
                                                                    <label class="form-label small">Description</label>
                                                                    <input type="text" class="form-control form-control-sm" 
                                                                           name="variants[{{ $variantCounter }}][options][{{ $optIndex }}][description]" 
                                                                           value="{{ $item->description ?? '' }}" placeholder="Optional description">
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <label class="form-label small">Retail Price (TZS)</label>
                                                                    <input type="number" step="0.01" class="form-control form-control-sm" 
                                                                           name="variants[{{ $variantCounter }}][options][{{ $optIndex }}][retail_price]" 
                                                                           value="{{ $item->retail_price ?? 0 }}" min="0" placeholder="0.00">
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <label class="form-label small">Wholesale Price (TZS)</label>
                                                                    <input type="number" step="0.01" class="form-control form-control-sm" 
                                                                           name="variants[{{ $variantCounter }}][options][{{ $optIndex }}][wholesale_price]" 
                                                                           value="{{ $item->wholesale_price ?? 0 }}" min="0" placeholder="0.00">
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeVariantOption(this)">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            @php $optIndex++; @endphp
                                                        @endforeach
                                                    @endif
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addVariantOption({{ $variantCounter }})">
                                                    <i class="fas fa-plus me-1"></i>Add Option
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @php $variantCounter++; @endphp
                                    @endif {{-- Close the @if($category->category) --}}
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Retail Pricing Tiers -->
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <h6 class="card-title mb-0 text-white">
                            <i class="fas fa-shopping-cart me-2"></i>Retail Pricing Tiers
                        </h6>
                        <button type="button" class="btn btn-light btn-sm py-1 px-2" id="btn-add-retail-tier">
                            <i class="fas fa-plus me-1"></i>Add Tier
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            These prices will be visible only to retail customers.
                        </p>
                        <div id="retail-tiers-container">
                            @php $retailTierCounter = 0; @endphp
                            @if($enhancedProduct->priceTiers)
                                @foreach($enhancedProduct->priceTiers->where('customer_type', 'retail') as $tier)
                                    <div class="pricing-tier-item fade-in">
                                        <div class="row bg-light-success">
                                            <div class="col-md-3">
                                                <label class="form-label small">Min Quantity <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" 
                                                       name="price_tiers[{{ $retailTierCounter }}][min_quantity]" 
                                                       min="1" value="{{ $tier->min_quantity }}" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">Max Quantity</label>
                                                <input type="number" class="form-control" 
                                                       name="price_tiers[{{ $retailTierCounter }}][max_quantity]" 
                                                       min="1" value="{{ $tier->max_quantity }}" placeholder="Unlimited">
                                                <small class="text-muted">Leave empty for unlimited</small>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small">Price per Unit (TZS) <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" class="form-control" 
                                                       name="price_tiers[{{ $retailTierCounter }}][price_per_unit]" 
                                                       min="0" value="{{ $tier->price_per_unit }}" placeholder="0.00" required>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="removePricingTier(this)">
                                                    <i class="fas fa-trash me-1"></i>Remove
                                                </button>
                                            </div>
                                            <input type="hidden" name="price_tiers[{{ $retailTierCounter }}][customer_type]" value="retail">
                                        </div>
                                    </div>
                                    @php $retailTierCounter++; @endphp
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Wholesale Pricing Tiers -->
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <h6 class="card-title mb-0 text-white">
                            <i class="fas fa-warehouse me-2"></i>Wholesale Pricing Tiers
                        </h6>
                        <button type="button" class="btn btn-light btn-sm py-1 px-2" id="btn-add-wholesale-tier">
                            <i class="fas fa-plus me-1"></i>Add Tier
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            These prices will be visible only to wholesale customers.
                        </p>
                        <div id="wholesale-tiers-container">
                            @php $wholesaleTierCounter = 0; @endphp
                            @if($enhancedProduct->priceTiers)
                                @foreach($enhancedProduct->priceTiers->where('customer_type', 'wholesale') as $tier)
                                    <div class="pricing-tier-item fade-in">
                                        <div class="row bg-light-primary">
                                            <div class="col-md-3">
                                                <label class="form-label small">Min Quantity <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" 
                                                       name="price_tiers[{{ $wholesaleTierCounter }}][min_quantity]" 
                                                       min="1" value="{{ $tier->min_quantity }}" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">Max Quantity</label>
                                                <input type="number" class="form-control" 
                                                       name="price_tiers[{{ $wholesaleTierCounter }}][max_quantity]" 
                                                       min="1" value="{{ $tier->max_quantity }}" placeholder="Unlimited">
                                                <small class="text-muted">Leave empty for unlimited</small>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small">Price per Unit (TZS) <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" class="form-control" 
                                                       name="price_tiers[{{ $wholesaleTierCounter }}][price_per_unit]" 
                                                       min="0" value="{{ $tier->price_per_unit }}" placeholder="0.00" required>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="removePricingTier(this)">
                                                    <i class="fas fa-trash me-1"></i>Remove
                                                </button>
                                            </div>
                                            <input type="hidden" name="price_tiers[{{ $wholesaleTierCounter }}][customer_type]" value="wholesale">
                                        </div>
                                    </div>
                                    @php $wholesaleTierCounter++; @endphp
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Product Images -->
                <div class="card modern-image-card">
                    <div class="card-header modern-card-header">
                        <div class="header-content">
                            <div class="header-icon">
                                <i class="fas fa-images"></i>
                            </div>
                            <div class="header-text">
                                <h5 class="card-title mb-0">Product Images</h5>
                                <small class="text-muted">Manage your product gallery</small>
                            </div>
                        </div>
                        <div class="header-actions">
                            <span class="badge bg-primary">{{ $enhancedProduct->images ? $enhancedProduct->images->count() : 0 }} images</span>
                        </div>
                    </div>
                    <div class="card-body modern-card-body">
                        @if($enhancedProduct->images && $enhancedProduct->images->count() > 0)
                            <div class="mb-4">
                                <h6 class="section-title">
                                    <i class="fas fa-photo-video me-2"></i>Current Images
                                </h6>
                                <div class="image-gallery">
                                    @foreach($enhancedProduct->images as $image)
                                        <div class="image-item" data-image-id="{{ $image->id }}">
                                            <div class="image-container">
                                                <img src="{{ asset('storage/' . $image->image_path) }}" 
                                                     class="gallery-image" alt="Product Image">
                                                <div class="image-overlay">
                                                    <div class="image-actions">
                                                        <button type="button" class="btn btn-sm btn-light image-action-btn" 
                                                                onclick="viewImage('{{ asset('storage/' . $image->image_path) }}')" 
                                                                title="View Full Size">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger image-action-btn" 
                                                                onclick="showDeleteModal({{ $image->id }}, '{{ asset('storage/' . $image->image_path) }}')" 
                                                                title="Delete Image">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="image-info">
                                                <small class="text-muted">Image {{ $loop->iteration }}</small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-image"></i>
                                </div>
                                <h6 class="empty-title">No Images Yet</h6>
                                <p class="empty-text">Upload some images to showcase your product</p>
                            </div>
                        @endif

                        <div class="upload-section">
                            <div class="upload-area" onclick="document.getElementById('images').click()">
                                <div class="upload-content">
                                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                    <h6 class="upload-title">Add New Images</h6>
                                    <p class="upload-text">Click to browse or drag and drop</p>
                                    <small class="upload-hint">Up to 5 images, max 2MB each</small>
                                </div>
                            </div>
                            <input type="file" class="d-none @error('images') is-invalid @enderror" 
                                   id="images" name="images[]" multiple accept="image/*" onchange="previewNewImages(this)">
                            @error('images')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="new-images-preview" class="new-images-preview"></div>
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
                                   id="buying_price" name="buying_price" value="{{ old('buying_price', $enhancedProduct->buying_price) }}" required autocomplete="off">
                            @error('buying_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="retail_base_price" class="form-label">Retail Base Price (TZS)</label>
                                    <input type="number" step="0.01" class="form-control @error('retail_base_price') is-invalid @enderror" 
                                           id="retail_base_price" name="retail_base_price" value="{{ old('retail_base_price', $enhancedProduct->retail_base_price) }}" placeholder="0.00">
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
                                           id="b2b_base_price" name="b2b_base_price" value="{{ old('b2b_base_price', $enhancedProduct->b2b_base_price) }}" placeholder="0.00">
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
                                           id="min_quantity" name="min_quantity"
                                           value="{{ old('min_quantity', $enhancedProduct->min_quantity ?? 1) }}" autocomplete="off">
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
                                           id="max_quantity" name="max_quantity"
                                           value="{{ old('max_quantity', $enhancedProduct->max_quantity) }}" autocomplete="off">
                                    <div class="form-text">Optional upper limit per order. Leave empty for no limit.</div>
                                    @error('max_quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="stock_quantity" class="form-label">Stock Quantity</label>
                            <div class="input-group">
                                <input type="number" min="0" class="form-control @error('stock_quantity') is-invalid @enderror" 
                                       id="stock_quantity" name="stock_quantity" 
                                       value="{{ old('stock_quantity', $enhancedProduct->stock_quantity) }}" required>
                                <select class="form-select @error('stock_unit') is-invalid @enderror" 
                                        id="stock_unit" name="stock_unit" required style="max-width: 120px">
                                    <option value="pcs" {{ old('stock_unit', $enhancedProduct->stock_unit ?? 'pcs') == 'pcs' ? 'selected' : '' }}>pcs</option>
                                    <option value="m" {{ old('stock_unit', $enhancedProduct->stock_unit ?? 'pcs') == 'm' ? 'selected' : '' }}>m</option>
                                </select>
                            </div>
                            <div class="form-text">Current stock: {{ number_format($enhancedProduct->stock_quantity) }} {{ $enhancedProduct->stock_unit ?? 'pcs' }}. <a href="{{ route('admin.enhanced-products.quantity.edit', $enhancedProduct->barcode) }}">Adjust quantity</a>.</div>
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
                                    <input class="form-check-input" type="checkbox" id="retail_visible" name="retail_visible" value="1" {{ old('retail_visible', $enhancedProduct->retail_visible) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="retail_visible">Retail Visible</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="wholesale_visible" name="wholesale_visible" value="1" {{ old('wholesale_visible', $enhancedProduct->wholesale_visible) ? 'checked' : '' }}>
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
                            <button type="submit" class="btn btn-primary btn-lg" id="submit-btn" title="Click to update the product" data-no-global-handler>
                                <i class="fas fa-save me-2"></i>Update Product
                            </button>
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

<!-- Modern Modal for Image Deletion -->
<div class="modal fade" id="deleteImageModal" tabindex="-1" aria-labelledby="deleteImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modern-modal">
            <div class="modal-header modern-modal-header">
                <div class="modal-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h5 class="modal-title" id="deleteImageModalLabel">Delete Image</h5>
                <button type="button" class="btn-close modern-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body modern-modal-body">
                <p class="modal-message">Are you sure you want to delete this image? This action cannot be undone.</p>
                <div class="modal-image-preview" id="modalImagePreview">
                    <!-- Image preview will be inserted here -->
                </div>
            </div>
            <div class="modal-footer modern-modal-footer">
                <button type="button" class="btn btn-secondary modern-btn-cancel" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger modern-btn-delete" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-2"></i>Delete Image
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Modern Modal Styles */
.modern-modal {
    border: none;
    border-radius: 20px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}

.modern-modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 1.5rem;
    position: relative;
}

.modern-modal-header .modal-icon {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.modern-modal-header .modal-icon i {
    font-size: 1.5rem;
    color: white;
}

.modern-modal-header .modal-title {
    color: white;
    font-weight: 600;
    font-size: 1.25rem;
    margin: 0;
}

.modern-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    transition: all 0.3s ease;
}

.modern-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

.modern-modal-body {
    padding: 2rem;
    text-align: center;
}

.modern-modal-body .modal-message {
    font-size: 1.1rem;
    color: #6b7280;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.modal-image-preview {
    max-width: 200px;
    margin: 0 auto 1.5rem;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.modal-image-preview img {
    width: 100%;
    height: auto;
    display: block;
}

.modern-modal-footer {
    border: none;
    padding: 1.5rem 2rem;
    background: #f8fafc;
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.modern-btn-cancel {
    background: #e2e8f0;
    border: none;
    color: #64748b;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.modern-btn-cancel:hover {
    background: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.modern-btn-delete {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border: none;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.modern-btn-delete:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

/* Modern Image Card Styles */
.modern-image-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    overflow: hidden;
    transition: all 0.3s ease;
}

.modern-image-card:hover {
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    transform: translateY(-2px);
}

.modern-card-header {
    background-color: #dc3545;
    border: none;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.header-icon {
    width: 45px;
    height: 45px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.header-icon i {
    font-size: 1.25rem;
    color: white;
}

.header-text .card-title {
    color: white;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.header-text small {
    color: rgba(255, 255, 255, 0.8);
}

.header-actions .badge {
    background: rgba(255, 255, 255, 0.2) !important;
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    font-weight: 500;
}

.modern-card-body {
    padding: 1.5rem;
    background: #fafafa;
}

.section-title {
    color: #374151;
    font-weight: 600;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
}

.section-title i {
    color: #10b981;
}

/* Image Gallery Styles */
.image-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.image-item {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    background: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.image-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.image-container {
    position: relative;
    aspect-ratio: 1;
    overflow: hidden;
}

.gallery-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.image-item:hover .gallery-image {
    transform: scale(1.05);
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.image-item:hover .image-overlay {
    opacity: 1;
}

.image-actions {
    display: flex;
    gap: 0.5rem;
}

.image-action-btn {
    border-radius: 8px;
    padding: 0.5rem;
    border: none;
    transition: all 0.3s ease;
}

.image-action-btn:hover {
    transform: scale(1.1);
}

.image-info {
    padding: 0.5rem;
    text-align: center;
    background: white;
}

/* Empty State Styles */
.empty-state {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
}

.empty-icon {
    width: 60px;
    height: 60px;
    background: #f3f4f6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.empty-icon i {
    font-size: 1.5rem;
    color: #9ca3af;
}

.empty-title {
    color: #374151;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.empty-text {
    color: #6b7280;
    margin: 0;
}

/* Upload Section Styles */
.upload-section {
    margin-top: 1.5rem;
}

.upload-area {
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.upload-area:hover {
    border-color: #10b981;
    background: #f0fdf4;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
}

.upload-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.upload-icon {
    font-size: 2rem;
    color: #10b981;
    margin-bottom: 0.5rem;
}

.upload-title {
    color: #374151;
    font-weight: 600;
    margin: 0;
}

.upload-text {
    color: #6b7280;
    margin: 0;
    font-size: 0.9rem;
}

.upload-hint {
    color: #9ca3af;
    font-size: 0.8rem;
}

/* New Images Preview */
.new-images-preview {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.new-images-preview .image-item {
    animation: fadeInUp 0.3s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
        transform: scale(1);
    }
    to {
        opacity: 0;
        transform: scale(0.8);
    }
}

/* Legacy styles for compatibility */
.image-upload-box {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    transition: all 0.3s ease;
    min-height: 120px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.image-upload-box:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.image-preview {
    margin-top: 10px;
}

.image-preview img {
    max-width: 100%;
    max-height: 100px;
    border-radius: 4px;
}

.variant-item, .pricing-tier-item {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    border: 1px solid #dee2e6;
}

.variant-category-item {
    margin-bottom: 20px;
}

.variant-category-item .card {
    border: 2px solid #e3f2fd;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.variant-category-item .card-header {
    background-color: #dc3545 !important;
    border-bottom: 1px solid #bb2d3b;
}

.variant-option-item {
    background-color: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 10px;
    transition: all 0.2s ease;
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
    padding: 15px;
    margin-bottom: 15px;
}

.bg-light-primary {
    background-color: #d1ecf1 !important;
    border: 1px solid #bee5eb;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
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

/* Ultra compact card headers */
.card-header {
    padding: 0.375rem 0.75rem;
    min-height: 2.5rem;
}

.card-header h6 {
    font-size: 0.95rem;
    font-weight: 600;
    line-height: 1.2;
}

.card-header .btn-sm {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
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
    
    .card-header .btn-sm {
        padding: 0.2rem 0.4rem;
        font-size: 0.7rem;
    }
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

/* Increased spacing for variant sections */
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
</style>
@endpush

@push('scripts')
<script>
// Global counters
let variantCounter = {{ $variantCounter }};
let pricingTierCounter = {{ max($retailTierCounter, $wholesaleTierCounter) }};

// Debug: Log the variants data
console.log('Enhanced Product Variant Categories:', @json($enhancedProduct->variantCategories));
console.log('Variant Counter:', variantCounter);

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
        }, 1000);
    }, 500);
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
        }, 1000);
    }, 500);
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
        }, 1000);
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
    } else {
        customInput.classList.add('d-none');
        customInput.required = false;
        customInput.removeAttribute('name');
        select.setAttribute('name', `variants[${categoryIndex}][category]`);
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
            <div class="col-md-3">
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
            }, 500);
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

// Preview new images
function previewNewImages(input) {
    const previewContainer = document.getElementById('new-images-preview');
    previewContainer.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imageItem = document.createElement('div');
                    imageItem.className = 'image-item';
                    imageItem.innerHTML = `
                        <div class="image-container">
                            <img src="${e.target.result}" class="gallery-image" alt="New Image Preview">
                            <div class="image-overlay">
                                <div class="image-actions">
                                    <button type="button" class="btn btn-sm btn-light image-action-btn" 
                                            onclick="viewImage('${e.target.result}')" 
                                            title="View Full Size">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning image-action-btn" 
                                            onclick="removeNewImage(this)" 
                                            title="Remove Image">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="image-info">
                            <small class="text-muted">New Image ${index + 1}</small>
                        </div>
                    `;
                    previewContainer.appendChild(imageItem);
                };
                reader.readAsDataURL(file);
            }
        });
    }
}

// Remove new image from preview
function removeNewImage(button) {
    const imageItem = button.closest('.image-item');
    imageItem.style.animation = 'fadeOut 0.2s ease';
    setTimeout(() => {
        imageItem.remove();
    }, 200);
}

// Global variable to store current image ID for deletion
let currentImageId = null;

// Show modern delete modal
function showDeleteModal(imageId, imageSrc) {
    currentImageId = imageId;
    
    // Set the image preview in the modal
    const modalImagePreview = document.getElementById('modalImagePreview');
    modalImagePreview.innerHTML = `<img src="${imageSrc}" alt="Image to delete">`;
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('deleteImageModal'));
    modal.show();
}

// View image in full size
function viewImage(imageSrc) {
    // Create a simple lightbox effect
    const lightbox = document.createElement('div');
    lightbox.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        cursor: pointer;
    `;
    
    const img = document.createElement('img');
    img.src = imageSrc;
    img.style.cssText = `
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    `;
    
    lightbox.appendChild(img);
    document.body.appendChild(lightbox);
    
    // Close on click
    lightbox.addEventListener('click', () => {
        document.body.removeChild(lightbox);
    });
    
    // Close on escape key
    const handleEscape = (e) => {
        if (e.key === 'Escape') {
            document.body.removeChild(lightbox);
            document.removeEventListener('keydown', handleEscape);
        }
    };
    document.addEventListener('keydown', handleEscape);
}

// Delete existing image (called from modal)
function deleteImage() {
    if (!currentImageId) return;
    
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    const originalText = deleteBtn.innerHTML;
    
    // Show loading state
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Deleting...';
    
    fetch(`{{ url('/admin/enhanced-products/images') }}/${currentImageId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the image from the UI
            const imageItem = document.querySelector(`[data-image-id="${currentImageId}"]`);
            if (imageItem) {
                imageItem.style.animation = 'fadeOut 0.2s ease';
                setTimeout(() => {
                    imageItem.remove();
                    updateImageCount();
                }, 200);
            }
            
            // Hide modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteImageModal'));
            modal.hide();
            
            // Show success message
            showNotification('Image deleted successfully!', 'success');
        } else {
            showNotification('Failed to delete image: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while deleting the image.', 'error');
    })
    .finally(() => {
        // Reset button state
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = originalText;
        currentImageId = null;
    });
}

// Update image count badge
function updateImageCount() {
    const imageCount = document.querySelectorAll('.image-item').length;
    const badge = document.querySelector('.header-actions .badge');
    if (badge) {
        badge.textContent = `${imageCount} images`;
    }
}

// Show notification
function showNotification(message, type = 'success') {
    let alertClass = 'alert-success';
    let iconClass = 'fa-check-circle';
    if (type === 'error') { alertClass = 'alert-danger'; iconClass = 'fa-exclamation-circle'; }
    if (type === 'info') { alertClass = 'alert-info'; iconClass = 'fa-info-circle'; }
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alert.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    `;
    alert.innerHTML = `
        <i class="fas ${iconClass} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alert);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 3000);
}

// Emergency reset function (can be called from browser console)
function resetSubmitButton() {
    const submitBtn = document.getElementById('submit-btn');
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Update Product';
        showNotification('Submit button has been reset.', 'success');
    }
}

// Make reset function available globally
window.resetSubmitButton = resetSubmitButton;

// Initialize existing variants
function initializeExistingVariants() {
    const existingVariants = @json($enhancedProduct->variantCategories);
    console.log('Initializing existing variants:', existingVariants);
    
    if (existingVariants && Array.isArray(existingVariants)) {
        existingVariants.forEach((category, categoryIndex) => {
            const categoryCard = document.querySelector(`[data-category-index="${categoryIndex}"]`);
            if (categoryCard) {
                // Set the category type for proper handling
                categoryCard.setAttribute('data-category-type', category.category || '');
                
                // Handle custom categories
                if (category.category === 'Custom') {
                    const select = categoryCard.querySelector('.variant-category-select');
                    const customInput = categoryCard.querySelector('.custom-category-input');
                    if (select && customInput) {
                        select.classList.add('d-none');
                        customInput.classList.remove('d-none');
                        customInput.required = true;
                        customInput.value = category.category;
                    }
                }
            }
        });
    }
}

// Clean up empty variant entries before form submission
function cleanupVariants() {
    const variantInputs = document.querySelectorAll('input[name^="variants["]');
    const variantSelects = document.querySelectorAll('select[name^="variants["]');
    const variantTextareas = document.querySelectorAll('textarea[name^="variants["]');
    
    // Remove empty variant category items
    const variantCategories = document.querySelectorAll('.variant-category-item');
    variantCategories.forEach(categoryItem => {
        const categorySelect = categoryItem.querySelector('select[name*="[category]"]');
        const categoryInput = categoryItem.querySelector('input[name*="[category]"]');
        const categoryValue = categorySelect ? categorySelect.value : (categoryInput ? categoryInput.value : '');
        
        // If category is empty, remove the entire category item
        if (!categoryValue || categoryValue.trim() === '') {
            categoryItem.remove();
        }
    });
    
    // Remove empty variant option items
    const variantOptions = document.querySelectorAll('.variant-option-item');
    variantOptions.forEach(optionItem => {
        const nameInput = optionItem.querySelector('input[name*="[name]"]');
        const nameValue = nameInput ? nameInput.value : '';
        
        // If option name is empty, remove the option item
        if (!nameValue || nameValue.trim() === '') {
            optionItem.remove();
        }
    });
}

// Form submission handling
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('product-form');
    const submitBtn = document.getElementById('submit-btn');
    
    console.log('DOM loaded, form found:', !!form, 'submitBtn found:', !!submitBtn);
    
    // Initialize existing variants
    initializeExistingVariants();
    
    if (form && submitBtn) {
        // Store original button content
        const originalButtonContent = submitBtn.innerHTML;
        let isSubmitting = false;
        
        // Handle form submission
        form.addEventListener('submit', function(e) {
            console.log('Form submit event triggered');
            
            // Prevent double submission
            if (isSubmitting) {
                console.log('Already submitting, preventing double submission');
                e.preventDefault();
                return false;
            }
            
            // Clean up empty variant entries before submission
            cleanupVariants();
            
            console.log('Proceeding with submission (validation disabled for debugging)');
            
            // Ensure CSRF token is present
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            const tokenInput = form.querySelector('input[name="_token"]');
            if (csrfToken && tokenInput) {
                tokenInput.value = csrfToken.getAttribute('content');
                console.log('CSRF token set:', csrfToken.getAttribute('content'));
            } else {
                console.error('CSRF token not found!');
            }
            
            // Set submitting state
            isSubmitting = true;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
            
            console.log('Form submission started');
            
            // Auto-recover after 10 seconds if no response
            setTimeout(() => {
                if (isSubmitting) {
                    console.log('Auto-recovery triggered - no response after 10 seconds');
                    isSubmitting = false;
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalButtonContent;
                    showNotification('No response yet. The button was re-enabled; try again or check connection.', 'info');
                }
            }, 10000);
        });
        
        // Reset button state if page is being unloaded
        window.addEventListener('beforeunload', function() {
            if (isSubmitting) {
                isSubmitting = false;
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalButtonContent;
            }
        });
        
        // Reset button state if there's an error (page reload)
        window.addEventListener('load', function() {
            if (submitBtn.disabled) {
                isSubmitting = false;
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalButtonContent;
            }
        });
    }
    
    // Add event listener for modal delete button
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', deleteImage);
    }
});
</script>
@endpush