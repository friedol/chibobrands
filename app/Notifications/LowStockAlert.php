<?php

namespace App\Notifications;

use App\Models\Product;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public $product;

    /**
     * Create a new notification instance.
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
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
        $product = $this->product;
        
        return (new MailMessage)
            ->subject("Low Stock Alert - {$product->name}")
            ->greeting("Hello Admin,")
            ->line("⚠️ **LOW STOCK ALERT**")
            ->line("The following product is running low on stock:")
            ->line("**Product:** {$product->name}")
            ->line("**Current Stock:** {$product->stock} units")
            ->line("**Category:** {$product->category->name}")
            ->line("**Status:** " . ($product->stock <= 5 ? 'CRITICAL' : 'LOW'))
            ->action('Update Stock', url("/admin/products/{$product->id}/edit"))
            ->line('Please restock this product as soon as possible to avoid stockouts.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'current_stock' => $this->product->stock,
            'category_name' => $this->product->category->name,
            'message' => "Low stock alert for {$this->product->name}",
            'type' => 'low_stock',
        ];
    }

    /**
     * Send SMS notification to admin
     */
    public function toSms($notifiable)
    {
        $smsService = app(SmsService::class);
        $message = "LOW STOCK: {$this->product->name} has only {$this->product->stock} units left. Please restock.";
        
        return $smsService->send($notifiable->phone, $message);
    }
}
