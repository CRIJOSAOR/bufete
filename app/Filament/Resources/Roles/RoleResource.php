<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Casos\CasoResource;
use App\Filament\Resources\Contabilidad\FondosCliente\FondoClienteMovimientoResource;
use App\Filament\Resources\Contabilidad\Gastos\GastoResource;
use App\Filament\Resources\Contabilidad\Honorarios\HonorarioResource;
use App\Filament\Resources\Contabilidad\Pagos\PagoResource;
use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Models\RoleDataScope;
use App\Support\RecordAccessScope;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\FilamentShield\Resources\Roles\Pages\ListRoles;
use BezhanSalleh\FilamentShield\Resources\Roles\Pages\ViewRole;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource as ShieldRoleResource;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Spatie\Permission\Models\Role;

class RoleResource extends ShieldRoleResource
{
    public static function getShieldFormComponents(): Component
    {
        return Tabs::make('Permissions')
            ->contained()
            ->tabs([
                static::getTabFormComponentForResources(),
                static::getTabFormComponentForPage(),
                static::getTabFormComponentForWidget(),
                static::getCustomPermissionsTab(),
                Tab::make('data_scopes')
                    ->label('Alcance de datos')
                    ->schema([
                        Grid::make()
                            ->columns(2)
                            ->schema(static::getDataScopeSections()),
                    ]),
            ])
            ->columnSpan('full');
    }

    public static function getTabFormComponentForCustomPermissions(): Component
    {
        return static::getCustomPermissionsTab();
    }

    public static function getResourceEntitiesSchema(): ?array
    {
        return collect(FilamentShield::getResources())
            ->map(function (array $entity): Section {
                $sectionLabel = strval(
                    static::shield()->hasLocalizedPermissionLabels()
                        ? FilamentShield::getLocalizedResourceLabel($entity['resourceFqcn'])
                        : $entity['model']
                );

                return Section::make($sectionLabel)
                    ->compact()
                    ->schema([
                        static::getCheckBoxListComponentForResource($entity),
                    ])
                    ->columnSpan(static::shield()->getSectionColumnSpan())
                    ->collapsible()
                    ->collapsed();
            })
            ->toArray();
    }

    public static function getResourcePermissionOptions(array $entity): array
    {
        $options = parent::getResourcePermissionOptions($entity);

        if (($entity['resourceFqcn'] ?? null) === CasoResource::class) {
            $options[CasoResource::GENERATE_PDF_PERMISSION] = 'Generar PDF';
        }

        $availablePermissions = match ($entity['resourceFqcn'] ?? null) {
            HonorarioResource::class => ['ViewAny:Honorario', 'View:Honorario', 'Create:Honorario', 'Update:Honorario', 'Delete:Honorario', 'DeleteAny:Honorario'],
            PagoResource::class => ['ViewAny:Pago', 'View:Pago', 'Create:Pago'],
            GastoResource::class => ['ViewAny:Gasto', 'View:Gasto', 'Create:Gasto', 'Update:Gasto', 'Delete:Gasto', 'DeleteAny:Gasto'],
            FondoClienteMovimientoResource::class => ['ViewAny:FondoClienteMovimiento', 'View:FondoClienteMovimiento', 'Create:FondoClienteMovimiento'],
            default => array_keys($options),
        };

        return array_intersect_key($options, array_fill_keys($availablePermissions, true));
    }

    private static function getCustomPermissionsTab(): Component
    {
        $options = FilamentShield::getCustomPermissions(static::shield()->hasLocalizedPermissionLabels()) ?? [];
        $count = count($options);

        return Tab::make('custom_permissions')
            ->label(__('filament-shield::filament-shield.custom'))
            ->visible(fn (): bool => Utils::isCustomPermissionTabEnabled() && $count > 0)
            ->badge($count)
            ->schema([
                static::getCheckboxListFormComponent(
                    name: 'custom_permissions_tab',
                    options: $options,
                )
                    ->afterStateHydrated(function (CheckboxList $component, string $operation, ?Role $record, Set $set) use ($options): void {
                        if ($operation === 'edit' && $record) {
                            $component->state(static::getSelectedCustomPermissions($record, array_keys($options)));
                        }

                        static::toggleSelectAllViaEntities($component->getLivewire(), $set);
                    }),
            ]);
    }

    public static function getSelectedCustomPermissions(Role $role, array $permissionNames): array
    {
        return $role->permissions()
            ->whereIn('name', $permissionNames)
            ->pluck('name')
            ->all();
    }

    public static function getSelectedDataScopes(Role $role, string $modelClass): array
    {
        return RoleDataScope::query()
            ->where('role_id', $role->getKey())
            ->where('model_type', $modelClass)
            ->orderBy('scope')
            ->pluck('scope')
            ->all() ?: [RecordAccessScope::ALL];
    }

    public static function getDataScopeSections(): array
    {
        return collect(FilamentShield::getResources() ?? [])
            ->reject(fn (array $resource): bool => is_a($resource['modelFqcn'], Role::class, true))
            ->map(function (array $resource): Section {
                $modelClass = $resource['modelFqcn'];
                $resourceClass = $resource['resourceFqcn'];

                return Section::make($resourceClass::getPluralModelLabel())
                    ->schema([
                        CheckboxList::make('data_scopes.'.$modelClass)
                            ->label('Qué registros puede consultar este rol')
                            ->options(RecordAccessScope::optionsFor($modelClass))
                            ->default([RecordAccessScope::ALL])
                            ->afterStateHydrated(function (CheckboxList $component, string $operation, ?Role $record) use ($modelClass): void {
                                if ($operation === 'edit' && $record) {
                                    $component->state(static::getSelectedDataScopes($record, $modelClass));
                                }
                            })
                            ->required()
                            ->columns(1)
                            ->bulkToggleable(),
                    ])
                    ->compact()
                    ->columnSpan(1);
            })
            ->values()
            ->all();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'view' => ViewRole::route('/{record}'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
