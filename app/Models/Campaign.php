<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'product_id',
        'stage',
        'objective',
        'budget',
        'start_date',
        'end_date',
        'target_market',
        'expected_reach',
        'actual_reach',
        'status',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'budget' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(EnhancedProduct::class, 'product_id');
    }

    public function activities()
    {
        return $this->hasMany(CampaignActivity::class);
    }

    public function ads()
    {
        return $this->hasMany(Ad::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function calendarEvents()
    {
        return $this->morphMany(MarketingCalendarEvent::class, 'source');
    }
}
