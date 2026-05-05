<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SmsApiService;

class TestSmsApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:test {phone} {message?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test SMS API with a phone number and optional message';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phone = $this->argument('phone');
        $message = $this->argument('message') ?? 'Test SMS from CHIBO BRAND';

        $this->info("📱 Testing SMS API...");
        $this->info("📞 Phone: {$phone}");
        $this->info("💬 Message: {$message}");
        $this->newLine();

        $smsService = app(SmsApiService::class);
        $result = $smsService->sendSMS($phone, $message);

        if ($result['success']) {
            $this->info('✅ SMS sent successfully!');
            $this->line('Response: ' . ($result['message'] ?? 'N/A'));
            if (isset($result['data'])) {
                $this->line('Data: ' . json_encode($result['data']));
            }
        } else {
            $this->error('❌ SMS sending failed!');
            $this->line('Error: ' . ($result['message'] ?? 'Unknown error'));
            if (isset($result['status'])) {
                $this->line('Status: ' . $result['status']);
            }
        }

        return $result['success'] ? 0 : 1;
    }
}
