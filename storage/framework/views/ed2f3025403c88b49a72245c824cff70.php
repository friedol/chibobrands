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
            border-bottom: 2px solid #222;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .brand-logo {
            height: 50px;
            margin-bottom: 5px;
        }

        .company-name {
            font-weight: 800;
            font-size: 14pt;
            letter-spacing: -0.5px;
            margin: 0;
            color: #222;
        }

        .report-title {
            font-weight: 800;
            font-size: 16pt;
            text-transform: uppercase;
            margin: 0;
            text-align: right;
            color: #444;
        }

        /* Meta Information */
        .meta-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 30px;
        }

        .section-label {
            font-weight: 800;
            font-size: 8pt;
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
            margin-bottom: 20px;
        }

        .table-pro th {
            background-color: #f8f8f8 !important;
            border: 1px solid #ddd;
            padding: 8px 10px;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 8pt;
            color: #444;
        }

        .table-pro td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            vertical-align: middle;
        }

        /* Footer Positioning */
        .print-footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .dev-credit {
            font-size: 7.5pt;
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
                font-size: 7.5pt;
                color: #888;
            }
        }
    </style>
</head>
<body>

    <div class="no-print p-3 bg-dark text-white d-flex justify-content-between align-items-center mb-4">
        <div class="ms-2">
            <h6 class="mb-0 fw-bold"><i class="fas fa-print me-2"></i> PENDING PAYMENTS PRINT PREVIEW</h6>
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
                    <img src="<?php echo e(asset('images/logo.webp')); ?>" alt="BRAND LOGO" class="brand-logo" onerror="this.style.display='none'">
                    <h1 class="company-name">CHIBOBRAND CO. LTD.</h1>
                </div>
                <div class="col-5 text-end">
                    <h2 class="report-title">Outstanding Payments</h2>
                    <p class="mb-0 fw-bold">GENERATED: <?php echo e(now()->format('d/m/Y H:i')); ?></p>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="meta-container">
            <div>
                <span class="section-label">Report Status</span>
                <div class="fw-bold">
                    <?php if($search): ?>
                        Filtered Results: "<?php echo e($search); ?>"
                    <?php else: ?>
                        All Pending Payments
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-end">
                <span class="section-label">Financial Summary</span>
                <div class="small">
                    <?php
                        $totalBalance = $pendingTasks->sum('balance') + $pendingOrders->sum('balance');
                    ?>
                    <strong>Total Outstanding:</strong> TZS <?php echo e(number_format($totalBalance)); ?><br>
                    <strong>Design Tasks:</strong> <?php echo e($pendingTasks->count()); ?> (TZS <?php echo e(number_format($pendingTasks->sum('balance'))); ?>)<br>
                    <strong>Product Orders:</strong> <?php echo e($pendingOrders->count()); ?> (TZS <?php echo e(number_format($pendingOrders->sum('balance'))); ?>)
                </div>
            </div>
        </div>

        <?php if($pendingTasks->count() > 0): ?>
            <div class="mb-4">
                <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3" style="font-size: 10pt; color: #2C3E50;">
                    <i class="fas fa-palette me-2"></i>Design Tasks
                </h6>
                <table class="table-pro">
                    <thead>
                        <tr>
                            <th style="width: 120px;">TASK CODE</th>
                            <th>TASK TITLE</th>
                            <th>CUSTOMER</th>
                            <th class="text-end">TOTAL</th>
                            <th class="text-end">PAID</th>
                            <th class="text-end">BALANCE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $pendingTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $basePrice = $task->requires_receipt ? $task->price * 1.18 : $task->price;
                                $deliveryCost = (float) ($task->delivery_cost ?? 0);
                                $deliveryDiscount = (float) ($task->delivery_discount ?? 0);
                                $taskTotal = $basePrice + $deliveryCost - $deliveryDiscount;
                            ?>
                            <tr>
                                <td class="fw-bold"><?php echo e($task->task_code); ?></td>
                                <td><?php echo e($task->title); ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo e($task->customer->name ?? 'Walk-in'); ?></div>
                                    <div class="text-muted small"><?php echo e($task->customer->phone ?? ''); ?></div>
                                </td>
                                <td class="text-end"><?php echo e(number_format($taskTotal)); ?></td>
                                <td class="text-end text-success"><?php echo e(number_format($task->amount_paid)); ?></td>
                                <td class="text-end fw-bold text-danger"><?php echo e(number_format($task->balance)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <tr style="background-color: #f8f8f8; border-top: 2px solid #222;">
                            <td colspan="3" class="text-end fw-bold" style="padding: 10px;">TOTALS:</td>
                            <td class="text-end fw-bold" style="padding: 10px;">
                                <?php
                                    $tasksTotalSum = 0;
                                    foreach($pendingTasks as $t) {
                                        $base = $t->requires_receipt ? $t->price * 1.18 : $t->price;
                                        $tasksTotalSum += $base + (float)($t->delivery_cost ?? 0) - (float)($t->delivery_discount ?? 0);
                                    }
                                ?>
                                <?php echo e(number_format($tasksTotalSum)); ?>

                            </td>
                            <td class="text-end fw-bold text-success" style="padding: 10px;"><?php echo e(number_format($pendingTasks->sum('amount_paid'))); ?></td>
                            <td class="text-end fw-bold text-danger" style="padding: 10px;"><?php echo e(number_format($pendingTasks->sum('balance'))); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if($pendingOrders->count() > 0): ?>
            <div class="mb-4">
                <h6 class="fw-bold text-uppercase border-bottom pb-2 mb-3" style="font-size: 10pt; color: #2C3E50;">
                    <i class="fas fa-shopping-cart me-2"></i>Product Orders
                </h6>
                <table class="table-pro">
                    <thead>
                        <tr>
                            <th style="width: 120px;">ORDER CODE</th>
                            <th>CUSTOMER</th>
                            <th>DATE</th>
                            <th class="text-end">TOTAL</th>
                            <th class="text-end">PAID</th>
                            <th class="text-end">BALANCE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $pendingOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="fw-bold"><?php echo e($order->order_code); ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo e($order->user->name ?? 'Walk-in'); ?></div>
                                    <div class="text-muted small"><?php echo e($order->user->phone ?? ''); ?></div>
                                </td>
                                <td><?php echo e($order->created_at->format('d/m/Y')); ?></td>
                                <td class="text-end"><?php echo e(number_format($order->total_amount)); ?></td>
                                <td class="text-end text-success"><?php echo e(number_format($order->amount_paid)); ?></td>
                                <td class="text-end fw-bold text-danger"><?php echo e(number_format($order->balance)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <tr style="background-color: #f8f8f8; border-top: 2px solid #222;">
                            <td colspan="3" class="text-end fw-bold" style="padding: 10px;">TOTALS:</td>
                            <td class="text-end fw-bold" style="padding: 10px;"><?php echo e(number_format($pendingOrders->sum('total_amount'))); ?></td>
                            <td class="text-end fw-bold text-success" style="padding: 10px;"><?php echo e(number_format($pendingOrders->sum('amount_paid'))); ?></td>
                            <td class="text-end fw-bold text-danger" style="padding: 10px;"><?php echo e(number_format($pendingOrders->sum('balance'))); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

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
                setTimeout(() => { window.print(); }, 500);
            }
        };
    </script>
</body>
</html>
<?php /**PATH /Users/gotlaptopparts.com/Downloads/chibo_sales/resources/views/admin/finance/print-pending.blade.php ENDPATH**/ ?>