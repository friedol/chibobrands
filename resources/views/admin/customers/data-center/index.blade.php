@extends('layouts.admin')

@section('title', 'Customer Follow-up Center')

@push('styles')
<style>
    /* Stat cards: use global .dash-stat-card from layout */

    /* ── Filter Tabs ── */
    .fup-tabs { display: flex; gap: 5px; flex-wrap: nowrap; overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
    .fup-tabs::-webkit-scrollbar { display: none; }
    .fup-tab {
        padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .04em; border: 1.5px solid #e2e8f0;
        color: #64748b; background: #fff; text-decoration: none; transition: all .15s;
        white-space: nowrap; flex-shrink: 0;
    }
    .fup-tab:hover { border-color: #6366f1; color: #6366f1; }
    .fup-tab.active { background: #6366f1; border-color: #6366f1; color: #fff; }
    .fup-tab.active-danger { background: #ef4444; border-color: #ef4444; color: #fff; }
    .fup-tab.active-warning { background: #f59e0b; border-color: #f59e0b; color: #fff; }
    .fup-tab.active-info { background: #3b82f6; border-color: #3b82f6; color: #fff; }

    /* ── Status Badges ── */
    .status-pill {
        display: inline-block; padding: 3px 10px; border-radius: 20px;
        font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
    }

    /* ── Desktop Table ── */
    #fup-desktop { display: block; }
    #fup-mobile  { display: none; }

    /* ── Mobile Cards ── */
    .fup-card {
        background: #fff; border-radius: 14px; border: 1px solid #f0f0f0;
        box-shadow: 0 1px 5px rgba(0,0,0,.05); padding: 14px 16px; margin-bottom: 10px;
    }
    .fup-card-avatar {
        width: 40px; height: 40px; border-radius: 10px; object-fit: cover; flex-shrink: 0;
    }
    .fup-card-name { font-size: 13px; font-weight: 700; color: #1e293b; }
    .fup-card-meta { font-size: 11px; color: #64748b; }
    .fup-card-row { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; padding-top: 10px; border-top: 1px solid #f1f5f9; flex-wrap: wrap; gap: 6px; }
    .fup-card-item { font-size: 10px; color: #64748b; }
    .fup-card-item span { display: block; font-size: 12px; font-weight: 700; color: #1e293b; }

    /* ── Priority Bar ── */
    .priority-bar { height: 4px; border-radius: 2px; background: #e2e8f0; flex: 1; overflow: hidden; }
    .priority-bar-fill { height: 100%; border-radius: 2px; background: linear-gradient(90deg,#6366f1,#8b5cf6); }

    @media (max-width: 767.98px) {
        #fup-desktop { display: none !important; }
        #fup-mobile  { display: block !important; }
        .fup-search-wrap { width: 100% !important; }
        .fup-header-row { flex-direction: column; align-items: stretch !important; gap: 10px; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-3 py-md-4">

    {{-- ── Header ── --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-0">Customer Follow-up Center</h4>
        </div>
        <div class="fup-tabs">
            <a href="{{ route('admin.customer-data-center.index', ['status' => 'due']) }}"
               class="fup-tab {{ $statusFilter === 'due' ? 'active-danger' : '' }}">
               <i class="fas fa-exclamation-circle me-1"></i>Overdue
            </a>
            <a href="{{ route('admin.customer-data-center.index', ['status' => 'upcoming']) }}"
               class="fup-tab {{ $statusFilter === 'upcoming' ? 'active-warning' : '' }}">
               <i class="fas fa-clock me-1"></i>Upcoming
            </a>
            <a href="{{ route('admin.customer-data-center.index', ['status' => 'new']) }}"
               class="fup-tab {{ $statusFilter === 'new' ? 'active-info' : '' }}">
               <i class="fas fa-user-plus me-1"></i>New Customers
            </a>
            <a href="{{ route('admin.customer-data-center.index') }}"
               class="fup-tab {{ !in_array($statusFilter, ['due','upcoming','new']) ? 'active' : '' }}">
               All
            </a>
        </div>
    </div>

    {{-- ── Specific Filters (Date & Customer Type) ── --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background:#f8fafc; border:1px solid #e2e8f0;">
        <div class="card-body p-3">
            <form action="{{ route('admin.customer-data-center.index') }}" method="GET" class="row g-2 align-items-center">
                {{-- Keep current status filter --}}
                <input type="hidden" name="status" value="{{ $statusFilter }}">

                <div class="col-6 col-md-3">
                    <label class="form-label fw-bold mb-1 text-muted text-uppercase" style="font-size:10px; letter-spacing:.05em;">Customer Type</label>
                    <select name="type" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="all" {{ $typeFilter === 'all' ? 'selected' : '' }}>All Types</option>
                        <option value="new" {{ $typeFilter === 'new' ? 'selected' : '' }}>New (0-1 purchases)</option>
                        <option value="repeated" {{ $typeFilter === 'repeated' ? 'selected' : '' }}>Repeated (>1 purchases)</option>
                    </select>
                </div>
                
                <div class="col-6 col-md-3">
                    <label class="form-label fw-bold mb-1 text-muted text-uppercase" style="font-size:10px; letter-spacing:.05em;">Specific Follow-up Date</label>
                    <input type="date" name="date" class="form-control form-control-sm rounded-3" value="{{ $dateFilter }}" onchange="this.form.submit()">
                </div>
                
                <div class="col-12 col-md-auto ms-md-auto d-flex gap-2 align-self-end mt-2 mt-md-0">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">Apply Filters</button>
                    <a href="{{ route('admin.customer-data-center.index', ['status' => $statusFilter]) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Stat Cards ── --}}
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="dash-stat-card hover-lift" style="border-color:rgba(220,53,69,0.3);background:linear-gradient(150deg,rgba(220,53,69,0.06) 0%,#fff 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="dsc-icon" style="background:rgba(220,53,69,0.13);color:#dc3545;"><i class="fas fa-exclamation-triangle"></i></div>
                    <span class="dsc-trend">— Urgent</span>
                </div>
                <div class="dsc-value">{{ number_format($stats['overdue']) }}</div>
                <div class="dsc-label">Overdue Follow-ups</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="dash-stat-card hover-lift" style="border-color:rgba(255,193,7,0.3);background:linear-gradient(150deg,rgba(255,193,7,0.06) 0%,#fff 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="dsc-icon" style="background:rgba(255,193,7,0.13);color:#ffc107;"><i class="fas fa-clock"></i></div>
                    <span class="dsc-trend">— Optimal timing</span>
                </div>
                <div class="dsc-value">{{ number_format($stats['due_today']) }}</div>
                <div class="dsc-label">Due Today</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="dash-stat-card hover-lift" style="border-color:rgba(13,110,253,0.3);background:linear-gradient(150deg,rgba(13,110,253,0.06) 0%,#fff 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="dsc-icon" style="background:rgba(13,110,253,0.13);color:#0d6efd;"><i class="fas fa-calendar-alt"></i></div>
                    <span class="dsc-trend">— Next 3 days</span>
                </div>
                <div class="dsc-value">{{ number_format($stats['upcoming']) }}</div>
                <div class="dsc-label">Upcoming</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="dash-stat-card hover-lift" style="border-color:rgba(25,135,84,0.3);background:linear-gradient(150deg,rgba(25,135,84,0.06) 0%,#fff 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="dsc-icon" style="background:rgba(25,135,84,0.13);color:#198754;"><i class="fas fa-users"></i></div>
                    <span class="dsc-trend">— With patterns</span>
                </div>
                <div class="dsc-value">{{ number_format($stats['total_customers']) }}</div>
                <div class="dsc-label">Total Analyzed</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="dash-stat-card hover-lift" style="border-color:rgba(99,102,241,0.3);background:linear-gradient(150deg,rgba(99,102,241,0.06) 0%,#fff 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="dsc-icon" style="background:rgba(99,102,241,0.13);color:#6366f1;"><i class="fas fa-user-plus"></i></div>
                    <span class="dsc-trend">— Processed</span>
                </div>
                <div class="dsc-value">{{ number_format($stats['weekly_new']) }}</div>
                <div class="dsc-label">New This Week</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="dash-stat-card hover-lift" style="border-color:rgba(139,92,246,0.3);background:linear-gradient(150deg,rgba(139,92,246,0.06) 0%,#fff 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="dsc-icon" style="background:rgba(139,92,246,0.13);color:#8b5cf6;"><i class="fas fa-redo"></i></div>
                    <span class="dsc-trend">— Active</span>
                </div>
                <div class="dsc-value">{{ number_format($stats['weekly_repeated']) }}</div>
                <div class="dsc-label">Repeated This Week</div>
            </div>
        </div>
    </div>

    {{-- ── Main Card ── --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 py-3 px-3 px-md-4">
            <div class="fup-header-row d-flex align-items-center justify-content-between gap-2">
                <div>
                    <h6 class="fw-bold mb-0">Follow-up Intelligence List</h6>
                    <p class="text-muted mb-0" style="font-size:11px;">Prioritised outreach based on purchase frequency</p>
                </div>
                <div class="fup-search-wrap" style="width:240px;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 border-light"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="customerSearch" class="form-control bg-light border-start-0 border-light"
                               placeholder="Search customer…" style="font-size:12px;">
                    </div>
                </div>
            </div>
        </div>

        {{-- Desktop Table --}}
        <div id="fup-desktop" class="table-responsive">
            <table class="table align-middle mb-0 table-hover" id="followUpTable">
                <thead style="background:#f8fafc;">
                    <tr style="font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;">
                        <th class="ps-4 py-3 border-0 fw-700">Customer</th>
                        <th class="py-3 border-0 fw-700">Status</th>
                        <th class="py-3 border-0 fw-700">Avg Cycle</th>
                        <th class="py-3 border-0 fw-700">Next Follow-up</th>
                        <th class="py-3 border-0 fw-700">Priority</th>
                        <th class="pe-4 py-3 border-0 fw-700 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($customers as $customer)
                    @php
                        $nextDate = $customer->effective_follow_up_date;
                        $avatarSrc = !empty($customer->profile_image)
                            ? asset('storage/'.$customer->profile_image)
                            : asset('img/avatars/placeholder.png');
                    @endphp
                    <tr class="border-bottom border-light fup-row">
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $avatarSrc }}" alt="{{ $customer->name }}"
                                     class="rounded-circle" style="width:40px;height:40px;object-fit:cover;"
                                     onerror="this.src='{{ asset('img/avatars/placeholder.png') }}'">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size:13px;">{{ $customer->name }}</div>
                                    <div style="font-size:11px;color:#64748b;">
                                        <i class="fas fa-phone-alt me-1"></i>{{ $customer->phone }}
                                        @if($customer->company_name)
                                            &nbsp;·&nbsp;<span class="text-primary">{{ $customer->company_name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $sc = $customer->status_color;
                                $colorMap = ['danger'=>'#fef2f2,#ef4444','warning'=>'#fffbeb,#f59e0b','success'=>'#f0fdf4,#22c55e','primary'=>'#eff6ff,#3b82f6','secondary'=>'#f8fafc,#64748b'];
                                [$bg,$fg] = explode(',', $colorMap[$sc] ?? '#f8fafc,#64748b');
                            @endphp
                            <span class="status-pill" style="background:{{ $bg }};color:{{ $fg }};">
                                {{ $customer->follow_up_status }}
                            </span>
                            <div class="mt-1">
                                <span class="badge bg-{{ $customer->customer_type_badge }}" style="font-size: 9px; padding: 3px 6px;">
                                    {{ $customer->customer_type }}
                                </span>
                            </div>
                        </td>
                        <td>
                            @if($customer->avg_reorder_interval)
                                <div class="fw-bold" style="font-size:13px;">{{ number_format($customer->avg_reorder_interval,1) }}d</div>
                                <div style="font-size:10px;color:#94a3b8;">avg frequency</div>
                            @else
                                <span style="font-size:11px;color:#94a3b8;">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($nextDate)
                                <div class="fw-bold {{ $nextDate->isPast() ? 'text-danger' : 'text-dark' }}" style="font-size:13px;">
                                    {{ $nextDate->format('M d, Y') }}
                                </div>
                                <div style="font-size:10px;color:#94a3b8;">
                                    {{ $nextDate->diffForHumans() }}
                                    @if($customer->manual_follow_up_date && $customer->manual_follow_up_date->isFuture())
                                        <i class="fas fa-hand-pointer text-info ms-1" title="Manually scheduled"></i>
                                    @endif
                                </div>
                            @else
                                <span style="font-size:11px;color:#94a3b8;">No data</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2" style="min-width:90px;">
                                <div class="priority-bar">
                                    <div class="priority-bar-fill" style="width:{{ min(100,$customer->priority_ranking/3) }}%"></div>
                                </div>
                                <span class="fw-bold" style="font-size:11px;">#{{ $customer->priority_ranking }}</span>
                            </div>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('admin.customer-data-center.show', $customer) }}"
                                   class="btn btn-sm btn-light rounded-3 border" style="font-size:11px;" title="View Insights">
                                    <i class="fas fa-chart-line text-primary"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-light rounded-3 border" style="font-size:11px;"
                                        data-bs-toggle="modal" data-bs-target="#followUpModal{{ $customer->id }}" title="Log Contact">
                                    <i class="fab fa-whatsapp text-success"></i>
                                </button>
                                <a href="{{ route('admin.customers.show', $customer->id) }}"
                                   class="btn btn-sm btn-light rounded-3 border" style="font-size:11px;" title="Full Profile">
                                    <i class="fas fa-user text-muted"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success opacity-25 mb-3 d-block"></i>
                            <div class="fw-bold text-dark">All clear!</div>
                            <p class="text-muted small mb-3">No customers need attention for this filter.</p>
                            <a href="{{ route('admin.customer-data-center.index') }}" class="btn btn-sm btn-primary rounded-pill px-4">View All</a>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div id="fup-mobile" class="p-3">
            @forelse($customers as $customer)
                @php
                    $nextDate = $customer->effective_follow_up_date;
                    $avatarSrc = !empty($customer->profile_image)
                        ? asset('storage/'.$customer->profile_image)
                        : asset('img/avatars/placeholder.png');
                    $sc = $customer->status_color;
                    $colorMap = ['danger'=>'#fef2f2,#ef4444','warning'=>'#fffbeb,#f59e0b','success'=>'#f0fdf4,#22c55e','primary'=>'#eff6ff,#3b82f6','secondary'=>'#f8fafc,#64748b'];
                    [$bg,$fg] = explode(',', $colorMap[$sc] ?? '#f8fafc,#64748b');
                @endphp
                <div class="fup-card fup-row">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $avatarSrc }}" alt="{{ $customer->name }}"
                             class="fup-card-avatar rounded-circle"
                             onerror="this.src='{{ asset('img/avatars/placeholder.png') }}'">
                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <div class="fup-card-name text-truncate">{{ $customer->name }}</div>
                                <div class="d-flex gap-1 flex-shrink-0 align-items-center">
                                    <span class="badge bg-{{ $customer->customer_type_badge }}" style="font-size: 8px; padding: 2px 4px;">{{ $customer->customer_type }}</span>
                                    <span class="status-pill" style="background:{{ $bg }};color:{{ $fg }};">
                                        {{ $customer->follow_up_status }}
                                    </span>
                                </div>
                            </div>
                            <div class="fup-card-meta">
                                <i class="fas fa-phone-alt me-1"></i>{{ $customer->phone }}
                                @if($customer->company_name)
                                    &nbsp;·&nbsp;{{ $customer->company_name }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="fup-card-row">
                        <div class="fup-card-item">
                            Avg Cycle
                            <span>{{ $customer->avg_reorder_interval ? number_format($customer->avg_reorder_interval,1).'d' : '—' }}</span>
                        </div>
                        <div class="fup-card-item">
                            Next Follow-up
                            <span class="{{ $nextDate && $nextDate->isPast() ? 'text-danger' : '' }}">
                                {{ $nextDate ? $nextDate->format('M d, Y') : '—' }}
                            </span>
                        </div>
                        <div class="fup-card-item">
                            Priority
                            <span>#{{ $customer->priority_ranking }}</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-2 pt-2 border-top" style="border-color:#f1f5f9!important;">
                        <a href="{{ route('admin.customer-data-center.show', $customer) }}"
                           class="btn btn-sm btn-light border flex-fill" style="font-size:11px;">
                            <i class="fas fa-chart-line text-primary me-1"></i>Insights
                        </a>
                        <button type="button" class="btn btn-sm btn-light border flex-fill" style="font-size:11px;"
                                data-bs-toggle="modal" data-bs-target="#followUpModal{{ $customer->id }}">
                            <i class="fab fa-whatsapp text-success me-1"></i>Contact
                        </button>
                        <a href="{{ route('admin.customers.show', $customer->id) }}"
                           class="btn btn-sm btn-light border flex-fill" style="font-size:11px;">
                            <i class="fas fa-user text-muted me-1"></i>Profile
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success opacity-25 mb-3 d-block"></i>
                    <div class="fw-bold text-dark">All clear!</div>
                    <p class="text-muted small mb-3">No customers need attention for this filter.</p>
                    <a href="{{ route('admin.customer-data-center.index') }}" class="btn btn-sm btn-primary rounded-pill px-4">View All</a>
                </div>
            @endforelse
        </div>

        @if($customers->hasPages())
        <div class="card-footer bg-white border-0 py-3 px-3 px-md-4">
            {{ $customers->links() }}
        </div>
        @endif
    </div>
</div>

{{-- ── Contact Modals ── --}}
@foreach($customers as $customer)
<div class="modal fade" id="followUpModal{{ $customer->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fab fa-whatsapp text-success me-2"></i>Log Contact — {{ $customer->name }}
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.customer-data-center.follow-up.store', $customer) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-3" style="background:#f0fdf4;">
                        <div style="width:42px;height:42px;background:#22c55e;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem;flex-shrink:0;">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark" style="font-size:13px;">{{ $customer->name }}</div>
                            <div style="font-size:11px;color:#64748b;"><i class="fas fa-phone-alt me-1"></i>{{ $customer->phone }}</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:11px;text-transform:uppercase;color:#64748b;">Action Channel</label>
                        <select name="action" class="form-select form-select-sm rounded-3">
                            <option value="WhatsApp">WhatsApp Business</option>
                            <option value="Phone Call">Direct Phone Call</option>
                            <option value="Email">Email</option>
                            <option value="In Person">Face-to-Face</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:11px;text-transform:uppercase;color:#64748b;">Notes</label>
                        <textarea name="notes" class="form-control form-control-sm rounded-3" rows="3"
                                  placeholder="Brief summary of the outcome…"></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold" style="font-size:11px;text-transform:uppercase;color:#64748b;">Override Next Follow-up Date</label>
                        <input type="date" name="next_follow_up_date" class="form-control form-control-sm rounded-3">
                        <p style="font-size:10px;color:#94a3b8;margin-top:5px;margin-bottom:0;">
                            <i class="fas fa-info-circle me-1"></i>Overrides smart prediction if set.
                        </p>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <div class="d-flex gap-2">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->phone) }}" target="_blank"
                           class="btn btn-sm btn-success rounded-pill px-3">
                            <i class="fab fa-whatsapp me-1"></i>Message
                        </a>
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">Save Log</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('customerSearch');
    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.fup-row').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
});
</script>
@endpush

@endsection
