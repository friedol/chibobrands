<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        
        $productTemplates = [
            'Tshirts' => [
                ['name' => 'Premium Cotton T-Shirt', 'size' => 'M', 'material' => '100% Cotton'],
                ['name' => 'V-Neck T-Shirt', 'size' => 'L', 'material' => 'Cotton Blend'],
                ['name' => 'Polo Shirt', 'size' => 'XL', 'material' => 'Pique Cotton'],
                ['name' => 'Long Sleeve T-Shirt', 'size' => 'S', 'material' => '100% Cotton'],
                ['name' => 'Tank Top', 'size' => 'M', 'material' => 'Cotton Blend'],
                ['name' => 'Hoodie', 'size' => 'L', 'material' => 'Fleece'],
                ['name' => 'Sweatshirt', 'size' => 'XL', 'material' => 'Cotton Blend'],
                ['name' => 'Baseball T-Shirt', 'size' => 'M', 'material' => 'Cotton'],
            ],
            'Caps' => [
                ['name' => 'Baseball Cap', 'size' => 'One Size', 'material' => 'Cotton'],
                ['name' => 'Trucker Cap', 'size' => 'One Size', 'material' => 'Mesh'],
                ['name' => 'Snapback Cap', 'size' => 'Adjustable', 'material' => 'Cotton'],
                ['name' => 'Beanie', 'size' => 'One Size', 'material' => 'Acrylic'],
                ['name' => 'Bucket Hat', 'size' => 'One Size', 'material' => 'Cotton'],
                ['name' => 'Visor', 'size' => 'One Size', 'material' => 'Plastic'],
            ],
            'Office Branding' => [
                ['name' => 'Desk Name Plate', 'size' => 'A4', 'material' => 'Acrylic'],
                ['name' => 'Door Sign', 'size' => 'A5', 'material' => 'Aluminum'],
                ['name' => 'Reception Sign', 'size' => 'A3', 'material' => 'Acrylic'],
                ['name' => 'Office Directory', 'size' => 'A2', 'material' => 'Aluminum'],
                ['name' => 'Company Logo Sign', 'size' => 'Custom', 'material' => 'Acrylic'],
            ],
            'Large Format' => [
                ['name' => 'Banner 3x6ft', 'size' => '3x6ft', 'material' => 'Vinyl'],
                ['name' => 'Poster A1', 'size' => 'A1', 'material' => 'Paper'],
                ['name' => 'Billboard 4x8ft', 'size' => '4x8ft', 'material' => 'Vinyl'],
                ['name' => 'Backdrop 8x10ft', 'size' => '8x10ft', 'material' => 'Fabric'],
                ['name' => 'Window Display', 'size' => 'Custom', 'material' => 'Vinyl'],
            ],
            'Reflectors' => [
                ['name' => 'Safety Vest', 'size' => 'L', 'material' => 'Reflective'],
                ['name' => 'Reflective Tape', 'size' => '50m', 'material' => 'Reflective'],
                ['name' => 'Safety Helmet', 'size' => 'One Size', 'material' => 'Plastic'],
                ['name' => 'Reflective Stickers', 'size' => 'Various', 'material' => 'Reflective'],
            ],
            'Umbrella' => [
                ['name' => 'Golf Umbrella', 'size' => 'Large', 'material' => 'Polyester'],
                ['name' => 'Compact Umbrella', 'size' => 'Small', 'material' => 'Nylon'],
                ['name' => 'Beach Umbrella', 'size' => 'Extra Large', 'material' => 'Canvas'],
                ['name' => 'Promotional Umbrella', 'size' => 'Medium', 'material' => 'Polyester'],
            ],
            'Silicone Wristbands' => [
                ['name' => 'Standard Wristband', 'size' => 'One Size', 'material' => 'Silicone'],
                ['name' => 'Glow Wristband', 'size' => 'One Size', 'material' => 'Silicone'],
                ['name' => 'Debossed Wristband', 'size' => 'One Size', 'material' => 'Silicone'],
                ['name' => 'Color-Filled Wristband', 'size' => 'One Size', 'material' => 'Silicone'],
            ],
            'Tyre Cover' => [
                ['name' => 'Car Tyre Cover', 'size' => 'Standard', 'material' => 'Vinyl'],
                ['name' => 'Truck Tyre Cover', 'size' => 'Large', 'material' => 'Heavy Vinyl'],
                ['name' => 'Motorcycle Tyre Cover', 'size' => 'Small', 'material' => 'Vinyl'],
            ],
            'Stickers' => [
                ['name' => 'Vinyl Stickers', 'size' => 'Various', 'material' => 'Vinyl'],
                ['name' => 'Clear Stickers', 'size' => 'Various', 'material' => 'Clear Vinyl'],
                ['name' => 'Die-Cut Stickers', 'size' => 'Custom', 'material' => 'Vinyl'],
                ['name' => 'Kiss-Cut Stickers', 'size' => 'Various', 'material' => 'Vinyl'],
            ],
            'Cards (Business, Thank You)' => [
                ['name' => 'Business Cards', 'size' => '3.5x2', 'material' => 'Cardstock'],
                ['name' => 'Thank You Cards', 'size' => 'A6', 'material' => 'Cardstock'],
                ['name' => 'Invitation Cards', 'size' => 'A5', 'material' => 'Cardstock'],
                ['name' => 'Greeting Cards', 'size' => 'A6', 'material' => 'Cardstock'],
            ],
            'Flyers' => [
                ['name' => 'A4 Flyer', 'size' => 'A4', 'material' => 'Paper'],
                ['name' => 'A5 Flyer', 'size' => 'A5', 'material' => 'Paper'],
                ['name' => 'DL Flyer', 'size' => 'DL', 'material' => 'Paper'],
                ['name' => 'Brochure', 'size' => 'A4', 'material' => 'Paper'],
            ],
            'Advertising Stands' => [
                ['name' => 'X-Banner', 'size' => '85x200cm', 'material' => 'Vinyl'],
                ['name' => 'Roll-Up Banner', 'size' => '85x200cm', 'material' => 'Vinyl'],
                ['name' => 'Pop-Up Display', 'size' => '2x3ft', 'material' => 'Fabric'],
                ['name' => 'Table Top Display', 'size' => 'A4', 'material' => 'Acrylic'],
            ],
            'Gift Items' => [
                ['name' => 'Mug', 'size' => '11oz', 'material' => 'Ceramic'],
                ['name' => 'Keychain', 'size' => 'Small', 'material' => 'Metal'],
                ['name' => 'Pen', 'size' => 'Standard', 'material' => 'Plastic'],
                ['name' => 'Notebook', 'size' => 'A5', 'material' => 'Paper'],
            ],
            'Lanyards' => [
                ['name' => 'Standard Lanyard', 'size' => '36 inch', 'material' => 'Polyester'],
                ['name' => 'Breakaway Lanyard', 'size' => '36 inch', 'material' => 'Polyester'],
                ['name' => 'Beaded Lanyard', 'size' => '36 inch', 'material' => 'Polyester'],
                ['name' => 'Reflective Lanyard', 'size' => '36 inch', 'material' => 'Reflective'],
            ],
            'PVC IDs' => [
                ['name' => 'Employee ID Card', 'size' => 'CR80', 'material' => 'PVC'],
                ['name' => 'Student ID Card', 'size' => 'CR80', 'material' => 'PVC'],
                ['name' => 'Visitor Badge', 'size' => 'CR80', 'material' => 'PVC'],
                ['name' => 'Access Card', 'size' => 'CR80', 'material' => 'PVC'],
            ],
            'Car Branding' => [
                ['name' => 'Car Wrap', 'size' => 'Full Car', 'material' => 'Vinyl'],
                ['name' => 'Door Graphics', 'size' => 'Door Panel', 'material' => 'Vinyl'],
                ['name' => 'Window Decals', 'size' => 'Various', 'material' => 'Vinyl'],
                ['name' => 'Magnetic Signs', 'size' => 'Various', 'material' => 'Magnetic'],
            ],
            'Exhibition Tents' => [
                ['name' => '3x3m Exhibition Tent', 'size' => '3x3m', 'material' => 'Canvas'],
                ['name' => '3x6m Exhibition Tent', 'size' => '3x6m', 'material' => 'Canvas'],
                ['name' => '6x6m Exhibition Tent', 'size' => '6x6m', 'material' => 'Canvas'],
                ['name' => 'Custom Size Tent', 'size' => 'Custom', 'material' => 'Canvas'],
            ],
            'Department Signage' => [
                ['name' => 'Department Sign', 'size' => 'A3', 'material' => 'Acrylic'],
                ['name' => 'Directional Sign', 'size' => 'A4', 'material' => 'Aluminum'],
                ['name' => 'Room Number Sign', 'size' => 'A5', 'material' => 'Acrylic'],
                ['name' => 'Floor Plan Sign', 'size' => 'A2', 'material' => 'Aluminum'],
            ],
        ];

        foreach ($categories as $category) {
            $categoryName = $category->name;
            $templates = $productTemplates[$categoryName] ?? [];
            
            // Create 5-8 products per category
            $productCount = rand(5, 8);
            
            for ($i = 0; $i < $productCount; $i++) {
                $template = $templates[$i] ?? [
                    'name' => fake()->words(3, true),
                    'size' => fake()->randomElement(['S', 'M', 'L', 'XL', 'One Size', 'A4', 'A5', 'Custom']),
                    'material' => fake()->randomElement(['Cotton', 'Polyester', 'Vinyl', 'Paper', 'Plastic', 'Metal', 'Canvas'])
                ];
                
                $basePrice = fake()->randomFloat(2, 5000, 50000);
                $wholesalePrice = $basePrice * fake()->randomFloat(2, 0.6, 0.8); // 60-80% of base price
                
                $product = Product::create([
                    'category_id' => $category->id,
                    'name' => $template['name'],
                    'description' => fake()->paragraph(3),
                    'base_price' => $basePrice,
                    'wholesale_price' => $wholesalePrice,
                    'size' => $template['size'],
                    'material' => $template['material'],
                    'stock' => fake()->numberBetween(0, 100),
                    'status' => fake()->randomElement(['active', 'active', 'active', 'inactive']), // 75% active
                ]);
                
                // Create 2-4 images for each product
                $imageCount = rand(2, 4);
                for ($j = 0; $j < $imageCount; $j++) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => 'products/sample-' . fake()->numberBetween(1, 20) . '.jpg',
                    ]);
                }
            }
        }
    }
}