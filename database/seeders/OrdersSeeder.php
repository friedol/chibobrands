<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $retailCustomers = User::where('role', 'retail_customer')->get();
        $wholesaleCustomers = User::where('role', 'wholesale_customer')->get();
        $products = Product::all();
        
        // Create 10 retail orders
        for ($i = 0; $i < 10; $i++) {
            $customer = $retailCustomers->random();
            $order = Order::create([
                'user_id' => $customer->id,
                'order_code' => Order::generateOrderCode(),
                'total_amount' => 0, // Will be calculated
                'payment_status' => fake()->randomElement(['pending', 'paid', 'unpaid']),
                'approval_status' => fake()->randomElement(['requested', 'approved', 'cancelled']),
                'notes' => fake()->optional()->sentence(),
            ]);
            
            // Create 1-3 order items
            $itemCount = rand(1, 3);
            $totalAmount = 0;
            
            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products->random();
                $quantity = rand(1, 5);
                $unitPrice = $product->base_price; // Retail price
                $subtotal = $unitPrice * $quantity;
                $totalAmount += $subtotal;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
            }
            
            $order->update(['total_amount' => $totalAmount]);
        }
        
        // Create 10 wholesale orders
        for ($i = 0; $i < 10; $i++) {
            $customer = $wholesaleCustomers->random();
            $order = Order::create([
                'user_id' => $customer->id,
                'order_code' => Order::generateOrderCode(),
                'total_amount' => 0, // Will be calculated
                'payment_status' => fake()->randomElement(['pending', 'paid', 'unpaid']),
                'approval_status' => fake()->randomElement(['requested', 'approved', 'cancelled']),
                'notes' => fake()->optional()->sentence(),
            ]);
            
            // Create 2-5 order items (wholesale orders typically have more items)
            $itemCount = rand(2, 5);
            $totalAmount = 0;
            
            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products->random();
                $quantity = rand(5, 20); // Larger quantities for wholesale
                $unitPrice = $product->wholesale_price; // Wholesale price
                $subtotal = $unitPrice * $quantity;
                $totalAmount += $subtotal;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
            }
            
            $order->update(['total_amount' => $totalAmount]);
        }
    }
}