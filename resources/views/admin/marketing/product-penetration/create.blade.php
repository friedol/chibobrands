@extends('layouts.admin')

@section('title', 'New Product Penetration')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 fw-bold">New Product Penetration Activity</h2>
        <a href="{{ route('admin.marketing.product-penetration.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-4">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-body p-4">
            <form action="{{ route('admin.marketing.product-penetration.store') }}" method="POST">
                @csrf
                <div class="row g-4">

                    {{-- ── Item Type picker ── --}}
                    <div class="col-12">
                        <label class="form-label fw-bold">Item Type <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="item_type" id="type_product"
                                    value="product" {{ old('item_type', 'product') === 'product' ? 'checked' : '' }}
                                    onchange="switchItemType(this.value)">
                                <label class="form-check-label fw-semibold" for="type_product">
                                    <i class="fas fa-box me-1 text-warning"></i> Product
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="item_type" id="type_task"
                                    value="task_type" {{ old('item_type') === 'task_type' ? 'checked' : '' }}
                                    onchange="switchItemType(this.value)">
                                <label class="form-check-label fw-semibold" for="type_task">
                                    <i class="fas fa-paint-brush me-1 text-purple" style="color:#8b5cf6;"></i> Design Task Type
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- ── Product dropdown ── --}}
                    <div class="col-md-6" id="product-field">
                        <label for="product_id" class="form-label fw-bold">
                            <i class="fas fa-box me-1 text-warning"></i> Select Product <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="product_id" name="product_id">
                            <option value="">— Choose a product —</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                    @if($product->category) ({{ $product->category }})@endif
                                    @if(!$product->is_active) [Inactive]@endif
                                </option>
                            @endforeach
                        </select>
                        @if($products->isEmpty())
                            <div class="form-text text-danger"><i class="fas fa-exclamation-circle me-1"></i>No products found in the system.</div>
                        @endif
                    </div>

                    {{-- ── Design Task Type dropdown ── --}}
                    <div class="col-md-6 d-none" id="task-type-field">
                        <label for="task_type_id" class="form-label fw-bold">
                            <i class="fas fa-paint-brush me-1" style="color:#8b5cf6;"></i> Select Design Task Type <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="task_type_id" name="task_type_id">
                            <option value="">— Choose a task type —</option>
                            @foreach($taskTypes as $type)
                                <option value="{{ $type->id }}" {{ old('task_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($taskTypes->isEmpty())
                            <div class="form-text text-danger"><i class="fas fa-exclamation-circle me-1"></i>No design task types found.</div>
                        @endif
                    </div>

                    {{-- ── Spacer col for second column when task type shown ── --}}
                    <div class="col-md-6" id="task-type-spacer" style="display:none;"></div>

                    <div class="col-md-6">
                        <label for="target_segment" class="form-label fw-bold">Target Market Segment</label>
                        <input type="text" class="form-control" id="target_segment" name="target_segment"
                            value="{{ old('target_segment') }}" placeholder="e.g. Students, Corporates...">
                    </div>

                    <div class="col-md-6">
                        <label for="current_penetration" class="form-label fw-bold">Current Penetration (%)</label>
                        <input type="number" step="0.01" min="0" max="100" class="form-control"
                            id="current_penetration" name="current_penetration"
                            value="{{ old('current_penetration', '0') }}" placeholder="0.00">
                    </div>

                    <div class="col-md-6">
                        <label for="target_penetration" class="form-label fw-bold">Target Penetration (%)</label>
                        <input type="number" step="0.01" min="0" max="100" class="form-control"
                            id="target_penetration" name="target_penetration"
                            value="{{ old('target_penetration', '0') }}" placeholder="0.00">
                    </div>

                    <div class="col-12">
                        <label for="strategy_notes" class="form-label fw-bold">Strategy Notes</label>
                        <textarea class="form-control" id="strategy_notes" name="strategy_notes"
                            rows="4" placeholder="Detail the strategy to achieve this penetration...">{{ old('strategy_notes') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label fw-bold">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="planning"  {{ old('status', 'planning') === 'planning'   ? 'selected' : '' }}>Planning</option>
                            <option value="active"    {{ old('status') === 'active'                 ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ old('status') === 'completed'              ? 'selected' : '' }}>Completed</option>
                            <option value="on_hold"   {{ old('status') === 'on_hold'                ? 'selected' : '' }}>On Hold</option>
                        </select>
                    </div>

                    <div class="col-12 mt-2 text-end">
                        <a href="{{ route('admin.marketing.product-penetration.index') }}" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 8px;">
                            <i class="fas fa-save me-2"></i> Save Activity
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function switchItemType(type) {
        const productField   = document.getElementById('product-field');
        const taskTypeField  = document.getElementById('task-type-field');
        const productSelect  = document.getElementById('product_id');
        const taskTypeSelect = document.getElementById('task_type_id');

        if (type === 'product') {
            productField.classList.remove('d-none');
            taskTypeField.classList.add('d-none');
            productSelect.required  = true;
            taskTypeSelect.required = false;
            taskTypeSelect.value    = '';
        } else {
            productField.classList.add('d-none');
            taskTypeField.classList.remove('d-none');
            productSelect.required  = false;
            taskTypeSelect.required = true;
            productSelect.value     = '';
        }
    }

    // Apply initial state on load
    document.addEventListener('DOMContentLoaded', () => {
        const checked = document.querySelector('input[name="item_type"]:checked');
        if (checked) switchItemType(checked.value);
    });
</script>
@endpush
@endsection
