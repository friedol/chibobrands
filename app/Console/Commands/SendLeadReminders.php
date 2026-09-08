<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\User;
use App\Notifications\LeadFollowUpReminder;
use App\Services\SmsApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendLeadReminders extends Command
{
    protected $signature = 'leads:send-reminders
                            {--dry-run : Preview without sending notifications}';

    protected $description = 'Send notifications for leads that need follow-up today, and SMS when promised_order_date is reached';

    public function handle(SmsApiService $sms): int
    {
        $dryRun = $this->option('dry-run');
        $today  = Carbon::today();

        // 1. In-app notifications for leads with follow_up_date = today
        $followUpLeads = Lead::whereDate('follow_up_date', $today)
            ->where('status', 'pending')
            ->whereNotNull('assigned_seller_id')
            ->get();

        foreach ($followUpLeads as $lead) {
            $seller = User::find($lead->assigned_seller_id);
            if (!$seller) {
                continue;
            }

            if ($dryRun) {
                $this->line("[DRY RUN] Follow-up notification → {$seller->name} for lead: {$lead->customer_name}");
                continue;
            }

            $seller->notify(new LeadFollowUpReminder($lead));
            $this->info("✓ Follow-up reminder sent to {$seller->name} for lead: {$lead->customer_name}");
        }

        // 2. SMS to seller when lead's promised_order_date is today
        $promisedLeads = Lead::whereDate('promised_order_date', $today)
            ->whereNotIn('status', ['won', 'lost', 'cancelled'])
            ->whereNotNull('assigned_seller_id')
            ->where(function ($q) use ($today) {
                $q->whereNull('last_reminder_sms_at')
                  ->orWhereDate('last_reminder_sms_at', '<', $today);
            })
            ->get();

        $smsSent = 0;

        foreach ($promisedLeads as $lead) {
            $seller = User::find($lead->assigned_seller_id);
            if (!$seller || !$seller->phone) {
                continue;
            }

            $message = "Kumbusho: Leo ndio tarehe ya agizo iliyoahidiwa kwa mteja {$lead->customer_name}. " .
                       "Wasiliana nao leo ili kufuatilia agizo. - Chibobrand";

            if ($dryRun) {
                $this->line("[DRY RUN] Promised-order SMS → {$seller->name} ({$seller->phone}) for lead: {$lead->customer_name}");
                $smsSent++;
                continue;
            }

            $result = $sms->sendSMS($seller->phone, $message);

            if ($result && ($result['success'] ?? false)) {
                $lead->update(['last_reminder_sms_at' => now()]);
                $this->info("✓ Promised-order SMS sent to {$seller->name} ({$seller->phone}) — lead: {$lead->customer_name}");
                Log::info('promised_order_sms_sent', [
                    'lead_id'   => $lead->id,
                    'seller_id' => $seller->id,
                    'phone'     => $seller->phone,
                ]);
                $smsSent++;
            } else {
                $this->error("✗ Failed to send promised-order SMS to {$seller->name} ({$seller->phone})");
            }
        }

        $this->info("Done. Follow-up notifications: {$followUpLeads->count()}, Promised-order SMSs sent: {$smsSent}");
        return 0;
    }
}
