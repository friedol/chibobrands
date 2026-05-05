<?php $__env->startSection('title', 'Expense Tracking'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Modern Header & Actions -->
    <div class="row mb-3 align-items-end">
        <div class="col-lg-6 mb-2 mb-lg-0">
            <h4 class="fw-bold mb-0">Expense Tracking</h4>
            <p class="text-muted small mb-0">Monitor company spendings and department distributions</p>
        </div>
        <div class="col-lg-6 text-lg-end">
            <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                <button type="button" onclick="printDirect('<?php echo e(route('admin.finance.expenses.print', request()->all())); ?>')" class="btn btn-dark btn-sm px-3 fw-bold shadow-sm">
                    <i class="fas fa-print me-1"></i> Print
                </button>
                <button class="btn btn-outline-danger btn-sm px-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                    <i class="fas fa-filter me-1"></i> Filter
                    <?php if(request()->anyFilled(['search', 'category', 'department_id', 'date_from', 'date_to'])): ?>
                        <span class="badge bg-danger ms-1">Active</span>
                    <?php endif; ?>
                </button>
                
                <button class="btn btn-danger btn-sm px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                    <i class="fas fa-minus-circle me-1"></i> Record Expense
                </button>
            </div>
        </div>
    </div>

    <!-- Modern Collapsable Filters -->
    <div class="collapse <?php echo e(request()->anyFilled(['search', 'category', 'department_id', 'date_from', 'date_to']) ? 'show' : ''); ?> mb-4" id="filterCollapse">
        <div class="card border-0 shadow-sm border-top border-4 border-danger">
            <div class="card-body bg-light p-3">
                <form action="<?php echo e(route('admin.finance.expenses')); ?>" method="GET" class="row g-2" data-no-global-handler>
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Search Notes</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Keywords..." value="<?php echo e(request('search')); ?>">
                        </div>
                    </div>
                    
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Category</label>
                        <select name="category" class="form-select form-select-sm">
                            <option value="all">All Categories</option>
                            <option value="raw_materials" <?php echo e(request('category') == 'raw_materials' ? 'selected' : ''); ?>>Raw Materials</option>
                            <option value="marketing" <?php echo e(request('category') == 'marketing' ? 'selected' : ''); ?>>Marketing</option>
                            <option value="transport" <?php echo e(request('category') == 'transport' ? 'selected' : ''); ?>>Transport</option>
                            <option value="electricity" <?php echo e(request('category') == 'electricity' ? 'selected' : ''); ?>>Electricity</option>
                            <option value="salaries" <?php echo e(request('category') == 'salaries' ? 'selected' : ''); ?>>Salaries</option>
                            <option value="overtime" <?php echo e(request('category') == 'overtime' ? 'selected' : ''); ?>>Overtime</option>
                            <option value="other" <?php echo e(request('category') == 'other' ? 'selected' : ''); ?>>Other</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Department</label>
                        <select name="department_id" class="form-select form-select-sm">
                            <option value="all">All Depts</option>
                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($dept->id); ?>" <?php echo e(request('department_id') == $dept->id ? 'selected' : ''); ?>><?php echo e($dept->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">From Date</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo e(request('date_from')); ?>">
                    </div>

                    <div class="col-6 col-md-1">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">To Date</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo e(request('date_to')); ?>">
                    </div>

                    <div class="col-12 d-md-none mt-2">
                         <button type="submit" class="btn btn-danger btn-sm w-100 fw-bold">APPLY FILTERS</button>
                         <a href="<?php echo e(route('admin.finance.expenses')); ?>" class="btn btn-light btn-sm w-100 mt-2">RESET</a>
                    </div>
                    
                    <div class="col-md-auto d-none d-md-flex align-items-end ms-auto">
                        <div class="btn-group shadow-sm">
                            <button type="submit" class="btn btn-danger btn-sm px-3 fw-bold">APPLY</button>
                            <a href="<?php echo e(route('admin.finance.expenses')); ?>" class="btn btn-dark btn-sm px-3 fw-bold">RESET</a>
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
                            <th class="ps-4">Date</th>
                            <th>Category</th>
                            <th>Department</th>
                            <th>Amount</th>
                            <th class="text-start">Payment Method</th>
                            <th>Approved By</th>
                            <th class="text-start pe-4">Notes</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="ps-4"><?php echo e($expense->date->format('M d, Y')); ?></td>
                            <td class="fw-bold"><?php echo e($expense->category); ?></td>
                            <td><?php echo e($expense->department->name); ?></td>
                            <td class="text-danger fw-bold">TZS <?php echo e(number_format($expense->amount)); ?></td>
                            <td class="text-start"><?php echo e(ucfirst(str_replace('_', ' ', $expense->payment_method))); ?></td>
                            <td><?php echo e($expense->approvedBy->name); ?></td>
                            <td class="text-start small text-muted"><?php echo e($expense->notes); ?></td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <button type="button" onclick="printDirect('<?php echo e(route('admin.finance.expenses.voucher', $expense->id)); ?>')" class="btn btn-sm btn-light border text-dark px-2" title="Print Voucher">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light border text-primary px-2" 
                                            onclick="openEditExpenseModal(<?php echo e(json_encode($expense)); ?>)" title="Edit Expense">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light border text-danger px-2" 
                                            onclick="openDeleteExpenseModal(<?php echo e($expense->id); ?>)" title="Delete Expense">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No expenses recorded yet.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <?php echo e($expenses->links()); ?>

            </div>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo e(route('admin.finance.expenses.store')); ?>" method="POST" data-no-global-handler>
            <?php echo csrf_field(); ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Company Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Amount (TZS)</label>
                            <input type="number" class="form-control" name="amount" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category" required>
                                <option value="raw_materials">Raw Materials</option>
                                <option value="marketing">Marketing</option>
                                <option value="transport">Transport</option>
                                <option value="electricity">Electricity</option>
                                <option value="salaries">Salaries</option>
                                <option value="overtime">Overtime</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="department_id" required>
                                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($dept->id); ?>"><?php echo e($dept->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="date" value="<?php echo e(date('Y-m-d')); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" name="payment_method" required>
                                <option value="cash">Cash</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="bank">Bank</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Notes / Description</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger" data-no-global-handler>Save Expense</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Expense Modal -->
<div class="modal fade" id="editExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="editExpenseForm" method="POST" data-no-global-handler>
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Company Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Amount (TZS)</label>
                            <input type="number" class="form-control" name="amount" id="edit_amount" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category" id="edit_category" required>
                                <option value="raw_materials">Raw Materials</option>
                                <option value="marketing">Marketing</option>
                                <option value="transport">Transport</option>
                                <option value="electricity">Electricity</option>
                                <option value="salaries">Salaries</option>
                                <option value="overtime">Overtime</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="department_id" id="edit_department_id" required>
                                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($dept->id); ?>"><?php echo e($dept->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="date" id="edit_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" name="payment_method" id="edit_payment_method" required>
                                <option value="cash">Cash</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="bank">Bank</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Notes / Description</label>
                            <textarea class="form-control" name="notes" id="edit_notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger" data-no-global-handler>Update Expense</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Delete Expense Modal -->
<div class="modal fade" id="deleteExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <form id="deleteExpenseForm" method="POST" data-no-global-handler>
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this expense record? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $__env->startPush('scripts'); ?>
<script>
    function openEditExpenseModal(expense) {
        const form = document.getElementById('editExpenseForm');
        // Construct the URL using a placeholder and then replacing it
        // Since we don't have the route() function in JS directly without global vars, 
        // we can use a relative path or construct it.
        form.action = `/admin/finance/expenses/${expense.id}`;
        
        document.getElementById('edit_amount').value = expense.amount;
        document.getElementById('edit_category').value = expense.category;
        document.getElementById('edit_department_id').value = expense.department_id;
        
        // Format date to YYYY-MM-DD for the input[type=date]
        const date = new Date(expense.date);
        const formattedDate = date.toISOString().split('T')[0];
        document.getElementById('edit_date').value = formattedDate;
        
        document.getElementById('edit_payment_method').value = expense.payment_method;
        document.getElementById('edit_notes').value = expense.notes;
        
        const modal = new bootstrap.Modal(document.getElementById('editExpenseModal'));
        modal.show();
    }

    function openDeleteExpenseModal(id) {
        const form = document.getElementById('deleteExpenseForm');
        form.action = `/admin/finance/expenses/${id}`;
        
        const modal = new bootstrap.Modal(document.getElementById('deleteExpenseModal'));
        modal.show();
    }
</script>
<?php $__env->stopPush(); ?>

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

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/gotlaptopparts.com/Desktop/LaravelProject/chibo_sales/resources/views/admin/finance/expenses.blade.php ENDPATH**/ ?>