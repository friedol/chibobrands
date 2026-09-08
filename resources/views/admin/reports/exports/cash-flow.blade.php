@extends('admin.reports.exports.base')

@php
    $totalIn = $entries->where('entry_type', 'payment')->sum('amount');
    $totalOut = $entries->where('entry_type', 'expense')->sum('amount');
@endphp

@section('report_content')
    <table class="stats-grid">
        <tr>
            <td class="stats-card" style="width: 33%;">
                <div class="stats-label">Total Inflow</div>
                <div class="stats-value" style="color: #198754;">TZS {{ number_format($totalIn) }}</div>
            </td>
            <td class="stats-card" style="width: 33%;">
                <div class="stats-label">Total Outflow</div>
                <div class="stats-value" style="color: #dc3545;">TZS {{ number_format($totalOut) }}</div>
            </td>
            <td class="stats-card" style="width: 34%;">
                <div class="stats-label">Net Cash Flow</div>
                <div class="stats-value">TZS {{ number_format($totalIn - $totalOut) }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Ledger Entries</div>
    <table>
        <thead>
            <tr>
                <th class="text-center">Flow</th>
                <th>Date</th>
                <th>Source / Recipient</th>
                <th>Details</th>
                <th class="text-center">Method</th>
                <th class="text-end">Amount (TZS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries as $entry)
            <tr>
                <td class="text-center">
                    @if($entry->entry_type === 'payment')
                        <span class="badge bg-success">IN</span>
                    @else
                        <span class="badge bg-danger">OUT</span>
                    @endif
                </td>
                <td>{{ $entry->date->format('d M, Y') }}</td>
                <td class="fw-bold">
                    {{ $entry->entry_type === 'payment' ? ($entry->customer->name ?? 'Unknown') : $entry->category }}
                </td>
                <td>
                    @if($entry->entry_type === 'payment')
                        {{ $entry->order ? 'Order #' . $entry->order->order_code : ($entry->designTask->title ?? 'Manual Payment') }}
                    @else
                        {{ $entry->notes ?: '—' }}
                    @endif
                </td>
                <td class="text-center">{{ strtoupper(str_replace('_', ' ', $entry->payment_method)) }}</td>
                <td class="text-end fw-bold">{{ number_format($entry->amount) }}</td>
            </tr>
            @endforeach
            @if($entries->isEmpty())
            <tr><td colspan="6" class="text-center">No transactions found for the selected filters.</td></tr>
            @endif
        </tbody>
    </table>
@endsection
