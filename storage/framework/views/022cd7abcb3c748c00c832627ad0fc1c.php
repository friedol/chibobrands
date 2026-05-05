<?php $__env->startSection('title', 'Sales Department Overview'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h2 class="fw-bold mb-1">Sales Department</h2>
                    <p class="text-muted small mb-0">Performance, Rankings & Department Metrics</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <a href="<?php echo e(route('admin.sales-dept.targets')); ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 x-small">
                        <i class="fas fa-bullseye me-1"></i> Targets
                    </a>
                    <button class="btn btn-outline-dark btn-sm rounded-pill px-3 x-small" type="button"
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

    <!-- Collapsable Filters -->
    <div class="collapse <?php echo e(request()->anyFilled(['period', 'start_date', 'end_date']) ? 'show' : ''); ?> mb-4"
        id="filterCollapse">
        <div class="card border-0 shadow-sm border-top border-4 border-primary">
            <div class="card-body bg-light p-3">
                <form action="<?php echo e(route('admin.sales-dept.index')); ?>" method="GET" class="row g-2 align-items-end"
                    data-no-global-handler>
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Time Period</label>
                        <select name="period" id="periodSelect" class="form-select form-select-sm">
                            <option value="today" <?php echo e(($period ?? '') == 'today' ? 'selected' : ''); ?>>Today</option>
                            <option value="yesterday" <?php echo e(($period ?? '') == 'yesterday' ? 'selected' : ''); ?>>Yesterday</option>
                            <option value="week" <?php echo e(($period ?? '') == 'week' ? 'selected' : ''); ?>>This Week</option>
                            <option value="month" <?php echo e(($period ?? '') == 'month' || !isset($period) ? 'selected' : ''); ?>>This Month</option>
                            <option value="6_months" <?php echo e(($period ?? '') == '6_months' ? 'selected' : ''); ?>>Last 6 Months</option>
                            <option value="year" <?php echo e(($period ?? '') == 'year' ? 'selected' : ''); ?>>This Year</option>
                            <option value="2_years" <?php echo e(($period ?? '') == '2_years' ? 'selected' : ''); ?>>Last 2 Years</option>
                            <option value="custom" <?php echo e(($period ?? '') == 'custom' ? 'selected' : ''); ?>>Custom Range</option>
                            <option value="all" <?php echo e(($period ?? '') == 'all' ? 'selected' : ''); ?>>All Time</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-2 custom-date-group <?php echo e(($period ?? '') == 'custom' ? '' : 'd-none'); ?>">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">From Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm"
                            value="<?php echo e(request('start_date', $dateFrom)); ?>">
                    </div>

                    <div class="col-6 col-md-2 custom-date-group <?php echo e(($period ?? '') == 'custom' ? '' : 'd-none'); ?>">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">To Date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm"
                            value="<?php echo e(request('end_date', $dateTo)); ?>">
                    </div>

                    <div class="col-12 col-md-auto ms-auto">
                        <div class="btn-group shadow-sm w-100">
                            <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">APPLY</button>
                            <a href="<?php echo e(route('admin.sales-dept.index')); ?>" class="btn btn-dark btn-sm px-4 fw-bold">RESET</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const periodSelect = document.getElementById('periodSelect');
            const customDateGroups = document.querySelectorAll('.custom-date-group');

            if (periodSelect) {
                periodSelect.addEventListener('change', function() {
                    if (this.value === 'custom') {
                        customDateGroups.forEach(group => group.classList.remove('d-none'));
                    } else {
                        customDateGroups.forEach(group => group.classList.add('d-none'));
                    }
                });
            }
        });
    </script>
    <?php $__env->stopPush(); ?>

    <!-- Top stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-4">
                    <div class="x-small text-uppercase fw-bold mb-1 opacity-75">Total Department Sales</div>
                    <div class="h2 mb-0 fw-bold">TZS <?php echo e(number_format($totalDepartmentRevenue)); ?></div>
                    <div class="mt-2 x-small">
                        <span class="fw-bold"><?php echo e($reportData['total_orders']); ?></span> total orders processed
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="x-small text-uppercase fw-bold text-muted mb-1">Leads Conversion</div>
                    <div class="h2 mb-0 fw-bold text-dark"><?php echo e($leadsStats['conversion_rate']); ?>%</div>
                    <div class="mt-2 x-small text-success">
                        <i class="fas fa-check-circle me-1"></i> <?php echo e($leadsStats['converted']); ?> leads converted
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="x-small text-uppercase fw-bold text-muted mb-1">Avg Deal Value</div>
                    <div class="h2 mb-0 fw-bold text-dark">TZS <?php echo e(number_format($reportData['avg_order_value'])); ?></div>
                    <div class="mt-2 x-small text-primary">
                        <i class="fas fa-chart-line me-1"></i> Per order average
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Seller Rankings & Performance -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Seller Performance & Rankings</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0">Rank</th>
                                    <th class="py-3 border-0">Salesperson</th>
                                    <th class="py-3 border-0 text-center">Orders</th>
                                    <th class="py-3 border-0 text-end">Total Revenue</th>
                                    <th class="py-3 border-0 text-center">Contribution</th>
                                    <th class="py-3 border-0">Target Progress</th>
                                    <th class="pe-4 py-3 border-0 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $sellerPerformance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seller): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="ps-4">
                                        <?php if($seller->ranking <= 3): ?>
                                            <span class="badge bg-warning text-dark rounded-3 px-3">#<?php echo e($seller->ranking); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">#<?php echo e($seller->ranking); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo e($seller->name); ?></div>
                                        <div class="x-small text-muted">ID: <?php echo e($seller->id); ?></div>
                                    </td>
                                    <td class="text-center"><?php echo e($seller->design_tasks_count); ?></td>
                                    <td class="text-end fw-bold text-primary">TZS <?php echo e(number_format($seller->total_revenue)); ?></td>
                                    <td class="text-center">
                                        <div class="badge bg-info bg-opacity-10 text-info px-3"><?php echo e($seller->contribution_percent); ?>%</div>
                                    </td>
                                    <td style="min-width: 200px;">
                                        <div class="d-flex justify-content-between x-small mb-1">
                                            <span><?php echo e($seller->target_achievement); ?>% Achievement</span>
                                            <span class="text-muted text-uppercase">T: TZS <?php echo e(number_format($seller->active_target / 1000, 0)); ?>k</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-<?php echo e($seller->target_achievement >= 100 ? 'success' : ($seller->target_achievement >= 50 ? 'primary' : 'warning')); ?>" 
                                                 role="progressbar" 
                                                 style="width: <?php echo e(min(100, $seller->target_achievement)); ?>%"></div>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="<?php echo e(route('admin.saler-performance.show', $seller->id)); ?>" class="btn btn-sm btn-light rounded-3">View Profile</a>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Sections -->
    <div class="row g-4 mb-4">
        <!-- Leads Status -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Conversion Tracker</h5>
                    <a href="<?php echo e(route('admin.leads.index')); ?>" class="text-primary small fw-bold text-decoration-none">Manage Leads</a>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <div>
                            <div class="h3 mb-0 fw-bold text-dark"><?php echo e($leadsStats['total']); ?></div>
                            <div class="small text-muted">Total Customer Leads</div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-4">
                            <div class="bg-light p-3 rounded-3 text-center">
                                <div class="fw-bold text-warning h5 mb-0"><?php echo e($leadsStats['pending']); ?></div>
                                <div class="x-small text-muted text-uppercase">Pending</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-light p-3 rounded-3 text-center">
                                <div class="fw-bold text-success h5 mb-0"><?php echo e($leadsStats['converted']); ?></div>
                                <div class="x-small text-muted text-uppercase">Converted</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-light p-3 rounded-3 text-center">
                                <div class="fw-bold text-danger h5 mb-0"><?php echo e(\App\Models\Lead::where('status', 'not_interested')->count()); ?></div>
                                <div class="x-small text-muted text-uppercase">Lost</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Mix Visualization area -->
        <div class="col-lg-6">
             <div class="card border-0 shadow-sm h-100 bg-dark text-white">
                <div class="card-body p-4 d-flex flex-column justify-content-center">
                    <h5 class="fw-bold mb-3"><i class="fas fa-chart-pie me-2 text-primary"></i>Quick Reports</h5>
                    <p class="small opacity-75 mb-4">Access detailed department, seller, or product specific revenue reports.</p>
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('admin.sales-dept.reports')); ?>" class="btn btn-primary rounded-3">View Department Reports</a>
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="<?php echo e(route('admin.saler-performance.index')); ?>" class="btn btn-outline-light w-100 rounded-3">Seller List</a>
                            </div>
                            <div class="col-6">
                                <a href="<?php echo e(route('admin.enhanced-products.index')); ?>" class="btn btn-outline-light w-100 rounded-3">Product Mix</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .x-small { font-size: 0.75rem; }
    .table thead th { letter-spacing: 0.5px; text-transform: uppercase; font-size: 0.7rem; }
    .card { border-radius: 15px; }
    .progress { border-radius: 10px; background-color: #f0f0f0; }
    .badge { font-weight: 600; padding: 0.5em 0.8em; }
    .dropdown-menu-item { display: block; width: 100%; padding: 0.25rem 1rem; clear: both; font-weight: 400; color: #212529; text-align: inherit; text-decoration: none; white-space: nowrap; background-color: transparent; border: 0; }
    .dropdown-menu-item:hover { background-color: #f8f9fa; }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/gotlaptopparts.com/Downloads/chibo_sales/resources/views/admin/sales-dept/index.blade.php ENDPATH**/ ?>