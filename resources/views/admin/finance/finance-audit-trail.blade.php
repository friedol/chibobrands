@extends('layouts.admin')

@section('title', 'Finance Audit Trail')

@push('styles')
<style>
    .fat-header { background: #f8fafc; border: 1px solid #e2e8f0; color: #1e293b; border-radius: 12px; padding: 20px 24px; margin-bottom: 24px; }
    .filter-panel { background: #f8fafc; border-radius: 10px; padding: 14px 16px; border: 1px solid #e2e8f0; margin-bottom: 20px; }
    .fat-table th { font-size: 0.72rem; text-transform: uppercase; letter-spacing: .4px; color: #64748b; font-weight: 700; padding: 10px 14px; background: #f8fafc; border: none; }
    .fat-table td { font-size: 0.82rem; padding: 9px 14px; border-color: #f1f5f9; vertical-align: middle; }
    .action-pill { font-size: 0.68rem; font-weight: 700; padding: 3px 8px; border-radius: 20px; }
    .val-code { font-size: 0.7rem; background: #f1f5f9; padding: 2px 5px; border-radius: 4px; max-width: 180px; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; cursor: pointer; }
    .sum-chip { background: #f1f5f9; border-radius: 8px; padding: 8px 14px; text-align: center; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="fat-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-1" style="color: #1e293b;"><i class="fas fa-history me-2" style="color:#f87171;"></i>Finance Audit Trail</h5>
            <p class="mb-0 text-muted" style="font-size:.82rem;">Every financial modification logged with user, time, old value, new value, and reason.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.finance.verification-dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-shield-alt me-1"></i> Verification
            </a>
            <x-report-export-menu
                :print-url="route('admin.finance.finance-audit-trail.print', request()->all())"
                :pdf-url="route('admin.finance.finance-audit-trail.pdf', request()->all())"
                :excel-url="route('admin.finance.finance-audit-trail.excel', request()->all())"
                label="Export"
            />
        </div>
    </div>

    {{-- Summary Chips --}}
    <div class="row g-3 mb-4">
        @foreach([['label'=>'Total','count'=>$summary['total'],'icon'=>'fa-list','color'=>'dark'],['label'=>'Created','count'=>$summary['created'],'icon'=>'fa-plus','color'=>'success'],['label'=>'Updated','count'=>$summary['updated'],'icon'=>'fa-edit','color'=>'primary'],['label'=>'Deleted','count'=>$summary['deleted'],'icon'=>'fa-trash','color'=>'danger'],['label'=>'Reconciled','count'=>$summary['reconciled'],'icon'=>'fa-balance-scale','color'=>'info']] as $s)
        <div class="col">
            <div class="sum-chip">
                <i class="fas {{ $s['icon'] }} text-{{ $s['color'] }} mb-1"></i>
                <div class="fw-bold">{{ number_format($s['count']) }}</div>
                <div style="font-size:.72rem;color:#64748b;">{{ $s['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="filter-panel">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label fw-semibold small mb-1">Period</label>
                <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach(['today'=>'Today','week'=>'This Week','month'=>'This Month','year'=>'This Year','custom'=>'Custom Range'] as $v => $l)
                    <option value="{{ $v }}" {{ $period === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            @if($period === 'custom')
            <div class="col-md-2">
                <label class="form-label fw-semibold small mb-1">From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small mb-1">To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            @endif
            <div class="col-md-2">
                <label class="form-label fw-semibold small mb-1">Action</label>
                <select name="action" class="form-select form-select-sm">
                    <option value="">All Actions</option>
                    @foreach(['created','updated','deleted','reconciled','adjusted','waived'] as $a)
                    <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ ucfirst($a) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small mb-1">Entity</label>
                <select name="entity_type" class="form-select form-select-sm">
                    <option value="">All Entities</option>
                    @foreach(['payment','design_task','order','reconciliation','expense'] as $e)
                    <option value="{{ $e }}" {{ request('entity_type') === $e ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$e)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small mb-1">User</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">All Users</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-dark btn-sm px-3">Filter</button>
                <a href="{{ route('admin.finance.finance-audit-trail.index') }}" class="btn btn-light btn-sm">Reset</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 fat-table">
                <thead>
                    <tr>
                        <th>Date / Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Transaction Date</th>
                        <th>Old Value</th>
                        <th>New Value</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                    <tr>
                        <td class="text-muted">{{ $entry->created_at->format('d M Y') }}<br><small>{{ $entry->created_at->format('H:i:s') }}</small></td>
                        <td class="fw-semibold">{{ $entry->user?->name ?? 'System' }}</td>
                        <td><span class="action-pill bg-{{ $entry->action_color }} text-white">{{ $entry->action_label }}</span></td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-dark">{{ ucwords(str_replace('_',' ',$entry->entity_type)) }}</span>
                            @if($entry->entity_id)<small class="text-muted ms-1">#{{ $entry->entity_id }}</small>@endif
                        </td>
                        <td class="text-muted">{{ $entry->transaction_date?->format('d M Y') ?? '—' }}</td>
                        <td>
                            @if($entry->old_value)
                            <span class="val-code" title="{{ json_encode($entry->old_value, JSON_PRETTY_PRINT) }}">{{ Str::limit(json_encode($entry->old_value), 40) }}</span>
                            @else <span class="text-muted">—</span> @endif
                        </td>
                        <td>
                            @if($entry->new_value)
                            <span class="val-code" title="{{ json_encode($entry->new_value, JSON_PRETTY_PRINT) }}">{{ Str::limit(json_encode($entry->new_value), 40) }}</span>
                            @else <span class="text-muted">—</span> @endif
                        </td>
                        <td style="max-width:200px;" class="text-muted">{{ Str::limit($entry->reason, 60) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x d-block mb-2 opacity-25"></i>No audit entries for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($entries->hasPages())
        <div class="card-footer bg-white border-0 py-3">{{ $entries->links() }}</div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Expand value on click
    document.querySelectorAll('.val-code').forEach(el => {
        el.addEventListener('click', () => {
            const full = el.getAttribute('title');
            alert(full);
        });
    });
</script>
@endpush
