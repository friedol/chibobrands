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
            color: #dc2626;
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
            color: #dc2626;
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

        .badge-interest {
            font-size: 7pt;
            font-weight: 700;
            text-transform: uppercase;
            padding: 1px 4px;
            border-radius: 3px;
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
            
            .table-pro {
                margin-bottom: 60px;
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

    <div class="no-print py-1 px-3 bg-primary text-white d-flex justify-content-between align-items-center mb-3">
        <small class="fw-semibold"><i class="fas fa-print me-1"></i> LEADS LOG PRINT PREVIEW</small>
        <div class="d-flex gap-1">
            <button class="btn btn-light btn-sm fw-bold px-3 text-primary" onclick="window.print()">Print</button>
            <button class="btn btn-outline-light btn-sm px-2" onclick="window.close()">Close</button>
        </div>
    </div>

    <div class="log-wrapper">
        <!-- Header -->
        <div class="report-header">
            <div class="row align-items-start">
                <div class="col-7">
                    @include('partials.logo-print')
                    <h1 class="company-name">CHIBOBRAND CO. LTD.</h1>
                </div>
                <div class="col-5 text-end">
                    <h2 class="report-title">Leads Pipeline Report</h2>
                    <p class="mb-0 fw-bold">GENERATED: {{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Meta -->
        <div class="meta-container">
            <div>
                <span class="section-label">Report Period</span>
                <div class="fw-bold">
                    @if(request('date_from') && request('date_to'))
                        {{ \Carbon\Carbon::parse(request('date_from'))->format('d M Y') }} to {{ \Carbon\Carbon::parse(request('date_to'))->format('d M Y') }}
                    @elseif(request('date_from'))
                        From {{ \Carbon\Carbon::parse(request('date_from'))->format('d M Y') }}
                    @elseif(request('date_to'))
                        Until {{ \Carbon\Carbon::parse(request('date_to'))->format('d M Y') }}
                    @else
                        All Leads in Directory
                    @endif
                </div>
            </div>
            <div class="text-end">
                <span class="section-label">Pipeline Summary</span>
                <div class="small">
                    <strong>Total Leads:</strong> {{ $leads->count() }}<br>
                    <strong>Won (Converted):</strong> {{ $leads->where('status', 'converted')->count() }}<br>
                    <strong>Conversion Rate:</strong> {{ $leads->count() > 0 ? number_format(($leads->where('status', 'converted')->count() / $leads->count()) * 100, 1) : 0 }}%
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table-pro">
            <thead>
                <tr>
                    <th style="width: 80px;">DATE</th>
                    <th style="width: 150px;">CUSTOMER</th>
                    <th style="width: 120px;">PRODUCT/SOURE</th>
                    <th style="width: 100px;">INTEREST</th>
                    <th style="width: 80px;">STATUS</th>
                    <th>ASSIGNED SELLER</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leads as $lead)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $lead->created_at->format('d/m/Y') }}</div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $lead->customer_name }}</div>
                            <div class="small text-muted">{{ $lead->phone }}</div>
                        </td>
                        <td>
                            <div class="fw-bold small">{{ $lead->product_requested ?: '-' }}</div>
                            <div class="x-small text-muted">Src: {{ $lead->source ?: 'N/A' }}</div>
                        </td>
                        <td class="text-center">
                            @php
                                $interestStyles = [
                                    'high' => 'bg-danger text-white',
                                    'medium' => 'bg-warning text-dark',
                                    'low' => 'bg-info text-white'
                                ];
                                $style = $interestStyles[strtolower($lead->interest_level)] ?? 'bg-light text-dark';
                            @endphp
                            <span class="badge-interest {{ $style }}">
                                {{ $lead->interest_level ?: 'UNKNOWN' }}
                            </span>
                        </td>
                        <td class="text-center text-uppercase" style="font-size: 7pt; font-weight: 700;">
                            <span class="text-{{ $lead->status == 'converted' ? 'success' : ($lead->status == 'not_interested' ? 'danger' : 'warning') }}">
                                {{ $lead->status }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold small">{{ $lead->seller->name ?? 'Unassigned' }}</div>
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
