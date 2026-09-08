<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Customer Reports - Print</title>
<style>
    @media print { .no-print { display: none !important; } body { margin: 0; } }
    body { font-family: Arial, sans-serif; font-size: 12px; color: #111827; padding: 18px; }
    h1 { font-size: 22px; margin: 0 0 4px; }
    .report-header { border-bottom: 1.5px solid #111827; padding-bottom: 10px; margin-bottom: 12px; }
    .header-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; }
    .brand-logo { height: 52px; margin-bottom: 4px; }
    .company-name { margin: 0; font-size: 14px; font-weight: 800; color: #111827; letter-spacing: 0.2px; }
    .report-title { margin: 0; font-size: 18px; font-weight: 800; color: #dc2626; text-align: right; }
    .header-right { min-width: 320px; }
    .header-summary { margin-top: 6px; border: none; border-radius: 0; padding: 0; }
    .summary-line { display: flex; justify-content: space-between; align-items: center; font-size: 10px; padding: 2px 0; }
    .summary-line + .summary-line { border-top: 1px dashed #e5e7eb; }
    .summary-name { color: #6b7280; text-transform: uppercase; font-weight: 700; }
    .summary-num { font-weight: 800; font-size: 12px; color: #111827; }
    .meta { margin-bottom: 14px; color: #4b5563; font-size: 11px; }
    .section { margin-top: 16px; margin-bottom: 8px; font-weight: 700; font-size: 14px; border-bottom: 2px solid #111827; padding-bottom: 3px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    th { background: #111827; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; text-transform: uppercase; }
    td { border-bottom: 1px solid #d1d5db; padding: 6px 8px; }
    tr:nth-child(even) td { background: #f9fafb; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .btn-print { position: fixed; right: 14px; top: 14px; padding: 8px 14px; border: 0; border-radius: 4px; background: #111827; color: #fff; cursor: pointer; }
</style>
</head>
<body>
    <button class="btn-print no-print" onclick="window.print()">Print</button>

    <div class="report-header">
        <div class="header-row">
            <div>
                @include('partials.logo-print')
                <p class="company-name">CHIBOBRAND CO. LTD.</p>
            </div>
            <div class="header-right">
                <h1 class="report-title">Customer Reporting & Analytics</h1>
                <div class="header-summary">
                    <div class="summary-line">
                        <span class="summary-name">Active Customers</span>
                        <span class="summary-num">{{ number_format($retentionSummary['active']) }}</span>
                    </div>
                    <div class="summary-line">
                        <span class="summary-name">Lost Customers</span>
                        <span class="summary-num">{{ number_format($retentionSummary['lost']) }}</span>
                    </div>
                    <div class="summary-line">
                        <span class="summary-name">Returning Customers</span>
                        <span class="summary-num">{{ number_format($retentionSummary['returning']) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="meta">
        Date Range: <strong>{{ $startDate->format('d M Y') }}</strong> to <strong>{{ $endDate->format('d M Y') }}</strong>
        &nbsp;|&nbsp; Printed: {{ now()->format('d M Y H:i') }}
    </div>

    <div class="section">New Customers Report</div>
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
                    <td>{{ $customer->created_at?->format('d M Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section">Returning Customers Report</div>
    <table>
        <thead>
            <tr>
                <th>Customer Name</th>
                <th class="text-center">Number of Purchases</th>
                <th>Last Purchase Date</th>
                <th class="text-right">Total Contribution (TZS)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($returningCustomers as $customer)
                <tr>
                    <td>{{ $customer->name }}</td>
                    <td class="text-center">{{ number_format(max((int)$customer->purchase_count, (int)$customer->total_orders)) }}</td>
                    <td>{{ $customer->last_order_date ? \Carbon\Carbon::parse($customer->last_order_date)->format('d M Y') : 'N/A' }}</td>
                    <td class="text-right">{{ number_format((float)$customer->total_spent, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section">Customer Contribution Report</div>
    <table>
        <thead>
            <tr>
                <th>Customer Name</th>
                <th class="text-right">Total Sales Amount</th>
                <th class="text-right">Total Payments</th>
                <th class="text-right">Outstanding Debt</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contributionRows as $row)
                <tr>
                    <td>{{ $row['customer']->name }}</td>
                    <td class="text-right">{{ number_format($row['total_sales_amount'], 2) }}</td>
                    <td class="text-right">{{ number_format($row['total_payments'], 2) }}</td>
                    <td class="text-right">{{ number_format($row['outstanding_debt'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section">Customer Source Report</div>
    <table>
        <thead>
            <tr>
                <th>Source</th>
                <th class="text-center">Number of Customers</th>
                <th class="text-right">Sales Generated</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sourceRows as $row)
                <tr>
                    <td>{{ $row->source }}</td>
                    <td class="text-center">{{ number_format($row->customers_count) }}</td>
                    <td class="text-right">{{ number_format((float)$row->sales_generated, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section">Customer Growth Report</div>
    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th class="text-right">New Registrations</th>
            </tr>
        </thead>
        <tbody>
            @foreach($growthData as $month)
                <tr>
                    <td>{{ $month['month_label'] }}</td>
                    <td class="text-right">{{ number_format($month['new_registrations']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section">Branch Comparison</div>
    <table>
        <thead>
            <tr>
                <th>Branch</th>
                <th class="text-right">New Registrations</th>
            </tr>
        </thead>
        <tbody>
            @forelse($branchGrowth as $branch)
                <tr>
                    <td>{{ $branch->branch_name }}</td>
                    <td class="text-right">{{ number_format($branch->customers_count) }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="text-center">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
