@extends('public.layouts.app')

@section('title', 'CHIBO BRAND - We Design. We Print. We Build Brands That Stand Out')
@section('description', 'Welcome to CHIBO BRAND — your trusted partner for creative design, high-quality printing, and complete branding solutions in Tanzania.')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/landing-page.css') }}">
@endpush

@section('content')
<!-- 1️⃣ HERO SECTION -->
<section class="hero-landing">
    <!-- 3D Animated Bubbles -->
    <div class="bubbles-container-hero">
        <div class="bubble-hero bubble-hero-1"></div>
        <div class="bubble-hero bubble-hero-2"></div>
        <div class="bubble-hero bubble-hero-3"></div>
        <div class="bubble-hero bubble-hero-4"></div>
        <div class="bubble-hero bubble-hero-5"></div> 
        <div class="bubble-hero bubble-hero-6"></div>
    </div>

    <div class="floating-elements">
        <i class="fas fa-tshirt fa-3x float-item"></i>
        <i class="fas fa-mug-hot fa-3x float-item"></i>
        <i class="fas fa-id-card fa-3x float-item"></i>
        <i class="fas fa-flag fa-3x float-item"></i>
    </div>
    
    <div class="particle-container"></div>
    
    <div class="hero-content container">
        <div class="hero-logo fade-in mb-4">
            <a href="{{ url('/shop') }}">
                <img src="{{ asset('images/logo.webp') }}" alt="CHIBO BRAND Logo" class="img-fluid" style="max-width: 200px; height: auto;" onerror="this.style.display='none'">
            </a>
        </div>
        <h1 class="hero-title fade-in">
            We Design. We Print. We Build Brands That Stand Out.
        </h1>
        <p class="hero-subtitle fade-in">
            Welcome to CHIBO BRAND — your trusted partner for creative design, high-quality printing, and complete branding solutions.
        </p>
        <div class="hero-buttons fade-in">
            <a href="#services" class="btn btn-hero btn-hero-primary">
                <i class="fas fa-rocket me-2"></i>Explore Our Services
            </a>
            <a href="{{ url('/contact') }}" class="btn btn-hero btn-hero-outline">
                <i class="fas fa-envelope me-2"></i>Get a Free Quote
            </a>
        </div>
    </div>
    
    <div class="scroll-indicator">
        <i class="fas fa-chevron-down fa-2x text-white"></i>
    </div>
</section>

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
