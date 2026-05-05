<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationPendingCustomer extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database']; // Send both email and database notification
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("CHIBO BRAND — Registration Received")
            ->greeting("Hello {$notifiable->name},")
            ->line("Thank you for registering with CHIBO BRAND!")
            ->line("Your registration has been received and is currently pending verification by our admin team.")
            ->line("**What happens next?**")
            ->line("• Our team will review your account details")
            ->line("• You will receive an email notification once your account is verified")
            ->line("• After verification, you can log in and start using our system")
            ->line("**Important:** You will not be able to log in until your account is verified.")
            ->line("This process usually takes 1-2 business days. If you have any questions, please contact our support team.")
            ->line('Thank you for your patience!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $notifiable->id,
            'user_name' => $notifiable->name,
            'message' => "Your registration is pending verification",
            'type' => 'registration_pending',
        ];
    }
}
