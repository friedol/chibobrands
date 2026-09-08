@extends('admin.reports.exports.base')

@section('report_content')
@php
    $totalPaid  = $rows->sum('amount_paid');
    $converted  = $rows->filter(fn($r) => $r->follow_up_status === 'converted')->count();
    $pending    = $rows->filter(fn($r) => ($r->follow_up_status ?? '') === 'pending')->count();
    $withOrders = $rows->filter(fn($r) => !is_null($r->product_ordered))->count();
@endphp

    {{-- Verification Note --}}
    <div style="font-size:9px;border-left:4px solid #dc2626;background:#f8fafc;padding:6px 10px;margin-bottom:12px;border:1px solid #e2e8f0;">
        <strong>System Verification Confirmation:</strong> This report represents official, system-tracked seller activity including lead follow-ups, conversions, design tasks, and payment records for the selected period.
    </div>

    {{-- Agent Summary Bar (table-based for DomPDF) --}}
    <div class="section-title">Seller Summary — {{ $saler->name }}</div>
    <table class="stats-grid" style="margin-bottom:14px;">
        <tr>
            <td style="width:20%;padding:10px;background:#fef2f2;border:1px solid #fca5a5;border-radius:4px;vertical-align:middle;">
                <div style="font-size:11px;font-weight:800;color:#0f172a;">{{ $saler->name }}</div>
                <div style="font-size:9px;color:#64748b;font-weight:600;">{{ ucfirst($saler->role) }}</div>
            </td>
            <td class="stats-card" style="width:16%;">
                <div class="stats-label">Total Leads</div>
                <div class="stats-value">{{ $rows->count() }}</div>
            </td>
            <td class="stats-card" style="width:16%;color:#166534;">
                <div class="stats-label">Converted</div>
                <div class="stats-value" style="color:#166534;">{{ $converted }}</div>
            </td>
            <td class="stats-card" style="width:16%;color:#b45309;">
                <div class="stats-label">Pending</div>
                <div class="stats-value" style="color:#b45309;">{{ $pending }}</div>
            </td>
            <td class="stats-card" style="width:16%;">
                <div class="stats-label">With Orders</div>
                <div class="stats-value" style="color:#1e40af;">{{ $withOrders }}</div>
            </td>
            <td class="stats-card" style="width:16%;">
                <div class="stats-label">Total Amount Paid</div>
                <div class="stats-value" style="font-size:11px;">TZS {{ number_format($totalPaid) }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Lead &amp; Activity Details</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Client</th>
                <th>Phone</th>
                <th>Source</th>
                <th>Follow Up Date</th>
                <th>Follow Up Status</th>
                <th>Product Asked</th>
                <th>Product Ordered</th>
                <th class="text-end">Amount Paid</th>
                <th>Work Status</th>
                <th>Delivery Status</th>
                <th>Feedback</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td class="fw-bold">{{ $row->client_name ?: '—' }}</td>
                <td>{{ $row->phone ?: '—' }}</td>
                <td>{{ $row->source ? ucfirst(str_replace('_', ' ', $row->source)) : '—' }}</td>
                <td>{{ $row->follow_up_date ? \Carbon\Carbon::parse($row->follow_up_date)->format('d M Y') : '—' }}</td>
                <td>{{ $row->follow_up_status ? ucfirst($row->follow_up_status) : '—' }}</td>
                <td>{{ $row->product_asked ?: '—' }}</td>
                <td>{{ $row->product_ordered ?: '—' }}</td>
                <td class="text-end">{{ $row->amount_paid !== null ? number_format($row->amount_paid) : '—' }}</td>
                <td>{{ $row->work_status ? ucwords(str_replace('_', ' ', $row->work_status)) : '—' }}</td>
                <td>{{ $row->delivery_status ? ucwords(str_replace('_', ' ', $row->delivery_status)) : '—' }}</td>
                <td>{{ $row->feedback ?: '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center">No activity data found for this period.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Summary by Lead Source</div>
    <table>
        <thead>
            <tr>
                <th>Source</th>
                <th class="text-center">Total</th>
                <th class="text-center">Paid</th>
                <th class="text-center">Unpaid</th>
                <th class="text-end">Amount Paid (TZS)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($summary as $grp)
            <tr>
                <td class="fw-bold">{{ $grp['label'] }}</td>
                <td class="text-center">{{ $grp['total'] }}</td>
                <td class="text-center">{{ $grp['paid_count'] }}</td>
                <td class="text-center">{{ $grp['unpaid_count'] }}</td>
                <td class="text-end">{{ number_format($grp['amount_paid']) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">No summary data available.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-end fw-bold">TOTAL AMOUNT PAID</td>
                <td class="text-end fw-bold">{{ number_format(collect($summary)->sum('amount_paid')) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($notice)
    <div class="section-title">Summary Notice</div>
    <div style="font-size:9px;white-space:pre-wrap;background:#fffbeb;border:1.5px solid #e2e8f0;border-radius:4px;padding:8px 12px;">{{ $notice }}</div>
    @endif

    {{-- Signature --}}
    <table style="width:100%;margin-top:30px;">
        <tr>
            <td style="width:40%;border-top:1px solid #475569;padding-top:4px;text-align:center;font-size:8px;font-weight:600;color:#334155;">
                {{ $saler->name }}<br><small style="font-weight:400;color:#64748b;">Seller Signature &amp; Date</small>
            </td>
            <td style="width:20%;"></td>
            <td style="width:40%;border-top:1px solid #475569;padding-top:4px;text-align:center;font-size:8px;font-weight:600;color:#334155;">
                Sales Manager / Supervisor<br><small style="font-weight:400;color:#64748b;">Authorized Signature &amp; Date</small>
            </td>
        </tr>
    </table>
@endsection
