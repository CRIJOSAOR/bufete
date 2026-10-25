<?php

namespace App\Support;

use App\Models\RoleDataScope;
use App\Models\User;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class RecordAccessScope
{
    public const ALL = 'all';

    public const CREATED_BY_ME = 'created_by_me';

    public const ASSIGNED_TO_ME = 'assigned_to_me';

    public static function apply(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        $model = $query->getModel();
        $roleIds = $user->roles->modelKeys();

        if ($roleIds === []) {
            return $query->whereRaw('1 = 0');
        }

        $roleScopes = RoleDataScope::query()
            ->whereIn('role_id', $roleIds)
            ->where('model_type', $model::class)
            ->get(['role_id', 'scope'])
            ->groupBy('role_id');

        $scopes = [];

        foreach ($roleIds as $roleId) {
            $scopesForRole = $roleScopes->get($roleId, collect())->pluck('scope')->all();

            if (in_array(self::ALL, $scopesForRole, true)) {
                return $query;
            }

            $scopes = array_merge($scopes, $scopesForRole);
        }

        if ($scopes === []) {
            return $query->whereRaw('1 = 0');
        }

        $scopes = array_values(array_filter(
            $scopes,
            fn (string $scope): bool => match ($scope) {
                self::CREATED_BY_ME => self::creatorColumn($model) !== null,
                self::ASSIGNED_TO_ME => method_exists($model, 'scopeAssignedTo'),
                default => false,
            },
        ));

        if ($scopes === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $query) use ($model, $scopes, $user): void {
            foreach ($scopes as $scope) {
                if ($scope === self::CREATED_BY_ME) {
                    $column = self::creatorColumn($model);

                    if ($column) {
                        $query->orWhere($model->qualifyColumn($column), $user->getKey());
                    }
                }

                if ($scope === self::ASSIGNED_TO_ME && method_exists($model, 'scopeAssignedTo')) {
                    $query->orWhere(function (Builder $query) use ($user): void {
                        $query->assignedTo($user);
                    });
                }
            }
        });
    }

    public static function optionsFor(string $modelClass): array
    {
        $model = new $modelClass;

        if (! $model instanceof Model) {
            return [self::ALL => 'Todos los registros'];
        }

        $options = [self::ALL => 'Todos los registros'];

        if (self::creatorColumn($model)) {
            $options[self::CREATED_BY_ME] = 'Solo los que creó el usuario';
        }

        if (method_exists($model, 'scopeAssignedTo')) {
            $options[self::ASSIGNED_TO_ME] = 'Solo los asignados o relacionados con el usuario';
        }

        return $options;
    }

    public static function syncForRole(Role $role, array $scopes): void
    {
        DB::transaction(function () use ($role, $scopes): void {
            foreach (FilamentShield::getResources() ?? [] as $resource) {
                $modelClass = $resource['modelFqcn'];

                if (is_a($modelClass, Role::class, true)) {
                    continue;
                }

                $selectedScopes = $scopes[$modelClass] ?? [self::ALL];
                $availableScopes = self::optionsFor($modelClass);

                if (! is_array($selectedScopes)) {
                    $selectedScopes = [$selectedScopes];
                }

                $selectedScopes = array_values(array_unique($selectedScopes, SORT_REGULAR));
                $invalidScopes = array_filter(
                    $selectedScopes,
                    fn (mixed $scope): bool => ! is_string($scope) || ! array_key_exists($scope, $availableScopes),
                );

                if ($invalidScopes !== [] || $selectedScopes === []) {
                    throw ValidationException::withMessages([
                        'data_scopes.'.$modelClass => 'Selecciona un alcance válido para este módulo.',
                    ]);
                }

                if (in_array(self::ALL, $selectedScopes, true)) {
                    $selectedScopes = [self::ALL];
                }

                RoleDataScope::query()
                    ->where('role_id', $role->getKey())
                    ->where('model_type', $modelClass)
                    ->delete();

                foreach ($selectedScopes as $scope) {
                    RoleDataScope::query()->create([
                        'role_id' => $role->getKey(),
                        'model_type' => $modelClass,
                        'scope' => $scope,
                    ]);
                }
            }
        });
    }

    private static function creatorColumn(Model $model): ?string
    {
        foreach (['creado_por', 'created_by'] as $column) {
            if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), $column)) {
                return $column;
            }
        }

        return null;
    }
}
