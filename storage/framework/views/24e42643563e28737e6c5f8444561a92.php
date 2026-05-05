<?php $__env->startSection('title', 'Manage Departments'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <h4 class="fw-bold mb-0">Manage Department</h4>
            <p class="text-muted small mb-0">Overview of all company departments</p>
        </div>
        <div class="col-md-6 text-end">
            <div class="d-flex justify-content-end gap-2 align-items-center">
                <button class="btn btn-outline-dark btn-sm rounded-pill px-3 x-small" type="button"
                    data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                    <i class="fas fa-filter me-1"></i> Filter
                    <?php if(request()->anyFilled(['period', 'start_date', 'end_date', 'search'])): ?>
                        <span class="badge bg-primary ms-1">Active</span>
                    <?php endif; ?>
                </button>
                
                <?php if(auth()->user()->hasPermission('manage_finance') || auth()->user()->role === 'accountant'): ?>
                <button class="btn btn-primary btn-sm rounded-pill px-3 x-small" data-bs-toggle="modal" data-bs-target="#addDepartmentModal" data-no-global-handler>
                    <i class="fas fa-plus-circle me-1"></i>Add Department
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Collapsable Filters -->
    <div class="collapse <?php echo e(request()->anyFilled(['period', 'start_date', 'end_date', 'search']) ? 'show' : ''); ?> mb-4"
        id="filterCollapse">
        <div class="card border-0 shadow-sm border-top border-4 border-primary">
            <div class="card-body bg-light p-3">
                <form action="<?php echo e(route('admin.finance.departments.index')); ?>" method="GET" class="row g-2 align-items-end"
                    data-no-global-handler>
                    
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Dept name..." value="<?php echo e(request('search')); ?>">
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Activity Period</label>
                        <select name="period" id="periodSelect" class="form-select form-select-sm">
                            <option value="all" <?php echo e(($period ?? '') == 'all' ? 'selected' : ''); ?>>All Time (History)</option>
                            <option value="today" <?php echo e(($period ?? '') == 'today' ? 'selected' : ''); ?>>Today</option>
                            <option value="yesterday" <?php echo e(($period ?? '') == 'yesterday' ? 'selected' : ''); ?>>Yesterday</option>
                            <option value="week" <?php echo e(($period ?? '') == 'week' ? 'selected' : ''); ?>>This Week</option>
                            <option value="month" <?php echo e(($period ?? '') == 'month' ? 'selected' : ''); ?>>This Month</option>
                            <option value="6_months" <?php echo e(($period ?? '') == '6_months' ? 'selected' : ''); ?>>Last 6 Months</option>
                            <option value="year" <?php echo e(($period ?? '') == 'year' ? 'selected' : ''); ?>>This Year</option>
                            <option value="2_years" <?php echo e(($period ?? '') == '2_years' ? 'selected' : ''); ?>>Last 2 Years</option>
                            <option value="custom" <?php echo e(($period ?? '') == 'custom' ? 'selected' : ''); ?>>Custom Range</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-2 custom-date-group <?php echo e(($period ?? '') == 'custom' ? '' : 'd-none'); ?>">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">From Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm"
                            value="<?php echo e(request('start_date', $dateFromStr)); ?>">
                    </div>

                    <div class="col-6 col-md-2 custom-date-group <?php echo e(($period ?? '') == 'custom' ? '' : 'd-none'); ?>">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">To Date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm"
                            value="<?php echo e(request('end_date', $dateToStr)); ?>">
                    </div>

                    <div class="col-12 col-md-auto ms-auto">
                        <div class="btn-group shadow-sm w-100">
                            <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">APPLY</button>
                            <a href="<?php echo e(route('admin.finance.departments.index')); ?>" class="btn btn-dark btn-sm px-4 fw-bold">RESET</a>
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

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Name</th>
                            <th>Description</th>
                            <th class="text-center">Design Tasks</th>
                            <th class="text-center">Orders</th>
                            <th class="text-center">Expenses</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="ps-4 fw-bold text-dark"><?php echo e($dept->name); ?></td>
                            <td><?php echo e(Str::limit($dept->description, 50) ?: 'No description'); ?></td>
                            <td class="text-center"><span class="badge bg-info bg-opacity-10 text-info px-3"><?php echo e($dept->design_tasks_count); ?></span></td>
                            <td class="text-center"><span class="badge bg-success bg-opacity-10 text-success px-3"><?php echo e($dept->orders_count); ?></span></td>
                            <td class="text-center"><span class="badge bg-danger bg-opacity-10 text-danger px-3"><?php echo e($dept->expenses_count); ?></span></td>
                            <td class="text-end pe-4">
                                <?php if(auth()->user()->hasPermission('manage_finance') || auth()->user()->role === 'accountant'): ?>
                                <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editDepartmentModal<?php echo e($dept->id); ?>" data-no-global-handler>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="<?php echo e(route('admin.finance.departments.destroy', $dept->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this department?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="btn btn-sm btn-outline-danger" <?php echo e(($dept->design_tasks_count > 0 || $dept->orders_count > 0) ? 'disabled' : ''); ?> data-no-global-handler>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php else: ?>
                                <span class="text-muted small fst-italic">View Only</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No departments found matching your search.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            <?php echo e($departments->links()); ?>

        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?php echo e(route('admin.finance.departments.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Department Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Digital Branding" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief overview of what this department handles..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" data-no-global-handler>Create Department</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Edit Modals -->
<?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="modal fade" id="editDepartmentModal<?php echo e($dept->id); ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?php echo e(route('admin.finance.departments.update', $dept->id)); ?>" method="POST">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Department: <?php echo e($dept->name); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Department Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo e($dept->name); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo e($dept->description); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" data-no-global-handler>Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .container-fluid { font-size: 13px; }
    h4 { font-size: 1.25rem !important; }
    .table th { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
    .table td { font-size: 13px; }
    .btn, .form-control, .form-select, .input-group-text { font-size: 13px !important; }
    .btn-sm { font-size: 12px !important; }
    .badge { font-size: 10px !important; }
    .small { font-size: 13px !important; }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/gotlaptopparts.com/Downloads/chibo_sales/resources/views/admin/departments/index.blade.php ENDPATH**/ ?>