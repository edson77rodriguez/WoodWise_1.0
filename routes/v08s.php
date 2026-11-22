<?php
// Incluir SOLO dentro del grupo admin ya existente de routes/web.php:
// Route::middleware(['auth','role:Administrador'])->group(function () { ... require __DIR__.'/v08s.php'; });
use App\Http\Controllers\V08sAnalysisController;
use Illuminate\Support\Facades\Route;

Route::get('/analisis-uav/v08s',
    [V08sAnalysisController::class,'index'])->name('analysis.v08s.index');

Route::post('/analisis-uav/mosaicos/{mosaic:uuid}/v08s',
    [V08sAnalysisController::class,'start'])->name('analysis.v08s.start');
Route::get('/analisis-uav/jobs/{analysisUuid}/v08s/estado',
    [V08sAnalysisController::class,'status'])->name('analysis.v08s.status');
