<?php $__env->startSection('title', 'All Pending Payments - CHIBO BRAND'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <!-- Header -->
        <!-- Header -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h2 class="fw-bold mb-1">Pending Payments</h2>
                        <p class="text-muted small mb-0">Track outstanding balances and debt</p>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <button class="btn btn-outline-dark btn-sm rounded-pill px-3 x-small" type="button"
                            data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                            <i class="fas fa-filter me-1"></i> Filter
                            <?php if(request()->anyFilled(['period', 'start_date', 'end_date', 'search'])): ?>
                                <span class="badge bg-primary ms-1">Active</span>
                            <?php endif; ?>
                        </button>
                        <button class="btn btn-outline-dark btn-sm rounded-pill px-3 x-small"
                                onclick="window.open('<?php echo e(route('admin.finance.pending-payments.print')); ?>?search=<?php echo e($search); ?>&period=<?php echo e($period ?? 'all'); ?>&start_date=<?php echo e(request('start_date', $dateFromStr)); ?>&end_date=<?php echo e(request('end_date', $dateToStr)); ?>', '_blank')">
                            <i class="fas fa-print me-1"></i> Print
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
        <div class="collapse <?php echo e(request()->anyFilled(['period', 'start_date', 'end_date', 'search']) ? 'show' : ''); ?> mb-4"
            id="filterCollapse">
            <div class="card border-0 shadow-sm border-top border-4 border-primary">
                <div class="card-body bg-light p-3">
                    <form action="<?php echo e(route('admin.finance.pending-payments')); ?>" method="GET" class="row g-2 align-items-end"
                        data-no-global-handler>
                        
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-bold x-small text-uppercase mb-1">Search Records</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control" placeholder="Customer or code..." value="<?php echo e($search); ?>">
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label fw-bold x-small text-uppercase mb-1">Debt Age (Created Within)</label>
                            <select name="period" id="periodSelect" class="form-select form-select-sm">
                                <option value="all" <?php echo e(($period ?? '') == 'all' ? 'selected' : ''); ?>>All Time (Total Debt)</option>
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
                                <a href="<?php echo e(route('admin.finance.pending-payments')); ?>" class="btn btn-dark btn-sm px-4 fw-bold">RESET</a>
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

        <!-- 12 NAVIGATABLE STATS CARDS - 6 Per Row on Desktop -->
        <div class="row g-2 g-md-3 mb-4">
            <!-- Total Due -->
            <div class="col-6 col-md-3">
                <div class="card shadow-sm h-100 border-0 border-start border-4 border-primary hover-lift">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2">
                                <i class="fas fa-hand-holding-dollar"></i>
                            </div>
                            <span class="text-uppercase x-small fw-bold text-muted">Total Due</span>
                        </div>
                        <div class="h5 mb-0 fw-bold text-primary">
                            TZS <?php echo e(number_format($pendingOrders->sum('balance') + $pendingTasks->sum('balance'))); ?>

                        </div>
                        <div class="x-small text-muted mt-2">Combined outstanding</div>
                    </div>
                </div>
            </div>
            <!-- Orders Due -->
            <div class="col-6 col-md-3">
                <div class="card shadow-sm h-100 border-0 border-start border-4 border-warning hover-lift">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-circle bg-warning bg-opacity-10 text-warning me-2">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <span class="text-uppercase x-small fw-bold text-muted">Orders Due</span>
                        </div>
                        <div class="h5 mb-0 fw-bold text-dark"><?php echo e($pendingOrders->count()); ?> Orders</div>
                        <div class="x-small text-warning mt-2">TZS <?php echo e(number_format($pendingOrders->sum('balance'))); ?></div>
                    </div>
                </div>
            </div>
            <!-- Tasks Due -->
            <div class="col-6 col-md-3">
                <div class="card shadow-sm h-100 border-0 border-start border-4 border-info hover-lift">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-circle bg-info bg-opacity-10 text-info me-2">
                                <i class="fas fa-palette"></i>
                            </div>
                            <span class="text-uppercase x-small fw-bold text-muted">Tasks Due</span>
                        </div>
                        <div class="h5 mb-0 fw-bold text-dark"><?php echo e($pendingTasks->count()); ?> Tasks</div>
                        <div class="x-small text-info mt-2">TZS <?php echo e(number_format($pendingTasks->sum('balance'))); ?></div>
                    </div>
                </div>
            </div>
            <!-- Progress -->
            <div class="col-6 col-md-3">
                <div class="card shadow-sm h-100 border-0 border-start border-4 border-success hover-lift">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-circle bg-success bg-opacity-10 text-success me-2">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <span class="text-uppercase x-small fw-bold text-muted">Progress</span>
                        </div>
                        <?php
                            $totalVal = ($pendingOrders->sum('total_amount') + $pendingTasks->sum('price'));
                            $totalPaid = ($pendingOrders->sum('amount_paid') + $pendingTasks->sum('amount_paid'));
                            $percent = $totalVal > 0 ? ($totalPaid / $totalVal) * 100 : 0;
                        ?>
                        <div class="h5 mb-0 fw-bold text-success"><?php echo e(number_format($percent, 1)); ?>%</div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-success" style="width: <?php echo e($percent); ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Managed Tabs -->
        <div class="mb-4">
            <nav class="nav nav-pills p-1 bg-light rounded-3 d-flex" id="pendingTabList" role="tablist" style="max-width: 600px; margin: 0 auto;">
                <button class="nav-link flex-fill rounded-3 py-2 fw-bold text-uppercase tab-trigger active" 
                        id="tasks-tab-btn" 
                        data-bs-toggle="tab" 
                        data-bs-target="#tasks-content" 
                        role="tab"
                        aria-controls="tasks-content"
                        aria-selected="true"
                        style="transition: all 0.3s ease; font-size: 11px; letter-spacing: 0.5px;">
                    <i class="fas fa-palette me-2"></i>Design Tasks <span class="badge bg-white text-dark ms-1"><?php echo e($pendingTasks->count()); ?></span>
                </button>
                <button class="nav-link flex-fill rounded-3 py-2 fw-bold text-uppercase tab-trigger" 
                        id="orders-tab-btn" 
                        data-bs-toggle="tab" 
                        data-bs-target="#orders-content" 
                        role="tab"
                        aria-controls="orders-content"
                        aria-selected="false"
                        style="transition: all 0.3s ease; font-size: 11px; letter-spacing: 0.5px;">
                    <i class="fas fa-shopping-cart me-2"></i>Product Orders <span class="badge bg-white text-dark ms-1"><?php echo e($pendingOrders->count()); ?></span>
                </button>
            </nav>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
            <div class="card-body p-0">
                <div class="tab-content" id="pendingTabsContent">
                    <!-- Design Tasks Tab -->
                    <div class="tab-pane fade show active" id="tasks-content" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 x-small border-0 text-muted">TASK</th>
                                        <th class="x-small border-0 text-muted">CUSTOMER</th>
                                        <th class="x-small border-0 text-muted text-end">TOTAL</th>
                                        <th class="x-small border-0 text-muted text-end">PAID</th>
                                        <th class="x-small border-0 text-muted text-end">BALANCE</th>
                                        <th class="x-small border-0 text-muted">STATUS</th>
                                        <th class="pe-4 x-small border-0 text-muted text-end">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $pendingTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <?php
                                            // Total for task = base price (potential VAT) + delivery cost - discount
                                            $basePrice = $task->requires_receipt ? $task->price * 1.18 : $task->price;
                                            $deliveryCost = (float) ($task->delivery_cost ?? 0);
                                            $deliveryDiscount = (float) ($task->delivery_discount ?? 0);
                                            $taskTotal = $basePrice + $deliveryCost - $deliveryDiscount;
                                        ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold small"><?php echo e($task->title); ?></div>
                                                <div class="x-small text-muted"><?php echo e($task->task_code); ?></div>
                                            </td>
                                            <td>
                                                <div class="small fw-bold"><?php echo e($task->customer->name ?? 'Walk-in'); ?></div>
                                                <div class="x-small text-muted"><?php echo e($task->customer->phone ?? 'N/A'); ?></div>
                                            </td>
                                            <td class="text-end small"><?php echo e(number_format($taskTotal)); ?></td>
                                            <td class="text-end small text-success"><?php echo e(number_format($task->amount_paid)); ?></td>
                                            <td class="text-end small text-danger fw-bold"><?php echo e(number_format($task->balance)); ?>

                                            </td>
                                            <td class="small">
                                                <span
                                                    class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 x-small text-uppercase">
                                                    <?php echo e(str_replace('_', ' ', $task->status)); ?>

                                                </span>
                                            </td>
                                             <td class="pe-4 text-end">
                                                <button type="button"
                                                    class="btn btn-link text-info p-0 btn-open-task-payment border-0"
                                                    data-id="<?php echo e($task->id); ?>" data-balance="<?php echo e($task->balance); ?>"
                                                    data-title="<?php echo e($task->title); ?>" title="Pay">
                                                    <i class="fas fa-money-bill-wave fs-5"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <div class="mb-2"><i class="fas fa-check-circle fa-2x opacity-25"></i></div>
                                                <p class="mb-0">No pending design tasks found.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Orders Tab -->
                    <div class="tab-pane fade" id="orders-content" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 x-small border-0 text-muted">ORDER</th>
                                        <th class="x-small border-0 text-muted">CUSTOMER</th>
                                        <th class="x-small border-0 text-muted text-end">TOTAL</th>
                                        <th class="x-small border-0 text-muted text-end">PAID</th>
                                        <th class="x-small border-0 text-muted text-end">BALANCE</th>
                                        <th class="x-small border-0 text-muted">DATE</th>
                                        <th class="pe-4 x-small border-0 text-muted text-end">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $pendingOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold small"><?php echo e($order->order_code); ?></div>
                                                <?php if($order->payment_status === 'partial'): ?>
                                                    <span
                                                        class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 x-small">PARTIAL</span>
                                                <?php else: ?>
                                                    <span
                                                        class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 x-small">PENDING</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="small fw-bold"><?php echo e($order->user->name ?? 'Walk-in'); ?></div>
                                                <div class="x-small text-muted"><?php echo e($order->user->phone ?? 'N/A'); ?></div>
                                            </td>
                                            <td class="text-end small"><?php echo e(number_format($order->total_amount)); ?></td>
                                            <td class="text-end small text-success"><?php echo e(number_format($order->amount_paid)); ?>

                                            </td>
                                            <td class="text-end small text-danger fw-bold"><?php echo e(number_format($order->balance)); ?>

                                            </td>
                                            <td class="small text-muted"><?php echo e($order->created_at->format('d M, Y')); ?></td>
                                             <td class="pe-4 text-end">
                                                <button type="button"
                                                    class="btn btn-link text-primary p-0 btn-open-order-payment border-0"
                                                    data-id="<?php echo e($order->id); ?>" data-code="<?php echo e($order->order_code); ?>"
                                                    data-paid="<?php echo e($order->amount_paid); ?>" data-balance="<?php echo e($order->balance); ?>" title="Pay">
                                                    <i class="fas fa-money-bill-wave fs-5"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <div class="mb-2"><i class="fas fa-check-circle fa-2x opacity-25"></i></div>
                                                <p class="mb-0">No pending product orders found.</p>
                                            </td>
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

    <!-- Order Payment Modal -->
    <div class="modal fade" id="orderPaymentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <form id="orderPaymentForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="modal-header bg-primary text-white border-0 py-3">
                        <h6 class="modal-title fw-bold mb-0"><i class="fas fa-shopping-cart me-2"></i>Update Order Payment
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-light border py-2 mb-3">
                            <small style="font-size: 12px;"><i class="fas fa-info-circle me-1 text-muted"></i>Order: <strong
                                    id="modal_order_code"></strong></small>
                        </div>

                        <div class="row mb-3 gx-2 text-center">
                            <div class="col-6">
                                <div class="border rounded p-2 bg-light">
                                    <div class="x-small text-muted text-uppercase fw-bold mb-0">Already Paid</div>
                                    <div class="small mb-0 text-success fw-bold" id="modal_order_paid"></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 bg-light">
                                    <div class="x-small text-muted text-uppercase fw-bold mb-0">Balance Due</div>
                                    <div class="small mb-0 text-danger fw-bold" id="modal_order_balance_display"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Amount to Pay (TZS)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="fas fa-money-bill"></i></span>
                                <input type="number" step="0.01" class="form-control" id="order_payment_amount"
                                    name="amount" required>
                                <button class="btn btn-outline-secondary" type="button" id="btnFillOrderFull">Full</button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Payment Method</label>
                            <select class="form-select form-select-sm" name="payment_method" required>
                                <option value="Cash">Cash</option>
                                <option value="Mobile Money">Mobile Money</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Card">Card</option>
                            </select>
                        </div>

                        <div class="mb-0">
                            <label class="form-label small fw-bold">Note (Optional)</label>
                            <textarea class="form-control form-control-sm" name="note" rows="2"
                                placeholder="Reference no, transaction ID etc..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top p-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold"><i
                                class="fas fa-check me-1"></i>Process Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Design Task Payment Modal (Matches design-tasks/index.blade.php) -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <form id="paymentForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="modal-header bg-white border-bottom py-3">
                        <h6 class="modal-title text-dark fw-bold mb-0" style="font-size: 14px;"><i
                                class="fas fa-money-bill-wave me-2"></i>Payment</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-light border py-2 mb-3">
                            <small style="font-size: 12px;"><i class="fas fa-info-circle me-1 text-muted"></i>Task: <strong
                                    id="paymentTaskTitle"></strong></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark" style="font-size: 12px;">Balance</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white" style="font-size: 12px;"><i
                                        class="fas fa-wallet"></i></span>
                                <input type="text" class="form-control" id="displayBalance" readonly
                                    style="font-size: 12px;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label fw-bold text-dark" style="font-size: 12px;">Amount <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white" style="font-size: 12px;"><i
                                        class="fas fa-money-bill"></i></span>
                                <input type="number" class="form-control" id="paymentAmount" name="amount" min="1" step="1"
                                    required style="font-size: 12px;">
                            </div>
                            <div class="form-text" style="font-size: 11px;">Max: <span id="maxPaymentText"></span></div>
                        </div>

                        <div class="mb-3">
                            <label for="payment_method" class="form-label fw-bold text-dark" style="font-size: 12px;">Method
                                <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="paymentMethod" name="payment_method" required
                                style="font-size: 12px;">
                                <option value="Cash">Cash</option>
                                <option value="Mobile Money">Mobile Money</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Card">Card</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark" style="font-size: 12px;">Note <span
                                    class="text-muted" style="font-size: 11px;">(Optional)</span></label>
                            <textarea class="form-control form-control-sm" name="notes" rows="2" placeholder="Notes..."
                                style="font-size: 12px;"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="additional_cost" class="form-label fw-bold text-dark" style="font-size: 12px;">
                                Additional Cost (TZS) <span class="text-muted" style="font-size: 11px;">(optional)</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white" style="font-size: 12px;"><i
                                        class="fas fa-plus-circle"></i></span>
                                <input type="number" id="additional_cost" name="additional_cost" class="form-control"
                                    min="0" step="0.01" value="0" style="font-size: 12px;" placeholder="0.00">
                            </div>
                            <small class="form-text text-muted" style="font-size: 11px;">If you enter a value > 0, you must
                                provide a reason.</small>
                        </div>

                        <div class="mb-0">
                            <label for="additional_cost_reason" class="form-label fw-bold text-dark"
                                style="font-size: 12px;">
                                Additional Cost Reason
                            </label>
                            <textarea class="form-control form-control-sm" id="additional_cost_reason"
                                name="additional_cost_reason" rows="2" placeholder="Delivery or other items..."
                                style="font-size: 12px;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top p-3">
                        <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-4"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-dark rounded-pill px-4 fw-bold"><i
                                class="fas fa-check me-1"></i>Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php $__env->startPush('styles'); ?>
        <style>
            .letter-spacing-1 {
                letter-spacing: 1px;
            }

            .x-small {
                font-size: 10px;
            }

            .icon-circle {
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 8px;
                font-size: 0.8rem;
            }

            @media (max-width: 768px) {
                .h5 {
                    font-size: 1rem !important;
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

            /* Custom Pill Tabs Styling */
            .tab-trigger {
                color: #6c757d;
                border: none;
                background: transparent;
            }
            .tab-trigger:hover {
                color: var(--bs-primary);
                background: rgba(0,0,0,0.03);
            }
            .tab-trigger.active {
                background: var(--bs-primary) !important;
                color: #fff !important;
                box-shadow: 0 4px 15px rgba(var(--bs-primary-rgb), 0.3);
            }
            .tab-trigger.active .badge {
                color: var(--bs-primary) !important;
            }

            .hover-lift {
                transition: transform 0.2s ease;
            }

            .hover-lift:hover {
                transform: translateY(-3px);
            }
        </style>
    <?php $__env->stopPush(); ?>

    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // --- Product Order Payments ---
                const orderModalEl = document.getElementById('orderPaymentModal');
                const orderModal = new bootstrap.Modal(orderModalEl);
                let currentOrderBalance = 0;

                document.querySelectorAll('.btn-open-order-payment').forEach(btn => {
                    btn.addEventListener('click', function () {
                        const id = this.dataset.id;
                        const code = this.dataset.code;
                        const paid = parseFloat(this.dataset.paid);
                        const balance = parseFloat(this.dataset.balance);

                        currentOrderBalance = balance;
                        document.getElementById('modal_order_code').textContent = code;
                        document.getElementById('modal_order_paid').textContent = 'TZS ' + new Intl.NumberFormat().format(paid);
                        document.getElementById('modal_order_balance_display').textContent = 'TZS ' + new Intl.NumberFormat().format(balance);

                        const amountInput = document.getElementById('order_payment_amount');
                        amountInput.value = balance;
                        amountInput.max = balance;

                        const form = document.getElementById('orderPaymentForm');
                        form.action = `/admin/payments/${id}/update`;

                        orderModal.show();
                    });
                });

                document.getElementById('btnFillOrderFull')?.addEventListener('click', function () {
                    document.getElementById('order_payment_amount').value = currentOrderBalance;
                });

                // --- Design Task Payments ---
                const taskModalEl = document.getElementById('paymentModal');
                const taskModal = new bootstrap.Modal(taskModalEl);
                let currentTaskBalance = 0;

                document.querySelectorAll('.btn-open-task-payment').forEach(btn => {
                    btn.addEventListener('click', function () {
                        const taskId = this.dataset.id;
                        const balance = parseFloat(this.dataset.balance);
                        const taskTitle = this.dataset.title;

                        currentTaskBalance = balance;

                        const form = document.getElementById('paymentForm');
                        const taskTitleEl = document.getElementById('paymentTaskTitle');
                        const displayBalanceEl = document.getElementById('displayBalance');
                        const paymentAmountEl = document.getElementById('paymentAmount');
                        const maxPaymentTextEl = document.getElementById('maxPaymentText');
                        const additionalCostEl = document.getElementById('additional_cost');
                        const additionalCostReasonEl = document.getElementById('additional_cost_reason');

                        // Set form action
                        form.action = `/admin/design-tasks/${taskId}/update-payment`;

                        // Set task details
                        if (taskTitleEl) taskTitleEl.textContent = taskTitle;
                        if (displayBalanceEl) displayBalanceEl.value = new Intl.NumberFormat('en-US').format(balance);

                        // Set amount constraints
                        if (paymentAmountEl) {
                            paymentAmountEl.max = balance;
                            paymentAmountEl.value = balance; // Default to full balance
                        }
                        if (maxPaymentTextEl) maxPaymentTextEl.textContent = new Intl.NumberFormat('en-US').format(balance);

                        // Reset additional cost fields for each task
                        if (additionalCostEl) additionalCostEl.value = 0;
                        if (additionalCostReasonEl) additionalCostReasonEl.value = '';

                        taskModal.show();
                    });
                });
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/gotlaptopparts.com/Desktop/LaravelProject/chibo_sales/resources/views/admin/finance/pending-payments.blade.php ENDPATH**/ ?>