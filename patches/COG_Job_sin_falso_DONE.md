# Parche puntual opcional: evitar falso `DONE` de COG

En el archivo real `app/Jobs/GenerateMosaicCogJob.php`, localiza `handle()`.
**No reemplaces todo el archivo sin compararlo**, pues la versión desplegada puede ser posterior a la incluida en los archivos del chat.

Justo después de `$controller->processCogJob($mosaic, $ai, $this->force);` añade:

```php
$fresh = Mosaic::find($this->mosaicId);
if ($fresh && data_get($fresh->metadata, 'web_cog_generation.status') === 'failed') {
    throw new \RuntimeException(
        'COG fallido: '.(string) data_get($fresh->metadata, 'web_cog_generation.error', 'sin detalle')
    );
}
```

Así se informa al sistema de colas que el trabajo terminó con error en vez de ocultarlo detrás de `DONE`.
La recuperación sigue a cargo del comando independiente con un límite de cinco reencolados.

En `InspectMosaicJob::handle()` es posible aplicar el mismo patrón **solo después de revisar la versión real desplegada**, verificando `$mosaic->status === 'failed'` y `inspection_failure.error`. No se incluye reemplazo automático para evitar conflictos.
