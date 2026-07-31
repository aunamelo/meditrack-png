<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'MediTrack PNG Report')</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, sans-serif;
            color: #111827;
            background: #fff;
            font-size: 13px;
            line-height: 1.45;
        }
        .sheet { max-width: 900px; margin: 0 auto; padding: 28px 32px 40px; }
        .toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-bottom: 20px;
        }
        .toolbar button, .toolbar a {
            appearance: none;
            border: 1px solid #cbd5e1;
            background: #0f766e;
            color: #fff;
            border-radius: 6px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }
        .toolbar a.secondary { background: #fff; color: #0f172a; }
        .brand { font-size: 11px; letter-spacing: .12em; text-transform: uppercase; color: #0f766e; font-weight: 700; }
        h1 { margin: 6px 0 4px; font-size: 24px; }
        .meta { color: #64748b; margin-bottom: 20px; }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px 16px;
            margin-bottom: 22px;
        }
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 14px;
        }
        .card .label { font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: #64748b; font-weight: 600; }
        .card .value { margin-top: 4px; font-size: 20px; font-weight: 700; }
        h2 { font-size: 15px; margin: 22px 0 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px 6px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        th { font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
        .footer { margin-top: 28px; font-size: 11px; color: #94a3b8; }
        @media print {
            .toolbar { display: none !important; }
            .sheet { padding: 0; max-width: none; }
            a { color: inherit; text-decoration: none; }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="toolbar no-print">
            <button type="button" onclick="window.print()">Print / Save PDF</button>
            <a class="secondary" href="{{ url()->previous() }}">Back</a>
        </div>
        @yield('content')
        <p class="footer">MediTrack PNG · Generated {{ $report['generated_at'] ?? now()->format('M d, Y H:i') }} · {{ $report['generated_by'] ?? '' }}</p>
    </div>
</body>
</html>
