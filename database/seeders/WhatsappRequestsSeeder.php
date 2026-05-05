<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WhatsappRequest;
use App\Models\Order;
use Carbon\Carbon;

class WhatsappRequestsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::all();
        
        foreach ($orders as $order) {
            // Create WhatsApp request for each order
            $message = $this->generateWhatsAppMessage($order);
            
            WhatsappRequest::create([
                'order_id' => $order->id,
                'customer_phone' => $order->user->phone,
                'message_sent' => $message,
                'sent_at' => fake()->dateTimeBetween($order->created_at, now()),
            ]);
        }
    }
    
    private function generateWhatsAppMessage(Order $order): string
    {
        $message = "Hello CHIBO BRAND 👋,\nI'd like to place this order:\n\n";
        
        foreach ($order->items as $item) {
            $message .= "• {$item->product->name} (x{$item->quantity})\n";
            $message .= "  Price: TZS " . number_format($item->unit_price, 0) . " each\n";
            $message .= "  Subtotal: TZS " . number_format($item->subtotal, 0) . "\n\n";
        }
        
        $message .= "Total: TZS " . number_format($order->total_amount, 0) . "\n";
        $message .= "Order #: {$order->order_code}\n\n";
        $message .= "My name: {$order->user->name}\n";
        $message .= "Phone: {$order->user->phone}\n";
        $message .= "Email: {$order->user->email}\n";
        $message .= "Domain: " . ($order->user->role === 'wholesale_customer' ? 'b2b.chibobrand.com' : 'chibobrand.com');
        
        return $message;
    }
}