<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "1. Iniciando...\n";
flush();

try {
    require 'bootstrap/app.php';
    echo "2. Bootstrap cargado\n";
    flush();
    
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    echo "3. Kernel iniciado\n";
    flush();

    use Illuminate\Support\Facades\DB;

    echo "4. Conectando a BD...\n";
    flush();
    
    $all = DB::table('personas')->select('id_persona', 'nom', 'ap', 'telefono', 'id_rol')->get();
    echo "5. Query ejecutada. Resultados: " . count($all) . "\n";
    flush();
    
    foreach($all as $p) {
        echo "ID: {$p->id_persona} | {$p->nom} {$p->ap} | TEL: [{$p->telefono}] | ROL: {$p->id_rol}\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
