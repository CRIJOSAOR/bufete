<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

        static::deleting(function (Caso $caso): void {
            if (! $caso->isForceDeleting()) {
                return;
            }

            $caso->documentos->each->delete();
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

    public function tareas(): HasMany
    {
        return $this->hasMany(CasoTarea::class);
    }

    public function audiencias(): HasMany
    {
        return $this->hasMany(CasoAudiencia::class);
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(CasoEvento::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(CasoDocumento::class);
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
        return $query->whereHas('abogados', fn (Builder $query) => $query->where('users.id', $user->getKey()));
    }
}