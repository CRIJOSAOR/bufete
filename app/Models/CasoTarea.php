<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CasoTarea extends Model
{
    protected $fillable = [
        'caso_id',
        'asignado_a',
        'titulo',
        'descripcion',
        'prioridad',
        'estado',
        'fecha_limite',
        'completada_en',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_limite' => 'datetime',
            'completada_en' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CasoTarea $tarea): void {
            if (auth()->check() && ! $tarea->creado_por) {
                $tarea->creado_por = auth()->id();
            }

            if ($tarea->estado === 'completada' && ! $tarea->completada_en) {
                $tarea->completada_en = now();
            }
        });

        static::updating(function (CasoTarea $tarea): void {
            $tarea->completada_en = $tarea->estado === 'completada'
                ? ($tarea->completada_en ?? now())
                : null;
        });
    }

    public function caso(): BelongsTo
    {
        return $this->belongsTo(Caso::class);
    }

    public function asignado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}
