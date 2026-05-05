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
            <li class="breadcrumb-item active" aria-current="page">
                <i class="fas fa-box me-1"></i>Products
            </li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-1 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0 fw-semibold text-white">
                            <i class="fas fa-boxes me-1"></i>Products
                        </h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success btn-sm py-1 px-2" id="saveSortOrderBtn" style="display: none;">
                                <i class="fas fa-save me-1"></i>Save Sort Order
                            </button>
                            <button type="button" class="btn btn-warning btn-sm py-1 px-2" id="fixSortOrderBtn">
                                <i class="fas fa-sort-numeric-up me-1"></i>Fix Sort Order
                            </button>
                            <a href="{{ route('admin.enhanced-products.create') }}" class="btn btn-light btn-sm py-1 px-2">
                                <i class="fas fa-plus me-1"></i>Add
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 80px;">Sort Order</th>
                                    <th>Barcode</th>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Retail Price</th>
                                    <th>Wholesale Price</th>
                                    <th>Stock</th>
                                    <th>Offers</th>
                                    <th>Visibility</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $index => $product)
                                    <tr data-product-id="{{ $product->id }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <input type="number" 
                                                       class="form-control form-control-sm sort-order-input" 
                                                       value="{{ $product->sort_order ?? ($index + 1) }}" 
                                                       min="1" 
                                                       max="9999"
                                                       data-product-id="{{ $product->id }}"
                                                       data-original-value="{{ $product->sort_order ?? ($index + 1) }}"
                                                       style="width: 60px; text-align: center;">
                                            </div>
                                        </td>
                                        <td>
                                            <strong>{{ $product->barcode }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($product->images->count() > 0)
                                                    <div class="position-relative me-3">
                                                        <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" 
                                                             alt="{{ $product->name }}" 
                                                             class="img-thumbnail product-thumbnail" 
                                                             style="object-fit: cover; cursor: pointer; width: 60px; height: 60px;"
                                                             onmouseenter="showImagePreview(this, '{{ asset('storage/' . $product->images->first()->image_path) }}')"
                                                             onmouseleave="hideImagePreview()">
                                                    </div>
                                                @else
                                                    <div class="bg-light d-flex align-items-center justify-content-center me-3 product-thumbnail" 
                                                         style="width: 60px; height: 60px; border: 1px solid #dee2e6; border-radius: 0.375rem;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <strong class="d-block">{{ $product->name }}</strong>
                                                    <small class="text-muted">{{ $product->barcode }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>{{ $product->category ?? 'N/A' }}</strong>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                @php
                                                    $retailPrice = (float) ($product->retail_base_price ?? $product->base_price ?? 0);
                                                    $basePrice = (float) ($product->base_price ?? 0);
                                                    
                                                    // Get real offer data from database
                                                    $retailOffer = $product->offers->where('target_channel', 'retail')->first() ?? 
                                                                  $product->offers->where('target_channel', 'both')->first();
                                                    $retailDiscountAmount = 0;
                                                    $retailFinalPrice = $retailPrice;
                                                    
                                                    if ($retailOffer) {
                                                        if ($retailOffer->offer_type === 'percentage') {
                                                            $retailDiscountAmount = ($retailPrice * $retailOffer->discount_value) / 100;
                                                        } elseif ($retailOffer->offer_type === 'fixed') {
                                                            $retailDiscountAmount = $retailOffer->discount_value;
                                                        }
                                                        $retailFinalPrice = $retailPrice - $retailDiscountAmount;
                                                    }
                                                @endphp
                                                
                                                <div class="fw-bold text-primary">
                                                    {{ number_format($retailFinalPrice) }} TZS
                                                </div>
                                                
                                                @if($retailDiscountAmount > 0)
                                                    <div class="text-success small">
                                                        <i class="fas fa-tag me-1"></i>Save {{ number_format($retailDiscountAmount) }} TZS
                                                    </div>
                                                    <div class="text-muted small text-decoration-line-through">
                                                        {{ number_format($retailPrice) }} TZS
                                                    </div>
                                                @else
                                                    @if($retailPrice != $basePrice)
                                                        <small class="text-muted">
                                                            Base: {{ number_format($basePrice) }} TZS
                                                        </small>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                @php
                                                    $wholesalePrice = (float) ($product->b2b_base_price ?? $product->base_price ?? 0);
                                                    
                                                    // Get real offer data from database
                                                    $wholesaleOffer = $product->offers->where('target_channel', 'wholesale')->first() ?? 
                                                                     $product->offers->where('target_channel', 'both')->first();
                                                    $wholesaleDiscountAmount = 0;
                                                    $wholesaleFinalPrice = $wholesalePrice;
                                                    
                                                    if ($wholesaleOffer) {
                                                        if ($wholesaleOffer->offer_type === 'percentage') {
                                                            $wholesaleDiscountAmount = ($wholesalePrice * $wholesaleOffer->discount_value) / 100;
                                                        } elseif ($wholesaleOffer->offer_type === 'fixed') {
                                                            $wholesaleDiscountAmount = $wholesaleOffer->discount_value;
                                                        }
                                                        $wholesaleFinalPrice = $wholesalePrice - $wholesaleDiscountAmount;
                                                    }
                                                @endphp
                                                
                                                <div class="fw-bold text-success">
                                                    {{ number_format($wholesaleFinalPrice) }} TZS
                                                </div>
                                                
                                                @if($wholesaleDiscountAmount > 0)
                                                    <div class="text-success small">
                                                        <i class="fas fa-tag me-1"></i>Save {{ number_format($wholesaleDiscountAmount) }} TZS
                                                    </div>
                                                    <div class="text-muted small text-decoration-line-through">
                                                        {{ number_format($wholesalePrice) }} TZS
                                                    </div>
                                                @else
                                                    @if($wholesalePrice != $basePrice)
                                                        <small class="text-muted">
                                                            Base: {{ number_format($basePrice) }} TZS
                                                        </small>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $product->stock_quantity > 0 ? 'bg-success' : 'bg-danger' }}">
                                                {{ number_format($product->stock_quantity) }} {{ $product->stock_unit ?? 'pcs' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                @php
                                                    // Get real offer data from database
                                                    $retailOffer = $product->offers->where('target_channel', 'retail')->first() ?? 
                                                                  $product->offers->where('target_channel', 'both')->first();
                                                    $wholesaleOffer = $product->offers->where('target_channel', 'wholesale')->first() ?? 
                                                                     $product->offers->where('target_channel', 'both')->first();
                                                @endphp
                                                
                                                @if($retailOffer)
                                                    <div class="offer-badge retail mb-1">
                                                        <span class="badge bg-warning small">
                                                            <i class="fas fa-store me-1"></i>
                                                            @if($retailOffer->offer_type === 'percentage')
                                                                {{ $retailOffer->discount_value }}% Off
                                                            @else
                                                                {{ number_format($retailOffer->discount_value) }} TZS Off
                                                            @endif
                                                        </span>
                                                    </div>
                                                @endif
                                                
                                                @if($wholesaleOffer)
                                                    <div class="offer-badge wholesale mb-1">
                                                        <span class="badge bg-info small">
                                                            <i class="fas fa-building me-1"></i>
                                                            @if($wholesaleOffer->offer_type === 'percentage')
                                                                {{ $wholesaleOffer->discount_value }}% Off
                                                            @else
                                                                {{ number_format($wholesaleOffer->discount_value) }} TZS Off
                                                            @endif
                                                        </span>
                                                    </div>
                                                @endif
                                                
                                                @if(!$retailOffer && !$wholesaleOffer)
                                                    <span class="text-muted small">
                                                        <i class="fas fa-minus-circle me-1"></i>No Offers
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-2">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" 
                                                           id="retail_{{ $product->barcode }}" 
                                                           {{ $product->retail_visible ? 'checked' : '' }}
                                                           onchange="toggleVisibility('{{ $product->barcode }}', 'retail', this.checked)">
                                                    <label class="form-check-label" for="retail_{{ $product->barcode }}">
                                                        <small>Retail</small>
                                                    </label>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" 
                                                           id="wholesale_{{ $product->barcode }}" 
                                                           {{ $product->wholesale_visible ? 'checked' : '' }}
                                                           onchange="toggleVisibility('{{ $product->barcode }}', 'wholesale', this.checked)">
                                                    <label class="form-check-label" for="wholesale_{{ $product->barcode }}">
                                                        <small>Wholesale</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group" style="gap:0;">
                                                <a href="{{ route('admin.enhanced-products.show', $product->barcode) }}" class="btn btn-info text-white" title="View">
                                                    <i class="fas fa-eye" style="font-size: 0.6rem;"></i>
                                                </a>
                                                <a href="{{ route('admin.enhanced-products.edit', $product->barcode) }}" class="btn btn-warning text-white" title="Edit">
                                                    <i class="fas fa-edit" style="font-size: 0.6rem;"></i>
                                                </a>
                                                <a href="{{ route('admin.enhanced-products.offers.product', $product->barcode) }}" 
                                                   class="btn btn-success text-white" title="Manage Offers">
                                                    <i class="fas fa-percentage" style="font-size: 0.6rem;"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger text-white" title="Delete" onclick="deleteProduct('{{ $product->barcode }}', '{{ $product->name }}')">
                                                    <i class="fas fa-trash" style="font-size: 0.6rem;"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">
                                            <div class="text-muted empty-state">
                                                <i class="fas fa-box-open mb-3"></i>
                                                <h5>No products found</h5>
                                                <p>Start by adding your first product.</p>
                                                <a href="{{ route('admin.enhanced-products.create') }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-plus"></i> Add Product
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($products->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top bg-light px-3 py-3">
                            <div class="text-muted small fw-medium">
                                <i class="fas fa-info-circle me-1"></i>
                                Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products
                            </div>
                            <div class="pagination-wrapper">
                                {{ $products->appends(request()->query())->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
// Sort order management
let sortOrderChanged = false;

document.addEventListener('DOMContentLoaded', function() {
    const sortOrderInputs = document.querySelectorAll('.sort-order-input');
    const saveSortOrderBtn = document.getElementById('saveSortOrderBtn');
    
    // Load saved sort order values from localStorage
    sortOrderInputs.forEach(input => {
        const productId = input.dataset.productId;
        const savedValue = localStorage.getItem(`sort_order_${productId}`);
        if (savedValue !== null) {
            input.value = savedValue;
            console.log('Loaded saved sort order for product', productId, ':', savedValue);
            // Add visual indicator for unsaved changes
            input.style.backgroundColor = '#fff3cd';
            input.style.borderColor = '#ffc107';
        }
    });
    
    // Function to handle sort order changes
    function handleSortOrderChange(changedInput) {
        const newValue = parseInt(changedInput.value);
        const productId = changedInput.dataset.productId;
        
        if (isNaN(newValue) || newValue < 1) {
            changedInput.value = changedInput.dataset.originalValue;
            return;
        }
        
        // Update the original value for the changed input
        changedInput.dataset.originalValue = newValue;
        
        // Save to localStorage
        localStorage.setItem(`sort_order_${productId}`, newValue);
        
        // Add visual indicator for unsaved changes
        changedInput.style.backgroundColor = '#fff3cd';
        changedInput.style.borderColor = '#ffc107';
        
        sortOrderChanged = true;
        saveSortOrderBtn.style.display = 'inline-block';
        
        console.log('Sort order changed for product', productId, 'to', newValue);
    }
    
    // Show save button when sort order changes
    sortOrderInputs.forEach(input => {
        input.addEventListener('change', function() {
            handleSortOrderChange(this);
        });
        
        // Also listen for input events to catch typing
        input.addEventListener('input', function() {
            handleSortOrderChange(this);
        });
    });
    
    // Fix sort order button
    document.getElementById('fixSortOrderBtn').addEventListener('click', function() {
        if (!confirm('This will renumber all products sequentially starting from 1. Continue?')) {
            return;
        }
        
        // Show loading state
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Fixing...';
        this.disabled = true;
        
        fetch('{{ route("admin.enhanced-products.fix-sort-order") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Sort order fixed successfully! Reloading page...', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert('Failed to fix sort order: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to fix sort order. Please try again.', 'error');
        })
        .finally(() => {
            // Reset button state
            this.innerHTML = '<i class="fas fa-sort-numeric-up me-1"></i>Fix Sort Order';
            this.disabled = false;
        });
    });
    
    // Save sort order
    saveSortOrderBtn.addEventListener('click', function() {
        if (!sortOrderChanged) return;
        
        const sortOrders = [];
        sortOrderInputs.forEach(input => {
            sortOrders.push({
                product_id: input.dataset.productId,
                sort_order: parseInt(input.value) || 0
            });
        });
        
        // Check for duplicates and warn user
        const sortValues = sortOrders.map(item => item.sort_order);
        const duplicates = sortValues.filter((value, index) => sortValues.indexOf(value) !== index);
        
        if (duplicates.length > 0) {
            if (!confirm('Duplicate sort order values detected: ' + [...new Set(duplicates)].join(', ') + '. The system will automatically assign sequential numbers (1, 2, 3, etc.) based on your current order. Continue?')) {
                return;
            }
        }
        
        console.log('Saving sort orders:', sortOrders);
        
        // Show loading state
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
        this.disabled = true;
        
        fetch('{{ route("admin.enhanced-products.update-sort-order") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ sort_orders: sortOrders })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Save response:', data);
            if (data.success) {
                // Show success message
                const message = duplicates.length > 0 ? 
                    'Sort order updated successfully! Duplicates were automatically resolved with sequential numbering.' : 
                    'Sort order updated successfully!';
                showAlert(message, 'success');
                sortOrderChanged = false;
                this.style.display = 'none';
                
                // Update the display with corrected sort orders
                if (data.updated_orders) {
                    data.updated_orders.forEach(updatedOrder => {
                        const input = document.querySelector(`input[data-product-id="${updatedOrder.product_id}"]`);
                        if (input) {
                            input.value = updatedOrder.sort_order;
                            input.dataset.originalValue = updatedOrder.sort_order;
                            // Clear visual indicators
                            input.style.backgroundColor = '';
                            input.style.borderColor = '';
                        }
                    });
                }
                
                // Clear localStorage after successful save
                sortOrderInputs.forEach(input => {
                    localStorage.removeItem(`sort_order_${input.dataset.productId}`);
                });
                
                // Reload page after a short delay to ensure everything is synced
                setTimeout(() => {
                    // Clear any cached data
                    if ('caches' in window) {
                        caches.keys().then(names => {
                            names.forEach(name => {
                                caches.delete(name);
                            });
                        });
                    }
                    
                    // Add cache-busting parameter to force fresh load
                    const url = new URL(window.location);
                    url.searchParams.set('_t', Date.now());
                    window.location.href = url.toString();
                }, 1000);
            } else {
                let errorMessage = 'Failed to update sort order: ' + (data.message || 'Unknown error');
                if (data.duplicates && data.duplicates.length > 0) {
                    errorMessage += '\nDuplicate values: ' + data.duplicates.join(', ');
                }
                showAlert(errorMessage, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to update sort order. Please try again.', 'error');
        })
        .finally(() => {
            // Reset button state
            this.innerHTML = '<i class="fas fa-save me-1"></i>Save Sort Order';
            this.disabled = false;
        });
    });
    
    // Warn user about unsaved changes when navigating away
    window.addEventListener('beforeunload', function(e) {
        if (sortOrderChanged) {
            e.preventDefault();
            e.returnValue = 'You have unsaved sort order changes. Are you sure you want to leave?';
            return e.returnValue;
        }
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

function toggleVisibility(barcode, type, isVisible) {
    fetch(`/admin/enhanced-products/${barcode}/toggle-visibility`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            type: type,
            visible: isVisible
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Optional: Show success message
            console.log(`${type} visibility updated successfully`);
        } else {
            // Revert the switch if update failed
            document.getElementById(`${type}_${barcode}`).checked = !isVisible;
            alert('Failed to update visibility. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Revert the switch if update failed
        document.getElementById(`${type}_${barcode}`).checked = !isVisible;
        alert('Failed to update visibility. Please try again.');
    });
}

// Custom image preview functions
function showImagePreview(element, imageSrc) {
    // Remove any existing preview
    hideImagePreview();
    
    // Create preview element
    const preview = document.createElement('div');
    preview.id = 'image-preview';
    preview.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.8);
        z-index: 9999;
        background: white;
        border-radius: 12px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        padding: 8px;
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        pointer-events: none;
    `;
    
    // Create image
    const img = document.createElement('img');
    img.src = imageSrc;
    img.style.cssText = `
        width: 300px;
        height: 300px;
        object-fit: cover;
        border-radius: 12px;
        display: block;
        box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    `;
    
    preview.appendChild(img);
    document.body.appendChild(preview);
    
    // Animate in
    setTimeout(() => {
        preview.style.opacity = '1';
        preview.style.transform = 'translate(-50%, -50%) scale(1)';
    }, 10);
}

function hideImagePreview() {
    const preview = document.getElementById('image-preview');
    if (preview) {
        preview.style.opacity = '0';
        preview.style.transform = 'translate(-50%, -50%) scale(0.8)';
        setTimeout(() => {
            if (preview.parentNode) {
                preview.parentNode.removeChild(preview);
            }
        }, 300);
    }
}

function deleteProduct(barcode, productName) {
    console.log('Delete product called:', barcode, productName);
    
    if (!confirm(`Are you sure you want to delete "${productName}"?\n\nThis action cannot be undone.`)) {
        console.log('User cancelled deletion');
        return;
    }
    
    console.log('User confirmed deletion, submitting...');
    
    // Create a form and submit it
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/enhanced-products/${barcode}`;
    form.style.display = 'none';
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);
    
    // Add method override
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);
    
    // Add to body and submit
    document.body.appendChild(form);
    console.log('Submitting delete form...');
    form.submit();
}

function confirmDelete(productName) {
    console.log('Delete confirmation for:', productName);
    const confirmed = confirm(`Are you sure you want to delete "${productName}"?\n\nThis action cannot be undone.`);
    console.log('User confirmed:', confirmed);
    return confirmed;
}

</script>
@endpush

@push('styles')

@endpush
