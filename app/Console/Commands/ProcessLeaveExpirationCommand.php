<?php

namespace App\Console\Commands;

use App\Services\HRLeaveAutomationService;
use Illuminate\Console\Command;

class ProcessLeaveExpirationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hr:process-leave-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automate employee leave expiration, restore employee Active status, and log audit entries.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting HR Leave Automation process...');

        $results = HRLeaveAutomationService::runAutomation();

        $this->info("Activated Leaves: {$results['activated']}");
        $this->info("Completed Expired Leaves: {$results['completed']}");
        $this->info('HR Leave Automation executed successfully.');

        return Command::SUCCESS;
    }
}
