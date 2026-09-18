<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('precios_mercado', function (Blueprint $table) {
            if (!Schema::hasColumn('precios_mercado', 'estado')) {
                $table->string('estado')->default('Estado de Mexico')->after('especie');
            }
        });

        DB::table('precios_mercado')
            ->whereNull('estado')
            ->orWhere('estado', '')
            ->update(['estado' => 'Estado de Mexico']);

        Schema::table('precios_mercado', function (Blueprint $table) {
            try {
                $table->dropUnique('precios_mercado_especie_unique');
            } catch (\Throwable $e) {
                // Si el indice no existe, continuamos.
            }

            try {
                $table->unique(['especie', 'estado'], 'precios_mercado_especie_estado_unique');
            } catch (\Throwable $e) {
                // Si ya existe, continuamos.
            }
        });
    }

    public function down(): void
    {
        Schema::table('precios_mercado', function (Blueprint $table) {
            try {
                $table->dropUnique('precios_mercado_especie_estado_unique');
            } catch (\Throwable $e) {
                // Ignorar si no existe.
            }

            if (Schema::hasColumn('precios_mercado', 'estado')) {
                $table->dropColumn('estado');
            }

            try {
                $table->unique('especie');
            } catch (\Throwable $e) {
                // Ignorar si ya existe.
            }
        });
    }
};