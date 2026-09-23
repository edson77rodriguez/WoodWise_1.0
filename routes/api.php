<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\BotController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\API\MobileAuthController;
use App\Http\Controllers\API\MobileParcelaController;
use App\Http\Controllers\API\MobileArbolController;
use App\Http\Controllers\API\MobileTrozaController;
use App\Http\Controllers\API\MobileEspecieController;
use App\Http\Controllers\API\MobileTipoEstimacionController;
use App\Http\Controllers\API\MobileCatalogoController;
use App\Http\Controllers\API\MobileFormulaController;
use App\Http\Controllers\API\MobileEstimacionTrozaController;

Route::prefix('v1/bot')
    ->middleware(['botkey', 'verify.hmac'])
    ->group(function () {
        Route::post('/verificar', [BotController::class, 'verificarUsuario']);
        Route::post('/menu-principal', [BotController::class, 'obtenerMenuPrincipal']);
        Route::post('/mis-parcelas', [BotController::class, 'listarParcelas']);
        Route::post('/mis-trozas', [BotController::class, 'obtenerResumenTrozas']);
        Route::get('/cotizacion/parcela/{id_parcela}', [BotController::class, 'generarCotizacion']);
        Route::get('/cotizacion/parcela/{id_parcela}/pdf', [BotController::class, 'descargarCotizacionMercadoPdf']);
        Route::post('/mis-estimaciones-trozas', [BotController::class, 'obtenerResumenEstimacionesTrozas']);
        Route::post('/mis-arboles', [BotController::class, 'obtenerResumenArboles']);
        Route::post('/mis-estimaciones-arboles', [BotController::class, 'obtenerResumenEstimacionesArboles']);
        Route::post('/impacto-ambiental', [BotController::class, 'obtenerImpactoAmbiental']);
        Route::post('/impacto-ambiental/pdf', [BotController::class, 'descargarImpactoAmbientalPdf']);
        Route::post('/kit-campo', [BotController::class, 'obtenerKitCampo']);
        Route::post('/asistente-guiado', [BotController::class, 'asistenteGuiado']);
        Route::post('/excel-webhook', [BotController::class, 'recibirExcelWebhook']);
        Route::post('/registro-masivo', [BotController::class, 'registroMasivo']);
        Route::post('/inf', [BotController::class, 'descargarInformeBotPdf']);
        Route::get('/parcelas/{id_parcela}/reporte.pdf', [BotController::class, 'descargarReporteParcelaPdf']);
    });

Route::prefix('v1/cotizacion')
    ->middleware(['botkey'])
    ->group(function () {
        Route::post('/sincronizar-precios-ia', [CotizacionController::class, 'sincronizarPreciosIA']);
    });
    //////////////////////////////////
Route::prefix('v1/mobile')->group(function () {

    Route::post('/login', [
        MobileAuthController::class,
        'login'
    ]);

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/me', [
            MobileAuthController::class,
            'me'
        ]);

        Route::post('/logout', [
            MobileAuthController::class,
            'logout'
        ]);
    });

    Route::middleware([
        'auth:sanctum',
        'api.role:Tecnico,Productor'
    ])->get('/mis-parcelas', [
        MobileParcelaController::class,
        'index'
    ]);

    Route::middleware([
    'auth:sanctum',
    'api.role:Tecnico,Productor'
])->get('/parcelas/{id}', [
    MobileParcelaController::class,
    'show'
]);

Route::middleware([
    'auth:sanctum',
    'api.role:Tecnico'
])->post('/parcelas/{id}/arboles', [
    MobileArbolController::class,
    'store'
]);

Route::middleware([
    'auth:sanctum',
    'api.role:Tecnico'
])->post('/parcelas/{id}/trozas', [
    MobileTrozaController::class,
    'store'
]);

Route::middleware([
    'auth:sanctum',
    'api.role:Tecnico,Productor'
])->get('/especies', [
    MobileEspecieController::class,
    'index'
]);

Route::middleware([
    'auth:sanctum',
    'api.role:Tecnico,Productor'
])->get('/tipo-estimaciones', [
    MobileTipoEstimacionController::class,
    'index'
]);

Route::middleware([
    'auth:sanctum',
    'api.role:Tecnico,Productor'
])->get('/catalogos', [
    MobileCatalogoController::class,
    'index'
]);

Route::middleware([
    'auth:sanctum',
    'api.role:Tecnico,Productor'
])->get('/formulas', [
    MobileFormulaController::class,
    'index'
]);


Route::middleware([
    'auth:sanctum',
    'api.role:Tecnico'
])->post(
    '/parcelas/{idParcela}/trozas/{idTroza}/estimaciones',
    [
        MobileEstimacionTrozaController::class,
        'store'
    ]
);

});