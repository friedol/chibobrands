<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\DesignTask;
use App\Services\SmsApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBalanceReminders extends Command
{
    protected $signature = 'payments:send-balance-reminders
                            {--days=3 : Days between reminders, starting from when the task was super completed}
                            {--dry-run : Preview recipients without sending}';

    protected $description = 'Remind customers who still owe money on super-completed tasks, every N days, per customer';

    /** Cap how many individual task lines are itemized in the SMS to keep it a reasonable length/cost. */
    private const MAX_ITEMS_IN_MESSAGE = 5;

    public function handle(SmsApiService $sms): int
    {
        $days   = (int) $this->option('days');
        $cutoff = Carbon::today()->subDays($days);
        $dryRun = $this->option('dry-run');

        // Only tasks that have actually reached (or passed) super_completed carry a
        // `super_completed_at` timestamp — that's the "marked super complete" reference point.
        $tasks = DesignTask::with('customer')
            ->whereIn('status', [DesignTask::STATUS_SUPER_COMPLETED, DesignTask::STATUS_DELIVERED])
            ->whereNotNull('super_completed_at')
            ->where('balance', '>', 0)
            ->whereHas('customer', fn ($q) => $q->whereNotNull('phone'))
            ->get();

        $byCustomer = $tasks->groupBy('customer_id');
        $sent    = 0;
        $skipped = 0;

        foreach ($byCustomer as $customerId => $customerTasks) {
            /** @var Customer|null $customer */
            $customer = $customerTasks->first()->customer;
            if (!$customer || !$customer->phone) {
                continue;
            }

            // The clock for THIS customer's balance reminders starts at the earliest
            // super_completed date among their still-owing tasks, then repeats every
            // {days} days from whenever the last balance reminder actually went out.
            $referenceDate = $customer->last_balance_reminder_sms_at
                ? Carbon::parse($customer->last_balance_reminder_sms_at)
                : $customerTasks->min('super_completed_at');

            if (!$referenceDate || Carbon::parse($referenceDate)->gt($cutoff)) {
                $skipped++;
                continue;
            }

            $totalBalance = (float) $customerTasks->sum('balance');
            if ($totalBalance <= 0) {
                continue;
            }

            $sortedTasks = $customerTasks->sortBy('super_completed_at')->values();
            $lines = $sortedTasks->take(self::MAX_ITEMS_IN_MESSAGE)->map(function (DesignTask $task) {
                $label = $task->title ?: ($task->task_code ?: ('#' . $task->id));
                $date  = Carbon::parse($task->super_completed_at)->format('d M Y');
                return "- {$label}: TZS " . number_format((float) $task->balance) . " (kamilika {$date})";
            })->implode("\n");

            $extraCount = $sortedTasks->count() - self::MAX_ITEMS_IN_MESSAGE;
            if ($extraCount > 0) {
                $lines .= "\n- na kazi nyingine {$extraCount} zenye deni";
            }

            $message = "Habari {$customer->name}, unadaiwa jumla ya TZS " . number_format($totalBalance)
                . " kwa kazi zifuatazo:\n{$lines}\n"
                . "Tafadhali tupigie kwa mawasiliano zaidi. Asante - CHIBO BRANDS. Simu: 0655392319";

            if ($dryRun) {
                $this->line("[DRY RUN] {$customer->name} ({$customer->phone}) — TZS " . number_format($totalBalance) . " owed across {$sortedTasks->count()} task(s)");
                $sent++;
                continue;
            }

            $result = $sms->sendSMS($customer->phone, $message);

            if ($result && ($result['success'] ?? false)) {
                $customer->update(['last_balance_reminder_sms_at' => now()]);
                $this->info("✓ Balance reminder sent to {$customer->name} ({$customer->phone}) — TZS " . number_format($totalBalance));
                Log::info('balance_reminder_sent', [
                    'customer_id'   => $customerId,
                    'phone'         => $customer->phone,
                    'total_balance' => $totalBalance,
                    'task_count'    => $sortedTasks->count(),
                ]);
                $sent++;
            } else {
                $this->error("✗ Failed to send to {$customer->name} ({$customer->phone}): " . ($result['message'] ?? 'Unknown error'));
                Log::warning('balance_reminder_failed', [
                    'customer_id' => $customerId,
                    'phone'       => $customer->phone,
                    'error'       => $result['message'] ?? 'Unknown',
                ]);
            }
        }

        $this->info("Done. Sent: {$sent}, Skipped (not due yet): {$skipped}");
        return 0;
    }
}
