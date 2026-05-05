<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ProductAddon;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Get cart from session.
     */
    private function getCart(): array
    {
        return Session::get('cart', []);
    }

    /**
     * Sync cart from localStorage to session.
     */
    public function sync(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'cart' => 'required|array',
                'cart.*.product_id' => 'required',
                'cart.*.quantity' => 'required|integer|min:1',
                'cart.*.variations' => 'nullable|array',
                'cart.*.addons' => 'nullable|array',
                'cart.*.custom_inputs' => 'nullable|array',
            ]);

            $cartData = $request->input('cart', []);
            
            // Convert to the format expected by the checkout process
            $cart = [];
            foreach ($cartData as $item) {
                Log::info('Processing cart item for sync', [
                    'item' => $item,
                    'product_id' => $item['product_id'],
                    'is_numeric' => is_numeric($item['product_id'])
                ]);
                
                // Try to find the product by ID or barcode
                $product = null;
                if (is_numeric($item['product_id'])) {
                    $product = \App\Models\EnhancedProduct::find($item['product_id']);
                    Log::info('Found product by ID', [
                        'product_id' => $item['product_id'],
                        'product_found' => $product ? 'yes' : 'no',
                        'product_name' => $product ? $product->name : 'not found'
                    ]);
                } else {
                    $product = \App\Models\EnhancedProduct::where('barcode', $item['product_id'])->first();
                    Log::info('Found product by barcode', [
                        'barcode' => $item['product_id'],
                        'product_found' => $product ? 'yes' : 'no',
                        'product_name' => $product ? $product->name : 'not found'
                    ]);
                }
                
                // Determine customer type based on URL context
                $isWholesale = request()->is('b2b/*') || request()->is('b2b');
                $customerType = $isWholesale ? 'wholesale' : 'retail';
                
                Log::info('Customer type determination', [
                    'is_wholesale' => $isWholesale,
                    'customer_type' => $customerType,
                    'url' => request()->fullUrl()
                ]);
                
                // Get the appropriate price
                $price = 0;
                if ($product) {
                    Log::info('Product found, getting price', [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'retail_base_price' => $product->retail_base_price,
                        'b2b_base_price' => $product->b2b_base_price,
                        'buying_price' => $product->buying_price,
                        'quantity' => $item['quantity']
                    ]);
                    
                    // Try to get price from price tiers first
                    $price = $product->getPriceForQuantity($item['quantity'], $customerType);
                    Log::info('Price from tiers', ['price' => $price]);
                    
                    // Fallback to base price if no tier found
                    if (!$price) {
                        $price = $product->getBasePriceForChannel($customerType);
                        Log::info('Price from base channel', ['price' => $price]);
                    }
                } else {
                    Log::warning('Product not found for cart item', [
                        'product_id' => $item['product_id'],
                        'is_numeric' => is_numeric($item['product_id'])
                    ]);
                }
                
                Log::info('Final price calculation', [
                    'product_id' => $item['product_id'],
                    'final_price' => $price,
                    'customer_type' => $customerType
                ]);
                
                $cart[] = [
                    'id' => 'sync-' . $item['product_id'] . '-' . time(),
                    'product_id' => $product ? $product->id : $item['product_id'],
                    'name' => $product ? $product->name : 'Synced Product',
                    'price' => $price,
                    'quantity' => $item['quantity'],
                    'image' => $product ? $product->images()->first()?->image_path : null,
                    'variations' => $item['variations'] ?? [],
                    'addons' => $item['addons'] ?? [],
                    'custom_inputs' => $item['custom_inputs'] ?? []
                ];
            }

            // Save to session
            $this->saveCart($cart);

            Log::info('Cart synced from localStorage', [
                'cart_count' => count($cart),
                'session_id' => session()->getId()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cart synced successfully',
                'cart_count' => count($cart)
            ]);

        } catch (\Exception $e) {
            Log::error('Cart sync failed: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync cart: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save cart to session.
     */
    private function saveCart(array $cart): void
    {
        Session::put('cart', $cart);
    }

    /**
     * Calculate cart totals.
     */
    private function calculateCartTotals(array $cart, bool $includeVAT = false): array
    {
        $subtotal = 0;
        $itemCount = 0;

        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
            $itemCount += $item['quantity'];
        }

        $vat = 0;
        if ($includeVAT) {
            $vat = round($subtotal * 0.18);
        }
        
        $shipping = 0; // No shipping for now
        $total = $subtotal + $vat + $shipping;

        return [
            'subtotal' => $subtotal,
            'vat' => $vat,
            'shipping' => $shipping,
            'total' => $total,
            'item_count' => $itemCount,
        ];
    }

    /**
     * Generate unique order number.
     */
    private function generateOrderNumber(): string
    {
        return 'CHB-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Display the cart page.
     */
    public function index(Request $request): View
    {
        $cart = $this->getCart();
        $includeVAT = $request->boolean('vat_receipt', false);
        $totals = $this->calculateCartTotals($cart, $includeVAT);

        return view('public.cart', compact('cart', 'totals'));
    }

    /**
     * Display the wholesale cart page.
     */
    public function wholesaleIndex(): View
    {
        $cart = $this->getCart();
        $totals = $this->calculateCartTotals($cart);

        return view('public.wholesale-cart', compact('cart', 'totals'));
    }

    /**
     * Add item to cart via AJAX.
     */
    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:100',
            'variations' => 'nullable|array',
            'addons' => 'nullable|array',
            'custom_inputs' => 'nullable|array',
        ]);

        $product = Product::with(['images', 'variations', 'addons', 'customInputs'])->findOrFail($request->product_id);

        // Calculate price
        $price = $product->base_price;
        $variationDetails = [];
        $addonDetails = [];

        // Add variation prices
        if ($request->has('variations')) {
            foreach ($request->variations as $variationId) {
                $variation = ProductVariation::find($variationId);
                if ($variation) {
                    $price += $variation->extra_price;
                    $variationDetails[] = [
                        'id' => $variation->id,
                        'name' => $variation->name,
                        'option_value' => $variation->option_value,
                        'extra_price' => $variation->extra_price,
                    ];
                }
            }
        }

        // Add addon prices
        if ($request->has('addons')) {
            foreach ($request->addons as $addonId) {
                $addon = ProductAddon::find($addonId);
                if ($addon) {
                    $price += $addon->addon_price;
                    $addonDetails[] = [
                        'id' => $addon->id,
                        'addon_name' => $addon->addon_name,
                        'addon_price' => $addon->addon_price,
                        'description' => $addon->description,
                    ];
                }
            }
        }

        $cartItem = [
            'id' => uniqid(),
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $price,
            'quantity' => $request->quantity,
            'image' => $product->images->first() ? $product->images->first()->optimized_url : null,
            'variations' => $variationDetails,
            'addons' => $addonDetails,
            'custom_inputs' => $request->custom_inputs ?? [],
            'added_at' => now()->toISOString(),
        ];

        // Get current cart
        $cart = $this->getCart();
        
        // Check if item already exists (same product with same variations/addons)
        $existingItemKey = $this->findExistingCartItem($cart, $cartItem);
        
        if ($existingItemKey !== null) {
            // Update quantity of existing item
            $cart[$existingItemKey]['quantity'] += $request->quantity;
        } else {
            // Add new item
            $cart[] = $cartItem;
        }

        // Save cart to session
        $this->saveCart($cart);

        // Calculate new totals
        $totals = $this->calculateCartTotals($cart, false);

        return response()->json([
            'success' => true,
            'item' => $cartItem,
            'totals' => $totals,
            'message' => 'Item added to cart successfully!'
        ]);
    }

    /**
     * Find existing cart item with same product and options.
     */
    private function findExistingCartItem(array $cart, array $newItem): ?int
    {
        foreach ($cart as $key => $item) {
            if ($item['product_id'] === $newItem['product_id'] &&
                $this->arraysEqual($item['variations'], $newItem['variations']) &&
                $this->arraysEqual($item['addons'], $newItem['addons']) &&
                $this->arraysEqual($item['custom_inputs'], $newItem['custom_inputs'])) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Compare two arrays for equality.
     */
    private function arraysEqual(array $a, array $b): bool
    {
        if (count($a) !== count($b)) {
            return false;
        }
        
        sort($a);
        sort($b);
        
        return $a === $b;
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'item_id' => 'required|string',
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $cart = $this->getCart();
        $itemId = $request->item_id;
        $newQuantity = $request->quantity;

        // Find and update the item
        foreach ($cart as $key => $item) {
            if ($item['id'] === $itemId) {
                $cart[$key]['quantity'] = $newQuantity;
                break;
            }
        }

        $this->saveCart($cart);
        $totals = $this->calculateCartTotals($cart, false);

        return response()->json([
            'success' => true,
            'totals' => $totals,
            'message' => 'Cart updated successfully!'
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function remove(Request $request): JsonResponse
    {
        $request->validate([
            'item_id' => 'required|string',
        ]);

        $cart = $this->getCart();
        $itemId = $request->item_id;

        // Remove the item
        $cart = array_filter($cart, function ($item) use ($itemId) {
            return $item['id'] !== $itemId;
        });

        // Re-index array
        $cart = array_values($cart);

        $this->saveCart($cart);
        $totals = $this->calculateCartTotals($cart, false);

        return response()->json([
            'success' => true,
            'totals' => $totals,
            'message' => 'Item removed from cart successfully!'
        ]);
    }

    /**
     * Clear entire cart.
     */
    public function clear(): JsonResponse
    {
        $this->saveCart([]);

        return response()->json([
            'success' => true,
            'totals' => $this->calculateCartTotals([], false),
            'message' => 'Cart cleared successfully!'
        ]);
    }

    /**
     * Get cart contents.
     */
    public function get(): JsonResponse
    {
        $cart = $this->getCart();
        $totals = $this->calculateCartTotals($cart, false);

        return response()->json([
            'success' => true,
            'cart' => $cart,
            'totals' => $totals,
        ]);
    }

    /**
     * Display checkout page.
     */
    public function checkout(): View|RedirectResponse
    {
        try {
            $cart = $this->getCart();
            $includeVAT = request()->boolean('vat_receipt', false);
            $totals = $this->calculateCartTotals($cart, $includeVAT);

            Log::info('=== GUEST CHECKOUT PAGE ACCESSED ===', [
                'cart_count' => count($cart),
                'cart_items' => $cart,
                'includeVAT' => $includeVAT,
                'totals' => $totals,
                'session_id' => session()->getId(),
                'user_agent' => request()->userAgent(),
                'customer_logged_in' => Auth::guard('customer')->check(),
                'url' => request()->fullUrl()
            ]);

            if (empty($cart)) {
                Log::info('Cart is empty, redirecting to cart page');
                return redirect()->route('cart')->with('error', 'Your cart is empty.');
            }

            return view('public.checkout', compact('cart', 'totals'));
        } catch (\Exception $e) {
            Log::error('Checkout page error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'session_id' => session()->getId()
            ]);
            return redirect()->route('cart')->with('error', 'An error occurred. Please try again.');
        }
    }

    /**
     * Display wholesale checkout page.
     */
    public function wholesaleCheckout(Request $request): View|RedirectResponse
    {
        $cart = $this->getCart();
        $includeVAT = $request->boolean('vat_receipt', false);
        $totals = $this->calculateCartTotals($cart, $includeVAT);

        if (empty($cart)) {
            return redirect()->route('wholesale.cart')->with('error', 'Your cart is empty.');
        }

        return view('public.wholesale-checkout', compact('cart', 'totals'));
    }

    /**
     * Process checkout and redirect to WhatsApp.
     */
    public function processCheckout(Request $request): RedirectResponse
    {
        Log::info('=== GUEST CHECKOUT FORM SUBMITTED ===', [
            'request_data' => $request->all(),
            'session_id' => session()->getId(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'customer_logged_in' => Auth::guard('customer')->check(),
            'session_cart' => session('cart', []),
            'session_all' => session()->all()
        ]);

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',
                'shipping_address' => 'required|string|max:500',
                'billing_address' => 'required|string|max:500',
                'payment_method' => 'required|string|max:255',
                'notes' => 'nullable|string|max:1000',
            ]);

            Log::info('Form validation passed successfully');

            $cart = $this->getCart();
            $includeVAT = $request->boolean('vat_receipt', false);
            
            Log::info('Cart retrieved from session', [
                'cart_count' => count($cart),
                'cart_items' => $cart,
                'includeVAT' => $includeVAT
            ]);
            
            if (empty($cart)) {
                Log::error('Cart is empty during checkout process');
                return redirect()->route('cart')->with('error', 'Your cart is empty.');
            }

            $totals = $this->calculateCartTotals($cart, $includeVAT);
            Log::info('Cart totals calculated', ['totals' => $totals]);

            // Prepare notes with VAT information
            $notes = $request->notes ?? "Order placed via WhatsApp from " . (request()->is('b2b/*') ? 'b2b.chibobrand.com' : 'chibobrand.com');
            if ($includeVAT) {
                $notes .= "\n\nVAT Receipt Requested: Yes (+18% VAT included)";
            }

            // Check if there's a logged-in customer, otherwise create guest customer
            $user = null;
            $isCustomerLoggedIn = Auth::guard('customer')->check();
            
            Log::info('=== CUSTOMER AUTHENTICATION CHECK ===', [
                'is_customer_logged_in' => $isCustomerLoggedIn,
                'customer_id' => $isCustomerLoggedIn ? Auth::guard('customer')->id() : null,
                'customer_name' => $isCustomerLoggedIn ? Auth::guard('customer')->user()->name : null,
                'customer_email' => $isCustomerLoggedIn ? Auth::guard('customer')->user()->email : null
            ]);
        
        if ($isCustomerLoggedIn) {
            $customer = Auth::guard('customer')->user();
            Log::info('Processing order for logged-in customer', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'is_wholesale' => $customer->is_wholesale
            ]);
            
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
            
            Log::info('User record created/found for logged-in customer', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->name
            ]);
        } else {
            Log::info('Processing order for guest customer');
            
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
            
            Log::info('Guest user record created/found', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->name,
                'is_guest' => true
            ]);
        }

        // Create order in database
        Log::info('Creating order in database', [
            'user_id' => $user->id,
            'total_amount' => $totals['total'],
            'notes' => $notes
        ]);
        
        $order = Order::create([
            'user_id' => $user->id,
            'order_code' => $this->generateOrderNumber(),
            'total_amount' => $totals['total'],
            'payment_status' => 'pending',
            'approval_status' => 'requested',
            'notes' => $notes,
        ]);

        Log::info('Order created successfully', [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'user_id' => $order->user_id,
            'total_amount' => $order->total_amount,
            'is_guest' => $user->email === 'guest@chibobrand.com'
        ]);

        // Create order items
        Log::info('Creating order items', ['items_count' => count($cart)]);
        foreach ($cart as $item) {
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'product_name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'subtotal' => $item['price'] * $item['quantity'],
                'product_variations' => $item['variations'],
                'product_addons' => $item['addons'],
                'custom_inputs' => $item['custom_inputs'],
                'channel' => $isWholesale ? 'wholesale' : 'retail',
            ]);
            
            Log::info('Order item created', [
                'order_item_id' => $orderItem->id,
                'product_id' => $item['product_id'],
                'product_name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'subtotal' => $item['price'] * $item['quantity']
            ]);
        }

        // Generate WhatsApp message
        $message = $this->generateWhatsAppMessage($order, $cart, $totals);

        // Clear cart after successful order
        Log::info('Clearing cart after successful order');
        $this->saveCart([]);

        // Create WhatsApp URL
        $whatsappUrl = 'https://wa.me/255655392319?text=' . urlencode($message);

        Log::info('=== GUEST CHECKOUT COMPLETED SUCCESSFULLY ===', [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'user_id' => $user->id,
            'is_guest' => $user->email === 'guest@chibobrand.com',
            'total_amount' => $order->total_amount,
            'whatsapp_url' => $whatsappUrl
        ]);

        return redirect($whatsappUrl)
            ->with('success', 'Your order has been received! We will confirm via WhatsApp soon.')
            ->with('clear_cart', true);
            
        } catch (\Exception $e) {
            Log::error('=== GUEST CHECKOUT FAILED ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'session_id' => session()->getId()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while processing your order. Please try again.');
        }
    }

    /**
     * Generate WhatsApp message from order data.
     */
    private function generateWhatsAppMessage(Order $order, array $cart, array $totals): string
    {
        $message = "Hello CHIBO BRAND 👋,\nI'd like to place this order:\n\n";

        foreach ($cart as $item) {
            $message .= "• {$item['name']} (x{$item['quantity']})\n";
            
            // Add variations if any
            if (!empty($item['variations'])) {
                foreach ($item['variations'] as $variation) {
                    $message .= "  - {$variation['name']}: {$variation['option_value']} (+TZS " . number_format($variation['extra_price'], 0) . ")\n";
                }
            }
            
            // Add addons if any
            if (!empty($item['addons'])) {
                foreach ($item['addons'] as $addon) {
                    $message .= "  - {$addon['addon_name']} (+TZS " . number_format($addon['addon_price'], 0) . ")\n";
                }
            }
            
            $message .= "\n";
        }

        $message .= "Subtotal: TZS " . number_format($totals['subtotal'], 0) . "\n";
        if ($totals['vat'] > 0) {
            $message .= "VAT (18%): TZS " . number_format($totals['vat'], 0) . "\n";
        }
        $message .= "Total: TZS " . number_format($totals['total'], 0) . "\n";
        $message .= "Order #: {$order->order_number}\n\n";
        $message .= "My name: {$order->name}\n";
        $message .= "Phone: {$order->phone}\n";
        $message .= "Email: " . ($order->email ?? 'Not provided') . "\n";
        $message .= "Shipping Address: {$order->shipping_address}\n";
        $message .= "Payment Method: {$order->payment_method}\n";
        if ($totals['vat'] > 0) {
            $message .= "VAT Receipt: Yes (18% included)\n";
        }
        $message .= "Domain: " . (request()->is('b2b/*') ? 'b2b.chibobrand.com' : 'chibobrand.com');

        return $message;
    }
}