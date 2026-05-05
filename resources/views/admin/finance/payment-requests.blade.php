@extends('layouts.admin')

@section('title', 'Payment Requests')

@section('content')
<div class="container-fluid">
    <!-- Modern Header & Actions -->
    <div class="row mb-3 align-items-end">
        <div class="col-lg-6 mb-2 mb-lg-0">
            <h4 class="fw-bold mb-0">Payment Requests</h4>

        </div>
        <div class="col-lg-6 text-lg-end">
            <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                <button type="button" onclick="printDirect('{{ route('admin.finance.payment-requests.print', request()->all()) }}')" class="btn btn-dark btn-sm px-3 fw-bold shadow-sm">
                    <i class="fas fa-print me-1"></i>
                </button>
                <button class="btn btn-outline-warning btn-sm px-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                    <i class="fas fa-filter me-1"></i>
                    @if(request()->anyFilled(['search', 'approval_status', 'payment_status', 'department_id', 'date_from', 'date_to']))
                        <span class="badge bg-warning text-dark ms-1">Active</span>
                    @endif
                </button>
                
                <button class="btn btn-warning btn-sm px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addRequestModal">
                    <i class="fas fa-hand-holding-usd me-1"></i>Create
                </button>
            </div>
        </div>
    </div>

    <!-- Modern Collapsable Filters -->
    <div class="collapse {{ request()->anyFilled(['search', 'approval_status', 'payment_status', 'department_id', 'date_from', 'date_to']) ? 'show' : '' }} mb-4" id="filterCollapse">
        <div class="card border-0 shadow-sm border-top border-4 border-warning">
            <div class="card-body bg-light p-3">
                <form action="{{ route('admin.finance.payment-requests.index') }}" method="GET" class="row g-2" data-no-global-handler>
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Search Reason</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Keywords..." value="{{ request('search') }}">
                        </div>
                    </div>
                    
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Approve Status</label>
                        <select name="approval_status" class="form-select form-select-sm">
                            <option value="all">All</option>
                            <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('approval_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Payment Status</label>
                        <select name="payment_status" class="form-select form-select-sm">
                            <option value="all">All</option>
                            <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Department</label>
                        <select name="department_id" class="form-select form-select-sm">
                            <option value="all">All Depts</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-1">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">From Date</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-6 col-md-1">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">To Date</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>

                    <div class="col-12 d-md-none mt-2">
                         <button type="submit" class="btn btn-warning btn-sm w-100 fw-bold">APPLY FILTERS</button>
                         <a href="{{ route('admin.finance.payment-requests.index') }}" class="btn btn-light btn-sm w-100 mt-2">RESET</a>
                    </div>
                    
                    <div class="col-md-auto d-none d-md-flex align-items-end ms-auto">
                        <div class="btn-group shadow-sm">
                            <button type="submit" class="btn btn-warning btn-sm px-3 fw-bold">APPLY</button>
                            <a href="{{ route('admin.finance.payment-requests.index') }}" class="btn btn-dark btn-sm px-3 fw-bold">RESET</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Reason</th>
                            <th>Requested For</th>
                            <th>Amount</th>
                            <th>Department</th>
                            <th>Created By</th>
                            <th>Approval Status</th>
                            <th>Payment Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $item)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $item->reason }}</td>
                            <td>
                                @if($item->requestedTo)
                                    <span class="badge bg-light text-dark border">{{ $item->requestedTo->name }}</span>
                                @elseif($item->requested_to_name)
                                    <span class="badge bg-light text-dark border">{{ $item->requested_to_name }} (Ext)</span>
                                @else
                                    <span class="text-muted small">---</span>
                                @endif
                            </td>
                            <td class="fw-bold">TZS {{ number_format($item->amount) }}</td>
                            <td>{{ $item->department->name }}</td>
                            <td>{{ $item->createdBy->name }}</td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $item->approval_status == 'approved' ? 'success' : ($item->approval_status == 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($item->approval_status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $item->payment_status == 'paid' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($item->payment_status) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                @if(in_array(auth()->user()->role, ['super_admin', 'manager', 'accountant']))
                                <div class="d-flex justify-content-end gap-2">
                                    {{-- Approval Action --}}
                                    @if($item->approval_status === 'pending')
                                    <form action="{{ route('admin.finance.payment-requests.status', $item->id) }}" method="POST" class="d-inline" data-no-global-handler>
                                        @csrf @method('PUT')
                                        <input type="hidden" name="approval_status" value="approved">
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Approve Request" data-no-global-handler>
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.finance.payment-requests.status', $item->id) }}" method="POST" class="d-inline" data-no-global-handler>
                                        @csrf @method('PUT')
                                        <input type="hidden" name="approval_status" value="rejected">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Reject Request" data-no-global-handler>
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                    @endif

                                    {{-- Payment Action --}}
                                    @if($item->approval_status === 'approved' && $item->payment_status === 'unpaid')
                                    <button type="button" class="btn btn-sm btn-outline-primary" title="Mark as Paid" 
                                            onclick="openPayModal({{ $item->id }}, '{{ $item->reason }}', {{ $item->amount }})">
                                        <i class="fas fa-receipt"></i>
                                    </button>
                                    @endif

                                    @if($item->approval_status !== 'pending' && ($item->approval_status === 'rejected' || $item->payment_status === 'paid'))
                                        <span class="text-muted small italic opacity-50">Done</span>
                                    @endif
                                </div>
                                @else
                                <span class="text-muted small">View Only</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No payment requests found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Add Request Modal -->
<div class="modal fade" id="addRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.finance.payment-requests.store') }}" method="POST" data-no-global-handler>
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Payment Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Requested For</label>
                        <div class="d-flex gap-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="requested_for_type" id="reqTypeInternal" value="internal" checked onchange="toggleReqType()">
                                <label class="form-check-label" for="reqTypeInternal">Internal Staff</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="requested_for_type" id="reqTypeExternal" value="external" onchange="toggleReqType()">
                                <label class="form-check-label" for="reqTypeExternal">External Person</label>
                            </div>
                        </div>

                        <div id="internalReqField">
                             <select class="form-select" name="requested_to_user_id" id="reqUserId">
                                <option value="">Select Staff Member...</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="externalReqField" class="d-none">
                            <input type="text" class="form-control" name="requested_to_name" id="reqUserName" placeholder="Enter Full Name">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason / Item</label>
                        <input type="text" class="form-control" name="reason" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (TZS)</label>
                        <input type="number" class="form-control" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select class="form-select" name="department_id" required>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning" data-no-global-handler>Submit Request</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Pay Request Modal -->
<div class="modal fade" id="payRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="payRequestForm" method="POST" data-no-global-handler>
            @csrf @method('PUT')
            <input type="hidden" name="payment_status" value="paid">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complete Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Confirm payment for: <strong id="payRequestReason"></strong></p>
                    <p class="mb-3">Amount: <strong id="payRequestAmount"></strong></p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Method</label>
                        <select class="form-select" name="payment_method" required>
                            <option value="Cash">Cash</option>
                            <option value="Mobile Money">Mobile Money</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" data-no-global-handler>Confirm Payment</button>
                </div>
            </div>
        </form>
    </div>
</div>
@push('styles')
<style>
    .container-fluid { font-size: 13px; }
    h4 { font-size: 1.25rem !important; font-weight: 700; }
    .table th { font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 700; color: #666; }
    .table td { font-size: 13px; }
    .btn { font-weight: 600; letter-spacing: 0.2px; }
    .form-control, .form-select { font-size: 13px !important; border-radius: 6px; }
    .x-small { font-size: 10px !important; }
    .card { border-radius: 10px; }
    .table-responsive { overflow: visible !important; }
</style>
@endpush
@push('scripts')
<script>
    function toggleReqType() {
        if (document.getElementById('reqTypeInternal').checked) {
            document.getElementById('internalReqField').classList.remove('d-none');
            document.getElementById('externalReqField').classList.add('d-none');
            document.getElementById('reqUserId').required = true;
            document.getElementById('reqUserName').required = false;
        } else {
            document.getElementById('internalReqField').classList.add('d-none');
            document.getElementById('externalReqField').classList.remove('d-none');
            document.getElementById('reqUserId').required = false;
            document.getElementById('reqUserName').required = true;
        }
    }

    function openPayModal(id, reason, amount) {
        const form = document.getElementById('payRequestForm');
        form.action = `/admin/finance/payment-requests/${id}/status`;
        document.getElementById('payRequestReason').innerText = reason;
        document.getElementById('payRequestAmount').innerText = 'TZS ' + new Intl.NumberFormat().format(amount);
        
        const myModal = new bootstrap.Modal(document.getElementById('payRequestModal'));
        myModal.show();
    }
</script>
@endpush
@endsection
