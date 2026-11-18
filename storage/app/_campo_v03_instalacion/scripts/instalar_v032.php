<?php
/**
 * SIGMAD Campo V0.3.2 — instalador conservador CLI.
 * Uso: php instalar_v032.php /ruta/woodwise_src /ruta/public_html /ruta/extraido [--install]
 * Sin --install únicamente verifica; deja intacta la instalación.
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
if ($argc < 4) { fwrite(STDERR, "Uso: php instalar_v032.php APP PUBLIC_HTML STAGE [--install]\n"); exit(2); }
[$app, $web, $stage] = [rtrim($argv[1], '/'), rtrim($argv[2], '/'), rtrim($argv[3], '/')];
$install = in_array('--install', $argv, true);
$view = $app.'/resources/views/campo/registros/index.blade.php';
$routes = $app.'/routes/campo.php';
if (!is_file($app.'/artisan') || !is_file($view) || !is_file($routes)) {
    fwrite(STDERR, "ERROR: no se encontró artisan, routes/campo.php o la vista de registros.\n"); exit(2);
}

$start = <<<'MARKER'
    <section class="cr-panel">
        <div class="cr-section-head"><div><h2>Mapa de observaciones GPS</h2>
MARKER;
$endMarker = '    </section>';
$newMap = <<<'BLADE'
    <section class="cr-panel">
        <div class="cr-section-head"><div>
            <h2>Mapa integrado: campo + copas IA</h2>
            <p>Administración y revisión posterior al levantamiento. Las copas son únicamente las asignadas a la misión elegida.</p>
        </div><span id="cr-map-count">Preparando…</span></div>
        <div class="cr-map-toolbar">
            <label>Misión UAV para superponer
                <select id="cr-mission-select"><option value="">Solo puntos GPS de campo</option></select>
            </label>
            <button type="button" id="cr-fit-mission">Enfocar misión</button>
            <button type="button" id="cr-fit-gps">Enfocar GPS de campo</button>
        </div>
        <div class="cr-map-toggles" aria-label="Capas del mapa">
            <label><input type="checkbox" id="cr-layer-image" checked> Ortomosaico (vista previa / HD)</label>
            <label><input type="checkbox" id="cr-layer-ai" checked> Copas IA de la misión</label>
            <label><input type="checkbox" id="cr-layer-gps" checked> Árboles registrados en campo</label>
            <label><input type="checkbox" id="cr-layer-accuracy" checked> Precisión GNSS reportada</label>
        </div>
        <div class="cr-map-legend" aria-label="Simbología">
            <span><b></b> GPS medido</span>
            <span><b class="cr-ai-legend"></b> Contorno IA asignado</span>
        </div>
        <p class="cr-map-status" id="cr-map-status" role="status">Preparando visualización…</p>
        <div id="cr-record-map" role="region" aria-label="Mapa comparativo de observaciones georreferenciadas y copas asignadas por IA"></div>
        <p class="cr-map-note" id="cr-map-note">El punto GPS es la posición reportada del teléfono junto al fuste, no el centro de la copa IA. No se clasifica TP/FP/FN automáticamente.</p>
        <div id="cr-selected-record" class="cr-map-detail" hidden></div>
        <p class="cr-map-info">Mapa administrativo online; no sustituye las misiones HD offline de SIGMAD Campo. Las capas y filtros no modifican mediciones.</p>
    </section>
BLADE;
$originalView = file_get_contents($view);
$originalRoutes = file_get_contents($routes);
if ($originalView === false || $originalRoutes === false) {fwrite(STDERR, "No se pudieron leer los archivos.\n"); exit(2);}

try {
    $nextView = $originalView;
    if (!str_contains($nextView, 'registros-mapa-integrado.js')) {
        $startAt = strpos($nextView, $start);
        if ($startAt === false) throw new RuntimeException('La vista del mapa V0.3 cambió: no se identifica su sección original.');
        $endAt = strpos($nextView, $endMarker, $startAt);
        if ($endAt === false) throw new RuntimeException('No se encontró el cierre del mapa.');
        $endAt += strlen($endMarker);
        $nextView = substr($nextView, 0, $startAt).$newMap.substr($nextView, $endAt);

        $oldCss = <<<'STR'
<link rel="stylesheet" href="{{ asset('sigmad-campo-assets/registros.css') }}">
STR;
        $newCss = $oldCss."\n".<<<'STR'
<link rel="stylesheet" href="{{ asset('sigmad-campo-assets/registros-mapa-integrado.css') }}?v=0320">
STR;
        if (substr_count($nextView, $oldCss) !== 1) throw new RuntimeException('No se encontró una sola referencia a registros.css');
        $nextView = str_replace($oldCss, $newCss, $nextView);

        $oldBoot = <<<'STR'
window.SIGMAD_FIELD_RECORD_MAP = @json(['url' => route('campo.records.map', request()->query())]);
STR;
        $newBoot = <<<'STR'
window.SIGMAD_FIELD_RECORD_MAP = @json([
    'url' => route('campo.records.map', request()->query()),
    'missions_url' => route('campo.records.layers.missions', array_filter(['parcela' => request()->query('parcela')])),
    'parcela_id' => request()->query('parcela'),
]);
STR;
        if (substr_count($nextView, $oldBoot) !== 1) throw new RuntimeException('No se identificó una sola configuración original del mapa.');
        $nextView = str_replace($oldBoot, $newBoot, $nextView);

        $oldOl = <<<'STR'
<script src="{{ asset('sigmad-campo-assets/vendor/ol.js') }}" defer></script>
STR;
        $proj = <<<'STR'
<script src="{{ asset('sigmad-campo-assets/vendor/proj4.js') }}" defer></script>
STR;
        if (substr_count($nextView, $oldOl) !== 1) throw new RuntimeException('No se encontró la referencia a ol.js');
        $nextView = str_replace($oldOl, $proj."\n".$oldOl, $nextView);
        $pattern = '~<script src="\{\{ asset\(\'sigmad-campo-assets/registros\.js\'\) \}\}(?:\?v=[^\"]*)?" defer></script>~';
        $nextView = preg_replace($pattern, <<<'STR'
<script src="{{ asset('sigmad-campo-assets/registros-mapa-integrado.js') }}?v=0320" defer></script>
STR, $nextView, 1, $count);
        if ($count !== 1) throw new RuntimeException('No se reconoció registros.js para reemplazar.');
    }
    $include = "require __DIR__ . '/campo_registros_capas.php';";
    $nextRoutes = $originalRoutes;
    if (!str_contains($nextRoutes, $include)) {
        if (!str_starts_with(ltrim($nextRoutes), '<?php') || str_contains($nextRoutes, '?>')) throw new RuntimeException('Formato routes/campo.php no apto para parche automático.');
        $nextRoutes = rtrim($nextRoutes)."\n\n// SIGMAD Campo V0.3.2 — mapa administrativo (solo lectura).\n".$include."\n";
    }
    $copies = [
        $app.'/app/Http/Controllers/FieldRecordsLayersController.php' => $stage.'/app/Http/Controllers/FieldRecordsLayersController.php',
        $app.'/routes/campo_registros_capas.php' => $stage.'/routes/campo_registros_capas.php',
        $web.'/sigmad-campo-assets/registros-mapa-integrado.js' => $stage.'/public/sigmad-campo-assets/registros-mapa-integrado.js',
        $web.'/sigmad-campo-assets/registros-mapa-integrado.css' => $stage.'/public/sigmad-campo-assets/registros-mapa-integrado.css',
    ];
    foreach ($copies as $source) if (!is_file($source) || filesize($source) < 100) throw new RuntimeException('Falta archivo en paquete: '.$source);
    echo "OK: archivos reconocidos, nueva ruta GET /campo/registros/capas/misiones.\n";
    echo "No se requieren migraciones ni cambios en observaciones/misiones.\n";
    if (!$install) {echo "SIMULACIÓN: ninguna modificación realizada. Usa --install para instalar.\n";exit(0);}
    $backup = $app.'/storage/app/respaldo_campo_v032_'.date('Ymd_His');
    if (!mkdir($backup, 0750, true)) throw new RuntimeException('No se pudo crear carpeta de respaldo.');
    $oldFiles = [$view, $routes];
    foreach ($copies as $dest => $src) $oldFiles[] = $dest;
    foreach ($oldFiles as $path) {
        if (!is_file($path)) continue;
        $prefix = str_starts_with($path, $app.'/') ? 'laravel/' : 'public_html/';
        $rel = str_starts_with($path, $app.'/') ? substr($path, strlen($app)+1) : substr($path, strlen($web)+1);
        $target = $backup.'/'.$prefix.$rel;
        if (!is_dir(dirname($target))) mkdir(dirname($target), 0750, true);
        if (!copy($path, $target)) throw new RuntimeException('No se pudo respaldar '.$path);
    }
    foreach ($copies as $dest => $src) {
        if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0755, true);
        if (!copy($src, $dest)) throw new RuntimeException('No se pudo instalar '.$dest);
    }
    if (file_put_contents($view, $nextView, LOCK_EX) === false || file_put_contents($routes, $nextRoutes, LOCK_EX) === false)
        throw new RuntimeException('No se pudieron escribir la vista o las rutas. Revisar respaldo.');
    echo "INSTALADO. Respaldo: {$backup}\n";
    echo "Ahora ejecuta php -l, route:clear, route:list y view:cache.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "ERROR: ".$e->getMessage()."\n");exit(2);
}
