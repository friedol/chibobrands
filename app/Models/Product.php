<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'base_price',
        'wholesale_price',
        'size',
        'material',
        'stock',
        'status',
        'brand',
        'color_options',
        'size_options',
        'printing_type',
        'specifications',
        'price_range_display',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'stock' => 'integer',
        'color_options' => 'array',
        'size_options' => 'array',
        'specifications' => 'array',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the images for the product.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Get the order items for the product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the variations for the product.
     */
    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    /**
     * Get the add-ons for the product.
     */
    public function addons(): HasMany
    {
        return $this->hasMany(ProductAddon::class);
    }

    /**
     * Get the custom inputs for the product.
     */
    public function customInputs(): HasMany
    {
        return $this->hasMany(ProductCustomInput::class);
    }

    /**
     * Get the price tiers for the product.
     */
    public function priceTiers(): HasMany
    {
        return $this->hasMany(ProductPriceTier::class);
    }

    /**
     * Get the retail price tiers for the product.
     */
    public function retailTiers(): HasMany
    {
        return $this->hasMany(ProductPriceTier::class)->where('is_wholesale', false);
    }

    /**
     * Get the wholesale price tiers for the product.
     */
    public function wholesaleTiers(): HasMany
    {
        return $this->hasMany(ProductPriceTier::class)->where('is_wholesale', true);
    }

    /**
     * Scope to get only active products.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get products by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope to get products with stock.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Get the primary image for the product.
     */
    public function getPrimaryImageAttribute()
    {
        return $this->images()->first();
    }

    /**
     * Check if product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    /**
     * Get price based on user role.
     */
    public function getPriceForRole(string $role): float
    {
        return match($role) {
            'wholesale_customer' => $this->wholesale_price,
            default => $this->base_price,
        };
    }

    /**
     * Get the price range attribute.
     */
    public function getPriceRangeAttribute()
    {
        $min = $this->priceTiers()->min('price_per_unit');
        $max = $this->priceTiers()->max('price_per_unit');
        // Fallback to base prices if no tiers
        $min = $min ?? $this->base_price;
        $max = $max ?? $this->base_price;
        return number_format($min) . ' - ' . number_format($max);
    }

}