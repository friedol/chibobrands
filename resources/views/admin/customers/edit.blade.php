@extends('layouts.admin')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.customers.index') }}" class="text-decoration-none">
                    <i class="fas fa-users me-1"></i>Customers
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <i class="fas fa-edit me-1"></i>Edit Customer
            </li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Edit Customer</h4>
            <p class="text-muted small mb-0">{{ $customer->name }}</p>
        </div>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Validation Errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header py-2">
            <h6 class="mb-0 fw-bold text-white">
                <i class="fas fa-user-edit me-1"></i>Customer Information
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.customers.update', $customer) }}" data-no-preloader>
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $customer->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email', $customer->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="phone_country_code" class="form-select" style="max-width: 120px;">
                                <option value="+255" {{ str_starts_with(old('phone', $customer->phone), '+255') ? 'selected' : '' }}>🇹🇿 +255</option>
                                <option value="+254" {{ str_starts_with(old('phone', $customer->phone), '+254') ? 'selected' : '' }}>🇰🇪 +254</option>
                                <option value="+256" {{ str_starts_with(old('phone', $customer->phone), '+256') ? 'selected' : '' }}>🇺🇬 +256</option>
                                <option value="+250" {{ str_starts_with(old('phone', $customer->phone), '+250') ? 'selected' : '' }}>🇷🇼 +250</option>
                                <option value="+257" {{ str_starts_with(old('phone', $customer->phone), '+257') ? 'selected' : '' }}>🇧🇮 +257</option>
                                <option value="+243" {{ str_starts_with(old('phone', $customer->phone), '+243') ? 'selected' : '' }}>🇨🇩 +243</option>
                                <option value="+27" {{ str_starts_with(old('phone', $customer->phone), '+27') ? 'selected' : '' }}>🇿🇦 +27</option>
                                <option value="+234" {{ str_starts_with(old('phone', $customer->phone), '+234') ? 'selected' : '' }}>🇳🇬 +234</option>
                                <option value="+1" {{ str_starts_with(old('phone', $customer->phone), '+1') ? 'selected' : '' }}>🇺🇸 +1</option>
                                <option value="+44" {{ str_starts_with(old('phone', $customer->phone), '+44') ? 'selected' : '' }}>🇬🇧 +44</option>
                                <option value="+971" {{ str_starts_with(old('phone', $customer->phone), '+971') ? 'selected' : '' }}>🇦🇪 +971</option>
                                <option value="+91" {{ str_starts_with(old('phone', $customer->phone), '+91') ? 'selected' : '' }}>🇮🇳 +91</option>
                                <option value="+86" {{ str_starts_with(old('phone', $customer->phone), '+86') ? 'selected' : '' }}>🇨🇳 +86</option>
                            </select>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone', preg_replace('/^\+\d{1,4}\s*/', '', $customer->phone)) }}" placeholder="XXX XXX XXX" required>
                        </div>

                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">WhatsApp Number</label>
                        <div class="input-group">
                            <select name="whatsapp_country_code" class="form-select" style="max-width: 120px;">
                                <option value="+255" {{ str_starts_with(old('whatsapp_number', $customer->whatsapp_number), '+255') ? 'selected' : 'selected' }}>🇹🇿 +255</option>
                                <option value="+254" {{ str_starts_with(old('whatsapp_number', $customer->whatsapp_number), '+254') ? 'selected' : '' }}>🇰🇪 +254</option>
                                <option value="+256" {{ str_starts_with(old('whatsapp_number', $customer->whatsapp_number), '+256') ? 'selected' : '' }}>🇺🇬 +256</option>
                                <option value="+250" {{ str_starts_with(old('whatsapp_number', $customer->whatsapp_number), '+250') ? 'selected' : '' }}>🇷🇼 +250</option>
                                <option value="+257" {{ str_starts_with(old('whatsapp_number', $customer->whatsapp_number), '+257') ? 'selected' : '' }}>🇧🇮 +257</option>
                                <option value="+243" {{ str_starts_with(old('whatsapp_number', $customer->whatsapp_number), '+243') ? 'selected' : '' }}>🇨🇩 +243</option>
                                <option value="+27" {{ str_starts_with(old('whatsapp_number', $customer->whatsapp_number), '+27') ? 'selected' : '' }}>🇿🇦 +27</option>
                                <option value="+234" {{ str_starts_with(old('whatsapp_number', $customer->whatsapp_number), '+234') ? 'selected' : '' }}>🇳🇬 +234</option>
                                <option value="+1" {{ str_starts_with(old('whatsapp_number', $customer->whatsapp_number), '+1') ? 'selected' : '' }}>🇺🇸 +1</option>
                                <option value="+44" {{ str_starts_with(old('whatsapp_number', $customer->whatsapp_number), '+44') ? 'selected' : '' }}>🇬🇧 +44</option>
                                <option value="+971" {{ str_starts_with(old('whatsapp_number', $customer->whatsapp_number), '+971') ? 'selected' : '' }}>🇦🇪 +971</option>
                                <option value="+91" {{ str_starts_with(old('whatsapp_number', $customer->whatsapp_number), '+91') ? 'selected' : '' }}>🇮🇳 +91</option>
                                <option value="+86" {{ str_starts_with(old('whatsapp_number', $customer->whatsapp_number), '+86') ? 'selected' : '' }}>🇨🇳 +86</option>
                            </select>
                            <input type="text" name="whatsapp_number" class="form-control @error('whatsapp_number') is-invalid @enderror" 
                                   value="{{ old('whatsapp_number', $customer->whatsapp_number ? preg_replace('/^\+\d{1,4}\s*/', '', $customer->whatsapp_number) : '') }}" placeholder="XXX XXX XXX">
                        </div>

                        @error('whatsapp_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 

                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" 
                               placeholder="Confirm new password">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" 
                               value="{{ old('company_name', $customer->company_name) }}">
                        @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Business Type</label>
                        <select name="business_type" class="form-select @error('business_type') is-invalid @enderror">
                            <option value="">Select Business Type</option>
                            <option value="retail" {{ old('business_type', $customer->business_type) == 'retail' ? 'selected' : '' }}>Retail Store</option>
                            <option value="wholesale" {{ old('business_type', $customer->business_type) == 'wholesale' ? 'selected' : '' }}>Wholesale Distributor</option>
                            <option value="printing" {{ old('business_type', $customer->business_type) == 'printing' ? 'selected' : '' }}>Printing Company</option>
                            <option value="advertising" {{ old('business_type', $customer->business_type) == 'advertising' ? 'selected' : '' }}>Advertising Agency</option>
                            <option value="corporate" {{ old('business_type', $customer->business_type) == 'corporate' ? 'selected' : '' }}>Corporate</option>
                            <option value="other" {{ old('business_type', $customer->business_type) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('business_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @if(auth()->user()->role !== 'saler')
                    <div class="col-md-6">
                        <label class="form-label">Brought By (Saler)</label>
                        <select name="added_by" class="form-select @error('added_by') is-invalid @enderror">
                            <option value="">Select Saler (Optional)</option>
                            @foreach($salers as $saler)
                                <option value="{{ $saler->id }}" {{ old('added_by', $customer->added_by) == $saler->id ? 'selected' : '' }}>
                                    {{ $saler->name }} ({{ $saler->phone ?? 'No Phone' }})
                                </option>
                            @endforeach
                        </select>

                        @error('added_by')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @endif
                    <div class="col-12">
                        <label class="form-label">Business Address</label>
                        <textarea name="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address', $customer->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12 d-flex align-items-end gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="verified" id="verified" 
                                   {{ old('verified', $customer->verified) ? 'checked' : '' }}>
                            <label class="form-check-label" for="verified">Verified</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                   {{ old('is_active', $customer->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_wholesale" id="is_wholesale" value="1" 
                                   {{ old('is_wholesale', $customer->is_wholesale) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_wholesale">Interested in Wholesale Pricing</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-danger" data-no-global-handler>
                        <i class="fas fa-save me-1"></i> Update Customer
                    </button>
                    <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-eye me-1"></i> View Details
                    </a>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Breadcrumb styles */
.breadcrumb {
    background: transparent;
    padding: 0.5rem 0;
    margin-bottom: 0;
    font-size: 0.875rem;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: #6c757d;
    font-size: 1.2rem;
    font-weight: 600;
    margin: 0 0.5rem;
}

.breadcrumb-item a {
    color: #6c757d;
    text-decoration: none;
    transition: color 0.2s ease;
}

.breadcrumb-item a:hover {
    color: #0d6efd;
}

.breadcrumb-item.active {
    color: #495057;
    font-weight: 500;
}

    .breadcrumb-item i {
        font-size: 0.75rem;
    }
    
    /* Font size refinements */
    body { font-size: 13px !important; }
    h4, .h4 { font-size: 14px !important; }
    .form-control, .form-select, .btn, label { font-size: 13px !important; }
    .text-muted { font-size: 12px !important; }
    .card-header h6 { font-size: 14px !important; }
</style>
@endpush

@endsection

