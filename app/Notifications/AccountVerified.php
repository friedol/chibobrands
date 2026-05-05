<?php

namespace App\Notifications;

use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountVerified extends Notification
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
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("CHIBO BRAND — Account Verified")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your account on CHIBO BRAND has been verified. You can now log in and view your order history.")
            ->line("**What you can do now:**")
            ->line("• View your order history")
            ->line("• Track order status")
            ->line("• Update your profile")
            ->line("• Place new orders")
            ->action('Login to Your Account', url('/login'))
            ->line('Thank you for choosing CHIBO BRAND!');
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
            'message' => "Your CHIBO BRAND account has been verified",
            'type' => 'account_verified',
        ];
    }

    /**
     * Send SMS notification to customer
     */
    public function toSms($notifiable)
    {
        $smsService = app(SmsService::class);
        $message = "Hi {$notifiable->name}, your CHIBO BRAND account has been verified. You can now log in.";
        
        return $smsService->send($notifiable->phone, $message);
    }
}
