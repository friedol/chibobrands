@extends('public.layouts.app')

@section('title', 'Profile - CHIBO BRAND')
@section('description', 'Manage your account profile and settings')

@section('content')
<!-- Modern Profile with Red Decoration -->
<div class="container py-4">
    <!-- Hero Section with Red Gradient -->
   

    <div class="row">
        <!-- Profile Form -->
        <div class="col-lg-8">
            <div class="modern-card" style="background: white; border: 2px solid #f3f4f6; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden;">
                <div class="card-header-modern" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); padding: 0.75rem; border-bottom: none;">
                    <h5 class="card-title mb-0 text-white fw-bold">Personal Information</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ request()->is('b2b*') ? route('b2b.customer.profile.update') : route('retail.customer.profile.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <!-- Personal Information Section -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3 fw-bold">Personal Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label fw-semibold">Full Name *</label>
                                        <input type="text" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               id="name" 
                                               name="name" 
                                               value="{{ old('name', auth()->user()->name) }}" 
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label fw-semibold">Email Address *</label>
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email', auth()->user()->email) }}" 
                                               required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information Section -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3 fw-bold">Contact Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label fw-semibold">Phone Number *</label>
                                        <input type="tel" 
                                               class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" 
                                               name="phone" 
                                               value="{{ old('phone', auth()->user()->phone) }}" 
                                               required>
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="company_name" class="form-label fw-semibold">Company Name</label>
                                        <input type="text" 
                                               class="form-control @error('company_name') is-invalid @enderror" 
                                               id="company_name" 
                                               name="company_name" 
                                               value="{{ old('company_name', auth()->user()->company_name) }}">
                                        @error('company_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Business Information Section -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3 fw-bold">Business Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="business_type" class="form-label fw-semibold">Business Type</label>
                                        <select class="form-select @error('business_type') is-invalid @enderror" 
                                                id="business_type" 
                                                name="business_type">
                                            <option value="">Select Business Type</option>
                                            <option value="retail" {{ old('business_type', auth()->user()->business_type) == 'retail' ? 'selected' : '' }}>Retail Store</option>
                                            <option value="wholesale" {{ old('business_type', auth()->user()->business_type) == 'wholesale' ? 'selected' : '' }}>Wholesale Distributor</option>
                                            <option value="printing" {{ old('business_type', auth()->user()->business_type) == 'printing' ? 'selected' : '' }}>Printing Company</option>
                                            <option value="advertising" {{ old('business_type', auth()->user()->business_type) == 'advertising' ? 'selected' : '' }}>Advertising Agency</option>
                                            <option value="corporate" {{ old('business_type', auth()->user()->business_type) == 'corporate' ? 'selected' : '' }}>Corporate</option>
                                            <option value="individual" {{ old('business_type', auth()->user()->business_type) == 'individual' ? 'selected' : '' }}>Individual</option>
                                            <option value="other" {{ old('business_type', auth()->user()->business_type) == 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        @error('business_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="is_wholesale" 
                                                   name="is_wholesale" 
                                                   value="1" 
                                                   {{ old('is_wholesale', auth()->user()->is_wholesale) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="is_wholesale">
                                                I'm interested in wholesale pricing
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address Section -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3 fw-bold">Address Information</h6>
                            <div class="mb-3">
                                <label for="address" class="form-label fw-semibold">Address</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" 
                                          name="address" 
                                          rows="3" 
                                          placeholder="Enter your complete address">{{ old('address', auth()->user()->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="border-top pt-4 mt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ request()->is('b2b*') ? route('b2b.customer.dashboard') : route('retail.customer.dashboard') }}" class="btn btn-modern-outline">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                </a>
                                <button type="submit" class="btn btn-modern-primary">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Account Information -->
        <div class="col-lg-4">
            <!-- Account Status -->
            <div class="modern-card mb-4" style="background: white; border: 2px solid #f3f4f6; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden;">
                <div class="card-header-modern" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); padding: 0.75rem; border-bottom: none;">
                    <h5 class="card-title mb-0 text-white fw-bold">Account Status</h5>
                </div>
                <div class="card-body p-4">
                    <div class="text-center">
                        @if(auth()->user()->verified)
                            <div class="mb-3">
                                <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="text-success mb-2">Account Verified</h5>
                            <p class="text-muted mb-0">Your account is fully verified and active.</p>
                        @else
                            <div class="mb-3">
                                <i class="fas fa-clock text-warning" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="text-warning mb-2">Pending Verification</h5>
                            <p class="text-muted mb-0">Your account is under review. You'll receive an email once verified.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Account Details -->
            <div class="modern-card mb-4" style="background: white; border: 2px solid #f3f4f6; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden;">
                <div class="card-header-modern" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1b 100%); padding: 0.75rem; border-bottom: none;">
                    <h5 class="card-title mb-0 text-white fw-bold">Account Details</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="info-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>Member Since:</strong>
                                    <span class="text-muted">{{ auth()->user()->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>Last Login:</strong>
                                    <span class="text-muted">{{ auth()->user()->last_login_at ? auth()->user()->last_login_at->format('M d, Y H:i') : 'Never' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>Total Orders:</strong>
                                    <span class="text-muted">Order tracking coming soon</span>
                                </div>
                            </div>
                        </div>
                        @if(auth()->user()->is_wholesale)
                            <div class="col-12">
                                <div class="info-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong>Account Type:</strong>
                                        <span class="badge bg-primary">Wholesale Customer</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="modern-card" style="background: white; border: 2px solid #f3f4f6; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden;">
                <div class="card-header-modern" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1b 100%); padding: 0.75rem; border-bottom: none;">
                    <h5 class="card-title mb-0 text-white fw-bold">Quick Actions</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-grid gap-3">
                        <a href="{{ request()->is('b2b*') ? route('b2b.customer.change-password') : route('retail.customer.change-password') }}" class="btn btn-modern-outline">
                            <i class="fas fa-key me-2"></i>Change Password
                        </a>
                        <a href="{{ request()->is('b2b*') ? route('b2b.customer.orders.index') : route('retail.customer.orders.index') }}" class="btn btn-modern-outline">
                            <i class="fas fa-list me-2"></i>View Orders
                        </a>
                        <a href="{{ route('products.index') }}" class="btn btn-modern-outline">
                            <i class="fas fa-shopping-bag me-2"></i>Browse Products
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.hero-dashboard {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(220, 38, 38, 0.2);
    transition: all 0.3s ease;
}

.hero-dashboard:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(220, 38, 38, 0.3);
}

.modern-card {
    background: white;
    border: 2px solid #f3f4f6;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s ease;
}

.modern-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.12);
    border-color: #dc2626;
}

.card-header-modern {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    padding: 0.75rem;
    border-bottom: none;
}

.btn-modern-primary {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border: none;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
}

.btn-modern-primary:hover {
    background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(220, 38, 38, 0.4);
    color: white;
}

.btn-modern-outline {
    background: transparent;
    border: 2px solid #dc2626;
    color: #dc2626;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-modern-outline:hover {
    background: #dc2626;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(220, 38, 38, 0.3);
}

.info-item {
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    border-radius: 6px;
    border-left: 4px solid #dc2626;
    padding: 1rem;
    margin-bottom: 0.75rem;
    transition: all 0.3s ease;
}

.info-item:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 15px rgba(220, 38, 38, 0.1);
}

.info-item:last-child {
    margin-bottom: 0;
}

body {
    background: linear-gradient(135deg, #fef2f2 0%, #f3f4f6 100%);
    min-height: 100vh;
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

.form-control, .form-select {
    font-size: 0.8rem !important;
}

.btn {
    font-size: 0.85rem !important;
}

.info-item strong {
    font-size: 0.8rem !important;
}

.info-item .text-muted {
    font-size: 0.75rem !important;
}

.badge {
    font-size: 0.75rem !important;
}

h5 {
    font-size: 1rem !important;
}

h6 {
    font-size: 0.9rem !important;
}

.text-center h5 {
    font-size: 0.9rem !important;
}

.text-center p {
    font-size: 0.8rem !important;
}

.form-check-label {
    font-size: 0.8rem !important;
}

/* Mobile responsive adjustments */
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
    
    .form-control, .form-select {
        font-size: 0.7rem !important;
    }
    
    .btn {
        font-size: 0.75rem !important;
    }
    
    .info-item strong {
        font-size: 0.7rem !important;
    }
    
    .info-item .text-muted {
        font-size: 0.65rem !important;
    }
    
    .badge {
        font-size: 0.65rem !important;
    }
    
    h5 {
        font-size: 0.9rem !important;
    }
    
    h6 {
        font-size: 0.8rem !important;
    }
    
    .text-center h5 {
        font-size: 0.8rem !important;
    }
    
    .text-center p {
        font-size: 0.7rem !important;
    }
    
    .form-check-label {
        font-size: 0.7rem !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length > 0) {
            if (value.startsWith('255')) {
                // Already has country code
                value = value.substring(0, 12);
            } else if (value.startsWith('0')) {
                // Remove leading 0 and add country code
                value = '255' + value.substring(1);
                value = value.substring(0, 12);
            } else {
                // Add country code
                value = '255' + value;
                value = value.substring(0, 12);
            }
        }
        this.value = value;
    });
});
</script>
@endpush
