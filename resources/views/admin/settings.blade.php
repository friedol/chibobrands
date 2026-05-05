@extends('layouts.admin')

@section('page-title', 'Settings')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <i class="fas fa-cog me-1"></i>Settings
            </li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Settings</h2>
                    <p class="text-muted">Manage your system settings and preferences</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Tabs -->
    <div class="card">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="card-title mb-0">
                <i class="fas fa-cog me-2"></i>System Settings
            </h6>
        </div>
        <div class="card-body p-0">
            <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                        <i class="fas fa-cog me-2"></i>General
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="company-tab" data-bs-toggle="tab" data-bs-target="#company" type="button" role="tab">
                        <i class="fas fa-building me-2"></i>Company Info
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">
                        <i class="fas fa-bell me-2"></i>Notifications
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab">
                        <i class="fas fa-server me-2"></i>System
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="settingsTabContent">
                <!-- General Settings -->
                <div class="tab-pane fade show active" id="general" role="tabpanel">
                    <h5 class="mb-3">General Settings</h5>
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="site_name" class="form-label">Site Name</label>
                                <input type="text" class="form-control" id="site_name" name="site_name" value="CHIBO BRAND" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="site_tagline" class="form-label">Site Tagline</label>
                                <input type="text" class="form-control" id="site_tagline" name="site_tagline" value="Professional Printing & Branding Solutions">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="timezone" class="form-label">Timezone</label>
                                <select class="form-select" id="timezone" name="timezone">
                                    <option value="Africa/Dar_es_Salaam" selected>East Africa Time (EAT)</option>
                                    <option value="UTC">UTC</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="currency" class="form-label">Currency</label>
                                <select class="form-select" id="currency" name="currency">
                                    <option value="TZS" selected>Tanzanian Shilling (TZS)</option>
                                    <option value="USD">US Dollar (USD)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="maintenance_mode" class="form-label">Maintenance Mode</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode">
                                <label class="form-check-label" for="maintenance_mode">
                                    Enable maintenance mode (site will be unavailable to customers)
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </form>
                </div>

                <!-- Company Info -->
                <div class="tab-pane fade" id="company" role="tabpanel">
                    <h5 class="mb-3">Company Information</h5>
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company_name" class="form-label">Company Name</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" value="CHIBO BRAND" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="company_email" class="form-label">Company Email</label>
                                <input type="email" class="form-control" id="company_email" name="company_email" value="info@chibobrand.com" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company_phone" class="form-label">Company Phone</label>
                                <input type="text" class="form-control" id="company_phone" name="company_phone" value="+255 XXX XXX XXX" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="company_whatsapp" class="form-label">WhatsApp Number</label>
                                <input type="text" class="form-control" id="company_whatsapp" name="company_whatsapp" value="+255 XXX XXX XXX">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="company_address" class="form-label">Company Address</label>
                            <textarea class="form-control" id="company_address" name="company_address" rows="3" required>Dar es Salaam, Tanzania</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company_website" class="form-label">Website</label>
                                <input type="url" class="form-control" id="company_website" name="company_website" value="https://chibobrand.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="company_tax_id" class="form-label">Tax ID / TIN</label>
                                <input type="text" class="form-control" id="company_tax_id" name="company_tax_id">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </form>
                </div>

                <!-- Notifications -->
                <div class="tab-pane fade" id="notifications" role="tabpanel">
                    <h5 class="mb-3">Notification Settings</h5>
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <h6>Email Notifications</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="notify_new_order" name="notify_new_order" checked>
                                <label class="form-check-label" for="notify_new_order">
                                    New order notifications
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="notify_low_stock" name="notify_low_stock" checked>
                                <label class="form-check-label" for="notify_low_stock">
                                    Low stock alerts
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="notify_new_customer" name="notify_new_customer">
                                <label class="form-check-label" for="notify_new_customer">
                                    New customer registrations
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>SMS Notifications</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="sms_order_confirmation" name="sms_order_confirmation" checked>
                                <label class="form-check-label" for="sms_order_confirmation">
                                    Send SMS order confirmations to customers
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="sms_order_status" name="sms_order_status" checked>
                                <label class="form-check-label" for="sms_order_status">
                                    Send SMS order status updates
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="admin_email" class="form-label">Admin Notification Email</label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email" value="admin@chibobrand.com">
                            <small class="text-muted">Email address to receive admin notifications</small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </form>
                </div>

                <!-- System Settings -->
                <div class="tab-pane fade" id="system" role="tabpanel">
                    <h5 class="mb-3">System Information</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Laravel Version</h6>
                                    <p class="card-text h4">{{ app()->version() }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">PHP Version</h6>
                                    <p class="card-text h4">{{ PHP_VERSION }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Environment</h6>
                                    <p class="card-text h4">{{ app()->environment() }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Debug Mode</h6>
                                    <p class="card-text h4">{{ config('app.debug') ? 'Enabled' : 'Disabled' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3">System Actions</h6>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-primary" onclick="clearCache()">
                            <i class="fas fa-broom me-2"></i>Clear Cache
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="clearViews()">
                            <i class="fas fa-eye-slash me-2"></i>Clear Views
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="clearConfig()">
                            <i class="fas fa-cog me-2"></i>Clear Config
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="clearAll()">
                            <i class="fas fa-trash-alt me-2"></i>Clear All
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Breadcrumb styles */
.breadcrumb {
    background: transparent;
    padding: 0.5rem 0;
    margin-bottom: 0;
    font-size: 0.875rem;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: #6c757d;
    font-size: 1.2rem;
    font-weight: 600;
    margin: 0 0.5rem;
}

.breadcrumb-item a {
    color: #6c757d;
    text-decoration: none;
    transition: color 0.2s ease;
}

.breadcrumb-item a:hover {
    color: #0d6efd;
}

.breadcrumb-item.active {
    color: #495057;
    font-weight: 500;
}

.breadcrumb-item i {
    font-size: 0.75rem;
}

/* Card header height reduction */
.card-header {
    padding: 0.5rem 1rem;
    min-height: 2.5rem;
}

.card-header h6 {
    font-size: 0.95rem;
    font-weight: 600;
    line-height: 1.2;
}

/* Font size reductions */
h2 {
    font-size: 1.5rem;
}

h5 {
    font-size: 1rem;
}

h6 {
    font-size: 0.9rem;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 500;
}

.form-control, .form-select {
    font-size: 0.875rem;
}

.btn {
    font-size: 0.875rem;
}

.text-muted {
    font-size: 0.8rem;
}

small {
    font-size: 0.75rem;
}

.card-text {
    font-size: 0.9rem;
}

.card-text.h4 {
    font-size: 1.1rem;
}

/* Settings Page Styles */
.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 2px solid transparent;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
    color: #FF0000;
    border-color: transparent;
}

.nav-tabs .nav-link.active {
    color: #FF0000;
    background-color: #fff; /* Add subtle white or light background */
    border-color: transparent transparent #FF0000;
    font-weight: 700;
    text-shadow: 0 0 1px rgba(255, 0, 0, 0.4); /* make text pop slightly */
}


.card {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.card-header {
    background: white;
    border-bottom: 1px solid #e9ecef;
    padding: 0;
}

.form-control,
.form-select {
    border-radius: 8px;
    border: 1px solid #e9ecef;
    padding: 0.75rem;
}

.form-control:focus,
.form-select:focus {
    border-color: #FF0000;
    box-shadow: 0 0 0 0.2rem rgba(255, 0, 0, 0.1);
}

.btn-primary {
    background: linear-gradient(135deg, #FF0000 0%, #cc0000 100%);
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #cc0000 0%, #990000 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 0, 0, 0.3);
}

.btn-outline-primary,
.btn-outline-info,
.btn-outline-warning,
.btn-outline-danger {
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
}

.form-check-input:checked {
    background-color: #FF0000;
    border-color: #FF0000;
}

.bg-light {
    background-color: #f8f9fa !important;
}

/* Responsive */
@media (max-width: 768px) {
    .nav-tabs .nav-link {
        padding: 0.75rem 1rem;
        font-size: 0.8rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    h2 {
        font-size: 1.25rem;
    }
    
    h5 {
        font-size: 0.9rem;
    }
    
    h6 {
        font-size: 0.85rem;
    }
    
    .form-label {
        font-size: 0.8rem;
    }
    
    .form-control, .form-select {
        font-size: 0.8rem;
    }
    
    .btn {
        font-size: 0.8rem;
    }
    
    .card-header {
        padding: 0.25rem 0.5rem;
        min-height: 2rem;
    }
    
    .card-header h6 {
        font-size: 0.85rem;
    }
}

@media (max-width: 576px) {
    .nav-tabs {
        flex-wrap: nowrap;
        overflow-x: auto;
    }
    
    .nav-tabs .nav-link {
        padding: 0.75rem;
        font-size: 0.75rem;
        white-space: nowrap;
    }
    
    .nav-tabs .nav-link i {
        display: none;
    }
}
</style>

<script>
function clearCache() {
    if (confirm('Clear application cache?')) {
        fetch('/admin/clear-cache', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || 'Cache cleared successfully!');
        })
        .catch(error => {
            alert('Error clearing cache');
        });
    }
}

function clearViews() {
    if (confirm('Clear compiled views?')) {
        fetch('/admin/clear-views', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || 'Views cleared successfully!');
        })
        .catch(error => {
            alert('Error clearing views');
        });
    }
}

function clearConfig() {
    if (confirm('Clear configuration cache?')) {
        fetch('/admin/clear-config', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || 'Config cleared successfully!');
        })
        .catch(error => {
            alert('Error clearing config');
        });
    }
}

function clearAll() {
    if (confirm('Clear all caches? This will clear cache, views, config, and routes.')) {
        fetch('/admin/clear-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || 'All caches cleared successfully!');
        })
        .catch(error => {
            alert('Error clearing caches');
        });
    }
}
</script>
@endsection
