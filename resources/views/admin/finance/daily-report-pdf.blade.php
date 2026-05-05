<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Finance Daily Report – {{ $isRange ? $carbonFrom->format('d M') . ' - ' . $carbonTo->format('d M Y') : $carbonFrom->format('d M Y') }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #1e293b; margin: 0; padding: 12px; }
        .header { margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid #1e293b; overflow: hidden; }
        .header-left { float: left; }
        .header-right { float: right; text-align: right; }
        .header h1 { margin: 0; font-size: 16px; font-weight: bold; }
        .header .sub { margin: 2px 0 0 0; font-size: 8px; color: #64748b; }
        .header .period { font-size: 12px; font-weight: bold; margin-top: 4px; }
        .clear { clear: both; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #94a3b8; padding: 4px 6px; text-align: left; }
        th { background: #f1f5f9; color: #334155; font-size: 8px; text-transform: uppercase; font-weight: bold; }
        th.num, td.num { text-align: right; }
        tr.total td { background: #e2e8f0; font-weight: bold; }
        .dept-section { margin-bottom: 16px; page-break-inside: avoid; }
        .section-title { font-size: 10px; font-weight: bold; margin: 14px 0 6px 0; padding-bottom: 4px; border-bottom: 1px solid #cbd5e1; }
        .summary-grid { margin-top: 14px; }
        .summary-row { margin: 3px 0; display: table; width: 100%; }
        .summary-label { display: table-cell; width: 55%; color: #64748b; }
        .summary-value { display: table-cell; width: 45%; text-align: right; font-weight: bold; }
        .net-row { margin-top: 8px; padding-top: 8px; border-top: 1px solid #cbd5e1; font-size: 11px; font-weight: bold; }
        .expense-detail { margin-top: 8px; font-size: 8px; color: #64748b; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <h1>Financial Report</h1>
            <p class="sub">Dar es Salaam, Tanzania | Tel: +255 655 392 319</p>
        </div>
        <div class="header-right">
            <div style="font-size: 10px; font-weight: bold;">BUSINESS FINANCE SUMMARY</div>
            <p class="period">
                @if($isRange)
                    {{ $carbonFrom->format('d M') }} – {{ $carbonTo->format('d M Y') }}
                @else
                    {{ $carbonFrom->format('d M Y') }}
                @endif
            </p>
            <p class="sub">Generated: {{ now()->format('M d, Y H:i') }}</p>
        </div>
    </div>
    <div class="clear"></div>

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

        <div class="dept-section">
            <table>
                <thead>
                    <tr>
                        <th style="width:14%">CUSTOMER</th>
                        <th style="width:18%">{{ $dept->name }} TASK</th>
                        <th class="num" style="width:8%">MOBILE</th>
                        <th class="num" style="width:8%">CASH</th>
                        <th class="num" style="width:8%">BANK</th>
                        <th class="num" style="width:8%">REMAIN</th>
                        <th style="width:16%">CASHOUT (EXPENSES)</th>
                        <th class="num" style="width:7%">MOBILE</th>
                        <th class="num" style="width:7%">CASH</th>
                        <th class="num" style="width:8%">BANK</th>
                    </tr>
                </thead>
                <tbody>
                    @for($i = 0; $i < $maxItems; $i++)
                        @php
                            $income = $incomeItems[$i] ?? null;
                            $expense = $expenseItems[$i] ?? null;
                            if ($income) {
                                $deptTotalIncomeMobile += $income['mobile'];
                                $deptTotalIncomeCash += $income['cash'];
                                $deptTotalIncomeBank += $income['bank'];
                                $deptTotalIncomeRemain += $income['remain'];
                                $grandTotalIncomeMobile += $income['mobile'];
                                $grandTotalIncomeCash += $income['cash'];
                                $grandTotalIncomeBank += $income['bank'];
                                $grandTotalIncomeRemain += $income['remain'];
                            }
                            if ($expense) {
                                $deptTotalExpenseMobile += $expense['mobile'];
                                $deptTotalExpenseCash += $expense['cash'];
                                $deptTotalExpenseBank += $expense['bank'];
                                $grandTotalExpenseMobile += $expense['mobile'];
                                $grandTotalExpenseCash += $expense['cash'];
                                $grandTotalExpenseBank += $expense['bank'];
                            }
                        @endphp
                        <tr>
                            <td><strong>{{ $income['customer_name'] ?? '' }}@if(isset($income['is_debt']) && $income['is_debt']) (dept) @endif</strong></td>
                            <td>{{ $income['description'] ?? '' }}</td>
                            <td class="num">{{ $income && $income['mobile'] > 0 ? number_format($income['mobile'], 0) : '' }}</td>
                            <td class="num">{{ $income && $income['cash'] > 0 ? number_format($income['cash'], 0) : '' }}</td>
                            <td class="num">{{ $income && $income['bank'] > 0 ? number_format($income['bank'], 0) : '' }}</td>
                            <td class="num">{{ $income && $income['remain'] > 0 ? number_format($income['remain'], 0) : '' }}</td>
                            <td>{{ $expense['description'] ?? '' }}</td>
                            <td class="num">{{ $expense && $expense['mobile'] > 0 ? number_format($expense['mobile'], 0) : '' }}</td>
                            <td class="num">{{ $expense && $expense['cash'] > 0 ? number_format($expense['cash'], 0) : '' }}</td>
                            <td class="num">{{ $expense && $expense['bank'] > 0 ? number_format($expense['bank'], 0) : '' }}</td>
                        </tr>
                    @endfor
                    <tr class="total">
                        <td colspan="2"><strong>{{ $dept->name }} TOTALS</strong></td>
                        <td class="num">{{ number_format($deptTotalIncomeMobile, 0) }}</td>
                        <td class="num">{{ number_format($deptTotalIncomeCash, 0) }}</td>
                        <td class="num">{{ number_format($deptTotalIncomeBank, 0) }}</td>
                        <td class="num">{{ number_format($deptTotalIncomeRemain, 0) }}</td>
                        <td>DEPT CASHOUT</td>
                        <td class="num">{{ number_format($deptTotalExpenseMobile, 0) }}</td>
                        <td class="num">{{ number_format($deptTotalExpenseCash, 0) }}</td>
                        <td class="num">{{ number_format($deptTotalExpenseBank, 0) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endforeach

    @php
        $totalIn = $grandTotalIncomeMobile + $grandTotalIncomeCash + $grandTotalIncomeBank;
        $totalOut = $grandTotalExpenseMobile + $grandTotalExpenseCash + $grandTotalExpenseBank;
        $net = $totalIn - $totalOut;
    @endphp

    <div class="section-title">Global Consolidated Summary</div>
    <div class="summary-grid">
        <div class="summary-row">
            <span class="summary-label">Total Revenue (In):</span>
            <span class="summary-value">TZS {{ number_format($totalIn, 0) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Expenses (Out):</span>
            <span class="summary-value">TZS {{ number_format($totalOut, 0) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Outstanding (Remain):</span>
            <span class="summary-value">TZS {{ number_format($grandTotalIncomeRemain, 0) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Debt Collected:</span>
            <span class="summary-value">TZS {{ number_format($grandTotals['income']['total_debt'], 0) }}</span>
        </div>
        <div class="summary-row net-row">
            <span class="summary-label">Net Position:</span>
            <span class="summary-value">TZS {{ number_format($net, 0) }}</span>
        </div>
    </div>

    <div class="section-title">Payment Breakdown</div>
    <div class="summary-grid">
        <div class="summary-row">
            <span class="summary-label">Cash (In):</span>
            <span class="summary-value">TZS {{ number_format($grandTotals['income']['cash'], 0) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Mobile (In):</span>
            <span class="summary-value">TZS {{ number_format($grandTotals['income']['mobile'], 0) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Bank (In):</span>
            <span class="summary-value">TZS {{ number_format($grandTotals['income']['bank'], 0) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total In:</span>
            <span class="summary-value">TZS {{ number_format($grandTotals['income']['cash'] + $grandTotals['income']['mobile'] + $grandTotals['income']['bank'], 0) }}</span>
        </div>
    </div>
    <div class="expense-detail">
        Expenses — Cash: TZS {{ number_format($grandTotals['expense']['cash'], 0) }} | Mobile: TZS {{ number_format($grandTotals['expense']['mobile'], 0) }} | Bank: TZS {{ number_format($grandTotals['expense']['bank'], 0) }}
    </div>
</body>
</html>
