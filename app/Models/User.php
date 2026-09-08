<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'department_id',
        'department_ids',
        'verified',
        'is_active',
        'profile_image',
        'bio',
        'monthly_salary',
    ];

    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = \App\Services\PhoneNormalizationService::normalize($value);
    }

    /**
     * Get the department that the user belongs to.
     */
    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get all departments that the user is assigned to.
     */
    public function departments()
    {
        $ids = $this->department_ids ?? [];
        if ($this->department_id && !in_array($this->department_id, $ids)) {
            $ids[] = $this->department_id;
        }
        return Department::whereIn('id', $ids)->get();
    }

    /**
     * Check if user is assigned to a specific department.
     */
    public function isInDepartment($departmentId): bool
    {
        if ($this->department_id == $departmentId) {
            return true;
        }
        $ids = $this->department_ids ?? [];
        return in_array($departmentId, $ids);
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'verified' => 'boolean',
        'is_active' => 'boolean',
        'department_ids' => 'array',
    ];

    /**
     * Get the orders for the user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the orders where the user is the salesperson.
     */
    public function salerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'saler_id');
    }

    /**
     * Get the design tasks where the user is the salesperson.
     */
    public function designTasks(): HasMany
    {
        return $this->hasMany(DesignTask::class, 'saler_id');
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the product movements where the user is the gatekeeper.
     */
    public function gatekeeperMovements(): HasMany
    {
        return $this->hasMany(ProductMovement::class, 'gatekeeper_id');
    }

    /**
     * Get the design tasks where the user is the delivery person.
     */
    public function deliveryTasks(): HasMany
    {
        return $this->hasMany(DesignTask::class, 'delivery_id');
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is retail customer.
     */
    public function isRetailCustomer(): bool
    {
        return $this->role === 'retail_customer';
    }

    /**
     * Check if user is wholesale customer.
     */
    public function isWholesaleCustomer(): bool
    {
        return $this->role === 'wholesale_customer';
    }

    /**
     * Check if user is operator (can act as both designer and receptionist).
     */
    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    /**
     * Check if user can act as designer (designer or operator).
     */
    public function canActAsDesigner(): bool
    {
        return in_array($this->role, ['designer', 'operator']);
    }

    /**
     * Check if user can act as receptionist (receptionist or operator).
     */
    public function canActAsReceptionist(): bool
    {
        return in_array($this->role, ['receptionist', 'operator']);
    }

    /**
     * Check if user is delivery person.
     */
    public function isDelivery(): bool
    {
        return $this->role === 'delivery';
    }

    /**
     * Check if user is gatekeeper.
     */
    public function isGatekeeper(): bool
    {
        return $this->role === 'gatekeeper';
    }

    /**
     * Check if user is accountant.
     */
    public function isAccountant(): bool
    {
        return $this->role === 'accountant';
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        // For super_admin, always return true for now unless we want explicit checks
        if ($this->role === 'super_admin') {
            return true;
        }

        // Fetch roles and permissions from cache
        $rolesPermissions = \Illuminate\Support\Facades\Cache::get('roles_permissions');

        if ($rolesPermissions && isset($rolesPermissions[$this->role])) {
            $permissions = $rolesPermissions[$this->role]['permissions'] ?? [];
            if (isset($permissions[$permission])) {
                return $permissions[$permission];
            }
        }

        // Fallback default permissions if cache is missing or role not found in cache
        $defaultPermissions = [
            'admin'             => ['manage_users', 'manage_roles', 'manage_settings', 'view_audit_logs', 'manage_departments', 'manage_products', 'manage_inventory', 'manage_orders', 'manage_pos', 'manage_customers', 'manage_leads', 'manage_design_tasks', 'manage_finance', 'manage_reports', 'manage_hero_slides', 'view_contact_messages', 'manage_performance', 'manage_hr', 'manage_marketing', 'manage_delivery', 'manage_gatekeeper', 'reset_passwords'],
            'manager'           => ['manage_settings', 'view_audit_logs', 'manage_departments', 'manage_products', 'manage_inventory', 'manage_orders', 'manage_pos', 'manage_customers', 'manage_leads', 'manage_design_tasks', 'manage_finance', 'manage_reports', 'manage_hero_slides', 'view_contact_messages', 'manage_performance', 'manage_hr', 'manage_marketing'],
            'receptionist'      => ['manage_orders', 'manage_pos', 'manage_customers', 'manage_leads', 'manage_design_tasks', 'view_contact_messages'],
            'operator'          => ['manage_orders', 'manage_pos', 'manage_customers', 'manage_leads', 'manage_design_tasks', 'view_contact_messages'],
            'saler'             => ['manage_orders', 'manage_pos', 'manage_customers', 'manage_leads', 'manage_performance'],
            'designer'          => ['manage_design_tasks', 'view_contact_messages'],
            'delivery'          => ['manage_delivery', 'manage_gatekeeper'],
            'gatekeeper'        => ['manage_inventory', 'manage_gatekeeper'],
            'accountant'        => ['manage_finance', 'manage_orders', 'manage_customers', 'manage_leads', 'manage_reports', 'view_audit_logs', 'manage_departments', 'manage_performance', 'manage_hr', 'manage_gatekeeper', 'view_contact_messages'],
            'marketing_manager' => ['manage_products', 'manage_customers', 'manage_leads', 'manage_reports', 'manage_hero_slides', 'view_contact_messages', 'manage_performance', 'manage_marketing'],
            'hr_officer'        => ['manage_users', 'manage_reports', 'view_audit_logs', 'manage_departments', 'manage_performance', 'manage_hr'],
        ];

        if (isset($defaultPermissions[$this->role])) {
            return in_array($permission, $defaultPermissions[$this->role]);
        }
        
        return false;
    }

    /**
     * Scope to get only verified users.
     */
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    /**
     * Scope to get users by role.
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope to get users manageable by the current authenticated user.
     */
    public function scopeManageable($query)
    {
        $currentUser = auth()->user();
        if (!$currentUser) return $query;

        // Apply Hierarchy Logic
        if ($currentUser->role === 'accountant') {
            // Accountants can only see/manage roles NOT in [super_admin, admin, manager, accountant]
            return $query->whereNotIn('role', ['super_admin', 'admin', 'manager', 'accountant']);
        } elseif ($currentUser->role === 'admin') {
            // Admins cannot manage super_admins
            return $query->where('role', '!=', 'super_admin');
        }

        return $query;
    }

    /**
     * Scope to get staff/users viewable by the current authenticated user in operational contexts.
     */
    public function scopeViewableStaff($query)
    {
        $currentUser = auth()->user();
        if (!$currentUser) return $query;

        if ($currentUser->role === 'accountant') {
            return $query->whereNotIn('role', ['super_admin', 'admin', 'manager', 'accountant']);
        }

        return $query;
    }
}