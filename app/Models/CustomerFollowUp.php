<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerFollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'user_id',
        'follow_up_date',
        'action',
        'notes',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
    ];

    /**
     * Get the customer that this follow-up belongs to.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user (saler) who performed/should perform the follow-up.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
