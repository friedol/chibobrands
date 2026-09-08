@extends('layouts.admin')

@section('page-title', 'Edit Design Task')

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

    body {
        font-family: 'Nunito Sans', sans-serif;
        background-color: #f8fafc;
    }

    .edit-container {
        padding: 2rem 1rem;
        max-width: 1000px;
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

    .reason-box {
        background: #fff1f2;
        border: 1px solid #fecdd3;
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 2rem;
    }

    .reason-box .form-label-premium {
        color: #9f1239;
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

    .btn-save-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        color: white;
    }

    .btn-save-premium:active {
        transform: translateY(0);
    }

    .btn-cancel-premium {
        background: #f1f5f9;
        color: #475569;
        border: none;
        padding: 0.8rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.2s;
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

    /* Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .animate-in {
        animation: fadeIn 0.5s ease forwards;
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
                    <li class="breadcrumb-item"><a href="{{ route('admin.design-tasks.index') }}" class="text-muted text-decoration-none">Tasks</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $designTask->task_code }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold text-slate-800 m-0">Modify Design Task</h4>
        </div>
        <a href="{{ route('admin.design-tasks.show', $designTask) }}" class="btn btn-cancel-premium d-flex align-items-center gap-2">
            <i class="fas fa-times"></i> Discard Changes
        </a>
    </div>
    

    <!-- Main Form -->
    <form method="POST" action="{{ route('admin.design-tasks.update', $designTask) }}" id="editTaskForm" data-no-global-handler>
        @csrf
        @method('PUT')
        
        <div class="glass-card">
            <div class="card-header-premium d-flex justify-content-between align-items-center">
                <h6><i class="fas fa-layer-group me-2"></i> TASK {{ $designTask->task_code }}</h6>
                <span class="badge bg-light text-dark border badge-premium">Current: {{ $designTask->status_label }}</span>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <!-- Basic Info Section -->
                <div class="form-section-title">Essential Information</div>
                <div class="row g-4 mb-5">
                    <div class="col-12">
                        <label class="form-label-premium">Task Title <span class="text-danger">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-heading"></i>
                            <input type="text" name="title" class="form-control-premium @error('title') is-invalid @enderror" 
                                   value="{{ old('title', $designTask->title) }}" placeholder="What's this task called?" required>
                        </div>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-premium">Priority Status <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select form-control-premium @error('priority') is-invalid @enderror" required>
                            <option value="1" {{ old('priority', $designTask->priority) == 1 ? 'selected' : '' }}>High Priority</option>
                            <option value="3" {{ old('priority', $designTask->priority) == 3 ? 'selected' : '' }}>Medium Priority</option>
                            <option value="5" {{ old('priority', $designTask->priority) == 5 ? 'selected' : '' }}>Low Priority</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-premium">Submission Deadline</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-calendar-alt"></i>
                            <input type="datetime-local" name="deadline" class="form-control-premium @error('deadline') is-invalid @enderror" 
                                   value="{{ old('deadline', $designTask->deadline ? $designTask->deadline->format('Y-m-d\TH:i') : '') }}">
                        </div>
                    </div>
                </div>

                <!-- Logistics & Team Section -->
                <div class="form-section-title">Assignment & Logistics</div>
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <label class="form-label-premium">Department <span class="text-danger">*</span></label>
                        <select name="department_id" class="form-select form-control-premium select2 @error('department_id') is-invalid @enderror" required>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $designTask->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-premium">Assigned Saler</label>
                        <select name="saler_id" class="form-select form-control-premium select2">
                            <option value="">Not specified</option>
                            @foreach($salers as $saler)
                                <option value="{{ $saler->id }}" {{ old('saler_id', $designTask->saler_id) == $saler->id ? 'selected' : '' }}>
                                    {{ $saler->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-premium">Assigned Designer</label>
                        <select name="designer_id" class="form-select form-control-premium select2">
                            <option value="">Awaiting assignment</option>
                            @foreach($designers as $designer)
                                <option value="{{ $designer->id }}" {{ old('designer_id', $designTask->designer_id) == $designer->id ? 'selected' : '' }}>
                                    {{ $designer->name }} ({{ ucfirst($designer->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-premium">Assigned Operator</label>
                        <select name="operator_id" class="form-select form-control-premium select2">
                            <option value="">Awaiting assignment</option>
                            @foreach($operators as $operator)
                                <option value="{{ $operator->id }}" {{ old('operator_id', $designTask->operator_id) == $operator->id ? 'selected' : '' }}>
                                    {{ $operator->name }} ({{ ucfirst($operator->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Financials Section -->
                <div class="form-section-title">Service Details & Billing</div>
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <label class="form-label-premium">Quantity <span class="text-danger">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-cubes"></i>
                            <input type="number" id="edit_qty" name="qty" class="form-control-premium @error('qty') is-invalid @enderror" 
                                   value="{{ old('qty', $designTask->qty) }}" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-premium">Rate per Unit (TZS) <span class="text-danger">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-tag"></i>
                            <input type="number" id="edit_rate" name="rate" class="form-control-premium @error('rate') is-invalid @enderror" 
                                   value="{{ old('rate', $designTask->rate) }}" step="0.01" min="0" required>
                        </div>
                    </div>

                    <!-- Delivery Fields -->
                    <div class="col-md-6">
                        <label class="form-label-premium">
                            <i class="fas fa-truck me-1 text-info"></i> Delivery Cost (TZS)
                        </label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-truck text-info"></i>
                            <input type="number" id="edit_delivery_cost" name="delivery_cost" 
                                   class="form-control-premium @error('delivery_cost') is-invalid @enderror" 
                                   value="{{ old('delivery_cost', $designTask->delivery_cost ?? 0) }}" 
                                   step="0.01" min="0" placeholder="0.00">
                        </div>
                        @error('delivery_cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <span class="text-muted" style="font-size: 0.75rem;">Additional delivery/shipping charge</span>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-premium">
                            <i class="fas fa-percent me-1 text-success"></i> Delivery Discount (TZS)
                        </label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-percent text-success"></i>
                            <input type="number" id="edit_delivery_discount" name="delivery_discount" 
                                   class="form-control-premium @error('delivery_discount') is-invalid @enderror" 
                                   value="{{ old('delivery_discount', $designTask->delivery_discount ?? 0) }}" 
                                   step="0.01" min="0" placeholder="0.00">
                        </div>
                        @error('delivery_discount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <span class="text-muted" style="font-size: 0.75rem;">Discount applied to delivery charge</span>
                    </div>

                    <!-- VAT Toggle -->
                    <div class="col-12">
                        <div class="p-3 rounded-3 border d-flex align-items-center justify-content-between" style="background:#f0fdf4;">
                            <div>
                                <div class="fw-semibold" style="font-size:13px;"><i class="fas fa-file-invoice me-1 text-warning"></i> VAT (18%)</div>
                                <div class="text-muted" style="font-size:11px;">Enable if this task requires a VAT receipt</div>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="edit_requires_receipt" name="requires_receipt" value="1"
                                       {{ old('requires_receipt', $designTask->requires_receipt) ? 'checked' : '' }}
                                       style="width:2.5rem;height:1.25rem;cursor:pointer;">
                            </div>
                        </div>
                    </div>

                    <!-- Live Total Preview -->
                    <div class="col-12">
                        <div class="p-3 rounded-3 border" style="background: #f8fafc;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">Subtotal (Qty × Rate)</div>
                                <div class="fw-bold" id="preview_subtotal">TZS {{ number_format($designTask->price, 2) }}</div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <div class="text-muted small">Delivery Cost</div>
                                <div class="fw-bold text-info" id="preview_delivery_cost">TZS {{ number_format($designTask->delivery_cost ?? 0, 2) }}</div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <div class="text-muted small">Delivery Discount</div>
                                <div class="fw-bold text-success" id="preview_delivery_discount">- TZS {{ number_format($designTask->delivery_discount ?? 0, 2) }}</div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1" id="vat_preview_row" style="{{ $designTask->requires_receipt ? '' : 'display:none!important;' }}">
                                <div class="text-muted small">VAT (18%)</div>
                                <div class="fw-bold text-warning" id="preview_vat">TZS 0.00</div>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fw-bold">Grand Total</div>
                                <div class="fw-bold text-primary fs-5" id="preview_total">TZS 0.00</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content/Instructions -->
                <div class="form-section-title">Requirements & Brief</div>
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <label class="form-label-premium">Public Description</label>
                        <textarea name="description" class="form-control-premium w-100 @error('description') is-invalid @enderror" 
                                  rows="4" placeholder="Update the general description of the task...">{{ old('description', $designTask->description) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label-premium">Internal Instructions for Designer</label>
                        <textarea name="designer_instructions" class="form-control-premium w-100 @error('designer_instructions') is-invalid @enderror" 
                                  rows="4" placeholder="Specific technical requirements or notes for the production team...">{{ old('designer_instructions', $designTask->designer_instructions) }}</textarea>
                    </div>
                </div>

                <!-- Reason for Editing Section -->
                <div class="reason-box">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <label class="form-label-premium m-0">Required Audit Reason <span class="text-danger">*</span></label>
                    </div>
                    <textarea name="edit_reason" class="form-control-premium w-100 @error('edit_reason') is-invalid @enderror" 
                              rows="3" placeholder="Explain why you are making these changes. This will be logged in the history for management review." required>{{ old('edit_reason') }}</textarea>
                    @error('edit_reason') <div class="invalid-feedback d-block mt-2">{{ $message }}</div> @enderror
                    <div class="mt-2 d-flex align-items-center gap-1 text-muted" style="font-size: 0.75rem;">
                        <i class="fas fa-info-circle"></i>
                        <span>Changes to Quantity or Rate will automatically recalculate the task balance.</span>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-top d-flex gap-3">
                    <button type="submit" class="btn btn-save-premium" id="saveButton">
                        <span class="btn-text text-uppercase">Update Task Records</span>
                        <i class="fas fa-save"></i>
                    </button>
                    <a href="{{ route('admin.design-tasks.show', $designTask) }}" class="btn btn-cancel-premium text-uppercase">
                        Cancel
                    </a>
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
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        const requiresReceipt = {{ $designTask->requires_receipt ? 'true' : 'false' }};

        function updatePreview() {
            const qty = parseFloat($('#edit_qty').val()) || 0;
            const rate = parseFloat($('#edit_rate').val()) || 0;
            const deliveryCost = parseFloat($('#edit_delivery_cost').val()) || 0;
            const deliveryDiscount = parseFloat($('#edit_delivery_discount').val()) || 0;

            const subtotal = qty * rate;
            const vat = requiresReceipt ? subtotal * 0.18 : 0;
            const netDelivery = deliveryCost - deliveryDiscount;
            const total = subtotal + vat + netDelivery;

            const fmt = n => 'TZS ' + n.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

            $('#preview_subtotal').text(fmt(subtotal));
            $('#preview_delivery_cost').text(fmt(deliveryCost));
            $('#preview_delivery_discount').text('- ' + fmt(deliveryDiscount));
            if (requiresReceipt) { $('#preview_vat').text(fmt(vat)); }
            $('#preview_total').text(fmt(total));
        }

        $('#edit_qty, #edit_rate, #edit_delivery_cost, #edit_delivery_discount').on('input', updatePreview);
        updatePreview(); // run on load

        $('#editTaskForm').on('submit', function() {
            const btn = $('#saveButton');
            const text = btn.find('.btn-text');
            const icon = btn.find('i');
            
            btn.prop('disabled', true);
            text.text('Processing...');
            icon.removeClass('fa-save').addClass('fa-spinner fa-spin');
        });
    });
</script>
@endpush
