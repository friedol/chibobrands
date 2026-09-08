@extends('layouts.admin')

@section('title', 'POS Terminal - CHIBO BRAND')

@push('styles')
    <!-- Plus Jakarta Sans & Outfit Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
@endpush

@section('content')
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
            <!-- Left Column: Products & Design Tasks -->
            <div class="col-lg-7 pos-product-section p-3">
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" id="posTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" id="design-tasks-tab" data-bs-toggle="tab"
                            data-bs-target="#design-tasks-content" type="button" role="tab"
                            aria-controls="design-tasks-content" aria-selected="true">
                            <i class="fas fa-pencil-ruler me-1"></i> Design Tasks
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="products-tab" data-bs-toggle="tab"
                            data-bs-target="#products-content" type="button" role="tab" aria-controls="products-content"
                            aria-selected="false">
                            <i class="fas fa-box me-1"></i> Products
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="posTabsContent"
                    style="overflow-y: auto; max-height: calc(100vh - 200px); overflow-x: hidden;">
                    
                    <!-- Design Tasks Tab -->
                    <div class="tab-pane fade show active" id="design-tasks-content" role="tabpanel" aria-labelledby="design-tasks-tab">
                        <!-- Search for Design Tasks -->
                        <div class="pos-search-wrapper mb-3">
                            <div class="input-group border">
                                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" id="design-task-search" class="form-control border-start-0 ps-0"
                                    placeholder="Search design tasks by name...">
                            </div>
                        </div>

                        <!-- Department Filters for Design Tasks -->
                        <div class="mb-3 d-flex flex-wrap gap-2 align-items-center" id="task-dept-filters">
                            <span class="text-muted small fw-bold text-uppercase me-2" style="font-size: 11px; letter-spacing: 0.5px;">Filter:</span>
                            <button type="button" class="btn btn-sm btn-outline-danger active rounded-pill px-3 py-1 text-uppercase fw-bold" onclick="filterTaskTypesByDept('all')" style="font-size: 10px; letter-spacing: 0.5px;">All</button>
                            @foreach($departments as $dept)
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 text-uppercase fw-bold" onclick="filterTaskTypesByDept({{ $dept->id }})" style="font-size: 10px; letter-spacing: 0.5px;">{{ $dept->name }}</button>
                            @endforeach
                        </div>

                        <div class="pos-product-grid" id="task-types-grid">
                            @foreach($taskTypes as $type)
                                <div class="product-card task-type-col" data-department-id="{{ $type->department_id ?? 'none' }}"
                                    onclick="selectTaskType({{ $type->id }}, '{{ $type->name }}', {{ $type->price }}, '{{ $type->description }}', {{ $type->department_id ?? 'null' }})">
                                    
                                    <!-- Card Image Header -->
                                    <div class="product-img-wrapper position-relative">
                                        @if($type->image_path)
                                            <img src="{{ asset('storage/' . $type->image_path) }}" alt="{{ $type->name }}" class="product-img" onerror="this.src='/images/service-placeholder.webp'">
                                        @else
                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                                <i class="fas fa-pencil-ruler fa-2x"></i>
                                            </div>
                                        @endif
                                        @if($type->department)
                                            <span class="position-absolute top-0 end-0 badge bg-danger m-2" style="font-size: 9px; opacity: 0.9;">{{ $type->department->name }}</span>
                                        @endif
                                    </div>

                                    <div class="product-info">
                                        <div class="product-name" style="font-size: 13px; font-weight: 700; height: auto; min-height: 2.6em; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 6px;">{{ $type->name }}</div>
                                        
                                        <div class="product-prices-wrapper">
                                            <div class="price-row active">
                                                <span class="price-label">Rate</span>
                                                <span style="font-weight: 700;">TZS {{ number_format($type->price) }}</span>
                                            </div>
                                        </div>

                                        <div class="product-stock d-flex justify-content-between align-items-center mt-auto pt-2">
                                            @if($type->department)
                                                <span class="badge bg-danger text-white px-2 py-1" style="font-size: 9px; font-weight: 600; border-radius: 2px; text-transform: uppercase;">
                                                    {{ $type->department->name }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary text-white px-2 py-1" style="font-size: 9px; font-weight: 600; border-radius: 2px; text-transform: uppercase;">
                                                    DESIGN
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Products Tab -->
                    <div class="tab-pane fade" id="products-content" role="tabpanel"
                        aria-labelledby="products-tab">
                        <div class="pos-search-wrapper mb-3">
                            <div class="input-group border">
                                <span class="input-group-text bg-white border-end-0"><i
                                        class="fas fa-search text-muted"></i></span>
                                <input type="text" id="product-search" class="form-control border-start-0 ps-0"
                                    placeholder="Search products by name, barcode, or nickname...">
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
                        <div class="p-4 border-bottom selection-section">
                            <!-- Customer Selector with Tabs/Switch -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold text-muted mb-0"
                                        style="font-size: 10px;">CUSTOMER</label>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal"
                                        style="font-size: 10px; padding: 2px 5px;">
                                        <i class="fas fa-plus"></i> New
                                    </button>
                                </div>

                                <!-- Search Form -->
                                <div id="cust-search-container">
                                    <div class="position-relative">
                                        <div class="input-group border">
                                            <span class="input-group-text bg-white border-0"><i
                                                    class="fas fa-search text-muted"></i></span>
                                            <input type="text" id="customer-search-input" class="form-control border-0"
                                                placeholder="Search customer by name or phone..." autocomplete="off">
                                        </div>
                                        <div id="customer-search-results" class="dropdown-menu w-100 border mt-1"
                                            style="max-height: 300px; overflow-y: auto;"></div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Selected Customer Details -->
                        <div id="selected-customer-display" class="d-none mb-3">
                            <div class="alert alert-primary p-2 border mb-0 d-flex justify-content-between align-items-center" style="background: #e7f1ff; border-radius: 8px;">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px; font-weight: bold; background: var(--pos-primary-grad) !important;">
                                        C
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-primary small" id="cust-name"></h6>
                                        <div class="small text-muted mt-0" style="font-size: 11px;">
                                            <span><i class="fas fa-phone-alt me-1 opacity-75"></i><span id="cust-phone"></span></span>
                                            <span class="ms-2"><i class="fab fa-whatsapp me-1 text-success"></i><span id="cust-whatsapp"></span></span>
                                        </div>
                                        <div class="small text-muted" style="font-size: 10px;">
                                            <i class="fas fa-map-marker-alt me-1 opacity-75"></i><span id="cust-address"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-1 align-items-center">
                                    <button type="button" class="btn btn-xs btn-outline-secondary p-1" style="font-size: 10px; border-radius: 4px; line-height: 1; padding: 3px 6px !important;" id="btn-edit-customer" title="Edit Customer">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-danger p-1" style="font-size: 10px; border-radius: 4px; line-height: 1; padding: 3px 6px !important;" id="clear-customer" title="Deselect Customer">
                                        <i class="fas fa-trash-alt"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>



                        <!-- Product Specific Fields (Salesperson, Department, Wholesale) -->
                        <div id="pos-product-fields" class="d-none">
                            <!-- Salesperson Selector with Select2 -->
                            <div class="mb-3 px-3">
                                <label class="form-label small fw-bold text-muted mb-1"
                                    style="font-size: 10px;">SALESPERSON</label>
                                <select id="saler-select" class="form-select form-select-sm" style="width: 100%;">
                                    @foreach($salers as $saler)
                                        <option value="{{ $saler->id }}" {{ $saler->id == $currentUser->id ? 'selected' : '' }}
                                            data-phone="{{ $saler->phone }}">
                                            {{ $saler->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3 px-3">
                                <label class="form-label small fw-bold text-muted mb-1"
                                    style="font-size: 10px;">DEPARTMENT</label>
                                <select id="department-select" class="form-select form-select-sm" style="width: 100%;">
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="px-3">
                                <div class="form-check form-switch pt-1 mb-3">
                                    <input class="form-check-input" type="checkbox" id="pricing-toggle">
                                    <label class="form-check-label fw-bold text-primary" for="pricing-toggle"
                                        style="font-size: 10px;">Switch to Wholesale</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Cart Items -->
                    <div id="cart-items" class="p-4" style="overflow-y: auto; max-height: 400px;">
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-shopping-basket fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0">Cart is empty</p>
                        </div>
                    </div>

                    <!-- Totals & Checkout -->
                    <div class="p-4 bg-white border-top"
                        style="padding-top: 5px !important; padding-bottom: 5px !important;">
                        <div class="d-flex gap-2">
                            <div class="form-check form-switch mb-2 p-1 bg-white rounded border d-flex justify-content-between align-items-center flex-fill"
                                style="background-color: #f8f9fa !important;">
                                <label class="form-check-label small fw-bold text-primary mb-0 ms-2" for="vat-toggle"
                                    style="font-size: 10px;">
                                    <i class="fas fa-percent me-1"></i> VAT
                                </label>
                                <input class="form-check-input ms-0" type="checkbox" id="vat-toggle">
                            </div>
                            <div class="form-check form-switch mb-2 p-1 bg-white rounded border d-flex justify-content-between align-items-center flex-fill"
                                style="background-color: #f8f9fa !important;">
                                <label class="form-check-label small fw-bold text-primary mb-0 ms-2" for="proforma-toggle"
                                    style="font-size: 10px;">
                                    <i class="fas fa-file-invoice me-1"></i> PROFORMA
                                </label>
                                <input class="form-check-input ms-0" type="checkbox" id="proforma-toggle">
                            </div>
                        </div>
                        <!-- Order-Level Discount & Delivery Fee Summary Inputs -->
                        <div class="row g-2 mb-2 p-2 bg-light rounded border">
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark mb-1" style="font-size: 10px;"><i class="fas fa-tag text-danger me-1"></i>DISCOUNT (TZS)</label>
                                <input type="number" id="order-level-discount" class="form-control form-control-sm border-0 shadow-sm fw-bold" value="0" min="0" placeholder="0.00" style="font-size: 11px;">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-dark mb-1" style="font-size: 10px;"><i class="fas fa-truck text-primary me-1"></i>DELIVERY FEE (TZS)</label>
                                <input type="number" id="order-level-delivery" class="form-control form-control-sm border-0 shadow-sm fw-bold" value="0" min="0" placeholder="0.00" style="font-size: 11px;">
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

                        <!-- Payment Section Type Toggle -->
                        <div class="mb-3" id="payment-type-container">
                            <label class="small fw-bold text-muted d-block mb-1">PAYMENT TYPE</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="payment_type" id="pay-type-now" value="pay_now" checked>
                                <label class="btn btn-outline-primary py-2" for="pay-type-now">
                                    <i class="fas fa-wallet me-1"></i> Pay Now
                                </label>

                                <input type="radio" class="btn-check" name="payment_type" id="pay-type-later" value="pay_later">
                                <label class="btn btn-outline-primary py-2" for="pay-type-later">
                                    <i class="fas fa-clock me-1"></i> Pay Later
                                </label>
                            </div>
                        </div>

                        <!-- Split Payment Methods (Hidden if Pay Later is active) -->
                        <div id="split-payment-methods-wrapper">
                            <label class="small fw-bold text-muted d-block mb-1">ENTER AMOUNT PAID</label>
                            <div class="row g-2 mb-2">
                                <div class="col-12">
                                    <div class="input-group border rounded">
                                        <span class="input-group-text bg-white border-0" style="width: 100px;">
                                            <i class="fas fa-money-bill-wave me-2 text-success"></i> Cash
                                        </span>
                                        <input type="number" id="pay-cash-amount" class="form-control border-0 payment-split-input" placeholder="TZS 0" min="0" step="0.01">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="input-group border rounded">
                                        <span class="input-group-text bg-white border-0" style="width: 100px;">
                                            <i class="fas fa-mobile-alt me-2 text-primary"></i> Mobile
                                        </span>
                                        <input type="number" id="pay-mobile-amount" class="form-control border-0 payment-split-input" placeholder="TZS 0" min="0" step="0.01">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="input-group border rounded">
                                        <span class="input-group-text bg-white border-0" style="width: 100px;">
                                            <i class="fas fa-university me-2 text-danger"></i> Bank
                                        </span>
                                        <input type="number" id="pay-bank-amount" class="form-control border-0 payment-split-input" placeholder="TZS 0" min="0" step="0.01">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Keep hidden input for amount-paid for backward compatibility -->
                        <input type="hidden" id="amount-paid" value="">

                        <div id="balance-display-container" class="mb-2">
                            <div id="balance-display" class="p-2 bg-light rounded border d-none">
                                <div class="d-flex justify-content-between">
                                    <span class="small fw-bold text-muted">Balance:</span>
                                    <span id="balance-amount" class="small fw-bold text-danger">0</span>
                                </div>
                            </div>
                        </div>

                        <button id="checkout-btn"
                            class="btn btn-primary btn-lg w-100 fw-bold py-2 checkout-btn-now text-uppercase">
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

    <!-- Design Task Details Modal -->
    <div class="modal fade" id="designTaskModal" tabindex="-1" aria-labelledby="designTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-primary" id="designTaskModalLabel">
                        <i class="fas fa-pencil-ruler me-2"></i>Design Task Details
                    </h5>
                    <button type="button" class="btn-close" onclick="hideDesignTaskForm()" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="pos-task-type-id">

                    <div class="mb-3">
                        <label class="form-label fw-bold small mb-1" style="font-size: 10px;">TASK TITLE <span class="text-danger">*</span></label>
                        <input type="text" id="pos-task-title" class="form-control form-control-sm" placeholder="Enter task title" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small mb-1" style="font-size: 10px;">DESCRIPTION</label>
                        <textarea id="pos-task-description" class="form-control form-control-sm" rows="2" placeholder="Describe design requirements..."></textarea>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold small mb-1" style="font-size: 10px;">PRIORITY <span class="text-danger">*</span></label>
                                <select id="pos-task-priority" class="form-select form-select-sm" required>
                                    <option value="1">High</option>
                                    <option value="3" selected>Medium</option>
                                    <option value="5">Low</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold small mb-1" style="font-size: 10px;">QTY <span class="text-danger">*</span></label>
                                <input type="number" id="pos-task-qty" class="form-control form-control-sm" value="1" step="0.01" min="0.01" required oninput="calculatePosTaskTotal()">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold small mb-1" style="font-size: 10px;">RATE <span class="text-danger">*</span></label>
                                <input type="number" id="pos-task-rate" class="form-control form-control-sm" value="0" step="0.01" min="0" required oninput="calculatePosTaskTotal()">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small mb-1" style="font-size: 10px;">AMOUNT (TZS)</label>
                        <input type="number" id="pos-task-amount" class="form-control form-control-sm bg-white" value="0" readonly>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold small mb-1" style="font-size: 10px;">DEPARTMENT <span class="text-danger">*</span></label>
                                <select id="pos-task-department" class="form-select form-select-sm" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold small mb-1" style="font-size: 10px;">SALER</label>
                                <select id="pos-task-saler" class="form-select form-select-sm">
                                    <option value="">Not specified</option>
                                    @foreach($salers as $saler)
                                        <option value="{{ $saler->id }}">{{ $saler->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold small mb-1" style="font-size: 10px;">DESIGNER</label>
                                <select id="pos-task-designer" class="form-select form-select-sm">
                                    <option value="" data-department-id="">Not assigned</option>
                                    @foreach($designers as $designer)
                                        @php
                                            $deptIds = $designer->department_ids ?? [];
                                            if ($designer->department_id && !in_array($designer->department_id, $deptIds)) {
                                                $deptIds[] = $designer->department_id;
                                            }
                                            $deptIdsStr = implode(',', $deptIds);
                                        @endphp
                                        <option value="{{ $designer->id }}" data-department-id="{{ $designer->department_id ?? '' }}" data-department-ids="{{ $deptIdsStr }}">{{ $designer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold small mb-1" style="font-size: 10px;">OPERATOR</label>
                                <select id="pos-task-operator" class="form-select form-select-sm">
                                    <option value="" data-department-id="">Not assigned</option>
                                    @foreach($operators as $operator)
                                        @php
                                            $deptIds = $operator->department_ids ?? [];
                                            if ($operator->department_id && !in_array($operator->department_id, $deptIds)) {
                                                $deptIds[] = $operator->department_id;
                                            }
                                            $deptIdsStr = implode(',', $deptIds);
                                        @endphp
                                        <option value="{{ $operator->id }}" data-department-id="{{ $operator->department_id ?? '' }}" data-department-ids="{{ $deptIdsStr }}">{{ $operator->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small mb-1" style="font-size: 10px;">INSTRUCTIONS</label>
                        <textarea id="pos-task-instructions" class="form-control form-control-sm" rows="2" placeholder="Instructions for the team..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small mb-1" style="font-size: 10px;">DEADLINE</label>
                        <div class="row g-1">
                            <div class="col-3">
                                <select id="pos-deadline-day" class="form-select form-select-sm" onchange="updatePosDeadline()">
                                    <option value="">Day</option>
                                    @for($i = 1; $i <= 31; $i++)
                                        <option value="{{ sprintf('%02d', $i) }}">{{ sprintf('%02d', $i) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-3">
                                <select id="pos-deadline-month" class="form-select form-select-sm" onchange="updatePosDeadline()">
                                    <option value="">Month</option>
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ sprintf('%02d', $i) }}">{{ sprintf('%02d', $i) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-3">
                                <select id="pos-deadline-year" class="form-select form-select-sm" onchange="updatePosDeadline()">
                                    <option value="">Year</option>
                                    @for($i = now()->year; $i <= now()->year + 2; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-3">
                                <input type="time" id="pos-deadline-time" class="form-control form-control-sm" onchange="updatePosDeadline()">
                            </div>
                        </div>
                        <input type="hidden" id="pos-task-deadline">
                    </div>

                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" onclick="hideDesignTaskForm()">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" id="pos-add-to-cart-btn" class="btn btn-primary fw-bold px-4" onclick="addDesignTaskToCart()">
                        <i class="fas fa-plus-circle me-1"></i> ADD TO CART
                    </button>
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
                                <div class="small fw-bold text-primary mb-2 text-uppercase"
                                    style="letter-spacing: 1px; font-size: 11px;">
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
                    <button type="button" class="btn btn-outline-primary px-4" data-bs-dismiss="modal"
                        style="font-size: 12px;">Cancel</button>
                    <button type="button" class="btn btn-primary px-4 fw-bold" id="add-to-cart-confirm"
                        style="font-size: 12px;">ADD TO CART</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border">
                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title fw-bold text-dark" style="font-size: 15px;"><i class="fas fa-user-plus me-2 text-primary"></i>New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="add-customer-form">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small mb-1">FULL NAME <span class="text-danger">*</span></label>
                                <input type="text" id="new-cust-name" class="form-control" placeholder="Enter customer name" required style="font-size: 12px;">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small mb-1">PHONE NUMBER <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" id="new-cust-phone-prefix" class="form-control text-center fw-bold"
                                           value="255" maxlength="5"
                                           style="max-width:62px;font-size:12px;border-right:0;background:#f8f9fa;"
                                           title="Country code (editable)">
                                    <input type="text" id="new-cust-phone" class="form-control" placeholder="Phone Number *" required style="font-size: 12px;">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small mb-1">WHATSAPP NUMBER</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fab fa-whatsapp text-success"></i></span>
                                    <input type="text" id="new-cust-whatsapp" class="form-control" placeholder="WhatsApp Number (Optional)" style="font-size: 12px;">
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small mb-1">EMAIL ADDRESS</label>
                                <input type="email" id="new-cust-email" class="form-control" placeholder="customer@example.com" style="font-size: 12px;">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small mb-1">COMPANY NAME</label>
                                <input type="text" id="new-cust-company" class="form-control" placeholder="Company Name (Optional)" style="font-size: 12px;">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small mb-1">BUSINESS TYPE</label>
                                <select id="new-cust-business-type" class="form-select" style="font-size: 12px;">
                                    <option value="">Select Business Type</option>
                                    <option value="retail">Retail Store</option>
                                    <option value="wholesale">Wholesale Distributor</option>
                                    <option value="printing">Printing Company</option>
                                    <option value="advertising">Advertising Agency</option>
                                    <option value="corporate">Corporate</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small mb-1">CUSTOMER SOURCE</label>
                                <select id="new-cust-source" class="form-select" style="font-size: 12px;">
                                    <option value="">Select Marketing Source</option>
                                    @php $sources = \App\Models\CustomerSource::active()->get(); @endphp
                                    @foreach($sources as $source)
                                        <option value="{{ $source->name }}">{{ $source->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small mb-1">REGION</label>
                                <select id="new-cust-region" class="form-select" style="font-size: 12px;">
                                    <option value="">Select Region (Optional)</option>
                                    @foreach($regions as $region)
                                        <option value="{{ $region->id }}">{{ $region->region_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small mb-1">DISTRICT</label>
                                <select id="new-cust-district" class="form-select" style="font-size: 12px;">
                                    <option value="">Select District (Optional)</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-8">
                                <label class="form-label fw-bold small mb-1">ADDRESS / LOCATION</label>
                                <input type="text" id="new-cust-address" class="form-control" placeholder="City, Area, Street" style="font-size: 12px;">
                            </div>
                            <div class="col-12 col-md-4 d-flex align-items-center">
                                <div class="form-check form-switch p-0 d-flex justify-content-between align-items-center w-100 mt-md-4">
                                    <label class="form-check-label fw-bold small me-2" for="new-cust-wholesale">WHOLESALE CUSTOMER</label>
                                    <input class="form-check-input ms-0" type="checkbox" id="new-cust-wholesale">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="font-size: 12px;">Cancel</button>
                    <button type="button" class="btn btn-primary px-4 fw-bold" id="btn-save-customer" style="font-size: 12px;">SAVE CUSTOMER</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border">
                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title fw-bold text-dark" style="font-size: 15px;"><i class="fas fa-user-edit me-2 text-primary"></i>Edit Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="edit-customer-form">
                        <input type="hidden" id="edit-cust-id">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small mb-1">FULL NAME <span class="text-danger">*</span></label>
                                <input type="text" id="edit-cust-name" class="form-control" placeholder="Enter customer name" required style="font-size: 12px;">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small mb-1">PHONE NUMBER <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" id="edit-cust-phone-prefix" class="form-control text-center fw-bold"
                                           value="255" maxlength="5"
                                           style="max-width:62px;font-size:12px;border-right:0;background:#f8f9fa;"
                                           title="Country code (editable)">
                                    <input type="text" id="edit-cust-phone" class="form-control" placeholder="Phone Number *" required style="font-size: 12px;">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small mb-1">WHATSAPP NUMBER</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fab fa-whatsapp text-success"></i></span>
                                    <input type="text" id="edit-cust-whatsapp" class="form-control" placeholder="WhatsApp Number" style="font-size: 12px;">
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small mb-1">EMAIL ADDRESS</label>
                                <input type="email" id="edit-cust-email" class="form-control" placeholder="customer@example.com" style="font-size: 12px;">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small mb-1">COMPANY NAME</label>
                                <input type="text" id="edit-cust-company" class="form-control" placeholder="Company Name" style="font-size: 12px;">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small mb-1">BUSINESS TYPE</label>
                                <select id="edit-cust-business-type" class="form-select form-select-sm" style="font-size: 12px;">
                                    <option value="">Select Business Type</option>
                                    <option value="retail">Retail Store</option>
                                    <option value="wholesale">Wholesale Distributor</option>
                                    <option value="printing">Printing Company</option>
                                    <option value="advertising">Advertising Agency</option>
                                    <option value="corporate">Corporate</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small mb-1">CUSTOMER SOURCE</label>
                                <select id="edit-cust-source" class="form-select" style="font-size: 12px;">
                                    <option value="">Select Marketing Source</option>
                                    @php $sources = \App\Models\CustomerSource::active()->get(); @endphp
                                    @foreach($sources as $source)
                                        <option value="{{ $source->name }}">{{ $source->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small mb-1">REGION</label>
                                <select id="edit-cust-region" class="form-select" style="font-size: 12px;">
                                    <option value="">Select Region (Optional)</option>
                                    @foreach($regions as $region)
                                        <option value="{{ $region->id }}">{{ $region->region_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small mb-1">DISTRICT</label>
                                <select id="edit-cust-district" class="form-select" style="font-size: 12px;">
                                    <option value="">Select District (Optional)</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-8">
                                <label class="form-label fw-bold small mb-1">ADDRESS/LOCATION</label>
                                <input type="text" id="edit-cust-address" class="form-control" placeholder="City, Area, Street" style="font-size: 12px;">
                            </div>
                            <div class="col-12 col-md-4 d-flex align-items-center">
                                <div class="form-check form-switch p-0 d-flex justify-content-between align-items-center w-100 mt-md-4">
                                    <label class="form-check-label fw-bold small me-2" for="edit-cust-wholesale">WHOLESALE CUSTOMER</label>
                                    <input class="form-check-input ms-0" type="checkbox" id="edit-cust-wholesale">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal" style="font-size: 12px;">Cancel</button>
                    <button type="button" class="btn btn-primary px-4 fw-bold" id="btn-update-customer" style="font-size: 12px;">UPDATE CUSTOMER</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Modern Premium POS Style System */
        :root {
            --pos-bg: #f8fafc;
            --pos-primary: #dc2626; /* Premium Red matching admin sidebar theme */
            --pos-primary-rgb: 220, 38, 38;
            --pos-primary-grad: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            --pos-success-grad: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --pos-secondary: #475569;
            --pos-accent: #b91c1c;
            --pos-accent-rgb: 185, 28, 28;
            --border-color: #e2e8f0;
            --pos-card-bg: #ffffff;
            --pos-text-main: #0f172a;
            --pos-text-muted: #64748b;
            --pos-shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.03);
            --pos-shadow-md: 0 10px 25px -4px rgba(0, 0, 0, 0.05);
            --pos-shadow-lg: 0 20px 40px -4px rgba(0, 0, 0, 0.08);
            --pos-radius: 16px;
            --pos-font-sans: 'Plus Jakarta Sans', 'Nunito Sans', system-ui, -apple-system, sans-serif;
        }

        .btn-primary {
            background: var(--pos-primary-grad) !important;
            border: none !important;
            box-shadow: 0 4px 12px rgba(var(--pos-primary-rgb), 0.2);
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(var(--pos-primary-rgb), 0.3);
            opacity: 0.95;
        }

        .text-primary {
            color: var(--pos-primary) !important;
        }

        .bg-primary {
            background: var(--pos-primary-grad) !important;
        }

        .btn-outline-primary {
            color: var(--pos-primary) !important;
            border-color: rgba(var(--pos-primary-rgb), 0.3) !important;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-outline-primary:hover {
            background: var(--pos-primary-grad) !important;
            border-color: transparent !important;
            color: #fff !important;
            box-shadow: 0 4px 10px rgba(var(--pos-primary-rgb), 0.15);
        }

        /* Modern Navigation Pill Tabs */
        .nav-tabs {
            border-bottom: none;
            background: #e2e8f0;
            padding: 4px;
            border-radius: 12px;
            display: inline-flex;
            gap: 4px;
            margin-bottom: 1.5rem !important;
        }

        .nav-tabs .nav-item {
            margin-bottom: 0;
        }

        .nav-tabs .nav-link {
            border: none !important;
            border-radius: 9px !important;
            color: var(--pos-text-muted) !important;
            font-size: 13px !important;
            padding: 8px 18px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: transparent !important;
        }

        .nav-tabs .nav-link:hover {
            color: var(--pos-text-main) !important;
        }

        .nav-tabs .nav-link.active {
            background: #ffffff !important;
            color: var(--pos-primary) !important;
            font-weight: 700 !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
        }

        .pos-container {
            background: var(--pos-bg);
            min-height: calc(100vh - 60px);
            display: flex;
            flex-direction: column;
            font-family: var(--pos-font-sans);
            font-size: 13px;
            color: var(--pos-text-main);
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

        #pos-terminal.fullscreen-mode .pos-container {
            height: calc(100vh - 65px) !important;
            overflow: hidden !important;
        }

        #pos-terminal.fullscreen-mode .pos-cart-section {
            height: calc(100vh - 65px) !important;
            overflow-y: auto !important;
        }

        .pos-header {
            background: #ffffff;
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            height: 65px;
            box-shadow: var(--pos-shadow-sm);
        }

        .pos-header h4 {
            font-family: 'Outfit', var(--pos-font-sans);
            font-size: 18px !important;
            letter-spacing: 0.5px;
            color: var(--pos-text-main);
        }

        .pos-main-content {
            flex-grow: 1;
        }

        .pos-product-section {
            min-height: calc(100vh - 65px);
            background: var(--pos-bg);
            padding: 24px !important;
        }

        .pos-cart-section {
            background: #ffffff;
            border-left: 1px solid var(--border-color);
            height: auto;
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.02);
        }

        .pos-cart-section .card {
            background: transparent !important;
            height: auto;
        }

        .pos-cart-section .card-header {
            padding: 18px 24px !important;
            border-bottom: 1px solid var(--border-color) !important;
        }

        .pos-cart-section .card-header h5 {
            font-family: 'Outfit', var(--pos-font-sans);
            font-size: 16px !important;
            font-weight: 700 !important;
            color: var(--pos-text-main);
        }

        /* Compact Typography */
        h4, .h4, h5, .h5, h6, .h6 {
            font-family: 'Outfit', var(--pos-font-sans);
        }

        .pos-cart-section .form-label {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            color: var(--pos-text-muted);
        }

        .pos-cart-section .form-control,
        .pos-cart-section .form-select,
        .pos-cart-section .input-group-text,
        #customer-search-input {
            font-size: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: #f8fafc;
            color: var(--pos-text-main);
            transition: all 0.2s ease;
        }

        .pos-cart-section .form-control:focus,
        .pos-cart-section .form-select:focus,
        #customer-search-input:focus {
            border-color: var(--pos-primary);
            box-shadow: 0 0 0 4px rgba(var(--pos-primary-rgb), 0.1);
            background: #ffffff;
        }

        .pos-cart-section .btn-lg {
            font-size: 14px;
            padding: 12px;
            border-radius: 12px;
        }

        .pos-search-wrapper .input-group {
            border: 1px solid var(--border-color) !important;
            border-radius: var(--pos-radius);
            box-shadow: var(--pos-shadow-sm);
            background: #ffffff;
            transition: all 0.2s ease;
            overflow: hidden;
            padding: 2px 4px;
        }

        .pos-search-wrapper .input-group:focus-within {
            border-color: var(--pos-primary) !important;
            box-shadow: 0 0 0 4px rgba(var(--pos-primary-rgb), 0.1) !important;
        }

        .pos-search-wrapper .form-control {
            border: none !important;
            box-shadow: none !important;
            background: transparent;
            font-weight: 500;
            font-size: 13px !important;
            height: 42px;
        }

        .pos-search-wrapper .input-group-text {
            border: none !important;
            background: transparent !important;
            font-size: 16px;
        }

        /* Modern Product Grid & Cards */
        .pos-product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 16px;
            padding: 4px;
        }

        .product-card {
            background: var(--pos-card-bg);
            border-radius: var(--pos-radius);
            padding: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            height: 100%;
            position: relative;
            overflow: hidden;
            box-shadow: var(--pos-shadow-sm);
        }

        .product-card:hover {
            border-color: rgba(var(--pos-primary-rgb), 0.3);
            transform: translateY(-4px);
            box-shadow: var(--pos-shadow-md);
        }

        .product-img-wrapper {
            position: relative;
            width: 100%;
            height: 140px;
            margin-bottom: 12px;
            overflow: hidden;
            border-radius: 12px;
            background: #f1f5f9;
        }

        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .product-card:hover .product-img {
            transform: scale(1.08);
        }

        .product-info {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-name {
            font-family: var(--pos-font-sans);
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--pos-text-main);
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
            margin-top: auto;
            gap: 4px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price-row.active {
            color: var(--pos-text-main);
            font-weight: 800;
            font-size: 14px;
            font-family: 'Outfit', var(--pos-font-sans);
        }

        .price-row.inactive {
            color: var(--pos-text-muted);
            font-size: 11px;
            opacity: 0.6;
        }

        .price-label {
            font-size: 9px;
            text-transform: uppercase;
            font-weight: 700;
            background: #f1f5f9;
            color: var(--pos-text-muted);
            padding: 2px 6px;
            border-radius: 4px;
        }

        .price-row.active .price-label {
            background: rgba(var(--pos-primary-rgb), 0.08);
            color: var(--pos-primary);
        }

        .product-stock {
            font-size: 11px;
            color: var(--pos-text-muted);
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
            font-weight: 500;
        }

        .price-tier-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: var(--pos-primary-grad);
            color: #fff;
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 800;
            z-index: 2;
            box-shadow: 0 4px 10px rgba(var(--pos-primary-rgb), 0.2);
            letter-spacing: 0.5px;
        }

        .badge {
            border-radius: 8px !important;
            padding: 4px 8px !important;
            font-family: var(--pos-font-sans);
            font-weight: 700;
        }

        .badge.bg-primary {
            background: rgba(var(--pos-primary-rgb), 0.08) !important;
            color: var(--pos-primary) !important;
            border: 1px solid rgba(var(--pos-primary-rgb), 0.15) !important;
        }

        .badge.bg-danger {
            background: rgba(239, 68, 68, 0.08) !important;
            color: #ef4444 !important;
            border: 1px solid rgba(239, 68, 68, 0.15) !important;
        }

        /* Cart Styles */
        .cart-item {
            background: #ffffff;
            border-radius: var(--pos-radius);
            padding: 12px 14px;
            margin-bottom: 8px;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
            box-shadow: var(--pos-shadow-sm);
        }

        .cart-item:hover {
            border-color: rgba(var(--pos-primary-rgb), 0.2);
            box-shadow: var(--pos-shadow-md);
        }

        .cart-item-info {
            flex-grow: 1;
        }

        .cart-item-name {
            font-weight: 700;
            font-size: 13px;
            color: var(--pos-text-main);
            margin-bottom: 2px;
        }

        .cart-item-variant {
            font-size: 10px;
            color: var(--pos-primary);
            background: rgba(var(--pos-primary-rgb), 0.06);
            display: inline-block;
            padding: 2px 8px;
            border-radius: 6px;
            margin-top: 4px;
            font-weight: 600;
            border: 1px solid rgba(var(--pos-primary-rgb), 0.1);
        }

        .cart-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f1f5f9;
            padding: 2px;
            border-radius: 8px;
        }

        .qty-btn {
            width: 26px;
            height: 26px;
            border-radius: 6px;
            border: none;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 11px;
            color: var(--pos-text-main);
            box-shadow: var(--pos-shadow-sm);
            transition: all 0.2s ease;
        }

        .qty-btn:hover {
            background: var(--pos-primary);
            color: #ffffff;
        }

        .qty-input {
            width: 52px;
            min-width: 52px;
            text-align: center;
            border: none;
            background: transparent;
            font-size: 13px;
            font-weight: 800;
            color: var(--pos-text-main);
        }

        .remove-item {
            color: #ef4444;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .remove-item:hover {
            background: rgba(239, 68, 68, 0.1);
            transform: scale(1.05);
        }

        /* Modal Custom Style */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: var(--pos-shadow-lg);
            overflow: hidden;
        }

        .modal-header {
            border-bottom: 1px solid var(--border-color);
            background: #f8fafc;
            padding: 20px 24px;
        }

        .modal-body {
            padding: 24px;
        }

        .variant-option-btn {
            padding: 10px 18px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: #ffffff;
            font-size: 12px;
            font-weight: 600;
            margin-right: 8px;
            margin-bottom: 8px;
            transition: all 0.2s ease;
            box-shadow: var(--pos-shadow-sm);
        }

        .variant-option-btn:hover {
            border-color: var(--pos-primary);
            color: var(--pos-primary);
        }

        .variant-option-btn.active {
            background: var(--pos-primary-grad);
            color: #ffffff;
            border-color: transparent;
            box-shadow: 0 4px 10px rgba(var(--pos-primary-rgb), 0.25);
        }

        /* Skeleton Loader */
        .skeleton {
            background: #f1f5f9;
            background: linear-gradient(90deg, #f1f5f9 25%, #f8fafc 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 12px;
        }

        /* Premium Completed Check Transaction Trigger */
        .checkout-btn-now {
            background: var(--pos-primary-grad) !important;
            border: none !important;
            color: #fff !important;
            font-size: 14px !important;
            font-weight: 800 !important;
            padding: 14px !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 15px rgba(var(--pos-primary-rgb), 0.3) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            letter-spacing: 0.5px;
        }

        .checkout-btn-now:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 20px rgba(var(--pos-primary-rgb), 0.4) !important;
        }

        .checkout-btn-now:active {
            transform: translateY(0) !important;
        }

        .tier-active-indicator {
            font-size: 11px;
            color: #10b981;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 4px;
        }

        /* Split Payments Rounded Group */
        #split-payment-methods-wrapper .input-group {
            border: 1px solid var(--border-color);
            background: #f8fafc;
            transition: all 0.2s ease;
            box-shadow: var(--pos-shadow-sm);
            border-radius: 10px;
            overflow: hidden;
        }

        #split-payment-methods-wrapper .input-group:focus-within {
            border-color: var(--pos-primary);
            box-shadow: 0 0 0 4px rgba(var(--pos-primary-rgb), 0.1);
            background: #ffffff;
        }

        #split-payment-methods-wrapper .input-group-text {
            background: transparent !important;
            border: none !important;
            font-weight: 700;
            color: var(--pos-text-main);
        }

        #split-payment-methods-wrapper input {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            font-weight: 800;
            color: var(--pos-text-main);
            text-align: right;
            padding-right: 15px;
        }

        /* Beautiful Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 100px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* ── PAYMENT TYPE BUTTONS ─────────────────────────────────── */
        #payment-type-container .btn-group {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            width: 100% !important;
            align-items: stretch !important;
        }
        #payment-type-container .btn-group .btn {
            flex: 1 1 0% !important;
            min-width: 0 !important;
            white-space: nowrap;
            text-align: center;
        }
        /* Active/checked state — red */
        .btn-check:checked + .btn-outline-primary,
        .btn-check:checked + .btn-outline-primary:focus {
            background: var(--pos-primary-grad) !important;
            border-color: var(--pos-primary) !important;
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(var(--pos-primary-rgb), 0.3) !important;
        }

        /* ── RESPONSIVE: MOBILE ────────────────────────────────────── */
        @media (max-width: 991.98px) {
            .pos-product-section {
                min-height: unset !important;
                padding: 14px !important;
            }
            .pos-cart-section {
                border-left: none !important;
                border-top: 1px solid var(--border-color);
            }
            .tab-content {
                max-height: 45vh !important;
            }
        }

        @media (max-width: 767.98px) {
            /* Product/task view capped shorter so cart is visible without scrolling */
            .tab-content {
                max-height: 38vh !important;
            }
            /* 2 cards per row */
            .pos-product-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 8px !important;
            }
            .product-card {
                padding: 7px !important;
                border-radius: 10px !important;
            }
            /* Shorter image so 2 cards fit comfortably in the view */
            .product-img-wrapper {
                height: 75px !important;
                border-radius: 7px !important;
                margin-bottom: 6px !important;
            }
            .product-name {
                font-size: 11px !important;
                height: auto !important;
                min-height: 2em !important;
                margin-bottom: 3px !important;
                -webkit-line-clamp: 2 !important;
            }
            .price-row.active {
                font-size: 11px !important;
            }
            .price-label {
                font-size: 8px !important;
                padding: 1px 4px !important;
            }
            .product-stock {
                font-size: 9px !important;
                margin-top: 3px !important;
            }
            #task-dept-filters .btn {
                font-size: 9px !important;
                padding: 3px 8px !important;
            }
            .pos-header {
                padding: 0.5rem 1rem !important;
                height: 55px !important;
            }
            .pos-header h4 {
                font-size: 14px !important;
            }
            .nav-tabs {
                display: flex !important;
                width: 100% !important;
            }
            .nav-tabs .nav-item {
                flex: 1;
            }
            .nav-tabs .nav-link {
                text-align: center !important;
                padding: 8px 6px !important;
                font-size: 11px !important;
            }
        }

        @media (max-width: 399.98px) {
            .tab-content {
                max-height: 34vh !important;
            }
            .pos-product-grid {
                gap: 6px !important;
            }
            .product-img-wrapper {
                height: 60px !important;
            }
            .product-name {
                font-size: 10px !important;
            }
            .price-row.active {
                font-size: 10px !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let cart = [];
            let editingCartItemId = null;
            let products = [];
            let selectedCustomer = null;
            let lastOrderData = null;
            let isWholesale = false;
            let currentProductForVariants = null;
            let selectedVariants = {};
            let hasVAT = false;
            let searchType = 'product';

            // Tab switching listener to hide/show product fields
            const designTasksTab = document.getElementById('design-tasks-tab');
            const productsTab = document.getElementById('products-tab');
            const productFields = document.getElementById('pos-product-fields');

            if (designTasksTab) {
                designTasksTab.addEventListener('shown.bs.tab', function () {
                    if (productFields) productFields.classList.add('d-none');
                });
            }
            if (productsTab) {
                productsTab.addEventListener('shown.bs.tab', function () {
                    if (productFields) productFields.classList.remove('d-none');
                });
            }
            // Listen for department selection change in design task details
            const taskDeptSelect = document.getElementById('pos-task-department');
            if (taskDeptSelect) {
                taskDeptSelect.addEventListener('change', function() {
                    const deptId = this.value;
                    if (typeof handleDepartmentStaffAutoSelect === 'function') {
                        handleDepartmentStaffAutoSelect(deptId);
                    }
                });
            }

            // Auto-populate deadline on startup
            setTimeout(() => {
                if (typeof autoPopulateDeadline === 'function') {
                    autoPopulateDeadline();
                }
            }, 100);

            let isProforma = @json($isProforma ?? false);

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

            const designTaskModalEl = document.getElementById('designTaskModal');
            const designTaskModal = designTaskModalEl ? new bootstrap.Modal(designTaskModalEl, { backdrop: 'static', keyboard: false }) : null;

            // Set time
            setInterval(() => {
                document.getElementById('current-time').textContent = new Date().toLocaleString();
            }, 1000);

            // Premium SweetAlert Toast
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            window.showAlert = (title, icon = 'info') => {
                Toast.fire({
                    icon: icon,
                    title: title
                });
            };

            window.loadDistricts = async (regionId, districtSelectElement, selectedDistrictId = null) => {
                districtSelectElement.innerHTML = '<option value="">Loading districts...</option>';
                districtSelectElement.disabled = true;
                
                if (!regionId) {
                    districtSelectElement.innerHTML = '<option value="">Select District (Optional)</option>';
                    districtSelectElement.disabled = false;
                    return;
                }
                
                try {
                    const response = await fetch('/regions/' + regionId + '/districts');
                    const districts = await response.json();
                    
                    let html = '<option value="">Select District (Optional)</option>';
                    districts.forEach(district => {
                        html += '<option value="' + district.id + '"' + (selectedDistrictId == district.id ? ' selected' : '') + '>' + district.district_name + '</option>';
                    });
                    districtSelectElement.innerHTML = html;
                    districtSelectElement.disabled = false;
                } catch (error) {
                    console.error('Failed to load districts:', error);
                    districtSelectElement.innerHTML = '<option value="">Error loading districts</option>';
                    districtSelectElement.disabled = false;
                }
            };

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
                Swal.fire({
                    title: 'Exit POS?',
                    text: 'Any unsaved changes will be lost.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#475569',
                    confirmButtonText: 'Yes, exit',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('admin.finance.dashboard') }}";
                    }
                });
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
                    const response = await fetch(`{{ route('admin.pos.products.search') }}?query=${query}&type=${searchType}`);
                    products = await response.json();
                    renderProducts();
                } catch (error) {
                    console.error(error);
                    productGrid.innerHTML = '<div class="col-12 text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-2x mb-2"></i><br>Error loading items.</div>';
                }
            }

            window.setSearchType = (type) => {
                searchType = type;
                if (type === 'product') {
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

            window.onPosTaskTypeChange = (select) => {
                const option = select.options[select.selectedIndex];
                if (!option.value) return;

                const price = option.getAttribute('data-price');
                const description = option.getAttribute('data-description');
                const name = option.text.split('(')[0].trim();

                document.getElementById('pos-task-title').value = name;
                document.getElementById('pos-task-rate').value = price;
                document.getElementById('pos-task-description').value = description;

                calculatePosTaskTotal();
            };

            window.calculatePosTaskTotal = () => {
                const qty = parseFloat(document.getElementById('pos-task-qty').value) || 0;
                const rate = parseFloat(document.getElementById('pos-task-rate').value) || 0;
                const amount = qty * rate;
                document.getElementById('pos-task-amount').value = amount;
            };

            window.addDesignTaskToCart = () => {
                const title = document.getElementById('pos-task-title').value;
                const qty = parseFloat(document.getElementById('pos-task-qty').value) || 0;
                const rate = parseFloat(document.getElementById('pos-task-rate').value) || 0;

                if (!title) {
                    showAlert('Please enter a task title.', 'warning');
                    return;
                }

                if (qty <= 0) {
                    showAlert('Quantity must be greater than 0.', 'warning');
                    return;
                }

                const taskDetails = {
                    task_type_id: document.getElementById('pos-task-type-id').value,
                    description: document.getElementById('pos-task-description').value,
                    priority: document.getElementById('pos-task-priority').value,
                    department_id: document.getElementById('pos-task-department').value,
                    saler_id: document.getElementById('pos-task-saler').value,
                    designer_id: document.getElementById('pos-task-designer').value,
                    operator_id: document.getElementById('pos-task-operator').value,
                    instructions: document.getElementById('pos-task-instructions').value,
                    deadline: document.getElementById('pos-task-deadline').value,
                };

                if (editingCartItemId) {
                    // Update existing cart item
                    const existingItem = cart.find(i => i.id === editingCartItemId);
                    if (existingItem) {
                        existingItem.name = title;
                        existingItem.quantity = qty;
                        existingItem.price = rate;
                        existingItem.task_details = taskDetails;
                    }
                    editingCartItemId = null;
                    document.getElementById('pos-add-to-cart-btn').innerHTML = '<i class="fas fa-plus-circle me-1"></i> ADD TO CART';
                    renderCart();
                    showAlert('Design task updated!', 'success');
                } else {
                    const taskData = {
                        id: 'design_task_' + Date.now() + '_' + Math.floor(Math.random() * 1000),
                        name: title,
                        quantity: qty,
                        price: rate,
                        is_design_task: true,
                        delivery_cost: 0,
                        delivery_discount: 0,
                        task_details: taskDetails
                    };
                    cart.push(taskData);
                    renderCart();
                    showAlert('Design task added to cart!', 'success');
                }

                // Reset form
                document.getElementById('pos-task-title').value = '';
                document.getElementById('pos-task-description').value = '';
                document.getElementById('pos-task-qty').value = '1';
                document.getElementById('pos-task-rate').value = '0';
                document.getElementById('pos-task-amount').value = '0';
                document.getElementById('pos-task-type-id').value = '';
                document.getElementById('pos-task-instructions').value = '';
                document.getElementById('pos-task-deadline').value = '';
                document.getElementById('pos-deadline-day').value = '';
                document.getElementById('pos-deadline-month').value = '';
                document.getElementById('pos-deadline-year').value = '';
                document.getElementById('pos-deadline-time').value = '';

                hideDesignTaskForm();
            };

            window.editDesignTaskInCart = (itemId) => {
                const item = cart.find(i => i.id === itemId);
                if (!item) return;

                editingCartItemId = itemId;

                // Populate form fields
                document.getElementById('pos-task-type-id').value = item.task_details.task_type_id || '';
                document.getElementById('pos-task-title').value = item.name || '';
                document.getElementById('pos-task-rate').value = item.price || 0;
                document.getElementById('pos-task-qty').value = item.quantity || 1;
                document.getElementById('pos-task-description').value = item.task_details.description || '';
                document.getElementById('pos-task-priority').value = item.task_details.priority || 'normal';
                document.getElementById('pos-task-instructions').value = item.task_details.instructions || '';
                document.getElementById('pos-task-deadline').value = item.task_details.deadline || '';
                document.getElementById('pos-task-saler').value = item.task_details.saler_id || '';
                document.getElementById('pos-task-operator').value = item.task_details.operator_id || '';

                // Department triggers staff auto-select
                const deptEl = document.getElementById('pos-task-department');
                deptEl.value = item.task_details.department_id || '';
                handleDepartmentStaffAutoSelect(deptEl.value);

                // Set designer after department populates options (slight delay)
                setTimeout(() => {
                    document.getElementById('pos-task-designer').value = item.task_details.designer_id || '';
                }, 100);

                // Parse deadline back into day/month/year/time fields
                if (item.task_details.deadline) {
                    const parts = item.task_details.deadline.split('T');
                    const dateParts = (parts[0] || '').split('-');
                    document.getElementById('pos-deadline-year').value = dateParts[0] || '';
                    document.getElementById('pos-deadline-month').value = dateParts[1] || '';
                    document.getElementById('pos-deadline-day').value = dateParts[2] || '';
                    document.getElementById('pos-deadline-time').value = parts[1] || '';
                }

                calculatePosTaskTotal();

                // Update button label
                document.getElementById('pos-add-to-cart-btn').innerHTML = '<i class="fas fa-save me-1"></i> UPDATE TASK';

                if (designTaskModal) designTaskModal.show();
            };

            let activeTaskDeptId = 'all';

            window.filterTaskTypesByDept = (deptId) => {
                activeTaskDeptId = deptId;
                
                // Update active class on filter buttons
                const buttons = document.querySelectorAll('#task-dept-filters button');
                buttons.forEach(btn => btn.classList.remove('active'));
                
                // Find active button
                const activeBtn = Array.from(buttons).find(btn => btn.getAttribute('onclick').includes(deptId));
                if (activeBtn) {
                    activeBtn.classList.add('active');
                }

                applyDesignTaskFilters();
            };

            window.applyDesignTaskFilters = () => {
                const searchInput = document.getElementById('design-task-search');
                const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
                
                const cols = document.querySelectorAll('.task-type-col');
                cols.forEach(col => {
                    const colDeptId = col.getAttribute('data-department-id');
                    const name = col.querySelector('.product-name').textContent.toLowerCase();
                    
                    const matchesDept = (activeTaskDeptId === 'all' || colDeptId == activeTaskDeptId);
                    const matchesSearch = name.includes(query);
                    
                    if (matchesDept && matchesSearch) {
                        col.style.setProperty('display', 'block', 'important');
                    } else {
                        col.style.setProperty('display', 'none', 'important');
                    }
                });
            };

            // Bind input event to real-time search
            const searchInput = document.getElementById('design-task-search');
            if (searchInput) {
                searchInput.addEventListener('input', applyDesignTaskFilters);
            }

            window.autoPopulateDeadline = () => {
                const now = new Date();
                const day = String(now.getDate()).padStart(2, '0');
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const year = String(now.getFullYear());
                
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const time = `${hours}:${minutes}`;

                document.getElementById('pos-deadline-day').value = day;
                document.getElementById('pos-deadline-month').value = month;
                document.getElementById('pos-deadline-year').value = year;
                document.getElementById('pos-deadline-time').value = time;
                
                updatePosDeadline();
            };

            window.handleDepartmentStaffAutoSelect = (deptId) => {
                // Filter and auto-select designer
                const designerSelect = document.getElementById('pos-task-designer');
                if (designerSelect) {
                    Array.from(designerSelect.options).forEach(opt => {
                        const optDeptId = opt.getAttribute('data-department-id');
                        const optDeptIdsStr = opt.getAttribute('data-department-ids') || '';
                        const optDeptIds = optDeptIdsStr ? optDeptIdsStr.split(',') : [];
                        if (!deptId || opt.value === '' || optDeptId == deptId || optDeptIds.includes(String(deptId))) {
                            opt.style.display = 'block';
                        } else {
                            opt.style.display = 'none';
                        }
                    });
                    
                    if (deptId) {
                        const match = Array.from(designerSelect.options).find(opt => {
                            if (opt.value === '') return false;
                            const optDeptId = opt.getAttribute('data-department-id');
                            const optDeptIdsStr = opt.getAttribute('data-department-ids') || '';
                            const optDeptIds = optDeptIdsStr ? optDeptIdsStr.split(',') : [];
                            return optDeptId == deptId || optDeptIds.includes(String(deptId));
                        });
                        if (match) {
                            designerSelect.value = match.value;
                        } else {
                            designerSelect.value = '';
                        }
                    }
                }

                // Filter and auto-select operator
                const operatorSelect = document.getElementById('pos-task-operator');
                if (operatorSelect) {
                    Array.from(operatorSelect.options).forEach(opt => {
                        const optDeptId = opt.getAttribute('data-department-id');
                        const optDeptIdsStr = opt.getAttribute('data-department-ids') || '';
                        const optDeptIds = optDeptIdsStr ? optDeptIdsStr.split(',') : [];
                        if (!deptId || opt.value === '' || optDeptId == deptId || optDeptIds.includes(String(deptId))) {
                            opt.style.display = 'block';
                        } else {
                            opt.style.display = 'none';
                        }
                    });
                    
                    if (deptId) {
                        const match = Array.from(operatorSelect.options).find(opt => {
                            if (opt.value === '') return false;
                            const optDeptId = opt.getAttribute('data-department-id');
                            const optDeptIdsStr = opt.getAttribute('data-department-ids') || '';
                            const optDeptIds = optDeptIdsStr ? optDeptIdsStr.split(',') : [];
                            return optDeptId == deptId || optDeptIds.includes(String(deptId));
                        });
                        if (match) {
                            operatorSelect.value = match.value;
                        } else {
                            operatorSelect.value = '';
                        }
                    }
                }
            };

            window.selectTaskType = (id, name, price, description, departmentId) => {
                document.getElementById('pos-task-type-id').value = id;
                document.getElementById('pos-task-title').value = name;
                document.getElementById('pos-task-rate').value = price;
                document.getElementById('pos-task-description').value = description;

                if (departmentId) {
                    document.getElementById('pos-task-department').value = departmentId;
                    handleDepartmentStaffAutoSelect(departmentId);
                } else {
                    document.getElementById('pos-task-department').value = '';
                    handleDepartmentStaffAutoSelect('');
                }

                autoPopulateDeadline();
                calculatePosTaskTotal();

                if (designTaskModal) designTaskModal.show();
            };

            window.hideDesignTaskForm = () => {
                if (designTaskModal) designTaskModal.hide();
                if (editingCartItemId) {
                    editingCartItemId = null;
                    document.getElementById('pos-add-to-cart-btn').innerHTML = '<i class="fas fa-plus-circle me-1"></i> ADD TO CART';
                }
            };

            window.updatePosDeadline = () => {
                const day = document.getElementById('pos-deadline-day').value;
                const month = document.getElementById('pos-deadline-month').value;
                const year = document.getElementById('pos-deadline-year').value;
                const time = document.getElementById('pos-deadline-time').value;
                const input = document.getElementById('pos-task-deadline');

                if (day && month && year && time) {
                    input.value = `${year}-${month}-${day}T${time}`;
                } else {
                    input.value = '';
                }
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
                        return showAlert('Please select all options.', 'warning');
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
                    if (item.is_design_task) {
                        const deliveryInfo = (item.delivery_cost > 0 || item.delivery_discount > 0)
                            ? `<div class="text-muted small mt-1" style="font-size: 11px;">
                                 ${item.delivery_cost > 0 ? `<span class="me-2 text-info fw-semibold"><i class="fas fa-truck me-1"></i>Deliv: +${formatNumber(item.delivery_cost)}</span>` : ''}
                                 ${item.delivery_discount > 0 ? `<span class="text-danger fw-semibold"><i class="fas fa-tags me-1"></i>Disc: -${formatNumber(item.delivery_discount)}</span>` : ''}
                               </div>`
                            : '';
                        return `
                                        <div class="cart-item">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="cart-item-info" style="flex:1;">
                                                    <div class="cart-item-name"><i class="fas fa-pencil-ruler text-primary me-1"></i>${item.name}</div>
                                                    <div class="cart-item-variant">Design Task</div>
                                                    ${deliveryInfo}
                                                    <div class="fw-bold mt-1 text-dark">${formatNumber(item.price)}</div>
                                                    <button type="button" class="btn btn-outline-warning btn-sm mt-1 px-2 py-0" style="font-size:10px;" onclick="editDesignTaskInCart('${item.id}')">
                                                        <i class="fas fa-edit me-1"></i>Edit
                                                    </button>
                                                </div>
                                                <div class="cart-controls">
                                                    <div class="qty-btn" onclick="updateQty(${index}, -1)"><i class="fas fa-minus"></i></div>
                                                    <input type="number" class="qty-input bg-transparent" value="${item.quantity}" min="0.01" step="0.01" onchange="manualUpdateQty(${index}, this.value)">
                                                    <div class="qty-btn" onclick="updateQty(${index}, 1)"><i class="fas fa-plus"></i></div>
                                                    <div class="remove-item ms-2" onclick="removeItem(${index})"><i class="fas fa-trash-alt"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                    }

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

            window.removeItem = (index) => {
                cart.splice(index, 1);
                renderCart();
            };

            window.updateQty = (index, delta) => {
                cart[index].quantity += delta;
                const minQty = cart[index].is_design_task ? 0.01 : 1;
                if (cart[index].quantity < minQty) removeItem(index);
                else renderCart();
            };

            window.manualUpdateQty = (index, value) => {
                if (isNaN(newQty) || newQty < (cart[index].is_design_task ? 0.01 : 1)) {
                    newQty = cart[index].is_design_task ? 0.01 : 1;
                }
                cart[index].quantity = newQty;
                renderCart();
            };

            function updateTotals() {
                let subtotal = 0;
                let taskDeliveryCostTotal = 0;
                let taskDeliveryDiscountTotal = 0;

                cart.forEach(item => {
                    if (item.is_design_task) {
                        subtotal += item.price * item.quantity;
                        taskDeliveryCostTotal += parseFloat(item.delivery_cost) || 0;
                        taskDeliveryDiscountTotal += parseFloat(item.delivery_discount) || 0;
                    } else {
                        subtotal += calculateUnitPriceWithIndicator(item).price * item.quantity;
                    }
                });

                const orderDiscount = parseFloat(document.getElementById('order-level-discount')?.value) || 0;
                const orderDelivery = parseFloat(document.getElementById('order-level-delivery')?.value) || 0;
                const totalDeliveryFee = orderDelivery + (taskDeliveryCostTotal - taskDeliveryDiscountTotal);

                const netSubtotal = Math.max(0, subtotal - orderDiscount);
                const vatAmount = hasVAT ? netSubtotal * 0.18 : 0;
                const total = netSubtotal + totalDeliveryFee + vatAmount;

                subtotalEl.textContent = formatNumber(subtotal);
                totalEl.textContent = formatNumber(total);

                // Update Delivery row if present
                const deliveryRow = document.getElementById('delivery-display-row');
                if (totalDeliveryFee !== 0) {
                    const sign = totalDeliveryFee >= 0 ? '+' : '';
                    if (!deliveryRow) {
                        subtotalEl.parentElement.insertAdjacentHTML('afterend', `
                                        <div class="d-flex justify-content-between mb-2" id="delivery-display-row">
                                            <span class="text-muted fw-bold small">Delivery Fee</span>
                                            <span class="small fw-bold text-info">${sign}${formatNumber(totalDeliveryFee)}</span>
                                        </div>
                                    `);
                    } else {
                        deliveryRow.querySelector('.text-info').textContent = `${sign}${formatNumber(totalDeliveryFee)}`;
                    }
                } else if (deliveryRow) {
                    deliveryRow.remove();
                }

                // Update Order Discount row if present
                const discountRow = document.getElementById('discount-display-row');
                if (orderDiscount > 0) {
                    if (!discountRow) {
                        subtotalEl.parentElement.insertAdjacentHTML('afterend', `
                                        <div class="d-flex justify-content-between mb-2" id="discount-display-row">
                                            <span class="text-muted fw-bold small">Order Discount</span>
                                            <span class="small fw-bold text-danger">-${formatNumber(orderDiscount)}</span>
                                        </div>
                                    `);
                    } else {
                        discountRow.querySelector('.text-danger').textContent = `-${formatNumber(orderDiscount)}`;
                    }
                } else if (discountRow) {
                    discountRow.remove();
                }

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

                // Dynamically update balance display on total updates
                if (window.calculateBalance) {
                    window.calculateBalance();
                }
            }

            // Hook input events for Order Discount and Delivery Fee
            const orderDiscountInput = document.getElementById('order-level-discount');
            const orderDeliveryInput = document.getElementById('order-level-delivery');
            if (orderDiscountInput) orderDiscountInput.addEventListener('input', updateTotals);
            if (orderDeliveryInput) orderDeliveryInput.addEventListener('input', updateTotals);

            window.calculateCartTotal = () => {
                let subtotal = 0;
                let taskDeliveryCostTotal = 0;
                let taskDeliveryDiscountTotal = 0;

                cart.forEach(item => {
                    if (item.is_design_task) {
                        subtotal += item.price * item.quantity;
                        taskDeliveryCostTotal += parseFloat(item.delivery_cost) || 0;
                        taskDeliveryDiscountTotal += parseFloat(item.delivery_discount) || 0;
                    } else {
                        subtotal += (calculateUnitPriceWithIndicator(item).price || 0) * item.quantity;
                    }
                });
                const orderDiscount = parseFloat(document.getElementById('order-level-discount')?.value) || 0;
                const orderDelivery = parseFloat(document.getElementById('order-level-delivery')?.value) || 0;
                const totalDeliveryFee = orderDelivery + (taskDeliveryCostTotal - taskDeliveryDiscountTotal);
                const netSubtotal = Math.max(0, subtotal - orderDiscount);
                const vatAmount = hasVAT ? netSubtotal * 0.18 : 0;
                return netSubtotal + totalDeliveryFee + vatAmount;
            };

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

            // Select split inputs
            const payCashInput = document.getElementById('pay-cash-amount');
            const payMobileInput = document.getElementById('pay-mobile-amount');
            const payBankInput = document.getElementById('pay-bank-amount');
            const splitWrapper = document.getElementById('split-payment-methods-wrapper');

            window.calculateCartTotal = () => {
                let subtotal = 0;
                cart.forEach(item => {
                    if (item.is_design_task) {
                        subtotal += item.price * item.quantity;
                    } else {
                        subtotal += (calculateUnitPriceWithIndicator(item).price || 0) * item.quantity;
                    }
                });
                const orderDiscount = parseFloat(document.getElementById('order-level-discount')?.value) || 0;
                const orderDelivery = parseFloat(document.getElementById('order-level-delivery')?.value) || 0;
                const netSubtotal = Math.max(0, subtotal - orderDiscount);
                const vatAmount = hasVAT ? netSubtotal * 0.18 : 0;
                return netSubtotal + orderDelivery + vatAmount;
            };

            window.calculateBalance = () => {
                const total = calculateCartTotal();
                const paymentType = document.querySelector('input[name="payment_type"]:checked')?.value || 'pay_now';

                let actualPaid = 0;
                if (paymentType === 'pay_later') {
                    actualPaid = 0;
                } else {
                    const cash = parseFloat(payCashInput.value) || 0;
                    const mobile = parseFloat(payMobileInput.value) || 0;
                    const bank = parseFloat(payBankInput.value) || 0;
                    
                    // If all split inputs are blank, default to full payment (representing Cash by default)
                    if (payCashInput.value === '' && payMobileInput.value === '' && payBankInput.value === '') {
                        actualPaid = total;
                    } else {
                        actualPaid = cash + mobile + bank;
                    }
                }

                // Update hidden amount-paid input so that backend and receipt get correct amount
                amountPaidInput.value = actualPaid;

                const balance = total - actualPaid;

                if (balance !== 0) {
                    balanceDisplay.classList.remove('d-none');
                    balanceAmount.textContent = formatNumber(Math.abs(balance));
                    balanceAmount.className = balance > 0 ? 'small fw-bold text-danger' : 'small fw-bold text-success';
                } else {
                    balanceDisplay.classList.add('d-none');
                }
            };

            // Bind inputs to recalculate balance dynamically
            [payCashInput, payMobileInput, payBankInput].forEach(inp => {
                if (inp) inp.addEventListener('input', calculateBalance);
            });

            // Listen for Payment Type changes (Pay Now vs Pay Later)
            document.querySelectorAll('input[name="payment_type"]').forEach(radio => {
                radio.addEventListener('change', (e) => {
                    const type = e.target.value;
                    if (type === 'pay_later') {
                        splitWrapper.classList.add('d-none');
                        // Reset all inputs
                        payCashInput.value = '';
                        payMobileInput.value = '';
                        payBankInput.value = '';
                    } else {
                        splitWrapper.classList.remove('d-none');
                    }
                    calculateBalance();
                });
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
                    const response = await fetch(`{{ route('admin.pos.customers.search') }}?query=${query}`);
                    const customers = await response.json();

                    if (customers.length === 0) {
                        customerSearchResults.innerHTML = '<div class="dropdown-item text-muted">No customers found.</div>';
                    } else {
                        customerSearchResults.innerHTML = customers.map((c, index) => `
                                        <button class="dropdown-item d-flex flex-column py-2 border-bottom customer-result-item" type="button" data-index="${index}">
                                            <span class="fw-bold">${c.name}</span>
                                            <small class="text-muted">${c.phone || 'No phone'} ${c.address ? '• ' + c.address : ''}</small>
                                        </button>
                                    `).join('');

                        // Add click handlers to customer result items
                        document.querySelectorAll('.customer-result-item').forEach(item => {
                            item.addEventListener('click', function () {
                                const index = this.dataset.index;
                                const customer = customers[index];
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

            // Guest button handler removed because element does not exist

            function restoreSearchInput() {
                const searchContainer = document.getElementById('cust-search-container');
                if (searchContainer) {
                    searchContainer.innerHTML = `
                        <div class="position-relative">
                            <div class="input-group border">
                                <span class="input-group-text bg-white border-0"><i
                                        class="fas fa-search text-muted"></i></span>
                                <input type="text" id="customer-search-input" class="form-control border-0"
                                    placeholder="Search customer by name or phone..." autocomplete="off">
                            </div>
                            <div id="customer-search-results" class="dropdown-menu w-100 border mt-1"
                                style="max-height: 300px; overflow-y: auto;"></div>
                        </div>
                    `;
                    
                    // Re-define variables and re-attach listeners
                    const newSearchInput = document.getElementById('customer-search-input');
                    const newSearchResults = document.getElementById('customer-search-results');
                    
                    if (newSearchInput) {
                        newSearchInput.addEventListener('input', async (e) => {
                            const query = e.target.value;
                            if (query.length < 2) {
                                newSearchResults.classList.remove('show');
                                return;
                            }

                            newSearchResults.innerHTML = '<div class="dropdown-item text-center py-2"><div class="spinner-border spinner-border-sm text-dark"></div></div>';
                            newSearchResults.classList.add('show');

                            try {
                                const response = await fetch(`{{ route('admin.pos.customers.search') }}?query=${encodeURIComponent(query)}`);
                                const customers = await response.json();

                                if (customers.length === 0) {
                                    newSearchResults.innerHTML = '<div class="dropdown-item text-muted">No customers found.</div>';
                                } else {
                                    newSearchResults.innerHTML = customers.map((c, index) => `
                                        <button class="dropdown-item d-flex flex-column py-2 border-bottom customer-result-item" type="button" data-index="${index}">
                                            <span class="fw-bold">${c.name}</span>
                                            <small class="text-muted">${c.phone || 'No phone'} ${c.address ? '• ' + c.address : ''}</small>
                                        </button>
                                    `).join('');

                                    document.querySelectorAll('.customer-result-item').forEach(item => {
                                        item.addEventListener('click', function () {
                                            const index = this.dataset.index;
                                            const customer = customers[index];
                                            window.setCustomer(customer);
                                        });
                                    });
                                }
                            } catch (error) {
                                newSearchResults.innerHTML = '<div class="dropdown-item text-danger">Error loading customers.</div>';
                            }
                        });

                        newSearchInput.addEventListener('focus', (e) => {
                            if (e.target.value.length >= 2) {
                                newSearchResults.classList.add('show');
                            }
                        });
                    }
                    
                    document.addEventListener('click', (e) => {
                        const searchInput = document.getElementById('customer-search-input');
                        const searchResults = document.getElementById('customer-search-results');
                        if (searchInput && searchResults) {
                            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                                searchResults.classList.remove('show');
                            }
                        }
                    });
                }
            }

            window.setCustomer = (customer) => {
                selectedCustomer = customer;
                
                const searchContainer = document.getElementById('cust-search-container');
                if (searchContainer) {
                    searchContainer.innerHTML = `
                        <div class="alert alert-primary p-2 border mb-0 d-flex justify-content-between align-items-center" style="background: #e7f1ff; border-radius: 8px;">
                            <div class="d-flex align-items-start gap-2">
                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px; font-weight: bold; background: var(--pos-primary-grad) !important;">
                                    ${customer.name ? customer.name.charAt(0).toUpperCase() : 'C'}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-primary small">${customer.name}</h6>
                                    <div class="small text-muted mt-0" style="font-size: 11px;">
                                        ${customer.phone ? `<span><i class="fas fa-phone-alt me-1 opacity-75"></i>${customer.phone}</span>` : ''}
                                        ${customer.whatsapp_number ? `<span class="ms-2"><i class="fab fa-whatsapp me-1 text-success"></i>${customer.whatsapp_number}</span>` : ''}
                                    </div>
                                    <div class="small text-muted" style="font-size: 10px;">
                                        <i class="fas fa-map-marker-alt me-1 opacity-75"></i>${customer.address || 'No Address'}
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-1 align-items-center">
                                <button type="button" class="btn btn-xs btn-outline-secondary p-1" style="font-size: 10px; border-radius: 4px; line-height: 1; padding: 3px 6px !important;" id="btn-edit-customer" title="Edit Customer">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-danger p-1" style="font-size: 10px; border-radius: 4px; line-height: 1; padding: 3px 6px !important;" id="clear-customer" title="Deselect Customer">
                                    <i class="fas fa-trash-alt"></i> Remove
                                </button>
                            </div>
                        </div>
                    `;

                    // Attach click handlers to the new elements
                    document.getElementById('clear-customer').addEventListener('click', () => {
                        selectedCustomer = null;
                        restoreSearchInput();
                    });

                    document.getElementById('btn-edit-customer').addEventListener('click', () => {
                        if (!selectedCustomer) return;
                        document.getElementById('edit-cust-id').value = selectedCustomer.id;
                        document.getElementById('edit-cust-name').value = selectedCustomer.name;
                        const sp = posExtractPhonePrefix(selectedCustomer.phone || '');
                        document.getElementById('edit-cust-phone-prefix').value = sp.prefix;
                        document.getElementById('edit-cust-phone').value = sp.local;
                        document.getElementById('edit-cust-email').value = selectedCustomer.email || '';
                        document.getElementById('edit-cust-address').value = selectedCustomer.address || '';
                        document.getElementById('edit-cust-wholesale').checked = !!selectedCustomer.is_wholesale;
                        document.getElementById('edit-cust-whatsapp').value = selectedCustomer.whatsapp_number || '';
                        document.getElementById('edit-cust-company').value = selectedCustomer.company_name || '';
                        document.getElementById('edit-cust-business-type').value = selectedCustomer.business_type || '';
                        if (document.getElementById('edit-cust-source')) document.getElementById('edit-cust-source').value = selectedCustomer.customer_source || '';
                        
                        const editRegionVal = selectedCustomer.region_id || '';
                        const editDistrictVal = selectedCustomer.district_id || '';
                        document.getElementById('edit-cust-region').value = editRegionVal;
                        window.loadDistricts(editRegionVal, document.getElementById('edit-cust-district'), editDistrictVal);

                        if (updateCustModal) updateCustModal.show();
                        else new bootstrap.Modal(document.getElementById('editCustomerModal')).show();
                    });
                }

                if (customer.is_wholesale) {
                    pricingToggle.checked = true;
                    pricingToggle.dispatchEvent(new Event('change'));
                }
            };

            // Old clear-customer listener removed (now handled in setCustomer)

            // Customer Creation
            const btnAddCustomer = document.getElementById('btn-add-customer');
            if (btnAddCustomer) {
                btnAddCustomer.addEventListener('click', () => {
                    if (addCustModal) addCustModal.show();
                });
            }

            // Region and District Change Listeners for POS Modals
            const newCustRegion = document.getElementById('new-cust-region');
            const newCustDistrict = document.getElementById('new-cust-district');
            if (newCustRegion && newCustDistrict) {
                newCustRegion.addEventListener('change', function () {
                    window.loadDistricts(this.value, newCustDistrict);
                });
            }

            const editCustRegion = document.getElementById('edit-cust-region');
            const editCustDistrict = document.getElementById('edit-cust-district');
            if (editCustRegion && editCustDistrict) {
                editCustRegion.addEventListener('change', function () {
                    window.loadDistricts(this.value, editCustDistrict);
                });
            }

            const btnSaveCustomer = document.getElementById('btn-save-customer');
            if (btnSaveCustomer) {
                btnSaveCustomer.addEventListener('click', async () => {
                    const name = document.getElementById('new-cust-name').value;
                    const phonePrefix = (document.getElementById('new-cust-phone-prefix')?.value || '').trim();
                    const phoneLocal  = document.getElementById('new-cust-phone').value.trim();
                    const phone = phonePrefix ? phonePrefix + phoneLocal : phoneLocal;
                    const email = document.getElementById('new-cust-email').value;
                    const region_id = document.getElementById('new-cust-region').value;
                    const district_id = document.getElementById('new-cust-district').value;
                    const address = document.getElementById('new-cust-address').value;
                    const isWholesaleCust = document.getElementById('new-cust-wholesale').checked;
                    const whatsapp = document.getElementById('new-cust-whatsapp')?.value ?? '';
                    const company = document.getElementById('new-cust-company')?.value ?? '';
                    const businessType = document.getElementById('new-cust-business-type')?.value ?? '';
                    const customerSource = document.getElementById('new-cust-source')?.value ?? '';

                    if (!name || !phone) return showAlert('Name and phone are required.', 'warning');

                    try {
                        const response = await fetch(`{{ route('admin.pos.customers.store') }}`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ name, phone, email, address, is_wholesale: isWholesaleCust, whatsapp_number: whatsapp, company_name: company, business_type: businessType, customer_source: customerSource, region_id, district_id })
                        });
                        const result = await response.json();
                        if (result.success) {
                            setCustomer(result.customer);
                            addCustModal.hide();
                            document.getElementById('add-customer-form').reset();
                            document.getElementById('new-cust-district').innerHTML = '<option value="">Select District (Optional)</option>';
                            showAlert('Customer saved successfully!', 'success');
                        } else { showAlert(result.message || 'Failed to save customer', 'error'); }
                    } catch (error) { showAlert('Failed to save customer', 'error'); }
                });
            }


            if (btnUpdateCustomer) {
                btnUpdateCustomer.addEventListener('click', async () => {
                const id = document.getElementById('edit-cust-id').value;
                const name = document.getElementById('edit-cust-name').value;
                const editPhonePrefix = (document.getElementById('edit-cust-phone-prefix')?.value || '').trim();
                const editPhoneLocal  = document.getElementById('edit-cust-phone').value.trim();
                const phone = editPhonePrefix ? editPhonePrefix + editPhoneLocal : editPhoneLocal;
                const email = document.getElementById('edit-cust-email').value;
                const region_id = document.getElementById('edit-cust-region').value;
                const district_id = document.getElementById('edit-cust-district').value;
                const address = document.getElementById('edit-cust-address').value;
                const isWholesaleCust = document.getElementById('edit-cust-wholesale').checked;
                const whatsapp = document.getElementById('edit-cust-whatsapp').value;
                const company = document.getElementById('edit-cust-company').value;
                const business_type = document.getElementById('edit-cust-business-type').value;
                const customer_source = document.getElementById('edit-cust-source')?.value ?? '';

                if (!name || !phone) return showAlert('Name and phone are required.', 'warning');

                try {
                    const url = `{{ route('admin.pos.customers.update', ':id') }}`.replace(':id', id);
                    const response = await fetch(url, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ name, phone, email, address, is_wholesale: isWholesaleCust, whatsapp_number: whatsapp, company_name: company, business_type, customer_source, region_id, district_id })
                    });
                    const result = await response.json();
                    if (result.success) {
                        setCustomer(result.customer);
                        if (updateCustModal) updateCustModal.hide();
                        else {
                            const inst = bootstrap.Modal.getInstance(document.getElementById('editCustomerModal'));
                            if (inst) inst.hide();
                        }
                        showAlert('Customer updated successfully!', 'success');
                    } else { showAlert(result.message || 'Failed to update customer', 'error'); }
                } catch (error) { showAlert('Failed to update customer', 'error'); }
            });
            }

            checkoutBtn.addEventListener('click', async () => {
                if (cart.length === 0) return showAlert('Cart is empty!', 'warning');

                if (!selectedCustomer) {
                    return showAlert('Please search and select a customer, or click "New" to add one first.', 'warning');
                }

                const customerName = selectedCustomer.name;
                const customerPhone = selectedCustomer.phone;
                const customerAddress = selectedCustomer.address || '';
                const salerOption = salerSelect.options[salerSelect.selectedIndex];

                const subtotal = cart.reduce((acc, item) => {
                    const price = item.is_design_task ? item.price : (calculateUnitPriceWithIndicator(item).price || 0);
                    return acc + (price * item.quantity);
                }, 0);
                const orderDiscount = parseFloat(document.getElementById('order-level-discount')?.value) || 0;
                const orderDelivery = parseFloat(document.getElementById('order-level-delivery')?.value) || 0;
                const netSubtotal = Math.max(0, subtotal - orderDiscount);
                const vatAmount = hasVAT ? netSubtotal * 0.18 : 0;
                const totalAmount = (netSubtotal + orderDelivery + vatAmount) || 0;
                
                // Retrieve amount paid (set dynamically by calculateBalance)
                const amountPaid = parseFloat(amountPaidInput.value) || 0;

                const paymentType = document.querySelector('input[name="payment_type"]:checked').value;
                let paymentMethod = 'cash';
                let paymentSplits = null;

                if (paymentType === 'pay_later') {
                    paymentMethod = 'pay_later';
                } else {
                    const cash = parseFloat(payCashInput.value) || 0;
                    const mobile = parseFloat(payMobileInput.value) || 0;
                    const bank = parseFloat(payBankInput.value) || 0;
                    
                    paymentSplits = {
                        cash: cash,
                        mobile_money: mobile,
                        bank: bank
                    };

                    const nonZero = [];
                    if (cash > 0) nonZero.push('cash');
                    if (mobile > 0) nonZero.push('mobile_money');
                    if (bank > 0) nonZero.push('bank');

                    if (nonZero.length === 1) {
                        paymentMethod = nonZero[0];
                    } else if (nonZero.length > 1) {
                        paymentMethod = 'split';
                    } else {
                        paymentMethod = 'cash';
                    }
                }

                const payload = {
                    customer_id: selectedCustomer ? selectedCustomer.id : null,
                    customer_name: customerName,
                    customer_phone: customerPhone,
                    customer_address: customerAddress,
                    customer_email: selectedCustomer.email || '',
                    is_wholesale: selectedCustomer.is_wholesale || false,
                    whatsapp_number: selectedCustomer.whatsapp_number || '',
                    company_name: selectedCustomer.company_name || '',
                    business_type: selectedCustomer.business_type || '',
                    saler_id: salerSelect.value,
                    department_id: document.getElementById('department-select').value,
                    items: cart.map(item => ({
                        id: item.id,
                        name: item.name,
                        quantity: item.quantity,
                        price: item.is_design_task ? item.price : (calculateUnitPriceWithIndicator(item).price || 0),
                        variants: item.variants,
                        is_design_task: item.is_design_task || false,
                        delivery_cost: item.is_design_task ? (item.delivery_cost || 0) : 0,
                        delivery_discount: item.is_design_task ? (item.delivery_discount || 0) : 0,
                        task_details: item.task_details || null
                    })),
                    payment_method: paymentMethod,
                    payment_splits: paymentSplits,
                    is_wholesale: isWholesale,
                    total_amount: totalAmount,
                    amount_paid: amountPaid,
                    has_vat: hasVAT,
                    vat_amount: vatAmount,
                    discount: parseFloat(document.getElementById('order-level-discount')?.value) || 0,
                    delivery_fee: parseFloat(document.getElementById('order-level-delivery')?.value) || 0,
                    order_type: document.getElementById('proforma-toggle').checked ? 'proforma' : 'sales_invoice',
                    _token: '{{ csrf_token() }}'
                };

                checkoutBtn.disabled = true;
                checkoutBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                try {
                    const response = await fetch(`{{ route('admin.pos.store') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
                        Swal.fire('Fatal Error', 'Server returned non-JSON response. Please check if you are still logged in.', 'error');
                        return;
                    }

                    if (response.ok && result.success) {
                        const balance = totalAmount - amountPaid;
                        lastOrderData = {
                            customer: { name: customerName, phone: customerPhone, address: customerAddress },
                            saler: { name: salerOption.text.trim(), phone: salerOption.dataset.phone || '' },
                            items: cart.map(item => {
                                const price = item.is_design_task ? item.price : (calculateUnitPriceWithIndicator(item).price || 0);
                                return {
                                    name: item.name,
                                    quantity: item.quantity,
                                    price: price,
                                    total: price * item.quantity
                                };
                            }),
                            subtotal: subtotal,
                            vat: vatAmount,
                            total: totalAmount,
                            deliveryCost: orderDelivery,
                            deliveryDiscount: orderDiscount,
                            deliveryNet: orderDelivery - orderDiscount,
                            amountPaid: amountPaid,
                            balance: balance,
                            orderCode: result.order_code,
                            hasVAT: hasVAT,
                            isProforma: result.is_proforma || isProforma,
                            paymentMethod: paymentMethod,
                            proformaUrl: result.proforma_url || null
                        };

                        reprintBtn.classList.remove('d-none');
                        cart = []; renderCart(); document.getElementById('clear-customer').click();
                        document.getElementById('amount-paid').value = '';
                        document.getElementById('balance-display').classList.add('d-none');

                        // Clear split payment inputs
                        if (payCashInput) payCashInput.value = '';
                        if (payMobileInput) payMobileInput.value = '';
                        if (payBankInput) payBankInput.value = '';

                        // ── Print prompt ──────────────────────────────────────────
                        if (result.is_proforma || isProforma) {
                            // Proforma: ask if they want to open/print the proforma invoice
                            Swal.fire({
                                title: 'Proforma Invoice Generated!',
                                html: `Proforma <strong>${result.order_code}</strong> created successfully.<br>Do you want to print the proforma invoice?`,
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonText: '<i class="fas fa-print me-1"></i> Print Invoice',
                                cancelButtonText: 'Not now',
                                confirmButtonColor: '#2563eb',
                                cancelButtonColor: '#6c757d',
                            }).then((r) => {
                                if (r.isConfirmed && lastOrderData.proformaUrl) {
                                    window.open(lastOrderData.proformaUrl + '?print=true', '_blank');
                                }
                            });
                        } else {
                            // Regular sale: ask if they want to print the receipt
                            Swal.fire({
                                title: 'Transaction Completed!',
                                html: `Order <strong>${result.order_code}</strong> saved successfully.<br>Do you want to print the receipt?`,
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonText: '<i class="fas fa-print me-1"></i> Print Receipt',
                                cancelButtonText: 'Not now',
                                confirmButtonColor: '#16a34a',
                                cancelButtonColor: '#6c757d',
                            }).then((r) => {
                                if (r.isConfirmed) printReceipt(lastOrderData);
                            });
                        }
                    } else {
                        Swal.fire('Checkout Error', result.message || 'Unknown error occurred.', 'error');
                        console.error('Checkout failed:', result);
                    }
                } catch (error) {
                    console.error('Fetch error:', error);
                    Swal.fire('Checkout failed', error.message + '. See browser console for details.', 'error');
                } finally {
                    checkoutBtn.disabled = false;
                    checkoutBtn.innerHTML = 'Complete Transaction';
                }
            });

            reprintBtn.addEventListener('click', () => {
                if (!lastOrderData) return;
                if (lastOrderData.isProforma && lastOrderData.proformaUrl) {
                    window.open(lastOrderData.proformaUrl + '?print=true', '_blank');
                } else {
                    printReceipt(lastOrderData);
                }
            });

            function printReceipt(data) {
                const receiptWindow = window.open('', '_blank', 'width=400,height=700');
                const dateTimeStr = new Date().toLocaleString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).replace(',', '');
                const paymentMethod = (data.paymentMethod || 'CASH').toUpperCase();

                const receiptHtml = `
                                <!DOCTYPE html>
                                <html>
                                <head>
                                    <title>${data.isProforma ? 'Proforma Invoice' : 'Sales Receipt'} - ${data.orderCode}</title>
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
                                    ${(data.deliveryCost && data.deliveryCost > 0) ? `
                                    <div class="total-row">
                                        <span>DELIVERY CHARGE:</span>
                                        <span>+${formatNumber(data.deliveryCost)} TZS</span>
                                    </div>
                                    ` : ''}
                                    ${(data.deliveryDiscount && data.deliveryDiscount > 0) ? `
                                    <div class="total-row">
                                        <span>DELIVERY DISCOUNT:</span>
                                        <span>-${formatNumber(data.deliveryDiscount)} TZS</span>
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
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Select2 for Salesperson only
            $('#saler-select').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select salesperson',
                width: '100%'
            });

            // Handle customer clear defensively
            const selectedCustDisp = document.getElementById('selected-customer-display');
            if (selectedCustDisp) {
                selectedCustDisp.classList.add('d-none');
            }
            const guestCustForm = document.getElementById('guest-customer-form');
            if (guestCustForm) {
                guestCustForm.classList.add('d-none');
            }
            selectedCustomer = null;
        });

        // Update clear customer button to reset select2
        const clearBtn = document.getElementById('clear-customer');
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                $('#customer-select').val(null).trigger('change');
            });
        }

        // Split a stored phone number into { prefix, local }
        function posExtractPhonePrefix(phone) {
            if (!phone) return { prefix: '255', local: '' };
            const clean = phone.replace(/^\+/, '');
            const known = ['255','254','256','250','257','243','971','234','250','27','44','91','86','1'];
            for (const p of known) {
                if (clean.startsWith(p)) return { prefix: p, local: clean.slice(p.length) };
            }
            return { prefix: '', local: phone };
        }
    </script>

@endsection