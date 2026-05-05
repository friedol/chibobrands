@extends('layouts.app')

@section('title', $product->name . ' - CHIBO BRAND')
@section('description', Str::limit($product->description, 160))

@section('content')
<div class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
                @if($product->category)
                    <li class="breadcrumb-item"><a href="{{ route('products.index', ['category' => $product->category->id]) }}">{{ $product->category->name }}</a></li>
                @endif
                <li class="breadcrumb-item active">{{ $product->name }}</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container">
    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6 mb-4">
            @if($product->images->count() > 0)
                <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach($product->images as $index => $image)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <img src="{{ asset('storage/' . $image->image_path) }}?v={{ time() }}" class="d-block w-100" alt="{{ $product->name }}" style="height: 400px; object-fit: cover;">
                            </div>
                        @endforeach
                    </div>
                    @if($product->images->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    @endif
                </div>
            @else
                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                    <i class="fas fa-image text-muted" style="font-size: 4rem;"></i>
                </div>
            @endif
        </div>

        <!-- Product Details -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title">{{ $product->name }}</h1>
                    
                    @if($product->category)
                        <p class="text-muted mb-3">
                            <i class="fas fa-tag me-2"></i>Category: {{ $product->category->name }}
                        </p>
                    @endif

                    <div class="mb-3">
                        <span class="price display-6">TZS {{ number_format($product->base_price, 0) }}</span>
                        @if($product->availability === 'out_of_stock')
                            <span class="badge bg-danger ms-2">Out of Stock</span>
                        @elseif($product->availability === 'custom')
                            <span class="badge bg-warning ms-2">Custom Order</span>
                        @else
                            <span class="badge bg-success ms-2">In Stock</span>
                        @endif
                    </div>

                    <div class="mb-4">
                        <h5>Description</h5>
                        <p>{{ $product->description }}</p>
                    </div>

                    @if($product->features)
                        <div class="mb-4">
                            <h5>Features</h5>
                            <ul class="list-unstyled">
                                @foreach(explode("\n", $product->features) as $feature)
                                    @if(trim($feature))
                                        <li><i class="fas fa-check text-success me-2"></i>{{ trim($feature) }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Product Options Form -->
                    <form id="productForm" class="mb-4">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        
                        <!-- Variations -->
                        @if($product->variations->count() > 0)
                            <div class="mb-3">
                                <h6>Options</h6>
                                @foreach($product->variations->groupBy('name') as $variationName => $variations)
                                    <div class="mb-2">
                                        <label class="form-label">{{ $variationName }}</label>
                                        <select class="form-select variation-select" name="variations[]" data-name="{{ $variationName }}">
                                            <option value="">Select {{ $variationName }}</option>
                                            @foreach($variations as $variation)
                                                <option value="{{ $variation->id }}" data-price="{{ $variation->extra_price }}">
                                                    {{ $variation->option_value }} 
                                                    @if($variation->extra_price > 0)
                                                        (+TZS {{ number_format($variation->extra_price, 0) }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Add-ons -->
                        @if($product->addons->count() > 0)
                            <div class="mb-3">
                                <h6>Add-ons</h6>
                                @foreach($product->addons as $addon)
                                    <div class="form-check">
                                        <input class="form-check-input addon-checkbox" type="checkbox" 
                                               name="addons[]" value="{{ $addon->id }}" 
                                               id="addon_{{ $addon->id }}" data-price="{{ $addon->addon_price }}">
                                        <label class="form-check-label" for="addon_{{ $addon->id }}">
                                            {{ $addon->addon_name }} 
                                            @if($addon->addon_price > 0)
                                                (+TZS {{ number_format($addon->addon_price, 0) }})
                                            @endif
                                            @if($addon->description)
                                                <small class="text-muted d-block">{{ $addon->description }}</small>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Custom Inputs -->
                        @if($product->customInputs->count() > 0)
                            <div class="mb-3">
                                <h6>Custom Requirements</h6>
                                @foreach($product->customInputs as $input)
                                    <div class="mb-2">
                                        <label class="form-label">
                                            {{ $input->input_label }}
                                            @if($input->is_required)
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        @if($input->input_type === 'textarea')
                                            <textarea class="form-control custom-input" 
                                                      name="custom_inputs[{{ $input->id }}]" 
                                                      {{ $input->is_required ? 'required' : '' }}
                                                      rows="3"></textarea>
                                        @elseif($input->input_type === 'file')
                                            <input type="file" class="form-control custom-input" 
                                                   name="custom_inputs[{{ $input->id }}]" 
                                                   {{ $input->is_required ? 'required' : '' }}
                                                   accept="image/*,.pdf,.doc,.docx">
                                        @else
                                            <input type="text" class="form-control custom-input" 
                                                   name="custom_inputs[{{ $input->id }}]" 
                                                   {{ $input->is_required ? 'required' : '' }}>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Quantity -->
                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <div class="input-group" style="max-width: 150px;">
                                <button class="btn btn-outline-secondary" type="button" onclick="decreaseQuantity()">-</button>
                                <input type="number" class="form-control text-center" name="quantity" id="quantity" value="1" min="1" max="100">
                                <button class="btn btn-outline-secondary" type="button" onclick="increaseQuantity()">+</button>
                            </div>
                        </div>

                        <!-- Price Display -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Total Price:</span>
                                <span class="price" id="totalPrice">TZS {{ number_format($product->base_price, 0) }}</span>
                            </div>
                        </div>

                        <!-- Add to Cart Button -->
                        @if($product->availability !== 'out_of_stock')
                            <button type="button" class="btn btn-primary btn-lg w-100" onclick="addToCart()">
                                <i class="fas fa-cart-plus me-2"></i>Add to Cart
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary btn-lg w-100" disabled>
                                <i class="fas fa-times me-2"></i>Out of Stock
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="section-title">Related Products</h3>
                <div class="row">
                    @foreach($relatedProducts as $relatedProduct)
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card product-card h-100">
                                @if($relatedProduct->images->count() > 0)
                                    <img src="{{ asset('storage/' . $relatedProduct->images->first()->image_path) }}?v={{ time() }}" 
                                         class="card-img-top product-image" alt="{{ $relatedProduct->name }}">
                                @else
                                    <div class="card-img-top product-image bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-image text-muted" style="font-size: 2rem;"></i>
                                    </div>
                                @endif
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title">{{ $relatedProduct->name }}</h6>
                                    <p class="card-text text-muted small flex-grow-1">{{ Str::limit($relatedProduct->description, 80) }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="price small">TZS {{ number_format($relatedProduct->base_price, 0) }}</span>
                                        <a href="{{ route('products.show', $relatedProduct) }}" class="btn btn-primary btn-sm">
                                            View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    let basePrice = {{ $product->base_price }};
    let currentPrice = basePrice;

    // Update price when variations or addons change
    function updatePrice() {
        currentPrice = basePrice;
        
        // Add variation prices
        $('.variation-select').each(function() {
            const selectedOption = $(this).find('option:selected');
            if (selectedOption.val()) {
                currentPrice += parseFloat(selectedOption.data('price') || 0);
            }
        });
        
        // Add addon prices
        $('.addon-checkbox:checked').each(function() {
            currentPrice += parseFloat($(this).data('price') || 0);
        });
        
        // Update display
        $('#totalPrice').text('TZS ' + currentPrice.toLocaleString());
    }

    // Quantity controls
    function increaseQuantity() {
        const quantityInput = document.getElementById('quantity');
        const currentValue = parseInt(quantityInput.value);
        if (currentValue < 100) {
            quantityInput.value = currentValue + 1;
        }
    }

    function decreaseQuantity() {
        const quantityInput = document.getElementById('quantity');
        const currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    }

    // Add to cart function
    function addToCart() {
        const formData = new FormData(document.getElementById('productForm'));
        
        // Collect variations
        const variations = [];
        $('.variation-select').each(function() {
            if ($(this).val()) {
                variations.push($(this).val());
            }
        });
        
        // Collect addons
        const addons = [];
        $('.addon-checkbox:checked').each(function() {
            addons.push($(this).val());
        });
        
        // Collect custom inputs
        const customInputs = {};
        $('.custom-input').each(function() {
            const name = $(this).attr('name');
            if (name && $(this).val()) {
                customInputs[name] = $(this).val();
            }
        });
        
        $.ajax({
            url: '{{ route("cart.add") }}',
            method: 'POST',
            data: {
                product_id: {{ $product->id }},
                quantity: $('#quantity').val(),
                variations: variations,
                addons: addons,
                custom_inputs: customInputs,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    updateCartCount();
                }
            },
            error: function(xhr) {
                showToast('Error adding item to cart', 'error');
            }
        });
    }

    // Event listeners
    $(document).ready(function() {
        $('.variation-select, .addon-checkbox').on('change', updatePrice);
        updatePrice();
    });
</script>
@endpush