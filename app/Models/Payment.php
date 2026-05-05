<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
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
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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
}
