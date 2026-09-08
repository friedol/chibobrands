@extends('layouts.admin')

@section('title', 'Financial Audit & Control')

@push('styles')
<style>
    body { background: #f8fafc; font-size: 13px; }

    /* ── Compact stat cards (design-task style) ── */
    .dash-stat-card-compact {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 9px 11px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        transition: all 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-decoration: none;
        color: inherit;
    }
    .dash-stat-card-compact:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .dsc-icon { width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0; }
    .dsc-val  { font-size: 1.05rem; font-weight: 700; line-height: 1.25; margin-top: 4px; }
    .dsc-lbl  { font-size: 11px; font-weight: 600; color: #64748b; margin-top: 1px; }
    .dsc-sub  { font-size: 10px; font-weight: 500; color: #94a3b8; }

    /* ── Filter bar ── */
    .audit-filter-bar {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 18px;
    }
    .filter-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; margin-bottom: 5px; }
    .period-group { display: flex; gap: 4px; }
    .period-btn {
        padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 700;
        border: 1.5px solid #e2e8f0; background: #fff; color: #64748b;
        text-decoration: none; transition: all .15s;
    }
    .period-btn:hover { border-color: #cbd5e1; color: #334155; }
    .period-btn.active { background: #0f172a; color: #fff; border-color: #0f172a; }


    /* ── System status banner ── */
    .status-banner {
        border-radius: 12px; padding: 12px 18px;
        display: flex; align-items: center; gap: 14px;
        border: 1px solid transparent;
    }
    .status-banner.has-issues { background: #fffbeb; border-color: #fde68a; }
    .status-banner.all-clear  { background: #f0fdf4; border-color: #bbf7d0; }

    /* ── Audit section cards ── */
    .audit-section {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
        overflow: hidden; margin-bottom: 20px;
    }
    .audit-section-header {
        padding: 13px 18px; display: flex; justify-content: space-between;
        align-items: center; border-bottom: 1px solid #f1f5f9;
    }
    .audit-section-header .title { font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
    .audit-section-header .meta  { display: flex; align-items: center; gap: 8px; font-size: 11px; }

    .audit-section.danger  .audit-section-header { background: #fff5f5; border-bottom-color: #fee2e2; }
    .audit-section.warning .audit-section-header { background: #fffbeb; border-bottom-color: #fde68a; }
    .audit-section.blue    .audit-section-header { background: #eff6ff; border-bottom-color: #bfdbfe; }

    /* ── Audit table ── */
    .audit-table { width: 100%; border-collapse: collapse; }
    .audit-table th {
        font-size: 10px; text-transform: uppercase; letter-spacing: .5px;
        font-weight: 700; color: #94a3b8; background: #f8fafc;
        padding: 9px 14px; border-bottom: 1px solid #e2e8f0; white-space: nowrap;
    }
    .audit-table td { font-size: 12.5px; padding: 10px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .audit-table tbody tr:last-child td { border-bottom: none; }
    .audit-table tbody tr:hover td { background: #f8fafc; }

    /* ── Ref link ── */
    .ref-link { text-decoration: none; }
    .ref-link .ref-code { font-weight: 700; color: #1d4ed8; font-size: 12.5px; }
    .ref-link:hover .ref-code { text-decoration: underline; }
    .ref-meta { font-size: 10px; color: #94a3b8; margin-top: 1px; }

    /* ── Amount cells ── */
    .amt { font-weight: 700; font-size: 12.5px; }
    .amt-danger { color: #dc2626; }
    .amt-warn   { color: #d97706; }

    /* ── Badges ── */
    .pill { display: inline-block; border-radius: 5px; padding: 2px 8px; font-size: 10px; font-weight: 700; }
    .pill-danger  { background: #fee2e2; color: #b91c1c; }
    .pill-warn    { background: #fef3c7; color: #92400e; }
    .pill-success { background: #dcfce7; color: #15803d; }
    .pill-blue    { background: #dbeafe; color: #1e40af; }

    /* ── Empty state ── */
    .empty-state { padding: 36px; text-align: center; color: #94a3b8; }
    .empty-state i { font-size: 28px; opacity: .3; margin-bottom: 8px; display: block; }

    /* ── Finding description ── */
    .finding { font-size: 11.5px; font-weight: 600; color: #7f1d1d; display: flex; align-items: center; gap: 6px; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- ── Header ── --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-0">Financial Audit &amp; Control</h4>
            <p class="text-muted mb-0" style="font-size:12px;">Anomaly detection and balance reconciliation</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.finance.dashboard') }}" class="btn btn-light btn-sm fw-bold border rounded-3 px-3" data-no-global-handler data-no-preloader>
                <i class="fas fa-chart-line me-1"></i>Finance Hub
            </a>
            <x-report-export-menu
                :print-url="route('admin.finance.audit.print', request()->all())"
                :pdf-url="route('admin.finance.audit.pdf', request()->all())"
                :excel-url="route('admin.finance.audit.excel', request()->all())"
                label="Export"
            />
        </div>
    </div>

    {{-- ── Filter bar ── --}}
    <div class="audit-filter-bar mb-4">
        <form action="{{ route('admin.finance.audit') }}" method="GET" class="d-flex flex-wrap align-items-end gap-3" data-no-global-handler>
            <div>
                <div class="filter-label">Quick Period</div>
                <div class="period-group">
                    <a href="{{ route('admin.finance.audit', ['period'=>'today']) }}" class="period-btn {{ $period=='today' ? 'active' : '' }}" data-no-global-handler data-no-preloader>Today</a>
                    <a href="{{ route('admin.finance.audit', ['period'=>'week']) }}"  class="period-btn {{ $period=='week'  ? 'active' : '' }}" data-no-global-handler data-no-preloader>Week</a>
                    <a href="{{ route('admin.finance.audit', ['period'=>'month']) }}" class="period-btn {{ $period=='month' ? 'active' : '' }}" data-no-global-handler data-no-preloader>Month</a>
                    <a href="{{ route('admin.finance.audit', ['period'=>'year']) }}"  class="period-btn {{ $period=='year'  ? 'active' : '' }}" data-no-global-handler data-no-preloader>Year</a>
                </div>
            </div>
            <div style="width:1px;height:32px;background:#e2e8f0;align-self:flex-end;" class="d-none d-md-block"></div>
            <div>
                <div class="filter-label">Custom Range</div>
                <div class="d-flex gap-2">
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm rounded-3" style="width:148px;font-weight:600;">
                    <input type="date" name="date_to"   value="{{ $dateTo }}"   class="form-control form-control-sm rounded-3" style="width:148px;font-weight:600;">
                    <button type="submit" class="btn btn-dark btn-sm fw-bold rounded-3 px-3" data-no-global-handler data-no-preloader>
                        <i class="fas fa-sync-alt me-1"></i>Run
                    </button>
                </div>
            </div>
            <div class="ms-auto d-none d-md-flex align-items-end">
                <span class="text-muted" style="font-size:11px;">
                    Scope: <strong>{{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</strong>
                </span>
            </div>
        </form>
    </div>

    @php
        $totalIssues   = $unbalancedOrders->count() + $unbalancedTasks->count()
                       + $missingOrderPayments->count() + $completedWithBalance->count()
                       + $mismatchedOrders->count();

        $missingCount  = $missingOrderPayments->count() + $completedWithBalance->count();
        $discrepCount  = $unbalancedOrders->count() + $unbalancedTasks->count();
        $mismatchCount = $mismatchedOrders->count();

        // Financial exposure per category
        $missingExposure = $missingOrderPayments->sum('total_amount')
                         + $completedWithBalance->sum('balance');

        $discrepExposure = $unbalancedOrders->sum(fn($o) => abs($o->total_amount - ($o->amount_paid + $o->balance)))
                         + $unbalancedTasks->sum(function($t) {
                               $total = $t->requires_receipt ? $t->price * 1.18 : $t->price;
                               return abs($total - ($t->amount_paid + $t->balance));
                           });

        $mismatchExposure = $mismatchedOrders->sum('balance');
    @endphp

    {{-- ── System status banner ── --}}
    <div class="status-banner mb-4 {{ $totalIssues > 0 ? 'has-issues' : 'all-clear' }}">
        <i class="fas {{ $totalIssues > 0 ? 'fa-exclamation-triangle text-warning' : 'fa-shield-check text-success' }} fs-4"></i>
        <div>
            @if($totalIssues > 0)
                <div class="fw-bold">{{ $totalIssues }} anomalie{{ $totalIssues != 1 ? 's' : '' }} detected — total exposure: <span class="text-danger">TZS {{ number_format($missingExposure + $discrepExposure + $mismatchExposure) }}</span></div>
                <div class="text-muted" style="font-size:11.5px;">Review each flagged section below to maintain financial accuracy.</div>
            @else
                <div class="fw-bold text-success">System balanced — no anomalies detected</div>
                <div class="text-muted" style="font-size:11.5px;">All orders and tasks match their payment records for {{ \Carbon\Carbon::parse($dateFrom)->format('d M') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M, Y') }}.</div>
            @endif
        </div>
    </div>

    {{-- ── Summary stat cards (design-task compact style) ── --}}
    <div class="row g-2 mb-4">
        {{-- Total Issues --}}
        <div class="col-6 col-md-3">
            <div class="dash-stat-card-compact">
                <div class="d-flex justify-content-between align-items-center">
                    @if($totalIssues > 0)
                        <div class="dsc-icon bg-danger-subtle text-danger"><i class="fas fa-bug"></i></div>
                        <span class="dsc-sub">Issues</span>
                    @else
                        <div class="dsc-icon bg-success-subtle text-success"><i class="fas fa-check"></i></div>
                        <span class="dsc-sub">Clean</span>
                    @endif
                </div>
                <div class="dsc-val {{ $totalIssues > 0 ? 'text-danger' : 'text-success' }}">{{ $totalIssues }}</div>
                <div class="dsc-lbl">Total Anomalies</div>
            </div>
        </div>

        {{-- Missing Payments --}}
        <div class="col-6 col-md-3">
            <div class="dash-stat-card-compact">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="dsc-icon bg-warning-subtle text-warning"><i class="fas fa-clock"></i></div>
                    <span class="dsc-sub">{{ $missingExposure > 0 ? 'TZS '.number_format($missingExposure) : 'None' }}</span>
                </div>
                <div class="dsc-val text-warning">{{ $missingCount }}</div>
                <div class="dsc-lbl">Missing Payments</div>
            </div>
        </div>

        {{-- Discrepancies --}}
        <div class="col-6 col-md-3">
            <div class="dash-stat-card-compact">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="dsc-icon bg-danger-subtle text-danger"><i class="fas fa-scale-unbalanced"></i></div>
                    <span class="dsc-sub">{{ $discrepExposure > 0 ? 'TZS '.number_format($discrepExposure) : 'None' }}</span>
                </div>
                <div class="dsc-val text-danger">{{ $discrepCount }}</div>
                <div class="dsc-lbl">Discrepancies</div>
            </div>
        </div>

        {{-- Status Conflicts --}}
        <div class="col-6 col-md-3">
            <div class="dash-stat-card-compact">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="dsc-icon bg-primary-subtle text-primary"><i class="fas fa-file-invoice-dollar"></i></div>
                    <span class="dsc-sub">{{ $mismatchExposure > 0 ? 'TZS '.number_format($mismatchExposure) : 'None' }}</span>
                </div>
                <div class="dsc-val text-primary">{{ $mismatchCount }}</div>
                <div class="dsc-lbl">Status Conflicts</div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════
         SECTION 1 — Missing Payments
    ══════════════════════════════════ --}}
    <div class="audit-section warning">
        <div class="audit-section-header">
            <div class="title text-warning">
                <i class="fas fa-clock"></i> Missing Payments Flag
                <span class="pill {{ $missingCount > 0 ? 'pill-warn' : 'pill-success' }}">
                    {{ $missingCount }} issue{{ $missingCount != 1 ? 's' : '' }}
                </span>
            </div>
            @if($missingExposure > 0)
            <div class="meta">
                <span class="text-muted">Exposure:</span>
                <span class="fw-bold text-danger">TZS {{ number_format($missingExposure) }}</span>
            </div>
            @endif
        </div>
        <div class="table-responsive">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th style="padding-left:18px;">Reference</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Flag</th>
                        <th class="text-end">Total</th>
                        <th class="text-end" style="padding-right:18px;">Outstanding</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($missingOrderPayments as $order)
                    <tr>
                        <td style="padding-left:18px;">
                            <a href="{{ route('admin.orders.show', $order->order_code) }}" class="ref-link" target="_blank">
                                <div class="ref-code">{{ $order->order_code }}</div>
                                <div class="ref-meta">{{ $order->created_at->format('d M Y') }}</div>
                            </a>
                        </td>
                        <td class="fw-semibold">{{ $order->user->name ?? 'Guest' }}</td>
                        <td><span class="pill pill-blue">Order</span></td>
                        <td><span class="pill pill-danger"><i class="fas fa-ban me-1"></i>No Payment Recorded</span></td>
                        <td class="text-end amt">{{ number_format($order->total_amount) }}</td>
                        <td class="text-end amt amt-danger" style="padding-right:18px;">{{ number_format($order->total_amount) }}</td>
                    </tr>
                    @endforeach

                    @foreach($completedWithBalance as $task)
                    @php $taskTotal = $task->requires_receipt ? $task->price * 1.18 : $task->price; @endphp
                    <tr>
                        <td style="padding-left:18px;">
                            <a href="{{ route('admin.design-tasks.show', $task->id) }}" class="ref-link" target="_blank">
                                <div class="ref-code">{{ $task->task_code }}</div>
                                <div class="ref-meta">{{ $task->created_at->format('d M Y') }} · {{ ucfirst(str_replace('_', ' ', $task->status)) }}</div>
                            </a>
                        </td>
                        <td class="fw-semibold">{{ $task->customer->name ?? '—' }}</td>
                        <td><span class="pill pill-blue">Design Task{{ $task->requires_receipt ? ' +VAT' : '' }}</span></td>
                        <td><span class="pill pill-warn"><i class="fas fa-exclamation me-1"></i>Balance Unpaid</span></td>
                        <td class="text-end amt">{{ number_format($taskTotal) }}</td>
                        <td class="text-end amt amt-danger" style="padding-right:18px;">{{ number_format($task->balance) }}</td>
                    </tr>
                    @endforeach

                    @if($missingOrderPayments->isEmpty() && $completedWithBalance->isEmpty())
                    <tr><td colspan="6" class="empty-state"><i class="fas fa-check-circle text-success"></i>No missing payments flagged for this period</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══════════════════════════════════
         SECTION 2 — Amount Discrepancy
    ══════════════════════════════════ --}}
    <div class="audit-section danger">
        <div class="audit-section-header">
            <div class="title text-danger">
                <i class="fas fa-scale-unbalanced"></i> Amount Discrepancy
                <span class="pill {{ $discrepCount > 0 ? 'pill-danger' : 'pill-success' }}">
                    {{ $discrepCount }} issue{{ $discrepCount != 1 ? 's' : '' }}
                </span>
            </div>
            @if($discrepExposure > 0)
            <div class="meta">
                <span class="text-muted">Total gap:</span>
                <span class="fw-bold text-danger">TZS {{ number_format($discrepExposure) }}</span>
            </div>
            @endif
        </div>
        <div class="table-responsive">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th style="padding-left:18px;">Reference</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th class="text-end">Expected</th>
                        <th class="text-end">Recorded (Paid+Bal)</th>
                        <th class="text-end" style="padding-right:18px;">Gap</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($unbalancedOrders as $order)
                    <tr>
                        <td style="padding-left:18px;">
                            <a href="{{ route('admin.orders.show', $order->order_code) }}" class="ref-link" target="_blank">
                                <div class="ref-code">{{ $order->order_code }}</div>
                                <div class="ref-meta">{{ $order->created_at->format('d M Y') }}</div>
                            </a>
                        </td>
                        <td class="fw-semibold">{{ $order->user->name ?? 'Guest' }}</td>
                        <td><span class="pill pill-blue">Order</span></td>
                        <td class="text-end amt">{{ number_format($order->total_amount) }}</td>
                        <td class="text-end">{{ number_format($order->amount_paid + $order->balance) }}</td>
                        <td class="text-end amt amt-danger" style="padding-right:18px;">
                            {{ number_format(abs($order->total_amount - ($order->amount_paid + $order->balance))) }}
                        </td>
                    </tr>
                    @endforeach

                    @foreach($unbalancedTasks as $task)
                    @php $taskTotal = $task->requires_receipt ? $task->price * 1.18 : $task->price; @endphp
                    <tr>
                        <td style="padding-left:18px;">
                            <a href="{{ route('admin.design-tasks.show', $task->id) }}" class="ref-link" target="_blank">
                                <div class="ref-code">{{ $task->task_code }}</div>
                                <div class="ref-meta">{{ $task->created_at->format('d M Y') }}{{ $task->requires_receipt ? ' · Incl. 18% VAT' : '' }}</div>
                            </a>
                        </td>
                        <td class="fw-semibold">{{ $task->customer->name ?? '—' }}</td>
                        <td><span class="pill pill-blue">Design Task</span></td>
                        <td class="text-end amt">{{ number_format($taskTotal) }}</td>
                        <td class="text-end">{{ number_format($task->amount_paid + $task->balance) }}</td>
                        <td class="text-end amt amt-danger" style="padding-right:18px;">
                            {{ number_format(abs($taskTotal - ($task->amount_paid + $task->balance))) }}
                        </td>
                    </tr>
                    @endforeach

                    @if($unbalancedOrders->isEmpty() && $unbalancedTasks->isEmpty())
                    <tr><td colspan="6" class="empty-state"><i class="fas fa-check-circle text-success"></i>All transactions reflect exact balance</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══════════════════════════════════
         SECTION 3 — Status Conflicts
    ══════════════════════════════════ --}}
    <div class="audit-section blue">
        <div class="audit-section-header">
            <div class="title text-primary">
                <i class="fas fa-file-invoice-dollar"></i> Status Logic Conflicts
                <span class="pill {{ $mismatchCount > 0 ? 'pill-blue' : 'pill-success' }}">
                    {{ $mismatchCount }} issue{{ $mismatchCount != 1 ? 's' : '' }}
                </span>
            </div>
            @if($mismatchExposure > 0)
            <div class="meta">
                <span class="text-muted">Balance at risk:</span>
                <span class="fw-bold text-primary">TZS {{ number_format($mismatchExposure) }}</span>
            </div>
            @endif
        </div>
        <div class="table-responsive">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th style="padding-left:18px;">Order Reference</th>
                        <th>Customer</th>
                        <th>Payment Status</th>
                        <th>Logic Finding</th>
                        <th class="text-end" style="padding-right:18px;">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mismatchedOrders as $order)
                    <tr>
                        <td style="padding-left:18px;">
                            <a href="{{ route('admin.orders.show', $order->order_code) }}" class="ref-link" target="_blank">
                                <div class="ref-code">{{ $order->order_code }}</div>
                                <div class="ref-meta">{{ $order->created_at->format('d M Y') }}</div>
                            </a>
                        </td>
                        <td class="fw-semibold">{{ $order->user->name ?? '—' }}</td>
                        <td>
                            <span class="pill {{ $order->payment_status === 'paid' ? 'pill-success' : 'pill-danger' }}">
                                {{ strtoupper($order->payment_status) }}
                            </span>
                        </td>
                        <td>
                            <div class="finding">
                                <i class="fas fa-bug text-danger"></i>
                                @if($order->payment_status === 'paid' && $order->balance > 0)
                                    Marked PAID but has outstanding balance of TZS {{ number_format($order->balance) }}
                                @elseif($order->payment_status === 'unpaid' && $order->amount_paid > 0)
                                    Marked UNPAID but TZS {{ number_format($order->amount_paid) }} already recorded as paid
                                @endif
                            </div>
                        </td>
                        <td class="text-end amt amt-danger" style="padding-right:18px;">{{ number_format($order->balance) }}</td>
                    </tr>
                    @endforeach

                    @if($mismatchedOrders->isEmpty())
                    <tr><td colspan="5" class="empty-state"><i class="fas fa-check-circle text-success"></i>No payment status conflicts detected</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
