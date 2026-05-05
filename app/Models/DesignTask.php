<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\TaskUpdate;

class DesignTask extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'task_code',
        'description',
        'designer_instructions',
        'reference_images',
        'customer_id',
        'receptionist_id',
        'designer_id',
        'saler_id',
        'operator_id',
        'status',
        'priority',
        'deadline',
        'completed_at',
        'price',
        'amount_paid',
        'balance',
        'requires_receipt',
        'operator_id',
        'delivery_id',
        'delivery_status',
        'delivery_notes',
        'delivered_at',
        'delivery_payment_method',
        'department_id',
        'qty',
        'rate',
        'design_task_type_id',
        'delivery_cost',
        'delivery_discount',
        'is_loss',
        'loss_reason',
        'loss_recorded_at',
        'loss_recorded_by',
        'loss_amount',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'completed_at' => 'datetime',
        'priority' => 'integer',
        'reference_images' => 'array',
        'price' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'requires_receipt' => 'boolean',
        'delivered_at' => 'datetime',
        'qty' => 'decimal:2',
        'rate' => 'decimal:2',
        'is_loss' => 'boolean',
        'loss_recorded_at' => 'datetime',
        'loss_amount' => 'decimal:2',
    ];

    protected $appends = ['status_label', 'priority_label'];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_IN_REVIEW = 'in_review';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PRINTING = 'printing';
    const STATUS_PRINTED = 'printed';
    const STATUS_SUPER_COMPLETED = 'super_completed';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    // Priority constants
    const PRIORITY_LOW = 5;
    const PRIORITY_MEDIUM = 3;
    const PRIORITY_HIGH = 1;

    public function getStatusLabelAttribute(): string
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_IN_REVIEW => 'In Review',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_PRINTING => 'Printing',
            self::STATUS_PRINTED => 'Printed',
            self::STATUS_SUPER_COMPLETED => 'Super Completed',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_CANCELLED => 'Cancelled',
        ][$this->status] ?? (string)($this->status ?? 'Unknown');
    }

    public function getPriorityLabelAttribute(): string
    {
        return [
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_LOW => 'Low',
        ][$this->priority] ?? 'Normal';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function receptionist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receptionist_id');
    }

    public function designer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    public function saler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saler_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_id');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(TaskUpdate::class, 'task_id')->latest();
    }

    public function designTaskType(): BelongsTo
    {
        return $this->belongsTo(DesignTaskType::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Department::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'loss_recorded_by');
    }

    public function latestUpdate()
    {
        return $this->hasOne(TaskUpdate::class, 'task_id')->latestOfMany();
    }

    public function scopeForDesigner($query, $designerId)
    {
        return $query->where('designer_id', $designerId);
    }

    public function scopeForOperator($query, $operatorId)
    {
        return $query->where('operator_id', $operatorId);
    }

    public function scopeForSaler($query, $user)
    {
        if (!$user || $user->role !== 'saler') {
            return $query;
        }
        return $query->where('saler_id', $user->id);
    }

    public function scopeForReceptionist($query, $receptionistId)
    {
        return $query->where('receptionist_id', $receptionistId);
    }

    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Get the status options for a design task.
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CONFIRMED => 'Design Confirmed',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_IN_REVIEW => 'In Review',
            self::STATUS_PRINTING => 'Printing',
            self::STATUS_PRINTED => 'Printed',
            self::STATUS_COMPLETED => 'Done',
            self::STATUS_SUPER_COMPLETED => 'Closed',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public function scopeActiveFinance($query)
    {
        return $query->where('status', '!=', self::STATUS_CANCELLED)
                     ->where(function($q) {
                         $q->where('is_loss', false)->orWhereNull('is_loss');
                     });
    }

    /**
     * Generate unique task code.
     */
    public static function generateTaskCode(): string
    {
        do {
            $code = 'TSK-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (static::where('task_code', $code)->exists());

        return $code;
    }
}
