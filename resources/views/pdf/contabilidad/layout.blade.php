<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 36px; }
        body { color: #292524; font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.45; }
        h1 { color: #78350f; font-size: 20px; margin: 0; }
        h2 { border-bottom: 1px solid #d6d3d1; color: #78350f; font-size: 13px; margin: 20px 0 8px; padding-bottom: 4px; }
        p { margin: 5px 0; }
        table { border-collapse: collapse; margin-top: 8px; width: 100%; }
        th, td { border-bottom: 1px solid #e7e5e4; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f5f5f4; color: #44403c; }
        .header { border-bottom: 2px solid #b45309; margin-bottom: 16px; padding-bottom: 10px; }
        .muted { color: #78716c; }
        .amount { color: #166534; font-size: 16px; font-weight: bold; }
        .notice { background: #fff7ed; border: 1px solid #fed7aa; margin-top: 18px; padding: 9px; }
        .footer { color: #78716c; font-size: 8px; margin-top: 24px; }
    </style>
</head>
<body>
    @yield('content')
    <p class="footer">Reporte de control financiero interno. No constituye factura fiscal ni reemplaza la contabilidad oficial.</p>
</body>
</html>
