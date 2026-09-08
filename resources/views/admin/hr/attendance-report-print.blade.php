<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report – {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #111; background: #fff; padding: 20px; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        .subtitle { color: #555; font-size: 11px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1e293b; color: #fff; padding: 7px 8px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em; }
        td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) td { background: #f8fafc; }
        .num { text-align: center; }
        .footer { margin-top: 24px; font-size: 10px; color: #888; }
        @media print { body { padding: 0; } }
    </style>
</head>
<body>
    <h1>Monthly Attendance Report</h1>
    <div class="subtitle">
        {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }} &nbsp;|&nbsp;
        Working Days: {{ $workingDays }} &nbsp;|&nbsp;
        Generated: {{ now()->format('d M Y, H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Department</th>
                <th class="num">Present</th>
                <th class="num">Absent</th>
                <th class="num">Late</th>
                <th class="num">On Leave</th>
                <th class="num">Total Hours</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $i => $emp)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $emp->full_name }}</td>
                <td>{{ $emp->department ?? '—' }}</td>
                <td class="num">{{ $emp->present }}</td>
                <td class="num">{{ $emp->absent }}</td>
                <td class="num">{{ $emp->late }}</td>
                <td class="num">{{ $emp->on_leave }}</td>
                <td class="num">{{ $emp->total_hours }}h</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Chibo Brands Ltd &mdash; HR Attendance Report &mdash; Printed {{ now()->format('d M Y') }}</div>

    <script>window.onload = function() { window.print(); };</script>
</body>
</html>
