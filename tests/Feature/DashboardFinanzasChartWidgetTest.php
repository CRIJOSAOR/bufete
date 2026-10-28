<?php

namespace Tests\Feature;

use App\Filament\Widgets\DashboardFinanzasHnlChartWidget;
use App\Filament\Widgets\DashboardFinanzasUsdChartWidget;
use App\Models\Cliente;
use App\Models\Gasto;
use App\Models\Honorario;
use App\Models\Pago;
use App\Models\RoleDataScope;
use App\Models\User;
use App\Support\RecordAccessScope;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardFinanzasChartWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_financial_charts_separate_currencies_and_exclude_out_of_scope_records(): void
    {
        $this->travelTo('2026-03-10 09:00:00');
        $user = $this->createReportingUser();
        $client = Cliente::create([
            'tipo' => 'natural',
            'nombre' => 'Cliente de gráfica',
        ]);
        $hnlFee = $this->createHonorarium($client, $user, 'HNL');
        $usdFee = $this->createHonorarium($client, $user, 'USD');
        $privateHnlFee = $this->createHonorarium($client, User::factory()->create(), 'HNL');
        $this->createPayment($hnlFee, '25.50');
        $this->createPayment($hnlFee, '10.00', now()->subMonth());
        $this->createPayment($usdFee, '12.00');
        $this->createPayment($privateHnlFee, '80.00');
        $this->createExpense($client, $user, 'HNL', '3.25');
        $this->createExpense($client, $user, 'HNL', '2.00', '2026-02-10');
        $this->createExpense($client, $user, 'USD', '4.25');
        $this->createExpense($client, User::factory()->create(), 'HNL', '40.00');
        $this->assertDatabaseHas('gastos', [
            'creado_por' => $user->getKey(),
            'moneda' => 'HNL',
            'monto' => '3.25',
        ]);

        $this->actingAs($user);

        Livewire::test(DashboardFinanzasHnlChartWidget::class)
            ->assertSee('canvas', false)
            ->assertSee('HNL 25.50')
            ->assertSee('feb. 2026 HNL 10.00')
            ->assertSee('HNL 3.25')
            ->assertSee('feb. 2026 HNL 2.00')
            ->assertDontSee('USD 12.00')
            ->assertDontSee('HNL 80.00')
            ->assertDontSee('HNL 40.00');

        Livewire::test(DashboardFinanzasUsdChartWidget::class)
            ->assertSee('canvas', false)
            ->assertSee('USD 12.00')
            ->assertSee('USD 4.25')
            ->assertDontSee('HNL 25.50');
    }

    public function test_financial_charts_are_hidden_without_reporting_permission(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->assertFalse(DashboardFinanzasHnlChartWidget::canView());
        $this->assertFalse(DashboardFinanzasUsdChartWidget::canView());
    }

    private function createReportingUser(): User
    {
        $role = Role::create([
            'name' => 'dashboard-chart-reporting',
            'guard_name' => 'web',
        ]);
        $role->givePermissionTo(Permission::firstOrCreate([
            'name' => 'consultar_reportes_contables',
            'guard_name' => 'web',
        ]));

        $user = User::factory()->create();
        $user->assignRole($role);

        foreach ([Honorario::class, Gasto::class] as $modelClass) {
            RoleDataScope::create([
                'role_id' => $role->getKey(),
                'model_type' => $modelClass,
                'scope' => RecordAccessScope::CREATED_BY_ME,
            ]);
        }

        return $user;
    }

    private function createHonorarium(Cliente $client, User $creator, string $currency): Honorario
    {
        return Honorario::create([
            'cliente_id' => $client->getKey(),
            'creado_por' => $creator->getKey(),
            'concepto' => 'Honorario de prueba',
            'modalidad' => 'fijo',
            'monto_acordado' => '200.00',
            'moneda' => $currency,
        ]);
    }

    private function createPayment(Honorario $fee, string $amount, ?DateTimeInterface $date = null): Pago
    {
        return Pago::create([
            'honorario_id' => $fee->getKey(),
            'monto' => $amount,
            'fecha_pago' => $date ?? now(),
            'metodo' => 'efectivo',
        ]);
    }

    private function createExpense(Cliente $client, User $creator, string $currency, string $amount, ?string $date = null): Gasto
    {
        return Gasto::create([
            'cliente_id' => $client->getKey(),
            'creado_por' => $creator->getKey(),
            'concepto' => 'Gasto de prueba',
            'categoria' => 'copias',
            'monto' => $amount,
            'moneda' => $currency,
            'fecha' => $date ?? now()->toDateString(),
        ]);
    }
}
