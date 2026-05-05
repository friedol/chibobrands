<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'size', 'color', 'notes'
    ];

    public function product()
    {
        return $this->belongsTo(EnhancedProduct::class, 'product_id');
    }

    public function getDisplayNameAttribute()
    {
        return $this->size . ' - ' . $this->color . ($this->notes ? ' (' . $this->notes . ')' : '');
    }
}
