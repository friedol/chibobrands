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

    <div class="no-print p-3 bg-danger text-white d-flex justify-content-between align-items-center mb-4">
        <div class="ms-2">
            <h6 class="mb-0 fw-bold"><i class="fas fa-print me-2"></i> EXPENSE LOG PRINT PREVIEW</h6>
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
                    <img src="<?php echo e(asset('images/logo.webp')); ?>" alt="BRAND LOGO" class="brand-logo" onerror="this.style.display='none'">
                    <h1 class="company-name">CHIBOBRAND CO. LTD.</h1>
                </div>
                <div class="col-5 text-end">
                    <h2 class="report-title">Expense Report</h2>
                    <p class="mb-0 fw-bold">GENERATED: <?php echo e(now()->format('d/m/Y H:i')); ?></p>
                </div>
            </div>
        </div>

        <!-- Meta -->
        <div class="meta-container">
            <div>
                <span class="section-label">Report Period</span>
                <div class="fw-bold">
                    <?php if(request('date_from') && request('date_to')): ?>
                        <?php echo e(\Carbon\Carbon::parse(request('date_from'))->format('d M Y')); ?> to <?php echo e(\Carbon\Carbon::parse(request('date_to'))->format('d M Y')); ?>

                    <?php elseif(request('date_from')): ?>
                        From <?php echo e(\Carbon\Carbon::parse(request('date_from'))->format('d M Y')); ?>

                    <?php elseif(request('date_to')): ?>
                        Until <?php echo e(\Carbon\Carbon::parse(request('date_to'))->format('d M Y')); ?>

                    <?php else: ?>
                        All Time Expense Records
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-end">
                <span class="section-label">Summary</span>
                <div class="small">
                    <strong>Total Expenses:</strong> <?php echo e($expenses->count()); ?> records<br>
                    <strong>Grand Total:</strong> TZS <?php echo e(number_format($expenses->sum('amount'))); ?>

                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table-pro">
            <thead>
                <tr>
                    <th style="width: 80px;">DATE</th>
                    <th style="width: 120px;">CATEGORY</th>
                    <th style="width: 120px;">DEPARTMENT</th>
                    <th>NOTES / DESCRIPTION</th>
                    <th style="width: 100px;">METHOD</th>
                    <th style="width: 100px;" class="text-end">AMOUNT (TZS)</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?php echo e($expense->date->format('d/m/Y')); ?></div>
                        </td>
                        <td>
                            <div class="fw-bold text-uppercase" style="font-size: 7.5pt;"><?php echo e($expense->category); ?></div>
                        </td>
                        <td>
                            <div><?php echo e($expense->department->name ?? 'General'); ?></div>
                        </td>
                        <td>
                            <div class="small text-muted"><?php echo e($expense->notes ?: '-'); ?></div>
                        </td>
                        <td class="text-center text-uppercase" style="font-size: 7pt;">
                            <?php echo e(str_replace('_', ' ', $expense->payment_method)); ?>

                        </td>
                        <td class="text-end fw-bold">
                            <?php echo e(number_format($expense->amount)); ?>

                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end fw-bold text-uppercase" style="font-size: 7.5pt; background-color: #f8f8f8;">Grand Total</td>
                    <td class="text-end fw-bold" style="background-color: #f8f8f8;"><?php echo e(number_format($expenses->sum('amount'))); ?></td>
                </tr>
            </tfoot>
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
<?php /**PATH /Users/gotlaptopparts.com/Desktop/LaravelProject/chibo_sales/resources/views/admin/finance/print-expenses.blade.php ENDPATH**/ ?>