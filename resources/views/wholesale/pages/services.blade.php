@extends('public.layouts.app')

@section('title', 'Wholesale Services - CHIBO BRAND B2B')
@section('description', 'Wholesale solutions in Designing, Printing, Branding, Promotion, and Marketing.')

@section('content')
@push('styles')
<style>
    :root{
        --brand:#dc3545;          /* CHIBO red */
        --brand-dark:#b52a37;
        --ink:#0f172a;
        --muted:#6b7280;
        --soft:#f7f7fb;
    }

    /* Hero Slideshow Styles */
    .hero-slideshow {
        position: relative;
        height: 500px;
        overflow: hidden;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        margin: 10px 10px 0 10px;
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
        background: linear-gradient(135deg, #0a0a0a 0%, #1a0000 50%, #000000 100%);
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        opacity: 0;
        transition: opacity 1s ease-in-out;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .hero-slide-video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        z-index: 0;
    }
    
    .hero-slide.active {
        opacity: 1;
    }
    
    .hero-slide-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: transparent;
        z-index: 1;
    }
    
    .hero-slide-content {
        position: relative;
        z-index: 2;
        color: white;
        text-align: center;
        width: 100%;
    }
    
    .hero-slide-title {
        font-size: clamp(32px, 6vw, 56px);
        font-weight: 900;
        color: #fff;
        line-height: 1.2;
        letter-spacing: -1px;
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.9), -1px -1px 4px rgba(0, 0, 0, 0.8);
        margin-bottom: 1rem;
    }
    
    .hero-slide-subtitle {
        font-size: clamp(16px, 2.5vw, 20px);
        color: #fff;
        line-height: 1.6;
        text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.9), -1px -1px 3px rgba(0, 0, 0, 0.8);
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
    
    .hero-slideshow-indicator:hover {
        background: rgba(255, 0, 0, 0.5);
        border-color: rgba(255, 0, 0, 0.5);
    }

    /* Hero Banner - Dark + Red 3D Theme (Fallback) */
    .hero-banner{background:linear-gradient(135deg,#0a0a0a 0%,#1a0000 50%,#000000 100%);padding:80px 0;position:relative;overflow:hidden;min-height:500px;margin:10px 10px 0 10px;border-radius:16px}
    .hero-content{position:relative;z-index:10}
    
    /* 3D Animated Bubbles */
    .bubbles-container{position:absolute;width:100%;height:100%;top:0;left:0;overflow:hidden;z-index:1}
    .bubble{position:absolute;border-radius:50%;background:radial-gradient(circle at 30% 30%,rgba(255,0,0,0.3),rgba(255,0,0,0.05));box-shadow:inset 0 0 30px rgba(255,0,0,0.2),0 0 50px rgba(255,0,0,0.1);animation:float 20s infinite ease-in-out;backdrop-filter:blur(2px)}
    .bubble::before{content:'';position:absolute;top:10%;left:10%;width:40%;height:40%;border-radius:50%;background:radial-gradient(circle at 50% 50%,rgba(255,255,255,0.3),transparent)}
    .bubble-1{width:200px;height:200px;left:10%;top:20%;animation-delay:0s;animation-duration:25s}
    .bubble-2{width:150px;height:150px;right:15%;top:40%;animation-delay:3s;animation-duration:20s}
    .bubble-3{width:250px;height:250px;left:50%;top:60%;animation-delay:6s;animation-duration:30s}
    .bubble-4{width:180px;height:180px;right:30%;top:10%;animation-delay:2s;animation-duration:22s}
    .bubble-5{width:120px;height:120px;left:30%;bottom:10%;animation-delay:4s;animation-duration:18s}
    .bubble-6{width:220px;height:220px;right:10%;bottom:20%;animation-delay:5s;animation-duration:28s}
    
    @keyframes float{
        0%,100%{transform:translate(0,0) scale(1) rotate(0deg)}
        25%{transform:translate(30px,-30px) scale(1.1) rotate(90deg)}
        50%{transform:translate(-20px,20px) scale(0.9) rotate(180deg)}
        75%{transform:translate(40px,10px) scale(1.05) rotate(270deg)}
    }
    
    .hero-badge{display:inline-flex;align-items:center;background:linear-gradient(135deg,rgba(255,0,0,0.2),rgba(255,0,0,0.1));color:#ff0000;padding:10px 24px;border-radius:50px;font-size:14px;font-weight:700;border:2px solid rgba(255,0,0,0.3);box-shadow:0 0 20px rgba(255,0,0,0.2);text-transform:uppercase;letter-spacing:1px}
    .hero-title{font-size:clamp(32px,6vw,56px);font-weight:900;color:#fff;line-height:1.2;letter-spacing:-1px;text-shadow:0 0 30px rgba(255,0,0,0.3),0 0 60px rgba(255,0,0,0.2)}
    .hero-subtitle{font-size:clamp(16px,2.5vw,20px);color:rgba(255,255,255,0.85);line-height:1.6;text-shadow:0 2px 10px rgba(0,0,0,0.5)}
    .hero-cta-btn{padding:16px 32px;border-radius:50px;font-weight:600;font-size:16px;transition:all 0.3s;border:2px solid;text-transform:uppercase;letter-spacing:0.5px}
    .hero-cta-btn.btn-danger{background:#ff0000;border-color:#ff0000;box-shadow:0 10px 30px rgba(255,0,0,0.4)}
    .hero-cta-btn.btn-danger:hover{background:#cc0000;border-color:#cc0000;transform:translateY(-2px);box-shadow:0 15px 40px rgba(255,0,0,0.5)}
    .hero-cta-btn.btn-outline-light{background:transparent;border-color:rgba(255,255,255,0.5);color:#fff}
    .hero-cta-btn.btn-outline-light:hover{background:rgba(255,255,255,0.1);border-color:#fff;transform:translateY(-2px)}

    .service-card{
        background:#fff;border:none;border-radius:16px;
        box-shadow:0 10px 30px rgba(0,0,0,.06);
        transition: transform .3s cubic-bezier(.2,.8,.2,1), box-shadow .3s;
        transform-style:preserve-3d;
    }
    .service-card:hover{ transform: translateY(-6px) rotateX(3deg) rotateY(-3deg);
        box-shadow:0 20px 45px rgba(0,0,0,.10); }
    .service-icon{
        width:54px;height:54px;border-radius:12px; display:flex;align-items:center;justify-content:center;
        background: linear-gradient(135deg, var(--brand), var(--brand-dark));
        color:#fff; font-size:1.25rem; box-shadow:0 10px 20px rgba(220,53,69,.25); transform: translateZ(40px);
    }
    .service-title{ font-weight:700;margin-bottom:.25rem; }
    .service-text{ color:var(--muted); }

    .why-chip{ border-radius:14px;background:#fff;border:1px solid #eef2f7;
        padding:1rem; transition:.2s; height:100%; }
    .why-chip:hover{ box-shadow:0 10px 20px rgba(0,0,0,.07); transform:translateY(-3px); }
    .why-icon{ color:var(--brand); }

    .mini-card{ border:none;border-radius:14px; overflow:hidden;
        box-shadow:0 10px 25px rgba(0,0,0,.06); transition:.25s; }
    .mini-card img{ width:100%; height:160px; object-fit:cover; }
    .mini-card:hover{ transform: translateY(-4px); }

    .cta-band{
        background: linear-gradient(135deg, var(--brand), var(--brand-dark));
        border-radius:18px; color:#fff;
        box-shadow:0 24px 48px rgba(220,53,69,.35);
    }
    .cta-band .btn{ border-radius:999px; padding:.75rem 1.15rem; }

    .reveal{ opacity:0; transform: translateY(16px); transition:.6s ease; }
    .reveal.show{ opacity:1; transform:none; }

    /* Responsive */
    @media (max-width: 768px) {
        .hero-slideshow {
            height: 300px;
            margin: 8px 8px 0 8px;
        }
        
        .hero-banner {
            margin: 8px 8px 0 8px;
        }
        
        .hero-slide-title {
            font-size: clamp(24px, 8vw, 32px);
        }
        
        .hero-slide-subtitle {
            font-size: clamp(14px, 4vw, 16px);
        }
        
        .hero-slide-btn {
            padding: 12px 24px;
            font-size: 14px;
        }
    }
    
    @media (max-width: 480px) {
        .hero-slideshow {
            height: 250px;
            margin: 5px 5px 0 5px;
        }
        
        .hero-banner {
            margin: 5px 5px 0 5px;
        }
        
        .hero-slide-title {
            font-size: clamp(18px, 5vw, 24px);
        }
        
        .hero-slide-subtitle {
            font-size: clamp(10px, 3vw, 12px);
        }
        
        .hero-slide-btn {
            padding: 8px 16px;
            font-size: 10px;
        }
    }
</style>
@endpush

<!-- Hero Slideshow -->
@if($heroSlides && $heroSlides->count() > 0)
<div id="hero-slideshow" class="hero-slideshow mb-4">
    <div class="hero-slides-container">
        @foreach($heroSlides as $index => $slide)
            @php
                // Determine if the file is a video
                $extension = $slide->image_path ? strtolower(pathinfo($slide->image_path, PATHINFO_EXTENSION)) : '';
                $isVideo = in_array($extension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv', '3gp', 'mpg', 'mpeg']);
            @endphp
            <div class="hero-slide {{ $index === 0 ? 'active' : '' }}" 
                 @if($slide->image_path && !$isVideo)
                     style="background-image: url('{{ asset('storage/' . $slide->image_path) }}');"
                 @endif>
                
                @if($isVideo && $slide->image_path)
                    <video class="hero-slide-video" autoplay muted loop playsinline>
                        <source src="{{ asset('storage/' . $slide->image_path) }}" type="video/{{ $extension }}">
                    </video>
                @endif
                
                <div class="hero-slide-overlay"></div>
                <div class="hero-slide-content">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-10 text-center">
                                <h1 class="hero-slide-title mb-4">{{ $slide->title }}</h1>
                                @if($slide->subtitle)
                                    <p class="hero-slide-subtitle mb-5">{{ $slide->subtitle }}</p>
                                @endif
                                @if($slide->button_text && $slide->button_url)
                                    <a href="{{ $slide->button_url }}" 
                                       class="btn hero-slide-btn" 
                                       style="background-color: {{ $slide->button_color }};">
                                        {{ $slide->button_text }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <!-- Slideshow Controls -->
    @if($heroSlides->count() > 1)
        <div class="hero-slideshow-controls">
            <button class="hero-slideshow-prev" id="hero-slideshow-prev">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="hero-slideshow-next" id="hero-slideshow-next">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        
        <!-- Slideshow Indicators -->
        <div class="hero-slideshow-indicators">
            @foreach($heroSlides as $index => $slide)
                <button class="hero-slideshow-indicator {{ $index === 0 ? 'active' : '' }}" 
                        data-slide="{{ $index }}"></button>
            @endforeach
        </div>
    @endif
</div>
@else
<!-- Fallback Hero Banner with 3D Bubbles -->
<div class="hero-banner mb-4">
    <div class="bubbles-container">
        <div class="bubble bubble-1"></div>
        <div class="bubble bubble-2"></div>
        <div class="bubble bubble-3"></div>
        <div class="bubble bubble-4"></div>
        <div class="bubble bubble-5"></div>
        <div class="bubble bubble-6"></div>
    </div>
    
    <div class="hero-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 text-center">
                    <div class="hero-badge mb-4">
                        <i class="fas fa-layer-group me-2"></i>B2B Services
                    </div>
                    <h1 class="hero-title mb-4">
                        We Design. We Print. We Build Brands That Stand Out.
                    </h1>
                    <p class="hero-subtitle mb-5">
                        At CHIBO BRAND, we empower businesses with scalable design, high-volume printing, and impactful branding.
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="{{ url('/b2b/contact') }}" class="btn btn-danger hero-cta-btn">
                            <i class="fas fa-calculator me-2"></i>Request Quote
                        </a>
                        <a href="{{ url('/b2b/products') }}" class="btn btn-outline-light hero-cta-btn">
                            <i class="fas fa-briefcase me-2"></i>Explore B2B Solutions
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="container py-4 py-lg-5">

    <!-- CORE SERVICES -->
    <section class="mb-4 mb-lg-5">
        <h2 class="h4 fw-bold mb-3">Our Core Services</h2>
        <div class="row g-3 g-lg-4">
            @php
                $services = [
                    ['icon'=>'palette','title'=>'Designing','text'=>'Custom corporate identity, logo sets, and adaptable visual systems for scale.'],
                    ['icon'=>'print','title'=>'Printing','text'=>'Business-ready digital & offset printing with consistent color and finishing.'],
                    ['icon'=>'building','title'=>'Branding','text'=>'Fleet wraps, signage programs, uniforms, and workspace branding at scale.'],
                    ['icon'=>'bullhorn','title'=>'Promotion','text'=>'Trade show kits, roll-ups, pop-ups, and outdoor promotion assets.'],
                    ['icon'=>'chart-line','title'=>'Marketing','text'=>'Campaign assets and brand awareness initiatives for B2B growth.'],
                ];
            @endphp
            @foreach($services as $svc)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="service-card p-3 p-lg-4 reveal h-100">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="service-icon">
                            <i class="fas fa-{{ $svc['icon'] }}"></i>
                        </div>
                        <div>
                            <div class="service-title">{{ $svc['title'] }}</div>
                            <div class="text-muted small">CHIBO BRAND</div>
                        </div>
                    </div>
                    <p class="service-text mb-0">{{ $svc['text'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <!-- WHY CHOOSE US -->
    <section class="mb-4 mb-lg-5">
        <h2 class="h4 fw-bold mb-3">Why Choose CHIBO BRAND?</h2>
        @php
            $why = [
                ['icon'=>'lightbulb', 'text'=>'Creative & Custom B2B Design'],
                ['icon'=>'users', 'text'=>'Experienced Team'],
                ['icon'=>'boxes', 'text'=>'Quality Materials at Scale'],
                ['icon'=>'money-bill-wave', 'text'=>'Competitive Rates'],
                ['icon'=>'clock', 'text'=>'Fast Turnaround'],
                ['icon'=>'thumbs-up', 'text'=>'Trusted by Businesses'],
            ];
        @endphp
        <div class="row g-3 g-lg-4">
            @foreach($why as $w)
            <div class="col-6 col-md-4">
                <div class="why-chip reveal d-flex align-items-center gap-2">
                    <i class="fas fa-{{ $w['icon'] }} why-icon"></i>
                    <span class="fw-semibold">{{ $w['text'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <!-- CTA BAND (no portfolio and no footer note to match retail) -->
    <section class="cta-band p-4 p-lg-5 mb-4 mb-lg-5 text-center">
        <h3 class="fw-bold mb-2">Ready to Power Your Brand at Scale?</h3>
        <p class="mb-3">Let’s plan your next campaign, rollout, or workspace branding.</p>
        <div class="d-flex justify-content-center gap-2">
            <a href="{{ url('/b2b/contact') }}" class="btn btn-light"><i class="fas fa-calculator me-1"></i> Get a Free Quote</a>
            <a href="{{ url('/b2b/contact') }}" class="btn btn-dark"><i class="fas fa-phone me-1"></i> Contact Us Today</a>
        </div>
    </section>
</div>

@push('scripts')
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
        // Remove active class from all slides and indicators
        slides.forEach(slide => slide.classList.remove('active'));
        indicators.forEach(indicator => indicator.classList.remove('active'));
        
        // Add active class to current slide and indicator
        slides[index].classList.add('active');
        if (indicators[index]) {
            indicators[index].classList.add('active');
        }
        
        currentSlide = index;
    }
    
    function nextSlide() {
        const nextIndex = (currentSlide + 1) % slides.length;
        showSlide(nextIndex);
    }
    
    function prevSlide() {
        const prevIndex = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(prevIndex);
    }
    
    function startSlideshow() {
        slideInterval = setInterval(nextSlide, 5000); // Change slide every 5 seconds
    }
    
    function stopSlideshow() {
        clearInterval(slideInterval);
    }
    
    // Event listeners
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            nextSlide();
            stopSlideshow();
            startSlideshow(); // Restart timer
        });
    }
    
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            prevSlide();
            stopSlideshow();
            startSlideshow(); // Restart timer
        });
    }
    
    // Indicator click events
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            showSlide(index);
            stopSlideshow();
            startSlideshow(); // Restart timer
        });
    });
    
    // Pause slideshow on hover
    slideshow.addEventListener('mouseenter', stopSlideshow);
    slideshow.addEventListener('mouseleave', startSlideshow);
    
    // Start slideshow if there are multiple slides
    if (slides.length > 1) {
        startSlideshow();
    }
});

// Reveal animation observer
const observer = new IntersectionObserver((entries)=>{
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('show'); });
}, { threshold:.1 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>
@endpush
@endsection


