<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Marketing Report – {{ now()->format('d M Y') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; background: #fff; padding: 30px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .subtitle { color: #555; font-size: 11px; margin-bottom: 20px; }
        .notice { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 20px; color: #555; text-align: center; margin-top: 40px; }
        .footer { margin-top: 40px; font-size: 10px; color: #aaa; }
        @media print { body { padding: 0; } }
    </style>
</head>
<body>
    <h1>Marketing Report</h1>
    <div class="subtitle">Generated: {{ now()->format('d M Y, H:i') }}</div>

    <div class="notice">
        Marketing report data will appear here once the report parameters are configured.
    </div>

    <div class="footer">Chibo Brands Ltd &mdash; Marketing Report &mdash; {{ now()->format('d M Y') }}</div>

    <script>window.onload = function() { window.print(); };</script>
</body>
</html>
