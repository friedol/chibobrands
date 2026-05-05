@extends('public.layouts.app')

@section('title', 'CHIBO BRAND B2B - Your Wholesale Partner for Printing & Branding')
@section('description', 'Welcome to CHIBO BRAND B2B — your trusted wholesale partner for creative design, bulk printing, and complete branding solutions in Tanzania.')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/landing-page.css') }}">
@endpush

@section('content')
<!-- HERO SECTION -->
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
        <i class="fas fa-boxes fa-3x float-item"></i>
        <i class="fas fa-handshake fa-3x float-item"></i>
        <i class="fas fa-truck fa-3x float-item"></i>
        <i class="fas fa-chart-line fa-3x float-item"></i>
    </div>
    
    <div class="particle-container"></div>
    
    <div class="hero-content container">
        <div class="hero-logo fade-in mb-4">
            <a href="{{ url('/b2b/shop') }}">
                <img src="{{ asset('images/logo.webp') }}" alt="CHIBO BRAND Logo" class="img-fluid" style="max-width: 200px; height: auto;" onerror="this.style.display='none'">
            </a>
        </div>
        <h1 class="hero-title fade-in">
            Your Trusted B2B Partner for Bulk Printing & Branding
        </h1>
        <p class="hero-subtitle fade-in">
            Welcome to CHIBO BRAND B2B — Competitive wholesale pricing, reliable delivery, and dedicated support for your business.
        </p>
        <div class="hero-buttons fade-in">
            <a href="#services" class="btn btn-hero btn-hero-primary">
                <i class="fas fa-briefcase me-2"></i>Explore B2B Services
            </a>
            <a href="{{ url('/b2b/contact') }}" class="btn btn-hero btn-hero-outline">
                <i class="fas fa-envelope me-2"></i>Request Wholesale Quote
            </a>
        </div>
    </div>
    
    <div class="scroll-indicator">
        <i class="fas fa-chevron-down fa-2x text-white"></i>
    </div>
</section>

<!-- ABOUT SECTION -->
<section class="section-landing about-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0 slide-in-left">
                <div class="position-relative">
                    <img src="{{ asset('images/logo.webp') }}" alt="CHIBO BRAND Logo" class="img-fluid about-image" onerror="this.src='{{ asset('images/default.webp') }}'">
                </div>
            </div>
            <div class="col-lg-6 slide-in-right">
                <h2 class="section-title">Your B2B Partner</h2>
                <p class="lead mb-4">
                    CHIBO BRAND B2B delivers exceptional value through competitive wholesale pricing and reliable bulk order fulfillment.
                </p>
                <p class="text-muted mb-4">
                    We understand the unique needs of businesses. Our dedicated B2B team ensures consistent quality, on-time delivery, and personalized account management for all your branding and printing requirements.
                </p>
                <a href="{{ url('/b2b/about') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-right me-2"></i>Learn More
                </a>
            </div>
        </div>
    </div>
</section>

<!-- SERVICES SECTION -->
<section id="services" class="section-landing">
    <div class="container">
        <div class="text-center mb-5 fade-in">
            <h2 class="section-title">B2B Services</h2>
            <p class="section-subtitle">Comprehensive wholesale solutions for your business</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h3 class="service-title">Bulk Printing</h3>
                    <p class="service-description">
                        Volume printing services with competitive wholesale pricing. Perfect for large orders and recurring business needs.
                    </p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3 class="service-title">Corporate Branding</h3>
                    <p class="service-description">
                        Complete corporate identity solutions. From employee uniforms to office branding materials.
                    </p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="service-title">Account Management</h3>
                    <p class="service-description">
                        Dedicated account managers to handle your orders, ensure quality, and provide personalized support.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PRODUCTS SECTION -->
<section class="section-landing bg-light">
    <div class="container">
        <div class="text-center mb-5 fade-in">
            <h2 class="section-title">Wholesale Products</h2>
            <p class="section-subtitle">Bulk pricing on quality products</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-3 fade-in">
                <div class="product-card">
                    <img src="{{ asset('images/default.webp') }}" alt="Bulk T-Shirts" class="product-image">
                    <div class="product-info">
                        <h4 class="mb-2">Bulk Apparel</h4>
                        <p class="text-muted small">Wholesale pricing on branded clothing</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 fade-in">
                <div class="product-card">
                    <img src="{{ asset('images/default.webp') }}" alt="Business Cards" class="product-image">
                    <div class="product-info">
                        <h4 class="mb-2">Business Cards</h4>
                        <p class="text-muted small">Volume discounts available</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 fade-in">
                <div class="product-card">
                    <img src="{{ asset('images/default.webp') }}" alt="Signage" class="product-image">
                    <div class="product-info">
                        <h4 class="mb-2">Signage & Banners</h4>
                        <p class="text-muted small">Large format bulk orders</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 fade-in">
                <div class="product-card">
                    <img src="{{ asset('images/default.webp') }}" alt="Promotional" class="product-image">
                    <div class="product-info">
                        <h4 class="mb-2">Promotional Items</h4>
                        <p class="text-muted small">Bulk branded merchandise</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5">
            <a href="{{ url('/b2b/shop') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-bag me-2"></i>View Wholesale Catalog
            </a>
        </div>
    </div>
</section>

<!-- STATS SECTION -->
<section class="stats-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title text-white">Trusted by Businesses</h2>
            <p class="text-white-50 fs-5">
                Reliable wholesale partner for businesses across Tanzania
            </p>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="stat-item">
                    <span class="stat-number" data-target="1000">0</span>
                    <div class="stat-label">Bulk Orders Delivered</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <span class="stat-number" data-target="200">0</span>
                    <div class="stat-label">B2B Clients</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-item">
                    <span class="stat-number" data-target="98">0</span>
                    <div class="stat-label">% On-Time Delivery</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA SECTION -->
<section class="cta-section">
    <div class="cta-content container text-center">
        <h2 class="cta-title fade-in">Ready to Partner with Us?</h2>
        <p class="lead text-white-50 mb-5 fade-in">
            Get competitive wholesale pricing and dedicated support for your business.
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap fade-in">
            <a href="{{ url('/b2b/contact') }}" class="btn btn-hero btn-hero-primary btn-lg">
                <i class="fas fa-briefcase me-2"></i>Request B2B Quote
            </a>
            <a href="{{ url('/b2b/contact') }}" class="btn btn-hero btn-hero-outline btn-lg">
                <i class="fas fa-phone me-2"></i>Contact B2B Team
            </a>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script src="{{ asset('js/landing-page.js') }}"></script>
@endpush
