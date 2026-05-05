<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'customer_phone',
        'message_sent',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Get the order that owns the WhatsApp request.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
