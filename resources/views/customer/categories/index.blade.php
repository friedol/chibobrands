@extends('public.layouts.app')

@section('title', 'Wholesale Categories - CHIBO BRAND B2B')
@section('description', 'Browse our wholesale product categories with exclusive business pricing.')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/wholesale-categories.css') }}">
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
@endpush

@section('content')
<!-- Hero Section -->
<section class="categories-hero">
    <!-- Animated Bubbles -->
    <div class="hero-bubbles">
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
    </div>
    
    <div class="hero-floating-icons">
        <i class="fas fa-tags floating-icon"></i>
        <i class="fas fa-box floating-icon"></i>
        <i class="fas fa-tshirt floating-icon"></i>
        <i class="fas fa-print floating-icon"></i>
        <i class="fas fa-palette floating-icon"></i>
    </div>
    
    <div class="hero-content container">
        <h1 class="hero-title">WHOLESALE CATEGORIES</h1>
        <p class="hero-subtitle">
            Browse through our diverse range of professional branding and promotional product categories.
        </p>
        
        <div class="categories-breadcrumb">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('/b2b/shop') }}"><i class="fas fa-home me-1"></i>Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Categories</li>
                </ol>
            </nav>
        </div>
    </div>
</section>

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
                        
                        <a href="{{ route('wholesale.categories.show', $category) }}" class="view-products-btn">
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
            <h4>No Wholesale Categories Available</h4>
            <p>We're working on organizing our wholesale products. Please check back soon!</p>
            <a href="{{ route('customer.products.index') }}" class="view-products-btn" style="display: inline-flex;">
                <i class="fas fa-box"></i>
                <span>Browse All Wholesale Products</span>
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
                    <h5>Need Help with Wholesale Orders?</h5>
                    <p class="mb-0">Our team is here to help you with bulk orders, custom pricing, and business solutions.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="https://wa.me/255687183330" target="_blank" class="support-btn">
                        <i class="fab fa-whatsapp"></i>
                        <span>Contact Support</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- WhatsApp Floating Button -->
<a href="https://wa.me/255687183330?text=Hello%20CHIBO%20BRAND%20👋,%20I%20need%20help%20with%20wholesale%20categories." 
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
