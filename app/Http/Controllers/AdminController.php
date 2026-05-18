<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Notification;
use App\Models\DesignTask;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Lead;
use App\Models\SalesTarget;
use App\Notifications\AdminUserCreated;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function dashboard(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        
        // Redirect salespeople to their own dashboard
        if ($user->role === 'saler') {
            return redirect()->route('admin.saler.my-dashboard');
        }
        
        // Handle receptionist dashboard (receptionist or operator)
        if (in_array($user->role, ['receptionist', 'operator'])) {
            return $this->receptionistDashboard($request);
        }
        
        // Handle designer dashboard (designer or operator)
        if ($user->role === 'designer') {
            return $this->designerDashboard($request);
        }

        // Handle delivery dashboard
        if ($user->role === 'delivery') {
            return $this->deliveryDashboard($request);
        }

        // Handle gatekeeper dashboard
        if ($user->role === 'gatekeeper') {
            return redirect()->route('gatekeeper.dashboard');
        }

        // Handle accountant dashboard
        if ($user->role === 'accountant') {
            return redirect()->route('admin.finance.dashboard');
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
                $request->get('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : null,
                $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : null
            ],
            'all' => [null, null],
            default => [now()->startOfMonth(), now()->endOfMonth()]
        };

        $applyPeriod = function($query, $column = 'created_at') use ($dateRange) {
            if ($dateRange[0] && $dateRange[1]) {
                $query->whereBetween($column, [$dateRange[0], $dateRange[1]]);
            }
            return $query;
        };

        // Role-aware constraint for saler
        $applySalerScope = function($query) {
            if (Auth::check() && in_array(Auth::user()->role, ['saler'])) {
                $salerPhone = preg_replace('/[^\d\+]/', '', Auth::user()->phone ?? '');
                $salerPhone = ltrim($salerPhone, '+');
                if (!empty($salerPhone)) {
                    // Match with or without + prefix in notes (same logic as orders index)
                    $query->where(function($q) use ($salerPhone) {
                        $q->where('notes', 'like', "%Assigned to saler: +{$salerPhone}%")
                          ->orWhere('notes', 'like', "%Assigned to saler: {$salerPhone}%");
                    });
                } else {
                    $query->whereRaw('1=0');
                }
            }
            return $query;
        };

        // Calculate revenues first to combine them
        // Sum actual payments received (amount) from Payment model during this period
        $orderRevenue = \App\Models\Payment::activeFinance()->whereNotNull('order_id')
            ->whereBetween('date', [$dateRange[0], $dateRange[1]])
            ->whereHas('order', function($q) use ($applySalerScope) {
                return $applySalerScope($q);
            })->sum('amount') ?? 0;

        $designRevenue = \App\Models\Payment::activeFinance()->whereNotNull('design_task_id')
            ->whereBetween('date', [$dateRange[0], $dateRange[1]])
            ->sum('amount') ?? 0;

        // Get quick stats
        $stats = [
            'total_orders' => $applyPeriod($applySalerScope(\App\Models\Order::query()))->count(),
            'total_products' => $applyPeriod(\App\Models\Product::query())->count(),
            'total_customers' => $applyPeriod(\App\Models\Customer::query())->count(),
            'pending_orders' => $applyPeriod($applySalerScope(\App\Models\Order::query()))->where('approval_status', 'requested')->count(),
            'total_revenue' => $orderRevenue + $designRevenue, // This is "Total Collected" but let's see if we should separate it
            'active_users' => \App\Models\User::where('is_active', true)->whereIn('role', ['retail_customer', 'wholesale_customer'])->count(),
            'new_users' => $applyPeriod(\App\Models\User::query())->count(),
            'new_products' => $applyPeriod(\App\Models\Product::query())->count(),
            'overdue_orders' => 0, 
            'open_tickets' => 0, 
            'pending_tickets' => 0, 
            'in_progress' => $applyPeriod(DesignTask::where('status', 'in_progress'))->count(),
            'pending_review' => $applyPeriod(DesignTask::where('status', 'in_review'))->count(),
            'completed_tasks' => $applyPeriod(DesignTask::activeFinance()->whereIn('status', [DesignTask::STATUS_COMPLETED, DesignTask::STATUS_SUPER_COMPLETED, DesignTask::STATUS_PRINTED]))->count(),
            'total_design_tasks' => $applyPeriod(DesignTask::activeFinance())->count(),
            'design_revenue' => $designRevenue,
            'order_revenue' => $orderRevenue,
            'design_paid' => $designRevenue, // Transaction-based
            'design_pending' => $applyPeriod(DesignTask::activeFinance())->sum('balance') ?? 0, // Still created-period based
            'order_paid' => $orderRevenue, // Transaction-based
            'order_pending' => $applyPeriod($applySalerScope(Order::activeFinance()))->where('approval_status', 'approved')->sum('balance') ?? 0,
            'incomplete_tasks' => $applyPeriod(DesignTask::activeFinance()->whereNotIn('status', ['super_completed', 'completed', 'printed']))->count(),
            'overdue_tasks' => $applyPeriod(DesignTask::activeFinance()->whereNotIn('status', ['super_completed', 'completed', 'printed'])->where('deadline', '<', now()))->count(),
            
            // Comprehensive financials
            // Total Billed = Total Amount of items created in this period
            'total_billed' => ($applyPeriod(DesignTask::activeFinance())->sum('price') ?? 0) + ($applyPeriod($applySalerScope(Order::activeFinance()))->where('approval_status', 'approved')->sum('total_amount') ?? 0),
            
            // Total Debt Collected
            'total_debt_collected' => \App\Models\Payment::activeFinance()->whereBetween('date', [$dateRange[0], $dateRange[1]])->where('is_debt', 1)->sum('amount') ?? 0,
            
            // Total Collected = Payments made in this period
            'total_collected' => $orderRevenue + $designRevenue,
            
            // Total Balance Due = same definition as Daily Report: outstanding on tasks/orders created in period OR that had a payment in period (so dashboard and daily report match)
            'total_balance_due' => $totalBalanceDue = $this->dashboardBalanceDue($dateRange, $applyPeriod, $applySalerScope),

            // Total Revenue = Collected + Outstanding (cash in hand + still owed) so the formula is clear
            'total_revenue' => ($orderRevenue + $designRevenue) + $totalBalanceDue,

            'order_total_revenue' => $applyPeriod($applySalerScope(Order::activeFinance()))->where('approval_status', 'approved')->sum('total_amount') ?? 0,
            'design_total_revenue' => $applyPeriod(DesignTask::activeFinance())->sum('price') ?? 0,
            'total_expenses' => $totalExpenses = ($applyPeriod(Expense::query(), 'date')->sum('amount') ?? 0),
            'net_profit' => ($orderRevenue + $designRevenue) - $totalExpenses,
        ];

        $recentExpenses = $applyPeriod(Expense::query(), 'date')->latest()->limit(10)->get();

        // Calculate Top Salers Performance
        $topSalers = User::whereIn('role', ['saler', 'admin', 'manager'])
            ->get()
            ->map(function($user) use ($applyPeriod) {
                $orderSales = $applyPeriod(\App\Models\Order::where('saler_id', $user->id)->where('approval_status', 'approved'))->sum('total_amount') ?? 0;
                $taskSales = $applyPeriod(DesignTask::where('saler_id', $user->id))->sum('price') ?? 0;
                $totalSales = $orderSales + $taskSales;
                
                // Get most recent target for this user
                $target = SalesTarget::where('seller_id', $user->id)
                    ->where(function($q) {
                        $q->where('end_date', '>=', now())
                          ->orWhereNull('end_date');
                    })
                    ->latest()
                    ->first();
                
                $targetAmount = $target ? $target->target_amount : 0;
                $achievement = $targetAmount > 0 ? ($totalSales / $targetAmount) * 100 : 0;
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'profile_image' => $user->profile_image,
                    'total_sales' => $totalSales,
                    'target_amount' => $targetAmount,
                    'achievement' => min($achievement, 100), // Cap at 100 for progress bar if needed
                    'raw_achievement' => $achievement
                ];
            })
            ->filter(fn($s) => $s['total_sales'] > 0)
            ->sortByDesc('total_sales')
            ->take(5);

        // Calculate Top Designers Performance
        $topDesigners = User::where(function($q) {
                $q->where('role', 'designer')->orWhere('role', 'operator');
            })
            ->get()
            ->map(function($user) use ($applyPeriod) {
                $completedTasks = $applyPeriod(DesignTask::where('designer_id', $user->id)
                    ->whereIn('status', [DesignTask::STATUS_COMPLETED, DesignTask::STATUS_SUPER_COMPLETED, DesignTask::STATUS_PRINTED]))->count();
                
                $totalDesignValue = $applyPeriod(DesignTask::where('designer_id', $user->id)
                    ->whereIn('status', [DesignTask::STATUS_COMPLETED, DesignTask::STATUS_SUPER_COMPLETED, DesignTask::STATUS_PRINTED]))->sum('price') ?? 0;
                
                // For designers, we might use a target based on task count or value
                // Reusing SalesTarget as a "Production Target" if available
                $target = SalesTarget::where('seller_id', $user->id) // Reusing the same table for all staff targets
                    ->where(function($q) {
                        $q->where('end_date', '>=', now())
                          ->orWhereNull('end_date');
                    })
                    ->latest()
                    ->first();
                
                $targetAmount = $target ? $target->target_amount : 0;
                $achievement = $targetAmount > 0 ? ($totalDesignValue / $targetAmount) * 100 : 0;
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'completed_tasks' => $completedTasks,
                    'total_value' => $totalDesignValue,
                    'target_amount' => $targetAmount,
                    'achievement' => min($achievement, 100),
                    'raw_achievement' => $achievement
                ];
            })
            ->filter(fn($d) => $d['completed_tasks'] > 0)
            ->sortByDesc('completed_tasks')
            ->take(5);

        // Get period-specific stats for Order Distribution Chart
        $periodStats = [
            'orders' => $applyPeriod($applySalerScope(\App\Models\Order::query()))->count(),
            'revenue' => $applyPeriod($applySalerScope(\App\Models\Order::where('approval_status', 'approved')))->sum('amount_paid'),
            'approved' => $applyPeriod($applySalerScope(\App\Models\Order::where('approval_status', 'approved')))->count(),
            'pending' => $applyPeriod($applySalerScope(\App\Models\Order::where('approval_status', 'requested')))->count(),
            'cancelled' => $applyPeriod($applySalerScope(\App\Models\Order::where('approval_status', 'cancelled')))->count(),
        ];

        // Legacy comparison stats (Today vs Yesterday) - keeping for the small badges
        $today = now()->startOfDay();
        $todayStats = [
            'orders' => $applySalerScope(\App\Models\Order::whereDate('created_at', $today))->count(),
            'revenue' => $applySalerScope(\App\Models\Order::whereDate('created_at', $today)->where('approval_status', 'approved'))->sum('amount_paid'),
            'approved' => $applySalerScope(\App\Models\Order::whereDate('created_at', $today)->where('approval_status', 'approved'))->count(),
            'pending' => $applySalerScope(\App\Models\Order::whereDate('created_at', $today)->where('approval_status', 'requested'))->count(),
            'cancelled' => $applySalerScope(\App\Models\Order::whereDate('created_at', $today)->where('approval_status', 'cancelled'))->count(),
        ];

        $yesterday = now()->subDay()->startOfDay();
        $yesterdayStats = [
            'orders' => $applySalerScope(\App\Models\Order::whereDate('created_at', $yesterday))->count(),
            'revenue' => $applySalerScope(\App\Models\Order::whereDate('created_at', $yesterday)->where('approval_status', 'approved'))->sum('amount_paid'),
            'approved' => $applySalerScope(\App\Models\Order::whereDate('created_at', $yesterday)->where('approval_status', 'approved'))->count(),
            'pending' => $applySalerScope(\App\Models\Order::whereDate('created_at', $yesterday)->where('approval_status', 'requested'))->count(),
            'cancelled' => $applySalerScope(\App\Models\Order::whereDate('created_at', $yesterday)->where('approval_status', 'cancelled'))->count(),
        ];

        // Get monthly orders data for current year
        $currentYear = now()->year;
        $monthlyOrdersCurrentYear = [];
        $monthlyRevenueCurrentYear = [];
        $combinedMonthlyRevenueCurrentYear = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyOrdersCurrentYear[] = $applySalerScope(\App\Models\Order::whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $month))
                ->count();
            
            // Transaction-based Revenue (Payments)
            $orderRev = \App\Models\Payment::whereYear('date', $currentYear)
                ->whereMonth('date', $month)
                ->whereHas('order', function($q) use ($applySalerScope) {
                    return $applySalerScope($q);
                })->sum('amount') ?? 0;
            
            $designRev = \App\Models\Payment::whereYear('date', $currentYear)
                ->whereMonth('date', $month)
                ->whereNotNull('design_task_id')
                ->sum('amount') ?? 0;

            $monthlyRevenueCurrentYear[] = $orderRev;
            $combinedMonthlyRevenueCurrentYear[] = $orderRev + $designRev;
        }

        // Get monthly orders data for previous year
        $previousYear = $currentYear - 1;
        $monthlyOrdersPreviousYear = [];
        $monthlyRevenuePreviousYear = [];
        $combinedMonthlyRevenuePreviousYear = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyOrdersPreviousYear[] = $applySalerScope(\App\Models\Order::whereYear('created_at', $previousYear)
                ->whereMonth('created_at', $month))
                ->count();
            
            // Transaction-based Revenue (Payments)
            $orderRevPrev = \App\Models\Payment::whereYear('date', $previousYear)
                ->whereMonth('date', $month)
                ->whereHas('order', function($q) use ($applySalerScope) {
                    return $applySalerScope($q);
                })->sum('amount') ?? 0;
            
            $designRevPrev = \App\Models\Payment::whereYear('date', $previousYear)
                ->whereMonth('date', $month)
                ->whereNotNull('design_task_id')
                ->sum('amount') ?? 0;

            $monthlyRevenuePreviousYear[] = $orderRevPrev;
            $combinedMonthlyRevenuePreviousYear[] = $orderRevPrev + $designRevPrev;
        }

        // Dynamic Revenue Trend based on Period
        $revenueLabels = []; 
        $revenueDataCurrent = [];
        $revenueDataPrevious = [];
        $combinedRevenueDataPrevious = [];
        $profitDataCurrent = [];
        $profitDataPrevious = [];
        $expenseDataCurrent = [];

        if ($period == 'today') {
            for ($i = 11; $i >= 0; $i--) {
                $hour = now()->subHours($i * 2);
                $prevHour = $hour->copy()->subDay();
                $revenueLabels[] = $hour->format('H:00');
                
                // Orders
                $revenueDataCurrent[] = $applySalerScope(\App\Models\Order::whereBetween('created_at', [$hour->copy()->startOfHour(), $hour->copy()->addHour()->endOfHour()])->where('approval_status', 'approved'))->sum('amount_paid') ?? 0;
                $revenueDataPrevious[] = $applySalerScope(\App\Models\Order::whereBetween('created_at', [$prevHour->copy()->startOfHour(), $prevHour->copy()->addHour()->endOfHour()])->where('approval_status', 'approved'))->sum('amount_paid') ?? 0;
                
                // Design Tasks (Combined)
                $designRevCurrent = DesignTask::whereBetween('created_at', [$hour->copy()->startOfHour(), $hour->copy()->addHour()->endOfHour()])->sum('amount_paid') ?? 0;
                $designRevPrev = DesignTask::whereBetween('created_at', [$prevHour->copy()->startOfHour(), $prevHour->copy()->addHour()->endOfHour()])->sum('amount_paid') ?? 0;
                
                
                $combinedRevenueDataCurrent[] = $revC = $revenueDataCurrent[count($revenueDataCurrent)-1] + $designRevCurrent;
                $combinedRevenueDataPrevious[] = $revP = $revenueDataPrevious[count($revenueDataPrevious)-1] + $designRevPrev;

                $expC = Expense::whereBetween('date', [$hour->copy()->startOfHour(), $hour->copy()->addHour()->endOfHour()])->sum('amount') ?? 0;
                $expP = Expense::whereBetween('date', [$prevHour->copy()->startOfHour(), $prevHour->copy()->addHour()->endOfHour()])->sum('amount') ?? 0;
                
                $expenseDataCurrent[] = $expC;
                $profitDataCurrent[] = $revC - $expC;
                $profitDataPrevious[] = $revP - $expP;
            }
        } elseif ($period == 'week' || $period == 'month') {
            $days = $period == 'week' ? 7 : 30;
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $prevDate = $date->copy()->subMonth(); // Comparing to last month for sequence
                $revenueLabels[] = $date->format('M d');
                
                $revenueDataCurrent[] = $applySalerScope(\App\Models\Order::whereDate('created_at', $date->format('Y-m-d'))->where('approval_status', 'approved'))->sum('amount_paid') ?? 0;
                $revenueDataPrevious[] = $applySalerScope(\App\Models\Order::whereDate('created_at', $prevDate->format('Y-m-d'))->where('approval_status', 'approved'))->sum('amount_paid') ?? 0;
                
                $designRevCurrent = DesignTask::whereDate('created_at', $date->format('Y-m-d'))->sum('amount_paid') ?? 0;
                $designRevPrev = DesignTask::whereDate('created_at', $prevDate->format('Y-m-d'))->sum('amount_paid') ?? 0;
                
                
                $combinedRevenueDataCurrent[] = $revC = $revenueDataCurrent[count($revenueDataCurrent)-1] + $designRevCurrent;
                $combinedRevenueDataPrevious[] = $revP = $revenueDataPrevious[count($revenueDataPrevious)-1] + $designRevPrev;

                $expC = Expense::whereDate('date', $date->format('Y-m-d'))->sum('amount') ?? 0;
                $expP = Expense::whereDate('date', $prevDate->format('Y-m-d'))->sum('amount') ?? 0;

                $expenseDataCurrent[] = $expC;
                $profitDataCurrent[] = $revC - $expC;
                $profitDataPrevious[] = $revP - $expP;
            }
        } else {
            $months = ($period == '2_years') ? 24 : 12;
            for ($i = $months - 1; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $prevMonth = $month->copy()->subYear();
                $revenueLabels[] = $month->format('M Y');
                
                $revenueDataCurrent[] = $applySalerScope(\App\Models\Order::whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->where('approval_status', 'approved'))->sum('amount_paid') ?? 0;
                $revenueDataPrevious[] = $applySalerScope(\App\Models\Order::whereYear('created_at', $prevMonth->year)->whereMonth('created_at', $prevMonth->month)->where('approval_status', 'approved'))->sum('amount_paid') ?? 0;
                
                $designRevCurrent = DesignTask::whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->sum('amount_paid') ?? 0;
                $designRevPrev = DesignTask::whereYear('created_at', $prevMonth->year)->whereMonth('created_at', $prevMonth->month)->sum('amount_paid') ?? 0;
                
                
                $combinedRevenueDataCurrent[] = $revC = $revenueDataCurrent[count($revenueDataCurrent)-1] + $designRevCurrent;
                $combinedRevenueDataPrevious[] = $revP = $revenueDataPrevious[count($revenueDataPrevious)-1] + $designRevPrev;

                $expC = Expense::whereYear('date', $month->year)->whereMonth('date', $month->month)->sum('amount') ?? 0;
                $expP = Expense::whereYear('date', $prevMonth->year)->whereMonth('date', $prevMonth->month)->sum('amount') ?? 0;

                $expenseDataCurrent[] = $expC;
                $profitDataCurrent[] = $revC - $expC;
                $profitDataPrevious[] = $revP - $expP;
            }
        }
        
        $profit_chart_data = [
            'labels' => $revenueLabels,
            'current' => $profitDataCurrent,
            'previous' => $profitDataPrevious,
            'expenses' => $expenseDataCurrent,
            'revenue' => $combinedRevenueDataCurrent
        ];
        
        $revenue_chart_data = [
            'labels' => $revenueLabels,
            'current' => $revenueDataCurrent,
            'previous' => $revenueDataPrevious
        ];
        
        $combined_revenue_chart_data = [
            'labels' => $revenueLabels,
            'current' => $combinedRevenueDataCurrent,
            'previous' => $combinedRevenueDataPrevious
        ];

        // Get recent orders (filtered by period)
        $recentOrders = $applyPeriod(\App\Models\Order::with(['user', 'items.product'])->orderBy('created_at', 'desc'))->limit(10)->get();

        // Get recent assigned design tasks (filtered by period)
        $recentAssignedTasks = $applyPeriod(DesignTask::with(['customer', 'designer', 'receptionist'])
            ->whereNotNull('designer_id')
            ->latest('updated_at'))
            ->limit(10)
            ->get();
        
        // Low stock products (Global)
        $lowStockProducts = \App\Models\Product::with(['images'])
            ->where('stock', '<=', 10)
            ->where('status', 'active')
            ->limit(6)
            ->get();
        
        // Get sales by category (from order items with approved orders)
        $categorySalesQuery = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('enhanced_products', 'order_items.product_id', '=', 'enhanced_products.id')
            ->where('orders.approval_status', 'approved');
        $categorySalesQuery = $applyPeriod($categorySalesQuery, 'orders.created_at');
        $categorySalesQuery->selectRaw('enhanced_products.category, SUM(order_items.quantity) as total_quantity')
            ->groupBy('enhanced_products.category')
            ->orderBy('total_quantity', 'desc')
            ->limit(10);
        
        // Apply saler scope if needed
        if (Auth::check() && in_array(Auth::user()->role, ['saler'])) {
            $salerPhone = preg_replace('/[^\d\+]/', '', Auth::user()->phone ?? '');
            $salerPhone = ltrim($salerPhone, '+');
            if (!empty($salerPhone)) {
                $categorySalesQuery->where(function($q) use ($salerPhone) {
                    $q->where('orders.notes', 'like', "%Assigned to saler: +{$salerPhone}%")
                      ->orWhere('orders.notes', 'like', "%Assigned to saler: {$salerPhone}%");
                });
            } else {
                $categorySalesQuery->whereRaw('1=0');
            }
        }
        
        $categorySales = $categorySalesQuery->get();
        
        $categoryLabels = $categorySales->pluck('category')->map(function($cat) {
            return $cat ?: 'Uncategorized';
        })->toArray();
        $categoryData = $categorySales->pluck('total_quantity')->toArray();
        
        $category_data = [
            'labels' => $categoryLabels,
            'data' => $categoryData
        ];
        
        // Get top selling products (by quantity sold in approved orders)
        $topProductsQuery = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.approval_status', 'approved');
        $topProductsQuery = $applyPeriod($topProductsQuery, 'orders.created_at');
        $topProductsQuery->selectRaw('order_items.product_id, SUM(order_items.quantity) as sales, AVG(order_items.unit_price) as avg_price')
            ->groupBy('order_items.product_id')
            ->orderBy('sales', 'desc')
            ->limit(6);
        
        // Apply saler scope if needed
        if (Auth::check() && in_array(Auth::user()->role, ['saler'])) {
            $salerPhone = preg_replace('/[^\d\+]/', '', Auth::user()->phone ?? '');
            $salerPhone = ltrim($salerPhone, '+');
            if (!empty($salerPhone)) {
                $topProductsQuery->where(function($q) use ($salerPhone) {
                    $q->where('orders.notes', 'like', "%Assigned to saler: +{$salerPhone}%")
                      ->orWhere('orders.notes', 'like', "%Assigned to saler: {$salerPhone}%");
                });
            } else {
                $topProductsQuery->whereRaw('1=0');
            }
        }
        
        $topProductsData = $topProductsQuery->get();
        
        // Format top products with images and product details
        $top_products = $topProductsData->map(function($item) {
            $product = \App\Models\EnhancedProduct::with('images')->find($item->product_id);
            
            if (!$product) {
                return null;
            }
            
            $firstImage = $product->images->first();
            
            return (object)[
                'id' => $product->id,
                'name' => $product->name,
                'sales' => (int)$item->sales,
                'price' => (float)$item->avg_price,
                'image' => $firstImage ? $firstImage->image_path : null
            ];
        })->filter();

        // 1. Design Task Status Distribution
        // 1. Design Task Status Distribution
        $designTaskStatus = [
            'pending' => $applyPeriod(DesignTask::where('status', DesignTask::STATUS_PENDING))->count(),
            'in_progress' => $applyPeriod(DesignTask::whereIn('status', [DesignTask::STATUS_IN_PROGRESS, DesignTask::STATUS_CONFIRMED]))->count(),
            'in_review' => $applyPeriod(DesignTask::where('status', DesignTask::STATUS_IN_REVIEW))->count(),
            'printing' => $applyPeriod(DesignTask::where('status', DesignTask::STATUS_PRINTING))->count(),
            'printed' => $applyPeriod(DesignTask::where('status', DesignTask::STATUS_PRINTED))->count(),
            'completed' => $applyPeriod(DesignTask::where('status', DesignTask::STATUS_COMPLETED))->count(),
            'super_completed' => $applyPeriod(DesignTask::where('status', DesignTask::STATUS_SUPER_COMPLETED))->count(),
            'delivered' => $applyPeriod(DesignTask::where('delivery_status', 'delivered'))->count(),
            'rejected' => $applyPeriod(DesignTask::where('status', DesignTask::STATUS_REJECTED))->count(),
        ];

        // 2. Dynamic Design Task Pulse based on Period
        $designTrendLabels = []; $tasksCreatedTrend = []; $tasksCompletedTrend = [];
        if ($period == 'today') {
            for ($i = 11; $i >= 0; $i--) {
                $hour = now()->subHours($i * 2);
                $designTrendLabels[] = $hour->format('H:00');
                $tasksCreatedTrend[] = DesignTask::whereBetween('created_at', [$hour->copy()->startOfHour(), $hour->copy()->addHour()->endOfHour()])->count();
                $tasksCompletedTrend[] = DesignTask::whereIn('status', ['completed', 'confirmed', 'super_completed'])
                    ->whereBetween('completed_at', [$hour->copy()->startOfHour(), $hour->copy()->addHour()->endOfHour()])->count();
            }
        } elseif ($period == 'week' || $period == 'month') {
            $days = $period == 'week' ? 7 : 30;
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $designTrendLabels[] = $date->format('M d');
                $tasksCreatedTrend[] = DesignTask::whereDate('created_at', $date->format('Y-m-d'))->count();
                $tasksCompletedTrend[] = DesignTask::whereIn('status', ['completed', 'confirmed', 'super_completed'])
                    ->whereDate('completed_at', $date->format('Y-m-d'))->count();
            }
        } else {
            $months = ($period == '2_years') ? 24 : 12;
            for ($i = $months - 1; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $designTrendLabels[] = $month->format('M Y');
                $tasksCreatedTrend[] = DesignTask::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)->count();
                $tasksCompletedTrend[] = DesignTask::whereIn('status', ['completed', 'confirmed', 'super_completed'])
                    ->whereYear('completed_at', $month->year)->whereMonth('completed_at', $month->month)->count();
            }
        }

        // ── Daily Lead Stats ──────────────────────────────────────────────────
        $leadScope = fn($q) => $user->role === 'saler'
            ? $q->where('assigned_seller_id', $user->id)
            : $q;

        $leadStats = [
            'today_new'       => $leadScope(Lead::whereDate('created_at', today()))->count(),
            'today_follow_ups'=> $leadScope(Lead::dueToday())->count(),
            'today_conversions'=> $leadScope(Lead::where('status', 'converted')
                ->whereDate('updated_at', today()))->count(),
            'overdue_count'   => $leadScope(Lead::overdue())->count(),
            'total_pending'   => $leadScope(Lead::where('status', 'pending'))->count(),
            'week_new'        => $leadScope(Lead::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))->count(),
        ];

        return view('admin.dashboard', compact(
            'stats',
            'todayStats',
            'yesterdayStats',
            'periodStats',
            'topDesigners', 
            'lowStockProducts',
            'monthlyOrdersCurrentYear',
            'monthlyOrdersPreviousYear',
            'monthlyRevenueCurrentYear',
            'monthlyRevenuePreviousYear',
            'combinedMonthlyRevenueCurrentYear',
            'combinedMonthlyRevenuePreviousYear',
            'currentYear',
            'previousYear',
            'recentAssignedTasks',
            'topSalers',
            'revenue_chart_data',
            'combined_revenue_chart_data',
            'profit_chart_data',
            'category_data',
            'designTaskStatus',
            'designTrendLabels',
            'tasksCreatedTrend',
            'tasksCompletedTrend',
            'period',
            'recentExpenses',
            'leadStats'
        ));
    }

    /**
     * Balance due using same definition as Finance Daily Report: sum of current balance for
     * tasks/orders that are either created in the period OR had a payment recorded in the period.
     * This keeps dashboard "Balance Due" consistent with Daily Report "Total Outstanding".
     */
    private function dashboardBalanceDue(array $dateRange, callable $applyPeriod, callable $applySalerScope): float
    {
        $from = $dateRange[0] ?? null;
        $to = $dateRange[1] ?? null;
        if (!$from || !$to) {
            return 0;
        }
        $taskIdsCreated = DesignTask::activeFinance()->whereBetween('created_at', [$from, $to])->pluck('id')->toArray();
        $taskIdsFromPayments = Payment::activeFinance()->whereBetween('date', [$from, $to])
            ->whereNotNull('design_task_id')
            ->pluck('design_task_id')
            ->unique()
            ->values()
            ->toArray();
        $taskIds = array_values(array_unique(array_merge($taskIdsCreated, $taskIdsFromPayments)));

        $orderIdsCreated = $applyPeriod(Order::activeFinance()->where('approval_status', 'approved'))->pluck('id')->toArray();
        $orderIdsFromPayments = Payment::activeFinance()->whereBetween('date', [$from, $to])
            ->whereNotNull('order_id')
            ->pluck('order_id')
            ->unique()
            ->values()
            ->toArray();
        $orderIds = array_values(array_unique(array_merge($orderIdsCreated, $orderIdsFromPayments)));

        $taskBalance = empty($taskIds) ? 0 : DesignTask::activeFinance()->whereIn('id', $taskIds)->sum('balance');
        $orderBalance = empty($orderIds) ? 0 : Order::activeFinance()->whereIn('id', $orderIds)->where('approval_status', 'approved')->sum('balance');

        return (float) ($taskBalance + $orderBalance);
    }

    /**
     * Display receptionist dashboard.
     */
    private function receptionistDashboard(Request $request): View
    {
        $user = Auth::user();
        $period = $request->get('period', 'today');
        
        $dateRange = match($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            '6_months' => [now()->subMonths(6), now()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            '2_years' => [now()->subYears(2), now()],
            'custom' => [
                $request->get('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : null,
                $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : null
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

        // For operator role, get tasks where they are receptionist OR designer
        $isOperator = $user->role === 'operator';
        
        if ($isOperator) {
            // Operator sees all tasks globally
            $stats = [
                'total_tasks' => $applyPeriod(DesignTask::query())->count(),
                'pending_assignment' => $applyPeriod(DesignTask::whereNull('designer_id')
                    ->where('status', DesignTask::STATUS_PENDING))->count(),
                'assigned_tasks' => $applyPeriod(DesignTask::whereNotNull('designer_id')
                    ->whereIn('status', [DesignTask::STATUS_PENDING, DesignTask::STATUS_IN_PROGRESS, DesignTask::STATUS_IN_REVIEW, DesignTask::STATUS_COMPLETED, DesignTask::STATUS_CONFIRMED, DesignTask::STATUS_PRINTING, DesignTask::STATUS_PRINTED]))->count(),
                'in_progress' => $applyPeriod(DesignTask::whereIn('status', [DesignTask::STATUS_IN_PROGRESS, DesignTask::STATUS_PRINTING, DesignTask::STATUS_PRINTED]))->count(),
                'pending_review' => $applyPeriod(DesignTask::whereIn('status', [DesignTask::STATUS_IN_REVIEW, DesignTask::STATUS_COMPLETED, DesignTask::STATUS_CONFIRMED]))->count(),
                'completed_tasks' => $applyPeriod(DesignTask::whereIn('status', [DesignTask::STATUS_SUPER_COMPLETED]))->count(),
                'completed' => $applyPeriod(DesignTask::whereIn('status', [DesignTask::STATUS_SUPER_COMPLETED]))->count(),
                'monthly_customers' => $applyPeriod(Customer::query())->count(),
                'monthly_tasks' => $applyPeriod(DesignTask::query())->count(),
            ];
            
            $recentTasks = $applyPeriod(DesignTask::with(['customer', 'designer', 'receptionist']))
                ->latest()
                ->limit(10)
                ->get();
            
            $pendingTasks = $applyPeriod(DesignTask::with(['customer'])
                ->whereNull('designer_id')
                ->where('status', DesignTask::STATUS_PENDING))
                ->orderBy('priority', 'asc')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            $tasksByStatus = [
                'pending' => $applyPeriod(DesignTask::where('status', 'pending'))->count(),
                'in_progress' => $applyPeriod(DesignTask::where('status', 'in_progress'))->count(),
                'in_review' => $applyPeriod(DesignTask::where('status', 'in_review'))->count(),
                'completed' => $applyPeriod(DesignTask::whereIn('status', ['completed', 'confirmed', 'super_completed']))->count(),
                'printing' => $applyPeriod(DesignTask::whereIn('status', ['printing', 'printed']))->count(),
            ];
            
            $barChartLabels = []; $barChartData = [];
            if ($period == 'today') {
                for ($i = 11; $i >= 0; $i--) {
                    $hour = now()->subHours($i * 2);
                    $barChartLabels[] = $hour->format('H:00');
                    $barChartData[] = DesignTask::whereBetween('created_at', [$hour->copy()->startOfHour(), $hour->copy()->addHour()->endOfHour()])->count();
                }
            } elseif ($period == 'week' || $period == 'month') {
                $days = $period == 'week' ? 7 : 30;
                for ($i = $days - 1; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $barChartLabels[] = $date->format('M d');
                    $barChartData[] = DesignTask::whereDate('created_at', $date->format('Y-m-d'))->count();
                }
            } elseif ($period == '2_years') {
                for ($i = 23; $i >= 0; $i--) {
                    $month = now()->subMonths($i);
                    $barChartLabels[] = $month->format('M Y');
                    $barChartData[] = DesignTask::where('status', DesignTask::STATUS_COMPLETED)
                      ->whereYear('completed_at', $month->year)
                      ->whereMonth('completed_at', $month->month)->count();
                }
            } else { // 6_months, year, all (default to months)
                $months = ($period == 'year') ? 12 : 6;
                for ($i = $months - 1; $i >= 0; $i--) {
                    $month = now()->subMonths($i);
                    $barChartLabels[] = $month->format('M Y');
                    $barChartData[] = DesignTask::where('status', DesignTask::STATUS_COMPLETED)
                      ->whereYear('completed_at', $month->year)
                      ->whereMonth('completed_at', $month->month)->count();
                }
            }
            // Sync all trend vars
            $last7Days = $barChartLabels;
            $tasksCompletedData = $barChartData;
            $last6Months = $barChartLabels;
            $monthlyCompletedData = $barChartData;
        } else {
            // Receptionist only sees their own tasks
            $stats = [
                'total_tasks' => $applyPeriod(DesignTask::where('receptionist_id', $user->id))->count(),
                'pending_assignment' => $applyPeriod(DesignTask::where('receptionist_id', $user->id)
                    ->whereNull('designer_id')
                    ->where('status', DesignTask::STATUS_PENDING))->count(),
                'assigned_tasks' => $applyPeriod(DesignTask::where('receptionist_id', $user->id)
                    ->whereNotNull('designer_id')
                    ->whereIn('status', [DesignTask::STATUS_PENDING, DesignTask::STATUS_IN_PROGRESS, DesignTask::STATUS_IN_REVIEW, DesignTask::STATUS_COMPLETED, DesignTask::STATUS_CONFIRMED, DesignTask::STATUS_PRINTING, DesignTask::STATUS_PRINTED]))->count(),
                'in_progress' => $applyPeriod(DesignTask::where('receptionist_id', $user->id)
                    ->whereIn('status', [DesignTask::STATUS_IN_PROGRESS, DesignTask::STATUS_PRINTING, DesignTask::STATUS_PRINTED]))->count(),
                'pending_review' => $applyPeriod(DesignTask::where('receptionist_id', $user->id)
                    ->whereIn('status', [DesignTask::STATUS_IN_REVIEW, DesignTask::STATUS_COMPLETED, DesignTask::STATUS_CONFIRMED]))->count(),
                'completed_tasks' => $applyPeriod(DesignTask::where('receptionist_id', $user->id)
                    ->whereIn('status', [DesignTask::STATUS_SUPER_COMPLETED]))->count(),
                'completed' => $applyPeriod(DesignTask::where('receptionist_id', $user->id)
                    ->whereIn('status', [DesignTask::STATUS_SUPER_COMPLETED]))->count(),
                'monthly_customers' => $applyPeriod(Customer::query())->count(),
                'monthly_tasks' => $applyPeriod(DesignTask::where('receptionist_id', $user->id))->count(),
            ];
            
            $recentTasks = $applyPeriod(DesignTask::with(['customer', 'designer'])
                ->where('receptionist_id', $user->id))
                ->latest()
                ->limit(10)
                ->get();
            
            $pendingTasks = $applyPeriod(DesignTask::with(['customer'])
                ->where('receptionist_id', $user->id)
                ->whereNull('designer_id')
                ->where('status', DesignTask::STATUS_PENDING))
                ->orderBy('priority', 'asc')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            $tasksByStatus = [
                'pending' => $applyPeriod(DesignTask::where('receptionist_id', $user->id)->where('status', 'pending'))->count(),
                'in_progress' => $applyPeriod(DesignTask::where('receptionist_id', $user->id)->where('status', 'in_progress'))->count(),
                'in_review' => $applyPeriod(DesignTask::where('receptionist_id', $user->id)->where('status', 'in_review'))->count(),
                'completed' => $applyPeriod(DesignTask::where('receptionist_id', $user->id)->whereIn('status', ['completed', 'confirmed', 'super_completed']))->count(),
                'printing' => $applyPeriod(DesignTask::where('receptionist_id', $user->id)->whereIn('status', ['printing', 'printed']))->count(),
            ];
            
            $barChartLabels = []; $barChartData = [];
            if ($period == 'today') {
                for ($i = 11; $i >= 0; $i--) {
                    $hour = now()->subHours($i * 2);
                    $barChartLabels[] = $hour->format('H:00');
                    $barChartData[] = DesignTask::where('receptionist_id', $user->id)->whereBetween('created_at', [$hour->copy()->startOfHour(), $hour->copy()->addHour()->endOfHour()])->count();
                }
            } elseif ($period == 'week' || $period == 'month') {
                $days = $period == 'week' ? 7 : 30;
                for ($i = $days - 1; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $barChartLabels[] = $date->format('M d');
                    $barChartData[] = DesignTask::where('receptionist_id', $user->id)->whereDate('created_at', $date->format('Y-m-d'))->count();
                }
            } elseif ($period == '2_years') {
                for ($i = 23; $i >= 0; $i--) {
                    $month = now()->subMonths($i);
                    $barChartLabels[] = $month->format('M Y');
                    $barChartData[] = DesignTask::where('receptionist_id', $user->id)->where('status', 'completed')
                        ->whereYear('completed_at', $month->year)->whereMonth('completed_at', $month->month)->count();
                }
            } else {
                $months = ($period == 'year') ? 12 : 6;
                for ($i = $months - 1; $i >= 0; $i--) {
                    $month = now()->subMonths($i);
                    $barChartLabels[] = $month->format('M Y');
                    $barChartData[] = DesignTask::where('receptionist_id', $user->id)->where('status', 'completed')
                        ->whereYear('completed_at', $month->year)->whereMonth('completed_at', $month->month)->count();
                }
            }
            // Sync all trend vars
            $last7Days = $barChartLabels;
            $tasksCompletedData = $barChartData;
            $last6Months = $barChartLabels;
            $monthlyCompletedData = $barChartData;
        }
        
        $recentCustomers = $applyPeriod(Customer::query())->latest()->limit(5)->get();
        
        $view = $isOperator ? 'admin.dashboards.operator' : 'admin.dashboards.receptionist';

        return view($view, compact(
            'stats', 'recentTasks', 'pendingTasks', 'tasksByStatus', 
            'last7Days', 'tasksCompletedData', 'last6Months', 
            'monthlyCompletedData', 'recentCustomers', 'period'
        ));
    }

    /**
     * Display designer dashboard.
     */
    private function designerDashboard(Request $request): View
    {
        $user = Auth::user();
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
                $request->get('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : null,
                $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : null
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

        // Get task statistics (operator can also see tasks where they are receptionist)
        $isOperator = $user->role === 'operator';
        
        if ($isOperator) {
            $stats = [
                'assigned_tasks' => $applyPeriod(DesignTask::whereNotNull('designer_id')
                    ->whereIn('status', [DesignTask::STATUS_PENDING, DesignTask::STATUS_IN_PROGRESS, DesignTask::STATUS_IN_REVIEW, DesignTask::STATUS_COMPLETED, DesignTask::STATUS_CONFIRMED, DesignTask::STATUS_PRINTING, DesignTask::STATUS_PRINTED]))->count(),
                'in_progress' => $applyPeriod(DesignTask::whereIn('status', [DesignTask::STATUS_IN_PROGRESS, DesignTask::STATUS_PRINTING, DesignTask::STATUS_PRINTED]))->count(),
                'pending_review' => $applyPeriod(DesignTask::whereIn('status', [DesignTask::STATUS_IN_REVIEW, DesignTask::STATUS_COMPLETED, DesignTask::STATUS_CONFIRMED]))->count(),
                'completed' => $applyPeriod(DesignTask::whereIn('status', [DesignTask::STATUS_SUPER_COMPLETED]))->count(),
                'completed_tasks' => $applyPeriod(DesignTask::whereIn('status', [DesignTask::STATUS_SUPER_COMPLETED]))->count(),
            ];
            
            $recentTasks = $applyPeriod(DesignTask::with(['customer', 'receptionist', 'designer']))
                ->latest()
                ->limit(10)
                ->get();
            
            $inProgressTasks = $applyPeriod(DesignTask::with(['customer', 'receptionist']))
                ->where('status', 'in_progress')
                ->orderBy('priority', 'asc')
                ->orderBy('deadline', 'asc')
                ->limit(5)
                ->get();
            
            $overdueTasks = $applyPeriod(DesignTask::with(['customer', 'receptionist'])
                ->where('status', '!=', 'completed')
                ->whereNotNull('deadline')
                ->where('deadline', '<', now()))
                ->orderBy('deadline', 'asc')
                ->limit(5)
                ->get();
            
            $tasksByStatus = [
                'pending' => $applyPeriod(DesignTask::query())->count(),
                'in_progress' => $applyPeriod(DesignTask::where('status', 'in_progress'))->count(),
                'in_review' => $applyPeriod(DesignTask::where('status', 'in_review'))->count(),
                'completed' => $applyPeriod(DesignTask::whereIn('status', ['completed', 'confirmed', 'super_completed']))->count(),
                'printing' => $applyPeriod(DesignTask::whereIn('status', ['printing', 'printed']))->count(),
            ];
            
            $last7Days = []; $tasksCompletedData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $last7Days[] = $date->format('M d');
                $tasksCompletedData[] = DesignTask::whereIn('status', ['completed', 'confirmed', 'printed', 'super_completed'])
                  ->whereDate('completed_at', $date->format('Y-m-d'))
                  ->count();
            }
            
            $barChartLabels = []; $barChartData = [];
            if ($period == 'today') {
                for ($i = 11; $i >= 0; $i--) {
                    $hour = now()->subHours($i * 2);
                    $barChartLabels[] = $hour->format('H:00');
                    $barChartData[] = DesignTask::whereBetween('created_at', [$hour->copy()->startOfHour(), $hour->copy()->addHour()->endOfHour()])->count();
                }
            } elseif ($period == 'week' || $period == 'month') {
                $days = $period == 'week' ? 7 : 30;
                for ($i = $days - 1; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $barChartLabels[] = $date->format('M d');
                    $barChartData[] = DesignTask::whereDate('created_at', $date->format('Y-m-d'))->count();
                }
            } elseif ($period == '2_years') {
                for ($i = 23; $i >= 0; $i--) {
                    $month = now()->subMonths($i);
                    $barChartLabels[] = $month->format('M Y');
                    $barChartData[] = DesignTask::where('status', 'completed')
                      ->whereYear('completed_at', $month->year)
                      ->whereMonth('completed_at', $month->month)->count();
                }
            } else {
                $months = ($period == 'year') ? 12 : 6;
                for ($i = $months - 1; $i >= 0; $i--) {
                    $month = now()->subMonths($i);
                    $barChartLabels[] = $month->format('M Y');
                    $barChartData[] = DesignTask::where('status', 'completed')
                      ->whereYear('completed_at', $month->year)
                      ->whereMonth('completed_at', $month->month)->count();
                }
            }
            // Sync all trend vars
            $last7Days = $barChartLabels;
            $tasksCompletedData = $barChartData;
            $last6Months = $barChartLabels;
            $monthlyCompletedData = $barChartData;
        } else {
            $stats = [
                'assigned_tasks' => $applyPeriod(DesignTask::where('designer_id', $user->id)
                    ->whereIn('status', [DesignTask::STATUS_PENDING, DesignTask::STATUS_IN_PROGRESS, DesignTask::STATUS_IN_REVIEW, DesignTask::STATUS_COMPLETED, DesignTask::STATUS_CONFIRMED, DesignTask::STATUS_PRINTING, DesignTask::STATUS_PRINTED]))->count(),
                'in_progress' => $applyPeriod(DesignTask::where('designer_id', $user->id)
                    ->whereIn('status', [DesignTask::STATUS_IN_PROGRESS, DesignTask::STATUS_PRINTING, DesignTask::STATUS_PRINTED]))->count(),
                'pending_review' => $applyPeriod(DesignTask::where('designer_id', $user->id)
                    ->whereIn('status', [DesignTask::STATUS_IN_REVIEW, DesignTask::STATUS_COMPLETED, DesignTask::STATUS_CONFIRMED]))->count(),
                'completed' => $applyPeriod(DesignTask::where('designer_id', $user->id)
                    ->whereIn('status', [DesignTask::STATUS_SUPER_COMPLETED]))->count(),
                'completed_tasks' => $applyPeriod(DesignTask::where('designer_id', $user->id)
                    ->whereIn('status', [DesignTask::STATUS_SUPER_COMPLETED]))->count(),
            ];
            
            $recentTasks = $applyPeriod(DesignTask::with(['customer', 'receptionist'])
                ->where('designer_id', $user->id))
                ->latest()
                ->limit(10)
                ->get();
            
            $inProgressTasks = $applyPeriod(DesignTask::with(['customer', 'receptionist'])
                ->where('designer_id', $user->id)
                ->where('status', 'in_progress'))
                ->orderBy('priority', 'asc')
                ->orderBy('deadline', 'asc')
                ->limit(5)
                ->get();
            
            $overdueTasks = $applyPeriod(DesignTask::with(['customer', 'receptionist'])
                ->where('designer_id', $user->id)
                ->where('status', '!=', 'completed')
                ->whereNotNull('deadline')
                ->where('deadline', '<', now()))
                ->orderBy('deadline', 'asc')
                ->limit(5)
                ->get();
            
            $tasksByStatus = [
                'pending' => $applyPeriod(DesignTask::where('designer_id', $user->id)->where('status', 'pending'))->count(),
                'in_progress' => $applyPeriod(DesignTask::where('designer_id', $user->id)->where('status', 'in_progress'))->count(),
                'in_review' => $applyPeriod(DesignTask::where('designer_id', $user->id)->where('status', 'in_review'))->count(),
                'completed' => $applyPeriod(DesignTask::where('designer_id', $user->id)->whereIn('status', ['completed', 'confirmed', 'super_completed']))->count(),
                'printing' => $applyPeriod(DesignTask::where('designer_id', $user->id)->whereIn('status', ['printing', 'printed']))->count(),
            ];
            
            $barChartLabels = []; $barChartData = [];
            if ($period == 'today') {
                for ($i = 11; $i >= 0; $i--) {
                    $hour = now()->subHours($i * 2);
                    $barChartLabels[] = $hour->format('H:00');
                    $barChartData[] = DesignTask::where('designer_id', $user->id)->whereBetween('created_at', [$hour->copy()->startOfHour(), $hour->copy()->addHour()->endOfHour()])->count();
                }
            } elseif ($period == 'week' || $period == 'month') {
                $days = $period == 'week' ? 7 : 30;
                for ($i = $days - 1; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $barChartLabels[] = $date->format('M d');
                    $barChartData[] = DesignTask::where('designer_id', $user->id)->whereDate('created_at', $date->format('Y-m-d'))->count();
                }
            } elseif ($period == '2_years') {
                for ($i = 23; $i >= 0; $i--) {
                    $month = now()->subMonths($i);
                    $barChartLabels[] = $month->format('M Y');
                    $barChartData[] = DesignTask::where('designer_id', $user->id)->where('status', 'completed')
                        ->whereYear('completed_at', $month->year)->whereMonth('completed_at', $month->month)->count();
                }
            } else {
                $months = ($period == 'year') ? 12 : 6;
                for ($i = $months - 1; $i >= 0; $i--) {
                    $month = now()->subMonths($i);
                    $barChartLabels[] = $month->format('M Y');
                    $barChartData[] = DesignTask::where('designer_id', $user->id)->where('status', 'completed')
                        ->whereYear('completed_at', $month->year)->whereMonth('completed_at', $month->month)->count();
                }
            }
            // Sync all trend vars
            $last7Days = $barChartLabels;
            $tasksCompletedData = $barChartData;
            $last6Months = $barChartLabels;
            $monthlyCompletedData = $barChartData;
        }
        
        return view('admin.dashboards.designer', compact(
            'stats', 'recentTasks', 'inProgressTasks', 'overdueTasks', 
            'tasksByStatus', 'last7Days', 'tasksCompletedData', 
            'last6Months', 'monthlyCompletedData', 'period'
        ));
    }

    /**
     * Display delivery dashboard.
     */
    private function deliveryDashboard(Request $request): View
    {
        $user = Auth::user();
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
                $request->get('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : null,
                $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : null
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

        $stats = [
            'assigned_tasks' => $applyPeriod(DesignTask::where('delivery_id', $user->id))->count(),
            'pending_delivery' => $applyPeriod(DesignTask::where('delivery_id', $user->id)->where('delivery_status', 'assigned'))->count(),
            'delivered' => $applyPeriod(DesignTask::where('delivery_id', $user->id)->where('delivery_status', 'delivered'))->count(),
            'failed' => $applyPeriod(DesignTask::where('delivery_id', $user->id)->where('delivery_status', 'failed'))->count(),
        ];

        $assignedTasks = DesignTask::with(['customer', 'receptionist'])
            ->where('delivery_id', $user->id)
            ->where('delivery_status', 'assigned')
            ->latest()
            ->get();

        $recentDeliveries = $applyPeriod(DesignTask::with(['customer'])
            ->where('delivery_id', $user->id)
            ->where('delivery_status', 'delivered'))
            ->latest('delivered_at')
            ->limit(10)
            ->get();

        // Chart Data: Adaptive constraints based on period
        $chartData = [
            'labels' => [],
            'data' => []
        ];

        if ($period == 'today') {
            // Hourly breakdown for today
            for ($i = 0; $i <= 23; $i++) {
                $chartData['labels'][] = sprintf('%02d:00', $i);
                $chartData['data'][] = DesignTask::where('delivery_id', $user->id)
                    ->where('delivery_status', 'delivered')
                    ->whereDate('delivered_at', now()->today())
                    ->whereTime('delivered_at', '>=', sprintf('%02d:00:00', $i))
                    ->whereTime('delivered_at', '<=', sprintf('%02d:59:59', $i))
                    ->count();
            }
        } elseif ($period == 'year' || $period == '2_years') {
            // Monthly breakdown
            $startDate = $dateRange[0] ?? now()->startOfYear();
            $endDate = $dateRange[1] ?? now()->endOfYear();
            $current = $startDate->copy();
            
            while ($current <= $endDate) {
                $chartData['labels'][] = $current->format('M Y');
                $chartData['data'][] = DesignTask::where('delivery_id', $user->id)
                    ->where('delivery_status', 'delivered')
                    ->whereYear('delivered_at', $current->year)
                    ->whereMonth('delivered_at', $current->month)
                    ->count();
                $current->addMonth();
            }
        } else {
            // Daily breakdown (default for week, month, etc.)
            $startDate = $dateRange[0] ?? now()->subDays(6);
            $endDate = $dateRange[1] ?? now();
            
            // Limit chart points to avoid overcrowding if range is huge (e.g. all time)
            if ($startDate->diffInDays($endDate) > 31) {
                 // Weekly grouping for large ranges? For simplicity, stick to daily or restrict range.
                 // Let's stick to daily iteration for now, assuming month/week usage.
            }

            $current = $startDate->copy();
            while ($current <= $endDate) {
                $chartData['labels'][] = $current->format('D, M d');
                $chartData['data'][] = DesignTask::where('delivery_id', $user->id)
                    ->where('delivery_status', 'delivered')
                    ->whereDate('delivered_at', $current->format('Y-m-d'))
                    ->count();
                $current->addDay();
            }
        }

        return view('admin.dashboards.delivery', compact('stats', 'assignedTasks', 'recentDeliveries', 'period', 'chartData'));
    }

    /**
     * Display gatekeeper dashboard.
     */
    private function gatekeeperDashboard(Request $request): View
    {
        $user = Auth::user();
        $period = $request->get('period', 'today'); // Default to today for gatekeeper as monitoring is immediate

        return view('admin.dashboards.gatekeeper', compact('period'));
    }

    /**
     * Display incoming tasks for delivery user.
     */
    public function deliveryIncoming(Request $request): View
    {
        $user = Auth::user();
        $tasks = DesignTask::with(['customer', 'receptionist'])
            ->where('delivery_id', $user->id)
            ->whereIn('delivery_status', ['assigned', 'picked_up'])
            ->latest()
            ->paginate(15)->withQueryString();
            
        $viewType = 'incoming';
        return view('admin.delivery.tasks', compact('tasks', 'viewType'));
    }

    /**
     * Search for tasks available for delivery (Super Completed tasks not yet assigned).
     */
    public function deliverySearch(Request $request): JsonResponse
    {
        $search = $request->get('query');
        $tasks = DesignTask::where('status', DesignTask::STATUS_SUPER_COMPLETED)
            ->where(function($q) {
                $q->whereNull('delivery_id')
                  ->orWhere('delivery_status', '!=', 'delivered');
            })
            ->where(function($q) use ($search) {
                $q->where('task_code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            })
            ->with(['customer'])
            ->limit(10)
            ->get();

        return response()->json($tasks);
    }

    /**
     * Pull a task to current delivery person.
     */
    public function deliveryPull(Request $request, DesignTask $designTask): RedirectResponse
    {
        $user = Auth::user();
        if ($user->role !== 'delivery') {
            abort(403);
        }

        if ($designTask->status !== DesignTask::STATUS_SUPER_COMPLETED) {
            return redirect()->back()->with('error', 'Only super completed tasks can be pulled for delivery.');
        }

        $designTask->update([
            'delivery_id' => $user->id,
            'delivery_status' => 'assigned',
            'delivery_assigned_at' => now(),
        ]);

        TaskUpdate::create([
            'task_id' => $designTask->id,
            'admin_id' => $user->id,
            'type' => TaskUpdate::TYPE_COMMENT,
            'content' => "Task pulled by delivery person: {$user->name}",
        ]);

        return redirect()->route('admin.delivery.incoming')->with('success', 'Task successfully pulled and assigned to you.');
    }

    /**
     * Display completed tasks for delivery user.
     */
    public function deliveryCompleted(Request $request): View
    {
        $user = Auth::user();
        $tasks = DesignTask::with(['customer', 'receptionist'])
            ->where('delivery_id', $user->id)
            ->where('delivery_status', 'delivered')
            ->latest('delivered_at')
            ->paginate(15)->withQueryString();
            
        $viewType = 'completed';
        return view('admin.delivery.tasks', compact('tasks', 'viewType'));
    }

    /**
     * Display canceled/failed tasks for delivery user.
     */
    public function deliveryCanceled(Request $request): View
    {
        $user = Auth::user();
        $tasks = DesignTask::with(['customer', 'receptionist'])
            ->where('delivery_id', $user->id)
            ->where('delivery_status', 'failed')
            ->latest('updated_at')
            ->paginate(15)->withQueryString();
            
        $viewType = 'canceled';
        return view('admin.delivery.tasks', compact('tasks', 'viewType'));
    }

    /**
     * Display all delivery updates for admins/receptionists.
     */
    public function deliveryUpdates(Request $request): View
    {
        $tasks = DesignTask::with(['customer', 'receptionist', 'delivery'])
            ->whereNotNull('delivery_status')
            ->latest('updated_at')
            ->paginate(20)->withQueryString();
            
        $viewType = 'all delivery updates';
        // Reuse the tasks view but might need small adjustment to show delivery person
        return view('admin.delivery.tasks', compact('tasks', 'viewType'));
    }

    /**
     * Print filtered delivery tasks.
     */
    public function printDeliveryTasks(Request $request)
    {
        $user = Auth::user();
        $type = $request->get('view_type', 'updates');
        
        $query = DesignTask::with(['customer', 'receptionist', 'delivery']);

        if ($type === 'incoming') {
            $query->where('delivery_id', $user->id)
                  ->whereIn('delivery_status', ['assigned', 'picked_up']);
            $viewType = 'Incoming Deliveries';
        } elseif ($type === 'completed') {
            $query->where('delivery_id', $user->id)
                  ->where('delivery_status', 'delivered');
            $viewType = 'Completed Deliveries';
        } elseif ($type === 'canceled') {
            $query->where('delivery_id', $user->id)
                  ->where('delivery_status', 'failed');
            $viewType = 'Canceled/Failed Deliveries';
        } else {
            $query->whereNotNull('delivery_status');
            $viewType = 'All Delivery Updates';
        }

        $tasks = $query->latest('updated_at')->get();

        if ($tasks->isEmpty()) {
            return redirect()->back()->with('error', 'No records found to print.');
        }

        return view('admin.delivery.print-tasks', compact('tasks', 'viewType'));
    }

    /**
     * Get monthly orders data for AJAX requests
     */
    public function getMonthlyOrdersData(Request $request)
    {
        $year = $request->get('year', now()->year);
        
        $monthlyOrders = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyOrders[] = \App\Models\Order::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();
        }

        return response()->json([
            'success' => true,
            'data' => $monthlyOrders,
            'year' => $year
        ]);
    }

    /**
     * Display the admin login page.
     */
    public function login(): View
    {
        return view('admin.login');
    }

    /**
     * Handle admin login authentication.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (auth()->guard('admin')->attempt($credentials)) {
            $user = auth()->guard('admin')->user();
            if ($user->role === 'admin' && $user->verified) {
                $request->session()->regenerate();
                // Always redirect to dashboard, ignoring any stored intended URL
                return redirect('/admin/dashboard');
            } else {
                auth()->guard('admin')->logout();
                return back()->withErrors([
                    'email' => 'Access denied. Admin privileges required.',
                ])->onlyInput('email');
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle admin logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        // Logout from admin guard if using separate guard
        if (auth()->guard('admin')->check()) {
            auth()->guard('admin')->logout();
        }
        
        // Also logout from default guard if admin is logged in as User
        if (auth()->check() && auth()->user()->role === 'admin') {
            Auth::logout();
        }
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Display admin settings page.
     */
    public function settings(): View
    {
        return view('admin.settings');
    }

    /**
     * Update admin settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email',
            'company_phone' => 'required|string',
        ]);

        // Update settings logic here
        // This would typically involve updating a settings table or config

        return redirect()->route('admin.settings')->with('success', 'Settings updated successfully.');
    }

    /**
     * Display all orders for management.
     */
    public function orders(Request $request): View
    {
        // Restrict access for receptionist and designer (operator has access)
        $user = Auth::user();
        if (in_array($user->role ?? '', ['designer'])) {
            abort(403, 'You do not have permission to view orders.');
        }
        
        $query = \App\Models\Order::with(['user', 'items.product']);

        // Filter by approval status
        if ($request->filled('status')) {
            $query->where('approval_status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        // Restrictions for specific roles
        if ($user->role === 'saler') {
            $query->where(function($q) use ($user) {
                $q->where('saler_id', $user->id)
                  ->orWhereNull('saler_id');
            });
        }

        // Apply Search (Order Code or Customer Name)
        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }
        // Apply Amount Range Filter
        if (request()->filled('amount_min')) {
            $query->where('total_amount', '>=', request('amount_min'));
        }
        if (request()->filled('amount_max')) {
            $query->where('total_amount', '<=', request('amount_max'));
        }

        // DEBUG LOGGING
        \Log::info('User Role: ' . ($user->role ?? 'none') . ', ID: ' . ($user->id ?? 'none'));
        \Log::info('SQL: ' . $query->toSql());
        \Log::info('Bindings: ', $query->getBindings());

        $orders = $query->latest()->paginate(20)->withQueryString();
        $templates = \App\Models\MessageTemplate::active()->get();

        return view('admin.orders.index', compact('orders', 'templates'));
    }

    /**
     * View order details.
     */
    public function viewOrder($order_code): View
    {
        // Restrict access for receptionist and designer (operator has access)
        $user = Auth::user();
        if (in_array($user->role ?? '', ['designer'])) {
            abort(403, 'You do not have permission to view orders.');
        }
        
        $order = \App\Models\Order::where('order_code', $order_code)->firstOrFail();
        $order->load(['user', 'items.product', 'whatsappRequests']);
        $templates = \App\Models\MessageTemplate::active()->get();
        return view('admin.orders.show', compact('order', 'templates'));
    }

    /**
     * Approve an order.
     */
    public function approveOrder(Request $request, $order_code): RedirectResponse
    {
        // Restrict access for receptionist and designer (operator has access)
        $user = Auth::user();
        if (in_array($user->role ?? '', ['designer'])) {
            abort(403, 'You do not have permission to approve orders.');
        }
        
        $order = \App\Models\Order::where('order_code', $order_code)->firstOrFail();
        
        if ($order->approval_status !== 'requested') {
            return redirect()->back()->with('error', 'Only pending orders can be approved.');
        }

        // Check stock availability
        $stockIssues = [];
        foreach ($order->items as $item) {
            $product = $item->product;
            if ($product->track_stock && $product->stock_quantity < $item->quantity) {
                $stockIssues[] = "Insufficient stock for {$product->name}. Available: {$product->stock_quantity}, Required: {$item->quantity}";
            }
        }

        if (!empty($stockIssues)) {
            return redirect()->back()->with('error', 'Cannot approve order due to stock issues: ' . implode(', ', $stockIssues));
        }

        // Update order status - preserve saler assignment in notes
        $existingNotes = $order->notes ?? '';
        $adminNotes = $request->admin_notes ?? 'Order approved by admin';
        
        // Check if saler assignment exists in notes
        $salerAssignment = '';
        if (preg_match('/Assigned to saler: [\+]?(\d+)/', $existingNotes, $matches)) {
            $salerAssignment = ' | Assigned to saler: +' . $matches[1];
        }
        
        // Combine admin notes with saler assignment
        $newNotes = $adminNotes . $salerAssignment;
        
        $order->update([
            'approval_status' => 'approved',
            'payment_status' => 'paid', // Auto-set payment status to paid when order is approved
            'notes' => $newNotes,
        ]);

        // Deduct stock quantities
        foreach ($order->items as $item) {
            $product = $item->product;
            if ($product->track_stock) {
                $product->decrement('stock_quantity', $item->quantity);
            }
        }

        // Create notification
        \App\Models\Notification::create([
            'user_id' => $order->user_id,
            'type' => 'order_approved',
            'message' => "Your order {$order->order_code} has been approved! We will contact you soon for delivery details.",
            'status' => 'unread',
        ]);

        return redirect()->back()->with('success', 'Order approved successfully and stock updated.');
    }

    /**
     * Cancel an order.
     */
    public function cancelOrder(Request $request, $order_code): RedirectResponse
    {
        // Restrict access for receptionist and designer (operator has access)
        $user = Auth::user();
        if (in_array($user->role ?? '', ['designer'])) {
            abort(403, 'You do not have permission to cancel orders.');
        }
        
        $order = \App\Models\Order::where('order_code', $order_code)->firstOrFail();
        
        if ($order->approval_status === 'cancelled') {
            return redirect()->back()->with('error', 'Order is already cancelled.');
        }

        // Update order status
        $order->update([
            'approval_status' => 'cancelled',
            'notes' => $request->cancellation_reason ?? 'Order cancelled by admin',
        ]);

        // Create notification
        \App\Models\Notification::create([
            'user_id' => $order->user_id,
            'type' => 'order_cancelled',
            'message' => "Your order {$order->order_code} has been cancelled. Please contact us for more information.",
            'status' => 'unread',
        ]);

        return redirect()->back()->with('success', 'Order cancelled successfully.');
    }

    /**
     * Delete an order.
     */
    public function destroyOrder($order_code): RedirectResponse
    {
        // Restrict access
        $user = Auth::user();
        if (!in_array($user->role ?? '', ['admin', 'super_admin', 'manager', 'accountant'])) {
            abort(403, 'You do not have permission to delete orders.');
        }

        $order = \App\Models\Order::where('order_code', $order_code)->firstOrFail();
        
        // Delete items first
        $order->items()->delete();
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Order deleted successfully.');
    }

    /**
     * Convert proforma to sales invoice.
     */
    public function convertToInvoice($order_code): RedirectResponse
    {
        $order = \App\Models\Order::where('order_code', $order_code)->firstOrFail();
        
        if ($order->type !== 'proforma') {
            return redirect()->back()->with('error', 'Only proforma invoices can be converted.');
        }

        $order->update([
            'type' => 'sales_invoice',
            'approval_status' => 'approved', // Ensure it's approved
        ]);

        return redirect()->route('admin.orders.show', $order->order_code)
            ->with('success', 'Converted to sales invoice successfully.');
    }

    /**
     * Edit order quantities.
     */
    public function editOrder($order_code): View
    {
        $order = \App\Models\Order::where('order_code', $order_code)->firstOrFail();
        $order->load(['user', 'items.product']);
        return view('admin.orders.edit', compact('order'));
    }

    /**
     * Update order quantities.
     */
    public function updateOrder(Request $request, $order_code): RedirectResponse
    {
        $order = \App\Models\Order::where('order_code', $order_code)->firstOrFail();
        
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $totalAmount = 0;

        foreach ($request->items as $itemData) {
            $orderItem = $order->items()->find($itemData['id']);
            if ($orderItem) {
                $orderItem->update([
                    'quantity' => $itemData['quantity'],
                    'subtotal' => $itemData['quantity'] * $orderItem->unit_price,
                ]);
                $totalAmount += $orderItem->subtotal;
            }
        }

        $order->update(['total_amount' => $totalAmount]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order quantities updated successfully.');
    }

    /**
     * Display notifications panel.
     */
    public function notifications(Request $request): View
    {
        $admin = Auth::user();
        $query = $admin->notifications()->with('sender'); // Use App\Models\Notification relation

        // Filter by read/unread status
        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->where('status', 'unread');
            } elseif ($request->status === 'read') {
                $query->where('status', 'read');
            }
        }

        // Filter by notification type
        if ($request->filled('type')) {
            $query->where('type', 'like', '%' . $request->type . '%');
        }

        // Filter by sender
        if ($request->filled('sender_id')) {
            $query->where('sender_id', $request->sender_id);
        }

        $notifications = $query->latest()->paginate(20)->withQueryString();

        // Get staff for filter
        $staff = User::whereIn('role', ['admin', 'super_admin', 'operator', 'receptionist', 'manager', 'designer', 'accountant', 'saler', 'delivery', 'gatekeeper'])
            ->viewableStaff()
            ->orderBy('name')
            ->get();

        return view('admin.notifications.index', compact('notifications', 'staff'));
    }

    /**
     * Show notification details.
     */
    public function showNotification($notificationId): View
    {
        $admin = Auth::user();
        $notification = $admin->notifications()->where('id', $notificationId)->firstOrFail();
        
        // Automatically mark as read when viewed
        if ($notification->isUnread()) {
            $notification->markAsRead();
        }
        
        return view('admin.notifications.show', compact('notification'));
    }

    /**
     * Mark notification as read.
     */
    public function markNotificationRead($notificationId): RedirectResponse|JsonResponse
    {
        $admin = Auth::user();
        $notification = $admin->notifications()->where('id', $notificationId)->first();
        
        if ($notification) {
            $notification->markAsRead();
        }
        
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Notification marked as read.']);
        }
        
        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Delete notification.
     */
    public function deleteNotification($notificationId): RedirectResponse
    {
        $admin = Auth::user();
        $notification = $admin->notifications()->where('id', $notificationId)->first();
        
        if ($notification) {
            $notification->delete();
        }
        
        return redirect()->back()->with('success', 'Notification deleted.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllNotificationsRead(): RedirectResponse|JsonResponse
    {
        $admin = Auth::user();
        $admin->notifications()->unread()->update(['status' => 'read']);
        
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'All notifications marked as read.']);
        }
        
        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
    
    /**
     * Get notification count for AJAX refresh.
     */
    public function getNotificationCount()
    {
        $admin = Auth::user();
        if (!$admin) return response()->json(['count' => 0, 'latest' => null]);
        
        $unread = $admin->notifications()->unread();
        $count = $unread->count();
        $latest = $unread->latest()->first();
        
        // Generate URL based on notification type
        $url = route('admin.notifications.index');
        if ($latest && $latest->related_type === 'App\\Models\\DesignTask' && $latest->related_id) {
            $url = route('admin.design-tasks.show', $latest->related_id);
        }
        
        return response()->json([
            'count' => $count,
            'latest' => $latest ? [
                'id' => $latest->id,
                'title' => ucwords(str_replace('_', ' ', $latest->type ?? 'notification')),
                'message' => $latest->message ?? '',
                'url' => $url,
            ] : null
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        $admin = Auth::user();
        if (!$admin) return redirect()->back()->with('error', 'Unauthorized');
        
        $admin->notifications()->unread()->update(['status' => 'read']);
        
        return redirect()->back()->with('success', 'All notifications marked as read');
    }

    /**
     * Display reports page.
     */
    public function reports(): View
    {
        return view('admin.reports.index');
    }

    /**
     * Generate daily report.
     */
    public function dailyReport(Request $request): View
    {
        $period = $request->get('period', 'month');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        if ($period == 'custom') {
            $dateFrom = $dateFrom ?: now()->subDays(30)->format('Y-m-d');
            $dateTo = $dateTo ?: now()->format('Y-m-d');
        } else {
            switch ($period) {
                case 'today':
                    $dateFrom = now()->format('Y-m-d');
                    $dateTo = now()->format('Y-m-d');
                    break;
                case 'yesterday':
                    $dateFrom = now()->subDay()->format('Y-m-d');
                    $dateTo = now()->subDay()->format('Y-m-d');
                    break;
                case 'week':
                    $dateFrom = now()->startOfWeek()->format('Y-m-d');
                    $dateTo = now()->endOfWeek()->format('Y-m-d');
                    break;
                case 'month':
                    $dateFrom = now()->startOfMonth()->format('Y-m-d');
                    $dateTo = now()->endOfMonth()->format('Y-m-d');
                    break;
                case 'year':
                    $dateFrom = now()->startOfYear()->format('Y-m-d');
                    $dateTo = now()->endOfYear()->format('Y-m-d');
                    break;
                case 'all':
                    $dateFrom = '2000-01-01'; // Good enough for "all time" start
                    $dateTo = now()->format('Y-m-d');
                    break;
                default:
                    // Default fallback if period is unknown or initial load (though we default to month)
                    // If no period was passed but dates were, respect dates (handled by 'custom' logic usually, 
                    // but here we might want to be robust).
                    // Actually, let's Stick to 'month' as default if nothing provided.
                    $dateFrom = now()->startOfMonth()->format('Y-m-d');
                    $dateTo = now()->endOfMonth()->format('Y-m-d');
                    break;
            }
        }

        // Get daily reports
        $dailyReports = \App\Models\Order::selectRaw('
                DATE(created_at) as date, 
                COUNT(*) as total_requests, 
                SUM(CASE WHEN approval_status = "approved" THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN approval_status = "cancelled" THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN approval_status = "requested" THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN approval_status = "approved" AND payment_status = "paid" THEN total_amount ELSE 0 END) as revenue
            ')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        // Get summary statistics
        $summary = [
            'total_orders' => $dailyReports->sum('total_requests'),
            'approved_orders' => $dailyReports->sum('approved'),
            'cancelled_orders' => $dailyReports->sum('cancelled'),
            'total_revenue' => $dailyReports->sum('revenue'),
        ];

        // Prepare chart data
        $chartData = [
            'labels' => $dailyReports->pluck('date')->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('M j');
            })->toArray(),
            'total' => $dailyReports->pluck('total_requests')->toArray(),
            'approved' => $dailyReports->pluck('approved')->toArray(),
            'revenue' => $dailyReports->pluck('revenue')->toArray(),
        ];

        return view('admin.reports.daily', compact('dailyReports', 'summary', 'chartData', 'dateFrom', 'dateTo', 'period'));
    }

    /**
     * Generate monthly report.
     */
    public function monthlyReport(Request $request): View
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        
        $report = \App\Models\Order::selectRaw('DATE(created_at) as date, COUNT(*) as total, 
            SUM(CASE WHEN approval_status = "approved" THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN approval_status = "cancelled" THEN 1 ELSE 0 END) as cancelled,
            SUM(CASE WHEN approval_status = "requested" THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN approval_status = "approved" AND payment_status = "paid" THEN total_amount ELSE 0 END) as approved_revenue')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $summary = [
            'total_requests' => $report->sum('total'),
            'total_approved' => $report->sum('approved'),
            'total_cancelled' => $report->sum('cancelled'),
            'total_pending' => $report->sum('pending'),
            'total_revenue' => $report->sum('approved_revenue'),
        ];

        return view('admin.reports.monthly', compact('report', 'summary', 'year', 'month'));
    }

    /**
     * Generate design tasks report.
     */
    public function designTasksReport(Request $request): View
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $designerId = $request->get('designer_id');

        // Get Designers for filter
        $designers = User::where('role', 'designer')->get();

        // Get all design tasks within date range
        $query = DesignTask::with(['customer', 'receptionist', 'designer'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($designerId) {
            $query->where('designer_id', $designerId);
        }

        $tasks = $query->get();

        // Summary statistics
        $summary = [
            'total_tasks' => $tasks->count(),
            'pending' => $tasks->where('status', 'pending')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'in_review' => $tasks->where('status', 'in_review')->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'rejected' => $tasks->where('status', 'rejected')->count(),
        ];

        // Tasks by status over time (last 30 days)
        $tasksByDate = [];
        $startDate = \Carbon\Carbon::parse($dateFrom);
        $endDate = \Carbon\Carbon::parse($dateTo);
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $dayTasks = $tasks->filter(function($task) use ($dateStr) {
                return $task->created_at->format('Y-m-d') === $dateStr;
            });
            
            $tasksByDate[] = [
                'date' => $dateStr,
                'total' => $dayTasks->count(),
                'pending' => $dayTasks->where('status', 'pending')->count(),
                'in_progress' => $dayTasks->where('status', 'in_progress')->count(),
                'completed' => $dayTasks->where('status', 'completed')->count(),
            ];
        }

        // Tasks by designer
        $tasksByDesigner = $tasks->whereNotNull('designer_id')
            ->groupBy('designer_id')
            ->map(function($designerTasks) {
                return [
                    'designer' => $designerTasks->first()->designer,
                    'total' => $designerTasks->count(),
                    'completed' => $designerTasks->where('status', 'completed')->count(),
                    'in_progress' => $designerTasks->where('status', 'in_progress')->count(),
                ];
            })->values();

        // Tasks by receptionist
        $tasksByReceptionist = $tasks->whereNotNull('receptionist_id')
            ->groupBy('receptionist_id')
            ->map(function($receptionistTasks) {
                return [
                    'receptionist' => $receptionistTasks->first()->receptionist,
                    'total' => $receptionistTasks->count(),
                ];
            })->values();

        return view('admin.reports.design-tasks', compact('tasks', 'summary', 'tasksByDate', 'tasksByDesigner', 'tasksByReceptionist', 'dateFrom', 'dateTo', 'designers', 'designerId'));
    }

    /**
     * Generate operator performance report.
     */
    public function operatorsReport(Request $request): View
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $operatorId = $request->get('operator_id');

        // Get Operators for filter
        $operators = User::whereIn('role', ['operator', 'admin', 'super_admin', 'manager', 'accountant'])
            ->viewableStaff()
            ->get();

        // Get all design tasks handled by operators (usually printing tasks)
        $query = DesignTask::with(['customer', 'operator', 'designer'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        
        if ($operatorId) {
            $query->where('operator_id', $operatorId);
        }

        $tasks = $query->get();

        // Summary Statistics focused on Operations
        $summary = [
            'total_managed' => $tasks->whereNotNull('operator_id')->count(),
            'printing' => $tasks->where('status', 'printing')->count(),
            'printed' => $tasks->where('status', 'printed')->count(),
            'revenue_managed' => $tasks->whereNotNull('operator_id')->sum('price'),
        ];

        // Tasks by status over time (Trend)
        $tasksByDate = [];
        $startDate = \Carbon\Carbon::parse($dateFrom);
        $endDate = \Carbon\Carbon::parse($dateTo);
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $dayTasks = $tasks->filter(function($task) use ($dateStr) {
                return $task->created_at->format('Y-m-d') === $dateStr;
            });
            
            $tasksByDate[] = [
                'date' => $dateStr,
                'total' => $dayTasks->count(),
                'printed' => $dayTasks->where('status', 'printed')->count(),
            ];
        }

        // Group by Operator
        $tasksByOperator = $tasks->whereNotNull('operator_id')
            ->groupBy('operator_id')
            ->map(function($opTasks) {
                $total = $opTasks->count();
                $printed = $opTasks->where('status', 'printed')->count();
                return [
                    'operator' => $opTasks->first()->operator,
                    'total' => $total,
                    'printed' => $printed,
                    'revenue' => $opTasks->sum('price'),
                    'efficiency' => $total > 0 ? ($printed / $total) * 100 : 0,
                ];
            })->values();

        return view('admin.reports.operators', compact('tasks', 'summary', 'tasksByOperator', 'dateFrom', 'dateTo', 'operators', 'operatorId', 'tasksByDate'));
    }

    /**
     * Print operator performance report.
     */
    public function printOperatorsReport(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $operatorId = $request->get('operator_id');

        // Get all design tasks handled by operators (usually printing tasks)
        $query = DesignTask::with(['customer', 'operator', 'designer'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        
        if ($operatorId) {
            $query->where('operator_id', $operatorId);
        }

        $tasks = $query->get();

        if ($tasks->isEmpty()) {
            return redirect()->back()->with('error', 'No records found for the selected period.');
        }

        // Summary Statistics
        $summary = [
            'total_managed' => $tasks->whereNotNull('operator_id')->count(),
            'printed' => $tasks->where('status', 'printed')->count(),
            'revenue_managed' => $tasks->whereNotNull('operator_id')->sum('price'),
        ];

        // Tasks by status over time (Trend)
        $tasksByDate = [];
        $startDate = \Carbon\Carbon::parse($dateFrom);
        $endDate = \Carbon\Carbon::parse($dateTo);
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $dayTasks = $tasks->filter(function($task) use ($dateStr) {
                return $task->created_at->format('Y-m-d') === $dateStr;
            });
            
            $tasksByDate[] = [
                'date' => $date->format('M d'),
                'total' => $dayTasks->count(),
                'printed' => $dayTasks->where('status', 'printed')->count(),
            ];
        }

        // Group by Operator
        $tasksByOperator = $tasks->whereNotNull('operator_id')
            ->groupBy('operator_id')
            ->map(function($opTasks) {
                $total = $opTasks->count();
                $printed = $opTasks->where('status', 'printed')->count();
                $efficiency = $total > 0 ? ($printed / $total) * 100 : 0;
                
                return [
                    'operator' => $opTasks->first()->operator,
                    'total' => $total,
                    'printed' => $printed,
                    'revenue' => $opTasks->sum('price'),
                    'efficiency' => $efficiency,
                ];
            })->values();

        return view('admin.reports.print-operators', compact('tasks', 'summary', 'tasksByOperator', 'dateFrom', 'dateTo', 'operatorId', 'tasksByDate'));
    }

    public function exportDesignTasksReport(Request $request)
    {
        $type = $request->get('type', 'pdf');
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $designerId = $request->get('designer_id');

        $query = DesignTask::with(['customer', 'receptionist', 'designer'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($designerId) {
            $query->where('designer_id', $designerId);
        }

        $tasks = $query->get();

        // Summary for export
        $summary = [
            'total' => $tasks->count(),
            'pending' => $tasks->where('status', 'pending')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'rejected' => $tasks->where('status', 'rejected')->count(),
            'completion_rate' => $tasks->count() > 0 ? round(($tasks->where('status', 'completed')->count() / $tasks->count()) * 100, 1) : 0,
        ];

        $tasksByDesigner = $tasks->whereNotNull('designer_id')
            ->groupBy('designer_id')
            ->map(function($designerTasks) {
                return [
                    'designer' => $designerTasks->first()->designer,
                    'total' => $designerTasks->count(),
                    'completed' => $designerTasks->where('status', 'completed')->count(),
                    'in_progress' => $designerTasks->where('status', 'in_progress')->count(),
                ];
            })->values();

        $title = $designerId ? ($tasks->first()->designer->name ?? 'Designer') . ' Performance Report' : 'Designer Performance Report';

        if ($type === 'pdf') {
            $history = [];
            if ($designerId) {
                // Get last 6 months summary for the individual report
                for ($i = 0; $i < 6; $i++) {
                    $date = now()->subMonths($i);
                    $month = $date->month;
                    $year = $date->year;
                    
                    $history[] = [
                        'month' => $date->format('F Y'),
                        'total' => DesignTask::where('designer_id', $designerId)->whereYear('created_at', $year)->whereMonth('created_at', $month)->count(),
                        'completed' => DesignTask::where('designer_id', $designerId)->whereYear('created_at', $year)->whereMonth('created_at', $month)->where('status', 'completed')->count(),
                    ];
                }
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.exports.designer-performance', compact('tasks', 'tasksByDesigner', 'dateFrom', 'dateTo', 'title', 'summary', 'history'));
            return $pdf->download('designer-performance-report.pdf');
        } else {
            // Excel Export
            return $this->exportToExcel([
                ['Designer', 'Total Tasks', 'In Progress', 'Completed', 'Completion Rate'],
                ...$tasksByDesigner->map(fn($d) => [
                    $d['designer']->name ?? 'N/A',
                    $d['total'],
                    $d['in_progress'],
                    $d['completed'],
                    ($d['total'] > 0 ? round(($d['completed'] / $d['total']) * 100, 1) : 0) . '%'
                ])
            ], 'designer-performance-report.csv');
        }
    }

    private function exportToExcel(array $data, string $filename)
    {
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }
    
    public function designerAnalytics($id, Request $request): View
    {
        $designer = User::whereIn('role', ['designer', 'operator', 'receptionist', 'admin', 'super_admin'])->findOrFail($id);
        
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Get all design tasks for this designer within date range
        $tasks = DesignTask::with(['customer', 'receptionist'])
            ->where('designer_id', $id)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Summary statistics
        $summary = [
            'total_tasks' => $tasks->count(),
            'pending' => $tasks->where('status', 'pending')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'in_review' => $tasks->where('status', 'in_review')->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'rejected' => $tasks->where('status', 'rejected')->count(),
        ];

        // Tasks by status over time
        $tasksByDate = [];
        $startDate = \Carbon\Carbon::parse($dateFrom);
        $endDate = \Carbon\Carbon::parse($dateTo);
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $dayTasks = $tasks->filter(function($task) use ($dateStr) {
                return $task->created_at->format('Y-m-d') === $dateStr;
            });
            
            $tasksByDate[] = [
                'date' => $dateStr,
                'total' => $dayTasks->count(),
                'completed' => $dayTasks->where('status', 'completed')->count(),
            ];
        }

        return view('admin.reports.designer-analytics', compact('designer', 'tasks', 'summary', 'tasksByDate', 'dateFrom', 'dateTo'));
    }

    // Admin Management Methods
    public function adminsIndex(Request $request)
    {
        $currentUser = Auth::user();
        
        $query = User::with('department')->whereIn('role', ['admin', 'super_admin', 'manager', 'saler', 'receptionist', 'designer', 'operator', 'delivery', 'gatekeeper', 'accountant'])
            ->where('email', '!=', 'softmine.co@gmail.com');

        // Apply Hierarchy Logic
        if ($currentUser->role === 'accountant') {
            // Accountants can only see/manage roles NOT in [super_admin, admin, manager, accountant]
            $query->whereNotIn('role', ['super_admin', 'admin', 'manager', 'accountant']);
        } elseif ($currentUser->role === 'admin') {
            // Admins cannot manage super_admins
            $query->where('role', '!=', 'super_admin');
        }
        
        // Filter by role if specified
        if ($request->has('role') && $request->role !== '' && $request->role !== null) {
            $query->where('role', $request->role);
        }
        
        $admins = $query->latest()->paginate(15)->withQueryString();
            
        // Get available roles and their descriptions
        $roles = [
            'super_admin' => [
                'name' => 'Super Admin',
                'description' => 'Full access to all features and settings. Can manage all users and system configurations.'
            ],
            'admin' => [
                'name' => 'Admin',
                'description' => 'Can manage most settings and content. Cannot manage other admin users or system settings.'
            ],
            'manager' => [
                'name' => 'Manager',
                'description' => 'Can manage products, categories, and view reports. Limited access to system settings.'
            ],
            'receptionist' => [
                'name' => 'Receptionist',
                'description' => 'Can manage customer interactions, appointments, and assign tasks to designers.'
            ],
            'designer' => [
                'name' => 'Designer',
                'description' => 'Can view and update assigned design tasks. Limited access to other features.'
            ],
            'saler' => [
                'name' => 'Sales',
                'description' => 'Can manage sales, customers, and orders. Limited access to system settings.'
            ],
            'operator' => [
                'name' => 'Operator',
                'description' => 'Can act as both designer and receptionist. Can perform all tasks assigned to both roles.'
            ],
            'delivery' => [
                'name' => 'Delivery',
                'description' => 'Can view assigned delivery tasks and update delivery status.'
            ],
            'gatekeeper' => [
                'name' => 'Gatekeeper',
                'description' => 'Can verify items leaving the premises.'
            ],
            'accountant' => [
                'name' => 'Accountant',
                'description' => 'Full access to financial dashboards, expenses, cash flow, and management of subordinate staff.'
            ],
        ];

        // Get just the role names for the dropdown
        $roleOptions = [];
        foreach ($roles as $key => $role) {
            $roleOptions[$key] = $role['name'];
        }
        
        // Calculate statistics based on manageable users
        $statsQuery = User::whereIn('role', array_keys($roles))
            ->where('email', '!=', 'softmine.co@gmail.com')
            ->manageable();

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'active' => (clone $statsQuery)->where('is_active', true)->count(),
            'new_this_month' => (clone $statsQuery)->whereMonth('created_at', now()->month)->count(),
            'online' => 0 
        ];

        $departments = \App\Models\Department::all();

        // Get IDs of staff paid this month
        $paidStaffIds = \App\Models\Expense::where('category', 'Salary')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->whereNotNull('staff_id')
            ->pluck('staff_id')
            ->toArray();

        return view('admin.admins.index', [
            'admins' => $admins,
            'paidStaffIds' => $paidStaffIds,
            'roles' => $roleOptions,
            'roleDescriptions' => $roles,
            'stats' => $stats,
            'departments' => $departments
        ]);
    }

    public function showAdmin($id)
    {
        $currentUser = Auth::user();
        $admin = User::with('department')->findOrFail($id);

        // Hierarchy Logic (same as adminsIndex)
        if ($currentUser->role === 'accountant') {
            if (in_array($admin->role, ['super_admin', 'admin', 'manager', 'accountant']) && $admin->id !== $currentUser->id) {
                return redirect()->route('admin.admins.index')->with('error', 'You do not have permission to view this user.');
            }
        } elseif ($currentUser->role === 'admin') {
            if ($admin->role === 'super_admin' && $admin->id !== $currentUser->id) {
                return redirect()->route('admin.admins.index')->with('error', 'You do not have permission to view super admins.');
            }
        }

        // Get Salary History (from Expenses)
        $salaryHistory = [];
        // Show history if user is super_admin/admin/accountant OR if they are viewing their own profile
        if (in_array($currentUser->role, ['super_admin', 'admin', 'accountant']) || $currentUser->id === $admin->id) {
            $salaryHistory = \App\Models\Expense::where('staff_id', $admin->id)
                ->where(function($q) {
                    $q->where('category', 'Salary')
                      ->orWhere('category', 'salary')
                      ->orWhere('notes', 'LIKE', '%Salary%');
                })
                ->latest('date')
                ->get();
        }

        return view('admin.admins.show', compact('admin', 'salaryHistory'));
    }
public function storeAdmin(Request $request)
{
    \Log::info('========== ADMIN STORE REQUEST ==========');
    \Log::info('Request Data: ', $request->all());
    
    try {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:super_admin,admin,manager,receptionist,designer,saler,operator,delivery,gatekeeper,accountant',
            'department_id' => 'nullable|exists:departments,id',
            'monthly_salary' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        \Log::info('Validation passed', $validated);

        // Store the plain password before hashing (needed for email)
        $plainPassword = $validated['password'];

        // Role Hierarchy Check before creation
        $currentUser = Auth::user();
        if ($currentUser->role === 'accountant' && in_array($validated['role'], ['super_admin', 'admin', 'manager', 'accountant'])) {
            return redirect()->back()->with('error', 'You do not have permission to create users with this role.')->withInput();
        } elseif ($currentUser->role === 'admin' && $validated['role'] === 'super_admin') {
            return redirect()->back()->with('error', 'You do not have permission to create super admins.')->withInput();
        }

        // Create the admin user
        // Admin users are always automatically verified when created by another admin
        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($plainPassword),
            'role' => $validated['role'],
            'department_id' => $validated['department_id'] ?? null,
            'monthly_salary' => $validated['monthly_salary'] ?? 0,
            'is_active' => $request->has('is_active') ? (bool)$validated['is_active'] : true,
            'verified' => true, // Always verified - admin users created by admins don't need verification
        ]);
        
        // Log audit for admin creation
        try {
            \App\Services\AuditLogService::created($admin, 'Created admin user: ' . $admin->name . ' (' . $admin->email . ') with role: ' . $admin->role);
        } catch (\Exception $e) {
            \Log::warning('Failed to log audit for admin creation: ' . $e->getMessage());
        }

        \Log::info('Admin created successfully!', [
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'verified' => $admin->verified,
            'is_active' => $admin->is_active,
            'role' => $admin->role
        ]);

        // Double-check verified status is set correctly
        if (!$admin->verified) {
            \Log::warning('Admin user was not verified after creation, fixing...', [
                'admin_id' => $admin->id
            ]);
            $admin->update(['verified' => true]);
            $admin->refresh();
        }

        // Send verification email with login credentials
        $emailSent = false;
        try {
            $admin->notify(new AdminUserCreated($plainPassword));
            $emailSent = true;
            \Log::info('Verification email sent to admin user', [
                'admin_id' => $admin->id,
                'email' => $admin->email
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email to admin user', [
                'admin_id' => $admin->id,
                'email' => $admin->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Continue even if email fails - user is still created
        }

        \Log::info('========== END ADMIN STORE REQUEST ==========');

        // Build success message
        $successMessage = 'Admin user "' . $admin->name . '" created successfully and verified!';
        if ($emailSent) {
            $successMessage .= ' Verification email with login credentials has been sent to ' . $admin->email . '.';
        } else {
            $successMessage .= ' Please note: Email notification failed, but the user account is active and verified.';
        }

        // SIMPLE REDIRECT LIKE CATEGORY CONTROLLER
        return redirect()->route('admin.admins.index')
            ->with('success', $successMessage);

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::warning('Validation failed:', $e->errors());
        
        return redirect()->back()
            ->withErrors($e->errors())
            ->withInput();
            
    } catch (\Exception $e) {
        \Log::error('Error creating admin', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        return redirect()->back()
            ->with('error', 'Failed to create admin: ' . $e->getMessage())
            ->withInput();
    }
}
    public function updateAdmin(Request $request, $id)
    {
        $admin = User::findOrFail($id);
        $currentUser = Auth::user();

        // Hierarchy Logic for Accountants
        if ($currentUser->role === 'accountant') {
            if (in_array($admin->role, ['super_admin', 'admin', 'manager', 'accountant']) || $admin->id === $currentUser->id) {
                return back()->with('error', 'You do not have permission to manage this user.');
            }
            // Cannot promote to elite roles
            if (in_array($request->role, ['super_admin', 'admin', 'manager', 'accountant'])) {
                return back()->with('error', 'You cannot assign this role.');
            }
        }

        // Prevent non-super admins from modifying super admin accounts
        if ($admin->role === 'super_admin' && $currentUser->role !== 'super_admin') {
            return back()->with('error', 'You do not have permission to update super admin accounts.');
        }

        // Prevent users from demoting themselves
        if ($admin->id === $currentUser->id && $request->has('role') && $request->role !== $admin->role) {
            return back()->with('error', 'You cannot change your own role.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,super_admin,manager,saler,receptionist,designer,operator,delivery,gatekeeper,accountant',
            'department_id' => 'nullable|exists:departments,id',
            'monthly_salary' => 'nullable|numeric|min:0',
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'sometimes|boolean',
        ]);

        // Store old values for audit log
        $oldValues = $admin->only(['name', 'email', 'phone', 'role', 'is_active']);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'monthly_salary' => $validated['monthly_salary'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ];

        // Only update role if current user is super admin or admin (with hierarchy check)
        if ($currentUser->role === 'super_admin' || $currentUser->role === 'admin' || $currentUser->role === 'accountant') {
            $updateData['role'] = $validated['role'];
        }

        $admin->update($updateData);

        if (!empty($validated['password'])) {
            $admin->update(['password' => Hash::make($validated['password'])]);
        }

        // Log audit for admin update
        try {
            \App\Services\AuditLogService::updated($admin, $oldValues, 'Updated admin user: ' . $admin->name . ' (' . $admin->email . ')');
        } catch (\Exception $e) {
            \Log::warning('Failed to log audit for admin update: ' . $e->getMessage());
        }

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin updated successfully!');
    }

    public function destroyAdmin($id)
    {
        try {
            $admin = User::findOrFail($id);
            $currentUser = Auth::user();
            
            // Prevent deleting own account
            if ($admin->id === $currentUser->id) {
                return back()->with('error', 'You cannot delete your own account!');
            }
            
            // Hierarchy Check for Accountants
            if ($currentUser->role === 'accountant') {
                if (in_array($admin->role, ['super_admin', 'admin', 'manager', 'accountant']) || $admin->id === $currentUser->id) {
                    return back()->with('error', 'You do not have permission to delete this user.');
                }
            }

            // Prevent non-super admins from deleting super admin accounts
            if ($admin->role === 'super_admin' && $currentUser->role !== 'super_admin') {
                return back()->with('error', 'You do not have permission to delete super admin accounts.');
            }
            
            // Prevent deleting the last super admin
            if ($admin->role === 'super_admin' && User::where('role', 'super_admin')->count() <= 1) {
                return back()->with('error', 'Cannot delete the last super admin account.');
            }
            
            // Store admin info before deletion for audit log
            $adminInfo = $admin->only(['name', 'email', 'role']);
            
            $admin->delete();
            
            // Log audit for admin deletion
            try {
                \App\Services\AuditLogService::log('deleted', 'Deleted admin user: ' . $adminInfo['name'] . ' (' . $adminInfo['email'] . ') with role: ' . $adminInfo['role'], null, $adminInfo, null);
            } catch (\Exception $e) {
                \Log::warning('Failed to log audit for admin deletion: ' . $e->getMessage());
            }
            
            return redirect()->route('admin.admins.index')
                ->with('success', 'Admin deleted successfully!');
                
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while deleting the admin: ' . $e->getMessage());
        }
    }

    // Profile Methods
    public function profile()
    {
        return view('admin.profile');
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:32',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::delete('public/' . $user->profile_image);
            }
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $data['profile_image'] = $path;
        }

        $user->update($data);

        return back()->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password updated successfully!');
    }

    public function updatePreferences(Request $request)
    {
        // Store preferences logic here
        return back()->with('success', 'Preferences updated successfully!');
    }

    public function paySalary(Request $request, $id)
    {
        $admin = User::findOrFail($id);
        $currentUser = Auth::user();

        // Authorization check
        if (!in_array($currentUser->role, ['super_admin', 'admin', 'accountant'])) {
            return back()->with('error', 'You are not authorized to process salary payments.');
        }

        // Hierarchy Logic for Accountants
        if ($currentUser->role === 'accountant') {
            if (in_array($admin->role, ['super_admin', 'admin', 'manager', 'accountant'])) {
                return back()->with('error', 'You do not have permission to pay salary to this user.');
            }
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        try {
            // Create an Expense record
            // Create an Expense record
            \App\Models\Expense::create([
                'amount' => $validated['amount'],
                'category' => 'Salary',
                'department_id' => $admin->department_id ?? \App\Models\Department::where('name', 'LIKE', '%GENERAL%')->first()?->id ?? \App\Models\Department::first()?->id,
                'staff_id' => $admin->id,
                'date' => $validated['date'],
                'approved_by_id' => $currentUser->id,
                'payment_method' => $validated['payment_method'],
                'notes' => "Salary Payment for {$admin->name}. " . ($validated['notes'] ?? ''),
            ]);

            return redirect()->back()->with('success', "Salary payment of " . number_format($validated['amount']) . " TZS for {$admin->name} has been processed and recorded as an expense.");

        } catch (\Exception $e) {
            \Log::error('Error processing salary payment: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to process salary payment: ' . $e->getMessage());
        }
    }

    public function payAllSalaries(Request $request)
    {
        $currentUser = Auth::user();

        // Authorization check
        if (!in_array($currentUser->role, ['super_admin', 'admin', 'accountant'])) {
            return back()->with('error', 'You are not authorized to process salary payments.');
        }

        $query = User::where('monthly_salary', '>', 0);

        // Apply Hierarchy Logic (Same as index)
        if ($currentUser->role === 'accountant') {
            $query->whereNotIn('role', ['super_admin', 'admin', 'manager', 'accountant']);
        } elseif ($currentUser->role === 'admin') {
            $query->where('role', '!=', 'super_admin');
        }

        // Apply filters if any (role, department from request)
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $usersToPay = $query->get();

        if ($usersToPay->isEmpty()) {
            return back()->with('warning', 'No users found with a set salary for the selected filters.');
        }

        $paymentMethod = $request->input('payment_method', 'Bank Transfer');
        $paymentDate = $request->input('date', date('Y-m-d'));
        $monthYear = $request->input('month_year', date('F Y'));

        try {
            \DB::beginTransaction();

            foreach ($usersToPay as $admin) {
                \App\Models\Expense::create([
                    'amount' => $admin->monthly_salary,
                    'category' => 'Salary',
                    'department_id' => $admin->department_id ?? \App\Models\Department::where('name', 'LIKE', '%GENERAL%')->first()?->id ?? \App\Models\Department::first()?->id,
                    'staff_id' => $admin->id,
                    'date' => $paymentDate,
                    'approved_by_id' => $currentUser->id,
                    'payment_method' => $paymentMethod,
                    'notes' => "Bulk Salary Payment for {$admin->name}. Salary for {$monthYear}",
                ]);
            }

            \DB::commit();

            return redirect()->back()->with('success', "Salary payments for " . $usersToPay->count() . " staff members have been processed and recorded as expenses.");

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error processing bulk salary payment: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to process bulk salary payment: ' . $e->getMessage());
        }
    }
}
