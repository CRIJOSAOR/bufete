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

class EventosRelationManager extends RelationManager
{
    protected static string $relationship = 'eventos';

    protected static ?string $title = 'Cronología';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('tipo')
                ->options([
                    'actuacion' => 'Actuación procesal',
                    'notificacion' => 'Notificación',
                    'comunicacion' => 'Comunicación con cliente',
                    'reunion' => 'Reunión',
                    'nota' => 'Nota',
                ])
                ->default('nota')
                ->required(),
            TextInput::make('titulo')->label('Título')->required()->maxLength(255),
            DateTimePicker::make('ocurrido_en')->label('Fecha y hora')->required()->default(now()),
            Textarea::make('descripcion')->label('Detalle')->rows(5)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('ocurrido_en', 'desc')
            ->columns([
                TextColumn::make('ocurrido_en')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('tipo')->badge(),
                TextColumn::make('titulo')->label('Actuación')->searchable()->wrap(),
                TextColumn::make('creador.name')->label('Registrado por'),
                TextColumn::make('descripcion')->label('Detalle')->limit(70)->wrap(),
            ])
            ->filters([
                SelectFilter::make('tipo')->options([
                    'actuacion' => 'Actuación procesal',
                    'notificacion' => 'Notificación',
                    'comunicacion' => 'Comunicación con cliente',
                    'reunion' => 'Reunión',
                    'nota' => 'Nota',
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
