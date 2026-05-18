<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeKpi extends Model
{
    use HasFactory;

    protected $table = 'employee_kpis';

    protected $fillable = [
        'employee_id', 'evaluated_by', 'period_type', 'period_start', 'period_end',
        'attendance_score', 'productivity_score', 'quality_score', 'punctuality_score',
        'teamwork_score', 'overall_score', 'strengths', 'areas_for_improvement',
        'goals_next_period', 'comments',
    ];

    protected $casts = [
        'period_start'       => 'date',
        'period_end'         => 'date',
        'attendance_score'   => 'decimal:2',
        'productivity_score' => 'decimal:2',
        'quality_score'      => 'decimal:2',
        'punctuality_score'  => 'decimal:2',
        'teamwork_score'     => 'decimal:2',
        'overall_score'      => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function evaluatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function calculateOverall(): float
    {
        $scores = [
            $this->attendance_score,
            $this->productivity_score,
            $this->quality_score,
            $this->punctuality_score,
            $this->teamwork_score,
        ];
        return round(array_sum($scores) / count($scores), 2);
    }

    public function getGradeAttribute(): string
    {
        return match(true) {
            $this->overall_score >= 90 => 'A+',
            $this->overall_score >= 80 => 'A',
            $this->overall_score >= 70 => 'B',
            $this->overall_score >= 60 => 'C',
            $this->overall_score >= 50 => 'D',
            default => 'F',
        };
    }

    public function getGradeColorAttribute(): string
    {
        return match(true) {
            $this->overall_score >= 80 => 'success',
            $this->overall_score >= 60 => 'warning',
            default => 'danger',
        };
    }
}
