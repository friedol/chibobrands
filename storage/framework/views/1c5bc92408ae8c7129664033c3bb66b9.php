<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=no">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'CHIBO BRAND - Professional Printing & Branding Solutions'); ?></title>
    <meta name="description"
        content="<?php echo $__env->yieldContent('description', 'CHIBO BRAND offers professional printing and branding solutions. Quality products, competitive prices, and excellent customer service.'); ?>">
    <meta name="keywords"
        content="printing, branding, business cards, flyers, banners, promotional materials, Tanzania">
    <meta name="author" content="CHIBO BRAND">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo $__env->yieldContent('title', 'CHIBO BRAND - Professional Printing & Branding Solutions'); ?>">
    <meta property="og:description"
        content="<?php echo $__env->yieldContent('description', 'CHIBO BRAND offers professional printing and branding solutions.'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo e(url()->current()); ?>">
    <meta property="og:image" content="<?php echo e(asset('images/chibo-brand-logo.png')); ?>">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $__env->yieldContent('title', 'CHIBO BRAND'); ?>">
    <meta name="twitter:description" content="<?php echo $__env->yieldContent('description', 'Professional Printing & Branding Solutions'); ?>">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo e(url('favicon.ico')); ?>?v=<?php echo e(time()); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e(asset('images/apple-touch-icon.png')); ?>">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>?v=<?php echo e(filemtime(public_path('css/app.css'))); ?>">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <!-- Custom CSS -->

    <!-- Animate On Scroll -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body class="min-vh-100">
    <!-- Preloader -->
    <div id="preloader">
        <div class="preloader-content">
            <div class="preloader-logo-wrapper">
                <img src="<?php echo e(asset('images/round.webp')); ?>" alt="Loading..." class="preloader-logo">
            </div>
        </div>
    </div>
    <script>
        (function () {
            const hide = function () {
                const p = document.getElementById("preloader");
                if (p && !p.classList.contains('fade-out')) {
                    p.classList.add("fade-out");
                    setTimeout(function () { p.style.display = "none"; }, 300);
                }
            };
            if (document.readyState === "loading") {
                window.addEventListener("DOMContentLoaded", hide);
            } else {
                hide();
            }
            setTimeout(hide, 2000); // Fail-safe
        })();
    </script>

    <?php $channel = request()->is('b2b/*') ? 'wholesale' : 'retail';
$prefix = $channel === 'wholesale' ? '/b2b' : ''; ?>
    <?php
$defaultWhatsapp = '255655392319';
$dynamicWhatsapp = $defaultWhatsapp;
// Prefer explicit saler param from the shared URL
$salerParam = request()->query('saler');
if ($salerParam) {
    $p = preg_replace('/[^\d\+]/', '', $salerParam);
    $p = ltrim($p, '+');
    if (!empty($p)) {
        $dynamicWhatsapp = $p;
    }
} elseif (\Illuminate\Support\Facades\Auth::check()) {
    $user = \Illuminate\Support\Facades\Auth::user();
    if (($user->role ?? null) === 'saler' && !empty($user->phone)) {
        $phone = preg_replace('/[^\d\+]/', '', $user->phone);
        $phone = ltrim($phone, '+');
        $dynamicWhatsapp = $phone ?: $defaultWhatsapp;
    }
}
    ?>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
        <div class="container">
            <!-- Brand Logo -->
            <a class="navbar-brand" href="<?php echo e(url($prefix . '/')); ?>">
                <img src="<?php echo e(asset('images/logo.webp')); ?>" alt="CHIBO BRAND Logo" style="height: 50px; width: auto;"
                    loading="lazy" onerror="this.onerror=null; this.src='<?php echo e(asset('images/default.webp')); ?>'">
            </a>

            <!-- Mobile Controls -->
            <div class="d-flex align-items-center gap-1 d-lg-none ms-auto position-static">
                <!-- Mobile search toggle -->
                <button class="btn btn-link p-1 text-danger" onclick="toggleMobileSearch()" aria-label="Open search">
                    <i class="fas fa-search" style="font-size: 1.1rem;"></i>
                </button>

                <!-- Mobile cart -->
                <a class="btn btn-link position-relative p-1 text-danger" href="#"
                    onclick="openCartModal(); return false;" aria-label="Open cart">
                    <i class="fas fa-shopping-cart" style="font-size: 1.1rem;"></i>
                    <span
                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count"
                        style="font-size: 0.65rem;">0</span>
                </a>

                <!-- Mobile login/user icon -->
                <?php if(auth()->guard('customer')->check()): ?>
                    <button class="btn btn-link p-1 text-danger" onclick="toggleMobileSidebar()"
                        aria-label="Open account menu">
                        <i class="fas fa-user" style="font-size: 1.1rem;"></i>
                    </button>
                <?php else: ?>
                    <a class="btn btn-link p-1 text-danger" href="<?php echo e(route('login')); ?>" aria-label="Login or register">
                        <i class="fas fa-sign-in-alt" style="font-size: 1.1rem;"></i>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Desktop Navigation -->
            <div class="collapse navbar-collapse d-none d-lg-block" id="navbarNav">
                <div class="d-flex align-items-center w-100">
                    <!-- Main Navigation Links -->
                    <ul class="navbar-nav me-auto ms-lg-4">
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(url($prefix . '/')); ?>">Home</a></li>
                        <li class="nav-item"><a class="nav-link"
                                href="<?php echo e($channel === 'wholesale' ? route('wholesale.products.index') : url($prefix . '/products')); ?>">Products</a>
                        </li>
                        <li class="nav-item"><a class="nav-link"
                                href="<?php echo e($channel === 'wholesale' ? route('wholesale.categories.index') : url($prefix . '/categories')); ?>">Categories</a>
                        </li>
                        <li class="nav-item"><a class="nav-link"
                                href="<?php echo e($channel === 'wholesale' ? url('/b2b/our-brand') : url('/our-brand')); ?>">Our
                                Brand</a></li>
                        <li class="nav-item"><a class="nav-link"
                                href="<?php echo e($channel === 'wholesale' ? url('/b2b/services') : url('/services')); ?>">Services</a>
                        </li>
                        <li class="nav-item"><a class="nav-link"
                                href="<?php echo e($channel === 'wholesale' ? url('/b2b/about') : url('/about')); ?>">About</a></li>
                        <li class="nav-item"><a class="nav-link"
                                href="<?php echo e($channel === 'wholesale' ? url('/b2b/contact') : url('/contact')); ?>">Contact
                                Us</a></li>
                    </ul>

                    <!-- Right Side: Cart & User Account -->
                    <div class="ms-auto d-flex align-items-center justify-content-end gap-3" style="max-width: 450px;">
                        <!-- Search (top/app bar) -->
                        <div class="d-none d-lg-flex align-items-center position-relative me-2" style="width: 280px;">
                            <div class="search-wrapper w-100 m-0 p-0 position-relative">
                                <i class="fas fa-search search-icon text-muted"
                                    style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); z-index: 10;"></i>
                                <input id="hero-search-input" type="text"
                                    class="form-control search-input border rounded-1" placeholder="search products..."
                                    autocomplete="off" style="padding-left: 36px; height: 38px;">
                                <div id="hero-search-results" class="search-results-dropdown"></div>
                            </div>
                        </div>

                        <!-- Cart -->
                        <div class="nav-item d-flex align-items-center">
                            <a class="nav-link position-relative d-flex align-items-center m-0 p-0" href="#"
                                onclick="openCartModal(); return false;">
                                <i class="fas fa-shopping-cart text-dark" style="font-size: 1.25rem;"></i>
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count"
                                    style="font-size: 0.65rem;">0</span>
                            </a>
                        </div>

                        <!-- User Account -->
                        <?php if(auth()->guard('customer')->check()): ?>
                            <div class="nav-item dropdown d-flex align-items-center mb-0 mt-0">
                                <a class="nav-link dropdown-toggle d-flex align-items-center text-dark m-0 p-0" href="#"
                                    role="button" data-bs-toggle="dropdown">
                                    <i
                                        class="fas fa-user me-2"></i><?php echo e(explode(' ', Auth::guard('customer')->user()->name)[0]); ?>

                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                                    <?php if($channel === 'wholesale'): ?>
                                        <li><a class="dropdown-item" href="<?php echo e(route('b2b.customer.dashboard')); ?>">Home</a></li>
                                        <li><a class="dropdown-item" href="<?php echo e(route('b2b.customer.profile')); ?>">Profile</a></li>
                                        <li><a class="dropdown-item" href="<?php echo e(route('b2b.customer.orders.index')); ?>">Orders</a>
                                        </li>
                                    <?php else: ?>
                                        <li><a class="dropdown-item"
                                                href="<?php echo e(route('retail.customer.dashboard')); ?>">Dashboard</a></li>
                                        <li><a class="dropdown-item" href="<?php echo e(route('retail.customer.profile')); ?>">Profile</a>
                                        </li>
                                        <li><a class="dropdown-item"
                                                href="<?php echo e(route('retail.customer.orders.index')); ?>">Orders</a></li>
                                    <?php endif; ?>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form
                                            action="<?php echo e($channel === 'wholesale' ? route('b2b.customer.logout') : route('retail.customer.logout')); ?>"
                                            method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="dropdown-item text-danger" data-no-global-handler>
                                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <div class="d-flex align-items-center gap-2 m-0 p-0">
                                <a class="btn border rounded-2 px-3 d-flex align-items-center gap-2 fw-semibold"
                                    style="font-size: 0.85rem; color: #1e293b; border-color: #e2e8f0 !important; transition: all 0.2s;"
                                    href="<?php echo e(route('login')); ?>">
                                    <i class="fas fa-sign-in-alt opacity-75"></i> Sign In
                                </a>

                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Search Overlay (Hidden by default) -->
        <div id="mobileSearchOverlay" class="d-none position-absolute top-0 start-0 w-100 h-100 bg-white"
            style="z-index: 1060;">
            <div class="d-flex align-items-center w-100 h-100 px-3">
                <div class="search-wrapper flex-grow-1 position-relative m-0 p-0">
                    <i class="fas fa-search search-icon text-muted"
                        style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); z-index: 10;"></i>
                    <input id="hero-search-input-mobile" type="text"
                        class="form-control search-input mobile-search-input border rounded-1 bg-light"
                        placeholder="search products..." autocomplete="off"
                        style="padding-left: 36px; height: 38px; box-shadow: none;">
                    <div id="hero-search-results-mobile"
                        class="search-results-dropdown w-100 position-absolute shadow flex-column"
                        style="top: 100%; left: 0; z-index: 1000;"></div>
                </div>
                <button class="btn btn-link text-dark ms-2 p-1" onclick="toggleMobileSearch()"
                    aria-label="Close search">
                    <i class="fas fa-times" style="font-size: 1.2rem;"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Mobile Sidebar -->
    <div class="mobile-sidebar" id="mobileSidebar">
        <div class="mobile-sidebar-overlay"></div>
        <div class="mobile-sidebar-content">
            <div class="mobile-sidebar-header">
                <div class="d-flex align-items-center">
                    <img src="<?php echo e(asset('images/logo.webp')); ?>" alt="CHIBO BRAND Logo" style="height: 40px; width: auto;"
                        loading="lazy" onerror="this.onerror=null; this.src='<?php echo e(asset('images/default.webp')); ?>'">
                </div>
                <button class="btn btn-link text-dark p-0" onclick="closeMobileSidebar()"
                    aria-label="Close mobile menu">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mobile-sidebar-body">
                <nav class="nav flex-column">
                    <!-- Main Navigation Links -->
                    <a class="nav-link"
                        href="<?php echo e($channel === 'wholesale' ? url('/b2b/our-brand') : url('/our-brand')); ?>"
                        onclick="closeMobileSidebar()">
                        <i class="fas fa-building"></i>Our Brand
                    </a>
                    <a class="nav-link" href="<?php echo e(url($prefix . '/')); ?>" onclick="closeMobileSidebar()">
                        <i class="fas fa-home"></i>Home
                    </a>
                    <a class="nav-link"
                        href="<?php echo e($channel === 'wholesale' ? route('wholesale.products.index') : url($prefix . '/products')); ?>"
                        onclick="closeMobileSidebar()">
                        <i class="fas fa-box"></i>Products
                    </a>
                    <a class="nav-link"
                        href="<?php echo e($channel === 'wholesale' ? route('wholesale.categories.index') : url($prefix . '/categories')); ?>"
                        onclick="closeMobileSidebar()">
                        <i class="fas fa-tags"></i>Categories
                    </a>
                    <a class="nav-link" href="<?php echo e($channel === 'wholesale' ? url('/b2b/services') : url('/services')); ?>"
                        onclick="closeMobileSidebar()">
                        <i class="fas fa-cogs"></i>Services
                    </a>
                    <a class="nav-link" href="<?php echo e($channel === 'wholesale' ? url('/b2b/about') : url('/about')); ?>"
                        onclick="closeMobileSidebar()">
                        <i class="fas fa-info-circle"></i>About
                    </a>
                    <a class="nav-link" href="<?php echo e($channel === 'wholesale' ? url('/b2b/contact') : url('/contact')); ?>"
                        onclick="closeMobileSidebar()">
                        <i class="fas fa-phone"></i>Contact Us
                    </a>

                    <hr class="my-3">

                    <!-- User Account Section -->
                    <?php if(auth()->guard('customer')->check()): ?>
                        <!-- User Info Header -->
                        <div class="nav-link text-muted small mb-2">
                            <i class="fas fa-user me-2"></i><?php echo e(Auth::guard('customer')->user()->name); ?>

                            <span
                                class="badge <?php echo e(Auth::guard('customer')->user()->is_wholesale ? 'bg-warning' : 'bg-info'); ?> ms-2">
                                <?php echo e(Auth::guard('customer')->user()->is_wholesale ? 'Wholesale' : 'Retail'); ?>

                            </span>
                        </div>

                        <!-- Account Navigation Links -->
                        <?php if($channel === 'wholesale'): ?>
                            <a class="nav-link" href="<?php echo e(route('b2b.customer.dashboard')); ?>" onclick="closeMobileSidebar()">
                                <i class="fas fa-tachometer-alt"></i>Dashboard
                            </a>
                            <a class="nav-link" href="<?php echo e(route('b2b.customer.profile')); ?>" onclick="closeMobileSidebar()">
                                <i class="fas fa-user-edit"></i>Profile
                            </a>
                            <a class="nav-link" href="<?php echo e(route('b2b.customer.orders.index')); ?>" onclick="closeMobileSidebar()">
                                <i class="fas fa-shopping-bag"></i>Orders
                            </a>
                        <?php else: ?>
                            <a class="nav-link" href="<?php echo e(route('retail.customer.dashboard')); ?>" onclick="closeMobileSidebar()">
                                <i class="fas fa-tachometer-alt"></i>Dashboard
                            </a>
                            <a class="nav-link" href="<?php echo e(route('retail.customer.profile')); ?>" onclick="closeMobileSidebar()">
                                <i class="fas fa-user-edit"></i>Profile
                            </a>
                            <a class="nav-link" href="<?php echo e(route('retail.customer.orders.index')); ?>"
                                onclick="closeMobileSidebar()">
                                <i class="fas fa-shopping-bag"></i>Orders
                            </a>
                        <?php endif; ?>

                        <hr class="my-3">

                        <!-- Logout Link -->
                        <form
                            action="<?php echo e($channel === 'wholesale' ? route('b2b.customer.logout') : route('retail.customer.logout')); ?>"
                            method="POST" class="d-inline w-100">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent"
                                data-no-global-handler onclick="closeMobileSidebar()">
                                <i class="fas fa-sign-out-alt"></i>Logout
                            </button>
                        </form>
                    <?php else: ?>
                        <a class="nav-link" href="<?php echo e(route('login')); ?>" onclick="closeMobileSidebar()">
                            <i class="fas fa-sign-in-alt"></i>Login
                        </a>
                        <a class="nav-link" href="<?php echo e(route('customer.register')); ?>" onclick="closeMobileSidebar()">
                            <i class="fas fa-user-plus"></i>Register
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <!-- Mobile-First Design -->
            <div class="row g-4">
                <!-- Brand Section -->
                <div class="col-lg-4 col-12 text-center text-lg-start">
                    <div class="footer-brand-section">
                        <div class="footer-logo mb-3">
                            <a href="<?php echo e(url($prefix . '/')); ?>">
                                <img src="<?php echo e(asset('images/logo.webp')); ?>" alt="CHIBO BRAND Logo"
                                    style="max-width: 180px; height: auto;" onerror="this.style.display='none'">
                            </a>
                        </div>
                        <p class="text-white-50 d-none d-lg-block mb-3">
                            Professional printing and branding solutions for businesses of all sizes.
                            Quality products, competitive prices, and excellent customer service.
                        </p>
                        <div class="social-links d-flex justify-content-center justify-content-lg-start gap-3 mb-3">
                            <a href="https://www.instagram.com/chibobrands" target="_blank" class="social-icon"><i
                                    class="fab fa-facebook-f"></i></a>
                            <a href="https://www.instagram.com/chibobrands" target="_blank" class="social-icon"><i
                                    class="fab fa-instagram"></i></a>
                            <a href="https://www.tiktok.com/@chibo_brandsmifuko1?_t=ZM-90eYvciqi7A&_r=1" target="_blank"
                                class="social-icon"><i class="fab fa-tiktok"></i></a>
                            <a href="https://wa.me/<?php echo e($dynamicWhatsapp); ?>" target="_blank" class="social-icon"><i
                                    class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Quick Links - Hidden on mobile -->
                <div class="col-lg-2 col-md-6 d-none d-md-block">
                    <h6 class="text-white mb-3 fw-bold">Quick Links</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="<?php echo e(url($prefix . '/')); ?>"><i class="fas fa-chevron-right me-2"></i>Home</a></li>
                        <li><a href="<?php echo e(url($prefix . '/products')); ?>"><i
                                    class="fas fa-chevron-right me-2"></i>Products</a></li>
                        <li><a href="<?php echo e(url($prefix . '/categories')); ?>"><i
                                    class="fas fa-chevron-right me-2"></i>Categories</a></li>
                    </ul>
                </div>

                <!-- Services - Hidden on mobile -->
                <div class="col-lg-2 col-md-6 d-none d-md-block">
                    <h6 class="text-white mb-3 fw-bold">Services</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Business Cards</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Flyers & Brochures</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Banners & Signs</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Promotional Items</a></li>
                    </ul>
                </div>

                <!-- Contact Info - Always visible with card design on mobile -->
                <div class="col-lg-4 col-12">
                    <div class="contact-card">
                        <h6 class="text-white mb-3 fw-bold text-center text-lg-start">
                            <i class="fas fa-headset me-2"></i>Contact Info
                        </h6>
                        <div class="contact-items">
                            <a href="tel:+255753883382" class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="contact-text">
                                    <small class="d-block text-white-50">Call Us</small>
                                    <span class="text-white">+255 753 883 382<br>+255 68 238 7901<br>+255 65 539
                                        2319<br>+255 794 366 877<br>+255 762 332 849</span>
                                </div>
                            </a>

                            <a href="mailto:chibobrandsltd@gmail.com" class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="contact-text">
                                    <small class="d-block text-white-50">Email Us</small>
                                    <span class="text-white">info@chibobrand.com</span>
                                </div>
                            </a>

                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="contact-text">
                                    <small class="d-block text-white-50">Location</small>
                                    <span class="text-white">Dar es Salaam, Tanzania</span>
                                </div>
                            </div>

                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="contact-text">
                                    <small class="d-block text-white-50">Working Hours</small>
                                    <span class="text-white">Mon - Sat: 8:00 AM - 6:00 PM</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom mt-4 pt-4">
                <div class="row align-items-center">
                    <div class="col-12 text-center">
                        <p class="text-white-50 mb-2 small">
                            © <?php echo e(date('Y')); ?> CHIBO BRAND. All rights reserved.
                        </p>
                        <div class="d-none d-md-block mb-2">
                            <a href="#" class="text-white-50 small me-3 footer-link">Privacy Policy</a>
                            <a href="#" class="text-white-50 small footer-link">Terms of Service</a>
                        </div>
                        <p class="text-white-50 mb-0 mt-3 x-small">
                            <a href="https://wa.me/255717489868" target="_blank"
                                class="text-white-50 text-decoration-none hover-white">
                                Proud of serving Tanzania 🇹🇿 <span class="text-white fw-bold">FridolTech</span>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Floating Button -->
    <a href="https://wa.me/<?php echo e($dynamicWhatsapp); ?>?text=Hello%20CHIBO%20BRAND%20👋,%20I%20would%20like%20to%20know%20more%20about%20your%20services."
        class="whatsapp-float" target="_blank" title="Chat with us on WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        window.chiboConfig = {
            dynamicWhatsapp: <?php echo json_encode($dynamicWhatsapp, 15, 512) ?>,
            defaultImage: "/images/default.webp"
        };
    </script>
    <script src="<?php echo e(asset('js/app.js')); ?>?v=<?php echo e(filemtime(public_path('js/app.js'))); ?>"></script>



    <!-- Centered Cart Modal -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-size: 1rem;"><i class="fas fa-shopping-cart me-2"></i>Your Cart
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="cartModalBody">
                        <!-- Cart items injected here -->
                    </div>
                </div>
                <div class="modal-footer d-block">
                    <!-- VAT Receipt Option -->
                    <div class="mb-2">
                        <div class="form-check vat-option">
                            <input class="form-check-input" type="checkbox" id="modalVatReceipt"
                                onchange="toggleModalVATReceipt()">
                            <label class="form-check-label" for="modalVatReceipt">
                                <strong>Include VAT Receipt (+18%)</strong>
                                <small class="text-muted d-block">Check this if you need a VAT receipt for your
                                    purchase</small>
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-muted small" style="font-size: 0.75rem;">Items: <strong
                                id="cartItemsCount">0</strong></div>
                        <div class="small" style="font-size: 0.75rem;">
                            <div id="modalSubtotal" style="display: none;" class="d-flex justify-content-between mb-1">
                                <span>Subtotal:</span>
                                <strong id="modalSubtotalAmount">0 TZS</strong>
                            </div>
                            <div id="modalVatRow" style="display: none;" class="d-flex justify-content-between mb-1">
                                <span>VAT (18%):</span>
                                <strong id="modalVatAmount">0 TZS</strong>
                            </div>
                            <div class="d-flex justify-content-between border-top pt-1">
                                <span class="fw-bold">Total:</span>
                                <strong class="text-primary" id="cartModalTotal" style="font-size: 0.9rem;">0
                                    TZS</strong>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-between">
                        <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Continue Shopping</button>
                        <button type="button" class="btn btn-success" onclick="sendOrderToWhatsApp()">
                            <i class="fab fa-whatsapp me-2"></i>Send your Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
        <div id="cartToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="fas fa-check-circle text-success me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastMessage">
                Product added to cart successfully!
            </div>
        </div>
    </div>



    <!-- Animate On Scroll -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            easing: 'ease-in-out'
        });

        // Mobile search toggle
        function toggleMobileSearch() {
            const overlay = document.getElementById('mobileSearchOverlay');
            if (overlay) {
                if (overlay.classList.contains('d-none')) {
                    overlay.classList.remove('d-none');
                    // Focus the input safely
                    setTimeout(() => {
                        const input = document.getElementById('hero-search-input-mobile');
                        if (input) input.focus();
                    }, 100);
                } else {
                    overlay.classList.add('d-none');
                }
            }
        }
    </script>

    <?php echo $__env->yieldPushContent('modals'); ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html><?php /**PATH /Users/gotlaptopparts.com/Downloads/chibo_sales/resources/views/public/layouts/app.blade.php ENDPATH**/ ?>