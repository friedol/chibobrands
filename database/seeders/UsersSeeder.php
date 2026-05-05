<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Msami Damian',
            'email' => 'admin@chibobrand.com',
            'phone' => '+255687183330',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'verified' => true,
        ]);

        // Create retail customers
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone' => '+255' . fake()->numerify('##########'),
                'password' => Hash::make('password123'),
                'role' => 'retail_customer',
                'verified' => fake()->boolean(80), // 80% verified
            ]);
        }

        // Create wholesale customers
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => fake()->company() . ' - ' . fake()->name(),
                'email' => fake()->unique()->companyEmail(),
                'phone' => '+255' . fake()->numerify('##########'),
                'password' => Hash::make('password123'),
                'role' => 'wholesale_customer',
                'verified' => fake()->boolean(90), // 90% verified
            ]);
        }
    }
}