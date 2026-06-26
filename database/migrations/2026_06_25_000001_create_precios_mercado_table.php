<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('precios_mercado', function (Blueprint $table) {
            $table->id();
            $table->string('especie')->unique();
            $table->decimal('precio_por_m3', 10, 2);
            $table->string('moneda')->default('MXN');
            $table->string('fuente')->nullable();
            $table->date('ultima_actualizacion');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('precios_mercado');
    }
};