<?php

use App\Http\Controllers\FieldRecordsLayersController;
use Illuminate\Support\Facades\Route;

// SIGMAD Campo V0.3.2: consulta autorizada; no contiene operaciones de escritura.
Route::middleware(['auth', 'role:Administrador'])->group(function () {
    Route::get('/campo/registros/capas/misiones', [FieldRecordsLayersController::class, 'missions'])
        ->name('campo.records.layers.missions');
});
