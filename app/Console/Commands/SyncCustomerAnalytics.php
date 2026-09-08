<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Services\CustomerAnalyticsService;

class SyncCustomerAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:sync-analytics {--chunk=100 : Number of customers to process at a time}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Recalculate purchase stats, total spent, reorder intervals, and is_repeated flags for all historical customers';

    /**
     * Execute the console command.
     */
    public function handle(CustomerAnalyticsService $analyticsService)
    {
        $this->info('Starting Customer Analytics Sync...');

        $totalCustomers = Customer::count();
        $this->info("Total customers to process: {$totalCustomers}");

        $bar = $this->output->createProgressBar($totalCustomers);
        $bar->start();

        $processedCount = 0;
        $repeatedCount = 0;

        Customer::chunk((int) $this->option('chunk'), function ($customers) use ($analyticsService, $bar, &$processedCount, &$repeatedCount) {
            foreach ($customers as $customer) {
                try {
                    // This method computes total_spent, total_orders, avg_reorder_interval, next_expected_order_date,
                    // and calls $customer->recalculatePurchaseStats() to set purchase_count and is_repeated flag!
                    $analyticsService->recalculateCustomerAnalytics($customer->id);
                    
                    $customer->refresh();
                    if ($customer->is_repeated) {
                        $repeatedCount++;
                    }
                    
                    $processedCount++;
                } catch (\Exception $e) {
                    $this->error("\nFailed to sync customer ID {$customer->id}: " . $e->getMessage());
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->info("\n\nSync complete!");
        $this->info("Successfully processed {$processedCount} / {$totalCustomers} customers.");
        $this->info("Identified and updated {$repeatedCount} Repeated Customers.");

        return Command::SUCCESS;
    }
}
