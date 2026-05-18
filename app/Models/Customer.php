<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'whatsapp_number',
        'email',
        'password',
        'verified',
        'company_name',
        'business_type',
        'tax_id',
        'website',
        'address',
        'notes',
        'is_wholesale',
        'is_active',
        'added_by',
        'total_orders',
        'total_spent',
        'avg_reorder_interval',
        'last_order_date',
        'next_expected_order_date',
        'manual_follow_up_date',
        'follow_up_status',
        'priority_ranking',
        'is_repeated',
        'first_purchase_date',
        'purchase_count',
        'welcome_sms_sent_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'verified' => 'boolean',
        'is_wholesale' => 'boolean',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_order_date' => 'date',
        'next_expected_order_date' => 'date',
        'manual_follow_up_date' => 'date',
        'total_spent' => 'decimal:2',
    ];

    // Note: Orders are linked to the users table, not customers table
    // If you need to get orders for a customer, you would need to:
    // 1. Find the corresponding user record, or
    // 2. Create a proper relationship between customers and users tables

    /**
     * Scope to get only verified customers.
     */
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    /**
     * Scope to get only wholesale customers.
     */
    public function scopeWholesale($query)
    {
        return $query->where('is_wholesale', true);
    }

    /**
     * Scope to get only active customers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter customers for a specific saler.
     */
    /**
     * Get the user (saler) who added this customer.
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Get the follow-up interactions for the customer.
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(CustomerFollowUp::class);
    }

    /**
     * Get the product-specific analytics for the customer.
     */
    public function productAnalytics(): HasMany
    {
        return $this->hasMany(CustomerProductAnalytic::class);
    }

    /**
     * Get the design tasks for the customer.
     */
    public function designTasks(): HasMany
    {
        return $this->hasMany(DesignTask::class);
    }

    /**
     * Get the designs for the customer.
     */
    public function designs(): HasMany
    {
        return $this->hasMany(CustomerDesign::class);
    }

    /**
     * Get the status color based on follow-up status.
     */
    public function getStatusColorAttribute()
    {
        return match($this->follow_up_status) {
            'Overdue' => 'danger',
            'Due Today' => 'warning',
            'Upcoming' => 'primary',
            'Active' => 'info',
            'New Customer' => 'success',
            default => 'secondary'
        };
    }

    public function taskTypeAnalytics(): HasMany
    {
        return $this->hasMany(CustomerTaskTypeAnalytic::class);
    }

    /**
     * Get the effective follow-up date (manual overrides automatic).
     */
    public function getEffectiveFollowUpDateAttribute()
    {
        return $this->manual_follow_up_date ?: $this->next_expected_order_date;
    }

    /**
     * True if this customer has placed more than one purchase (design task or order).
     */
    public function getIsNewCustomerAttribute(): bool
    {
        return (int) $this->purchase_count <= 1;
    }

    public function getCustomerTypeAttribute(): string
    {
        return $this->is_repeated ? 'Repeated' : 'New';
    }

    public function getCustomerTypeBadgeAttribute(): string
    {
        return $this->is_repeated ? 'info' : 'success';
    }

    /**
     * Recalculate purchase count and update is_repeated flag.
     * Call after any new completed task or paid order.
     */
    public function recalculatePurchaseStats(): void
    {
        $taskCount  = $this->designTasks()->where('status', '!=', 'cancelled')->count();
        $orderCount = DesignTask::where('customer_id', $this->id)->count(); // already counted above
        $total = $taskCount;

        $this->purchase_count = $total;
        $this->is_repeated    = $total > 1;

        if ($total === 1 && !$this->first_purchase_date) {
            $this->first_purchase_date = today();
        }

        $this->save();
    }

    public function scopeNewCustomers($query)
    {
        return $query->where('is_repeated', false);
    }

    public function scopeRepeatedCustomers($query)
    {
        return $query->where('is_repeated', true);
    }

    public function scopeAddedThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeAddedThisMonth($query)
    {
        return $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    public function scopeForSaler($query, $user)
    {
        if (!$user || $user->role !== 'saler') {
            return $query;
        }

        return $query->where(function($q) use ($user) {
            // Customers added by this saler
            $q->where('added_by', $user->id);
            
            // Customers linked to DesignTasks assigned to this saler
            $q->orWhereExists(function($subQuery) use ($user) {
                $subQuery->selectRaw('1')
                    ->from('design_tasks')
                    ->whereColumn('design_tasks.customer_id', 'customers.id')
                    ->where('design_tasks.saler_id', $user->id);
            });

            // Legacy: Check for orders assigned to this saler via phone match
            $salerPhone = preg_replace('/[^\d\+]/', '', $user->phone ?? '');
            $salerPhone = ltrim($salerPhone, '+');
            if (!empty($salerPhone)) {
                $q->orWhereExists(function($qExists) use ($salerPhone) {
                    $qExists->selectRaw('1')
                        ->from('users')
                        ->where(function($qInner) {
                            $qInner->whereColumn('users.email', 'customers.email')
                                   ->orWhereColumn('users.phone', 'customers.phone');
                        })
                        ->whereExists(function($q2) use ($salerPhone) {
                            $q2->selectRaw('1')
                                ->from('orders')
                                ->whereColumn('orders.user_id', 'users.id')
                                ->where('orders.notes', 'like', "%Assigned to saler: +{$salerPhone}%");
                        });
                });
            }
        });
    }
}
