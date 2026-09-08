<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Targets Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Nunito Sans', sans-serif;
            background-color: #fff;
            color: #222;
            font-size: 8.5pt;
            line-height: 1.3;
        }

        .log-wrapper {
            max-width: 1000px;
            margin: 0 auto;
            padding: 10px 15px;
        }

        /* Header Layout */
        .report-header {
            border-bottom: 1.5px solid #222;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .brand-logo {
            height: 45px;
            margin-bottom: 4px;
        }

        .company-name {
            font-weight: 800;
            font-size: 13pt;
            letter-spacing: -0.5px;
            margin: 0;
            color: #222;
        }

        .report-title {
            font-weight: 800;
            font-size: 15pt;
            text-transform: uppercase;
            margin: 0;
            text-align: right;
            color: #dc2626;
        }

        /* Meta Information */
        .meta-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            gap: 30px;
        }

        .section-label {
            font-weight: 800;
            font-size: 7.5pt;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 6px;
            border-bottom: 1px solid #ddd;
            display: inline-block;
            padding-bottom: 1px;
        }

        /* Section divider */
        .section-heading {
            font-weight: 800;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 5px 8px;
            margin: 16px 0 8px;
            display: flex;
            align-items: center;
            gap: 6px;
            page-break-after: avoid;
        }
        .section-heading.dept  { background: #eff6ff; color: #1d4ed8; border-left: 3px solid #1d4ed8; }
        .section-heading.saler { background: #fdf4ff; color: #7c3aed; border-left: 3px solid #7c3aed; }

        /* Dept sub-heading inside saler section */
        .dept-sub {
            font-size: 7.5pt;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.4px;
            margin: 10px 0 4px;
            padding-left: 4px;
            border-left: 3px solid #7c3aed;
        }

        /* Table Design */
        .table-pro {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .table-pro th {
            background-color: #f8f8f8 !important;
            border: 1px solid #ddd;
            padding: 6px 8px;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 7.5pt;
            color: #dc2626;
        }

        .table-pro td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            vertical-align: top;
        }

        .table-pro tbody tr:nth-child(even) td { background: #fafafa; }

        .table-pro tfoot td {
            background: #f8f8f8;
            font-weight: 800;
            border: 1px solid #ddd;
            padding: 6px 8px;
        }

        /* Period pill */
        .p-pill {
            display: inline-block;
            border-radius: 3px;
            padding: 1px 5px;
            font-size: 7pt;
            font-weight: 700;
            text-transform: uppercase;
        }
        .p-daily    { background:#fef3c7; color:#92400e; }
        .p-weekly   { background:#e0f2fe; color:#0369a1; }
        .p-monthly  { background:#eff6ff; color:#1d4ed8; }
        .p-quarterly{ background:#dcfce7; color:#15803d; }
        .p-yearly   { background:#fde8e8; color:#b91c1c; }

        /* Status */
        .st-active   { color: #16a34a; font-weight: 700; }
        .st-upcoming { color: #0369a1; font-weight: 700; }
        .st-expired  { color: #94a3b8; }

        /* Grand total bar */
        .grand-total-bar {
            background: #222;
            color: #fff;
            padding: 7px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
            font-weight: 800;
            font-size: 9pt;
        }

        /* Footer Positioning */
        .print-footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .dev-credit {
            font-size: 7pt;
            color: #888;
            text-align: center;
            width: 100%;
            padding: 5px 0;
        }

        .dev-credit a {
            color: #888;
            text-decoration: none;
            font-weight: 600;
        }

        @media print {
            @page {
                size: A4;
                margin: 15mm 15mm 25mm 15mm;
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                padding: 0;
                margin: 0;
            }
            .no-print { display: none !important; }
            .log-wrapper { width: 100%; max-width: 100%; padding: 0; }
            .print-footer {
                position: fixed;
                bottom: 0; left: 0; right: 0;
                width: 100%;
                background: white;
                padding: 5mm 0;
                margin-top: 0;
                border-top: 1px solid #ddd;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .table-pro { margin-bottom: 60px; }
            .print-footer::before {
                content: "Page " counter(page);
                position: absolute;
                bottom: 5mm;
                right: 15mm;
                font-size: 7pt;
                color: #888;
            }
        }
    </style>
</head>
<body>

@php
    $now = now();
    $deptTargets  = $targets->filter(fn($t) => !$t->seller_id);
    $salerTargets = $targets->filter(fn($t) =>  $t->seller_id);
    $salersByDept = $salerTargets->groupBy(fn($t) => $t->department->name ?? 'No Department');

    $titleMap = [
        'department' => 'Department Targets Report',
        'saler'      => 'Saler Targets Report',
        'all'        => 'Sales Targets Report',
    ];
    $reportTitle = $titleMap[$targetType] ?? 'Sales Targets Report';

    function stLabel($t, $now) {
        if ($t->start_date <= $now && $t->end_date >= $now) return ['Active',   'st-active'];
        if ($t->start_date > $now)                          return ['Upcoming', 'st-upcoming'];
        return ['Expired', 'st-expired'];
    }
@endphp

<div class="no-print py-1 px-3 bg-dark text-white d-flex justify-content-between align-items-center mb-3">
    <small class="fw-semibold"><i class="fas fa-print me-1"></i> SALES TARGETS PRINT PREVIEW</small>
    <div class="d-flex gap-1">
        <button class="btn btn-primary btn-sm fw-bold px-3" onclick="window.print()">Print</button>
        <button class="btn btn-outline-light btn-sm px-2" onclick="window.close()">Close</button>
    </div>
</div>

<div class="log-wrapper">

    <!-- Header -->
    <div class="report-header">
        <div class="row align-items-start">
            <div class="col-7">
                @include('partials.logo-print')
                <h1 class="company-name">CHIBOBRAND CO. LTD.</h1>
            </div>
            <div class="col-5 text-end">
                <h2 class="report-title">{{ $reportTitle }}</h2>
                <p class="mb-0 fw-bold">GENERATED: {{ $now->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>

    <!-- Meta -->
    <div class="meta-container">
        <div>
            <span class="section-label">Report Scope</span>
            <div class="fw-bold">
                @if($targetType === 'department') Department Targets Only
                @elseif($targetType === 'saler')  Saler Targets Only
                @else All Targets (Departments &amp; Salers)
                @endif
            </div>
            @if(request('date_from') || request('date_to'))
            <div class="mt-1">
                <span class="section-label">Date Filter</span>
                <div class="fw-bold">
                    @if(request('date_from') && request('date_to'))
                        {{ \Carbon\Carbon::parse(request('date_from'))->format('d M Y') }} — {{ \Carbon\Carbon::parse(request('date_to'))->format('d M Y') }}
                    @elseif(request('date_from'))
                        From {{ \Carbon\Carbon::parse(request('date_from'))->format('d M Y') }}
                    @else
                        Until {{ \Carbon\Carbon::parse(request('date_to'))->format('d M Y') }}
                    @endif
                </div>
            </div>
            @endif
            @if(request('period') && request('period') !== 'all')
            <div class="mt-1">
                <span class="section-label">Period Filter</span>
                <div class="fw-bold">{{ ucfirst(request('period')) }}</div>
            </div>
            @endif
        </div>
        <div class="text-end">
            <span class="section-label">Summary</span>
            <div class="small">
                <strong>Total Targets:</strong> {{ $targets->count() }} records<br>
                <strong>Grand Total Target:</strong> TZS {{ number_format($targets->sum('target_amount')) }}<br>
                @if($targetType === 'all')
                <strong>— Dept Targets:</strong> {{ $deptTargets->count() }} · TZS {{ number_format($deptTargets->sum('target_amount')) }}<br>
                <strong>— Saler Targets:</strong> {{ $salerTargets->count() }} · TZS {{ number_format($salerTargets->sum('target_amount')) }}<br>
                @endif
                <strong>Active Now:</strong> {{ $targets->filter(fn($t) => $t->start_date <= $now && $t->end_date >= $now)->count() }} targets
            </div>
        </div>
    </div>

    {{-- ══════════════════════
         DEPARTMENT TARGETS
    ══════════════════════ --}}
    @if($targetType !== 'saler' && $deptTargets->count())

        @if($targetType === 'all')
        <div class="section-heading dept">
            <i class="fas fa-building"></i> Department Targets
            <span style="font-weight:600;opacity:.75;">({{ $deptTargets->count() }} record{{ $deptTargets->count()!=1?'s':'' }})</span>
        </div>
        @endif

        <table class="table-pro">
            <thead>
                <tr>
                    <th style="width:25%;">Department</th>
                    <th style="width:12%;">Period</th>
                    <th style="width:13%;">Start Date</th>
                    <th style="width:13%;">End Date</th>
                    <th style="width:10%;">Status</th>
                    <th style="width:10%;">Repeat</th>
                    <th style="width:17%;" class="text-end">Target Amount (TZS)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deptTargets as $t)
                @php [$stLbl, $stCls] = stLabel($t, $now); @endphp
                <tr>
                    <td class="fw-bold">{{ $t->department->name ?? 'All Departments' }}</td>
                    <td><span class="p-pill p-{{ $t->period }}">{{ ucfirst($t->period) }}</span></td>
                    <td>{{ $t->start_date->format('d/m/Y') }}</td>
                    <td>{{ $t->end_date->format('d/m/Y') }}</td>
                    <td class="{{ $stCls }}">{{ $stLbl }}</td>
                    <td>
                        @if($t->recurrence_enabled) Auto
                        @elseif($t->recurrence_source_id) Generated
                        @else —
                        @endif
                    </td>
                    <td class="text-end fw-bold">{{ number_format($t->target_amount) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-end text-uppercase" style="font-size:7.5pt;">Total Department Target</td>
                    <td class="text-end">{{ number_format($deptTargets->sum('target_amount')) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- ══════════════════════
         SALER TARGETS
    ══════════════════════ --}}
    @if($targetType !== 'department' && $salerTargets->count())

        @if($targetType === 'all')
        <div class="section-heading saler">
            <i class="fas fa-user-tie"></i> Saler Targets
            <span style="font-weight:600;opacity:.75;">({{ $salerTargets->count() }} record{{ $salerTargets->count()!=1?'s':'' }})</span>
        </div>
        @endif

        @foreach($salersByDept as $deptName => $deptSalerTargets)

        @if($salersByDept->count() > 1)
        <div class="dept-sub">
            {{ $deptName }}
            &nbsp;·&nbsp; {{ $deptSalerTargets->count() }} target{{ $deptSalerTargets->count()!=1?'s':'' }}
            &nbsp;·&nbsp; TZS {{ number_format($deptSalerTargets->sum('target_amount')) }}
        </div>
        @endif

        <table class="table-pro">
            <thead>
                <tr>
                    <th style="width:22%;">Salesperson</th>
                    @if($salersByDept->count() <= 1)<th style="width:18%;">Department</th>@endif
                    <th style="width:12%;">Period</th>
                    <th style="width:13%;">Start Date</th>
                    <th style="width:13%;">End Date</th>
                    <th style="width:10%;">Status</th>
                    <th style="width:10%;">Repeat</th>
                    <th style="width:12%;" class="text-end">Target Amount (TZS)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deptSalerTargets as $t)
                @php [$stLbl, $stCls] = stLabel($t, $now); @endphp
                <tr>
                    <td class="fw-bold">{{ $t->seller->name ?? '—' }}</td>
                    @if($salersByDept->count() <= 1)<td>{{ $t->department->name ?? 'All Depts' }}</td>@endif
                    <td><span class="p-pill p-{{ $t->period }}">{{ ucfirst($t->period) }}</span></td>
                    <td>{{ $t->start_date->format('d/m/Y') }}</td>
                    <td>{{ $t->end_date->format('d/m/Y') }}</td>
                    <td class="{{ $stCls }}">{{ $stLbl }}</td>
                    <td>
                        @if($t->recurrence_enabled) Auto
                        @elseif($t->recurrence_source_id) Generated
                        @else —
                        @endif
                    </td>
                    <td class="text-end fw-bold">{{ number_format($t->target_amount) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="{{ $salersByDept->count() > 1 ? 6 : 7 }}" class="text-end text-uppercase" style="font-size:7.5pt;">
                        @if($salersByDept->count() > 1) Subtotal — {{ $deptName }}
                        @else Total Saler Target
                        @endif
                    </td>
                    <td class="text-end">{{ number_format($deptSalerTargets->sum('target_amount')) }}</td>
                </tr>
            </tfoot>
        </table>
        @endforeach
    @endif

    {{-- Empty state --}}
    @if($targets->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="fas fa-bullseye fa-3x mb-3 opacity-25"></i>
        <p class="mb-0">No targets found for the selected filters.</p>
    </div>
    @endif

    {{-- Grand total (all types combined) --}}
    @if($targets->isNotEmpty() && $targetType === 'all')
    <div class="grand-total-bar">
        <span style="font-size:7.5pt;text-transform:uppercase;letter-spacing:.5px;">Grand Total — All Targets</span>
        <span>TZS {{ number_format($targets->sum('target_amount')) }}</span>
    </div>
    @endif

    <!-- Footer -->
    <div class="print-footer">
        <div class="dev-credit">
            Developed by <a href="https://fridoltech.com" target="_blank">Fridoltech</a>
        </div>
    </div>

</div>

<script>
    window.onload = function () {
        if (window.location.search.includes('print=true')) {
            setTimeout(() => window.print(), 400);
        }
    };
</script>
</body>
</html>
