<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operator Performance Report - CHIBOBRAND</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #1a1a1a; background: #fff; padding: 30px 40px; }

        /* ── Header ── */
        .header { text-align: center; margin-bottom: 24px; padding-bottom: 14px; border-bottom: 2.5px solid #1d4ed8; }
        .company-name { font-size: 22px; font-weight: 900; letter-spacing: .02em; color: #111; margin-bottom: 4px; }
        .report-title { font-size: 16px; color: #333; margin-bottom: 6px; }
        .report-info { font-size: 11px; color: #666; line-height: 1.7; }

        /* ── Section titles ── */
        .section-title { font-size: 14px; font-weight: 800; color: #1d4ed8; margin: 22px 0 10px; padding-bottom: 4px; border-bottom: 1px solid #dbeafe; }

        /* ── Stats grid ── */
        .stats-row { display: table; width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 4px; }
        .stats-cell { display: table-cell; border: 1px solid #d1d5db; border-radius: 6px; padding: 12px 10px; text-align: center; background: #fafafa; vertical-align: middle; }
        .stats-label { font-size: 9px; color: #6b7280; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 5px; }
        .stats-value { font-size: 22px; font-weight: 900; color: #111; line-height: 1; }

        /* ── Tables ── */
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead th { background: #f3f4f6; font-size: 11px; font-weight: 700; text-align: left; padding: 9px 10px; border: 1px solid #e5e7eb; color: #374151; }
        tbody td { padding: 8px 10px; border: 1px solid #e5e7eb; font-size: 11px; vertical-align: middle; }
        tbody tr:nth-child(even) td { background: #f9fafb; }
        .text-center { text-align: center; }
        .text-end, .text-right { text-align: right; }
        .fw-bold { font-weight: 700; }
        .text-success { color: #15803d; }
        .text-warning { color: #b45309; }
        .text-danger  { color: #b91c1c; }
        .text-dark    { color: #111827; }
        .text-muted   { color: #9ca3af; }

        .badge-status { font-size: 9px; font-weight: 700; padding: 2px 8px; border-radius: 10px; display: inline-block; }
        .badge-completed { background: #d1fae5; color: #065f46; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-overdue { background: #fee2e2; color: #991b1b; }

        /* ── Signature / Footer ── */
        .signature-row { display: table; width: 100%; margin-top: 40px; font-size: 10px; color: #4b5563; }
        .signature-row .line { display: table-cell; border-top: 1px solid #9ca3af; width: 45%; padding-top: 4px; text-align: center; }
        .signature-row .line-spacer { display: table-cell; width: 10%; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }

        .btn-print { position: fixed; right: 14px; top: 14px; padding: 8px 14px; border: 0; border-radius: 4px; background: #1d4ed8; color: #fff; cursor: pointer; font-size: 13px; font-weight: 700; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 15px 20px; }
            @page { margin: 1cm; }
        }
    </style>
</head>
<body>

    @unless($isPdf ?? false)
    <button class="btn-print no-print" onclick="window.print()">🖨 Print</button>
    @endunless

    {{-- Header --}}
    <div class="header">
        @include('partials.logo-print')
        <div class="company-name">CHIBOBRAND CO. LTD</div>
        <div class="report-title">Operator Performance &amp; Audit Report</div>
        <div class="report-info">
            Generated on: {{ now()->format('M d, Y H:i') }}<br>
            Period: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} – {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
        </div>
    </div>

    {{-- Performance Summary --}}
    <div class="section-title">Executive Performance Summary</div>
    <div class="stats-row">
        <div class="stats-cell">
            <div class="stats-label">Total Assigned</div>
            <div class="stats-value">{{ number_format($summary['total_assigned']) }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Completed</div>
            <div class="stats-value">{{ number_format($summary['total_completed']) }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Completion Rate</div>
            <div class="stats-value">{{ $summary['completion_rate'] }}%</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Pending</div>
            <div class="stats-value">{{ number_format($summary['total_pending']) }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Overdue</div>
            <div class="stats-value">{{ number_format($summary['total_overdue']) }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Revenue Managed</div>
            <div class="stats-value">{{ number_format($summary['total_revenue']) }}</div>
        </div>
    </div>

    {{-- Operator Breakdown --}}
    <div class="section-title">Operator Work Breakdown &amp; Efficiency Rankings</div>
    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Operator Name</th>
                <th class="text-center">Assigned</th>
                <th class="text-center">Completed</th>
                <th class="text-center">Pending</th>
                <th class="text-center">Overdue</th>
                <th class="text-center">Completion Rate</th>
                <th class="text-end">Revenue Managed</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasksByOperator as $op)
            <tr>
                <td class="fw-bold text-dark">{{ $op['operator']->name ?? 'Unassigned' }}</td>
                <td class="text-center fw-bold">{{ number_format($op['assigned']) }}</td>
                <td class="text-center text-success fw-bold">{{ number_format($op['completed']) }}</td>
                <td class="text-center text-warning fw-bold">{{ number_format($op['pending']) }}</td>
                <td class="text-center text-danger fw-bold">{{ number_format($op['overdue']) }}</td>
                <td class="text-center fw-bold {{ $op['efficiency'] >= 80 ? 'text-success' : ($op['efficiency'] >= 50 ? 'text-warning' : 'text-danger') }}">
                    {{ number_format($op['efficiency'], 1) }}%
                </td>
                <td class="text-end fw-bold">TZS {{ number_format($op['revenue']) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="color:#9ca3af;padding:16px;">No operator task activity recorded for this period.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Detailed Task Audit List --}}
    <div class="section-title">Detailed Task Verification Audit List</div>
    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Task Code</th>
                <th style="width: 24%;">Task Title</th>
                <th style="width: 18%;">Customer</th>
                <th style="width: 16%;">Operator</th>
                <th style="width: 12%;">Assigned Date</th>
                <th style="width: 10%;">Completion/Due</th>
                <th class="text-end" style="width: 8%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $t)
            @php
                $isCompleted = in_array($t->status, [
                    \App\Models\DesignTask::STATUS_COMPLETED,
                    \App\Models\DesignTask::STATUS_CONFIRMED,
                    \App\Models\DesignTask::STATUS_PRINTED,
                    \App\Models\DesignTask::STATUS_SUPER_COMPLETED,
                    \App\Models\DesignTask::STATUS_DELIVERED
                ]);
                $isOverdue = $t->deadline && $t->deadline->lt(now()) && !$isCompleted && $t->status !== \App\Models\DesignTask::STATUS_CANCELLED;
            @endphp
            <tr>
                <td><code>{{ $t->task_code ?? "TASK-{$t->id}" }}</code></td>
                <td class="fw-bold text-dark">{{ $t->title }}</td>
                <td>{{ $t->customer->name ?? 'N/A' }}</td>
                <td>{{ $t->operator->name ?? 'Unassigned' }}</td>
                <td>{{ $t->created_at->format('d M Y') }}</td>
                <td>
                    @if($isCompleted && $t->completed_at)
                        {{ $t->completed_at->format('d M Y') }}
                    @elseif($t->deadline)
                        {{ $t->deadline->format('d M Y') }}
                    @else
                        —
                    @endif
                </td>
                <td class="text-end">
                    @if($isCompleted)
                        <span class="badge-status badge-completed">Completed</span>
                    @elseif($isOverdue)
                        <span class="badge-status badge-overdue">Overdue</span>
                    @else
                        <span class="badge-status badge-pending">{{ ucfirst(str_replace('_', ' ', $t->status)) }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="color:#9ca3af;padding:16px;">No task records found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Sign-Off Footer --}}
    <div class="signature-row">
        <div class="line">Operations Supervisor Signature &amp; Date</div>
        <div class="line-spacer"></div>
        <div class="line">Head of HR / General Manager Approval</div>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} CHIBOBRAND CO. LTD. All rights reserved.
    </div>

</body>
</html>
