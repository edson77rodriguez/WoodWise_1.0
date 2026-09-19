<?php

namespace App\Http\Controllers;

use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

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
}