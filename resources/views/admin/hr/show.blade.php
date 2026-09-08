@extends('layouts.admin')
@section('title', 'Employee Profile — ' . $employee->full_name)

@section('content')
<div class="container-fluid py-2 pt-1">

    {{-- ── Header ── --}}
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <i class="fas fa-id-badge text-primary"></i> Employee Master Profile
            </h4>
            <p class="text-muted small mb-0">Detailed administrative profile and records for <strong>{{ $employee->full_name }}</strong>.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.hr.edit', $employee) }}" class="btn btn-primary btn-sm rounded-2 px-3 fw-semibold">
                <i class="fas fa-edit me-1"></i> Edit Profile
            </a>
            <a href="{{ route('admin.hr.index') }}" class="btn btn-outline-secondary btn-sm rounded-2 px-3 fw-semibold">
                <i class="fas fa-arrow-left me-1"></i> Back to HR
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- ── Left Column: Identity, Status & Leaves ── --}}
        <div class="col-12 col-lg-4">
            <!-- Profile Photo & Identity Card -->
            <div class="card border-0 shadow-sm mb-4 position-relative overflow-hidden hover-lift" style="border-radius:16px;">
                <div class="card-body p-4 text-center">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <p class="fw-bold x-small text-uppercase text-muted m-0" style="letter-spacing:.08em;">Identity & Status</p>
                        <span class="badge bg-secondary rounded-pill font-monospace" style="font-size:10px; padding: 4px 8px;">{{ $employee->employee_code }}</span>
                    </div>
                    
                    <!-- Avatar preview fallback -->
                    <div class="position-relative d-inline-block mb-3">
                        <div class="rounded-circle shadow-sm border border-3 border-white overflow-hidden" 
                             style="width: 140px; height: 140px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                            @if($employee->photo)
                                <img src="{{ Storage::url($employee->photo) }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <div class="w-100 h-100 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center">
                                    <span class="fw-bold text-primary fs-1">{{ strtoupper(substr($employee->full_name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <h5 class="fw-bold mb-1 text-dark">{{ $employee->full_name }}</h5>
                    <p class="text-muted small mb-3">{{ $employee->role_title ?? 'No Role' }} &bull; {{ $employee->department ?? 'No Department' }}</p>
                    
                    @php $statusColors = ['active'=>'success','inactive'=>'secondary','terminated'=>'danger','on_leave'=>'warning']; @endphp
                    <div class="mb-2">
                        <span class="badge bg-{{ $statusColors[$employee->status] ?? 'secondary' }} px-3 py-2 rounded-pill small">
                            <i class="fas fa-circle me-1" style="font-size:8px;"></i>
                            {{ ucfirst(str_replace('_',' ',$employee->status)) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Leave Balances --}}
            @if($leaveBalances->isNotEmpty())
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-body p-4">
                    <p class="fw-bold x-small text-uppercase text-muted mb-3" style="letter-spacing:.08em;">
                        <i class="fas fa-calendar-minus text-warning me-1"></i> Leave Balances ({{ now()->year }})
                    </p>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless align-middle mb-0" style="font-size: 12.5px;">
                            <thead>
                                <tr class="text-muted" style="border-bottom: 1px solid #f1f5f9;">
                                    <th class="ps-0 pb-2">Type</th>
                                    <th class="text-center pb-2">Total</th>
                                    <th class="text-center pb-2">Used</th>
                                    <th class="text-center pb-2">Left</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaveBalances as $bal)
                                <tr style="border-bottom: 1px dashed #f1f5f9;">
                                    <td class="ps-0 py-2 fw-semibold text-dark">{{ ucfirst($bal->leave_type) }}</td>
                                    <td class="text-center py-2 text-dark">{{ $bal->total_days }}</td>
                                    <td class="text-center py-2 text-danger">{{ $bal->used_days }}</td>
                                    <td class="text-center py-2 text-success fw-bold">{{ $bal->remaining_days }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Employee Documents Explorer Card -->
            <div class="card border-0 shadow-sm mb-4" id="documents-section" style="border-radius:16px; border-left: 4px solid #6366f1;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <p class="fw-bold x-small text-uppercase text-muted m-0" style="letter-spacing:.08em;">
                            <i class="fas fa-folder-open text-primary me-1"></i> E-Files ({{ $employee->documents->count() }})
                        </p>
                        <button class="btn btn-xs btn-primary rounded-pill px-2.5 py-1 fw-semibold shadow-sm" style="font-size:10px;" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                            <i class="fas fa-upload me-1"></i> Upload
                        </button>
                    </div>
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-3 border-0 shadow-sm py-2 px-3 small" style="border-radius: 8px;">
                            <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
                            <button type="button" class="btn-close" style="padding: 0.75rem;" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if($employee->documents->isEmpty())
                        <div class="text-center py-4 bg-light rounded-3 border border-dashed">
                            <i class="fas fa-folder-open text-muted opacity-50 mb-2" style="font-size: 24px;"></i>
                            <p class="text-muted small mb-0" style="font-size:11px;">No documents uploaded yet.</p>
                        </div>
                    @else
                        <div class="d-flex flex-column gap-2" style="max-height: 400px; overflow-y: auto;">
                            @foreach($employee->documents as $doc)
                                @php
                                    $expiredClass = $doc->is_expired ? 'text-danger fw-bold' : ($doc->is_expiring_soon ? 'text-warning fw-bold' : 'text-muted');
                                    $documentLabels = [
                                        'national_id' => 'National ID', 'passport' => 'Passport',
                                        'contract' => 'Contract', 'certificate' => 'Certificate',
                                        'insurance' => 'Insurance', 'bank_letter' => 'Bank Letter',
                                        'nssf' => 'NSSF Card', 'nhif' => 'NHIF Card', 'other' => 'Other File',
                                    ];
                                @endphp
                                <div class="p-3 rounded-3 border bg-light bg-opacity-50 position-relative hover-lift">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="rounded-3 bg-primary bg-opacity-10 p-2 d-flex align-items-center justify-content-center" style="width:40px; height:40px; flex-shrink:0;">
                                            <i class="fas {{ $doc->icon }} text-primary fs-5"></i>
                                        </div>
                                        <div class="overflow-hidden w-100">
                                            <div class="d-flex justify-content-between align-items-start gap-2">
                                                <h6 class="fw-bold text-dark text-truncate mb-0" style="font-size:12.5px; max-width:180px;" title="{{ $doc->title }}">{{ $doc->title }}</h6>
                                                
                                                <div class="d-flex gap-1" style="flex-shrink:0;">
                                                    <a href="{{ $doc->download_url }}" target="_blank" class="btn btn-xs btn-outline-primary rounded-circle p-0 d-flex align-items-center justify-content-center shadow-xs" style="width:24px; height:24px;" title="View File">
                                                        <i class="fas fa-download" style="font-size: 10px;"></i>
                                                    </a>
                                                    <form action="{{ route('admin.hr.documents.destroy', [$employee, $doc]) }}" method="POST" class="d-inline">
                                                        @csrf @method('DELETE')
                                                        <button type="button" class="btn btn-xs btn-outline-danger rounded-circle p-0 d-flex align-items-center justify-content-center shadow-xs" style="width:24px; height:24px;"
                                                                onclick="modernConfirm('Delete document?', () => this.closest('form').submit())">
                                                            <i class="fas fa-trash" style="font-size: 10px;"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="text-muted x-small text-truncate mt-0.5" style="font-size:10.5px;">{{ $doc->file_name }} &bull; {{ $doc->file_size_human }}</div>
                                            
                                            <div class="mt-2 pt-2 border-top border-light d-flex flex-wrap justify-content-between gap-1" style="font-size:10px;">
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2" style="font-size:9px;">
                                                    {{ $documentLabels[$doc->document_type] ?? 'Document' }}
                                                </span>
                                                @if($doc->expiry_date)
                                                    <span class="{{ $expiredClass }}">Exp: {{ $doc->expiry_date->format('d M Y') }}</span>
                                                @endif
                                            </div>
                                            
                                            @if($doc->notes)
                                                <div class="mt-1.5 p-1 bg-white rounded border small text-muted fst-italic" style="font-size: 9.5px; line-height: 1.3;">
                                                    <i class="fas fa-sticky-note me-1"></i>{{ $doc->notes }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- ── Right Column: Details & Stats ── --}}
        <div class="col-12 col-lg-8">
            {{-- This Month Attendance Summary --}}
            <div class="row g-3 mb-4">
                @foreach([
                    ['Present','success','fas fa-user-check',$presentDays],
                    ['Absent','danger','fas fa-user-times',$absentDays],
                    ['Late Days','warning','fas fa-clock',$lateDays],
                    ['Hours Worked','info','fas fa-hourglass',$totalWorked],
                ] as [$label, $color, $icon, $value])
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100 hover-lift" style="border-radius:12px;">
                        <div class="card-body d-flex align-items-center py-3 px-3">
                            <div class="rounded-circle bg-{{ $color }} bg-opacity-10 p-2 me-2 d-flex align-items-center justify-content-center" style="width:36px; height:36px; flex-shrink:0;">
                                <i class="{{ $icon }} text-{{ $color }}" style="font-size: 14px;"></i>
                            </div>
                            <div class="overflow-hidden">
                                <div class="fs-5 fw-bold text-dark mb-0">{{ is_float($value) ? number_format($value, 1) : $value }}</div>
                                <div class="x-small text-muted text-truncate" style="font-size: 10px;">{{ $label }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Personal Information Card --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-body p-4">
                    <p class="fw-bold x-small text-uppercase text-muted mb-3" style="letter-spacing:.08em;">
                        <i class="fas fa-id-card text-primary me-1"></i> Personal Information
                    </p>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3 bg-light bg-opacity-50 border border-light">
                                <span class="text-muted d-block small mb-1">Phone Number</span>
                                <span class="fw-semibold text-dark">{{ $employee->phone ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3 bg-light bg-opacity-50 border border-light">
                                <span class="text-muted d-block small mb-1">Email Address</span>
                                <span class="fw-semibold text-dark">{{ $employee->email ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3 bg-light bg-opacity-50 border border-light">
                                <span class="text-muted d-block small mb-1">National ID / NIDA</span>
                                <span class="fw-semibold text-dark">{{ $employee->national_id ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3 bg-light bg-opacity-50 border border-light">
                                <span class="text-muted d-block small mb-1">Emergency Contact</span>
                                <span class="fw-semibold text-dark">
                                    {{ $employee->emergency_contact_name ?? '—' }}
                                    @if($employee->emergency_contact_phone)
                                        <span class="text-muted fw-normal">({{ $employee->emergency_contact_phone }})</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 rounded-3 bg-light bg-opacity-50 border border-light">
                                <span class="text-muted d-block small mb-1">Residential Address</span>
                                <span class="fw-semibold text-dark">{{ $employee->address ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Job Placement & Banking Compensation Card --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px; border-left: 4px solid #10b981;">
                <div class="card-body p-4">
                    <p class="fw-bold x-small text-uppercase text-muted mb-3" style="letter-spacing:.08em;">
                        <i class="fas fa-briefcase text-success me-1"></i> Job Placement & Compensation
                    </p>
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-4">
                            <div class="p-2 border-bottom">
                                <span class="text-muted small">Department</span>
                                <div class="fw-semibold text-dark">{{ $employee->department ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="p-2 border-bottom">
                                <span class="text-muted small">Role Title</span>
                                <div class="fw-semibold text-dark">{{ $employee->role_title ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="p-2 border-bottom">
                                <span class="text-muted small">Contract Type</span>
                                <div class="fw-semibold text-dark">{{ ucfirst(str_replace('_',' ',$employee->contract_type)) }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="p-2 border-bottom">
                                <span class="text-muted small">Hire Date</span>
                                <div class="fw-semibold text-dark">{{ $employee->hire_date?->format('d M Y') ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="p-2 border-bottom">
                                <span class="text-muted small">Contract End Date</span>
                                <div class="fw-semibold text-dark">{{ $employee->contract_end_date?->format('d M Y') ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="p-2 border-bottom">
                                <span class="text-muted small">Years of Service</span>
                                <div class="fw-semibold text-dark">{{ $employee->years_of_service }} yrs</div>
                            </div>
                        </div>
                        
                        {{-- Salary & Banking detail items --}}
                        <div class="col-12 mt-3">
                            <div class="p-3 rounded-3" style="background-color: #f0fdf4; border: 1px solid #d1fae5;">
                                <div class="row g-3">
                                    <div class="col-6 col-md-3 border-end border-light">
                                        <span class="text-muted small d-block mb-1">Basic Salary</span>
                                        <span class="fw-semibold text-dark">TZS {{ number_format($employee->basic_salary) }}</span>
                                    </div>
                                    <div class="col-6 col-md-3 border-end border-light">
                                        <span class="text-muted small d-block mb-1">Allowances</span>
                                        <span class="fw-semibold text-success">+TZS {{ number_format($employee->allowances) }}</span>
                                    </div>
                                    <div class="col-6 col-md-3 border-end border-light">
                                        <span class="text-muted small d-block mb-1">Deductions</span>
                                        <span class="fw-semibold text-danger">-TZS {{ number_format($employee->deductions) }}</span>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <span class="text-muted small d-block mb-1">Net Salary</span>
                                        <span class="fw-bold text-success fs-5">TZS {{ number_format($employee->net_salary) }}</span>
                                    </div>
                                </div>
                                
                                @if($employee->bank_name || $employee->bank_account)
                                <div class="row mt-3 pt-2 border-top border-light g-2 small">
                                    <div class="col-sm-6">
                                        <span class="text-muted">Bank Name:</span>
                                        <span class="fw-semibold text-dark ms-1">{{ $employee->bank_name ?? '—' }}</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="text-muted">Account Number:</span>
                                        <span class="fw-semibold text-dark ms-1">{{ $employee->bank_account ?? '—' }}</span>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Latest KPI Evaluation --}}
            @if($latestKpi)
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <p class="fw-bold x-small text-uppercase text-muted m-0" style="letter-spacing:.08em;">
                            <i class="fas fa-star text-warning me-1"></i> Latest KPI Evaluation
                        </p>
                        <span class="badge bg-{{ $latestKpi->grade_color }} fs-6 px-3 rounded-pill">{{ $latestKpi->grade }}</span>
                    </div>
                    
                    <div class="row g-2 text-center mb-3">
                        @foreach(['attendance_score'=>'Attendance','productivity_score'=>'Productivity','quality_score'=>'Quality','punctuality_score'=>'Punctuality','teamwork_score'=>'Teamwork'] as $field => $lbl)
                        <div class="col">
                            <div class="fw-bold fs-5 {{ $latestKpi->$field >= 80 ? 'text-success' : ($latestKpi->$field >= 60 ? 'text-warning' : 'text-danger') }}">
                                {{ number_format($latestKpi->$field, 1) }}
                            </div>
                            <div class="x-small text-muted" style="font-size:10px;">{{ $lbl }}</div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="progress mb-2 rounded-pill" style="height:12px">
                        <div class="progress-bar bg-{{ $latestKpi->grade_color }} rounded-pill" style="width:{{ $latestKpi->overall_score }}%">
                            {{ number_format($latestKpi->overall_score, 1) }}%
                        </div>
                    </div>
                    @if($latestKpi->comments)
                        <p class="text-muted small mb-0 mt-3 p-2 bg-light rounded-3 border-start border-3 border-secondary"><i class="fas fa-comment-dots text-muted me-1"></i>{{ $latestKpi->comments }}</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Recent Attendance Records --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-body p-4">
                    <p class="fw-bold x-small text-uppercase text-muted mb-3" style="letter-spacing:.08em;">
                        <i class="fas fa-list text-info me-1"></i> Recent Attendance (Last 30 records)
                    </p>
                    @if($employee->attendances->isEmpty())
                        <div class="text-center py-4 bg-light rounded-3">
                            <i class="fas fa-user-clock text-muted opacity-50 mb-2" style="font-size: 24px;"></i>
                            <p class="text-muted small mb-0">No attendance records registered yet.</p>
                        </div>
                    @else
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0 small" style="font-size:12.5px;">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th class="ps-2 py-2">Date</th>
                                    <th class="py-2">Status</th>
                                    <th class="py-2">Clock In</th>
                                    <th class="py-2">Clock Out</th>
                                    <th class="py-2 text-center">Hours</th>
                                    <th class="pe-2 py-2 text-end">Late</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employee->attendances as $att)
                                <tr style="border-bottom: 1px dashed #f1f5f9;">
                                    <td class="ps-2 py-2 fw-semibold text-dark">{{ $att->attendance_date->format('d M Y') }}</td>
                                    <td class="py-2"><span class="badge bg-{{ $att->status_badge }} rounded-pill" style="font-size:10px;">{{ ucfirst($att->status) }}</span></td>
                                    <td class="py-2 text-muted">{{ $att->clock_in ?? '—' }}</td>
                                    <td class="py-2 text-muted">{{ $att->clock_out ?? '—' }}</td>
                                    <td class="py-2 text-center text-dark fw-medium">{{ $att->hours_worked ? number_format($att->hours_worked, 1).'h' : '—' }}</td>
                                    <td class="pe-2 py-2 text-end">
                                        @if($att->is_late)
                                            <span class="badge bg-warning text-dark rounded-pill" style="font-size:10px;">+{{ $att->late_minutes }}m</span>
                                        @else —
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Section Padding --}}
    <div class="pb-5"></div>
</div>

{{-- Upload Document Modal --}}
<div class="modal fade" id="uploadDocModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:12px; overflow:hidden;">
            <div class="modal-header py-3 px-4 bg-white border-bottom">
                <div>
                    <h5 class="modal-title fw-bold mb-0 text-dark"><i class="fas fa-upload me-2 text-primary"></i>Upload Employee Document</h5>
                    <p class="mb-0 small text-muted">{{ $employee->full_name }} — {{ $employee->employee_code }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.hr.documents.store', $employee) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="letter-spacing:0.05rem;">Document Type</label>
                            <select name="document_type" class="form-select" required>
                                <option value="">— Select Type —</option>
                                @foreach($documentTypes as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="letter-spacing:0.05rem;">Document Title</label>
                            <input type="text" name="title" class="form-control" required
                                   placeholder="e.g. National ID Front, Employment Contract 2025">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="letter-spacing:0.05rem;">File</label>
                            <input type="file" name="file" class="form-control" required
                                   accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx">
                            <div class="form-text small">Max 10MB. PDF, images, Word, Excel accepted.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="letter-spacing:0.05rem;">Expiry Date <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="date" name="expiry_date" class="form-control">
                            <div class="form-text small">Leave blank if document does not expire.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="letter-spacing:0.05rem;">Notes <span class="text-muted fw-normal">(optional)</span></label>
                            <textarea name="notes" class="form-control" rows="2"
                                      placeholder="Any additional notes about this document..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold" data-no-global-handler>
                        <i class="fas fa-upload me-2"></i>Upload Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
