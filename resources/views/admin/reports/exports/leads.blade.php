@extends('admin.reports.exports.base')

@php
    $totalLeads     = $leads->count();
    $convertedLeads = $leads->where('status', 'converted')->count();
    $pendingLeads   = $leads->where('status', 'pending')->count();
    $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;
@endphp

@section('report_content')
    <div class="section-title">Pipeline Summary</div>
    <table class="stats-grid">
        <tr>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Total Leads</div>
                <div class="stats-value">{{ $totalLeads }}</div>
            </td>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Converted</div>
                <div class="stats-value">{{ $convertedLeads }}</div>
            </td>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Pending</div>
                <div class="stats-value">{{ $pendingLeads }}</div>
            </td>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Conversion Rate</div>
                <div class="stats-value">{{ $conversionRate }}%</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Leads</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Customer</th>
                <th>Product / Source</th>
                <th class="text-center">Interest</th>
                <th class="text-center">Status</th>
                <th>Assigned Seller</th>
            </tr>
        </thead>
        <tbody>
            @foreach($leads as $lead)
            <tr>
                <td>{{ $lead->created_at?->format('M d, Y') }}</td>
                <td>
                    <div class="fw-bold">{{ $lead->customer_name }}</div>
                    <div style="font-size: 9px; color: #666;">{{ $lead->phone }}</div>
                </td>
                <td>
                    {{ $lead->product_requested ?: '-' }}
                    <div style="font-size: 9px; color: #888;">Src: {{ $lead->source ?: 'N/A' }}</div>
                </td>
                <td class="text-center">{{ $lead->interest_level ? ucfirst($lead->interest_level) : '-' }}</td>
                <td class="text-center">{{ ucfirst(str_replace('_', ' ', $lead->status)) }}</td>
                <td>{{ $lead->seller->name ?? 'Unassigned' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
