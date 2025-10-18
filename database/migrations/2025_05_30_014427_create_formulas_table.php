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
         Schema::create('formulas', function (Blueprint $table) {
            $table->id('id_formula');
            $table->string('nom_formula')->unique('nom_formula');
            $table->string('expresion');
            $table->unsignedBigInteger('id_tipo_e');
            $table->foreign('id_tipo_e')->references('id_tipo_e')->on('tipo_estimaciones');
            $table->unsignedBigInteger('id_cat');
            $table->foreign('id_cat')->references('id_cat')->on('catalogos');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formulas');
    }
};
