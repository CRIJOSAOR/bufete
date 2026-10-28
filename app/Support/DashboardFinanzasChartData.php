<?php

namespace App\Support;

use App\Models\Gasto;
use App\Models\Honorario;
use App\Models\Pago;
use App\Models\User;

class DashboardFinanzasChartData
{
    /**
     * @return array{labels: array<string>, payments: array<float>, expenses: array<float>}
     */
    public static function monthlyTotals(User $user, string $currency): array
    {
        $now = now();
        $endOfToday = $now->copy()->endOfDay();
        $months = collect(range(5, 0))
            ->map(fn (int $monthsAgo) => $now->copy()->startOfMonth()->subMonths($monthsAgo))
            ->values();
        $periods = $months->map(fn ($month) => [
            'start' => $month,
            'end' => $month->copy()->endOfMonth()->min($endOfToday),
            'label' => $month->copy()->locale('es')->translatedFormat('M Y'),
        ]);
        $firstMonth = $periods->first()['start'];

        $paymentTotals = Pago::query()
            ->join('honorarios', 'honorarios.id', '=', 'pagos.honorario_id')
            ->where('honorarios.moneda', $currency)
            ->whereIn(
                'honorarios.id',
                RecordAccessScope::apply(Honorario::query(), $user)->select('honorarios.id'),
            )
            ->whereBetween('pagos.fecha_pago', [$firstMonth, $endOfToday]);

        $expenseTotals = RecordAccessScope::apply(
            Gasto::query()
                ->where('moneda', $currency)
                ->whereBetween('fecha', [$firstMonth->copy()->startOfDay(), $endOfToday]),
            $user,
        );

        foreach ($periods as $index => $period) {
            $paymentTotals->selectRaw(
                "COALESCE(SUM(CASE WHEN pagos.fecha_pago BETWEEN ? AND ? THEN pagos.monto ELSE 0 END), 0) AS month_{$index}",
                [$period['start'], $period['end']],
            );

            $expenseTotals->selectRaw(
                "COALESCE(SUM(CASE WHEN fecha BETWEEN ? AND ? THEN monto ELSE 0 END), 0) AS month_{$index}",
                [$period['start']->copy()->startOfDay(), $period['end']->copy()->endOfDay()],
            );
        }

        $paymentTotals = $paymentTotals->first();
        $expenseTotals = $expenseTotals->first();
        $payments = [];
        $expenses = [];

        foreach ($periods as $index => $period) {
            $payments[] = AccountingMoney::toMinorUnits((string) $paymentTotals->getAttribute("month_{$index}")) / 100;
            $expenses[] = AccountingMoney::toMinorUnits((string) $expenseTotals->getAttribute("month_{$index}")) / 100;
        }

        return [
            'labels' => $periods->pluck('label')->all(),
            'payments' => $payments,
            'expenses' => $expenses,
        ];
    }
}
