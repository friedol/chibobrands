<?php

namespace App\Notifications;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerFollowUpReminder extends Notification
{
    use Queueable;

    protected $customer;
    protected $type;

    /**
     * Create a new notification instance.
     * 
     * @param Customer $customer
     * @param string $type (due, overdue, high_value)
     */
    public function __construct(Customer $customer, $type = 'due')
    {
        $this->customer = $customer;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $title = '';
        $message = '';

        switch ($this->type) {
            case 'overdue':
                $title = '🔴 Overdue Follow-up';
                $message = "{$this->customer->name} is overdue for a follow-up. Last task was on {$this->customer->last_order_date?->format('M d')}.";
                break;
            case 'high_value':
                $title = '⭐ High Value Customer Alert';
                $message = "{$this->customer->name} (Priority #{$this->customer->priority_ranking}) has not placed an order in a long time.";
                break;
            case 'due':
            default:
                $title = '🟡 Follow-up Due Today';
                $message = "It's the best time to contact {$this->customer->name} for their next design task.";
                break;
        }

        return [
            'title' => $title,
            'message' => $message,
            'customer_id' => $this->customer->id,
            'action_url' => route('admin.customer-data-center.show', $this->customer->id),
            'type' => 'customer_follow_up'
        ];
    }
}
