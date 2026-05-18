<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'phone',
        'email',
        'product_requested',
        'source',
        'follow_up_date',
        'assigned_seller_id',
        'status',
        'interest_level',
        'customer_response',
        'priority',
        'promised_amount',
        'promised_order_date',
        'lead_type',
        'last_follow_up_notes',
        'last_follow_up_date',
        'days_overdue',
        'last_reminder_sms_at',
    ];

    protected $casts = [
        'follow_up_date'      => 'date',
        'promised_order_date' => 'date',
        'last_follow_up_date' => 'date',
        'promised_amount'     => 'decimal:2',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_seller_id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(LeadFollowUp::class);
    }

    // ── Scopes ──────────────────────────────────────────────────────────────

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                     ->whereNotNull('follow_up_date')
                     ->where('follow_up_date', '<', today());
    }

    public function scopeDueToday($query)
    {
        return $query->where('status', 'pending')
                     ->whereDate('follow_up_date', today());
    }

    public function scopeUpcoming($query, int $days = 7)
    {
        return $query->where('status', 'pending')
                     ->whereDate('follow_up_date', '>', today())
                     ->whereDate('follow_up_date', '<=', today()->addDays($days));
    }

    public function scopeForSeller($query, int $sellerId)
    {
        return $query->where('assigned_seller_id', $sellerId);
    }

    // ── Computed attributes ─────────────────────────────────────────────────

    public function getDaysOverdueAttribute($value): int
    {
        if (!$this->follow_up_date || $this->status !== 'pending') return 0;
        $diff = today()->diffInDays($this->follow_up_date, false);
        return $diff < 0 ? abs($diff) : 0;
    }

    public function getFollowUpStatusAttribute(): string
    {
        if ($this->status !== 'pending') return 'closed';
        if (!$this->follow_up_date) return 'no_date';
        if ($this->follow_up_date->isPast() && !$this->follow_up_date->isToday()) return 'overdue';
        if ($this->follow_up_date->isToday()) return 'today';
        return 'upcoming';
    }

    public function getFollowUpStatusColorAttribute(): string
    {
        return match($this->follow_up_status) {
            'overdue'  => 'danger',
            'today'    => 'warning',
            'upcoming' => 'success',
            default    => 'secondary',
        };
    }

    public function getPriorityBadgeAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'danger',
            'high'   => 'warning',
            'normal' => 'primary',
            'low'    => 'secondary',
            default  => 'secondary',
        };
    }
}
