<?php

namespace App\Filament\Widgets;

class DashboardFinanzasHnlChartWidget extends DashboardFinanzasChartWidget
{
    protected static bool $isDiscovered = true;

    protected static ?int $sort = 4;

    protected ?string $heading = 'Tendencia financiera · HNL';

    protected ?string $description = 'Pagos y gastos mensuales en lempiras; no se convierten a otra moneda.';

    protected function currency(): string
    {
        return 'HNL';
    }
}
