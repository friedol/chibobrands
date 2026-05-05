@extends('layouts.admin')

@section('title', 'Sales Targets Management')

@section('content')
<div class="container-fluid py-3">
    <!-- Modern Header & Actions -->
    <div class="row mb-3 align-items-end">
        <div class="col-lg-6 mb-2 mb-lg-0">
            <h4 class="fw-bold mb-0">Sales Targets Management</h4>
            <p class="text-muted small mb-0">Assign and track revenue targets for the sales team</p>
        </div>
        <div class="col-lg-6 text-lg-end">
            <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                <button type="button" onclick="printDirect('{{ route('admin.sales-dept.targets.print', request()->all()) }}')" class="btn btn-dark btn-sm px-3 fw-bold shadow-sm">
                    <i class="fas fa-print me-1"></i> PRINT LOG
                </button>
                <button class="btn btn-outline-primary btn-sm px-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                    <i class="fas fa-filter me-1"></i> FILTERS 
                    @if(request()->anyFilled(['seller_id', 'department_id', 'period', 'date_from', 'date_to']))
                        <span class="badge bg-primary ms-1">Active</span>
                    @endif
                </button>
                
                @if(in_array(auth()->user()->role, ['admin', 'super_admin', 'manager']))
                <button class="btn btn-primary btn-sm px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addTargetModal" data-no-global-handler>
                    <i class="fas fa-plus-circle me-1"></i> NEW TARGET
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Modern Collapsable Filters -->
    <div class="collapse {{ request()->anyFilled(['seller_id', 'department_id', 'period', 'date_from', 'date_to']) ? 'show' : '' }} mb-4" id="filterCollapse">
        <div class="card border-0 shadow-sm border-top border-4 border-primary">
            <div class="card-body bg-light p-3">
                <form action="{{ route('admin.sales-dept.targets') }}" method="GET" class="row g-2" data-no-global-handler>
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Salesperson</label>
                        <select name="seller_id" class="form-select form-select-sm">
                            <option value="all">All Staff</option>
                            @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>{{ $seller->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Department</label>
                        <select name="department_id" class="form-select form-select-sm">
                            <option value="all">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Period</label>
                        <select name="period" class="form-select form-select-sm">
                            <option value="all">All Periods</option>
                            <option value="monthly" {{ request('period') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ request('period') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="annual" {{ request('period') == 'annual' ? 'selected' : '' }}>Annual</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">From Date</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">To Date</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>

                    <div class="col-12 col-md-1 d-flex align-items-end gap-1">
                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">SEARCH</button>
                        <a href="{{ route('admin.sales-dept.targets') }}" class="btn btn-outline-secondary btn-sm fw-bold"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0">Saler</th>
                                    <th class="py-3 border-0">Period</th>
                                    <th class="py-3 border-0 text-end">Target Amount</th>
                                    <th class="py-3 border-0">Start Date</th>
                                    <th class="py-3 border-0">End Date</th>
                                    <th class="py-3 border-0">Department</th>
                                    <th class="py-3 border-0 text-center">Status</th>
                                    <th class="pe-4 py-3 border-0 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($targets as $target)
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $target->seller->name }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-3 px-3">{{ ucfirst($target->period) }}</span>
                                    </td>
                                    <td class="text-end fw-bold">TZS {{ number_format($target->target_amount) }}</td>
                                    <td>{{ $target->start_date->format('M d, Y') }}</td>
                                    <td>{{ $target->end_date->format('M d, Y') }}</td>
                                    <td>{{ $target->department->name ?? 'All Departments' }}</td>
                                    <td class="text-center">
                                        @if($target->end_date >= now() && $target->start_date <= now())
                                            <span class="badge bg-success rounded-3 px-3">Active</span>
                                        @elseif($target->start_date > now())
                                            <span class="badge bg-info rounded-3 px-3">Upcoming</span>
                                        @else
                                            <span class="badge bg-secondary rounded-3 px-3">Expired</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-3" data-bs-toggle="modal" data-bs-target="#editTargetModal{{ $target->id }}" data-no-global-handler>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('admin.sales-dept.targets.destroy', $target->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this target?')" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" data-no-global-handler>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-bullseye fa-3x mb-3 opacity-25"></i>
                                            <p class="mb-0">No sales targets defined yet.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($targets->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $targets->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Target Modal -->
<div class="modal fade" id="addTargetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">Assign New Sales Target</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.sales-dept.targets.store') }}" method="POST" data-no-global-handler>
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select Salesperson</label>
                        <select name="seller_id" class="form-select border-0 bg-light" required>
                            @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}">{{ $seller->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Department (Optional)</label>
                        <select name="department_id" class="form-select border-0 bg-light">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Target Amount (TZS)</label>
                            <input type="number" name="target_amount" class="form-control border-0 bg-light" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Period</label>
                            <select name="period" class="form-select border-0 bg-light" required>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="annual">Annual</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Start Date</label>
                            <input type="date" name="start_date" class="form-control border-0 bg-light" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">End Date</label>
                            <input type="date" name="end_date" class="form-control border-0 bg-light" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4" data-no-global-handler>Save Target</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($targets as $target)
<!-- Edit Target Modal -->
<div class="modal fade" id="editTargetModal{{ $target->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">Edit Sales Target</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.sales-dept.targets.update', $target->id) }}" method="POST" data-no-global-handler>
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select Salesperson</label>
                        <select name="seller_id" class="form-select border-0 bg-light" required>
                            @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}" {{ $target->seller_id == $seller->id ? 'selected' : '' }}>{{ $seller->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Department (Optional)</label>
                        <select name="department_id" class="form-select border-0 bg-light">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ $target->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Target Amount (TZS)</label>
                            <input type="number" name="target_amount" class="form-control border-0 bg-light" value="{{ (int)$target->target_amount }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Period</label>
                            <select name="period" class="form-select border-0 bg-light" required>
                                <option value="monthly" {{ $target->period == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ $target->period == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="annual" {{ $target->period == 'annual' ? 'selected' : '' }}>Annual</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Start Date</label>
                            <input type="date" name="start_date" class="form-control border-0 bg-light" value="{{ $target->start_date->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">End Date</label>
                            <input type="date" name="end_date" class="form-control border-0 bg-light" value="{{ $target->end_date->format('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4" data-no-global-handler>Update Target</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection

@push('styles')
<style>
    body { font-size: 13px; font-family: 'Nunito Sans', sans-serif; }
    .card { border-radius: 12px; }
    .table thead th { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: #6c757d; background: #f8f9fa; border: none; }
    .table td { font-size: 13px; padding: 12px 15px !important; }
    .badge { font-weight: 600; padding: 0.5em 0.8em; font-size: 10px !important; }
    .form-control, .form-select, .btn { font-size: 13px !important; border-radius: 8px; }
    .x-small { font-size: 11px !important; }
    .modal-content { border-radius: 15px; }
</style>
@endpush
