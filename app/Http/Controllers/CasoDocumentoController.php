<?php

namespace App\Http\Controllers;

use App\Filament\Resources\Casos\CasoResource;
use App\Models\CasoDocumento;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class CasoDocumentoController extends Controller
{
    public function download(int $caso, CasoDocumento $documento)
    {
        $caseRecord = CasoResource::getEloquentQuery()->findOrFail($caso);

        Gate::authorize('view', $caseRecord);
        abort_unless($documento->caso_id === $caseRecord->getKey(), 404);
        abort_unless(Storage::disk('case_files')->exists($documento->ruta), 404);

        return Storage::disk('case_files')->download(
            $documento->ruta,
            $documento->nombre_original,
            ['Content-Type' => $documento->mime_type],
        );
    }
}
