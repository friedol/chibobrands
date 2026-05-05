<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\DesignTask;
use App\Models\User;
use App\Models\SalesTarget;
use App\Models\Department;
use App\Models\Product;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesDepartmentController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        $dateRange = $this->getDateRange($period, $request);
        $dateFrom = $dateRange[0] ? $dateRange[0]->format('Y-m-d') : null;
        $dateTo = $dateRange[1] ? $dateRange[1]->format('Y-m-d') : null;

        // A. Seller Performance & Rankings (Individual sales from DesignTasks)
        $sellerPerformance = User::where('role', 'saler')
            ->select('users.id', 'users.name')
            ->withSum(['designTasks' => function($q) use ($dateRange) {
                if ($dateRange[0]) $q->whereBetween('created_at', [$dateRange[0], $dateRange[1]]);
                $q->where('status', '!=', DesignTask::STATUS_CANCELLED);
            }], 'price')
            ->withCount(['designTasks' => function($q) use ($dateRange) {
                if ($dateRange[0]) $q->whereBetween('created_at', [$dateRange[0], $dateRange[1]]);
                $q->where('status', '!=', DesignTask::STATUS_CANCELLED);
            }])
            ->get()
            ->map(function($seller) {
                $seller->total_revenue = $seller->design_tasks_sum_price ?? 0;
                return $seller;
            })
            ->sortByDesc('total_revenue')
            ->values();

        $totalDepartmentRevenue = $sellerPerformance->sum('total_revenue');

        foreach ($sellerPerformance as $key => $seller) {
            $seller->ranking = $key + 1;
            $seller->contribution_percent = $totalDepartmentRevenue > 0 ? round(($seller->total_revenue / $totalDepartmentRevenue) * 100, 1) : 0;
            
            // Sales vs Target
            $target = SalesTarget::where('seller_id', $seller->id)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();
                
            $seller->active_target = $target ? $target->target_amount : 0;
            $seller->target_achievement = $seller->active_target > 0 ? round(($seller->total_revenue / $seller->active_target) * 100, 1) : 0;
        }

        // B. Sales Reports Summary
        $reportData = [
            'total_sales' => $totalDepartmentRevenue,
            'total_orders' => $sellerPerformance->sum('design_tasks_count'),
            'avg_order_value' => $sellerPerformance->sum('design_tasks_count') > 0 ? $totalDepartmentRevenue / $sellerPerformance->sum('design_tasks_count') : 0
        ];

        // Leads Management Overview
        $leadsStats = [
            'total' => Lead::count(),
            'pending' => Lead::where('status', 'pending')->count(),
            'converted' => Lead::where('status', 'converted')->count(),
            'conversion_rate' => Lead::count() > 0 ? round((Lead::where('status', 'converted')->count() / Lead::count()) * 100, 1) : 0
        ];

        return view('admin.sales-dept.index', compact('sellerPerformance', 'totalDepartmentRevenue', 'reportData', 'leadsStats', 'period', 'dateFrom', 'dateTo'));
    }

    public function targets(Request $request)
    {
        $query = SalesTarget::with(['seller', 'department']);
        
        // Seller filter
        if ($request->filled('seller_id') && $request->seller_id !== 'all') {
            $query->where('seller_id', $request->seller_id);
        }

        // Department filter
        if ($request->filled('department_id') && $request->department_id !== 'all') {
            $query->where('department_id', $request->department_id);
        }

        // Period filter
        if ($request->filled('period') && $request->period !== 'all') {
            $query->where('period', $request->period);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('end_date', '<=', $request->date_to);
        }

        $sellers = User::whereIn('role', ['saler', 'admin', 'super_admin'])->get();
        $departments = Department::all();
        $targets = $query->latest()->paginate(25)->withQueryString();
        
        return view('admin.sales-dept.targets', compact('sellers', 'departments', 'targets'));
    }

    public function printTargets(Request $request)
    {
        $query = SalesTarget::with(['seller', 'department']);
        
        if ($request->filled('seller_id') && $request->seller_id !== 'all') {
            $query->where('seller_id', $request->seller_id);
        }

        if ($request->filled('department_id') && $request->department_id !== 'all') {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('period') && $request->period !== 'all') {
            $query->where('period', $request->period);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('end_date', '<=', $request->date_to);
        }

        $targets = $query->latest()->get();
        
        return view('admin.sales-dept.print-targets', compact('targets'));
    }

    public function storeTarget(Request $request)
    {
        $validated = $request->validate([
            'seller_id' => 'required|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'target_amount' => 'required|numeric|min:0',
            'period' => 'required|in:monthly,quarterly,annual',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        SalesTarget::create($validated);

        return redirect()->back()->with('success', 'Sales target assigned successfully.');
    }

    public function updateTarget(Request $request, SalesTarget $target)
    {
        $validated = $request->validate([
            'seller_id' => 'required|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'target_amount' => 'required|numeric|min:0',
            'period' => 'required|in:monthly,quarterly,annual',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $target->update($validated);

        return redirect()->back()->with('success', 'Sales target updated successfully.');
    }

    public function destroyTarget(SalesTarget $target)
    {
        $target->delete();

        return redirect()->back()->with('success', 'Sales target deleted successfully.');
    }

    public function reports(Request $request)
    {
        $groupBy = $request->get('group_by', 'seller'); // seller, department, product
        $period = $request->get('period', 'month');
        $dateRange = $this->getDateRange($period, $request);
        $dateFrom = $dateRange[0] ? $dateRange[0]->format('Y-m-d') : null;
        $dateTo = $dateRange[1] ? $dateRange[1]->format('Y-m-d') : null;

        $results = [];

        if ($groupBy === 'seller') {
            $results = User::where('role', 'saler')
                ->withSum(['designTasks' => function($q) use ($dateRange) {
                    if ($dateRange[0]) $q->whereBetween('created_at', [$dateRange[0], $dateRange[1]]);
                    $q->where('status', '!=', DesignTask::STATUS_CANCELLED);
                }], 'price')
                ->get();
        } elseif ($groupBy === 'department') {
            $results = Department::withSum(['designTasks' => function($q) use ($dateRange) {
                if ($dateRange[0]) $q->whereBetween('created_at', [$dateRange[0], $dateRange[1]]);
                $q->where('status', '!=', DesignTask::STATUS_CANCELLED);
            }], 'price')->get();
        } elseif ($groupBy === 'product') {
            $results = DB::table('design_tasks')
                ->join('design_task_types', 'design_tasks.design_task_type_id', '=', 'design_task_types.id')
                ->where('design_tasks.status', '!=', DesignTask::STATUS_CANCELLED);
                
            if ($dateRange[0]) {
                $results->whereBetween('design_tasks.created_at', [$dateRange[0], $dateRange[1]]);
            }

            $results = $results->select('design_task_types.name', DB::raw('COUNT(*) as total_qty'), DB::raw('SUM(design_tasks.price) as total_revenue'))
                ->groupBy('design_task_types.id', 'design_task_types.name')
                ->orderByDesc('total_revenue')
                ->limit(20)
                ->get();
        }

        return view('admin.sales-dept.reports', compact('results', 'groupBy', 'period', 'dateFrom', 'dateTo'));
    }

    private function getDateRange($period, $request = null)
    {
        return match($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            '6_months' => [now()->subMonths(6), now()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            '2_years' => [now()->subYears(2), now()],
            'custom' => [
                $request && $request->get('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : null,
                $request && $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : null
            ],
            'all' => [null, null],
            default => [now()->startOfMonth(), now()->endOfMonth()]
        };
    }
}
