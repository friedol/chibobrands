<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CHIBO BRAND</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo e(url('favicon.ico')); ?>?v=<?php echo e(time()); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/login.css')); ?>?v=<?php echo e(time()); ?>">
</head>
<body>

<!-- 3D Background -->
<div class="login-background">
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
        <div class="shape shape-5"></div>
    </div>
    <div class="gradient-overlay"></div>
</div>

<!-- Login Container -->
<div class="login-container">
    <!-- Showoff Section (Large Screens Only) -->
    <div class="showoff-section">
        <div class="showoff-content">
            <div class="showoff-logo">
                <img src="<?php echo e(asset('images/logo.webp')); ?>" alt="CHIBO BRAND">
            </div>
            <h1 class="showoff-title">Welcome to CHIBO BRAND</h1>
            <p class="showoff-subtitle">Your trusted partner in professional printing and branding solutions</p>
            
            <div class="showoff-features">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-print"></i>
                    </div>
                    <h3>Premium Printing</h3>
                    <p>High-quality printing services for all your needs</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3>Custom Branding</h3>
                    <p>Unique designs that make your brand stand out</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h3>Fast Delivery</h3>
                    <p>Quick turnaround times for urgent projects</p>
                </div>
            </div>
        </div>
        
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
        </div>
    </div>
    
    <!-- Login Form Section -->
    <div class="login-form-section">
        <div class="login-card">
            <div class="login-card-inner">
                <div class="login-content">
                <!-- Logo -->
                <div class="login-logo">
                    <div class="logo-image">
                        <img src="<?php echo e(asset('images/logo.webp')); ?>" alt="CHIBO BRAND Logo">
                    </div>
                    <p class="brand-tagline">Professional Printing & Branding</p>
                </div>

                <!-- Login Form -->
                <form method="POST" action="<?php echo e(route('login')); ?>" class="login-form">
                    <?php echo csrf_field(); ?>
                    
                    <h2 class="form-title">Welcome Back</h2>
                    <p class="form-subtitle">Sign in to continue to your account</p>
                    
                    <!-- Email Field -->
                    <div class="input-wrapper">
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <input type="email" 
                               class="modern-input <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               id="email" 
                               name="email" 
                               value="<?php echo e(old('email')); ?>" 
                               placeholder="Email Address"
                               required 
                               autofocus>
                        <label class="floating-label">Email Address</label>
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="error-message"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Password Field -->
                    <div class="input-wrapper">
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <input type="password" 
                               class="modern-input <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                               id="password" 
                               name="password" 
                               placeholder="Password"
                               required>
                        <label class="floating-label">Password</label>
                        <button class="toggle-password" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="error-message"><?php echo e($message); ?></span>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="remember-forgot">
                        <label class="custom-checkbox">
                            <input type="checkbox" name="remember" id="remember">
                            <span class="checkmark"></span>
                            <span class="label-text">Remember me</span>
                        </label>
                        <a href="<?php echo e(route('password.request')); ?>" class="forgot-link">Forgot Password?</a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="login-btn">
                        <span class="btn-text">Sign In</span>
                        <span class="btn-icon"><i class="fas fa-arrow-right"></i></span>
                        <div class="btn-shine"></div>
                    </button>
                    
                    <!-- Success Message -->
                    <?php if(session('success')): ?>
                        <div class="success-alert">
                            <i class="fas fa-check-circle"></i>
                            <span><?php echo e(session('success')); ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Unverified Customer Alert -->
                    <?php if(session('unverified_customer')): ?>
                        <div class="warning-alert">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <strong>Account Pending Verification</strong><br>
                                Hello <strong><?php echo e(session('unverified_customer')['name']); ?></strong>, your account is currently under review. 
                                You will receive an email notification once your account is approved.
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Error Messages -->
                    <?php if($errors->any()): ?>
                        <div class="error-alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?php echo e($errors->first()); ?></span>
                        </div>
                    <?php endif; ?>
                </form>

                <!-- Divider -->
                <div class="divider">
                    <span>OR</span>
                </div>
                
                <!-- Register Link -->
                <div class="register-link">
                    <p>Don't have an account?</p>
                    <a href="<?php echo e(route('customer.register')); ?>" class="create-account-btn">
                        <i class="fas fa-user-plus"></i> Create Account
                    </a>
                </div>
                
                <!-- Back to Home -->
                <a href="<?php echo e(url('/')); ?>" class="back-home">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
    </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    
    if (togglePassword && password) {
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }

    // Add loading state to form
    const form = document.querySelector('.login-form');
    const submitBtn = form.querySelector('.login-btn');
    
    form.addEventListener('submit', function() {
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing In...';
        submitBtn.disabled = true;
    });

    // Show unverified modal if customer tried to login but not verified
    <?php if(session('unverified_customer')): ?>
        // Add a small delay to ensure the page is fully loaded
        setTimeout(() => {
            showUnverifiedModal(
                '<?php echo e(session('unverified_customer')['name']); ?>',
                '<?php echo e(session('unverified_customer')['email']); ?>',
                '<?php echo e(session('unverified_customer')['registered_at']); ?>'
            );
        }, 500);
    <?php endif; ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php /**PATH /Users/gotlaptopparts.com/Desktop/LaravelProject/chibo_sales/resources/views/auth/login.blade.php ENDPATH**/ ?>