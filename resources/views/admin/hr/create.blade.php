@extends('layouts.admin')

@section('content')
<div class="container-fluid py-2 pt-1">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <i class="fas fa-user-plus text-primary"></i> Add New Employee
            </h4>
        </div>
        <a href="{{ route('admin.hr.index') }}" class="btn btn-outline-secondary btn-sm rounded-2 px-3 fw-semibold">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>`
    </div>

    <!-- Form -->
    <form action="{{ route('admin.hr.store') }}" method="POST" enctype="multipart/form-data" class="row g-4">
        @csrf

        {{-- ── LEFT COLUMN: IDENTITY & DOCUMENTS ── --}}
        <div class="col-12 col-lg-4">
            <!-- Profile Photo & Identity -->
            <div class="card border-0 shadow-sm mb-4 position-relative overflow-hidden hover-lift" style="border-radius:16px;">
                <div class="card-body p-4 text-center">
                    <p class="fw-bold x-small text-uppercase text-muted text-start mb-3" style="letter-spacing:.08em;">Identity & Avatar</p>
                    
                    <!-- Avatar Preview Slot -->
                    <div class="position-relative d-inline-block mb-3">
                        <div class="avatar-preview-container rounded-circle shadow-sm border border-3 border-white overflow-hidden" 
                             style="width: 140px; height: 140px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                            <img id="avatar-preview" src="{{ asset('images/default-avatar.png') }}" 
                                 onerror="this.src='https://ui-avatars.com/api/?name=New+Employee&background=6366f1&color=fff&size=128'"
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
                                   value="{{ old('full_name') }}" required placeholder="e.g. Rachel Mwangi">
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
                                   value="{{ old('national_id') }}" placeholder="e.g. 199012345678...">
                        </div>
                        @error('national_id')
                            <div class="text-danger small mt-1" style="font-size:11px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="text-start">
                        <label class="form-label small fw-bold">Link to System User</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-link"></i></span>
                            <select name="user_id" id="user_select" class="form-select border-start-0 @error('user_id') is-invalid @enderror">
                                <option value="">-- Not Linked --</option>
                                @foreach($linkedUsers as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-muted mt-1 d-block" style="font-size: 10px;">Links profile to a system login account.</small>
                        @error('user_id')
                            <div class="text-danger small mt-1" style="font-size:11px;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Necessary Documents Checklist Place -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px; border-left: 4px solid #6366f1;">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3 small d-flex align-items-center gap-2" style="letter-spacing:.05em;">
                        <i class="fas fa-folder-open text-primary"></i> Necessary Employee Documents
                    </h6>
                    <p class="text-muted small mb-3" style="font-size:11px; line-height:1.4;">
                        Once the employee record is created, you will be able to upload and manage the following necessary official documents in the left sidebar:
                    </p>
                    
                    <div class="d-flex flex-column gap-2 mb-3 bg-light p-3 rounded-3" style="font-size:11px;">
                        <div class="d-flex align-items-center gap-2 text-muted">
                            <i class="far fa-square"></i> <span>National ID / NIDA Card</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 text-muted">
                            <i class="far fa-square"></i> <span>Signed Contract Agreement</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 text-muted">
                            <i class="far fa-square"></i> <span>Academic Certificates</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 text-muted">
                            <i class="far fa-square"></i> <span>Bank Account Letter</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 text-muted">
                            <i class="far fa-square"></i> <span>NSSF & NHIF Cards</span>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center gap-2 text-primary fw-bold small" style="font-size: 11px;">
                        <i class="fas fa-info-circle"></i> Upload panel will activate instantly!
                    </div>
                </div>
            </div>
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
                                   value="{{ old('role_title') }}" placeholder="e.g. Sales Executive">
                            @error('role_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Contract Type <span class="text-danger">*</span></label>
                            <select name="contract_type" class="form-select form-select-sm @error('contract_type') is-invalid @enderror" required>
                                <option value="permanent" {{ old('contract_type') == 'permanent' ? 'selected' : '' }}>Permanent</option>
                                <option value="contract" {{ old('contract_type') == 'contract' ? 'selected' : '' }}>Contract</option>
                                <option value="part_time" {{ old('contract_type') == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                <option value="intern" {{ old('contract_type') == 'intern' ? 'selected' : '' }}>Intern</option>
                            </select>
                            @error('contract_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Hire Date</label>
                            <input type="date" name="hire_date" class="form-control form-control-sm @error('hire_date') is-invalid @enderror" value="{{ old('hire_date') }}">
                            @error('hire_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Contract End Date</label>
                            <input type="date" name="contract_end_date" class="form-control form-control-sm @error('contract_end_date') is-invalid @enderror" value="{{ old('contract_end_date') }}">
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
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="e.g. employee@company.com">
                            </div>
                            @error('email')
                                <div class="text-danger small mt-1" style="font-size:11px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Phone Number</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted"><i class="fas fa-phone-alt"></i></span>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="e.g. +255 712 345 678">
                            </div>
                            @error('phone')
                                <div class="text-danger small mt-1" style="font-size:11px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Residential Address</label>
                            <textarea name="address" class="form-control form-control-sm @error('address') is-invalid @enderror" rows="2" placeholder="Describe main residential area...">{{ old('address') }}</textarea>
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
                                <input type="number" name="basic_salary" class="form-control @error('basic_salary') is-invalid @enderror" value="{{ old('basic_salary', 0) }}" min="0" step="0.01">
                            </div>
                            @error('basic_salary')
                                <div class="text-danger small mt-1" style="font-size:11px;">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Monthly Allowances</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted">TZS</span>
                                <input type="number" name="allowances" class="form-control @error('allowances') is-invalid @enderror" value="{{ old('allowances', 0) }}" min="0" step="0.01">
                            </div>
                            @error('allowances')
                                <div class="text-danger small mt-1" style="font-size:11px;">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Deductions</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted">TZS</span>
                                <input type="number" name="deductions" class="form-control @error('deductions') is-invalid @enderror" value="{{ old('deductions', 0) }}" min="0" step="0.01">
                            </div>
                            @error('deductions')
                                <div class="text-danger small mt-1" style="font-size:11px;">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control form-control-sm @error('bank_name') is-invalid @enderror" 
                                   value="{{ old('bank_name') }}" placeholder="e.g. CRDB Bank, NMB Bank">
                            @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Bank Account Number</label>
                            <input type="text" name="bank_account" class="form-control form-control-sm @error('bank_account') is-invalid @enderror" 
                                   value="{{ old('bank_account') }}" placeholder="e.g. 0152438923000">
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
                                   value="{{ old('emergency_contact_name') }}" placeholder="e.g. Father, Spouse, Kin Fullname">
                            @error('emergency_contact_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Emergency Contact Phone</label>
                            <input type="text" name="emergency_contact_phone" class="form-control form-control-sm @error('emergency_contact_phone') is-invalid @enderror" 
                                   value="{{ old('emergency_contact_phone') }}" placeholder="e.g. +255 655 123 456">
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
                    <textarea name="notes" class="form-control form-control-sm @error('notes') is-invalid @enderror" rows="2" placeholder="Any additional placement notes...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Form Action Footer -->
            <div class="d-flex align-items-center justify-content-end mb-5 gap-2">
                <a href="{{ route('admin.hr.index') }}" class="btn btn-light rounded-2 px-4 fw-semibold border">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-2 px-5 fw-bold">
                    <i class="fas fa-save me-1"></i> Save Employee Record
                </button>
            </div>
        </div>
    </form>
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
        const users = @json($linkedUsers);
        const userSelect = document.getElementById('user_select');
        const fullNameInput = document.getElementById('full_name');
        const emailInput = document.getElementById('email');
        const phoneInput = document.getElementById('phone');

        userSelect.addEventListener('change', function() {
            const userId = this.value;
            if (userId) {
                const user = users.find(u => u.id == userId);
                if (user) {
                    if(!fullNameInput.value) fullNameInput.value = user.name || '';
                    if(!emailInput.value) emailInput.value = user.email || '';
                    if(!phoneInput.value) phoneInput.value = user.phone || '';
                }
            }
        });

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
