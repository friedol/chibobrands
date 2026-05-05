@extends('admin.reports.exports.base')

@section('report_content')
    <div class="section-title">Sales Performance Summary</div>
    <table class="stats-grid">
        <tr>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Total Orders</div>
                <div class="stats-value">{{ number_format($summary['total_orders']) }}</div>
            </td>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Design Tasks</div>
                <div class="stats-value">{{ number_format($summary['design_tasks']) }}</div>
            </td>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Total Revenue</div>
                <div class="stats-value">TZS {{ number_format($summary['total_revenue']) }}</div>
            </td>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Avg. Transaction</div>
                <div class="stats-value">TZS {{ number_format($summary['avg_deal']) }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Salesperson Breakdown</div>
    <table>
        <thead>
            <tr>
                <th>Salesperson</th>
                <th class="text-center">Orders</th>
                <th class="text-center">Design Tasks</th>
                <th class="text-end">Total Revenue</th>
                <th class="text-end">Avg. Trans.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salers as $saler)
            @php
                $revenue = $saler->salerOrders->sum('total_amount') + $saler->designTasks->sum('price');
                $totalTrans = ($saler->saler_orders_count ?? $saler->salerOrders->count()) + ($saler->design_tasks_count ?? $saler->designTasks->count());
                $avg = $totalTrans > 0 ? $revenue / $totalTrans : 0;
            @endphp
            <tr>
                <td class="fw-bold">{{ $saler->name }}</td>
                <td class="text-center">{{ $saler->saler_orders_count ?? $saler->salerOrders->count() }}</td>
                <td class="text-center">{{ $saler->design_tasks_count ?? $saler->designTasks->count() }}</td>
                <td class="text-end">TZS {{ number_format($revenue) }}</td>
                <td class="text-end">TZS {{ number_format($avg) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if(count($history) > 0)
    <div class="section-title">6-Month Performance History</div>
    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th class="text-center">Orders</th>
                <th class="text-center">Design Tasks</th>
                <th class="text-end">Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($history as $h)
            <tr>
                <td>{{ $h['month'] }}</td>
                <td class="text-center">{{ $h['orders'] }}</td>
                <td class="text-center">{{ $h['tasks'] }}</td>
                <td class="text-end">TZS {{ number_format($h['revenue']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(count($recentOrders) > 0)
    <div class="section-title">Recent Orders</div>
    <table>
        <thead>
            <tr>
                <th>Order Date</th>
                <th>Customer</th>
                <th class="text-center">Status</th>
                <th class="text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentOrders as $order)
            <tr>
                <td>{{ $order->created_at->format('M d, Y') }}</td>
                <td>{{ $order->customer->name ?? 'N/A' }}</td>
                <td class="text-center">{{ ucfirst($order->status) }}</td>
                <td class="text-end">TZS {{ number_format($order->total_amount) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(count($recentTasks) > 0)
    <div class="section-title">Recent Design Tasks</div>
    <table>
        <thead>
            <tr>
                <th>Task Title</th>
                <th>Customer</th>
                <th class="text-center">Status</th>
                <th class="text-end">Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentTasks as $task)
            <tr>
                <td>{{ $task->title }}</td>
                <td>{{ $task->customer->name ?? 'N/A' }}</td>
                <td class="text-center">{{ ucfirst($task->status) }}</td>
                <td class="text-end">TZS {{ number_format($task->price) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
@endsection
