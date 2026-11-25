<?php
// Incluido desde routes/web.php DENTRO de auth + role:Administrador.
use App\Http\Controllers\V09uAnalysisController;
use Illuminate\Support\Facades\Route;

Route::get('/analisis-uav/v09u', [V09uAnalysisController::class, 'index'])
    ->name('analysis.v09u.index');
Route::post('/analisis-uav/mosaicos/{mosaic:uuid}/v09u',
    [V09uAnalysisController::class, 'start'])->name('analysis.v09u.start');
Route::get('/analisis-uav/jobs/{analysisUuid}/v09u/estado',
    [V09uAnalysisController::class, 'status'])->name('analysis.v09u.status');
