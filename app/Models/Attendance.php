<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'attendance_date', 'clock_in', 'clock_out',
        'hours_worked', 'status', 'is_late', 'late_minutes',
        'location', 'method', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'is_late' => 'boolean',
        'hours_worked' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'present'  => 'success',
            'late'     => 'warning',
            'absent'   => 'danger',
            'half_day' => 'info',
            'on_leave' => 'primary',
            'holiday'  => 'secondary',
            default    => 'secondary',
        };
    }

    public function calculateHoursWorked(): void
    {
        if ($this->clock_in && $this->clock_out) {
            $in  = Carbon::parse($this->clock_in);
            $out = Carbon::parse($this->clock_out);
            $this->hours_worked = round($in->diffInMinutes($out) / 60, 2);
            $this->save();
        }
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('attendance_date', $date);
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('attendance_date', $year)->whereMonth('attendance_date', $month);
    }
}
