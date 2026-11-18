<?php
require 'bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$telefono = '527226367194';

echo "=== BUSCANDO TELÉFONO: $telefono ===\n";

// Búsqueda exacta
$result = DB::table('personas')
    ->where('telefono', $telefono)
    ->first();

if ($result) {
    echo "✅ ENCONTRADO (búsqueda exacta):\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
    
    // Verificar rol
    $rol = DB::table('roles')->where('id_rol', $result->id_rol)->first();
    echo "📋 ROL ASIGNADO: " . ($rol ? $rol->nom_rol : 'SIN ROL') . "\n";
} else {
    echo "❌ NO ENCONTRADO (búsqueda exacta)\n";
    
    // Mostrar todos los teléfonos
    echo "\n--- TODOS LOS TELÉFONOS EN LA BD ---\n";
    $all = DB::table('personas')->select('id_persona', 'nom', 'ap', 'telefono', 'id_rol')->get();
    foreach($all as $p) {
        echo "ID: {$p->id_persona} | {$p->nom} {$p->ap} | TEL: [{$p->telefono}] | ROL: {$p->id_rol}\n";
    }
}
