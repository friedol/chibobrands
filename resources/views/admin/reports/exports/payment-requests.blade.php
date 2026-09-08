@extends('admin.reports.exports.base')

@php
    $totalAmount    = $requests->sum('amount');
    $approvedAmount = $requests->where('approval_status', 'approved')->sum('amount');
    $paidAmount     = $requests->where('payment_status', 'paid')->sum('amount');
@endphp

@section('report_content')
    <div class="section-title">Summary</div>
    <table class="stats-grid">
        <tr>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Total Requests</div>
                <div class="stats-value">{{ $requests->count() }}</div>
            </td>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Total Amount</div>
                <div class="stats-value">{{ number_format($totalAmount) }}</div>
            </td>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Approved Sum</div>
                <div class="stats-value">{{ number_format($approvedAmount) }}</div>
            </td>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Paid Sum</div>
                <div class="stats-value">{{ number_format($paidAmount) }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Payment Requests</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Reason</th>
                <th>Department</th>
                <th>Requested By</th>
                <th class="text-center">Approval</th>
                <th class="text-center">Payment</th>
                <th class="text-end">Amount (TZS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requests as $item)
            <tr>
                <td>{{ $item->created_at?->format('M d, Y') }}</td>
                <td class="fw-bold">{{ $item->reason }}</td>
                <td>{{ $item->department->name ?? '-' }}</td>
                <td>{{ $item->createdBy->name ?? '-' }}</td>
                <td class="text-center">{{ ucfirst($item->approval_status) }}</td>
                <td class="text-center">{{ ucfirst($item->payment_status) }}</td>
                <td class="text-end">{{ number_format($item->amount) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-end fw-bold">Total</td>
                <td class="text-end fw-bold">{{ number_format($totalAmount) }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
