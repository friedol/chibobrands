<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>&nbsp;</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Nunito Sans', sans-serif;
            background-color: #fff;
            color: #222;
            font-size: 8.5pt;
            line-height: 1.3;
        }

        .log-wrapper {
            max-width: 1000px;
            margin: 0 auto;
            padding: 10px 15px;
        }

        .report-header {
            border-bottom: 1.5px solid #222;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .brand-logo {
            height: 45px;
            margin-bottom: 4px;
        }

        .company-name {
            font-weight: 800;
            font-size: 13pt;
            letter-spacing: -0.5px;
            margin: 0;
            color: #222;
        }

        .report-title {
            font-weight: 800;
            font-size: 15pt;
            text-transform: uppercase;
            margin: 0;
            text-align: right;
            color: #dc2626;
        }

        .meta-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            gap: 30px;
        }

        .section-label {
            font-weight: 800;
            font-size: 7.5pt;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 6px;
            border-bottom: 1px solid #ddd;
            display: inline-block;
            padding-bottom: 1px;
        }

        .table-pro {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .table-pro th {
            background-color: #f8f8f8 !important;
            border: 1px solid #ddd;
            padding: 6px 8px;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 7.5pt;
            color: #dc2626;
        }

        .table-pro td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            vertical-align: top;
        }

        .print-footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .dev-credit {
            font-size: 7pt;
            color: #888;
            text-align: center;
            width: 100%;
            padding: 5px 0;
        }

        .dev-credit a {
            color: #888;
            text-decoration: none;
            font-weight: 600;
        }

        @media print {
            @page {
                size: A4;
                margin: 15mm 15mm 25mm 15mm;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                padding: 0;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            .log-wrapper {
                width: 100%;
                max-width: 100%;
                padding: 0;
            }

            .table-pro thead {
                display: table-header-group;
            }

            .print-footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                width: 100%;
                background: white;
                padding: 5mm 0;
                border-top: 1px solid #ddd;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .print-footer::before {
                content: "Page " counter(page);
                position: absolute;
                bottom: 5mm;
                right: 15mm;
                font-size: 7pt;
                color: #888;
            }
        }
    </style>
</head>
<body>

    <div class="no-print py-1 px-3 bg-dark text-white d-flex justify-content-between align-items-center mb-3">
        <small class="fw-semibold"><i class="fas fa-print me-1"></i> BALANCE SHEET PRINT PREVIEW</small>
        <div class="d-flex gap-1">
            <button class="btn btn-primary btn-sm fw-bold px-3" onclick="window.print()">Print</button>
            <button class="btn btn-outline-light btn-sm px-2" onclick="window.close()">Close</button>
        </div>
    </div>

    @php
        $asAtC = $asAt instanceof \Carbon\Carbon ? $asAt : \Carbon\Carbon::parse($asAt);
        $dateFromC = $dateFrom instanceof \Carbon\Carbon ? $dateFrom : \Carbon\Carbon::parse($dateFrom);
    @endphp

    <div class="log-wrapper">
        <div class="report-header">
            <div class="row align-items-start">
                <div class="col-7">
                    @include('partials.logo-print')
                    <h1 class="company-name">CHIBOBRAND CO. LTD.</h1>
                </div>
                <div class="col-5 text-end">
                    <h2 class="report-title">Balance Sheet</h2>
                    <p class="mb-0 fw-bold">GENERATED: {{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <div class="meta-container">
            <div>
                <span class="section-label">Period</span>
                <div class="fw-bold">{{ $dateFromC->format('d M Y') }} – {{ $asAtC->format('d M Y') }}</div>
                <div class="text-muted small">{{ ($currentDept ?? null) ? $currentDept->name : 'Consolidated' }}</div>
            </div>
            <div class="text-end">
                <span class="section-label">Summary</span>
                <div class="small">
                    <strong>Total Assets:</strong> TZS {{ number_format($total_assets) }}<br>
                    <strong>Total Liabilities &amp; Equity:</strong> TZS {{ number_format($total_liabilities_equity) }}
                </div>
            </div>
        </div>

        <table class="table-pro">
            <thead>
                <tr>
                    <th>ASSETS</th>
                    <th class="text-end">TZS</th>
                    <th>LIABILITIES &amp; EQUITY</th>
                    <th class="text-end">TZS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Mobile (net)</td>
                    <td class="text-end fw-bold">{{ number_format($cash_mobile ?? 0) }}</td>
                    <td>Liabilities</td>
                    <td class="text-end fw-bold">{{ number_format($liabilities) }}</td>
                </tr>
                <tr>
                    <td>Cash (net)</td>
                    <td class="text-end fw-bold">{{ number_format($cash_cash ?? 0) }}</td>
                    <td>Equity (net position)</td>
                    <td class="text-end fw-bold">{{ number_format($equity) }}</td>
                </tr>
                <tr>
                    <td>Bank (net)</td>
                    <td class="text-end fw-bold">{{ number_format($cash_bank ?? 0) }}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>Accounts receivable</td>
                    <td class="text-end fw-bold">{{ number_format($receivables) }}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr style="background-color: #f8f8f8; border-top: 2px solid #222;">
                    <td class="fw-bold" style="padding: 10px;">Total assets</td>
                    <td class="text-end fw-bold" style="padding: 10px;">{{ number_format($total_assets) }}</td>
                    <td class="fw-bold" style="padding: 10px;">Total liabilities &amp; equity</td>
                    <td class="text-end fw-bold" style="padding: 10px;">{{ number_format($total_liabilities_equity) }}</td>
                </tr>
            </tbody>
        </table>

        @if(!($currentDept ?? null) && $department_breakdown->isNotEmpty())
            <div class="mb-3">
                <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3" style="font-size: 10pt; color: #2C3E50;">
                    By Department
                </h6>
                <table class="table-pro">
                    <thead>
                        <tr>
                            <th>DEPARTMENT</th>
                            <th class="text-end">MOBILE</th>
                            <th class="text-end">CASH</th>
                            <th class="text-end">BANK</th>
                            <th class="text-end">RECEIVABLES</th>
                            <th class="text-end">TOTAL ASSETS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($department_breakdown as $d)
                            <tr>
                                <td class="fw-bold">{{ $d['name'] }}</td>
                                <td class="text-end">{{ number_format($d['mobile'] ?? 0) }}</td>
                                <td class="text-end">{{ number_format($d['cash'] ?? 0) }}</td>
                                <td class="text-end">{{ number_format($d['bank'] ?? 0) }}</td>
                                <td class="text-end">{{ number_format($d['receivables']) }}</td>
                                <td class="text-end fw-bold">{{ number_format($d['total_assets']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="print-footer">
            <div class="dev-credit">
                Developed by <a href="https://fridoltech.com" target="_blank">Fridoltech</a>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            if (window.location.search.includes('print=true')) {
                setTimeout(() => { window.print(); }, 400);
            }
        };
    </script>
</body>
</html>
