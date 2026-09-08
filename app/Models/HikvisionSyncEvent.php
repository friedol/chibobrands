<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HikvisionSyncEvent extends Model
{
    protected $fillable = [
        'device_id', 'serial_no', 'employee_no', 'employee_id',
        'event_time', 'major', 'minor', 'verify_mode',
        'door_no', 'card_reader_no',
        'status', 'attendance_id', 'failure_reason',
    ];

    protected $casts = [
        'event_time' => 'datetime',
    ];

    // Status constants
    const STATUS_PROCESSED = 'processed';
    const STATUS_DUPLICATE = 'duplicate';
    const STATUS_IGNORED   = 'ignored';
    const STATUS_FAILED    = 'failed';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
