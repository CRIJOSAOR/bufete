<?php

namespace App\Filament\Resources\Casos\Tables;

use App\Models\Caso;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CasosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('numero')
                    ->label('Expediente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('titulo')
                    ->label('Caso')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->searchable(),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Caso::TIPOS[$state] ?? $state),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Caso::ESTADOS[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'abierto'    => 'success',
                        'en_tramite' => 'info',
                        'en_espera'  => 'warning',
                        default      => 'gray',
                    }),

                TextColumn::make('abogados.name')
                    ->label('Abogados')
                    ->badge()
                    ->placeholder('Sin asignar'),

                TextColumn::make('juzgado')
                    ->label('Juzgado')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(Caso::ESTADOS),

                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options(Caso::TIPOS),

                TrashedFilter::make()->label('Eliminados'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}