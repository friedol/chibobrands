@extends('layouts.admin')

@section('page-title', 'SMS Configuration')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h4 class="mb-1" style="font-size: 1.25rem;">SMS Configuration</h4>
                    <p class="text-muted mb-0" style="font-size: 0.875rem;">Configure Beem Africa SMS API settings</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom py-2 bg-primary text-white" style="font-size: 0.9375rem;">
                    <h6 class="mb-0" style="font-size: 1rem; font-weight: 600;">
                        <i class="fas fa-sms me-2"></i>Beem Africa SMS API Settings
                    </h6>
                </div>
                <div class="card-body p-3">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.settings.sms.update') }}" id="smsSettingsForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="api_key" class="form-label fw-semibold" style="font-size: 0.875rem;">
                                API Key <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('api_key') is-invalid @enderror" 
                                   id="api_key" 
                                   name="api_key" 
                                   value="{{ old('api_key', $settings['api_key']) }}" 
                                   required
                                   placeholder="Enter Beem Africa API Key"
                                   style="font-size: 0.875rem;">
                            @error('api_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="api_secret" class="form-label fw-semibold" style="font-size: 0.875rem;">
                                API Secret Key <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('api_secret') is-invalid @enderror" 
                                       id="api_secret" 
                                       name="api_secret" 
                                       value="{{ old('api_secret', $settings['api_secret']) }}" 
                                       required
                                       placeholder="Enter API Secret Key"
                                       style="font-size: 0.875rem;">
                                <button class="btn btn-outline-secondary" type="button" id="toggleSecret">
                                    <i class="fas fa-eye" id="toggleSecretIcon"></i>
                                </button>
                            </div>
                            @error('api_secret')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="api_url" class="form-label fw-semibold" style="font-size: 0.875rem;">
                                API URL <span class="text-danger">*</span>
                            </label>
                            <input type="url" 
                                   class="form-control @error('api_url') is-invalid @enderror" 
                                   id="api_url" 
                                   name="api_url" 
                                   value="{{ old('api_url', $settings['api_url']) }}" 
                                   required
                                   placeholder="https://apisms.beem.africa/v1/send"
                                   style="font-size: 0.875rem;">
                            @error('api_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="sender_id" class="form-label fw-semibold" style="font-size: 0.875rem;">
                                Sender ID <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('sender_id') is-invalid @enderror" 
                                   id="sender_id" 
                                   name="sender_id" 
                                   value="{{ old('sender_id', $settings['sender_id']) }}" 
                                   required
                                   maxlength="50"
                                   placeholder="CHIBOBRAND or INFO"
                                   style="font-size: 0.875rem;">
                            @error('sender_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2 mt-3 flex-wrap">
                            <button type="submit" class="btn btn-primary btn-sm" id="saveSettingsBtn" data-no-global-handler style="font-size: 0.875rem;">
                                <i class="fas fa-save me-2"></i>Save Settings
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm" id="testSmsBtn" style="font-size: 0.875rem;">
                                <i class="fas fa-paper-plane me-2"></i>Test SMS
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm" id="diagnoseSmsBtn" style="font-size: 0.875rem;">
                                <i class="fas fa-bug me-2"></i>Diagnose
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="clearConfigCacheBtn" style="font-size: 0.875rem;">
                                <i class="fas fa-redo me-2"></i>Clear Config Cache
                            </button>
                        </div>
                        
                        <!-- Diagnostic Info -->
                        <div id="diagnosticInfo" class="mt-3" style="display: none;">
                            <div class="alert alert-info" style="font-size: 0.875rem;">
                                <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Diagnostic Information</h6>
                                <pre id="diagnosticContent" style="font-size: 0.75rem; max-height: 300px; overflow-y: auto; white-space: pre-wrap; word-wrap: break-word;"></pre>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Test SMS Modal -->
            <div class="modal fade" id="testSmsModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white py-2" style="font-size: 0.9375rem;">
                            <h6 class="modal-title mb-0" style="font-size: 1rem; font-weight: 600;">
                                <i class="fas fa-paper-plane me-2"></i>Test SMS Configuration
                            </h6>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div id="testSmsAlert"></div>
                            <form id="testSmsForm">
                                @csrf
                                <div class="mb-3">
                                    <label for="test_phone" class="form-label fw-semibold" style="font-size: 0.875rem;">
                                        Phone Number <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="test_phone" 
                                           name="test_phone" 
                                           required
                                           placeholder="255622367322"
                                           style="font-size: 0.875rem;">
                                </div>
                                <div class="mb-3">
                                    <label for="test_message" class="form-label fw-semibold" style="font-size: 0.875rem;">
                                        Test Message
                                    </label>
                                    <textarea class="form-control" 
                                              id="test_message" 
                                              name="test_message" 
                                              rows="3"
                                              maxlength="160"
                                              placeholder="Test SMS from CHIBO BRAND SMS Configuration"
                                              style="font-size: 0.875rem;">Test SMS from CHIBO BRAND SMS Configuration</textarea>
                                    <small class="form-text text-muted d-flex justify-content-end" style="font-size: 0.75rem;"><span id="charCount">0</span>/160</small>
                                </div>
                                <input type="hidden" id="test_api_key" name="api_key" required>
                                <input type="hidden" id="test_api_secret" name="api_secret" required>
                                <input type="hidden" id="test_sender_id" name="sender_id" required>
                            </form>
                        </div>
                        <div class="modal-footer py-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="font-size: 0.875rem;">Cancel</button>
                            <button type="button" class="btn btn-primary btn-sm" id="sendTestSmsBtn" style="font-size: 0.875rem;">
                                <i class="fas fa-paper-plane me-2"></i>Send Test SMS
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle SMS Settings Form submission with loading state
    const smsSettingsForm = document.getElementById('smsSettingsForm');
    const saveSettingsBtn = document.getElementById('saveSettingsBtn');
    
    if (smsSettingsForm && saveSettingsBtn) {
        let isFormSubmitting = false;
        const originalSaveText = saveSettingsBtn.innerHTML;
        
        smsSettingsForm.addEventListener('submit', function(e) {
            // Prevent duplicate submissions
            if (isFormSubmitting) {
                e.preventDefault();
                return false;
            }
            
            // Check form validity
            if (!smsSettingsForm.checkValidity()) {
                e.preventDefault();
                smsSettingsForm.reportValidity();
                return false;
            }
            
            // Set submitting state
            isFormSubmitting = true;
            saveSettingsBtn.disabled = true;
            saveSettingsBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            
            // Auto-recovery after 10 seconds
            setTimeout(() => {
                if (isFormSubmitting) {
                    isFormSubmitting = false;
                    saveSettingsBtn.disabled = false;
                    saveSettingsBtn.innerHTML = originalSaveText;
                }
            }, 10000);
        });
    }
    
    // Toggle secret key visibility
    const toggleSecret = document.getElementById('toggleSecret');
    const apiSecret = document.getElementById('api_secret');
    const toggleSecretIcon = document.getElementById('toggleSecretIcon');
    
    if (toggleSecret) {
        toggleSecret.addEventListener('click', function() {
            const type = apiSecret.getAttribute('type') === 'password' ? 'text' : 'password';
            apiSecret.setAttribute('type', type);
            toggleSecretIcon.classList.toggle('fa-eye');
            toggleSecretIcon.classList.toggle('fa-eye-slash');
        });
    }
    
    // Character counter for test message
    const testMessage = document.getElementById('test_message');
    const charCount = document.getElementById('charCount');
    
    if (testMessage && charCount) {
        charCount.textContent = testMessage.value.length;
        testMessage.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }
    
    // Diagnose SMS button
    const diagnoseSmsBtn = document.getElementById('diagnoseSmsBtn');
    const diagnosticInfo = document.getElementById('diagnosticInfo');
    const diagnosticContent = document.getElementById('diagnosticContent');
    
    if (diagnoseSmsBtn) {
        diagnoseSmsBtn.addEventListener('click', function() {
            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
            
            fetch('{{ route("admin.settings.sms.diagnose") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            })
            .then(response => response.json())
            .then(data => {
                diagnosticContent.textContent = JSON.stringify(data, null, 2);
                diagnosticInfo.style.display = 'block';
                diagnosticInfo.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            })
            .catch(error => {
                console.error('Error:', error);
                diagnosticContent.textContent = 'Error loading diagnostic information: ' + error.message;
                diagnosticInfo.style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
        });
    }
    
    // Clear config cache button
    const clearConfigCacheBtn = document.getElementById('clearConfigCacheBtn');
    
    if (clearConfigCacheBtn) {
        clearConfigCacheBtn.addEventListener('click', function() {
            if (!confirm('This will clear the Laravel configuration cache. Continue?')) {
                return;
            }
            
            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Clearing...';
            
            fetch('{{ route("admin.settings.sms.update") }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    _method: 'POST',
                    clear_cache: true,
                }),
            })
            .then(response => response.json())
            .then(data => {
                alert('Config cache cleared successfully! Please test SMS again.');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error clearing cache. You may need to run: php artisan config:clear manually via SSH.');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
        });
    }

    // Test SMS button
    const testSmsBtn = document.getElementById('testSmsBtn');
    const testSmsModal = new bootstrap.Modal(document.getElementById('testSmsModal'));
    
    if (testSmsBtn) {
        testSmsBtn.addEventListener('click', function() {
            // Copy current form values to test form (from UI, not .env)
            const apiKey = document.getElementById('api_key').value.trim();
            const apiSecret = document.getElementById('api_secret').value.trim();
            const senderId = document.getElementById('sender_id').value.trim();
            
            // Validate that credentials are provided in the form
            if (!apiKey || !apiSecret || !senderId) {
                alert('Please fill in all SMS settings (API Key, API Secret, and Sender ID) in the form above before testing.');
                return;
            }
            
            // Copy values to test form
            document.getElementById('test_api_key').value = apiKey;
            document.getElementById('test_api_secret').value = apiSecret;
            document.getElementById('test_sender_id').value = senderId;
            document.getElementById('testSmsAlert').innerHTML = '';
            document.getElementById('test_phone').value = '';
            document.getElementById('test_message').value = 'Test SMS from CHIBO BRAND SMS Configuration';
            charCount.textContent = document.getElementById('test_message').value.length;
            testSmsModal.show();
        });
    }
    
    // Send test SMS
    const sendTestSmsBtn = document.getElementById('sendTestSmsBtn');
    const testSmsForm = document.getElementById('testSmsForm');
    const testSmsAlert = document.getElementById('testSmsAlert');
    
    if (sendTestSmsBtn) {
        sendTestSmsBtn.addEventListener('click', function() {
            const btn = this;
            const originalText = btn.innerHTML;
            
            // Validate form
            if (!testSmsForm.checkValidity()) {
                testSmsForm.reportValidity();
                return;
            }
            
            // Disable button and show loading
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
            
            // Get form data
            const formData = new FormData(testSmsForm);
            
            // Send AJAX request
            fetch('{{ route("admin.settings.sms.test") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    testSmsAlert.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Success!</strong> ${data.message}
                        </div>
                    `;
                    // Clear form
                    document.getElementById('test_phone').value = '';
                } else {
                    testSmsAlert.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Error:</strong> ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                testSmsAlert.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Error:</strong> ${error.message}
                    </div>
                `;
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });
    }
});
</script>
@endpush
@endsection



