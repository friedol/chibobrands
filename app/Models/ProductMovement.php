<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductMovement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'movement_date',
        'gatekeeper_id',
        'product_name',
        'product_id',
        'quantity',
        'unit_price',
        'purpose',
        'authorization_reference',
        'handler_type',
        'handler_name',
        'handler_identifier',
        'handler_user_id',
        'source_type',
        'source_name',
        'source_identifier',
        'recipient_type',
        'recipient_name',
        'recipient_identifier',
        'delivery_method',
        'verification_code',
        'notes',
    ];

    protected $casts = [
        'movement_date' => 'datetime',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    /**
     * Get the gatekeeper who recorded the movement.
     */
    public function gatekeeper(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gatekeeper_id');
    }

    /**
     * Get the product (if linked to system product).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the handler user (if linked to system user).
     */
    public function handlerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handler_user_id');
    }
}
