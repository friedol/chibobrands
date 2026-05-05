<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DesignTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DesignTaskReportController extends Controller
{
    /**
     * Display the advanced design task reports.
     */
    public function index(Request $request)
    {
        // Users for filters
        $designers = User::where('role', 'designer')->get();
        $receptionists = User::where('role', 'receptionist')->get();
        $operators = User::whereIn('role', ['operator', 'admin', 'super_admin'])->get();
        $salers = User::where('role', 'saler')->get();

        // Default filters
        $dateFrom = $request->input('date_from', Carbon::now()->startOfYear()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->endOfDay()->format('Y-m-d'));
        $period = $request->input('period', 'day'); // day, week, month, quarter, year

        // Handle quick period filters (like design task index)
        if ($request->filled('filter_period')) {
            $filterPeriod = $request->filter_period;
            $now = Carbon::now();
            
            switch ($filterPeriod) {
                case 'today':
                    $dateFrom = $now->startOfDay()->format('Y-m-d');
                    $dateTo = $now->endOfDay()->format('Y-m-d');
                    $period = 'hour'; // Group by hour for today
                    break;
                case 'week':
                    $dateFrom = $now->startOfWeek()->format('Y-m-d');
                    $dateTo = $now->endOfWeek()->format('Y-m-d');
                    $period = 'day'; // Group by day for week
                    break;
                case 'month':
                    $dateFrom = $now->startOfMonth()->format('Y-m-d');
                    $dateTo = $now->endOfMonth()->format('Y-m-d');
                    $period = 'day'; // Group by day for month
                    break;
                case 'half_year':
                    $dateFrom = $now->copy()->subMonths(6)->format('Y-m-d');
                    $dateTo = $now->format('Y-m-d');
                    $period = 'month'; // Group by month for 6 months
                    break;
                case 'year':
                    $dateFrom = $now->startOfYear()->format('Y-m-d');
                    $dateTo = $now->endOfYear()->format('Y-m-d');
                    $period = 'month'; // Group by month for year
                    break;
            }
        }

        // Base Query
        $tasksQuery = DesignTask::query()
            ->whereBetween('created_at', [
                Carbon::parse($dateFrom)->startOfDay(), 
                Carbon::parse($dateTo)->endOfDay()
            ]);

        // Apply filters
        if ($request->filled('designer_id')) {
            $tasksQuery->where('designer_id', $request->designer_id);
        }
        if ($request->filled('receptionist_id')) {
            $tasksQuery->where('receptionist_id', $request->receptionist_id);
        }
        if ($request->filled('saler_id')) {
            $tasksQuery->where('saler_id', $request->saler_id);
        }
        if ($request->filled('operator_id')) {
            $tasksQuery->where('operator_id', $request->operator_id);
        }
        if ($request->filled('status')) {
            $tasksQuery->where('status', $request->status);
        }

        // Clone query for stats to avoid modifying the original query object state excessively if needed
        // but here we can just execute aggregates on the builder
        
        // 1. Summary Statistics
        $totalTasks = (clone $tasksQuery)->count();
        $completedTasks = (clone $tasksQuery)->whereIn('status', [
            DesignTask::STATUS_COMPLETED, 
            DesignTask::STATUS_CONFIRMED, 
            DesignTask::STATUS_SUPER_COMPLETED
        ])->count();
        
        $totalRevenue = (clone $tasksQuery)->sum('amount_paid') + (clone $tasksQuery)->sum('balance');
        $totalPaid = (clone $tasksQuery)->sum('amount_paid');
        $totalBalance = (clone $tasksQuery)->sum('balance');
        
        // Avg completion time (for completed tasks) - precise calculation in PHP or DB
        // DB approach for better performance on large datasets
        // Assuming MySQL/Postgres timestamp diff
        // For simplicity and cross-db compatibility, fetching completed tasks and averaging in collection is okay for moderate datasets
        // But let's try a DB raw query for efficiency
        $avgCompletionHours = 0;
        // Only if we have completed tasks
        if ($completedTasks > 0) {
             // Calculate difference between created_at and completed_at
             // reliable way in MySQL: AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at))
             $avgCompletionHours = (clone $tasksQuery)
                ->whereNotNull('completed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_hours')
                ->value('avg_hours');
        }

        // 2. Charts Data Construction
        $chartData = $this->getChartData((clone $tasksQuery), $period, $dateFrom, $dateTo);

        // B. Status Distribution
        $statusDistribution = (clone $tasksQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // C. Priority Distribution
        $priorityDistribution = (clone $tasksQuery)
            ->select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->get()
            ->mapWithKeys(function ($item) {
                $labels = [1 => 'High', 3 => 'Medium', 5 => 'Low'];
                return [$labels[$item->priority] ?? "Priority $item->priority" => $item->count];
            })
            ->toArray();

        // D. Performance Rankings
        // Designer Rankings
        $topDesigners = (clone $tasksQuery)
            ->whereNotNull('designer_id')
            ->select('designer_id', DB::raw('count(*) as task_count'), DB::raw('SUM(price) as total_revenue'))
            ->groupBy('designer_id')
            ->with('designer:id,name')
            ->orderByDesc('task_count')
            ->limit(5)
            ->get();

        // Receptionist Rankings
        $topReceptionists = (clone $tasksQuery)
            ->whereNotNull('receptionist_id')
            ->select('receptionist_id', DB::raw('count(*) as task_count'))
            ->groupBy('receptionist_id')
            ->with('receptionist:id,name')
            ->orderByDesc('task_count')
            ->limit(5)
            ->get();

        // Saler Rankings
        $topSalers = (clone $tasksQuery)
            ->whereNotNull('saler_id')
            ->select('saler_id', DB::raw('count(*) as task_count'))
            ->groupBy('saler_id')
            ->with('saler:id,name')
            ->orderByDesc('task_count')
            ->limit(5)
            ->get();

        // Operator Rankings
        $topOperators = (clone $tasksQuery)
            ->whereNotNull('operator_id')
            ->select('operator_id', DB::raw('count(*) as task_count'))
            ->groupBy('operator_id')
            ->with('operator:id,name')
            ->orderByDesc('task_count')
            ->limit(5)
            ->get();

        // 3. Operational Glimpse
        $printingTasks = (clone $tasksQuery)->whereIn('status', [DesignTask::STATUS_PRINTING, DesignTask::STATUS_PRINTED])->count();
        $revisionCount = \App\Models\TaskUpdate::whereIn('task_id', (clone $tasksQuery)->pluck('id'))
            ->where('type', \App\Models\TaskUpdate::TYPE_REVISION)
            ->count();
        
        $overdueTasks = (clone $tasksQuery)
            ->whereNull('completed_at')
            ->where('deadline', '<', Carbon::now())
            ->count();
            
        // 4. Detailed List for Printing
        $recentTasks = (clone $tasksQuery)
            ->with(['customer', 'designer', 'saler'])
            ->latest()
            ->take(50)
            ->get();

        return view('admin.design-tasks.reports', compact(
            'designers', 'receptionists', 'operators', 'salers',
            'dateFrom', 'dateTo', 'period',
            'totalTasks', 'completedTasks', 'totalRevenue', 'totalPaid', 'totalBalance', 'avgCompletionHours',
            'chartData', 'statusDistribution', 'priorityDistribution',
            'topDesigners', 'topReceptionists', 'topSalers', 'topOperators',
            'printingTasks', 'revisionCount', 'overdueTasks',
            'recentTasks'
        ));
    }

    /**
     * Helper to group data by period
     */
    private function getChartData($query, $period, $dateFrom, $dateTo)
    {
        $groupBy = '';
        $dateFormat = '';

        switch ($period) {
            case 'hour':
                $groupBy = 'DATE_FORMAT(created_at, "%Y-%m-%d %H:00")';
                $dateFormat = 'M d, H:i';
                break;
            case 'day':
                $groupBy = 'DATE(created_at)';
                $dateFormat = 'M d';
                break;
            case 'week':
                // MySQL week mode 1: Monday-Sunday
                $groupBy = 'YEARWEEK(created_at, 1)';
                $dateFormat = 'W, Y'; // Week number
                break;
            case 'month':
                $groupBy = 'DATE_FORMAT(created_at, "%Y-%m")';
                $dateFormat = 'M Y';
                break;
            case 'half_month':
                $groupBy = 'CONCAT(YEAR(created_at), "-", LPAD(MONTH(created_at), 2, "0"), "-", IF(DAY(created_at) <= 15, "01-15", "16-end"))';
                $dateFormat = 'Y-m-d';
                break;
            case 'quarter':
                $groupBy = 'CONCAT(YEAR(created_at), "-Q", QUARTER(created_at))';
                $dateFormat = 'Q Y';
                break;
            case 'year':
                $groupBy = 'YEAR(created_at)';
                $dateFormat = 'Y';
                break;
            default: // day
                $groupBy = 'DATE(created_at)';
                $dateFormat = 'M d';
        }

        $results = $query->select(
            DB::raw("$groupBy as period"),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(price) as revenue')
        )
        ->groupBy('period')
        ->orderBy('period')
        ->get();

        // Fill in missing gaps (optional but good for charts)
        // For now, let's just return what we have to keep it simple, 
        // chart.js can handle dates on x-axis if we format them right.
        
        return $results;
    }
}
