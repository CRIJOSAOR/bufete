<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Support\RecordAccessScope;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

abstract class BaseResource extends Resource
{
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        return RecordAccessScope::apply(
            parent::getEloquentQuery(),
            $user instanceof User ? $user : null,
        );
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
