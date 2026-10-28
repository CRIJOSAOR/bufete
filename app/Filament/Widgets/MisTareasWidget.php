<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Casos\CasoResource;
use App\Models\Caso;
use App\Models\CasoTarea;
use App\Models\User;
use App\Support\RecordAccessScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class MisTareasWidget extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && CasoResource::canViewAny()
            && $user->can('View:Caso');
    }

    public static function queryForUser(User $user): Builder
    {
        return CasoTarea::query()
            ->where('asignado_a', $user->getKey())
            ->whereIn('estado', ['pendiente', 'en_progreso'])
            ->whereIn(
                'caso_id',
                RecordAccessScope::apply(Caso::query(), $user)->select('casos.id'),
            )
            ->with('caso:id,numero,titulo')
            ->orderByRaw('fecha_limite IS NULL')
            ->orderBy('fecha_limite')
            ->orderBy('id');
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->query($user instanceof User
                ? self::queryForUser($user)
                : CasoTarea::query()->whereRaw('1 = 0'))
            ->heading('Mis tareas')
            ->description('Pendientes asignadas a ti en los casos que puedes consultar.')
            ->emptyStateHeading('No tienes tareas pendientes')
            ->emptyStateDescription('Las tareas asignadas a ti aparecerán aquí.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->defaultPaginationPageOption(6)
            ->paginationPageOptions([6, 10, 25])
            ->columns([
                TextColumn::make('titulo')
                    ->label('Tarea')
                    ->weight('medium')
                    ->wrap(),
                TextColumn::make('caso.numero')
                    ->label('Expediente')
                    ->description(fn (CasoTarea $record): string => $record->caso->titulo)
                    ->url(fn (CasoTarea $record): ?string => CasoResource::canEdit($record->caso)
                        ? CasoResource::getUrl('edit', ['record' => $record->caso])
                        : null)
                    ->wrap(),
                TextColumn::make('fecha_limite')
                    ->label('Fecha límite')
                    ->formatStateUsing(fn (CasoTarea $record): string => $record->fecha_limite === null
                        ? 'Sin fecha'
                        : ($record->fecha_limite->isPast() ? 'Vencida · ' : '')
                            .$record->fecha_limite->format('d/m/Y H:i'))
                    ->color(fn (CasoTarea $record): string => $record->fecha_limite?->isPast()
                        ? 'danger'
                        : 'gray'),
                TextColumn::make('prioridad')
                    ->label('Prioridad')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'urgente' => 'Urgente',
                        'alta' => 'Alta',
                        'baja' => 'Baja',
                        default => 'Normal',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'urgente' => 'danger',
                        'alta' => 'warning',
                        'baja' => 'gray',
                        default => 'info',
                    }),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'en_progreso' => 'En progreso',
                        default => 'Pendiente',
                    })
                    ->color(fn (string $state): string => $state === 'en_progreso' ? 'info' : 'warning'),
            ]);
    }
}
