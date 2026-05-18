@extends('layouts.admin')
@section('title', 'Follow-Up Data Center')

@section('content')
<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="fas fa-crosshairs text-primary me-2"></i>Follow-Up Data Center</h4>
            <p class="text-muted small mb-0">Manage promised orders, priority levels, and follow-up notes</p>
        </div>
        <a href="{{ route('admin.leads.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Leads
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3" style="border-radius:12px;">
                <div class="text-muted small fw-bold text-uppercase mb-1">Pending Leads</div>
                <div class="fs-3 fw-bold text-primary">{{ number_format($counts['total']) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3" style="border-radius:12px;">
                <div class="text-muted small fw-bold text-uppercase mb-1">High / Urgent</div>
                <div class="fs-3 fw-bold text-danger">{{ number_format($counts['urgent']) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3" style="border-radius:12px;">
                <div class="text-muted small fw-bold text-uppercase mb-1">With Promised Orders</div>
                <div class="fs-3 fw-bold text-success">{{ number_format($counts['promised']) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3" style="border-radius:12px;">
                <div class="text-muted small fw-bold text-uppercase mb-1">Overdue Follow-Ups</div>
                <div class="fs-3 fw-bold text-warning">{{ number_format($counts['overdue']) }}</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light p-3">
            <form action="{{ route('admin.leads.follow-up-center') }}" method="GET" class="row g-2 align-items-end">
                @if(in_array(auth()->user()->role, ['admin','super_admin','accountant']))
                <div class="col-6 col-md-2">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">Seller</label>
                    <select name="saler_id" class="form-select form-select-sm">
                        <option value="">All Sellers</option>
                        @foreach($sellers as $s)
                            <option value="{{ $s->id }}" {{ request('saler_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-6 col-md-2">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">Priority</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">All Priorities</option>
                        @foreach(['urgent'=>'Urgent','high'=>'High','normal'=>'Normal','low'=>'Low'] as $val => $label)
                            <option value="{{ $val }}" {{ request('priority') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">Lead Type</label>
                    <select name="lead_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach(['hot'=>'Hot','warm'=>'Warm','cold'=>'Cold'] as $val => $label)
                            <option value="{{ $val }}" {{ request('lead_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">Follow-Up Status</label>
                    <select name="follow_up_status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="overdue" {{ request('follow_up_status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="today" {{ request('follow_up_status') === 'today' ? 'selected' : '' }}>Due Today</option>
                        <option value="upcoming" {{ request('follow_up_status') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                    <a href="{{ route('admin.leads.follow-up-center') }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="card border-0 shadow-sm" style="border-radius:15px; overflow:hidden;">
        <div class="card-header border-0 py-2 px-4" style="background:linear-gradient(135deg,#0d6efd,#0a58ca);">
            <h6 class="mb-0 fw-bold text-white"><i class="fas fa-list me-2"></i>Pending Leads Follow-Up Tracker ({{ $leads->total() }})</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="bg-light text-muted text-uppercase" style="font-size:0.7rem;letter-spacing:0.05rem;">
                        <tr>
                            <th class="ps-4 py-3">Customer</th>
                            <th class="py-3">Seller</th>
                            <th class="py-3">Priority</th>
                            <th class="py-3">Type</th>
                            <th class="py-3">Follow-Up Date</th>
                            <th class="py-3">Promised Order</th>
                            <th class="py-3">Promised Amount</th>
                            <th class="py-3">Last Note</th>
                            <th class="py-3">Last Follow-Up</th>
                            <th class="text-end pe-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads as $lead)
                        @php
                            $statusColor = $lead->follow_up_status_color;
                            $rowBg = $statusColor === 'danger' ? 'rgba(220,53,69,0.04)' : ($statusColor === 'warning' ? 'rgba(255,193,7,0.05)' : '');
                        @endphp
                        <tr style="background:{{ $rowBg }}">
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark">{{ $lead->customer_name }}</div>
                                <div class="text-muted" style="font-size:0.7rem;"><i class="fas fa-phone-alt me-1"></i>{{ $lead->phone ?? '—' }}</div>
                            </td>
                            <td class="py-3">
                                <span class="text-dark">{{ $lead->seller?->name ?? '—' }}</span>
                            </td>
                            <td class="py-3">
                                @php
                                    $pBadge = match($lead->priority) {
                                        'urgent' => 'danger', 'high' => 'warning',
                                        'low' => 'secondary', default => 'primary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $pBadge }} rounded-pill px-2">{{ ucfirst($lead->priority ?? 'normal') }}</span>
                            </td>
                            <td class="py-3">
                                @if($lead->lead_type)
                                    @php $typeBadge = match($lead->lead_type) { 'hot'=>'danger','warm'=>'warning', default=>'info' }; @endphp
                                    <span class="badge bg-{{ $typeBadge }} bg-opacity-10 text-{{ $typeBadge }} rounded-pill px-2 border border-{{ $typeBadge }}">{{ ucfirst($lead->lead_type) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($lead->follow_up_date)
                                    <span class="fw-bold text-{{ $statusColor }}">
                                        {{ $lead->follow_up_date->format('d M Y') }}
                                    </span>
                                    @if($lead->follow_up_status === 'overdue')
                                        <div class="text-danger" style="font-size:0.65rem;">{{ $lead->days_overdue }}d overdue</div>
                                    @elseif($lead->follow_up_status === 'today')
                                        <div class="text-warning" style="font-size:0.65rem;">Due today</div>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($lead->promised_order_date)
                                    @php
                                        $promisedDays = now()->diffInDays($lead->promised_order_date, false);
                                        $promisedColor = $promisedDays < 0 ? 'danger' : ($promisedDays <= 3 ? 'warning' : 'success');
                                    @endphp
                                    <span class="text-{{ $promisedColor }} fw-bold">{{ $lead->promised_order_date->format('d M Y') }}</span>
                                    <div style="font-size:0.65rem;" class="text-{{ $promisedColor }}">
                                        {{ $promisedDays >= 0 ? 'in '.$promisedDays.'d' : abs($promisedDays).'d passed' }}
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($lead->promised_amount)
                                    <span class="fw-bold text-success">{{ number_format($lead->promised_amount) }} <small class="text-muted fw-normal">TZS</small></span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="py-3" style="max-width:160px;">
                                @if($lead->last_follow_up_notes)
                                    <span class="text-dark text-truncate d-inline-block" style="max-width:150px;" title="{{ $lead->last_follow_up_notes }}">
                                        {{ $lead->last_follow_up_notes }}
                                    </span>
                                @else
                                    <span class="text-muted fst-italic">No notes</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($lead->last_follow_up_date)
                                    <span class="text-dark">{{ $lead->last_follow_up_date->format('d M Y') }}</span>
                                    <div class="text-muted" style="font-size:0.65rem;">{{ $lead->last_follow_up_date->diffForHumans() }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end pe-4 py-3">
                                <button type="button"
                                    class="btn btn-sm btn-primary rounded-pill px-3"
                                    data-bs-toggle="modal"
                                    data-bs-target="#followUpModal{{ $lead->id }}">
                                    <i class="fas fa-edit me-1"></i>Update
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="fas fa-check-circle text-success mb-3" style="font-size:3rem; opacity:0.3;"></i>
                                <h6 class="text-muted">No pending leads match your filters.</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($leads->hasPages())
        <div class="card-footer border-0 bg-white px-4 py-3">
            {{ $leads->links() }}
        </div>
        @endif
    </div>

</div>

{{-- Update Modals --}}
@foreach($leads as $lead)
<div class="modal fade" id="followUpModal{{ $lead->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px; overflow:hidden;">
            <div class="modal-header border-0 text-white py-3 px-4" style="background:linear-gradient(135deg,#0d6efd,#0a58ca);">
                <div>
                    <h5 class="modal-title fw-bold mb-0 text-white"><i class="fas fa-crosshairs me-2"></i>Update Follow-Up — {{ $lead->customer_name }}</h5>
                    <p class="mb-0 small text-white-50">{{ $lead->phone ?? '' }} · {{ $lead->product_requested ?? '' }}</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.leads.update-follow-up', $lead->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="letter-spacing:0.05rem;">Priority</label>
                            <select name="priority" class="form-select">
                                @foreach(['urgent'=>'Urgent','high'=>'High','normal'=>'Normal','low'=>'Low'] as $val => $label)
                                    <option value="{{ $val }}" {{ ($lead->priority ?? 'normal') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="letter-spacing:0.05rem;">Lead Type</label>
                            <select name="lead_type" class="form-select">
                                <option value="">— Select —</option>
                                @foreach(['hot'=>'Hot','warm'=>'Warm','cold'=>'Cold'] as $val => $label)
                                    <option value="{{ $val }}" {{ $lead->lead_type === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="letter-spacing:0.05rem;">Promised Amount (TZS)</label>
                            <input type="number" name="promised_amount" class="form-control" step="100" min="0"
                                   value="{{ $lead->promised_amount }}" placeholder="e.g. 50000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="letter-spacing:0.05rem;">Promised Order Date</label>
                            <input type="date" name="promised_order_date" class="form-control"
                                   value="{{ $lead->promised_order_date?->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="letter-spacing:0.05rem;">Last Follow-Up Date</label>
                            <input type="date" name="last_follow_up_date" class="form-control"
                                   value="{{ $lead->last_follow_up_date?->format('Y-m-d') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="letter-spacing:0.05rem;">Last Follow-Up Notes</label>
                            <textarea name="last_follow_up_notes" class="form-control" rows="4"
                                      placeholder="What was discussed? What did the customer say?">{{ $lead->last_follow_up_notes }}</textarea>
                        </div>
                    </div>

                    {{-- Follow-up history --}}
                    @if($lead->followUps->isNotEmpty())
                    <hr class="my-3">
                    <h6 class="fw-bold text-muted small text-uppercase mb-2"><i class="fas fa-history me-1"></i>Recent Follow-Up History</h6>
                    <div class="timeline-mini">
                        @foreach($lead->followUps as $fu)
                        <div class="d-flex gap-3 mb-2">
                            <div class="flex-shrink-0 text-center" style="width:36px;">
                                <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center mx-auto"
                                     style="width:30px;height:30px;">
                                    <i class="fas fa-phone-alt text-primary" style="font-size:0.6rem;"></i>
                                </div>
                                <div class="border-start border-2 mx-auto" style="width:2px; height:16px; margin-top:2px;"></div>
                            </div>
                            <div class="flex-grow-1 pb-2">
                                <div class="small text-dark">{{ $fu->notes ?? $fu->follow_up_notes ?? '(no notes)' }}</div>
                                <div class="text-muted" style="font-size:0.68rem;">
                                    {{ $fu->created_at->format('d M Y H:i') }}
                                    @if($fu->user) · by {{ $fu->user->name }} @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold" data-no-global-handler>
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@push('styles')
<style>
    .x-small { font-size: 0.75rem; }
    .text-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
</style>
@endpush
@endsection
