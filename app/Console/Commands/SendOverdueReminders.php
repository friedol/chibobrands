<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\MessageTemplate;
use App\Services\SmsApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendOverdueReminders extends Command
{
    protected $signature = 'messages:send-overdue-reminders
                            {--days=3 : Minimum days overdue before sending reminder}
                            {--dry-run : Preview recipients without sending}';

    protected $description = 'Send overdue follow-up reminder SMS to leads that have not been contacted';

    public function handle(SmsApiService $sms): int
    {
        $minDays = (int) $this->option('days');
        $cutoff  = Carbon::today()->subDays($minDays);

        $leads = Lead::where('status', 'pending')
            ->whereNotNull('phone')
            ->whereNotNull('follow_up_date')
            ->where('follow_up_date', '<=', $cutoff)
            ->where(function ($q) {
                $q->whereNull('last_reminder_sms_at')
                  ->orWhereDate('last_reminder_sms_at', '<', Carbon::today());
            })
            ->get();

        if ($leads->isEmpty()) {
            $this->info("No overdue leads (>{$minDays} days) require reminders.");
            return 0;
        }

        $template = MessageTemplate::active()
            ->where(function ($q) {
                $q->where('category', 'Follow-up')
                  ->orWhere('category', 'Overdue')
                  ->orWhere('title', 'like', '%Reminder%')
                  ->orWhere('title', 'like', '%Follow%');
            })
            ->first();

        $this->info("Found {$leads->count()} overdue lead(s) needing reminders.");

        $sent = 0;
        $failed = 0;

        foreach ($leads as $lead) {
            $daysOverdue = abs(Carbon::today()->diffInDays(Carbon::parse($lead->follow_up_date)));

            if ($template) {
                $message = str_replace(
                    ['{name}', '{customer}', '{days}', '{date}'],
                    [
                        $lead->customer_name,
                        $lead->customer_name,
                        $daysOverdue,
                        Carbon::parse($lead->follow_up_date)->format('d M Y'),
                    ],
                    $template->content
                );
            } else {
                $message = "Hello {$lead->customer_name}, CHIBO BRANDS inakukumbusha kuhusu mawasiliano yetu. "
                         . "Tumekuwa tukijaribu kukufikia kwa siku {$daysOverdue}. "
                         . "Tafadhali wasiliana nasi: 0655392319. Asante!";
            }

            if ($this->option('dry-run')) {
                $this->line("  [DRY RUN] {$lead->customer_name} ({$lead->phone}) — {$daysOverdue} days overdue");
                continue;
            }

            $result = $sms->sendSMS($lead->phone, $message);

            if ($result['success']) {
                $lead->update(['last_reminder_sms_at' => Carbon::now()]);
                $this->info("  ✓ Reminder sent to {$lead->customer_name} ({$lead->phone})");
                $sent++;
            } else {
                $this->warn("  ✗ Failed for {$lead->customer_name}: " . ($result['message'] ?? 'Unknown error'));
                Log::warning('Overdue reminder SMS failed', [
                    'lead_id' => $lead->id,
                    'phone'   => $lead->phone,
                    'error'   => $result['message'] ?? 'Unknown',
                ]);
                $failed++;
            }
        }

        $this->info("Done. Sent: {$sent}, Failed: {$failed}");
        return 0;
    }
}
