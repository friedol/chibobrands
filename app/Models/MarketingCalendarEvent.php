<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingCalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_type',
        'source_id',
        'title',
        'description',
        'start_datetime',
        'end_datetime',
        'platform',
        'content_format',
        'objective',
        'segment',
        'assigned_to',
        'status'
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    public function source()
    {
        return $this->morphTo();
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
