<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    /**
     * Send SMS message
     */
    public function send($to, $message)
    {
        $result = $this->twilioService->sendSMS($to, $message);
        return $result['success'];
    }

    /**
     * Send WhatsApp message (using wa.me link)
     */
    public function sendWhatsAppLink($phone, $message)
    {
        // Remove any non-numeric characters from phone
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Ensure phone starts with country code
        if (!str_starts_with($phone, '255')) {
            $phone = '255' . ltrim($phone, '0');
        }

        $encodedMessage = urlencode($message);
        $whatsappLink = "https://wa.me/{$phone}?text={$encodedMessage}";
        
        Log::info('WhatsApp link generated', [
            'phone' => $phone,
            'link' => $whatsappLink
        ]);

        return $whatsappLink;
    }

    /**
     * Format phone number for Tanzania
     */
    public function formatTanzaniaPhone($phone)
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If it starts with 0, replace with 255
        if (str_starts_with($phone, '0')) {
            $phone = '255' . substr($phone, 1);
        }
        
        // If it doesn't start with 255, add it
        if (!str_starts_with($phone, '255')) {
            $phone = '255' . $phone;
        }
        
        return '+' . $phone;
    }

    /**
     * Check if SMS service is configured
     */
    public function isConfigured()
    {
        return $this->twilioService->isConfigured();
    }
}
