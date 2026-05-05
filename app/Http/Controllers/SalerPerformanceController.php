<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\DesignTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SalerPerformanceController extends Controller
{
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

        // Summary Stats (of whatever is in $salers)
        $summary = [
            'total_orders' => 0, // Orders are now excluded from performance per request
            'total_tasks' => $salers->sum('design_tasks_count'),
            'total_revenue' => $salers->sum(function($s) {
                return $s->designTasks->where('status', '!=', DesignTask::STATUS_CANCELLED)->sum('price');
            }),
            'active_salers' => $salers->where('is_active', true)->count(),
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

        // Calculate Summary
        $summary = [
            'total_orders' => $salers->sum(fn($s) => $s->salerOrders->count()),
            'design_tasks' => $salers->sum(fn($s) => $s->designTasks->count()),
            'total_revenue' => $salers->sum(fn($s) => 
                $s->salerOrders->where('approval_status', 'approved')->sum('total_amount') + 
                $s->designTasks->sum('price')
            ),
            'avg_deal' => 0,
        ];
        
        $totalTransactions = $summary['total_orders'] + $summary['design_tasks'];
        $summary['avg_deal'] = $totalTransactions > 0 ? $summary['total_revenue'] / $totalTransactions : 0;

        if ($type === 'pdf') {
            // If single saler, get more details
            $history = [];
            $recentOrders = [];
            $recentTasks = [];
            
            if ($salerId || $user->role === 'saler') {
                $saler = $salers->first();
                // Get last 6 months summary for the individual report
                for ($i = 0; $i < 6; $i++) {
                    $date = now()->subMonths($i);
                    $month = $date->month;
                    $year = $date->year;
                    
                    $history[] = [
                        'month' => $date->format('F Y'),
                        'revenue' => $saler->salerOrders()->whereYear('created_at', $year)->whereMonth('created_at', $month)->where('approval_status', 'approved')->where('payment_status', 'paid')->sum('total_amount') + 
                                   $saler->designTasks()->whereYear('created_at', $year)->whereMonth('created_at', $month)->whereIn('status', [DesignTask::STATUS_COMPLETED, DesignTask::STATUS_SUPER_COMPLETED, DesignTask::STATUS_PRINTED])->sum('amount_paid'),
                        'orders' => $saler->salerOrders()->whereYear('created_at', $year)->whereMonth('created_at', $month)->count(),
                        'tasks' => $saler->designTasks()->whereYear('created_at', $year)->whereMonth('created_at', $month)->count(),
                    ];
                }
                
                $recentOrders = $saler->salerOrders()->with('customer')->latest()->take(10)->get();
                $recentTasks = $saler->designTasks()->with('customer')->latest()->take(10)->get();
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.exports.saler-performance', compact('salers', 'title', 'summary', 'history', 'recentOrders', 'recentTasks'));
            return $pdf->download('saler-performance-report.pdf');
        } else {
            // Excel Export
            return $this->exportToExcel([
                ['Salesperson', 'Total Orders', 'Design Tasks', 'Total Revenue'],
                ...$salers->map(fn($s) => [
                    $s->name,
                    $s->saler_orders_count ?? $s->salerOrders->count(),
                    $s->design_tasks_count ?? $s->designTasks->count(),
                    'TZS ' . number_format($s->salerOrders->where('approval_status', 'approved')->where('payment_status', 'paid')->sum('total_amount') + $s->designTasks->whereIn('status', [DesignTask::STATUS_COMPLETED, DesignTask::STATUS_SUPER_COMPLETED, DesignTask::STATUS_PRINTED])->sum('amount_paid'))
                ])
            ], 'saler-performance-report.csv');
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
