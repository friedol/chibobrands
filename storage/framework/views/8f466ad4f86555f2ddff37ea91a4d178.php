<?php $__env->startSection('page-title', 'Design Tasks'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    /* Premium SaaS Pro Theme */
    :root {
        --premium-primary: #4f46e5;
        --premium-success: #10b981;
        --premium-warning: #f59e0b;
        --premium-danger: #ef4444;
        --premium-info: #0ea5e9;
        --premium-dark: #1e293b;
        --premium-gray: #64748b;
    }

    .icon-circle {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .hover-lift {
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.1) !important;
    }

    .card-metric .h3, .card-metric .h5 {
        font-family: 'Nunito Sans', sans-serif;
        letter-spacing: -0.5px;
    }

    /* Mobile Responsive Adjustments */
    @media (max-width: 768px) {
        .container-fluid { padding: 0.75rem; }
        .h5 { font-size: 1.1rem !important; }
        .card-body { padding: 1rem !important; }
        .icon-circle { width: 32px; height: 32px; font-size: 11px; }
        .x-small { font-size: 9px; }
    }

    /* Table Refinement */
    .table thead th {
        background-color: #f8fafc;
        color: var(--premium-gray);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-top: none;
        padding: 1rem 0.75rem;
    }

    .table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        font-size: 13px;
        color: #334155;
    }

    /* Mobile View Toggle Logic */
    .view-hidden { display: none !important; }
    @media (min-width: 768px) {
        .mobile-view-toggle { display: none !important; }
        .desktop-table { display: block !important; }
        .mobile-cards { display: none !important; }
    }

    /* Mobile Card Styles */
    .task-card {
        background: #fff;
        border: 1px solid #f1f5f9;
        border-radius: 16px;
        margin-bottom: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        overflow: hidden;
        position: relative;
        transition: all 0.2s;
    }
    
    .task-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
    }

    .task-card.status-pending::before { background: var(--premium-warning); }
    .task-card.status-in_progress::before { background: var(--premium-primary); }
    .task-card.status-in_review::before { background: #8b5cf6; }
    .task-card.status-printing::before { background: #f97316; }
    .task-card.status-completed::before { background: var(--premium-success); }
    .task-card.status-confirmed::before { background: var(--premium-info); }
    .task-card.status-super_completed::before { background: #059669; }
    .task-card.status-rejected::before { background: var(--premium-danger); }
    .task-card.status-cancelled::before { background: var(--premium-dark); }
    
    .task-stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        margin-top: 1rem;
    }
    
    .task-stat-item {
        background: #f8fafc;
        padding: 0.75rem;
        border-radius: 12px;
    }
    
    .task-stat-label {
        font-size: 9px;
        font-weight: 800;
        color: var(--premium-gray);
        text-transform: uppercase;
        margin-bottom: 2px;
        display: block;
    }
    
    .task-stat-value {
        font-size: 0.85rem;
        font-weight: 700;
        color: #1e293b;
    }

    .btn-action-pill {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        background: transparent !important;
        transition: all 0.2s;
    }

    .btn-action-pill:hover {
        transform: scale(1.15);
        color: inherit !important;
    }

    .btn-action-pill:active { transform: scale(0.92); }
    
    .bg-action-blue { color: #2563eb !important; }
    .bg-action-green { color: #16a34a !important; }
    .bg-action-yellow { color: #d97706 !important; }
    .bg-action-red { color: #dc2626 !important; }
    .bg-action-gray { color: #475569 !important; }
    .bg-action-purple { color: #7c3aed !important; }
    .bg-action-cyan { color: #0891b2 !important; }

    .dropdown-menu { z-index: 1060 !important; border: none; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-radius: 12px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-2 px-md-4 py-3 py-md-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-row justify-content-between align-items-center mb-3 gap-2">
                <div class="flex-grow-1">
                    <h1 class="h5 mb-0 text-dark fw-bold" style="font-family: 'Nunito Sans', sans-serif;">Design Tasks Management</h1>
                    <!-- <p class="text-muted mb-0 small" style="font-size: 13px;">
                        <?php if($user->role === 'receptionist' || $user->role === 'operator' || $user->role === 'accountant'): ?>
                            Manage requirements & assign to designers
                        <?php elseif($user->role === 'designer'): ?>
                            View assigned tasks & provide feedback
                        <?php else: ?>
                            Manage all design tasks system-wide
                        <?php endif; ?>
                    </p> -->
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php if(in_array($user->role, ['receptionist', 'operator', 'accountant', 'admin', 'super_admin'])): ?>
                    <a href="<?php echo e(route('admin.design-tasks.create')); ?>" class="btn btn-dark btn-sm rounded-pill px-3 shadow-none" style="font-size: 13px;">
                        <i class="fas fa-plus me-1"></i>New Task
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Stats Cards (Dashboard Style) -->
            <?php if(isset($taskStats)): ?>
            <div class="row g-2 g-md-3 mb-4">
                <div class="col-6 col-md-3 col-lg-2 stats-col">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-primary hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2">
                                    <i class="fas fa-list"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Total</span>
                            </div>
                            <div class="h3 mb-0 fw-bold"><?php echo e(number_format($taskStats['total'])); ?></div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3 col-lg-2 stats-col">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-warning hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-warning bg-opacity-10 text-warning me-2">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Pending</span>
                            </div>
                            <div class="h3 mb-0 fw-bold"><?php echo e(number_format($taskStats['pending'])); ?></div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3 col-lg-2 stats-col">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-info hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-info bg-opacity-10 text-info me-2">
                                    <i class="fas fa-spinner"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Progress</span>
                            </div>
                            <div class="h3 mb-0 fw-bold"><?php echo e(number_format($taskStats['in_progress'])); ?></div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3 col-lg-2 stats-col">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-secondary hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-secondary bg-opacity-10 text-secondary me-2">
                                    <i class="fas fa-print"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Production</span>
                            </div>
                            <div class="h3 mb-0 fw-bold"><?php echo e(number_format($taskStats['printing'] + $taskStats['printed'])); ?></div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3 col-lg-2 stats-col">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-success hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-success bg-opacity-10 text-success me-2">
                                    <i class="fas fa-check-double"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Done</span>
                            </div>
                            <div class="h3 mb-0 fw-bold"><?php echo e(number_format($taskStats['completed'] + $taskStats['super_completed'])); ?></div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3 col-lg-2 stats-col">
                    <div class="card shadow-sm h-100 border-0 border-start border-4 border-dark hover-lift">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="icon-circle bg-dark bg-opacity-10 text-dark me-2">
                                    <i class="fas fa-shipping-fast"></i>
                                </div>
                                <span class="text-uppercase x-small fw-bold text-muted">Delivered</span>
                            </div>
                            <div class="h3 mb-0 fw-bold"><?php echo e(number_format($taskStats['delivered'])); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Search and Advanced Filters -->
            <div class="collapse show mb-4" id="filterCollapse">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-3">
                        <form action="<?php echo e(route('admin.design-tasks.index')); ?>" method="GET" class="row g-2 align-items-end" data-no-global-handler>
                            <div class="col-12 col-md-3">
                                <label class="text-uppercase x-small fw-bold text-muted mb-1 d-block">Search Task</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted opacity-50"></i></span>
                                    <input type="text" name="search" class="form-control border-start-0" placeholder="ID, Title, Customer..." value="<?php echo e(request('search')); ?>">
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="text-uppercase x-small fw-bold text-muted mb-1 d-block">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Statuses</option>
                                    <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($key); ?>" <?php echo e(request('status') == $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="text-uppercase x-small fw-bold text-muted mb-1 d-block">Designer</label>
                                <select name="designer_id" class="form-select form-select-sm">
                                    <option value="">All Designers</option>
                                    <?php $__currentLoopData = $all_designers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $designer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($designer->id); ?>" <?php echo e(request('designer_id') == $designer->id ? 'selected' : ''); ?>><?php echo e($designer->name); ?> (<?php echo e(ucfirst($designer->role)); ?>)</option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="text-uppercase x-small fw-bold text-muted mb-1 d-block">Receptionist</label>
                                <select name="receptionist_id" class="form-select form-select-sm">
                                    <option value="">All</option>
                                    <?php $__currentLoopData = $all_receptionists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($recp->id); ?>" <?php echo e(request('receptionist_id') == $recp->id ? 'selected' : ''); ?>><?php echo e($recp->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="text-uppercase x-small fw-bold text-muted mb-1 d-block">Date Range</label>
                                <div class="input-group input-group-sm">
                                    <input type="date" name="date_from" class="form-control" value="<?php echo e(request('date_from')); ?>">
                                    <input type="date" name="date_to" class="form-control" value="<?php echo e(request('date_to')); ?>">
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-dark btn-sm flex-grow-1">Filter</button>
                                    <button type="button" class="btn btn-outline-dark btn-sm" onclick="printDirect('<?php echo e(route('admin.design-tasks.print-filtered', request()->all())); ?>')">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <a href="<?php echo e(route('admin.design-tasks.index')); ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-undo"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Status Badges (Quick Filter) -->
            <div class="d-flex flex-wrap gap-1 gap-sm-2 mb-3">
                <a href="<?php echo e(request()->fullUrlWithQuery(['status' => '', 'delivery_filter' => ''])); ?>" 
                   class="badge bg-dark text-decoration-none <?php echo e(!request('status') && !request('delivery_filter') ? 'border border-2 border-primary' : ''); ?>"
                   style="padding: 0.35rem 0.55rem; font-size: 11px;">
                    All
                </a>
                <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(request()->fullUrlWithQuery(['status' => $key, 'delivery_filter' => ''])); ?>" 
                       class="badge text-decoration-none <?php echo e(request('status') == $key && !request('delivery_filter') ? 'border border-2 border-dark' : ''); ?>"
                       style="padding: 0.35rem 0.55rem; font-size: 11px; background-color: <?php echo e([
                           'pending' => '#ffc107',
                           'in_progress' => '#0d6efd',
                           'in_review' => '#6f42c1',
                           'printing' => '#fd7e14',
                           'printed' => '#6c757d',
                           'completed' => '#198754',
                           'confirmed' => '#0dcaf0',
                           'super_completed' => '#20c997',
                           'rejected' => '#dc3545',
                           'cancelled' => '#1e293b'
                       ][$key] ?? '#6c757d'); ?>; color: #fff;">
                        <?php echo e($label); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                
                <?php if(request('status') || request('delivery_filter')): ?>
                <a href="<?php echo e(request()->fullUrlWithQuery(['status' => '', 'delivery_filter' => '', 'is_loss' => ''])); ?>" 
                   class="badge bg-danger text-decoration-none"
                   style="padding: 0.35rem 0.55rem; font-size: 11px;">
                    <i class="fas fa-times me-1"></i>Clear Filters
                </a>
                <?php endif; ?>
                
                <!-- Loss Filter -->
                <a href="<?php echo e(request()->fullUrlWithQuery(['is_loss' => '1', 'status' => '', 'delivery_filter' => ''])); ?>" 
                   class="badge text-white text-decoration-none <?php echo e(request('is_loss') == '1' ? 'border border-2 border-dark' : ''); ?>"
                   style="padding: 0.35rem 0.55rem; font-size: 11px; background-color: #f8285b;">
                    <i class="fas fa-heart-crack me-1"></i>Losses (Hasara)
                </a>

                <!-- Delivered Filter -->
                <a href="<?php echo e(request()->fullUrlWithQuery(['delivery_filter' => 'delivered', 'status' => ''])); ?>" 
                   class="badge text-white text-decoration-none <?php echo e(request('delivery_filter') == 'delivered' ? 'border border-2 border-dark' : ''); ?>"
                   style="padding: 0.35rem 0.55rem; font-size: 11px; background-color: #055160;">
                    <i class="fas fa-check-circle me-1"></i>Delivered
                </a>
            </div>
        </div>
    </div>

    <!-- Tasks Table -->
    <div class="card border-0 main-tasks-wrapper" style="box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);">
        <div class="card-header bg-white border-bottom py-2">
            <h6 class="mb-0 fw-bold text-dark" style="font-size: 14px;"><i class="fas fa-list-check me-2"></i>All Tasks</h6>
        </div>
        <div class="card-body p-0">
            <!-- Mobile Card Layout -->
            <div id="mobileCardsWrapper" class="mobile-cards" style="padding: 0.25rem;">
                <?php
                    $groupedTasks = $tasks->groupBy(function($item) {
                        return ($item->customer_id ?? 'unknown') . '_' . $item->created_at->format('Y-m-d');
                    })->sortByDesc(function($group) {
                        return $group->max('created_at');
                    });
                ?>
                
                <?php $__empty_1 = true; $__currentLoopData = $groupedTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupKey => $customerTasks): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php 
                        $firstInGroup = $customerTasks->first(); 
                    ?>
                    <div class="px-2 py-2 mb-2 bg-light rounded-pill d-flex justify-content-between align-items-center border" style="font-size: 11px;">
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-dark"><i class="fas fa-user me-1"></i><?php echo e($firstInGroup->customer->name ?? 'N/A'); ?></span>
                            <span class="text-primary fw-bold" style="font-size: 10px;"><i class="far fa-calendar-alt me-1"></i><?php echo e($firstInGroup->created_at->format('M d, Y')); ?></span>
                        </div>
                        <div class="d-flex gap-1">
                            <?php if($customerTasks->count() > 1): ?>
                            <button type="button" class="btn btn-dark btn-sm rounded-pill py-0 px-2" style="font-size: 9px;" onclick="printDirect('<?php echo e(route('admin.design-tasks.print-filtered', ['customer_id' => $firstInGroup->customer_id, 'date' => $firstInGroup->created_at->format('Y-m-d')])); ?>')">
                                <i class="fas fa-print me-1"></i>Print Group
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php $__currentLoopData = $customerTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="task-card status-<?php echo e($task->status); ?>">
                        <div class="task-card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="x-small text-muted">
                                        <?php echo e($task->task_code); ?> • <?php echo e($task->created_at->format('H:i')); ?>

                                    </div>
                                </div>
                                <span class="badge rounded-pill" style="font-size: 10px; background-color: <?php echo e([
                                    'pending' => '#ffc107',
                                    'in_progress' => '#0d6efd',
                                    'in_review' => '#6f42c1',
                                    'printing' => '#fd7e14',
                                    'printed' => '#6c757d',
                                    'completed' => '#198754',
                                    'confirmed' => '#0dcaf0',
                                    'super_completed' => '#20c997',
                                    'rejected' => '#dc3545',
                                    'cancelled' => '#1e293b'
                                ][$task->status] ?? '#6c757d'); ?>; color: #fff;">
                                    <?php echo e($task->status_label); ?>

                                </span>
                            </div>
                        </div>
                        <div class="task-card-body">
                            <div class="mb-2">
                                <div class="fw-bold text-dark small">
                                    <?php echo e($task->title); ?>

                                </div>
                            </div>
                            
                            <div class="task-stats-grid">
                                <div class="task-stat-item">
                                    <span class="task-stat-label">Paid</span>
                                    <span class="task-stat-value text-success"><?php echo e(number_format($task->amount_paid, 0)); ?></span>
                                </div>
                                <div class="task-stat-item">
                                    <span class="task-stat-label">Balance</span>
                                    <span class="task-stat-value <?php echo e($task->balance > 0 ? 'text-danger' : 'text-success'); ?>"><?php echo e(number_format($task->balance, 0)); ?></span>
                                </div>
                                <div class="task-stat-item">
                                    <span class="task-stat-label">Deadline</span>
                                    <span class="task-stat-value <?php echo e($task->deadline && $task->deadline->isPast() && $task->status !== 'completed' ? 'text-danger' : ''); ?>">
                                        <?php echo e($task->deadline ? $task->deadline->format('M d') : 'None'); ?>

                                    </span>
                                </div>
                                <div class="task-stat-item">
                                    <span class="task-stat-label">Priority</span>
                                    <span class="task-stat-value">
                                        <i class="fas fa-flag me-1" style="color: <?php echo e($task->priority == 1 ? 'var(--premium-danger)' : ($task->priority == 3 ? 'var(--premium-warning)' : 'var(--premium-info)')); ?>"></i>
                                        <?php echo e($task->priority_label); ?>

                                    </span>
                                </div>
                            </div>

                            <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn-action-pill bg-action-gray" title="Print" onclick="printDirect('<?php echo e(route('admin.design-tasks.print-invoice', $task)); ?>')">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <button type="button" class="btn-action-pill bg-action-blue" title="View" onclick="viewInvoice(<?php echo e($task->id); ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="<?php echo e(route('admin.design-tasks.show', $task)); ?>" class="btn-action-pill bg-action-purple" title="Details">
                                        <i class="fas fa-list-check"></i>
                                    </a>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <?php if(in_array($user->role, ['admin', 'super_admin', 'manager', 'receptionist', 'operator', 'accountant']) && $task->delivery_status !== 'delivered'): ?>
                                    <button type="button" onclick="openEditTaskModal(<?php echo e($task->id); ?>)" class="btn-action-pill bg-action-gray" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <?php endif; ?>

                                    <?php if($task->balance > 0 && in_array($user->role, ['accountant', 'admin', 'super_admin'])): ?>
                                        <button type="button" class="btn-action-pill bg-action-green" onclick="openPaymentModal(<?php echo e($task->id); ?>, <?php echo e($task->balance); ?>, '<?php echo e(addslashes($task->title)); ?>')" title="Payment">
                                            <i class="fas fa-wallet"></i>
                                        </button>
                                    <?php endif; ?>

                                    <?php if(in_array($user->role, ['receptionist', 'accountant', 'admin', 'super_admin']) && $task->status === 'completed'): ?>
                                        <button type="button" class="btn-action-pill bg-action-cyan" title="Confirm" onclick="if(confirm('Confirm?')) { document.getElementById('status-form-<?php echo e($task->id); ?>-confirmed-mobile').submit(); }">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <form id="status-form-<?php echo e($task->id); ?>-confirmed-mobile" action="<?php echo e(route('admin.design-tasks.update-status', $task)); ?>" method="POST" class="d-none"><?php echo csrf_field(); ?><input type="hidden" name="status" value="confirmed"></form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-list-check text-muted opacity-25" style="font-size: 3rem;"></i>
                        <p class="mt-3 text-muted">No design tasks found</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Desktop Table -->
            <div id="desktopTableWrapper" class="desktop-table view-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-white border-bottom">
                        <tr>
                            <th class="ps-4">Customer</th>
                            <th>Task Details</th>
                            <th>Status</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th class="py-2 py-md-3 px-2 px-md-4 d-none d-md-table-cell text-start" style="font-size: 13px; text-transform: capitalize;">deadline</th>
                            <?php if($user->role !== 'designer'): ?>
                            <th class="py-2 py-md-3 px-2 px-md-4 d-none d-lg-table-cell text-start" style="font-size: 13px; text-transform: capitalize;">designer</th>
                            <th class="py-2 py-md-3 px-2 px-md-4 d-none d-lg-table-cell text-start" style="font-size: 13px; text-transform: capitalize;">operator</th>
                            <?php endif; ?>
                            <?php if($user->role !== 'receptionist'): ?>
                            <th class="py-2 py-md-3 px-2 px-md-4 d-none d-lg-table-cell text-start" style="font-size: 13px; text-transform: capitalize;">receptionist</th>
                            <?php endif; ?>
                            <th class="py-2 py-md-3 px-2 px-md-4 d-none d-sm-table-cell text-start" style="font-size: 13px; text-transform: capitalize;">priority</th>
                            <th class="py-2 py-md-3 px-2 px-md-4 text-start" style="font-size: 13px; text-transform: capitalize;">actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $groupedTasks = $tasks->groupBy(function($item) {
                                return ($item->customer_id ?? 'unknown') . '_' . $item->created_at->format('Y-m-d');
                            })->sortByDesc(function($group) {
                                return $group->max('created_at');
                            });
                        ?>
                        
                        <?php $__empty_1 = true; $__currentLoopData = $groupedTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customerId => $customerTasks): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php $__currentLoopData = $customerTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="border-bottom">
                                <?php if($index === 0): ?>
                                <td class="py-2 py-md-3 px-2 px-md-4 align-middle" rowspan="<?php echo e($customerTasks->count()); ?>" style="border-right: 1px solid rgba(0,0,0,0.05); background-color: rgba(248, 249, 250, 0.5);">
                                    <div class="fw-bold" style="font-size: 0.8rem; color: #333; text-transform: none;"><?php echo e($task->customer->name ?? 'N/A'); ?></div>
                                    <div class="text-primary fw-bold" style="font-size: 0.75rem;"><i class="far fa-calendar-alt me-1"></i><?php echo e($task->created_at->format('M d, Y')); ?></div>
                                    <div class="small text-muted" style="font-size: 0.7rem;"><i class="fas fa-phone-alt me-1" style="font-size: 0.65rem;"></i><?php echo e($task->customer->phone ?? 'N/A'); ?></div>
                                    
                                    <div class="mt-2 d-flex flex-column gap-1">
                                        <?php if($customerTasks->count() > 1): ?>
                                            <div class="badge bg-secondary text-white small" style="font-size: 0.65rem; width: fit-content;"><?php echo e($customerTasks->count()); ?> Tasks Today</div>
                                            <a href="javascript:void(0)" onclick="printDirect('<?php echo e(route('admin.design-tasks.print-filtered', ['customer_id' => $task->customer_id, 'date' => $task->created_at->format('Y-m-d')])); ?>')" class="badge bg-dark text-white text-decoration-none small" style="font-size: 0.65rem; width: fit-content;" title="Print Today's Tasks for this Customer">
                                                <i class="fas fa-print me-1"></i>Print Group
                                            </a>
                                        <?php endif; ?>
                                        <a href="javascript:void(0)" onclick="printDirect('<?php echo e(route('admin.design-tasks.print-customer-invoice', $task->customer_id)); ?>')" class="text-info text-decoration-none x-small mt-1" style="font-size: 0.6rem;" title="Print All-Time Customer Statement">
                                            <i class="fas fa-history me-1"></i>Full Statement
                                        </a>
                                    </div>
                                </td>
                                <?php endif; ?>
                                <td class="py-2 py-md-3 px-2 px-md-4">
                                    <div class="fw-bold text-dark" style="font-size: 0.9rem;"><?php echo e($task->title); ?></div>
                                    <div class="x-small text-muted"><?php echo e($task->task_code); ?></div>
                                </td>
                                <td class="py-2 py-md-3 px-2 px-md-4 text-start">
                                    <span class="badge rounded-pill" style="background-color: <?php echo e([
                                        'pending' => '#f59e0b',
                                        'in_progress' => '#4f46e5',
                                        'in_review' => '#8b5cf6',
                                        'completed' => '#10b981',
                                        'confirmed' => '#0ea5e9',
                                        'super_completed' => '#059669',
                                        'rejected' => '#ef4444',
                                        'cancelled' => '#1e293b'
                                    ][$task->status] ?? '#64748b'); ?>; color: #fff; font-size: 11px; padding: 0.5rem 1rem;">
                                        <?php echo e($task->status_label); ?>

                                    </span>
                                </td>
                                <td class="py-2 py-md-3 px-2 px-md-4 fw-bold text-success text-start">
                                    <?php echo e(number_format($task->amount_paid, 0)); ?>

                                </td>
                                <td class="py-2 py-md-3 px-2 px-md-4 fw-bold <?php echo e($task->balance > 0 ? 'text-danger' : 'text-success'); ?> text-start">
                                    <?php echo e(number_format($task->balance, 0)); ?>

                                </td>
                                <td class="py-2 py-md-3 px-2 px-md-4 d-none d-md-table-cell text-start" style="font-size: 0.8rem;">
                                    <?php if($task->deadline): ?>
                                        <div class="<?php echo e($task->deadline->isPast() && $task->status !== 'completed' ? 'text-danger fw-bold' : ''); ?>">
                                            <i class="far fa-calendar-alt me-1"></i><?php echo e($task->deadline->format('M d, Y')); ?>

                                        </div>
                                        <?php if($task->deadline->isPast() && $task->status !== 'completed'): ?>
                                            <div class="text-danger small fw-bold" style="font-size: 0.65rem;">OVERDUE</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">No deadline</span>
                                    <?php endif; ?>
                                </td>
                                <?php if($user->role !== 'designer'): ?>
                                <td class="py-2 py-md-3 px-2 px-md-4 d-none d-lg-table-cell text-start" style="font-size: 0.8rem;">
                                    <?php echo e($task->designer->name ?? 'Unassigned'); ?>

                                </td>
                                <td class="py-2 py-md-3 px-2 px-md-4 d-none d-lg-table-cell text-start" style="font-size: 0.8rem;">
                                    <?php echo e($task->operator->name ?? 'Unassigned'); ?>

                                </td>
                                <?php endif; ?>
                                <?php if($user->role !== 'receptionist'): ?>
                                <td class="py-2 py-md-3 px-2 px-md-4 d-none d-lg-table-cell text-start" style="font-size: 0.8rem;">
                                    <?php echo e($task->receptionist->name ?? 'N/A'); ?>

                                </td>
                                <?php endif; ?>
                                <td class="py-2 py-md-3 px-2 px-md-4 d-none d-sm-table-cell text-start">
                                    <small class="badge bg-light text-dark border" style="font-size: 0.65rem;"><?php echo e($task->priority_label); ?></small>
                                </td>
                                <td class="py-2 py-md-3 px-2 px-md-4 text-start">
                                    <div class="d-flex justify-content-start gap-1 flex-wrap">
                                        <!-- Task Management -->
                                        <?php if(in_array($user->role, ['admin', 'super_admin', 'manager', 'receptionist', 'operator', 'accountant']) && $task->delivery_status !== 'delivered'): ?>
                                        <button type="button" onclick="openEditTaskModal(<?php echo e($task->id); ?>)" class="btn-action-pill bg-action-gray" title="Edit Task">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php endif; ?>
                                        <button type="button" onclick="viewInvoice(<?php echo e($task->id); ?>)" class="btn-action-pill bg-action-blue" title="View Task">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <a href="<?php echo e(route('admin.design-tasks.show', $task)); ?>" class="btn-action-pill bg-action-purple" title="Task Management">
                                            <i class="fas fa-list-check"></i>
                                        </a>
                                        
                                        <button type="button" class="btn-action-pill bg-action-gray" title="Print Invoice" onclick="printDirect('<?php echo e(route('admin.design-tasks.print-invoice', $task)); ?>')">
                                            <i class="fas fa-print"></i>
                                        </button>
                                        
                                        <?php if($task->balance > 0 && in_array($user->role, ['accountant', 'admin', 'super_admin'])): ?>
                                            <button type="button" class="btn-action-pill bg-action-green" onclick="openPaymentModal(<?php echo e($task->id); ?>, <?php echo e($task->balance); ?>, '<?php echo e(addslashes($task->title)); ?>')" title="Add Payment">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <!-- Confirm Task (Receptionist) -->
                                        <?php if(in_array($user->role, ['receptionist', 'accountant', 'admin', 'super_admin']) && $task->status === 'completed'): ?>
                                        <button type="button" class="btn-action-pill bg-action-green" title="Confirm Task"
                                                onclick="if(confirm('Confirm this task as completed?')) { document.getElementById('status-form-<?php echo e($task->id); ?>-confirmed').submit(); }">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <form id="status-form-<?php echo e($task->id); ?>-confirmed" action="<?php echo e(route('admin.design-tasks.update-status', $task)); ?>" method="POST" class="d-none" data-no-global-handler>
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="status" value="confirmed">
                                        </form>
                                        <?php endif; ?>
 
                                        <!-- Super Complete (Operator/Admin) -->
                                        <?php if(($user->role === 'operator' || $user->role === 'admin' || $user->role === 'super_admin') && $task->status === 'confirmed'): ?>
                                        <button type="button" class="btn-action-pill bg-action-green" title="Super Complete Task"
                                                onclick="if(confirm('Mark this task as super completed and notify customer?')) { document.getElementById('status-form-<?php echo e($task->id); ?>-super').submit(); }">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                        <form id="status-form-<?php echo e($task->id); ?>-super" action="<?php echo e(route('admin.design-tasks.update-status', $task)); ?>" method="POST" class="d-none" data-no-global-handler>
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="status" value="super_completed">
                                        </form>
                                        <?php endif; ?>
 
                                        <!-- Send for Delivery / Assign Delivery person -->
                                        <?php if($task->status === 'super_completed' && in_array($user->role, ['receptionist', 'operator', 'admin', 'super_admin', 'accountant'])): ?>
                                            <?php if($task->delivery_status !== 'delivered' && !$task->delivery_id): ?>
                                                <button type="button" class="btn-action-pill bg-action-yellow" title="Assign Delivery" onclick="openDeliveryModal(<?php echo e($task->id); ?>, '<?php echo e($task->customer->name ?? 'N/A'); ?>', <?php echo e($task->saler_id ?? 'null'); ?>, '<?php echo e($task->saler->name ?? ''); ?>')">
                                                    <i class="fas fa-truck"></i>
                                                </button>
                                            <?php elseif($task->delivery_id && $task->delivery_status !== 'delivered'): ?>
                                                <button type="button" class="btn-action-pill bg-action-gray" style="opacity: 0.5; cursor: not-allowed;" title="Already Assigned" onclick="alert('Delivery already assigned up to <?php echo e($task->delivery->name ?? 'a person'); ?>'); return false;">
                                                    <i class="fas fa-truck"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
 
                                        <!-- Send Template Messages (Restricted for Salers) -->
                                        <?php if($templates->count() > 0 && ($task->customer && $task->customer->phone) && $user->role !== 'saler'): ?>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn-action-pill bg-action-blue dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Send Message">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border" style="font-size: 0.8rem; min-width: 180px;">
                                                    <li class="dropdown-header fw-bold text-uppercase" style="font-size: 0.7rem;">Send Template</li>
                                                    <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <li>
                                                            <form action="<?php echo e(route('admin.message-templates.send')); ?>" method="POST" data-no-global-handler>
                                                                <?php echo csrf_field(); ?>
                                                                <input type="hidden" name="template_id" value="<?php echo e($template->id); ?>">
                                                                <input type="hidden" name="customer_id" value="<?php echo e($task->customer_id); ?>">
                                                                <button type="submit" class="dropdown-item py-2" data-no-global-handler>
                                                                    <i class="fas fa-comment-alt me-2 text-info opacity-50"></i><?php echo e($template->title); ?>

                                                                </button>
                                                            </form>
                                                        </li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="<?php echo e($user->role === 'designer' ? 7 : (in_array($user->role, ['receptionist', 'accountant']) ? 7 : 8)); ?>" class="text-center py-5">
                                    <i class="fas fa-tasks text-muted" style="font-size: 3rem;"></i>
                                    <p class="mt-3 text-muted">No design tasks found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div> <!-- Close table-responsive -->
        </div> <!-- Close desktop-table -->
        </div> <!-- Close card-body -->
        
        <?php if($tasks->hasPages()): ?>
        <div class="card-footer bg-white border-top py-3 pagination-wrapper">
            <?php echo e($tasks->links()); ?>

        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="paymentForm" method="POST" data-no-global-handler>
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="modal-header bg-white border-bottom">
                    <h6 class="modal-title text-dark fw-bold mb-0" style="font-size: 14px;"><i class="fas fa-money-bill-wave me-2"></i>Payment</h6>
                    <button type="button" class="btn-close" onclick="closePaymentModal()" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border py-2 mb-3">
                        <small style="font-size: 12px;"><i class="fas fa-info-circle me-1 text-muted"></i>Task: <strong id="paymentTaskTitle"></strong></small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size: 12px;">Balance</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white" style="font-size: 12px;"><i class="fas fa-wallet"></i></span>
                            <input type="text" class="form-control" id="displayBalance" readonly style="font-size: 12px;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label fw-bold text-dark" style="font-size: 12px;">Amount <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white" style="font-size: 12px;"><i class="fas fa-money-bill"></i></span>
                            <input type="number" class="form-control" id="paymentAmount" name="amount" min="1" step="1" required style="font-size: 12px;">
                        </div>
                        <div class="form-text" style="font-size: 11px;">Max: <span id="maxPaymentText"></span></div>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label fw-bold text-dark" style="font-size: 12px;">Method <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" id="paymentMethod" name="payment_method" required style="font-size: 12px;">
                            <option value="Cash">Cash</option>
                            <option value="Mobile Money">Mobile Money</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Card">Card</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size: 12px;">Note <span class="text-muted" style="font-size: 11px;">(Optional)</span></label>
                        <textarea class="form-control form-control-sm" name="notes" rows="2" placeholder="Notes..." style="font-size: 12px;"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="additional_cost" class="form-label fw-bold text-dark" style="font-size: 12px;">
                            Additional Cost (TZS) <span class="text-muted" style="font-size: 11px;">(optional)</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white" style="font-size: 12px;"><i class="fas fa-plus-circle"></i></span>
                            <input type="number"
                                   id="additional_cost"
                                   name="additional_cost"
                                   class="form-control"
                                   min="0"
                                   step="0.01"
                                   value="0"
                                   style="font-size: 12px;"
                                   placeholder="0.00">
                        </div>
                        <small class="form-text text-muted" style="font-size: 11px;">If you enter a value &gt; 0, you must provide a reason.</small>
                    </div>

                    <div class="mb-0">
                        <label for="additional_cost_reason" class="form-label fw-bold text-dark" style="font-size: 12px;">
                            Additional Cost Reason
                        </label>
                        <textarea class="form-control form-control-sm"
                                  id="additional_cost_reason"
                                  name="additional_cost_reason"
                                  rows="2"
                                  placeholder="Delivery or other items..."
                                  style="font-size: 12px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="closePaymentModal()" style="font-size: 12px;">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-dark" data-no-global-handler style="font-size: 12px;"><i class="fas fa-check me-1"></i>Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <form id="editTaskForm" method="POST" data-no-global-handler>
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="modal-header bg-white border-bottom py-3">
                    <h6 class="modal-title fw-bold text-dark mb-0" style="font-size: 16px;"><i class="fas fa-edit me-2"></i>Modify Task: <span id="editTaskCodeDisplay"></span></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    <div class="row g-3">
                        <div class="col-12 text-center mb-2">
                             <span class="badge bg-light text-dark border px-3" style="font-size: 12px;" id="editTaskStatusBadge"></span>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Task Title <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="fas fa-heading text-muted"></i></span>
                                <input type="text" name="title" id="edit_title" class="form-control" placeholder="Task title..." required>
                            </div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Priority <span class="text-danger">*</span></label>
                            <select name="priority" id="edit_priority" class="form-select form-select-sm" required>
                                <option value="1">High</option>
                                <option value="3">Medium</option>
                                <option value="5">Low</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Deadline</label>
                            <input type="datetime-local" name="deadline" id="edit_deadline" class="form-control form-control-sm">
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Department <span class="text-danger">*</span></label>
                            <select name="department_id" id="edit_department_id" class="form-select form-select-sm" required>
                                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($dept->id); ?>"><?php echo e($dept->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Saler</label>
                            <select name="saler_id" id="edit_saler_id" class="form-select form-select-sm">
                                <option value="">Not specified</option>
                                <?php $__currentLoopData = $salers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($slr->id); ?>"><?php echo e($slr->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Designer</label>
                            <select name="designer_id" id="edit_designer_id" class="form-select form-select-sm">
                                <option value="">Awaiting assignment</option>
                                <?php $__currentLoopData = $all_designers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dsnr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($dsnr->id); ?>"><?php echo e($dsnr->name); ?> (<?php echo e(ucfirst($dsnr->role)); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Operator</label>
                            <select name="operator_id" id="edit_operator_id" class="form-select form-select-sm">
                                <option value="">Awaiting assignment</option>
                                <?php $__currentLoopData = $operators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($op->id); ?>"><?php echo e($op->name); ?> (<?php echo e(ucfirst($op->role)); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="qty" id="edit_qty" class="form-control form-control-sm" step="0.01" min="0.01" required>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Rate (TZS) <span class="text-danger">*</span></label>
                            <input type="number" name="rate" id="edit_rate" class="form-control form-control-sm" step="0.01" min="0" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Public Description</label>
                            <textarea name="description" id="edit_description" class="form-control form-control-sm" rows="3" placeholder="Task description..."></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-dark x-small text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Internal Instructions</label>
                            <textarea name="designer_instructions" id="edit_designer_instructions" class="form-control form-control-sm" rows="3" placeholder="Instructions for designer..."></textarea>
                        </div>

                        <div class="col-12">
                            <div class="p-3 border-start border-4 border-danger rounded-3" style="background-color: #fff1f2;">
                                <label class="form-label fw-bold text-danger x-small text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.5px;"><i class="fas fa-shield-alt me-1"></i>Reason for Edit <span class="text-danger">*</span></label>
                                <textarea name="edit_reason" class="form-control form-control-sm" id="edit_reason" rows="2" placeholder="Why are you changing this task? (Required for audit)" required></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-dark px-4 shadow-sm" id="updateTaskBtn" data-no-global-handler>
                        <i class="fas fa-save me-1"></i> Update Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Task Detail Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border" style="box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.1);">
            <div class="modal-header bg-white border-bottom">
                <h6 class="modal-title fw-bold text-dark mb-0" style="font-size: 14px;"><i class="fas fa-eye me-2"></i>Task Details</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="invoiceModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-dark" role="status" style="width: 2rem; height: 2rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" id="printInvoiceBtn" class="btn btn-sm btn-dark px-3 d-none" onclick="printDirect(this.getAttribute('data-url'))" style="font-size: 12px;">
                    <i class="fas fa-print me-1"></i>Invoice
                </button>
                <button type="button" id="printStatementBtn" class="btn btn-sm btn-outline-dark px-3 d-none" onclick="printDirect(this.getAttribute('data-url'))" style="font-size: 12px;">
                    <i class="fas fa-file-invoice me-1"></i>Statement
                </button>
                <button type="button" class="btn btn-sm btn-outline-dark px-3" data-bs-dismiss="modal" style="font-size: 12px;">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delivery Modal -->
<div class="modal fade" id="deliveryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deliveryForm" method="POST" data-no-global-handler>
                <?php echo csrf_field(); ?>
                <div class="modal-header bg-white border-bottom">
                    <h6 class="modal-title fw-bold text-dark mb-0" style="font-size: 14px;"><i class="fas fa-truck me-2"></i>Delivery</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border py-2 mb-3">
                        <small style="font-size: 12px;"><i class="fas fa-user-circle me-1 text-muted"></i>Customer: <strong id="deliveryCustomerName"></strong></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size: 12px;">Method <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3 mt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delivery_method" id="methodPickup" value="pickup" checked onchange="toggleDeliverySelection()">
                                <label class="form-check-label" for="methodPickup" style="font-size: 12px;">Pickup</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delivery_method" id="methodDelivery" value="delivery" onchange="toggleDeliverySelection()">
                                <label class="form-check-label" for="methodDelivery" style="font-size: 12px;">Delivery</label>
                            </div>
                        </div>
                    </div>

                    <div id="deliveryPersonSelection" class="mb-3 d-none">
                        <label for="delivery_id" class="form-label fw-bold text-dark" style="font-size: 12px;">Person <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" name="delivery_id" id="delivery_id" style="font-size: 12px;">
                            <option value="">Select...</option>
                            <?php $__currentLoopData = $all_delivery; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deliveryMember): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($deliveryMember->id); ?>"><?php echo e($deliveryMember->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    
                    <div class="mb-0">
                        <label for="delivery_notes" class="form-label fw-bold text-dark" style="font-size: 12px;">Notes <span class="text-muted" style="font-size: 11px;">(Optional)</span></label>
                        <textarea class="form-control form-control-sm" name="delivery_notes" id="delivery_notes" rows="2" placeholder="Instructions..." style="font-size: 12px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-sm btn-outline-dark" data-bs-dismiss="modal" style="font-size: 12px;">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-dark" data-no-global-handler style="font-size: 12px;"><i class="fas fa-check me-1"></i>Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if(session('success')): ?>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <?php echo e(session('success')); ?>

            </div>
        </div>
    </div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Mobile View Persistence
function switchView(view) {
    const table = document.getElementById('desktopTableWrapper');
    const cards = document.getElementById('mobileCardsWrapper');
    const btnTable = document.getElementById('btnViewTable');
    const btnCards = document.getElementById('btnViewCards');

    if (!table || !cards) return;

    if (view === 'table') {
        table.classList.remove('view-hidden');
        cards.classList.add('view-hidden');
        btnTable && btnTable.classList.remove('btn-outline-dark');
        btnTable && btnTable.classList.add('btn-dark');
        btnCards && btnCards.classList.remove('btn-dark');
        btnCards && btnCards.classList.add('btn-outline-dark');
    } else {
        table.classList.add('view-hidden');
        cards.classList.remove('view-hidden');
        btnTable && btnTable.classList.remove('btn-dark');
        btnTable && btnTable.classList.add('btn-outline-dark');
        btnCards && btnCards.classList.remove('btn-outline-dark');
        btnCards && btnCards.classList.add('btn-dark');
    }
    localStorage.setItem('designTasksPreferredView', view);
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.innerWidth < 768) {
        const savedView = localStorage.getItem('designTasksPreferredView') || 'cards';
        switchView(savedView);
    }
});
</script>

<script>
function openPaymentModal(taskId, balance, taskTitle) {
    const modalEl = document.getElementById('paymentModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
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
    
    modal.show();
}

function openEditTaskModal(id) {
    const modalEl = document.getElementById('editTaskModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    const form = document.getElementById('editTaskForm');
    
    // Reset form
    form.reset();
    form.action = `/admin/design-tasks/${id}`;
    
    // Show loading state or just show modal and then fill
    modal.show();
    
    // Fetch data
    fetch(`/admin/design-tasks/task-data/${id}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            document.getElementById('editTaskCodeDisplay').textContent = data.task_code;
            document.getElementById('edit_title').value = data.title;
            document.getElementById('edit_priority').value = data.priority;
            document.getElementById('edit_deadline').value = data.deadline;
            document.getElementById('edit_department_id').value = data.department_id;
            document.getElementById('edit_saler_id').value = data.saler_id || '';
            document.getElementById('edit_designer_id').value = data.designer_id || '';
            document.getElementById('edit_operator_id').value = data.operator_id || '';
            document.getElementById('edit_qty').value = data.qty;
            document.getElementById('edit_rate').value = data.rate;
            document.getElementById('edit_description').value = data.description || '';
            document.getElementById('edit_designer_instructions').value = data.designer_instructions || '';
            
            const statusBadge = document.getElementById('editTaskStatusBadge');
            statusBadge.textContent = `Current Status: ${data.status_label}`;
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load task data.'
            });
            modal.hide();
        });
}

// Handle Edit Form Submission Loading State
document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.getElementById('editTaskForm');
    if (editForm) {
        editForm.addEventListener('submit', function() {
            const btn = document.getElementById('updateTaskBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Updating...';
            }
        });
    }
});

function closePaymentModal() {
    const modalEl = document.getElementById('paymentModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.hide();
}

// Initialize tooltips for batch payment indicators
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Note: Receipt printing now happens immediately on the create page before redirect
// Removed sessionStorage auto-print to avoid duplicate printing

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}

// Delivery Modal Logic
function openDeliveryModal(taskId, customerName, salerId, salerName) {
    const modalEl = document.getElementById('deliveryModal');
    if (!modalEl) return;
    
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    const form = document.getElementById('deliveryForm');
    const customerNameEl = document.getElementById('deliveryCustomerName');
    
    if (form) form.action = `/admin/design-tasks/${taskId}/assign-delivery`;
    if (customerNameEl) customerNameEl.textContent = customerName;
    
    // Reset form fields if they exist
    const methodPickup = document.getElementById('methodPickup');
    const deliveryId = document.getElementById('delivery_id');
    const deliveryNotes = document.getElementById('delivery_notes');
    
    if (methodPickup) methodPickup.checked = true;
    toggleDeliverySelection();
    if (deliveryNotes) deliveryNotes.value = '';
    
    // Handle the specific saler for this task
    // Remove any previously added saler option (they have a specific class)
    const existingSalerOpt = deliveryId.querySelector('.task-specific-saler');
    if (existingSalerOpt) existingSalerOpt.remove();
    
    // If salerId is provided and not already in the list
    if (salerId) {
        let exists = false;
        for (let i = 0; i < deliveryId.options.length; i++) {
            if (deliveryId.options[i].value == salerId) {
                exists = true;
                break;
            }
        }
        
        if (!exists) {
            const opt = document.createElement('option');
            opt.value = salerId;
            opt.textContent = `${salerName} (Assigned Saler)`;
            opt.className = 'task-specific-saler text-primary fw-bold';
            deliveryId.appendChild(opt);
        }
    }
    
    modal.show();
}

function closeDeliveryModal() {
    const modalEl = document.getElementById('deliveryModal');
    if (!modalEl) return;
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.hide();
}

function toggleDeliverySelection() {
    const methodDelivery = document.getElementById('methodDelivery');
    if (!methodDelivery) return;
    
    const isDelivery = methodDelivery.checked;
    const selectionDiv = document.getElementById('deliveryPersonSelection');
    const deliverySelect = document.getElementById('delivery_id');
    
    if (selectionDiv && deliverySelect) {
        if (isDelivery) {
            selectionDiv.classList.remove('d-none');
            deliverySelect.required = true;
        } else {
            selectionDiv.classList.add('d-none');
            deliverySelect.required = false;
            deliverySelect.value = '';
        }
    }
}

function viewInvoice(id) {
    const modalEl = document.getElementById('invoiceModal');
    const modalBody = document.getElementById('invoiceModalBody');
    const modal = new bootstrap.Modal(modalEl);
    
    const printBtn = document.getElementById('printInvoiceBtn');
    const statementBtn = document.getElementById('printStatementBtn');
    
    if (printBtn) {
        printBtn.classList.add('d-none');
        printBtn.setAttribute('data-url', `/admin/design-tasks/${id}/print-invoice`);
    }
    
    if (statementBtn) {
        statementBtn.classList.add('d-none');
    }

    modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    modal.show();
    
    fetch(`/admin/design-tasks/invoice-data/${id}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (printBtn) printBtn.classList.remove('d-none');
            if (statementBtn && data.customer.id) {
                statementBtn.setAttribute('data-url', `/admin/design-tasks/customer/${data.customer.id}/print-invoice`);
                statementBtn.classList.remove('d-none');
            }
            const statusColors = {
                'pending': '#ffc107',
                'in_progress': '#0d6efd',
                'in_review': '#6f42c1',
                'completed': '#198754',
                'confirmed': '#0dcaf0',
                'super_completed': '#20c997',
                'rejected': '#dc3545'
            };
            
            const statusColor = statusColors[data.status] || '#6c757d';
            const balanceColor = data.balance > 0 ? 'text-danger' : 'text-success';
            
            modalBody.innerHTML = `
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-muted small mb-2 text-uppercase letter-spacing-1">Customer Information</h6>
                        <h5 class="mb-1 fw-bold">${data.customer.name}</h5>
                        <p class="mb-1 text-muted"><i class="fas fa-phone-alt me-2"></i>${data.customer.phone}</p>
                        <p class="mb-0 text-muted"><i class="fas fa-map-marker-alt me-2"></i>${data.customer.address}</p>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <h6 class="fw-bold text-muted small mb-2 text-uppercase letter-spacing-1">Task Status</h6>
                        <span class="badge px-3 py-2 mb-2" style="background-color: ${statusColor}; color: white; border-radius: 30px;">
                            ${data.status_label}
                        </span>
                        <p class="mb-0 text-muted small">Created: ${data.created_at}</p>
                        <p class="mb-0 text-muted small">Deadline: ${data.deadline}</p>
                    </div>
                </div>
                
                <div class="card bg-light border-0 rounded-3 mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2">Task: ${data.title}</h6>
                        <p class="text-muted small mb-0">${data.description || 'No description provided'}</p>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-borderless bg-white rounded shadow-sm">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3 py-3 text-muted small text-uppercase fw-bold">Financial Summary</th>
                                <th class="pe-3 py-3 text-end text-muted small text-uppercase fw-bold">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-3 py-2">Base Design Price</td>
                                <td class="pe-3 py-2 text-end fw-bold">${formatCurrency(data.price)}</td>
                            </tr>
                            ${data.requires_receipt ? `
                            <tr>
                                <td class="ps-3 py-2">VAT (18%)</td>
                                <td class="pe-3 py-2 text-end text-muted">${formatCurrency(data.price * 0.18)}</td>
                            </tr>
                            <tr class="border-top">
                                <td class="ps-3 py-2 fw-bold">Total with VAT</td>
                                <td class="pe-3 py-2 text-end fw-bold text-primary fs-5">${formatCurrency(data.price * 1.18)}</td>
                            </tr>
                            ` : `
                            <tr class="border-top">
                                <td class="ps-3 py-2 fw-bold text-uppercase small">Total Amount</td>
                                <td class="pe-3 py-2 text-end fw-bold text-primary fs-5">${formatCurrency(data.price)}</td>
                            </tr>
                            `}
                            <tr>
                                <td class="ps-3 py-2">Amount Paid</td>
                                <td class="pe-3 py-2 text-end text-success fw-bold">${formatCurrency(data.amount_paid)}</td>
                            </tr>
                            <tr class="border-top bg-light">
                                <td class="ps-3 py-3 fw-bold">Current Balance</td>
                                <td class="pe-3 py-3 text-end fw-bold ${balanceColor} fs-4">${formatCurrency(data.balance)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="row mt-2">
                    <div class="col-md-6">
                        <p class="mb-0 small text-muted"><strong>Designer:</strong> ${data.designer}</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-0 small text-muted"><strong>Receptionist:</strong> ${data.receptionist}</p>
                    </div>
                </div>
            `;
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Failed to load invoice details. Please try again or contact support.',
                confirmButtonColor: '#dc3545'
            });
            // Still show a simple error in the modal if it's already open
            modalBody.innerHTML = `
                <div class="alert alert-danger d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div>Failed to load invoice details.</div>
                </div>
            `;
        });
}

// Generate receipt HTML (same as in create.blade.php)
function generateReceiptHTML(customerData, tasks, subtotal, vat, total, totalPaid = 0, totalBalance = 0, includesVat = true) {
    const now = new Date();
    const receiptNumber = 'INV-' + String(Date.now()).slice(-5);
    const date = now.toLocaleDateString('en-GB', { 
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
    const time = now.toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });
    
    // Company information
    const companyInfo = {
        name: 'CHIBO BRANDS CO.LTD',
        location: 'Dar es Salaam, Tanzania',
        phone: '+255 655 392 319',
        phone2: '+255 711 711 111',
        tin: '154 747 214',
        website: 'www.chibobrand.com'
    };
    
    // Current user info
    const currentUser = {
        name: '<?php echo e(auth()->user()->name ?? "Admin"); ?>'
    };
    
    // Generate QR code data
    const qrData = JSON.stringify({
        receipt: receiptNumber,
        date: date,
        total: total,
        customer: customerData?.name || 'Walk-in Customer',
        items: tasks.length
    });
    
    return `
<!DOCTYPE html>
<html>
<head>
    <title>Receipt - ${receiptNumber}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier Prime', 'Courier New', monospace;
            max-width: 80mm;
            margin: 0 auto;
            padding: 10px;
            background: white;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .receipt {
            width: 100%;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 2px dashed #000;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }
        
        .company-info {
            font-size: 10px;
            margin: 3px 0;
        }
        
        .company-phone {
            font-size: 10px;
            margin: 3px 0;
        }
        
        .tin {
            font-size: 10px;
            margin: 3px 0;
            font-weight: bold;
        }
        
        .separator-dashed {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        
        .separator-solid {
            border-top: 1px solid #000;
            margin: 8px 0;
        }
        
        .receipt-info {
            margin: 8px 0;
            font-size: 11px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 11px;
        }
        
        .info-label {
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .items-header {
            margin-top: 10px;
            margin-bottom: 5px;
            padding: 5px 0;
            border-bottom: 1px solid #000;
            border-top: 1px solid #000;
        }
        
        .items-header-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.2fr;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            padding: 3px 0;
        }
        
        .item-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.2fr;
            padding: 4px 0;
            font-size: 11px;
            border-bottom: 1px dotted #ccc;
        }
        
        .item-row:last-child {
            border-bottom: none;
        }
        
        .item-name {
            font-weight: bold;
        }
        
        .item-name small {
            display: block;
            margin-top: 2px;
        }
        
        .item-qty {
            text-align: center;
        }
        
        .item-price {
            text-align: right;
        }
        
        .item-total {
            text-align: right;
            font-weight: bold;
        }
        
        .totals-section {
            margin-top: 10px;
            padding-top: 8px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 4px 0;
            font-size: 12px;
        }
        
        .total-label {
            font-weight: bold;
        }
        
        .total-value {
            font-weight: bold;
            text-align: right;
        }
        
        .grand-total {
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 2px solid #000;
        }
        
        .payment-section {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #000;
        }
        
        .payment-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 11px;
        }
        
        .qr-section {
            text-align: center;
            margin: 15px 0;
            padding: 10px 0;
        }
        
        .qr-code {
            width: 120px;
            height: 120px;
            margin: 10px auto;
            border: 2px solid #000;
            padding: 5px;
            background: white;
        }
        
        .qr-code img {
            width: 100%;
            height: 100%;
            display: block;
        }
        
        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-size: 10px;
            line-height: 1.5;
        }
        
        .footer-message {
            margin: 5px 0;
            font-style: italic;
        }
        
        .website {
            margin-top: 5px;
            font-weight: bold;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 5mm;
            }
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="company-name">${companyInfo.name}</div>
            <div class="company-info">KINONDONI, MWIJUMA ROAD</div>
            <div class="company-info">DAR ES SALAAM, TANZANIA</div>
            <div class="company-sub" style="font-size: 10px;">TEL: ${companyInfo.phone} | ${companyInfo.phone2}</div>
            <div class="company-sub" style="font-size: 10px; font-weight: bold;">TIN: ${companyInfo.tin}</div>
        </div>
        
        <div class="separator-dashed"></div>
        
        <div class="receipt-info">
            <div class="info-row">
                <span class="info-label">RECEIPT #:</span>
                <span>${receiptNumber}</span>
            </div>
            <div class="info-row">
                <span class="info-label">DATE:</span>
                <span>${date} ${time}</span>
            </div>
            <div class="info-row">
                <span class="info-label">CUSTOMER:</span>
                <span>${customerData && customerData.name ? customerData.name : 'Walk-in Customer'}</span>
            </div>
            ${customerData && customerData.phone ? `
            <div class="info-row">
                <span class="info-label">PHONE:</span>
                <span>${customerData.phone}</span>
            </div>
            ` : ''}
            <div class="info-row">
                <span class="info-label">ISSUED BY:</span>
                <span>${currentUser.name}</span>
            </div>
        </div>
        
        <div class="separator-solid"></div>
        
        <div class="items-header">
            <div class="items-header-row">
                <span>TASK</span>
                <span class="item-qty">QTY</span>
                <span class="item-price">PRICE</span>
                <span class="item-total">TOTAL</span>
            </div>
        </div>
        
        ${tasks.map((task, index) => `
        <div class="item-row">
            <div class="item-name">
                ${task.title}
                ${task.designer ? `<br><small style="font-size: 9px; color: #666;">Designer: ${task.designer}${task.designerPhone ? ' (WhatsApp: ' + task.designerPhone + ')' : ''}</small>` : ''}
            </div>
            <span class="item-qty">1</span>
            <span class="item-price">${parseFloat(task.price).toFixed(2)}</span>
            <span class="item-total">${parseFloat(task.price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
        </div>
        `).join('')}
        
        <div class="separator-solid"></div>
        <div class="separator-dashed"></div>
        
        <div class="totals-section">
            <div class="total-row">
                <span class="total-label">SUBTOTAL:</span>
                <span class="total-value">${subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
            </div>
            ${vat > 0 ? `
            <div class="total-row">
                <span class="total-label">VAT (18%):</span>
                <span class="total-value">${vat.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
            </div>
            ` : ''}
            <div class="total-row total-header">
                <span class="total-label">Grand Total:</span>
                <span class="total-value">${total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
            </div>
            <div class="total-row">
                <span class="total-label">Amount Paid:</span>
                <span class="total-value">${totalPaid.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
            </div>
            ${totalBalance > 0 ? `
            <div class="total-row balance-row">
                <span class="total-label">Balance Due:</span>
                <span class="total-value">${totalBalance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
            </div>
            ` : ''}
        </div>
        
        <div class="separator-solid"></div>
        
        <div class="payment-section">
            <div class="payment-row">
                <span class="info-label">PAYMENT METHOD:</span>
                <span>CASH</span>
            </div>
            <div class="payment-row">
                <span class="info-label">CHECKED BY:</span>
                <span>${currentUser.name}</span>
            </div>
        </div>
        
        <div class="qr-section">
            <div class="qr-code">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=${encodeURIComponent(qrData)}" alt="QR Code" />
            </div>
        </div>
        
        <div class="footer">
            <div class="footer-message">THANK YOU FOR SHOPPING WITH US!</div>
            <div class="footer-message" style="font-weight: bold; margin-top: 5px; font-size: 11px;">“CREATIVITY MEETS TECHNOLOGY”</div>
            <div class="website">${companyInfo.website}</div>
            <div style="margin-top: 10px; text-align: center; border-top: 1px dotted #ccc; padding-top: 5px;">
                <div style="font-size: 8px; color: #444;">Developed by Fridoltech</div>
                <div style="font-size: 8px; color: #0066cc; font-weight: bold;">www.fridoltech.org</div>
            </div>
        </div>
    </div>
</body>
</html>
    `;
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<?php if(session('auto_print_instructions') && session('print_tasks_data')): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tasksData = <?php echo json_encode(session('print_tasks_data'), 15, 512) ?>;
    
    // Generate and print instructions
    const printWindow = window.open('', '_blank');
    const printContent = generateInstructionsHTMLForPrint(tasksData);
    printWindow.document.write(printContent);
    printWindow.document.close();
    
    // Wait for content to load, then print
    setTimeout(() => {
        printWindow.print();
        // Keep window open for a moment in case user wants to reprint
        setTimeout(() => {
            // Optionally close after 5 seconds if not printing
            // printWindow.close();
        }, 5000);
    // NEW: Automatic print after successful save
    <?php if(session('print_receipt_data')): ?>
        const sessionReceiptData = <?php echo json_encode(session('print_receipt_data'), 15, 512) ?>;
        setTimeout(() => {
            printReceiptAutomatically(sessionReceiptData);
        }, 500);
    <?php endif; ?>
});

// Function to automatically print receipt in popup
function printReceiptAutomatically(receiptData) {
    try {
        const receiptWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes');
        
        if (!receiptWindow) {
            alert('Please allow popups to print the receipt automatically.');
            return;
        }
        
        const receiptContent = generateReceiptHTML(
            receiptData.customerData, 
            receiptData.tasks, 
            receiptData.subtotal, 
            receiptData.vat, 
            receiptData.total,
            receiptData.totalPaid,
            receiptData.totalBalance,
            receiptData.requires_receipt,
            receiptData.paymentMethod || 'CASH'
        );
        
        receiptWindow.document.write(receiptContent);
        receiptWindow.document.close();
        
        let hasPrinted = false;
        // Define a single print function to ensure it's only called once
        const triggerPrint = () => {
            if (hasPrinted || !receiptWindow || receiptWindow.closed) return;
            hasPrinted = true;
            receiptWindow.focus();
            receiptWindow.print();
        };

        // Wait for content, then print
        receiptWindow.onload = triggerPrint;
        
        // Fallback for cases where onload might not fire as expected
        setTimeout(triggerPrint, 1000);
    } catch (error) {
        console.error('Error printing receipt:', error);
        alert('Error opening print dialog. Please try again.');
    }
}

function generateReceiptHTML(customerData, tasks, subtotal, vat, total, totalPaid = 0, totalBalance = 0, includesVat = true, paymentMethod = 'CASH') {
    const now = new Date();
    const receiptNumber = 'INV-' + String(Date.now()).slice(-5);
    const date = now.toLocaleDateString('en-GB', { 
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
    const time = now.toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });
    
    // Generate QR code data
    const qrData = receiptNumber;

    const companyInfo = {
        name: 'CHIBO BRAND',
        phone: '0712 345 678',
        phone2: '0754 123 456',
        tin: '123-456-789',
        website: 'www.chibobrand.com'
    };

    const currentUser = {
        name: "<?php echo e(auth()->user()->name); ?>",
        phone: "<?php echo e(auth()->user()->phone ?? ''); ?>"
    };
    
    return `
<!DOCTYPE html>
<html>
<head>
    <title>Receipt - ${receiptNumber}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier Prime', 'Courier New', monospace;
            max-width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            background: white;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .receipt {
            width: 100%;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 2px dashed #000;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }
        
        .company-info {
            font-size: 10px;
            margin: 3px 0;
        }
        
        .company-phone {
            font-size: 10px;
            margin: 3px 0;
        }
        
        .tin {
            font-size: 10px;
            margin: 3px 0;
            font-weight: bold;
        }
        
        .separator-dashed {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        
        .receipt-info {
            margin: 8px 0;
            font-size: 11px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 11px;
        }
        
        .info-label {
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .items-header {
            margin-top: 10px;
            margin-bottom: 5px;
            padding: 5px 0;
            border-bottom: 1px solid #000;
        }
        
        .items-header-row {
            display: grid;
            grid-template-columns: 2fr 0.6fr 1.2fr 1.4fr;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            padding: 3px 0;
        }
        
        .item-row {
            display: grid;
            grid-template-columns: 2fr 0.6fr 1.2fr 1.4fr;
            padding: 4px 0;
            font-size: 11px;
            border-bottom: 1px dotted #ccc;
        }
        
        .totals-section {
            margin-top: 10px;
            padding-top: 8px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 4px 0;
            font-size: 12px;
        }
        
        .total-label {
            font-weight: bold;
        }
        
        .grand-total {
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        
        .payment-section {
            margin-top: 10px;
            padding-top: 8px;
        }
        
        .payment-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 11px;
        }
        
        .qr-section {
            text-align: center;
            margin: 15px 0;
            padding: 10px 0;
        }
        
        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-size: 10px;
        }
        
        @media print {
            body { margin: 0; padding: 5mm; }
            @page { size: 80mm auto; margin: 0; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="company-name">${companyInfo.name}</div>
            <div class="company-info">KINONDONI, MWIJUMA ROAD</div>
            <div class="company-info">DAR ES SALAAM, TANZANIA</div>
            <div class="company-phone">TEL: ${companyInfo.phone}</div>
            <div class="tin">TIN: ${companyInfo.tin}</div>
        </div>
        
        <div class="receipt-info">
            <div class="info-row">
                <span class="info-label">RECEIPT #:</span>
                <span>${receiptNumber}</span>
            </div>
            <div class="info-row">
                <span class="info-label">DATE:</span>
                <span>${date} ${time}</span>
            </div>
            <div class="info-row">
                <span class="info-label">CUSTOMER:</span>
                <span>${customerData && customerData.name ? customerData.name : 'Walk-in Customer'}</span>
            </div>
        </div>
        
        <div class="separator-dashed"></div>
        
        <div class="items-header">
            <div class="items-header-row">
                <span>ITEM</span>
                <span style="text-align: center;">QTY</span>
                <span style="text-align: right;">RATE</span>
                <span style="text-align: right;">TOTAL</span>
            </div>
        </div>
        
        ${tasks.map(task => `
        <div class="item-row">
            <div>${task.title}</div>
            <div style="text-align: center;">${parseFloat(task.qty).toFixed(1)}</div>
            <div style="text-align: right;">${parseFloat(task.rate).toFixed(2)}</div>
            <div style="text-align: right;">${parseFloat(task.price).toLocaleString('en-US', {minimumFractionDigits: 2})}</div>
        </div>
        `).join('')}
        
        <div class="separator-dashed"></div>
        
        <div class="totals-section">
            <div class="total-row">
                <span class="total-label">SUBTOTAL:</span>
                <span>${subtotal.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
            </div>
            ${vat > 0 ? `
            <div class="total-row">
                <span class="total-label">VAT (18%):</span>
                <span>${vat.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
            </div>
            ` : ''}
            <div class="total-row grand-total">
                <span class="total-label">TOTAL:</span>
                <span>${total.toLocaleString('en-US', {minimumFractionDigits: 2})} TZS</span>
            </div>
            <div class="total-row">
                <span class="total-label">PAID:</span>
                <span>${totalPaid.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
            </div>
        </div>
        
        <div class="payment-section">
            <div class="payment-row">
                <span class="info-label">PAYMENT:</span>
                <span>${paymentMethod.toUpperCase()}</span>
            </div>
        </div>
        
        <div class="qr-section">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${encodeURIComponent(qrData)}" width="100" height="100" />
        </div>
        
        <div class="footer">
            <div>THANK YOU FOR SHOPPING WITH US!</div>
            <div class="website">${companyInfo.website}</div>
        </div>
    </div>
</body>
</html>`;
}

function generateInstructionsHTMLForPrint(tasksData) {
    const now = new Date();
    const date = now.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric'
    }) + ' at ' + now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });
    
    // Get customer data from first task (assuming all tasks are for same customer)
    const customerData = tasksData[0]?.customer || null;
    
    return `
<!DOCTYPE html>
<html>
<head>
    <title>Design Task Instructions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #dc3545;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 5px;
        }
        .document-title {
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
        }
        .info-section {
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .info-row {
            padding: 5px 0;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        .task-section {
            margin: 30px 0;
            page-break-inside: avoid;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            background: white;
        }
        .task-header {
            background: #dc3545;
            color: white;
            padding: 10px 15px;
            margin: -20px -20px 15px -20px;
            border-radius: 5px 5px 0 0;
            font-weight: bold;
            font-size: 18px;
        }
        .instructions-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
            white-space: pre-wrap;
            font-size: 14px;
            line-height: 1.6;
        }
        .priority-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            margin-left: 10px;
        }
        .priority-high { background: #dc3545; color: white; }
        .priority-medium { background: #ffc107; color: #000; }
        .priority-low { background: #198754; color: white; }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        @media print {
            body { margin: 0; padding: 15px; }
            .task-section { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">CHIBO BRAND</div>
        <div>Design Task Instructions</div>
    </div>
    
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Date:</span>
            <span>${date}</span>
        </div>
        ${customerData && customerData.name ? `
        <div class="info-row">
            <span class="info-label">Customer:</span>
            <span>${customerData.name}</span>
        </div>
        ${customerData.email ? `
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span>${customerData.email}</span>
        </div>
        ` : ''}
        ${customerData.phone ? `
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span>${customerData.phone}</span>
        </div>
        ` : ''}
        ` : ''}
    </div>
    
    ${tasksData.map((task, index) => `
    <div class="task-section">
        <div class="task-header">
            Task #${index + 1}: ${task.title || 'Untitled Task'}
            <span class="priority-badge priority-${(task.priority || 'Medium').toLowerCase()}">${task.priority || 'Medium'} Priority</span>
        </div>
        
        ${task.designer ? `
        <div class="info-row">
            <span class="info-label">Assigned to:</span>
            <span><strong>${task.designer}</strong></span>
        </div>
        ` : ''}
        
        ${task.deadline ? `
        <div class="info-row">
            <span class="info-label">Deadline:</span>
            <span>${task.deadline}</span>
        </div>
        ` : ''}
        
        ${task.description ? `
        <div style="margin: 15px 0;">
            <strong>Task Description:</strong>
            <p style="margin-top: 5px; white-space: pre-wrap;">${task.description}</p>
        </div>
        ` : ''}
        
        ${task.designer_instructions ? `
        <div style="margin-top: 20px;">
            <strong style="font-size: 16px;">Instructions for Designer/Operator:</strong>
            <div class="instructions-box">${task.designer_instructions}</div>
        </div>
        ` : ''}
    </div>
    `).join('')}
    
    <div class="footer">
        <div>Please follow these instructions carefully.</div>
        <div style="margin-top: 10px;">CHIBO BRAND - Quality Design Services</div>
    </div>
</body>
</html>
    `;
}
</script>
<?php endif; ?>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/gotlaptopparts.com/Downloads/chibo_sales/resources/views/admin/design-tasks/index.blade.php ENDPATH**/ ?>