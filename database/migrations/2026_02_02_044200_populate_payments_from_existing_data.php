<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\DesignTask;
use App\Models\Order;
use App\Models\Payment;

return new class extends Migration
{
    public function up(): void
    {
        // Create payments from Design Tasks with amount_paid > 0
        $tasks = DesignTask::where('amount_paid', '>', 0)->get();
        
        foreach ($tasks as $task) {
            // Check if payment already exists
            $existingPayment = Payment::where('design_task_id', $task->id)->first();
            
            if (!$existingPayment) {
                Payment::create([
                    'customer_id' => $task->customer_id,
                    'design_task_id' => $task->id,
                    'amount' => $task->amount_paid,
                    'payment_method' => 'cash', // Default, you can update manually later
                    'date' => $task->updated_at ?? $task->created_at,
                    'seller_id' => $task->saler_id,
                    'department_id' => $task->department_id,
                    'invoice_reference' => 'DT-' . $task->id,
                    'notes' => 'Auto-generated from Design Task #' . $task->id,
                ]);
            }
        }
        
        // Create payments from Orders with amount_paid > 0
        $orders = Order::where('amount_paid', '>', 0)->get();
        
        foreach ($orders as $order) {
            // Check if payment already exists
            $existingPayment = Payment::where('order_id', $order->id)->first();
            
            if (!$existingPayment) {
                Payment::create([
                    'customer_id' => $order->customer_id,
                    'order_id' => $order->id,
                    'amount' => $order->amount_paid,
                    'payment_method' => 'cash', // Default
                    'date' => $order->updated_at ?? $order->created_at,
                    'seller_id' => $order->saler_id,
                    'department_id' => $order->department_id,
                    'invoice_reference' => 'ORD-' . $order->order_code,
                    'notes' => 'Auto-generated from Order #' . $order->order_code,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Remove auto-generated payments
        Payment::where('notes', 'like', 'Auto-generated from%')->delete();
    }
};
