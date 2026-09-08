<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerSource;
use App\Models\Department;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerReportController extends Controller
{
    private function resolveDateRange(Request $request): array
    {
        $from = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : now()->startOfMonth();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : now()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    private function applyCustomerFilters($query, Request $request)
    {
        if ($request->filled('branch_id')) {
            $query->where('customers.branch_id', (int) $request->branch_id);
        }

        if ($request->filled('salesperson_id')) {
            $query->where('customers.added_by', (int) $request->salesperson_id);
        }

        if ($request->filled('source')) {
            $query->where('customers.customer_source', $request->source);
        }

        return $query;
    }

    private function buildReportData(Request $request): array
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);

        $filteredCustomers = $this->applyCustomerFilters(Customer::query(), $request);

        // 1) New Customers Report
        $newCustomersQuery = (clone $filteredCustomers)
            ->whereBetween('customers.created_at', [$startDate, $endDate]);

        $newCustomers = $newCustomersQuery->latest('customers.created_at')->get([
            'id',
            'name',
            'phone',
            'branch_id',
            'added_by',
            'customer_source',
            'created_at',
        ])->load([
            'branch:id,name',
            'addedBy:id,name',
        ]);

        // 2) Returning Customers Report
        $returningCustomers = (clone $filteredCustomers)
            ->where(function ($q) {
                $q->where('purchase_count', '>=', 2)
                    ->orWhere('total_orders', '>=', 2)
                    ->orWhere('is_repeated', true);
            })
            ->orderByDesc(DB::raw('COALESCE(total_spent, 0)'))
            ->get([
                'id',
                'name',
                'phone',
                'purchase_count',
                'total_orders',
                'last_order_date',
                'total_spent',
            ]);

        $customerIds = (clone $filteredCustomers)->pluck('id');

        $paymentsByCustomer = Payment::activeFinance()
            ->whereIn('customer_id', $customerIds)
            ->select('customer_id', DB::raw('SUM(amount) as total_payments'))
            ->groupBy('customer_id')
            ->pluck('total_payments', 'customer_id');

        // 3) Customer Contribution Report
        $contributionRows = (clone $filteredCustomers)
            ->orderByDesc(DB::raw('COALESCE(total_spent, 0)'))
            ->get([
                'id',
                'name',
                'phone',
                'total_spent',
            ])
            ->map(function ($customer) use ($paymentsByCustomer) {
                $totalSales = (float) ($customer->total_spent ?? 0);
                $totalPayments = (float) ($paymentsByCustomer[$customer->id] ?? 0);

                return [
                    'customer' => $customer,
                    'total_sales_amount' => $totalSales,
                    'total_payments' => $totalPayments,
                    'outstanding_debt' => max($totalSales - $totalPayments, 0),
                ];
            });

        // 4) Customer Source Report
        $sourceRows = (clone $filteredCustomers)
            ->select(
                DB::raw("COALESCE(customers.customer_source, 'Unspecified') as source"),
                DB::raw('COUNT(customers.id) as customers_count'),
                DB::raw('SUM(COALESCE(customers.total_spent, 0)) as sales_generated')
            )
            ->groupBy('source')
            ->orderByDesc('customers_count')
            ->get();

        // 5) Customer Growth Report
        $growthData = [];
        $cursor = $startDate->copy()->startOfMonth();
        $limit = $endDate->copy()->startOfMonth();

        while ($cursor->lte($limit)) {
            $monthStart = $cursor->copy()->startOfMonth();
            $monthEnd = $cursor->copy()->endOfMonth();

            $count = $this->applyCustomerFilters(Customer::query(), $request)
                ->whereBetween('customers.created_at', [$monthStart, $monthEnd])
                ->count();

            $growthData[] = [
                'month_key' => $cursor->format('Y-m'),
                'month_label' => $cursor->format('M Y'),
                'new_registrations' => $count,
            ];

            $cursor->addMonth();
        }

        $branchGrowth = $this->applyCustomerFilters(Customer::query(), $request)
            ->whereBetween('customers.created_at', [$startDate, $endDate])
            ->leftJoin('departments', 'customers.branch_id', '=', 'departments.id')
            ->select(
                DB::raw("COALESCE(departments.name, 'Unassigned') as branch_name"),
                DB::raw('COUNT(customers.id) as customers_count')
            )
            ->groupBy('branch_name')
            ->orderByDesc('customers_count')
            ->get();

        // 6) Customer Retention Report
        $retentionPool = (clone $filteredCustomers)->get([
            'id',
            'purchase_count',
            'total_orders',
            'is_repeated',
            'last_order_date',
        ]);

        $cutoff = now()->subDays(90);

        $returningCount = $retentionPool->filter(function ($c) {
            return (int) ($c->purchase_count ?? 0) >= 2
                || (int) ($c->total_orders ?? 0) >= 2
                || (bool) $c->is_repeated;
        })->count();

        $activeCount = $retentionPool->filter(function ($c) use ($cutoff) {
            return $c->last_order_date && Carbon::parse($c->last_order_date)->gte($cutoff);
        })->count();

        $lostCount = max($retentionPool->count() - $activeCount, 0);

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'newCustomers' => $newCustomers,
            'returningCustomers' => $returningCustomers,
            'contributionRows' => $contributionRows,
            'sourceRows' => $sourceRows,
            'growthData' => $growthData,
            'branchGrowth' => $branchGrowth,
            'retentionSummary' => [
                'active' => $activeCount,
                'lost' => $lostCount,
                'returning' => $returningCount,
            ],
        ];
    }

    public function index(Request $request)
    {
        $reportData = $this->buildReportData($request);

        $branches = Department::orderBy('name')->get(['id', 'name']);
        $salespeople = User::whereIn('role', ['saler', 'admin', 'super_admin'])
            ->orderBy('name')
            ->get(['id', 'name']);
        $sources = CustomerSource::active()->pluck('name');

        return view('admin.reports.customers', array_merge($reportData, [
            'branches' => $branches,
            'salespeople' => $salespeople,
            'sources' => $sources,
            'filters' => [
                'date_from' => $request->input('date_from', $reportData['startDate']->format('Y-m-d')),
                'date_to' => $request->input('date_to', $reportData['endDate']->format('Y-m-d')),
                'branch_id' => $request->input('branch_id', ''),
                'salesperson_id' => $request->input('salesperson_id', ''),
                'source' => $request->input('source', ''),
            ],
        ]));
    }

    public function print(Request $request)
    {
        $reportData = $this->buildReportData($request);

        return view('admin.reports.customers-print', array_merge($reportData, [
            'filters' => [
                'date_from' => $request->input('date_from', $reportData['startDate']->format('Y-m-d')),
                'date_to' => $request->input('date_to', $reportData['endDate']->format('Y-m-d')),
                'branch_id' => $request->input('branch_id', ''),
                'salesperson_id' => $request->input('salesperson_id', ''),
                'source' => $request->input('source', ''),
            ],
        ]));
    }

    public function pdf(Request $request)
    {
        $reportData = $this->buildReportData($request);

        $title    = 'Customer Reporting & Analytics';
        $dateFrom = $reportData['startDate'];
        $dateTo   = $reportData['endDate'];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.exports.customer-report', array_merge(
            $reportData,
            ['title' => $title, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo]
        ))->setPaper('a4', 'portrait');

        $filename = 'customer-report-' . $dateFrom->format('Y-m-d') . '-to-' . $dateTo->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function excel(Request $request)
    {
        $reportData = $this->buildReportData($request);

        $newCustomersRows = $reportData['newCustomers']->map(function ($customer) {
            return [
                $customer->name,
                $customer->phone,
                $customer->branch?->name ?? 'Unassigned',
                $customer->addedBy?->name ?? 'Unassigned',
                $customer->customer_source ?? 'Unspecified',
                optional($customer->created_at)->format('d M Y H:i'),
            ];
        })->all();

        $returningCustomersRows = $reportData['returningCustomers']->map(function ($customer) {
            return [
                $customer->name,
                max((int) $customer->purchase_count, (int) $customer->total_orders),
                $customer->last_order_date ? \Carbon\Carbon::parse($customer->last_order_date)->format('d M Y') : 'N/A',
                (float) $customer->total_spent,
            ];
        })->all();

        $contributionRows = $reportData['contributionRows']->map(function ($row) {
            return [
                $row['customer']->name,
                $row['total_sales_amount'],
                $row['total_payments'],
                $row['outstanding_debt'],
            ];
        })->all();

        $sourceRows = $reportData['sourceRows']->map(function ($row) {
            return [
                $row->source,
                $row->customers_count,
                (float) $row->sales_generated,
            ];
        })->all();

        $growthRows = collect($reportData['growthData'])->map(function ($month) {
            return [$month['month_label'], $month['new_registrations']];
        })->all();

        $branchGrowthRows = $reportData['branchGrowth']->map(function ($branch) {
            return [$branch->branch_name, $branch->customers_count];
        })->all();

        $retentionRows = [
            ['Active Customers', $reportData['retentionSummary']['active']],
            ['Lost Customers', $reportData['retentionSummary']['lost']],
            ['Returning Customers', $reportData['retentionSummary']['returning']],
        ];

        $sheets = [
            new \App\Exports\SimpleArrayExport($newCustomersRows, ['Name', 'Phone', 'Branch', 'Salesperson', 'Source', 'Registered On'], 'New Customers'),
            new \App\Exports\SimpleArrayExport($returningCustomersRows, ['Customer Name', 'Number of Purchases', 'Last Purchase Date', 'Total Contribution (TZS)'], 'Returning Customers'),
            new \App\Exports\SimpleArrayExport($contributionRows, ['Customer Name', 'Total Sales Amount', 'Total Payments', 'Outstanding Debt'], 'Contribution'),
            new \App\Exports\SimpleArrayExport($sourceRows, ['Source', 'Number of Customers', 'Sales Generated'], 'Source Breakdown'),
            new \App\Exports\SimpleArrayExport($growthRows, ['Month', 'New Registrations'], 'Growth'),
            new \App\Exports\SimpleArrayExport($branchGrowthRows, ['Branch', 'New Registrations'], 'Branch Comparison'),
            new \App\Exports\SimpleArrayExport($retentionRows, ['Metric', 'Value'], 'Retention'),
        ];

        $filename = 'customer-report-' . $reportData['startDate']->format('Y-m-d') . '-to-' . $reportData['endDate']->format('Y-m-d') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\MultiSheetExport($sheets),
            $filename
        );
    }
}
