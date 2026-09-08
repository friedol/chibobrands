<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignTaskType extends Model
{
    protected $fillable = [
        'name',
        'price',
        'description',
        'department_id',
        'image_path',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
