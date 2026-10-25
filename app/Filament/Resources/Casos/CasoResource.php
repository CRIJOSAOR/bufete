<?php

namespace App\Filament\Resources\Casos;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Casos\Pages\CreateCaso;
use App\Filament\Resources\Casos\Pages\EditCaso;
use App\Filament\Resources\Casos\Pages\ListCasos;
use App\Filament\Resources\Casos\RelationManagers\AudienciasRelationManager;
use App\Filament\Resources\Casos\RelationManagers\DocumentosRelationManager;
use App\Filament\Resources\Casos\RelationManagers\EventosRelationManager;
use App\Filament\Resources\Casos\RelationManagers\TareasRelationManager;
use App\Filament\Resources\Casos\Schemas\CasoForm;
use App\Filament\Resources\Casos\Tables\CasosTable;
use App\Models\Caso;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CasoResource extends BaseResource
{
    protected static ?string $model = Caso::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|UnitEnum|null $navigationGroup = 'Gestión';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'caso';

    protected static ?string $pluralModelLabel = 'casos';

    protected static ?string $recordTitleAttribute = 'titulo';

    public static function form(Schema $schema): Schema
    {
        return CasoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CasosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            TareasRelationManager::class,
            AudienciasRelationManager::class,
            EventosRelationManager::class,
            DocumentosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCasos::route('/'),
            'create' => CreateCaso::route('/create'),
            'edit' => EditCaso::route('/{record}/edit'),
        ];
    }

}