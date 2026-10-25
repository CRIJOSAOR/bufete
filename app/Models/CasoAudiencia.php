<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CasoAudiencia extends Model
{
    protected $fillable = [
        'caso_id',
        'responsable_id',
        'titulo',
        'fecha_hora',
        'lugar',
        'modalidad',
        'estado',
        'resultado',
        'notas',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CasoAudiencia $audiencia): void {
            if (auth()->check() && ! $audiencia->creado_por) {
                $audiencia->creado_por = auth()->id();
            }
        });
    }

    public function caso(): BelongsTo
    {
        return $this->belongsTo(Caso::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}
