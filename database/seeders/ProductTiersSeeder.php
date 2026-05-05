<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductPriceTier;
use App\Models\Category;

class ProductTiersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all categories
        $categories = Category::all();
        
        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Please run CategorySeeder first.');
            return;
        }

        // Sample product data with attributes and pricing tiers
        $products = [
            [
                'name' => 'Premium Cotton T-Shirt',
                'description' => 'High-quality cotton t-shirt perfect for branding and customization.',
                'brand' => 'CHIBO',
                'material' => '100% Cotton',
                'color_options' => ['Red', 'Black', 'White', 'Navy Blue'],
                'size_options' => ['S', 'M', 'L', 'XL', 'XXL'],
                'printing_type' => 'Digital Print',
                'specifications' => ['Weight' => '200g', 'Print' => 'Full Color', 'Wash' => 'Machine Washable'],
                'base_price' => 1500,
                'wholesale_price' => 1200,
                'tiers' => [
                    ['min' => 1, 'max' => 9, 'price' => 1500, 'is_wholesale' => false],
                    ['min' => 10, 'max' => 49, 'price' => 1300, 'is_wholesale' => false],
                    ['min' => 50, 'max' => null, 'price' => 1100, 'is_wholesale' => false],
                    ['min' => 1, 'max' => 9, 'price' => 1200, 'is_wholesale' => true],
                    ['min' => 10, 'max' => 49, 'price' => 1000, 'is_wholesale' => true],
                    ['min' => 50, 'max' => null, 'price' => 800, 'is_wholesale' => true],
                ]
            ],
            [
                'name' => 'Embroidered Baseball Cap',
                'description' => 'Classic baseball cap with premium embroidery options.',
                'brand' => 'CHIBO',
                'material' => 'Cotton/Polyester Blend',
                'color_options' => ['Black', 'White', 'Red', 'Navy'],
                'size_options' => ['One Size', 'Adjustable'],
                'printing_type' => 'Embroidery',
                'specifications' => ['Weight' => '150g', 'Print' => 'Embroidery', 'Adjustable' => 'Yes'],
                'base_price' => 2500,
                'wholesale_price' => 2000,
                'tiers' => [
                    ['min' => 1, 'max' => 9, 'price' => 2500, 'is_wholesale' => false],
                    ['min' => 10, 'max' => 49, 'price' => 2200, 'is_wholesale' => false],
                    ['min' => 50, 'max' => null, 'price' => 1900, 'is_wholesale' => false],
                    ['min' => 1, 'max' => 9, 'price' => 2000, 'is_wholesale' => true],
                    ['min' => 10, 'max' => 49, 'price' => 1800, 'is_wholesale' => true],
                    ['min' => 50, 'max' => null, 'price' => 1600, 'is_wholesale' => true],
                ]
            ],
            [
                'name' => 'Vinyl Banner - Large Format',
                'description' => 'Durable vinyl banner for outdoor advertising and events.',
                'brand' => 'CHIBO',
                'material' => 'Heavy Duty Vinyl',
                'color_options' => ['Full Color'],
                'size_options' => ['2x3ft', '3x5ft', '4x6ft', 'Custom Size'],
                'printing_type' => 'Large Format Digital',
                'specifications' => ['Weight' => '500g/m²', 'Print' => 'Full Color', 'Weather' => 'Weather Resistant'],
                'base_price' => 8000,
                'wholesale_price' => 6000,
                'tiers' => [
                    ['min' => 1, 'max' => 4, 'price' => 8000, 'is_wholesale' => false],
                    ['min' => 5, 'max' => 9, 'price' => 7000, 'is_wholesale' => false],
                    ['min' => 10, 'max' => null, 'price' => 6000, 'is_wholesale' => false],
                    ['min' => 1, 'max' => 4, 'price' => 6000, 'is_wholesale' => true],
                    ['min' => 5, 'max' => 9, 'price' => 5500, 'is_wholesale' => true],
                    ['min' => 10, 'max' => null, 'price' => 5000, 'is_wholesale' => true],
                ]
            ],
            [
                'name' => 'Business Cards - Premium',
                'description' => 'High-quality business cards with premium finish options.',
                'brand' => 'CHIBO',
                'material' => 'Premium Cardstock',
                'color_options' => ['Full Color'],
                'size_options' => ['Standard (3.5x2")', 'Square (2.5x2.5")', 'Custom Size'],
                'printing_type' => 'Offset Printing',
                'specifications' => ['Weight' => '300gsm', 'Print' => 'Full Color', 'Finish' => 'Glossy/Matte'],
                'base_price' => 500,
                'wholesale_price' => 350,
                'tiers' => [
                    ['min' => 100, 'max' => 499, 'price' => 500, 'is_wholesale' => false],
                    ['min' => 500, 'max' => 999, 'price' => 400, 'is_wholesale' => false],
                    ['min' => 1000, 'max' => null, 'price' => 300, 'is_wholesale' => false],
                    ['min' => 100, 'max' => 499, 'price' => 350, 'is_wholesale' => true],
                    ['min' => 500, 'max' => 999, 'price' => 300, 'is_wholesale' => true],
                    ['min' => 1000, 'max' => null, 'price' => 250, 'is_wholesale' => true],
                ]
            ],
            [
                'name' => 'Reflective Safety Vest',
                'description' => 'High-visibility reflective safety vest for construction and road work.',
                'brand' => 'CHIBO',
                'material' => 'Polyester with Reflective Strips',
                'color_options' => ['Yellow', 'Orange', 'Lime Green'],
                'size_options' => ['S', 'M', 'L', 'XL', 'XXL'],
                'printing_type' => 'Screen Print',
                'specifications' => ['Weight' => '250g', 'Print' => 'Screen Print', 'Reflective' => 'ANSI Class 2'],
                'base_price' => 3500,
                'wholesale_price' => 2800,
                'tiers' => [
                    ['min' => 1, 'max' => 9, 'price' => 3500, 'is_wholesale' => false],
                    ['min' => 10, 'max' => 49, 'price' => 3200, 'is_wholesale' => false],
                    ['min' => 50, 'max' => null, 'price' => 2900, 'is_wholesale' => false],
                    ['min' => 1, 'max' => 9, 'price' => 2800, 'is_wholesale' => true],
                    ['min' => 10, 'max' => 49, 'price' => 2600, 'is_wholesale' => true],
                    ['min' => 50, 'max' => null, 'price' => 2400, 'is_wholesale' => true],
                ]
            ]
        ];

        foreach ($products as $productData) {
            // Get a random category
            $category = $categories->random();
            
            // Create the product
            $product = Product::create([
                'category_id' => $category->id,
                'name' => $productData['name'],
                'description' => $productData['description'],
                'base_price' => $productData['base_price'],
                'wholesale_price' => $productData['wholesale_price'],
                'brand' => $productData['brand'],
                'material' => $productData['material'],
                'color_options' => $productData['color_options'],
                'size_options' => $productData['size_options'],
                'printing_type' => $productData['printing_type'],
                'specifications' => $productData['specifications'],
                'stock' => rand(50, 500),
                'status' => 'active',
            ]);

            // Create pricing tiers
            foreach ($productData['tiers'] as $tierData) {
                ProductPriceTier::create([
                    'product_id' => $product->id,
                    'min_quantity' => $tierData['min'],
                    'max_quantity' => $tierData['max'],
                    'price_per_unit' => $tierData['price'],
                    'is_wholesale' => $tierData['is_wholesale'],
                ]);
            }

            // Set price_range_display
            $min = $product->priceTiers()->min('price_per_unit');
            $max = $product->priceTiers()->max('price_per_unit');
            $product->price_range_display = number_format($min) . ' - ' . number_format($max);
            $product->save();

            $this->command->info("Created product: {$product->name} with pricing tiers");
        }

        $this->command->info('ProductTiersSeeder completed successfully!');
    }
}
