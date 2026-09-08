@extends('layouts.admin')

@section('title', 'Sales Targets')

@push('styles')
<style>
    body { font-size: 13px; }
    .card { border-radius: 12px; }

    /* ── Stat summary cards ── */
    .tgt-stat {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        cursor: pointer;
        transition: box-shadow 0.18s, border-color 0.18s, transform 0.18s;
        text-decoration: none;
        color: inherit;
    }
    .tgt-stat:hover { box-shadow: 0 4px 14px rgba(0,0,0,0.09); transform: translateY(-2px); color: inherit; }
    .tgt-stat.active-filter { border-width: 2px; }
    .tgt-stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
    .tgt-stat-val { font-size: 1.25rem; font-weight: 800; line-height: 1.1; }
    .tgt-stat-lbl { font-size: 10px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.4px; }

    /* ── Tab pills ── */
    .tgt-tabs { display: flex; gap: 6px; flex-wrap: wrap; }
    .tgt-tab {
        padding: 5px 14px; border-radius: 20px; font-size: 11px; font-weight: 700;
        border: 1.5px solid #e2e8f0; color: #64748b; background: #fff;
        cursor: pointer; text-decoration: none; transition: all 0.15s;
    }
    .tgt-tab:hover { border-color: #cbd5e1; color: #334155; }
    .tgt-tab.active { background: #0f172a; color: #fff; border-color: #0f172a; }

    /* ── Type badge ── */
    .type-dept { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 6px; font-size: 10px; font-weight: 700; padding: 2px 8px; }
    .type-saler { background: #fdf4ff; color: #7c3aed; border: 1px solid #e9d5ff; border-radius: 6px; font-size: 10px; font-weight: 700; padding: 2px 8px; }

    /* ── Period badge ── */
    .period-badge { border-radius: 6px; font-size: 10px; font-weight: 700; padding: 2px 8px; }

    /* ── Status badge ── */
    .status-active  { background: #dcfce7; color: #16a34a; border-radius: 6px; font-size: 10px; font-weight: 700; padding: 2px 8px; }
    .status-upcoming { background: #e0f2fe; color: #0369a1; border-radius: 6px; font-size: 10px; font-weight: 700; padding: 2px 8px; }
    .status-expired  { background: #f1f5f9; color: #64748b; border-radius: 6px; font-size: 10px; font-weight: 700; padding: 2px 8px; }

    /* ── Table ── */
    .table thead th { font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: #94a3b8; background: #f8fafc; border: none; padding: 10px 14px; }
    .table td { font-size: 12.5px; padding: 11px 14px !important; vertical-align: middle; }
    .table tbody tr:hover { background: #f8fafc; }

    /* ── Modal target-type toggle ── */
    .tgt-type-btn {
        flex: 1; padding: 12px 10px; border-radius: 10px; border: 2px solid #e2e8f0;
        background: #f8fafc; text-align: center; cursor: pointer; transition: all 0.18s;
        color: #64748b; user-select: none;
    }
    .tgt-type-btn.selected { border-color: #0f172a; background: #0f172a; color: #fff; }
    .tgt-type-btn .tbt-icon { font-size: 20px; display: block; margin-bottom: 4px; }
    .tgt-type-btn .tbt-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; }
    .tgt-type-btn .tbt-sub { font-size: 10px; opacity: 0.65; margin-top: 2px; }

    /* ── Amount input big ── */
    .amount-input-wrap { position: relative; }
    .amount-input-wrap .currency-pfx { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 11px; font-weight: 700; color: #64748b; pointer-events: none; }
    .amount-input-wrap input { padding-left: 44px; font-size: 1.1rem; font-weight: 700; height: 46px; }

    /* ── Period selector pills ── */
    .period-pill-group { display: flex; gap: 6px; flex-wrap: wrap; }
    .period-pill { padding: 5px 13px; border-radius: 20px; border: 1.5px solid #e2e8f0; font-size: 11px; font-weight: 700; color: #64748b; background: #fff; cursor: pointer; transition: all 0.15s; }
    .period-pill.selected { background: #0f172a; color: #fff; border-color: #0f172a; }

    /* ── Recurrence toggle ── */
    .recurrence-toggle { display: flex; align-items: center; gap: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; }

    @media (max-width: 767px) {
        .tgt-stat { padding: 10px 12px; }
        .tgt-stat-val { font-size: 1rem; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-3" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- ── Header ── --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-0">Sales Targets</h4>
            <p class="text-muted mb-0" style="font-size:12px;">Manage department-wide and individual saler targets</p>
        </div>
        <div class="d-flex gap-2">
            <x-report-export-menu
                :print-url="route('admin.sales-dept.targets.print', request()->all())"
                :pdf-url="route('admin.sales-dept.targets.pdf', request()->all())"
                :excel-url="route('admin.sales-dept.targets.excel', request()->all())"
            />
            @if(in_array(auth()->user()->role, ['admin','super_admin','manager','accountant']))
            <button class="btn btn-dark btn-sm fw-bold rounded-3 px-3" data-bs-toggle="modal" data-bs-target="#addTargetModal" data-no-global-handler>
                <i class="fas fa-plus me-1"></i>New Target
            </button>
            @endif
        </div>
    </div>

    {{-- ── Summary stat cards ── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <a href="{{ request()->fullUrlWithQuery(['target_type'=>'all']) }}" class="tgt-stat {{ !request('target_type') || request('target_type')=='all' ? 'active-filter border-dark' : '' }}">
                <div class="tgt-stat-icon bg-dark text-white"><i class="fas fa-bullseye"></i></div>
                <div>
                    <div class="tgt-stat-val">{{ $targetStats['total'] }}</div>
                    <div class="tgt-stat-lbl">Total Targets</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ request()->fullUrlWithQuery(['target_type'=>'all']) }}" class="tgt-stat">
                <div class="tgt-stat-icon bg-success-subtle text-success"><i class="fas fa-circle-check"></i></div>
                <div>
                    <div class="tgt-stat-val text-success">{{ $targetStats['active'] }}</div>
                    <div class="tgt-stat-lbl">Active Now</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ request()->fullUrlWithQuery(['target_type'=>'department']) }}" class="tgt-stat {{ request('target_type')=='department' ? 'active-filter border-primary' : '' }}">
                <div class="tgt-stat-icon bg-primary-subtle text-primary"><i class="fas fa-building"></i></div>
                <div>
                    <div class="tgt-stat-val text-primary">{{ $targetStats['department'] }}</div>
                    <div class="tgt-stat-lbl">Dept Targets</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ request()->fullUrlWithQuery(['target_type'=>'saler']) }}" class="tgt-stat {{ request('target_type')=='saler' ? 'active-filter border-purple' : '' }}" style="{{ request('target_type')=='saler' ? 'border-color:#7c3aed' : '' }}">
                <div class="tgt-stat-icon" style="background:#fdf4ff;color:#7c3aed;"><i class="fas fa-user-tie"></i></div>
                <div>
                    <div class="tgt-stat-val" style="color:#7c3aed;">{{ $targetStats['saler'] }}</div>
                    <div class="tgt-stat-lbl">Saler Targets</div>
                </div>
            </a>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.sales-dept.targets') }}" method="GET" class="row g-2 align-items-end" data-no-global-handler>
                <input type="hidden" name="target_type" value="{{ request('target_type','all') }}">

                <div class="col-6 col-md-2">
                    <label class="form-label fw-bold mb-1" style="font-size:10px;text-transform:uppercase;letter-spacing:.4px;">Type</label>
                    <select name="target_type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" {{ request('target_type','all')=='all' ? 'selected' : '' }}>All Types</option>
                        <option value="department" {{ request('target_type')=='department' ? 'selected' : '' }}>Department</option>
                        <option value="saler" {{ request('target_type')=='saler' ? 'selected' : '' }}>Saler</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label fw-bold mb-1" style="font-size:10px;text-transform:uppercase;letter-spacing:.4px;">Department</label>
                    <select name="department_id" class="form-select form-select-sm">
                        <option value="all">All Depts</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id')==$dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label fw-bold mb-1" style="font-size:10px;text-transform:uppercase;letter-spacing:.4px;">Salesperson</label>
                    <select name="seller_id" class="form-select form-select-sm">
                        <option value="all">All Salers</option>
                        @foreach($sellers as $s)
                            <option value="{{ $s->id }}" {{ request('seller_id')==$s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label fw-bold mb-1" style="font-size:10px;text-transform:uppercase;letter-spacing:.4px;">Period</label>
                    <select name="period" class="form-select form-select-sm">
                        <option value="all">All Periods</option>
                        <option value="daily" {{ request('period')=='daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ request('period')=='weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ request('period')=='monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarterly" {{ request('period')=='quarterly' ? 'selected' : '' }}>Quarterly</option>
                        <option value="yearly" {{ request('period')=='yearly' ? 'selected' : '' }}>Yearly</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label fw-bold mb-1" style="font-size:10px;text-transform:uppercase;letter-spacing:.4px;">Date From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>

                <div class="col-6 col-md-1">
                    <button type="submit" class="btn btn-dark btn-sm w-100 fw-bold rounded-3">Apply</button>
                </div>
                <div class="col-6 col-md-1">
                    <a href="{{ route('admin.sales-dept.targets') }}" class="btn btn-light btn-sm w-100 fw-bold rounded-3 border">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Targets Table ── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <span class="fw-bold" style="font-size:13px;">
                @if(request('target_type')=='department')
                    <i class="fas fa-building me-2 text-primary"></i>Department Targets
                @elseif(request('target_type')=='saler')
                    <i class="fas fa-user-tie me-2" style="color:#7c3aed;"></i>Saler Targets
                @else
                    <i class="fas fa-list me-2 text-muted"></i>All Targets
                @endif
            </span>
            <span class="badge bg-light text-dark border" style="font-size:11px;">{{ $targets->total() }} record{{ $targets->total()!=1?'s':'' }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Type / Scope</th>
                            <th>Department</th>
                            <th>Period</th>
                            <th class="text-end">Target Amount</th>
                            <th>Date Range</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Recurrence</th>
                            <th class="pe-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($targets as $target)
                        <tr>
                            {{-- Type + scope --}}
                            <td class="ps-4">
                                @if($target->seller_id)
                                    <span class="type-saler"><i class="fas fa-user-tie me-1"></i>Saler</span>
                                    <div class="fw-bold text-dark mt-1" style="font-size:13px;">{{ $target->seller->name ?? '—' }}</div>
                                @else
                                    <span class="type-dept"><i class="fas fa-building me-1"></i>Department</span>
                                    <div class="fw-bold text-dark mt-1" style="font-size:13px;"><i class="fas fa-users me-1 text-muted" style="font-size:10px;"></i>Whole Dept</div>
                                @endif
                            </td>

                            {{-- Department --}}
                            <td>
                                @if($target->department)
                                    <span class="fw-semibold">{{ $target->department->name }}</span>
                                @else
                                    <span class="text-muted">All Depts</span>
                                @endif
                            </td>

                            {{-- Period --}}
                            <td>
                                @php
                                    $periodColors = ['daily'=>'bg-warning-subtle text-warning','weekly'=>'bg-info-subtle text-info','monthly'=>'bg-primary-subtle text-primary','quarterly'=>'bg-success-subtle text-success','yearly'=>'bg-danger-subtle text-danger'];
                                    $pc = $periodColors[$target->period] ?? 'bg-secondary-subtle text-secondary';
                                @endphp
                                <span class="period-badge {{ $pc }}">{{ ucfirst($target->period) }}</span>
                            </td>

                            {{-- Target amount --}}
                            <td class="text-end">
                                <div class="fw-bold" style="font-size:14px;">TZS {{ number_format($target->target_amount) }}</div>
                            </td>

                            {{-- Date range --}}
                            <td>
                                <div style="font-size:11px;color:#64748b;">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    {{ $target->start_date->format('d M Y') }}
                                </div>
                                <div style="font-size:11px;color:#94a3b8;">
                                    → {{ $target->end_date->format('d M Y') }}
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="text-center">
                                @if($target->start_date <= now() && $target->end_date >= now())
                                    <span class="status-active"><i class="fas fa-circle me-1" style="font-size:6px;"></i>Active</span>
                                @elseif($target->start_date > now())
                                    <span class="status-upcoming"><i class="fas fa-clock me-1" style="font-size:9px;"></i>Upcoming</span>
                                @else
                                    <span class="status-expired"><i class="fas fa-check me-1" style="font-size:9px;"></i>Expired</span>
                                @endif
                            </td>

                            {{-- Recurrence --}}
                            <td class="text-center">
                                @if($target->recurrence_enabled)
                                    <span class="badge bg-dark text-white" style="font-size:10px;border-radius:6px;padding:3px 8px;"><i class="fas fa-sync-alt me-1"></i>Auto</span>
                                @elseif($target->recurrence_source_id)
                                    <span class="badge bg-light text-muted border" style="font-size:10px;border-radius:6px;padding:3px 8px;"><i class="fas fa-link me-1"></i>Generated</span>
                                @else
                                    <span class="text-muted" style="font-size:11px;">—</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    @if(in_array(auth()->user()->role, ['admin','super_admin','manager','accountant']))
                                    <button class="btn btn-sm btn-outline-primary rounded-3" style="padding:3px 9px;"
                                        data-bs-toggle="modal" data-bs-target="#editModal{{ $target->id }}" data-no-global-handler>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger rounded-3" style="padding:3px 9px;"
                                        onclick="confirmDelete({{ $target->id }})" data-no-global-handler>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $target->id }}" action="{{ route('admin.sales-dept.targets.destroy', $target->id) }}" method="POST" class="d-none" data-no-global-handler>
                                        @csrf @method('DELETE')
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-bullseye fa-3x mb-3 opacity-25"></i>
                                    <p class="fw-bold mb-1">No targets found</p>
                                    <p class="small mb-0">Create a new target using the button above.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($targets->hasPages())
        <div class="card-footer bg-white border-0 py-3 px-4">
            {{ $targets->links() }}
        </div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════
     ADD TARGET MODAL
══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="addTargetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div>
                    <h5 class="fw-bold mb-0">New Sales Target</h5>
                    <p class="text-muted mb-0" style="font-size:11px;">Choose type, then fill in the details</p>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.sales-dept.targets.store') }}" method="POST" data-no-global-handler id="addTargetForm">
                @csrf
                <div class="modal-body px-4 py-3">

                    {{-- Target type toggle --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-2" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Target Type</label>
                        <div class="d-flex gap-3">
                            <div class="tgt-type-btn selected" id="addTypeDept" onclick="setAddType('department')">
                                <span class="tbt-icon">🏢</span>
                                <span class="tbt-label">Department</span>
                                <span class="tbt-sub">Whole dept goal</span>
                            </div>
                            <div class="tgt-type-btn" id="addTypeSaler" onclick="setAddType('saler')">
                                <span class="tbt-icon">👤</span>
                                <span class="tbt-label">Saler</span>
                                <span class="tbt-sub">Individual goal</span>
                            </div>
                        </div>
                    </div>

                    {{-- Department --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Department <span class="text-danger">*</span></label>
                        <select name="department_id" id="addDeptSelect" class="form-select" onchange="filterSalersByDept('add')">
                            <option value="">— Select Department —</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Saler (hidden for department type) --}}
                    <div class="mb-3" id="addSalerWrap" style="display:none;">
                        <label class="form-label fw-bold mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Salesperson <span class="text-danger">*</span></label>
                        <select name="seller_id" id="addSellerSelect" class="form-select">
                            <option value="">— Select Salesperson —</option>
                            @foreach($sellers as $s)
                                <option value="{{ $s->id }}" data-dept="{{ $s->department_id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text" style="font-size:10px;">Showing salers for the selected department.</div>
                    </div>

                    {{-- Target Amount --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Target Amount (TZS) <span class="text-danger">*</span></label>
                        <div class="amount-input-wrap">
                            <span class="currency-pfx">TZS</span>
                            <input type="number" name="target_amount" class="form-control" placeholder="0" min="0" required>
                        </div>
                    </div>

                    {{-- Period --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Period <span class="text-danger">*</span></label>
                        <div class="period-pill-group" id="addPeriodGroup">
                            @foreach(['daily'=>'Daily','weekly'=>'Weekly','monthly'=>'Monthly','quarterly'=>'Quarterly','yearly'=>'Yearly'] as $val=>$lbl)
                            <span class="period-pill {{ $val=='monthly'?'selected':'' }}" onclick="selectPeriod('add','{{ $val }}',this)">{{ $lbl }}</span>
                            @endforeach
                        </div>
                        <input type="hidden" name="period" id="addPeriodInput" value="monthly">
                    </div>

                    {{-- Recurrence --}}
                    <div class="recurrence-toggle">
                        <input class="form-check-input mt-0" type="checkbox" id="addRecurrence" name="recurrence_enabled" value="1">
                        <div class="ms-1">
                            <label class="form-check-label fw-bold mb-0" for="addRecurrence" style="font-size:12px;cursor:pointer;">
                                <i class="fas fa-sync-alt me-1 text-muted"></i>Auto-repeat each period
                            </label>
                            <div class="text-muted" style="font-size:10px;">System will create a new target automatically when this one ends.</div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                    <button type="button" class="btn btn-light rounded-3 px-4 fw-bold border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark rounded-3 px-4 fw-bold" data-no-global-handler>
                        <i class="fas fa-bullseye me-1"></i>Create Target
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     EDIT TARGET MODALS
══════════════════════════════════════════════════════ --}}
@foreach($targets as $target)
<div class="modal fade" id="editModal{{ $target->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div>
                    <h5 class="fw-bold mb-0">Edit Sales Target</h5>
                    <p class="text-muted mb-0" style="font-size:11px;">
                        @if($target->seller_id) Saler target · {{ $target->seller->name ?? '' }}
                        @else Department target · {{ $target->department->name ?? 'All Depts' }}
                        @endif
                    </p>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.sales-dept.targets.update', $target->id) }}" method="POST" data-no-global-handler>
                @csrf @method('PUT')
                <div class="modal-body px-4 py-3">

                    {{-- Target type toggle --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-2" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Target Type</label>
                        <div class="d-flex gap-3">
                            <div class="tgt-type-btn {{ !$target->seller_id ? 'selected' : '' }}" id="editTypeDept{{ $target->id }}" onclick="setEditType({{ $target->id }},'department')">
                                <span class="tbt-icon">🏢</span>
                                <span class="tbt-label">Department</span>
                                <span class="tbt-sub">Whole dept goal</span>
                            </div>
                            <div class="tgt-type-btn {{ $target->seller_id ? 'selected' : '' }}" id="editTypeSaler{{ $target->id }}" onclick="setEditType({{ $target->id }},'saler')">
                                <span class="tbt-icon">👤</span>
                                <span class="tbt-label">Saler</span>
                                <span class="tbt-sub">Individual goal</span>
                            </div>
                        </div>
                    </div>

                    {{-- Department --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Department</label>
                        <select name="department_id" id="editDept{{ $target->id }}" class="form-select" onchange="filterSalersByDept('edit{{ $target->id }}')">
                            <option value="">— Select Department —</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ $target->department_id==$dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Saler --}}
                    <div class="mb-3" id="editSalerWrap{{ $target->id }}" style="{{ $target->seller_id ? '' : 'display:none;' }}">
                        <label class="form-label fw-bold mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Salesperson</label>
                        <select name="seller_id" id="editSeller{{ $target->id }}" class="form-select">
                            <option value="">— Select Salesperson —</option>
                            @foreach($sellers as $s)
                                <option value="{{ $s->id }}" data-dept="{{ $s->department_id }}" {{ $target->seller_id==$s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Amount --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Target Amount (TZS)</label>
                        <div class="amount-input-wrap">
                            <span class="currency-pfx">TZS</span>
                            <input type="number" name="target_amount" class="form-control" value="{{ (int)$target->target_amount }}" min="0" required>
                        </div>
                    </div>

                    {{-- Period --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Period</label>
                        <div class="period-pill-group">
                            @foreach(['daily'=>'Daily','weekly'=>'Weekly','monthly'=>'Monthly','quarterly'=>'Quarterly','yearly'=>'Yearly'] as $val=>$lbl)
                            <span class="period-pill {{ $target->period==$val ? 'selected' : '' }}"
                                onclick="selectPeriod('edit{{ $target->id }}','{{ $val }}',this)">{{ $lbl }}</span>
                            @endforeach
                        </div>
                        <input type="hidden" name="period" id="editPeriodInput{{ $target->id }}" value="{{ $target->period }}">
                    </div>

                    {{-- Recurrence --}}
                    <div class="recurrence-toggle">
                        <input class="form-check-input mt-0" type="checkbox" id="editRec{{ $target->id }}" name="recurrence_enabled" value="1" {{ $target->recurrence_enabled ? 'checked' : '' }}>
                        <div class="ms-1">
                            <label class="form-check-label fw-bold mb-0" for="editRec{{ $target->id }}" style="font-size:12px;cursor:pointer;">
                                <i class="fas fa-sync-alt me-1 text-muted"></i>Auto-repeat each period
                            </label>
                            <div class="text-muted" style="font-size:10px;">System will create a new target automatically when this one ends.</div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                    <button type="button" class="btn btn-light rounded-3 px-4 fw-bold border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark rounded-3 px-4 fw-bold" data-no-global-handler>
                        <i class="fas fa-save me-1"></i>Update Target
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection

@push('scripts')
<script>
// ── Target type toggle (Add modal) ──
let addCurrentType = 'department';
function setAddType(type) {
    addCurrentType = type;
    document.getElementById('addTypeDept').classList.toggle('selected', type === 'department');
    document.getElementById('addTypeSaler').classList.toggle('selected', type === 'saler');
    const salerWrap = document.getElementById('addSalerWrap');
    salerWrap.style.display = type === 'saler' ? '' : 'none';
    const sellerSel = document.getElementById('addSellerSelect');
    if (type === 'saler') {
        sellerSel.required = true;
        filterSalersByDept('add');
    } else {
        sellerSel.required = false;
        sellerSel.value = '';
    }
}

// ── Target type toggle (Edit modals) ──
function setEditType(id, type) {
    document.getElementById('editTypeDept' + id).classList.toggle('selected', type === 'department');
    document.getElementById('editTypeSaler' + id).classList.toggle('selected', type === 'saler');
    const salerWrap = document.getElementById('editSalerWrap' + id);
    salerWrap.style.display = type === 'saler' ? '' : 'none';
    const sellerSel = document.getElementById('editSeller' + id);
    if (type === 'department') {
        sellerSel.value = '';
        sellerSel.required = false;
    } else {
        sellerSel.required = true;
        filterSalersByDept('edit' + id);
    }
}

// ── Filter salers by selected department ──
function filterSalersByDept(prefix) {
    const deptSel   = document.getElementById(prefix === 'add' ? 'addDeptSelect' : 'editDept' + prefix.replace('edit',''));
    const sellerSel = document.getElementById(prefix === 'add' ? 'addSellerSelect' : 'editSeller' + prefix.replace('edit',''));
    if (!deptSel || !sellerSel) return;
    const deptId = deptSel.value;
    Array.from(sellerSel.options).forEach(opt => {
        if (!opt.value) return;
        opt.hidden = deptId && opt.dataset.dept != deptId;
    });
    // Deselect if hidden
    if (sellerSel.selectedOptions[0]?.hidden) sellerSel.value = '';
}

// ── Period pill selector ──
function selectPeriod(prefix, value, el) {
    const group = el.closest('.period-pill-group');
    group.querySelectorAll('.period-pill').forEach(p => p.classList.remove('selected'));
    el.classList.add('selected');
    const inputId = prefix === 'add' ? 'addPeriodInput' : 'editPeriodInput' + prefix.replace('edit','');
    document.getElementById(inputId).value = value;
}

// ── Delete with SweetAlert ──
function confirmDelete(id) {
    Swal.fire({
        title: 'Delete Target?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel',
    }).then(result => {
        if (result.isConfirmed) document.getElementById('delete-form-' + id).submit();
    });
}
</script>
@endpush
