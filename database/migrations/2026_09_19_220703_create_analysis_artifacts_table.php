<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'analysis_artifacts',
            function (Blueprint $table) {

                $table->id();

                $table->uuid('uuid')
                    ->unique();


                /*
                |--------------------------------------------------------------------------
                | Job
                |--------------------------------------------------------------------------
                */

                $table->foreignId(
                    'analysis_job_id'
                )
                    ->constrained()
                    ->cascadeOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Tipo de resultado
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'type',
                    50
                );

                /*
                 * Ejemplos:
                 *
                 * geojson
                 * csv
                 * metrics
                 * preview
                 * report
                 * raster
                 */


                /*
                |--------------------------------------------------------------------------
                | R2
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'object_key',
                    1024
                );

                $table->string(
                    'filename'
                )->nullable();

                $table->string(
                    'mime_type',
                    100
                )->nullable();

                $table->unsignedBigInteger(
                    'size_bytes'
                )->nullable();


                /*
                |--------------------------------------------------------------------------
                | Integridad
                |--------------------------------------------------------------------------
                */

                $table->char(
                    'checksum_sha256',
                    64
                )->nullable();


                /*
                |--------------------------------------------------------------------------
                | Metadata
                |--------------------------------------------------------------------------
                */

                $table->json(
                    'metadata'
                )->nullable();


                $table->timestamps();


                /*
                |--------------------------------------------------------------------------
                | Índices
                |--------------------------------------------------------------------------
                */

                $table->index([
                    'analysis_job_id',
                    'type'
                ]);
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'analysis_artifacts'
        );
    }
};