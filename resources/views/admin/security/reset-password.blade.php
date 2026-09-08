@extends('layouts.admin')

@section('title', 'Reset Password')

@push('styles')
<style>
.rp-wrap { width: 100%; }
.rp-type-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
.rp-type-card { position: relative; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 12px 14px; cursor: pointer; transition: all 0.15s ease; background: #fff; display: flex; align-items: center; gap: 10px; }
.rp-type-card input { position: absolute; opacity: 0; pointer-events: none; }
.rp-type-card i.type-icon { font-size: 1rem; color: #9ca3af; width: 18px; text-align: center; }
.rp-type-card span { font-size: 0.82rem; font-weight: 600; color: #374151; }
.rp-type-card:hover { border-color: #d1d5db; background: #f9fafb; }
.rp-type-card.active { border-color: #dc3545; background: rgba(220,53,69,0.03); }
.rp-type-card.active i.type-icon { color: #dc3545; }
.rp-type-card.active span { color: #dc3545; }
.rp-check { margin-left: auto; width: 16px; height: 16px; border-radius: 50%; border: 1.5px solid #d1d5db; display: flex; align-items: center; justify-content: center; font-size: 0.55rem; color: transparent; transition: all 0.15s; flex-shrink: 0; }
.rp-type-card.active .rp-check { background: #dc3545; border-color: #dc3545; color: #fff; }
.rp-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; overflow: visible; margin-bottom: 14px; }
.rp-card-header { padding: 11px 16px; border-bottom: 1px solid #f3f4f6; background: #fafafa; display: flex; align-items: center; gap: 8px; }
.rp-card-header .hicon { font-size: 0.75rem; color: #dc3545; }
.rp-card-header .hlabel { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; }
.rp-card-body { padding: 16px; }
.rp-selected { display: none; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px 12px; margin-top: 10px; align-items: center; gap: 10px; }
.rp-selected.show { display: flex; }
.rp-avatar { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #dc3545, #c0392b); color: #fff; font-size: 0.78rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.rp-selected-name { font-size: 0.8rem; font-weight: 600; color: #111827; line-height: 1.2; }
.rp-selected-sub  { font-size: 0.7rem; color: #6b7280; }
.strength-bar-wrap { height: 3px; background: #e5e7eb; border-radius: 2px; margin-top: 5px; }
.strength-bar { height: 100%; width: 0; border-radius: 2px; transition: all 0.3s; }
.strength-bar.weak   { width: 33%; background: #ef4444; }
.strength-bar.medium { width: 66%; background: #f59e0b; }
.strength-bar.strong { width: 100%; background: #22c55e; }
.rp-search-results { position: absolute; z-index: 999; width: 100%; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); margin-top: 3px; max-height: 240px; overflow-y: auto; display: none; }
.rp-search-item { padding: 9px 12px; border-bottom: 1px solid #f3f4f6; cursor: pointer; display: flex; align-items: center; gap: 9px; }
.rp-search-item:last-child { border-bottom: none; }
.rp-search-item:hover { background: #f9fafb; }
.ri-name { font-size: 0.8rem; font-weight: 600; color: #111827; }
.ri-sub  { font-size: 0.7rem; color: #6b7280; }
/* Custom user dropdown */
.ud-wrap { position: relative; }
.ud-input { cursor: pointer; background: #fff !important; caret-color: transparent; }
.ud-input.searching { caret-color: auto; cursor: text; }
.ud-dropdown { position: absolute; z-index: 9999; width: 100%; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); margin-top: 3px; display: none; }
.ud-dropdown.open { display: block; }
.ud-search-wrap { padding: 8px; border-bottom: 1px solid #f3f4f6; }
.ud-search { width: 100%; border: 1px solid #e5e7eb; border-radius: 6px; padding: 5px 10px; font-size: 0.78rem; outline: none; }
.ud-search:focus { border-color: #dc3545; }
.ud-list { max-height: 200px; overflow-y: auto; }
.ud-option { padding: 8px 12px; font-size: 0.82rem; color: #374151; cursor: pointer; border-bottom: 1px solid #f9fafb; }
.ud-option:last-child { border-bottom: none; }
.ud-option:hover, .ud-option.focused { background: #f9fafb; color: #dc3545; }
.ud-option.selected { background: rgba(220,53,69,0.06); color: #dc3545; font-weight: 600; }
.ud-empty { padding: 10px 12px; font-size: 0.78rem; color: #9ca3af; text-align: center; }
.form-label { font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 4px; }
.form-control, .form-select { font-size: 0.82rem; border-color: #e5e7eb; border-radius: 7px; padding: 0.46rem 0.7rem; }
.form-control:focus, .form-select:focus { border-color: #dc3545; box-shadow: 0 0 0 0.15rem rgba(220,53,69,0.12); }
.form-text { font-size: 0.7rem; color: #9ca3af; margin-top: 3px; }
.input-group-text { font-size: 0.75rem; border-color: #e5e7eb; background: #f9fafb; color: #6b7280; }
.input-group .btn-outline-secondary { border-color: #e5e7eb; color: #6b7280; font-size: 0.75rem; }
.btn-rp-submit { background: linear-gradient(135deg, #dc3545, #c0392b); color: #fff; border: none; border-radius: 7px; padding: 8px 22px; font-size: 0.82rem; font-weight: 600; transition: all 0.2s; }
.btn-rp-submit:hover { background: linear-gradient(135deg, #c0392b, #a93226); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(220,53,69,0.25); color: #fff; }
.btn-rp-cancel { border-radius: 7px; padding: 8px 16px; font-size: 0.82rem; }
.rp-info-banner { background: #fffbeb; border: 1px solid #fde68a; border-left: 3px solid #f59e0b; border-radius: 8px; padding: 9px 13px; font-size: 0.76rem; color: #92400e; margin-bottom: 14px; display: flex; gap: 7px; align-items: flex-start; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3">
<div class="rp-wrap">

    <div class="mb-3">
        <h6 class="mb-0 fw-bold text-dark">Reset User Password</h6>
        <small class="text-muted">Reset password for admin staff or customers</small>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" style="font-size:0.8rem;" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3" style="font-size:0.8rem;" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="rp-info-banner">
        <i class="fas fa-shield-alt mt-1 flex-shrink-0"></i>
        <span>Password resets are recorded in the audit trail. The user must log in with the new password immediately after reset.</span>
    </div>

    <form method="POST" action="{{ route('admin.security.reset-password.store') }}" id="resetPasswordForm">
        @csrf

        <!-- Account Type -->
        <div class="rp-card">
            <div class="rp-card-header"><i class="fas fa-users hicon"></i><span class="hlabel">Account Type</span></div>
            <div class="rp-card-body">
                <div class="rp-type-row">
                    <label class="rp-type-card active" for="user_type_user">
                        <input type="radio" name="user_type" id="user_type_user" value="user" checked>
                        <i class="fas fa-user-tie type-icon"></i>
                        <span>Admin / Staff</span>
                        <div class="rp-check"><i class="fas fa-check"></i></div>
                    </label>
                    <label class="rp-type-card" for="user_type_customer">
                        <input type="radio" name="user_type" id="user_type_customer" value="customer">
                        <i class="fas fa-user-tag type-icon"></i>
                        <span>Customer</span>
                        <div class="rp-check"><i class="fas fa-check"></i></div>
                    </label>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Left: User Selection -->
            <div class="col-lg-6">
                <!-- Staff -->
                <div class="rp-card" id="userSelectionSection">
                    <div class="rp-card-header"><i class="fas fa-user-check hicon"></i><span class="hlabel">Select User</span></div>
                    <div class="rp-card-body">
                        <label class="form-label">Staff / Admin <span class="text-danger">*</span></label>
                        <input type="hidden" name="user_id" id="user_id">
                        <div class="ud-wrap">
                            <input type="text" class="form-control ud-input @error('user_id') is-invalid @enderror"
                                   id="udTrigger" placeholder="— Choose a user —"
                                   autocomplete="off" readonly>
                            <div class="ud-dropdown" id="udDropdown">
                                <div class="ud-search-wrap">
                                    <input type="text" class="ud-search" id="udSearch" placeholder="Search by name…">
                                </div>
                                <div class="ud-list" id="udList">
                                    @foreach($users as $user)
                                        <div class="ud-option" data-id="{{ $user->id }}" data-name="{{ $user->name }}" data-email="{{ $user->email }}">{{ $user->name }}</div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @error('user_id')<div class="text-danger" style="font-size:0.72rem;margin-top:3px;">{{ $message }}</div>@enderror
                        <div class="form-text">Select the staff member whose password needs resetting</div>
                        <div class="rp-selected" id="selectedUserDisplay">
                            <div class="rp-avatar" id="userAvatar">U</div>
                            <div>
                                <div class="rp-selected-name" id="userName">—</div>
                                <div class="rp-selected-sub"  id="userEmail">—</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer search -->
                <div class="rp-card d-none" id="customerSelectionSection">
                    <div class="rp-card-header"><i class="fas fa-search hicon"></i><span class="hlabel">Find Customer</span></div>
                    <div class="rp-card-body">
                        <label class="form-label">Search Customer <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="text" class="form-control" id="customer_search" placeholder="Name, email or phone…" autocomplete="off">
                            <input type="hidden" name="user_id" id="customer_id">
                            <div class="rp-search-results" id="customer_results"></div>
                        </div>
                        <div class="form-text">Type at least 2 characters</div>
                        <div class="rp-selected" id="selectedCustomerDisplay">
                            <div class="rp-avatar" id="customerAvatar">C</div>
                            <div>
                                <div class="rp-selected-name" id="customerName">—</div>
                                <div class="rp-selected-sub"  id="customerEmail">—</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Password -->
            <div class="col-lg-6">
                <div class="rp-card">
                    <div class="rp-card-header"><i class="fas fa-lock hicon"></i><span class="hlabel">New Password</span></div>
                    <div class="rp-card-body">
                        <div class="mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       id="password" name="password" required minlength="8" placeholder="New password">
                                <button class="btn btn-outline-secondary px-2" type="button" id="togglePassword">
                                    <i class="fas fa-eye" style="font-size:0.72rem;"></i>
                                </button>
                            </div>
                            <div class="strength-bar-wrap"><div class="strength-bar" id="strengthBar"></div></div>
                            <div class="form-text" id="strengthText">Minimum 8 characters</div>
                            @error('password')<div style="font-size:0.72rem;color:#dc3545;">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                <input type="password" class="form-control"
                                       id="password_confirmation" name="password_confirmation"
                                       required minlength="8" placeholder="Re-enter password">
                                <button class="btn btn-outline-secondary px-2" type="button" id="togglePasswordConf">
                                    <i class="fas fa-eye" style="font-size:0.72rem;"></i>
                                </button>
                            </div>
                            <div id="matchMsg" class="form-text" style="display:none;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="d-flex justify-content-end gap-2 mt-1">
            <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary btn-rp-cancel">Cancel</a>
            <button type="submit" class="btn btn-rp-submit" id="submitBtn">
                <i class="fas fa-key me-2"></i>Reset Password
            </button>
        </div>
    </form>

</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Type cards
    document.querySelectorAll('.rp-type-card').forEach(card => {
        card.addEventListener('click', function () {
            document.querySelectorAll('.rp-type-card').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            const val = this.querySelector('input').value;
            if (val === 'user') {
                document.getElementById('userSelectionSection').classList.remove('d-none');
                document.getElementById('customerSelectionSection').classList.add('d-none');
                document.getElementById('user_id').required = true;
                document.getElementById('customer_id').removeAttribute('required');
            } else {
                document.getElementById('userSelectionSection').classList.add('d-none');
                document.getElementById('customerSelectionSection').classList.remove('d-none');
                document.getElementById('user_id').required = false;
                document.getElementById('customer_id').required = true;
            }
        });
    });

    // ── Custom user dropdown with search ──
    const udTrigger  = document.getElementById('udTrigger');
    const udDropdown = document.getElementById('udDropdown');
    const udSearch   = document.getElementById('udSearch');
    const udList     = document.getElementById('udList');
    const userIdHidden = document.getElementById('user_id');
    const allOptions = Array.from(udList.querySelectorAll('.ud-option'));

    udTrigger.addEventListener('click', function () {
        udDropdown.classList.toggle('open');
        if (udDropdown.classList.contains('open')) {
            udSearch.value = '';
            renderOptions('');
            udSearch.focus();
        }
    });

    udSearch.addEventListener('input', function () {
        renderOptions(this.value.trim().toLowerCase());
    });

    function renderOptions(q) {
        udList.innerHTML = '';
        const filtered = q ? allOptions.filter(o => o.dataset.name.toLowerCase().includes(q)) : allOptions;
        if (!filtered.length) {
            udList.innerHTML = '<div class="ud-empty">No users found</div>';
            return;
        }
        filtered.forEach(opt => {
            const el = opt.cloneNode(true);
            if (el.dataset.id === userIdHidden.value) el.classList.add('selected');
            el.addEventListener('click', function () {
                userIdHidden.value  = this.dataset.id;
                udTrigger.value     = this.dataset.name;
                document.getElementById('userName').textContent  = this.dataset.name;
                document.getElementById('userEmail').textContent = this.dataset.email || '';
                document.getElementById('userAvatar').textContent = this.dataset.name.charAt(0).toUpperCase();
                document.getElementById('selectedUserDisplay').classList.add('show');
                udDropdown.classList.remove('open');
            });
            udList.appendChild(el);
        });
    }

    document.addEventListener('click', function (e) {
        if (!udTrigger.contains(e.target) && !udDropdown.contains(e.target))
            udDropdown.classList.remove('open');
    });

    // Customer search
    let timer;
    const csearch = document.getElementById('customer_search');
    const cid     = document.getElementById('customer_id');
    const cres    = document.getElementById('customer_results');
    csearch.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length < 2) { cres.style.display = 'none'; return; }
        timer = setTimeout(() => {
            fetch(`{{ route('admin.security.search-users') }}?q=${encodeURIComponent(q)}`)
                .then(r => r.json()).then(data => {
                    cres.innerHTML = '';
                    const list = data.customers || [];
                    if (!list.length) {
                        cres.innerHTML = '<div class="rp-search-item" style="font-size:0.78rem;color:#9ca3af;">No customers found</div>';
                    } else {
                        list.forEach(c => {
                            const el = document.createElement('div');
                            el.className = 'rp-search-item';
                            el.innerHTML = `<div class="rp-avatar" style="width:26px;height:26px;font-size:0.68rem;">${c.name.charAt(0).toUpperCase()}</div><div><div class="ri-name">${c.name}</div><div class="ri-sub">${c.email||c.phone||''}</div></div>`;
                            el.addEventListener('click', () => {
                                cid.value = c.id;
                                csearch.value = `${c.name} (${c.email||c.phone||''})`;
                                cres.style.display = 'none';
                                document.getElementById('customerName').textContent   = c.name;
                                document.getElementById('customerEmail').textContent  = c.email||c.phone||'';
                                document.getElementById('customerAvatar').textContent = c.name.charAt(0).toUpperCase();
                                document.getElementById('selectedCustomerDisplay').classList.add('show');
                            });
                            cres.appendChild(el);
                        });
                    }
                    cres.style.display = 'block';
                });
        }, 280);
    });
    document.addEventListener('click', e => { if (!csearch.contains(e.target) && !cres.contains(e.target)) cres.style.display = 'none'; });

    // Toggle password visibility
    function makeToggle(btnId, inputId) {
        document.getElementById(btnId).addEventListener('click', function () {
            const inp = document.getElementById(inputId);
            inp.type = inp.type === 'password' ? 'text' : 'password';
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }
    makeToggle('togglePassword', 'password');
    makeToggle('togglePasswordConf', 'password_confirmation');

    // Strength
    const pwInput = document.getElementById('password');
    const bar = document.getElementById('strengthBar');
    const stText = document.getElementById('strengthText');
    pwInput.addEventListener('input', function () {
        const p = this.value; let s = 0;
        if (p.length>=8) s++; if (p.length>=12) s++;
        if (/[a-z]/.test(p)) s++; if (/[A-Z]/.test(p)) s++;
        if (/[0-9]/.test(p)) s++; if (/[^A-Za-z0-9]/.test(p)) s++;
        bar.className = 'strength-bar';
        if (!p) { stText.textContent = 'Minimum 8 characters'; stText.style.color = ''; return; }
        if (s<=2)      { bar.classList.add('weak');   stText.textContent='Weak';   stText.style.color='#ef4444'; }
        else if (s<=4) { bar.classList.add('medium'); stText.textContent='Medium'; stText.style.color='#f59e0b'; }
        else           { bar.classList.add('strong'); stText.textContent='Strong'; stText.style.color='#22c55e'; }
        checkMatch();
    });

    // Match
    const pwConf = document.getElementById('password_confirmation');
    const matchEl = document.getElementById('matchMsg');
    function checkMatch() {
        if (!pwConf.value) { matchEl.style.display='none'; return; }
        matchEl.style.display = 'block';
        if (pwInput.value === pwConf.value)
            matchEl.innerHTML = '<i class="fas fa-check-circle me-1" style="color:#22c55e"></i><span style="color:#22c55e">Passwords match</span>';
        else
            matchEl.innerHTML = '<i class="fas fa-times-circle me-1" style="color:#ef4444"></i><span style="color:#ef4444">Passwords do not match</span>';
    }
    pwConf.addEventListener('input', checkMatch);

    // Submit guard
    document.getElementById('resetPasswordForm').addEventListener('submit', function (e) {
        const type = document.querySelector('input[name="user_type"]:checked').value;
        if (type==='user' && !userIdHidden.value) { e.preventDefault(); alert('Please select a user.'); return; }
        if (type==='customer' && !cid.value) { e.preventDefault(); alert('Please select a customer.'); return; }
        if (pwInput.value !== pwConf.value) { e.preventDefault(); alert('Passwords do not match.'); return; }
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Resetting…';
    });
});
</script>
@endpush
