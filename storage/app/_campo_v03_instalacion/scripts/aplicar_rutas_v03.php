<?php
/** Append-only patch for SIGMAD Campo V0.3; run via CLI after backing up routes/campo.php. */
if (PHP_SAPI !== 'cli') { fwrite(STDERR, "Solo CLI.\n"); exit(2); }
$target = $argv[1] ?? '';
if ($target === '' || !is_file($target) || basename($target) !== 'campo.php') {
    fwrite(STDERR, "Uso: php aplicar_rutas_v03.php /ruta/woodwise_src/routes/campo.php\n"); exit(2);
}
$source = file_get_contents($target);
if (!str_starts_with(ltrim($source), '<?php')) { fwrite(STDERR, "No parece un archivo PHP.\n"); exit(2); }
if (str_contains($source, "'/campo/registros'")) {
    fwrite(STDOUT, "Rutas de registros ya presentes; no se duplicaron. Comprueba route:list.\n"); exit(0);
}
if (str_contains($source, '?>')) {
    fwrite(STDERR, "El archivo tiene cierre PHP; agrega las rutas manualmente. No modificado.\n"); exit(2);
}
$patch = <<<'ROUTES'

// SIGMAD Campo V0.3 — administración de observaciones (mantener las rutas V0.2.1).
\Illuminate\Support\Facades\Route::middleware(['auth', 'role:Administrador'])->group(function () {
    \Illuminate\Support\Facades\Route::get('/campo/registros', [\App\Http\Controllers\FieldRecordsController::class, 'index'])->name('campo.records.index');
    \Illuminate\Support\Facades\Route::get('/campo/registros/exportar', [\App\Http\Controllers\FieldRecordsController::class, 'export'])->name('campo.records.export');
    \Illuminate\Support\Facades\Route::get('/campo/registros/mapa', [\App\Http\Controllers\FieldRecordsController::class, 'map'])->name('campo.records.map');
    \Illuminate\Support\Facades\Route::get('/campo/registros/{record}', [\App\Http\Controllers\FieldRecordsController::class, 'show'])->whereNumber('record')->name('campo.records.show');
    \Illuminate\Support\Facades\Route::post('/campo/registros/{record}/revision', [\App\Http\Controllers\FieldRecordsController::class, 'review'])->whereNumber('record')->name('campo.records.review');
});
ROUTES;
if (file_put_contents($target, rtrim($source)."\n".$patch."\n", LOCK_EX) === false) {
    fwrite(STDERR, "No fue posible escribir las rutas.\n"); exit(1);
}
fwrite(STDOUT, "Rutas V0.3 agregadas sin reemplazar las anteriores.\n");
