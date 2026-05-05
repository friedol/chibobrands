<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Services\CustomerAnalyticsService;
use Illuminate\Support\Facades\Log;

class UpdateCustomerFollowUpStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:update-follow-ups';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Updates follow-up status for all customers based on their expected next order dates';

    /**
     * Execute the console command.
     */
    public function handle(CustomerAnalyticsService $analyticsService)
    {
        $this->info('Starting customer follow-up status update...');
        
        $customers = Customer::whereNotNull('next_expected_order_date')
            ->orWhereNotNull('manual_follow_up_date')
            ->get();

        $this->info("Processing {$customers->count()} customers...");

        $count = 0;
        $notificationsSent = 0;

        foreach ($customers as $customer) {
            try {
                $oldStatus = $customer->follow_up_status;
                // We use the service to determine the status logic consistently
                $status = $analyticsService->determineFollowUpStatus($customer);
                
                if ($oldStatus !== $status) {
                    $customer->update(['follow_up_status' => $status]);
                    $count++;

                    // Notify assigned saler or admins if status became Due Today or Overdue
                    if (in_array($status, ['Due Today', 'Overdue'])) {
                        $this->notifyStaff($customer, $status === 'Overdue' ? 'overdue' : 'due');
                        $notificationsSent++;
                    }
                }

                // Periodic check for High Value customers who are significantly overdue
                if ($customer->priority_ranking > 100 && $customer->follow_up_status === 'Overdue') {
                    $expectedDate = $customer->effective_follow_up_date;
                    if ($expectedDate && now()->diffInDays($expectedDate) >= 7) {
                        // Send a high-value alert if not sent recently (e.g., once a week)
                        // This logic is a bit simplified, ideally we'd track last_notified_at
                        if (now()->dayOfWeek === \Carbon\Carbon::MONDAY) {
                            $this->notifyStaff($customer, 'high_value');
                        }
                    }
                }

            } catch (\Exception $e) {
                $this->error("Failed to update status for customer ID {$customer->id}: " . $e->getMessage());
                Log::error("Follow-up status update error", ['customer_id' => $customer->id, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Finished! Updated {$count} customer statuses. Sent {$notificationsSent} notifications.");
        return Command::SUCCESS;
    }

    /**
     * Notify relevant staff about a customer follow-up.
     */
    protected function notifyStaff(Customer $customer, $type)
    {
        $saler = \App\Models\User::find($customer->added_by);
        
        // If saler exists, notify them
        if ($saler) {
            $saler->notify(new \App\Notifications\CustomerFollowUpReminder($customer, $type));
        }

        // Also notify admins for high value or overdue
        if ($type === 'high_value' || $type === 'overdue') {
            $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
            foreach ($admins as $admin) {
                // Don't notify twice if admin added the customer
                if (!$saler || $admin->id !== $saler->id) {
                    $admin->notify(new \App\Notifications\CustomerFollowUpReminder($customer, $type));
                }
            }
        }
    }
}
