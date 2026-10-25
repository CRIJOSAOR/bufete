<?php

namespace App\Filament\Resources\Casos\Pages;

use App\Filament\Resources\Casos\CasoResource;
use App\Models\Caso;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class EditCaso extends EditRecord
{
    protected static string $resource = CasoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportar_pdf')
                ->label('Exportar expediente PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->authorize(fn (Caso $record): bool => Gate::allows('view', $record))
                ->form([
                    Toggle::make('incluir_confidencial')
                        ->label('Incluir notas confidenciales y estrategia')
                        ->helperText('El PDF puede contener información jurídica sensible.')
                        ->default(false)
                        ->visible(fn (): bool => auth()->user()->puedeVerConfidencial()),
                ])
                ->action(function (Caso $record, array $data) {
                    Gate::authorize('view', $record);

                    $incluirConfidencial = (bool) ($data['incluir_confidencial'] ?? false);

                    abort_if(
                        $incluirConfidencial && ! auth()->user()->puedeVerConfidencial(),
                        403,
                    );

                    $record->load([
                        'cliente',
                        'abogados',
                        'tareas.asignado',
                        'audiencias.responsable',
                        'eventos.creador',
                        'documentos.subidor',
                    ]);

                    $pdf = Pdf::loadView('pdf.casos.expediente', [
                        'caso' => $record,
                        'incluirConfidencial' => $incluirConfidencial,
                    ])->setPaper('a4');

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        Str::slug($record->numero.'-'.$record->titulo).'.pdf',
                        ['Content-Type' => 'application/pdf'],
                    );
                }),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
