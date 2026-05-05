<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Balance Sheet – <?php echo e(\Carbon\Carbon::parse($dateFrom)->format('d M')); ?> – <?php echo e($asAt->format('d M Y')); ?></title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1e293b; margin: 0; padding: 16px; }
        .header { margin-bottom: 14px; padding-bottom: 10px; border-bottom: 2px solid #1e293b; overflow: hidden; }
        .header-left { float: left; }
        .header-right { float: right; text-align: right; }
        .header h1 { margin: 0; font-size: 18px; font-weight: bold; }
        .header .sub { margin: 2px 0 0 0; font-size: 9px; color: #64748b; }
        .header .as-at { font-size: 12px; font-weight: bold; margin-top: 4px; }
        .clear { clear: both; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        th, td { border: 1px solid #94a3b8; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; color: #334155; font-size: 9px; text-transform: uppercase; font-weight: bold; }
        th.num, td.num { text-align: right; }
        tr.total td { background: #e2e8f0; font-weight: bold; }
        .section-title { font-size: 11px; font-weight: bold; margin: 12px 0 6px 0; padding-bottom: 4px; border-bottom: 1px solid #cbd5e1; }
        .dept-table { margin-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <h1>Balance Sheet</h1>
            <p class="sub">CHIBOBRAND CO. LTD. | Dar es Salaam, Tanzania</p>
        </div>
        <div class="header-right">
            <div style="font-size: 10px; font-weight: bold;">PERIOD</div>
            <p class="as-at"><?php echo e(\Carbon\Carbon::parse($dateFrom)->format('d M')); ?> – <?php echo e($asAt->format('d M Y')); ?></p>
            <p class="sub"><?php echo e($currentDept ? $currentDept->name : 'Consolidated'); ?> · Generated: <?php echo e(now()->format('M d, Y H:i')); ?></p>
            <p class="sub">Base currency: <strong>TZS</strong></p>
        </div>
    </div>
    <div class="clear"></div>

    <table>
        <thead>
            <tr>
                <th>ASSETS</th>
                <th class="num">TZS</th>
                <th>LIABILITIES & EQUITY</th>
                <th class="num">TZS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Mobile (net)</td>
                <td class="num"><?php echo e(number_format($cash_mobile ?? 0)); ?></td>
                <td>Liabilities</td>
                <td class="num"><?php echo e(number_format($liabilities)); ?></td>
            </tr>
            <tr>
                <td>Cash (net)</td>
                <td class="num"><?php echo e(number_format($cash_cash ?? 0)); ?></td>
                <td>Equity (net position)</td>
                <td class="num"><?php echo e(number_format($equity)); ?></td>
            </tr>
            <tr>
                <td>Bank (net)</td>
                <td class="num"><?php echo e(number_format($cash_bank ?? 0)); ?></td>
                <td></td>
                <td class="num"></td>
            </tr>
            <tr>
                <td>Accounts receivable</td>
                <td class="num"><?php echo e(number_format($receivables)); ?></td>
                <td></td>
                <td class="num"></td>
            </tr>
            <tr class="total">
                <td>Total assets</td>
                <td class="num"><?php echo e(number_format($total_assets)); ?></td>
                <td>Total liabilities & equity</td>
                <td class="num"><?php echo e(number_format($total_liabilities_equity)); ?></td>
            </tr>
        </tbody>
    </table>

    <?php if(!$currentDept && $department_breakdown->isNotEmpty()): ?>
    <div class="section-title">By department (<?php echo e(\Carbon\Carbon::parse($dateFrom)->format('d M')); ?> – <?php echo e($asAt->format('d M Y')); ?>)</div>
    <table class="dept-table">
        <thead>
            <tr>
                <th>Department</th>
                <th class="num">Mobile</th>
                <th class="num">Cash</th>
                <th class="num">Bank</th>
                <th class="num">Receivables</th>
                <th class="num">Total assets</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $department_breakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($d['name']); ?></td>
                <td class="num"><?php echo e(number_format($d['mobile'] ?? 0)); ?></td>
                <td class="num"><?php echo e(number_format($d['cash'] ?? 0)); ?></td>
                <td class="num"><?php echo e(number_format($d['bank'] ?? 0)); ?></td>
                <td class="num"><?php echo e(number_format($d['receivables'])); ?></td>
                <td class="num"><?php echo e(number_format($d['total_assets'])); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php endif; ?>
</body>
</html>
<?php /**PATH /Users/gotlaptopparts.com/Desktop/LaravelProject/chibo_sales/resources/views/admin/finance/balance-sheet-pdf.blade.php ENDPATH**/ ?>