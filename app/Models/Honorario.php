<?php

namespace App\Models;

use App\Support\AccountingMoney;
use App\Support\RecordAccessScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Honorario extends Model
{
    public const MONEDAS = ['HNL' => 'Lempiras (HNL)', 'USD' => 'Dólares (USD)'];

    protected $fillable = [
        'cliente_id', 'caso_id', 'creado_por', 'concepto', 'modalidad',
        'monto_acordado', 'moneda', 'fecha_vencimiento', 'notas',
    ];

    protected function casts(): array
    {
        return [
            'monto_acordado' => 'decimal:2',
            'fecha_vencimiento' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Honorario $honorario): void {
            if (auth()->check()) {
                $honorario->creado_por = auth()->id();
            }
        });

        static::updating(function (Honorario $honorario): void {
            $hasPayments = $honorario->pagos()->exists();

            if ($honorario->isDirty('moneda') && $hasPayments) {
                throw ValidationException::withMessages([
                    'moneda' => 'No se puede cambiar la moneda después de registrar pagos.',
                ]);
            }

            if ($honorario->isDirty('monto_acordado')
                && AccountingMoney::toMinorUnits($honorario->monto_acordado) < AccountingMoney::toMinorUnits($honorario->total_pagado)) {
                throw ValidationException::withMessages([
                    'monto_acordado' => 'El honorario no puede quedar por debajo de los pagos ya registrados.',
                ]);
            }
        });

        static::saving(function (Honorario $honorario): void {
            if (! array_key_exists($honorario->moneda, self::MONEDAS) || AccountingMoney::toMinorUnits($honorario->monto_acordado, 'monto_acordado') <= 0) {
                throw ValidationException::withMessages([
                    'monto_acordado' => 'Selecciona una moneda admitida y un monto mayor que cero.',
                ]);
            }

            if ($honorario->caso_id && $honorario->cliente_id) {
                $caseClientId = Caso::query()->whereKey($honorario->caso_id)->value('cliente_id');

                if ((int) $caseClientId !== (int) $honorario->cliente_id) {
                    throw ValidationException::withMessages([
                        'caso_id' => 'El caso seleccionado pertenece a otro cliente.',
                    ]);
                }
            }

            if (auth()->user() instanceof User) {
                abort_unless(
                    RecordAccessScope::apply(Cliente::query(), auth()->user())->whereKey($honorario->cliente_id)->exists(),
                    404,
                );

                if ($honorario->caso_id) {
                    abort_unless(
                        RecordAccessScope::apply(Caso::query(), auth()->user())->whereKey($honorario->caso_id)->exists(),
                        404,
                    );
                }
            }
        });

        static::deleting(function (Honorario $honorario): void {
            if ($honorario->pagos()->exists()) {
                throw ValidationException::withMessages([
                    'record' => 'No se puede eliminar un honorario que ya tiene pagos. Regístrelo como cancelado o genere un ajuste.',
                ]);
            }
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

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function scopeWithTotalPagado(Builder $query): Builder
    {
        return $query->addSelect([
            'pagos_sum_monto' => Pago::query()
                ->selectRaw('ROUND(COALESCE(SUM(pagos.monto), 0), 2)')
                ->whereColumn('pagos.honorario_id', 'honorarios.id'),
        ]);
    }

    public function getTotalPagadoAttribute(): string
    {
        $total = $this->getAttribute('pagos_sum_monto');

        if ($total !== null) {
            return AccountingMoney::formatAmount((string) $total);
        }

        $total = $this->pagos()
            ->selectRaw('ROUND(COALESCE(SUM(monto), 0), 2) as aggregate')
            ->value('aggregate');

        return AccountingMoney::formatAmount((string) ($total ?? '0'));
    }

    public function getSaldoAttribute(): string
    {
        $agreed = AccountingMoney::toMinorUnits($this->monto_acordado);
        $paid = AccountingMoney::toMinorUnits($this->total_pagado);

        return AccountingMoney::formatMinorUnits(max(0, $agreed - $paid));
    }

    public function getEstadoPagoAttribute(): string
    {
        $paid = AccountingMoney::toMinorUnits($this->total_pagado);
        $agreed = AccountingMoney::toMinorUnits($this->monto_acordado);

        if ($paid >= $agreed) {
            return 'Pagado';
        }

        return $paid > 0 ? 'Parcial' : 'Pendiente';
    }

    public function scopeAssignedTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user): void {
            $query->whereHas('caso', fn (Builder $caseQuery) => $caseQuery->assignedTo($user))
                ->orWhereHas('cliente', fn (Builder $clientQuery) => $clientQuery->assignedTo($user));
        });
    }
}
