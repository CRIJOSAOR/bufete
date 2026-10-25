<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Pago extends Model
{
    protected $fillable = [
        'honorario_id', 'registrado_por', 'monto', 'fecha_pago', 'metodo',
        'referencia', 'comprobante_ruta', 'comprobante_original',
        'comprobante_mime', 'comprobante_tamano',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha_pago' => 'datetime',
            'comprobante_tamano' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Pago $pago): void {
            if (auth()->check()) {
                $pago->registrado_por = auth()->id();
            }
        });

        static::deleting(function (Pago $pago): void {
            if ($pago->comprobante_ruta) {
                Storage::disk('accounting_files')->delete($pago->comprobante_ruta);
            }
        });

        static::updating(function (): never {
            throw new \LogicException('Los pagos son inmutables; registre un ajuste en lugar de editarlos.');
        });
    }

    public function honorario(): BelongsTo
    {
        return $this->belongsTo(Honorario::class);
    }

    public function scopeAssignedTo(Builder $query, User $user): Builder
    {
        return $query->whereHas('honorario', fn (Builder $feeQuery) => $feeQuery->assignedTo($user));
    }
}
