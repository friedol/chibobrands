<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update super admin user
        User::updateOrCreate(
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
        
        $this->command->info('Super Admin user created/updated successfully!');
        $this->command->info('Email: superadmin@chibobrand.com');
        $this->command->info('Password: password');
    }
}
