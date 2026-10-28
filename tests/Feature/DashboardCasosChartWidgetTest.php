<?php

namespace Tests\Feature;

use App\Filament\Widgets\DashboardCasosChartWidget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesDashboardFixtures;
use Tests\TestCase;

class DashboardCasosChartWidgetTest extends TestCase
{
    use CreatesDashboardFixtures, RefreshDatabase;

    public function test_case_chart_renders_only_states_from_accessible_cases(): void
    {
        $user = $this->createDashboardUser(
            ['ViewAny:Caso', 'View:Caso'],
            $this->assignedCaseScopes(),
        );
        $openCase = $this->createDashboardCase('EXP-GRAFICA-ABIERTO');
        $openCase->abogados()->attach($user);
        $closedCase = $this->createDashboardCase('EXP-GRAFICA-CERRADO');
        $closedCase->update(['estado' => 'cerrado']);
        $closedCase->abogados()->attach($user);
        $privateCase = $this->createDashboardCase('EXP-GRAFICA-PRIVADO');
        $privateCase->update(['estado' => 'archivado']);

        $this->actingAs($user);

        Livewire::test(DashboardCasosChartWidget::class)
            ->assertSee('canvas', false)
            ->assertSee('Abierto: 1')
            ->assertSee('Cerrado: 1')
            ->assertSee('Archivado: 0');
    }

    public function test_case_chart_is_hidden_without_case_view_permissions(): void
    {
        $user = $this->createDashboardUser(['ViewAny:Caso']);
        $this->actingAs($user);

        $this->assertFalse(DashboardCasosChartWidget::canView());
    }
}
