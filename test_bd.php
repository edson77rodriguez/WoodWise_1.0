<?php
require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== TELÉFONOS EN BD ===\n";
$all = DB::table('personas')->select('id_persona', 'nom', 'ap', 'telefono', 'id_rol')->get();
foreach($all as $p) {
    echo "ID: {$p->id_persona} | {$p->nom} {$p->ap} | TEL: [{$p->telefono}] | ROL: {$p->id_rol}\n";
}
