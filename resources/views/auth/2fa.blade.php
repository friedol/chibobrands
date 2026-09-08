<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Verification - CHIBO BRAND</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ url('favicon.ico') }}?v={{ time() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}?v={{ time() }}">
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
                    <img src="{{ asset('images/logo.webp') }}" alt="CHIBO BRAND">
                </div>
                <h1 class="showoff-title">Security Verification</h1>
                <p class="showoff-subtitle">We want to make sure it's really you accessing your account.</p>
            </div>

            <!-- Moving Bubbles -->
            <div class="bubbles">
                <div class="bubble"></div>
                <div class="bubble"></div>
                <div class="bubble"></div>
                <div class="bubble"></div>
                <div class="bubble"></div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="login-form-section">
            <div class="login-card">
                <div class="login-card-inner">
                    <div class="login-content">
                        <!-- Logo -->
                        <div class="login-logo">
                            <div class="logo-image">
                                <img src="{{ asset('images/logo.webp') }}" alt="CHIBO BRAND Logo">
                            </div>
                            <p class="brand-tagline">Professional Printing & Branding</p>
                        </div>

                        <!-- 2FA Form -->
                        <form method="POST" action="{{ route('2fa.verify') }}" class="login-form">
                            @csrf

                            <h2 class="form-title">Two-Factor Authentication</h2>
                            <p class="form-subtitle">A verification code has been sent to your email. Please enter it
                                below to continue.</p>

                            <!-- Code Field -->
                            <div class="input-wrapper">
                                <div class="input-icon">
                                    <i class="fas fa-key"></i>
                                </div>
                                <input type="text" class="modern-input @if(session('error')) is-invalid @endif"
                                    id="code" name="code" placeholder="Verification Code" required autofocus>
                                @if(session('error'))
                                    <span class="error-message">{{ session('error') }}</span>
                                @endif
                            </div>
                            <!-- Submit Button -->
                            <button type="submit" class="login-btn">
                                <span class="btn-text">Verify</span>
                                <span class="btn-icon"><i class="fas fa-arrow-right"></i></span>
                                <div class="btn-shine"></div>
                            </button>
                        </form>

                        <!-- Back to Login -->
                        <a href="{{ route('login') }}" class="back-home mt-4">
                            <i class="fas fa-arrow-left"></i> Back to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>