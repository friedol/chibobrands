@extends('layouts.admin')

@section('title', 'Finance Verification Dashboard')

@push('styles')
<style>
    :root { --red: #dc2626; --dark: #1e293b; }

    .vd-header { background: #f8fafc; border: 1px solid #e2e8f0; color: #1e293b; border-radius: 14px; padding: 24px 28px; margin-bottom: 28px; }
    .vd-header h4 { font-weight: 800; font-size: 1.35rem; margin: 0; color: #1e293b; }
    .vd-header p { margin: 6px 0 0; color: #64748b; font-size: 0.85rem; }

    .health-ring-wrap { display: flex; align-items: center; gap: 20px; }
    .health-ring { width: 90px; height: 90px; border-radius: 50%; border: 6px solid #334155; display: flex; align-items: center; justify-content: center; flex-direction: column; }
    .health-score-num { font-size: 1.4rem; font-weight: 800; line-height: 1; color: #1e293b; }
    .health-grade { font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }

    .stat-card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.07); transition: transform .15s; }
    .stat-card:hover { transform: translateY(-2px); }
    .stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }

    .section-card { border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.04); overflow: hidden; margin-bottom: 24px; background: #ffffff; }
    .section-card .card-header { background: #ffffff; color: #1e293b; border-bottom: 1px solid #e2e8f0; padding: 12px 18px; font-weight: 700; font-size: 0.82rem; text-transform: uppercase; letter-spacing: .4px; display: flex; align-items: center; justify-content: space-between; }
    .section-card table th { font-size: 0.72rem; text-transform: uppercase; letter-spacing: .3px; color: #64748b; font-weight: 700; padding: 10px 14px; background: #f8fafc; border: none; }
    .section-card table td { font-size: 0.82rem; padding: 9px 14px; border-color: #f1f5f9; vertical-align: middle; }

    .badge-status { font-size: 0.68rem; font-weight: 700; padding: 3px 8px; border-radius: 20px; }

    .fix-btn { font-size: 0.72rem; }
    .no-data { text-align: center; padding: 30px 0; color: #94a3b8; }
    .no-data i { font-size: 2rem; display: block; margin-bottom: 6px; opacity: .25; }

    .progress-bar-red { background-color: #dc2626 !important; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- ── HEADER ── --}}
    <div class="vd-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4><i class="fas fa-shield-alt me-2" style="color:#f87171;"></i>Finance Verification Dashboard</h4>
                <p>Outstanding balances · Reconciliation status · Debt mismatches · Duplicate payments · Missing transactions</p>
            </div>
            <div class="health-ring-wrap">
                <div class="health-ring" style="border-color: {{ match($healthScore['grade']) {
                    'Excellent' => '#22c55e', 'Good' => '#3b82f6', 'Fair' => '#f59e0b',
                    'Poor' => '#f97316', default => '#dc2626'
                } }};">
                    <div class="health-score-num">{{ $healthScore['score'] }}</div>
                    <div class="health-grade" style="color:#94a3b8;">/ 100</div>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:.95rem;">{{ $healthScore['grade'] }}</div>
                    <div style="font-size:.75rem;color:#94a3b8;">Financial Health</div>
                    <div class="d-flex gap-2 mt-1 flex-wrap" style="font-size:.68rem;color:#64748b;">
                        <span>Debt: {{ $healthScore['debt_score'] }}/40</span>
                        <span>Tasks: {{ $healthScore['task_score'] }}/30</span>
                        <span>Dupes: {{ $healthScore['dup_score'] }}/20</span>
                        <span>Missing: {{ $healthScore['missing_score'] }}/10</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STAT CARDS ── --}}
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="stat-card card p-3 text-center">
                <div class="stat-icon bg-danger bg-opacity-10 mx-auto mb-2"><i class="fas fa-coins text-danger"></i></div>
                <div class="fw-bold" style="font-size:.8rem;color:#64748b;">Outstanding Balance</div>
                <div class="fw-bold text-danger" style="font-size:.95rem;">TZS {{ number_format($stats['total_outstanding']) }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card card p-3 text-center">
                <div class="stat-icon bg-warning bg-opacity-10 mx-auto mb-2"><i class="fas fa-users text-warning"></i></div>
                <div class="fw-bold" style="font-size:.8rem;color:#64748b;">Customers with Debt</div>
                <div class="fw-bold" style="font-size:1.4rem;">{{ number_format($stats['outstanding_customers']) }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card card p-3 text-center">
                <div class="stat-icon bg-primary bg-opacity-10 mx-auto mb-2"><i class="fas fa-copy text-primary"></i></div>
                <div class="fw-bold" style="font-size:.8rem;color:#64748b;">Duplicate Payments</div>
                <div class="fw-bold {{ $stats['duplicate_count'] > 0 ? 'text-danger' : 'text-success' }}" style="font-size:1.4rem;">{{ $stats['duplicate_count'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card card p-3 text-center">
                <div class="stat-icon bg-info bg-opacity-10 mx-auto mb-2"><i class="fas fa-search-minus text-info"></i></div>
                <div class="fw-bold" style="font-size:.8rem;color:#64748b;">Missing Transactions</div>
                <div class="fw-bold {{ $stats['missing_count'] > 0 ? 'text-danger' : 'text-success' }}" style="font-size:1.4rem;">{{ $stats['missing_count'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card card p-3 text-center">
                <div class="stat-icon bg-danger bg-opacity-10 mx-auto mb-2"><i class="fas fa-exclamation-triangle text-danger"></i></div>
                <div class="fw-bold" style="font-size:.8rem;color:#64748b;">Status Mismatches</div>
                <div class="fw-bold {{ $stats['mismatch_count'] > 0 ? 'text-danger' : 'text-success' }}" style="font-size:1.4rem;">{{ $stats['mismatch_count'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card card p-3 text-center">
                <div class="stat-icon bg-success bg-opacity-10 mx-auto mb-2"><i class="fas fa-check-circle text-success"></i></div>
                <div class="fw-bold" style="font-size:.8rem;color:#64748b;">Reconciled</div>
                <div class="fw-bold text-success" style="font-size:1.4rem;">{{ $stats['reconciliation_count'] }}</div>
            </div>
        </div>
    </div>

    {{-- ── SECTION 1: Outstanding Balances ── --}}
    <div class="section-card card">
        <div class="card-header">
            <span><i class="fas fa-coins me-2"></i>1. Outstanding Customer Balances (Top 20 by Debt)</span>
            <a href="{{ route('admin.finance.reconciliation.create') }}" class="btn btn-danger btn-sm py-1 px-2" style="font-size:.72rem;">
                <i class="fas fa-plus me-1"></i> Reconcile
            </a>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>#</th><th>Customer</th><th>Phone</th><th>Tasks</th><th>Total Billed</th><th>Total Paid</th><th>Outstanding</th><th></th></tr></thead>
                <tbody>
                    @forelse($topDebtors as $i => $d)
                    <tr>
                        <td class="text-muted">{{ $i + 1 }}</td>
                        <td class="fw-bold">{{ $d->customer_name }}</td>
                        <td class="font-monospace text-muted">{{ $d->phone ?: '—' }}</td>
                        <td>{{ $d->task_count }}</td>
                        <td>TZS {{ number_format($d->total_billed) }}</td>
                        <td class="text-success fw-bold">TZS {{ number_format($d->total_paid) }}</td>
                        <td class="text-danger fw-bold">TZS {{ number_format($d->total_outstanding) }}</td>
                        <td>
                            <a href="{{ route('admin.finance.reconciliation.create', ['customer_id' => $d->customer_id]) }}" class="btn btn-outline-danger btn-sm py-1 px-2 fix-btn">Reconcile</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="no-data"><i class="fas fa-check-circle text-success"></i>All customer balances are cleared!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4">
        {{-- ── SECTION 2: Debt Status Mismatches ── --}}
        <div class="col-lg-6">
            <div class="section-card card">
                <div class="card-header">
                    <span><i class="fas fa-sync-alt me-2"></i>2. Debt Status Mismatches</span>
                    @if($debtMismatches->count() > 0)
                    <button class="btn btn-warning btn-sm py-1 px-2 fix-btn" id="fixMismatchesBtn">
                        <i class="fas fa-magic me-1"></i> Auto-Fix All
                    </button>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Customer</th><th>Task</th><th>Task Balance</th><th>Payment Status</th></tr></thead>
                        <tbody>
                            @forelse($debtMismatches as $m)
                            <tr>
                                <td>{{ $m->customer_name }}</td>
                                <td class="font-monospace text-muted">{{ $m->task_code }}</td>
                                <td class="{{ $m->task_balance <= 0 ? 'text-success' : 'text-danger' }} fw-bold">TZS {{ number_format($m->task_balance) }}</td>
                                <td><span class="badge-status bg-danger text-white">{{ ucfirst($m->debt_status) }}</span>
                                    <small class="text-muted ms-1">→ should be Paid</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="no-data"><i class="fas fa-thumbs-up text-success"></i>No mismatches found!</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ── SECTION 3: Duplicate Payments ── --}}
        <div class="col-lg-6">
            <div class="section-card card">
                <div class="card-header"><span><i class="fas fa-copy me-2"></i>3. Potential Duplicate Payments</span></div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Customer</th><th>Amount</th><th>Date</th><th>Count</th><th>Payment IDs</th></tr></thead>
                        <tbody>
                            @forelse($duplicatePayments as $d)
                            <tr>
                                <td class="fw-bold">{{ $d->customer_name }}</td>
                                <td class="text-danger fw-bold">TZS {{ number_format($d->amount) }}</td>
                                <td>{{ $d->payment_date }}</td>
                                <td><span class="badge bg-danger">{{ $d->count }}x</span></td>
                                <td class="font-monospace text-muted small">{{ $d->payment_ids }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="no-data"><i class="fas fa-check text-success"></i>No duplicate payments detected.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SECTION 4: Missing Transactions ── --}}
    <div class="section-card card">
        <div class="card-header"><span><i class="fas fa-search-minus me-2"></i>4. Missing Transaction Records</span>
            <span class="small opacity-75">Tasks with amount_paid > 0 but no matching payment row</span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Task Code</th><th>Customer</th><th>Price</th><th>Amount Paid (task record)</th><th>Balance</th><th>Payment Rows</th><th></th></tr></thead>
                <tbody>
                    @forelse($missingTx as $t)
                    <tr>
                        <td class="font-monospace fw-bold">{{ $t->task_code }}</td>
                        <td>{{ $t->customer_name }}</td>
                        <td>TZS {{ number_format($t->price) }}</td>
                        <td class="text-warning fw-bold">TZS {{ number_format($t->amount_paid) }}</td>
                        <td class="text-danger fw-bold">TZS {{ number_format($t->balance) }}</td>
                        <td><span class="badge bg-danger">0 payments</span></td>
                        <td>
                            <a href="{{ route('admin.finance.reconciliation.create', ['design_task_id' => $t->id, 'amount' => $t->amount_paid]) }}"
                               class="btn btn-outline-danger btn-sm py-1 px-2 fix-btn">Add Record</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="no-data"><i class="fas fa-check text-success"></i>All tasks with payments have matching payment records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── SECTION 5: Reconciliation Summary ── --}}
    <div class="section-card card">
        <div class="card-header">
            <span><i class="fas fa-chart-pie me-2"></i>5. Reconciliation Summary</span>
            <a href="{{ route('admin.finance.reconciliation.index') }}" class="btn btn-outline-light btn-sm py-1 px-2 fix-btn">View All</a>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-muted small fw-semibold">Total Reconciliations</div>
                    <div class="fw-bold fs-4">{{ $reconSummary['total_reconciliations'] }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small fw-semibold">Total Amount</div>
                    <div class="fw-bold fs-5 text-danger">TZS {{ number_format($reconSummary['total_amount']) }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small fw-semibold">Pending Review</div>
                    <div class="fw-bold fs-5 {{ $reconSummary['pending_review'] > 0 ? 'text-warning' : 'text-success' }}">{{ $reconSummary['pending_review'] }}</div>
                </div>
                <div class="col-md-9">
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @foreach($reconSummary['by_type'] as $type => $bt)
                        <div class="bg-light rounded px-3 py-2 text-center" style="min-width:120px;">
                            <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;font-weight:700;">{{ ucwords(str_replace('_',' ',$type)) }}</div>
                            <div class="fw-bold">{{ $bt->count }}</div>
                            <div class="text-primary small">TZS {{ number_format($bt->total) }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="d-flex gap-2 flex-wrap mt-2">
        <a href="{{ route('admin.finance.reconciliation.index') }}" class="btn btn-dark btn-sm"><i class="fas fa-balance-scale me-1"></i> All Reconciliations</a>
        <a href="{{ route('admin.finance.finance-audit-trail.index') }}" class="btn btn-outline-dark btn-sm"><i class="fas fa-history me-1"></i> Finance Audit Trail</a>
        <a href="{{ route('admin.finance.zoho-comparison') }}" class="btn btn-outline-dark btn-sm"><i class="fas fa-code-branch me-1"></i> Zoho Comparison</a>
        <a href="{{ route('admin.finance.pending-payments') }}" class="btn btn-outline-danger btn-sm"><i class="fas fa-exclamation-circle me-1"></i> Pending Payments</a>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const fixBtn = document.getElementById('fixMismatchesBtn');
    if (fixBtn) {
        fixBtn.addEventListener('click', function() {
            Swal.fire({
                title: 'Auto-Fix Mismatches?',
                text: 'This will automatically mark pending debt entries as PAID where the design task balance is 0. This modification will be logged in the Finance Audit Trail.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, fix them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fixBtn.disabled = true;
                    fixBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Fixing…';

                    fetch('{{ route('admin.finance.reconciliation.fix-mismatches') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#1e293b'
                        }).then(() => {
                            location.reload();
                        });
                    })
                    .catch(() => {
                        Swal.fire({
                            title: 'Error',
                            text: 'An unexpected error occurred. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#dc2626'
                        });
                        fixBtn.disabled = false;
                        fixBtn.innerHTML = '<i class="fas fa-magic me-1"></i> Auto-Fix All';
                    });
                }
            });
        });
    }
</script>
@endpush
