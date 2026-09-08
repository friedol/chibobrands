<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SalesReportExport;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\CustomerSource;
use App\Models\DesignTask;
use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\Payment;
use App\Models\SalesProgram;
use App\Models\SalesTarget;
use App\Models\User;
use App\Support\Concerns\ResolvesSalesTargets;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SalesReportController extends Controller
{
    use ResolvesSalesTargets;

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
        $target    = $this->getTarget($sellerId, $from, $to, $period);
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

        // ── Lead source breakdown (dynamic from CustomerSource) ───────────
        $allSources  = CustomerSource::active()->pluck('name');
        $sourceStats = [];

        foreach ($allSources as $src) {
            $srcQuery = Lead::where('source', $src)->whereBetween('created_at', [$from, $to]);
            if ($sellerId) $srcQuery->where('assigned_seller_id', $sellerId);

            $total   = $srcQuery->count();
            $paid    = (clone $srcQuery)->where('status', 'converted')->count();
            $unpaid  = (clone $srcQuery)->where('status', 'pending')->count();

            $srcRevQuery = Payment::activeFinance()
                ->whereBetween('date', [$from, $to])
                ->whereHas('customer.leads', fn($q) => $q->where('source', $src));
            if ($sellerId) $srcRevQuery->where('seller_id', $sellerId);
            $revenue = $srcRevQuery->sum('amount');

            if ($total > 0 || $revenue > 0) {
                $entry = compact('total', 'paid', 'unpaid', 'revenue');

                // Instagram: campaign sub-breakdown
                if (strtolower($src) === 'instagram') {
                    $campaigns = Campaign::select('id', 'title')->orderBy('title')->get();
                    $campaignBreakdown = [];
                    foreach ($campaigns as $camp) {
                        $cq    = Lead::where('source', $src)->where('campaign_id', $camp->id)->whereBetween('created_at', [$from, $to]);
                        if ($sellerId) $cq->where('assigned_seller_id', $sellerId);
                        $cTotal = $cq->count();
                        if ($cTotal > 0) {
                            $campaignBreakdown[] = ['name' => $camp->title, 'total' => $cTotal];
                        }
                    }
                    $entry['campaigns'] = $campaignBreakdown;
                }

                // Inside Programs: program sub-breakdown
                if (strtolower($src) === 'inside programs') {
                    $programs = SalesProgram::active()->get();
                    $programBreakdown = [];
                    foreach ($programs as $prog) {
                        $pq    = Lead::where('source', $src)->where('program_id', $prog->id)->whereBetween('created_at', [$from, $to]);
                        if ($sellerId) $pq->where('assigned_seller_id', $sellerId);
                        $pTotal = $pq->count();
                        if ($pTotal > 0) {
                            $programBreakdown[] = ['name' => $prog->name, 'total' => $pTotal];
                        }
                    }
                    $entry['programs'] = $programBreakdown;
                }

                $sourceStats[$src] = $entry;
            }
        }

        // ── New & repeated customer counts ─────────────────────────────────
        $newCustomerCount = Customer::newCustomers()->whereBetween('created_at', [$from, $to])->count();
        $repCustomerCount = Customer::repeatedCustomers()
            ->whereHas('designTasks', fn($q) => $q->whereBetween('created_at', [$from, $to]))
            ->count();

        // ── Unified Follow-up Activities & Comments ───────────────────────
        $activities = collect();

        // 1. Fetch Lead Follow-ups
        $leadFollowUpsQuery = LeadFollowUp::with(['lead', 'user'])
            ->whereBetween('created_at', [$from, $to]);
        if ($sellerId) {
            $leadFollowUpsQuery->where('user_id', $sellerId);
        }
        foreach ($leadFollowUpsQuery->get() as $lfu) {
            $activities->push([
                'date' => $lfu->created_at,
                'type' => 'Lead Follow-Up',
                'contact_name' => $lfu->lead?->customer_name ?? 'Unknown Lead',
                'phone' => $lfu->lead?->phone ?? '—',
                'channel' => '—',
                'notes' => $lfu->notes,
                'seller_name' => $lfu->user?->name ?? '—'
            ]);
        }

        // 2. Fetch Customer Follow-ups
        $custFollowUpsQuery = \App\Models\CustomerFollowUp::with(['customer', 'user'])
            ->whereBetween('created_at', [$from, $to]);
        if ($sellerId) {
            $custFollowUpsQuery->where('user_id', $sellerId);
        }
        foreach ($custFollowUpsQuery->get() as $cfu) {
            $activities->push([
                'date' => $cfu->created_at,
                'type' => 'Customer Follow-Up',
                'contact_name' => $cfu->customer?->name ?? 'Unknown Customer',
                'phone' => $cfu->customer?->phone ?? '—',
                'channel' => $cfu->action ?? '—',
                'notes' => $cfu->notes,
                'seller_name' => $cfu->user?->name ?? '—'
            ]);
        }

        $activities = $activities->sortByDesc('date')->values()->all();

        // ── Detailed Registered Leads List ────────────────────────────────
        $regLeadsQuery = Lead::with('seller')->whereBetween('created_at', [$from, $to]);
        if ($sellerId) {
            $regLeadsQuery->where('assigned_seller_id', $sellerId);
        }
        $registeredLeadsList = $regLeadsQuery->orderBy('created_at', 'desc')->get();

        // ── Detailed Paid Clients List ───────────────────────────────────
        $paidClQuery = DesignTask::with(['customer', 'saler'])
            ->whereBetween('created_at', [$from, $to])
            ->where('balance', '<=', 0);
        if ($sellerId) {
            $paidClQuery->where('saler_id', $sellerId);
        }
        $paidClientsList = $paidClQuery->orderBy('created_at', 'desc')->get();

        return compact(
            'totalLeads', 'convertedLeads', 'pendingLeads', 'notInterested',
            'followUpsDone', 'totalRevenue', 'newRevenue', 'repRevenue',
            'paidTasks', 'unpaidTasks', 'totalBilled',
            'sourceStats', 'newCustomerCount', 'repCustomerCount', 'activities',
            'registeredLeadsList', 'paidClientsList'
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

    private function getTarget(?int $sellerId, Carbon $from, Carbon $to, ?string $period = null): ?object
    {
        if (!$sellerId) return null;

        [$amount, ] = $this->resolveSalesTarget(
            SalesTarget::where('seller_id', $sellerId),
            $from, $to, $period
        );

        return $amount > 0 ? (object) ['target_amount' => $amount] : null;
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
        $target     = $this->getTarget($sellerId, $from, $to, $period);

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
        $target     = $this->getTarget($sellerId, $from, $to, $period);
        $ranking    = in_array($user->role, ['admin', 'super_admin', 'accountant']) ? $this->buildSellerRanking($from, $to) : [];

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
