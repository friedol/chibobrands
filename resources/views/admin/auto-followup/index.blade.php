@extends('layouts.admin')

@section('title', 'Auto Follow-up')

@push('styles')
<style>
.afu-stat-card {
    border-radius: 14px;
    padding: 1.1rem 1.4rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    border: none;
}
.afu-stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}
.afu-stat-value { font-size: 1.6rem; font-weight: 700; line-height: 1; }
.afu-stat-label { font-size: 0.72rem; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; }
.range-btn.active { font-weight: 700; }
.customer-row:hover { background: #f8fafc; }
.days-badge { font-size: 0.68rem; padding: 2px 8px; border-radius: 20px; font-weight: 600; }
.overdue-badge { background: #fee2e2; color: #dc2626; }
.today-badge  { background: #fef9c3; color: #b45309; }
.upcoming-badge { background: #dcfce7; color: #16a34a; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 py-3">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h2 class="mb-0 fw-bold">Auto Follow-up</h2>
            <p class="text-muted small mb-0">Customers due for repeat purchase follow-up</p>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="afu-stat-card bg-white">
                <div class="afu-stat-icon" style="background:rgba(220,38,38,0.1);color:#dc2626;">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div>
                    <div class="afu-stat-value text-danger">{{ $stats['overdue'] }}</div>
                    <div class="afu-stat-label">Overdue</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="afu-stat-card bg-white">
                <div class="afu-stat-icon" style="background:rgba(245,158,11,0.1);color:#f59e0b;">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div>
                    <div class="afu-stat-value text-warning">{{ $stats['due_today'] }}</div>
                    <div class="afu-stat-label">Due Today</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="afu-stat-card bg-white">
                <div class="afu-stat-icon" style="background:rgba(22,163,74,0.1);color:#16a34a;">
                    <i class="fas fa-calendar-week"></i>
                </div>
                <div>
                    <div class="afu-stat-value text-success">{{ $stats['this_week'] }}</div>
                    <div class="afu-stat-label">This Week</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Range Filter Tabs --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        @foreach([
            'overdue_and_today' => ['Overdue & Today', 'btn-danger'],
            'today'             => ['Due Today', 'btn-warning'],
            'this_week'         => ['This Week', 'btn-success'],
            'upcoming_7'        => ['Next 7 Days', 'btn-info'],
        ] as $key => [$label, $color])
            <a href="{{ route('admin.auto-followup.index', array_merge(request()->except('range', 'page'), ['range' => $key])) }}"
               class="btn btn-sm range-btn {{ $range === $key ? $color : 'btn-outline-secondary' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Admin seller filter --}}
    @if(in_array(auth()->user()->role, ['admin', 'super_admin', 'manager']))
    <form method="GET" action="{{ route('admin.auto-followup.index') }}" class="mb-3 d-flex gap-2 flex-wrap align-items-end" data-no-global-handler>
        <input type="hidden" name="range" value="{{ $range }}">
        <div>
            <label class="form-label fw-bold x-small text-uppercase mb-1" style="font-size:10px;color:#64748b;">Seller</label>
            <select name="saler_id" class="form-select form-select-sm" style="min-width:160px;">
                <option value="">All Sellers</option>
                @foreach($salers as $s)
                    <option value="{{ $s->id }}" {{ request('saler_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label fw-bold x-small text-uppercase mb-1" style="font-size:10px;color:#64748b;">Status</label>
            <select name="follow_up_status" class="form-select form-select-sm" style="min-width:140px;">
                <option value="">All Statuses</option>
                <option value="pending"     {{ request('follow_up_status') == 'pending'     ? 'selected' : '' }}>Pending</option>
                <option value="contacted"   {{ request('follow_up_status') == 'contacted'   ? 'selected' : '' }}>Contacted</option>
                <option value="ordered"     {{ request('follow_up_status') == 'ordered'     ? 'selected' : '' }}>Ordered</option>
                <option value="not_interested" {{ request('follow_up_status') == 'not_interested' ? 'selected' : '' }}>Not Interested</option>
            </select>
        </div>
        <div class="align-self-end">
            <button type="submit" class="btn btn-sm btn-primary fw-bold" data-no-global-handler>
                <i class="fas fa-sync-alt me-1"></i>Filter
            </button>
            <a href="{{ route('admin.auto-followup.index') }}" class="btn btn-sm btn-outline-secondary fw-bold ms-1">Reset</a>
        </div>
    </form>
    @endif

    {{-- Customer Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="bg-light">
                            <th class="ps-3 small fw-bold">#</th>
                            <th class="small fw-bold">Customer</th>
                            <th class="small fw-bold">Phone</th>
                            <th class="small fw-bold">Follow-up Date</th>
                            <th class="small fw-bold text-center">Due In</th>
                            <th class="small fw-bold">Status</th>
                            <th class="small fw-bold">Last Order</th>
                            <th class="small fw-bold text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            @php
                                $followUpDate = $customer->manual_follow_up_date ?? $customer->next_expected_order_date;
                                $daysUntil    = $followUpDate ? now()->startOfDay()->diffInDays($followUpDate->startOfDay(), false) : null;
                                if ($daysUntil === null) continue;
                                $dueBadge = $daysUntil < 0
                                    ? ['overdue-badge', abs($daysUntil) . 'd overdue']
                                    : ($daysUntil == 0
                                        ? ['today-badge', 'Today']
                                        : ['upcoming-badge', "In {$daysUntil}d"]);
                            @endphp
                            <tr class="customer-row">
                                <td class="ps-3 small text-muted">{{ $loop->iteration + ($customers->currentPage() - 1) * $customers->perPage() }}</td>
                                <td>
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="fw-bold text-dark text-decoration-none small">
                                        {{ $customer->name }}
                                    </a>
                                    @if($customer->company_name)
                                        <div class="x-small text-muted">{{ $customer->company_name }}</div>
                                    @endif
                                </td>
                                <td class="small">{{ $customer->phone }}</td>
                                <td class="small">
                                    {{ $followUpDate ? $followUpDate->format('d M Y') : '—' }}
                                    @if($customer->manual_follow_up_date)
                                        <span class="badge bg-primary bg-opacity-10 text-primary ms-1" style="font-size:0.6rem;">Manual</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="days-badge {{ $dueBadge[0] }}">{{ $dueBadge[1] }}</span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill
                                        @switch($customer->follow_up_status)
                                            @case('contacted')  bg-info text-white @break
                                            @case('ordered')    bg-success text-white @break
                                            @case('not_interested') bg-danger text-white @break
                                            @default            bg-warning text-dark
                                        @endswitch"
                                        style="font-size:0.65rem;">
                                        {{ ucfirst(str_replace('_', ' ', $customer->follow_up_status ?? 'pending')) }}
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    {{ $customer->last_order_date ? \Carbon\Carbon::parse($customer->last_order_date)->format('d M Y') : '—' }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.customer-data-center.show', $customer) }}"
                                       class="btn btn-outline-primary btn-xs p-1" title="Open CRM Profile">
                                        <i class="fas fa-eye" style="font-size:0.75rem;"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-check-circle text-success fa-2x mb-2 d-block"></i>
                                    No customers due for follow-up in this range.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($customers->hasPages())
        <div class="card-footer bg-white border-0 py-2">
            {{ $customers->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
