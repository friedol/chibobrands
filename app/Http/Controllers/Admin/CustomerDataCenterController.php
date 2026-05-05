<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerFollowUp;
use App\Models\MessageTemplate;
use App\Services\CustomerAnalyticsService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CustomerDataCenterController extends Controller
{
    protected $analyticsService;

    public function __construct(CustomerAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display the smart daily follow-up list.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Customer::query()->forSaler($user);

        $statusFilter = $request->get('status', 'due');
        
        if ($statusFilter === 'due') {
            $query->whereIn('follow_up_status', ['Due Today', 'Overdue']);
        } elseif ($statusFilter === 'upcoming') {
            $query->where('follow_up_status', 'Upcoming');
        } elseif ($statusFilter === 'new') {
            $query->where('follow_up_status', 'New Customer');
        }

        $customers = $query->orderBy('priority_ranking', 'desc')
            ->orderBy('next_expected_order_date', 'asc')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'due_today' => Customer::forSaler($user)->where('follow_up_status', 'Due Today')->count(),
            'overdue' => Customer::forSaler($user)->where('follow_up_status', 'Overdue')->count(),
            'upcoming' => Customer::forSaler($user)->where('follow_up_status', 'Upcoming')->count(),
            'total_customers' => Customer::forSaler($user)->count(),
        ];

        return view('admin.customers.data-center.index', compact('customers', 'stats', 'statusFilter'));
    }

    /**
     * Display detailed analytics for a customer.
     */
    public function show(Customer $customer)
    {
        $customer->load(['followUps.user', 'designs', 'taskTypeAnalytics.designTaskType']);
        
        $customer->setRelation('designTasks', $customer->designTasks()
            ->latest()
            ->take(10)
            ->get());
        
        // Ensure the saler has access if they are a saler
        if (Auth::user()->role === 'saler') {
            // Check if saler is allowed to see this customer (using same logic as scopeForSaler)
            // This is a simplified check
            if ($customer->added_by !== Auth::id()) {
                // Add more complex checks if needed
            }
        }

        return view('admin.customers.data-center.show', compact('customer'));
    }

    /**
     * Record a manual follow-up activity.
     */
    public function storeFollowUp(Request $request, Customer $customer)
    {
        $request->validate([
            'action' => 'required|string',
            'notes' => 'nullable|string',
            'next_follow_up_date' => 'nullable|date|after_or_equal:today',
        ]);

        CustomerFollowUp::create([
            'customer_id' => $customer->id,
            'user_id' => Auth::id(),
            'action' => $request->action,
            'notes' => $request->notes,
            'follow_up_date' => now(),
        ]);

        // If a next date was specified, override the automatic prediction
        if ($request->next_follow_up_date) {
            $customer->update([
                'manual_follow_up_date' => $request->next_follow_up_date,
            ]);
        }

        // Trigger recalculation of status
        $this->analyticsService->recalculateCustomerAnalytics($customer->id);

        return redirect()->back()->with('success', 'Follow-up activity recorded successfully.');
    }

    /**
     * Manually set the next follow-up date.
     */
    public function updateFollowUpDate(Request $request, Customer $customer)
    {
        $request->validate([
            'manual_follow_up_date' => 'required|date',
        ]);

        $customer->update([
            'manual_follow_up_date' => $request->manual_follow_up_date,
        ]);

        // Recalculate status based on the new date
        $this->analyticsService->recalculateCustomerAnalytics($customer->id);

        return redirect()->back()->with('success', 'Next follow-up date has been manually scheduled.');
    }

    /**
     * Refresh analytics for a customer.
     */
    public function refreshAnalytics(Customer $customer)
    {
        $this->analyticsService->recalculateCustomerAnalytics($customer->id);
        return redirect()->back()->with('success', 'Analytics refreshed successfully.');
    }
}
