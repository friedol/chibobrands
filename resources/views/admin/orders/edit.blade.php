@extends('layouts.admin')

@section('title', 'Edit Order - CHIBO BRAND')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Order Quantities</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Order
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order: {{ $order->order_code }}</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.orders.update', $order) }}" data-no-preloader>
                        @csrf
                        @method('PUT')
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Current Stock</th>
                                        <th>Current Quantity</th>
                                        <th>New Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($item->product && $item->product->images->count() > 0)
                                                        <img src="{{ $item->product->images->first()->image_url }}" 
                                                             alt="{{ $item->product->name }}" 
                                                             class="img-thumbnail me-3" 
                                                             style="width: 50px; height: 50px; object-fit: cover;">
                                                    @else
                                                        <div class="img-thumbnail me-3 bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <h6 class="mb-0">{{ $item->product->name ?? $item->product_name }}</h6>
                                                        <small class="text-muted">{{ $item->product->category ?? 'No Category' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $stock = $item->product->stock_quantity ?? 0;
                                                @endphp
                                                <span class="badge badge-{{ $stock > 10 ? 'success' : ($stock > 0 ? 'warning' : 'danger') }}">
                                                    {{ $stock }}
                                                </span>
                                            </td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>
                                                <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                                <input type="number" 
                                                       name="items[{{ $loop->index }}][quantity]" 
                                                       value="{{ $item->quantity }}" 
                                                       min="1" 
                                                       max="{{ ($item->product->stock_quantity ?? 0) + $item->quantity }}"
                                                       class="form-control quantity-input" 
                                                       data-unit-price="{{ $item->unit_price }}"
                                                       required>
                                            </td>
                                            <td>TZS {{ number_format($item->unit_price, 0) }}</td>
                                            <td class="subtotal-cell">TZS {{ number_format($item->subtotal, 0) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-primary">
                                        <th colspan="5" class="text-end">New Total:</th>
                                        <th class="h5" id="new-total">TZS {{ number_format($order->total_amount, 0) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Maximum quantity is limited by current stock + current order quantity
                                </small>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary" data-no-global-handler>
                                    <i class="fas fa-save me-1"></i>Update Order
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Order Summary -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order Summary</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Order Code:</strong></td>
                            <td>{{ $order->order_code }}</td>
                        </tr>
                        <tr>
                            <td><strong>Customer:</strong></td>
                            <td>{{ $order->user->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Current Total:</strong></td>
                            <td class="h6 text-primary">TZS {{ number_format($order->total_amount, 0) }}</td>
                        </tr>
                        <tr>
                            <td><strong>New Total:</strong></td>
                            <td class="h6 text-success" id="summary-new-total">TZS {{ number_format($order->total_amount, 0) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Difference:</strong></td>
                            <td class="h6" id="difference">TZS 0</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Stock Warning -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>Stock Information
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Current stock levels for each product. Make sure the new quantities don't exceed available stock when the order is approved.
                    </p>
                    @foreach($order->items as $item)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small">{{ $item->product->name ?? $item->product_name }}</span>
                            @php
                                $stock = $item->product->stock_quantity ?? 0;
                            @endphp
                            <span class="badge badge-{{ $stock > 10 ? 'success' : ($stock > 0 ? 'warning' : 'danger') }}">
                                {{ $stock }} in stock
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const subtotalCells = document.querySelectorAll('.subtotal-cell');
    const newTotalElement = document.getElementById('new-total');
    const summaryNewTotalElement = document.getElementById('summary-new-total');
    const differenceElement = document.getElementById('difference');
    const originalTotal = {{ $order->total_amount }};

    function updateTotals() {
        let newTotal = 0;
        
        quantityInputs.forEach((input, index) => {
            const quantity = parseInt(input.value) || 0;
            const unitPrice = parseFloat(input.dataset.unitPrice);
            const subtotal = quantity * unitPrice;
            
            subtotalCells[index].textContent = 'TZS ' + subtotal.toLocaleString();
            newTotal += subtotal;
        });
        
        const formattedTotal = 'TZS ' + newTotal.toLocaleString();
        newTotalElement.textContent = formattedTotal;
        summaryNewTotalElement.textContent = formattedTotal;
        
        const difference = newTotal - originalTotal;
        differenceElement.textContent = 'TZS ' + difference.toLocaleString();
        differenceElement.className = 'h6 ' + (difference > 0 ? 'text-success' : (difference < 0 ? 'text-danger' : 'text-muted'));
    }

    quantityInputs.forEach(input => {
        input.addEventListener('input', updateTotals);
    });
});
</script>
@endsection
