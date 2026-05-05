<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gatekeeper Performance Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        .header { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #333; }
        .header img { height: 50px; float: left; }
        .header .title { float: right; text-align: right; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 5px 0 0 0; font-size: 10px; color: #666; }
        .clearfix::after { content: ""; display: table; clear: both; }
        
        .metrics { margin: 20px 0; }
        .metric-box { width: 23%; display: inline-block; padding: 10px; margin: 5px; background: #f8f9fa; border-radius: 5px; text-align: center; }
        .metric-box .label { font-size: 9px; text-transform: uppercase; color: #666; font-weight: bold; }
        .metric-box .value { font-size: 18px; font-weight: bold; margin-top: 5px; }
        
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th { background: #f8f9fa; padding: 8px; text-align: left; font-size: 9px; text-transform: uppercase; border-bottom: 2px solid #ddd; }
        td { padding: 6px 8px; border-bottom: 1px solid #eee; font-size: 10px; }
        tr:hover { background: #f8f9fa; }
        
        .section-title { font-size: 14px; font-weight: bold; margin: 20px 0 10px 0; padding-bottom: 5px; border-bottom: 1px solid #ddd; }
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 9px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header clearfix">
        <div style="float: left; width: 30%;">
            <div style="font-size: 24px; font-weight: bold; color: #333;">CHIBO</div>
            <div style="font-size: 10px; color: #666;">Performance Analytics</div>
        </div>
        <div class="title" style="float: right; width: 65%;">
            <h1>Gatekeeper Performance Report</h1>
            <p>Period: {{ $dateFrom->format('M d, Y') }} - {{ $dateTo->format('M d, Y') }}</p>
            <p>Generated: {{ now()->format('M d, Y H:i') }}</p>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="metrics clearfix">
        <div class="metric-box">
            <div class="label">Total Movements</div>
            <div class="value">{{ number_format($summary['total_movements']) }}</div>
        </div>
        <div class="metric-box">
            <div class="label">Incoming</div>
            <div class="value">{{ number_format($summary['total_in']) }}</div>
        </div>
        <div class="metric-box">
            <div class="metric-box">
            <div class="label">Outgoing</div>
            <div class="value">{{ number_format($summary['total_out']) }}</div>
        </div>
        <div class="metric-box">
            <div class="label">Active Staff</div>
            <div class="value">{{ number_format($summary['active_gatekeepers']) }}</div>
        </div>
    </div>

    <!-- Performance Rankings -->
    <div class="section-title">Gatekeeper Performance Rankings</div>
    <table>
        <thead>
            <tr>
                <th>Gatekeeper</th>
                <th class="text-center">Total Movements</th>
                <th class="text-center">Incoming</th>
                <th class="text-center">Outgoing</th>
                <th class="text-end">Impact %</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gatekeepers as $gatekeeper)
            @php
                $impact = $summary['total_movements'] > 0 ? round(($gatekeeper->total_movements / $summary['total_movements']) * 100, 1) : 0;
            @endphp
            <tr>
                <td><strong>{{ $gatekeeper->name }}</strong><br><small style="color: #666;">{{ $gatekeeper->phone }}</small></td>
                <td class="text-center">{{ $gatekeeper->total_movements }}</td>
                <td class="text-center">{{ $gatekeeper->movements_in }}</td>
                <td class="text-center">{{ $gatekeeper->movements_out }}</td>
                <td class="text-end"><strong>{{ $impact }}%</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Recent Activity -->
    <div class="section-title">Recent Activity (Last 50 Movements)</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Product</th>
                <th class="text-center">Qty</th>
                <th>Handler</th>
                <th>Gatekeeper</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentMovements as $movement)
            <tr>
                <td>{{ $movement->movement_date->format('M d, H:i') }}</td>
                <td>
                    @if($movement->type === 'in')
                        <span class="badge badge-success">IN</span>
                    @else
                        <span class="badge badge-warning">OUT</span>
                    @endif
                </td>
                <td>{{ $movement->product_name }}</td>
                <td class="text-center">{{ $movement->quantity }}</td>
                <td>{{ $movement->handler_name }}</td>
                <td>{{ $movement->gatekeeper->name ?? 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 20px; color: #999;">No movements found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>This report is computer-generated and contains confidential information.</p>
        <p>© {{ now()->year }} - All Rights Reserved</p>
    </div>
</body>
</html>
