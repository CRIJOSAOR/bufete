<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Casos\CasoResource;
use App\Filament\Resources\Clientes\ClienteResource;
use App\Filament\Resources\Contabilidad\Gastos\GastoResource;
use App\Filament\Resources\Contabilidad\Pagos\PagoResource;
use App\Models\User;
use Filament\Widgets\Widget;

class AccesosRapidosWidget extends Widget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.accesos-rapidos-widget';

    public static function canView(): bool
    {
        return self::actions() !== [];
    }

    protected function getViewData(): array
    {
        return ['actions' => self::actions()];
    }

    /**
     * @return array<array{label: string, description: string, icon: string, url: string}>
     */
    private static function actions(): array
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return [];
        }

        $actions = [];

        if (PagoResource::canCreate()) {
            $actions[] = [
                'label' => 'Registrar pago',
                'description' => 'Anotar un honorario recibido',
                'icon' => 'heroicon-o-banknotes',
                'url' => PagoResource::getUrl('create'),
            ];
        }

        if (GastoResource::canCreate()) {
            $actions[] = [
                'label' => 'Registrar gasto',
                'description' => 'Guardar un gasto del bufete o de un caso',
                'icon' => 'heroicon-o-receipt-refund',
                'url' => GastoResource::getUrl('create'),
            ];
        }

        if (ClienteResource::canCreate()) {
            $actions[] = [
                'label' => 'Crear cliente',
                'description' => 'Agregar un nuevo cliente',
                'icon' => 'heroicon-o-user-plus',
                'url' => ClienteResource::getUrl('create'),
            ];
        }

        if (CasoResource::canCreate()) {
            $actions[] = [
                'label' => 'Abrir un caso',
                'description' => 'Iniciar un nuevo expediente',
                'icon' => 'heroicon-o-briefcase',
                'url' => CasoResource::getUrl('create'),
            ];
        }

        if (CasoResource::canViewAny() && $user->can('View:Caso')) {
            $actions[] = [
                'label' => 'Abrir casos',
                'description' => 'Consultar expedientes autorizados',
                'icon' => 'heroicon-o-folder-open',
                'url' => CasoResource::getUrl('index'),
            ];
        }

        if (DashboardAgendaWidget::canView()) {
            $actions[] = [
                'label' => 'Consultar agenda',
                'description' => 'Ver pendientes y audiencias próximas',
                'icon' => 'heroicon-o-calendar-days',
                'url' => '#dashboard-agenda',
            ];
        }

        return $actions;
    }
}
