@extends('layouts.admin')

@section('content')
<div class="container-fluid py-2 pt-1">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <i class="fas fa-user-edit text-primary"></i> Edit Employee Record
            </h4>
            <p class="text-muted small mb-0">Modify master employee profile for <strong>{{ $employee->full_name }}</strong> ({{ $employee->employee_code }}).</p>
        </div>
        <a href="{{ route('admin.hr.show', $employee) }}" class="btn btn-outline-secondary btn-sm rounded-2 px-3 fw-semibold">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.hr.update', $employee) }}" method="POST" enctype="multipart/form-data" class="row g-4">
        @csrf
        @method('PUT')

        {{-- ── LEFT COLUMN: IDENTITY, STATUS & UPLOADED DOCUMENTS ── --}}
        <div class="col-12 col-lg-4">
            <!-- Profile Photo & Identity -->
            <div class="card border-0 shadow-sm mb-4 position-relative overflow-hidden hover-lift" style="border-radius:16px;">
                <div class="card-body p-4 text-center">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <p class="fw-bold x-small text-uppercase text-muted m-0" style="letter-spacing:.08em;">Identity & Avatar</p>
                        <span class="badge bg-secondary rounded-pill font-monospace" style="font-size:10px; padding: 4px 8px;">{{ $employee->employee_code }}</span>
                    </div>
                    
                    <!-- Avatar Preview Slot -->
                    <div class="position-relative d-inline-block mb-3">
                        <div class="avatar-preview-container rounded-circle shadow-sm border border-3 border-white overflow-hidden" 
                             style="width: 140px; height: 140px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                            <img id="avatar-preview" 
                                 src="{{ $employee->photo ? asset('storage/' . $employee->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($employee->full_name) . '&background=6366f1&color=fff&size=128' }}" 
                                 alt="Avatar Preview" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <label for="photo-upload" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 p-0 d-flex align-items-center justify-content-center shadow-sm" 
                               style="width:34px; height:34px; cursor: pointer; border: 2px solid #fff;" title="Upload Photo">
                            <i class="fas fa-camera" style="font-size: 12px;"></i>
                        </label>
                        <input type="file" name="photo" id="photo-upload" class="d-none @error('photo') is-invalid @enderror" accept="image/*" onchange="previewImage(this)">
                    </div>
                    
                    <div class="mb-3 text-start">
                        <label class="form-label small fw-bold">Full Name <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-user-circle"></i></span>
                            <input type="text" name="full_name" id="full_name" class="form-control border-start-0 @error('full_name') is-invalid @enderror" 
                                   value="{{ old('full_name', $employee->full_name) }}" required placeholder="Rachel Mwangi">
                        </div>
                        @error('full_name')
                            <div class="text-danger small mt-1" style="font-size:11px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 text-start">
                        <label class="form-label small fw-bold">National ID / NIDA</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-id-card"></i></span>
                            <input type="text" name="national_id" class="form-control border-start-0 @error('national_id') is-invalid @enderror" 
                                   value="{{ old('national_id', $employee->national_id) }}" placeholder="e.g. 199012345678...">
                        </div>
                        @error('national_id')
                            <div class="text-danger small mt-1" style="font-size:11px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 text-start">
                        <label class="form-label small fw-bold">Link to System User</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-link"></i></span>
                            <select name="user_id" id="user_select" class="form-select border-start-0 @error('user_id') is-invalid @enderror">
                                <option value="">-- Not Linked --</option>
                                @foreach($linkedUsers as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id', $employee->user_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('user_id')
                            <div class="text-danger small mt-1" style="font-size:11px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="text-start">
                        <label class="form-label small fw-bold">Employment Status <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-info-circle"></i></span>
                            <select name="status" class="form-select border-start-0 @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="terminated" {{ old('status', $employee->status) == 'terminated' ? 'selected' : '' }}>Terminated</option>
                                <option value="on_leave" {{ old('status', $employee->status) == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                            </select>
                        </div>
                        @error('status')
                            <div class="text-danger small mt-1" style="font-size:11px;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                        <!-- Employee Documents Explorer sidebar -->
            <div class="card border-0 shadow-sm mb-4" id="documents-section" style="border-radius:16px; border-left: 4px solid #6366f1;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <p class="fw-bold x-small text-uppercase text-muted m-0" style="letter-spacing:.08em;">
                            <i class="fas fa-folder-open text-primary me-1"></i> E-Files ({{ $employee->documents->count() }})
                        </p>
                        <button type="button" class="btn btn-xs btn-primary rounded-pill px-2.5 py-1 fw-semibold shadow-sm" style="font-size:10px;" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                            <i class="fas fa-upload me-1"></i> Upload
                        </button>
                    </div>
                    
                    @if($employee->documents->isEmpty())
                        <div class="text-center py-4 bg-light rounded-3 border border-dashed">
                            <i class="fas fa-folder-open text-muted opacity-50 mb-2" style="font-size: 24px;"></i>
                            <p class="text-muted small mb-0" style="font-size:11px;">No documents uploaded yet.</p>
                        </div>
                    @else
                        <div class="d-flex flex-column gap-2" style="max-height: 350px; overflow-y: auto;">
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
                                <div class="p-2 rounded-3 border bg-light bg-opacity-50 hover-lift">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="rounded-3 bg-primary bg-opacity-10 p-1.5 d-flex align-items-center justify-content-center" style="width:32px; height:32px; flex-shrink:0;">
                                            <i class="fas {{ $doc->icon }} text-primary" style="font-size: 14px;"></i>
                                        </div>
                                        <div class="overflow-hidden w-100">
                                            <div class="d-flex justify-content-between align-items-start gap-1">
                                                <h6 class="fw-bold text-dark text-truncate mb-0" style="font-size:11.5px; max-width:130px;" title="{{ $doc->title }}">{{ $doc->title }}</h6>
                                                
                                                <div class="d-flex gap-1" style="flex-shrink:0;">
                                                    <a href="{{ $doc->download_url }}" target="_blank" class="btn btn-xs btn-outline-primary rounded-circle p-0 d-flex align-items-center justify-content-center shadow-xs" style="width:20px; height:20px;" title="View File">
                                                        <i class="fas fa-download" style="font-size: 8px;"></i>
                                                    </a>
                                                    <form action="{{ route('admin.hr.documents.destroy', [$employee, $doc]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete document?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-outline-danger rounded-circle p-0 d-flex align-items-center justify-content-center shadow-xs" style="width:20px; height:20px;" title="Delete">
                                                            <i class="fas fa-trash" style="font-size: 8px;"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="text-muted x-small text-truncate mt-0.5" style="font-size:9.5px;">{{ $doc->file_name }} &bull; {{ $doc->file_size_human }}</div>
                                            
                                            <div class="mt-1 pt-1 border-top border-light d-flex flex-wrap justify-content-between gap-1" style="font-size:9px;">
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-1.5" style="font-size:8px; padding: 2px 4px;">
                                                    {{ $documentLabels[$doc->document_type] ?? 'Document' }}
                                                </span>
                                                @if($doc->expiry_date)
                                                    <span class="{{ $expiredClass }}">Exp: {{ $doc->expiry_date->format('d M Y') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>  </div>
        </div>

        {{-- ── RIGHT COLUMN: JOB DETAILS, CONTACTS, COMPENSATION & BANKING ── --}}
        <div class="col-12 col-lg-8">
            <!-- Job & Placement Information -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <p class="fw-bold x-small text-uppercase text-muted mb-3" style="letter-spacing:.08em;"><i class="fas fa-briefcase me-1 text-success"></i>Job & Placement Information</p>
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold d-block mb-2">Departments <span class="text-danger">*</span></label>
                            <div class="p-3 rounded-3 bg-light bg-opacity-50 border border-light">
                                <div class="d-flex flex-wrap gap-2 mb-3" id="departments-pills-container">
                                    @foreach($departments as $dept)
                                        @php
                                            $isChecked = false;
                                            if (old('departments')) {
                                                $isChecked = in_array($dept, old('departments'));
                                            } elseif ($employee->department) {
                                                $employeeDepts = array_map('trim', explode(',', $employee->department));
                                                $isChecked = in_array($dept, $employeeDepts);
                                            }
                                        @endphp
                                        <div class="dept-pill-item">
                                            <input class="form-check-input d-none dept-checkbox" type="checkbox" name="departments[]" 
                                                   id="dept-checkbox-{{ $loop->index }}" value="{{ $dept }}" {{ $isChecked ? 'checked' : '' }} onchange="togglePillIcon(this)">
                                            <label class="btn btn-sm btn-outline-secondary rounded-2 px-2.5 py-1 fw-semibold dept-pill-label shadow-sm bg-white" 
                                                   for="dept-checkbox-{{ $loop->index }}" style="font-size: 11.5px; cursor: pointer; transition: all 0.2s; border: 1px solid #cbd5e1;">
                                                <i class="fas {{ $isChecked ? 'fa-check-circle text-white' : 'fa-plus-circle text-muted' }} me-1 icon-state"></i> 
                                                <span class="dept-name">{{ $dept }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="input-group input-group-sm animate-fade-in" style="max-width: 320px;">
                                    <input type="text" id="new-dept-input" class="form-control border-end-0" placeholder="Type new department name...">
                                    <button class="btn btn-outline-primary fw-bold" type="button" id="add-dept-btn">
                                        <i class="fas fa-plus me-1"></i>Add
                                    </button>
                                </div>
                                <div class="text-danger small mt-1 d-none" id="dept-error-msg" style="font-size: 11px;"></div>
                                @error('departments')
                                    <div class="text-danger small mt-1" style="font-size:11px;">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Role Title</label>
                            <input type="text" name="role_title" class="form-control form-control-sm @error('role_title') is-invalid @enderror" 
                                   value="{{ old('role_title', $employee->role_title) }}" placeholder="e.g. Sales Executive">
                            @error('role_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Contract Type <span class="text-danger">*</span></label>
                            <select name="contract_type" class="form-select form-select-sm @error('contract_type') is-invalid @enderror" required>
                                <option value="permanent" {{ old('contract_type', $employee->contract_type) == 'permanent' ? 'selected' : '' }}>Permanent</option>
                                <option value="contract" {{ old('contract_type', $employee->contract_type) == 'contract' ? 'selected' : '' }}>Contract</option>
                                <option value="part_time" {{ old('contract_type', $employee->contract_type) == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                <option value="intern" {{ old('contract_type', $employee->contract_type) == 'intern' ? 'selected' : '' }}>Intern</option>
                            </select>
                            @error('contract_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Hire Date</label>
                            <input type="date" name="hire_date" class="form-control form-control-sm @error('hire_date') is-invalid @enderror" 
                                   value="{{ old('hire_date', $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '') }}">
                            @error('hire_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Contract End Date</label>
                            <input type="date" name="contract_end_date" class="form-control form-control-sm @error('contract_end_date') is-invalid @enderror" 
                                   value="{{ old('contract_end_date', $employee->contract_end_date ? $employee->contract_end_date->format('Y-m-d') : '') }}">
                            <small class="text-muted" style="font-size:10px;">Applicable for contract terms only.</small>
                            @error('contract_end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <p class="fw-bold x-small text-uppercase text-muted mb-3" style="letter-spacing:.08em;"><i class="fas fa-address-book me-1 text-primary"></i>Contact Information</p>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email Address</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $employee->email) }}" placeholder="e.g. employee@company.com">
                            </div>
                            @error('email')
                                <div class="text-danger small mt-1" style="font-size:11px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Phone Number</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted"><i class="fas fa-phone-alt"></i></span>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone', $employee->phone) }}" placeholder="e.g. +255 712 345 678">
                            </div>
                            @error('phone')
                                <div class="text-danger small mt-1" style="font-size:11px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Residential Address</label>
                            <textarea name="address" class="form-control form-control-sm @error('address') is-invalid @enderror" 
                                      rows="2" placeholder="Describe main residential area...">{{ old('address', $employee->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compensation & Bank Details -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <p class="fw-bold x-small text-uppercase text-muted mb-3" style="letter-spacing:.08em;"><i class="fas fa-money-bill-wave me-1 text-warning"></i>Compensation & Bank Details</p>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Basic Salary</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted">TZS</span>
                                <input type="number" name="basic_salary" class="form-control @error('basic_salary') is-invalid @enderror" 
                                       value="{{ old('basic_salary', $employee->basic_salary) }}" min="0" step="0.01">
                            </div>
                            @error('basic_salary')
                                <div class="text-danger small mt-1" style="font-size:11px;">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Monthly Allowances</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted">TZS</span>
                                <input type="number" name="allowances" class="form-control @error('allowances') is-invalid @enderror" 
                                       value="{{ old('allowances', $employee->allowances) }}" min="0" step="0.01">
                            </div>
                            @error('allowances')
                                <div class="text-danger small mt-1" style="font-size:11px;">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Deductions</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted">TZS</span>
                                <input type="number" name="deductions" class="form-control @error('deductions') is-invalid @enderror" 
                                       value="{{ old('deductions', $employee->deductions) }}" min="0" step="0.01">
                            </div>
                            @error('deductions')
                                <div class="text-danger small mt-1" style="font-size:11px;">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control form-control-sm @error('bank_name') is-invalid @enderror" 
                                   value="{{ old('bank_name', $employee->bank_name) }}" placeholder="e.g. CRDB Bank, NMB Bank">
                            @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Bank Account Number</label>
                            <input type="text" name="bank_account" class="form-control form-control-sm @error('bank_account') is-invalid @enderror" 
                                   value="{{ old('bank_account', $employee->bank_account) }}" placeholder="e.g. 0152438923000">
                            @error('bank_account')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <p class="fw-bold x-small text-uppercase text-muted mb-3" style="letter-spacing:.08em;"><i class="fas fa-heartbeat me-1 text-danger"></i>Emergency Contact Information</p>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Emergency Contact Name</label>
                            <input type="text" name="emergency_contact_name" class="form-control form-control-sm @error('emergency_contact_name') is-invalid @enderror" 
                                   value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}" placeholder="e.g. Father, Spouse, Kin Fullname">
                            @error('emergency_contact_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Emergency Contact Phone</label>
                            <input type="text" name="emergency_contact_phone" class="form-control form-control-sm @error('emergency_contact_phone') is-invalid @enderror" 
                                   value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}" placeholder="e.g. +255 655 123 456">
                            @error('emergency_contact_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <p class="fw-bold x-small text-uppercase text-muted mb-3" style="letter-spacing:.08em;"><i class="fas fa-sticky-note me-1 text-secondary"></i>Other Notes</p>
                    <textarea name="notes" class="form-control form-control-sm @error('notes') is-invalid @enderror" rows="2" placeholder="Any additional placement notes...">{{ old('notes', $employee->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Form Action Footer -->
            <div class="d-flex align-items-center justify-content-end mb-5 gap-2">
                <a href="{{ route('admin.hr.show', $employee) }}" class="btn btn-light rounded-2 px-4 fw-semibold border">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-2 px-5 fw-bold">
                    <i class="fas fa-save me-1"></i> Update Employee Record
                </button>
            </div>
        </div>
    </form>
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
                <input type="hidden" name="redirect_to_edit" value="1">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="letter-spacing:0.05rem;">Document Type</label>
                            <select name="document_type" class="form-select" required>
                                <option value="">— Select Type —</option>
                                <option value="national_id">National ID</option>
                                <option value="passport">Passport</option>
                                <option value="contract">Contract</option>
                                <option value="certificate">Certificate</option>
                                <option value="insurance">Insurance</option>
                                <option value="bank_letter">Bank Letter</option>
                                <option value="nssf">NSSF Card</option>
                                <option value="nhif">NHIF Card</option>
                                <option value="other">Other</option>
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

@push('scripts')
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatar-preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize multiple departments selector
        const addDeptBtn = document.getElementById('add-dept-btn');
        const newDeptInput = document.getElementById('new-dept-input');
        const container = document.getElementById('departments-pills-container');
        const errorMsg = document.getElementById('dept-error-msg');

        if (addDeptBtn && newDeptInput && container) {
            addDeptBtn.addEventListener('click', function() {
                let name = newDeptInput.value.trim();
                errorMsg.classList.add('d-none');
                
                if (!name) {
                    errorMsg.textContent = "Please enter a department name.";
                    errorMsg.classList.remove('d-none');
                    return;
                }
                
                // Check for duplicates
                let exists = false;
                container.querySelectorAll('.dept-name').forEach(el => {
                    if (el.textContent.trim().toLowerCase() === name.toLowerCase()) {
                        exists = true;
                    }
                });
                
                if (exists) {
                    errorMsg.textContent = "This department is already in the list.";
                    errorMsg.classList.remove('d-none');
                    return;
                }
                
                let index = container.children.length;
                let pillHtml = `
                    <div class="dept-pill-item">
                        <input class="form-check-input d-none dept-checkbox" type="checkbox" name="departments[]" 
                               id="dept-checkbox-${index}" value="${name}" checked onchange="togglePillIcon(this)">
                        <label class="btn btn-sm btn-outline-secondary rounded-2 px-2.5 py-1 fw-semibold dept-pill-label shadow-sm bg-white" 
                               for="dept-checkbox-${index}" style="font-size: 11.5px; cursor: pointer; transition: all 0.2s; border: 1px solid #cbd5e1;">
                            <i class="fas fa-check-circle text-white me-1 icon-state"></i> 
                            <span class="dept-name">${name}</span>
                        </label>
                    </div>
                `;
                
                container.insertAdjacentHTML('beforeend', pillHtml);
                newDeptInput.value = '';
                
                let checkbox = document.getElementById(`dept-checkbox-${index}`);
                updatePillStyle(checkbox);
            });
            
            newDeptInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addDeptBtn.click();
                }
            });
        }

        // Initialize active pill styles on load
        document.querySelectorAll('.dept-checkbox').forEach(checkbox => {
            updatePillStyle(checkbox);
        });
    });

    function togglePillIcon(checkbox) {
        updatePillStyle(checkbox);
    }

    function updatePillStyle(checkbox) {
        let label = checkbox.nextElementSibling;
        let icon = label.querySelector('.icon-state');
        
        if (checkbox.checked) {
            label.classList.remove('btn-outline-secondary', 'bg-white');
            label.classList.add('btn-primary');
            label.style.borderColor = "#6366f1";
            label.style.backgroundColor = "#6366f1";
            label.style.color = "#fff";
            icon.classList.remove('fa-plus-circle', 'text-muted');
            icon.classList.add('fa-check-circle', 'text-white');
        } else {
            label.classList.remove('btn-primary');
            label.classList.add('btn-outline-secondary', 'bg-white');
            label.style.borderColor = "#cbd5e1";
            label.style.backgroundColor = "#fff";
            label.style.color = "";
            icon.classList.remove('fa-check-circle', 'text-white');
            icon.classList.add('fa-plus-circle', 'text-muted');
        }
    }
</script>
@endpush
@endsection
