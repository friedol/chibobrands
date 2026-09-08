<?php

namespace App\Jobs;

use App\Models\SmsCampaign;
use App\Services\SmsApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSmsCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $campaign;

    /**
     * Create a new job instance.
     */
    public function __construct(SmsCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Execute the job.
     */
    public function handle(SmsApiService $smsApiService): void
    {
        $campaign = $this->campaign->fresh(['recipients']);

        if (!$campaign || $campaign->status === 'completed') {
            return;
        }

        $campaign->update(['status' => 'processing']);

        $sentCount = 0;
        $failedCount = 0;

        foreach ($campaign->recipients as $recipient) {
            // Skip already processed recipients to prevent duplicate sending
            if ($recipient->status === 'sent') {
                $sentCount++;
                continue;
            }

            try {
                $result = $smsApiService->sendSMS($recipient->phone_number, $campaign->message);

                if (isset($result['success']) && $result['success']) {
                    $recipient->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'error_message' => null,
                    ]);
                    $sentCount++;
                } else {
                    $recipient->update([
                        'status' => 'failed',
                        'error_message' => $result['message'] ?? 'SMS API Error',
                    ]);
                    $failedCount++;
                }
            } catch (\Exception $e) {
                Log::error('Error processing bulk SMS recipient ID: ' . $recipient->id, [
                    'error' => $e->getMessage()
                ]);
                $recipient->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
                $failedCount++;
            }
        }

        $campaign->update([
            'status' => 'completed',
            'total_sent' => $sentCount,
            'total_failed' => $failedCount,
            'completed_at' => now(),
        ]);
    }
}
