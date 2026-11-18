<?php

use App\Http\Controllers\FieldCrownLinkController;
use Illuminate\Support\Facades\Route;

// Revisión científica exclusivamente posterior al levantamiento, solo Administrador.
Route::middleware(['auth', 'role:Administrador'])->group(function () {
    Route::get('/campo/vinculaciones', [FieldCrownLinkController::class, 'index'])
        ->name('campo.links.index');
    Route::get('/campo/vinculaciones/puntos', [FieldCrownLinkController::class, 'points'])
        ->name('campo.links.points');
    Route::get('/campo/vinculaciones/exportar', [FieldCrownLinkController::class, 'export'])
        ->name('campo.links.export');
    Route::post('/campo/vinculaciones', [FieldCrownLinkController::class, 'store'])
        ->name('campo.links.store');
});
