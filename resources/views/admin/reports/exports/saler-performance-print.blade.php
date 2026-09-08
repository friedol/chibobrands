<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $singleSaler ? ($singleSaler['name'] . ' Performance Report - CHIBOBRAND') : 'Sales Performance Report - CHIBOBRAND' }}</title>
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

    /* ── Tables ── */
    table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    thead th { background: #f3f4f6; font-size: 11px; font-weight: 700; text-align: left; padding: 9px 10px; border: 1px solid #e5e7eb; color: #374151; }
    tbody td { padding: 8px 10px; border: 1px solid #e5e7eb; font-size: 11px; vertical-align: middle; }
    tbody tr:nth-child(even) td { background: #f9fafb; }
    tfoot td { border: 1px solid #e5e7eb; padding: 8px 10px; font-size: 11px; background: #f3f4f6; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .fw-bold { font-weight: 700; }

    .rank { display: inline-block; width: 18px; height: 18px; line-height: 18px; border-radius: 50%; background: #1d4ed8; color: #fff; font-size: 9px; font-weight: 800; text-align: center; }
    .achv { font-weight: 800; }
    .achv-good { color: #15803d; }
    .achv-warn { color: #b45309; }
    .achv-bad  { color: #b91c1c; }

    /* ── Signature / Footer ── */
    .signature-row { display: flex; justify-content: space-between; margin-top: 40px; font-size: 10px; color: #4b5563; }
    .signature-row .line { border-top: 1px solid #9ca3af; width: 220px; padding-top: 4px; text-align: center; }
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
        <div class="report-title">{{ $singleSaler ? $singleSaler['name'] . ' Performance Report' : 'Sales Performance Report' }}</div>
        <div class="report-info">
            Generated on: {{ now()->format('M d, Y H:i') }}<br>
            Period: {{ $periodLabel ?? 'Report Overview' }}
            &nbsp;|&nbsp; Prepared by: <strong>{{ auth()->user()->name }}</strong>
        </div>
    </div>

    {{-- Performance Summary --}}
    <div class="section-title">Performance Summary</div>
    <div class="stats-row">
        @if($singleSaler)
            <div class="stats-cell">
                <div class="stats-label">Design Tasks</div>
                <div class="stats-value">{{ number_format($summary['total_tasks'] ?? 0) }}</div>
            </div>
            <div class="stats-cell">
                <div class="stats-label">Total Revenue</div>
                <div class="stats-value">{{ number_format($summary['total_revenue'] ?? 0) }}</div>
            </div>
            <div class="stats-cell">
                <div class="stats-label">Balance Due</div>
                <div class="stats-value">{{ number_format($summary['balance_due'] ?? 0) }}</div>
            </div>
        @else
            <div class="stats-cell">
                <div class="stats-label">Total Tasks</div>
                <div class="stats-value">{{ number_format($summary['total_tasks'] ?? 0) }}</div>
            </div>
            <div class="stats-cell">
                <div class="stats-label">Active Sellers</div>
                <div class="stats-value">{{ number_format($summary['active_salers'] ?? 0) }}</div>
            </div>
            <div class="stats-cell">
                <div class="stats-label">Total Revenue</div>
                <div class="stats-value">{{ number_format($summary['total_revenue'] ?? 0) }}</div>
            </div>
        @endif
    </div>

    @if($singleSaler)
        <div class="section-title">Task Revenue Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th style="width:36px;">#</th>
                    <th>Task Number</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Task Name</th>
                    <th>Status</th>
                    <th class="text-right">Revenue (TZS)</th>
                    <th class="text-right">Paid (TZS)</th>
                    <th class="text-right">Balance Due (TZS)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($singleSalerTasks as $index => $task)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $task->task_code ?: '#' . $task->id }}</td>
                        <td>{{ $task->customer->name ?? $task->customer->company_name ?? 'Walk-in / Unknown' }}</td>
                        <td>{{ $task->customer->phone ?? $task->customer->whatsapp_number ?? 'N/A' }}</td>
                        <td>{{ $task->title ?: 'Untitled Task' }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $task->status ?? 'unknown')) }}</td>
                        <td class="text-right">{{ number_format((float) $task->price) }}</td>
                        <td class="text-right">{{ number_format((float) $task->amount_paid) }}</td>
                        <td class="text-right">{{ number_format((float) $task->balance) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center" style="color:#9ca3af;padding:16px;">No task records found for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    @else
        <div class="section-title">Salesperson Performance Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th style="width:36px;">#</th>
                    <th>Salesperson</th>
                    <th class="text-center">Tasks</th>
                    <th class="text-right">Total Sales</th>
                    <th class="text-right">Target</th>
                    <th class="text-center">Achievement</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salers as $index => $saler)
                    @php
                        $ach = min($saler['achievement'], 100);
                        $achClass = $ach >= 100 ? 'achv-good' : ($ach >= 60 ? 'achv-warn' : 'achv-bad');
                    @endphp
                    <tr>
                        <td class="text-center"><span class="rank">{{ $index + 1 }}</span></td>
                        <td>
                            <div class="fw-bold">{{ $saler['name'] }}</div>
                            <div style="font-size:9px;color:#9ca3af;text-transform:capitalize;">{{ $saler['role'] }}</div>
                        </td>
                        <td class="text-center">{{ number_format($saler['task_count']) }}</td>
                        <td class="text-right fw-bold">{{ number_format($saler['total_sales']) }}</td>
                        <td class="text-right">

                            @if($saler['target_amount'] > 0)
                                {{ number_format($saler['target_amount']) }}
                                @if(!empty($saler['target_note']))
                                    <div style="font-size:7.5px;color:#9ca3af;font-weight:400;">{{ $saler['target_note'] }}</div>
                                @endif
                            @else
                                <span style="color:#9ca3af;">&mdash;</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($saler['target_amount'] > 0)
                                <span class="achv {{ $achClass }}">{{ number_format($saler['achievement'], 1) }}%</span>
                            @else
                                <span style="color:#9ca3af;font-size:10px;">No target</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center" style="color:#9ca3af;padding:16px;">No records found.</td></tr>
                @endforelse
            </tbody>
            @if($salers->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right fw-bold">GRAND TOTAL</td>
                        <td class="text-right fw-bold" style="color:#1d4ed8;">{{ number_format($grandTotal) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @endif

    <div class="signature-row">
        <div class="line">Prepared By: {{ auth()->user()->name }}</div>
        <div class="line">Approved By: General Manager / Director</div>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} CHIBOBRAND CO. LTD. All rights reserved. | Official Sales Performance Document
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            if (window.location.search.includes('autoprint=1')) {
                setTimeout(() => window.print(), 300);
            }
        });
    </script>
</body>
</html>
