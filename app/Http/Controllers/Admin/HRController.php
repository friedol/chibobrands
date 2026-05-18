<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\EmployeeKpi;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\AuditLogService;

class HRController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    //  EMPLOYEE MANAGEMENT
    // ══════════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $query = Employee::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('employee_code', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
            );
        }

        if ($request->filled('department') && $request->department !== 'all') {
            $query->where('department', $request->department);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('contract_type') && $request->contract_type !== 'all') {
            $query->where('contract_type', $request->contract_type);
        }

        $employees   = $query->orderBy('full_name')->paginate(20)->withQueryString();
        $departments = Employee::distinct()->pluck('department')->filter()->sort()->values();
        $linkedUsers = User::whereNotIn('id', Employee::whereNotNull('user_id')->pluck('user_id'))->get();

        // Summary cards
        $totalActive     = Employee::active()->count();
        $onLeave         = Employee::where('status', 'on_leave')->count();
        $pendingLeaves   = LeaveRequest::pending()->count();
        $todayPresent    = Attendance::whereDate('attendance_date', today())->where('status', 'present')->count();

        return view('admin.hr.index', compact(
            'employees', 'departments', 'linkedUsers',
            'totalActive', 'onLeave', 'pendingLeaves', 'todayPresent'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'     => 'required|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'national_id'   => 'nullable|string|max:50',
            'department'    => 'nullable|string|max:100',
            'role_title'    => 'nullable|string|max:100',
            'contract_type' => 'required|in:permanent,contract,part_time,intern',
            'hire_date'     => 'nullable|date',
            'contract_end_date' => 'nullable|date',
            'basic_salary'  => 'nullable|numeric|min:0',
            'allowances'    => 'nullable|numeric|min:0',
            'deductions'    => 'nullable|numeric|min:0',
            'bank_name'     => 'nullable|string|max:100',
            'bank_account'  => 'nullable|string|max:50',
            'emergency_contact_name'  => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'address'       => 'nullable|string',
            'notes'         => 'nullable|string',
            'user_id'       => 'nullable|exists:users,id',
            'photo'         => 'nullable|image|max:2048',
        ]);

        $validated['employee_code'] = Employee::generateCode();
        $validated['basic_salary']  = $validated['basic_salary'] ?? 0;
        $validated['allowances']    = $validated['allowances'] ?? 0;
        $validated['deductions']    = $validated['deductions'] ?? 0;

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('employees/photos', 'public');
        }

        $employee = Employee::create($validated);
        AuditLogService::created($employee, "Added employee: {$employee->full_name} ({$employee->employee_code})");

        return redirect()->route('admin.hr.index')->with('success', 'Employee added successfully.');
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'attendances'  => fn($q) => $q->latest()->limit(30),
            'leaveRequests',
            'kpis'         => fn($q) => $q->latest()->limit(5),
            'documents'    => fn($q) => $q->latest(),
        ]);

        // Attendance summary for current month
        $month        = now()->month;
        $year         = now()->year;
        $monthAttend  = Attendance::where('employee_id', $employee->id)->forMonth($year, $month)->get();
        $presentDays  = $monthAttend->where('status', 'present')->count();
        $absentDays   = $monthAttend->where('status', 'absent')->count();
        $lateDays     = $monthAttend->where('is_late', true)->count();
        $totalWorked  = $monthAttend->sum('hours_worked');

        // Leave balance for current year
        $leaveBalances = LeaveBalance::where('employee_id', $employee->id)->where('year', $year)->get();

        // Latest KPI
        $latestKpi = $employee->kpis()->latest()->first();

        $documentTypes = [
            'national_id' => 'National ID', 'passport' => 'Passport',
            'contract' => 'Contract', 'certificate' => 'Certificate',
            'insurance' => 'Insurance', 'bank_letter' => 'Bank Letter',
            'nssf' => 'NSSF Card', 'nhif' => 'NHIF Card', 'other' => 'Other',
        ];

        return view('admin.hr.show', compact(
            'employee', 'monthAttend', 'presentDays', 'absentDays',
            'lateDays', 'totalWorked', 'leaveBalances', 'latestKpi', 'documentTypes'
        ));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'full_name'     => 'required|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'national_id'   => 'nullable|string|max:50',
            'department'    => 'nullable|string|max:100',
            'role_title'    => 'nullable|string|max:100',
            'contract_type' => 'required|in:permanent,contract,part_time,intern',
            'hire_date'     => 'nullable|date',
            'contract_end_date' => 'nullable|date',
            'basic_salary'  => 'nullable|numeric|min:0',
            'allowances'    => 'nullable|numeric|min:0',
            'deductions'    => 'nullable|numeric|min:0',
            'bank_name'     => 'nullable|string|max:100',
            'bank_account'  => 'nullable|string|max:50',
            'emergency_contact_name'  => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'address'       => 'nullable|string',
            'status'        => 'required|in:active,inactive,terminated,on_leave',
            'notes'         => 'nullable|string',
            'user_id'       => 'nullable|exists:users,id',
        ]);

        if ($request->hasFile('photo')) {
            if ($employee->photo) Storage::disk('public')->delete($employee->photo);
            $validated['photo'] = $request->file('photo')->store('employees/photos', 'public');
        }

        $oldValues = $employee->toArray();
        $employee->update($validated);
        AuditLogService::updated($employee, $oldValues, "Updated employee: {$employee->full_name}");

        return redirect()->back()->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        AuditLogService::deleted($employee, "Deleted employee: {$employee->full_name} ({$employee->employee_code})");
        if ($employee->photo) Storage::disk('public')->delete($employee->photo);
        $employee->delete();
        return redirect()->route('admin.hr.index')->with('success', 'Employee removed.');
    }

    // ══════════════════════════════════════════════════════════════
    //  ATTENDANCE
    // ══════════════════════════════════════════════════════════════

    public function attendance(Request $request)
    {
        $date      = $request->get('date', today()->format('Y-m-d'));
        $employees = Employee::active()->with(['attendances' => fn($q) => $q->whereDate('attendance_date', $date)])->get();

        // Enrich with today's record
        $employees->each(function ($emp) use ($date) {
            $emp->today = $emp->attendances->first();
        });

        // Summary
        $present = Attendance::whereDate('attendance_date', $date)->where('status', 'present')->count();
        $absent  = Attendance::whereDate('attendance_date', $date)->where('status', 'absent')->count();
        $late    = Attendance::whereDate('attendance_date', $date)->where('is_late', true)->count();
        $total   = Employee::active()->count();

        return view('admin.hr.attendance', compact('employees', 'date', 'present', 'absent', 'late', 'total'));
    }

    public function storeAttendance(Request $request)
    {
        $validated = $request->validate([
            'employee_id'     => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'clock_in'        => 'nullable|date_format:H:i',
            'clock_out'       => 'nullable|date_format:H:i',
            'status'          => 'required|in:present,absent,late,half_day,on_leave,holiday',
            'notes'           => 'nullable|string',
        ]);

        $validated['recorded_by'] = auth()->id();
        $validated['method']      = 'manual';

        // Detect late (assume 09:00 is start time)
        if (!empty($validated['clock_in'])) {
            $startTime  = Carbon::parse($validated['attendance_date'] . ' 09:00:00');
            $clockIn    = Carbon::parse($validated['attendance_date'] . ' ' . $validated['clock_in']);
            if ($clockIn->gt($startTime)) {
                $validated['is_late']     = true;
                $validated['late_minutes'] = $startTime->diffInMinutes($clockIn);
                if ($validated['status'] === 'present') {
                    $validated['status'] = 'late';
                }
            }
        }

        // Calculate hours worked
        if (!empty($validated['clock_in']) && !empty($validated['clock_out'])) {
            $in  = Carbon::parse($validated['clock_in']);
            $out = Carbon::parse($validated['clock_out']);
            $validated['hours_worked'] = round($in->diffInMinutes($out) / 60, 2);
        }

        $attendance = Attendance::updateOrCreate(
            ['employee_id' => $validated['employee_id'], 'attendance_date' => $validated['attendance_date']],
            $validated
        );
        AuditLogService::log('attendance', "Recorded attendance for employee #{$validated['employee_id']} on {$validated['attendance_date']}: {$validated['status']}", $attendance);

        return redirect()->back()->with('success', 'Attendance recorded.');
    }

    public function bulkAttendance(Request $request)
    {
        $date    = $request->get('date', today()->format('Y-m-d'));
        $records = $request->get('records', []);

        $count = 0;
        foreach ($records as $empId => $record) {
            Attendance::updateOrCreate(
                ['employee_id' => $empId, 'attendance_date' => $date],
                [
                    'status'      => $record['status'] ?? 'absent',
                    'clock_in'    => $record['clock_in'] ?? null,
                    'clock_out'   => $record['clock_out'] ?? null,
                    'notes'       => $record['notes'] ?? null,
                    'recorded_by' => auth()->id(),
                    'method'      => 'manual',
                ]
            );
            $count++;
        }
        AuditLogService::log('attendance', "Bulk attendance saved for {$count} employees on {$date}");

        return redirect()->back()->with('success', 'Bulk attendance saved for ' . $date . '.');
    }

    public function attendanceReport(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        $employees = Employee::active()->get()->map(function ($emp) use ($month, $year) {
            $records       = Attendance::where('employee_id', $emp->id)->forMonth($year, $month)->get();
            $emp->present  = $records->where('status', 'present')->count();
            $emp->absent   = $records->where('status', 'absent')->count();
            $emp->late     = $records->where('is_late', true)->count();
            $emp->on_leave = $records->where('status', 'on_leave')->count();
            $emp->total_hours = round($records->sum('hours_worked'), 1);
            return $emp;
        });

        $workingDays = $this->countWorkingDays($year, $month);

        return view('admin.hr.attendance-report', compact('employees', 'month', 'year', 'workingDays'));
    }

    // ══════════════════════════════════════════════════════════════
    //  LEAVE MANAGEMENT
    // ══════════════════════════════════════════════════════════════

    public function leaves(Request $request)
    {
        $query = LeaveRequest::with('employee')->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('leave_type') && $request->leave_type !== 'all') {
            $query->where('leave_type', $request->leave_type);
        }

        $leaveRequests = $query->paginate(20)->withQueryString();
        $employees     = Employee::active()->orderBy('full_name')->get();
        $pendingCount  = LeaveRequest::pending()->count();

        return view('admin.hr.leaves', compact('leaveRequests', 'employees', 'pendingCount'));
    }

    public function storeLeave(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type'  => 'required|string|max:50',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'reason'      => 'required|string',
            'notes'       => 'nullable|string',
        ]);

        $start  = Carbon::parse($validated['start_date']);
        $end    = Carbon::parse($validated['end_date']);
        $validated['total_days'] = $start->diffInDays($end) + 1;

        LeaveRequest::create($validated);

        return redirect()->back()->with('success', 'Leave request submitted.');
    }

    public function approveLeave(Request $request, LeaveRequest $leave)
    {
        $leave->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        AuditLogService::log('approved', "Approved {$leave->leave_type} leave for employee #{$leave->employee_id} ({$leave->total_days} days)", $leave);

        // Deduct from balance
        $balance = LeaveBalance::firstOrCreate(
            ['employee_id' => $leave->employee_id, 'year' => now()->year, 'leave_type' => $leave->leave_type],
            ['total_days' => 21, 'used_days' => 0, 'remaining_days' => 21]
        );
        $balance->deduct($leave->total_days);

        // Mark employee as on_leave if dates cover today
        if ($leave->start_date->lte(today()) && $leave->end_date->gte(today())) {
            $leave->employee->update(['status' => 'on_leave']);
        }

        return redirect()->back()->with('success', 'Leave approved.');
    }

    public function rejectLeave(Request $request, LeaveRequest $leave)
    {
        $request->validate(['rejection_reason' => 'required|string']);

        $leave->update([
            'status'           => 'rejected',
            'approved_by'      => auth()->id(),
            'approved_at'      => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);
        AuditLogService::log('rejected', "Rejected {$leave->leave_type} leave for employee #{$leave->employee_id}: {$request->rejection_reason}", $leave);

        return redirect()->back()->with('success', 'Leave rejected.');
    }

    // ══════════════════════════════════════════════════════════════
    //  KPI & EVALUATIONS
    // ══════════════════════════════════════════════════════════════

    public function kpis(Request $request)
    {
        $query = EmployeeKpi::with(['employee', 'evaluatedBy'])->latest();

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('period_type') && $request->period_type !== 'all') {
            $query->where('period_type', $request->period_type);
        }

        $kpis      = $query->paginate(20)->withQueryString();
        $employees = Employee::active()->orderBy('full_name')->get();

        return view('admin.hr.kpis', compact('kpis', 'employees'));
    }

    public function storeKpi(Request $request)
    {
        $validated = $request->validate([
            'employee_id'        => 'required|exists:employees,id',
            'period_type'        => 'required|in:daily,weekly,monthly',
            'period_start'       => 'required|date',
            'period_end'         => 'required|date|after_or_equal:period_start',
            'attendance_score'   => 'required|numeric|min:0|max:100',
            'productivity_score' => 'required|numeric|min:0|max:100',
            'quality_score'      => 'required|numeric|min:0|max:100',
            'punctuality_score'  => 'required|numeric|min:0|max:100',
            'teamwork_score'     => 'required|numeric|min:0|max:100',
            'strengths'          => 'nullable|string',
            'areas_for_improvement' => 'nullable|string',
            'goals_next_period'  => 'nullable|string',
            'comments'           => 'nullable|string',
        ]);

        $validated['evaluated_by']  = auth()->id();
        $validated['overall_score'] = round(
            ($validated['attendance_score'] + $validated['productivity_score'] +
             $validated['quality_score'] + $validated['punctuality_score'] +
             $validated['teamwork_score']) / 5,
            2
        );

        $kpi = EmployeeKpi::create($validated);
        AuditLogService::log('created', "KPI evaluation recorded for employee #{$kpi->employee_id}: overall score {$kpi->overall_score}", $kpi);

        return redirect()->back()->with('success', 'KPI evaluation saved.');
    }

    // ══════════════════════════════════════════════════════════════
    //  EMPLOYEE DOCUMENTS
    // ══════════════════════════════════════════════════════════════

    public function uploadDocument(Request $request, Employee $employee)
    {
        $request->validate([
            'document_type' => 'required|in:national_id,passport,contract,certificate,insurance,bank_letter,nssf,nhif,other',
            'title'         => 'required|string|max:255',
            'file'          => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,gif,doc,docx,xls,xlsx',
            'notes'         => 'nullable|string|max:500',
            'expiry_date'   => 'nullable|date',
        ]);

        $file      = $request->file('file');
        $path      = $file->store("employees/documents/{$employee->id}", 'public');

        $document = EmployeeDocument::create([
            'employee_id'   => $employee->id,
            'uploaded_by'   => auth()->id(),
            'document_type' => $request->document_type,
            'title'         => $request->title,
            'file_path'     => $path,
            'file_name'     => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'notes'         => $request->notes,
            'expiry_date'   => $request->expiry_date,
        ]);
        AuditLogService::created($document, "Uploaded document '{$document->title}' for {$employee->full_name}");

        return redirect()->route('admin.hr.show', $employee)->with('success', 'Document uploaded successfully.');
    }

    public function deleteDocument(Employee $employee, EmployeeDocument $document)
    {
        if ($document->employee_id !== $employee->id) {
            abort(403);
        }

        AuditLogService::deleted($document, "Deleted document '{$document->title}' from {$employee->full_name}");
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return redirect()->route('admin.hr.show', $employee)->with('success', 'Document deleted.');
    }

    // ══════════════════════════════════════════════════════════════
    //  HELPERS
    // ══════════════════════════════════════════════════════════════

    private function countWorkingDays(int $year, int $month): int
    {
        $start = Carbon::create($year, $month, 1);
        $end   = $start->copy()->endOfMonth();
        $days  = 0;
        while ($start->lte($end)) {
            if (!$start->isWeekend()) $days++;
            $start->addDay();
        }
        return $days;
    }
}
