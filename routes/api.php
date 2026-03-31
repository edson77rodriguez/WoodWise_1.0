<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\BotController;

Route::prefix('v1/bot')
    ->middleware(['botkey'])
    ->group(function () {
        Route::post('/verificar', [BotController::class, 'verificarUsuario']);
        Route::post('/mis-trozas', [BotController::class, 'obtenerResumenTrozas']);
        Route::post('/mis-estimaciones-trozas', [BotController::class, 'obtenerResumenEstimacionesTrozas']);
        Route::get('/parcelas/{id_parcela}/reporte.pdf', [BotController::class, 'descargarReporteParcelaPdf']);
    });
