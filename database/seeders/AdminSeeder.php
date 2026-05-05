<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        Admin::create([
            'name' => 'CHIBO BRAND Admin',
            'email' => 'admin@chibobrand.com',
            'password' => Hash::make('admin123'),
            'is_active' => true,
            'role' => 'super_admin',
        ]);

        // Create additional admin users if needed
        Admin::create([
            'name' => 'Manager',
            'email' => 'manager@chibobrand.com',
            'password' => Hash::make('manager123'),
            'is_active' => true,
            'role' => 'manager',
        ]);
    }
}