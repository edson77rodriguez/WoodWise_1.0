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

// =====================================================================
// RUTAS PARA TÉCNICOS FORESTALES
// =====================================================================
Route::prefix('T')->middleware(['auth'])->group(function () {
    // Dashboard principal del técnico
    Route::get('/index', [TecnicoDashboardController::class, 'index'])
        ->name('tecnico.dashboard');

    // Detalle de parcela
    Route::get('/parcelas/{id_parcela}/detalle', [TecnicoDashboardController::class, 'parcelaDetalle'])
        ->name('tecnico.parcela.detalle');

    // Crear nueva parcela
    Route::post('/parcelas', [TecnicoDashboardController::class, 'parcelaStore'])
        ->name('tecnico.parcela.store');

    // Crear troza
    Route::post('/trozas', [TecnicoDashboardController::class, 'trozaStore'])
        ->name('tecnico.troza.store');

    // Crear árbol
    Route::post('/arboles', [TecnicoDashboardController::class, 'arbolStore'])
        ->name('tecnico.arbol.store');

    // Crear estimación de troza
    Route::post('/estimaciones', [TecnicoDashboardController::class, 'estimacionStore'])
        ->name('tecnico.estimacion.store');

    // Crear estimación de árbol
    Route::post('/estimaciones-arbol', [TecnicoDashboardController::class, 'estimacionArbolStore'])
        ->name('tecnico.estimacion-arbol.store');

    // Exportar PDF de parcela
    Route::get('/parcelas/{id_parcela}/export-pdf', [TecnicoDashboardController::class, 'exportParcelaToPdf'])
        ->name('tecnico.parcela.pdf');
});

// =====================================================================
// RUTAS PARA PRODUCTORES
// =====================================================================
Route::prefix('P')->middleware(['auth'])->group(function () {
    // Dashboard principal
    Route::get('/index', [ProductorDashboardController::class, 'index'])
        ->name('productor.dashboard');

    // Crear nueva parcela
    Route::post('/parcelas', [ProductorDashboardController::class, 'parcelaStore'])
        ->name('productor.parcela.store');

    // Registrar turno de corta
    Route::post('/turnos', [ProductorDashboardController::class, 'turnoStore'])
        ->name('productor.turno.store');

    // Detalle de parcela
    Route::get('/parcelas/{id_parcela}/detalle', [ProductorDashboardController::class, 'parcelaDetalle'])
        ->name('productor.parcela.detalle');

    // Exportar PDF de parcela individual
    Route::get('/parcelas/{id_parcela}/pdf', [ProductorDashboardController::class, 'exportParcelaPdf'])
        ->name('productor.parcela.pdf');

    // Exportar reporte general
    Route::get('/reporte-general', [ProductorDashboardController::class, 'exportarGeneral'])
        ->name('productor.reporte.general');
});
