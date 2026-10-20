<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

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

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function casos(): HasMany
    {
        return $this->hasMany(Caso::class);
    }

    // Qué clientes puede ver cada usuario
    public function scopeVisiblesPara(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasAnyRole(['super_admin', 'secretaria'])) {
            return $query;
        }

        if ($user->hasRole('abogado')) {
            return $query->where(function ($q) use ($user) {
                $q->where('creado_por', $user->id)
                  ->orWhereHas('casos', fn ($c) => $c->visiblesPara($user));
            });
        }

        return $query->whereRaw('1 = 0');
    }
}