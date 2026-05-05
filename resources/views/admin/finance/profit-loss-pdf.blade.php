<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Profit & Loss – {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d M Y') : 'All time' }} – {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d M Y') : '' }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1e293b; margin: 0; padding: 14px; }
        .header { margin-bottom: 14px; padding-bottom: 10px; border-bottom: 2px solid #1e293b; overflow: hidden; }
        .header-left { float: left; }
        .header-right { float: right; text-align: right; }
        h1 { margin: 0; font-size: 16px; font-weight: bold; }
        .sub { margin: 2px 0 0 0; font-size: 8.5px; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #94a3b8; padding: 4px 6px; text-align: left; }
        th { background: #f1f5f9; color: #334155; font-size: 8px; text-transform: uppercase; font-weight: bold; }
        td.num { text-align: right; }
        .muted { color: #64748b; }
        .profit { color: #16a34a; }
        .loss { color: #dc2626; }
        tr.total td { background: #e2e8f0; font-weight: bold; }
        .net-row td { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <h1>Profit and Loss</h1>
            <p class="sub">CHIBOBRAND CO. LTD. | Dar es Salaam, Tanzania</p>
        </div>
        <div class="header-right">
            <div style="font-size: 10px; font-weight: bold;">Basis</div>
            <div class="sub" style="color:#0f172a; font-weight:bold;">{{ $basisLabel ?? 'Accrual' }}</div>
            <div class="sub" style="margin-top:6px;">
                Period:
                @if($dateFrom && $dateTo)
                    {{ $dateFrom->format('d M Y') }} – {{ $dateTo->format('d M Y') }}
                @else
                    All time
                @endif
            </div>
            <div class="sub">Base currency: <strong>TZS</strong></div>
        </div>
    </div>

    @php
        $net = (float) ($netProfitLoss ?? 0);
        $isLoss = $net < 0;
        $lossOrProfitLabel = $isLoss ? 'Net Loss' : 'Net Profit';
    @endphp

    <table>
        <thead>
            <tr>
                <th>Account</th>
                <th class="num">Amount (TZS)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Operating Income</td>
                <td class="num">{{ number_format($operatingIncome ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="muted">Sales</td>
                <td class="num">{{ number_format($sales ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="muted">Discount</td>
                <td class="num loss">{{ number_format($discount ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Total Operating Income</td>
                <td class="num">{{ number_format($operatingIncome ?? 0, 2) }}</td>
            </tr>

            <tr>
                <td>Cost of Goods Sold</td>
                <td class="num">{{ number_format($cogs ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Gross Profit</td>
                <td class="num {{ ($grossProfit ?? 0) >= 0 ? 'profit' : 'loss' }}">{{ number_format($grossProfit ?? 0, 2) }}</td>
            </tr>

            <tr>
                <td>Operating Expense</td>
                <td class="num loss">{{ number_format($operatingExpense ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Operating Profit</td>
                <td class="num {{ ($operatingProfit ?? 0) >= 0 ? 'profit' : 'loss' }}">{{ number_format($operatingProfit ?? 0, 2) }}</td>
            </tr>

            <tr>
                <td>Non Operating Income</td>
                <td class="num">0.00</td>
            </tr>
            <tr>
                <td>Non Operating Expense</td>
                <td class="num loss">0.00</td>
            </tr>

            <tr class="net-row">
                <td>{{ $lossOrProfitLabel }}</td>
                <td class="num {{ $isLoss ? 'loss' : 'profit' }}">{{ number_format($net, 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>

