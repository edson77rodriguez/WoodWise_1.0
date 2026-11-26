<?php
// Auditoría PRE-DESPLIEGUE contra salida accidental antes de <?php.
// Uso desde la raíz Laravel: php scripts/verificar_inicio_php.php
$paths = ['app', 'bootstrap', 'config', 'routes'];
$files = ['public/index.php'];
foreach ($paths as $path) {
    if (!is_dir($path)) continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $entry) {
        if ($entry->isFile() && strtolower($entry->getExtension()) === 'php') {
            $files[] = $entry->getPathname();
        }
    }
}
$issues = [];
foreach ($files as $file) {
    if (!is_file($file)) continue;
    $handle = fopen($file, 'rb');
    $head = fread($handle, 20);
    fclose($handle);
    if (!str_starts_with($head, '<?php')) {
        $issues[] = "$file: no comienza exactamente con <?php (HEX " . bin2hex(substr($head, 0, 10)) . ')';
    }
}
if ($issues) {
    fwrite(STDERR, "SIGMAD: ALERTA de bytes previos a la apertura PHP:\n" . implode("\n", $issues) . "\n");
    exit(1);
}
echo "SIGMAD: OK. " . count($files) . " archivos PHP examinados, sin bytes antes de <?php.\n";
