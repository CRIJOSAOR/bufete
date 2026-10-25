<?php

namespace App\Filament\Resources\Contabilidad\Honorarios\Pages;

use App\Filament\Resources\Contabilidad\Honorarios\HonorarioResource;
use App\Models\Honorario;
use App\Support\ContabilidadPdf;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHonorario extends EditRecord
{
    protected static string $resource = HonorarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('estado_cuenta')
                ->label('Descargar estado de cuenta PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->authorize(fn (Honorario $record): bool => auth()->user()->can('view', $record))
                ->action(fn (Honorario $record, ContabilidadPdf $pdf) => $pdf->honorario($record)),
            DeleteAction::make(),
        ];
    }
}
