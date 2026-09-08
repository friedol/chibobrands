<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Leads Report</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1a1a1a; background: #fff; margin: 0; padding: 20px 30px; }

    /* ── Header ── */
    .header { text-align: center; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 2.5px solid #1d4ed8; }
    .company-name { font-size: 20px; font-weight: 900; letter-spacing: .02em; color: #111; margin-bottom: 4px; }
    .report-title { font-size: 15px; color: #333; margin-bottom: 6px; }
    .report-info { font-size: 10px; color: #666; line-height: 1.7; }

    /* ── Section titles ── */
    .section-title { font-size: 13px; font-weight: 800; color: #1d4ed8; margin: 18px 0 8px; padding-bottom: 4px; border-bottom: 1px solid #dbeafe; }

    /* ── Stats grid — table-based for DomPDF ── */
    .stats-row { width: 100%; border-collapse: separate; border-spacing: 5px; margin-bottom: 4px; }
    .stats-cell { border: 1px solid #d1d5db; border-radius: 4px; padding: 10px 8px; text-align: center; background: #fafafa; vertical-align: middle; }
    .stats-label { font-size: 8px; color: #6b7280; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }
    .stats-value { font-size: 20px; font-weight: 900; color: #111; line-height: 1; }

    /* ── Target bar ── */
    .target-row { width: 100%; border-collapse: separate; border-spacing: 5px; margin-bottom: 14px; }
    .target-cell { border: 1px solid #d1d5db; border-radius: 4px; padding: 10px 8px; text-align: center; background: #fafafa; vertical-align: middle; }
    .target-label { font-size: 8px; color: #6b7280; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }
    .target-value { font-size: 16px; font-weight: 900; }

    /* ── Tables ── */
    table.data { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    table.data thead th { background: #f3f4f6; font-size: 10px; font-weight: 700; text-align: left; padding: 8px 9px; border: 1px solid #e5e7eb; color: #374151; }
    table.data tbody td { padding: 7px 9px; border: 1px solid #e5e7eb; font-size: 10px; vertical-align: middle; }
    table.data tbody tr.even td { background: #f9fafb; }
    table.data tfoot td { border: 1px solid #e5e7eb; padding: 7px 9px; font-size: 10px; background: #f3f4f6; font-weight: 700; }
    .text-right  { text-align: right; }
    .text-center { text-align: center; }

    .badge { display: inline-block; padding: 2px 7px; border-radius: 8px; font-size: 8px; font-weight: 700; }
    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-danger  { background: #fee2e2; color: #991b1b; }
    .badge-warning { background: #fef3c7; color: #92400e; }

    .footer { margin-top: 24px; text-align: center; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 8px; }
</style>
</head>
<body>

    {{-- ── HEADER ── --}}
    <div class="header">
        @php
            $logoPath = public_path('images/logo-pdf.png');
            if (!file_exists($logoPath)) $logoPath = public_path('images/logo.png');
        @endphp
        @if(file_exists($logoPath))
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" alt="Chibo Brands" style="max-height:60px;max-width:160px;margin-bottom:8px;">
        @endif
        <div class="company-name">CHIBOBRAND CO. LTD</div>
        <div class="report-title">Leads Report</div>
        <div class="report-info">
            Generated on: {{ now()->format('M d, Y H:i') }}<br>
            Seller: <strong>{{ $sellerName }}</strong>
            &nbsp;|&nbsp; Period: <strong>{{ ucfirst($period) }}</strong>
            &nbsp;|&nbsp; {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}
        </div>
    </div>

    {{-- ── TARGET ACHIEVEMENT ── --}}
    @if($target)
    <div class="section-title">Target Achievement</div>
    @php $pct = $target->target_amount > 0 ? min(100, round(($data['totalRevenue'] / $target->target_amount) * 100, 1)) : 0; @endphp
    <table class="target-row">
        <tr>
            <td class="target-cell">
                <div class="target-label">Monthly Target</div>
                <div class="target-value">TZS {{ number_format($target->target_amount) }}</div>
            </td>
            <td class="target-cell">
                <div class="target-label">Revenue Achieved</div>
                <div class="target-value" style="color:#15803d;">TZS {{ number_format($data['totalRevenue']) }}</div>
            </td>
            <td class="target-cell">
                <div class="target-label">Performance</div>
                <div class="target-value" style="color:{{ $pct >= 100 ? '#15803d' : ($pct >= 70 ? '#b45309' : '#b91c1c') }};">{{ $pct }}%</div>
            </td>
        </tr>
    </table>
    @endif

    {{-- ── PERFORMANCE SUMMARY ── --}}
    <div class="section-title">Performance Summary</div>
    <table class="stats-row">
        <tr>
            <td class="stats-cell">
                <div class="stats-label">Total Leads</div>
                <div class="stats-value">{{ number_format($data['totalLeads']) }}</div>
            </td>
            <td class="stats-cell">
                <div class="stats-label">Won (Converted)</div>
                <div class="stats-value">{{ number_format($data['convertedLeads']) }}</div>
            </td>
            <td class="stats-cell">
                <div class="stats-label">Pending Leads</div>
                <div class="stats-value">{{ number_format($data['pendingLeads']) }}</div>
            </td>
            <td class="stats-cell">
                <div class="stats-label">Follow-Ups Done</div>
                <div class="stats-value">{{ number_format($data['followUpsDone']) }}</div>
            </td>
        </tr>
    </table>
    <table class="stats-row">
        <tr>
            <td class="stats-cell">
                <div class="stats-label">Paid Clients</div>
                <div class="stats-value">{{ number_format($data['paidTasks']) }}</div>
            </td>
            <td class="stats-cell">
                <div class="stats-label">Unpaid Clients</div>
                <div class="stats-value">{{ number_format($data['unpaidTasks']) }}</div>
            </td>
            <td class="stats-cell">
                <div class="stats-label">New Customers</div>
                <div class="stats-value">{{ number_format($data['newCustomerCount']) }}</div>
            </td>
            <td class="stats-cell">
                <div class="stats-label">Repeat Customers</div>
                <div class="stats-value">{{ number_format($data['repCustomerCount']) }}</div>
            </td>
        </tr>
    </table>

    {{-- ── REVENUE ANALYSIS ── --}}
    <div class="section-title">Revenue Analysis</div>
    <table class="data">
        <thead><tr><th>Category</th><th class="text-right">Amount (TZS)</th></tr></thead>
        <tbody>
            <tr><td>Total Revenue Collected</td><td class="text-right" style="font-weight:700;">{{ number_format($data['totalRevenue']) }}</td></tr>
            <tr class="even"><td>New Customer Revenue</td><td class="text-right">{{ number_format($data['newRevenue']) }}</td></tr>
            <tr><td>Repeated Customer Revenue</td><td class="text-right">{{ number_format($data['repRevenue']) }}</td></tr>
            <tr class="even"><td>Total Billed (All Tasks)</td><td class="text-right">{{ number_format($data['totalBilled']) }}</td></tr>
        </tbody>
    </table>

    {{-- ── LEAD SOURCE BREAKDOWN ── --}}
    <div class="section-title">Lead Source Breakdown</div>
    @if(!empty($data['sourceStats']))
    <table class="data">
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
                <td style="font-weight:700;">{{ ucfirst(str_replace('_',' ',$src)) }}</td>
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
    <p style="color:#9ca3af;padding:12px 0;">No lead source data for this period.</p>
    @endif

    {{-- ── SELLER RANKINGS ── --}}
    @if(!empty($ranking))
    <div class="section-title">Seller Rankings</div>
    <table class="data">
        <thead>
            <tr>
                <th>#</th><th>Seller</th><th class="text-right">Revenue (TZS)</th>
                <th class="text-center">Leads</th><th class="text-center">Won</th><th class="text-center">Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ranking as $r => $s)
            <tr>
                <td>{{ $r + 1 }}</td>
                <td style="font-weight:600;">{{ $s['name'] }}</td>
                <td class="text-right" style="color:#15803d;">{{ number_format($s['revenue']) }}</td>
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

    {{-- ── LEADS REGISTERED ── --}}
    <div class="section-title">Leads Registered</div>
    @if(!$data['registeredLeadsList']->isEmpty())
    <table class="data">
        <thead>
            <tr>
                <th>Date Registered</th>
                <th>Lead Name</th>
                <th>Phone</th>
                <th>Source</th>
                <th>Interest</th>
                <th>Status</th>
                @if($sellerName === 'All Sellers')
                <th>Seller</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($data['registeredLeadsList'] as $i => $lead)
            <tr{{ $i % 2 !== 0 ? ' class="even"' : '' }}>
                <td>{{ $lead->created_at->format('d M Y, H:i') }}</td>
                <td style="font-weight:700;">{{ $lead->customer_name }}</td>
                <td>{{ $lead->phone }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $lead->source)) }}</td>
                <td>{{ ucfirst($lead->interest_level ?: '—') }}</td>
                <td>{{ $lead->status === 'converted' ? 'Won' : ($lead->status === 'not_interested' ? 'Lost' : ucfirst($lead->status)) }}</td>
                @if($sellerName === 'All Sellers')
                <td>{{ $lead->seller?->name ?? '—' }}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="color:#9ca3af;font-style:italic;padding:12px 0;">No leads registered during this period.</p>
    @endif

    {{-- ── PAID CLIENTS & TASKS ── --}}
    <div class="section-title">Paid Clients &amp; Tasks</div>
    @if(!$data['paidClientsList']->isEmpty())
    <table class="data">
        <thead>
            <tr>
                <th>Task Date</th>
                <th>Customer Name</th>
                <th>Task Code</th>
                <th>Department</th>
                <th>Design Details</th>
                <th class="text-right">Price</th>
                @if($sellerName === 'All Sellers')
                <th>Seller</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($data['paidClientsList'] as $i => $task)
            <tr{{ $i % 2 !== 0 ? ' class="even"' : '' }}>
                <td>{{ $task->created_at->format('d M Y, H:i') }}</td>
                <td style="font-weight:700;">{{ $task->customer?->name ?? 'Guest' }}</td>
                <td style="font-family:monospace;font-size:9px;">{{ $task->task_code }}</td>
                <td>{{ $task->department?->name ?? 'POS' }}</td>
                <td>{{ $task->title }} ({{ $task->quantity }} pcs)</td>
                <td class="text-right" style="color:#15803d;font-weight:700;">{{ number_format($task->price) }}</td>
                @if($sellerName === 'All Sellers')
                <td>{{ $task->saler?->name ?? '—' }}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="color:#9ca3af;font-style:italic;padding:12px 0;">No paid tasks recorded for this period.</p>
    @endif

    {{-- ── ACTIVITIES DONE & COMMENTS ── --}}
    <div class="section-title">Activities Done &amp; Comments</div>
    @if(!empty($data['activities']))
    <table class="data">
        <thead>
            <tr>
                <th>Date &amp; Time</th>
                <th>Type</th>
                <th>Contact</th>
                <th>Phone</th>
                <th>Channel</th>
                <th>Comments / Notes</th>
                @if($sellerName === 'All Sellers')
                <th>Seller</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($data['activities'] as $i => $act)
            <tr{{ $i % 2 !== 0 ? ' class="even"' : '' }}>
                <td>{{ \Carbon\Carbon::parse($act['date'])->format('d M Y, H:i') }}</td>
                <td>{{ $act['type'] }}</td>
                <td style="font-weight:700;">{{ $act['contact_name'] }}</td>
                <td>{{ $act['phone'] }}</td>
                <td>{{ $act['channel'] }}</td>
                <td>{{ $act['notes'] ?: '—' }}</td>
                @if($sellerName === 'All Sellers')
                <td>{{ $act['seller_name'] }}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="color:#9ca3af;font-style:italic;padding:12px 0;">No follow-up activities recorded for this period.</p>
    @endif

    <div class="footer">
        &copy; {{ date('Y') }} CHIBOBRAND CO. LTD. All rights reserved. | Confidential Leads Report
    </div>

</body>
</html>
