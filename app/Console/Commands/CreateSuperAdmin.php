<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-superadmin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or update super admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::updateOrCreate(
            ['email' => 'superadmin@chibobrand.com'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@chibobrand.com',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'verified' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        
        $this->info('Super Admin user created/updated successfully!');
        $this->info('Email: superadmin@chibobrand.com');
        $this->info('Password: password');
        
        return 0;
    }
}
