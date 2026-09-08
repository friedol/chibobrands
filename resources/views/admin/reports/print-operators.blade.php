<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>&nbsp;</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Nunito Sans', sans-serif;
            background-color: #fff;
            color: #dc2626;
            font-size: 8.5pt;
            line-height: 1.3;
        }

        .report-wrapper {
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

        .stats-card {
            background-color: #fcfcfc;
            border: 1px solid #eee;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }

        .stats-value {
            font-weight: 800;
            font-size: 11pt;
            color: #222;
        }

        .stats-label {
            font-size: 6.5pt;
            text-transform: uppercase;
            color: #888;
            display: block;
        }

        .star-rating {
            color: #ffc107;
            font-size: 8pt;
        }

        .chart-container {
            border: 1px solid #eee;
            padding: 15px;
            background: #fbfbfb;
            border-radius: 6px;
            margin-bottom: 20px;
            height: 200px;
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
            .report-wrapper { width: 100%; max-width: 100%; padding: 0; }
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
        <small class="fw-semibold"><i class="fas fa-print me-1"></i> OPERATOR PERFORMANCE & ANALYTICS PREVIEW</small>
        <div class="d-flex gap-1">
            <button class="btn btn-light btn-sm fw-bold px-3" onclick="window.print()">Print</button>
            <button class="btn btn-outline-light btn-sm px-2" onclick="window.close()">Close</button>
        </div>
    </div>

    <div class="report-wrapper">
        <!-- Header -->
        <div class="report-header">
            <div class="row align-items-start">
                <div class="col-7">
                    @include('partials.logo-print')
                    <h1 class="company-name">CHIBOBRAND CO. LTD.</h1>
                </div>
                <div class="col-5 text-end">
                    <h2 class="report-title">Operator Performance Report</h2>
                    <p class="mb-0 fw-bold">GENERATED: {{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Meta & Primary Stats -->
        <div class="meta-container mb-4">
            <div>
                <span class="section-label">Report Period</span>
                <div class="fw-bold">{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</div>
                @if($operatorId)
                    @php $op = \App\Models\User::find($operatorId); @endphp
                    <div class="small text-muted">Filtered for Operator: <strong>{{ $op->name ?? 'N/A' }}</strong></div>
                @endif
            </div>
            <div class="d-flex gap-2">
                <div class="stats-card" style="min-width: 100px;">
                    <span class="stats-label">Total Jobs</span>
                    <span class="stats-value">{{ $summary['total_managed'] }}</span>
                </div>
                <div class="stats-card" style="min-width: 100px;">
                    <span class="stats-label">Jobs Printed</span>
                    <span class="stats-value text-success">{{ $summary['printed'] }}</span>
                </div>
                <div class="stats-card" style="min-width: 120px;">
                    <span class="stats-label">Total Revenue</span>
                    <span class="stats-value">{{ number_format($summary['revenue_managed']) }} TZS</span>
                </div>
            </div>
        </div>

        <!-- Trend Graph -->
        <div class="mb-4">
            <span class="section-label">Production Trend Analytics</span>
            <div class="chart-container">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- Per Operator Efficiency + "Starts" (Star Ratings) -->
        <div class="mb-4">
            <span class="section-label">Operator Efficiency & Performance Rating</span>
            <table class="table-pro">
                <thead>
                    <tr>
                        <th>OPERATOR NAME</th>
                        <th class="text-center">TOTAL TASKS</th>
                        <th class="text-center">PRINTED</th>
                        <th class="text-center">EFFICIENCY</th>
                        <th class="text-center">RATING</th>
                        <th class="text-end">REVENUE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasksByOperator as $opStat)
                        @php
                            $efficiency = $opStat['efficiency'];
                            $stars = floor($efficiency / 20); // 1 star per 20%
                            if ($stars > 5) $stars = 5;
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $opStat['operator']->name ?? 'Unknown' }}</td>
                            <td class="text-center">{{ $opStat['total'] }}</td>
                            <td class="text-center">{{ $opStat['printed'] }}</td>
                            <td class="text-center">
                                <span class="fw-bold {{ $efficiency >= 80 ? 'text-success' : ($efficiency >= 50 ? 'text-warning' : 'text-danger') }}">
                                    {{ number_format($efficiency, 1) }}%
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="star-rating">
                                    @for($i=0; $i<5; $i++)
                                        <i class="{{ $i < $stars ? 'fas' : 'far' }} fa-star"></i>
                                    @endfor
                                </span>
                            </td>
                            <td class="text-end fw-bold">{{ number_format($opStat['revenue']) }} TZS</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Detailed Task Log -->
        <div>
            <span class="section-label">Detailed Activity Log</span>
            <table class="table-pro">
                <thead>
                    <tr>
                        <th style="width: 80px;">DATE</th>
                        <th style="width: 80px;">TASK ID</th>
                        <th>CUSTOMER & TASK</th>
                        <th style="width: 100px;">STATUS</th>
                        <th style="width: 120px;">OPERATOR</th>
                        <th class="text-end" style="width: 90px;">PRICE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                        <tr>
                            <td>{{ $task->created_at->format('d/m/Y') }}</td>
                            <td class="fw-bold text-center">#{{ $task->task_code ?? $task->id }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $task->customer?->name ?? $task->customer_name }}</div>
                                <div class="small text-muted">{{ Str::limit($task->title, 60) }}</div>
                            </td>
                            <td class="text-center">
                                <span class="small fw-bold text-uppercase" style="font-size: 6.5pt;">{{ str_replace('_', ' ', $task->status) }}</span>
                            </td>
                            <td>{{ $task->operator->name ?? '-' }}</td>
                            <td class="text-end fw-bold">{{ number_format($task->price) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Fixed Footer -->
        <div class="print-footer">
            <div class="row align-items-start">
                <div class="col-8">
                     <p class="small text-muted mb-0">Note: This performance report is an internal analytical document. Efficiency is calculated as the ratio of successfully printed tasks to total managed tasks.</p>
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
        // Graph Data
        const trendData = @json($tasksByDate);
        const labels = trendData.map(d => d.date);
        const totalLine = trendData.map(d => d.total);
        const printedLine = trendData.map(d => d.printed);

        const ctx = document.getElementById('trendChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Tasks',
                        data: totalLine,
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Tasks Printed',
                        data: printedLine,
                        borderColor: '#1cc88a',
                        backgroundColor: 'rgba(28, 200, 138, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false, // Disable for print stability
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { size: 9, weight: 'bold' }, boxWidth: 12 }
                    }
                },
                scales: {
                    x: { ticks: { font: { size: 8 } }, grid: { display: false } },
                    y: { 
                        beginAtZero: true, 
                        ticks: { font: { size: 8 }, stepSize: 1 } 
                    }
                }
            }
        });

        window.onload = function() {
            if (window.location.search.includes('print=true')) {
                setTimeout(() => { window.print(); }, 800);
            }
        };
    </script>
</body>
</html>
