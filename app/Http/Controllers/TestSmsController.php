<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TwilioService;
use Illuminate\Support\Facades\Log;

class TestSmsController extends Controller
{
    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    /**
     * Test SMS sending functionality
     */
    public function testSMS(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string|max:160'
        ]);

        $phone = $request->phone;
        $message = $request->message;

        // Check if Twilio is configured
        if (!$this->twilioService->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Twilio is not configured. Check your .env file.',
                'phone' => $phone,
                'message' => $message
            ], 400);
        }

        // Send SMS
        $result = $this->twilioService->sendSMS($phone, $message);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'SMS sent successfully!',
                'phone' => $phone,
                'message' => $message,
                'twilio_sid' => $result['sid'] ?? null,
                'status' => $result['status'] ?? null
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'SMS sending failed: ' . $result['message'],
                'phone' => $phone,
                'message' => $message
            ], 500);
        }
    }

    /**
     * Test SMS with default message
     */
    public function testSMSDefault($phone)
    {
        $message = 'Hello from CHIBO BRAND via Twilio! This is a test SMS from your Laravel application.';
        
        return $this->testSMS(new Request([
            'phone' => $phone,
            'message' => $message
        ]));
    }

    /**
     * Get Twilio account information
     */
    public function getTwilioInfo()
    {
        if (!$this->twilioService->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Twilio is not configured'
            ], 400);
        }

        $accountInfo = $this->twilioService->getAccountInfo();
        
        return response()->json([
            'success' => true,
            'configured' => true,
            'account_info' => $accountInfo,
            'from_number' => config('services.twilio.from')
        ]);
    }

    /**
     * Show test SMS form (for web interface)
     */
    public function showTestForm()
    {
        return view('test-sms', [
            'configured' => $this->twilioService->isConfigured(),
            'from_number' => config('services.twilio.from')
        ]);
    }
}
