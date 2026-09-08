<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'ad_name',
        'platform',
        'objective',
        'target_audience',
        'budget',
        'start_date',
        'end_date',
        'content_type',
        'status',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'budget' => 'decimal:2',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function performanceReports()
    {
        return $this->hasMany(AdPerformanceReport::class);
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
