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
        'country',
        'region_id',
        'district_id',
        'notes',
        'is_wholesale',
        'is_active',
        'registered_by_id',
        'account_owner_id',
        'branch_id',
        'customer_source',
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
        'last_reminder_sms_at',
        'last_balance_reminder_sms_at',
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
        'first_purchase_date' => 'date',
        'total_spent' => 'decimal:2',
        'last_reminder_sms_at' => 'datetime',
        'last_balance_reminder_sms_at' => 'datetime',
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
     * Get the region this customer belongs to.
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the district this customer belongs to.
     */
    public function district()
    {
        return $this->belongsTo(District::class);
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

    public function salerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    /**
     * Get the business profiles owned by this customer.
     */
    public function businesses(): HasMany
    {
        return $this->hasMany(CustomerBusiness::class);
    }

    public function primaryBusiness()
    {
        return $this->hasOne(CustomerBusiness::class)->where('is_primary', true);
    }

    /**
     * Get the leads connected to this customer.
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * Get the SMS campaign records sent to this customer.
     */
    public function smsRecipients(): HasMany
    {
        return $this->hasMany(SmsCampaignRecipient::class);
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

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by_id');
    }

    public function accountOwner()
    {
        return $this->belongsTo(User::class, 'account_owner_id');
    }

    public function branch()
    {
        return $this->belongsTo(Department::class, 'branch_id');
    }

    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = \App\Services\PhoneNormalizationService::normalize($value);
    }

    public function setWhatsappNumberAttribute($value)
    {
        $this->attributes['whatsapp_number'] = \App\Services\PhoneNormalizationService::normalize($value);
    }

    public function scopeForSaler($query, $user)
    {
        if (!$user || $user->role !== 'saler') {
            return $query;
        }

        return $query->broughtBySaler($user->id, $user->phone);
    }

    /**
     * Customers "brought by" a given staff member — by any of the ways that
     * relationship is actually recorded in this system: whoever added the
     * customer record, whoever has served them via a design task, or (legacy)
     * whoever an order's notes say the customer was assigned to. Used both by
     * scopeForSaler() (auto-scoping the logged-in saler) and directly by the
     * admin "Salesperson (brought by)" filter, which is not restricted to the
     * role=saler dropdown list — role can change after the fact while historical
     * task assignments stay put.
     */
    public function scopeBroughtBySaler($query, $salerId, ?string $salerPhone = null)
    {
        return $query->where(function ($q) use ($salerId, $salerPhone) {
            // Customers added by this staff member
            $q->where('added_by', $salerId);

            // Customers linked to DesignTasks assigned to this staff member
            $q->orWhereExists(function ($subQuery) use ($salerId) {
                $subQuery->selectRaw('1')
                    ->from('design_tasks')
                    ->whereColumn('design_tasks.customer_id', 'customers.id')
                    ->where('design_tasks.saler_id', $salerId);
            });

            // Legacy: Check for orders assigned to this staff member via phone match
            $phone = preg_replace('/[^\d\+]/', '', $salerPhone ?? '');
            $phone = ltrim($phone, '+');
            if (!empty($phone)) {
                $q->orWhereExists(function ($qExists) use ($phone) {
                    $qExists->selectRaw('1')
                        ->from('users')
                        ->where(function ($qInner) {
                            $qInner->whereColumn('users.email', 'customers.email')
                                   ->orWhereColumn('users.phone', 'customers.phone');
                        })
                        ->whereExists(function ($q2) use ($phone) {
                            $q2->selectRaw('1')
                                ->from('orders')
                                ->whereColumn('orders.user_id', 'users.id')
                                ->where('orders.notes', 'like', "%Assigned to saler: +{$phone}%");
                        });
                });
            }
        });
    }
}
