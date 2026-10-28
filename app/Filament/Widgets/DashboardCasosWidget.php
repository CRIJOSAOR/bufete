<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Casos\CasoResource;
use App\Models\Caso;
use App\Models\CasoEvento;
use App\Models\User;
use App\Support\RecordAccessScope;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardCasosWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Resumen de casos';

    protected ?string $description = 'Actividad y volumen de los expedientes que puedes consultar.';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && CasoResource::canViewAny()
            && $user->can('View:Caso');
    }

    protected function getStats(): array
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return [];
        }

        $cases = RecordAccessScope::apply(Caso::query(), $user);
        $activeStates = ['abierto', 'en_tramite', 'en_espera'];
        $activeCount = (clone $cases)->whereIn('estado', $activeStates)->count();
        $newCount = (clone $cases)->where('created_at', '>=', now()->subDays(7))->count();
        $inactiveSince = now()->subDays(30);
        $recentEventCaseIds = CasoEvento::query()
            ->where('ocurrido_en', '>=', $inactiveSince)
            ->select('caso_id');
        $inactiveCount = (clone $cases)
            ->whereIn('estado', $activeStates)
            ->where('casos.updated_at', '<', $inactiveSince)
            ->whereNotIn('casos.id', $recentEventCaseIds)
            ->count();

        return [
            Stat::make('Casos activos', (string) $activeCount)
                ->description('Abiertos, en trámite o en espera')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary'),
            Stat::make('Nuevos en 7 días', (string) $newCount)
                ->description('Expedientes creados recientemente')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('success'),
            Stat::make('Sin actividad por 30 días', (string) $inactiveCount)
                ->description('Casos activos que requieren seguimiento')
                ->descriptionIcon('heroicon-m-clock')
                ->color($inactiveCount > 0 ? 'warning' : 'success'),
        ];
    }
}
