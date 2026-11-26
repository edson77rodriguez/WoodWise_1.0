<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sigmad_review_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_mission_id')->constrained('field_missions')->restrictOnDelete();
            $table->unsignedInteger('revision_no');
            $table->char('source_sha256', 64);
            $table->longText('geojson'); // COPIA de revisión. Nunca sobrescribe field_missions.geojson.
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('reason', 500);
            $table->timestamps();
            $table->unique(['field_mission_id', 'revision_no'], 'sigmad_review_mission_rev_uniq');
            $table->index(['field_mission_id', 'created_at'], 'sigmad_review_mission_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sigmad_review_revisions');
    }
};
