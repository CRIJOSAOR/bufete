<?php

namespace App\Filament\Resources\Casos\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AudienciasRelationManager extends RelationManager
{
    protected static string $relationship = 'audiencias';

    protected static ?string $title = 'Audiencias y citas';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('titulo')->label('Audiencia o cita')->required()->maxLength(255),
            DateTimePicker::make('fecha_hora')->label('Fecha y hora')->required(),
            Select::make('responsable_id')
                ->label('Responsable')
                ->relationship('responsable', 'name', fn ($query) => $query->where('activo', true))
                ->searchable()
                ->preload(),
            TextInput::make('lugar')->label('Lugar o enlace')->maxLength(255),
            Select::make('modalidad')
                ->options(['presencial' => 'Presencial', 'virtual' => 'Virtual', 'telefonica' => 'Telefónica'])
                ->default('presencial')
                ->required(),
            Select::make('estado')
                ->options(['programada' => 'Programada', 'realizada' => 'Realizada', 'cancelada' => 'Cancelada', 'reprogramada' => 'Reprogramada'])
                ->default('programada')
                ->required(),
            Textarea::make('resultado')->label('Resultado')->rows(3)->columnSpanFull(),
            Textarea::make('notas')->label('Notas de preparación')->rows(3)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('fecha_hora')
            ->columns([
                TextColumn::make('titulo')->label('Audiencia / cita')->searchable()->wrap(),
                TextColumn::make('fecha_hora')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('responsable.name')->label('Responsable')->placeholder('Sin asignar'),
                TextColumn::make('modalidad')->badge(),
                TextColumn::make('estado')->badge(),
                TextColumn::make('lugar')->label('Lugar')->limit(35),
            ])
            ->filters([
                SelectFilter::make('estado')->options([
                    'programada' => 'Programada',
                    'realizada' => 'Realizada',
                    'cancelada' => 'Cancelada',
                    'reprogramada' => 'Reprogramada',
                ]),
            ])
            ->headerActions([
                CreateAction::make()->authorize(fn (): bool => auth()->user()->can('Update:Caso')),
            ])
            ->recordActions([
                EditAction::make()->authorize(fn (): bool => auth()->user()->can('Update:Caso')),
                DeleteAction::make()->authorize(fn (): bool => auth()->user()->can('Update:Caso')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->authorize(fn (): bool => auth()->user()->can('Update:Caso')),
                ]),
            ]);
    }
}
