<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'analysis_jobs',
            function (Blueprint $table) {

                $table->id();

                $table->uuid('uuid')
                    ->unique();


                /*
                |--------------------------------------------------------------------------
                | Ortomosaico analizado
                |--------------------------------------------------------------------------
                */

                $table->foreignId(
                    'mosaic_id'
                )
                    ->constrained()
                    ->cascadeOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Tipo de análisis
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'analysis_type',
                    50
                );

                /*
                 * Ejemplos:
                 *
                 * yolo
                 * maskrcnn
                 * compare
                 * full_mosaic
                 * geometry
                 */


                /*
                |--------------------------------------------------------------------------
                | Estado del job
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'status',
                    30
                )->default('queued');

                /*
                 * queued
                 * processing
                 * completed
                 * failed
                 * cancelled
                 */


                /*
                |--------------------------------------------------------------------------
                | Progreso
                |--------------------------------------------------------------------------
                */

                $table->unsignedTinyInteger(
                    'progress'
                )->default(0);

                $table->unsignedInteger(
                    'total_tiles'
                )->default(0);

                $table->unsignedInteger(
                    'processed_tiles'
                )->default(0);


                /*
                |--------------------------------------------------------------------------
                | Configuración científica
                |--------------------------------------------------------------------------
                */

                $table->json(
                    'models'
                )->nullable();

                $table->json(
                    'parameters'
                )->nullable();


                /*
                 * Ejemplo parameters:
                 *
                 * {
                 *   "tile_size": 1024,
                 *   "overlap": 256,
                 *   "yolo_threshold": 0.25,
                 *   "maskrcnn_threshold": 0.40,
                 *   "matching_iou": 0.50
                 * }
                 */


                /*
                |--------------------------------------------------------------------------
                | Resultado resumido
                |--------------------------------------------------------------------------
                */

                $table->json(
                    'summary'
                )->nullable();


                /*
                |--------------------------------------------------------------------------
                | Carpeta lógica en R2
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'result_prefix',
                    1024
                )->nullable();


                /*
                |--------------------------------------------------------------------------
                | Error
                |--------------------------------------------------------------------------
                */

                $table->text(
                    'error_message'
                )->nullable();


                /*
                |--------------------------------------------------------------------------
                | Tiempo
                |--------------------------------------------------------------------------
                */

                $table->timestamp(
                    'started_at'
                )->nullable();

                $table->timestamp(
                    'completed_at'
                )->nullable();

                $table->timestamp(
                    'heartbeat_at'
                )->nullable();


                $table->timestamps();


                /*
                |--------------------------------------------------------------------------
                | Índices
                |--------------------------------------------------------------------------
                */

                $table->index([
                    'mosaic_id',
                    'status'
                ]);

                $table->index(
                    'analysis_type'
                );
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'analysis_jobs'
        );
    }
};