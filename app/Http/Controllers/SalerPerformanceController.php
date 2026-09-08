<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\DesignTask;
use App\Models\SalesTarget;
use App\Support\Concerns\ResolvesSalesTargets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SalerPerformanceController extends Controller
{
    use ResolvesSalesTargets;


    /**
     * Display the saler performance dashboard.
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        $user = Auth::user();

        // Handle date range based on period
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

        $dateFrom = $dateRange[0] ? $dateRange[0]->format('Y-m-d') : null;
        $dateTo = $dateRange[1] ? $dateRange[1]->format('Y-m-d') : null;

        $salerId = $request->get('saler_id');
        
        // Get all salers for the filter dropdown
        $allSalers = User::where('role', 'saler')->get();

        $query = User::where('role', 'saler')
            ->withCount(['designTasks' => function($q) use ($dateFrom, $dateTo) {
                if ($dateFrom && $dateTo) {
                    $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
                }
                $q->where('status', '!=', DesignTask::STATUS_CANCELLED);
            }])
            ->withCount(['salerOrders' => function($q) use ($dateFrom, $dateTo) {
                if ($dateFrom && $dateTo) {
                    $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
                }
            }])
            ->with(['designTasks' => function($q) use ($dateFrom, $dateTo) {
                if ($dateFrom && $dateTo) {
                    $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
                }
                // Design tasks are the sole source of saler performance revenue —
                // exclude cancelled tasks so this matches $summary['total_revenue'] below.
                $q->where('status', '!=', DesignTask::STATUS_CANCELLED);
            }])
            ->with(['salerOrders' => function($q) use ($dateFrom, $dateTo) {
                if ($dateFrom && $dateTo) {
                    $q->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
                }
            }]);

        // If user is a saler, show only their data
        if ($user->role === 'saler') {
            $query->where('id', $user->id);
            $salerId = $user->id; // Force own ID
        } elseif ($salerId) {
            // Admin filtering for specific saler
            $query->where('id', $salerId);
        }

        $salers = $query->get();

        $leadsQuery = \App\Models\Lead::query();
        $payingCustomersQuery = \App\Models\Customer::where(function($q) {
            $q->where('purchase_count', '>', 0)
              ->orWhereHas('orders');
        });

        if ($salerId || $user->role === 'saler') {
            $sid = $salerId ?: $user->id;
            $leadsQuery->where('assigned_seller_id', $sid);
            $payingCustomersQuery->where('account_owner_id', $sid);
        }

        $totalLeads = $leadsQuery->count();
        $payingCustomersCount = $payingCustomersQuery->count();
        $unconvertedLeadsCount = max(0, $totalLeads - $payingCustomersCount);

        // Count unique customers from design tasks in the period
        $customerCountQuery = \App\Models\DesignTask::query()
            ->where('status', '!=', DesignTask::STATUS_CANCELLED);
        if ($dateFrom && $dateTo) {
            $customerCountQuery->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        }
        if ($salerId || $user->role === 'saler') {
            $sid = $salerId ?: $user->id;
            $customerCountQuery->where(function($q) use ($sid) {
                $q->where('receptionist_id', $sid)
                  ->orWhere('saler_id', $sid);
            });
        }
        $totalCustomers = $customerCountQuery->distinct('customer_id')->count('customer_id');

        // Summary Stats
        $summary = [
            'total_orders' => 0,
            'total_tasks' => $salers->sum('design_tasks_count'),
            'total_revenue' => $salers->sum(function($s) {
                return $s->designTasks->where('status', '!=', DesignTask::STATUS_CANCELLED)->sum('price');
            }),
            'balance_due' => $salers->sum(function($s) {
                return $s->designTasks->where('status', '!=', DesignTask::STATUS_CANCELLED)->sum('balance');
            }),
            'total_customers' => $totalCustomers,
            'active_salers' => $salers->where('is_active', true)->count(),
            'total_leads' => $totalLeads,
            'paying_customers_count' => $payingCustomersCount,
            'unconverted_leads_count' => $unconvertedLeadsCount,
        ];

        // Trend Data (Last 30 Days)
        $trendData = [];
        $startDate = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            
            $dayOrdersRevenue = 0;
            $dayTasksRevenue = 0;
            
            foreach($salers as $saler) {
                $dayTasksRevenue += $saler->designTasks->filter(fn($t) => $t->created_at->format('Y-m-d') === $dateStr && $t->status !== DesignTask::STATUS_CANCELLED)->sum('price');
            }
            
            $trendData[] = [
                'date' => $dateStr,
                'revenue' => $dayTasksRevenue,
                'orders' => 0,
                'tasks' => $dayTasksRevenue
            ];
        }

        // Status Distribution
        $statusDistribution = [
            'Pending' => 0,
            'Processing' => 0,
            'Completed' => 0,
            'Cancelled' => 0,
        ];
        
        foreach($salers as $saler) {
            foreach($saler->salerOrders as $order) {
                $lbl = ucfirst($order->approval_status);
                if (isset($statusDistribution[$lbl])) {
                    $statusDistribution[$lbl]++;
                } else {
                    $statusDistribution['Processing']++;
                }
            }
        }
            
        // Fetch recent data for detailed reporting (Advanced Printing)
        $orderDetailQuery = Order::query();
        $taskDetailQuery = DesignTask::query();

        if ($dateFrom && $dateTo) {
            $orderDetailQuery->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            $taskDetailQuery->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        }
        
        if ($salerId || $user->role === 'saler') {
            $sid = $salerId ?: $user->id;
            $orderDetailQuery->where('saler_id', $sid);
            $taskDetailQuery->where('saler_id', $sid);
        }
        
        $recentOrders = $orderDetailQuery->with(['user', 'items'])->latest()->take(30)->get();
        $recentTasks = $taskDetailQuery->with('customer')->latest()->take(30)->get();

        return view('admin.saler-performance.index', compact('salers', 'period', 'summary', 'allSalers', 'salerId', 'dateFrom', 'dateTo', 'trendData', 'statusDistribution', 'recentOrders', 'recentTasks'));
    }

    public function print(Request $request)
    {
        $period = $request->get('period', 'month');

        $dateRange = match($period) {
            'today'     => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week'      => [now()->startOfWeek(), now()->endOfWeek()],
            'month'     => [now()->startOfMonth(), now()->endOfMonth()],
            '6_months'  => [now()->subMonths(6)->startOfDay(), now()->endOfDay()],
            'year'      => [now()->startOfYear(), now()->endOfYear()],
            '2_years'   => [now()->subYears(2)->startOfDay(), now()->endOfDay()],
            'custom'    => [
                $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : now()->startOfMonth(),
                $request->filled('end_date')   ? Carbon::parse($request->end_date)->endOfDay()     : now()->endOfDay(),
            ],
            'all'       => [Carbon::createFromDate(2000, 1, 1), now()->endOfDay()],
            default     => [now()->startOfMonth(), now()->endOfMonth()],
        };

        [$startDate, $endDate] = $dateRange;

        $periodLabel = match($period) {
            'today'     => 'Today — ' . now()->format('M d, Y'),
            'yesterday' => 'Yesterday — ' . now()->subDay()->format('M d, Y'),
            'week'      => 'This Week (' . now()->startOfWeek()->format('M d') . ' – ' . now()->endOfWeek()->format('M d, Y') . ')',
            'month'     => 'This Month — ' . now()->format('F Y'),
            '6_months'  => 'Last 6 Months',
            'year'      => 'This Year — ' . now()->format('Y'),
            '2_years'   => 'Last 2 Years',
            'custom'    => $startDate->format('M d, Y') . ' – ' . $endDate->format('M d, Y'),
            'all'       => 'All Time',
            default     => ucfirst(str_replace('_', ' ', $period)),
        };

        $applyPeriod = fn($query, $col = 'created_at') => $query->whereBetween($col, [$startDate, $endDate]);

        $authUser = Auth::user();

        $userQuery = User::whereIn('role', ['saler', 'admin', 'manager'])->orderBy('name');
        if ($authUser->role === 'saler') {
            $userQuery->where('id', $authUser->id);
        } elseif ($request->filled('saler_id')) {
            $userQuery->where('id', (int) $request->saler_id);
        }

        $salers = $userQuery->get()
            ->map(function ($user) use ($applyPeriod, $startDate, $endDate, $period) {
                // Design tasks are the sole source of saler performance revenue (orders are excluded).
                $taskQuery  = DesignTask::where('saler_id', $user->id)->where('status', '!=', DesignTask::STATUS_CANCELLED);
                $totalSales = $applyPeriod(clone $taskQuery)->sum('price') ?? 0;

                [$targetAmount, $targetNote] = $this->resolveSalesTarget(
                    SalesTarget::where('seller_id', $user->id),
                    $startDate, $endDate, $period
                );

                $achievement  = $targetAmount > 0 ? round(($totalSales / $targetAmount) * 100, 1) : 0;
                $taskCount    = $applyPeriod(clone $taskQuery)->count();

                return [
                    'id'            => $user->id,
                    'name'          => $user->name,
                    'role'          => $user->role,
                    'total_sales'   => $totalSales,
                    'target_amount' => $targetAmount,
                    'target_note'   => $targetNote,
                    'achievement'   => $achievement,
                    'task_count'    => $taskCount,
                ];
            })
            ->sortByDesc('total_sales')
            ->values();

        $grandTotal   = $salers->sum('total_sales');
        $singleSaler  = $salers->count() === 1 ? $salers->first() : null;
        $singleSalerTasks = collect();

        $summary = [
            'total_tasks' => $salers->sum('task_count'),
            'total_revenue' => $salers->sum('total_sales'),
            'active_salers' => $salers->count(),
        ];

        if ($singleSaler) {
            $singleSalerTasks = DesignTask::query()
                ->with(['customer:id,name,company_name,phone,whatsapp_number'])
                ->where('saler_id', $singleSaler['id'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', '!=', DesignTask::STATUS_CANCELLED)
                ->orderByDesc('created_at')
                ->get(['id', 'task_code', 'title', 'status', 'customer_id', 'price', 'amount_paid', 'balance']);

            $summary['total_tasks'] = $singleSalerTasks->count();
            $summary['total_revenue'] = (float) $singleSalerTasks->sum('price');
            $summary['total_paid'] = (float) $singleSalerTasks->sum('amount_paid');
            $summary['balance_due'] = (float) $singleSalerTasks->sum('balance');
        }

        return view('admin.reports.exports.saler-performance-print', compact('salers', 'periodLabel', 'grandTotal', 'period', 'startDate', 'endDate', 'singleSaler', 'singleSalerTasks', 'summary'));
    }

    /**
     * Display detailed performance for a single saler.
     */
    public function show($id, Request $request)
    {
        $saler = User::where('role', 'saler')->findOrFail($id);
        $timeframe = $request->get('timeframe', 'monthly');
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        // Check access
        $user = Auth::user();
        if ($user->role === 'saler' && $user->id != $id) {
            abort(403);
        }

        // Fetch recent data for detailed reporting
        $recentOrders = $saler->salerOrders()->with('user')->latest()->take(20)->get();
        $recentTasks = $saler->designTasks()->with('customer')->latest()->take(20)->get();

        return view('admin.saler-performance.show', compact('saler', 'timeframe', 'dateFrom', 'dateTo', 'recentOrders', 'recentTasks'));
    }

    /**
     * Get performance data for charts.
     */
    public function getChartData(Request $request)
    {
        $timeframe = $request->get('timeframe', 'monthly');
        $salerId = $request->get('saler_id');
        
        $data = [
            'labels' => [],
            'revenue' => [],
            'customers' => [],
            'orders_revenue' => [],
            'tasks_revenue' => [],
            'orders_count' => [],
            'tasks_count' => [],
        ];

        switch ($timeframe) {
            case 'daily':
                $start = now()->subDays(14);
                $end = now();
                $format = 'M d';
                break;
            case 'weekly':
                $start = now()->subWeeks(8);
                $end = now();
                $format = '\Ww';
                break;
            case 'monthly':
            default:
                $start = now()->subMonths(12);
                $end = now();
                $format = 'M Y';
                break;
            case 'yearly':
                $start = now()->subYears(5);
                $end = now();
                $format = 'Y';
                break;
        }

        // Generate date ranges
        $periods = $this->generatePeriods($start, $end, $timeframe);
        
        foreach ($periods as $period) {
            $data['labels'][] = $period['label'];
            
            // Calculate revenue and customers for this period
            $stats = $this->getStatsForPeriod($period['start'], $period['end'], $salerId);
            $data['revenue'][] = $stats['revenue'];
            $data['customers'][] = $stats['customers'];
            $data['orders_revenue'][] = $stats['orders_revenue'];
            $data['tasks_revenue'][] = $stats['tasks_revenue'];
            $data['orders_count'][] = $stats['orders_count'];
            $data['tasks_count'][] = $stats['tasks_count'];
        }

        return response()->json($data);
    }

    private function generatePeriods($start, $end, $timeframe)
    {
        $periods = [];
        $current = clone $start;

        while ($current <= $end) {
            $pStart = clone $current;
            
            switch ($timeframe) {
                case 'daily':
                    $pEnd = (clone $current)->endOfDay();
                    $label = $current->format('M d');
                    $current->addDay();
                    break;
                case 'weekly':
                    $pStart = (clone $current)->startOfWeek();
                    $pEnd = (clone $current)->endOfWeek();
                    $label = 'Week ' . $current->weekOfYear;
                    $current->addWeek();
                    break;
                case 'monthly':
                    $pStart = (clone $current)->startOfMonth();
                    $pEnd = (clone $current)->endOfMonth();
                    $label = $current->format('M Y');
                    $current->addMonth();
                    break;
                case 'yearly':
                    $pStart = (clone $current)->startOfYear();
                    $pEnd = (clone $current)->endOfYear();
                    $label = $current->format('Y');
                    $current->addYear();
                    break;
            }
            
            $periods[] = [
                'start' => $pStart,
                'end' => $pEnd,
                'label' => $label
            ];
        }

        return $periods;
    }

    private function getStatsForPeriod($start, $end, $salerId = null)
    {
        // Design Tasks Stats (Now the sole source of performance revenue)
        $taskBaseQuery = DesignTask::whereBetween('created_at', [$start, $end])
            ->where('status', '!=', DesignTask::STATUS_CANCELLED);
            
        if ($salerId) {
            $taskBaseQuery->where('saler_id', $salerId);
        }
        
        // Use separate query clones to avoid modifying the base query
        $taskRevenue = (clone $taskBaseQuery)->sum('price');
        $taskCustomers = (clone $taskBaseQuery)->distinct('customer_id')->count('customer_id');
        $taskCount = (clone $taskBaseQuery)->count();

        // Orders Stats are now excluded from performance
        $orderRevenue = 0;
        $orderCustomers = 0;
        $orderCount = 0;

        return [
            'revenue' => $taskRevenue,
            'customers' => $taskCustomers,
            'orders_revenue' => $orderRevenue,
            'tasks_revenue' => $taskRevenue,
            'orders_count' => $orderCount,
            'tasks_count' => $taskCount,
        ];
    }
    public function export(Request $request)
    {
        $type = $request->get('type', 'pdf');
        $user = Auth::user();
        $salerId = $request->get('saler_id');
        $period = $request->get('period', 'month');

        // Handle date range based on period
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

        $dateFrom = $dateRange[0];
        $dateTo = $dateRange[1];

        $salerQuery = User::where('role', 'saler');
        if ($salerId) {
            $salerQuery->where('id', $salerId);
        } elseif ($user->role === 'saler') {
            $salerQuery->where('id', $user->id);
        }

        $salers = $salerQuery->with(['designTasks' => function($q) use ($dateFrom, $dateTo) {
                if ($dateFrom && $dateTo) {
                    $q->whereBetween('created_at', [$dateFrom, $dateTo]);
                }
                $q->where('status', '!=', DesignTask::STATUS_CANCELLED);
            }, 'salerOrders' => function($q) use ($dateFrom, $dateTo) {
                if ($dateFrom && $dateTo) {
                    $q->whereBetween('created_at', [$dateFrom, $dateTo]);
                }
            }])->get();

        $title = 'Saler Performance Report';

        // Calculate Summary — design tasks are the sole source of saler performance revenue (orders excluded)
        $summary = [
            'design_tasks' => $salers->sum(fn($s) => $s->designTasks->count()),
            'total_revenue' => $salers->sum(fn($s) => $s->designTasks->sum('price')),
            'avg_deal' => 0,
        ];

        $summary['avg_deal'] = $summary['design_tasks'] > 0 ? $summary['total_revenue'] / $summary['design_tasks'] : 0;

        if ($type === 'pdf') {
            // Build same rich data structure as print() for a consistent PDF design
            $periodLabel = match($period) {
                'today'     => 'Today — ' . now()->format('M d, Y'),
                'yesterday' => 'Yesterday — ' . now()->subDay()->format('M d, Y'),
                'week'      => 'This Week (' . now()->startOfWeek()->format('M d') . ' – ' . now()->endOfWeek()->format('M d, Y') . ')',
                'month'     => 'This Month — ' . now()->format('F Y'),
                '6_months'  => 'Last 6 Months',
                'year'      => 'This Year — ' . now()->format('Y'),
                '2_years'   => 'Last 2 Years',
                'custom'    => ($dateFrom ? $dateFrom->format('M d, Y') : '?') . ' – ' . ($dateTo ? $dateTo->format('M d, Y') : '?'),
                'all'       => 'All Time',
                default     => ucfirst(str_replace('_', ' ', $period)),
            };

            $applyPeriod = fn($query) => ($dateFrom && $dateTo)
                ? $query->whereBetween('created_at', [$dateFrom, $dateTo])
                : $query;

            $salersPdf = $salers->map(function ($user) use ($applyPeriod, $dateFrom, $dateTo, $period) {
                $taskQuery   = DesignTask::where('saler_id', $user->id)->where('status', '!=', DesignTask::STATUS_CANCELLED);
                $totalSales  = $applyPeriod(clone $taskQuery)->sum('price') ?? 0;
                $taskCount   = $applyPeriod(clone $taskQuery)->count();

                [$targetAmount] = $this->resolveSalesTarget(
                    SalesTarget::where('seller_id', $user->id),
                    $dateFrom ?? now()->startOfMonth(), $dateTo ?? now()->endOfMonth(), $period
                );

                $achievement = $targetAmount > 0 ? round(($totalSales / $targetAmount) * 100, 1) : 0;

                return [
                    'id'            => $user->id,
                    'name'          => $user->name,
                    'role'          => $user->role,
                    'total_sales'   => $totalSales,
                    'target_amount' => $targetAmount,
                    'achievement'   => $achievement,
                    'task_count'    => $taskCount,
                ];
            })->sortByDesc('total_sales')->values();

            $grandTotal  = $salersPdf->sum('total_sales');
            $singleSaler = $salersPdf->count() === 1 ? $salersPdf->first() : null;

            // 6-month history for single saler view
            $history = [];
            $singleSalerTasks = collect();
            if ($singleSaler) {
                $salerUser = $salers->first();
                for ($i = 0; $i < 6; $i++) {
                    $date = now()->subMonths($i);
                    $history[] = [
                        'month'   => $date->format('F Y'),
                        'revenue' => DesignTask::where('saler_id', $singleSaler['id'])->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->where('status', '!=', DesignTask::STATUS_CANCELLED)->sum('price'),
                        'tasks'   => DesignTask::where('saler_id', $singleSaler['id'])->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->where('status', '!=', DesignTask::STATUS_CANCELLED)->count(),
                    ];
                }
                $singleSalerTasks = DesignTask::with(['customer:id,name,phone'])
                    ->where('saler_id', $singleSaler['id'])
                    ->when($dateFrom && $dateTo, fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]))
                    ->where('status', '!=', DesignTask::STATUS_CANCELLED)
                    ->orderByDesc('created_at')
                    ->get(['id', 'task_code', 'title', 'status', 'customer_id', 'price', 'amount_paid', 'balance']);
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.exports.saler-performance', compact(
                'salersPdf', 'grandTotal', 'periodLabel', 'singleSaler',
                'singleSalerTasks', 'history', 'dateFrom', 'dateTo', 'summary'
            ))->setPaper('a4', 'portrait');
            return $pdf->download('saler-performance-report.pdf');
        } else {
            // Multi-sheet XLSX matching the PDF/print content
            $periodLabel = ucfirst(str_replace('_', ' ', $period));
            $dateLabel   = ($dateFrom && $dateTo)
                ? $dateFrom->format('Y-m-d') . ' to ' . $dateTo->format('Y-m-d')
                : 'All Time';

            // Sheet 1 — Summary
            $summarySheet = [
                'title'    => 'Summary',
                'headings' => ['Metric', 'Value'],
                'rows'     => [
                    ['Report Period',        $dateLabel],
                    ['Generated',            now()->format('Y-m-d H:i')],
                    ['Total Design Tasks',   $summary['design_tasks']],
                    ['Total Revenue (TZS)',  number_format($summary['total_revenue'], 2)],
                    ['Avg Deal Value (TZS)', number_format($summary['avg_deal'], 2)],
                ],
            ];

            // Sheet 2 — Saler Breakdown
            $breakdownRows = [];
            foreach ($salers as $saler) {
                $tasks   = $saler->designTasks->count();
                $revenue = $saler->designTasks->sum('price');
                $orders  = $saler->salerOrders->count();
                $breakdownRows[] = [
                    $saler->name,
                    ucfirst($saler->role),
                    $orders,
                    $tasks,
                    number_format($revenue, 2),
                    $saler->is_active ? 'Active' : 'Inactive',
                ];
            }
            $breakdownSheet = [
                'title'    => 'Saler Breakdown',
                'headings' => ['Salesperson', 'Role', 'Total Orders', 'Design Tasks', 'Revenue (TZS)', 'Status'],
                'rows'     => $breakdownRows,
            ];

            // Sheet 3 — Task Details
            $taskRows = [];
            foreach ($salers as $saler) {
                foreach ($saler->designTasks as $task) {
                    $taskRows[] = [
                        $task->task_code,
                        $task->title,
                        $saler->name,
                        optional($task->created_at)->format('Y-m-d'),
                        ucfirst(str_replace('_', ' ', $task->status)),
                        number_format($task->price, 2),
                        number_format($task->amount_paid, 2),
                        number_format($task->balance, 2),
                    ];
                }
            }
            $taskSheet = [
                'title'    => 'Task Details',
                'headings' => ['Task Code', 'Title', 'Salesperson', 'Date', 'Status', 'Price (TZS)', 'Paid (TZS)', 'Balance (TZS)'],
                'rows'     => $taskRows,
            ];

            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\MultiSheetReportExport([$summarySheet, $breakdownSheet, $taskSheet]),
                "saler-performance-{$period}-" . now()->format('Y-m-d') . '.xlsx'
            );
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
}
