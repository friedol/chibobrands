<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'saler_id',
        'order_code',
        'subtotal',
        'discount',
        'total_amount',
        'vat_amount',
        'amount_paid',
        'balance',
        'payment_status',
        'approval_status',
        'department_id',
        'notes',
        'type',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'order_code';
    }

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the saler that owns the order.
     */
    public function saler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saler_id');
    }

    /**
     * Get the department that the order belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the order items for the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the WhatsApp requests for the order.
     */
    public function whatsappRequests(): HasMany
    {
        return $this->hasMany(WhatsappRequest::class);
    }

    /**
     * Scope to get orders by approval status.
     */
    public function scopeByApprovalStatus($query, $status)
    {
        return $query->where('approval_status', $status);
    }

    /**
     * Scope to get orders by payment status.
     */
    public function scopeByPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope to get pending orders.
     */
    public function scopePending($query)
    {
        return $query->where('approval_status', 'requested');
    }

    /**
     * Scope to get approved orders.
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    /**
     * Scope to get cancelled orders.
     */
    public function scopeCancelled($query)
    {
        return $query->where('approval_status', 'cancelled');
    }

    /**
     * Check if order is pending.
     */
    public function isPending(): bool
    {
        return $this->approval_status === 'requested';
    }

    /**
     * Check if order is approved.
     */
    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    /**
     * Check if order is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->approval_status === 'cancelled';
    }

    /**
     * Get formatted total amount.
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'TZS ' . number_format($this->total_amount, 0);
    }

    public function scopeActiveFinance($query)
    {
        return $query->where('approval_status', '!=', 'cancelled');
    }

    /**
     * Generate unique order code.
     */
    public static function generateOrderCode(): string
    {
        do {
            $code = 'CHB-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (static::where('order_code', $code)->exists());

        return $code;
    }

    /**
     * Get the status (mapped to approval_status for backward compatibility).
     */
    public function getStatusAttribute(): string
    {
        return $this->approval_status;
    }

    /**
     * Set the status (mapped to approval_status for backward compatibility).
     */
    public function setStatusAttribute($value): void
    {
        $this->attributes['approval_status'] = $value;
    }

    /**
     * Generate WhatsApp message for this order.
     */
    public function generateWhatsAppMessage(): string
    {
        $message = "🛒 *New Order {$this->order_code}*\n\n";
        
        foreach ($this->items as $item) {
            $productName = $item->product_name ?? ($item->product->name ?? 'Unknown Product');
            $message .= "📦 *{$productName}* x{$item->quantity}\n";
            
            // Show variant information if available
            if ($item->variants && count($item->variants) > 0) {
                $variantText = [];
                foreach ($item->variants as $key => $value) {
                    if ($value) {
                        // Handle different value types
                        if (is_array($value)) {
                            $displayValue = implode(', ', $value);
                        } elseif (is_object($value)) {
                            // Handle object values - extract name or value property
                            if (isset($value->name)) {
                                $displayValue = $value->name;
                            } elseif (isset($value->value)) {
                                $displayValue = $value->value;
                            } elseif (isset($value->label)) {
                                $displayValue = $value->label;
                            } else {
                                $displayValue = (string) $value;
                            }
                        } else {
                            $displayValue = (string) $value;
                        }
                        
                        if (!empty($displayValue)) {
                            $variantText[] = "{$key}: {$displayValue}";
                        }
                    }
                }
                if (!empty($variantText)) {
                    $message .= "   Options: " . implode(', ', $variantText) . "\n";
                }
            }
            
            $message .= "   💰 Price: TZS " . number_format($item->unit_price, 0) . " each\n";
            $message .= "   📊 Subtotal: TZS " . number_format($item->subtotal, 0) . "\n";
            
            // Show channel information
            if ($item->channel) {
                $message .= "   🏪 Channel: " . ucfirst($item->channel) . "\n";
            }
            
            $message .= "\n";
        }
        
        $message .= "💵 *Total: TZS " . number_format($this->total_amount, 0) . "*\n\n";
        $message .= "👤 Customer: {$this->user->name}\n";
        $message .= "📞 Phone: {$this->user->phone}\n";
        $message .= "📧 Email: {$this->user->email}\n";
        $message .= "💳 Payment: " . ucfirst($this->payment_status) . "\n";
        $message .= "✅ Status: " . ucfirst($this->approval_status);
        
        if ($this->notes) {
            $message .= "\n📝 Notes: {$this->notes}";
        }

        return $message;
    }
}