<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CasoDocumentoController;

Route::get('/', function () {
    return view('inicio');
});

Route::get('/panel/casos/{caso}/documentos/{documento}/descargar', [CasoDocumentoController::class, 'download'])
    ->middleware('auth')
    ->name('casos.documentos.download');