<?php $__env->startSection('title', 'Profit and Loss (P&L)'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <style>
        @media print {
            .no-print { display: none !important; }
        }
    </style>
    <?php
        $pdfParams = array_filter([
            'period' => $period ?? null,
            'start_date' => $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('Y-m-d') : null,
            'end_date' => $dateTo ? \Carbon\Carbon::parse($dateTo)->format('Y-m-d') : null,
        ], fn($v) => $v !== null && $v !== '');

        $pdfUrl = route('admin.finance.profit-loss.pdf', $pdfParams);
        $pdfFilename = 'profit-loss-' . ($dateFrom && $dateTo
            ? $dateFrom->format('Y-m-d') . '_to_' . $dateTo->format('Y-m-d')
            : ($period ?? 'report')
        ) . '.pdf';
    ?>

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                <div>
                    <h2 class="fw-bold mb-1">Profit and Loss</h2>
                    <p class="text-muted small mb-0">
                        Basis: <strong><?php echo e($basisLabel); ?></strong>
                        <?php if($dateFrom && $dateTo): ?>
                            · <?php echo e($dateFrom->format('d M, Y')); ?> – <?php echo e($dateTo->format('d M, Y')); ?>

                        <?php endif; ?>
                    </p>
                    <p class="text-muted small mb-0">Amount displayed in base currency: <strong>TZS</strong></p>
                </div>
                <div class="d-flex gap-2 no-print">
                    <a href="<?php echo e($pdfUrl); ?>"
                       target="_blank"
                       rel="noopener"
                       class="btn btn-success btn-sm share-pdf-btn"
                       data-pdf-url="<?php echo e($pdfUrl); ?>"
                       data-pdf-filename="<?php echo e($pdfFilename); ?>"
                       title="Share this Profit & Loss as PDF (email, messaging, etc.).">
                        <i class="fas fa-share-alt me-1"></i> Share PDF
                    </a>

                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="<?php echo e(route('admin.finance.profit-loss')); ?>" method="GET" class="row g-3 align-items-end" data-no-global-handler>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">Period</label>
                    <select name="period" class="form-select form-select-sm">
                        <option value="today" <?php echo e(($period ?? '') === 'today' ? 'selected' : ''); ?>>Today</option>
                        <option value="yesterday" <?php echo e(($period ?? '') === 'yesterday' ? 'selected' : ''); ?>>Yesterday</option>
                        <option value="week" <?php echo e(($period ?? '') === 'week' ? 'selected' : ''); ?>>This Week</option>
                        <option value="month" <?php echo e(($period ?? '') === 'month' ? 'selected' : ''); ?>>This Month</option>
                        <option value="year" <?php echo e(($period ?? '') === 'year' ? 'selected' : ''); ?>>This Year</option>
                        <option value="custom" <?php echo e(($period ?? '') === 'custom' ? 'selected' : ''); ?>>Custom Date</option>
                        <option value="all" <?php echo e(($period ?? '') === 'all' ? 'selected' : ''); ?>>All Time</option>
                    </select>
                </div>

                <div class="col-6 col-md-2 <?php echo e(($period ?? '') === 'custom' ? '' : 'd-none'); ?>">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">From</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="<?php echo e(request('start_date')); ?>">
                </div>
                <div class="col-6 col-md-2 <?php echo e(($period ?? '') === 'custom' ? '' : 'd-none'); ?>">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">To</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="<?php echo e(request('end_date')); ?>">
                </div>

                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-sync-alt me-1"></i> Update
                    </button>
                    <a href="<?php echo e(route('admin.finance.profit-loss')); ?>" class="btn btn-outline-secondary btn-sm">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php
        $net = (float) ($netProfitLoss ?? 0);
        $isLoss = $net < 0;
        $lossOrProfitLabel = $isLoss ? 'Net Loss' : 'Net Profit';
        $displayNetProfitLoss = $net; // keep sign so Loss is negative
    ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-file-invoice-dollar me-2 text-success"></i>Statement
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Account</th>
                            <th class="text-end pe-3">Amount (TZS)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-3">Operating Income</td>
                            <td class="text-end pe-3 fw-bold"><?php echo e(number_format($operatingIncome ?? 0, 2)); ?></td>
                        </tr>
                        <tr>
                            <td class="ps-3 text-muted">Sales</td>
                            <td class="text-end pe-3"><?php echo e(number_format($sales ?? 0, 2)); ?></td>
                        </tr>
                        <tr>
                            <td class="ps-3 text-muted">Discount</td>
                            <td class="text-end pe-3 text-danger">
                                <?php echo e(number_format($discount ?? 0, 2)); ?>

                            </td>
                        </tr>
                        <tr>
                            <td class="ps-3">Total Operating Income</td>
                            <td class="text-end pe-3 fw-bold"><?php echo e(number_format($operatingIncome ?? 0, 2)); ?></td>
                        </tr>

                        <tr>
                            <td class="ps-3">Cost of Goods Sold</td>
                            <td class="text-end pe-3 fw-bold"><?php echo e(number_format($cogs ?? 0, 2)); ?></td>
                        </tr>
                        <tr>
                            <td class="ps-3">Gross Profit</td>
                            <td class="text-end pe-3 <?php echo e(($grossProfit ?? 0) >= 0 ? 'text-success' : 'text-danger'); ?>">
                                <?php echo e(number_format($grossProfit ?? 0, 2)); ?>

                            </td>
                        </tr>

                        <tr>
                            <td class="ps-3">Operating Expense</td>
                            <td class="text-end pe-3 fw-bold text-danger"><?php echo e(number_format($operatingExpense ?? 0, 2)); ?></td>
                        </tr>
                        <tr>
                            <td class="ps-3">Operating Profit</td>
                            <td class="text-end pe-3 <?php echo e(($operatingProfit ?? 0) >= 0 ? 'text-success' : 'text-danger'); ?>">
                                <?php echo e(number_format($operatingProfit ?? 0, 2)); ?>

                            </td>
                        </tr>

                        <tr>
                            <td class="ps-3">Non Operating Income</td>
                            <td class="text-end pe-3 fw-bold">0.00</td>
                        </tr>
                        <tr>
                            <td class="ps-3">Non Operating Expense</td>
                            <td class="text-end pe-3 fw-bold text-danger">0.00</td>
                        </tr>

                        <tr class="<?php echo e($isLoss ? 'table-warning' : ''); ?>">
                            <td class="ps-3 fw-bold"><?php echo e($lossOrProfitLabel); ?></td>
                            <td class="text-end pe-3 fw-bold <?php echo e($isLoss ? 'text-danger' : 'text-success'); ?>">
                                <?php echo e(number_format($displayNetProfitLoss, 2)); ?>

                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/gotlaptopparts.com/Desktop/LaravelProject/chibo_sales/resources/views/admin/finance/profit-loss.blade.php ENDPATH**/ ?>