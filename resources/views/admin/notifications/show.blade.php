@extends('layouts.admin')

@section('title', 'Notification Details - CHIBO BRAND')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-primary text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}" class="text-primary text-decoration-none">Notifications</a></li>
            <li class="breadcrumb-item active">Details</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header Card -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden; background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-left: 6px solid #0d6efd !important;">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            @php
                                $isTask = str_contains(strtolower($notification->type ?? ''), 'task');
                                $isOrder = str_contains(strtolower($notification->type ?? ''), 'order');
                                $isUrgent = str_contains(strtolower($notification->type ?? ''), 'alert') || str_contains(strtolower($notification->type ?? ''), 'warning');
                                
                                $typeLabel = ucwords(str_replace(['_', '-'], ' ', $notification->type ?? 'System Alert'));
                                $icon = 'fa-bell';
                                $themeColor = '#0d6efd';
                                
                                if ($isTask) { $icon = 'fa-palette'; $themeColor = '#6f42c1'; }
                                elseif ($isOrder) { $icon = 'fa-shopping-bag'; $themeColor = '#198754'; }
                                elseif ($isUrgent) { $icon = 'fa-exclamation-triangle'; $themeColor = '#ffc107'; }
                            @endphp
                            
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background: {{ $themeColor }}20; color: {{ $themeColor }}; font-size: 1.2rem;">
                                    <i class="fas {{ $icon }}"></i>
                                </div>
                                <h1 class="h3 fw-bold text-dark mb-0">{{ $typeLabel }}</h1>
                            </div>
                            <p class="text-muted mb-0 d-flex align-items-center gap-2">
                                <i class="far fa-clock"></i>
                                Received {{ $notification->created_at->format('M d, Y') }} at {{ $notification->created_at->format('H:i A') }}
                                <span class="badge bg-light text-dark border ms-2 rounded-pill">{{ $notification->created_at->diffForHumans() }}</span>
                            </p>
                        </div>
                        
                        <div class="dropdown">
                            <button class="btn btn-light rounded-circle" data-bs-toggle="dropdown" style="width: 40px; height: 40px;">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg" style="border-radius: 12px;">
                                <li>
                                    <form method="POST" action="{{ route('admin.notifications.delete', $notification->id) }}" onsubmit="return confirm('Permanently delete this notification?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" data-no-global-handler class="dropdown-item text-danger d-flex align-items-center gap-2 py-2">
                                            <i class="fas fa-trash-alt"></i> Delete Forever
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="notification-body bg-white p-4 rounded-4 border shadow-xs mb-4" style="font-size: 1.1rem; line-height: 1.7; color: #444;">
                        {!! nl2br(e($notification->message)) !!}
                    </div>

                    <div class="d-flex flex-wrap gap-3 mt-4">
                        @php
                            $url = '#';
                            if (str_contains(strtolower($notification->message), 'task')) {
                                preg_match('/ID[:\s]+(\d+)/i', $notification->message, $matches);
                                if (isset($matches[1])) $url = route('admin.design-tasks.show', $matches[1]);
                            }
                        @endphp
                        
                        @if($url !== '#')
                        <a href="{{ $url }}" class="btn btn-primary px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" style="border-radius: 12px;">
                            <i class="fas fa-external-link-alt"></i>
                            View Related Task
                        </a>
                        @endif

                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary px-4 py-2 fw-bold" style="border-radius: 12px;">
                            <i class="fas fa-list me-2"></i>
                            All Notifications
                        </a>
                    </div>
                </div>
            </div>

            <!-- Context Card -->
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-4">Metadata Analysis</h5>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-4">
                                <label class="small text-uppercase fw-bold text-muted mb-1 d-block">Status</label>
                                @if($notification->status === 'unread')
                                    <span class="text-warning fw-bold d-flex align-items-center gap-1">
                                        <i class="fas fa-circle" style="font-size: 0.6rem;"></i> Unread
                                    </span>
                                @else
                                    <span class="text-success fw-bold d-flex align-items-center gap-1">
                                        <i class="fas fa-check-circle"></i> Read
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-4">
                                <label class="small text-uppercase fw-bold text-muted mb-1 d-block">Database ID</label>
                                <span class="fw-bold text-dark">#{{ $notification->id }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-4">
                                <label class="small text-uppercase fw-bold text-muted mb-1 d-block">Category</label>
                                <span class="fw-bold text-dark">{{ $isAlert ? 'Critical Alert' : 'Standard Info' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.shadow-xs {
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}
.rounded-4 {
    border-radius: 1rem !important;
}
.dropdown-item:hover {
    background-color: var(--bs-light);
}
</style>
@endsection
