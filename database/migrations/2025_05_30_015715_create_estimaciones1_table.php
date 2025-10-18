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
        Schema::create('estimaciones1', function (Blueprint $table) {
            $table->id('id_estimacion1');
             $table->unsignedBigInteger('id_tipo_e');
            $table->unsignedBigInteger('id_formula');
            $table->double('calculo');
                        $table->double('area_basal')->default(0);

            $table->double('biomasa')->default(0);
            $table->double('carbono')->default(0);

            $table->unsignedBigInteger('id_arbol');
            
            $table->foreign('id_tipo_e')->references('id_tipo_e')->on('tipo_estimaciones');
            $table->foreign('id_formula')->references('id_formula')->on('formulas');
            $table->foreign('id_arbol')->references('id_arbol')->on('arboles');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimaciones1');
    }
};
