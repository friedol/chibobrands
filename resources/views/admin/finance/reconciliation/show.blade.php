@extends('layouts.admin')

@section('title', 'Reconciliation Details')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <nav class="mb-3"><ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('admin.finance.reconciliation.index') }}" class="text-muted text-decoration-none">Reconciliation</a></li>
                <li class="breadcrumb-item active">Entry #{{ $reconciliation->id }}</li>
            </ol></nav>

            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-balance-scale me-2"></i>Reconciliation Entry #{{ $reconciliation->id }}</h6>
                    <span class="badge bg-{{ $reconciliation->status_color }}">{{ ucfirst(str_replace('_',' ',$reconciliation->status)) }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-muted small fw-semibold mb-1">Customer</div>
                            <div class="fw-bold">{{ $reconciliation->customer?->name ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small fw-semibold mb-1">Transaction Date</div>
                            <div class="fw-bold">{{ $reconciliation->transaction_date?->format('d M Y') }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small fw-semibold mb-1">Reconciliation Date</div>
                            <div>{{ $reconciliation->reconciliation_date?->format('d M Y') }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small fw-semibold mb-1">Type</div>
                            <span class="badge bg-{{ $reconciliation->type_color }}">{{ $reconciliation->type_label }}</span>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small fw-semibold mb-1">Amount</div>
                            <div class="fw-bold text-danger fs-5">TZS {{ number_format($reconciliation->amount) }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small fw-semibold mb-1">Reference</div>
                            <div>{{ $reconciliation->reference ?: '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small fw-semibold mb-1">Reason</div>
                            <div>{{ $reconciliation->reason }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small fw-semibold mb-1">Notes</div>
                            <div class="text-muted">{{ $reconciliation->notes ?: '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small fw-semibold mb-1">Recorded By</div>
                            <div>{{ $reconciliation->reconciledBy?->name ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Audit Trail for this entry --}}
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-history text-danger me-2"></i>Audit Trail</h6>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0" style="font-size:.82rem;">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th>Date / Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Old Value</th>
                                <th>New Value</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($auditTrail as $entry)
                            <tr>
                                <td>{{ $entry->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $entry->user?->name ?? '—' }}</td>
                                <td><span class="badge bg-{{ $entry->action_color }}">{{ $entry->action_label }}</span></td>
                                <td><code class="text-muted small">{{ $entry->old_value ? json_encode($entry->old_value) : '—' }}</code></td>
                                <td><code class="text-muted small">{{ $entry->new_value ? json_encode($entry->new_value) : '—' }}</code></td>
                                <td>{{ Str::limit($entry->reason, 80) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No audit entries found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
