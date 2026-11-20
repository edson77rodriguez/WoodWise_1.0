<?php
// REQUIERE incluir este archivo dentro de Route::middleware(['auth','role:Administrador'])->group(...)
use App\Http\Controllers\SigmadSam2Controller;
use Illuminate\Support\Facades\Route;
Route::get('/analisis-uav/jobs/{analysisUuid}/sam2', [SigmadSam2Controller::class, 'index'])
    ->name('analysis.sam2.index');
Route::get('/analisis-uav/jobs/{analysisUuid}/sam2/geojson', [SigmadSam2Controller::class, 'geojson'])
    ->name('analysis.sam2.geojson');
Route::post('/analisis-uav/jobs/{analysisUuid}/sam2/registrar', [SigmadSam2Controller::class, 'register'])
    ->name('analysis.sam2.register');
