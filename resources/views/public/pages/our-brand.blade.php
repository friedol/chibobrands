@extends('public.layouts.app')

@section('title', 'CHIBO BRAND - We Design. We Print. We Build Brands That Stand Out')
@section('description', 'Welcome to CHIBO BRAND — your trusted partner for creative design, high-quality printing, and complete branding solutions in Tanzania.')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/landing-page.css') }}">
@endpush

@push('styles')
<style>
    .our-brand-hero {
        background: linear-gradient(135deg, #c0392b 0%, #dc3545 60%, #e74c3c 100%);
        padding: 52px 0 44px;
        position: relative;
        overflow: hidden;
    }
    .our-brand-hero .ob-bubbles {
        position: absolute;
        width: 100%; height: 100%;
        top: 0; left: 0;
        overflow: hidden;
        z-index: 1;
        pointer-events: none;
    }
    .ob-bubble {
        position: absolute;
        border-radius: 50%;
        background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.25), rgba(255,255,255,0.04));
        box-shadow: inset 0 0 30px rgba(255,255,255,0.15), 0 0 50px rgba(255,255,255,0.05);
        animation: ob-float 20s infinite ease-in-out;
        backdrop-filter: blur(2px);
    }
    .ob-bubble::before {
        content: '';
        position: absolute;
        top: 10%; left: 10%;
        width: 40%; height: 40%;
        border-radius: 50%;
        background: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.35), transparent);
    }
    .ob-bubble-1 { width: 180px; height: 180px; left: 8%;   top: 10%;    animation-delay: 0s; animation-duration: 25s; }
    .ob-bubble-2 { width: 120px; height: 120px; right: 12%; top: 30%;    animation-delay: 3s; animation-duration: 20s; }
    .ob-bubble-3 { width: 220px; height: 220px; left: 45%;  top: 50%;    animation-delay: 6s; animation-duration: 30s; }
    .ob-bubble-4 { width: 150px; height: 150px; right: 28%; top: 5%;     animation-delay: 2s; animation-duration: 22s; }
    .ob-bubble-5 { width: 100px; height: 100px; left: 28%;  bottom: 5%;  animation-delay: 4s; animation-duration: 18s; }
    .ob-bubble-6 { width: 200px; height: 200px; right: 5%;  bottom: 10%; animation-delay: 5s; animation-duration: 28s; }
    @keyframes ob-float {
        0%,100% { transform: translate(0,0) scale(1) rotate(0deg); }
        25%      { transform: translate(25px,-25px) scale(1.08) rotate(90deg); }
        50%      { transform: translate(-15px,18px) scale(0.93) rotate(180deg); }
        75%      { transform: translate(35px,8px) scale(1.04) rotate(270deg); }
    }
    .ob-badge {
        display: inline-block;
        background: rgba(255,255,255,0.18);
        border: 1.5px solid rgba(255,255,255,0.35);
        color: #fff;
        padding: 7px 22px;
        border-radius: 50px;
        font-size: 13px;
        font-style: italic;
        font-family: Georgia, 'Times New Roman', serif;
        letter-spacing: 0.3px;
        margin-bottom: 16px;
        backdrop-filter: blur(6px);
    }
    .ob-title {
        font-size: clamp(1.8rem, 4vw, 2.6rem);
        font-weight: 800;
        color: #fff;
        line-height: 1.2;
        margin-bottom: 12px;
        text-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    .ob-subtitle {
        font-size: clamp(0.85rem, 1.5vw, 1rem);
        color: rgba(255,255,255,0.85);
        max-width: 480px;
        margin: 0 auto;
        line-height: 1.6;
    }
    @media (max-width: 768px) {
        .our-brand-hero { padding: 36px 0 30px; }
        .ob-bubble-3, .ob-bubble-6 { display: none; }
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<div class="our-brand-hero">
    <div class="ob-bubbles">
        <div class="ob-bubble ob-bubble-1"></div>
        <div class="ob-bubble ob-bubble-2"></div>
        <div class="ob-bubble ob-bubble-3"></div>
        <div class="ob-bubble ob-bubble-4"></div>
        <div class="ob-bubble ob-bubble-5"></div>
        <div class="ob-bubble ob-bubble-6"></div>
    </div>
    <div class="container text-center" style="position:relative;z-index:10;">
        <span class="ob-badge">Our Brand</span>
        <h1 class="ob-title">We Design. We Print. We Build Brands.</h1>
        <p class="ob-subtitle">Welcome to CHIBO BRAND — your trusted partner for creative design, high-quality printing, and complete branding solutions.</p>
    </div>
</div>

<!-- 2️⃣ ABOUT PREVIEW SECTION -->
<section class="section-landing about-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0 slide-in-left">
                <div class="position-relative">
                    <img src="{{ asset('images/logo.webp') }}" alt="CHIBO BRAND Logo" class="img-fluid about-image" onerror="this.src='{{ asset('images/default.webp') }}'">
                </div>
            </div>
            <div class="col-lg-6 slide-in-right">
                <h2 class="section-title">Who We Are</h2>
                <p class="lead mb-4">
                    At CHIBO BRAND, we blend creativity, technology, and craftsmanship to deliver exceptional branding and printing experiences.
                </p>
                <p class="text-muted mb-4">
                    Every design we create tells a story — your story. From concept to completion, we work closely with individuals, businesses, and organizations to build strong, visually appealing brand identities that stand out in every environment.
                </p>
                <a href="{{ url('/about') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-right me-2"></i>Learn More About Us
                </a>
            </div>
        </div>
    </div>
</section>

<!-- 3️⃣ OUR CORE SERVICES SECTION -->
<section id="services" class="section-landing">
    <div class="container">
        <div class="text-center mb-5 fade-in">
            <h2 class="section-title">What We Do</h2>
            <p class="section-subtitle">Comprehensive creative solutions for your brand</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3 class="service-title">Designing</h3>
                    <p class="service-description">
                        Creative graphic design services that bring your vision to life. From logos to complete brand identities, we craft designs that resonate.
                    </p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-print"></i>
                    </div>
                    <h3 class="service-title">Printing</h3>
                    <p class="service-description">
                        High-quality printing services for all your needs. Business cards, flyers, banners, and more with premium materials and finishes.
                    </p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3 class="service-title">Branding</h3>
                    <p class="service-description">
                        Complete branding solutions that establish your unique identity. We create cohesive brand experiences across all touchpoints.
                    </p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <h3 class="service-title">Promotion</h3>
                    <p class="service-description">
                        Strategic promotional campaigns and materials that get your message noticed. Stand out with impactful promotional products.
                    </p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="service-title">Marketing</h3>
                    <p class="service-description">
                        Data-driven marketing strategies that deliver results. From digital to traditional, we help you reach your target audience effectively.
                    </p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-gift"></i>
                    </div>
                    <h3 class="service-title">Custom Products</h3>
                    <p class="service-description">
                        Branded merchandise and custom products that leave a lasting impression. T-shirts, mugs, caps, and more with your unique branding.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 4️⃣ FEATURED PRODUCTS PREVIEW -->
<section class="section-landing bg-light">
    <div class="container">
        <div class="text-center mb-5 fade-in">
            <h2 class="section-title">Our Products & Solutions</h2>
            <p class="section-subtitle">Quality products that showcase your brand</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-3 fade-in">
                <div class="product-card">
                    <img src="{{ asset('images/default.webp') }}" alt="Custom T-Shirts" class="product-image">
                    <div class="product-info">
                        <h4 class="mb-2">Custom T-Shirts</h4>
                        <p class="text-muted small">Premium quality branded apparel</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 fade-in">
                <div class="product-card">
                    <img src="{{ asset('images/default.webp') }}" alt="Business Cards" class="product-image">
                    <div class="product-info">
                        <h4 class="mb-2">Business Cards</h4>
                        <p class="text-muted small">Professional cards that impress</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 fade-in">
                <div class="product-card">
                    <img src="{{ asset('images/default.webp') }}" alt="Banners & Signs" class="product-image">
                    <div class="product-info">
                        <h4 class="mb-2">Banners & Signs</h4>
                        <p class="text-muted small">Large format printing solutions</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 fade-in">
                <div class="product-card">
                    <img src="{{ asset('images/default.webp') }}" alt="Promotional Items" class="product-image">
                    <div class="product-info">
                        <h4 class="mb-2">Promotional Items</h4>
                        <p class="text-muted small">Branded merchandise that works</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5">
            <a href="{{ url('/shop') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-bag me-2"></i>View All Products
            </a>
        </div>
    </div>
</section>

<!-- 5️⃣ WHY CHOOSE US / STATS SECTION -->
<section class="stats-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title text-white">Why Businesses Choose CHIBO BRAND</h2>
            <p class="text-white-50 fs-5">
                Our dedication to creativity, precision, and fast delivery has made us a trusted partner for businesses across Tanzania and beyond.
            </p>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="stat-item">
                    <span class="stat-number" data-target="500">0</span>
                    <div class="stat-label">Projects Completed</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <span class="stat-number" data-target="100">0</span>
                    <div class="stat-label">Happy Clients</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <span class="stat-number" data-target="5">0</span>
                    <div class="stat-label">Years of Experience</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 6️⃣ PORTFOLIO PREVIEW -->
<section class="section-landing">
    <div class="container">
        <div class="text-center mb-5 fade-in">
            <h2 class="section-title">Our Creative Work</h2>
            <p class="section-subtitle">See what we've created for our clients</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="portfolio-item">
                    <img src="{{ asset('images/default.webp') }}" alt="Portfolio Item 1">
                    <div class="portfolio-overlay">
                        <h4 class="portfolio-title">Brand Identity Design</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="portfolio-item">
                    <img src="{{ asset('images/default.webp') }}" alt="Portfolio Item 2">
                    <div class="portfolio-overlay">
                        <h4 class="portfolio-title">Corporate Branding</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="portfolio-item">
                    <img src="{{ asset('images/default.webp') }}" alt="Portfolio Item 3">
                    <div class="portfolio-overlay">
                        <h4 class="portfolio-title">Event Promotion</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 7️⃣ CALL TO ACTION SECTION -->
<section class="cta-section">
    <div class="cta-content container text-center">
        <h2 class="cta-title fade-in">Ready to Bring Your Brand to Life?</h2>
        <p class="lead text-white-50 mb-5 fade-in">
            Let's create something amazing together. Get in touch today for a free consultation.
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap fade-in">
            <a href="{{ url('/contact') }}" class="btn btn-hero btn-hero-primary btn-lg">
                <i class="fas fa-paper-plane me-2"></i>Request a Quote
            </a>
            <a href="{{ url('/contact') }}" class="btn btn-hero btn-hero-outline btn-lg">
                <i class="fas fa-phone me-2"></i>Contact Us
            </a>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script src="{{ asset('js/landing-page.js') }}"></script>
@endpush
