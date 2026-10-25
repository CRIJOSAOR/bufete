@extends('pdf.contabilidad.layout')

@section('content')
    <div class="header">
        <p class="muted">REPORTE DE GASTO · #{{ $gasto->getKey() }}</p>
        <h1>{{ $gasto->concepto }}</h1>
        <p class="muted">Generado {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    <p><strong>Fecha:</strong> {{ $gasto->fecha->format('d/m/Y') }}</p>
    <p><strong>Categoría:</strong> {{ str($gasto->categoria)->headline() }}</p>
    <p><strong>Proveedor:</strong> {{ $gasto->proveedor ?: '—' }}</p>
    <p><strong>Pagado por:</strong> {{ $gasto->pagado_por ?: '—' }}</p>
    <p><strong>Cliente:</strong> {{ $gasto->cliente?->nombre ?? 'Bufete' }}</p>
    <p><strong>Caso:</strong> {{ $gasto->caso?->numero ?? 'General' }}</p>
    <p class="amount"><strong>Monto:</strong> {{ $gasto->moneda }} {{ \App\Support\AccountingMoney::formatAmount($gasto->monto) }}</p>
    <p><strong>Reembolsable:</strong> {{ $gasto->reembolsable ? 'Sí' : 'No' }}</p>
    @if ($gasto->reembolsable)
        <p><strong>Estado del reembolso:</strong> {{ $gasto->reembolsado ? 'Reembolsado' : 'Pendiente' }}</p>
    @endif
    @if ($gasto->comprobante_original)
        <p><strong>Comprobante:</strong> {{ $gasto->comprobante_original }}</p>
    @endif
    @if ($gasto->notas)
        <h2>Notas</h2>
        <p>{{ $gasto->notas }}</p>
    @endif
    @if ($comprobanteImagen)
        <h2>Foto del recibo</h2>
        <img src="{{ $comprobanteImagen }}" alt="Foto del recibo" style="max-height: 420px; max-width: 100%;">
    @endif
@endsection
