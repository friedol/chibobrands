@extends('admin.reports.exports.base')

@php
    $inCount  = $movements->where('type', 'in')->count();
    $outCount = $movements->where('type', 'out')->count();
@endphp

@section('report_content')
    <div class="section-title">Summary</div>
    <table class="stats-grid">
        <tr>
            <td class="stats-card" style="width: 33%;">
                <div class="stats-label">Total Records</div>
                <div class="stats-value">{{ $movements->count() }}</div>
            </td>
            <td class="stats-card" style="width: 33%;">
                <div class="stats-label">Incoming (IN)</div>
                <div class="stats-value">{{ $inCount }}</div>
            </td>
            <td class="stats-card" style="width: 34%;">
                <div class="stats-label">Outgoing (OUT)</div>
                <div class="stats-value">{{ $outCount }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Movement Log</div>
    <table>
        <thead>
            <tr>
                <th class="text-center">Type</th>
                <th>Date &amp; Time</th>
                <th>Product / Item</th>
                <th class="text-center">Qty</th>
                <th>Handler</th>
                <th>Context (Source / Recipient)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $movement)
            <tr>
                <td class="text-center">
                    <span class="badge {{ $movement->type === 'in' ? 'bg-success' : 'bg-warning' }}">{{ strtoupper($movement->type) }}</span>
                </td>
                <td>
                    <div class="fw-bold">{{ $movement->movement_date?->format('M d, Y') }}</div>
                    <div style="font-size: 9px; color: #666;">{{ $movement->movement_date?->format('h:i A') }}</div>
                </td>
                <td>
                    <div class="fw-bold">{{ $movement->product_name }}</div>
                    @if($movement->unit_price)
                        <div style="font-size: 9px; color: #666;">{{ number_format($movement->unit_price) }} TZS</div>
                    @endif
                </td>
                <td class="text-center fw-bold">{{ $movement->quantity }}</td>
                <td>
                    <div class="fw-bold">{{ $movement->handler_name }}</div>
                    <div style="font-size: 9px; color: #666; text-transform: uppercase;">{{ $movement->handler_type }}</div>
                </td>
                <td>
                    @if($movement->type === 'in')
                        <div>From: <strong>{{ $movement->source_name ?: '-' }}</strong></div>
                        <div style="font-size: 9px; color: #666;">Type: {{ $movement->source_type ?: '-' }}</div>
                    @else
                        <div>To: <strong>{{ $movement->recipient_name ?: '-' }}</strong></div>
                        <div style="font-size: 9px; color: #666;">Type: {{ $movement->recipient_type ?: '-' }}</div>
                        @if($movement->delivery_method)
                            <div style="font-size: 9px; color: #666;">via {{ $movement->delivery_method }}</div>
                        @endif
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
