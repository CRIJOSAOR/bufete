@extends('pdf.contabilidad.layout')

@section('content')
    <div class="header">
        <p class="eyebrow">ESTADO DE CUENTA</p>
        <h1>Honorario #{{ $honorario->getKey() }}</h1>
        <p class="muted">Generado {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    <table class="details">
        <tbody>
            <tr><th>Cliente</th><td>{{ $honorario->cliente->nombre }}</td></tr>
            <tr><th>Caso</th><td>{{ $honorario->caso?->numero ?? 'General' }} {{ $honorario->caso?->titulo }}</td></tr>
            <tr><th>Concepto</th><td>{{ $honorario->concepto }}</td></tr>
            <tr><th>Modalidad</th><td>{{ str($honorario->modalidad)->replace('_', ' ')->headline() }}</td></tr>
            <tr><th>Vencimiento</th><td>{{ $honorario->fecha_vencimiento?->format('d/m/Y') ?? 'No definido' }}</td></tr>
        </tbody>
    </table>

    <table class="summary">
        <thead><tr><th>Monto acordado</th><th>Total pagado</th><th>Saldo</th></tr></thead>
        <tbody><tr>
            <td>{{ $honorario->moneda }} {{ \App\Support\AccountingMoney::formatAmount($honorario->monto_acordado) }}</td>
            <td>{{ $honorario->moneda }} {{ \App\Support\AccountingMoney::formatAmount($honorario->total_pagado) }}</td>
            <td class="balance">{{ $honorario->moneda }} {{ \App\Support\AccountingMoney::formatAmount($honorario->saldo) }}</td>
        </tr></tbody>
    </table>

    <h2>Pagos registrados</h2>
    <table class="data-table">
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
