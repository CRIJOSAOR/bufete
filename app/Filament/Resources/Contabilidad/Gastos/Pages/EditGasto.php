<?php

namespace App\Filament\Resources\Contabilidad\Gastos\Pages;

use App\Filament\Resources\Contabilidad\Gastos\GastoResource;
use App\Models\Gasto;
use App\Support\ContabilidadPdf;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditGasto extends EditRecord
{
    protected static string $resource = GastoResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (filled($data['comprobante_ruta'] ?? null) && $data['comprobante_ruta'] !== $this->record->comprobante_ruta) {
            $data['comprobante_mime'] = Storage::disk('accounting_files')->mimeType($data['comprobante_ruta']);
            $data['comprobante_tamano'] = Storage::disk('accounting_files')->size($data['comprobante_ruta']);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reporte_pdf')
                ->label('Descargar reporte PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->authorize(fn (Gasto $record): bool => auth()->user()->can('view', $record))
                ->action(fn (Gasto $record, ContabilidadPdf $pdf) => $pdf->gasto($record)),
            DeleteAction::make(),
        ];
    }
}
