<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if (!empty($payment->customer_business_id)) {
                return;
            }

            if (!empty($payment->design_task_id)) {
                $businessId = DesignTask::whereKey($payment->design_task_id)->value('customer_business_id');
                if ($businessId) {
                    $payment->customer_business_id = $businessId;
                    return;
                }
            }

            if (!empty($payment->order_id)) {
                $businessId = Order::whereKey($payment->order_id)->value('customer_business_id');
                if ($businessId) {
                    $payment->customer_business_id = $businessId;
                    return;
                }
            }

            if (!empty($payment->customer_id)) {
                $businessId = CustomerBusiness::where('customer_id', $payment->customer_id)
                    ->orderByDesc('is_primary')
                    ->value('id');

                if ($businessId) {
                    $payment->customer_business_id = $businessId;
                }
            }
        });
    }

    // Debt status constants
    const DEBT_PENDING     = 'pending';
    const DEBT_PARTIAL     = 'partial';
    const DEBT_PAID        = 'paid';
    const DEBT_RECONCILED  = 'reconciled';
    const DEBT_WAIVED      = 'waived';

    protected $fillable = [
        'customer_id',
        'customer_business_id',
        'order_id',
        'design_task_id',
        'amount',
        'payment_method',
        'date',
        'seller_id',
        'department_id',
        'invoice_reference',
        'notes',
        'is_debt',
        'debt_status',
        'reconciled_at',
        'reconciled_by',
        'reconciliation_note',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'date'          => 'date',
        'reconciled_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerBusiness(): BelongsTo
    {
        return $this->belongsTo(CustomerBusiness::class, 'customer_business_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function designTask(): BelongsTo
    {
        return $this->belongsTo(DesignTask::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function reconciledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(FinanceReconciliation::class);
    }

    public function financeAuditTrail(): HasMany
    {
        return $this->hasMany(FinanceAuditTrail::class, 'entity_id')
                    ->where('entity_type', 'payment');
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopeActiveFinance($query)
    {
        return $query->where(function($q) {
            $q->whereDoesntHave('designTask')
               ->orWhereHas('designTask', function($tq) {
                   $tq->where('status', '!=', DesignTask::STATUS_CANCELLED)
                      ->where(function($sq) {
                          $sq->where('is_loss', false)->orWhereNull('is_loss');
                      });
               });
        })->where(function($q) {
            $q->whereDoesntHave('order')
              ->orWhereHas('order', function($oq) {
                  $oq->where('approval_status', '!=', 'cancelled');
              });
        });
    }

    public function scopePendingDebt($query)
    {
        return $query->where('is_debt', true)
                     ->where('debt_status', self::DEBT_PENDING);
    }

    public function scopeReconciled($query)
    {
        return $query->where('debt_status', self::DEBT_RECONCILED);
    }

    public function scopeOutstanding($query)
    {
        return $query->where('is_debt', true)
                     ->whereIn('debt_status', [self::DEBT_PENDING, self::DEBT_PARTIAL]);
    }

    // ── Business Logic ────────────────────────────────────────

    /**
     * Sync this payment's debt_status based on the linked task/order balance.
     * Call after any payment is created or deleted.
     */
    public function syncDebtStatus(): void
    {
        if (!$this->is_debt) {
            $this->debt_status = self::DEBT_PAID;
            $this->saveQuietly();
            return;
        }

        // Check linked design task
        if ($this->design_task_id && $task = $this->designTask) {
            $fresh = $task->fresh();
            if ($fresh->balance <= 0) {
                $this->debt_status = self::DEBT_PAID;
            } elseif ($fresh->amount_paid > 0 && $fresh->balance > 0) {
                $this->debt_status = self::DEBT_PARTIAL;
            } else {
                $this->debt_status = self::DEBT_PENDING;
            }
            $this->saveQuietly();
            return;
        }

        // Check linked order
        if ($this->order_id && $order = $this->order) {
            $fresh = $order->fresh();
            if (($fresh->balance ?? 0) <= 0) {
                $this->debt_status = self::DEBT_PAID;
            } elseif (($fresh->amount_paid ?? 0) > 0) {
                $this->debt_status = self::DEBT_PARTIAL;
            } else {
                $this->debt_status = self::DEBT_PENDING;
            }
            $this->saveQuietly();
        }
    }

    // ── Accessors ─────────────────────────────────────────────

    public function getDebtStatusLabelAttribute(): string
    {
        return match($this->debt_status) {
            self::DEBT_PENDING    => 'Pending',
            self::DEBT_PARTIAL    => 'Partial',
            self::DEBT_PAID       => 'Paid',
            self::DEBT_RECONCILED => 'Reconciled',
            self::DEBT_WAIVED     => 'Waived',
            default               => '—',
        };
    }

    public function getDebtStatusColorAttribute(): string
    {
        return match($this->debt_status) {
            self::DEBT_PENDING    => 'danger',
            self::DEBT_PARTIAL    => 'warning',
            self::DEBT_PAID       => 'success',
            self::DEBT_RECONCILED => 'info',
            self::DEBT_WAIVED     => 'secondary',
            default               => 'dark',
        };
    }
}
