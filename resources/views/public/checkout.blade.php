@extends('public.layouts.app')

@section('title', 'Checkout - CHIBO BRAND')
@section('description', 'Complete your order and get your products delivered.')

@section('content')
<div class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="page-title">Checkout</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('cart') }}">Cart</a></li>
                        <li class="breadcrumb-item active">Checkout</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <form method="POST" action="{{ route('checkout.process') }}" id="checkoutForm">
        @csrf
        <input type="hidden" name="vat_receipt" value="{{ request('vat_receipt', '0') }}">
        <div class="row">
            <!-- Customer Information -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="{{ auth('customer')->check() ? auth('customer')->user()->name : '' }}{{ old('name') }}" required>
                                @error('name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="{{ auth('customer')->check() ? auth('customer')->user()->phone : '' }}{{ old('phone') }}" required>
                                @error('phone')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="{{ auth('customer')->check() ? auth('customer')->user()->email : '' }}{{ old('email') }}">
                            @error('email')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Shipping Address</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">Shipping Address <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required>{{ auth('customer')->check() ? auth('customer')->user()->address : '' }}{{ old('shipping_address') }}</textarea>
                            @error('shipping_address')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="billing_address" class="form-label">Billing Address <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="billing_address" name="billing_address" rows="3" required>{{ auth('customer')->check() ? auth('customer')->user()->address : '' }}{{ old('billing_address') }}</textarea>
                            @error('billing_address')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Select Payment Method <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="cash_delivery" value="Cash on Delivery" checked>
                                <label class="form-check-label" for="cash_delivery">
                                    <i class="fas fa-money-bill-wave me-2"></i>Cash on Delivery
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="mobile_money" value="Mobile Money">
                                <label class="form-check-label" for="mobile_money">
                                    <i class="fas fa-mobile-alt me-2"></i>Mobile Money
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="Bank Transfer">
                                <label class="form-check-label" for="bank_transfer">
                                    <i class="fas fa-university me-2"></i>Bank Transfer
                                </label>
                            </div>
                        </div>
                        @error('payment_method')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Order Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Notes</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Special Instructions (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any special instructions for your order...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Summary</h5>
                        @if($totals['vat'] > 0)
                            <small class="text-success">
                                <i class="fas fa-receipt me-1"></i>VAT Receipt Included (+18%)
                            </small>
                        @endif
                    </div>
                    <div class="card-body">
                        <!-- Cart Items -->
                        <div class="mb-3">
                            @foreach($cart as $item)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <small class="fw-bold">{{ $item['name'] }}</small>
                                        <br>
                                        <small class="text-muted">Qty: {{ $item['quantity'] }}</small>
                                    </div>
                                    <small>TZS {{ number_format($item['price'] * $item['quantity'], 0) }}</small>
                                </div>
                            @endforeach
                        </div>
                        
                        <hr>
                        
                        <!-- Totals -->
                        <div class="small">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Subtotal:</span>
                                <span>TZS {{ number_format($totals['subtotal'], 0) }}</span>
                            </div>
                            @if($totals['vat'] > 0)
                            <div class="d-flex justify-content-between mb-1">
                                <span>VAT (18%):</span>
                                <span>TZS {{ number_format($totals['vat'], 0) }}</span>
                            </div>
                            @endif
                            <div class="d-flex justify-content-between mb-1">
                                <span>Shipping:</span>
                                <span>TZS {{ number_format($totals['shipping'], 0) }}</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between">
                                <strong>Total:</strong>
                                <strong class="text-primary">TZS {{ number_format($totals['total'], 0) }}</strong>
                            </div>
                        </div>
                        
                        <!-- Checkout Button -->
                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                            <i class="fas fa-credit-card me-2"></i>Complete Order
                        </button>
                        
                        <!-- WhatsApp Alternative -->
                        <div class="text-center">
                            <p class="text-muted small mb-2">Or order directly via WhatsApp</p>
                            <a href="https://wa.me/255655392319" class="btn btn-success" target="_blank">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp Order
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-shield-alt text-success me-2"></i>Secure Checkout
                        </h6>
                        <p class="text-muted small mb-0">
                            Your information is secure and will only be used to process your order. 
                            We never share your personal details with third parties.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Form validation
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        console.log('=== CHECKOUT FORM SUBMISSION STARTED ===');
        console.log('Form action:', this.action);
        console.log('Form method:', this.method);
        console.log('Form data:', new FormData(this));
        
        const requiredFields = ['name', 'phone', 'shipping_address', 'billing_address'];
        let isValid = true;
        
        requiredFields.forEach(function(fieldName) {
            const field = document.getElementById(fieldName);
            console.log('Field ' + fieldName + ':', field.value);
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            console.log('Form validation failed');
            e.preventDefault();
            if (typeof showToast === 'function') {
                showToast('Please fill in all required fields', 'error');
            } else {
                alert('Please fill in all required fields');
            }
        } else {
            console.log('Form validation passed, submitting...');
        }
    });

    // Copy shipping address to billing address
    document.getElementById('shipping_address').addEventListener('input', function() {
        if (this.value && !document.getElementById('billing_address').value) {
            document.getElementById('billing_address').value = this.value;
        }
    });

    // Auto-fill customer information if logged in
    @auth('customer')
        document.addEventListener('DOMContentLoaded', function() {
            const customer = @json(auth('customer')->user());
            if (customer && customer.name) {
                document.getElementById('name').value = customer.name || '';
                document.getElementById('phone').value = customer.phone || '';
                document.getElementById('email').value = customer.email || '';
                if (customer.address) {
                    document.getElementById('shipping_address').value = customer.address;
                    document.getElementById('billing_address').value = customer.address;
                }
            }
        });
    @endauth
</script>
@endpush
