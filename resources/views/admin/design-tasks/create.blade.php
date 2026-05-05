@extends('layouts.admin')

@section('page-title', 'Create Design Task')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --glass-border: rgba(226, 232, 240, 0.8);
        }

        body {
            font-family: 'Nunito Sans', sans-serif;
            background-color: #f8fafc;
        }

        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-header {
            background: #fff;
            padding: 1.25rem 2rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .card-header h5,
        .card-header h6 {
            font-size: 1rem;
            font-weight: 700;
            color: #000;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #334155;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 0.7rem 1rem;
            border: 1px solid #e2e8f0;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            background-color: #fcfcfd;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            background-color: #fff;
        }

        .form-text {
            display: none !important;
        }

        .customer-section,
        .tasks-section {
            margin-bottom: 2rem;
        }

        .existing-tasks-card {
            margin-top: 1.5rem;
        }

        .task-item {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            background: #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .task-item.collapsed {
            background: #f8fafc;
            padding: 1rem 1.5rem;
        }

        .task-item.collapsed .task-item-body {
            display: none;
        }

        .task-item-header {
            cursor: pointer;
            user-select: none;
        }

        .task-item-header .collapse-icon {
            transition: transform 0.3s ease;
        }

        .task-item.collapsed .collapse-icon {
            transform: rotate(-90deg);
        }

        .task-item-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .task-item.collapsed .task-item-header-content {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .remove-task-btn {
            margin-left: auto;
        }

        .existing-task-item {
            border-left: 3px solid #dc3545;
            padding: 0.75rem 1rem;
            margin-bottom: 0.75rem;
            background: #f8f9fa;
            border-radius: 0 8px 8px 0;
        }

        .task-title-suggestions,
        .task-description-suggestions {
            position: absolute;
            z-index: 1000;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            max-height: 200px;
            overflow-y: auto;
            width: 100%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .task-title-suggestion,
        .task-description-suggestion {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.15s;
        }

        .task-title-suggestion:hover,
        .task-description-suggestion:hover {
            background: #f1f4f9;
        }

        .task-title-suggestion:last-child,
        .task-description-suggestion:last-child {
            border-bottom: none;
        }

        /* Select2 Customization */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 4px;
            padding: 0.375rem 0.75rem;
            height: auto;
            border-color: #dee2e6;
            font-size: 12px;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding-left: 0;
            line-height: 1.5;
            color: #212529;
            font-size: 12px;
        }

        .select2-container--bootstrap-5 .select2-dropdown {
            border-radius: 4px;
            border-color: #dee2e6;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            font-size: 12px;
        }

        .select2-container--bootstrap-5 .select2-search__field {
            border-radius: 4px;
            padding: 0.375rem;
            font-size: 12px;
        }


        .select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected] {
            background-color: #212529;
        }

        /* Override for Receipt Checkbox - Orange Decoration */
        #requires_receipt:checked {
            background-color: #fd7e14 !important;
            border-color: #fd7e14 !important;
        }

        #requires_receipt:focus {
            border-color: #feb272 !important;
            box-shadow: 0 0 0 0.25rem rgba(253, 126, 20, 0.25) !important;
        }

        #requires_receipt {
            border-color: #fd7e14 !important;
            /* Orange border even when unchecked */
            cursor: pointer;
            width: 1.25rem;
            height: 1.25rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">Create Design Task(s)</h5>

                    </div>
                    <a href="{{ route('admin.design-tasks.index') }}" class="btn btn-outline-dark btn-sm"
                        style="font-size: 12px;">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.design-tasks.store') }}" id="taskForm" enctype="multipart/form-data"
            data-no-global-handler>
            @csrf

            <div class="row">
                <!-- Customer Information Section (Left Side) -->
                <div class="col-lg-5 col-md-12 mb-4">
                    <div class="card customer-section" style="height: fit-content;">
                        <div class="card-header border-bottom py-2 bg-white">
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 12px;">
                                <i class="fas fa-user me-2"></i>Customer Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Customer <span class="text-danger">*</span></label>
                                <select class="form-select select2 @error('customer_id') is-invalid @enderror"
                                    id="customer_select" name="customer_id" required>
                                    <option value="">Search customer...</option>
                                    <option value="new">+ Create New Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }} - {{ $customer->phone ?? 'No Phone' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Select an existing customer or create a new one</small>
                            </div>

                            <!-- New Customer Form (hidden by default) -->
                            <div id="new_customer_form" style="display: none;">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Full Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="customer_name"
                                            class="form-control @error('customer_name') is-invalid @enderror"
                                            value="{{ old('customer_name') }}" placeholder="Enter customer full name">
                                        @error('customer_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="email" name="customer_email"
                                            class="form-control @error('customer_email') is-invalid @enderror"
                                            value="{{ old('customer_email') }}"
                                            placeholder="customer@example.com (Optional)">
                                        @error('customer_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Phone <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="customer_phone"
                                            class="form-control @error('customer_phone') is-invalid @enderror"
                                            value="{{ old('customer_phone') }}" placeholder="+255 123 456 789">
                                        @error('customer_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">WhatsApp number preferred</small>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Company Name</label>
                                        <input type="text" name="customer_company" class="form-control"
                                            value="{{ old('customer_company') }}" placeholder="Optional">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Business Type</label>
                                        <select name="customer_business_type" class="form-select">
                                            <option value="">Select type (optional)</option>
                                            <option value="retail" {{ old('customer_business_type') == 'retail' ? 'selected' : '' }}>Retail Store</option>
                                            <option value="wholesale" {{ old('customer_business_type') == 'wholesale' ? 'selected' : '' }}>Wholesale Distributor</option>
                                            <option value="printing" {{ old('customer_business_type') == 'printing' ? 'selected' : '' }}>Printing Company</option>
                                            <option value="advertising" {{ old('customer_business_type') == 'advertising' ? 'selected' : '' }}>Advertising Agency</option>
                                            <option value="corporate" {{ old('customer_business_type') == 'corporate' ? 'selected' : '' }}>Corporate</option>
                                            <option value="other" {{ old('customer_business_type') == 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Business Address</label>
                                        <textarea name="customer_address" rows="2" class="form-control"
                                            placeholder="Optional">{{ old('customer_address') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Existing Customer Info Display -->
                            <div id="existing_customer_info" style="display: none;">
                                <div class="card border mb-3">
                                    <div class="card-header bg-light py-2">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-check text-primary me-2"></i>
                                            <strong class="text-dark mb-0" id="selected_customer_name"></strong>
                                        </div>
                                    </div>
                                    <div class="card-body py-3">
                                        <div class="row g-2">
                                            <div class="col-12" id="customer_email_row" style="display: none;">
                                                <div class="d-flex align-items-start">
                                                    <i class="fas fa-envelope text-muted me-2 mt-1"
                                                        style="width: 18px;"></i>
                                                    <div>
                                                        <small class="text-muted d-block">Email</small>
                                                        <span class="small" id="selected_customer_email"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12" id="customer_phone_row" style="display: none;">
                                                <div class="d-flex align-items-start">
                                                    <i class="fas fa-phone text-muted me-2 mt-1" style="width: 18px;"></i>
                                                    <div>
                                                        <small class="text-muted d-block">Phone</small>
                                                        <span class="small" id="selected_customer_phone"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12" id="customer_company_row" style="display: none;">
                                                <div class="d-flex align-items-start">
                                                    <i class="fas fa-building text-muted me-2 mt-1"
                                                        style="width: 18px;"></i>
                                                    <div>
                                                        <small class="text-muted d-block">Company</small>
                                                        <span class="small" id="selected_customer_company"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12" id="customer_business_type_row" style="display: none;">
                                                <div class="d-flex align-items-start">
                                                    <i class="fas fa-briefcase text-muted me-2 mt-1"
                                                        style="width: 18px;"></i>
                                                    <div>
                                                        <small class="text-muted d-block">Business Type</small>
                                                        <span class="small" id="selected_customer_business_type"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12" id="customer_address_row" style="display: none;">
                                                <div class="d-flex align-items-start">
                                                    <i class="fas fa-map-marker-alt text-muted me-2 mt-1"
                                                        style="width: 18px;"></i>
                                                    <div>
                                                        <small class="text-muted d-block">Address</small>
                                                        <span class="small" id="selected_customer_address"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12" id="customer_website_row" style="display: none;">
                                                <div class="d-flex align-items-start">
                                                    <i class="fas fa-globe text-muted me-2 mt-1" style="width: 18px;"></i>
                                                    <div>
                                                        <small class="text-muted d-block">Website</small>
                                                        <a href="#" target="_blank" class="small"
                                                            id="selected_customer_website"></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12" id="customer_notes_row" style="display: none;">
                                                <div class="d-flex align-items-start">
                                                    <i class="fas fa-sticky-note text-muted me-2 mt-1"
                                                        style="width: 18px;"></i>
                                                    <div>
                                                        <small class="text-muted d-block">Notes</small>
                                                        <span class="small" id="selected_customer_notes"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Existing Tasks for Selected Customer -->
                            <div id="existing_tasks_card" style="display: none;">
                                <div class="card shadow-sm border-0 existing-tasks-card">
                                    <div class="card-header border-bottom py-2 bg-light">
                                        <h6 class="mb-0 fw-semibold" style="font-size: 0.85rem;">
                                            <i class="fas fa-list me-2 text-primary"></i>Existing Tasks
                                        </h6>
                                    </div>
                                    <div class="card-body p-2" id="existing_tasks_list"
                                        style="max-height: 250px; overflow-y: auto;">
                                        <!-- Will be populated via JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tasks Section (Right Side) -->
                <div class="col-lg-7 col-md-12 mb-4">
                    <div class="card tasks-section">
                        <div
                            class="card-header border-bottom py-2 d-flex justify-content-between align-items-center bg-white">
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 12px;">
                                <i class="fas fa-tasks me-2"></i>Design Tasks
                            </h6>
                            <button type="button" class="btn btn-dark btn-sm" onclick="addTask()" style="font-size: 11px;">
                                <i class="fas fa-plus me-1"></i>Add Task
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="tasks_container">
                                <!-- Tasks will be added here dynamically -->
                                @foreach(old('tasks', [0]) as $index => $taskData)
                                    <div class="task-item" data-task-index="{{ $index }}">
                                        <div class="task-item-header-content" onclick="toggleTaskCollapse({{ $index }})">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fas fa-chevron-down collapse-icon"></i>
                                                <h6 class="mb-0 fw-bold">Task #{{ $index + 1 }}</h6>
                                            </div>
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-task-btn"
                                                onclick="event.stopPropagation(); removeTask({{ $index }})"
                                                style="{{ $index === 0 ? 'display: none;' : '' }}">
                                                <i class="fas fa-times"></i> Remove
                                            </button>
                                        </div>
                                        <div class="task-item-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Task Type (Optional)</label>
                                                <select class="form-select task-type-select select2-task-type"
                                                    name="tasks[{{ $index }}][task_type_id]"
                                                    onchange="onTaskTypeChange(this, {{ $index }})"
                                                    data-placeholder="-- Search or Select a Task Type --">
                                                    <option value=""></option>
                                                    @foreach($taskTypes as $type)
                                                        <option value="{{ $type->id }}" data-name="{{ $type->name }}"
                                                            data-price="{{ $type->price }}"
                                                            data-description="{{ $type->description }}">
                                                            {{ $type->name }} ({{ number_format($type->price) }} TZS)
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="form-text text-muted">Selecting a type will auto-fill Title,
                                                    Price, and Description.</small>
                                            </div>


                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Task Title <span
                                                        class="text-danger">*</span></label>
                                                <div class="position-relative">
                                                    <input type="text" name="tasks[{{ $index }}][title]"
                                                        id="task_title_{{ $index }}"
                                                        class="form-control task-title-input @error('tasks.' . $index . '.title') is-invalid @enderror"
                                                        value="{{ old('tasks.' . $index . '.title') }}" required
                                                        placeholder="Enter task title" autocomplete="off">
                                                    <div class="task-title-suggestions" id="title_suggestions_{{ $index }}"
                                                        style="display: none;"></div>
                                                </div>
                                                @error('tasks.' . $index . '.title')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="form-text text-muted">Type to see suggestions from existing
                                                    titles</small>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Description</label>
                                                <div class="position-relative">
                                                    <textarea name="tasks[{{ $index }}][description]"
                                                        class="form-control task-description-input @error('tasks.' . $index . '.description') is-invalid @enderror"
                                                        rows="3"
                                                        placeholder="Describe design requirements...">{{ old('tasks.' . $index . '.description') }}</textarea>
                                                    <div class="task-description-suggestions"
                                                        id="description_suggestions_{{ $index }}" style="display: none;"></div>
                                                </div>
                                                @error('tasks.' . $index . '.description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="form-text text-muted">Customer's design requirements</small>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Reference Images</label>
                                                <input type="file" name="tasks[{{ $index }}][reference_images][]"
                                                    class="form-control @error('tasks.' . $index . '.reference_images.*') is-invalid @enderror"
                                                    accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" multiple>
                                                @error('tasks.' . $index . '.reference_images.*')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="form-text text-muted">Max 5MB per image</small>
                                                <div class="task-image-preview mt-2" id="image_preview_{{ $index }}"></div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Priority <span
                                                                class="text-danger">*</span></label>
                                                        <select name="tasks[{{ $index }}][priority]"
                                                            class="form-select @error('tasks.' . $index . '.priority') is-invalid @enderror"
                                                            required>
                                                            <option value="1" {{ old('tasks.' . $index . '.priority') == 1 ? 'selected' : '' }}>High</option>
                                                            <option value="3" {{ old('tasks.' . $index . '.priority', 3) == 3 ? 'selected' : '' }}>Medium</option>
                                                            <option value="5" {{ old('tasks.' . $index . '.priority') == 5 ? 'selected' : '' }}>Low</option>
                                                        </select>
                                                        @error('tasks.' . $index . '.priority')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Qty <span
                                                                class="text-danger">*</span></label>
                                                        <input type="number" name="tasks[{{ $index }}][qty]"
                                                            class="form-control task-qty-input" id="task_qty_{{ $index }}"
                                                            value="{{ old('tasks.' . $index . '.qty', 1) }}" step="0.01"
                                                            min="0.01" required oninput="calculateTaskTotal({{ $index }})">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Rate <span
                                                                class="text-danger">*</span></label>
                                                        <input type="number" name="tasks[{{ $index }}][rate]"
                                                            class="form-control task-rate-input" id="task_rate_{{ $index }}"
                                                            value="{{ old('tasks.' . $index . '.rate', 0) }}" step="0.01"
                                                            min="0" required oninput="calculateTaskTotal({{ $index }})">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Amount (TZS)</label>
                                                        <input type="number" name="tasks[{{ $index }}][price]"
                                                            class="form-control task-price-input bg-light"
                                                            id="task_price_{{ $index }}"
                                                            value="{{ old('tasks.' . $index . '.price', 0) }}" readonly>
                                                        @error('tasks.' . $index . '.price')
                                                            <div class="invalid-feedback text-danger small">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Delivery Cost & Discount -->
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">
                                                            <i class="fas fa-truck me-1 text-info"></i> Delivery Cost (TZS)
                                                        </label>
                                                        <input type="number" name="tasks[{{ $index }}][delivery_cost]"
                                                            class="form-control task-delivery-cost-input"
                                                            id="task_delivery_cost_{{ $index }}"
                                                            value="{{ old('tasks.' . $index . '.delivery_cost', 0) }}"
                                                            step="0.01" min="0" placeholder="0.00"
                                                            oninput="calculateTaskTotal({{ $index }})">
                                                        <small class="form-text text-muted">Additional delivery/shipping
                                                            charge</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">
                                                            <i class="fas fa-percent me-1 text-success"></i> Delivery Discount
                                                            (TZS)
                                                        </label>
                                                        <input type="number" name="tasks[{{ $index }}][delivery_discount]"
                                                            class="form-control task-delivery-discount-input"
                                                            id="task_delivery_discount_{{ $index }}"
                                                            value="{{ old('tasks.' . $index . '.delivery_discount', 0) }}"
                                                            step="0.01" min="0" placeholder="0.00"
                                                            oninput="calculateTaskTotal({{ $index }})">
                                                        <small class="form-text text-muted">Discount applied to delivery
                                                            charge</small>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Department <span
                                                                class="text-danger">*</span></label>
                                                        <select name="tasks[{{ $index }}][department_id]"
                                                            class="form-select @error('tasks.' . $index . '.department_id') is-invalid @enderror"
                                                            required>
                                                            <option value="">Select Department</option>
                                                            @foreach($departments as $dept)
                                                                <option value="{{ $dept->id }}" {{ old('tasks.' . $index . '.department_id') == $dept->id ? 'selected' : '' }}>
                                                                    {{ $dept->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('tasks.' . $index . '.department_id')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Saler</label>
                                                        <select name="tasks[{{ $index }}][saler_id]"
                                                            class="form-select task-saler-select @error('tasks.' . $index . '.saler_id') is-invalid @enderror"
                                                            id="saler_select_{{ $index }}" data-task-index="{{ $index }}">
                                                            <option value="">Not specified</option>
                                                            @foreach($salers as $saler)
                                                                <option value="{{ $saler->id }}" {{ old('tasks.' . $index . '.saler_id') == $saler->id ? 'selected' : '' }}>
                                                                    {{ $saler->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('tasks.' . $index . '.saler_id')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror

                                                        <!-- Saler Details Display -->
                                                        <div id="saler_info_{{ $index }}" class="mt-2" style="display: none;">
                                                            <div class="card border bg-light p-2">
                                                                <small class="text-muted d-block mb-1"><strong>Saler
                                                                        Details:</strong></small>
                                                                <small class="d-block" id="saler_name_{{ $index }}"></small>
                                                                <small class="d-block text-muted"
                                                                    id="saler_phone_{{ $index }}"></small>
                                                                <small class="d-block text-muted"
                                                                    id="saler_email_{{ $index }}"></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Designer</label>
                                                        <select name="tasks[{{ $index }}][designer_id]"
                                                            class="form-select task-designer-select @error('tasks.' . $index . '.designer_id') is-invalid @enderror"
                                                            id="designer_select_{{ $index }}" data-task-index="{{ $index }}">
                                                            <option value="">Not assigned</option>
                                                            @foreach($designers as $designer)
                                                                <option value="{{ $designer->id }}" {{ old('tasks.' . $index . '.designer_id') == $designer->id ? 'selected' : '' }}>
                                                                    {{ $designer->name }} ({{ ucfirst($designer->role) }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('tasks.' . $index . '.designer_id')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                        <small class="form-text text-muted">Optional - assign later</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Operator</label>
                                                        <select name="tasks[{{ $index }}][operator_id]"
                                                            class="form-select task-operator-select @error('tasks.' . $index . '.operator_id') is-invalid @enderror"
                                                            id="operator_select_{{ $index }}" data-task-index="{{ $index }}">
                                                            <option value="">Not assigned</option>
                                                            @foreach($operators as $operator)
                                                                <option value="{{ $operator->id }}" {{ old('tasks.' . $index . '.operator_id') == $operator->id ? 'selected' : '' }}>
                                                                    {{ $operator->name }} ({{ ucfirst($operator->role) }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('tasks.' . $index . '.operator_id')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                        <small class="form-text text-muted">Optional - assign later</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Designer Instructions (shown when designer or operator is selected) -->
                                            <div class="mb-3" id="designer_instructions_row_{{ $index }}"
                                                style="{{ old('tasks.' . $index . '.designer_id') || old('tasks.' . $index . '.operator_id') ? 'display: block;' : 'display: none;' }}">
                                                <label class="form-label fw-semibold">Instructions for Team</label>
                                                <textarea name="tasks[{{ $index }}][designer_instructions]"
                                                    class="form-control task-designer-instructions @error('tasks.' . $index . '.designer_instructions') is-invalid @enderror"
                                                    rows="4"
                                                    placeholder="Provide clear instructions for the designer or operator...">{{ old('tasks.' . $index . '.designer_instructions') }}</textarea>
                                                @error('tasks.' . $index . '.designer_instructions')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="form-text text-muted">Optional. These instructions will be printed
                                                    automatically.</small>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Deadline</label>
                                                @php
                                                    $oldDeadline = old('tasks.' . $index . '.deadline');
                                                    $dDay = $oldDeadline ? \Carbon\Carbon::parse($oldDeadline)->format('d') : '';
                                                    $dMonth = $oldDeadline ? \Carbon\Carbon::parse($oldDeadline)->format('m') : '';
                                                    $dYear = $oldDeadline ? \Carbon\Carbon::parse($oldDeadline)->format('Y') : '';
                                                    $dTime = $oldDeadline ? \Carbon\Carbon::parse($oldDeadline)->format('H:i') : '';
                                                @endphp
                                                <div class="row g-2">
                                                    <div class="col-3">
                                                        <select class="form-select deadline-day" id="deadline_day_{{ $index }}"
                                                            onchange="updateHiddenDeadline({{ $index }})">
                                                            <option value="">Day</option>
                                                            @for($i = 1; $i <= 31; $i++)
                                                                <option value="{{ sprintf('%02d', $i) }}" {{ $dDay == sprintf('%02d', $i) ? 'selected' : '' }}>{{ $i }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                    <div class="col-4">
                                                        <select class="form-select deadline-month"
                                                            id="deadline_month_{{ $index }}"
                                                            onchange="updateHiddenDeadline({{ $index }})">
                                                            <option value="">Month</option>
                                                            @foreach(['01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec'] as $val => $label)
                                                                <option value="{{ $val }}" {{ $dMonth == $val ? 'selected' : '' }}>
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-3">
                                                        <select class="form-select deadline-year"
                                                            id="deadline_year_{{ $index }}"
                                                            onchange="updateHiddenDeadline({{ $index }})">
                                                            <option value="">Year</option>
                                                            @for($i = date('Y'); $i <= date('Y') + 5; $i++)
                                                                <option value="{{ $i }}" {{ $dYear == $i ? 'selected' : '' }}>{{ $i }}
                                                                </option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                    <div class="col-2">
                                                        <input type="time" class="form-control deadline-time"
                                                            id="deadline_time_{{ $index }}"
                                                            onchange="updateHiddenDeadline({{ $index }})" value="{{ $dTime }}">
                                                    </div>
                                                </div>
                                                <input type="hidden" name="tasks[{{ $index }}][deadline]"
                                                    id="deadline_input_{{ $index }}"
                                                    class="@error('tasks.' . $index . '.deadline') is-invalid @enderror"
                                                    value="{{ old('tasks.' . $index . '.deadline') }}">
                                                @error('tasks.' . $index . '.deadline')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="form-text text-muted">Optional</small>
                                            </div>
                                        </div><!-- End task-item-body -->
                                    </div>
                                @endforeach
                            </div>

                            <!-- Payment & Receipt Options Section -->
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header border-bottom py-2 bg-white">
                                    <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">
                                        <i class="fas fa-receipt me-2"></i>Payment & Receipt Options
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Total Amount Paid (TZS) <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" name="amount_paid" id="global_amount_paid"
                                                    class="form-control @error('amount_paid') is-invalid @enderror"
                                                    value="{{ old('amount_paid', 0) }}" step="0.01" min="0" required
                                                    placeholder="0.00">
                                                @error('amount_paid')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="form-text text-muted">Total amount paid for all tasks</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Payment Method <span
                                                        class="text-danger">*</span></label>
                                                <select name="payment_method"
                                                    class="form-select @error('payment_method') is-invalid @enderror"
                                                    required>
                                                    <option value="Cash" {{ old('payment_method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                                    <option value="Mobile Money" {{ old('payment_method') == 'Mobile Money' ? 'selected' : '' }}>Mobile Money</option>
                                                    <option value="Bank Transfer" {{ old('payment_method') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                                    <option value="Card" {{ old('payment_method') == 'Card' ? 'selected' : '' }}>Card</option>
                                                </select>
                                                @error('payment_method')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="form-text text-muted">Select how the customer paid</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Overall Balance (TZS)</label>
                                                <input type="number" id="global_balance" class="form-control" value="0"
                                                    readonly placeholder="0.00">
                                                <small class="form-text text-muted">Remaining balance for all tasks</small>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-3">

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="requires_receipt"
                                                id="requires_receipt" value="1" {{ old('requires_receipt') == '1' ? 'checked' : '' }} autocomplete="off">
                                            <label class="form-check-label fw-semibold" for="requires_receipt">
                                                Include 18% VAT (Standard Receipt)
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">Receipt will print automatically upon saving,
                                            with or without VAT.</small>
                                    </div>

                                    <!-- Receipt Summary -->
                                    <div id="receipt_summary" class="mt-3" style="display: none;"
                                        onclick="event.stopPropagation();">
                                        <div class="alert alert-danger mb-3">
                                            <strong id="summary_title">Price Summary:</strong>
                                            <div class="mt-2">
                                                <div class="d-flex justify-content-between">
                                                    <span>Subtotal:</span>
                                                    <span id="receipt_subtotal">0.00 TZS</span>
                                                </div>
                                                <div class="d-flex justify-content-between" id="vat_row">
                                                    <span>VAT (18%):</span>
                                                    <span id="receipt_vat">0.00 TZS</span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>Paid:</span>
                                                    <span id="receipt_total_paid">0.00 TZS</span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>Balance:</span>
                                                    <span id="receipt_total_balance" class="text-danger">0.00 TZS</span>
                                                </div>
                                                <hr class="my-2" style="border-top: 2px solid #f8b4b9;">
                                                <div class="d-flex justify-content-between fw-bold"
                                                    style="font-size: 1.1rem; color: #721c24; padding-top: 5px;">
                                                    <span>TOTAL AMOUNT:</span>
                                                    <span id="receipt_total" style="font-size: 1.2rem;">0.00 TZS</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="d-flex gap-2 mb-4">
                        <button type="submit" class="btn btn-dark" id="submitTaskForm" data-no-global-handler
                            style="font-size: 13px;">
                            <i class="fas fa-save me-2"></i>Create Task
                        </button>
                        <button type="button" class="btn btn-outline-dark" id="reprintReceiptBtn" style="font-size: 13px;">
                            <i class="fas fa-print me-2"></i>Reprint
                        </button>
                        <a href="{{ route('admin.design-tasks.index') }}" class="btn btn-outline-dark"
                            style="font-size: 13px;">
                            Cancel
                        </a>
                    </div>


                </div>
            </div>
        </form>
    </div>



    <script>
        // Handle Task Type Change - Auto-fill fields
        function onTaskTypeChange(selectElement, index) {
            const option = selectElement.options[selectElement.selectedIndex];
            if (!option.value) return;

            const name = option.getAttribute('data-name');
            const price = option.getAttribute('data-price');
            const description = option.getAttribute('data-description');

            // Fill fields
            if (name) {
                const descInput = document.querySelector(`textarea[name="tasks[${index}][description]"]`);
                if (descInput) {
                    descInput.value = name + (description ? "\n" + description : "");
                }
            }

            if (price) {
                const qtyInput = document.getElementById(`task_qty_${index}`) || document.querySelector(`input[name="tasks[${index}][qty]"]`);
                const rateInput = document.getElementById(`task_rate_${index}`) || document.querySelector(`input[name="tasks[${index}][rate]"]`);

                if (qtyInput) qtyInput.value = 1;
                if (rateInput) {
                    rateInput.value = price;
                    if (typeof calculateTaskTotal === 'function') {
                        calculateTaskTotal(index);
                    } else {
                        updateReceiptSummary();
                    }
                }
            }
        }

    </script>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            // Initialize Variables
            let taskIndex = {{ count(old('tasks', [0])) }};
            const existingTitles = @json($existingTitles ?? []);
            const taskTypeData = @json($taskTypes ?? []);
            let currentCustomerId = null;

            // Initialize Select2
            $(document).ready(function () {
                $('#customer_select').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Search for a customer...',
                    allowClear: true,
                    templateResult: formatCustomerOption,
                    templateSelection: formatCustomerSelection
                });

                function formatCustomerOption(state) {
                    if (!state.id || state.id === 'new') return state.text;

                    let [name, phone] = state.text.split(' - ');
                    let $state = $(
                        '<div class="d-flex justify-content-between align-items-center">' +
                        '<div>' +
                        '<div class="fw-bold">' + name + '</div>' +
                        (phone ? '<small class="text-muted"><i class="fas fa-phone-alt me-1" style="font-size: 0.7rem;"></i>' + phone + '</small>' : '') +
                        '</div>' +
                        '</div>'
                    );
                    return $state;
                }

                function formatCustomerSelection(state) {
                    if (!state.id || !state.text) return state.text;
                    // Return only the name (everything before the ' - ')
                    return state.text.split(' - ')[0];
                }

                // Handle Select2 change event
                $('#customer_select').on('change', function () {
                    // Trigger native change event for existing logic
                    this.dispatchEvent(new Event('change'));
                });
            });

            // Saler data for JavaScript
            const salersData = {
                @foreach($salers as $saler)
                                                        {{ $saler->id }}: {
                        name: @json($saler->name),
                        email: @json($saler->email ?? ''),
                        phone: @json($saler->phone ?? ''),
                    },
                @endforeach
                                };

            // Company information for receipts
            const companyInfo = {
                name: 'CHIBO BRANDS CO.LTD',
                location: 'Dar es Salaam, Tanzania',
                phone: '+255 655 392 319',
                phone2: '+255 711 711 111',
                tin: '154 747 214',
                website: 'www.chibobrand.com'
            };

            // Current user info
            const currentUser = {
                name: @json(auth()->user()->name ?? 'Admin'),
                email: @json(auth()->user()->email ?? ''),
                phone: @json(auth()->user()->phone ?? '')
            };

            // Show/hide new customer form
            // Customer data for JavaScript
            const customersData = {
                @foreach($customers as $customer)
                                                        {{ $customer->id }}: {
                        name: @json($customer->name),
                        email: @json($customer->email ?? ''),
                        phone: @json($customer->phone ?? ''),
                        company_name: @json($customer->company_name ?? ''),
                        business_type: @json($customer->business_type ?? ''),
                        address: @json($customer->address ?? ''),
                        website: @json($customer->website ?? ''),
                        notes: @json($customer->notes ?? ''),
                        is_wholesale: {{ $customer->is_wholesale ? 'true' : 'false' }},
                        tax_id: @json($customer->tax_id ?? '')
                    },
                @endforeach
                                };

            document.getElementById('customer_select').addEventListener('change', function () {
                const selectedValue = this.value;
                const newCustomerForm = document.getElementById('new_customer_form');
                const existingCustomerInfo = document.getElementById('existing_customer_info');
                const existingTasksCard = document.getElementById('existing_tasks_card');

                if (selectedValue === 'new') {
                    newCustomerForm.style.display = 'block';
                    existingCustomerInfo.style.display = 'none';
                    existingTasksCard.style.display = 'none';
                    currentCustomerId = null;
                } else if (selectedValue) {
                    newCustomerForm.style.display = 'none';
                    existingCustomerInfo.style.display = 'block';
                    existingTasksCard.style.display = 'block';
                    currentCustomerId = selectedValue;

                    // Get customer data from the customersData object
                    const customerData = customersData[selectedValue];

                    if (customerData) {
                        // Set customer name only in header
                        document.getElementById('selected_customer_name').textContent = customerData.name;

                        // Show/hide and populate email
                        const emailRow = document.getElementById('customer_email_row');
                        const emailField = document.getElementById('selected_customer_email');
                        if (customerData.email) {
                            emailField.textContent = customerData.email;
                            emailRow.style.display = 'flex';
                        } else {
                            emailRow.style.display = 'none';
                        }

                        // Show/hide and populate phone
                        const phoneRow = document.getElementById('customer_phone_row');
                        const phoneField = document.getElementById('selected_customer_phone');
                        if (customerData.phone) {
                            phoneField.textContent = customerData.phone;
                            phoneRow.style.display = 'flex';
                        } else {
                            phoneRow.style.display = 'none';
                        }

                        // Show/hide and populate company name
                        const companyRow = document.getElementById('customer_company_row');
                        const companyField = document.getElementById('selected_customer_company');
                        if (customerData.company_name) {
                            companyField.textContent = customerData.company_name;
                            companyRow.style.display = 'flex';
                        } else {
                            companyRow.style.display = 'none';
                        }

                        // Show/hide and populate business type
                        const businessTypeRow = document.getElementById('customer_business_type_row');
                        const businessTypeField = document.getElementById('selected_customer_business_type');
                        if (customerData.business_type) {
                            // Capitalize first letter
                            const businessType = customerData.business_type.charAt(0).toUpperCase() +
                                customerData.business_type.slice(1).replace(/_/g, ' ');
                            businessTypeField.textContent = businessType;
                            businessTypeRow.style.display = 'flex';
                        } else {
                            businessTypeRow.style.display = 'none';
                        }

                        // Show/hide and populate address
                        const addressRow = document.getElementById('customer_address_row');
                        const addressField = document.getElementById('selected_customer_address');
                        if (customerData.address) {
                            addressField.textContent = customerData.address;
                            addressRow.style.display = 'flex';
                        } else {
                            addressRow.style.display = 'none';
                        }

                        // Show/hide and populate website
                        const websiteRow = document.getElementById('customer_website_row');
                        const websiteField = document.getElementById('selected_customer_website');
                        if (customerData.website) {
                            let websiteUrl = customerData.website;
                            if (!websiteUrl.startsWith('http://') && !websiteUrl.startsWith('https://')) {
                                websiteUrl = 'https://' + websiteUrl;
                            }
                            websiteField.href = websiteUrl;
                            websiteField.textContent = customerData.website;
                            websiteRow.style.display = 'flex';
                        } else {
                            websiteRow.style.display = 'none';
                        }

                        // Show/hide and populate notes
                        const notesRow = document.getElementById('customer_notes_row');
                        const notesField = document.getElementById('selected_customer_notes');
                        if (customerData.notes) {
                            notesField.textContent = customerData.notes;
                            notesRow.style.display = 'flex';
                        } else {
                            notesRow.style.display = 'none';
                        }
                    } else {
                        // Fallback to option text if data not found
                        const option = this.options[this.selectedIndex];
                        document.getElementById('selected_customer_name').textContent = option.text;
                        // Hide all detail rows
                        ['email', 'phone', 'company', 'business_type', 'address', 'website', 'notes'].forEach(field => {
                            document.getElementById(`customer_${field}_row`).style.display = 'none';
                        });
                    }

                    // Load existing tasks
                    loadExistingTasks(selectedValue);
                } else {
                    newCustomerForm.style.display = 'none';
                    existingCustomerInfo.style.display = 'none';
                    const existingTasksCard = document.getElementById('existing_tasks_card');
                    if (existingTasksCard) {
                        existingTasksCard.style.display = 'none';
                    }
                    currentCustomerId = null;
                }
            });

            // Load existing tasks for customer
            function loadExistingTasks(customerId) {
                const url = '{{ route("admin.design-tasks.customer-tasks", ["customerId" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', customerId);
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        const container = document.getElementById('existing_tasks_list');
                        if (data.tasks && data.tasks.length > 0) {
                            let html = '<div class="mt-1">';
                            data.tasks.forEach(task => {
                                html += `<div class="existing-task-item mb-2 pb-2 border-bottom">
                                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                                            <strong class="small">${task.title}</strong>
                                                            <span class="badge small" style="background-color: ${getStatusColor(task.status)}; color: white;">${task.status_label}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <small class="text-muted">${task.created_at}</small>
                                                            <a href="${task.show_url}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.7rem;">View</a>
                                                        </div>
                                                    </div>`;
                            });
                            html += '</div>';
                            container.innerHTML = html;
                        } else {
                            container.innerHTML = '<p class="text-muted small mb-0">No existing tasks</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading tasks:', error);
                        document.getElementById('existing_tasks_list').innerHTML =
                            '<p class="text-danger small">Error loading existing tasks.</p>';
                    });
            }

            function getStatusColor(status) {
                const colors = {
                    'pending': '#ffc107',
                    'in_progress': '#0d6efd',
                    'in_review': '#6f42c1',
                    'completed': '#198754',
                    'rejected': '#dc3545'
                };
                return colors[status] || '#6c757d';
            }

            // Date helper functions
            function getDayOptions() {
                let options = '<option value="">Day</option>';
                for (let i = 1; i <= 31; i++) {
                    let val = i.toString().padStart(2, '0');
                    options += `<option value="${val}">${i}</option>`;
                }
                return options;
            }

            function getMonthOptions() {
                const months = { '01': 'Jan', '02': 'Feb', '03': 'Mar', '04': 'Apr', '05': 'May', '06': 'Jun', '07': 'Jul', '08': 'Aug', '09': 'Sep', '10': 'Oct', '11': 'Nov', '12': 'Dec' };
                let options = '<option value="">Month</option>';
                for (let [val, label] of Object.entries(months)) {
                    options += `<option value="${val}">${label}</option>`;
                }
                return options;
            }

            function getYearOptions() {
                let currentYear = new Date().getFullYear();
                let options = '<option value="">Year</option>';
                for (let i = currentYear; i <= currentYear + 5; i++) {
                    options += `<option value="${i}">${i}</option>`;
                }
                return options;
            }

            function updateHiddenDeadline(index) {
                const day = document.getElementById(`deadline_day_${index}`).value;
                const month = document.getElementById(`deadline_month_${index}`).value;
                const year = document.getElementById(`deadline_year_${index}`).value;
                const time = document.getElementById(`deadline_time_${index}`).value;
                const input = document.getElementById(`deadline_input_${index}`);

                if (day && month && year && time) {
                    input.value = `${year}-${month}-${day}T${time}`;
                } else {
                    input.value = '';
                }
            }

            // Add new task
            function addTask() {
                // Collapse all existing tasks before adding a new one
                collapseAllTasks();

                const container = document.getElementById('tasks_container');
                const taskHtml = `
                                        <div class="task-item" data-task-index="${taskIndex}">
                                            <div class="task-item-header-content" onclick="toggleTaskCollapse(${taskIndex})">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fas fa-chevron-down collapse-icon"></i>
                                                    <h6 class="mb-0 fw-bold">Task #${taskIndex + 1}</h6>
                                                </div>
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-task-btn" onclick="event.stopPropagation(); removeTask(${taskIndex})">
                                                    <i class="fas fa-times"></i> Remove
                                                </button>
                                            </div>
                                            <div class="task-item-body">

                                            <div class="mb-3">
                                                <label class="form-label">Task Type (Optional)</label>
                                                <select class="form-select task-type-select select2-task-type" name="tasks[${taskIndex}][task_type_id]" 
                                                        onchange="onTaskTypeChange(this, ${taskIndex})" data-placeholder="-- Search or Select a Task Type --">
                                                    <option value=""></option>
                                                    ${taskTypeData.map(type => `
                                                        <option value="${type.id}" 
                                                                data-name="${type.name}" 
                                                                data-price="${type.price}"
                                                                data-description="${type.description || ''}">
                                                            ${type.name} (${new Intl.NumberFormat().format(type.price)} TZS)
                                                        </option>
                                                    `).join('')}
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Task Title <span class="text-danger">*</span></label>
                                                <div class="position-relative">
                                                    <input type="text" name="tasks[${taskIndex}][title]" class="form-control task-title-input" 
                                                           required placeholder="Enter task title" autocomplete="off">
                                                    <div class="task-title-suggestions" id="title_suggestions_${taskIndex}" style="display: none;"></div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <div class="position-relative">
                                                    <textarea name="tasks[${taskIndex}][description]" class="form-control task-description-input" rows="3" 
                                                              placeholder="Describe design requirements..."></textarea>
                                                    <div class="task-description-suggestions" id="description_suggestions_${taskIndex}" style="display: none;"></div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Reference Images</label>
                                                <input type="file" name="tasks[${taskIndex}][reference_images][]" 
                                                       class="form-control" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" multiple>
                                                <div class="task-image-preview mt-2" id="image_preview_${taskIndex}"></div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Priority <span class="text-danger">*</span></label>
                                                        <select name="tasks[${taskIndex}][priority]" class="form-select" required>
                                                            <option value="3" selected>Medium</option>
                                                            <option value="1">High</option>
                                                            <option value="5">Low</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="mb-3">
                                                        <label class="form-label">Qty <span class="text-danger">*</span></label>
                                                        <input type="number" name="tasks[${taskIndex}][qty]" 
                                                               class="form-control task-qty-input" 
                                                               id="task_qty_${taskIndex}"
                                                               value="1" step="0.01" min="0.01" required
                                                               oninput="calculateTaskTotal(${taskIndex})">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label">Rate <span class="text-danger">*</span></label>
                                                        <input type="number" name="tasks[${taskIndex}][rate]" 
                                                               class="form-control task-rate-input" 
                                                               id="task_rate_${taskIndex}"
                                                               value="0" step="0.01" min="0" required
                                                               oninput="calculateTaskTotal(${taskIndex})">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Amount (TZS)</label>
                                                        <input type="number" name="tasks[${taskIndex}][price]" 
                                                               class="form-control task-price-input bg-light" 
                                                               id="task_price_${taskIndex}"
                                                               value="0" readonly>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Department <span class="text-danger">*</span></label>
                                                        <select name="tasks[${taskIndex}][department_id]" class="form-select" required>
                                                            <option value="">Select Department</option>
                                                            @foreach($departments as $dept)
                                                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Saler</label>
                                                        <select name="tasks[${taskIndex}][saler_id]" class="form-select task-saler-select" 
                                                                id="saler_select_${taskIndex}" data-task-index="${taskIndex}">
                                                            <option value="">Not specified</option>
                                                            @foreach($salers as $saler)
                                                                <option value="{{ $saler->id }}">{{ $saler->name }}</option>
                                                            @endforeach
                                                        </select>

                                                        <!-- Saler Details Display -->
                                                        <div id="saler_info_${taskIndex}" class="mt-2" style="display: none;">
                                                            <div class="card border bg-light p-2">
                                                                <small class="text-muted d-block mb-1"><strong>Saler Details:</strong></small>
                                                                <small class="d-block" id="saler_name_${taskIndex}"></small>
                                                                <small class="d-block text-muted" id="saler_phone_${taskIndex}"></small>
                                                                <small class="d-block text-muted" id="saler_email_${taskIndex}"></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Designer</label>
                                                        <select name="tasks[${taskIndex}][designer_id]" class="form-select task-designer-select" 
                                                                id="designer_select_${taskIndex}" data-task-index="${taskIndex}">
                                                            <option value="">Not assigned</option>
                                                            @foreach($designers as $designer)
                                                                <option value="{{ $designer->id }}">{{ $designer->name }} ({{ ucfirst($designer->role) }})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Operator</label>
                                                        <select name="tasks[${taskIndex}][operator_id]" class="form-select task-operator-select" 
                                                                id="operator_select_${taskIndex}" data-task-index="${taskIndex}">
                                                            <option value="">Not assigned</option>
                                                            @foreach($operators as $operator)
                                                                <option value="{{ $operator->id }}">{{ $operator->name }} ({{ ucfirst($operator->role) }})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Designer Instructions (shown when designer or operator is selected) -->
                                            <div class="mb-3" id="designer_instructions_row_${taskIndex}" style="display: none;">
                                                <label class="form-label">Instructions for Team</label>
                                                <textarea name="tasks[${taskIndex}][designer_instructions]" 
                                                          class="form-control task-designer-instructions" 
                                                          rows="4" 
                                                          placeholder="Provide clear instructions for the designer or operator..."></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Deadline</label>
                                                <div class="row g-2">
                                                    <div class="col-3">
                                                        <select class="form-select deadline-day" id="deadline_day_${taskIndex}" onchange="updateHiddenDeadline(${taskIndex})">
                                                            ${getDayOptions()}
                                                        </select>
                                                    </div>
                                                    <div class="col-4">
                                                        <select class="form-select deadline-month" id="deadline_month_${taskIndex}" onchange="updateHiddenDeadline(${taskIndex})">
                                                            ${getMonthOptions()}
                                                        </select>
                                                    </div>
                                                    <div class="col-3">
                                                         <select class="form-select deadline-year" id="deadline_year_${taskIndex}" onchange="updateHiddenDeadline(${taskIndex})">
                                                            ${getYearOptions()}
                                                        </select>
                                                    </div>
                                                    <div class="col-2">
                                                        <input type="time" class="form-control deadline-time" id="deadline_time_${taskIndex}" onchange="updateHiddenDeadline(${taskIndex})">
                                                    </div>
                                                </div>
                                                <input type="hidden" name="tasks[${taskIndex}][deadline]" id="deadline_input_${taskIndex}">
                                            </div>
                                            </div><!-- End task-item-body -->
                                        </div>
                                    `;
                container.insertAdjacentHTML('beforeend', taskHtml);

                // Initialize suggestions for new task
                initializeTitleSuggestions(taskIndex);
                initializeDescriptionSuggestions(taskIndex);

                // Initialize image preview for new task
                initializeImagePreview(taskIndex);

                // Initialize saler selection for new task
                initializeSalerSelection(taskIndex);

                // Initialize designer selection for new task
                initializeDesignerSelection(taskIndex);

                // Initialize global calculation for new task
                const currentTaskIndex = taskIndex;
                const priceInput = document.getElementById(`task_price_${currentTaskIndex}`);

                if (priceInput) {
                    priceInput.addEventListener('input', () => {
                        updateReceiptSummary();
                    });
                }

                // Initialize Select2 for the new task type select
                initializeTaskTypeSelect(taskIndex);

                taskIndex++;

                // Show remove button for first task if there are multiple tasks
                updateRemoveButtons();
            }

            // Initialize Select2 for Task Type
            function initializeTaskTypeSelect(index) {
                const selector = index !== undefined ?
                    `[data-task-index="${index}"] .select2-task-type` :
                    '.select2-task-type';

                $(selector).each(function () {
                    $(this).select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        allowClear: true,
                        placeholder: $(this).data('placeholder')
                    });
                });
            }

            // Toggle task collapse
            function toggleTaskCollapse(index) {
                const taskItem = document.querySelector(`[data-task-index="${index}"]`);
                if (taskItem) {
                    taskItem.classList.toggle('collapsed');
                }
            }

            function calculateTaskTotal(index) {
                const qtyInput = document.getElementById(`task_qty_${index}`);
                const rateInput = document.getElementById(`task_rate_${index}`);
                const priceInput = document.getElementById(`task_price_${index}`);

                if (qtyInput && rateInput && priceInput) {
                    const qty = parseFloat(qtyInput.value) || 0;
                    const rate = parseFloat(rateInput.value) || 0;
                    const total = qty * rate;
                    priceInput.value = total.toFixed(2);
                    updateReceiptSummary();
                }
            }

            // Collapse all tasks
            function collapseAllTasks() {
                document.querySelectorAll('.task-item').forEach(item => {
                    item.classList.add('collapsed');
                });
            }

            // Remove task
            function removeTask(index) {
                const taskItem = document.querySelector(`[data-task-index="${index}"]`);
                if (taskItem) {
                    taskItem.remove();
                    updateRemoveButtons();
                    renumberTasks();
                    updateReceiptSummary(); // Update receipt summary after removing a task
                }
            }

            // Update remove buttons visibility
            function updateRemoveButtons() {
                const taskItems = document.querySelectorAll('.task-item');
                taskItems.forEach((item, idx) => {
                    const removeBtn = item.querySelector('.remove-task-btn');
                    if (removeBtn) {
                        removeBtn.style.display = taskItems.length > 1 ? 'block' : 'none';
                    }
                });
            }

            // Renumber tasks
            function renumberTasks() {
                document.querySelectorAll('.task-item').forEach((item, index) => {
                    const header = item.querySelector('.task-item-header h6');
                    if (header) {
                        header.textContent = `Task #${index + 1}`;
                    }
                });
            }

            // Initialize title suggestions
            function initializeTitleSuggestions(index) {
                const input = document.querySelector(`[name="tasks[${index}][title]"]`);
                const suggestionsDiv = document.getElementById(`title_suggestions_${index}`);

                if (!input || !suggestionsDiv) return;

                input.addEventListener('input', function () {
                    const value = this.value.toLowerCase().trim();
                    if (value.length > 0) {
                        const filtered = existingTitles.filter(title =>
                            title.toLowerCase().includes(value) && title.toLowerCase() !== value
                        );
                        if (filtered.length > 0) {
                            suggestionsDiv.innerHTML = filtered.slice(0, 5).map(title =>
                                `<div class="task-title-suggestion" onclick="selectTitle(${index}, '${title.replace(/'/g, "\\'")}')">${title}</div>`
                            ).join('');
                            suggestionsDiv.style.display = 'block';
                        } else {
                            suggestionsDiv.style.display = 'none';
                        }
                    } else {
                        suggestionsDiv.style.display = 'none';
                    }
                });

                input.addEventListener('blur', function () {
                    setTimeout(() => {
                        suggestionsDiv.style.display = 'none';
                    }, 200);
                });
            }

            // Select title from suggestions
            function selectTitle(index, title) {
                const input = document.querySelector(`[name="tasks[${index}][title]"]`);
                if (input) {
                    input.value = title;
                    document.getElementById(`title_suggestions_${index}`).style.display = 'none';
                }
            }

            // Initialize description suggestions
            function initializeDescriptionSuggestions(index) {
                const input = document.querySelector(`[name="tasks[${index}][description]"]`);
                const suggestionsDiv = document.getElementById(`description_suggestions_${index}`);

                if (!input || !suggestionsDiv) return;

                input.addEventListener('input', function () {
                    const value = this.value.toLowerCase().trim();
                    if (value.length > 0) {
                        // Suggest from DesignTaskTypes (taskTypeData)
                        const filtered = taskTypeData.filter(type =>
                        (type.name.toLowerCase().includes(value) ||
                            (type.description && type.description.toLowerCase().includes(value)))
                        );

                        if (filtered.length > 0) {
                            suggestionsDiv.innerHTML = filtered.slice(0, 5).map(type => {
                                const fullDesc = type.name + (type.description ? "\n" + type.description : "");
                                const escapedFullDesc = fullDesc.replace(/'/g, "\\'").replace(/\n/g, "\\n");
                                return `<div class="task-description-suggestion" onclick="selectDescription(${index}, '${escapedFullDesc}')">
                                                        <div class="fw-bold small text-dark">${type.name}</div>
                                                        ${type.description ? `<div class="x-small text-muted">${type.description.substring(0, 80)}${type.description.length > 80 ? '...' : ''}</div>` : ''}
                                                    </div>`;
                            }).join('');
                            suggestionsDiv.style.display = 'block';
                        } else {
                            suggestionsDiv.style.display = 'none';
                        }
                    } else {
                        suggestionsDiv.style.display = 'none';
                    }
                });

                input.addEventListener('blur', function () {
                    setTimeout(() => {
                        suggestionsDiv.style.display = 'none';
                    }, 200);
                });
            }

            // Select description from suggestions
            function selectDescription(index, description) {
                const input = document.querySelector(`[name="tasks[${index}][description]"]`);
                if (input) {
                    input.value = description;
                    document.getElementById(`description_suggestions_${index}`).style.display = 'none';
                }
            }

            // Initialize image preview
            function initializeImagePreview(index) {
                const input = document.querySelector(`[name="tasks[${index}][reference_images][]"]`);
                const previewDiv = document.getElementById(`image_preview_${index}`);

                if (!input || !previewDiv) return;

                input.addEventListener('change', function (e) {
                    const files = e.target.files;
                    previewDiv.innerHTML = '';

                    if (files.length > 0) {
                        const container = document.createElement('div');
                        container.className = 'd-flex flex-wrap gap-2';

                        Array.from(files).forEach(file => {
                            if (file.type.startsWith('image/')) {
                                const reader = new FileReader();
                                reader.onload = function (e) {
                                    const imgWrapper = document.createElement('div');
                                    imgWrapper.className = 'position-relative';
                                    imgWrapper.style.width = '100px';
                                    imgWrapper.style.height = '100px';

                                    const img = document.createElement('img');
                                    img.src = e.target.result;
                                    img.className = 'img-thumbnail';
                                    img.style.width = '100%';
                                    img.style.height = '100%';
                                    img.style.objectFit = 'cover';

                                    imgWrapper.appendChild(img);
                                    container.appendChild(imgWrapper);
                                };
                                reader.readAsDataURL(file);
                            }
                        });

                        previewDiv.appendChild(container);
                    }
                });
            }

            // Initialize saler selection handler
            function initializeSalerSelection(index) {
                const salerSelect = document.getElementById(`saler_select_${index}`);
                if (!salerSelect) return;

                salerSelect.addEventListener('change', function () {
                    const selectedSalerId = this.value;
                    const salerInfo = document.getElementById(`saler_info_${index}`);
                    const salerName = document.getElementById(`saler_name_${index}`);
                    const salerPhone = document.getElementById(`saler_phone_${index}`);
                    const salerEmail = document.getElementById(`saler_email_${index}`);

                    if (selectedSalerId && salersData[selectedSalerId]) {
                        const salerData = salersData[selectedSalerId];
                        salerName.textContent = salerData.name;
                        salerPhone.textContent = salerData.phone || 'No phone';
                        salerEmail.textContent = salerData.email || 'No email';
                        salerInfo.style.display = 'block';
                    } else {
                        salerInfo.style.display = 'none';
                    }
                });
            }

            // Designer data for JavaScript
            const designersData = {
                @foreach($designers as $designer)
                                                        {{ $designer->id }}: {
                        name: @json($designer->name),
                        email: @json($designer->email ?? ''),
                        phone: @json($designer->phone ?? ''),
                        role: @json($designer->role),
                    },
                @endforeach
                                };

            // Initialize designer and operator selection handler
            function initializeDesignerSelection(index) {
                const designerSelect = document.getElementById(`designer_select_${index}`);
                const operatorSelect = document.getElementById(`operator_select_${index}`);
                const instructionsRow = document.getElementById(`designer_instructions_row_${index}`);

                if (!designerSelect || !operatorSelect) return;

                const toggleInstructions = () => {
                    if (designerSelect.value || operatorSelect.value) {
                        if (instructionsRow) instructionsRow.style.display = 'block';
                    } else {
                        if (instructionsRow) instructionsRow.style.display = 'none';
                    }
                };

                // Check initial values
                toggleInstructions();

                designerSelect.addEventListener('change', toggleInstructions);
                operatorSelect.addEventListener('change', toggleInstructions);
            }

            // Update balance calculations
            function updateReceiptSummary() {
                const requiresReceipt = document.getElementById('requires_receipt');
                const receiptSummary = document.getElementById('receipt_summary');
                const globalAmountPaidInput = document.getElementById('global_amount_paid');
                const globalBalanceInput = document.getElementById('global_balance');

                const isChecked = requiresReceipt ? requiresReceipt.checked : false;
                const globalAmountPaid = parseFloat(globalAmountPaidInput ? globalAmountPaidInput.value : 0) || 0;

                if (receiptSummary) {
                    receiptSummary.style.display = 'block';
                }

                // Calculate totals from tasks:
                // - task price inputs are base price (qty * rate)
                // - delivery cost/discount are additional adjustments (qty * rate +/- delivery)
                let subtotal = 0;
                let deliveryTotal = 0;
                let deliveryDiscountTotal = 0;

                document.querySelectorAll('.task-price-input').forEach(input => {
                    const price = parseFloat(input.value) || 0;
                    subtotal += price;
                });

                document.querySelectorAll('.task-delivery-cost-input').forEach(input => {
                    const v = parseFloat(input.value) || 0;
                    deliveryTotal += v;
                });

                document.querySelectorAll('.task-delivery-discount-input').forEach(input => {
                    const v = parseFloat(input.value) || 0;
                    deliveryDiscountTotal += v;
                });

                const deliveryDiff = deliveryTotal - deliveryDiscountTotal;

                // Calculate VAT (18%) - VAT applies to base subtotal only
                const vat = isChecked ? subtotal * 0.18 : 0;
                const totalAmount = subtotal + vat + deliveryDiff;
                const totalBalance = Math.max(0, totalAmount - globalAmountPaid);

                // Update Global Balance Input
                if (globalBalanceInput) {
                    globalBalanceInput.value = totalBalance.toFixed(2);
                }

                // Update Summary Display
                const subtotalEl = document.getElementById('receipt_subtotal');
                const vatEl = document.getElementById('receipt_vat');
                const totalEl = document.getElementById('receipt_total');
                const totalPaidEl = document.getElementById('receipt_total_paid');
                const totalBalanceEl = document.getElementById('receipt_total_balance');

                if (subtotalEl) {
                    subtotalEl.textContent = subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TZS';
                }
                if (vatEl) {
                    vatEl.textContent = vat.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TZS';
                    const vatRow = document.getElementById('vat_row');
                    if (vatRow) vatRow.style.display = vat > 0 ? 'flex' : 'none';

                    const summaryTitle = document.getElementById('summary_title');
                    if (summaryTitle) {
                        summaryTitle.textContent = vat > 0 ? 'Price Summary (with 18% VAT):' : 'Price Summary (Standard):';
                    }
                }
                if (totalEl) {
                    totalEl.textContent = totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TZS';
                }
                if (totalPaidEl) {
                    totalPaidEl.textContent = globalAmountPaid.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TZS';
                }
                if (totalBalanceEl) {
                    totalBalanceEl.textContent = totalBalance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TZS';
                }
            }

            // Generate receipt HTML - Professional Design Matching Kibada Cosmetics Style
            function generateReceiptHTML(customerData, tasks, subtotal, vat, total, totalPaid = 0, totalBalance = 0, includesVat = true, paymentMethod = 'CASH') {
                const now = new Date();
                const receiptNumber = 'INV-' + String(Date.now()).slice(-5);
                const date = now.toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
                const time = now.toLocaleTimeString('en-GB', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });

                // Generate QR code data
                const qrData = receiptNumber;

                return `
                                <!DOCTYPE html>
                                <html>
                                <head>
                                    <title>Receipt - ${receiptNumber}</title>
                                    <style>
                                        @import url('https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&display=swap');

                                        * {
                                            margin: 0;
                                            padding: 0;
                                            box-sizing: border-box;
                                        }

                                        body {
                                            font-family: 'Courier Prime', 'Courier New', monospace;
                                            max-width: 80mm;
                                            margin: 0 auto;
                                           padding: 5mm;
                                            background: white;
                                            font-size: 12px;
                                            line-height: 1.4;
                                        }

                                        .receipt {
                                            width: 100%;
                                            background: white;
                                        }

                                        .header {
                                            text-align: center;
                                            margin-bottom: 10px;
                                            padding-bottom: 10px;
                                            border-bottom: 2px dashed #000;
                                        }

                                        .company-name {
                                            font-size: 18px;
                                            font-weight: bold;
                                            text-transform: uppercase;
                                            margin-bottom: 5px;
                                            letter-spacing: 1px;
                                        }

                                        .company-info {
                                            font-size: 10px;
                                            margin: 3px 0;
                                        }

                                        .company-phone {
                                            font-size: 10px;
                                            margin: 3px 0;
                                        }

                                        .tin {
                                            font-size: 10px;
                                            margin: 3px 0;
                                            font-weight: bold;
                                        }

                                        .separator-dashed {
                                            border-top: 1px dashed #000;
                                            margin: 8px 0;
                                        }

                                        .separator-solid {
                                            border-top: 1px solid #000;
                                            margin: 8px 0;
                                        }

                                        .receipt-info {
                                            margin: 8px 0;
                                            font-size: 11px;
                                        }

                                        .info-row {
                                            display: flex;
                                            justify-content: space-between;
                                            margin: 3px 0;
                                            font-size: 11px;
                                        }

                                        .info-label {
                                            font-weight: bold;
                                            text-transform: uppercase;
                                        }

                                        .items-header {
                                            margin-top: 10px;
                                            margin-bottom: 5px;
                                            padding: 5px 0;
                                            border-bottom: 1px solid #000;
                                        }

                                        .items-header-row {
                                            display: grid;
                                            grid-template-columns: 2fr 0.6fr 1.2fr 1.4fr;
                                            font-weight: bold;
                                            text-transform: uppercase;
                                            font-size: 10px;
                                            padding: 3px 0;
                                        }

                                        .item-row {
                                            display: grid;
                                            grid-template-columns: 2fr 0.6fr 1.2fr 1.4fr;
                                            padding: 4px 0;
                                            font-size: 11px;
                                            border-bottom: 1px dotted #ccc;
                                        }

                                        .item-row:last-child {
                                            border-bottom: none;
                                        }

                                        .item-name {
                                            font-weight: bold;
                                        }

                                        .item-name small {
                                            display: block;
                                            margin-top: 2px;
                                        }

                                        .item-qty {
                                            text-align: center;
                                        }

                                        .item-price {
                                            text-align: right;
                                        }

                                        .item-total {
                                            text-align: right;
                                            font-weight: bold;
                                        }

                                        .totals-section {
                                            margin-top: 10px;
                                            padding-top: 8px;
                                        }

                                        .total-row {
                                            display: flex;
                                            justify-content: space-between;
                                            margin: 4px 0;
                                            font-size: 12px;
                                        }

                                        .total-label {
                                            font-weight: bold;
                                        }

                                        .total-value {
                                            font-weight: bold;
                                            text-align: right;
                                        }

                                        .grand-total {
                                            font-size: 14px;
                                            font-weight: bold;
                                            margin-top: 5px;
                                            padding-top: 5px;
                                            border-top: 1px solid #000;
                                            border-bottom: 1px solid #000;
                                            padding-bottom: 5px;
                                        }

                                        .payment-section {
                                            margin-top: 10px;
                                            padding-top: 8px;
                                        }

                                        .payment-row {
                                            display: flex;
                                            justify-content: space-between;
                                            margin: 3px 0;
                                            font-size: 11px;
                                        }

                                        .qr-section {
                                            text-align: center;
                                            margin: 15px 0;
                                            padding: 10px 0;
                                        }

                                        .footer {
                                            text-align: center;
                                            margin-top: 15px;
                                            padding-top: 10px;
                                            border-top: 1px dashed #000;
                                            font-size: 10px;
                                            line-height: 1.5;
                                        }

                                        .footer-message {
                                            margin: 5px 0;
                                            font-style: italic;
                                        }

                                        .website {
                                            margin-top: 5px;
                                            font-weight: bold;
                                        }

                                        @media print {
                                            body {
                                                margin: 0;
                                                padding: 5mm;
                                            }
                                            @page {
                                                size: 80mm auto;
                                                margin: 0;
                                            }
                                        }
                                    </style>
                                </head>
                                <body>
                                    <div class="receipt">
                                        <!-- Header Section -->
                                        <div class="header">
                                            <div class="company-name">${companyInfo.name}</div>
                                            <div class="company-info">KINONDONI, MWIJUMA ROAD</div>
                                            <div class="company-info">DAR ES SALAAM, TANZANIA</div>
                                            <div class="company-phone">TEL: ${companyInfo.phone} | ${companyInfo.phone2}</div>
                                            <div class="tin">TIN: ${companyInfo.tin}</div>
                                        </div>



                                        <!-- Receipt Details -->
                                        <div class="receipt-info">
                                            <div class="info-row">
                                                <span class="info-label">RECEIPT #:</span>
                                                <span>${receiptNumber}</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">DATE:</span>
                                                <span>${date} ${time}</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">CUSTOMER:</span>
                                                <span>${customerData && customerData.name ? customerData.name : 'Walk-in Customer'}</span>
                                            </div>
                                            ${customerData && customerData.phone ? `
                                            <div class="info-row">
                                                <span class="info-label">PHONE:</span>
                                                <span>${customerData.phone}</span>
                                            </div>
                                            ` : ''}
                                            <div class="info-row">
                                                <span class="info-label">ISSUED BY:</span>
                                                <span>${currentUser.name}</span>
                                            </div>
                                            ${tasks.length > 0 && tasks[0].saler ? `
                                            <div class="info-row">
                                                <span class="info-label">SALESPERSON:</span>
                                                <span>${tasks[0].saler} ${tasks[0].salerPhone ? `(${tasks[0].salerPhone})` : ''}</span>
                                            </div>
                                            ` : `
                                            <div class="info-row">
                                                <span class="info-label">CONTACT PERSON:</span>
                                                <span>${currentUser.name} ${currentUser.phone ? `(${currentUser.phone})` : ''}</span>
                                            </div>
                                            `}
                                        </div>

                                        <div class="separator-dashed"></div>

                                        <!-- Tasks Section -->
                                        <div class="items-header">
                                            <div class="items-header-row">
                                                <span>ITEM</span>
                                                <span style="text-align: center;">QTY</span>
                                                <span class="item-price">RATE</span>
                                                <span class="item-total">TOTAL</span>
                                            </div>
                                        </div>

                                        ${tasks.map((task, index) => `
                                        <div class="item-row">
                                            <div class="item-name">
                                                ${task.title}
                                            </div>
                                            <div class="item-qty">${parseFloat(task.qty || 1).toFixed(1)}</div>
                                            <div class="item-price">${parseFloat(task.rate || 0).toFixed(2)}</div>
                                            <div class="item-total">${(parseFloat(task.price || 0) + parseFloat(task.delivery_cost || 0) - parseFloat(task.delivery_discount || 0)).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                                        </div>
                                        `).join('')}

                                        <div class="separator-dashed"></div>

                                        <!-- Totals Section -->
                                        <div class="totals-section">
                                            <div class="total-row">
                                                <span class="total-label">SUBTOTAL:</span>
                                                <span class="total-value">${subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} TZS</span>
                                            </div>
                                            ${vat > 0 ? `
                                            <div class="total-row">
                                                <span class="total-label">VAT (18%):</span>
                                                <span class="total-value">${vat.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} TZS</span>
                                            </div>
                                            ` : ''}
                                            <div class="total-row grand-total" style="font-size: 16px; padding: 8px 0; border-top: 1px solid #000; border-bottom: 1px solid #000; margin-top: 5px;">
                                                <span class="total-label">TOTAL:</span>
                                                <span class="total-value">${total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} TZS</span>
                                            </div>
                                            <div class="total-row">
                                                <span class="total-label">AMOUNT PAID:</span>
                                                <span class="total-value">${totalPaid.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} TZS</span>
                                            </div>
                                            ${totalBalance > 0 ? `
                                            <div class="total-row" style="color: #d32f2f; font-weight: bold;">
                                                <span class="total-label">BALANCE:</span>
                                                <span class="total-value">${totalBalance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} TZS</span>
                                            </div>
                                            ` : ''}
                                        </div>



                                        <!-- Payment Section -->
                                        <div class="payment-section">
                                            <div class="payment-row">
                                                <span class="info-label">PAYMENT METHOD:</span>
                                                <span>${paymentMethod.toUpperCase()}</span>
                                            </div>
                                            <div class="payment-row">
                                                <span class="info-label">CHECKED BY:</span>
                                                <span>${currentUser.name}</span>
                                            </div>
                                        </div>

                                        <!-- QR Code Section -->
                                        <div class="qr-section">
                                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${encodeURIComponent(qrData)}" alt="QR Code: ${qrData}" width="100" height="100" style="display: block; margin: 0 auto;" />
                                            <div style="font-size: 10px; margin-top: 5px;">${qrData}</div>
                                        </div>

                                        <!-- Footer -->
                                        <div class="footer">
                                            <div class="footer-message">THANK YOU FOR SHOPPING WITH US!</div>
                                            <div class="footer-message" style="font-weight: bold; margin-top: 5px; font-size: 11px;">“CREATIVITY MEETS TECHNOLOGY”</div>
                                            <div class="website">${companyInfo.website}</div>
                                            <div style="margin-top: 10px; text-align: center; border-top: 1px dotted #ccc; padding-top: 5px;">
                                                <div style="font-size: 8px; color: #444;">Developed by Fridoltech</div>
                                                <div style="font-size: 8px; color: #0066cc; font-weight: bold;">www.fridoltech.org</div>
                                            </div>
                                        </div>
                                    </div>
                                </body>
                                </html>
                                    `;
            }



            // Initialize on page load
            document.addEventListener('DOMContentLoaded', function () {
                // Initialize all existing tasks
                const existingTasks = document.querySelectorAll('.task-item');
                existingTasks.forEach(task => {
                    const index = parseInt(task.getAttribute('data-task-index'));

                    initializeTitleSuggestions(index);
                    initializeDescriptionSuggestions(index);
                    initializeImagePreview(index);
                    initializeSalerSelection(index);
                    initializeDesignerSelection(index);

                    // Initialize price listeners
                    const priceInput = document.getElementById(`task_price_${index}`);
                    if (priceInput) {
                        priceInput.addEventListener('input', () => {
                            updateReceiptSummary();
                        });
                    }
                    // Initialize Task Type Select2
                    initializeTaskTypeSelect(index);
                });

                // Initialize global amount paid listener
                const globalAmountPaidInput = document.getElementById('global_amount_paid');
                if (globalAmountPaidInput) {
                    globalAmountPaidInput.addEventListener('input', () => {
                        updateReceiptSummary();
                    });
                }

                // Initialize receipt checkbox handler
                const receiptCheckbox = document.getElementById('requires_receipt');
                if (receiptCheckbox) {
                    receiptCheckbox.addEventListener('change', function (e) {
                        updateReceiptSummary();
                    });

                    receiptCheckbox.addEventListener('click', function (e) {
                        e.stopPropagation();
                    });

                    updateReceiptSummary();
                }

                // Update remove buttons
                updateRemoveButtons();

                // Handle customer selection if pre-selected
                const customerSelect = document.getElementById('customer_select');
                if (customerSelect.value) {
                    customerSelect.dispatchEvent(new Event('change'));
                }

                // Handle form submission - simple double-submission prevention
                const taskForm = document.getElementById('taskForm');
                const submitBtn = document.getElementById('submitTaskForm');

                if (taskForm && submitBtn) {
                    taskForm.addEventListener('submit', function (e) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
                    });
                }

                                    // NEW: Automatic print after successful save
                                    @if(session('print_receipt_data'))
                                                                                        const sessionReceiptData = @json(session('print_receipt_data'));
                                        setTimeout(() => {
                                            printReceiptAutomatically(sessionReceiptData);
                                        }, 500);
                                    @endif

                                    @if(session('print_tasks_data'))
                                                                                        const sessionTasksData = @json(session('print_tasks_data'));
                                        setTimeout(() => {
                                            printTasksAutomatically(sessionTasksData);
                                        }, 1500); // Wait a bit longer if both are printing
                                    @endif

                    // Function to automatically print designer instructions in popup
                    function printTasksAutomatically(tasksData) {
                        try {
                            const printWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes');
                            if (!printWindow) {
                                alert('Please allow popups to print designer instructions.');
                                return;
                            }
                            const printContent = generateInstructionsHTMLForPrint(tasksData);
                            printWindow.document.write(printContent);
                            printWindow.document.close();

                            setTimeout(() => {
                                printWindow.focus();
                                printWindow.print();
                            }, 1000);
                        } catch (error) {
                            console.error('Error printing instructions:', error);
                        }
                    }

                function generateInstructionsHTMLForPrint(tasksData) {
                    const now = new Date();
                    const date = now.toLocaleDateString('en-US', {
                        year: 'numeric', month: 'long', day: 'numeric'
                    }) + ' at ' + now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

                    const customerData = tasksData[0]?.customer || null;

                    return `
                                <!DOCTYPE html>
                                <html>
                                <head>
                                    <title>Design Task Instructions</title>
                                    <style>
                                        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
                                        .header { text-align: center; border-bottom: 3px solid #dc3545; padding-bottom: 10px; margin-bottom: 20px; }
                                        .company-name { font-size: 24px; font-weight: bold; color: #dc3545; }
                                        .info-section { margin-bottom: 20px; background: #f8f9fa; padding: 15px; border-radius: 5px; }
                                        .info-row { padding: 5px 0; }
                                        .info-label { font-weight: bold; display: inline-block; width: 120px; }
                                        .task-section { margin: 20px 0; page-break-inside: avoid; border: 1px solid #ddd; border-radius: 5px; padding: 15px; }
                                        .task-header { background: #dc3545; color: white; padding: 8px 12px; margin: -15px -15px 12px -15px; border-radius: 5px 5px 0 0; font-weight: bold; }
                                        .instructions-box { background: #fff3cd; border: 2px solid #ffc107; border-radius: 5px; padding: 12px; margin: 10px 0; white-space: pre-wrap; font-size: 14px; }
                                        .priority-badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-weight: bold; font-size: 12px; }
                                        .priority-high { background: #dc3545; color: white; }
                                        .priority-medium { background: #ffc107; color: #000; }
                                        .priority-low { background: #198754; color: white; }
                                        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; border-top: 1px solid #ddd; padding-top: 15px; }
                                        @media print { body { margin: 0; padding: 10px; } .task-section { page-break-inside: avoid; } }
                                    </style>
                                </head>
                                <body>
                                    <div class="header">
                                        <div class="company-name">CHIBO BRAND</div>
                                        <div style="font-weight: bold; margin-top: 5px;">JOB SHEET / DESIGN INSTRUCTIONS</div>
                                    </div>
                                    <div class="info-section">
                                        <div class="info-row"><span class="info-label">Date:</span><span>${date}</span></div>
                                        ${customerData ? `<div class="info-row"><span class="info-label">Customer:</span><span>${customerData.name}</span></div>` : ''}
                                    </div>
                                    ${tasksData.map((task, index) => `
                                    <div class="task-section">
                                        <div class="task-header">Task #${index + 1}: ${task.title} <span class="priority-badge priority-${(task.priority || 'Medium').toLowerCase()}">${task.priority || 'Medium'}</span></div>
                                        ${task.designer ? `<div class="info-row"><span class="info-label">Designer:</span><span><strong>${task.designer}</strong></span></div>` : ''}
                                        ${task.deadline ? `<div class="info-row"><span class="info-label">Deadline:</span><span>${task.deadline}</span></div>` : ''}
                                        ${task.description ? `<div style="margin: 10px 0;"><strong>Description:</strong><p style="margin: 5px 0; font-size: 14px;">${task.description}</p></div>` : ''}
                                        ${task.designer_instructions ? `<div style="margin-top: 15px;"><strong>Instructions:</strong><div class="instructions-box">${task.designer_instructions}</div></div>` : ''}
                                    </div>`).join('')}
                                    <div class="footer">CHIBO BRAND - Creativity Meets Technology</div>
                                </body>
                                </html>`;
                }

                // Manual Print Receipt Button Handler
                const reprintBtn = document.getElementById('reprintReceiptBtn');
                if (reprintBtn) {
                    reprintBtn.addEventListener('click', function (e) {
                        e.preventDefault();
                        const receiptData = prepareReceiptData();
                        if (receiptData) {
                            printReceiptAutomatically(receiptData);
                        } else {
                            alert('Please ensure all required fields (Customer, Tasks, Price) are filled.');
                        }
                    });
                }

                // Function to automatically print receipt in popup
                function printReceiptAutomatically(receiptData) {
                    try {
                        const receiptWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes');

                        if (!receiptWindow) {
                            alert('Please allow popups to print the receipt automatically.');
                            return;
                        }

                        const receiptContent = generateReceiptHTML(
                            receiptData.customerData,
                            receiptData.tasks,
                            receiptData.subtotal,
                            receiptData.vat,
                            receiptData.total,
                            receiptData.totalPaid,
                            receiptData.totalBalance,
                            receiptData.requires_receipt,
                            receiptData.paymentMethod || 'CASH'
                        );

                        receiptWindow.document.write(receiptContent);
                        receiptWindow.document.close();

                        let hasPrinted = false;
                        // Define a single print function to ensure it's only called once
                        const triggerPrint = () => {
                            if (hasPrinted || !receiptWindow || receiptWindow.closed) return;
                            hasPrinted = true;
                            receiptWindow.focus();
                            receiptWindow.print();
                        };

                        // Wait for content, then print
                        receiptWindow.onload = triggerPrint;

                        // Fallback for cases where onload might not fire as expected
                        setTimeout(triggerPrint, 1000);
                    } catch (error) {
                        console.error('Error printing receipt:', error);
                        alert('Error opening print dialog. Please try again.');
                    }
                }

                // Prepare receipt data for printing
                function prepareReceiptData() {
                    const requiresReceipt = document.getElementById('requires_receipt').checked;
                    // if (!requiresReceipt) return null; // Always return data now

                    const customerSelect = document.getElementById('customer_select');
                    const customerId = customerSelect.value;
                    let customerData = null;

                    if (customerId && customerId !== 'new') {
                        customerData = customersData[customerId];
                    } else if (customerId === 'new') {
                        customerData = {
                            name: document.querySelector('[name="customer_name"]')?.value || '',
                            email: document.querySelector('[name="customer_email"]')?.value || '',
                            phone: document.querySelector('[name="customer_phone"]')?.value || '',
                            company_name: document.querySelector('[name="customer_company"]')?.value || ''
                        };
                    }

                    let subtotal = 0;
                    let deliveryTotal = 0;
                    let deliveryDiscountTotal = 0;
                    const tasks = [];
                    const container = document.getElementById('tasks_container');
                    if (!container) return null;

                    const taskItems = container.querySelectorAll('.task-item');
                    taskItems.forEach((item) => {
                        const titleInput = item.querySelector('.task-title-input');
                        const title = titleInput ? titleInput.value.trim() : '';

                        const priceInput = item.querySelector('.task-price-input');
                        const price = parseFloat(priceInput?.value) || 0;

                        const deliveryCostInput = item.querySelector('.task-delivery-cost-input');
                        const deliveryCost = parseFloat(deliveryCostInput?.value) || 0;

                        const deliveryDiscountInput = item.querySelector('.task-delivery-discount-input');
                        const deliveryDiscount = parseFloat(deliveryDiscountInput?.value) || 0;

                        const qtyInput = item.querySelector('.task-qty-input');
                        const qty = parseFloat(qtyInput?.value) || 1;

                        const rateInput = item.querySelector('.task-rate-input');
                        const rate = parseFloat(rateInput?.value) || 0;

                        const salerSelect = item.querySelector('.task-saler-select');
                        const salerId = salerSelect?.value || '';
                        const salerName = salerId && salersData[salerId] ? salersData[salerId].name : '';
                        const salerPhone = salerId && salersData[salerId] ? salersData[salerId].phone : '';

                        const designerSelect = item.querySelector('.task-designer-select');
                        const designerId = designerSelect?.value || '';
                        const designerName = designerId && designersData[designerId] ? designersData[designerId].name : '';
                        const designerPhone = designerId && designersData[designerId] ? designersData[designerId].phone : '';

                        if (title && price > 0) {
                            subtotal += price;
                            deliveryTotal += deliveryCost;
                            deliveryDiscountTotal += deliveryDiscount;
                            tasks.push({
                                title: title,
                                price: price,
                                qty: qty,
                                rate: rate,
                                delivery_cost: deliveryCost,
                                delivery_discount: deliveryDiscount,
                                saler: salerName,
                                salerPhone: salerPhone,
                                designer: designerName,
                                designerPhone: designerPhone
                            });
                        }
                    });

                    const globalAmountPaid = parseFloat(document.getElementById('global_amount_paid')?.value) || 0;
                    const vat = requiresReceipt ? subtotal * 0.18 : 0;
                    const deliveryDiff = deliveryTotal - deliveryDiscountTotal;
                    const totalAmount = subtotal + vat + deliveryDiff;
                    const totalBalance = Math.max(0, totalAmount - globalAmountPaid);

                    return {
                        customerData: customerData,
                        tasks: tasks,
                        subtotal: subtotal,
                        vat: vat,
                        total: totalAmount,
                        totalPaid: globalAmountPaid,
                        totalBalance: totalBalance,
                        requires_receipt: requiresReceipt,
                        paymentMethod: document.querySelector('[name="payment_method"]')?.value || 'Cash'
                    };
                }
            });
        </script>
    @endpush
@endsection