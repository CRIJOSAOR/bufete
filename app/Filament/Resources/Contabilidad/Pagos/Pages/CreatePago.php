<?php

namespace App\Filament\Resources\Contabilidad\Pagos\Pages;

use App\Filament\Resources\Contabilidad\Pagos\PagoResource;
use App\Models\Pago;
use App\Support\AccountingLedger;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CreatePago extends CreateRecord
{
    protected static string $resource = PagoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (filled($data['comprobante_ruta'] ?? null)) {
            $data['comprobante_mime'] = Storage::disk('accounting_files')->mimeType($data['comprobante_ruta']);
            $data['comprobante_tamano'] = Storage::disk('accounting_files')->size($data['comprobante_ruta']);
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(AccountingLedger::class)->registerPayment($data);
        } catch (Throwable $exception) {
            if (filled($data['comprobante_ruta'] ?? null)) {
                Storage::disk('accounting_files')->delete($data['comprobante_ruta']);
            }

            throw $exception;
        }
    }

    protected function getRedirectUrl(): string
    {
        return PagoResource::getUrl('index');
    }
}
