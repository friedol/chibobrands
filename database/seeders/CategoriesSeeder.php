<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Tshirts',
                'description' => 'Custom printed t-shirts in various sizes and colors'
            ],
            [
                'name' => 'Caps',
                'description' => 'Branded caps and hats for promotional purposes'
            ],
            [
                'name' => 'Office Branding',
                'description' => 'Professional office branding materials and signage'
            ],
            [
                'name' => 'Large Format',
                'description' => 'Large format printing for banners, posters, and displays'
            ],
            [
                'name' => 'Reflectors',
                'description' => 'High-visibility reflective materials and safety gear'
            ],
            [
                'name' => 'Umbrella',
                'description' => 'Custom branded umbrellas for promotional campaigns'
            ],
            [
                'name' => 'Silicone Wristbands',
                'description' => 'Custom silicone wristbands for events and promotions'
            ],
            [
                'name' => 'Tyre Cover',
                'description' => 'Custom tyre covers for vehicle branding'
            ],
            [
                'name' => 'Stickers',
                'description' => 'Custom stickers and decals for various applications'
            ],
            [
                'name' => 'Cards (Business, Thank You)',
                'description' => 'Professional business cards and thank you cards'
            ],
            [
                'name' => 'Flyers',
                'description' => 'Marketing flyers and promotional materials'
            ],
            [
                'name' => 'Advertising Stands',
                'description' => 'Portable advertising stands and displays'
            ],
            [
                'name' => 'Gift Items',
                'description' => 'Custom branded gift items and promotional products'
            ],
            [
                'name' => 'Lanyards',
                'description' => 'Custom lanyards for events and identification'
            ],
            [
                'name' => 'PVC IDs',
                'description' => 'Professional PVC ID cards and badges'
            ],
            [
                'name' => 'Car Branding',
                'description' => 'Vehicle branding and car wrap services'
            ],
            [
                'name' => 'Exhibition Tents',
                'description' => 'Custom branded exhibition tents and canopies'
            ],
            [
                'name' => 'Department Signage',
                'description' => 'Professional department and directional signage'
            ],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'sort_order' => 0,
                'is_active' => true,
            ]);
        }
    }
}