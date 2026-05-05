@extends('layouts.admin')

@section('title', 'Cash Flow Management')

@section('content')
<div class="row mb-3 align-items-end no-print">
    <div class="col-lg-5 mb-2 mb-lg-0">
        <h4 class="fw-bold mb-0">Cash Flow Management</h4>
        <p class="text-muted small mb-0">Unified financial ledger for inflow and outflow</p>
    </div>
    <div class="col-lg-7 text-lg-end">
        <div class="d-flex flex-wrap justify-content-lg-end gap-2">
            <div class="btn-group shadow-sm">
                <a href="{{ route('admin.finance.cash-flow', ['view' => 'history']) }}" data-no-global-handler data-no-preloader
                   class="btn {{ $view === 'history' ? 'btn-primary' : 'btn-outline-primary' }} btn-sm px-3 fw-bold">
                    <i class="fas fa-list me-1"></i> Transactions
                </a>
                <a href="{{ route('admin.finance.cash-flow', ['view' => 'customers']) }}" data-no-global-handler data-no-preloader
                   class="btn {{ $view === 'customers' ? 'btn-primary' : 'btn-outline-primary' }} btn-sm px-3 fw-bold">
                    <i class="fas fa-users me-1"></i> Customers
                </a>
            </div>

            @if($view === 'history')
            <button type="button" onclick="printDirect('{{ route('admin.finance.cash-flow.print', request()->all()) }}')" class="btn btn-dark btn-sm px-3 fw-bold shadow-sm">
                <i class="fas fa-print me-1"></i> Print
            </button>
            @endif

            <button class="btn btn-outline-primary btn-sm px-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                <i class="fas fa-filter me-1"></i> Filter
                @if(request()->anyFilled(['search', 'department_id', 'payment_method', 'date_from', 'date_to']))
                    <span class="badge bg-primary ms-1">Active</span>
                @endif
            </button>
        </div>
    </div>
</div>

<div class="container-fluid py-0 px-0">
    <!-- Modern Collapsable Filters -->
    <div class="collapse {{ request()->anyFilled(['search', 'department_id', 'payment_method', 'date_from', 'date_to']) ? 'show' : '' }} mb-4 no-print" id="filterCollapse">
        <div class="card border-0 shadow-sm border-top border-4 border-primary">
            <div class="card-body bg-light p-3">
                <form action="{{ route('admin.finance.cash-flow') }}" method="GET" class="row g-2 align-items-end" data-no-global-handler>
                    <input type="hidden" name="view" value="{{ $view }}">
                    
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Time Period</label>
                        <select name="period" id="periodSelect" class="form-select form-select-sm">
                            <option value="today" {{ ($period ?? '') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="yesterday" {{ ($period ?? '') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                            <option value="week" {{ ($period ?? '') == 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ ($period ?? '') == 'month' ? 'selected' : '' }}>This Month</option>
                            <option value="year" {{ ($period ?? '') == 'year' ? 'selected' : '' }}>This Year</option>
                            <option value="custom" {{ ($period ?? '') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Search Ledger</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Keywords..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Department</label>
                        <select name="department_id" class="form-select form-select-sm">
                            <option value="">All Depts</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">Method</label>
                        <select name="payment_method" class="form-select form-select-sm">
                            <option value="">All Methods</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="mobile_money" {{ request('payment_method') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                            <option value="bank" {{ request('payment_method') == 'bank' ? 'selected' : '' }}>Bank</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-2 custom-date-group {{ ($period ?? '') == 'custom' ? '' : 'd-none' }}">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">From Date</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                    </div>

                    <div class="col-6 col-md-2 custom-date-group {{ ($period ?? '') == 'custom' ? '' : 'd-none' }}">
                        <label class="form-label fw-bold x-small text-uppercase mb-1">To Date</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                    </div>
                    
                    <div class="col-md-auto d-flex align-items-end ms-auto">
                        <div class="btn-group shadow-sm w-100">
                            <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">APPLY</button>
                            <a href="{{ route('admin.finance.cash-flow', ['view' => $view]) }}" class="btn btn-dark btn-sm px-3 fw-bold">RESET</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 16px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                @if($view === 'history')
                <table class="table table-hover align-middle mb-0 ledger-table">
                    <thead class="bg-light">
                        <tr class="x-small fw-bold">
                            <th class="ps-4">DATE & TIME</th>
                            <th class="text-center">FLOW</th>
                            <th>SOURCE / DESCRIPTION</th>
                            <th>DETAILS</th>
                            <th>METHOD</th>
                            <th class="text-end">AMOUNT</th>
                            <th class="text-center">REF</th>
                            <th class="pe-4 text-end">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pagedEntries as $entry)
                        <tr class="{{ $entry->entry_type === 'expense' ? 'flow-out' : 'flow-in' }}">
                            <td class="ps-4 py-3">
                                <div class="fw-bold">{{ $entry->date->format('d M, Y') }}</div>
                                <div class="x-small text-muted">{{ $entry->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="text-center">
                                @if($entry->entry_type === 'payment')
                                    <span class="badge badge-in"><i class="fas fa-arrow-down me-1"></i>IN</span>
                                @else
                                    <span class="badge badge-out"><i class="fas fa-arrow-up me-1"></i>OUT</span>
                                @endif
                            </td>
                            <td>
                                @if($entry->entry_type === 'payment')
                                    <div class="fw-bold text-dark">{{ $entry->customer->name ?? 'Unknown' }}</div>
                                    <div class="x-small text-muted">{{ $entry->customer->phone ?? '---' }}</div>
                                @else
                                    <div class="fw-bold text-danger">{{ $entry->category }}</div>
                                    <div class="x-small text-muted">{{ $entry->department->name ?? 'General' }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="small text-truncate" style="max-width: 250px;">
                                    @if($entry->entry_type === 'payment')
                                        {{ $entry->order ? 'Order #' . $entry->order->order_code : ($entry->design_task ? 'Task: ' . $entry->design_task->title : 'Manual Payment') }}
                                    @else
                                        {{ $entry->notes ?: '---' }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border x-small">{{ strtoupper(str_replace('_', ' ', $entry->payment_method)) }}</span>
                            </td>
                            <td class="text-end fw-bold {{ $entry->entry_type === 'payment' ? 'text-success' : 'text-danger' }}">
                                {{ $entry->entry_type === 'payment' ? '+' : '-' }} {{ number_format($entry->amount) }}
                            </td>
                            <td class="text-center">
                                <span class="font-monospace x-small text-muted">{{ $entry->entry_type === 'payment' ? ($entry->invoice_reference ?: '-') : 'EXP-'.$entry->id }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm">
                                    @if($entry->entry_type === 'payment')
                                        <button type="button" onclick="printDirect('{{ route('admin.finance.invoices.receipt', $entry->id) }}')" class="btn btn-sm btn-white border">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    @else
                                        <button type="button" onclick="printDirect('{{ route('admin.finance.expenses.voucher', $entry->id) }}')" class="btn btn-sm btn-white border">
                                            <i class="fas fa-receipt"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">No transactions found for selected criteria.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @else
                <table class="table table-hover align-middle mb-0 ledger-table">
                    <thead class="bg-light">
                        <tr class="x-small fw-bold">
                            <th class="ps-4">CUSTOMER NAME</th>
                            <th>PHONENUMBER</th>
                            <th class="text-end">TOTAL PAID</th>
                            <th class="text-end">OUTSTANDING</th>
                            <th class="text-center">LEAD</th>
                            <th class="pe-4 text-end">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr>
                            <td class="ps-4 py-3 fw-bold">{{ $customer->name }}</td>
                            <td>{{ $customer->phone }}</td>
                            <td class="text-end fw-bold text-success">{{ number_format($customer->total_paid) }}</td>
                            <td class="text-end fw-bold text-danger">{{ number_format($customer->unpaid_balance) }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $customer->lead_status === 'closed' ? 'success' : 'primary' }} x-small">{{ strtoupper($customer->lead_status ?? 'NEW') }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-sm btn-outline-primary px-3 rounded-pill bg-white shadow-sm x-small fw-bold">
                                    <i class="fas fa-eye me-1"></i> VIEW
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No customers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @endif
            </div>
            
            <div class="px-4 py-3 bg-light border-top">
                @if($view === 'history')
                    {{ $pagedEntries->appends(request()->query())->links() }}
                @else
                    {{ $customers->appends(request()->query())->links() }}
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800&display=swap');

    :root {
        --cash-in: #10b981;
        --cash-out: #ef4444;
        --report-primary: #3b82f6;
        --report-bg: #f8fafc;
        --glass-bg: rgba(255, 255, 255, 0.85);
        --glass-border: rgba(255, 255, 255, 0.3);
    }

    body {
        background-color: var(--report-bg);
        font-family: 'Nunito Sans', sans-serif;
    }

    .glass-morphism {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
    }

    .filter-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-left: 5px; margin-bottom: 5px; }

    .btn-preset {
        font-size: 13px; font-weight: 600; padding: 8px 18px;
        border: 1px solid #e2e8f0; background: white; color: #475569; transition: all 0.2s ease;
    }
    .btn-preset:hover { background: #f1f5f9; color: var(--report-primary); }
    .btn-preset.active {
        background: var(--report-primary); color: white; border-color: var(--report-primary);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .input-modern {
        display: flex; align-items: center; background: white;
        border: 1px solid #e2e8f0; border-radius: 10px; padding: 6px 12px; gap: 8px;
    }
    .input-modern .icon { color: var(--report-primary); font-size: 14px; }
    .input-modern input, .input-modern select { font-size: 13px; font-weight: 600; color: #1e293b; outline: none; border: none; background: transparent; }

    .btn-apply {
        background: #1e293b; color: white; font-weight: 700; padding: 10px 24px;
        border-radius: 10px; font-size: 13px; transition: all 0.2s ease; border: none;
    }
    .btn-apply:hover { background: #0f172a; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2); }

    .filter-divider { width: 1px; height: 35px; background: #e2e8f0; align-self: flex-end; margin-bottom: 5px; }
    .preset-group { border-radius: 10px; overflow: hidden; }

    .ledger-table th {
        font-size: 10px; text-transform: uppercase; letter-spacing: 1px;
        color: #64748b; background-color: #f8fafc; border-bottom: 1px solid #edf2f7;
        padding: 15px 10px !important;
    }
    .ledger-table td { font-size: 13px; border-bottom: 1px solid #f1f5f9; padding: 12px 10px !important; }
    
    .badge-in { background: #ecfdf5; color: #059669; border: 1px solid #10b98133; }
    .badge-out { background: #fef2f2; color: #dc2626; border: 1px solid #ef444433; }
    
    .flow-in { background-color: transparent; }
    .flow-out { background-color: #fafafa; }
    
    .btn-white { background: white; color: #475569; }
    .btn-white:hover { background: #f8fafc; color: var(--report-primary); }

    .x-small { font-size: 11px; }
</style>
@endpush
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const periodSelect = document.getElementById('periodSelect');
        const customDateGroups = document.querySelectorAll('.custom-date-group');
        
        if (periodSelect) {
            function toggleCustomDates() {
                if (periodSelect.value === 'custom') {
                    customDateGroups.forEach(el => el.classList.remove('d-none'));
                } else {
                    customDateGroups.forEach(el => el.classList.add('d-none'));
                }
            }

            // Run on init
            toggleCustomDates();

            // Run on change
            periodSelect.addEventListener('change', toggleCustomDates);
        }
    });
</script>
@endpush
@endsection
