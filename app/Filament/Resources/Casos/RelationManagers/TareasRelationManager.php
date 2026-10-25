<?php

namespace App\Filament\Resources\Casos\RelationManagers;

use App\Models\CasoTarea;
use App\Models\User;
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

class TareasRelationManager extends RelationManager
{
    protected static string $relationship = 'tareas';

    protected static ?string $title = 'Tareas';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('titulo')->label('Tarea')->required()->maxLength(255),
            Textarea::make('descripcion')->label('Detalles')->rows(3)->columnSpanFull(),
            Select::make('asignado_a')
                ->label('Responsable')
                ->relationship('asignado', 'name', fn ($query) => $query->where('activo', true))
                ->searchable()
                ->preload(),
            Select::make('prioridad')
                ->options(['baja' => 'Baja', 'normal' => 'Normal', 'alta' => 'Alta', 'urgente' => 'Urgente'])
                ->default('normal')
                ->required(),
            Select::make('estado')
                ->options(['pendiente' => 'Pendiente', 'en_progreso' => 'En progreso', 'completada' => 'Completada'])
                ->default('pendiente')
                ->required(),
            DateTimePicker::make('fecha_limite')->label('Fecha límite'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('fecha_limite')
            ->columns([
                TextColumn::make('titulo')->label('Tarea')->searchable()->wrap(),
                TextColumn::make('asignado.name')->label('Responsable')->placeholder('Sin asignar'),
                TextColumn::make('prioridad')->badge()->color(fn (string $state): string => match ($state) {
                    'urgente' => 'danger',
                    'alta' => 'warning',
                    'baja' => 'gray',
                    default => 'info',
                }),
                TextColumn::make('estado')->badge(),
                TextColumn::make('fecha_limite')->label('Vence')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('estado')->options([
                    'pendiente' => 'Pendiente',
                    'en_progreso' => 'En progreso',
                    'completada' => 'Completada',
                ]),
                SelectFilter::make('prioridad')->options([
                    'baja' => 'Baja',
                    'normal' => 'Normal',
                    'alta' => 'Alta',
                    'urgente' => 'Urgente',
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
