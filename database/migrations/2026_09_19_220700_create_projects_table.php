<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')
                ->unique();

            /*
            |--------------------------------------------------------------------------
            | Propietario
            |--------------------------------------------------------------------------
            |
            | Laravel normalmente ya tiene tabla users.
            | Lo dejamos nullable para no bloquear desarrollo.
            |
            */

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Información del proyecto
            |--------------------------------------------------------------------------
            */

            $table->string('name');

            $table->text('description')
                ->nullable();

            $table->string('target_species')
                ->nullable();

            $table->string('companion_species')
                ->nullable();

            $table->string('status', 30)
                ->default('active');

            /*
            |--------------------------------------------------------------------------
            | Configuración flexible futura
            |--------------------------------------------------------------------------
            */

            $table->json('settings')
                ->nullable();

            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};