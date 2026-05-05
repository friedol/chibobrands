<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminUserCreated extends Notification
{
    use Queueable;

    protected $password;

    /**
     * Create a new notification instance.
     */
    public function __construct($password)
    {
        $this->password = $password;
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
        $loginUrl = route('login');
        
        return (new MailMessage)
            ->subject("CHIBO BRAND — Your Admin Account Has Been Created")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your admin account has been successfully created on CHIBO BRAND.")
            ->line("**Your Login Credentials:**")
            ->line("Email: {$notifiable->email}")
            ->line("Password: {$this->password}")
            ->line("**Important:**")
            ->line("• Please log in and change your password after your first login")
            ->line("• Keep your credentials secure and do not share them with anyone")
            ->line("• Contact the system administrator if you have any questions")
            ->action('Login to Admin Panel', $loginUrl)
            ->line('Welcome to the CHIBO BRAND admin team!');
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
            'message' => "Your admin account has been created. Check your email for login credentials.",
            'type' => 'admin_user_created',
        ];
    }
}








