<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantCategory extends Model
{
    protected $fillable = [
        'product_id',
        'category',
        'price_adjustment',
        'position'
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'position' => 'integer'
    ];

    public function product()
    {
        return $this->belongsTo(EnhancedProduct::class, 'product_id');
    }

    public function items()
    {
        return $this->hasMany(ProductVariantItem::class, 'variant_category_id');
    }
}
