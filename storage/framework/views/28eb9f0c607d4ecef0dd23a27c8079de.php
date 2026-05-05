<?php $__env->startSection('title', 'Proforma Invoices'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Modern Header & Actions -->
    <div class="row mb-3 align-items-end">
        <div class="col-lg-6 mb-2 mb-lg-0">
            <h4 class="fw-bold mb-0">Proforma Invoices</h4>
            <p class="text-muted small mb-0">Manage and track all issued proforma invoices</p>
        </div>
        <div class="col-lg-6 text-lg-end">
            <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                <button class="btn btn-outline-primary btn-sm px-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                    <i class="fas fa-filter me-1"></i> Filter
                    <?php if(request()->anyFilled(['search', 'department_id'])): ?>
                        <span class="badge bg-primary ms-1">Active</span>
                    <?php endif; ?>
                </button>
                
                <a href="<?php echo e(route('admin.pos.index', ['type' => 'proforma'])); ?>" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm">
                    <i class="fas fa-plus-circle me-1"></i> New Proforma
                </a>
            </div>
        </div>
    </div>

    <!-- Collapsable Filters -->
    <div class="collapse <?php echo e(request()->anyFilled(['search', 'department_id']) ? 'show' : ''); ?> mb-4" id="filterCollapse">
        <div class="card border-0 shadow-sm border-top border-4 border-primary">
            <div class="card-body bg-light p-3">
                <form action="<?php echo e(route('admin.finance.proforma.index')); ?>" method="GET" class="row g-2">
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Order # or Customer name..." value="<?php echo e(request('search')); ?>">
                        </div>
                    </div>
                    
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Department</label>
                        <select name="department_id" class="form-select form-select-sm">
                            <option value="all">All Departments</option>
                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($dept->id); ?>" <?php echo e(request('department_id') == $dept->id ? 'selected' : ''); ?>><?php echo e($dept->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-md-auto d-none d-md-flex align-items-end ms-auto">
                        <div class="btn-group shadow-sm">
                            <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">APPLY</button>
                            <a href="<?php echo e(route('admin.finance.proforma.index')); ?>" class="btn btn-dark btn-sm px-3 fw-bold">RESET</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Order Code</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Department</th>
                            <th>Total Amount</th>
                            <th>Issued By</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $proformas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proforma): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="ps-4 fw-bold">#<?php echo e($proforma->order_code); ?></td>
                            <td><?php echo e($proforma->created_at->format('M d, Y')); ?></td>
                            <td>
                                <div class="fw-bold"><?php echo e($proforma->user->name ?? 'N/A'); ?></div>
                                <div class="small text-muted"><?php echo e($proforma->user->phone ?? ''); ?></div>
                            </td>
                            <td><?php echo e($proforma->department->name ?? 'N/A'); ?></td>
                            <td class="fw-bold text-primary">TZS <?php echo e(number_format($proforma->total_amount)); ?></td>
                            <td><?php echo e($proforma->saler->name ?? 'N/A'); ?></td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <button type="button" onclick="printDirect('<?php echo e(route('admin.finance.invoices.proforma', $proforma->order_code)); ?>')" class="btn btn-sm btn-light border text-dark px-2" title="Print Proforma">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <a href="<?php echo e(route('admin.orders.show', $proforma->id)); ?>" class="btn btn-sm btn-light border text-primary px-2" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No proforma invoices found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <?php echo e($proformas->links()); ?>

            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
    .container-fluid { font-size: 13px; }
    h4 { font-size: 1.25rem !important; font-weight: 700; }
    .table th { font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 700; color: #666; }
    .table td { font-size: 13px; }
    .btn { font-weight: 600; letter-spacing: 0.2px; }
    .form-control, .form-select { font-size: 13px !important; border-radius: 6px; }
    .x-small { font-size: 10px !important; }
    .card { border-radius: 10px; }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/gotlaptopparts.com/Desktop/LaravelProject/chibo_sales/resources/views/admin/finance/proforma-index.blade.php ENDPATH**/ ?>