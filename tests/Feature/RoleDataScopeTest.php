<?php

namespace Tests\Feature;

use App\Models\Caso;
use App\Models\Cliente;
use App\Models\RoleDataScope;
use App\Models\User;
use App\Filament\Resources\Clientes\ClienteResource;
use App\Filament\Resources\Roles\RoleResource;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Support\RecordAccessScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleDataScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_created_by_scope_only_returns_records_created_by_the_user(): void
    {
        $role = Role::create(['name' => 'limited', 'guard_name' => 'web']);
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $viewer->assignRole($role);

        $ownClient = Cliente::create([
            'tipo' => 'natural',
            'nombre' => 'Cliente propio',
            'creado_por' => $viewer->id,
        ]);

        Cliente::create([
            'tipo' => 'natural',
            'nombre' => 'Cliente ajeno',
            'creado_por' => $owner->id,
        ]);

        RoleDataScope::create([
            'role_id' => $role->id,
            'model_type' => Cliente::class,
            'scope' => RecordAccessScope::CREATED_BY_ME,
        ]);

        $this->actingAs($viewer);
        $visibleIds = ClienteResource::getEloquentQuery()->pluck('id')->all();

        $this->assertSame([$ownClient->id], $visibleIds);
    }

    public function test_missing_scope_does_not_grant_records(): void
    {
        $role = Role::create(['name' => 'unconfigured', 'guard_name' => 'web']);
        $viewer = User::factory()->create();
        $viewer->assignRole($role);
        Cliente::create(['tipo' => 'natural', 'nombre' => 'No debe aparecer']);

        $visibleIds = RecordAccessScope::apply(Cliente::query(), $viewer)->pluck('id')->all();

        $this->assertSame([], $visibleIds);
    }

    public function test_created_and_assigned_scopes_are_combined(): void
    {
        $role = Role::create(['name' => 'combined', 'guard_name' => 'web']);
        $viewer = User::factory()->create();
        $viewer->assignRole($role);

        $createdClient = Cliente::create([
            'tipo' => 'natural',
            'nombre' => 'Creado por el usuario',
            'creado_por' => $viewer->id,
        ]);
        $assignedClient = Cliente::create(['tipo' => 'natural', 'nombre' => 'Asignado al usuario']);
        $unrelatedClient = Cliente::create(['tipo' => 'natural', 'nombre' => 'Sin relación']);
        $assignedCase = Caso::create([
            'numero' => 'EXP-5',
            'titulo' => 'Caso asignado',
            'cliente_id' => $assignedClient->id,
            'tipo' => 'civil',
        ]);
        Caso::create([
            'numero' => 'EXP-6',
            'titulo' => 'Caso de otro cliente',
            'cliente_id' => $unrelatedClient->id,
            'tipo' => 'civil',
        ]);
        $assignedCase->abogados()->attach($viewer);

        RoleDataScope::insert([
            [
                'role_id' => $role->id,
                'model_type' => Cliente::class,
                'scope' => RecordAccessScope::CREATED_BY_ME,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => $role->id,
                'model_type' => Cliente::class,
                'scope' => RecordAccessScope::ASSIGNED_TO_ME,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $visibleIds = RecordAccessScope::apply(Cliente::query(), $viewer)->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$createdClient->id, $assignedClient->id], $visibleIds);
        $this->assertNotContains($unrelatedClient->id, $visibleIds);
    }

    public function test_assigned_scope_returns_cases_assigned_to_the_user(): void
    {
        $role = Role::create(['name' => 'assigned', 'guard_name' => 'web']);
        $viewer = User::factory()->create();
        $viewer->assignRole($role);

        $assignedClient = Cliente::create(['tipo' => 'natural', 'nombre' => 'Asignado']);
        $otherClient = Cliente::create(['tipo' => 'natural', 'nombre' => 'Otro']);

        $assignedCase = Caso::create([
            'numero' => 'EXP-1',
            'titulo' => 'Caso asignado',
            'cliente_id' => $assignedClient->id,
            'tipo' => 'civil',
        ]);

        Caso::create([
            'numero' => 'EXP-2',
            'titulo' => 'Caso no asignado',
            'cliente_id' => $otherClient->id,
            'tipo' => 'civil',
        ]);

        $assignedCase->abogados()->attach($viewer);

        RoleDataScope::create([
            'role_id' => $role->id,
            'model_type' => Caso::class,
            'scope' => RecordAccessScope::ASSIGNED_TO_ME,
        ]);

        $visibleIds = RecordAccessScope::apply(Caso::query(), $viewer)->pluck('id')->all();

        $this->assertSame([$assignedCase->id], $visibleIds);
    }

    public function test_assigned_scope_returns_clients_related_to_the_users_cases(): void
    {
        $role = Role::create(['name' => 'client-assigned', 'guard_name' => 'web']);
        $viewer = User::factory()->create();
        $viewer->assignRole($role);

        $assignedClient = Cliente::create(['tipo' => 'natural', 'nombre' => 'Cliente asignado']);
        $otherClient = Cliente::create(['tipo' => 'natural', 'nombre' => 'Otro cliente']);

        $assignedCase = Caso::create([
            'numero' => 'EXP-3',
            'titulo' => 'Caso del cliente asignado',
            'cliente_id' => $assignedClient->id,
            'tipo' => 'civil',
        ]);

        Caso::create([
            'numero' => 'EXP-4',
            'titulo' => 'Caso del otro cliente',
            'cliente_id' => $otherClient->id,
            'tipo' => 'civil',
        ]);

        $assignedCase->abogados()->attach($viewer);

        RoleDataScope::create([
            'role_id' => $role->id,
            'model_type' => Cliente::class,
            'scope' => RecordAccessScope::ASSIGNED_TO_ME,
        ]);

        $visibleIds = RecordAccessScope::apply(Cliente::query(), $viewer)->pluck('id')->all();

        $this->assertSame([$assignedClient->id], $visibleIds);
    }

    public function test_scope_options_are_derived_from_model_capabilities(): void
    {
        $this->assertSame(
            [
                RecordAccessScope::ALL,
                RecordAccessScope::CREATED_BY_ME,
                RecordAccessScope::ASSIGNED_TO_ME,
            ],
            array_keys(RecordAccessScope::optionsFor(Cliente::class)),
        );
    }

    public function test_role_scope_settings_sync_for_discovered_resources(): void
    {
        $role = Role::create(['name' => 'scope-manager', 'guard_name' => 'web']);

        RecordAccessScope::syncForRole($role, [
            Cliente::class => [
                RecordAccessScope::CREATED_BY_ME,
                RecordAccessScope::ASSIGNED_TO_ME,
            ],
            Caso::class => [RecordAccessScope::ASSIGNED_TO_ME],
        ]);

        $this->assertDatabaseHas('role_data_scopes', [
            'role_id' => $role->id,
            'model_type' => Cliente::class,
            'scope' => RecordAccessScope::CREATED_BY_ME,
        ]);
        $this->assertDatabaseHas('role_data_scopes', [
            'role_id' => $role->id,
            'model_type' => Cliente::class,
            'scope' => RecordAccessScope::ASSIGNED_TO_ME,
        ]);
        $this->assertDatabaseHas('role_data_scopes', [
            'role_id' => $role->id,
            'model_type' => Caso::class,
            'scope' => RecordAccessScope::ASSIGNED_TO_ME,
        ]);
        $this->assertDatabaseHas('role_data_scopes', [
            'role_id' => $role->id,
            'model_type' => User::class,
            'scope' => RecordAccessScope::ALL,
        ]);
    }

    public function test_editing_a_role_reloads_its_saved_data_scopes(): void
    {
        $role = Role::create(['name' => 'edit-scopes', 'guard_name' => 'web']);

        RoleDataScope::insert([
            [
                'role_id' => $role->id,
                'model_type' => Cliente::class,
                'scope' => RecordAccessScope::CREATED_BY_ME,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role_id' => $role->id,
                'model_type' => Cliente::class,
                'scope' => RecordAccessScope::ASSIGNED_TO_ME,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $selected = RoleResource::getSelectedDataScopes($role, Cliente::class);

        $this->assertSame([
            RecordAccessScope::ASSIGNED_TO_ME,
            RecordAccessScope::CREATED_BY_ME,
        ], $selected);
    }

    public function test_editing_a_role_reloads_its_saved_custom_permissions(): void
    {
        $role = Role::create(['name' => 'edit-custom', 'guard_name' => 'web']);
        $confidential = Permission::create(['name' => 'ver_confidencial', 'guard_name' => 'web']);
        Permission::create(['name' => 'asignar_abogados', 'guard_name' => 'web']);
        $role->givePermissionTo($confidential);

        $selected = RoleResource::getSelectedCustomPermissions(
            $role,
            ['ver_confidencial', 'asignar_abogados'],
        );

        $this->assertSame(['ver_confidencial'], $selected);
    }

    public function test_only_the_configured_super_admin_can_manage_roles(): void
    {
        $policy = new RolePolicy;
        $role = Role::create(['name' => 'ordinary', 'guard_name' => 'web']);
        $administrator = User::factory()->create();
        $administrator->assignRole(Role::create([
            'name' => config('filament-shield.super_admin.name'),
            'guard_name' => 'web',
        ]));
        $ordinaryUser = User::factory()->create();
        $ordinaryRole = Role::findByName('ordinary', 'web');
        $ordinaryRole->givePermissionTo(Permission::create([
            'name' => 'Update:Role',
            'guard_name' => 'web',
        ]));
        $ordinaryUser->assignRole($ordinaryRole);

        $this->assertTrue($policy->update($administrator, $role));
        $this->assertFalse($policy->update($ordinaryUser, $role));
    }

    public function test_only_administrators_can_change_user_role_assignments(): void
    {
        $ordinaryRole = Role::create(['name' => 'user-manager', 'guard_name' => 'web']);
        $ordinaryRole->givePermissionTo(Permission::create([
            'name' => 'Update:User',
            'guard_name' => 'web',
        ]));

        $ordinaryUser = User::factory()->create();
        $ordinaryUser->assignRole($ordinaryRole);
        $targetUser = User::factory()->create();
        $policy = new UserPolicy;

        $this->assertFalse($policy->update($ordinaryUser, $targetUser));
        $this->assertFalse($policy->create($ordinaryUser));
    }
}
