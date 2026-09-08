@extends('admin.reports.exports.base')

@section('report_content')
    <div class="section-title">Audit Trail Entries ({{ $entries->count() }})</div>
    <table>
        <thead>
            <tr>
                <th>Date / Time</th>
                <th>User</th>
                <th>Action</th>
                <th>Entity</th>
                <th>Transaction Date</th>
                <th>Old Value</th>
                <th>New Value</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $entry)
            <tr>
                <td>{{ $entry->created_at->format('d M Y H:i:s') }}</td>
                <td class="fw-bold">{{ $entry->user?->name ?? 'System' }}</td>
                <td><span class="badge bg-{{ $entry->action_color }}">{{ $entry->action_label }}</span></td>
                <td>
                    {{ ucwords(str_replace('_', ' ', $entry->entity_type)) }}
                    @if($entry->entity_id)<span style="color:#666;">#{{ $entry->entity_id }}</span>@endif
                </td>
                <td>{{ $entry->transaction_date?->format('d M Y') ?? '—' }}</td>
                <td style="font-size: 9px; word-break: break-all;">
                    {{ $entry->old_value ? json_encode($entry->old_value) : '—' }}
                </td>
                <td style="font-size: 9px; word-break: break-all;">
                    {{ $entry->new_value ? json_encode($entry->new_value) : '—' }}
                </td>
                <td>{{ $entry->reason }}</td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center">No audit trail logs recorded for this period.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
