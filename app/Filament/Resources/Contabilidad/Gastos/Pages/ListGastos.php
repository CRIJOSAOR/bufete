<?php

namespace App\Filament\Resources\Contabilidad\Gastos\Pages;

use App\Filament\Resources\Contabilidad\Gastos\GastoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGastos extends ListRecords
{
    protected static string $resource = GastoResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
