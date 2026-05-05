<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?php echo $__env->yieldContent('title', 'Admin Dashboard - CHIBO BRAND'); ?></title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo e(url('favicon.ico')); ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo e(url('favicon.ico')); ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="<?php echo e(asset('css/admin.css')); ?>?v=<?php echo e(filemtime(public_path('css/admin.css'))); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>?v=<?php echo e(filemtime(public_path('css/style.css'))); ?>">
    <style>
        /* Enforce consistent app typography */
        body {
            font-family: 'Nunito Sans', sans-serif !important;
        }
    </style>
    <style>
        /* Technical Support Floating Button */
        .tech-support-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .tech-support-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4);
            color: white;
            font-size: 24px;
            position: relative;
            border: 2px solid white;
        }

        .tech-support-label {
            background: white;
            color: #333;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            margin-right: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            opacity: 0;
            transform: translateX(20px);
            transition: all 0.3s ease;
            white-space: nowrap;
            pointer-events: none;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .tech-support-float:hover .tech-support-label {
            opacity: 1;
            transform: translateX(0);
        }

        .tech-support-float:hover {
            transform: scale(1.05);
        }

        .tech-support-float:hover .tech-support-icon {
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.6);
        }

        .pulse-effect {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: #25D366;
            opacity: 0.6;
            z-index: -1;
            animation: pulse-wa 2s infinite;
        }

        @keyframes pulse-wa {
            0% {
                transform: scale(1);
                opacity: 0.6;
            }

            100% {
                transform: scale(1.6);
                opacity: 0;
            }
        }

        @media (max-width: 768px) {
            .tech-support-float {
                bottom: 20px;
                right: 20px;
            }

            .tech-support-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .tech-support-label {
                display: none;
            }
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body>
    <!-- Preloader -->
    <div id="preloader">
        <div class="preloader-content">
            <div class="preloader-logo-wrapper">
                <img src="<?php echo e(asset('images/round.webp')); ?>" alt="Loading..." class="preloader-logo">
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="text-start ps-2">
                    <img src="<?php echo e(asset('images/logo.webp')); ?>" alt="CHIBO BRAND Logo"
                        style="max-width: 85px; height: auto; margin-bottom: 0.1rem;"
                        onerror="this.style.display='none';">
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <?php
                    $userRole = auth()->user()->role ?? null;
                ?>

                
                <?php if($userRole === 'saler'): ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.saler.my-dashboard') ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.saler.my-dashboard')); ?>" data-no-preloader>
                            <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
                        </a>
                    </li>
                <?php endif; ?>

                
                <?php
                    $dashboardRoute = 'admin.dashboard';
                    if ($userRole === 'accountant')
                        $dashboardRoute = 'admin.finance.dashboard';
                    if ($userRole === 'gatekeeper')
                        $dashboardRoute = 'gatekeeper.dashboard';
                    // Super Admin and Admin use standard dashboard
                ?>

                <?php if(
                        in_array($userRole, [
                            'admin',
                            'super_admin',
                            'manager',
                            'receptionist',
                            'designer',
                            'operator',
                            'accountant',
                            'gatekeeper',
                            'delivery'
                        ])
                    ): ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs($dashboardRoute) ? 'active' : ''); ?>"
                            href="<?php echo e(route($dashboardRoute)); ?>" data-no-preloader>
                            <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
                        </a>
                    </li>
                <?php endif; ?>

                
                <?php if(auth()->user()->hasPermission('manage_design_tasks')): ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.design-tasks.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.design-tasks.index')); ?>" data-no-preloader>
                            <i class="fas fa-palette"></i><span>Design Tasks</span>
                        </a>
                    </li>
                <?php endif; ?>


                
                <?php if($userRole === 'delivery'): ?>
                    <!-- Delivery dashboard is handled by the main dashboard route, but we can add specific links if needed later -->
                <?php endif; ?>

                
                <?php if($userRole === 'gatekeeper'): ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('gatekeeper.movements.create') && request('type') == 'in' ? 'active' : ''); ?>"
                            href="<?php echo e(route('gatekeeper.movements.create', ['type' => 'in'])); ?>" data-no-preloader>
                            <i class="fas fa-arrow-down text-success"></i><span>Record Incoming</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('gatekeeper.movements.create') && request('type') == 'out' ? 'active' : ''); ?>"
                            href="<?php echo e(route('gatekeeper.movements.create', ['type' => 'out'])); ?>" data-no-preloader>
                            <i class="fas fa-arrow-up text-warning"></i><span>Record Outgoing</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('gatekeeper.movements.index') ? 'active' : ''); ?>"
                            href="<?php echo e(route('gatekeeper.movements.index')); ?>" data-no-preloader>
                            <i class="fas fa-list"></i><span>Movement Log</span>
                        </a>
                    </li>
                <?php endif; ?>

                
                <?php if(
                        (auth()->user()->hasPermission('manage_inventory') || $userRole === 'accountant') &&
                        !in_array($userRole, ['gatekeeper', 'receptionist', 'operator', 'saler'])
                    ): ?>
                    <li class="nav-item has-submenu <?php echo e(request()->routeIs('gatekeeper.*') ? 'active' : ''); ?>"
                        data-submenu-toggle>
                        <a class="nav-link d-flex align-items-center justify-content-between <?php echo e(request()->routeIs('gatekeeper.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('gatekeeper.movements.index')); ?>" data-no-preloader>
                            <span class="d-flex align-items-center">
                                <i class="fas fa-warehouse"></i><span>Gatekeeper</span>
                            </span>
                            <i class="fas fa-chevron-right submenu-arrow"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('gatekeeper.movements.create') && request('type') == 'in' ? 'active' : ''); ?>"
                                    href="<?php echo e(route('gatekeeper.movements.create', ['type' => 'in'])); ?>" data-no-preloader>
                                    <i class="fas fa-arrow-down text-success"></i><span>Record Incoming</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('gatekeeper.movements.create') && request('type') == 'out' ? 'active' : ''); ?>"
                                    href="<?php echo e(route('gatekeeper.movements.create', ['type' => 'out'])); ?>" data-no-preloader>
                                    <i class="fas fa-arrow-up text-warning"></i><span>Record Outgoing</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('gatekeeper.movements.index') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('gatekeeper.movements.index')); ?>" data-no-preloader>
                                    <i class="fas fa-exchange-alt"></i><span>Movement Logs</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>



                
                <?php if(in_array($userRole, ['admin', 'super_admin', 'manager', 'accountant'])): ?>
                    <li class="nav-item has-submenu <?php echo e((request()->routeIs('admin.finance.pending-payments') || request()->routeIs('admin.finance.cash-flow') || request()->routeIs('admin.finance.expenses') || request()->routeIs('admin.finance.balance-sheet') || request()->routeIs('admin.finance.profit-loss') || request()->routeIs('admin.finance.payment-requests.*') || request()->routeIs('admin.finance.audit') || request()->routeIs('admin.finance.departments.*') || request()->routeIs('admin.finance.proforma.*')) ? 'active' : ''); ?>"
                        data-submenu-toggle>
                        <a class="nav-link d-flex align-items-center justify-content-between <?php echo e((request()->routeIs('admin.finance.pending-payments') || request()->routeIs('admin.finance.cash-flow') || request()->routeIs('admin.finance.expenses') || request()->routeIs('admin.finance.balance-sheet') || request()->routeIs('admin.finance.profit-loss') || request()->routeIs('admin.finance.payment-requests.*') || request()->routeIs('admin.finance.audit') || request()->routeIs('admin.finance.departments.*') || request()->routeIs('admin.finance.proforma.*')) ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.finance.cash-flow')); ?>" data-no-preloader>
                            <span class="d-flex align-items-center">
                                <i class="fas fa-shield-halved text-warning"></i><span>Finance Control</span>
                            </span>
                            <i class="fas fa-chevron-right submenu-arrow"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.finance.cash-flow') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.finance.cash-flow')); ?>">
                                    <i class="fas fa-exchange-alt text-primary"></i><span>Cash Flow</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.finance.pending-payments') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.finance.pending-payments')); ?>">
                                    <i class="fas fa-money-bill-wave text-danger"></i><span>Depts</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.finance.daily-report') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.finance.daily-report')); ?>">
                                    <i class="fas fa-file-invoice-dollar text-success"></i><span>Daily Report</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.finance.balance-sheet') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.finance.balance-sheet')); ?>">
                                    <i class="fas fa-balance-scale text-primary"></i><span>Balance Sheet</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.finance.profit-loss') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.finance.profit-loss')); ?>">
                                    <i class="fas fa-file-invoice text-success"></i><span>Profit & Loss</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.finance.proforma.index') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.finance.proforma.index')); ?>">
                                    <i class="fas fa-file-invoice text-info"></i><span>Proforma Invoices</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.finance.expenses') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.finance.expenses')); ?>">
                                    <i class="fas fa-receipt text-danger"></i><span>Expenses</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.finance.payment-requests.*') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.finance.payment-requests.index')); ?>">
                                    <i class="fas fa-hand-holding-usd"></i><span>Payment Requests</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.finance.audit') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.finance.audit')); ?>">
                                    <i class="fas fa-calculator"></i><span>Audit & Logs</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                
                <?php if(in_array($userRole, ['super_admin', 'admin', 'accountant', 'manager'])): ?>
                    <li class="nav-item has-submenu <?php echo e((request()->routeIs('admin.sales-dept.*') || request()->routeIs('admin.leads.*') || request()->routeIs('admin.saler-performance.*')) ? 'active' : ''); ?>"
                        data-submenu-toggle>
                        <a class="nav-link d-flex align-items-center justify-content-between <?php echo e((request()->routeIs('admin.sales-dept.*') || request()->routeIs('admin.leads.*') || request()->routeIs('admin.saler-performance.*')) ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.sales-dept.index')); ?>" data-no-preloader>
                            <span class="d-flex align-items-center">
                                <i class="fas fa-briefcase text-primary"></i><span>Sales Dept</span>
                            </span>
                            <i class="fas fa-chevron-right submenu-arrow"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.sales-dept.index') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.sales-dept.index')); ?>">
                                    <i class="fas fa-chart-line"></i><span>Performance</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.leads.*') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.leads.index')); ?>">
                                    <i class="fas fa-filter text-info"></i><span>Leads Management</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.sales-dept.targets') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.sales-dept.targets')); ?>">
                                    <i class="fas fa-bullseye"></i><span>Sales Targets</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if($userRole === 'saler'): ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.saler-performance.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.saler-performance.index')); ?>" data-no-preloader>
                            <i class="fas fa-chart-line"></i><span>Performance Stats</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.leads.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.leads.index')); ?>" data-no-preloader>
                            <i class="fas fa-filter text-info"></i><span>Leads Management</span>
                        </a>
                    </li>
                <?php endif; ?>

                
                <?php if(
                        auth()->user()->hasPermission('manage_products') && !in_array($userRole, [
                            'receptionist',
                            'operator'
                        ])
                    ): ?>
                    <li class="nav-item has-submenu <?php echo e((request()->routeIs('admin.hero-slides.*') || request()->routeIs('admin.enhanced-products.*') || request()->routeIs('admin.products.*') || request()->routeIs('admin.categories.*')) ? 'active' : ''); ?>"
                        data-submenu-toggle>
                        <a class="nav-link d-flex align-items-center justify-content-between <?php echo e((request()->routeIs('admin.hero-slides.*') || request()->routeIs('admin.enhanced-products.*') || request()->routeIs('admin.products.*') || request()->routeIs('admin.categories.*')) ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.enhanced-products.index')); ?>" data-no-preloader>
                            <span class="d-flex align-items-center">
                                <i class="fas fa-box"></i><span>Inventory</span>
                            </span>
                            <i class="fas fa-chevron-right submenu-arrow"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(((request()->routeIs('admin.enhanced-products.*') && !request()->routeIs('admin.enhanced-products.offers*')) || request()->routeIs('admin.products.*')) && !request()->has('stock_status') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.enhanced-products.index')); ?>" data-no-preloader>
                                    <i class="fas fa-box"></i><span>All Products</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.enhanced-products.index') && request('stock_status') === 'in_stock' ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.enhanced-products.index', ['stock_status' => 'in_stock'])); ?>"
                                    data-no-preloader>
                                    <i class="fas fa-check-circle"></i><span>In Stock Products</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.enhanced-products.index') && request('stock_status') === 'out_of_stock' ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.enhanced-products.index', ['stock_status' => 'out_of_stock'])); ?>"
                                    data-no-preloader>
                                    <i class="fas fa-times-circle"></i><span>Out of Stock Products</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.categories.*') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.categories.index')); ?>" data-no-preloader>
                                    <i class="fas fa-tags"></i><span>Categories</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.enhanced-products.offers*') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.enhanced-products.offers')); ?>" data-no-preloader>
                                    <i class="fas fa-percentage"></i><span>Offers</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.hero-slides.*') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.hero-slides.index')); ?>" data-no-preloader>
                                    <i class="fas fa-images"></i><span>Hero Slides</span>
                                </a>
                            </li>
                            <?php if(
                                    auth()->user()->hasPermission('manage_design_tasks') && in_array($userRole, [
                                        'accountant',
                                        'super_admin',
                                        'manager',
                                        'admin'
                                    ])
                                ): ?>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.design-task-types.*') ? 'active' : ''); ?>"
                                        href="<?php echo e(route('admin.design-task-types.index')); ?>" data-no-preloader>
                                        <i class="fas fa-tags"></i><span>Task Types</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                
                <?php if(auth()->user()->hasPermission('manage_orders')): ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.orders.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.orders.index')); ?>" data-no-preloader>
                            <i class="fas fa-shopping-cart"></i><span>Online Orders</span>
                        </a>
                    </li>
                <?php endif; ?>



                
                <?php if(auth()->user()->hasPermission('manage_orders') && $userRole !== 'accountant'): ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.pos.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.pos.index')); ?>" data-no-preloader>
                            <i class="fas fa-cash-register"></i><span>POS Terminal</span>
                        </a>
                    </li>
                <?php endif; ?>

                
                <?php if(
                        auth()->user()->hasPermission('manage_customers') && !in_array($userRole, [
                            'receptionist',
                            'operator'
                        ])
                    ): ?>
                    <li class="nav-item has-submenu <?php echo e((request()->routeIs('admin.customers.*') || request()->routeIs('admin.customer-data-center.*')) ? 'active' : ''); ?>"
                        data-submenu-toggle>
                        <a class="nav-link d-flex align-items-center justify-content-between <?php echo e((request()->routeIs('admin.customers.*') || request()->routeIs('admin.customer-data-center.*')) ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.customers.index')); ?>" data-no-preloader>
                            <span class="d-flex align-items-center">
                                <i class="fas fa-users text-info"></i><span>Customers & CRM</span>
                            </span>
                            <i class="fas fa-chevron-right submenu-arrow"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.customers.*') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.customers.index')); ?>">
                                    <i class="fas fa-user-friends"></i><span>Manage Customers</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.customer-data-center.index') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.customer-data-center.index')); ?>">
                                    <i class="fas fa-brain text-warning"></i><span>Data Center</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                
                <?php if(in_array($userRole, ['super_admin', 'admin', 'manager'])): ?>
                    <li class="nav-item has-submenu <?php echo e((request()->routeIs('admin.message-templates.*') || request()->routeIs('admin.finance.departments.*') || request()->routeIs('admin.contact-messages.*')) ? 'active' : ''); ?>"
                        data-submenu-toggle>
                        <a class="nav-link d-flex align-items-center justify-content-between <?php echo e((request()->routeIs('admin.message-templates.*') || request()->routeIs('admin.finance.departments.*') || request()->routeIs('admin.contact-messages.*')) ? 'active' : ''); ?>"
                            href="#" data-no-preloader>
                            <span class="d-flex align-items-center">
                                <i class="fas fa-tools"></i><span>System Management</span>
                            </span>
                            <i class="fas fa-chevron-right submenu-arrow"></i>
                        </a>
                        <ul class="nav-submenu">
                            <?php if(
                                    auth()->user()->hasPermission('manage_customers') && !in_array($userRole, [
                                        'accountant',
                                        'receptionist',
                                        'operator',
                                        'saler'
                                    ])
                                ): ?>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.message-templates.*') ? 'active' : ''); ?>"
                                        href="<?php echo e(route('admin.message-templates.index')); ?>" data-no-preloader>
                                        <i class="fas fa-comment-dots"></i><span>Message Templates</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if(
                                    auth()->user()->hasPermission('view_contact_messages') && !in_array(
                                        $userRole,
                                        ['receptionist', 'operator', 'saler']
                                    )
                                ): ?>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.contact-messages.*') ? 'active' : ''); ?>"
                                        href="<?php echo e(route('admin.contact-messages.index')); ?>" data-no-preloader>
                                        <i class="fas fa-envelope"></i><span>Contact Messages</span>
                                        <?php
                                            $newMessagesCount = 0;
                                            if (in_array($userRole, ['admin', 'super_admin', 'manager'])) {
                                                $newMessagesCount = \App\Models\ContactMessage::where('status', 'new')->count();
                                            } else {
                                                $userEmail = auth()->user()->email ?? '';
                                                $userPhone = auth()->user()->phone ?? '';
                                                $normalizePhone = function ($phone) {
                                                    if (empty($phone))
                                                        return '';
                                                    return preg_replace('/[^\d+]/', '', $phone);
                                                };
                                                $normalizedUserPhone = $normalizePhone($userPhone);

                                                $newMessagesCount = \App\Models\ContactMessage::where('status', 'new')
                                                    ->where(function ($q) use ($userEmail, $normalizedUserPhone) {
                                                        if (!empty($userEmail)) {
                                                            $q->where('email', $userEmail);
                                                        }
                                                        if (!empty($normalizedUserPhone)) {
                                                            $q->orWhereRaw('REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, " ", ""), "-", ""), "(",
                                                                                                                                                                                                                                                                                            ""), ")", ""), ".", "") LIKE ?', ['%' . $normalizedUserPhone . '%']);
                                                            $q->orWhere('phone', 'LIKE', '%' . $normalizedUserPhone . '%');
                                                        }
                                                    })->count();
                                            }
                                        ?>
                                        <?php if($newMessagesCount > 0): ?>
                                            <span class="badge bg-danger ms-auto"><?php echo e($newMessagesCount); ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if(in_array($userRole, ['admin', 'super_admin'])): ?>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.finance.departments.*') ? 'active' : ''); ?>"
                                        href="<?php echo e(route('admin.finance.departments.index')); ?>" data-no-preloader>
                                        <i class="fas fa-building"></i><span>Manage Departments</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php else: ?>
                    
                    <?php if(
                            auth()->user()->hasPermission('manage_customers') && !in_array($userRole, [
                                'accountant',
                                'receptionist',
                                'operator',
                                'saler'
                            ])
                        ): ?>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.message-templates.*') ? 'active' : ''); ?>"
                                href="<?php echo e(route('admin.message-templates.index')); ?>" data-no-preloader>
                                <i class="fas fa-comment-dots"></i><span>Message Templates</span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.notifications.*') ? 'active' : ''); ?>"
                        href="<?php echo e(route('admin.notifications.index')); ?>" data-no-preloader>
                        <i class="fas fa-bell"></i><span>Notifications</span>
                        <?php
                            $unreadCount = auth()->user() ? auth()->user()->unreadNotifications()->count() : 0;
                        ?>
                        <?php if($unreadCount > 0): ?>
                            <span class="badge bg-danger ms-auto"><?php echo e($unreadCount); ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                
                <?php if(
                        auth()->user()->hasPermission('view_contact_messages') && !in_array($userRole, [
                            'receptionist',
                            'operator',
                            'saler',
                            'admin',
                            'super_admin',
                            'manager'
                        ])
                    ): ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.contact-messages.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.contact-messages.index')); ?>" data-no-preloader>
                            <i class="fas fa-envelope"></i><span>Contact Messages</span>
                            <?php
                                $newMessagesCount = 0;
                                if (in_array($userRole, ['admin', 'super_admin', 'manager'])) {
                                    $newMessagesCount = \App\Models\ContactMessage::where('status', 'new')->count();
                                } else {
                                    $userEmail = auth()->user()->email ?? '';
                                    $userPhone = auth()->user()->phone ?? '';
                                    $normalizePhone = function ($phone) {
                                        if (empty($phone))
                                            return '';
                                        return preg_replace('/[^\d+]/', '', $phone);
                                    };
                                    $normalizedUserPhone = $normalizePhone($userPhone);

                                    $newMessagesCount = \App\Models\ContactMessage::where('status', 'new')
                                        ->where(function ($q) use ($userEmail, $normalizedUserPhone) {
                                            if (!empty($userEmail)) {
                                                $q->where('email', $userEmail);
                                            }
                                            if (!empty($normalizedUserPhone)) {
                                                $q->orWhereRaw('REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, " ", ""), "-", ""), "(", ""),
                                                                                                                                                                                                                            ")", ""), ".", "") LIKE ?', ['%' . $normalizedUserPhone . '%']);
                                                $q->orWhere('phone', 'LIKE', '%' . $normalizedUserPhone . '%');
                                            }
                                        })->count();
                                }
                            ?>
                            <?php if($newMessagesCount > 0): ?>
                                <span class="badge bg-danger ms-auto"><?php echo e($newMessagesCount); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>


                
                <?php if(in_array($userRole, ['receptionist', 'designer', 'operator', 'delivery'])): ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.profile') ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.profile')); ?>" data-no-preloader>
                            <i class="fas fa-user"></i><span>Profile</span>
                        </a>
                    </li>
                <?php endif; ?>

                
                <?php if($userRole === 'delivery'): ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.delivery.incoming') ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.delivery.incoming')); ?>" data-no-preloader>
                            <i class="fas fa-inbox text-primary"></i><span>Incoming</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.delivery.completed') ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.delivery.completed')); ?>" data-no-preloader>
                            <i class="fas fa-check-circle text-success"></i><span>Completed</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.delivery.canceled') ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.delivery.canceled')); ?>" data-no-preloader>
                            <i class="fas fa-times-circle text-danger"></i><span>Canceled</span>
                        </a>
                    </li>
                <?php endif; ?>

                
                <?php if(
                        (auth()->user()->hasPermission('manage_inventory') ||
                            auth()->user()->hasPermission('manage_products') || $userRole === 'accountant') && $userRole !==
                        'saler'
                    ): ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.delivery.updates') ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.delivery.updates')); ?>" data-no-preloader>
                            <i class="fas fa-shipping-fast"></i><span>Delivery Updates</span>
                        </a>
                    </li>
                <?php endif; ?>



                
                <?php if(
                        auth()->user()->hasPermission('manage_reports') && !in_array($userRole, [
                            'receptionist',
                            'operator'
                        ])
                    ): ?>
                    <li class="nav-item has-submenu <?php echo e((request()->routeIs('admin.reports') || request()->routeIs('admin.reports.*') || request()->routeIs('admin.hero-slides.analytics') || request()->routeIs('admin.reports.design-tasks') || request()->routeIs('admin.design-tasks.reports') || request()->routeIs('admin.saler-performance.*') || request()->routeIs('admin.gatekeeper-performance.*') || request()->routeIs('admin.delivery-performance.*') || request()->routeIs('admin.reports.operators') || request()->routeIs('admin.finance.reports')) ? 'active' : ''); ?>"
                        data-submenu-toggle>
                        <a class="nav-link d-flex align-items-center justify-content-between <?php echo e((request()->routeIs('admin.reports') || request()->routeIs('admin.reports.*') || request()->routeIs('admin.hero-slides.analytics') || request()->routeIs('admin.reports.design-tasks') || request()->routeIs('admin.reports.operators') || request()->routeIs('admin.design-tasks.reports') || request()->routeIs('admin.saler-performance.*') || request()->routeIs('admin.gatekeeper-performance.*') || request()->routeIs('admin.delivery-performance.*') || request()->routeIs('admin.finance.reports')) ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.reports')); ?>" data-no-preloader>
                            <span class="d-flex align-items-center">
                                <i class="fas fa-chart-bar"></i><span>Reports</span>
                            </span>
                            <i class="fas fa-chevron-right submenu-arrow"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.design-tasks.reports') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.design-tasks.reports')); ?>" data-no-preloader>
                                    <i class="fas fa-chart-pie"></i><span>Design Tasks</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.finance.reports') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.finance.reports')); ?>" data-no-preloader>
                                    <i class="fas fa-coins text-warning"></i><span>Financial Reports</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.finance.daily-report') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.finance.daily-report')); ?>" data-no-preloader>
                                    <i class="fas fa-calendar-day text-info"></i><span>Daily Finance Summary</span>
                                </a>
                            </li>


                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.hero-slides.analytics') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.hero-slides.analytics')); ?>" data-no-preloader>
                                    <i class="fas fa-images"></i><span>Ads Reports</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.saler-performance.*') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.saler-performance.index')); ?>" data-no-preloader>
                                    <i class="fas fa-chart-line"></i><span>Saler Performance</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.reports.design-tasks') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.reports.design-tasks')); ?>" data-no-preloader>
                                    <i class="fas fa-paint-brush"></i><span>Designer Performance</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.reports.operators') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.reports.operators')); ?>" data-no-preloader>
                                    <i class="fas fa-print"></i><span>Operator Performance</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.gatekeeper-performance.*') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.gatekeeper-performance.index')); ?>" data-no-preloader>
                                    <i class="fas fa-door-open"></i><span>Gatekeeper Performance</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.delivery-performance.*') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.delivery-performance.index')); ?>" data-no-preloader>
                                    <i class="fas fa-truck-loading"></i><span>Delivery Performance</span>
                                </a>
                            </li>

                        </ul>
                    </li>
                <?php endif; ?>

                
                
                <?php if(auth()->user()->hasPermission('view_audit_logs')): ?>
                            <li class="nav-item has-submenu <?php echo e((request()->routeIs('admin.audit-logs.*') || request()->routeIs('admin.security.*') || request()->routeIs('admin.audit.*')) ? 'active' : ''); ?>"
                                data-submenu-toggle>
                                <a class="nav-link d-flex align-items-center justify-content-between <?php echo e((request()->routeIs('admin.audit-logs.*') || request()->routeIs('admin.security.*')) ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.audit-logs.index')); ?>" data-no-preloader>
                                    <span class="d-flex align-items-center">
                                        <i class="fas fa-shield-alt"></i><span><?php echo e(in_array($userRole, ['receptionist', 'operator'])
                    ? 'Audit Logs' : 'Security & Audit'); ?></span>
                                    </span>
                                    <?php if(!in_array($userRole, ['receptionist', 'operator'])): ?>
                                        <i class="fas fa-chevron-right submenu-arrow"></i>
                                    <?php endif; ?>
                                </a>
                                <?php if(!in_array($userRole, ['receptionist', 'operator'])): ?>
                                    <ul class="nav-submenu">
                                        <li class="nav-item">
                                            <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.audit.index') ? 'active' : ''); ?>"
                                                href="<?php echo e(route('admin.audit.index')); ?>" data-no-preloader>
                                                <i class="fas fa-check-double"></i><span>Audit Control</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.audit-logs.*') ? 'active' : ''); ?>"
                                                href="<?php echo e(route('admin.audit-logs.index')); ?>" data-no-preloader>
                                                <i class="fas fa-clipboard-list"></i><span>Activity Logs</span>
                                            </a>
                                        </li>
                                        <?php if(auth()->user()->hasPermission('reset_passwords')): ?>
                                            <li class="nav-item">
                                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.security.reset-password') ? 'active' : ''); ?>"
                                                    href="<?php echo e(route('admin.security.reset-password')); ?>" data-no-preloader>
                                                    <i class="fas fa-key"></i><span>Reset Password</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                <?php endif; ?>
                            </li>
                <?php endif; ?>

                
                
                <?php if(auth()->user()->hasPermission('manage_users') || auth()->user()->hasPermission('manage_roles')): ?>
                    <li class="nav-item has-submenu <?php echo e((request()->routeIs('admin.admins.*') || request()->routeIs('admin.roles-permissions.*')) ? 'active' : ''); ?>"
                        data-submenu-toggle>
                        <a class="nav-link d-flex align-items-center justify-content-between <?php echo e(request()->routeIs('admin.admins.*') || request()->routeIs('admin.roles-permissions.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.admins.index')); ?>" data-no-preloader>
                            <span class="d-flex align-items-center">
                                <i class="fas fa-users-cog"></i><span>Manage Users</span>
                            </span>
                            <i class="fas fa-chevron-right submenu-arrow"></i>
                        </a>
                        <ul class="nav-submenu">
                            <?php if(auth()->user()->hasPermission('manage_users')): ?>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.admins.*') ? 'active' : ''); ?>"
                                        href="<?php echo e(route('admin.admins.index')); ?>" data-no-preloader>
                                        <i class="fas fa-users"></i><span>Users</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if(auth()->user()->hasPermission('manage_roles')): ?>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.roles-permissions.*') ? 'active' : ''); ?>"
                                        href="<?php echo e(route('admin.roles-permissions.index')); ?>" data-no-preloader>
                                        <i class="fas fa-user-shield"></i><span>Roles</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                
                
                <?php if(auth()->user()->hasPermission('manage_settings')): ?>
                    <li class="nav-item has-submenu <?php echo e((request()->routeIs('admin.settings') || request()->routeIs('admin.settings.*')) ? 'active' : ''); ?>"
                        data-submenu-toggle>
                        <a class="nav-link d-flex align-items-center justify-content-between <?php echo e((request()->routeIs('admin.settings') || request()->routeIs('admin.settings.*')) ? 'active' : ''); ?>"
                            href="<?php echo e(route('admin.settings')); ?>" data-no-preloader>
                            <span class="d-flex align-items-center">
                                <i class="fas fa-cog"></i><span>Configurations</span>
                            </span>
                            <i class="fas fa-chevron-right submenu-arrow"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.settings') && !request()->routeIs('admin.settings.*') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.settings')); ?>" data-no-preloader>
                                    <i class="fas fa-cog"></i><span>Settings</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center <?php echo e(request()->routeIs('admin.settings.sms') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('admin.settings.sms')); ?>" data-no-preloader>
                                    <i class="fas fa-sms"></i><span>SMS</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Logout Button in Sidebar -->
        <div class="p-3 border-top border-white border-opacity-25 mt-auto flex-shrink-0"
            style="background: rgba(0, 0, 0, 0.2);">
            <form method="POST" action="<?php echo e(route('admin.logout')); ?>" id="logout-form">
                <?php echo csrf_field(); ?>
                <button type="submit" class="nav-link w-100 text-start d-flex align-items-center logout-btn"
                    data-no-global-handler
                    style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; cursor: pointer; transition: all 0.3s ease; font-weight: 600;">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="logout-text">Logout</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle Menu">
                    <i class="fas fa-bars"></i>
                </button>
                <img src="<?php echo e(asset('images/logo.webp')); ?>" alt="CHIBO BRAND" style="height: 40px; width: auto;"
                    onerror="this.style.display='none'">
            </div>

            <div class="user-info">
                <div class="d-flex align-items-center gap-3">
                    <?php
                        $unreadNotifications = auth()->user() ?
                            auth()->user()->notifications()->unread()->latest()->take(5)->get() : collect();
                        $unreadNotificationsCount = auth()->user() ? auth()->user()->notifications()->unread()->count() : 0;
                        $newMessages = \App\Models\ContactMessage::where('status', 'new')->latest()->take(5)->get();
                        $newMessagesCount = \App\Models\ContactMessage::where('status', 'new')->count();
                    ?>

                    <!-- Notifications Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-link position-relative p-2 text-decoration-none" type="button"
                            id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                            style="border-radius: 10px; transition: all 0.3s; background: rgba(0,0,0,0.03);">
                            <i class="fas fa-bell fa-lg text-dark"></i>
                            <?php if($unreadNotificationsCount > 0): ?>
                                <span class="position-absolute translate-middle badge rounded-pill bg-danger"
                                    style="top: 8px; right: -5px; border: 2px solid #fff; font-size: 0.65rem; min-width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; padding: 0;">
                                    <?php echo e($unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount); ?>

                                </span>
                            <?php endif; ?>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-0 border-0 shadow-lg mt-2"
                            aria-labelledby="notificationDropdown"
                            style="width: 380px; max-width: 90vw; border-radius: 16px; overflow: hidden; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); z-index: 9999;">
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light">
                                <h6 class="mb-0 fw-bold">Recent Activities</h6>
                                <div class="d-flex gap-2">
                                    <?php if($unreadNotificationsCount > 0): ?>
                                        <button type="button"
                                            class="btn btn-sm btn-link text-primary p-0 text-decoration-none small fw-bold mark-all-read-btn">
                                            Clear All
                                        </button>
                                    <?php endif; ?>
                                    <a href="<?php echo e(route('admin.notifications.index')); ?>" class="text-muted"
                                        title="View Hub">
                                        <i class="fas fa-external-link-alt small"></i>
                                    </a>
                                </div>
                            </div>

                            <div id="notificationList" style="max-height: 400px; overflow-y: auto;">
                                <?php $__empty_1 = true; $__currentLoopData = $unreadNotifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                            <?php
                                                                $isTask = str_contains(strtolower($notification->type ?? ''), 'task');
                                                                $isOrder = str_contains(strtolower($notification->type ?? ''), 'order');
                                                                $isUrgent = str_contains(strtolower($notification->type ?? ''), 'alert') ||
                                                                    str_contains(strtolower($notification->type ?? ''), 'warning');

                                                                $iconBoxClass = 'bg-primary-subtle text-primary';
                                                                $icon = 'fa-bell';

                                                                if ($isTask) {
                                                                    $iconBoxClass = 'bg-purple-subtle text-purple';
                                                                    $icon = 'fa-palette';
                                                                } elseif ($isOrder) {
                                                                    $iconBoxClass = 'bg-success-subtle text-success';
                                                                    $icon =
                                                                        'fa-shopping-bag';
                                                                } elseif ($isUrgent) {
                                                                    $iconBoxClass = 'bg-warning-subtle text-warning';
                                                                    $icon =
                                                                        'fa-exclamation-triangle';
                                                                }
                                                            ?>
                                                            <?php
                                                                // Generate URL for dropdown click
                                                                $url = route('admin.notifications.index');
                                                                if (
                                                                    $notification->related_type === 'App\Models\DesignTask' &&
                                                                    $notification->related_id
                                                                ) {
                                                                    $url = route('admin.design-tasks.show', $notification->related_id);
                                                                } elseif (str_contains(strtolower($notification->message), 'task')) {
                                                                    preg_match('/ID[:\s]+(\d+)/i', $notification->message, $matches);
                                                                    if (isset($matches[1]))
                                                                        $url = route('admin.design-tasks.show', $matches[1]);
                                                                }
                                                            ?>
                                                            <a href="<?php echo e($url); ?>"
                                                                class="text-decoration-none d-block p-3 border-bottom notification-item position-relative"
                                                                style="transition: background 0.2s;">
                                                                <div class="d-flex gap-3">
                                                                    <div class="flex-shrink-0">
                                                                        <div class="rounded-circle d-flex align-items-center justify-content-center <?php echo e($iconBoxClass); ?>"
                                                                            style="width: 42px; height: 42px; font-size: 1rem;">
                                                                            <i class="fas <?php echo e($icon); ?>"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="flex-grow-1 min-width-0">
                                                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                                                            <span class="fw-bold text-dark small text-truncate pe-3">
                                                                                <?php echo e(ucwords(str_replace(['_', '-'], ' ', $notification->type ??
                                    'Notification'))); ?>

                                                                            </span>
                                                                            <small class="text-muted flex-shrink-0" style="font-size: 0.7rem;">
                                                                                <?php echo e($notification->created_at->diffForHumans(null, true)); ?>

                                                                            </small>
                                                                        </div>
                                                                        <div class="text-muted small text-truncate-2"
                                                                            style="font-size: 0.8rem; line-height: 1.4;">
                                                                            <?php if($notification->sender): ?>
                                                                                <span
                                                                                    class="text-primary fw-bold"><?php echo e($notification->sender->name); ?>:</span>
                                                                            <?php endif; ?>
                                                                            <?php echo e($notification->message); ?>

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="p-5 text-center">
                                        <div class="mb-3">
                                            <i class="fas fa-check-circle text-success"
                                                style="font-size: 2.5rem; opacity: 0.5;"></i>
                                        </div>
                                        <p class="text-muted small mb-0">You're all caught up!</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <a href="<?php echo e(route('admin.notifications.index')); ?>"
                                class="d-block p-2 text-center text-primary small fw-bold bg-light text-decoration-none">
                                View Full Activity Log
                            </a>
                        </div>
                    </div>



                    <?php if((auth()->user()->role ?? null) === 'saler'): ?>
                        <!-- Share Links icon (Saler only) -->
                        <button class="btn btn-link p-2 text-decoration-none" type="button" data-bs-toggle="modal"
                            data-bs-target="#shareLinksModal" title="Share Links" aria-label="Share Links">
                            <i class="fas fa-share-alt" style="font-size: 1rem;"></i>
                        </button>
                    <?php endif; ?>

                    <!-- World / Landing Page -->
                    <a href="<?php echo e(route('home')); ?>" class="btn btn-link p-2 text-decoration-none"
                        title="Go to Landing Page" aria-label="Go to Landing Page">
                        <i class="fas fa-globe fa-lg text-dark"></i>
                    </a>

                    <!-- User Profile Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-link p-0 text-decoration-none d-flex align-items-center"
                            id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar">
                                <?php echo e(substr(auth()->user()->name ?? 'A', 0, 1)); ?>

                            </div>
                            <div class="ms-2 user-name">
                                <div class="fw-bold"><?php echo e(auth()->user()->name ?? 'Admin'); ?></div>
                            </div>
                            <i class="fas fa-caret-down ms-2 text-muted"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuDropdown">
                            <li>
                                <a class="dropdown-item" href="<?php echo e(route('admin.profile')); ?>">
                                    <i class="fas fa-user me-2 text-muted"></i> Profile
                                </a>
                            </li>

                            <?php if((auth()->user()->role ?? null) === 'super_admin'): ?>
                                <li>
                                    <a class="dropdown-item" href="<?php echo e(route('admin.settings')); ?>">
                                        <i class="fas fa-cog me-2 text-muted"></i> Settings
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="<?php echo e(route('admin.logout')); ?>" class="px-3 m-0">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-link dropdown-item text-danger p-0"
                                        data-no-global-handler>
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">


            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        window.adminConfig = {
            routes: {
                notificationsMarkAllRead: "<?php echo e(route('admin.notifications.mark-all-read')); ?>",
                notificationsIndex: "<?php echo e(route('admin.notifications.index')); ?>",
                notificationsRefreshCount: "<?php echo e(route('admin.notifications.refresh-count')); ?>"
            }
        };
    </script>
    <script src="<?php echo e(asset('js/admin.js')); ?>?v=<?php echo e(filemtime(public_path('js/admin.js'))); ?>"></script>

    <!-- Initialize Session Notifications -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            <?php if(session('success')): ?>
                showNotification(<?php echo json_encode(session('success'), 15, 512) ?>, 'success');
            <?php endif; ?>
            <?php if(session('error')): ?>
                showNotification(<?php echo json_encode(session('error'), 15, 512) ?>, 'error');
            <?php endif; ?>
            <?php if(session('warning')): ?>
                showNotification(<?php echo json_encode(session('warning'), 15, 512) ?>, 'warning');
            <?php endif; ?>
            <?php if(session('info')): ?>
                showNotification(<?php echo json_encode(session('info'), 15, 512) ?>, 'info');
            <?php endif; ?>
        });
    </script>
    <script>
        function printDirect(url) {
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = url + (url.includes('?') ? '&' : '?') + 'print=true';
            document.body.appendChild(iframe);

            if (typeof showNotification === 'function') {
                showNotification('Preparing document for print...', 'info');
            }

            iframe.onload = function () {
                setTimeout(() => {
                    if (iframe.contentWindow) {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    }
                }, 500);
            };
        }

        // Modern Confirmation Dialog
        function modernConfirm(message, onConfirm, options = {}) {
            const title = options.title || 'Are you sure?';
            const confirmText = options.confirmText || 'Yes, proceed';
            const cancelText = options.cancelText || 'Cancel';
            const type = options.type || 'primary'; // primary, danger, success, warning

            // Create modal elements
            const modalId = 'modernConfirmModal' + Date.now();
            const modalHtml = `
                <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-sm">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-body p-4 text-center">
                                <div class="mb-3">
                                    <i class="fas fa-exclamation-circle fa-3x text-${type}"></i>
                                </div>
                                <h5 class="fw-bold mb-2">${title}</h5>
                                <p class="text-muted mb-4" style="font-size: 14px;">${message}</p>
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">${cancelText}</button>
                                    <button type="button" class="btn btn-${type} px-4" id="confirmBtn${modalId}">${confirmText}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modalElement = document.getElementById(modalId);
            const modal = new bootstrap.Modal(modalElement);

            modalElement.querySelector(`#confirmBtn${modalId}`).addEventListener('click', () => {
                modal.hide();
                if (typeof onConfirm === 'function') onConfirm();
                setTimeout(() => modalElement.remove(), 500);
            });

            modalElement.addEventListener('hidden.bs.modal', () => {
                setTimeout(() => modalElement.remove(), 500);
            });

            modal.show();
        }
    </script>
    <!-- Global Share PDF: any .share-pdf-btn with data-pdf-url (and optional data-pdf-filename) opens native share or new tab -->
    <script>
        (function () {
            function resetSharePdfBtn(btn, icon) {
                if (btn.disabled !== undefined) btn.disabled = false;
                btn.style.pointerEvents = '';
                if (btn.removeAttribute) btn.removeAttribute('aria-busy');
                if (icon) { icon.classList.remove('fa-spinner', 'fa-spin'); icon.classList.add('fa-share-alt'); }
            }
            document.addEventListener('click', function (e) {
                var btn = e.target.closest('.share-pdf-btn');
                if (!btn) return;
                e.preventDefault();
                e.stopPropagation();
                var url = (btn.getAttribute('data-pdf-url') || btn.getAttribute('href') || '').trim();
                var filename = btn.getAttribute('data-pdf-filename') || 'report.pdf';
                if (!url || url === '#' || url.indexOf('javascript') === 0) return;
                var icon = btn.querySelector('i');
                if (icon) { icon.classList.remove('fa-share-alt'); icon.classList.add('fa-spinner', 'fa-spin'); }
                if (btn.disabled !== undefined) btn.disabled = true;
                btn.style.pointerEvents = 'none';
                if (btn.setAttribute) btn.setAttribute('aria-busy', 'true');
                fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/pdf' } })
                    .then(function (r) {
                        if (!r.ok) { window.open(url, '_blank'); return null; }
                        return r.blob();
                    })
                    .then(function (blob) {
                        if (!blob) { resetSharePdfBtn(btn, icon); return; }
                        var file = new File([blob], filename, { type: 'application/pdf' });
                        var canShare = navigator.share && (typeof navigator.canShare !== 'undefined' ? navigator.canShare({ files: [file] }) : true);
                        if (canShare) {
                            navigator.share({ title: 'Report', text: 'Report (CHIBO BRANDS)', files: [file] })
                                .catch(function (err) { if (err.name !== 'AbortError') window.open(url, '_blank'); });
                        } else {
                            window.open(url, '_blank');
                        }
                    })
                    .catch(function () { window.open(url, '_blank'); })
                    .finally(function () { resetSharePdfBtn(btn, icon); });
            }, true);
        })();
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>



    <!-- Real-time Notification Sound -->
    <audio id="notificationSound" preload="auto">
        <source src="<?php echo e(asset('notification.wav')); ?>" type="audio/wav">
    </audio>



    <!-- Beautiful Notification System -->
    <div id="notificationContainer" style="position: fixed; top: 20px; right: 20px; z-index: 99999; max-width: 400px;">
    </div>




    <?php
        $salerPhoneRaw = auth()->user()->phone ?? '';
        $salerPhoneDigits = preg_replace('/[^\d\+]/', '', $salerPhoneRaw);
        $salerPhoneForWa = ltrim($salerPhoneDigits, '+');
        $hasSalerPhone = !empty($salerPhoneForWa);
        $retailLink = url('/shop') . ($hasSalerPhone ? ('?saler=+' . $salerPhoneForWa) : '');
        $wholesaleLink = url('/b2b/shop') . ($hasSalerPhone ? ('?saler=+' . $salerPhoneForWa) : '');
        $waTextRetail = urlencode('Hi, please view products here: ' . $retailLink);
        $waTextWholesale = urlencode('Hi, please view B2B products here: ' . $wholesaleLink);
    ?>

    <?php if((auth()->user()->role ?? null) === 'saler'): ?>
        <!-- Share Links Modal (Saler only) -->
        <div class="modal fade" id="shareLinksModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-share-alt me-2"></i>Share Your Links</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if(!$hasSalerPhone): ?>
                            <div id="salerPhoneWarning" class="alert alert-warning d-flex align-items-center" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Please add your WhatsApp number in Profile to enable share links.
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Retail Landing Link</label>
                            <div class="input-group">
                                <input id="retailShareLink" type="text" class="form-control" readonly
                                    value="<?php echo e($retailLink); ?>">
                                <button class="btn btn-outline-secondary" type="button" data-copy-target="#retailShareLink">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <?php if($hasSalerPhone): ?>
                                    <a class="btn btn-success" target="_blank"
                                        href="https://wa.me/<?php echo e($salerPhoneForWa); ?>?text=<?php echo e($waTextRetail); ?>">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted">Share this link with customers to send orders directly to your
                                WhatsApp.</small>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-semibold">Wholesale Landing Link</label>
                            <div class="input-group">
                                <input id="wholesaleShareLink" type="text" class="form-control" readonly
                                    value="<?php echo e($wholesaleLink); ?>">
                                <button class="btn btn-outline-secondary" type="button"
                                    data-copy-target="#wholesaleShareLink">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <?php if($hasSalerPhone): ?>
                                    <a class="btn btn-success" target="_blank"
                                        href="https://wa.me/<?php echo e($salerPhoneForWa); ?>?text=<?php echo e($waTextWholesale); ?>">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted">Use this for B2B customers.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</body>

</html><?php /**PATH /Users/gotlaptopparts.com/Desktop/LaravelProject/chibo_sales/resources/views/layouts/admin.blade.php ENDPATH**/ ?>