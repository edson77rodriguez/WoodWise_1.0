<?php

use App\Http\Controllers\FieldObservationController;
use Illuminate\Support\Facades\Route;

// Los recursos públicos están en /sigmad-campo-assets/ para no crear
// un directorio físico /campo que bloquearía la ruta en Apache/Hostinger.
Route::middleware(['auth', 'role:Administrador,Tecnico'])->group(function () {
    Route::get('/campo', [FieldObservationController::class, 'index'])->name('campo.index');
    Route::post('/campo/sync', [FieldObservationController::class, 'sync'])->name('campo.sync');
});
