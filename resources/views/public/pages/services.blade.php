@extends('public.layouts.app')

@section('title', 'Our Services - CHIBO BRAND')
@section('description', 'Designing, Printing, Branding, Promotion, and Marketing services by CHIBO BRAND.')

@section('content')
@push('styles')
<style>
    /* Hero */
    .svc-hero{background:linear-gradient(135deg,#c0392b 0%,#dc3545 60%,#e74c3c 100%);padding:52px 0 44px;position:relative;overflow:hidden}
    .svc-hero .svc-bubbles{position:absolute;width:100%;height:100%;top:0;left:0;overflow:hidden;z-index:1;pointer-events:none}
    .svc-bubble{position:absolute;border-radius:50%;background:radial-gradient(circle at 30% 30%,rgba(255,255,255,0.25),rgba(255,255,255,0.04));box-shadow:inset 0 0 30px rgba(255,255,255,0.15),0 0 50px rgba(255,255,255,0.05);animation:svc-float 20s infinite ease-in-out;backdrop-filter:blur(2px)}
    .svc-bubble::before{content:'';position:absolute;top:10%;left:10%;width:40%;height:40%;border-radius:50%;background:radial-gradient(circle at 50% 50%,rgba(255,255,255,0.35),transparent)}
    .svc-bubble-1{width:180px;height:180px;left:8%;top:10%;animation-delay:0s;animation-duration:25s}
    .svc-bubble-2{width:120px;height:120px;right:12%;top:30%;animation-delay:3s;animation-duration:20s}
    .svc-bubble-3{width:220px;height:220px;left:45%;top:50%;animation-delay:6s;animation-duration:30s}
    .svc-bubble-4{width:150px;height:150px;right:28%;top:5%;animation-delay:2s;animation-duration:22s}
    .svc-bubble-5{width:100px;height:100px;left:28%;bottom:5%;animation-delay:4s;animation-duration:18s}
    .svc-bubble-6{width:200px;height:200px;right:5%;bottom:10%;animation-delay:5s;animation-duration:28s}
    @keyframes svc-float{
        0%,100%{transform:translate(0,0) scale(1) rotate(0deg)}
        25%{transform:translate(25px,-25px) scale(1.08) rotate(90deg)}
        50%{transform:translate(-15px,18px) scale(0.93) rotate(180deg)}
        75%{transform:translate(35px,8px) scale(1.04) rotate(270deg)}
    }
    .svc-badge{display:inline-block;background:rgba(255,255,255,0.18);border:1.5px solid rgba(255,255,255,0.35);color:#fff;padding:7px 22px;border-radius:50px;font-size:13px;font-style:italic;font-family:Georgia,'Times New Roman',serif;letter-spacing:0.3px;margin-bottom:16px;backdrop-filter:blur(6px)}
    .svc-title{font-size:clamp(1.8rem,4vw,2.6rem);font-weight:800;color:#fff;line-height:1.2;margin-bottom:12px;text-shadow:0 2px 10px rgba(0,0,0,0.2)}
    .svc-subtitle{font-size:clamp(0.85rem,1.5vw,1rem);color:rgba(255,255,255,0.85);max-width:480px;margin:0 auto;line-height:1.6}

    /* Trust Badges */
    .trust-badges{background:#f8f9fa;padding:20px 0;margin-bottom:2rem}
    .trust-badge{display:flex;align-items:center;gap:15px;padding:15px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);transition:all 0.3s}
    .trust-badge:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,0.1)}
    .trust-badge i{font-size:32px;color:#dc3545;min-width:40px;text-align:center}
    .trust-text strong{display:block;font-size:14px;color:#1a1a1a;margin-bottom:2px}
    .trust-text small{font-size:12px;color:#6c757d}

    :root{--brand:#dc3545;--brand-dark:#b52a37;--ink:#0f172a;--muted:#6b7280;--soft:#f7f7fb}
    .service-card{background:#fff;border:none;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.06);transition:transform .3s cubic-bezier(.2,.8,.2,1),box-shadow .3s;transform-style:preserve-3d}
    .service-card:hover{transform:translateY(-6px) rotateX(3deg) rotateY(-3deg);box-shadow:0 20px 45px rgba(0,0,0,.10)}
    .service-icon{width:54px;height:54px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--brand),var(--brand-dark));color:#fff;font-size:1.25rem;box-shadow:0 10px 20px rgba(220,53,69,.25);transform:translateZ(40px)}
    .service-title{font-weight:700;margin-bottom:.25rem}
    .service-text{color:var(--muted)}
    .why-chip{border-radius:14px;background:#fff;border:1px solid #eef2f7;padding:1rem;transition:.2s;height:100%}
    .why-chip:hover{box-shadow:0 10px 20px rgba(0,0,0,.07);transform:translateY(-3px)}
    .why-icon{color:var(--brand)}
    .mini-card{border:none;border-radius:14px;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,.06);transition:.25s}
    .mini-card img{width:100%;height:160px;object-fit:cover}
    .mini-card:hover{transform:translateY(-4px)}
    .cta-band{background:linear-gradient(135deg,var(--brand),var(--brand-dark));border-radius:18px;color:#fff;box-shadow:0 24px 48px rgba(220,53,69,.35)}
    .cta-band .btn{border-radius:999px;padding:.75rem 1.15rem}
    .reveal{opacity:0;transform:translateY(16px);transition:.6s ease}
    .reveal.show{opacity:1;transform:none}

    @media(max-width:768px){
        .svc-hero{padding:36px 0 30px}
        .svc-bubble-3,.svc-bubble-6{display:none}
        .trust-badge{flex-direction:column;text-align:center}
        .trust-badge i{margin-bottom:8px}
    }
</style>
@endpush

<!-- Hero Section -->
<div class="svc-hero">
    <div class="svc-bubbles">
        <div class="svc-bubble svc-bubble-1"></div>
        <div class="svc-bubble svc-bubble-2"></div>
        <div class="svc-bubble svc-bubble-3"></div>
        <div class="svc-bubble svc-bubble-4"></div>
        <div class="svc-bubble svc-bubble-5"></div>
        <div class="svc-bubble svc-bubble-6"></div>
    </div>
    <div class="container text-center" style="position:relative;z-index:10;">
        <span class="svc-badge">Our Services</span>
        <h1 class="svc-title">We Design. We Print. We Build Brands.</h1>
        <p class="svc-subtitle">At CHIBO BRAND, we bring your ideas to life through modern design, printing excellence, and strong brand identity that makes an impact.</p>
    </div>
</div>

<!-- Trust Badges -->
<div class="trust-badges">
    <div class="container">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="trust-badge">
                    <i class="fas fa-palette"></i>
                    <div class="trust-text">
                        <strong>Creative Design</strong>
                        <small>Professional quality</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="trust-badge">
                    <i class="fas fa-print"></i>
                    <div class="trust-text">
                        <strong>Quality Printing</strong>
                        <small>Premium materials</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="trust-badge">
                    <i class="fas fa-award"></i>
                    <div class="trust-text">
                        <strong>Brand Identity</strong>
                        <small>Stand out solutions</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="trust-badge">
                    <i class="fas fa-headset"></i>
                    <div class="trust-text">
                        <strong>24/7 Support</strong>
                        <small>Always available</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-4 py-lg-5">
    <section id="services">

    <!-- CORE SERVICES -->
    <section class="mb-4 mb-lg-5">
        <h2 class="h4 fw-bold mb-3">Our Core Services</h2>
        <div class="row g-3 g-lg-4">
            @php
                $services = [
                    ['icon'=>'palette','title'=>'Designing','text'=>'Visual identity, logo creation, layouts, and creative concepts crafted to fit your brand.'],
                    ['icon'=>'print','title'=>'Printing','text'=>'High-quality digital and offset printing for all formats and campaign sizes.'],
                    ['icon'=>'building','title'=>'Branding','text'=>'Vehicle wraps, signage, uniforms, office branding, and complete space branding.'],
                    ['icon'=>'bullhorn','title'=>'Promotion','text'=>'Event displays, promotional materials, outdoor advertising and activation kits.'],
                    ['icon'=>'chart-line','title'=>'Marketing','text'=>'Creative campaigns and brand awareness strategies that deliver results.'],
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
                ['icon'=>'lightbulb', 'text'=>'Creative and Custom Designs'],
                ['icon'=>'users', 'text'=>'Professional Team'],
                ['icon'=>'boxes', 'text'=>'High-Quality Materials'],
                ['icon'=>'money-bill-wave', 'text'=>'Affordable Rates'],
                ['icon'=>'clock', 'text'=>'Fast Turnaround Time'],
                ['icon'=>'thumbs-up', 'text'=>'100% Customer Satisfaction'],
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


    <!-- CTA BAND -->
    <section class="cta-band p-4 p-lg-5 mb-4 mb-lg-5 text-center">
        <h3 class="fw-bold mb-2">Ready to Bring Your Brand to Life?</h3>
        <p class="mb-3">Talk to our expert team and get a fast, tailored quotation for your project.</p>
        <div class="d-flex justify-content-center gap-2">
            <a href="{{ url('/contact') }}" class="btn btn-light"><i class="fas fa-calculator me-1"></i> Get a Free Quote</a>
            <a href="{{ url('/contact') }}" class="btn btn-dark"><i class="fas fa-phone me-1"></i> Contact Us Today</a>
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
