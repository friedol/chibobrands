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

        .invoice-wrapper {
            max-width: 850px;
            margin: 0 auto;
            padding: 10px 15px;
        }

        /* Header Layout */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
        }

        .company-header-info {
            font-size: 9pt;
            line-height: 1.4;
        }

        .company-header-info h1 {
            font-weight: 800;
            font-size: 14pt;
            margin: 0 0 5px 0;
            color: #d63031; /* Chibo Red */
        }

        .invoice-header-title {
            text-align: right;
        }

        .invoice-header-title h2 {
            font-size: 24pt;
            font-weight: 800;
            margin: 0;
            color: #333;
            text-transform: uppercase;
            letter-spacing: -1px;
        }

        .balance-due-header-box {
            margin-top: 10px;
        }

        .balance-due-header-box .amount {
            font-weight: 800;
            font-size: 18pt;
            color: #d63031; /* Chibo Red */
        }

        /* Billing and Info Section */
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .bill-to h3 {
            font-size: 10pt;
            color: #666;
            margin: 0 0 5px 0;
            font-weight: 400;
        }

        .bill-to .customer-name {
            font-weight: 700;
            font-size: 11pt;
            color: #000;
        }

        .invoice-details table {
            border-collapse: collapse;
        }

        .invoice-details td {
            padding: 2px 10px;
            font-size: 9.5pt;
        }

        .invoice-details td:first-child {
            text-align: right;
            color: #555;
        }

        .invoice-details td:last-child {
            text-align: right;
            font-weight: 500;
        }

        /* Table Design */
        .table-pro {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .table-pro th {
            background-color: #3f3f3f !important;
            padding: 10px;
            font-weight: 500;
            font-size: 10pt;
            color: #fff;
            border: none;
        }

        .table-pro td {
            border-bottom: 1px solid #eee;
            padding: 12px 10px;
            vertical-align: middle;
            font-size: 10pt;
        }

        .table-pro tr:last-child td {
            border-bottom: 2px solid #ccc;
        }

        /* Financials */
        .financial-summary {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 40px;
        }

        .summary-box {
            width: 320px;
        }

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

        .summary-row.balance-due-row {
            background-color: #f5f5f5;
            padding: 12px 10px;
            margin-top: 10px;
            font-weight: 700;
            font-size: 11pt;
            color: #d63031; /* Chibo Red */
        }

        /* Footer */
        .notes-section {
            margin-top: 30px;
            font-size: 10pt;
        }

        .notes-section h3 {
            font-size: 11pt;
            color: #333;
            margin-bottom: 8px;
            font-weight: 400;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            font-size: 8pt;
            line-height: 1.4;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }

        .payment-info h4 {
            font-size: 8.5pt;
            font-weight: 700;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }

        .company-contact {
            text-align: right;
        }

        .company-contact .name {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8.5pt;
        }

        .dev-credit {
            font-size: 7.5pt;
            color: #666;
            text-align: center;
            line-height: 1.4;
            margin-top: 15px;
        }

        .dev-credit a {
            color: #444 !important;
            text-decoration: none;
            font-weight: 600;
        }

        @media print {
            @page {
                size: A4;
                margin: 0mm; /* Using body padding for margins */
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                padding: 15mm 15mm 140px 15mm !important; /* Bottom padding for footer space */
                margin: 0 !important;
                background: white;
            }
            .no-print { display: none !important; }
            .invoice-wrapper { width: 100%; max-width: 100%; padding: 0; margin: 0; }
            
            /* Pin footer to absolute bottom of every page */
            .footer-container {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                width: 100%;
                background: white !important;
                padding: 0 15mm 10mm 15mm;
                z-index: 1000;
            }

            .footer-content {
                display: flex;
                justify-content: space-between;
                align-items: flex-end;
                margin-top: 0 !important;
                border-top: 1px solid #eee !important;
                padding-top: 15px !important;
                font-size: 8pt !important;
            }

            .dev-credit {
                margin-top: 10px !important;
                border-top: none;
                padding-top: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print py-1 px-3 bg-white text-dark d-flex justify-content-between align-items-center mb-3 border-bottom">
        <small class="fw-semibold text-dark"><i class="fas fa-print me-1 text-primary"></i> PROFESSIONAL PRINT PREVIEW</small>
        <div class="d-flex gap-1">
            <button class="btn btn-dark btn-sm fw-bold px-3" onclick="window.print()">Print</button>
            <button class="btn btn-outline-dark btn-sm px-2" onclick="window.close()">Close</button>
        </div>
    </div>

    <div class="invoice-wrapper">
        <!-- Header -->
        <div class="header-section">
            <div class="company-header-info">
                @include('partials.logo-print', ['logoStyle' => 'height:60px;margin-bottom:10px;object-fit:contain;'])
                <h1>Chibo Brands Co Ltd</h1>
                <div>Kinondoni Dar es Salaam 14108</div>
                <div>Tanzania</div>
                <div>+255 753 883 382</div>
                <div>www.chibobrands.com</div>
            </div>
            <div class="invoice-header-title">
                <h2>Invoice</h2>
                <div class="customer-id-header" style="font-size: 10pt; color: #666;"># {{ strtolower($customer->name) }}</div>
                <div class="balance-due-header-box">
                    <div class="label" style="font-size: 9pt; color: #888; text-transform: uppercase; font-weight: 600;">Balance Due</div>
                    <div class="amount">TZS {{ number_format(($tasks->sum('price') + ($tasks->where('requires_receipt', true)->sum('price') * 0.18)) - $tasks->sum('amount_paid'), 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Meta -->
        <div class="info-section">
            <div class="bill-to">
                <h3>Bill To</h3>
                <div class="customer-name">{{ $customer->name }}</div>
            </div>
            <div class="invoice-details">
                <table>
                    <tr>
                        <td>Invoice Date :</td>
                        <td>{{ now()->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td>Terms :</td>
                        <td>Due on Receipt</td>
                    </tr>
                    <tr>
                        <td>Due Date :</td>
                        <td>{{ now()->format('d M Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table-pro">
            <thead style="display: table-header-group;">
                <tr>
                    <th class="text-center" style="width: 40px;">#</th>
                    <th class="text-start">Item & Description</th>
                    <th class="text-end" style="width: 80px;">Qty</th>
                    <th class="text-end" style="width: 120px;">Rate</th>
                    <th class="text-end" style="width: 140px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $totalPrice = 0; 
                    $totalVat = 0;
                    $totalPaid = 0;
                @endphp
                @foreach($tasks as $index => $task)
                    @php
                        // Use price as the base price. For older records, rate might be 0 but price is set.
                        // For newer records, price is already qty * rate.
                        $basePrice = $task->price;
                        $taskVat = $task->requires_receipt ? $basePrice * 0.18 : 0;
                        $totalPrice += $basePrice;
                        $totalVat += $taskVat;
                        $totalPaid += $task->amount_paid;
                        
                        // Calculate display rate if it's 0 but price exists
                        $displayRate = $task->rate > 0 ? $task->rate : ($task->qty > 0 ? $task->price / $task->qty : 0);
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-start">
                            <div class="fw-bold">{{ $task->title }}</div>
                            @if($task->description)
                                <div class="small text-muted" style="font-size: 8.5pt;">{{ Str::limit($task->description, 200) }}</div>
                            @endif
                        </td>
                        <td class="text-end">{{ number_format($task->qty, 2) }}</td>
                        <td class="text-end">{{ number_format($displayRate, 2) }}</td>
                        <td class="text-end">{{ number_format($basePrice, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot style="display: table-footer-group;">
                <tr>
                    <td colspan="5" style="border: none; padding: 0;">
                        <div style="height: 25mm;"></div> <!-- Reserve space for fixed footer -->
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Summary -->
        <div class="financial-summary">
            <div class="summary-box">
                <div class="summary-row">
                    <span>Sub Total</span>
                    <span>{{ number_format($totalPrice, 2) }}</span>
                </div>
                @if($totalVat > 0)
                <div class="summary-row">
                    <span>VAT (18%)</span>
                    <span>{{ number_format($totalVat, 2) }}</span>
                </div>
                @endif
                <div class="summary-row grand-total mt-1">
                    <span>Total</span>
                    <span>TZS{{ number_format($totalPrice + $totalVat, 2) }}</span>
                </div>
                <div class="summary-row balance-due-row">
                    <span>Balance Due</span>
                    <span>TZS{{ number_format(($totalPrice + $totalVat) - $totalPaid, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="notes-section">
            <h3>Notes</h3>
            <div>Thanks for your business.</div>
        </div>

        <!-- Footer Container -->
        <div class="footer-container">
            <div class="footer-content">
                <div class="payment-info" style="width: 50%;">
                    <h4 class="fw-bold text-uppercase mb-2" style="font-size: 8.5pt;">Payment Method</h4>
                    <div class="mb-1">ACCOUNT NAME: CHIBOBRANDS COMPANY LTD</div>
                    <div class="mb-1">ACC NO: 0150637915500</div>
                    <div class="mb-1">CRDB BANK</div>
                    <div class="mt-2">
                        <strong>M-PESA LIPA:</strong> 51212697 CHIBOBRANDS
                    </div>
                </div>
                <div class="company-contact text-end" style="width: 50%;">
                    <div class="name fw-bold text-uppercase mb-2" style="font-size: 8.5pt;">Chibo Brands Company Limited</div>
                    <div class="mb-1">Kinondoni Studio Opposite Vijana House</div>
                    <div class="mb-1">P.O.BOX 77773</div>
                    <div class="mb-1">MOB: 0753 883 382</div>
                    <div class="mb-0">EMAIL: info@chibobrands.com</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
          
            if (window.location.search.includes('print=true') && window.self === window.top) {
                setTimeout(() => { window.print(); }, 500);
            }
        };
    </script>
</body>
</html>
