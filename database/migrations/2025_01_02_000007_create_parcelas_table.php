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
        Schema::create('parcelas', function (Blueprint $table) {
            $table->id('id_parcela');
            $table->string('nom_parcela')->unique();
            $table->string('ubicacion');
            $table->unsignedBigInteger('id_productor');
            $table->string('extension');
            $table->text('direccion');
            $table->integer('CP');
            
            $table->foreign('id_productor')->references('id_productor')->on('productores');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcelas');
    }
};
