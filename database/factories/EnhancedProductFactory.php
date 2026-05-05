<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnhancedProduct>
 */
class EnhancedProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => 'PID-' . strtoupper(uniqid()),
            'name' => $this->faker->words(3, true),
            'barcode' => 'CHB-' . str_pad($this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'description' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement(['Clothing', 'Accessories', 'Office Supplies', 'Promotional Items']),
            'brand' => 'CHIBO',
            'material' => $this->faker->randomElement(['100% Cotton', 'Polyester', 'Cotton Blend', 'Vinyl', 'Cardstock']),
            'printing_type' => $this->faker->randomElement(['Digital Print', 'Screen Print', 'Embroidery', 'Offset Printing']),
            'buying_price' => $this->faker->randomFloat(2, 100, 2000),
            'retail_visible' => $this->faker->boolean(80),
            'wholesale_visible' => $this->faker->boolean(60),
        ];
    }
}
