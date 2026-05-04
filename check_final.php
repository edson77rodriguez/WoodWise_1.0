<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$request = \Illuminate\Http\Request::capture();
$kernel->handle($request);

use App\Models\Estimacion;
use App\Models\Estimacion1;

echo "===== VERIFICACIÓN DE ESTIMACIONES =====" . PHP_EOL;

echo PHP_EOL . "Estimaciones de Trozas (primeras 3):" . PHP_EOL;
Estimacion::limit(3)->each(function($est) {
    echo "ID: {$est->id_estimacion}, Troza: {$est->id_troza}, Cálculo: {$est->calculo}, Biomasa: {$est->biomasa}, Carbono: {$est->carbono}" . PHP_EOL;
});

echo PHP_EOL . "Estimaciones de Árboles (primeras 3):" . PHP_EOL;
Estimacion1::limit(3)->each(function($est) {
    echo "ID: {$est->id_estimacion1}, Árbol: {$est->id_arbol}, Cálculo: {$est->calculo}, Biomasa: {$est->biomasa}, Carbono: {$est->carbono}" . PHP_EOL;
});

echo PHP_EOL . "===== RESUMEN =====" . PHP_EOL;
echo "Total Estimaciones (Trozas): " . Estimacion::count() . PHP_EOL;
echo "Total Estimaciones (Árboles): " . Estimacion1::count() . PHP_EOL;
echo "Total Trozas: " . \App\Models\Troza::count() . PHP_EOL;
echo "Total Árboles: " . \App\Models\Arbol::count() . PHP_EOL;
