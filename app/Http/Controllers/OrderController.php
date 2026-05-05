<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Admin;
use App\Notifications\OrderRequestedAdmin;
use App\Notifications\OrderApproved;
use App\Notifications\OrderCancelled;
use App\Notifications\LowStockAlert;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;

class OrderController extends Controller
{
    /**
     * Display a listing of orders for admin.
     */
    public function index(Request $request): View
    {
        // Restrict access for designer (receptionist can now see orders)
        $user = Auth::user();
        if (in_array($user->role ?? '', ['designer'])) {
            abort(403, 'You do not have permission to view orders.');
        }
        
        $query = Order::with(['user', 'items.product']);

        // If a saler is logged in (admin area), restrict orders to those assigned to their WhatsApp number
        // If a saler is logged in (admin area), restrict orders to those assigned to them OR unassigned
        if (Auth::check() && in_array(Auth::user()->role, ['saler'])) {
            $query->where(function($q) {
                $q->where('saler_id', Auth::id())
                  ->orWhereNull('saler_id');
            });
        }

        // Create a base query for stats that respects role restrictions
        $statsQuery = Order::query();
        if (Auth::check() && in_array(Auth::user()->role, ['saler'])) {
            $statsQuery->where(function($q) {
                $q->where('saler_id', Auth::id())
                  ->orWhereNull('saler_id');
            });
        }
        
        $stats = [
            'total' => $statsQuery->clone()->count(),
            'pending' => $statsQuery->clone()->where('approval_status', 'requested')->count(),
            'approved' => $statsQuery->clone()->where('approval_status', 'approved')->count(),
            'cancelled' => $statsQuery->clone()->where('approval_status', 'cancelled')->count(),
            'paid' => $statsQuery->clone()->where('payment_status', 'paid')->count(),
        ];

        // Filter by approval status
        if ($request->filled('status')) {
            $query->where('approval_status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by order type (proforma, sales_invoice)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Amount range filter
        if ($request->filled('amount_min')) {
            $query->where('total_amount', '>=', $request->amount_min);
        }
        if ($request->filled('amount_max')) {
            $query->where('total_amount', '<=', $request->amount_max);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%")
                                   ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Get message templates for the send message dropdown
        $templates = \App\Models\MessageTemplate::where('is_active', true)->get();

        // Debug logging for admin orders
        Log::info('Admin orders query results', [
            'total_orders' => $orders->total(),
            'orders_on_page' => $orders->count(),
            'current_page' => $orders->currentPage(),
            'first_order' => $orders->first() ? $orders->first()->order_code : 'none',
            'last_order' => $orders->last() ? $orders->last()->order_code : 'none',
            'guest_orders_count' => $orders->where('user.email', 'guest@chibobrand.com')->count(),
            'recent_orders' => $orders->take(5)->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'user_email' => $order->user ? $order->user->email : 'no user',
                    'is_guest' => $order->user && $order->user->email === 'guest@chibobrand.com',
                    'created_at' => $order->created_at
                ];
            })->toArray()
        ]);

        return view('admin.orders.index', compact('orders', 'templates', 'stats'));
    }

    /**
     * Display a listing of customer orders.
     */
    public function customerOrders(): View
    {
        $customer = Auth::guard('customer')->user();
        
        // Find or create corresponding user record for the customer
        $user = \App\Models\User::firstOrCreate(
            ['email' => $customer->email ?: $customer->phone . '@chibobrand.com'],
            [
                'name' => $customer->name,
                'email' => $customer->email ?: $customer->phone . '@chibobrand.com',
                'phone' => $customer->phone,
                'password' => bcrypt('password'), // Default password
                'role' => $customer->is_wholesale ? 'wholesale_customer' : 'retail_customer',
                'verified' => $customer->verified,
                'is_active' => $customer->is_active,
            ]
        );
        
        $orders = Order::with(['items.product'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10)->withQueryString();

        return view('customer.orders.index', compact('orders'));
    }

    /**
     * Display the specified order for admin.
     */
    public function show(Order $order): View
    {
        // Restrict access for designer (receptionist can now see orders)
        $user = Auth::user();
        if (in_array($user->role ?? '', ['designer'])) {
            abort(403, 'You do not have permission to view orders.');
        }
        
        $order->load(['user', 'items.product']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Display the specified order for customer.
     */
    public function customerShow(Order $order): View
    {
        $customer = Auth::guard('customer')->user();
        
        // Find or create corresponding user record for the customer
        $user = \App\Models\User::firstOrCreate(
            ['email' => $customer->email ?: $customer->phone . '@chibobrand.com'],
            [
                'name' => $customer->name,
                'email' => $customer->email ?: $customer->phone . '@chibobrand.com',
                'phone' => $customer->phone,
                'password' => bcrypt('password'), // Default password
                'role' => $customer->is_wholesale ? 'wholesale_customer' : 'retail_customer',
                'verified' => $customer->verified,
                'is_active' => $customer->is_active,
            ]
        );
        
        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== $user->id) {
            abort(403, 'Unauthorized access to order.');
        }

        $order->load(['items.product']);

        return view('customer.orders.show', compact('order'));
    }

    /**
     * Store a newly created order from WhatsApp checkout.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'shipping_address' => 'required|string',
            'billing_address' => 'required|string',
            'payment_method' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'domain' => 'required|string|in:chibobrand.com,b2b.chibobrand.com',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $customer = Auth::guard('customer')->user();
        
        // Find or create corresponding user record for the customer
        $user = \App\Models\User::firstOrCreate(
            ['email' => $customer->email ?: $customer->phone . '@chibobrand.com'],
            [
                'name' => $customer->name,
                'email' => $customer->email ?: $customer->phone . '@chibobrand.com',
                'phone' => $customer->phone,
                'password' => bcrypt('password'), // Default password
                'role' => $customer->is_wholesale ? 'wholesale_customer' : 'retail_customer',
                'verified' => $customer->verified,
                'is_active' => $customer->is_active,
            ]
        );

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => $this->generateOrderNumber(),
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'shipping_address' => $request->shipping_address,
            'billing_address' => $request->billing_address,
            'payment_method' => $request->payment_method,
            'subtotal' => $request->total_amount,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'total_amount' => $request->total_amount,
            'status' => 'pending',
            'notes' => "Order placed via WhatsApp from {$request->domain}",
        ]);

        foreach ($request->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'subtotal' => $item['quantity'] * $item['price'],
                'product_variations' => $item['variations'] ?? null,
                'product_addons' => $item['addons'] ?? null,
                'custom_inputs' => $item['custom_inputs'] ?? null,
            ]);
        }

        // Send notification to admin about new order
        $admins = Admin::all();
        foreach ($admins as $admin) {
            $admin->notify(new OrderRequestedAdmin($order));
        }

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'message' => 'Order placed successfully! We will contact you via WhatsApp to confirm.'
        ]);
    }

    /**
     * Update the specified order status (admin only).
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        // Restrict access for designer
        $user = Auth::user();
        if (in_array($user->role ?? '', ['designer'])) {
            abort(403, 'You do not have permission to update orders.');
        }
        
        $request->validate([
            'status' => 'required|in:pending,approved,cancelled,processing,shipped,delivered',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $order->status;
        $order->status = $request->status;

        // Update admin notes if provided
        if ($request->filled('admin_notes')) {
            $order->admin_notes = $request->admin_notes;
        }

        // Update timestamps based on status
        if ($request->status === 'approved' && $oldStatus === 'pending') {
            $order->approved_at = now();
        } elseif ($request->status === 'shipped' && $oldStatus !== 'shipped') {
            $order->shipped_at = now();
            
            // Send Completion/Ready SMS
            try {
                if ($order->user && $order->user->phone) {
                    $smsService = app(\App\Services\SmsApiService::class);
                    $smsService->sendOrderCompletion($order);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send order completion SMS', ['error' => $e->getMessage()]);
            }
        } elseif ($request->status === 'delivered' && $oldStatus !== 'delivered') {
            $order->delivered_at = now();
        }

        $order->save();

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order status updated successfully.');
    }

    /**
     * Approve an order (admin only).
     */
    public function approve(Order $order): RedirectResponse
    {
        // Restrict access for designer
        $user = Auth::user();
        if (in_array($user->role ?? '', ['designer'])) {
            abort(403, 'You do not have permission to approve orders.');
        }
        
        if ($order->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending orders can be approved.');
        }

        // Check stock availability and reduce stock
        $lowStockProducts = [];
        foreach ($order->items as $item) {
            $product = $item->product;
            if ($product->track_stock) {
                if (!$product->reduceStock($item->quantity)) {
                    return redirect()->back()->with('error', 
                        "Insufficient stock for product: {$product->name}. Available: {$product->stock_quantity}, Required: {$item->quantity}");
                }
                
                // Check for low stock alert after reducing stock
                if ($product->stock_quantity <= $product->low_stock_threshold) {
                    $lowStockProducts[] = $product->name;
                    \Log::warning("Low stock alert for product: {$product->name}. Current stock: {$product->stock_quantity}");
                }
            }
        }

        $order->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Send notification to customer about order approval
        $order->user->notify(new OrderApproved($order));

        // Send low stock alerts to admin if any
        foreach ($order->items as $item) {
            $product = $item->product;
            if ($product->track_stock && $product->stock_quantity <= $product->low_stock_threshold) {
                $admins = Admin::all();
                foreach ($admins as $admin) {
                    $admin->notify(new LowStockAlert($product));
                }
            }
        }

        $message = 'Order approved successfully and stock updated.';
        if (!empty($lowStockProducts)) {
            $message .= ' Low stock alert for: ' . implode(', ', $lowStockProducts);
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('success', $message);
    }

    /**
     * Cancel an order (admin only).
     */
    public function cancel(Order $order): RedirectResponse
    {
        // Restrict access for designer
        $user = Auth::user();
        if (in_array($user->role ?? '', ['designer'])) {
            abort(403, 'You do not have permission to cancel orders.');
        }
        
        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return redirect()->back()->with('error', 'This order cannot be cancelled.');
        }

        // If order was approved, restore stock
        if ($order->status === 'approved') {
            foreach ($order->items as $item) {
                $product = $item->product;
                if ($product->track_stock) {
                    $product->increaseStock($item->quantity);
                }
            }
        }

        $order->update([
            'status' => 'cancelled',
        ]);

        // Trigger Customer Analytics Recalculation on cancellation
        try {
            if ($order->user) {
                $customer = \App\Models\Customer::where('phone', $order->user->phone)
                    ->orWhere('email', $order->user->email)
                    ->first();
                
                if ($customer) {
                    $analyticsService = app(\App\Services\CustomerAnalyticsService::class);
                    $analyticsService->recalculateCustomerAnalytics($customer->id);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to update customer analytics on order cancellation', ['error' => $e->getMessage()]);
        }

        // Send notification to customer about order cancellation
        $order->user->notify(new OrderCancelled($order));

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order cancelled successfully and stock restored.');
    }

    /**
     * Get daily order summary for admin dashboard.
     */
    public function dailySummary(Request $request): View
    {
        // Restrict access for designer
        $user = Auth::user();
        if (in_array($user->role ?? '', ['designer'])) {
            abort(403, 'You do not have permission to view order summaries.');
        }
        
        $date = $request->get('date', now()->format('Y-m-d'));
        
        $orders = Order::whereDate('created_at', $date)
            ->with(['user', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'date' => $date,
            'total_requests' => $orders->count(),
            'approved' => $orders->where('status', 'approved')->count(),
            'cancelled' => $orders->where('status', 'cancelled')->count(),
            'pending' => $orders->where('status', 'pending')->count(),
            'processing' => $orders->where('status', 'processing')->count(),
            'shipped' => $orders->where('status', 'shipped')->count(),
            'delivered' => $orders->where('status', 'delivered')->count(),
        ];

        return view('admin.orders.daily-summary', compact('orders', 'summary'));
    }

    /**
     * Create order from cart data (API endpoint).
     */
    /**
     * Clean product name to remove any JSON objects or unwanted data
     */
    private function cleanProductName($productName)
    {
        if (empty($productName)) {
            return 'Unknown Product';
        }
        
        // If it's a JSON string, try to extract the name
        if (is_string($productName) && (strpos($productName, '{') === 0 || strpos($productName, '[') === 0)) {
            $decoded = json_decode($productName, true);
            if (is_array($decoded) && isset($decoded['name'])) {
                return $decoded['name'];
            }
        }
        
        // If it's an array or object, extract the name
        if (is_array($productName) && isset($productName['name'])) {
            return $productName['name'];
        }
        
        if (is_object($productName) && isset($productName->name)) {
            return $productName->name;
        }
        
        // Return as string, but limit length
        return substr((string) $productName, 0, 255);
    }

    public function createFromCart(Request $request)
    {
        try {
            // Debug: Log the incoming request
            \Log::info('Order creation request received:', $request->all());
            
            $request->validate([
                'cart' => 'required|array|min:1',
                'cart.*.product_id' => 'required',
                'cart.*.quantity' => 'required|integer|min:1',
                'cart.*.variations' => 'nullable|array',
                'cart.*.addons' => 'nullable|array',
                'cart.*.custom_inputs' => 'nullable|array',
            ]);

            // Check if there's a logged-in customer, otherwise create guest customer
            $user = null;
            $isCustomerLoggedIn = Auth::guard('customer')->check();
            Log::info('Order creation auth check', [
                'is_customer_logged_in' => $isCustomerLoggedIn,
                'customer_id' => $isCustomerLoggedIn ? Auth::guard('customer')->id() : null,
                'customer_name' => $isCustomerLoggedIn ? Auth::guard('customer')->user()->name : null,
                'customer_email' => $isCustomerLoggedIn ? Auth::guard('customer')->user()->email : null
            ]);
            
            if ($isCustomerLoggedIn) {
                $customer = Auth::guard('customer')->user();
                // Find or create corresponding user record for the customer
                $user = \App\Models\User::firstOrCreate(
                    ['email' => $customer->email ?: $customer->phone . '@chibobrand.com'],
                    [
                        'name' => $customer->name,
                        'email' => $customer->email ?: $customer->phone . '@chibobrand.com',
                        'phone' => $customer->phone,
                        'password' => bcrypt('password'), // Default password
                        'role' => $customer->is_wholesale ? 'wholesale_customer' : 'retail_customer',
                        'verified' => $customer->verified,
                        'is_active' => $customer->is_active,
                    ]
                );
                Log::info('Order created for logged-in customer', [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'user_id' => $user->id
                ]);
            } else {
                // Create or find guest customer
                $user = \App\Models\User::firstOrCreate(
                    ['email' => 'guest@chibobrand.com'],
                    [
                        'name' => 'Guest Customer',
                        'phone' => 'WhatsApp Order',
                        'email' => 'guest@chibobrand.com',
                        'password' => bcrypt('guest'),
                        'email_verified_at' => now(),
                        'is_verified' => true,
                        'user_type' => 'customer'
                    ]
                );
                Log::info('Order created for guest customer', [
                    'user_id' => $user->id,
                    'user_email' => $user->email
                ]);
            }

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_code' => $request->order_code,
                'total_amount' => $request->total_amount,
                'payment_status' => $request->payment_status,
                'approval_status' => $request->approval_status,
                'notes' => $request->notes,
            ]);

                // Create order items
                foreach ($request->items as $itemData) {
                    // Find product by barcode
                    $product = \App\Models\EnhancedProduct::where('barcode', $itemData['product_barcode'])->first();
                    
                    if ($product) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'quantity' => $itemData['quantity'],
                            'unit_price' => $itemData['unit_price'],
                            'subtotal' => $itemData['subtotal'],
                            'variants' => $itemData['variants'] ?? [],
                            'product_barcode' => $itemData['product_barcode'],
                            'product_name' => $this->cleanProductName($itemData['product_name']),
                            'channel' => $itemData['channel'],
                        ]);
                    } else {
                        // Create a placeholder order item if product not found
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => null,
                            'quantity' => $itemData['quantity'],
                            'unit_price' => $itemData['unit_price'],
                            'subtotal' => $itemData['subtotal'],
                            'variants' => $itemData['variants'] ?? [],
                            'product_barcode' => $itemData['product_barcode'],
                            'product_name' => $this->cleanProductName($itemData['product_name']),
                            'channel' => $itemData['channel'],
                        ]);
                    }
                }

            // Create WhatsApp request record
            \App\Models\WhatsappRequest::create([
                'order_id' => $order->id,
                'customer_phone' => 'WhatsApp Order',
                'message_sent' => 'Order created from cart',
                'sent_at' => now(),
            ]);

            // Send notification to admin about new order
            $admins = \App\Models\Admin::all();
            foreach ($admins as $admin) {
                $admin->notify(new OrderRequestedAdmin($order));
            }

            // Send SMS Receipt to Customer
            try {
                if ($order->user && $order->user->phone) {
                    $smsService = app(\App\Services\SmsApiService::class);
                    $smsService->sendOrderReceipt($order);
                    Log::info('Order receipt SMS sent (createFromCart)', ['order_code' => $order->order_code]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send order receipt SMS (createFromCart)', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'message' => 'Order created successfully',
                'clear_cart' => true
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error creating order from cart:', $e->errors());
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating order from cart: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a unique order number.
     */
    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'CHB-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Create order from cart data (for WhatsApp orders)
     */
    public function createFromCartData(Request $request)
    {
        try {
            // Debug: Log the incoming request
            \Log::info('Order creation request received:', $request->all());
            
            $request->validate([
                'cart' => 'required|array|min:1',
                'cart.*.product_id' => 'required',
                'cart.*.quantity' => 'required|integer|min:1',
                'cart.*.variations' => 'nullable|array',
                'cart.*.addons' => 'nullable|array',
                'cart.*.custom_inputs' => 'nullable|array',
            ]);

            // Process cart items and calculate prices
            $cartData = $request->input('cart', []);
            // Check if wholesale is specified in request, otherwise check URL pattern
            $isWholesaleInput = $request->input('is_wholesale', false);
            // Handle both boolean and string values
            $isWholesale = filter_var($isWholesaleInput, FILTER_VALIDATE_BOOLEAN) 
                        || request()->is('b2b/*') 
                        || request()->is('b2b');
            $customerType = $isWholesale ? 'wholesale' : 'retail';
            
            \Log::info('Store type determination', [
                'is_wholesale_input' => $isWholesaleInput,
                'is_wholesale_input_type' => gettype($isWholesaleInput),
                'is_wholesale_final' => $isWholesale,
                'url_pattern_check' => request()->is('b2b/*') || request()->is('b2b'),
                'customer_type' => $customerType
            ]);
            $totalAmount = 0;
            $processedItems = [];
            
            Log::info('Processing cart for order creation', [
                'cart_count' => count($cartData),
                'is_wholesale' => $isWholesale,
                'customer_type' => $customerType
            ]);
            
            foreach ($cartData as $item) {
                // Find product by ID or barcode
                $product = null;
                $identifier = $item['product_id'] ?? null;
                $identifierStr = is_null($identifier) ? null : (string) $identifier;

                // 1) If numeric, try enhanced_products.id
                if ($identifier !== null && is_numeric($identifierStr)) {
                    $product = \App\Models\EnhancedProduct::find((int) $identifierStr);
                }

                // 2) Try barcode (most common, stored in cart)
                if (!$product && $identifierStr) {
                    $product = \App\Models\EnhancedProduct::where('barcode', $identifierStr)->first();
                }

                // 3) Try enhanced_products.product_id (PID-... legacy string)
                if (!$product && $identifierStr) {
                    $product = \App\Models\EnhancedProduct::where('product_id', $identifierStr)->first();
                }
                
                if ($product) {
                    // Get the appropriate price
                    $price = $product->getPriceForQuantity($item['quantity'], $customerType);
                    if (!$price) {
                        $price = $product->getBasePriceForChannel($customerType);
                    }
                    
                    $subtotal = $price * $item['quantity'];
                    $totalAmount += $subtotal;
                    
                    $processedItems[] = [
                        'product' => $product,
                        'quantity' => $item['quantity'],
                        'price' => $price,
                        'subtotal' => $subtotal,
                        'variations' => $item['variations'] ?? [],
                        'addons' => $item['addons'] ?? [],
                        'custom_inputs' => $item['custom_inputs'] ?? []
                    ];
                    
                    Log::info('Processed cart item', [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $item['quantity'],
                        'price' => $price,
                        'subtotal' => $subtotal
                    ]);
                } else {
                    Log::warning('Product not found for cart item', [
                        'product_id' => $item['product_id']
                    ]);
                }
            }
            
            if (empty($processedItems)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid products found in cart'
                ], 400);
            }

            // Check if there's a logged-in customer, otherwise create guest customer
            $user = null;
            $isCustomerLoggedIn = Auth::guard('customer')->check();
            Log::info('Order creation auth check', [
                'is_customer_logged_in' => $isCustomerLoggedIn,
                'customer_id' => $isCustomerLoggedIn ? Auth::guard('customer')->id() : null,
                'customer_name' => $isCustomerLoggedIn ? Auth::guard('customer')->user()->name : null,
                'customer_email' => $isCustomerLoggedIn ? Auth::guard('customer')->user()->email : null
            ]);
            
            if ($isCustomerLoggedIn) {
                $customer = Auth::guard('customer')->user();
                // Find or create corresponding user record for the customer
                $user = \App\Models\User::firstOrCreate(
                    ['email' => $customer->email ?: $customer->phone . '@chibobrand.com'],
                    [
                        'name' => $customer->name,
                        'email' => $customer->email ?: $customer->phone . '@chibobrand.com',
                        'phone' => $customer->phone,
                        'password' => bcrypt('password'), // Default password
                        'role' => $customer->is_wholesale ? 'wholesale_customer' : 'retail_customer',
                        'verified' => $customer->verified,
                        'is_active' => $customer->is_active,
                    ]
                );
                Log::info('Order created for logged-in customer', [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'user_id' => $user->id
                ]);
            } else {
                // Create or find guest customer
                $user = \App\Models\User::firstOrCreate(
                    ['email' => 'guest@chibobrand.com'],
                    [
                        'name' => 'Guest Customer',
                        'phone' => 'WhatsApp Order',
                        'email' => 'guest@chibobrand.com',
                        'password' => bcrypt('guest'),
                        'email_verified_at' => now(),
                        'is_verified' => true,
                        'user_type' => 'customer'
                    ]
                );
                Log::info('Order created for guest customer', [
                    'user_id' => $user->id,
                    'user_email' => $user->email
                ]);
            }

            // Generate order code
            $orderCode = 'CHB-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Process saler phone if present to find saler_id
            $salerPhoneRaw = $request->input('saler_phone')
                        ?? $request->header('X-Saler-Phone') 
                        ?? $request->header('x-saler-phone')
                        ?? ($_SERVER['HTTP_X_SALER_PHONE'] ?? null)
                        ?? null;
            
            $salerId = null;
            $salerNotesMatch = "";
            
            if ($salerPhoneRaw && $salerPhoneRaw !== '' && $salerPhoneRaw !== 'null') {
                $p = preg_replace('/[^\d\+]/', '', $salerPhoneRaw);
                $p = ltrim($p, '+');
                
                if (!empty($p) && strlen($p) >= 5) {
                    // Try to find the saler in the users table
                    $saler = \App\Models\User::where('phone', 'like', "%{$p}%")
                                ->where('role', 'saler')
                                ->first();
                    
                    if ($saler) {
                        $salerId = $saler->id;
                        $salerNotesMatch = " | Assigned to saler: +" . $p;
                    }
                }
            }

            // Create order
            $orderData = [
                'user_id' => $user->id,
                'order_code' => $orderCode,
                'total_amount' => $totalAmount,
                'payment_status' => 'pending',
                'approval_status' => 'requested',
            ];

            if (Schema::hasColumn('orders', 'saler_id')) {
                $orderData['saler_id'] = $salerId;
            }

            if (Schema::hasColumn('orders', 'notes')) {
                $orderData['notes'] = "Order placed via WhatsApp from " . ($isWholesale ? 'B2B' : 'RETAIL') . " store" . $salerNotesMatch;
            }

            $order = Order::create($orderData);

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'total_amount' => $totalAmount,
                'user_id' => $user->id
            ]);

            // Create order items
            foreach ($processedItems as $item) {
                    // `order_items.product_id` foreign key now points to `enhanced_products.id`
                    $productsId = $item['product']->id;

                $orderItemData = [
                    'order_id' => $order->id,
                    'product_id' => $productsId,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ];

                // Add optional columns only if they exist in the current DB schema.
                if (Schema::hasColumn('order_items', 'product_name')) {
                    $orderItemData['product_name'] = $item['product']->name;
                }
                if (Schema::hasColumn('order_items', 'product_barcode')) {
                    $orderItemData['product_barcode'] = $item['product']->barcode;
                }
                if (Schema::hasColumn('order_items', 'product_variations')) {
                    $orderItemData['product_variations'] = $item['variations'] ?? [];
                }
                if (Schema::hasColumn('order_items', 'product_addons')) {
                    $orderItemData['product_addons'] = $item['addons'] ?? [];
                }
                if (Schema::hasColumn('order_items', 'custom_inputs')) {
                    $orderItemData['custom_inputs'] = $item['custom_inputs'] ?? [];
                }
                if (Schema::hasColumn('order_items', 'channel')) {
                    $orderItemData['channel'] = $customerType;
                }

                OrderItem::create($orderItemData);
                
                Log::info('Order item created', [
                    'order_item_id' => OrderItem::latest()->first()->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['subtotal']
                ]);
            }

            // Create WhatsApp request record
            \App\Models\WhatsappRequest::create([
                'order_id' => $order->id,
                'customer_phone' => 'WhatsApp Order',
                'message_sent' => 'Order created from cart',
                'sent_at' => now(),
            ]);

            // Send notification to admin about new order
            $admins = \App\Models\Admin::all();
            foreach ($admins as $admin) {
                $admin->notify(new OrderRequestedAdmin($order));
            }

            // Send SMS Receipt to Customer
            try {
                if ($order->user && $order->user->phone) {
                    $smsService = app(\App\Services\SmsApiService::class);
                    $smsService->sendOrderReceipt($order);
                    Log::info('Order receipt SMS sent', ['order_code' => $order->order_code]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send order receipt SMS', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'total_amount' => $totalAmount,
                'message' => 'Order created successfully',
                'clear_cart' => true
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error creating order from cart:', $e->errors());
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating order from cart: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the order. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
