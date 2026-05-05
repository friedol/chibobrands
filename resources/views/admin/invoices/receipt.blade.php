<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $payment->id }}</title>
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

        .receipt-header-title {
            text-align: right;
        }

        .receipt-header-title h2 {
            font-size: 24pt;
            font-weight: 600;
            margin: 0;
            color: #333;
            text-transform: uppercase;
            letter-spacing: -1px;
        }

        .amount-header-box {
            margin-top: 10px;
        }

        .amount-header-box .amount {
            font-weight: 800;
            font-size: 18pt;
            color: #198754; /* Success Green for payments */
        }

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

        .payment-summary {
            background-color: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dotted #ccc;
        }

        .payment-row:last-child {
            border-bottom: none;
        }

        .payment-label {
            font-weight: 500;
            color: #555;
        }

        .payment-value {
            font-weight: 700;
            color: #000;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
            font-size: 9.5pt;
            line-height: 1.5;
        }

        .company-contact {
            text-align: right;
        }

        .company-contact .name {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10pt;
        }

        .authorized-sign {
            margin-top: 40px;
            text-align: center;
        }

        .sign-line {
            width: 200px;
            border-top: 1px solid #000;
            margin: 10px auto;
        }

        @media print {
            @page {
                size: A4;
                margin: 0mm;
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                padding: 15mm 15mm 140px 15mm !important; /* Space for footer */
                margin: 0 !important;
                background: white;
            }
            .no-print { display: none !important; }
            .invoice-wrapper { width: 100%; max-width: 100%; padding: 0; margin: 0; }

            .payment-summary, .notes-section, .authorized-sign {
                page-break-inside: avoid;
            }
            
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
    </style>
</head>
<body>

    <div class="no-print p-3 bg-primary text-white d-flex justify-content-between align-items-center mb-4">
        <div class="ms-2">
            <h6 class="mb-0 "><i class="fas fa-print me-2"></i> PAYMENT RECEIPT PREVIEW</h6>
        </div>
        <div class="me-2">
            <button class="btn btn-light fw-bold px-4" onclick="window.print()">PRINT NOW</button>
            <button class="btn btn-outline-light ms-2" onclick="window.close() || window.history.back()">CLOSE</button>
        </div>
    </div>

    <div class="invoice-wrapper">
        <div class="header-section">
            <div class="company-header-info">
                <img src="{{ asset('images/logo.png') }}" alt="CHIBO BRANDS" style="height: 60px; margin-bottom: 10px;" onerror="this.src='{{ asset('images/logo.webp') }}'">
                <h1>Chibo Brands Co Ltd</h1>
                <div>Kinondoni Dar es Salaam 14108</div>
                <div>Tanzania</div>
                <div>+255 753 883 382</div>
                <div>www.chibobrand.com</div>
            </div>
            <div class="receipt-header-title">
                <h2>Payment Receipt</h2>
                <div class="customer-id-header" style="font-size: 10pt; color: #666;">Receipt # {{ $payment->id }}</div>
                <div class="amount-header-box">
                    <div class="label" style="font-size: 9pt; color: #888; text-transform: uppercase; font-weight: 600;">Amount Paid</div>
                    <div class="amount">TZS {{ number_format($payment->amount, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="bill-to">
                <h3>Received From</h3>
                <div class="customer-name">{{ $payment->customer->name ?? 'N/A' }}</div>
                <div>{{ $payment->customer->phone ?? 'N/A' }}</div>
                @if($payment->customer->company_name)
                    <div>{{ $payment->customer->company_name }}</div>
                @endif
            </div>
            <div class="invoice-details">
                <table>
                    <tr>
                        <td>Date :</td>
                        <td>{{ $payment->date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td>Payment Method :</td>
                        <td>{{ $payment->payment_method }}</td>
                    </tr>
                    <tr>
                        <td>Reference :</td>
                        <td>{{ $payment->reference ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="payment-summary mt-4">
            <h4 class="h6 fw-bold border-bottom pb-2 mb-3">Payment Distribution</h4>
            @if($payment->order)
            <div class="payment-row">
                <span class="payment-label">Applied to Order:</span>
                <span class="payment-value">#{{ $payment->order->order_code }}</span>
            </div>
            @endif
            @if($payment->design_task)
            <div class="payment-row">
                <span class="payment-label">Applied to Task:</span>
                <span class="payment-value">{{ $payment->design_task->title }}</span>
            </div>
            @endif
            <div class="payment-row">
                <span class="payment-label">Department:</span>
                <span class="payment-value">{{ $payment->department->name ?? 'General' }}</span>
            </div>
            <div class="payment-row">
                <span class="payment-label">Received By:</span>
                <span class="payment-value">{{ $payment->seller->name ?? 'System' }}</span>
            </div>
        </div>

        <div class="row">
            <div class="col-8">
                <div class="notes-section">
                    <h3>Important Notice</h3>
                    <div class="text-muted">This is an official proof of payment. Please keep it for your records. All payments are non-refundable unless stated otherwise.</div>
                </div>
            </div>
            <div class="col-4">
                <div class="authorized-sign">
                    <p class="mb-0 small">Authorized Signature</p>
                    <div class="sign-line"></div>
                    <p class="text-muted small">Chibo Brands Co Ltd</p>
                </div>
            </div>
        </div>

        <div class="footer-container">
            <div class="footer-content">
                <div class="payment-info" style="width: 50%;">
                    <h4 style="font-size: 8.5pt; font-weight: 700; margin: 0 0 5px 0; text-transform: uppercase;">Legal Notice</h4>
                    <div class="text-muted" style="font-size: 8pt; line-height: 1.4;">
                        This is an official proof of payment. Please keep it for your records. 
                        All payments are subject to company terms and conditions.
                    </div>
                </div>
                <div class="company-contact text-end" style="width: 50%;">
                    <div class="name">Chibo Brands Company Limited</div>
                    <div>Kinondoni Studio Opposite Vijana House</div>
                    <div>P.O.BOX 77773 | MOB: 0753 883 382</div>
                    <div>EMAIL: info@chibobrands.com</div>
                </div>
            </div>

            <div class="dev-credit">
                Developed by <a href="https://www.fridoltech.org" target="_blank">Fridoltech</a>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            if (window.location.search.includes('print=true')) {
                setTimeout(() => { window.print(); }, 500);
            }
        };
    </script>
</body>
</html>
