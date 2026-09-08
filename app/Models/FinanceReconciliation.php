<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceReconciliation extends Model
{
    protected $table = 'finance_reconciliations';

    protected $fillable = [
        'reconciled_by',
        'customer_id',
        'payment_id',
        'design_task_id',
        'order_id',
        'amount',
        'transaction_date',
        'reconciliation_date',
        'type',
        'status',
        'reference',
        'notes',
        'reason',
    ];

    protected $casts = [
        'transaction_date'    => 'date',
        'reconciliation_date' => 'date',
        'amount'              => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function designTask(): BelongsTo
    {
        return $this->belongsTo(DesignTask::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeInPeriod($query, $from, $to)
    {
        return $query->whereBetween('transaction_date', [$from, $to]);
    }

    // ── Accessors ─────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'debt_write_off'   => 'Debt Write-off',
            'partial_payment'  => 'Partial Payment',
            'full_payment'     => 'Full Payment',
            'credit_note'      => 'Credit Note',
            'adjustment'       => 'Adjustment',
            'historical_entry' => 'Historical Entry',
            default            => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'full_payment'     => 'success',
            'partial_payment'  => 'info',
            'debt_write_off'   => 'danger',
            'credit_note'      => 'warning',
            'adjustment'       => 'primary',
            'historical_entry' => 'secondary',
            default            => 'dark',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'approved'       => 'success',
            'pending_review' => 'warning',
            'rejected'       => 'danger',
            default          => 'secondary',
        };
    }
}
