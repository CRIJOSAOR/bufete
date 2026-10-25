<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CasoDocumentoController;
use App\Http\Controllers\AccountingAttachmentController;

Route::get('/', function () {
    return view('inicio');
});

Route::get('/panel/casos/{caso}/documentos/{documento}/descargar', [CasoDocumentoController::class, 'download'])
    ->middleware('auth')
    ->name('casos.documentos.download');

Route::get('/panel/contabilidad/{type}/{record}/comprobante', [AccountingAttachmentController::class, 'download'])
    ->middleware('auth')
    ->name('contabilidad.comprobantes.download');