<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateHikvisionToken extends Command
{
    protected $signature   = 'hikvision:generate-token';
    protected $description = 'Generate a secure HIKVISION_SYNC_TOKEN and show the .env line to add.';

    public function handle(): int
    {
        $token = Str::random(64);
        $this->info('Generated Hikvision sync token:');
        $this->line('');
        $this->line("HIKVISION_SYNC_TOKEN={$token}");
        $this->line('');
        $this->warn('Add the line above to your .env file, then run: php artisan config:clear');
        $this->warn('Store the same token in your office sync agent configuration.');
        return self::SUCCESS;
    }
}
