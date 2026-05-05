<?php $__env->startSection('title', 'Admin Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h2 class="fw-bold mb-1">Hello! <?php echo e(auth()->user()->name); ?></h2>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <button class="btn btn-outline-primary btn-sm rounded-pill px-3 x-small" type="button"
                            data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                            <i class="fas fa-filter me-1"></i> Filter
                            <?php if(request()->anyFilled(['period', 'start_date', 'end_date'])): ?>
                                <span class="badge bg-primary ms-1">Active</span>
                            <?php endif; ?>
                        </button>
                        <button class="btn btn-outline-dark btn-sm rounded-pill px-3 x-small"
                            onclick="window.location.reload()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modern Collapsable Filters -->
        <div class="collapse <?php echo e(request()->anyFilled(['period', 'start_date', 'end_date']) ? 'show' : ''); ?> mb-4"
            id="filterCollapse">
            <div class="card border-0 shadow-sm border-top border-4 border-primary">
                <div class="card-body bg-light p-3">
                    <form action="<?php echo e(route('admin.dashboard')); ?>" method="GET" class="row g-2 align-items-end"
                        data-no-global-handler>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-bold x-small text-uppercase mb-1">Time Period</label>
                            <select name="period" id="periodSelect" class="form-select form-select-sm">
                                <option value="today" <?php echo e(($period ?? '') == 'today' ? 'selected' : ''); ?>>Today</option>
                                <option value="yesterday" <?php echo e(($period ?? '') == 'yesterday' ? 'selected' : ''); ?>>Yesterday
                                </option>
                                <option value="week" <?php echo e(($period ?? '') == 'week' ? 'selected' : ''); ?>>This Week</option>
                                <option value="month" <?php echo e(($period ?? '') == 'month' || !isset($period) ? 'selected' : ''); ?>>
                                    This Month</option>
                                <option value="6_months" <?php echo e(($period ?? '') == '6_months' ? 'selected' : ''); ?>>Last 6 Months
                                </option>
                                <option value="year" <?php echo e(($period ?? '') == 'year' ? 'selected' : ''); ?>>This Year</option>
                                <option value="2_years" <?php echo e(($period ?? '') == '2_years' ? 'selected' : ''); ?>>Last 2 Years
                                </option>
                                <option value="custom" <?php echo e(($period ?? '') == 'custom' ? 'selected' : ''); ?>>Custom Range
                                </option>
                                <option value="all" <?php echo e(($period ?? '') == 'all' ? 'selected' : ''); ?>>All Time</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-2 custom-date-group <?php echo e(($period ?? '') == 'custom' ? '' : 'd-none'); ?>">
                            <label class="form-label fw-bold x-small text-uppercase mb-1">From Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm"
                                value="<?php echo e(request('start_date')); ?>">
                        </div>

                        <div class="col-6 col-md-2 custom-date-group <?php echo e(($period ?? '') == 'custom' ? '' : 'd-none'); ?>">
                            <label class="form-label fw-bold x-small text-uppercase mb-1">To Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm"
                                value="<?php echo e(request('end_date')); ?>">
                        </div>

                        <div class="col-12 col-md-auto ms-auto">
                            <div class="btn-group shadow-sm w-100">
                                <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">APPLY</button>
                                <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn btn-dark btn-sm px-4 fw-bold">RESET</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 12 NAVIGATABLE STATS CARDS - 6 Per Row on Desktop -->
        <div class="row g-2 g-md-3 mb-4">
            <!-- 0. Total Sales -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?php echo e(route('admin.finance.reports')); ?>" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-info hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-info bg-opacity-10 text-info me-2">
                                    <i class="fas fa-tags"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Total Sales</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-info"><?php echo e(number_format($stats['total_billed'] ?? 0)); ?></div>
                            <div class="x-small text-muted mt-2">Gross value sold</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 1. Total Revenue -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?php echo e(route('admin.finance.reports')); ?>" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-primary hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2">
                                    <i class="fas fa-arrow-trend-up"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Total Revenue</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-dark"><?php echo e(number_format($stats['total_revenue'] ?? 0)); ?></div>
                            <div class="x-small text-muted mt-2">Collected + Outstanding</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 2. Total Collected -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?php echo e(route('admin.finance.cash-flow')); ?>" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-success hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-success bg-opacity-10 text-success me-2">
                                    <i class="fas fa-wallet"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Collected</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-success"><?php echo e(number_format($stats['total_collected'] ?? 0)); ?>

                            </div>
                            <div class="x-small text-muted mt-2">Cash in hand</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- Debt Collected -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?php echo e(route('admin.finance.cash-flow')); ?>" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 hover-lift"
                        style="border-left-color: #20c997 !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-opacity-10 me-2"
                                    style="background-color: rgba(32, 201, 151, 0.1); color: #20c997;">
                                    <i class="fas fa-hand-holding-dollar"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Debt Collected</span>
                            </div>
                            <div class="h3 mb-0 fw-bold" style="color: #20c997;">
                                <?php echo e(number_format($stats['total_debt_collected'] ?? 0)); ?></div>
                            <div class="x-small text-muted mt-2">Past debts recovered</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 3. Total Expenses -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?php echo e(route('admin.finance.expenses')); ?>" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-danger hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-danger bg-opacity-10 text-danger me-2">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Expenses</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-danger"><?php echo e(number_format($stats['total_expenses'] ?? 0)); ?>

                            </div>
                            <div class="x-small text-muted mt-2">Spending</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 4. Balance Due -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?php echo e(route('admin.finance.audit')); ?>" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-warning hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-warning bg-opacity-10 text-warning me-2">
                                    <i class="fas fa-clock text-warning"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Balance Due</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-warning"><?php echo e(number_format($stats['total_balance_due'] ?? 0)); ?>

                            </div>
                            <div class="x-small text-muted mt-2">Outstanding</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 5. Total Orders -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?php echo e(route('admin.orders.index')); ?>" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-info hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-info bg-opacity-10 text-info me-2">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Total Orders</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-dark"><?php echo e(number_format($stats['total_orders'] ?? 0)); ?></div>
                            <div class="x-small text-muted mt-2">In period</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 6. Pending Orders -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?php echo e(route('admin.orders.index', ['status' => 'requested'])); ?>" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-secondary hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-secondary bg-opacity-10 text-secondary me-2">
                                    <i class="fas fa-hourglass-half"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Pending Orders</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-dark"><?php echo e(number_format($periodStats['pending'] ?? 0)); ?></div>
                            <div class="x-small text-muted mt-2">Awaiting approval</div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- ROW 2 -->
            <!-- 7. Active Tasks -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?php echo e(route('admin.design-tasks.index')); ?>" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-info hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-info bg-opacity-10 text-info me-2">
                                    <i class="fas fa-palette"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Active Tasks</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-dark"><?php echo e($stats['in_progress'] ?? 0); ?></div>
                            <div class="x-small text-info mt-2">Work in progress</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 8. Overdue Tasks -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?php echo e(route('admin.design-tasks.index')); ?>" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-danger hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-danger bg-opacity-10 text-danger me-2">
                                    <i class="fas fa-fire"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Overdue Tasks</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-danger"><?php echo e($stats['overdue_tasks'] ?? 0); ?></div>
                            <div class="x-small text-danger mt-2">Past deadline</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 9. Completed Tasks -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?php echo e(route('admin.design-tasks.index', ['status' => 'completed'])); ?>" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-success hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-success bg-opacity-10 text-success me-2">
                                    <i class="fas fa-check-double"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Completed</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-success"><?php echo e($stats['completed_tasks'] ?? 0); ?></div>
                            <div class="x-small text-success mt-2">Ready for delivery</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 10. Total Customers -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?php echo e(route('admin.customers.index')); ?>" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-dark hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-dark bg-opacity-10 text-dark me-2">
                                    <i class="fas fa-users"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Customers</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-dark"><?php echo e(number_format($stats['total_customers'] ?? 0)); ?></div>
                            <div class="x-small text-muted mt-2">Database count</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 11. Net Profit -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?php echo e(route('admin.finance.reports')); ?>" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 hover-lift"
                        style="border-left-color: #6610f2 !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-opacity-10 me-2"
                                    style="background-color: rgba(102, 16, 242, 0.1); color: #6610f2;">
                                    <i class="fas fa-scale-balanced"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Net Profit</span>
                            </div>
                            <div class="h3 mb-0 fw-bold" style="color: #6610f2;">
                                <?php echo e(number_format($stats['net_profit'] ?? 0)); ?></div>
                            <div class="x-small text-muted mt-2">After expenses</div>
                        </div>
                    </div>
                </a>
            </div>
            <!-- 12. Stock Alerts -->
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?php echo e(route('admin.enhanced-products.index', ['stock' => 'low'])); ?>" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-danger hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-danger bg-opacity-10 text-danger me-2">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Stock Alerts</span>
                            </div>
                            <div class="h3 mb-0 fw-bold text-danger"><?php echo e($lowStockProducts->count()); ?></div>
                            <div class="x-small text-danger mt-2">Low inventory</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- MAIN GRAPHS ROW -->
        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-chart-line me-2"></i>Profitability Trend</h6>
                        <div class="btn-group btn-group-sm shadow-sm" role="group">
                            <button type="button" class="btn btn-outline-dark active" id="btnLineChart"
                                onclick="toggleProfitChart('line')">
                                <i class="fas fa-chart-line"></i>
                            </button>
                            <button type="button" class="btn btn-outline-dark" id="btnBarChart"
                                onclick="toggleProfitChart('bar')">
                                <i class="fas fa-chart-bar"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="combinedRevenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-tasks me-2"></i>Design Task Distribution</h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 250px;">
                            <canvas id="taskStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RECENT ACTIVITY ROW 1: ORDERS & TOP PRODUCTS -->
        <div class="row g-3 mb-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-pen-nib me-2 text-primary"></i>Top Design
                            Performance</h6>
                        <a href="<?php echo e(route('admin.design-tasks.index')); ?>"
                            class="btn btn-sm btn-link text-primary p-0 text-decoration-none small">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php $__empty_1 = true; $__currentLoopData = $topDesigners ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $designer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="list-group-item border-0 py-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center fw-bold me-2"
                                                style="width: 32px; height: 32px; font-size: 12px;">
                                                <?php echo e(substr($designer['name'], 0, 1)); ?>

                                            </div>
                                            <div>
                                                <div class="small fw-bold text-dark"><?php echo e($designer['name']); ?></div>
                                                <div class="x-small text-muted">Completed: <span
                                                        class="fw-bold"><?php echo e($designer['completed_tasks']); ?></span> tasks</div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="small fw-bold text-info">
                                                <?php echo e(number_format($designer['raw_achievement'], 1)); ?>%</div>
                                            <div class="x-small text-muted">of target</div>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 6px; border-radius: 3px;">
                                        <div class="progress-bar bg-info" role="progressbar"
                                            style="width: <?php echo e($designer['achievement']); ?>%"
                                            aria-valuenow="<?php echo e($designer['achievement']); ?>" aria-valuemin="0"
                                            aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-palette fa-2x mb-2 opacity-25"></i>
                                    <p class="small mb-0">No design performance data available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-trophy me-2 text-warning"></i>Top Sales
                            Performance</h6>
                        <a href="<?php echo e(route('admin.saler-performance.index')); ?>"
                            class="btn btn-sm btn-link text-primary p-0 text-decoration-none small">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php $__empty_1 = true; $__currentLoopData = $topSalers ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $saler): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="list-group-item border-0 py-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold me-2"
                                                style="width: 32px; height: 32px; font-size: 12px;">
                                                <?php echo e(substr($saler['name'], 0, 1)); ?>

                                            </div>
                                            <div>
                                                <div class="small fw-bold text-dark"><?php echo e($saler['name']); ?></div>
                                                <div class="x-small text-muted">Sales: TZS
                                                    <?php echo e(number_format($saler['total_sales'])); ?></div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="small fw-bold text-primary">
                                                <?php echo e(number_format($saler['raw_achievement'], 1)); ?>%</div>
                                            <div class="x-small text-muted">of target</div>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 6px; border-radius: 3px;">
                                        <div class="progress-bar bg-primary" role="progressbar"
                                            style="width: <?php echo e($saler['achievement']); ?>%"
                                            aria-valuenow="<?php echo e($saler['achievement']); ?>" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fa-2x mb-2 opacity-25"></i>
                                    <p class="small mb-0">No sales performance data available</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RECENT ACTIVITY ROW 2: DESIGN TASKS & RECENT EXPENSES -->
        <div class="row g-3 mb-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-palette me-2"></i>Recent Design Tasks</h6>
                        <a href="<?php echo e(route('admin.design-tasks.index')); ?>"
                            class="btn btn-sm btn-link text-primary p-0 text-decoration-none">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted x-small text-uppercase">
                                    <tr>
                                        <th class="ps-3 py-2">ID</th>
                                        <th>Designer</th>
                                        <th>Status</th>
                                        <th class="text-center pe-3">Deadline</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $recentAssignedTasks ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr onclick="window.location='<?php echo e(route('admin.design-tasks.show', $task->id)); ?>'"
                                            style="cursor: pointer;">
                                            <td class="ps-3 fw-bold">#<?php echo e($task->id); ?></td>
                                            <td class="small"><?php echo e($task->designer->name ?? 'Unassigned'); ?></td>
                                            <td>
                                                <span
                                                    class="badge rounded-pill bg-info text-dark x-small"><?php echo e(ucfirst($task->status)); ?></span>
                                            </td>
                                            <td class="text-center pe-3 small">
                                                <?php echo e($task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('M d') : '-'); ?>

                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-3 text-muted">No recent tasks</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-danger"><i class="fas fa-receipt me-2"></i>Recent Expenses</h6>
                        <a href="<?php echo e(route('admin.finance.expenses')); ?>"
                            class="btn btn-sm btn-link text-danger p-0 text-decoration-none">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted x-small text-uppercase">
                                    <tr>
                                        <th class="ps-3 py-2">Category</th>
                                        <th class="text-end pe-3">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $recentExpenses ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td class="ps-3 py-2">
                                                <div class="small fw-bold"><?php echo e($expense->category); ?></div>
                                                <div class="x-small text-muted text-truncate" style="max-width: 150px;">
                                                    <?php echo e($expense->notes); ?></div>
                                            </td>
                                            <td class="text-end pe-3 fw-bold text-danger small">TZS
                                                <?php echo e(number_format($expense->amount)); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="2" class="text-center py-4 text-muted">No recent expenses</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        let profitChart;

        function toggleProfitChart(type) {
            const ctx = document.getElementById('combinedRevenueChart').getContext('2d');
            const labels = <?php echo json_encode($profit_chart_data['labels'] ?? []); ?>;
            const revenueData = <?php echo json_encode($profit_chart_data['revenue'] ?? []); ?>;
            const expenseData = <?php echo json_encode($profit_chart_data['expenses'] ?? []); ?>;
            const profitData = <?php echo json_encode($profit_chart_data['current'] ?? []); ?>;

            if (profitChart) {
                profitChart.destroy();
            }

            const isBar = type === 'bar';

            // Update UI buttons
            document.getElementById('btnLineChart').classList.toggle('active', type === 'line');
            document.getElementById('btnBarChart').classList.toggle('active', type === 'bar');

            profitChart = new Chart(ctx, {
                type: type,
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Revenue',
                            data: revenueData,
                            borderColor: '#28a745',
                            backgroundColor: isBar ? '#28a745' : 'rgba(40, 167, 69, 0.05)',
                            borderWidth: 2,
                            fill: !isBar,
                            tension: 0.4,
                            barPercentage: 0.6,
                            categoryPercentage: 0.5
                        },
                        {
                            label: 'Expenses',
                            data: expenseData,
                            borderColor: '#dc3545',
                            backgroundColor: isBar ? '#dc3545' : 'rgba(220, 53, 69, 0.05)',
                            borderWidth: 2,
                            fill: !isBar,
                            tension: 0.4,
                            barPercentage: 0.6,
                            categoryPercentage: 0.5
                        },
                        {
                            label: 'Net Profit',
                            data: profitData,
                            borderColor: '#6610f2',
                            backgroundColor: isBar ? '#6610f2' : 'rgba(102, 16, 242, 0.1)',
                            borderWidth: 3,
                            pointBackgroundColor: '#6610f2',
                            fill: !isBar,
                            tension: 0.4,
                            barPercentage: 0.6,
                            categoryPercentage: 0.5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || '';
                                    if (label) label += ': ';
                                    if (context.parsed.y !== null) label += 'TZS ' + context.parsed.y.toLocaleString();
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: v => 'TZS ' + v.toLocaleString(),
                                font: { size: 10 }
                            },
                            grid: { borderDash: [5, 5] }
                        },
                        x: {
                            ticks: { font: { size: 10 } },
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Initial load
            toggleProfitChart('line');

            // Task Status Distribution Chart
            const taskStatusCtx = document.getElementById('taskStatusChart').getContext('2d');
            new Chart(taskStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'In Progress', 'In Review', 'Printing', 'Completed', 'Rejected'],
                    datasets: [{
                        data: [
                            <?php echo e($designTaskStatus['pending'] ?? 0); ?>,
                            <?php echo e($designTaskStatus['in_progress'] ?? 0); ?>,
                            <?php echo e($designTaskStatus['in_review'] ?? 0); ?>,
                            <?php echo e($designTaskStatus['printing'] ?? 0); ?>,
                            <?php echo e(($designTaskStatus['completed'] ?? 0) + ($designTaskStatus['super_completed'] ?? 0)); ?>,
                            <?php echo e($designTaskStatus['rejected'] ?? 0); ?>

                        ],
                        backgroundColor: ['#ffc107', '#007bff', '#6c757d', '#17a2b8', '#28a745', '#dc3545'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } },
                    cutout: '75%'
                }
            });

            // Period Selection Handling
            const periodSelect = document.getElementById('periodSelect');
            const customDateGroups = document.querySelectorAll('.custom-date-group');

            if (periodSelect) {
                periodSelect.addEventListener('change', function () {
                    if (this.value === 'custom') {
                        customDateGroups.forEach(el => el.classList.remove('d-none'));
                    } else {
                        customDateGroups.forEach(el => el.classList.add('d-none'));
                    }
                });
            }
        });
    </script>

    <style>
        .icon-circle {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 0.8rem;
        }

        .x-small {
            font-size: 10px;
        }

        .hover-lift {
            transition: transform 0.2s ease-in-out;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
        }

        @media (max-width: 768px) {
            .h3 {
                font-size: 1.1rem !important;
            }

            .card-body {
                padding: 0.75rem !important;
            }

            .icon-circle {
                width: 28px;
                height: 28px;
                font-size: 11px;
            }

            .x-small {
                font-size: 9px;
            }
        }
    </style>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/gotlaptopparts.com/Desktop/LaravelProject/chibo_sales/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>