<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Casos\CasoResource;
use App\Models\Caso;
use App\Models\CasoAudiencia;
use App\Models\User;
use App\Support\RecordAccessScope;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class DashboardAgendaWidget extends Widget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.dashboard-agenda-widget';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && CasoResource::canViewAny()
            && $user->can('View:Caso');
    }

    protected function getViewData(): array
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return [
                'overdueTasks' => collect(),
                'upcomingTasks' => collect(),
                'upcomingHearings' => collect(),
            ];
        }

        $now = now();
        $endOfWeek = $now->copy()->addDays(7)->endOfDay();
        $tasks = MisTareasWidget::queryForUser($user);

        $overdueTasks = (clone $tasks)
            ->whereNotNull('fecha_limite')
            ->where('fecha_limite', '<', $now)
            ->reorder('fecha_limite', 'desc')
            ->limit(3)
            ->get();

        $upcomingTasks = (clone $tasks)
            ->whereBetween('fecha_limite', [$now, $endOfWeek])
            ->reorder('fecha_limite')
            ->limit(5)
            ->get();

        $accessibleCaseIds = RecordAccessScope::apply(Caso::query(), $user)
            ->select('casos.id');

        $upcomingHearings = CasoAudiencia::query()
            ->whereIn('caso_id', $accessibleCaseIds)
            ->whereBetween('fecha_hora', [$now, $endOfWeek])
            ->whereIn('estado', ['programada', 'reprogramada'])
            ->where(function (Builder $query) use ($user): void {
                $query->where('responsable_id', $user->getKey())
                    ->orWhereNull('responsable_id');
            })
            ->with('caso:id,numero,titulo')
            ->orderBy('fecha_hora')
            ->limit(5)
            ->get();

        return [
            'overdueTasks' => $overdueTasks,
            'upcomingTasks' => $upcomingTasks,
            'upcomingHearings' => $upcomingHearings,
        ];
    }
}
