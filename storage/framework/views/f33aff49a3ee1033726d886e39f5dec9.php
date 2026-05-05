<?php $__env->startSection('title', 'POS Terminal - CHIBO BRAND'); ?>

<?php $__env->startPush('styles'); ?>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div id="pos-terminal" class="pos-container">
    <!-- Header / Top Bar -->
    <div class="pos-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <button id="toggle-fullscreen" class="btn btn-primary me-3" title="Toggle Fullscreen">
                <i class="fas fa-expand"></i>
            </button>
            <h4 class="mb-0 fw-bold"><i class="fas fa-cash-register me-2"></i>POS TERMINAL</h4>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div id="current-time" class="fw-bold text-muted d-none d-md-block"></div>
            <button id="exit-pos" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-sign-out-alt me-1"></i> Exit
            </button>
        </div>
    </div>

    <div class="pos-main-content row g-0">
        <!-- Left Column: Products -->
        <div class="col-lg-7 pos-product-section p-3">
            <div class="pos-search-wrapper mb-3">
                <div class="input-group border">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="product-search" class="form-control border-start-0 ps-0" placeholder="Search products by name, barcode, or nickname...">
                </div>
            </div>

            <!-- Product/Service Toggle -->
            <div class="mb-3">
                <div class="btn-group w-100" role="group">
                    <button type="button" class="btn btn-primary fw-bold" id="btn-type-product" onclick="setSearchType('product')">
                        <i class="fas fa-box me-1"></i> PRODUCTS
                    </button>
                    <button type="button" class="btn btn-outline-primary fw-bold" id="btn-type-service" onclick="setSearchType('service')">
                        <i class="fas fa-pencil-ruler me-1"></i> DESIGN SERVICES
                    </button>
                </div>
            </div>

            <div id="product-grid" class="pos-product-grid">
                <!-- Products will be loaded here -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Loading products...</p>
                </div>
            </div>
        </div>

        <!-- Right Column: Cart & Checkout -->
        <div class="col-lg-5 pos-cart-section p-3 border-start">
            <div class="card h-100 border-0">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-shopping-cart me-2"></i>Order Summary</h5>
                    <span id="cart-count" class="badge bg-primary rounded-pill">0</span>
                </div>
                
                <div class="card-body p-0 d-flex flex-column">
                    <!-- Customer & Salesperson Selector -->
                    <div class="p-2 border-bottom selection-section">
                        <!-- Customer Selector with Select2 -->
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold text-muted mb-0" style="font-size: 10px;">CUSTOMER</label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-primary fw-bold" id="btn-add-customer">
                                    <i class="fas fa-plus-circle me-1"></i> New Customer
                                </button>
                            </div>
                            <div class="position-relative">
                                <div class="input-group border">
                                    <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" id="customer-search-input" class="form-control border-0" placeholder="Search customer by name or phone..." autocomplete="off">
                                </div>
                                <div id="customer-search-results" class="dropdown-menu w-100 border mt-1" style="max-height: 300px; overflow-y: auto;"></div>
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary w-100 text-uppercase fw-bold" id="select-guest-btn" style="font-size: 10px;">
                                    <i class="fas fa-user-secret me-1"></i> Walk-in Guest
                                </button>
                            </div>
                        </div>

                        <!-- Selected Customer Details -->
                        <div id="selected-customer-display" class="d-none mb-3">
                            <div class="alert alert-primary p-2 border position-relative" style="background: #e7f1ff;">
                                <button class="btn btn-sm btn-link text-danger position-absolute top-0 end-0 m-1 p-0" id="clear-customer" title="Remove Customer">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold text-primary small" id="cust-name"></h6>
                                        <div class="small"><i class="fas fa-phone-alt me-1 opacity-75"></i> <span id="cust-phone"></span></div>
                                        <div class="small text-muted mt-1">
                                            <i class="fas fa-map-marker-alt me-1 opacity-75"></i> 
                                            <span id="cust-address" class="text-truncate"></span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-2 ms-2" id="btn-edit-customer">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Guest Entry Form -->
                        <div id="guest-customer-form" class="d-none mb-3 p-2 bg-white rounded border">
                            <p class="small fw-bold text-muted mb-2">QUICK GUEST ENTRY</p>
                            <input type="text" id="guest-name" class="form-control form-control-sm mb-1" placeholder="Guest Name">
                            <input type="text" id="guest-phone" class="form-control form-control-sm mb-1" placeholder="Guest Phone">
                            <input type="text" id="guest-address" class="form-control form-control-sm" placeholder="Location/Address">
                        </div>

                        <!-- Salesperson Selector with Select2 -->
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted mb-1" style="font-size: 10px;">SALESPERSON</label>
                            <select id="saler-select" class="form-select form-select-sm" style="width: 100%;">
                                <?php $__currentLoopData = $salers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $saler): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($saler->id); ?>" <?php echo e($saler->id == $currentUser->id ? 'selected' : ''); ?> data-phone="<?php echo e($saler->phone); ?>">
                                        <?php echo e($saler->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted mb-1" style="font-size: 10px;">DEPARTMENT</label>
                            <select id="department-select" class="form-select form-select-sm" style="width: 100%;">
                                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($dept->id); ?>"><?php echo e($dept->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="form-check form-switch pt-1">
                            <input class="form-check-input" type="checkbox" id="pricing-toggle">
                            <label class="form-check-label fw-bold text-primary" for="pricing-toggle" style="font-size: 10px;">Switch to Wholesale</label>
                        </div>
                    </div>

                    <!-- Cart Items -->
                    <div id="cart-items" class="p-2">
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-shopping-basket fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0">Cart is empty</p>
                        </div>
                    </div>

                    <!-- Totals & Checkout -->
                    <div class="p-2 bg-white border-top" style="padding-top: 5px !important; padding-bottom: 5px !important;">
                        <div class="d-flex gap-2">
                            <div class="form-check form-switch mb-2 p-1 bg-white rounded border d-flex justify-content-between align-items-center flex-fill" style="background-color: #f8f9fa !important;">
                                <label class="form-check-label small fw-bold text-primary mb-0 ms-2" for="vat-toggle" style="font-size: 10px;">
                                    <i class="fas fa-percent me-1"></i> VAT
                                </label>
                                <input class="form-check-input ms-0" type="checkbox" id="vat-toggle">
                            </div>
                            <div class="form-check form-switch mb-2 p-1 bg-white rounded border d-flex justify-content-between align-items-center flex-fill" style="background-color: #f8f9fa !important;">
                                <label class="form-check-label small fw-bold text-primary mb-0 ms-2" for="proforma-toggle" style="font-size: 10px;">
                                    <i class="fas fa-file-invoice me-1"></i> PROFORMA
                                </label>
                                <input class="form-check-input ms-0" type="checkbox" id="proforma-toggle">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted fw-bold">Subtotal</span>
                            <span id="subtotal" class="fw-bold">0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 h6">
                            <span class="fw-bold">Total</span>
                            <span id="total" class="fw-bold text-primary">0</span>
                        </div>

                        <div class="mb-2" id="payment-method-container">
                            <label class="small fw-bold text-muted d-block mb-1">PAYMENT METHOD</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="payment_method" id="pay-cash" value="cash" checked>
                                <label class="btn btn-outline-primary py-2" for="pay-cash"><i class="fas fa-money-bill-wave me-1"></i> Cash</label>

                                <input type="radio" class="btn-check" name="payment_method" id="pay-mobile" value="mobile_money">
                                <label class="btn btn-outline-primary py-2" for="pay-mobile"><i class="fas fa-mobile-alt me-1"></i> Mobile</label>
                            </div>
                        </div>

                        <div class="mb-2" id="amount-paid-container">
                            <label class="small fw-bold text-muted d-block mb-1">AMOUNT PAID</label>
                            <div class="input-group border">
                                <span class="input-group-text bg-white border-0"><i class="fas fa-money-bill"></i></span>
                                <input type="number" id="amount-paid" class="form-control border-0" placeholder="Enter amount paid" min="0" step="0.01">
                            </div>
                            <div id="balance-display" class="mt-2 p-2 bg-light rounded border d-none">
                                <div class="d-flex justify-content-between">
                                    <span class="small fw-bold text-muted">Balance:</span>
                                    <span id="balance-amount" class="small fw-bold text-danger">0</span>
                                </div>
                            </div>
                        </div>

                        <button id="checkout-btn" class="btn btn-primary btn-lg w-100 fw-bold py-2 checkout-btn-now text-uppercase">
                            Complete Transaction
                        </button>
                        
                        <button id="reprint-last-btn" class="btn btn-outline-secondary btn-sm w-100 mt-2 d-none">
                            <i class="fas fa-print me-1"></i> Reprint Last Receipt
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Variant & Volume Discount Modal -->
<div class="modal fade" id="variantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary" id="variant-product-name">Product Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 border-end">
                        <div id="variant-options-container">
                            <!-- Variant categories will be loaded here -->
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="volume-discounts-section">
                            <div class="small fw-bold text-primary mb-2 text-uppercase" style="letter-spacing: 1px; font-size: 11px;">
                                <i class="fas fa-tags me-1"></i> Volume Discounts
                            </div>
                            <div id="volume-discounts-container">
                                <!-- Volume discounts will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-primary px-4" data-bs-dismiss="modal" style="font-size: 12px;">Cancel</button>
                <button type="button" class="btn btn-primary px-4 fw-bold" id="add-to-cart-confirm" style="font-size: 12px;">ADD TO CART</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border">
            <div class="modal-header bg-primary text-white rounded-0">
                <h5 class="modal-title fw-bold" style="font-size: 14px;"><i class="fas fa-user-plus me-2"></i>New Customer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="add-customer-form">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">FULL NAME <span class="text-danger">*</span></label>
                        <input type="text" id="new-cust-name" class="form-control" placeholder="Enter customer name" required style="font-size: 12px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">PHONE NUMBER <span class="text-danger">*</span></label>
                        <input type="text" id="new-cust-phone" class="form-control" placeholder="+255..." required style="font-size: 12px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">EMAIL ADDRESS</label>
                        <input type="email" id="new-cust-email" class="form-control" placeholder="customer@example.com" style="font-size: 12px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">ADDRESS/LOCATION</label>
                        <input type="text" id="new-cust-address" class="form-control" placeholder="City, Area, Street" style="font-size: 12px;">
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch p-0 d-flex justify-content-between">
                            <label class="form-check-label fw-bold small" for="new-cust-wholesale">WHOLESALE CUSTOMER</label>
                            <input class="form-check-input ms-0" type="checkbox" id="new-cust-wholesale">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal" style="font-size: 12px;">Cancel</button>
                <button type="button" class="btn btn-primary px-4 fw-bold" id="btn-save-customer" style="font-size: 12px;">SAVE CUSTOMER</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border">
            <div class="modal-header bg-primary text-white rounded-0">
                <h5 class="modal-title fw-bold" style="font-size: 14px;"><i class="fas fa-user-edit me-2"></i>Edit Customer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="edit-customer-form">
                    <input type="hidden" id="edit-cust-id">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">FULL NAME <span class="text-danger">*</span></label>
                        <input type="text" id="edit-cust-name" class="form-control" placeholder="Enter customer name" required style="font-size: 12px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">PHONE NUMBER <span class="text-danger">*</span></label>
                        <input type="text" id="edit-cust-phone" class="form-control" placeholder="+255..." required style="font-size: 12px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">EMAIL ADDRESS</label>
                        <input type="email" id="edit-cust-email" class="form-control" placeholder="customer@example.com" style="font-size: 12px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">ADDRESS/LOCATION</label>
                        <input type="text" id="edit-cust-address" class="form-control" placeholder="City, Area, Street" style="font-size: 12px;">
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch p-0 d-flex justify-content-between">
                            <label class="form-check-label fw-bold small" for="edit-cust-wholesale">WHOLESALE CUSTOMER</label>
                            <input class="form-check-input ms-0" type="checkbox" id="edit-cust-wholesale">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal" style="font-size: 12px;">Cancel</button>
                <button type="button" class="btn btn-primary px-4 fw-bold" id="btn-update-customer" style="font-size: 12px;">UPDATE CUSTOMER</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Blue & White Compact POS Styles */
:root {
    --pos-bg: #fff;
    --pos-primary: #0d6efd;
    --pos-secondary: #6c757d;
    --pos-accent: #0a58ca;
    --border-color: #dee2e6;
}

.pos-container {
    background: var(--pos-bg);
    min-height: calc(100vh - 100px);
    display: flex;
    flex-direction: column;
    font-family: 'Nunito Sans', sans-serif;
    font-size: 12px;
}

#pos-terminal.fullscreen-mode {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 1040;
    background: var(--pos-bg);
}

.pos-header {
    background: #fff;
    padding: 0.75rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
    height: 60px;
}

.pos-main-content {
    flex-grow: 1;
}

.pos-product-section {
    min-height: calc(100vh - 60px);
    background: #fff;
    padding-bottom: 50px;
}

.pos-cart-section {
    background: #f8f9fa;
    border-left: 1px solid var(--border-color);
    height: auto;
}

.pos-cart-section .card {
    height: auto;
}

.pos-cart-section .card-header {
    padding: 10px 15px !important;
}

.pos-cart-section .selection-section {
    /* Full visibility, no internal scrolling */
}

.pos-cart-section .card-body {
    /* Dynamic height */
}

#cart-items {
    /* Dynamic height based on items */
}

/* Compact Typography */
h4, .h4, h5, .h5, h6, .h6 {
    font-size: 14px !important;
}

.pos-cart-section {
    font-size: 12px;
}

.pos-cart-section .form-label { 
    font-size: 11px; 
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 0.25rem;
    color: var(--pos-primary);
}

.pos-cart-section .form-control, 
.pos-cart-section .form-select,
.pos-cart-section .input-group-text { 
    font-size: 12px; 
    padding: 0.375rem 0.75rem;
    border-radius: 4px;
}

.pos-cart-section .btn-lg { font-size: 14px; padding: 0.75rem; border-radius: 4px; }

.pos-search-wrapper .form-control,
#customer-search-input {
    font-size: 12px !important;
    height: 38px;
}

.pos-search-wrapper .input-group-text {
    font-size: 14px;
}

.pos-product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 12px;
}

.product-card {
    background: #fff;
    border-radius: 4px;
    padding: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.product-card:hover {
    border-color: var(--pos-primary);
    background: #f8f9fa;
}

.product-img-wrapper {
    position: relative;
    width: 100%;
    height: 140px;
    margin-bottom: 8px;
    overflow: hidden;
    border-radius: 2px;
    background: #f8f9fa;
}

.product-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-img {
    transform: scale(1.05);
}

.product-info {
    flex-grow: 1;
}

.product-name {
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 4px;
    color: #212529;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    height: 2.6em;
    line-height: 1.3;
}

.product-prices-wrapper {
    display: flex;
    flex-direction: column;
    margin-top: 4px;
}

.price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.price-row.active {
    color: #212529;
    font-weight: 800;
    font-size: 13px;
}

.price-row.inactive {
    color: #adb5bd;
    font-size: 10px;
}

.price-label {
    font-size: 8px;
    text-transform: uppercase;
    font-weight: 600;
    background: #f8f9fa;
    padding: 0 4px;
    border-radius: 2px;
    border: 1px solid #eee;
}

.price-row.active .price-label {
    background: #e7f1ff;
    color: #0d6efd;
    border-color: #cfe2ff;
}

.product-stock {
    font-size: 10px;
    color: #6c757d;
    margin-top: 4px;
}

.price-tier-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #0d6efd;
    color: #fff;
    padding: 2px 6px;
    border-radius: 2px;
    font-size: 9px;
    font-weight: 700;
    z-index: 2;
}

/* Cart Styles */
.cart-item {
    background: #fff;
    border-radius: 4px;
    padding: 6px 10px;
    margin-bottom: 5px;
    border: 1px solid var(--border-color);
    transition: background 0.2s;
}

.cart-item:hover {
    background: #fdfdfd;
}

.cart-item-info {
    flex-grow: 1;
}

.cart-item-name {
    font-weight: 600;
    font-size: 12px;
    color: #212529;
}

.cart-item-variant {
    font-size: 10px;
    color: #6c757d;
    background: #f8f9fa;
    display: inline-block;
    padding: 1px 6px;
    border-radius: 2px;
    margin-top: 2px;
    border: 1px solid #dee2e6;
}

.cart-controls {
    display: flex;
    align-items: center;
    gap: 6px;
}

.qty-btn {
    width: 28px;
    height: 28px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 10px;
}

.qty-btn:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
}

.qty-input {
    width: 40px;
    text-align: center;
    border: 1px solid transparent;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 700;
}

/* Modal Styling */
.modal-content {
    border-radius: 4px;
    border: none;
}

.variant-option-btn {
    padding: 8px 16px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
    background: #fff;
    font-size: 12px;
    font-weight: 600;
    margin-right: 5px;
    margin-bottom: 5px;
    transition: all 0.2s;
}

.variant-option-btn.active {
    background: #0d6efd;
    color: #fff;
    border-color: #0d6efd;
}

/* Skeleton Loader */
.skeleton {
    background: #f0f0f0;
    background: linear-gradient(90deg, #f0f0f0 25%, #f8f8f8 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    to { background-position-x: -200%; }
}

.checkout-btn-now {
    background: #0d6efd;
    border: none;
    color: #fff;
    transition: all 0.2s;
}

.checkout-btn-now:hover {
    background: #0a58ca;
    transform: translateY(-1px);
}

.tier-active-indicator {
    font-size: 10px;
    color: #0d6efd;
    font-weight: 600;
    display: block;
    margin-top: 2px;
}

/* Scrollbar */
::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-track {
    background: #fff;
}
::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
    background: #ced4da;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let cart = [];
    let products = [];
    let selectedCustomer = null;
    let lastOrderData = null;
    let isWholesale = false;
    let currentProductForVariants = null;
    let selectedVariants = {};
    let hasVAT = false;
    let searchType = 'product';
    let isProforma = <?php echo json_encode($isProforma ?? false, 15, 512) ?>;

    const productGrid = document.getElementById('product-grid');
    const productSearch = document.getElementById('product-search');
    const btnTypeProduct = document.getElementById('btn-type-product');
    const btnTypeService = document.getElementById('btn-type-service');
    const cartContainer = document.getElementById('cart-items');
    const subtotalEl = document.getElementById('subtotal');
    const totalEl = document.getElementById('total');
    const checkoutBtn = document.getElementById('checkout-btn');
    const reprintBtn = document.getElementById('reprint-last-btn');
    const pricingToggle = document.getElementById('pricing-toggle');
    const customerSearch = document.getElementById('customer-search');
    const customerResults = document.getElementById('customer-results');
    const salerSelect = document.getElementById('saler-select');
    const selectedCustomerDisplay = document.getElementById('selected-customer-display');
    const guestCustomerForm = document.getElementById('guest-customer-form');
    const fullscreenToggle = document.getElementById('toggle-fullscreen');
    const posTerminal = document.getElementById('pos-terminal');
    const exitPos = document.getElementById('exit-pos');
    const vatToggle = document.getElementById('vat-toggle');
    const editCustModalEl = document.getElementById('editCustomerModal');
    const updateCustModal = editCustModalEl ? new bootstrap.Modal(editCustModalEl) : null;
    const btnUpdateCustomer = document.getElementById('btn-update-customer');

    const addCustModalEl = document.getElementById('addCustomerModal');
    const addCustModal = addCustModalEl ? new bootstrap.Modal(addCustModalEl) : null;
    
    const variantModalEl = document.getElementById('variantModal');
    const variantModal = variantModalEl ? new bootstrap.Modal(variantModalEl) : null;
    
    // Set time
    setInterval(() => {
        document.getElementById('current-time').textContent = new Date().toLocaleString();
    }, 1000);

    // Fullscreen Toggle
    fullscreenToggle.addEventListener('click', () => {
        posTerminal.classList.toggle('fullscreen-mode');
        const icon = fullscreenToggle.querySelector('i');
        icon.classList.toggle('fa-expand');
        icon.classList.toggle('fa-compress');
        const sidebar = document.querySelector('.sidebar');
        const navbar = document.querySelector('.top-navbar');
        if (posTerminal.classList.contains('fullscreen-mode')) {
            if (sidebar) sidebar.style.display = 'none';
            if (navbar) navbar.style.display = 'none';
            document.body.style.overflow = 'hidden';
        } else {
            if (sidebar) sidebar.style.display = 'block';
            if (navbar) navbar.style.display = 'flex';
            document.body.style.overflow = 'auto';
        }
    });

    exitPos.addEventListener('click', () => {
        if (confirm('Exit POS? Any unsaved changes will be lost.')) {
            window.location.href = "<?php echo e(route('admin.finance.dashboard')); ?>";
        }
    });

    // Initialize Proforma Mode
    if (isProforma) {
        document.getElementById('proforma-toggle').checked = true;
        updateProformaDisplay(true);
    }


    // Initial load
    fetchProducts('');
    
    selectedCustomerDisplay.classList.add('d-none'); // Ensure reset

    let productSearchTimeout;
    productSearch.addEventListener('input', (e) => {
        clearTimeout(productSearchTimeout);
        productSearchTimeout = setTimeout(() => fetchProducts(e.target.value), 300);
    });

    async function fetchProducts(query) {
        showProductLoading();
        try {
            const response = await fetch(`<?php echo e(route('admin.pos.products.search')); ?>?query=${query}&type=${searchType}`);
            products = await response.json();
            renderProducts();
        } catch (error) { 
            console.error(error); 
            productGrid.innerHTML = '<div class="col-12 text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-2x mb-2"></i><br>Error loading items.</div>';
        }
    }

    window.setSearchType = (type) => {
        searchType = type;
        if(type === 'product') {
            btnTypeProduct.classList.add('active', 'btn-primary');
            btnTypeProduct.classList.remove('btn-outline-primary');
            btnTypeService.classList.remove('active', 'btn-primary');
            btnTypeService.classList.add('btn-outline-primary');
        } else {
            btnTypeService.classList.add('active', 'btn-primary');
            btnTypeService.classList.remove('btn-outline-primary');
            btnTypeProduct.classList.remove('active', 'btn-primary');
            btnTypeProduct.classList.add('btn-outline-primary');
        }
        fetchProducts(productSearch.value);
    };

    function showProductLoading() {
        productGrid.innerHTML = Array(8).fill(0).map(() => `
            <div class="product-card border-0 shadow-none" style="pointer-events: none; opacity: 0.7;">
                <div class="skeleton mb-3" style="height: 150px; width: 100%;"></div>
                <div class="skeleton mb-2" style="height: 20px; width: 80%;"></div>
                <div class="skeleton mb-3" style="height: 15px; width: 40%;"></div>
                <div class="d-flex justify-content-between">
                    <div class="skeleton" style="height: 20px; width: 30%;"></div>
                    <div class="skeleton" style="height: 20px; width: 20%;"></div>
                </div>
            </div>
        `).join('');
    }

    function renderProducts() {
        if (products.length === 0) {
            productGrid.innerHTML = '<div class="col-12 text-center py-5 text-muted">No items found.</div>';
            return;
        }

        productGrid.innerHTML = products.map(product => {
            const hasTiers = product.price_tiers && product.price_tiers.some(t => t.customer_type === (isWholesale ? 'wholesale' : 'retail'));
            // Use quotes for ID in onclick to support string IDs
            return `
                <div class="product-card" onclick="handleProductClick('${product.id}')">
                    ${hasTiers ? `<div class="price-tier-badge">BULK</div>` : ''}
                    <div class="product-img-wrapper">
                        <img src="${product.image}" class="product-img" alt="${product.name}" onerror="this.src='/images/default.webp'">
                    </div>
                    <div class="product-info">
                        <div class="product-name">${product.name}</div>
                        
                        <div class="product-prices-wrapper">
                            <div class="price-row ${!isWholesale ? 'active' : 'inactive'}">
                                <span class="price-label">Retail</span>
                                <span>TZS ${formatNumber(product.retail_price)}</span>
                            </div>
                            <div class="price-row ${isWholesale ? 'active' : 'inactive'}">
                                <span class="price-label">Wholesale</span>
                                <span>TZS ${formatNumber(product.wholesale_price)}</span>
                            </div>
                        </div>

                        <div class="product-stock d-flex justify-content-between align-items-center">
                            <span class="badge ${product.stock > 0 ? 'bg-primary text-white' : 'bg-danger text-white'}" style="font-size: 9px; border-radius: 2px;">
                                ${product.track_stock ? `${product.stock} LEFT` : 'IN STOCK'}
                            </span>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    window.handleProductClick = (productId) => {
        // productId can be string 'service_X' or int
        const product = products.find(p => String(p.id) === String(productId));
        if (!product) return;

        if (product.is_service) {
            addToCart(product, 1, []);
        } else {
            openVariantModal(product);
        }
    };

    function openVariantModal(product) {
        currentProductForVariants = product;
        selectedVariants = {};
        document.getElementById('variant-product-name').textContent = product.name;
        
        // Variants
        const varContainer = document.getElementById('variant-options-container');
        if (product.variants && product.variants.length > 0) {
            varContainer.innerHTML = product.variants.map(cat => `
                <div class="variant-category-group mb-4">
                    <div class="small fw-bold text-muted mb-2 text-uppercase letter-spacing-1">${cat.name}</div>
                    <div class="variant-btn-group">
                        ${cat.items.map(item => `
                            <button type="button" class="variant-option-btn" 
                                    onclick="selectVariantOption(${cat.id}, ${item.id}, '${item.name.replace(/'/g, "\\'")}')"
                                    id="variant-opt-${item.id}">
                                ${item.name}
                            </button>
                        `).join('')}
                    </div>
                </div>
            `).join('');
        } else {
            varContainer.innerHTML = '<div class="alert alert-light border text-center p-4"><i class="fas fa-info-circle mb-2 d-block fs-3"></i>No variants for this product</div>';
        }

        // Discounts
        const discountContainer = document.getElementById('volume-discounts-container');
        const relevantTiers = (product.price_tiers || []).filter(t => t.customer_type === (isWholesale ? 'wholesale' : 'retail'));
        
        if (relevantTiers.length > 0) {
            discountContainer.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-sm table-hover border">
                        <thead class="bg-light">
                            <tr><th class="small py-2 ps-2">Quantity Range</th><th class="small py-2 text-end pe-2">Price Per Unit</th></tr>
                        </thead>
                        <tbody>
                            ${relevantTiers.map(t => `
                                <tr>
                                    <td class="py-2 ps-2 fw-bold">${t.min_quantity}${t.max_quantity ? ' - ' + t.max_quantity : '+'} items</td>
                                    <td class="py-2 text-end pe-2 text-dark fw-bold">TZS ${formatNumber(t.price_per_unit)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-dark py-2 small mb-0"><i class="fas fa-info-circle me-1"></i> Discount automatically applies in cart based on quantity.</div>
            `;
        } else {
            discountContainer.innerHTML = '<div class="text-center py-4 text-muted small">No volume discounts available for ' + (isWholesale ? 'Wholesale' : 'Retail') + '.</div>';
        }

        if (variantModal) variantModal.show();
        else new bootstrap.Modal(document.getElementById('variantModal')).show();
    }

    window.selectVariantOption = (catId, itemId, itemName) => {
        const parent = document.getElementById(`variant-opt-${itemId}`).closest('.variant-btn-group');
        parent.querySelectorAll('.variant-option-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById(`variant-opt-${itemId}`).classList.add('active');
        const cat = currentProductForVariants.variants.find(c => c.id === catId);
        selectedVariants[cat.name] = itemName;
    };

    document.getElementById('add-to-cart-confirm').addEventListener('click', () => {
        if (currentProductForVariants.variants && currentProductForVariants.variants.length > 0) {
            if (Object.keys(selectedVariants).length < currentProductForVariants.variants.length) {
                return alert('Please select all options.');
            }
        }
        addToCart(currentProductForVariants, selectedVariants);
        if (variantModal) variantModal.hide();
        else {
            const inst = bootstrap.Modal.getInstance(document.getElementById('variantModal'));
            if (inst) inst.hide();
        }
    });

    function addToCart(product, variants) {
        const variantKey = Object.values(variants).join('-');
        const cartItem = cart.find(item => item.id === product.id && item.variantKey === variantKey);
        if (cartItem) {
            cartItem.quantity++;
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                base_retail_price: parseFloat(product.retail_price) || 0,
                base_wholesale_price: parseFloat(product.wholesale_price) || 0,
                price_tiers: product.price_tiers || [],
                quantity: 1,
                variants: { ...variants },
                variantKey: variantKey,
                image: product.image
            });
        }
        renderCart();
    }

    function renderCart() {
        if (cart.length === 0) {
            cartContainer.innerHTML = '<div class="text-center py-3 text-muted"><i class="fas fa-shopping-basket fa-2x mb-2 opacity-10"></i><p class="mb-0">Cart is empty</p></div>';
            document.getElementById('cart-count').textContent = '0';
            updateTotals();
            return;
        }
        
        document.getElementById('cart-count').textContent = cart.reduce((acc, i) => acc + i.quantity, 0);

        cartContainer.innerHTML = cart.map((item, index) => {
            const variantDisplay = Object.entries(item.variants).map(([k, v]) => `${k}: ${v}`).join(', ');
            const unitData = calculateUnitPriceWithIndicator(item);
            return `
                <div class="cart-item">
                    <div class="d-flex align-items-center gap-3">
                        <div class="cart-item-info">
                            <div class="cart-item-name">${item.name}</div>
                            ${variantDisplay ? `<div class="cart-item-variant">${variantDisplay}</div>` : ''}
                            <div class="fw-bold mt-1 text-dark">${formatNumber(unitData.price)}</div>
                            ${unitData.isTiered ? `<span class="tier-active-indicator"><i class="fas fa-check-circle me-1"></i>Bulk Price Applied</span>` : ''}
                        </div>
                        <div class="cart-controls">
                            <div class="qty-btn" onclick="updateQty(${index}, -1)"><i class="fas fa-minus"></i></div>
                            <input type="number" class="qty-input bg-transparent" value="${item.quantity}" min="1" onchange="manualUpdateQty(${index}, this.value)">
                            <div class="qty-btn" onclick="updateQty(${index}, 1)"><i class="fas fa-plus"></i></div>
                            <div class="remove-item ms-2" onclick="removeItem(${index})"><i class="fas fa-trash-alt"></i></div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        updateTotals();
    }

    function calculateUnitPriceWithIndicator(item) {
        const customerType = isWholesale ? 'wholesale' : 'retail';
        const basePrice = isWholesale ? item.base_wholesale_price : item.base_retail_price;
        if (item.price_tiers && item.price_tiers.length > 0) {
            const matchingTier = item.price_tiers.find(tier => 
                tier.customer_type === customerType && 
                item.quantity >= tier.min_quantity && 
                (!tier.max_quantity || item.quantity <= tier.max_quantity)
            );
            if (matchingTier) return { price: parseFloat(matchingTier.price_per_unit), isTiered: true };
        }
        return { price: basePrice, isTiered: false };
    }

    window.updateQty = (index, delta) => {
        cart[index].quantity += delta;
        if (cart[index].quantity < 1) removeItem(index);
        else renderCart();
    };

    window.manualUpdateQty = (index, value) => {
        let newQty = parseInt(value);
        if (isNaN(newQty) || newQty < 1) {
            newQty = 1;
        }
        cart[index].quantity = newQty;
        renderCart();
    };

    window.removeItem = (index) => {
        cart.splice(index, 1);
        renderCart();
    };

    function updateTotals() {
        let subtotal = 0;
        cart.forEach(item => subtotal += calculateUnitPriceWithIndicator(item).price * item.quantity);
        
        const vatAmount = hasVAT ? subtotal * 0.18 : 0;
        const total = subtotal + vatAmount;

        subtotalEl.textContent = formatNumber(subtotal);
        totalEl.textContent = formatNumber(total);
        
        // Update VAT display if present
        const vatRow = document.getElementById('vat-display-row');
        if (hasVAT) {
            if (!vatRow) {
                totalEl.parentElement.insertAdjacentHTML('beforebegin', `
                    <div class="d-flex justify-content-between mb-2" id="vat-display-row">
                        <span class="text-muted fw-bold small">VAT (18%)</span>
                        <span class="small fw-bold text-success">${formatNumber(vatAmount)}</span>
                    </div>
                `);
            } else {
                vatRow.querySelector('.text-success').textContent = formatNumber(vatAmount);
            }
        } else if (vatRow) {
            vatRow.remove();
        }
    }

    pricingToggle.addEventListener('change', (e) => {
        isWholesale = e.target.checked;
        const label = e.target.nextElementSibling;
        if (isWholesale) {
            label.textContent = 'Wholesale Active';
            label.classList.replace('text-primary', 'text-success');
        } else {
            label.textContent = 'Switch to Wholesale';
            label.classList.replace('text-success', 'text-primary');
        }
        renderProducts();
        renderCart();
    });

    vatToggle.addEventListener('change', (e) => {
        hasVAT = e.target.checked;
        renderCart();
    });

    const proformaToggle = document.getElementById('proforma-toggle');
    proformaToggle.addEventListener('change', (e) => {
        isProforma = e.target.checked;
        updateProformaDisplay(isProforma);
    });

    function updateProformaDisplay(active) {
        const pmContainer = document.getElementById('payment-method-container');
        const apContainer = document.getElementById('amount-paid-container');
        
        if (active) {
            checkoutBtn.textContent = 'Generate Proforma';
            checkoutBtn.classList.remove('btn-primary');
            checkoutBtn.classList.add('btn-success');
            if (pmContainer) pmContainer.classList.add('d-none');
            if (apContainer) apContainer.classList.add('d-none');
        } else {
            checkoutBtn.textContent = 'Complete Transaction';
            checkoutBtn.classList.remove('btn-success');
            checkoutBtn.classList.add('btn-primary');
            if (pmContainer) pmContainer.classList.remove('d-none');
            if (apContainer) apContainer.classList.remove('d-none');
        }
    }

    // Balance calculation
    const amountPaidInput = document.getElementById('amount-paid');
    const balanceDisplay = document.getElementById('balance-display');
    const balanceAmount = document.getElementById('balance-amount');
    
    amountPaidInput.addEventListener('input', () => {
        const paid = parseFloat(amountPaidInput.value) || 0;
        const total = cart.reduce((acc, item) => acc + (calculateUnitPriceWithIndicator(item).price * item.quantity), 0) + (hasVAT ? cart.reduce((acc, item) => acc + (calculateUnitPriceWithIndicator(item).price * item.quantity), 0) * 0.18 : 0);
        const balance = total - paid;
        
        if (paid > 0 && balance !== 0) {
            balanceDisplay.classList.remove('d-none');
            balanceAmount.textContent = formatNumber(Math.abs(balance));
            balanceAmount.className = balance > 0 ? 'small fw-bold text-danger' : 'small fw-bold text-success';
        } else {
            balanceDisplay.classList.add('d-none');
        }
    });

    // Customer search functionality
    const customerSearchInput = document.getElementById('customer-search-input');
    const customerSearchResults = document.getElementById('customer-search-results');
    const selectGuestBtn = document.getElementById('select-guest-btn');
    let searchTimeout;

    customerSearchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        
        if (query.length < 2) {
            customerSearchResults.classList.remove('show');
            return;
        }
        
        searchTimeout = setTimeout(() => {
            fetchAndDisplayCustomers(query);
        }, 300);
    });

    customerSearchInput.addEventListener('focus', (e) => {
        if (e.target.value.trim().length >= 2) {
            fetchAndDisplayCustomers(e.target.value.trim());
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!customerSearchInput.contains(e.target) && !customerSearchResults.contains(e.target)) {
            customerSearchResults.classList.remove('show');
        }
    });

    async function fetchAndDisplayCustomers(query) {
        customerSearchResults.innerHTML = '<div class="dropdown-item text-center py-2"><div class="spinner-border spinner-border-sm text-dark"></div></div>';
        customerSearchResults.classList.add('show');
        
        try {
            const response = await fetch(`<?php echo e(route('admin.pos.customers.search')); ?>?query=${query}`);
            const customers = await response.json();
            
            if (customers.length === 0) {
                customerSearchResults.innerHTML = '<div class="dropdown-item text-muted">No customers found.</div>';
            } else {
                customerSearchResults.innerHTML = customers.map(c => `
                    <button class="dropdown-item d-flex flex-column py-2 border-bottom customer-result-item" type="button" data-customer='${JSON.stringify(c)}'>
                        <span class="fw-bold">${c.name}</span>
                        <small class="text-muted">${c.phone || 'No phone'} ${c.address ? '• ' + c.address : ''}</small>
                    </button>
                `).join('');
                
                // Add click handlers to customer result items
                document.querySelectorAll('.customer-result-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const customer = JSON.parse(this.dataset.customer);
                        window.setCustomer(customer);
                        customerSearchInput.value = '';
                        customerSearchResults.classList.remove('show');
                    });
                });
            }
        } catch (error) {
            customerSearchResults.innerHTML = '<div class="dropdown-item text-danger">Error loading customers.</div>';
        }
    }

    // Guest button handler
    selectGuestBtn.addEventListener('click', () => {
        document.getElementById('guest-customer-form').classList.remove('d-none');
        document.getElementById('selected-customer-display').classList.add('d-none');
        selectedCustomer = null;
        customerSearchInput.value = '';
        customerSearchResults.classList.remove('show');
    });

    // Update clear customer button
    document.getElementById('clear-customer').addEventListener('click', () => {
        selectedCustomer = null;
        selectedCustomerDisplay.classList.add('d-none');
        customerSearchInput.value = '';
        customerSearchResults.classList.remove('show');
        guestCustomerForm.classList.add('d-none');
    });

    window.setCustomer = (customer) => {
        selectedCustomer = customer;
        document.getElementById('cust-name').textContent = customer.name;
        document.getElementById('cust-phone').textContent = customer.phone || 'No phone';
        document.getElementById('cust-address').textContent = customer.address || 'No Address Provided';
        selectedCustomerDisplay.classList.remove('d-none');
        customerSearch.parentElement.classList.add('d-none');
        customerResults.classList.remove('show');
        if (customer.is_wholesale) { 
            pricingToggle.checked = true; 
            pricingToggle.dispatchEvent(new Event('change'));
        }
    };

    document.getElementById('clear-customer').addEventListener('click', () => {
        selectedCustomer = null;
        selectedCustomerDisplay.classList.add('d-none');
        customerSearch.parentElement.classList.remove('d-none');
        customerSearch.value = '';
        guestCustomerForm.classList.add('d-none');
    });

    // Customer Creation
    document.getElementById('btn-add-customer').addEventListener('click', () => {
        if (addCustModal) addCustModal.show();
    });

    document.getElementById('btn-save-customer').addEventListener('click', async () => {
        const name = document.getElementById('new-cust-name').value;
        const phone = document.getElementById('new-cust-phone').value;
        const email = document.getElementById('new-cust-email').value;
        const address = document.getElementById('new-cust-address').value;
        const isWholesaleCust = document.getElementById('new-cust-wholesale').checked;

        if (!name || !phone) return alert('Name and phone are required.');

        try {
            const response = await fetch(`<?php echo e(route('admin.pos.customers.store')); ?>`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
                body: JSON.stringify({ name, phone, email, address, is_wholesale: isWholesaleCust })
            });
            const result = await response.json();
            if (result.success) {
                setCustomer(result.customer);
                addCustModal.hide();
                document.getElementById('add-customer-form').reset();
            } else { alert(result.message); }
        } catch (error) { alert('Failed to save customer'); }
    });

    document.getElementById('btn-edit-customer').addEventListener('click', () => {
        if (!selectedCustomer) return;
        document.getElementById('edit-cust-id').value = selectedCustomer.id;
        document.getElementById('edit-cust-name').value = selectedCustomer.name;
        document.getElementById('edit-cust-phone').value = selectedCustomer.phone || '';
        document.getElementById('edit-cust-email').value = selectedCustomer.email || '';
        document.getElementById('edit-cust-address').value = selectedCustomer.address || '';
        document.getElementById('edit-cust-wholesale').checked = !!selectedCustomer.is_wholesale;
        if (updateCustModal) updateCustModal.show();
        else new bootstrap.Modal(document.getElementById('editCustomerModal')).show();
    });

    btnUpdateCustomer.addEventListener('click', async () => {
        const id = document.getElementById('edit-cust-id').value;
        const name = document.getElementById('edit-cust-name').value;
        const phone = document.getElementById('edit-cust-phone').value;
        const email = document.getElementById('edit-cust-email').value;
        const address = document.getElementById('edit-cust-address').value;
        const isWholesaleCust = document.getElementById('edit-cust-wholesale').checked;

        if (!name || !phone) return alert('Name and phone are required.');

        try {
            const url = `<?php echo e(route('admin.pos.customers.update', ':id')); ?>`.replace(':id', id);
            const response = await fetch(url, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
                body: JSON.stringify({ name, phone, email, address, is_wholesale: isWholesaleCust })
            });
            const result = await response.json();
            if (result.success) {
                setCustomer(result.customer);
                if (updateCustModal) updateCustModal.hide();
                else {
                    const inst = bootstrap.Modal.getInstance(document.getElementById('editCustomerModal'));
                    if (inst) inst.hide();
                }
            } else { alert(result.message); }
        } catch (error) { alert('Failed to update customer'); }
    });

    checkoutBtn.addEventListener('click', async () => {
        if (cart.length === 0) return alert('Cart is empty!');
        
        const guestName = document.getElementById('guest-name').value;
        const guestPhone = document.getElementById('guest-phone').value;
        const guestAddress = document.getElementById('guest-address').value;

        if (!selectedCustomer && (!guestName || !guestPhone)) {
            // If no customer selected, try to show guest form if not visible
            if (guestCustomerForm.classList.contains('d-none')) {
                guestCustomerForm.classList.remove('d-none');
                return alert('Please fill in Guest details or select a customer.');
            }
            return alert('Guest Name and Phone are required.');
        }

        const customerName = selectedCustomer ? selectedCustomer.name : guestName;
        const customerPhone = selectedCustomer ? selectedCustomer.phone : guestPhone;
        const customerAddress = selectedCustomer ? (selectedCustomer.address || '') : guestAddress;
        const salerOption = salerSelect.options[salerSelect.selectedIndex];
        
        const subtotal = cart.reduce((acc, item) => acc + ((calculateUnitPriceWithIndicator(item).price || 0) * item.quantity), 0);
        const vatAmount = hasVAT ? subtotal * 0.18 : 0;
        const totalAmount = (subtotal + vatAmount) || 0;
        const amountPaid = parseFloat(document.getElementById('amount-paid').value) || totalAmount;

        const payload = {
            customer_id: selectedCustomer ? selectedCustomer.id : null,
            customer_name: customerName,
            customer_phone: customerPhone,
            customer_address: customerAddress,
            saler_id: salerSelect.value,
            department_id: document.getElementById('department-select').value,
            items: cart.map(item => ({ id: item.id, name: item.name, quantity: item.quantity, price: (calculateUnitPriceWithIndicator(item).price || 0), variants: item.variants })),
            payment_method: document.querySelector('input[name="payment_method"]:checked').value,
            is_wholesale: isWholesale,
            total_amount: totalAmount,
            amount_paid: amountPaid,
            has_vat: hasVAT,
            vat_amount: vatAmount,
            order_type: document.getElementById('proforma-toggle').checked ? 'proforma' : 'sales_invoice',
            _token: '<?php echo e(csrf_token()); ?>'
        };

        checkoutBtn.disabled = true;
        checkoutBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        try {
            const response = await fetch(`<?php echo e(route('admin.pos.store')); ?>`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' 
                },
                body: JSON.stringify(payload)
            });
            
            const contentType = response.headers.get("content-type");
            let result;
            if (contentType && contentType.indexOf("application/json") !== -1) {
                result = await response.json();
            } else {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                alert('Fatal Error: Server returned non-JSON response. Please check if you are still logged in.');
                return;
            }
            
            if (response.ok && result.success) {
                const balance = totalAmount - amountPaid;
                lastOrderData = {
                    customer: { name: customerName, phone: customerPhone, address: customerAddress },
                    saler: { name: salerOption.text.trim(), phone: salerOption.dataset.phone || '' },
                    items: cart.map(item => ({
                        name: item.name,
                        quantity: item.quantity,
                        price: calculateUnitPriceWithIndicator(item).price,
                        total: calculateUnitPriceWithIndicator(item).price * item.quantity
                    })),
                    subtotal: subtotal,
                    vat: vatAmount,
                    total: totalAmount,
                    amountPaid: amountPaid,
                    balance: balance,
                    orderCode: result.order_code,
                    hasVAT: hasVAT,
                    isProforma: isProforma
                };
                printReceipt(lastOrderData);
                reprintBtn.classList.remove('d-none');
                cart = []; renderCart(); document.getElementById('clear-customer').click();
                document.getElementById('amount-paid').value = '';
                document.getElementById('balance-display').classList.add('d-none');
            } else {
                alert('Checkout Error: ' + (result.message || 'Unknown error occurred.'));
                console.error('Checkout failed:', result);
            }
        } catch (error) { 
            console.error('Fetch error:', error);
            alert('Checkout failed: ' + error.message + '. See browser console for details.'); 
        } finally { 
            checkoutBtn.disabled = false; 
            checkoutBtn.innerHTML = 'Complete Transaction'; 
        }
    });

    reprintBtn.addEventListener('click', () => { if (lastOrderData) printReceipt(lastOrderData); });

    function printReceipt(data) {
        const receiptWindow = window.open('', '_blank', 'width=400,height=700');
        const dateTimeStr = new Date().toLocaleString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).replace(',', '');
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value.toUpperCase();
        
        const receiptHtml = `
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    @page { margin: 0; }
                    body { font-family: 'Courier New', Courier, monospace; font-size: 13px; padding: 15px; width: 80mm; margin: 0 auto; line-height: 1.2; color: #000; text-transform: uppercase; }
                    .center { text-align: center; }
                    .header-top { font-weight: bold; font-size: 16px; margin-bottom: 2px; }
                    .header-sub { font-size: 11px; margin-bottom: 2px; }
                    .sep { border-top: 1px dashed #000; margin: 8px 0; }
                    .meta-row { display: flex; margin-bottom: 2px; }
                    .meta-label { width: 100px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 5px; }
                    th { border-bottom: 1px solid #000; text-align: left; padding: 5px 0; font-size: 11px; }
                    td { padding: 4px 0; vertical-align: top; font-size: 11px; }
                    .total-row { display: flex; justify-content: space-between; margin-top: 4px; font-weight: bold; }
                    .total-final { font-size: 16px; border-top: 1px solid #000; padding-top: 5px; margin-top: 5px; }
                    .footer-qr { margin: 15px 0; text-align: center; }
                    .footer-text { font-size: 11px; line-height: 1.3; }
                </style>
            </head>
            <body>
                <div class="center">
                    <div class="header-top">${data.isProforma ? 'PROFORMA INVOICE' : 'CHIBO BRANDS CO.LTD'}</div>
                    ${data.isProforma ? `<div style="font-size: 11px; margin-bottom: 5px;">CHIBO BRANDS CO.LTD</div>` : ''}
                    <div class="header-sub">KINONDONI, MWIJUMA ROAD</div>
                    <div class="header-sub">DAR ES SALAAM, TANZANIA</div>
                    <div class="header-sub">TEL: +255 655 392 319 | +255 711 711 111</div>
                    <div class="header-sub">TIN:  154 747 214</div>
                </div>
                
                <div class="sep"></div>
                
                <div class="meta-row"><span class="meta-label">${data.isProforma ? 'PROFORMA #' : 'ORDER #'}</span> <span>${data.orderCode}</span></div>
                <div class="meta-row"><span class="meta-label">DATE:</span> <span>${dateTimeStr}</span></div>
                <div class="meta-row"><span class="meta-label">CUSTOMER:</span> <span>${data.customer.name}</span></div>
                <div class="meta-row"><span class="meta-label">TEL:</span> <span>${data.customer.phone || 'N/A'}</span></div>
                <div class="meta-row"><span class="meta-label">LOC:</span> <span>${data.customer.address || 'N/A'}</span></div>
                <div class="meta-row"><span class="meta-label">ISSUED BY:</span> <span>${data.saler.name}</span></div>
                
                <div class="sep"></div>
                
                <table>
                    <thead>
                        <tr>
                            <th style="width: 45%;">ITEM</th>
                            <th style="width: 15%;">QTY</th>
                            <th style="width: 20%; text-align:right;">PRICE</th>
                            <th style="width: 20%; text-align:right;">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.items.map(i => `
                            <tr>
                                <td>${i.name}</td>
                                <td>${i.quantity} PCS</td>
                                <td style="text-align:right;">${formatNumber(i.price)}</td>
                                <td style="text-align:right;">${formatNumber(i.total)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                
                <div class="sep"></div>
                
                <div class="total-row">
                    <span>SUBTOTAL:</span>
                    <span>${formatNumber(data.subtotal || data.total)} TZS</span>
                </div>
                ${data.hasVAT ? `
                <div class="total-row">
                    <span>VAT (18%):</span>
                    <span>${formatNumber(data.vat)} TZS</span>
                </div>
                ` : ''}
                <div class="total-row total-final">
                    <span>TOTAL:</span>
                    <span>${formatNumber(data.total)} TZS</span>
                </div>
                ${!data.isProforma ? `
                <div class="total-row">
                    <span>AMOUNT PAID:</span>
                    <span>${formatNumber(data.amountPaid || data.total)} TZS</span>
                </div>
                ${(data.balance && data.balance > 0) ? `
                <div class="total-row" style="color: #d32f2f; font-weight: bold;">
                    <span>BALANCE:</span>
                    <span>${formatNumber(data.balance)} TZS</span>
                </div>
                ` : ''}
                ` : ''}
                
                <div style="margin-top: 10px; font-size: 11px;">
                    <div>PAYMENT METHOD: ${paymentMethod}</div>
                    <div>CHECKED BY: ${data.saler.name}${data.saler.phone ? ' (' + data.saler.phone + ')' : ''}</div>
                </div>
                
                <div class="footer-qr">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${data.orderCode}" width="100">
                </div>
                
                <div class="center footer-text">
                    <div>THANK YOU FOR SHOPPING WITH US!</div>
                    <div style="font-weight: bold; margin-top: 5px; font-size: 12px;">“CREATIVITY MEETS TECHNOLOGY”</div>
                    
                    <div style="font-weight: bold; margin-top: 5px;">WWW.CHIBOBRAND.COM</div>
                </div>
            </body>
            </html>
        `;
        receiptWindow.document.write(receiptHtml);
        receiptWindow.document.close();
        receiptWindow.onload = () => {
            setTimeout(() => {
                receiptWindow.print();
                setTimeout(() => receiptWindow.close(), 500);
            }, 300);
        };
    }

    function formatNumber(num) { return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","); }
});
</script>

<!-- jQuery (required for Select2) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 for Salesperson only
    $('#saler-select').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select salesperson',
        width: '100%'
    });

    // Handle customer clear
        document.getElementById('selected-customer-display').classList.add('d-none');
        document.getElementById('guest-customer-form').classList.add('d-none');
        selectedCustomer = null;
    });

    // Update clear customer button to reset select2
    const clearBtn = document.getElementById('clear-customer');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            $('#customer-select').val(null).trigger('change');
        });
    }
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/gotlaptopparts.com/Downloads/chibo_sales/resources/views/admin/pos/index.blade.php ENDPATH**/ ?>