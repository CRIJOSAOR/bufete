<?php

namespace Tests\Feature;

use App\Filament\Resources\Casos\CasoResource;
use App\Filament\Widgets\MisTareasWidget;
use App\Models\Caso;
use App\Models\Cliente;
use App\Models\RoleDataScope;
use App\Models\User;
use App\Support\RecordAccessScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MisTareasWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_only_open_tasks_assigned_to_the_user_in_accessible_cases(): void
    {
        $role = $this->caseViewerRole();
        $user = User::factory()->create(['activo' => true]);
        $user->assignRole($role);

        $visibleCase = $this->createCase('EXP-VISIBLE');
        $visibleCase->abogados()->attach($user);
        $visibleCase->tareas()->create([
            'titulo' => 'Preparar escrito',
            'asignado_a' => $user->getKey(),
            'fecha_limite' => now()->addDay(),
        ]);
        $visibleCase->tareas()->create([
            'titulo' => 'Revisar vencimiento',
            'asignado_a' => $user->getKey(),
            'fecha_limite' => now()->subDay(),
        ]);
        $visibleCase->tareas()->create([
            'titulo' => 'Tarea de otro responsable',
            'asignado_a' => User::factory()->create()->getKey(),
        ]);
        $visibleCase->tareas()->create([
            'titulo' => 'Tarea ya completada',
            'asignado_a' => $user->getKey(),
            'estado' => 'completada',
        ]);

        $outOfScopeCase = $this->createCase('EXP-PRIVADO');
        $outOfScopeCase->tareas()->create([
            'titulo' => 'Tarea de caso privado',
            'asignado_a' => $user->getKey(),
        ]);

        $this->actingAs($user);

        Livewire::test(MisTareasWidget::class)
            ->assertSee('Preparar escrito')
            ->assertSee('Vencida')
            ->assertSee('EXP-VISIBLE')
            ->assertDontSee('Tarea de otro responsable')
            ->assertDontSee('Tarea ya completada')
            ->assertDontSee('Tarea de caso privado')
            ->assertDontSee(CasoResource::getUrl('edit', ['record' => $visibleCase]));
    }

    public function test_dashboard_hides_my_tasks_without_both_case_view_permissions(): void
    {
        $role = Role::create(['name' => 'case-list-only', 'guard_name' => 'web']);
        $role->givePermissionTo(Permission::create([
            'name' => 'ViewAny:Caso',
            'guard_name' => 'web',
        ]));
        $user = User::factory()->create(['activo' => true]);
        $user->assignRole($role);

        $this->actingAs($user);

        $this->assertFalse(MisTareasWidget::canView());

        $role->givePermissionTo(Permission::create([
            'name' => 'View:Caso',
            'guard_name' => 'web',
        ]));

        $this->assertTrue(MisTareasWidget::canView());
    }

    public function test_dashboard_does_not_show_tasks_when_case_data_scope_is_unconfigured(): void
    {
        $role = $this->caseViewerRole(withDataScope: false);
        $user = User::factory()->create(['activo' => true]);
        $user->assignRole($role);
        $case = $this->createCase('EXP-SIN-ALCANCE');
        $case->abogados()->attach($user);
        $case->tareas()->create([
            'titulo' => 'No debe aparecer',
            'asignado_a' => $user->getKey(),
        ]);
        $this->actingAs($user);

        Livewire::test(MisTareasWidget::class)
            ->assertDontSee('No debe aparecer')
            ->assertSee('No tienes tareas pendientes');
    }

    private function caseViewerRole(bool $withDataScope = true): Role
    {
        $role = Role::create(['name' => 'case-viewer', 'guard_name' => 'web']);
        $role->givePermissionTo([
            Permission::create(['name' => 'ViewAny:Caso', 'guard_name' => 'web']),
            Permission::create(['name' => 'View:Caso', 'guard_name' => 'web']),
        ]);

        if ($withDataScope) {
            RoleDataScope::create([
                'role_id' => $role->getKey(),
                'model_type' => Caso::class,
                'scope' => RecordAccessScope::ASSIGNED_TO_ME,
            ]);
        }

        return $role;
    }

    private function createCase(string $number): Caso
    {
        $client = Cliente::create([
            'tipo' => 'natural',
            'nombre' => 'Cliente '.$number,
        ]);

        return Caso::create([
            'numero' => $number,
            'titulo' => 'Caso '.$number,
            'cliente_id' => $client->getKey(),
            'tipo' => 'civil',
        ]);
    }
}
