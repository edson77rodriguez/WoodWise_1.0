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
