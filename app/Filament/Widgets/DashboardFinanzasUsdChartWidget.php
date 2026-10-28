<?php

namespace App\Filament\Widgets;

class DashboardFinanzasUsdChartWidget extends DashboardFinanzasChartWidget
{
    protected static bool $isDiscovered = true;

    protected static ?int $sort = 4;

    protected ?string $heading = 'Tendencia financiera · USD';

    protected ?string $description = 'Pagos y gastos mensuales en dólares; no se convierten a otra moneda.';

    protected function currency(): string
    {
        return 'USD';
    }
}
