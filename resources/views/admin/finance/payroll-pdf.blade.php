<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payroll – {{ now()->format('F Y') }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1e293b; margin: 0; padding: 14px; }
        .header { margin-bottom: 14px; padding-bottom: 10px; border-bottom: 2px solid #1e293b; overflow: hidden; }
        .header-left { float: left; }
        .header-right { float: right; text-align: right; }
        h1 { margin: 0; font-size: 16px; font-weight: bold; }
        .sub { margin: 2px 0 0 0; font-size: 8.5px; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #94a3b8; padding: 4px 6px; text-align: left; }
        th { background: #f1f5f9; color: #334155; font-size: 8px; text-transform: uppercase; font-weight: bold; }
        td.num { text-align: right; }
        .muted { color: #64748b; }
        .success { color: #16a34a; }
        .danger { color: #dc2626; }
        tr.total td { background: #e2e8f0; font-weight: bold; }
        .net-row td { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <h1>Employee Payroll</h1>
            <p class="sub">CHIBOBRAND CO. LTD. | Dar es Salaam, Tanzania</p>
        </div>
        <div class="header-right">
            <div style="font-size: 10px; font-weight: bold;">Period</div>
            <div class="sub" style="color:#0f172a; font-weight:bold;">{{ now()->format('F Y') }}</div>
            <div class="sub">Base currency: <strong>TZS</strong></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Department</th>
                <th class="num">Basic Salary</th>
                <th class="num">Allowances</th>
                <th class="num">Deductions</th>
                <th class="num">Net Salary</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $emp)
            <tr>
                <td>{{ $emp->full_name }} ({{ $emp->employee_code }})</td>
                <td>{{ $emp->department ?? '—' }}</td>
                <td class="num">{{ number_format($emp->basic_salary, 2) }}</td>
                <td class="num success">+{{ number_format($emp->allowances, 2) }}</td>
                <td class="num danger">-{{ number_format($emp->deductions, 2) }}</td>
                <td class="num" style="font-weight:bold;">{{ number_format($emp->net_salary, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total">
                <td colspan="2">Total</td>
                <td class="num">{{ number_format($employees->sum('basic_salary'), 2) }}</td>
                <td class="num success">+{{ number_format($employees->sum('allowances'), 2) }}</td>
                <td class="num danger">-{{ number_format($employees->sum('deductions'), 2) }}</td>
                <td class="num">{{ number_format($employees->sum('net_salary'), 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
