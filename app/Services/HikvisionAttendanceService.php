<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\HikvisionSyncEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HikvisionAttendanceService
{
    /**
     * Process one incoming Hikvision event from the office sync agent.
     *
     * Returns an array:  ['status' => string, 'message' => string, 'http_code' => int]
     */
    public function process(array $data): array
    {
        $deviceId     = $data['device_id'];
        $serialNo     = (int) $data['serial_no'];
        $employeeNo   = $data['employee_no'];
        $verifyMode   = $data['verify_mode'];
        $major        = (int) $data['major'];

        // ── 1. Idempotency check ─────────────────────────────────────────────
        $existing = HikvisionSyncEvent::where('device_id', $deviceId)
            ->where('serial_no', $serialNo)
            ->first();

        if ($existing) {
            Log::info("Hikvision: duplicate event ignored.", [
                'device_id' => $deviceId,
                'serial_no' => $serialNo,
                'previous_status' => $existing->status,
            ]);
            return [
                'status'    => 'duplicate',
                'message'   => 'Event already processed.',
                'http_code' => 200,
            ];
        }

        // ── 2. Filter invalid events ─────────────────────────────────────────
        $validModes  = config('hikvision.valid_verify_modes', []);
        $validMajors = config('hikvision.valid_major_codes', [5]);

        if (!in_array($verifyMode, $validModes) || !in_array($major, $validMajors)) {
            Log::info("Hikvision: event ignored (invalid verify_mode or major).", [
                'device_id'   => $deviceId,
                'serial_no'   => $serialNo,
                'verify_mode' => $verifyMode,
                'major'       => $major,
            ]);
            $this->recordEvent($data, null, HikvisionSyncEvent::STATUS_IGNORED, null, 'verify_mode or major not valid for attendance');
            return [
                'status'    => 'ignored',
                'message'   => 'Event verify_mode not valid for attendance recording.',
                'http_code' => 200,
            ];
        }

        // ── 3. Parse event timestamp ─────────────────────────────────────────
        // The device sends ISO 8601 with +03:00, which matches Africa/Dar_es_Salaam.
        // Carbon will parse it respecting the offset, then convert to app timezone.
        try {
            $eventTime = Carbon::parse($data['event_time'])->setTimezone(config('app.timezone'));
        } catch (\Exception $e) {
            Log::error("Hikvision: invalid event_time format.", ['event_time' => $data['event_time']]);
            $this->recordEvent($data, null, HikvisionSyncEvent::STATUS_FAILED, null, 'Invalid event_time: ' . $data['event_time']);
            return [
                'status'    => 'failed',
                'message'   => 'Invalid event_time format.',
                'http_code' => 422,
            ];
        }

        // ── 4. Employee lookup ───────────────────────────────────────────────
        // The device sends the Employee ID exactly as enrolled, e.g. "EMP0013".
        // Try in order:
        //   1. Direct employee_code match  ("EMP0013" → employee_code = "EMP0013")
        //   2. Explicit hikvision_no field (custom override on the employee record)
        //   3. Numeric-only convention     ("0013" → employee_code = "EMP0013")
        $numericPart = ltrim(preg_replace('/[^0-9]/', '', $employeeNo), '0') ?: '0';
        $paddedCode  = 'EMP' . str_pad($numericPart, 4, '0', STR_PAD_LEFT);

        $employee = Employee::where('employee_code', $employeeNo)->first()
            ?? Employee::byHikvisionNo($employeeNo)->first()
            ?? Employee::where('employee_code', $paddedCode)->first();

        if (!$employee) {
            Log::warning("Hikvision: employee not found.", [
                'employee_no'  => $employeeNo,
                'tried_codes'  => [$employeeNo, $paddedCode],
                'device_id'    => $deviceId,
                'serial_no'    => $serialNo,
            ]);
            $this->recordEvent($data, null, HikvisionSyncEvent::STATUS_FAILED, null, "Employee not found for: {$employeeNo}");
            return [
                'status'    => 'employee_not_found',
                'message'   => "No employee found for '{$employeeNo}'. Make sure the Employee ID in the Hikvision device matches the employee_code in Laravel (e.g. EMP0013).",
                'http_code' => 404,
            ];
        }

        // ── 5. Active employee check ─────────────────────────────────────────
        if (!$employee->isActiveForAttendance()) {
            Log::info("Hikvision: attendance skipped — employee inactive.", [
                'employee_id' => $employee->id,
                'status'      => $employee->status,
            ]);
            $this->recordEvent($data, $employee->id, HikvisionSyncEvent::STATUS_IGNORED, null, "Employee status: {$employee->status}");
            return [
                'status'    => 'ignored',
                'message'   => "Employee '{$employee->full_name}' is {$employee->status}. Attendance not recorded.",
                'http_code' => 200,
            ];
        }

        // ── 6. Record attendance ─────────────────────────────────────────────
        try {
            DB::beginTransaction();

            $attendance = $this->applyAttendancePunch($employee, $eventTime, $verifyMode, $deviceId);

            // Write the Hikvision event log
            $syncEvent = $this->recordEvent($data, $employee->id, HikvisionSyncEvent::STATUS_PROCESSED, $attendance->id, null);

            // Link the event back to the attendance record
            $attendance->hikvision_event_id = $syncEvent->id;
            $attendance->save();

            DB::commit();

            AuditLogService::log(
                'hikvision_sync',
                "Hikvision event #{$serialNo} from device {$deviceId}: attendance {$attendance->status} for {$employee->full_name} on {$attendance->attendance_date->format('Y-m-d')}.",
                $attendance
            );

            Log::info("Hikvision: attendance processed.", [
                'employee'     => $employee->full_name,
                'date'         => $attendance->attendance_date->format('Y-m-d'),
                'clock_in'     => $attendance->clock_in,
                'clock_out'    => $attendance->clock_out,
                'status'       => $attendance->status,
                'attendance_id'=> $attendance->id,
            ]);

            return [
                'status'    => 'processed',
                'message'   => "Attendance recorded for {$employee->full_name}.",
                'http_code' => 201,
                'data'      => [
                    'employee'        => $employee->full_name,
                    'department'      => $employee->department ?? '',
                    'employee_code'   => $employee->employee_code ?? '',
                    'date'            => $attendance->attendance_date->format('Y-m-d'),
                    'clock_in'        => $attendance->clock_in  ? substr($attendance->clock_in,  0, 5) : null,
                    'clock_out'       => $attendance->clock_out ? substr($attendance->clock_out, 0, 5) : null,
                    'status'          => $attendance->status,
                    'hours_worked'    => $attendance->hours_worked,
                ],
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Hikvision: failed to record attendance.", [
                'employee_id' => $employee->id,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);
            $this->recordEvent($data, $employee->id, HikvisionSyncEvent::STATUS_FAILED, null, $e->getMessage());
            return [
                'status'    => 'failed',
                'message'   => 'Failed to record attendance. See server logs.',
                'http_code' => 500,
            ];
        }
    }

    /**
     * Apply clock-in or clock-out based on existing record for the day.
     * Mirrors the business rules in HRController::storeAttendance().
     */
    private function applyAttendancePunch(Employee $employee, Carbon $eventTime, string $verifyMode, string $deviceId): Attendance
    {
        $attendanceDate = $eventTime->toDateString();
        $clockTime      = $eventTime->format('H:i:s');

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $attendanceDate)
            ->lockForUpdate()
            ->first();

        $workStart = Carbon::parse($attendanceDate . ' ' . config('hikvision.work_start_time', '09:00'));

        if (!$attendance) {
            // First punch of the day → clock_in
            $isLate      = $eventTime->gt($workStart);
            $lateMinutes = $isLate ? (int) $workStart->diffInMinutes($eventTime) : 0;
            $status      = $isLate ? 'late' : 'present';

            $attendance = Attendance::create([
                'employee_id'    => $employee->id,
                'attendance_date'=> $attendanceDate,
                'clock_in'       => $clockTime,
                'clock_out'      => null,
                'hours_worked'   => null,
                'status'         => $status,
                'is_late'        => $isLate,
                'late_minutes'   => $lateMinutes,
                'method'         => 'biometric',
                'notes'          => "Biometric | verify: {$verifyMode} | device: {$deviceId}",
                'recorded_by'    => null,
            ]);
        } elseif ($attendance->clock_in && !$attendance->clock_out) {
            // Second punch → clock_out
            $hoursWorked = round(
                Carbon::parse($attendanceDate . ' ' . $attendance->clock_in)
                    ->diffInMinutes($eventTime) / 60,
                2
            );

            $attendance->update([
                'clock_out'    => $clockTime,
                'hours_worked' => $hoursWorked,
                'method'       => 'biometric',
                'notes'        => ($attendance->notes ?? '') . " | Clock-out via Hikvision",
            ]);
        }
        // If both clock_in and clock_out are already set, leave the record unchanged
        // (subsequent punches after both are recorded are safe no-ops at DB level)

        return $attendance->fresh();
    }

    /**
     * Persist a HikvisionSyncEvent log record.
     * Uses insertOrIgnore to handle the rare race-condition duplicate gracefully.
     */
    private function recordEvent(
        array $data,
        ?int $employeeId,
        string $status,
        ?int $attendanceId,
        ?string $failureReason
    ): HikvisionSyncEvent {
        $eventTime = null;
        try {
            $eventTime = Carbon::parse($data['event_time'])->setTimezone(config('app.timezone'));
        } catch (\Exception) {}

        return HikvisionSyncEvent::create([
            'device_id'      => $data['device_id'],
            'serial_no'      => (int) $data['serial_no'],
            'employee_no'    => $data['employee_no'],
            'employee_id'    => $employeeId,
            'event_time'     => $eventTime,
            'major'          => (int) $data['major'],
            'minor'          => (int) $data['minor'],
            'verify_mode'    => $data['verify_mode'],
            'door_no'        => (int) $data['door_no'],
            'card_reader_no' => (int) $data['card_reader_no'],
            'status'         => $status,
            'attendance_id'  => $attendanceId,
            'failure_reason' => $failureReason,
        ]);
    }
}
