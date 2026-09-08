<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'activity_name',
        'platform',
        'content_type',
        'scheduled_at',
        'assigned_to',
        'notes',
        'status'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
