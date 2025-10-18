<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->id('id_persona'); // Equivalente a unsignedBigInteger + autoincrement
            $table->string('nom');
            $table->string('ap');
            $table->string('am');
            $table->string('telefono');
            $table->string('correo')->unique();
            $table->string('contrasena');
            $table->unsignedBigInteger('id_rol');
            $table->boolean('is_producer')->default(false); // O el tipo adecuado
            $table->foreign('id_rol')->references('id_rol')->on('roles');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
