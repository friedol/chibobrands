<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;

class ClearCustomersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all customer accounts (retail and wholesale) from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing all customer accounts...');
        
        // Count customers before deletion
        $retailCount = Customer::where('is_wholesale', false)->count();
        $wholesaleCount = Customer::where('is_wholesale', true)->count();
        $totalCount = $retailCount + $wholesaleCount;
        
        if ($totalCount === 0) {
            $this->info('No customers found in the database.');
            return 0;
        }
        
        $this->warn("Found {$totalCount} customers:");
        $this->warn("- Retail customers: {$retailCount}");
        $this->warn("- Wholesale customers: {$wholesaleCount}");
        
        if (!$this->confirm('Are you sure you want to delete all customer accounts? This action cannot be undone.')) {
            $this->info('Operation cancelled.');
            return 0;
        }
        
        try {
            // Delete all customers
            Customer::truncate();
            
            // Clear related notifications
            \DB::table('notifications')->whereIn('type', [
                'App\\Notifications\\NewRegistrationPending',
                'App\\Notifications\\RegistrationPendingCustomer',
                'App\\Notifications\\AccountVerified'
            ])->delete();
            
            $this->info("✓ Successfully deleted {$totalCount} customer accounts!");
            $this->info('✓ Related notifications cleared.');
            $this->info('You can now start fresh with new customer registrations.');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('✗ Failed to delete customers!');
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
