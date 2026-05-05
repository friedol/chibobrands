<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadFollowUpReminder extends Notification
{
    use Queueable;

    protected $lead;

    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'lead_follow_up',
            'message' => "Reminder: Follow up with lead '{$this->lead->customer_name}' regarding '{$this->lead->product_requested}'.",
            'lead_id' => $this->lead->id,
        ];
    }
}
