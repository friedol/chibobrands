<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\DesignTask;
use App\Models\SalesTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalerDashboardController extends Controller
{
    /**
     * Show the saler's personal dashboard.
     */
    public function myDashboard(Request $request)
    {
        $saler = Auth::user();
        
        if ($saler->role !== 'saler') {
            abort(403, 'Unauthorized access.');
        }

        $period = $request->get('period', 'month');
        $dateRange = match($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            '6_months' => [now()->subMonths(6), now()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            '2_years' => [now()->subYears(2), now()],
            'custom' => [
                $request->get('start_date') ? \Carbon\Carbon::parse($request->get('start_date'))->startOfDay() : null,
                $request->get('end_date') ? \Carbon\Carbon::parse($request->get('end_date'))->endOfDay() : null
            ],
            'all' => [null, null],
            default => [now()->startOfMonth(), now()->endOfMonth()]
        };

        $applyPeriod = function($query) use ($dateRange) {
            if ($dateRange[0] && $dateRange[1]) {
                $query->whereBetween('created_at', [$dateRange[0], $dateRange[1]]);
            }
            return $query;
        };

        // Helper for order query scope - include own orders OR unassigned (online) orders
        $orderScope = function($query) use ($saler) {
            $query->where(function($q) use ($saler) {
                $q->where('orders.saler_id', $saler->id)
                  ->orWhereNull('orders.saler_id');
            });
        };

        // Get statistics for the authenticated saler + online orders
        $sharedOrderQuery = Order::where($orderScope)->where('approval_status', '!=', 'cancelled');
        
        $totalOrders = $applyPeriod(clone $sharedOrderQuery)->count();
        $totalRevenue = $applyPeriod(clone $sharedOrderQuery)->sum('total_amount');
        $totalPaid = $applyPeriod(clone $sharedOrderQuery)->sum('amount_paid');
        $totalBalance = $applyPeriod(clone $sharedOrderQuery)->sum('balance');
        
        $totalTasks = $applyPeriod(DesignTask::where('saler_id', $saler->id))->count();
        $tasksRevenue = $applyPeriod(DesignTask::where('saler_id', $saler->id))->sum('price');
        $tasksPaid = $applyPeriod(DesignTask::where('saler_id', $saler->id))->sum('amount_paid');
        $tasksBalance = $applyPeriod(DesignTask::where('saler_id', $saler->id))->sum('balance');

        $overallRevenue = $totalRevenue + $tasksRevenue;
        $overallPaid = $totalPaid + $tasksPaid;
        $overallBalance = $totalBalance + $tasksBalance;
        
        // Today's stats
        $todayOrders = Order::where($orderScope)
            ->whereDate('created_at', today())
            ->where('approval_status', '!=', 'cancelled')
            ->count();
            
        $todayRevenue = Order::where($orderScope)
            ->whereDate('created_at', today())
            ->where('approval_status', '!=', 'cancelled')
            ->sum('total_amount');
        
        // Get unique customers count
        $customers = User::whereIn('id', function($query) use ($saler, $dateRange) {
            $query->select('user_id')
                ->from('orders')
                ->where(function($q) use ($saler) {
                    $q->where('saler_id', $saler->id)
                      ->orWhereNull('saler_id');
                });
            if ($dateRange[0] && $dateRange[1]) {
                $query->whereBetween('created_at', [$dateRange[0], $dateRange[1]]);
            }
        })->orWhereIn('id', function($query) use ($saler, $dateRange) {
            $query->select('customer_id')
                ->from('design_tasks')
                ->where('saler_id', $saler->id);
            if ($dateRange[0] && $dateRange[1]) {
                $query->whereBetween('created_at', [$dateRange[0], $dateRange[1]]);
            }
        })->count();

        // Dynamic Revenue Bar Chart based on Period
        $revenueData = [];
        $revenueLabels = [];
        
        if ($period == 'today') {
            for ($i = 11; $i >= 0; $i--) {
                $hour = now()->subHours($i * 2);
                $revenueLabels[] = $hour->format('H:00');
                $revenueData[] = Order::where($orderScope)
                    ->whereBetween('created_at', [$hour->copy()->startOfHour(), $hour->copy()->addHour()->endOfHour()])
                    ->where('approval_status', '!=', 'cancelled')->sum('total_amount');
            }
        } elseif ($period == 'week' || $period == 'month') {
            $days = $period == 'week' ? 7 : 30;
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $revenueLabels[] = $date->format('M d');
                $revenueData[] = Order::where($orderScope)
                    ->whereDate('created_at', $date->format('Y-m-d'))
                    ->where('approval_status', '!=', 'cancelled')->sum('total_amount');
            }
        } elseif ($period == '2_years') {
            for ($i = 23; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $revenueLabels[] = $month->format('M');
                $revenueData[] = Order::where($orderScope)
                    ->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)
                    ->where('approval_status', '!=', 'cancelled')->sum('total_amount');
            }
        } else { // 6_months, year, all (default to months)
            $months = ($period == 'year') ? 12 : 6;
            for ($i = $months - 1; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $revenueLabels[] = $month->format('M');
                $revenueData[] = Order::where($orderScope)
                    ->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)
                    ->where('approval_status', '!=', 'cancelled')->sum('total_amount');
            }
        }

        // Category sales data (from order items)
        $categoryQuery = \DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where(function($query) use ($saler) {
                $query->where('orders.saler_id', $saler->id)
                      ->orWhereNull('orders.saler_id');
            })
            ->where('orders.approval_status', '!=', 'cancelled');
            
        // Apply period filter to the DB join query
        if ($dateRange[0] && $dateRange[1]) {
            $categoryQuery->whereBetween('orders.created_at', [$dateRange[0], $dateRange[1]]);
        }

        $categoryData = $categoryQuery->select(\DB::raw('COALESCE(categories.name, "Uncategorized") as category'), \DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $categoryLabels = $categoryData->pluck('category')->toArray();
        $categorySales = $categoryData->pluck('total_sold')->toArray();

        // Recent orders
        $recentOrders = $applyPeriod(Order::where($orderScope)->with(['user', 'items']))->latest()->limit(10)->get();

        // Recent tasks
        $recentTasks = $applyPeriod(DesignTask::where('saler_id', $saler->id)->with('customer'))->latest()->limit(5)->get();

        // Task Status Breakdown
        $taskStatus = [
            'pending' => $applyPeriod(DesignTask::where('saler_id', $saler->id)->where('status', 'pending'))->count(),
            'in_progress' => $applyPeriod(DesignTask::where('saler_id', $saler->id)->where('status', 'in_progress'))->count(),
            'in_review' => $applyPeriod(DesignTask::where('saler_id', $saler->id)->where('status', 'in_review'))->count(),
            'completed' => $applyPeriod(DesignTask::where('saler_id', $saler->id)->whereIn('status', ['completed', 'confirmed', 'super_completed']))->count(),
            'printing' => $applyPeriod(DesignTask::where('saler_id', $saler->id)->whereIn('status', ['printing', 'printed']))->count(),
        ];

        // Sales Target Tracking
        $currentTarget = null;
        $targetAmount = 0;
        $targetAchievement = 0;
        $targetRemaining = 0;
        $targetPeriod = '';
        
        // Find active sales target for this seller
        $activeTarget = SalesTarget::where('seller_id', $saler->id)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderBy('created_at', 'desc')
            ->first();
            
        if ($activeTarget) {
            $currentTarget = $activeTarget;
            $targetAmount = $activeTarget->target_amount;
            $targetPeriod = ucfirst($activeTarget->period);
            
            // Calculate achievement based on the target period
            $targetStartDate = $activeTarget->start_date;
            $targetEndDate = $activeTarget->end_date;
            
            $targetRevenue = Order::where(function($q) use ($saler) {
                    $q->where('saler_id', $saler->id)
                      ->orWhereNull('saler_id');
                })
                ->where('approval_status', '!=', 'cancelled')
                ->whereBetween('created_at', [$targetStartDate, $targetEndDate])
                ->sum('total_amount');
                
            $targetTasksRevenue = DesignTask::where('saler_id', $saler->id)
                ->whereBetween('created_at', [$targetStartDate, $targetEndDate])
                ->sum('price');
                
            $totalTargetRevenue = $targetRevenue + $targetTasksRevenue;
            $targetAchievement = $targetAmount > 0 ? round(($totalTargetRevenue / $targetAmount) * 100, 1) : 0;
            $targetRemaining = max(0, $targetAmount - $totalTargetRevenue);
        }
        
        // Invoice count (total orders issued)
        $totalInvoices = $applyPeriod(clone $sharedOrderQuery)->count();

        // Predictive Sales Follow-up Stats
        $followUpStats = [
            'due_today' => \App\Models\Customer::forSaler($saler)->where('follow_up_status', 'Due Today')->count(),
            'overdue' => \App\Models\Customer::forSaler($saler)->where('follow_up_status', 'Overdue')->count(),
            'upcoming' => \App\Models\Customer::forSaler($saler)->where('follow_up_status', 'Upcoming')->count(),
            'total_follow_ups' => \App\Models\Customer::forSaler($saler)->whereIn('follow_up_status', ['Due Today', 'Overdue'])->count(),
        ];

        // Get some urgent follow-ups
        $urgentFollowUps = \App\Models\Customer::forSaler($saler)
            ->whereIn('follow_up_status', ['Due Today', 'Overdue'])
            ->orderBy('priority_ranking', 'desc')
            ->limit(5)
            ->get();


        return view('admin.saler.my-dashboard', compact(
            'totalOrders', 'totalRevenue', 'totalTasks', 'tasksRevenue', 
            'overallPaid', 'overallBalance',
            'customers', 'todayOrders', 'todayRevenue', 'recentOrders', 
            'recentTasks', 'revenueData', 'revenueLabels', 'categoryLabels', 
            'categorySales', 'taskStatus', 'period',
            'currentTarget', 'targetAmount', 'targetAchievement', 'targetRemaining', 'targetPeriod',
            'totalInvoices', 'followUpStats', 'urgentFollowUps'
        ));
    }
}
