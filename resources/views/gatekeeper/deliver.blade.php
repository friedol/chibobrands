@extends('layouts.admin')

@section('title', 'Mark Task as Delivered - Gatekeeper')

@push('styles')
<style>
    .search-bar { border-radius: 12px; border: 2px solid #e2e8f0; font-size: 14px; padding: 10px 16px 10px 44px; transition: border-color .2s; }
    .search-bar:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.1); outline: none; }
    .search-wrapper { position: relative; }
    .search-wrapper .search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 15px; pointer-events: none; }

    .task-card { border-radius: 14px; border: 1.5px solid #e2e8f0; background: #fff; transition: box-shadow .2s, border-color .2s; }
    .task-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.09); border-color: #4f46e5; }

    .badge-super { background: #fef3c7; color: #92400e; font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
    .badge-delivered { background: #d1fae5; color: #065f46; font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 20px; }

    .deliver-btn { background: #4f46e5; color: #fff; border: none; border-radius: 10px; padding: 8px 20px; font-size: 12px; font-weight: 700; cursor: pointer; transition: background .2s, transform .1s; }
    .deliver-btn:hover { background: #4338ca; transform: scale(1.03); }
    .deliver-btn:active { transform: scale(.97); }

    .meta-row { font-size: 12px; color: #64748b; }
    .meta-row i { width: 14px; text-align: center; }

    .empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
    .empty-state i { font-size: 3rem; margin-bottom: 12px; display: block; opacity: .3; }

    .counter-pill { background: #4f46e5; color: #fff; border-radius: 20px; font-size: 10px; font-weight: 700; padding: 2px 8px; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 pt-2 pb-5">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-box-open me-2" style="color:#4f46e5;"></i>Mark Task as Delivered</h5>
        </div>
        <a href="{{ route('gatekeeper.dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3" style="font-size:12px;">
            <i class="fas fa-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-3" role="alert" style="font-size:13px;">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-3" role="alert" style="font-size:13px;">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Search --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('gatekeeper.deliver') }}" id="searchForm">
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="q" id="searchInput" class="form-control search-bar w-100"
                           placeholder="Search by task code, customer name or phone number…"
                           value="{{ $q }}" autocomplete="off" autofocus>
                </div>
            </form>
        </div>
    </div>

    {{-- Results --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="fw-bold mb-0 text-dark" style="font-size:13px;">
            Tasks Ready for Delivery
            <span class="counter-pill ms-1">{{ $tasks->count() }}</span>
        </h6>
        @if($q)
        <a href="{{ route('gatekeeper.deliver') }}" class="x-small text-muted text-decoration-none">
            <i class="fas fa-times me-1"></i>Clear search
        </a>
        @endif
    </div>

    @forelse($tasks as $task)
    <div class="task-card p-3 mb-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">

        {{-- Task Info --}}
        <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="fw-bold text-dark" style="font-size:14px;">{{ $task->task_code }}</span>
                <span class="badge-super">Ready for Delivery</span>
            </div>
            <div class="fw-semibold mb-1" style="font-size:13px;">{{ $task->title }}</div>
            <div class="meta-row d-flex flex-wrap gap-3 mt-1">
                <span><i class="fas fa-user"></i> {{ $task->customer->name ?? '—' }}</span>
                <span><i class="fas fa-phone"></i> {{ $task->customer->phone ?? '—' }}</span>
                <span><i class="fas fa-layer-group"></i> {{ $task->department->name ?? '—' }}</span>
                <span><i class="fas fa-calendar-alt"></i> {{ $task->created_at->format('M d, Y') }}</span>
                @if($task->balance > 0)
                <span class="text-danger fw-bold"><i class="fas fa-exclamation-circle"></i> Balance: TZS {{ number_format($task->balance) }}</span>
                @else
                <span class="text-success fw-bold"><i class="fas fa-check-circle"></i> Fully Paid</span>
                @endif
            </div>
        </div>

        {{-- Action --}}
        <div class="flex-shrink-0">
            <form method="POST" action="{{ route('gatekeeper.tasks.mark-delivered', $task) }}"
                  onsubmit="return confirmDeliver(this, '{{ addslashes($task->task_code) }}', '{{ addslashes($task->customer->name ?? '') }}')">
                @csrf
                <button type="submit" class="deliver-btn">
                    <i class="fas fa-check me-1"></i>Mark as Delivered
                </button>
            </form>
        </div>

    </div>
    @empty
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body empty-state">
            <i class="fas fa-box-open"></i>
            @if($q)
                <p class="mb-1 fw-semibold" style="font-size:14px;">No tasks found for "{{ $q }}"</p>
                <p class="x-small">Try searching by task code (e.g. <strong>CHB-0042</strong>), customer name, or phone number.</p>
            @else
                <p class="mb-1 fw-semibold" style="font-size:14px;">No tasks ready for delivery right now</p>
                <p class="x-small">Tasks with status <strong>Super Completed</strong> will appear here.</p>
            @endif
        </div>
    </div>
    @endforelse

</div>
@endsection

@push('scripts')
<script>
function confirmDeliver(form, taskCode, customerName) {
    return confirm('Mark task ' + taskCode + ' for "' + customerName + '" as DELIVERED?\n\nThis will change the status to Delivered and cannot be undone easily.');
}

// Auto-submit search form on typing (with debounce)
(function () {
    const input = document.getElementById('searchInput');
    const form  = document.getElementById('searchForm');
    if (!input || !form) return;
    let timer;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () { form.submit(); }, 450);
    });
})();
</script>
@endpush
