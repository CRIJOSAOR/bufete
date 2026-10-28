<?php

namespace Tests\Feature;

use App\Filament\Widgets\ActividadRecienteWidget;
use App\Models\Caso;
use App\Models\Cliente;
use App\Models\Honorario;
use App\Models\Pago;
use App\Support\AccountingLedger;
use App\Support\RecordAccessScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesDashboardFixtures;
use Tests\TestCase;

class ActividadRecienteWidgetTest extends TestCase
{
    use CreatesDashboardFixtures, RefreshDatabase;

    public function test_activity_shows_actor_and_only_records_within_the_users_scope(): void
    {
        $permissions = [
            'ViewAny:Caso', 'View:Caso',
            'ViewAny:Cliente', 'View:Cliente',
            'ViewAny:Pago', 'View:Pago', 'Create:Pago',
        ];
        $scopes = [
            Caso::class => RecordAccessScope::ASSIGNED_TO_ME,
            Cliente::class => RecordAccessScope::ASSIGNED_TO_ME,
            Honorario::class => RecordAccessScope::ASSIGNED_TO_ME,
            Pago::class => RecordAccessScope::ASSIGNED_TO_ME,
        ];
        $user = $this->createDashboardUser($permissions, $scopes);
        $this->actingAs($user);
        $visibleCase = $this->createDashboardCase('EXP-ACTIVIDAD');
        $visibleCase->abogados()->attach($user);
        $client = $visibleCase->cliente;
        $client->update(['telefono' => 'NO-DEBE-REGISTRARSE']);
        $privateCase = $this->createDashboardCase('EXP-OCULTO');

        $honorarium = Honorario::create([
            'cliente_id' => $client->getKey(),
            'caso_id' => $visibleCase->getKey(),
            'concepto' => 'Consulta legal',
            'monto_acordado' => '100.00',
            'moneda' => 'HNL',
        ]);
        app(AccountingLedger::class)->registerPayment([
            'honorario_id' => $honorarium->getKey(),
            'monto' => '25.00',
            'fecha_pago' => now(),
            'metodo' => 'efectivo',
            'referencia' => 'REFERENCIA-PRIVADA',
        ]);

        Livewire::test(ActividadRecienteWidget::class)
            ->assertSee('Se registró el cliente Cliente EXP-ACTIVIDAD')
            ->assertSee('Se creó el expediente EXP-ACTIVIDAD')
            ->assertSee('Se registró un pago')
            ->assertSee($user->name)
            ->assertDontSee('EXP-OCULTO')
            ->assertDontSee('NO-DEBE-REGISTRARSE')
            ->assertDontSee('REFERENCIA-PRIVADA');
    }

    public function test_activity_is_hidden_when_the_user_has_no_record_view_permission(): void
    {
        $user = $this->createDashboardUser(['ViewAny:Caso']);
        $this->actingAs($user);

        $this->assertFalse(ActividadRecienteWidget::canView());
    }
}
