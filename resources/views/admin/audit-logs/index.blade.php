@extends('layouts.admin')

@section('title', 'System Audit Logs')

@section('content')
<div class="row mb-4 align-items-center no-print">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1">System Audit Logs</h4>
        <p class="text-muted small mb-0">Track all user activities and system changes</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
            <a href="{{ route('admin.dashboard') }}" data-no-global-handler data-no-preloader class="btn btn-outline-primary btn-sm px-3 shadow-sm">
                <i class="fas fa-home me-1"></i> Dashboard
            </a>
        </div>
    </div>
</div>

<div class="container-fluid py-0 px-0">
    <!-- Modern Unified Filter Bar -->
    <div class="filter-bar shadow-sm glass-morphism no-print mb-4">
        <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="d-flex flex-wrap align-items-end gap-3 p-3">
            <div class="filter-group">
                <label class="filter-label">Quick Periods</label>
                <div class="btn-group preset-group shadow-sm">
                    <a href="{{ route('admin.audit-logs.index', ['period' => 'today']) }}" data-no-global-handler data-no-preloader
                       class="btn btn-preset {{ ($period ?? '') == 'today' ? 'active' : '' }}">Today</a>
                    <a href="{{ route('admin.audit-logs.index', ['period' => 'week']) }}" data-no-global-handler data-no-preloader
                       class="btn btn-preset {{ ($period ?? '') == 'week' ? 'active' : '' }}">Weekly</a>
                    <a href="{{ route('admin.audit-logs.index', ['period' => 'month']) }}" data-no-global-handler data-no-preloader
                       class="btn btn-preset {{ ($period ?? '') == 'month' ? 'active' : '' }}">Monthly</a>
                    <a href="{{ route('admin.audit-logs.index', ['period' => 'year']) }}" data-no-global-handler data-no-preloader
                       class="btn btn-preset {{ ($period ?? '') == 'year' ? 'active' : '' }}">Yearly</a>
                </div>
            </div>

            <div class="filter-divider d-none d-lg-block"></div>

            <div class="filter-group">
                <label class="filter-label">Date Range</label>
                <div class="d-flex gap-2">
                    <div class="input-modern shadow-sm">
                        <i class="fas fa-calendar-alt icon"></i>
                        <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="border-0 bg-transparent x-small fw-bold">
                    </div>
                    <div class="input-modern shadow-sm">
                        <i class="fas fa-calendar-check icon"></i>
                        <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="border-0 bg-transparent x-small fw-bold">
                    </div>
                </div>
            </div>

            @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'manager', 'accountant']))
            <div class="filter-group">
                <label class="filter-label">User</label>
                <div class="input-modern shadow-sm">
                    <select name="user_id" class="border-0 bg-transparent x-small fw-bold" style="max-width: 120px;">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif

            <div class="filter-group">
                <label class="filter-label">Action</label>
                <div class="input-modern shadow-sm">
                    <select name="action" class="border-0 bg-transparent x-small fw-bold">
                        <option value="">All Actions</option>
                        @foreach($actions as $act)
                            <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>{{ ucfirst($act) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="filter-group">
                <label class="filter-label">Model</label>
                <div class="input-modern shadow-sm">
                    <select name="model_type" class="border-0 bg-transparent x-small fw-bold">
                        <option value="">All Models</option>
                        @foreach($modelTypes as $mType)
                            <option value="{{ $mType }}" {{ request('model_type') == $mType ? 'selected' : '' }}>{{ class_basename($mType) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="filter-actions d-flex gap-2">
                <button type="submit" data-no-global-handler data-no-preloader class="btn btn-apply shadow-sm">
                    <i class="fas fa-search me-1"></i> Search
                </button>
                <a href="{{ route('admin.audit-logs.index') }}" data-no-global-handler data-no-preloader class="btn btn-outline-secondary btn-apply bg-white text-dark shadow-sm">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Audit Logs ({{ $auditLogs->total() }})
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Model</th>
                            <th>Device & Location</th>
                            <th>IP Address</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($auditLogs as $log)
                            <tr>
                                <td>
                                    <div>{{ $log->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $log->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    @if($log->user)
                                        <div class="fw-bold">{{ $log->user->name }}</div>
                                        <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $log->user->role ?? 'N/A')) }}</small>
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $actionColors = [
                                            'created' => 'success',
                                            'updated' => 'info',
                                            'deleted' => 'danger',
                                            'login' => 'primary',
                                            'logout' => 'secondary',
                                            'approved' => 'success',
                                            'cancelled' => 'danger',
                                            'assigned' => 'warning',
                                        ];
                                        $color = $actionColors[$log->action] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                                </td>
                                <td>{{ $log->description }}</td>
                                <td>
                                    @if($log->model_type)
                                        <div>{{ class_basename($log->model_type) }}</div>
                                        @if($log->model_id)
                                            <small class="text-muted">ID: {{ $log->model_id }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->device_name)
                                        <div class="d-flex align-items-center mb-1">
                                            @php
                                                $deviceIcons = [
                                                    'mobile' => 'fa-mobile-alt',
                                                    'tablet' => 'fa-tablet-alt',
                                                    'desktop' => 'fa-desktop'
                                                ];
                                                $icon = $deviceIcons[$log->device_type] ?? 'fa-desktop';
                                            @endphp
                                            <i class="fas {{ $icon }} me-2 text-primary"></i>
                                            <span class="fw-bold">{{ $log->device_name }}</span>
                                        </div>
                                    @endif
                                    @if($log->location && $log->location !== 'Local' && $log->location !== 'Unknown')
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                            <small class="text-muted">{{ $log->location }}</small>
                                        </div>
                                    @elseif($log->location === 'Local')
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-home me-2 text-secondary"></i>
                                            <small class="text-muted">Local</small>
                                        </div>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $log->ip_address ?? '-' }}</small>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" type="button" data-bs-toggle="modal" data-bs-target="#logModal{{ $log->id }}">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal for log details -->
                            <div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1" aria-labelledby="logModalLabel{{ $log->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="logModalLabel{{ $log->id }}">
                                                <i class="fas fa-info-circle me-2"></i>Audit Log Details
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <!-- Action Info -->
                                            <div class="mb-4">
                                                <h6 class="text-primary mb-3"><i class="fas fa-tasks me-2"></i>Action Information</h6>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <strong>Action:</strong>
                                                        <div>
                                                            @php
                                                                $actionColors = [
                                                                    'created' => 'success',
                                                                    'updated' => 'info',
                                                                    'deleted' => 'danger',
                                                                    'login' => 'primary',
                                                                    'logout' => 'secondary',
                                                                    'approved' => 'success',
                                                                    'cancelled' => 'danger',
                                                                    'assigned' => 'warning',
                                                                ];
                                                                $color = $actionColors[$log->action] ?? 'secondary';
                                                            @endphp
                                                            <span class="badge bg-{{ $color }} fs-6">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <strong>Date & Time:</strong>
                                                        <div>
                                                            {{ $log->created_at->format('M d, Y') }}<br>
                                                            <small class="text-muted">{{ $log->created_at->format('h:i A') }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 mb-3">
                                                        <strong>Description:</strong>
                                                        <p class="mb-0">{{ $log->description }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- User Information -->
                                            <div class="mb-4">
                                                <h6 class="text-primary mb-3"><i class="fas fa-user me-2"></i>User Information</h6>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <strong>User:</strong>
                                                        <div>
                                                            @if($log->user)
                                                                <div class="fw-bold">{{ $log->user->name }}</div>
                                                                <small class="text-muted">{{ $log->user->email }}</small><br>
                                                                <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $log->user->role ?? 'N/A')) }}</span>
                                                            @else
                                                                <span class="text-muted">System</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <strong>Model:</strong>
                                                        <div>
                                                            @if($log->model_type)
                                                                <div>{{ class_basename($log->model_type) }}</div>
                                                                @if($log->model_id)
                                                                    <small class="text-muted">ID: {{ $log->model_id }}</small>
                                                                @endif
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Changes (if any) -->
                                            @if($log->old_values || $log->new_values)
                                            <div class="mb-4">
                                                <h6 class="text-primary mb-3"><i class="fas fa-exchange-alt me-2"></i>Changes</h6>
                                                @if($log->old_values)
                                                <div class="mb-3">
                                                    <strong>Old Values:</strong>
                                                    <pre class="bg-light p-3 rounded border" style="max-height: 200px; overflow-y: auto; font-size: 0.875rem;"><code>{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                </div>
                                                @endif
                                                @if($log->new_values)
                                                <div class="mb-3">
                                                    <strong>New Values:</strong>
                                                    <pre class="bg-light p-3 rounded border" style="max-height: 200px; overflow-y: auto; font-size: 0.875rem;"><code>{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                </div>
                                                @endif
                                            </div>
                                            @endif

                                            <!-- Device & Location -->
                                            <div class="mb-4">
                                                <h6 class="text-primary mb-3"><i class="fas fa-laptop me-2"></i>Device & Location</h6>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <strong>Device:</strong>
                                                        <div>
                                                            @if($log->device_name)
                                                                <i class="fas fa-{{ $log->device_type === 'mobile' ? 'mobile-alt' : ($log->device_type === 'tablet' ? 'tablet-alt' : 'desktop') }} me-2 text-primary"></i>
                                                                <span class="fw-bold">{{ $log->device_name }}</span>
                                                                <span class="badge bg-secondary ms-2">{{ ucfirst($log->device_type) }}</span>
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <strong>Location:</strong>
                                                        <div>
                                                            @if($log->location && $log->location !== 'Unknown' && $log->location !== 'Local')
                                                                <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                                                <span>{{ $log->location }}</span>
                                                                @if($log->latitude && $log->longitude)
                                                                    <a href="https://www.openstreetmap.org/?mlat={{ $log->latitude }}&mlon={{ $log->longitude }}&zoom=12" target="_blank" class="ms-2 btn btn-sm btn-outline-primary">
                                                                        <i class="fas fa-external-link-alt"></i> View on Map
                                                                    </a>
                                                                @endif
                                                            @else
                                                                <span class="text-muted">{{ $log->location ?? 'N/A' }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Network Information -->
                                            <div class="mb-3">
                                                <h6 class="text-primary mb-3"><i class="fas fa-network-wired me-2"></i>Network Information</h6>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <strong>IP Address:</strong>
                                                        <div><code>{{ $log->ip_address ?? 'N/A' }}</code></div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <strong>User Agent:</strong>
                                                        <div><small class="text-muted">{{ $log->user_agent ?? 'N/A' }}</small></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                <i class="fas fa-times me-1"></i>Close
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted">No audit logs found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($auditLogs->hasPages())
        <div class="card-footer bg-white">
            {{ $auditLogs->links() }}
        </div>
        @endif
    </div>
</div>
@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800&display=swap');

    :root {
        --report-primary: #3b82f6;
        --report-bg: #f8fafc;
        --glass-bg: rgba(255, 255, 255, 0.85);
        --glass-border: rgba(255, 255, 255, 0.3);
    }

    body {
        background-color: var(--report-bg);
        font-family: 'Nunito Sans', sans-serif;
    }

    .glass-morphism {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
    }

    .filter-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-left: 5px; margin-bottom: 5px; }

    .btn-preset {
        font-size: 13px; font-weight: 600; padding: 8px 18px;
        border: 1px solid #e2e8f0; background: white; color: #475569; transition: all 0.2s ease;
    }
    .btn-preset:hover { background: #f1f5f9; color: var(--report-primary); }
    .btn-preset.active {
        background: var(--report-primary); color: white; border-color: var(--report-primary);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .input-modern {
        display: flex; align-items: center; background: white;
        border: 1px solid #e2e8f0; border-radius: 10px; padding: 6px 12px; gap: 8px;
    }
    .input-modern .icon { color: var(--report-primary); font-size: 14px; }
    .input-modern input, .input-modern select { font-size: 12px; font-weight: 600; color: #1e293b; outline: none; border: none; background: transparent; }

    .btn-apply {
        background: #1e293b; color: white; font-weight: 700; padding: 10px 24px;
        border-radius: 10px; font-size: 13px; transition: all 0.2s ease; border: none;
    }
    .btn-apply:hover { background: #0f172a; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); }

    .filter-divider { width: 1px; height: 35px; background: #e2e8f0; align-self: flex-end; margin-bottom: 5px; }
    .preset-group { border-radius: 10px; overflow: hidden; }

    .audit-table th {
        font-size: 10px; text-transform: uppercase; letter-spacing: 1px;
        color: #64748b; background-color: #f8fafc; border-bottom: 1px solid #edf2f7;
    }
    .audit-table td { font-size: 13px; }
    
    .x-small { font-size: 11px; }
</style>
@endpush
@endsection






