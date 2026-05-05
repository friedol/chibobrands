<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    protected $fillable = [
        'product_barcode',
        'offer_type',
        'target_channel',
        'discount_value',
        'min_quantity',
        'free_quantity',
        'start_date',
        'end_date',
        'description',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'discount_value' => 'decimal:2',
        'min_quantity' => 'decimal:2',
        'free_quantity' => 'decimal:2'
    ];

    /**
     * Get the product that owns the offer
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(EnhancedProduct::class, 'product_barcode', 'barcode');
    }

    /**
     * Scope for active offers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for current offers (within date range)
     */
    public function scopeCurrent($query)
    {
        $today = now()->toDateString();
        return $query->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today);
    }

    /**
     * Get the calculated discount amount for a given price
     */
    public function getDiscountAmount($price)
    {
        if ($this->offer_type === 'percentage') {
            return ($price * $this->discount_value) / 100;
        }
        
        return $this->discount_value;
    }

    /**
     * Get the final price after discount
     */
    public function getFinalPrice($price)
    {
        return $price - $this->getDiscountAmount($price);
    }
}
