<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $singleSaler ? $singleSaler['name'] . ' — Performance Report' : 'Sales Performance Report' }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        background: #fff;
        color: #333;
        font-size: 8.5pt;
        line-height: 1.4;
    }
    .page { padding: 15mm 15mm 25mm 15mm; }

    /* ── Header ── */
    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
    .header-table td { vertical-align: top; padding: 0; }
    .company-name { font-size: 14pt; font-weight: 700; color: #dc2626; margin: 6px 0 3px 0; }
    .company-meta { font-size: 8.5pt; color: #555; line-height: 1.6; }
    .report-title { font-size: 22pt; font-weight: 800; color: #dc2626; text-align: right; letter-spacing: -1px; text-transform: uppercase; }
    .report-sub   { font-size: 9pt; color: #777; text-align: right; margin-top: 3px; }
    .grand-label  { font-size: 8pt; color: #888; text-transform: uppercase; font-weight: 600; text-align: right; margin-top: 8px; }
    .grand-amount { font-size: 15pt; font-weight: 800; color: #dc2626; text-align: right; }

    /* ── Divider ── */
    .divider { border: none; border-top: 2px solid #dc2626; margin: 10px 0 14px 0; }

    /* ── Meta row ── */
    .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
    .meta-table td { vertical-align: top; padding: 0; }
    .meta-label  { font-size: 8pt; color: #888; font-weight: 600; text-transform: uppercase; margin-bottom: 2px; }
    .meta-value  { font-size: 10pt; font-weight: 700; color: #111; }
    .meta-right  { text-align: right; }
    .meta-detail { font-size: 8.5pt; color: #555; line-height: 1.7; }

    /* ── Summary cards ── */
    .summary-cards { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
    .summary-cards td { width: 25%; padding: 8px 10px; border: 1px solid #e5e7eb; background: #fafafa; text-align: center; }
    .s-label { font-size: 7.5pt; color: #888; text-transform: uppercase; font-weight: 600; margin-bottom: 3px; }
    .s-value { font-size: 12pt; font-weight: 800; }
    .s-blue   { color: #2563eb; }
    .s-green  { color: #16a34a; }
    .s-red    { color: #dc2626; }
    .s-teal   { color: #0891b2; }

    /* ── Section title ── */
    .section-title {
        font-size: 8pt; font-weight: 700; color: #fff;
        background: #1e293b;
        padding: 5px 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0;
    }

    /* ── Performance table ── */
    table.perf { width: 100%; border-collapse: collapse; }
    table.perf thead tr { background: #dc2626; }
    table.perf thead th {
        color: #fff; font-size: 8.5pt; font-weight: 700;
        padding: 7px 8px; border: none; text-align: left;
    }
    table.perf thead th.text-right { text-align: right; }
    table.perf thead th.text-center { text-align: center; }
    table.perf tbody tr { border-bottom: 1px solid #eee; }
    table.perf tbody tr.even { background: #fafafa; }
    table.perf tbody tr.rank-1 { background: #fefce8; }
    table.perf tbody tr.rank-2 { background: #f0fdf4; }
    table.perf tbody tr.rank-3 { background: #eff6ff; }
    table.perf tbody td { padding: 7px 8px; font-size: 8.5pt; vertical-align: middle; }
    table.perf tbody td.text-right  { text-align: right; }
    table.perf tbody td.text-center { text-align: center; }
    table.perf tfoot td { padding: 6px 8px; font-size: 9pt; font-weight: 700; border-top: 2px solid #ccc; background: #f1f5f9; }
    table.perf tfoot td.text-right { text-align: right; }

    /* Rank badge */
    .badge { display: inline-block; width: 18px; height: 18px; border-radius: 50%; text-align: center; font-size: 7.5pt; font-weight: 800; line-height: 18px; }
    .b1 { background: #fbbf24; color: #78350f; }
    .b2 { background: #94a3b8; color: #1e293b; }
    .b3 { background: #cd7c35; color: #fff; }
    .bn { background: #e2e8f0; color: #475569; }

    .saler-name { font-weight: 700; color: #111; }
    .saler-role { font-size: 7.5pt; color: #999; text-transform: capitalize; }

    /* Achievement bar */
    .bar-wrap { display: inline-block; width: 50px; height: 5px; background: #e2e8f0; border-radius: 3px; vertical-align: middle; margin-right: 3px; }
    .bar-fill { display: inline-block; height: 5px; border-radius: 3px; }
    .bar-good { background: #10b981; }
    .bar-warn { background: #f59e0b; }
    .bar-low  { background: #ef4444; }
    .ach-pct  { font-size: 8pt; font-weight: 700; }

    /* Financial summary box */
    .fin-wrap { width: 100%; border-collapse: collapse; margin-top: 12px; }
    .fin-wrap td { padding: 0; }
    .fin-box { width: 280px; float: right; border: 1px solid #e5e7eb; }
    .fin-row { width: 100%; border-collapse: collapse; }
    .fin-row td { padding: 6px 10px; font-size: 9pt; border-bottom: 1px solid #eee; }
    .fin-row td:last-child { text-align: right; font-weight: 600; }
    .fin-total { background: #dc2626; }
    .fin-total td { color: #fff; font-weight: 700; font-size: 10pt; border-bottom: none; }
    .fin-total td:last-child { text-align: right; font-weight: 700; }

    /* History & task list tables */
    table.sub { width: 100%; border-collapse: collapse; }
    table.sub thead tr { background: #f1f5f9; }
    table.sub thead th { padding: 6px 8px; font-size: 8pt; font-weight: 700; color: #374151; border-bottom: 2px solid #e5e7eb; text-align: left; }
    table.sub thead th.text-right  { text-align: right; }
    table.sub thead th.text-center { text-align: center; }
    table.sub tbody tr { border-bottom: 1px solid #f0f0f0; }
    table.sub tbody tr.even { background: #fafafa; }
    table.sub tbody td { padding: 5px 8px; font-size: 8.5pt; }
    table.sub tbody td.text-right  { text-align: right; }
    table.sub tbody td.text-center { text-align: center; }

    /* Status pills */
    .pill { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 7pt; font-weight: 700; text-transform: capitalize; }
    .p-completed { background: #d1fae5; color: #065f46; }
    .p-pending   { background: #fef3c7; color: #92400e; }
    .p-in_progress { background: #dbeafe; color: #1e40af; }
    .p-rejected  { background: #fee2e2; color: #991b1b; }
    .p-cancelled { background: #f3f4f6; color: #6b7280; }

    /* Footer */
    .footer { margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px; width: 100%; border-collapse: collapse; }
    .footer td { vertical-align: top; font-size: 8pt; color: #555; }
    .footer .sig-line { border-top: 1px solid #aaa; width: 150px; margin-top: 25px; }
    .footer .sig-label { font-size: 7.5pt; color: #999; margin-top: 3px; }
    .footer .company-r { text-align: right; }
    .footer .company-r .cname { font-size: 9pt; font-weight: 700; color: #111; text-transform: uppercase; margin-bottom: 4px; }

    .spacer { height: 12px; }
</style>
</head>
<body>
<div class="page">

    {{-- ── HEADER ── --}}
    @php
        $logoPath = public_path('images/logo-pdf.png');
        if (!file_exists($logoPath)) $logoPath = public_path('images/logo.png');
    @endphp
    <table class="header-table">
        <tr>
            <td style="width:55%;">
                @if(file_exists($logoPath))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" alt="Chibo Brands" style="max-height:55px;max-width:150px;margin-bottom:6px;display:block;">
                @endif
                <div class="company-name">Chibo Brands Co Ltd</div>
                <div class="company-meta">
                    Kinondoni Dar es Salaam 14108, Tanzania<br>
                    +255 753 883 382 &nbsp;|&nbsp; www.chibobrands.com
                </div>
            </td>
            <td style="width:45%; text-align:right;">
                <div class="report-title">{{ $singleSaler ? 'Performance' : 'Sales Report' }}</div>
                @if($singleSaler)
                    <div class="report-sub">{{ $singleSaler['name'] }}</div>
                @endif
                <div class="report-sub">{{ $periodLabel }}</div>
                <div class="grand-label">Grand Total Sales</div>
                <div class="grand-amount">TZS {{ number_format($grandTotal) }}</div>
            </td>
        </tr>
    </table>
    <hr class="divider">

    {{-- ── META ── --}}
    <table class="meta-table">
        <tr>
            <td style="width:50%;">
                <div class="meta-label">Prepared by</div>
                <div class="meta-value">{{ auth()->user()->name }}</div>
                <div style="font-size:8pt;color:#888;">{{ ucfirst(auth()->user()->role) }}</div>
            </td>
            <td style="width:50%; text-align:right;">
                <div class="meta-detail">
                    <strong>Report Date:</strong> {{ now()->format('d M Y') }}<br>
                    <strong>Period:</strong> {{ $periodLabel }}<br>
                    <strong>Total Salers:</strong> {{ $salersPdf->count() }}<br>
                    <strong>Generated at:</strong> {{ now()->format('H:i') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- ── SUMMARY CARDS ── --}}
    <table class="summary-cards">
        <tr>
            <td>
                <div class="s-label">Customers</div>
                <div class="s-value s-teal">{{ number_format($summary['total_customers'] ?? 0) }}</div>
            </td>
            <td>
                <div class="s-label">Design Tasks</div>
                <div class="s-value s-blue">{{ number_format($summary['total_tasks'] ?? $salersPdf->sum('task_count')) }}</div>
            </td>
            <td>
                <div class="s-label">Total Revenue</div>
                <div class="s-value s-green">TZS {{ number_format($grandTotal) }}</div>
            </td>
            <td>
                <div class="s-label">Balance Due</div>
                <div class="s-value s-red">TZS {{ number_format($summary['balance_due'] ?? 0) }}</div>
            </td>
        </tr>
    </table>

    {{-- ── PERFORMANCE TABLE ── --}}
    @if($salersPdf->isEmpty())
        <p style="text-align:center;color:#999;padding:30px 0;">No sales performance data for this period.</p>
    @else
    <div class="section-title">Staff Performance Rankings</div>
    @php $grandTasks = $salersPdf->sum('task_count'); @endphp
    <table class="perf">
        <thead>
            <tr>
                <th style="width:28px;">#</th>
                <th>Salesperson</th>
                <th class="text-center" style="width:55px;">Tasks</th>
                <th class="text-right" style="width:130px;">Total Sales (TZS)</th>
                <th class="text-right" style="width:115px;">Target (TZS)</th>
                <th class="text-center" style="width:80px;">Achievement</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salersPdf as $i => $saler)
            @php
                $rank = $i + 1;
                $ach  = min($saler['achievement'], 100);
                $pClass = $ach >= 100 ? 'bar-good' : ($ach >= 60 ? 'bar-warn' : 'bar-low');
                $achColor = $ach >= 100 ? '#10b981' : ($ach >= 60 ? '#f59e0b' : '#ef4444');
                $rowClass = $rank === 1 ? 'rank-1' : ($rank === 2 ? 'rank-2' : ($rank === 3 ? 'rank-3' : ($i % 2 === 0 ? '' : 'even')));
            @endphp
            <tr class="{{ $rowClass }}">
                <td class="text-center">
                    <span class="badge {{ $rank === 1 ? 'b1' : ($rank === 2 ? 'b2' : ($rank === 3 ? 'b3' : 'bn')) }}">{{ $rank }}</span>
                </td>
                <td>
                    <div class="saler-name">{{ $saler['name'] }}</div>
                    <div class="saler-role">{{ ucfirst($saler['role']) }}</div>
                </td>
                <td class="text-center">{{ number_format($saler['task_count']) }}</td>
                <td class="text-right" style="font-weight:700;">{{ number_format($saler['total_sales']) }}</td>
                <td class="text-right">
                    @if($saler['target_amount'] > 0)
                        {{ number_format($saler['target_amount']) }}
                    @else
                        <span style="color:#bbb;">—</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($saler['target_amount'] > 0)
                        <span class="ach-pct" style="color:{{ $achColor }};">{{ number_format($saler['achievement'], 1) }}%</span>
                    @else
                        <span style="color:#bbb;font-size:7.5pt;">No target</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td><strong>TOTAL</strong></td>
                <td class="text-center"><strong>{{ number_format($grandTasks) }}</strong></td>
                <td class="text-right"><strong>TZS {{ number_format($grandTotal) }}</strong></td>
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    {{-- ── FINANCIAL SUMMARY ── --}}
    <div class="spacer"></div>
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="width:55%;"></td>
            <td style="width:45%;">
                <table style="width:100%;border-collapse:collapse;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="padding:6px 10px;font-size:8.5pt;border-bottom:1px solid #eee;">Total Design Tasks</td>
                        <td style="padding:6px 10px;font-size:8.5pt;border-bottom:1px solid #eee;text-align:right;font-weight:600;">{{ number_format($salersPdf->sum('task_count')) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 10px;font-size:8.5pt;border-bottom:1px solid #eee;">Total Revenue (TZS)</td>
                        <td style="padding:6px 10px;font-size:8.5pt;border-bottom:1px solid #eee;text-align:right;font-weight:600;">{{ number_format($grandTotal) }}</td>
                    </tr>
                    @if(($summary['balance_due'] ?? 0) > 0)
                    <tr>
                        <td style="padding:6px 10px;font-size:8.5pt;border-bottom:1px solid #eee;">Balance Due (TZS)</td>
                        <td style="padding:6px 10px;font-size:8.5pt;border-bottom:1px solid #eee;text-align:right;font-weight:600;color:#dc2626;">{{ number_format($summary['balance_due']) }}</td>
                    </tr>
                    @endif
                    <tr style="background:#dc2626;">
                        <td style="padding:8px 10px;font-size:10pt;font-weight:700;color:#fff;">Grand Total Sales</td>
                        <td style="padding:8px 10px;font-size:10pt;font-weight:700;color:#fff;text-align:right;">TZS {{ number_format($grandTotal) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    @endif

    {{-- ── 6-MONTH HISTORY (single saler) ── --}}
    @if(!empty($history))
    <div class="spacer"></div>
    <div class="section-title">6-Month Performance History — {{ $singleSaler['name'] }}</div>
    <table class="sub">
        <thead>
            <tr>
                <th>Month</th>
                <th class="text-center">Tasks</th>
                <th class="text-right">Revenue (TZS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($history as $i => $h)
            <tr class="{{ $i % 2 !== 0 ? 'even' : '' }}">
                <td>{{ $h['month'] }}</td>
                <td class="text-center">{{ number_format($h['tasks']) }}</td>
                <td class="text-right">{{ number_format($h['revenue']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ── TASK DETAILS (single saler) ── --}}
    @if($singleSalerTasks->isNotEmpty())
    <div class="spacer"></div>
    <div class="section-title">Task Details</div>
    <table class="sub">
        <thead>
            <tr>
                <th>Task Code</th>
                <th>Title</th>
                <th>Customer</th>
                <th class="text-center">Status</th>
                <th class="text-right">Price (TZS)</th>
                <th class="text-right">Paid (TZS)</th>
                <th class="text-right">Balance (TZS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($singleSalerTasks as $i => $task)
            <tr class="{{ $i % 2 !== 0 ? 'even' : '' }}">
                <td style="font-family:monospace;font-size:8pt;">{{ $task->task_code }}</td>
                <td>{{ Str::limit($task->title, 35) }}</td>
                <td>{{ $task->customer->name ?? '—' }}</td>
                <td class="text-center">
                    <span class="pill p-{{ $task->status }}">{{ str_replace('_', ' ', $task->status) }}</span>
                </td>
                <td class="text-right">{{ number_format($task->price) }}</td>
                <td class="text-right">{{ number_format($task->amount_paid) }}</td>
                <td class="text-right" style="{{ $task->balance > 0 ? 'color:#dc2626;font-weight:600;' : '' }}">{{ number_format($task->balance) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ── FOOTER ── --}}
    <div class="spacer"></div>
    <table class="footer">
        <tr>
            <td style="width:50%;">
                <div style="font-size:8pt;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Authorised Signature</div>
                <div class="sig-line"></div>
                <div class="sig-label">Manager / Director</div>
            </td>
            <td style="width:50%;text-align:right;" class="company-r">
                <div class="cname">Chibo Brands Company Limited</div>
                <div>Kinondoni Studio, Opposite Vijana House</div>
                <div>P.O.BOX 77773 &nbsp;|&nbsp; MOB: 0753 883 382</div>
                <div>EMAIL: info@chibobrands.com</div>
            </td>
        </tr>
    </table>

</div>
</body>
</html>
