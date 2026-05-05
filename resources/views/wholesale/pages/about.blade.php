@extends('public.layouts.app')

@section('title', 'About Us - CHIBO BRAND B2B')
@section('description', 'We don’t just print — we build brands. Learn about CHIBO BRAND’s story, mission, vision, and values.')

@section('content')
@push('styles')
<style>
    /* Hero Banner - Dark + Red 3D Theme */
    .hero-banner{background:linear-gradient(135deg,#0a0a0a 0%,#1a0000 50%,#000000 100%);padding:80px 0;position:relative;overflow:hidden;min-height:450px}
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

    /* Trust Badges */
    .trust-badges{background:#f8f9fa;padding:20px 0;margin-bottom:2rem}
    .trust-badge{display:flex;align-items:center;gap:15px;padding:15px;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);transition:all 0.3s}
    .trust-badge:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,0.1)}
    .trust-badge i{font-size:32px;color:#ff0000;min-width:40px;text-align:center}
    .trust-text strong{display:block;font-size:14px;color:#1a1a1a;margin-bottom:2px}
    .trust-text small{font-size:12px;color:#6c757d}

    :root{ --brand:#dc3545; --brand-dark:#b52a37; --ink:#0f172a; --muted:#6b7280; }
    .card-soft{ border:none; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,.06); }
    .chip{ border-radius:14px; background:#fff; border:1px solid #eef2f7; padding:1rem; height:100%; }
    .icon-box{ width:52px; height:52px; border-radius:12px; display:flex; align-items:center; justify-content:center;
        background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color:#fff; }
    .mini{ border:none; border-radius:14px; overflow:hidden; box-shadow:0 10px 25px rgba(0,0,0,.06); }
    .mini img{ width:100%; height:140px; object-fit:cover; }
    .cta-band{ background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color:#fff; border-radius:18px; }
    .team-card{ border:none; border-radius:14px; overflow:hidden; box-shadow:0 10px 25px rgba(0,0,0,.06); }
    .team-card img{ width:100%; height:220px; object-fit:cover; }
    
    @media (max-width: 768px) {
        .hero-banner{padding:40px 0;min-height:350px}
        .trust-badge{flex-direction:column;text-align:center}
        .trust-badge i{margin-bottom:8px}
    }
</style>
@endpush

<!-- Modern Hero Banner with 3D Bubbles -->
<div class="hero-banner mb-0">
    <!-- Animated 3D Bubbles -->
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
                        <i class="fas fa-award me-2"></i>About CHIBO BRAND
                    </div>
                    <h1 class="hero-title mb-4">
                        We Don't Just Print — We Build Brands
                    </h1>
                    <p class="hero-subtitle mb-5">
                        Your trusted partner in designing, printing, and branding powerful visual identities that connect with your audience and drive business growth.
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="{{ url('/b2b/services') }}" class="btn btn-danger hero-cta-btn">
                            <i class="fas fa-layer-group me-2"></i>Explore Services
                        </a>
                        <a href="{{ url('/b2b/contact') }}" class="btn btn-outline-light hero-cta-btn">
                            <i class="fas fa-envelope me-2"></i>Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Trust Badges -->
<div class="trust-badges">
    <div class="container">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="trust-badge">
                    <i class="fas fa-users"></i>
                    <div class="trust-text">
                        <strong>100+ Clients</strong>
                        <small>Trusted partners</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="trust-badge">
                    <i class="fas fa-project-diagram"></i>
                    <div class="trust-text">
                        <strong>500+ Projects</strong>
                        <small>Successfully delivered</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="trust-badge">
                    <i class="fas fa-clock"></i>
                    <div class="trust-text">
                        <strong>5+ Years</strong>
                        <small>Industry experience</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="trust-badge">
                    <i class="fas fa-trophy"></i>
                    <div class="trust-text">
                        <strong>Quality First</strong>
                        <small>Premium standards</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-4 py-lg-5">

    <!-- WHO WE ARE -->
    <section class="mb-4 mb-lg-5">
        <h2 class="h4 fw-bold mb-3">Who We Are</h2>
        <div class="row g-3 g-lg-4 align-items-center">
            <div class="col-md-6">
                <div class="card-soft p-2 p-lg-3">
                    <img src="{{ asset('images/default.svg') }}" alt="Our Team" class="img-fluid rounded">
                </div>
            </div>
            <div class="col-md-6">
                <p class="text-muted">CHIBO BRAND is a creative and printing agency driven by passion, design excellence, and innovation. We specialize in helping businesses bring their brands to life through outstanding visuals, high-quality printing, and impactful marketing materials. With a talented team and cutting-edge equipment, we deliver top‑notch branding solutions that make every client stand out.</p>
            </div>
        </div>
    </section>

    <!-- MISSION / VISION / VALUES -->
    <section class="mb-4 mb-lg-5">
        <div class="row g-3 g-lg-4">
            <div class="col-md-4">
                <div class="chip d-flex gap-3">
                    <div class="icon-box"><i class="fas fa-bullseye"></i></div>
                    <div>
                        <div class="fw-bold">Mission</div>
                        <div class="text-muted small">To help businesses and individuals express their brand identity through creative design, quality printing, and strong visual communication.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chip d-flex gap-3">
                    <div class="icon-box"><i class="fas fa-eye"></i></div>
                    <div>
                        <div class="fw-bold">Vision</div>
                        <div class="text-muted small">To be a leading branding and printing company recognized for innovation, reliability, and excellence across Africa.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chip d-flex gap-3">
                    <div class="icon-box"><i class="fas fa-heart"></i></div>
                    <div>
                        <div class="fw-bold">Core Values</div>
                        <div class="text-muted small">Creativity & Innovation · Quality & Consistency · Integrity & Professionalism · Customer Satisfaction</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- WHAT WE DO BEST -->
    <section class="mb-4 mb-lg-5">
        <h2 class="h4 fw-bold mb-3">What We Do Best</h2>
        <p class="text-muted">From custom designs to full‑scale branding and marketing solutions, CHIBO BRAND provides everything your business needs to stand out.</p>
        @php
            $best = [
                ['icon'=>'palette','title'=>'Designing','text'=>'Creative concepts, logos, and brand systems.'],
                ['icon'=>'print','title'=>'Printing','text'=>'Digital, offset, and large‑format printing.'],
                ['icon'=>'building','title'=>'Branding','text'=>'Vehicle wraps, signage, uniforms, and spaces.'],
                ['icon'=>'bullhorn','title'=>'Promotion','text'=>'Event kits, roll‑ups, and outdoor media.'],
                ['icon'=>'chart-line','title'=>'Marketing','text'=>'Campaign assets and brand awareness.'],
            ];
        @endphp
        <div class="row g-3 g-lg-4">
            @foreach($best as $b)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card-soft p-3 h-100">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="icon-box"><i class="fas fa-{{ $b['icon'] }}"></i></div>
                        <div class="fw-semibold">{{ $b['title'] }}</div>
                    </div>
                    <div class="text-muted small">{{ $b['text'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <!-- OUR JOURNEY (TIMELINE) -->
    <section class="mb-4 mb-lg-5">
        <h2 class="h4 fw-bold mb-3">Our Journey</h2>
        <div class="row g-3 g-lg-4">
            @php
                $steps = [
                    ['title'=>'The Vision', 'text'=>'Started with a vision for creative design.'],
                    ['title'=>'Pro Printing', 'text'=>'Expanded to professional printing and large‑format branding.'],
                    ['title'=>'Strong Partnerships', 'text'=>'Partnered with top brands to deliver nationwide campaigns.'],
                    ['title'=>'Today & Beyond', 'text'=>'Continuing to inspire through quality and innovation.'],
                ];
            @endphp
            @foreach($steps as $i => $s)
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card-soft p-3 h-100">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="badge bg-danger rounded-pill">{{ $i+1 }}</div>
                        <div class="fw-semibold">{{ $s['title'] }}</div>
                    </div>
                    <div class="text-muted small">{{ $s['text'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    

    <!-- WHY CLIENTS CHOOSE US -->
    <section class="mb-4 mb-lg-5">
        <h2 class="h4 fw-bold mb-3">Why Businesses Trust CHIBO BRAND</h2>
        @php
            $reasons = [
                ['icon'=>'bolt','text'=>'Reliable and Fast Service'],
                ['icon'=>'user-tie','text'=>'Professional Design Team'],
                ['icon'=>'cogs','text'=>'Modern Equipment'],
                ['icon'=>'money-bill','text'=>'Affordable Pricing'],
                ['icon'=>'handshake','text'=>'Long‑Term Partnerships'],
                ['icon'=>'check-circle','text'=>'100% Quality Guarantee'],
            ];
        @endphp
        <div class="row g-3 g-lg-4">
            @foreach($reasons as $r)
            <div class="col-6 col-md-4">
                <div class="chip d-flex align-items-center gap-2">
                    <i class="fas fa-{{ $r['icon'] }}" style="color:var(--brand);"></i>
                    <span class="fw-semibold">{{ $r['text'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-band p-4 p-lg-5 mb-4 mb-lg-5 text-center">
        <h3 class="fw-bold mb-2">Let’s Create Something Amazing Together!</h3>
        <p class="mb-3">Talk to our team and get a fast, tailored quotation for your project.</p>
        <div class="d-flex justify-content-center gap-2">
            <a href="{{ url('/b2b/contact') }}" class="btn btn-light"><i class="fas fa-calculator me-1"></i> Request a Quote</a>
            <a href="{{ url('/b2b/services') }}" class="btn btn-dark"><i class="fas fa-layer-group me-1"></i> Explore Services</a>
        </div>
    </section>

    <!-- FOOTER NOTE -->
    <p class="text-center fw-bold fst-italic text-white p-3" style="background:var(--brand); border-radius:10px;">
        make sure you’re registered to login
    </p>
</div>

@endsection


