<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\MessageTemplate;
use App\Models\SmsCampaign;
use App\Models\SmsCampaignRecipient;
use App\Services\SmsApiService;
use App\Services\SmsAutoSettingsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendOverdueReminders extends Command
{
    protected $signature = 'messages:send-overdue-reminders
                            {--dry-run : Preview recipients without sending}';

    protected $description = 'Send follow-up SMS to leads on their follow_up_date, and to customers 3 days before their follow-up date. Creates SmsCampaign records so sends appear in History.';

    public function handle(SmsApiService $sms, SmsAutoSettingsService $autoSettings): int
    {
        $today  = Carbon::today();
        $dryRun = $this->option('dry-run');

        $grandSent   = 0;
        $grandFailed = 0;

        // ── 1. Leads: SMS on their follow_up_date ────────────────────────────
        $leads = Lead::where('status', 'pending')
            ->whereNotNull('phone')
            ->whereNotNull('follow_up_date')
            ->whereDate('follow_up_date', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('last_reminder_sms_at')
                  ->orWhereDate('last_reminder_sms_at', '<', $today);
            })
            ->get();

        if ($leads->isNotEmpty()) {
            $this->info("Found {$leads->count()} lead(s) with follow-up date today.");

            // Prefer auto-settings template if enabled, otherwise fall back to MessageTemplate
            $useAutoTemplate = $autoSettings->isEnabled('lead_reminder');
            $autoTemplate    = $useAutoTemplate ? $autoSettings->getTemplate('lead_reminder') : null;

            $msgTemplate = MessageTemplate::active()
                ->where(function ($q) {
                    $q->where('category', 'Follow-up')
                      ->orWhere('category', 'Overdue')
                      ->orWhere('title', 'like', '%Reminder%')
                      ->orWhere('title', 'like', '%Follow%');
                })
                ->first();

            $sampleMessage = $autoTemplate
                ?? $msgTemplate?->content
                ?? "Habari {name}, CHIBO BRANDS inakukumbusha kuhusu miadi yetu ya leo {date}. Piga simu: 0655392319. Asante!";

            $campaign = null;
            if (!$dryRun) {
                $unitsPerMsg = ceil(mb_strlen($sampleMessage) / 160) ?: 1;
                $campaign = SmsCampaign::create([
                    'title'                => 'Auto: Lead Reminder — ' . $today->format('d M Y'),
                    'message'              => $sampleMessage,
                    'status'               => 'processing',
                    'total_recipients'     => $leads->count(),
                    'sms_units_per_message'=> $unitsPerMsg,
                    'total_sms_units'      => $leads->count() * $unitsPerMsg,
                    'sent_by'              => null,
                ]);
            }

            $sent = 0; $failed = 0; $rows = [];

            foreach ($leads as $lead) {
                $followUpDate = Carbon::parse($lead->follow_up_date)->format('d M Y');

                if ($autoTemplate) {
                    $msg = $autoSettings->resolveTemplate('lead_reminder', [
                        'name'           => $lead->customer_name,
                        'task_code'      => '',
                        'task_title'     => '',
                        'amount'         => '',
                        'paid'           => '',
                        'balance'        => '',
                        'pickup_code'    => '',
                        'deadline'       => $followUpDate,
                        'seller_contact' => '0655392319',
                    ]);
                } elseif ($msgTemplate) {
                    $msg = str_replace(
                        ['{name}', '{customer}', '{days}', '{date}'],
                        [$lead->customer_name, $lead->customer_name, 0, $followUpDate],
                        $msgTemplate->content
                    );
                } else {
                    $msg = "Habari {$lead->customer_name}, CHIBO BRANDS inakukumbusha kuhusu miadi yetu ya leo {$followUpDate}. "
                         . "Tungependa kuwasiliana nawe. Piga simu: 0655392319. Asante!";
                }

                if ($dryRun) {
                    $this->line("  [DRY RUN] [Lead] {$lead->customer_name} ({$lead->phone}) — follow-up today");
                    continue;
                }

                $result = $sms->sendSMS($lead->phone, $msg);

                $formattedPhone = $sms->formatPhoneNumber($lead->phone);

                if ($result['success'] ?? false) {
                    $lead->update(['last_reminder_sms_at' => now()]);
                    $this->info("  ✓ Lead {$lead->customer_name} ({$lead->phone})");
                    $sent++;
                    $rows[] = [
                        'sms_campaign_id' => $campaign->id,
                        'customer_id'     => null,
                        'recipient_name'  => $lead->customer_name,
                        'phone_number'    => $formattedPhone,
                        'status'          => 'sent',
                        'error_message'   => null,
                        'sent_at'         => now(),
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                } else {
                    $errMsg = $result['message'] ?? 'Unknown error';
                    $this->warn("  ✗ Failed for {$lead->customer_name}: {$errMsg}");
                    Log::warning('lead_reminder_sms_failed', [
                        'lead_id' => $lead->id,
                        'phone'   => $lead->phone,
                        'error'   => $errMsg,
                    ]);
                    $failed++;
                    $rows[] = [
                        'sms_campaign_id' => $campaign->id,
                        'customer_id'     => null,
                        'recipient_name'  => $lead->customer_name,
                        'phone_number'    => $formattedPhone,
                        'status'          => 'failed',
                        'error_message'   => $errMsg,
                        'sent_at'         => null,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                }
            }

            if ($campaign) {
                if (!empty($rows)) SmsCampaignRecipient::insert($rows);
                $campaign->update([
                    'status'       => 'completed',
                    'total_sent'   => $sent,
                    'total_failed' => $failed,
                    'completed_at' => now(),
                ]);
            }

            $grandSent   += $sent;
            $grandFailed += $failed;
        } else {
            $this->info('No leads with follow-up date today.');
        }

        // ── 2. Customers: SMS 3 days before their follow-up date ─────────────
        $targetDate          = Carbon::today()->addDays(3)->toDateString();
        $followUpDateFormatted = Carbon::parse($targetDate)->format('d M Y');

        $customers = \App\Models\Customer::whereNotNull('phone')
            ->where(function ($q) use ($targetDate) {
                $q->whereDate('manual_follow_up_date', $targetDate)
                  ->orWhere(function ($sq) use ($targetDate) {
                      $sq->whereNull('manual_follow_up_date')
                         ->whereDate('next_expected_order_date', $targetDate);
                  });
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('last_reminder_sms_at')
                  ->orWhereDate('last_reminder_sms_at', '<', $today);
            })
            ->get();

        if ($customers->isNotEmpty()) {
            $this->info("Found {$customers->count()} customer(s) with follow-up in 3 days ({$targetDate}).");

            $custTemplate = MessageTemplate::active()
                ->where(function ($q) {
                    $q->where('category', 'Customer')
                      ->orWhere('title', 'like', '%Customer%')
                      ->orWhere('title', 'like', '%Re-engage%');
                })
                ->first();

            $sampleMessage = $custTemplate?->content
                ?? "Habari {name}, CHIBO BRANDS inakukumbusha kuhusu huduma yetu. Tunatarajia kukuona tarehe {date}. Wasiliana nasi: 0655392319. Asante!";

            $campaign = null;
            if (!$dryRun) {
                $unitsPerMsg = ceil(mb_strlen($sampleMessage) / 160) ?: 1;
                $campaign = SmsCampaign::create([
                    'title'                => 'Auto: Customer Follow-up (3 days) — ' . $today->format('d M Y'),
                    'message'              => $sampleMessage,
                    'status'               => 'processing',
                    'total_recipients'     => $customers->count(),
                    'sms_units_per_message'=> $unitsPerMsg,
                    'total_sms_units'      => $customers->count() * $unitsPerMsg,
                    'sent_by'              => null,
                ]);
            }

            $sent = 0; $failed = 0; $rows = [];

            foreach ($customers as $customer) {
                $msg = $custTemplate
                    ? str_replace(
                        ['{name}', '{customer}', '{days}', '{date}'],
                        [$customer->name, $customer->name, 3, $followUpDateFormatted],
                        $custTemplate->content
                    )
                    : "Habari {$customer->name}, CHIBO BRANDS inakukumbusha kuhusu huduma yetu. "
                    . "Tunatarajia kukuona tarehe {$followUpDateFormatted}. Wasiliana nasi: 0655392319. Asante!";

                if ($dryRun) {
                    $this->line("  [DRY RUN] [Customer] {$customer->name} ({$customer->phone}) — follow-up on {$followUpDateFormatted}");
                    continue;
                }

                $result = $sms->sendSMS($customer->phone, $msg);
                $formattedPhone = $sms->formatPhoneNumber($customer->phone);

                if ($result['success'] ?? false) {
                    $customer->update(['last_reminder_sms_at' => now()]);
                    $this->info("  ✓ Customer {$customer->name} ({$customer->phone})");
                    $sent++;
                    $rows[] = [
                        'sms_campaign_id' => $campaign->id,
                        'customer_id'     => $customer->id,
                        'recipient_name'  => $customer->name,
                        'phone_number'    => $formattedPhone,
                        'status'          => 'sent',
                        'error_message'   => null,
                        'sent_at'         => now(),
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                } else {
                    $errMsg = $result['message'] ?? 'Unknown error';
                    $this->warn("  ✗ Failed for {$customer->name}: {$errMsg}");
                    Log::warning('customer_followup_sms_failed', [
                        'customer_id' => $customer->id,
                        'phone'       => $customer->phone,
                        'error'       => $errMsg,
                    ]);
                    $failed++;
                    $rows[] = [
                        'sms_campaign_id' => $campaign->id,
                        'customer_id'     => $customer->id,
                        'recipient_name'  => $customer->name,
                        'phone_number'    => $formattedPhone,
                        'status'          => 'failed',
                        'error_message'   => $errMsg,
                        'sent_at'         => null,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                }
            }

            if ($campaign) {
                if (!empty($rows)) SmsCampaignRecipient::insert($rows);
                $campaign->update([
                    'status'       => 'completed',
                    'total_sent'   => $sent,
                    'total_failed' => $failed,
                    'completed_at' => now(),
                ]);
            }

            $grandSent   += $sent;
            $grandFailed += $failed;
        } else {
            $this->info("No customers with follow-up date in 3 days ({$targetDate}).");
        }

        $this->info("Done. Total sent: {$grandSent}, Total failed: {$grandFailed}");
        return 0;
    }
}
