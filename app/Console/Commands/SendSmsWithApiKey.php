<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendSmsWithApiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:send-apikey {phone} {message?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send SMS using Twilio API Key authentication';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phone = $this->argument('phone');
        $message = $this->argument('message') ?: 'Hello from CHIBO BRAND! This is a test SMS from your Laravel application.';

        $this->info("📱 Sending SMS using API Key authentication...");
        $this->info("📞 Phone: {$phone}");
        $this->info("💬 Message: {$message}");
        $this->newLine();

        // Try different credential combinations
        $credentials = [
            'sid' => config('services.twilio.sid'),
            'token' => config('services.twilio.token'),
            'api_key' => config('services.twilio.api_key'),
            'api_secret' => config('services.twilio.api_secret'),
            'from' => config('services.twilio.from'),
        ];

        $this->info("🔧 Available credentials:");
        $this->line("   SID: " . ($credentials['sid'] ? '✅ Set' : '❌ Not set'));
        $this->line("   Token: " . ($credentials['token'] ? '✅ Set' : '❌ Not set'));
        $this->line("   API Key: " . ($credentials['api_key'] ? '✅ Set' : '❌ Not set'));
        $this->line("   API Secret: " . ($credentials['api_secret'] ? '✅ Set' : '❌ Not set'));
        $this->line("   From: " . ($credentials['from'] ?: '❌ Not set'));
        $this->newLine();

        // Format phone number
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        // Try with Account SID and Auth Token first
        if ($credentials['sid'] && $credentials['token']) {
            $this->info("🔄 Trying with Account SID and Auth Token...");
            $result = $this->sendWithBasicAuth($credentials['sid'], $credentials['token'], $phone, $message);
            if ($result['success']) {
                return 0;
            }
        }

        // Try with API Key if available
        if ($credentials['api_key'] && $credentials['api_secret']) {
            $this->info("🔄 Trying with API Key and Secret...");
            $result = $this->sendWithApiKey($credentials['api_key'], $credentials['api_secret'], $phone, $message);
            if ($result['success']) {
                return 0;
            }
        }

        $this->error('❌ All authentication methods failed!');
        $this->newLine();
        $this->warn('💡 Please check your Twilio credentials in the .env file.');
        $this->line('   Make sure you have either:');
        $this->line('   • Valid Account SID and Auth Token, OR');
        $this->line('   • Valid API Key and API Secret');

        return 1;
    }

    private function sendWithBasicAuth($sid, $token, $phone, $message)
    {
        try {
            $response = Http::withOptions(['verify' => false])
                ->withBasicAuth($sid, $token)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => config('services.twilio.from'),
                    'To' => $phone,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->info('✅ SMS sent successfully with Account SID/Auth Token!');
                $this->line("📋 Message SID: " . ($data['sid'] ?? 'N/A'));
                $this->line("📊 Status: " . ($data['status'] ?? 'N/A'));
                $this->line("💰 Price: " . ($data['price'] ?? 'N/A'));
                $this->newLine();
                $this->info('📱 Check your phone for the SMS message!');
                return ['success' => true];
            } else {
                $this->error('❌ Account SID/Auth Token failed: ' . $response->status());
                $errorData = $response->json();
                if (isset($errorData['message'])) {
                    $this->error("Error: " . $errorData['message']);
                }
                return ['success' => false];
            }
        } catch (\Exception $e) {
            $this->error('❌ Account SID/Auth Token exception: ' . $e->getMessage());
            return ['success' => false];
        }
    }

    private function sendWithApiKey($apiKey, $apiSecret, $phone, $message)
    {
        try {
            $response = Http::withOptions(['verify' => false])
                ->withBasicAuth($apiKey, $apiSecret)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/" . config('services.twilio.sid') . "/Messages.json", [
                    'From' => config('services.twilio.from'),
                    'To' => $phone,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->info('✅ SMS sent successfully with API Key!');
                $this->line("📋 Message SID: " . ($data['sid'] ?? 'N/A'));
                $this->line("📊 Status: " . ($data['status'] ?? 'N/A'));
                $this->line("💰 Price: " . ($data['price'] ?? 'N/A'));
                $this->newLine();
                $this->info('📱 Check your phone for the SMS message!');
                return ['success' => true];
            } else {
                $this->error('❌ API Key failed: ' . $response->status());
                $errorData = $response->json();
                if (isset($errorData['message'])) {
                    $this->error("Error: " . $errorData['message']);
                }
                return ['success' => false];
            }
        } catch (\Exception $e) {
            $this->error('❌ API Key exception: ' . $e->getMessage());
            return ['success' => false];
        }
    }
}
