<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Support\AccountingMoney;
use App\Support\RecordAccessScope;

class Gasto extends Model
{
    protected $fillable = [
        'cliente_id', 'caso_id', 'creado_por', 'concepto', 'categoria',
        'monto', 'moneda', 'fecha', 'proveedor', 'pagado_por',
        'reembolsable', 'reembolsado', 'comprobante_ruta',
        'comprobante_original', 'comprobante_mime', 'comprobante_tamano', 'notas',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha' => 'date',
            'reembolsable' => 'boolean',
            'reembolsado' => 'boolean',
            'comprobante_tamano' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Gasto $gasto): void {
            if (auth()->check()) {
                $gasto->creado_por = auth()->id();
            }
        });

        static::saving(function (Gasto $gasto): void {
            if (! array_key_exists($gasto->moneda, Honorario::MONEDAS) || AccountingMoney::toMinorUnits($gasto->monto) <= 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'monto' => 'Selecciona una moneda admitida y un monto mayor que cero.',
                ]);
            }

            if ($gasto->caso_id) {
                $caseClientId = Caso::query()->whereKey($gasto->caso_id)->value('cliente_id');

                if (! $gasto->cliente_id) {
                    $gasto->cliente_id = $caseClientId;
                } elseif ((int) $caseClientId !== (int) $gasto->cliente_id) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'caso_id' => 'El caso seleccionado pertenece a otro cliente.',
                    ]);
                }
            }

            if ($gasto->reembolsado && ! $gasto->reembolsable) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'reembolsado' => 'Solo se puede marcar como reembolsado un gasto reembolsable.',
                ]);
            }

            if ($gasto->reembolsable && ! $gasto->cliente_id) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'cliente_id' => 'Asigne un cliente a los gastos que deben reembolsarse.',
                ]);
            }

            if (auth()->user() instanceof User) {
                if ($gasto->cliente_id) {
                    abort_unless(
                        RecordAccessScope::apply(Cliente::query(), auth()->user())->whereKey($gasto->cliente_id)->exists(),
                        404,
                    );
                }

                if ($gasto->caso_id) {
                    abort_unless(
                        RecordAccessScope::apply(Caso::query(), auth()->user())->whereKey($gasto->caso_id)->exists(),
                        404,
                    );
                }
            }
        });

        static::deleting(function (Gasto $gasto): void {
            if ($gasto->comprobante_ruta) {
                Storage::disk('accounting_files')->delete($gasto->comprobante_ruta);
            }
        });

        static::updated(function (Gasto $gasto): void {
            if ($gasto->wasChanged('comprobante_ruta') && $gasto->getOriginal('comprobante_ruta')) {
                Storage::disk('accounting_files')->delete($gasto->getOriginal('comprobante_ruta'));
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

    public function scopeAssignedTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user): void {
            $query->where('creado_por', $user->getKey())
                ->orWhereHas('caso', fn (Builder $caseQuery) => $caseQuery->assignedTo($user))
                ->orWhereHas('cliente', fn (Builder $clientQuery) => $clientQuery->assignedTo($user));
        });
    }
}
