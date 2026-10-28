<?php

namespace Tests\Feature;

use App\Filament\Widgets\DashboardAgendaWidget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesDashboardFixtures;
use Tests\TestCase;

class DashboardAgendaWidgetTest extends TestCase
{
    use CreatesDashboardFixtures, RefreshDatabase;

    public function test_agenda_shows_assigned_upcoming_work_in_accessible_cases_only(): void
    {
        $this->travelTo('2026-03-10 09:00:00');
        $user = $this->createDashboardUser(
            ['ViewAny:Caso', 'View:Caso'],
            $this->assignedCaseScopes(),
        );
        $visibleCase = $this->createDashboardCase('EXP-AGENDA');
        $visibleCase->abogados()->attach($user);
        $visibleCase->tareas()->create([
            'titulo' => 'Preparar audiencia',
            'asignado_a' => $user->getKey(),
            'fecha_limite' => now()->addDays(2),
        ]);
        $visibleCase->tareas()->create([
            'titulo' => 'Vencimiento atrasado',
            'asignado_a' => $user->getKey(),
            'fecha_limite' => now()->subDay(),
        ]);
        $visibleCase->tareas()->create([
            'titulo' => 'Vencimiento antiguo',
            'asignado_a' => $user->getKey(),
            'fecha_limite' => now()->subDays(2),
        ]);
        $visibleCase->audiencias()->create([
            'responsable_id' => $user->getKey(),
            'titulo' => 'Audiencia preliminar',
            'fecha_hora' => now()->addDays(3),
        ]);

        $privateCase = $this->createDashboardCase('EXP-PRIVADO');
        $privateCase->tareas()->create([
            'titulo' => 'Tarea de expediente privado',
            'asignado_a' => $user->getKey(),
            'fecha_limite' => now()->addDay(),
        ]);
        $privateCase->audiencias()->create([
            'responsable_id' => $user->getKey(),
            'titulo' => 'Audiencia privada',
            'fecha_hora' => now()->addDays(2),
        ]);
        $this->actingAs($user);

        Livewire::test(DashboardAgendaWidget::class)
            ->assertSee('Preparar audiencia')
            ->assertSee('Vencimiento atrasado')
            ->assertSeeInOrder(['Vencimiento atrasado', 'Vencimiento antiguo'])
            ->assertSee('Audiencia preliminar')
            ->assertDontSee('Tarea de expediente privado')
            ->assertDontSee('Audiencia privada');
    }

    public function test_agenda_does_not_show_cases_without_view_permissions(): void
    {
        $user = $this->createDashboardUser(['ViewAny:Caso']);
        $this->actingAs($user);

        $this->assertFalse(DashboardAgendaWidget::canView());
    }
}
