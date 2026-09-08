<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Log;

class HRLeaveAutomationService
{
    /**
     * Run all automated leave processing:
     * 1. Activate approved leaves whose start_date has arrived.
     * 2. Complete leaves whose end_date has expired and restore employee Active status.
     */
    public static function runAutomation(): array
    {
        $activatedCount = static::activateStartingLeaves();
        $completedCount = static::processExpiredLeaves();

        return [
            'activated' => $activatedCount,
            'completed' => $completedCount,
        ];
    }

    /**
     * Activate approved leaves that have started (start_date <= today <= end_date).
     * Updates employee status to 'on_leave'.
     */
    public static function activateStartingLeaves(): int
    {
        $today = today();
        
        $startingLeaves = LeaveRequest::with('employee')
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->get();

        $count = 0;
        foreach ($startingLeaves as $leave) {
            if ($leave->employee && $leave->employee->status !== 'on_leave') {
                $oldStatus = $leave->employee->status;
                $leave->employee->update(['status' => 'on_leave']);

                AuditLogService::log(
                    'system_automation',
                    "Automated Leave Activation: Employee #{$leave->employee->id} ({$leave->employee->full_name}) marked 'on_leave' for {$leave->leave_type} leave (Start: {$leave->start_date->format('Y-m-d')}, End: {$leave->end_date->format('Y-m-d')}). Previous status: {$oldStatus}.",
                    $leave
                );

                $count++;
            }
        }

        return $count;
    }

    /**
     * Detect and complete expired leaves (end_date < today).
     * Updates LeaveRequest status to 'completed' and restores Employee status to 'active'.
     */
    public static function processExpiredLeaves(): int
    {
        $today = today();

        // Find approved or active leaves whose end_date has passed
        $expiredLeaves = LeaveRequest::with('employee')
            ->whereIn('status', ['approved'])
            ->whereDate('end_date', '<', $today)
            ->get();

        $completedCount = 0;

        foreach ($expiredLeaves as $leave) {
            $employee = $leave->employee;
            $prevLeaveStatus = $leave->status;
            
            // Mark Leave Request as Completed
            $leave->update([
                'status' => 'completed',
                'notes'  => trim(($leave->notes ?? '') . " [System Auto-Completed on " . now()->format('Y-m-d H:i') . "]"),
            ]);

            if ($employee) {
                $prevEmpStatus = $employee->status;

                // Check if employee has another active approved leave covering today
                $hasOtherActiveLeave = LeaveRequest::where('employee_id', $employee->id)
                    ->where('id', '!=', $leave->id)
                    ->where('status', 'approved')
                    ->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->exists();

                if (!$hasOtherActiveLeave && $employee->status === 'on_leave') {
                    $employee->update(['status' => 'active']);

                    AuditLogService::log(
                        'system_automation',
                        "Automated Leave Completion: Expired {$leave->leave_type} leave #{$leave->id} for {$employee->full_name} marked as Completed. Employee status restored from '{$prevEmpStatus}' to 'active'.",
                        $leave
                    );
                } else {
                    AuditLogService::log(
                        'system_automation',
                        "Automated Leave Completion: Expired {$leave->leave_type} leave #{$leave->id} for {$employee->full_name} marked as Completed.",
                        $leave
                    );
                }
            }

            $completedCount++;
        }

        return $completedCount;
    }

    /**
     * Fetch structured leave statistics and returning employee groups for HR Dashboard.
     */
    public static function getDashboardMetrics(): array
    {
        // Run automation first so metrics are 100% up to date
        static::runAutomation();

        $today = today();

        // 1. Employees Currently On Leave
        $onLeaveEmployees = Employee::where('status', 'on_leave')
            ->with(['leaveRequests' => function ($q) use ($today) {
                $q->where('status', 'approved')
                  ->whereDate('start_date', '<=', $today)
                  ->whereDate('end_date', '>=', $today);
            }])
            ->get();

        // 2. Employees Returning Today (end_date = today)
        $returningToday = LeaveRequest::with('employee')
            ->whereIn('status', ['approved', 'completed'])
            ->whereDate('end_date', $today)
            ->get();

        // 3. Employees Returning Soon (end_date within next 7 days)
        $returningSoon = LeaveRequest::with('employee')
            ->where('status', 'approved')
            ->whereDate('end_date', '>', $today)
            ->whereDate('end_date', '<=', $today->copy()->addDays(7))
            ->orderBy('end_date', 'asc')
            ->get();

        // 4. Recently Completed Leaves
        $recentlyCompleted = LeaveRequest::with(['employee', 'approvedBy'])
            ->where('status', 'completed')
            ->latest('updated_at')
            ->limit(10)
            ->get();

        // 5. Expired Leave Records
        $expiredRecords = LeaveRequest::with(['employee', 'approvedBy'])
            ->where('status', 'completed')
            ->whereDate('end_date', '<', $today)
            ->latest('end_date')
            ->limit(10)
            ->get();

        return [
            'on_leave_employees' => $onLeaveEmployees,
            'returning_today'    => $returningToday,
            'returning_soon'     => $returningSoon,
            'recently_completed' => $recentlyCompleted,
            'expired_records'    => $expiredRecords,
        ];
    }
}
