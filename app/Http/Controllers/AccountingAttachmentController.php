<?php

namespace App\Http\Controllers;

use App\Models\FondoClienteMovimiento;
use App\Models\Gasto;
use App\Models\Pago;
use App\Models\User;
use App\Support\RecordAccessScope;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AccountingAttachmentController extends Controller
{
    public function download(string $type, int $record)
    {
        [$model, $pathColumn, $nameColumn, $mimeColumn] = match ($type) {
            'pago' => [Pago::class, 'comprobante_ruta', 'comprobante_original', 'comprobante_mime'],
            'gasto' => [Gasto::class, 'comprobante_ruta', 'comprobante_original', 'comprobante_mime'],
            'fondos-cliente' => [FondoClienteMovimiento::class, 'comprobante_ruta', 'comprobante_original', 'comprobante_mime'],
            default => abort(404),
        };

        $item = RecordAccessScope::apply(
            $model::query(),
            auth()->user() instanceof User ? auth()->user() : null,
        )->findOrFail($record);

        Gate::authorize('view', $item);
        abort_unless($item->{$pathColumn} && Storage::disk('accounting_files')->exists($item->{$pathColumn}), 404);

        return Storage::disk('accounting_files')->download(
            $item->{$pathColumn},
            $item->{$nameColumn} ?: basename($item->{$pathColumn}),
            ['Content-Type' => $item->{$mimeColumn} ?: 'application/octet-stream'],
        );
    }
}
