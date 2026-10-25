@extends('pdf.contabilidad.layout')

@section('content')
    <div class="header">
        <p class="muted">ESTADO DE CUENTA</p>
        <h1>Honorario #{{ $honorario->getKey() }}</h1>
        <p class="muted">Generado {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    <p><strong>Cliente:</strong> {{ $honorario->cliente->nombre }}</p>
    <p><strong>Caso:</strong> {{ $honorario->caso?->numero ?? 'General' }} {{ $honorario->caso?->titulo }}</p>
    <p><strong>Concepto:</strong> {{ $honorario->concepto }}</p>
    <p><strong>Modalidad:</strong> {{ str($honorario->modalidad)->replace('_', ' ')->headline() }}</p>
    <p><strong>Monto acordado:</strong> {{ $honorario->moneda }} {{ \App\Support\AccountingMoney::formatAmount($honorario->monto_acordado) }}</p>
    <p><strong>Total pagado:</strong> {{ $honorario->moneda }} {{ \App\Support\AccountingMoney::formatAmount($honorario->total_pagado) }}</p>
    <p class="amount"><strong>Saldo:</strong> {{ $honorario->moneda }} {{ \App\Support\AccountingMoney::formatAmount($honorario->saldo) }}</p>
    <p><strong>Vencimiento:</strong> {{ $honorario->fecha_vencimiento?->format('d/m/Y') ?? 'No definido' }}</p>

    <h2>Pagos registrados</h2>
    <table>
        <thead><tr><th>Fecha</th><th>Método</th><th>Referencia</th><th>Monto</th></tr></thead>
        <tbody>
        @forelse ($honorario->pagos as $pago)
            <tr>
                <td>{{ $pago->fecha_pago->format('d/m/Y H:i') }}</td>
                <td>{{ str($pago->metodo)->headline() }}</td>
                <td>{{ $pago->referencia ?: '—' }}</td>
                <td>{{ $honorario->moneda }} {{ \App\Support\AccountingMoney::formatAmount($pago->monto) }}</td>
            </tr>
        @empty
            <tr><td colspan="4">No se han registrado pagos.</td></tr>
        @endforelse
        </tbody>
    </table>
@endsection
