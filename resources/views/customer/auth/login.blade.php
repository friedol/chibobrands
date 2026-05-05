@extends('public.layouts.app')

@section('title', 'Login - CHIBO BRAND')
@section('description', 'Sign in to your CHIBO BRAND account to access wholesale pricing and manage your orders.')

@section('content')
<!-- Login Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="h3 mb-3">Sign In</h2>
                            <p class="text-muted">Access your CHIBO BRAND account</p>
                        </div>

                        <!-- Success Message -->
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Unverified Customer Alert -->
                        @if(session('unverified_customer'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Account Pending Verification</strong><br>
                                Hello <strong>{{ session('unverified_customer')['name'] }}</strong>, your account is currently under review. 
                                You will receive an email notification once your account is approved.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('customer.login') }}">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       required 
                                       autofocus>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
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
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="remember" 
                                           name="remember" 
                                           {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">
                                        Remember me
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                </button>
                            </div>
                        </form>

                        <!-- Links -->
                        <div class="text-center mt-4">
                            <p class="mb-2">Don't have an account? 
                                <a href="{{ route('customer.register') }}" class="text-primary">Create one here</a>
                            </p>
                            <p class="mb-0">
                                <a href="#" class="text-primary">Forgot your password?</a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Account Status Info -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-info-circle text-primary me-2"></i>Account Status
                        </h6>
                        <p class="card-text small text-muted">
                            New accounts are created as "unverified" and will be reviewed by our team. 
                            You'll receive an email notification once your account is approved and you can access wholesale pricing.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus email field
    const emailInput = document.getElementById('email');
    if (emailInput && !emailInput.value) {
        emailInput.focus();
    }
    
    // Show unverified modal if customer tried to login but not verified
    @if(session('unverified_customer'))
        // Add a small delay to ensure the page is fully loaded
        setTimeout(() => {
            showUnverifiedModal(
                '{{ session('unverified_customer')['name'] }}',
                '{{ session('unverified_customer')['email'] }}',
                '{{ session('unverified_customer')['registered_at'] }}'
            );
        }, 500);
    @endif
});

// Unverified Customer Modal
function showUnverifiedModal(name, email, registeredAt) {
    const modalHTML = `
        <div class="modern-modal-overlay" id="unverifiedModal">
            <div class="modern-modal-container">
                <div class="modern-modal-content unverified-modal">
                    <button class="modal-close-btn" onclick="closeUnverifiedModal()" title="Close">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="unverified-icon">
                        <div class="clock-icon">
                            <i class="fas fa-clock"></i>
                            <div class="clock-pulse"></div>
                        </div>
                    </div>
                    <h3 class="modern-modal-title unverified-title">Account Pending Verification</h3>
                    <p class="modern-modal-greeting">Hello <strong>${name}</strong>,</p>
                    <p class="modern-modal-message">
                        Your account is currently under review by our admin team. 
                        You will not be able to login until your account has been verified.
                    </p>
                    <div class="unverified-details">
                        <div class="detail-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <small class="text-muted">Email</small>
                                <div>${email}</div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar"></i>
                            <div>
                                <small class="text-muted">Registered</small>
                                <div>${registeredAt}</div>
                            </div>
                        </div>
                    </div>
                    <div class="unverified-info">
                        <h6><i class="fas fa-info-circle me-2"></i>What happens next?</h6>
                        <ul>
                            <li>Our team is reviewing your account details</li>
                            <li>You will receive an email once verified</li>
                            <li>Verification usually takes 1-2 business days</li>
                            <li>After verification, you can login immediately</li>
                        </ul>
                    </div>
                    <div class="unverified-contact">
                        <small class="text-muted">Need help?</small>
                        <div class="mt-2">
                            <a href="mailto:info@chibobrand.com" class="contact-link">
                                <i class="fas fa-envelope me-2"></i>info@chibobrand.com
                            </a>
                            <span class="mx-2">|</span>
                            <a href="tel:+255655392319" class="contact-link">
                                <i class="fas fa-phone me-2"></i>+255 655 392 319
                            </a>
                        </div>
                    </div>
                    <button class="modern-btn modern-btn-unverified-close" onclick="closeUnverifiedModal()">
                        <i class="fas fa-check me-2"></i>I Understand
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    setTimeout(() => {
        document.getElementById('unverifiedModal').classList.add('show');
    }, 100);
}

function closeUnverifiedModal() {
    const modal = document.getElementById('unverifiedModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => modal.remove(), 300);
    }
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUnverifiedModal();
    }
});

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('unverifiedModal');
    if (modal && e.target === modal) {
        closeUnverifiedModal();
    }
});
</script>

<style>
/* Unverified Modal Styles - Fully Responsive */
.unverified-modal {
    max-width: 520px;
    max-height: 90vh;
    overflow-y: auto;
}

/* Custom Scrollbar */
.unverified-modal::-webkit-scrollbar {
    width: 6px;
}

.unverified-modal::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.unverified-modal::-webkit-scrollbar-thumb {
    background: #ffc107;
    border-radius: 10px;
}

.unverified-modal::-webkit-scrollbar-thumb:hover {
    background: #ff9800;
}

.unverified-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    position: relative;
}

.clock-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ffc107, #ff9800);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    color: white;
    position: relative;
    animation: clockBounce 2s infinite;
    box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4);
}

.clock-pulse {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 3px solid #ffc107;
    animation: pulse 2s infinite;
}

@keyframes clockBounce {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

@keyframes pulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    100% {
        transform: scale(1.5);
        opacity: 0;
    }
}

.unverified-title {
    color: #ff9800;
    font-size: 22px;
    margin-bottom: 10px;
    line-height: 1.3;
}

.modern-modal-greeting {
    font-size: 15px;
    color: #495057;
    margin-bottom: 8px;
}

.unverified-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin: 16px 0;
    padding: 14px;
    background: #f8f9fa;
    border-radius: 10px;
}

.detail-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.detail-item i {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #ffc107, #ff9800);
    color: white;
    border-radius: 8px;
    font-size: 14px;
    flex-shrink: 0;
}

.detail-item small {
    display: block;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-item div div {
    font-weight: 600;
    color: #2c3e50;
    font-size: 13px;
    word-break: break-word;
}

.unverified-info {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 12px;
    border-radius: 8px;
    margin: 14px 0;
    text-align: left;
}

.unverified-info h6 {
    color: #856404;
    margin-bottom: 8px;
    font-size: 14px;
}

.unverified-info ul {
    margin: 0;
    padding-left: 18px;
    color: #856404;
    font-size: 13px;
    line-height: 1.6;
}

.unverified-info li {
    margin-bottom: 4px;
}

.unverified-contact {
    text-align: center;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 14px;
}

.unverified-contact small {
    font-size: 11px;
}

.unverified-contact > div {
    margin-top: 8px;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    gap: 8px;
}

.contact-link {
    color: #FF0000;
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
}

.contact-link:hover {
    color: #cc0000;
    text-decoration: underline;
}

.modern-btn-unverified-close {
    background: linear-gradient(135deg, #ffc107, #ff9800);
    color: white;
    width: 100%;
    padding: 14px;
    font-size: 16px;
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.modern-btn-unverified-close:hover {
    background: linear-gradient(135deg, #ff9800, #f57c00);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4);
}

/* Modal Close Button */
.modal-close-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(0, 0, 0, 0.1);
    border: none;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 10;
}

.modal-close-btn:hover {
    background: rgba(0, 0, 0, 0.2);
    color: #dc3545;
    transform: scale(1.1);
}

/* Modal Base Styles */
.modern-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100000;
    opacity: 0;
    transition: opacity 0.3s ease;
    padding: 10px;
}

.modern-modal-overlay.show {
    opacity: 1;
}

.modern-modal-overlay.show .modern-modal-container {
    transform: scale(1) translateY(0);
    opacity: 1;
}

.modern-modal-container {
    transform: scale(0.7) translateY(-50px);
    opacity: 0;
    transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    width: 100%;
    max-width: 520px;
}

.modern-modal-content {
    background: white;
    border-radius: 20px;
    padding: 30px 25px;
    width: 100%;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
    text-align: center;
    position: relative;
}

.modern-modal-title {
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 12px;
    font-family: 'Nunito Sans', sans-serif;
}

.modern-modal-message {
    font-size: 14px;
    color: #6c757d;
    line-height: 1.5;
    margin-bottom: 14px;
}

/* Tablet Responsive */
@media (max-width: 768px) {
    .unverified-icon {
        width: 70px;
        height: 70px;
        margin-bottom: 16px;
    }
    
    .clock-icon {
        width: 70px;
        height: 70px;
        font-size: 32px;
    }
    
    .unverified-title {
        font-size: 20px;
    }
    
    .modern-modal-content {
        padding: 25px 20px;
    }
    
    .unverified-details {
        gap: 10px;
        padding: 12px;
    }
    
    .detail-item i {
        width: 30px;
        height: 30px;
        font-size: 13px;
    }
}

/* Mobile Responsive */
@media (max-width: 576px) {
    .modern-modal-overlay {
        padding: 5px;
    }
    
    .unverified-modal {
        max-height: 95vh;
    }
    
    .modern-modal-content {
        padding: 20px 16px;
        border-radius: 16px;
    }
    
    .unverified-icon {
        width: 60px;
        height: 60px;
        margin-bottom: 14px;
    }
    
    .clock-icon {
        width: 60px;
        height: 60px;
        font-size: 28px;
    }
    
    .unverified-title {
        font-size: 18px;
        margin-bottom: 8px;
    }
    
    .modern-modal-greeting {
        font-size: 14px;
    }
    
    .modern-modal-message {
        font-size: 13px;
        margin-bottom: 12px;
    }
    
    .unverified-details {
        grid-template-columns: 1fr;
        gap: 8px;
        padding: 10px;
        margin: 12px 0;
    }
    
    .detail-item {
        gap: 8px;
    }
    
    .detail-item i {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }
    
    .detail-item small {
        font-size: 9px;
    }
    
    .detail-item div div {
        font-size: 12px;
    }
    
    .unverified-info {
        padding: 10px;
        margin: 12px 0;
    }
    
    .unverified-info h6 {
        font-size: 13px;
        margin-bottom: 6px;
    }
    
    .unverified-info ul {
        font-size: 12px;
        padding-left: 16px;
    }
    
    .unverified-info li {
        margin-bottom: 3px;
    }
    
    .unverified-contact {
        padding: 10px;
        margin-bottom: 12px;
    }
    
    .unverified-contact small {
        font-size: 10px;
    }
    
    .unverified-contact > div {
        flex-direction: column;
        gap: 6px;
    }
    
    .unverified-contact .mx-2 {
        display: none;
    }
    
    .contact-link {
        font-size: 11px;
    }
    
    .modern-btn-unverified-close {
        padding: 12px;
        font-size: 15px;
    }
}

/* Extra Small Mobile */
@media (max-width: 375px) {
    .unverified-title {
        font-size: 16px;
    }
    
    .modern-modal-message {
        font-size: 12px;
    }
    
    .unverified-info ul {
        font-size: 11px;
    }
    
    .contact-link {
        font-size: 10px;
    }
}
</style>
@endpush
