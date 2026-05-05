@extends('layouts.admin')

@section('title', 'Financial Audit & Control')

@section('content')
<div class="row mb-4 align-items-center no-print">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1">Financial Audit & Control</h4>
        <p class="text-muted small mb-0">Anomaly detection and balance reconciliation</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
            <a href="{{ route('admin.finance.dashboard') }}" data-no-global-handler data-no-preloader class="btn btn-outline-primary btn-sm">
                <i class="fas fa-chart-line me-1"></i> Finance Hub
            </a>
            <button type="button" onclick="printDirect('{{ route('admin.finance.audit.print', request()->all()) }}')" class="btn btn-dark btn-sm shadow-sm">
                <i class="fas fa-print me-1"></i> PRINT AUDIT
            </button>
        </div>
    </div>
</div>

<div class="container-fluid py-0 px-0">
    <!-- Modern Unified Filter Bar -->
    <div class="filter-bar shadow-sm glass-morphism no-print mb-4">
        <form action="{{ route('admin.finance.audit') }}" method="GET" class="d-flex flex-wrap align-items-end gap-3 p-3">
            <div class="filter-group">
                <label class="filter-label">Quick Periods</label>
                <div class="btn-group preset-group shadow-sm">
                    <a href="{{ route('admin.finance.audit', ['period' => 'today']) }}" data-no-global-handler data-no-preloader
                       class="btn btn-preset {{ $period == 'today' ? 'active' : '' }}">Today</a>
                    <a href="{{ route('admin.finance.audit', ['period' => 'week']) }}" data-no-global-handler data-no-preloader
                       class="btn btn-preset {{ $period == 'week' ? 'active' : '' }}">Weekly</a>
                    <a href="{{ route('admin.finance.audit', ['period' => 'month']) }}" data-no-global-handler data-no-preloader
                       class="btn btn-preset {{ $period == 'month' ? 'active' : '' }}">Monthly</a>
                    <a href="{{ route('admin.finance.audit', ['period' => 'year']) }}" data-no-global-handler data-no-preloader
                       class="btn btn-preset {{ $period == 'year' ? 'active' : '' }}">Yearly</a>
                </div>
            </div>

            <div class="filter-divider d-none d-lg-block"></div>

            <div class="filter-group flex-grow-1">
                <label class="filter-label">Audit Scope Period</label>
                <div class="d-flex gap-2">
                    <div class="input-modern shadow-sm">
                        <i class="fas fa-calendar-alt icon"></i>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="border-0 bg-transparent">
                    </div>
                    <div class="input-modern shadow-sm">
                        <i class="fas fa-calendar-check icon"></i>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="border-0 bg-transparent">
                    </div>
                </div>
            </div>

            <div class="filter-actions d-flex gap-2">
                <button type="submit" data-no-global-handler data-no-preloader class="btn btn-apply shadow-sm">
                    <i class="fas fa-sync-alt me-1"></i> Run Audit
                </button>
            </div>
        </form>
    </div>

    @php
        $totalIssues = $unbalancedOrders->count() + $unbalancedTasks->count() + $missingOrderPayments->count() + $mismatchedOrders->count();
    @endphp

    @if($totalIssues > 0)
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4 py-3" style="border-radius: 12px; border-left: 5px solid #fbbf24 !important; background: #fffbeb;">
            <i class="fas fa-exclamation-triangle fs-4 me-3 text-warning"></i>
            <div>
                <h6 class="mb-0 fw-bold text-dark">Anomaly Detection System: {{ $totalIssues }} potential issues identified for this period.</h6>
                <p class="small mb-0 opacity-75 text-muted">Please review the flagged transactions below to maintain account accuracy.</p>
            </div>
        </div>
    @else
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4 py-3" style="border-radius: 12px; border-left: 5px solid #10b981 !important; background: #f0fdf4;">
            <i class="fas fa-check-circle fs-4 me-3 text-success"></i>
            <div>
                <h6 class="mb-0 fw-bold text-dark">System Balanced! No major anomalies detected in selected scope.</h6>
                <p class="small mb-0 opacity-75 text-muted">All orders and tasks match their payment records for the period of {{ \Carbon\Carbon::parse($dateFrom)->format('d M') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d M, Y') }}.</p>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- 1. Missing Payments -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden" style="border-radius: 16px;">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-warning"><i class="fas fa-clock me-2"></i>Missing Payments Flag</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 audit-table">
                            <thead class="bg-light">
                                <tr class="x-small fw-bold">
                                    <th class="ps-4">ITEM / REF</th>
                                    <th>CUSTOMER</th>
                                    <th>STATUS</th>
                                    <th class="text-end">TOTAL</th>
                                    <th class="text-end pe-4">BALANCE</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($missingOrderPayments as $order)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <a href="{{ route('admin.orders.show', $order->order_code) }}" class="text-decoration-none h-link">
                                            <div class="fw-bold text-primary">{{ $order->order_code }}</div>
                                            <div class="x-small text-muted">Order • {{ $order->created_at->format('d M') }}</div>
                                        </a>
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-bold">{{ $order->user->name ?? 'Guest' }}</div>
                                    </td>
                                    <td><span class="badge bg-danger rounded-pill x-small">NO PAYMENT</span></td>
                                    <td class="text-end py-3 fw-bold">{{ number_format($order->total_amount) }}</td>
                                    <td class="text-end pe-4 py-3 fw-bold text-danger">{{ number_format($order->total_amount) }}</td>
                                </tr>
                                @endforeach
                                @foreach($completedWithBalance as $task)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <a href="{{ route('admin.design-tasks.show', $task->id) }}" class="text-decoration-none h-link">
                                            <div class="fw-bold text-primary">{{ $task->task_code }}</div>
                                            <div class="x-small text-muted">Task • {{ $task->created_at->format('d M') }}</div>
                                        </a>
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-bold">{{ $task->customer->name ?? '---' }}</div>
                                    </td>
                                    <td><span class="badge bg-warning text-dark x-small">UNPAID BAL</span></td>
                                    @php
                                        $taskTotal = $task->requires_receipt ? $task->price * 1.18 : $task->price;
                                    @endphp
                                    <td class="text-end py-3 fw-bold">{{ number_format($taskTotal) }}</td>
                                    <td class="text-end pe-4 py-3 fw-bold text-danger">{{ number_format($task->balance) }}</td>
                                </tr>
                                @endforeach
                                @if($missingOrderPayments->isEmpty() && $completedWithBalance->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted small">No items flag in this category</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Unbalanced Transactions -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden" style="border-radius: 16px;">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-danger"><i class="fas fa-scale-unbalanced me-2"></i>Amount Discrepancy</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 audit-table">
                            <thead class="bg-light">
                                <tr class="x-small fw-bold">
                                    <th class="ps-4">REFERENCE</th>
                                    <th>CUSTOMER</th>
                                    <th>EXPECTED</th>
                                    <th>BOOKED</th>
                                    <th class="text-end pe-4">GAP (DIFF)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unbalancedOrders as $order)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <a href="{{ route('admin.orders.show', $order->order_code) }}" class="text-decoration-none h-link">
                                            <div class="fw-bold text-primary">{{ $order->order_code }}</div>
                                            <div class="x-small text-muted">Order • {{ $order->created_at->format('d M') }}</div>
                                        </a>
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-bold">{{ $order->user->name ?? 'Guest' }}</div>
                                    </td>
                                    <td class="py-3 fw-bold">{{ number_format($order->total_amount) }}</td>
                                    <td class="py-3">{{ number_format($order->amount_paid + $order->balance) }}</td>
                                    <td class="text-end pe-4 py-3 text-danger fw-bold">
                                        {{ number_format(abs($order->total_amount - ($order->amount_paid + $order->balance))) }}
                                    </td>
                                </tr>
                                @endforeach
                                @foreach($unbalancedTasks as $task)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <a href="{{ route('admin.design-tasks.show', $task->id) }}" class="text-decoration-none h-link">
                                            <div class="fw-bold text-primary">{{ $task->task_code }}</div>
                                            <div class="x-small text-muted">Task • {{ $task->created_at->format('d M') }}</div>
                                        </a>
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-bold">{{ $task->customer->name ?? '---' }}</div>
                                    </td>
                                    @php
                                        $taskTotal = $task->requires_receipt ? $task->price * 1.18 : $task->price;
                                    @endphp
                                    <td class="py-3 fw-bold">{{ number_format($taskTotal) }}</td>
                                    <td class="py-3">{{ number_format($task->amount_paid + $task->balance) }}</td>
                                    <td class="text-end pe-4 py-3 text-danger fw-bold">
                                        {{ number_format(abs($taskTotal - ($task->amount_paid + $task->balance))) }}
                                    </td>
                                </tr>
                                @endforeach
                                @if($unbalancedOrders->isEmpty() && $unbalancedTasks->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted small">All transactions reflect exact balance</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Logic Mismatches -->
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 16px;">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-file-invoice-dollar me-2"></i>Status Logic Validation</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 audit-table">
                            <thead class="bg-light">
                                <tr class="x-small fw-bold">
                                    <th class="ps-4">ORDER REFERENCE</th>
                                    <th>CUSTOMER</th>
                                    <th>PAYMENT STATUS</th>
                                    <th>AUDIT LOGIC FINDING</th>
                                    <th class="text-end pe-4">BALANCE</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mismatchedOrders as $order)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <a href="{{ route('admin.orders.show', $order->order_code) }}" class="text-decoration-none h-link">
                                            <div class="fw-bold text-primary">{{ $order->order_code }}</div>
                                        </a>
                                    </td>
                                    <td class="py-3">
                                        <div class="fw-bold">{{ $order->user->name ?? '---' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'danger' }} x-small px-3">
                                            {{ strtoupper($order->payment_status) }}
                                        </span>
                                    </td>
                                    <td class="text-danger small fw-bold">
                                        @if($order->payment_status === 'paid' && $order->balance > 0)
                                            <i class="fas fa-bug me-1"></i> Conflict: Marked "PAID" but has outstanding balance of {{ number_format($order->balance) }}
                                        @elseif($order->payment_status === 'unpaid' && $order->amount_paid > 0)
                                            <i class="fas fa-bug me-1"></i> Conflict: Marked "UNPAID" but records show {{ number_format($order->amount_paid) }} paid!
                                        @endif
                                    </td>
                                    <td class="text-end pe-4 py-3 fw-bold">TZS {{ number_format($order->balance) }}</td>
                                </tr>
                                @endforeach
                                @if($mismatchedOrders->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted small">No payment logic conflicts detected</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800&display=swap');

    :root {
        --report-primary: #3b82f6;
        --report-danger: #ef4444;
        --report-bg: #f8fafc;
        --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        --glass-bg: rgba(255, 255, 255, 0.85);
        --glass-border: rgba(255, 255, 255, 0.3);
    }

    body {
        background-color: var(--report-bg);
        font-family: 'Nunito Sans', sans-serif;
    }

    .glass-morphism {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
    }

    .filter-bar { transition: all 0.3s ease; }
    .filter-group { display: flex; flex-direction: column; gap: 5px; }
    .filter-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-left: 5px; }

    .btn-preset {
        font-size: 13px; font-weight: 600; padding: 8px 18px;
        border: 1px solid #e2e8f0; background: white; color: #475569; transition: all 0.2s ease;
    }
    .btn-preset:hover { background: #f1f5f9; color: var(--report-primary); }
    .btn-preset.active {
        background: var(--report-primary); color: white; border-color: var(--report-primary);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .input-modern {
        display: flex; align-items: center; background: white;
        border: 1px solid #e2e8f0; border-radius: 10px; padding: 6px 12px; gap: 10px;
    }
    .input-modern .icon { color: var(--report-primary); font-size: 14px; }
    .input-modern input { font-size: 13px; font-weight: 600; color: #1e293b; outline: none; }

    .btn-apply {
        background: #1e293b; color: white; font-weight: 700; padding: 10px 24px;
        border-radius: 10px; font-size: 13px; transition: all 0.2s ease; border: none;
    }
    .btn-apply:hover { background: #0f172a; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); }

    .filter-divider { width: 1px; height: 35px; background: #e2e8f0; align-self: flex-end; margin-bottom: 5px; }
    .preset-group { border-radius: 10px; overflow: hidden; }
    .preset-group .btn-preset:first-child { border-radius: 10px 0 0 10px; }
    .preset-group .btn-preset:last-child { border-radius: 0 10px 10px 0; }

    .audit-table th {
        font-size: 10px; text-transform: uppercase; letter-spacing: 1px;
        color: #64748b; background-color: #f8fafc; border-bottom: 1px solid #edf2f7;
    }
    .audit-table td { font-size: 13px; border-bottom: 1px solid #f1f5f9; }
    .audit-table tr:last-child td { border-bottom: none; }

    .h-link:hover .text-primary { text-decoration: underline; }
    .x-small { font-size: 11px; }
    .badge { font-weight: 700; letter-spacing: 0.3px; }
</style>
@endpush
@endsection
