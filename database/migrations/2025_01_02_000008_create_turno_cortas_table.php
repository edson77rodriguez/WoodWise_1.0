<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('turno_cortas', function (Blueprint $table) {
            $table->id('id_turno');
            $table->unsignedBigInteger('id_parcela');
            $table->string('codigo_corta')->unique();
            $table->date('fecha_corta');
            $table->date('fecha_fin');
            $table->foreign('id_parcela')->references('id_parcela')->on('parcelas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turno_cortas');
    }
};
