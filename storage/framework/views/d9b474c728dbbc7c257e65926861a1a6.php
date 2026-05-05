<?php $__env->startSection('content'); ?>
    <?php 
    $channel = isset($channel) ? $channel : (request()->is('b2b/*') ? 'wholesale' : 'retail');
    
    // Get hero slides (non-ad slides)
    $heroSlides = isset($heroSlides) ? $heroSlides : \App\Models\HeroSlide::active()
        ->where(function($q) {
            $q->where('page_type', 'all')
              ->orWhere('page_type', 'homepage')
              ->orWhere('page_type', 'products');
        })
        ->ordered()
        ->get();
    
    // Get active ads for this page
    $activeAds = \App\Models\HeroSlide::activeAds()
        ->forPage('products')
        ->forTargetAudience($channel)
        ->orderBy('sort_order', 'asc')
        ->get();
    
    // Group ads by type and position
    $adsByType = $activeAds->groupBy('ad_type');
    $bannerAds = $adsByType->get('banner', collect());
    $popupAds = $adsByType->get('popup', collect());
    $sidebarAds = $adsByType->get('sidebar', collect());
    $inlineAds = $adsByType->get('inline', collect());
    // Get categories for sidebar filter
    $allCategories = isset($categories) ? $categories : \App\Models\Category::where('is_active', true)->orderBy('name', 'asc')->get();
    ?>


<!-- Hero Slideshow -->
<?php if($heroSlides && $heroSlides->count() > 0): ?>
<div class="container">
    <div class="hero-split-wrapper mb-4">
    <div class="hero-split-left">
        <div id="hero-slideshow" class="hero-slideshow">
            <div class="hero-slides-container">
                <?php $__currentLoopData = $heroSlides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $slide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="hero-slide <?php echo e($index === 0 ? 'active' : ''); ?>" 
                        <?php if($slide->image_path && !$slide->isVideo()): ?>
                            style="background-image: url('<?php echo e(asset('storage/' . $slide->image_path)); ?>');"
                        <?php endif; ?>>
                        <?php if($slide->image_path && $slide->isVideo()): ?>
                            <video autoplay muted loop playsinline class="hero-video-bg">
                                <source src="<?php echo e(asset('storage/' . $slide->image_path)); ?>" type="video/<?php echo e(strtolower(pathinfo($slide->image_path, PATHINFO_EXTENSION) == 'mov' ? 'mp4' : pathinfo($slide->image_path, PATHINFO_EXTENSION))); ?>">
                            </video>
                        <?php endif; ?>
                        <div class="hero-slide-overlay"></div>
                        <div class="hero-slide-content">
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-lg-10 text-center">
                                        <h1 class="hero-slide-title mb-3"><?php echo e($slide->title); ?></h1>
                                        <?php if($slide->subtitle): ?>
                                            <p class="hero-slide-subtitle mb-4"><?php echo e($slide->subtitle); ?></p>
                                        <?php endif; ?>
                                        <?php if($slide->button_text && $slide->button_url): ?>
                                            <a href="<?php echo e($slide->button_url); ?>" 
                                                class="btn hero-slide-btn" 
                                                style="background-color: <?php echo e($slide->button_color ?? '#dc3545'); ?>;">
                                                <?php echo e($slide->button_text); ?>

                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            
            <?php if($heroSlides->count() > 1): ?>
                <div class="hero-slideshow-controls">
                    <button class="hero-slideshow-prev" id="hero-slideshow-prev">
                        <i class="fas fa-angle-left"></i>
                    </button>
                    <button class="hero-slideshow-next" id="hero-slideshow-next">
                        <i class="fas fa-angle-right"></i>
                    </button>
                </div>
                
                <div class="hero-slideshow-indicators">
                    <?php $__currentLoopData = $heroSlides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $slide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button class="hero-slideshow-indicator <?php echo e($index === 0 ? 'active' : ''); ?>" 
                            data-slide="<?php echo e($index); ?>"></button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="hero-split-right d-none d-lg-block">
        <img
            src="<?php echo e(asset('images/right.png')); ?>"
            alt="CHIBO BRAND side banner"
            class="hero-split-right-img"
            onerror="this.style.display='none'">
    </div>
</div>
</div>
<?php endif; ?>

<?php if(!($heroSlides && $heroSlides->count() > 0)): ?>
<!-- Hero Banner (Standardized) - Only show if no dynamic slides -->
<div class="hero-banner mb-4">
    <div class="hero-content">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 text-center text-lg-start">
                    <div class="hero-badge mb-3">
                        <i class="fas fa-star me-2"></i><?php echo e($channel === 'wholesale' ? 'B2B Solutions' : 'Premium Quality'); ?>

                    </div>
                    <h1 class="hero-title mb-3">
                        <?php if($channel === 'wholesale'): ?>
                            Professional Printing & Branding
                        <?php else: ?>
                            Discover Premium Products
                        <?php endif; ?>
                    </h1>
                    <p class="hero-subtitle mb-4">
                        <?php if($channel === 'wholesale'): ?>
                            Bulk orders • Tiered pricing • Fast delivery
                        <?php else: ?>
                            Quality products • Transparent pricing • Trusted by thousands
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-lg-6 mt-4 mt-lg-0 d-none d-lg-block">
                    <div class="hero-banner-side d-flex align-items-center justify-content-center">
                        <img
                            src="<?php echo e(asset('images/right.png')); ?>"
                            alt="CHIBO BRAND side banner"
                            class="img-fluid hero-banner-side-logo"
                            onerror="this.style.display='none'">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

    <!-- Top Banner Ads -->
    <?php if($bannerAds->where('ad_position', 'top')->count() > 0): ?>
        <div class="banner-ads-top mb-4" data-aos="fade-down">
            <?php $__currentLoopData = $bannerAds->where('ad_position', 'top'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="ad-container" data-ad-id="<?php echo e($ad->id); ?>" data-ad-type="banner">
                    <div class="ad-content">
                        <?php if($ad->image_path): ?>
                            <img src="<?php echo e(asset('storage/' . $ad->image_path)); ?>" 
                                 alt="<?php echo e($ad->title); ?>" 
                                 class="img-fluid w-100 ad-image"
                                 style="border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <?php endif; ?>
                        <?php if($ad->title || $ad->subtitle): ?>
                            <div class="ad-overlay">
                                <?php if($ad->title): ?>
                                    <h5 class="ad-title"><?php echo e($ad->title); ?></h5>
                                <?php endif; ?>
                                <?php if($ad->subtitle): ?>
                                    <p class="ad-subtitle"><?php echo e($ad->subtitle); ?></p>
                                <?php endif; ?>
                                <?php if($ad->button_text && $ad->button_url): ?>
                                    <a href="<?php echo e($ad->button_url); ?>" 
                                       class="btn btn-primary ad-button"
                                       style="background-color: <?php echo e($ad->button_color); ?>; border-color: <?php echo e($ad->button_color); ?>;"
                                       onclick="trackAdClick(<?php echo e($ad->id); ?>)">
                                        <?php echo e($ad->button_text); ?>

                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    


<?php $__env->startPush('styles'); ?>
<style>
    /* Hero Banner - Standardized Styling */
    .hero-banner {
        background: linear-gradient(135deg, #1a0000 0%, #dc3545 100%);
        padding: 20px 0;
        position: relative;
        overflow: visible;
        min-height: 220px;
        margin: 10px 0 0 0;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero-content {
        position: relative;
        z-index: 5;
        width: 100%;
    }

    .hero-banner-side {
        height: 100%;
        min-height: 220px;
    }

    .hero-banner-side-logo {
        max-width: none;
        width: 100%;
        height: 100%;
        object-fit: cover;
        filter: drop-shadow(0 12px 35px rgba(255, 0, 0, 0.25));
        opacity: 0.98;
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(5px);
        color: #fff;
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 700;
        border: 1px solid rgba(255, 255, 255, 0.2);
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .hero-title {
        font-size: clamp(1.8rem, 4vw, 2.5rem);
        font-weight: 800;
        color: #fff;
        line-height: 1.2;
        text-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }

    .hero-subtitle {
        font-size: clamp(0.9rem, 1.5vw, 1.1rem);
        font-weight: 400;
        color: rgba(255, 255, 255, 0.9);
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }


                .hero-search {
                    max-width: 700px
                }

                .search-wrapper {
                    position: relative
                }

                .search-icon {
                    position: absolute;
                    left: 24px;
                    top: 50%;
                    transform: translateY(-50%);
                    color: #ff0000;
                    font-size: 20px;
                    z-index: 12
                }

                .search-input {
                    padding: 12px 20px 12px 50px;
                    border-radius: 50px;
                    border: 2px solid rgba(255, 0, 0, 0.3);
                    background: rgba(255, 255, 255, 0.95);
                    font-size: 15px;
                    transition: all 0.4s;
                    box-shadow: 0 10px 40px rgba(255, 0, 0, 0.2), inset 0 0 20px rgba(255, 0, 0, 0.05)
                }

                .search-input:focus {
                    border-color: #ff0000;
                    box-shadow: 0 0 0 6px rgba(255, 0, 0, 0.2), 0 15px 50px rgba(255, 0, 0, 0.3);
                    background: #fff;
                    transform: translateY(-2px)
                }

                .search-results-dropdown {
                    position: absolute;
                    top: calc(100% + 12px);
                    left: 0;
                    right: 0;
                    background: #fff;
                    border-radius: 20px;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                    max-height: 400px;
                    overflow-y: auto;
                    display: none;
                    z-index: 1000;
                    border: 2px solid rgba(255, 0, 0, 0.1)
                }

                .search-results-dropdown.show {
                    display: block;
                    animation: slideDown 0.3s ease
                }

                @keyframes slideDown {
                    from {
                        opacity: 0;
                        transform: translateY(-10px)
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0)
                    }
                }

                .search-result-item {
                    padding: 16px 24px;
                    border-bottom: 1px solid #f0f0f0;
                    cursor: pointer;
                    transition: all 0.2s;
                    display: flex;
                    align-items: center;
                    gap: 16px
                }

                .search-result-item:hover {
                    background: linear-gradient(90deg, rgba(255, 0, 0, 0.05), transparent);
                    padding-left: 28px
                }

                .search-result-item:last-child {
                    border-bottom: none
                }

                .search-result-img {
                    width: 50px;
                    height: 50px;
                    object-fit: cover;
                    border-radius: 8px;
                    border: 2px solid #f0f0f0
                }

                .search-result-info {
                    flex: 1
                }

                .search-result-name {
                    font-weight: 600;
                    color: #1a1a1a;
                    margin-bottom: 4px
                }

                .search-result-meta {
                    font-size: 13px;
                    color: #999
                }

                .search-result-price {
                    font-weight: 700;
                    color: #ff0000;
                    font-size: 16px
                }

                .search-no-results {
                    padding: 40px;
                    text-align: center;
                    color: #999
                }

                .search-no-results i {
                    font-size: 48px;
                    margin-bottom: 16px;
                    opacity: 0.3
                }

    /* Trust Badges */
                .trust-badges {
                    background: #f8f9fa;
                    padding: 20px 0
                }

                .trust-badge {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                    padding: 15px;
                    background: #fff;
                    border-radius: 12px;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
                    transition: all 0.3s
                }

                .trust-badge:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1)
                }

                .trust-badge i {
                    font-size: 32px;
                    color: #ff0000;
                    min-width: 40px;
                    text-align: center
                }

                .trust-text strong {
                    display: block;
                    font-size: 14px;
                    color: #1a1a1a;
                    margin-bottom: 2px
                }

                .trust-text small {
                    font-size: 12px;
                    color: #6c757d
                }

    /* Section Headers */
                .section-title {
                    font-size: 28px;
                    font-weight: 700;
                    color: #1a1a1a;
                    margin: 0
                }

                .products-header {
                    border-bottom: 2px solid #f0f0f0;
                    padding-bottom: 20px
                }

    /* Product Cards - Enhanced */
                .product-card {
                    border: none;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                    transition: all 0.3s ease;
                    background: #fff;
                    height: 100%;
                    display: flex;
                    flex-direction: column;
                    position: relative
                }

                .product-card:hover {
                    transform: translateY(-8px);
                    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15)
                }

                .product-card:hover::after {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0, 0, 0, 0.02);
                    pointer-events: none;
                    border-radius: 12px
                }

                .product-card::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: linear-gradient(135deg, rgba(255, 0, 0, 0.05) 0%, rgba(255, 0, 0, 0.02) 100%);
                    opacity: 0;
                    transition: opacity 0.3s ease;
                    pointer-events: none;
                    border-radius: 12px
                }

                .product-card:hover::before {
                    opacity: 1
                }

                .product-image-wrapper {
                    position: relative;
                    background: #f8f9fa;
                    overflow: hidden
                }

                .product-image-wrapper::before {
                    content: '';
                    position: absolute;
                    inset: 0;
                    background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.3) 100%);
                    opacity: 0;
                    transition: opacity 0.3s;
                    z-index: 1
                }

                .product-card:hover .product-image-wrapper::before {
                    opacity: 1
                }

                .product-badge {
                    position: absolute;
                    top: 10px;
                    left: 10px;
                    background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%);
                    color: #fff;
                    font-size: 10px;
                    font-weight: 700;
                    padding: 4px 12px;
                    border-radius: 50px;
                    z-index: 5;
                    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
                    letter-spacing: 0.5px;
                }

                .product-quick {
                    position: absolute;
                    right: 15px;
                    bottom: 15px;
                    display: flex;
                    gap: 10px;
                    opacity: 0;
                    transform: translateY(15px);
                    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
                    z-index: 5
                }

                .product-card:hover .product-quick {
                    opacity: 1;
                    transform: translateY(0)
                }

                .product-quick .btn {
                    border-radius: 12px;
                    width: 44px;
                    height: 44px;
                    padding: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: rgba(255, 255, 255, 0.9);
                    backdrop-filter: blur(5px);
                    border: 1px solid rgba(0,0,0,0.05);
                    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                    transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
                }

                .product-quick .btn:hover {
                    background: #dc3545;
                    color: #fff;
                    transform: scale(1.1);
                    box-shadow: 0 8px 20px rgba(220, 53, 69, 0.3);
                }

                .img-cover {
                    width: 100%;
                    height: 140px;
                    object-fit: contain;
                    transition: transform 0.8s cubic-bezier(0.165, 0.84, 0.44, 1);
                    background: #fff;
                    padding: 10px;
                }

                .product-card:hover .img-cover {
                    transform: scale(1.1);
                }

                .product-title {
                    font-size: 11px;
                    font-weight: 600;
                    margin: 0;
                    color: #1a1a1a;
                    line-height: 1.4;
                    display: -webkit-box;
                    -webkit-line-clamp: 2;
                    -webkit-box-orient: vertical;
                    overflow: hidden;
                    transition: color 0.3s ease;
                }
                
                .product-card:hover .product-title {
                    color: #dc3545;
                }

                .product-meta {
                    font-size: 11px;
                    color: #adb5bd;
                    margin-top: 5px;
                    letter-spacing: 0.5px;
                }

                .price {
                    font-weight: 800;
                    font-size: 14px;
                    color: #1a1a1a;
                    letter-spacing: -0.5px;
                }
                
                .price::after {
                    content: ' TZS';
                    font-size: 12px;
                    font-weight: 600;
                    color: #6c757d;
                    margin-left: 2px;
                }

                .price-old {
                    text-decoration: line-through;
                    color: #adb5bd;
                    font-size: 14px;
                    margin-left: 10px;
                    font-weight: 400;
                }

                .category-pill {
                    background: rgba(220, 53, 69, 0.08);
                    border-radius: 50px;
                    padding: 5px 15px;
                    font-size: 10px;
                    color: #dc3545;
                    font-weight: 700;
                    letter-spacing: 0.5px;
                    border: 1px solid rgba(220, 53, 69, 0.1);
                }

                .btn-add {
                    background: #dc3545;
                    border: none;
                    border-radius: 8px;
                    font-weight: 700;
                    letter-spacing: 0.5px;
                    font-size: 8px;
                    padding: 4px 10px;
                    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
                    box-shadow: 0 5px 15px rgba(220, 53, 69, 0.2);
                }

                .btn-add:hover {
                    background: #c82333;
                    transform: translateY(-2px);
                    box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
                }

                .toolbar-top .btn-outline-secondary {
                    font-size: 8px;
                    padding: 4px 10px;
                    border-radius: 8px;
                    font-weight: 600;
                    letter-spacing: 0.5px;
                }


                .rating {
                    display: flex;
                    align-items: center;
                    gap: 4px;
                    font-size: 14px
                }

                .rating i {
                    color: #fbbf24
                }

                .chip {
                    background: #e8f5e9;
                    color: #2e7d32;
                    border-radius: 50px;
                    padding: 4px 10px;
                    font-size: 11px;
                    font-weight: 500
                }

                .toolbar-top {
                    display: flex;
                    gap: 5px;
                    margin-bottom: 12px
                }

                .skeleton {
                    position: relative;
                    overflow: hidden;
                    background: #e9ecef
                }

                .skeleton::after {
                    content: "";
                    position: absolute;
                    inset: 0;
                    background: linear-gradient(90deg, rgba(255, 255, 255, 0) 0, rgba(255, 255, 255, 0.4) 50%, rgba(255, 255, 255, 0) 100%);
                    transform: translateX(-100%);
                    animation: shimmer 1.2s infinite
                }

                @keyframes shimmer {
                    to {
                        transform: translateX(100%)
                    }
                }

    /* Search Controls */
    .card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    
    .form-label {
        font-size: 0.875rem;
        color: #495057;
        margin-bottom: 0.25rem;
    }
    
    .form-select {
        border: 1px solid #ced4da;
        border-radius: 8px;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    
    .form-select:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }
    
    .form-control {
        border: 1px solid #ced4da;
        border-radius: 8px;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }
    
    .form-control:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }
    
    .btn {
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border: none;
    }
    
    .btn-danger:hover {
        background: linear-gradient(135deg, #c82333 0%, #a71e2a 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }
    
    .btn-outline-secondary {
        border: 1px solid #6c757d;
        color: #6c757d;
    }
    
    .btn-outline-secondary:hover {
        background: #6c757d;
        border-color: #6c757d;
        color: #fff;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .hero-split-wrapper {
            height: auto;
            flex-direction: column;
            margin: 8px 0 0 0;
        }

        .hero-split-left {
            flex: 0 0 auto;
        }

        .hero-split-right {
            flex: 0 0 auto;
            height: 180px;
        }

        .hero-slideshow {
            height: 200px;
            margin: 0;
        }

                    .hero-slide {
                        background-size: cover;
                        background-position: center;
                    }
        
        .hero-banner {
            margin: 8px 0 0 0;
        }
        /* Reduce product card vertical size on small tablets */
        .img-cover {
            height: 140px;
        }
        .product-card .p-3 {
            padding: 0.75rem !important;
        }
        .toolbar-top {
            margin-bottom: 8px;
        }
        
        .hero-slide-title {
            font-size: clamp(20px, 7vw, 28px);
        }
        
        .hero-slide-subtitle {
            font-size: clamp(12px, 3.5vw, 14px);
        }
        
        .hero-slide-btn {
            padding: 10px 20px;
            font-size: 12px;
        }

        /* General button size reduction for mobile */
        .btn {
            font-size: 0.8rem;
            padding: 0.45rem 0.8rem;
        }
        
                    .hero-banner {
                        padding: 40px 0
                    }

                    .trust-badge {
                        flex-direction: column;
                        text-align: center
                    }

                    .trust-badge i {
                        margin-bottom: 8px
                    }

                    .row>div {
            margin-bottom: 1rem;
        }
        
        .d-flex.gap-2 {
            flex-direction: column;
            gap: 0.5rem !important;
        }
        
        .d-flex.gap-2 .btn {
            width: 100%;
        }
    }
    
    @media (max-width: 480px) {
        .hero-split-wrapper {
            margin: 5px 10px 0 10px;
        }

        .hero-split-right {
            height: 140px;
        }

        .hero-slideshow {
            height: 120px;
            margin: 0;
        }
        
        .hero-banner {
            margin: 5px 0 0 0;
        }
        
        .hero-slide-title {
            font-size: clamp(16px, 5vw, 20px);
        }
        
        .hero-slide-subtitle {
            font-size: clamp(9px, 2.8vw, 11px);
        }
        
        .hero-slide-btn {
            padding: 6px 12px;
            font-size: 9px;
        }
        
        .img-cover {
            height: 120px;
            object-fit: contain;
            background: #f8f9fa;
        }
        .product-card .p-3 {
            padding: 0.5rem !important;
        }
        .toolbar-top {
            margin-bottom: 6px;
        }
        
        .product-card:hover .img-cover {
            transform: none;
        }
        
        .product-title {
            font-size: 11px;
            line-height: 1.2;
        }
        
        .price {
            font-size: 13px;
        }
        
        .product-meta {
            font-size: 8px;
        }
        
        .btn-add {
            font-size: 9px;
            padding: 3px 6px;
        }
        
        .toolbar-top {
            gap: 3px;
        }
        
        .toolbar-top .btn {
            font-size: 8px;
            padding: 2px 5px;
        }
        
        .category-pill {
            font-size: 8px;
            padding: 2px 6px;
        }
        
        .chip {
            font-size: 8px;
            padding: 2px 6px;
        }
        
        .rating {
            font-size: 10px;
        }
        
        .rating i {
            font-size: 8px;
        }
        
        /* Search Controls Mobile Optimization */
        .card-body {
            padding: 0.75rem !important;
        }
        
        .form-label {
            font-size: 0.75rem;
            margin-bottom: 0.25rem;
        }
        
        .form-select,
        .form-control {
            font-size: 0.8rem;
            padding: 0.4rem 0.5rem;
        }
        
        .btn {
            font-size: 0.75rem;
            padding: 0.4rem 0.75rem;
        }
        
        .row.g-3 {
            --bs-gutter-y: 0.5rem;
        }
        
        .col-md-3,
        .col-md-9 {
            margin-bottom: 0.5rem;
        }
        
        /* Mobile Search Layout Optimization */
        .d-flex.gap-2 {
            flex-direction: row;
            gap: 0.5rem !important;
            flex-wrap: nowrap;
        }
        
        .flex-shrink-0 {
            flex-shrink: 0;
        }
        
        .flex-grow-1 {
            flex-grow: 1;
            min-width: 0;
        }
        
        .form-label {
            margin-bottom: 0.25rem;
            font-size: 0.6rem;
        }
        
        .form-select {
            font-size: 0.65rem;
            padding: 0.25rem 0.4rem;
        }
        
        .form-control {
            font-size: 0.65rem;
            padding: 0.25rem 0.4rem;
        }
        
        .btn {
            font-size: 0.6rem;
            padding: 0.25rem 0.4rem;
            min-width: 30px;
        }
        
        .card-body {
            padding: 0.5rem !important;
        }
        
        /* Section Headers Mobile */
        .section-title {
            font-size: 20px;
        }
        
        .products-header p {
            font-size: 12px;
        }
        
        .btn-outline-danger {
            font-size: 10px;
            padding: 4px 8px;
        }
    }
</style>
<?php $__env->stopPush(); ?>

        <!-- Ad Display Styles -->
        <style>
            /* Ad Container Base Styles */
            .ad-container {
                position: relative;
                overflow: hidden;
                border-radius: 8px;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .ad-container:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            }

            .ad-content {
                position: relative;
                width: 100%;
            }

            .ad-image {
                width: 100%;
                height: auto;
                display: block;
            }

            .ad-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 100%);
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
                padding: 1rem;
                color: white;
            }

            .ad-title {
                font-size: 1.2rem;
                font-weight: 700;
                margin-bottom: 0.5rem;
                text-shadow: 0 2px 4px rgba(0,0,0,0.5);
            }

            .ad-subtitle {
                font-size: 0.9rem;
                margin-bottom: 1rem;
                text-shadow: 0 1px 2px rgba(0,0,0,0.5);
            }

            .ad-button {
                border-radius: 25px;
                padding: 0.5rem 1.5rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                transition: all 0.3s ease;
            }

            .popup-ad-text .ad-button {
                background: #ff0000 !important;
                border-color: #ff0000 !important;
                color: white !important;
                box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);
            }

            .popup-ad-text .ad-button:hover {
                background: #cc0000 !important;
                border-color: #cc0000 !important;
                transform: scale(1.05);
                box-shadow: 0 6px 20px rgba(255, 0, 0, 0.4);
            }

            .ad-button:hover {
                transform: scale(1.05);
                box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            }

            /* Banner Ads */
            .banner-ads-top,
            .banner-ads-bottom {
                /* Removed container styling - now using card structure */
            }

            /* Banner Slider Styles */
            .banner-ads-bottom {
                padding: 0 15px; /* Add horizontal padding instead of card */
            }

            .banner-slider-container {
                position: relative;
                overflow: hidden;
                border-radius: 16px;
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                padding: 1rem;
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            }

            .banner-slider-wrapper {
                position: relative;
                width: 100%;
            }

            .banner-slider {
                display: flex;
                transition: transform 0.5s ease-in-out;
                gap: 0.5rem;
            }

            .banner-slide {
                flex: 0 0 30%; /* 3 slides per view for wider ads */
                min-width: 0;
            }

            .banner-slide .ad-container {
                height: 100px;
                border-radius: 12px;
                overflow: hidden;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                position: relative;
            }

            .banner-slide .ad-content {
                position: relative;
                width: 100%;
                height: 100%;
            }

            .banner-slide .ad-container:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            }

            .banner-slide .ad-image {
                width: 100%;
                height: 100%;
                object-fit: cover;
                object-position: center;
                display: block;
                image-rendering: auto;
                image-rendering: -webkit-optimize-contrast;
                image-rendering: crisp-edges;
                image-rendering: high-quality;
                transition: transform 0.3s ease;
            }

            .banner-slide .ad-container:hover .ad-image {
                transform: scale(1.05);
            }

            /* Banner ads without images */
            .banner-slide .ad-container.no-image {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
            }

            .banner-slide .ad-container.no-image .ad-overlay.standalone-text {
                position: static;
                background: none;
                opacity: 1;
                padding: 0;
            }

            .banner-slide .ad-container.no-image .ad-title {
                color: #fff;
                text-shadow: 0 2px 4px rgba(0,0,0,0.5);
            }

            .banner-slide .ad-container.no-image .ad-subtitle {
                color: rgba(255, 255, 255, 0.9);
                text-shadow: 0 1px 2px rgba(0,0,0,0.5);
            }

            .banner-slide .ad-container.no-image .ad-button {
                background: #ff0000 !important;
                border-color: #ff0000 !important;
                color: white !important;
                box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);
            }

            .banner-slide .ad-container.no-image .ad-button:hover {
                background: #cc0000 !important;
                border-color: #cc0000 !important;
                transform: scale(1.05);
                box-shadow: 0 6px 20px rgba(255, 0, 0, 0.4);
            }

            .banner-slide .ad-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 100%);
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
                padding: 1rem;
                color: white;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .banner-slide .ad-container:hover .ad-overlay {
                opacity: 1;
            }

            .banner-slide .ad-title {
                font-size: 0.85rem;
                font-weight: 700;
                margin-bottom: 0.4rem;
                text-shadow: 0 2px 4px rgba(0,0,0,0.5);
            }

            .banner-slide .ad-subtitle {
                font-size: 0.7rem;
                margin-bottom: 0.8rem;
                text-shadow: 0 1px 2px rgba(0,0,0,0.5);
            }

            .banner-slide .ad-button {
                border-radius: 25px;
                padding: 0.4rem 1rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.3px;
                transition: all 0.3s ease;
                font-size: 0.65rem;
            }

            .banner-slide .ad-button:hover {
                transform: scale(1.05);
                box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            }

            /* Sidebar Filter Styles */
            .sticky-sidebar {
                position: sticky;
                top: 100px;
                height: fit-content;
            }

            .list-group-item {
                border: none;
                padding: 10px 15px;
                font-size: 0.85rem;
                font-weight: 500;
                transition: all 0.2s ease;
            }

            .list-group-item:not(.active):hover {
                background-color: #f8f9fa;
                color: #dc3545;
                padding-left: 25px;
            }

            .list-group-item.active {
                box-shadow: 0 4px 10px rgba(220, 53, 69, 0.2);
            }

            @media (max-width: 991px) {
                .sticky-sidebar {
                    position: static;
                    margin-bottom: 2rem;
                }
                
                .list-group {
                    display: flex;
                    flex-direction: row;
                    overflow-x: auto;
                    white-space: nowrap;
                    padding-bottom: 10px;
                    gap: 10px;
                }
                
                .list-group-item {
                    border: 1px solid #eee !important;
                    border-radius: 50px !important;
                    flex: 0 0 auto;
                }

                .list-group-item .badge {
                    display: none;
                }
            }
            /* Slider Controls */
            .banner-slider-prev,
            .banner-slider-next {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                background: rgba(255, 255, 255, 0.9);
                border: none;
                color: #333;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                cursor: pointer;
                transition: all 0.3s ease;
                backdrop-filter: blur(10px);
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                z-index: 10;
            }

            .banner-slider-prev {
                left: -25px;
            }

            .banner-slider-next {
                right: -25px;
            }

            .banner-slider-prev:hover,
            .banner-slider-next:hover {
                background: #ff0000;
                color: white;
                transform: translateY(-50%) scale(1.1);
                box-shadow: 0 6px 20px rgba(255, 0, 0, 0.3);
            }

            /* Slider Indicators */
            .banner-slider-indicators {
                position: absolute;
                bottom: -40px;
                left: 50%;
                transform: translateX(-50%);
                display: flex;
                gap: 10px;
                z-index: 10;
            }

            .banner-slider-indicator {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                border: 2px solid rgba(255, 0, 0, 0.3);
                background: transparent;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .banner-slider-indicator.active {
                background: #ff0000;
                border-color: #ff0000;
            }

            .banner-slider-indicator:hover {
                background: rgba(255, 0, 0, 0.5);
                border-color: rgba(255, 0, 0, 0.5);
            }

            /* Sidebar Ads */
            .sidebar-ads-left,
            .sidebar-ads-right {
                position: sticky;
                top: 100px;
            }

            .sidebar-ads-left .ad-container,
            .sidebar-ads-right .ad-container {
                margin-bottom: 1.5rem;
            }

            /* Inline Ads */
            .inline-ads {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                border-radius: 12px;
                padding: 2rem;
                border: 2px dashed #dee2e6;
                text-align: center;
            }

            .inline-ads .ad-overlay {
                position: static;
                background: none;
                color: #333;
                padding: 0;
            }

            .inline-ads .ad-title {
                color: #333;
                text-shadow: none;
            }

            .inline-ads .ad-subtitle {
                color: #666;
                text-shadow: none;
            }

            /* Popup Ads */
            .popup-ad-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: transparent;
                z-index: 9999;
                display: none;
                padding: 1rem;
            }

            /* Popup Position Classes */
            .popup-ad-modal.position-center {
                justify-content: center;
                align-items: center;
            }

            .popup-ad-modal.position-top {
                justify-content: center;
                align-items: flex-start;
                padding-top: 2rem;
            }

            .popup-ad-modal.position-bottom {
                justify-content: center;
                align-items: flex-end;
                padding-bottom: 2rem;
            }

            .popup-ad-modal.position-left {
                justify-content: flex-start;
                align-items: center;
                padding-left: 2rem;
            }

            .popup-ad-modal.position-right {
                justify-content: flex-end;
                align-items: center;
                padding-right: 2rem;
            }

            .popup-ad-modal.show {
                display: flex;
                animation: popupFadeIn 0.3s ease-out;
            }

            @keyframes popupFadeIn {
                from {
                    opacity: 0;
                    transform: scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }

            .popup-ad-content {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 12px;
                max-width: 600px;
                width: 100%;
                max-height: 35vh;
                overflow: hidden;
                position: relative;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }

            .popup-ad-close {
                position: absolute;
                top: 10px;
                right: 15px;
                background: none;
                border: none;
                font-size: 1.5rem;
                color: #666;
                cursor: pointer;
                z-index: 10;
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                transition: all 0.3s ease;
            }

            .popup-ad-close:hover {
                background: #f8f9fa;
                color: #333;
            }

            .popup-ad-body {
                padding: 0;
                position: relative;
                height: 100%;
                min-height: 150px;
            }

            /* Popup with image */
            .popup-ad-body.has-image {
                padding: 0;
            }

            .popup-ad-body.has-image .ad-image {
                width: 100%;
                height: 100%;
                object-fit: cover;
                border-radius: 12px;
            }

            .popup-ad-body.has-image .popup-ad-text.overlay-text {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                text-align: center;
                background: rgba(0, 0, 0, 0.7);
                padding: 1rem;
                border-radius: 8px;
                width: 90%;
                backdrop-filter: blur(5px);
            }

            /* Popup without image */
            .popup-ad-body.no-image {
                padding: 2rem;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .popup-ad-body.no-image .popup-ad-text.standalone-text {
                position: static;
                transform: none;
                text-align: center;
                background: none;
                padding: 0;
                width: 100%;
                backdrop-filter: none;
            }

            .popup-ad-body.no-image .ad-title {
                color: #fff;
                text-shadow: 0 2px 4px rgba(0,0,0,0.5);
            }

            .popup-ad-body.no-image .ad-subtitle {
                color: rgba(255, 255, 255, 0.9);
                text-shadow: 0 1px 2px rgba(0,0,0,0.5);
            }

            .popup-ad-body.no-image .ad-button {
                background: #ff0000 !important;
                border-color: #ff0000 !important;
                color: white !important;
                box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);
            }

            .popup-ad-body.no-image .ad-button:hover {
                background: #cc0000 !important;
                border-color: #cc0000 !important;
                transform: scale(1.05);
                box-shadow: 0 6px 20px rgba(255, 0, 0, 0.4);
            }

            .popup-ad-text .ad-title {
                color: #fff;
                text-shadow: 0 2px 4px rgba(0,0,0,0.5);
                margin-bottom: 0.3rem;
                font-size: 1rem;
                font-weight: 700;
            }

            .popup-ad-text .ad-subtitle {
                color: rgba(255, 255, 255, 0.9);
                text-shadow: 0 1px 2px rgba(0,0,0,0.5);
                margin-bottom: 0.6rem;
                font-size: 0.75rem;
            }

            /* Tablet Responsive */
            @media (max-width: 1024px) and (min-width: 769px) {
                .popup-ad-content {
                    max-width: 80%;
                    max-height: 40vh;
                }

                .popup-ad-body {
                    min-height: 140px;
                }
            }

            /* Mobile Responsive */
            @media (max-width: 768px) {
                .ad-title {
                    font-size: 1rem;
                }

                .ad-subtitle {
                    font-size: 0.8rem;
                }

                .ad-button {
                    padding: 0.4rem 1rem;
                    font-size: 0.8rem;
                }

                .sidebar-ads-left,
                .sidebar-ads-right {
                    display: none;
                }

                .popup-ad-content {
                    max-width: 98%;
                    margin: 0.5rem;
                    max-height: 40vh;
                }

                .popup-ad-body {
                    min-height: 120px;
                }

                .popup-ad-body.has-image .popup-ad-text.overlay-text {
                    padding: 0.8rem;
                    width: 95%;
                }

                .popup-ad-body.no-image {
                    padding: 1.5rem;
                }

                .popup-ad-body.no-image .popup-ad-text.standalone-text {
                    padding: 0;
                    width: 100%;
                }

                .popup-ad-text .ad-title {
                    font-size: 0.9rem;
                }

                .popup-ad-text .ad-subtitle {
                    font-size: 0.7rem;
                }

                .popup-ad-text .ad-button {
                    padding: 0.35rem 0.8rem;
                    font-size: 0.7rem;
                }

                .inline-ads {
                    padding: 1rem;
                }

                /* Mobile popup positioning adjustments */
                .popup-ad-modal.position-left,
                .popup-ad-modal.position-right {
                    justify-content: center;
                    align-items: center;
                }

                .popup-ad-modal.position-top {
                    padding-top: 1rem;
                }

                .popup-ad-modal.position-bottom {
                    padding-bottom: 1rem;
                }
            }

            @media (max-width: 576px) {
                .popup-ad-content {
                    max-width: 99%;
                    margin: 0.25rem;
                    max-height: 35vh;
                }

                .popup-ad-body {
                    min-height: 100px;
                }

                .popup-ad-body.has-image .popup-ad-text.overlay-text {
                    padding: 0.6rem;
                    width: 98%;
                }

                .popup-ad-body.no-image {
                    padding: 1rem;
                }

                .popup-ad-body.no-image .popup-ad-text.standalone-text {
                    padding: 0;
                    width: 100%;
                }

                .popup-ad-text .ad-title {
                    font-size: 1rem;
                }

                .popup-ad-text .ad-subtitle {
                    font-size: 0.75rem;
                }

                .popup-ad-text .ad-button {
                    padding: 0.3rem 0.8rem;
                    font-size: 0.75rem;
                }

                .banner-ads-top,
                .banner-ads-bottom {
                    /* Removed container styling - now using card structure */
                }

                /* Mobile Banner Slider */
                .banner-ads-bottom .card-body {
                    padding: 0 !important;
                }

                .banner-slider-container {
                    padding: 0.5rem;
                    margin: 0;
                }

                .banner-slider {
                    gap: 0.4rem;
                }

                .banner-slide {
                    flex: 0 0 48%; /* 2 slides per view on mobile */
                    max-width: 48%;
                    margin: 0 1%;
                }

                .banner-slide .ad-container {
                    height: 90px; /* Reduced height */
                }

                .banner-slide .ad-title {
                    font-size: 0.75rem;
                }

                .banner-slide .ad-subtitle {
                    font-size: 0.65rem;
                }

                .banner-slide .ad-button {
                    padding: 0.3rem 0.8rem;
                    font-size: 0.6rem;
                }

                .banner-slider-prev,
                .banner-slider-next {
                    width: 40px;
                    height: 40px;
                    font-size: 14px;
                }

                .banner-slider-prev {
                    left: -10px;
                    z-index: 10;
                }

                .banner-slider-next {
                    right: -10px;
                    z-index: 10;
                }

                .ad-overlay {
                    padding: 0.5rem;
                }

                .ad-title {
                    font-size: 0.9rem;
                }

                .ad-subtitle {
                    font-size: 0.75rem;
                }

                .ad-button {
                    padding: 0.3rem 0.8rem;
                    font-size: 0.75rem;
                }
            }
        </style>
    <!-- Filters & Products Section -->
    <div class="container">
        <div class="products-header mb-4" data-aos="fade-up">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h2 class="section-title mb-0"><?php echo e($channel === 'wholesale' ? 'wholesale products' : 'Our Products'); ?></h2>
                <a href="<?php echo e($channel === 'wholesale' ? route('wholesale.products') : route('products.index')); ?>"
                    class="btn btn-danger btn-sm rounded-pill px-4">
                    view all<i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
            <p class="text-muted small mb-0">Discover our range of premium products & solutions</p>
        </div>

        <div class="row g-4" id="products-grid">
            <!-- Sidebar: Categories & Filters -->
            <?php if(!request()->is('/') || request()->has('category')): ?>
            <div class="col-12 col-lg-2">
                <div class="sticky-sidebar">
                    <!-- Categories Filter -->
                    <div class="card mb-4 border-0 shadow-sm overflow-hidden">
                        <div class="card-header bg-dark text-white py-3">
                            <h5 class="card-title mb-0" style="font-size: 1rem;"><i class="fas fa-list me-2"></i>Categories</h5>
                        </div>
                        <div class="list-group list-group-flush">
                            <a href="<?php echo e(request()->fullUrlWithQuery(['category' => null])); ?>" class="list-group-item list-group-item-action <?php echo e(!request('category') ? 'active bg-danger border-danger' : ''); ?> d-flex justify-content-between align-items-center">
                                All Categories
                                <span class="badge rounded-pill bg-light text-dark">All</span>
                            </a>
                            <?php $__currentLoopData = $allCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e(request()->fullUrlWithQuery(['category' => $cat->name])); ?>" class="list-group-item list-group-item-action <?php echo e(request('category') == $cat->name ? 'active bg-danger border-danger' : ''); ?> d-flex justify-content-between align-items-center">
                                    <?php echo e($cat->name); ?>

                                    <span class="badge rounded-pill <?php echo e(request('category') == $cat->name ? 'bg-white text-danger' : 'bg-light text-muted'); ?>">
                                        <?php echo e($cat->products_count ?? ''); ?>

                                    </span>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <!-- Sidebar Ads -->
                    <?php if($sidebarAds->where('ad_position', 'left')->count() > 0): ?>
                        <div class="sidebar-ads-left">
                            <?php $__currentLoopData = $sidebarAds->where('ad_position', 'left'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="ad-container mb-3" data-ad-id="<?php echo e($ad->id); ?>" data-ad-type="sidebar">
                                    <div class="ad-content">
                                        <?php if($ad->image_path): ?>
                                            <img src="<?php echo e(asset('storage/' . $ad->image_path)); ?>" 
                                                 alt="<?php echo e($ad->title); ?>" 
                                                 class="img-fluid w-100 ad-image"
                                                 style="border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                        <?php endif; ?>
                                        <?php if($ad->title || $ad->subtitle): ?>
                                            <div class="ad-overlay">
                                                <?php if($ad->title): ?>
                                                    <h6 class="ad-title"><?php echo e($ad->title); ?></h6>
                                                <?php endif; ?>
                                                <?php if($ad->subtitle): ?>
                                                    <p class="ad-subtitle small"><?php echo e($ad->subtitle); ?></p>
                                                <?php endif; ?>
                                                <?php if($ad->button_text && $ad->button_url): ?>
                                                    <a href="<?php echo e($ad->button_url); ?>" 
                                                       class="btn btn-sm btn-primary ad-button"
                                                       style="background-color: <?php echo e($ad->button_color); ?>; border-color: <?php echo e($ad->button_color); ?>;"
                                                       onclick="trackAdClick(<?php echo e($ad->id); ?>)">
                                                        <?php echo e($ad->button_text); ?>

                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Products Column -->
            <div class="col-12 <?php echo e((!request()->is('/') || request()->has('category')) ? 'col-lg-10' : 'col-lg-12'); ?>">
                <div class="row g-2 row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6">
                    <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <!-- Inline Ads - Show after every 8 products -->
                        <?php if($index > 0 && $index % 8 === 0 && $inlineAds->count() > 0): ?>
                            <div class="col-12 mb-4">
                                <div class="inline-ads">
                                    <?php $__currentLoopData = $inlineAds->take(1); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="ad-container" data-ad-id="<?php echo e($ad->id); ?>" data-ad-type="inline">
                                            <div class="ad-content text-center">
                                                <?php if($ad->image_path): ?>
                                                    <img src="<?php echo e(asset('storage/' . $ad->image_path)); ?>" 
                                                         alt="<?php echo e($ad->title); ?>" 
                                                         class="img-fluid ad-image"
                                                         style="max-height: 200px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                                <?php endif; ?>
                                                <?php if($ad->title || $ad->subtitle): ?>
                                                    <div class="ad-overlay mt-3">
                                                        <?php if($ad->title): ?>
                                                            <h5 class="ad-title"><?php echo e($ad->title); ?></h5>
                                                        <?php endif; ?>
                                                        <?php if($ad->subtitle): ?>
                                                            <p class="ad-subtitle"><?php echo e($ad->subtitle); ?></p>
                                                        <?php endif; ?>
                                                        <?php if($ad->button_text && $ad->button_url): ?>
                                                            <a href="<?php echo e($ad->button_url); ?>" 
                                                               class="btn btn-primary ad-button"
                                                               style="background-color: <?php echo e($ad->button_color); ?>; border-color: <?php echo e($ad->button_color); ?>;"
                                                               onclick="trackAdClick(<?php echo e($ad->id); ?>)">
                                                                <?php echo e($ad->button_text); ?>

                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endif; ?>

    <div class="col" data-aos="fade-up" data-aos-delay="<?php echo e(($index % 6) * 100); ?>">
        <?php 
            // Define viewUrl first
            $viewUrl = '';
            if ($channel === 'wholesale') {
                $viewUrl = !empty($product->barcode)
                    ? route('wholesale.product.show', $product->barcode)
                    : route('wholesale.products.show', $product);
            } else {
                $viewUrl = !empty($product->barcode)
                    ? route('product.show', $product->barcode)
                    : route('products.show', $product);
            }
            
            // Debug: Log image information
                        echo "<!-- Debug: Product {$product->id} - Images count: " . $product->images->count() . ' -->';
            // Determine base price depending on channel
            if ($channel === 'wholesale') {
                            $basePrice =
                                $product->b2b_base_price ??
                                ($product->wholesale_price ?? ($product->buying_price ?? 0));
            } else {
                // For retail: use retail_base_price from enhanced_products table
                            $basePrice = $product->retail_base_price ?? ($product->buying_price ?? 0);
            }
            
            // Get offers for this product
            $offer = null;
            $discountAmount = 0;
            $displayPrice = $basePrice;
            $hasDiscount = false;
            
            if ($channel === 'wholesale') {
                            $offer =
                                $product->offers->where('target_channel', 'wholesale')->first() ??
                         $product->offers->where('target_channel', 'both')->first();
            } else {
                            $offer =
                                $product->offers->where('target_channel', 'retail')->first() ??
                         $product->offers->where('target_channel', 'both')->first();
            }
            
            if ($offer) {
                if ($offer->offer_type === 'percentage') {
                    $discountAmount = ($basePrice * $offer->discount_value) / 100;
                } elseif ($offer->offer_type === 'fixed') {
                    $discountAmount = $offer->discount_value;
                }
                $displayPrice = $basePrice - $discountAmount;
                $hasDiscount = true;
            }
            
            $base = $basePrice;
            // Clean display name (remove trailing numeric ids like " ... 13")
            $displayName = preg_replace('/\s+\d+$/', '', (string) $product->name);
        ?>
                    <div class="product-card h-100" onclick="navigateToProduct('<?php echo e($viewUrl); ?>')"
                        style="cursor: pointer;">
            <div class="product-image-wrapper">
                            <?php if($product->images->count() > 0): ?>
                    <img src="<?php echo e(asset('storage/' . $product->images->first()->image_path)); ?>" 
                                    class="img-cover" alt="<?php echo e($displayName); ?>">
                <?php else: ?>
                    <div class="bg-light d-flex align-items-center justify-content-center img-cover">
                        <i class="fas fa-image text-muted" style="font-size: 2rem;"></i>
                    </div>
                <?php endif; ?>
                            <?php if($hasDiscount): ?>
                    <span class="product-badge">Best price</span>
                <?php endif; ?>
            </div>
            <div class="p-3">
                <div class="toolbar-top" onclick="event.stopPropagation();">
                                <button class="btn btn-add btn-sm text-white" data-bs-toggle="modal"
                                    data-bs-target="#addToCartModal-<?php echo e($product->id); ?>"><i
                                        class="fas fa-cart-plus me-1"></i>Place Order</button>
                    <a href="<?php echo e($viewUrl); ?>" class="btn btn-outline-secondary btn-sm">Details</a>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <h3 class="product-title"><?php echo e($displayName); ?></h3>
                                <?php if(!empty($product->category)): ?>
                                    <span
                                        class="category-pill"><?php echo e(is_object($product->category) ? $product->category->name ?? '' : $product->category); ?></span>
                                        <?php endif; ?>
                                    </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="rating">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                        class="fas fa-star"></i><i class="far fa-star"></i>
                    </div>
                    <span class="chip">Fast delivery</span>
                                    </div>
                <div class="mb-2">
                                <?php if($hasDiscount): ?>
                        <div class="text-center">
                            <span class="badge bg-success mb-1">Special Offer!</span>
                                        <div class="price text-success fw-bold"><?php echo e(number_format((float) $displayPrice)); ?>

                                            TZS</div>
                            <div class="small text-muted">
                                            <del class="text-muted"><?php echo e(number_format((float) $base)); ?> TZS</del>
                              
                            </div>
                            <div class="small text-success">
                                            <?php if($offer->offer_type === 'percentage'): ?>
                                    <?php echo e($offer->discount_value); ?>% OFF
                                <?php else: ?>
                                    <?php echo e(number_format($offer->discount_value)); ?> TZS OFF
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                                    <span class="price"><?php echo e(number_format((float) $displayPrice)); ?> TZS</span>
                    <?php endif; ?>
                </div>
                <div class="product-meta"><?php echo e($product->barcode); ?></div>
                                </div>
                            </div>
    
    <?php $__env->startPush('modals'); ?>
    <!-- Add to Cart Modal (per product) -->
    <div class="modal fade" id="addToCartModal-<?php echo e($product->id); ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Place Order - <?php echo e($displayName); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                                            <?php if($product->images->count() > 0): ?>
                                <img src="<?php echo e(asset('storage/' . $product->images->first()->image_path)); ?>" 
                                                    class="img-fluid rounded" alt="<?php echo e($displayName); ?>"
                                     style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center img-fluid rounded" 
                                     style="height: 200px;">
                                    <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                                            <h6 class="mb-2">Price
                                                (<?php echo e($channel === 'wholesale' ? 'Wholesale' : 'Retail'); ?>)</h6>
                            <?php 
                                $modalPrice = $displayPrice; // Use the already calculated price with discounts
                            ?>
                            <div class="alert alert-success mb-3">
                                <h4 class="mb-0"><?php echo e(number_format($modalPrice, 0)); ?> TZS per unit</h4>
                                                <?php if($hasDiscount): ?>
                                    <small class="text-muted">
                                        <del><?php echo e(number_format($base, 0)); ?> TZS</del> 
                                     
                                    </small>
                                <?php endif; ?>
                            </div>

                                            <?php if(method_exists($product, 'variantCategories') && $product->variantCategories && $product->variantCategories->count()): ?>
                                                <h6 class="mt-3 mb-3 fw-bold">Variants</h6>
                                                <?php $__currentLoopData = $product->variantCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="variant-section mb-3">
                                                        <label class="variant-label"><?php echo e($cat->category); ?></label>
                                                        <div class="variant-options">
                                                            <?php $__currentLoopData = $cat->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <?php
                                                                    $variantPrice =
                                                                        $channel === 'wholesale'
                                                                            ? $item->wholesale_price ?? $item->price
                                                                            : $item->retail_price ?? $item->price;
                                                ?>
                                                                <button type="button" class="variant-option-btn"
                                                                    data-product-id="<?php echo e($product->id); ?>"
                                                                    data-category="<?php echo e($cat->category); ?>"
                                                                    data-name="<?php echo e($item->name); ?>"
                                                                    data-price="<?php echo e((float) $variantPrice); ?>">
                                                                    <span class="variant-name"><?php echo e($item->name); ?></span>
                                                                    <?php if($variantPrice > 0): ?>
                                                                        <span
                                                                            class="variant-price">+<?php echo e(number_format($variantPrice, 0)); ?></span>
                                                    <?php endif; ?>
                                                </button>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>

                                            <!-- Mobile-Optimized Quantity and Pricing Section -->
                                            <div class="mt-3">
                                                <!-- Quantity Input Row -->
                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label for="qty-<?php echo e($product->id); ?>" class="form-label mb-1" style="font-size: 0.8rem;">Quantity</label>
                                                        <div class="input-group">
                                                            <button class="btn btn-outline-secondary" type="button" onclick="decreaseQty('<?php echo e($product->id); ?>')" style="font-size: 1rem; padding: 0.5rem 0.75rem; font-weight: 600;">-</button>
                                                            <?php
                                                                $minOrderQty = $product->min_quantity ?? 1;
                                                            ?>
                                                            <input id="qty-<?php echo e($product->id); ?>" type="number" class="form-control text-center qty-input" 
                                                                   value="<?php echo e($minOrderQty); ?>" min="<?php echo e($minOrderQty); ?>" data-product-id="<?php echo e($product->id); ?>" 
                                                                   style="font-size: 1.2rem; padding: 0.5rem 0.3rem; font-weight: 700;">
                                                            <button class="btn btn-outline-secondary" type="button" onclick="increaseQty('<?php echo e($product->id); ?>')" style="font-size: 1rem; padding: 0.5rem 0.75rem; font-weight: 600;">+</button>
                                </div>
                            </div>
                                                    <div class="col-6">
                                                        <div class="text-end">
                                                            <div class="small text-muted" style="font-size: 0.7rem;">Unit Price</div>
                                                            <div class="fw-bold" id="unit-<?php echo e($product->id); ?>" style="font-size: 0.9rem;">0 TZS</div>
                        </div>
                    </div>
                </div>
                                                
                                                <!-- Total Price Row -->
                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <div class="small text-muted" style="font-size: 0.7rem;">Total Amount</div>
                                                        <div class="fw-bold text-primary" id="total-<?php echo e($product->id); ?>" style="font-size: 1.1rem;">0 TZS</div>
                                                    </div>
                                                    <div class="col-6">
                                                        <button class="btn btn-danger w-100 add-to-cart-confirm"
                                                                data-product-id="<?php echo e($product->id); ?>"
                                                                data-barcode="<?php echo e($product->barcode); ?>"
                                                                data-name="<?php echo e($product->name); ?>" 
                                                                data-channel="<?php echo e($channel); ?>"
                                                                data-base="<?php echo e((float) ($channel === 'wholesale' ? $product->b2b_base_price ?? ($product->buying_price ?? 0) : $product->retail_base_price ?? ($product->buying_price ?? 0))); ?>"
                                                                data-minqty="<?php echo e((int) ($product->min_quantity ?? 1)); ?>"
                                                                data-bs-dismiss="modal"
                                                                style="font-size: 0.8rem; padding: 0.5rem 0.75rem;">
                                                            <i class="fas fa-cart-plus me-1"></i>Place Order
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
            </div>
        </div>
    </div>
    <?php $__env->stopPush(); ?>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="col-12">
                            <div class="empty-state text-center py-5">
                                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                                <h3 class="text-muted">No Products Found</h3>
                                <p class="text-muted">We couldn't find any products matching your criteria.</p>
                        <a href="<?php echo e($channel === 'wholesale' ? route('wholesale.products') : route('products.index')); ?>"
                            class="btn btn-danger mt-3">
                                    <i class="fas fa-arrow-left me-2"></i>view all products
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
</div>

            <!-- Right Sidebar Ads -->
            <?php if($sidebarAds->where('ad_position', 'right')->count() > 0): ?>
                <div class="col-12 col-lg-2 d-none d-lg-block">
                    <div class="sidebar-ads-right mt-4">
                        <div class="card border-0 shadow-sm overflow-hidden">
                            <div class="card-header bg-dark text-white">
                                <h6 class="mb-0">Featured Ads</h6>
                            </div>
                            <div class="card-body p-2">
                                <?php $__currentLoopData = $sidebarAds->where('ad_position', 'right'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="ad-container mb-3" data-ad-id="<?php echo e($ad->id); ?>" data-ad-type="sidebar">
                                        <div class="ad-content">
                                            <?php if($ad->image_path): ?>
                                                <img src="<?php echo e(asset('storage/' . $ad->image_path)); ?>" 
                                                     alt="<?php echo e($ad->title); ?>" 
                                                     class="img-fluid w-100 ad-image"
                                                     style="border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                            <?php endif; ?>
                                            <?php if($ad->title || $ad->subtitle): ?>
                                                <div class="ad-overlay">
                                                    <?php if($ad->title): ?>
                                                        <h6 class="ad-title"><?php echo e($ad->title); ?></h6>
                                                    <?php endif; ?>
                                                    <?php if($ad->subtitle): ?>
                                                        <p class="ad-subtitle small"><?php echo e($ad->subtitle); ?></p>
                                                    <?php endif; ?>
                                                    <?php if($ad->button_text && $ad->button_url): ?>
                                                        <a href="<?php echo e($ad->button_url); ?>" 
                                                           class="btn btn-sm btn-primary ad-button"
                                                           style="background-color: <?php echo e($ad->button_color); ?>; border-color: <?php echo e($ad->button_color); ?>;"
                                                           onclick="trackAdClick(<?php echo e($ad->id); ?>)">
                                                            <?php echo e($ad->button_text); ?>

                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>


    <!-- CTA Cards Section (Home Page Only) -->
    <?php if(request()->is('/') && !request()->has('category')): ?>
    <section class="py-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <!-- CTA Card 1 -->
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="cta-card cta-wholesale">
                        <div class="cta-content">
                            <span class="cta-badge">B2B Portal</span>
                            <h3 class="cta-title">Bulk Wholesale</h3>
                            <p class="cta-text">Access exclusive tiered pricing and professional branding solutions for your business.</p>
                            <a href="<?php echo e(url('/b2b')); ?>" class="btn cta-btn">Enter Wholesale</a>
                        </div>
                    </div>
                </div>
                <!-- CTA Card 2 -->
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="cta-card cta-printing">
                        <div class="cta-content">
                            <span class="cta-badge">Services</span>
                            <h3 class="cta-title">Custom Printing</h3>
                            <p class="cta-text">From business cards to large format banners, we bring your vision to life with precision.</p>
                            <a href="<?php echo e(url('/services')); ?>" class="btn cta-btn">Explore Services</a>
                        </div>
                    </div>
                </div>
                <!-- CTA Card 3 -->
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="cta-card cta-support">
                        <div class="cta-content">
                            <span class="cta-badge">Support</span>
                            <h3 class="cta-title">Need a Quote?</h3>
                            <p class="cta-text">Get in touch with our expert team for custom branding requirements and bulk quotes.</p>
                            <a href="https://wa.me/<?php echo e($dynamicWhatsapp ?? '255655392319'); ?>" class="btn cta-btn">Chat with Us</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .cta-card {
            position: relative;
            border-radius: 20px;
            padding: 40px 30px;
            height: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            min-height: 320px;
            transition: all 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);
            color: #fff;
            border: none;
        }

        .cta-wholesale { 
            background: linear-gradient(135deg, #1a1a1a 0%, #434343 100%); 
        }
        .cta-printing { 
            background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%); 
        }
        .cta-support { 
            background: linear-gradient(135deg, #25d366 0%, #128c7e 100%); 
        }

        .cta-card::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transition: all 0.5s ease;
        }

        .cta-card:hover::before {
            transform: scale(1.5);
            background: rgba(255,255,255,0.15);
        }

        .cta-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
        }

        .cta-badge {
            display: inline-block;
            padding: 6px 14px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 20px;
            letter-spacing: 1px;
            width: fit-content;
        }

        .cta-title {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 12px;
            line-height: 1.2;
        }

        .cta-text {
            font-size: 15px;
            opacity: 0.85;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .cta-btn {
            background: #fff;
            color: #000 !important;
            border: none;
            font-weight: 700;
            border-radius: 50px;
            padding: 12px 30px;
            font-size: 13px;
            width: fit-content;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            text-decoration: none;
        }

        .cta-btn:hover {
            background: #000;
            color: #fff !important;
            transform: translateX(5px);
        }
    </style>
    <?php endif; ?>

    <!-- Banner Ads with Slider (bottom) -->
    <?php if($bannerAds->where('ad_position', 'bottom')->count() > 0): ?>
        <div class="container">
            <div class="banner-ads-bottom mt-4 mb-4" data-aos="fade-up">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="banner-slider-container">
                            <div class="banner-slider-wrapper">
                                <div class="banner-slider" id="bannerSlider">
                                    <?php $__currentLoopData = $bannerAds->where('ad_position', 'bottom'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="banner-slide" data-ad-id="<?php echo e($ad->id); ?>" data-ad-type="banner">
                                            <div class="ad-container <?php echo e($ad->image_path ? 'has-image' : 'no-image'); ?>">
                                                <div class="ad-content">
                                                    <?php if($ad->image_path): ?>
                                                        <img src="<?php echo e(asset('storage/' . $ad->image_path)); ?>" 
                                                             alt="<?php echo e($ad->title); ?>" 
                                                             class="ad-image"
                                                             loading="lazy">
                                                    <?php endif; ?>
                                                    <div class="ad-overlay <?php echo e($ad->image_path ? 'overlay-text' : 'standalone-text'); ?>">
                                                        <?php if($ad->title): ?>
                                                            <h5 class="ad-title"><?php echo e($ad->title); ?></h5>
                                                        <?php endif; ?>
                                                        <?php if($ad->subtitle): ?>
                                                            <p class="ad-subtitle"><?php echo e($ad->subtitle); ?></p>
                                                        <?php endif; ?>
                                                        <?php if($ad->button_text && $ad->button_url): ?>
                                                            <a href="<?php echo e($ad->button_url); ?>" 
                                                               class="btn btn-primary ad-button"
                                                               style="background-color: <?php echo e($ad->button_color); ?>; border-color: <?php echo e($ad->button_color); ?>;"
                                                               onclick="trackAdClick(<?php echo e($ad->id); ?>)">
                                                                <?php echo e($ad->button_text); ?>

                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <?php if($bannerAds->where('ad_position', 'bottom')->count() > 4): ?>
                                    <button class="banner-slider-prev" id="bannerSliderPrev">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button class="banner-slider-next" id="bannerSliderNext">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Popup Ads -->
    <?php if($popupAds->count() > 0): ?>
        <?php $__currentLoopData = $popupAds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="popup-ad-modal position-<?php echo e($ad->ad_position ?? 'center'); ?>" id="popupAd<?php echo e($ad->id); ?>" data-ad-id="<?php echo e($ad->id); ?>" data-ad-type="popup" data-duration="<?php echo e($ad->ad_duration); ?>">
                <div class="popup-ad-content">
                    <?php if($ad->ad_closable): ?>
                        <button class="popup-ad-close" onclick="closePopupAd(<?php echo e($ad->id); ?>)">
                            <i class="fas fa-times"></i>
                        </button>
                    <?php endif; ?>
                    <div class="popup-ad-body <?php echo e($ad->image_path ? 'has-image' : 'no-image'); ?>">
                        <?php if($ad->image_path): ?>
                            <img src="<?php echo e(asset('storage/' . $ad->image_path)); ?>" 
                                 alt="<?php echo e($ad->title); ?>" 
                                 class="img-fluid w-100 ad-image"
                                 style="border-radius: 8px;">
                        <?php endif; ?>
                        <?php if($ad->title || $ad->subtitle || ($ad->button_text && $ad->button_url)): ?>
                            <div class="popup-ad-text <?php echo e($ad->image_path ? 'overlay-text' : 'standalone-text'); ?>">
                                <?php if($ad->title): ?>
                                    <h4 class="ad-title"><?php echo e($ad->title); ?></h4>
                                <?php endif; ?>
                                <?php if($ad->subtitle): ?>
                                    <p class="ad-subtitle"><?php echo e($ad->subtitle); ?></p>
                                <?php endif; ?>
                                <?php if($ad->button_text && $ad->button_url): ?>
                                    <a href="<?php echo e($ad->button_url); ?>" 
                                       class="btn btn-primary ad-button"
                                       style="background-color: <?php echo e($ad->button_color); ?>; border-color: <?php echo e($ad->button_color); ?>;"
                                       onclick="trackAdClick(<?php echo e($ad->id); ?>)">
                                        <?php echo e($ad->button_text); ?>

                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>

                <!-- Pagination -->
    <?php if(method_exists($products, 'links')): ?>
    <div class="mt-4 d-none"><?php echo e($products->links()); ?></div>
<?php endif; ?>

    <!-- Ad Tracking and Display JavaScript -->
    <script>
        // Track ad impressions when they come into view
        function trackAdImpression(adId) {
            fetch(`/hero-slides/${adId}/track-impression`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            }).catch(error => {
                console.log('Ad impression tracking failed:', error);
            });
        }

        // Track ad clicks
        function trackAdClick(adId) {
            fetch(`/hero-slides/${adId}/track-click`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            }).catch(error => {
                console.log('Ad click tracking failed:', error);
            });
        }

        // Close popup ad
        function closePopupAd(adId) {
            const popup = document.getElementById(`popupAd${adId}`);
            if (popup) {
                popup.classList.remove('show');
                popup.style.display = 'none';
            }
        }

        // Show popup ads with delay and auto-close
        function showPopupAds() {
            const popupAds = document.querySelectorAll('.popup-ad-modal');
            console.log('Found popup ads:', popupAds.length);
            
            popupAds.forEach((popup, index) => {
                const adId = popup.getAttribute('data-ad-id');
                const duration = popup.getAttribute('data-duration');
                console.log(`Popup ad ${index + 1}: ID=${adId}, Duration=${duration}s`);
                
                // Show popup after a delay (staggered)
                setTimeout(() => {
                    console.log(`Showing popup ad ${adId}`);
                    popup.classList.add('show');
                    popup.style.display = 'flex';
                    
                    // Track impression
                    trackAdImpression(adId);
                    
                    // Auto-close if duration is set
                    if (duration && duration > 0) {
                        console.log(`Auto-closing popup ad ${adId} in ${duration} seconds`);
                        setTimeout(() => {
                            closePopupAd(adId);
                        }, duration * 1000);
                    }
                }, (index + 1) * 2000); // 2 second delay between popups
            });
        }

        // Intersection Observer for impression tracking
        function setupAdImpressionTracking() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const adId = entry.target.getAttribute('data-ad-id');
                        if (adId && !entry.target.hasAttribute('data-impression-tracked')) {
                            trackAdImpression(adId);
                            entry.target.setAttribute('data-impression-tracked', 'true');
                        }
                    }
                });
            }, {
                threshold: 0.5 // Trigger when 50% of the ad is visible
            });

            // Observe all ad containers
            document.querySelectorAll('.ad-container').forEach(ad => {
                observer.observe(ad);
            });
        }

        // Initialize ad functionality when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing ad functionality...');
            
            // Setup impression tracking
            setupAdImpressionTracking();
            
            // Initialize banner slider
            initializeBannerSlider();
            
            // Show popup ads after page load
            setTimeout(() => {
                console.log('Starting popup ads display...');
                showPopupAds();
            }, 3000); // 3 second delay after page load
        });

        // Banner Slider Functionality
        function initializeBannerSlider() {
            const slider = document.getElementById('bannerSlider');
            const prevBtn = document.getElementById('bannerSliderPrev');
            const nextBtn = document.getElementById('bannerSliderNext');
            const indicators = document.querySelectorAll('.banner-slider-indicator');
            
            if (!slider) return;

            const slides = slider.querySelectorAll('.banner-slide');
            const totalSlides = slides.length;
            const originalSlides = Math.floor(totalSlides / 2); // Get count of original (non-duplicated) slides
            const slidesPerView = window.innerWidth <= 768 ? 2 : 3;
            const totalPages = totalSlides;
            
            let currentIndex = 0;
            let autoSlideInterval;
            let isTransitioning = false;

            function updateSlider(instant = false) {
                if (instant) {
                    slider.style.transition = 'none';
                } else {
                    slider.style.transition = 'transform 0.5s ease-in-out';
                }
                
                const translateX = -currentIndex * (100 / slidesPerView);
                slider.style.transform = `translateX(${translateX}%)`;
                
                // Update indicators based on actual position (using original slides count)
                const indicatorIndex = currentIndex % Math.ceil(originalSlides / slidesPerView);
                indicators.forEach((indicator, index) => {
                    indicator.classList.toggle('active', index === indicatorIndex);
                });
            }

            function nextSlide() {
                if (isTransitioning) return;
                
                isTransitioning = true;
                
                // Calculate the maximum index we can reach before looping
                const maxIndex = originalSlides;
                
                // If we've reached the end of the original slides, jump back to the start instantly
                if (currentIndex >= maxIndex - slidesPerView) {
                    currentIndex = 0;
                    updateSlider(true);
                } else {
                    currentIndex++;
                    updateSlider();
                }
                
                setTimeout(() => {
                    isTransitioning = false;
                }, 500);
            }

            function prevSlide() {
                if (isTransitioning) return;
                
                isTransitioning = true;
                
                // Calculate the maximum index we can reach
                const maxIndex = originalSlides;
                
                // If we're at the beginning, jump to the end of the original slides
                if (currentIndex <= 0) {
                    currentIndex = maxIndex - slidesPerView;
                    updateSlider(true);
                } else {
                    currentIndex--;
                    updateSlider();
                }
                
                setTimeout(() => {
                    isTransitioning = false;
                }, 500);
            }

            function startAutoSlide() {
                if (totalPages > 1 && !autoSlideInterval) {
                    autoSlideInterval = setInterval(() => {
                        // Reset to beginning when reaching the end of original slides (infinite loop)
                        if (currentIndex >= originalSlides - slidesPerView) {
                            currentIndex = 0;
                            updateSlider(true);
                            return;
                        }
                        nextSlide();
                    }, 5000); // Auto-slide every 5 seconds
                }
            }

            function stopAutoSlide() {
                if (autoSlideInterval) {
                    clearInterval(autoSlideInterval);
                    autoSlideInterval = null;
                }
            }

            // Event listeners
            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    nextSlide();
                    stopAutoSlide();
                    startAutoSlide();
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    prevSlide();
                    stopAutoSlide();
                    startAutoSlide();
                });
            }

            // Indicator click events
            indicators.forEach((indicator, index) => {
                indicator.addEventListener('click', () => {
                    stopAutoSlide();
                    isTransitioning = true;
                    currentIndex = index * slidesPerView;
                    updateSlider();
                    setTimeout(() => {
                        isTransitioning = false;
                        startAutoSlide();
                    }, 500);
                });
            });

            // Pause auto-slide on hover
            const sliderContainer = document.querySelector('.banner-slider-container');
            if (sliderContainer) {
                sliderContainer.addEventListener('mouseenter', stopAutoSlide);
                sliderContainer.addEventListener('mouseleave', startAutoSlide);
            }

            // Handle window resize
            window.addEventListener('resize', () => {
                const newSlidesPerView = window.innerWidth <= 768 ? 2 : 3;
                if (newSlidesPerView !== slidesPerView) {
                    stopAutoSlide();
                    currentIndex = 0;
                    isTransitioning = false;
                    updateSlider(true);
                    startAutoSlide();
                }
            });

            // Start auto-slide
            startAutoSlide();
        }

        // Close popup on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.popup-ad-modal.show').forEach(popup => {
                    const adId = popup.getAttribute('data-ad-id');
                    closePopupAd(adId);
                });
            }
        });

        // Close popup when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('popup-ad-modal')) {
                const adId = e.target.getAttribute('data-ad-id');
                closePopupAd(adId);
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        /* Split Hero Layout (left slideshow + right image) */
        .hero-split-wrapper {
            position: relative;
            height: 500px;
            /* Wrapper should not clip; cards will have their own radius */
            border-radius: 0;
            overflow: visible;
            box-shadow: none;
            margin: 10px 0 0 0;
            display: flex;
            gap: 12px;
            align-items: stretch;
        }

        .hero-split-left {
            flex: 1 1 auto;
            height: 100%;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.18);
        }

        .hero-split-right {
            position: relative;
            flex: 0 0 32%;
            height: 100%;
            background: #111;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.18);
        }

        .hero-split-right-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .hero-slideshow {
            position: relative;
            height: 100%;
            overflow: hidden;
            border-radius: 0;
            box-shadow: none;
            margin: 0;
            width: 100%;
        }

        @media (max-width: 768px) {
            .hero-split-wrapper {
                flex-direction: column;
                height: auto;
                gap: 12px;
            }

            .hero-split-left {
                height: 200px;
            }

            .hero-split-right {
                flex: 0 0 auto;
                height: 180px;
            }
        }
        
        .hero-slides-container {
            position: relative;
            width: 100%;
            height: 100%;
        }
        
        .hero-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #000;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0;
            transition: opacity 1.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            transform: scale(1.1);
        }

        .hero-video-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }
        
        .hero-slide.active {
            opacity: 1;
            transform: scale(1);
        }
        
        .hero-slide-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: -1px;
            line-height: 1.1;
            text-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .hero-slide-subtitle {
            font-size: 1.25rem;
            font-weight: 400;
            color: rgba(255,255,255,0.9);
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            letter-spacing: 0.5px;
        }

        .hero-slide-btn {
            padding: 12px 35px;
            border-radius: 50px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            color: white !important;
            border: none;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .hero-slide-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            filter: brightness(1.1);
        }

        /* Integrated Search in Slideshow */
        .hero-slideshow-search-overlay {
            position: absolute;
            bottom: 40px;
            left: 0;
            right: 0;
            z-index: 20;
        }

        .hero-slideshow-search-overlay .search-input {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        @media (max-width: 768px) {
            .hero-slideshow-search-overlay {
                bottom: 20px;
            }
            .hero-slideshow-search-overlay .container {
                padding: 0 20px;
            }
        }
        
        .hero-slide-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /* Keep overlay element for layout stacking,
               but make it visually neutral so images/videos look clean. */
            background: transparent;
            opacity: 0;
            z-index: 1;
        }
        
        .hero-slide-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }
        
        .hero-slide-content {
            position: relative;
            z-index: 2;
            color: white;
            width: 100%;
            height: 100%;
            text-align: center;
        }
        
        .hero-slide-title {
            font-size: clamp(32px, 6vw, 56px);
            font-weight: 900;
            color: #fff;
            line-height: 1.2;
            letter-spacing: -1px;
            text-shadow: 0 0 30px rgba(255,0,0,0.3), 0 0 60px rgba(255,0,0,0.2);
            margin-bottom: 1rem;
        }
        
        .hero-slide-subtitle {
            font-size: clamp(16px, 2.5vw, 20px);
            color: rgba(255, 255, 255, 0.85);
            line-height: 1.6;
            text-shadow: 0 2px 10px rgba(0,0,0,0.5);
            margin-bottom: 2rem;
        }
        
        .hero-slide-btn {
            background: #ff0000;
            border: none;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255,0,0,0.3);
        }
        
        .hero-slide-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255,0,0,0.4);
            color: white;
        }
        
        .hero-slideshow-controls {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 3;
        }
        
        .hero-slideshow-prev,
        .hero-slideshow-next {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .hero-slideshow-prev:hover,
        .hero-slideshow-next:hover {
            background: rgba(255, 0, 0, 0.8);
            transform: scale(1.1);
        }
        
        .hero-slideshow-indicators {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 3;
        }
        
        .hero-slideshow-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.5);
            background: transparent;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .hero-slideshow-indicator.active {
            background: #ff0000;
            border-color: #ff0000;
        }
        
        @media (max-width: 768px) {
            .hero-slideshow { height: 300px; }
        }

        /* Variant Section Styles */
        .variant-section {
            margin-bottom: 1rem;
        }

        .variant-label {
            display: block;
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }

        .variant-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(70px, 1fr));
            gap: 0.4rem;
        }

        .variant-option-btn {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            padding: 0.4rem 0.6rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.2rem;
            min-height: 50px;
            justify-content: center;
        }

        .variant-option-btn:hover {
            background: #e9ecef;
            border-color: #dc3545;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .variant-option-btn.active {
            background: #dc3545;
        border-color: #dc3545;
        color: white;
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }
    
        .variant-name {
            font-weight: 500;
            font-size: 0.75rem;
            line-height: 1.2;
        }

        .variant-price {
            font-size: 0.65rem;
            opacity: 0.8;
            font-weight: 500;
        }

        /* Mobile Responsive Variants */
        @media (max-width: 768px) {
            .variant-options {
                grid-template-columns: repeat(4, 1fr);
                gap: 0.3rem;
            }

            .variant-option-btn {
                padding: 0.3rem 0.4rem;
                min-height: 40px;
            }

            .variant-name {
                font-size: 0.7rem;
            }

            .variant-price {
                font-size: 0.6rem;
            }

            .variant-label {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 576px) {
            .variant-options {
                grid-template-columns: repeat(4, 1fr);
                gap: 0.25rem;
            }

            .variant-option-btn {
                padding: 0.25rem 0.3rem;
                min-height: 35px;
            }

            .variant-name {
                font-size: 0.65rem;
            }

            .variant-price {
                font-size: 0.55rem;
            }

            .variant-label {
                font-size: 0.75rem;
            }

            /* Mobile Modal Adjustments */
            .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100% - 1rem);
            }

            .modal-content {
                border-radius: 12px;
            }

            .modal-header {
                padding: 0.75rem 1rem;
            }

            .modal-header .modal-title {
                font-size: 0.9rem;
            }

            .modal-body {
                padding: 0.75rem;
            }

            .modal-body .row.g-3 {
                --bs-gutter-x: 0.5rem;
                --bs-gutter-y: 0.5rem;
            }

            .modal-body .col-md-4,
            .modal-body .col-md-8 {
                margin-bottom: 0.75rem;
            }

            .modal-body .col-md-4 img {
                height: 150px !important;
            }

            .modal-body .alert {
                padding: 0.5rem;
                margin-bottom: 0.75rem;
            }

            .modal-body .alert h4 {
                font-size: 0.9rem;
                margin-bottom: 0;
            }

            .modal-body .alert small {
                font-size: 0.7rem;
            }

            /* Mobile Quantity and Pricing Layout */
            .modal-body .row.g-2 {
                --bs-gutter-x: 0.5rem;
                --bs-gutter-y: 0.5rem;
            }

            .modal-body .input-group-sm .btn {
                font-size: 0.6rem;
                padding: 0.25rem 0.4rem;
            }

            .modal-body .input-group-sm .form-control {
                font-size: 0.7rem;
                padding: 0.25rem 0.1rem;
            }

            /* Make quantity input larger and more visible */
            .modal-body .qty-input {
                font-size: 1.1rem !important;
                padding: 0.45rem 0.3rem !important;
                font-weight: 700 !important;
            }

            /* Style buttons next to quantity input - only for quantity input groups */
            .modal-body .row.g-2 .input-group .btn {
                font-size: 0.95rem !important;
                padding: 0.45rem 0.7rem !important;
                font-weight: 600 !important;
            }

            .modal-body .form-label {
                font-size: 0.7rem;
                margin-bottom: 0.25rem;
            }

            .modal-body .btn {
                font-size: 0.75rem;
                padding: 0.4rem 0.6rem;
            }

            .modal-body .small {
                font-size: 0.65rem;
            }

            .modal-body .fw-bold {
                font-size: 0.8rem;
            }

            .modal-body .text-primary {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .variant-options {
                grid-template-columns: repeat(4, 1fr);
                gap: 0.2rem;
            }

            .variant-option-btn {
                padding: 0.2rem 0.25rem;
                min-height: 32px;
            }

            .variant-name {
                font-size: 0.6rem;
            }

            .variant-price {
                font-size: 0.5rem;
            }

            .variant-label {
                font-size: 0.7rem;
            }

            /* Extra Small Mobile Modal Adjustments */
            .modal-dialog {
                margin: 0.25rem;
                max-width: calc(100% - 0.5rem);
            }

            .modal-header {
                padding: 0.5rem 0.75rem;
            }

            .modal-header .modal-title {
                font-size: 0.8rem;
            }

            .modal-body {
                padding: 0.5rem;
            }

            .modal-body .col-md-4 img {
                height: 120px !important;
            }

            .modal-body .alert {
                padding: 0.4rem;
                margin-bottom: 0.5rem;
            }

            .modal-body .alert h4 {
                font-size: 0.8rem;
            }

            .modal-body .alert small {
                font-size: 0.65rem;
            }

            .modal-body .input-group-sm .btn {
                font-size: 0.55rem;
                padding: 0.2rem 0.3rem;
            }

            .modal-body .input-group-sm .form-control {
                font-size: 0.65rem;
                padding: 0.2rem 0.05rem;
            }

            /* Make quantity input larger and more visible on small screens */
            .modal-body .qty-input {
                font-size: 1rem !important;
                padding: 0.4rem 0.25rem !important;
                font-weight: 700 !important;
            }

            /* Style buttons next to quantity input on small screens */
            .modal-body .row.g-2 .input-group .btn {
                font-size: 0.9rem !important;
                padding: 0.4rem 0.6rem !important;
                font-weight: 600 !important;
            }

            .modal-body .form-label {
                font-size: 0.65rem;
            }

            .modal-body .btn {
                font-size: 0.7rem;
                padding: 0.35rem 0.5rem;
            }

            .modal-body .small {
                font-size: 0.6rem;
            }

            .modal-body .fw-bold {
                font-size: 0.75rem;
            }

            .modal-body .text-primary {
                font-size: 0.85rem;
            }
    }
    
    .color-preview {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 1px #ddd;
        margin: 0 auto 0.25rem;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>

// Live autocomplete for retail search (UI only; endpoint to be wired later)
        document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('retail-search');
    const box = document.getElementById('retail-search-suggestions');
    if (!input || !box) return;
    let controller = null;

            function render(items) {
                if (!items || !items.length) {
                    box.style.display = 'none';
                    box.innerHTML = '';
                    return;
                }
        box.innerHTML = items.map(i => `
            <a href="#" class="list-group-item list-group-item-action d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="fw-semibold">${i.name}</div>
                    <small class="text-muted">${i.barcode || ''}</small>
                </div>
            </a>`).join('');
                box.style.display = 'block';
    }
            async function searchRetail(q) {
        if (controller) controller.abort();
        controller = new AbortController();
                try {
                    const res = await fetch(`/products/autocomplete?q=${encodeURIComponent(q)}`, {
                        signal: controller.signal
                    });
            const data = await res.json();
            render(data.items || []);
                } catch (e) {
                    /* aborted or error */ }
    }
            input.addEventListener('input', function() {
        const q = this.value.trim();
                if (q.length === 0) {
                    render([]);
                    return;
                }
        searchRetail(q);
    });
            document.addEventListener('click', (e) => {
                if (!box.contains(e.target) && e.target !== input) {
                    box.style.display = 'none';
                }
    });
});
// State: selected variants per product
const selectedVariantsByProduct = {};
// Cache last calculated unit price per product (so cart uses same value as UI)
const lastUnitPriceByProduct = {};

// Template URL for volume-discount API (barcode placeholder will be replaced in JS)
const productPriceUrlTemplate = '<?php echo e(route('product.get-price', ['barcode' => '__BARCODE__'])); ?>';

// Helper: fetch unit price from backend using same volume-discount logic as product details page
async function fetchUnitPriceForQuantity(barcode, customerType, quantity, fallbackBase) {
    const safeQty = quantity && quantity > 0 ? quantity : 1;
    const customer = customerType || '<?php echo e($channel); ?>';
    
    try {
        const url = productPriceUrlTemplate.replace('__BARCODE__', encodeURIComponent(barcode)) +
            `?quantity=${encodeURIComponent(safeQty)}&customer_type=${encodeURIComponent(customer)}`;
        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (response.ok) {
            const data = await response.json();
            if (data && typeof data.unit_price !== 'undefined') {
                return parseFloat(data.unit_price);
            }
        }
    } catch (e) {
        // Silent fallback to base price if API fails
        console.error('Failed to fetch tiered price', e);
    }
    
    // Fallback: use base price when no tier/endpoint available
    return parseFloat(fallbackBase || 0);
}

// Handle variant selection
document.querySelectorAll('.variant-option').forEach(btn => {
    btn.addEventListener('click', function() {
        const pid = this.dataset.productId;
        const cat = this.dataset.category;
        const name = this.dataset.name;
        const price = parseFloat(this.dataset.price || '0');
        if (!selectedVariantsByProduct[pid]) selectedVariantsByProduct[pid] = {};

        // Check if this button is already active (for unselection)
        if (this.classList.contains('active')) {
            // Unselect: remove active class and clear variant
            this.classList.remove('active');
            delete selectedVariantsByProduct[pid][cat];
        } else {
            // Select: remove active class from other buttons in the same category
            this.parentElement.querySelectorAll('.variant-option').forEach(sib => {
                if (sib.dataset.category === cat) sib.classList.remove('active');
            });
            this.classList.add('active');

                    selectedVariantsByProduct[pid][cat] = {
                        name,
                        price
                    };
        }
        
        updatePriceDisplay(pid);
    });
});

// Handle qty changes
document.querySelectorAll('.qty-input').forEach(inp => {
            inp.addEventListener('input', function() {
        const min = parseInt(this.getAttribute('min') || '1');
        let val = parseInt(this.value || '1');
        if (isNaN(val) || val < min) {
            val = min;
            this.value = min;
        }
                updatePriceDisplay(this.dataset.productId);
            });
        });

        // Quantity increase/decrease functions
        function increaseQty(productId) {
            const qtyInput = document.getElementById('qty-' + productId);
            if (qtyInput) {
        const min = parseInt(qtyInput.getAttribute('min') || '1');
        let current = parseInt(qtyInput.value || String(min));
        if (isNaN(current) || current < min) current = min;
        qtyInput.value = current + 1;
                updatePriceDisplay(productId);
            }
        }

        function decreaseQty(productId) {
            const qtyInput = document.getElementById('qty-' + productId);
            if (qtyInput) {
        const min = parseInt(qtyInput.getAttribute('min') || '1');
        let currentValue = parseInt(qtyInput.value || String(min));
        if (isNaN(currentValue) || currentValue <= min) {
            currentValue = min;
        } else {
            currentValue = currentValue - 1;
        }
        qtyInput.value = currentValue;
                    updatePriceDisplay(productId);
            }
        }

async function updatePriceDisplay(pid) {
    const qtyEl = document.getElementById('qty-' + pid);
    if (!qtyEl) return;
    let qty = parseInt(qtyEl.value || '1');
    const minQty = parseInt(qtyEl.getAttribute('min') || '1');
    if (isNaN(qty) || qty < minQty) {
        qty = minQty;
        qtyEl.value = minQty;
    }

    // Find modal confirm button for base price
    const confirmBtn = document.querySelector('.add-to-cart-confirm[data-product-id="' + pid + '"]');
    if (!confirmBtn) return;
    const base = parseFloat(confirmBtn.dataset.base || '0');
    const barcode = confirmBtn.dataset.barcode || '';
    const channel = confirmBtn.dataset.channel || '<?php echo e($channel); ?>';

    // Get unit price from backend using same volume-discount logic as product details
    let unit = await fetchUnitPriceForQuantity(barcode, channel, qty, base);

    // Add variant price adjustments
    const selected = selectedVariantsByProduct[pid] || {};
            Object.values(selected).forEach(v => {
                unit += parseFloat(v.price || 0);
            });

    const unitEl = document.getElementById('unit-' + pid);
    const totalEl = document.getElementById('total-' + pid);
    if (unitEl) unitEl.textContent = unit.toLocaleString() + ' TZS';
    if (totalEl) totalEl.textContent = (unit * qty).toLocaleString() + ' TZS';

    // Cache last unit price so cart item matches what user sees
    lastUnitPriceByProduct[pid] = unit;
}

// Add to cart localStorage
document.querySelectorAll('.add-to-cart-confirm').forEach(btn => {
    btn.addEventListener('click', async function() {
        const pid = this.dataset.productId;
        const barcode = this.dataset.barcode;
        const name = this.dataset.name;
        const channel = this.dataset.channel;
        const base = parseFloat(this.dataset.base || '0');

        const qtyEl = document.getElementById('qty-' + pid);
        let qty = parseInt(qtyEl.value || '1');
        const minQty = parseInt(this.dataset.minqty || qtyEl.getAttribute('min') || '1');
        if (isNaN(qty) || qty < minQty) {
            qty = minQty;
            qtyEl.value = minQty;
        }

        // Use the same unit price as UI; if not cached, fetch from backend
        let unit = typeof lastUnitPriceByProduct[pid] !== 'undefined'
            ? lastUnitPriceByProduct[pid]
            : await fetchUnitPriceForQuantity(barcode, channel, qty, base);

        const selected = selectedVariantsByProduct[pid] || {};
                Object.values(selected).forEach(v => {
                    unit += parseFloat(v.price || 0);
                });

        // Get the product name from the button data
        const productName = btn.dataset.name;
        
        const item = {
            barcode, 
            name: productName,
            channel,
                    qty,
                    unitPrice: unit,
                    total: unit * qty,
            variants: selected,
            // Persist minimum order quantity so cart modal respects it
            minQuantity: minQty
        };

                const cartKey = window.location.hostname.includes('b2b') || window.location.pathname
                    .includes('/b2b') ? 'chibo_wholesale_cart' : 'chibo_retail_cart';
        let cart = [];
                try {
                    cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
                } catch (e) {
                    cart = [];
                }

        cart.push(item);
        localStorage.setItem(cartKey, JSON.stringify(cart));

        // Update all cart badges instantly
        const badges = document.querySelectorAll('#cart-count, .cart-count, [id*="cart-count"]');
        badges.forEach(badge => {
            badge.textContent = cart.length;
        });
        
        // Dispatch cart update event
        document.dispatchEvent(new Event('chibo_cart_updated'));
        
        // Show success notification
        showSuccessToast('Product added to cart successfully!');
        
        // Visual feedback on button
        const originalHtml = this.innerHTML;
        this.innerHTML = '<i class="fas fa-check me-2"></i>Added!';
        this.classList.add('btn-success');
        this.classList.remove('btn-primary');
        
        setTimeout(() => {
            this.innerHTML = originalHtml;
            this.classList.remove('btn-success');
            this.classList.add('btn-primary');
        }, 2000);

                // Open cart modal after adding item
                setTimeout(() => {
                    const cartModal = document.getElementById('cartModal');
                    if (cartModal) {
                        const modal = new bootstrap.Modal(cartModal);
                        modal.show();
                        // Update cart modal content
                        if (typeof updateCartModal === 'function') {
                            updateCartModal();
                        }
                    }
                }, 1000); // Wait 1 second to show the success feedback first
    });
});

// Handle modal variant selection
document.addEventListener('click', function(e) {
            if (e.target.classList.contains('variant-option-btn')) {
        const btn = e.target;
        const pid = btn.dataset.productId;
        const cat = btn.dataset.category;
                const name = btn.dataset.name;
        const price = parseFloat(btn.dataset.price || '0');
        
        if (!selectedVariantsByProduct[pid]) selectedVariantsByProduct[pid] = {};

        // Check if this button is already active (for unselection)
        if (btn.classList.contains('active')) {
            // Unselect: remove active class and clear variant
            btn.classList.remove('active');
            delete selectedVariantsByProduct[pid][cat];
        } else {
            // Select: remove active class from other buttons in the same category
            const modal = btn.closest('.modal');
            modal.querySelectorAll(`[data-category="${cat}"]`).forEach(sib => {
                sib.classList.remove('active');
            });
            btn.classList.add('active');

                    selectedVariantsByProduct[pid][cat] = {
                        name,
                        price
                    };
        }
        
        updatePriceDisplay(pid);
    }
});
</script>

<script>
// Hero Slideshow Functionality
document.addEventListener('DOMContentLoaded', function() {
    const slideshow = document.getElementById('hero-slideshow');
    if (!slideshow) return;
    
    const slides = slideshow.querySelectorAll('.hero-slide');
    const indicators = slideshow.querySelectorAll('.hero-slideshow-indicator');
    const prevBtn = document.getElementById('hero-slideshow-prev');
    const nextBtn = document.getElementById('hero-slideshow-next');
    
    let currentSlide = 0;
    let slideInterval;
    
    function showSlide(index) {
        if (!slides[index]) return;
        slides.forEach(slide => slide.classList.remove('active'));
        indicators.forEach(indicator => indicator.classList.remove('active'));
        slides[index].classList.add('active');
        if (indicators[index]) indicators[index].classList.add('active');
        currentSlide = index;
    }
    
    function nextSlide() {
        showSlide((currentSlide + 1) % slides.length);
    }
    
    function prevSlide() {
        showSlide((currentSlide - 1 + slides.length) % slides.length);
    }
    
    function startSlideshow() {
        if (slides.length > 1) {
            slideInterval = setInterval(nextSlide, 5000);
        }
    }
    
    function stopSlideshow() {
        clearInterval(slideInterval);
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', () => { nextSlide(); stopSlideshow(); startSlideshow(); });
    }
    if (prevBtn) {
        prevBtn.addEventListener('click', () => { prevSlide(); stopSlideshow(); startSlideshow(); });
    }
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => { showSlide(index); stopSlideshow(); startSlideshow(); });
    });
    
    slideshow.addEventListener('mouseenter', stopSlideshow);
    slideshow.addEventListener('mouseleave', startSlideshow);
    startSlideshow();
});
</script>

<script>

// Navigation function for product cards
function navigateToProduct(url) {
    if (url) {
        window.location.href = url;
    }
}

// Toast notification function
function showSuccessToast(message) {
    // Check if toast container exists, if not create it
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-check-circle me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Initialize and show toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 3000
    });
    
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('public.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/gotlaptopparts.com/Desktop/LaravelProject/chibo_sales/resources/views/public/pages/home.blade.php ENDPATH**/ ?>