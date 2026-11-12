<?php

use App\Http\Controllers\FieldObservationController;
use Illuminate\Support\Facades\Route;

// Incluir este archivo desde routes/web.php, que ya recibe middleware web.
Route::middleware(['auth', 'role:Administrador,Tecnico'])->group(function () {
    Route::get('/campo', fn () => redirect()->route('campo.index'));
    Route::get('/campo/', [FieldObservationController::class, 'index'])->name('campo.index');
    Route::post('/campo/sync', [FieldObservationController::class, 'sync'])->name('campo.sync');
});
