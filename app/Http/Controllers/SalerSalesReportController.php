<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\DesignTask;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SalerSalesReportController extends Controller
{
    private function resolveDateRange(string $period, ?string $startDate, ?string $endDate): array
    {
        return match($period) {
            'today'     => [now()->startOfDay(), now()->endOfDay(), 'Today — ' . now()->format('M d, Y')],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay(), 'Yesterday — ' . now()->subDay()->format('M d, Y')],
            'week'      => [now()->startOfWeek(), now()->endOfWeek(), 'This Week'],
            'month'     => [now()->startOfMonth(), now()->endOfMonth(), 'This Month — ' . now()->format('F Y')],
            'year'      => [now()->startOfYear(), now()->endOfYear(), 'This Year — ' . now()->format('Y')],
            'custom'    => [
                $startDate ? Carbon::parse($startDate)->startOfDay() : now()->startOfMonth(),
                $endDate   ? Carbon::parse($endDate)->endOfDay()     : now()->endOfDay(),
                ($startDate ? Carbon::parse($startDate)->format('M d, Y') : '') . ' – ' . ($endDate ? Carbon::parse($endDate)->format('M d, Y') : ''),
            ],
            default     => [now()->startOfMonth(), now()->endOfMonth(), 'This Month — ' . now()->format('F Y')],
        };
    }

    private function buildSummary(\Illuminate\Support\Collection $rows): array
    {
        // Group rows by source; fall back to lead_type then 'Other'
        $groups = $rows->groupBy(fn($r) => $r->source ?: 'other');

        $summary = [];
        foreach ($groups as $source => $groupRows) {
            $paid   = $groupRows->filter(fn($r) => $r->amount_paid > 0);
            $unpaid = $groupRows->filter(fn($r) => !($r->amount_paid > 0));
            $summary[] = [
                'label'       => strtoupper(str_replace('_', ' ', $source)),
                'total'       => $groupRows->count(),
                'paid_count'  => $paid->count(),
                'unpaid_count'=> $unpaid->count(),
                'amount_paid' => $paid->sum('amount_paid'),
            ];
        }

        return $summary;
    }

    private function buildRows(int $salerId, Carbon $from, Carbon $to): \Illuminate\Support\Collection
    {
        // Get all leads assigned to this saler within the period
        $leads = Lead::where('assigned_seller_id', $salerId)
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get all customers matching lead phones for this saler's tasks
        $phones = $leads->pluck('phone')->filter()->unique()->values();

        $tasksByPhone = DesignTask::where('saler_id', $salerId)
            ->whereHas('customer', fn($q) => $q->whereIn('phone', $phones))
            ->with('customer')
            ->latest()
            ->get()
            ->groupBy(fn($t) => $t->customer->phone ?? '');

        return $leads->map(function ($lead) use ($tasksByPhone) {
            $tasks = $tasksByPhone->get($lead->phone, collect());
            $latestTask = $tasks->first();

            return (object) [
                'client_name'       => $lead->customer_name,
                'phone'             => $lead->phone,
                'source'            => $lead->source,
                'follow_up_date'    => $lead->last_follow_up_date ?? $lead->follow_up_date,
                'follow_up_status'  => $lead->status,
                'product_asked'     => $lead->product_requested,
                'product_ordered'   => $latestTask ? $latestTask->title : null,
                'amount_paid'       => $latestTask ? $latestTask->amount_paid : null,
                'work_status'       => $latestTask ? $latestTask->status : null,
                'delivery_status'   => $latestTask ? $latestTask->delivery_status : null,
                'feedback'          => $latestTask ? ($latestTask->delivery_notes ?: null) : null,
                'task_count'        => $tasks->count(),
            ];
        });
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $allSalers = \App\Models\User::whereIn('role', ['saler', 'admin', 'manager'])->orderBy('name')->get();

        if ($user->role === 'saler') {
            $saler = $user;
        } elseif ($request->filled('saler_id')) {
            $saler = \App\Models\User::find($request->saler_id) ?: $user;
        } else {
            $saler = $user;
        }

        $period = $request->get('period', 'month');

        [$from, $to, $periodLabel] = $this->resolveDateRange(
            $period,
            $request->get('start_date'),
            $request->get('end_date')
        );

        $rows    = $this->buildRows($saler->id, $from, $to);
        $summary = $this->buildSummary($rows);
        $notice  = $request->get('notice', '');

        return view('admin.saler.sales-report', compact(
            'rows', 'summary', 'notice', 'period', 'periodLabel', 'from', 'to', 'saler', 'allSalers'
        ));
    }

    public function print(Request $request)
    {
        $user = Auth::user();
        $allSalers = \App\Models\User::whereIn('role', ['saler', 'admin', 'manager'])->orderBy('name')->get();

        if ($user->role === 'saler') {
            $saler = $user;
        } elseif ($request->filled('saler_id')) {
            $saler = \App\Models\User::find($request->saler_id) ?: $user;
        } else {
            $saler = $user;
        }

        $period = $request->get('period', 'month');

        [$from, $to, $periodLabel] = $this->resolveDateRange(
            $period,
            $request->get('start_date'),
            $request->get('end_date')
        );

        $rows    = $this->buildRows($saler->id, $from, $to);
        $summary = $this->buildSummary($rows);
        $notice  = $request->get('notice', '');
        $summaryTotals = [
            'total_leads'   => $rows->count(),
            'paid_count'    => $rows->filter(fn($row) => $row->amount_paid > 0)->count(),
            'unpaid_count'  => $rows->filter(fn($row) => !($row->amount_paid > 0))->count(),
            'amount_paid'   => $rows->sum('amount_paid'),
        ];

        return view('admin.saler.sales-report-print', compact(
            'rows', 'summary', 'summaryTotals', 'notice', 'period', 'periodLabel', 'from', 'to', 'saler', 'allSalers'
        ));
    }

    private function resolveSalerForRequest(Request $request): \App\Models\User
    {
        $user = Auth::user();

        if ($user->role === 'saler') {
            return $user;
        }

        if ($request->filled('saler_id')) {
            return \App\Models\User::find($request->saler_id) ?: $user;
        }

        return $user;
    }

    public function pdf(Request $request)
    {
        $saler  = $this->resolveSalerForRequest($request);
        $period = $request->get('period', 'month');

        [$from, $to, $periodLabel] = $this->resolveDateRange(
            $period,
            $request->get('start_date'),
            $request->get('end_date')
        );

        $rows    = $this->buildRows($saler->id, $from, $to);
        $summary = $this->buildSummary($rows);
        $notice  = $request->get('notice', '');

        $title    = 'Seller Activity Report — ' . $saler->name;
        $dateFrom = $from;
        $dateTo   = $to;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.exports.saler-sales-report', compact(
            'rows', 'summary', 'notice', 'periodLabel', 'saler', 'title', 'dateFrom', 'dateTo'
        ))->setPaper('a3', 'landscape');

        $filename = 'saler-sales-report-' . \Illuminate\Support\Str::slug($saler->name) . '-' . $from->format('Y-m-d') . '-to-' . $to->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function excel(Request $request)
    {
        $saler  = $this->resolveSalerForRequest($request);
        $period = $request->get('period', 'month');

        [$from, $to] = $this->resolveDateRange(
            $period,
            $request->get('start_date'),
            $request->get('end_date')
        );

        $rows    = $this->buildRows($saler->id, $from, $to);
        $summary = $this->buildSummary($rows);

        $activityRows = $rows->map(function ($row, $i) {
            return [
                $i + 1,
                $row->client_name,
                $row->phone,
                $row->source,
                $row->follow_up_date ? \Carbon\Carbon::parse($row->follow_up_date)->format('d M Y') : null,
                $row->follow_up_status,
                $row->product_asked,
                $row->product_ordered,
                $row->amount_paid,
                $row->work_status,
                $row->delivery_status,
                $row->feedback,
            ];
        })->all();

        $summaryRows = array_map(function ($grp) {
            return [$grp['label'], $grp['total'], $grp['paid_count'], $grp['unpaid_count'], $grp['amount_paid']];
        }, $summary);

        $sheets = [
            new \App\Exports\SimpleArrayExport($activityRows, [
                '#', 'Name of Client', 'Phone Number', 'Source of Lead', 'Follow Up Date', 'Follow Up Status',
                'Product Asked', 'Product Ordered', 'Amount Paid', 'Work Status', 'Delivery Status', 'After Sale Feedback',
            ], 'Seller Activity'),
            new \App\Exports\SimpleArrayExport($summaryRows, [
                'Source', 'Total', 'Paid', 'Unpaid', 'Amount Paid (TZS)',
            ], 'Summary by Source'),
        ];

        $filename = 'saler-sales-report-' . \Illuminate\Support\Str::slug($saler->name) . '-' . $from->format('Y-m-d') . '-to-' . $to->format('Y-m-d') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\MultiSheetExport($sheets),
            $filename
        );
    }
}
