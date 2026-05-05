<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnhancedProductPriceTier extends Model
{
    use HasFactory;

    protected $table = 'enhanced_product_price_tiers';

    protected $fillable = [
        'product_id', 'customer_type', 'min_quantity', 'max_quantity', 'price_per_unit'
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(EnhancedProduct::class, 'product_id');
    }

    public function getQuantityRangeAttribute()
    {
        if ($this->max_quantity) {
            return $this->min_quantity . ' - ' . $this->max_quantity;
        }
        return $this->min_quantity . '+';
    }

    public function getFormattedPriceAttribute()
    {
        return number_format($this->price_per_unit) . ' TZS';
    }

    public function scopeRetail($query)
    {
        return $query->where('customer_type', 'retail');
    }

    public function scopeWholesale($query)
    {
        return $query->where('customer_type', 'wholesale');
    }
}
