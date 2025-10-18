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
        Schema::create('asigna_parcelas', function (Blueprint $table) {
            $table->id('id_asigna_p');
            $table->unsignedBigInteger('id_tecnico');
            $table->unsignedBigInteger('id_parcela');
            
            $table->foreign('id_tecnico')->references('id_tecnico')->on('tecnicos');
            $table->foreign('id_parcela')->references('id_parcela')->on('parcelas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asigna_parcelas');
    }
};
