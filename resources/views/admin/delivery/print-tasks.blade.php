<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>&nbsp;</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Nunito Sans', sans-serif;
            background-color: #fff;
            color: #dc2626;
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

        /* Table Design */
        .table-pro {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .table-pro th {
            background-color: #f8f8f8 !important;
            border: 1px solid #ddd;
            padding: 8px 10px;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 7.5pt;
            color: #dc2626;
        }

        .table-pro td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            vertical-align: top;
        }

        /* Footer Positioning */
        .print-footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .company-seal {
            text-align: right;
            font-size: 8pt;
            line-height: 1.4;
            color: #666;
        }

        .dev-credit {
            font-size: 7pt;
            color: #888;
            margin-top: 10px;
            text-align: center;
        }

        .dev-credit a {
            color: #888;
            text-decoration: none;
            font-weight: 600;
        }

        .status-badge {
            font-weight: 700;
            font-size: 7pt;
            text-transform: uppercase;
        }

        @media print {
            @page { 
                size: auto;   
                margin: 0mm;  
            }
            body { 
                -webkit-print-color-adjust: exact; 
                padding: 15mm 15mm 120px 15mm; 
            }
            .no-print { display: none !important; }
            .log-wrapper { width: 100%; max-width: 100%; padding: 0; }
            .print-footer { 
                position: fixed; 
                bottom: 0; 
                left: 0; 
                right: 0; 
                width: 100%;
                background: white;
                padding: 0 15mm 10mm 15mm;
                margin-top: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print py-1 px-3 bg-primary text-white d-flex justify-content-between align-items-center mb-3">
        <small class="fw-semibold"><i class="fas fa-print me-1"></i> DELIVERY LOG PRINT PREVIEW</small>
        <div class="d-flex gap-1">
            <button class="btn btn-light btn-sm fw-bold px-3" onclick="window.print()">Print</button>
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
                    <h2 class="report-title">Delivery Status Log</h2>
                    <p class="mb-0 fw-bold">GENERATED: {{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Meta -->
        <div class="meta-container">
            <div>
                <span class="section-label">Report Type</span>
                <div class="fw-bold text-uppercase">{{ $viewType }}</div>
            </div>
            <div class="text-end">
                <span class="section-label">Summary</span>
                <div class="small">
                    <strong>Total Tasks:</strong> {{ $tasks->count() }}<br>
                    <strong>Delivered:</strong> {{ $tasks->where('delivery_status', 'delivered')->count() }}<br>
                    <strong>Failed/Canceled:</strong> {{ $tasks->where('delivery_status', 'failed')->count() }}
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table-pro">
            <thead>
                <tr>
                    <th style="width: 80px;">TASK ID</th>
                    <th style="width: 150px;">CUSTOMER</th>
                    <th>TASK DETAILS</th>
                    <th style="width: 100px;">STATUS</th>
                    <th style="width: 130px;">DELIVERY PERSONNEL</th>
                    <th style="width: 90px;">DATE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tasks as $task)
                    <tr>
                        <td class="fw-bold text-center">#{{ $task->id }}</td>
                        <td>
                            <div class="fw-bold">{{ $task->customer?->name ?? $task->customer_name }}</div>
                            <div class="small text-muted">{{ $task->customer?->phone ?? '-' }}</div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $task->title ?? 'Design Task' }}</div>
                            <div class="small text-muted">{{ Str::limit($task->description, 100) }}</div>
                        </td>
                        <td class="text-center">
                            @php
                                $statusColor = match($task->delivery_status) {
                                    'assigned' => '#fd7e14',
                                    'picked_up' => '#0dcaf0',
                                    'delivered' => '#198754',
                                    'failed' => '#dc3545',
                                    default => '#6c757d'
                                };
                            @endphp
                            <span class="status-badge" style="color: {{ $statusColor }};">{{ strtoupper($task->delivery_status ?? 'Pending') }}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $task->delivery?->name ?? 'Unassigned' }}</div>
                            @if($task->delivered_at)
                                <div class="small text-success" style="font-size: 7pt;">Completed: {{ $task->delivered_at->format('d/m H:i') }}</div>
                            @endif
                        </td>
                        <td class="small">
                            {{ $task->created_at->format('d/m/Y') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Fixed Footer -->
        <div class="print-footer">
            <div class="row align-items-start">
                <div class="col-8">
                     <p class="small text-muted mb-0">Note: This delivery status log is an internal record of Chibo Brands. It tracks the movement of design tasks from assignment to final delivery.</p>
                </div>
                <div class="col-4">
                    <div class="company-seal">
                        <strong class="text-uppercase" style="font-size: 9pt;">Chibo Brands Company Limited</strong><br>
                        Kinondoni Studio Opposite Vijana House<br>
                        P.O.BOX 77773, Mwanza, Tanzania<br>
                        <strong class="text-dark">MOB: 0753 553 382</strong>
                    </div>
                </div>
            </div>
            <div class="dev-credit">
                Developed by <a href="https://fridoltech.com" target="_blank">Fridoltech</a>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            if (window.location.search.includes('print=true')) {
                setTimeout(() => { window.print(); }, 400);
            }
        };
    </script>
</body>
</html>
