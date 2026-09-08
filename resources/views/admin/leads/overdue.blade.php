@extends('layouts.admin')
@section('title', 'Overdue Follow-Ups')

@section('content')
<div class="container-fluid py-3">

    {{-- ── Header ── --}}
    <div class="row mb-3 align-items-center">
        <div class="col-lg-6">
            <h4 class="fw-bold mb-0"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Follow-Up Tracker</h4>
            <p class="text-muted small mb-0">Monitor overdue, today's and upcoming follow-ups</p>
        </div>
        <div class="col-lg-6 text-lg-end mt-2 mt-lg-0">
            <a href="{{ route('admin.leads.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fas fa-arrow-left me-1"></i> Back to Leads
            </a>
        </div>
    </div>

    {{-- ── Summary cards ── --}}
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-12 col-md-4">
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'overdue']) }}" class="text-decoration-none">
                <div class="cust-stat-card {{ $filter === 'overdue' ? 'border-danger shadow-sm' : '' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="cust-stat-icon bg-danger-subtle text-danger"><i class="fas fa-calendar-times"></i></div>
                        <span class="cust-stat-sub text-danger fw-bold">Needs action</span>
                    </div>
                    <div class="cust-stat-val text-danger">{{ number_format($overdueCount) }}</div>
                    <div class="cust-stat-lbl">Overdue Follow-Ups</div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'today']) }}" class="text-decoration-none">
                <div class="cust-stat-card {{ $filter === 'today' ? 'border-warning shadow-sm' : '' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="cust-stat-icon bg-warning-subtle text-warning"><i class="fas fa-clock"></i></div>
                        <span class="cust-stat-sub text-warning fw-bold">Today</span>
                    </div>
                    <div class="cust-stat-val text-warning">{{ number_format($todayCount) }}</div>
                    <div class="cust-stat-lbl">Due Today</div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="{{ request()->fullUrlWithQuery(['filter' => 'upcoming']) }}" class="text-decoration-none">
                <div class="cust-stat-card {{ $filter === 'upcoming' ? 'border-success shadow-sm' : '' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="cust-stat-icon bg-success-subtle text-success"><i class="fas fa-calendar-check"></i></div>
                        <span class="cust-stat-sub text-success fw-bold">Next 7 days</span>
                    </div>
                    <div class="cust-stat-val text-success">{{ number_format($upcomingCount) }}</div>
                    <div class="cust-stat-lbl">Upcoming Follow-Ups</div>
                </div>
            </a>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light p-3">
            <form action="{{ route('admin.leads.overdue') }}" method="GET" class="row g-2">
                {{-- Filter tabs --}}
                <div class="col-12 col-md-3">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">View</label>
                    <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="overdue"  {{ $filter === 'overdue'  ? 'selected' : '' }}>Overdue</option>
                        <option value="today"    {{ $filter === 'today'    ? 'selected' : '' }}>Due Today</option>
                        <option value="upcoming" {{ $filter === 'upcoming' ? 'selected' : '' }}>Upcoming (7 days)</option>
                    </select>
                </div>

                @if(in_array(auth()->user()->role, ['admin','super_admin','accountant']))
                <div class="col-12 col-md-3">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">Seller</label>
                    <select name="saler_id" class="form-select form-select-sm">
                        <option value="">All Sellers</option>
                        @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}" {{ request('saler_id') == $seller->id ? 'selected' : '' }}>
                                {{ $seller->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="col-6 col-md-2">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">Source</label>
                    <select name="source" class="form-select form-select-sm">
                        <option value="all">All Sources</option>
                        @foreach(\App\Models\CustomerSource::active()->get() as $src)
                            <option value="{{ $src->name }}" {{ request('source') === $src->name ? 'selected' : '' }}>{{ $src->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label fw-bold x-small text-uppercase mb-1">From Date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>

                <div class="col-6 col-md-2 d-flex align-items-end gap-1">
                    <div class="flex-grow-1">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">To Date</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm mb-0"><i class="fas fa-search"></i></button>
                    <a href="{{ route('admin.leads.overdue') }}" class="btn btn-outline-secondary btn-sm mb-0"><i class="fas fa-times"></i></a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Table ── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom py-2">
            <span class="fw-semibold">
                @if($filter === 'overdue')  <span class="badge bg-danger me-1">OVERDUE</span>
                @elseif($filter === 'today') <span class="badge bg-warning text-dark me-1">TODAY</span>
                @else <span class="badge bg-success me-1">UPCOMING</span>
                @endif
                {{ $leads->total() }} follow-ups
            </span>
            <a href="{{ route('admin.leads.print', array_merge(request()->all(), ['overdue' => 1])) }}"
               target="_blank" class="btn btn-outline-dark btn-sm">
                <i class="fas fa-print me-1"></i> Print
            </a>
        </div>

        <div class="card-body p-0">
            @if($leads->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="text-muted">No follow-ups found for this filter.</p>
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Seller</th>
                            <th>Source</th>
                            <th>Follow-Up Date</th>
                            @if($filter === 'overdue')<th>Days Overdue</th>@endif
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Last Note</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leads as $i => $lead)
                        @php
                            $rowClass = match($lead->follow_up_status) {
                                'overdue'  => 'table-danger',
                                'today'    => 'table-warning',
                                'upcoming' => '',
                                default    => '',
                            };
                            $daysOver = $lead->days_overdue_value ?? 0;
                            if ($lead->follow_up_date && $lead->follow_up_date->isPast() && !$lead->follow_up_date->isToday()) {
                                $daysOver = today()->diffInDays($lead->follow_up_date);
                            }
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td class="text-muted">{{ $leads->firstItem() + $i }}</td>
                            <td>
                                <span class="fw-semibold">{{ $lead->customer_name }}</span>
                                @if($lead->product_requested)
                                    <br><span class="text-muted x-small">{{ Str::limit($lead->product_requested, 30) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($lead->phone)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" target="_blank" class="text-success text-decoration-none">
                                        <i class="fab fa-whatsapp me-1"></i>{{ $lead->phone }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $lead->seller?->name ?? '—' }}</td>
                            <td>
                                @if($lead->source)
                                    <span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_',' ',$lead->source)) }}</span>
                                @else —
                                @endif
                            </td>
                            <td>
                                @if($lead->follow_up_date)
                                    <span class="badge bg-{{ $lead->follow_up_status_color }}">
                                        {{ $lead->follow_up_date->format('d M Y') }}
                                    </span>
                                @else <span class="text-muted">Not set</span>
                                @endif
                            </td>
                            @if($filter === 'overdue')
                            <td>
                                @if($daysOver > 0)
                                    <span class="badge bg-danger">{{ $daysOver }}d late</span>
                                @else —
                                @endif
                            </td>
                            @endif
                            <td>
                                <span class="badge bg-{{ $lead->priority_badge ?? 'secondary' }}">
                                    {{ ucfirst($lead->priority ?? 'normal') }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusColors = ['pending' => 'warning', 'converted' => 'success', 'not_interested' => 'secondary'];
                                    $statusLabels = ['pending' => 'Pending', 'converted' => 'Won', 'not_interested' => 'Lost'];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$lead->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$lead->status] ?? $lead->status }}
                                </span>
                            </td>
                            <td class="text-muted" style="max-width:160px;">
                                @if($lead->followUps->isNotEmpty())
                                    <small>{{ Str::limit($lead->followUps->first()->notes, 50) }}</small>
                                @else <small>—</small>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    {{-- Quick WhatsApp --}}
                                    @if($lead->phone)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}"
                                       target="_blank" class="btn btn-sm btn-outline-success py-0 px-2" title="WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                    @endif
                                    {{-- Quick update modal trigger --}}
                                    <button class="btn btn-sm btn-outline-primary py-0 px-2"
                                        data-bs-toggle="modal" data-bs-target="#updateLeadModal"
                                        data-lead-id="{{ $lead->id }}"
                                        data-lead-name="{{ $lead->customer_name }}"
                                        data-lead-status="{{ $lead->status }}"
                                        data-lead-seller="{{ $lead->assigned_seller_id }}"
                                        onclick="fillUpdateModal(this)"
                                        title="Update">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                <small class="text-muted">Showing {{ $leads->firstItem() }}–{{ $leads->lastItem() }} of {{ $leads->total() }}</small>
                {{ $leads->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ── Quick Update Modal ── --}}
<div class="modal fade" id="updateLeadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-1">
                <h6 class="modal-title fw-bold">Update Lead</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="updateLeadForm">
                @csrf @method('PUT')
                <div class="modal-body pt-2">
                    <input type="hidden" name="lead_id_ref" id="modal_lead_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Lead Name</label>
                        <input type="text" name="customer_name" class="form-control form-control-sm" id="modal_lead_name" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="pending">Pending</option>
                                <option value="converted">Won (Converted)</option>
                                <option value="not_interested">Lost</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Next Follow-Up</label>
                            <input type="date" name="next_follow_up_date" class="form-control form-control-sm"
                                   min="{{ today()->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="mt-2">
                        <label class="form-label fw-semibold small">Notes</label>
                        <textarea name="follow_up_notes" class="form-control form-control-sm" rows="3"
                                  placeholder="What happened in this follow-up?"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Save Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function fillUpdateModal(btn) {
    const leadId = btn.dataset.leadId;
    document.getElementById('modal_lead_id').value = leadId;
    document.getElementById('modal_lead_name').value = btn.dataset.leadName;
    document.getElementById('updateLeadForm').action = `/admin/leads/${leadId}`;
    const statusSel = document.querySelector('#updateLeadModal select[name="status"]');
    if (statusSel) statusSel.value = btn.dataset.leadStatus;
}
</script>
@endpush
