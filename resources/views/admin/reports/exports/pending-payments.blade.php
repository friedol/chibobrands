@extends('admin.reports.exports.base')

@php
    $totalBalance = $pendingTasks->sum('balance') + $pendingOrders->sum('balance');
@endphp

@section('report_content')
    <table class="stats-grid">
        <tr>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Total Outstanding</div>
                <div class="stats-value">TZS {{ number_format($totalBalance) }}</div>
            </td>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Design Tasks</div>
                <div class="stats-value">{{ $pendingTasks->count() }}</div>
            </td>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Product Orders</div>
                <div class="stats-value">{{ $pendingOrders->count() }}</div>
            </td>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Filter</div>
                <div class="stats-value" style="font-size: 13px;">
                    {{ $salerName ?? ($search ? '"' . $search . '"' : 'All') }}
                </div>
            </td>
        </tr>
    </table>

    @if($pendingTasks->isNotEmpty())
    <div class="section-title">Design Tasks</div>
    <table>
        <thead>
            <tr>
                <th>Task Code</th>
                <th>Title</th>
                <th>Customer</th>
                <th>Salesperson</th>
                <th class="text-end">Total</th>
                <th class="text-end">Paid</th>
                <th class="text-end">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pendingTasks as $task)
                @php
                    $basePrice = $task->requires_receipt ? $task->price * 1.18 : $task->price;
                    $deliveryCost = (float) ($task->delivery_cost ?? 0);
                    $deliveryDiscount = (float) ($task->delivery_discount ?? 0);
                    $taskTotal = $basePrice + $deliveryCost - $deliveryDiscount;
                @endphp
                <tr>
                    <td class="fw-bold">{{ $task->task_code }}</td>
                    <td>{{ $task->title }}</td>
                    <td>{{ $task->customer->name ?? 'Walk-in' }}</td>
                    <td>{{ $task->saler->name ?? '-' }}</td>
                    <td class="text-end">{{ number_format($taskTotal) }}</td>
                    <td class="text-end" style="color: #198754;">{{ number_format($task->amount_paid) }}</td>
                    <td class="text-end fw-bold" style="color: #dc3545;">{{ number_format($task->balance) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-end fw-bold">Totals</td>
                <td class="text-end fw-bold">
                    @php
                        $tasksTotalSum = 0;
                        foreach ($pendingTasks as $t) {
                            $base = $t->requires_receipt ? $t->price * 1.18 : $t->price;
                            $tasksTotalSum += $base + (float) ($t->delivery_cost ?? 0) - (float) ($t->delivery_discount ?? 0);
                        }
                    @endphp
                    {{ number_format($tasksTotalSum) }}
                </td>
                <td class="text-end fw-bold">{{ number_format($pendingTasks->sum('amount_paid')) }}</td>
                <td class="text-end fw-bold">{{ number_format($pendingTasks->sum('balance')) }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    @if($pendingOrders->isNotEmpty())
    <div class="section-title">Product Orders</div>
    <table>
        <thead>
            <tr>
                <th>Order Code</th>
                <th>Customer</th>
                <th>Date</th>
                <th class="text-end">Total</th>
                <th class="text-end">Paid</th>
                <th class="text-end">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pendingOrders as $order)
                <tr>
                    <td class="fw-bold">{{ $order->order_code }}</td>
                    <td>{{ $order->user->name ?? 'Walk-in' }}</td>
                    <td>{{ $order->created_at->format('d M, Y') }}</td>
                    <td class="text-end">{{ number_format($order->total_amount) }}</td>
                    <td class="text-end" style="color: #198754;">{{ number_format($order->amount_paid) }}</td>
                    <td class="text-end fw-bold" style="color: #dc3545;">{{ number_format($order->balance) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-end fw-bold">Totals</td>
                <td class="text-end fw-bold">{{ number_format($pendingOrders->sum('total_amount')) }}</td>
                <td class="text-end fw-bold">{{ number_format($pendingOrders->sum('amount_paid')) }}</td>
                <td class="text-end fw-bold">{{ number_format($pendingOrders->sum('balance')) }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    @if($pendingTasks->isEmpty() && $pendingOrders->isEmpty())
    <p class="text-center">No pending payments found for the selected filters.</p>
    @endif
@endsection
