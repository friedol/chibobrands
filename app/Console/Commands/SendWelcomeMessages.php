<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\MessageTemplate;
use App\Services\SmsApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendWelcomeMessages extends Command
{
    protected $signature = 'messages:send-welcome
                            {--dry-run : Preview recipients without sending}';

    protected $description = 'Send welcome SMS to new customers who have not received one yet';

    public function handle(SmsApiService $sms): int
    {
        $customers = Customer::whereNull('welcome_sms_sent_at')
            ->whereNotNull('phone')
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->get();

        if ($customers->isEmpty()) {
            $this->info('No new customers to welcome.');
            return 0;
        }

        $template = MessageTemplate::active()
            ->where(function ($q) {
                $q->where('category', 'Welcome')
                  ->orWhere('title', 'like', '%Welcome%');
            })
            ->first();

        $this->info("Found {$customers->count()} new customer(s) to welcome.");

        $sent = 0;
        $failed = 0;

        foreach ($customers as $customer) {
            $message = $template
                ? str_replace(['{name}', '{customer}'], $customer->name, $template->content)
                : "Hello {$customer->name}! Welcome to CHIBO BRANDS. We're happy to have you. For any assistance contact us at 0655392319. Thank you!";

            if ($this->option('dry-run')) {
                $this->line("  [DRY RUN] Would send to: {$customer->name} ({$customer->phone})");
                continue;
            }

            $result = $sms->sendSMS($customer->phone, $message);

            if ($result['success']) {
                $customer->update(['welcome_sms_sent_at' => Carbon::now()]);
                $this->info("  ✓ Sent to {$customer->name} ({$customer->phone})");
                $sent++;
            } else {
                $this->warn("  ✗ Failed for {$customer->name}: " . ($result['message'] ?? 'Unknown error'));
                Log::warning('Welcome SMS failed', [
                    'customer_id' => $customer->id,
                    'phone'       => $customer->phone,
                    'error'       => $result['message'] ?? 'Unknown',
                ]);
                $failed++;
            }
        }

        $this->info("Done. Sent: {$sent}, Failed: {$failed}");
        return 0;
    }
}
