@extends('admin.reports.exports.base')

@section('report_content')
    <table class="stats-grid">
        <tr>
            <td class="stats-card" style="width: 33%;">
                <div class="stats-label">Total Customers</div>
                <div class="stats-value">{{ $customers->count() }}</div>
            </td>
            <td class="stats-card" style="width: 33%;">
                <div class="stats-label">Total Paid</div>
                <div class="stats-value" style="color: #198754;">TZS {{ number_format($customers->sum('total_paid')) }}</div>
            </td>
            <td class="stats-card" style="width: 34%;">
                <div class="stats-label">Total Outstanding</div>
                <div class="stats-value" style="color: #dc3545;">TZS {{ number_format($customers->sum('unpaid_balance')) }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Customer Balances</div>
    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Phone</th>
                <th class="text-center">Lead Status</th>
                <th class="text-end">Total Paid</th>
                <th class="text-end">Outstanding</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
            <tr>
                <td class="fw-bold">{{ $customer->name }}</td>
                <td>{{ $customer->phone }}</td>
                <td class="text-center">{{ $customer->lead_status ? ucfirst($customer->lead_status) : '—' }}</td>
                <td class="text-end fw-bold" style="color: #198754;">{{ number_format($customer->total_paid) }}</td>
                <td class="text-end fw-bold" style="color: #dc3545;">{{ number_format($customer->unpaid_balance) }}</td>
            </tr>
            @endforeach
            @if($customers->isEmpty())
            <tr><td colspan="5" class="text-center">No customers found for the selected filters.</td></tr>
            @endif
        </tbody>
    </table>
@endsection
