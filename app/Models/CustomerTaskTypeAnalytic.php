<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerTaskTypeAnalytic extends Model
{
    protected $fillable = [
        'customer_id',
        'design_task_type_id',
        'avg_reorder_interval',
        'last_purchase_date',
        'next_expected_purchase_date',
        'total_quantity_bought',
    ];

    protected $casts = [
        'last_purchase_date' => 'date',
        'next_expected_purchase_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function designTaskType(): BelongsTo
    {
        return $this->belongsTo(DesignTaskType::class);
    }
}
