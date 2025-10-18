<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('especies', function (Blueprint $table) {
            $table->id('id_especie'); // Auto-incremental
            $table->string('nom_cientifico')->unique();
            $table->string('nom_comun');
            $table->string('imagen')->nullable(); // Campo para la ruta de la imagen
            $table->timestamps(); // Opcional: created_at y updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('especies');
    }
};
