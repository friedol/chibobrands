<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DesignTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperatorPerformanceController extends Controller
{
    /**
     * Display Operator Performance Report dashboard with database-level aggregations for maximum speed.
     */
    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $operatorId = $request->get('operator_id');
        $departmentId = $request->get('department_id');
        $status = $request->get('status', 'all');

        $completedStatuses = [
            DesignTask::STATUS_COMPLETED,
            DesignTask::STATUS_CONFIRMED,
            DesignTask::STATUS_PRINTED,
            DesignTask::STATUS_SUPER_COMPLETED,
            DesignTask::STATUS_DELIVERED
        ];

        // Operators list for filter (lightweight query) — actual operators only.
        $operators = User::select(['id', 'name', 'role'])
            ->where('role', 'operator')
            ->viewableStaff()
            ->orderBy('name')
            ->get();

        $departments = Department::select(['id', 'name'])->orderBy('name')->get();

        // 1. FAST SQL SUMMARY AGGREGATION
        $baseQuery = DB::table('design_tasks')
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($operatorId) {
            $baseQuery->where('operator_id', $operatorId);
        } else {
            $baseQuery->whereNotNull('operator_id');
        }

        if ($departmentId) {
            $baseQuery->where('department_id', $departmentId);
        }

        if ($status !== 'all') {
            if ($status === 'completed') {
                $baseQuery->whereIn('status', $completedStatuses);
            } elseif ($status === 'pending') {
                $baseQuery->whereIn('status', [
                    DesignTask::STATUS_PENDING,
                    DesignTask::STATUS_IN_PROGRESS,
                    DesignTask::STATUS_IN_REVIEW,
                    DesignTask::STATUS_PRINTING
                ]);
            } elseif ($status === 'overdue') {
                $baseQuery->where('deadline', '<', now())->whereNotIn('status', $completedStatuses)->where('status', '!=', 'cancelled');
            } else {
                $baseQuery->where('status', $status);
            }
        }

        $totalAssigned = (clone $baseQuery)->distinct('customer_id')->count('customer_id');
        $totalCompleted = (clone $baseQuery)->whereIn('status', $completedStatuses)->distinct('customer_id')->count('customer_id');
        $totalPending = (clone $baseQuery)->whereNotIn('status', $completedStatuses)->where('status', '!=', 'cancelled')->distinct('customer_id')->count('customer_id');
        $totalOverdue = (clone $baseQuery)->where('deadline', '<', now())->whereNotIn('status', $completedStatuses)->where('status', '!=', 'cancelled')->distinct('customer_id')->count('customer_id');
        $totalRevenue = (clone $baseQuery)->sum('price');
        $printingCount = (clone $baseQuery)->where('status', 'printing')->count();
        $printedCount = (clone $baseQuery)->where('status', 'printed')->count();

        $completionRate = $totalAssigned > 0 ? round(($totalCompleted / $totalAssigned) * 100, 1) : 0;
        $pendingRate = $totalAssigned > 0 ? round(($totalPending / $totalAssigned) * 100, 1) : 0;

        $summary = [
            'total_assigned'  => $totalAssigned,
            'total_completed' => $totalCompleted,
            'total_pending'   => $totalPending,
            'total_overdue'   => $totalOverdue,
            'completion_rate' => $completionRate,
            'pending_rate'    => $pendingRate,
            'total_revenue'   => $totalRevenue,
            'printing'        => $printingCount,
            'printed'         => $printedCount,
        ];

        // 2. FAST OPERATOR GROUP BY AGGREGATION (count unique customers, not tasks)
        $operatorRawStats = DB::table('design_tasks')
            ->select(
                'operator_id',
                DB::raw('COUNT(DISTINCT customer_id) as total_assigned'),
                DB::raw("COUNT(DISTINCT CASE WHEN status IN ('completed','confirmed','printed','super_completed','delivered') THEN customer_id END) as total_completed"),
                DB::raw("COUNT(DISTINCT CASE WHEN status NOT IN ('completed','confirmed','printed','super_completed','delivered','cancelled') THEN customer_id END) as total_pending"),
                DB::raw("COUNT(DISTINCT CASE WHEN deadline IS NOT NULL AND deadline < NOW() AND status NOT IN ('completed','confirmed','printed','super_completed','delivered','cancelled') THEN customer_id END) as total_overdue"),
                DB::raw('SUM(price) as total_revenue')
            )
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->when($operatorId, fn($q) => $q->where('operator_id', $operatorId), fn($q) => $q->whereNotNull('operator_id'))
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->groupBy('operator_id')
            ->get();

        // Load operators mapped efficiently
        $operatorUserIds = $operatorRawStats->pluck('operator_id')->filter()->unique();
        $userMap = User::select(['id', 'name', 'role'])->whereIn('id', $operatorUserIds)->get()->keyBy('id');

        $tasksByOperator = $operatorRawStats->map(function($stat) use ($userMap) {
            $assigned = $stat->total_assigned;
            $completed = $stat->total_completed;
            $efficiency = $assigned > 0 ? round(($completed / $assigned) * 100, 1) : 0;
            $opUser = $userMap->get($stat->operator_id) ?? (object)['name' => 'Unassigned', 'role' => 'Staff'];

            return [
                'operator'   => $opUser,
                'assigned'   => $assigned,   // unique customers served
                'completed'  => $completed,
                'pending'    => $stat->total_pending,
                'overdue'    => $stat->total_overdue,
                'revenue'    => $stat->total_revenue ?? 0,
                'efficiency' => $efficiency,
            ];
        })->sortByDesc('completed')->values();

        // 3. FAST SQL DAILY TREND GROUP BY
        $rawTrend = DB::table('design_tasks')
            ->select(
                DB::raw('DATE(created_at) as task_date'),
                DB::raw('COUNT(*) as total_assigned'),
                DB::raw("SUM(CASE WHEN status IN ('completed','confirmed','printed','super_completed','delivered') THEN 1 ELSE 0 END) as total_completed")
            )
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->when($operatorId, fn($q) => $q->where('operator_id', $operatorId), fn($q) => $q->whereNotNull('operator_id'))
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get()
            ->keyBy('task_date');

        $tasksByDate = [];
        $startDate = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $dayData = $rawTrend->get($dateStr);

            $tasksByDate[] = [
                'date'      => $dateStr,
                'assigned'  => $dayData->total_assigned ?? 0,
                'completed' => $dayData->total_completed ?? 0,
            ];
        }

        // 4. LIGHTWEIGHT PAGINATED DETAILED TASK LIST
        $taskQuery = DesignTask::select([
                'id', 'task_code', 'title', 'customer_id', 'operator_id',
                'department_id', 'created_at', 'deadline', 'completed_at', 'status', 'price'
            ])
            ->with([
                'customer:id,name,company_name',
                'operator:id,name,role',
                'department:id,name'
            ])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($operatorId) {
            $taskQuery->where('operator_id', $operatorId);
        } else {
            $taskQuery->whereNotNull('operator_id');
        }

        if ($departmentId) {
            $taskQuery->where('department_id', $departmentId);
        }

        if ($status !== 'all') {
            if ($status === 'completed') {
                $taskQuery->whereIn('status', $completedStatuses);
            } elseif ($status === 'pending') {
                $taskQuery->whereIn('status', [
                    DesignTask::STATUS_PENDING,
                    DesignTask::STATUS_IN_PROGRESS,
                    DesignTask::STATUS_IN_REVIEW,
                    DesignTask::STATUS_PRINTING
                ]);
            } elseif ($status === 'overdue') {
                $taskQuery->where('deadline', '<', now())->whereNotIn('status', $completedStatuses)->where('status', '!=', 'cancelled');
            } else {
                $taskQuery->where('status', $status);
            }
        }

        $tasks = $taskQuery->latest()->paginate(25)->withQueryString();

        return view('admin.reports.operators', compact(
            'tasks',
            'summary',
            'tasksByOperator',
            'dateFrom',
            'dateTo',
            'operators',
            'departments',
            'operatorId',
            'departmentId',
            'status',
            'tasksByDate'
        ));
    }

    /**
     * Display smart print page for operator performance report (no graphs, no stars).
     */
    public function print(Request $request)
    {
        return view('admin.reports.operators_print', $this->buildPrintData($request));
    }

    /**
     * Download the same operator performance report as a PDF.
     */
    public function exportPdf(Request $request)
    {
        $data = $this->buildPrintData($request);
        $data['isPdf'] = true;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.operators_print', $data)
            ->setPaper('a4', 'portrait');

        $filename = "operator-performance-report-{$data['dateFrom']}-to-{$data['dateTo']}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Shared data builder for both the browser print-preview and the PDF export.
     */
    private function buildPrintData(Request $request): array
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $operatorId = $request->get('operator_id');
        $departmentId = $request->get('department_id');
        $status = $request->get('status', 'all');

        $completedStatuses = [
            DesignTask::STATUS_COMPLETED,
            DesignTask::STATUS_CONFIRMED,
            DesignTask::STATUS_PRINTED,
            DesignTask::STATUS_SUPER_COMPLETED,
            DesignTask::STATUS_DELIVERED
        ];

        // Summary Aggregations
        $baseQuery = DB::table('design_tasks')
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($operatorId) {
            $baseQuery->where('operator_id', $operatorId);
        } else {
            $baseQuery->whereNotNull('operator_id');
        }

        if ($departmentId) {
            $baseQuery->where('department_id', $departmentId);
        }

        if ($status !== 'all') {
            if ($status === 'completed') {
                $baseQuery->whereIn('status', $completedStatuses);
            } elseif ($status === 'pending') {
                $baseQuery->whereIn('status', [
                    DesignTask::STATUS_PENDING,
                    DesignTask::STATUS_IN_PROGRESS,
                    DesignTask::STATUS_IN_REVIEW,
                    DesignTask::STATUS_PRINTING
                ]);
            } elseif ($status === 'overdue') {
                $baseQuery->where('deadline', '<', now())->whereNotIn('status', $completedStatuses)->where('status', '!=', 'cancelled');
            } else {
                $baseQuery->where('status', $status);
            }
        }

        $totalAssigned = (clone $baseQuery)->distinct('customer_id')->count('customer_id');
        $totalCompleted = (clone $baseQuery)->whereIn('status', $completedStatuses)->distinct('customer_id')->count('customer_id');
        $totalPending = (clone $baseQuery)->whereNotIn('status', $completedStatuses)->where('status', '!=', 'cancelled')->distinct('customer_id')->count('customer_id');
        $totalOverdue = (clone $baseQuery)->where('deadline', '<', now())->whereNotIn('status', $completedStatuses)->where('status', '!=', 'cancelled')->distinct('customer_id')->count('customer_id');
        $totalRevenue = (clone $baseQuery)->sum('price');

        $summary = [
            'total_assigned'  => $totalAssigned,
            'total_completed' => $totalCompleted,
            'total_pending'   => $totalPending,
            'total_overdue'   => $totalOverdue,
            'completion_rate' => $totalAssigned > 0 ? round(($totalCompleted / $totalAssigned) * 100, 1) : 0,
            'pending_rate'    => $totalAssigned > 0 ? round(($totalPending / $totalAssigned) * 100, 1) : 0,
            'total_revenue'   => $totalRevenue,
        ];

        // Operator Stats (count unique customers, not raw task rows — a customer
        // bringing several item types, e.g. 3 bag styles, in one visit is one job).
        $operatorRawStats = DB::table('design_tasks')
            ->select(
                'operator_id',
                DB::raw('COUNT(DISTINCT customer_id) as total_assigned'),
                DB::raw("COUNT(DISTINCT CASE WHEN status IN ('completed','confirmed','printed','super_completed','delivered') THEN customer_id END) as total_completed"),
                DB::raw("COUNT(DISTINCT CASE WHEN status NOT IN ('completed','confirmed','printed','super_completed','delivered','cancelled') THEN customer_id END) as total_pending"),
                DB::raw("COUNT(DISTINCT CASE WHEN deadline IS NOT NULL AND deadline < NOW() AND status NOT IN ('completed','confirmed','printed','super_completed','delivered','cancelled') THEN customer_id END) as total_overdue"),
                DB::raw('SUM(price) as total_revenue')
            )
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->when($operatorId, fn($q) => $q->where('operator_id', $operatorId), fn($q) => $q->whereNotNull('operator_id'))
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->groupBy('operator_id')
            ->get();

        $operatorUserIds = $operatorRawStats->pluck('operator_id')->filter()->unique();
        $userMap = User::select(['id', 'name', 'role'])->whereIn('id', $operatorUserIds)->get()->keyBy('id');

        $tasksByOperator = $operatorRawStats->map(function($stat) use ($userMap) {
            $assigned = $stat->total_assigned;
            $completed = $stat->total_completed;
            $efficiency = $assigned > 0 ? round(($completed / $assigned) * 100, 1) : 0;
            $opUser = $userMap->get($stat->operator_id) ?? (object)['name' => 'Unassigned', 'role' => 'Staff'];

            return [
                'operator'   => $opUser,
                'assigned'   => $assigned,
                'completed'  => $completed,
                'pending'    => $stat->total_pending,
                'overdue'    => $stat->total_overdue,
                'revenue'    => $stat->total_revenue ?? 0,
                'efficiency' => $efficiency,
            ];
        })->sortByDesc('completed')->values();

        // Tasks list for print
        $taskQuery = DesignTask::select([
                'id', 'task_code', 'title', 'customer_id', 'operator_id',
                'department_id', 'created_at', 'deadline', 'completed_at', 'status', 'price'
            ])
            ->with([
                'customer:id,name,company_name',
                'operator:id,name,role',
                'department:id,name'
            ])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($operatorId) {
            $taskQuery->where('operator_id', $operatorId);
        } else {
            $taskQuery->whereNotNull('operator_id');
        }

        if ($departmentId) {
            $taskQuery->where('department_id', $departmentId);
        }

        if ($status !== 'all') {
            if ($status === 'completed') {
                $taskQuery->whereIn('status', $completedStatuses);
            } elseif ($status === 'pending') {
                $taskQuery->whereIn('status', [
                    DesignTask::STATUS_PENDING,
                    DesignTask::STATUS_IN_PROGRESS,
                    DesignTask::STATUS_IN_REVIEW,
                    DesignTask::STATUS_PRINTING
                ]);
            } elseif ($status === 'overdue') {
                $taskQuery->where('deadline', '<', now())->whereNotIn('status', $completedStatuses)->where('status', '!=', 'cancelled');
            } else {
                $taskQuery->where('status', $status);
            }
        }

        $tasks = $taskQuery->latest()->get();
        $headerSummary = [
            'total_assigned'  => $summary['total_assigned'],
            'total_completed' => $summary['total_completed'],
            'completion_rate' => $summary['completion_rate'],
            'total_overdue'   => $summary['total_overdue'],
        ];

        return compact(
            'tasks',
            'summary',
            'headerSummary',
            'tasksByOperator',
            'dateFrom',
            'dateTo'
        );
    }

    /**
     * Export operator performance report as multi-sheet XLSX
     * (Summary / Operator Breakdown / Task Details — matching the print view).
     */
    public function exportCsv(Request $request)
    {
        $data     = $this->buildPrintData($request);
        $s        = $data['summary'];
        $dateFrom = $data['dateFrom'];
        $dateTo   = $data['dateTo'];

        // Sheet 1 — Summary
        $summarySheet = [
            'title'    => 'Summary',
            'headings' => ['Metric', 'Value'],
            'rows'     => [
                ['Report Period', "{$dateFrom} to {$dateTo}"],
                ['Generated',     now()->format('Y-m-d H:i')],
                ['Total Assigned',  $s['total_assigned']],
                ['Completed',       $s['total_completed']],
                ['Pending',         $s['total_pending']],
                ['Overdue',         $s['total_overdue']],
                ['Completion Rate', $s['completion_rate'] . '%'],
                ['Total Revenue (TZS)', number_format($s['total_revenue'], 2)],
            ],
        ];

        // Sheet 2 — Operator Breakdown
        $breakdownRows = [];
        foreach ($data['tasksByOperator'] as $op) {
            $breakdownRows[] = [
                $op['operator']->name ?? 'N/A',
                $op['assigned'],
                $op['completed'],
                $op['pending'],
                $op['overdue'],
                number_format($op['revenue'], 2),
                $op['efficiency'] . '%',
            ];
        }
        $breakdownSheet = [
            'title'    => 'Operator Breakdown',
            'headings' => ['Operator', 'Assigned', 'Completed', 'Pending', 'Overdue', 'Revenue (TZS)', 'Efficiency %'],
            'rows'     => $breakdownRows,
        ];

        // Sheet 3 — Task Details
        $taskRows = [];
        foreach ($data['tasks'] as $task) {
            $taskRows[] = [
                $task->task_code ?? "TASK-{$task->id}",
                $task->title,
                $task->customer->name ?? 'N/A',
                $task->operator->name ?? 'Unassigned',
                $task->department->name ?? 'General',
                $task->created_at->format('Y-m-d H:i'),
                $task->deadline ? $task->deadline->format('Y-m-d H:i') : 'N/A',
                $task->completed_at ? $task->completed_at->format('Y-m-d H:i') : 'N/A',
                ucfirst(str_replace('_', ' ', $task->status)),
                number_format($task->price ?? 0, 2),
            ];
        }
        $taskSheet = [
            'title'    => 'Task Details',
            'headings' => ['Task Code', 'Task Name', 'Customer', 'Operator', 'Department', 'Assigned Date', 'Deadline', 'Completion Date', 'Status', 'Amount (TZS)'],
            'rows'     => $taskRows,
        ];

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\MultiSheetReportExport([$summarySheet, $breakdownSheet, $taskSheet]),
            "operator-performance-{$dateFrom}-to-{$dateTo}.xlsx"
        );
    }
}
