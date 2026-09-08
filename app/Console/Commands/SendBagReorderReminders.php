<?php

namespace App\Console\Commands;

use App\Models\CustomerTaskTypeAnalytic;
use App\Models\DesignTaskType;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\SmsApiService;

class SendBagReorderReminders extends Command
{
    protected $signature = 'bags:send-reorder-reminders
                            {--days=3 : Days before the customer\'s projected bag reorder date to send the reminder}
                            {--dry-run : Preview without sending}';

    protected $description = 'Remind bag (mifuko) customers to reorder before their bags are expected to run out';

    public function handle(SmsApiService $sms): int
    {
        $days       = (int) $this->option('days');
        $dryRun     = $this->option('dry-run');
        $targetDate = Carbon::today()->addDays($days);

        // Scope strictly to the MIFUKO (bags) department's design task types —
        // a customer's general reorder rhythm across all products is irrelevant here.
        $bagTypeIds = DesignTaskType::whereHas('department', fn ($q) => $q->where('name', 'like', '%MIFUKO%'))
            ->pluck('id');

        if ($bagTypeIds->isEmpty()) {
            $this->error('No design task types found under the MIFUKO department — check department naming.');
            return 1;
        }

        // Per-type projected reorder date lands exactly on {days} days from today —
        // this naturally fires once per cycle (the date only matches on that one day).
        $analytics = CustomerTaskTypeAnalytic::with('customer')
            ->whereIn('design_task_type_id', $bagTypeIds)
            ->whereDate('next_expected_purchase_date', $targetDate)
            ->whereHas('customer', fn ($q) => $q->whereNotNull('phone'))
            ->get();

        if ($analytics->isEmpty()) {
            $this->info("No bag customers projected to reorder on {$targetDate->format('Y-m-d')}.");
            return 0;
        }

        $byCustomer = $analytics->groupBy('customer_id');
        $sent = 0;
        $skipped = 0;

        foreach ($byCustomer as $customerId => $rows) {
            $customer = $rows->first()->customer;
            if (!$customer || !$customer->phone) {
                $skipped++;
                continue;
            }

            $message = "Habari {$customer->name}, kumbusho kutoka CHIBO BRANDS: mifuko yako inakaribia kuisha. "
                . "Tafadhali weka oda mapema ili usikose bidhaa. Wasiliana nasi: 0655392319. Asante!";

            if ($dryRun) {
                $this->line("[DRY RUN] {$customer->name} ({$customer->phone}) — projected reorder {$targetDate->format('d M Y')}");
                $sent++;
                continue;
            }

            $result = $sms->sendSMS($customer->phone, $message);

            if ($result && ($result['success'] ?? false)) {
                $this->info("✓ Bag reorder reminder sent to {$customer->name} ({$customer->phone})");
                Log::info('bag_reorder_reminder_sent', [
                    'customer_id'    => $customerId,
                    'phone'          => $customer->phone,
                    'projected_date' => $targetDate->format('Y-m-d'),
                ]);
                $sent++;
            } else {
                $this->error("✗ Failed to send to {$customer->name} ({$customer->phone}): " . ($result['message'] ?? 'Unknown error'));
                Log::warning('bag_reorder_reminder_failed', [
                    'customer_id' => $customerId,
                    'phone'       => $customer->phone,
                    'error'       => $result['message'] ?? 'Unknown',
                ]);
            }
        }

        $this->info("Done. Sent: {$sent}, Skipped (no phone): {$skipped}");
        return 0;
    }
}
