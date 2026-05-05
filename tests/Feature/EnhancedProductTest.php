<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\EnhancedProduct;
use App\Models\ProductVariant;
use App\Models\EnhancedProductImage;
use App\Models\EnhancedProductPriceTier;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EnhancedProductTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create an admin user for testing
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'verified' => true
        ]);
    }

    /**
     * Test creating a product with all features
     */
    public function test_can_create_enhanced_product_with_all_features(): void
    {
        Storage::fake('public');

        $productData = [
            'name' => 'Test T-Shirt',
            'description' => 'A high-quality cotton t-shirt',
            'category' => 'Clothing',
            'brand' => 'CHIBO',
            'material' => '100% Cotton',
            'printing_type' => 'Digital Print',
            'buying_price' => 500.00,
            'retail_visible' => true,
            'wholesale_visible' => true,
            'variants' => [
                ['size' => 'M', 'color' => 'Red', 'notes' => 'Cotton blend'],
                ['size' => 'L', 'color' => 'Blue', 'notes' => 'Premium quality'],
            ],
            'price_tiers' => [
                ['customer_type' => 'retail', 'min_quantity' => 1, 'max_quantity' => 9, 'price_per_unit' => 1000.00],
                ['customer_type' => 'retail', 'min_quantity' => 10, 'max_quantity' => null, 'price_per_unit' => 900.00],
                ['customer_type' => 'wholesale', 'min_quantity' => 1, 'max_quantity' => 49, 'price_per_unit' => 800.00],
                ['customer_type' => 'wholesale', 'min_quantity' => 50, 'max_quantity' => null, 'price_per_unit' => 700.00],
            ],
            'images' => [
                UploadedFile::fake()->image('product1.jpg', 800, 600),
                UploadedFile::fake()->image('product2.jpg', 800, 600),
            ]
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.enhanced-products.store'), $productData);

        $response->assertRedirect(route('admin.enhanced-products.index'));

        // Assert product was created
        $this->assertDatabaseHas('enhanced_products', [
            'name' => 'Test T-Shirt',
            'brand' => 'CHIBO',
            'material' => '100% Cotton',
            'buying_price' => 500.00,
            'retail_visible' => true,
            'wholesale_visible' => true,
        ]);

        $product = EnhancedProduct::where('name', 'Test T-Shirt')->first();

        // Assert variants were created
        $this->assertEquals(2, $product->variants()->count());
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'size' => 'M',
            'color' => 'Red',
            'notes' => 'Cotton blend',
        ]);

        // Assert price tiers were created
        $this->assertEquals(4, $product->priceTiers()->count());
        $this->assertDatabaseHas('enhanced_product_price_tiers', [
            'product_id' => $product->id,
            'customer_type' => 'retail',
            'min_quantity' => 1,
            'max_quantity' => 9,
            'price_per_unit' => 1000.00,
        ]);

        // Assert images were uploaded
        $this->assertEquals(2, $product->images()->count());
        Storage::disk('public')->assertExists($product->images->first()->image_path);

        // Assert product ID and barcode were generated
        $this->assertNotNull($product->product_id);
        $this->assertNotNull($product->barcode);
        $this->assertStringStartsWith('PID-', $product->product_id);
        $this->assertStringStartsWith('CHB-', $product->barcode);
    }

    /**
     * Test product price calculation
     */
    public function test_product_price_calculation(): void
    {
        $product = EnhancedProduct::factory()->create([
            'name' => 'Test Product',
            'buying_price' => 500.00,
        ]);

        // Create price tiers
        EnhancedProductPriceTier::create([
            'product_id' => $product->id,
            'customer_type' => 'retail',
            'min_quantity' => 1,
            'max_quantity' => 9,
            'price_per_unit' => 1000.00,
        ]);

        EnhancedProductPriceTier::create([
            'product_id' => $product->id,
            'customer_type' => 'retail',
            'min_quantity' => 10,
            'max_quantity' => null,
            'price_per_unit' => 900.00,
        ]);

        EnhancedProductPriceTier::create([
            'product_id' => $product->id,
            'customer_type' => 'wholesale',
            'min_quantity' => 1,
            'max_quantity' => null,
            'price_per_unit' => 800.00,
        ]);

        // Test retail pricing
        $this->assertEquals(1000.00, $product->getPriceForQuantity(5, 'retail'));
        $this->assertEquals(900.00, $product->getPriceForQuantity(15, 'retail'));

        // Test wholesale pricing
        $this->assertEquals(800.00, $product->getPriceForQuantity(5, 'wholesale'));
        $this->assertEquals(800.00, $product->getPriceForQuantity(100, 'wholesale'));

        // Test price range
        $retailRange = $product->getPriceRange('retail');
        $this->assertEquals(900.00, $retailRange['min']);
        $this->assertEquals(1000.00, $retailRange['max']);
        $this->assertStringContainsString('900 - 1,000', $retailRange['formatted']);
    }

    /**
     * Test product visibility scopes
     */
    public function test_product_visibility_scopes(): void
    {
        $retailProduct = EnhancedProduct::factory()->create([
            'retail_visible' => true,
            'wholesale_visible' => false,
        ]);

        $wholesaleProduct = EnhancedProduct::factory()->create([
            'retail_visible' => false,
            'wholesale_visible' => true,
        ]);

        $bothProduct = EnhancedProduct::factory()->create([
            'retail_visible' => true,
            'wholesale_visible' => true,
        ]);

        $hiddenProduct = EnhancedProduct::factory()->create([
            'retail_visible' => false,
            'wholesale_visible' => false,
        ]);

        // Test retail visible scope
        $retailProducts = EnhancedProduct::retailVisible()->get();
        $this->assertCount(2, $retailProducts);
        $this->assertTrue($retailProducts->contains($retailProduct));
        $this->assertTrue($retailProducts->contains($bothProduct));

        // Test wholesale visible scope
        $wholesaleProducts = EnhancedProduct::wholesaleVisible()->get();
        $this->assertCount(2, $wholesaleProducts);
        $this->assertTrue($wholesaleProducts->contains($wholesaleProduct));
        $this->assertTrue($wholesaleProducts->contains($bothProduct));
    }

    /**
     * Test product variants relationship
     */
    public function test_product_variants_relationship(): void
    {
        $product = EnhancedProduct::factory()->create();

        $variant1 = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'color' => 'Red',
            'notes' => 'Cotton',
        ]);

        $variant2 = ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'L',
            'color' => 'Blue',
            'notes' => 'Polyester',
        ]);

        $this->assertEquals(2, $product->variants()->count());
        $this->assertEquals('M - Red (Cotton)', $variant1->display_name);
        $this->assertEquals('L - Blue (Polyester)', $variant2->display_name);
    }

    /**
     * Test product images relationship
     */
    public function test_product_images_relationship(): void
    {
        $product = EnhancedProduct::factory()->create();

        $image1 = EnhancedProductImage::create([
            'product_id' => $product->id,
            'color' => 'Red',
            'image_path' => 'products/test1.jpg',
        ]);

        $image2 = EnhancedProductImage::create([
            'product_id' => $product->id,
            'color' => 'Blue',
            'image_path' => 'products/test2.jpg',
        ]);

        $this->assertEquals(2, $product->images()->count());
        $this->assertStringContainsString('storage/products/test1.jpg', $image1->image_url);
    }

    /**
     * Test product price tiers relationship
     */
    public function test_product_price_tiers_relationship(): void
    {
        $product = EnhancedProduct::factory()->create();

        $retailTier = EnhancedProductPriceTier::create([
            'product_id' => $product->id,
            'customer_type' => 'retail',
            'min_quantity' => 1,
            'max_quantity' => 9,
            'price_per_unit' => 1000.00,
        ]);

        $wholesaleTier = EnhancedProductPriceTier::create([
            'product_id' => $product->id,
            'customer_type' => 'wholesale',
            'min_quantity' => 1,
            'max_quantity' => null,
            'price_per_unit' => 800.00,
        ]);

        $this->assertEquals(2, $product->priceTiers()->count());
        $this->assertEquals(1, $product->retailPriceTiers()->count());
        $this->assertEquals(1, $product->wholesalePriceTiers()->count());

        $this->assertEquals('1 - 9', $retailTier->quantity_range);
        $this->assertEquals('1+', $wholesaleTier->quantity_range);
        $this->assertEquals('1,000 TZS', $retailTier->formatted_price);
    }

    /**
     * Test product update functionality
     */
    public function test_can_update_enhanced_product(): void
    {
        $product = EnhancedProduct::factory()->create([
            'name' => 'Original Name',
            'brand' => 'Original Brand',
        ]);

        // Create some variants and price tiers
        ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'color' => 'Red',
        ]);

        EnhancedProductPriceTier::create([
            'product_id' => $product->id,
            'customer_type' => 'retail',
            'min_quantity' => 1,
            'max_quantity' => 9,
            'price_per_unit' => 1000.00,
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'brand' => 'Updated Brand',
            'buying_price' => 600.00,
            'retail_visible' => true,
            'wholesale_visible' => true,
            'variants' => [
                ['size' => 'L', 'color' => 'Blue', 'notes' => 'Updated variant'],
            ],
            'price_tiers' => [
                ['customer_type' => 'retail', 'min_quantity' => 1, 'max_quantity' => null, 'price_per_unit' => 1200.00],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.enhanced-products.update', $product), $updateData);

        $response->assertRedirect(route('admin.enhanced-products.index'));

        // Assert product was updated
        $product->refresh();
        $this->assertEquals('Updated Name', $product->name);
        $this->assertEquals('Updated Brand', $product->brand);
        $this->assertEquals(600.00, $product->buying_price);

        // Assert variants were updated (old ones deleted, new ones created)
        $this->assertEquals(1, $product->variants()->count());
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'size' => 'L',
            'color' => 'Blue',
            'notes' => 'Updated variant',
        ]);

        // Assert price tiers were updated
        $this->assertEquals(1, $product->priceTiers()->count());
        $this->assertDatabaseHas('enhanced_product_price_tiers', [
            'product_id' => $product->id,
            'customer_type' => 'retail',
            'min_quantity' => 1,
            'max_quantity' => null,
            'price_per_unit' => 1200.00,
        ]);
    }

    /**
     * Test product deletion
     */
    public function test_can_delete_enhanced_product(): void
    {
        Storage::fake('public');

        $product = EnhancedProduct::factory()->create();

        // Create related records
        ProductVariant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'color' => 'Red',
        ]);

        EnhancedProductPriceTier::create([
            'product_id' => $product->id,
            'customer_type' => 'retail',
            'min_quantity' => 1,
            'max_quantity' => 9,
            'price_per_unit' => 1000.00,
        ]);

        $image = EnhancedProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/test.jpg',
        ]);

        // Create a fake file
        Storage::disk('public')->put($image->image_path, 'fake content');

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.enhanced-products.destroy', $product));

        $response->assertRedirect(route('admin.enhanced-products.index'));

        // Assert product was deleted
        $this->assertDatabaseMissing('enhanced_products', ['id' => $product->id]);

        // Assert related records were deleted (cascade)
        $this->assertDatabaseMissing('product_variants', ['product_id' => $product->id]);
        $this->assertDatabaseMissing('enhanced_product_price_tiers', ['product_id' => $product->id]);
        $this->assertDatabaseMissing('enhanced_product_images', ['product_id' => $product->id]);

        // Assert image file was deleted
        Storage::disk('public')->assertMissing($image->image_path);
    }

    /**
     * Test validation rules
     */
    public function test_product_validation_rules(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.enhanced-products.store'), []);

        $response->assertSessionHasErrors(['name', 'buying_price']);

        // Test with invalid data
        $invalidData = [
            'name' => 'Test',
            'buying_price' => -100, // Invalid negative price
            'variants' => [
                ['size' => '', 'color' => 'Red'], // Missing size
            ],
            'price_tiers' => [
                ['customer_type' => 'invalid', 'min_quantity' => 0, 'price_per_unit' => -50], // Invalid data
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.enhanced-products.store'), $invalidData);

        $response->assertSessionHasErrors([
            'buying_price',
            'variants.0.size',
            'price_tiers.0.customer_type',
            'price_tiers.0.min_quantity',
            'price_tiers.0.price_per_unit',
        ]);
    }
}
