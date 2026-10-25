<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Support\AccountingMoney;
use App\Support\RecordAccessScope;

class FondoClienteMovimiento extends Model
{
    public const INGRESO = 'ingreso';

    public const EGRESO = 'egreso';

    protected $table = 'fondos_cliente_movimientos';

    protected $fillable = [
        'cliente_id', 'caso_id', 'registrado_por', 'tipo', 'monto', 'moneda',
        'fecha', 'concepto', 'referencia', 'comprobante_ruta',
        'comprobante_original', 'comprobante_mime', 'comprobante_tamano',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha' => 'datetime',
            'comprobante_tamano' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (FondoClienteMovimiento $movimiento): void {
            if (auth()->check()) {
                $movimiento->registrado_por = auth()->id();
            }
        });

        static::saving(function (FondoClienteMovimiento $movimiento): void {
            if (! array_key_exists($movimiento->moneda, Honorario::MONEDAS)
                || ! in_array($movimiento->tipo, [self::INGRESO, self::EGRESO], true)
                || AccountingMoney::toMinorUnits($movimiento->monto) <= 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'monto' => 'Selecciona un tipo de movimiento y moneda válidos, y un monto mayor que cero.',
                ]);
            }

            if ($movimiento->caso_id && $movimiento->cliente_id) {
                $caseClientId = Caso::query()->whereKey($movimiento->caso_id)->value('cliente_id');

                if ((int) $caseClientId !== (int) $movimiento->cliente_id) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'caso_id' => 'El caso seleccionado pertenece a otro cliente.',
                    ]);
                }
            }

            if (auth()->user() instanceof User) {
                abort_unless(
                    RecordAccessScope::apply(Cliente::query(), auth()->user())->whereKey($movimiento->cliente_id)->exists(),
                    404,
                );

                if ($movimiento->caso_id) {
                    abort_unless(
                        RecordAccessScope::apply(Caso::query(), auth()->user())->whereKey($movimiento->caso_id)->exists(),
                        404,
                    );
                }
            }
        });

        static::deleting(function (FondoClienteMovimiento $movimiento): void {
            if ($movimiento->comprobante_ruta) {
                Storage::disk('accounting_files')->delete($movimiento->comprobante_ruta);
            }
        });

        static::updating(function (): never {
            throw new \LogicException('Los movimientos de fondos de clientes son inmutables; registre un movimiento de ajuste.');
        });
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function caso(): BelongsTo
    {
        return $this->belongsTo(Caso::class);
    }

    public function scopeAssignedTo(Builder $query, User $user): Builder
    {
        return $query->whereHas('cliente', fn (Builder $clientQuery) => $clientQuery->assignedTo($user));
    }
}
