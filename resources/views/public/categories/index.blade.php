@extends('public.layouts.app')

@section('title', 'Categories - CHIBO BRAND')
@section('description', 'Browse our categories to find the perfect printing and branding solutions for your business.')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/wholesale-categories.css') }}">
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
@endpush

@push('styles')
<style>
    .categories-page-hero {
        background: linear-gradient(135deg, #c0392b 0%, #dc3545 60%, #e74c3c 100%);
        padding: 52px 0 44px;
        position: relative;
        overflow: hidden;
    }
    .categories-page-hero .bubbles-container {
        position: absolute;
        width: 100%; height: 100%;
        top: 0; left: 0;
        overflow: hidden;
        z-index: 1;
        pointer-events: none;
    }
    .cat-bubble {
        position: absolute;
        border-radius: 50%;
        background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.25), rgba(255,255,255,0.04));
        box-shadow: inset 0 0 30px rgba(255,255,255,0.15), 0 0 50px rgba(255,255,255,0.05);
        animation: cat-float 20s infinite ease-in-out;
        backdrop-filter: blur(2px);
    }
    .cat-bubble::before {
        content: '';
        position: absolute;
        top: 10%; left: 10%;
        width: 40%; height: 40%;
        border-radius: 50%;
        background: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.35), transparent);
    }
    .cat-bubble-1 { width: 180px; height: 180px; left: 8%;   top: 10%;    animation-delay: 0s; animation-duration: 25s; }
    .cat-bubble-2 { width: 120px; height: 120px; right: 12%; top: 30%;    animation-delay: 3s; animation-duration: 20s; }
    .cat-bubble-3 { width: 220px; height: 220px; left: 45%;  top: 50%;    animation-delay: 6s; animation-duration: 30s; }
    .cat-bubble-4 { width: 150px; height: 150px; right: 28%; top: 5%;     animation-delay: 2s; animation-duration: 22s; }
    .cat-bubble-5 { width: 100px; height: 100px; left: 28%;  bottom: 5%;  animation-delay: 4s; animation-duration: 18s; }
    .cat-bubble-6 { width: 200px; height: 200px; right: 5%;  bottom: 10%; animation-delay: 5s; animation-duration: 28s; }
    @keyframes cat-float {
        0%,100% { transform: translate(0,0) scale(1) rotate(0deg); }
        25%      { transform: translate(25px,-25px) scale(1.08) rotate(90deg); }
        50%      { transform: translate(-15px,18px) scale(0.93) rotate(180deg); }
        75%      { transform: translate(35px,8px) scale(1.04) rotate(270deg); }
    }
    .cat-pph-badge {
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
    .cat-pph-title {
        font-size: clamp(1.8rem, 4vw, 2.6rem);
        font-weight: 800;
        color: #fff;
        line-height: 1.2;
        margin-bottom: 12px;
        text-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    .cat-pph-subtitle {
        font-size: clamp(0.85rem, 1.5vw, 1rem);
        color: rgba(255,255,255,0.85);
        max-width: 480px;
        margin: 0 auto;
        line-height: 1.6;
    }
    @media (max-width: 768px) {
        .categories-page-hero { padding: 36px 0 30px; }
        .cat-bubble-3, .cat-bubble-6 { display: none; }
    }
</style>
@endpush

@section('content')
<!-- Hero Section -->
<div class="categories-page-hero">
    <div class="bubbles-container">
        <div class="cat-bubble cat-bubble-1"></div>
        <div class="cat-bubble cat-bubble-2"></div>
        <div class="cat-bubble cat-bubble-3"></div>
        <div class="cat-bubble cat-bubble-4"></div>
        <div class="cat-bubble cat-bubble-5"></div>
        <div class="cat-bubble cat-bubble-6"></div>
    </div>
    <div class="container text-center" style="position:relative;z-index:10;">
        <span class="cat-pph-badge">Browse Collection</span>
        <h1 class="cat-pph-title">All Categories</h1>
        <p class="cat-pph-subtitle">Explore our comprehensive range of printing and branding solutions.</p>
    </div>
</div>

<!-- Filter Section -->
<div class="container">
    <div class="filter-section">
        <div class="filter-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="categorySearch" placeholder="Search categories..." class="form-control-plaintext">
            </div>
            <div class="filter-dropdown">
                <select id="categoryFilter" class="form-select">
                    <option value="all">All Categories</option>
                    <option value="popular">Popular</option>
                    <option value="new">New Arrivals</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Categories Grid -->
<div class="container">
    @if($categories->count() > 0)
        <div class="categories-grid">
            @foreach($categories as $category)
                <div class="category-card-modern" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                    <div class="category-image-wrapper">
                        @if(!empty($category->image_path))
                            <img src="{{ asset('storage/'.$category->image_path) }}" alt="{{ $category->name }}" class="category-image" onerror="this.src='{{ asset('images/default.webp') }}'">
                        @else
                            <img src="{{ asset('images/default.webp') }}" alt="{{ $category->name }}" class="category-image">
                            <i class="fas fa-tag category-icon-3d"></i>
                        @endif
                    </div>
                    
                    <div class="category-card-body">
                        <h3 class="category-name">{{ $category->name }}</h3>
                        @if($category->description)
                            <p class="category-description">
                                {{ Str::limit($category->description, 120) }}
                            </p>
                        @else
                            <p class="category-description">
                                Explore our premium {{ strtolower($category->name) }} collection for your business needs.
                            </p>
                        @endif
                        
                        <a href="{{ route('categories.show', $category) }}" class="view-products-btn">
                            <i class="fas fa-eye"></i>
                            <span>View Products</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state" data-aos="fade-up">
            <div class="empty-state-icon">
                <i class="fas fa-tags"></i>
            </div>
            <h4>No Categories Available</h4>
            <p>We're working on organizing our products. Please check back soon!</p>
            <a href="{{ route('products.index') }}" class="view-products-btn" style="display: inline-flex;">
                <i class="fas fa-box"></i>
                <span>Browse All Products</span>
            </a>
        </div>
    @endif
    
    <!-- Empty Search State (Hidden by default) -->
    <div id="emptySearchState" class="empty-state" style="display: none;">
        <div class="empty-state-icon">
            <i class="fas fa-search"></i>
        </div>
        <h4>No Categories Found</h4>
        <p>Try adjusting your search or filter to find what you're looking for.</p>
    </div>
</div>

<!-- Support Section -->
<div class="container">
    <div class="support-section" data-aos="fade-up">
        <div class="support-content">
            <div class="row align-items-center">
                <div class="col-md-8 mb-3 mb-md-0">
                    <h5>Need Help Finding the Right Product?</h5>
                    <p class="mb-0">Our team is here to help you find the perfect printing and branding solutions for your business needs.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="https://wa.me/255687183330" target="_blank" class="support-btn">
                        <i class="fab fa-whatsapp"></i>
                        <span>Contact Us</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- WhatsApp Floating Button -->
<a href="https://wa.me/255655392319?text=Hello%20CHIBO%20BRAND%20👋,%20I%20need%20help%20with%20categories." 
   class="whatsapp-float-categories" 
   target="_blank" 
   title="Chat with us on WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>
@endsection

@push('scripts')
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="{{ asset('js/wholesale-categories.js') }}"></script>
@endpush
