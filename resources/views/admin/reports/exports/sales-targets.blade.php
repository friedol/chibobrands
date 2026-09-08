@extends('admin.reports.exports.base')

@php
    $now = now();
    $deptTargets  = $targets->filter(fn($t) => !$t->seller_id);
    $salerTargets = $targets->filter(fn($t) =>  $t->seller_id);

    $stLabelPdf = function ($t, $now) {
        if ($t->start_date <= $now && $t->end_date >= $now) return 'Active';
        if ($t->start_date > $now) return 'Upcoming';
        return 'Expired';
    };
@endphp

@section('report_content')
    <div class="section-title">Summary</div>
    <table class="stats-grid">
        <tr>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Total Targets</div>
                <div class="stats-value">{{ $targets->count() }}</div>
            </td>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Grand Total</div>
                <div class="stats-value">{{ number_format($targets->sum('target_amount')) }}</div>
            </td>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Department Targets</div>
                <div class="stats-value">{{ $deptTargets->count() }}</div>
            </td>
            <td class="stats-card" style="width: 25%;">
                <div class="stats-label">Saler Targets</div>
                <div class="stats-value">{{ $salerTargets->count() }}</div>
            </td>
        </tr>
    </table>

    @if($targetType !== 'saler' && $deptTargets->count())
    <div class="section-title">Department Targets</div>
    <table>
        <thead>
            <tr>
                <th>Department</th>
                <th class="text-center">Period</th>
                <th class="text-center">Start Date</th>
                <th class="text-center">End Date</th>
                <th class="text-center">Status</th>
                <th class="text-end">Target Amount (TZS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deptTargets as $t)
            <tr>
                <td class="fw-bold">{{ $t->department->name ?? 'All Departments' }}</td>
                <td class="text-center">{{ ucfirst($t->period) }}</td>
                <td class="text-center">{{ $t->start_date->format('M d, Y') }}</td>
                <td class="text-center">{{ $t->end_date->format('M d, Y') }}</td>
                <td class="text-center">{{ $stLabelPdf($t, $now) }}</td>
                <td class="text-end">{{ number_format($t->target_amount) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-end fw-bold">Total</td>
                <td class="text-end fw-bold">{{ number_format($deptTargets->sum('target_amount')) }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    @if($targetType !== 'department' && $salerTargets->count())
    <div class="section-title">Saler Targets</div>
    <table>
        <thead>
            <tr>
                <th>Salesperson</th>
                <th>Department</th>
                <th class="text-center">Period</th>
                <th class="text-center">Start Date</th>
                <th class="text-center">End Date</th>
                <th class="text-center">Status</th>
                <th class="text-end">Target Amount (TZS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salerTargets as $t)
            <tr>
                <td class="fw-bold">{{ $t->seller->name ?? '-' }}</td>
                <td>{{ $t->department->name ?? 'All Depts' }}</td>
                <td class="text-center">{{ ucfirst($t->period) }}</td>
                <td class="text-center">{{ $t->start_date->format('M d, Y') }}</td>
                <td class="text-center">{{ $t->end_date->format('M d, Y') }}</td>
                <td class="text-center">{{ $stLabelPdf($t, $now) }}</td>
                <td class="text-end">{{ number_format($t->target_amount) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-end fw-bold">Total</td>
                <td class="text-end fw-bold">{{ number_format($salerTargets->sum('target_amount')) }}</td>
            </tr>
        </tfoot>
    </table>
    @endif
@endsection
