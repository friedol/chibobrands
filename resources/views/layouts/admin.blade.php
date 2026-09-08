<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>@yield('title', 'CHIBO BRANDS Co. LTD')</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ url('favicon.ico') }}">
    <link rel="icon" type="image/x-icon" href="{{ url('favicon.ico') }}">

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

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
    <style>
        /* Enforce consistent app typography */
        body {
            font-family: 'Nunito Sans', sans-serif !important;
        }

        .x-small {
            font-size: 0.75rem;
        }

        /* Sidebar Overrides - White Background with Red Active State */
        .sidebar {
            background: #ffffff !important;
            color: #333 !important;
            border-right: 1px solid rgba(0, 0, 0, 0.05) !important;
        }

        .sidebar .nav-link {
            padding: 0.6rem 0.75rem !important;
            margin: 2px 6px !important;
            border-radius: 8px !important;
            transition: all 0.2s ease;
            color: #555 !important;
        }

        .sidebar .nav-link:hover {
            color: #111 !important;
            background: rgba(0, 0, 0, 0.03) !important;
        }

        .sidebar .nav-link.active {
            color: #dc2626 !important;
            background: rgba(220, 38, 38, 0.1) !important;
            /* Transparent Red */
            box-shadow: none !important;
            font-weight: 600 !important;
        }

        .sidebar .nav-link i {
            color: #666;
        }

        .sidebar .nav-link:hover i {
            color: #111 !important;
        }

        .sidebar .nav-link.active i {
            color: #dc2626 !important;
        }

        .sidebar-header h4 {
            color: #111 !important;
        }

        .sidebar-header .sidebar-brand-logo {
            height: calc(var(--app-header-height) - 14px);
            max-height: 40px;
            width: auto;
            max-width: 150px;
            object-fit: contain;
            display: block;
        }

        .sidebar.minimized .sidebar-header .sidebar-brand-logo {
            height: 28px;
            max-width: 54px;
        }

        .sidebar-header .subtitle {
            color: #666 !important;
        }

        .submenu-arrow {
            color: #666 !important;
        }

        .sidebar .nav-link.active .submenu-arrow {
            color: #dc2626 !important;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        /* ── Sidebar user profile strip ── */
        .sidebar-user-strip {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px 10px;
            background: rgba(0, 0, 0, 0.03);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            margin-bottom: 6px;
        }

        .sidebar-user-avatar {
            width: 36px;
            height: 36px;
            background: rgba(0, 0, 0, 0.08);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
            color: #333;
            flex-shrink: 0;
            border: 1.5px solid rgba(0, 0, 0, 0.1);
        }

        .sidebar-user-name {
            font-size: 0.82rem;
            font-weight: 700;
            color: #333;
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 140px;
        }

        .sidebar-user-role {
            font-size: 0.68rem;
            color: rgba(0, 0, 0, 0.5);
            text-transform: capitalize;
            margin-top: 1px;
        }

        /* ── Section labels ── */
        padding: 0.85rem 1rem 0.2rem;
        font-size: 0.6rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.35);
        list-style: none;
        margin-top: 2px;
        pointer-events: none;
        user-select: none;
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
    <style>
        /* ── Global Dash Stat Card ── */
        .dash-stat-card {
            border-radius: 14px;
            padding: 14px 14px 12px;
            background: #fff;
            border: 1.5px solid rgba(0, 0, 0, 0.08);
            display: block;
            height: 100%;
            box-sizing: border-box;
        }

        .dsc-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .dsc-trend {
            font-size: 0.68rem;
            color: #94a3b8;
            font-weight: 500;
            white-space: nowrap;
        }

        .dsc-value {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
            word-break: break-word;
            margin-top: 12px;
        }

        .dsc-label {
            font-size: 0.71rem;
            color: #94a3b8;
            margin-top: 3px;
            font-weight: 500;
        }

        .hover-lift {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.1) !important;
        }

        @media (max-width: 768px) {
            .dsc-value {
                font-size: 0.9rem;
            }

            .dsc-icon {
                width: 32px;
                height: 32px;
                font-size: 0.8rem;
            }

            .dash-stat-card {
                padding: 11px;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Preloader -->
    <div id="preloader">
        <div class="preloader-content">
            <div class="preloader-logo-wrapper">
                <img src="{{ asset('images/round.webp') }}" alt="Loading..." class="preloader-logo">
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="text-start ps-2">
                    <img src="{{ asset('images/logo.webp') }}" alt="CHIBO BRAND Logo"
                        class="sidebar-brand-logo"
                        onerror="this.style.display='none';">
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            @php
                $u = auth()->user();
                $userRole = $u->role ?? null;

                $isAdmin = in_array($userRole, ['super_admin', 'admin']);
                $isManager = in_array($userRole, ['super_admin', 'admin', 'manager']);
                $isFinance = in_array($userRole, ['super_admin', 'admin', 'manager', 'accountant']);

                $dashRoute = match ($userRole) {
                    'accountant' => 'admin.finance.dashboard',
                    'gatekeeper' => 'gatekeeper.dashboard',
                    'saler' => 'admin.saler.my-dashboard',
                    'hr_officer' => 'admin.hr.index',
                    'marketing_manager' => 'admin.marketing.dashboard',
                    default => 'admin.dashboard',
                };

                // Compute contact-message badge once
                $sidebarMsgCount = 0;
                if ($u->hasPermission('view_contact_messages')) {
                    if ($isManager) {
                        $sidebarMsgCount = \App\Models\ContactMessage::where('status', 'new')->count();
                    } else {
                        $uEmail = $u->email ?? '';
                        $uPhone = preg_replace('/[^\d+]/', '', $u->phone ?? '');
                        $sidebarMsgCount = \App\Models\ContactMessage::where('status', 'new')
                            ->where(function ($q) use ($uEmail, $uPhone) {
                                if ($uEmail)
                                    $q->where('email', $uEmail);
                                if ($uPhone)
                                    $q->orWhere('phone', 'LIKE', '%' . $uPhone . '%');
                            })->count();
                    }
                }

                $sidebarUnread = $u ? $u->unreadNotifications()->count() : 0;
            @endphp

            <ul class="nav flex-column">

                {{-- ══════════════════════════════════════
                OVERVIEW
                ══════════════════════════════════════ --}}
                @if(
                        in_array($userRole, [
                            'admin',
                            'super_admin',
                            'manager',
                            'receptionist',
                            'designer',
                            'operator',
                            'accountant',
                            'gatekeeper',
                            'delivery',
                            'saler',
                            'marketing_manager',
                            'hr_officer',
                        ])
                    )
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs($dashRoute) ? 'active' : '' }}"
                            href="{{ route($dashRoute) }}" data-no-preloader>
                            <i class="fas fa-tachometer-alt" style="color: #64748b;"></i><span>Dashboard</span>
                        </a>
                    </li>
                @endif

                {{-- Notifications - Always visible to all users --}}
                {{-- Operations & Tasks --}}
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.design-tasks.*') && !request()->routeIs('admin.design-tasks.reports') ? 'active' : '' }}"
                        href="{{ route('admin.design-tasks.index') }}" data-no-preloader>
                        <i class="fas fa-paint-brush" style="color: #8b5cf6;"></i><span>Design Tasks</span>
                    </a>
                </li>

                {{-- ══════════════════════════════════════
                SALES & RETAIL
                ══════════════════════════════════════ --}}
                @if(auth()->user()->hasPermission('manage_orders'))
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
                            href="{{ route('admin.orders.index') }}" data-no-preloader>
                            <i class="fas fa-shopping-cart" style="color: #10b981;"></i><span>Online Orders</span>
                        </a>
                    </li>
                @endif
                @if(auth()->user()->hasPermission('manage_pos'))
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.pos.*') ? 'active' : '' }}"
                            href="{{ route('admin.pos.index') }}" data-no-preloader>
                            <i class="fas fa-cash-register" style="color: #10b981;"></i><span>POS Terminal</span>
                        </a>
                    </li>
                @endif

                {{-- ══════════════════════════════════════
                INVENTORY
                ══════════════════════════════════════ --}}
                @if(($isManager || $u->hasPermission('manage_products')) && !in_array($userRole, ['receptionist', 'operator']))
                    @php
                        $inventoryMenuActive = (request()->routeIs('admin.enhanced-products.*') && !request()->routeIs('admin.enhanced-products.offers*') && !request()->routeIs('admin.categories.*'))
                            || request()->routeIs('admin.products.*')
                            || request()->routeIs('admin.hero-slides.*');
                    @endphp
                    <li class="nav-item has-submenu {{ $inventoryMenuActive ? 'active' : '' }}" data-submenu-toggle>
                        <a class="nav-link d-flex align-items-center justify-content-between"
                            href="{{ route('admin.enhanced-products.index') }}" data-no-preloader>
                            <span class="d-flex align-items-center">
                                <i class="fas fa-boxes" style="color: #10b981;"></i><span>Inventory</span>
                            </span>
                            <i class="fas fa-chevron-right submenu-arrow"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ (request()->routeIs('admin.enhanced-products.index') && !request()->has('stock_status') && !request()->routeIs('admin.enhanced-products.offers*')) ? 'active' : '' }}"
                                    href="{{ route('admin.enhanced-products.index') }}" data-no-preloader>
                                    <i class="fas fa-box" style="color: #10b981;"></i><span>All Products</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.enhanced-products.index') && request('stock_status') === 'in_stock' ? 'active' : '' }}"
                                    href="{{ route('admin.enhanced-products.index', ['stock_status' => 'in_stock']) }}"
                                    data-no-preloader>
                                    <i class="fas fa-check-circle" style="color: #10b981;"></i><span>In Stock</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.enhanced-products.index') && request('stock_status') === 'out_of_stock' ? 'active' : '' }}"
                                    href="{{ route('admin.enhanced-products.index', ['stock_status' => 'out_of_stock']) }}"
                                    data-no-preloader>
                                    <i class="fas fa-times-circle" style="color: #ef4444;"></i><span>Out of Stock</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.hero-slides.*') ? 'active' : '' }}"
                                    href="{{ route('admin.hero-slides.index') }}" data-no-preloader>
                                    <i class="fas fa-images" style="color: #10b981;"></i><span>Hero Slides</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- ══════════════════════════════════════
                PRODUCT
                ══════════════════════════════════════ --}}
                @if(($isManager || $u->hasPermission('manage_products')) && !in_array($userRole, ['receptionist', 'operator']))
                    @php
                        $productMenuActive = request()->routeIs('admin.categories.*')
                            || request()->routeIs('admin.enhanced-products.offers*')
                            || request()->routeIs('admin.design-task-types.*');
                    @endphp
                    <li class="nav-item has-submenu {{ $productMenuActive ? 'active' : '' }}" data-submenu-toggle>
                        <a class="nav-link d-flex align-items-center justify-content-between"
                            href="{{ route('admin.enhanced-products.index') }}" data-no-preloader>
                            <span class="d-flex align-items-center">
                                <i class="fas fa-box-open" style="color: #f59e0b;"></i><span>Product</span>
                            </span>
                            <i class="fas fa-chevron-right submenu-arrow"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ (request()->routeIs('admin.enhanced-products.index') && !request()->routeIs('admin.enhanced-products.offers*') && !request()->has('stock_status')) ? 'active' : '' }}"
                                    href="{{ route('admin.enhanced-products.index') }}">
                                    <i class="fas fa-list" style="color: #f59e0b;"></i><span>All List</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
                                    href="{{ route('admin.categories.index') }}">
                                    <i class="fas fa-tags" style="color: #f59e0b;"></i><span>Categories</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" href="#">
                                    <i class="fas fa-ruler-combined" style="color: #f59e0b;"></i><span>Unit</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.enhanced-products.offers*') ? 'active' : '' }}"
                                    href="{{ route('admin.enhanced-products.offers') }}">
                                    <i class="fas fa-percentage" style="color: #f59e0b;"></i><span>Offers</span>
                                </a>
                            </li>
                            @if($u->hasPermission('manage_design_tasks') && in_array($userRole, ['super_admin', 'admin', 'manager', 'accountant']))
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.design-task-types.*') ? 'active' : '' }}"
                                        href="{{ route('admin.design-task-types.index') }}" data-no-preloader>
                                        <i class="fas fa-layer-group" style="color: #f59e0b;"></i><span>Task Type</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- ══════════════════════════════════════
                CUSTOMERS
                ══════════════════════════════════════ --}}
                @if(auth()->user()->hasPermission('manage_customers') || auth()->user()->hasPermission('manage_leads'))
                    @php
                        $customersActive = request()->routeIs('admin.customers.*')
                            || request()->routeIs('admin.customer-data-center.*')
                            || request()->routeIs('admin.customers.map')
                            || request()->routeIs('admin.auto-followup.*');

                        $leadsActive = request()->routeIs('admin.leads.*')
                            || request()->routeIs('admin.message-templates.*')
                            || request()->routeIs('admin.saler.sales-report*');
                    @endphp
                    @if(auth()->user()->hasPermission('manage_customers'))
                        <li class="nav-item has-submenu {{ $customersActive ? 'active' : '' }}" data-submenu-toggle>
                            <a class="nav-link d-flex align-items-center justify-content-between {{ $customersActive ? 'active' : '' }}"
                                href="{{ route('admin.customers.index') }}" data-no-preloader>
                                <span class="d-flex align-items-center">
                                    <i class="fas fa-users" style="color: #ec4899;"></i><span>Customers</span>
                                </span>
                                <i class="fas fa-chevron-right submenu-arrow"></i>
                            </a>
                            <ul class="nav-submenu">
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.customers.index') ? 'active' : '' }}"
                                        href="{{ route('admin.customers.index') }}" data-no-preloader>
                                        <i class="fas fa-list" style="color: #ec4899;"></i><span>All List</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.customers.create') ? 'active' : '' }}"
                                        href="{{ route('admin.customers.create') }}" data-no-preloader>
                                        <i class="fas fa-user-plus" style="color: #ec4899;"></i><span>Add New</span>
                                    </a>
                                </li>
                                @if(!in_array($userRole, ['accountant']))
                                    <li class="nav-item">
                                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.customers.map') ? 'active' : '' }}"
                                            href="{{ route('admin.customers.map') }}">
                                            <i class="fas fa-map-marked-alt text-success"></i><span>Map</span>
                                        </a>
                                    </li>
                                @endif
                                @if(in_array($userRole, ['saler', 'admin', 'super_admin', 'manager']))
                                    <li class="nav-item">
                                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.auto-followup.*') ? 'active' : '' }}"
                                            href="{{ route('admin.auto-followup.index') }}" data-no-preloader>
                                            <i class="fas fa-sync-alt" style="color:#f59e0b;"></i><span>Auto Follow-up</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if(auth()->user()->hasPermission('manage_leads'))
                        <li class="nav-item has-submenu {{ $leadsActive ? 'active' : '' }}" data-submenu-toggle>
                            <a class="nav-link d-flex align-items-center justify-content-between {{ $leadsActive ? 'active' : '' }}"
                                href="{{ route('admin.leads.index') }}">
                                <span class="d-flex align-items-center">
                                    <i class="fas fa-funnel-dollar" style="color: #a855f7;"></i><span>Leads/Follow-up</span>
                                </span>
                                <i class="fas fa-chevron-right submenu-arrow"></i>
                            </a>
                            <ul class="nav-submenu">
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.leads.index') ? 'active' : '' }}"
                                        href="{{ route('admin.leads.index') }}">
                                        <i class="fas fa-funnel-dollar" style="color: #a855f7;"></i><span>
                                            Follow-Up</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.leads.overdue') ? 'active' : '' }}"
                                        href="{{ route('admin.leads.overdue') }}">
                                        <i class="fas fa-exclamation-circle" style="color: #ef4444;"></i><span>Overdue
                                            Leads</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.saler.sales-report*') ? 'active' : '' }}"
                                        href="{{ route('admin.saler.sales-report') }}">
                                        <i class="fas fa-chart-line" style="color: #0ea5e9;"></i><span>Saler Report</span>
                                    </a>
                                </li>
                                @if(!in_array($userRole, ['accountant']))
                                    <li class="nav-item">
                                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.message-templates.*') ? 'active' : '' }}"
                                            href="{{ route('admin.message-templates.index') }}" data-no-preloader>
                                            <i class="fas fa-comment-dots" style="color: #ec4899;"></i><span>Send Campaign</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                @endif

                {{-- Finance Module --}}
                @if(auth()->user()->hasPermission('manage_finance'))
                    <li class="nav-item has-submenu {{ ((request()->routeIs('admin.finance.*') && !request()->routeIs('admin.finance.reports') && !request()->routeIs('admin.finance.daily-report') && !request()->routeIs('admin.finance.departments.*')) || request()->routeIs('admin.sales-dept.targets*')) ? 'active' : '' }}"
                        data-submenu-toggle>
                        <a class="nav-link d-flex align-items-center justify-content-between {{ ((request()->routeIs('admin.finance.*') && !request()->routeIs('admin.finance.reports') && !request()->routeIs('admin.finance.daily-report') && !request()->routeIs('admin.finance.departments.*')) || request()->routeIs('admin.sales-dept.targets*')) ? 'active' : '' }}"
                            href="{{ route('admin.finance.dashboard') }}" data-no-preloader>
                            <span class="d-flex align-items-center">
                                <i class="fas fa-wallet" style="color: #eab308;"></i><span>Finance</span>
                            </span>
                            <i class="fas fa-chevron-right submenu-arrow"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.finance.dashboard') ? 'active' : '' }}"
                                    href="{{ route('admin.finance.dashboard') }}">
                                    <i class="fas fa-chart-line" style="color: #64748b;"></i><span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.finance.pending-payments') ? 'active' : '' }}"
                                    href="{{ route('admin.finance.pending-payments') }}">
                                    <i class="fas fa-hand-holding-usd" style="color: #f59e0b;"></i><span>Debts
                                        (Unbalances)</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.finance.payment-requests.*') ? 'active' : '' }}"
                                    href="{{ route('admin.finance.payment-requests.index') }}">
                                    <i class="fas fa-paper-plane" style="color: #f59e0b;"></i><span>Pending Requests</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.finance.expenses') ? 'active' : '' }}"
                                    href="{{ route('admin.finance.expenses') }}">
                                    <i class="fas fa-file-invoice-dollar" style="color: #ef4444;"></i><span>Expenses</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.finance.payroll') ? 'active' : '' }}"
                                    href="{{ route('admin.finance.payroll') }}">
                                    <i class="fas fa-money-check-alt" style="color: #10b981;"></i><span>Payroll</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.finance.proforma.index') ? 'active' : '' }}"
                                    href="{{ route('admin.finance.proforma.index') }}">
                                    <i class="fas fa-file-alt" style="color: #3b82f6;"></i><span>Proforma Invoices</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.finance.cash-flow') ? 'active' : '' }}"
                                    href="{{ route('admin.finance.cash-flow') }}">
                                    <i class="fas fa-exchange-alt" style="color: #0d9488;"></i><span>Cash Flow</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.finance.profit-loss') ? 'active' : '' }}"
                                    href="{{ route('admin.finance.profit-loss') }}">
                                    <i class="fas fa-balance-scale" style="color: #4f46e5;"></i><span>Profit & Loss</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.finance.balance-sheet') ? 'active' : '' }}"
                                    href="{{ route('admin.finance.balance-sheet') }}">
                                    <i class="fas fa-file-invoice" style="color: #7c3aed;"></i><span>Balance Sheet</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.finance.audit') ? 'active' : '' }}"
                                    href="{{ route('admin.finance.audit') }}">
                                    <i class="fas fa-calculator" style="color: #10b981;"></i><span>Audit Logs</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.sales-dept.targets*') ? 'active' : '' }}"
                                    href="{{ route('admin.sales-dept.targets') }}">
                                    <i class="fas fa-bullseye" style="color: #ec4899;"></i><span>Sales Target</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.sales.programs.*') ? 'active' : '' }}"
                                    href="{{ route('admin.sales.programs.index') }}">
                                    <i class="fas fa-layer-group" style="color: #f59e0b;"></i><span>Inside Programs</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.finance.verification-dashboard') ? 'active' : '' }}"
                                    href="{{ route('admin.finance.verification-dashboard') }}">
                                    <i class="fas fa-shield-alt text-danger"></i>
                                    <span>Verification</span>
                                    @php
                                        $mismatchCount = \Illuminate\Support\Facades\Cache::remember('finance_mismatch_count', 300, function () {
                                            return \Illuminate\Support\Facades\DB::table('payments as p')
                                                ->join('design_tasks as dt', 'dt.id', '=', 'p.design_task_id')
                                                ->where('p.is_debt', true)
                                                ->where('p.debt_status', 'pending')
                                                ->where('dt.balance', '<=', 0)
                                                ->count();
                                        });
                                    @endphp
                                    @if($mismatchCount > 0)
                                        <span class="badge ms-auto bg-danger"
                                            style="font-size:.6rem;">{{ $mismatchCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.finance.reconciliation.*') ? 'active' : '' }}"
                                    href="{{ route('admin.finance.reconciliation.index') }}">
                                    <i class="fas fa-balance-scale text-primary"></i><span>Reconciliation</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.finance.finance-audit-trail.*') ? 'active' : '' }}"
                                    href="{{ route('admin.finance.finance-audit-trail.index') }}">
                                    <i class="fas fa-history text-info"></i><span>Finance Audit Trail</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.finance.zoho-comparison') ? 'active' : '' }}"
                                    href="{{ route('admin.finance.zoho-comparison') }}">
                                    <i class="fas fa-code-branch text-warning"></i><span>Zoho Comparison</span>
                                </a>
                            </li>


                        </ul>
                    </li>
                @endif

                {{-- HR Module --}}
                @if(auth()->user()->hasPermission('manage_hr'))
                    <li class="nav-item has-submenu {{ request()->routeIs('admin.hr.*') ? 'active' : '' }}"
                        data-submenu-toggle>
                        <a class="nav-link d-flex align-items-center justify-content-between" href="#" data-no-preloader>
                            <span class="d-flex align-items-center">
                                <i class="fas fa-user-tie" style="color: #10b981;"></i><span>HR Module</span>
                            </span>
                            <i class="fas fa-chevron-right submenu-arrow"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.hr.index') ? 'active' : '' }}"
                                    href="{{ route('admin.hr.index') }}">
                                    <i class="fas fa-id-card" style="color: #10b981;"></i><span>Employees</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.hr.attendance') ? 'active' : '' }}"
                                    href="{{ route('admin.hr.attendance') }}">
                                    <i class="fas fa-clock" style="color: #10b981;"></i><span>Attendance</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.hr.leaves') ? 'active' : '' }}"
                                    href="{{ route('admin.hr.leaves') }}">
                                    <i class="fas fa-calendar-times" style="color: #10b981;"></i><span>Leave Requests</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.hr.kpis') ? 'active' : '' }}"
                                    href="{{ route('admin.hr.kpis') }}">
                                    <i class="fas fa-star" style="color: #10b981;"></i><span>KPI Evaluations</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- Gatekeeper System --}}
                @if(auth()->user()->hasPermission('manage_gatekeeper'))
                    <li class="nav-item has-submenu {{ request()->routeIs('gatekeeper.*') ? 'active' : '' }}"
                        data-submenu-toggle>
                        <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('gatekeeper.*') ? 'active' : '' }}"
                            href="{{ route('gatekeeper.dashboard') }}" data-no-preloader>
                            <span class="d-flex align-items-center">
                                <i class="fas fa-door-open" style="color: #4f46e5;"></i><span>Gatekeeper</span>
                            </span>
                            <i class="fas fa-chevron-right submenu-arrow"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('gatekeeper.dashboard') ? 'active' : '' }}"
                                    href="{{ route('gatekeeper.dashboard') }}">
                                    <i class="fas fa-chart-pie" style="color: #4f46e5;"></i><span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('gatekeeper.deliver') ? 'active' : '' }}"
                                    href="{{ route('gatekeeper.deliver') }}">
                                    <i class="fas fa-box-open" style="color: #4f46e5;"></i><span>Mark Delivery</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('gatekeeper.movements.*') ? 'active' : '' }}"
                                    href="{{ route('gatekeeper.movements.index') }}">
                                    <i class="fas fa-exchange-alt" style="color: #4f46e5;"></i><span>Product
                                        Movements</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- ══════════════════════════════════════
                LOGISTICS & DELIVERY
                ══════════════════════════════════════ --}}
                @php
                    $canSeeDeliveryUpdates = (auth()->user()->hasPermission('manage_inventory') || auth()->user()->hasPermission('manage_products')) && $userRole !== 'saler';
                    $canSeeDeliveryCore = auth()->user()->hasPermission('manage_delivery');
                @endphp

                @if($canSeeDeliveryCore || $canSeeDeliveryUpdates)
                    <li class="nav-item has-submenu {{ request()->routeIs('admin.delivery.*') ? 'active' : '' }}"
                        data-submenu-toggle>
                        <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('admin.delivery.*') ? 'active' : '' }}"
                            href="#" data-no-preloader>
                            <span class="d-flex align-items-center">
                                <i class="fas fa-shipping-fast" style="color: #06b6d4;"></i><span>Delivery</span>
                            </span>
                            <i class="fas fa-chevron-right submenu-arrow"></i>
                        </a>
                        <ul class="nav-submenu">
                            @if($canSeeDeliveryCore)
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ (request()->routeIs('admin.dashboard') && request()->get('view') === 'delivery') ? 'active' : '' }}"
                                        href="{{ route('admin.dashboard', ['view' => 'delivery']) }}">
                                        <i class="fas fa-chart-pie" style="color: #06b6d4;"></i><span>Dashboard</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.delivery.incoming') ? 'active' : '' }}"
                                        href="{{ route('admin.delivery.incoming') }}">
                                        <i class="fas fa-inbox" style="color: #06b6d4;"></i><span>Incoming</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.delivery.completed') ? 'active' : '' }}"
                                        href="{{ route('admin.delivery.completed') }}">
                                        <i class="fas fa-check-circle text-success"></i><span>Completed</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.delivery.canceled') ? 'active' : '' }}"
                                        href="{{ route('admin.delivery.canceled') }}">
                                        <i class="fas fa-times-circle text-danger"></i><span>Canceled</span>
                                    </a>
                                </li>
                            @endif
                            @if($canSeeDeliveryUpdates)
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.delivery.updates') ? 'active' : '' }}"
                                        href="{{ route('admin.delivery.updates') }}">
                                        <i class="fas fa-shipping-fast" style="color: #06b6d4;"></i><span>Delivery
                                            Updates</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                {{-- ══════════════════════════════════════
                MARKETING MODULE
                ══════════════════════════════════════ --}}
                @if(auth()->user()->hasPermission('manage_marketing'))
                    <li class="nav-item has-submenu {{ request()->routeIs('admin.marketing.*') ? 'active' : '' }}"
                        data-submenu-toggle>
                        <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('admin.marketing.*') ? 'active' : '' }}"
                            href="{{ route('admin.marketing.dashboard') }}" data-no-preloader>
                            <span class="d-flex align-items-center">
                                <i class="fas fa-bullhorn" style="color: #f43f5e;"></i><span>Marketing</span>
                            </span>
                            <i class="fas fa-chevron-right submenu-arrow"></i>
                        </a>
                        <ul class="nav-submenu">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.marketing.dashboard') ? 'active' : '' }}"
                                    href="{{ route('admin.marketing.dashboard') }}">
                                    <i class="fas fa-chart-line" style="color: #f43f5e;"></i><span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.marketing.product-penetration.*') ? 'active' : '' }}"
                                    href="{{ route('admin.marketing.product-penetration.index') }}">
                                    <i class="fas fa-bullseye" style="color: #f43f5e;"></i><span>Product Penetration</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.marketing.theme-events.*') ? 'active' : '' }}"
                                    href="{{ route('admin.marketing.theme-events.index') }}">
                                    <i class="fas fa-calendar-day" style="color: #f43f5e;"></i><span>Theme Events</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.marketing.campaigns.*') ? 'active' : '' }}"
                                    href="{{ route('admin.marketing.campaigns.index') }}">
                                    <i class="fas fa-flag" style="color: #f43f5e;"></i><span>Campaigns</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.marketing.calendar.*') ? 'active' : '' }}"
                                    href="{{ route('admin.marketing.calendar.index') }}">
                                    <i class="fas fa-calendar-alt" style="color: #f43f5e;"></i><span>Calendar</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.marketing.ads.*') ? 'active' : '' }}"
                                    href="{{ route('admin.marketing.ads.index') }}">
                                    <i class="fas fa-ad" style="color: #f43f5e;"></i><span>Ads Tracking</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.marketing.reports.*') ? 'active' : '' }}"
                                    href="{{ route('admin.marketing.reports.index') }}">
                                    <i class="fas fa-chart-pie" style="color: #f43f5e;"></i><span>Marketing Reports</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
                {{-- ══════════════════════════════════════
                BULK SMS (standalone)
                ══════════════════════════════════════ --}}
                @if(auth()->user()->hasPermission('manage_marketing'))
                    <li class="nav-item {{ request()->routeIs('admin.bulk-sms.*') ? 'active' : '' }}">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.bulk-sms.*') ? 'active' : '' }}"
                            href="{{ route('admin.bulk-sms.index') }}" data-no-preloader>
                            <i class="fas fa-sms" style="color: #8b5cf6;"></i><span>Bulk SMS</span>
                        </a>
                    </li>
                @endif
                {{-- ══════════════════════════════════════
                REPORTS & ANALYTICS
                ══════════════════════════════════════ --}}
                @if(
                        auth()->user()->hasPermission('manage_reports') && !in_array($userRole, [
                            'receptionist',
                            'operator'
                        ])
                    )
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ (request()->routeIs('admin.reports') || request()->routeIs('admin.reports.*') || request()->routeIs('admin.hero-slides.analytics') || request()->routeIs('admin.design-tasks.reports') || request()->routeIs('admin.saler-performance.*') || request()->routeIs('admin.gatekeeper-performance.*') || request()->routeIs('admin.delivery-performance.*') || request()->routeIs('admin.finance.reports') || request()->routeIs('admin.finance.daily-report')) ? 'active' : '' }}"
                            href="{{ route('admin.reports') }}" data-no-preloader>
                            <i class="fas fa-chart-bar" style="color: #ef4444;"></i><span>Reports</span>
                        </a>
                    </li>
                @endif

                {{-- Notifications --}}
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}"
                        href="{{ route('admin.notifications.index') }}" data-no-preloader>
                        <i class="fas fa-bell" style="color: #3b82f6;"></i><span>Notifications</span>
                        @php
                            $unreadCount = auth()->user() ? auth()->user()->unreadNotifications()->count() : 0;
                        @endphp
                        @if($unreadCount > 0)
                            <span class="badge bg-danger ms-auto">{{ $unreadCount }}</span>
                        @endif
                    </a>
                </li>

                {{-- Contact Messages --}}
                @if(auth()->user()->hasPermission('view_contact_messages') && !in_array($userRole, ['receptionist', 'operator', 'saler']))
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}"
                            href="{{ route('admin.contact-messages.index') }}" data-no-preloader>
                            <i class="fas fa-envelope" style="color: #3b82f6;"></i><span>Messages</span>
                            @if($sidebarMsgCount > 0)
                                <span class="badge bg-danger ms-auto">{{ $sidebarMsgCount }}</span>
                            @endif
                        </a>
                    </li>
                @endif

                {{-- Administration (formerly Configurations) --}}
                @if(!in_array($userRole, ['saler']) && (in_array($userRole, ['super_admin', 'admin', 'manager']) || auth()->user()->hasPermission('view_audit_logs') || auth()->user()->hasPermission('manage_users') || auth()->user()->hasPermission('manage_settings') || auth()->user()->hasPermission('manage_customers')))
                    <li class="nav-item has-submenu {{ (request()->routeIs('admin.finance.departments.*') || request()->routeIs('admin.admins.*') || request()->routeIs('admin.roles-permissions.*')) ? 'active' : '' }}"
                        data-submenu-toggle>
                        <a class="nav-link d-flex align-items-center justify-content-between {{ (request()->routeIs('admin.finance.departments.*') || request()->routeIs('admin.admins.*') || request()->routeIs('admin.roles-permissions.*')) ? 'active' : '' }}"
                            href="#" data-no-preloader>
                            <span class="d-flex align-items-center">
                                <i class="fas fa-shield-alt" style="color: #6366f1;"></i><span>Administration</span>
                            </span>
                            <i class="fas fa-chevron-right submenu-arrow"></i>
                        </a>
                        <ul class="nav-submenu">

                            {{-- System Management --}}
                            @if(in_array($userRole, ['admin', 'super_admin']))
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.finance.departments.*') ? 'active' : '' }}"
                                        href="{{ route('admin.finance.departments.index') }}" data-no-preloader>
                                        <i class="fas fa-building" style="color: #6366f1;"></i><span>Departments</span>
                                    </a>
                                </li>
                            @endif

                            {{-- Manage Users --}}
                            @if(auth()->user()->hasPermission('manage_users'))
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}"
                                        href="{{ route('admin.admins.index') }}" data-no-preloader>
                                        <i class="fas fa-users" style="color: #6366f1;"></i><span>Users</span>
                                    </a>
                                </li>
                            @endif
                            @if(auth()->user()->hasPermission('manage_roles'))
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.roles-permissions.*') ? 'active' : '' }}"
                                        href="{{ route('admin.roles-permissions.index') }}" data-no-preloader>
                                        <i class="fas fa-user-shield" style="color: #6366f1;"></i><span>Roles</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>

                    {{-- Security (standalone submenu: Activity Logs + Reset Password) --}}
                    @if(auth()->user()->hasPermission('view_audit_logs'))
                        <li class="nav-item has-submenu {{ (request()->routeIs('admin.audit-logs.*') || request()->routeIs('admin.security.*')) ? 'active' : '' }}"
                            data-submenu-toggle>
                            <a class="nav-link d-flex align-items-center justify-content-between {{ (request()->routeIs('admin.audit-logs.*') || request()->routeIs('admin.security.*')) ? 'active' : '' }}"
                                href="#" data-no-preloader>
                                <span class="d-flex align-items-center">
                                    <i class="fas fa-lock" style="color: #6366f1;"></i><span>Security</span>
                                </span>
                                <i class="fas fa-chevron-right submenu-arrow"></i>
                            </a>
                            <ul class="nav-submenu">
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}"
                                        href="{{ route('admin.audit-logs.index') }}" data-no-preloader>
                                        <i class="fas fa-clipboard-list" style="color: #6366f1;"></i><span>Activity Logs</span>
                                    </a>
                                </li>
                                @if(auth()->user()->hasPermission('reset_passwords') && !in_array($userRole, ['receptionist', 'operator']))
                                    <li class="nav-item">
                                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.security.reset-password') ? 'active' : '' }}"
                                            href="{{ route('admin.security.reset-password') }}" data-no-preloader>
                                            <i class="fas fa-key" style="color: #6366f1;"></i><span>Reset Password</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    {{-- Settings (standalone top-level link) --}}
                    @if(auth()->user()->hasPermission('manage_settings'))
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.settings') || request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                                href="{{ route('admin.settings') }}" data-no-preloader>
                                <i class="fas fa-cog" style="color: #6366f1;"></i><span>Settings</span>
                            </a>
                        </li>
                    @endif
                @endif

                {{-- ══════════════════════════════════════
                PERSONAL
                ══════════════════════════════════════ --}}
                @if(in_array($userRole, ['receptionist', 'designer', 'operator', 'delivery', 'saler']))
                @endif

                @if($userRole === 'saler')
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.saler.sales-report') ? 'active' : '' }}"
                            href="{{ route('admin.saler.sales-report') }}" data-no-preloader>
                            <i class="fas fa-file-alt" style="color:#f59e0b;"></i><span>Sales Report</span>
                        </a>
                    </li>
                @endif

                @if(in_array($userRole, ['receptionist', 'designer', 'operator', 'delivery', 'saler']))
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.profile') ? 'active' : '' }}"
                            href="{{ route('admin.profile') }}" data-no-preloader>
                            <i class="fas fa-user"></i><span>Profile</span>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>

        <!-- Logout Button in Sidebar -->
        <div class="p-3 border-top flex-shrink-0" style="border-color:rgba(0,0,0,0.07)!important;">
            <form method="POST" action="{{ route('admin.logout') }}" id="logout-form">
                @csrf
                <button type="submit" class="nav-link w-100 text-start d-flex align-items-center logout-btn"
                    data-no-global-handler>
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
                <img src="{{ asset('images/logo.webp') }}" alt="CHIBO BRAND" style="height: 30px; width: auto;"
                    onerror="this.style.display='none'">
            </div>

            <div class="user-info">
                <div class="d-flex align-items-center gap-3">
                    @php
                        $unreadNotifications = auth()->user() ?
                            auth()->user()->notifications()->unread()->latest()->take(5)->get() : collect();
                        $unreadNotificationsCount = auth()->user() ? auth()->user()->notifications()->unread()->count() : 0;
                        $newMessages = \App\Models\ContactMessage::where('status', 'new')->latest()->take(5)->get();
                        $newMessagesCount = \App\Models\ContactMessage::where('status', 'new')->count();
                    @endphp

                    <!-- Notifications Slide Sheet -->
                    <button class="btn btn-link position-relative p-2 text-decoration-none" type="button"
                        id="notificationTrigger" data-bs-toggle="offcanvas" data-bs-target="#notificationSheet"
                        aria-controls="notificationSheet" aria-label="Open notifications"
                        style="border-radius: 10px; transition: all 0.3s; background: rgba(0,0,0,0.03);">
                        <i class="fas fa-bell fa-lg text-dark"></i>
                        @if($unreadNotificationsCount > 0)
                            <span class="position-absolute translate-middle badge rounded-pill bg-danger"
                                style="top: 8px; right: -5px; border: 2px solid #fff; font-size: 0.65rem; min-width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; padding: 0;">
                                {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                            </span>
                        @endif
                    </button>



                    @if((auth()->user()->role ?? null) === 'saler')
                        <!-- Share Links icon (Saler only) -->
                        <button class="btn btn-link p-2 text-decoration-none" type="button" data-bs-toggle="modal"
                            data-bs-target="#shareLinksModal" title="Share Links" aria-label="Share Links">
                            <i class="fas fa-share-alt" style="font-size: 1rem;"></i>
                        </button>
                    @endif

                    <!-- World / Landing Page -->
                    <a href="{{ route('home') }}" class="btn btn-link p-2 text-decoration-none"
                        title="Go to Landing Page" aria-label="Go to Landing Page">
                        <i class="fas fa-globe fa-lg text-dark"></i>
                    </a>

                    <!-- User Profile Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-link p-0 text-decoration-none d-flex align-items-center"
                            id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar">
                                {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                            </div>
                            <div class="ms-2 user-name">
                                <div class="fw-bold">{{ auth()->user()->name ?? 'Admin' }}</div>
                            </div>
                            <i class="fas fa-caret-down ms-2 text-muted"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.profile') }}">
                                    <i class="fas fa-user me-2 text-muted"></i> Profile
                                </a>
                            </li>

                            @if((auth()->user()->role ?? null) === 'super_admin')
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.settings') }}">
                                        <i class="fas fa-cog me-2 text-muted"></i> Settings
                                    </a>
                                </li>
                            @endif
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('admin.logout') }}" class="px-3 m-0">
                                    @csrf
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

        <!-- Notifications Slide Sheet (Outside Navbar) -->
        <div class="offcanvas offcanvas-end notification-sheet" tabindex="-1" id="notificationSheet"
            aria-labelledby="notificationSheetLabel">
            <div class="offcanvas-header border-bottom py-2">
                <div>
                    <h5 class="offcanvas-title mb-0 fw-bold" id="notificationSheetLabel">Recent Activities</h5>
                    <small class="text-muted d-block">Latest updates and alerts</small>
                </div>
                <div class="d-flex gap-2 ms-3">
                    @php $unreadNotificationsCount = auth()->user() ? auth()->user()->notifications()->unread()->count() : 0; @endphp
                    @if($unreadNotificationsCount > 0)
                        <button type="button"
                            class="btn btn-sm btn-link text-primary p-0 text-decoration-none small fw-bold mark-all-read-btn">
                            Clear All
                        </button>
                    @endif
                    <a href="{{ route('admin.notifications.index') }}" class="text-muted"
                        title="View Hub">
                        <i class="fas fa-external-link-alt small"></i>
                    </a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0 d-flex flex-column">
                <div id="notificationList" class="flex-grow-1" style="overflow-y: auto;">
                    @php $unreadNotifications = auth()->user() ? auth()->user()->notifications()->unread()->latest()->take(10)->get() : collect(); @endphp
                    @forelse($unreadNotifications as $notification)
                        @php
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
                                $icon = 'fa-shopping-bag';
                            } elseif ($isUrgent) {
                                $iconBoxClass = 'bg-warning-subtle text-warning';
                                $icon = 'fa-exclamation-triangle';
                            }
                        @endphp
                        @php
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
                        @endphp
                        <a href="{{ $url }}"
                            class="text-decoration-none d-block p-3 border-bottom notification-item position-relative"
                            style="transition: background 0.2s;">
                            <div class="d-flex gap-3">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center {{ $iconBoxClass }}"
                                        style="width: 42px; height: 42px; font-size: 1rem;">
                                        <i class="fas {{ $icon }}"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="fw-bold text-dark small text-truncate pe-3">
                                            {{ ucwords(str_replace(['_', '-'], ' ', $notification->type ?? 'Notification')) }}
                                        </span>
                                        <small class="text-muted flex-shrink-0" style="font-size: 0.7rem;">
                                            {{ $notification->created_at->diffForHumans(null, true) }}
                                        </small>
                                    </div>
                                    <div class="text-muted small text-truncate-2"
                                        style="font-size: 0.8rem; line-height: 1.4;">
                                        @if($notification->sender)
                                            <span class="text-primary fw-bold">{{ $notification->sender->name }}:</span>
                                        @endif
                                        {{ $notification->message }}
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-5 text-center">
                            <div class="mb-3">
                                <i class="fas fa-check-circle text-success"
                                    style="font-size: 2.5rem; opacity: 0.5;"></i>
                            </div>
                            <p class="text-muted small mb-0">You're all caught up!</p>
                        </div>
                    @endforelse
                </div>

                <a href="{{ route('admin.notifications.index') }}"
                    class="d-block p-3 text-center text-primary small fw-bold bg-light text-decoration-none border-top mt-auto">
                    <i class="fas fa-arrow-right me-1"></i> View Full Activity Log
                </a>
            </div>
        </div>
        <div class="content-area">


            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        window.adminConfig = {
            routes: {
                notificationsMarkAllRead: "{{ route('admin.notifications.mark-all-read') }}",
                notificationsIndex: "{{ route('admin.notifications.index') }}",
                notificationsRefreshCount: "{{ route('admin.notifications.refresh-count') }}"
            }
        };
    </script>
    <script src="{{ asset('js/admin.js') }}?v={{ filemtime(public_path('js/admin.js')) }}"></script>

    <!-- Initialize Session Notifications -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if (session('success'))
                showNotification(@json(session('success')), 'success');
            @endif
            @if (session('error'))
                showNotification(@json(session('error')), 'error');
            @endif
            @if (session('warning'))
                showNotification(@json(session('warning')), 'warning');
            @endif
            @if (session('info'))
                showNotification(@json(session('info')), 'info');
            @endif
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
    @stack('scripts')



    <!-- Real-time Notification Sound -->
    <audio id="notificationSound" preload="auto">
        <source src="{{ asset('notification.wav') }}" type="audio/wav">
    </audio>



    <!-- Beautiful Notification System -->
    <div id="notificationContainer" style="position: fixed; top: 20px; right: 20px; z-index: 99999; max-width: 400px;">
    </div>




    @php
        $salerPhoneRaw = auth()->user()->phone ?? '';
        $salerPhoneDigits = preg_replace('/[^\d\+]/', '', $salerPhoneRaw);
        $salerPhoneForWa = ltrim($salerPhoneDigits, '+');
        $hasSalerPhone = !empty($salerPhoneForWa);
        $retailLink = url('/shop') . ($hasSalerPhone ? ('?saler=+' . $salerPhoneForWa) : '');
        $wholesaleLink = url('/b2b/shop') . ($hasSalerPhone ? ('?saler=+' . $salerPhoneForWa) : '');
        $waTextRetail = urlencode('Hi, please view products here: ' . $retailLink);
        $waTextWholesale = urlencode('Hi, please view B2B products here: ' . $wholesaleLink);
    @endphp

    @if((auth()->user()->role ?? null) === 'saler')
        <!-- Share Links Modal (Saler only) -->
        <div class="modal fade" id="shareLinksModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-share-alt me-2"></i>Share Your Links</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if(!$hasSalerPhone)
                            <div id="salerPhoneWarning" class="alert alert-warning d-flex align-items-center" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Please add your WhatsApp number in Profile to enable share links.
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Retail Landing Link</label>
                            <div class="input-group">
                                <input id="retailShareLink" type="text" class="form-control" readonly
                                    value="{{ $retailLink }}">
                                <button class="btn btn-outline-secondary" type="button" data-copy-target="#retailShareLink">
                                    <i class="fas fa-copy"></i>
                                </button>
                                @if($hasSalerPhone)
                                    <a class="btn btn-success" target="_blank"
                                        href="https://wa.me/{{ $salerPhoneForWa }}?text={{ $waTextRetail }}">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                @endif
                            </div>
                            <small class="text-muted">Share this link with customers to send orders directly to your
                                WhatsApp.</small>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-semibold">Wholesale Landing Link</label>
                            <div class="input-group">
                                <input id="wholesaleShareLink" type="text" class="form-control" readonly
                                    value="{{ $wholesaleLink }}">
                                <button class="btn btn-outline-secondary" type="button"
                                    data-copy-target="#wholesaleShareLink">
                                    <i class="fas fa-copy"></i>
                                </button>
                                @if($hasSalerPhone)
                                    <a class="btn btn-success" target="_blank"
                                        href="https://wa.me/{{ $salerPhoneForWa }}?text={{ $waTextWholesale }}">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                @endif
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
    @endif

</body>

</html>