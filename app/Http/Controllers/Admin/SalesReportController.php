<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SalesReportExport;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\DesignTask;
use App\Models\User;
use App\Models\SalesTarget;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SalesReportController extends Controller
{
    // ── Date range resolver ────────────────────────────────────────────────

    private function resolveRange(Request $request): array
    {
        $period = $request->get('period', 'today');

        return match($period) {
            'today'     => [now()->startOfDay(),  now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week'      => [now()->startOfWeek(),  now()->endOfWeek()],
            'month'     => [now()->startOfMonth(), now()->endOfMonth()],
            'year'      => [now()->startOfYear(),  now()->endOfYear()],
            'custom'    => [
                $request->filled('date_from') ? Carbon::parse($request->date_from)->startOfDay() : now()->startOfDay(),
                $request->filled('date_to')   ? Carbon::parse($request->date_to)->endOfDay()     : now()->endOfDay(),
            ],
            default => [now()->startOfDay(), now()->endOfDay()],
        };
    }

    // ── Main report index ──────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user   = auth()->user();
        $period = $request->get('period', 'today');
        [$from, $to] = $this->resolveRange($request);

        // Admins can view any seller; salers only see themselves
        $sellers = collect();
        if (in_array($user->role, ['admin', 'super_admin', 'accountant'])) {
            $sellers = User::whereIn('role', ['saler', 'admin', 'super_admin'])->get();
        }

        $sellerId = in_array($user->role, ['admin', 'super_admin', 'accountant'])
            ? ($request->filled('saler_id') ? (int) $request->saler_id : null)
            : $user->id;

        $data = $this->buildReportData($from, $to, $sellerId);

        // Sales targets
        $target    = $this->getTarget($sellerId, $from);
        $allSeller = in_array($user->role, ['admin', 'super_admin', 'accountant']) && !$sellerId;

        // Seller ranking (admin only, period-scoped)
        $ranking = [];
        if (in_array($user->role, ['admin', 'super_admin', 'accountant'])) {
            $ranking = $this->buildSellerRanking($from, $to);
        }

        return view('admin.reports.sales-report', compact(
            'data', 'period', 'from', 'to', 'sellers', 'sellerId',
            'target', 'allSeller', 'ranking'
        ));
    }

    // ── Per-seller report data ─────────────────────────────────────────────

    private function buildReportData(Carbon $from, Carbon $to, ?int $sellerId): array
    {
        // ── Leads ──────────────────────────────────────────────────────────
        $leadQuery = Lead::whereBetween('created_at', [$from, $to]);
        if ($sellerId) $leadQuery->where('assigned_seller_id', $sellerId);

        $totalLeads       = $leadQuery->count();
        $convertedLeads   = (clone $leadQuery)->where('status', 'converted')->count();
        $pendingLeads     = (clone $leadQuery)->where('status', 'pending')->count();
        $notInterested    = (clone $leadQuery)->where('status', 'not_interested')->count();

        // ── Follow-ups done in period ──────────────────────────────────────
        $followUpQuery = LeadFollowUp::whereBetween('created_at', [$from, $to]);
        if ($sellerId) {
            $followUpQuery->whereHas('lead', fn($q) => $q->where('assigned_seller_id', $sellerId));
        }
        $followUpsDone = $followUpQuery->count();

        // ── Revenue ────────────────────────────────────────────────────────
        $paymentQuery = Payment::activeFinance()->whereBetween('date', [$from, $to]);
        if ($sellerId) $paymentQuery->where('seller_id', $sellerId);

        $totalRevenue = $paymentQuery->sum('amount');

        // ── New vs Repeated customer revenue ──────────────────────────────
        $newCustIds = Customer::newCustomers()->pluck('id');
        $repCustIds = Customer::repeatedCustomers()->pluck('id');

        $newRevenue = (clone $paymentQuery)->whereIn('customer_id', $newCustIds)->sum('amount');
        $repRevenue = (clone $paymentQuery)->whereIn('customer_id', $repCustIds)->sum('amount');

        // ── Design tasks (paid vs unpaid) ──────────────────────────────────
        $taskQuery = DesignTask::whereBetween('created_at', [$from, $to]);
        if ($sellerId) $taskQuery->where('saler_id', $sellerId);

        $paidTasks   = (clone $taskQuery)->where('balance', '<=', 0)->count();
        $unpaidTasks = (clone $taskQuery)->where('balance', '>', 0)->count();
        $totalBilled = $taskQuery->sum('price');

        // ── Lead source breakdown ─────────────────────────────────────────
        $sources = ['promo', 'instagram', 'follow_up', 'referral', 'walk_in', 'whatsapp', 'other'];
        $sourceStats = [];

        foreach ($sources as $src) {
            $srcQuery = Lead::where('source', $src)->whereBetween('created_at', [$from, $to]);
            if ($sellerId) $srcQuery->where('assigned_seller_id', $sellerId);

            $total     = $srcQuery->count();
            $paid      = (clone $srcQuery)->where('status', 'converted')->count();
            $unpaid    = (clone $srcQuery)->where('status', 'pending')->count();

            // Revenue from this source (payments for tasks whose lead source matches)
            $srcRevQuery = Payment::activeFinance()
                ->whereBetween('date', [$from, $to])
                ->whereHas('designTask.lead', fn($q) => $q->where('source', $src));
            if ($sellerId) $srcRevQuery->where('seller_id', $sellerId);
            $revenue = $srcRevQuery->sum('amount');

            if ($total > 0 || $revenue > 0) {
                $sourceStats[$src] = compact('total', 'paid', 'unpaid', 'revenue');
            }
        }

        // ── New & repeated customer counts ─────────────────────────────────
        $newCustomerCount = Customer::newCustomers()->whereBetween('created_at', [$from, $to])->count();
        $repCustomerCount = Customer::repeatedCustomers()
            ->whereHas('designTasks', fn($q) => $q->whereBetween('created_at', [$from, $to]))
            ->count();

        return compact(
            'totalLeads', 'convertedLeads', 'pendingLeads', 'notInterested',
            'followUpsDone', 'totalRevenue', 'newRevenue', 'repRevenue',
            'paidTasks', 'unpaidTasks', 'totalBilled',
            'sourceStats', 'newCustomerCount', 'repCustomerCount'
        );
    }

    // ── Seller ranking ─────────────────────────────────────────────────────

    private function buildSellerRanking(Carbon $from, Carbon $to): array
    {
        return User::whereIn('role', ['saler', 'admin', 'super_admin'])
            ->get()
            ->map(function (User $seller) use ($from, $to) {
                $revenue  = Payment::activeFinance()
                    ->where('seller_id', $seller->id)
                    ->whereBetween('date', [$from, $to])
                    ->sum('amount');
                $leads    = Lead::where('assigned_seller_id', $seller->id)
                    ->whereBetween('created_at', [$from, $to])
                    ->count();
                $converted = Lead::where('assigned_seller_id', $seller->id)
                    ->where('status', 'converted')
                    ->whereBetween('created_at', [$from, $to])
                    ->count();
                $rate = $leads > 0 ? round(($converted / $leads) * 100, 1) : 0;

                return [
                    'name'      => $seller->name,
                    'id'        => $seller->id,
                    'revenue'   => (float) $revenue,
                    'leads'     => $leads,
                    'converted' => $converted,
                    'rate'      => $rate,
                ];
            })
            ->filter(fn($s) => $s['leads'] > 0 || $s['revenue'] > 0)
            ->sortByDesc('revenue')
            ->values()
            ->toArray();
    }

    // ── Sales target helper ────────────────────────────────────────────────

    private function getTarget(?int $sellerId, Carbon $from): ?object
    {
        if (!$sellerId) return null;

        return SalesTarget::where('user_id', $sellerId)
            ->where('month', $from->month)
            ->where('year', $from->year)
            ->first();
    }

    // ── PDF export ─────────────────────────────────────────────────────────

    public function exportPdf(Request $request)
    {
        $user     = auth()->user();
        $period   = $request->get('period', 'today');
        [$from, $to] = $this->resolveRange($request);

        $sellerId = in_array($user->role, ['admin', 'super_admin', 'accountant'])
            ? ($request->filled('saler_id') ? (int) $request->saler_id : null)
            : $user->id;

        $sellerName = $sellerId ? (User::find($sellerId)?->name ?? 'All Sellers') : 'All Sellers';
        $data       = $this->buildReportData($from, $to, $sellerId);
        $target     = $this->getTarget($sellerId, $from);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.sales-report-pdf', compact(
            'data', 'period', 'from', 'to', 'sellerName', 'target'
        ))->setPaper('a4', 'portrait');

        $filename = 'sales-report-' . ($sellerId ?? 'all') . '-' . $from->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    // ── Print view ─────────────────────────────────────────────────────────

    public function print(Request $request)
    {
        $user   = auth()->user();
        $period = $request->get('period', 'today');
        [$from, $to] = $this->resolveRange($request);

        $sellerId = in_array($user->role, ['admin', 'super_admin', 'accountant'])
            ? ($request->filled('saler_id') ? (int) $request->saler_id : null)
            : $user->id;

        $sellerName = $sellerId ? (User::find($sellerId)?->name ?? 'All Sellers') : 'All Sellers';
        $data       = $this->buildReportData($from, $to, $sellerId);
        $target     = $this->getTarget($sellerId, $from);
        $ranking    = in_array($user->role, ['admin', 'super_admin']) ? $this->buildSellerRanking($from, $to) : [];

        return view('admin.reports.sales-report-print', compact(
            'data', 'period', 'from', 'to', 'sellerName', 'target', 'ranking'
        ));
    }

    // ── Excel export ────────────────────────────────────────────────────────

    public function exportExcel(Request $request)
    {
        $user   = auth()->user();
        $period = $request->get('period', 'today');
        [$from, $to] = $this->resolveRange($request);

        $sellerId = in_array($user->role, ['admin', 'super_admin', 'accountant'])
            ? ($request->filled('saler_id') ? (int) $request->saler_id : null)
            : $user->id;

        $sellerName = $sellerId ? (User::find($sellerId)?->name ?? 'All Sellers') : 'All Sellers';
        $data       = $this->buildReportData($from, $to, $sellerId);
        $ranking    = in_array($user->role, ['admin', 'super_admin', 'accountant'])
            ? $this->buildSellerRanking($from, $to)
            : [];

        $filename = 'sales-report-' . str_replace(' ', '-', strtolower($sellerName))
                  . '-' . $from->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new SalesReportExport($data, $ranking, $from, $to, $period, $sellerName),
            $filename
        );
    }
}
