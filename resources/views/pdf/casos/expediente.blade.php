<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Expediente {{ $caso->numero }}</title>
    <style>
        @page { margin: 34px 38px; }
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.45; }
        h1 { color: #78350f; font-size: 22px; margin: 0; }
        h2 { border-bottom: 1px solid #d6d3d1; color: #78350f; font-size: 14px; margin: 20px 0 8px; padding-bottom: 5px; page-break-after: avoid; }
        p { margin: 4px 0; }
        table { border-collapse: collapse; margin-top: 6px; width: 100%; }
        th, td { border-bottom: 1px solid #e7e5e4; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f5f5f4; color: #44403c; }
        .muted { color: #78716c; }
        .confidential { background: #fff7ed; border: 1px solid #fed7aa; padding: 10px; }
        .header { border-bottom: 2px solid #b45309; margin-bottom: 14px; padding-bottom: 10px; }
        .header p { margin: 0 0 4px; }
        .eyebrow { color: #78716c; font-size: 8px; font-weight: bold; }
        .footer { color: #78716c; font-size: 8px; margin-top: 24px; }
        .preserve { white-space: pre-wrap; }
        .data-table { margin-bottom: 10px; }
        .data-table thead { display: table-header-group; }
        .data-table tr { page-break-inside: avoid; }
        .data-table th { font-size: 8px; }
        .empty { color: #78716c; font-style: italic; }
        .attachment-image { display: block; margin: 8px 0 14px; max-height: 300px; max-width: 100%; page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="header">
        <p class="eyebrow">EXPEDIENTE JURÍDICO</p>
        <h1>{{ $caso->numero }} — {{ $caso->titulo }}</h1>
        <p class="muted">Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <h2>Datos generales</h2>
    <table>
        <tr><th>Cliente</th><td>{{ $caso->cliente?->nombre ?? '—' }}</td><th>Tipo</th><td>{{ \App\Models\Caso::TIPOS[$caso->tipo] ?? $caso->tipo }}</td></tr>
        <tr><th>Estado</th><td>{{ \App\Models\Caso::ESTADOS[$caso->estado] ?? $caso->estado }}</td><th>Inicio</th><td>{{ $caso->fecha_inicio?->format('d/m/Y') ?? '—' }}</td></tr>
        <tr><th>Juzgado</th><td colspan="3">{{ $caso->juzgado ?: '—' }}</td></tr>
        <tr><th>Abogados</th><td colspan="3">{{ $caso->abogados->pluck('name')->join(', ') ?: 'Sin asignar' }}</td></tr>
    </table>
    @if ($caso->descripcion)
        <h2>Descripción</h2>
        <p class="preserve">{{ $caso->descripcion }}</p>
    @endif

    <h2>Tareas</h2>
    @if ($caso->tareas->isNotEmpty())
        <table class="data-table">
            <thead><tr><th>Tarea</th><th>Estado</th><th>Responsable</th><th>Fecha límite</th></tr></thead>
            <tbody>
            @foreach ($caso->tareas as $tarea)
                <tr>
                    <td><strong>{{ $tarea->titulo }}</strong>@if ($tarea->descripcion)<br><span class="muted">{{ $tarea->descripcion }}</span>@endif</td>
                    <td>{{ str($tarea->estado)->replace('_', ' ')->headline() }}</td>
                    <td>{{ $tarea->asignado?->name ?? '—' }}</td>
                    <td>{{ $tarea->fecha_limite?->format('d/m/Y H:i') ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p class="empty">No hay tareas registradas.</p>
    @endif

    <h2>Audiencias y citas</h2>
    @if ($caso->audiencias->isNotEmpty())
        <table class="data-table">
            <thead><tr><th>Fecha</th><th>Audiencia o cita</th><th>Estado</th><th>Responsable y lugar</th></tr></thead>
            <tbody>
            @foreach ($caso->audiencias as $audiencia)
                <tr>
                    <td>{{ $audiencia->fecha_hora->format('d/m/Y H:i') }}</td>
                    <td><strong>{{ $audiencia->titulo }}</strong>@if ($audiencia->resultado)<br><span class="muted">Resultado: {{ $audiencia->resultado }}</span>@endif</td>
                    <td>{{ str($audiencia->estado)->headline() }}</td>
                    <td>{{ $audiencia->responsable?->name ?? '—' }}@if ($audiencia->lugar)<br>{{ $audiencia->lugar }}@endif</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p class="empty">No hay audiencias o citas registradas.</p>
    @endif

    <h2>Cronología</h2>
    @if ($caso->eventos->isNotEmpty())
        <table class="data-table">
            <thead><tr><th>Fecha</th><th>Actuación</th><th>Tipo</th><th>Registrado por</th></tr></thead>
            <tbody>
            @foreach ($caso->eventos as $evento)
                <tr>
                    <td>{{ $evento->ocurrido_en->format('d/m/Y H:i') }}</td>
                    <td><strong>{{ $evento->titulo }}</strong>@if ($evento->descripcion)<br><span class="muted">{{ $evento->descripcion }}</span>@endif</td>
                    <td>{{ str($evento->tipo)->headline() }}</td>
                    <td>{{ $evento->creador?->name ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p class="empty">No hay actuaciones registradas.</p>
    @endif

    <h2>Documentos adjuntos</h2>
    @if ($caso->documentos->isNotEmpty())
        <table class="data-table">
            <thead><tr><th>Documento</th><th>Nombre original</th><th>Categoría</th><th>Tamaño</th></tr></thead>
            <tbody>
            @foreach ($caso->documentos as $documento)
                <tr>
                    <td>{{ $documento->titulo }}</td>
                    <td>{{ $documento->nombre_original }}</td>
                    <td>{{ str($documento->categoria)->headline() }}</td>
                    <td>{{ number_format($documento->tamano / 1024, 0) }} KB</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @foreach ($caso->documentos as $documento)
            @if (isset($imagenes[$documento->getKey()]))
                <div style="page-break-inside: avoid;">
                    <p><strong>{{ $documento->titulo }}</strong></p>
                    <img class="attachment-image" src="{{ $imagenes[$documento->getKey()] }}" alt="{{ $documento->titulo }}">
                </div>
            @endif
        @endforeach
    @else
        <p class="empty">No hay documentos adjuntos.</p>
    @endif

    @if ($incluirConfidencial)
        <h2>Información confidencial</h2>
        <div class="confidential">
            <p><strong>Notas internas</strong></p>
            <p class="preserve">{{ $caso->notas_internas ?: 'Sin notas internas.' }}</p>
            <p><strong>Estrategia</strong></p>
            <p class="preserve">{{ $caso->estrategia ?: 'Sin estrategia registrada.' }}</p>
        </div>
    @endif

    <p class="footer">Documento generado desde el sistema de gestión del bufete. Verifique la actualidad de la información antes de utilizarlo.</p>
</body>
</html>
