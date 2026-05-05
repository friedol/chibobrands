<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 20px;
            color: #444;
            margin-bottom: 10px;
        }
        .report-info {
            font-size: 12px;
            color: #666;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: bold;
            text-align: left;
            padding: 10px;
            border: 1px solid #dee2e6;
            font-size: 12px;
        }
        td {
            padding: 8px 10px;
            border: 1px solid #dee2e6;
            font-size: 11px;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #999;
            padding: 10px 0;
            border-top: 1px solid #eee;
        }
        .badge {
            padding: 3px 7px;
            border-radius: 10px;
            font-size: 10px;
            color: white;
            text-transform: uppercase;
        }
        .bg-success { background-color: #198754; }
        .bg-primary { background-color: #0d6efd; }
        .bg-warning { background-color: #ffc107; color: #000; }
        .bg-danger { background-color: #dc3545; }
        .bg-info { background-color: #0dcaf0; }
        
        .stats-grid {
            margin-bottom: 30px;
            width: 100%;
        }
        .stats-card {
            border: 1px solid #dee2e6;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            background-color: #fff;
        }
        .stats-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .stats-value {
            font-size: 18px;
            font-weight: bold;
            color: #1a1a1a;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('images/logo.webp')))
            <img src="data:image/webp;base64,{{ base64_encode(file_get_contents(public_path('images/logo.webp'))) }}" class="logo">
        @endif
        <div class="company-name">CHIBOBRAND CO. LTD</div>
        <div class="report-title">{{ $title }}</div>
        <div class="report-info">
            Generated on: {{ now()->format('M d, Y H:i') }}<br>
            @if(isset($dateFrom) && isset($dateTo))
                Period: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
            @endif
        </div>
    </div>

    @yield('report_content')

    <div class="footer">
        &copy; {{ date('Y') }} CHIBOBRAND CO. LTD. All rights reserved.<br>
        Developed by <a href="https://fridoltech.org" style="color: #999; text-decoration: none;">Fridoltech</a>
    </div>
</body>
</html>
