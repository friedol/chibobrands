<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle }} — Department Sales Report</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #1a1a1a; background: #fff; padding: 30px 40px; }

        /* ── PRINT BUTTONS ── */
        .btn-print { position: fixed; right: 14px; top: 14px; padding: 8px 14px; border: 0; border-radius: 4px; background: #1d4ed8; color: #fff; cursor: pointer; font-size: 13px; font-weight: 700; z-index: 999; }
        .btn-close-print { position: fixed; right: 160px; top: 14px; padding: 8px 14px; border: 1px solid #d1d5db; border-radius: 4px; background: #fff; color: #374151; cursor: pointer; font-size: 13px; font-weight: 700; z-index: 999; }

        /* ── HEADER (centered, matching performance print style) ── */
        .header { text-align: center; margin-bottom: 24px; padding-bottom: 14px; border-bottom: 2.5px solid #1d4ed8; }
        .company-name { font-size: 22px; font-weight: 900; letter-spacing: .02em; color: #111; margin-bottom: 4px; }
        .report-title { font-size: 16px; color: #333; margin-bottom: 6px; }
        .report-info { font-size: 11px; color: #666; line-height: 1.7; }

        /* ── SECTION TITLES ── */
        .section-title { font-size: 14px; font-weight: 800; color: #1d4ed8; margin: 22px 0 10px; padding-bottom: 4px; border-bottom: 1px solid #dbeafe; }
        .dept-title { font-size: 13px; font-weight: 800; color: #1d4ed8; margin: 28px 0 4px; padding-bottom: 4px; border-bottom: 1px solid #dbeafe; text-transform: uppercase; }

        /* ── EXECUTIVE SUMMARY STRIP ── */
        .stats-row { display: table; width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 16px; }
        .stats-cell { display: table-cell; border: 1px solid #d1d5db; border-radius: 6px; padding: 12px 10px; text-align: center; background: #fafafa; vertical-align: middle; }
        .stats-label { font-size: 9px; color: #6b7280; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 5px; }
        .stats-value { font-size: 22px; font-weight: 900; color: #111; line-height: 1; }

        /* ── TABLES (matching performance print style) ── */
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        thead th { background: #f3f4f6; font-size: 11px; font-weight: 700; text-align: left; padding: 9px 10px; border: 1px solid #e5e7eb; color: #374151; }
        tbody td { padding: 8px 10px; border: 1px solid #e5e7eb; font-size: 11px; vertical-align: middle; }
        tbody tr:nth-child(even) td { background: #f9fafb; }
        tfoot td { border: 1px solid #e5e7eb; padding: 8px 10px; font-size: 11px; background: #f3f4f6; font-weight: 700; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: 700; }
        .text-muted { color: #6b7280; }

        /* ── FINANCIAL SUMMARY (right-aligned) ── */
        .financial-summary { display: flex; justify-content: flex-end; margin: 4px 0 20px; }
        .summary-box { width: 320px; }
        .summary-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 11px; border-bottom: 1px solid #f1f5f9; }
        .summary-row:last-child { border-bottom: none; }
        .summary-row.grand-total { background: #f5f5f5; padding: 10px; margin-top: 6px; font-weight: 700; font-size: 12px; color: #1d4ed8; }

        /* ── SIGNATURE / FOOTER ── */
        .signature-row { display: flex; justify-content: space-between; margin-top: 40px; font-size: 10px; color: #4b5563; }
        .signature-row .line { border-top: 1px solid #9ca3af; width: 220px; padding-top: 4px; text-align: center; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }

        @media print { .no-print { display: none !important; } body { margin: 0; } }
    </style>
</head>
<body>

    {{-- ── FIXED PRINT BUTTONS ── --}}
    <button class="btn-print no-print" onclick="window.print()">🖨 Print / Save PDF</button>
    <button class="btn-close-print no-print" onclick="window.close()">Close</button>

    {{-- ── HEADER ── --}}
    <div class="header">
        @include('partials.logo-print')
        <div class="company-name">CHIBOBRAND CO. LTD</div>
        <div class="report-title">{{ $reportTitle }}</div>
        <div class="report-info">
            Generated on: {{ now()->format('M d, Y H:i') }}<br>
            Period: <strong>{{ $startDate->format('d M Y') }} – {{ $endDate->format('d M Y') }}</strong>
        </div>
    </div>

    {{-- ── EXECUTIVE SUMMARY ── --}}
    <div class="section-title">Executive Summary</div>
    <div class="stats-row">
        <div class="stats-cell">
            <div class="stats-label">Overall Achievement</div>
            <div class="stats-value" style="color:{{ $overallPercentage >= 100 ? '#15803d' : '#1d4ed8' }};">{{ $overallPercentage }}%</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Total Revenue (TZS)</div>
            <div class="stats-value" style="font-size:16px;">{{ number_format($overallTotalSales) }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Overall Target (TZS)</div>
            <div class="stats-value" style="font-size:16px;color:#6b7280;">{{ number_format($overallTotalTarget) }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Departments</div>
            <div class="stats-value">{{ count($reportData) }}</div>
        </div>
    </div>

    {{-- ── DEPARTMENT DETAILS ── --}}
    @foreach($reportData as $data)
    <div>
        @if(count($reportData) > 1)
            <div class="dept-title">{{ $data['display_name'] }}</div>
        @else
            <div class="section-title">Sales Details</div>
        @endif

        @if(!empty($data['target_note']))
            <div style="font-size:10px;color:#888;margin-bottom:8px;"><em>{{ $data['target_note'] }}</em></div>
        @endif

        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width:5%;">No</th>
                    <th style="width:22%;">Customer</th>
                    <th style="width:38%;">Task Description</th>
                    <th class="text-center" style="width:10%;">Qty</th>
                    <th class="text-right" style="width:25%;">Revenue (TZS)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['tasks'] as $index => $task)
                <tr>
                    <td class="text-center text-muted">{{ $index + 1 }}</td>
                    <td>
                        <div class="fw-bold" style="color:#111;">{{ $task['customer_name'] }}</div>
                    </td>
                    <td>
                        <div class="fw-bold">{{ $task['title'] }}</div>
                        @if($task['description'])
                            <div class="text-muted" style="font-size:10px;">{{ Str::limit($task['description'], 100) }}</div>
                        @endif
                    </td>
                    <td class="text-center fw-bold">{{ number_format($task['qty']) }}</td>
                    <td class="text-right fw-bold" style="color:#15803d;">{{ number_format($task['price']) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted" style="font-style:italic;padding:16px 0;">No sales recorded for this department in this period.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Department Sub-Summary --}}
        <div class="financial-summary">
            <div class="summary-box">
                <div class="summary-row">
                    <span class="text-muted fw-bold">Department Target</span>
                    <span class="fw-bold">TZS {{ number_format($data['target_amount']) }}</span>
                </div>
                <div class="summary-row">
                    <span class="text-muted fw-bold">Achievement</span>
                    <span class="fw-bold" style="color:{{ $data['percentage'] >= 100 ? '#15803d' : '#1d4ed8' }};">{{ $data['percentage'] }}%</span>
                </div>
                <div class="summary-row grand-total">
                    <span>Total Revenue</span>
                    <span>TZS {{ number_format($data['total_sales']) }}</span>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    {{-- ── SIGNATURE ── --}}
    <div class="signature-row">
        <div class="line">
            Prepared By: {{ auth()->user()->name }}<br>
            <span style="color:#9ca3af;">{{ ucfirst(auth()->user()->role) }}</span>
        </div>
        <div class="line">
            Authorised By: General Manager / Director<br>
            <span style="color:#9ca3af;">Official Stamp &amp; Authorization</span>
        </div>
    </div>

    {{-- ── FOOTER ── --}}
    <div class="footer">
        &copy; {{ date('Y') }} CHIBOBRAND CO. LTD. &nbsp;|&nbsp; Kinondoni Studio Opposite Vijana House, P.O.BOX 77773 &nbsp;|&nbsp; MOB: 0753 883 382 &nbsp;|&nbsp; info@chibobrands.com
    </div>

    <script>
        window.onload = function() {
            if (window.location.search.includes('print=true') && window.self === window.top) {
                setTimeout(() => window.print(), 500);
            }
        };
    </script>
</body>
</html>
