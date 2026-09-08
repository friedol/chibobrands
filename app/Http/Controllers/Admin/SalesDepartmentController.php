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
use App\Support\Concerns\ResolvesSalesTargets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class SalesDepartmentController extends Controller
{
    use ResolvesSalesTargets;


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

            // Sales vs Target — matched/scaled to the selected date range (see ResolvesSalesTargets).
            if ($dateRange[0] && $dateRange[1]) {
                [$activeTarget, $targetNote] = $this->resolveSalesTarget(
                    SalesTarget::where('seller_id', $seller->id),
                    $dateRange[0], $dateRange[1], $period
                );
            } else {
                $target = SalesTarget::where('seller_id', $seller->id)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->first();
                $activeTarget = $target ? (float) $target->target_amount : 0.0;
                $targetNote = null;
            }

            $seller->active_target = $activeTarget;
            $seller->target_note = $targetNote;
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

    /**
     * Shared filtered query used by targets(), printTargets(), targetsPdf() and
     * targetsExcel() so every report format is built from the exact same data.
     */
    private function buildTargetsQuery(Request $request)
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

        // Target type filter (department-only vs saler-specific)
        if ($request->filled('target_type') && $request->target_type !== 'all') {
            if ($request->target_type === 'department') {
                $query->whereNull('seller_id');
            } elseif ($request->target_type === 'saler') {
                $query->whereNotNull('seller_id');
            }
        }

        return $query;
    }

    public function targets(Request $request)
    {
        $this->syncRecurringTargets();

        $sellers = User::where('role', 'saler')->orderBy('name')->get();
        $departments = Department::all();
        $targets = $this->buildTargetsQuery($request)->latest()->paginate(25)->withQueryString();

        // Summary stats
        $statsBase = SalesTarget::query();
        $targetStats = [
            'total'      => (clone $statsBase)->count(),
            'active'     => (clone $statsBase)->where('start_date', '<=', now())->where('end_date', '>=', now())->count(),
            'department' => (clone $statsBase)->whereNull('seller_id')->count(),
            'saler'      => (clone $statsBase)->whereNotNull('seller_id')->count(),
        ];

        return view('admin.sales-dept.targets', compact('sellers', 'departments', 'targets', 'targetStats'));
    }

    public function printTargets(Request $request)
    {
        $this->syncRecurringTargets();

        $targets = $this->buildTargetsQuery($request)->orderBy('department_id')->orderBy('seller_id')->get();
        $targetType = $request->get('target_type', 'all');

        return view('admin.sales-dept.print-targets', compact('targets', 'targetType'));
    }

    /**
     * PDF export of the sales targets report — same filtered data as targets()/printTargets().
     */
    public function targetsPdf(Request $request)
    {
        $this->syncRecurringTargets();

        $targets    = $this->buildTargetsQuery($request)->orderBy('department_id')->orderBy('seller_id')->get();
        $targetType = $request->get('target_type', 'all');

        $title    = 'Sales Targets Report';
        $dateFrom = $request->date_from;
        $dateTo   = $request->date_to;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.exports.sales-targets', compact('targets', 'targetType', 'title', 'dateFrom', 'dateTo'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('sales-targets-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Excel export of the sales targets report — same filtered data as targets()/printTargets().
     */
    public function targetsExcel(Request $request)
    {
        $this->syncRecurringTargets();

        $targets = $this->buildTargetsQuery($request)->orderBy('department_id')->orderBy('seller_id')->get();

        $headings = ['Type', 'Department', 'Salesperson', 'Period', 'Start Date', 'End Date', 'Status', 'Target Amount (TZS)'];

        $now = now();
        $rows = $targets->map(function ($t) use ($now) {
            $status = ($t->start_date <= $now && $t->end_date >= $now)
                ? 'Active'
                : ($t->start_date > $now ? 'Upcoming' : 'Expired');

            return [
                $t->seller_id ? 'Saler' : 'Department',
                $t->department->name ?? 'All Departments',
                $t->seller->name ?? '-',
                ucfirst($t->period),
                $t->start_date->format('Y-m-d'),
                $t->end_date->format('Y-m-d'),
                $status,
                (float) $t->target_amount,
            ];
        })->toArray();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SimpleArrayExport($rows, $headings, 'Sales Targets'),
            'sales-targets-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function storeTarget(Request $request)
    {
        $validated = $request->validate([
            'seller_id' => 'nullable|required_without:department_id|exists:users,id',
            'department_id' => 'nullable|required_without:seller_id|exists:departments,id',
            'target_amount' => 'required|numeric|min:0',
            'period' => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'recurrence_enabled' => 'nullable|boolean',
        ]);

        [$cycleStart, $cycleEnd] = $this->buildPeriodDateRange($validated['period']);

        $validated['recurrence_enabled'] = $request->boolean('recurrence_enabled');
        $validated['recurrence_period'] = $validated['recurrence_enabled']
            ? $this->normalizeRecurrencePeriod($validated['period'])
            : null;

        if ($validated['recurrence_enabled'] && !$validated['recurrence_period']) {
            throw ValidationException::withMessages([
                'period' => 'Automatic repeat currently supports daily, weekly, monthly, and yearly target periods.',
            ]);
        }

        $validated['start_date'] = $cycleStart->toDateString();
        $validated['end_date'] = $cycleEnd->toDateString();
        $validated['recurrence_source_id'] = null;

        SalesTarget::create($validated);

        return redirect()->back()->with('success', 'Sales target assigned successfully.');
    }

    public function updateTarget(Request $request, SalesTarget $target)
    {
        $validated = $request->validate([
            'seller_id' => 'nullable|required_without:department_id|exists:users,id',
            'department_id' => 'nullable|required_without:seller_id|exists:departments,id',
            'target_amount' => 'required|numeric|min:0',
            'period' => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'recurrence_enabled' => 'nullable|boolean',
        ]);

        [$cycleStart, $cycleEnd] = $this->buildPeriodDateRange($validated['period']);

        $validated['recurrence_enabled'] = $request->boolean('recurrence_enabled');
        $validated['recurrence_period'] = $validated['recurrence_enabled']
            ? $this->normalizeRecurrencePeriod($validated['period'])
            : null;

        if ($validated['recurrence_enabled'] && !$validated['recurrence_period']) {
            throw ValidationException::withMessages([
                'period' => 'Automatic repeat currently supports daily, weekly, monthly, and yearly target periods.',
            ]);
        }

        $validated['start_date'] = $cycleStart->toDateString();
        $validated['end_date'] = $cycleEnd->toDateString();

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
                ->whereNull('design_tasks.deleted_at')
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

    public function reportsPrint(Request $request)
    {
        ['results' => $results, 'groupBy' => $groupBy, 'period' => $period, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->buildReportsData($request);
        return view('admin.sales-dept.reports-print', compact('results', 'groupBy', 'period', 'dateFrom', 'dateTo'));
    }

    public function reportsPdf(Request $request)
    {
        ['results' => $results, 'groupBy' => $groupBy, 'period' => $period, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->buildReportsData($request);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'admin.sales-dept.reports-print',
            compact('results', 'groupBy', 'period', 'dateFrom', 'dateTo')
        )->setPaper('a4', 'portrait');
        return $pdf->download('sales-dept-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function reportsExcel(Request $request)
    {
        ['results' => $results, 'groupBy' => $groupBy, 'period' => $period, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $this->buildReportsData($request);

        if ($groupBy === 'seller') {
            $headings = ['Salesperson', 'Total Sales (TZS)'];
            $rows = collect($results)->map(fn($r) => [$r->name, (float)($r->design_tasks_sum_price ?? 0)])->toArray();
        } elseif ($groupBy === 'department') {
            $headings = ['Department', 'Total Sales (TZS)'];
            $rows = collect($results)->map(fn($r) => [$r->name, (float)($r->design_tasks_sum_price ?? 0)])->toArray();
        } else {
            $headings = ['Product / Service', 'Qty', 'Total Revenue (TZS)'];
            $rows = collect($results)->map(fn($r) => [$r->name, $r->total_qty, (float)$r->total_revenue])->toArray();
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SimpleArrayExport($rows, $headings, 'Sales Dept Report'),
            'sales-dept-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    private function buildReportsData(Request $request): array
    {
        $groupBy   = $request->get('group_by', 'seller');
        $period    = $request->get('period', 'month');
        $dateRange = $this->getDateRange($period, $request);
        $dateFrom  = $dateRange[0] ? $dateRange[0]->format('Y-m-d') : null;
        $dateTo    = $dateRange[1] ? $dateRange[1]->format('Y-m-d') : null;
        $results   = [];

        if ($groupBy === 'seller') {
            $results = User::where('role', 'saler')
                ->withSum(['designTasks' => function ($q) use ($dateRange) {
                    if ($dateRange[0]) $q->whereBetween('created_at', [$dateRange[0], $dateRange[1]]);
                    $q->where('status', '!=', DesignTask::STATUS_CANCELLED);
                }], 'price')->get();
        } elseif ($groupBy === 'department') {
            $results = Department::withSum(['designTasks' => function ($q) use ($dateRange) {
                if ($dateRange[0]) $q->whereBetween('created_at', [$dateRange[0], $dateRange[1]]);
                $q->where('status', '!=', DesignTask::STATUS_CANCELLED);
            }], 'price')->get();
        } elseif ($groupBy === 'product') {
            $q = DB::table('design_tasks')
                ->join('design_task_types', 'design_tasks.design_task_type_id', '=', 'design_task_types.id')
                ->whereNull('design_tasks.deleted_at')
                ->where('design_tasks.status', '!=', DesignTask::STATUS_CANCELLED);
            if ($dateRange[0]) $q->whereBetween('design_tasks.created_at', [$dateRange[0], $dateRange[1]]);
            $results = $q->select('design_task_types.name', DB::raw('COUNT(*) as total_qty'), DB::raw('SUM(design_tasks.price) as total_revenue'))
                ->groupBy('design_task_types.id', 'design_task_types.name')
                ->orderByDesc('total_revenue')
                ->limit(20)->get();
        }

        return compact('results', 'groupBy', 'period', 'dateFrom', 'dateTo');
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

    private function syncRecurringTargets(): void
    {
        // Skip recurring sync until recurrence columns exist (safe for pre-migration state).
        if (!Schema::hasColumns('sales_targets', ['recurrence_enabled', 'recurrence_period', 'recurrence_source_id'])) {
            return;
        }

        $today = now()->startOfDay();

        $templates = SalesTarget::query()
            ->whereNull('recurrence_source_id')
            ->where('recurrence_enabled', true)
            ->whereIn('recurrence_period', ['daily', 'weekly', 'monthly', 'yearly'])
            ->get();

        foreach ($templates as $template) {
            $latest = SalesTarget::query()
                ->where('id', $template->id)
                ->orWhere('recurrence_source_id', $template->id)
                ->orderByDesc('end_date')
                ->first();

            if (!$latest) {
                continue;
            }

            $cycleStart = Carbon::parse($latest->start_date)->startOfDay();
            $cycleEnd = Carbon::parse($latest->end_date)->endOfDay();
            $guard = 0;

            while ($cycleEnd->lt($today) && $guard < 120) {
                [$cycleStart, $cycleEnd] = $this->advanceTargetCycle($cycleStart, $cycleEnd, $template->recurrence_period);

                $exists = SalesTarget::query()
                    ->where(function ($q) use ($template) {
                        $q->where('id', $template->id)
                          ->orWhere('recurrence_source_id', $template->id);
                    })
                    ->whereDate('start_date', $cycleStart->toDateString())
                    ->whereDate('end_date', $cycleEnd->toDateString())
                    ->exists();

                if (!$exists) {
                    SalesTarget::create([
                        'seller_id' => $template->seller_id,
                        'department_id' => $template->department_id,
                        'target_amount' => $template->target_amount,
                        'period' => $template->period,
                        'start_date' => $cycleStart->toDateString(),
                        'end_date' => $cycleEnd->toDateString(),
                        'recurrence_enabled' => false,
                        'recurrence_period' => null,
                        'recurrence_source_id' => $template->id,
                    ]);
                }

                $guard++;
            }
        }
    }

    private function advanceTargetCycle(Carbon $start, Carbon $end, string $frequency): array
    {
        return match ($frequency) {
            'daily' => [$start->copy()->addDay(), $end->copy()->addDay()],
            'weekly' => [$start->copy()->addWeek(), $end->copy()->addWeek()],
            'monthly' => [$start->copy()->addMonth(), $end->copy()->addMonth()],
            'yearly' => [$start->copy()->addYear(), $end->copy()->addYear()],
            default => [$start->copy(), $end->copy()],
        };
    }

    private function normalizeRecurrencePeriod(string $period): ?string
    {
        return in_array($period, ['daily', 'weekly', 'monthly', 'yearly'], true)
            ? $period
            : null;
    }

    private function buildPeriodDateRange(string $period): array
    {
        $now = now();

        return match ($period) {
            'daily' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'weekly' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'monthly' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'quarterly' => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()],
            'yearly' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }
}
