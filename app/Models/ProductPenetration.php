<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPenetration extends Model
{
    protected $fillable = [
        'item_type',
        'product_id',
        'task_type_id',
        'target_segment',
        'current_penetration',
        'target_penetration',
        'strategy_notes',
        'status',
        'created_by',
    ];

    public function product()
    {
        return $this->belongsTo(EnhancedProduct::class, 'product_id');
    }

    public function taskType()
    {
        return $this->belongsTo(DesignTaskType::class, 'task_type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getItemNameAttribute(): string
    {
        if ($this->item_type === 'task_type') {
            return $this->taskType?->name ?? 'Unknown Task Type';
        }
        return $this->product?->name ?? 'Unknown Product';
    }
}
