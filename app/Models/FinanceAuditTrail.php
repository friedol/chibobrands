<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class FinanceAuditTrail extends Model
{
    protected $table = 'finance_audit_trail';

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'old_value',
        'new_value',
        'reason',
        'transaction_date',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_value'        => 'array',
        'new_value'        => 'array',
        'transaction_date' => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopeForEntity($query, string $type, int $id)
    {
        return $query->where('entity_type', $type)->where('entity_id', $id);
    }

    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    // ── Static Helper ─────────────────────────────────────────

    /**
     * One-liner to record a finance audit event from anywhere in the app.
     *
     * @param  string       $action          created|updated|deleted|reconciled|adjusted|waived
     * @param  string       $entityType      payment|design_task|order|reconciliation|expense
     * @param  int|null     $entityId
     * @param  array|null   $oldValue
     * @param  array|null   $newValue
     * @param  string       $reason
     * @param  string|null  $transactionDate  YYYY-MM-DD (historical date)
     */
    public static function record(
        string $action,
        string $entityType,
        ?int   $entityId,
        ?array $oldValue,
        ?array $newValue,
        string $reason,
        ?string $transactionDate = null
    ): self {
        return static::create([
            'user_id'          => Auth::id(),
            'action'           => $action,
            'entity_type'      => $entityType,
            'entity_id'        => $entityId,
            'old_value'        => $oldValue,
            'new_value'        => $newValue,
            'reason'           => $reason,
            'transaction_date' => $transactionDate,
            'ip_address'       => Request::ip(),
            'user_agent'       => Request::userAgent(),
        ]);
    }

    // ── Accessors ─────────────────────────────────────────────

    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'created'     => 'Created',
            'updated'     => 'Updated',
            'deleted'     => 'Deleted',
            'reconciled'  => 'Reconciled',
            'adjusted'    => 'Adjusted',
            'waived'      => 'Waived',
            default       => ucfirst($this->action),
        };
    }

    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'created'    => 'success',
            'updated'    => 'primary',
            'deleted'    => 'danger',
            'reconciled' => 'info',
            'adjusted'   => 'warning',
            'waived'     => 'secondary',
            default      => 'dark',
        };
    }
}
