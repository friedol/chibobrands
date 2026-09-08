@extends('layouts.admin')

@section('title', 'Finance Reconciliation')

@push('styles')
    <style>
        .rec-stat-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
        }

        .type-badge {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .table-rec th {
            background: #f8fafc;
            color: #64748b;
            font-size: 0.72rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: .4px;
            border: none;
            padding: 10px 12px;
        }

        .table-rec td {
            vertical-align: middle;
            font-size: 0.82rem;
            padding: 10px 12px;
            border-color: #f1f5f9;
        }

        .filter-panel {
            background: #f8fafc;
            border-radius: 10px;
            padding: 16px;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1" style="color:#1e293b;">
                    <i class="fas fa-balance-scale me-2" style="color:#dc2626;"></i>Finance Reconciliation
                </h4>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.finance.verification-dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-shield-check me-1"></i> Verification
                </a>
                <a href="{{ route('admin.finance.reconciliation.create') }}" class="btn btn-danger btn-sm px-3">
                    <i class="fas fa-plus me-1"></i> New Reconciliation
                </a>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="rec-stat-card card p-3">
                    <div class="text-muted small fw-600 mb-1">Total Reconciliations</div>
                    <div class="fw-bold fs-4">{{ number_format($summary['total']) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="rec-stat-card card p-3">
                    <div class="text-muted small fw-600 mb-1">Total Amount Reconciled</div>
                    <div class="fw-bold fs-5 text-danger">TZS {{ number_format($summary['total_amount']) }}</div>
                </div>
            </div>
            @foreach($summary['by_type'] as $bt)
                <div class="col-md-3">
                    <div class="rec-stat-card card p-3">
                        <div class="text-muted small fw-600 mb-1">{{ ucwords(str_replace('_', ' ', $bt->type)) }}</div>
                        <div class="fw-bold fs-5">{{ number_format($bt->count) }} <small
                                class="text-muted fs-6 fw-normal">entries</small></div>
                        <div class="text-primary small">TZS {{ number_format($bt->total) }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Filter --}}
        <div class="filter-panel">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold small mb-1">Customer</label>
                    <select name="customer_id" class="form-select form-select-sm">
                        <option value="">All Customers</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small mb-1">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach(['full_payment', 'partial_payment', 'debt_write_off', 'credit_note', 'adjustment', 'historical_entry'] as $t)
                            <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $t)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small mb-1">Date From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                        value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small mb-1">Date To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark btn-sm px-3">Filter</button>
                    <a href="{{ route('admin.finance.reconciliation.index') }}" class="btn btn-light btn-sm">Reset</a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show small py-2"><i
                    class="fas fa-check-circle me-1"></i>{{ session('success') }}<button type="button" class="btn-close btn-sm"
                    data-bs-dismiss="alert"></button></div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 table-rec">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Transaction Date</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Amount (TZS)</th>
                            <th>Reference</th>
                            <th>Recorded By</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reconciliations as $rec)
                            <tr>
                                <td class="text-muted">{{ $loop->iteration }}</td>
                                <td><strong>{{ $rec->transaction_date?->format('d M Y') }}</strong></td>
                                <td>{{ $rec->customer?->name ?? '—' }}</td>
                                <td><span class="type-badge bg-{{ $rec->type_color }} text-white">{{ $rec->type_label }}</span>
                                </td>
                                <td class="fw-bold text-danger">{{ number_format($rec->amount) }}</td>
                                <td class="text-muted">{{ $rec->reference ?: '—' }}</td>
                                <td>{{ $rec->reconciledBy?->name ?? '—' }}</td>
                                <td style="max-width:200px;" class="text-muted">{{ Str::limit($rec->reason, 50) }}</td>
                                <td><span
                                        class="badge bg-{{ $rec->status_color }}">{{ ucfirst(str_replace('_', ' ', $rec->status)) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.finance.reconciliation.show', $rec->id) }}"
                                        class="btn btn-sm btn-outline-secondary py-1 px-2" title="View Audit Trail">
                                        <i class="fas fa-history"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5"><i
                                        class="fas fa-inbox fa-2x d-block mb-2 opacity-25"></i>No reconciliation records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($reconciliations->hasPages())
                <div class="card-footer bg-white border-0 py-3">{{ $reconciliations->links() }}</div>
            @endif
        </div>
    </div>
@endsection