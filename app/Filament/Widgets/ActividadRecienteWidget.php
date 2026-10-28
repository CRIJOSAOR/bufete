<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Casos\CasoResource;
use App\Filament\Resources\Clientes\ClienteResource;
use App\Filament\Resources\Contabilidad\Pagos\PagoResource;
use App\Models\Caso;
use App\Models\Cliente;
use App\Models\Pago;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class ActividadRecienteWidget extends TableWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Actividad reciente';

    protected static ?string $description = 'Cambios recientes en los registros que puedes consultar.';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User && self::hasVisibleResources($user);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->activityQuery())
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->emptyStateHeading('Sin actividad reciente')
            ->emptyStateDescription('Los cambios de tus casos, clientes y pagos aparecerán aquí.')
            ->columns([
                TextColumn::make('description')
                    ->label('Movimiento')
                    ->limit(90)
                    ->wrap(),
                TextColumn::make('subject_label')
                    ->label('Registro')
                    ->state(fn (Activity $record): string => self::subjectLabel($record)),
                TextColumn::make('causer.name')
                    ->label('Realizado por')
                    ->placeholder('Sistema'),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i'),
            ]);
    }

    private function activityQuery(): Builder
    {
        $user = auth()->user();
        $query = Activity::query()->with(['causer', 'subject'])->latest()->limit(8);

        if (! $user instanceof User) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $query) use ($user): void {
            foreach (self::visibleResources($user) as [$modelClass, $resourceClass]) {
                $model = new $modelClass;
                $visibleRecords = $resourceClass::getEloquentQuery()
                    ->select($model->qualifyColumn($model->getKeyName()));

                $query->orWhere(function (Builder $query) use ($model, $visibleRecords): void {
                    $query->where('subject_type', $model->getMorphClass())
                        ->whereIn('subject_id', $visibleRecords);
                });
            }

            if (! self::hasVisibleResources($user)) {
                $query->whereRaw('1 = 0');
            }
        });
    }

    /**
     * @return array<array{class-string, class-string}>
     */
    private static function visibleResources(User $user): array
    {
        $resources = [];

        foreach ([
            [Caso::class, CasoResource::class, 'View:Caso'],
            [Cliente::class, ClienteResource::class, 'View:Cliente'],
            [Pago::class, PagoResource::class, 'View:Pago'],
        ] as [$modelClass, $resourceClass, $permission]) {
            if ($resourceClass::canViewAny() && $user->can($permission)) {
                $resources[] = [$modelClass, $resourceClass];
            }
        }

        return $resources;
    }

    private static function hasVisibleResources(User $user): bool
    {
        return self::visibleResources($user) !== [];
    }

    private static function subjectLabel(Activity $activity): string
    {
        return match (true) {
            $activity->subject instanceof Caso => $activity->subject->numero.' · '.$activity->subject->titulo,
            $activity->subject instanceof Cliente => $activity->subject->nombre,
            $activity->subject instanceof Pago => 'Pago #'.$activity->subject->getKey(),
            default => 'Registro',
        };
    }
}
