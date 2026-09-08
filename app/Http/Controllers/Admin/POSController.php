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
        if (!Auth::user()->hasPermission('manage_pos')) {
            abort(403, 'You do not have permission to access the POS terminal.');
        }
        // Only fetch users with 'saler' role for the salesperson dropdown
        $salers = User::where('role', 'saler')->orderBy('name')->get();
        $departments = \App\Models\Department::all();
        $designers = User::where('role', 'designer')->where('verified', true)->get();
        $operators = User::where('role', 'operator')->where('verified', true)->get();
        $taskTypes = \App\Models\DesignTaskType::orderBy('name')->get();
        $currentUser = Auth::user();
        $regions = \App\Models\Region::orderBy('region_name')->get();

        $isProforma = $request->get('type') === 'proforma';
        
        return view('admin.pos.index', compact('salers', 'departments', 'currentUser', 'isProforma', 'designers', 'operators', 'taskTypes', 'regions'));
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
                        'image' => $service->image_path ? asset('storage/' . $service->image_path) : asset('images/service-placeholder.webp'),
                        'track_stock' => false,
                        'stock' => 9999,
                        'is_service' => true,
                        'department_id' => $service->department_id,
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
            'region_id' => 'nullable|exists:regions,id',
            'district_id' => 'nullable|exists:districts,id',
            'whatsapp_number' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'customer_source' => 'nullable|string|max:255',
            'is_wholesale' => 'boolean',
        ]);

        try {
            $customer = Customer::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'region_id' => $request->region_id,
                'district_id' => $request->district_id,
                'whatsapp_number' => $request->whatsapp_number,
                'company_name' => $request->company_name,
                'business_type' => $request->business_type,
                'customer_source' => $request->customer_source,
                'is_wholesale' => $request->is_wholesale ?: false,
                'is_active' => true,
                'verified' => true,
                'registered_by_id' => auth()->id(),
                'account_owner_id' => auth()->id(),
                'branch_id' => auth()->user()->department_id,
                'added_by' => auth()->id(),
            ]);

            // Auto-link matching leads
            \App\Services\CustomerJourneyService::linkLeadsToCustomer($customer);

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
            'region_id' => 'nullable|exists:regions,id',
            'district_id' => 'nullable|exists:districts,id',
            'whatsapp_number' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'customer_source' => 'nullable|string|max:255',
            'is_wholesale' => 'boolean',
        ]);

        try {
            $customer->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'region_id' => $request->region_id,
                'district_id' => $request->district_id,
                'whatsapp_number' => $request->whatsapp_number,
                'company_name' => $request->company_name,
                'business_type' => $request->business_type,
                'customer_source' => $request->customer_source ?? $customer->customer_source,
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
        if (!Auth::user()->hasPermission('manage_pos')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to use the POS terminal.',
            ], 403);
        }

        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required_without:customer_id|string|max:255',
            'customer_phone' => 'required_without:customer_id|string|max:20',
            'customer_address' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email',
            'whatsapp_number' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'region_id' => 'nullable|exists:regions,id',
            'district_id' => 'nullable|exists:districts,id',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable',
            'items.*.name' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.variants' => 'nullable|array',
            'payment_method' => 'required|string',
            'payment_splits' => 'nullable|array',
            'notes' => 'nullable|string',
            'is_wholesale' => 'boolean',
            'total_amount' => 'required|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'has_vat' => 'nullable|boolean',
            'vat_amount' => 'nullable|numeric|min:0',
            'saler_id' => 'nullable|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'order_type' => 'nullable|string|in:proforma,sales_invoice',
            'discount' => 'nullable|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
        ]);

        Log::info('POS Order Attempt', $request->all());

        try {
            return DB::transaction(function() use ($request) {
                $user = null;
                $customer = null;
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
                    $email = $request->customer_email ?: (($request->customer_phone ?: 'pos_' . time()) . '@chibobrand.com');
                    
                    $customer = Customer::where('phone', $request->customer_phone)->first() ?: Customer::create([
                        'name' => $request->customer_name,
                        'email' => $request->customer_email,
                        'phone' => $request->customer_phone,
                        'address' => $request->customer_address,
                        'region_id' => $request->region_id,
                        'district_id' => $request->district_id,
                        'company_name' => $request->company_name,
                        'business_type' => $request->business_type,
                        'whatsapp_number' => $request->whatsapp_number,
                        'is_wholesale' => $request->is_wholesale ?: false,
                        'is_active' => true,
                        'verified' => true,
                    ]);

                    $user = User::where('email', $email)->first() ?: User::create([
                        'name' => $request->customer_name,
                        'email' => $email,
                        'phone' => $request->customer_phone,
                        'address' => $request->customer_address,
                        'company_name' => $request->company_name,
                        'business_type' => $request->business_type,
                        'whatsapp_number' => $request->whatsapp_number,
                        'password' => bcrypt('password'),
                        'role' => $request->is_wholesale ? 'wholesale_customer' : 'retail_customer',
                    ]);
                }

                $orderType = $request->order_type ?? 'sales_invoice';
                $totalOrderAmount = floatval($request->total_amount);
                $totalOrderPaid = ($orderType == 'proforma') ? 0 : floatval($request->amount_paid ?? 0);
                
                if ($orderType == 'sales_invoice' && !$request->has('amount_paid')) {
                    $totalOrderPaid = $totalOrderAmount; // Default to full payment for sales invoice if not specified
                }

                // 1. Separate regular product items and design task items
                // Proforma invoices do NOT create DesignTasks — they are price quotes only.
                // All design task items in proforma mode are treated as plain order items.
                $productItems = [];
                $designTaskItems = [];
                $productsSubtotal = 0;

                foreach ($request->items as $itemData) {
                    if (!empty($itemData['is_design_task']) && $orderType !== 'proforma') {
                        $designTaskItems[] = $itemData;
                    } else {
                        $productItems[] = $itemData;
                        $productsSubtotal += floatval($itemData['price']) * intval($itemData['quantity']);
                    }
                }

                $productsVat = 0;
                if ($request->has_vat) {
                    $productsVat = $productsSubtotal * 0.18;
                }
                $productsTotal = $productsSubtotal + $productsVat;

                // 2. Create Design Tasks first (skipped entirely for proforma orders)
                $tasksCreated = [];
                $totalTasksPaid = 0;

                // Track split proportions if split payment
                $splitProportions = [];
                if ($totalOrderPaid > 0 && $request->has('payment_splits') && is_array($request->payment_splits)) {
                    foreach ($request->payment_splits as $method => $amount) {
                        $amount = floatval($amount);
                        if ($amount > 0) {
                            $splitProportions[$method] = $amount / $totalOrderPaid;
                        }
                    }
                }

                // Sequential (waterfall) payment distribution — same as DesignTaskController::store()
                // Fill each task fully before moving the remainder to the next task.
                $remainingTaskPaid = $totalOrderPaid;

                // Order-level delivery and discount go entirely to the first task to avoid decimal distribution
                $orderLevelDelivery  = floatval($request->delivery_fee ?? 0);
                $orderLevelDiscount  = floatval($request->discount ?? 0);
                $isFirstTask         = true;

                foreach ($designTaskItems as $itemData) {
                    $details = $itemData['task_details'] ?? [];
                    $deliveryCost     = $isFirstTask ? $orderLevelDelivery : 0;
                    $deliveryDiscount = $isFirstTask ? $orderLevelDiscount : 0;
                    $isFirstTask      = false;

                    $basePrice = floatval($itemData['price']) * intval($itemData['quantity']);
                    $requiresReceipt = $request->has_vat ? true : false;
                    $taskPriceWithVat = $requiresReceipt
                        ? ($basePrice * 1.18) + $deliveryCost - $deliveryDiscount
                        : $basePrice + $deliveryCost - $deliveryDiscount;

                    // Pay as much as possible from the remaining pool, never exceeding this task's price
                    $apportionedTaskPaid    = min($remainingTaskPaid, $taskPriceWithVat);
                    $remainingTaskPaid     -= $apportionedTaskPaid;
                    $apportionedTaskBalance = $taskPriceWithVat - $apportionedTaskPaid;

                    $totalTasksPaid += $apportionedTaskPaid;

                    // Create Design Task
                    $task = \App\Models\DesignTask::create([
                        'title' => $itemData['name'],
                        'task_code' => \App\Models\DesignTask::generateTaskCode(),
                        'description' => $details['description'] ?? null,
                        'designer_instructions' => $details['instructions'] ?? null,
                        'customer_id' => $customer ? $customer->id : null,
                        'receptionist_id' => Auth::id(),
                        'designer_id' => $details['designer_id'] ?: null,
                        'operator_id' => $details['operator_id'] ?: null,
                        'saler_id' => $details['saler_id'] ?: null,
                        'department_id' => $details['department_id'] ?: $request->department_id,
                        'priority' => $details['priority'] ?? 3,
                        'deadline' => $details['deadline'] ?? null,
                        'price' => $basePrice,
                        'qty' => $itemData['quantity'],
                        'rate' => $itemData['price'],
                        'amount_paid' => $apportionedTaskPaid,
                        'balance' => $apportionedTaskBalance,
                        'requires_receipt' => $requiresReceipt,
                        'design_task_type_id' => $details['task_type_id'] ?: null,
                        'delivery_cost' => $deliveryCost,
                        'delivery_discount' => $deliveryDiscount,
                        'status' => \App\Models\DesignTask::STATUS_PENDING,
                    ]);
                    
                    $tasksCreated[] = $task;

                    // Record Payment in Finance module for the design task
                    if ($orderType == 'sales_invoice' && $apportionedTaskPaid > 0) {
                        if (!empty($splitProportions)) {
                            foreach ($splitProportions as $method => $proportion) {
                                \App\Models\Payment::create([
                                    'design_task_id' => $task->id,
                                    'customer_id' => $customer ? $customer->id : null,
                                    'amount' => $apportionedTaskPaid * $proportion,
                                    'payment_method' => $method,
                                    'date' => now(),
                                    'seller_id' => auth()->id(),
                                    'department_id' => $task->department_id,
                                ]);
                            }
                        } else {
                            \App\Models\Payment::create([
                                'design_task_id' => $task->id,
                                'customer_id' => $customer ? $customer->id : null,
                                'amount' => $apportionedTaskPaid,
                                'payment_method' => $request->payment_method,
                                'date' => now(),
                                'seller_id' => auth()->id(),
                                'department_id' => $task->department_id,
                            ]);
                        }
                    }

                    // Audit logs and notifications exactly like DesignTaskController
                    try {
                        $task->load('customer');
                        if ($task->customer) {
                            \App\Services\AuditLogService::created($task, 'Created design task: ' . $task->title . ' for customer: ' . $task->customer->name . ' via POS');
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to log audit for design task creation from POS: ' . $e->getMessage());
                    }

                    // Notify operators and admins about the new task
                    $staffToNotify = \App\Models\User::whereIn('role', ['operator', 'admin', 'super_admin', 'accountant'])->get();
                    foreach ($staffToNotify as $staff) {
                        try {
                            $staff->notify(new \App\Notifications\NewTaskCreatedNotification($task, Auth::user()));
                        } catch (\Exception $e) {
                            Log::error('Failed to send NewTaskCreatedNotification from POS: ' . $e->getMessage());
                        }
                    }

                    // If a designer was assigned during creation, notify them too
                    if ($task->designer_id) {
                        $designer = \App\Models\User::find($task->designer_id);
                        if ($designer) {
                            try {
                                $designer->notify(new \App\Notifications\TaskAssignedNotification($task));
                            } catch (\Exception $e) {
                                Log::error('Failed to send TaskAssignedNotification from POS: ' . $e->getMessage());
                            }
                        }
                    }
                }

                // 3. Process products order (only if there are products in the cart)
                $order = null;
                if (!empty($productItems)) {
                    $orderCode = Order::generateOrderCode();
                    $orderPaid = max(0, $totalOrderPaid - $totalTasksPaid);
                    $orderBalance = max(0, $productsTotal - $orderPaid);

                    // Determine payment status
                    if ($orderType == 'proforma') {
                        $paymentStatus = 'pending';
                        $approvalStatus = 'requested';
                    } elseif ($orderBalance <= 0) {
                        $paymentStatus = 'paid';
                        $orderBalance = 0;
                        $approvalStatus = 'approved';
                    } elseif ($orderPaid > 0) {
                        $paymentStatus = 'partial';
                        $approvalStatus = 'approved';
                    } else {
                        $paymentStatus = 'pending';
                        $approvalStatus = 'approved';
                    }

                    $order = Order::create([
                        'user_id' => $user->id,
                        'order_code' => $orderCode,
                        'subtotal' => $productsSubtotal,
                        'discount' => floatval($request->discount ?? 0),
                        'shipping_cost' => floatval($request->delivery_fee ?? $request->shipping_cost ?? 0),
                        'total_amount' => $productsTotal - floatval($request->discount ?? 0) + floatval($request->delivery_fee ?? $request->shipping_cost ?? 0),
                        'vat_amount' => $productsVat,
                        'amount_paid' => $orderPaid,
                        'balance' => $orderBalance,
                        'payment_status' => $paymentStatus,
                        'approval_status' => $approvalStatus,
                        'saler_id' => $request->saler_id,
                        'department_id' => $request->department_id,
                        'type' => $orderType,
                        'notes' => ($orderType == 'proforma' ? "PROFORMA | " : "POS Order | ") . ($request->notes ?: "No notes"),
                    ]);

                    // Record Payment in Finance module for the order
                    if ($orderType == 'sales_invoice' && $orderPaid > 0) {
                        if (!empty($splitProportions)) {
                            foreach ($splitProportions as $method => $amountProportion) {
                                \App\Models\Payment::create([
                                    'order_id' => $order->id,
                                    'customer_id' => $customer ? $customer->id : null,
                                    'amount' => $orderPaid * $amountProportion,
                                    'payment_method' => $method,
                                    'date' => now(),
                                    'seller_id' => auth()->id(),
                                    'department_id' => $request->department_id,
                                ]);
                            }
                        } else {
                            \App\Models\Payment::create([
                                'order_id' => $order->id,
                                'customer_id' => $customer ? $customer->id : null,
                                'amount' => $orderPaid,
                                'payment_method' => $request->payment_method,
                                'date' => now(),
                                'seller_id' => auth()->id(),
                                'department_id' => $request->department_id,
                            ]);
                        }
                    }

                    // Create OrderItem records and decrement stock
                    foreach ($productItems as $itemData) {
                        $rawId = $itemData['id'] ?? null;
                        $finalProductId = null;
                        $productObj = null;
                        $isService = $rawId && is_string($rawId) && str_starts_with($rawId, 'service_');

                        if ($rawId && !$isService) {
                            $productObj = EnhancedProduct::find($rawId);
                            if ($productObj) {
                                if (DB::table('products')->where('id', $productObj->id)->exists()) {
                                    $finalProductId = $productObj->id;
                                }
                            }
                        }

                        if (!$finalProductId) {
                            $genericProduct = DB::table('products')->where('name', 'POS Service / Custom Item')->first();
                            
                            if (!$genericProduct) {
                                $catId = DB::table('categories')->value('id') ?: DB::table('categories')->insertGetId(['name' => 'General', 'slug' => 'general', 'created_at' => now(), 'updated_at' => now()]);
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

                    // Send SMS Receipt to Customer for the order
                    try {
                        if ($order->user && $order->user->phone) {
                            $smsService = app(\App\Services\SmsApiService::class);
                            $smsService->sendOrderReceipt($order);
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to send order receipt SMS from POS', ['error' => $e->getMessage()]);
                    }
                }

                // Send Batch Task Receipt SMS if design tasks were created
                try {
                    if (!empty($tasksCreated)) {
                        $smsService = app(\App\Services\SmsApiService::class);
                        $smsService->sendBatchTaskReceipt($tasksCreated);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send batch task receipt SMS from POS', ['error' => $e->getMessage()]);
                }

                // Auto-convert any pending leads for this customer
                if ($customer) {
                    try {
                        \App\Services\CustomerJourneyService::convertLeadsByCustomer($customer);
                    } catch (\Exception $e) {
                        Log::error('Failed to auto-convert leads on POS checkout', ['error' => $e->getMessage()]);
                    }
                }

                $orderCode = $order
                    ? $order->order_code
                    : (!empty($tasksCreated) ? $tasksCreated[0]->task_code : null);

                return response()->json([
                    'success'       => true,
                    'order_id'      => $order ? $order->id : null,
                    'order_code'    => $orderCode,
                    'is_proforma'   => $orderType === 'proforma',
                    'proforma_url'  => ($orderType === 'proforma' && $order)
                        ? route('admin.finance.invoices.proforma', ['order_code' => $order->order_code])
                        : null,
                    'message'       => $order ? 'Order created successfully!' : 'Design task(s) created successfully!',
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
