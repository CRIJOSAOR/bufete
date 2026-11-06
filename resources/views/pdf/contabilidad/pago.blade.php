@extends('pdf.contabilidad.layout')

@section('content')
    <div class="header">
        <p class="eyebrow">RECIBO DE PAGO · #{{ $pago->getKey() }}</p>
        <h1>{{ $pago->honorario->cliente->nombre }}</h1>
        <p class="muted">Emitido {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    <div class="amount-panel">
        <p class="amount-label">Monto recibido</p>
        <p class="amount">{{ $pago->honorario->moneda }} {{ \App\Support\AccountingMoney::formatAmount($pago->monto) }}</p>
    </div>
    <table class="details">
        <tbody>
            <tr><th>Fecha del pago</th><td>{{ $pago->fecha_pago->format('d/m/Y H:i') }}</td></tr>
            <tr><th>Concepto</th><td>{{ $pago->honorario->concepto }}</td></tr>
            <tr><th>Expediente</th><td>{{ $pago->honorario->caso?->numero ?? 'General' }}</td></tr>
            <tr><th>Método</th><td>{{ str($pago->metodo)->headline() }}</td></tr>
            <tr><th>Referencia</th><td>{{ $pago->referencia ?: '—' }}</td></tr>
            <tr><th>Saldo restante</th><td>{{ $pago->honorario->moneda }} {{ \App\Support\AccountingMoney::formatAmount($pago->honorario->saldo) }}</td></tr>
        </tbody>
    </table>
    @if ($pago->comprobante_original)
        <p class="muted"><strong>Comprobante adjunto:</strong> {{ $pago->comprobante_original }}</p>
    @endif
    @if ($comprobanteImagen)
        <h2>Comprobante</h2>
        <img class="attachment" src="{{ $comprobanteImagen }}" alt="Comprobante del pago">
    @endif
@endsection
