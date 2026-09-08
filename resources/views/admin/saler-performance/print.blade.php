<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $singleSaler ? $singleSaler['name'] . ' — Performance Report' : 'Sales Performance Report' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Nunito Sans', sans-serif;
            background-color: #fff;
            color: #444;
            font-size: 8.5pt;
            line-height: 1.3;
        }

        .invoice-wrapper {
            max-width: 850px;
            margin: 0 auto;
            padding: 10px 15px;
        }

        /* Header */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
        }

        .company-header-info { font-size: 9pt; line-height: 1.4; }
        .company-header-info h1 {
            font-weight: 800;
            font-size: 14pt;
            margin: 0 0 5px 0;
            color: #d63031;
        }

        .report-header-title { text-align: right; }
        .report-header-title h2 {
            font-size: 24pt;
            font-weight: 800;
            margin: 0;
            color: #dc2626;
            text-transform: uppercase;
            letter-spacing: -1px;
        }
        .report-header-title .report-sub {
            font-size: 10pt;
            color: #666;
            margin-top: 4px;
        }
        .report-header-title .report-total {
            margin-top: 10px;
        }
        .report-header-title .report-total .label {
            font-size: 9pt;
            color: #888;
            text-transform: uppercase;
            font-weight: 600;
        }
        .report-header-title .report-total .amount {
            font-weight: 800;
            font-size: 18pt;
            color: #d63031;
        }

        /* Meta info section */
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .info-left h3 { font-size: 10pt; color: #666; margin: 0 0 5px 0; font-weight: 400; }
        .info-left .value { font-weight: 700; font-size: 11pt; color: #000; }

        .report-details table { border-collapse: collapse; }
        .report-details td { padding: 2px 10px; font-size: 9.5pt; }
        .report-details td:first-child { text-align: right; color: #555; }
        .report-details td:last-child { text-align: right; font-weight: 500; }

        /* Table */
        .table-pro {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .table-pro th {
            background-color: #dc2626 !important;
            padding: 10px;
            font-weight: 700;
            font-size: 10pt;
            color: #fff;
            border: none;
        }
        .table-pro td {
            border-bottom: 1px solid #eee;
            padding: 10px 10px;
            vertical-align: middle;
            font-size: 9.5pt;
        }
        .table-pro tr:last-child td { border-bottom: 2px solid #ccc; }
        .table-pro tbody tr:nth-child(even) td { background: #fafafa; }
        .table-pro tbody tr.top-1 td { background: #fefce8 !important; }
        .table-pro tbody tr.top-2 td { background: #f0fdf4 !important; }
        .table-pro tbody tr.top-3 td { background: #eff6ff !important; }

        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            font-size: 8pt;
            font-weight: 800;
        }
        .rank-1 { background: #fbbf24; color: #78350f; }
        .rank-2 { background: #94a3b8; color: #1e293b; }
        .rank-3 { background: #cd7c35; color: #fff; }
        .rank-other { background: #e2e8f0; color: #475569; }

        .saler-name { font-weight: 700; color: #222; }
        .saler-role { font-size: 8pt; color: #999; text-transform: capitalize; }

        .progress-wrap {
            background: #e2e8f0;
            border-radius: 999px;
            height: 5px;
            width: 70px;
            display: inline-block;
            vertical-align: middle;
            margin-right: 4px;
        }
        .progress-fill { height: 5px; border-radius: 999px; display: block; }
        .progress-fill.good  { background: #10b981; }
        .progress-fill.warn  { background: #f59e0b; }
        .progress-fill.low   { background: #ef4444; }

        /* Summary box (right-aligned) */
        .financial-summary {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 40px;
        }
        .summary-box { width: 320px; }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 10pt;
        }
        .summary-row.grand-total {
            font-weight: 700;
            font-size: 10.5pt;
        }
        .summary-row.total-row {
            background-color: #f5f5f5;
            padding: 12px 10px;
            margin-top: 10px;
            font-weight: 700;
            font-size: 11pt;
            color: #d63031;
        }

        /* Footer */
        .footer-content {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            font-size: 8pt;
            line-height: 1.4;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .company-contact { text-align: right; }
        .company-contact .name { font-weight: 700; text-transform: uppercase; font-size: 8.5pt; }

        @media print {
            @page { size: A4; margin: 0mm; }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                padding: 15mm 15mm 140px 15mm !important;
                margin: 0 !important;
                background: white;
            }
            .no-print { display: none !important; }
            .invoice-wrapper { width: 100%; max-width: 100%; padding: 0; margin: 0; }
            .footer-container {
                position: fixed;
                bottom: 0; left: 0; right: 0;
                width: 100%;
                background: white !important;
                padding: 0 15mm 10mm 15mm;
                z-index: 1000;
            }
            .footer-content {
                margin-top: 0 !important;
                border-top: 1px solid #eee !important;
                padding-top: 15px !important;
            }
        }
    </style>
</head>
<body>

    {{-- Print bar --}}
    <div class="no-print py-1 px-3 bg-white text-dark d-flex justify-content-between align-items-center mb-3 border-bottom">
        <small class="fw-semibold text-dark"><i class="fas fa-print me-1 text-primary"></i> SALES PERFORMANCE REPORT — PRINT PREVIEW</small>
        <div class="d-flex gap-1">
            <button class="btn btn-dark btn-sm fw-bold px-3" onclick="window.print()">Print</button>
            <button class="btn btn-outline-dark btn-sm px-2" onclick="window.close()">Close</button>
        </div>
    </div>

    <div class="invoice-wrapper">

        {{-- ── HEADER ── --}}
        <div class="header-section">
            <div class="company-header-info">
                @include('partials.logo-print')
                <h1>Chibo Brands Co Ltd</h1>
                <div>Kinondoni Dar es Salaam 14108</div>
                <div>Tanzania</div>
                <div>+255 753 883 382</div>
                <div>www.chibobrands.com</div>
            </div>
            <div class="report-header-title">
                <h2>{{ $singleSaler ? 'Performance' : 'Sales Report' }}</h2>
                @if($singleSaler)
                    <div class="report-sub">{{ $singleSaler['name'] }}</div>
                @endif
                <div class="report-sub" style="color:#888;">{{ $periodLabel }}</div>
                <div class="report-total">
                    <div class="label">Grand Total Sales</div>
                    <div class="amount">TZS {{ number_format($grandTotal) }}</div>
                </div>
            </div>
        </div>

        {{-- ── META / DETAILS ── --}}
        <div class="info-section">
            <div class="info-left">
                <h3>Prepared by</h3>
                <div class="value">{{ auth()->user()->name }}</div>
                <div style="color:#666;margin-top:4px;">{{ ucfirst(auth()->user()->role) }}</div>
            </div>
            <div class="report-details">
                <table>
                    <tr>
                        <td>Report Date :</td>
                        <td>{{ now()->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td>Period :</td>
                        <td>{{ $periodLabel }}</td>
                    </tr>
                    <tr>
                        <td>Total Salers :</td>
                        <td>{{ $salers->count() }}</td>
                    </tr>
                    <tr>
                        <td>Generated at :</td>
                        <td>{{ now()->format('H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- ── PERFORMANCE TABLE ── --}}
        @if($salers->isEmpty())
            <p style="text-align:center;color:#999;padding:40px 0;">No sales performance data for this period.</p>
        @else
        <table class="table-pro">
            <thead style="display: table-header-group;">
                <tr>
                    <th class="text-center" style="width:36px;">#</th>
                    <th class="text-start">Salesperson</th>
                    <th class="text-center" style="width:60px;">Tasks</th>
                    <th class="text-center" style="width:60px;">Orders</th>
                    <th class="text-end" style="width:130px;">Task Sales</th>
                    <th class="text-end" style="width:130px;">Order Sales</th>
                    <th class="text-end" style="width:140px;">Total Sales</th>
                    <th class="text-end" style="width:120px;">Target</th>
                    <th class="text-center" style="width:110px;">Achievement</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTaskSales = $salers->sum('task_sales'); $grandOrderSales = $salers->sum('order_sales'); @endphp
                @foreach($salers as $i => $saler)
                @php
                    $rank = $i + 1;
                    $ach  = min($saler['achievement'], 100);
                    $progressClass = $ach >= 100 ? 'good' : ($ach >= 60 ? 'warn' : 'low');
                    $rowClass = $rank === 1 ? 'top-1' : ($rank === 2 ? 'top-2' : ($rank === 3 ? 'top-3' : ''));
                @endphp
                <tr class="{{ $rowClass }}">
                    <td class="text-center">
                        <span class="rank-badge {{ $rank <= 3 ? 'rank-' . $rank : 'rank-other' }}">{{ $rank }}</span>
                    </td>
                    <td>
                        <div class="saler-name">{{ $saler['name'] }}</div>
                        <div class="saler-role">{{ ucfirst($saler['role']) }}</div>
                    </td>
                    <td class="text-center">{{ number_format($saler['task_count']) }}</td>
                    <td class="text-center">{{ number_format($saler['order_count']) }}</td>
                    <td class="text-end">{{ number_format($saler['task_sales']) }}</td>
                    <td class="text-end">{{ number_format($saler['order_sales']) }}</td>
                    <td class="text-end" style="font-weight:700;">TZS {{ number_format($saler['total_sales']) }}</td>
                    <td class="text-end">
                        @if($saler['target_amount'] > 0)
                            TZS {{ number_format($saler['target_amount']) }}
                        @else
                            <span style="color:#bbb;">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($saler['target_amount'] > 0)
                            <span class="progress-wrap">
                                <span class="progress-fill {{ $progressClass }}" style="width:{{ $ach }}%;"></span>
                            </span>
                            <span style="font-weight:700;font-size:8.5pt;color:{{ $ach >= 100 ? '#10b981' : ($ach >= 60 ? '#f59e0b' : '#ef4444') }}">
                                {{ number_format($saler['achievement'], 1) }}%
                            </span>
                        @else
                            <span style="color:#bbb;font-size:8pt;">No target</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot style="display: table-footer-group;">
                <tr>
                    <td colspan="9" style="border:none;padding:0;">
                        <div style="height:25mm;"></div>
                    </td>
                </tr>
            </tfoot>
        </table>

        {{-- ── SUMMARY (right-aligned, invoice style) ── --}}
        <div class="financial-summary">
            <div class="summary-box">
                <div class="summary-row">
                    <span>Total Task Sales</span>
                    <span>TZS {{ number_format($grandTaskSales) }}</span>
                </div>
                <div class="summary-row">
                    <span>Total Order Sales</span>
                    <span>TZS {{ number_format($grandOrderSales) }}</span>
                </div>
                <div class="summary-row grand-total mt-1" style="border-top:1px solid #eee;padding-top:10px;">
                    <span>Sub Total</span>
                    <span>TZS {{ number_format($grandTotal) }}</span>
                </div>
                <div class="summary-row total-row">
                    <span>Grand Total Sales</span>
                    <span>TZS {{ number_format($grandTotal) }}</span>
                </div>
            </div>
        </div>
        @endif

        {{-- ── FOOTER ── --}}
        <div class="footer-container">
            <div class="footer-content">
                <div style="width:50%;">
                    <div style="font-weight:700;text-transform:uppercase;font-size:8.5pt;margin-bottom:6px;">Authorised Signature</div>
                    <div style="margin-top:30px;border-top:1px solid #ccc;width:160px;"></div>
                    <div style="margin-top:4px;font-size:7.5pt;color:#888;">Manager / Director</div>
                </div>
                <div class="company-contact" style="width:50%;">
                    <div class="name mb-2">Chibo Brands Company Limited</div>
                    <div class="mb-1">Kinondoni Studio Opposite Vijana House</div>
                    <div class="mb-1">P.O.BOX 77773</div>
                    <div class="mb-1">MOB: 0753 883 382</div>
                    <div class="mb-0">EMAIL: info@chibobrands.com</div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
