@extends('layouts.admin')

@section('title', 'Customer Reports & Analytics')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-users text-danger me-2"></i>Customer Reports
            </h4>
        </div>
        <x-report-export-menu
            :print-url="route('admin.reports.customers.print', request()->all())"
            :pdf-url="route('admin.reports.customers.pdf', request()->all())"
            :excel-url="route('admin.reports.customers.excel', request()->all())"
        />
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light">
            <form method="GET" action="{{ route('admin.reports.customers') }}" class="row g-2 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold mb-1">Date From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold mb-1">Date To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold mb-1">Branch</label>
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ (string)$filters['branch_id'] === (string)$branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold mb-1">Salesperson</label>
                    <select name="salesperson_id" class="form-select form-select-sm">
                        <option value="">All Salespeople</option>
                        @foreach($salespeople as $sp)
                            <option value="{{ $sp->id }}" {{ (string)$filters['salesperson_id'] === (string)$sp->id ? 'selected' : '' }}>{{ $sp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold mb-1">Source</label>
                    <select name="source" class="form-select form-select-sm">
                        <option value="">All Sources</option>
                        @foreach($sources as $source)
                            <option value="{{ $source }}" {{ (string)$filters['source'] === (string)$source ? 'selected' : '' }}>{{ $source }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-1 d-flex gap-2">
                    <button class="btn btn-danger btn-sm w-100" type="submit">Apply</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h6 class="fw-bold mb-0"><i class="fas fa-user-plus text-success me-2"></i>New Customers Report</h6>
            <small class="text-muted">Filters: Date range, Branch, Salesperson, Source</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Branch</th>
                            <th>Salesperson</th>
                            <th>Source</th>
                            <th>Registered On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($newCustomers as $customer)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.customers.show', $customer->id) }}" class="text-decoration-none fw-semibold text-dark">
                                        {{ $customer->name }}
                                    </a>
                                </td>
                                <td>{{ $customer->phone }}</td>
                                <td>{{ $customer->branch?->name ?? 'Unassigned' }}</td>
                                <td>{{ $customer->addedBy?->name ?? 'Unassigned' }}</td>
                                <td>{{ $customer->customer_source ?? 'Unspecified' }}</td>
                                <td>{{ $customer->created_at?->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No new customers found for selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h6 class="fw-bold mb-0"><i class="fas fa-redo text-primary me-2"></i>Returning Customers Report</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Customer Name</th>
                            <th class="text-center">Number of Purchases</th>
                            <th>Last Purchase Date</th>
                            <th class="text-end">Total Contribution (TZS)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returningCustomers as $customer)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.customers.show', $customer->id) }}" class="text-decoration-none fw-semibold text-dark">{{ $customer->name }}</a>
                                </td>
                                <td class="text-center">{{ number_format(max((int)$customer->purchase_count, (int)$customer->total_orders)) }}</td>
                                <td>{{ $customer->last_order_date ? \Carbon\Carbon::parse($customer->last_order_date)->format('d M Y') : 'N/A' }}</td>
                                <td class="text-end fw-bold">{{ number_format((float)$customer->total_spent, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No returning customers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h6 class="fw-bold mb-0"><i class="fas fa-coins text-warning me-2"></i>Customer Contribution Report</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Customer Name</th>
                            <th class="text-end">Total Sales Amount</th>
                            <th class="text-end">Total Payments</th>
                            <th class="text-end">Outstanding Debt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contributionRows as $row)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.customers.show', $row['customer']->id) }}" class="text-decoration-none fw-semibold text-dark">{{ $row['customer']->name }}</a>
                                </td>
                                <td class="text-end">{{ number_format($row['total_sales_amount'], 2) }}</td>
                                <td class="text-end text-success fw-semibold">{{ number_format($row['total_payments'], 2) }}</td>
                                <td class="text-end {{ $row['outstanding_debt'] > 0 ? 'text-danger fw-semibold' : '' }}">{{ number_format($row['outstanding_debt'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No contribution data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h6 class="fw-bold mb-0"><i class="fas fa-share-alt text-info me-2"></i>Customer Source Report</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Source</th>
                            <th class="text-center">Number of Customers</th>
                            <th class="text-end">Sales Generated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sourceRows as $row)
                            <tr>
                                <td>{{ $row->source }}</td>
                                <td class="text-center fw-semibold">{{ number_format($row->customers_count) }}</td>
                                <td class="text-end fw-semibold">{{ number_format((float)$row->sales_generated, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No source data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="fw-bold mb-0"><i class="fas fa-chart-line text-danger me-2"></i>Customer Growth Report</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Month</th>
                                    <th class="text-end">New Registrations</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($growthData as $month)
                                    <tr>
                                        <td>{{ $month['month_label'] }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($month['new_registrations']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="fw-bold mb-0"><i class="fas fa-code-branch text-primary me-2"></i>Branch Comparison</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Branch</th>
                                    <th class="text-end">New Registrations</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branchGrowth as $branch)
                                    <tr>
                                        <td>{{ $branch->branch_name }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($branch->customers_count) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-4">No branch growth data available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h6 class="fw-bold mb-0"><i class="fas fa-heartbeat text-success me-2"></i>Customer Retention Report</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="border rounded p-3 h-100">
                        <div class="small text-muted text-uppercase fw-bold">Active Customers</div>
                        <div class="fs-3 fw-bold text-success">{{ number_format($retentionSummary['active']) }}</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="border rounded p-3 h-100">
                        <div class="small text-muted text-uppercase fw-bold">Lost Customers</div>
                        <div class="fs-3 fw-bold text-danger">{{ number_format($retentionSummary['lost']) }}</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="border rounded p-3 h-100">
                        <div class="small text-muted text-uppercase fw-bold">Returning Customers</div>
                        <div class="fs-3 fw-bold text-primary">{{ number_format($retentionSummary['returning']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
