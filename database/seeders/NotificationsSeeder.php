<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use App\Models\Order;

class NotificationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $orders = Order::all();
        
        foreach ($users as $user) {
            // Create registration verification notification for unverified users
            if (!$user->verified && $user->role !== 'admin') {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'registration_verification',
                    'message' => 'Your account registration is pending verification. You will be notified once approved.',
                    'status' => 'unread',
                ]);
            }
            
            // Create order-related notifications
            $userOrders = $orders->where('user_id', $user->id);
            foreach ($userOrders as $order) {
                if ($order->approval_status === 'approved') {
                    Notification::create([
                        'user_id' => $user->id,
                        'type' => 'order_approved',
                        'message' => "Your order #{$order->order_code} has been approved and is being processed.",
                        'status' => fake()->randomElement(['read', 'unread']),
                    ]);
                } elseif ($order->approval_status === 'cancelled') {
                    Notification::create([
                        'user_id' => $user->id,
                        'type' => 'order_cancelled',
                        'message' => "Your order #{$order->order_code} has been cancelled. Please contact us for more information.",
                        'status' => fake()->randomElement(['read', 'unread']),
                    ]);
                } else {
                    Notification::create([
                        'user_id' => $user->id,
                        'type' => 'order_placed',
                        'message' => "Your order #{$order->order_code} has been received and is pending approval.",
                        'status' => fake()->randomElement(['read', 'unread']),
                    ]);
                }
            }
            
            // Create some random notifications
            $randomCount = rand(1, 3);
            for ($i = 0; $i < $randomCount; $i++) {
                $notificationTypes = [
                    'system_update' => 'System maintenance scheduled for tomorrow at 2 AM.',
                    'new_product' => 'New products have been added to our catalog. Check them out!',
                    'promotion' => 'Special promotion: 20% off on all t-shirts this week!',
                    'payment_reminder' => 'Payment reminder: Please complete your pending order payment.',
                ];
                
                $type = fake()->randomElement(array_keys($notificationTypes));
                
                Notification::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'message' => $notificationTypes[$type],
                    'status' => fake()->randomElement(['read', 'unread']),
                ]);
            }
        }
    }
}