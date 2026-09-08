@extends('admin.reports.exports.base')

@section('report_content')
    <div class="section-title">Retention Summary</div>
    <table class="stats-grid">
        <tr>
            <td class="stats-card" style="width: 33%;">
                <div class="stats-label">Active Customers</div>
                <div class="stats-value">{{ number_format($retentionSummary['active']) }}</div>
            </td>
            <td class="stats-card" style="width: 33%;">
                <div class="stats-label">Lost Customers</div>
                <div class="stats-value">{{ number_format($retentionSummary['lost']) }}</div>
            </td>
            <td class="stats-card" style="width: 34%;">
                <div class="stats-label">Returning Customers</div>
                <div class="stats-value">{{ number_format($retentionSummary['returning']) }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">New Customers Report</div>
    <table>
        <thead>
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
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->branch?->name ?? 'Unassigned' }}</td>
                    <td>{{ $customer->addedBy?->name ?? 'Unassigned' }}</td>
                    <td>{{ $customer->customer_source ?? 'Unspecified' }}</td>
                    <td>{{ optional($customer->created_at)->format('d M Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Returning Customers Report</div>
    <table>
        <thead>
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
                    <td>{{ $customer->name }}</td>
                    <td class="text-center">{{ number_format(max((int) $customer->purchase_count, (int) $customer->total_orders)) }}</td>
                    <td>{{ $customer->last_order_date ? \Carbon\Carbon::parse($customer->last_order_date)->format('d M Y') : 'N/A' }}</td>
                    <td class="text-end">{{ number_format((float) $customer->total_spent, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Customer Contribution Report</div>
    <table>
        <thead>
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
                    <td>{{ $row['customer']->name }}</td>
                    <td class="text-end">{{ number_format($row['total_sales_amount'], 2) }}</td>
                    <td class="text-end">{{ number_format($row['total_payments'], 2) }}</td>
                    <td class="text-end">{{ number_format($row['outstanding_debt'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Customer Source Report</div>
    <table>
        <thead>
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
                    <td class="text-center">{{ number_format($row->customers_count) }}</td>
                    <td class="text-end">{{ number_format((float) $row->sales_generated, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Customer Growth Report</div>
    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th class="text-end">New Registrations</th>
            </tr>
        </thead>
        <tbody>
            @foreach($growthData as $month)
                <tr>
                    <td>{{ $month['month_label'] }}</td>
                    <td class="text-end">{{ number_format($month['new_registrations']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Branch Comparison</div>
    <table>
        <thead>
            <tr>
                <th>Branch</th>
                <th class="text-end">New Registrations</th>
            </tr>
        </thead>
        <tbody>
            @forelse($branchGrowth as $branch)
                <tr>
                    <td>{{ $branch->branch_name }}</td>
                    <td class="text-end">{{ number_format($branch->customers_count) }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="text-center">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
