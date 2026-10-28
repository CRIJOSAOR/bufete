<?php

namespace App\Filament\Widgets;

use App\Models\FondoClienteMovimiento;
use App\Models\Gasto;
use App\Models\Honorario;
use App\Models\User;
use App\Support\AccountingMoney;
use App\Support\RecordAccessScope;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ContabilidadResumenWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Resumen financiero';

    protected ?string $description = 'Control interno del bufete. Los saldos se presentan por moneda y no se convierten entre sí.';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && ($user->isPermissionAdministrator() || $user->can('consultar_reportes_contables'));
    }

    protected function getStats(): array
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return [];
        }

        $feesByCurrency = RecordAccessScope::apply(
            Honorario::query()
                ->select('moneda')
                ->selectRaw('ROUND(SUM(monto_acordado), 2) as monto')
                ->groupBy('moneda'),
            $user,
        )->pluck('monto', 'moneda');

        $paymentsByCurrency = DB::table('pagos')
            ->join('honorarios', 'honorarios.id', '=', 'pagos.honorario_id')
            ->whereIn('honorarios.id', RecordAccessScope::apply(Honorario::query(), $user)->select('honorarios.id'))
            ->selectRaw('honorarios.moneda, ROUND(SUM(pagos.monto), 2) as monto')
            ->groupBy('honorarios.moneda')
            ->pluck('monto', 'moneda');

        $expensesByCurrency = RecordAccessScope::apply(
            Gasto::query()
                ->select('moneda')
                ->selectRaw('ROUND(SUM(monto), 2) as monto')
                ->groupBy('moneda'),
            $user,
        )->pluck('monto', 'moneda');

        $clientFundsByCurrency = RecordAccessScope::apply(
            FondoClienteMovimiento::query()
                ->select('moneda')
                ->selectRaw("ROUND(SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE -monto END), 2) as monto")
                ->groupBy('moneda'),
            $user,
        )->pluck('monto', 'moneda');

        $stats = [];

        foreach (Honorario::MONEDAS as $currency => $label) {
            $fees = AccountingMoney::toMinorUnits((string) ($feesByCurrency[$currency] ?? '0'));
            $paid = AccountingMoney::toMinorUnits((string) ($paymentsByCurrency[$currency] ?? '0'));

            $stats[] = Stat::make("Honorarios pendientes ({$currency})", $currency.' '.AccountingMoney::formatMinorUnits(max(0, $fees - $paid)));
            $stats[] = Stat::make("Pagos recibidos ({$currency})", $currency.' '.AccountingMoney::formatMinorUnits($paid));
            $stats[] = Stat::make("Gastos registrados ({$currency})", $currency.' '.AccountingMoney::formatMinorUnits(AccountingMoney::toMinorUnits((string) ($expensesByCurrency[$currency] ?? '0'))));
            $stats[] = Stat::make("Fondos de clientes ({$currency})", $currency.' '.AccountingMoney::formatMinorUnits(AccountingMoney::toMinorUnits((string) ($clientFundsByCurrency[$currency] ?? '0'))))
                ->color('info');
        }

        return $stats;
    }
}
