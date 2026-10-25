<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Support\RecordAccessScope;
use BezhanSalleh\FilamentShield\Resources\Roles\Pages\EditRole as ShieldEditRole;

class EditRole extends ShieldEditRole
{
    protected static string $resource = RoleResource::class;

    protected array $dataScopes = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->dataScopes = $data['data_scopes'] ?? [];
        unset($data['data_scopes']);

        return parent::mutateFormDataBeforeSave($data);
    }

    protected function afterSave(): void
    {
        parent::afterSave();
        RecordAccessScope::syncForRole($this->record, $this->dataScopes);
    }
}
