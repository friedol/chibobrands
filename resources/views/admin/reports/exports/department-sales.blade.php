@extends('admin.reports.exports.base')

@section('report_content')
    <div class="section-title">Target Achievement Summary</div>
    <table class="stats-grid">
        <tr>
            @foreach($reportData as $data)
            <td class="stats-card" style="width: {{ 100 / (count($reportData) + 1) }}%;">
                <div class="stats-label">{{ $data['display_name'] }}</div>
                <div class="stats-value">{{ $data['percentage'] }}%</div>
                <div style="font-size: 9px; color: #666; margin-top: 3px;">
                    {{ number_format($data['total_sales']) }} / {{ number_format($data['target_amount']) }}
                </div>
            </td>
            @endforeach
            <td class="stats-card" style="width: {{ 100 / (count($reportData) + 1) }}%;">
                <div class="stats-label">Overall Total</div>
                <div class="stats-value">{{ $overallPercentage }}%</div>
                <div style="font-size: 9px; color: #666; margin-top: 3px;">
                    {{ number_format($overallTotalSales) }} / {{ number_format($overallTotalTarget) }}
                </div>
            </td>
        </tr>
    </table>

    @foreach($reportData as $data)
    <div class="section-title">{{ $data['display_name'] }}</div>
    @if(!empty($data['target_note']))
        <div style="font-size: 9px; color: #888; margin: -6px 0 8px 0;">{{ $data['target_note'] }}</div>
    @endif
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">No</th>
                <th style="width: 20%;">Customer</th>
                <th style="width: 40%;">Task Description</th>
                <th class="text-center" style="width: 10%;">Qty</th>
                <th class="text-end" style="width: 25%;">Revenue (TZS)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['tasks'] as $index => $task)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $task['customer_name'] }}</td>
                <td>
                    <div class="fw-bold">{{ $task['title'] }}</div>
                    @if($task['description'])
                        <div style="font-size: 9px; color: #666;">{{ \Illuminate\Support\Str::limit($task['description'], 100) }}</div>
                    @endif
                </td>
                <td class="text-center">{{ number_format($task['qty']) }}</td>
                <td class="text-end">{{ number_format($task['price']) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">No sales recorded in this period.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3"></td>
                <td class="text-end fw-bold">TOTAL</td>
                <td class="text-end fw-bold">{{ number_format($data['total_sales']) }}</td>
            </tr>
        </tfoot>
    </table>
    @endforeach

    <div class="section-title">Grand Total</div>
    <table class="stats-grid">
        <tr>
            <td class="stats-card" style="width: 50%;">
                <div class="stats-label">Grand Total Revenue</div>
                <div class="stats-value">TZS {{ number_format($overallTotalSales) }}</div>
            </td>
            <td class="stats-card" style="width: 50%;">
                <div class="stats-label">Overall Achievement</div>
                <div class="stats-value">{{ $overallPercentage }}%</div>
            </td>
        </tr>
    </table>
@endsection
