@extends('pdf.contabilidad.layout')

@section('content')
    <div class="header">
        <p class="eyebrow">REPORTE DE GASTO · #{{ $gasto->getKey() }}</p>
        <h1>{{ $gasto->concepto }}</h1>
        <p class="muted">Generado {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    <div class="amount-panel">
        <p class="amount-label">Monto del gasto</p>
        <p class="amount">{{ $gasto->moneda }} {{ \App\Support\AccountingMoney::formatAmount($gasto->monto) }}</p>
    </div>
    <table class="details">
        <tbody>
            <tr><th>Fecha</th><td>{{ $gasto->fecha->format('d/m/Y') }}</td></tr>
            <tr><th>Categoría</th><td>{{ str($gasto->categoria)->headline() }}</td></tr>
            <tr><th>Proveedor</th><td>{{ $gasto->proveedor ?: '—' }}</td></tr>
            <tr><th>Pagado por</th><td>{{ $gasto->pagado_por ?: '—' }}</td></tr>
            <tr><th>Cliente</th><td>{{ $gasto->cliente?->nombre ?? 'Bufete' }}</td></tr>
            <tr><th>Caso</th><td>{{ $gasto->caso?->numero ?? 'General' }}</td></tr>
            <tr><th>Reembolsable</th><td>{{ $gasto->reembolsable ? 'Sí' : 'No' }}</td></tr>
    @if ($gasto->reembolsable)
            <tr><th>Estado del reembolso</th><td>{{ $gasto->reembolsado ? 'Reembolsado' : 'Pendiente' }}</td></tr>
    @endif
    @if ($gasto->comprobante_original)
            <tr><th>Comprobante</th><td>{{ $gasto->comprobante_original }}</td></tr>
    @endif
        </tbody>
    </table>
    @if ($gasto->notas)
        <h2>Notas</h2>
        <p class="preserve">{{ $gasto->notas }}</p>
    @endif
    @if ($comprobanteImagen)
        <h2>Foto del recibo</h2>
        <img class="attachment" src="{{ $comprobanteImagen }}" alt="Foto del recibo">
    @endif
@endsection
