<?php

use App\Http\Controllers\FieldMissionController;
use App\Http\Controllers\FieldObservationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:Administrador,Tecnico'])->group(function () {
    Route::get('/campo', [FieldObservationController::class, 'index'])->name('campo.index');
    Route::post('/campo/sync', [FieldObservationController::class, 'sync'])->name('campo.sync');

    // El controlador verifica que el técnico tenga asignada la parcela de la misión.
    Route::get('/campo/misiones/{mission:uuid}/datos', [FieldMissionController::class, 'data'])->name('campo.mission.data');
    Route::get('/campo/misiones/{mission:uuid}/exportar-json', [FieldMissionController::class, 'exportDefinition'])->name('campo.mission.export');
    Route::get('/campo/misiones/{mission:uuid}/preview', [FieldMissionController::class, 'preview'])->name('campo.mission.preview');
    Route::get('/campo/misiones/{mission:uuid}/teselas/manifiesto', [FieldMissionController::class, 'tilesManifest'])->name('campo.mission.tiles.manifest');
    Route::get('/campo/misiones/{mission:uuid}/teselas/{z}/{x}/{y}', [FieldMissionController::class, 'tile'])->whereNumber(['z', 'x', 'y'])->name('campo.mission.tiles.tile');
});

Route::middleware(['auth', 'role:Administrador'])->group(function () {
    Route::get('/campo/misiones', [FieldMissionController::class, 'create'])->name('campo.missions.create');
    Route::post('/campo/misiones', [FieldMissionController::class, 'store'])->name('campo.missions.store');
});

// SIGMAD Campo V0.3 — administración de observaciones (mantener las rutas V0.2.1).
\Illuminate\Support\Facades\Route::middleware(['auth', 'role:Administrador'])->group(function () {
    \Illuminate\Support\Facades\Route::get('/campo/registros', [\App\Http\Controllers\FieldRecordsController::class, 'index'])->name('campo.records.index');
    \Illuminate\Support\Facades\Route::get('/campo/registros/exportar', [\App\Http\Controllers\FieldRecordsController::class, 'export'])->name('campo.records.export');
    \Illuminate\Support\Facades\Route::get('/campo/registros/mapa', [\App\Http\Controllers\FieldRecordsController::class, 'map'])->name('campo.records.map');
    \Illuminate\Support\Facades\Route::get('/campo/registros/{record}', [\App\Http\Controllers\FieldRecordsController::class, 'show'])->whereNumber('record')->name('campo.records.show');
    \Illuminate\Support\Facades\Route::post('/campo/registros/{record}/revision', [\App\Http\Controllers\FieldRecordsController::class, 'review'])->whereNumber('record')->name('campo.records.review');
});

// SIGMAD Campo V0.3.2 — mapa administrativo (solo lectura).
require __DIR__ . '/campo_registros_capas.php';
