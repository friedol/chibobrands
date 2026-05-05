@extends('layouts.admin')

@section('page-title', 'Create Proforma Invoice')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(226, 232, 240, 0.8);
    }

    .edit-container {
        font-family: 'Nunito Sans', sans-serif;
        color: #334155;
    }

    .edit-container * {
        font-family: 'Nunito Sans', sans-serif;
    }

    .edit-container {
        padding: 2rem 1rem;
        max-width: 1600px;
        margin: 0 auto;
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .card-header-premium {
        background: #fff;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .card-header-premium h6 {
        color: #000;
        font-weight: 700;
        font-size: 1.1rem;
        margin: 0;
        letter-spacing: 0.5px;
    }

    .form-section-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 1.5rem;
        margin-top: 2rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-section-title::after {
        content: '';
        height: 1px;
        flex-grow: 1;
        background: #e2e8f0;
    }

    .form-label-premium {
        font-weight: 600;
        font-size: 0.85rem;
        color: #334155;
        margin-bottom: 0.5rem;
    }

    .form-control-premium {
        border-radius: 10px;
        padding: 0.75rem 1rem;
        border: 1px solid #e2e8f0;
        font-size: 0.95rem;
        transition: all 0.2s ease;
        background-color: #fcfcfd;
    }

    .form-control-premium:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        background-color: #fff;
        outline: none;
    }

    .input-icon-wrapper {
        position: relative;
    }

    .input-icon-wrapper i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .input-icon-wrapper .form-control-premium {
        padding-left: 2.75rem;
    }

    .btn-save-premium {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 0.8rem 2rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
    }

    .btn-save-premium:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        color: white;
    }
    
    .btn-save-premium:disabled {
        background: #cbd5e1;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    .btn-cancel-premium {
        background: #f1f5f9;
        color: #475569;
        border: none;
        padding: 0.8rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-cancel-premium:hover {
        background: #e2e8f0;
        color: #1e293b;
    }

    /* Select2 Modernization */
    .select2-container--bootstrap-5 .select2-selection {
        border-radius: 10px !important;
        padding: 0.5rem 0.75rem !important;
        height: auto !important;
        border: 1px solid #e2e8f0 !important;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        color: #334155 !important;
        font-size: 0.95rem !important;
    }
    
    .badge-premium {
        font-size: 0.7rem;
        font-weight: 800;
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Select2 Fixes */
    .select2-container--bootstrap-5 .select2-selection--single {
        background-color: #fcfcfd !important;
        border: 1px solid #e2e8f0 !important;
    }
    .select2-container--bootstrap-5 .select2-selection__rendered {
        color: #334155 !important;
        font-weight: 600 !important;
    }
    .select2-results__option {
        padding: 8px 12px !important;
    }

    /* Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .animate-in {
        animation: fadeIn 0.5s ease forwards;
    }
    
    /* Custom Grid for Items */
    .item-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1rem;
        max-height: 400px;
        overflow-y: auto;
        padding: 4px 8px 4px 4px;
    }
    
    .item-grid::-webkit-scrollbar { width: 6px; }
    .item-grid::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 3px; }
    .item-grid::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }

    .item-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.8rem 1rem;
        cursor: pointer;
        position: relative;
        transition: all 0.15s ease;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .item-card:hover {
        border-color: #4f46e5;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    .item-card.selected {
        border-color: #4f46e5;
        background-color: #eef2ff;
        box-shadow: 0 0 0 1px #4f46e5;
    }
    
    .item-checkbox {
        width: 1.1rem;
        height: 1.1rem;
        accent-color: #4f46e5;
    }
    
    .edit-container .nav-tabs {
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 1.5rem;
    }
    
    .edit-container .nav-link {
        color: #64748b;
        font-weight: 600;
        border: none;
        background: transparent;
        padding: 0.75rem 1.5rem;
        border-bottom: 2px solid transparent;
        transition: all 0.2s;
    }
    
    .edit-container .nav-link:hover {
        color: #4f46e5 !important;
        background: rgba(79, 70, 229, 0.05);
    }

    .edit-container .nav-link.active {
        color: #4f46e5 !important;
        border-bottom-color: #4f46e5;
        background: transparent;
    }
    
    .edit-container .summary-box {
        background: #fdfdfd;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.5rem;
    }

    .edit-container .form-section-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .edit-container .form-section-title::after {
        content: '';
        height: 1px;
        flex-grow: 1;
        background: #e2e8f0;
    }

    .price-badge {
        font-size: 0.65rem;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        font-weight: 600;
        border: 1px solid transparent;
    }

    .price-badge.retail {
        background: rgba(79, 70, 229, 0.08);
        color: #4f46e5;
        border-color: rgba(79, 70, 229, 0.2);
    }

    .price-badge.wholesale {
        background: rgba(16, 185, 129, 0.08);
        color: #10b981;
        border-color: rgba(16, 185, 129, 0.2);
    }

    .price-badge.active {
        box-shadow: 0 0 0 2px #fff, 0 0 0 4px currentColor;
    }

    .pricing-mode-selector .btn-check:checked + .btn {
        background-color: currentColor;
        color: #fff !important;
        border-color: transparent !important;
    }
    
    .pricing-mode-selector .btn-outline-primary {
        color: #4f46e5;
        border-color: #e2e8f0;
    }
    
    .pricing-mode-selector .btn-outline-success {
        color: #10b981;
        border-color: #e2e8f0;
    }
</style>
@endpush

@section('content')
<div class="edit-container animate-in">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.finance.dashboard') }}" class="text-muted text-decoration-none">Finance</a></li>
                    <li class="breadcrumb-item active" aria-current="page">New Proforma</li>
                </ol>
            </nav>
            <h4 class="fw-bold text-slate-800 m-0">Create Proforma Invoice</h4>
        </div>
        <a href="{{ route('admin.finance.dashboard') }}" class="btn btn-cancel-premium d-flex align-items-center gap-2">
            <i class="fas fa-times"></i> Cancel
        </a>
    </div>

    <!-- Main Form -->
    <form action="{{ route('admin.finance.proforma.generate') }}" method="POST" id="proformaForm">
        @csrf
        
        <div class="row g-4">
            <!-- Left Column: Details & Items -->
            <div class="col-lg-8">
                <!-- 1. Invoice & Customer Details -->
                <div class="glass-card mb-4">
                    <div class="card-header-premium d-flex justify-content-between align-items-center">
                        <h6><i class="fas fa-file-invoice me-2"></i> INVOICE DETAILS</h6>
                        <span class="badge bg-light text-dark border badge-premium">DRAFT MODE</span>
                    </div>
                    
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-lg-12">
                                <div class="form-section-title mt-0">
                                    <i class="fas fa-user-circle"></i> Customer Information
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label-premium">Customer <span class="text-danger">*</span></label>
                                <select class="form-select select2" name="customer_id" id="customer_select" required>
                                    <option value=""></option>
                                </select>
                                
                                <!-- New Customer Form (hidden by default) -->
                                <div id="new_customer_form" style="display: none;" class="mt-3 p-3 rounded-4 bg-light border">
                                    <h6 class="fw-bold mb-3 small text-uppercase text-primary"><i class="fas fa-plus-circle me-1"></i> New Customer Details</h6>
                                    <div class="mb-3">
                                        <label class="form-label-premium small">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="customer_name" class="form-control-premium form-control-sm" placeholder="Full name">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label-premium small">Phone Number <span class="text-danger">*</span></label>
                                        <input type="text" name="customer_phone" class="form-control-premium form-control-sm" placeholder="Phone number">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label-premium small">Email Address</label>
                                        <input type="email" name="customer_email" class="form-control-premium form-control-sm" placeholder="Optional">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label-premium small">Company Name</label>
                                        <input type="text" name="customer_company" class="form-control-premium form-control-sm" placeholder="Optional">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label-premium small">Business Type</label>
                                        <select name="customer_business_type" class="form-select form-select-sm">
                                            <option value="">Select type (optional)</option>
                                            <option value="retail">Retail Store</option>
                                            <option value="wholesale">Wholesale Distributor</option>
                                            <option value="printing">Printing Company</option>
                                            <option value="advertising">Advertising Agency</option>
                                            <option value="corporate">Corporate</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label-premium small">Address</label>
                                        <textarea name="customer_address" class="form-control-premium form-control-sm" rows="2" placeholder="Optional"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label-premium">Department <span class="text-danger">*</span></label>
                                <select class="form-select form-control-premium" name="department_id" required>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ (auth()->user()->department_id == $department->id) ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label-premium">Invoice Date <span class="text-danger">*</span></label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-calendar-alt"></i>
                                    <input type="date" class="form-control-premium" name="invoice_date" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Items Section -->
                <div class="glass-card">
                    <div class="card-header-premium">
                        <h6><i class="fas fa-box-open me-2"></i> INVOICE ITEMS</h6>
                    </div>
                    <div class="card-body p-4">
                        <ul class="nav nav-tabs" id="itemTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" id="services-tab" data-bs-toggle="tab" data-bs-target="#services" type="button">
                                    <i class="fas fa-layer-group me-2"></i>Design Services
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="products-tab" data-bs-toggle="tab" data-bs-target="#products" type="button">
                                    <i class="fas fa-box-open me-2"></i>Inventory Products
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="itemTabsContent">
                            <!-- Services Tab -->
                            <div class="tab-pane fade show active" id="services">
                                <div class="mb-3 input-icon-wrapper">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="form-control-premium" id="searchServices" placeholder="Search services...">
                                </div>
                                <div class="item-grid" id="servicesList">
                                    @forelse($designTaskTypes as $type)
                                        <label class="item-card">
                                            <input type="checkbox" name="design_task_types[]" value="{{ $type->id }}" class="item-checkbox service-checkbox" data-price="{{ $type->price ?? 0 }}">
                                            <div class="flex-grow-1" style="min-width: 0;">
                                                <div class="fw-bold text-dark text-truncate">{{ $type->name }}</div>
                                                <div class="small text-muted text-truncate">{{ $type->description }}</div>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold text-primary">{{ number_format($type->price ?? 0, 0) }}</div>
                                                <div class="small text-muted text-uppercase" style="font-size: 0.6rem;">Service</div>
                                            </div>
                                        </label>
                                    @empty
                                        <div class="text-center py-4 text-muted small">No services found.</div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Products Tab -->
                            <div class="tab-pane fade" id="products">
                                <div class="mb-3 input-icon-wrapper">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="form-control-premium" id="searchProducts" placeholder="Search products...">
                                </div>
                                <div class="item-grid" id="productsList">
                                    @forelse($products as $product)
                                        @php
                                            $retailPrice = $product->getEffectivePriceForChannel('retail');
                                            $wholesalePrice = $product->getEffectivePriceForChannel('wholesale');
                                        @endphp
                                        <label class="item-card">
                                            <input type="checkbox" name="products[]" value="{{ $product->id }}" 
                                                   class="item-checkbox product-checkbox" 
                                                   data-retail-price="{{ $retailPrice }}"
                                                   data-wholesale-price="{{ $wholesalePrice }}">
                                            <div class="flex-grow-1" style="min-width: 0;">
                                                <div class="fw-bold text-dark text-truncate">{{ $product->name }}</div>
                                                <div class="small text-muted">Stock: {{ $product->stock_quantity }}</div>
                                            </div>
                                            <div class="d-flex flex-column align-items-end gap-1">
                                                <span class="price-badge retail">R: {{ number_format($retailPrice, 0) }}</span>
                                                <span class="price-badge wholesale">W: {{ number_format($wholesalePrice, 0) }}</span>
                                            </div>
                                        </label>
                                    @empty
                                        <div class="text-center py-4 text-muted small">No products available.</div>
                                    @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Summary & Actions -->
            <div class="col-lg-4">
                <div class="sticky-top" style="top: 2rem;">
                    <div class="glass-card mb-4 shadow-sm border-0">
                        <div class="card-header-premium bg-white">
                            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-calculator me-2 text-primary"></i> ORDER SUMMARY</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between mb-3 small">
                                <span class="text-muted">Selected Items</span>
                                <span class="fw-bold fs-6" id="selectedCount">0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 small">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-bold fs-6" id="subtotalDisplay">TZS 0</span>
                            </div>
                            
                            <hr class="my-3 text-secondary opacity-25">
                            
                            <div class="pricing-mode-selector mb-4">
                                <label class="form-label-premium small text-uppercase mb-2 d-block text-muted" style="letter-spacing: 0.5px;">Pricing Tier</label>
                                <div class="btn-group w-100 shadow-sm" role="group">
                                    <input type="radio" class="btn-check" name="pricing_mode" id="modeRetail" value="retail" checked autocomplete="off">
                                    <label class="btn btn-outline-primary btn-sm py-2 d-flex flex-column align-items-center" for="modeRetail">
                                        <i class="fas fa-user mb-1"></i> <span>Retail</span>
                                    </label>
                                    <input type="radio" class="btn-check" name="pricing_mode" id="modeWholesale" value="wholesale" autocomplete="off">
                                    <label class="btn btn-outline-success btn-sm py-2 d-flex flex-column align-items-center" for="modeWholesale">
                                        <i class="fas fa-building mb-1"></i> <span>Wholesale</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" role="switch" id="vatSwitch" name="has_vat" value="1" checked>
                                <label class="form-check-label small fw-bold text-dark" for="vatSwitch">Include VAT (18%)</label>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-3 text-danger small p-2 bg-danger bg-opacity-10 rounded-3 d-none" id="vatRow">
                                <span>VAT Amount</span>
                                <span class="fw-bold" id="vatDisplay">TZS 0</span>
                            </div>

                            <div class="d-flex justify-content-between pt-3 border-top mb-4">
                                <span class="h6 fw-bold mb-0 text-dark">Total Due</span>
                                <span class="h6 fw-bold text-primary mb-0 fs-5" id="totalDisplay">TZS 0</span>
                            </div>
                            
                            <div class="mb-0">
                                <label class="form-label-premium small text-muted text-uppercase mb-2 d-block">Notes / Terms</label>
                                <textarea class="form-control-premium w-100" name="notes" rows="3" placeholder="Additional notes or terms..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-3">
                        <button type="submit" class="btn btn-save-premium justify-content-center py-3" id="generateBtn" disabled>
                            <i class="fas fa-print me-2"></i>
                            <span class="text-uppercase" style="letter-spacing: 1px;">Generate Proforma</span>
                        </button>
                        <a href="{{ route('admin.finance.dashboard') }}" class="btn btn-cancel-premium text-center py-3">
                            <i class="fas fa-times me-2"></i> CANCEL
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Search for a customer...',
            allowClear: true,
            ajax: {
                url: "{{ route('admin.finance.proforma.customers.search') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term // search term
                    };
                },
                processResults: function (data) {
                    let results = data.results || [];
                    
                    // Add "Create New" if no exact match or at start
                    results.unshift({
                        id: 'new',
                        text: '+ Create New Customer',
                        isNew: true
                    });
                    
                    return { results: results };
                },
                cache: true
            },
            templateResult: function(data) {
                if (data.loading) return data.text;
                if (data.isNew) {
                    return $('<div class="fw-bold text-primary"><i class="fas fa-plus-circle me-2"></i>' + data.text + '</div>');
                }
                return $('<div><div class="fw-bold">' + data.text + '</div></div>');
            },
            templateSelection: function(data) {
                if (!data.id) return data.text;
                return data.text;
            },
            escapeMarkup: function(m) { return m; },
            minimumInputLength: 0
        });

        // Handle customer change
        $('#customer_select').on('change', function() {
            if ($(this).val() === 'new') {
                $('#new_customer_form').slideDown();
                // Make name and phone required
                $('#new_customer_form input[name="customer_name"]').prop('required', true);
                $('#new_customer_form input[name="customer_phone"]').prop('required', true);
            } else {
                $('#new_customer_form').slideUp();
                $('#new_customer_form input[name="customer_name"]').prop('required', false);
                $('#new_customer_form input[name="customer_phone"]').prop('required', false);
            }
        });

        // Search Handlers
        $('#searchProducts').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#productsList label').filter(function() {
                var text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(value) > -1)
            });
        });

        $('#searchServices').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#servicesList label').filter(function() {
                var text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(value) > -1)
            });
        });

        // Item Selection
        $(document).on('change', '.item-checkbox', function() {
            if($(this).is(':checked')) {
                $(this).closest('.item-card').addClass('selected');
            } else {
                $(this).closest('.item-card').removeClass('selected');
            }
            updateSummary();
        });

        // VAT Toggle
        $('#vatSwitch').on('change', function() {
            if($(this).is(':checked')) {
                $('#vatRow').removeClass('d-none');
            } else {
                $('#vatRow').addClass('d-none');
            }
            updateSummary();
        });

        // Summary Calculation
        function updateSummary() {
            let subtotal = 0;
            let count = 0;
            const mode = $('input[name="pricing_mode"]:checked').val();

            $('.item-checkbox:checked').each(function() {
                count++;
                let price = 0;
                if ($(this).hasClass('product-checkbox')) {
                    price = parseFloat(mode === 'wholesale' ? $(this).data('wholesale-price') : $(this).data('retail-price'));
                } else {
                    price = parseFloat($(this).data('price'));
                }
                subtotal += price;
            });

            $('#selectedCount').text(count);
            $('#subtotalDisplay').text('TZS ' + subtotal.toLocaleString());

            let vat = 0;
            if ($('#vatSwitch').is(':checked')) {
                vat = subtotal * 0.18;
            }

            $('#vatDisplay').text('TZS ' + vat.toLocaleString());
            $('#totalDisplay').text('TZS ' + (subtotal + vat).toLocaleString());
            if (count > 0) {
                $('#generateBtn').removeAttr('disabled');
            } else {
                $('#generateBtn').attr('disabled', 'disabled');
            }
        }

        $('input[name="pricing_mode"]').on('change', function() {
            updateSummary();
            
            // Visual feedback on cards
            if ($(this).val() === 'wholesale') {
                $('.price-badge.wholesale').addClass('active');
                $('.price-badge.retail').removeClass('active');
            } else {
                $('.price-badge.retail').addClass('active');
                $('.price-badge.wholesale').removeClass('active');
            }
        });

        // Update summary on tab change too
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            updateSummary();
        });

        // Trigger initial state
        $('input[name="pricing_mode"]:checked').trigger('change');
        updateSummary(); 
    });
</script>
@endpush
