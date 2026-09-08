@extends('layouts.admin')

@section('page-title', 'Settings')

@section('content')
<div class="container-fluid px-3">

    <!-- Settings Layout: Sidebar + Content -->
    <div class="settings-layout d-flex gap-3" style="min-height: 70vh;">

        <!-- ── Left Sidebar ── -->
        <div class="settings-sidebar flex-shrink-0">
            <div class="settings-sidebar-inner">
                <div class="px-2 py-2">
                    <a href="#" class="settings-nav-item" data-tab="general">
                        <i class="fas fa-sliders-h"></i>
                        <span>General</span>
                    </a>
                    <a href="#" class="settings-nav-item" data-tab="company">
                        <i class="fas fa-building"></i>
                        <span>Company Info</span>
                    </a>
                    <a href="#" class="settings-nav-item" data-tab="notifications">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
                    </a>
                    <a href="{{ route('admin.settings.sms') }}" class="settings-nav-item">
                        <i class="fas fa-sms"></i>
                        <span>SMS</span>
                    </a>
                    <a href="#" class="settings-nav-item" data-tab="system">
                        <i class="fas fa-server"></i>
                        <span>System</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- ── Right Content Panel ── -->
        <div class="settings-content flex-grow-1">

            <!-- ── General ── -->
            <div class="settings-panel active" id="panel-general">
                <div class="settings-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="panel-icon-wrap bg-primary-soft">
                            <i class="fas fa-sliders-h text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">General Settings</h6>
                            <small class="text-muted">Basic site configuration</small>
                        </div>
                    </div>
                </div>
                <div class="settings-panel-body">
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="settings-section">
                            <div class="settings-section-title">Site Identity</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Site Name</label>
                                    <input type="text" class="form-control" name="site_name" value="CHIBO BRAND">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Site Tagline</label>
                                    <input type="text" class="form-control" name="site_tagline" value="Professional Printing & Branding Solutions">
                                </div>
                            </div>
                        </div>

                        <div class="settings-section">
                            <div class="settings-section-title">Regional</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Timezone</label>
                                    <select class="form-select" name="timezone">
                                        <option value="Africa/Dar_es_Salaam" selected>East Africa Time (EAT)</option>
                                        <option value="UTC">UTC</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Currency</label>
                                    <select class="form-select" name="currency">
                                        <option value="TZS" selected>Tanzanian Shilling (TZS)</option>
                                        <option value="USD">US Dollar (USD)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="settings-section">
                            <div class="settings-section-title">Maintenance</div>
                            <div class="toggle-row">
                                <div>
                                    <div class="fw-semibold" style="font-size:0.82rem;">Maintenance Mode</div>
                                    <div class="text-muted" style="font-size:0.75rem;">Site will be unavailable to customers when enabled</div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" role="switch">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end pt-2">
                            <button type="submit" class="btn btn-save">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ── Company Info ── -->
            <div class="settings-panel" id="panel-company">
                <div class="settings-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="panel-icon-wrap bg-info-soft">
                            <i class="fas fa-building text-info"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Company Information</h6>
                            <small class="text-muted">Legal and contact details</small>
                        </div>
                    </div>
                </div>
                <div class="settings-panel-body">
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="settings-section">
                            <div class="settings-section-title">Identity</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Company Name</label>
                                    <input type="text" class="form-control" name="company_name" value="CHIBO BRAND">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tax ID / TIN</label>
                                    <input type="text" class="form-control" name="company_tax_id">
                                </div>
                            </div>
                        </div>

                        <div class="settings-section">
                            <div class="settings-section-title">Contact</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="company_email" value="info@chibobrand.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control" name="company_phone" value="+255 XXX XXX XXX">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">WhatsApp</label>
                                    <input type="text" class="form-control" name="company_whatsapp" value="+255 XXX XXX XXX">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Website</label>
                                    <input type="url" class="form-control" name="company_website" value="https://chibobrand.com">
                                </div>
                            </div>
                        </div>

                        <div class="settings-section">
                            <div class="settings-section-title">Address</div>
                            <textarea class="form-control" name="company_address" rows="3">Dar es Salaam, Tanzania</textarea>
                        </div>

                        <div class="d-flex justify-content-end pt-2">
                            <button type="submit" class="btn btn-save">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ── Notifications ── -->
            <div class="settings-panel" id="panel-notifications">
                <div class="settings-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="panel-icon-wrap bg-warning-soft">
                            <i class="fas fa-bell text-warning"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Notification Settings</h6>
                            <small class="text-muted">Control alert and messaging preferences</small>
                        </div>
                    </div>
                </div>
                <div class="settings-panel-body">
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="settings-section">
                            <div class="settings-section-title">Email Notifications</div>
                            <div class="toggle-row">
                                <div>
                                    <div class="fw-semibold" style="font-size:0.82rem;">New Order</div>
                                    <div class="text-muted" style="font-size:0.75rem;">Get notified when a new order is placed</div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="notify_new_order" role="switch" checked>
                                </div>
                            </div>
                            <div class="toggle-row">
                                <div>
                                    <div class="fw-semibold" style="font-size:0.82rem;">Low Stock Alerts</div>
                                    <div class="text-muted" style="font-size:0.75rem;">Get notified when stock levels are low</div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="notify_low_stock" role="switch" checked>
                                </div>
                            </div>
                            <div class="toggle-row">
                                <div>
                                    <div class="fw-semibold" style="font-size:0.82rem;">New Customer Registration</div>
                                    <div class="text-muted" style="font-size:0.75rem;">Get notified when a customer registers</div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="notify_new_customer" role="switch">
                                </div>
                            </div>
                        </div>

                        <div class="settings-section">
                            <div class="settings-section-title">SMS Notifications</div>
                            <div class="toggle-row">
                                <div>
                                    <div class="fw-semibold" style="font-size:0.82rem;">Order Confirmation SMS</div>
                                    <div class="text-muted" style="font-size:0.75rem;">Send SMS to customers when order is confirmed</div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="sms_order_confirmation" role="switch" checked>
                                </div>
                            </div>
                            <div class="toggle-row">
                                <div>
                                    <div class="fw-semibold" style="font-size:0.82rem;">Order Status Updates</div>
                                    <div class="text-muted" style="font-size:0.75rem;">Send SMS when order status changes</div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="sms_order_status" role="switch" checked>
                                </div>
                            </div>
                        </div>

                        <div class="settings-section">
                            <div class="settings-section-title">Admin Notification Email</div>
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Recipient Email</label>
                                    <input type="email" class="form-control" name="admin_email" value="admin@chibobrand.com">
                                    <div class="form-text">All admin alerts will be sent to this address</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end pt-2">
                            <button type="submit" class="btn btn-save">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ── System ── -->
            <div class="settings-panel" id="panel-system">
                <div class="settings-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="panel-icon-wrap bg-secondary-soft">
                            <i class="fas fa-server text-secondary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">System Information</h6>
                            <small class="text-muted">Environment details and maintenance tools</small>
                        </div>
                    </div>
                </div>
                <div class="settings-panel-body">

                    <div class="settings-section">
                        <div class="settings-section-title">Security</div>
                        <div class="toggle-row">
                            <div>
                                <div class="fw-semibold" style="font-size:0.82rem;">Two-Factor Authentication (2FA)</div>
                                <div class="text-muted" style="font-size:0.75rem;">Require users to verify their identity on unrecognized devices during login</div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span id="twofa-status-label" class="badge {{ $twoFaEnabled ? 'bg-success' : 'bg-secondary' }}" style="font-size:0.72rem;">
                                    {{ $twoFaEnabled ? 'Enabled' : 'Disabled' }}
                                </span>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="twofa_toggle" role="switch"
                                           {{ $twoFaEnabled ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                        <div class="row g-3">
                            <div class="col-sm-6 col-lg-3">
                                <div class="sys-info-card">
                                    <div class="sys-info-icon"><i class="fab fa-laravel"></i></div>
                                    <div class="sys-info-label">Laravel</div>
                                    <div class="sys-info-value">{{ app()->version() }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="sys-info-card">
                                    <div class="sys-info-icon"><i class="fab fa-php"></i></div>
                                    <div class="sys-info-label">PHP</div>
                                    <div class="sys-info-value">{{ PHP_VERSION }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="sys-info-card">
                                    <div class="sys-info-icon"><i class="fas fa-code-branch"></i></div>
                                    <div class="sys-info-label">Environment</div>
                                    <div class="sys-info-value text-capitalize">{{ app()->environment() }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="sys-info-card">
                                    <div class="sys-info-icon"><i class="fas fa-bug"></i></div>
                                    <div class="sys-info-label">Debug Mode</div>
                                    <div class="sys-info-value">{{ config('app.debug') ? 'On' : 'Off' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="settings-section">
                        <div class="settings-section-title">Cache & Maintenance</div>
                        <div class="row g-2">
                            <div class="col-auto">
                                <button type="button" class="btn btn-action-outline btn-outline-primary" onclick="clearCache()">
                                    <i class="fas fa-broom me-2"></i>Clear Cache
                                </button>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-action-outline btn-outline-info" onclick="clearViews()">
                                    <i class="fas fa-eye-slash me-2"></i>Clear Views
                                </button>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-action-outline btn-outline-warning" onclick="clearConfig()">
                                    <i class="fas fa-cog me-2"></i>Clear Config
                                </button>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-action-outline btn-outline-danger" onclick="clearAll()">
                                    <i class="fas fa-trash-alt me-2"></i>Clear All
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /.settings-content -->
    </div><!-- /.settings-layout -->
</div>

<style>
/* ── Layout ── */
.settings-layout {
    align-items: flex-start;
}

/* ── Sidebar ── */
.settings-sidebar {
    width: 220px;
    position: sticky;
    top: 70px;
}

.settings-sidebar-inner {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}

.settings-sidebar-brand {
    border-bottom: 1px solid #f1f3f5;
    background: #fafafa;
}

.brand-icon-wrap {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #dc3545, #c0392b);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 0.8rem;
    flex-shrink: 0;
}

.settings-nav-group {
    padding: 0 4px;
}

.settings-nav-label {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: #adb5bd;
    padding: 0 8px 4px;
}

.settings-nav-item {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 7px 10px;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 500;
    color: #495057;
    text-decoration: none;
    transition: all 0.18s ease;
    margin-bottom: 2px;
}

.settings-nav-item i {
    font-size: 0.78rem;
    width: 16px;
    text-align: center;
    color: #868e96;
    flex-shrink: 0;
    transition: color 0.18s;
}

.settings-nav-item:hover {
    background: #f8f9fa;
    color: #dc3545;
}

.settings-nav-item:hover i {
    color: #dc3545;
}

.settings-nav-item.active {
    background: rgba(220, 53, 69, 0.08);
    color: #dc3545;
    font-weight: 600;
}

.settings-nav-item.active i {
    color: #dc3545;
}

/* ── Content Panel ── */
.settings-content {
    min-width: 0;
}

.settings-panel {
    display: none;
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}

.settings-panel.active {
    display: block;
}

.settings-panel-header {
    padding: 14px 20px;
    border-bottom: 1px solid #f1f3f5;
    background: #fafafa;
}

.panel-icon-wrap {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.bg-primary-soft   { background: rgba(13,110,253,0.1); }
.bg-info-soft      { background: rgba(13,202,240,0.1); }
.bg-warning-soft   { background: rgba(255,193,7,0.12); }
.bg-secondary-soft { background: rgba(108,117,125,0.1); }

.settings-panel-body {
    padding: 20px;
}

/* ── Sections ── */
.settings-section {
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid #f1f3f5;
}

.settings-section:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.settings-section-title {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #adb5bd;
    margin-bottom: 14px;
}

/* ── Toggle Row ── */
.toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f8f9fa;
}

.toggle-row:last-child {
    border-bottom: none;
}

/* ── Form ── */
.form-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 5px;
}

.form-control, .form-select {
    font-size: 0.82rem;
    border-radius: 8px;
    border-color: #e9ecef;
    padding: 0.5rem 0.75rem;
}

.form-control:focus, .form-select:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.18rem rgba(220,53,69,0.12);
}

.form-text {
    font-size: 0.72rem;
    color: #adb5bd;
}

/* ── Save Button ── */
.btn-save {
    background: linear-gradient(135deg, #dc3545 0%, #c0392b 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 8px 20px;
    font-size: 0.82rem;
    font-weight: 600;
    transition: all 0.2s ease;
}

.btn-save:hover {
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220,53,69,0.25);
    color: #fff;
}

/* ── System Info Cards ── */
.sys-info-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 14px;
    text-align: center;
}

.sys-info-icon {
    font-size: 1.4rem;
    color: #868e96;
    margin-bottom: 6px;
}

.sys-info-label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #adb5bd;
    margin-bottom: 3px;
}

.sys-info-value {
    font-size: 0.9rem;
    font-weight: 700;
    color: #343a40;
}

.btn-action-outline {
    font-size: 0.8rem;
    border-radius: 8px;
    padding: 7px 14px;
    font-weight: 600;
}

/* ── Form switch red ── */
.form-check-input:checked {
    background-color: #dc3545;
    border-color: #dc3545;
}

/* ── Responsive ── */
@media (max-width: 768px) {
    .settings-layout {
        flex-direction: column;
    }
    .settings-sidebar {
        width: 100%;
        position: static;
    }
    .settings-sidebar-inner {
        display: flex;
        overflow-x: auto;
    }
    .settings-sidebar-brand {
        display: none;
    }
    .settings-nav-group {
        display: flex;
        flex-direction: row;
        gap: 4px;
        white-space: nowrap;
        padding: 6px;
    }
    .settings-nav-label { display: none; }
    .settings-nav-item {
        padding: 6px 10px;
        margin-bottom: 0;
    }
}
</style>

<script>
// ── Tab switching ──
var initialTab = new URLSearchParams(window.location.search).get('tab') || 'general';

function activateTab(tabName) {
    document.querySelectorAll('.settings-nav-item[data-tab]').forEach(function(i) { i.classList.remove('active'); });
    var navItem = document.querySelector('.settings-nav-item[data-tab="' + tabName + '"]');
    if (navItem) navItem.classList.add('active');

    document.querySelectorAll('.settings-panel').forEach(function(p) { p.classList.remove('active'); });
    var panel = document.getElementById('panel-' + tabName);
    if (panel) panel.classList.add('active');
}

// Activate on page load from URL param
activateTab(initialTab);

document.querySelectorAll('.settings-nav-item').forEach(function(item) {
    item.addEventListener('click', function(e) {
        var tab = this.dataset.tab;

        // If no data-tab, it's a real navigation link — let it navigate
        if (!tab) return;

        e.preventDefault();
        activateTab(tab);
    });
});

// ── Cache helpers ──
function clearCache() {
    if (!confirm('Clear application cache?')) return;
    fetch('/admin/clear-cache', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } })
        .then(r => r.json()).then(d => alert(d.message || 'Cache cleared!')).catch(() => alert('Error clearing cache'));
}
function clearViews() {
    if (!confirm('Clear compiled views?')) return;
    fetch('/admin/clear-views', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } })
        .then(r => r.json()).then(d => alert(d.message || 'Views cleared!')).catch(() => alert('Error clearing views'));
}
function clearConfig() {
    if (!confirm('Clear configuration cache?')) return;
    fetch('/admin/clear-config', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } })
        .then(r => r.json()).then(d => alert(d.message || 'Config cleared!')).catch(() => alert('Error clearing config'));
}
function clearAll() {
    if (!confirm('Clear all caches?')) return;
    fetch('/admin/clear-all', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } })
        .then(r => r.json()).then(d => alert(d.message || 'All caches cleared!')).catch(() => alert('Error clearing caches'));
}

// ── 2FA Toggle ──
const twofaToggle = document.getElementById('twofa_toggle');
if (twofaToggle) {
    twofaToggle.addEventListener('change', function() {
        const enabled = this.checked;
        const label = document.getElementById('twofa-status-label');
        this.disabled = true;

        fetch('{{ route('admin.settings.toggle2fa') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
            body: JSON.stringify({ enabled: enabled })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                label.textContent = enabled ? 'Enabled' : 'Disabled';
                label.className = 'badge ' + (enabled ? 'bg-success' : 'bg-secondary');
            } else {
                this.checked = !enabled; // revert
            }
        })
        .catch(() => { this.checked = !enabled; })
        .finally(() => { this.disabled = false; });
    });
}
</script>
@endsection
