@extends('pdf.contabilidad.layout')

@section('content')
    <div class="header">
        <p class="muted">RECIBO DE PAGO · #{{ $pago->getKey() }}</p>
        <h1>{{ $pago->honorario->cliente->nombre }}</h1>
        <p class="muted">Emitido {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    <p><strong>Fecha del pago:</strong> {{ $pago->fecha_pago->format('d/m/Y H:i') }}</p>
    <p><strong>Concepto:</strong> {{ $pago->honorario->concepto }}</p>
    <p><strong>Expediente:</strong> {{ $pago->honorario->caso?->numero ?? 'General' }}</p>
    <p><strong>Método:</strong> {{ str($pago->metodo)->headline() }}</p>
    <p><strong>Referencia:</strong> {{ $pago->referencia ?: '—' }}</p>
    <p class="amount"><strong>Recibido:</strong> {{ $pago->honorario->moneda }} {{ \App\Support\AccountingMoney::formatAmount($pago->monto) }}</p>
    <p><strong>Saldo restante:</strong> {{ $pago->honorario->moneda }} {{ \App\Support\AccountingMoney::formatAmount($pago->honorario->saldo) }}</p>
    @if ($pago->comprobante_original)
        <p class="muted">Comprobante adjunto: {{ $pago->comprobante_original }}</p>
    @endif
    @if ($comprobanteImagen)
        <h2>Comprobante</h2>
        <img src="{{ $comprobanteImagen }}" alt="Comprobante del pago" style="max-height: 420px; max-width: 100%;">
    @endif
@endsection
