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
        Schema::create('estimaciones', function (Blueprint $table) {
            $table->id('id_estimacion');
            $table->unsignedBigInteger('id_tipo_e');
            $table->unsignedBigInteger('id_formula');
            $table->double('calculo');
            $table->double('biomasa')->default(0);
            $table->double('carbono')->default(0);
            $table->unsignedBigInteger('id_troza');
            $table->foreign('id_tipo_e')->references('id_tipo_e')->on('tipo_estimaciones');
            $table->foreign('id_formula')->references('id_formula')->on('formulas');
            $table->foreign('id_troza')->references('id_troza')->on('trozas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimaciones');
    }
};
