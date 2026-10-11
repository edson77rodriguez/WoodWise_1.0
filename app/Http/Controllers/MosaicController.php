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

public function inspect(
    Mosaic $mosaic,
    AiService $ai
)
{
    $mosaic->update([
        'status' => 'inspecting',
    ]);

    try {
        $result = $ai->inspectMosaic(
            $mosaic->uuid,
            $mosaic->object_key
        );

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

        $mosaic->refresh();
        $mosaic->loadMissing('project');

        /*
        |--------------------------------------------------------------------------
        | V0.8A.WEB.4 - COG web automático
        |--------------------------------------------------------------------------
        |
        | El COG es un derivado de visualización del MOSAICO, no del AnalysisJob.
        | Se genera una sola vez por fuente verificada y se reutiliza en todos
        | los análisis posteriores del mismo mosaico.
        |
        | Si falla, NO invalida la inspección científica del GeoTIFF original.
        |
        */
        $cogGenerated = false;
        $cogAlreadyValid = false;

        try {
            if ($this->hasCurrentVerifiedWebCog($mosaic)) {
                $cogAlreadyValid = true;
            } else {
                $cogResult = $ai->generateMosaicWebCog(
                    $mosaic->uuid,
                    $mosaic->object_key
                );

                $this->persistWebCogResult(
                    $mosaic,
                    $cogResult
                );

                $cogGenerated = true;
            }
        } catch (\Throwable $cogError) {
            Log::warning(
                'La inspección terminó, pero no fue posible generar el COG web.',
                [
                    'mosaic_uuid' =>
                        $mosaic->uuid,

                    'object_key' =>
                        $mosaic->object_key,

                    'error' =>
                        $cogError->getMessage(),
                ]
            );

            $this->recordWebCogGenerationFailure(
                $mosaic,
                $cogError
            );
        }

        $message = 'Ortomosaico inspeccionado correctamente.';

        if ($cogGenerated) {
            $message .= ' COG web de alta resolución generado y verificado.';
        } elseif ($cogAlreadyValid) {
            $message .= ' El COG web existente continúa válido.';
        } else {
            $message .= ' El COG web quedó pendiente; el GeoTIFF original permanece válido.';
        }

        return redirect()
            ->route('mosaics.index')
            ->with(
                'success',
                $message
            );

    } catch (\Throwable $e) {
        Log::error(
            'Error inspeccionando ortomosaico',
            [
                'mosaic_uuid' =>
                    $mosaic->uuid,

                'error' =>
                    $e->getMessage(),
            ]
        );

        $mosaic->update([
            'status' =>
                'failed',
        ]);

        return redirect()
            ->route('mosaics.index')
            ->with(
                'error',
                'No se pudo inspeccionar: '
                . $e->getMessage()
            );
    }
}


/**
 * Generar o recuperar el COG web de un mosaico READY.
 *
 * Sirve para:
 * - backfill de mosaicos antiguos;
 * - reintentos después de una falla;
 * - regeneración explícita con force=1.
 */
public function generateCog(
    Request $request,
    Mosaic $mosaic,
    AiService $ai
)
{
    if ($mosaic->status !== 'ready') {
        return redirect()
            ->route('mosaics.index')
            ->with(
                'error',
                'El ortomosaico debe estar inspeccionado antes de generar el COG web.'
            );
    }

    $force = $request->boolean(
        'force',
        false
    );

    try {
        $mosaic->loadMissing('project');

        if (
            !$force
            &&
            $this->hasCurrentVerifiedWebCog(
                $mosaic
            )
        ) {
            return redirect()
                ->route('mosaics.index')
                ->with(
                    'success',
                    'El COG web ya existe, pertenece al mosaico y continúa verificado.'
                );
        }

        $result = $ai->generateMosaicWebCog(
            $mosaic->uuid,
            $mosaic->object_key
        );

        $this->persistWebCogResult(
            $mosaic,
            $result
        );

        return redirect()
            ->route('mosaics.index')
            ->with(
                'success',
                'COG web generado y verificado correctamente.'
            );

    } catch (\Throwable $e) {
        Log::error(
            'Error generando COG web del ortomosaico',
            [
                'mosaic_uuid' =>
                    $mosaic->uuid,

                'object_key' =>
                    $mosaic->object_key,

                'error' =>
                    $e->getMessage(),
            ]
        );

        $this->recordWebCogGenerationFailure(
            $mosaic,
            $e
        );

        return redirect()
            ->route('mosaics.index')
            ->with(
                'error',
                'No se pudo generar el COG web: '
                . $e->getMessage()
            );
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
     * V0.8A.WEB.5
     * Eliminar de forma controlada un ortomosaico de prueba.
     *
     * Alcance:
     * - bloquea eliminación si existe un AnalysisJob en cola/proceso;
     * - elimina TODOS los objetos R2 bajo el prefijo exclusivo del mosaico;
     * - elimina artifacts y AnalysisJobs asociados;
     * - elimina finalmente el registro Mosaic.
     *
     * IMPORTANTE:
     * esta acción es irreversible.
     */
    public function destroy(
        Mosaic $mosaic
    )
    {
        $mosaic->loadMissing([
            'project',
            'analysisJobs.artifacts',
        ]);

        $hasActiveJobs = (
            $mosaic->analysisJobs()
                ->whereIn(
                    'status',
                    [
                        'queued',
                        'processing',
                    ]
                )
                ->exists()
        );

        if ($hasActiveJobs) {
            return redirect()
                ->route('mosaics.index')
                ->with(
                    'error',
                    'No se puede eliminar el ortomosaico porque existe un análisis UAV en cola o procesamiento.'
                );
        }

        $projectUuid = (
            $mosaic->project->uuid
            ?? null
        );

        if (!$projectUuid) {
            return redirect()
                ->route('mosaics.index')
                ->with(
                    'error',
                    'No se puede eliminar el ortomosaico porque no tiene un proyecto válido asociado.'
                );
        }

        $prefix = (
            "projects/{$projectUuid}/"
            . "mosaics/{$mosaic->uuid}/"
        );

        $disk = Storage::disk('r2');

        try {
            $remoteFiles = $disk->allFiles(
                $prefix
            );

            $deletedRemoteCount = count(
                $remoteFiles
            );

            foreach (
                array_chunk(
                    $remoteFiles,
                    500
                )
                as
                $chunk
            ) {
                if (!empty($chunk)) {
                    $disk->delete(
                        $chunk
                    );
                }
            }

            $remainingFiles = $disk->allFiles(
                $prefix
            );

            if (!empty($remainingFiles)) {
                throw new \RuntimeException(
                    'R2 todavía contiene '
                    . count($remainingFiles)
                    . ' objeto(s) bajo el prefijo del mosaico.'
                );
            }

            $deletedJobs = 0;
            $deletedArtifacts = 0;

            DB::transaction(
                function () use (
                    $mosaic,
                    &$deletedJobs,
                    &$deletedArtifacts
                ) {
                    foreach (
                        $mosaic->analysisJobs
                        as
                        $job
                    ) {
                        $artifactCount = (
                            $job->artifacts
                                ->count()
                        );

                        if ($artifactCount > 0) {
                            $job->artifacts()
                                ->delete();

                            $deletedArtifacts += (
                                $artifactCount
                            );
                        }

                        $job->delete();

                        $deletedJobs += 1;
                    }

                    $mosaic->delete();
                }
            );

            Log::notice(
                'Ortomosaico eliminado manualmente.',
                [
                    'mosaic_uuid' =>
                        $mosaic->uuid,

                    'project_uuid' =>
                        $projectUuid,

                    'r2_prefix' =>
                        $prefix,

                    'deleted_r2_objects' =>
                        $deletedRemoteCount,

                    'deleted_analysis_jobs' =>
                        $deletedJobs,

                    'deleted_analysis_artifacts' =>
                        $deletedArtifacts,

                    'user_id' =>
                        auth()->id(),
                ]
            );

            return redirect()
                ->route('mosaics.index')
                ->with(
                    'success',
                    'Ortomosaico eliminado correctamente. '
                    . "R2: {$deletedRemoteCount} archivo(s); "
                    . "análisis: {$deletedJobs}; "
                    . "artifacts: {$deletedArtifacts}."
                );

        } catch (\Throwable $e) {
            Log::error(
                'Error eliminando ortomosaico.',
                [
                    'mosaic_uuid' =>
                        $mosaic->uuid,

                    'project_uuid' =>
                        $projectUuid,

                    'r2_prefix' =>
                        $prefix,

                    'user_id' =>
                        auth()->id(),

                    'error' =>
                        $e->getMessage(),
                ]
            );

            return redirect()
                ->route('mosaics.index')
                ->with(
                    'error',
                    'No se pudo eliminar el ortomosaico: '
                    . $e->getMessage()
                );
        }
    }

}