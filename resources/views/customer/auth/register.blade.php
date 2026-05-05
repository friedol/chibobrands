<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CHIBO BRAND</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ url('favicon.ico') }}?v={{ time() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>

<!-- 3D Background -->
<div class="register-background">
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
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
    </div>
</div>

<!-- Register Container -->
<div class="register-container">
    <div class="register-card">
        <div class="register-card-inner">
            <div class="register-content">
                <!-- Logo -->
                <div class="register-logo">
                    <div class="logo-image">
                        <img src="{{ asset('images/logo.webp') }}" alt="CHIBO BRAND Logo">
                    </div>
                    <p class="brand-tagline">Professional Printing & Branding</p>
                </div>

                <!-- Register Form -->
                <form method="POST" action="{{ route('customer.register') }}" class="register-form">
                    @csrf
                    
                    <h2 class="form-title">Create Account</h2>
                    <p class="form-subtitle">Join CHIBO BRAND today</p>
                    
                    <div class="form-grid">
                            
                            <div class="row">
                                <!-- Personal Information -->
                                <div class="col-md-6">
                                    <h5 class="mb-3 text-primary">Personal Information</h5>
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               id="name" 
                                               name="name" 
                                               value="{{ old('name') }}" 
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email') }}" 
                                               required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" 
                                               class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" 
                                               name="phone" 
                                               value="{{ old('phone') }}" 
                                               required>
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               id="password" 
                                               name="password" 
                                               required>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" 
                                               class="form-control" 
                                               id="password_confirmation" 
                                               name="password_confirmation" 
                                               required>
                                    </div>
                                </div>

                                <!-- Business Information -->
                                <div class="col-md-6">
                                    <h5 class="mb-3 text-primary">Business Information</h5>
                                    
                                    <div class="mb-3">
                                        <label for="company_name" class="form-label">Company Name</label>
                                        <input type="text" 
                                               class="form-control @error('company_name') is-invalid @enderror" 
                                               id="company_name" 
                                               name="company_name" 
                                               value="{{ old('company_name') }}">
                                        @error('company_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="business_type" class="form-label">Business Type</label>
                                        <select class="form-select @error('business_type') is-invalid @enderror" 
                                                id="business_type" 
                                                name="business_type">
                                            <option value="">Select Business Type</option>
                                            <option value="retail" {{ old('business_type') == 'retail' ? 'selected' : '' }}>Retail Store</option>
                                            <option value="wholesale" {{ old('business_type') == 'wholesale' ? 'selected' : '' }}>Wholesale Distributor</option>
                                            <option value="printing" {{ old('business_type') == 'printing' ? 'selected' : '' }}>Printing Company</option>
                                            <option value="advertising" {{ old('business_type') == 'advertising' ? 'selected' : '' }}>Advertising Agency</option>
                                            <option value="corporate" {{ old('business_type') == 'corporate' ? 'selected' : '' }}>Corporate</option>
                                            <option value="other" {{ old('business_type') == 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        @error('business_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="address" class="form-label">Business Address</label>
                                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                                  id="address" 
                                                  name="address" 
                                                  rows="3">{{ old('address') }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="is_wholesale" 
                                                   name="is_wholesale" 
                                                   value="1" 
                                                   {{ old('is_wholesale') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_wholesale">
                                                I am interested in wholesale pricing
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="terms" 
                                                   name="terms" 
                                                   value="1" 
                                                   required>
                                            <label class="form-check-label" for="terms">
                                                I agree to the <a href="#" class="text-primary">Terms and Conditions</a>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Create Account
                                </button>
                            </div>
                    </div>
                </form>

                <!-- Divider -->
                <div class="divider">
                    <span>OR</span>
                </div>
                
                <!-- Login Link -->
                <div class="login-link">
                    <p>Already have an account?</p>
                    <a href="{{ route('login') }}" class="signin-btn">
                        <i class="fas fa-sign-in-alt"></i> Sign In
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

/* 3D Background - Same as login */
.register-background {
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
.bubble:nth-child(2) { width: 80px; height: 80px; left: 10%; animation-duration: 15s; animation-delay: 2s; }
.bubble:nth-child(3) { width: 70px; height: 70px; left: 15%; animation-duration: 14s; animation-delay: 4s; }
.bubble:nth-child(4) { width: 90px; height: 90px; left: 20%; animation-duration: 16s; animation-delay: 1s; }
.bubble:nth-child(5) { width: 65px; height: 65px; left: 25%; animation-duration: 12s; animation-delay: 3s; }
.bubble:nth-child(6) { width: 75px; height: 75px; left: 30%; animation-duration: 14s; animation-delay: 5s; }
.bubble:nth-child(7) { width: 85px; height: 85px; left: 35%; animation-duration: 17s; }
.bubble:nth-child(8) { width: 70px; height: 70px; left: 40%; animation-duration: 13s; animation-delay: 6s; }
.bubble:nth-child(9) { width: 95px; height: 95px; left: 45%; animation-duration: 15s; animation-delay: 2s; }
.bubble:nth-child(10) { width: 60px; height: 60px; left: 50%; animation-duration: 14s; animation-delay: 4s; }
.bubble:nth-child(11) { width: 80px; height: 80px; left: 55%; animation-duration: 16s; animation-delay: 7s; }
.bubble:nth-child(12) { width: 70px; height: 70px; left: 60%; animation-duration: 13s; animation-delay: 1s; }
.bubble:nth-child(13) { width: 85px; height: 85px; left: 65%; animation-duration: 15s; animation-delay: 3s; }
.bubble:nth-child(14) { width: 75px; height: 75px; left: 70%; animation-duration: 14s; animation-delay: 5s; }
.bubble:nth-child(15) { width: 90px; height: 90px; left: 75%; animation-duration: 16s; animation-delay: 2s; }
.bubble:nth-child(16) { width: 65px; height: 65px; left: 80%; animation-duration: 12s; animation-delay: 4s; }
.bubble:nth-child(17) { width: 85px; height: 85px; left: 85%; animation-duration: 17s; animation-delay: 1s; }
.bubble:nth-child(18) { width: 70px; height: 70px; left: 90%; animation-duration: 13s; animation-delay: 6s; }
.bubble:nth-child(19) { width: 95px; height: 95px; left: 95%; animation-duration: 15s; animation-delay: 3s; }
.bubble:nth-child(20) { width: 60px; height: 60px; left: 8%; animation-duration: 14s; animation-delay: 5s; }
.bubble:nth-child(21) { width: 80px; height: 80px; left: 18%; animation-duration: 16s; animation-delay: 2s; }
.bubble:nth-child(22) { width: 70px; height: 70px; left: 38%; animation-duration: 13s; animation-delay: 7s; }
.bubble:nth-child(23) { width: 85px; height: 85px; left: 58%; animation-duration: 15s; animation-delay: 1s; }
.bubble:nth-child(24) { width: 75px; height: 75px; left: 78%; animation-duration: 14s; animation-delay: 4s; }
.bubble:nth-child(25) { width: 90px; height: 90px; left: 88%; animation-duration: 16s; animation-delay: 6s; }

@keyframes rise {
    0% { bottom: -100px; transform: translateX(0); opacity: 0.5; }
    50% { transform: translateX(100px); opacity: 0.3; }
    100% { bottom: 110%; transform: translateX(-100px); opacity: 0; }
}

/* Register Container */
.register-container {
    position: relative;
    z-index: 1;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
}

.register-card {
    width: 100%;
    max-width: 900px;
    animation: cardEntrance 0.8s ease;
}

@keyframes cardEntrance {
    0% { opacity: 0; transform: translateY(50px); }
    100% { opacity: 1; transform: translateY(0); }
}

.register-card-inner {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 30px;
    padding: 30px 50px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), 0 0 100px rgba(255, 0, 0, 0.2);
    max-height: 90vh;
    overflow-y: auto;
}

/* Custom Scrollbar */
.register-card-inner::-webkit-scrollbar {
    width: 6px;
}

.register-card-inner::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.register-card-inner::-webkit-scrollbar-thumb {
    background: #FF0000;
    border-radius: 10px;
}

.register-card-inner::-webkit-scrollbar-thumb:hover {
    background: #cc0000;
}

/* Logo */
.register-logo {
    text-align: center;
    margin-bottom: 20px;
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
.register-form {
    margin-bottom: 20px;
}

.form-title {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
    text-align: center;
}

.form-subtitle {
    font-size: 13px;
    color: #6c757d;
    text-align: center;
    margin-bottom: 20px;
}

/* Red Button */
.btn-primary {
    background: linear-gradient(135deg, #FF0000, #cc0000) !important;
    border: none !important;
    color: white !important;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #cc0000, #990000) !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255, 0, 0, 0.3);
}

/* Divider */
.divider {
    position: relative;
    text-align: center;
    margin: 20px 0;
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
    font-size: 12px;
    font-weight: 600;
}

/* Login Link */
.login-link {
    text-align: center;
    margin-bottom: 15px;
}

.login-link p {
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 8px;
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

/* Form Validation Styles */
.form-control.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #dc3545;
}

/* Loading State */
.btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Responsive */
@media (max-width: 768px) {
    .register-card-inner {
        padding: 40px 30px;
    }
}

@media (max-width: 576px) {
    .register-container {
        padding: 20px 15px;
    }
    
    .register-card-inner {
        padding: 30px 20px;
        border-radius: 20px;
    }
    
    .logo-image {
        width: 80px;
        height: 80px;
    }
    
    .form-title {
        font-size: 24px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.startsWith('255')) {
                    value = value.substring(0, 12);
                } else if (value.startsWith('0')) {
                    value = '255' + value.substring(1);
                    value = value.substring(0, 12);
                } else {
                    value = '255' + value;
                    value = value.substring(0, 12);
                }
            }
            this.value = value;
        });
    }

    // Password toggle
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    if (togglePassword && password) {
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }

    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const confirmPassword = document.getElementById('password_confirmation');
    if (toggleConfirmPassword && confirmPassword) {
        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPassword.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }

    // Enhanced form validation
    const form = document.querySelector('.register-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const pwd = document.getElementById('password').value;
            const confirmPwd = document.getElementById('password_confirmation').value;
            
            // Clear previous error states
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
            
            let hasErrors = false;
            
            // Name validation
            if (name.length < 2) {
                showFieldError('name', 'Name must be at least 2 characters long');
                hasErrors = true;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showFieldError('email', 'Please enter a valid email address');
                hasErrors = true;
            }
            
            // Phone validation
            if (phone.length < 10) {
                showFieldError('phone', 'Please enter a valid phone number');
                hasErrors = true;
            }
            
            // Password validation
            if (pwd.length < 8) {
                showFieldError('password', 'Password must be at least 8 characters long');
                hasErrors = true;
            }
            
            // Password confirmation
            if (pwd !== confirmPwd) {
                showFieldError('password_confirmation', 'Passwords do not match');
                hasErrors = true;
            }
            
            // Terms validation
            const terms = document.getElementById('terms');
            if (!terms.checked) {
                showFieldError('terms', 'You must agree to the terms and conditions');
                hasErrors = true;
            }
            
            if (hasErrors) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Account...';
            submitBtn.disabled = true;
            
            // Re-enable button after 10 seconds as fallback
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 10000);
        });
    }
    
    // Helper function to show field errors
    function showFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        field.classList.add('is-invalid');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        
        field.parentNode.appendChild(errorDiv);
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
