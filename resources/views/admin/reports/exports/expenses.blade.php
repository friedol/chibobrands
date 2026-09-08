@extends('admin.reports.exports.base')

@section('report_content')
    <table class="stats-grid">
        <tr>
            <td class="stats-card" style="width: 33%;">
                <div class="stats-label">Total Records</div>
                <div class="stats-value">{{ $expenses->count() }}</div>
            </td>
            <td class="stats-card" style="width: 33%;">
                <div class="stats-label">Grand Total</div>
                <div class="stats-value">TZS {{ number_format($expenses->sum('amount')) }}</div>
            </td>
            <td class="stats-card" style="width: 34%;">
                <div class="stats-label">Categories</div>
                <div class="stats-value">{{ $expenses->pluck('category')->unique()->count() }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Expense Records</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Department</th>
                <th>Notes</th>
                <th class="text-center">Method</th>
                <th class="text-end">Amount (TZS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $expense->date->format('d M, Y') }}</td>
                <td class="fw-bold">{{ ucfirst(str_replace('_', ' ', $expense->category)) }}</td>
                <td>{{ $expense->department->name ?? 'General' }}</td>
                <td>{{ $expense->notes ?: '—' }}</td>
                <td class="text-center">{{ strtoupper(str_replace('_', ' ', $expense->payment_method)) }}</td>
                <td class="text-end fw-bold">{{ number_format($expense->amount) }}</td>
            </tr>
            @endforeach
            @if($expenses->isEmpty())
            <tr><td colspan="6" class="text-center">No expenses recorded for the selected filters.</td></tr>
            @endif
        </tbody>
        @if($expenses->isNotEmpty())
        <tfoot>
            <tr>
                <td colspan="5" class="text-end fw-bold">Grand Total</td>
                <td class="text-end fw-bold">{{ number_format($expenses->sum('amount')) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>
@endsection
