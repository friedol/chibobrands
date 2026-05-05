@extends('layouts.admin')

@section('title', 'Order Details - CHIBO BRAND')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 fw-bold text-primary mb-2" style="font-size: 1.5rem;">
                                <i class="fas fa-receipt me-2"></i>Order Details
                            </h1>
                            <p class="text-muted mb-0" style="font-size: 0.9rem;">Order Code: <span class="fw-bold">{{ $order->order_code }}</span></p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary d-flex align-items-center justify-content-center">
                                <i class="fas fa-arrow-left me-md-1"></i>
                                <span class="d-none d-md-inline">Back to Orders</span>
                            </a>
                            <a href="https://wa.me/255655392319?text={{ urlencode($order->generateWhatsAppMessage()) }}" 
                               class="btn btn-success d-flex align-items-center justify-content-center" target="_blank">
                                <i class="fab fa-whatsapp me-md-1"></i>
                                <span class="d-none d-md-inline">Open in WhatsApp</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Order Information -->
        <div class="col-lg-8 mb-4">
            <!-- Order Summary Card -->
            <div class="card mb-4">
                <div class="card-header" style="padding: 0.5rem 1rem;">
                    <h5 class="mb-0" style="font-size: 0.9rem;">
                        <i class="fas fa-info-circle me-2"></i>Order Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted" style="font-size: 0.8rem;">Order Code</label>
                                <div class="text-primary fw-bold" style="font-size: 0.9rem;">{{ $order->order_code }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted" style="font-size: 0.8rem;">Order Date</label>
                                <div style="font-size: 0.85rem;">{{ $order->created_at->format('M j, Y H:i') }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted" style="font-size: 0.8rem;">Total Amount</label>
                                <div class="text-success fw-bold" style="font-size: 1rem;">TZS {{ number_format($order->total_amount, 0) }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted" style="font-size: 0.8rem;">Payment Status</label>
                                <div>
                                    <span class="badge badge-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'unpaid' ? 'danger' : 'warning') }}" style="font-size: 0.7rem;">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted" style="font-size: 0.8rem;">Approval Status</label>
                                <div>
                                    <span class="badge badge-{{ $order->approval_status === 'approved' ? 'success' : ($order->approval_status === 'cancelled' ? 'danger' : 'warning') }}" style="font-size: 0.7rem;">
                                        {{ ucfirst($order->approval_status) }}
                                    </span>
                                </div>
                            </div>
                            @if($order->notes)
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted" style="font-size: 0.8rem;">Notes</label>
                                    <div class="p-3 bg-light rounded" style="font-size: 0.8rem;">{{ $order->notes }}</div>
                                </div>
                            @endif
                            @php
                                $salerPhone = null;
                                $saler = null;
                                if ($order->notes && preg_match('/Assigned to saler: \+(\d+)/', $order->notes, $matches)) {
                                    $salerPhone = '+' . $matches[1];
                                    // Try to find the saler user
                                    $saler = \App\Models\User::where('role', 'saler')
                                        ->whereRaw("REPLACE(REPLACE(phone, '+', ''), ' ', '') = ?", [str_replace(['+', ' '], '', $matches[1])])
                                        ->first();
                                }
                            @endphp
                            @if($salerPhone)
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-muted" style="font-size: 0.8rem;">Assigned Saler</label>
                                    <div class="p-3 bg-light rounded">
                                        @if($saler)
                                            <div class="fw-bold text-primary" style="font-size: 0.85rem;">{{ $saler->name }}</div>
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                <i class="fas fa-phone me-1"></i>{{ $salerPhone }}
                                            </small>
                                        @else
                                            <div class="text-muted" style="font-size: 0.85rem;">{{ $salerPhone }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card">
                <div class="card-header" style="padding: 0.5rem 1rem;">
                    <h5 class="mb-0" style="font-size: 0.9rem;">
                        <i class="fas fa-shopping-bag me-2"></i>Order Items
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="font-size: 0.8rem;">Product</th>
                                    <th style="font-size: 0.8rem;">Quantity</th>
                                    <th style="font-size: 0.8rem;">Unit Price</th>
                                    <th style="font-size: 0.8rem;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($item->product && $item->product->images && $item->product->images->count() > 0)
                                                    @php
                                                        $image = $item->product->images->first();
                                                        $imageUrl = asset('storage/' . $image->image_path) . '?v=' . time();
                                                    @endphp
                                                    <img src="{{ $imageUrl }}" 
                                                         alt="{{ $item->product_name ?? 'Product' }}" 
                                                         class="img-thumbnail me-3" 
                                                         style="width: 45px; height: 45px; object-fit: cover; border-radius: 0;"
                                                         onerror="this.onerror=null; this.src='{{ asset('images/default.svg') }}';">
                                                @else
                                                    <img src="{{ asset('images/default.svg') }}" 
                                                         alt="Default Product Image" 
                                                         class="img-thumbnail me-3" 
                                                         style="width: 45px; height: 45px; object-fit: cover; border-radius: 0;">
                                                @endif
                                                 <div>
                                                     @php
                                                         // Get product name from stored field or relationship
                                                         $productName = $item->product_name;
                                                         
                                                         // If product_name is empty, try to get from relationship
                                                         if (empty($productName) && $item->product) {
                                                             $productName = $item->product->name;
                                                         }
                                                         
                                                         // Fallback if still empty
                                                         if (empty($productName)) {
                                                             $productName = 'Product Not Found';
                                                         }
                                                     @endphp
                                                     <h6 class="mb-1 fw-bold" style="font-size: 0.85rem;">{{ $productName }}</h6>
                                                     <small class="text-muted" style="font-size: 0.7rem;">Product Item</small>
                                                     
                                                     <!-- Product Details -->
                                                     <div class="mt-2">
                                                         @if($item->product_barcode)
                                                             <small class="text-muted d-block" style="font-size: 0.65rem;">
                                                                 <i class="fas fa-barcode me-1"></i>
                                                                 <strong>Barcode:</strong> {{ $item->product_barcode }}
                                                             </small>
                                                         @endif
                                                         
                                                         @if($item->product && $item->product->description)
                                                             @php
                                                                 // Clean description to remove JSON objects
                                                                 $description = $item->product->description;
                                                                 // If it's a JSON string, extract the description
                                                                 if (is_string($description) && (strpos($description, '{') === 0 || strpos($description, '[') === 0)) {
                                                                     $decoded = json_decode($description, true);
                                                                     if (is_array($decoded) && isset($decoded['description'])) {
                                                                         $description = $decoded['description'];
                                                                     } elseif (is_array($decoded) && isset($decoded['name'])) {
                                                                         $description = $decoded['name'];
                                                                     }
                                                                 }
                                                             @endphp
                                                             <small class="text-muted d-block mt-1" style="font-size: 0.65rem;">
                                                                 <i class="fas fa-info-circle me-1"></i>
                                                                 <strong>Description:</strong> {{ $description }}
                                                             </small>
                                                         @endif
                                                     </div>
                                                    
                                                    @if($item->variants && count($item->variants) > 0)
                                                        <div class="mt-2">
                                                            <small class="text-muted fw-bold" style="font-size: 0.65rem;">Selected Options:</small>
                                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                                 @foreach($item->variants as $key => $value)
                                                                     @if($value)
                                                                         @php
                                                                             // Handle different value types
                                                                             if (is_array($value)) {
                                                                                 $displayValue = implode(', ', $value);
                                                                             } elseif (is_object($value)) {
                                                                                 // Handle object values - extract name or value property
                                                                                 if (isset($value->name)) {
                                                                                     $displayValue = $value->name;
                                                                                 } elseif (isset($value->value)) {
                                                                                     $displayValue = $value->value;
                                                                                 } elseif (isset($value->label)) {
                                                                                     $displayValue = $value->label;
                                                                                 } else {
                                                                                     $displayValue = (string) $value;
                                                                                 }
                                                                             } else {
                                                                                 $displayValue = (string) $value;
                                                                             }
                                                                         @endphp
                                                                         <span class="badge bg-primary" style="font-size: 0.6rem; border-radius: 0;">
                                                                             {{ ucfirst($key) }}: {{ $displayValue }}
                                                                         </span>
                                                                     @endif
                                                                 @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                    
                                                    @if($item->channel)
                                                        <div class="mt-1">
                                                            <span class="badge bg-{{ $item->channel === 'wholesale' ? 'info' : 'secondary' }}" style="font-size: 0.6rem; border-radius: 0;">
                                                                {{ ucfirst($item->channel) }} Customer
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary" style="font-size: 0.7rem; border-radius: 0;">{{ $item->quantity }}</span>
                                        </td>
                                        <td class="fw-bold" style="font-size: 0.8rem;">TZS {{ number_format($item->unit_price, 0) }}</td>
                                        <td class="fw-bold text-success" style="font-size: 0.8rem;">TZS {{ number_format($item->subtotal, 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-primary">
                                    <th colspan="3" class="text-end" style="font-size: 0.9rem;">Total:</th>
                                    <th class="text-success" style="font-size: 1rem;">TZS {{ number_format($order->total_amount, 0) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Information & Actions -->
        <div class="col-lg-4">
            <!-- Customer Information -->
            <div class="card mb-4">
                <div class="card-header" style="padding: 0.5rem 1rem;">
                    <h5 class="mb-0" style="font-size: 0.9rem;">
                        <i class="fas fa-user me-2"></i>Customer Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="user-avatar mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                            {{ substr($order->user->name ?? 'G', 0, 1) }}
                        </div>
                        <h5 class="fw-bold" style="font-size: 0.9rem;">{{ $order->user->name ?? 'Guest Customer' }}</h5>
                        <p class="text-muted" style="font-size: 0.8rem;">{{ $order->user->email ?? 'WhatsApp Order' }}</p>
                        @if($order->user->email === 'guest@chibobrand.com')
                            <span class="badge bg-info" style="font-size: 0.7rem;">Guest Customer</span>
                        @endif
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold text-muted" style="font-size: 0.8rem;">Phone</label>
                            <div>
                                @if($order->user->phone)
                                    <a href="tel:{{ $order->user->phone }}" class="text-decoration-none" style="font-size: 0.8rem;">
                                        <i class="fas fa-phone me-2"></i>{{ $order->user->phone }}
                                    </a>
                                @else
                                    <span class="text-muted" style="font-size: 0.8rem;">WhatsApp Order</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-muted" style="font-size: 0.8rem;">Customer Type</label>
                            <div>
                                @if($order->user->email === 'guest@chibobrand.com')
                                    <span class="badge bg-info" style="font-size: 0.7rem;">Guest Customer</span>
                                @else
                                    <span class="badge badge-{{ $order->user->role === 'wholesale_customer' ? 'info' : 'secondary' }}" style="font-size: 0.7rem;">
                                        {{ ucfirst(str_replace('_', ' ', $order->user->role ?? 'customer')) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-muted" style="font-size: 0.8rem;">Verification Status</label>
                            <div>
                                @if($order->user->email === 'guest@chibobrand.com')
                                    <span class="badge bg-warning" style="font-size: 0.7rem;">Guest Order</span>
                                @else
                                    <span class="badge badge-{{ $order->user->verified ? 'success' : 'warning' }}" style="font-size: 0.7rem;">
                                        {{ $order->user->verified ? 'Verified' : 'Unverified' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Templates -->
            @if($templates->count() > 0 && ($order->user && $order->user->phone))
            <div class="card mb-4">
                <div class="card-header" style="padding: 0.5rem 1rem;">
                    <h5 class="mb-0" style="font-size: 0.9rem;">
                        <i class="fas fa-comment-dots me-2"></i>Quick Templates
                    </h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">Send a pre-defined message to the customer.</p>
                    <div class="d-grid gap-2">
                        @foreach($templates as $template)
                            <form action="{{ route('admin.message-templates.send') }}" method="POST">
                                @csrf
                                <input type="hidden" name="template_id" value="{{ $template->id }}">
                                <input type="hidden" name="customer_id" value="{{ $order->user->customer_id ?? \App\Models\Customer::where('phone', $order->user->phone)->first()?->id }}">
                                <button type="submit" class="btn btn-outline-info btn-sm w-100 text-start d-flex justify-content-between align-items-center" data-no-global-handler>
                                    <span><i class="fas fa-paper-plane me-2 opacity-50"></i>{{ $template->title }}</span>
                                    <i class="fas fa-chevron-right x-small"></i>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Order Actions -->
            @if($order->approval_status !== 'cancelled' && $order->approval_status !== 'delivered')
                <div class="card mb-4 border-{{ $order->approval_status === 'requested' ? 'warning' : 'danger' }}">
                    <div class="card-header" style="padding: 0.5rem 1rem;">
                        <h5 class="mb-0" style="font-size: 0.9rem;">
                            <i class="fas fa-cogs me-2"></i>Order Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($order->approval_status === 'requested')
                            <form method="POST" action="{{ route('admin.orders.approve', $order->order_code) }}" class="mb-3" id="approveOrderForm">
                                @csrf
                                <div class="mb-3">
                                    <label for="admin_notes" class="form-label fw-bold">Admin Notes (Optional)</label>
                                    <textarea name="admin_notes" id="admin_notes" class="form-control" rows="3" 
                                              placeholder="Add notes for this approval..."></textarea>
                                </div>
                                <button type="button" class="btn btn-success w-100 d-flex align-items-center justify-content-center" 
                                        onclick="modernConfirm('Approve this order? This will reduce product stock.', () => document.getElementById('approveOrderForm').submit(), { title: 'Approve Order', type: 'success', icon: 'fa-check-circle', confirmText: 'Approve Order' })">
                                    <i class="fas fa-check me-md-2"></i>
                                    <span class="d-none d-md-inline">Approve Order</span>
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.orders.cancel', $order->order_code) }}" class="mb-3" id="cancelOrderForm">
                            @csrf
                            <div class="mb-3">
                                <label for="cancellation_reason" class="form-label fw-bold">Cancellation Reason</label>
                                <textarea name="cancellation_reason" id="cancellation_reason" class="form-control" rows="2" 
                                          placeholder="Reason for cancellation..." required></textarea>
                            </div>
                            <button type="button" class="btn btn-danger w-100 d-flex align-items-center justify-content-center" 
                                    onclick="modernConfirm('Cancel this order? This will restore stock if order was approved.', () => document.getElementById('cancelOrderForm').submit(), { title: 'Cancel Order', type: 'danger', icon: 'fa-times-circle', confirmText: 'Cancel Order' })">
                                <i class="fas fa-times me-md-2"></i>
                                <span class="d-none d-md-inline">Cancel Order</span>
                            </button>
                        </form>

                        @if($order->approval_status === 'requested')
                            <a href="{{ route('admin.orders.edit', $order->order_code) }}" class="btn btn-warning w-100 d-flex align-items-center justify-content-center">
                                <i class="fas fa-edit me-md-2"></i>
                                <span class="d-none d-md-inline">Edit Quantities</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- WhatsApp Messages -->
            @if($order->whatsappRequests->count() > 0)
                <div class="card">
                    <div class="card-header" style="padding: 0.5rem 1rem;">
                        <h5 class="mb-0" style="font-size: 0.9rem;">
                            <i class="fab fa-whatsapp me-2 text-success"></i>WhatsApp Messages
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($order->whatsappRequests as $request)
                            <div class="mb-3 p-3 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>{{ $request->sent_at->format('M j, Y H:i') }}
                                    </small>
                                    <span class="badge bg-success">Sent</span>
                                </div>
                                <p class="mb-0 small">{{ Str::limit($request->message_sent, 100) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add animation to cards
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Add hover effects to action buttons
    const actionButtons = document.querySelectorAll('.btn');
    actionButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>

<style>
/* Mobile Responsiveness */
@media (max-width: 768px) {
    .container-fluid {
        padding: 0.25rem;
    }
    
    .mb-4 {
        margin-bottom: 0.75rem !important;
    }
    
    .card {
        margin-bottom: 0.75rem;
        border-radius: 8px;
    }
    
    .card-header {
        padding: 0.3rem 0.75rem !important;
    }
    
    .card-header h5 {
        font-size: 0.8rem !important;
    }
    
    .card-body {
        padding: 0.75rem !important;
    }
    
    .h3 {
        font-size: 1.2rem !important;
    }
    
    .text-muted {
        font-size: 0.8rem !important;
    }
    
    .table th,
    .table td {
        padding: 0.3rem;
        font-size: 0.7rem;
    }
    
    .btn {
        padding: 0.4rem 0.8rem;
        font-size: 0.75rem;
        min-height: 36px;
    }
    
    .form-label {
        font-size: 0.7rem !important;
    }
    
    .badge {
        font-size: 0.6rem !important;
        padding: 0.2rem 0.4rem !important;
    }
    
    .user-avatar {
        width: 50px !important;
        height: 50px !important;
        font-size: 1.2rem !important;
    }
    
    .d-flex.gap-2 {
        flex-direction: column;
        gap: 0.5rem !important;
    }
    
    .d-flex.gap-2 .btn {
        width: 100%;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding: 0.15rem;
    }
    
    .mb-4 {
        margin-bottom: 0.5rem !important;
    }
    
    .card {
        margin-bottom: 0.5rem;
        border-radius: 6px;
    }
    
    .card-header {
        padding: 0.25rem 0.5rem !important;
    }
    
    .card-header h5 {
        font-size: 0.75rem !important;
    }
    
    .card-body {
        padding: 0.5rem !important;
    }
    
    .h3 {
        font-size: 1rem !important;
    }
    
    .text-muted {
        font-size: 0.75rem !important;
    }
    
    .table th,
    .table td {
        padding: 0.2rem;
        font-size: 0.65rem;
    }
    
    .btn {
        padding: 0.3rem 0.6rem;
        font-size: 0.7rem;
        min-height: 32px;
    }
    
    .form-label {
        font-size: 0.65rem !important;
    }
    
    .badge {
        font-size: 0.55rem !important;
        padding: 0.15rem 0.3rem !important;
    }
    
    .user-avatar {
        width: 40px !important;
        height: 40px !important;
        font-size: 1rem !important;
    }
    
    .d-flex.gap-2 {
        gap: 0.4rem !important;
    }
    
    .row.g-3 {
        --bs-gutter-y: 0.5rem;
    }
}
</style>
@endsection