<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('field_missions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->unsignedBigInteger('id_parcela');
            $table->foreign('id_parcela')->references('id_parcela')->on('parcelas')->restrictOnDelete();
            $table->foreignId('analysis_job_id')->constrained('analysis_jobs')->restrictOnDelete();
            $table->string('title', 120);
            $table->string('status', 16)->default('active');
            $table->string('crs', 32);
            $table->json('bounds');
            $table->json('geojson'); // Instantánea de las copas seleccionadas; no el análisis completo.
            $table->unsignedSmallInteger('selected_count');
            $table->timestamps();
            $table->index(['id_parcela', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_missions');
    }
};
