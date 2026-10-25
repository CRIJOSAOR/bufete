@extends('pdf.contabilidad.layout')

@section('content')
    <div class="header">
        <p class="muted">ESTADO DE FONDOS DEL CLIENTE · {{ $movimiento->moneda }}</p>
        <h1>{{ $movimiento->cliente->nombre }}</h1>
        <p class="muted">Generado {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    <p><strong>Saldo actual registrado:</strong> {{ $movimiento->moneda }} {{ \App\Support\AccountingMoney::formatAmount($saldoActual) }}</p>
    <p class="muted">Solo incluye los movimientos visibles según el alcance de datos del usuario.</p>
    <table>
        <thead><tr><th>Fecha</th><th>Concepto</th><th>Caso</th><th>Tipo</th><th>Monto</th><th>Saldo</th></tr></thead>
        <tbody>
        @forelse ($movimientos as $fila)
            <tr>
                <td>{{ $fila['movimiento']->fecha->format('d/m/Y H:i') }}</td>
                <td>{{ $fila['movimiento']->concepto }}</td>
                <td>{{ $fila['movimiento']->caso?->numero ?? 'General' }}</td>
                <td>{{ $fila['movimiento']->tipo === 'ingreso' ? 'Ingreso' : 'Egreso' }}</td>
                <td>{{ $movimiento->moneda }} {{ \App\Support\AccountingMoney::formatAmount($fila['movimiento']->monto) }}</td>
                <td>{{ $movimiento->moneda }} {{ \App\Support\AccountingMoney::formatAmount($fila['balance']) }}</td>
            </tr>
        @empty
            <tr><td colspan="6">No hay movimientos registrados.</td></tr>
        @endforelse
        </tbody>
    </table>
    @if ($comprobanteImagen)
        <h2>Comprobante del movimiento seleccionado</h2>
        <img src="{{ $comprobanteImagen }}" alt="Comprobante del movimiento" style="max-height: 360px; max-width: 100%;">
    @endif
@endsection
