<?php

namespace Tests\Feature;

use App\Filament\Widgets\DashboardCasosWidget;
use App\Models\CasoEvento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesDashboardFixtures;
use Tests\TestCase;

class DashboardCasosWidgetTest extends TestCase
{
    use CreatesDashboardFixtures, RefreshDatabase;

    public function test_case_summary_counts_only_accessible_cases(): void
    {
        $this->travelTo('2026-03-10 09:00:00');
        $user = $this->createDashboardUser(
            ['ViewAny:Caso', 'View:Caso'],
            $this->assignedCaseScopes(),
        );
        $activeCase = $this->createDashboardCase('EXP-ACTIVO');
        $activeCase->abogados()->attach($user);
        $staleCase = $this->createDashboardCase('EXP-INACTIVO');
        $staleCase->abogados()->attach($user);
        DB::table('casos')->where('id', $staleCase->getKey())->update([
            'updated_at' => now()->subDays(31),
            'created_at' => now()->subDays(40),
        ]);

        $recentlyActiveCase = $this->createDashboardCase('EXP-EVENTO-RECIENTE');
        $recentlyActiveCase->abogados()->attach($user);
        DB::table('casos')->where('id', $recentlyActiveCase->getKey())->update([
            'updated_at' => now()->subDays(31),
            'created_at' => now()->subDays(40),
        ]);
        CasoEvento::create([
            'caso_id' => $recentlyActiveCase->getKey(),
            'titulo' => 'Gestión reciente',
            'ocurrido_en' => now()->subDays(2),
        ]);

        $newClosedCase = $this->createDashboardCase('EXP-NUEVO');
        $newClosedCase->abogados()->attach($user);
        $newClosedCase->update(['estado' => 'cerrado']);

        $privateCase = $this->createDashboardCase('EXP-FUERA-ALCANCE');
        $privateCase->update(['estado' => 'abierto']);
        $this->actingAs($user);

        Livewire::test(DashboardCasosWidget::class)
            ->assertSeeInOrder([
                'Casos activos',
                '3',
                'Nuevos en 7 días',
                '2',
                'Sin actividad por 30 días',
                '1',
            ]);
    }

    public function test_case_summary_is_hidden_without_both_case_view_permissions(): void
    {
        $user = $this->createDashboardUser(['ViewAny:Caso']);
        $this->actingAs($user);

        $this->assertFalse(DashboardCasosWidget::canView());
    }
}
