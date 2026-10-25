<?php

namespace App\Filament\Resources\Contabilidad\Gastos\Pages;

use App\Filament\Resources\Contabilidad\Gastos\GastoResource;
use App\Models\Gasto;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateGasto extends CreateRecord
{
    protected static string $resource = GastoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (filled($data['comprobante_ruta'] ?? null)) {
            $data['comprobante_mime'] = Storage::disk('accounting_files')->mimeType($data['comprobante_ruta']);
            $data['comprobante_tamano'] = Storage::disk('accounting_files')->size($data['comprobante_ruta']);
        }

        return $data;
    }
}
