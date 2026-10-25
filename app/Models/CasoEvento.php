<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CasoEvento extends Model
{
    protected $fillable = [
        'caso_id',
        'tipo',
        'titulo',
        'descripcion',
        'ocurrido_en',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'ocurrido_en' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CasoEvento $evento): void {
            if (auth()->check() && ! $evento->creado_por) {
                $evento->creado_por = auth()->id();
            }

            $evento->ocurrido_en ??= now();
        });
    }

    public function caso(): BelongsTo
    {
        return $this->belongsTo(Caso::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}
