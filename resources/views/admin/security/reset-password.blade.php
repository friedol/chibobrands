@extends('layouts.admin')

@section('title', 'Reset Password - CHIBO BRAND')

@push('styles')
<style>
    .user-type-selector {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .user-type-card {
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
        position: relative;
    }

    .user-type-card:hover {
        border-color: #0d6efd;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
        transform: translateY(-2px);
    }

    .user-type-card.active {
        border-color: #0d6efd;
        background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
        box-shadow: 0 4px 16px rgba(13, 110, 253, 0.2);
    }

    .user-type-card input[type="radio"] {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }

    .user-type-card .card-icon {
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
        color: #6c757d;
        transition: all 0.3s ease;
    }

    .user-type-card.active .card-icon {
        color: #0d6efd;
        transform: scale(1.1);
    }

    .user-type-card .card-title {
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 0;
        color: #212529;
    }

    .user-type-card .card-description {
        display: none;
    }

    .form-section {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #212529;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .section-title i {
        color: #0d6efd;
    }

    .search-results {
        position: absolute;
        z-index: 1000;
        width: 100%;
        max-height: 300px;
        overflow-y: auto;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        margin-top: 0.5rem;
    }

    .search-result-item {
        padding: 1rem;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .search-result-item:hover {
        background: #f8f9fa;
    }

    .search-result-item:last-child {
        border-bottom: none;
    }

    .password-strength {
        margin-top: 0.5rem;
        font-size: 0.875rem;
    }

    .password-strength-indicator {
        height: 4px;
        border-radius: 2px;
        background: #e9ecef;
        margin-top: 0.5rem;
        overflow: hidden;
    }

    .password-strength-bar {
        height: 100%;
        width: 0%;
        transition: all 0.3s ease;
        border-radius: 2px;
    }

    .password-strength-bar.weak {
        width: 33%;
        background: #dc3545;
    }

    .password-strength-bar.medium {
        width: 66%;
        background: #ffc107;
    }

    .password-strength-bar.strong {
        width: 100%;
        background: #28a745;
    }

    .info-alert {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border-left: 4px solid #ffc107;
        border-radius: 8px;
        padding: 1rem 1.5rem;
        margin-bottom: 2rem;
    }

    .selected-user-display {
        background: #f8f9fa;
        border: 2px solid #0d6efd;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
        display: none;
    }

    .selected-user-display.show {
        display: block;
    }

    .selected-user-info {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .selected-user-details {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .selected-user-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 1.2rem;
    }

    @media (max-width: 991px) {
        .user-type-selector {
            grid-template-columns: 1fr;
        }
        
        .form-section {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.audit-logs.index') }}" class="text-decoration-none">
                    <i class="fas fa-shield-alt me-1"></i>Security
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <i class="fas fa-key me-1"></i>Reset Password
            </li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="fas fa-key me-2 text-primary"></i>Reset User Password
            </h1>
            <p class="text-muted mb-0">Reset password for users or customers who forgot their password</p>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-2 fs-5"></i>
            <div class="flex-grow-1">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Info Alert -->
    <div class="info-alert">
        <div class="d-flex align-items-start">
            <i class="fas fa-info-circle me-2 mt-1"></i>
            <div>
                <strong>Important:</strong> This action will immediately reset the user's password. 
                They will need to use the new password to log in. All password reset actions are logged in the audit logs for security purposes.
            </div>
        </div>
    </div>

    <!-- Reset Password Form -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0">
                <i class="fas fa-lock me-2 text-primary"></i>Password Reset Form
            </h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.security.reset-password.store') }}" id="resetPasswordForm">
                @csrf

                <!-- User Type Selection -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-users"></i>
                        <span>Select User Type</span>
                    </div>
                    <div class="user-type-selector">
                        <label class="user-type-card" for="user_type_user">
                            <input type="radio" name="user_type" id="user_type_user" value="user" checked>
                            <div class="card-icon">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="card-title">Admin/Staff User</div>
                        </label>

                        <label class="user-type-card" for="user_type_customer">
                            <input type="radio" name="user_type" id="user_type_customer" value="customer">
                            <div class="card-icon">
                                <i class="fas fa-user-tag"></i>
                            </div>
                            <div class="card-title">Customer</div>
                        </label>
                    </div>
                </div>

                <!-- User Selection and Password Sections Side by Side -->
                <div class="row g-4">
                    <!-- Left Column: User Selection -->
                    <div class="col-lg-6">
                        <!-- User Selection Section -->
                        <div class="form-section" id="userSelectionSection">
                            <div class="section-title">
                                <i class="fas fa-user-check"></i>
                                <span>Select User</span>
                            </div>
                            <div class="mb-3">
                                <label for="user_id" class="form-label fw-bold">Choose User <span class="text-danger">*</span></label>
                                <select name="user_id" id="user_id" class="form-select form-select-lg @error('user_id') is-invalid @enderror" required>
                                    <option value="">-- Select a user from the list --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" data-type="user">
                                            {{ $user->name }} ({{ $user->email }}) - {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>Select the admin or staff user whose password you want to reset
                                </div>
                                @error('user_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Selected User Display -->
                            <div class="selected-user-display" id="selectedUserDisplay">
                                <div class="selected-user-info">
                                    <div class="selected-user-details">
                                        <div class="selected-user-avatar" id="userAvatar">U</div>
                                        <div>
                                            <div class="fw-bold" id="userName">-</div>
                                            <small class="text-muted" id="userEmail">-</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Selection Section -->
                        <div class="form-section d-none" id="customerSelectionSection">
                            <div class="section-title">
                                <i class="fas fa-user-tag"></i>
                                <span>Search Customer</span>
                            </div>
                            <div class="mb-3 position-relative">
                                <label for="customer_search" class="form-label fw-bold">Search Customer <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control form-control-lg @error('user_id') is-invalid @enderror" 
                                       id="customer_search" 
                                       placeholder="Type customer name, email, or phone number..."
                                       autocomplete="off">
                                <input type="hidden" name="user_id" id="customer_id" value="">
                                <div id="customer_results" class="search-results" style="display: none;"></div>
                                <div class="form-text">
                                    <i class="fas fa-search me-1"></i>Start typing to search for customers (minimum 2 characters)
                                </div>
                                @error('user_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Selected Customer Display -->
                            <div class="selected-user-display" id="selectedCustomerDisplay">
                                <div class="selected-user-info">
                                    <div class="selected-user-details">
                                        <div class="selected-user-avatar" id="customerAvatar">C</div>
                                        <div>
                                            <div class="fw-bold" id="customerName">-</div>
                                            <small class="text-muted" id="customerEmail">-</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Password Section -->
                    <div class="col-lg-6">
                        <div class="form-section">
                            <div class="section-title">
                                <i class="fas fa-lock"></i>
                                <span>New Password</span>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold">
                                    New Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" 
                                           class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           required 
                                           minlength="8"
                                           placeholder="Enter new password">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength">
                                    <div class="password-strength-indicator">
                                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                                    </div>
                                    <small id="passwordStrengthText" class="text-muted">Password strength: -</small>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-shield-alt me-1"></i>Minimum 8 characters required. Use a strong password with letters, numbers, and symbols.
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label fw-bold">
                                    Confirm Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                    <input type="password" 
                                           class="form-control form-control-lg" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           required 
                                           minlength="8"
                                           placeholder="Confirm new password">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirmation">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>Re-enter the password to confirm
                                </div>
                                <div id="passwordMatch" class="mt-2" style="display: none;">
                                    <small class="text-success">
                                        <i class="fas fa-check-circle me-1"></i>Passwords match
                                    </small>
                                </div>
                                <div id="passwordMismatch" class="mt-2" style="display: none;">
                                    <small class="text-danger">
                                        <i class="fas fa-times-circle me-1"></i>Passwords do not match
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                        <i class="fas fa-key me-2"></i>Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const userTypeUser = document.getElementById('user_type_user');
    const userTypeCustomer = document.getElementById('user_type_customer');
    const userSelectionSection = document.getElementById('userSelectionSection');
    const customerSelectionSection = document.getElementById('customerSelectionSection');
    const customerSearch = document.getElementById('customer_search');
    const customerId = document.getElementById('customer_id');
    const customerResults = document.getElementById('customer_results');
    const userIdSelect = document.getElementById('user_id');
    const passwordInput = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');
    const togglePassword = document.getElementById('togglePassword');
    const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');
    const passwordStrengthBar = document.getElementById('passwordStrengthBar');
    const passwordStrengthText = document.getElementById('passwordStrengthText');
    const passwordMatch = document.getElementById('passwordMatch');
    const passwordMismatch = document.getElementById('passwordMismatch');
    const selectedUserDisplay = document.getElementById('selectedUserDisplay');
    const selectedCustomerDisplay = document.getElementById('selectedCustomerDisplay');
    let searchTimeout;

    // Update active state for user type cards
    function updateUserTypeCards() {
        const cards = document.querySelectorAll('.user-type-card');
        cards.forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            if (radio.checked) {
                card.classList.add('active');
            } else {
                card.classList.remove('active');
            }
        });
    }

    // Handle user type change
    userTypeUser.addEventListener('change', function() {
        if (this.checked) {
            userSelectionSection.classList.remove('d-none');
            customerSelectionSection.classList.add('d-none');
            customerId.value = '';
            customerSearch.value = '';
            customerResults.style.display = 'none';
            selectedCustomerDisplay.classList.remove('show');
            userIdSelect.required = true;
            customerId.removeAttribute('required');
            updateUserTypeCards();
        }
    });

    userTypeCustomer.addEventListener('change', function() {
        if (this.checked) {
            userSelectionSection.classList.add('d-none');
            customerSelectionSection.classList.remove('d-none');
            userIdSelect.value = '';
            selectedUserDisplay.classList.remove('show');
            userIdSelect.removeAttribute('required');
            customerId.required = true;
            updateUserTypeCards();
        }
    });

    // Update active cards on load
    updateUserTypeCards();

    // Show selected user info
    userIdSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const userName = selectedOption.text.split(' (')[0];
            const userEmail = selectedOption.text.match(/\(([^)]+)\)/)[1];
            document.getElementById('userName').textContent = userName;
            document.getElementById('userEmail').textContent = userEmail;
            document.getElementById('userAvatar').textContent = userName.charAt(0).toUpperCase();
            selectedUserDisplay.classList.add('show');
        } else {
            selectedUserDisplay.classList.remove('show');
        }
    });

    // Customer search functionality
    customerSearch.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            customerResults.style.display = 'none';
            selectedCustomerDisplay.classList.remove('show');
            return;
        }

        searchTimeout = setTimeout(function() {
            fetch(`{{ route('admin.security.search-users') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    customerResults.innerHTML = '';
                    
                    if (data.customers && data.customers.length > 0) {
                        data.customers.forEach(customer => {
                            const item = document.createElement('div');
                            item.className = 'search-result-item';
                            item.innerHTML = `
                                <div class="d-flex align-items-center">
                                    <div class="selected-user-avatar me-3" style="width: 40px; height: 40px; font-size: 1rem;">
                                        ${customer.name.charAt(0).toUpperCase()}
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">${customer.name}</div>
                                        <small class="text-muted">${customer.email || 'No email'}</small>
                                        ${customer.phone ? `<br><small class="text-muted"><i class="fas fa-phone me-1"></i>${customer.phone}</small>` : ''}
                                    </div>
                                </div>
                            `;
                            item.addEventListener('click', function() {
                                customerId.value = customer.id;
                                customerSearch.value = `${customer.name} (${customer.email || customer.phone})`;
                                customerResults.style.display = 'none';
                                document.getElementById('customerName').textContent = customer.name;
                                document.getElementById('customerEmail').textContent = customer.email || customer.phone || 'No contact info';
                                document.getElementById('customerAvatar').textContent = customer.name.charAt(0).toUpperCase();
                                selectedCustomerDisplay.classList.add('show');
                            });
                            customerResults.appendChild(item);
                        });
                        customerResults.style.display = 'block';
                    } else {
                        const noResult = document.createElement('div');
                        noResult.className = 'search-result-item text-muted text-center';
                        noResult.textContent = 'No customers found';
                        customerResults.appendChild(noResult);
                        customerResults.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error searching customers:', error);
                });
        }, 300);
    });

    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (!customerSearch.contains(e.target) && !customerResults.contains(e.target)) {
            customerResults.style.display = 'none';
        }
    });

    // Password toggle visibility
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    togglePasswordConfirmation.addEventListener('click', function() {
        const type = passwordConfirmation.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordConfirmation.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    // Password strength checker
    function checkPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;

        if (strength <= 2) {
            return { level: 'weak', text: 'Weak', class: 'weak' };
        } else if (strength <= 4) {
            return { level: 'medium', text: 'Medium', class: 'medium' };
        } else {
            return { level: 'strong', text: 'Strong', class: 'strong' };
        }
    }

    passwordInput.addEventListener('input', function() {
        const password = this.value;
        if (password.length > 0) {
            const strength = checkPasswordStrength(password);
            passwordStrengthBar.className = `password-strength-bar ${strength.class}`;
            passwordStrengthText.textContent = `Password strength: ${strength.text}`;
            passwordStrengthText.className = strength.level === 'weak' ? 'text-danger' : 
                                            strength.level === 'medium' ? 'text-warning' : 'text-success';
            checkPasswordMatch();
        } else {
            passwordStrengthBar.className = 'password-strength-bar';
            passwordStrengthText.textContent = 'Password strength: -';
            passwordStrengthText.className = 'text-muted';
        }
    });

    // Password match checker
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirmation = passwordConfirmation.value;

        if (confirmation.length === 0) {
            passwordMatch.style.display = 'none';
            passwordMismatch.style.display = 'none';
            return;
        }

        if (password === confirmation) {
            passwordMatch.style.display = 'block';
            passwordMismatch.style.display = 'none';
        } else {
            passwordMatch.style.display = 'none';
            passwordMismatch.style.display = 'block';
        }
    }

    passwordConfirmation.addEventListener('input', checkPasswordMatch);

    // Form validation and submission
    document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
        const userType = document.querySelector('input[name="user_type"]:checked').value;
        
        if (userType === 'user' && !userIdSelect.value) {
            e.preventDefault();
            alert('Please select a user');
            userIdSelect.focus();
            return false;
        }
        
        if (userType === 'customer' && !customerId.value) {
            e.preventDefault();
            alert('Please select a customer');
            customerSearch.focus();
            return false;
        }

        const password = passwordInput.value;
        const confirmation = passwordConfirmation.value;

        if (password !== confirmation) {
            e.preventDefault();
            alert('Passwords do not match. Please check and try again.');
            passwordConfirmation.focus();
            return false;
        }

        if (!confirm('Are you sure you want to reset this password? The user will need to use the new password to log in immediately.')) {
            e.preventDefault();
            return false;
        }

        // Disable submit button to prevent double submission
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Resetting...';
    });
});
</script>
@endpush
@endsection






