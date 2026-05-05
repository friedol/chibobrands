<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    protected $client;
    protected $from;

    public function __construct()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $this->from = config('services.twilio.from');
        
        // Initialize Twilio client
        if ($sid && $token) {
            try {
                $this->client = new Client($sid, $token);
            } catch (\Exception $e) {
                Log::error('Failed to initialize Twilio client', [
                    'error' => $e->getMessage()
                ]);
                $this->client = null;
            }
        } else {
            Log::warning('Twilio credentials not configured. SMS will be logged instead of sent.');
            $this->client = null;
        }
    }

    /**
     * Send SMS message via Twilio
     *
     * @param string $to Phone number to send to (with country code)
     * @param string $message Message content
     * @return array Result array with success status and message
     */
    public function sendSMS($to, $message)
    {
        try {
            // If Twilio client is not initialized, log the message
            if (!$this->client) {
                Log::info('SMS would be sent (Twilio not configured)', [
                    'to' => $to,
                    'message' => $message,
                    'from' => $this->from
                ]);
                
                return [
                    'success' => true,
                    'message' => 'SMS logged (Twilio not configured)',
                    'sid' => null
                ];
            }

            // Validate phone number format
            $to = $this->formatPhoneNumber($to);
            
            // Send SMS via Twilio
            $message = $this->client->messages->create($to, [
                'from' => $this->from,
                'body' => $message,
            ]);

            Log::info('SMS sent successfully via Twilio', [
                'to' => $to,
                'message_sid' => $message->sid,
                'status' => $message->status,
                'from' => $this->from
            ]);

            return [
                'success' => true,
                'message' => 'SMS sent successfully',
                'sid' => $message->sid,
                'status' => $message->status
            ];

        } catch (\Exception $e) {
            Log::error('SMS sending failed via Twilio', [
                'to' => $to,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ]);
            
            return [
                'success' => false,
                'message' => 'SMS sending failed: ' . $e->getMessage(),
                'sid' => null
            ];
        }
    }

    /**
     * Format phone number for international use
     *
     * @param string $phone Phone number to format
     * @return string Formatted phone number
     */
    public function formatPhoneNumber($phone)
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // If it starts with 0, replace with country code
        if (str_starts_with($phone, '0')) {
            $phone = '255' . substr($phone, 1);
        }
        
        // If it doesn't start with +, add it
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }
        
        return $phone;
    }

    /**
     * Check if Twilio is properly configured
     *
     * @return bool
     */
    public function isConfigured()
    {
        return $this->client !== null;
    }

    /**
     * Get Twilio account information
     *
     * @return array|null
     */
    public function getAccountInfo()
    {
        if (!$this->client) {
            return null;
        }

        try {
            $account = $this->client->api->accounts(config('services.twilio.sid'))->fetch();
            return [
                'friendly_name' => $account->friendlyName,
                'status' => $account->status,
                'type' => $account->type
            ];
        } catch (\Exception $e) {
            Log::error('Failed to fetch Twilio account info', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
