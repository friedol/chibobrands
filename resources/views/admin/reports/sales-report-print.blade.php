<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Leads Report — Print</title>
<style>
    * { box-sizing: border-box; }
    @media print { .no-print { display: none !important; } body { margin: 0; } }
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

    /* ── Target bar ── */
    .target-bar { display: table; width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 16px; }
    .target-item { display: table-cell; border: 1px solid #d1d5db; border-radius: 6px; padding: 12px 10px; text-align: center; background: #fafafa; vertical-align: middle; }
    .target-label { font-size: 9px; color: #6b7280; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 5px; }
    .target-value { font-size: 18px; font-weight: 900; }

    /* ── Tables ── */
    table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    thead th { background: #f3f4f6; font-size: 11px; font-weight: 700; text-align: left; padding: 9px 10px; border: 1px solid #e5e7eb; color: #374151; text-transform: none; }
    tbody td { padding: 8px 10px; border: 1px solid #e5e7eb; font-size: 11px; vertical-align: middle; }
    tbody tr:nth-child(even) td { background: #f9fafb; }
    tfoot td { border: 1px solid #e5e7eb; padding: 8px 10px; font-size: 11px; background: #f3f4f6; font-weight: 700; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }

    .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; }
    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-danger  { background: #fee2e2; color: #991b1b; }
    .badge-warning { background: #fef3c7; color: #92400e; }
    .rank-row td { font-weight: 600; }

    .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    .btn-print { position: fixed; right: 14px; top: 14px; padding: 8px 14px; border: 0; border-radius: 4px; background: #1d4ed8; color: #fff; cursor: pointer; font-size: 13px; font-weight: 700; }
</style>
</head>
<body>
    <button class="btn-print no-print" onclick="window.print()">🖨 Print</button>

    {{-- Header --}}
    <div class="header">
        @include('partials.logo-print')
        <div class="company-name">CHIBOBRAND CO. LTD</div>
        <div class="report-title">Leads Report</div>
        <div class="report-info">
            Generated on: {{ now()->format('M d, Y H:i') }}<br>
            Seller: <strong>{{ $sellerName }}</strong>
            &nbsp;|&nbsp; Period: <strong>{{ ucfirst($period) }}</strong>
            &nbsp;|&nbsp; {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}
        </div>
    </div>

    @if($target)
    <div class="section-title">Target Achievement</div>
    <div class="target-bar">
        <div class="target-item">
            <div class="target-label">Monthly Target</div>
            <div class="target-value">TZS {{ number_format($target->target_amount) }}</div>
        </div>
        <div class="target-item">
            <div class="target-label">Revenue Achieved</div>
            <div class="target-value" style="color:#15803d">TZS {{ number_format($data['totalRevenue']) }}</div>
        </div>
        <div class="target-item">
            @php $pct = $target->target_amount > 0 ? min(100, round(($data['totalRevenue'] / $target->target_amount) * 100, 1)) : 0; @endphp
            <div class="target-label">Performance</div>
            <div class="target-value" style="color:{{ $pct >= 100 ? '#15803d' : ($pct >= 70 ? '#b45309' : '#b91c1c') }}">{{ $pct }}%</div>
        </div>
    </div>
    @endif

    <div class="section-title">Performance Summary</div>
    <div class="stats-row">
        <div class="stats-cell">
            <div class="stats-label">Total Leads</div>
            <div class="stats-value">{{ number_format($data['totalLeads']) }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Won (Converted)</div>
            <div class="stats-value">{{ number_format($data['convertedLeads']) }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Pending Leads</div>
            <div class="stats-value">{{ number_format($data['pendingLeads']) }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Follow-Ups Done</div>
            <div class="stats-value">{{ number_format($data['followUpsDone']) }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Paid Clients</div>
            <div class="stats-value">{{ number_format($data['paidTasks']) }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Unpaid Clients</div>
            <div class="stats-value">{{ number_format($data['unpaidTasks']) }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">New Customers</div>
            <div class="stats-value">{{ number_format($data['newCustomerCount']) }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Repeat Customers</div>
            <div class="stats-value">{{ number_format($data['repCustomerCount']) }}</div>
        </div>
    </div>

    <div class="section-title">Revenue Analysis</div>
    <table>
        <thead><tr><th>Category</th><th class="text-right">Amount (TZS)</th></tr></thead>
        <tbody>
            <tr><td>Total Revenue Collected</td><td class="text-right" style="font-weight:700;">{{ number_format($data['totalRevenue']) }}</td></tr>
            <tr><td>New Customer Revenue</td><td class="text-right">{{ number_format($data['newRevenue']) }}</td></tr>
            <tr><td>Repeated Customer Revenue</td><td class="text-right">{{ number_format($data['repRevenue']) }}</td></tr>
            <tr><td>Total Billed (All Tasks)</td><td class="text-right">{{ number_format($data['totalBilled']) }}</td></tr>
        </tbody>
    </table>

    <div class="section-title">Lead Source Breakdown</div>
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
    <p style="color:#9ca3af;padding:16px 0;">No lead source data for this period.</p>
    @endif

    @if(!empty($ranking))
    <div class="section-title">Seller Rankings</div>
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
                <td class="text-right" style="color:#15803d">{{ number_format($s['revenue']) }}</td>
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

    <div class="section-title">Leads Registered</div>
    @if(!$data['registeredLeadsList']->isEmpty())
    <table>
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
            @foreach($data['registeredLeadsList'] as $lead)
            <tr>
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
    <p style="color:#9ca3af;font-style:italic;padding:16px 0;">No leads registered during this period.</p>
    @endif

    <div class="section-title">Paid Clients &amp; Tasks</div>
    @if(!$data['paidClientsList']->isEmpty())
    <table>
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
            @foreach($data['paidClientsList'] as $task)
            <tr>
                <td>{{ $task->created_at->format('d M Y, H:i') }}</td>
                <td style="font-weight:700;">{{ $task->customer?->name ?? 'Guest' }}</td>
                <td><code>{{ $task->task_code }}</code></td>
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
    <p style="color:#9ca3af;font-style:italic;padding:16px 0;">No paid tasks recorded for this period.</p>
    @endif

    <div class="section-title">Activities Done &amp; Comments</div>
    @if(!empty($data['activities']))
    <table>
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
            @foreach($data['activities'] as $act)
            <tr>
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
    <p style="color:#9ca3af;font-style:italic;padding:16px 0;">No follow-up activities recorded for this period.</p>
    @endif

    <div class="footer">
        &copy; {{ date('Y') }} CHIBOBRAND CO. LTD. All rights reserved. | Confidential Leads Report
    </div>
</body>
</html>
