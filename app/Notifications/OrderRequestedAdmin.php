<?php

namespace App\Notifications;

use App\Models\Order;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderRequestedAdmin extends Notification implements ShouldQueue
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
        
        $mailMessage = (new MailMessage)
            ->subject("New Order Request: {$order->order_code}")
            ->greeting("Hello Admin,")
            ->line("A new order request has been created on CHIBO BRAND.")
            ->line("**Order Details:**")
            ->line("Order Code: {$order->order_code}")
            ->line("Customer: {$order->user->name}")
            ->line("Phone: {$order->user->phone}")
            ->line("Email: {$order->user->email}")
            ->line("Total: TZS " . number_format($order->total_amount, 0));

        // Add product details
        if ($orderItems->count() > 0) {
            $mailMessage->line("**Products:**");
            foreach ($orderItems as $item) {
                $mailMessage->line("- {$item->product->name} x {$item->quantity} — TZS " . number_format($item->subtotal, 0));
            }
        }

        $mailMessage->action('View Order', url("/admin/orders/{$order->id}"))
            ->line('Please confirm or cancel this request in the admin panel.');

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
            'customer_name' => $this->order->user->name,
            'total_amount' => $this->order->total_amount,
            'message' => "New order request from {$this->order->user->name}",
            'type' => 'order_requested',
        ];
    }

    /**
     * Send SMS notification to admin
     */
    public function toSms($notifiable)
    {
        $smsService = app(SmsService::class);
        $message = "New order {$this->order->order_code} from {$this->order->user->name}. Total: TZS " . number_format($this->order->total_amount, 0) . ". Check admin panel.";
        
        return $smsService->send($notifiable->phone, $message);
    }
}
