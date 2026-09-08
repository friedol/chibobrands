<?php

namespace App\Console\Commands;

use App\Models\DesignTask;
use App\Services\SmsApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPickupReminders extends Command
{
    protected $signature = 'tasks:send-pickup-reminders
                            {--days=3 : Days after super_completed before sending reminder}
                            {--dry-run : Preview without sending}';

    protected $description = 'Remind customers to pick up orders that have been ready for 3+ days';

    public function handle(SmsApiService $sms): int
    {
        $days    = (int) $this->option('days');
        $cutoff  = Carbon::today()->subDays($days);
        $dryRun  = $this->option('dry-run');

        // Find tasks that are super_completed, not yet delivered, completed_at >= cutoff
        $tasks = DesignTask::with(['customer'])
            ->where('status', 'super_completed')
            ->whereNotIn('delivery_status', ['delivered'])
            ->whereNotNull('completed_at')
            ->whereDate('completed_at', '<=', $cutoff)
            ->whereHas('customer', fn($q) => $q->whereNotNull('phone'))
            ->get();

        // Group by customer — one SMS per customer per day
        $byCustomer = $tasks->groupBy('customer_id');
        $sent = 0;
        $skipped = 0;

        foreach ($byCustomer as $customerId => $customerTasks) {
            $customer = $customerTasks->first()->customer;
            if (!$customer || !$customer->phone) {
                continue;
            }

            // Deduplicate: skip if we already sent a pickup reminder today
            if ($customer->last_reminder_sms_at &&
                Carbon::parse($customer->last_reminder_sms_at)->isToday()) {
                $skipped++;
                continue;
            }

            $taskCount = $customerTasks->count();
            $message   = "Habari {$customer->name}, kazi yako ya ubunifu " .
                         ($taskCount > 1 ? "({$taskCount} kazi)" : '') .
                         " imekamilika na iko tayari kukusanywa. Tafadhali kuja Chibobrand kuchukua bidhaa yako. Asante!";

            if ($dryRun) {
                $this->line("[DRY RUN] {$customer->name} ({$customer->phone}) — {$taskCount} task(s) ready");
                $sent++;
                continue;
            }

            $result = $sms->sendSMS($customer->phone, $message);

            if ($result && ($result['success'] ?? false)) {
                $customer->update(['last_reminder_sms_at' => now()]);
                $this->info("✓ Pickup reminder sent to {$customer->name} ({$customer->phone})");
                Log::info('pickup_reminder_sent', [
                    'customer_id' => $customerId,
                    'phone'       => $customer->phone,
                    'task_count'  => $taskCount,
                ]);
                $sent++;
            } else {
                $this->error("✗ Failed to send to {$customer->name} ({$customer->phone})");
            }
        }

        $this->info("Done. Sent: {$sent}, Skipped (already notified today): {$skipped}");
        return 0;
    }
}
