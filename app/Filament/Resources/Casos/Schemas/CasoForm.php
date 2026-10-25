<?php

namespace App\Filament\Resources\Casos\Schemas;

use App\Models\Caso;
use App\Models\User;
use App\Support\RecordAccessScope;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CasoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del caso')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('numero')
                            ->label('Número de expediente')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(60),

                        TextInput::make('titulo')
                            ->label('Título del caso')
                            ->required()
                            ->maxLength(255),

                        Select::make('cliente_id')
                            ->label('Cliente')
                            ->relationship(
                                'cliente',
                                'nombre',
                                fn (Builder $query) => RecordAccessScope::apply(
                                    $query,
                                    auth()->user() instanceof User ? auth()->user() : null,
                                )
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('tipo')
                            ->label('Tipo de caso')
                            ->options(Caso::TIPOS)
                            ->required(),

                        TextInput::make('juzgado')
                            ->label('Juzgado o instancia')
                            ->maxLength(255),

                        Select::make('estado')
                            ->label('Estado')
                            ->options(Caso::ESTADOS)
                            ->default('abierto')
                            ->required(),

                        DatePicker::make('fecha_inicio')
                            ->label('Fecha de inicio'),

                        Textarea::make('descripcion')
                            ->label('Descripción general')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Abogados asignados')
                    ->columnSpanFull()
                    ->visible(fn (): bool => auth()->user()->isPermissionAdministrator() || auth()->user()->can('asignar_abogados'))
                    ->schema([
                        Select::make('abogados')
                            ->label('Abogados')
                            ->relationship(
                                'abogados',
                                'name',
                                fn (Builder $query) => $query->permission('ser_asignable_a_casos')->where('activo', true)
                            )
                            ->multiple()
                            ->preload()
                            ->helperText('Solo el administrador asigna abogados a un caso.'),
                    ]),

                Section::make('Información confidencial')
                    ->columnSpanFull()
                    ->visible(fn (): bool => auth()->user()->puedeVerConfidencial())
                    ->schema([
                        Textarea::make('notas_internas')
                            ->label('Notas internas')
                            ->rows(4),

                        Textarea::make('estrategia')
                            ->label('Estrategia')
                            ->rows(4),
                    ]),
            ]);
    }
}