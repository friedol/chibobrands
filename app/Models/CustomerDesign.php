<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDesign extends Model
{
    protected $fillable = [
        'customer_id',
        'user_id',
        'image_path',
        'title',
        'notes',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
