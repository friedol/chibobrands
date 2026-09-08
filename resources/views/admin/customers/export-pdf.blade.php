<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Report Preview</title>
    <style>
        body { font-family: "Nunito Sans", Arial, sans-serif; font-size: 10px; color: #1e293b; margin: 0; background: #fff; }
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #0f172a;
            color: #fff;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .toolbar-title { font-size: 12px; font-weight: 700; letter-spacing: 0.03em; }
        .toolbar-actions { display: flex; gap: 8px; }
        .btn {
            border: 1px solid #334155;
            background: #1e293b;
            color: #fff;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 11px;
            cursor: pointer;
        }
        .btn:hover { background: #334155; }
        .btn-primary { background: #2563eb; border-color: #2563eb; }
        .btn-primary:hover { background: #1d4ed8; }
        .page-wrap { max-width: 1200px; margin: 0 auto; padding: 16px; }
        h2 { font-size: 16px; margin: 0 0 4px; }
        .sub { font-size: 10px; color: #64748b; margin-bottom: 12px; }

        .print-header-banner {
            border-bottom: 3px solid #dc2626;
            padding-bottom: 12px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }

        .company-logo {
            max-height: 45px;
            width: auto;
            margin-bottom: 4px;
        }

        .company-title {
            font-size: 16pt;
            font-weight: 800;
            color: #dc2626;
            letter-spacing: 0.4px;
            margin: 0;
        }

        .report-title {
            font-size: 11pt;
            font-weight: 800;
            color: #dc2626;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 6px;
        }

        .meta-box {
            background-color: transparent;
            border: none;
            border-radius: 0;
            padding: 0;
            font-size: 8.7pt;
            color: #334155;
            min-width: 260px;
            text-align: right;
        }

        .meta-box strong {
            color: #991b1b;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #1e293b; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: .04em; }
        td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; }
        tr:nth-child(even) td { background: #f8fafc; }
        .badge-new { background: #dcfce7; color: #15803d; padding: 2px 6px; border-radius: 4px; font-weight: 700; font-size: 8px; }
        .badge-repeated { background: #e0e7ff; color: #4338ca; padding: 2px 6px; border-radius: 4px; font-weight: 700; font-size: 8px; }
        .footer { margin-top: 16px; font-size: 9px; color: #94a3b8; }

        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            .toolbar { display: none !important; }
            .page-wrap { max-width: 100%; padding: 0; }
            .print-header-banner {
                flex-direction: row !important;
                align-items: flex-start !important;
            }
            .meta-box {
                width: auto !important;
                min-width: 260px;
                text-align: right !important;
            }
        }

        @media screen and (max-width: 768px) {
            .print-header-banner {
                flex-direction: column;
                align-items: flex-start;
            }

            .meta-box {
                min-width: 0;
                width: 100%;
                text-align: left;
            }
        }
    </style>
</head>
<body>
<div class="toolbar">
    <div class="toolbar-title">CUSTOMER REPORT PREVIEW</div>
    <div class="toolbar-actions">
        <button class="btn btn-primary" onclick="window.print()">Print / Save PDF</button>
        <button class="btn" onclick="window.close()">Close</button>
    </div>
</div>

<div class="page-wrap">
<div class="print-header-banner">
    <div>
        @include('partials.logo-print', ['logoStyle' => 'max-height:60px;max-width:180px;object-fit:contain;'])
        <h1 class="company-title">CHIBOBRAND CO. LTD.</h1>
    </div>
    <div class="meta-box">
        <div class="report-title">Customer Report & Registration Analytics</div>
        <div><strong>Period:</strong> {{ $label }}</div>
        <div>
            <strong>Date Range:</strong>
            @if($from && $to)
                {{ $from->format('d M Y') }} - {{ $to->format('d M Y') }}
            @else
                All Time
            @endif
        </div>
        <div><strong>Generated On:</strong> {{ now()->format('d M Y, H:i') }} EAT</div>
        <div><strong>Source:</strong> Customer Management System</div>
    </div>
</div>

@php
    $newCount      = $customers->where('is_repeated', false)->count();
    $repeatedCount = $customers->where('is_repeated', true)->count();
    $total         = $customers->count();
@endphp

<table style="width:auto;margin-bottom:12px;">
    <tr>
        <td style="padding:4px 12px 4px 0;border:none;">
            <div style="font-size:9px;color:#64748b;text-transform:uppercase;">Total</div>
            <div style="font-size:16px;font-weight:700;">{{ $total }}</div>
        </td>
        <td style="padding:4px 12px;border:none;">
            <div style="font-size:9px;color:#15803d;text-transform:uppercase;">New</div>
            <div style="font-size:16px;font-weight:700;color:#15803d;">{{ $newCount }}</div>
        </td>
        <td style="padding:4px 12px;border:none;">
            <div style="font-size:9px;color:#4338ca;text-transform:uppercase;">Repeated</div>
            <div style="font-size:16px;font-weight:700;color:#4338ca;">{{ $repeatedCount }}</div>
        </td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Type</th>
            <th>Segment</th>
            <th>Status</th>
            <th>Purchases</th>
            <th>Registered</th>
        </tr>
    </thead>
    <tbody>
    @foreach($customers as $i => $customer)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $customer->name }}</td>
            <td>{{ $customer->phone }}</td>
            <td>
                @if($customer->is_repeated)
                    <span class="badge-repeated">REPEAT</span>
                @else
                    <span class="badge-new">NEW</span>
                @endif
            </td>
            <td>{{ $customer->is_wholesale ? 'Wholesale' : 'Retail' }}</td>
            <td>{{ $customer->verified ? 'Verified' : 'Unverified' }}</td>
            <td>{{ $customer->purchase_count ?? 0 }}</td>
            <td>{{ $customer->created_at ? $customer->created_at->format('d M Y') : '—' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">Chibo Brands Ltd &nbsp;|&nbsp; {{ now()->format('d M Y H:i') }}</div>
</div>
</body>
</html>
