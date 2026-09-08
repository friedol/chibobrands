@extends('admin.reports.exports.base')

@section('report_content')
    <div class="section-title">Performance Summary</div>
    <table class="stats-grid">
        <tr>
            <td class="stats-card" style="width: 20%;">
                <div class="stats-label">Total Tasks</div>
                <div class="stats-value">{{ $summary['total'] }}</div>
            </td>
            <td class="stats-card" style="width: 20%;">
                <div class="stats-label">Completed</div>
                <div class="stats-value">{{ $summary['completed'] }}</div>
            </td>
            <td class="stats-card" style="width: 20%;">
                <div class="stats-label">In Progress</div>
                <div class="stats-value">{{ $summary['in_progress'] }}</div>
            </td>
            <td class="stats-card" style="width: 20%;">
                <div class="stats-label">Awaiting</div>
                <div class="stats-value">{{ $summary['pending'] }}</div>
            </td>
            <td class="stats-card" style="width: 20%;">
                <div class="stats-label">Compl. Rate</div>
                <div class="stats-value">{{ $summary['completion_rate'] }}%</div>
            </td>
        </tr>
    </table>

    @if($designerName)
        <div class="report-info" style="text-align:center; margin-bottom: 10px;">
            Designer: <strong>{{ $designerName }}</strong>
        </div>
    @endif

    @if($tasksByDesigner->count() > 0)
    <div class="section-title">Designer Breakdown</div>
    <table>
        <thead>
            <tr>
                <th>Designer</th>
                <th class="text-center">Tasks</th>
                <th class="text-center">In Progress</th>
                <th class="text-center">Completed</th>
                <th class="text-center">Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasksByDesigner as $designerData)
            <tr>
                <td class="fw-bold">{{ $designerData['designer']->name ?? 'N/A' }}</td>
                <td class="text-center">{{ $designerData['total'] }}</td>
                <td class="text-center">{{ $designerData['in_progress'] }}</td>
                <td class="text-center">{{ $designerData['completed'] }}</td>
                <td class="text-center">
                    {{ $designerData['total'] > 0 ? round(($designerData['completed'] / $designerData['total']) * 100, 1) : 0 }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="section-title">Recent Design Tasks</div>
    <table>
        <thead>
            <tr>
                <th>Task Title</th>
                <th>Customer</th>
                <th>Designer</th>
                <th class="text-center">Status</th>
                <th class="text-end">Created</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
            <tr>
                <td>
                    <div class="fw-bold">{{ $task->title }}</div>
                    <div style="font-size: 9px; color: #666;">{{ $task->task_code }}</div>
                </td>
                <td>{{ $task->customer->name ?? 'N/A' }}</td>
                <td>{{ $task->designer->name ?? 'Unassigned' }}</td>
                <td class="text-center">
                    <span class="badge badge-{{ $task->status }}">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                </td>
                <td class="text-end">{{ $task->created_at->format('M d, Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center" style="color:#999;">No tasks in this period.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
@endsection
