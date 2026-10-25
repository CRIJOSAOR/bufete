<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CasoDocumento extends Model
{
    protected $fillable = [
        'caso_id',
        'titulo',
        'categoria',
        'descripcion',
        'ruta',
        'nombre_original',
        'mime_type',
        'tamano',
        'subido_por',
    ];

    protected function casts(): array
    {
        return [
            'tamano' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CasoDocumento $documento): void {
            if (auth()->check() && ! $documento->subido_por) {
                $documento->subido_por = auth()->id();
            }
        });

        static::deleting(function (CasoDocumento $documento): void {
            if ($documento->ruta) {
                Storage::disk('case_files')->delete($documento->ruta);
            }
        });

        static::updated(function (CasoDocumento $documento): void {
            if ($documento->isDirty('ruta') && $documento->getOriginal('ruta')) {
                Storage::disk('case_files')->delete($documento->getOriginal('ruta'));
            }
        });
    }

    public function caso(): BelongsTo
    {
        return $this->belongsTo(Caso::class);
    }

    public function subidor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }
}
