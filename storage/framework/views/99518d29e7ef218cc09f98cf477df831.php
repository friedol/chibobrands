<?php $__env->startSection('title', 'Design Task Reports'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    /* Mobile Responsive - Font Size Reductions (Matching Task Index) */
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
        
        .badge {
            font-size: 0.65rem !important;
            padding: 0.3rem 0.5rem !important;
        }
        
        .btn {
            font-size: 0.75rem !important;
            padding: 0.375rem 0.625rem !important;
        }
        
        .btn-sm {
            font-size: 0.7rem !important;
            padding: 0.25rem 0.5rem !important;
        }
        
        .table th, .table td {
            font-size: 0.75rem !important;
            padding: 0.375rem 0.5rem !important;
        }
        
        strong {
            font-size: 0.85rem !important;
        }
        
        small {
            font-size: 0.65rem !important;
        }

        /* Analytics Card Side-by-Side on small screens */
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
        
        .text-muted {
            font-size: 0.7rem !important;
        }
        
        .card-header {
            padding: 0.375rem 0.5rem !important;
        }
        
        .card-header h6 {
            font-size: 0.75rem !important;
        }
        
        .card-body {
            padding: 0.75rem !important;
        }
        
        .badge {
            font-size: 0.6rem !important;
            padding: 0.25rem 0.4rem !important;
        }
        
        .btn {
            font-size: 0.7rem !important;
            padding: 0.3rem 0.5rem !important;
        }
        
        .btn-sm {
            font-size: 0.65rem !important;
            padding: 0.2rem 0.4rem !important;
        }
        
        .table th, .table td {
            font-size: 0.7rem !important;
            padding: 0.3rem 0.4rem !important;
        }
        
        strong {
            font-size: 0.8rem !important;
        }
        
        small {
            font-size: 0.6rem !important;
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
    
    .card-metric .h2, .card-metric .h3 {
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

    /* High-Density Print Layout */
    @media print {
        @page { size: A4; margin: 8mm; }
        body { background: white !important; font-size: 10pt !important; margin: 0 !important; padding: 0 !important; color: #000 !important; }
        .btn, .sidebar, .sidebar-nav, .filter-section, .top-navbar, .mobile-menu-toggle, .btn-group, #filterCollapse, .card-header .btn, .breadcrumb, footer, .d-flex.flex-wrap.gap-2 { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; overflow: visible !important; }
        .container-fluid { width: 100% !important; padding: 0 !important; max-width: 100% !important; }
        .card { border: 1px solid #dee2e6 !important; box-shadow: none !important; margin-bottom: 4px !important; break-inside: avoid; }
        .card-body { padding: 0.25rem !important; }
        .card-metric .h2 { font-size: 1rem !important; }
        .icon-circle { width: 20px !important; height: 20px !important; }
        .icon-circle i { font-size: 0.6rem !important; }
        
        /* Table Visibility & Contrast */
        .table { width: 100% !important; border-collapse: collapse !important; font-size: 9.5pt !important; color: #000 !important; }
        .table th, .table td { border: 1px solid #dee2e6 !important; padding: 4px 6px !important; }
        .table thead th { background-color: #eee !important; font-weight: 800 !important; -webkit-print-color-adjust: exact; color-adjust: exact; text-transform: uppercase; }
        
        .stats-col { flex: 0 0 25% !important; max-width: 25% !important; }
        
        /* Force charts to show and fit */
        canvas { max-width: 100% !important; height: auto !important; max-height: 180px !important; }
        .chart-area { height: 180px !important; }
        
        .no-print { display: none !important; }
        .print-only { display: block !important; }
        .report-header { margin-bottom: 10px; border-bottom: 2px solid #dee2e6; padding-bottom: 5px; }
        .report-header h1 { font-size: 18pt !important; margin: 0; }
        
        /* Recurring Footer on Every Page */
        .print-footer {
            position: fixed;
            bottom: 0px;
            left: 0;
            right: 0;
            background: white !important;
            padding: 8px 0;
            border-top: 1px solid #dee2e6 !important;
            text-align: center;
            font-size: 8pt !important;
            color: #000 !important;
        }
        body { padding-bottom: 50px !important; }
        
        /* Layout adjustments for A4 - Horizontal flow */
        .row { display: flex !important; flex-wrap: wrap !important; margin-left: -2px !important; margin-right: -2px !important; }
        .row > [class*="col-"] { padding: 0 2px !important; }
        
        .metrics-row .col-6 { flex: 0 0 16.666667% !important; max-width: 16.666667% !important; }
        .charts-row .col-lg-7 { flex: 0 0 60% !important; max-width: 60% !important; }
        .charts-row .col-lg-5 { flex: 0 0 40% !important; max-width: 40% !important; }
        
        .detailed-list-section { display: block !important; width: 100% !important; margin-top: 10px !important; }
    }
    .print-only { display: none; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Print Header -->
    <div class="print-only report-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <img src="<?php echo e(asset('images/logo.webp')); ?>" alt="Logo" style="height: 50px;" onerror="this.style.display='none'">
                <h1 class="fw-bold text-dark mt-2">Design Performance Report</h1>
                <p class="mb-0 text-dark">Period: <?php echo e(\Carbon\Carbon::parse($dateFrom)->format('M d, Y')); ?> - <?php echo e(\Carbon\Carbon::parse($dateTo)->format('M d, Y')); ?></p>
            </div>
            <div class="text-end text-dark">
                <p class="mb-1 fw-bold">CHIBOBRAND CO. LTD.</p>
                <p class="mb-1">Generated: <?php echo e(now()->format('M d, Y H:i')); ?></p>
                <p class="mb-0">Format: A4 Professional Analytics</p>
            </div>
        </div>
    </div>

    <!-- Header (Matching Task Index Style) -->
    <div class="row mb-3 no-print">
        <div class="col-12">
            <div class="d-flex flex-row justify-content-between align-items-start mb-2 gap-2">
                <div class="flex-grow-1">
                    <h2 class="mb-0 fw-bold">Design Task Analytics</h2>

                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?php echo e(route('admin.design-tasks.index')); ?>" class="btn <?php echo e(request()->routeIs('admin.design-tasks.index') ? 'btn-primary' : 'btn-outline-primary'); ?> btn-sm">
                        <i class="fas fa-list me-1"></i> All Tasks
                    </a>
                    <a href="<?php echo e(route('admin.design-tasks.paid')); ?>" class="btn <?php echo e(request()->routeIs('admin.design-tasks.paid') ? 'btn-success' : 'btn-outline-success'); ?> btn-sm">
                        <i class="fas fa-check-circle me-1"></i> Paid
                    </a>
                    <a href="<?php echo e(route('admin.design-tasks.pending')); ?>" class="btn <?php echo e(request()->routeIs('admin.design-tasks.pending') ? 'btn-warning' : 'btn-outline-warning'); ?> btn-sm text-dark">
                        <i class="fas fa-clock me-1"></i> Pending
                    </a>
                    <a href="<?php echo e(route('admin.design-tasks.invoices')); ?>" class="btn <?php echo e(request()->routeIs('admin.design-tasks.invoices') ? 'btn-info' : 'btn-outline-info'); ?> btn-sm text-white">
                        <i class="fas fa-file-invoice me-1"></i> Invoices
                    </a>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-1 mb-2">
                <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                    <i class="fas fa-filter"></i>
                </button>
                <button class="btn btn-primary btn-sm" onclick="window.print()">
                    <i class="fas fa-print"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="collapse show mb-4 filter-section" id="filterCollapse">
        <div class="card border-0 shadow-sm border-top border-4 border-primary">
            <div class="card-body bg-light p-3">
                <form action="<?php echo e(route('admin.design-tasks.reports')); ?>" method="GET" class="row g-2">
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">From</label>
                        <input type="date" class="form-control form-control-sm" name="date_from" value="<?php echo e($dateFrom); ?>">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">To</label>
                        <input type="date" class="form-control form-control-sm" name="date_to" value="<?php echo e($dateTo); ?>">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Grouping</label>
                        <select class="form-select form-select-sm" name="period">
                            <option value="hour" <?php echo e($period == 'hour' ? 'selected' : ''); ?>>Hourly</option>
                            <option value="day" <?php echo e($period == 'day' ? 'selected' : ''); ?>>Daily</option>
                            <option value="week" <?php echo e($period == 'week' ? 'selected' : ''); ?>>Weekly</option>
                            <option value="month" <?php echo e($period == 'month' ? 'selected' : ''); ?>>Monthly</option>
                            <option value="half_month" <?php echo e($period == 'half_month' ? 'selected' : ''); ?>>15-Day</option>
                            <option value="quarter" <?php echo e($period == 'quarter' ? 'selected' : ''); ?>>Quarterly</option>
                            <option value="year" <?php echo e($period == 'year' ? 'selected' : ''); ?>>Yearly</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Status</label>
                        <select class="form-select form-select-sm" name="status">
                            <option value="">All</option>
                            <?php $__currentLoopData = \App\Models\DesignTask::getStatusOptions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php echo e(request('status') == $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end mt-2 mt-md-0">
                        <button type="submit" data-no-global-handler class="btn btn-primary btn-sm w-100 fw-bold">
                            <i class="fas fa-sync-alt me-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Period Filters -->
    <div class="row mb-3 no-print">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo e(route('admin.design-tasks.reports', ['filter_period' => 'today'])); ?>" 
                   class="btn btn-sm <?php echo e(request('filter_period') == 'today' ? 'btn-primary' : 'btn-outline-primary'); ?>">
                    <i class="fas fa-clock me-1"></i> Today
                </a>
                <a href="<?php echo e(route('admin.design-tasks.reports', ['filter_period' => 'week'])); ?>" 
                   class="btn btn-sm <?php echo e(request('filter_period') == 'week' ? 'btn-primary' : 'btn-outline-primary'); ?>">
                    <i class="fas fa-calendar-day me-1"></i> This Week
                </a>
                <a href="<?php echo e(route('admin.design-tasks.reports', ['filter_period' => 'month'])); ?>" 
                   class="btn btn-sm <?php echo e(request('filter_period') == 'month' ? 'btn-primary' : 'btn-outline-primary'); ?>">
                    <i class="fas fa-calendar-alt me-1"></i> This Month
                </a>
                <a href="<?php echo e(route('admin.design-tasks.reports', ['filter_period' => 'half_year'])); ?>" 
                   class="btn btn-sm <?php echo e(request('filter_period') == 'half_year' ? 'btn-primary' : 'btn-outline-primary'); ?>">
                    <i class="fas fa-calendar-week me-1"></i> 6 Months
                </a>
                <a href="<?php echo e(route('admin.design-tasks.reports', ['filter_period' => 'year'])); ?>" 
                   class="btn btn-sm <?php echo e(request('filter_period') == 'year' ? 'btn-primary' : 'btn-outline-primary'); ?>">
                    <i class="fas fa-calendar me-1"></i> This Year
                </a>
                <a href="<?php echo e(route('admin.design-tasks.reports')); ?>" 
                   class="btn btn-sm <?php echo e(!request('filter_period') && !request('date_from') ? 'btn-primary' : 'btn-outline-secondary'); ?>">
                    <i class="fas fa-infinity me-1"></i> All Time
                </a>
            </div>
        </div>
    </div>

    <!-- Main Metrics - Row 1 (Core Activity) -->
    <div class="row g-2 mb-2 metrics-row">
        <div class="col-6 col-md-2 stats-col">
            <div class="card border-0 shadow-sm h-100 card-metric">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2">
                            <i class="fas fa-layer-group fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Total Tasks</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-dark"><?php echo e(number_format($totalTasks)); ?></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2 stats-col">
            <div class="card border-0 shadow-sm h-100 card-metric">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-success bg-opacity-10 text-success me-2">
                            <i class="fas fa-check-double fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Completed</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-dark"><?php echo e(number_format($completedTasks)); ?></div>
                    <div class="x-small text-success fw-bold"><?php echo e($totalTasks > 0 ? round(($completedTasks/$totalTasks)*100, 1) : 0); ?>% Finished</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2 stats-col">
            <div class="card border-0 shadow-sm h-100 card-metric">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-info bg-opacity-10 text-info me-2">
                            <i class="fas fa-wallet fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Total Revenue</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-dark"><?php echo e(number_format($totalRevenue)); ?></div>
                    <div class="x-small text-muted">Received + Outstanding</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2 stats-col">
            <div class="card border-0 shadow-sm h-100 card-metric">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-warning bg-opacity-10 text-warning me-2">
                            <i class="fas fa-stopwatch fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Avg Time</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-dark"><?php echo e(number_format($avgCompletionHours, 1)); ?><span class="fs-6 fw-normal">h</span></div>
                    <div class="x-small text-muted">Per completion</div>
                </div>
            </div>
        </div>
        <!-- Merged Row 2 starts here -->
        <div class="col-6 col-md-2 stats-col">
            <div class="card border-0 shadow-sm h-100 card-metric">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-secondary bg-opacity-10 text-secondary me-2">
                            <i class="fas fa-print fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Printing Job</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-dark"><?php echo e(number_format($printingTasks)); ?></div>
                    <div class="x-small text-muted">Ready/In-Print</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2 stats-col">
            <div class="card border-0 shadow-sm h-100 card-metric">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-danger bg-opacity-10 text-danger me-2">
                            <i class="fas fa-undo-alt fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Revisions</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-dark"><?php echo e(number_format($revisionCount)); ?></div>
                    <div class="x-small text-muted">Total requests</div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-md-2 stats-col">
            <div class="card border-0 shadow-sm h-100 card-metric">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2">
                            <i class="fas fa-receipt fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Amount Collected</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-dark"><?php echo e(number_format($totalPaid)); ?></div>
                    <div class="x-small text-success fw-bold">Money received</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2 stats-col">
            <div class="card border-0 shadow-sm h-100 card-metric">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-warning bg-opacity-10 text-warning me-2">
                            <i class="fas fa-hourglass-half fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Balance Due</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-dark"><?php echo e(number_format($totalBalance)); ?></div>
                    <div class="x-small text-danger fw-bold">Outstanding</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-2 stats-col">
            <div class="card border-0 shadow-sm h-100 card-metric">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-danger bg-opacity-10 text-danger me-2">
                            <i class="fas fa-exclamation-triangle fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Overdue</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-danger"><?php echo e(number_format($overdueTasks)); ?></div>
                    <div class="x-small text-muted">Missed deadline</div>
                </div>
            </div>
        </div>
        <!-- Merged Row 3 starts here -->
        <?php $__currentLoopData = ['High' => 'danger', 'Medium' => 'warning', 'Low' => 'success']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-6 col-md-2 stats-col">
            <div class="card border-0 shadow-sm h-100 card-metric">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-<?php echo e($color); ?> bg-opacity-10 text-<?php echo e($color); ?> me-2">
                            <i class="fas fa-flag fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted"><?php echo e($label); ?> Priority</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-<?php echo e($color); ?>"><?php echo e(number_format($priorityDistribution[$label] ?? 0)); ?></div>
                    <div class="x-small text-muted">Tasks</div>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        

         <div class="col-6 col-md-2 stats-col">
            <div class="card border-0 shadow-sm h-100 card-metric">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary me-2">
                            <i class="fas fa-percent fa-sm"></i>
                        </div>
                        <span class="text-uppercase x-small fw-bold text-muted">Success Rate</span>
                    </div>
                    <div class="h2 mb-0 fw-bold text-primary"><?php echo e($totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0); ?>%</div>
                    <div class="x-small text-muted">Completion rate</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4 charts-row">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-area me-2"></i>Engagement Trend</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 280px;">
                        <canvas id="tasksAreaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-pie me-2"></i>Current Status</h6>
                </div>
                <div class="card-body">
                    <div style="height: 220px;">
                        <canvas id="statusPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Multi-Role Performance Row -->
    <div class="row g-3 mb-4">
        <!-- Designers -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary">Top Designers</h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary x-small">By Volume</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th class="ps-3 border-0 x-small">Design Staff</th>
                                    <th class="text-center border-0 x-small">Tasks</th>
                                    <th class="text-end pe-3 border-0 x-small">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $topDesigners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $designer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center py-1">
                                            <div class="avatar-circle bg-primary text-white me-2">
                                                <?php echo e(substr($designer->designer->name ?? '?', 0, 1)); ?>

                                            </div>
                                            <span class="small fw-bold"><?php echo e($designer->designer->name ?? 'Unknown'); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center small"><?php echo e($designer->task_count); ?></td>
                                    <td class="text-end pe-3 small text-success fw-bold"><?php echo e(number_format($designer->total_revenue)); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Receptionists -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-info">Top Receptionists</h6>
                    <span class="badge bg-info bg-opacity-10 text-info x-small">Lead Gen</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th class="ps-3 border-0 x-small">User</th>
                                    <th class="text-center border-0 x-small">Tasks Managed</th>
                                    <th class="text-center border-0 x-small">Impact</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $topReceptionists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center py-1">
                                            <div class="avatar-circle bg-info text-white me-2">
                                                <?php echo e(substr($rec->receptionist->name ?? '?', 0, 1)); ?>

                                            </div>
                                            <span class="small fw-bold"><?php echo e($rec->receptionist->name ?? 'Unknown'); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center small"><?php echo e($rec->task_count); ?></td>
                                    <td class="text-center">
                                        <div class="progress" style="height: 4px; width: 60px; margin: 0 auto;">
                                            <div class="progress-bar bg-info" style="width: <?php echo e($totalTasks > 0 ? ($rec->task_count / $totalTasks) * 100 : 0); ?>%"></div>
                                        </div>
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

    <!-- Secondary Role Row -->
    <div class="row g-3 mb-4">
         <!-- Salers -->
         <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-success">Top Salers</h6>
                    <span class="badge bg-success bg-opacity-10 text-success x-small">Sales Boost</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th class="ps-3 border-0 x-small">Saler</th>
                                    <th class="text-center border-0 x-small">Design Conversion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $topSalers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $saler): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center py-1">
                                            <div class="avatar-circle bg-success text-white me-2">
                                                <?php echo e(substr($saler->saler->name ?? '?', 0, 1)); ?>

                                            </div>
                                            <span class="small fw-bold"><?php echo e($saler->saler->name ?? 'Unknown'); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center small"><?php echo e($saler->task_count); ?> jobs</td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operators -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark">Top Operators</h6>
                    <span class="badge bg-dark bg-opacity-10 text-dark x-small">Production</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th class="ps-3 border-0 x-small">Operator</th>
                                    <th class="text-center border-0 x-small">Total Handling</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $topOperators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center py-1">
                                            <div class="avatar-circle bg-dark text-white me-2">
                                                <?php echo e(substr($op->operator->name ?? '?', 0, 1)); ?>

                                            </div>
                                            <span class="small fw-bold"><?php echo e($op->operator->name ?? 'Unknown'); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center small"><?php echo e($op->task_count); ?> jobs</td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    </div>

    <!-- Detailed List Section (Advanced Printing) -->
    <div class="row g-3 mb-4 detailed-list-section">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-list me-2"></i>Recent Design Tasks</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th class="small ps-3">Date</th>
                                    <th class="small">Code</th>
                                    <th class="small">Title</th>
                                    <th class="small">Customer</th>
                                    <th class="small">Designer</th>
                                    <th class="small text-center">Status</th>
                                    <th class="text-end pe-3">Price</th>
                                    <th class="text-center no-print">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $recentTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="ps-3 small"><?php echo e($task->created_at->format('M d, Y')); ?></td>
                                    <td class="small fw-bold"><?php echo e($task->task_code); ?></td>
                                    <td class="small"><?php echo e(Str::limit($task->title, 30)); ?></td>
                                    <td class="small"><?php echo e($task->customer->name ?? 'N/A'); ?></td>
                                    <td class="small"><?php echo e($task->designer->name ?? 'N/A'); ?></td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-opacity-10 
                                            <?php echo e($task->status == 'completed' || $task->status == 'super_completed' ? 'bg-success text-success' : 'bg-warning text-dark'); ?>" style="font-size: 0.65rem;">
                                            <?php echo e(ucfirst(str_replace('_', ' ', $task->status))); ?>

                                        </span>
                                    </td>
                                    <td class="text-end pe-3 small fw-bold"><?php echo e(number_format($task->price)); ?></td>
                                    <td class="text-center no-print">
                                        <a href="<?php echo e(route('admin.design-tasks.show', $task)); ?>" class="btn btn-primary btn-xs p-1" title="View Task">
                                            <i class="fas fa-eye shadow-sm" style="font-size: 0.75rem;"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No design tasks found for this period.</td>
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
        <p class="mb-0">&copy; <?php echo e(date('Y')); ?> CHIBO BRANDS CO. LTD. All rights reserved.</p>
        <p class="mb-0">Developed by <a href="https://fridoltech.org" style="color: #000; text-decoration: none; font-weight: bold;">Fridoltech</a></p>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    const chartDataRaw = <?php echo json_encode($chartData, 15, 512) ?>;
    const periods = chartDataRaw.map(item => item.period);
    const counts = chartDataRaw.map(item => item.count);
    const revenues = chartDataRaw.map(item => item.revenue);

    const statusDataRaw = <?php echo json_encode($statusDistribution, 15, 512) ?>;
    const statusLabels = Object.keys(statusDataRaw);
    const statusValues = Object.values(statusDataRaw);

    // Area Chart
    const ctxArea = document.getElementById("tasksAreaChart").getContext('2d');
    new Chart(ctxArea, {
        type: 'line',
        data: {
            labels: periods,
            datasets: [{
                label: "Task Count",
                lineTension: 0.3,
                backgroundColor: "rgba(78, 115, 223, 0.05)",
                borderColor: "rgba(78, 115, 223, 1)",
                pointRadius: 2,
                data: counts,
                yAxisID: 'y'
            }, {
                label: "Revenue",
                type: 'bar',
                backgroundColor: "rgba(28, 200, 138, 0.4)",
                data: revenues,
                yAxisID: 'y1',
                barThickness: 8
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { type: 'linear', position: 'left', ticks: { font: { size: 10 } } },
                y1: { type: 'linear', position: 'right', grid: { display: false }, ticks: { font: { size: 9 }, callback: v => v.toLocaleString() } }
            },
            plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 10, font: { size: 10 } } } }
        }
    });

    // Doughnut Chart
    const ctxPie = document.getElementById("statusPieChart").getContext('2d');
    const colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#5a5c69', '#fd7e14', '#20c997'];
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: statusLabels.map(s => s.replace('_', ' ').toUpperCase()),
            datasets: [{
                data: statusValues,
                backgroundColor: colors.slice(0, statusValues.length),
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 } } }
            },
            cutout: '70%'
        }
    });

    // Professional Print Title
    window.onbeforeprint = () => { document.title = "Designer_Performance_Report_<?php echo e(now()->format('Ymd')); ?>"; };
    window.onafterprint = () => { document.title = "<?php echo $__env->yieldContent('title'); ?>"; };
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/gotlaptopparts.com/Downloads/chibo_sales/resources/views/admin/design-tasks/reports.blade.php ENDPATH**/ ?>