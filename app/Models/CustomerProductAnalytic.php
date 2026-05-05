<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerProductAnalytic extends Model
{
    use HasFactory;

    protected $table = 'customer_product_analytics';

    protected $fillable = [
        'customer_id',
        'product_id',
        'avg_reorder_interval',
        'last_purchase_date',
        'next_expected_purchase_date',
        'total_quantity_bought',
    ];

    protected $casts = [
        'last_purchase_date' => 'date',
        'next_expected_purchase_date' => 'date',
    ];

    /**
     * Get the customer that this analytic belongs to.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the product that this analytic belongs to.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(EnhancedProduct::class, 'product_id');
    }
}
