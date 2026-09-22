<?php

namespace App\Services;

use App\Models\AnalysisArtifact;
use App\Models\AnalysisJob;
use App\Models\Mosaic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class WallToWallAnalysisService
{
    public function __construct(
        private AiService $aiService
    ) {
    }


    /**
     * Ejecuta el pipeline wall-to-wall V0.7E
     * para un mosaico ya autorizado por la capa superior.
     *
     * IMPORTANTE:
     * Este servicio NO decide permisos de usuario.
     * Eso corresponderá al controlador/policy.
     */
    public function run(
        Mosaic $mosaic
    ): AnalysisJob {

        // ====================================================
        // 1. VALIDAR MOSAICO / PROYECTO
        // ====================================================

        $mosaic->loadMissing(
            'project'
        );


        if (!$mosaic->project) {

            throw new RuntimeException(
                'El mosaico no tiene un proyecto asociado.'
            );
        }


        $projectUuid = (
            $mosaic->project->uuid
            ?? null
        );


        if (!$projectUuid) {

            throw new RuntimeException(
                'El proyecto asociado no tiene UUID.'
            );
        }


        if (!$mosaic->uuid) {

            throw new RuntimeException(
                'El mosaico no tiene UUID.'
            );
        }


        if (!$mosaic->object_key) {

            throw new RuntimeException(
                'El mosaico no tiene object_key.'
            );
        }


        // ====================================================
        // 2. CONFIGURACIÓN CIENTÍFICA CONGELADA
        // ====================================================

        $parameters = [

            'scientific_method_version' =>
                'V0.6G',

            'pipeline_version' =>
                'V0.7E',

            'source_size' =>
                2144,

            'output_size' =>
                1024,

            'output_overlap' =>
                256,

            'yolo_threshold' =>
                0.25,

            'maskrcnn_threshold' =>
                0.40,

            'mask_threshold' =>
                0.50,

            'intramodel_iou' =>
                0.50,

            'intermodel_iou' =>
                0.50,
        ];


        $models = [

            'primary_geometry_model' =>
                'Mask R-CNN',

            'secondary_model' =>
                'YOLO-Seg',

            'scientific_method_version' =>
                'V0.6G',

            'pipeline_version' =>
                'V0.7E',
        ];


        // ====================================================
        // 3. CREAR JOB ANTES DE LLAMAR A FASTAPI
        // ====================================================

        $job = AnalysisJob::create([

            'uuid' =>
                (string) Str::uuid(),

            'mosaic_id' =>
                $mosaic->id,

            'analysis_type' =>
                'wall_to_wall_tree_crown',

            'status' =>
                'processing',

            'progress' =>
                0,

            // Por ahora FastAPI no devuelve el número de
            // ventanas como campo HTTP independiente.
            // No lo inventamos.
            'total_tiles' =>
                0,

            'processed_tiles' =>
                0,

            'models' =>
                $models,

            'parameters' =>
                $parameters,

            'summary' =>
                null,

            'result_prefix' =>
                null,

            'error_message' =>
                null,

            'started_at' =>
                now(),

            'completed_at' =>
                null,

            'heartbeat_at' =>
                now(),
        ]);


        try {

            // =================================================
            // 4. EJECUTAR FASTAPI
            // =================================================

            $result = (
                $this->aiService
                    ->analyzeMosaicWallToWall(

                        $job->uuid,

                        $mosaic->uuid,

                        $mosaic->object_key,

                        $projectUuid
                    )
            );


            // =================================================
            // 5. VALIDAR IDENTIDAD DE LA RESPUESTA
            // =================================================

            if (
                ($result['status'] ?? null)
                !==
                'ok'
            ) {

                throw new RuntimeException(
                    'FastAPI no devolvió status=ok.'
                );
            }


            if (
                ($result['analysis_uuid'] ?? null)
                !==
                $job->uuid
            ) {

                throw new RuntimeException(
                    'El analysis_uuid devuelto por FastAPI '
                    . 'no coincide con el AnalysisJob.'
                );
            }


            if (
                ($result['mosaic_uuid'] ?? null)
                !==
                $mosaic->uuid
            ) {

                throw new RuntimeException(
                    'El mosaic_uuid devuelto por FastAPI '
                    . 'no coincide con el mosaico solicitado.'
                );
            }


            if (
                ($result['project_uuid'] ?? null)
                !==
                $projectUuid
            ) {

                throw new RuntimeException(
                    'El project_uuid devuelto por FastAPI '
                    . 'no coincide con el proyecto solicitado.'
                );
            }


            // =================================================
            // 6. VALIDAR SUMMARY
            // =================================================

            $summary = (
                $result['summary']
                ?? null
            );


            if (!is_array($summary)) {

                throw new RuntimeException(
                    'FastAPI no devolvió un summary válido.'
                );
            }


            $requiredSummaryFields = [

                'raw_predictions',
                'unique_predictions',
                'unique_yolo',
                'unique_maskrcnn',
                'catalog_groups',
                'primary_objects',
                'primary_maskrcnn',
                'primary_yolo_fallback',
                'requires_review',
                'no_review',
                'bilateral_clean',
                'yolo_only',
                'mrcnn_only',
                'split_merge_objects',
                'containment_conflict_objects',
                'intermodel_relations',
                'primary_matches',
            ];


            foreach (
                $requiredSummaryFields
                as
                $field
            ) {

                if (
                    !array_key_exists(
                        $field,
                        $summary
                    )
                ) {

                    throw new RuntimeException(
                        "Falta {$field} en summary."
                    );
                }
            }


            // =================================================
            // 7. VALIDAR ARTIFACTS
            // =================================================

            $artifacts = (
                $result['artifacts']
                ?? null
            );


            if (
                !is_array($artifacts)
                ||
                count($artifacts) !== 3
            ) {

                throw new RuntimeException(
                    'Se esperaban exactamente 3 artifacts.'
                );
            }


            $artifactTypes = [];


            foreach (
                $artifacts
                as
                $artifact
            ) {

                $type = (
                    $artifact['type']
                    ?? null
                );


                if (
                    !in_array(
                        $type,
                        [
                            'geopackage',
                            'manifest',
                            'geojson',
                        ],
                        true
                    )
                ) {

                    throw new RuntimeException(
                        'Tipo de artifact inesperado.'
                    );
                }


                if (
                    isset(
                        $artifactTypes[$type]
                    )
                ) {

                    throw new RuntimeException(
                        "Artifact duplicado: {$type}."
                    );
                }


                $artifactTypes[
                    $type
                ] = true;


                if (
                    ($artifact['verified'] ?? false)
                    !==
                    true
                ) {

                    throw new RuntimeException(
                        "Artifact {$type} no fue verificado."
                    );
                }


                $checksum = (
                    $artifact[
                        'checksum_sha256'
                    ]
                    ?? null
                );


                if (
                    !is_string($checksum)
                    ||
                    strlen($checksum) !== 64
                ) {

                    throw new RuntimeException(
                        "Checksum SHA-256 inválido "
                        . "para {$type}."
                    );
                }


                foreach (
                    [
                        'object_key',
                        'filename',
                        'mime_type',
                        'size_bytes',
                    ]
                    as
                    $requiredField
                ) {

                    if (
                        !isset(
                            $artifact[
                                $requiredField
                            ]
                        )
                    ) {

                        throw new RuntimeException(
                            "Falta {$requiredField} "
                            . "en artifact {$type}."
                        );
                    }
                }


                if (
                    !is_numeric(
                        $artifact['size_bytes']
                    )
                    ||
                    (int) $artifact['size_bytes'] <= 0
                ) {

                    throw new RuntimeException(
                        "size_bytes inválido para {$type}."
                    );
                }


                $expectedMimeTypes = [
                    'geopackage' =>
                        'application/geopackage+sqlite3',

                    'manifest' =>
                        'application/json',

                    'geojson' =>
                        'application/geo+json',
                ];


                if (
                    $artifact['mime_type']
                    !==
                    $expectedMimeTypes[$type]
                ) {

                    throw new RuntimeException(
                        "mime_type inesperado para {$type}."
                    );
                }


                if ($type === 'geojson') {

                    $featureCount = (
                        $artifact['feature_count']
                        ?? null
                    );


                    if (
                        !is_numeric($featureCount)
                        ||
                        (int) $featureCount
                        !==
                        (int) ($summary['primary_objects'] ?? -1)
                    ) {

                        throw new RuntimeException(
                            'feature_count del GeoJSON no coincide '
                            . 'con summary.primary_objects.'
                        );
                    }


                    if (
                        ($artifact['source_crs'] ?? null)
                        !==
                        'EPSG:32614'
                    ) {

                        throw new RuntimeException(
                            'source_crs inesperado en GeoJSON.'
                        );
                    }


                    if (
                        ($artifact['web_crs'] ?? null)
                        !==
                        'EPSG:4326'
                    ) {

                        throw new RuntimeException(
                            'web_crs inesperado en GeoJSON.'
                        );
                    }


                    $geometryTypes = (
                        $artifact['geometry_types']
                        ?? null
                    );


                    if (!is_array($geometryTypes)) {

                        throw new RuntimeException(
                            'geometry_types inválido en GeoJSON.'
                        );
                    }


                    $allowedGeometryTypes = [
                        'Polygon',
                        'MultiPolygon',
                    ];


                    foreach (
                        $geometryTypes
                        as
                        $geometryType => $geometryCount
                    ) {

                        if (
                            !in_array(
                                $geometryType,
                                $allowedGeometryTypes,
                                true
                            )
                            ||
                            !is_numeric($geometryCount)
                            ||
                            (int) $geometryCount < 0
                        ) {

                            throw new RuntimeException(
                                'geometry_types contiene valores inválidos.'
                            );
                        }
                    }


                    if (
                        array_sum($geometryTypes)
                        !==
                        (int) $featureCount
                    ) {

                        throw new RuntimeException(
                            'La suma de geometry_types no coincide '
                            . 'con feature_count.'
                        );
                    }
                }
            }


            foreach (
                [
                    'geopackage',
                    'manifest',
                    'geojson',
                ]
                as
                $requiredArtifactType
            ) {

                if (
                    !isset(
                        $artifactTypes[
                            $requiredArtifactType
                        ]
                    )
                ) {

                    throw new RuntimeException(
                        "Falta artifact requerido: {$requiredArtifactType}."
                    );
                }
            }


            // =================================================
            // 8. VALIDAR PREFIX R2
            // =================================================

            $resultPrefix = (
                $result[
                    'result_prefix'
                ]
                ?? null
            );


            $expectedPrefix = (

                "projects/{$projectUuid}/"
                . "mosaics/{$mosaic->uuid}/"
                . "analyses/{$job->uuid}/"
            );


            if (
                !is_string(
                    $resultPrefix
                )
                ||
                !str_starts_with(
                    $resultPrefix,
                    $expectedPrefix
                )
            ) {

                throw new RuntimeException(
                    'result_prefix no corresponde '
                    . 'al análisis solicitado.'
                );
            }


            foreach (
                $artifacts
                as
                $artifact
            ) {

                if (
                    !str_starts_with(
                        $artifact['object_key'],
                        $resultPrefix . '/'
                    )
                ) {

                    throw new RuntimeException(
                        'Artifact fuera del result_prefix esperado: '
                        . $artifact['type']
                    );
                }
            }


            // =================================================
            // 9. PERSISTENCIA ATÓMICA
            // =================================================

            DB::transaction(
                function () use (
                    $job,
                    $result,
                    $summary,
                    $artifacts,
                    $resultPrefix
                ) {

                    foreach (
                        $artifacts
                        as
                        $artifact
                    ) {

                        $artifactMetadata = [

                            'verified' =>
                                true,

                            'scientific_method_version' =>
                                $result[
                                    'scientific_method_version'
                                ]
                                ?? 'V0.6G',

                            'pipeline_version' =>
                                $result[
                                    'pipeline_version'
                                ]
                                ?? 'V0.7E',
                        ];


                        if (
                            $artifact['type']
                            ===
                            'geojson'
                        ) {

                            $artifactMetadata[
                                'feature_count'
                            ] = (int) $artifact[
                                'feature_count'
                            ];

                            $artifactMetadata[
                                'source_crs'
                            ] = $artifact[
                                'source_crs'
                            ];

                            $artifactMetadata[
                                'web_crs'
                            ] = $artifact[
                                'web_crs'
                            ];

                            $artifactMetadata[
                                'geometry_types'
                            ] = $artifact[
                                'geometry_types'
                            ];

                            $artifactMetadata[
                                'purpose'
                            ] = 'web_visualization';
                        }


                        AnalysisArtifact::create([

                            'uuid' =>
                                (string) Str::uuid(),

                            'analysis_job_id' =>
                                $job->id,

                            'type' =>
                                $artifact[
                                    'type'
                                ],

                            'object_key' =>
                                $artifact[
                                    'object_key'
                                ],

                            'filename' =>
                                $artifact[
                                    'filename'
                                ],

                            'mime_type' =>
                                $artifact[
                                    'mime_type'
                                ],

                            'size_bytes' =>
                                (int) $artifact[
                                    'size_bytes'
                                ],

                            'checksum_sha256' =>
                                $artifact[
                                    'checksum_sha256'
                                ],

                            'metadata' =>
                                $artifactMetadata,
                        ]);
                    }


                    // -----------------------------------------
                    // total_tiles:
                    //
                    // FastAPI V0.7E devuelve el número real
                    // de ventanas planificadas para el mosaico.
                    // -----------------------------------------

                    $totalTiles = (
                        data_get(
                            $result,
                            'total_tiles'
                        )
                    );


                    $job->update([

                        'status' =>
                            'completed',

                        'progress' =>
                            100,

                        'total_tiles' =>
                            $totalTiles,

                        'processed_tiles' =>
                            $totalTiles,

                        'summary' =>
                            $summary,

                        'result_prefix' =>
                            $resultPrefix,

                        'error_message' =>
                            null,

                        'completed_at' =>
                            now(),

                        'heartbeat_at' =>
                            now(),
                    ]);
                }
            );


            // =================================================
            // 10. DEVOLVER JOB ACTUALIZADO
            // =================================================

            return (
                $job
                    ->fresh()
                    ->load(
                        'artifacts'
                    )
            );

        } catch (Throwable $exception) {

            // =================================================
            // ERROR
            // =================================================

            $job->update([

                'status' =>
                    'failed',

                'error_message' =>
                    Str::limit(
                        $exception->getMessage(),
                        2000
                    ),

                'completed_at' =>
                    now(),

                'heartbeat_at' =>
                    now(),
            ]);


            Log::error(
                'Wall-to-wall analysis failed.',
                [

                    'analysis_uuid' =>
                        $job->uuid,

                    'mosaic_uuid' =>
                        $mosaic->uuid,

                    'exception' =>
                        get_class(
                            $exception
                        ),

                    'message' =>
                        $exception->getMessage(),
                ]
            );


            throw $exception;
        }
    }
}