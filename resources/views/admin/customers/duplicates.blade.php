@extends('layouts.admin')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-users-cog text-warning me-2"></i>Duplicate Customer Records</h4>

        </div>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> Back to Customers
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($duplicateGroups->isEmpty())
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="fas fa-check-double text-success fa-3x mb-3"></i>
                <h5 class="fw-bold">No Duplicate Customers Found</h5>
                <p class="text-muted small">All phone numbers in your customer database are unique and properly normalized.</p>
            </div>
        </div>
    @else
        @foreach($duplicateGroups as $group)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="badge bg-warning text-dark px-3 py-2 me-2 font-monospace" style="font-size:13px;">
                            <i class="fas fa-phone-alt me-1"></i> {{ $group['phone'] }}
                        </span>
                        <span class="text-muted small fw-bold">{{ $group['total'] }} Matching Profiles Found</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:13px;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Customer ID</th>
                                    <th>Full Name</th>
                                    <th>Registration Date</th>
                                    <th>Orders Count</th>
                                    <th>Total Spent</th>
                                    <th>Registered By</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($group['customers'] as $cust)
                                    <tr>
                                        <td class="fw-bold">#{{ $cust->id }}</td>
                                        <td>
                                            <a href="{{ route('admin.customers.show', $cust->id) }}" class="fw-bold text-decoration-none text-dark" target="_blank">
                                                {{ $cust->name }}
                                            </a>
                                            @if($cust->company_name)
                                                <small class="text-muted d-block">{{ $cust->company_name }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $cust->created_at ? $cust->created_at->format('d M Y') : 'N/A' }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $cust->total_orders ?? 0 }} orders</span></td>
                                        <td class="fw-bold text-success">TZS {{ number_format($cust->total_spent ?? 0) }}</td>
                                        <td>{{ $cust->registeredBy->name ?? 'System' }}</td>
                                        <td class="text-end">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-merge-trigger"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#mergeModal"
                                                    data-group-phone="{{ $group['phone'] }}"
                                                    data-cust-id="{{ $cust->id }}"
                                                    data-cust-name="{{ $cust->name }}"
                                                    data-group-json="{{ json_encode($group['customers']) }}">
                                                <i class="fas fa-object-group me-1"></i> Merge Group Into This
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

<!-- Merge Customer Confirmation Modal -->
<div class="modal fade" id="mergeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('admin.customers.merge') }}">
                @csrf
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title fw-bold text-dark"><i class="fas fa-object-group text-primary me-2"></i>Merge Duplicate Customers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        You are consolidating duplicate records for phone number <span id="modal-phone-display" class="fw-bold text-dark font-monospace"></span>.
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">PRIMARY TARGET PROFILE (KEEP THIS)</label>
                        <select name="target_customer_id" id="modal-target-select" class="form-select fw-bold" required></select>
                        <small class="form-text text-muted">All orders, payments, follow-ups, and debts will be transferred to this profile.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">DUPLICATE PROFILE TO MERGE & REMOVE</label>
                        <select name="source_customer_id" id="modal-source-select" class="form-select" required></select>
                        <small class="form-text text-danger">This profile will be transferred and then archived.</small>
                    </div>

                    <div class="alert alert-warning py-2 mb-0 d-flex align-items-center gap-2">
                        <i class="fas fa-shield-alt text-warning fa-lg"></i>
                        <div class="small">This merge action is permanent and will be logged in the Security Audit Log.</div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">CONFIRM & MERGE</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mergeTriggers = document.querySelectorAll('.btn-merge-trigger');
    const phoneDisplay = document.getElementById('modal-phone-display');
    const targetSelect = document.getElementById('modal-target-select');
    const sourceSelect = document.getElementById('modal-source-select');

    mergeTriggers.forEach(btn => {
        btn.addEventListener('click', function() {
            const phone = this.dataset.groupPhone;
            const targetId = parseInt(this.dataset.custId);
            const groupData = JSON.parse(this.dataset.groupJson);

            phoneDisplay.textContent = phone;
            targetSelect.innerHTML = '';
            sourceSelect.innerHTML = '';

            groupData.forEach(c => {
                const isPrimary = (c.id === targetId);
                const optText = `#${c.id} - ${c.name} (${c.total_orders || 0} orders, TZS ${(c.total_spent || 0).toLocaleString()})`;
                
                const tOpt = new Option(optText, c.id, isPrimary, isPrimary);
                targetSelect.add(tOpt);

                if (!isPrimary) {
                    const sOpt = new Option(optText, c.id, false, false);
                    sourceSelect.add(sOpt);
                }
            });
        });
    });

    targetSelect.addEventListener('change', function() {
        const selectedTargetId = parseInt(this.value);
        // Refresh source select options based on target choice
        const options = Array.from(targetSelect.options);
        sourceSelect.innerHTML = '';
        options.forEach(opt => {
            if (parseInt(opt.value) !== selectedTargetId) {
                sourceSelect.add(new Option(opt.text, opt.value));
            }
        });
    });
});
</script>
@endpush
@endsection
