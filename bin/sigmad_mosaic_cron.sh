#!/usr/bin/env bash
set -uo pipefail

# Instalacion: ajustar APP_DIR al valor real mostrado por "pwd" en woodwise_src.
APP_DIR="/RUTA/REAL/woodwise_src"
PHP_BIN="$(command -v php)"

cd "$APP_DIR" || exit 10
mkdir -p storage/logs

# lock de sistema operativo: aun si cron lanza una segunda instancia,
# no se solapan los trabajos largos de mosaic-long.
exec 9>storage/framework/sigmad-mosaic-cron.lock
flock -n 9 || exit 0

"$PHP_BIN" artisan sigmad:pipeline:tick >>storage/logs/sigmad-pipeline-tick.log 2>&1
