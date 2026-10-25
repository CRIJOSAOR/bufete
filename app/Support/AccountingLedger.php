<?php

namespace App\Support;

use App\Models\FondoClienteMovimiento;
use App\Models\Honorario;
use App\Models\Pago;
use App\Models\User;
use App\Support\AccountingMoney;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountingLedger
{
    public function registerPayment(array $data): Pago
    {
        $data = Arr::only($data, [
            'honorario_id', 'monto', 'fecha_pago', 'metodo', 'referencia',
            'comprobante_ruta', 'comprobante_original', 'comprobante_mime', 'comprobante_tamano',
        ]);

        return DB::transaction(function () use ($data): Pago {
            $honorario = RecordAccessScope::apply(
                Honorario::query(),
                auth()->user() instanceof User ? auth()->user() : null,
            )
                ->lockForUpdate()
                ->findOrFail($data['honorario_id']);

            $paid = AccountingMoney::toMinorUnits($honorario->total_pagado);
            $amount = AccountingMoney::toMinorUnits($data['monto']);
            $total = AccountingMoney::toMinorUnits($honorario->monto_acordado);

            if ($amount <= 0 || $paid + $amount > $total) {
                throw ValidationException::withMessages([
                    'monto' => 'El pago debe ser mayor que cero y no puede exceder el saldo pendiente.',
                ]);
            }

            return $honorario->pagos()->create($data);
        });
    }

    public function registerClientFundMovement(array $data): FondoClienteMovimiento
    {
        $data = Arr::only($data, [
            'cliente_id', 'caso_id', 'tipo', 'monto', 'moneda', 'fecha',
            'concepto', 'referencia', 'comprobante_ruta', 'comprobante_original',
            'comprobante_mime', 'comprobante_tamano',
        ]);

        return DB::transaction(function () use ($data): FondoClienteMovimiento {
            $client = RecordAccessScope::apply(
                \App\Models\Cliente::query(),
                auth()->user() instanceof User ? auth()->user() : null,
            )
                ->lockForUpdate()
                ->findOrFail($data['cliente_id']);

            $balance = (string) FondoClienteMovimiento::query()
                ->where('cliente_id', $client->getKey())
                ->where('moneda', $data['moneda'])
                ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE -monto END), 0) as balance")
                ->value('balance');
            $balanceInMinorUnits = AccountingMoney::toMinorUnits($balance);
            $amount = AccountingMoney::toMinorUnits($data['monto']);

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'monto' => 'El monto debe ser mayor que cero.',
                ]);
            }

            if (! in_array($data['tipo'], [FondoClienteMovimiento::INGRESO, FondoClienteMovimiento::EGRESO], true)) {
                throw ValidationException::withMessages([
                    'tipo' => 'Selecciona un tipo de movimiento válido.',
                ]);
            }

            if ($data['tipo'] === FondoClienteMovimiento::EGRESO && $amount > $balanceInMinorUnits) {
                throw ValidationException::withMessages([
                    'monto' => 'El egreso supera el saldo disponible de los fondos del cliente.',
                ]);
            }

            return FondoClienteMovimiento::query()->create($data);
        });
    }
}
