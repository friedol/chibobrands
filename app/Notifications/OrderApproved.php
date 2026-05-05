<?php

namespace App\Notifications;

use App\Models\Order;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderApproved extends Notification implements ShouldQueue
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
        $orderItems = $order->items()->with('product')->get();
        $smsService = app(SmsService::class);
        $whatsappLink = $smsService->sendWhatsAppLink($notifiable->phone, "Hello, I need help with Order {$order->order_code}");
        
        $mailMessage = (new MailMessage)
            ->subject("Your CHIBO BRAND Order {$order->order_code} is Approved")
            ->greeting("Hello {$notifiable->name},")
            ->line("Good news! Your order **{$order->order_code}** has been approved.")
            ->line("**Order Details:**")
            ->line("Order Code: {$order->order_code}")
            ->line("Total: TZS " . number_format($order->total_amount, 0))
            ->line("Status: Approved")
            ->line("**Next Steps:** Please complete payment or confirm delivery details with our sales team.");

        // Add product details
        if ($orderItems->count() > 0) {
            $mailMessage->line("**Your Products:**");
            foreach ($orderItems as $item) {
                $mailMessage->line("- {$item->product->name} x {$item->quantity} — TZS " . number_format($item->subtotal, 0));
            }
        }

        $mailMessage->action('Chat on WhatsApp', $whatsappLink)
            ->line('If you need to chat, click the WhatsApp button above.')
            ->line('Thank you for choosing CHIBO BRAND!');

        return $mailMessage;
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
            'message' => "Your order {$this->order->order_code} has been approved",
            'type' => 'order_approved',
        ];
    }

    /**
     * Send SMS notification to customer
     */
    public function toSms($notifiable)
    {
        $smsService = app(SmsService::class);
        $message = "Hi {$notifiable->name}, your CHIBO BRAND order {$this->order->order_code} has been approved. Total: TZS " . number_format($this->order->total_amount, 0) . ". We will notify you for delivery. - CHIBO BRAND";
        
        return $smsService->send($notifiable->phone, $message);
    }
}
