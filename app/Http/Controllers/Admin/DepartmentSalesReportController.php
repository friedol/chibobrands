<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DesignTask;
use App\Models\SalesTarget;
use App\Support\Concerns\ResolvesSalesTargets;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DepartmentSalesReportController extends Controller
{
    use ResolvesSalesTargets;

    private function resolveDateRange(string $period, ?string $dateFrom, ?string $dateTo): array
    {
        $now = Carbon::now();

        if ($period === 'custom' && $dateFrom && $dateTo) {
            $start = Carbon::parse($dateFrom)->startOfDay();
            $end   = Carbon::parse($dateTo)->endOfDay();
            $label = Carbon::parse($dateFrom)->format('M d, Y') . ' – ' . Carbon::parse($dateTo)->format('M d, Y');
            return [$start, $end, $label];
        }

        switch ($period) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Today'];
            case 'yesterday':
                $y = $now->copy()->subDay();
                return [$y->copy()->startOfDay(), $y->copy()->endOfDay(), 'Yesterday'];
            case 'week':
                return [$now->copy()->startOfWeek(), $now->copy()->endOfWeek(), 'This Week'];
            case 'year':
                return [$now->copy()->startOfYear(), $now->copy()->endOfYear(), 'This Year'];
            case 'month':
            default:
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'This Month'];
        }
    }

    /**
     * Department-level target only — never mixed with individual salesperson targets.
     * See ResolvesSalesTargets for how the amount is matched/scaled to the report range.
     */
    private function calculateDepartmentTarget(int $departmentId, Carbon $startDate, Carbon $endDate, ?string $period = null): array
    {
        $scopeQuery = SalesTarget::where('department_id', $departmentId)->whereNull('seller_id');

        return $this->resolveSalesTarget($scopeQuery, $startDate, $endDate, $period);
    }

    private function buildReport(Carbon $startDate, Carbon $endDate, ?int $departmentId = null, ?string $period = null): array
    {
        $departments        = $departmentId
            ? Department::where('id', $departmentId)->get()
            : Department::all();
        $reportData         = [];
        $overallTotalSales  = 0;
        $overallTotalTarget = 0;

        foreach ($departments as $department) {
            $tasks = DesignTask::with('customer')
                ->where('department_id', $department->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', '!=', DesignTask::STATUS_CANCELLED)
                ->orderBy('created_at', 'desc')
                ->get();

            $groupedTasks = $tasks->groupBy(function ($task) {
                $customerName = $task->customer ? strtolower(trim($task->customer->name)) : 'unknown';
                $title        = strtolower(trim($task->title));
                $description  = strtolower(trim($task->description));
                return $customerName . '|' . $title . '|' . $description;
            })->map(function ($group) {
                $first = $group->first();
                return [
                    'customer_name' => $first->customer ? $first->customer->name : 'Unknown',
                    'title'         => $first->title,
                    'description'   => $first->description,
                    'qty'           => $group->sum('qty'),
                    // Revenue formula matches finance/sales reporting: VAT(if required) + delivery - discount
                    'price'         => $group->sum(function ($task) {
                        $basePrice = $task->requires_receipt ? ((float) $task->price * 1.18) : (float) $task->price;
                        $deliveryCost = (float) ($task->delivery_cost ?? 0);
                        $deliveryDiscount = (float) ($task->delivery_discount ?? 0);
                        return $basePrice + $deliveryCost - $deliveryDiscount;
                    }),
                ];
            })->values();

            $totalSales         = $groupedTasks->sum('price');
            $overallTotalSales += $totalSales;

            // Department-level target only — never mixed with individual salesperson targets.
            [$activeTargetAmount, $targetNote] = $this->calculateDepartmentTarget($department->id, $startDate, $endDate, $period);

            $overallTotalTarget += $activeTargetAmount;

            $percentage = $activeTargetAmount > 0
                ? round(($totalSales / $activeTargetAmount) * 100)
                : 0;

            $displayName = trim(str_replace(['CHIBO-', 'CHIBO –'], '', $department->name));
            if ($displayName === 'MAIN') $displayName = 'CHIBO MAIN';

            $reportData[] = [
                'department'    => $department,
                'display_name'  => $displayName,
                'target_amount' => $activeTargetAmount,
                'target_note'   => $targetNote,
                'tasks'         => $groupedTasks,
                'total_sales'   => $totalSales,
                'percentage'    => $percentage,
            ];
        }

        $overallPercentage = $overallTotalTarget > 0
            ? round(($overallTotalSales / $overallTotalTarget) * 100)
            : 0;

        return [$reportData, $overallTotalSales, $overallTotalTarget, $overallPercentage];
    }

    public function index(Request $request)
    {
        $period       = $request->get('period', 'month');
        $dateFrom     = $request->get('date_from');
        $dateTo       = $request->get('date_to');
        $departmentId = $request->filled('department_id') ? (int) $request->department_id : null;

        [$startDate, $endDate, $periodLabel] = $this->resolveDateRange($period, $dateFrom, $dateTo);
        [$reportData, $overallTotalSales, $overallTotalTarget, $overallPercentage] = $this->buildReport($startDate, $endDate, $departmentId, $period);

        $allDepartments = Department::orderBy('name')->get();

        return view('admin.reports.department-sales', compact(
            'reportData', 'startDate', 'endDate',
            'period', 'dateFrom', 'dateTo', 'periodLabel',
            'departmentId', 'allDepartments',
            'overallTotalSales', 'overallTotalTarget', 'overallPercentage'
        ));
    }

    public function print(Request $request)
    {
        $period       = $request->get('period', 'month');
        $dateFrom     = $request->get('date_from');
        $dateTo       = $request->get('date_to');
        $departmentId = $request->filled('department_id') ? (int) $request->department_id : null;

        // Legacy month/year support
        if ($request->filled('month') && $request->filled('year')) {
            $startDate   = Carbon::createFromDate($request->year, $request->month, 1)->startOfDay();
            $endDate     = $startDate->copy()->endOfMonth();
            $periodLabel = $startDate->format('F Y');
        } else {
            [$startDate, $endDate, $periodLabel] = $this->resolveDateRange($period, $dateFrom, $dateTo);
        }

        [$reportData, $overallTotalSales, $overallTotalTarget, $overallPercentage] = $this->buildReport($startDate, $endDate, $departmentId, $period);

        // Single department selected → "{Department} Sales Report"; otherwise every branch is in the table → "All Branches Sales Report".
        $reportTitle = ($departmentId && count($reportData) === 1)
            ? $reportData[0]['display_name'] . ' Sales Report'
            : 'All Branches Sales Report';

        return view('admin.reports.print-department-sales', compact(
            'reportData', 'startDate', 'endDate', 'periodLabel', 'reportTitle',
            'overallTotalSales', 'overallTotalTarget', 'overallPercentage'
        ));
    }

    public function pdf(Request $request)
    {
        $period       = $request->get('period', 'month');
        $dateFromInput = $request->get('date_from');
        $dateToInput   = $request->get('date_to');
        $departmentId = $request->filled('department_id') ? (int) $request->department_id : null;

        [$startDate, $endDate, $periodLabel] = $this->resolveDateRange($period, $dateFromInput, $dateToInput);
        [$reportData, $overallTotalSales, $overallTotalTarget, $overallPercentage] = $this->buildReport($startDate, $endDate, $departmentId, $period);

        // Single department selected → "{Department} Sales Report"; otherwise every branch is in the table → "All Branches Sales Report".
        $reportTitle = ($departmentId && count($reportData) === 1)
            ? $reportData[0]['display_name'] . ' Sales Report'
            : 'All Branches Sales Report';

        $title    = $reportTitle;
        $dateFrom = $startDate;
        $dateTo   = $endDate;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.exports.department-sales', compact(
            'reportData', 'startDate', 'endDate', 'periodLabel', 'title', 'dateFrom', 'dateTo',
            'overallTotalSales', 'overallTotalTarget', 'overallPercentage'
        ))->setPaper('a4', 'portrait');

        $filename = 'department-sales-report-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function excel(Request $request)
    {
        $period       = $request->get('period', 'month');
        $dateFrom     = $request->get('date_from');
        $dateTo       = $request->get('date_to');
        $departmentId = $request->filled('department_id') ? (int) $request->department_id : null;

        [$startDate, $endDate] = $this->resolveDateRange($period, $dateFrom, $dateTo);
        [$reportData, $overallTotalSales, , $overallPercentage] = $this->buildReport($startDate, $endDate, $departmentId, $period);

        $rows = [];
        foreach ($reportData as $data) {
            foreach ($data['tasks'] as $index => $task) {
                $rows[] = [
                    $data['display_name'],
                    $index + 1,
                    $task['customer_name'],
                    $task['title'],
                    $task['description'],
                    $task['qty'],
                    $task['price'],
                ];
            }
            $rows[] = [$data['display_name'], '', '', '', 'DEPARTMENT TOTAL', '', $data['total_sales']];
        }
        $rows[] = ['', '', '', '', 'GRAND TOTAL', '', $overallTotalSales];

        $headings = ['Department', 'No', 'Customer', 'Task Title', 'Description', 'Qty', 'Revenue (TZS)'];

        $filename = 'department-sales-report-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SimpleArrayExport($rows, $headings, 'Department Sales'),
            $filename
        );
    }
}
