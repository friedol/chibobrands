<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EnhancedProduct;
use App\Models\ProductVariant;
use App\Models\EnhancedProductImage;
use App\Models\EnhancedProductPriceTier;

class EnhancedProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating enhanced products with variants and pricing tiers...');

        // Sample product data
        $products = [
            [
                'name' => 'Premium Cotton T-Shirt',
                'description' => 'High-quality 100% cotton t-shirt perfect for branding and promotional use.',
                'category' => 'Clothing',
                'brand' => 'CHIBO',
                'material' => '100% Cotton',
                'printing_type' => 'Digital Print',
                'buying_price' => 800.00,
                'retail_visible' => true,
                'wholesale_visible' => true,
                'variants' => [
                    ['size' => 'S', 'color' => 'Red', 'notes' => 'Cotton blend'],
                    ['size' => 'M', 'color' => 'Red', 'notes' => 'Cotton blend'],
                    ['size' => 'L', 'color' => 'Red', 'notes' => 'Cotton blend'],
                    ['size' => 'XL', 'color' => 'Red', 'notes' => 'Cotton blend'],
                    ['size' => 'S', 'color' => 'Black', 'notes' => 'Premium quality'],
                    ['size' => 'M', 'color' => 'Black', 'notes' => 'Premium quality'],
                    ['size' => 'L', 'color' => 'Black', 'notes' => 'Premium quality'],
                    ['size' => 'XL', 'color' => 'Black', 'notes' => 'Premium quality'],
                ],
                'price_tiers' => [
                    ['customer_type' => 'retail', 'min_quantity' => 1, 'max_quantity' => 9, 'price_per_unit' => 1500.00],
                    ['customer_type' => 'retail', 'min_quantity' => 10, 'max_quantity' => 49, 'price_per_unit' => 1300.00],
                    ['customer_type' => 'retail', 'min_quantity' => 50, 'max_quantity' => null, 'price_per_unit' => 1100.00],
                    ['customer_type' => 'wholesale', 'min_quantity' => 1, 'max_quantity' => 9, 'price_per_unit' => 1200.00],
                    ['customer_type' => 'wholesale', 'min_quantity' => 10, 'max_quantity' => 49, 'price_per_unit' => 1000.00],
                    ['customer_type' => 'wholesale', 'min_quantity' => 50, 'max_quantity' => null, 'price_per_unit' => 800.00],
                ],
            ],
            [
                'name' => 'Embroidered Baseball Cap',
                'description' => 'Classic baseball cap with high-quality embroidery for professional branding.',
                'category' => 'Accessories',
                'brand' => 'CHIBO',
                'material' => 'Cotton/Polyester Blend',
                'printing_type' => 'Embroidery',
                'buying_price' => 1200.00,
                'retail_visible' => true,
                'wholesale_visible' => true,
                'variants' => [
                    ['size' => 'One Size', 'color' => 'Black', 'notes' => 'Adjustable strap'],
                    ['size' => 'One Size', 'color' => 'White', 'notes' => 'Adjustable strap'],
                    ['size' => 'One Size', 'color' => 'Red', 'notes' => 'Adjustable strap'],
                    ['size' => 'One Size', 'color' => 'Navy', 'notes' => 'Adjustable strap'],
                ],
                'price_tiers' => [
                    ['customer_type' => 'retail', 'min_quantity' => 1, 'max_quantity' => 9, 'price_per_unit' => 2500.00],
                    ['customer_type' => 'retail', 'min_quantity' => 10, 'max_quantity' => 49, 'price_per_unit' => 2200.00],
                    ['customer_type' => 'retail', 'min_quantity' => 50, 'max_quantity' => null, 'price_per_unit' => 1900.00],
                    ['customer_type' => 'wholesale', 'min_quantity' => 1, 'max_quantity' => 9, 'price_per_unit' => 2000.00],
                    ['customer_type' => 'wholesale', 'min_quantity' => 10, 'max_quantity' => 49, 'price_per_unit' => 1800.00],
                    ['customer_type' => 'wholesale', 'min_quantity' => 50, 'max_quantity' => null, 'price_per_unit' => 1600.00],
                ],
            ],
            [
                'name' => 'Vinyl Banner - Large Format',
                'description' => 'Heavy-duty vinyl banner perfect for outdoor advertising and events.',
                'category' => 'Large Format',
                'brand' => 'CHIBO',
                'material' => 'Heavy Duty Vinyl',
                'printing_type' => 'Large Format Digital',
                'buying_price' => 3000.00,
                'retail_visible' => true,
                'wholesale_visible' => true,
                'variants' => [
                    ['size' => '2x3ft', 'color' => 'Full Color', 'notes' => 'Standard size'],
                    ['size' => '3x5ft', 'color' => 'Full Color', 'notes' => 'Medium size'],
                    ['size' => '4x6ft', 'color' => 'Full Color', 'notes' => 'Large size'],
                    ['size' => 'Custom Size', 'color' => 'Full Color', 'notes' => 'Any size available'],
                ],
                'price_tiers' => [
                    ['customer_type' => 'retail', 'min_quantity' => 1, 'max_quantity' => 4, 'price_per_unit' => 8000.00],
                    ['customer_type' => 'retail', 'min_quantity' => 5, 'max_quantity' => 9, 'price_per_unit' => 7000.00],
                    ['customer_type' => 'retail', 'min_quantity' => 10, 'max_quantity' => null, 'price_per_unit' => 6000.00],
                    ['customer_type' => 'wholesale', 'min_quantity' => 1, 'max_quantity' => 4, 'price_per_unit' => 6000.00],
                    ['customer_type' => 'wholesale', 'min_quantity' => 5, 'max_quantity' => 9, 'price_per_unit' => 5500.00],
                    ['customer_type' => 'wholesale', 'min_quantity' => 10, 'max_quantity' => null, 'price_per_unit' => 5000.00],
                ],
            ],
            [
                'name' => 'Business Cards - Premium',
                'description' => 'High-quality business cards with premium cardstock and professional finish.',
                'category' => 'Cards',
                'brand' => 'CHIBO',
                'material' => 'Premium Cardstock',
                'printing_type' => 'Offset Printing',
                'buying_price' => 150.00,
                'retail_visible' => true,
                'wholesale_visible' => true,
                'variants' => [
                    ['size' => 'Standard (3.5x2")', 'color' => 'Full Color', 'notes' => 'Standard business card size'],
                    ['size' => 'Square (2.5x2.5")', 'color' => 'Full Color', 'notes' => 'Modern square format'],
                    ['size' => 'Custom Size', 'color' => 'Full Color', 'notes' => 'Any custom size'],
                ],
                'price_tiers' => [
                    ['customer_type' => 'retail', 'min_quantity' => 100, 'max_quantity' => 499, 'price_per_unit' => 500.00],
                    ['customer_type' => 'retail', 'min_quantity' => 500, 'max_quantity' => 999, 'price_per_unit' => 400.00],
                    ['customer_type' => 'retail', 'min_quantity' => 1000, 'max_quantity' => null, 'price_per_unit' => 300.00],
                    ['customer_type' => 'wholesale', 'min_quantity' => 100, 'max_quantity' => 499, 'price_per_unit' => 350.00],
                    ['customer_type' => 'wholesale', 'min_quantity' => 500, 'max_quantity' => 999, 'price_per_unit' => 300.00],
                    ['customer_type' => 'wholesale', 'min_quantity' => 1000, 'max_quantity' => null, 'price_per_unit' => 250.00],
                ],
            ],
            [
                'name' => 'Reflective Safety Vest',
                'description' => 'High-visibility safety vest with reflective strips for construction and road work.',
                'category' => 'Safety',
                'brand' => 'CHIBO',
                'material' => 'Polyester with Reflective Strips',
                'printing_type' => 'Screen Print',
                'buying_price' => 1800.00,
                'retail_visible' => true,
                'wholesale_visible' => true,
                'variants' => [
                    ['size' => 'S', 'color' => 'Yellow', 'notes' => 'High visibility'],
                    ['size' => 'M', 'color' => 'Yellow', 'notes' => 'High visibility'],
                    ['size' => 'L', 'color' => 'Yellow', 'notes' => 'High visibility'],
                    ['size' => 'XL', 'color' => 'Yellow', 'notes' => 'High visibility'],
                    ['size' => 'S', 'color' => 'Orange', 'notes' => 'Construction grade'],
                    ['size' => 'M', 'color' => 'Orange', 'notes' => 'Construction grade'],
                    ['size' => 'L', 'color' => 'Orange', 'notes' => 'Construction grade'],
                    ['size' => 'XL', 'color' => 'Orange', 'notes' => 'Construction grade'],
                ],
                'price_tiers' => [
                    ['customer_type' => 'retail', 'min_quantity' => 1, 'max_quantity' => 9, 'price_per_unit' => 3500.00],
                    ['customer_type' => 'retail', 'min_quantity' => 10, 'max_quantity' => 49, 'price_per_unit' => 3200.00],
                    ['customer_type' => 'retail', 'min_quantity' => 50, 'max_quantity' => null, 'price_per_unit' => 2900.00],
                    ['customer_type' => 'wholesale', 'min_quantity' => 1, 'max_quantity' => 9, 'price_per_unit' => 2800.00],
                    ['customer_type' => 'wholesale', 'min_quantity' => 10, 'max_quantity' => 49, 'price_per_unit' => 2600.00],
                    ['customer_type' => 'wholesale', 'min_quantity' => 50, 'max_quantity' => null, 'price_per_unit' => 2400.00],
                ],
            ],
        ];

        foreach ($products as $productData) {
            // Create the product
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
            ]);

            // Create variants
            foreach ($productData['variants'] as $variantData) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'size' => $variantData['size'],
                    'color' => $variantData['color'],
                    'notes' => $variantData['notes'],
                ]);
            }

            // Create price tiers
            foreach ($productData['price_tiers'] as $tierData) {
                EnhancedProductPriceTier::create([
                    'product_id' => $product->id,
                    'customer_type' => $tierData['customer_type'],
                    'min_quantity' => $tierData['min_quantity'],
                    'max_quantity' => $tierData['max_quantity'],
                    'price_per_unit' => $tierData['price_per_unit'],
                ]);
            }

            $this->command->info("Created product: {$product->name} with " . count($productData['variants']) . " variants and " . count($productData['price_tiers']) . " price tiers");
        }

        $this->command->info('Enhanced products seeding completed successfully!');
    }
}
