<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payslip – {{ $employee->full_name }} – {{ now()->format('F Y') }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1e293b; margin: 0; padding: 14px; }
        .header { margin-bottom: 14px; padding-bottom: 10px; border-bottom: 2px solid #1e293b; overflow: hidden; }
        .header-left { float: left; }
        .header-right { float: right; text-align: right; }
        h1 { margin: 0; font-size: 16px; font-weight: bold; }
        .sub { margin: 2px 0 0 0; font-size: 8.5px; color: #64748b; }
        .employee-info { margin-top: 12px; margin-bottom: 12px; }
        .employee-info table { border: none; }
        .employee-info td { border: none; padding: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #94a3b8; padding: 4px 6px; text-align: left; }
        th { background: #f1f5f9; color: #334155; font-size: 8px; text-transform: uppercase; font-weight: bold; }
        td.num { text-align: right; }
        .muted { color: #64748b; }
        .success { color: #16a34a; }
        .danger { color: #dc2626; }
        tr.total td { background: #e2e8f0; font-weight: bold; }
        .net-row td { font-weight: bold; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <h1>Employee Payslip</h1>
            <p class="sub">CHIBOBRAND CO. LTD. | Dar es Salaam, Tanzania</p>
        </div>
        <div class="header-right">
            <div style="font-size: 10px; font-weight: bold;">Month</div>
            <div class="sub" style="color:#0f172a; font-weight:bold;">{{ now()->format('F Y') }}</div>
            <div class="sub">Base currency: <strong>TZS</strong></div>
        </div>
    </div>

    <div class="employee-info">
        <table>
            <tr>
                <td style="width: 100px; font-weight: bold;">Employee Name:</td>
                <td>{{ $employee->full_name }}</td>
                <td style="width: 100px; font-weight: bold;">Employee Code:</td>
                <td>{{ $employee->employee_code }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Department:</td>
                <td>{{ $employee->department ?? '—' }}</td>
                <td style="font-weight: bold;">Role:</td>
                <td>{{ $employee->role_title ?? '—' }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="num">Amount (TZS)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Basic Salary</td>
                <td class="num">{{ number_format($employee->basic_salary, 2) }}</td>
            </tr>
            <tr>
                <td class="success">Allowances</td>
                <td class="num success">+{{ number_format($employee->allowances, 2) }}</td>
            </tr>
            <tr>
                <td class="danger">Deductions</td>
                <td class="num danger">-{{ number_format($employee->deductions, 2) }}</td>
            </tr>
            <tr class="net-row">
                <td>Net Payable</td>
                <td class="num text-primary">{{ number_format($employee->net_salary, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 40px; overflow: hidden;">
        <div style="float: left; width: 200px; border-top: 1px solid #1e293b; text-align: center; padding-top: 5px;">
            <p style="margin: 0; font-size: 8.5px;">Employee Signature</p>
        </div>
        <div style="float: right; width: 200px; border-top: 1px solid #1e293b; text-align: center; padding-top: 5px;">
            <p style="margin: 0; font-size: 8.5px;">Authorized Signature</p>
        </div>
    </div>
</body>
</html>
