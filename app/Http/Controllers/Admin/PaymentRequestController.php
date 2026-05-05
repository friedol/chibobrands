<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use App\Models\Department;
use App\Models\Expense;
use Illuminate\Http\Request;

class PaymentRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = PaymentRequest::with(['department', 'createdBy']);

        // Search filter (Reason)
        if ($request->filled('search')) {
            $query->where('reason', 'like', '%' . $request->search . '%');
        }

        // Approval Status filter
        if ($request->filled('approval_status') && $request->approval_status !== 'all') {
            $query->where('approval_status', $request->approval_status);
        }

        // Payment Status filter
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        // Department filter
        if ($request->filled('department_id') && $request->department_id !== 'all') {
            $query->where('department_id', $request->department_id);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requests = $query->latest()->paginate(25)->withQueryString();
        $departments = Department::all();
        $users = \App\Models\User::orderBy('name')->where('verified', true)->get();
        
        return view('admin.finance.payment-requests', compact('requests', 'departments', 'users'));
    }

    public function store(Request $request)
    {
        // Only Super Admin, Manager, and Accountant can create
        if (!in_array(auth()->user()->role, ['super_admin', 'manager', 'accountant'])) {
            return redirect()->back()->with('error', 'Only Managers and Accountants can create payment requests.');
        }

        $validated = $request->validate([
            'reason' => 'required|string',
            'amount' => 'required|numeric',
            'department_id' => 'required|exists:departments,id',
            'requested_for_type' => 'required|in:internal,external',
            'requested_to_user_id' => 'nullable|required_if:requested_for_type,internal|exists:users,id',
            'requested_to_name' => 'nullable|required_if:requested_for_type,external|string|max:255',
        ]);

        $validated['created_by_id'] = auth()->id();
        $validated['approval_status'] = 'pending';
        $validated['payment_status'] = 'unpaid';

        PaymentRequest::create($validated);

        return redirect()->back()->with('success', 'Payment request created.');
    }

    public function updateStatus(Request $request, PaymentRequest $paymentRequest)
    {
        // Only Super Admin, Manager, and Accountant can update status
        if (!in_array(auth()->user()->role, ['super_admin', 'manager', 'accountant'])) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'approval_status' => 'nullable|in:pending,approved,rejected',
            'payment_status' => 'nullable|in:unpaid,paid',
            'payment_method' => 'nullable|string',
        ]);

        $previousStatus = $paymentRequest->payment_status;
        $paymentRequest->update([
            'approval_status' => $validated['approval_status'] ?? $paymentRequest->approval_status,
            'payment_status' => $validated['payment_status'] ?? $paymentRequest->payment_status,
        ]);

        // If status changed to paid, create an automatic expense (cash out)
        if (isset($validated['payment_status']) && $validated['payment_status'] === 'paid' && $previousStatus !== 'paid') {
            Expense::create([
                'amount' => $paymentRequest->amount,
                'category' => 'other', // Default category for automated requests
                'department_id' => $paymentRequest->department_id,
                'date' => now(),
                'approved_by_id' => auth()->id(),
                'payment_method' => $validated['payment_method'] ?? 'Cash', 
                'notes' => 'Automated expense from Payment Request: ' . $paymentRequest->reason,
            ]);
        }

        return redirect()->back()->with('success', 'Status updated successfully.');
    }
    
    public function print(Request $request)
    {
        $query = PaymentRequest::with(['department', 'createdBy']);

        if ($request->filled('search')) {
            $query->where('reason', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('approval_status') && $request->approval_status !== 'all') {
            $query->where('approval_status', $request->approval_status);
        }

        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('department_id') && $request->department_id !== 'all') {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requests = $query->latest()->get();
        
        return view('admin.finance.print-payment-requests', compact('requests'));
    }
}
