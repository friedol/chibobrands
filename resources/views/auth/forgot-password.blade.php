<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - CHIBO BRAND</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>

<!-- 3D Background -->
<div class="forgot-background">
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
        <div class="shape shape-5"></div>
    </div>
    <div class="gradient-overlay"></div>
    
    <!-- Moving Bubbles -->
    <div class="bubbles">
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
    </div>
</div>

<!-- Forgot Password Container -->
<div class="forgot-container">
    <div class="forgot-card">
        <div class="forgot-card-inner">
            <div class="forgot-content">
                <!-- Logo -->
                <div class="forgot-logo">
                    <div class="logo-image">
                        <img src="{{ asset('images/logo.webp') }}" alt="CHIBO BRAND Logo">
                    </div>
                    <p class="brand-tagline">Professional Printing & Branding</p>
                </div>

                <!-- Forgot Password Form -->
                <form method="POST" action="{{ route('password.email') }}" class="forgot-form">
                    @csrf
                    
                    <h2 class="form-title">Forgot Password?</h2>
                    <p class="form-subtitle">Enter your email and we'll send you a reset link</p>
                    
                    <!-- Success Message -->
                    @if (session('status'))
                        <div class="success-alert">
                            <i class="fas fa-check-circle"></i>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif
                    
                    <!-- Email Input -->
                    <div class="input-wrapper">
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <input type="email" 
                               class="modern-input @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               placeholder="Email Address"
                               required 
                               autofocus>
                        <label class="floating-label">Email Address</label>
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="forgot-btn">
                        <span class="btn-text">Send Reset Link</span>
                        <span class="btn-icon"><i class="fas fa-paper-plane"></i></span>
                        <div class="btn-shine"></div>
                    </button>
                </form>

                <!-- Divider -->
                <div class="divider">
                    <span>OR</span>
                </div>
                
                <!-- Back to Login -->
                <div class="login-link">
                    <p>Remember your password?</p>
                    <a href="{{ route('login') }}" class="signin-btn">
                        <i class="fas fa-sign-in-alt"></i> Back to Login
                    </a>
                </div>
                
                <!-- Back to Home -->
                <a href="{{ url('/') }}" class="back-home">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
</div>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Nunito Sans', sans-serif;
    overflow-x: hidden;
    min-height: 100vh;
    background: #000;
}

/* 3D Background */
.forgot-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #000000 100%);
    z-index: 0;
}

.gradient-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle at 50% 50%, rgba(255, 0, 0, 0.1) 0%, transparent 70%);
    animation: pulseGlow 8s ease-in-out infinite;
}

@keyframes pulseGlow {
    0%, 100% { opacity: 0.5; }
    50% { opacity: 1; }
}

.floating-shapes {
    position: absolute;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.shape {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    opacity: 0.3;
    animation: float 20s infinite ease-in-out;
}

.shape-1 {
    width: 300px;
    height: 300px;
    background: linear-gradient(135deg, #FF0000, #cc0000);
    top: 10%;
    left: 10%;
}

.shape-2 {
    width: 250px;
    height: 250px;
    background: linear-gradient(135deg, #FF0000, #990000);
    top: 60%;
    right: 10%;
    animation-delay: 2s;
}

.shape-3 {
    width: 200px;
    height: 200px;
    background: linear-gradient(135deg, #cc0000, #FF0000);
    bottom: 10%;
    left: 30%;
    animation-delay: 4s;
}

.shape-4 {
    width: 180px;
    height: 180px;
    background: linear-gradient(135deg, #990000, #FF0000);
    top: 30%;
    right: 30%;
    animation-delay: 6s;
}

.shape-5 {
    width: 220px;
    height: 220px;
    background: linear-gradient(135deg, #FF0000, #cc0000);
    bottom: 30%;
    right: 20%;
    animation-delay: 8s;
}

@keyframes float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    25% { transform: translate(30px, -30px) scale(1.1); }
    50% { transform: translate(-20px, 20px) scale(0.9); }
    75% { transform: translate(20px, 30px) scale(1.05); }
}

/* Bubbles */
.bubbles {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 1;
}

.bubble {
    position: absolute;
    bottom: -100px;
    border-radius: 50%;
    opacity: 0.5;
    background: radial-gradient(circle at 30% 30%, rgba(255, 0, 0, 0.3), rgba(139, 0, 0, 0.5));
    animation: rise 15s infinite ease-in;
}

.bubble:nth-child(1) { width: 60px; height: 60px; left: 5%; animation-duration: 13s; }
.bubble:nth-child(2) { width: 80px; height: 80px; left: 15%; animation-duration: 15s; animation-delay: 2s; }
.bubble:nth-child(3) { width: 70px; height: 70px; left: 25%; animation-duration: 14s; animation-delay: 4s; }
.bubble:nth-child(4) { width: 90px; height: 90px; left: 35%; animation-duration: 16s; animation-delay: 1s; }
.bubble:nth-child(5) { width: 65px; height: 65px; left: 45%; animation-duration: 12s; animation-delay: 3s; }
.bubble:nth-child(6) { width: 75px; height: 75px; left: 55%; animation-duration: 14s; animation-delay: 5s; }
.bubble:nth-child(7) { width: 85px; height: 85px; left: 65%; animation-duration: 17s; }
.bubble:nth-child(8) { width: 70px; height: 70px; left: 75%; animation-duration: 13s; animation-delay: 6s; }
.bubble:nth-child(9) { width: 95px; height: 95px; left: 85%; animation-duration: 15s; animation-delay: 2s; }
.bubble:nth-child(10) { width: 60px; height: 60px; left: 95%; animation-duration: 14s; animation-delay: 4s; }
.bubble:nth-child(11) { width: 80px; height: 80px; left: 10%; animation-duration: 16s; animation-delay: 7s; }
.bubble:nth-child(12) { width: 70px; height: 70px; left: 30%; animation-duration: 13s; animation-delay: 1s; }
.bubble:nth-child(13) { width: 85px; height: 85px; left: 50%; animation-duration: 15s; animation-delay: 3s; }
.bubble:nth-child(14) { width: 75px; height: 75px; left: 70%; animation-duration: 14s; animation-delay: 5s; }
.bubble:nth-child(15) { width: 90px; height: 90px; left: 90%; animation-duration: 16s; animation-delay: 2s; }
.bubble:nth-child(16) { width: 65px; height: 65px; left: 20%; animation-duration: 12s; animation-delay: 4s; }
.bubble:nth-child(17) { width: 85px; height: 85px; left: 40%; animation-duration: 17s; animation-delay: 1s; }
.bubble:nth-child(18) { width: 70px; height: 70px; left: 60%; animation-duration: 13s; animation-delay: 6s; }
.bubble:nth-child(19) { width: 95px; height: 95px; left: 80%; animation-duration: 15s; animation-delay: 3s; }
.bubble:nth-child(20) { width: 60px; height: 60px; left: 12%; animation-duration: 14s; animation-delay: 5s; }

@keyframes rise {
    0% { bottom: -100px; transform: translateX(0); opacity: 0.5; }
    50% { transform: translateX(100px); opacity: 0.3; }
    100% { bottom: 110%; transform: translateX(-100px); opacity: 0; }
}

/* Forgot Container */
.forgot-container {
    position: relative;
    z-index: 1;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
}

.forgot-card {
    width: 100%;
    max-width: 500px;
    animation: cardEntrance 0.8s ease;
}

@keyframes cardEntrance {
    0% { opacity: 0; transform: translateY(50px); }
    100% { opacity: 1; transform: translateY(0); }
}

.forgot-card-inner {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 30px;
    padding: 50px 50px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), 0 0 100px rgba(255, 0, 0, 0.2);
}

/* Logo */
.forgot-logo {
    text-align: center;
    margin-bottom: 30px;
}

.logo-image {
    width: 110px;
    height: 110px;
    margin: 0 auto 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    filter: drop-shadow(0 5px 15px rgba(255, 0, 0, 0.2));
}

.logo-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.brand-tagline {
    font-size: 13px;
    color: #6c757d;
    font-weight: 500;
}

/* Form */
.forgot-form {
    margin-bottom: 25px;
}

.form-title {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 8px;
    text-align: center;
}

.form-subtitle {
    font-size: 14px;
    color: #6c757d;
    text-align: center;
    margin-bottom: 30px;
}

/* Input Wrapper */
.input-wrapper {
    position: relative;
    margin-bottom: 25px;
}

.input-icon {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    font-size: 18px;
    z-index: 2;
    transition: all 0.3s ease;
}

.modern-input {
    width: 100%;
    padding: 16px 50px 16px 50px;
    border: 2px solid #e9ecef;
    border-radius: 15px;
    font-size: 15px;
    font-weight: 500;
    background: #f8f9fa;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
}

.modern-input:focus {
    border-color: #FF0000;
    background: white;
    box-shadow: 0 0 0 4px rgba(255, 0, 0, 0.1);
    transform: translateY(-2px);
}

.modern-input:focus + .floating-label,
.modern-input:not(:placeholder-shown) + .floating-label {
    top: -10px;
    left: 15px;
    font-size: 11px;
    color: #FF0000;
    background: white;
    padding: 0 8px;
}

.modern-input:focus ~ .input-icon {
    color: #FF0000;
    transform: translateY(-50%) scale(1.1);
}

.floating-label {
    position: absolute;
    left: 50px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    font-size: 15px;
    pointer-events: none;
    transition: all 0.3s ease;
}

.error-message {
    display: block;
    color: #dc3545;
    font-size: 12px;
    margin-top: 6px;
    margin-left: 4px;
    font-weight: 500;
}

/* Success Alert */
.success-alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    border: 2px solid #28a745;
    border-radius: 12px;
    color: #155724;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 25px;
    animation: slideIn 0.5s ease;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.success-alert i {
    font-size: 20px;
    color: #28a745;
}

/* Forgot Button */
.forgot-btn {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #FF0000, #cc0000);
    border: none;
    border-radius: 15px;
    color: white;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.forgot-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(255, 0, 0, 0.4);
}

.btn-shine {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s ease;
}

.forgot-btn:hover .btn-shine {
    left: 100%;
}

.btn-icon {
    transition: transform 0.3s ease;
}

.forgot-btn:hover .btn-icon {
    transform: translateX(5px);
}

/* Divider */
.divider {
    position: relative;
    text-align: center;
    margin: 25px 0;
}

.divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    width: 100%;
    height: 1px;
    background: linear-gradient(90deg, transparent, #e9ecef, transparent);
}

.divider span {
    position: relative;
    background: white;
    padding: 0 15px;
    color: #6c757d;
    font-size: 13px;
    font-weight: 600;
}

/* Login Link */
.login-link {
    text-align: center;
    margin-bottom: 20px;
}

.login-link p {
    font-size: 14px;
    color: #6c757d;
    margin-bottom: 10px;
}

.signin-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border: 2px solid #e9ecef;
    border-radius: 12px;
    color: #2c3e50;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
}

.signin-btn:hover {
    background: linear-gradient(135deg, #FF0000, #cc0000);
    border-color: #FF0000;
    color: white;
    transform: translateY(-2px);
}

/* Back to Home */
.back-home {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #6c757d;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.back-home:hover {
    color: #FF0000;
}

/* Responsive */
@media (max-width: 576px) {
    .forgot-container {
        padding: 20px 15px;
    }
    
    .forgot-card-inner {
        padding: 35px 25px;
        border-radius: 20px;
    }
    
    .logo-image {
        width: 90px;
        height: 90px;
    }
    
    .form-title {
        font-size: 24px;
    }
    
    .form-subtitle {
        font-size: 13px;
    }
    
    .modern-input {
        padding: 14px 45px 14px 45px;
        font-size: 14px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.forgot-form');
    const submitBtn = document.querySelector('.forgot-btn');
    
    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
            submitBtn.disabled = true;
        });
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
