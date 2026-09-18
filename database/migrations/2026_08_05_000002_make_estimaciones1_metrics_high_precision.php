<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE estimaciones1 
            MODIFY calculo DECIMAL(20,10) NOT NULL DEFAULT 0,
            MODIFY biomasa DECIMAL(20,10) NOT NULL DEFAULT 0,
            MODIFY carbono DECIMAL(20,10) NOT NULL DEFAULT 0");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE estimaciones1 
            MODIFY calculo DOUBLE NOT NULL,
            MODIFY biomasa DOUBLE NOT NULL,
            MODIFY carbono DOUBLE NOT NULL");
    }
};