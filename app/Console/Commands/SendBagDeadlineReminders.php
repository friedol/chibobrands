<?php

namespace App\Console\Commands;

use App\Models\DesignTask;
use App\Services\SmsApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBagDeadlineReminders extends Command
{
    protected $signature = 'tasks:send-bag-deadline-reminders
                            {--days=3 : Days before deadline to send reminder}
                            {--dry-run : Preview without sending}';

    protected $description = 'Send approaching-deadline SMS reminders for bag design tasks';

    public function handle(SmsApiService $sms): int
    {
        $days   = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $targetDate = Carbon::today()->addDays($days);

        // Find bag tasks with deadline exactly $days days from today, not yet completed/cancelled
        $tasks = DesignTask::with(['customer', 'designTaskType'])
            ->whereHas('designTaskType', fn($q) => $q->where('name', 'like', '%bag%'))
            ->whereDate('deadline', $targetDate)
            ->whereNotIn('status', ['super_completed', 'delivered', 'cancelled'])
            ->whereHas('customer', fn($q) => $q->whereNotNull('phone'))
            ->get();

        if ($tasks->isEmpty()) {
            $this->info("No bag tasks with deadline in {$days} days.");
            return 0;
        }

        $this->info("Found {$tasks->count()} bag task(s) with deadline on {$targetDate->format('Y-m-d')}.");

        $sent = 0;
        $skipped = 0;

        // Group by customer to avoid duplicate SMS
        $byCustomer = $tasks->groupBy('customer_id');

        foreach ($byCustomer as $customerId => $customerTasks) {
            $customer = $customerTasks->first()->customer;
            if (!$customer || !$customer->phone) {
                continue;
            }

            // Deduplicate: skip if we already sent a reminder today
            if ($customer->last_reminder_sms_at &&
                Carbon::parse($customer->last_reminder_sms_at)->isToday()) {
                $skipped++;
                continue;
            }

            $taskCount = $customerTasks->count();
            $deadlineStr = $targetDate->format('d/m/Y');

            $message = "Habari {$customer->name}, kumbusho: mfuko wako " .
                       ($taskCount > 1 ? "({$taskCount} mifuko)" : '') .
                       " una tarehe ya mwisho ya {$deadlineStr}. Tafadhali wasiliana nasi ikiwa una mabadiliko yoyote. Asante! - Chibobrand";

            if ($dryRun) {
                $this->line("[DRY RUN] {$customer->name} ({$customer->phone}) — deadline {$deadlineStr}, {$taskCount} task(s)");
                $sent++;
                continue;
            }

            $result = $sms->sendSMS($customer->phone, $message);

            if ($result && ($result['success'] ?? false)) {
                $customer->update(['last_reminder_sms_at' => now()]);
                $this->info("✓ Bag deadline reminder sent to {$customer->name} ({$customer->phone})");
                Log::info('bag_deadline_reminder_sent', [
                    'customer_id' => $customerId,
                    'phone'       => $customer->phone,
                    'deadline'    => $deadlineStr,
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
