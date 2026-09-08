@extends('admin.reports.exports.base')

@php
    $totalIssues   = $unbalancedOrders->count() + $unbalancedTasks->count()
                   + $missingOrderPayments->count() + $completedWithBalance->count()
                   + $mismatchedOrders->count();

    $missingCount  = $missingOrderPayments->count() + $completedWithBalance->count();
    $discrepCount  = $unbalancedOrders->count() + $unbalancedTasks->count();
    $mismatchCount = $mismatchedOrders->count();

    $missingExposure = $missingOrderPayments->sum('total_amount')
                     + $completedWithBalance->sum('balance');

    $discrepExposure = $unbalancedOrders->sum(fn($o) => abs($o->total_amount - ($o->amount_paid + $o->balance)))
                     + $unbalancedTasks->sum(function($t) {
                           $total = $t->requires_receipt ? $t->price * 1.18 : $t->price;
                           return abs($total - ($t->amount_paid + $t->balance));
                       });

    $mismatchExposure = $mismatchedOrders->sum('balance');
    $totalExposure = $missingExposure + $discrepExposure + $mismatchExposure;
@endphp

@section('report_content')
    <table class="stats-grid">
        <tr>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Total Anomalies</div>
                <div class="stats-value">{{ $totalIssues }}</div>
            </td>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Missing Payments</div>
                <div class="stats-value">{{ $missingCount }}</div>
            </td>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Discrepancies</div>
                <div class="stats-value">{{ $discrepCount }}</div>
            </td>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Status Conflicts</div>
                <div class="stats-value">{{ $mismatchCount }}</div>
            </td>
        </tr>
    </table>

    @if($totalIssues > 0)
        <p style="font-size: 12px;">
            Total financial exposure across all flagged items:
            <strong style="color: #dc3545;">TZS {{ number_format($totalExposure) }}</strong>
        </p>
    @else
        <p style="font-size: 12px; color: #198754; font-weight: bold;">System balanced — no anomalies detected for this period.</p>
    @endif

    {{-- 1. Missing Payments --}}
    <div class="section-title">Missing Payments Flag ({{ $missingCount }})</div>
    <table>
        <thead>
            <tr>
                <th>Reference</th>
                <th>Customer</th>
                <th>Type</th>
                <th>Flag</th>
                <th class="text-end">Total</th>
                <th class="text-end">Outstanding</th>
            </tr>
        </thead>
        <tbody>
            @foreach($missingOrderPayments as $order)
            <tr>
                <td class="fw-bold">{{ $order->order_code }}</td>
                <td>{{ $order->user->name ?? 'Guest' }}</td>
                <td>Order</td>
                <td><span class="badge bg-danger">No Payment Recorded</span></td>
                <td class="text-end">{{ number_format($order->total_amount) }}</td>
                <td class="text-end fw-bold">{{ number_format($order->total_amount) }}</td>
            </tr>
            @endforeach
            @foreach($completedWithBalance as $task)
            @php $taskTotal = $task->requires_receipt ? $task->price * 1.18 : $task->price; @endphp
            <tr>
                <td class="fw-bold">{{ $task->task_code }}</td>
                <td>{{ $task->customer->name ?? 'N/A' }}</td>
                <td>Design Task{{ $task->requires_receipt ? ' +VAT' : '' }}</td>
                <td><span class="badge bg-warning">Balance Unpaid</span></td>
                <td class="text-end">{{ number_format($taskTotal) }}</td>
                <td class="text-end fw-bold">{{ number_format($task->balance) }}</td>
            </tr>
            @endforeach
            @if($missingOrderPayments->isEmpty() && $completedWithBalance->isEmpty())
            <tr><td colspan="6" class="text-center">No missing payments flagged for this period</td></tr>
            @endif
        </tbody>
    </table>

    {{-- 2. Amount Discrepancy --}}
    <div class="section-title">Amount Discrepancy ({{ $discrepCount }})</div>
    <table>
        <thead>
            <tr>
                <th>Reference</th>
                <th>Customer</th>
                <th>Type</th>
                <th class="text-end">Expected</th>
                <th class="text-end">Recorded (Paid+Bal)</th>
                <th class="text-end">Gap</th>
            </tr>
        </thead>
        <tbody>
            @foreach($unbalancedOrders as $order)
            <tr>
                <td class="fw-bold">{{ $order->order_code }}</td>
                <td>{{ $order->user->name ?? 'Guest' }}</td>
                <td>Order</td>
                <td class="text-end">{{ number_format($order->total_amount) }}</td>
                <td class="text-end">{{ number_format($order->amount_paid + $order->balance) }}</td>
                <td class="text-end fw-bold">{{ number_format(abs($order->total_amount - ($order->amount_paid + $order->balance))) }}</td>
            </tr>
            @endforeach
            @foreach($unbalancedTasks as $task)
            @php $taskTotal = $task->requires_receipt ? $task->price * 1.18 : $task->price; @endphp
            <tr>
                <td class="fw-bold">{{ $task->task_code }}</td>
                <td>{{ $task->customer->name ?? 'N/A' }}</td>
                <td>Design Task</td>
                <td class="text-end">{{ number_format($taskTotal) }}</td>
                <td class="text-end">{{ number_format($task->amount_paid + $task->balance) }}</td>
                <td class="text-end fw-bold">{{ number_format(abs($taskTotal - ($task->amount_paid + $task->balance))) }}</td>
            </tr>
            @endforeach
            @if($unbalancedOrders->isEmpty() && $unbalancedTasks->isEmpty())
            <tr><td colspan="6" class="text-center">All transactions reflect exact balance</td></tr>
            @endif
        </tbody>
    </table>

    {{-- 3. Status Conflicts --}}
    <div class="section-title">Status Logic Conflicts ({{ $mismatchCount }})</div>
    <table>
        <thead>
            <tr>
                <th>Order Reference</th>
                <th>Customer</th>
                <th>Payment Status</th>
                <th>Logic Finding</th>
                <th class="text-end">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mismatchedOrders as $order)
            <tr>
                <td class="fw-bold">{{ $order->order_code }}</td>
                <td>{{ $order->user->name ?? 'N/A' }}</td>
                <td>
                    <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success' : 'bg-danger' }}">
                        {{ strtoupper($order->payment_status) }}
                    </span>
                </td>
                <td>
                    @if($order->payment_status === 'paid' && $order->balance > 0)
                        Marked PAID but has outstanding balance of TZS {{ number_format($order->balance) }}
                    @elseif($order->payment_status === 'unpaid' && $order->amount_paid > 0)
                        Marked UNPAID but TZS {{ number_format($order->amount_paid) }} already recorded as paid
                    @endif
                </td>
                <td class="text-end fw-bold">{{ number_format($order->balance) }}</td>
            </tr>
            @endforeach
            @if($mismatchedOrders->isEmpty())
            <tr><td colspan="5" class="text-center">No payment status conflicts detected</td></tr>
            @endif
        </tbody>
    </table>
@endsection
