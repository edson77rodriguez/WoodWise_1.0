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
        Schema::create('arboles', function (Blueprint $table) {
            $table->id('id_arbol');
            $table->unsignedBigInteger('id_especie');
            $table->unsignedBigInteger('id_parcela');
             $table->decimal('altura_total',10,5);
            $table->decimal('diametro_pecho',10,5);
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
        Schema::dropIfExists('arboles');
    }
};
