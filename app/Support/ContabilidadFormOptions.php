<?php

namespace App\Support;

use App\Models\Honorario;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ContabilidadFormOptions
{
    public static function clientes(Builder $query): Builder
    {
        return RecordAccessScope::apply(
            $query,
            auth()->user() instanceof User ? auth()->user() : null,
        );
    }

    public static function casos(Builder $query): Builder
    {
        return RecordAccessScope::apply(
            $query,
            auth()->user() instanceof User ? auth()->user() : null,
        );
    }

    public static function honorarios(Builder $query): Builder
    {
        return RecordAccessScope::apply(
            $query,
            auth()->user() instanceof User ? auth()->user() : null,
        );
    }

    public static function monedas(): array
    {
        return Honorario::MONEDAS;
    }
}
