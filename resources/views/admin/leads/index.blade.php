@extends('layouts.admin')

@section('title', 'Leads Management')

@push('styles')
<style>
    /* Stat cards: use global .dash-stat-card from layout */

    /* ── Quick Tabs ── */
    .lead-tabs { display:flex; gap:5px; flex-wrap:nowrap; overflow-x:auto; -webkit-overflow-scrolling:touch; scrollbar-width:none; }
    .lead-tabs::-webkit-scrollbar { display:none; }
    .lead-tab {
        padding:5px 12px; border-radius:6px; font-size:11px; font-weight:700;
        text-transform:uppercase; letter-spacing:.04em; border:1.5px solid #e2e8f0;
        color:#64748b; background:#fff; text-decoration:none; transition:all .15s;
        white-space:nowrap; flex-shrink:0;
    }
    .lead-tab:hover { border-color:#6366f1; color:#6366f1; }
    .lead-tab.t-all     { }
    .lead-tab.t-all.active     { background:#6366f1; border-color:#6366f1; color:#fff; }
    .lead-tab.t-pending.active { background:#f59e0b; border-color:#f59e0b; color:#fff; }
    .lead-tab.t-won.active     { background:#22c55e; border-color:#22c55e; color:#fff; }
    .lead-tab.t-lost.active    { background:#ef4444; border-color:#ef4444; color:#fff; }
    .lead-tab.t-hot.active     { background:#f97316; border-color:#f97316; color:#fff; }

    /* ── Status / Interest Badges ── */
    .s-pill {
        display:inline-block; padding:3px 9px; border-radius:5px;
        font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.04em;
    }
    .s-pending      { background:#fffbeb; color:#d97706; }
    .s-converted    { background:#f0fdf4; color:#16a34a; }
    .s-not_interested { background:#fef2f2; color:#dc2626; }
    .i-hot  { background:#fff7ed; color:#ea580c; }
    .i-warm { background:#fefce8; color:#ca8a04; }
    .i-cold { background:#eff6ff; color:#2563eb; }

    /* ── Table ── */
    #lead-desktop { display:block; }
    #lead-mobile  { display:none; }

    /* ── Mobile Cards ── */
    .lead-card {
        background:#fff; border-radius:12px; border:1px solid #f0f0f0;
        box-shadow:0 1px 5px rgba(0,0,0,.05); padding:13px 14px; margin-bottom:9px;
    }
    .lead-card-name { font-size:13px; font-weight:700; color:#1e293b; }
    .lead-card-meta { font-size:11px; color:#64748b; }
    .lead-card-row  { display:flex; align-items:center; flex-wrap:wrap; gap:8px; margin-top:10px; padding-top:10px; border-top:1px solid #f1f5f9; }
    .lead-card-item { font-size:10px; color:#94a3b8; }
    .lead-card-item span { display:block; font-size:11px; font-weight:700; color:#1e293b; }

    /* ── Filter card ── */
    .filter-card { border-radius:10px; border:1px solid #e2e8f0; background:#f8fafc; }

    @media (max-width:767.98px) {
        #lead-desktop { display:none !important; }
        #lead-mobile  { display:block !important; }
        .lead-header-actions { flex-wrap:wrap; }
    }
    .x-small { font-size:10px !important; }
</style>
@endpush

@section('content')
@php
    use App\Models\Lead;
    $baseQ   = auth()->user()->role === 'saler'
        ? Lead::where('assigned_seller_id', auth()->id())
        : Lead::query();
    $statTotal   = (clone $baseQ)->count();
    $statPending = (clone $baseQ)->where('status','pending')->count();
    $statWon     = (clone $baseQ)->where('status','converted')->count();
    $statLost    = (clone $baseQ)->where('status','not_interested')->count();
    $statHot     = (clone $baseQ)->where('interest_level','hot')->count();

    // Today's daily stats
    $todayLeads      = (clone $baseQ)->whereDate('created_at', today())->count();
    $todayDueCount   = (clone $baseQ)->whereDate('follow_up_date', today())->count();
    $todayFollowsDone = \App\Models\LeadFollowUp::whereDate('created_at', today())
        ->when(auth()->user()->role === 'saler', fn($q) =>
            $q->whereHas('lead', fn($lq) => $lq->where('assigned_seller_id', auth()->id()))
        )->count();

    $activeStatus   = request('status','pending');
    $activeInterest = request('interest_level','all');
    $hasFilters     = request()->anyFilled(['search','status','interest_level','date_from','date_to','saler_id']);
@endphp

<div class="container-fluid px-3 px-md-4 py-3 py-md-4">

    {{-- ── Header ── --}}
    <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-0">Leads Management</h4>
        </div>
        <div class="d-flex gap-2 flex-wrap lead-header-actions">
            <button class="btn btn-sm btn-outline-secondary rounded-2 fw-bold px-3" type="button"
                    data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                <i class="fas fa-sliders-h me-1"></i>Filter
                @if($hasFilters)<span class="badge bg-primary ms-1">On</span>@endif
            </button>
            <x-report-export-menu
                :print-url="route('admin.leads.print', request()->all())"
                :pdf-url="route('admin.leads.pdf', request()->all())"
                :excel-url="route('admin.leads.excel', request()->all())"
            />
            @if(in_array(auth()->user()->role, ['admin','super_admin','saler']))
                <button class="btn btn-sm btn-primary rounded-2 fw-bold px-3 shadow-sm"
                        data-bs-toggle="modal" data-bs-target="#addLeadModal" data-no-global-handler>
                    <i class="fas fa-plus me-1"></i>New Lead
                </button>
                <button class="btn btn-sm btn-success rounded-2 fw-bold px-3 shadow-sm"
                        data-bs-toggle="modal" data-bs-target="#messageCustomersModal" data-no-global-handler>
                    <i class="fab fa-whatsapp me-1"></i>Message
                </button>
            @endif
        </div>
    </div>

    {{-- ── Stat Cards ── --}}
    <div class="row g-2 mb-4">
        <div class="col-6 col-md-4 col-lg">
            <div class="cust-stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="cust-stat-icon bg-primary-subtle text-primary"><i class="fas fa-layer-group"></i></div>
                    <span class="cust-stat-sub">All leads</span>
                </div>
                <div class="cust-stat-val text-dark">{{ number_format($statTotal) }}</div>
                <div class="cust-stat-lbl">Total Leads</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="cust-stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="cust-stat-icon bg-warning-subtle text-warning"><i class="fas fa-hourglass-half"></i></div>
                    <span class="cust-stat-sub">In progress</span>
                </div>
                <div class="cust-stat-val text-warning">{{ number_format($statPending) }}</div>
                <div class="cust-stat-lbl">Pending</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="cust-stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="cust-stat-icon bg-success-subtle text-success"><i class="fas fa-trophy"></i></div>
                    <span class="cust-stat-sub">Converted</span>
                </div>
                <div class="cust-stat-val text-success">{{ number_format($statWon) }}</div>
                <div class="cust-stat-lbl">Won</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="cust-stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="cust-stat-icon bg-danger-subtle text-danger"><i class="fas fa-times-circle"></i></div>
                    <span class="cust-stat-sub">Not interested</span>
                </div>
                <div class="cust-stat-val text-danger">{{ number_format($statLost) }}</div>
                <div class="cust-stat-lbl">Lost</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="cust-stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="cust-stat-icon" style="background:#ffedd5;color:#ea580c;"><i class="fas fa-fire"></i></div>
                    <span class="cust-stat-sub">High interest</span>
                </div>
                <div class="cust-stat-val" style="color:#ea580c;">{{ number_format($statHot) }}</div>
                <div class="cust-stat-lbl">Hot Leads</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="cust-stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="cust-stat-icon bg-info-subtle text-info"><i class="fas fa-plus"></i></div>
                    <span class="cust-stat-sub">Registered</span>
                </div>
                <div class="cust-stat-val text-info">{{ number_format($todayLeads) }}</div>
                <div class="cust-stat-lbl">Today's New Leads</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="cust-stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="cust-stat-icon bg-success-subtle text-success"><i class="fas fa-check-double"></i></div>
                    <span class="cust-stat-sub">Contacted</span>
                </div>
                <div class="cust-stat-val text-success">{{ number_format($todayFollowsDone) }}</div>
                <div class="cust-stat-lbl">Today's Follow-Ups</div>
            </div>
        </div>
    </div>

    {{-- ── Today's Activity Bar ── --}}
    <div class="d-flex align-items-center gap-2 flex-wrap mb-3 px-1">
        <span class="fw-bold x-small text-uppercase text-muted me-1" style="letter-spacing:.06em;">Today:</span>
        <a href="{{ route('admin.leads.index', array_merge(request()->except(['date_from','date_to','page']), ['date_from'=>today()->format('Y-m-d'),'date_to'=>today()->format('Y-m-d')])) }}"
           class="badge rounded-pill px-3 py-2 text-decoration-none"
           style="background:#e0e7ff;color:#4338ca;font-size:11px;font-weight:700;">
            <i class="fas fa-plus me-1"></i>{{ $todayLeads }} New Leads
        </a>
        <a href="{{ route('admin.leads.overdue', ['filter'=>'today']) }}"
           class="badge rounded-pill px-3 py-2 text-decoration-none"
           style="background:{{ $todayDueCount > 0 ? '#fef9c3' : '#f1f5f9' }};color:{{ $todayDueCount > 0 ? '#854d0e' : '#64748b' }};font-size:11px;font-weight:700;">
            <i class="fas fa-clock me-1"></i>{{ $todayDueCount }} Due Today
        </a>
        <span class="badge rounded-pill px-3 py-2"
              style="background:#dcfce7;color:#15803d;font-size:11px;font-weight:700;">
            <i class="fas fa-check me-1"></i>{{ $todayFollowsDone }} Follow-Ups Done
        </span>
    </div>

    {{-- ── Quick Filter Tabs ── --}}
    <div class="lead-tabs mb-3">
        <a href="{{ route('admin.leads.index', array_merge(request()->except(['status','page']), ['status'=>'all'])) }}"
           class="lead-tab t-all {{ $activeStatus === 'all' ? 'active' : '' }}">All</a>
        <a href="{{ route('admin.leads.index', array_merge(request()->except(['status','page']), ['status'=>'pending'])) }}"
           class="lead-tab t-pending {{ $activeStatus === 'pending' ? 'active' : '' }}"><i class="fas fa-hourglass-half me-1"></i>Pending</a>
        <a href="{{ route('admin.leads.index', array_merge(request()->except(['status','page']), ['status'=>'converted'])) }}"
           class="lead-tab t-won {{ $activeStatus === 'converted' ? 'active' : '' }}"><i class="fas fa-trophy me-1"></i>Won</a>
        <a href="{{ route('admin.leads.index', array_merge(request()->except(['status','page']), ['status'=>'not_interested'])) }}"
           class="lead-tab t-lost {{ $activeStatus === 'not_interested' ? 'active' : '' }}"><i class="fas fa-times me-1"></i>Lost</a>
        <a href="{{ route('admin.leads.index', array_merge(request()->except(['interest_level','status','page']), ['interest_level'=>'hot'])) }}"
           class="lead-tab t-hot {{ $activeInterest === 'hot' ? 'active' : '' }}"><i class="fas fa-fire me-1"></i>Hot</a>
    </div>

    {{-- ── Advanced Filter Collapse ── --}}
    <div class="collapse {{ $hasFilters ? 'show' : '' }} mb-4" id="filterCollapse">
        <div class="card filter-card border-0 shadow-sm">
            <div class="card-body p-3">
                <form action="{{ route('admin.leads.index') }}" method="GET" class="row g-2" data-no-global-handler>
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0"
                                   placeholder="Name, phone, email…" value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="all">All Stages</option>
                            <option value="pending"        {{ request('status') == 'pending'        ? 'selected' : '' }}>Pending</option>
                            <option value="converted"      {{ request('status') == 'converted'      ? 'selected' : '' }}>Won</option>
                            <option value="not_interested" {{ request('status') == 'not_interested' ? 'selected' : '' }}>Lost</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Interest</label>
                        <select name="interest_level" class="form-select form-select-sm">
                            <option value="all">All Levels</option>
                            <option value="cold" {{ request('interest_level') == 'cold' ? 'selected' : '' }}>Cold</option>
                            <option value="warm" {{ request('interest_level') == 'warm' ? 'selected' : '' }}>Warm</option>
                            <option value="hot"  {{ request('interest_level') == 'hot'  ? 'selected' : '' }}>Hot</option>
                        </select>
                    </div>
                    @if(in_array(auth()->user()->role, ['admin','super_admin','accountant']))
                    <div class="col-12 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Salesperson</label>
                        <select name="saler_id" class="form-select form-select-sm">
                            <option value="all">All Sellers</option>
                            @foreach($sellers as $s)
                                <option value="{{ $s->id }}" {{ request('saler_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-6 col-md-1">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-12 col-md-auto d-flex align-items-end gap-2 ms-md-auto">
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold rounded-2 flex-fill flex-md-grow-0">Apply</button>
                        <a href="{{ route('admin.leads.index') }}" class="btn btn-outline-secondary btn-sm px-3 fw-bold rounded-2 flex-fill flex-md-grow-0">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Main Card ── --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 py-3 px-3 px-md-4 d-flex align-items-center justify-content-between gap-2">
            <div>
                <h6 class="fw-bold mb-0">Pipeline</h6>
                <p class="text-muted mb-0" style="font-size:11px;">{{ $leads->total() }} lead{{ $leads->total() !== 1 ? 's' : '' }} found</p>
            </div>
            <div style="width:220px;" class="d-none d-md-block">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0 border-light"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="quickSearch" class="form-control bg-light border-start-0 border-light"
                           placeholder="Quick search…" style="font-size:12px;">
                </div>
            </div>
        </div>

        {{-- Desktop Table --}}
        <div id="lead-desktop" class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="leadTable">
                <thead style="background:#f8fafc;">
                    <tr style="font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;">
                        <th class="ps-4 py-3 border-0 fw-bold">Customer</th>
                        <th class="py-3 border-0 fw-bold">Product / Interest</th>
                        <th class="py-3 border-0 fw-bold">Assigned To</th>
                        <th class="py-3 border-0 fw-bold">Follow-up</th>
                        <th class="py-3 border-0 fw-bold">Interest</th>
                        <th class="py-3 border-0 fw-bold">Status</th>
                        <th class="py-3 border-0 fw-bold" style="max-width:180px;">Last Follow-up Note</th>
                        <th class="pe-4 py-3 border-0 fw-bold text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($leads as $lead)
                    <tr class="border-bottom border-light lead-row">
                        <td class="ps-4 py-3">
                            <div class="fw-bold text-dark" style="font-size:13px;">{{ $lead->customer_name }}</div>
                            <div style="font-size:11px;color:#64748b;"><i class="fas fa-phone-alt me-1 opacity-50"></i>{{ $lead->phone }}</div>
                        </td>
                        <td>
                            <span style="font-size:12px;color:#334155;">{{ $lead->product_requested ?: '—' }}</span>
                        </td>
                        <td style="font-size:12px;color:#64748b;">{{ $lead->seller->name ?? '—' }}</td>
                        <td>
                            @if($lead->follow_up_date)
                                @if($lead->follow_up_date->isToday())
                                    <span class="badge bg-warning text-dark fw-bold mb-1" style="font-size:9px;letter-spacing:.05em;">TODAY</span><br>
                                    <div class="fw-bold text-warning" style="font-size:12px;">{{ $lead->follow_up_date->format('d M Y') }}</div>
                                @elseif($lead->follow_up_date->isPast())
                                    <span class="badge bg-danger fw-bold mb-1" style="font-size:9px;letter-spacing:.05em;">OVERDUE</span><br>
                                    <div class="fw-bold text-danger" style="font-size:12px;">{{ $lead->follow_up_date->format('d M Y') }}</div>
                                    <div style="font-size:10px;color:#94a3b8;">{{ $lead->follow_up_date->diffForHumans() }}</div>
                                @else
                                    <div class="fw-bold text-dark" style="font-size:12px;">{{ $lead->follow_up_date->format('d M Y') }}</div>
                                    <div style="font-size:10px;color:#94a3b8;">{{ $lead->follow_up_date->diffForHumans() }}</div>
                                @endif
                            @else
                                <span style="font-size:11px;color:#94a3b8;">—</span>
                            @endif
                            @if($lead->promised_order_date)
                                @php $pd = now()->diffInDays($lead->promised_order_date, false); @endphp
                                <div class="mt-1" style="font-size:10px;">
                                    <i class="fas fa-handshake me-1 {{ $pd < 0 ? 'text-danger' : 'text-success' }}"></i>
                                    <span class="{{ $pd < 0 ? 'text-danger' : 'text-success' }} fw-bold">
                                        Promised {{ $lead->promised_order_date->format('d M') }}
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td><span class="s-pill i-{{ $lead->interest_level }}">{{ $lead->interest_level }}</span></td>
                        <td><span class="s-pill s-{{ $lead->status }}">{{ str_replace('_',' ',$lead->status) }}</span></td>
                        <td class="text-muted" style="max-width: 180px; font-size: 11px;">
                            @php
                                $lastFollow = $lead->followUps->sortByDesc('created_at')->first();
                            @endphp
                            @if($lastFollow)
                                <div class="text-dark fw-semibold" style="font-size: 11px;">{{ $lastFollow->user?->name ?? '—' }}</div>
                                <div class="text-truncate text-muted" title="{{ $lastFollow->notes }}" style="font-size: 11px; max-width: 170px;">{{ $lastFollow->notes }}</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="pe-4 text-end">
                            @if(in_array(auth()->user()->role, ['admin','super_admin']) || (auth()->user()->role === 'saler' && auth()->id() === $lead->assigned_seller_id))
                                <button class="btn btn-sm btn-primary rounded-2 px-3"
                                        data-bs-toggle="modal" data-bs-target="#manageLeadModal{{ $lead->id }}" data-no-global-handler>
                                    <i class="fas fa-edit me-1"></i>Manage
                                </button>
                            @else
                                <button class="btn btn-sm btn-light border rounded-2 px-3"
                                        data-bs-toggle="modal" data-bs-target="#manageLeadModal{{ $lead->id }}" data-no-global-handler>
                                    <i class="fas fa-eye me-1"></i>View
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">No leads found in the pipeline.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div id="lead-mobile" class="p-3">
            @forelse($leads as $lead)
                <div class="lead-card lead-row">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div>
                            <div class="lead-card-name">{{ $lead->customer_name }}</div>
                            <div class="lead-card-meta"><i class="fas fa-phone-alt me-1 opacity-50"></i>{{ $lead->phone }}</div>
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0">
                            <span class="s-pill i-{{ $lead->interest_level }}">{{ $lead->interest_level }}</span>
                            <span class="s-pill s-{{ $lead->status }}">{{ str_replace('_',' ',$lead->status) }}</span>
                        </div>
                    </div>

                    <div class="lead-card-row">
                        <div class="lead-card-item">
                            Product
                            <span>{{ Str::limit($lead->product_requested ?: '—', 20) }}</span>
                        </div>
                        <div class="lead-card-item">
                            Assigned
                            <span>{{ $lead->seller->name ?? '—' }}</span>
                        </div>
                        <div class="lead-card-item">
                            Follow-up
                            @if($lead->follow_up_date)
                                @if($lead->follow_up_date->isToday())
                                    <span class="text-warning fw-bold">TODAY</span>
                                @elseif($lead->follow_up_date->isPast())
                                    <span class="text-danger">{{ $lead->follow_up_date->format('d M Y') }} (OVERDUE)</span>
                                @else
                                    <span>{{ $lead->follow_up_date->format('d M Y') }}</span>
                                @endif
                            @else
                                <span>—</span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-2 pt-2 border-top" style="border-color:#f1f5f9!important;">
                        @if(in_array(auth()->user()->role, ['admin','super_admin']) || (auth()->user()->role === 'saler' && auth()->id() === $lead->assigned_seller_id))
                            <button class="btn btn-sm btn-primary rounded-2 w-100"
                                    data-bs-toggle="modal" data-bs-target="#manageLeadModal{{ $lead->id }}" data-no-global-handler>
                                <i class="fas fa-edit me-1"></i>Manage Lead
                            </button>
                        @else
                            <button class="btn btn-sm btn-light border rounded-2 w-100"
                                    data-bs-toggle="modal" data-bs-target="#manageLeadModal{{ $lead->id }}" data-no-global-handler>
                                <i class="fas fa-eye me-1"></i>View Lead
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">No leads found in the pipeline.</div>
            @endforelse
        </div>

        <div class="card-footer bg-white border-0 py-3 px-3 px-md-4">
            {{ $leads->appends(request()->query())->links() }}
        </div>
    </div>
</div>

{{-- ── Manage / View Lead Modals ── --}}
@foreach($leads as $lead)
<div class="modal fade" id="manageLeadModal{{ $lead->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form action="{{ route('admin.leads.update', $lead->id) }}" method="POST" data-no-global-handler>
                @csrf @method('PUT')
                <div class="modal-header bg-white border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:36px;height:36px;border-radius:8px;background:#6366f1;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;flex-shrink:0;">
                            {{ strtoupper(substr($lead->customer_name,0,1)) }}
                        </div>
                        <div>
                            <div class="fw-bold text-dark" style="font-size:14px;">{{ $lead->customer_name }}</div>
                            <div style="font-size:11px;color:#64748b;"><i class="fas fa-phone-alt me-1 opacity-50"></i>{{ $lead->phone }}</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0">
                    <div class="row g-0">
                        {{-- Left: Update Form --}}
                        <div class="col-md-7 p-4 border-end">
                            <p class="fw-bold x-small text-uppercase text-muted mb-3" style="letter-spacing:.08em;">Update Lead Status</p>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold" style="font-size:12px;">Customer Name</label>
                                    <input type="text" name="customer_name" class="form-control form-control-sm"
                                           value="{{ $lead->customer_name }}"
                                           {{ !in_array(auth()->user()->role,['admin','super_admin']) && auth()->id() !== $lead->assigned_seller_id ? 'readonly':'' }}>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold" style="font-size:12px;">Phone</label>
                                    <input type="text" name="phone" class="form-control form-control-sm"
                                           value="{{ $lead->phone }}"
                                           {{ !in_array(auth()->user()->role,['admin','super_admin']) && auth()->id() !== $lead->assigned_seller_id ? 'readonly':'' }}>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <label class="form-label fw-bold" style="font-size:12px;">Lead Stage</label>
                                    <select name="status" class="form-select form-select-sm" {{ !in_array(auth()->user()->role,['admin','super_admin']) && auth()->id() !== $lead->assigned_seller_id ? 'disabled' : '' }}>
                                        <option value="pending"        {{ $lead->status=='pending'        ? 'selected':'' }}>Pending</option>
                                        <option value="converted"      {{ $lead->status=='converted'      ? 'selected':'' }}>Won (Converted)</option>
                                        <option value="not_interested" {{ $lead->status=='not_interested' ? 'selected':'' }}>Lost (Not Interested)</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold" style="font-size:12px;">Interest Level</label>
                                    <select name="interest_level" class="form-select form-select-sm" {{ !in_array(auth()->user()->role,['admin','super_admin']) && auth()->id() !== $lead->assigned_seller_id ? 'disabled' : '' }}>
                                        <option value="cold" {{ $lead->interest_level=='cold' ? 'selected':'' }}>Cold</option>
                                        <option value="warm" {{ $lead->interest_level=='warm' ? 'selected':'' }}>Warm</option>
                                        <option value="hot"  {{ $lead->interest_level=='hot'  ? 'selected':'' }}>Hot</option>
                                    </select>
                                </div>

                                @if(in_array(auth()->user()->role,['admin','super_admin']) || auth()->id() === $lead->assigned_seller_id)
                                <div class="col-12">
                                    <label class="form-label fw-bold" style="font-size:12px;">Reassign Salesperson</label>
                                    <select name="assigned_seller_id" class="form-select form-select-sm">
                                        <option value="">— Unassigned —</option>
                                        @foreach($sellers as $seller)
                                            <option value="{{ $seller->id }}" {{ $lead->assigned_seller_id==$seller->id ? 'selected':'' }}>{{ $seller->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                <div class="col-6">
                                    <label class="form-label fw-bold" style="font-size:12px;">Promised Order Date</label>
                                    <input type="date" name="promised_order_date" class="form-control form-control-sm"
                                           value="{{ $lead->promised_order_date?->format('Y-m-d') }}"
                                           {{ !in_array(auth()->user()->role,['admin','super_admin']) && auth()->id() !== $lead->assigned_seller_id ? 'disabled':'' }}>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold" style="font-size:12px;">Promised Amount (TZS)</label>
                                    <input type="number" name="promised_amount" class="form-control form-control-sm" min="0" step="100"
                                           value="{{ $lead->promised_amount }}" placeholder="e.g. 50000"
                                           {{ !in_array(auth()->user()->role,['admin','super_admin']) && auth()->id() !== $lead->assigned_seller_id ? 'disabled':'' }}>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold" style="font-size:12px;">Feedback / Summary</label>
                                    <textarea name="customer_response" class="form-control form-control-sm" rows="2" {{ !in_array(auth()->user()->role,['admin','super_admin']) && auth()->id() !== $lead->assigned_seller_id ? 'disabled':'' }}>{{ $lead->customer_response }}</textarea>
                                </div>
                            </div>

                            <p class="fw-bold x-small text-uppercase text-muted mb-3" style="letter-spacing:.08em;">Log New Interaction</p>

                            <div class="mb-3">
                                <label class="form-label fw-bold" style="font-size:12px;">Interaction Notes</label>
                                <textarea name="follow_up_notes" class="form-control form-control-sm" rows="4"
                                          placeholder="Details of your recent contact…" {{ !in_array(auth()->user()->role,['admin','super_admin']) && auth()->id() !== $lead->assigned_seller_id ? 'disabled':'' }}></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold" style="font-size:12px;">Next Follow-up Date</label>
                                <input type="date" name="next_follow_up_date" class="form-control form-control-sm"
                                       value="{{ $lead->follow_up_date ? $lead->follow_up_date->format('Y-m-d') : '' }}" {{ !in_array(auth()->user()->role,['admin','super_admin']) && auth()->id() !== $lead->assigned_seller_id ? 'disabled':'' }}>
                                <div style="font-size:10px;color:#94a3b8;margin-top:4px;">A reminder will be sent on this date.</div>
                            </div>
                        </div>

                        {{-- Right: History --}}
                        <div class="col-md-5 p-4" style="background:#f8fafc;">
                            <p class="fw-bold x-small text-uppercase text-muted mb-3" style="letter-spacing:.08em;">Interaction History</p>
                            <div style="max-height:420px;overflow-y:auto;padding-right:6px;">
                                @forelse($lead->followUps->sortByDesc('created_at') as $history)
                                    <div class="mb-4 ps-3 border-start border-2 border-primary position-relative">
                                        <div class="position-absolute start-0 translate-middle-x bg-white" style="margin-left:-1px;margin-top:5px;">
                                            <i class="fas fa-dot-circle text-primary" style="font-size:9px;"></i>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div class="fw-bold" style="font-size:11px;">{{ $history->created_at->format('M d, Y') }}</div>
                                            <span class="badge bg-white text-dark shadow-sm border" style="font-size:9px;">{{ $history->user->name ?? 'System' }}</span>
                                        </div>
                                        <div style="font-size:11px;color:#64748b;margin-bottom:4px;">{{ $history->notes }}</div>
                                        @if($history->follow_up_date)
                                            <div style="font-size:10px;color:#ef4444;font-weight:700;">
                                                <i class="fas fa-calendar-alt me-1"></i>Next: {{ $history->follow_up_date->format('d M Y') }}
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-center py-5 text-muted small">
                                        <i class="fas fa-history fa-2x mb-2 d-block opacity-25"></i>No history yet.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                @if(in_array(auth()->user()->role,['admin','super_admin']) || (auth()->user()->role==='saler' && auth()->id()===$lead->assigned_seller_id))
                <div class="modal-footer bg-white border-top py-3 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-2 px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-2 px-4 fw-bold" data-no-global-handler>Save Updates</button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>
@endforeach

{{-- ── Send Message Modal ── --}}
<div class="modal fade" id="messageCustomersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form action="{{ route('admin.message-templates.send') }}" method="POST" id="messageForm" data-no-global-handler>
            @csrf
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-white border-bottom">
                    <h6 class="modal-title fw-bold text-dark"><i class="fab fa-whatsapp text-success me-2"></i>Send Message</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:12px;">Recipients</label>
                        <select name="recipient_type" id="msg_recipient_type" class="form-select form-select-sm" onchange="toggleRecipientType()">
                            <option value="assigned_leads">All My Assigned Leads</option>
                            @if(in_array(auth()->user()->role,['admin','super_admin','manager','saler']))
                                <option value="all_leads">All Leads (System-wide)</option>
                                <option value="all_customers">All Customers (System-wide)</option>
                            @endif
                            <option value="selected">Specific Customer(s)</option>
                        </select>
                        <div id="specific_customer_search" class="d-none mt-2">
                            <label class="form-label small text-muted">Search & Add Customers</label>
                            <div class="position-relative">
                                <input type="text" id="msgCustomerSearch" class="form-control form-control-sm" placeholder="Type name to search…">
                                <div id="msgCustomerResults" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index:1060;"></div>
                            </div>
                            <div id="selected_customers_list" class="mt-2 d-flex flex-wrap gap-2"></div>
                            <div id="selected_ids_container"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:12px;">Template (Optional)</label>
                        <select name="template_id" id="msg_template_id" class="form-select form-select-sm" onchange="applyTemplate()">
                            <option value="">— Write Custom Message —</option>
                            @foreach($templates as $tpl)
                                <option value="{{ $tpl->id }}" data-content="{{ $tpl->content }}">{{ $tpl->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold" style="font-size:12px;">Message</label>
                        <textarea name="custom_content" id="msg_content" class="form-control form-control-sm" rows="5" required placeholder="Type your message here…"></textarea>
                        <div class="text-end mt-1" style="font-size:10px;color:#94a3b8;" id="charCount">0 characters</div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-2 px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success rounded-2 px-4 fw-bold">
                        <i class="fas fa-paper-plane me-1"></i>Send
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Add New Lead Modal ── --}}
<div class="modal fade" id="addLeadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form action="{{ route('admin.leads.store') }}" method="POST" data-no-global-handler>
            @csrf
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-white border-bottom">
                    <h6 class="modal-title fw-bold text-dark"><i class="fas fa-plus-circle text-primary me-2"></i>Add New Lead</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size:12px;">Search Existing Customers</label>
                        <div class="position-relative">
                            <input type="text" id="customerSearch" class="form-control form-control-sm bg-light"
                                   placeholder="Search by name or phone to auto-fill…">
                            <div id="customerResults" class="list-group position-absolute w-100 shadow-sm d-none"
                                 style="z-index:1050;border:1px solid #dee2e6;"></div>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold" style="font-size:12px;">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" id="lead_customer_name" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:12px;">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="lead_phone" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:12px;">Email</label>
                            <input type="email" name="email" id="lead_email" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold" style="font-size:12px;">Lead Source</label>
                            <select name="source" id="leadSourceSelect" class="form-select form-select-sm" onchange="handleLeadSourceChange(this.value)">
                                <option value="">— Select Lead Source —</option>
                                @php $leadSources = \App\Models\CustomerSource::active()->get(); @endphp
                                @foreach($leadSources as $ls)
                                    <option value="{{ $ls->name }}">{{ $ls->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 d-none" id="campaignSubPanel">
                            <label class="form-label fw-bold" style="font-size:12px;">Campaign <span class="text-muted">(Instagram)</span></label>
                            <select name="campaign_id" id="campaignIdSelect" class="form-select form-select-sm">
                                <option value="">— Select Campaign —</option>
                            </select>
                        </div>
                        <div class="col-12 d-none" id="programSubPanel">
                            <label class="form-label fw-bold" style="font-size:12px;">Inside Program</label>
                            <select name="program_id" id="programIdSelect" class="form-select form-select-sm">
                                <option value="">— Select Program —</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold" style="font-size:12px;">Product / Service Interest</label>
                            <input type="text" name="product_requested" class="form-control form-control-sm" placeholder="What are they looking for?">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:12px;">Assign to Seller</label>
                            @if(auth()->user()->role === 'saler')
                                <input type="text" class="form-control form-control-sm bg-light" value="{{ auth()->user()->name }}" readonly>
                                <input type="hidden" name="assigned_seller_id" value="{{ auth()->id() }}">
                            @else
                                <select name="assigned_seller_id" class="form-select form-select-sm">
                                    <option value="">— Unassigned —</option>
                                    @foreach($sellers as $seller)
                                        <option value="{{ $seller->id }}">{{ $seller->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size:12px;">Scheduled Follow-up</label>
                            <input type="date" name="follow_up_date" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime('+3 days')) }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-2 px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-2 px-4 fw-bold" data-no-global-handler>Create Lead</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// ── Lead source sub-dropdowns (Instagram → campaign, Inside Programs → program) ──
let _campaignsCache = null;
let _programsCache  = null;

function handleLeadSourceChange(val) {
    const isInstagram = val.toLowerCase() === 'instagram';
    const isPrograms  = val.toLowerCase() === 'inside programs';

    const campPanel = document.getElementById('campaignSubPanel');
    const progPanel = document.getElementById('programSubPanel');

    campPanel.classList.toggle('d-none', !isInstagram);
    progPanel.classList.toggle('d-none', !isPrograms);

    if (isInstagram && !_campaignsCache) {
        fetch('{{ route('admin.api.campaigns-list') }}')
            .then(r => r.json())
            .then(data => {
                _campaignsCache = data;
                populateSelect('campaignIdSelect', data, 'id', 'title', '— Select Campaign —');
            });
    }
    if (isPrograms && !_programsCache) {
        fetch('{{ route('admin.api.programs-list') }}')
            .then(r => r.json())
            .then(data => {
                _programsCache = data;
                populateSelect('programIdSelect', data, 'id', 'name', '— Select Program —');
            });
    }
}

function populateSelect(selectId, items, valKey, labelKey, placeholder) {
    const sel = document.getElementById(selectId);
    if (!sel) return;
    sel.innerHTML = '<option value="">' + placeholder + '</option>';
    items.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item[valKey];
        opt.textContent = item[labelKey];
        sel.appendChild(opt);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    // ── Quick search (client-side) ──
    const quickSearch = document.getElementById('quickSearch');
    if (quickSearch) {
        quickSearch.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.lead-row').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    // ── Add Lead: customer search ──
    const searchInput      = document.getElementById('customerSearch');
    const resultsContainer = document.getElementById('customerResults');
    const nameInput        = document.getElementById('lead_customer_name');
    const phoneInput       = document.getElementById('lead_phone');
    const emailInput       = document.getElementById('lead_email');
    let timeout = null;

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(timeout);
            const query = this.value.trim();
            if (query.length < 2) { resultsContainer.classList.add('d-none'); return; }
            timeout = setTimeout(() => {
                fetch(`{{ route('admin.pos.customers.search') }}?query=${encodeURIComponent(query)}`)
                    .then(r => r.json())
                    .then(data => {
                        resultsContainer.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(c => {
                                const item = document.createElement('button');
                                item.type = 'button';
                                item.className = 'list-group-item list-group-item-action border-0 py-2 small';
                                item.innerHTML = `<div class="d-flex justify-content-between"><span class="fw-bold">${c.name}</span><span class="text-muted" style="font-size:11px;">${c.phone}</span></div>`;
                                item.onclick = () => {
                                    nameInput.value = c.name;
                                    phoneInput.value = c.phone;
                                    emailInput.value = c.email || '';
                                    resultsContainer.classList.add('d-none');
                                    searchInput.value = c.name;
                                };
                                resultsContainer.appendChild(item);
                            });
                            resultsContainer.classList.remove('d-none');
                        } else { resultsContainer.classList.add('d-none'); }
                    });
            }, 300);
        });

        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target))
                resultsContainer.classList.add('d-none');
        });
    }

    // ── Auto-open from URL params ──
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('auto_create')) {
        if (nameInput)  nameInput.value  = urlParams.get('name')  || '';
        if (phoneInput) phoneInput.value = urlParams.get('phone') || '';
        if (emailInput) emailInput.value = urlParams.get('email') || '';
        new bootstrap.Modal(document.getElementById('addLeadModal')).show();
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // ── Message Modal ──
    const msgSearchInput = document.getElementById('msgCustomerSearch');
    const msgResultsContainer = document.getElementById('msgCustomerResults');
    const selectedList   = document.getElementById('selected_customers_list');
    const idsContainer   = document.getElementById('selected_ids_container');
    let msgTimeout = null;

    window.toggleRecipientType = function () {
        const type = document.getElementById('msg_recipient_type').value;
        document.getElementById('specific_customer_search').classList.toggle('d-none', type !== 'selected');
    };

    window.applyTemplate = function () {
        const sel = document.getElementById('msg_template_id');
        const opt = sel.options[sel.selectedIndex];
        if (opt.dataset.content) {
            document.getElementById('msg_content').value = opt.dataset.content;
            updateCharCount();
        }
    };

    const msgContent = document.getElementById('msg_content');
    if (msgContent) msgContent.addEventListener('input', updateCharCount);

    function updateCharCount() {
        const el = document.getElementById('charCount');
        if (el) el.innerText = document.getElementById('msg_content').value.length + ' characters';
    }

    if (msgSearchInput) {
        msgSearchInput.addEventListener('input', function () {
            clearTimeout(msgTimeout);
            const query = this.value.trim();
            if (query.length < 2) { msgResultsContainer.classList.add('d-none'); return; }
            msgTimeout = setTimeout(() => {
                fetch(`{{ route('admin.pos.customers.search') }}?query=${encodeURIComponent(query)}`)
                    .then(r => r.json())
                    .then(data => {
                        msgResultsContainer.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(c => {
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'list-group-item list-group-item-action py-2';
                                btn.innerHTML = `<strong>${c.name}</strong> <small class='text-muted'>${c.phone}</small>`;
                                btn.onclick = () => addCustomerToMessage(c);
                                msgResultsContainer.appendChild(btn);
                            });
                            msgResultsContainer.classList.remove('d-none');
                        } else { msgResultsContainer.classList.add('d-none'); }
                    });
            }, 300);
        });
    }

    function addCustomerToMessage(customer) {
        if (document.querySelector(`input[name="customer_ids[]"][value="${customer.id}"]`)) return;
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = 'customer_ids[]'; input.value = customer.id;
        idsContainer.appendChild(input);
        const tag = document.createElement('div');
        tag.className = 'badge bg-light text-dark border p-2 d-flex align-items-center gap-2';
        tag.innerHTML = `<span>${customer.name}</span><i class="fas fa-times text-danger" style="cursor:pointer;" onclick="removeCustomerFromMessage('${customer.id}',this)"></i>`;
        selectedList.appendChild(tag);
        msgSearchInput.value = '';
        msgResultsContainer.classList.add('d-none');
    }

    window.removeCustomerFromMessage = function (id, el) {
        el.parentElement.remove();
        const inp = document.querySelector(`input[name="customer_ids[]"][value="${id}"]`);
        if (inp) inp.remove();
    };
});
</script>
@endpush

@endsection
