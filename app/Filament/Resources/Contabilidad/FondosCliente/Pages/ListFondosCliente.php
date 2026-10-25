<?php

namespace App\Filament\Resources\Contabilidad\FondosCliente\Pages;

use App\Filament\Resources\Contabilidad\FondosCliente\FondoClienteMovimientoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFondosCliente extends ListRecords
{
    protected static string $resource = FondoClienteMovimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
