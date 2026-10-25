<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Expediente {{ $caso->numero }}</title>
    <style>
        @page { margin: 34px 38px; }
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.45; }
        h1 { color: #78350f; font-size: 22px; margin: 0; }
        h2 { border-bottom: 1px solid #d6d3d1; color: #78350f; font-size: 14px; margin: 20px 0 8px; padding-bottom: 4px; }
        p { margin: 4px 0; }
        table { border-collapse: collapse; margin-top: 6px; width: 100%; }
        th, td { border-bottom: 1px solid #e7e5e4; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f5f5f4; color: #44403c; }
        .muted { color: #78716c; }
        .confidential { background: #fff7ed; border: 1px solid #fed7aa; padding: 10px; }
        .header { border-bottom: 2px solid #b45309; margin-bottom: 14px; padding-bottom: 10px; }
        .footer { color: #78716c; font-size: 8px; margin-top: 24px; }
        .preserve { white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="header">
        <p class="muted">EXPEDIENTE JURÍDICO</p>
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
        <p><strong>Descripción:</strong> {{ $caso->descripcion }}</p>
    @endif

    <h2>Tareas</h2>
    @forelse ($caso->tareas as $tarea)
        <p><strong>{{ $tarea->titulo }}</strong> — {{ str($tarea->estado)->replace('_', ' ')->headline() }}
            @if ($tarea->asignado) · Responsable: {{ $tarea->asignado->name }} @endif
            @if ($tarea->fecha_limite) · Vence: {{ $tarea->fecha_limite->format('d/m/Y H:i') }} @endif
        </p>
        @if ($tarea->descripcion)<p class="muted">{{ $tarea->descripcion }}</p>@endif
    @empty
        <p class="muted">No hay tareas registradas.</p>
    @endforelse

    <h2>Audiencias y citas</h2>
    @forelse ($caso->audiencias as $audiencia)
        <p><strong>{{ $audiencia->titulo }}</strong> — {{ $audiencia->fecha_hora->format('d/m/Y H:i') }}
            · {{ str($audiencia->estado)->headline() }}
            @if ($audiencia->responsable) · Responsable: {{ $audiencia->responsable->name }} @endif
            @if ($audiencia->lugar) · {{ $audiencia->lugar }} @endif
        </p>
        @if ($audiencia->resultado)<p class="muted">Resultado: {{ $audiencia->resultado }}</p>@endif
    @empty
        <p class="muted">No hay audiencias o citas registradas.</p>
    @endforelse

    <h2>Cronología</h2>
    @forelse ($caso->eventos as $evento)
        <p><strong>{{ $evento->ocurrido_en->format('d/m/Y H:i') }} · {{ $evento->titulo }}</strong>
            — {{ str($evento->tipo)->headline() }}
            @if ($evento->creador) · {{ $evento->creador->name }} @endif
        </p>
        @if ($evento->descripcion)<p class="muted">{{ $evento->descripcion }}</p>@endif
    @empty
        <p class="muted">No hay actuaciones registradas.</p>
    @endforelse

    <h2>Documentos adjuntos</h2>
    @forelse ($caso->documentos as $documento)
        <p><strong>{{ $documento->titulo }}</strong> — {{ $documento->nombre_original }}
            · {{ str($documento->categoria)->headline() }}
            · {{ number_format($documento->tamano / 1024, 0) }} KB
        </p>
    @empty
        <p class="muted">No hay documentos adjuntos.</p>
    @endforelse

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
