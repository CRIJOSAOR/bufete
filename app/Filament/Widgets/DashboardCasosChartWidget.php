<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Casos\CasoResource;
use App\Models\Caso;
use App\Models\User;
use App\Support\RecordAccessScope;
use Filament\Widgets\ChartWidget;

class DashboardCasosChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Casos por estado';

    protected ?string $description = 'Distribución de los expedientes que puedes consultar.';

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = null;

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && CasoResource::canViewAny()
            && $user->can('View:Caso');
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return [];
        }

        $counts = RecordAccessScope::apply(Caso::query(), $user)
            ->select('estado')
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('estado')
            ->pluck('total', 'estado');
        $data = collect(Caso::ESTADOS)
            ->keys()
            ->map(fn (string $state): int => (int) ($counts[$state] ?? 0))
            ->all();

        if (array_sum($data) === 0) {
            return [];
        }

        return [
            'labels' => array_values(Caso::ESTADOS),
            'datasets' => [[
                'data' => $data,
                'backgroundColor' => ['#315D75', '#568B88', '#D6A052', '#8292A1', '#B46F70'],
                'borderWidth' => 0,
                'hoverOffset' => 6,
            ]],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'cutout' => '66%',
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 16,
                    ],
                ],
            ],
        ];
    }

    public function getChartAssistiveContent(): ?string
    {
        $data = $this->getCachedData();

        if ($data === []) {
            return null;
        }

        return collect(array_values(Caso::ESTADOS))
            ->map(fn (string $label, int $index): string => $label.': '.(int) ($data['datasets'][0]['data'][$index] ?? 0))
            ->implode('; ');
    }
}
