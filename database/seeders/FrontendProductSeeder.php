<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EnhancedProduct;
use App\Models\ProductVariant;
use App\Models\EnhancedProductImage;
use App\Models\EnhancedProductPriceTier;

class FrontendProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample product data for frontend testing
        $products = [
            [
                'name' => 'Custom Printed T-Shirt',
                'description' => 'High-quality cotton T-shirt with customizable print design. Perfect for promotional events, team building, or personal use.',
                'category' => 'Apparel',
                'brand' => 'CHIBO BRAND',
                'material' => '100% Cotton',
                'printing_type' => 'Screen Print',
                'buying_price' => 800,
                'retail_visible' => true,
                'wholesale_visible' => true,
                'variants' => [
                    ['size' => 'S', 'color' => 'Red', 'notes' => 'Classic Red'],
                    ['size' => 'M', 'color' => 'Red', 'notes' => 'Classic Red'],
                    ['size' => 'L', 'color' => 'Red', 'notes' => 'Classic Red'],
                    ['size' => 'XL', 'color' => 'Red', 'notes' => 'Classic Red'],
                    ['size' => 'S', 'color' => 'Black', 'notes' => 'Premium Black'],
                    ['size' => 'M', 'color' => 'Black', 'notes' => 'Premium Black'],
                    ['size' => 'L', 'color' => 'Black', 'notes' => 'Premium Black'],
                    ['size' => 'XL', 'color' => 'Black', 'notes' => 'Premium Black'],
                    ['size' => 'S', 'color' => 'White', 'notes' => 'Clean White'],
                    ['size' => 'M', 'color' => 'White', 'notes' => 'Clean White'],
                    ['size' => 'L', 'color' => 'White', 'notes' => 'Clean White'],
                    ['size' => 'XL', 'color' => 'White', 'notes' => 'Clean White'],
                ],
                'price_tiers' => [
                    ['customer_type' => 'retail', 'min_quantity' => 1, 'max_quantity' => 9, 'price_per_unit' => 1500],
                    ['customer_type' => 'retail', 'min_quantity' => 10, 'max_quantity' => 50, 'price_per_unit' => 1300],
                    ['customer_type' => 'retail', 'min_quantity' => 51, 'max_quantity' => null, 'price_per_unit' => 1200],
                    ['customer_type' => 'wholesale', 'min_quantity' => 51, 'max_quantity' => 100, 'price_per_unit' => 1100],
                    ['customer_type' => 'wholesale', 'min_quantity' => 101, 'max_quantity' => 500, 'price_per_unit' => 1000],
                    ['customer_type' => 'wholesale', 'min_quantity' => 501, 'max_quantity' => null, 'price_per_unit' => 900],
                ]
            ],
            [
                'name' => 'Business Cards Premium',
                'description' => 'Professional business cards with premium finish. Available in various sizes and finishes including matte, glossy, and spot UV.',
                'category' => 'Business Cards',
                'brand' => 'CHIBO BRAND',
                'material' => 'Premium Cardstock',
                'printing_type' => 'Digital Print',
                'buying_price' => 50,
                'retail_visible' => true,
                'wholesale_visible' => true,
                'variants' => [
                    ['size' => 'Standard', 'color' => 'White', 'notes' => 'Classic White'],
                    ['size' => 'Standard', 'color' => 'Cream', 'notes' => 'Elegant Cream'],
                    ['size' => 'Standard', 'color' => 'Gray', 'notes' => 'Professional Gray'],
                    ['size' => 'Oversized', 'color' => 'White', 'notes' => 'Large Format'],
                    ['size' => 'Oversized', 'color' => 'Cream', 'notes' => 'Large Format'],
                ],
                'price_tiers' => [
                    ['customer_type' => 'retail', 'min_quantity' => 100, 'max_quantity' => 499, 'price_per_unit' => 2.5],
                    ['customer_type' => 'retail', 'min_quantity' => 500, 'max_quantity' => 999, 'price_per_unit' => 2.0],
                    ['customer_type' => 'retail', 'min_quantity' => 1000, 'max_quantity' => null, 'price_per_unit' => 1.5],
                    ['customer_type' => 'wholesale', 'min_quantity' => 1000, 'max_quantity' => 4999, 'price_per_unit' => 1.2],
                    ['customer_type' => 'wholesale', 'min_quantity' => 5000, 'max_quantity' => null, 'price_per_unit' => 1.0],
                ]
            ],
            [
                'name' => 'Promotional Flyers',
                'description' => 'High-quality promotional flyers perfect for marketing campaigns, events, and announcements. Available in various sizes and finishes.',
                'category' => 'Marketing Materials',
                'brand' => 'CHIBO BRAND',
                'material' => 'Glossy Paper',
                'printing_type' => 'Offset Print',
                'buying_price' => 15,
                'retail_visible' => true,
                'wholesale_visible' => true,
                'variants' => [
                    ['size' => 'A4', 'color' => 'Full Color', 'notes' => 'Standard Size'],
                    ['size' => 'A5', 'color' => 'Full Color', 'notes' => 'Half Size'],
                    ['size' => 'A6', 'color' => 'Full Color', 'notes' => 'Postcard Size'],
                    ['size' => 'A4', 'color' => 'Black & White', 'notes' => 'Economy Option'],
                    ['size' => 'A5', 'color' => 'Black & White', 'notes' => 'Economy Option'],
                ],
                'price_tiers' => [
                    ['customer_type' => 'retail', 'min_quantity' => 100, 'max_quantity' => 499, 'price_per_unit' => 0.8],
                    ['customer_type' => 'retail', 'min_quantity' => 500, 'max_quantity' => 999, 'price_per_unit' => 0.6],
                    ['customer_type' => 'retail', 'min_quantity' => 1000, 'max_quantity' => null, 'price_per_unit' => 0.4],
                    ['customer_type' => 'wholesale', 'min_quantity' => 1000, 'max_quantity' => 4999, 'price_per_unit' => 0.3],
                    ['customer_type' => 'wholesale', 'min_quantity' => 5000, 'max_quantity' => null, 'price_per_unit' => 0.25],
                ]
            ],
            [
                'name' => 'Vinyl Banners',
                'description' => 'Durable vinyl banners for outdoor advertising, events, and promotions. Weather-resistant and long-lasting.',
                'category' => 'Banners & Signs',
                'brand' => 'CHIBO BRAND',
                'material' => 'Heavy Duty Vinyl',
                'printing_type' => 'Large Format Print',
                'buying_price' => 2000,
                'retail_visible' => true,
                'wholesale_visible' => false, // Only for retail
                'variants' => [
                    ['size' => '2x3 feet', 'color' => 'Full Color', 'notes' => 'Small Banner'],
                    ['size' => '3x6 feet', 'color' => 'Full Color', 'notes' => 'Medium Banner'],
                    ['size' => '4x8 feet', 'color' => 'Full Color', 'notes' => 'Large Banner'],
                    ['size' => '6x12 feet', 'color' => 'Full Color', 'notes' => 'Extra Large'],
                ],
                'price_tiers' => [
                    ['customer_type' => 'retail', 'min_quantity' => 1, 'max_quantity' => 4, 'price_per_unit' => 15000],
                    ['customer_type' => 'retail', 'min_quantity' => 5, 'max_quantity' => 9, 'price_per_unit' => 12000],
                    ['customer_type' => 'retail', 'min_quantity' => 10, 'max_quantity' => null, 'price_per_unit' => 10000],
                ]
            ],
            [
                'name' => 'Corporate Brochures',
                'description' => 'Professional corporate brochures with premium finish. Perfect for company presentations, product catalogs, and marketing materials.',
                'category' => 'Marketing Materials',
                'brand' => 'CHIBO BRAND',
                'material' => 'Premium Paper',
                'printing_type' => 'Digital Print',
                'buying_price' => 100,
                'retail_visible' => false, // Only for wholesale
                'wholesale_visible' => true,
                'variants' => [
                    ['size' => 'A4', 'color' => 'Full Color', 'notes' => 'Standard Format'],
                    ['size' => 'A5', 'color' => 'Full Color', 'notes' => 'Compact Format'],
                    ['size' => 'A4', 'color' => 'Spot Color', 'notes' => 'Brand Colors'],
                    ['size' => 'A5', 'color' => 'Spot Color', 'notes' => 'Brand Colors'],
                ],
                'price_tiers' => [
                    ['customer_type' => 'wholesale', 'min_quantity' => 100, 'max_quantity' => 499, 'price_per_unit' => 5.0],
                    ['customer_type' => 'wholesale', 'min_quantity' => 500, 'max_quantity' => 999, 'price_per_unit' => 4.0],
                    ['customer_type' => 'wholesale', 'min_quantity' => 1000, 'max_quantity' => null, 'price_per_unit' => 3.0],
                ]
            ]
        ];

        foreach ($products as $productData) {
            // Create the enhanced product
            $product = EnhancedProduct::create([
                'product_id' => EnhancedProduct::generateProductId(),
                'name' => $productData['name'],
                'barcode' => EnhancedProduct::generateBarcode(),
                'description' => $productData['description'],
                'category' => $productData['category'],
                'brand' => $productData['brand'],
                'material' => $productData['material'],
                'printing_type' => $productData['printing_type'],
                'buying_price' => $productData['buying_price'],
                'retail_visible' => $productData['retail_visible'],
                'wholesale_visible' => $productData['wholesale_visible'],
                'base_price' => $productData['buying_price'] * 1.5, // 50% markup
                'weight' => rand(1, 50) / 10, // Random weight between 0.1 and 5.0 kg
                'dimensions' => rand(10, 100) . 'x' . rand(10, 100) . 'x' . rand(1, 20) . ' cm',
                'availability' => 'in_stock',
                'features' => "High quality materials\nProfessional printing\nFast delivery\nCustomizable options",
                'is_active' => true,
                'is_wholesale' => $productData['wholesale_visible'],
                'customization_allowed' => true,
                'track_stock' => true,
                'stock_quantity' => rand(50, 500),
                'low_stock_threshold' => 10,
                'min_quantity' => 1,
                'max_quantity' => 1000,
                'sort_order' => 0,
                'meta_title' => $productData['name'] . ' - CHIBO BRAND',
                'meta_description' => $productData['description'],
            ]);

            // Create variants
            foreach ($productData['variants'] as $variant) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'size' => $variant['size'],
                    'color' => $variant['color'],
                    'notes' => $variant['notes'],
                ]);
            }

            // Create price tiers
            foreach ($productData['price_tiers'] as $tier) {
                EnhancedProductPriceTier::create([
                    'product_id' => $product->id,
                    'customer_type' => $tier['customer_type'],
                    'min_quantity' => $tier['min_quantity'],
                    'max_quantity' => $tier['max_quantity'],
                    'price_per_unit' => $tier['price_per_unit'],
                ]);
            }

            $this->command->info("Created product: {$product->name} (Barcode: {$product->barcode})");
        }

        $this->command->info('FrontendProductSeeder completed successfully!');
        $this->command->info('You can now test the frontend product display with these sample products.');
    }
}
