<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; margin: 0; padding: 20px; }
    h1 { font-size: 18px; margin: 0 0 4px; color: #1a1a2e; }
    .subtitle { color: #666; font-size: 10px; margin-bottom: 16px; }
    .kpi-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
    .kpi-box { border: 1px solid #ddd; border-radius: 6px; padding: 8px 12px; min-width: 130px; }
    .kpi-val { font-size: 20px; font-weight: bold; color: #1a1a2e; }
    .kpi-lbl { font-size: 9px; color: #888; text-transform: uppercase; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    th { background: #f0f4ff; padding: 6px 8px; text-align: left; font-size: 10px; text-transform: uppercase; border: 1px solid #ddd; }
    td { padding: 5px 8px; border: 1px solid #ddd; vertical-align: middle; }
    tr:nth-child(even) td { background: #fafafa; }
    tfoot td { background: #e8f0fe; font-weight: bold; }
    .section-title { font-size: 13px; font-weight: bold; color: #1a1a2e; border-bottom: 2px solid #3b5bdb; padding-bottom: 4px; margin: 14px 0 8px; }
    .badge-pill { display: inline-block; padding: 2px 7px; border-radius: 10px; font-size: 9px; font-weight: bold; }
    .bg-success { background: #d1fae5; color: #065f46; }
    .bg-danger  { background: #fee2e2; color: #991b1b; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .logo-area { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
</style>
</head>
<body>

<div class="logo-area">
    <div>
        <h1>CHIBO BRANDS — Sales Report</h1>
        <div class="subtitle">
            Seller: <strong>{{ $sellerName }}</strong> |
            Period: <strong>{{ ucfirst($period) }}</strong> |
            {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}
        </div>
    </div>
    <div style="text-align:right; color:#888; font-size:10px;">
        Generated: {{ now()->format('d M Y H:i') }}<br>
        By: {{ auth()->user()->name ?? 'System' }}
    </div>
</div>

@if($target)
<div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:6px; padding:10px; margin-bottom:14px;">
    <strong>Monthly Target:</strong> TZS {{ number_format($target->target_amount) }} &nbsp;|&nbsp;
    <strong>Achieved:</strong> TZS {{ number_format($data['totalRevenue']) }} &nbsp;|&nbsp;
    <strong>Performance:</strong>
    @php $pct = $target->target_amount > 0 ? min(100, round(($data['totalRevenue'] / $target->target_amount) * 100, 1)) : 0; @endphp
    {{ $pct }}%
</div>
@endif

<div class="section-title">Performance Summary</div>
<div class="kpi-grid">
    @foreach([
        'Total Leads' => $data['totalLeads'],
        'Converted' => $data['convertedLeads'],
        'Pending' => $data['pendingLeads'],
        'Follow-Ups' => $data['followUpsDone'],
        'Paid Clients' => $data['paidTasks'],
        'Unpaid Clients' => $data['unpaidTasks'],
        'New Customers' => $data['newCustomerCount'],
        'Repeated Customers' => $data['repCustomerCount'],
    ] as $label => $value)
    <div class="kpi-box">
        <div class="kpi-val">{{ number_format($value) }}</div>
        <div class="kpi-lbl">{{ $label }}</div>
    </div>
    @endforeach
</div>

<div class="section-title">Revenue Analysis</div>
<table>
    <tr>
        <th>Category</th>
        <th class="text-right">Amount (TZS)</th>
    </tr>
    <tr><td>Total Revenue Collected</td><td class="text-right">{{ number_format($data['totalRevenue']) }}</td></tr>
    <tr><td>New Customer Revenue</td><td class="text-right">{{ number_format($data['newRevenue']) }}</td></tr>
    <tr><td>Repeated Customer Revenue</td><td class="text-right">{{ number_format($data['repRevenue']) }}</td></tr>
    <tr><td>Total Billed (Tasks)</td><td class="text-right">{{ number_format($data['totalBilled']) }}</td></tr>
</table>

<div class="section-title">Lead Source Breakdown</div>
@if(!empty($data['sourceStats']))
<table>
    <thead>
        <tr>
            <th>Source</th>
            <th class="text-center">Total Leads</th>
            <th class="text-center">Paid</th>
            <th class="text-center">Unpaid</th>
            <th class="text-right">Revenue (TZS)</th>
            <th class="text-center">Conversion</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['sourceStats'] as $src => $stat)
        @php $rate = $stat['total'] > 0 ? round(($stat['paid']/$stat['total'])*100,1) : 0; @endphp
        <tr>
            <td><strong>{{ ucfirst(str_replace('_',' ',$src)) }}</strong></td>
            <td class="text-center">{{ $stat['total'] }}</td>
            <td class="text-center"><span class="badge-pill bg-success">{{ $stat['paid'] }}</span></td>
            <td class="text-center"><span class="badge-pill bg-danger">{{ $stat['unpaid'] }}</span></td>
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

<div style="margin-top:30px; border-top:1px solid #ddd; padding-top:8px; color:#999; font-size:9px;">
    CHIBO BRANDS LTD — Confidential Sales Report — Generated by CHIBO CRM System
</div>

</body>
</html>
