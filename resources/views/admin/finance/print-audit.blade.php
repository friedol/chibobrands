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

        .section-header {
            background-color: #dc3545;
            color: white;
            padding: 8px 12px;
            font-weight: 800;
            font-size: 9pt;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .section-header.warning {
            background-color: #ffc107;
            color: #222;
        }

        .section-header.info {
            background-color: #0d6efd;
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

    <div class="no-print p-3 bg-danger text-white d-flex justify-content-between align-items-center mb-4">
        <div class="ms-2">
            <h6 class="mb-0 fw-bold"><i class="fas fa-print me-2"></i> FINANCIAL AUDIT REPORT PREVIEW</h6>
        </div>
        <div class="me-2">
            <button class="btn btn-light fw-bold px-4 text-danger" onclick="window.print()">PRINT NOW</button>
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
                    <h2 class="report-title">Financial Audit Report</h2>
                    <p class="mb-0 fw-bold">GENERATED: {{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Meta -->
        @php
            $totalIssues = $unbalancedOrders->count() + $unbalancedTasks->count() + $missingOrderPayments->count() + $mismatchedOrders->count() + $completedWithBalance->count();
        @endphp

        <div class="meta-container">
            <div>
                <span class="section-label">Audit Status</span>
                <div class="fw-bold">
                    @if($totalIssues > 0)
                        <span style="color: #dc3545;">{{ $totalIssues }} ANOMALIES DETECTED</span>
                    @else
                        <span style="color: #198754;">SYSTEM BALANCED</span>
                    @endif
                </div>
            </div>
            <div class="text-end">
                <span class="section-label">Report Date</span>
                <div class="small fw-bold">
                    {{ now()->format('d M Y') }}
                </div>
            </div>
        </div>

        <!-- 1. Unbalanced Transactions -->
        @if($unbalancedOrders->count() > 0 || $unbalancedTasks->count() > 0)
        <div class="section-header">
            <i class="fas fa-scale-unbalanced me-2"></i> UNBALANCED TRANSACTIONS ({{ $unbalancedOrders->count() + $unbalancedTasks->count() }})
        </div>
        <table class="table-pro">
            <thead>
                <tr>
                    <th style="width: 120px;">REFERENCE</th>
                    <th style="width: 150px;">CUSTOMER</th>
                    <th style="width: 80px;">TYPE</th>
                    <th style="width: 100px;" class="text-end">SHOULD PAY</th>
                    <th style="width: 100px;" class="text-end">PAID + BAL</th>
                    <th style="width: 100px;" class="text-end">DIFFERENCE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($unbalancedOrders as $order)
                <tr>
                    <td class="fw-bold">{{ $order->order_code }}</td>
                    <td class="fw-bold">{{ $order->user->name ?? 'Guest' }}</td>
                    <td>Order</td>
                    <td class="text-end">{{ number_format($order->total_amount) }}</td>
                    <td class="text-end">{{ number_format($order->amount_paid + $order->balance) }}</td>
                    <td class="text-end fw-bold" style="color: #dc3545;">
                        {{ number_format(abs($order->total_amount - ($order->amount_paid + $order->balance))) }}
                    </td>
                </tr>
                @endforeach
                @foreach($unbalancedTasks as $task)
                @php
                    $taskTotal = $task->requires_receipt ? $task->price * 1.18 : $task->price;
                @endphp
                <tr>
                    <td class="fw-bold">{{ $task->task_code }}</td>
                    <td class="fw-bold">{{ $task->customer->name ?? '---' }}</td>
                    <td>Design Task {!! $task->requires_receipt ? '<br><small style="font-size: 7pt; color: #666;">(Incl. 18% VAT)</small>' : '' !!}</td>
                    <td class="text-end">{{ number_format($taskTotal) }}</td>
                    <td class="text-end">{{ number_format($task->amount_paid + $task->balance) }}</td>
                    <td class="text-end fw-bold" style="color: #dc3545;">
                        {{ number_format(abs($taskTotal - ($task->amount_paid + $task->balance))) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- 2. Missing Payments -->
        @if($missingOrderPayments->count() > 0 || $completedWithBalance->count() > 0)
        <div class="section-header warning">
            <i class="fas fa-clock me-2"></i> MISSING PAYMENTS FLAG ({{ $missingOrderPayments->count() + $completedWithBalance->count() }})
        </div>
        <table class="table-pro">
            <thead>
                <tr>
                    <th style="width: 120px;">REFERENCE</th>
                    <th style="width: 150px;">CUSTOMER</th>
                    <th style="width: 80px;">CREATED</th>
                    <th style="width: 90px;">STATUS</th>
                    <th style="width: 95px;" class="text-end">TOTAL COST</th>
                    <th style="width: 95px;" class="text-end">BALANCE DUE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($missingOrderPayments as $order)
                <tr>
                    <td class="fw-bold">{{ $order->order_code }}</td>
                    <td class="fw-bold">{{ $order->user->name ?? 'Guest' }}</td>
                    <td>{{ $order->created_at->format('d M Y') }}</td>
                    <td style="color: #dc3545; font-weight: 700;">NO PAYMENT</td>
                    <td class="text-end">{{ number_format($order->total_amount) }}</td>
                    <td class="text-end fw-bold" style="color: #dc3545;">{{ number_format($order->total_amount) }}</td>
                </tr>
                @endforeach
                @foreach($completedWithBalance as $task)
                @php
                    $taskTotal = $task->requires_receipt ? $task->price * 1.18 : $task->price;
                @endphp
                <tr>
                    <td class="fw-bold">{{ $task->task_code }}</td>
                    <td class="fw-bold">{{ $task->customer->name ?? '---' }}</td>
                    <td>{{ $task->created_at->format('d M Y') }}</td>
                    <td style="color: #ffc107; font-weight: 700;">UNPAID BAL</td>
                    <td class="text-end">{{ number_format($taskTotal) }} {!! $task->requires_receipt ? '<br><small style="font-size: 7pt; color: #666;">(Incl. VAT)</small>' : '' !!}</td>
                    <td class="text-end fw-bold" style="color: #dc3545;">{{ number_format($task->balance) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- 3. Status Mismatch -->
        @if($mismatchedOrders->count() > 0)
        <div class="section-header info">
            <i class="fas fa-file-invoice-dollar me-2"></i> STATUS & LOGIC MISMATCH ({{ $mismatchedOrders->count() }})
        </div>
        <table class="table-pro">
            <thead>
                <tr>
                    <th style="width: 120px;">ORDER REF</th>
                    <th style="width: 150px;">CUSTOMER</th>
                    <th style="width: 100px;">PAYMENT STATUS</th>
                    <th>LOGIC ERROR DETECTED</th>
                    <th style="width: 100px;" class="text-end">BALANCE (TZS)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mismatchedOrders as $order)
                <tr>
                    <td class="fw-bold">{{ $order->order_code }}</td>
                    <td>{{ $order->user->name ?? '---' }}</td>
                    <td class="text-uppercase fw-bold">{{ $order->payment_status }}</td>
                    <td style="color: #dc3545; font-weight: 700; font-style: italic;">
                        @if($order->payment_status === 'paid' && $order->balance > 0)
                            <i class="fas fa-bug me-1"></i> Marked PAID but has outstanding balance!
                        @elseif($order->payment_status === 'unpaid' && $order->amount_paid > 0)
                            <i class="fas fa-bug me-1"></i> Marked UNPAID but has active payments!
                        @endif
                    </td>
                    <td class="text-end fw-bold">{{ number_format($order->balance) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($totalIssues == 0)
        <div class="text-center py-5">
            <i class="fas fa-check-circle" style="font-size: 48pt; color: #198754; opacity: 0.3;"></i>
            <p class="fw-bold mt-3" style="font-size: 11pt;">NO ANOMALIES DETECTED</p>
            <p class="text-muted" style="font-size: 8pt;">All transactions are balanced and properly recorded.</p>
        </div>
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
