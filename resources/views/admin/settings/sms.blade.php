@extends('layouts.admin')

@section('page-title', 'SMS Configuration')

@section('content')
<div class="container-fluid px-3">

    <!-- Layout: Sidebar + Content -->
    <div class="settings-layout d-flex gap-3" style="min-height: 70vh;">

        <!-- Left Sidebar -->
        <div class="settings-sidebar flex-shrink-0">
            <div class="settings-sidebar-inner">
                <div class="px-2 py-2">
                    <a href="{{ route('admin.settings') }}?tab=general" class="settings-nav-item">
                        <i class="fas fa-sliders-h"></i>
                        <span>General</span>
                    </a>
                    <a href="{{ route('admin.settings') }}?tab=company" class="settings-nav-item">
                        <i class="fas fa-building"></i>
                        <span>Company Info</span>
                    </a>
                    <a href="{{ route('admin.settings') }}?tab=notifications" class="settings-nav-item">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
                    </a>
                    <a href="{{ route('admin.settings.sms') }}" class="settings-nav-item active">
                        <i class="fas fa-sms"></i>
                        <span>SMS</span>
                    </a>
                    <a href="{{ route('admin.settings') }}?tab=system" class="settings-nav-item">
                        <i class="fas fa-server"></i>
                        <span>System</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- ── Right Content Panel ── -->
        <div class="settings-content flex-grow-1">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert" style="font-size:0.82rem;">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert" style="font-size:0.82rem;">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- API Credentials -->
            <div class="sms-card mb-3">
                <div class="sms-card-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="panel-icon-wrap bg-primary-soft">
                            <i class="fas fa-key text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">API Credentials</h6>
                            <small class="text-muted">Beem Africa SMS gateway authentication</small>
                        </div>
                    </div>
                </div>
                <div class="sms-card-body">
                    <form method="POST" action="{{ route('admin.settings.sms.update') }}" id="smsSettingsForm">
                        @csrf

                        <div class="settings-section">
                            <div class="settings-section-title">Authentication</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">API Key <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('api_key') is-invalid @enderror"
                                           id="api_key" name="api_key"
                                           value="{{ old('api_key', $settings['api_key']) }}"
                                           placeholder="Enter Beem Africa API Key" required>
                                    @error('api_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">API Secret Key <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password"
                                               class="form-control @error('api_secret') is-invalid @enderror"
                                               id="api_secret" name="api_secret"
                                               value="{{ old('api_secret', $settings['api_secret']) }}"
                                               placeholder="Enter API Secret Key" required>
                                        <button class="btn btn-outline-secondary" type="button" id="toggleSecret">
                                            <i class="fas fa-eye" id="toggleSecretIcon"></i>
                                        </button>
                                    </div>
                                    @error('api_secret')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="settings-section">
                            <div class="settings-section-title">Endpoint & Sender</div>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">API URL <span class="text-danger">*</span></label>
                                    <input type="url"
                                           class="form-control @error('api_url') is-invalid @enderror"
                                           id="api_url" name="api_url"
                                           value="{{ old('api_url', $settings['api_url']) }}"
                                           placeholder="https://apisms.beem.africa/v1/send" required>
                                    @error('api_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Sender ID <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('sender_id') is-invalid @enderror"
                                           id="sender_id" name="sender_id"
                                           value="{{ old('sender_id', $settings['sender_id']) }}"
                                           maxlength="50" placeholder="CHIBOBRAND" required>
                                    @error('sender_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end pt-2">
                            <button type="submit" class="btn btn-save" id="saveSettingsBtn" data-no-global-handler>
                                <i class="fas fa-save me-2"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Test & Diagnose -->
            <div class="sms-card">
                <div class="sms-card-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="panel-icon-wrap bg-info-soft">
                            <i class="fas fa-paper-plane text-info"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Test & Diagnose</h6>
                            <small class="text-muted">Verify your SMS configuration is working</small>
                        </div>
                    </div>
                </div>
                <div class="sms-card-body">

                    <div class="settings-section">
                        <div class="settings-section-title">Send Test SMS</div>
                        <form id="testSmsForm">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="test_phone" name="test_phone"
                                           placeholder="255622367322" required>
                                    <div class="form-text">Include country code, no spaces</div>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label">Message</label>
                                    <textarea class="form-control" id="test_message" name="test_message"
                                              rows="2" maxlength="160">Test SMS from CHIBO BRAND SMS Configuration</textarea>
                                    <div class="d-flex justify-content-end">
                                        <small class="text-muted"><span id="charCount">0</span>/160</small>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="test_api_key" name="api_key">
                            <input type="hidden" id="test_api_secret" name="api_secret">
                            <input type="hidden" id="test_sender_id" name="sender_id">

                            <div id="testSmsAlert" class="mt-3"></div>

                            <div class="d-flex justify-content-end mt-3">
                                <button type="button" class="btn btn-save" id="sendTestSmsBtn">
                                    <i class="fas fa-paper-plane me-2"></i>Send Test
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="settings-section">
                        <div class="settings-section-title">Tools</div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-action-outline btn-outline-warning" id="diagnoseSmsBtn">
                                <i class="fas fa-bug me-2"></i>Run Diagnostics
                            </button>
                            <button type="button" class="btn btn-action-outline btn-outline-secondary" id="clearConfigCacheBtn">
                                <i class="fas fa-redo me-2"></i>Clear Config Cache
                            </button>
                        </div>

                        <div id="diagnosticInfo" class="mt-3" style="display:none;">
                            <div class="alert alert-info mb-0" style="font-size:0.82rem;">
                                <h6 class="alert-heading fw-bold" style="font-size:0.82rem;"><i class="fas fa-info-circle me-2"></i>Diagnostic Results</h6>
                                <pre id="diagnosticContent" style="font-size:0.72rem; max-height:260px; overflow-y:auto; white-space:pre-wrap; word-wrap:break-word; margin:0;"></pre>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div><!-- /.settings-content -->
    </div><!-- /.settings-layout -->
</div>

<style>
/* Layout */
.settings-layout { align-items: flex-start; }
.settings-sidebar { width: 220px; position: sticky; top: 70px; }
.settings-sidebar-inner { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }
.settings-content { min-width: 0; flex: 1; }

/* Nav items */
.settings-nav-item { display: flex; align-items: center; gap: 9px; padding: 7px 10px; border-radius: 8px; font-size: 0.82rem; font-weight: 500; color: #495057; text-decoration: none; transition: all 0.18s ease; margin-bottom: 2px; }
.settings-nav-item i { font-size: 0.78rem; width: 16px; text-align: center; color: #868e96; flex-shrink: 0; }
.settings-nav-item:hover { background: #f8f9fa; color: #dc3545; }
.settings-nav-item:hover i { color: #dc3545; }
.settings-nav-item.active { background: rgba(220,53,69,0.08); color: #dc3545; font-weight: 600; }
.settings-nav-item.active i { color: #dc3545; }

/* Cards */
.sms-card { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }
.sms-card-header { padding: 14px 20px; border-bottom: 1px solid #f1f3f5; background: #fafafa; }
.sms-card-body { padding: 20px; }

/* Icon wrappers */
.panel-icon-wrap { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; flex-shrink: 0; }
.bg-primary-soft { background: rgba(13,110,253,0.1); }
.bg-info-soft    { background: rgba(13,202,240,0.1); }

/* Sections */
.settings-section { margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid #f1f3f5; }
.settings-section:last-of-type { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.settings-section-title { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #adb5bd; margin-bottom: 14px; }

/* Forms */
.form-label { font-size: 0.8rem; font-weight: 600; color: #495057; margin-bottom: 5px; }
.form-control, .form-select { font-size: 0.82rem; border-radius: 8px; border-color: #e9ecef; padding: 0.5rem 0.75rem; }
.form-control:focus, .form-select:focus { border-color: #dc3545; box-shadow: 0 0 0 0.18rem rgba(220,53,69,0.12); }
.form-text { font-size: 0.72rem; color: #adb5bd; }

/* Buttons */
.btn-save { background: linear-gradient(135deg, #dc3545 0%, #c0392b 100%); color: #fff; border: none; border-radius: 8px; padding: 8px 20px; font-size: 0.82rem; font-weight: 600; transition: all 0.2s ease; }
.btn-save:hover { background: linear-gradient(135deg, #c0392b 0%, #a93226 100%); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(220,53,69,0.25); color: #fff; }
.btn-save:disabled { opacity: 0.7; transform: none; }
.btn-action-outline { font-size: 0.8rem; border-radius: 8px; padding: 7px 14px; font-weight: 600; }

@media (max-width: 768px) {
    .settings-layout { flex-direction: column; }
    .settings-sidebar { width: 100%; position: static; }
    .settings-sidebar-inner { display: flex; overflow-x: auto; }
    .settings-nav-item { padding: 6px 10px; margin-bottom: 0; white-space: nowrap; }
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // The SMS page sidebar items are all real navigation links — no JS tab switching needed here.
    // They navigate directly to their respective pages.

    // ── Save form loading state ──
    const smsSettingsForm = document.getElementById('smsSettingsForm');
    const saveSettingsBtn = document.getElementById('saveSettingsBtn');
    if (smsSettingsForm && saveSettingsBtn) {
        let isSubmitting = false;
        const originalHtml = saveSettingsBtn.innerHTML;
        smsSettingsForm.addEventListener('submit', function(e) {
            if (isSubmitting) { e.preventDefault(); return; }
            if (!smsSettingsForm.checkValidity()) { e.preventDefault(); smsSettingsForm.reportValidity(); return; }
            isSubmitting = true;
            saveSettingsBtn.disabled = true;
            saveSettingsBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
            setTimeout(() => { isSubmitting = false; saveSettingsBtn.disabled = false; saveSettingsBtn.innerHTML = originalHtml; }, 10000);
        });
    }

    // ── Toggle secret visibility ──
    const toggleSecret = document.getElementById('toggleSecret');
    if (toggleSecret) {
        toggleSecret.addEventListener('click', function() {
            const input = document.getElementById('api_secret');
            const icon = document.getElementById('toggleSecretIcon');
            input.type = input.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }

    // ── Char counter ──
    const testMessage = document.getElementById('test_message');
    const charCount = document.getElementById('charCount');
    if (testMessage && charCount) {
        charCount.textContent = testMessage.value.length;
        testMessage.addEventListener('input', function() { charCount.textContent = this.value.length; });
    }

    // ── Diagnose ──
    const diagnoseSmsBtn = document.getElementById('diagnoseSmsBtn');
    const diagnosticInfo = document.getElementById('diagnosticInfo');
    const diagnosticContent = document.getElementById('diagnosticContent');
    if (diagnoseSmsBtn) {
        diagnoseSmsBtn.addEventListener('click', function() {
            const btn = this;
            const orig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Running...';
            fetch('{{ route("admin.settings.sms.diagnose") }}', {
                method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, credentials: 'same-origin'
            })
            .then(r => r.json())
            .then(data => { diagnosticContent.textContent = JSON.stringify(data, null, 2); diagnosticInfo.style.display = 'block'; diagnosticInfo.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); })
            .catch(err => { diagnosticContent.textContent = 'Error: ' + err.message; diagnosticInfo.style.display = 'block'; })
            .finally(() => { btn.disabled = false; btn.innerHTML = orig; });
        });
    }

    // ── Clear config cache ──
    const clearConfigCacheBtn = document.getElementById('clearConfigCacheBtn');
    if (clearConfigCacheBtn) {
        clearConfigCacheBtn.addEventListener('click', function() {
            if (!confirm('This will clear the Laravel configuration cache. Continue?')) return;
            const btn = this;
            const orig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Clearing...';
            fetch('{{ route("admin.settings.sms.update") }}', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                credentials: 'same-origin',
                body: JSON.stringify({ _method: 'POST', clear_cache: true })
            })
            .then(r => r.json())
            .then(() => alert('Config cache cleared!'))
            .catch(() => alert('Error clearing cache.'))
            .finally(() => { btn.disabled = false; btn.innerHTML = orig; });
        });
    }

    // ── Send test SMS ──
    const sendTestSmsBtn = document.getElementById('sendTestSmsBtn');
    const testSmsForm = document.getElementById('testSmsForm');
    const testSmsAlert = document.getElementById('testSmsAlert');
    if (sendTestSmsBtn) {
        sendTestSmsBtn.addEventListener('click', function() {
            // Populate hidden fields from credential panel
            const apiKey    = document.getElementById('api_key')?.value.trim() || '';
            const apiSecret = document.getElementById('api_secret')?.value.trim() || '';
            const senderId  = document.getElementById('sender_id')?.value.trim() || '';
            if (!apiKey || !apiSecret || !senderId) {
                testSmsAlert.innerHTML = '<div class="alert alert-warning" style="font-size:0.82rem;"><i class="fas fa-exclamation-triangle me-2"></i>Please fill in API credentials first (API Credentials tab).</div>';
                return;
            }
            document.getElementById('test_api_key').value    = apiKey;
            document.getElementById('test_api_secret').value = apiSecret;
            document.getElementById('test_sender_id').value  = senderId;

            if (!testSmsForm.checkValidity()) { testSmsForm.reportValidity(); return; }

            const btn = this;
            const orig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

            fetch('{{ route("admin.settings.sms.test") }}', {
                method: 'POST', body: new FormData(testSmsForm), headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(r => r.json())
            .then(data => {
                testSmsAlert.innerHTML = data.success
                    ? `<div class="alert alert-success" style="font-size:0.82rem;"><i class="fas fa-check-circle me-2"></i><strong>Success!</strong> ${data.message}</div>`
                    : `<div class="alert alert-danger" style="font-size:0.82rem;"><i class="fas fa-exclamation-circle me-2"></i><strong>Error:</strong> ${data.message}</div>`;
                if (data.success) document.getElementById('test_phone').value = '';
            })
            .catch(err => {
                testSmsAlert.innerHTML = `<div class="alert alert-danger" style="font-size:0.82rem;"><i class="fas fa-exclamation-circle me-2"></i><strong>Error:</strong> ${err.message}</div>`;
            })
            .finally(() => { btn.disabled = false; btn.innerHTML = orig; });
        });
    }
});
</script>
@endpush
@endsection
