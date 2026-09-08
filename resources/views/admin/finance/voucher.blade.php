<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Voucher - {{ $expense->id }}</title>
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

        .voucher-header-title {
            text-align: right;
        }

        .voucher-header-title h2 {
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

        .amount-header-box .label {
            font-size: 9pt;
            color: #888;
            text-transform: uppercase;
            font-weight: 600;
        }

        .amount-header-box .amount {
            font-weight: 800;
            font-size: 18pt;
            color: #d63031; /* Expense red */
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .voucher-meta h3 {
            font-size: 10pt;
            color: #666;
            margin: 0 0 5px 0;
            font-weight: 400;
        }

        .voucher-meta .detail-val {
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

        .voucher-summary {
            background-color: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .voucher-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dotted #ccc;
        }

        .voucher-row:last-child {
            border-bottom: none;
        }

        .voucher-label {
            font-weight: 500;
            color: #555;
        }

        .voucher-value {
            font-weight: 700;
            color: #000;
        }

        .authorized-section {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
        }

        .sign-box {
            text-align: center;
            width: 250px;
        }

        .sign-line {
            width: 100%;
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
                padding: 15mm 15mm 140px 15mm !important;
                margin: 0 !important;
                background: white;
            }
            .no-print { display: none !important; }
            .invoice-wrapper { width: 100%; max-width: 100%; padding: 0; margin: 0; }
            
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
                border-top: 1px solid #eee !important;
                padding-top: 15px !important;
                font-size: 8pt !important;
            }
        }

        .footer-container {
            margin-top: 80px;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-top: 1px solid #eee;
            padding-top: 15px;
            font-size: 8pt;
        }

        .dev-credit {
            font-size: 7.5pt;
            color: #666;
            text-align: center;
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

    <div class="no-print py-1 px-3 bg-primary text-white d-flex justify-content-between align-items-center mb-3">
        <small class="fw-semibold"><i class="fas fa-print me-1"></i> PAYMENT VOUCHER PREVIEW</small>
        <div class="d-flex gap-1">
            <button class="btn btn-light btn-sm fw-bold px-3" onclick="window.print()">Print</button>
            <button class="btn btn-outline-light btn-sm px-2" onclick="window.close() || window.history.back()">Close</button>
        </div>
    </div>

    <div class="invoice-wrapper">
        <div class="header-section">
            <div class="company-header-info">
                @include('partials.logo-print', ['logoStyle' => 'height:60px;margin-bottom:10px;object-fit:contain;'])
                <h1>Chibo Brands Co Ltd</h1>
                <div>Kinondoni Dar es Salaam 14108</div>
                <div>Tanzania</div>
                <div>+255 753 883 382</div>
                <div>www.chibobrands.com</div>
            </div>
            <div class="voucher-header-title">
                <h2>Payment Voucher</h2>
                <div class="customer-id-header" style="font-size: 10pt; color: #666;">Voucher # {{ $expense->id }}</div>
                <div class="amount-header-box">
                    <div class="label">Total Amount</div>
                    <div class="amount">TZS {{ number_format($expense->amount, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="voucher-meta">
                <h3>Payment Details</h3>
                <div class="detail-val">{{ strtoupper(str_replace('_', ' ', $expense->category)) }}</div>
                <div class="text-muted small">Department: {{ $expense->department->name ?? 'General' }}</div>
            </div>
            <div class="invoice-details">
                <table>
                    <tr>
                        <td>Date :</td>
                        <td>{{ $expense->date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td>Method :</td>
                        <td>{{ $expense->payment_method }}</td>
                    </tr>
                    <tr>
                        <td>Status :</td>
                        <td class="text-success fw-bold">PAID</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="voucher-summary mt-4">
            <h4 class="h6 fw-bold border-bottom pb-2 mb-3">Voucher Breakdown</h4>
            <div class="voucher-row">
                <span class="voucher-label">Description / Purpose:</span>
                <span class="voucher-value">{{ $expense->notes ?: 'Official expenses' }}</span>
            </div>
            <div class="voucher-row">
                <span class="voucher-label">Department:</span>
                <span class="voucher-value">{{ $expense->department->name ?? 'General' }}</span>
            </div>
            <div class="voucher-row">
                <span class="voucher-label">Total Amount:</span>
                <span class="voucher-value">TZS {{ number_format($expense->amount, 2) }}</span>
            </div>
        </div>

        <div class="authorized-section">
            <div class="sign-box">
                <p class="mb-0 small">Prepared By</p>
                <div class="sign-line"></div>
                <p class="text-dark fw-bold small">{{ $expense->approvedBy->name ?? 'System' }}</p>
            </div>
            <div class="sign-box">
                <p class="mb-0 small">Receiver Signature</p>
                <div class="sign-line"></div>
                <p class="text-muted small">Name & ID</p>
            </div>
            <div class="sign-box">
                <p class="mb-0 small">Approved By</p>
                <div class="sign-line"></div>
                <p class="text-muted small">Manager Signature</p>
            </div>
        </div>

        <div class="footer-container">
            <div class="footer-content">
                <div class="legal-info" style="width: 50%;">
                    <h4 style="font-size: 8.5pt; font-weight: 700; margin: 0 0 5px 0; text-transform: uppercase;">Note</h4>
                    <div class="text-muted" style="font-size: 8pt; line-height: 1.4;">
                        This is a system generated payment voucher. 
                        It serves as official proof of cash outflow from Chibo Brands CO LTD.
                    </div>
                </div>
                <div class="company-contact text-end" style="width: 40%;">
                    <div class="name fw-bold">Chibo Brands Company Limited</div>
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
