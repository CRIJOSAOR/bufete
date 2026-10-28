<?php

namespace Tests\Feature;

use App\Filament\Widgets\AccesosRapidosWidget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesDashboardFixtures;
use Tests\TestCase;

class AccesosRapidosWidgetTest extends TestCase
{
    use CreatesDashboardFixtures, RefreshDatabase;

    public function test_quick_actions_only_show_resources_the_user_can_access(): void
    {
        $user = $this->createDashboardUser([
            'ViewAny:Caso',
            'View:Caso',
            'Create:Caso',
            'Create:Cliente',
            'Create:Gasto',
        ]);
        $this->actingAs($user);

        Livewire::test(AccesosRapidosWidget::class)
            ->assertSee('Abrir casos')
            ->assertSee('Crear cliente')
            ->assertSee('Registrar gasto')
            ->assertDontSee('Registrar pago');
    }

    public function test_quick_actions_are_hidden_when_the_user_has_no_available_shortcuts(): void
    {
        $user = $this->createDashboardUser([]);
        $this->actingAs($user);

        $this->assertFalse(AccesosRapidosWidget::canView());
    }
}
