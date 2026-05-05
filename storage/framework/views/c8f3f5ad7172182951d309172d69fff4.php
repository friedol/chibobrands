<?php $__env->startSection('title', 'Saler Performance Dashboard'); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        /* Mobile Responsive - Font Size Reductions */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 0.5rem;
            }

            h2 {
                font-size: 1.25rem !important;
            }

            .text-muted {
                font-size: 0.75rem !important;
            }

            .card-header h6 {
                font-size: 0.8rem !important;
            }

            .btn-sm {
                font-size: 0.7rem !important;
            }

            .table th,
            .table td {
                font-size: 0.75rem !important;
                padding: 0.375rem 0.5rem !important;
            }

            .stats-col {
                flex: 0 0 50% !important;
                max-width: 50% !important;
            }
        }

        @media (max-width: 575.98px) {
            .container-fluid {
                padding: 0.25rem;
            }

            h2 {
                font-size: 1.1rem !important;
            }

            .card-body {
                padding: 0.75rem !important;
            }
        }

        .icon-circle {
            height: 40px;
            width: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-metric .h2 {
            font-size: 1.4rem;
            margin-bottom: 2px;
        }

        .avatar-circle {
            height: 28px;
            width: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
        }

        @media print {
            @page {
                size: A4;
                margin: 8mm;
            }

            body {
                background: white !important;
                font-size: 10pt !important;
                margin: 0 !important;
                padding: 0 !important;
                color: #000 !important;
            }

            .btn,
            .sidebar,
            .sidebar-nav,
            .filter-section,
            .top-navbar,
            .mobile-menu-toggle,
            .btn-group,
            #filterCollapse,
            .card-header .btn,
            .breadcrumb,
            footer,
            .no-print {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                overflow: visible !important;
            }

            .container-fluid {
                width: 100% !important;
                padding: 0 !important;
                max-width: 100% !important;
            }

            /* Unified Card Style for Print */
            .card {
                border: 1px solid #bfbcbcff !important;
                box-shadow: none !important;
                margin-bottom: 8px !important;
                break-inside: avoid;
            }

            .card-header {
                border: none !important;
                padding-left: 0 !important;
                padding-bottom: 5px !important;
                border-bottom: 2px solid #000 !important;
                margin-bottom: 10px !important;
            }

            .card-header h5,
            .card-header h6 {
                font-size: 11pt !important;
                color: #000 !important;
                display: inline-block;
                margin-bottom: 0 !important;
            }

            .card-body {
                padding: 0.5rem !important;
            }

            /* Layout adjustments for A4 - Horizontal flow */
            .row {
                display: flex !important;
                flex-wrap: wrap !important;
                margin-left: -2px !important;
                margin-right: -2px !important;
            }

            .row>[class*="col-"] {
                padding: 0 2px !important;
            }

            .metrics-row .col-6 {
                flex: 0 0 25% !important;
                max-width: 25% !important;
            }

            .charts-row .col-lg-8 {
                flex: 0 0 65% !important;
                max-width: 65% !important;
            }

            .charts-row .col-lg-4 {
                flex: 0 0 35% !important;
                max-width: 35% !important;
            }

            /* Table Visibility & Contrast */
            .table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 9.5pt !important;
                color: #000 !important;
            }

            .table th,
            .table td {
                border: 1px solid #000 !important;
                padding: 4px 6px !important;
            }

            .table thead th {
                background-color: #eee !important;
                font-weight: 800 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                text-transform: uppercase;
            }

            /* Chart sizing */
            canvas {
                max-width: 100% !important;
                height: auto !important;
                max-height: 180px !important;
            }

            .chart-container {
                height: 180px !important;
            }

            .print-only {
                display: block !important;
            }

            .report-header {
                margin-bottom: 10px;
                border-bottom: 2px solid #000;
                padding-bottom: 5px;
            }

            .report-header h1 {
                font-size: 18pt !important;
                margin: 0;
            }

            /* Fixed Footer at bottom of pages */
            .print-footer {
                position: fixed;
                bottom: 0px;
                left: 0;
                right: 0;
                background: white !important;
                padding: 8px 0;
                border-top: 1px solid #000 !important;
                text-align: center;
                font-size: 8pt !important;
                color: #000 !important;
            }

            body {
                padding-bottom: 50px !important;
            }
        }

        .print-only {
            display: none;
        }

        .report-header {
            text-align: left;
            margin-bottom: 30px;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
        }

        .report-header img {
            max-width: 120px;
            margin-bottom: 10px;
        }

        .report-header h1 {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }

        .report-header p {
            color: #666;
            margin: 2px 0;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid no-print-padding">
        <div class="print-only report-header mb-4">
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <img src="<?php echo e(asset('images/logo.webp')); ?>" alt="Logo" style="height: 50px;"
                        onerror="this.style.display='none'">
                    <h1 class="fw-bold text-dark mt-2" style="font-size: 20pt; margin-bottom: 0;">Saler Activity & Revenue
                        Report</h1>
                </div>
                <div class="text-end text-dark" style="font-size: 9pt;">
                    <p class="mb-0 fw-bold" style="font-size: 11pt;">CHIBOBRAND CO. LTD.</p>
                    <p class="mb-0">Period: <?php echo e(\Carbon\Carbon::parse($dateFrom)->format('M d, Y')); ?> -
                        <?php echo e(\Carbon\Carbon::parse($dateTo)->format('M d, Y')); ?></p>
                    <p class="mb-0">Generated: <?php echo e(now()->format('M d, Y H:i')); ?></p>
                    <p class="mb-0 italic">Sales Operations Audit</p>
                </div>
            </div>
            <hr class="border-dark opacity-100 my-2">
        </div>

        <!-- Header Section -->
        <div class="mb-4 no-print">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="mb-0 fw-bold">Saler Performance</h2>
                </div>
                <div class="action-icons d-flex gap-2 ms-2 align-items-center">
                    <button class="text-primary fs-5 border-0 bg-transparent p-0 me-2" type="button"
                        data-bs-toggle="collapse" data-bs-target="#filterCollapse" title="Toggle Filters">
                        <i class="fas fa-filter"></i>
                    </button>
                    <?php $salerPdfUrl = route('admin.saler-performance.export', ['type' => 'pdf', 'saler_id' => $salerId, 'date_from' => $dateFrom, 'date_to' => $dateTo]); ?>
                    <a href="<?php echo e($salerPdfUrl); ?>" target="_blank" rel="noopener" class="text-success fs-5 share-pdf-btn"
                        data-pdf-url="<?php echo e($salerPdfUrl); ?>"
                        data-pdf-filename="saler-performance-<?php echo e($dateFrom ?? 'report'); ?>-<?php echo e($dateTo ?? ''); ?>.pdf"
                        title="Share PDF">
                        <i class="fas fa-share-alt"></i>
                    </a>
                    <button class="text-dark fs-5 border-0 bg-transparent p-0 ms-1" onclick="window.print()"
                        title="Print Summary Report">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="collapse <?php echo e(($salerId || request('date_from')) ? 'show' : ''); ?> mb-4 filter-section"
            id="filterCollapse">
            <div class="card border-0 shadow-sm border-top border-4 border-primary">
                <div class="card-body bg-light p-3">
                    <form action="<?php echo e(route('admin.saler-performance.index')); ?>" method="GET" class="row g-2"
                        data-no-preloader data-no-global-handler>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-bold x-small text-uppercase mb-1">From</label>
                            <input type="date" class="form-control form-control-sm" name="date_from"
                                value="<?php echo e($dateFrom); ?>">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-bold x-small text-uppercase mb-1">To</label>
                            <input type="date" class="form-control form-control-sm" name="date_to" value="<?php echo e($dateTo); ?>">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold x-small text-uppercase mb-1">Salesperson</label>
                            <select class="form-select form-select-sm" name="saler_id">
                                <option value="">All Revenue Generated (Full Team)</option>
                                <?php $__currentLoopData = $allSalers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($s->id); ?>" <?php echo e($salerId == $s->id ? 'selected' : ''); ?>>
                                        <?php echo e($s->name); ?> (Staff)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end mt-2 mt-md-0">
                            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold" data-no-global-handler>
                                <i class="fas fa-sync-alt me-1"></i> UPDATE
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Metrics -->
        <div class="row g-2 mb-4 metrics-row">
            <div class="col-6 col-lg-3 stats-col">
                <div class="card border-0 shadow-sm h-100 card-metric">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-1">
                            <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2">
                                <i class="fas fa-shopping-cart fa-sm"></i>
                            </div>
                            <span class="text-uppercase x-small fw-bold text-muted">Total Orders</span>
                        </div>
                        <div class="h2 mb-0 fw-bold text-dark"><?php echo e(number_format($summary['total_orders'])); ?></div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3 stats-col">
                <div class="card border-0 shadow-sm h-100 card-metric">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-1">
                            <div class="icon-circle bg-info bg-opacity-10 text-info me-2">
                                <i class="fas fa-palette fa-sm"></i>
                            </div>
                            <span class="text-uppercase x-small fw-bold text-muted">Design Tasks</span>
                        </div>
                        <div class="h2 mb-0 fw-bold text-dark"><?php echo e(number_format($summary['total_tasks'])); ?></div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3 stats-col">
                <div class="card border-0 shadow-sm h-100 card-metric">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-1">
                            <div class="icon-circle bg-success bg-opacity-10 text-success me-2">
                                <i class="fas fa-money-bill-wave fa-sm"></i>
                            </div>
                            <span class="text-uppercase x-small fw-bold text-muted">Total Revenue</span>
                        </div>
                        <div class="h2 mb-0 fw-bold text-dark"><?php echo e(number_format($summary['total_revenue'])); ?></div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3 stats-col">
                <div class="card border-0 shadow-sm h-100 card-metric">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-1">
                            <div class="icon-circle bg-dark bg-opacity-10 text-dark me-2">
                                <i class="fas fa-users fa-sm"></i>
                            </div>
                            <span class="text-uppercase x-small fw-bold text-muted">Active Staff</span>
                        </div>
                        <div class="h2 mb-0 fw-bold text-dark"><?php echo e(number_format($summary['active_salers'])); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Table -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-line me-2"></i>Staff Performance Rankings</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3 border-0 x-small">Salesperson</th>
                                <th class="text-center border-0 x-small">Orders</th>
                                <th class="text-center border-0 x-small">Design Tasks</th>
                                <th class="text-end border-0 x-small">Total Revenue</th>
                                <th class="text-end pe-3 border-0 x-small">Impact</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $salers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $saler): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $revenue = $saler->salerOrders->sum('total_amount') + $saler->designTasks->sum('price');
                                    $impact = $summary['total_revenue'] > 0 ? round(($revenue / $summary['total_revenue']) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center py-1">
                                            <div class="avatar-circle bg-info text-white me-2">
                                                <?php echo e(substr($saler->name, 0, 1)); ?>

                                            </div>
                                            <div>
                                                <div class="small fw-bold"><?php echo e($saler->name); ?></div>
                                                <div class="x-small text-muted"><?php echo e($saler->phone); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center small"><?php echo e($saler->saler_orders_count); ?></td>
                                    <td class="text-center small"><?php echo e($saler->design_tasks_count); ?></td>
                                    <td class="text-end small fw-bold text-success"><?php echo e(number_format($revenue)); ?></td>
                                    <td class="text-end pe-3">
                                        <div class="d-flex align-items-center justify-content-end">
                                            <div class="progress me-2" style="height: 4px; width: 50px;">
                                                <div class="progress-bar bg-primary" style="width: <?php echo e($impact); ?>%"></div>
                                            </div>
                                            <span class="x-small fw-bold"><?php echo e($impact); ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row g-3 mb-4 charts-row">
            <!-- Revenue Trend -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-line me-2"></i>Revenue Trend (Last 30
                            Days)</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 250px;">
                            <canvas id="salerTrendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Status Distribution -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-pie me-2"></i>Order Status</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 250px;">
                            <canvas id="salerStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Sales Volume Visualization -->
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-bar me-2"></i>Revenue Contribution by
                            Staff</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="salerRevenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Data Tables (Visible in Print) -->
        <div class="row g-3 mt-2 mb-2 detailed-tables">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-shopping-cart me-2"></i>Recent Team Orders
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.8rem;">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">Date</th>
                                        <th>Customer</th>
                                        <th>Saler</th>
                                        <th class="text-end pe-3">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td class="ps-3"><?php echo e($order->created_at->format('M d, Y')); ?></td>
                                            <td><?php echo e($order->user->name ?? 'Walk-in'); ?></td>
                                            <td><?php echo e($order->saler->name ?? 'N/A'); ?></td>
                                            <td class="text-end pe-3 fw-bold"><?php echo e(number_format($order->total_amount)); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No orders found for this period.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-info"><i class="fas fa-palette me-2"></i>Recent Team Design Tasks</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.8rem;">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">Task Title</th>
                                        <th>Saler</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end pe-3">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $recentTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td class="ps-3 text-truncate" style="max-width: 150px;"><?php echo e($task->title); ?></td>
                                            <td><?php echo e($task->saler->name ?? 'N/A'); ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-info rounded-pill" style="font-size: 0.7rem;">
                                                    <?php echo e(ucfirst($task->status)); ?>

                                                </span>
                                            </td>
                                            <td class="text-end pe-3 fw-bold"><?php echo e(number_format($task->price)); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No design tasks found for this
                                                period.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Print Only Footer (Repeats on every page) -->
        <div class="print-only print-footer">
            <p class="mb-0">&copy; <?php echo e(date('Y')); ?> CHIBOBRAND CO. LTD. All rights reserved.</p>
            <p class="mb-0">Developed by <a href="https://fridoltech.org"
                    style="color: #666; text-decoration: none; font-weight: bold;">Fridoltech</a></p>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            // Trend Chart
            const trendData = <?php echo json_encode($trendData, 15, 512) ?>;
            const trendLabels = trendData.map(d => d.date);
            const revenueValues = trendData.map(d => d.revenue);

            new Chart(document.getElementById('salerTrendChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'Revenue',
                        data: revenueValues,
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.05)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                        y: { ticks: { font: { size: 9 }, callback: v => v.toLocaleString() } }
                    }
                }
            });

            // Status Chart
            const statusData = <?php echo json_encode($statusDistribution, 15, 512) ?>;
            new Chart(document.getElementById('salerStatusChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(statusData),
                    datasets: [{
                        data: Object.values(statusData),
                        backgroundColor: ['#f6c23e', '#36b9cc', '#1cc88a', '#e74a3b'],
                        borderWidth: 0
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
                    },
                    cutout: '70%'
                }
            });

            // Bar Chart
            const salerNames = <?php echo json_encode($salers->pluck('name'), 15, 512) ?>;
            const salerRevenues = <?php echo json_encode($salers->map(fn($s) => $s->salerOrders->sum('total_amount') + $s->designTasks->sum('price')), 15, 512) ?>;

            const ctx = document.getElementById('salerRevenueChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: salerNames,
                    datasets: [{
                        label: 'Total Revenue Generated',
                        data: salerRevenues,
                        backgroundColor: 'rgba(28, 200, 138, 0.8)',
                        borderColor: '#1cc88a',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    indexAxis: 'y', // Horizontal bars
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { font: { size: 9 }, callback: v => v.toLocaleString() } },
                        y: { ticks: { font: { size: 10 } } }
                    }
                }
            });

            // Professional Print Title
            window.onbeforeprint = () => { document.title = "Saler_Performance_Report_<?php echo e(now()->format('Ymd')); ?>"; };
            window.onafterprint = () => { document.title = "<?php echo $__env->yieldContent('title'); ?>"; };
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/gotlaptopparts.com/Downloads/chibo_sales/resources/views/admin/saler-performance/index.blade.php ENDPATH**/ ?>