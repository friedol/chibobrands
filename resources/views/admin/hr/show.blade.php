@extends('layouts.admin')
@section('title', 'Employee Profile — ' . $employee->full_name)

@section('content')
<div class="container-fluid py-3">

    {{-- ── Header ── --}}
    <div class="row mb-3 align-items-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center gap-3">
                @if($employee->photo)
                    <img src="{{ Storage::url($employee->photo) }}" class="rounded-circle shadow" width="64" height="64" style="object-fit:cover">
                @else
                    <div class="rounded-circle bg-primary bg-opacity-15 d-flex align-items-center justify-content-center shadow" style="width:64px;height:64px">
                        <span class="fw-bold text-primary fs-3">{{ strtoupper(substr($employee->full_name, 0, 1)) }}</span>
                    </div>
                @endif
                <div>
                    <h4 class="fw-bold mb-0">{{ $employee->full_name }}</h4>
                    <div class="text-muted small">
                        <code>{{ $employee->employee_code }}</code> &bull;
                        {{ $employee->role_title ?? 'No Role' }} &bull;
                        {{ $employee->department ?? 'No Department' }}
                    </div>
                    @php $statusColors = ['active'=>'success','inactive'=>'secondary','terminated'=>'danger','on_leave'=>'warning']; @endphp
                    <span class="badge bg-{{ $statusColors[$employee->status] ?? 'secondary' }} mt-1">
                        {{ ucfirst(str_replace('_',' ',$employee->status)) }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 text-lg-end mt-2 mt-lg-0">
            <a href="{{ route('admin.hr.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fas fa-arrow-left me-1"></i>Back to HR
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- ── Left Column: Profile ── --}}
        <div class="col-lg-4">
            {{-- Personal Info --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom py-2 fw-bold small text-uppercase">
                    <i class="fas fa-id-card text-primary me-2"></i>Personal Information
                </div>
                <div class="card-body small">
                    @foreach([
                        'Phone' => $employee->phone,
                        'Email' => $employee->email,
                        'National ID' => $employee->national_id,
                        'Address' => $employee->address,
                    ] as $lbl => $val)
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">{{ $lbl }}</span>
                        <span class="fw-semibold text-end">{{ $val ?? '—' }}</span>
                    </div>
                    @endforeach
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Emergency Contact</span>
                        <span class="fw-semibold text-end">
                            {{ $employee->emergency_contact_name ?? '—' }}
                            @if($employee->emergency_contact_phone)
                                <br><small>{{ $employee->emergency_contact_phone }}</small>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            {{-- Employment Info --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom py-2 fw-bold small text-uppercase">
                    <i class="fas fa-briefcase text-info me-2"></i>Employment
                </div>
                <div class="card-body small">
                    @foreach([
                        'Contract Type' => ucfirst(str_replace('_',' ',$employee->contract_type)),
                        'Hire Date' => $employee->hire_date?->format('d M Y') ?? '—',
                        'Contract End' => $employee->contract_end_date?->format('d M Y') ?? 'N/A',
                        'Years of Service' => $employee->years_of_service . ' yrs',
                    ] as $lbl => $val)
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">{{ $lbl }}</span>
                        <span class="fw-semibold">{{ $val }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Salary Info --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom py-2 fw-bold small text-uppercase">
                    <i class="fas fa-money-bill-wave text-success me-2"></i>Salary
                </div>
                <div class="card-body small">
                    @foreach([
                        'Basic Salary' => number_format($employee->basic_salary),
                        'Allowances' => number_format($employee->allowances),
                        'Deductions' => number_format($employee->deductions),
                    ] as $lbl => $val)
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">{{ $lbl }}</span>
                        <span>{{ $val }}</span>
                    </div>
                    @endforeach
                    <div class="d-flex justify-content-between py-1 fw-bold">
                        <span>Net Salary (TZS)</span>
                        <span class="text-success fs-6">{{ number_format($employee->net_salary) }}</span>
                    </div>
                </div>
            </div>

            {{-- Leave Balances --}}
            @if($leaveBalances->isNotEmpty())
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom py-2 fw-bold small text-uppercase">
                    <i class="fas fa-calendar-minus text-warning me-2"></i>Leave Balances ({{ now()->year }})
                </div>
                <div class="card-body small p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Type</th><th class="text-center">Total</th><th class="text-center">Used</th><th class="text-center">Left</th></tr></thead>
                        <tbody>
                            @foreach($leaveBalances as $bal)
                            <tr>
                                <td>{{ ucfirst($bal->leave_type) }}</td>
                                <td class="text-center">{{ $bal->total_days }}</td>
                                <td class="text-center text-danger">{{ $bal->used_days }}</td>
                                <td class="text-center text-success fw-bold">{{ $bal->remaining_days }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        {{-- ── Right Column ── --}}
        <div class="col-lg-8">
            {{-- This Month Attendance Summary --}}
            <div class="row g-3 mb-3">
                @foreach([
                    ['Present','success','fas fa-user-check',$presentDays],
                    ['Absent','danger','fas fa-user-times',$absentDays],
                    ['Late Days','warning','fas fa-clock',$lateDays],
                    ['Hours Worked','info','fas fa-hourglass',$totalWorked],
                ] as [$label, $color, $icon, $value])
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center py-3">
                            <div class="rounded-circle bg-{{ $color }} bg-opacity-10 p-2 me-2">
                                <i class="{{ $icon }} text-{{ $color }} small"></i>
                            </div>
                            <div>
                                <div class="fs-5 fw-bold">{{ is_float($value) ? number_format($value, 1) : $value }}</div>
                                <div class="x-small text-muted">{{ $label }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Latest KPI --}}
            @if($latestKpi)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom py-2 fw-bold small text-uppercase d-flex justify-content-between">
                    <span><i class="fas fa-star text-warning me-2"></i>Latest KPI Evaluation</span>
                    <span class="badge bg-{{ $latestKpi->grade_color }} fs-6 px-3">{{ $latestKpi->grade }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-2 text-center mb-3">
                        @foreach(['attendance_score'=>'Attendance','productivity_score'=>'Productivity','quality_score'=>'Quality','punctuality_score'=>'Punctuality','teamwork_score'=>'Teamwork'] as $field => $lbl)
                        <div class="col">
                            <div class="fw-bold fs-5 {{ $latestKpi->$field >= 80 ? 'text-success' : ($latestKpi->$field >= 60 ? 'text-warning' : 'text-danger') }}">
                                {{ number_format($latestKpi->$field, 1) }}
                            </div>
                            <div class="x-small text-muted">{{ $lbl }}</div>
                        </div>
                        @endforeach
                    </div>
                    <div class="progress mb-2" style="height:12px">
                        <div class="progress-bar bg-{{ $latestKpi->grade_color }}" style="width:{{ $latestKpi->overall_score }}%">
                            {{ number_format($latestKpi->overall_score, 1) }}%
                        </div>
                    </div>
                    @if($latestKpi->comments)
                        <p class="text-muted small mb-0 mt-2">{{ $latestKpi->comments }}</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Recent Attendance Records --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-2 fw-bold small text-uppercase">
                    <i class="fas fa-list text-info me-2"></i>Recent Attendance (Last 30 records)
                </div>
                <div class="card-body p-0">
                    @if($employee->attendances->isEmpty())
                        <p class="text-muted text-center py-4 small">No attendance records yet.</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Hours</th>
                                    <th>Late</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employee->attendances as $att)
                                <tr>
                                    <td>{{ $att->attendance_date->format('d M Y') }}</td>
                                    <td><span class="badge bg-{{ $att->status_badge }}">{{ ucfirst($att->status) }}</span></td>
                                    <td>{{ $att->clock_in ?? '—' }}</td>
                                    <td>{{ $att->clock_out ?? '—' }}</td>
                                    <td>{{ $att->hours_worked ? number_format($att->hours_worked, 1).'h' : '—' }}</td>
                                    <td>
                                        @if($att->is_late)
                                            <span class="badge bg-warning text-dark">+{{ $att->late_minutes }}m</span>
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

    {{-- ── Documents Section ─────────────────────────────────── --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius:15px; overflow:hidden;">
                <div class="card-header border-0 py-2 px-4 d-flex justify-content-between align-items-center"
                     style="background:linear-gradient(135deg,#0d6efd,#0a58ca);">
                    <h6 class="mb-0 fw-bold text-white"><i class="fas fa-folder-open me-2"></i>Employee Documents ({{ $employee->documents->count() }})</h6>
                    <button class="btn btn-light btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                        <i class="fas fa-upload me-1"></i>Upload Document
                    </button>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show m-3 mb-0 border-0 shadow-sm">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card-body p-0">
                    @if($employee->documents->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open text-muted mb-3" style="font-size:3rem; opacity:0.2;"></i>
                            <p class="text-muted small">No documents uploaded yet. Click <strong>Upload Document</strong> to add one.</p>
                        </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="bg-light text-muted text-uppercase" style="font-size:0.7rem; letter-spacing:0.05rem;">
                                <tr>
                                    <th class="ps-4 py-3">Document</th>
                                    <th class="py-3">Type</th>
                                    <th class="py-3">Size</th>
                                    <th class="py-3">Expiry</th>
                                    <th class="py-3">Uploaded By</th>
                                    <th class="py-3">Date</th>
                                    <th class="text-end pe-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employee->documents as $doc)
                                @php
                                    $expiredClass = $doc->is_expired ? 'text-danger' : ($doc->is_expiring_soon ? 'text-warning' : 'text-dark');
                                @endphp
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas {{ $doc->icon }} fs-5"></i>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $doc->title }}</div>
                                                <div class="text-muted" style="font-size:0.7rem;">{{ $doc->file_name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2">
                                            {{ \App\Models\EmployeeDocument::typeLabel($doc->document_type) }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-muted">{{ $doc->file_size_human }}</td>
                                    <td class="py-3">
                                        @if($doc->expiry_date)
                                            <span class="{{ $expiredClass }} fw-bold">{{ $doc->expiry_date->format('d M Y') }}</span>
                                            @if($doc->is_expired)
                                                <span class="badge bg-danger ms-1 small">Expired</span>
                                            @elseif($doc->is_expiring_soon)
                                                <span class="badge bg-warning text-dark ms-1 small">Expiring Soon</span>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-muted">{{ $doc->uploader?->name ?? '—' }}</td>
                                    <td class="py-3 text-muted">{{ $doc->created_at->format('d M Y') }}</td>
                                    <td class="text-end pe-4 py-3">
                                        <a href="{{ $doc->download_url }}" target="_blank"
                                           class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1">
                                            <i class="fas fa-download me-1"></i>View
                                        </a>
                                        <form action="{{ route('admin.hr.documents.destroy', [$employee, $doc]) }}"
                                              method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                    onclick="modernConfirm('Delete this document?', () => this.closest('form').submit())">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @if($doc->notes)
                                <tr class="bg-light">
                                    <td colspan="7" class="ps-5 py-1 text-muted small fst-italic">
                                        <i class="fas fa-sticky-note me-1"></i>{{ $doc->notes }}
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Upload Document Modal --}}
<div class="modal fade" id="uploadDocModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px; overflow:hidden;">
            <div class="modal-header border-0 text-white py-3 px-4"
                 style="background:linear-gradient(135deg,#0d6efd,#0a58ca);">
                <div>
                    <h5 class="modal-title fw-bold mb-0 text-white"><i class="fas fa-upload me-2"></i>Upload Employee Document</h5>
                    <p class="mb-0 small text-white-50">{{ $employee->full_name }} — {{ $employee->employee_code }}</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
