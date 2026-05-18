<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sales Report — Print</title>
<style>
    @media print { .no-print { display: none !important; } body { margin: 0; } }
    body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #222; padding: 20px; }
    h1 { font-size: 22px; font-weight: 800; margin: 0 0 2px; }
    .meta { color: #555; margin-bottom: 18px; font-size: 11px; }
    .kpi-row { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; }
    .kpi-box { border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; min-width: 140px; flex: 1; }
    .kpi-val { font-size: 22px; font-weight: 800; }
    .kpi-lbl { font-size: 9px; color: #888; text-transform: uppercase; letter-spacing: .5px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    th { background: #1e293b; color: #fff; padding: 7px 10px; font-size: 10px; text-transform: uppercase; }
    td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; }
    tr:nth-child(even) td { background: #f8fafc; }
    tfoot td { background: #1e293b; color: #fff; font-weight: 700; }
    .section { font-size: 14px; font-weight: 700; color: #1e293b; border-bottom: 3px solid #3b5bdb; padding-bottom: 4px; margin: 18px 0 10px; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; }
    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-danger  { background: #fee2e2; color: #991b1b; }
    .badge-warning { background: #fef3c7; color: #92400e; }
    .rank-row td { font-weight: 600; }
    .target-bar { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 12px; margin-bottom: 16px; display: flex; gap: 30px; }
    .target-item { flex: 1; text-align: center; }
    .target-label { font-size: 9px; color: #888; text-transform: uppercase; }
    .target-value { font-size: 18px; font-weight: 800; }
    .print-btn { position: fixed; top: 16px; right: 16px; padding: 8px 20px; background: #1e293b; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; }
</style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">🖨 Print</button>

    <h1>CHIBO BRANDS — Sales Report</h1>
    <div class="meta">
        Seller: <strong>{{ $sellerName }}</strong> &nbsp;|&nbsp;
        Period: <strong>{{ ucfirst($period) }}</strong> &nbsp;|&nbsp;
        {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }} &nbsp;|&nbsp;
        Printed: {{ now()->format('d M Y H:i') }}
    </div>

    @if($target)
    <div class="target-bar">
        <div class="target-item">
            <div class="target-label">Monthly Target</div>
            <div class="target-value">TZS {{ number_format($target->target_amount) }}</div>
        </div>
        <div class="target-item">
            <div class="target-label">Revenue Achieved</div>
            <div class="target-value" style="color:#16a34a">TZS {{ number_format($data['totalRevenue']) }}</div>
        </div>
        <div class="target-item">
            @php $pct = $target->target_amount > 0 ? min(100, round(($data['totalRevenue'] / $target->target_amount) * 100, 1)) : 0; @endphp
            <div class="target-label">Performance</div>
            <div class="target-value" style="color:{{ $pct >= 100 ? '#16a34a' : ($pct >= 70 ? '#d97706' : '#dc2626') }}">{{ $pct }}%</div>
        </div>
    </div>
    @endif

    <div class="section">Performance Summary</div>
    <div class="kpi-row">
        @foreach([
            'Total Leads' => [$data['totalLeads'], '#3b5bdb'],
            'Won (Converted)' => [$data['convertedLeads'], '#16a34a'],
            'Pending Leads' => [$data['pendingLeads'], '#d97706'],
            'Follow-Ups Done' => [$data['followUpsDone'], '#0891b2'],
            'Paid Clients' => [$data['paidTasks'], '#16a34a'],
            'Unpaid Clients' => [$data['unpaidTasks'], '#dc2626'],
            'New Customers' => [$data['newCustomerCount'], '#3b5bdb'],
            'Repeat Customers' => [$data['repCustomerCount'], '#0891b2'],
        ] as $lbl => [$val, $color])
        <div class="kpi-box">
            <div class="kpi-val" style="color:{{ $color }}">{{ number_format($val) }}</div>
            <div class="kpi-lbl">{{ $lbl }}</div>
        </div>
        @endforeach
    </div>

    <div class="section">Revenue Analysis</div>
    <table>
        <thead><tr><th>Category</th><th class="text-right">Amount (TZS)</th></tr></thead>
        <tbody>
            <tr><td>Total Revenue Collected</td><td class="text-right"><strong>{{ number_format($data['totalRevenue']) }}</strong></td></tr>
            <tr><td>New Customer Revenue</td><td class="text-right">{{ number_format($data['newRevenue']) }}</td></tr>
            <tr><td>Repeated Customer Revenue</td><td class="text-right">{{ number_format($data['repRevenue']) }}</td></tr>
            <tr><td>Total Billed (All Tasks)</td><td class="text-right">{{ number_format($data['totalBilled']) }}</td></tr>
        </tbody>
    </table>

    <div class="section">Lead Source Breakdown</div>
    @if(!empty($data['sourceStats']))
    <table>
        <thead>
            <tr>
                <th>Source</th>
                <th class="text-center">Total</th>
                <th class="text-center">Paid</th>
                <th class="text-center">Unpaid</th>
                <th class="text-right">Revenue (TZS)</th>
                <th class="text-center">Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['sourceStats'] as $src => $stat)
            @php $rate = $stat['total'] > 0 ? round(($stat['paid']/$stat['total'])*100,1) : 0; @endphp
            <tr>
                <td><strong>{{ ucfirst(str_replace('_',' ',$src)) }}</strong></td>
                <td class="text-center">{{ $stat['total'] }}</td>
                <td class="text-center"><span class="badge badge-success">{{ $stat['paid'] }}</span></td>
                <td class="text-center"><span class="badge badge-danger">{{ $stat['unpaid'] }}</span></td>
                <td class="text-right">{{ number_format($stat['revenue']) }}</td>
                <td class="text-center">{{ $rate }}%</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>TOTAL</td>
                <td class="text-center">{{ array_sum(array_column($data['sourceStats'],'total')) }}</td>
                <td class="text-center">{{ array_sum(array_column($data['sourceStats'],'paid')) }}</td>
                <td class="text-center">{{ array_sum(array_column($data['sourceStats'],'unpaid')) }}</td>
                <td class="text-right">{{ number_format(array_sum(array_column($data['sourceStats'],'revenue'))) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @else
    <p style="color:#888">No lead source data for this period.</p>
    @endif

    @if(!empty($ranking))
    <div class="section">Seller Rankings</div>
    <table>
        <thead>
            <tr>
                <th>#</th><th>Seller</th><th class="text-right">Revenue (TZS)</th>
                <th class="text-center">Leads</th><th class="text-center">Won</th><th class="text-center">Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ranking as $r => $s)
            <tr class="rank-row">
                <td>{{ $r + 1 }}</td>
                <td>{{ $s['name'] }}</td>
                <td class="text-right" style="color:#16a34a">{{ number_format($s['revenue']) }}</td>
                <td class="text-center">{{ $s['leads'] }}</td>
                <td class="text-center">{{ $s['converted'] }}</td>
                <td class="text-center">
                    <span class="badge {{ $s['rate'] >= 60 ? 'badge-success' : ($s['rate'] >= 30 ? 'badge-warning' : 'badge-danger') }}">
                        {{ $s['rate'] }}%
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div style="margin-top:30px; border-top:1px solid #e2e8f0; padding-top:8px; color:#999; font-size:9px;">
        CHIBO BRANDS LTD — Confidential Sales Report — Generated by CHIBO CRM
    </div>
</body>
</html>
