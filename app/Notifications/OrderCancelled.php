<?php

namespace App\Notifications;

use App\Models\Order;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
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
        $order = $this->order;
        $smsService = app(SmsService::class);
        $whatsappLink = $smsService->sendWhatsAppLink($notifiable->phone, "Hello, I need help with Order {$order->order_code}");
        
        return (new MailMessage)
            ->subject("Order Cancelled: {$order->order_code}")
            ->greeting("Hello {$notifiable->name},")
            ->line("We regret to inform you that your order **{$order->order_code}** has been cancelled.")
            ->line("**Order Details:**")
            ->line("Order Code: {$order->order_code}")
            ->line("Total: TZS " . number_format($order->total_amount, 0))
            ->line("Status: Cancelled")
            ->line("**Reason:** Please contact us for more details about the cancellation.")
            ->action('Contact us on WhatsApp', $whatsappLink)
            ->line('If you have any questions, please contact us on WhatsApp.')
            ->line('We apologize for any inconvenience caused.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_code' => $this->order->order_code,
            'total_amount' => $this->order->total_amount,
            'message' => "Your order {$this->order->order_code} has been cancelled",
            'type' => 'order_cancelled',
        ];
    }

    /**
     * Send SMS notification to customer
     */
    public function toSms($notifiable)
    {
        $smsService = app(SmsService::class);
        $message = "Hi {$notifiable->name}, your order {$this->order->order_code} has been cancelled. Contact us on WhatsApp +255687183330 for details.";
        
        return $smsService->send($notifiable->phone, $message);
    }
}
