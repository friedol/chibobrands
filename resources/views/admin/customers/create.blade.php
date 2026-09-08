@extends('layouts.admin')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Create Customer</h4>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.customers.store') }}" data-no-global-handler data-no-preloader>
                @csrf

                @if ($errors->any())
                    <div class="alert alert-danger mb-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="phone_country_code"
                                   class="form-control text-center fw-bold"
                                   value="{{ old('phone_country_code', '+255') }}"
                                   placeholder="+255" maxlength="6"
                                   style="max-width:80px;" title="Country code — e.g. +255, +254">
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="XXX XXX XXX" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="form-text text-muted">Primary contact number</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">WhatsApp Number</label>
                        <div class="input-group">
                            <select name="whatsapp_country_code" class="form-select" style="max-width: 120px;">
                                <option value="+255" selected>🇹🇿 +255</option>
                                <option value="+254">🇰🇪 +254</option>
                                <option value="+256">🇺🇬 +256</option>
                                <option value="+250">🇷🇼 +250</option>
                                <option value="+257">🇧🇮 +257</option>
                                <option value="+243">🇨🇩 +243</option>
                                <option value="+27">🇿🇦 +27</option>
                                <option value="+234">🇳🇬 +234</option>
                                <option value="+1">🇺🇸 +1</option>
                                <option value="+44">🇬🇧 +44</option>
                                <option value="+971">🇦🇪 +971</option>
                                <option value="+91">🇮🇳 +91</option>
                                <option value="+86">🇨🇳 +86</option>
                            </select>
                            <input type="text" name="whatsapp_number" class="form-control @error('whatsapp_number') is-invalid @enderror" value="{{ old('whatsapp_number') }}" placeholder="XXX XXX XXX">
                            @error('whatsapp_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="form-text text-muted">Leave blank if same as phone number</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Business Type</label>
                        <select name="business_type" class="form-select">
                            <option value="">Select Business Type</option>
                            <option value="retail" {{ old('business_type')=='retail' ? 'selected' : '' }}>Retail Store</option>
                            <option value="wholesale" {{ old('business_type')=='wholesale' ? 'selected' : '' }}>Wholesale Distributor</option>
                            <option value="printing" {{ old('business_type')=='printing' ? 'selected' : '' }}>Printing Company</option>
                            <option value="advertising" {{ old('business_type')=='advertising' ? 'selected' : '' }}>Advertising Agency</option>
                            <option value="corporate" {{ old('business_type')=='corporate' ? 'selected' : '' }}>Corporate</option>
                            <option value="other" {{ old('business_type')=='other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Customer Source</label>
                        <select name="customer_source" class="form-select">
                            <option value="">Select Marketing Source</option>
                            @php $sources = \App\Models\CustomerSource::active()->get(); @endphp
                            @foreach($sources as $source)
                                <option value="{{ $source->name }}" {{ old('customer_source') == $source->name ? 'selected' : '' }}>{{ $source->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(auth()->user()->role !== 'saler')
                    <div class="col-md-6">
                        <label class="form-label">Brought By (Saler)</label>
                        <select name="added_by" class="form-select @error('added_by') is-invalid @enderror">
                            <option value="">Select Saler (Optional)</option>
                            @foreach($salers as $saler)
                                <option value="{{ $saler->id }}" {{ old('added_by') == $saler->id ? 'selected' : '' }}>
                                    {{ $saler->name }} ({{ $saler->phone ?? 'No Phone' }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select the saler who brought this customer.</small>
                        @error('added_by')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @endif
                    <div class="col-md-6">
                        <label class="form-label">Region</label>
                        <select name="region_id" id="region_id" class="form-select @error('region_id') is-invalid @enderror">
                            <option value="">Select Region (Optional)</option>
                            @foreach($regions as $region)
                                <option value="{{ $region->id }}" {{ old('region_id') == $region->id ? 'selected' : '' }}>{{ $region->region_name }}</option>
                            @endforeach
                        </select>
                        @error('region_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">District</label>
                        <select name="district_id" id="district_id" class="form-select @error('district_id') is-invalid @enderror">
                            <option value="">Select District (Optional)</option>
                        </select>
                        @error('district_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Latitude</label>
                        <input type="text" name="latitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude') }}" placeholder="-6.7924">
                        <small class="form-text text-muted">Needed for map pin display.</small>
                        @error('latitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Longitude</label>
                        <input type="text" name="longitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude') }}" placeholder="39.2083">
                        <small class="form-text text-muted">Needed for map pin display.</small>
                        @error('longitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Business Address</label>
                        <textarea name="address" rows="3" class="form-control">{{ old('address') }}</textarea>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check me-4">
                            <input class="form-check-input" type="checkbox" name="verified" id="verified" {{ old('verified') ? 'checked' : '' }}>
                            <label class="form-check-label" for="verified">Verified</label>
                        </div>
                        <div class="form-check me-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_wholesale" id="is_wholesale" value="1" {{ old('is_wholesale') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_wholesale">Interested in Wholesale Pricing</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-danger" data-no-global-handler><i class="fas fa-save me-1"></i> Save</button>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const regionSelect = document.getElementById('region_id');
    const districtSelect = document.getElementById('district_id');
    const oldDistrictId = "{{ old('district_id') }}";

    if (regionSelect && districtSelect) {
        regionSelect.addEventListener('change', function() {
            const regionId = this.value;
            districtSelect.innerHTML = '<option value="">Loading districts...</option>';
            districtSelect.disabled = true;
            
            if (!regionId) {
                districtSelect.innerHTML = '<option value="">Select District (Optional)</option>';
                districtSelect.disabled = false;
                return;
            }
            
            fetch(`/regions/${regionId}/districts`)
                .then(response => response.json())
                .then(data => {
                    let html = '<option value="">Select District (Optional)</option>';
                    data.forEach(district => {
                        html += `<option value="${district.id}">${district.district_name}</option>`;
                    });
                    districtSelect.innerHTML = html;
                    districtSelect.disabled = false;
                    if (oldDistrictId) {
                        districtSelect.value = oldDistrictId;
                    }
                })
                .catch(error => {
                    console.error('Error fetching districts:', error);
                    districtSelect.innerHTML = '<option value="">Error loading districts</option>';
                    districtSelect.disabled = false;
                });
        });
        
        if (regionSelect.value) {
            regionSelect.dispatchEvent(new Event('change'));
        }
    }
});
</script>
@endpush


