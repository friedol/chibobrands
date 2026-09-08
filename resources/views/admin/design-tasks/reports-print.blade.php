<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            background: #fff;
            padding: 30px 40px;
        }

        /* ── Header ── */
        .header {
            text-align: center;
            margin-bottom: 24px;
            padding-bottom: 14px;
            border-bottom: 2.5px solid #1d4ed8;
        }
        .company-name {
            font-size: 22px;
            font-weight: 900;
            letter-spacing: .02em;
            color: #111;
            margin-bottom: 4px;
        }
        .report-title {
            font-size: 16px;
            color: #333;
            margin-bottom: 6px;
        }
        .report-info {
            font-size: 11px;
            color: #666;
            line-height: 1.7;
        }

        /* ── Section titles ── */
        .section-title {
            font-size: 14px;
            font-weight: 800;
            color: #1d4ed8;
            margin: 22px 0 10px;
            padding-bottom: 4px;
            border-bottom: 1px solid #dbeafe;
        }

        /* ── Stats grid ── */
        .stats-row {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 4px;
        }
        .stats-cell {
            display: table-cell;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 12px 10px;
            text-align: center;
            background: #fafafa;
            vertical-align: middle;
        }
        .stats-label {
            font-size: 9px;
            color: #6b7280;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 5px;
        }
        .stats-value {
            font-size: 22px;
            font-weight: 900;
            color: #111;
            line-height: 1;
        }

        /* ── Tables ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        thead th {
            background: #f3f4f6;
            font-size: 11px;
            font-weight: 700;
            text-align: left;
            padding: 9px 10px;
            border: 1px solid #e5e7eb;
            color: #374151;
        }
        tbody td {
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            font-size: 11px;
            vertical-align: middle;
        }
        tbody tr:nth-child(even) td { background: #f9fafb; }
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .fw-bold { font-weight: 700; }

        /* ── Status badges ── */
        .badge {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .badge-pending       { background: #fef3c7; color: #92400e; }
        .badge-in_progress   { background: #dbeafe; color: #1e40af; }
        .badge-in_review     { background: #e0e7ff; color: #3730a3; }
        .badge-printing      { background: #e0f2fe; color: #0369a1; }
        .badge-printed       { background: #d1fae5; color: #065f46; }
        .badge-completed     { background: #d1fae5; color: #065f46; }
        .badge-confirmed     { background: #d1fae5; color: #065f46; }
        .badge-super_completed { background: #d1fae5; color: #065f46; }
        .badge-delivered     { background: #bbf7d0; color: #14532d; }
        .badge-rejected      { background: #fee2e2; color: #991b1b; }
        .badge-cancelled     { background: #f3f4f6; color: #6b7280; }

        /* ── Task code sub-line ── */
        .task-code {
            font-size: 9px;
            color: #9ca3af;
            margin-top: 2px;
        }

        /* ── Footer ── */
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }

        @media print {
            body { padding: 15px 20px; }
            .no-print { display: none !important; }
            @page { margin: 1cm; }
        }
    </style>
</head>
<body>

    {{-- No-print print button --}}
    <div class="no-print" style="text-align:right; margin-bottom:12px;">
        <button onclick="window.print()" style="background:#1d4ed8;color:#fff;border:none;padding:7px 18px;border-radius:6px;font-size:12px;cursor:pointer;font-weight:700;">
            &#x1F5A8; Print / Save PDF
        </button>
        <button onclick="window.close()" style="background:#f3f4f6;color:#374151;border:1px solid #d1d5db;padding:7px 14px;border-radius:6px;font-size:12px;cursor:pointer;margin-left:6px;">
            Close
        </button>
    </div>

    {{-- Header --}}
    <div class="header">
        @php
            $__logoPrintPath = public_path('images/logo.png');
            if (!file_exists($__logoPrintPath)) $__logoPrintPath = public_path('images/logo.webp');
        @endphp
        @if(file_exists($__logoPrintPath))
            <img src="data:image/{{ pathinfo($__logoPrintPath, PATHINFO_EXTENSION) === 'webp' ? 'webp' : 'png' }};base64,{{ base64_encode(file_get_contents($__logoPrintPath)) }}"
                 alt="Chibobrand Logo"
                 style="max-height:64px;max-width:180px;object-fit:contain;margin-bottom:10px;">
        @endif
        <div class="company-name">CHIBOBRAND CO. LTD</div>
        <div class="report-title">{{ $title }}</div>
        <div class="report-info">
            Generated on: {{ now()->format('M d, Y H:i') }}<br>
            Period: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} – {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
            @if($designerName)
                &nbsp;|&nbsp; Designer: <strong>{{ $designerName }}</strong>
            @endif
        </div>
    </div>

    {{-- Performance Summary --}}
    <div class="section-title">Performance Summary</div>
    <div class="stats-row">
        <div class="stats-cell">
            <div class="stats-label">Total Tasks</div>
            <div class="stats-value">{{ $summary['total'] }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Completed</div>
            <div class="stats-value">{{ $summary['completed'] }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">In Progress</div>
            <div class="stats-value">{{ $summary['in_progress'] }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Awaiting</div>
            <div class="stats-value">{{ $summary['pending'] }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Compl. Rate</div>
            <div class="stats-value">{{ $summary['completion_rate'] }}%</div>
        </div>
    </div>

    {{-- Designer Breakdown --}}
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
            @foreach($tasksByDesigner as $d)
            <tr>
                <td class="fw-bold">{{ $d['designer']->name ?? 'N/A' }}</td>
                <td class="text-center">{{ $d['total'] }}</td>
                <td class="text-center">{{ $d['in_progress'] }}</td>
                <td class="text-center">{{ $d['completed'] }}</td>
                <td class="text-center">
                    {{ $d['total'] > 0 ? round(($d['completed'] / $d['total']) * 100, 1) : 0 }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Recent Design Tasks --}}
    <div class="section-title">Recent Design Tasks</div>
    <table>
        <thead>
            <tr>
                <th>Task Title</th>
                <th>Customer</th>
                <th>Designer</th>
                <th class="text-center">Status</th>
                <th class="text-right" style="white-space:nowrap;">Created</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
            <tr>
                <td>
                    <div class="fw-bold">{{ $task->title }}</div>
                    <div class="task-code">{{ $task->task_code }}</div>
                </td>
                <td>{{ $task->customer->name ?? 'N/A' }}</td>
                <td>{{ $task->designer->name ?? 'Unassigned' }}</td>
                <td class="text-center">
                    <span class="badge badge-{{ $task->status }}">
                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                    </span>
                </td>
                <td class="text-right" style="white-space:nowrap;">{{ $task->created_at->format('M d, Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center" style="color:#9ca3af;padding:16px;">No tasks in this period.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        &copy; {{ date('Y') }} CHIBOBRAND CO. LTD. All rights reserved.
    </div>

</body>
</html>
