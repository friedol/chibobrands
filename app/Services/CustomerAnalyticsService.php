<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CustomerProductAnalytic;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerAnalyticsService
{
    /**
     * Recalculate analytics for a customer based on their orders.
     */
    public function recalculateCustomerAnalytics(int $customerId)
    {
        $customer = Customer::find($customerId);
        if (!$customer) return;

        // Get all design tasks for this customer
        $designTasks = \App\Models\DesignTask::where('customer_id', $customerId)
            ->whereIn('status', [
                \App\Models\DesignTask::STATUS_COMPLETED,
                \App\Models\DesignTask::STATUS_CONFIRMED,
                \App\Models\DesignTask::STATUS_PRINTED,
                \App\Models\DesignTask::STATUS_SUPER_COMPLETED
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        if ($designTasks->isEmpty()) {
            $customer->update([
                'total_orders' => 0,
                'total_spent' => 0,
                'avg_reorder_interval' => null,
                'last_order_date' => null,
                'next_expected_order_date' => null,
                'follow_up_status' => 'New Customer',
            ]);
            return;
        }

        // Use Design Tasks for interval calculation
        $allEvents = collect();
        foreach ($designTasks as $task) {
            // Standardizing on total spent by customer.
            $taskTotal = $task->requires_receipt ? $task->price * 1.18 : $task->price;
            $allEvents->push(['date' => $task->created_at, 'amount' => (float)$taskTotal]);
        }
        
        $totalOrders = $allEvents->count();
        $totalSpent = $allEvents->sum('amount');
        $lastOrderDate = $allEvents->last()['date'];

        // Calculate average reorder interval
        $intervals = [];
        for ($i = 1; $i < $totalOrders; $i++) {
            $prevDate = Carbon::parse($allEvents[$i - 1]['date']);
            $currDate = Carbon::parse($allEvents[$i]['date']);
            $intervals[] = $prevDate->diffInDays($currDate);
        }

        $avgInterval = count($intervals) > 0 ? (int) (array_sum($intervals) / count($intervals)) : null;
        
        $nextExpectedDate = null;
        if ($avgInterval) {
            $nextExpectedDate = Carbon::parse($lastOrderDate)->addDays($avgInterval);
        }

        // Determine follow-up status
        $status = $this->determineStatus($nextExpectedDate);

        $customer->update([
            'total_orders' => $totalOrders,
            'total_spent' => $totalSpent,
            'avg_reorder_interval' => $avgInterval,
            'last_order_date' => $lastOrderDate,
            'next_expected_order_date' => $nextExpectedDate,
            'follow_up_status' => $status,
            'priority_ranking' => $this->calculatePriority($totalSpent, $totalOrders),
        ]);

        // Update per-task-type analytics
        $this->updateTaskTypeAnalytics($customerId, $designTasks);

        // Update repeated customer stats
        $customer->recalculatePurchaseStats();
    }

    /**
     * Update analytics for each design task type purchased by the customer.
     */
    protected function updateTaskTypeAnalytics(int $customerId, $designTasks)
    {
        $taskTypePurchases = [];

        foreach ($designTasks as $task) {
            if (!$task->design_task_type_id) continue;
            
            if (!isset($taskTypePurchases[$task->design_task_type_id])) {
                $taskTypePurchases[$task->design_task_type_id] = [];
            }
            $taskTypePurchases[$task->design_task_type_id][] = [
                'date' => $task->created_at,
                'quantity' => $task->qty
            ];
        }

        foreach ($taskTypePurchases as $taskTypeId => $purchases) {
            $totalQty = array_sum(array_column($purchases, 'quantity'));
            $lastPurchaseDate = end($purchases)['date'];
            
            $intervals = [];
            for ($i = 1; $i < count($purchases); $i++) {
                $prevDate = Carbon::parse($purchases[$i - 1]['date']);
                $currDate = Carbon::parse($purchases[$i]['date']);
                $intervals[] = $prevDate->diffInDays($currDate);
            }

            $avgInterval = count($intervals) > 0 ? (int) (array_sum($intervals) / count($intervals)) : null;
            $nextExpectedDate = $avgInterval ? Carbon::parse($lastPurchaseDate)->addDays($avgInterval) : null;

            \App\Models\CustomerTaskTypeAnalytic::updateOrCreate(
                ['customer_id' => $customerId, 'design_task_type_id' => $taskTypeId],
                [
                    'avg_reorder_interval' => $avgInterval,
                    'last_purchase_date' => $lastPurchaseDate,
                    'next_expected_purchase_date' => $nextExpectedDate,
                    'total_quantity_bought' => $totalQty,
                ]
            );
        }
    }

    /**
     * Update analytics for each product purchased by the customer.
     */
    protected function updateProductAnalytics(int $customerId, $orders)
    {
        $productPurchases = [];

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                if (!$item->product_id) continue;
                
                if (!isset($productPurchases[$item->product_id])) {
                    $productPurchases[$item->product_id] = [];
                }
                $productPurchases[$item->product_id][] = [
                    'date' => $order->created_at,
                    'quantity' => $item->quantity
                ];
            }
        }

        foreach ($productPurchases as $productId => $purchases) {
            $totalQty = array_sum(array_column($purchases, 'quantity'));
            $lastPurchaseDate = end($purchases)['date'];
            
            $intervals = [];
            for ($i = 1; $i < count($purchases); $i++) {
                $prevDate = Carbon::parse($purchases[$i - 1]['date']);
                $currDate = Carbon::parse($purchases[$i]['date']);
                $intervals[] = $prevDate->diffInDays($currDate);
            }

            $avgInterval = count($intervals) > 0 ? (int) (array_sum($intervals) / count($intervals)) : null;
            $nextExpectedDate = $avgInterval ? Carbon::parse($lastPurchaseDate)->addDays($avgInterval) : null;

            CustomerProductAnalytic::updateOrCreate(
                ['customer_id' => $customerId, 'product_id' => $productId],
                [
                    'avg_reorder_interval' => $avgInterval,
                    'last_purchase_date' => $lastPurchaseDate,
                    'next_expected_purchase_date' => $nextExpectedDate,
                    'total_quantity_bought' => $totalQty,
                ]
            );
        }
    }

    /**
     * Determine the follow-up status for a customer model.
     * Customers who have fully paid (no outstanding balance) are never flagged as Overdue.
     */
    public function determineFollowUpStatus(Customer $customer)
    {
        $status = $this->determineStatus($customer->effective_follow_up_date);

        if ($status === 'Overdue') {
            $hasUnpaidBalance = \App\Models\DesignTask::where('customer_id', $customer->id)
                ->where('balance', '>', 0)
                ->where('status', '!=', \App\Models\DesignTask::STATUS_CANCELLED)
                ->where(function ($q) { $q->where('is_loss', false)->orWhereNull('is_loss'); })
                ->exists();

            if (!$hasUnpaidBalance) {
                return 'Active';
            }
        }

        return $status;
    }

    /**
     * Determine the follow-up status based on the next expected order date.
     */
    public function determineStatus($nextExpectedDate)
    {
        if (!$nextExpectedDate) return 'New Customer';

        $today = Carbon::today();
        $expected = Carbon::parse($nextExpectedDate)->startOfDay();

        if ($expected->isToday()) {
            return 'Due Today';
        } elseif ($expected->isPast()) {
            return 'Overdue';
        } else {
            $diff = $expected->diffInDays($today);
            if ($diff <= 3) {
                return 'Upcoming';
            }
        }

        return 'Active'; // For dates far in the future
    }

    /**
     * Calculate priority ranking (higher score = higher priority).
     */
    protected function calculatePriority($totalSpent, $totalOrders)
    {
        // Simple heuristic: 1 point per 100,000 spent + 10 points per order
        return (int) (($totalSpent / 100000) + ($totalOrders * 10));
    }
}
