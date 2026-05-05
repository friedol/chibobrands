<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupStorageSymlink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:setup-symlink {--force : Force creation even if symlink exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup storage symlink for shared hosting environments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Setting up storage symlink for shared hosting...');
        
        $storagePath = storage_path('app/public');
        $publicStorage = public_path('storage');
        
        // Check if storage directory exists
        if (!File::exists($storagePath)) {
            $this->error('❌ Storage directory does not exist: ' . $storagePath);
            $this->info('Creating storage directory...');
            File::makeDirectory($storagePath, 0755, true);
        }
        
        // Check if symlink already exists
        if (File::exists($publicStorage) && !$this->option('force')) {
            if (is_link($publicStorage)) {
                $this->info('✅ Storage symlink already exists: ' . $publicStorage);
                $this->info('Target: ' . readlink($publicStorage));
                return 0;
            } else {
                $this->warn('⚠️ Storage path exists but is not a symlink: ' . $publicStorage);
                if (!$this->confirm('Do you want to remove it and create a symlink?')) {
                    return 1;
                }
            }
        }
        
        // Remove existing file/directory
        if (File::exists($publicStorage)) {
            $this->info('Removing existing storage path...');
            if (is_dir($publicStorage)) {
                File::deleteDirectory($publicStorage);
            } else {
                File::delete($publicStorage);
            }
        }
        
        // Create symlink
        try {
            if (symlink($storagePath, $publicStorage)) {
                $this->info('✅ Storage symlink created successfully!');
                $this->info('Symlink: ' . $publicStorage . ' → ' . $storagePath);
                
                // Test the symlink
                $testFile = $publicStorage . '/test-' . time() . '.txt';
                if (file_put_contents($testFile, 'Test file created at ' . now())) {
                    $this->info('✅ Symlink test successful!');
                    unlink($testFile);
                } else {
                    $this->warn('⚠️ Symlink created but test failed. Check permissions.');
                }
                
                return 0;
            } else {
                $this->error('❌ Failed to create symlink!');
                $this->error('This might be due to server restrictions or permissions.');
                $this->info('Try running: php artisan storage:link');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Error creating symlink: ' . $e->getMessage());
            return 1;
        }
    }
}





