<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\EnhancedProduct; // Matches POS controller usage
use App\Models\DesignTaskType; // Use Types for quoting services
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    /**
     * Search for customers (AJAX).
     */
    public function searchCustomers(Request $request)
    {
        $term = $request->get('q');
        $customers = Customer::where('is_active', true)
            ->where(function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('phone', 'like', "%{$term}%");
            })
            ->limit(20)
            ->get();

        $results = $customers->map(function($customer) {
            return [
                'id' => $customer->id,
                'text' => $customer->name . ' (' . $customer->phone . ')'
            ];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * Generate a Proforma Invoice for an order.
     */
    public function proforma($order_code)
    {
        $order = Order::with(['user', 'saler', 'department', 'items.product'])->where('order_code', $order_code)->firstOrFail();
        
        return view('admin.invoices.proforma', compact('order'));
    }

    /**
     * Generate a Sales Invoice for an order.
     */
    public function sales($order_code)
    {
        $order = Order::with(['user', 'saler', 'department', 'items.product'])->where('order_code', $order_code)->firstOrFail();
        
        return view('admin.invoices.sales', compact('order'));
    }

    /**
     * Generate a Payment Receipt.
     */
    public function receipt($id)
    {
        $payment = Payment::with(['customer', 'seller', 'department', 'order.items.product'])->findOrFail($id);
        
        return view('admin.invoices.receipt', compact('payment'));
    }

    /**
     * Show the proforma invoice creation form.
     */
    public function createProforma()
    {
        return redirect()->route('admin.pos.index', ['type' => 'proforma']);
    }

    /**
     * Generate proforma invoice from selected items.
     */
    public function generateProforma(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|string', // Changed to string to support 'new'
            'department_id' => 'required|exists:departments,id',
            'invoice_date' => 'required|date',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
            'design_task_types' => 'nullable|array',
            'design_task_types.*' => 'exists:design_task_types,id',
            'notes' => 'nullable|string|max:1000',
            'has_vat' => 'nullable|boolean',
            'pricing_mode' => 'required|in:retail,wholesale',
            // Optional validation for new customer
            'customer_name' => 'required_if:customer_id,new|nullable|string|max:255',
            'customer_phone' => 'required_if:customer_id,new|nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'nullable|string|max:500',
            'customer_company' => 'nullable|string|max:255',
            'customer_business_type' => 'nullable|string|max:255',
        ]);

        // Validate that at least one item is selected
        if (empty($request->products) && empty($request->design_task_types)) {
            return redirect()->back()->with('error', 'Please select at least one product or service type.');
        }

        return DB::transaction(function() use ($request) {
            $customerId = $request->customer_id;

            if ($customerId === 'new') {
                // Create new customer
                $customer = Customer::create([
                    'name' => $request->customer_name,
                    'phone' => $request->customer_phone,
                    'email' => $request->customer_email,
                    'address' => $request->customer_address,
                    'company_name' => $request->customer_company,
                    'business_type' => $request->customer_business_type,
                    'is_active' => true,
                    'added_by' => auth()->id(),
                    'password' => bcrypt('password'), // default password for customers
                ]);
                $customerId = $customer->id;
            } else {
                $customer = Customer::findOrFail($customerId);
            }

            // Ensure a User exists for this Customer (Order requires user_id)
            $user = User::where('email', $customer->email ?: $customer->phone . '@chibobrand.com')->first();
            
            if (!$user) {
                // Create a user account for the customer if one doesn't exist
                $user = User::create([
                    'name' => $customer->name,
                    'email' => $customer->email ?: $customer->phone . '@chibobrand.com',
                    'phone' => $customer->phone,
                    'password' => bcrypt('password'), // consistent with POS defaults
                    'role' => $customer->is_wholesale ? 'wholesale_customer' : 'retail_customer',
                    'is_verified' => true,
                ]);
            }

            // Generate order code
            $orderCode = 'PRO-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            while (Order::where('order_code', $orderCode)->exists()) {
                $orderCode = 'PRO-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }

            // Create order (Standard Order Model)
            $order = new Order();
            $order->order_code = $orderCode;
            $order->user_id = $user->id;
            $order->saler_id = auth()->id();
            $order->department_id = $request->department_id;
            $order->type = 'proforma';
            $order->payment_status = 'pending';
            $order->approval_status = 'approved';
            $order->subtotal = 0;
            $order->discount = 0;
            $order->vat_amount = 0;
            $order->total_amount = 0;
            $order->amount_paid = 0;
            $order->balance = 0;
            $order->notes = 'PROFORMA | ' . ($request->notes ?? '');
            $order->created_at = Carbon::parse($request->invoice_date);
            $order->save();

            $subtotal = 0;

            // Add products
            if (!empty($request->products)) {
                $products = EnhancedProduct::whereIn('id', $request->products)->get();
                foreach ($products as $product) {
                    $orderItem = new OrderItem();
                    $orderItem->order_id = $order->id;
                    $orderItem->product_id = $product->id;
                    $orderItem->product_name = $product->name;
                    $orderItem->product_barcode = $product->barcode;
                    $orderItem->quantity = 1; 
                    $price = ($request->pricing_mode === 'wholesale') 
                        ? ($product->b2b_base_price ?? $product->retail_base_price ?? 0) 
                        : ($product->retail_base_price ?? 0);
                    $orderItem->unit_price = $price;
                    $orderItem->subtotal = $price;
                    $orderItem->channel = $request->pricing_mode;
                    $orderItem->save();

                    $subtotal += $price;
                }
            }

            // Add Design Service Types (Quoting)
            if (!empty($request->design_task_types)) {
                $taskTypes = DesignTaskType::whereIn('id', $request->design_task_types)->get();
                foreach ($taskTypes as $type) {
                    $orderItem = new OrderItem();
                    $orderItem->order_id = $order->id;
                    // Generic Service setup
                    $orderItem->product_name = $type->name;
                    $orderItem->product_barcode = 'SVC-' . str_pad($type->id, 4, '0', STR_PAD_LEFT);
                    $orderItem->quantity = 1;
                    $price = $type->price ?? 0;
                    $orderItem->unit_price = $price;
                    $orderItem->subtotal = $price;
                    $orderItem->variants = [
                        'type' => 'service_quote',
                        'service_type_id' => $type->id,
                    ];
                    $orderItem->save();

                    $subtotal += $price;
                }
            }

            // Calculate Totals
            $vatAmount = 0;
            if ($request->has_vat) {
                $vatAmount = $subtotal * 0.18;
            }
            
            $totalAmount = $subtotal + $vatAmount;

            $order->subtotal = $subtotal;
            $order->vat_amount = $vatAmount;
            $order->total_amount = $totalAmount;
            $order->balance = $totalAmount;
            $order->save();

            // Redirect to proforma invoice view
            return redirect()->route('admin.finance.invoices.proforma', $order->order_code)
                ->with('success', 'Proforma invoice generated successfully!');
        });
    }
}
