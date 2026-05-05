<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnhancedProduct extends Model
{
    use HasFactory;

    protected $table = 'enhanced_products';

    protected $fillable = [
        'product_id', 'name', 'product_nickname', 'slug', 'barcode', 'description', 'category', 'measured_in',
        'brand', 'material', 'weight', 'printing_type', 'buying_price', 'retail_base_price', 'b2b_base_price',
        'retail_visible', 'wholesale_visible', 'dimensions',
        'availability', 'features', 'variants', 'is_active', 'is_wholesale', 'customization_allowed',
        'track_stock', 'stock_quantity', 'stock_unit', 'low_stock_threshold', 'min_quantity',
        'max_quantity', 'sort_order', 'meta_title', 'meta_description'
    ];

    protected $casts = [
        'retail_visible' => 'boolean',
        'wholesale_visible' => 'boolean',
        'buying_price' => 'decimal:2',
        'retail_base_price' => 'decimal:2',
        'b2b_base_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'variants' => 'array',
        'is_active' => 'boolean',
        'is_wholesale' => 'boolean',
        'customization_allowed' => 'boolean',
        'track_stock' => 'boolean',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'sort_order' => 'integer',
    ];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function variantCategories()
    {
        return $this->hasMany(ProductVariantCategory::class, 'product_id');
    }

    public function images()
    {
        return $this->hasMany(EnhancedProductImage::class, 'product_id');
    }

    public function priceTiers()
    {
        return $this->hasMany(EnhancedProductPriceTier::class, 'product_id');
    }

    public function retailPriceTiers()
    {
        return $this->hasMany(EnhancedProductPriceTier::class, 'product_id')
                    ->where('customer_type', 'retail');
    }

    public function wholesalePriceTiers()
    {
        return $this->hasMany(EnhancedProductPriceTier::class, 'product_id')
                    ->where('customer_type', 'wholesale');
    }

    public function offers()
    {
        return $this->hasMany(Offer::class, 'product_barcode', 'barcode');
    }

    public function productAnalytics()
    {
        return $this->hasMany(CustomerProductAnalytic::class, 'product_id');
    }

    public static function generateBarcode()
    {
        $latest = self::orderBy('id', 'desc')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        return 'CHB-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    public static function generateProductId()
    {
        return 'PID-' . strtoupper(uniqid());
    }

    public function getPriceForQuantity($quantity, $customerType = 'retail')
    {
        $tier = $this->priceTiers()
                    ->where('customer_type', $customerType)
                    ->where('min_quantity', '<=', $quantity)
                    ->where(function($query) use ($quantity) {
                        $query->whereNull('max_quantity')
                              ->orWhere('max_quantity', '>=', $quantity);
                    })
                    ->orderBy('min_quantity', 'desc')
                    ->first();

        return $tier ? $tier->price_per_unit : null;
    }

    public function getPriceRange($customerType = 'retail')
    {
        $tiers = $this->priceTiers()->where('customer_type', $customerType)->get();
        
        if ($tiers->isEmpty()) {
            return null;
        }

        $min = $tiers->min('price_per_unit');
        $max = $tiers->max('price_per_unit');
        
        return [
            'min' => $min,
            'max' => $max,
            'formatted' => number_format($min) . ' - ' . number_format($max) . ' TZS'
        ];
    }

    public function getBasePriceForChannel($channel = 'retail')
    {
        if ($channel === 'wholesale') {
            return $this->b2b_base_price ?? $this->buying_price;
        }
        
        return $this->retail_base_price ?? $this->buying_price;
    }

    public function getEffectivePriceForChannel($channel = 'retail')
    {
        // 1. Check direct base price
        $price = ($channel === 'wholesale') ? $this->b2b_base_price : $this->retail_base_price;
        if ($price > 0) return $price;

        // 2. Fallback to buying price if available
        if ($this->buying_price > 0) return $this->buying_price;

        // 3. Fallback to Tier 1 price from price tiers
        $tierPrice = $this->getPriceForQuantity(1, $channel);
        if ($tierPrice > 0) return $tierPrice;

        return 0;
    }

    public function scopeRetailVisible($query)
    {
        return $query->where('retail_visible', true);
    }

    public function scopeWholesaleVisible($query)
    {
        return $query->where('wholesale_visible', true);
    }
}
