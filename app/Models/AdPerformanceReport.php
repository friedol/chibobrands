<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdPerformanceReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'ad_id',
        'leads',
        'conversions',
        'saves',
        'shares',
        'impressions',
        'engagement',
        'amount_spent',
        'cpr',
        'roi',
        'notes',
        'report_date'
    ];

    protected $casts = [
        'report_date' => 'date',
        'amount_spent' => 'decimal:2',
        'cpr' => 'decimal:2',
        'roi' => 'decimal:2',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }
}
