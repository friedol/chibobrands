<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantItem extends Model
{
    protected $fillable = [
        'variant_category_id',
        'name',
        'color_code',
        'description',
        'price',
        'retail_price',
        'wholesale_price',
        'position'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'position' => 'integer'
    ];

    public function variantCategory()
    {
        return $this->belongsTo(ProductVariantCategory::class, 'variant_category_id');
    }

    /**
     * Get the price for a specific customer type
     */
    public function getPriceForCustomerType($customerType = 'retail')
    {
        if ($customerType === 'wholesale') {
            return $this->wholesale_price ?? $this->price;
        }
        
        return $this->retail_price ?? $this->price;
    }
}
