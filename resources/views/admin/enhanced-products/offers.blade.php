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
                <i class="fas fa-percentage me-1"></i>Manage Offers
            </li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0 fw-semibold text-white">
                            <i class="fas fa-percentage me-1"></i>Product Offers Management
                        </h6>
                        <button type="button" class="btn btn-light btn-sm py-1 px-2" id="createOfferBtn">
                            <i class="fas fa-plus me-1"></i>Create Offer
                        </button>
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

                    <!-- Product Selection -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Select Product</label>
                            <select class="form-select form-select-sm" id="productSelect">
                                <option value="">Choose a product to manage offers</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->barcode }}" 
                                            data-retail-price="{{ $product->retail_base_price ?? $product->base_price ?? 0 }}"
                                            data-wholesale-price="{{ $product->b2b_base_price ?? $product->base_price ?? 0 }}"
                                            data-product-name="{{ $product->name }}">
                                        {{ $product->name }} ({{ $product->barcode }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Search Products</label>
                            <input type="text" class="form-control form-control-sm" id="productSearch" placeholder="Search by name or barcode...">
                        </div>
                    </div>

                    <!-- Selected Product Info -->
                    <div id="selectedProductInfo" class="row mb-4" style="display: none;">
                        <div class="col-12">
                            <div class="product-info-container">
                                <div class="product-info-header">
                                    <h6 class="mb-0 small">
                                        <i class="fas fa-box me-1"></i>Selected Product Information
                                    </h6>
                                </div>      
                                <div class="product-info-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <div class="fw-bold text-primary" id="selectedProductName">-</div>
                                                <small class="text-muted" id="selectedProductBarcode">-</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <div class="fw-bold text-primary" id="selectedRetailPrice">-</div>
                                                <small class="text-muted">Retail Price</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <div class="fw-bold text-success" id="selectedWholesalePrice">-</div>
                                                <small class="text-muted">Wholesale Price</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Offers Display -->
                    <div id="currentOffersSection" style="display: none;">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="offers-container">
                                    <div class="offers-header retail-header">
                                        <h6 class="mb-0 small">
                                            <i class="fas fa-store me-1"></i>Retail Offers
                                        </h6>
                                    </div>
                                    <div class="offers-body">
                                        <div id="retailOffersList">
                                            <p class="text-muted small mb-0">No active retail offers</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="offers-container">
                                    <div class="offers-header wholesale-header">
                                        <h6 class="mb-0 small">
                                            <i class="fas fa-building me-1"></i>Wholesale Offers
                                        </h6>
                                    </div>
                                    <div class="offers-body">
                                        <div id="wholesaleOffersList">
                                            <p class="text-muted small mb-0">No active wholesale offers</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Offer Creation Form -->
                    <div id="offerFormSection" style="display: none;">
                        <div class="offer-form-container">
                            <div class="offer-form-header">
                                <h6 class="mb-0 small">
                                    <i class="fas fa-plus-circle me-1"></i>Create New Offer
                                </h6>
                            </div>
                            <div class="offer-form-body">
                                <form id="offerForm" method="POST" action="#">
                                    @csrf
                                    
                                    <!-- Offer Type Selection -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">Offer Type</label>
                                            <select class="form-select form-select-sm" name="offer_type" id="offerType">
                                                <option value="">Select Offer Type</option>
                                                <option value="percentage">Percentage Discount</option>
                                                <option value="fixed">Fixed Amount Discount</option>
                                                <option value="buy_x_get_y">Buy X Get Y</option>
                                                <option value="bulk_discount">Bulk Discount</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">Target Channel</label>
                                            <select class="form-select form-select-sm" name="target_channel" id="targetChannel">
                                                <option value="both">Both Retail & Wholesale</option>
                                                <option value="retail">Retail Only</option>
                                                <option value="wholesale">Wholesale Only</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Dynamic Offer Fields -->
                                    <div id="offerFields">
                                        <p class="text-muted small">Please select an offer type to configure the offer details.</p>
                                    </div>

                                    <!-- Real-time Pricing Preview -->
                                    <div class="row mb-3" id="pricingPreview" style="display: none;">
                                        <div class="col-12">
                                            <div class="pricing-preview-container">
                                                <div class="pricing-preview-header">
                                                    <h6 class="mb-0 small">
                                                        <i class="fas fa-calculator me-1"></i>Pricing Preview
                                                    </h6>
                                                </div>
                                                <div class="pricing-preview-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="pricing-preview-item">
                                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                                    <span class="small fw-semibold text-primary">Retail Price:</span>
                                                                    <span class="small" id="retailOriginal">-</span>
                                                                </div>
                                                                <div class="d-flex justify-content-between align-items-center mb-1" id="retailDiscount" style="display: none;">
                                                                    <span class="small fw-semibold text-success">Discount:</span>
                                                                    <span class="small text-success" id="retailDiscountAmount">0 TZS</span>
                                                                </div>
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span class="small fw-semibold text-primary">Final Price:</span>
                                                                    <span class="small fw-bold text-primary" id="retailFinal">-</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="pricing-preview-item">
                                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                                    <span class="small fw-semibold text-success">Wholesale Price:</span>
                                                                    <span class="small" id="wholesaleOriginal">-</span>
                                                                </div>
                                                                <div class="d-flex justify-content-between align-items-center mb-1" id="wholesaleDiscount" style="display: none;">
                                                                    <span class="small fw-semibold text-success">Discount:</span>
                                                                    <span class="small text-success" id="wholesaleDiscountAmount">0 TZS</span>
                                                                </div>
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span class="small fw-semibold text-success">Final Price:</span>
                                                                    <span class="small fw-bold text-success" id="wholesaleFinal">-</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Validity Period -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">Start Date</label>
                                            <input type="date" class="form-control form-control-sm" 
                                                   name="start_date" id="startDate">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">End Date</label>
                                            <input type="date" class="form-control form-control-sm" 
                                                   name="end_date" id="endDate">
                                        </div>
                                    </div>

                                    <!-- Offer Description -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small">Offer Description</label>
                                        <textarea class="form-control form-control-sm" name="description" 
                                                  rows="3" placeholder="Enter offer description (optional)"></textarea>
                                    </div>

                                    <!-- Active Status -->
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                               id="isActive" name="is_active" value="1" checked>
                                        <label class="form-check-label fw-semibold small" for="isActive">
                                            Activate this offer immediately
                                        </label>
                                    </div>

                                    <!-- Form Actions -->
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-secondary btn-sm" id="cancelOffer">
                                            <i class="fas fa-times me-1"></i>Cancel
                                        </button>
                                        <button type="submit" class="btn btn-success btn-sm" id="saveOffer">
                                            <i class="fas fa-save me-1"></i>Save Offer
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('productSelect');
    const productSearch = document.getElementById('productSearch');
    const selectedProductInfo = document.getElementById('selectedProductInfo');
    const currentOffersSection = document.getElementById('currentOffersSection');
    const offerFormSection = document.getElementById('offerFormSection');
    const offerType = document.getElementById('offerType');
    const targetChannel = document.getElementById('targetChannel');
    const offerFields = document.getElementById('offerFields');
    const pricingPreview = document.getElementById('pricingPreview');
    
    let selectedProduct = null;
    
    // Product selection handler
    productSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            selectedProduct = {
                barcode: selectedOption.value,
                name: selectedOption.dataset.productName,
                retailPrice: parseFloat(selectedOption.dataset.retailPrice),
                wholesalePrice: parseFloat(selectedOption.dataset.wholesalePrice)
            };
            
            // Update selected product info
            document.getElementById('selectedProductName').textContent = selectedProduct.name;
            document.getElementById('selectedProductBarcode').textContent = selectedProduct.barcode;
            document.getElementById('selectedRetailPrice').textContent = selectedProduct.retailPrice.toLocaleString() + ' TZS';
            document.getElementById('selectedWholesalePrice').textContent = selectedProduct.wholesalePrice.toLocaleString() + ' TZS';
            
            // Show sections
            selectedProductInfo.style.display = 'block';
            currentOffersSection.style.display = 'block';
            offerFormSection.style.display = 'block';
            
            // Load current offers for this product
            loadProductOffers();
        } else {
            selectedProduct = null;
            selectedProductInfo.style.display = 'none';
            currentOffersSection.style.display = 'none';
            offerFormSection.style.display = 'none';
        }
    });
    
    // Check if a specific product was pre-selected server-side
    @if($selectedProduct)
        // Pre-select the product from server-side
        const preSelectedProduct = '{{ $selectedProduct->barcode }}';
        const productOption = Array.from(productSelect.options).find(option => option.value === preSelectedProduct);
        if (productOption) {
            productSelect.value = preSelectedProduct;
            productSelect.dispatchEvent(new Event('change'));
            // Load offers for the pre-selected product
            loadProductOffers();
        }
    @else
        // Check if a specific product was requested via URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const requestedProduct = urlParams.get('product');
        if (requestedProduct) {
            // Find and select the requested product
            const productOption = Array.from(productSelect.options).find(option => option.value === requestedProduct);
            if (productOption) {
                productSelect.value = requestedProduct;
                productSelect.dispatchEvent(new Event('change'));
            }
        }
    @endif
    
    // Product search handler
    productSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const options = productSelect.options;
        
        for (let i = 1; i < options.length; i++) {
            const option = options[i];
            const text = option.textContent.toLowerCase();
            option.style.display = text.includes(searchTerm) ? 'block' : 'none';
        }
    });
    
    // Offer type change handler
    offerType.addEventListener('change', function() {
        window.updateOfferFields(this.value);
    });
    
    // Target channel change handler
    targetChannel.addEventListener('change', function() {
        window.updatePricingPreview();
    });
    
    // Cancel offer handler
    document.getElementById('cancelOffer').addEventListener('click', function() {
        resetOfferForm();
    });
    
    // Form submission handler
    document.getElementById('offerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Form submit event triggered');
        handleOfferSubmit();
    });
    
    // Direct button click handler (backup)
    document.getElementById('saveOffer').addEventListener('click', function(e) {
        e.preventDefault();
        console.log('Save button clicked directly');
        handleOfferSubmit();
    });
    
    // Create offer button handler
    document.getElementById('createOfferBtn').addEventListener('click', function() {
        if (!selectedProduct) {
            alert('Please select a product first to create an offer.');
            return;
        }
        
        // Scroll to the offer form section
        document.getElementById('offerFormSection').scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
        
        // Focus on the offer type select
        document.getElementById('offerType').focus();
    });
    
    // Load current offers for a product
    function loadCurrentOffers(barcode) {
        // This would typically make an AJAX call to load current offers
        // For now, we'll show placeholder content
        document.getElementById('retailOffersList').innerHTML = '<p class="text-muted small mb-0">No active retail offers</p>';
        document.getElementById('wholesaleOffersList').innerHTML = '<p class="text-muted small mb-0">No active wholesale offers</p>';
    }
    
    // Update offer fields based on selected type
    window.updateOfferFields = function(offerType) {
        let fieldsHTML = '';
        
        if (offerType) {
            pricingPreview.style.display = 'block';
            // Update pricing preview with current values
            setTimeout(() => {
                window.updatePricingPreview();
            }, 100);
        } else {
            pricingPreview.style.display = 'none';
        }
        
        switch(offerType) {
            case 'percentage':
                fieldsHTML = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Discount Percentage</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control" name="discount_value" 
                                       min="1" max="100" placeholder="10" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Minimum Quantity</label>
                            <input type="number" class="form-control form-control-sm" name="min_quantity" 
                                   min="1" value="1" placeholder="1">
                        </div>
                    </div>
                `;
                break;
                
            case 'fixed':
                fieldsHTML = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Discount Amount (TZS)</label>
                            <input type="number" class="form-control form-control-sm" name="discount_value" 
                                   min="1" placeholder="5000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Minimum Quantity</label>
                            <input type="number" class="form-control form-control-sm" name="min_quantity" 
                                   min="1" value="1" placeholder="1">
                        </div>
                    </div>
                `;
                break;
                
            case 'buy_x_get_y':
                fieldsHTML = `
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Buy Quantity</label>
                            <input type="number" class="form-control form-control-sm" name="buy_quantity" 
                                   min="1" placeholder="2" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Get Quantity</label>
                            <input type="number" class="form-control form-control-sm" name="get_quantity" 
                                   min="1" placeholder="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Discount % on Free Items</label>
                            <input type="number" class="form-control form-control-sm" name="discount_value" 
                                   min="0" max="100" value="100" placeholder="100">
                        </div>
                    </div>
                `;
                break;
                
            case 'bulk_discount':
                fieldsHTML = `
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Minimum Quantity</label>
                            <input type="number" class="form-control form-control-sm" name="min_quantity" 
                                   min="1" placeholder="10" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Discount Percentage</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control" name="discount_value" 
                                       min="1" max="100" placeholder="15" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Maximum Quantity</label>
                            <input type="number" class="form-control form-control-sm" name="max_quantity" 
                                   min="1" placeholder="100">
                        </div>
                    </div>
                `;
                break;
                
            default:
                fieldsHTML = '<p class="text-muted small">Please select an offer type to configure the offer details.</p>';
        }
        
        offerFields.innerHTML = fieldsHTML;
        
        // Ensure the dynamic fields are properly associated with the form
        const form = document.getElementById('offerForm');
        const dynamicFields = offerFields.querySelectorAll('input, select, textarea');
        dynamicFields.forEach(field => {
            // Make sure the field is properly associated with the form
            if (!field.form) {
                field.setAttribute('form', 'offerForm');
            }
        });
        
        // Add event listeners to discount value inputs
        const discountInputs = offerFields.querySelectorAll('input[name="discount_value"]');
        discountInputs.forEach(input => {
            input.addEventListener('input', window.updatePricingPreview);
        });
    }
    
    // Update pricing preview
    window.updatePricingPreview = function() {
        if (!selectedProduct) return;
        
        const offerType = document.getElementById('offerType').value;
        const targetChannel = document.getElementById('targetChannel').value;
        const discountValue = document.querySelector('input[name="discount_value"]')?.value;
        
        // Always show original prices
        document.getElementById('retailOriginal').textContent = selectedProduct.retailPrice.toLocaleString() + ' TZS';
        document.getElementById('wholesaleOriginal').textContent = selectedProduct.wholesalePrice.toLocaleString() + ' TZS';
        
        if (!offerType || !discountValue) {
            // Show original prices without discount
            document.getElementById('retailFinal').textContent = selectedProduct.retailPrice.toLocaleString() + ' TZS';
            document.getElementById('wholesaleFinal').textContent = selectedProduct.wholesalePrice.toLocaleString() + ' TZS';
            document.getElementById('retailDiscount').style.display = 'none';
            document.getElementById('wholesaleDiscount').style.display = 'none';
            return;
        }
        
        const discount = parseFloat(discountValue);
        let retailDiscount = 0;
        let wholesaleDiscount = 0;
        
        // Calculate discounts based on offer type and target channel
        if (targetChannel === 'retail' || targetChannel === 'both') {
            if (offerType === 'percentage') {
                retailDiscount = (selectedProduct.retailPrice * discount) / 100;
            } else {
                retailDiscount = discount;
            }
        }
        
        if (targetChannel === 'wholesale' || targetChannel === 'both') {
            if (offerType === 'percentage') {
                wholesaleDiscount = (selectedProduct.wholesalePrice * discount) / 100;
            } else {
                wholesaleDiscount = discount;
            }
        }
        
        // Update retail pricing display
        document.getElementById('retailOriginal').textContent = selectedProduct.retailPrice.toLocaleString() + ' TZS';
        const retailDiscountElement = document.getElementById('retailDiscount');
        const retailDiscountAmount = document.getElementById('retailDiscountAmount');
        const retailFinal = document.getElementById('retailFinal');
        
        if (retailDiscount > 0) {
            retailDiscountElement.style.display = 'flex';
            retailDiscountAmount.textContent = retailDiscount.toLocaleString() + ' TZS';
            retailFinal.textContent = (selectedProduct.retailPrice - retailDiscount).toLocaleString() + ' TZS';
        } else {
            retailDiscountElement.style.display = 'none';
            retailFinal.textContent = selectedProduct.retailPrice.toLocaleString() + ' TZS';
        }
        
        // Update wholesale pricing display
        document.getElementById('wholesaleOriginal').textContent = selectedProduct.wholesalePrice.toLocaleString() + ' TZS';
        const wholesaleDiscountElement = document.getElementById('wholesaleDiscount');
        const wholesaleDiscountAmount = document.getElementById('wholesaleDiscountAmount');
        const wholesaleFinal = document.getElementById('wholesaleFinal');
        
        if (wholesaleDiscount > 0) {
            wholesaleDiscountElement.style.display = 'flex';
            wholesaleDiscountAmount.textContent = wholesaleDiscount.toLocaleString() + ' TZS';
            wholesaleFinal.textContent = (selectedProduct.wholesalePrice - wholesaleDiscount).toLocaleString() + ' TZS';
        } else {
            wholesaleDiscountElement.style.display = 'none';
            wholesaleFinal.textContent = selectedProduct.wholesalePrice.toLocaleString() + ' TZS';
        }
    }
    
    // Handle offer form submission
    function handleOfferSubmit() {
        console.log('=== OFFER SUBMISSION STARTED ===');
        console.log('Selected product:', selectedProduct);
        
        // Simple test to see if function is called
        console.log('Form submission function called successfully');
        
        if (!selectedProduct) {
            console.log('No product selected - showing warning');
            showNotification('Please select a product first.', 'warning');
            return;
        }
        
        // Show loading state
        const saveBtn = document.getElementById('saveOffer');
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
        saveBtn.disabled = true;
        
        // Fallback timeout to reset button (10 seconds)
        const timeoutId = setTimeout(() => {
            console.log('Timeout fallback - resetting button');
            resetSaveButton(saveBtn, originalText);
        }, 10000);
        
        try {
            const form = document.getElementById('offerForm');
            const formData = new FormData(form);
            
            // Debug: Check if discount_value field exists in the form
            const discountField = form.querySelector('input[name="discount_value"]');
            console.log('Discount field found:', discountField);
            console.log('Discount field value:', discountField ? discountField.value : 'NOT FOUND');
            
            // If discount field is not found in form, look in the offerFields container
            if (!discountField || !discountField.value) {
                const offerFieldsContainer = document.getElementById('offerFields');
                const discountFieldInContainer = offerFieldsContainer.querySelector('input[name="discount_value"]');
                console.log('Discount field in container:', discountFieldInContainer);
                console.log('Discount field value in container:', discountFieldInContainer ? discountFieldInContainer.value : 'NOT FOUND');
                
                if (discountFieldInContainer && discountFieldInContainer.value) {
                    formData.set('discount_value', discountFieldInContainer.value);
                    console.log('Manually set discount_value:', discountFieldInContainer.value);
                }
            }
            
            // Add product barcode to form data
            formData.append('product_barcode', selectedProduct.barcode);
            
            // Debug: Log form data
            console.log('Submitting offer data:', {
                product_barcode: selectedProduct.barcode,
                offer_type: formData.get('offer_type'),
                target_channel: formData.get('target_channel'),
                discount_value: formData.get('discount_value'),
                start_date: formData.get('start_date'),
                end_date: formData.get('end_date'),
                description: formData.get('description'),
                is_active: formData.get('is_active')
            });
            
            // Debug: Log all form data
            console.log('All form data entries:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }
            
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                console.error('CSRF token not found!');
                showNotification('CSRF token not found. Please refresh the page.', 'error');
                resetSaveButton(saveBtn, originalText);
                return;
            }
            
            // Check if CSRF token is in form data
            const formToken = formData.get('_token');
            const metaToken = csrfToken.getAttribute('content');
            console.log('Form CSRF token:', formToken);
            console.log('Meta CSRF token:', metaToken);
            console.log('Tokens match:', formToken === metaToken);
            
            console.log('CSRF Token:', csrfToken.getAttribute('content'));
            console.log('Submitting to URL:', '{{ route("admin.offers.store") }}');
            
            // Submit to backend
            console.log('Sending request to backend...');
            fetch('{{ route("admin.offers.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Response received, status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    showNotification(data.message, 'success');
                    resetOfferForm();
                    loadProductOffers();
                } else {
                    showNotification(data.message || 'Unknown error', 'error');
                }
                // Reset button after successful response
                console.log('Resetting button after response');
                clearTimeout(timeoutId);
                resetSaveButton(saveBtn, originalText);
            })
            .catch(error => {
                console.error('Request failed:', error);
                showNotification('Request failed: ' + error.message, 'error');
                // Reset button after error
                console.log('Resetting button after error');
                clearTimeout(timeoutId);
                resetSaveButton(saveBtn, originalText);
            });
            
        } catch (error) {
            console.error('Try-catch error:', error);
            clearTimeout(timeoutId);
            showNotification('An error occurred while saving the offer: ' + error.message, 'error');
            resetSaveButton(saveBtn, originalText);
        }
    }
    
    // Reset save button
    function resetSaveButton(btn, originalText) {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
    
    // Emergency reset function (can be called from console)
    window.resetOfferButton = function() {
        const saveBtn = document.getElementById('saveOffer');
        if (saveBtn) {
            saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Offer';
            saveBtn.disabled = false;
            console.log('Offer button reset manually');
        }
    };
    
    
    // Show notification
    function showNotification(message, type = 'info') {
        console.log('showNotification called:', message, type);
        
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert at the top of the card body
        const cardBody = document.querySelector('.card-body');
        if (cardBody) {
            cardBody.insertBefore(notification, cardBody.firstChild);
            console.log('Notification inserted successfully');
        } else {
            console.error('Card body not found for notification');
            // Fallback: show alert
            alert(message);
        }
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
    
    // Reset offer form
    function resetOfferForm() {
        document.getElementById('offerForm').reset();
        offerFields.innerHTML = '<p class="text-muted small">Please select an offer type to configure the offer details.</p>';
        pricingPreview.style.display = 'none';
    }
    
    // Load offers for the selected product
    function loadProductOffers() {
        if (!selectedProduct) return;
        
        // First test if the route is accessible
        fetch('{{ route("admin.offers.test") }}')
            .then(response => {
                console.log('Test route response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Route test result:', data);
            })
            .catch(error => {
                console.error('Route test failed:', error);
            });
        
        // Test public route
        fetch('{{ url("offers/public-test") }}')
            .then(response => {
                console.log('Public route response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Public route result:', data);
            })
            .catch(error => {
                console.error('Public route test failed:', error);
            });
        
        fetch(`{{ route("admin.offers.product", ":barcode") }}`.replace(':barcode', selectedProduct.barcode))
            .then(response => {
                console.log('Load offers response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Load offers response data:', data);
                if (data.success) {
                    displayProductOffers(data.offers);
                } else {
                    console.error('Error loading offers:', data.message);
                }
            })
            .catch(error => {
                console.error('Error loading offers:', error);
            });
    }
    
    // Display existing offers for the product
    function displayProductOffers(offers) {
        const retailOffersContainer = document.getElementById('retailOffersList');
        const wholesaleOffersContainer = document.getElementById('wholesaleOffersList');
        
        if (!retailOffersContainer || !wholesaleOffersContainer) {
            console.error('Offer containers not found in DOM');
            return;
        }
        
        // Separate offers by channel
        const retailOffers = offers.filter(offer => offer.target_channel === 'retail' || offer.target_channel === 'both');
        const wholesaleOffers = offers.filter(offer => offer.target_channel === 'wholesale' || offer.target_channel === 'both');
        
        // Display retail offers
        if (retailOffers.length === 0) {
            retailOffersContainer.innerHTML = '<p class="text-muted small mb-0">No active retail offers</p>';
        } else {
            let retailHtml = '';
            retailOffers.forEach(offer => {
                retailHtml += createOfferCard(offer);
            });
            retailOffersContainer.innerHTML = retailHtml;
        }
        
        // Display wholesale offers
        if (wholesaleOffers.length === 0) {
            wholesaleOffersContainer.innerHTML = '<p class="text-muted small mb-0">No active wholesale offers</p>';
        } else {
            let wholesaleHtml = '';
            wholesaleOffers.forEach(offer => {
                wholesaleHtml += createOfferCard(offer);
            });
            wholesaleOffersContainer.innerHTML = wholesaleHtml;
        }
    }
    
    // Create offer card HTML
    function createOfferCard(offer) {
        const statusClass = offer.is_active ? 'success' : 'secondary';
        const statusText = offer.is_active ? 'Active' : 'Inactive';
        const startDate = new Date(offer.start_date).toLocaleDateString();
        const endDate = new Date(offer.end_date).toLocaleDateString();
        
        return `
            <div class="card mb-2">
                <div class="card-header py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-white">${offer.offer_type.toUpperCase()}</span>
                        <span class="badge bg-${offer.target_channel === 'both' ? 'info' : offer.target_channel === 'retail' ? 'success' : 'primary'} small">${offer.target_channel.toUpperCase()}</span>
                    </div>
                </div>
                <div class="card-body py-2">
                    <p class="mb-1 small"><strong>Discount:</strong> ${offer.discount_value}${offer.offer_type === 'percentage' ? '%' : ' TZS'}</p>
                    <p class="mb-1 small"><strong>Period:</strong> ${startDate} - ${endDate}</p>
                    <p class="mb-1 small"><strong>Status:</strong> <span class="badge bg-${statusClass} small">${statusText}</span></p>
                    ${offer.description ? `<p class="mb-1 small"><strong>Description:</strong> ${offer.description}</p>` : ''}
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary btn-sm" onclick="editOffer(${offer.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="deleteOffer(${offer.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Edit offer function
    function editOffer(offerId) {
        // TODO: Implement edit functionality
        showNotification('Edit functionality coming soon!', 'info');
    }
    
    // Delete offer function
    function deleteOffer(offerId) {
        if (confirm('Are you sure you want to delete this offer?')) {
            fetch(`{{ route("admin.offers.destroy", ":id") }}`.replace(':id', offerId), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    loadProductOffers(); // Reload offers
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('An error occurred while deleting the offer.', 'error');
                console.error('Delete offer error:', error);
            });
        }
    }
    
    // Set default dates
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    
    if (startDate) {
        startDate.value = new Date().toISOString().slice(0, 10);
    }
    
    if (endDate) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        endDate.value = tomorrow.toISOString().slice(0, 10);
    }
});
</script>
@endpush

@push('styles')
<style>
/* Custom container styles - no rounded corners, no shadows */
.product-info-container,
.offers-container,
.offer-form-container,
.pricing-preview-container {
    background: #fff;
    border: 1px solid #e9ecef;
    margin-bottom: 1rem;
    box-shadow: none;
    border-radius: 0;
}

.product-info-header,
.offers-header,
.offer-form-header,
.pricing-preview-header {
    background: #fff;
    border-bottom: 2px solid #e9ecef;
    padding: 0.75rem 1rem;
    position: relative;
}

.product-info-header::after,
.offers-header::after,
.offer-form-header::after,
.pricing-preview-header::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 30px;
    height: 2px;
    background: #007bff;
}

.retail-header::after {
    background: #28a745;
}

.wholesale-header::after {
    background: #007bff;
}

.offer-form-header::after {
    background: #ffc107;
}

.pricing-preview-header::after {
    background: #17a2b8;
}

.product-info-body,
.offers-body,
.offer-form-body,
.pricing-preview-body {
    padding: 1rem;
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

/* Card header styles */
.card-header {
    padding: 0.5rem 1rem;
    min-height: 2.5rem;
}

.card-header h6 {
    font-size: 0.95rem;
    font-weight: 600;
    line-height: 1.2;
}

/* Form styles */
.form-control-sm, .form-select-sm {
    font-size: 0.8125rem;
    padding: 0.375rem 0.75rem;
}

.input-group-sm .form-control {
    font-size: 0.8125rem;
    padding: 0.375rem 0.75rem;
}

.input-group-sm .input-group-text {
    font-size: 0.8125rem;
    padding: 0.375rem 0.75rem;
}

/* Pricing preview styles */
.pricing-preview-item {
    font-size: 0.8rem;
}

.pricing-preview-item .fw-bold {
    font-size: 0.85rem;
}

/* Offer card styles */
.card .card-header {
    padding: 0.5rem 0.75rem;
    min-height: 2rem;
}

.card .card-body {
    padding: 0.75rem;
}

.card .badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

.card .btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .card-header {
        padding: 0.375rem 0.75rem;
        min-height: 2rem;
    }
    
    .card-header h6 {
        font-size: 0.85rem;
    }
    
    .breadcrumb {
        font-size: 0.8125rem;
        padding: 0.375rem 0;
    }
    
    .breadcrumb-item i {
        font-size: 0.6875rem;
    }
}
</style>
@endpush
