<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductPriceTier;
use App\Models\Category;

class PriceTierTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating product with pricing tiers.
     */
    public function test_can_create_product_with_pricing_tiers(): void
    {
        // Create a category first
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test Description'
        ]);

        // Create a product
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'base_price' => 1000,
            'wholesale_price' => 800,
            'brand' => 'CHIBO',
            'material' => 'Cotton',
            'color_options' => ['Red', 'Black'],
            'size_options' => ['S', 'M', 'L'],
            'printing_type' => 'Digital',
            'specifications' => ['Weight' => '200g'],
            'stock' => 100,
            'status' => 'active',
        ]);

        // Create pricing tiers
        $tier1 = ProductPriceTier::create([
            'product_id' => $product->id,
            'min_quantity' => 1,
            'max_quantity' => 9,
            'price_per_unit' => 1000,
            'is_wholesale' => false,
        ]);

        $tier2 = ProductPriceTier::create([
            'product_id' => $product->id,
            'min_quantity' => 10,
            'max_quantity' => null,
            'price_per_unit' => 800,
            'is_wholesale' => false,
        ]);

        // Assertions
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'brand' => 'CHIBO',
        ]);

        $this->assertDatabaseHas('product_price_tiers', [
            'product_id' => $product->id,
            'min_quantity' => 1,
            'max_quantity' => 9,
            'price_per_unit' => 1000,
        ]);

        $this->assertDatabaseHas('product_price_tiers', [
            'product_id' => $product->id,
            'min_quantity' => 10,
            'max_quantity' => null,
            'price_per_unit' => 800,
        ]);

        // Test relationships
        $this->assertEquals(2, $product->priceTiers()->count());
        $this->assertEquals(1, $tier1->product->id);
    }

    /**
     * Test price endpoint returns correct price for quantity.
     */
    public function test_price_endpoint_returns_correct_price(): void
    {
        // Create a category first
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test Description'
        ]);

        // Create a product
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'base_price' => 1000,
            'wholesale_price' => 800,
            'brand' => 'CHIBO',
            'material' => 'Cotton',
            'stock' => 100,
            'status' => 'active',
        ]);

        // Create pricing tiers
        ProductPriceTier::create([
            'product_id' => $product->id,
            'min_quantity' => 1,
            'max_quantity' => 9,
            'price_per_unit' => 1000,
            'is_wholesale' => false,
        ]);

        ProductPriceTier::create([
            'product_id' => $product->id,
            'min_quantity' => 10,
            'max_quantity' => null,
            'price_per_unit' => 800,
            'is_wholesale' => false,
        ]);

        // Test quantity 5 (should use first tier)
        $response = $this->get("/products/get-price/{$product->id}?quantity=5");
        $response->assertStatus(200);
        $response->assertJson([
            'unit_price' => 1000,
            'total' => 5000,
        ]);

        // Test quantity 15 (should use second tier)
        $response = $this->get("/products/get-price/{$product->id}?quantity=15");
        $response->assertStatus(200);
        $response->assertJson([
            'unit_price' => 800,
            'total' => 12000,
        ]);
    }

    /**
     * Test product price range attribute.
     */
    public function test_product_price_range_attribute(): void
    {
        // Create a category first
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test Description'
        ]);

        // Create a product
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'base_price' => 1000,
            'wholesale_price' => 800,
            'brand' => 'CHIBO',
            'material' => 'Cotton',
            'stock' => 100,
            'status' => 'active',
        ]);

        // Create pricing tiers
        ProductPriceTier::create([
            'product_id' => $product->id,
            'min_quantity' => 1,
            'max_quantity' => 9,
            'price_per_unit' => 1000,
            'is_wholesale' => false,
        ]);

        ProductPriceTier::create([
            'product_id' => $product->id,
            'min_quantity' => 10,
            'max_quantity' => null,
            'price_per_unit' => 800,
            'is_wholesale' => false,
        ]);

        // Test price range
        $priceRange = $product->price_range;
        $this->assertEquals('800 - 1,000', $priceRange);
    }
}
