<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\ContactMessage;

class SendContactReplyEmail implements ShouldQueue
{
    use Queueable;

    public $message;
    public $emailSubject;
    public $emailReply;
    public $adminName;

    /**
     * Create a new job instance.
     */
    public function __construct(ContactMessage $message, $emailSubject, $emailReply, $adminName)
    {
        $this->message = $message;
        $this->emailSubject = $emailSubject;
        $this->emailReply = $emailReply;
        $this->adminName = $adminName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Sending contact reply email', [
                'message_id' => $this->message->id,
                'to' => $this->message->email
            ]);

            Mail::send('emails.contact-reply', [
                'customerName' => $this->message->name,
                'originalMessage' => $this->message->message,
                'originalSubject' => $this->message->subject,
                'replyMessage' => $this->emailReply,
                'adminName' => $this->adminName,
            ], function($mail) {
                $mail->to($this->message->email, $this->message->name)
                     ->subject($this->emailSubject);
                
                if (config('mail.from.address')) {
                    $mail->from(config('mail.from.address'), config('mail.from.name') ?? 'CHIBO BRAND');
                }
            });

            Log::info('Contact reply email sent successfully', [
                'message_id' => $this->message->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send contact reply email', [
                'message_id' => $this->message->id,
                'error' => $e->getMessage()
            ]);
            
            // Re-throw to mark job as failed
            throw $e;
        }
    }
}
