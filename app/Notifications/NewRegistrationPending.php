<?php

namespace App\Notifications;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRegistrationPending extends Notification
{
    use Queueable;

    public $customer;

    /**
     * Create a new notification instance.
     */
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Only database for now, mail can be added later when SMTP is configured
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $customer = $this->customer;
        $customerType = $customer->is_wholesale ? 'Wholesale Customer' : 'Retail Customer';
        
        return (new MailMessage)
            ->subject("New Customer Registration - {$customer->name}")
            ->greeting("Hello Admin,")
            ->line("A new customer has registered on CHIBO BRAND and is waiting for verification.")
            ->line("**Customer Details:**")
            ->line("Name: {$customer->name}")
            ->line("Email: {$customer->email}")
            ->line("Phone: {$customer->phone}")
            ->line("Type: {$customerType}")
            ->line("Registration Date: {$customer->created_at->format('F d, Y H:i')}")
            ->action('Review Customer', url("/admin/customers/{$customer->id}"))
            ->line('Please review and verify this customer account in the admin panel.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'customer_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'customer_email' => $this->customer->email,
            'customer_phone' => $this->customer->phone,
            'message' => "New customer registration from {$this->customer->name}",
            'type' => 'new_registration',
        ];
    }
}
