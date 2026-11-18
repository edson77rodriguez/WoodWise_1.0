<?php
/**
 * Ejecutar SOLO en el repositorio de desarrollo local (antes de git commit).
 * php scripts/registrar_v04.php [--apply]
 * Sin --apply: simulación. No toca base de datos ni archivos públicos de Hostinger.
 */
if (PHP_SAPI !== 'cli') { fwrite(STDERR, "Solo CLI.\n"); exit(2); }
$apply = in_array('--apply', $argv, true);
$root = dirname(__DIR__);
$routePath = $root.'/routes/campo.php';
$indexPath = $root.'/resources/views/campo/registros/index.blade.php';
foreach ([$routePath, $indexPath] as $path) {
    if (!is_file($path)) {
        fwrite(STDERR, "ERROR: falta archivo existente: $path\n"); exit(2);
    }
}
$route = file_get_contents($routePath);
$index = file_get_contents($indexPath);
if ($route === false || $index === false || !str_starts_with(ltrim($route), '<?php')) {
    fwrite(STDERR, "ERROR: archivos incompatibles; no se modificó nada.\n"); exit(2);
}
if (str_contains($route, '?>')) {
    fwrite(STDERR, "ERROR: routes/campo.php cierra PHP; requiere integración manual.\n"); exit(2);
}
$routeSnippet = "require __DIR__ . '/campo_vinculaciones.php';";
$routeChanged = !str_contains($route, $routeSnippet);
if ($routeChanged) $route = rtrim($route)."\n\n// SIGMAD Campo V0.4.0 — Vinculación científica.\n".$routeSnippet."\n";

$link = <<<'BLADE'
            <a class="cr-btn cr-light" href="{{ route('campo.links.index') }}">Vincular copas IA</a>
BLADE;
$anchor = <<<'BLADE'
            <a class="cr-btn cr-light" href="{{ route('campo.missions.create') }}">Misiones</a>
BLADE;
$indexChanged = !str_contains($index, "route('campo.links.index')");
if ($indexChanged) {
    if (substr_count($index, $anchor) !== 1) {
        fwrite(STDERR, "ERROR: no se encontró de forma única el botón 'Misiones'. No se modificó nada.\n"); exit(2);
    }
    $index = str_replace($anchor, $anchor."\n".$link, $index);
}

echo ($apply ? 'INSTALACIÓN LOCAL' : 'SIMULACIÓN') . " V0.4.0\n";
echo 'routes/campo.php: '.($routeChanged ? 'añadir require' : 'ya está incluido')."\n";
echo 'resources/views/campo/registros/index.blade.php: '.($indexChanged ? 'añadir botón' : 'botón existente')."\n";
if (!$apply) { echo "Sin modificaciones. Usa --apply después de revisar.\n"; exit(0); }
$stamp = date('Ymd_His');
foreach ([[$routePath,$route,$routeChanged],[$indexPath,$index,$indexChanged]] as [$path,$source,$changed]) {
    if (!$changed) continue;
    $backup = $path.'.bak_v04_'.$stamp;
    if (!copy($path, $backup) || file_put_contents($path, $source, LOCK_EX) === false) {
        fwrite(STDERR, "ERROR al guardar $path. Revisa el respaldo $backup.\n"); exit(1);
    }
    echo "OK: modificado $path (respaldo: $backup)\n";
}
echo "Listo. Revisa git diff y realiza commit de los cambios elegidos.\n";
