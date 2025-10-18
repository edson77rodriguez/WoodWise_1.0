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
        Schema::create('trozas', function (Blueprint $table) {
            $table->id('id_troza');
            $table->decimal('longitud',10,5);
            $table->decimal('diametro',10,5);
            $table->decimal('diametro_otro_extremo',10,5)->nullable();
            $table->decimal('diametro_medio',10,5)->nullable();
            $table->decimal('densidad',10,5);
            $table->unsignedBigInteger('id_especie');
            $table->unsignedBigInteger('id_parcela');
            
            $table->foreign('id_especie')->references('id_especie')->on('especies');
            $table->foreign('id_parcela')->references('id_parcela')->on('parcelas');
            
            $table->timestamps();
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trozas');
    }
};
