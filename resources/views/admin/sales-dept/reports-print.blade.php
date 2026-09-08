<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report – {{ now()->format('d M Y') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #111; background: #fff; padding: 20px; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        .subtitle { color: #555; font-size: 11px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1e293b; color: #fff; padding: 7px 8px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em; }
        td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) td { background: #f8fafc; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .footer { margin-top: 24px; font-size: 10px; color: #888; }
        @media print { body { padding: 0; } }
    </style>
</head>
<body>
    <h1>Sales Department Report</h1>
    <div class="subtitle">
        Grouped by: <strong>{{ ucfirst($groupBy) }}</strong> &nbsp;|&nbsp;
        Period: <strong>{{ $dateFrom ?? '—' }} {{ $dateTo && $dateTo !== $dateFrom ? '→ ' . $dateTo : '' }}</strong> &nbsp;|&nbsp;
        Generated: {{ now()->format('d M Y, H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                @if($groupBy === 'product')
                    <th>Product / Service</th>
                    <th class="num">Qty</th>
                    <th class="num">Revenue (TZS)</th>
                @else
                    <th>{{ $groupBy === 'seller' ? 'Salesperson' : 'Department' }}</th>
                    <th class="num">Total Sales (TZS)</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($results as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                @if($groupBy === 'product')
                    <td>{{ $row->name }}</td>
                    <td class="num">{{ number_format($row->total_qty) }}</td>
                    <td class="num">{{ number_format($row->total_revenue) }}</td>
                @else
                    <td>{{ $row->name }}</td>
                    <td class="num">{{ number_format($row->design_tasks_sum_price ?? 0) }}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Chibo Brands Ltd &mdash; Sales Department Report &mdash; Printed {{ now()->format('d M Y') }}</div>

    <script>window.onload = function() { window.print(); };</script>
</body>
</html>
