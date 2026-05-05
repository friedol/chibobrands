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
            color: #dc3545;
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
            
            .report-header {
                page-break-after: avoid;
                page-break-inside: avoid;
            }
            
            .meta-container {
                page-break-after: avoid;
                page-break-inside: avoid;
            }
            
            .table-pro thead {
                display: table-header-group;
            }
            
            .table-pro tbody {
                display: table-row-group;
            }
            
            .table-pro tr {
                page-break-inside: avoid;
                page-break-after: auto;
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

    <div class="no-print p-3 bg-dark text-white d-flex justify-content-between align-items-center mb-4">
        <div class="ms-2">
            <h6 class="mb-0 fw-bold"><i class="fas fa-print me-2"></i> CASH FLOW STATEMENT PRINT PREVIEW</h6>
        </div>
        <div class="me-2">
            <button class="btn btn-primary fw-bold px-4" onclick="window.print()">PRINT NOW</button>
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
                    <h2 class="report-title">Cash Flow Statement</h2>
                    <p class="mb-0 fw-bold">GENERATED: {{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Meta -->
        @if(isset($view) && $view === 'customers')
        <div class="meta-container">
            <div>
                <span class="section-label">Report Type</span>
                <div class="fw-bold">
                    Customer Balance Sheet
                </div>
            </div>
            <div class="text-end">
                <span class="section-label">Summary</span>
                <div class="small">
                    <strong>Total Customers:</strong> {{ $customers->count() }}<br>
                    <strong>Total Unpaid Balance:</strong> TZS {{ number_format($customers->sum('unpaid_balance')) }}
                </div>
            </div>
        </div>

        <!-- Customer Table -->
        <table class="table-pro">
            <thead>
                <tr>
                    <th style="width: 250px;">CUSTOMER NAME</th>
                    <th style="width: 120px;">PHONE</th>
                    <th>LEAD STATUS</th>
                    <th style="width: 120px;" class="text-end">TOTAL PAID</th>
                    <th style="width: 120px;" class="text-end">UNPAID BALANCE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $customer)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $customer->name }}</div>
                            @if($customer->company_name)
                                <div class="small text-muted">{{ $customer->company_name }}</div>
                            @endif
                        </td>
                        <td>{{ $customer->phone }}</td>
                        <td>
                            @if($customer->lead_status)
                                <span style="font-size: 8pt; text-transform: uppercase;">{{ ucfirst($customer->lead_status) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold text-success">
                            {{ number_format($customer->total_paid) }}
                        </td>
                        <td class="text-end fw-bold text-danger">
                            {{ number_format($customer->unpaid_balance) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="meta-container">
            <div>
                <span class="section-label">Statement Period</span>
                <div class="fw-bold">
                    @if(request('date_from') && request('date_to'))
                        {{ \Carbon\Carbon::parse(request('date_from'))->format('d M Y') }} to {{ \Carbon\Carbon::parse(request('date_to'))->format('d M Y') }}
                    @elseif(request('date_from'))
                        From {{ \Carbon\Carbon::parse(request('date_from'))->format('d M Y') }}
                    @elseif(request('date_to'))
                        Until {{ \Carbon\Carbon::parse(request('date_to'))->format('d M Y') }}
                    @else
                        All Time Financial Records
                    @endif
                </div>
            </div>
            <div class="text-end">
                <span class="section-label">Financial Summary</span>
                <div class="small">
                    <strong>Total Inflow (IN):</strong> TZS {{ number_format($entries->where('entry_type', 'payment')->sum('amount')) }}<br>
                    <strong>Total Outflow (OUT):</strong> TZS {{ number_format($entries->where('entry_type', 'expense')->sum('amount')) }}<br>
                    <hr class="my-1">
                    <strong>Net Cash Flow:</strong> TZS {{ number_format($entries->where('entry_type', 'payment')->sum('amount') - $entries->where('entry_type', 'expense')->sum('amount')) }}
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table-pro">
            <thead>
                <tr>
                    <th style="width: 40px;">FLOW</th>
                    <th style="width: 80px;">DATE</th>
                    <th style="width: 150px;">SOURCE / RECIPIENT</th>
                    <th>DETAILS / DESCRIPTION</th>
                    <th style="width: 80px;">METHOD</th>
                    <th style="width: 100px;" class="text-end">AMOUNT (TZS)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                    <tr>
                        <td class="text-center">
                            @if($entry->entry_type === 'payment')
                                <span class="badge-in">IN</span>
                            @else
                                <span class="badge-out">OUT</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold">{{ $entry->date->format('d/m/Y') }}</div>
                            <div class="small text-muted" style="font-size: 7pt;">{{ $entry->created_at->format('h:i A') }}</div>
                        </td>
                        <td>
                            @if($entry->entry_type === 'payment')
                                <div class="fw-bold">{{ $entry->customer->name ?? 'Unknown' }}</div>
                                @if($entry->customer && $entry->customer->company_name)
                                    <div class="small text-muted">{{ $entry->customer->company_name }}</div>
                                @endif
                            @else
                                <div class="fw-bold text-danger">{{ $entry->category }}</div>
                                <div class="small text-muted">Dept: {{ $entry->department->name ?? 'General' }}</div>
                            @endif
                        </td>
                        <td>
                            @if($entry->entry_type === 'payment')
                                @if($entry->order)
                                    <div class="small">Order #{{ $entry->order->order_code }}</div>
                                @elseif($entry->design_task)
                                    <div class="small">Design Task: {{ $entry->design_task->title }}</div>
                                @else
                                    <span class="small italic">Manual Record</span>
                                @endif
                            @else
                                <div class="small">{{ $entry->notes ?: '-' }}</div>
                            @endif
                        </td>
                        <td class="text-center text-uppercase" style="font-size: 7pt;">
                            {{ str_replace('_', ' ', $entry->payment_method) }}
                        </td>
                        <td class="text-end fw-bold">
                            {{ number_format($entry->amount) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif

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
