<?php

namespace App\Filament\Resources\Contabilidad\Honorarios\Pages;

use App\Filament\Resources\Contabilidad\Honorarios\HonorarioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHonorarios extends ListRecords
{
    protected static string $resource = HonorarioResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
