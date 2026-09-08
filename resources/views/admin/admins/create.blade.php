@extends('layouts.admin')

@section('page-title', 'New Team Member')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.admins.index') }}" class="text-decoration-none">Team Members</a></li>
                    <li class="breadcrumb-item active">New Member</li>
                </ol>
            </nav>

            <div class="card border-0 shadow-sm">
                <div class="card-header py-3 bg-white border-bottom">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-user-plus me-2 text-danger"></i>Create New Team Member
                    </h5>
                </div>
                <div class="card-body p-4">

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.admins.store') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="Enter full name" required autofocus>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" placeholder="Enter email address" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="" disabled selected>Select a role</option>
                                    @foreach($roles as $key => $roleName)
                                        @if(auth()->user()->role === 'accountant' && in_array($key, ['super_admin', 'admin', 'manager', 'accountant']))
                                            @continue
                                        @endif
                                        <option value="{{ $key }}" {{ old('role') == $key ? 'selected' : '' }}>
                                            {{ $roleName }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold text-muted">+</span>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                        value="{{ old('phone', '255') }}" placeholder="255123456789">
                                </div>
                                <div class="form-text text-muted">Start with country code (e.g. 255)</div>
                                @error('phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Department(s)</label>
                                <div class="border rounded p-2 bg-white" style="max-height: 120px; overflow-y: auto;">
                                    @foreach($departments as $dept)
                                        <div class="form-check mb-1">
                                            <input class="form-check-input" type="checkbox" name="department_ids[]"
                                                value="{{ $dept->id }}" id="dept_{{ $dept->id }}"
                                                {{ is_array(old('department_ids')) && in_array($dept->id, old('department_ids')) ? 'checked' : '' }}>
                                            <label class="form-check-label small fw-medium" for="dept_{{ $dept->id }}">
                                                {{ $dept->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="form-text text-muted">Select all branches that apply.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Monthly Salary (TZS)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold text-muted">TZS</span>
                                    <input type="number" name="monthly_salary" step="0.01"
                                        class="form-control @error('monthly_salary') is-invalid @enderror"
                                        value="{{ old('monthly_salary', '0') }}" placeholder="0.00">
                                </div>
                                @error('monthly_salary')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch p-3 bg-light rounded border">
                                    <input class="form-check-input ms-0 me-3" type="checkbox" role="switch"
                                        id="is_active" name="is_active" value="1"
                                        {{ old('is_active', true) ? 'checked' : '' }}
                                        style="width: 2.5em; height: 1.25em;">
                                    <label class="form-check-label pt-1" for="is_active">
                                        <div class="fw-bold text-dark">Active Account</div>
                                        <div class="text-muted small">User can log in to the system immediately</div>
                                    </label>
                                </div>
                            </div>

                            <div class="col-12"><hr class="my-1"></div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        minlength="8" placeholder="••••••••" required>
                                    <button class="btn btn-outline-secondary toggle-pw" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-text">Minimum 8 characters</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control" placeholder="••••••••" required>
                                    <button class="btn btn-outline-secondary toggle-pw" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info small mt-4 mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            After creating the user, an email with login instructions will be sent to their email address.
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <a href="{{ route('admin.admins.index') }}" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-arrow-left me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-5 fw-bold">
                                <i class="fas fa-user-plus me-2"></i>Create Member
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-primary {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border: none;
        box-shadow: 0 2px 8px rgba(220,53,69,0.25);
        transition: all 0.2s ease;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(220,53,69,0.35);
    }
    .card { border-radius: 12px; overflow: hidden; }
    .form-control:focus, .form-select:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220,53,69,0.15);
    }
    .form-check-input:checked { background-color: #dc3545; border-color: #dc3545; }
</style>

<script>
document.querySelectorAll('.toggle-pw').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.previousElementSibling;
        const icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
});
</script>
@endsection
