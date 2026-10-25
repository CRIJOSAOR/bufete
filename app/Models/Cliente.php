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

    public function scopeAssignedTo(Builder $query, User $user): Builder
    {
        return $query->whereHas('casos', fn (Builder $query) => $query->assignedTo($user));
    }
}