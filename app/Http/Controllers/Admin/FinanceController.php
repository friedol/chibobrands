<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Department;
use App\Models\Order;
use App\Models\DesignTask;
use App\Models\Customer;
use App\Models\User;
use App\Models\FinanceAuditTrail;
use App\Models\FinanceReconciliation;
use App\Services\FinanceHealthService;
use App\Services\ZohoComparisonService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class FinanceController extends Controller
{
    public function dashboard(Request $request)
    {
        $period = $request->get('period', 'today');
        
        // Harmonize date range logic
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

        $applyPeriod = function($query, $column = 'created_at') use ($dateRange) {
            if ($dateRange[0] && $dateRange[1]) {
                $query->whereBetween($column, [$dateRange[0], $dateRange[1]]);
            }
            return $query;
        };

        // Core stats
        // Apply date filtering only if dates are provided
        $totalInQuery = Payment::activeFinance();
        $totalOutQuery = Expense::query();
        $pendingPaymentsQuery = Payment::activeFinance()->where('is_debt', true);
        $totalLossesQuery = DesignTask::where('is_loss', true);
        $cancelledTasksQuery = DesignTask::where('status', DesignTask::STATUS_CANCELLED);

        if ($dateFrom && $dateTo) {
            $totalInQuery->whereBetween('date', [$dateFrom, $dateTo]);
            $totalOutQuery->whereBetween('date', [$dateFrom, $dateTo]);
            $pendingPaymentsQuery->whereBetween('date', [$dateFrom, $dateTo]);
            $totalLossesQuery->whereBetween('loss_recorded_at', [$dateFrom, $dateTo]);
            $cancelledTasksQuery->whereBetween('updated_at', [$dateFrom, $dateTo]);
        }

        $totalIn = $totalInQuery->sum('amount');
        $totalOut = $totalOutQuery->sum('amount');
        $balanceDue = $this->financeBalanceDue($dateFrom, $dateTo, null);
        $totalRevenue = $totalIn + $balanceDue;

        // Total Billed (value of work created in period)
        $totalBilled = $applyPeriod(DesignTask::activeFinance())->sum('price');

        // Net Profit = Total Sales (Billed) - Total Expenses
        // Formula corrected: we use the full sales value (not just collected) minus expenditure
        $netProfit = $totalBilled - $totalOut;

        // Total Debt Collected
        $debtCollected = Payment::whereBetween('date', [$dateFrom, $dateTo])->where('is_debt', 1)->sum('amount');

        // --- ENHANCEMENTS for System Workflow ---
        // Losses & Credit audit (Global visibility)
        $generalBalanceDue = $this->financeBalanceDue(null, null, null); 
        $totalCredits = abs(DesignTask::where('balance', '<', 0)->sum('balance')) + abs(Order::where('balance', '<', 0)->sum('balance'));
        
        $totalLossesQuery = DesignTask::where('is_loss', true);
        if ($dateFrom && $dateTo) {
            $totalLossesQuery->whereBetween('loss_recorded_at', [$dateFrom, $dateTo]);
        }
        $totalLosses = $totalLossesQuery->sum('loss_amount');
        
        $recentLossesQuery = DesignTask::with('customer')->where('is_loss', true);
        if ($dateFrom && $dateTo) {
            $recentLossesQuery->whereBetween('loss_recorded_at', [$dateFrom, $dateTo]);
        }
        $recentLosses = $recentLossesQuery->latest('loss_recorded_at')->limit(10)->get();
            
        // For the credit tables at the bottom
        $creditTasks = DesignTask::with('customer')->where('balance', '<', 0)->orderBy('balance')->limit(10)->get();
        $creditOrders = Order::with('user')->where('balance', '<', 0)->orderBy('balance')->limit(10)->get();
        // --- END ENHANCEMENTS ---

        // Department Performance
        $departmentReports = Department::all()->map(function($dept) use ($dateFrom, $dateTo) {
            $expQuery = $dept->expenses();
            $payQuery = Payment::activeFinance()->where('department_id', $dept->id);
            $taskQuery = DesignTask::activeFinance()->where('department_id', $dept->id);

            if ($dateFrom && $dateTo) {
                $expQuery->whereBetween('date', [$dateFrom, $dateTo]);
                $payQuery->whereBetween('date', [$dateFrom, $dateTo]);
                $taskQuery->whereBetween('created_at', [$dateFrom, $dateTo]);
            }

            $dept->total_expenses = $expQuery->sum('amount');
            $dept->total_collected = $payQuery->sum('amount');
            $deptTaskPending = $taskQuery->sum('balance');
            
            $dept->total_sales = $taskQuery->sum('price');
            
            $dept->total_revenue = $dept->total_collected + $deptTaskPending;
            $dept->profit = $dept->total_collected - $dept->total_expenses;
            return $dept;
        })->sortByDesc('total_revenue');

        // Chart Data: Cash flow trend
        $chartData = $this->getCashFlowChartData($dateFrom, $dateTo, $period);

        // Expense Breakdown by Category
        $expenseBreakdownQuery = Expense::query()
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total');
        
        if ($dateFrom && $dateTo) {
            $expenseBreakdownQuery->whereBetween('date', [$dateFrom, $dateTo]);
        }
        $expenseBreakdown = $expenseBreakdownQuery->get();

        // Recent Activity
        $recentPaymentsQuery = Payment::with(['customer', 'department']);
        if ($dateFrom && $dateTo) {
            $recentPaymentsQuery->whereBetween('date', [$dateFrom, $dateTo]);
        }
        $recentPayments = $recentPaymentsQuery->latest()->limit(10)->get();

        return view('admin.finance.dashboard', compact(
            'totalIn', 'totalOut', 'totalBilled', 'totalRevenue', 'balanceDue', 'netProfit', 'departmentReports', 'dateFrom', 'dateTo',
            'period', 'chartData', 'expenseBreakdown', 'recentPayments', 'debtCollected',
            'generalBalanceDue', 'totalCredits', 'totalLosses', 'recentLosses', 'creditTasks', 'creditOrders'
        ));
    }

    /**
     * Build payroll data (active employees). Shared by view, print, PDF and Excel exports
     * so all four surfaces show the exact same figures.
     */
    private function buildPayrollData(): \Illuminate\Support\Collection
    {
        return \App\Models\Employee::orderBy('full_name')->get();
    }

    /**
     * Display payroll for active employees.
     */
    public function payroll(Request $request)
    {
        $employees = $this->buildPayrollData();
        return view('admin.finance.payroll', compact('employees'));
    }

    /**
     * Print payroll (browser print view).
     */
    public function payrollPrint(Request $request)
    {
        $employees = $this->buildPayrollData();
        return view('admin.finance.print-payroll', compact('employees'));
    }

    /**
     * Download payroll as PDF.
     */
    public function payrollPdf(Request $request)
    {
        $employees = $this->buildPayrollData();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.finance.payroll-pdf', compact('employees'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('payroll-' . now()->format('Y-m') . '.pdf');
    }

    /**
     * Download payroll as Excel.
     */
    public function payrollExcel(Request $request)
    {
        $employees = $this->buildPayrollData();

        $rows = $employees->map(function ($emp) {
            return [
                $emp->full_name . ' (' . $emp->employee_code . ')',
                $emp->department ?? '—',
                (float) $emp->basic_salary,
                (float) $emp->allowances,
                (float) $emp->deductions,
                (float) $emp->net_salary,
                ucfirst($emp->status),
            ];
        })->toArray();

        $headings = ['Employee', 'Department', 'Basic Salary', 'Allowances', 'Deductions', 'Net Salary', 'Status'];

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SimpleArrayExport($rows, $headings, 'Payroll'),
            'payroll-' . now()->format('Y-m') . '.xlsx'
        );
    }

    /**
     * Download payslip as PDF.
     */
    public function payslipPdf($id)
    {
        $employee = \App\Models\Employee::findOrFail($id);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.finance.payslip-pdf', compact('employee'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('payslip-' . $employee->employee_code . '-' . now()->format('Y-m') . '.pdf');
    }

    /**
     * Display all pending payments for both Design Tasks and Orders.
     */
    /**
     * Build pending payments data (orders + design tasks with an outstanding balance).
     * Shared by view, print, PDF and Excel exports so all four surfaces show identical figures.
     */
    private function buildPendingPaymentsData(Request $request): array
    {
        $search   = $request->get('search');
        $salerId  = $request->get('saler_id');
        $period   = $request->get('period', 'all');

        // Harmonize date range logic
        $dateRange = match($period) {
            'today'     => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week'      => [now()->startOfWeek(), now()->endOfWeek()],
            'month'     => [now()->startOfMonth(), now()->endOfMonth()],
            '6_months'  => [now()->subMonths(6), now()],
            'year'      => [now()->startOfYear(), now()->endOfYear()],
            '2_years'   => [now()->subYears(2), now()],
            'custom'    => [
                $request->get('start_date') ? \Carbon\Carbon::parse($request->get('start_date'))->startOfDay() : null,
                $request->get('end_date')   ? \Carbon\Carbon::parse($request->get('end_date'))->endOfDay()   : null,
            ],
            'all' => [null, null],
            default => [null, null],
        };

        $dateFrom = $dateRange[0];
        $dateTo   = $dateRange[1];

        // Orders query
        $ordersQuery = Order::activeFinance()->with(['user', 'saler'])
            ->whereIn('payment_status', ['pending', 'partial']);

        if ($dateFrom && $dateTo) {
            $ordersQuery->whereBetween('created_at', [$dateFrom, $dateTo]);
        }

        if ($salerId) {
            $ordersQuery->where('saler_id', $salerId);
        }

        if ($search) {
            $ordersQuery->where(function($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('saler', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        $pendingOrders = $ordersQuery->orderBy('created_at', 'desc')->get();

        // Design Tasks query
        $tasksQuery = DesignTask::activeFinance()->with(['customer', 'receptionist', 'designer', 'saler'])
            ->where('balance', '>', 0);

        if ($dateFrom && $dateTo) {
            $tasksQuery->whereBetween('created_at', [$dateFrom, $dateTo]);
        }

        // Filter by specific salesperson
        if ($salerId) {
            $tasksQuery->where('saler_id', $salerId);
        }

        if ($search) {
            $tasksQuery->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('task_code', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('saler', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        $pendingTasks = $tasksQuery->orderBy('created_at', 'desc')->get();

        return [
            'pendingOrders' => $pendingOrders,
            'pendingTasks' => $pendingTasks,
            'search' => $search,
            'salerId' => $salerId,
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ];
    }

    public function pendingPayments(Request $request)
    {
        $data = $this->buildPendingPaymentsData($request);
        ['pendingOrders' => $pendingOrders, 'pendingTasks' => $pendingTasks, 'search' => $search,
         'salerId' => $salerId, 'period' => $period, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $data;

        $templates   = \App\Models\MessageTemplate::active()->get();
        $salers      = User::where('role', 'saler')
                   ->viewableStaff()->orderBy('name')->get();
        $dateFromStr = $dateFrom ? $dateFrom->format('Y-m-d') : null;
        $dateToStr   = $dateTo   ? $dateTo->format('Y-m-d')   : null;

        return view('admin.finance.pending-payments', compact(
            'pendingOrders', 'pendingTasks', 'templates',
            'search', 'salerId', 'salers', 'period', 'dateFromStr', 'dateToStr'
        ));
    }

    /**
     * Print all pending payments.
     */
    public function printPendingPayments(Request $request)
    {
        $data = $this->buildPendingPaymentsData($request);
        ['pendingOrders' => $pendingOrders, 'pendingTasks' => $pendingTasks, 'search' => $search,
         'salerId' => $salerId, 'period' => $period, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo] = $data;

        $salerName = $salerId
            ? User::where('role', 'saler')->where('id', $salerId)->value('name')
            : null;

        return view('admin.finance.print-pending', compact(
            'pendingOrders', 'pendingTasks', 'search', 'salerId', 'salerName', 'period', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Download all pending payments as PDF.
     */
    public function pendingPaymentsPdf(Request $request)
    {
        $data = $this->buildPendingPaymentsData($request);
        ['pendingOrders' => $pendingOrders, 'pendingTasks' => $pendingTasks, 'search' => $search,
         'salerId' => $salerId] = $data;

        $salerName = $salerId
            ? User::where('role', 'saler')->where('id', $salerId)->value('name')
            : null;
        $title = 'Outstanding Payments';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.exports.pending-payments', compact(
            'pendingOrders', 'pendingTasks', 'search', 'salerName', 'title'
        ))->setPaper('a4', 'portrait');
        return $pdf->download('pending-payments-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Download all pending payments as Excel.
     */
    public function pendingPaymentsExcel(Request $request)
    {
        $data = $this->buildPendingPaymentsData($request);
        $pendingOrders = $data['pendingOrders'];
        $pendingTasks = $data['pendingTasks'];

        $rows = [];

        foreach ($pendingTasks as $task) {
            $basePrice = $task->requires_receipt ? $task->price * 1.18 : $task->price;
            $deliveryCost = (float) ($task->delivery_cost ?? 0);
            $deliveryDiscount = (float) ($task->delivery_discount ?? 0);
            $taskTotal = $basePrice + $deliveryCost - $deliveryDiscount;

            $rows[] = [
                'Design Task',
                $task->task_code,
                $task->customer->name ?? 'Walk-in',
                $task->saler->name ?? '-',
                (float) $taskTotal,
                (float) $task->amount_paid,
                (float) $task->balance,
                $task->created_at->format('Y-m-d'),
            ];
        }

        foreach ($pendingOrders as $order) {
            $rows[] = [
                'Order',
                $order->order_code,
                $order->user->name ?? 'Walk-in',
                $order->saler->name ?? '-',
                (float) $order->total_amount,
                (float) $order->amount_paid,
                (float) $order->balance,
                $order->created_at->format('Y-m-d'),
            ];
        }

        $headings = ['Type', 'Code', 'Customer', 'Salesperson', 'Total', 'Paid', 'Balance', 'Created'];

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SimpleArrayExport($rows, $headings, 'Pending Payments'),
            'pending-payments-' . now()->format('Y-m-d') . '.xlsx'
        );
    }


    /**
     * Profit and Loss (P&L) - Accrual basis.
     *
     * We treat "accrual" here as:
     * - Revenue/discount recognized by `design_tasks.created_at` within the selected period
     * - Expenses recognized by `expenses.date` within the selected period
     *
     * Currency note: Values are shown in base currency (TZS).
     */
    public function profitLoss(Request $request): \Illuminate\View\View
    {
        $data = $this->buildProfitLossData($request);
        return view('admin.finance.profit-loss', $data);
    }

    /**
     * Print Profit & Loss (browser print view).
     */
    public function profitLossPrint(Request $request)
    {
        $data = $this->buildProfitLossData($request);
        return view('admin.finance.print-profit-loss', $data);
    }

    /**
     * Download Profit & Loss as PDF (used by Share PDF button).
     */
    public function profitLossPdf(Request $request)
    {
        $data = $this->buildProfitLossData($request);

        $dateFrom = $data['dateFrom'];
        $dateTo = $data['dateTo'];
        $period = $data['period'];
        $filename = 'profit-loss-' . ($dateFrom && $dateTo ? $dateFrom->format('Y-m-d') . '_to_' . $dateTo->format('Y-m-d') : ($period ?? 'report')) . '.pdf';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.finance.profit-loss-pdf', $data)
            ->setPaper('a4', 'portrait');
        return $pdf->download($filename);
    }

    /**
     * Download Profit & Loss as Excel.
     */
    public function profitLossExcel(Request $request)
    {
        $data = $this->buildProfitLossData($request);

        $rows = [
            ['Operating Income', $data['operatingIncome']],
            ['Sales', $data['sales']],
            ['Discount', $data['discount']],
            ['Total Operating Income', $data['operatingIncome']],
            ['Cost of Goods Sold', $data['cogs']],
            ['Gross Profit', $data['grossProfit']],
            ['Operating Expense', -abs($data['operatingExpense'])],
            ['Operating Profit', $data['operatingProfit']],
            ['Non Operating Income', 0],
            ['Non Operating Expense', 0],
            [$data['netProfitLoss'] < 0 ? 'Net Loss' : 'Net Profit', $data['netProfitLoss']],
        ];

        $headings = ['Account', 'Amount (TZS)'];

        $dateFrom = $data['dateFrom'];
        $dateTo = $data['dateTo'];
        $filename = 'profit-loss-' . ($dateFrom && $dateTo ? $dateFrom->format('Y-m-d') . '_to_' . $dateTo->format('Y-m-d') : ($data['period'] ?? 'report')) . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SimpleArrayExport($rows, $headings, 'Profit and Loss'),
            $filename
        );
    }

    /**
     * Build Profit & Loss data (accrual basis) for the requested period.
     * Shared by view, print, PDF and Excel exports so all four surfaces agree.
     *
     * P&L from design tasks (not orders). DesignTask math:
     * - base "price" is subtotal
     * - if requires_receipt = true, VAT is applied as (price * 1.18)
     * - delivery_discount is stored as a positive value and we show it as a negative line item
     */
    private function buildProfitLossData(Request $request): array
    {
        $period = $request->get('period', 'year');

        $range = match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            'custom' => [
                $request->filled('start_date') ? Carbon::parse($request->get('start_date'))->startOfDay() : now()->startOfYear(),
                $request->filled('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : now()->endOfYear(),
            ],
            'all' => [null, null],
            default => [now()->startOfYear(), now()->endOfYear()],
        };

        [$dateFrom, $dateTo] = $range;

        $tasksQuery = DesignTask::activeFinance()
            ->where('status', '!=', DesignTask::STATUS_REJECTED);

        if ($dateFrom && $dateTo) {
            $tasksQuery->whereBetween('created_at', [$dateFrom, $dateTo]);
        }

        // Sales (before discount) = (price with VAT if required) + delivery_cost
        $sales = (float) $tasksQuery
            ->selectRaw(
                'COALESCE(SUM(' .
                '(CASE WHEN requires_receipt = 1 THEN (price * 1.18) ELSE price END) ' .
                '+ COALESCE(delivery_cost, 0)' .
                '), 0) as sales_gross'
            )
            ->value('sales_gross');

        $discountRaw = (float) $tasksQuery->sum('delivery_discount');
        $discount = $discountRaw === 0.0 ? 0.0 : -abs($discountRaw);

        // Total Operating Income = Sales + Discount = (price with VAT if required) + delivery_cost - delivery_discount
        $operatingIncome = (float) $tasksQuery
            ->selectRaw(
                'COALESCE(SUM(' .
                '(CASE WHEN requires_receipt = 1 THEN (price * 1.18) ELSE price END) ' .
                '+ COALESCE(delivery_cost, 0) ' .
                '- COALESCE(delivery_discount, 0)' .
                '), 0) as operating_income'
            )
            ->value('operating_income');

        $expensesQuery = Expense::query();
        if ($dateFrom && $dateTo) {
            $expensesQuery->whereBetween('date', [$dateFrom, $dateTo]);
        }
        $operatingExpense = (float) $expensesQuery->sum('amount');

        $cogs = 0.0;
        $grossProfit = $operatingIncome - $cogs;
        $operatingProfit = $grossProfit - $operatingExpense;

        $netProfitLoss = $operatingProfit; // No non-operating sections in current data model

        return [
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'basisLabel' => 'Accrual',
            'sales' => $sales,
            'discount' => $discount,
            'operatingIncome' => $operatingIncome,
            'cogs' => $cogs,
            'grossProfit' => $grossProfit,
            'operatingExpense' => $operatingExpense,
            'operatingProfit' => $operatingProfit,
            'netProfitLoss' => $netProfitLoss,
        ];
    }

    private function getDateRangeFromPeriod($period)
    {
        $to = match($period) {
            'today' => now()->endOfDay(),
            'yesterday' => now()->subDay()->endOfDay(),
            'week' => now()->endOfWeek(),
            'month' => now()->endOfMonth(),
            'quarter' => now(),
            'year' => now()->endOfYear(),
            default => now()->endOfMonth(),
        };
        $from = match($period) {
            'today' => now()->startOfDay(),
            'yesterday' => now()->subDay()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->subMonths(3),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        return ['from' => $from, 'to' => $to];
    }

    /**
     * Balance due: tasks created in the period OR that had a payment in the period. Tasks only.
     * Optional $departmentId filters by department.
     */
    protected function financeBalanceDue($dateFrom, $dateTo, $departmentId = null, $customerIds = null): float
    {
        $taskQuery = DesignTask::activeFinance();
        $paymentQuery = Payment::activeFinance();

        if ($dateFrom && $dateTo) {
            $taskQuery->whereBetween('created_at', [$dateFrom, $dateTo]);
            $paymentQuery->whereBetween('date', [$dateFrom, $dateTo]);
        }

        if ($departmentId) {
            $taskQuery->where('department_id', $departmentId);
            $paymentQuery->where('department_id', $departmentId);
        }

        if ($customerIds !== null) {
            $taskQuery->whereIn('customer_id', $customerIds);
            $paymentQuery->whereIn('customer_id', $customerIds);
        }

        $taskIdsCreated = $taskQuery->pluck('id')->toArray();
        $taskIdsFromPayments = $paymentQuery->whereNotNull('design_task_id')->pluck('design_task_id')->unique()->values()->toArray();
        $taskIds = array_values(array_unique(array_merge($taskIdsCreated, $taskIdsFromPayments)));

        return (float) (empty($taskIds) ? 0 : DesignTask::whereIn('id', $taskIds)->sum('balance'));
    }

    private function getCashFlowChartData($from, $to, $period, $departmentId = null, $customerIds = null)
    {
        // Handle "All Time" or missing range
        if (!$from || !$to) {
            $to = now()->endOfDay();
            // Default to last 30 days if no range provided
            $from = now()->subDays(29)->startOfDay();
        }

        $labels = [];
        $incomeData = [];
        $expenseData = [];

        $current = clone $from;
        // Limit loop to avoid memory issues if range is somehow huge
        $maxIterations = 1000; 
        $count = 0;

        $isLongTerm = in_array($period, ['6_months', 'year', '2_years', 'all']);
        
        while ($current <= $to && $count < $maxIterations) {
            $labels[] = $current->format($isLongTerm ? 'M y' : 'd M');
            
            $incomeQuery = Payment::activeFinance();
            $expenseQuery = Expense::query();

            if ($isLongTerm) {
                $incomeQuery->whereMonth('date', $current->month)->whereYear('date', $current->year);
                $expenseQuery->whereMonth('date', $current->month)->whereYear('date', $current->year);
            } else {
                $incomeQuery->whereDate('date', $current->format('Y-m-d'));
                $expenseQuery->whereDate('date', $current->format('Y-m-d'));
            }
            
            if ($departmentId) {
                $incomeQuery->where('department_id', $departmentId);
                $expenseQuery->where('department_id', $departmentId);
            }

            if ($customerIds !== null) {
                $incomeQuery->whereIn('customer_id', $customerIds);
            }

            $incomeData[] = (float) $incomeQuery->sum('amount');
            $expenseData[] = (float) $expenseQuery->sum('amount');

            if ($isLongTerm) {
                $current->addMonth();
            } else {
                $current->addDay();
            }
            $count++;
        }

        return [
            'labels' => $labels,
            'income' => $incomeData,
            'expenses' => $expenseData,
        ];
    }

    /**
     * Resolve period/date params for balance sheet from request. Returns [dateFrom, dateTo] (Carbon).
     * - For preset periods (today, week, month, year): always derive range from period so the report matches the filter.
     * - For custom: use explicit start_date/end_date from request only.
     * This prevents stale or wrong dates (e.g. "This Year" showing March because of old hidden fields).
     */
    protected function resolveBalanceSheetPeriod(Request $request): array
    {
        $period = $request->get('period', 'month');

        if ($period === 'custom') {
            if ($request->filled('end_date')) {
                $endDate = Carbon::parse($request->get('end_date'))->endOfDay();
                $startDate = $request->filled('start_date')
                    ? Carbon::parse($request->get('start_date'))->startOfDay()
                    : $endDate->copy()->startOfDay();
                return [$startDate, $endDate];
            }
            $from = now()->startOfMonth();
            $to = now()->endOfMonth();
            return [$from, $to];
        }

        $range = $this->getDateRangeFromPeriod($period);
        return [$range['from'], $range['to']];
    }

    /**
     * Sum payment amounts by method: mobile, cash, bank (same logic as daily report).
     */
    protected function sumPaymentsByMethod($payments): array
    {
        $mobile = $cash = $bank = 0.0;
        foreach ($payments as $p) {
            $method = strtolower((string) ($p->payment_method ?? ''));
            $amount = (float) $p->amount;
            if (str_contains($method, 'mobile') || str_contains($method, 'money') || str_contains($method, 'mpesa') || str_contains($method, 'tigopesa') || str_contains($method, 'airtel')) {
                $mobile += $amount;
            } elseif (str_contains($method, 'bank') || str_contains($method, 'transfer') || str_contains($method, 'crdb') || str_contains($method, 'nmb') || str_contains($method, 'card')) {
                $bank += $amount;
            } else {
                $cash += $amount;
            }
        }
        return ['mobile' => $mobile, 'cash' => $cash, 'bank' => $bank];
    }

    /**
     * Sum expense amounts by method: mobile, cash, bank.
     */
    protected function sumExpensesByMethod($expenses): array
    {
        $mobile = $cash = $bank = 0.0;
        foreach ($expenses as $e) {
            $method = strtolower((string) ($e->payment_method ?? ''));
            $amount = (float) $e->amount;
            if (str_contains($method, 'mobile') || str_contains($method, 'money')) {
                $mobile += $amount;
            } elseif (str_contains($method, 'bank') || str_contains($method, 'card')) {
                $bank += $amount;
            } else {
                $cash += $amount;
            }
        }
        return ['mobile' => $mobile, 'cash' => $cash, 'bank' => $bank];
    }

    /**
     * Build balance sheet data for the selected period only (not cumulative).
     * Cash = payments in period − expenses in period, split by method (mobile, cash, bank). Receivables = balance due for tasks in period. Tasks only.
     */
    protected function buildBalanceSheetData($dateFrom, $dateTo, $departmentId = null): array
    {
        $asAt = $dateTo instanceof \Carbon\Carbon ? $dateTo : Carbon::parse($dateTo)->endOfDay();
        $from = $dateFrom instanceof \Carbon\Carbon ? $dateFrom : Carbon::parse($dateFrom)->startOfDay();

        $paymentQuery = Payment::whereBetween('date', [$from, $asAt]);
        $expenseQuery = Expense::whereBetween('date', [$from, $asAt]);

        if ($departmentId) {
            $paymentQuery->where('department_id', $departmentId);
            $expenseQuery->where('department_id', $departmentId);
        }

        $payments = $paymentQuery->get();
        $expenses = $expenseQuery->get();
        $payByMethod = $this->sumPaymentsByMethod($payments);
        $expByMethod = $this->sumExpensesByMethod($expenses);

        $mobile = $payByMethod['mobile'] - $expByMethod['mobile'];
        $cash = $payByMethod['cash'] - $expByMethod['cash'];
        $bank = $payByMethod['bank'] - $expByMethod['bank'];
        $cashTotal = $mobile + $cash + $bank;

        $receivables = $this->financeBalanceDue($from, $asAt, $departmentId);
        $totalAssets = $cashTotal + $receivables;
        $liabilities = 0;
        $equity = $totalAssets - $liabilities;

        $departmentBreakdown = Department::all()->map(function ($dept) use ($from, $asAt) {
            $paymentsDept = Payment::where('department_id', $dept->id)->whereBetween('date', [$from, $asAt])->get();
            $expensesDept = Expense::where('department_id', $dept->id)->whereBetween('date', [$from, $asAt])->get();
            $payM = $this->sumPaymentsByMethod($paymentsDept);
            $expM = $this->sumExpensesByMethod($expensesDept);
            $netMobile = $payM['mobile'] - $expM['mobile'];
            $netCash = $payM['cash'] - $expM['cash'];
            $netBank = $payM['bank'] - $expM['bank'];
            $deptCash = $netMobile + $netCash + $netBank;
            $taskBal = $this->financeBalanceDue($from, $asAt, $dept->id);
            return [
                'id' => $dept->id,
                'name' => $dept->name,
                'mobile' => $netMobile,
                'cash' => $netCash,
                'bank' => $netBank,
                'total_cash' => $deptCash,
                'receivables' => $taskBal,
                'total_assets' => $deptCash + $taskBal,
            ];
        })->filter(fn ($d) => $d['total_assets'] != 0 || $d['total_cash'] != 0 || $d['receivables'] != 0);

        return [
            'asAt' => $asAt,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'cash' => $cashTotal,
            'cash_mobile' => $mobile,
            'cash_cash' => $cash,
            'cash_bank' => $bank,
            'receivables' => $receivables,
            'total_assets' => $totalAssets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_liabilities_equity' => $liabilities + $equity,
            'department_breakdown' => $departmentBreakdown,
        ];
    }

    /**
     * Balance sheet page: as-at snapshot with period/department filters, print, PDF share.
     */
    public function balanceSheet(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveBalanceSheetPeriod($request);
        $departmentId = $request->get('department_id') ?: null;
        $currentDept = $departmentId ? Department::find($departmentId) : null;
        $data = $this->buildBalanceSheetData($dateFrom, $dateTo, $departmentId);
        $departments = Department::all();
        $period = $request->get('period', 'month');

        return view('admin.finance.balance-sheet', array_merge($data, [
            'period' => $period,
            'currentDept' => $currentDept,
            'departments' => $departments,
        ]));
    }

    /**
     * Balance sheet PDF export (same filters as page).
     */
    public function balanceSheetPdf(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveBalanceSheetPeriod($request);
        $departmentId = $request->get('department_id') ?: null;
        $currentDept = $departmentId ? Department::find($departmentId) : null;
        $data = $this->buildBalanceSheetData($dateFrom, $dateTo, $departmentId);
        $data['period'] = $request->get('period', 'month');
        $data['currentDept'] = $currentDept;

        $asAtStr = $data['asAt']->format('Y-m-d');
        $filename = 'balance-sheet-' . $asAtStr . ($departmentId ? '-dept-' . $departmentId : '') . '.pdf';
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.finance.balance-sheet-pdf', $data)
            ->setPaper('a4', 'portrait');
        return $pdf->download($filename);
    }

    /**
     * Balance sheet print view (same filters as page).
     */
    public function balanceSheetPrint(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveBalanceSheetPeriod($request);
        $departmentId = $request->get('department_id') ?: null;
        $currentDept = $departmentId ? Department::find($departmentId) : null;
        $data = $this->buildBalanceSheetData($dateFrom, $dateTo, $departmentId);
        $data['period'] = $request->get('period', 'month');
        $data['currentDept'] = $currentDept;

        return view('admin.finance.print-balance-sheet', $data);
    }

    /**
     * Balance sheet Excel export (same filters as page).
     */
    public function balanceSheetExcel(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveBalanceSheetPeriod($request);
        $departmentId = $request->get('department_id') ?: null;
        $data = $this->buildBalanceSheetData($dateFrom, $dateTo, $departmentId);

        $headings = ['Item', 'Amount (TZS)', 'Department', 'Mobile', 'Cash', 'Bank', 'Receivables', 'Total Assets'];

        $rows = [
            ['Mobile (net)', $data['cash_mobile'], '', '', '', '', '', ''],
            ['Cash (net)', $data['cash_cash'], '', '', '', '', '', ''],
            ['Bank (net)', $data['cash_bank'], '', '', '', '', '', ''],
            ['Accounts receivable', $data['receivables'], '', '', '', '', '', ''],
            ['Total assets', $data['total_assets'], '', '', '', '', '', ''],
            ['Liabilities', $data['liabilities'], '', '', '', '', '', ''],
            ['Equity (net position)', $data['equity'], '', '', '', '', '', ''],
            ['Total liabilities & equity', $data['total_liabilities_equity'], '', '', '', '', '', ''],
        ];

        foreach ($data['department_breakdown'] as $d) {
            $rows[] = ['', '', $d['name'], $d['mobile'] ?? 0, $d['cash'] ?? 0, $d['bank'] ?? 0, $d['receivables'], $d['total_assets']];
        }

        $asAtStr = $data['asAt']->format('Y-m-d');
        $filename = 'balance-sheet-' . $asAtStr . ($departmentId ? '-dept-' . $departmentId : '') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SimpleArrayExport($rows, $headings, 'Balance Sheet'),
            $filename
        );
    }

    /**
     * Build the filtered expenses query (search / category / department / date range).
     * Shared by index, print, PDF and Excel so all four surfaces show identical figures.
     */
    private function buildExpensesQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = Expense::with(['department', 'approvedBy']);

        // Search filter (Notes)
        if ($request->filled('search')) {
            $query->where('notes', 'like', '%' . $request->search . '%');
        }

        // Category filter
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Department filter
        if ($request->filled('department_id') && $request->department_id !== 'all') {
            $query->where('department_id', $request->department_id);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        return $query->latest('date');
    }

    public function expenses(Request $request)
    {
        $expenses = $this->buildExpensesQuery($request)->paginate(25)->withQueryString();
        $departments = Department::all();

        return view('admin.finance.expenses', compact('expenses', 'departments'));
    }

    public function printExpenses(Request $request)
    {
        $expenses = $this->buildExpensesQuery($request)->get();

        return view('admin.finance.print-expenses', compact('expenses'));
    }

    /**
     * Download filtered expenses as PDF.
     */
    public function expensesPdf(Request $request)
    {
        $expenses = $this->buildExpensesQuery($request)->get();
        $title = 'Expense Report';
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.exports.expenses', compact('expenses', 'title', 'dateFrom', 'dateTo'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('expense-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Download filtered expenses as Excel.
     */
    public function expensesExcel(Request $request)
    {
        $expenses = $this->buildExpensesQuery($request)->get();

        $rows = $expenses->map(function ($expense) {
            return [
                $expense->date->format('Y-m-d'),
                $expense->category,
                $expense->department->name ?? 'General',
                $expense->notes ?: '',
                ucfirst(str_replace('_', ' ', $expense->payment_method)),
                (float) $expense->amount,
                $expense->approvedBy->name ?? '',
            ];
        })->toArray();

        $headings = ['Date', 'Category', 'Department', 'Notes', 'Payment Method', 'Amount (TZS)', 'Approved By'];

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SimpleArrayExport($rows, $headings, 'Expenses'),
            'expense-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'category' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'date' => 'required|date',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $validated['approved_by_id'] = auth()->id();

        Expense::create($validated);

        return redirect()->back()->with('success', 'Expense recorded successfully.');
    }

    public function updateExpense(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'category' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'date' => 'required|date',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $expense->update($validated);

        return redirect()->back()->with('success', 'Expense updated successfully.');
    }

    public function destroyExpense($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return redirect()->back()->with('success', 'Expense deleted successfully.');
    }

    public function destroyPayment($id)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Unauthorized action. Only Superadmins can delete payments.');
        }

        $payment = Payment::findOrFail($id);
        $amount = (float) $payment->amount;

        // Revert DesignTask balance if linked
        if ($payment->design_task_id) {
            $task = DesignTask::find($payment->design_task_id);
            if ($task) {
                $task->amount_paid = max(0, (float)$task->amount_paid - $amount);
                
                // Recalculate balance
                $basePrice = $task->requires_receipt ? $task->price * 1.18 : $task->price;
                $deliveryCost = floatval($task->delivery_cost ?? 0);
                $deliveryDiscount = floatval($task->delivery_discount ?? 0);
                $totalPrice = $basePrice + $deliveryCost - $deliveryDiscount;
                $task->balance = max(0, $totalPrice - $task->amount_paid);
                $task->save();

                // Log a task update
                \App\Models\TaskUpdate::create([
                    'task_id' => $task->id,
                    'admin_id' => auth()->id(),
                    'type' => \App\Models\TaskUpdate::TYPE_COMMENT,
                    'content' => "Payment transaction deleted by Superadmin. Amount reverted: TZS " . number_format($amount) . ".",
                ]);
            }
        }

        // Revert Order balance and status if linked
        if ($payment->order_id) {
            $order = Order::find($payment->order_id);
            if ($order) {
                $order->amount_paid = max(0, (float)$order->amount_paid - $amount);
                $order->balance = max(0, (float)$order->total_amount - $order->amount_paid);
                
                // Determine payment status
                if ($order->amount_paid <= 0) {
                    $order->payment_status = 'pending';
                } elseif ($order->balance <= 0) {
                    $order->payment_status = 'paid';
                } else {
                    $order->payment_status = 'partial';
                }

                // Log payment deletion in order notes
                $noteEntry = "Payment deleted: TZS " . number_format($amount) . " by Superadmin [" . now()->format('d/m/Y H:i') . "]";
                $order->notes = ($order->notes ? $order->notes . "\n" : "") . $noteEntry;
                $order->save();
            }
        }

        $customerId = $payment->customer_id;

        // Permanently delete the payment
        $payment->delete();

        // Recalculate customer analytics
        if ($customerId) {
            try {
                $analyticsService = app(\App\Services\CustomerAnalyticsService::class);
                $analyticsService->recalculateCustomerAnalytics($customerId);
            } catch (\Exception $e) {
                \Log::error('Failed to update customer analytics on payment deletion', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->back()->with('success', 'Payment deleted and transaction reverted successfully.');
    }

    public function voucher($id)
    {
        $expense = Expense::with(['department', 'approvedBy'])->findOrFail($id);
        return view('admin.finance.voucher', compact('expense'));
    }

    /**
     * Resolve [dateFrom, dateTo] (Y-m-d strings or null) for the cash flow ledger from
     * either explicit date_from/date_to request params or a named period.
     * Shared by view, print, PDF and Excel so period shortcuts behave identically everywhere
     * (previously printCashFlow ignored the "period" param entirely, drifting from the page).
     */
    private function resolveCashFlowDates(Request $request): array
    {
        $period = $request->get('period');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        if ($period && !$dateFrom && !$dateTo) {
            switch ($period) {
                case 'today':
                    $dateFrom = now()->format('Y-m-d');
                    $dateTo = now()->format('Y-m-d');
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
            }
        }

        return [$dateFrom, $dateTo];
    }

    /**
     * Cash flow "customers" view query (outstanding balance per customer). Shared by view, print,
     * PDF and Excel.
     */
    private function buildCashFlowCustomersQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        return Customer::select('customers.*')
            ->selectRaw('(SELECT SUM(amount) FROM payments WHERE payments.customer_id = customers.id) as total_paid')
            ->selectRaw('(SELECT SUM(balance) FROM design_tasks WHERE design_tasks.customer_id = customers.id AND design_tasks.deleted_at IS NULL) as unpaid_balance')
            ->selectRaw('(SELECT status FROM leads WHERE leads.phone = customers.phone ORDER BY created_at DESC LIMIT 1) as lead_status')
            ->when($request->search, function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            })
            ->orderByDesc('unpaid_balance')
            ->orderByDesc('total_paid');
    }

    /**
     * Cash flow unified ledger (payments + expenses, sorted by date desc). Shared by view, print,
     * PDF and Excel so all four surfaces show identical figures.
     */
    private function buildCashFlowLedgerEntries(Request $request, ?string $dateFrom, ?string $dateTo): \Illuminate\Support\Collection
    {
        // Fetch Payments (Cash In)
        $paymentQuery = Payment::activeFinance()->with(['customer', 'order.items.product', 'seller', 'department']);
        if ($dateFrom) $paymentQuery->whereDate('date', '>=', $dateFrom);
        if ($dateTo) $paymentQuery->whereDate('date', '<=', $dateTo);
        if ($request->filled('payment_method')) $paymentQuery->where('payment_method', $request->payment_method);
        if ($request->filled('seller_id')) $paymentQuery->where('seller_id', $request->seller_id);
        if ($request->filled('department_id')) $paymentQuery->where('department_id', $request->department_id);
        if ($request->filled('search')) {
            $paymentQuery->whereHas('customer', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }
        $payments = $paymentQuery->get()->map(function($p) {
            $p->entry_type = 'payment';
            $p->date = \Carbon\Carbon::parse($p->date);
            return $p;
        });

        // Fetch Expenses (Cash Out)
        $expenseQuery = Expense::with(['department', 'approvedBy']);
        if ($dateFrom) $expenseQuery->whereDate('date', '>=', $dateFrom);
        if ($dateTo) $expenseQuery->whereDate('date', '<=', $dateTo);
        if ($request->filled('payment_method')) $expenseQuery->where('payment_method', $request->payment_method);
        if ($request->filled('department_id')) $expenseQuery->where('department_id', $request->department_id);
        if ($request->filled('search')) {
            $expenseQuery->where(function($q) use ($request) {
                $q->where('category', 'like', "%{$request->search}%")
                  ->orWhere('notes', 'like', "%{$request->search}%");
            });
        }
        $expenses = $expenseQuery->get()->map(function($e) {
            $e->entry_type = 'expense';
            $e->date = \Carbon\Carbon::parse($e->date);
            return $e;
        });

        // Combine and Sort
        return $payments->concat($expenses)->sortByDesc('date');
    }

    public function cashFlow(Request $request)
    {
        $view = $request->get('view', 'history');
        $sellers = User::whereIn('role', ['saler', 'admin', 'super_admin', 'manager', 'accountant'])
            ->viewableStaff()
            ->get();
        $departments = Department::all();
        $period = $request->get('period');
        [$dateFrom, $dateTo] = $this->resolveCashFlowDates($request);

        if ($view === 'customers') {
            $customers = $this->buildCashFlowCustomersQuery($request)->paginate(20)->withQueryString();

            return view('admin.finance.cash-flow', compact('customers', 'view', 'sellers', 'departments', 'period', 'dateFrom', 'dateTo'));
        }

        $allEntries = $this->buildCashFlowLedgerEntries($request, $dateFrom, $dateTo);

        // Manual Pagination
        $perPage = 25;
        $page = $request->get('page', 1);
        $pagedEntries = new \Illuminate\Pagination\LengthAwarePaginator(
            $allEntries->forPage($page, $perPage),
            $allEntries->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.finance.cash-flow', compact('pagedEntries', 'view', 'sellers', 'departments', 'period', 'dateFrom', 'dateTo'));
    }

    public function printCashFlow(Request $request)
    {
        $view = $request->get('view', 'history');
        [$dateFrom, $dateTo] = $this->resolveCashFlowDates($request);

        if ($view === 'customers') {
            $customers = $this->buildCashFlowCustomersQuery($request)->get();

            return view('admin.finance.print-cash-flow', compact('customers', 'view'));
        }

        $entries = $this->buildCashFlowLedgerEntries($request, $dateFrom, $dateTo);

        return view('admin.finance.print-cash-flow', compact('entries', 'view', 'dateFrom', 'dateTo'));
    }

    /**
     * Download cash flow (ledger or customers, matching ?view=) as PDF.
     */
    public function cashFlowPdf(Request $request)
    {
        $view = $request->get('view', 'history');
        [$dateFrom, $dateTo] = $this->resolveCashFlowDates($request);

        if ($view === 'customers') {
            $customers = $this->buildCashFlowCustomersQuery($request)->get();
            $title = 'Customer Balances';

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.exports.cash-flow-customers', compact('customers', 'title'))
                ->setPaper('a4', 'portrait');
            return $pdf->download('cash-flow-customers-' . now()->format('Y-m-d') . '.pdf');
        }

        $entries = $this->buildCashFlowLedgerEntries($request, $dateFrom, $dateTo);
        $title = 'Cash Flow Statement';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.exports.cash-flow', compact('entries', 'title', 'dateFrom', 'dateTo'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('cash-flow-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Download cash flow (ledger or customers, matching ?view=) as Excel.
     */
    public function cashFlowExcel(Request $request)
    {
        $view = $request->get('view', 'history');
        [$dateFrom, $dateTo] = $this->resolveCashFlowDates($request);

        if ($view === 'customers') {
            $customers = $this->buildCashFlowCustomersQuery($request)->get();

            $rows = $customers->map(function ($c) {
                return [
                    $c->name,
                    $c->phone,
                    (float) ($c->total_paid ?? 0),
                    (float) ($c->unpaid_balance ?? 0),
                    $c->lead_status ? ucfirst($c->lead_status) : 'New',
                ];
            })->toArray();

            $headings = ['Customer Name', 'Phone', 'Total Paid', 'Outstanding Balance', 'Lead Status'];

            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\SimpleArrayExport($rows, $headings, 'Customer Balances'),
                'cash-flow-customers-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        $entries = $this->buildCashFlowLedgerEntries($request, $dateFrom, $dateTo);

        $rows = $entries->map(function ($entry) {
            return [
                $entry->entry_type === 'payment' ? 'IN' : 'OUT',
                $entry->date->format('Y-m-d'),
                $entry->entry_type === 'payment' ? ($entry->customer->name ?? 'Unknown') : $entry->category,
                $entry->entry_type === 'payment'
                    ? ($entry->order ? 'Order #' . $entry->order->order_code : ($entry->designTask->title ?? 'Manual Payment'))
                    : ($entry->notes ?: ''),
                ucfirst(str_replace('_', ' ', $entry->payment_method)),
                (float) $entry->amount,
            ];
        })->toArray();

        $headings = ['Flow', 'Date', 'Source / Recipient', 'Details', 'Method', 'Amount (TZS)'];

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SimpleArrayExport($rows, $headings, 'Cash Flow'),
            'cash-flow-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function reports(Request $request)
    {
        $period = $request->get('period', 'month');
        $dateFrom = null;
        $dateTo = null;
        $customerIds = null;

        // Mode: customer_range (single date range used for both finance dates + customer filtering)
        if ($period === 'customer_range') {
            if ($request->filled('customer_date_from') || $request->filled('customer_date_to')) {
                $dateFrom = $request->filled('customer_date_from')
                    ? Carbon::parse($request->get('customer_date_from'))->startOfDay()
                    : now()->startOfMonth()->startOfDay();
                $dateTo = $request->filled('customer_date_to')
                    ? Carbon::parse($request->get('customer_date_to'))->endOfDay()
                    : now()->endOfMonth()->endOfDay();

                $customerIds = Customer::whereBetween('created_at', [$dateFrom, $dateTo])
                    ->pluck('id')
                    ->toArray();
            } else {
                // fallback: still show finance month, but no customer filter
                $dateRange = $this->getDateRangeFromPeriod('month');
                $dateFrom = $dateRange['from'];
                $dateTo = $dateRange['to'];
            }
        } else {
            // Support custom range (same UI concept as finance dashboard).
            if ($period === 'custom') {
                $dateFrom = $request->filled('start_date')
                    ? Carbon::parse($request->get('start_date'))->startOfDay()
                    : now()->startOfMonth()->startOfDay();
                $dateTo = $request->filled('end_date')
                    ? Carbon::parse($request->get('end_date'))->endOfDay()
                    : now()->endOfMonth()->endOfDay();
            } else {
                $dateRange = $this->getDateRangeFromPeriod($period);
                $dateFrom = $dateRange['from'];
                $dateTo = $dateRange['to'];
            }
        }
        $departmentId = $request->get('department_id');
        $currentDept = $departmentId ? Department::find($departmentId) : null;

        // Optional: filter analytics by customers added in a date range
        // (when not in customer_range mode, we use the separate customer_date_from/to inputs)
        if ($period !== 'customer_range' && ($request->filled('customer_date_from') || $request->filled('customer_date_to'))) {
            $custFrom = $request->filled('customer_date_from')
                ? Carbon::parse($request->get('customer_date_from'))->startOfDay()
                : Carbon::parse($request->get('customer_date_to'))->startOfDay();

            $custTo = $request->filled('customer_date_to')
                ? Carbon::parse($request->get('customer_date_to'))->endOfDay()
                : Carbon::parse($request->get('customer_date_from'))->endOfDay();

            $customerIds = Customer::whereBetween('created_at', [$custFrom, $custTo])->pluck('id')->toArray();
        }

        // Base Queries with optional department filter (tasks only)
        $paymentQuery = Payment::activeFinance()->whereBetween('date', [$dateFrom, $dateTo]);
        $expenseQuery = Expense::whereBetween('date', [$dateFrom, $dateTo]);
        $taskQuery = DesignTask::activeFinance()->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($departmentId) {
            $paymentQuery->where('department_id', $departmentId);
            $expenseQuery->where('department_id', $departmentId);
            $taskQuery->where('department_id', $departmentId);
        }

        if ($customerIds !== null) {
            $paymentQuery->whereIn('customer_id', $customerIds);
            $taskQuery->whereIn('customer_id', $customerIds);
        }

        // Totals — Balance due: tasks created in period OR had payment in period
        $deposited = $paymentQuery->sum('amount');
        $spent = $expenseQuery->sum('amount');
        $balanceDue = $this->financeBalanceDue($dateFrom, $dateTo, $departmentId, $customerIds);
        $totalRevenue = $deposited + $balanceDue;
        $balance = $deposited - $spent;
        $credited = $taskQuery->sum('balance'); // for dept/seller breakdown
        
        $totalBilled = $taskQuery->sum('price');
        $debtCollected = $paymentQuery->where('is_debt', 1)->sum('amount');


        // Dept Breakdown
        $deptWise = Department::all()->map(function($dept) use ($dateFrom, $dateTo, $customerIds) {
            $collectedQuery = Payment::where('department_id', $dept->id)
                ->whereBetween('date', [$dateFrom, $dateTo]);
            $creditedQuery = DesignTask::where('department_id', $dept->id)
                ->whereBetween('created_at', [$dateFrom, $dateTo]);

            if ($customerIds !== null) {
                $collectedQuery->whereIn('customer_id', $customerIds);
                $creditedQuery->whereIn('customer_id', $customerIds);
            }

            return [
                'id' => $dept->id,
                'name' => $dept->name,
                'collected' => $collectedQuery->sum('amount'),
                'expenses' => Expense::where('department_id', $dept->id)->whereBetween('date', [$dateFrom, $dateTo])->sum('amount'),
                'credited' => $creditedQuery->sum('balance')
            ];
        })->map(function($d) {
            $d['total_revenue'] = $d['collected'] + $d['credited'];
            return $d;
        });

        // Seller Breakdown (tasks only)
        $sellerWise = User::whereIn('role', ['saler', 'admin', 'super_admin', 'manager', 'accountant'])
            ->viewableStaff()
            ->get()->map(function($seller) use ($dateFrom, $dateTo, $departmentId, $customerIds) {
            $collectedQuery = Payment::where('seller_id', $seller->id)->whereBetween('date', [$dateFrom, $dateTo]);
            $taskCreditedQuery = DesignTask::where('saler_id', $seller->id)->whereBetween('created_at', [$dateFrom, $dateTo]);

            if ($departmentId) {
                $collectedQuery->where('department_id', $departmentId);
                $taskCreditedQuery->where('department_id', $departmentId);
            }

            if ($customerIds !== null) {
                $collectedQuery->whereIn('customer_id', $customerIds);
                $taskCreditedQuery->whereIn('customer_id', $customerIds);
            }

            return [
                'name' => $seller->name,
                'collected' => $collectedQuery->sum('amount'),
                'credited' => $taskCreditedQuery->sum('balance')
            ];
        })->map(function($s) {
            $s['total_revenue'] = $s['collected'] + $s['credited'];
            return $s;
        })->filter(fn($s) => $s['total_revenue'] > 0);

        // Recent Entries for this context
        $recentTransactions = Payment::with(['customer', 'department'])
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->when($customerIds !== null, fn($q) => $q->whereIn('customer_id', $customerIds))
            ->latest()
            ->limit(10)
            ->get();

        $departments = Department::all();
        $chartData = $this->getCashFlowChartData($dateFrom, $dateTo, $period, $departmentId, $customerIds);

        return view('admin.finance.reports', compact(
            'deposited', 'spent', 'credited', 'balanceDue', 'totalRevenue', 'balance', 'deptWise', 'sellerWise',
            'period', 'dateFrom', 'dateTo', 'currentDept', 'departments', 'recentTransactions', 'chartData', 'totalBilled', 'debtCollected'
        ));
    }

    /**
     * Resolve period/date params and return [period, dateFrom, dateTo].
     */
    protected function resolveDailyReportPeriod(?string $period, ?string $dateFrom, ?string $dateTo): array
    {
        if (!$period) {
            $period = ($dateFrom || $dateTo) ? 'custom' : 'today';
        }
        if ($period === 'custom') {
            $dateFrom = $dateFrom ?: now()->format('Y-m-d');
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
                default:
                    $dateFrom = now()->format('Y-m-d');
                    $dateTo = now()->format('Y-m-d');
            }
        }
        return [$period, $dateFrom, $dateTo];
    }

    /**
     * Build daily report data for the given period. Used by both admin and shared public report.
     */
    protected function buildDailyReportData(string $period, string $dateFrom, string $dateTo): array
    {
        $carbonFrom = Carbon::parse($dateFrom)->startOfDay();
        $carbonTo = Carbon::parse($dateTo)->endOfDay();
        $isRange = $dateFrom !== $dateTo;
        $createdFrom = $carbonFrom->copy();
        $createdTo = $carbonTo->copy();
        $departments = Department::all();
        $reportData = [];

        // Add a "General/Main" bucket for items without a department if necessary
        // But let's follow the standard departments first
        foreach ($departments as $dept) {
            $incomeItems = [];

            // 1. Get all design tasks created in this period for this dept (full day range so unpaid clients are included)
            $tasks = DesignTask::where('department_id', $dept->id)
                ->whereBetween('created_at', [$createdFrom, $createdTo])
                ->where('status', '!=', DesignTask::STATUS_CANCELLED)
                ->where('is_loss', false)
                ->with('customer')
                ->get();

            foreach ($tasks as $task) {
                $key = 'task_' . $task->id;
                $incomeItems[$key] = [
                    'customer_name' => $task->customer->name ?? 'N/A',
                    'description' => ($task->description ?: $task->title) . ($task->requires_receipt ? ' (Incl. VAT)' : ''),
                    'mobile' => 0,
                    'cash' => 0,
                    'bank' => 0,
                    'remain' => $task->balance,
                    'is_debt' => false
                ];
            }

            // 2. Get all payments recorded in this period for this dept (tasks only; includes payments for old debts)
            $payments = Payment::where('department_id', $dept->id)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->where(function($q) {
                    $q->whereDoesntHave('designTask')
                      ->orWhereHas('designTask', function($tq) {
                          $tq->where('status', '!=', DesignTask::STATUS_CANCELLED)
                             ->where('is_loss', false);
                      });
                })
                ->where(function($q) {
                    $q->whereDoesntHave('order')
                      ->orWhereHas('order', function($oq) {
                          $oq->where('approval_status', '!=', 'cancelled');
                      });
                })
                ->with(['designTask.customer', 'customer'])
                ->get();

            foreach ($payments as $payment) {
                $key = '';
                if ($payment->design_task_id) {
                    $key = 'task_' . $payment->design_task_id;
                } else {
                    $key = 'pay_' . $payment->id;
                }

                if (!isset($incomeItems[$key])) {
                    $customerName = 'N/A';
                    $description = '';
                    $balance = 0;

                    if ($payment->customer) {
                        $customerName = $payment->customer->name;
                    } elseif ($payment->designTask && $payment->designTask->customer) {
                        $customerName = $payment->designTask->customer->name;
                    }

                    if ($payment->design_task_id && $payment->designTask) {
                        $description = ($payment->designTask->description ?: $payment->designTask->title) . ($payment->designTask->requires_receipt ? ' (Incl. VAT)' : '');
                        $balance = $payment->designTask->balance;
                    } else {
                        $description = ($payment->customer->name ?? 'Direct Payment') . ($payment->notes ? ' - ' . $payment->notes : '');
                        $balance = 0;
                    }

                    $incomeItems[$key] = [
                        'customer_name' => $customerName,
                        'description' => $description,
                        'mobile' => 0,
                        'cash' => 0,
                        'bank' => 0,
                        'remain' => $balance,
                        'is_debt' => $payment->is_debt
                    ];
                }

                $method = strtolower($payment->payment_method);
                if (str_contains($method, 'mobile') || str_contains($method, 'money') || str_contains($method, 'mpesa') || str_contains($method, 'tigopesa') || str_contains($method, 'airtel')) {
                    $incomeItems[$key]['mobile'] += $payment->amount;
                } elseif (str_contains($method, 'bank') || str_contains($method, 'transfer') || str_contains($method, 'crdb') || str_contains($method, 'nmb')) {
                    $incomeItems[$key]['bank'] += $payment->amount;
                } else {
                    $incomeItems[$key]['cash'] += $payment->amount;
                }

                if ($payment->is_debt) {
                    $incomeItems[$key]['is_debt'] = true;
                }
            }

            $expenses = Expense::where('department_id', $dept->id)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->get();

            $expenseItems = [];
            foreach ($expenses as $expense) {
                $item = [
                    'description' => $expense->notes ?: $expense->category,
                    'mobile' => 0,
                    'cash' => 0,
                    'bank' => 0,
                ];
                
                $method = strtolower($expense->payment_method);
                if (str_contains($method, 'mobile') || str_contains($method, 'money')) {
                    $item['mobile'] = $expense->amount;
                } elseif (str_contains($method, 'bank')) {
                    $item['bank'] = $expense->amount;
                } else {
                    $item['cash'] = $expense->amount;
                }
                $expenseItems[] = $item;
            }

            if (!empty($incomeItems) || !empty($expenseItems)) {
                $reportData[] = [
                    'department' => $dept,
                    'incomeItems' => array_values($incomeItems),
                    'expenseItems' => $expenseItems,
                ];
            }
        }

        // Handle items with NULL department_id
        $incomeItems = [];

        $unassignedTasks = DesignTask::whereNull('department_id')
            ->whereBetween('created_at', [$createdFrom, $createdTo])
            ->where('status', '!=', DesignTask::STATUS_CANCELLED)
            ->where('is_loss', false)
            ->with('customer')
            ->get();

        foreach ($unassignedTasks as $task) {
            $key = 'task_' . $task->id;
            $incomeItems[$key] = [
                'customer_name' => $task->customer->name ?? 'N/A',
                'description' => ($task->description ?: $task->title) . ($task->requires_receipt ? ' (Incl. VAT)' : ''),
                'mobile' => 0,
                'cash' => 0,
                'bank' => 0,
                'remain' => $task->balance,
                'is_debt' => false
            ];
        }

        $unassignedPayments = Payment::whereNull('department_id')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->where(function($q) {
                $q->whereDoesntHave('designTask')
                  ->orWhereHas('designTask', function($tq) {
                      $tq->where('status', '!=', DesignTask::STATUS_CANCELLED)
                         ->where('is_loss', false);
                  });
            })
            ->where(function($q) {
                $q->whereDoesntHave('order')
                  ->orWhereHas('order', function($oq) {
                      $oq->where('approval_status', '!=', 'cancelled');
                  });
            })
            ->with(['designTask.customer', 'customer'])
            ->get();

        foreach ($unassignedPayments as $payment) {
            $key = '';
            if ($payment->design_task_id) {
                $key = 'task_' . $payment->design_task_id;
            } else {
                $key = 'pay_' . $payment->id;
            }

            if (!isset($incomeItems[$key])) {
                $customerName = 'N/A';
                $description = '';
                $balance = 0;

                if ($payment->customer) {
                    $customerName = $payment->customer->name;
                } elseif ($payment->designTask && $payment->designTask->customer) {
                    $customerName = $payment->designTask->customer->name;
                }

                if ($payment->design_task_id && $payment->designTask) {
                    $description = ($payment->designTask->description ?: $payment->designTask->title) . ($payment->designTask->requires_receipt ? ' (Incl. VAT)' : '');
                    $balance = $payment->designTask->balance;
                } else {
                    $description = ($payment->customer->name ?? 'Direct Payment') . ($payment->notes ? ' - ' . $payment->notes : '');
                    $balance = 0;
                }

                $incomeItems[$key] = [
                    'customer_name' => $customerName,
                    'description' => $description,
                    'mobile' => 0,
                    'cash' => 0,
                    'bank' => 0,
                    'remain' => $balance,
                    'is_debt' => $payment->is_debt
                ];
            }

            $method = strtolower($payment->payment_method);
            if (str_contains($method, 'mobile') || str_contains($method, 'money') || str_contains($method, 'mpesa') || str_contains($method, 'tigopesa') || str_contains($method, 'airtel')) {
                $incomeItems[$key]['mobile'] += $payment->amount;
            } elseif (str_contains($method, 'bank') || str_contains($method, 'transfer') || str_contains($method, 'crdb') || str_contains($method, 'nmb') || str_contains($method, 'card')) {
                $incomeItems[$key]['bank'] += $payment->amount;
            } else {
                $incomeItems[$key]['cash'] += $payment->amount;
            }

            if ($payment->is_debt) {
                $incomeItems[$key]['is_debt'] = true;
            }
        }

        $unassignedExpenses = Expense::whereNull('department_id')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->get();

        $expenseItems = [];
        foreach ($unassignedExpenses as $expense) {
            $item = ['description' => $expense->notes ?: $expense->category, 'mobile' => 0, 'cash' => 0, 'bank' => 0];
            $method = strtolower($expense->payment_method);
            if (str_contains($method, 'mobile') || str_contains($method, 'money')) { $item['mobile'] = $expense->amount; }
            elseif (str_contains($method, 'bank') || str_contains($method, 'card')) { $item['bank'] = $expense->amount; }
            else { $item['cash'] = $expense->amount; }
            $expenseItems[] = $item;
        }

        if (!empty($incomeItems) || !empty($expenseItems)) {
            $reportData[] = [
                'department' => (object)['name' => 'General / Unassigned', 'id' => 0],
                'incomeItems' => array_values($incomeItems),
                'expenseItems' => $expenseItems,
            ];
        }

        // Calculate Grand Totals by Method for the Summary
        $grandTotals = [
            'income' => ['mobile' => 0, 'cash' => 0, 'bank' => 0, 'remain' => 0, 'total_debt' => 0],
            'expense' => ['mobile' => 0, 'cash' => 0, 'bank' => 0]
        ];

        foreach ($reportData as $data) {
            foreach ($data['incomeItems'] as $item) {
                $grandTotals['income']['mobile'] += $item['mobile'];
                $grandTotals['income']['cash'] += $item['cash'];
                $grandTotals['income']['bank'] += $item['bank'];
                $grandTotals['income']['remain'] += $item['remain'];
                if ($item['is_debt'] ?? false) {
                    $grandTotals['income']['total_debt'] += ($item['mobile'] + $item['cash'] + $item['bank']);
                }
            }
            foreach ($data['expenseItems'] as $item) {
                $grandTotals['expense']['mobile'] += $item['mobile'];
                $grandTotals['expense']['cash'] += $item['cash'];
                $grandTotals['expense']['bank'] += $item['bank'];
            }
        }

        return compact(
            'reportData', 'dateFrom', 'dateTo',
            'carbonFrom', 'carbonTo', 'isRange', 'departments', 'period', 'grandTotals'
        );
    }

    public function dailyReport(Request $request)
    {
        [$period, $dateFrom, $dateTo] = $this->resolveDailyReportPeriod(
            $request->get('period'),
            $request->get('date_from'),
            $request->get('date_to')
        );
        $data = $this->buildDailyReportData($period, $dateFrom, $dateTo);
        $data['previewUrl'] = URL::temporarySignedRoute(
            'finance.daily-report.shared',
            now()->addHours(12),
            array_filter([
                'period' => $period,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ], fn ($v) => $v !== null && $v !== '')
        );
        return view('admin.finance.daily-report', $data);
    }

    /**
     * Public shareable daily report. Requires valid signed URL (no auth).
     * Use the "Share" button in admin to generate a link valid for 7 days.
     */
    public function dailyReportShared(Request $request)
    {
        [$period, $dateFrom, $dateTo] = $this->resolveDailyReportPeriod(
            $request->get('period'),
            $request->get('date_from'),
            $request->get('date_to')
        );
        $data = $this->buildDailyReportData($period, $dateFrom, $dateTo);
        $data['shareView'] = true;
        return view('admin.finance.daily-report-public', $data);
    }

    /**
     * Download the filtered daily report as PDF for sharing.
     */
    public function dailyReportPdf(Request $request)
    {
        [$period, $dateFrom, $dateTo] = $this->resolveDailyReportPeriod(
            $request->get('period'),
            $request->get('date_from'),
            $request->get('date_to')
        );
        $data = $this->buildDailyReportData($period, $dateFrom, $dateTo);
        $filename = 'finance-daily-report-' . ($dateFrom === $dateTo ? $dateFrom : $dateFrom . '_to_' . $dateTo) . '.pdf';
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.finance.daily-report-pdf', $data)
            ->setPaper('a4', 'landscape');
        return $pdf->download($filename);
    }

    public function proformaIndex(Request $request)
    {
        $query = Order::with(['user', 'department', 'saler'])
            ->where('type', 'proforma');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('order_code', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($c) use ($request) {
                      $c->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('department_id') && $request->department_id !== 'all') {
            $query->where('department_id', $request->department_id);
        }

        $proformas = $query->latest()->paginate(25)->withQueryString();
        $departments = Department::all();

        return view('admin.finance.proforma-index', compact('proformas', 'departments'));
    }

    public function getProformaDetails($id)
    {
        $order = Order::with(['items', 'user', 'saler', 'department'])->where('type', 'proforma')->findOrFail($id);
        return response()->json([
            'id'              => $order->id,
            'order_code'      => $order->order_code,
            'date'            => $order->created_at->format('M d, Y H:i'),
            'status'          => $order->approval_status,
            'notes'           => $order->notes,
            'subtotal'        => $order->subtotal,
            'total_amount'    => $order->total_amount,
            'customer'        => [
                'name'    => $order->user->name ?? 'N/A',
                'phone'   => $order->user->phone ?? '',
                'email'   => $order->user->email ?? '',
                'address' => $order->user->address ?? '',
            ],
            'saler'           => $order->saler->name ?? 'N/A',
            'department'      => $order->department->name ?? 'N/A',
            'print_url'       => route('admin.finance.invoices.proforma', $order->order_code),
            'items'           => $order->items->map(fn($item) => [
                'product_name' => $item->product_name,
                'quantity'     => $item->quantity,
                'unit_price'   => $item->unit_price,
                'subtotal'     => $item->subtotal,
            ]),
        ]);
    }

    public function getProformaEditData($id)
    {
        $order = Order::with('items')->where('type', 'proforma')->findOrFail($id);
        return response()->json([
            'id'         => $order->id,
            'order_code' => $order->order_code,
            'notes'      => $order->notes,
            'items'      => $order->items->map(fn($item) => [
                'id'           => $item->id,
                'product_name' => $item->product_name,
                'quantity'     => $item->quantity,
                'unit_price'   => $item->unit_price,
                'subtotal'     => $item->subtotal,
            ]),
        ]);
    }

    public function updateProforma(Request $request, $id)
    {
        $order = Order::with('items')->where('type', 'proforma')->findOrFail($id);

        $request->validate([
            'notes'              => 'nullable|string|max:1000',
            'items'              => 'required|array|min:1',
            'items.*.id'         => 'required|integer',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $subtotal = 0;
        foreach ($request->items as $itemData) {
            $item = $order->items()->findOrFail($itemData['id']);
            $itemSubtotal = $itemData['quantity'] * $itemData['unit_price'];
            $item->update([
                'quantity'   => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'subtotal'   => $itemSubtotal,
            ]);
            $subtotal += $itemSubtotal;
        }

        $order->notes        = $request->notes;
        $order->subtotal     = $subtotal;
        $order->total_amount = $subtotal;
        $order->balance      = $subtotal;
        $order->save();

        return response()->json(['success' => true, 'message' => 'Proforma invoice updated.']);
    }

    public function convertProformaToTasks(Request $request, $id)
    {
        $order = Order::with(['items', 'user'])->where('type', 'proforma')->findOrFail($id);

        // Resolve Customer from CustomerBusiness or User contact info
        $customer = null;
        if ($order->customer_business_id) {
            $business = \App\Models\CustomerBusiness::find($order->customer_business_id);
            if ($business) {
                $customer = Customer::find($business->customer_id);
            }
        }
        if (!$customer && $order->user) {
            $user = $order->user;
            $customer = Customer::where(function ($q) use ($user) {
                if ($user->email) $q->where('email', $user->email);
                if ($user->phone) $q->orWhere('phone', $user->phone);
            })->first();
        }

        $tasksCreated = [];
        foreach ($order->items as $item) {
            $task = DesignTask::create([
                'title'       => $item->product_name,
                'task_code'   => DesignTask::generateTaskCode(),
                'customer_id' => $customer?->id,
                'receptionist_id' => auth()->id(),
                'saler_id'    => $order->saler_id,
                'department_id' => $order->department_id,
                'price'       => $item->subtotal,
                'qty'         => $item->quantity,
                'rate'        => $item->unit_price,
                'amount_paid' => 0,
                'balance'     => $item->subtotal,
                'status'      => DesignTask::STATUS_PENDING,
                'priority'    => 3,
            ]);
            $tasksCreated[] = $task;

            try {
                if ($customer) {
                    \App\Services\AuditLogService::created(
                        $task,
                        "Converted from Proforma Invoice {$order->order_code} by " . auth()->user()->name
                    );
                }
            } catch (\Exception $e) {}

            $staffToNotify = User::whereIn('role', ['operator', 'admin', 'super_admin', 'accountant'])->get();
            foreach ($staffToNotify as $staff) {
                try {
                    $staff->notify(new \App\Notifications\NewTaskCreatedNotification($task, auth()->user()));
                } catch (\Exception $e) {}
            }
        }

        // Mark the proforma as converted so it isn't exported again accidentally
        $order->approval_status = 'approved';
        $conversionNote = 'Converted to ' . count($tasksCreated) . ' task(s) on ' . now()->format('Y-m-d H:i') . ' by ' . auth()->user()->name . '.';
        $order->notes = $order->notes ? $order->notes . "\n" . $conversionNote : $conversionNote;
        $order->save();

        return response()->json([
            'success'    => true,
            'tasks_count' => count($tasksCreated),
            'message'    => count($tasksCreated) . ' task(s) created from this proforma.',
            'tasks_url'  => route('admin.design-tasks.index'),
        ]);
    }

    // ── VERIFICATION DASHBOARD ────────────────────────────────

    public function verificationDashboard(Request $request)
    {
        $health = new FinanceHealthService();

        $healthScore        = $health->computeHealthScore();
        $outstandingBals    = $health->getOutstandingBalances();
        $duplicatePayments  = $health->detectDuplicatePayments();
        $missingTx          = $health->detectMissingTransactions();
        $debtMismatches     = $health->getDebtStatusMismatches();
        $reconSummary       = $health->getReconciliationSummary();

        // Top debtors (top 20)
        $topDebtors = $outstandingBals->take(20);

        // Stats for header cards
        $stats = [
            'total_outstanding'     => $outstandingBals->sum('total_outstanding'),
            'outstanding_customers' => $outstandingBals->count(),
            'duplicate_count'       => $duplicatePayments->count(),
            'missing_count'         => $missingTx->count(),
            'mismatch_count'        => $debtMismatches->count(),
            'reconciliation_count'  => $reconSummary['total_reconciliations'],
        ];

        return view('admin.finance.verification-dashboard', compact(
            'healthScore', 'topDebtors', 'outstandingBals',
            'duplicatePayments', 'missingTx', 'debtMismatches',
            'reconSummary', 'stats'
        ));
    }

    // ── ZOHO COMPARISON ───────────────────────────────────────

    public function zohoComparison(Request $request)
    {
        return view('admin.finance.zoho-comparison', [
            'result'   => null,
            'dateFrom' => $request->date_from ?? now()->startOfMonth()->toDateString(),
            'dateTo'   => $request->date_to   ?? now()->toDateString(),
        ]);
    }

    public function zohoCompare(Request $request)
    {
        $request->validate([
            'zoho_file'  => 'required|file|mimes:csv,txt',
            'date_from'  => 'required|date',
            'date_to'    => 'required|date|after_or_equal:date_from',
        ]);

        $service = new ZohoComparisonService();
        $zohoRows = $service->importZohoCsv($request->file('zoho_file'));
        $result   = $service->compare($zohoRows, $request->date_from, $request->date_to);

        return view('admin.finance.zoho-comparison', [
            'result'   => $result,
            'dateFrom' => $request->date_from,
            'dateTo'   => $request->date_to,
        ]);
    }
}
