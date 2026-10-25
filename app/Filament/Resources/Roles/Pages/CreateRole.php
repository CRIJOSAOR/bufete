<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Support\RecordAccessScope;
use BezhanSalleh\FilamentShield\Resources\Roles\Pages\CreateRole as ShieldCreateRole;
use Spatie\Permission\Models\Role;

class CreateRole extends ShieldCreateRole
{
    protected static string $resource = RoleResource::class;

    protected array $dataScopes = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->dataScopes = $data['data_scopes'] ?? [];
        unset($data['data_scopes']);

        return parent::mutateFormDataBeforeCreate($data);
    }

    protected function afterCreate(): void
    {
        parent::afterCreate();
        RecordAccessScope::syncForRole($this->record, $this->dataScopes);
    }
}
