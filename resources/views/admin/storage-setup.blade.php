@extends('layouts.admin')

@section('title', 'Storage Setup - Shared Hosting')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-hdd me-2"></i>Storage Symlink Setup
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6>Current Status</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Storage Directory</strong></td>
                                        <td>
                                            @if($status['storage_exists'])
                                                <span class="badge bg-success">✅ Exists</span>
                                            @else
                                                <span class="badge bg-danger">❌ Missing</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Storage Writable</strong></td>
                                        <td>
                                            @if($status['storage_writable'])
                                                <span class="badge bg-success">✅ Yes</span>
                                            @else
                                                <span class="badge bg-danger">❌ No</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Public Directory Writable</strong></td>
                                        <td>
                                            @if($status['public_writable'])
                                                <span class="badge bg-success">✅ Yes</span>
                                            @else
                                                <span class="badge bg-danger">❌ No</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Symlink Exists</strong></td>
                                        <td>
                                            @if($status['symlink_exists'])
                                                <span class="badge bg-success">✅ Yes</span>
                                            @else
                                                <span class="badge bg-warning">⚠️ No</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Is Valid Symlink</strong></td>
                                        <td>
                                            @if($status['is_symlink'])
                                                <span class="badge bg-success">✅ Yes</span>
                                            @elseif($status['symlink_exists'])
                                                <span class="badge bg-warning">⚠️ No (Regular File/Folder)</span>
                                            @else
                                                <span class="badge bg-secondary">- N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($status['symlink_target'])
                                    <tr>
                                        <td><strong>Symlink Target</strong></td>
                                        <td><code>{{ $status['symlink_target'] }}</code></td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6>Quick Actions</h6>
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" onclick="createSymlink()">
                                    <i class="fas fa-link me-2"></i>Create Symlink
                                </button>
                                <button class="btn btn-info" onclick="runArtisanCommand()">
                                    <i class="fas fa-terminal me-2"></i>Run Artisan Command
                                </button>
                                <button class="btn btn-success" onclick="testStorage()">
                                    <i class="fas fa-check me-2"></i>Test Storage
                                </button>
                                <button class="btn btn-secondary" onclick="location.reload()">
                                    <i class="fas fa-refresh me-2"></i>Refresh Status
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-12">
                            <h6>Manual Setup Instructions</h6>
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>For Shared Hosting</h6>
                                <p>If automatic setup fails, follow these manual steps:</p>
                                <ol>
                                    <li><strong>Using cPanel File Manager:</strong>
                                        <ul>
                                            <li>Login to cPanel</li>
                                            <li>Open File Manager</li>
                                            <li>Navigate to public_html</li>
                                            <li>Delete existing "storage" folder if it exists</li>
                                            <li>Right-click → "Create Symbolic Link"</li>
                                            <li>Link name: <code>storage</code></li>
                                            <li>Target: <code>../storage/app/public</code></li>
                                        </ul>
                                    </li>
                                    <li><strong>Using FTP/SFTP:</strong>
                                        <ul>
                                            <li>Connect to your server</li>
                                            <li>Navigate to public_html</li>
                                            <li>Create symlink from <code>storage</code> to <code>../storage/app/public</code></li>
                                        </ul>
                                    </li>
                                    <li><strong>Alternative - .htaccess method:</strong>
                                        <div class="mt-2">
                                            <p>Add this to your <code>public_html/.htaccess</code> file:</p>
                                            <pre class="bg-dark text-light p-2 rounded"><code>RewriteEngine On
RewriteRule ^storage/(.*)$ ../storage/app/public/$1 [L]</code></pre>
                                        </div>
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p id="loadingText">Processing...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showLoading(text = 'Processing...') {
    document.getElementById('loadingText').textContent = text;
    new bootstrap.Modal(document.getElementById('loadingModal')).show();
}

function hideLoading() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('loadingModal'));
    if (modal) modal.hide();
}

function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.card-body').insertBefore(alertDiv, document.querySelector('.card-body').firstChild);
}

function createSymlink() {
    showLoading('Creating symlink...');
    
    fetch('{{ route("admin.storage.create-symlink") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showAlert(`✅ ${data.message}`, 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showAlert(`❌ ${data.message}`, 'danger');
        }
    })
    .catch(error => {
        hideLoading();
        showAlert(`❌ Error: ${error.message}`, 'danger');
    });
}

function runArtisanCommand() {
    showLoading('Running artisan storage:link...');
    
    fetch('{{ route("admin.storage.run-artisan") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showAlert(`✅ ${data.message}`, 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showAlert(`❌ ${data.message}`, 'danger');
        }
    })
    .catch(error => {
        hideLoading();
        showAlert(`❌ Error: ${error.message}`, 'danger');
    });
}

function testStorage() {
    showLoading('Testing storage access...');
    
    fetch('{{ route("admin.storage.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showAlert(`✅ ${data.message}`, 'success');
        } else {
            showAlert(`❌ ${data.message}`, 'danger');
        }
    })
    .catch(error => {
        hideLoading();
        showAlert(`❌ Error: ${error.message}`, 'danger');
    });
}
</script>
@endpush
