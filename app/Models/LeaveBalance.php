<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'year', 'leave_type', 'total_days', 'used_days', 'remaining_days',
    ];

    protected $casts = [
        'total_days'     => 'decimal:1',
        'used_days'      => 'decimal:1',
        'remaining_days' => 'decimal:1',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function deduct(float $days): void
    {
        $this->used_days      += $days;
        $this->remaining_days  = $this->total_days - $this->used_days;
        $this->save();
    }
}
