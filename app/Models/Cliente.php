<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Cliente extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'tipo', 'nombre', 'documento', 'telefono', 'correo',
        'direccion', 'notas', 'notas_privadas', 'creado_por',
    ];

    protected static function booted(): void
    {
        static::creating(function (Cliente $cliente) {
            if (auth()->check() && ! $cliente->creado_por) {
                $cliente->creado_por = auth()->id();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('clientes')
            ->logOnly(['tipo', 'nombre'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event): string => match ($event) {
                'created' => "Se registró el cliente {$this->nombre}",
                'updated' => "Se actualizó el cliente {$this->nombre}",
                'deleted' => "Se archivó el cliente {$this->nombre}",
                'restored' => "Se restauró el cliente {$this->nombre}",
                default => "Se modificó el cliente {$this->nombre}",
            });
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function casos(): HasMany
    {
        return $this->hasMany(Caso::class);
    }

    public function honorarios(): HasMany
    {
        return $this->hasMany(Honorario::class);
    }

    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class);
    }

    public function movimientosFondos(): HasMany
    {
        return $this->hasMany(FondoClienteMovimiento::class);
    }

    public function scopeAssignedTo(Builder $query, User $user): Builder
    {
        return $query->whereHas('casos', fn (Builder $query) => $query->assignedTo($user));
    }
}
