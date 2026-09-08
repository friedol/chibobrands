@extends('layouts.admin')

@section('title', 'New Reconciliation Entry')

@push('styles')
<!-- Select2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
    .form-card { border: none; border-radius: 14px; box-shadow: 0 4px 20px rgba(0,0,0,.07); }
    .form-section-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #64748b; border-bottom: 1.5px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 14px; }
    .audit-notice { background: #fff5f5; border: 1px solid #fecaca; border-radius: 8px; padding: 12px 14px; font-size: 0.82rem; }
    .task-info-box { background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 10px 14px; font-size: 0.82rem; }
    
    /* Select2 Bootstrap 5 Integration overrides */
    .select2-container--bootstrap-5 .select2-selection {
        border: 1px solid #dee2e6;
        height: 38px;
        padding: 5px 8px;
        font-size: 0.9rem;
        border-radius: 6px;
    }
    .select2-container--bootstrap-5 .select2-selection__rendered {
        line-height: 26px !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            {{-- Breadcrumb --}}
            <nav class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.finance.dashboard') }}" class="text-muted text-decoration-none">Finance</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.finance.reconciliation.index') }}" class="text-muted text-decoration-none">Reconciliation</a></li>
                    <li class="breadcrumb-item active">New Entry</li>
                </ol>
            </nav>

            <div class="form-card card p-4">
                <div class="mb-3">
                    <h5 class="fw-bold mb-1" style="color:#1e293b;">
                        <i class="fas fa-balance-scale text-danger me-2"></i>New Reconciliation Entry
                    </h5>
                    <p class="text-muted small mb-0">Record a historical debt reconciliation. All entries are permanently logged in the Finance Audit Trail.</p>
                </div>

                {{-- Audit Notice --}}
                <div class="audit-notice mb-4">
                    <i class="fas fa-shield-alt text-danger me-2"></i>
                    <strong>Audit Notice:</strong> This action will be permanently logged with your name, date, time, and reason. Financial records cannot be deleted after reconciliation.
                </div>

                <form action="{{ route('admin.finance.reconciliation.store') }}" method="POST" id="reconForm">
                    @csrf

                    {{-- Task Info (if pre-filled) --}}
                    @if($task)
                    <div class="task-info-box mb-4">
                        <div class="fw-bold text-success mb-1"><i class="fas fa-tasks me-1"></i> Linked Design Task</div>
                        <div><strong>Code:</strong> {{ $task->task_code }}</div>
                        <div><strong>Customer:</strong> {{ $task->customer?->name }}</div>
                        <div class="d-flex gap-4 mt-1">
                            <span>Price: <strong>TZS {{ number_format($task->price) }}</strong></span>
                            <span>Paid: <strong class="text-success">TZS {{ number_format($task->amount_paid) }}</strong></span>
                            <span>Balance: <strong class="text-danger">TZS {{ number_format($task->balance) }}</strong></span>
                        </div>
                    </div>
                    @endif

                    {{-- Section 1: Basic Info --}}
                    <div class="form-section-title">1. Transaction Details</div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select select2" id="customerSelect" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}"
                                    {{ (old('customer_id', $prefill['customer_id'] ?? '') == $c->id) ? 'selected' : '' }}>
                                    {{ $c->name }} @if($c->phone) — {{ $c->phone }}@endif
                                </option>
                                @endforeach
                            </select>
                            @error('customer_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Transaction Date (Historical) <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" class="form-control"
                                   value="{{ old('transaction_date', now()->toDateString()) }}"
                                   max="{{ now()->toDateString() }}">
                            <div class="form-text">Use the original date of the transaction, not today.</div>
                            @error('transaction_date')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Reconciliation Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select">
                                @foreach(['historical_entry'=>'Historical Entry','full_payment'=>'Full Payment','partial_payment'=>'Partial Payment','debt_write_off'=>'Debt Write-off','credit_note'=>'Credit Note','adjustment'=>'Adjustment'] as $val => $label)
                                <option value="{{ $val }}" {{ old('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Amount (TZS) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01"
                                   value="{{ old('amount', $prefill['amount'] ?? '') }}" placeholder="0.00">
                            @error('amount')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Reference / Cheque / Receipt No.</label>
                            <input type="text" name="reference" class="form-control" value="{{ old('reference') }}" placeholder="e.g. CHQ-0012345">
                        </div>
                    </div>

                    {{-- Section 2: Links --}}
                    <div class="form-section-title">2. Link to Existing Record (Optional)</div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Linked Design Task ID</label>
                            <input type="number" name="design_task_id" class="form-control"
                                   value="{{ old('design_task_id', $prefill['design_task_id'] ?? '') }}"
                                   placeholder="Task ID (optional)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Linked Payment ID</label>
                            <input type="number" name="payment_id" class="form-control"
                                   value="{{ old('payment_id', $prefill['payment_id'] ?? '') }}"
                                   placeholder="Payment ID (optional)">
                            <div class="form-text">If linked, the payment's debt_status will be set to <strong>Reconciled</strong>.</div>
                        </div>
                    </div>

                    {{-- Section 3: Notes & Reason --}}
                    <div class="form-section-title">3. Reason & Notes</div>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Reason for Reconciliation <span class="text-danger">*</span> <small class="text-muted">(minimum 10 characters)</small></label>
                            <textarea name="reason" class="form-control" rows="3" required minlength="10"
                                      placeholder="Explain why this reconciliation is being recorded (e.g. customer paid in cash on 15 Jan 2024 but was not captured in the system)...">{{ old('reason') }}</textarea>
                            @error('reason')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Additional Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any other notes...">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger px-4 fw-bold">
                            <i class="fas fa-save me-1"></i> Save Reconciliation
                        </button>
                        <a href="{{ route('admin.finance.reconciliation.index') }}" class="btn btn-light px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- jQuery & Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select Customer',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush

