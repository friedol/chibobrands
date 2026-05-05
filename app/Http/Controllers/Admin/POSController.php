<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EnhancedProduct;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class POSController extends Controller
{
    /**
     * Display the POS terminal.
     */
    public function index(Request $request)
    {
        if (Auth::user()->role === 'accountant') {
            abort(403, 'Accountants are not allowed to access the POS terminal.');
        }

        // Only fetch users with 'saler' role for the salesperson dropdown
        $salers = User::where('role', 'saler')->orderBy('name')->get();
        $departments = \App\Models\Department::all();
        $currentUser = Auth::user();

        $isProforma = $request->get('type') === 'proforma';
        
        return view('admin.pos.index', compact('salers', 'departments', 'currentUser', 'isProforma'));
    }

    /**
     * Search for products with variants and price tiers.
     */
    public function searchProducts(Request $request)
    {
        $query = $request->get('query');
        $type = $request->get('type', 'product');

        if ($type === 'service') {
            $services = \App\Models\DesignTaskType::query()
                ->where('name', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->get()
                ->map(function($service) {
                    return [
                        'id' => 'service_' . $service->id,
                        'original_id' => $service->id,
                        'name' => $service->name,
                        'category' => 'Design Service',
                        'barcode' => 'SVC-' . str_pad($service->id, 4, '0', STR_PAD_LEFT),
                        'retail_price' => (float) $service->price,
                        'wholesale_price' => (float) $service->price,
                        'image' => asset('images/service-placeholder.webp'),
                        'track_stock' => false,
                        'stock' => 9999,
                        'is_service' => true,
                        'variants' => [],
                        'price_tiers' => [],
                        'description' => $service->description
                    ];
                });
            return response()->json($services);
        }
        
        $products = EnhancedProduct::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%")
                  ->orWhere('product_nickname', 'like', "%{$query}%");
            })
            ->with(['images', 'variantCategories.items', 'priceTiers'])
            ->limit(20)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category ?: 'Uncategorized',
                    'barcode' => $product->barcode,
                    'retail_price' => $product->retail_base_price,
                    'wholesale_price' => $product->b2b_base_price,
                    'image' => $product->images->first() ? asset('storage/' . $product->images->first()->image_path) : asset('images/default.webp'),
                    'track_stock' => $product->track_stock,
                    'stock' => $product->stock_quantity,
                    'variants' => $product->variantCategories->map(function($category) {
                        return [
                            'id' => $category->id,
                            'name' => $category->category,
                            'items' => $category->items->map(function($item) {
                                return [
                                    'id' => $item->id,
                                    'name' => $item->name,
                                    'price_adjustment' => $item->price,
                                    'retail_price' => $item->retail_price,
                                    'wholesale_price' => $item->wholesale_price,
                                ];
                            }),
                        ];
                    }),
                    'price_tiers' => $product->priceTiers->map(function($tier) {
                        return [
                            'min_quantity' => $tier->min_quantity,
                            'max_quantity' => $tier->max_quantity,
                            'price_per_unit' => $tier->price_per_unit,
                            'customer_type' => $tier->customer_type,
                        ];
                    }),
                ];
            });

        return response()->json($products);
    }

    /**
     * Search for customers.
     */
    public function searchCustomers(Request $request)
    {
        $query = $request->get('query');
        
        $customers = Customer::forSaler(Auth::user())
            ->where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        return response()->json($customers);
    }

    /**
     * Store a new customer from POS.
     */
    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'email' => 'nullable|email|max:255|unique:customers,email',
            'address' => 'nullable|string|max:255',
            'is_wholesale' => 'boolean',
        ]);

        try {
            $customer = Customer::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'is_wholesale' => $request->is_wholesale ?: false,
                'is_active' => true,
                'verified' => true,
            ]);

            return response()->json([
                'success' => true,
                'customer' => $customer,
                'message' => 'Customer created successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update customer info from POS.
     */
    public function updateCustomer(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone,' . $customer->id,
            'email' => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'address' => 'nullable|string|max:255',
            'is_wholesale' => 'boolean',
        ]);

        try {
            $customer->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'is_wholesale' => $request->is_wholesale ?: false,
            ]);

            // Also update associated user if exists
            $user = User::where('email', $customer->email ?: $customer->phone . '@chibobrand.com')->first();
            if ($user) {
                $userData = [
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                ];
                
                if (Schema::hasColumn('users', 'address')) {
                    $userData['address'] = $customer->address;
                }
                
                $user->update($userData);
            }

            return response()->json([
                'success' => true,
                'customer' => $customer,
                'message' => 'Customer updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new POS order.
     */
    public function storeOrder(Request $request)
    {
        if (Auth::user()->role === 'accountant') {
            return response()->json([
                'success' => false,
                'message' => 'Accountants are not allowed to use the POS terminal.',
            ], 403);
        }

        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required_without:customer_id|string|max:255',
            'customer_phone' => 'required_without:customer_id|string|max:20',
            'customer_address' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable',
            'items.*.name' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.variants' => 'nullable|array',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
            'is_wholesale' => 'boolean',
            'total_amount' => 'required|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'has_vat' => 'nullable|boolean',
            'vat_amount' => 'nullable|numeric|min:0',
            'saler_id' => 'nullable|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'order_type' => 'nullable|string|in:proforma,sales_invoice',
        ]);

        Log::info('POS Order Attempt', $request->all());

        try {
            return DB::transaction(function() use ($request) {
                $user = null;
                $orderType = $request->order_type ?? 'sales_invoice';
                
                if ($request->customer_id) {
                    $customer = Customer::find($request->customer_id);
                    $user = User::where('email', $customer->email ?: $customer->phone . '@chibobrand.com')->first();
                    
                    if (!$user) {
                        $user = User::create([
                            'name' => $customer->name,
                            'email' => $customer->email ?: $customer->phone . '@chibobrand.com',
                            'phone' => $customer->phone,
                            'password' => bcrypt('password'),
                            'role' => $customer->is_wholesale ? 'wholesale_customer' : 'retail_customer',
                        ]);
                    }
                } else {
                    $email = ($request->customer_phone ?: 'pos_' . time()) . '@chibobrand.com';
                    $user = User::where('email', $email)->first() ?: User::create([
                        'name' => $request->customer_name,
                        'email' => $email,
                        'phone' => $request->customer_phone,
                        'password' => bcrypt('password'),
                        'role' => $request->is_wholesale ? 'wholesale_customer' : 'retail_customer',
                    ]);
                }

                $orderCode = Order::generateOrderCode();
                
                // Calculate payment status and balance
            $orderType = $request->order_type ?? 'sales_invoice';
            $totalAmount = $request->total_amount;
            $amountPaid = ($orderType == 'proforma') ? 0 : ($request->amount_paid ?? 0);
            
            if ($orderType == 'sales_invoice' && !$request->has('amount_paid')) {
                $amountPaid = $totalAmount; // Default to full payment for sales invoice if not specified
            }

            $balance = ($orderType == 'proforma') ? 0 : ($totalAmount - $amountPaid);
            
            // Determine payment status
            if ($orderType == 'proforma') {
                $paymentStatus = 'pending';
                $approvalStatus = 'requested';
            } elseif ($balance <= 0) {
                $paymentStatus = 'paid';
                $balance = 0;
                $approvalStatus = 'approved';
            } elseif ($amountPaid > 0) {
                $paymentStatus = 'partial';
                $approvalStatus = 'approved';
            } else {
                $paymentStatus = 'pending';
                $approvalStatus = 'approved';
            }
            
            $order = Order::create([
                'user_id' => $user->id,
                'order_code' => $orderCode,
                'subtotal' => $totalAmount - ($request->vat_amount ?? 0),
                'total_amount' => $totalAmount,
                'vat_amount' => $request->vat_amount ?? 0,
                'amount_paid' => $amountPaid,
                'balance' => $balance,
                'payment_status' => $paymentStatus,
                'approval_status' => $approvalStatus,
                'saler_id' => $request->saler_id,
                'department_id' => $request->department_id,
                'type' => $orderType,
                'notes' => ($orderType == 'proforma' ? "PROFORMA | " : "POS Order | ") . ($request->notes ?: "No notes"),
            ]);

            // Record Payment in Finance module (Only for sales invoices)
            if ($orderType == 'sales_invoice' && $amountPaid > 0) {
                \App\Models\Payment::create([
                    'order_id' => $order->id,
                    'customer_id' => $user->id,
                    'amount' => $amountPaid,
                    'payment_method' => $request->payment_method,
                    'date' => now(),
                    'seller_id' => auth()->id(),
                    'department_id' => $request->department_id,
                ]);
            }

            foreach ($request->items as $itemData) {
                $rawId = $itemData['id'] ?? null;
                $finalProductId = null;
                $productObj = null;

                // Check if the ID is a service string (e.g. 'service_123')
                $isService = $rawId && is_string($rawId) && str_starts_with($rawId, 'service_');

                if ($rawId && !$isService) {
                    $productObj = EnhancedProduct::find($rawId);
                    if ($productObj) {
                        // Check if this ID allows insertion (Foreign Key Check against 'products' table)
                        if (DB::table('products')->where('id', $productObj->id)->exists()) {
                            $finalProductId = $productObj->id;
                        }
                    }
                }

                // Fallback to a Generic Service Product if no valid ID found.
                if (!$finalProductId) {
                    $genericProduct = DB::table('products')->where('name', 'POS Service / Custom Item')->first();
                    
                    if (!$genericProduct) {
                        // Need a category ID first
                        $catId = DB::table('categories')->value('id');
                        if (!$catId) {
                            $catId = DB::table('categories')->insertGetId([
                                'name' => 'General', 
                                'slug' => 'general', 
                                'created_at' => now(), 
                                'updated_at' => now()
                            ]);
                        }
                        
                        $finalProductId = DB::table('products')->insertGetId([
                            'category_id' => $catId, 
                            'name' => 'POS Service / Custom Item',
                            'description' => 'Placeholder for POS services and custom items',
                            'base_price' => 0,
                            'wholesale_price' => 0,
                            'stock' => 999999,
                            'status' => 'active',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    } else {
                        $finalProductId = $genericProduct->id;
                    }
                }
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $finalProductId,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['price'],
                    'subtotal' => $itemData['price'] * $itemData['quantity'],
                    'product_name' => $productObj ? $productObj->name : $itemData['name'],
                    'product_barcode' => $productObj ? $productObj->barcode : ($isService ? $rawId : 'CUSTOM'),
                    'channel' => $request->is_wholesale ? 'wholesale' : 'retail',
                    'variants' => $itemData['variants'] ?? null,
                ]);

                // ONLY DECREMENT STOCK FOR SALES INVOICES
                if ($orderType == 'sales_invoice' && $productObj && $productObj->track_stock) {
                    $productObj->decrement('stock_quantity', $itemData['quantity']);
                }
            }

                Log::info('POS Order Success', ['order_code' => $order->order_code]);

                // Send SMS Receipt to Customer
                try {
                    if ($order->user && $order->user->phone) {
                        $smsService = app(\App\Services\SmsApiService::class);
                        $smsService->sendOrderReceipt($order);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send order receipt SMS from POS', ['error' => $e->getMessage()]);
                }

                return response()->json([
                    'success' => true,
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'message' => 'Order created successfully!',
                ]);
            });
        } catch (\Exception $e) {
            Log::error('POS Order Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 500);
        }
    }
}
