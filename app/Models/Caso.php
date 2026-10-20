<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Caso extends Model
{
    use SoftDeletes;

    public const TIPOS = [
        'civil'     => 'Civil',
        'familia'   => 'Familia',
        'mercantil' => 'Mercantil',
        'penal'     => 'Penal',
        'laboral'   => 'Laboral',
        'registral' => 'Bienes y registro',
    ];

    public const ESTADOS = [
        'abierto'    => 'Abierto',
        'en_tramite' => 'En trámite',
        'en_espera'  => 'En espera',
        'cerrado'    => 'Cerrado',
        'archivado'  => 'Archivado',
    ];

    protected $fillable = [
        'numero', 'titulo', 'cliente_id', 'tipo', 'juzgado', 'estado',
        'fecha_inicio', 'descripcion', 'notas_internas', 'estrategia', 'creado_por',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Caso $caso) {
            if (auth()->check() && ! $caso->creado_por) {
                $caso->creado_por = auth()->id();
            }
        });
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function abogados(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'caso_user')->withTimestamps();
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    // Qué casos puede ver cada usuario
    public function scopeVisiblesPara(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasAnyRole(['super_admin', 'secretaria'])) {
            return $query;
        }

        if ($user->hasRole('abogado')) {
            return $query->whereHas('abogados', fn ($q) => $q->where('users.id', $user->id));
        }

        return $query->whereRaw('1 = 0');
    }
}