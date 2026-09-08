@extends('layouts.admin')

@section('page-title', 'Notification Detail')

@section('content')
@php
    $type = strtolower($notification->type ?? '');
    $isTask     = str_contains($type, 'task') && !str_contains($type, 'delivery');
    $isOrder    = str_contains($type, 'order');
    $isDelivery = str_contains($type, 'delivery');
    $isAlert    = str_contains($type, 'alert') || str_contains($type, 'warning');
    $isReg      = str_contains($type, 'registration');

    if ($isTask)         { $iconClass = 'fa-paint-brush'; $iconBg = '#f5f3ff'; $iconColor = '#7c3aed'; }
    elseif ($isOrder)    { $iconClass = 'fa-shopping-cart'; $iconBg = '#f0fdf4'; $iconColor = '#16a34a'; }
    elseif ($isDelivery) { $iconClass = 'fa-truck'; $iconBg = '#ecfeff'; $iconColor = '#0891b2'; }
    elseif ($isAlert)    { $iconClass = 'fa-exclamation-triangle'; $iconBg = '#fffbeb'; $iconColor = '#d97706'; }
    elseif ($isReg)      { $iconClass = 'fa-user-plus'; $iconBg = '#eff6ff'; $iconColor = '#2563eb'; }
    else                 { $iconClass = 'fa-bell'; $iconBg = '#f1f5f9'; $iconColor = '#64748b'; }

    $title = ucwords(str_replace(['_','-'],' ', $notification->type ?? 'System Notification'));
    $isUnread = $notification->status === 'unread';

    $url = '#';
    if ($notification->related_type === 'App\Models\DesignTask' && $notification->related_id) {
        $url = route('admin.design-tasks.show', $notification->related_id);
    } elseif (str_contains(strtolower($notification->message ?? ''), 'task')) {
        preg_match('/ID[:\s]+(\d+)/i', $notification->message, $m);
        if (isset($m[1])) $url = route('admin.design-tasks.show', $m[1]);
    }
@endphp

<div class="container-fluid py-3">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0" style="font-size:0.8rem;">
            <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}" class="text-decoration-none">Notifications</a></li>
            <li class="breadcrumb-item active">{{ $title }}</li>
        </ol>
    </nav>

    <div class="row g-3">

        {{-- Main detail card --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius:12px; overflow:hidden;">

                {{-- Card header --}}
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:40px;height:40px;border-radius:10px;background:{{ $iconBg }};color:{{ $iconColor }};display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">
                            <i class="fas {{ $iconClass }}"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold text-dark" style="font-size:0.95rem;">{{ $title }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">
                                <i class="far fa-clock me-1"></i>
                                {{ $notification->created_at->format('M d, Y · H:i') }}
                                &bull; {{ $notification->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            @if($isUnread)
                                <span class="badge bg-primary rounded-pill" style="font-size:0.65rem;">Unread</span>
                            @else
                                <span class="badge bg-success rounded-pill" style="font-size:0.65rem;">Read</span>
                            @endif

                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-flex align-items-center justify-content-center"
                                    style="width:30px;height:30px;" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v" style="font-size:0.7rem;"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow" style="border-radius:10px;font-size:0.8rem;">
                                    <li>
                                        <form method="POST" action="{{ route('admin.notifications.delete', $notification->id) }}"
                                            onsubmit="return confirm('Delete this notification?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" data-no-global-handler class="dropdown-item text-danger d-flex align-items-center gap-2 py-2">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Message body --}}
                <div class="card-body px-4 py-3">
                    <div class="p-3 bg-light rounded-3" style="font-size:0.875rem;line-height:1.7;color:#334155;white-space:pre-wrap;">{{ $notification->message }}</div>
                </div>

                {{-- Footer actions --}}
                <div class="card-footer bg-white border-top py-3 px-4 d-flex gap-2 flex-wrap">
                    @if($url !== '#')
                        <a href="{{ $url }}" class="btn btn-primary btn-sm px-4">
                            <i class="fas fa-external-link-alt me-2"></i>View Related
                        </a>
                    @endif

                    @if($isUnread)
                        <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit" data-no-global-handler class="btn btn-outline-success btn-sm px-4">
                                <i class="fas fa-check me-2"></i>Mark as Read
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary btn-sm px-4">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>

        {{-- Sidebar meta --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border-radius:12px;">
                <div class="card-header bg-white border-bottom py-2 px-4">
                    <span class="fw-bold text-dark" style="font-size:0.85rem;">Details</span>
                </div>
                <div class="card-body p-0">

                    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Status</span>
                        @if($isUnread)
                            <span class="badge bg-primary rounded-pill" style="font-size:0.65rem;">Unread</span>
                        @else
                            <span class="badge bg-success rounded-pill" style="font-size:0.65rem;">Read</span>
                        @endif
                    </div>

                    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Type</span>
                        <span class="fw-semibold text-dark small">{{ $title }}</span>
                    </div>

                    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Received</span>
                        <span class="fw-semibold text-dark small">{{ $notification->created_at->format('M d, Y') }}</span>
                    </div>

                    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Time</span>
                        <span class="fw-semibold text-dark small">{{ $notification->created_at->format('H:i') }}</span>
                    </div>

                    @if($notification->sender)
                    <div class="px-4 py-3 border-bottom">
                        <div class="text-muted small mb-1">Sent by</div>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:28px;height:28px;border-radius:7px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:0.7rem;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark" style="font-size:0.8rem;">{{ $notification->sender->name }}</div>
                                <div class="text-muted" style="font-size:0.7rem;">{{ ucfirst($notification->sender->role) }}</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="px-4 py-3">
                        <div class="text-muted small mb-1">Notification ID</div>
                        <span class="text-dark fw-semibold small">#{{ $notification->id }}</span>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection
