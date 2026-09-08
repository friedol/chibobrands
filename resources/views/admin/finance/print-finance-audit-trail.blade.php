<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Audit Trail - Print Preview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 8.5pt; color: #1e293b; background: white; }
        .print-header { border-bottom: 2px solid #dc2626; padding-bottom: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end; }
        .company-title { font-size: 14pt; font-weight: 800; color: #dc2626; }
        .report-title { font-size: 11pt; font-weight: 700; color: #1e293b; }
        .table th { background-color: #f8fafc !important; color: #64748b !important; font-size: 7.5pt; font-weight: 700; text-transform: uppercase; padding: 6px 8px; border: 1px solid #e2e8f0; }
        .table td { font-size: 7.5pt; padding: 6px 8px; border: 1px solid #e2e8f0; vertical-align: middle; }
        .badge-created { background: #dcfce7; color: #166534; border: 1px solid #86efac; padding: 2px 6px; border-radius: 4px; font-weight: 700; font-size: 6.5pt; }
        .badge-updated { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; padding: 2px 6px; border-radius: 4px; font-weight: 700; font-size: 6.5pt; }
        .badge-deleted { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; padding: 2px 6px; border-radius: 4px; font-weight: 700; font-size: 6.5pt; }
        .badge-reconciled { background: #e0f2fe; color: #0c4a6e; border: 1px solid #7dd3fc; padding: 2px 6px; border-radius: 4px; font-weight: 700; font-size: 6.5pt; }
        .badge-adjusted { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; padding: 2px 6px; border-radius: 4px; font-weight: 700; font-size: 6.5pt; }
        .badge-waived { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 2px 6px; border-radius: 4px; font-weight: 700; font-size: 6.5pt; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
            .table th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    {{-- Top Action Bar --}}
    <div class="no-print py-1 px-3 bg-dark text-white d-flex justify-content-between align-items-center mb-3">
        <small class="fw-semibold"><i class="fas fa-print me-1 text-danger"></i>FINANCE AUDIT TRAIL — PRINT PREVIEW</small>
        <div class="d-flex gap-1">
            <button class="btn btn-danger btn-sm fw-bold px-3" onclick="window.print()">Print</button>
            <button class="btn btn-outline-light btn-sm px-2" onclick="window.close()">Close</button>
        </div>
    </div>

    <div class="px-4">
        {{-- Header --}}
        <div class="print-header">
            <div>
                <div class="company-title">CHIBOBRAND CO. LTD.</div>
                <div class="report-title">FINANCIAL AUDIT TRAIL REPORT</div>
            </div>
            <div class="text-end small">
                <div><strong>Printed On:</strong> {{ now()->format('d M Y, H:i') }} EAT</div>
                <div><strong>Source:</strong> System Security Logs</div>
            </div>
        </div>

        {{-- Table --}}
        <table class="table table-bordered table-striped w-100">
            <thead>
                <tr>
                    <th style="width: 100px;">Date &amp; Time</th>
                    <th style="width: 100px;">User</th>
                    <th style="width: 80px;">Action</th>
                    <th style="width: 120px;">Entity</th>
                    <th>Old Value</th>
                    <th>New Value</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                @php
                    $actClass = match($entry->action) {
                        'created' => 'badge-created',
                        'updated' => 'badge-updated',
                        'deleted' => 'badge-deleted',
                        'reconciled' => 'badge-reconciled',
                        'adjusted' => 'badge-adjusted',
                        'waived' => 'badge-waived',
                        default => 'badge-secondary'
                    };
                @endphp
                <tr>
                    <td>{{ $entry->created_at->format('d M Y') }}<br><small class="text-muted">{{ $entry->created_at->format('H:i:s') }}</small></td>
                    <td class="fw-bold">{{ $entry->user?->name ?? 'System' }}</td>
                    <td><span class="{{ $actClass }}">{{ $entry->action_label }}</span></td>
                    <td>
                        {{ ucwords(str_replace('_', ' ', $entry->entity_type)) }}
                        @if($entry->entity_id) #{{ $entry->entity_id }}@endif
                    </td>
                    <td style="word-break: break-all; max-width: 200px;">
                        @if($entry->old_value)
                            <small class="font-monospace text-muted">{{ json_encode($entry->old_value) }}</small>
                        @else — @endif
                    </td>
                    <td style="word-break: break-all; max-width: 200px;">
                        @if($entry->new_value)
                            <small class="font-monospace text-muted">{{ json_encode($entry->new_value) }}</small>
                        @else — @endif
                    </td>
                    <td>{{ $entry->reason }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">No audit trail logs recorded for this report.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</body>
</html>
