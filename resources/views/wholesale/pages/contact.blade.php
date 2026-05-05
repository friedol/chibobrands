@extends('public.layouts.app')

@section('title', 'Contact Us - CHIBO BRAND B2B')
@section('description', 'Let’s talk about your next project. Contact CHIBO BRAND for wholesale designing, printing, and branding services.')

@section('content')
@push('styles')
<style>
    /* Hero Banner - Dark + Red 3D Theme */
    .hero-banner{background:linear-gradient(135deg,#0a0a0a 0%,#1a0000 50%,#000000 100%);padding:60px 0;position:relative;overflow:hidden;min-height:320px}
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
    
    .hero-badge{display:inline-flex;align-items:center;background:linear-gradient(135deg,rgba(255,0,0,0.2),rgba(255,0,0,0.1));color:#ff0000;padding:8px 18px;border-radius:50px;font-size:12px;font-weight:700;border:2px solid rgba(255,0,0,0.3);box-shadow:0 0 20px rgba(255,0,0,0.2);text-transform:uppercase;letter-spacing:1px}
    .hero-title{font-size:clamp(24px,5vw,40px);font-weight:900;color:#fff;line-height:1.2;letter-spacing:-1px;text-shadow:0 0 30px rgba(255,0,0,0.3),0 0 60px rgba(255,0,0,0.2)}
    .hero-subtitle{font-size:clamp(14px,2vw,18px);color:rgba(255,255,255,0.85);line-height:1.6;text-shadow:0 2px 10px rgba(0,0,0,0.5)}
    .hero-cta-btn{padding:12px 24px;border-radius:50px;font-weight:600;font-size:14px;transition:all 0.3s;border:2px solid;text-transform:uppercase;letter-spacing:0.5px}
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
    .chip{ border-radius:14px; background:#fff; border:1px solid #eef2f7; padding:1rem; height:100%; }
    .icon-box{ width:52px; height:52px; border-radius:12px; display:flex; align-items:center; justify-content:center;
        background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color:#fff; }
    .cta-band{ background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color:#fff; border-radius:18px; }
    .form-control:focus{ box-shadow:0 0 0 .2rem rgba(220,53,69,.15); border-color: var(--brand); }
    .is-valid{ border-color:#28a745 !important; }
    .valid-icon{ color:#28a745; }
    .social a{ color:var(--ink); }
    .social a:hover{ color:var(--brand); }
    .map-embed{ border:0; width:100%; height:280px; border-radius:12px; }
    
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
                        <i class="fas fa-envelope me-2"></i>Contact Us
                    </div>
                    <h1 class="hero-title mb-4">
                        Let's Talk About Your Next Project
                    </h1>
                    <p class="hero-subtitle mb-5">
                        We're here to help you design, print, and grow your brand with creative solutions that make an impact. Get in touch today!
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="#contact-form" class="btn btn-danger hero-cta-btn">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </a>
                        <a href="https://wa.me/255655392319" target="_blank" class="btn btn-outline-light hero-cta-btn">
                            <i class="fab fa-whatsapp me-2"></i>WhatsApp Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="container py-4 py-lg-5">

    <!-- CONTACT INFORMATION -->
    <section class="mb-4 mb-lg-5">
        <h2 class="h4 fw-bold mb-3">Contact Information</h2>
        <div class="row g-3 g-lg-4">
            <div class="col-md-4">
                <div class="chip h-100">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="icon-box"><i class="fas fa-phone"></i></div>
                        <div class="fw-semibold">Phone</div>
                    </div>
                    <div class="text-muted small">+255 753 883 382</div>
                    <div class="text-muted small">+255 68 238 7901</div>
                    <div class="text-muted small">+255 65 539 2319</div>
                    <div class="text-muted small">+255 794 366 877</div>
                    <div class="text-muted small">+255 762 332 849</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chip h-100">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="icon-box"><i class="fas fa-envelope"></i></div>
                        <div class="fw-semibold">Email</div>
                    </div>
                    <div class="text-muted small">info@chibobrand.com</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chip h-100">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="icon-box"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="fw-semibold">Address</div>
                    </div>
                    <div class="text-muted small">CHIBO BRAND Headquarters</div>
                    <div class="text-muted small">Kinondoni, Mwijuma Road, opposite Vijana House</div>
                    <div class="text-muted small">Dar es Salaam, Tanzania</div>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <iframe class="map-embed" src="https://maps.google.com/maps?q=Kinondoni%2C%20Mwijuma%20Road%2C%20opposite%20Vijana%20House%2C%20Dar%20es%20Salaam&t=&z=16&ie=UTF8&iwloc=&output=embed" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </section>

    <!-- CONTACT FORM -->
    <section class="mb-4 mb-lg-5" id="contact-form">
        <h2 class="h4 fw-bold mb-3">Send Us a Message</h2>
        <div class="row">
            <div class="col-lg-8">
                <form method="POST" action="{{ route('contact.submit') }}" class="card p-3 p-lg-4" novalidate>
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="name" class="form-control" required>
                                <span class="input-group-text bg-transparent border-0 valid-icon">✅</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="email" name="email" class="form-control" required>
                                <span class="input-group-text bg-transparent border-0 valid-icon">✅</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+255 xxx xxx xxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" placeholder="Quote / Inquiry / Collaboration">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message</label>
                            <textarea name="message" rows="5" class="form-control" placeholder="Tell us about your project..."></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="antispam">
                                <label class="form-check-label" for="antispam">I am not a robot</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-danger"><i class="fas fa-paper-plane me-1"></i> Submit</button>
                        <a href="https://wa.me/255655392319" target="_blank" class="btn btn-outline-success"><i class="fab fa-whatsapp me-1"></i> WhatsApp Us</a>
                    </div>
                    @if(session('status'))
                        <div class="alert alert-success mt-3">{{ session('status') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger mt-3">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </form>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0">
                <div class="card p-3 h-100">
                    <h5 class="fw-bold">Business Hours</h5>
                    <ul class="list-unstyled text-muted small mb-4">
                        <li>Monday – Saturday: 8:00 AM – 6:00 PM</li>

                    </ul>
                    <p class="text-muted small">Visit our office during working hours or send us a message — we’ll respond as soon as possible.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- SOCIAL MEDIA -->
    <section class="mb-4 mb-lg-5">
        <h2 class="h4 fw-bold mb-3">Connect With Us Online</h2>
        <p class="text-muted">Follow us to stay updated on our latest projects, offers, and creative works.</p>
        <div class="d-flex gap-3 social">
            <a href="https://www.instagram.com/chibobrands" target="_blank" aria-label="Facebook"><i class="fab fa-facebook fa-lg"></i></a>
            <a href="https://www.instagram.com/chibobrands" target="_blank" aria-label="Instagram"><i class="fab fa-instagram fa-lg"></i></a>
            <a href="https://wa.me/255655392319" target="_blank" aria-label="WhatsApp"><i class="fab fa-whatsapp fa-lg"></i></a>
            <a href="https://www.tiktok.com/@chibo_brandsmifuko1?_t=ZM-90eYvciqi7A&_r=1" target="_blank" aria-label="TikTok"><i class="fab fa-tiktok fa-lg"></i></a>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-band p-4 p-lg-5 mb-4 mb-lg-5 text-center">
        <h3 class="fw-bold mb-2">Let’s Bring Your Brand to Life — Start Your Project with CHIBO BRAND Today!</h3>
        <div class="d-flex justify-content-center gap-2">
            <a href="{{ url('/b2b/contact') }}" class="btn btn-light"><i class="fas fa-calculator me-1"></i> Request a Quote</a>
            <a href="{{ url('/b2b/services') }}" class="btn btn-dark"><i class="fas fa-layer-group me-1"></i> Explore Services</a>
        </div>
    </section>

    <!-- FOOTER NOTE -->
    <div class="text-center">
        <div class="small text-white fw-bold fst-italic p-2" style="background:var(--brand); border-radius:10px; display:inline-block;">
            make sure you’re registered to login
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <div class="success-checkmark mx-auto mb-3">
                        <div class="check-icon">
                            <span class="icon-line line-tip"></span>
                            <span class="icon-line line-long"></span>
                            <div class="icon-circle"></div>
                            <div class="icon-fix"></div>
                        </div>
                    </div>
                </div>
                <h3 class="text-success fw-bold mb-3">Message Sent Successfully!</h3>
                <p class="text-muted mb-4">
                    Thank you for contacting us! We have received your message and will get back to you as soon as possible.
                </p>
                <button type="button" class="btn btn-success btn-lg px-5" data-bs-dismiss="modal">
                    <i class="fas fa-check me-2"></i>Got it!
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Success Checkmark Animation */
    .success-checkmark {
        width: 80px;
        height: 80px;
        margin: 0 auto;
    }
    
    .success-checkmark .check-icon {
        width: 80px;
        height: 80px;
        position: relative;
        border-radius: 50%;
        box-sizing: content-box;
        border: 4px solid #28a745;
    }
    
    .success-checkmark .check-icon::before {
        top: 3px;
        left: -2px;
        width: 30px;
        transform-origin: 100% 50%;
        border-radius: 100px 0 0 100px;
    }
    
    .success-checkmark .check-icon::after {
        top: 0;
        left: 30px;
        width: 60px;
        transform-origin: 0 50%;
        border-radius: 0 100px 100px 0;
        animation: rotate-circle 4.25s ease-in;
    }
    
    .success-checkmark .check-icon::before, .success-checkmark .check-icon::after {
        content: '';
        height: 100px;
        position: absolute;
        background: #fff;
        transform: rotate(-45deg);
    }
    
    .success-checkmark .check-icon .icon-line {
        height: 5px;
        background-color: #28a745;
        display: block;
        border-radius: 2px;
        position: absolute;
        z-index: 10;
    }
    
    .success-checkmark .check-icon .icon-line.line-tip {
        top: 46px;
        left: 14px;
        width: 25px;
        transform: rotate(45deg);
        animation: icon-line-tip 0.75s;
    }
    
    .success-checkmark .check-icon .icon-line.line-long {
        top: 38px;
        right: 8px;
        width: 47px;
        transform: rotate(-45deg);
        animation: icon-line-long 0.75s;
    }
    
    .success-checkmark .check-icon .icon-circle {
        top: -4px;
        left: -4px;
        z-index: 10;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        position: absolute;
        box-sizing: content-box;
        border: 4px solid rgba(40, 167, 69, .5);
    }
    
    .success-checkmark .check-icon .icon-fix {
        top: 8px;
        width: 5px;
        left: 26px;
        z-index: 1;
        height: 85px;
        position: absolute;
        transform: rotate(-45deg);
        background-color: #fff;
    }
    
    @keyframes rotate-circle {
        0% {
            transform: rotate(-45deg);
        }
        5% {
            transform: rotate(-45deg);
        }
        12% {
            transform: rotate(-405deg);
        }
        100% {
            transform: rotate(-405deg);
        }
    }
    
    @keyframes icon-line-tip {
        0% {
            width: 0;
            left: 1px;
            top: 19px;
        }
        54% {
            width: 0;
            left: 1px;
            top: 19px;
        }
        70% {
            width: 50px;
            left: -8px;
            top: 37px;
        }
        84% {
            width: 17px;
            left: 21px;
            top: 48px;
        }
        100% {
            width: 25px;
            left: 14px;
            top: 46px;
        }
    }
    
    @keyframes icon-line-long {
        0% {
            width: 0;
            right: 46px;
            top: 54px;
        }
        65% {
            width: 0;
            right: 46px;
            top: 54px;
        }
        84% {
            width: 55px;
            right: 0px;
            top: 35px;
        }
        100% {
            width: 47px;
            right: 8px;
            top: 38px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Show success modal if message was sent
    @if(session('status'))
        document.addEventListener('DOMContentLoaded', function() {
            var successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
        });
    @endif
</script>
@endpush

@endsection
