<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 40px 42px; }
        body { color: #292524; font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.45; }
        h1 { color: #78350f; font-size: 20px; margin: 0; }
        h2 { border-bottom: 1px solid #d6d3d1; color: #78350f; font-size: 13px; margin: 20px 0 8px; padding-bottom: 5px; page-break-after: avoid; }
        p { margin: 5px 0; }
        table { border-collapse: collapse; margin-top: 8px; width: 100%; }
        th, td { border-bottom: 1px solid #e7e5e4; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f5f5f4; color: #44403c; }
        .header { border-bottom: 2px solid #b45309; margin-bottom: 16px; padding-bottom: 10px; }
        .header p { margin: 0 0 4px; }
        .eyebrow { color: #78716c; font-size: 8px; font-weight: bold; }
        .muted { color: #78716c; }
        .details { margin: 0 0 14px; }
        .details th { background: #fafaf9; color: #57534e; font-weight: bold; width: 27%; }
        .details td { width: 73%; }
        .summary { margin: 12px 0 16px; table-layout: fixed; }
        .summary th { font-size: 8px; text-transform: uppercase; }
        .summary td { color: #292524; font-size: 13px; font-weight: bold; }
        .summary .balance { background: #f0fdf4; color: #166534; }
        .amount-panel { background: #f0fdf4; border-left: 3px solid #15803d; margin: 12px 0 16px; padding: 9px 12px; page-break-inside: avoid; }
        .amount-label { color: #57534e; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .amount { color: #166534; font-size: 17px; font-weight: bold; margin: 2px 0 0; }
        .data-table thead { display: table-header-group; }
        .data-table tr { page-break-inside: avoid; }
        .data-table th { font-size: 8px; }
        .number { text-align: right; white-space: nowrap; }
        .notice { background: #fff7ed; border: 1px solid #fed7aa; margin-top: 18px; padding: 9px; }
        .attachment { display: block; margin: 8px 0 14px; max-height: 420px; max-width: 100%; }
        .footer { color: #78716c; font-size: 8px; margin-top: 24px; }
    </style>
</head>
<body>
    @yield('content')
    <p class="footer">Reporte de control financiero interno. No constituye factura fiscal ni reemplaza la contabilidad oficial.</p>
</body>
</html>
