<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mosaics', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')
                ->unique();


            /*
            |--------------------------------------------------------------------------
            | Proyecto
            |--------------------------------------------------------------------------
            */

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Archivo original
            |--------------------------------------------------------------------------
            */

            $table->string('original_name');

            $table->string(
                'object_key',
                1024
            )->unique();

            $table->unsignedBigInteger(
                'size_bytes'
            )->nullable();

            $table->string(
                'mime_type',
                100
            )->nullable();

            $table->string(
                'extension',
                20
            )->nullable();


            /*
            |--------------------------------------------------------------------------
            | Integridad científica
            |--------------------------------------------------------------------------
            */

            $table->char(
                'checksum_sha256',
                64
            )->nullable();


            /*
            |--------------------------------------------------------------------------
            | Metadata raster
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger(
                'width'
            )->nullable();

            $table->unsignedInteger(
                'height'
            )->nullable();

            $table->unsignedSmallInteger(
                'bands'
            )->nullable();

            $table->string(
                'dtype',
                50
            )->nullable();

            $table->string(
                'crs',
                100
            )->nullable();


            /*
            |--------------------------------------------------------------------------
            | Resolución espacial
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'pixel_size_x',
                14,
                8
            )->nullable();

            $table->decimal(
                'pixel_size_y',
                14,
                8
            )->nullable();

            $table->decimal(
                'gsd_cm',
                10,
                4
            )->nullable();


            /*
            |--------------------------------------------------------------------------
            | Información geoespacial
            |--------------------------------------------------------------------------
            */

            $table->json('bounds')
                ->nullable();

            $table->json('transform')
                ->nullable();

            $table->string(
                'nodata',
                100
            )->nullable();


            /*
            |--------------------------------------------------------------------------
            | Preview
            |--------------------------------------------------------------------------
            */

            $table->string(
                'preview_object_key',
                1024
            )->nullable();


            /*
            |--------------------------------------------------------------------------
            | Estado
            |--------------------------------------------------------------------------
            */

            $table->string(
                'status',
                30
            )->default('uploaded');

            /*
             * Ejemplos:
             *
             * uploading
             * uploaded
             * inspecting
             * ready
             * processing
             * failed
             */


            /*
            |--------------------------------------------------------------------------
            | Metadata adicional Rasterio
            |--------------------------------------------------------------------------
            */

            $table->json('metadata')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Fechas operativas
            |--------------------------------------------------------------------------
            */

            $table->timestamp(
                'uploaded_at'
            )->nullable();

            $table->timestamp(
                'inspected_at'
            )->nullable();


            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */

            $table->index([
                'project_id',
                'status'
            ]);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('mosaics');
    }
};