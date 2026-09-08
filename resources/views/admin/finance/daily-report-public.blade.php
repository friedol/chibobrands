<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Report – {{ $isRange ? $carbonFrom->format('d M') . ' - ' . $carbonTo->format('d M Y') : $carbonFrom->format('d M Y') }} | CHIBO BRANDS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-red: #dc2626;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #dbe3ef;
            --panel: #f8fafc;
            --ok: #15803d;
            --warn: #b45309;
            --info: #2563eb;
            --danger: #b91c1c;
        }

        body {
            font-family: 'Nunito Sans', sans-serif;
            background: #f3f6fb;
            color: var(--ink);
            margin: 0;
            padding: 12px 0;
        }

        .preview-toolbar {
            position: sticky;
            top: 0;
            z-index: 12;
            background: #0f172a;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 14px;
            border-radius: 10px;
            margin: 0 auto 10px;
            max-width: 1380px;
        }

        .preview-toolbar .toolbar-title {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .toolbar-actions {
            display: flex;
            gap: 8px;
        }

        .toolbar-btn {
            border: 1px solid #334155;
            background: #1e293b;
            color: #fff;
            border-radius: 6px;
            padding: 4px 11px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
        }

        .toolbar-btn:hover {
            background: #334155;
        }

        .toolbar-btn-primary {
            background: var(--brand-red);
            border-color: var(--brand-red);
        }

        .toolbar-btn-primary:hover {
            background: #b91c1c;
        }

        .page-wrap {
            max-width: 1380px;
            margin: 0 auto;
            padding: 0 8px;
        }

        .report-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
        }

        .report-header-banner {
            border-bottom: 3px solid var(--brand-red);
            padding-bottom: 12px;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
        }

        .company-title {
            font-size: 16pt;
            font-weight: 800;
            color: var(--brand-red);
            letter-spacing: 0.4px;
            margin: 0;
        }

        .report-title {
            color: var(--brand-red);
            font-size: 11pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 5px;
        }

        .report-meta {
            text-align: right;
            font-size: 8.7pt;
            color: #334155;
            min-width: 285px;
        }

        .report-meta strong {
            color: #991b1b;
        }

        .report-table {
            border: none !important;
            margin-top: 12px;
            border-radius: 8px;
            overflow: hidden;
            border-collapse: separate !important;
            border-spacing: 0;
        }

        .report-table tbody td {
            padding: 5px 8px !important;
            border: 1px solid var(--line) !important;
            font-size: 12px;
            color: #0f172a;
            background: #fff;
        }

        .report-table thead th {
            background: #1f2937 !important;
            color: #ffffff !important;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 9px;
            padding: 7px 8px !important;
            border: 1px solid #1f2937 !important;
        }

        .report-table thead th:nth-child(7) {
            background: #7f1d1d !important;
            color: #ffffff !important;
        }

        .report-table tbody tr:not(.total-row) td:nth-child(1),
        .report-table tbody tr:not(.total-row) td:nth-child(2),
        .report-table tbody tr:not(.total-row) td:nth-child(7) {
            background-color: #f8fafc !important;
        }

        .report-table td:nth-child(6), .report-table th:nth-child(6) {
            border-right: 2px solid #94a3b8 !important;
        }

        .description-cell {
            font-weight: 500;
            color: #1a202c !important;
            text-align: left !important;
        }

        .amount-cell {
            text-align: right;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            letter-spacing: -0.3px;
        }

        .report-table tbody tr:not(.total-row) td.remain-cell {
            color: var(--info) !important;
            background-color: #eff6ff !important;
        }

        .report-table tr.total-row td {
            background-color: var(--panel) !important;
            color: #0f172a !important;
            font-weight: 800 !important;
            font-size: 12px !important;
            border: 1px solid #94a3b8 !important;
            padding: 7px 8px !important;
        }

        .x-small {
            font-size: 11px;
            font-weight: 600;
        }

        .no-break { page-break-inside: avoid; break-inside: avoid; }

        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            .preview-toolbar { display: none !important; }
            body { background: #fff; padding: 0; }
            .page-wrap { max-width: 100%; padding: 0; }
            .report-container {
                border: none;
                border-radius: 0;
                box-shadow: none;
                padding: 0;
            }
            .report-header-banner {
                flex-direction: row !important;
                align-items: flex-start !important;
            }
            .report-meta {
                text-align: right !important;
            }
            .report-table thead {
                display: table-header-group;
            }
        }

        @media screen and (max-width: 900px) {
            .report-header-banner {
                flex-direction: column;
                align-items: flex-start;
            }
            .report-meta {
                min-width: 0;
                width: 100%;
                text-align: left;
            }
            .report-container {
                padding: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrap">
        <div class="preview-toolbar">
            <div class="toolbar-title"><i class="fas fa-share-alt me-1"></i> Finance Report Preview</div>
            <div class="toolbar-actions">
                <button class="toolbar-btn toolbar-btn-primary" onclick="window.print()">Print / Save PDF</button>
                <button class="toolbar-btn" onclick="window.close()">Close</button>
            </div>
        </div>

        <div class="report-container">
            <div class="report-header-banner">
                <div>
                    @include('partials.logo-print', ['logoStyle' => 'height:50px;width:auto;margin-bottom:4px;object-fit:contain;'])
                    <h1 class="company-title">CHIBOBRAND CO. LTD.</h1>
                    <p class="mb-0 x-small text-muted mt-1">Dar es Salaam, Tanzania | Tel: +255 655 392 319</p>
                </div>
                <div class="report-meta">
                    <div class="report-title">Business Finance Summary</div>
                    <div>
                        <strong>Period:</strong>
                        @if($isRange)
                            {{ $carbonFrom->format('d M, Y') }} - {{ $carbonTo->format('d M, Y') }}
                        @else
                            {{ $carbonFrom->format('d M, Y') }}
                        @endif
                    </div>
                    <div><strong>Generated:</strong> {{ now()->format('d M Y, H:i') }} EAT</div>
                    <div><strong>Source:</strong> Finance Analytics System</div>
                </div>
            </div>

            @php
                $grandTotalIncomeMobile = 0;
                $grandTotalIncomeCash = 0;
                $grandTotalIncomeBank = 0;
                $grandTotalIncomeRemain = 0;
                $grandTotalExpenseMobile = 0;
                $grandTotalExpenseCash = 0;
                $grandTotalExpenseBank = 0;
            @endphp

            @foreach($reportData as $data)
                @php
                    $incomeItems = $data['incomeItems'];
                    $expenseItems = $data['expenseItems'];
                    $dept = $data['department'];
                    $maxItems = max(count($incomeItems), count($expenseItems));
                    $deptTotalIncomeMobile = $deptTotalIncomeCash = $deptTotalIncomeBank = $deptTotalIncomeRemain = 0;
                    $deptTotalExpenseMobile = $deptTotalExpenseCash = $deptTotalExpenseBank = 0;
                @endphp

                <div class="department-segment mb-5">
                    <div class="table-responsive">
                        <table class="table table-bordered report-table align-middle shadow-sm">
                            <thead>
                                <tr class="header-row">
                                    <th style="width: 12%; text-align: left !important;">CUSTOMER</th>
                                    <th style="width: 18%; text-align: left !important;">{{ $dept->name }} TASK</th>
                                    <th style="width: 6%">MOBILE</th>
                                    <th style="width: 6%">CASH</th>
                                    <th style="width: 6%">BANK</th>
                                    <th style="width: 6%">REMAIN</th>
                                    <th style="width: 19%; text-align: left !important;">CASHOUT (EXPENSES)</th>
                                    <th style="width: 9%">MOBILE</th>
                                    <th style="width: 9%">CASH</th>
                                    <th style="width: 9%">BANK</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < $maxItems; $i++)
                                    @php
                                        $income = $incomeItems[$i] ?? null;
                                        $expense = $expenseItems[$i] ?? null;
                                        if($income) {
                                            $deptTotalIncomeMobile += $income['mobile'];
                                            $deptTotalIncomeCash += $income['cash'];
                                            $deptTotalIncomeBank += $income['bank'];
                                            $deptTotalIncomeRemain += $income['remain'];
                                            $grandTotalIncomeMobile += $income['mobile'];
                                            $grandTotalIncomeCash += $income['cash'];
                                            $grandTotalIncomeBank += $income['bank'];
                                            $grandTotalIncomeRemain += $income['remain'];
                                        }
                                        if($expense) {
                                            $deptTotalExpenseMobile += $expense['mobile'];
                                            $deptTotalExpenseCash += $expense['cash'];
                                            $deptTotalExpenseBank += $expense['bank'];
                                            $grandTotalExpenseMobile += $expense['mobile'];
                                            $grandTotalExpenseCash += $expense['cash'];
                                            $grandTotalExpenseBank += $expense['bank'];
                                        }
                                    @endphp
                                    <tr>
                                        <td class="description-cell ps-3"><div class="fw-bold text-dark">{{ $income['customer_name'] ?? '' }}@if(isset($income['is_debt']) && $income['is_debt']) (dept) @endif</div></td>
                                        <td class="description-cell ps-3 small text-muted">{{ $income['description'] ?? '' }}</td>
                                        <td class="amount-cell">{{ $income && $income['mobile'] > 0 ? number_format($income['mobile'], 0) : '' }}</td>
                                        <td class="amount-cell">{{ $income && $income['cash'] > 0 ? number_format($income['cash'], 0) : '' }}</td>
                                        <td class="amount-cell">{{ $income && $income['bank'] > 0 ? number_format($income['bank'], 0) : '' }}</td>
                                        <td class="amount-cell remain-cell fw-bold">{{ $income && $income['remain'] > 0 ? number_format($income['remain'], 0) : '' }}</td>
                                        <td class="description-cell ps-3">{{ $expense['description'] ?? '' }}</td>
                                        <td class="amount-cell">{{ $expense && $expense['mobile'] > 0 ? number_format($expense['mobile'], 0) : '' }}</td>
                                        <td class="amount-cell text-danger fw-bold">{{ $expense && $expense['cash'] > 0 ? number_format($expense['cash'], 0) : '' }}</td>
                                        <td class="amount-cell">{{ $expense && $expense['bank'] > 0 ? number_format($expense['bank'], 0) : '' }}</td>
                                    </tr>
                                @endfor
                                <tr class="total-row">
                                    <td colspan="2" class="fw-bold ps-3" style="text-align: left !important;">{{ $dept->name }} TOTALS</td>
                                    <td class="amount-cell fw-bold">{{ number_format($deptTotalIncomeMobile, 0) }}</td>
                                    <td class="amount-cell fw-bold text-success">{{ number_format($deptTotalIncomeCash, 0) }}</td>
                                    <td class="amount-cell fw-bold">{{ number_format($deptTotalIncomeBank, 0) }}</td>
                                    <td class="amount-cell fw-bold text-primary">{{ number_format($deptTotalIncomeRemain, 0) }}</td>
                                    <td class="fw-bold ps-3" style="text-align: left !important;">DEPT CASHOUT</td>
                                    <td class="amount-cell fw-bold">{{ number_format($deptTotalExpenseMobile, 0) }}</td>
                                    <td class="amount-cell fw-bold text-danger">{{ number_format($deptTotalExpenseCash, 0) }}</td>
                                    <td class="amount-cell fw-bold">{{ number_format($deptTotalExpenseBank, 0) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            @php
                $totalIn = $grandTotalIncomeMobile + $grandTotalIncomeCash + $grandTotalIncomeBank;
                $totalOut = $grandTotalExpenseMobile + $grandTotalExpenseCash + $grandTotalExpenseBank;
                $net = $totalIn - $totalOut;
            @endphp

            <div class="row mt-5 g-5 no-break">
                <div class="col-md-6">
                    <div class="ps-2">
                        <h6 class="fw-bold text-uppercase mb-4 pb-2 border-bottom border-secondary border-opacity-10">
                            <i class="fas fa-calculator me-2 text-primary"></i>Global Consolidated Summary
                        </h6>
                        <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Total Revenue (In):</span><span class="fw-bold text-dark">TZS {{ number_format($totalIn, 0) }}</span></div>
                        <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Total Expenses (Out):</span><span class="fw-bold text-warning">TZS {{ number_format($totalOut, 0) }}</span></div>
                        <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Total Outstanding (Remain):</span><span class="fw-bold text-info">TZS {{ number_format($grandTotalIncomeRemain, 0) }}</span></div>
                        <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Total Debt Collected:</span><span class="fw-bold text-success">TZS {{ number_format($grandTotals['income']['total_debt'], 0) }}</span></div>
                        <div class="d-flex justify-content-between border-top border-secondary border-opacity-10 pt-3 mt-3 align-items-center">
                            <span class="fw-bold small text-uppercase">Net Position:</span>
                            <span class="fw-bold fs-5 text-primary">TZS {{ number_format($net, 0) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="ps-md-5 border-start border-secondary border-opacity-10">
                        <h6 class="fw-bold text-uppercase mb-4 pb-2 border-bottom border-secondary border-opacity-10">
                            <i class="fas fa-money-check-alt me-2 text-primary"></i>Payment Breakdown
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-6"><div class="small text-muted mb-1 text-uppercase x-small">Cash (In)</div><div class="fw-bold text-success small">TZS {{ number_format($grandTotals['income']['cash'], 0) }}</div></div>
                            <div class="col-6"><div class="small text-muted mb-1 text-uppercase x-small">Mobile (In)</div><div class="fw-bold text-primary small">TZS {{ number_format($grandTotals['income']['mobile'], 0) }}</div></div>
                            <div class="col-6"><div class="small text-muted mb-1 text-uppercase x-small">Bank (In)</div><div class="fw-bold text-info small">TZS {{ number_format($grandTotals['income']['bank'], 0) }}</div></div>
                            <div class="col-6"><div class="small text-muted mb-1 text-uppercase x-small">Total In</div><div class="fw-bold text-dark small">TZS {{ number_format($grandTotals['income']['cash'] + $grandTotals['income']['mobile'] + $grandTotals['income']['bank'], 0) }}</div></div>
                        </div>
                        <div class="pt-2 border-top border-secondary border-opacity-10">
                            <span class="x-small fw-bold text-muted text-uppercase d-block mb-2">Expenses Detail</span>
                            <div class="d-flex justify-content-between x-small mb-1"><span class="text-muted">Cash:</span><span class="text-danger">- TZS {{ number_format($grandTotals['expense']['cash'], 0) }}</span></div>
                            <div class="d-flex justify-content-between x-small mb-1"><span class="text-muted">Mobile:</span><span>- TZS {{ number_format($grandTotals['expense']['mobile'], 0) }}</span></div>
                            <div class="d-flex justify-content-between x-small mb-1"><span class="text-muted">Bank:</span><span>- TZS {{ number_format($grandTotals['expense']['bank'], 0) }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
