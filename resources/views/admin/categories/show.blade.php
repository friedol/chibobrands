@extends('layouts.admin')

@section('title', $category->name . ' - Category Details')
@section('description', 'View category details and products')

@push('styles')
<style>
    /* Compact card headers */
    .card-header {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    .card-header h5 {
        font-size: 0.95rem;
        margin-bottom: 0;
        font-weight: 600;
    }
    
    /* Compact page header */
    .page-title-section h2 {
        font-size: 1.25rem;
        margin-bottom: 0.25rem;
    }
    
    .page-title-section p {
        font-size: 0.85rem;
    }
    
    /* Compact category info */
    .category-info-row {
        font-size: 0.875rem;
        padding: 0.5rem 0;
    }
    
    .category-info-row strong {
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .category-info-row > div:first-child {
        color: #6c757d;
    }
    
    /* Ensure cards have equal height on left */
    .col-lg-4 .card {
        height: auto;
    }
    
    /* Make products table card full height */
    .col-lg-8 .card.h-100 {
        display: flex;
        flex-direction: column;
    }
    
    .col-lg-8 .card-body {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .table-responsive {
        flex: 1;
        min-height: 0;
    }
    
    /* Product table styling */
    .product-image-table {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border: 2px solid #e9ecef;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .product-image-table:hover {
        transform: scale(1.15);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        z-index: 10;
        position: relative;
    }
    
    .table {
        font-size: 0.875rem;
    }
    
    .table thead th {
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.75rem 0.5rem;
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
    
    .table tbody td {
        padding: 0.75rem 0.5rem;
        vertical-align: middle;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    /* Compact buttons */
    .btn-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    /* Compact badges */
    .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }
    
    /* Compact action buttons */
    .btn-group .btn {
        padding: 0.25rem 0.4rem;
    }
    
    @media (max-width: 768px) {
        .product-image-table {
            width: 50px;
            height: 50px;
        }
        
        .table th,
        .table td {
            padding: 0.5rem 0.25rem;
            
            font-size: 0.8rem;
        }
        
        .card-header {
            padding: 0.5rem 0.75rem;
        }
        
        .card-header h5 {
            font-size: 0.85rem;
        }
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 page-title-section">
    <div>
        <h2 class="h4 mb-1">{{ $category->name }}</h2>
        <p class="text-muted mb-0 small">Category details and products</p>
    </div>
    <div>
        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-warning btn-sm me-2">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<!-- Main Content Layout: Left Sidebar + Products -->
<div class="row mb-3">
    <!-- Left Side: Category Info & Quick Actions -->
    <div class="col-lg-4 col-xl-3 mb-3 mb-lg-0">
<!-- Category Information Card -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Category Information</h5>
            </div>
            <div class="card-body py-2">
                <div class="row category-info-row border-bottom">
                    <div class="col-12 mb-2">
                        <div class="d-flex align-items-center gap-2">
                        @if($category->image_path)
                                <img src="{{ asset('storage/'.$category->image_path) }}" alt="{{ $category->name }}" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                        @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                    <i class="fas fa-image text-muted" style="font-size:0.875rem;"></i>
                            </div>
                        @endif
                            <span class="fw-semibold">{{ $category->name }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="row category-info-row border-bottom">
                    <div class="col-4">
                        <strong>Description:</strong>
                    </div>
                    <div class="col-8">
                        @if($category->description)
                            <small>{{ Str::limit($category->description, 60) }}</small>
                        @else
                            <small class="text-muted">None</small>
                        @endif
                    </div>
                </div>
                
                <div class="row category-info-row border-bottom">
                    <div class="col-4">
                        <strong>Status:</strong>
                    </div>
                    <div class="col-8">
                        @if($category->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </div>
                </div>
                
                <div class="row category-info-row border-bottom">
                    <div class="col-4">
                        <strong>Products:</strong>
                    </div>
                    <div class="col-8">
                        <span class="badge bg-primary">{{ isset($products) ? $products->count() : 0 }}</span>
                    </div>
                </div>
                
                <div class="row category-info-row">
                    <div class="col-4">
                        <strong>Created:</strong>
                    </div>
                    <div class="col-8">
                        <small class="text-muted">{{ $category->created_at->format('M d, Y') }}</small>
                </div>
            </div>
        </div>
    </div>
    
        <!-- Quick Actions Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body py-2">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-warning btn-sm flex-fill">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    
                    <form action="{{ route('admin.categories.status.update', $category->slug) }}" method="POST" class="mb-0 flex-fill" id="statusForm" style="min-width: 0;">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="is_active" value="{{ $category->is_active ? '0' : '1' }}">
                        <button type="submit" class="btn btn-{{ $category->is_active ? 'secondary' : 'success' }} btn-sm w-100" id="statusBtn" data-no-global-handler>
                            <i class="fas fa-toggle-{{ $category->is_active ? 'off' : 'on' }} me-1"></i>
                            {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    
                    <form action="{{ route('admin.categories.destroy', $category->slug) }}" 
                          method="POST" 
                          class="mb-0 flex-fill"
                          id="deleteForm"
                          style="min-width: 0;"
                          onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm w-100" id="deleteBtn" data-no-global-handler>
                            <i class="fas fa-trash me-1"></i>Delete
                        </button>
                    </form>
            </div>
        </div>
    </div>
</div>

    <!-- Right Side: Products Table -->
    <div class="col-lg-8 col-xl-9">
        <div class="card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
                    <i class="fas fa-box me-2"></i>Products ({{ isset($products) ? $products->count() : 0 }})
        </h5>
                <a href="{{ route('admin.enhanced-products.create') }}?category={{ urlencode($category->name) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Add
        </a>
    </div>
    <div class="card-body p-0">
        @if(isset($products) && $products->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px;">Image</th>
                            <th>Product Name</th>
                            <th>Barcode</th>
                            <th style="width: 110px;">Retail</th>
                            <th style="width: 110px;">Wholesale</th>
                            <th style="width: 100px;">Stock</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 90px;">Actions</th>
                        </tr>
                        
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            @php
                                $mainImage = $product->images->first();
                                $retailPrice = $product->retail_base_price ?? $product->buying_price ?? 0;
                                $wholesalePrice = $product->b2b_base_price ?? $product->buying_price ?? 0;
                                $stockQty = $product->track_stock ? ($product->stock_quantity ?? 0) : null;
                            @endphp
                            <tr>
                                <td>
                                    @if($mainImage && !empty($mainImage->image_path))
                                        <img src="{{ asset('storage/' . $mainImage->image_path) }}?v={{ time() }}" 
                                             alt="{{ $product->name }}"
                                             class="product-image-table"
                                             onerror="this.onerror=null; this.src='{{ asset('images/default.webp') }}'; this.className='product-image-table';">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center shadow-sm product-image-table" 
                                             style="border: 2px solid #e9ecef;">
                                            <i class="fas fa-image text-muted fa-lg"></i>
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <strong class="d-block" style="font-size: 0.875rem;">{{ $product->name }}</strong>
                                    @if($product->description)
                                        <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">{{ Str::limit(strip_tags($product->description), 50) }}</small>
                                    @endif
                                </td>

                                <td>
                                    <code class="bg-light px-1 py-0 rounded" style="font-size: 0.75rem;">{{ $product->barcode }}</code>
                                </td>
                                <td>
                                    @if($retailPrice > 0)
                                        <strong class="text-success" style="font-size: 0.85rem;">{{ number_format($retailPrice, 0) }}</strong>
                                        <small class="text-muted d-block" style="font-size: 0.7rem;">TZS</small>
                                    @else
                                        <small class="text-muted">Not set</small>
                                    @endif
                                </td>
                                <td>
                                    @if($wholesalePrice > 0)
                                        <strong class="text-primary" style="font-size: 0.85rem;">{{ number_format($wholesalePrice, 0) }}</strong>
                                        <small class="text-muted d-block" style="font-size: 0.7rem;">TZS</small>
                                    @else
                                        <small class="text-muted">Not set</small>
                                    @endif
                                </td>
                                <td>
                                    @if($product->track_stock)
                                        @if($stockQty > 10)
                                            <span class="badge bg-success">{{ $stockQty }} {{ $product->stock_unit ?? 'pcs' }}</span>
                                        @elseif($stockQty > 0)
                                            <span class="badge bg-warning">{{ $stockQty }} {{ $product->stock_unit ?? 'pcs' }}</span>
                                        @else
                                            <span class="badge bg-danger">Out of Stock</span>
                                        @endif
                                    @else
                                        <span class="badge bg-info">Not Tracked</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                    @if($product->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                        <div class="d-flex gap-1">
                                            @if($product->retail_visible)
                                                <span class="badge bg-info" style="font-size: 0.65rem;">R</span>
                                            @endif
                                            @if($product->wholesale_visible)
                                                <span class="badge bg-warning" style="font-size: 0.65rem;">W</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.enhanced-products.show', $product->barcode) }}" 
                                       class="btn btn-sm btn-light" 
                                       title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                        <a href="{{ route('admin.enhanced-products.edit', $product->barcode) }}" 
                                       class="btn btn-sm btn-warning" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-box-open text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-2 mb-2" style="font-size: 1rem;">No Products Found</h5>
                <p class="text-muted mb-2" style="font-size: 0.875rem;">No products with category "{{ $category->name }}"</p>
                <a href="{{ route('admin.enhanced-products.create') }}?category={{ urlencode($category->name) }}" class="btn btn-primary btn-sm mt-2">
                    <i class="fas fa-plus me-1"></i>Add Product
                </a>
            </div>
        @endif
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div class="toast show" role="alert">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                {{ session('success') }}
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div class="toast show" role="alert">
            <div class="toast-header bg-danger text-white">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong class="me-auto">Error</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                {{ session('error') }}
            </div>
        </div>
    </div>
@endif

@if($errors->any())
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div class="toast show" role="alert">
            <div class="toast-header bg-danger text-white">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong class="me-auto">Validation Error</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle status update form
    const statusForm = document.getElementById('statusForm');
    const statusBtn = document.getElementById('statusBtn');
    
    if (statusForm && statusBtn) {
        const originalStatusText = statusBtn.innerHTML;
        let isSubmitting = false;
        
        statusForm.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                return false;
            }
            
            isSubmitting = true;
            statusBtn.disabled = true;
            statusBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            
            // Auto-recovery after 10 seconds
            setTimeout(() => {
                if (isSubmitting) {
                    isSubmitting = false;
                    statusBtn.disabled = false;
                    statusBtn.innerHTML = originalStatusText;
                }
            }, 10000);
        });
    }
    
    // Handle delete form
    const deleteForm = document.getElementById('deleteForm');
    const deleteBtn = document.getElementById('deleteBtn');
    
    if (deleteForm && deleteBtn) {
        const originalDeleteText = deleteBtn.innerHTML;
        let isDeleting = false;
        
        deleteForm.addEventListener('submit', function(e) {
            if (isDeleting) {
                e.preventDefault();
                return false;
            }
            
            // Confirm dialog is already handled by onsubmit attribute
            isDeleting = true;
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            
            // Auto-recovery after 10 seconds
            setTimeout(() => {
                if (isDeleting) {
                    isDeleting = false;
                    deleteBtn.disabled = false;
                    deleteBtn.innerHTML = originalDeleteText;
                }
            }, 10000);
        });
    }
});
</script>
@endpush
@endsection
