<?php

namespace Tests\Feature\Concerns;

use App\Models\Caso;
use App\Models\Cliente;
use App\Models\RoleDataScope;
use App\Models\User;
use App\Support\RecordAccessScope;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait CreatesDashboardFixtures
{
    /**
     * @param  array<string>  $permissions
     * @param  array<class-string, string>  $scopes
     */
    protected function createDashboardUser(array $permissions, array $scopes = []): User
    {
        $role = Role::create([
            'name' => 'dashboard-'.Str::uuid(),
            'guard_name' => 'web',
        ]);

        $role->givePermissionTo(collect($permissions)
            ->map(fn (string $permission): Permission => Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]))
            ->all());

        foreach ($scopes as $modelClass => $scope) {
            RoleDataScope::create([
                'role_id' => $role->getKey(),
                'model_type' => $modelClass,
                'scope' => $scope,
            ]);
        }

        $user = User::factory()->create(['activo' => true]);
        $user->assignRole($role);

        return $user;
    }

    protected function createDashboardCase(string $number): Caso
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

    /**
     * @return array<class-string, string>
     */
    protected function assignedCaseScopes(): array
    {
        return [Caso::class => RecordAccessScope::ASSIGNED_TO_ME];
    }
}
