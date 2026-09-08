@extends('layouts.admin')

@section('page-title', 'Notifications')

@push('styles')
<style>
    .notif-row {
        display: flex;
        align-items: flex-start;
        gap: 0.875rem;
        padding: 0.875rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s;
        position: relative;
    }

    .notif-row:last-child { border-bottom: none; }

    .notif-row:hover { background: #f8faff; }

    .notif-row.is-unread { background: #f0f6ff; }
    .notif-row.is-unread:hover { background: #e8f0ff; }

    .unread-dot {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: #0d6efd;
        border-radius: 0 2px 2px 0;
    }

    .notif-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .ni-task     { background: #f5f3ff; color: #7c3aed; }
    .ni-order    { background: #f0fdf4; color: #16a34a; }
    .ni-delivery { background: #ecfeff; color: #0891b2; }
    .ni-alert    { background: #fffbeb; color: #d97706; }
    .ni-reg      { background: #eff6ff; color: #2563eb; }
    .ni-system   { background: #f1f5f9; color: #64748b; }

    .notif-body { flex: 1; min-width: 0; }

    .notif-title {
        font-size: 0.825rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.15rem;
    }

    .notif-msg {
        font-size: 0.78rem;
        color: #64748b;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .notif-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.35rem;
    }

    .notif-time {
        font-size: 0.7rem;
        color: #94a3b8;
    }

    .notif-sender {
        font-size: 0.7rem;
        color: #94a3b8;
    }

    .notif-actions {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        flex-shrink: 0;
    }

    .btn-notif {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        cursor: pointer;
        transition: all 0.15s;
        background: transparent;
    }

    .btn-notif-read  { color: #16a34a; }
    .btn-notif-read:hover  { background: #f0fdf4; color: #15803d; }
    .btn-notif-del   { color: #dc2626; }
    .btn-notif-del:hover   { background: #fef2f2; color: #b91c1c; }
    .btn-notif-view  { color: #2563eb; }
    .btn-notif-view:hover  { background: #eff6ff; color: #1d4ed8; }

    /* Filter bar */
    .filter-pill {
        padding: 0.35rem 0.875rem;
        border-radius: 20px;
        font-size: 0.775rem;
        font-weight: 500;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
        text-decoration: none;
        transition: all 0.15s;
        white-space: nowrap;
    }

    .filter-pill:hover { border-color: #0d6efd; color: #0d6efd; }
    .filter-pill.active { background: #0d6efd; border-color: #0d6efd; color: #fff; }

    /* Stat chips */
    .stat-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.75rem;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        font-size: 0.775rem;
    }

    .stat-chip .chip-num {
        font-weight: 700;
        font-size: 1rem;
        line-height: 1;
    }

    .empty-state { padding: 3rem 1rem; text-align: center; }
    .empty-state i { font-size: 2.5rem; color: #cbd5e0; margin-bottom: 0.75rem; display: block; }
</style>
@endpush

@section('content')
@php
    $unreadCount = auth()->user()->notifications()->where('status','unread')->count();
    $totalCount  = auth()->user()->notifications()->count();
    $readCount   = $totalCount - $unreadCount;
@endphp

<div class="container-fluid py-3">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                Notifications
                @if($unreadCount > 0)
                    <span class="badge bg-danger rounded-pill ms-1" style="font-size:0.65rem;">{{ $unreadCount }} new</span>
                @endif
            </h4>
            <p class="text-muted small mb-0">Your activity feed — tasks, orders, alerts and system events</p>
        </div>
        @if($unreadCount > 0)
        <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}">
            @csrf
            <button type="submit" data-no-global-handler class="btn btn-outline-primary btn-sm px-4">
                <i class="fas fa-check-double me-2"></i>Mark All Read
            </button>
        </form>
        @endif
    </div>

    {{-- Stats --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        <span class="stat-chip">
            <i class="fas fa-bell text-primary"></i>
            <span class="text-muted">Total</span>
            <span class="chip-num text-dark">{{ $totalCount }}</span>
        </span>
        <span class="stat-chip">
            <i class="fas fa-bolt text-danger"></i>
            <span class="text-muted">Unread</span>
            <span class="chip-num text-danger">{{ $unreadCount }}</span>
        </span>
        <span class="stat-chip">
            <i class="fas fa-check-circle text-success"></i>
            <span class="text-muted">Read</span>
            <span class="chip-num text-success">{{ $readCount }}</span>
        </span>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
        <div class="card-body py-2 px-3">
            <form method="GET" action="{{ route('admin.notifications.index') }}" class="d-flex flex-wrap align-items-center gap-2">

                {{-- Status pills --}}
                <div class="d-flex gap-1 flex-wrap">
                    <a href="{{ route('admin.notifications.index', request()->except('status', 'page')) }}"
                       class="filter-pill {{ !request('status') ? 'active' : '' }}">All</a>
                    <a href="{{ route('admin.notifications.index', array_merge(request()->except('status','page'), ['status'=>'unread'])) }}"
                       class="filter-pill {{ request('status')==='unread' ? 'active' : '' }}">
                        Unread @if($unreadCount > 0)<span class="ms-1 badge bg-danger rounded-pill" style="font-size:0.6rem;">{{ $unreadCount }}</span>@endif
                    </a>
                    <a href="{{ route('admin.notifications.index', array_merge(request()->except('status','page'), ['status'=>'read'])) }}"
                       class="filter-pill {{ request('status')==='read' ? 'active' : '' }}">Read</a>
                </div>

                <div class="vr d-none d-md-block mx-1" style="height:20px;"></div>

                {{-- Type select --}}
                <select name="type" class="form-select form-select-sm" style="width:auto; border-radius:8px; font-size:0.775rem;" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="task"         {{ request('type')==='task'         ? 'selected' : '' }}>Design Tasks</option>
                    <option value="delivery"     {{ request('type')==='delivery'     ? 'selected' : '' }}>Delivery</option>
                    <option value="order"        {{ request('type')==='order'        ? 'selected' : '' }}>Orders</option>
                    <option value="registration" {{ request('type')==='registration' ? 'selected' : '' }}>Registrations</option>
                    <option value="alert"        {{ request('type')==='alert'        ? 'selected' : '' }}>Alerts</option>
                </select>

                {{-- Sender select --}}
                <select name="sender_id" class="form-select form-select-sm" style="width:auto; border-radius:8px; font-size:0.775rem;" onchange="this.form.submit()">
                    <option value="">All Senders</option>
                    @foreach($staff as $member)
                        <option value="{{ $member->id }}" {{ request('sender_id') == $member->id ? 'selected' : '' }}>
                            {{ $member->name }}
                        </option>
                    @endforeach
                </select>

                @if(request()->anyFilled(['status','type','sender_id']))
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-secondary px-3" style="border-radius:8px; font-size:0.775rem;">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Notification List --}}
    <div class="card border-0 shadow-sm" style="border-radius:12px; overflow:hidden;">
        @forelse($notifications as $notification)
        @php
            $type = strtolower($notification->type ?? '');
            $isTask     = str_contains($type, 'task') && !str_contains($type, 'delivery');
            $isOrder    = str_contains($type, 'order');
            $isDelivery = str_contains($type, 'delivery');
            $isAlert    = str_contains($type, 'alert') || str_contains($type, 'warning');
            $isReg      = str_contains($type, 'registration');

            if ($isTask)         { $iconClass = 'fa-paint-brush'; $iconBg = 'ni-task'; }
            elseif ($isOrder)    { $iconClass = 'fa-shopping-cart'; $iconBg = 'ni-order'; }
            elseif ($isDelivery) { $iconClass = 'fa-truck'; $iconBg = 'ni-delivery'; }
            elseif ($isAlert)    { $iconClass = 'fa-exclamation-triangle'; $iconBg = 'ni-alert'; }
            elseif ($isReg)      { $iconClass = 'fa-user-plus'; $iconBg = 'ni-reg'; }
            else                 { $iconClass = 'fa-bell'; $iconBg = 'ni-system'; }

            $isUnread = $notification->status === 'unread';
            $title = ucwords(str_replace(['_','-'],' ', $notification->type ?? 'System Notification'));

            $url = '#';
            if ($notification->related_type === 'App\Models\DesignTask' && $notification->related_id) {
                $url = route('admin.design-tasks.show', $notification->related_id);
            } elseif (str_contains(strtolower($notification->message ?? ''), 'task')) {
                preg_match('/ID[:\s]+(\d+)/i', $notification->message, $m);
                if (isset($m[1])) $url = route('admin.design-tasks.show', $m[1]);
            }
        @endphp

        <div class="notif-row {{ $isUnread ? 'is-unread' : '' }}">
            @if($isUnread)<div class="unread-dot"></div>@endif

            {{-- Icon --}}
            <div class="notif-icon {{ $iconBg }}">
                <i class="fas {{ $iconClass }}"></i>
            </div>

            {{-- Body --}}
            <div class="notif-body">
                <div class="d-flex align-items-center gap-2 mb-0.5">
                    <span class="notif-title">{{ $title }}</span>
                    @if($isUnread)
                        <span class="badge bg-primary rounded-pill" style="font-size:0.6rem; padding:0.2rem 0.5rem;">New</span>
                    @endif
                </div>
                <div class="notif-msg">{{ $notification->message }}</div>
                <div class="notif-meta">
                    <span class="notif-time">
                        <i class="far fa-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                    </span>
                    @if($notification->sender)
                        <span class="notif-sender">
                            &bull; {{ $notification->sender->name }}
                            <span class="text-muted">({{ ucfirst($notification->sender->role) }})</span>
                        </span>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="notif-actions">
                @if($url !== '#')
                    <a href="{{ $url }}" class="btn-notif btn-notif-view" title="View related">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                @endif

                <a href="{{ route('admin.notifications.show', $notification->id) }}" class="btn-notif btn-notif-view" title="Open notification">
                    <i class="fas fa-eye"></i>
                </a>

                @if($isUnread)
                <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" data-no-global-handler class="btn-notif btn-notif-read" title="Mark as read">
                        <i class="fas fa-check"></i>
                    </button>
                </form>
                @endif

                <form method="POST" action="{{ route('admin.notifications.delete', $notification->id) }}"
                    class="d-inline" onsubmit="return confirm('Delete this notification?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" data-no-global-handler class="btn-notif btn-notif-del" title="Delete">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <i class="fas fa-bell-slash"></i>
            <h6 class="fw-bold text-dark">No notifications found</h6>
            <p class="text-muted small mb-3">
                @if(request()->anyFilled(['status','type','sender_id']))
                    No results match your current filters.
                @else
                    You're all caught up! New activity will appear here.
                @endif
            </p>
            @if(request()->anyFilled(['status','type','sender_id']))
                <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-primary btn-sm px-4">Clear Filters</a>
            @endif
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())
    <div class="mt-3 d-flex justify-content-center">
        {{ $notifications->links() }}
    </div>
    @endif
</div>
@endsection
