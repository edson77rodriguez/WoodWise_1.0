<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\EspecieController;
use App\Http\Controllers\ParcelaController;
use App\Http\Controllers\FormulaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TrozaController;
use App\Http\Controllers\EstimacionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\TipoEstimacionController;
use App\Http\Controllers\ProductorController;
use App\Http\Controllers\TecnicoController;
use App\Http\Controllers\TurnoCortaController;
use App\Http\Controllers\AsignaParcelaController;
use App\Http\Controllers\TecnicoDashboardController;
use App\Http\Controllers\ProductorDashboardController;
use App\Http\Controllers\ArbolController;
use App\Http\Controllers\Estimacion1Controller;





// Ruta principal
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Autenticación
Auth::routes();

// Rutas públicas
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/acerca-de', fn() => view('acerca_nosotros'))->name('acerca');
Route::get('/contactos', fn() => view('contactos'))->name('contacto');

// Ruta de perfil

Route::middleware(['auth'])->group(function () {
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil.index');
    Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::put('/perfil/password', [PerfilController::class, 'updatePassword'])->name('perfil.updatePassword');
});

Route::middleware(['auth'])->group(function() {
    // Dashboard general
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dashboard específico para técnicos
    Route::get('/dashboard1', [DashboardController::class, 'index'])->name('dashboard1');

    // Rutas para parcelas (protegidas por middleware de rol)
    Route::middleware(['role:Tecnico'])->group(function() {
        Route::resource('parcelas', ParcelaController::class);
    });
});

    Route::resource('formulas', FormulaController::class);
    Route::resource('especies', EspecieController::class);
    Route::resource('usuarios', PersonaController::class);
    Route::resource('tipo_estimaciones', TipoEstimacionController::class);
    Route::resource('productores', ProductorController::class);
    Route::resource('tecnicos', TecnicoController::class);
    Route::resource('parcelas', ParcelaController::class);
    Route::resource('trozas', TrozaController::class);
    Route::resource('arboles', ArbolController::class);
    Route::resource('estimaciones1', Estimacion1Controller::class);

    Route::resource('turno_cortas', TurnoCortaController::class);
    Route::resource('asigna_parcelas', AsignaParcelaController::class);
    Route::resource('estimaciones', EstimacionController::class);
    Route::get('/estimaciones/formulas-por-tipo/{tipoId}', [EstimacionController::class, 'getFormulasByTipo']);
    Route::get('/catalogo-especies', [EspecieController::class, 'catalogo'])->name('especies.catalogo');
    Route::get('/tecnicos/dashboard', [TecnicoController::class, 'dashboard'])
    ->name('tecnicos.dashboard');

    // Rutas para técnicos
Route::prefix('T')->group(function () {
    Route::get('/dashboard', [TecnicoDashboardController::class, 'index'])->name('tecnico.dashboard');

     // PARCELAS
    Route::post('/parcelas', [TecnicoDashboardController::class, 'parcelaStore'])->name('parcelas.store');

    // TROZAS
    Route::post('/trozas', [TecnicoDashboardController::class, 'trozaStore'])->name('trozas.store');

  
      // ÁRBOLES
    Route::post('/arboles', [TecnicoDashboardController::class, 'arbolStore'])->name('arboles.store');
    
    // ESTIMACIONES
    Route::post('/estimaciones', [TecnicoDashboardController::class, 'estimacionStore'])->name('estimaciones.store');
    Route::post('/estimaciones-arbol', [TecnicoDashboardController::class, 'estimacionArbolStore'])->name('estimaciones.arbol.store');
     // EXPORTACIÓN
    Route::get('/parcelas/{id}/export-pdf', [TecnicoDashboardController::class, 'exportParcelaToPdf'])->name('parcelas.export.pdf');
    });
   Route::put('/gestion/trozas/{id_troza}', [ParcelaController::class, 'updateTroza'])->name('gestion.trozas.update');

Route::put('/gestion/estimaciones/{id_estimacion}', [ParcelaController::class, 'updateEstimacion'])->name('gestion.estimaciones.update');
Route::post('/tecnico/arboles', [TecnicoDashboardController::class, 'storeArbol'])->name('tecnico.arboles.store');
Route::post('/tecnico/estimaciones-arbol', [TecnicoDashboardController::class, 'storeEstimacionArbol'])->name('tecnico.estimaciones1.store');
Route::get('/parcelas/{id}/detalle', [ParcelaController::class, 'show'])->name('parcelas.show');    Route::get('/estimaciones/formulas/{tipoId}', [EstimacionController::class, 'getFormulasByTipo'])
     ->name('estimaciones.formulas');
Route::get('/parcelas/{id_parcela}/export-pdf', [TecnicoDashboardController::class, 'exportParcelaToPdf'])
    ->name('parcelas.export.pdf')
    ->middleware('auth');
Route::put('/trozas/{id_troza}', [TrozaController::class, 'update1'])->name('trozas.update1');





Route::prefix('P')->middleware(['auth'])->group(function () {
    Route::get('/index', [ProductorDashboardController::class, 'index'])
        ->name('productor.dashboard');

    Route::get('/exportar-general', [ProductorDashboardController::class, 'exportarGeneral'])
        ->name('exportar.general');

    Route::get('/parcelas/export', [ProductorDashboardController::class, 'exportarParcelas'])
        ->name('parcelas.export');

    Route::get('/trozas/export', [ProductorDashboardController::class, 'exportarTrozas'])
        ->name('trozas.export');

    Route::get('/estimaciones/export', [ProductorDashboardController::class, 'exportarEstimaciones'])
        ->name('estimaciones.export');

    Route::get('/parcela/{id}/pdf', [ProductorDashboardController::class, 'generarPdfParcela'])
        ->name('parcela.pdf');

    Route::get('/troza/{id}/pdf', [ProductorDashboardController::class, 'generarPdfTroza'])
        ->name('troza.pdf');
    Route::get('/estimacion/{id}/pdf', [ProductorDashboardController::class, 'generarPdfEstimacion'])
        ->name('estimacion.pdf');
});
