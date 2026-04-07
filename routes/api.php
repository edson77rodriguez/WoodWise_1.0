<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\BotController;

Route::prefix('v1/bot')
    ->middleware(['botkey'])
    ->group(function () {
        Route::post('/verificar', [BotController::class, 'verificarUsuario']);
        Route::post('/mis-parcelas', [BotController::class, 'listarParcelas']);
        Route::post('/mis-trozas', [BotController::class, 'obtenerResumenTrozas']);
        Route::post('/mis-estimaciones-trozas', [BotController::class, 'obtenerResumenEstimacionesTrozas']);
        Route::post('/mis-arboles', [BotController::class, 'obtenerResumenArboles']);
        Route::post('/mis-estimaciones-arboles', [BotController::class, 'obtenerResumenEstimacionesArboles']);
        Route::post('/registro-masivo', [BotController::class, 'registroMasivo']);
        Route::get('/parcelas/{id_parcela}/reporte.pdf', [BotController::class, 'descargarReporteParcelaPdf']);
    });
