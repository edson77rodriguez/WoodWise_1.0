<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formulas', function (Blueprint $table) {
            $table->string('modo_ejecucion')->default('trigger')->after('id_cat');
            $table->string('estado_revision')->default('revision')->after('modo_ejecucion');
            $table->json('variables_schema')->nullable()->after('estado_revision');
            $table->json('especies_relacionadas')->nullable()->after('variables_schema');
            $table->string('resultado_tipo')->default('calculo')->after('especies_relacionadas');
            $table->decimal('biomasa_factor', 10, 6)->nullable()->after('resultado_tipo');
            $table->decimal('carbono_factor', 10, 6)->nullable()->default(0.5)->after('biomasa_factor');
            $table->text('revision_notas')->nullable()->after('carbono_factor');
            $table->timestamp('revision_at')->nullable()->after('revision_notas');
        });
    }

    public function down(): void
    {
        Schema::table('formulas', function (Blueprint $table) {
            $table->dropColumn([
                'modo_ejecucion',
                'estado_revision',
                'variables_schema',
                'especies_relacionadas',
                'resultado_tipo',
                'biomasa_factor',
                'carbono_factor',
                'revision_notas',
                'revision_at',
            ]);
        });
    }
};