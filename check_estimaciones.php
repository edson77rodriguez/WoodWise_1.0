<?php
// Cargar Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->handle(
    $request = \Illuminate\Http\Request::capture()
);

use App\Models\Estimacion;
use App\Models\Estimacion1;
use App\Models\Troza;
use App\Models\Arbol;

echo "===== INFORMACIÓN DE DATOS =====" . PHP_EOL;
echo "Total Trozas: " . Troza::count() . PHP_EOL;
echo "Total Árboles: " . Arbol::count() . PHP_EOL;
echo "Total Estimaciones (Trozas): " . Estimacion::count() . PHP_EOL;
echo "Total Estimaciones1 (Árboles): " . Estimacion1::count() . PHP_EOL;

echo PHP_EOL . "===== MUESTRA DE ESTIMACIONES DE TROZAS =====" . PHP_EOL;
Estimacion::with('troza.especie')->limit(3)->each(function($est) {
    echo "ID: {$est->id_estimacion}, Troza: {$est->id_troza}, Cálculo: {$est->calculo}, Biomasa: {$est->biomasa}, Carbono: {$est->carbono}" . PHP_EOL;
});

echo PHP_EOL . "===== MUESTRA DE ESTIMACIONES DE ÁRBOLES =====" . PHP_EOL;
Estimacion1::with('arbol.especie')->limit(3)->each(function($est) {
    echo "ID: {$est->id_estimacion1}, Árbol: {$est->id_arbol}, Cálculo: {$est->calculo}, Biomasa: {$est->biomasa}, Carbono: {$est->carbono}" . PHP_EOL;
});
