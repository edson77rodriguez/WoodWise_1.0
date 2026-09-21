<?php

namespace App\Http\Controllers;

use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use App\Models\AnalysisJob;
use App\Models\AnalysisArtifact;
class AiAnalysisController extends Controller
{
    /**
     * Thresholds seleccionados mediante VALIDATION.
     */
    private const YOLO_DEFAULT_THRESHOLD = 0.25;

    private const MASKRCNN_DEFAULT_THRESHOLD = 0.40;

    /**
     * Umbral operativo inicial para acuerdo
     * espacial entre modelos.
     */
    private const MATCHING_IOU_DEFAULT = 0.50;


    /**
     * Pantalla principal.
     */
    public function index(
        Request $request,
        AiService $aiService
    ) {

        /*
        |--------------------------------------------------------------------------
        | Estado de FastAPI
        |--------------------------------------------------------------------------
        */

        $apiStatus = [
            'status' => 'offline',
            'service' => 'UAV Forest AI',
            'version' => null,
        ];


        try {

            $health = $aiService->health();

            $apiStatus = [
                'status' =>
                    $health['status'] ?? 'ok',

                'service' =>
                    $health['service']
                    ?? 'UAV Forest AI',

                'version' =>
                    $health['version']
                    ?? null,
            ];

        } catch (Throwable $e) {

            Log::warning(
                'FastAPI no disponible.',
                [
                    'message' =>
                        $e->getMessage(),
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Recuperar análisis temporal
        |--------------------------------------------------------------------------
        */

        $analysisData = null;

        $analysisId = $request->query(
            'analysis'
        );


        if ($analysisId) {

            $analysisData = Cache::get(
                'uav_analysis:' . $analysisId
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Variables de vista
        |--------------------------------------------------------------------------
        */

        $result =
            $analysisData['result']
            ?? null;


        $analysisMode =
            $analysisData['model']
            ?? old('model', 'yolo');


        $imageUrl = null;


        if (
            !empty(
                $analysisData['image_path']
            )
        ) {

            $imageUrl = Storage::url(
                $analysisData['image_path']
            );
        }


        return view(
            'analysis.index',
            compact(
                'apiStatus',
                'result',
                'analysisMode',
                'imageUrl',
                'analysisId'
            )
        );
    }


    /**
     * Ejecutar análisis.
     */
    public function analyze(
        Request $request,
        AiService $aiService
    ) {

        /*
        |--------------------------------------------------------------------------
        | Validación
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            'model' => [
                'required',
                'in:yolo,maskrcnn,both',
            ],

            /*
             * Threshold para análisis individual.
             */
            'threshold' => [
                'nullable',
                'numeric',
                'min:0.01',
                'max:0.99',
            ],

            /*
             * Parámetros del modo comparación.
             */
            'yolo_threshold' => [
                'nullable',
                'numeric',
                'min:0.01',
                'max:0.99',
            ],

            'maskrcnn_threshold' => [
                'nullable',
                'numeric',
                'min:0.01',
                'max:0.99',
            ],

            'matching_iou' => [
                'nullable',
                'numeric',
                'min:0.01',
                'max:0.99',
            ],

        ]);


        $model =
            $validated['model'];


        $image =
            $request->file('image');


        /*
        |--------------------------------------------------------------------------
        | Archivo seguro para visor
        |--------------------------------------------------------------------------
        */

        $extension = strtolower(
            $image->getClientOriginalExtension()
        );


        $filename =
            Str::uuid()
            . '.'
            . $extension;


        $directory =
            'analysis/'
            . now()->format('Y/m/d');


        $imagePath = null;


        try {

            /*
            |--------------------------------------------------------------------------
            | Guardar imagen
            |--------------------------------------------------------------------------
            */

            $imagePath = $image->storeAs(
                $directory,
                $filename,
                'public'
            );


            /*
            |--------------------------------------------------------------------------
            | Ejecutar modelo
            |--------------------------------------------------------------------------
            */

            switch ($model) {

                /*
                |--------------------------------------------------------------------------
                | YOLO
                |--------------------------------------------------------------------------
                */

                case 'yolo':

                    $threshold = (
                        isset(
                            $validated['threshold']
                        )
                        &&
                        $validated['threshold']
                        !== null
                    )
                        ? (float)
                            $validated['threshold']
                        : self::
                            YOLO_DEFAULT_THRESHOLD;


                    $result =
                        $aiService->predictYolo(
                            $image,
                            $threshold
                        );

                    break;


                /*
                |--------------------------------------------------------------------------
                | Mask R-CNN
                |--------------------------------------------------------------------------
                */

                case 'maskrcnn':

                    $threshold = (
                        isset(
                            $validated['threshold']
                        )
                        &&
                        $validated['threshold']
                        !== null
                    )
                        ? (float)
                            $validated['threshold']
                        : self::
                            MASKRCNN_DEFAULT_THRESHOLD;


                    $result =
                        $aiService
                            ->predictMaskRcnn(
                                $image,
                                $threshold
                            );

                    break;


                /*
                |--------------------------------------------------------------------------
                | Comparación
                |--------------------------------------------------------------------------
                */

                case 'both':

                    $yoloThreshold =
                        (
                            isset(
                                $validated[
                                    'yolo_threshold'
                                ]
                            )
                            &&
                            $validated[
                                'yolo_threshold'
                            ] !== null
                        )
                            ? (float)
                                $validated[
                                    'yolo_threshold'
                                ]
                            : self::
                                YOLO_DEFAULT_THRESHOLD;


                    $maskThreshold =
                        (
                            isset(
                                $validated[
                                    'maskrcnn_threshold'
                                ]
                            )
                            &&
                            $validated[
                                'maskrcnn_threshold'
                            ] !== null
                        )
                            ? (float)
                                $validated[
                                    'maskrcnn_threshold'
                                ]
                            : self::
                                MASKRCNN_DEFAULT_THRESHOLD;


                    $matchingIou =
                        (
                            isset(
                                $validated[
                                    'matching_iou'
                                ]
                            )
                            &&
                            $validated[
                                'matching_iou'
                            ] !== null
                        )
                            ? (float)
                                $validated[
                                    'matching_iou'
                                ]
                            : self::
                                MATCHING_IOU_DEFAULT;


                    $result =
                        $aiService->compareModels(
                            $image,
                            $yoloThreshold,
                            $maskThreshold,
                            $matchingIou
                        );

                    break;


                default:

                    throw new \RuntimeException(
                        'Modelo no reconocido.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Validar respuesta
            |--------------------------------------------------------------------------
            */

            if (
                !is_array($result)
                ||
                ($result['status'] ?? null)
                !== 'ok'
            ) {

                throw new \RuntimeException(
                    'FastAPI devolvió una respuesta no válida.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Metadata
            |--------------------------------------------------------------------------
            */

            $result['analysis_timestamp'] =
                now()->toDateTimeString();


            /*
            |--------------------------------------------------------------------------
            | Guardar resultado temporal
            |--------------------------------------------------------------------------
            |
            | No usamos session() para guardar los polígonos porque
            | los resultados comparativos pueden ser grandes.
            |
            */

            $analysisId =
                (string) Str::uuid();


            Cache::put(
                'uav_analysis:' . $analysisId,
                [
                    'result' =>
                        $result,

                    'model' =>
                        $model,

                    'image_path' =>
                        $imagePath,
                ],
                now()->addHour()
            );


            /*
            |--------------------------------------------------------------------------
            | Redirección
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route(
                    'analysis.index',
                    [
                        'analysis' =>
                            $analysisId,
                    ]
                )
                ->with(
                    'success',
                    'Análisis completado correctamente.'
                );


        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Limpiar imagen si falla
            |--------------------------------------------------------------------------
            */

            if (
                $imagePath
                &&
                Storage::disk('public')
                    ->exists($imagePath)
            ) {

                Storage::disk('public')
                    ->delete($imagePath);
            }


            /*
            |--------------------------------------------------------------------------
            | Log
            |--------------------------------------------------------------------------
            */

            Log::error(
                'Error durante análisis UAV.',
                [
                    'model' =>
                        $model,

                    'filename' =>
                        $image
                            ->getClientOriginalName(),

                    'message' =>
                        $e->getMessage(),
                ]
            );


            return redirect()
                ->route('analysis.index')
                ->withInput()
                ->with(
                    'error',
                    'No fue posible completar el análisis. '
                    . $e->getMessage()
                );
        }
    }
    /**
 * Consultar un análisis UAV persistente.
 */
public function showJob(
    string $analysisUuid
) {

    $job = AnalysisJob::query()
        ->where(
            'uuid',
            $analysisUuid
        )
        ->with('artifacts')
        ->firstOrFail();


    return response()->json([

        'status' => 'ok',

        'analysis' => [

            'uuid' =>
                $job->uuid,

            'analysis_type' =>
                $job->analysis_type,

            'status' =>
                $job->status,

            'progress' =>
                $job->progress,

            'total_tiles' =>
                $job->total_tiles,

            'processed_tiles' =>
                $job->processed_tiles,

            'models' =>
                $job->models,

            'parameters' =>
                $job->parameters,

            'summary' =>
                $job->summary,

            'started_at' =>
                optional(
                    $job->started_at
                )->toIso8601String(),

            'completed_at' =>
                optional(
                    $job->completed_at
                )->toIso8601String(),
        ],


        'artifacts' =>
            $job->artifacts
                ->map(
                    function (
                        AnalysisArtifact $artifact
                    ) use ($job) {

                        return [

                            'uuid' =>
                                $artifact->uuid,

                            'type' =>
                                $artifact->type,

                            'filename' =>
                                $artifact->filename,

                            'mime_type' =>
                                $artifact->mime_type,

                            'size_bytes' =>
                                $artifact->size_bytes,

                            'checksum_sha256' =>
                                $artifact
                                    ->checksum_sha256,

                            'metadata' =>
                                $artifact->metadata,

                            /*
                             * Importante:
                             * no exponemos object_key.
                             */
                            'download_url' =>
                                route(
                                    'analysis.artifacts.download',
                                    [
                                        'analysisUuid' =>
                                            $job->uuid,

                                        'artifactUuid' =>
                                            $artifact->uuid,
                                    ]
                                ),
                        ];
                    }
                )
                ->values(),
    ]);
}

/**
 * Descargar un artifact de un análisis desde R2 privado.
 */
public function downloadArtifact(
    string $analysisUuid,
    string $artifactUuid
) {

    /*
    |--------------------------------------------------------------------------
    | Resolver job
    |--------------------------------------------------------------------------
    */

    $job = AnalysisJob::query()
        ->where(
            'uuid',
            $analysisUuid
        )
        ->firstOrFail();


    /*
    |--------------------------------------------------------------------------
    | Resolver artifact asegurando que pertenece al job
    |--------------------------------------------------------------------------
    */

    $artifact = AnalysisArtifact::query()
        ->where(
            'uuid',
            $artifactUuid
        )
        ->where(
            'analysis_job_id',
            $job->id
        )
        ->firstOrFail();


    /*
    |--------------------------------------------------------------------------
    | Protección adicional del prefijo
    |--------------------------------------------------------------------------
    |
    | Incluso si hubiera un registro inconsistente en BD,
    | el objeto debe pertenecer al result_prefix del análisis.
    |
    */

    if (
        empty($job->result_prefix)
        ||
        !str_starts_with(
            $artifact->object_key,
            $job->result_prefix
        )
    ) {

        abort(
            403,
            'El archivo no pertenece al análisis solicitado.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Comprobar que existe físicamente en R2
    |--------------------------------------------------------------------------
    */

    if (
        !Storage::disk('r2')
            ->exists(
                $artifact->object_key
            )
    ) {

        abort(
            404,
            'El archivo del análisis no existe en R2.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Generar acceso temporal
    |--------------------------------------------------------------------------
    */

    $url = Storage::disk('r2')
        ->temporaryUrl(
            $artifact->object_key,
            now()->addMinutes(10)
        );


    /*
    |--------------------------------------------------------------------------
    | Navegador → R2
    |--------------------------------------------------------------------------
    */

    return redirect()->away(
        $url
    );
}
}