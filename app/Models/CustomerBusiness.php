<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerBusiness extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'business_name',
        'business_type',
        'phone',
        'email',
        'country',
        'region_id',
        'district_id',
        'address',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = \App\Services\PhoneNormalizationService::normalize($value);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function designTasks(): HasMany
    {
        return $this->hasMany(DesignTask::class, 'customer_business_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_business_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'customer_business_id');
    }
}
