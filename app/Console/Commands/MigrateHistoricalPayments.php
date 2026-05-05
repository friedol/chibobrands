<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\DesignTask;
use App\Models\Payment;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class MigrateHistoricalPayments extends Command
{
    protected $signature = 'finance:migrate-historical';
    protected $description = 'Create default departments and migrate historical payments from orders and design tasks';

    public function handle()
    {
        $this->info('Starting migration...');

        // 1. Create Default Departments if none exist
        if (Department::count() === 0) {
            $this->info('Creating default departments...');
            $departments = ['Printing', 'Design', 'Branding', 'Photography', 'Marketing'];
            foreach ($departments as $name) {
                Department::firstOrCreate(
                    ['slug' => \Illuminate\Support\Str::slug($name)],
                    ['name' => $name]
                );
            }
        }

        $defaultDept = Department::first();

        // 2. Migrate Order Payments
        $this->info('Migrating Order payments...');
        $orders = Order::where('amount_paid', '>', 0)->get();
        foreach ($orders as $order) {
            // Assign default department if null
            if (!$order->department_id) {
                $order->update(['department_id' => $order->department_id ?: $defaultDept->id]);
            }
            
            Payment::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'customer_id' => $order->user_id,
                    'amount' => $order->amount_paid,
                    'payment_method' => 'cash',
                    'date' => $order->created_at,
                    'seller_id' => $order->saler_id ?: 1,
                    'department_id' => $order->department_id,
                    'notes' => 'Imported from Order #' . $order->order_code
                ]
            );
        }

        // 3. Migrate Design Task Payments
        $this->info('Migrating Design Task payments...');
        $tasks = DesignTask::where('amount_paid', '>', 0)->get();
        foreach ($tasks as $task) {
            // Assign default department if null
            if (!$task->department_id) {
                $task->update(['department_id' => $task->department_id ?: $defaultDept->id]);
            }

            Payment::firstOrCreate(
                ['design_task_id' => $task->id],
                [
                    'customer_id' => $task->customer_id,
                    'amount' => $task->amount_paid,
                    'payment_method' => 'cash',
                    'date' => $task->created_at,
                    'seller_id' => $task->receptionist_id ?: 1,
                    'department_id' => $task->department_id,
                    'notes' => 'Imported from Task #' . $task->task_code
                ]
            );
        }

        $this->info('Migration completed successfully!');
    }
}
