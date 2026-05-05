@extends('layouts.admin')

@section('title', 'Notifications - CHIBO BRAND')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-primary text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active">Notifications</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-5 gap-3">
        <div>
            <h1 class="h2 fw-bold text-dark mb-1">Notifications Hub</h1>
            <p class="text-muted mb-0">Stay updated with the latest tasks, orders, and system activities.</p>
        </div>
        <div class="d-flex gap-2">
            @php
                $unreadCount = auth()->user()->notifications()->unread()->count();
            @endphp
            @if($unreadCount > 0)
            <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}">
                @csrf
                <button type="submit" data-no-global-handler class="btn btn-primary d-flex align-items-center gap-2 px-4 shadow-sm border-0" style="border-radius: 12px; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                    <i class="fas fa-check-double"></i>
                    <span>Mark All as Read</span>
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm sticky-top" style="top: 100px; border-radius: 20px; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px);">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
                        <i class="fas fa-sliders-h text-primary"></i>
                        Filter Activity
                    </h5>
                    
                    <form method="GET" action="{{ route('admin.notifications.index') }}">
                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Status</label>
                            <div class="d-flex flex-column gap-2 mt-2">
                                <a href="{{ route('admin.notifications.index', ['status' => 'unread'] + request()->except('status')) }}" 
                                   class="btn btn-light text-start border-0 py-2 px-3 d-flex justify-content-between align-items-center {{ request('status') === 'unread' ? 'bg-primary text-white shadow-sm' : '' }}" 
                                   style="border-radius: 10px;">
                                   <span>Unread</span>
                                   @if($unreadCount > 0)
                                   <span class="badge {{ request('status') === 'unread' ? 'bg-white text-primary' : 'bg-danger text-white' }} rounded-pill">{{ $unreadCount }}</span>
                                   @endif
                                </a>
                                <a href="{{ route('admin.notifications.index', ['status' => 'read'] + request()->except('status')) }}" 
                                   class="btn btn-light text-start border-0 py-2 px-3 {{ request('status') === 'read' ? 'bg-primary text-white shadow-sm' : '' }}" 
                                   style="border-radius: 10px;">Read</a>
                                <a href="{{ route('admin.notifications.index', request()->except('status')) }}" 
                                   class="btn btn-light text-start border-0 py-2 px-3 {{ !request('status') ? 'bg-primary text-white shadow-sm' : '' }}" 
                                   style="border-radius: 10px;">All Notifications</a>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Notification Type</label>
                            <select name="type" class="form-select border-0 bg-light py-2" style="border-radius: 10px;">
                                <option value="">All Types</option>
                                <option value="task" {{ request('type') === 'task' ? 'selected' : '' }}>Design Tasks</option>
                                <option value="delivery" {{ request('type') === 'delivery' ? 'selected' : '' }}>Delivery Tasks</option>
                                <option value="order" {{ request('type') === 'order' ? 'selected' : '' }}>Orders</option>
                                <option value="registration" {{ request('type') === 'registration' ? 'selected' : '' }}>Registrations</option>
                                <option value="alert" {{ request('type') === 'alert' ? 'selected' : '' }}>Alerts</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-uppercase text-muted">Sent By</label>
                            <select name="sender_id" class="form-select border-0 bg-light py-2" style="border-radius: 10px;">
                                <option value="">Everyone</option>
                                @foreach($staff as $member)
                                    <option value="{{ $member->id }}" {{ request('sender_id') == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }} ({{ ucfirst($member->role) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <button type="submit" data-no-global-handler class="btn btn-primary w-100 mt-4 py-2 shadow-sm border-0" style="border-radius: 10px;">Apply Filters</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Notification Feed -->
        <div class="col-lg-9">
            @if(request()->anyFilled(['status', 'type', 'sender_id']))
                <div class="alert bg-white border-0 shadow-sm mb-4 d-flex justify-content-between align-items-center" style="border-radius: 12px; padding: 12px 20px;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-filter text-primary small"></i>
                        <span class="small text-muted fw-bold">Active Filters:</span>
                        <div class="d-flex flex-wrap gap-1 ms-2">
                            @if(request('status')) <span class="badge bg-primary-subtle text-primary rounded-pill">Status: {{ ucfirst(request('status')) }}</span> @endif
                            @if(request('type')) <span class="badge bg-primary-subtle text-primary rounded-pill">Type: {{ ucfirst(request('type')) }}</span> @endif
                            @if(request('sender_id')) 
                                @php $s = $staff->where('id', request('sender_id'))->first(); @endphp
                                <span class="badge bg-primary-subtle text-primary rounded-pill">Sent By: {{ $s ? $s->name : 'Staff' }}</span> 
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-link text-muted text-decoration-none small fw-bold">
                        <i class="fas fa-times me-1"></i>Reset
                    </a>
                </div>
            @endif
            <div class="notification-feed">
                @forelse($notifications as $notification)
                    @php
                        $isTask = str_contains(strtolower($notification->type ?? ''), 'task');
                        $isOrder = str_contains(strtolower($notification->type ?? ''), 'order');
                        $isRegistration = str_contains(strtolower($notification->type ?? ''), 'registration');
                        $isAlert = str_contains(strtolower($notification->type ?? ''), 'alert') || str_contains(strtolower($notification->type ?? ''), 'warning');
                        $isDelivery = str_contains(strtolower($notification->type ?? ''), 'delivery');
                        
                        $iconClass = 'fa-bell';
                        $iconBg = 'primary';
                        
                        if ($isTask && !$isDelivery) { $iconClass = 'fa-palette'; $iconBg = 'purple'; }
                        elseif ($isOrder) { $iconClass = 'fa-shopping-bag'; $iconBg = 'success'; }
                        elseif ($isRegistration) { $iconClass = 'fa-user-plus'; $iconBg = 'info'; }
                        elseif ($isAlert) { $iconClass = 'fa-exclamation-triangle'; $iconBg = 'warning'; }
                        elseif ($isDelivery) { $iconClass = 'fa-truck'; $iconBg = 'info'; }
                    @endphp
                    
                    <div class="card border-0 shadow-sm mb-3 position-relative overflow-hidden notification-card {{ $notification->status === 'unread' ? 'unread-card shadow-md' : '' }}" 
                         style="border-radius: 18px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border-left: 5px solid transparent !important;">
                        
                        @if($notification->status === 'unread')
                        <div class="unread-indicator"></div>
                        @endif

                        <div class="card-body p-4">
                            <div class="d-flex gap-4">
                                <!-- Status Icon -->
                                <div class="flex-shrink-0">
                                    <div class="icon-box icon-{{ $iconBg }} shadow-sm">
                                        <i class="fas {{ $iconClass }}"></i>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h6 class="fw-bold text-dark mb-0">
                                            {{ ucwords(str_replace(['_', '-'], ' ', $notification->type ?? 'System Alert')) }}
                                        </h6>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="d-flex flex-column align-items-end">
                                                <span class="small text-muted fw-medium d-flex align-items-center gap-1">
                                                    <i class="far fa-clock"></i>
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </span>
                                                @if($notification->sender)
                                                <div class="mt-1 d-flex align-items-center gap-1">
                                                    <span class="x-small text-muted">Sent By:</span>
                                                    <span class="badge bg-light text-dark border p-1 px-2 fw-bold" style="font-size: 0.65rem; border-radius: 6px;">
                                                        {{ $notification->sender->name }}
                                                        <span class="ms-1 text-primary opacity-75">({{ ucfirst($notification->sender->role) }})</span>
                                                    </span>
                                                </div>
                                                @endif
                                            </div>
                                            
                                            <div class="dropdown">
                                                <button class="btn btn-link text-muted p-0 border-0" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg" style="border-radius: 12px; padding: 8px;">
                                                    @if($notification->status === 'unread')
                                                    <li>
                                                        <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                                                            @csrf
                                                            <button type="submit" data-no-global-handler class="dropdown-item d-flex align-items-center gap-2 py-2" style="border-radius: 8px;">
                                                                <i class="fas fa-check text-success"></i> Mark as Read
                                                            </button>
                                                        </form>
                                                    </li>
                                                    @endif
                                                    <li>
                                                        <form method="POST" action="{{ route('admin.notifications.delete', $notification->id) }}" onsubmit="return confirm('Archive this notification?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" data-no-global-handler class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger" style="border-radius: 8px;">
                                                                <i class="fas fa-archive"></i> Archive
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="notification-text text-muted mb-3" style="font-size: 0.95rem; line-height: 1.6;">
                                        {!! nl2br(e($notification->message)) !!}
                                    </div>

                                    @php
                                        // Generate URL based on related model
                                        $url = '#';
                                        if ($notification->related_type === 'App\Models\DesignTask' && $notification->related_id) {
                                            $url = route('admin.design-tasks.show', $notification->related_id);
                                        } elseif (str_contains(strtolower($notification->message), 'task')) {
                                            // Fallback for old notifications
                                            preg_match('/ID[:\s]+(\d+)/i', $notification->message, $matches);
                                            if (isset($matches[1])) $url = route('admin.design-tasks.show', $matches[1]);
                                        }
                                    @endphp
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        @if($url !== '#')
                                        <a href="{{ $url }}" class="btn btn-link p-0 text-decoration-none fw-bold d-flex align-items-center gap-1 action-link">
                                            View Details
                                            <i class="fas fa-arrow-right" style="font-size: 0.8rem;"></i>
                                        </a>
                                        @else
                                        <span></span> {{-- Spacer --}}
                                        @endif
                                        
                                        @if($isDelivery)
                                        <span class="badge bg-info-subtle text-info rounded-pill px-3 py-1 fw-bold" style="font-size: 0.65rem;">
                                            <i class="fas fa-truck me-1"></i>DELIVERY
                                        </span>
                                        @endif
                                        
                                        @if($notification->status === 'unread')
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold" style="font-size: 0.65rem;">
                                            <i class="fas fa-bolt me-1"></i>NEW
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-bell-slash text-muted" style="font-size: 5rem; opacity: 0.2;"></i>
                        </div>
                        <h4 class="fw-bold text-dark">No Notifications Found</h4>
                        <p class="text-muted">You're all caught up! When you receive new activities, they'll appear here.</p>
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-primary px-4 mt-2" style="border-radius: 12px;">Clear Filters</a>
                    </div>
                @endforelse

                <!-- Pagination -->
                <div class="mt-5 d-flex justify-content-center">
                    {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern Color Palette */
:root {
    --purple: #6f42c1;
    -- purple-light: #ebe5f7;
    --success-light: #e7f3ec;
    --info-light: #e7f0f7;
    --warning-light: #fef5e7;
    --primary-light: #e7efff;
}

.x-small { font-size: 0.7rem; }

body {
    background-color: #f8f9fa;
}

/* Glassmorphism Classes */
.notification-card {
    background: #fff;
    border-radius: 18px;
    transition: all 0.3s ease;
}

.notification-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08) !important;
}

.unread-card {
    background: #fff;
    border-left: 5px solid #0d6efd !important;
}

/* Icon Box Custom Styling */
.icon-box {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.icon-purple { background-color: var(--purple-light); color: var(--purple); }
.icon-success { background-color: var(--success-light); color: #28a745; }
.icon-info { background-color: var(--info-light); color: #0dcaf0; }
.icon-warning { background-color: var(--warning-light); color: #ffc107; }
.icon-primary { background-color: var(--primary-light); color: #0d6efd; }

.action-link {
    font-size: 0.85rem;
    color: #6c757d;
    transition: all 0.2s;
}

.action-link:hover {
    color: #0d6efd;
}

.action-link i {
    transition: transform 0.2s;
}

.action-link:hover i {
    transform: translateX(4px);
}

.unread-indicator {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 10px;
    height: 10px;
    background-color: #0d6efd;
    border-radius: 50%;
    box-shadow: 0 0 10px rgba(13, 110, 253, 0.5);
}

/* Badge Tweaks */
.bg-primary-subtle {
    background-color: #e7efff !important;
}

/* Responsive Table Fixes */
    /* Mobile Responsiveness & Font Size Reductions */
    @media (max-width: 768px) {
        /* Page Header */
        h1.h2 { font-size: 1.25rem !important; }
        .text-muted.mb-0 { font-size: 0.75rem !important; }
        .btn-primary.px-4 { padding: 0.5rem 1rem !important; font-size: 0.8rem !important; }
        
        /* Filter Sidebar - ensure it doesn't take too much space or adjust fonts */
        .card-body.p-4 { padding: 1rem !important; }
        h5.fw-bold { font-size: 1rem !important; }
        .form-label { font-size: 0.6rem !important; }
        .btn-light { font-size: 0.8rem !important; padding: 0.5rem !important; }
        .form-select { font-size: 0.8rem !important; }
        
        /* Notification Card */
        .notification-card .card-body { padding: 1rem !important; }
        .icon-box { width: 45px !important; height: 45px !important; font-size: 1.1rem !important; }
        h6.fw-bold { font-size: 0.85rem !important; }
        .small.text-muted { font-size: 0.7rem !important; }
        .notification-text { font-size: 0.8rem !important; line-height: 1.4 !important; }
        .action-link { font-size: 0.75rem !important; }
        .badge { font-size: 0.6rem !important; padding: 0.25rem 0.5rem !important; }
        
        /* Sidebar collapse behavior (since it's col-lg-3, it will be full width on mobile) */
        .col-lg-3 { margin-bottom: 1.5rem; }
    }
</style>
@endsection
