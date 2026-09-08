<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'employee_code', 'hikvision_no', 'full_name', 'phone', 'email', 'national_id',
        'department', 'role_title', 'contract_type', 'hire_date', 'contract_end_date',
        'basic_salary', 'allowances', 'deductions', 'bank_name', 'bank_account',
        'emergency_contact_name', 'emergency_contact_phone', 'address', 'photo',
        'status', 'notes',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'contract_end_date' => 'date',
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
    ];

    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = \App\Services\PhoneNormalizationService::normalize($value);
    }

    public function setEmergencyContactPhoneAttribute($value)
    {
        $this->attributes['emergency_contact_phone'] = \App\Services\PhoneNormalizationService::normalize($value);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function kpis(): HasMany
    {
        return $this->hasMany(EmployeeKpi::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function getNetSalaryAttribute(): float
    {
        return (float) $this->basic_salary + (float) $this->allowances - (float) $this->deductions;
    }

    public function getYearsOfServiceAttribute(): float
    {
        if (!$this->hire_date) return 0;
        return round($this->hire_date->diffInMonths(now()) / 12, 1);
    }

    public function todayAttendance()
    {
        return $this->attendances()->whereDate('attendance_date', today())->first();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByHikvisionNo($query, string $hikvisionNo)
    {
        return $query->where('hikvision_no', $hikvisionNo);
    }

    public function isActiveForAttendance(): bool
    {
        return in_array($this->status, ['active', 'on_leave']);
    }

    public function scopeByDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    // Auto-generate employee code
    public static function generateCode(): string
    {
        $last = self::latest()->first();
        $num = $last ? (int) substr($last->employee_code, 3) + 1 : 1;
        return 'EMP' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
