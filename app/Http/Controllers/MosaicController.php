<?php



namespace App\Http\Controllers;



use App\Models\Mosaic;

use App\Models\Project;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;

use App\Services\AiService;

use Illuminate\Support\Facades\Log;





class MosaicController extends Controller

{

    /**

     * Mostrar ortomosaicos.

     */

    public function index()

    {

        $projects = Project::where('status', 'active')

            ->orderBy('name')

            ->get();



        $mosaics = Mosaic::with('project')

            ->latest()

            ->get();



        return view('mosaics.index', compact(

            'projects',

            'mosaics'

        ));

    }





    /**

     * Crear registro y generar URL temporal R2.

     */

    public function presign(Request $request)

    {

        $validated = $request->validate([

            'project_id' => [

                'required',

                'exists:projects,id'

            ],



            'filename' => [

                'required',

                'string',

                'max:255'

            ],



            'size_bytes' => [

                'required',

                'integer',

                'min:1'

            ],



            'mime_type' => [

                'nullable',

                'string',

                'max:100'

            ],

        ]);





        /*

        |--------------------------------------------------------------------------

        | Validar extensión

        |--------------------------------------------------------------------------

        */



        $extension = strtolower(

            pathinfo(

                $validated['filename'],

                PATHINFO_EXTENSION

            )

        );



        if (!in_array($extension, ['tif', 'tiff'])) {

            return response()->json([

                'message' =>

                    'Solo se permiten archivos GeoTIFF .tif o .tiff.'

            ], 422);

        }





        /*

        |--------------------------------------------------------------------------

        | Proyecto

        |--------------------------------------------------------------------------

        */



        $project = Project::findOrFail(

            $validated['project_id']

        );





        /*

        |--------------------------------------------------------------------------

        | UUID del mosaico

        |--------------------------------------------------------------------------

        */



        $mosaicUuid = (string) Str::uuid();





        /*

        |--------------------------------------------------------------------------

        | Clave del objeto en R2

        |--------------------------------------------------------------------------

        */



        $objectKey =

            'projects/'

            . $project->uuid

            . '/mosaics/'

            . $mosaicUuid

            . '/original/orthomosaic.'

            . $extension;





        /*

        |--------------------------------------------------------------------------

        | Registro inicial

        |--------------------------------------------------------------------------

        */



        $mosaic = Mosaic::create([



            'uuid' =>

                $mosaicUuid,



            'project_id' =>

                $project->id,



            'original_name' =>

                $validated['filename'],



            'object_key' =>

                $objectKey,



            'size_bytes' =>

                $validated['size_bytes'],



            'mime_type' =>

                $validated['mime_type']

                    ?? 'image/tiff',



            'extension' =>

                $extension,



            'status' =>

                'uploading',

        ]);





        /*

        |--------------------------------------------------------------------------

        | URL temporal de subida

        |--------------------------------------------------------------------------

        |

        | El archivo va directamente:

        |

        | Navegador -> R2

        |

        | NO:

        |

        | Navegador -> Laravel -> R2

        |

        */



        try {



            $upload = Storage::disk('r2')

                ->temporaryUploadUrl(

                    $objectKey,

                    now()->addMinutes(30)

                );



        } catch (\Throwable $e) {



            $mosaic->delete();



            return response()->json([

                'message' =>

                    'No se pudo generar la URL de subida.',



                'error' =>

                    $e->getMessage(),

            ], 500);

        }





        return response()->json([



            'status' =>

                'ok',



            'mosaic_uuid' =>

                $mosaic->uuid,



            'object_key' =>

                $objectKey,



            'upload_url' =>

                $upload['url'],



            'headers' =>

                $upload['headers'] ?? [],

        ]);

    }





    /**

     * Confirmar que la subida terminó.

     */

    public function complete(Request $request)

    {

        $validated = $request->validate([

            'mosaic_uuid' => [

                'required',

                'uuid'

            ],

        ]);





        $mosaic = Mosaic::where(

            'uuid',

            $validated['mosaic_uuid']

        )->firstOrFail();





        /*

        |--------------------------------------------------------------------------

        | Verificar físicamente en R2

        |--------------------------------------------------------------------------

        */



        if (

            !Storage::disk('r2')

                ->exists($mosaic->object_key)

        ) {



            return response()->json([

                'message' =>

                    'El archivo todavía no existe en R2.'

            ], 422);

        }





        /*

        |--------------------------------------------------------------------------

        | Tamaño real

        |--------------------------------------------------------------------------

        */



        $size = Storage::disk('r2')

            ->size($mosaic->object_key);





        /*

        |--------------------------------------------------------------------------

        | Actualizar estado

        |--------------------------------------------------------------------------

        */



        $mosaic->update([



            'size_bytes' =>

                $size,



            'status' =>

                'uploaded',



            'uploaded_at' =>

                now(),

        ]);





        return response()->json([



            'status' =>

                'ok',



            'mosaic_uuid' =>

                $mosaic->uuid,



            'size_bytes' =>

                $size,



            'message' =>

                'Ortomosaico almacenado correctamente.'

        ]);

    }

public function inspect(Mosaic $mosaic)
{
    // La petición HTTP sólo registra trabajo. No descarga ni analiza el GeoTIFF.
    $originalStatus = $mosaic->status;
    if (!in_array($originalStatus, ['uploaded', 'failed'], true)) {
        return redirect()->route('mosaics.index')
            ->with('error', 'Este mosaico ya está en proceso o no está listo para inspección.');
    }

    $claimed = Mosaic::whereKey($mosaic->getKey())
        ->where('status', $originalStatus)
        ->update(['status' => 'inspecting']);
    if (!$claimed) {
        return redirect()->route('mosaics.index')
            ->with('error', 'El estado del mosaico cambió. Recarga y revisa su estado.');
    }

    try {
        \App\Jobs\InspectMosaicJob::dispatch($mosaic->getKey())
            ->onConnection('database_mosaic')
            ->onQueue('mosaic-long');
    } catch (\Throwable $e) {
        Mosaic::whereKey($mosaic->getKey())->where('status', 'inspecting')
            ->update(['status' => $originalStatus]);
        Log::error('No se pudo encolar inspección de mosaico', [
            'mosaic_uuid' => $mosaic->uuid, 'error' => $e->getMessage(),
        ]);
        return redirect()->route('mosaics.index')
            ->with('error', 'No fue posible registrar la inspección en la cola. Revisa el registro del servidor.');
    }

    return redirect()->route('mosaics.index')
        ->with('success', 'Inspección registrada en la cola. Actualiza la página para consultar el estado.');
}

/**
 * Ejecutado ÚNICAMENTE por el job en CLI. Inspecciona el GeoTIFF y conserva
 * los metadatos/insumos existentes; nunca marca fallida la fuente si falla el COG.
 */
public function processInspectionJob(Mosaic $mosaic, AiService $ai): void
{
    $mosaic->refresh();
    if ($mosaic->status !== 'inspecting') {
        Log::warning('Inspección de mosaico omitida por estado no elegible', [
            'mosaic_uuid' => $mosaic->uuid, 'status' => $mosaic->status,
        ]);
        return;
    }

    try {
        $result = $ai->inspectMosaic($mosaic->uuid, $mosaic->object_key);
        if (($result['mosaic_uuid'] ?? null) !== $mosaic->uuid
            || ($result['object_key'] ?? null) !== $mosaic->object_key
            || (isset($result['size_bytes'])
                && (int) $result['size_bytes'] !== (int) $mosaic->size_bytes)) {
            throw new \RuntimeException('La inspección no corresponde al objeto R2 registrado.');
        }

        /*
        |--------------------------------------------------------------------------
        | Preservar metadata existente
        |--------------------------------------------------------------------------
        |
        | Una reinspección NO debe borrar preview, tiling_preview ni web_cog.
        |
        */
        $metadata = $this->normalizeMosaicMetadata(
            $mosaic->metadata
        );

        $inspectionMetadata = (
            isset($result['metadata'])
            && is_array($result['metadata'])
        )
            ? $result['metadata']
            : [];

        $metadata = array_replace_recursive(
            $metadata,
            $inspectionMetadata
        );

        $metadata['inspection'] = [
            'processing_seconds' =>
                $result['processing_seconds'] ?? null,

            'size_bytes' =>
                $result['size_bytes'] ?? null,

            'inspected_at' =>
                now()->toIso8601String(),
        ];

        $mosaic->update([
            'width' =>
                $result['width'] ?? null,

            'height' =>
                $result['height'] ?? null,

            'bands' =>
                $result['bands'] ?? null,

            'dtype' =>
                $result['dtype'] ?? null,

            'crs' =>
                $result['crs'] ?? null,

            'pixel_size_x' =>
                $result['pixel_size_x'] ?? null,

            'pixel_size_y' =>
                $result['pixel_size_y'] ?? null,

            'gsd_cm' =>
                $result['gsd_cm'] ?? null,

            'bounds' =>
                $result['bounds'] ?? null,

            'transform' =>
                $result['transform'] ?? null,

            'nodata' =>
                $result['nodata'] ?? null,

            'checksum_sha256' =>
                $result['checksum_sha256'] ?? null,

            'metadata' =>
                $metadata,

            'status' =>
                'ready',

            'inspected_at' =>
                now(),
        ]);

        // La fuente científica ya quedó inspeccionada. El COG es un derivado
        // independiente y se genera en OTRA tarea, con su propio tiempo límite.
        $mosaic->refresh();
        try {
            \App\Jobs\GenerateMosaicCogJob::dispatch($mosaic->getKey(), false)
                ->onConnection('database_mosaic')
                ->onQueue('mosaic-long');
            $metadata = $this->normalizeMosaicMetadata($mosaic->metadata);
            $metadata['web_cog_generation'] = [
                'status' => 'queued', 'queued_at' => now()->toIso8601String(),
            ];
            $mosaic->update(['metadata' => $metadata]);
        } catch (\Throwable $cogQueueError) {
            Log::warning('Inspección correcta, pero no se pudo encolar el COG.', [
                'mosaic_uuid' => $mosaic->uuid, 'error' => $cogQueueError->getMessage(),
            ]);
            $this->recordWebCogGenerationFailure($mosaic, $cogQueueError);
        }
    } catch (\Throwable $e) {
        Log::error('Error inspeccionando ortomosaico (cola)', [
            'mosaic_uuid' => $mosaic->uuid, 'error' => $e->getMessage(),
        ]);
        $mosaic->refresh();
        $metadata = $this->normalizeMosaicMetadata($mosaic->metadata);
        $metadata['inspection_failure'] = [
            'at' => now()->toIso8601String(), 'error' => $e->getMessage(),
        ];
        $mosaic->update(['status' => 'failed', 'metadata' => $metadata]);
    }
}

/**
 * Solicitud manual de COG: encolar, nunca generar dentro de la petición web.
 */
public function generateCog(Request $request, Mosaic $mosaic)
{
    if ($mosaic->status !== 'ready') {
        return redirect()->route('mosaics.index')
            ->with('error', 'Primero debe terminar la inspección del ortomosaico.');
    }
    $force = $request->boolean('force', false);
    $metadata = $this->normalizeMosaicMetadata($mosaic->metadata);
    if (!$force && ($metadata['web_cog_generation']['status'] ?? null) === 'queued') {
        return redirect()->route('mosaics.index')
            ->with('error', 'El COG ya se encuentra en la cola.');
    }
    try {
        \App\Jobs\GenerateMosaicCogJob::dispatch($mosaic->getKey(), $force)
            ->onConnection('database_mosaic')
            ->onQueue('mosaic-long');
    } catch (\Throwable $e) {
        Log::error('No se pudo encolar COG', [
            'mosaic_uuid' => $mosaic->uuid, 'error' => $e->getMessage(),
        ]);
        return redirect()->route('mosaics.index')
            ->with('error', 'No se pudo registrar la generación del COG.');
    }
    $metadata['web_cog_generation'] = [
        'status' => 'queued', 'queued_at' => now()->toIso8601String(),
    ];
    $mosaic->update(['metadata' => $metadata]);
    return redirect()->route('mosaics.index')
        ->with('success', 'COG registrado en la cola. Recarga la página para comprobar el estado.');
}

/**
 * Segundo trabajo: genera/reutiliza el COG preservando los datos de inspección.
 */
public function processCogJob(Mosaic $mosaic, AiService $ai, bool $force = false): void
{
    $claimed = Mosaic::whereKey($mosaic->getKey())
        ->where('status', 'ready')
        ->update(['status' => 'processing']);
    if (!$claimed) {
        Log::warning('COG omitido: mosaico no disponible para procesar', [
            'mosaic_uuid' => $mosaic->uuid,
        ]);
        return;
    }

    $mosaic->refresh();
    try {
        $metadata = $this->normalizeMosaicMetadata($mosaic->metadata);
        $metadata['web_cog_generation'] = [
            'status' => 'generating', 'started_at' => now()->toIso8601String(),
        ];
        $mosaic->update(['metadata' => $metadata]);

        if (!$force && $this->hasCurrentVerifiedWebCog($mosaic)) {
            $metadata = $this->normalizeMosaicMetadata($mosaic->fresh()->metadata);
            $metadata['web_cog_generation'] = [
                'status' => 'ok', 'generated_at' => now()->toIso8601String(),
                'error' => null,
            ];
            $mosaic->update(['metadata' => $metadata]);
        } else {
            $result = $ai->generateMosaicWebCog($mosaic->uuid, $mosaic->object_key);
            $this->persistWebCogResult($mosaic, $result);
        }
    } catch (\Throwable $e) {
        Log::warning('Inspección válida; falló generación de COG en cola', [
            'mosaic_uuid' => $mosaic->uuid, 'error' => $e->getMessage(),
        ]);
        $this->recordWebCogGenerationFailure($mosaic, $e);
    } finally {
        Mosaic::whereKey($mosaic->getKey())->where('status', 'processing')
            ->update(['status' => 'ready']);
    }
}

/**
 * Normalizar metadata del modelo Mosaic.
 */
private function normalizeMosaicMetadata(
    $metadata
): array
{
    if (is_array($metadata)) {
        return $metadata;
    }

    if (is_string($metadata)) {
        $decoded = json_decode(
            $metadata,
            true
        );

        return is_array($decoded)
            ? $decoded
            : [];
    }

    return [];
}


/**
 * Determinar si el COG registrado sigue siendo válido para
 * el GeoTIFF original actual.
 */
private function hasCurrentVerifiedWebCog(
    Mosaic $mosaic
): bool
{
    $mosaic->loadMissing('project');

    $projectUuid = (
        $mosaic->project->uuid
        ?? null
    );

    if (!$projectUuid) {
        return false;
    }

    $metadata = $this->normalizeMosaicMetadata(
        $mosaic->metadata
    );

    $cog = data_get(
        $metadata,
        'web_cog',
        []
    );

    if (!is_array($cog)) {
        return false;
    }

    $verified = filter_var(
        $cog['verified'] ?? false,
        FILTER_VALIDATE_BOOLEAN
    );

    $objectKey = (
        $cog['object_key']
        ?? null
    );

    $sourceObjectKey = (
        $cog['source_object_key']
        ?? null
    );

    $sourceSha256 = (
        $cog['source_sha256']
        ?? null
    );

    if (
        !$verified
        ||
        !is_string($objectKey)
        ||
        $objectKey === ''
        ||
        $sourceObjectKey !== $mosaic->object_key
    ) {
        return false;
    }

    /*
     * Si ambos checksums están disponibles, deben coincidir.
     */
    if (
        !empty($mosaic->checksum_sha256)
        &&
        !empty($sourceSha256)
        &&
        !hash_equals(
            strtolower(
                (string) $mosaic->checksum_sha256
            ),
            strtolower(
                (string) $sourceSha256
            )
        )
    ) {
        return false;
    }

    $expectedPrefix = (
        "projects/{$projectUuid}/"
        . "mosaics/{$mosaic->uuid}/web/"
    );

    if (
        !str_starts_with(
            $objectKey,
            $expectedPrefix
        )
    ) {
        return false;
    }

    return Storage::disk('r2')
        ->exists(
            $objectKey
        );
}


/**
 * Validar la respuesta FastAPI y persistir metadata.web_cog.
 */
private function persistWebCogResult(
    Mosaic $mosaic,
    array $result
): void
{
    $mosaic->refresh();
    $mosaic->loadMissing('project');

    $projectUuid = (
        $mosaic->project->uuid
        ?? null
    );

    if (!$projectUuid) {
        throw new \RuntimeException(
            'El mosaico no tiene proyecto asociado.'
        );
    }

    if (
        ($result['status'] ?? null)
        !==
        'ok'
    ) {
        throw new \RuntimeException(
            'FastAPI no devolvió status=ok para el COG.'
        );
    }

    if (
        ($result['mosaic_uuid'] ?? null)
        !==
        $mosaic->uuid
    ) {
        throw new \RuntimeException(
            'El COG devuelto no corresponde al UUID del mosaico.'
        );
    }

    if (
        ($result['source_object_key'] ?? null)
        !==
        $mosaic->object_key
    ) {
        throw new \RuntimeException(
            'El COG no fue derivado del object_key original esperado.'
        );
    }

    $verified = filter_var(
        $result['verified'] ?? false,
        FILTER_VALIDATE_BOOLEAN
    );

    if (!$verified) {
        throw new \RuntimeException(
            'FastAPI devolvió un COG no verificado.'
        );
    }

    $cogObjectKey = (
        $result['cog_object_key']
        ?? null
    );

    if (
        !is_string($cogObjectKey)
        ||
        $cogObjectKey === ''
    ) {
        throw new \RuntimeException(
            'FastAPI no devolvió cog_object_key.'
        );
    }

    $expectedPrefix = (
        "projects/{$projectUuid}/"
        . "mosaics/{$mosaic->uuid}/web/"
    );

    if (
        !str_starts_with(
            $cogObjectKey,
            $expectedPrefix
        )
    ) {
        throw new \RuntimeException(
            'El cog_object_key está fuera del prefijo web permitido.'
        );
    }

    if (
        !Storage::disk('r2')
            ->exists(
                $cogObjectKey
            )
    ) {
        throw new \RuntimeException(
            'FastAPI reportó el COG, pero Laravel no lo encuentra en R2.'
        );
    }

    $metadata = $this->normalizeMosaicMetadata(
        $mosaic->metadata
    );

    $metadata['web_cog'] = [
        'status' =>
            'ready',

        'version' =>
            $result['version'] ?? 'V01',

        'source_object_key' =>
            $result['source_object_key'],

        'source_size_bytes' =>
            $result['source_size_bytes']
            ?? $mosaic->size_bytes,

        'source_sha256' =>
            $result['source_sha256']
            ?? $mosaic->checksum_sha256,

        'object_key' =>
            $cogObjectKey,

        'content_type' =>
            'image/tiff',

        'size_bytes' =>
            isset($result['size_bytes'])
                ? (int) $result['size_bytes']
                : null,

        'sha256' =>
            $result['sha256'] ?? null,

        'crs' =>
            $result['crs']
            ?? $mosaic->crs,

        'width' =>
            isset($result['width'])
                ? (int) $result['width']
                : $mosaic->width,

        'height' =>
            isset($result['height'])
                ? (int) $result['height']
                : $mosaic->height,

        'bands' =>
            isset($result['bands'])
                ? (int) $result['bands']
                : $mosaic->bands,

        'dtype' =>
            $result['dtype']
            ?? $mosaic->dtype,

        'bounds' =>
            $result['bounds']
            ?? $mosaic->bounds,

        'transform' =>
            $result['transform']
            ?? $mosaic->transform,

        'block_size' =>
            isset($result['block_size'])
                ? (int) $result['block_size']
                : 512,

        'block_shapes' =>
            $result['block_shapes'] ?? [],

        'overviews' =>
            $result['overviews'] ?? [],

        'processing_seconds' =>
            $result['processing_seconds']
            ?? null,

        'generated_at' =>
            now()->toIso8601String(),

        'verified' =>
            true,
    ];

    $metadata['web_cog_generation'] = [
        'status' =>
            'ok',

        'generated_at' =>
            now()->toIso8601String(),

        'error' =>
            null,
    ];

    $mosaic->update([
        'metadata' =>
            $metadata,
    ]);
}


/**
 * Registrar una falla del derivado web sin invalidar el GeoTIFF original.
 */
private function recordWebCogGenerationFailure(
    Mosaic $mosaic,
    \Throwable $error
): void
{
    $mosaic->refresh();

    $metadata = $this->normalizeMosaicMetadata(
        $mosaic->metadata
    );

    $metadata['web_cog_generation'] = [
        'status' =>
            'failed',

        'attempted_at' =>
            now()->toIso8601String(),

        'error' =>
            $error->getMessage(),
    ];

    $mosaic->update([
        'metadata' =>
            $metadata,
    ]);
}


public function generatePreview(

    Mosaic $mosaic,

    AiService $ai

)

{

    if ($mosaic->status !== 'ready') {



        return redirect()

            ->route('mosaics.index')

            ->with(

                'error',

                'El ortomosaico debe estar inspeccionado antes de generar la vista previa.'

            );

    }





    try {



        $result =

            $ai->generateMosaicPreview(

                $mosaic->uuid,

                $mosaic->object_key

            );





        $metadata =

            $mosaic->metadata

            ?? [];





        $metadata['preview'] = [



            'width' =>

                $result[

                    'preview_width'

                ] ?? null,



            'height' =>

                $result[

                    'preview_height'

                ] ?? null,



            'size_bytes' =>

                $result[

                    'size_bytes'

                ] ?? null,



            'jpeg_quality' =>

                $result[

                    'jpeg_quality'

                ] ?? null,



            'processing_seconds' =>

                $result[

                    'processing_seconds'

                ] ?? null,



            'generated_at' =>

                now()

                    ->toIso8601String(),

        ];





        $mosaic->update([



            'preview_object_key' =>

                $result[

                    'preview_object_key'

                ],



            'metadata' =>

                $metadata,

        ]);





        return redirect()

            ->route('mosaics.index')

            ->with(

                'success',

                'Vista previa generada correctamente.'

            );





    } catch (\Throwable $e) {



        Log::error(

            'Error generando preview de ortomosaico',

            [

                'mosaic_uuid' =>

                    $mosaic->uuid,



                'error' =>

                    $e->getMessage(),

            ]

        );





        return redirect()

            ->route('mosaics.index')

            ->with(

                'error',

                'No se pudo generar la vista previa: '

                . $e->getMessage()

            );

    }

}





public function preview(

    Mosaic $mosaic

)

{

    if (

        !$mosaic->preview_object_key

    ) {



        abort(

            404,

            'Este ortomosaico no tiene vista previa.'

        );

    }





    if (

        !Storage::disk('r2')

            ->exists(

                $mosaic->preview_object_key

            )

    ) {



        abort(

            404,

            'La vista previa no existe en R2.'

        );

    }





    $url =

        Storage::disk('r2')

            ->temporaryUrl(

                $mosaic->preview_object_key,

                now()->addMinutes(10)

            );





    return redirect()->away(

        $url

    );

}



public function generateTilingPreview(

    Mosaic $mosaic,

    AiService $ai

)

{

    if ($mosaic->status !== 'ready') {



        return redirect()

            ->route('mosaics.index')

            ->with(

                'error',

                'El ortomosaico debe estar inspeccionado antes de generar la cuadrícula.'

            );

    }



    try {



        $result = $ai->generateMosaicTilingPreview(

            $mosaic->uuid,

            $mosaic->object_key

        );





        if (

            empty($result['footprints_object_key'])

            ||

            empty($result['coverage_object_key'])

        ) {



            throw new \RuntimeException(

                'FastAPI no devolvió las rutas de los productos de tiling.'

            );

        }





        $metadata = $mosaic->metadata ?? [];





        $metadata['tiling_preview'] = [



            'footprints_object_key' =>

                $result['footprints_object_key'],



            'coverage_object_key' =>

                $result['coverage_object_key'],



            'width' =>

                $result['preview_width'] ?? null,



            'height' =>

                $result['preview_height'] ?? null,



            'footprints_size_bytes' =>

                $result['footprints_size_bytes'] ?? null,



            'coverage_size_bytes' =>

                $result['coverage_size_bytes'] ?? null,



            'processing_seconds' =>

                $result['processing_seconds'] ?? null,



            'configuration' =>

                $result['configuration'] ?? [],



            'grid' =>

                $result['grid'] ?? [],



            'actual_overlap' =>

                $result['actual_overlap'] ?? [],



            'summary' =>

                $result['summary'] ?? [],



            'coverage' =>

                $result['coverage'] ?? [],



            'generated_at' =>

                now()->toIso8601String(),

        ];





        $mosaic->update([

            'metadata' => $metadata,

        ]);





        return redirect()

            ->route('mosaics.index')

            ->with(

                'success',

                'Productos QA/QC de tiling generados correctamente.'

            );



    } catch (\Throwable $e) {



        Log::error(

            'Error generando productos de tiling',

            [

                'mosaic_uuid' => $mosaic->uuid,

                'object_key' => $mosaic->object_key,

                'error' => $e->getMessage(),

            ]

        );





        return redirect()

            ->route('mosaics.index')

            ->with(

                'error',

                'No se pudieron generar los productos de tiling: '

                . $e->getMessage()

            );

    }

}

public function tilingPreview(

    Mosaic $mosaic,

    string $type = 'footprints'

)

{

    if (

        !in_array(

            $type,

            [

                'footprints',

                'coverage',

            ],

            true

        )

    ) {



        abort(

            404,

            'Tipo de vista de tiling no válido.'

        );

    }





    $metadataPath =

        $type === 'coverage'

            ? 'tiling_preview\.coverage_object_key'

            : 'tiling_preview\.footprints_object_key';





    $objectKey = data_get(

        $mosaic->metadata,

        $metadataPath

    );





    if (!$objectKey) {



        abort(

            404,

            'No existe la vista de tiling solicitada.'

        );

    }





    if (

        !Storage::disk('r2')

            ->exists($objectKey)

    ) {



        abort(

            404,

            'El archivo de tiling no existe en R2.'

        );

    }





    $url = Storage::disk('r2')

        ->temporaryUrl(

            $objectKey,

            now()->addMinutes(10)

        );





    return redirect()->away(

        $url

    );

}












    /**
     * SIGMAD CLEANUP V1
     * DELETE /ortomosaicos/{mosaic}
     * Alcances explícitos: results (sólo IA), full (mosaico y derivados).
     * Usa la ruta y middleware existentes. NO acepta eliminación implícita.
     */
    public function destroy(Request $request, Mosaic $mosaic)
    {
        $validated = $request->validate([
            'deletion_scope' => ['required', 'in:results,full'],
            'confirmation_uuid' => ['required', 'string', 'max:36'],
        ]);

        $scope = $validated['deletion_scope'];
        if (!hash_equals((string) $mosaic->uuid, $validated['confirmation_uuid'])) {
            return redirect()->route('mosaics.index')->with(
                'error', 'Confirmación rechazada. Debes escribir el UUID exacto del ortomosaico.'
            );
        }

        $actor = auth()->id();
        $originalStatus = null;
        $claimed = false;
        $prefix = null;

        try {
            // Reserva atómica: impide que una segunda petición inicie otra limpieza.
            DB::transaction(function () use ($mosaic, $scope, &$originalStatus, &$claimed, &$prefix) {
                $current = Mosaic::query()->whereKey($mosaic->getKey())
                    ->lockForUpdate()->firstOrFail();
                $current->loadMissing('project');
                $projectUuid = $current->project?->uuid;

                if (!$projectUuid || !preg_match('/^[a-f0-9-]{36}$/i', (string) $projectUuid)) {
                    throw new \RuntimeException('Proyecto inválido; no es seguro seleccionar el prefijo R2.');
                }
                if (!preg_match('/^[a-f0-9-]{36}$/i', (string) $current->uuid)) {
                    throw new \RuntimeException('UUID de mosaico inválido.');
                }

                $prefix = "projects/{$projectUuid}/mosaics/{$current->uuid}/";
                if (!str_starts_with((string) $current->object_key, $prefix)) {
                    throw new \RuntimeException('La clave del GeoTIFF original está fuera del prefijo permitido.');
                }

                $metadata = $this->normalizeMosaicMetadata($current->metadata);
                $previous = $metadata['cleanup'] ?? [];
                $retry = $current->status === 'processing'
                    && ($previous['status'] ?? null) === 'failed'
                    && ($previous['scope'] ?? null) === $scope;

                if (!$retry && in_array($current->status, ['uploading', 'inspecting', 'processing'], true)) {
                    throw new \RuntimeException('El mosaico se está subiendo o procesando. No es seguro eliminarlo.');
                }
                if ($current->analysisJobs()->whereIn('status', [
                    'queued', 'processing', 'pending', 'running', 'starting', 'in_progress'
                ])->exists()) {
                    throw new \RuntimeException('Existen análisis en cola o ejecución. Debes terminarlos antes.');
                }

                // Los jobs de COG usan mosaic-long y pueden seguir activos aunque el mosaico esté ready.
                $cogStatus = data_get($metadata, 'web_cog_generation.status');
                if (in_array($cogStatus, ['queued', 'generating'], true)) {
                    throw new \RuntimeException(
                        'El COG figura en cola/generación. Comprueba y cancela primero ese trabajo de mosaic-long.'
                    );
                }

                $jobs = $current->analysisJobs()->with('artifacts')->get();
                $resultsPrefix = $prefix . 'analyses/';

                foreach ($jobs as $job) {
                    if ($job->result_prefix && !str_starts_with((string) $job->result_prefix, $resultsPrefix)) {
                        throw new \RuntimeException('Hay un AnalysisJob con result_prefix fuera de analyses/.');
                    }
                    foreach ($job->artifacts as $artifact) {
                        if (!str_starts_with((string) $artifact->object_key, $resultsPrefix)) {
                            throw new \RuntimeException('Hay un artifact fuera del prefijo analyses/; requiere revisión.');
                        }
                    }
                }

                if ($scope === 'full') {
                    foreach ([
                        $current->preview_object_key,
                        data_get($metadata, 'web_cog.object_key'),
                        data_get($metadata, 'tiling_preview.footprints_object_key'),
                        data_get($metadata, 'tiling_preview.coverage_object_key'),
                    ] as $knownKey) {
                        if ($knownKey && !str_starts_with((string) $knownKey, $prefix)) {
                            throw new \RuntimeException('Hay un derivado fuera del prefijo de mosaico; revisar antes de borrar.');
                        }
                    }
                }

                // Detener si existen referencias reales en otras tablas: protege la verdad de campo.
                $jobIds = $jobs->pluck('id')->all();
                $artifactIds = $jobs->flatMap(fn ($job) => $job->artifacts->pluck('id'))->all();
                $this->ensureNoOtherDatabaseReferences('analysis_artifacts', $artifactIds, []);
                $this->ensureNoOtherDatabaseReferences('analysis_jobs', $jobIds, ['analysis_artifacts']);
                if ($scope === 'full') {
                    $this->ensureNoOtherDatabaseReferences('mosaics', [$current->id], ['analysis_jobs']);
                }

                $originalStatus = $retry
                    ? ($previous['original_status'] ?? 'ready')
                    : $current->status;
                $metadata['cleanup'] = [
                    'scope' => $scope,
                    'status' => 'running',
                    'original_status' => $originalStatus,
                    'started_at' => now()->toIso8601String(),
                    'actor_id' => auth()->id(),
                ];
                $current->update(['status' => 'processing', 'metadata' => $metadata]);
                $claimed = true;
            });

            $disk = Storage::disk('r2');
            $targetPrefix = $scope === 'results' ? $prefix . 'analyses/' : $prefix;
            $files = $disk->allFiles($targetPrefix);
            if (!is_array($files)) {
                throw new \RuntimeException('No se pudo obtener el inventario R2.');
            }
            foreach ($files as $key) {
                if (!str_starts_with($key, $targetPrefix)) {
                    throw new \RuntimeException('R2 devolvió una clave fuera del prefijo autorizado.');
                }
                // No confundir archivos de verdad de campo con derivados de IA.
                // Bloquea rutas sospechosas en lugar de borrarlas automáticamente.
                $relative = substr($key, strlen($targetPrefix));
                if (preg_match('~(^|/)(campo|field_truth|ground_truth|fustes|parcelas|mediciones)(/|_|\.)~i', $relative)) {
                    throw new \RuntimeException(
                        'La carpeta contiene posibles datos de campo. Se bloqueó la eliminación para revisión manual.'
                    );
                }
            }

            $total = count($files);
            // No se transfieren los bytes del TIFF: R2 elimina por clave de objeto.
            foreach (array_chunk($files, 200) as $chunk) {
                $disk->delete($chunk);
            }
            $remaining = $disk->allFiles($targetPrefix);
            if (!is_array($remaining) || count($remaining) !== 0) {
                throw new \RuntimeException('R2 aún contiene objetos dentro del prefijo de eliminación.');
            }

            $deletedJobs = 0;
            $deletedArtifacts = 0;
            DB::transaction(function () use ($mosaic, $scope, $originalStatus, &$deletedJobs, &$deletedArtifacts) {
                $current = Mosaic::query()->whereKey($mosaic->getKey())
                    ->lockForUpdate()->firstOrFail();
                if ($current->status !== 'processing'
                    || data_get($current->metadata, 'cleanup.status') !== 'running') {
                    throw new \RuntimeException('El estado del mosaico cambió durante la limpieza.');
                }
                $jobs = $current->analysisJobs()->with('artifacts')->get();
                foreach ($jobs as $job) {
                    $deletedArtifacts += $job->artifacts->count();
                    $job->artifacts()->delete();
                    $job->delete();
                    $deletedJobs++;
                }
                if ($scope === 'full') {
                    $current->delete();
                } else {
                    $metadata = $this->normalizeMosaicMetadata($current->metadata);
                    $metadata['cleanup']['status'] = 'completed';
                    $metadata['cleanup']['completed_at'] = now()->toIso8601String();
                    $current->update(['status' => $originalStatus, 'metadata' => $metadata]);
                }
            });

            Log::notice('SIGMAD limpieza confirmada', [
                'mosaic_uuid' => $mosaic->uuid,
                'scope' => $scope,
                'r2_prefix' => $targetPrefix,
                'removed_objects' => $total,
                'removed_analysis_jobs' => $deletedJobs,
                'removed_artifacts' => $deletedArtifacts,
                'actor_id' => $actor,
            ]);

            return redirect()->route('mosaics.index')->with('success',
                ($scope === 'full' ? 'Ortomosaico completo eliminado. ' : 'Resultados de IA eliminados; GeoTIFF conservado. ')
                . "R2: {$total} objetos; análisis: {$deletedJobs}; artifacts: {$deletedArtifacts}."
            );

        } catch (\Throwable $e) {
            if ($claimed) {
                try {
                    $fresh = Mosaic::find($mosaic->getKey());
                    if ($fresh && data_get($fresh->metadata, 'cleanup.status') === 'running') {
                        $metadata = $this->normalizeMosaicMetadata($fresh->metadata);
                        $metadata['cleanup']['status'] = 'failed';
                        $metadata['cleanup']['error'] = $e->getMessage();
                        $metadata['cleanup']['failed_at'] = now()->toIso8601String();
                        // Se mantiene processing hasta reintento: evita usar resultados parcialmente borrados.
                        $fresh->update(['status' => 'processing', 'metadata' => $metadata]);
                    }
                } catch (\Throwable $markError) {
                    Log::critical('No se pudo registrar fallo de limpieza SIGMAD', [
                        'mosaic_uuid' => $mosaic->uuid, 'error' => $markError->getMessage()
                    ]);
                }
            }
            Log::error('SIGMAD limpieza bloqueada o incompleta', [
                'mosaic_uuid' => $mosaic->uuid,
                'scope' => $scope,
                'claimed' => $claimed,
                'actor_id' => $actor,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('mosaics.index')->with('error',
                'Limpieza no completada: ' . $e->getMessage()
                . ($claimed ? ' El mosaico quedó bloqueado para evitar inconsistencias; puedes reintentar la misma opción.' : '')
            );
        }
    }

    /**
     * Guard contra borrado indirecto de tablas científicas relacionadas.
     * Si la comprobación de claves foráneas falla, abortamos ANTES de tocar R2.
     */
    private function ensureNoOtherDatabaseReferences(string $table, array $ids, array $allowed): void
    {
        if (!$ids) return;
        $refs = DB::select(
            'SELECT TABLE_NAME AS table_name, COLUMN_NAME AS column_name '
            . 'FROM information_schema.KEY_COLUMN_USAGE '
            . 'WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_SCHEMA = DATABASE() '
            . 'AND REFERENCED_TABLE_NAME = ?',
            [$table]
        );
        foreach ($refs as $ref) {
            $child = (string) $ref->table_name;
            $column = (string) $ref->column_name;
            if (in_array($child, $allowed, true)) continue;
            if (!preg_match('/^[A-Za-z0-9_]+$/', $child)
                || !preg_match('/^[A-Za-z0-9_]+$/', $column)) {
                throw new \RuntimeException('Referencia inesperada en el esquema de datos.');
            }
            foreach (array_chunk($ids, 500) as $chunk) {
                if (DB::table($child)->whereIn($column, $chunk)->exists()) {
                    throw new \RuntimeException(
                        "Existen registros científicos relacionados en {$child}; se bloqueó la eliminación."
                    );
                }
            }
        }
    }
}
