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
            color: #444;
            font-size: 8.5pt;
            line-height: 1.3;
        }

        .log-wrapper {
            max-width: 1000px;
            margin: 0 auto;
            padding: 10px 15px;
        }

        /* Header Layout */
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
            color: #444;
        }

        /* Meta Information */
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

        /* Table Design */
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
            color: #444;
        }

        .table-pro td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            vertical-align: top;
        }

        /* Footer Positioning */
        .print-footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .company-seal {
            text-align: right;
            font-size: 8pt;
            line-height: 1.4;
            color: #666;
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

        .badge-in {
            color: #198754;
            font-weight: 700;
            font-size: 7pt;
        }

        .badge-out {
            color: #fd7e14;
            font-weight: 700;
            font-size: 7pt;
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
            
            /* Header on every page */
            .report-header {
                page-break-after: avoid;
                page-break-inside: avoid;
            }
            
            /* Meta info stays with header */
            .meta-container {
                page-break-after: avoid;
                page-break-inside: avoid;
            }
            
            /* Table header repeats on each page */
            .table-pro thead {
                display: table-header-group;
            }
            
            .table-pro tbody {
                display: table-row-group;
            }
            
            .table-pro tfoot {
                display: table-footer-group;
            }
            
            /* Prevent page breaks inside table rows */
            .table-pro tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            /* Footer positioning */
            .print-footer { 
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                width: 100%;
                background: white;
                padding: 5mm 0;
                margin-top: 0;
                border-top: 1px solid #ddd;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            /* Add space for fixed footer */
            .table-pro {
                margin-bottom: 60px;
            }
            
            /* Page counter */
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

    <div class="no-print p-3 bg-primary text-white d-flex justify-content-between align-items-center mb-4">
        <div class="ms-2">
            <h6 class="mb-0 fw-bold"><i class="fas fa-print me-2"></i> GATEKEEPER LOG PRINT PREVIEW</h6>
        </div>
        <div class="me-2">
            <button class="btn btn-light fw-bold px-4" onclick="window.print()">PRINT NOW</button>
            <button class="btn btn-outline-light ms-2" onclick="window.close()">CLOSE</button>
        </div>
    </div>

    <div class="log-wrapper">
        <!-- Header -->
        <div class="report-header">
            <div class="row align-items-end">
                <div class="col-7">
                    <img src="{{ asset('images/logo.webp') }}" alt="BRAND LOGO" class="brand-logo" onerror="this.style.display='none'">
                    <h1 class="company-name">CHIBOBRAND CO. LTD.</h1>
                </div>
                <div class="col-5 text-end">
                    <h2 class="report-title">Gatekeeper Movement Log</h2>
                    <p class="mb-0 fw-bold">GENERATED: {{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Meta -->
        <div class="meta-container">
            <div>
                <span class="section-label">Log Period</span>
                <div class="fw-bold">
                    @if(request('date_from') && request('date_to'))
                        {{ request('date_from') }} to {{ request('date_to') }}
                    @elseif(request('date_from'))
                        From {{ request('date_from') }}
                    @elseif(request('date_to'))
                        Until {{ request('date_to') }}
                    @else
                        All Time Records
                    @endif
                </div>
            </div>
            <div class="text-end">
                <span class="section-label">Summary</span>
                <div class="small">
                    <strong>Total Records:</strong> {{ $movements->count() }}<br>
                    <strong>Incoming (IN):</strong> {{ $movements->where('type', 'in')->count() }}<br>
                    <strong>Outgoing (OUT):</strong> {{ $movements->where('type', 'out')->count() }}
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table-pro">
            <thead>
                <tr>
                    <th style="width: 40px;">TYPE</th>
                    <th style="width: 90px;">DATE & TIME</th>
                    <th style="width: 150px;">PRODUCT/ITEM</th>
                    <th style="width: 40px;" class="text-center">QTY</th>
                    <th>HANDLER (PERSON)</th>
                    <th>CONTEXT (SOURCE/RECIPIENT)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movements as $movement)
                    <tr>
                        <td class="text-center">
                            @if($movement->type === 'in')
                                <span class="badge-in">IN</span>
                            @else
                                <span class="badge-out">OUT</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold">{{ $movement->movement_date->format('d/m/Y') }}</div>
                            <div class="small text-muted">{{ $movement->movement_date->format('h:i A') }}</div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $movement->product_name }}</div>
                            @if($movement->unit_price)
                                <div class="small text-muted">{{ number_format($movement->unit_price) }} TZS</div>
                            @endif
                        </td>
                        <td class="text-center fw-bold">{{ $movement->quantity }}</td>
                        <td>
                            <div class="fw-bold">{{ $movement->handler_name }}</div>
                            <div class="small text-muted text-uppercase" style="font-size: 6.5pt;">{{ $movement->handler_type }}</div>
                        </td>
                        <td>
                            @if($movement->type === 'in')
                                <div class="small mb-1"><span class="text-muted">From:</span> <strong>{{ $movement->source_name ?: '-' }}</strong></div>
                                @if($movement->source_identifier)
                                    <div class="small text-muted fst-italic" style="margin-top:-2px;">ID/Phone: {{ $movement->source_identifier }}</div>
                                @endif
                                <div class="small"><span class="text-muted text-uppercase" style="font-size: 6pt;">Type:</span> {{ $movement->source_type ?: '-' }}</div>
                            @else
                                <div class="small mb-1"><span class="text-muted">To:</span> <strong>{{ $movement->recipient_name ?: '-' }}</strong></div>
                                @if($movement->recipient_identifier)
                                    <div class="small text-muted fst-italic" style="margin-top:-2px;">ID/Phone: {{ $movement->recipient_identifier }}</div>
                                @endif
                                <div class="small"><span class="text-muted text-uppercase" style="font-size: 6pt;">Type:</span> {{ $movement->recipient_type ?: '-' }}</div>
                                @if($movement->delivery_method)
                                    <div class="small text-muted fst-italic">via {{ $movement->delivery_method }}</div>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Fixed Footer -->
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
