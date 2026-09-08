<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Display paid orders.
     */
    public function paid()
    {
        $query = Order::with(['user', 'items.product'])
            ->where('payment_status', 'paid');

        // Saler Scope
        if (Auth::user()->role === 'saler') {
            $query->where(function($q) {
                $q->where('saler_id', Auth::id())
                  ->orWhereNull('saler_id');
            });
        }

        // Apply Search (Order Code or Customer Name)
        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }
            
        $orders = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $templates = \App\Models\MessageTemplate::active()->get();

        return view('admin.payments.paid', compact('orders', 'templates'));
    }

    /**
     * Display pending/partial payment orders.
     */
    public function pending()
    {
        $query = Order::with(['user', 'items.product'])
            ->whereIn('payment_status', ['pending', 'partial']);

        // Saler Scope
        if (Auth::user()->role === 'saler') {
            $query->where(function($q) {
                $q->where('saler_id', Auth::id())
                  ->orWhereNull('saler_id');
            });
        }

        // Apply Search (Order Code or Customer Name)
        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }
            
        $orders = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $templates = \App\Models\MessageTemplate::active()->get();

        return view('admin.payments.pending', compact('orders', 'templates'));
    }

    /**
     * Display all invoices.
     */
    public function invoices()
    {
        $query = Order::with(['user', 'items.product']);

        // Saler Scope
        if (Auth::user()->role === 'saler') {
            $query->where(function($q) {
                $q->where('saler_id', Auth::id())
                  ->orWhereNull('saler_id');
            });
        }

        // Apply Search (Order Code or Customer Name)
        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }
            
        $orders = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $templates = \App\Models\MessageTemplate::active()->get();

        return view('admin.payments.invoices', compact('orders', 'templates'));
    }

    /**
     * Get order data for receipt printing.
     */
    public function getOrderData($id)
    {
        $order = Order::with(['user', 'items.product', 'items.product.variants'])
            ->findOrFail($id);

        return response()->json([
            'id' => $order->id,
            'order_code' => $order->order_code,
            'total_amount' => $order->total_amount,
            'amount_paid' => $order->amount_paid,
            'balance' => $order->balance,
            'payment_status' => $order->payment_status,
            'notes' => $order->notes,
            'vat_amount' => $order->vat_amount,
            'subtotal' => $order->total_amount - ($order->vat_amount ?? 0),
            'created_at' => $order->created_at,
            'user' => [
                'name' => $order->user->name,
                'phone' => $order->user->phone ?? null,
            ],
            'customer' => $order->customer_name ? [
                'name' => $order->customer_name,
                'phone' => $order->customer_phone,
                'address' => $order->customer_address,
            ] : null,
            'saler' => $order->saler_id ? [
                'name' => \App\Models\User::find($order->saler_id)->name ?? 'Staff',
                'phone' => \App\Models\User::find($order->saler_id)->phone ?? null,
            ] : null,
            'items' => $order->items->map(function($item) {
                return [
                    'product' => [
                        'name' => $item->product->name ?? $item->product_name ?? 'Product',
                    ],
                    'variants' => $item->variants,
                    'quantity' => $item->quantity,
                    'price' => $item->unit_price ?? $item->price, // Use unit_price if available
                    'total' => $item->subtotal ?? $item->total,
                ];
            }),
        ]);
    }

    /**
     * Update payment for an order.
     */
    public function updatePayment(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'payment_date' => 'nullable|date',
            'note' => 'nullable|string',
        ]);

        $newAmount = $request->amount;
        $order->amount_paid += $newAmount;
        $order->balance = max(0, $order->total_amount - $order->amount_paid);

        if ($order->balance <= 0) {
            $order->payment_status = 'paid';
            $order->balance = 0; // Ensure no negative balance
        } else {
            $order->payment_status = 'partial';
        }

        $paymentDateStr = $request->filled('payment_date') ? \Carbon\Carbon::parse($request->payment_date)->format('d/m/Y') : now()->format('d/m/Y H:i');

        // Append note
        $noteEntry = "Payment: TZS " . number_format($newAmount) . " (" . $request->payment_method . ") Date: " . $paymentDateStr;
        if ($request->note) {
            $noteEntry .= " - " . $request->note;
        }

        $order->notes = ($order->notes ? $order->notes . "\n" : "") . $noteEntry;

        $order->save();

        return redirect()->back()->with('success', 'Payment updated successfully!');
    }
}
