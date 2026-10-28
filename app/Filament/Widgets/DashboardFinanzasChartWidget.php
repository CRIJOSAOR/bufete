<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Support\AccountingMoney;
use App\Support\DashboardFinanzasChartData;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

abstract class DashboardFinanzasChartWidget extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = null;

    abstract protected function currency(): string;

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && ($user->isPermissionAdministrator() || $user->can('consultar_reportes_contables'));
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return [];
        }

        $currency = $this->currency();
        $totals = DashboardFinanzasChartData::monthlyTotals($user, $currency);

        return [
            'labels' => $totals['labels'],
            'datasets' => [
                [
                    'label' => 'Pagos recibidos',
                    'data' => $totals['payments'],
                    'backgroundColor' => '#397C73',
                    'borderRadius' => 5,
                    'borderSkipped' => false,
                ],
                [
                    'label' => 'Gastos registrados',
                    'data' => $totals['expenses'],
                    'backgroundColor' => '#D9954F',
                    'borderRadius' => 5,
                    'borderSkipped' => false,
                ],
            ],
        ];
    }

    protected function getOptions(): array|RawJs|null
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 16,
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => ['display' => false],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => $this->currency(),
                    ],
                ],
            ],
        ];
    }

    public function getChartAssistiveContent(): ?string
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return null;
        }

        $data = $this->getCachedData();
        $lines = [];

        foreach ($data['datasets'] ?? [] as $dataset) {
            $points = [];

            foreach ($data['labels'] ?? [] as $index => $label) {
                $amount = AccountingMoney::formatMinorUnits((int) round($dataset['data'][$index] * 100));
                $points[] = "{$label} {$this->currency()} {$amount}";
            }

            $lines[] = $dataset['label'].': '.implode(', ', $points);
        }

        return implode('. ', $lines);
    }
}
