<?php
// Cargar Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$request = \Illuminate\Http\Request::capture();
$kernel->handle($request);

use App\Models\Troza;
use App\Models\Arbol;
use App\Models\Estimacion;
use App\Models\Estimacion1;

echo "===== CREANDO ESTIMACIONES DE PRUEBA =====" . PHP_EOL;

// Crear estimaciones para las 6 trozas
$trozas = Troza::all();
$formulas = [1, 2, 3, 4, 1, 2]; // Diferentes fórmulas

foreach ($trozas as $index => $troza) {
    $est = Estimacion::create([
        'id_troza' => $troza->id_troza,
        'id_tipo_e' => 1,
        'id_formula' => $formulas[$index % count($formulas)],
        'calculo' => 0, // El trigger lo calcula
    ]);
    echo "✓ Estimación para Troza {$troza->id_troza} (Fórmula {$formulas[$index % count($formulas)]}) → Volumen: {$est->calculo} m³, Biomasa: {$est->biomasa} ton" . PHP_EOL;
}

// Crear estimaciones para los 6 árboles (solo algunos)
$arboles = Arbol::limit(4)->get();
$arbolFormulas = [5, 7, 5, 6]; // Biomasa por especie

foreach ($arboles as $index => $arbol) {
    $est = Estimacion1::create([
        'id_arbol' => $arbol->id_arbol,
        'id_tipo_e' => 2, // Biomasa
        'id_formula' => $arbolFormulas[$index % count($arbolFormulas)],
        'calculo' => 0, // El trigger lo calcula
    ]);
    echo "✓ Estimación para Árbol {$arbol->id_arbol} (Fórmula {$arbolFormulas[$index % count($arbolFormulas)]}) → Biomasa: {$est->biomasa} kg, Carbono: {$est->carbono} kg" . PHP_EOL;
}

echo PHP_EOL . "===== DATOS AHORA REALISTAS =====" . PHP_EOL;
echo "Estimaciones Trozas: " . Estimacion::count() . PHP_EOL;
echo "Estimaciones Árboles: " . Estimacion1::count() . PHP_EOL;
