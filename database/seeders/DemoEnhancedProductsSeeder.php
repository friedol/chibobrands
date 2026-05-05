<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\EnhancedProduct;
use App\Models\EnhancedProductImage;
use App\Models\EnhancedProductPriceTier;
use App\Models\ProductVariantCategory;
use App\Models\ProductVariantItem;

class DemoEnhancedProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Purge existing product-related data
            EnhancedProductPriceTier::query()->delete();
            ProductVariantItem::query()->delete();
            ProductVariantCategory::query()->delete();
            EnhancedProductImage::query()->delete();
            EnhancedProduct::query()->delete();

            $brands = [
                'Chibo Tents', 'Chibo Branding', 'Chibo Events', 'Chibo Printing', 'Chibo Supplies'
            ];

            $categories = [
                'Exhibition Tents', 'Canopies', 'Billboards', 'Backdrops', 'Light Boxes',
                'Flyers', 'Brochures', 'Roll-up Banners', 'Stickers', 'Promo Tables'
            ];

            // 20 demo products
            for ($i = 1; $i <= 20; $i++) {
                $name = $categories[array_rand($categories)] . ' ' . $i;
                $brand = $brands[array_rand($brands)];

                $barcode = sprintf('CHB-%06d', $i);
                $slug = Str::slug($name . '-' . $i);

                $product = EnhancedProduct::create([
                    'product_id' => 'PID-' . strtoupper(Str::random(10)),
                    'name' => $name,
                    'product_nickname' => $name,
                    'slug' => $slug,
                    'barcode' => $barcode,
                    'description' => 'High quality ' . strtolower($name) . ' by Chibo Brand. Perfect for brand activation and corporate events.',
                    'category' => $categories[array_rand($categories)],
                    'brand' => $brand,
                    'material' => 'PVC Fabric',
                    'printing_type' => 'UV Printing',
                    'buying_price' => rand(50000, 150000),
                    'retail_base_price' => rand(160000, 300000),
                    'b2b_base_price' => rand(140000, 260000),
                    'stock_quantity' => rand(5, 50),
                    'stock_unit' => 'pcs',
                    'availability' => 'in_stock',
                    'is_active' => true,
                    'retail_visible' => true,
                    'wholesale_visible' => true,
                ]);

                // Images (use placeholder paths; ensure storage symlink exists)
                for ($img = 1; $img <= 3; $img++) {
                    EnhancedProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => 'placeholders/product_' . (($i % 10) + 1) . '.jpg',
                    ]);
                }

                // Variant Categories (Color, Size, Material, Finish) each with 5 options
                $variantDefinitions = [
                    'Color' => [
                        ['name' => 'Red', 'color' => '#e74c3c'],
                        ['name' => 'Blue', 'color' => '#3498db'],
                        ['name' => 'Green', 'color' => '#2ecc71'],
                        ['name' => 'Black', 'color' => '#2d3436'],
                        ['name' => 'White', 'color' => '#ecf0f1'],
                    ],
                    'Size' => [
                        ['name' => 'S'], ['name' => 'M'], ['name' => 'L'], ['name' => 'XL'], ['name' => 'XXL']
                    ],
                    'Material' => [
                        ['name' => 'PVC 300gsm'], ['name' => 'PVC 500gsm'], ['name' => 'Canvas'], ['name' => 'Polyester'], ['name' => 'Mesh']
                    ],
                    'Finish' => [
                        ['name' => 'Matte'], ['name' => 'Glossy'], ['name' => 'Laminated'], ['name' => 'UV Coated'], ['name' => 'Waterproof']
                    ],
                ];

                $position = 0;
                foreach ($variantDefinitions as $category => $items) {
                    $cat = ProductVariantCategory::create([
                        'product_id' => $product->id,
                        'category' => $category,
                        'price_adjustment' => 0,
                        'position' => $position++,
                    ]);

                    $optPos = 0;
                    foreach ($items as $opt) {
                        ProductVariantItem::create([
                            'variant_category_id' => $cat->id,
                            'name' => $opt['name'],
                            'color_code' => $opt['color'] ?? null,
                            'description' => $opt['name'] . ' option',
                            'price' => rand(0, 20000),
                            'position' => $optPos++,
                        ]);
                    }
                }

                // Pricing tiers: 3 retail, 5 wholesale
                $retailTiers = [
                    ['min' => 1, 'max' => 4, 'price' => max(50000, (int)($product->retail_base_price))],
                    ['min' => 5, 'max' => 9, 'price' => max(40000, (int)($product->retail_base_price * 0.95))],
                    ['min' => 10, 'max' => null, 'price' => max(35000, (int)($product->retail_base_price * 0.9))],
                ];

                foreach ($retailTiers as $t) {
                    EnhancedProductPriceTier::create([
                        'product_id' => $product->id,
                        'customer_type' => 'retail',
                        'min_quantity' => $t['min'],
                        'max_quantity' => $t['max'],
                        'price_per_unit' => $t['price'],
                    ]);
                }

                $wholesaleTiers = [
                    ['min' => 10, 'max' => 49, 'price' => max(30000, (int)($product->b2b_base_price * 0.95))],
                    ['min' => 50, 'max' => 99, 'price' => max(28000, (int)($product->b2b_base_price * 0.9))],
                    ['min' => 100, 'max' => 199, 'price' => max(26000, (int)($product->b2b_base_price * 0.85))],
                    ['min' => 200, 'max' => 499, 'price' => max(24000, (int)($product->b2b_base_price * 0.82))],
                    ['min' => 500, 'max' => null, 'price' => max(22000, (int)($product->b2b_base_price * 0.8))],
                ];

                foreach ($wholesaleTiers as $t) {
                    EnhancedProductPriceTier::create([
                        'product_id' => $product->id,
                        'customer_type' => 'wholesale',
                        'min_quantity' => $t['min'],
                        'max_quantity' => $t['max'],
                        'price_per_unit' => $t['price'],
                    ]);
                }
            }
        });
    }
}
