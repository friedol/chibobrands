@extends('public.layouts.app')

@section('title', 'Change Password - CHIBO BRAND')
@section('description', 'Change your account password')

@section('content')
<!-- Hero Section with Red Gradient -->
<div class="hero-dashboard" style="min-height: 100px; background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); padding: 1rem 0; margin-bottom: 2rem;">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="text-white mb-2" style="font-size: 1.5rem; font-weight: 700;">
                    <i class="fas fa-lock me-2"></i>Change Password
                </h1>
                <p class="text-white-50 mb-0" style="font-size: 0.9rem;">
                    Update your account security settings
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Form -->
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="{{ request()->is('b2b*') ? route('b2b.customer.profile') : route('retail.customer.profile') }}" class="btn btn-modern-outline">
                        <i class="fas fa-arrow-left me-2"></i>Back to Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="modern-card" style="border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border: none;">
                <div class="card-header-modern" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: white; border-radius: 16px 16px 0 0; padding: 1rem 1.5rem;">
                    <h5 class="card-title mb-0" style="font-weight: 600;">
                        <i class="fas fa-key me-2"></i>Update Password
                    </h5>
                </div>
                <div class="card-body" style="padding: 2rem;">
                    <form method="POST" action="{{ request()->is('b2b*') ? route('b2b.customer.change-password') : route('retail.customer.change-password') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password *</label>
                            <input type="password" 
                                   class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" 
                                   name="current_password" 
                                   required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password *</label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Password must be at least 8 characters long</div>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password *</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required>
                        </div>

                        <!-- Password Strength Indicator -->
                        <div class="mb-3">
                            <div class="password-strength">
                                <div class="strength-bar">
                                    <div class="strength-fill" id="strengthFill"></div>
                                </div>
                                <small class="text-muted" id="strengthText">Enter a password</small>
                            </div>
                        </div>

                        <!-- Security Tips -->
                        <div class="alert-modern" style="background: linear-gradient(135deg, #fef7f7 0%, #ffffff 100%); border-left: 4px solid #dc2626; border-radius: 12px; padding: 1rem 1.5rem; margin: 1.5rem 0;">
                            <h6 style="color: #dc2626; font-weight: 600; margin-bottom: 0.75rem;">
                                <i class="fas fa-shield-alt me-2"></i>Password Security Tips:
                            </h6>
                            <ul class="mb-0" style="color: #666; font-size: 0.9rem;">
                                <li>Use at least 8 characters</li>
                                <li>Include uppercase and lowercase letters</li>
                                <li>Add numbers and special characters</li>
                                <li>Avoid common words or personal information</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between gap-3">
                            <a href="{{ request()->is('b2b*') ? route('b2b.customer.profile') : route('retail.customer.profile') }}" class="btn btn-modern-outline flex-fill">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-modern-primary flex-fill">
                                <i class="fas fa-save me-2"></i>Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Modern Design System */
.hero-dashboard {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
    border-radius: 16px;
    margin-bottom: 2rem;
}

.modern-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    border: none;
    overflow: hidden;
}

.card-header-modern {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
    border: none;
    padding: 1rem 1.5rem;
    font-weight: 600;
}

.btn-modern-primary {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border: none;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-modern-primary:hover {
    background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.btn-modern-outline {
    background: transparent;
    border: 2px solid #dc2626;
    color: #dc2626;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-modern-outline:hover {
    background: #dc2626;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.alert-modern {
    background: linear-gradient(135deg, #fef7f7 0%, #ffffff 100%);
    border-left: 4px solid #dc2626;
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin: 1.5rem 0;
}

/* Password Strength Indicator */
.password-strength {
    margin-top: 10px;
}

.strength-bar {
    width: 100%;
    height: 8px;
    background-color: #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 8px;
}

.strength-fill {
    height: 100%;
    width: 0%;
    transition: all 0.3s ease;
    border-radius: 8px;
}

.strength-weak { background: linear-gradient(90deg, #dc3545, #ff6b6b); }
.strength-fair { background: linear-gradient(90deg, #ffc107, #ffd93d); }
.strength-good { background: linear-gradient(90deg, #17a2b8, #20c997); }
.strength-strong { background: linear-gradient(90deg, #28a745, #20c997); }

/* Form Enhancements */
.form-control:focus {
    border-color: #dc2626;
    box-shadow: 0 0 0 0.2rem rgba(220, 38, 38, 0.25);
}

.form-label {
    font-weight: 500;
    color: #333;
    margin-bottom: 0.5rem;
}

/* Global font size reductions */
.hero-dashboard h1 {
    font-size: 1.3rem !important;
}

.hero-dashboard p {
    font-size: 0.85rem !important;
}

.card-title {
    font-size: 0.95rem !important;
}

.form-label {
    font-size: 0.85rem !important;
}

.form-control {
    font-size: 0.8rem !important;
}

.btn-modern-primary,
.btn-modern-outline {
    font-size: 0.85rem !important;
}

.alert-modern h6 {
    font-size: 0.9rem !important;
}

.alert-modern ul {
    font-size: 0.8rem !important;
}

.form-text {
    font-size: 0.75rem !important;
}

#strengthText {
    font-size: 0.75rem !important;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .hero-dashboard h1 {
        font-size: 1.1rem !important;
    }
    
    .hero-dashboard p {
        font-size: 0.75rem !important;
    }
    
    .card-title {
        font-size: 0.85rem !important;
    }
    
    .form-label {
        font-size: 0.75rem !important;
    }
    
    .form-control {
        font-size: 0.7rem !important;
    }
    
    .btn-modern-primary,
    .btn-modern-outline {
        padding: 0.6rem 1.2rem;
        font-size: 0.75rem !important;
    }
    
    .alert-modern h6 {
        font-size: 0.8rem !important;
    }
    
    .alert-modern ul {
        font-size: 0.7rem !important;
    }
    
    .form-text {
        font-size: 0.65rem !important;
    }
    
    #strengthText {
        font-size: 0.65rem !important;
    }
    
    .card-body {
        padding: 1.5rem !important;
    }
    
    .d-flex.gap-3 {
        flex-direction: column;
        gap: 0.75rem !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = calculatePasswordStrength(password);
        
        updateStrengthIndicator(strength);
    });
    
    function calculatePasswordStrength(password) {
        let score = 0;
        
        if (password.length >= 8) score += 1;
        if (password.length >= 12) score += 1;
        if (/[a-z]/.test(password)) score += 1;
        if (/[A-Z]/.test(password)) score += 1;
        if (/[0-9]/.test(password)) score += 1;
        if (/[^A-Za-z0-9]/.test(password)) score += 1;
        
        return score;
    }
    
    function updateStrengthIndicator(strength) {
        const strengthLevels = [
            { level: 'weak', text: 'Weak', width: '25%', class: 'strength-weak' },
            { level: 'fair', text: 'Fair', width: '50%', class: 'strength-fair' },
            { level: 'good', text: 'Good', width: '75%', class: 'strength-good' },
            { level: 'strong', text: 'Strong', width: '100%', class: 'strength-strong' }
        ];
        
        let level;
        if (strength <= 2) level = strengthLevels[0];
        else if (strength <= 3) level = strengthLevels[1];
        else if (strength <= 4) level = strengthLevels[2];
        else level = strengthLevels[3];
        
        strengthFill.style.width = level.width;
        strengthFill.className = 'strength-fill ' + level.class;
        strengthText.textContent = level.text;
    }
    
    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('password_confirmation').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
        
        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long!');
            return false;
        }
    });
});
</script>
@endpush
