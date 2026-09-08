<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $saler->name }} — Saler Activity Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #1a1a1a; background: #fff; padding: 30px 40px; }

        /* ── HEADER (centered, like saler performance print) ── */
        .header { text-align: center; margin-bottom: 24px; padding-bottom: 14px; border-bottom: 2.5px solid #1d4ed8; }
        .company-name { font-size: 22px; font-weight: 900; letter-spacing: .02em; color: #111; margin-bottom: 4px; }
        .report-title { font-size: 16px; color: #333; margin-bottom: 6px; }
        .report-info { font-size: 11px; color: #666; line-height: 1.7; }

        /* ── SECTION TITLES ── */
        .section-title { font-size: 14px; font-weight: 800; color: #1d4ed8; margin: 22px 0 10px; padding-bottom: 4px; border-bottom: 1px solid #dbeafe; }

        /* ── STAT STRIP ── */
        .stats-row { display: table; width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 16px; }
        .stats-cell { display: table-cell; border: 1px solid #d1d5db; border-radius: 6px; padding: 12px 10px; text-align: center; background: #fafafa; vertical-align: middle; }
        .stats-label { font-size: 9px; color: #6b7280; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 5px; }
        .stats-value { font-size: 22px; font-weight: 900; color: #111; line-height: 1; }

        /* ── PRINT BUTTONS ── */
        .btn-print { position: fixed; right: 14px; top: 14px; padding: 8px 14px; border: 0; border-radius: 4px; background: #1d4ed8; color: #fff; cursor: pointer; font-size: 13px; font-weight: 700; z-index: 999; }
        .btn-close-print { position: fixed; right: 160px; top: 14px; padding: 8px 14px; border: 1px solid #d1d5db; border-radius: 4px; background: #fff; color: #374151; cursor: pointer; font-size: 13px; font-weight: 700; z-index: 999; }

        /* ── TABLES (matching saler/designer performance print style) ── */
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        thead th { background: #f3f4f6; font-size: 11px; font-weight: 700; text-align: left; padding: 9px 10px; border: 1px solid #e5e7eb; color: #374151; }
        tbody td { padding: 8px 10px; border: 1px solid #e5e7eb; font-size: 11px; vertical-align: middle; }
        tbody tr:nth-child(even) td { background: #f9fafb; }
        tfoot td { border: 1px solid #e5e7eb; padding: 8px 10px; font-size: 11px; background: #f3f4f6; font-weight: 700; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* ── BADGES ── */
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; }
        .badge-converted, .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger    { background: #fee2e2; color: #991b1b; }
        .badge-pending, .badge-warning   { background: #fef3c7; color: #92400e; }
        .badge-primary   { background: #dbeafe; color: #1e40af; }
        .badge-info      { background: #e0f2fe; color: #0c4a6e; }
        .badge-secondary { background: #f1f5f9; color: #475569; }


        /* ── SIGNATURE / FOOTER ── */
        .signature-row { display: flex; justify-content: space-between; margin-top: 40px; font-size: 10px; color: #4b5563; }
        .signature-row .line { border-top: 1px solid #9ca3af; width: 220px; padding-top: 4px; text-align: center; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }

        /* ── FINANCIAL SUMMARY ── */
        .financial-summary { display: flex; justify-content: flex-end; margin: 8px 0 20px; }
        .summary-box { width: 300px; }
        .summary-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 11px; border-bottom: 1px solid #f1f5f9; }
        .summary-row:last-child { border-bottom: none; }
        .summary-row.total-row { background: #f5f5f5; padding: 10px; margin-top: 6px; font-weight: 700; font-size: 12px; color: #1d4ed8; }

        /* ── NOTICE ── */
        .notice-box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; background: #fffbeb; margin-bottom: 16px; }
        .notice-title { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 5px; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px; }

        @media print { .no-print { display: none !important; } body { margin: 0; } }
    </style>
</head>
<body>

    {{-- ── FIXED PRINT BUTTONS ── --}}
    <button class="btn-print no-print" onclick="window.print()">🖨 Print / Save PDF</button>
    <button class="btn-close-print no-print" onclick="window.close()">Close</button>

    @php
        $totalPaid  = $rows->sum('amount_paid');
        $converted  = $rows->filter(fn($r) => $r->follow_up_status === 'converted')->count();
        $pending    = $rows->filter(fn($r) => ($r->follow_up_status ?? '') === 'pending')->count();
        $withOrders = $rows->filter(fn($r) => !is_null($r->product_ordered))->count();
        $lostLeads  = $rows->filter(fn($r) => ($r->follow_up_status ?? '') === 'lost')->count();
    @endphp

    {{-- ── HEADER (centered, matches saler performance print style) ── --}}
    <div class="header">
        @include('partials.logo-print')
        <div class="company-name">CHIBOBRAND CO. LTD</div>
        <div class="report-title">{{ $saler->name }} — Saler Activity Report</div>
        <div class="report-info">
            Generated on: {{ now()->format('M d, Y H:i') }}<br>
            Period: {{ $periodLabel }} &nbsp;|&nbsp; Seller: <strong>{{ $saler->name }}</strong>
        </div>
    </div>

    {{-- ── STAT SUMMARY ── --}}
    <div class="section-title">Activity Summary</div>
    <div class="stats-row">
        <div class="stats-cell">
            <div class="stats-label">Total Leads</div>
            <div class="stats-value">{{ number_format($rows->count()) }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Converted (Won)</div>
            <div class="stats-value" style="color:#15803d;">{{ number_format($converted) }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Pending</div>
            <div class="stats-value" style="color:#b45309;">{{ number_format($pending) }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Lost</div>
            <div class="stats-value" style="color:#b91c1c;">{{ number_format($lostLeads) }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">With Orders</div>
            <div class="stats-value" style="color:#6d28d9;">{{ number_format($withOrders) }}</div>
        </div>
        <div class="stats-cell">
            <div class="stats-label">Total Paid (TZS)</div>
            <div class="stats-value" style="font-size:14px;color:#1d4ed8;">{{ number_format($totalPaid) }}</div>
        </div>
    </div>

        {{-- ── ACTIVITY TABLE ── --}}
        <div class="section-title">Lead &amp; Activity Details</div>

        @if($rows->isEmpty())
            <p style="text-align:center;color:#9ca3af;padding:30px 0;font-style:italic;">No activity data found for this period.</p>
        @else
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width:22px;">#</th>
                    <th>Client Name</th>
                    <th>Phone</th>
                    <th>Source</th>
                    <th>Follow-Up Date</th>
                    <th>Status</th>
                    <th>Product Asked</th>
                    <th>Product Ordered</th>
                    <th>Amount Paid</th>
                    <th>Work Status</th>
                    <th>Delivery</th>
                    <th>Feedback</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $i => $row)
                @php
                    $fc = match($row->follow_up_status ?? '') {
                        'converted' => 'converted',
                        'lost'      => 'danger',
                        'pending'   => 'pending',
                        default     => 'secondary'
                    };
                    $wc = match($row->work_status ?? '') {
                        'completed','super_completed' => 'success',
                        'in_progress'                 => 'primary',
                        'cancelled'                   => 'danger',
                        'delivered'                   => 'info',
                        default                       => 'secondary'
                    };
                @endphp
                <tr>
                    <td style="color:#94a3b8;">{{ $i + 1 }}</td>
                    <td style="font-weight:700;color:#0f172a;">{{ $row->client_name ?: '—' }}</td>
                    <td style="font-family:monospace;font-size:7.5pt;">{{ $row->phone ?: '—' }}</td>
                    <td>
                        @if($row->source)
                            <span class="badge badge-primary">{{ ucfirst(str_replace('_',' ',$row->source)) }}</span>
                        @else —
                        @endif
                    </td>
                    <td>{{ $row->follow_up_date ? \Carbon\Carbon::parse($row->follow_up_date)->format('d M Y') : '—' }}</td>
                    <td><span class="badge badge-{{ $fc }}">{{ ucfirst($row->follow_up_status ?? '—') }}</span></td>
                    <td>{{ $row->product_asked ?: '—' }}</td>
                    <td>{{ $row->product_ordered ?: '—' }}</td>
                    <td style="font-weight:700;color:{{ $row->amount_paid ? '#15803d' : '#9ca3af' }};">
                        {{ $row->amount_paid !== null ? 'TZS '.number_format($row->amount_paid) : '—' }}
                    </td>
                    <td>
                        @if($row->work_status)
                            <span class="badge badge-{{ $wc }}">{{ ucwords(str_replace('_',' ',$row->work_status)) }}</span>
                        @else —
                        @endif
                    </td>
                    <td>
                        @if($row->delivery_status)
                            <span class="badge badge-info">{{ ucwords(str_replace('_',' ',$row->delivery_status)) }}</span>
                        @else —
                        @endif
                    </td>
                    <td style="color:#64748b;max-width:100px;word-break:break-word;font-size:7pt;">{{ $row->feedback ?: '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ── SUMMARY BY SOURCE ── --}}
        <div class="section-title">Summary by Lead Source</div>
        @php $grandPaid = collect($summary)->sum('amount_paid'); @endphp
        <table style="max-width:520px;">
            <thead>
                <tr>
                    <th>Source</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Paid</th>
                    <th class="text-center">Unpaid</th>
                    <th class="text-end">Amount Paid (TZS)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary as $grp)
                <tr>
                    <td style="font-weight:700;">{{ $grp['label'] }}</td>
                    <td class="text-center">{{ $grp['total'] }}</td>
                    <td class="text-center" style="color:#166534;font-weight:700;">{{ $grp['paid_count'] }}</td>
                    <td class="text-center" style="color:#991b1b;font-weight:700;">{{ $grp['unpaid_count'] }}</td>
                    <td class="text-end" style="font-weight:700;color:#1d4ed8;">{{ number_format($grp['amount_paid']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ── FINANCIAL SUMMARY (right-aligned) ── --}}
        @php
            $wonCount = $converted;
            $total    = $rows->count();
            $convRate = $total > 0 ? round(($wonCount / $total) * 100, 1) : 0;
        @endphp
        <div class="financial-summary">
            <div class="summary-box">
                <div class="summary-row">
                    <span>Total Leads</span>
                    <span>{{ number_format($total) }}</span>
                </div>
                <div class="summary-row">
                    <span>Converted (Won)</span>
                    <span style="color:#15803d;font-weight:700;">{{ number_format($wonCount) }}</span>
                </div>
                <div class="summary-row">
                    <span>Conversion Rate</span>
                    <span style="font-weight:700;">{{ $convRate }}%</span>
                </div>
                <div class="summary-row total-row">
                    <span>Total Amount Paid</span>
                    <span>TZS {{ number_format($grandPaid) }}</span>
                </div>
            </div>
        </div>

        {{-- ── NOTICE ── --}}
        @if($notice)
        <div class="notice-box">
            <div class="notice-title">Summary Notice</div>
            <div style="font-size:10px;color:#0f172a;line-height:1.6;white-space:pre-wrap;">{{ $notice }}</div>
        </div>
        @endif

        @endif

        {{-- ── SIGNATURE ── --}}
        <div class="signature-row">
            <div class="line">
                {{ $saler->name }}<br>
                <span style="color:#9ca3af;">{{ ucfirst($saler->role) }} — Sales Agent</span>
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
