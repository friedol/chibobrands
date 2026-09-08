<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessSmsCampaign;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Region;
use App\Models\SmsCampaign;
use App\Models\SmsCampaignRecipient;
use App\Models\User;
use App\Models\MessageTemplate;
use App\Services\SmsApiService;
use App\Services\SmsAutoSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class BulkSmsController extends Controller
{
    /**
     * Show the main Bulk SMS module dashboard.
     */
    public function index()
    {
        $regions = Schema::hasTable('regions') 
            ? Region::orderBy('region_name')->get() 
            : collect();
            
        $salesReps = User::whereIn('role', ['saler', 'admin', 'super_admin'])->get();
        
        $businessTypes = Customer::whereNotNull('business_type')
            ->distinct()
            ->pluck('business_type')
            ->filter();

        $stats = $this->calculateStats();

        $recentCampaigns = SmsCampaign::with('sender')
            ->latest()
            ->paginate(20);

        $templates = MessageTemplate::where('is_active', true)->latest()->get();

        $autoSettings = app(SmsAutoSettingsService::class)->getAll();

        return view('admin.bulk-sms.index', compact(
            'regions',
            'salesReps',
            'businessTypes',
            'stats',
            'recentCampaigns',
            'templates',
            'autoSettings'
        ));
    }

    /**
     * Filter & search customers (and leads) dynamically.
     */
    public function getCustomers(Request $request)
    {
        $selectionType = $request->input('selection_type', 'all');

        // ── Completed Customers filter ──────────────────────────────────
        if ($selectionType === 'completed_customers') {
            [$dateFrom, $dateTo] = $this->resolveDateRange($request, 'order');
            $query = Customer::whereNotNull('last_order_date')
                ->whereNotNull('phone')->where('phone', '!=', '');
            if ($dateFrom) $query->whereDate('last_order_date', '>=', $dateFrom);
            if ($dateTo)   $query->whereDate('last_order_date', '<=', $dateTo);

            $totalCount = (clone $query)->count();
            $rows = $query->select(['id', 'name', 'phone', 'company_name', 'business_type', 'last_order_date'])
                ->limit(100)->get();
            return response()->json(['success' => true, 'total_count' => $totalCount, 'customers' => $rows]);
        }

        // ── Imported Leads filter ───────────────────────────────────────
        if ($selectionType === 'imported_leads') {
            [$dateFrom, $dateTo] = $this->resolveDateRange($request, 'lead');
            $query = Lead::whereNotNull('phone')->where('phone', '!=', '');
            if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
            if ($dateTo)   $query->whereDate('created_at', '<=', $dateTo);

            $totalCount = (clone $query)->count();
            $rows = $query->select(['id', 'customer_name as name', 'phone'])->limit(100)->get();
            return response()->json(['success' => true, 'total_count' => $totalCount, 'customers' => $rows]);
        }

        // ── Standard Customer filters ───────────────────────────────────
        $query = Customer::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        if ($selectionType === 'category' && $category = $request->input('category')) {
            if ($category === 'wholesale') {
                $query->where('is_wholesale', true);
            } elseif ($category === 'retail') {
                $query->where('is_wholesale', false);
            } else {
                $query->where('business_type', $category);
            }
        }

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->input('region_id'));
        }

        if ($request->filled('sales_rep_id')) {
            $query->where('added_by', $request->input('sales_rep_id'));
        }

        $buyerStatus = $request->input('buyer_status');
        if ($buyerStatus === 'recent') {
            $query->where('last_order_date', '>=', now()->subDays(30));
        } elseif ($buyerStatus === 'inactive') {
            $query->where(function ($q) {
                $q->whereNull('last_order_date')
                  ->orWhere('last_order_date', '<', now()->subDays(30));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        if ($selectionType === 'individual' && $request->filled('selected_ids')) {
            $ids = is_array($request->input('selected_ids'))
                ? $request->input('selected_ids')
                : explode(',', $request->input('selected_ids'));
            $query->whereIn('id', array_filter($ids));
        }

        $query->whereNotNull('phone')->where('phone', '!=', '');

        $totalCount = (clone $query)->count();
        $customers  = $query->select(['id', 'name', 'phone', 'company_name', 'business_type', 'last_order_date'])
                            ->limit(100)->get();

        return response()->json(['success' => true, 'total_count' => $totalCount, 'customers' => $customers]);
    }

    /**
     * Resolve a date range from period + optional custom from/to fields.
     * $prefix distinguishes field names when multiple date pickers coexist ('order', 'lead').
     */
    private function resolveDateRange(Request $request, string $prefix): array
    {
        $period   = $request->input("{$prefix}_period", 'today');
        $dateFrom = null;
        $dateTo   = null;

        if ($period === 'today') {
            $dateFrom = $dateTo = now()->toDateString();
        } elseif ($period === 'week') {
            $dateFrom = now()->startOfWeek()->toDateString();
            $dateTo   = now()->endOfWeek()->toDateString();
        } elseif ($period === 'custom') {
            $dateFrom = $request->input("{$prefix}_date_from");
            $dateTo   = $request->input("{$prefix}_date_to");
        }

        return [$dateFrom, $dateTo];
    }

    /**
     * Preview SMS campaign before sending.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $customerResponse = $this->getCustomers($request)->getData();
        $totalRecipients = $customerResponse->total_count ?? 0;

        if ($totalRecipients === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No valid recipients selected. Please adjust your filters or selection.'
            ], 422);
        }

        $message = $request->input('message');
        $charCount = mb_strlen($message);
        $unitsPerMessage = ceil($charCount / 160) ?: 1;
        $totalSmsUnits = $totalRecipients * $unitsPerMessage;

        return response()->json([
            'success' => true,
            'total_recipients' => $totalRecipients,
            'char_count' => $charCount,
            'units_per_message' => $unitsPerMessage,
            'total_sms_units' => $totalSmsUnits,
            'message_preview' => $message,
        ]);
    }

    /**
     * Send bulk SMS campaign.
     */
    public function send(Request $request, SmsApiService $smsApiService)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'title' => 'nullable|string|max:255',
        ]);

        $selectionType = $request->input('selection_type', 'all');

        // ── Completed Customers ─────────────────────────────────────────
        if ($selectionType === 'completed_customers') {
            [$dateFrom, $dateTo] = $this->resolveDateRange($request, 'order');
            $q = Customer::whereNotNull('last_order_date')
                ->whereNotNull('phone')->where('phone', '!=', '');
            if ($dateFrom) $q->whereDate('last_order_date', '>=', $dateFrom);
            if ($dateTo)   $q->whereDate('last_order_date', '<=', $dateTo);
            $customers = $q->get(['id', 'name', 'phone']);
        }
        // ── Imported Leads ──────────────────────────────────────────────
        elseif ($selectionType === 'imported_leads') {
            [$dateFrom, $dateTo] = $this->resolveDateRange($request, 'lead');
            $q = Lead::whereNotNull('phone')->where('phone', '!=', '');
            if ($dateFrom) $q->whereDate('created_at', '>=', $dateFrom);
            if ($dateTo)   $q->whereDate('created_at', '<=', $dateTo);
            $customers = $q->get(['id', 'customer_name as name', 'phone']);
        }
        // ── Standard Customer filters ───────────────────────────────────
        else {
            $customerQuery = Customer::query();

            if ($search = $request->input('search')) {
                $customerQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('id', $search);
                });
            }

            if ($selectionType === 'category' && $category = $request->input('category')) {
                if ($category === 'wholesale') {
                    $customerQuery->where('is_wholesale', true);
                } elseif ($category === 'retail') {
                    $customerQuery->where('is_wholesale', false);
                } else {
                    $customerQuery->where('business_type', $category);
                }
            }

            if ($request->filled('region_id')) {
                $customerQuery->where('region_id', $request->input('region_id'));
            }

            if ($request->filled('sales_rep_id')) {
                $customerQuery->where('added_by', $request->input('sales_rep_id'));
            }

            $buyerStatus = $request->input('buyer_status');
            if ($buyerStatus === 'recent') {
                $customerQuery->where('last_order_date', '>=', now()->subDays(30));
            } elseif ($buyerStatus === 'inactive') {
                $customerQuery->where(function ($q) {
                    $q->whereNull('last_order_date')
                      ->orWhere('last_order_date', '<', now()->subDays(30));
                });
            }

            if ($request->filled('date_from')) {
                $customerQuery->whereDate('created_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $customerQuery->whereDate('created_at', '<=', $request->input('date_to'));
            }

            if ($selectionType === 'individual' && $request->filled('selected_ids')) {
                $ids = is_array($request->input('selected_ids'))
                    ? $request->input('selected_ids')
                    : explode(',', $request->input('selected_ids'));
                $customerQuery->whereIn('id', array_filter($ids));
            }

            $customerQuery->whereNotNull('phone')->where('phone', '!=', '');
            $customers = $customerQuery->get(['id', 'name', 'phone']);
        }

        if ($customers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot send SMS: Recipient list is empty.'
            ], 422);
        }

        $message = $request->input('message');
        $charCount = mb_strlen($message);
        $unitsPerMessage = ceil($charCount / 160) ?: 1;
        $totalRecipients = $customers->count();
        $totalSmsUnits = $totalRecipients * $unitsPerMessage;

        DB::beginTransaction();
        try {
            $campaign = SmsCampaign::create([
                'title' => $request->input('title') ?: 'Bulk SMS Campaign ' . now()->format('Y-m-d H:i'),
                'message' => $message,
                'status' => 'processing',
                'total_recipients' => $totalRecipients,
                'sms_units_per_message' => $unitsPerMessage,
                'total_sms_units' => $totalSmsUnits,
                'sent_by' => auth()->id() ?? 1,
            ]);

            $recipientsData = [];
            $seenPhones = [];

            foreach ($customers as $customer) {
                $phone = $smsApiService->formatPhoneNumber($customer->phone);
                if (in_array($phone, $seenPhones)) {
                    continue;
                }
                $seenPhones[] = $phone;

                $recipientsData[] = [
                    'sms_campaign_id' => $campaign->id,
                    'customer_id' => $customer->id,
                    'recipient_name' => $customer->name,
                    'phone_number' => $phone,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach (array_chunk($recipientsData, 500) as $chunk) {
                SmsCampaignRecipient::insert($chunk);
            }

            DB::commit();

            try {
                ProcessSmsCampaign::dispatchSync($campaign);
            } catch (\Exception $e) {
                ProcessSmsCampaign::dispatch($campaign);
            }

            $campaign->refresh();

            Log::info("Bulk SMS Campaign ID {$campaign->id} initiated by User " . (auth()->id() ?? 1), [
                'total_recipients' => $campaign->total_recipients,
                'total_sent' => $campaign->total_sent,
                'total_failed' => $campaign->total_failed,
            ]);

            $totalSent   = (int) $campaign->total_sent;
            $totalFailed = (int) $campaign->total_failed;

            // All failed — surface the real error instead of showing false success
            if ($totalSent === 0 && $totalFailed > 0) {
                $sampleError = $campaign->recipients()
                    ->where('status', 'failed')
                    ->value('error_message');

                return response()->json([
                    'success' => false,
                    'campaign_id' => $campaign->id,
                    'total_recipients' => $campaign->total_recipients,
                    'total_sms_sent' => 0,
                    'total_failed' => $totalFailed,
                    'message' => 'SMS sending failed: ' . ($sampleError ?? 'API error — check Beem Africa account balance and credentials.'),
                ], 422);
            }

            return response()->json([
                'success' => true,
                'campaign_id' => $campaign->id,
                'total_recipients' => $campaign->total_recipients,
                'total_sms_sent' => $totalSent,
                'total_failed' => $totalFailed,
                'date' => now()->format('d F Y H:i'),
                'message' => $totalFailed > 0
                    ? "Campaign done: {$totalSent} sent, {$totalFailed} failed."
                    : 'SMS Campaign Successfully Executed',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk SMS Creation Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate campaign: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch Beem Africa account balance.
     */
    public function getBalance(SmsApiService $smsApiService)
    {
        $result = $smsApiService->getBalance();
        return response()->json($result, $result['success'] ? 200 : 503);
    }

    /**
     * Send a direct test SMS to a single phone number.
     * Returns the raw API result so the UI can show the real error.
     */
    public function testSend(Request $request, SmsApiService $smsApiService)
    {
        $request->validate([
            'phone'   => 'required|string|max:20',
            'message' => 'required|string|max:500',
        ]);

        $result = $smsApiService->sendSMS($request->input('phone'), $request->input('message'));

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Return auto SMS trigger settings as JSON.
     */
    public function getAutoSettings(SmsAutoSettingsService $service)
    {
        return response()->json(['success' => true, 'settings' => $service->getAll()]);
    }

    /**
     * Save auto SMS trigger settings.
     */
    public function saveAutoSettings(Request $request, SmsAutoSettingsService $service)
    {
        $service->saveAll($request->input('settings', []));
        return response()->json(['success' => true, 'message' => 'Auto SMS settings saved successfully.']);
    }

    /**
     * Get system-specific SMS usage statistics.
     */
    public function getStats()
    {
        return response()->json([
            'success' => true,
            'stats' => $this->calculateStats()
        ]);
    }

    /**
     * Calculate SMS stats and graph data sent originating from this system only.
     */
    private function calculateStats(): array
    {
        $today = now()->startOfDay();
        $startOfWeek = now()->startOfWeek();
        $startOfMonth = now()->startOfMonth();
        $startOfYear = now()->startOfYear();

        // 1. Sent Today
        $sentToday = SmsCampaignRecipient::where(function($q) use ($today) {
                $q->where('sent_at', '>=', $today)->orWhere('created_at', '>=', $today);
            })
            ->whereIn('status', ['sent', 'completed', 'pending'])
            ->count();

        if ($sentToday === 0) {
            $sentToday = (int) SmsCampaign::where('created_at', '>=', $today)->sum('total_recipients');
        }

        // 2. Sent This Week
        $sentThisWeek = SmsCampaignRecipient::where(function($q) use ($startOfWeek) {
                $q->where('sent_at', '>=', $startOfWeek)->orWhere('created_at', '>=', $startOfWeek);
            })
            ->whereIn('status', ['sent', 'completed', 'pending'])
            ->count();

        if ($sentThisWeek === 0) {
            $sentThisWeek = (int) SmsCampaign::where('created_at', '>=', $startOfWeek)->sum('total_recipients');
        }

        // 3. Sent This Month
        $sentThisMonth = SmsCampaignRecipient::where(function($q) use ($startOfMonth) {
                $q->where('sent_at', '>=', $startOfMonth)->orWhere('created_at', '>=', $startOfMonth);
            })
            ->whereIn('status', ['sent', 'completed', 'pending'])
            ->count();

        if ($sentThisMonth === 0) {
            $sentThisMonth = (int) SmsCampaign::where('created_at', '>=', $startOfMonth)->sum('total_recipients');
        }

        // 4. Sent This Year
        $sentThisYear = SmsCampaignRecipient::where(function($q) use ($startOfYear) {
                $q->where('sent_at', '>=', $startOfYear)->orWhere('created_at', '>=', $startOfYear);
            })
            ->whereIn('status', ['sent', 'completed', 'pending'])
            ->count();

        if ($sentThisYear === 0) {
            $sentThisYear = (int) SmsCampaign::where('created_at', '>=', $startOfYear)->sum('total_recipients');
        }

        // 5. Total System Sent
        $totalSystemSent = SmsCampaignRecipient::count();
        if ($totalSystemSent === 0) {
            $totalSystemSent = (int) SmsCampaign::sum('total_recipients');
        }

        $totalCampaigns = SmsCampaign::count();
        $totalUnits = (int) SmsCampaign::sum('total_sms_units');

        // Monthly Trend Data (Last 6 Months)
        $monthlyLabels = [];
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');
            
            $count = SmsCampaignRecipient::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            if ($count === 0) {
                $count = (int) SmsCampaign::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->sum('total_recipients');
            }
            $monthlyData[] = $count;
        }

        // Status Breakdown
        $completedCount = SmsCampaignRecipient::whereIn('status', ['sent', 'completed'])->count();
        $pendingCount = SmsCampaignRecipient::where('status', 'pending')->count();
        $failedCount = SmsCampaignRecipient::where('status', 'failed')->count();

        if ($completedCount == 0 && $pendingCount == 0 && $failedCount == 0) {
            $completedCount = (int) SmsCampaign::where('status', 'completed')->sum('total_sent');
            $failedCount = (int) SmsCampaign::where('status', 'failed')->sum('total_failed');
            $pendingCount = (int) SmsCampaign::where('status', 'processing')->sum('total_recipients');
        }

        return [
            'today' => $sentToday,
            'this_week' => $sentThisWeek,
            'this_month' => $sentThisMonth,
            'this_year' => $sentThisYear,
            'total_system' => $totalSystemSent,
            'total_campaigns' => $totalCampaigns,
            'total_units' => $totalUnits,
            'monthly_labels' => $monthlyLabels,
            'monthly_data' => $monthlyData,
            'status_completed' => $completedCount,
            'status_pending' => $pendingCount,
            'status_failed' => $failedCount,
        ];
    }
}
